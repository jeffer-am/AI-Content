<?php

namespace AIMuse\Wordpress\Hooks\Actions;

class DeactivatePluginAction extends Action
{
  public function __construct()
  {
    $this->name = 'deactivate_' . aimuse()->file();
  }

  public function handle()
  {
    wp_clear_scheduled_hook('aimuse_stream_check_event');
  }
}
