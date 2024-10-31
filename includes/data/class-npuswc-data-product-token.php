<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Product_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Data_Product_Token {

	/**
	 * Should be Order ID
	 * @var null|int
	**/
	protected $id;

	/**
	 * Data
	 * @var array
	**/
	protected $data = array();

	/**
	 * Data
	 * @var array
	**/
	protected $defaults = array(
		'package_type'           => '',
		'package_version'        => '1.0.0',
		'environment_version'    => '',
		'download_expiry_in_day' => -1, // WC setting
		'update_expiry'          => 'no',
		'date_package_updated'   => '',
		'extended_expiry_in_day' => 0,
		'restrict_access'        => 'no',
		'accessible_url_number'  => 1,
	);

	/**
	 * Product Object
	 * @var WC_Product
	**/
	protected $wc_product = null;

	/**
	 * Constructor
	 * @param mixed $order
	 * @param mixed $product
	**/
	public function __construct( $product )
	{

		$wc_product = WC()->product_factory->get_product( $product );
		if ( false === $wc_product ) {
			return false;
		}

		$this->wc_product = $wc_product;
		$this->id = $this->wc_product->get_id();

	}

	/**
	 * Get Product Data
	 * @return bool|string[]
	**/
	public function get_data()
	{

		$product_id = intval( $this->wc_product->get_id() );

		$data = $this->defaults;

		$data['product_id'] = $product_id;

		// Product Package Type
		$_npuswc_product_package_type = get_post_meta( $product_id, '_npuswc_product_package_type', true );
		if ( 'none' === $_npuswc_product_package_type ) {
			return false;
		}
		$data['package_type'] = $_npuswc_product_package_type;

		// Product Package Version
		$_npuswc_product_package_version = get_post_meta( $product_id, '_npuswc_product_package_version', true );
		if ( npuswc_is_string_and_version( $_npuswc_product_package_version ) ) {
			$data['package_version'] = $_npuswc_product_package_version;
		}

		// Product Environment Version
		$_npuswc_tested_environment_version = get_post_meta( $product_id, '_npuswc_tested_environment_version', true );
		if ( npuswc_is_string_and_version( $_npuswc_tested_environment_version ) ) {
			$data['environment_version'] = $_npuswc_tested_environment_version;
		}

		// Download Expiry
		$_download_expiry = get_post_meta( $product_id, '_download_expiry', true );
		if ( is_numeric( $_download_expiry ) && 0 < intval( $_download_expiry ) ) {
			$data['download_expiry_in_day'] = $_download_expiry;
		}

		return apply_filters( 'npuswc_filter_data_product_token', $data, $this->wc_product );

	}

}
}


