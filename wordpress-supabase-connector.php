<?php

/**
 * Plugin WordPress pour se connecter à une base de données Supabase
 *
 * @link              https://github.com/leyscore/wordpress-supabase-connector
 * @since             1.0.0
 * @package           Wordpress_Supabase_Connector
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Supabase Connector
 * Plugin URI:        https://github.com/leyscore/wordpress-supabase-connector
 * Description:       Plugin WordPress pour se connecter à une base de données Supabase
 * Version:           1.0.0
 * Author:            Etienne Baurice
 * Author URI:        https://github.com/leyscore
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wordpress-supabase-connector
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WORDPRESS_SUPABASE_CONNECTOR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wordpress-supabase-connector-activator.php
 */
function activate_wordpress_supabase_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-supabase-connector-activator.php';
	Wordpress_Supabase_Connector_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wordpress-supabase-connector-deactivator.php
 */
function deactivate_wordpress_supabase_connector() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-supabase-connector-deactivator.php';
	Wordpress_Supabase_Connector_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wordpress_supabase_connector' );
register_deactivation_hook( __FILE__, 'deactivate_wordpress_supabase_connector' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-supabase-connector.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wordpress_supabase_connector() {

	$plugin = new Wordpress_Supabase_Connector();
	$plugin->run();

}
run_wordpress_supabase_connector();
