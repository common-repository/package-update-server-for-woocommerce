<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'npuswc_is_wp_ajax' ) ) {
	/**
	 * WP AJAX or not
	**/
	function npuswc_is_wp_ajax() {

		// Case : WP AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return true;
		}

		// End
		return false;

	}
}

if ( ! function_exists( 'npuswc_is_wp_cron' ) ) {
	/**
	 * WP AJAX or not
	**/
	function npuswc_is_wp_cron() {

		// Case : WP Cron
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		// End
		return false;

	}
}

if ( ! function_exists( 'npuswc_is_working' ) ) {
	/**
	 * WCYSS is Working
	**/
	function npuswc_is_working() {

		// WCYSS DOING WORK
		return boolval( is_admin() || npuswc_is_wp_ajax() || npuswc_is_wp_cron() );

	}
}
