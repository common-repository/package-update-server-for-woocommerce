<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// JWT
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha512;



if ( ! class_exists( 'NPUSWC_Token_Manager' ) ) {
/**
 * 
 * 
**/
class NPUSWC_Token_Manager extends NPUSWC_Unique {

	#
	# Properties
	#
		#
		# Public
		#

		#
		# Protected
		#
			/**
			 * JWT Holder
			 * 
			 * @var array
			**/
			protected $jwt_holder = array();

	#
	# Vars
	#
		#
		# Public
		#

		#
		# Protected
		#
			/**
			 * Instance of Self
			 * 
			 * @var Self
			**/
			protected static $instance = null;

	#
	# Settings
	#


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
		 * Constructor
		**/
		protected function __construct()
		{

			// Init hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

			// Actions
				// Recieving
				add_action( 'init', array( $this, 'check_recieved_post_data' ) );

				/**
				 * self::
				 * 
				**/

				/**
				 * WC action hook "woocommerce_grant_product_download_permissions"
				 * at the end of the function "wc_downloadable_product_permissions"
				 * 
				 * @param int $order_id
				**/
				add_action( 'woocommerce_grant_product_download_permissions', array( $this, 'generate_tokens_by_order_id' ), 10, 1 );

			// Filters
				// in WC_Order_Data_Store_CPT::update()
					/**
					 * Complete
					 * 
					 * @param string ( $order->needs_processing() ? 'processing' : 'completed' )
					 * @param int $order_id
					 * @param WC_Order
					 * 
					 * @param string
					**/
					//add_filter( 'woocommerce_payment_complete_order_status', array( $this, '' ), 10, 3 ); 


					//add_action( '', 'wc_downloadable_product_permissions' );

				/**
				 * Add a JWT to the download array
				 * @param  array $downloads
				 * @param  int   $customer_id
				 * @return array
				 */
				add_filter( 'woocommerce_customer_available_downloads', array( $this, 'customer_available_downloads' ), 10, 2 );
				add_filter( 'woocommerce_order_get_downloadable_items', array( $this, 'customer_available_downloads' ), 10, 2 );

		}

		public function check_recieved_post_data()
		{

		}

	#
	# Actions
	#

	#
	# Tools
	#
		#
		# Create
		# 
		# 1. Requires :
		# 	client content version
		# 	checked content version
		# 	
		# 	
		# 2. 
		#
			/**
			 * Hooked in the WC action "woocommerce_grant_product_download_permissions"
			 * at the end of the function "wc_downloadable_product_permissions"
			 * 
			 * @param string|int : $order_id
			**/
			public function generate_tokens_by_order_id( $order_id, $requires_generate_new_download_permission = false )
			{

				try {

					// NPUSWC_Order
					$npuswc_order = new NPUSWC_Order( intval( $order_id ) );
					$result = $npuswc_order->generate_tokens();
					if ( 0 >= intval( $result ) ) {
						throw new Exception( esc_html__( 'Failed to generate tokens.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
					}

				} catch ( Exception $e ) {
					return $e->getMessage();
				}

				return $result;

			}

		#
		# Parse
		#

	#
	# Validate
	#
		/**
		 * Validate token except the expires.
		 * 		Order ID
		 *   	Product ID
		 *    	Signers
		 *     	Token Index
		 *      Indexed Token
		 * @param string|Token $token_obj
		 * @param string       $type : 'variation' 'update'
		 * @return string|Token
		**/
		public function validate_token( $token_obj, $type = 'validation' )
		{

			try {

				//
				$token_handler = new NPUSWC_Token_Handler( $token_obj );
				$token_obj = $token_handler->validate( $token_handler->get_token() );

				// Prepare var $token from ID and some params
				if ( is_string( $token_obj ) ) {
					throw new NPUSWC_Exception( 'Invalid Token.' );
				}

			} catch ( NPUSWC_Exception $e ) {
				return $e->getMessage();
			} catch ( Exception $e ) {
				return $e->getMessage();
			}

			// End
				return $token_obj;

		}

		/**
		 * Validate token expiry
		 * @param string|Token $token_obj
		 * @return string|Token
		**/
		public function validate_expiry( $token_obj )
		{

			// Prepare var $token from ID and some params
				if ( is_string( $token_obj ) && '' !== $token_obj ) {
					$token_obj = NPUSWC_Token_Methods::parse_from_string( $token_obj );
				} elseif ( is_string( $token_obj ) && '' === $token_obj ) {
					return esc_html__( 'Token is invalid string.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

				if ( ! $token_obj->hasClaim( 'order_id' ) 
					|| ! is_numeric( $token_obj->getClaim( 'order_id' ) )
					|| 0 >= intval( $token_obj->getClaim( 'order_id' ) )
					|| ! $token_obj->hasClaim( 'product_id' ) 
					|| ! is_numeric( $token_obj->getClaim( 'product_id' ) )
					|| 0 >= intval( $token_obj->getClaim( 'product_id' ) )
					|| ! $token_obj->hasClaim( 'registered_index' ) 
					|| ! is_numeric( $token_obj->getClaim( 'registered_index' ) )
					|| 0 > intval( $token_obj->getClaim( 'registered_index' ) )
				) {
					return esc_html__( 'Token does not have required params.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

			// Token ID
				$order_id = $token_obj->getClaim( 'order_id' );
				$npuswc_order = new NPUSWC_Order( $order_id );

				$product_id = $token_obj->getClaim( 'product_id' );
				$wc_product = WC()->product_factory->get_product( $product_id );
				$registered_index = $token_obj->getClaim( 'registered_index' );

				$token_id = $npuswc_order->get_registered_token_id_by_product( $product_id, $registered_index );
				$data_token = NPUSWC_Data_Token::get_instance( $token_id );

			// Expiry
				$access_expiry = intval( $data_token->get_prop( 'access_expiry' ) );
				if ( -1 !== $access_expiry
					&& $access_expiry < current_time( 'timestamp', true ) + 60
				) {

					// Messages
						$token_expired_message = esc_html__( 'Token is expired.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
						$error_messages = array(
							$token_expired_message
						);

					// End
						return implode( '<br>', $error_messages );

				}
			// End
				return $token_obj;

		}

		/**
		 * Add a JWT Column
		 *
		 * @param array  $downloads
		 * @param int $customer_id
		 *
		 * @return array
			$downloads[] = array(
				'download_url'          => add_query_arg(
					array(
						'download_file' => $product_id,
						'order'         => $result->order_key,
						'email'         => urlencode( $result->user_email ),
						'key'           => $result->download_id,
					),
					home_url( '/' )
				),
				'download_id'           => $result->download_id,
				'product_id'            => $_product->get_id(),
				'product_name'          => $_product->get_name(),
				'product_url'           => $_product->is_visible() ? $_product->get_permalink() : '', // Since 3.3.0.
				'download_name'         => $download_name,
				'order_id'              => $order->get_id(),
				'order_key'             => $order->get_order_key(),
				'downloads_remaining'   => $result->downloads_remaining,
				'access_expiry'        => $result->access_expires,
				'file'                  => array(
					'name' => $download_file->get_name(),
					'file' => $download_file->get_file(),
				),
			);
		**/
		public function customer_available_downloads( $downloads, $customer_id = 0 )
		{

			if ( ! is_int( $customer_id ) 
				&& is_object( $customer_id ) 
				&& 'WC_Order' === get_class( $customer_id ) 
				&& method_exists( $customer_id, 'get_customer_id' )
			) {
				$customer_id = intval( $customer_id->get_customer_id() );
			}

			if ( 0 === $customer_id ) {
				return $downloads;
			}

			$downloads = $this->set_jwts_to_the_downloads( $downloads, $customer_id );

			return $downloads;
			
		}

			/**
			 * Get all download jwt with data
			 *
			 * @param array &$downloads
			 * @param int   $customer_id
			 *
			 * @return array
			**/
			protected function set_jwts_to_the_downloads( &$downloads, $customer_id = 0 )
			{

				$holder = array();

				foreach ( $downloads as $download_index => &$download ) {

					// Vars
					$order_id = intval( $download['order_id'] );
					$download['npuswc_jwt'] = '';

					$wc_order = npuswc_get_order( $order_id );
					//$_npuswc_downloadable_jwts = json_decode( get_post_meta( $order_id, '_npuswc_downloadable_jwts', true ), true );
					$_npuswc_registered_token_ids = json_decode( get_post_meta( $order_id, '_npuswc_registered_token_ids', true ), true );

					// Case : JWT is saved
					if ( null === $_npuswc_registered_token_ids ) {
						continue;
					}

					// Each JWT
					foreach ( $_npuswc_registered_token_ids as $product_id => $registered_token_ids ) {

						if ( ! is_array( $registered_token_ids ) || 0 >= count( $registered_token_ids ) ) {
							continue;
						}

						foreach ( $registered_token_ids as $registered_index => $registered_token_id ) {

							try {
								$data_token = NPUSWC_Data_Token::get_instance( $registered_token_id, null, $registered_index );
							} catch ( Exception $e ) {
								continue;
							}

							$latest_token = $data_token->get_the_latest_purchased_token();
							$download['npuswc_jwt'] = $latest_token;

						}

					}

				}

				// End
				return $downloads;

			}


}
}
