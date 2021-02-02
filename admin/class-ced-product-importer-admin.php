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
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('verify-ajax-call'),
			)
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
			<div id='loader' style='display: none;'>
				<h1>Processing.....</h1>
			</div>
			<div id="displaydata"></div>

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
		$obj = new  Ced_Product_List();
		if (check_ajax_referer('verify-ajax-call', 'nonce')) {
			$getFilename     = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : false;
			$upload          = wp_upload_dir();
			$upload_dir      = $upload['basedir'];
			$upload_dir      = $upload_dir . '/cedcommerce_product_file/' . $getFilename;
			$getFileData     = file_get_contents($upload_dir);
			$decodedFileData = json_decode($getFileData, true);
			$obj->items      = $decodedFileData;
			$obj->prepare_items();
			print_r($obj->display());
		}
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
			echo 'failed';
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

		if (check_ajax_referer('verify-ajax-call', 'nonce')) {
			$id                       = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : false;
			$fileName                 = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : false;
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
							echo 'success';
						} else {
							echo 'failed';
						}
					} else {
						$post_id    = $this->ced_create_simple_product($element['item']);
						$check      = $this->ced_create_product_meta($post_id, $element['item']);
						$checkImage = $this->ced_create_image_for_product($post_id, $element['item']);
						if ($checkImage) {
							$checkattr = $this->ced_create_product_attributes($post_id, $element['item']);
							if ($checkattr) {
								echo 'success';
							} else {
								echo 'failed';
							}
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
		if (check_ajax_referer('verify-ajax-call', 'nonce')) {
			$bulkId                   = $_POST['dataForBulk'];
			$fileName                 = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : false;
			$upload                   = wp_upload_dir();
			$upload_dir               = $upload['basedir'];
			$upload_dir               = $upload_dir . '/cedcommerce_product_file/' . $fileName;
			$getFileDataForImport     = file_get_contents($upload_dir);
			$decodedFileDataForImport = json_decode($getFileDataForImport, true);
			$checkBulkVariation       = false;
			$checkBulkSimple          = false;
			foreach ($bulkId as $id) {
				foreach ($decodedFileDataForImport as $element) {
					if ($element['item']['item_sku'] == $id) {
						if (1 == $element['item']['has_variation']) {
							$post_id          = $this->ced_create_simple_product($element['item']);
							$checkProductMeta = $this->ced_create_product_meta($post_id, $element['item']);
							$uploadImage      = $this->ced_create_image_for_product($post_id, $element['item']);
							$attributes       = $this->ced_create_attribute_for_variation($element['tier_variation']);
							$check            = $this->ced_create_variation($element['item']['variations'], $attributes, $post_id);
							if ($check) {
								$checkBulkVariation = true;
							} else {
								$checkBulkVariation = false;
							}
						} else {
							$post_id    = $this->ced_create_simple_product($element['item']);
							$check      = $this->ced_create_product_meta($post_id, $element['item']);
							$checkImage = $this->ced_create_image_for_product($post_id, $element['item']);
							if ($checkImage) {
								$checkattr = $this->ced_create_product_attributes($post_id, $element['item']);
								if ($checkattr) {
									$checkBulkSimple = true;
								} else {
									$checkBulkSimple = false;
								}
							}
						}
					}
				}
			}
			if ($checkBulkVariation) {
				echo 'success';
			}
			if ($checkBulkSimple) {
				echo 'success';
			}
		}
		wp_die();
	}

	/**
	 * Function: ced_order_importer
	 * Description : Creating New Menu Having a name 'Import Order' For Importing a File for creating a Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function ced_Order_importer_page() {
		add_menu_page(
			'Order Importer', // Menu Title
			'Import Orders', // Menu Name
			'manage_woocommerce', //Capabilities
			'import-order', //Slug
			'ced_import_order_html', // call backFunction
			'dashicons-upload', //Icon
			30
		);

		function ced_import_order_html() {
			include PLUGIN_DIRPATH . '/admin/upload-order.php';
			$getFile = get_option('uploaded_order_file', 1);
			?>
			<label> <b>Select File For Creating A order</b></label>
			<select name='fileSelection_for_order' id='fileSelection_for_order'>
				<option value="">Select a File</option>
				<?php
				foreach ($getFile as $filename) {
					?>
					<option value="<?php echo esc_html($filename); ?>"><?php echo esc_html($filename); ?></option>
				<?php
				}
				?>
			</select>
			<button class="button button-primary button-next" id="ced_create_order">Create Order</button>
			<div id='loader' style='display: none;'>
				<h1>Processing.....</h1>
			</div>

			<div id='messagefororder'>
			</div>
<?php

		}
	}

	/**
	 * Function : ced_get_sku
	 * Description : Fetching SKU of Product for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $sku
	 * @return $sku
	 */
	public function ced_get_sku( $data) {
		$sku = '';
		foreach ($data as $elements => $element) {
			$sku = $element['Item']['SKU'];
		}
		return $sku;
	}

	/**
	 * Function : ced_get_qty
	 * Description :  Fetching Purchased Quantity of Product for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $qty
	 * @return $qty
	 */
	public function ced_get_qty( $data) {
		foreach ($data as $elements => $element) {
			$qty = $element['QuantityPurchased'];
		}
		return $qty;
	}

	/**
	 * Function : fetch_shipping_address_for_order
	 * Description :  Fetching Shipping Address for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $shippingAddress
	 * @return $shippingAddress
	 */
	public function ced_fetch_shipping_address_for_order( $data) {
		$shippingAddress = array(
			'first_name' => $data['Name'],
			'address_1' => $data['Street1'],
			'city' => $data['CityName'],
			'state' => $data['StateOrProvince'],
			'postcode' => $data['PostalCode'],
			'country' => $data['Country'],
		);
		return $shippingAddress;
	}


	/**
	 * Function :ced_fetch_billing_address_for_order
	 * Description :  Fetching Billing Address for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $billingAddress
	 * @return $billingAddress
	 */
	public function ced_fetch_billing_address_for_order( $data) {
		$billingAddress = array(
			'first_name' => $data['Name'],
			'address_1' => $data['Street1'],
			'city' => $data['CityName'],
			'state' => $data['StateOrProvince'],
			'postcode' => $data['PostalCode'],
			'country' => $data['Country'],
		);
		return $billingAddress;
	}


	/**
	 * Function :ced_get_shipping_title
	 * Description :  Fetching Shipping Title for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $shippingMethodTitle
	 * @return $shippingMethodTitle
	 */
	public function ced_get_shipping_title( $data) {
		$shippingMethodTitle = $data['ShippingService'];
		return $shippingMethodTitle;
	}


	/**
	 * Function : ced_get_shipping_cost
	 * Description :  Fetching Shipping Cost for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $shippingMethodCost
	 * @return $shippingMethodCost
	 */
	public function ced_get_shipping_cost( $data) {
		foreach ($data as $elements => $element) {
			$shippingMethodCost = $element['value'];
		}
		return $shippingMethodCost;
	}


	/**
	 * Function :ced_get_tax
	 * Description :Fetching Tax Deatils(Name,Cost) for creation of new Order
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @param  mixed $data
	 * @var $tax_details
	 * @return $tax_details
	 */
	public function ced_get_tax_detail( $data) {
		foreach ($data as $elements => $element) {
			foreach ($element as $values => $value) {
				foreach ($value as $key => $secondElement) {
					foreach ($secondElement as $v => $lastElement) {
						$tax_details = array(
							'tax_name' => $lastElement['TaxDescription'],
							'tax_amount' => $lastElement['TaxAmount']['value']
						);
					}
				}
			}
		}
		return $tax_details;
	}


	/**
	 * Function :ced_create_order
	 * Description : Creating a New Order  Using a JSON File
	 * Version:1.0.0
	 *
	 * @since    1.0.0
	 * @var $orderfileName
	 * @var $upload
	 * @var $upload_dir
	 * @var $getFileDataForImport
	 * @var $getOrderFileDataForImport
	 * @var $products_to_add
	 * @var $user_id
	 * @var $args
	 * @var $new_order
	 * @var $sku
	 * @var $id
	 * @var $product
	 * @var $shippingAddress
	 * @var $billingAddress
	 * @var $getShippingTitle
	 * @var $getShippingCost
	 * @var $getTaxfeeDetail
	 * @return void
	 */
	public function ced_create_order() {
		if (check_ajax_referer('verify-ajax-call', 'nonce')) {
			global $wpdb;
			$orderfileName                 = isset($_POST['orderfilename']) ? sanitize_text_field($_POST['orderfilename']) : false;
			$upload                        = wp_upload_dir();
			$upload_dir                    = $upload['basedir'];
			$upload_dir                    = $upload_dir . '/cedcommerce_order_file/' . $orderfileName;
			$getOrderFileDataForImport     = file_get_contents($upload_dir);
			$decodedOrderFileDataForImport = json_decode($getOrderFileDataForImport, true);
			$products_to_add               = array();
			foreach ($decodedOrderFileDataForImport as $elements => $element) {
				foreach ($element as $values => $value) {
					foreach ($value as $data) {
						$user_id  = $data['BuyerUserID'];
						$orderKey = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s", '_order_id_unique'));
						if ($orderKey === $data['OrderID']) {
							echo 'Order Already Exist';
						} else {
							$args      = array(
								'customer_id'   => $user_id,
								'status'        => 'Processing',
								'customer_note' => 'Your order is on Processing.',
								'order_id'      => $data['OrderID'],
							);
							$new_order = wc_create_order($args);
							update_post_meta($new_order->ID, '_order_id_unique', $data['OrderID']);
							$sku     = $this->ced_get_sku($data['TransactionArray']['Transaction']);
							$id      = wc_get_product_id_by_sku($sku);
							$product = wc_get_product($id);
							if ($id) {
								$products_to_add[$product->get_id()] = $this->ced_get_qty($data['TransactionArray']['Transaction']);
							} else {
								//Create Product
							}
							foreach ($products_to_add as $product => $qty) {
								$new_order->add_product(wc_get_product($product), $qty);
							}
							$date_time        = new WC_DateTime();
							$addShipping      = new WC_Order_Item_Shipping();
							$addfee           = new WC_Order_Item_Fee();
							$shippingAddress  = $this->ced_fetch_shipping_address_for_order($data['ShippingAddress']);
							$billingAddress   = $this->ced_fetch_billing_address_for_order($data['ShippingAddress']);
							$getShippingTitle = $this->ced_get_shipping_title($data['ShippingServiceSelected']);
							$getShippingCost  = $this->ced_get_shipping_cost($data['ShippingServiceSelected']);
							$getTaxfeeDetail  = $this->ced_get_tax_detail($data['TransactionArray']['Transaction']);
							$new_order->set_date_created($date_time);
							$new_order->set_address($shippingAddress, 'shipping');
							$new_order->set_address($billingAddress, 'billing');
							$new_order->set_currency($data['Total']['currencyID']);
							$addShipping->set_method_title($getShippingTitle);
							$addShipping->set_total($getShippingCost);
							$new_order->add_item($addShipping); //Adding  shipping Detail To Order
							$addfee->set_name($getTaxfeeDetail['tax_name']);
							$addfee->set_amount($getTaxfeeDetail['tax_amount']);
							$addfee->set_total($getTaxfeeDetail['tax_amount']);
							$addfee->set_tax_class('');
							$addfee->set_tax_status('taxable');
							$new_order->add_item($addfee); // Adding  Tax fee to order
							$new_order->calculate_totals();
							$new_order->save();
							echo 'Order Created Successfully';
						}
					}
				}
			}
		}
		wp_die();
	}
}
