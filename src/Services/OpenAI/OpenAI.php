<?php

namespace AIMuse\Services\OpenAI;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenAI\Transporters\HttpTransporter;

class OpenAI
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
