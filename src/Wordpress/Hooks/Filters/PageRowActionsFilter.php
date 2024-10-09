<?php

namespace AIMuse\Wordpress\Hooks\Filters;

use WP_Post;

/**
 * Filters the array of row action links on the Pages list table.
 *
 * @link https://developer.wordpress.org/reference/hooks/page_row_actions/
 */
class PageRowActionsFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'page_row_actions';
    $this->acceptedArgs = 2;
  }

  public function handle(array $actions, WP_Post $post): array
  {
    if (!current_user_can('edit_pages')) {
      return $actions;
    }

    $actions['aimuse'] = '';

    return $actions;
  }
}
