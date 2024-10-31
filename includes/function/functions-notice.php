<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'npuswc_doing_it_wrong' ) ) {
	/**
	 * Wrapper for _doing_it_wrong.
	 *
	 * @since 3.0.0
	 * @param string $function
	 * @param string $version
	 * @param string $replacement
	 */
	function npuswc_doing_it_wrong( $function, $message, $version )
	{

		// Get Backtrace
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		// AJAX
		if ( defined( 'NPUSWC_IS_WP_AJAX' ) && NPUSWC_IS_WP_AJAX ) {

			// Log
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );

		}
		// Regular called
		else {

			// Trigger error
			_doing_it_wrong( $function, $message, $version );

		}

	}
}

if ( ! function_exists( 'npuswc_test_var_dump' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param mixed $var
	 * @param bool  $echo : Default true
	 * 
	 * @see var_dump( $var )
	**/
	function npuswc_test_var_dump( $var, $echo = true )
	{

		// Case : Enabled debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Get Output
			ob_start();
				var_dump( $var );
			$var_dump_str = ob_get_clean();

			// Case : Echo true
			if ( boolval( $echo ) ) {
				echo '<pre>';
				echo esc_html( $var_dump_str );
				echo '</pre>';
			}

			// End
			return $var_dump_str;

		}

	}
}

if ( ! function_exists( 'npuswc_notice_message' ) ) {
	/**
	 * Add action admin notice 
	 * 
	 * @param string $notice_message : Message to be wrapped
	 * @param string $type           : 'notice', 'warning', 'updated'
	 * 
	 * @see npuswc_is_string_and_not_empty( $string )
	 * 
	 * @return string
	**/
	function npuswc_notice_message( $notice_message = '', $type = 'notice' )
	{

		// Check the param
		if ( ! is_string( $notice_message ) ) {
			ob_start();
			var_dump( $notice_message );
			$notice_message = ob_get_clean();
			ob_start();
			echo '<pre>';
			echo esc_html( $notice_message );
			echo '</pre>';
			$notice_message = ob_get_clean();
		}

		if ( ! did_action( 'all_admin_notices' ) ) {
			add_action( 'all_admin_notices', function() use ( $notice_message, $type ) {
				echo npuswc_wrap_as_notices( $notice_message, $type );
			} );
		}

	}
}

if ( ! function_exists( 'npuswc_wrap_as_notices' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param string $notice_message : Message to be wrapped
	 * @param string $type           : 'notice', 'warning', 'updated'
	 * 
	 * @see npuswc_is_string_and_not_empty( $string )
	 * 
	 * @return string
	**/
	function npuswc_wrap_as_notices( $notice_message = '', $type = 'notice' )
	{

		// Check the param
		if ( ! is_string( $notice_message ) ) {
			ob_start();
			var_dump( $notice_message );
			$notice_message = ob_get_clean();
			ob_start();
			echo '<pre>';
			echo esc_html( $notice_message );
			echo '</pre>';
			$notice_message = ob_get_clean();
		}

		// Init Message
		$format = '<div class="notice %s wc-stripe-apple-pay-notice is-dismissible"><p>%s</p></div>' . PHP_EOL;
		$notice_type = ( in_array( $type, array( 'warning' ) )
			? 'notice-' . $type
			: $type
		);
		$notice = sprintf( $format, $notice_type, $notice_message );

		// End
		return $notice;

	}
}

if ( ! function_exists( 'npuswc_current_file_and_line' ) ) {
	/**
	 * Test Var Dump
	 * 
	 * @param int  $add_line
	 * @param bool $return
	 * @param bool $add_eol
	 * 
	 * @return string
	**/
	function npuswc_current_file_and_line( $add_line = 0, $echo = true, $add_eol = true )
	{

		// Get Backtraces
		$debugtraces = debug_backtrace();
		$prev_backtrace = $debugtraces[0];

		// Vars
		$file = $prev_backtrace['file'];
		$line = intval( $prev_backtrace['line'] ) + intval( $add_line );

		// Vars
		$format = esc_html__( 'Now Here is line %1$d in file "%2$s"', 'wcyss' );
		$message = sprintf(
			$format,
			$line,
			$file
		);

		// Add End of Line
		if ( $add_eol ) {
			$message .= PHP_EOL;
		}

		// Echo if you want
		if ( $echo ) {
			echo $message;
		}

		// End
		return $message;

	}
}

if ( ! function_exists( 'npuswc_debug_append_notice' ) ) {
	/**
	 * Append message
	 * Option "npuswc_debug_notice_messages"
	 * 
	 * @param string $message
	 * 
	 * @return bool
	**/
	function npuswc_debug_append_notice( $notice_name, $notice_message, $notice_type = 'updated' )
	{

		// Case : Not string
		if ( ! is_string( $notice_message ) ) {
			ob_start();
				var_dump( $notice_message );
			$message = ob_get_clean();
		}
		else {
			if ( '' === $notice_message ) {
				return false;
			}
		}

		$notice_messages = json_decode( get_option( 'npuswc_debug_notice_messages', '{}' ), true );

		return npuswc()->admin->notices->add_notice( $notice_name, $notice_message, $notice_type );

	}
}

if ( ! function_exists( 'npuswc_debug_get_notice' ) ) {
	/**
	 * Get message
	 * Option "npuswc_debug_notice_messages"
	 * 
	 * @return array
	**/
	function npuswc_debug_get_notice( $notice_name )
	{

		return npuswc()->admin->notices->get_notice( $notice_name );

	}
}

if ( ! function_exists( 'npuswc_debug_delete_notices' ) ) {
	/**
	 * Make message
	 * Option "npuswc_debug_notice_messages"
	**/
	function npuswc_debug_delete_notices()
	{

		delete_option( 'npuswc_debug_notice_messages' );

	}

}
