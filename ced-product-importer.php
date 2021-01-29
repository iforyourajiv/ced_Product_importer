<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cedcommerce.com/
 * @since             1.0.0
 * @package           Ced_Product_Importer
 *
 * @wordpress-plugin
 * Plugin Name:       Product Importer by CedCommerce
 * Plugin URI:        https://cedcommerce.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Cedcommerce
 * Author URI:        https://cedcommerce.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ced-product-importer
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
define( 'CED_PRODUCT_IMPORTER_VERSION', '1.0.0' );
define('PLUGIN_DIRPATH', plugin_dir_path(__FILE__));


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ced-product-importer-activator.php
 */
function activate_ced_product_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-product-importer-activator.php';
	Ced_Product_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ced-product-importer-deactivator.php
 */
function deactivate_ced_product_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ced-product-importer-deactivator.php';
	Ced_Product_Importer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ced_product_importer' );
register_deactivation_hook( __FILE__, 'deactivate_ced_product_importer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ced-product-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ced_product_importer() {

	$plugin = new Ced_Product_Importer();
	$plugin->run();

}
run_ced_product_importer();
