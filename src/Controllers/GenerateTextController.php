<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Models\Settings;
use AIMuse\Models\Template;
use AIMuse\Attributes\Route;
use AIMuse\Attributes\GenerateTextOptions;
use AIMuse\Exceptions\ApiKeyNotSetException;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Exceptions\GenerateException;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Helpers\PromptHelper;
use AIMuse\Validators\Validator;
use AIMuse\Helpers\ResponseHelper;
use AIMuse\Middleware\Editor;
use AIMuse\Models\AIModel;
use AIMuse\Models\Dataset;
use AIMuse\Services\OpenAI\OpenAI;
use AIMuse\Validators\GenerateTextValidator;
use AIMuse\Services\GoogleAI\GoogleAI;
use AIMuse\Services\OpenRouter\OpenRouter;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class GenerateTextController extends Controller
{
  public array $middlewares = [
    Editor::class,
  ];

  /**
   * @Route(path="/admin/generate/text", method="POST")
   */
  public function text(Request $request)
  {
    $violations = $request->validate(GenerateTextValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $systemPrompt = '';

    if (!$request->withoutSystemPrompt) {
      $systemPromptSlugs = ['global-text'];

      if (in_array($request->component, ['text-block'])) {
        $systemPromptSlugs[] = $request->component;
      }

      $systemPrompts = Template::query()->whereIn('slug', $systemPromptSlugs);
      $systemPrompt = $systemPrompts->pluck('prompt')->implode("\n");

      try {
        $systemPrompt = PromptHelper::replaceSiteVariables($systemPrompt);
      } catch (\Throwable $th) {
        throw new ControllerException([
          [
            'message' => 'An error occured when replacing site variables'
          ]
        ], 500);
      }
    }

    $request->templates = collect($request->templates);
    $slugs = $request->templates->pluck('slug')->toArray();
    $templates = Template::query()->whereIn('slug', $slugs)->get();

    $prompts = [];

    if ($request->prompt) {
      $prompts[] = $request->prompt;
    }

    foreach ($request->templates as $template) {
      $prompt = $templates->where('slug', $template['slug'])->first()->prompt;
      $option = $template['option'] ?? '';
      $prompts[] = str_replace('{{option}}', $option, $prompt);
    }

    if (count($prompts) == 0) {
      throw new ControllerException([
        [
          'message' => 'Templates or prompt must be provided'
        ]
      ], 400);
    }

    $userPrompt = implode("\n", $prompts);
    $userPrompt = str_replace('{{text}}', $request->text, $userPrompt);

    if ($request->post) {
      try {
        $userPrompt = PromptHelper::replaceVariables($userPrompt, $request->post);
      } catch (\Throwable $th) {
        throw new ControllerException([
          [
            'message' => 'An error occured when replacing variables in user prompt'
          ]
        ], 500);
      }
    }

    $model = AIModel::getByRequest($request, 'textModel');

    if (!PremiumHelper::isPremium()) {
      $premiumServices = [
        'googleai',
        'openrouter',
      ];

      if (in_array($model->service, $premiumServices)) {
        throw new ControllerException([
          [
            'message' => "You need to upgrade to premium to use {$model->service} service"
          ]
        ], 400);
      }
    }

    if (!isset(AIModel::$keyNames[$model->service])) {
      throw new ControllerException([
        [
          'message' => "Text model service {$model->service} is not supported"
        ]
      ], 400);
    }

    $apiKey = Settings::get(AIModel::$keyNames[$model->service]);

    if (!$apiKey) {
      throw new ApiKeyNotSetException("Your text model is {$model->service}@{$model->name} but {$model->service} API key is not set");
    }

    ResponseHelper::setMode('sse');
    ResponseHelper::prepareSSE();

    $isStreamAvailable = Settings::get('isStreamAvailable', null);

    if (!function_exists('openssl_encrypt')) {
      $isStreamAvailable = true;
    }

    if (!$isStreamAvailable) {
      try {
        $websocket = aimuse()->api()->stream();
        ResponseHelper::prepareWebsocket($websocket, $request->json('channel'));
        ResponseHelper::setMode('websocket');
        ResponseHelper::setSecretKey($request->secret);
      } catch (\Throwable $th) {
        Log::error('An error occured when connect to AI Muse stream server', [
          'error' => $th,
          'trace' => $th->getTrace(),
        ]);
      }
    }

    $callback = function ($event, $data) {
      return ResponseHelper::send($event, $data);
    };

    $messages = [];
    $datasetSlugs = [];

    if ($request->json('dataset')) {
      array_push($datasetSlugs, $request->json('dataset'));
    }

    foreach ($templates as $template) {
      if (!$template->dataset_slug) continue;

      // Skip the dataset that is already included
      if (in_array($template->dataset_slug, $datasetSlugs)) continue;

      array_push($datasetSlugs, $template->dataset_slug);
    }

    if (count($datasetSlugs) > 0) {
      $datasets = Dataset::with('conversations')->whereIn('slug', $datasetSlugs)->get();

      foreach ($datasets as $dataset) {
        foreach ($dataset->conversations as $conversation) {
          $messages[] = [
            'content' => $conversation->prompt,
            'role' => 'user',
          ];

          $messages[] = [
            'content' => $conversation->response,
            'role' => 'model',
          ];
        }
      }
    }

    $messages = array_merge($messages, $request->messages ?? []);

    $options = new GenerateTextOptions([
      'systemPrompt' => $systemPrompt,
      'userPrompt' => $userPrompt,
      'model' => $model,
      'component' => $request->component,
      'callback' => $callback,
      'session' => $request->session ?? "",
      'messages' => $messages,
      'contextLength' => $request->contextLength ?? 0,
    ]);

    if ($request->preview) {
      ResponseHelper::release();
      return new WP_REST_Response($options);
    }

    // We need to set the time limit to 0 to prevent the script from timing out when the stream is running
    set_time_limit(0);

    try {
      if ($model->service == 'openai') {
        $client = OpenAI::client($apiKey);
        $client->chat()->stream($options);
      } elseif ($model->service == 'googleai') {
        $client = GoogleAI::client($apiKey);
        $client->content()->stream($options);
      } elseif ($model->service == 'openrouter') {
        $client = OpenRouter::client($apiKey);
        $client->chat()->stream($options);
      }
    } catch (GenerateException $error) {
      ResponseHelper::release();
      throw $error;
    }
  }
}
