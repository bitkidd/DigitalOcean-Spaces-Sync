<?php

/**
 * Fired during plugin activation
 *
 * @since      2.0.4
 *
 * @package    Dos
 * @subpackage Dos/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.4
 * @package    Dos
 * @subpackage Dos/includes
 * @author     Alessio Masucci <msc.alessio@gmail.com>
 */
class Dos_Activator {

    /**
     * Register required settings for the plugins
     *
     * @since    2.0.4
     */
    public static function activate() {

        register_setting('dos_settings', 'dos_key');
        register_setting('dos_settings', 'dos_secret');
        register_setting('dos_settings', 'dos_endpoint');
        register_setting('dos_settings', 'dos_container');
        register_setting('dos_settings', 'dos_storage_path');
        register_setting('dos_settings', 'dos_storage_file_only');
        register_setting('dos_settings', 'dos_storage_file_delete');
        register_setting('dos_settings', 'dos_filter');
        // register_setting('dos_settings', 'dos_debug');
        register_setting('dos_settings', 'upload_url_path');
        register_setting('dos_settings', 'upload_path');
	}

}
