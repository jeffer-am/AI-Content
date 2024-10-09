<?php

namespace AIMuse\Services\OpenRouter;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\OpenRouter\Resources\Auth;
use AIMuse\Services\OpenRouter\Resources\Chat;
use AIMuse\Services\OpenRouter\Resources\Models;

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

  public function auth()
  {
    return new Auth($this->transporter);
  }
}
