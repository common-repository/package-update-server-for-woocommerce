<?php

if ( ! class_exists( 'NPUSWC_REST_API_JWT' ) ) {
/**
 * 
**/
class NPUSWC_REST_API_JWT extends NPUSWC_REST_API
{

	#
	# Properties
	#
		/**
		 * The unique identifier.
		 *
		 * @var string The string used to uniquely identify this plugin.
		**/
		protected $plugin_base_name = 'npuswc';

		/**
		 * The unique identifier.
		 *
		 * @var string The string used to uniquely identify this plugin.
		**/
		protected $plugin_name = 'npuswc';

		/**
		 * The current version.
		 *
		 * @var string The current version of the plugin.
		**/
		protected $version = '1.0.0';

		/**
		 * Type
		 *
		 * @var string The current version of the plugin.
		**/
		protected $type = 'jwt';

		/**
		 * Endpoints
		**/
		protected $class_endpoints = 'NPUSWC_REST_API_Endpoints_JWT';

		/**
		 * Auth Public
		 *
		 * @var $this->clss_endpoints
		**/
		protected $endpoints = null;

	#
	# Init
	#
		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct()
		{

			parent::__construct();

			// Init Endpoints
			$this->endpoints = new $this->class_endpoints( $this->plugin_name, $this->version );


		}

}
}
