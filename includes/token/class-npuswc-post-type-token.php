<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Post_Type_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Post_Type_Token {

	/*
	 * Statics
	**/
		/**
		 * Instance of the Class
		 * 
		 * @var [Nora_Package_Update_Server_For_WooCommerce]
		**/
		private static $instance;

	/**
	 * Settings
	**/
		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone()
		{
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup()
		{
			npuswc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

	/**
	 * Init
	**/
		/**
		 * Public Initializer
		 * @return Self
		**/
		public static function get_instance()
		{
			if ( null === self::$instance ) {
				self::$instance = new Self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		**/
		private function __construct()
		{

			$this->init_hooks();

		}

		/**
		 * Init Hooks
		**/
		private function init_hooks()
		{

			add_action( 'init', array( $this, 'register_post_types' ) );

		}

		/**
		 * Register Post Type "token"
		**/
		public function register_post_types()
		{

			$labels = array(
				'name'               => _x( 'NPUSWC Tokens', 'post type general name', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'singular_name'      => _x( 'NPUSWC Token', 'post type singular name', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'menu_name'          => _x( 'NPUSWC Tokens', 'admin menu', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'name_admin_bar'     => _x( 'NPUSWC Token', 'add new on admin bar', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'add_new'            => _x( 'Add New', 'token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'add_new_item'       => __( 'Add New Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'new_item'           => __( 'New Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'view_item'          => __( 'View Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'all_items'          => __( 'All Tokens', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'search_items'       => __( 'Search NPUSWC Tokens', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'parent_item_colon'  => __( 'Parent Tokens:', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'not_found'          => __( 'No tokens found.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No tokens found in Trash.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
			);

			$args = apply_filters( 'npuswc_filter_post_type_args', array(
				'labels'              => $labels,
				'description'         => __( 'Description.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'npuswc-token' ),
				'capability_type'     => 'page',
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
			), 'npuswc-token' );

			register_post_type( 'npuswc-token', $args );

		}

}
}