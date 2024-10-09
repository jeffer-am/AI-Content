<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpaimuse.com
 * @since             1.0.0
 * @package           AIMuse
 *
 * @wordpress-plugin
 * Plugin Name:       AI Muse
 * Plugin URI:        https://wpaimuse.com
 * Description:       AI Content Generation with over 100+ models from OpenAI, Google AI and OpenRouter. Generate AI Content and AI Images with one click!
 * Version:           1.2.3
 * Author:            AI Muse
 * Author URI:        https://wpaimuse.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aimuse
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

define('AI_MUSE_NAME', 'aimuse');
define('AI_MUSE_VERSION', '1.2.3');
define('AI_MUSE_PREFIX', 'aimuse');
define('AI_MUSE_DIR', plugin_dir_path(__FILE__));
define('AI_MUSE_FILE', plugin_basename(__FILE__));
define('AI_MUSE_URL', plugin_dir_url(__FILE__));

if (defined('FQDB')) {
  add_action('admin_notices', 'show_admin_notice');
  function show_admin_notice()
  {
    wp_admin_notice('The AI Muse plugin is not available on WordPress Playground.', [
      'type' => 'error',
    ]);
  }
} else {
  require_once AI_MUSE_DIR . 'vendor/autoload.php';

  $aimuse = AIMuse\App::getInstance();
  $aimuse->register();

  function aimuse()
  {
    return AIMuse\App::getInstance();
  }

  if (!defined('WP_UNINSTALL_PLUGIN')) {
    $aimuse->run();
  }
}
