<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( "NPUSWC_Exception" ) ) {
/**
 * 
**/
class NPUSWC_Exception extends Exception {

	/**
	 * Consts
	**/
		const ERROR_LOG_FILE = ABSPATH . 'wp-content/uploads/npuswc/logs/error-logs.txt';

	/**
	 * Properties
	**/
		/**
		 * Protected
		**/
			/**
			 * Debug backtrace
			**/
			protected $debug_backtrace = array();

			/**
			 * Code Reference
			**/
			protected $codes = array(
				0    => 'unexpected_error',
				100  => '_error',
				200  => 'input_error',
				300  => 'input_error',
				400  => 'input_error',
				500  => 'input_error',
				600  => 'input_error',
				700  => 'input_error',
				800  => 'input_error',
				900  => 'input_error',
				1000 => 'error',
			);

	/**
	 * Init
	**/
		/**
		 * Construct
		**/
		function __construct( $message = '', $code = 0, $previous = null ) {

			$all_args       = func_get_args();
			$extra_args     = $this->get_extra_args( $all_args );
			$all_var_dumped = $this->convert_array_into_log_message( $extra_args );

			$logged_message = sprintf( "%s<br>", $message );

			//$debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 5 );
			$this->debug_backtraces = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );

			//$this->debug_backtraces = array_reverse( $this->debug_backtraces );
			$count = 0;
			foreach( $this->debug_backtraces as $debug_backtrace ) {

				$count++;
				if ( 1 === $count ) {
					continue;
				}
				$function = $debug_backtrace['function'];
				$line     = $debug_backtrace['line'];
				$file     = $debug_backtrace['file'];

				if ( isset( $debug_backtrace['class'] ) ) {
					$function = $debug_backtrace['class'] . '::' . $function;
				}

				$format = '%1$d : "<strong>%2$s line %3$d</strong>" <strong>%4$s</strong><br>';
				$current_message = sprintf(
					$format,
					$count,
					$file,
					$line,
					$function
				);

				ob_start();
					npuswc_test_var_dump( $current_message );
					//$debug_backtrace_message = $debug_backtrace;
				$debug_backtrace_message = ob_get_clean();

				//$message .= $debug_backtrace_message . PHP_EOL;
				$logged_message .= $current_message;

				//break;

			}

			$logged_message = apply_filters( 'npuswc_filter_exception_logged_message', str_replace( ABSPATH, '', $logged_message ) ) . PHP_EOL;

			do_action( 'npuswc_action_exception_init', $logged_message, $code, $previous );

			parent::__construct( $message, $code, $previous );

		}

		/**
		 * @param array $all_args
		 * @param int   $num_args
		 * @param array
		**/
		protected function get_extra_args( $all_args )
		{

			$default_num_args_construct = 3;

			if ( $default_num_args_construct >= count( $all_args ) ) {
				return array();
			}

			for ( $i = 0; $i < $default_num_args_construct; $i++ ) {
				array_shift( $all_args );
			}

			if ( 0 < count( $all_args ) ) {
				return $all_args;
			}

			return array();

		}

		/**
		 * @param array $extra_args
		**/
		protected function convert_array_into_log_message( $extra_args = array() )
		{

			ob_start();
			if ( is_array( $extra_args ) && 0 < count( $extra_args ) ) {
			foreach ( $extra_args as $extra_arg ) {
				var_dump( $extra_arg );
			}
			} else {
				var_dump( $extra_args );
			}


			$all_var_dumped = str_replace( ABSPATH, '', ob_get_clean() ) . PHP_EOL;
			return $all_var_dumped;

		}

		/**
		 * Log to the file.
		**/
		protected function get_file_replaced_trace()
		{
			$traces = parent::getTrace();
			foreach ( $traces as &$trace ) {
				$trace['file'] = str_replace( ABSPATH, '', $trace['file'] );
			}
			return $traces;
		}

		/**
		 * 
		**/
		function __toString()
		{
			return str_replace( ABSPATH, '', parent::__toString() );
		}

}
}
