<?php

namespace AIMuse\Controllers;

use AIMuse\Contracts\Validator;
use AIMuseVendor\Symfony\Component\Validator\Constraint;
use WP_REST_Request;
use AIMuseVendor\Symfony\Component\Validator\Validation;
use AIMuseVendor\Symfony\Component\Validator\Constraints;
use AIMuseVendor\Symfony\Component\Validator\ConstraintViolationListInterface;

class Request
{
  public string $method;
  public string $path;
  public array $params;
  protected array $headers;
  protected array $post;
  protected array $query;
  protected array $files;
  protected array $json;

  public function __construct(WP_REST_Request $request)
  {
    $this->method = $request->get_method() ?? '';
    $this->path = $request->get_route() ?? '';
    $this->headers = $request->get_headers() ?? [];

    $this->post = $request->get_body_params() ?? [];
    $this->query = $request->get_query_params() ?? [];
    $this->files = $request->get_file_params() ?? [];
    $this->json = $request->get_json_params() ?? [];

    if (isset($this->query['rest_route'])) {
      unset($this->query['rest_route']);
    }

    if (isset($this->query['_locale'])) {
      unset($this->query['_locale']);
    }

    $this->params = $request->get_params();

    $this->parseHeaders();
  }

  public function param(string $name, $default = null)
  {
    if (isset($this->params[$name])) {
      return $this->params[$name];
    }

    return $default;
  }

  public function header(string $name, $default = null)
  {
    $header = null;

    if (isset($this->headers[$name])) {
      $header = $this->headers[$name];
    }

    if (isset($this->headers[strtolower($name)])) {
      $header = $this->headers[strtolower($name)];
    }

    if (is_array($header) && count($header) == 1) {
      return $header[0];
    }

    return $default;
  }

  private function parseHeaders()
  {
    $headers = [];
    foreach ($this->headers as $key => $value) {
      $key = str_replace('_', '-', $key);
      $headers[$key] = $value;
    }
    $this->headers = $headers;
  }

  public function merge(array $data, string $type = 'post')
  {
    $this->$type = array_merge($this->$type, $data);
  }

  public function except(array $keys): array
  {
    return array_diff_key($this->all(), array_flip($keys));
  }

  public function only(array $keys)
  {
    return array_intersect_key($this->all(), array_flip($keys));
  }

  public function delete(string $name)
  {
    unset($this->post[$name]);
    unset($this->query[$name]);
    unset($this->files[$name]);
    unset($this->json[$name]);
  }

  public function json(string $name = null, $default = null)
  {
    if (!$name) {
      return $this->json;
    }

    if (!isset($this->json[$name])) {
      return $default;
    }

    return $this->json[$name];
  }

  public function post(string $name, $default = null)
  {
    if (!isset($this->post[$name])) {
      return $default;
    }

    return $this->post[$name];
  }

  public function query(string $name, $default = null)
  {
    if (!isset($this->query[$name])) {
      return $default;
    }

    return $this->query[$name];
  }

  public function get(string $name, $default = null)
  {
    return $this->__get($name) ?? $default;
  }

  public function file(string $name, $default = null)
  {
    if (!isset($this->files[$name])) {
      return $default;
    }

    return $this->files[$name];
  }

  public function all()
  {
    return array_merge($this->post, $this->query, $this->files, $this->json);
  }

  public function has(string $name): bool
  {
    return array_key_exists($name, $this->post) || array_key_exists($name, $this->query) || array_key_exists($name, $this->files) || array_key_exists($name, $this->json);
  }

  /**
   * Validates the given array of rules against the request data.
   *
   * @param array|string|Validator $instance The rules array or the name of the validator class or instance.
   * @return ConstraintViolationListInterface The list of constraint violations.
   *
   * @see https://symfony.com/doc/current/reference/constraints.html for available constraints.
   */
  public function validate($instance): ConstraintViolationListInterface
  {
    if (is_array($instance)) {
      $validator = Validation::createValidator();
      $constraint = new Constraints\Collection($validator);
      return $validator->validate($this->all(), $constraint);
    } elseif (is_string($instance)) {
      $validator = aimuse()->make($instance);
      return $validator->validate($this->all());
    } elseif ($instance instanceof Validator) {
      return $instance->validate($this->all());
    } elseif ($instance instanceof Constraint) {
      $validator = Validation::createValidator();
      return $validator->validate($this->all(), $instance);
    } else {
      throw new \Exception("Invalid validator given.", 1);
    }
  }

  public function __get($name)
  {
    if (isset($this->post[$name])) {
      return $this->post[$name];
    }

    if (isset($this->query[$name])) {
      return $this->query[$name];
    }

    if (isset($this->json[$name])) {
      return $this->json[$name];
    }

    if (isset($this->files[$name])) {
      return $this->files[$name];
    }

    if (!isset($this->$name)) {
      return null;
    }

    return $this->$name;
  }

  public function __set($name, $value)
  {
    $this->$name = $value;
  }
}
