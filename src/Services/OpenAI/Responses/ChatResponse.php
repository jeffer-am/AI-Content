<?php

namespace AIMuse\Services\OpenAI\Responses;

class ChatResponse
{
  public string $id;
  public string $object;
  public int $created;
  public string $model;
  public array $choices;
  public object $usage;

  public function __construct(string $id, string $object, int $created, string $model, array $choices, object $usage)
  {
    $this->id = $id;
    $this->object = $object;
    $this->created = $created;
    $this->model = $model;
    $this->choices = $choices;
    $this->usage = $usage;
  }

  public static function fromJson(object $json): self
  {
    return new self(
      $json->id,
      $json->object,
      $json->created,
      $json->model,
      $json->choices,
      $json->usage
    );
  }
}
