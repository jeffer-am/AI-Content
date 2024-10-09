<?php

namespace AIMuse\Services\OpenAI\Exceptions;

use AIMuse\Exceptions\ControllerException;

class InvalidRequestException extends ControllerException
{
  public function __construct(string $message)
  {
    parent::__construct([
      [
        'message' => $message,
      ]
    ], 400);
  }
}
