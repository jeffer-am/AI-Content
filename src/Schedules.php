<?php

namespace AIMuse;

use AIMuse\Exceptions\ScheduleException;
use AIMuse\Wordpress\Schedules\Schedule;

class Schedules
{
  public static function register()
  {
    foreach (self::list() as $schedule) {
      $schedule = new $schedule();
      $schedule->register();
    }
  }

  public static function unregister()
  {
    foreach (self::list() as $schedule) {
      $schedule = new $schedule();
      $schedule->unregister();
    }
  }

  public static function run()
  {
    foreach (self::list() as $schedule) {
      $schedule = new $schedule();
      $schedule->run();
    }
  }

  public static function init()
  {
    foreach (self::list() as $schedule) {
      $schedule = new $schedule();
      $schedule->init();
    }
  }

  public static function list()
  {
    $schedules = [];

    $directory = new \RecursiveDirectoryIterator(aimuse()->dir() . 'src/Wordpress/Schedules');
    $iterator = new \RecursiveIteratorIterator($directory);

    /**
     * @var \SplFileInfo $file
     */
    foreach ($iterator as $file) {
      if ($file->getFilename() == 'Schedule.php') {
        continue;
      }

      if (
        !str_ends_with($file->getFilename(), 'Schedule.php')
      ) {
        continue;
      }

      $relativePath = ltrim($file->getPath(), aimuse()->dir() . 'src/');
      $relativePath = str_replace('/', '\\', $relativePath);

      if ($file->isFile()) {
        $schedules[] = 'AIMuse\\' . $relativePath . '\\' . rtrim($file->getFilename(), '.php');
      }
    }

    foreach ($schedules as $schedule) {
      if (!class_exists($schedule)) {
        throw new ScheduleException("Schedule class $schedule does not exist.");
      }

      if (!is_subclass_of($schedule, Schedule::class)) {
        throw new ScheduleException('Schedule must be a subclass of Schedule');
      }
    }

    return $schedules;
  }
}
