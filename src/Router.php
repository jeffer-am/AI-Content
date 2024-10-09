<?php

namespace AIMuse;

use ReflectionClass;
use AIMuse\Attributes\Route;
use AIMuse\Attributes\Middleware;
use AIMuseVendor\Doctrine\Common\Annotations\AnnotationReader;

class Router
{
  private static $version = 'v1';
  private static $controllers = [];
  private static $middlewares = [];

  public static function addMiddleware($middleware)
  {
    self::$middlewares[$middleware] = new $middleware();
    return self::$middlewares[$middleware];
  }

  public static function addController($controller)
  {
    self::$controllers[$controller] = new $controller();
    return self::$controllers[$controller];
  }

  public static function getMiddleware($middleware)
  {
    if (isset(self::$middlewares[$middleware])) {
      return self::$middlewares[$middleware];
    }

    throw new \Exception(sprintf('Middleware %s not found', $middleware));
  }

  public static function registerRoutes()
  {
    $controllers = [];

    $directory = new \RecursiveDirectoryIterator(aimuse()->dir() . 'src/Controllers');
    $iterator = new \RecursiveIteratorIterator($directory);

    /**
     * @var \SplFileInfo $file
     */
    foreach ($iterator as $file) {
      if ($file->getFilename() == 'Controller.php' || !str_ends_with($file->getFilename(), 'Controller.php')) {
        continue;
      }

      $relativePath = ltrim($file->getPath(), aimuse()->dir() . 'src/');
      $relativePath = str_replace('/', '\\', $relativePath);

      if ($file->isFile()) {
        $controllers[] = 'AIMuse\\' . $relativePath . '\\' . rtrim($file->getFilename(), '.php');
      }
    }

    $reader = new AnnotationReader();

    foreach ($controllers as $controller) {
      $reflectionClass = new ReflectionClass($controller);

      $methods = $reflectionClass->getMethods();

      if (!is_subclass_of($controller, Controllers\Controller::class)) {
        throw new \Exception(sprintf('Controller %s must be a subclass of Controller', $controller));
      }

      foreach ($methods as $method) {
        $route = $reader->getMethodAnnotation(
          $method,
          Route::class
        );

        if (!$route) {
          continue;
        }

        if (!isset(self::$controllers[$controller])) {
          self::$controllers[$controller] = new $controller();
        }

        $annotations = $reader->getMethodAnnotations($method);
        foreach ($annotations as $middleware) {
          if ($middleware instanceof Middleware) {
            if (!isset(self::$middlewares[$middleware->value])) {
              self::$middlewares[$middleware->value] = new $middleware->value();
            }
            $route->middlewares[] = $middleware->value;
          }
        }

        foreach (self::$controllers[$controller]->middlewares as $middleware) {
          if (in_array($middleware, $route->middlewares)) {
            continue;
          }

          if (!isset(self::$middlewares[$middleware])) {
            self::$middlewares[$middleware] = new $middleware();
          }

          $route->middlewares[] = $middleware;
        }

        register_rest_route(
          aimuse()->name() . '/' . self::$version,
          $route->path,
          [
            [
              'methods' => $route->method,
              'callback' => array(self::$controllers[$controller], "_{$method->getName()}"),
              'permission_callback' => array($route, 'middleware'),
              'accept_json' => true,
              'args' => array(),
            ]
          ]
        );
      }
    }
  }
}
