<?php

namespace AIMuse\Exceptions;

class ApiKeyNotSetException extends ControllerException
{
  public function __construct(string $message)
  {
    $error = [
      'message' => $message,
    ];

    if (current_user_can('manage_options')) {
      $error['links'] = [
        [
          'label' => 'Set API key',
          'url' => aimuse()->menu('#/settings')
        ]
      ];
    }

    parent::__construct([$error], 400);
  }
}
