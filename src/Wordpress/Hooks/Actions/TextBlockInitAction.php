<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Models\Settings;
use WP_Block_Type_Registry;

/**
 * Action Hook: Fires after WordPress has finished loading but before any headers are sent.
 *
 * @link https://developer.wordpress.org/reference/hooks/enqueue_block_assets/
 */
class TextBlockInitAction extends Action
{
  private $blockName;

  public function __construct()
  {
    $this->name = 'init';
    $this->blockName = aimuse()->name() . '/text-block';
  }

  public function isRegistered()
  {
    if (!WP_Block_Type_Registry::get_instance()->is_registered($this->blockName)) {
      return false;
    }
    return true;
  }

  public function handle()
  {
    if (!current_user_can('edit_pages')) {
      return;
    }

    if ($this->isRegistered()) {
      return;
    }

    if (!function_exists('register_block_type')) {
      // Block editor is not available.
      return;
    }

    $isTextBlockActivated = Settings::get('isTextBlockActive', true);

    if (!$isTextBlockActivated) {
      return;
    }

    if ($this->isRegistered()) {
      return;
    }

    register_block_type($this->blockName, [
      'editor_script' => 'aimuse-text-block',
      'editor_style' => 'aimuse-text-block'
    ]);
  }
}
