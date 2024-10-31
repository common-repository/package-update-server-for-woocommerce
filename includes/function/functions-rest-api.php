<?php
use Lcobucci\JWT\Signer\Hmac\Sha256;

if ( ! function_exists( 'npuswc_get_rest_api' ) ) {
	/**
	 * Init specified Auth by $type
	 * 
	 * @param string $type : Auth Type
	 * 
	 * @return NPUSWC_REST_API|bool
	**/
	function npuswc_get_rest_api( $type = 'basic' )
	{

		if ( class_exists( 'NPUSWC_REST_API_Loader' ) ) {
			return NPUSWC_REST_API_Loader::load( $type );
		}

		return false;

	}
}

if ( ! function_exists( 'npuswc_run_rest_api' ) ) {
	/**
	 * Run the Auth by $type
	 * 
	 * @param string $type : Auth Type
	 * 
	 * @return NPUSWC_REST_API|NPUSWC_REST_API_{$type}
	**/
	function npuswc_run_rest_api( $type )
	{

		// Init Auth
		$auth = npuswc_get_auth( $type );
		
		// Run
		$auth->run();

		// End
		return $auth;

	}
}


