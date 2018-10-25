<?php
/**
 * Plugin Name: DigitalOcean Spaces Sync
 * Plugin URI: https://github.com/keeross/DO-Spaces-Wordpress-Sync
 * Description: This WordPress plugin syncs your media library with DigitalOcean Spaces Container.
 * Version: 2.0.5
 * Author: keeross
 * Author URI: https://github.com/keeross
 * License: MIT
 * Text Domain: dos
 * Domain Path: /languages

 */

require plugin_dir_path( __FILE__ ) .  'dos_class.php';
require plugin_dir_path( __FILE__ ) .  'dos_class_filesystem.php';

load_plugin_textdomain('dos', false, dirname(plugin_basename(__FILE__)) . '/lang');

function dos_incompatibile($msg) {
  require_once ABSPATH . DIRECTORY_SEPARATOR . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php';
  deactivate_plugins(__FILE__);
  wp_die($msg);
}

if ( is_admin() && ( !defined('DOING_AJAX') || !DOING_AJAX ) ) {

  if ( version_compare(PHP_VERSION, '5.3.3', '<') ) {

    dos_incompatibile(
      __(
        'Plugin DigitalOcean Spaces Sync requires PHP 5.3.3 or higher. The plugin has now disabled itself.',
        'dos'
      )
    );

  } elseif ( !function_exists('curl_version')
    || !($curl = curl_version()) || empty($curl['version']) || empty($curl['features'])
    || version_compare($curl['version'], '7.16.2', '<')
  ) {

    dos_incompatibile(
      __('Plugin DigitalOcean Spaces Sync requires cURL 7.16.2+. The plugin has now disabled itself.', 'dos')
    );

  } elseif (!($curl['features'] & CURL_VERSION_SSL)) {

    dos_incompatibile(
      __(
        'Plugin DigitalOcean Spaces Sync requires that cURL is compiled with OpenSSL. The plugin has now disabled itself.',
        'dos'
      )
    );

  }

}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sw-spaces-activator.php
 */
function activate_dos() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/dos-class-activator.php';
  Dos_Activator::activate();
}

register_activation_hook( __FILE__, 'activate_dos' );

function run_dos() {
  $instance = new DOS();
  $instance->setup();
}

run_dos();
