<?php

namespace AIMuse\Services\OpenRouter;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenRouter\Transporters\HttpTransporter;

class OpenRouter
{
  private function __construct()
  {
  }

  public static function client(string $apiKey, Transporter $transporter = null)
  {
    if ($transporter === null) {
      $transporter = new HttpTransporter($apiKey);
    }
    return new Client($transporter);
  }
}
