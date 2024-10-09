<?php

namespace AIMuse\Services\WebSocket\Message;

class Close extends Message
{
  protected $opcode = 'close';
}
