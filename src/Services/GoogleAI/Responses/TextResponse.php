<?php

namespace AIMuse\Services\GoogleAI\Responses;

class TextResponse
{
  public array $candidates;
  public array $filters;
  public array $safetyFeedback;

  public function __construct(array $candidates, array $filters, array $safetyFeedback = [])
  {
    $this->candidates = $candidates;
    $this->filters = $filters;
    $this->safetyFeedback = $safetyFeedback;
  }

  public static function fromJson(object $json): self
  {
    return new self($json->candidates, $json->filters, $json->safetyFeedback);
  }
}
