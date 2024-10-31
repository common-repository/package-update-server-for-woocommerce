<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

#
# Int
#
	if ( ! function_exists( 'npuswc_is_numeric_between' ) ) {
		/**
		 * Check the int is between min and max
		 * @param int $var
		 * @param int $var
		 * @param int $var
		 * @return bool
		**/
		function npuswc_is_numeric_between( $var, $min = null, $max = null )
		{

			if ( ! is_numeric( $var )
				|| ( ! is_numeric( $min ) && ! is_numeric( $max ) )
			) {
				return false;
			}

			$var = intval( $var );
			if ( is_numeric( $min ) ) {
				if ( intval( $min ) > $var ) {
					return false;
				}
			}

			if ( is_numeric( $max ) ) {
				if ( intval( $max ) < $var ) {
					return false;
				}
			}

			return true;

		}
	}

#
# String
#
	if ( ! function_exists( 'npuswc_is_string_and_not_empty' ) ) {
		/**
		 * Check if $var is array and has values
		 * 
		 * @param mixed $var
		 * 
		 * @return bool
		**/
		function npuswc_is_string_and_not_empty( $var )
		{

			// Data
			if ( ! is_string( $var ) || 0 >= strlen( $var ) ) {
				return false;
			}

			// End
			return true;

		}
	}

	if ( ! function_exists( 'npuswc_is_string_and_version' ) ) {
		/**
		 * Check if $var is string and version
		 * 
		 * @param mixed $var
		 * 
		 * @return bool
		**/
		function npuswc_is_string_and_version( $var )
		{

			// Data
			if ( ! is_string( $var )
				|| '' === $var 
				|| preg_match( '/[^0-9\.]+/i', $var ) 
				|| 0 === strpos( $var, '.' )
				|| intval( strlen( $var ) - 1 ) === strrpos( $var, '.' )
			) {
				return false;
			}

			// End
			return true;

		}
	}

#
# Direcotry
#
	if ( ! function_exists( 'npuswc_convert_inner_file_url_into_file_path' ) ) {
		/**
		 * Convert file url into file path
		 *
		 * @param string $file_url : File URL
		 *
		 * @return string             :
		**/
		function npuswc_convert_inner_file_url_into_file_path( $file_url )
		{

			$file_path = '';

			$path = parse_url( $file_url, PHP_URL_PATH );

			//To get the dir, use: dirname($path)

			$file_path = $_SERVER['DOCUMENT_ROOT'] . $path;

			return $file_path;

		}
	}

#
# Array
#
	if ( ! function_exists( 'npuswc_array_key_last' ) ) {
		/**
		 * @param array $array
		 * @return mixed
		**/
		function npuswc_array_key_last( $array ) {

			if ( ! is_array( $array ) || empty( $array ) ) {
				return -1;
			}

			return array_keys( $array )[ count( $array ) - 1 ];

		}
	}

	if ( ! function_exists( 'npuswc_is_array_and_has_values' ) ) {
		/**
		 * Check if $var is array and has values
		 * 
		 * @param mixed $var
		 * 
		 * @return bool
		**/
		function npuswc_is_array_and_has_values( $var )
		{

			// Check the param
			if ( ! is_array( $var ) || 0 >= count( $var ) ) {
				return false;
			}

			// End
			return true;

		}
	}

	if ( ! function_exists( 'npuswc_array_insert' ) ) {
		/**
		 * Insert an array into another array before/after a certain key
		 *
		 * @param array $array The initial array
		 * @param array $pairs The array to insert
		 * @param string $key The certain key
		 * @param string $position Wether to insert the array before or after the key
		 * @return array
		 */
		function npuswc_array_insert( $array, $pairs, $key, $position = 'after' ) {
			$key_pos = array_search( $key, array_keys( $array ) );
			if ( 'after' == $position )
				$key_pos++;
			if ( false !== $key_pos ) {
				$result = array_slice( $array, 0, $key_pos );
				$result = array_merge( $result, $pairs );
				$result = array_merge( $result, array_slice( $array, $key_pos ) );
			}
			else {
				$result = array_merge( $array, $pairs );
			}
			return $result;
		}
	}

#
# CURL
#
	/*
	 * 1.Improving the security because if token is not sent in the header that sent in url, it will be logged by the network system, the server log ....
	 *
	 * 2.A good function to get Bearer tokens
	**/
	if ( ! function_exists( 'npuswc_get_authorization_header' ) ) {
		/** 
		 * Get hearder Authorization
		 * 
		 * @returnn array
		**/
		function npuswc_get_authorization_header()
		{

			$headers = null;
			if ( isset( $_SERVER['Authorization'] ) ) {

				$headers = trim($_SERVER["Authorization"]);

			}
			else if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI

				$headers = trim( $_SERVER["HTTP_AUTHORIZATION"] );

			} elseif ( function_exists( 'apache_request_headers' ) ) {

				$requestHeaders = apache_request_headers();
				// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
				$requestHeaders = array_combine(
					array_map( 'ucwords', array_keys( $requestHeaders ) ),
					array_values( $requestHeaders ) 
				);

				if ( isset( $requestHeaders['Authorization'] ) ) {
					$headers = trim( $requestHeaders['Authorization'] );
				}

			}

			return $headers;

		}
	}

	if ( ! function_exists( 'npuswc_get_bearer_token' ) ) {
		/**
		 * get access token from header
		 * 
		 * @return null|string
		**/
		function npuswc_get_bearer_token()
		{

			$headers = npuswc_get_authorization_header();
			// HEADER: Get the access token from the header
			if ( ! empty( $headers ) ) {
				if ( preg_match( '/Bearer\s(\S+)/', $headers, $matches ) ) {
					return $matches[1];
				}
			}

			return null;

		}
	}
