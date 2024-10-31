<?php
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;


if ( ! class_exists( 'NPUSWC_REST_API_Endpoints' ) ) {
/**
 * Auth in Public
**/
class NPUSWC_REST_API_Endpoints {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The current version of this plugin.
	 */
	protected $version;

	/**
	 * The namespace to add to the api calls.
	 *
	 * @var string The namespace to add to the api call
	 */
	protected $namespace;

	/**
	 * The auth type.
	**/
	protected $type = 'basic';

	/**
	 * Store errors to display if the JWT is wrong
	 *
	 * @var WP_Error
	 */
	protected $jwt_error = null;

	/**
	 * Methods
	 * 
	 * @var array
	**/
	protected $api_methods = array();

	/**
	 * Constructor
	 * 
	 * @param string $plugin_name
	 * @param string $version
	 * @param string $type        : 
	**/
	function __construct( $plugin_name, $version )
	{

		// Properties
			// Params
			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			// namespace
			$this->namespace = $this->plugin_name . '/v' . intval( $this->version );

		// Init
		$this->init_hooks();

	}

	/**
	 * Init WP Hooks
	**/
	function init_hooks()
	{

		// Actions
		//add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		//add_action( 'rest_api_init', array( $this, 'add_cors_support' ) );

	}

	/**
	 * Register REST routes
	 * 
	 * @param WP_REST_Server $wp_rest_server
	**/
	public function register_rest_routes( $wp_rest_server = '' )
	{

	}

	/**
	 * Add CORs suppot to the request.
	 * Required define const "NPUSWC_CORS_ENABLE_JWT_AUTH" to be true
	**/
	public function add_cors_support()
	{

		// Enable CORs
		$enable_cors = true;//defined( 'NPUSWC_CORS_ENABLE_JWT_AUTH' ) ? NPUSWC_CORS_ENABLE_JWT_AUTH : false;
		if ( $enable_cors ) {
			$headers = apply_filters( 
				'npuswc_filter_cors_allow_headers',
				'Access-Control-Allow-Headers, Content-Type, Authorization'
			);
			header( sprintf( 'Access-Control-Allow-Headers: %s', $headers ) );
		}

	}

}
}

