<?php

namespace AIMuse\Controllers;

use WP_REST_Response;
use AIMuse\Models\Settings;
use AIMuse\Models\Template;
use AIMuse\Attributes\Route;
use AIMuse\Exceptions\ApiKeyNotSetException;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Middleware\AdminAuth;
use AIMuse\Models\AIModel;
use AIMuse\Validators\Validator;
use AIMuse\Services\OpenAI\OpenAI;
use AIMuse\Validators\GenerateImageValidator;
use Exception;
use AIMuseVendor\GuzzleHttp\Exception\ClientException;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class GenerateImageController extends Controller
{
  public array $middlewares = [
    AdminAuth::class,
  ];

  /**
   * @Route(path="/admin/generate/image", method="POST")
   */
  public function image(Request $request)
  {
    $violations = $request->validate(GenerateImageValidator::class);

    if ($violations->count() > 0) {
      throw new ControllerException(Validator::toArray($violations), 400);
    }

    $request->templates = collect($request->templates);
    $slugs = $request->templates->pluck('slug')->toArray();
    $templates = Template::query()->whereIn('slug', $slugs)->get();

    $prompts = [];

    foreach ($request->templates as $template) {
      $prompt = $templates->where('slug', $template['slug'])->first()->prompt;
      $option = $template['option'] ?? '';
      $prompts[] = str_replace('{{option}}', $option, $prompt);
    }

    if ($request->prompt) {
      $prompts[] = $request->prompt;
    }

    if (count($prompts) == 0) {
      throw new ControllerException([
        [
          'message' => 'Templates or prompt must be provided',
        ]
      ], 400);
    }

    $model = AIModel::getByRequest($request, 'imageModel');

    $apiKey = Settings::get(AIModel::$keyNames[$model->service]);

    if (!$apiKey) {
      throw new ApiKeyNotSetException("Your image model is {$model->service}@{$model->name} but {$model->service} API key is not set");
    }

    $client = OpenAI::client($apiKey);

    // for now we only have openai service. later we will separate functions according to service.
    $options = [
      'prompt' => implode(", ", $prompts),
      'model' => $model->name,
    ];

    if (is_array($model->settings)) {
      foreach ($model->settings as $key => $value) {
        $options[$key] = $value;
      }
    }

    try {
      return $client->image()->create($options, $request->component);
    } catch (ClientException $e) {
      $body = $e->getResponse()->getBody()->getContents();
      $body = json_decode($body);
      throw new ControllerException([
        [
          'message' => "{$model->service} API error: {$body->error->message}",
        ],
      ], 400);
    }
  }
}
