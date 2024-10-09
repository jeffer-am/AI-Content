<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Models\Settings;
use WP_Post;

/**
 * Fires after all built-in meta boxes have been added.
 *
 * @link https://developer.wordpress.org/reference/hooks/add_meta_boxes/
 */
class SortOrderWCMetaBoxesAction extends Action
{
  public function __construct()
  {
    $this->name = 'add_meta_boxes';
  }

  public function handle(string $postType)
  {
    if ($postType === 'product' && class_exists('WooCommerce')) {
      $wcOrderOption = Settings::get('aiMuseWcMetaBoxesOrdered', false);
      if ($wcOrderOption) {
        return;
      }

      $currentMeta = get_user_meta(get_current_user_id(), 'meta-box-order_product', true);

      if (!$currentMeta) {
        return;
      }

      $sides = explode(',', $currentMeta['side']);

      if (count($sides) > 1) {
        $sides = array_filter($sides, function ($side) {
          return $side !== 'aimuse-wc-generate';
        });

        $sides = array_merge(
          array_slice($sides, 0, 1),
          ['aimuse-wc-generate'],
          array_slice($sides, 1)
        );

        $currentMeta['side'] = implode(',', $sides);

        $update = update_user_meta(
          get_current_user_id(),
          'meta-box-order_product',
          $currentMeta
        );

        if ($update) {
          Settings::set('aiMuseWcMetaBoxesOrdered', true);
        }
      }
    }
  }
}
