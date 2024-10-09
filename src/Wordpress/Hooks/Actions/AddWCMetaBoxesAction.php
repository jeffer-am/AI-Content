<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use WP_Post;

/**
 * Fires after all built-in meta boxes have been added.
 *
 * @link https://developer.wordpress.org/reference/hooks/add_meta_boxes/
 */
class AddWCMetaBoxesAction extends Action
{
  public function __construct()
  {
    $this->name = 'add_meta_boxes';
  }

  public function handle(string $postType)
  {
    if (!current_user_can('edit_pages')) {
      return $postType;
    }

    if ($postType === 'product' && class_exists('WooCommerce')) {
      add_meta_box('aimuse-wc-generate', __('AI Muse', 'aimuse'), [$this, 'renderWoocommerceMetaBox'], 'product', 'side', 'low');
    }
  }

  public function renderWoocommerceMetaBox(WP_Post $post)
  {
    echo '<div id="aimuse-wc-generate-metabox"><center>Loading...</center></div>';
  }
}
