<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Downloadable_Product' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Data_Downloadable_Product extends NPUSWC_Data_Product_Token {

	/**
	 * Order Object
	 * @var NPUSWC_Order
	**/
	protected $npuswc_order = null;

	/**
	 * Product Object
	 * @var WC_Product
	**/
	protected $wc_product = null;

	/**
	 * Data
	 * @var array
	**/
	protected $defaults = array(
		'package_type'            => '',
		'package_version'         => '1.0.0',
		'environment_version'     => '',
		'_download_expiry_in_day' => -1, // WC setting
		'update_expiry'           => 'no',
		'date_package_updated'    => '',
		'extended_expiry'         => '',
		'restrict_access'         => 'no',
		'accessible_url_number'   => 'no',
		'purchased_number'        => 0,
	);

	/**
	 * Constructor
	 * @param mixed $order
	 * @param mixed $product
	**/
	public function __construct( $order, $product )
	{

		parent::__construct( $product );

		$wc_order = WC()->order_factory->get_order( $order );
		if ( false === $wc_order ) {
			throw new Exception( 'Wrong Order Data.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
		}
		$this->npuswc_order = new NPUSWC_Order( $wc_order->get_id() );

		$wc_product = WC()->product_factory->get_product( $product );
		if ( false === $wc_product ) {
			throw new Exception( 'Wrong Product Data.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
		}
		$this->wc_product = $wc_product;

	}

	/**
	 * Get data
	 *  	// User ID
	 *  	'user_id'                  => $user_id,
	 *  	// Order ID
	 *  	'order_id'                 => $downloadable_item['order_id'],
	 *  	// Order key ( $_GET['order'] )
	 *  	'order_key'                => $downloadable_item['order_key'],
	 *  	// Product ID ( $_GET['download_file'] )
	 *  	'product_id'               => $downloadable_item['product_id'],
	 *  	// Product ID ( $_GET['download_file'] )
	 *  	'product_date_created'     => $date_created_timestamp,
	 *  	// Product ID ( $_GET['download_file'] )
	 *  	'product_date_modified'    => $date_modified_timestamp,
	 *  	// Product ID ( $_GET['download_file'] )
	 *  	'tested_environment_version' => $_npuswc_tested_environment_version,
	 *  	// Download key ( $_GET['key'] )
	 *  	'download_key'             => $downloadable_item['download_id'],
	 *  	// Name
	 *  	'file_name'                => $downloadable_item['download_name'],
	 *  	// File URL
	 *  	'file_url'                 => $downloadable_item['download_url'],
	 *  	// WP content type
	 *  	'package_type'          => $_npuswc_product_package_type,
	 *  	// WP content type
	 *  	'product_package_version'       => $_npuswc_product_package_version,
	 *  	// Download Limit : "-1" in unlimited case
	 *  	'downloads_remaining'      => $downloadable_item['downloads_remaining'],
	 *  	// Access Expire : "-1" in unlimited case
	 *  	'access_expiry'           => $exp,
	 *  	// Download expiry
	 *  	'download_expiry_in_day'   => $_download_expiry_in_day,
	 *  	// jwt ID just in case
	 * 	 	'jwt_id'                   => $jwt_id,
	**/
	public function get_data()
	{

		$data = parent::get_data();
		if ( ! $data ) {
			return false;
		}

		$data['order_key']        = $this->npuswc_order->get_order_key();
		$data['purchased_number'] = intval( $this->npuswc_order->get_token_item_quantity( $this->wc_product->get_id() ) );
		if ( 0 >= $data['purchased_number'] ) {
			return false;
		}

		return $data;

	}

}
}


