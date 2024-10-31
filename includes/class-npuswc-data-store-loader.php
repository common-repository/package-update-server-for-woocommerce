<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Store_Loader' ) ) {
/**
 * Data formats
**/
class NPUSWC_Data_Store_Loader {

	/**
	 * Contains an instance of the data store class that we are working with.
	 */
	private $instance = null;

	/**
	 * Contains an array of default WC supported data stores.
	 * Format of object name => class name.
	 * You can also pass something like product_<type> for product stores and
	 * that type will be used first when available, if a store is requested like
	 * this and doesn't exist, then the store would fall back to 'product'.
	 * Ran through `woocommerce_data_stores`.
	 */
	private $data_stores = array(
		'download'     => 'NPUSWC_Data_Store_Download',
		'option'       => 'NPUSWC_Data_Store_Option',
		'jwt-download' => 'NPUSWC_Data_Store_Download_JWT'
	);

	/**
	 * Contains the name of the current data store's class name.
	 * 
	 * @var [string] description
	 */
	private $current_class_name = '';

	/**
	 * The object type this store works with.
	 * @var [string]
	 */
	private $store_type = '';

	/**
	 * Magic Methods
	**/
		/**
		 * Tells WC_Data_Store which object (coupon, product, order, etc)
		 * store we want to work with.
		 *
		 * @param [string] $object_type Name of object.
		 *
		 * @throws [Exception]
		 */
		public function __construct( string $store_type )
		{

			$this->store_type = $store_type;
			$this->data_stores = apply_filters(
				'npuswc_data_stores',
				$this->data_stores
			);

			if ( array_key_exists( $store_type, $this->data_stores ) ) {
				$store = apply_filters( 'npuswc_data_store_' . $store_type, $this->data_stores[ $store_type ] );
				if ( is_object( $store ) ) {
					if ( ! $store instanceof NPUSWC_Data_Store_Interface ) {
						throw new Exception( __( 'Invalid data store.', 'woocommerce' ) );
					}
					$this->current_class_name = get_class( $store );
					$this->instance = $store;
				} else {
					if ( ! class_exists( $store ) ) {
						throw new Exception( __( 'Invalid data store.', 'woocommerce' ) );
					}
					$this->current_class_name = $store;
					$this->instance = new $store( $store );
				}
			} else {
				throw new Exception( __( 'Invalid data store.', 'woocommerce' ) );
			}

		}

		/**
		 * Only store the object type to avoid serializing the data store instance.
		 *
		 * @return [array]
		 */
		public function __sleep()
		{
			return array( 'object_type' );
		}

		/**
		 * Re-run the constructor with the object type.
		 */
		public function __wakeup()
		{
			$this->__construct( $this->object_type );
		}

		/**
		 * Data stores can define additional functions (for example, coupons have
		 * some helper methods for increasing or decreasing usage). This passes
		 * through to the instance if that function exists.
		 *
		 * @since 1.0.0
		 *
		 * @param [string] $method
		 * @param [array] $parameters
		 *
		 * @return [mixed]
		 */
		public function __call( $method, $parameters )
		{

			if ( is_callable( array( $this->instance, $method ) ) ) {

				$object = array_shift( $parameters );
				return call_user_func_array(
					array( $this->instance, $method ),
					array_merge(
						array( &$object ),
						$parameters
					)
				);

			}

		}

	/**
	 * Tools
	**/
		/**
		 * Returns the class name of the current data store.
		 *
		 * @since 1.0.0
		 * @return [string]
		 */
		public function get_current_class_name()
		{

			return $this->current_class_name;

		}

		/**
		 * Get data stores.
		 *
		 * @since 1.0.0
		 * @return [string]
		 */
		public function get_data_stores()
		{

			return $this->data_stores;

		}

	/**
	 * Init
	**/
		/**
		 * Loads a data store.
		 *
		 * @param [string] $object_type Name of object.
		 *
		 * @since 1.0.0
		 * @return [WC_Data_Store]
		 */
		public static function load( $object_type )
		{

			return new NPUSWC_Data_Store_Loader( $object_type );

		}

	/**
	 * CRUD
	**/
		/**
		 * Create an object in the data store.
		 *
		 * @since 1.0.0
		 * @param WC_Data
		 */
		public function create( &$data )
		{

			$this->instance->create( $data );

		}

		/**
		 * Reads an object from the data store.
		 *
		 * @since 1.0.0
		 * @param WC_Data
		 */
		public function read( &$data )
		{
			$this->instance->read( $data );
		}

		/**
		 * Update an object in the data store.
		 *
		 * @since 1.0.0
		 * @param WC_Data
		 */
		public function update( &$data )
		{
			$this->instance->update( $data );
		}

		/**
		 * Delete an object from the data store.
		 *
		 * @since 1.0.0
		 * @param WC_Data
		 * @param array $args Array of args to pass to the delete method.
		 */
		public function delete( &$data, $args = array() )
		{
			$this->instance->delete( $data, $args );
		}

}
}
