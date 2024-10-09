<?php

namespace AIMuse\Attributes;

use AIMuse\Router;

/**
 * @Annotation
 */
class Route
{
  public $path;
  public $method;
  public $middlewares = [];

  public function middleware()
  {
    foreach ($this->middlewares as $middleware) {
      $check = Router::getMiddleware($middleware)->handle();
      if ($check !== true) {
        return false;
      }
    }

    return true;
  }
}
