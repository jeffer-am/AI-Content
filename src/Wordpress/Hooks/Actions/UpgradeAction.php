<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use WP_Upgrader;

class UpgradeAction extends Action
{
  public function __construct()
  {
    $this->name = 'upgrader_process_complete';
  }

  public function handle(WP_Upgrader $upgrader)
  {
    if (!isset($upgrader->result['destination_name'])) {
      return;
    }

    $name = $upgrader->result['destination_name'];

    if ($name === aimuse()->name()) {
      aimuse()->install();
    }
  }
}
