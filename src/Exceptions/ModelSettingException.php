<?php

namespace AIMuse\Exceptions;

use AIMuse\Models\Settings;

class ModelSettingException extends ControllerException
{
  public function __construct(string $message, string $settingKey)
  {
    $type = $settingKey == 'textModel' ? 'text' : 'image';
    $label = Settings::$labels[$settingKey] ?? $settingKey;

    $error = [
      'message' => $message,
    ];

    if (current_user_can('manage_options')) {
      $error['links'] =  [
        [
          'label' => "Set Default {$label}",
          'url' => aimuse()->menu("#/settings/{$type}-models")
        ]
      ];
    }

    parent::__construct([
      $error
    ], 400);
  }
}
