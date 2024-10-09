<?php

namespace AIMuse\Services\GoogleAI\Resources;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\GoogleAI\Responses\ChatResponse;

class Chat
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function create(array $options, string $model)
  {
    $endpoint = "models/{$model}:generateMessage";
    $response = $this->transporter->post("$endpoint", $options);

    return ChatResponse::fromJson($response);
  }
}
