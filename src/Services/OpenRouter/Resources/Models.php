<?php

namespace AIMuse\Services\OpenRouter\Resources;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenRouter\Responses\ModelsResponse;

class Models
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function get($options = [])
  {
    $response = $this->transporter->get("models", $options);
    return ModelsResponse::fromJson($response);
  }
}
