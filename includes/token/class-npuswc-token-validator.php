<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Token_Validator' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Token_Validator {

	/**
	 * Token holder
	**/
	protected $is_token = false;

	/**
	 * Token holder
	**/
	protected $token;

	/**
	 * NPUSWC_Token_Handler
	**/
	protected $token_handler = null;

	/**
	 * NPUSWC_Order
	**/
	protected $npuswc_order = null;

	/**
	 * WC_Product
	**/
	protected $wc_product = null;


	/**
	 * Flag if validating token is done or not
	**/
	protected $validating_token_done = false;

	/**
	 * Flag if validating expiry is done or not
	**/
	protected $validating_expiry_done = false;

	/**
	 * Constructor
	 * @param string|Token $token_obj
	**/
	public function __construct( $token_obj )
	{

		// Parent WC_Order
		$this->init_vars( $token_obj );

	}

	/**
	 * Init Vars
	 * @param string|Token $token_obj
	**/
	//protected function init_vars( $token_obj )
	protected function init_vars( $token_obj )
	{

		// Prepare var $token from ID and some params
		$result = $this->set_token( $token_obj );
		if( is_string( $result ) ) {
			throw new NPUSWC_Exception( 
				esc_html__( 'Failed to parse string into Token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
				400,
				null,
				$token_obj
			);
			return;
		}

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
		$this->npuswc_order = NPUSWC_Order::get_instance( $order_id );
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
		if ( is_string( $this->data_token )
		) {
			throw new NPUSWC_Exception( $this->data_token );
		}

	}

	/**
	 * Init Vars
	 * @param string|Token $token_obj
	 * @return string|Token Returns string for error.
	**/
	protected function set_token( $token_obj )
	{

		// Prepare var $token from ID and some params
		if ( is_string( $token_obj ) ) {
			$token_obj = NPUSWC_Token_Methods::parse_from_string( $token_obj );
		}

		if ( is_string( $token_obj ) ) {
			return $token_obj;
		}

		if ( null === $token_obj ) {
			throw new NPUSWC_Exception( esc_html__( 'Validator : wrong param input "null".', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
		}

		$this->is_token = true;
		$this->token    = $token_obj;

		return $token_obj;

	}

	/**
	 * Init Vars
	 * @return Token Returns string for error.
	**/
	public function get_token()
	{

		return $this->token;

	}

	/**
	 * Validate token :
	 * 		token itself
	 *   	expiry
	 * @return string|Token
	**/
	public function validate()
	{

		try {

			$token_obj = $this->validate_token();
			if ( is_string( $token_obj ) ) {
				throw new NPUSWC_Exception( sprintf( 
					esc_html__( 'Validating Token: Result False. %s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					$token_obj
				) );
			}

			$token_obj = $this->validate_expiry();
			if ( is_string( $token_obj ) ) {
				throw new NPUSWC_Exception( sprintf( 
					esc_html__( 'Validating Expiry: Result False. %s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					$token_obj
				) );
			}

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $token_obj;

	}

		/**
		 * Validate token except the expires
		 * @return string|Token
		**/
		public function validate_token()
		{

			try {

				// Prepare var $token from ID and some params
					if ( ! $this->token ) {
						throw new NPUSWC_Exception( esc_html__( 'This is even not token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
					}

				// Check if the order exists and has purchased items.
					$this->validate_order();

				// Check if the token signer is valid
					$this->validate_signer();

			} catch ( Exception $e ) {
				return $e->getMessage();
			}

			return $this->token;

		}

			/**
			 * Check if the order exists
			 * @throws Exception
			**/
			protected function is_valid_token_data()
			{

				if ( false === $this->npuswc_order
					|| is_string( $this->npuswc_order )
				) {
					return false;
				}

				return true;

			}

			/**
			 * Check if the order exists
			 * @throws Exception
			**/
			protected function validate_order()
			{

				if ( ! $this->is_valid_token_data() ) {
					throw new NPUSWC_Exception( esc_html__( 'The token params are not valid.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}
				
				// Check if order items exist
					$order_id    = intval( $this->npuswc_order->get_id() );
					$product_id  = intval( $this->wc_product->get_id() );

					if ( 0 >= count( $this->npuswc_order->get_downloadable_items() ) ) {
						throw new NPUSWC_Exception( esc_html__( 'The order doesn\'t exist.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
					}

				// Check if the order saved the same token as this
					$is_token_id_registered_in_order = $this->is_token_id_registered_in_order();
					if ( ! $is_token_id_registered_in_order ) {
						throw new NPUSWC_Exception( esc_html__( 'Such a Token is not registered.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
					}

			}

				/**
				 * Check if the order exists
				 * @return bool
				**/
				protected function is_token_id_registered_in_order()
				{

					$product_id       = intval( $this->wc_product->get_id() );
					$registered_index = intval( $this->token->hasClaim( 'registered_index' ) ? $this->token->getClaim( 'registered_index' ) : 0 );
					$token_id         = intval( $this->data_token->get_id() );

					$token_id_holder = npuswc_get_post_meta( $this->npuswc_order->get_id(), '_npuswc_registered_token_ids' );
					if ( is_array( $token_id_holder )
						&& isset( $token_id_holder[ $product_id ] )
						&& is_array( $token_id_holder[ $product_id ] )
						&& 0 < count( $token_id_holder[ $product_id ] )
						&& isset( $token_id_holder[ $product_id ][ $registered_index ] )
						&& is_numeric( $token_id_holder[ $product_id ][ $registered_index ] )
						&& 0 <= intval( $token_id_holder[ $product_id ][ $registered_index ] )
						&& $token_id === intval( $token_id_holder[ $product_id ][ $registered_index ] )
					) {
						return true;
					}

					return false;

				}

				/**
				 * Check if the order exists
				 * @return bool
				**/
				public function is_token_latest()
				{

					//$target_token_histroy = $this->npuswc_order->get_the_purchased_tokens( $this->wc_product->get_id() );
					$latest_token_index = intval( $this->token_handler->get_the_latest_token_index() );

					// Compare the token index
					if ( intval( $this->get_the_token_index() ) < $latest_token_index ) {
						return true;
					}
					$this->is_the_latest = false;
					return false;


				}

			/**
			 * Check if the order exists
			 * @return bool
			**/
			protected function validate_signer()
			{

				// Signer index
					$token_index = $this->data_token->get_purchased_token_index( $this->token->__toString() );
					if ( null === $token_index ) {
						throw new NPUSWC_Exception( sprintf(
							esc_html__( 'Signer index does not exist in Token. %1$s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
							$token_index
						) );
					}

				// Indexed signer
					$indexed_signer = $this->data_token->get_used_signer_by_index( $token_index );
					if (  ! ( isset( $indexed_signer['signer'] ) && is_string( $indexed_signer['signer'] ) )
						|| ! ( isset( $indexed_signer['string'] ) && is_string( $indexed_signer['string'] ) )
					) {
						throw new NPUSWC_Exception( sprintf(
							esc_html__( 'Signer index does not exist in Token. %1$s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
							$token_index
						) );
					}

					$signer_type = $indexed_signer['signer'];
					$signer_str  = $indexed_signer['string'];

					$signer = NPUSWC_Token_Methods::get_jwt_signer( array(
						'type' => $signer_type
					) );

				// No signer
					if ( false === $signer ) {
					}
					elseif ( 'Sha256' === $signer_type ) {
						if( ! $this->token->verify( $signer, $signer_str ) ) {
							ob_start();
							npuswc_test_var_dump( $this->token );
							$html = ob_get_clean();
							throw new NPUSWC_Exception( esc_html__( 'Sign is not valid.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
						}
					}

			}

		/**
		 * Validate token except the expires
		 * @return string|Token
		**/
		public function validate_expiry()
		{

			// Prepare var $token from ID and some params
				if ( is_string( $this->token ) && '' !== $this->token ) {
					$this->token = NPUSWC_Token_Methods::parse_from_string( $this->token );
				} elseif ( is_string( $this->token ) && '' === $this->token ) {
					return esc_html__( 'Token is invalid string.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

			// Expiry
				if ( ! $this->token->hasClaim( 'access_expiry' ) ) {
					return $this->token;
				}
				$access_expiry = intval( $this->token->getClaim( 'access_expiry' ) );
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
				return $this->token;

		}

		/**
		 * Validate token except the expires
		 * @return string|Token
		**/
		public function validate_purchased_number()
		{

			$purchased_number = intval( $this->data_token->get_purchased_number() );
			if ( 0 >= $purchased_number ) {
				return esc_html__( 'Update token is all used.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}
			return $this->token;

		}

		/**
		 * Get the token index
		 * @return null|int|string
		**/
		protected function get_the_token_index()
		{

			// Check if order items exist
				return $this->npuswc_order->get_the_token_index( $this );

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


}
}



