<?php
if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


class Ced_Product_List extends WP_List_Table {



	/** Class constructor */
	public function __construct() {

		parent::__construct([
			'singular' => __('product'), //singular name of the listed records
			'plural'   => __('products'), //plural name of the listed records
			'ajax'     => false //should this table support ajax?
		]);
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e('No Products avaliable.');
	}


	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item) {
		return sprintf(
			'<input type="checkbox" name="bulk-import[]" value="%s" />',
			$item['item']['item_sku']
		);
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'images'   => __('Product Image'),
			'name'     => __('Name'),
			'item_sku' => __('SKU'),
			'price' => __('Price'),
			'type' => __('Type'),
			'Action' => __('Action')

		];

		return $columns;
	}



	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array('name', true),
			'price' => array('price', true)

		);

		return $sortable_columns;
	}

	public function column_default( $item, $column_name) {
		$id = $item['item']['item_sku'];
		global $wpdb;
		$product_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $id));
		if ($product_id) {
			$html = 'Already Imported';
		} else {
			$html = "<a href='#' class='button button-primary button-next' id='import_product'  data-productId='$id'>Import</a>";
		}
		switch ($column_name) {
			case 'images':
				return sprintf(
					'<img src="%s" style="width:90px; height:50px;"/>',
					$item['item']['images'][0]
				);
			case 'name':
				return $item['item'][$column_name];
			case 'item_sku':
				return $item['item'][$column_name];
			case 'price':
				return $item['item'][$column_name];
			case 'type':
				if ( 1 == $item['item']['has_variation'] ) {
					return 'Variable';
				} else {
					return 'Simple';
				}
			case 'Action':
				return $html;
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-import' => 'Import'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}
}
