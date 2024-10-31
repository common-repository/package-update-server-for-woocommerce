<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Option_Manager' ) ) {
/**
 * Option Manager
 * Should be initialized early
**/
class NPUSWC_Option_Manager {

	/**
	 * Vars
	**/
		/**
		 * Instance of this class
		**/
		protected static $instance = null;

	/**
	 * Properties
	**/
		/**
		 * Option Keys
		 * @var [array]
		**/
		private $option_keys = array(
			'activations',
			'package_update_server'
		);

		/**
		 * Option Keys
		 * initialized by $this->init_vars()
		 * @var [array]
		**/
		private $option_form_inputs = array();

		/**
		 * Option Keys
		 * @var [array]
		**/
		private $option_sanitizers = array(
			'activations'            => 'sanitize_option_activations',
			'package_update_server' => 'sanitize_option_package_update_server',
		);

		/**
		 * Options
		 * @var [array]
		**/
		private $options = array();

		/**
		 * Options
		 * @var [array]
		**/
		private $option_to_class = array(
			'package_update_server' => 'NPUSWC_WP_Content_Update_Checker',
		);

		/**
		 * Active extensions
		 * will set by $this->register_init()
		 * @var [array]
		**/
		private $active_extensions = array(
			'package_update_server' => 'no',
		);

		/**
		 * Options
		 * @var [array]
		**/
		private $extension_files = array(
			'package_update_server' => 'wp-content-update-checker/wp-content-update-checker.php',
		);

		/**
		 * Set to $data on construct so we can track and reset data if needed.
		 * 
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $_default_data = array(
			'activations' => array(
				'package_update_server' => 'no'
			),
			'package_update_server' => array(
				'jwt_secret_key' => ''
			)
		);

	/**
	 * Tools
	**/
		/**
		 * Sanitize unique prefix
		 * @param  [string] $prefix
		 * @param  [string] $sep
		 * @return [string]
		**/
		protected function sanitize_unique_prefix( string $prefix, string $sep = '_' )
		{

			return strtolower( preg_replace( '/[^a-zA-Z0-9]+/i', $sep, $prefix ) );

		}

		/**
		 * Get prefixed name
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		protected function get_prefixed_name( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				'option',
				$name
			) ), $sep );

		}

		/**
		 * Get prefixed action hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		protected function get_prefixed_action_hook( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				'action',
				'option',
				$name
			) ), $sep );

		}

		/**
		 * Get prefixed filter hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		protected function get_prefixed_filter_hook( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				'filter',
				'option',
				$name
			) ), $sep );

		}

	/**
	 * Initializer
	**/
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
		public function __construct()
		{

			/**
			 * init Vars
			**/
			$this->init_vars();

			/**
			 * Option keys
			**/
			//$this->reset_options();

			/**
			 * Init hooks
			**/
			//$this->init_hooks();

		}

		/**
		 * init Vars
		**/
		private function init_vars()
		{

			$this->option_form_inputs = array(
				// Activations
				'activations' => array(
					'package_update_server' => array(
						'type'        => 'checkbox',
						'name'        => 'package_update_server',
						'id'          => 'wp-content-update-checker',
						'class'       => 'wp-content-update-checker',
						'value'       => 'yes',// Only for checkbox
						'default'     => 'no',
						'label'       => __( 'Package Update Server', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
						'description' => __( 'Package Update Server.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
						'data_type'   => 'boolean',
						'regex'       => '',
						'optional'    => array( // for html attributes
							'style'       => '',
							'placeholder' => '',
							'hinting'     => '',
						),
					),
				),
				// WP Content Update Checker
				'package_update_server' => array(
					'jwt_secret_key' => array(
						'type'        => 'text',
						'name'        => 'jwt_secret_key',
						'id'          => 'jwt-secret-key',
						'class'       => 'jwt-secret-key',
						'value'       => '',// Only for checkbox
						'default'     => '',
						'label'       => __( 'Secret Key', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
						'description' => __( 'Secret Key for Token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
						'data_type'   => 'string',
						'regex'       => '/^[a-zA-Z0-9\-_]+?$/',
						'optional'    => array( // for html attributes
							'style'       => '',
							'placeholder' => '',
							'hinting'     => '',
						),
					),
				),
			);

			$this->option_to_class = apply_filters(
				$this->get_prefixed_filter_hook( 'option_to_class' ),
				$this->option_to_class
			);

		}

		/**
		 * Reset options' instance
		**/
		public function reset_options()
		{

			$this->option_keys = apply_filters( $this->get_prefixed_filter_hook( 'keys' ), $this->option_keys );
			if ( is_array( $this->option_keys ) && 0 < count( $this->option_keys ) ) {
				foreach ( $this->option_keys as $option_key ) {
					if ( is_string( $option_key ) && '' !== $option_key ) {
						//$this->options[ $option_key ] = get_option( $this->get_prefixed_name( $option_key, '' ) );
						$option_data_class = apply_filters( 
							$this->get_prefixed_filter_hook( 'data_class' ),
							'NPUSWC_Data_Option'
						);
						$this->options[ $option_key ] = new $option_data_class( $option_key );
					}
				}
			}

		}

		/**
		 * Init hooks
		**/
		public function init_hooks()
		{

			// Actions
				// Register init
				//add_action( 'npuswc_includes_extensions', array( $this, 'register_init' ) );

				// Enqueue scripts
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

				// AJAX update method
				add_action( 'wp_ajax_npuswc_update_option_data', array( $this, 'update_option_data' ) );

		}

		/**
		 * Register init of extension class
		 * Set the properties for the class object to the NPUSWC object
		 * 
		 * @param [Nora_Package_Update_Server_For_WooCommerce] $npuswc
		**/
		public function register_init( $npuswc )
		{

			foreach ( $this->options['activations']->get_data() as $extension_key => $is_active ) {

				if ( 'yes' === $is_active ) {
					$class = $this->option_to_class[ $extension_key ];
					$this->active_extensions[ $extension_key ] = 'yes';
					if ( ! class_exists( $class ) ) {
						$result = $this->require_extension_file( $extension_key );
					}

					if ( ! $result ) {
						continue;
					}

					$data_option = $this->get_data_option( $extension_key );
					if ( $data_option instanceof NPUSWC_Data_Option ) {
						$npuswc->{$extension_key} = $class::get_instance( $data_option );
					}
				}

			}

		}

		/**
		 * Includes the required file for the extension
		 * 
		 * @param [string] $extension_key
		 * 
		 * @return [void]
		**/
		public function require_extension_file( $extension_key )
		{

			if ( isset( $this->extension_files[ $extension_key ] ) ) {

				$file_path = NPUSWC_DIR_PATH . 'includes/' . $this->extension_files[ $extension_key ];
				require_once( $file_path );
				return true;

			}

			return false;

		}

		/**
		 * Triggered when the option is initialized
		 * 
		 * @param NPUSWC_Data_Option $option
		**/
		public function admin_enqueue_scripts( $hook )
		{

			if ( ! isset( $_GET['page'] )
				|| ! in_array( $_GET['page'], array( 'npuswc_admin_menu_page' ) )
			) {
				return;
			}

			wp_enqueue_style( 'npuswc-admin-setting-style' );
			wp_enqueue_script( 'npuswc-admin-setting-script' );

		}

	/**
	 * Setters
	**/
		/**
		 * Set option
		 * 
		 * @param [string] $option_key
		 * @return [NPUSWC_Data_Option|bool]
		**/
		public function set_option_prop( string $option_key, array $extra_data = array() )
		{

			$option_data_class = apply_filters( 
				$this->get_prefixed_filter_hook( 'data_class' ),
				'NPUSWC_Data_Option'
			);
			$this->options[ $option_key ] = new $option_data_class( $option_key );

		}

	/**
	 * Getters
	**/
		/**
		 * Get options
		 * 
		 * @return [array]
		**/
		public function get_options()
		{
			$options = array();
			if ( is_array( $this->options ) && 0 < count( $this->options ) ) {
				foreach ( $this->options as $option_key => $option ) {
					$options[ $option_key ] = $this->get_options( $option_key )->get_data();
				}
			}
			return $options;
		}

		/**
		 * Get option
		 * 
		 * @param [string] $option_key
		 * @return [NPUSWC_Data_Option|bool]
		**/
		public function get_data_option( $option_key )
		{

			if ( isset( $this->options[ $option_key ] )
				&& $this->options[ $option_key ] instanceof NPUSWC_Data_Option
			) {
				return $this->options[ $option_key ];
			}

			return false;

		}

		/**
		 * Get option
		 * 
		 * @param [string] $option_key
		 * @return [array|bool]
		**/
		public function get_option( $option_key )
		{

			if ( isset( $this->options[ $option_key ] )
				&& $this->options[ $option_key ] instanceof NPUSWC_Data_Option
			) {
				return $this->options[ $option_key ]->get_data();
			}

			return false;

		}

		/**
		 * Get option's default values
		 * 
		 * @param [string] $option_key
		 * 
		 * @return [array]
		**/
		public function get_option_default_values( $option_key )
		{

			$default = array();
			if ( is_array( $this->option_form_inputs[ $option_key ] ) && 0 < count( $this->option_form_inputs[ $option_key ] ) ) {
				foreach ( $this->option_form_inputs[ $option_key ] as $option_part_key => $option_part_data ) {
					$default[ $option_part_key ] = $option_part_data['default'];
				}

			}

			return $default;

		}

		/**
		 * Get option part default
		 * 
		 * @param [string] $option_key
		 * @param [string] $option_part_name
		 * 
		 * @return [mixed|bool]
		**/
		public function get_option_part_default( $option_key, $option_part_name )
		{

			$default = array();
			if ( is_array( $this->option_form_inputs[ $option_key ] ) && 0 < count( $this->option_form_inputs[ $option_key ] ) ) {
				foreach ( $this->option_form_inputs[ $option_key ] as $option_part_key => $option_part_data ) {
					if ( $option_part_name === $option_part_key ) {
						return $option_part_data['default'];
					}
				}

			}

			return false;

		}

		/**
		 * Get option_form_inputs
		 * 
		 * @param [string] $option_name 
		 * 
		 * @return [array]
		**/
		public function get_option_form_inputs( $option_name )
		{

			return $this->option_form_inputs[ $option_name ];

		}

	/**
	 * HTML
	**/


	/**
	 * AJAX
	**/
		/**
		 * Update option data
		 * 
		 * @uses $_REQUEST['option_key']
		 * @uses $_REQUEST['option_data']
		**/
		function update_option_data()
		{

			// Case : Invalid
			if ( ! isset( $_REQUEST['option_key'] )
				|| ! is_string( $_REQUEST['option_key'] )
				|| '' === $_REQUEST['option_key']
				|| ! isset( $_REQUEST['option_data'] )
				|| ! is_string( $_REQUEST['option_data'] )
				|| '' === $_REQUEST['option_data']
			) {
				wp_die(
					json_encode( array(
						'errorMessage' => __( 'No option data.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
					), JSON_UNESCAPED_UNICODE )
				);
			}

			$option_key = $_REQUEST['option_key'];
			$action     = 'npuswc_update_option_' . $option_key;
			$nonce_id   = 'npuswc_update_option_' . $option_key . '_nonce';

			// Check referer
			check_ajax_referer( $action, $nonce_id, true );

			// Case : Invalid
			if ( ! isset( $_REQUEST['option_key'] )
				|| ! is_string( $_REQUEST['option_key'] )
				|| '' === $_REQUEST['option_key']
				|| ! isset( $_REQUEST['option_data'] )
				|| ! is_string( $_REQUEST['option_data'] )
				|| '' === $_REQUEST['option_data']
			) {
				wp_die(
					json_encode( array(
						'errorMessage' => __( 'No option data.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
					), JSON_UNESCAPED_UNICODE )
				);
			}

			// Vars
			$option_name = $_REQUEST['option_key'];
			$option_data_in_json_str = $_REQUEST['option_data'];
			$option_data = json_decode( str_replace( '\"', '"', $option_data_in_json_str ), true );

			// Update
			if ( isset( $this->options[ $option_name ] ) ) {

				$this->options[ $option_name ]->set_props( $option_data );
				$this->options[ $option_name ]->save();

			}

		}

	/**
	 * Exception
	 * 
	 * Sanitizers
	**/
		public function __call( $method, $args )
		{

			/**
			 * Sanitizer
			**/
			if ( preg_match( '/^sanitize\_option\_[a-zA-Z0-9\_]+$/', $method ) ) {
				return call_user_func_array(
					array( $this, 'sanitize_option' ),
					array( $args )
				);
			}

		}

		public static function __callStatic( $name, $arguments )
		{

		}

}
}