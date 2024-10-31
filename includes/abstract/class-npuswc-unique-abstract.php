<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'NPUSWC_Unique' ) ) {
/**
 * Class which should be initialized only once
**/
class NPUSWC_Unique {

	#
	# Statics
	#
		/**
		 * Instance of the Class
		 * 
		 * @var object WCYSS
		**/
		protected static $instance = null;

	#
	# Settings
	#
		/**
		 * Cloning is forbidden.
		 * @since 1.0.0
		 */
		public function __clone()
		{
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0.0
		 */
		public function __wakeup() {
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

}
}

