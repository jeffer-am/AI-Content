<?php

namespace AIMuse\Middleware;

class Middleware
{
  public function handle(): bool
  {
    throw new \Exception('Middleware not implemented');
  }
}
