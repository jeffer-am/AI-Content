<?php

namespace AIMuse\Wordpress\ShortCodes;

use AIMuse\Wordpress\ShortCodes\ShortCode;

class ChatShortCode extends ShortCode
{
  protected string $name = 'chat';
  protected array $scripts = [];

  public function show(array $attributes = [])
  {
    return "<div id='chat'></div>";
  }
}
