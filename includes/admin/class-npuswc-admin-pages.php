<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Admin_Pages' ) ) {
/**
 * NPUSWC Guide Page
 * 
**/
class NPUSWC_Admin_Pages extends NPUSWC_Unique {

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
		function __construct()
		{

			// Setup
			$this->init_hooks();

		}

		/**
		 * Init Hooks
		**/
		protected function init_hooks()
		{

			#
			# Actions
			#
				// Submenu Page
				add_action( 'admin_menu', array( $this, 'add_admin_page' ), 100 );

				// Tab
				add_action( 'npuswc_action_setting_page_tab', array( $this, 'render_admin_page_tab' ) );

				// Enqueue Scripts
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );


			#
			# Filters
			#
				// To WC advanced settings pages
				//add_filter( 'woocommerce_settings_pages', array( $this, 'woocommerce_settings_pages' ) );

		}

	#
	# Actions
	#
		/**
		 * Add Admin Page
		**/
		public function add_admin_page()
		{

			if ( current_user_can( 'manage_woocommerce' ) ) {

				// Admin Page
				add_submenu_page(
					'woocommerce', // WooCommerce shop_order
					esc_html__( 'NPUSWC', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), 
					esc_html__( 'NPUSWC', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), 
					'manage_options', 
					'npuswc_admin_page', 
					array( $this, 'render_admin_page' )
				);

			}

		}

		/**
		 * Add Admin Page
		**/
		public function add_admin_page_tab( $tab )
		{

			require( npuswc_get_template_file_path( $tab, 'admin-page/parts' ) );

		}

		/**
		 * Render Admin Page
		**/
		public function render_admin_page()
		{

			// Load Template
				ob_start();
					require_once( NPUSWC_DIR_PATH . 'templates/admin-page/template-admin-page.php' );
				$admin_page = ob_get_clean();
				echo apply_filters( 'npuswc_filter_html_admin_page', $admin_page );

		}

		/**
		 * Render Admin Page Tab
		 * @param string $tab
		**/
		public function render_admin_page_tab( $tab )
		{

			// Load Template
				ob_start();
					require( npuswc_get_template_file_path( $tab, 'admin-page/parts' ) );
				$admin_page_tab = ob_get_clean();
				echo apply_filters( 'npuswc_filter_html_guide_page', $admin_page_tab );

		}

		/**
		 * Enqueue Scripts
		**/
		public function admin_enqueue_scripts( $hook )
		{

			// Check the URL Request Params
			if ( ! isset( $_GET['page'] ) 
				|| ! in_array( $_GET['page'], array(
					'npuswc_admin_page',
				) )
			) {
				return false;
			}

			wp_enqueue_style( 'npuswc-admin-menu-pages-css' );

			// Setting
			if ( isset( $_GET['page'] ) 
				&& in_array( $_GET['page'], array( 'npuswc_admin_page' ) )
			) {
				//wp_enqueue_style( 'npuswc-admin-setting-page-style' );
				wp_enqueue_script( 'npuswc-admin-setting-page-js' );
			}

			// Tool
			elseif ( isset( $_GET['page'] ) 
				&& ! in_array( $_GET['page'], array( 'npuswc_admin_page' ) )
			) {
				//wp_enqueue_style( 'npuswc-admin-setting-page-style' );
				wp_enqueue_script( 'npuswc-admin-setting-page-js' );
			}

		}

	/**
	 * Filters
	**/
		/**
		 * woocommerce_settings_pages
		**/
		public function woocommerce_settings_pages( $settings_pages )
		{

			foreach ( $settings_pages as $index => $settings_page ) {
				if ( 'woocommerce_myaccount_downloads_endpoint' === $settings_page['id'] ) {
					$key = $index;
					break;
				}
			}

			$settings_pages = npuswc_array_insert( $settings_pages, array( array(
				'title'    => __( 'Download Tokens', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'desc'     => __( 'Endpoint for the "Download Tokens" page.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'id'       => 'woocommerce_myaccount_npuswc_tokens_endpoint',
				'type'     => 'text',
				'default'  => 'npuswc-tokens',
				'desc_tip' => true,
			) ), $key );

			return $settings_pages;

		}


}

}

