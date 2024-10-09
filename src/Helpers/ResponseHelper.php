<?php

namespace AIMuse\Helpers;

use WP_REST_Response;
use AIMuse\Services\Api\Stream;

class ResponseHelper
{
  public static Stream $websocket;
  public static string $channel;
  public static string $mode = 'sse';
  private static string $secretKey = '';

  public static function prepareSSE()
  {
    ignore_user_abort(true);
    ob_start();
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);
  }

  public static function sendSSE($event, $data)
  {
    echo "event: " . esc_html($event) . "\n";
    echo 'data: ' . wp_json_encode(['message' => $data]) . "\n\n";
    if (ob_get_level() > 0) {
      @ob_flush();
    }
    flush();
  }

  public static function setSecretKey(string $secretKey)
  {
    if (strlen($secretKey) != 16) {
      throw new \Exception('Secret key must be 16 characters long');
    }

    self::$secretKey = $secretKey;
  }

  public static function release()
  {
    @ob_end_flush();
    header('Content-Type: application/json');
  }

  public static function sendWebsocket(string $event, $data)
  {
    if (is_array($data)) {
      $data = wp_json_encode($data);
    }

    $data = openssl_encrypt($data, 'aes-128-cbc', self::$secretKey, 0, self::$secretKey);

    return self::$websocket->publish(self::$channel, [
      'type' => $event,
      'data' => $data
    ]);
  }

  public static function prepareWebsocket(Stream $websocket, string $channel)
  {
    self::$websocket = $websocket;
    self::$channel = $channel;
  }

  public static function setMode(string $mode)
  {
    self::$mode = $mode;
  }

  public static function send($event, $data)
  {
    $action = 'continue';
    self::sendSSE($event, $data);

    if (self::$mode == 'sse') {
      if (connection_aborted()) {
        // $action = 'stop';
      }
    }

    if (self::$mode == 'websocket') {
      $response = self::sendWebsocket($event, $data);
      if ($response->code != 200) {
        $action = 'stop';
      }
    }

    if ($event == 'done') {
      self::exit();
    }

    return $action;
  }

  public static function exit()
  {
    if (self::$mode == 'websocket') {
      self::$websocket->close();
    }

    ob_end_flush();

    exit();
  }

  public static function error($data, int $status)
  {
    static::release();
    return new WP_REST_Response($data, $status);
  }
}
