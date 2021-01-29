<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    Ced_Product_Importer
 * @subpackage Ced_Product_Importer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ced_Product_Importer
 * @subpackage Ced_Product_Importer/includes
 * 
 */
class Ced_Product_Importer_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Creating New Folder in WooCommerce_uploads  Having a Name 'cedcommerce_product_file' When Plugin Will be Activated
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/cedcommerce_product_file';
		if (! is_dir($upload_dir)) {
		   mkdir($upload_dir, 755);
		}
	}

}
