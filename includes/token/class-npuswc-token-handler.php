<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Token_Handler' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Token_Handler {

	/**
	 * Token initialized
	**/
	protected $is_token_init = false;

	/**
	 * Token initialized
	**/
	protected $is_the_latest = true;

	/**
	 * Token Object
	**/
	protected $token;

	/**
	 * NPUSWC_Token_Validator
	**/
	protected $validator = null;

	/**
	 * NPUSWC_Order
	**/
	public $npuswc_order = null;

	/**
	 * WC_Product
	**/
	public $wc_product = null;

	/**
	 * NPUSWC_Data_Token
	**/
	public $data_token = null;




	/**
	 * Flag if validating token is done or not
	**/
	protected $validating_token_done = false;

	/**
	 * Flag if validating expiry is done or not
	**/
	protected $validating_expiry_done = false;

	/**
	 * Getters
	**/
		/**
		 * General
		**/
			/**
			 * Get Token Object
			 * @return Token
			**/
			public function get_token()
			{

				$this->token;

				$token = $this->data_token->get_the_latest_purchased_token();
				try {
					$token_obj = NPUSWC_Token_Methods::parse_from_string( $token );
				} catch ( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return $token_obj;
			}

		/**
		 * Validation Token
		**/
			/**
			 * Has Expiry
			 * @return bool
			**/
			public function has_expiry()
			{
				if ( ! $this->token->hasClaim( 'access_expiry' )
					|| -1 === intval( $this->token->getClaim( 'access_expiry' ) )
				) {
					return false;
				} 
				return true;
			}

			/**
			 * Access Expiry
			 * @return int
			**/
			public function get_access_expiry()
			{

				if ( ! $this->token->hasClaim( 'access_expiry' )
					|| -1 === intval( $this->token->getClaim( 'access_expiry' ) )
				) {
					return -1;
				}

				$access_expiry = $this->token->getClaim( 'access_expiry' );
				return $access_expiry;

			}

			/**
			 * Token Value
			 * @return string
			**/
			public function get_token_value()
			{
				$token_index = $this->data_token->get_purchased_token_index( $this->get_token()->__toString() );
				$token_value = $this->data_token->get_used_download_key( $token_index );
				return $token_value;
			}

	/**
	 * Init
	**/
		/**
		 * Constructor
		 * @param string|Token $token_obj
		**/
		public function __construct( $token_obj )
		{

			if ( '' === $token_obj ) {
				throw new NPUSWC_Exception( 'param token object is empty string.' );
			}

			// Parent WC_Order
			$this->init_vars( $token_obj );
			$this->maybe_update();
			$this->init_objs( $token_obj );

		}

		/**
		 * Init Vars
		 * @param string|Token $toke_obj
		**/
		protected function init_vars( $token_obj )
		{

			// Prepare var $token from ID and some params
			if ( is_string( $token_obj ) ) {
				$token_obj = NPUSWC_Token_Methods::parse_from_string( $token_obj );
			}

			// Validate
			$this->validator = new NPUSWC_Token_Validator( $token_obj );
			$token_obj = $this->validator->validate();
			if ( is_string( $token_obj ) ) {
				throw new NPUSWC_Exception( esc_html__( 'Token is not the object : ' . $token_obj, Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}

			$this->is_token_init = true;
			$this->token = $token_obj;

			if ( ! $this->token->hasClaim( 'order_id' )
				|| ! is_numeric( $this->token->getClaim( 'order_id' ) )
				|| 0 >= intval( $this->token->getClaim( 'order_id' ) )
				|| ! $this->token->hasClaim( 'product_id' ) 
				|| ! is_numeric( $this->token->getClaim( 'product_id' ) )
				|| 0 >= intval( $this->token->getClaim( 'product_id' ) )
				|| ! $this->token->hasClaim( 'registered_index' ) 
				|| ! is_numeric( $this->token->getClaim( 'registered_index' ) )
				|| 0 > intval( $this->token->getClaim( 'registered_index' ) )
			) {
				throw new NPUSWC_Exception( __( 'Token does not have required params.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}
			$order_id   = intval( $this->token->getClaim( 'order_id' ) );
			$this->npuswc_order = new NPUSWC_Order( $order_id );
			if ( false === $this->npuswc_order
				|| is_string( $this->npuswc_order )
			) {
				throw new NPUSWC_Exception( __( 'Invalid Order.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}

			$product_id = intval( $this->token->getClaim( 'product_id' ) );
			$this->wc_product = WC()->product_factory->get_product( $product_id );
			if ( false === $this->wc_product
				|| is_string( $this->wc_product )
			) {
				throw new NPUSWC_Exception( __( 'Invalid Product.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}

			$registered_id_index = $token_obj->hasClaim( 'registered_index' ) ? $token_obj->getClaim( 'registered_index' ) : 0;
			$registered_token_id = $this->npuswc_order->get_registered_token_id_by_product( $this->wc_product, $registered_id_index );
			$this->data_token = NPUSWC_Data_Token::get_instance( $registered_token_id );
			if ( false === $this->data_token
				|| is_string( $this->data_token )
			) {
				throw new NPUSWC_Exception( __( 'Invalid Token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}

		}

		/**
		 * Update Token if the later version exists
		**/
		protected function maybe_update()
		{

			$new_token = $this->get_the_latest_token();
			if ( null === $new_token || is_string( $new_token ) ) {
				return;
			}

			$new_token_obj = $this->validate( $new_token );
			if ( is_string( $new_token_obj ) ) {
				return;
			}

			$this->token = $new_token_obj;
			$this->is_the_latest = true;

		}

		/**
		 * Init Vars
		 * @param string|Token $token_obj
		**/
		protected function init_objs( $token_obj )
		{

		}

	/**
	 * Validate
	**/
		/**
		 * Validate the token
		 * @param string|Token $token : Default ''
		 * @return bool|Token
		**/
		public function validate( $token = '' )
		{

			try {
				$another_validator = new NPUSWC_Token_Validator( $token );
				$token_obj = $another_validator->validate();
			} catch ( NPUSWC_Exception $e ) {
				$token_obj = $this->validate( $this->token );
			}

			return $token_obj;
		}

	/**
	 * Update Tokens
	**/
		/**
		 * Need to update exipry
		 * @return bool
		**/
		public function need_update_expiry()
		{

			$update_expiry           = $this->data_token->get_prop( 'update_expiry' );
			$token_package_version   = $this->data_token->get_prop( 'package_version' );
			$current_package_version = get_post_meta( $this->wc_product->get_id(), '_npuswc_product_package_version', true );

			if ( 'yes' === $update_expiry
				&& version_compare( $token_package_version, $current_package_version, '<' )
			) {
				return true;
			}

			return false;

		}

		/**
		 * Get updated access expiry
		 * @return int
		**/
		protected function get_updated_access_expiry_base()
		{

			$date_updated_base = 0;

			$access_expiry  = intval( $this->data_token->get_prop( 'access_expiry' ) );
			$date_updated = intval( $this->data_token->get_prop( 'date_package_updated' ) );
			if ( false === $date_updated ) {
				$date_updated = 0;
			}

			if ( $access_expiry > $date_updated ) {
				$date_updated_base = $access_expiry;
			}

			$extending_days = intval( $this->data_token->get_prop( 'extended_expiry_in_day' ) );
			if ( -1 === $extending_days ) {
				$access_expiry = -1;
			} else {
				$access_expiry = $date_updated_base + ( $extending_days * DAY_IN_SECONDS );
			}

			return $access_expiry;

		}

		/**
		 * Extends the expiry
		 * @return string|Token
		**/
		public function extend_expiry()
		{

			// Token Version
				if ( ! $this->is_latest_token( $this ) ) {
					//$this->maybe_update();
					return esc_html__( 'Token is not the latest.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

				$need_update_expiry = $this->need_update_expiry();
				if ( ! $need_update_expiry ) {
					return esc_html__( 'No need update expiry.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

			// Access Expiry
//				$token_params = $this->data_token->get_data();
//				$extended_expiry = $this->get_updated_access_expiry_base();

				try {
					$new_token = $this->data_token->update_expiry();
				} catch ( NPUSWC_Exception $e ) {
					return $e->getMessage();
				}

				$this->maybe_update();

			// End
				return $new_token;

		}

		/**
		 * Update the Expiry
		 * @param array $new_params
		 * @return string|Token
		**/
		protected function update_the_access_expiry( $new_params = array() )
		{

			$result = $this->data_token->update_expiry( $new_token );
			if ( is_string( $result ) ) {
				throw new NPUSWC_Exception( $result );
			}

			return $this->data_token->get_the_latest_purchased_token();

		}

	/**
	 * Invalidate the Update Token
	**/
		/**
		 * Invalidate the Update Token
		 * @param NPUSWC_Token_Handler $validation_token_handler description
		 * @return bool
		**/
		public function invalidate_token( $validation_token_handler )
		{

			try {

				$decreased = $this->data_token->decrease_purchased_number( 1 );
				if ( false === $decreased ) {
					throw new NPUSWC_Exception( esc_html__( 'Failed to decrease the number of updated token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

				if ( 0 === $decreased ) {

					$result = $this->delete_the_update_token();
					if ( is_string( $result ) ) {
						throw new NPUSWC_Exception( esc_html__( 'Failed to delete the updated token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
					}

				}

			} catch ( NPUSWC_Exception $e ) {

				$this->cancel_updated_new_token( $validation_token_handler );
				return false;

			}

			return true;

		}

		/**
		 * Invalidate the Update Token
		 * @return bool|string Returns string for errors.
		**/
		protected function delete_the_update_token()
		{

			try {

				// Vars
					$order_id   = $this->npuswc_order->get_id();
					$product_id = $this->wc_product->get_id();


					$result = $this->npuswc_order->delete_registered_token_id_by_product( $product_id, $this->data_token->get_registered_index() );
					if ( is_string( $result ) ) {
						throw new NPUSWC_Exception( $result );
					}

					$result = $this->data_token->delete_the_update_token();
					if ( is_string( $result ) ) {
						throw new NPUSWC_Exception( $result );
					}

			} catch ( NPUSWC_Exception $e ) {
				return $e->getMessage();
			}

			// End
				return true;

		}

		/**
		 * Invalidate the Update Token
		 * @param NPUSWC_Token_Handler $validation_token_handler
		 * @return bool
		**/
		protected function cancel_updated_new_token( $validation_token_handler )
		{

			if ( ! $validation_token_handler->is_latest_token() ) {
				return false;
			}

			try {

				$result = $validation_token_handler->data_token->cancel_updated_new_token();
				if ( is_string( $result ) ) {
					throw new NPUSWC_Exception( esc_html__( 'Failed to cancel the updated validation token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

			} catch ( NPUSWC_Exception $e ) {
				return false;
			}

			// End
				return true;

		}

		/**
		 * Call
		 * @param callable $method
		 * @param array    $args
		 * @return mixed
		**/
		function __call( $method, $args )
		{

			if ( 'NPUSWC_Order' === get_class( $this->npuswc_order )
				&& method_exists( $this->npuswc_order, $method )
				&& is_callable( array( $this->npuswc_order, $method ) ) 
			) {
				return call_user_func_array(
					array( $this->npuswc_order, $method ),
					$args
				);
			}

			return null;

		}

		/**
		 * Get the token index
		**/
		protected function get_the_token_index()
		{

			// Check if order items exist
			return $this->data_token->get_purchased_token_index( $this->token->__toString() );

		}

		/**
		 * Get the latest purchased token
		 * @return string
		**/
		protected function get_the_latest_token()
		{

			// Check if order items exist
			return $this->data_token->get_the_latest_purchased_token();

		}

		/**
		 * Get the token value
		 * @return string
		**/
		public function get_the_used_token_value()
		{

			$token_index = $this->data_token->get_purchased_token_index( $this->get_token()->__toString() );
			$token_value = $this->data_token->get_used_download_key( $token_index );

			return $token_value;

		}

		/**
		 * Is latest token
		 * @return bool
		**/
		public function is_latest_token()
		{

			return $this->data_token->is_the_latest_purchased_token( $this->get_token()->__toString() );

		}


}
}















