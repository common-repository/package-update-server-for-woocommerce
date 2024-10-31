<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Formats' ) ) {
/**
 * Data formats
**/
class NPUSWC_Data_Formats extends NPUSWC_Unique {

	// Property "date_format" of WC Order
	const WC_ORDER_DATE_COMPLETED = 'y-m-d H:i:s.u'; // '2018-01-04 16:18:04.000000'

	#
	# Vars
	#
		/**
		 * Instance of Self
		 * 
		 * @var Self
		**/
		protected static $instance = null;

	/**
	 * Public Initializer
	 * 
	 * @uses self::$instance
	 * 
	 * @return Self
	**/
	public static function get_instance()
	{

		// Init if not yet
		if ( null === self::$instance ) {
			self::$instance = new Self();
		}

		// End
		return self::$instance;

	}

	/**
	 * Constructor
	**/
	protected function __construct()
	{

	}

}
}