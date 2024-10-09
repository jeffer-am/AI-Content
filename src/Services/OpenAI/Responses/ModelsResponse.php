<?php

namespace AIMuse\Services\OpenAI\Responses;

class ModelsResponse
{
  public array $data;

  public function __construct(array $data)
  {
    $this->data = $data;
  }

  public static function fromJson(object $json): ModelsResponse
  {
    return new ModelsResponse($json->data);
  }
}
