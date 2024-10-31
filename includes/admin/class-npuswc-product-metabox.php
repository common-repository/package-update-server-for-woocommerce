<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Product_Metabox' ) ) {
/**
 * 
**/
class NPUSWC_Product_Metabox extends NPUSWC_Unique {

	/** 
	 * Vars
	**/
		/**
		 * Instance of Self
		 * 
		 * @var Self
		**/
		protected static $instance = null;

	/** 
	 * Initializer
	**/
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

			/**
			 * Enqueue Scripts
			**/
				/**
				 * Admin enqueue scripts
				 * 
				 * @param int $post_id
				**/
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			/**
			 * Simple
			**/
				/**
				 * Triggered at the end of tab form of single product
				 * You should print the form view 
				**/
				add_action( 'woocommerce_product_options_downloads', array( $this, 'render_product_form' ), 10 );

				/**
				 * Print Setting Title
				 * 
				 * @param int $post_id
				**/
				add_action( 'npuswc_action_render_product_form_start', array( $this, 'settings_field_title' ), 10 );

				/**
				 * Save Action for variation
				 * 
				 * @param int $post_id
				**/
				add_action( 'woocommerce_process_product_meta_simple', array( $this, 'save_product_simple' ), 10 );



		}

	/**
	 * Admin enqueue scripts
	**/
		/**
		 * 
		**/
		public function admin_enqueue_scripts( $hook )
		{

			if ( ! isset( $hook )
				|| ! in_array( $hook, array( 'post-new.php', 'post.php' ) )
			) {
				return;
			}

			if ( 'post-new.php' === $hook ) {
				if ( ! isset( $_GET['post_type'] )
					|| 'product' !== $_GET['post_type']
				) {
					return;
				}
			}

			elseif ( 'post.php' === $hook ) {
				global $post;
				if ( 'product' !== $post->post_type ) {
					return;
				}
			}

			wp_enqueue_style( 'npuswc-product-settings-css' );
			wp_enqueue_script( 'npuswc-product-settings-js' );

		}

	/**
	 * Forms
	**/
		/**
		 * Load template of form WP content type
		**/
		public function render_product_form()
		{

			global $post;
			$product_id = intval( $post->ID );

			echo '<div id="npuswc_token_options" class="options_group npuswc_token_options">';

			do_action( 'npuswc_action_render_product_form_start', $product_id );

			// WP package type
			woocommerce_wp_select( array(
				'id'            => "_npuswc_product_package_type",
				'class'         => 'select',
				'wrapper_class' => implode( ' ', array( 
					'show_if_downloadable',
				) ),
				'name'          => "_npuswc_product_package_type",
				'label'         => __( 'Package Type', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => false,
				'description'   => __( 'Please select "Theme" or "Plugin" if the package is of WP Theme or Plugin.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'options'       => array(
					'none' => __( 'None', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					'theme' => __( 'Theme', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					'plugin' => __( 'Plugin', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					'others' => __( 'Others', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				),
			) );

			// WP package version
			woocommerce_wp_text_input( array(
				'id'            => "_npuswc_product_package_version",
				'class'         => 'short',
				'wrapper_class' => implode( ' ', array( 
					'show_if_downloadable',
				) ),
				'name'          => "_npuswc_product_package_version",
				'label'         => __( 'Package Version', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => false,
				'description'   => __( 'Please enter version of the package.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
			) );

			// Tested environment version
			woocommerce_wp_text_input( array(
				'id'            => "_npuswc_tested_environment_version",
				'class'         => 'short',
				'wrapper_class' => implode( ' ', array( 
					'show_if_downloadable',
				) ),
				'name'          => "_npuswc_tested_environment_version",
				'label'         => __( 'Tested Envirionment Version', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => false,
				'description'   => __( 'Tested Envirionment version.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
			) );

			do_action( 'npuswc_action_render_product_form_end', $product_id );

			echo '</div>';

		}

			/**
			 * Load 
			 * @param int $product_id
			**/
			public function settings_field_title( $product_id )
			{

				echo '<p class="npuswc-token-settings-label">'; echo esc_html__( 'Settings of Package Update Server for WooCommerce', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); echo '</p>';

			}

	/** 
	 * Save
	**/
		/**
		 * Save Action for variation
		 * 
		 * @param int $product_id
		**/
		public function save_product_simple( $product_id )
		{

			// WP content type
			if ( ! isset( $_POST['_npuswc_product_package_type'] ) ) {
			} else {
				$_npuswc_product_package_type = $this->sanitize_npuswc_product_package_type( $_POST['_npuswc_product_package_type'] );
				update_post_meta( $product_id, '_npuswc_product_package_type', $_npuswc_product_package_type );
			}


			// WP content version
			if ( ! isset( $_POST['_npuswc_product_package_version'] ) ) {
			} else {
				$_npuswc_product_package_version_old = get_post_meta( $product_id, '_npuswc_product_package_version', true );
				$_npuswc_product_package_version_new = $this->sanitize_npuswc_product_package_version( $_POST['_npuswc_product_package_version'] );
				if ( npuswc_is_string_and_version( $_npuswc_product_package_version_new )
					&& version_compare( $_npuswc_product_package_version_old, $_npuswc_product_package_version_new, '<' )
				) {
					update_post_meta( $product_id, '_npuswc_product_package_version', $_npuswc_product_package_version_new );
				}
			}

			// Tested WP version
			if ( ! isset( $_POST['_npuswc_tested_environment_version'] ) ) {
			} else {
				$_npuswc_tested_environment_version_old = get_post_meta( $product_id, '_npuswc_tested_environment_version', true );
				$_npuswc_tested_environment_version_new = $this->sanitize_npuswc_tested_environment_version( $_POST['_npuswc_tested_environment_version'] );
				if ( npuswc_is_string_and_version( $_npuswc_tested_environment_version_new )
					&& (
						! npuswc_is_string_and_version( $_npuswc_tested_environment_version_old )
						|| version_compare( $_npuswc_tested_environment_version_old, $_npuswc_tested_environment_version_new, '<' )
					)
				) {
					update_post_meta( $product_id, '_npuswc_tested_environment_version', $_npuswc_tested_environment_version_new );
				}
			}

			// Save Action
				do_action( 'npuswc_action_save_product_simple', $product_id, $_npuswc_product_package_type );

		}

	/** 
	 * Sanitize methods
	**/
		/**
		 * Sanitize data for the post meta "_npuswc_product_package_type"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_npuswc_product_package_type( $value )
		{

			// Check the required param
			if ( ! npuswc_is_string_and_not_empty( $value )
				|| ! in_array( $value, array( 'none', 'theme', 'plugin', 'others' ) )
			) {
				return 'none';
			}

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_npuswc_product_package_version"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_npuswc_product_package_version( $value )
		{

			// Check the required param
			if ( ! npuswc_is_string_and_version( $value ) ) {
				return '';
			}

			// Sanitize
			$value = preg_replace( '/[^0-9\.]+/i', '', $value );

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_npuswc_product_package_version"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_npuswc_tested_environment_version( $value )
		{

			// Check the required param
			if ( ! npuswc_is_string_and_version( $value ) ) {
				return '';
			}

			// Sanitize
			$value = preg_replace( '/[^0-9\.]+/i', '', $value );

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_npuswc_restrict_url_access"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_npuswc_restrict_url_access( $value )
		{

			// Check the required param
			if ( is_string( $value )
				&& 'yes' === $value
			) {
				return 'yes';
			}

			// End
			return 'no';

		}

}
}
