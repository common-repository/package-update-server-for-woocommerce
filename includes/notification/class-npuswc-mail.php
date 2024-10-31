<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Mail' ) ) {
/**
 * Email in order to notify Admin when Tokens are expired.
**/
class NPUSWC_Mail extends NPUSWC_Mail_Base {

	#
	# Vars
	#
		/**
		 * Instance of this class
		 * 
		 * @var Self $instance
		**/
		protected static $instance = null;

	#
	# Init
	#
		/**
		 * Constructor
		 * 
		 * @param array $args
		 *
		 * @return NPUSWC_Mail
		**/
		public static function get_instance( $args = array() )
		{

			// New Instance
			self::$instance = new self( $args );

			// End
			return self::$instance;

		}

		/**
		 * Initializer Called by "getInstance"
		 * 
		 * @param array $args
		 * 
		 * @uses $this->init_hooks()
		**/
		protected function __construct( $args = array() )
		{

			// To
			if ( isset( $args['to'] ) ) {
				$this->set_to( $args['to'] );
			}

			// Headers
			if ( isset( $args['headers'] ) ) {
				$this->set_headers( $args['headers'] );
			} else {

				// From
				if ( isset( $args['from'] ) ) {
					$this->set_from( $args['from'] );
				}

				// Cc
				if ( isset( $args['cc'] ) ) {
					$this->set_cc( $args['cc'] );
				}

				// Bcc
				if ( isset( $args['bcc'] ) ) {
					$this->set_bcc( $args['bcc'] );
				}

			}

			// Subject
			if ( isset( $args['subject'] ) ) {
				$this->set_subject( $args['subject'] );
			}

			// Message
			if ( isset( $args['message'] ) ) {
				$this->set_message( $args['message'] );
			}

			// Attachments
			if ( isset( $args['attachments'] ) ) {
				$this->set_attachments( $args['attachments'] );
			}

			// Init
			$this->init_hooks();

		}

		/**
		 * Initialize Hooks
		**/
		protected function init_hooks()
		{

		}

	#
	# Send Notification Template
	#
		/**
		 * Send Refresh Token is Expired
		**/
		public function send_refresh_token_is_expired()
		{

			// Prepare
			$this->prepare_send_refresh_token_is_expired();

			// Send
			return $this->send();

		}

		/**
		 * Send Refresh Token is Expired
		**/
		public function send_exec_cron_is_failed()
		{

			// Prepare
			$this->prepare_send_exec_cron_is_failed();

			// Send
			return $this->send();

		}

	#
	# Prepare Pack
	#
		/**
		 * Prepare: Refresh Token is Expired.
		 * 
		 * @return bool by is_valid()
		**/
		public function prepare_send_token_is_expired()
		{

			// Vars
				$site_name = get_bloginfo( 'name' );
				$subject = sprintf( __( '%s - NPUSWC: Token is Expired.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), $site_name );
				$message = __( 'Token is Expired.',
					Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN
				);

			// Set
				$this->set_the_roles_to_to( array( 'administrator', 'shop_manager' ) );
				$this->set_subject( $subject );
				$this->set_message( $message );

			// Check
				return $this->is_valid();

		}

		/**
		 * Prepare: Refresh Token is Expired.
		 * 
		 * @return bool by is_valid()
		**/
		public function prepare_send_exec_cron_is_failed()
		{

			// Vars
				$site_name = get_bloginfo( 'name' );
				$subject = sprintf( __( '%s - NPUSWC: Token is Expired.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ), $site_name );
				$message = sprintf(
					__( 'Failed.',
						Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN
					),
					esc_url( add_query_arg( 'page', 'npuswc_settings_page', admin_url( 'admin.php' ) ) )
				);

			// Set
				$this->set_the_roles_to_to( array( 'administrator', 'shop_manager' ) );
				$this->set_subject( $subject );
				$this->set_message( $message );

			// Check
				return $this->is_valid();

		}

}
}
