<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com/
 * @since      1.0.0
 *
 * @package    Ced_Product_Importer
 * @subpackage Ced_Product_Importer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ced_Product_Importer
 * @subpackage Ced_Product_Importer/admin
 * 
 */
class Ced_Product_Importer_Admin {



	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * 
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * 
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version) {
		global $hook_suffix;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		// ini_set('display_errors', 1);
		// ini_set('display_startup_errors', 1);
		// error_reporting(E_ALL);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ced_Product_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ced_Product_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ced-product-importer-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ced_Product_Importer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ced_Product_Importer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ced-product-importer-admin.js', array('jquery'), $this->version, false);
		wp_localize_script(
			$this->plugin_name,
			'ajax_fetch_file', //handle Name
			array('ajaxurl' => admin_url('admin-ajax.php'))
		);
	}
	/**
	 * Function: ced_product_importer
	 * Description : Creating New Menu Having a name 'Import Product' For Importing a File for creating a Product
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function ced_product_importer_page() {
		add_menu_page(
			'Product Importer', // Menu Title
			'Import Product', // Menu Name
			'manage_woocommerce', //Capabilities
			'import-product', //Slug
			'ced_import_product_html', // call backFunction
			'dashicons-cloud', //Icon
			30
		);

		function ced_import_product_html() {
			include PLUGIN_DIRPATH . '/admin/uploadfile.php';

			$getFile = get_option('uploaded_product_file', 1);
			?>
			<label> <b>Select File For Displaying a Product</b></label>
			<select name='fileSelection' id='fileSelection'>
				<option value="">Select a File</option>
				<?php
				foreach ($getFile as $filename) {
					?>
					<option value="<?php echo esc_html($filename); ?>"><?php echo esc_html($filename); ?></option>
				<?php
				}
				?>
			</select>
			<div id="displaydata"></div>
			<div id='loader' style='display: none;'>
				<h1>Processing.....</h1>
				<!-- <img src="loading.gif"> -->
			</div>
<?php

		}
	}


	/**
	 * Function :ced_ShowProductTable
	 * Description : Getting a File Name And Displaying a Product from that file Using WP-List-Table
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @var $obj 		//object 
	 * @var $upload     //Path of WordPress Default Upload Folder 
	 * @var	$upload_dir //Base Directory
	 * @var $getFileData //Getting All Data from Selected File
	 * @var $decodedFileData //Decoding Data From JSON to Array
	 * @return void
	 */
	public function ced_ShowProductTable() {
		require_once PLUGIN_DIRPATH . 'admin/class-showProduct-wp-list-table.php';
		$obj             = new  Ced_Product_List();
		$getFilename     = isset($_POST['filename'])?sanitize_text_field( $_POST['filename'] ):false;
		$upload          = wp_upload_dir();
		$upload_dir      = $upload['basedir'];
		$upload_dir      = $upload_dir . '/cedcommerce_product_file/' . $getFilename;
		$getFileData     = file_get_contents($upload_dir);
		$decodedFileData = json_decode($getFileData, true);
		$obj->items      = $decodedFileData;
		$obj->prepare_items();
		print_r($obj->display());
		wp_die();
	}

	/**
	 * Function :ced_create_simple_product
	 * Description : This Function is Creating a New Product For Simple Product and Parent Product for Varibale Product 
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param $data
	 * @var $user_id
	 * @var $post_id
	 * @return $post_id
	 */
	public function ced_create_simple_product( $data) {
		global $wpdb;
		//Checking if Product is Already Exist or Not
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data['item_sku']));
		if ($product_id) {
			echo 'Product Already Exist with This ID :-' . $product_id;
		} else {
			$user_id = get_current_user();
			$post_id = wp_insert_post(array(
				'post_author' => $user_id,
				'post_title' => $data['name'],
				'post_content' => $data['description'],
				'post_status' => 'publish',
				'post_type' => 'product',
			));

			if (1 == $data['has_variation']) {
				$productType = 'variable';
			} else {
				$productType = 'simple';
			}
			update_post_meta($post_id, '_sku', $data['item_sku']);
			wp_set_object_terms($post_id, $productType, 'product_type');
			return $post_id;
		}
	}



	/**
	 * Function : ced_create_product_meta
	 * Description :  Creating an All type of Meta which is required for created Post (product)
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param $post_id,$data
	 * @return true
	 */
	public function ced_create_product_meta( $post_id, $data) {
		update_post_meta($post_id, '_visibility', 'visible');
		update_post_meta($post_id, '_regular_price', $data['original_price']);
		update_post_meta($post_id, '_price', $data['price']);
		update_post_meta($post_id, '_manage_stock', 'yes');
		update_post_meta($post_id, '_backorders', 'no');
		update_post_meta($post_id, '_stock', $data['stock']);
		if (0 == $data['stock']) {
			update_post_meta($post_id, '_stock_status', 'outofstock');
		} else {
			update_post_meta($post_id, '_stock_status', 'instock');
		}
		wp_set_object_terms($post_id, 'clothing', 'product_cat');
		return true;
	}



	/**
	 * Function : ced_create_image_for_product
	 * Description :  Creating and Uploading a Image for Specific Post 
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param $post_id,$data
	 * @return true
	 */

	public function ced_create_image_for_product( $post_id, $data) {
		foreach ($data['images'] as $key => $value) {
			// Add Featured Image to Post
			$image_url        = $value; // Define the image URL here
			$image_name       = basename($value);
			$upload_dir       = wp_upload_dir(); // Set upload folder
			$image_data       = file_get_contents($image_url); // Get image data
			$unique_file_name = wp_unique_filename($upload_dir['path'], $image_name); // Generate unique name
			$filename         = basename($unique_file_name); // Create image file name

			if (wp_mkdir_p($upload_dir['path'])) {
				$file = $upload_dir['path'] . '/' . $filename;
			} else {
				$file = $upload_dir['basedir'] . '/' . $filename;
			}

			// Create the image  file on the server
			file_put_contents($file, $image_data);
			// Set attachment data
			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => sanitize_file_name($filename),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			// Create the attachment
			$attach_id = wp_insert_attachment($attachment, $file, $post_id);
			// Include image.php
			require_once ABSPATH . 'wp-admin/includes/image.php';
			// Define attachment metadata
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			// Assign metadata to attachment
			wp_update_attachment_metadata($attach_id, $attach_data);
			// And finally assign featured image to post
			set_post_thumbnail($post_id, $attach_id);
			return true;
		}
	}


	/**
	 * Function : ced_create_product_attributes
	 * Description :  Creating an Attributes for Specific Product
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param $post_id,$data
	 * @var $product_attributes 
	 * @var $i
	 * @var $varation_no
	 * @return true
	 */
	public function ced_create_product_attributes( $post_id, $data) {
		$i                  = 0;
		$product_attributes = array();
		foreach ($data['attributes'] as $key => $value) {
			if (1 == $data['has_variation']) {
				$varation_no = 1;
			} else {
				$varation_no = 0;
			}
			$product_attributes[sanitize_title($value['attribute_name'])] = array(
				'name' => wc_clean($value['attribute_name']), // set attribute name
				'value' => $value['attribute_value'], // set attribute value
				'position' => $i,
				'is_visible' => 1,
				'is_variation' => $varation_no,
				'is_taxonomy' => 0
			);
			$i++;
		}
		update_post_meta($post_id, '_product_attributes', $product_attributes);
		return true;
	}



	/**
	 * Function :ced_create_attribute_for_variation
	 * Description : Creating attributes For Assigning To Different Variable Products and their Variations
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @return $attribute
	 */
	public function ced_create_attribute_for_variation( $data) {
		foreach ($data as $key => $value) {
			$attribute = new WC_Product_Attribute();
			$attribute->set_id($key);
			$attribute->set_name($value['name']);
			$attribute->set_options($value['options']);
			$attribute->set_position(1);
			$attribute->set_visible(true);
			$attribute->set_variation(true);
		}
		return $attribute;
	}


	/**
	 * Function: ced_create_variation
	 * Description : Creating Variations for Specific Product
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @param  mixed $attributes
	 * @param  mixed $post_id
	 * @var $parent_id
	 * @var $attributeName
	 * @var $options
	 * @var $data_attributes
	 * @var	$variation
	 * @var $variation_id
	 * @return true
	 */
	public function ced_create_variation( $data, $attributes, $post_id) {
		$parent_id     = $post_id;
		$attributeName = $attributes->get_name();
		$options       = $attributes->get_options();
		$strOptions    = implode('|', $options);
		$data_attributes[sanitize_title($attributeName)] = array(
			'name' => wc_clean($attributeName), // set attribute name
			'value' => $strOptions, // set attribute value
			'is_visible' => true,
			'is_variation' => true,
			'is_taxonomy' => '0'
		);
		update_post_meta($parent_id, '_product_attributes', $data_attributes);

		foreach ($data as $key => $value) {

			$variation    = array(
				'post_title'   => $value['name'],
				'post_content' => $value['name'],
				'post_status'  => 'publish',
				'post_parent'  => $parent_id,
				'post_type'    => 'product_variation'
			);
			$variation_id = wp_insert_post($variation);
			update_post_meta($variation_id, '_sku', $value['variation_sku']);
			update_post_meta($variation_id, '_visibility', 'visible');
			update_post_meta($variation_id, '_regular_price', $value['original_price']);
			update_post_meta($variation_id, '_price', $value['price']);
			update_post_meta($variation_id, '_manage_stock', 'yes');
			update_post_meta($variation_id, '_backorders', 'no');
			update_post_meta($variation_id, '_stock', $value['stock']);
			foreach ($options as $option) {
				if ($value['name'] == $option) {
					update_post_meta($variation_id, 'attribute_' . strtolower($attributeName), $option);
				}
			}
			WC_Product_Variable::sync($parent_id);
		}
		return true;
	}

	/**
	 * Function : ced_product_import
	 * Description :  Importing a Product (Simple ,Variable) in DB Using Product SKU and Specific File 
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @var $id
	 * @var $filename
	 * @var $upload
	 * @var $upload_dir
	 * @var $getFileDataForImport
	 * @var $decodedFileDataForImport
	 * @var $post_id,$checkattr,$checkImage,$check
	 * @return void
	 */

	public function ced_product_import() {
		$id                       =isset($_POST['id'])?sanitize_text_field($_POST['id']):false ;
		$fileName                 = isset($_POST['filename'])? sanitize_text_field( $_POST['filename']):false;
		$upload                   = wp_upload_dir();
		$upload_dir               = $upload['basedir'];
		$upload_dir               = $upload_dir . '/cedcommerce_product_file/' . $fileName;
		$getFileDataForImport     = file_get_contents($upload_dir);
		$decodedFileDataForImport = json_decode($getFileDataForImport, true);
		foreach ($decodedFileDataForImport as $element) {
			if ($element['item']['item_sku'] == $id) {
				if (1 == $element['item']['has_variation']) {
					$post_id          = $this->ced_create_simple_product($element['item']);
					$checkProductMeta = $this->ced_create_product_meta($post_id, $element['item']);
					$uploadImage      = $this->ced_create_image_for_product($post_id, $element['item']);
					$attributes       = $this->ced_create_attribute_for_variation($element['tier_variation']);
					$check            = $this->ced_create_variation($element['item']['variations'], $attributes, $post_id);
					if ($check) {
						echo 'Product Imported Successfully';
					}
				} else {
					$post_id    = $this->ced_create_simple_product($element['item']);
					$check      = $this->ced_create_product_meta($post_id, $element['item']);
					$checkImage = $this->ced_create_image_for_product($post_id, $element['item']);
					if ($checkImage) {
						$checkattr = $this->ced_create_product_attributes($post_id, $element['item']);
						if ($checkattr) {
							echo 'Product Imported Successfully';
						}
					}
				}
			}
		}
		wp_die();
	}

	/**
	 * Function : ced_product_bulk_import
	 * Description :  Importing a Bulk Product (Simple ,Variable) in DB Using Product SKU and Specific File 
	 * Version:  1.0.0
	 *
	 * @since    1.0.0
	 * @var $id
	 * @var $filename
	 * @var $upload
	 * @var $upload_dir
	 * @var $getFileDataForImport
	 * @var $decodedFileDataForImport
	 * @var $post_id,$checkattr,$checkImage,$check
	 * @return void
	 */

	public function ced_product_bulk_import() {
		$bulkId                   = isset($_POST['dataForBulk'])?sanitize_text_field( $_POST['dataForBulk'] ):false;
		$fileName                 = isset($_POST['filename'])?sanitize_text_field($_POST['filename']):false;
		$upload                   = wp_upload_dir();
		$upload_dir               = $upload['basedir'];
		$upload_dir               = $upload_dir . '/cedcommerce_product_file/' . $fileName;
		$getFileDataForImport     = file_get_contents($upload_dir);
		$decodedFileDataForImport = json_decode($getFileDataForImport, true);
		foreach ($bulkId as $id) {
			foreach ($decodedFileDataForImport as $element) {
				if ($element['item']['item_sku'] == $id) {
					if (1 == $element['item']['has_variation']) {
						$post_id     = $this->ced_create_simple_product($element['item']);
						$uploadImage = $this->ced_create_image_for_product($post_id, $element['item']);
						$attributes  = $this->ced_create_attribute_for_variation($element['tier_variation']);
						$check       = $this->ced_create_variation($element['item']['variations'], $attributes, $post_id);
						if ($check) {
							echo 'Product Imported Successfully';
						}
					} else {
						$post_id    = $this->ced_create_simple_product($element['item']);
						$check      = $this->ced_create_product_meta($post_id, $element['item']);
						$checkImage = $this->ced_create_image_for_product($post_id, $element['item']);
						if ($checkImage) {
							$checkattr = $this->ced_create_product_attributes($post_id, $element['item']);
							if ($checkattr) {
								echo 'Product Imported Successfuly';
							}
						}
					}
				}
			}
		}
		wp_die();
	}
}
