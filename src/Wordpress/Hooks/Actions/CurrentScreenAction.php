<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Models\Settings;
use WP_Screen;

/**
 * Fires after the current screen has been set.
 *
 * @link https://developer.wordpress.org/reference/hooks/current_screen/
 */
class CurrentScreenAction extends Action
{
  public function __construct()
  {
    $this->name = 'current_screen';
  }

  public function handle(WP_Screen $screen)
  {
    $screens = [
      $screen->id == 'toplevel_page_' . aimuse()->name() && !aimuse()->freemius()->is_activation_mode(),
      $screen->base == 'edit' && $screen->action == '',
      !Settings::get('isStreamChecked', false),
      $screen->id == "product" && $screen->base == "post" && ($screen->action == "" || $screen->action == "add")
    ];

    aimuse()->define('screen', $screen);

    if (!$screen) {
      return;
    }

    if (!current_user_can('edit_pages')) {
      return;
    }

    if (in_array(true, $screens)) {
      aimuse()->define('add_admin_scripts', true);
    }
  }
}
