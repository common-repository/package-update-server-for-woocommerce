<?php
if ( ! class_exists( 'NPUSWC_REST_API' ) ) {
/**
 * Auth Class
 * 
 * @todo Completing NPUSWC_JWT_Auth
 * @todo Completing NPUSWC_REST_API_Endpoints
 * 
 * Action "rest_api_init"          : Route and COR Support
 * Filter "determine_current_user" : Determine current user
 * Filter "rest_pre_dispatch"      : Rest pre dispatch
**/
class NPUSWC_REST_API {

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
		protected $type = 'basic';

		/**
		 * Endpoints
		**/
		protected $class_endpoints = 'NPUSWC_REST_API_Endpoints';

		/**
		 * Auth Public
		 *
		 * @var $this->clss_endpoints
		**/
		protected $endpoints = null;

	#
	# Vars
	#

	#
	# Init
	#
		function __construct()
		{

			// Init Endpoints
			$this->endpoints = new $this->class_endpoints( $this->plugin_name, $this->version, $this->type );

		}

	#
	# Actions
	#

	#
	# Filters
	#

	#
	# Setter
	#

	#
	# Getter
	#
		/**
		* The name of the plugin used to uniquely identify it within the context of
		* WordPress and to define internationalization functionality.
		*
		* @return string The name of the plugin.
		*/
		public function get_plugin_base_name()
		{
			return $this->plugin_base_name;
		}

		/**
		* The name of the plugin used to uniquely identify it within the context of
		* WordPress and to define internationalization functionality.
		*
		* @return string The name of the plugin.
		*/
		public function get_plugin_name()
		{
			return $this->plugin_name;
		}

		/**
		* Retrieve the version.
		*
		* @return string The version number of the plugin.
		*/
		public function get_version()
		{
			return $this->version;
		}

		/**
		* Retrieve the type.
		*
		* @return string The version number of the plugin.
		*/
		public function get_type()
		{
			return $this->type;
		}

		/**
		* Retrieve class names of NPUSWC_Rest_API_Endpoints
		*
		* @return array class
		*/
		public function get_class_endpoints()
		{
			return $this->class_endpoints;
		}

		/**
		* Retrieve class of NPUSWC_Rest_API_Endpoints
		*
		* @return NPUSWC_Rest_API_Endpoints
		*/
		public function get_endpoints()
		{
			return $this->endpoints;
		}

	#
	# Run
	#
		/**
		* Run the loader to execute all of the hooks with WordPress.
		* 
		* @uses $this->endpoints
		*/
		public function run()
		{

			// Actions
			add_action( 'rest_api_init', array( $this->endpoints, 'register_rest_routes' ) );
			add_filter( 'rest_api_init', array( $this->endpoints, 'add_cors_support' ) );

		}

}
}

