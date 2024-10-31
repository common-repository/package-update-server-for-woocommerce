<?php

use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;


if ( ! class_exists( 'NPUSWC_REST_API_Endpoints_JWT' ) ) {
/**
 * JWT Auth in Public
 * 
**/
class NPUSWC_REST_API_Endpoints_JWT extends NPUSWC_REST_API_Endpoints {

	/**
	 * Consts
	**/
		const CLIENT_VERSION_KEY  = 'client_version';
		const PACKAGE_VERSION_KEY = 'package_version';
		const TOKEN_KEY           = 'token';
		const CLIENT_URI_KEY      = 'client_uri';
	
	/**
	 * The auth type.
	**/
	protected $type = 'jwt';

	/**
	 * Constructor
	 * 
	 * @param string $plugin_name
	 * @param string $version
	 * @param string $type        : 
	**/
	function __construct( $plugin_name, $version )
	{

		// Parent
		parent::__construct( $plugin_name, $version );

		// Validate
		//add_action( 'npuswc_action_validate_token', array( $this, 'validate_jwt' ) );

		// Get content data
		//add_action( 'npuswc_action_get_package_data', array( $this, 'get_the_requested_file_data' ) );

		// Download File
		//add_action( 'npuswc_action_download_file', array( $this, 'set_download_file' ) );
 
	}

	/**
	 * Register REST routes
	 * 
	 * @param WP_REST_Server $wp_rest_server
	**/
	public function register_rest_routes( $wp_rest_server = '' )
	{

		parent::register_rest_routes( $wp_rest_server );

		// Validate token
		register_rest_route( $this->namespace, '/token/get-package-data', array(
			'methods' => 'POST',
			'callback' => array( $this, 'get_package_data' )
		) );

		// Validate token
		register_rest_route( $this->namespace, '/token/download-file', array(
			'methods' => 'POST',
			'callback' => array( $this, 'download_file' )
		) );

	}

	/**
	 * Add CORs suppot to the request.
	 * Required define const "NPUSWC_CORS_ENABLE_JWT_AUTH" to be true
	 * 
	**/
	public function add_cors_support()
	{
		parent::add_cors_support();
	}

	/**
	 * Tools
	**/
		/**
		 * Parse into params
		 * @param  WP_REST_Request $request
		 * @throws NPUSWC_Exception
		 * @return array
		**/
		protected function parse_request_into_params( $request, $post_fields_key = 'npuswc' )
		{

			// Has Required Data
				$npuswc = $request->get_param( $post_fields_key );
				if ( null === $npuswc ) {
					throw new NPUSWC_Exception( esc_html__( 'Required param doesn\'t exist.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}
			// Data
				$params = json_decode( str_replace( array( '\\"' ), '"', urldecode( $npuswc ) ), true );
				if ( ! is_array( $params ) ) {
					throw new NPUSWC_Exception( esc_html__( 'Data could not be decoded.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

			// End
				return $params;

		}

		/**
		 * Parse into params
		 * @param  array $array
		 * @throws NPUSWC_Exception
		 * @return array
		**/
		protected function end_with_array()
		{

			echo json_encode( $array, JSON_UNESCAPED_UNICODE );
			die();

		}

	/**
	 * Validation
	**/
		/**
		 * Validate
		 * 
		 * @param WP_REST_Request $request
		 * @param string          $post_fields_key
		 * 
		 * @return string|Token : Returns string for error.
		**/
		public function validate( $request, $post_fields_key = 'npuswc' )
		{

			try {

				// Params
					$params = $this->parse_request_into_params( $request, $post_fields_key );

				// Token
					$token = npuswc_get_bearer_token();
					if ( null === $token ) {
						if ( ! isset( $params[ self::TOKEN_KEY ] ) || '' === $params[ self::TOKEN_KEY ] ) {
							throw new NPUSWC_Exception( esc_html__( 'Token is not set.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
						}
						$token = $params[ self::TOKEN_KEY ];
					}

				// Validate for update checker in the certain version
					$result = $this->validate_for_each_client( $params );
					// Update checker version
					if ( is_string( $result ) ) {
						throw new NPUSWC_Exception( $result );
					}

				// Validate the JWT
					$token_obj = $this->validate_token( $params );
					if ( is_string( $token_obj ) ) {
						throw new NPUSWC_Exception( $token_obj );
					}

				// Validate the hashed URL
				do_action( 'npuswc_action_rest_api_endpoint_validate', $request, $params, $token_obj );

			} catch ( NPUSWC_Exception $e ) {

				return array(
					'error_message' => $e->getMessage(),
				);

			} catch ( Exception $e ) {

				return array(
					'error_message' => $e->getMessage(),
				);

			}

			// Maybe Update Token
				$return =  array(
					'token'            => $token_obj->__toString(),
					'token_obj'        => $token_obj,
					'expiry'           => $token_obj->hasClaim( 'access_expiry' ) ? $token_obj->getClaim( 'access_expiry' ) : -1,
					'is_token_updated' => $params[ self::TOKEN_KEY ] !== $token_obj->__toString()
				);

			// End
				return $return;

		}

		/**
		 * Protected
		**/
			/**
			 * Validate JWT Token
			 * 
			 * 1. Validate the token
			 * 2. Check the version and the expiry
			 * 3. Update the JWT and return it before the expiry if the version is updated
			 * 4. 
			 * 
			 * @param array $params
			 * 
			 * @return string|Token : Returns string for error
			**/
			protected function validate_token( $params )
			{

				if ( isset( $params[ self::TOKEN_KEY ] ) && '' !== $params[ self::TOKEN_KEY ] ) {
					$token_obj = npuswc()->get_token_manager()->validate_token( $params[ self::TOKEN_KEY ] );
				}
				if ( is_string( $token_obj ) ) {
					return $token_obj;
				}

				// Maybe update token
				$token_obj = apply_filters(
					'npuswc_filter_rest_api_maybe_update_token',
					$token_obj,
					$params
				);

				// Expire
				$token_obj = npuswc()->get_token_manager()->validate_expiry( $token_obj );
				if ( is_string( $token_obj ) ) {
					return $token_obj;
				}

				// End
					return $token_obj;

			}

			/**
			 * Validate JWT Token v1.0.0
			 * 
			 * 1. Validate the token
			 * 2. Check the version and the expiry
			 * 3. Update the JWT and return it before the expiry if the version is updated
			 * 4. 
			 * 
			 * @param array $params
			 * 
			 * @return [bool|string] : Returns string for error
			**/
			protected function validate_for_each_client( $params )
			{

				// Vars
				$client_version = $params[ self::CLIENT_VERSION_KEY ];

				// Check by the update checker version
				$client_version_in_underscore = str_replace( array( '.' ), '_', $client_version );
				$method = 'validate_for_update_checker_' . $client_version_in_underscore;
				if ( method_exists( $this, $method ) ) {
					$result = call_user_func_array(
						array( $this, $method ),
						array( $params )
					);
					if ( ! $result ) {
						$message = esc_html__( 'Error for the update checker version %1$s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
						return sprintf( $message, $client_version );
					}
					unset( $result );
				}

				return true;

			}

				/**
				 * Validate JWT Token v1.0.0
				 * 
				 * 1. Validate the token
				 * 2. Check the version and the expiry
				 * 3. Update the JWT and return it before the expiry if the version is updated
				 * 4. 
				 * 
				 * @param array $params
				 * 
				 * @return [bool|string] : Returns string for error
				**/
				protected function validate_for_update_checker_1_0_0( $params )
				{

					return true;

				}

	/**
	 * API
	**/	
		/**
		 * Get data
		**/
			/**
			 * Get requested file data if jwt is valid.
			 * Hooked in "npuswc_action_validate_token"
			 * 
			 * @param WP_REST_Request $request
			 * 
			 * @return void
			**/
			public function get_package_data( $request )
			{

				/**
				 * Validate JWT string and return JWT instance if this succeeded
				 * otherwise die.
				 *
				 * @param string $post_fields_key
				 *
				 * @return array : 
				 *   'jwt'
				 *   'hashed_url'
				 *   'client_version'
				 */
				$validation_result = $this->validate( $request, 'npuswc' );
				if ( isset( $validation_result['error_message'] ) 
					&& is_string( $validation_result['error_message'] )
					&& '' !== $validation_result['error_message'] 
				) {
					echo json_encode( $validation_result, JSON_UNESCAPED_UNICODE );
					die();
				}
				$token_obj = $validation_result['token_obj'];

				// JSON
				$package_data = $this->generate_json_for_wp_update_checker( $token_obj );
				if ( false === $package_data ) {
					echo json_encode( array(
						'error_message' => esc_html__( 'Failed to generate package data for some reason.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
					) );
				}

				// Print
				echo $package_data;

				// End
				die();

			}

				/**
				 * Generate content data in json string
				 * 
				 * @param Token  $token_obj     : Token 
				 * @param string $type          : 'theme' or 'plugin'
				 * @param string $returned_type : 'array' or 'json'
				 * @param bool   $echo          : Will echo or not, Default true
				 * 
				 * @return bool|string
				**/
				protected function generate_json_for_wp_update_checker( $token_obj, $echo = false )
				{

					try {

						$token_handler = new NPUSWC_Token_Handler( $token_obj );
						$file_name = $token_handler->data_token->get_prop( 'file_name' );
						$data_token = $token_handler->data_token;

						// Attributes
						$json_in_array = $data_token->generate_package_data_for_wp_update_checker();
						if ( is_string( $json_in_array ) || ! is_array( $json_in_array ) ) {
							throw new NPUSWC_Exception( esc_html__( 'Failed to generate package data for some reason.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
						}

						// Encode to JSON
						$json_in_string = json_encode( $json_in_array, JSON_UNESCAPED_UNICODE );
						if ( null === $json_in_string ) {
							throw new NPUSWC_Exception( esc_html__( 'Failed to JSONify package data for some reason.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
						}

					} catch ( NPUSWC_Exception $e ) {
						return false;
					}

					if ( $echo ) {
						echo $json_in_string;
					}

					// End
					return $json_in_string;

				}

		/**
		 * Download
		**/
			/**
			 * Set header for downlaod file
			 * 
			 * @param WP_REST_Request $request
			 * 
			 * @param string $jwt_string
			**/
			function download_file( $request )
			{

				//echo json_encode( $request, JSON_UNESCAPED_UNICODE ); die();
				//echo json_encode( $_SERVER, JSON_UNESCAPED_UNICODE ); die();

				try {

					/**
					 * Validate JWT string and return JWT instance if this succeeded
					 * otherwise die.
					 *
					 * @param string $post_fields_key
					 *
					 * @return array : 
					 *   'jwt'
					 *   'hashed_url'
					 *   'client_version'
					 */
					$validation_result = $this->validate( $request, 'npuswc' );
					if ( isset( $validation_result['error_message'] ) 
						&& is_string( $validation_result['error_message'] )
						&& '' !== $validation_result['error_message']
					) {
						echo json_encode( $validation_result, JSON_UNESCAPED_UNICODE );
						die();
					}
					$token_obj              = $validation_result['token_obj'];

					// Vars
						//
						$token_handler = new NPUSWC_Token_Handler( $token_obj );
						$data_token = $token_handler->data_token;
						// Path
						$product_id = intval( $data_token->get_prop( 'product_id' ) );
						$file  = $data_token->get_prop( 'file' );
						if ( ! isset( $file['name'] ) || ! is_string( $file['name'] ) || '' === $file['name'] ) {
							$this->end_with_array( array(
								'error_message' => esc_html__( 'File URL not be found', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
							) );
							exit();
						}
						$file_name = $file['name'];
						$file_ext  = (
							( is_string( $data_token->get_prop( 'file_ext' ) ) 
								|| ! in_array( $data_token->get_prop( 'file_ext' ), array( null, '' ) )
							)
							? $data_token->get_prop( 'file_ext' )
							: ''
						);

					// Download File
						$file_url  = npuswc_get_downloadable_product_file_url( $product_id, $file_name );
						$base_file_url = str_replace( trailingslashit( get_site_url() ), trailingslashit( network_site_url() ), $file_url );
						if ( false === $base_file_url ) {
							$this->end_with_array( array(
								'error_message' => esc_html__( 'File URL not be found', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
							) );
							exit();
						}
						$file_path = npuswc_convert_inner_file_url_into_file_path( $base_file_url );

					// Case External
						$site_url = get_site_url();
						if ( false === strpos( $base_file_url, network_site_url() ) ) {

							$external_file_result = wp_remote_get( $base_file_url );
							if ( is_wp_error( $external_file_result ) 
								|| ! isset( $external_file_result['body'] )
								|| '' === $external_file_result['body']
								|| false === strpos( basename( $external_file_result['file_name'] ), $file_name )
							) {
								exit;
							}

							if ( 200 === $external_file_result['response']['code'] ) {
								readfile( $base_file_url );
							}

							exit;

						}

				} catch ( NPUSWC_Exception $e ) {

					// Case : Internal 
						//$file_path = npuswc_convert_inner_file_url_into_file_path( $file_url );
						// Send download file
						if ( ! file_exists( $file_path ) ) {
							$this->end_with_array( array(
								'error_message' => esc_html__( 'File does not exist.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
							) );
							echo json_encode( array(
								'error_message' => esc_html__( 'File does not exist', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
							), JSON_UNESCAPED_UNICODE );
							exit();
						}

				}

				// Case : Internal 
					//$file_path = npuswc_convert_inner_file_url_into_file_path( $file_url );
					// Send download file
					chmod( $file_path, 0755 );
					if ( file_exists( $file_path ) ) {
						$this->send_download( $file_name, $file_path, $file_ext );
					}
					chmod( $file_path, 0644 );

				// End
					exit;

			}

			/**
			 * Set http header for sending downnload file
			 * 
			 * @param  string $file_name
			 * @param  string $file_path
			 * @param  string $file_ext
			 * @return void
			 */
			protected function send_download( $file_name, $file_path, $file_ext = '' )
			{

				// Vars
				if ( '' !== $file_ext ) {
					$file_name = $file_name . '.' . $file_ext;
				}
				$length   = sprintf( "%u", filesize( $file_path ) );

				// Headers
				header( 'Connection: Keep-Alive' );
				header( 'Expires: 0' );
				header( 'Pragma: public' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-type: application/zip' );
				header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . $length );

				// Clean
				ob_clean();
				flush();
				ob_end_flush();

				// Read file
				readfile( $file_path );

			}

}
}