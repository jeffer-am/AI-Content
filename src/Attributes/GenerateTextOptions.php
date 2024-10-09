<?php

namespace AIMuse\Attributes;

use Closure;
use AIMuse\Models\AIModel;

class GenerateTextOptions
{
  public string $systemPrompt;
  public string $userPrompt;
  public AIModel $model;
  public string $component;
  public string $session;
  public Closure $callback;
  public array $messages = [];
  public int $contextLength = 0;

  public function __construct(array $options)
  {
    foreach ($options as $key => $value) {
      $this->{$key} = $value;
    }
  }

  public function __set($name, $value)
  {
    $this->{$name} = $value;
  }

  public function __get($name)
  {
    return $this->{$name};
  }

  public function callback($event, $data)
  {
    return call_user_func($this->callback, $event, $data);
  }
}
