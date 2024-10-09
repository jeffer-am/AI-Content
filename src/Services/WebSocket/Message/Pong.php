<?php

namespace AIMuse\Services\WebSocket\Message;

class Pong extends Message
{
  protected $opcode = 'pong';
}
