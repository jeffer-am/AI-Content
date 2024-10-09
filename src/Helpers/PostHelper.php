<?php

namespace AIMuse\Helpers;

class PostHelper
{
  public static function getPostTypes()
  {
    $excluded = array('attachment', 'revision', 'nav_menu_item');
    $postTypes = get_post_types([
      'public' => true,
      'show_ui' => true,
    ], 'objects');

    $types = [];
    $order = 10;

    foreach ($postTypes as $postType) {
      if (in_array($postType->name, $excluded)) {
        continue;
      }

      $data = [
        'name' => $postType->name,
        'label' => $postType->label,
        'singularLabel' => $postType->labels->singular_name ?? $postType->label,
        'dashicon' => $postType->menu_icon ?? 'dashicons-admin-post',
      ];
      $data['isWooCommerce'] = $postType->name === 'product' && class_exists('WooCommerce');
      $data['isPremium'] = !in_array($postType->name, PremiumHelper::$freePostTypes);

      if ($postType->name === 'post') {
        $data['order'] = 0;
      } elseif ($postType->name === 'page') {
        $data['order'] = 1;
      } elseif ($data['isWooCommerce']) {
        $data['order'] = 2;
      } else {
        $data['order'] = $order++;
      }

      $data['supports'] = [];

      foreach (['title', 'thumbnail', 'excerpt'] as $key) {
        $data['supports'][$key] = post_type_supports($postType->name, $key);
      }

      $data['taxonomies'] = get_object_taxonomies($postType->name);

      $types[] = $data;
    }

    usort($types, function ($a, $b) {
      return $a['order'] <=> $b['order'];
    });

    return $types;
  }
}
