<?php

namespace AIMuse\Services\GoogleAI\Responses;

class ChatResponse
{
  public array $candidates;
  public array $messages;

  public function __construct(array $candidates, array $messages)
  {
    $this->candidates = $candidates;
    $this->messages = $messages;
  }

  public static function fromJson(object $json): self
  {
    return new self($json->candidates, $json->messages);
  }
}
