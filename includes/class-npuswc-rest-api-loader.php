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
class NPUSWC_REST_API_Loader {

	#
	# Properties
	#
		/**
		 * The current version.
		 *
		 * @var string The current version of the plugin.
		**/
		protected $version = '1.0.0';

		/**
		 * Auth type
		 *
		 * @var string The current version of the plugin.
		**/
		private $type = 'basic';

		/**
		 * Endpoints
		**/
		private static $class_rest_api = array(
			'basic'     => 'NPUSWC_REST_API',
			'order_jwt' => 'NPUSWC_REST_API_JWT'
		);

		/**
		 * Auth Public
		 *
		 * @var $this->clss_endpoints
		**/
		protected $endpoints = null;

	/**
	 * 
	**/
		public static function load( $type )
		{

			// Endpoints
			if ( isset( self::$class_rest_api[ $type ] ) ) {
				$class_rest_api = self::$class_rest_api[ $type ];

				// Init
				return new $class_rest_api();

			}

			return false;

		}


}
}