<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Helpers\PostHelper;
use AIMuse\Helpers\PremiumHelper;
use AIMuse\Models\Settings;
use AIMuse\Services\Api\Stream;
use WP_Scripts;

/**
 * Action Hook: Fires when enqueuing scripts for all admin pages.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
 */
class AdminEnqueueScriptsAction extends Action
{
  public function __construct()
  {
    $this->name = 'admin_enqueue_scripts';
  }

  public function handle(string $context)
  {

    if (!aimuse()->has('add_admin_scripts')) {
      return;
    }

    wp_register_style(aimuse()->name() . '-admin', aimuse()->url('assets/css/admin.css'), [], aimuse()->version());
    wp_enqueue_style(aimuse()->name() . '-admin');


    wp_localize_script(
      aimuse()->name() . '-main',
      'WPAIMuseStream',
      array(
        'available' => Settings::get('isStreamAvailable', false),
        'token' => Settings::get('apiToken', ''),
        'url' => Stream::getUrl(),
      )
    );

    if (!function_exists('wp_style_is')) {
      return;
    }

    wp_register_script('popperjs', aimuse()->url('assets/plugins/popperjs/popper.min.js'), [], '2.11.8', true);
    wp_register_script('tippy', aimuse()->url('assets/plugins/tippy/tippy-bundle.umd.min.js'), ['popperjs'], '6.3.7', true);
    wp_enqueue_script('tippy');


    $jsDeps = array('wp-components', 'wp-element', 'wp-api-fetch', 'wp-url', 'wp-blob', 'wp-i18n', 'moment', 'lodash', 'tippy');

    $css_deps = array_filter(
      $jsDeps,
      function ($d) {
        return wp_style_is($d, 'registered');
      }
    );

    foreach ($css_deps as $dep) {
      wp_enqueue_style($dep);
    }

    wp_register_script(aimuse()->name() . '-main', aimuse()->url('assets/dist/admin/main.js'), $jsDeps, aimuse()->version(), true);
    wp_register_style(aimuse()->name() . '-main', aimuse()->url('assets/dist/admin/style.css'), null, aimuse()->version(), 'all');

    wp_enqueue_script(aimuse()->name() . '-main');
    wp_enqueue_style(aimuse()->name() . '-main');


    $userLocale = get_user_locale();

    $postTypes = PostHelper::getPostTypes();
    $postType = $postTypes[array_search(get_post_type(), array_column($postTypes, 'name'))];
    $blogId = get_current_blog_id();
    $userId = get_current_user_id();

    wp_localize_script(
      aimuse()->name() . '-main',
      'WPAIMuse',
      array(
        'api_base' => get_rest_url($blogId, '/' . aimuse()->name() . '/v1'),
        'wp_base' => get_rest_url($blogId, '/wp/v2'),
        'public_api_base' => 'https://api.wpaimuse.com',
        'version' => aimuse()->version(),
        'nonce' => wp_create_nonce('wp_rest'),
        'app_name' => aimuse()->name(),
        'assets' => array(
          'logo' => aimuse()->url('public/assets/images/logo.png'),
          'icon' => aimuse()->url('public/assets/images/icon.png'),
          'icon_light' => aimuse()->url('public/assets/images/icon_light.png'),
          'icon_36' => aimuse()->url('public/assets/images/icon_36.png'),
          'icon_64' => aimuse()->url('public/assets/images/icon_64.png'),
          'icon_128' => aimuse()->url('public/assets/images/icon_128.png'),
          'avatar' => get_avatar_url($userId)
        ),
        'max_upload_size' => wp_max_upload_size(),
        'premium' => PremiumHelper::toArray(),
        'locale' => $userLocale,
        'post_types' => $postTypes,
        'post_type' => $postType,
        'screen' => aimuse()->has('screen') ? aimuse()->get('screen') : (object)[],
        'is_stream_checked' => Settings::get('isStreamChecked', false),
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
      )
    );

    wp_localize_script(
      aimuse()->name() . '-main',
      'WPAIMuseStream',
      array(
        'available' => Settings::get('isStreamAvailable', false),
        'token' => Settings::get('apiToken', ''),
        'url' => Stream::getUrl(),
      )
    );
  }


}
