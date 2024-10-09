<?php

namespace AIMuse\Services\GoogleAI\Resources;

use AIMuse\Models\History;
use AIMuse\Helpers\StreamHelper;
use AIMuse\Contracts\Transporter;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use AIMuse\Attributes\GenerateTextOptions;
use AIMuse\Exceptions\GenerateException;
use AIMuse\Services\GoogleAI\Responses\ContentResponse;
use Exception;
use AIMuseVendor\GuzzleHttp\Exception\ClientException;

class Content
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function create(array $options, string $model)
  {
    $endpoint = "models/{$model}:generateContent";
    $response = $this->transporter->post("$endpoint", $options);

    return ContentResponse::fromJson($response);
  }

  private function trimContext(GenerateTextOptions $options)
  {
    $summary = array_reverse($options->messages);

    $totalLength = 0;

    $context = [];

    foreach ($summary as $message) {
      $totalLength += $message['tokens'];

      if ($totalLength <= $options->contextLength) {
        $context[] = $message;
      }
    }

    $context = array_reverse($context);

    if ($context[0]['role'] !== 'user') {
      array_splice($context, 0, 1);
    }

    return $context;
  }

  private function addSystemPrompt(GenerateTextOptions $options)
  {
    if ($options->systemPrompt) {
      array_unshift($options->request->contents[0]['parts'], [
        'text' => $options->systemPrompt
      ]);
    }

    return $options->request->contents;
  }

  public function stream(GenerateTextOptions $options)
  {
    $options->request = (object) [
      'contents' => [],
    ];

    if (!$options->contextLength) {
      $options->contextLength = $options->model->defaults['maxInputs'];
    }

    $options->contextLength -= $this->estimateTokens($options->systemPrompt);

    $options->messages[] = [
      'role' => 'user',
      'content' => $options->userPrompt
    ];

    $options->messages = $this->summary($options->messages);
    $options->messages = $this->trimContext($options);

    foreach ($options->messages as $message) {
      $options->request->contents[] = [
        'role' => $message['role'],
        'parts' => [
          [
            'text' => $message['content']
          ]
        ]
      ];
    }

    $options->request->contents = $this->addSystemPrompt($options);

    if (is_array($options->model->settings)) {
      $options->request->generationConfig = $options->model->settings;
    }

    $endpoint = "models/{$options->model->name}:streamGenerateContent";
    try {
      $response = $this->transporter->stream("$endpoint", (array) $options->request);
    } catch (ClientException $error) {
      $response = $error->getResponse()->getBody()->getContents();
      $data = json_decode($response, true);
      throw new GenerateException($data[0]['error']['message'], $data[0]['error']['code']);
    }

    $openArrayBrace = $response->read(1);

    if ($openArrayBrace !== '[') {
      throw new \Exception("Error Processing Request", 1);
    }

    $contents = $options->request->contents;

    while (!$response->eof()) {
      $data = StreamHelper::readJson($response);
      if ($data) {
        try {
          $candidate = $data['candidates'][0];
          $prediction = $candidate['content']['parts'][0]['text'];
          $reason = $candidate['finishReason'];

          if ($reason == 'SAFETY') {
            Log::warning('GoogleAI stream stopped due to safety reasons', [
              'candidate' => $candidate
            ]);

            $options->callback('error', 'Content generation stopped due to safety reasons');
          }

          if (!$prediction) {
            continue;
          }

          $contents[] = [
            [
              'parts' => [
                [
                  'text' => $prediction
                ]
              ]
            ]
          ];
          $action = $options->callback('message', $prediction);

          Log::debug('GoogleAI stream chunk', [
            'data' => $data
          ]);

          if ($action == 'stop') {
            break;
          }
        } catch (\Throwable $th) {
          Log::debug('GoogleAI stream chunk error', [
            'error' => $th
          ]);
        }
      }
      StreamHelper::readChar(',', $response);
    }

    $response->close();

    try {
      $tokens = $this->count([
        'contents' => $contents
      ], $options->model->name);
    } catch (\Throwable $th) {
      Log::error('GoogleAI token count error', [
        'error' => $th
      ]);
      $tokens = 0;
    }

    $price = 0;

    $history = new History([
      'user_id' => get_current_user_id(),
      'model' => $options->model->name,
      'service' => 'googleai',
      'component' => $options->component,
      'tokens' => $tokens,
      // TODO: Calculate price when Gemini Pro is released
      'price' => $price,
    ]);

    try {
      $history->save();
    } catch (\Throwable $th) {
      Log::error('GoogleAI history save error', [
        'error' => $th
      ]);
    }

    $options->callback('done', $history->toArray());
  }

  public function count(array $options, string $model)
  {
    $endpoint = "models/{$model}:countTokens";
    $response = $this->transporter->post("$endpoint", $options);

    return $response->totalTokens;
  }

  public function estimateTokens(string $message)
  {
    return intval(strlen($message) / 3.25);
  }

  public function summary(array $messages)
  {
    try {
      $tokens = aimuse()->api()->summary([
        'tokenizer' => 'gemini',
        'messages' => $messages
      ]);

      foreach ($messages as $key => &$message) {
        $message['tokens'] = $tokens[$key];
      }
    } catch (\Throwable $th) {
      Log::error('GoogleAI token summary error', [
        'error' => $th
      ]);

      foreach ($messages as &$message) {
        $message['tokens'] = $this->estimateTokens($message['content']);
      }
    }

    return $messages;
  }
}
