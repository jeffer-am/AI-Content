<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Helpers\PostHelper;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Models\Settings;
use AIMuse\Models\Template;
use AIMuse\Services\Api\Stream;

/**
 * Action Hook: Fires after block assets have been enqueued for the editing interface.
 *
 * @link https://developer.wordpress.org/reference/hooks/enqueue_block_editor_assets/
 */
class TextBlockAssetsAction extends Action
{
  public $js_deps;
  public $css_deps;

  public function __construct()
  {
    $this->name = 'enqueue_block_editor_assets';
    $this->priority = 9;
    $this->js_deps = array(
      'react',
      'react-dom',
      'wp-blocks',
      'wp-i18n',
      'wp-block-editor',
      'wp-components',
      'wp-compose',
      'wp-data',
      'wp-element',
      'wp-rich-text',
      'wp-hooks',
      'wp-api-fetch',
      'wp-url',
      'wp-edit-post',
      'wp-editor',
      'wp-widgets'
    );
  }

  public function handle()
  {

    if (!current_user_can('edit_pages')) {
      return;
    }

    if (!function_exists('wp_register_script') || !function_exists('wp_style_is') || !function_exists('wp_register_style') || !function_exists('wp_localize_script')) {
      return;
    }

    $this->css_deps = array_filter(
      $this->js_deps,
      function ($d) {
        return wp_style_is($d, 'registered');
      }
    );

    $postTypes = PostHelper::getPostTypes();
    $postType = $postTypes[array_search(get_post_type(), array_column($postTypes, 'name'))];

    wp_register_script('aimuse-text-block', aimuse()->url('assets/dist/blocks/blocks.js'), $this->js_deps, filemtime(aimuse()->dir() . 'assets/dist/blocks/blocks.js'));
    wp_localize_script('aimuse-text-block', 'WPAIMuseBlocks', array(
      'api_base' => get_rest_url(get_current_blog_id(), '/' . aimuse()->name() . '/v1'),
      'nonce' => wp_create_nonce('wp_rest'),
      'templates' => Template::query()->where('type', 'text')->where('enabled', true)->get(),
      'version' => aimuse()->version(),
      'app_name' => aimuse()->name(),
      'premium' => PremiumHelper::toArray(),
      'assets' => array(
        'logo' => aimuse()->url('public/assets/images/logo.png'),
        'icon' => aimuse()->url('public/assets/images/icon.png'),
        'icon_light' => aimuse()->url('public/assets/images/icon_light.png'),
        'icon_36' => aimuse()->url('public/assets/images/icon_36.png'),
        'icon_64' => aimuse()->url('public/assets/images/icon_64.png'),
        'icon_128' => aimuse()->url('public/assets/images/icon_128.png'),
      ),
      'post_types' => $postTypes,
      'post_type' => $postType,
      'screen' => aimuse()->has('screen') ? aimuse()->get('screen') : (object)[],
      'stream' => array(
        'available' => Settings::get('isStreamAvailable', false),
        'token' => Settings::get('apiToken', ''),
        'url' => Stream::getUrl(),
      )
    ));

    wp_localize_script('aimuse-text-block', 'WPAIMuseStream', array(
      'available' => Settings::get('isStreamAvailable', false),
      'token' => Settings::get('apiToken', ''),
      'url' => Stream::getUrl(),
    ));

    wp_register_style('aimuse-text-block', aimuse()->url('assets/dist/blocks/blocks.css'), [], filemtime(aimuse()->dir() . '/assets/dist/blocks/blocks.css'));

  }

}
