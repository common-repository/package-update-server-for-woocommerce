<?php

if ( ! function_exists( 'npuswc_get_data_option' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return NPUSWC_Data_Option
	**/
	function npuswc_get_data_option( $option_name )
	{

		return npuswc()->option_manager->get_data_option( $option_name );

	}
}

if ( ! function_exists( 'npuswc_get_option' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return array
	**/
	function npuswc_get_option( $option_name )
	{

		return npuswc_get_data_option( $option_name )->get_data();

	}
}

if ( ! function_exists( 'npuswc_get_options' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return NPUSWC_Data_Option
	**/
	function npuswc_get_options()
	{

		return npuswc()->option_manager->get_options();

	}
}

if ( ! function_exists( 'npuswc_get_options_default_values' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name
	 * 
	 * @return [array]
	**/
	function npuswc_get_options_default_values()
	{

		return npuswc()->get_option_manager()->get_option_default_values();

	}
}

if ( ! function_exists( 'npuswc_get_option_default_values' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name
	 * 
	 * @return [array]
	**/
	function npuswc_get_option_default_values( $option_name )
	{


		$default_data = npuswc()->get_option_manager()->get_option_default_values( $option_name );

		return $default_data[ $option_name ];

	}
}

