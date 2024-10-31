<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( "NPUSWC_Exception_Data" ) ) {
/**
 * 
**/
class NPUSWC_Exception_Data extends NPUSWC_Exception {

	/**
	 * Init
	**/
		/**
		 * Construct
		**/
		function __construct( $message = '', $code = 0, $previous = null )
		{
			parent::__construct( $message, $code, $previous );
		}

}
}