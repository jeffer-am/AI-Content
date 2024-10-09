<?php

namespace AIMuse\Wordpress\Hooks\Filters;

use AIMuse\App;

/**
 * Filter Hook: Filters the array of row meta for each plugin in the Plugins list table.
 *
 * @link https://developer.wordpress.org/reference/hooks/plugin_row_meta/
 */
class PluginRowMetaFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'plugin_row_meta';
    $this->acceptedArgs = 4;
  }

  public function handle(array $pluginMeta, string $pluginFile, array $pluginData): array
  {
    if (!current_user_can('edit_pages')) {
      return $pluginMeta;
    }

    if ($pluginFile == aimuse()->file()) {
      $pluginMeta[] = sprintf(
        '<a href="%s">%s</a>',
        esc_url(menu_page_url(aimuse()->name(), false) . '#/settings'),
        esc_html__('Settings', 'aimuse')
      );
    }

    return $pluginMeta;
  }
}
