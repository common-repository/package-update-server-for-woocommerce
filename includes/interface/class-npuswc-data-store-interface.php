<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Product Data Store Interface
 *
 * Functions that must be defined by product store classes.
 *
 * @version  3.0.0
 * @category Interface
 */
interface NPUSWC_Data_Store_Interface {

	/**
	 * Tools
	**/
		/**
		 * Get prefixed name
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_name( string $name );

		/**
		 * Get prefixed action hook
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_action_hook( string $name );

		/**
		 * Get prefixed filter hook
		 * @param  [string] $name
		 * @return [string]
		**/
		public function get_prefixed_filter_hook( string $name );

	/**
	 * CRUD
	**/
		/**
		 * Create
		**/
		public function create( &$data );

		/**
		 * Read
		**/
		public function read( &$data );

		/**
		 * Update
		**/
		public function update( &$data );

		/**
		 * Delete
		**/
		public function delete( &$data, $args = array() );

}
