<?php

namespace AIMuse\Services\GoogleAI\Resources;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\GoogleAI\Responses\TextResponse;

class Text
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function create(array $options, string $model)
  {
    $endpoint = "models/{$model}:generateText";
    $response = $this->transporter->post("$endpoint", $options);

    return TextResponse::fromJson($response);
  }
}
