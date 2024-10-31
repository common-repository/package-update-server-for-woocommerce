<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Store' ) ) {
/**
 * Data formats
**/
abstract class NPUSWC_Data_Store implements NPUSWC_Data_Store_Interface {

	/**
	 * Properties
	**/
		/**
		 * Contains an instance of the data store class that we are working with.
		 */
		private $instance = null;

		/**
		 * Contains an array of default WC supported data stores.
		 * Format of object name => class name.
		 * Example: 'product' => 'WC_Product_Data_Store_CPT'
		 * You can also pass something like product_<type> for product stores and
		 * that type will be used first when available, if a store is requested like
		 * this and doesn't exist, then the store would fall back to 'product'.
		 * Ran through `woocommerce_data_stores`.
		 */
		private $stores = array(
			'product' => 'NPUSWC_Product_Data_Store',
			'setting' => 'NPUSWC_Setting_Data_Store',
			'download-jwt'     => 'NPUSWC_Download_JWT_Data_Store'
		);

		/**
		 * Contains the name of the current data store's class name.
		 */
		private $current_class_name = '';

		/**
		 * The data type this store works with.
		 * @var string
		 */
		protected $data_type = '';

		/**
		 * The object type this store works with.
		 * @var string
		 */
		protected $object_type = '';

	/**
	 * Tools
	**/
		/**
		 * Sanitize unique prefix
		 * @param  [string] $prefix
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function sanitize_unique_prefix( string $prefix, string $sep = '_' )
		{

			return strtolower( preg_replace( '/[^a-zA-Z0-9]+/i', $sep, $prefix ) );

		}

		/**
		 * Get prefixed name
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_name( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				$this->data_type,
				$name
			) ), $sep );

		}

		/**
		 * Get prefixed action hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_action_hook( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				'action',
				$this->data_type,
				$name
			) ), $sep );

		}

		/**
		 * Get prefixed filter hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_filter_hook( string $name, string $sep = '_' )
		{

			return $this->sanitize_unique_prefix( implode( $sep, array(
				npuswc()->get_prefix_key(),
				'filter',
				$this->data_type,
				$name
			) ), $sep );

		}

			/**
			 * Get prefixed attribute name
			 * @param  [string] $name
			 * @param  [string] $sep
			 * @return [string]
			**/
			public function get_prefixed_attr_name_filter_hook( string $name, string $sep = '_' )
			{

				return $this->get_prefixed_filter_hook( implode( $sep, array(
					'attr',
					$name
				) ), $sep );

			}

			/**
			 * Get prefixed prop name
			 * @param  [string] $name
			 * @param  [string] $sep
			 * @return [string]
			**/
			public function get_prefixed_prop_name_filter_hook( string $name, string $sep = '_' )
			{

				return $this->get_prefixed_filter_hook( implode( $sep, array(
					'prop',
					$name
				) ), $sep );

			}

		/**
		 * Gets a list of props and meta keys that need updated based on change state
		 * or if they are present in the database or not.
		 *
		 * @param  WC_Data $object              The WP_Data object (WC_Coupon for coupons, etc).
		 * @param  array   $meta_key_to_props   A mapping of meta keys => prop names.
		 * @param  string  $meta_type           The internal WP meta type (post, user, etc).
		 * @return array                        A mapping of meta keys => prop names, filtered by ones that should be updated.
		 */
		protected function get_props_to_update( $data, $meta_key_to_props, $meta_type = 'post' ) {
			$props_to_update = array();
			$changed_props   = $data->get_changes();

			// Props should be updated if they are a part of the $changed array or don't exist yet.
			foreach ( $meta_key_to_props as $meta_key => $prop ) {
				if ( array_key_exists( $prop, $changed_props ) || ! metadata_exists( $meta_type, $data->get_id(), $meta_key ) ) {
					$props_to_update[ $meta_key ] = $prop;
				}
			}

			return $props_to_update;
		}

	/**
	 * Init
	**/
		/**
		 * Tells WC_Data_Store which object (coupon, product, order, etc)
		 * store we want to work with.
		 *
		 * @param string $object_type Name of object.
		 *
		 * @throws Exception
		 */
		public function __construct() {

		}

		/**
		 * Only store the object type to avoid serializing the data store instance.
		 *
		 * @return array
		 */
		public function __sleep() {
			return array( 'object_type' );
		}

		/**
		 * Re-run the constructor with the object type.
		 */
		public function __wakeup() {
			$this->__construct( $this->object_type );
		}

		/**
		 * Returns the class name of the current data store.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_current_class_name() {
			return $this->current_class_name;
		}

	/**
	 * CRUD
	**/
		/**
		 * Create an object in the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 */
		public function create( &$data ) {
			//$this->create( $data );
		}

		/**
		 * Reads an object from the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 */
		public function read( &$data ) {
			//$this->read( $data );
			$data->set_object_read( true );
		}

		/**
		 * Update an object in the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 */
		public function update( &$data ) {
			//$this->update( $data );
		}

		/**
		 * Delete an object from the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 * @param array $args Array of args to pass to the delete method.
		 */
		public function delete( &$data, $args = array() ) {
			//$this->delete( $data );
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
	public function __call( $method, $parameters ) {
		if ( is_callable( array( $this->instance, $method ) ) ) {
			$object = array_shift( $parameters );
			return call_user_func_array( array( $this->instance, $method ), array_merge( array( &$object ), $parameters ) );
		}
	}

}
}
