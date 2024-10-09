<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use WP_Site;

/**
 * Fires once a site has been deleted from the database.

 *
 * @link https://developer.wordpress.org/reference/hooks/wp_delete_site/
 */
class WpDeleteSiteAction extends Action
{
  public function __construct()
  {
    $this->name = "wp_delete_site";
  }

  public function handle(WP_Site $oldSite)
  {
    switch_to_blog($oldSite->blog_id);
    aimuse()->uninstall();
    restore_current_blog();
  }
}
