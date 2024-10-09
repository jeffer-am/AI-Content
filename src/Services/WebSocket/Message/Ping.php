<?php

namespace AIMuse\Services\WebSocket\Message;

class Ping extends Message
{
  protected $opcode = 'ping';
}
