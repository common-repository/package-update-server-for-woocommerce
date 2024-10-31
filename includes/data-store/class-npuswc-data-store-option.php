<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Store_Option' ) ) {
/**
 * Data formats
**/
class NPUSWC_Data_Store_Option extends NPUSWC_Data_Store implements NPUSWC_Data_Store_Option_Interface {

	/**
	 * Properties
	**/


	/**
	 * Properties
	**/
		/**
		 * The option name this store works with.
		 * @var string
		 */
		protected $option_name = '';

		/**
		 * The option name this store works with.
		 * @var string
		 */
		protected $data_type = 'option';

		/**
		 * The option name this store works with.
		 * @var string
		 */
		protected $object_type = '';

	
	/**
	 * Tools
	**/
		/**
		 * Get unique prefix for option
		 * @param  [string] $name
		 * @return [string]
		**/
		protected function get_prefixed_option_name()
		{

			return $this->get_prefixed_name( implode( '_', array(
				$this->option_name
			) ) );

		}
	
		/**
		 * Get unique prefix for option
		 * @param  [string] $name
		 * @return [string]
		**/
		protected function get_prefixed_option_action_hook( $name = 'read' )
		{

			$sep = '_';
			$key = implode( $sep, array(
				$name,
				$this->option_name,
			) );
			return $this->get_prefixed_action_hook( $key, $sep );

		}
	

	/**
	 * Initializer
	**/
		/**
		 * Constructor
		**/
		function __construct()
		{

		}

	/**
	 * Getters
	**/
		/**
		 * Get option name
		**/
		public function get_option_name()
		{
			return $this->option_name;
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
		public function create( &$option ) {
			//$this->create( $option );

			$this->option_name = $option->get_id();

			// Option data
			$options = get_option(
				$this->get_prefixed_option_name(),
				null
			);

			// Case : Not
			if ( null === $options ) {
				$result = add_option(
					$this->get_prefixed_option_name(),
					json_encode( $option->get_default_data(), JSON_UNESCAPED_UNICODE )
				);
				if ( $result ) {

					// Action
					$action_hook = $this->get_prefixed_option_action_hook( 'create' );
					do_action( $action_hook, $option );

				}
			}
		}

		/**
		 * Reads an object from the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 */
		public function read( &$option ) {
			//$this->read( $option );

			$this->option_name = $option->get_id();

			if ( null === get_option( $this->get_prefixed_option_name(), null ) ) {
				$this->create( $option );
			}

			// Option data
			$options_in_json_str = get_option(
				$this->get_prefixed_option_name(),
				'{}'
			);

			$options = json_decode(
				$options_in_json_str,
				true
			);

			// Set
			$option->set_props( $options );
			$option->set_attr_object_read( true );

			// Action
			$action_hook = $this->get_prefixed_option_action_hook( 'read' );
			do_action( $action_hook, $option );

		}

		/**
		 * Update an object in the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 */
		public function update( &$option ) {
			//$this->update( $option );

			$this->option_name = $option->get_id();

			// Apply changes
			$option->apply_changes();
			
			// Current
			$current_options = $option->get_data();

			if ( is_array( $current_options ) ) {
				$data_in_json = json_encode( $current_options, JSON_UNESCAPED_UNICODE );
				$result = update_option(
					$this->get_prefixed_option_name(),
					$data_in_json
				);
			}

			// Action
			$action_hook = $this->get_prefixed_option_action_hook( 'update' );
			do_action( $action_hook, $option );

		}

		/**
		 * Delete an object from the data store.
		 *
		 * @since 1.0.0
		 * @param NPUSWC_Data
		 * @param array $args Array of args to pass to the delete method.
		 */
		public function delete( &$option, $args = array() ) {
			//$this->delete( $option );

			if ( '' === $this->option ) {
				$this->option_name = $option->get_id();
			}

			$result = delete_option( $option->get_prefixed_option_name() );

			// Action
			$action_hook = $this->get_prefixed_option_action_hook( 'delete' );
			do_action( $action_hook, $option );

		}


}
}


