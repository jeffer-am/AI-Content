<?php

namespace AIMuse\Wordpress\Hooks\Filters;

use WP_Block_Editor_Context;

/**
 * Filters the allowed block types for all editor types.
 *
 * @link https://developer.wordpress.org/reference/hooks/allowed_block_types_all/
 */
class AllowedBlockTypesAllFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'allowed_block_types_all';
    $this->acceptedArgs = 2;
  }

  public function handle($allowedBlockTypes, WP_Block_Editor_Context $blockEditorContext)
  {
    if (!current_user_can('edit_pages')) {
      return $allowedBlockTypes;
    }

    aimuse()->define('add_admin_scripts', true);

    return $allowedBlockTypes;
  }
}
