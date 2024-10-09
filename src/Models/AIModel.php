<?php

namespace AIMuse\Models;

use Exception;
use AIMuse\Database\Model;
use AIMuse\Controllers\Request;
use AIMuse\Exceptions\ControllerException;
use AIMuse\Exceptions\ModelSettingException;
use AIMuse\Models\Casts\Serialize;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class AIModel extends Model
{
  protected $table = 'aimuse_models';
  protected $guarded = [];
  protected $keyType = 'string';
  public $incrementing = false;

  public static array $keyNames = [
    'openai' => 'openAiApiKey',
    'googleai' => 'googleAiApiKey',
    'openrouter' => 'openRouterApiKey'
  ];

  public $timestamps = false;

  protected $casts = [
    'pricing' => Serialize::class,
    'settings' => Serialize::class,
    'defaults' => Serialize::class,
  ];

  public static function getByRequest(Request $request, string $settingKey)
  {
    $label = Settings::$labels[$settingKey] ?? $settingKey;
    $defaultModelId = Settings::get($settingKey, null);
    if ($request->has('model')) {
      $model = static::find($request->model['id']);

      if (!$model) {
        if ($request->model['id'] == $defaultModelId) {
          throw new ModelSettingException("Default {$label} is invalid. Model not found with ID {$defaultModelId}", $settingKey);
        } else {
          throw new ControllerException([
            [
              'message' => 'Invalid model selected'
            ]
          ], 400);
        }
      }

      if (isset($request->model['settings'])) {
        $model->settings = $request->model['settings'];
      }
    } else {
      if (!$defaultModelId) {
        throw new ModelSettingException("Default {$label} setting is not set", $settingKey);
      }

      $model = static::find($defaultModelId);

      if (!$model) {
        throw new ModelSettingException("Default {$label} is invalid. Model not found with ID {$defaultModelId}", $settingKey);
      }
    }

    return $model;
  }

  public static function export()
  {
    $models = static::all();
    $models = $models->map(function ($model) {
      if (!is_array($model->settings) || count($model->settings) == 0) {
        return null;
      }

      return [
        'name' => $model->name,
        'service' => $model->service,
        'settings' => $model->settings,
      ];
    })->filter(fn ($model) => $model !== null);

    return $models->toArray();
  }

  public static function import(array $data)
  {
    foreach ($data as $backup) {
      $model = static::query()->where('name', $backup['name'])->where('service', $backup['service'])->first();
      Log::info('Model importing', ['model' => $model, 'backup' => $backup]);
      if ($model) {
        $model->settings = $backup['settings'];
        $model->save();
      }
    }
  }

  public static function sync()
  {
    $models = aimuse()->api()->models();

    static::query()->where('custom', false)->delete();

    foreach ($models as $model) {
      $id = hash_hmac('sha1', $model['name'] . $model['service'] . $model['type'], 'aimuse-model-id');
      $id = substr($id, 0, 10);

      $model = static::updateOrCreate([
        'id' => $id,
      ], [
        'name' => $model['name'],
        'service' => $model['service'],
        'type' => $model['type'],
        'pricing' => $model['pricing'] ?? [],
        'defaults' => $model['defaults'] ?? [],
      ]);

      if ($model->name == 'gpt-3.5-turbo') {
        Settings::default('textModel', $model->id);
      } elseif ($model->name == 'dall-e-3') {
        Settings::default('imageModel', $model->id);
      }
    }

    return $models;
  }
}
