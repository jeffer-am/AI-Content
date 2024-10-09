<?php

namespace AIMuse\Wordpress\Hooks\Actions;

/**
 * Action Hook: Fires as an admin screen or script is being initialized.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_init/
 */
class AdminInitAction extends Action
{
  public function __construct()
  {
    $this->name = 'admin_init';
  }

  public function handle()
  {
    if (!aimuse()->installed()) {
      aimuse()->install();
    }
  }
}
