<?php

namespace AIMuse\Wordpress\Schedules;

class Schedule
{
  protected $name = null;
  protected $interval = 'daily';
  protected $timestamp = null;
  protected $immediate = false;

  public function __construct()
  {
    if (is_null($this->name)) {
      throw new \Exception('Name is required');
    }
  }

  public function init()
  {
    add_action($this->name, [$this, 'run']);
  }

  public function register()
  {
    if (!wp_next_scheduled($this->name)) {
      wp_schedule_event($this->timestamp ?? time(), $this->interval, $this->name);
    }

    if ($this->immediate) {
      $this->run();
    }
  }

  public function unregister()
  {
    wp_clear_scheduled_hook($this->name);
  }

  public function run()
  {
    throw new \Exception('Not implemented');
  }
}
