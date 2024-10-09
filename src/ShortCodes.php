<?php

namespace AIMuse;

use AIMuse\Wordpress\ShortCodes\ShortCode;
use AIMuse\Exceptions\ShortCodeException;

class ShortCodes
{
  public static function register()
  {
    $shortCodes = [];

    $directory = new \RecursiveDirectoryIterator(aimuse()->dir() . 'src/Wordpress/ShortCodes');
    $iterator = new \RecursiveIteratorIterator($directory);

    /**
     * @var \SplFileInfo $file
     */
    foreach ($iterator as $file) {
      if (!str_ends_with($file->getFilename(), 'ShortCode.php') || $file->getFilename() === 'ShortCode.php') {
        continue;
      }

      $relativePath = ltrim($file->getPath(), aimuse()->dir() . 'src/');
      $relativePath = str_replace('/', '\\', $relativePath);

      if ($file->isFile()) {
        $shortCodes[] = 'AIMuse\\' . $relativePath . '\\' . rtrim($file->getFilename(), '.php');
      }
    }

    foreach ($shortCodes as $shortCode) {
      if (!class_exists($shortCode)) {
        throw new ShortCodeException("$shortCode shortcode class does not exist");
      }

      if (!is_subclass_of($shortCode, ShortCode::class)) {
        throw new ShortCodeException("$shortCode shortcode must be a subclass of ShortCode");
      }

      (new $shortCode())->register();
    }
  }
}
