<?php

namespace AIMuse\Middleware;

/** @Annotation */
class Editor extends Middleware
{
  public function handle(): bool
  {
    if (!isset($_SERVER['HTTP_X_WP_NONCE']) || !is_string($_SERVER['HTTP_X_WP_NONCE'])) {
      return false;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])), 'wp_rest')) {
      return false;
    }

    if (!current_user_can('edit_pages')) {
      return false;
    }

    return true;
  }
}
