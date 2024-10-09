<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Wordpress\Hooks\Hook;

class Action extends Hook
{
  protected string $name;
  protected int $priority = 10;

  public function register()
  {
    add_action($this->name, [$this, 'handle'], $this->priority);
  }
}
