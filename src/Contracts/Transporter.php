<?php

namespace AIMuse\Contracts;

use AIMuseVendor\Psr\Http\Message\StreamInterface;

interface Transporter
{
  public function get(string $endpoint);

  public function post(string $path, array $body);

  public function stream(string $path, array $body): StreamInterface;
}
