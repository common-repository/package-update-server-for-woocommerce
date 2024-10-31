<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 
**/
class NPUSWC_User_Meta_Manager extends NPUSWC_Unique {

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
		 * 
		**/
		public $scripts_text_object = 'scriptsObject';

	#
	# Initializer
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
		 * Init WP Hooks
		**/
		protected function init_hooks()
		{

			// Enqueue scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			// Add column for download page
			//add_filter( 'woocommerce_account_downloads_columns', array( $this, 'woocommerce_account_downloads_columns' ), 10 );

			add_filter( 'woocommerce_get_query_vars', array( $this, 'woocommerce_get_query_vars' ), 10 );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ), 10 );
			add_filter( 'woocommerce_endpoint_' . get_option( 'woocommerce_myaccount_npuswc_tokens_endpoint', 'npuswc-tokens' ) . '_title', array( $this, 'woocommerce_endpoint_title' ), 10, 2 );
			add_action( 'woocommerce_account_npuswc-tokens_endpoint', array( $this, 'woocommerce_account_endpoint' ), 10, 1 );


			/**
			 * Add a JWT Column
			 *
			 * @param string $column_id
			 * @param array  $download
			 */
			//add_action( 'woocommerce_account_downloads_column_npuswc-jwt', array( $this, 'woocommerce_account_downloads_column_npuswc_jwt' ) );
			//add_action( 'woocommerce_account_downloads_column_npuswc-jwt', array( $this, 'woocommerce_account_downloads_column_npuswc_jwt' ) );

			/**
			 * Load templates for js
			**/
			add_action( 'wp_footer', array( $this, 'print_templates_for_js' ), 10 );

		}

	#
	# Actions
	#
	
		/**
		 * Enqueue scripts
		**/
		function enqueue_scripts( $hook )
		{

			if ( is_account_page() || is_checkout() || is_wc_endpoint_url() ) {

				wp_enqueue_style( 'npuswc-customer-downloads-css' );

				wp_localize_script( 'npuswc-customer-downloads-js', $this->scripts_text_object, npuswc()->texts->get_admin_texts() );
				wp_enqueue_script( 'npuswc-customer-downloads-js' );

			}

		}

		/**
		 * Get query vars.
		 * @param array $query_vars
		 * @return array
		**/
		public function woocommerce_get_query_vars( $query_vars )
		{

			$query_vars['npuswc-tokens'] = get_option( 'woocommerce_myaccount_npuswc_tokens_endpoint', 'npuswc-tokens' );

			return $query_vars;

		}

		/**
		 * Insert NPUSWC Tokens Tab
		**/
		public function woocommerce_account_menu_items( $items )
		{

			foreach ( $items as $index => $item ) {
				if ( 'downloads' === $index ) {
					$key = $index;
					break;
				}
			}

			$items = npuswc_array_insert( $items, array( 'npuswc-tokens' => esc_html__( 'Download Tokens', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) ), $key );

			return $items;

		}

		/**
		 * Add Title for NPUSWC Token
		 * @param string $title
		 * @param string $endpoint
		 * @return string
		**/
		public function woocommerce_endpoint_title( $title, $endpoint )
		{

			if ( get_option( 'woocommerce_myaccount_npuswc_tokens_endpoint', 'npuswc-tokens' ) === $endpoint ) {
				$title = esc_html__( 'Puchased Tokens', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}

			return $title;

		}

		/**
		 * Render NPUSWC Tokens Tab
		 * @param string $value
		**/
		public function woocommerce_account_endpoint( $current_page )
		{
			include( 'views/template-purchased-downloadable-tokens.php' );
		}

		/**
		 * Print a JWT Column
		 *
		 * @param string $column_id
		 * @param array  $download
		**/
		public function popup_get_the_token( string $token_id, string $token_text = '' )
		{

			if ( '' === $token_id
				|| '' === $token_text
			) {
				return false;
			}

			echo '<td>';
				echo '<a id="npuswc-customer-purchased-token-' . $token_id . '" class="npuswc-customer-purchased-token button alt" href="javascript: void( 0 );" data-token-id="' . $token_id . '">' . esc_html__( 'Get the Token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</a>';
				echo '<input id="npuswc-hidden-customer-purchased-token-' . $token_id . '" class="npuswc-purchased-token-value" type="hidden" value="' . $token_text . '" data-token-id="' . $token_id . '">';
			echo '</td>';

		}

		/**
		 * Load template HTMLs
		 *
		 * @param string $column_id
		**/
		public function print_templates_for_js()
		{

			require_once( 'views/template-popup-customer-downloads.php' );

		}


}
