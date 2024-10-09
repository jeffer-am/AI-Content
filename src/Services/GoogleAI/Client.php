<?php

namespace AIMuse\Services\GoogleAI;

use AIMuse\Contracts\Transporter;
use AIMuse\Services\GoogleAI\Resources\Chat;
use AIMuse\Services\GoogleAI\Resources\Text;
use AIMuse\Services\GoogleAI\Resources\Models;
use AIMuse\Services\GoogleAI\Resources\Content;

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

  public function text()
  {
    return new Text($this->transporter);
  }

  public function models()
  {
    return new Models($this->transporter);
  }

  public function content()
  {
    return new Content($this->transporter);
  }
}
