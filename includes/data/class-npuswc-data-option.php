<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Option' ) ) {
/**
 * Data formats
**/
class NPUSWC_Data_Option extends NPUSWC_Data implements NPUSWC_Data_Interface {

	/**
	 * Properties
	**/
		/**
		 * Attributes for this object.
		 *
		 * @since 1.0.0
		 * @var [array]
		 */
		protected $attributes = array(
			'id'          => 0, // can be string
			'object_read' => false, // This is false until the object is read from the DB.
			'data_type'   => 'option', // like 'data' 'option' 'post' 'meta'
			'object_type' => '' // like 'single' 'downloadable'
		);

		/**
		 * Set to $data on construct so we can track and reset data if needed.
		 * 
		 *
		 * @since 1.0.0
		 * @var [array]
		 */
		protected $_default_data = array(
			'activations' => array(
				'package_update_server' => 'yes'
			),
			'package_update_server' => array(
				'jwt_secret_key' => ''
			)
		);

		/**
		 * Sanitize methods
		 * 
		 *
		 * @since 1.0.0
		 * @var [NPUSWC_Sanitize_Methods]
		 */
		public $sanitize_methods = null;

	/**
	 * Tools
	**/
		/**
		 * Get prefixed name
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_name( string $name, string $sep = '_' )
		{
			return $this->data_store->get_prefixed_name( $name, $sep );
		}

		/**
		 * Get prefixed action hook
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_action_hook( string $name, string $sep = '_' )
		{
			return $this->data_store->get_prefixed_action_hook( $name, $sep );
		}

		/**
		 * Get prefixed filter hook
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_filter_hook( string $name, string $sep = '_' )
		{
			return $this->data_store->get_prefixed_filter_hook( $name, $sep );
		}

	/**
	 * Init
	**/
		/**
		 * Constructor
		 * @param [string] $option_name
		 * @param [array]  $options
		**/
		public function __construct( string $option_name = '', array $options = array() )
		{

			if ( ! is_string( $option_name ) || '' === $option_name ) {
				throw new NPUSWC_Exception_Data( __( 'Invalid params: please use a not-empty string for 1st param.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}

			// Set params
			$this->set_id( $option_name );
			$this->extra_data   = $options;
			$this->default_data = $this->_default_data[ $option_name ] ? $this->_default_data[ $option_name ] : array();
			$this->default_data = npuswc()->get_option_manager()->get_option_default_values( $option_name );
			
			$this->set_defaults();

			// Sanitizer
			$this->sanitize_methods = new NPUSWC_Sanitize_Methods();

			// Set Data Store
			$this->data_store = NPUSWC_Data_Store_Loader::load( $this->get_attr_data_type() );
			$this->data_store->read( $this );

			/**
			 * Init
			 * @param [NPUSWC_Option_Data] $this
			**/
			do_action( $this->get_prefixed_action_hook( 'init_' . $option_name ), $this );

		}

		/**
		 * Data stores can define additional functions (for example, coupons have
		 * some helper methods for increasing or decreasing usage). This passes
		 * through to the instance if that function exists.
		 *
		 * @since 1.0.0
		 *
		 * @param $method
		 * @param $parameters
		 *
		 * @return mixed
		 */
		public function __call( $method, $params ) {

			// When calling get_{$prop}( $key );
			if ( preg_match( '/get\_([a-zA-Z]+)/i', $method, $matched ) ) {
				$prop = $matched[1];
				if ( $this->has_prop( $prop ) ) {
					return call_user_func_array( array( $this, 'get_prop' ), array_merge( array( $prop, 'view' ), $params ) );
				}
				return false;
			} 

		}

	/**
	 * Data
	**/
		/**
		 * Save the data.
		 *
		 * @since 1.0.0
		 */
		public function save()
		{

			/**
			 * Saved
			 * @param [NPUSWC_Option_Data] $this
			**/
			do_action( $this->get_prefixed_action_hook( 'will_save_' . $this->get_id() ), $this );

			$result = $this->data_store->update( $this );

			/**
			 * Saved
			 * @param [NPUSWC_Option_Data] $this
			**/
			do_action( $this->get_prefixed_action_hook( 'saved_' . $this->get_id() ), $this );

		}

		/**
		 * Apply changes
		 *
		 * @since 1.0.0
		 */
		public function apply_changes()
		{

		}

	/**
	 * Setters
	**/
		/**
		 * Data
		**/
			/**
			 * Set all props to default values.
			 *
			 * @since 1.0.0
			 */
			public function set_defaults()
			{
				$this->data        = $this->default_data;
				$this->changes     = array();
				$this->set_attr_object_read( false );
			}



	/**
	 * Getters
	**/
		/**
		 * Properties
		**/

		/**
		 * Attributes
		**/

		/**
		 * Data
		**/

}
}