<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Sanitize_Methods' ) ) {
/**
 * Option Manager
 * Should be initialized early
**/
class NPUSWC_Sanitize_Methods {

	function __construct()
	{

	}

	/**
	 * Options
	**/
		/**
		 * Sanitize option value
		 * 
		 * @param [mixed]  $input 
		 * @param [string] $option_name 
		 * @param [mixed]  $original_value  Default null
		 * 
		 * @return [string]
		**/
		public function sanitize_option( $input, $option_name, $original_value = null )
		{

			$sanitized_input = array();
			if ( is_array( $input ) ) {

				$option_parts = $this->option_form_inputs[ $option_name ] ? $this->option_form_inputs[ $option_name ] : array();

				if ( is_array( $option_parts ) && 0 < count( $option_parts ) ) {
					foreach ( $option_parts as $option_part_key => $option_part ) {

						if ( isset( $input[ $option_part_key ] ) ) {
							$value = $input[ $option_part_key ];
							if ( is_callable( array( $this, "sanitize_option_{$option_name}" ) ) ) {

							}
							$sanitized_input[ $option_] = call_user_func_array(
								array( $this, "sanitize_option_{$option_name}" ),
								array( $value, $option_part_key, $option_part )
							);
						}

					}
				}

			}
			$sanitized_input = $input;

			return $sanitized_input;

		}

		/**
		 * Sanitize option value
		 * 
		 * @param [mixed]  $input 
		 * @param [string] $option_name 
		 * @param [mixed]  $original_value  Default null
		 * 
		 * @return [string]
		**/
		public function sanitize_option_activations( $input, $option_name, $original_value = null )
		{

			$sanitized_input = array();
			foreach ( $this->option_form_inputs['activations'] as $option_part_key => $option_part ) {

				// Registered
				if ( isset( $input[ $option_part_key ] ) 
					&& isset( $option_part[ $option_part_key ] )
				) {

					// Checkbox
					if ( 'checkbox' === $option_part[ $option_part_key ]['type'] ) {
						$sanitized_input[ $option_part_key ] = npuswc_sanitize_checkbox_input( $input[ $option_part_key ] );
					} elseif ( 'boolean' === $option_part[ $option_part_key ]['data_type'] ) {
						$sanitized_input[ $option_part_key ] = boolval( $input[ $option_part_key ] ) ? 'yes' : 'no';
					}
					
				}



			}

		}

		/**
		 * Sanitize option value
		 * 
		 * @param [mixed]  $input 
		 * @param [string] $option_name 
		 * @param [mixed]  $original_value  Default null
		 * 
		 * @return [string]
		**/
		public function sanitize_option_package_update_server( $input, $option_name, $original_value = null )
		{

			$sanitized_input = array();
			foreach ( $this->option_form_inputs['package_update_server'] as $option_part_key => $option_part ) {

				// Registered
				if ( isset( $input[ $option_part_key ] ) 
					&& isset( $option_part[ $option_part_key ] )
				) {

					// Checkbox
					if ( 'checkbox' === $option_part[ $option_part_key ]['type'] ) {
						$sanitized_input[ $option_part_key ] = npuswc_sanitize_checkbox_input( $input[ $option_part_key ] );
					} elseif ( 'boolean' === $option_part[ $option_part_key ]['data_type'] ) {
						$sanitized_input[ $option_part_key ] = boolval( $input[ $option_part_key ] ) ? 'yes' : 'no';
					}
					
				}


			}

		}

}
}