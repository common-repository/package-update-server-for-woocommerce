<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'NPUSWC_Admin' ) ) {
/**
 * Admin Class
 * 
 * 
**/
class NPUSWC_Admin extends NPUSWC_Unique {

	#
	# Properties
	#
		/**
		 * Admin pages
		 * 
		 * @var NPUSWC_Admin_Pages
		**/
		public $admin_pages = null;

		/**
		 * Notices
		 * 
		 * @var NPUSWC_Notices
		**/
		public $notices = null;

		/**
		 * Instance of NPUSWC_User_Meta
		 * 
		 * @var NPUSWC_User_Meta
		**/
		public $user_meta_manager = null;

	#
	# Vars
	#
		/**
		 * Instance of Self
		 * 
		 * @var Self
		**/
		protected static $instance = null;

	#
	# Init
	#
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

			// Init WP hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

		}

}
}

