<?php

namespace AIMuse\Services\GoogleAI\Responses;

class ContentResponse
{
  public array $candidates;

  public function __construct(array $candidates)
  {
    $this->candidates = $candidates;
  }

  public static function fromJson(object $json): self
  {
    return new self($json->candidates);
  }
}
