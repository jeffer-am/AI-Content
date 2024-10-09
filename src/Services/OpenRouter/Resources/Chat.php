<?php

namespace AIMuse\Services\OpenRouter\Resources;

use AIMuse\Attributes\GenerateTextOptions;
use AIMuseVendor\GuzzleHttp\Psr7\Utils;
use AIMuse\Contracts\Transporter;
use AIMuse\Exceptions\GenerateException;
use AIMuse\Models\History;
use AIMuse\Services\OpenRouter\Responses\ChatResponse;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuseVendor\GuzzleHttp\Exception\ClientException;

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

    if ($options->systemPrompt) {
      $request['messages'][] = [
        'role' => 'system',
        'content' => $options->systemPrompt,
      ];
    }

    foreach ($options->messages as $message) {
      $request['messages'][] = [
        'role' => $this->convertRole($message['role']),
        'content' => $message['content'],
      ];
    }

    $request['messages'][] = [
      'role' => 'user',
      'content' => $options->userPrompt,
    ];

    if (is_array($options->model->settings)) {
      foreach ($options->model->settings as $key => $value) {
        $request[$key] = $value;
      }
    }

    $response = $this->transporter->stream("chat/completions", $request);

    $id = null;

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

      $data = json_decode($line);

      if (!$data) continue;

      if (property_exists($data, 'error')) {
        throw new GenerateException("OpenRouter Error: {$data->error->message}", $data->error->code);
      }

      try {
        if (!$id) {
          $id = $data->id;
        }

        Log::debug('OpenRouter stream chunk', [
          'data' => $data,
        ]);

        $prediction = $data->choices[0]->delta->content;
        if (strlen($prediction) == 0) {
          continue;
        }
        $action = $options->callback('message', $prediction);
        if ($action == 'stop') {
          break;
        }
      } catch (\Throwable $th) {
        Log::debug('OpenRouter stream chunk error', [
          'error' => $th,
        ]);

        continue;
      }
    }

    $response->close();

    if (!$id) {
      Log::warning('OpenRouter stream is not returning an id', [
        'model' => $options->model->name,
      ]);
    }

    $generation = $this->generation($id)->data;

    $totalTokens = $generation->tokens_prompt + $generation->tokens_completion;

    $history = new History([
      'user_id' => get_current_user_id(),
      'model' => $request['model'],
      'service' => 'openrouter',
      'component' => $options->component,
      'data' => [
        'id' => $id,
      ],
      'tokens' => $totalTokens,
      'price' => $generation->usage,
    ]);

    try {
      $history->save();
    } catch (\Throwable $th) {
      Log::error('OpenRouter history save error', [
        'error' => $th,
      ]);
    }

    $options->callback('done', $history->toArray());
  }

  public function generation($id, $tries = 0): object
  {
    $response = (object)[
      'data' => (object) [
        'tokens_prompt' => 0,
        'tokens_completion' => 0,
        'usage' => 0,
      ]
    ];

    if (!$id) {
      return $response;
    }

    try {
      $response = $this->transporter->get("generation?id=$id");
    } catch (ClientException $error) {
      if ($error->getCode() == 404) {
        if ($tries > 0) {
          sleep(1);
          return $this->generation($id, $tries - 1);
        }
      }
    } catch (\Throwable $th) {
    }

    return $response;
  }
}
