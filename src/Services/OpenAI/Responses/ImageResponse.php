<?php

namespace AIMuse\Services\OpenAI\Responses;

class ImageResponse
{
  public string $created;
  public array $data;

  public function __construct(string $created, array $data)
  {
    $this->created = $created;
    $this->data = $data;
  }

  public static function fromJson(object $json): self
  {
    return new self(
      $json->created,
      $json->data,
    );
  }
}
