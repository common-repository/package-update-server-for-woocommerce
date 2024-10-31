<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'NPUSWC_Notices' ) ) {
/**
 * Notices to be printed by WP Hook 'admin_notices'
 * 
 * Email with
 * 
 * 
**/
class NPUSWC_Notices {

	#
	# Properties
	#
		/**
		 * Email Handler
		 * 
		 * @var NPUSWC_Mail
		**/
		protected $mail = null;

	#
	# Static Vars
	#
		/**
		 * Instance
		 * 
		 * @var object $instance
		**/
		private static $instance = null;

		/**
		 * Idea1: Save Once as Option "npuswc_notices" or something and when Page reloaded Print Hooked in "admin_notice"
		 * 
		 * @var array $notices
		**/
		private static $notices = array();

	#
	# Consts
	#
		/**
		 * Option Field to save notices in json.
		 * 
		 * @const string
		**/
		const WP_OPTION_FIELD = 'npuswc_notices';

	#
	# Init
	#
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return Self
		**/
		public static function get_instance()
		{

			// Init if not yet
			if ( null === self::$instance ) {
				self::$instance = new Self();
			}

			// End
			return self::$instance;

		}

		/**
		 * Initializer Called by "get_instance"
		 * 
		 * @param array $args
		 * 
		 * @uses $this->init_hooks()
		**/
		protected function __construct( $args = array() )
		{

			// Get saved notices
			self::$notices = json_decode( get_option( self::WP_OPTION_FIELD, '{}' ), true );

			// Init WP hooks
			$this->init_hooks();

		}

		/**
		 * Initialize Hooks
		**/
		protected function init_hooks()
		{

			// Print notice
			add_action( 'all_admin_notices', array( $this, 'admin_notices' ) );

			// update notices
			add_action( 'shutdown', array( $this, 'save_notices' ) );

		}

		/**
		 * Print Notice
		**/
		public function admin_notices()
		{

			// Check the User Cap
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			// Case : notice are not set.
			if ( ! npuswc_is_array_and_has_values( self::$notices ) ) {
				return;
			}

			// Each Notice
			foreach ( self::$notices as $notice_name => $notice_data ) {

				// Vars
				$format = '<div class="notice %s wc-stripe-apple-pay-notice is-dismissible"><p>%s</p></div>' . PHP_EOL;
				$notice_message = $notice_data['message'];
				$notice_type = ( in_array( $notice_data['type'], array( 'warning' ) )
					? 'notice-' . $notice_data['type']
					: $notice_data['type']
				);

				// Print
				printf( $format, $notice_type, $notice_message );

			}

		}

	#
	# Getter
	#
		/**
		 * Get Saved Notices
		 * 
		 * @uese self::$notices
		 * 
		 * @return array : self::$notices
		**/
		public function get_notices()
		{

			return self::$notices;

		}

		/**
		 * Get Notice
		 * 
		 * @param str $notice_name
		 * 
		 * @return [array|bool]
		**/
		public function get_notice( $notice_name )
		{

			// Check Param
			if ( ! npuswc_is_string_and_not_empty( $notice_name ) ) {
				return false;
			}

			// Check if Notices has Message in Holder
			if ( isset( self::$notices[ $notice_name ] ) ) {
				
				// End
				return self::$notices[ $notice_name ];

			}

			// Failed
			return false;

		}

	#
	# Add
	#
		/**
		 * Set Saved Notices
		 * 
		 * @param str $notice_name
		 * @param str $notice_message 
		 * @param str $notice_type : Default "updated"
		 * 
		 * @return bool
		**/
		public function add_notice( $notice_name, $notice_message, $notice_type = 'updated' )
		{

			// Check the required params
			if ( ! npuswc_is_string_and_not_empty( $notice_message )
				|| ! npuswc_is_string_and_not_empty( $notice_name )
			) {
				return false;
			}

			// Init notice type
			if ( ! npuswc_is_string_and_not_empty( $notice_type )
				|| ! in_array( $notice_type, array( 'notice', 'warning', 'updated' ) )
			) {
				$notice_type = 'notice';
			}

			// Init notice data
			$notice = array(
				$notice_name => array(
					'message' => $notice_message,
					'type'    => $notice_type
				)
			);

			// Add the notice
			self::$notices = wp_parse_args( $notice, self::$notices );

			// Update
			return $this->save_notices();

		}

	#
	# Delete
	#
		/**
		 * Delete Notice
		 * 
		 * @param str $notice_name
		 * 
		 * @return bool
		**/
		public function delete_notice( $notice_name )
		{

			// Check Param
			if ( ! npuswc_is_string_and_not_empty( $notice_name ) ) {
				return false;
			}

			// Check if Notices has Message in Holder
			if ( isset( self::$notices[ $notice_name ] ) ) {
				
				// Unset
				unset( self::$notices[ $notice_name ] );

				// Update
				$result = $this->save_notices();

				// End
				return $result;

			}

			// Failed
			return false;

		}

	#
	# Update
	#
		/**
		 * Save the Data in type JSON String
		 * Hooked in action "shutdown" ( at the end of admin )
		 * 
		 * @return bool 
		**/
		public function save_notices()
		{

			// Check
			if ( ! is_array( self::$notices ) ) {
				update_option( self::WP_OPTION_FIELD, '{}' );
				return false;
			}

			// Vars
			$saved_data = sanitize_text_field( json_encode( self::$notices, JSON_UNESCAPED_UNICODE ) );

			// Update
			return update_option( self::WP_OPTION_FIELD, $saved_data );

		}

}
}
