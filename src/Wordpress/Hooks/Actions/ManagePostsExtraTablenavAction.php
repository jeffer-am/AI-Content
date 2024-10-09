<?php

namespace AIMuse\Wordpress\Hooks\Actions;

/**
 * Fires immediately following the closing “actions” div in the tablenav for the posts list table.
 *
 * @link https://developer.wordpress.org/reference/hooks/manage_posts_extra_tablenav/
 */
class ManagePostsExtraTablenavAction extends Action
{
  public function __construct()
  {
    $this->name = 'manage_posts_extra_tablenav';
  }

  public function handle()
  {
    if (!current_user_can('edit_pages')) {
      return;
    }

    echo '<div class="alignleft actions" data-muse-button="bulk-generate"></div>';
  }
}
