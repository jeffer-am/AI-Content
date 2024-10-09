<?php

namespace AIMuse\Services\OpenAI\Exceptions;

use AIMuse\Exceptions\ControllerException;

class ContextLengthExceededException extends ControllerException
{
  public function __construct(string $message)
  {
    parent::__construct([
      [
        'message' => $message,
        'links' => [
          [
            'label' => 'Change model settings',
            'url' => aimuse()->menu('#/settings/text-models')
          ]
        ]
      ]
    ], 400);
  }
}
