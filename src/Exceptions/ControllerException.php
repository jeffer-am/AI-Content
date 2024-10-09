<?php

namespace AIMuse\Exceptions;

use Exception;

class ControllerException extends Exception
{
  private array $errors = [];

  public function __construct(array $errors, int $code)
  {
    $this->errors = $errors;
    $this->code = $code;
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public static function make(string $message, int $code)
  {
    return new static([
      [
        'message' => $message,
      ]
    ], $code);
  }
}
