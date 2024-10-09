<?php

namespace AIMuse\Exceptions;

class GenerateException extends ControllerException
{
  public function __construct($message = 'An error occured when generating content', $code = 500)
  {
    parent::__construct([
      [
        'message' => $message
      ]
    ], $code);
  }
}
