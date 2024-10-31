<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data' ) ) {
/**
 * Data formats
**/
abstract class NPUSWC_Data {

	/**
	 * Properties
	**/
		/**
		 * ID for this object.
		 *
		 * @since 1.0.0
		 * @var [int|string]
		 */
		protected $id = 0;

		/**
		 * Attributes for this object.
		 *
		 * @since 1.0.0
		 * @var [array]
		 */
		protected $attributes = array(
			'id'          => 0, // can be string
			'object_read' => false, // This is false until the object is read from the DB.
			'data_type'   => 'data', // like 'data' 'option' 'post' 'meta'
			'object_type' => '', // like 'single' 'downloadable'
		);

		/**
		 * Core data for this object. Name value pairs (name + default value).
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $data = array();

		/**
		 * Core data changes for this object.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $changes = array();

		/**
		 * Extra data for this object. Name value pairs (name + default value).
		 * Used as a standard way for sub classes (like product types) to add
		 * additional information to an inherited class.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $extra_data = array();

		/**
		 * Set to _data on construct so we can track and reset data if needed.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $default_data = array();

		/**
		 * Contains a reference to the data store for this class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		protected $data_store;

		/**
		 * Stores additional meta data.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $meta_data = null;

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

			return $this->data_store->sanitize_unique_prefix( $prefix, $sep );

		}

		/**
		 * Get prefixed name
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_name( string $name, string $sep = '_' )
		{

			return $this->data_store->get_prefixed_name( $name, $sep );

		}

		/**
		 * Get prefixed action hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_action_hook( string $name, string $sep = '_' )
		{

			return $this->data_store->get_prefixed_action_hook( $name, $sep );

		}

		/**
		 * Get prefixed filter hook
		 * @param  [string] $name
		 * @param  [string] $sep
		 * @return [string]
		**/
		public function get_prefixed_filter_hook( string $name, string $sep = '_' )
		{

			return $this->data_store->get_prefixed_filter_hook( $name, $sep );

		}

			/**
			 * Get prefixed prop name
			 * @param  [string] $name
			 * @param  [string] $sep
			 * @return [string]
			**/
			public function get_prefixed_attr_name_filter_hook( string $name, string $sep = '_' )
			{

				return $this->data_store->get_prefixed_attr_name_filter_hook( $name, $sep );

			}

			/**
			 * Get prefixed prop name
			 * @param  [string] $name
			 * @param  [string] $sep
			 * @return [string]
			**/
			public function get_prefixed_prop_name_filter_hook( string $name, string $sep = '_' )
			{

				return $this->data_store->get_prefixed_prop_name_filter_hook( $name, $sep );

			}


	/**
	 * Init
	**/
		/**
		 * Default constructor.
		 *
		 * @param [int|object|array] $read ID to load from the DB (optional) or already queried data.
		 */
		public function __construct( $read = 0 )
		{

			$this->data         = array_merge( $this->data, $this->extra_data );
			$this->default_data = $this->data;

		}

		/**
		 * Return data changes only.
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_changes()
		{
			return $this->changes;
		}

		/**
		 * Apply changes
		**/
		public function apply_changes()
		{
			$this->data    = array_replace_recursive( $this->data, $this->changes );
			$this->changes = array();
		}

	/**
	 * CRUD
	**/
		/**
		 * Data
		**/
			/**
			 * Save the data
			 * 
			 * @uses $this->data_store->update( $this )
			 * 
			 * @return int|bool
			**/
			public function save() {

				// Check the requirements
				if ( ! $this->data_store 
					|| ! $this->data_store instanceof NPUSWC_Data_Store_Loader
				) {
					return false;
				}

				// Apply changes
				if ( method_exists( $this, 'apply_changes' ) ) {
					$this->apply_changes();
				}

				// Update
				$this->data_store->update( $this );

			}

			/**
			 * Delete an object, set the ID to 0, and return result.
			 *
			 * @since  1.0.0
			 * @param  bool $force_delete
			 * @return bool result
			 */
			public function delete( bool $force_delete = false ) {

				if ( $this->data_store ) {

					$this->data_store->delete(
						$this,
						array( 'force_delete' => $force_delete )
					);
					$this->set_id( 0 );
					return true;

				}

				return false;

			}

	/**
	 * Setters
	**/
		/**
		 * Properties
		**/
			/**
			 * Set unique prefix.
			 *
			 * @since 1.0.0
			 * @param [string] $name
			 */
			public function set_unique_prefix( string $name )
			{

				$this->unique_prefix = strtolower( preg_replace( '/[^a-zA-Z0-9]+/i', '_', $name ) );

			}

		/**
		 * Attributes
		**/
			/**
			 * Set attributes
			 * @param [array] $attributes
			 * @return [bool] 
			**/
			public function set_atts( array $atts = array() )
			{

				// Each attribute
				if ( is_array( $atts ) && 0 < count( $atts ) ) {
					foreach ( $atts as $attr_key => $attr_value ) {
						$result = $this->set_attr( $attr_key, $attr_value );
					}
				}

			}

			/**
			 * Set attribute
			 * @param [string] $key
			 * @param [mixed]  $value
			 * 
			 * @return [bool]
			**/
			public function set_attr( string $key, $value = null )
			{

				// Check the required params
				if ( ! is_string( $key ) || '' === $key ) {
					return false;
				}

				$this->attributes[ $key ] = apply_filters(
					$this->get_prefixed_prop_attr_filter_hook( $key, '_' ),
					$value,
					$this
				);

				return true;

			}

			/**
			 * Returns the unique ID for this object.
			 *
			 * @since  1.0.0
			 * @param  [int|string]
			 * @return [bool]
			 */
			public function set_id( $id = 0 )
			{

				if ( is_int( $id ) ) {
					$id = absint( $id );
				}

				if ( is_int( $id ) || is_string( $id ) || '' !== $id ) {
					return $this->set_attr( 'id', $id );
				}

				return false;

			}

			/**
			 * Get object read property.
			 *
			 * @since  1.0.0
			 * @param  [bool]
			 * @return [bool]
			 */
			public function set_attr_object_read( bool $bool = false )
			{
				return ( bool ) $this->set_attr( 'object_read' );
			}

			/**
			 * Returns data type.
			 *
			 * @since  1.0.0
			 * @param  [string]
			 * @return [bool]
			 */
			public function set_attr_data_type( string $data_type ) {
				return $this->set_attr( 'data_type', $data_type );
			}

			/**
			 * Returns object type.
			 *
			 * @since  1.0.0
			 * @param  [string] Default ''
			 * @return [bool]
			 */
			public function set_attr_object_type( string $object_type = '' )
			{
				return $this->set_attr( 'object_type', $object_type );
			}

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
			 * Set a collection of props in one go, collect any errors, and return the result.
			 * Only sets using public methods.
			 *
			 * @since  1.0.0
			 *
			 * @param  array $props Key value pairs to set. Key is the prop and should map to a setter function name.
			 * @param string $context
			 *
			 * @return bool|WP_Error
			**/
			public function set_props( $props, $context = 'set' )
			{

				//$errors = new WP_Error();

				foreach ( $props as $prop => $value ) {
					$this->set_prop( $prop, $value );
					/*
					try {
						if ( 'meta_data' === $prop ) {
							continue;
						}
						$setter = "set_$prop";
						if ( ! is_null( $value ) && is_callable( array( $this, $setter ) ) ) {
							$reflection = new ReflectionMethod( $this, $setter );

							if ( $reflection->isPublic() ) {
								$this->{$setter}( $value );
							}
						}
					} catch ( NPUSWC_Data_Exception $e ) {
						$errors->add( $e->getErrorCode(), $e->getMessage() );
					}
					*/
				}

				return true;
				//return sizeof( $errors->get_error_codes() ) ? $errors : true;

			}

			/**
			 * Gets a prop for a getter method.
			 *
			 * Gets the value from either current pending changes, or the data itself.
			 * Context controls what happens to the value before it's returned.
			 *
			 * @param  string $prop Name of prop to get.
			 * @param  string $context What the value is for. Valid values are view and edit.
			 * @return mixed
			**/
			protected function set_prop( $prop, $value )
			{

				if ( array_key_exists( $prop, $this->data ) ) {

					$value = apply_filters( $this->get_prefixed_prop_name_filter_hook( $prop, '_' ), $value, $this );

					if ( true === $this->get_attr_object_read() ) {
						if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
							$this->changes[ $prop ] = $value;
						}
					} else {
						$this->data[ $prop ] = $value;
					}
				}

			}

	/**
	 * Getters
	**/
		/**
		 * Properties
		**/
			/**
			 * Returns the unique ID for this object.
			 *
			 * @since  1.0.0
			 * @return [int|string]
			 */
			public function get_id( string $context = 'view' )
			{
				return $this->get_attr( 'id' );
			}

			/**
			 * Get the data store.
			 *
			 * @since  1.0.0
			 * @return object
			 */
			public function get_data_store( string $context = 'view' )
			{
				return $this->data_store;
			}

		/**
		 * Attributes
		**/
			/**
			 * Set attributes
			 * @param [array] $attributes
			 * @return [bool] 
			**/
			public function get_atts( string $context = 'view' )
			{

				return apply_filters( $this->get_prefixed_filter_hook( 'atts', '_' ), $this->attributes, $this );

			}

			/**
			 * Set attribute
			 * @param [string] $key
			 * @param [string] $context
			 * 
			 * @return [mixed]
			**/
			protected function get_attr( string $key, string $context = 'view' )
			{

				// Init
				$value = null;

				// Check the required params
				if ( ! is_string( $key ) || '' === $key ) {
					return $value;
				}

				$value = apply_filters(
					$this->get_prefixed_prop_attr_filter_hook( $key, '_' ),
					$this->attributes[ $key ],
					$this
				);

				return $value;

			}

			/**
			 * Returns the unique ID for this object.
			 *
			 * @since  1.0.0
			 * @return [int|string]
			 */
			public function get_attr_id( string $context = 'view' )
			{
				return $this->get_attr( 'id' );
			}

			/**
			 * Get object read property.
			 *
			 * @since  1.0.0
			 * @return boolean
			 */
			public function get_attr_object_read( string $context = 'view' )
			{
				return ( bool ) $this->get_attr( 'object_read' );
			}

			/**
			 * Returns data type.
			 *
			 * @since  1.0.0
			 * @return string
			 */
			public function get_attr_data_type( string $context = 'view' )
			{
				return $this->get_attr( 'data_type' );
			}

			/**
			 * Returns object type.
			 *
			 * @since  1.0.0
			 * @return string
			 */
			public function get_attr_object_type( string $context = 'view' )
			{
				return $this->get_attr( 'object_type' );
			}

		/**
		 * Data
		**/
			/**
			 * Get data.
			 * 
			 * @param  string $context What the value is for. Valid values are view and edit.
			 * @return mixed
			 */
			public function get_data( $context = 'view' )
			{

				return $this->data;

			}

			/**
			 * Get a prop for a getter method.
			 *
			 * Gets the value from either current pending changes, or the data itself.
			 * Context controls what happens to the value before it's returned.
			 *
			 * @param  string $prop Name of prop to get.
			 * @param  string $context What the value is for. Valid values are view and edit.
			 * @return mixed
			 */
			protected function get_prop( $prop, $context = 'view' )
			{

				$value = null;

				if ( array_key_exists( $prop, $this->data ) ) {
					$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];

					if ( 'view' === $context ) {
						$value = apply_filters( $this->get_prefixed_prop_name_filter_hook( $prop, '_' ), $value, $this );
					}
				}

				return $value;

			}

			/**
			 * Get a default data
			 * @return [array] description
			**/
			public function get_default_data()
			{
				return $this->default_data;
			}

	/**
	 * Meta
	**/
		/**
		 * Filter null meta values from array.
		 *
		 * @since  1.0.0
		 * @param mixed $meta Meta value to check.
		 * @return bool
		 */
		protected function filter_null_meta( $meta )
		{
			return ! is_null( $meta->value );
		}

		/**
		 * Get All Meta Data.
		 *
		 * @since 1.0.0
		 * @return array of objects.
		 */
		public function get_meta_data()
		{
			$this->maybe_read_meta_data();
			return array_values( array_filter( $this->meta_data, array( $this, 'filter_null_meta' ) ) );
		}

		/**
		 * See if meta data exists, since get_meta always returns a '' or array().
		 *
		 * @since  1.0.0
		 * @param  string $key
		 * @return boolean
		 */
		public function meta_exists( $key = '' )
		{
			$this->maybe_read_meta_data();
			$array_keys = wp_list_pluck( $this->get_meta_data(), 'key' );
			return in_array( $key, $array_keys );
		}

		/**
		 * Set all meta data from array.
		 *
		 * @since 1.0.0
		 * @param array $data Key/Value pairs
		 */
		public function set_meta_data( $data )
		{
			if ( ! empty( $data ) && is_array( $data ) ) {
				$this->maybe_read_meta_data();
				foreach ( $data as $meta ) {
					$meta = (array) $meta;
					if ( isset( $meta['key'], $meta['value'], $meta['id'] ) ) {
						$this->meta_data[] = new NPUSWC_Meta_Data( array(
							'id'    => $meta['id'],
							'key'   => $meta['key'],
							'value' => $meta['value'],
						) );
					}
				}
			}
		}

		/**
		 * Delete meta data.
		 *
		 * @since 1.0.0
		 * @param string $key Meta key
		 */
		public function delete_meta_data( string $key )
		{
			$this->maybe_read_meta_data();
			$array_keys = array_keys( wp_list_pluck( $this->meta_data, 'key' ), $key );

			if ( $array_keys ) {
				foreach ( $array_keys as $array_key ) {
					$this->meta_data[ $array_key ]->value = null;
				}
			}
		}

		/**
		 * Delete meta data.
		 *
		 * @since 1.0.0
		 * @param int $mid Meta ID
		 */
		public function delete_meta_data_by_mid( int $mid )
		{

			$this->maybe_read_meta_data();
			$array_keys = array_keys( wp_list_pluck( $this->meta_data, 'id' ), $mid );

			if ( $array_keys ) {
				foreach ( $array_keys as $array_key ) {
					$this->meta_data[ $array_key ]->value = null;
				}
			}

		}

		/**
		 * Read meta data if null.
		 *
		 * @since 1.0.0
		 */
		protected function maybe_read_meta_data()
		{
			if ( is_null( $this->meta_data ) ) {
				$this->read_meta_data();
			}
		}

		/**
		 * Read Meta Data from the database. Ignore any internal properties.
		 * Uses it's own caches because get_metadata does not provide meta_ids.
		 *
		 * @since 1.0.0
		 * @param bool $force_read True to force a new DB read (and update cache).
		 */
		public function read_meta_data( $force_read = false )
		{

			$this->meta_data  = array();
			$cache_loaded     = false;

			if ( ! $this->get_id() ) {
				return;
			}

			if ( ! $this->data_store ) {
				return;
			}

		}

		/**
		 * Update Meta Data in the database.
		 *
		 * @since 1.0.0
		 */
		public function save_meta_data()
		{

			if ( ! $this->data_store || is_null( $this->meta_data ) ) {
				return;
			}

			foreach ( $this->meta_data as $array_key => $meta ) {

				if ( is_null( $meta->value ) ) {

					if ( ! empty( $meta->id ) ) {

						$this->data_store->delete_meta( $this, $meta );
						unset( $this->meta_data[ $array_key ] );

					}

				} elseif ( empty( $meta->id ) ) {

					$meta->id = $this->data_store->add_meta( $this, $meta );
					$meta->apply_changes();

				} else {

					if ( $meta->get_changes() ) {

						$this->data_store->update_meta( $this, $meta );
						$meta->apply_changes();

					}

				}

			}

		}


}
}
