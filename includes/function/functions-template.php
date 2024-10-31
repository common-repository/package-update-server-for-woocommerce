<?php

if ( ! function_exists( 'npuswc_get_template_dir' ) ) {
	/**
	 * Generate content data in json string
	 * 
	 * @param string $type : Default 'admin-page' 
	 * 
	 * @return string
	**/
	function npuswc_get_template_dir( string $type = 'admin-page' )
	{

		$template_dir = NPUSWC_DIR_PATH . 'templates/';
		$template_dir .= $type . '/';

		return $template_dir;

	}
}

if ( ! function_exists( 'npuswc_get_template_file_path' ) ) {
	/**
	 * Generate content data in json string
	 * 
	 * @param string $file_name
	 * @param string $type : Default 'admin-page' 
	 * 
	 * @return string
	**/
	function npuswc_get_template_file_path( string $file_name, string $type = 'admin-page' )
	{

		if ( ! is_string( $file_name ) || '' === $file_name ) {
			return '';
		}

		if ( false === strpos( $file_name, '.php' ) ) {
			$file_name .= '.php';
		}

		// Setup
		$dir = npuswc_get_template_dir( $type );
		$file = $dir . $file_name;

		return $file;

	}
}

if ( ! function_exists( 'npuswc_load_template' ) ) {
	/**
	 * Generate content data in json string
	 * 
	 * @param string $template_id
	 * @param array  $type        : $args
	 * 
	 * @return void
	**/
	function npuswc_load_template( string $template_id, array $args = array() )
	{

		require( npuswc_get_template_file_path( $template_id ) );

	}
}

if ( ! function_exists( 'npuswc_template_admin_menu_page_tabs' ) ) {
	/**
	 * Generate content data in json string
	 * 
	 * @param array $tabs  
	 *    string $tab_name => array
	 *      string 'id'    
	 *      string 'class'
	 *      string 'text'
	 * @param string $wrapper_class : Default 'nav-tab-wrapper woo-nav-tab-wrapper'
	 * 
	 * @return void
	 * 	
	**/
	function npuswc_template_admin_menu_page_tabs( $tabs, $wrpper_class = 'nav-tab-wrapper woo-nav-tab-wrapper' )
	{

		// Load template
		require( NPUSWC_DIR_PATH . 'template-admin-setting-header-menu.php' );

	}
}
