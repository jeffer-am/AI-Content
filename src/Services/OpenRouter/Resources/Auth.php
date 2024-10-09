<?php

namespace AIMuse\Services\OpenRouter\Resources;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenRouter\Responses\ModelsResponse;

class Auth
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function key()
  {
    $response = $this->transporter->get("auth/key", []);
    return $response;
  }
}
