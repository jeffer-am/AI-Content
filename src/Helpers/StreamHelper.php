<?php

namespace AIMuse\Helpers;

class StreamHelper
{
  public static function readChar(string $text, $stream)
  {
    while (!$stream->eof()) {
      $content = $stream->read(1);
      if ($content == $text) {
        return $content;
      }
    }
  }

  public static function readJson($stream)
  {
    $content = self::readChar('{', $stream);
    while (!$stream->eof()) {
      $content .= $stream->read(1);
      $json = json_decode($content, true);
      if ($json) {
        return $json;
      }
    }
  }
}
