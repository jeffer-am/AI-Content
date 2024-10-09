<?php

namespace AIMuse\Services\GoogleAI;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\GoogleAI\Transporters\HttpTransporter;

class GoogleAI
{
  public static function client(string $apiKey, Transporter $transporter = null)
  {
    if ($transporter === null) {
      $transporter = new HttpTransporter($apiKey);
    }

    return new Client($transporter);
  }
}
