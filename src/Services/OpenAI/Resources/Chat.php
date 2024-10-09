<?php

namespace AIMuse\Services\OpenAI\Resources;

use AIMuse\Attributes\GenerateTextOptions;
use AIMuseVendor\GuzzleHttp\Psr7\Utils;
use AIMuse\Contracts\Transporter;
use AIMuse\Exceptions\GenerateException;
use AIMuse\Helpers\PricingHelper;
use AIMuse\Models\History;
use AIMuse\Models\Settings;
use AIMuse\Services\OpenAI\OpenAI;
use AIMuseVendor\Psr\Http\Message\StreamInterface;
use AIMuse\Services\OpenAI\Responses\ChatResponse;
use AIMuse\Services\Api\Stream;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class Chat
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function create($options)
  {
    $response = $this->transporter->post("chat/completions", $options);

    return ChatResponse::fromJson($response);
  }

  private function convertRole(string $role)
  {
    $roles = [
      'user' => 'user',
      'model' => 'assistant',
    ];

    return $roles[$role];
  }

  private function convertRoles(array $messages)
  {
    foreach ($messages as &$message) {
      $message['role'] = $this->convertRole($message['role']) ?? $message['role'];
    }

    return $messages;
  }

  private function trimContext(GenerateTextOptions $options)
  {
    $summary = array_reverse($options->messages);
    $totalLength = 0;
    $context = [];

    $last = $summary[array_key_last($summary)];

    if ($last['role'] === 'system') {
      $totalLength += $last['tokens'];
    }

    foreach ($summary as $message) {
      if ($totalLength + $message['tokens'] <= $options->contextLength || $message['role'] === 'system') {
        $context[] = $message;
        $totalLength += $message['tokens'];
      }
    }

    $context = array_reverse($context);

    return $context;
  }

  /**
   * Create a stream of chat completions
   *
   * @param array $request
   * @param callable $callback
   * @return void
   */
  public function stream(GenerateTextOptions $options)
  {
    $request = [
      'model' => $options->model->name,
      'messages' => [],
      'stream' => true
    ];

    if (!$options->contextLength) {
      $options->contextLength = $options->model->defaults['contextLength'];
    }

    if (is_array($options->model->settings)) {
      foreach ($options->model->settings as $key => $value) {
        $request[$key] = $value;
      }
    }

    if (!$options->messages) {
      $options->messages = [];
    }

    if ($options->systemPrompt) {
      array_unshift($options->messages, [
        'role' => 'system',
        'content' => $options->systemPrompt
      ]);
    }

    $options->messages[] = [
      'role' => 'user',
      'content' => $options->userPrompt,
    ];

    $options->messages = $this->convertRoles($options->messages);
    $options->messages = $this->summary($options->messages);

    $totalTokens = collect($options->messages)->sum('tokens');

    if (isset($request['max_tokens']) && $request['max_tokens'] + $totalTokens > $options->contextLength) {
      $request['max_tokens'] -= $totalTokens + 50;
      if ($request['max_tokens'] < 0) {
        unset($request['max_tokens']);
      }
    }

    $lastMessage = end($options->messages);
    if ($lastMessage['tokens'] > $options->contextLength) {
      throw new GenerateException('The context length is too short for the user prompt.', 400);
    }

    $options->messages = $this->trimContext($options);

    foreach ($options->messages as $message) {
      $request['messages'][] = [
        'role' => $message['role'],
        'content' => $message['content']
      ];
    }

    $response = $this->transporter->stream("chat/completions", $request);

    $completions = [];

    while (!$response->eof()) {
      $line = Utils::readLine($response);
      if (!str_starts_with($line, 'data: ')) {
        continue;
      }
      $line = explode('data: ', $line)[1];
      if (strlen($line) == 0) {
        continue;
      }

      if ($line == '[DONE]') {
        break;
      }

      try {
        $data = json_decode($line);

        Log::debug('OpenAI stream chunk', [
          'data' => $data
        ]);

        $prediction = $data->choices[0]->delta->content;

        if (strlen($prediction) == 0) {
          continue;
        }
        $completions[] = $prediction;
        $action = $options->callback('message', $prediction);
        if ($action == 'stop') {
          break;
        }
      } catch (\Throwable $th) {
        Log::debug('OpenAI stream chunk error', [
          'error' => $th
        ]);

        continue;
      }
    }

    $tokens = $this->count([
      'model' => $request['model'],
      'prompts' => $request['messages'],
      'completions' => $completions
    ]);

    $tokens['total_price'] ??= PricingHelper::text([
      'service' => 'openai',
      'model' => $request['model'],
      'tokens' => [
        'input' => $tokens['prompt_tokens'],
        'output' => $tokens['completion_tokens']
      ]
    ]);

    $history = new History([
      'user_id' => get_current_user_id(),
      'model' => $request['model'],
      'service' => 'openai',
      'component' => $options->component,
      'tokens' => $tokens['total_tokens'],
      'price' => $tokens['total_price'],
    ]);

    try {
      $history->save();
    } catch (\Throwable $th) {
      Log::error('OpenAI history save error', [
        'error' => $th
      ]);
    }

    $options->callback('done', $history->toArray());
    $response->close();
  }

  private function count(array $data)
  {
    try {
      return aimuse()->api()->count($data);
    } catch (\Throwable $th) {
      Log::warning("OpenAI token counting using the api service failed. Tokens will be counted in the traditional way.", [
        'error' => $th
      ]);

      $inputTokens = 0;
      $outputTokens = 0;

      if (isset($data['prompts'])) {
        foreach ($data['prompts'] as $prompt) {
          $inputTokens += $this->estimateTokens($prompt);
        }
      }

      if (isset($data['completions'])) {
        foreach ($data['completions'] as $completion) {
          $outputTokens += strlen($completion);
        }
      }

      $inputTokens = intval($inputTokens);
      $outputTokens = intval($outputTokens / 4);

      return [
        'prompt_tokens' => $inputTokens,
        'completion_tokens' => $outputTokens,
        'total_tokens' => $inputTokens + $outputTokens,
      ];
    }
  }

  public function estimateTokens(array $message)
  {
    $tokens = strlen($message['role']);
    $tokens += strlen($message['content']);
    $tokens += 3;
    return intval($tokens / 4);
  }

  public function summary(array $messages)
  {
    try {
      $tokens = aimuse()->api()->summary([
        'tokenizer' => 'gpt',
        'messages' => $messages
      ]);

      foreach ($messages as $key => &$message) {
        $message['tokens'] = $tokens[$key];
      }
    } catch (\Throwable $th) {
      Log::error('OpenAI token summary error', [
        'error' => $th
      ]);

      foreach ($messages as &$message) {
        $message['tokens'] = $this->estimateTokens($message);
      }
    }

    return $messages;
  }
}
