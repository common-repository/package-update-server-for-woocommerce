<?php

if ( ! function_exists( 'npuswc_make_temp_file' ) ) {
	/**
	 * Make Temp File
	 * 
	 * @param string $file_name
	 * 
	 * @return NPUSWC_Temp_File
	**/
	function npuswc_make_temp_file( $file_name )
	{

		$temp_file = new NPUSWC_Temp_File( $file_name );
		return $temp_file;

	}
}

if ( ! function_exists( 'npuswc_get_dir_path' ) ) {
	/**
	 * Get the directory path
	 * 
	 * @param string $dir
	 * 
	 * @return string
	**/
	function npuswc_get_dir_path( $dir = '' )
	{

		if ( ! is_string( $dir ) || '' === $dir ) {
			return '';
		} else {
			$dir_path = trailingslashit( NPUSWC_DIR_PATH . $dir );
		}

		return $dir_path;

	}
}

if ( ! function_exists( 'npuswc_get_dir_url' ) ) {
	/**
	 * Get URL of the directory
	 * 
	 * @param string $dir
	 * 
	 * @return string
	**/
	function npuswc_get_dir_url( $dir )
	{

		if ( ! is_string( $dir ) || '' === $dir ) {
			return '';
		} else {
			$dir_path = trailingslashit( NPUSWC_DIR_URL . $dir );
		}

		return $dir_path;

	}
}



