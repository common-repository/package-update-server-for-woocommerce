<?php


// Define Class Nora_Package_Update_Server_For_WooCommerce
if ( ! class_exists( 'Nora_Package_Update_Server_For_WooCommerce' ) ) {
/**
 * Package Update Server for WooCommerce
**/
final class Nora_Package_Update_Server_For_WooCommerce {

	#
	# Consts
	#
		/**
		 * Unique key to be used for prefixes
		**/
		const PLUGIN_NAME      = 'Package Update Server for WooCommerce';
		const PLUGIN_VERSION   = '0.1.7';
		const UNIQUE_KEY       = 'npuswc';
		const UPPER_UNIQUE_KEY = 'NPUSWC';

		// For update checker
		const TEXTDOMAIN       = 'npuswc';
		const PLUGIN_DIR_NAME  = 'package-update-server-for-woocommerce';

	#
	# Properties
	#
		#
		# Public
		#
			/**
			 * Instance of WCYSS_Notices
			 * 
			 * @var [NPUSWC_Notices]
			**/
			public $notices;

			/**
			 * Instance of NPUSWC_Translatable_Texts 
			 * 
			 * @var [NPUSWC_Translatable_Texts]
			**/
			public $texts;

			/**
			 * Instance of NPUSWC_Option_Manager 
			 * 
			 * @var [NPUSWC_Option_Manager]
			**/
			public $option_manager;

			/**
			 * Instance of NPUSWC_Admin 
			 * 
			 * @var [NPUSWC_Admin]
			**/
			public $admin;

			/**
			 * Flag to check if the WooCommerce is active
			 * 
			 * @var [bool]
			**/
			public $wc_is_active = true;

		#
		# Protected
		#

	#
	# Statics
	#
		/**
		 * Instance of the Class
		 * 
		 * @var [Nora_Package_Update_Server_For_WooCommerce]
		**/
		private static $instance;


	#
	# Settings
	#
		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone() {
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup() {
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

	#
	# Tools
	#
		/**
		 * Define Constant
		 * 
		 * @param string $name
		 * @param bool|int|string
		 * 
		 * @return bool
		**/
		private function define_const( $name, $value ) {

			// Check Name
			if( empty( $name ) || ! is_string( $name ) ) {
				return false;
			}

			// Check Value
			if( ! isset( $value ) || is_array( $value ) || is_object( $value ) ) {
				return false;
			}

			// Exec
			if( ! defined( $name ) ) {
				define( $name, $value );
				return true;
			}

			return false;

		}

		/**
		 * Returns the key
		 * @uses  [string] self::UNIQUE_KEY
		 * @return [string]
		**/
		public function get_prefix_key()
		{
			return self::UNIQUE_KEY;
		}

		/**
		 * Public Initializer
		 * 
		 * @return NPUSWC_Option_Manager
		**/
		public function get_option_manager()
		{

			// Init if not yet
			return $this->option_manager;

		}

		/**
		 * Public Initializer
		 * 
		 * @return NPUSWC_Translatable_Texts
		**/
		public function get_tramslatable_texts()
		{

			// Init if not yet
			return $this->texts;

		}

		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return NPUSWC_Token_Manager
		**/
		public function get_token_manager()
		{

			// Init if not yet
			return $this->token_manager;

		}

		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return NPUSWC_Client
		**/
		public function get_npuswc_client()
		{

			// Init if not yet
			return $this->npuswc_client;

		}

	#
	# Activation
	#
		/**
		 * Activation
		**/
		public function activate()
		{

			$this->includes();
			$endpoint_purchased_token = NPUSWC_Endpoint_Purchased_Tokens::install();
			flush_rewrite_rules();

		}

	#
	# Deactivation
	#
		/**
		 * Deactivation
		**/
		public function deactivate()
		{
			
		}

	#
	# Init
	#
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return Nora_Package_Update_Server_For_WooCommerce
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

			// Activate
			register_activation_hook( NPUSWC_MAIN_FILE, array( $this, 'activate' ) );

			// Deactivate
			register_deactivation_hook( NPUSWC_MAIN_FILE, array( $this, 'deactivate' ) );

			// Check if site has WooCommerce
			// Then, Start setup
			// Define Constants and Check if WCYSS is Working
			add_action( 'plugins_loaded', array( $this, 'define_requirements' ), 10 );

		}

		/**
		 * Define variables
		 * 		First, Check if WooCommerce is active
		 * 		Second, Start
		**/
		public function define_requirements()
		{

			// Check if WooCommerce is active
			if ( ! function_exists( 'wc' ) ) {
				return;
			}

			// Text Domain
			load_plugin_textdomain(
				Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN,
				false,
				plugin_basename( dirname( NPUSWC_MAIN_FILE ) ) . '/i18n/languages'
			);

			// Define Vars
				/**
				 * Flag to check if the WooCommerce is active
				 * 
				 * @var bool
				**/
				$this->wc_is_active = true;

				$this->define_const( 'NPUSWC_ASSET_CSS_URI', NPUSWC_DIR_URL . 'assets/css/' );
				$this->define_const( 'NPUSWC_ASSET_JS_URI', NPUSWC_DIR_URL . 'assets/js/' );
				$this->define_const( 'NPUSWC_ASSET_IMG_URI', NPUSWC_DIR_URL . 'assets/img/' );

			// Include files
			$this->includes();

			// Init classes
			$this->init_classes();

			// Init WP hooks
			$this->init_hooks(); 

		}

		/**
		 * Include required files
		**/
		protected function includes()
		{

			// Include files
			require_once( NPUSWC_DIR_PATH . 'includes/exec/include/required-files.php' );

		}

		/**
		 * Init Classes
		 * 		should be after 'plugins_loaded'
		 * 
		 * @usedby $this->define_requirements()
		 * 
		 * @return bool Returns false if this not 
		**/
		protected function init_classes()
		{

			/**
			 * Instance of NPUSWC_Translatable_Texts
			 * 
			 * @var NPUSWC_Translatable_Texts 
			**/
			$this->texts = NPUSWC_Translatable_Texts::get_instance();


			/**
			 * Instance of NPUSWC_Option_Manager
			 * 
			 * @var NPUSWC_Option_Manager 
			**/
			$this->option_manager = NPUSWC_Option_Manager::get_instance();
			$this->option_manager->reset_options();
			$this->option_manager->init_hooks();

			/**
			 * Instance of NPUSWC_Admin
			 * 
			 * @var NPUSWC_Admin 
			**/
			$this->admin = NPUSWC_Admin::get_instance();

			/**
			 * Instance of NPUSWC_Admin_Pages
			 * 
			 * @var NPUSWC_Admin_Pages
			**/
			$this->admin_pages = NPUSWC_Admin_Pages::get_instance();

			/**
			 * Instance of NPUSWC_Product_Metabox
			 * 
			 * @var NPUSWC_Product_Metabox
			**/
			$this->product_metabox = NPUSWC_Product_Metabox::get_instance();

			/**
			 * Instance of NPUSWC_Order_Metabox
			 * 
			 * @var NPUSWC_Order_Metabox
			**/
			$this->order_metabox = NPUSWC_Order_Metabox::get_instance();

			/**
			 * Instance of NPUSWC_Endpoint_Purchased_Tokens
			 * 
			 * @var NPUSWC_Endpoint_Purchased_Tokens
			**/
			$this->endpoint_purchsed_token = NPUSWC_Endpoint_Purchased_Tokens::get_instance();

			/**
			 * Instance of WCYSS_Notices
			 * 
			 * @var NPUSWC_Notices 
			**/
			$this->notices = NPUSWC_Notices::get_instance();

			/**
			 * NPUSWC_Post_Type_Token
			**/
			$this->post_type = NPUSWC_Post_Type_Token::get_instance();

			/**
			 * Instance of WCNE_WPCUC_Token_Manager
			 * 
			 * @var WCNE_WPCUC_Token_Manager 
			**/
			$this->token_manager = NPUSWC_Token_Manager::get_instance();

			/**
			 * Init extensions which is activated
			**/
			//do_action( 'npuswc_includes_extensions', $this );

			// NPUSWC_REST_API
			$this->rest_api = NPUSWC_REST_API_Loader::load( 'basic' );
			$this->rest_api->run();

			// NPUSWC_REST_API
			$this->rest_api = NPUSWC_REST_API_Loader::load( 'order_jwt' );
			$this->rest_api->run();

			do_action( 'npuswc_init_classes', $this );

		}

		/**
		 * Init WP hooks
		 * 		should be after 'plugins_loaded'
		 * 
		 * @usedby $this->define_requirements()
		 * 
		 * @return bool Returns false if this not 
		**/
		protected function init_hooks()
		{

			//add_action( 'init', array( $this, 'load_plugin_textdomain' ), 9 );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 9 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 9 );
			add_action( 'customize_preview_init', array( $this, 'register_scripts' ), 9 );
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'register_scripts' ), 9 );

		}

	#
	# Actions
	#
		/**
		 * 
		**/
		public function load_plugin_textdomain()
		{

		}

		/**
		 * Register CSS and JS
		 * @return void
		 */
		public function register_scripts()
		{

			// CSS
				// Admin setting page
				wp_register_style(
					'npuswc-admin-pages-css',
					NPUSWC_ASSET_CSS_URI . 'npuswc-admin-page.css'
				);

				// Admin setting page
				wp_register_style(
					'npuswc-product-settings-css',
					NPUSWC_ASSET_CSS_URI . 'product-settings.css'
				);

				// Admin setting page
				wp_register_style(
					'npuswc-customer-downloads-css',
					NPUSWC_ASSET_CSS_URI . 'npuswc-customer-downloads.css'
				);

			// JS
				// Base: Init var npuswc
				wp_register_script(
					'npuswc-base-js',
					NPUSWC_ASSET_JS_URI . 'npuswc.js',
					array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'jquery-ui-draggable', 'underscore', 'backbone' ),
					false,
					true
				);

				// Admin setting page
				wp_register_script(
					'npuswc-admin-setting-page-js',
					NPUSWC_ASSET_JS_URI . 'npuswc-admin-setting-page.js',
					array( 'npuswc-base-js' ),
					false,
					true
				);

				// Customer downloads
				wp_register_script(
					'npuswc-product-settings-js',
					NPUSWC_ASSET_JS_URI . 'product-settings.js',
					array( 'npuswc-base-js' ),
					false,
					true
				);

				// Customer downloads
				wp_register_script(
					'npuswc-customer-downloads-js',
					NPUSWC_ASSET_JS_URI . 'customer-downloads.js',
					array( 'npuswc-base-js' ),
					false,
					true
				);

		}

	#
	# Filters
	#

} // End Closure of the Class

}

