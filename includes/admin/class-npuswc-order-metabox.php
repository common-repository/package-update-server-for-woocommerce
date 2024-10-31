<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Order_Metabox' ) ) {
/**
 * 
**/
class NPUSWC_Order_Metabox extends NPUSWC_Unique {

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

			$this->init_hooks();

		}

		/**
		 * Init WP Hooks
		**/
		protected function init_hooks()
		{
			// Add Metaboxes
			//add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		}

		/**
		 * Add meta boxes
		 * 
		 * @param string $post_type
		 * @param object $post
		**/
		public function add_meta_boxes( $post_type = '', $post = '' )
		{

			// WC Order
			if ( 'shop_order' === $post_type ) {

				add_meta_box(
					'npuswc-user-url-viewer',
					esc_html__( 'NPUSWC User URL Viewer', 'wcyss' ),
					array( $this, 'render_shop_order_metabox' ),
					$post_type,
					'advanced',
					'high',
					array()
				);

			}

		}

		/**
		 * Render form for JWT data
		 * 
		 * @param WP_Post $order
		 * @param array   $args
		 *
		 * @uses file 'templates/metabox/form-jwt-data.php'
		**/
		public function render_shop_order_metabox( $order, $args = array() )
		{

			do_action( 'npuswc_action_shop_order_metabox_start', $order, $args );

			ob_start();
			include( 'views/template-shop-order-jwt.php' );
			$html = ob_get_clean();
			echo apply_filters( 'npuswc_filter_shop_order_metabox', $html, $order, $args );

			do_action( 'npuswc_action_shop_order_metabox_end', $order, $args );

		}



}
}