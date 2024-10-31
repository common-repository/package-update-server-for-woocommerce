<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Mail' ) ) {
/**
 * Email in order to notify Admin when Tokens are expired.
**/
abstract class NPUSWC_Mail_Base {

	#
	# Properties
	#
		/**
		 * E-Mail Adreess to send
		 * 
		 * @var array $to
		**/
		protected $to = array();

		/**
		 * Subject of E-Mail
		 * 
		 * @var array $subject
		**/
		protected $subject = array();

		/**
		 * E-Mail Message
		 * 
		 * @var string $message
		**/
		protected $message = '';

		/**
		 * E-Mail From-Address
		 * 
		 * @var string $from
		**/
		protected $from = '';

		/**
		 * E-Mail CC-Address
		 * 
		 * @var array $cc
		**/
		protected $cc = array();

		/**
		 * E-Mail BCC-Address
		 * 
		 * @var array $bcc
		**/
		protected $bcc = array();

		/**
		 * E-Mail Header
		 * 
		 * Example:
		 * $headers[] = 'From: Me Myself <me@example.jp>';
		 * $headers[] = 'Cc: John Q Codex <jqc@example.org>';
		 * $headers[] = 'Cc: iluvwp@example.org';
		 * 
		 * @var array $headers
		**/
		protected $headers = array();

		/**
		 * E-Mail Adreess to send
		 * 
		 * @var array $attachments
		**/
		protected $attachments = array();

	#
	# Settings
	#

	#
	# Getter
	#
		/**
		 * Get Params
		**/
		public function get_props()
		{

			// Vars
			$return = array(
				'from'    => $this->from,
				'to'      => $this->to,
				'headers' => $this->headers,
				'cc'      => $this->cc,
				'bcc'     => $this->bcc,
				'subject' => $this->subject,
				'message' => $this->message,
			);

			// End
			return $return;

		}

	#
	# Setter
	#

		#
		# To
		#
			/**
			 * Set the E-Mail To-Address 
			 * 
			 * @param array $to
			 * 
			 * @return bool
			**/
			public function set_to( $to = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $to )
				) {
					return false;
				}

				// Reset the Instance Property
				$this->to = array();

				// Set new addresses
				$result = $this->add_tos( $to );

				// End
				return true;

			}

			/**
			 * Add the E-Mail To-Addresses 
			 * 
			 * @param array $to
			 * 
			 * @return bool
			**/
			public function add_tos( $to = array() )
			{

				// Check the param
				if ( ! npuswc_is_array_and_has_values( $to )
				) {
					return false;
				}

				// Each to
				foreach ( $to as $each_to ) {

					// Case : String and not Empty
					if ( npuswc_is_string_and_not_empty( $each_to ) ) {

						// Add Address to To
						$result = $this->add_to( $each_to );

						// Case : Error
						if ( ! $result ) {
							// Error
						}

					}

				}

				// End
				return true;

			}

			/**
			 * Add an E-Mail To-Address 
			 * 
			 * @param strign $to
			 * 
			 * @return bool
			**/
			public function add_to( $to = '' )
			{

				// Check the Param and valid case
				if ( filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
					array_push( $this->to, $to );
					return true;
				}

				// Fail
				return false;

			}

				/**
				 * Set the User E-mail Addresses for To by Roles
				 * 
				 * @uses $this->add_the_roles_to_to( $roles = array() )
				 * 
				 * @param array $roles
				 * 
				 * @return bool
				**/
				public function set_the_roles_to_to( $roles = array() )
				{

					// Reset
					$this->to = array();

					// Check the Roles
					return $this->add_the_roles_to_to( $roles );

				}

				/**
				 * Add the User E-mail Addresses to To by Roles
				 * 
				 * @param array $roles : Default array()
				 * 
				 * @return bool
				**/
				public function add_the_roles_to_to( $roles = array() )
				{

					// Check the Roles
					if ( ! npuswc_is_array_and_has_values( $roles ) ) {
						return false;
					}

					// User
					$users = get_users( array( 
						'role__in' => $roles
					) );

					// User Email Addresses
					if ( npuswc_is_array_and_has_values( $users ) ) {

						// Each User
						foreach ( $users as $user ) {

							// ID
							$user_id = intval( $user->get( 'ID' ) );

							// Email
							$user_email = npuswc_get_user_email_address_by_id( $user_id );

							// Call Setter
							$result = $this->add_to( $user_email );

							// Case : Error
							if ( ! $result ) {
								// Result
							}

						}

					}

					// End
					return true;

				}

		#
		# From
		#
			/**
			 * Set the E-Mail From-Address 
			 * 
			 * @parma string $from : Default ""
			 * 
			 * @return bool
			**/
			public function set_from( $from = '' )
			{

				// Check the Param
				if ( ! npuswc_is_string_and_not_empty( $from ) ) {
					return false;
				}

				// Set as the property
				$this->from = $from;

				// End
				return true;

			}

		#
		# CC
		#
			/**
			 * Set the E-Mail CC-Address 
			 * 
			 * @param array $cc
			 * 
			 * @return bool
			**/
			public function set_cc( $cc = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $cc )
				) {
					return false;
				}

				// Reset the Property cc
				$this->cc = array();

				// Set the property cc
				$result = $this->add_ccs( $cc );

				// End
				return true;

			}

			/**
			 * Set the E-Mail CC-Address 
			 * 
			 * @param array $cc
			 * 
			 * @return bool
			**/
			public function add_ccs( $cc = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $cc ) ) {
					return false;
				}

				// Each CC
				foreach ( $cc as $each_cc ) {

					// Case : the CC is string and not empty 
					if ( npuswc_is_string_and_not_empty( $each_cc ) ) {

						// Call Setter
						$result = $this->add_cc( $each_cc );

						// Case : Error
						if ( ! $result ) {
							// Error
						}

					}

				}

				return true;

			}

			/**
			 * Add an E-Mail CC-Address 
			 * 
			 * @param strign $cc
			 * 
			 * @return bool
			**/
			public function add_cc( $cc = '' )
			{

				// Check the Param and valid case
				if ( filter_var( $cc, FILTER_VALIDATE_EMAIL ) ) {
					array_push( $this->cc, $cc );
					return true;
				}

				// End
				return false;

			}

				/**
				 * Set the User E-mail Addresses for Cc by Roles
				 * 
				 * @uses $this->add_the_roles_to_cc( $roles = array() )
				 * 
				 * @param array $roles
				 * 
				 * @return bool
				**/
				public function set_the_roles_to_cc( $roles = array() )
				{

					// Reset
					$this->cc = array();

					// Call Setter
					return $this->add_the_roles_to_cc( $roles );

				}

				/**
				 * Add the User E-mail Addresses to Cc by Roles
				 * 
				 * @param array $roles
				 * 
				 * @return bool
				**/
				public function add_the_roles_to_cc( $roles = array() )
				{

					// Check the Param
					if ( ! npuswc_is_array_and_has_values( $roles ) ) {
						return false;
					}

					// Users
					$users = get_users( array( 
						'role__in' => $roles
					) );

					// User Email Addresses
					if ( npuswc_is_array_and_has_values( $users ) ) {

						// Each User
						foreach ( $users as $user ) {

							// ID
							$user_id = intval( $user->get( 'ID' ) );

							// Email
							$user_email = npuswc_get_user_email_address_by_id( $user_id );

							// Call Setter
							$result = $this->add_cc( $user_email );

							// Case : Error
							if ( ! $result ) {
								// Error
							}

						}
					}

					// End
					return true;

				}

		#
		# BCC
		#
			/**
			 * Set the E-Mail BCC-Address 
			 * 
			 * @param array $bcc
			 * 
			 * @return bool
			**/
			public function set_bcc( $bcc = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $bcc ) ) {
					return false;
				}

				// Reset the property bcc
				$this->bcc = array();

				// Call Setter
				$result = $this->add_bccs( $bcc );

				// End
				return true;

			}

			/**
			 * Set the E-Mail CC-Address 
			 * 
			 * @param array $bcc
			 * 
			 * @return bool
			**/
			public function add_bccs( $bcc = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $bcc ) ) {
					return false;
				}

				// Each bcc
				foreach ( $bcc as $each_bcc ) {

					// Case : String and not Empty
					if ( npuswc_is_string_and_not_empty( $each_bcc ) ) {

						// Call Setter
						$result = $this->add_bcc( $each_bcc );

						// Case : Error
						if ( ! $result ) {
							// Error
						}

					}

				}

				return true;

			}

			/**
			 * Add an E-Mail CC-Address 
			 * 
			 * @param strign $bcc
			 * 
			 * @return bool
			**/
			public function add_bcc( $bcc = '' )
			{

				// Check the Param and valid case
				if ( filter_var( $bcc, FILTER_VALIDATE_EMAIL ) ) {
					array_push( $this->bcc, $bcc );
					return true;
				}

				// End
				return false;

			}

				/**
				 * Set the User E-mail Addresses for Bcc by Roles
				 * 
				 * @uses $this->add_the_roles_to_bcc( $roles = array() )
				 * 
				 * @param array $roles
				 * 
				 * @return bool
				**/
				public function set_the_roles_to_bcc( $roles = array() )
				{

					// Reset
					$this->bcc = array();

					// Call Setter
					return $this->add_the_roles_to_bcc( $roles );

				}

				/**
				 * Add the User E-mail Addresses to Bcc by Roles
				 * 
				 * @param array $roles
				 * 
				 * @return bool
				**/
				public function add_the_roles_to_bcc( $roles = array() )
				{

					// Check the Param
					if ( ! npuswc_is_array_and_has_values( $roles ) ) {
						return false;
					}

					// Users
					$users = get_users( array( 
						'role__in' => $roles
					) );

					// User Email Addresses
					if ( npuswc_is_array_and_has_values( $users ) ) {

						// Each User
						foreach ( $users as $user ) {

							// ID
							$user_id = intval( $user->get( 'ID' ) );

							// Email
							$user_email = npuswc_get_user_email_address_by_id( $user_id );

							// Add
							$result = $this->add_bcc( $user_email );

							// Case : Error
							if ( ! $result ) {
								// Error
							}

						}

					}

					// End
					return true;

				}

		#
		# Subject
		#
			/**
			 * Set the E-Mail Subject 
			 * 
			 * @param string $subject
			 * 
			 * @return bool
			**/
			public function set_subject( $subject = '' )
			{

				// Check the Param
				if ( ! npuswc_is_string_and_not_empty( $subject ) ) {
					return false;
				}

				// Set to the property subject
				$this->subject = $subject;

				// End
				return true;

			}

		#
		# Message
		#
			/**
			 * Set the E-Mail Message 
			 * 
			 * @param string $message
			 * 
			 * @return bool
			**/
			public function set_message( $message = '' )
			{

				// Check the Param
				if ( ! npuswc_is_string_and_not_empty( $message ) ) {
					return false;
				}

				// Set to the property message
				$this->message = $message;

				// End
				return true;

			}

			/**
			 * Append the E-Mail Message 
			 * 
			 * @param string $message
			 * 
			 * @return bool
			**/
			public function append_message( $message = '' )
			{

				// Check the Param
				if ( ! npuswc_is_string_and_not_empty( $message ) ) {
					return false;
				}

				// Append message with line break to property
				$this->message .= PHP_EOL;
				$this->message .= PHP_EOL;
				$this->message .= $message;
				$this->message .= PHP_EOL;

				// End
				return true;

			}

		#
		# Headers
		#
			/**
			 * Reset the E-Mail Headers 
			 * 
			 * @param string $from
			 * 
			 * @return bool
			**/
			public function set_headers( $headers = array() )
			{

				// Check the Param
				if ( ! npuswc_is_array_and_has_values( $headers )
				) {
					return false;
				}

				// Reset
				$this->headers = array();

				// Init Count
				$count = $set_count = 0;

				// Each Header
				foreach ( $headers as $header ) {

					// Count
					$count++;

					// Call Setter
					$result = $this->set_header( $header );

					// Case : Success
					if ( $result ) {
						$set_count++;
					}

				}

				// Case : All Success
				if ( $count === $set_count ) {
					return true;
				}

				// End
				return false;

			}

				/**
				 * Set the E-Mail From-Address 
				 * 
				 * @param string $from
				 * 
				 * @return bool
				**/
				public function set_header( $header = '' )
				{

					// Check the Param
					if ( ! npuswc_is_string_and_not_empty( $header ) ) {
						return false;
					}

					// Add to the property headers
					array_push( $this->headers, $header );

					// End
					return true;

				}

		#
		# Attachments
		#
			/**
			 * Set the E-Mail From-Address 
			 *
			 * @todo Complete
			 * 
			 * @param array $attachments
			**/
			public function set_attachments( $attachments = array() )
			{

				// Check the Param
				if ( ! npuswc_is_string_and_not_empty( $attachments ) ) {
					return false;
				}

				// 

			}

				/**
				 * Set the E-Mail From-Address 
				 *
				 * @todo Complete
				 * 
				 * @param string $attachment
				**/
				public function set_attachment( $attachment = '' )
				{

				}

	#
	# Sender
	#
		/**
		 * Send Action
		 * 
		 * @uses $this->is_valid()
		 * @uses $this->prepare()
		 * 
		 * @return bool
		**/
		public function send()
		{

			// Before Send
			$this->prepare();

			// Check if the Properties are Valid
			$result = $this->is_valid();
			if ( ! $result ) {
				return false;
			}

			// Send
			$send_results = array();
			if ( npuswc_is_array_and_has_values( $this->to ) ) {

				// Each To
				foreach ( $this->to as $each_to ) {

					// Send
					$send_results[] = wp_mail(
						$each_to,
						$this->subject,
						$this->message,
						$this->headers,
						$this->attachments
					);

				}

			}

			return $send_results;

		}

		/**
		 * Before Send Prepare the Properties
		 * 
		 * 
		 * 
		**/
		public function prepare()
		{

			// Reset Headers
			$headers = array();

			// From
			if ( npuswc_is_string_and_not_empty( $this->from ) ) {
				$from = 'From: ' . $this->from;
				array_push( $headers, $from );
			}

			// CC
			if ( npuswc_is_array_and_has_values( $this->cc ) ) {

				// Each CC
				foreach ( $this->cc as $each_cc ) {

					// Case the CC is Valid
					if ( npuswc_is_string_and_not_empty( $each_cc ) ) {
						$appended_cc = 'Cc: ' . $each_cc;
						array_push( $headers, $appended_cc );
					}

				}

			}

			// BCC
			if ( npuswc_is_array_and_has_values( $this->bcc ) ) {

				// Each BCC
				foreach ( $this->bcc as $each_bcc ) {

					// Case BCC is Valid
					if ( npuswc_is_string_and_not_empty( $each_bcc ) ) {
						$appended_bcc = 'Bcc: ' . $each_bcc;
						array_push( $headers, $appended_bcc );
					}
				}

			}

			// Set Headers
			$result = $this->set_headers( $headers );

			// Case Error
			if ( ! $result ) {
				// Error
				return;
			}

		}

		/**
		 * Check the Properties
		 * 
		 * @return bool
		**/
		protected function is_valid()
		{

			// To-Address
				// Array 0
				if ( ! npuswc_is_array_and_has_values( $this->to ) ) {
					return false;
				}
				// Each To
				foreach ( $this->to as $each_to ) {

					// E-Mail
					$sanitized_email = sanitize_email( $each_to );
					if ( empty( $sanitized_email ) ) {
						return false;
					}

				}

			// Message
				if ( ! npuswc_is_string_and_not_empty( $this->message ) ) {
					return false;
				}

			// From
				if ( ! npuswc_is_string_and_not_empty( $this->from ) ) {
					return false;
				}

			// Subject
				if( ! npuswc_is_string_and_not_empty( $this->subject ) ) {
					$this->subject = '';
				}

			// Headers
				// Array 0
				if ( ! npuswc_is_array_and_has_values( $this->headers ) ) {
					$this->headers = array();
				}
				// Array 
				else {

					// Holder
					$headers = array();

					// Each Header
					foreach ( $this->headers as $each_header ) {

						// Case : Header is String and not Empty
						if ( npuswc_is_string_and_not_empty( $each_header ) ) {
							array_push( $headers, $each_header );
						}

					}

					// Set the new headers as property
					if ( $this->headers !== $headers ) {
						$this->headers = $headers;
					}

				}

			// Attachments
				// Case : 0
				if ( ! npuswc_is_array_and_has_values( $this->attachments ) ) {
					$this->attachments = array();
				}
				// Case : 1 or more
				else {

					// Holder
					$attachments = array();

					// Each attachment
					foreach ( $this->attachments as $each_attachment ) {

						// Case valid
						if ( npuswc_is_string_and_not_empty( $each_attachment ) ) {
							array_push( $attachments, $each_attachment );
						}

					}

					// Set new attachments
					if ( $this->attachments !== $attachments ) {
						$this->attachments = $attachments;
					}

				}

			// End
			return true;

		}

}
}
