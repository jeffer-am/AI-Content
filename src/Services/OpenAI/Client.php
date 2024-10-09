<?php

namespace AIMuse\Services\OpenAI;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenAI\Resources\Chat;
use AIMuse\Services\OpenAI\Resources\Image;
use AIMuse\Services\OpenAI\Resources\Models;

class Client
{
  private Transporter $transporter;

  public function __construct(Transporter $transporter)
  {
    $this->transporter = $transporter;
  }

  public function chat()
  {
    return new Chat($this->transporter);
  }

  public function models()
  {
    return new Models($this->transporter);
  }

  public function image()
  {
    return new Image($this->transporter);
  }
}
