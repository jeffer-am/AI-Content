<?php

namespace AIMuse\Services\WebSocket;

trait OpcodeTrait
{
  private static $opcodes = [
    'continuation' => 0,
    'text'         => 1,
    'binary'       => 2,
    'close'        => 8,
    'ping'         => 9,
    'pong'         => 10,
  ];
}
