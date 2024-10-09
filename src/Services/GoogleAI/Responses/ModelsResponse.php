<?php

namespace AIMuse\Services\GoogleAI\Responses;

class ModelsResponse
{
  public array $models;

  public function __construct(array $models)
  {
    $this->models = $models;
  }

  public static function fromJson(object $json): self
  {
    return new self($json->models);
  }
}
