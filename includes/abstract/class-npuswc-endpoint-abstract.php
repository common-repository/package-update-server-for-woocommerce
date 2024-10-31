<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class NPUSWC_Endpoint_Abstract {

	public $endpoint = '';
	public $option_name_endpoint = '';
	public $title_endpoint = '';
	public $description_endpoint = '';
	public static $index_endpoint = 'downloads';

	public function init( string $endpoint, string $title_endpoint )
	{

		$this->endpoint             = $endpoint;
		$this->option_name_endpoint = preg_replace( '/[^a-zA-Z0-9\_]/i', '_', $endpoint );
		$this->title_endpoint       = $title_endpoint;

		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'woocommerce_settings_pages', array( $this, 'woocommerce_settings_pages' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		add_filter( 'the_title', array( $this, 'endpoint_title' ), 10, 2 );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ) );
		add_action( 'woocommerce_account_' . $this->endpoint .  '_endpoint', array( $this, 'woocommerce_account_endpoint' ) );
		add_action( 'wp_footer', array( $this, 'print_templates_for_js' ), 10 );

	}

		/**
		 * Register new endpoint to use inside My Account page
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		**/
		public function add_endpoints()
		{
			add_rewrite_endpoint( 
				get_option( 'woocommerce_myaccount_' . $this->option_name_endpoint . '_endpoint', $this->endpoint ),
				EP_PAGES
			);
		}

		/**
		 * woocommerce_settings_pages
		 * @version 1.0.0
		 * 
		**/
		public function woocommerce_settings_pages( $settings_pages )
		{

			foreach ( $settings_pages as $index => $settings_page ) {
				if ( 'woocommerce_myaccount_' . self::$index_endpoint . '_endpoint' === $settings_page['id'] ) {
					$key = $index;
					break;
				}
			}

			$settings_pages = npuswc_array_insert( $settings_pages, array( array(
				'title'    => $this->title_endpoint,
				'desc'     => $this->description,
				'id'       => 'woocommerce_myaccount_' . $this->option_name_endpoint . '_endpoint',
				'type'     => 'text',
				'default'  => $this->endpoint,
				'desc_tip' => true,
			) ), $key );

			return $settings_pages;

		}

		public function add_query_vars( $vars )
		{
			$vars[] = get_option( 'woocommerce_myaccount_' . $this->option_name_endpoint . '_endpoint', $this->endpoint );
			return $vars;
		}

		/**
		 * Add Title for NPUSWC Token
		 * @param string $title
		 * @param string $endpoint
		 * @return string
		**/
		public function endpoint_title( $title, $endpoint )
		{

			if ( get_option( 'woocommerce_myaccount_' . $this->option_name_endpoint . '_endpoint', $this->endpoint ) === $endpoint ) {
				$title = esc_html__( $this->title_endpoint, Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}

			return $title;

		}

		/**
		 * Insert NPUSWC Tokens Tab
		**/
		public function woocommerce_account_menu_items( $items )
		{

			foreach ( $items as $index => $item ) {
				if ( self::$index_endpoint === $index ) {
					$key = $index;
					break;
				}
			}

			$items = npuswc_array_insert(
				$items,
				array(
					$this->endpoint => esc_html__( $this->title_endpoint, Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
				),
				$key
			);

			return $items;

		}

		/**
		 * Render Tab
		 * @param string $value
		**/
		abstract public function woocommerce_account_endpoint( $current_page );

		public static function install() {
			flush_rewrite_rules();
		}

}
