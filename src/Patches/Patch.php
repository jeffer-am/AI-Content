<?php

namespace AIMuse\Patches;

class Patch
{
  public string $version;

  public function __construct()
  {
    if (!isset($this->version)) {
      throw new \Exception('Version not set');
    }
  }

  public function apply()
  {
    throw new \Exception('Not implemented');
  }

  public function applied(): bool
  {
    return version_compare(aimuse()->version(), $this->version, '>=');
  }
}
