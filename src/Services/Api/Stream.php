<?php

namespace AIMuse\Services\Api;

use AIMuse\Services\WebSocket;

class Stream
{
  private WebSocket\Client $websocket;
  public static string $url = 'wss://api.wpaimuse.com';

  public function __construct(string $token)
  {
    $url = static::$url;
    $options = [];
    $this->websocket = new WebSocket\Client("$url/stream?token=$token", $options);
    $this->websocket->receive();
  }

  public static function getUrl()
  {
    return static::$url;
  }

  public function publish($channel, $event)
  {
    $message = $this->message([
      'publish' => [
        'channel' => $channel,
        'event' => $event,
      ]
    ]);

    $this->websocket->text($message);
    $response = $this->websocket->receive();
    $response = json_decode($response);

    return $response;
  }

  public function close()
  {
    if (!$this->websocket->isConnected()) {
      return;
    }

    $this->websocket->close();
  }

  public function message(array $data)
  {
    return wp_json_encode($data);
  }
}
