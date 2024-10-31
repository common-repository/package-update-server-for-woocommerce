<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Order' ) && class_exists( 'WC_Order' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Order extends WC_Order {

	/**
	 * Token holder
	**/
	protected $token_holder = array();

	/**
	 * Token holder
	**/
	protected $all_token_params = array();

	/** 
	 * Public Initializer
	**/
	public static function get_instance( $order = 0 )
	{

		// Init if not yet
		try {
			$wc_order = WC()->order_factory->get_order( $order );
			if ( false === $wc_order ) {
				throw new NPUSWC_Exception( __( 'Invalid Order.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}
			$instance = new Self( $wc_order->get_id() );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		// End
		return $instance;

	}

	/**
	 * Constructor
	 * 
	 * @param mixed $order
	**/
	public function __construct( $order = 0 )
	{

		// Parent WC_Order
		parent::__construct( $order );

	}

	/**
	 * Get JWT related to the Order
	 * 
	 * @return NPUSWC_JWT
	**/
	public function get_jwt_set()
	{
		return $this->jwt_set;
	}

	/**
	 * Get the property "has_generated_jwt"
	 * 
	 * @return bool
	**/
	public function has_generated_jwt()
	{
		return $this->has_generated_jwt;
	}

	/**
	 * Delete JWT
	**/
	public function delete_jwt()
	{
		$this->jwt = null;
	}

	/**
	 * Get download item data
	 * 
	 * @param [string] $download_id
	 * 
	 * @return [array|bool] Returns false if the target doesn't exist.
	**/
	public function get_downloadable_item( $download_id )
	{

		if ( ! is_string( $download_id ) || '' === $download_id ) {
			return false;
		}

		$downloadable_items = $this->get_downloadable_items();

		if ( 0 >= count( $downloadable_items ) ) {
			return false;
		}

		foreach ( $downloadable_items as $downloadable_item ) {
			if ( $download_id === $downloadable_item['download_id'] ) {
				return $downloadable_item;
			}
		}

		return false;

	}

	/**
	 * Get download item data
	 * 
	 * @param [string] $download_id
	 * 
	 * @return [array|bool] Returns false if the target doesn't exist.
	**/
	public function get_downloadable_item_by_file_name( $file_name )
	{

		$downloadable_items = $this->get_downloadable_items();

		if ( 0 >= count( $downloadable_items ) ) {
			return false;
		}

		foreach ( $downloadable_items as $downloadable_item ) {
			if ( $download_id === $downloadable_item['download_id'] ) {
				return $downloadable_item;
			}
		}

		return false;

	}

	/**
	 * Generate Token
	 * @return bool|int Returns false if error occured, otherwise returns order ID
	**/
	public function generate_tokens()
	{

		//$all_token_params = $this->get_all_token_params();
			$all_token_params = $this->get_downloadable_params();
			if ( ! is_array( $all_token_params ) || 0 >= count( $all_token_params ) ) {
				return false;
			}

		// User
			$customer_id  = intval( $this->get_customer_id( 'view' ) );
			if ( 0 >= $customer_id ) {
				npuswc_notice_message( npuswc_current_file_and_line( 1, false ) );
				npuswc_notice_message( 'by the hook "woocommerce_grant_product_download_permissions" in the function "wc_downloadable_product_permissions".' . PHP_EOL );
				//return false;
			}

		// Current secret
			$data_option = npuswc_get_data_option( 'package_update_server' );
			$option_data = $data_option->get_data();
			$current_secret = hash( 'sha256', ( 
				is_string( $option_data['jwt_secret_key'] ) 
				? $option_data['jwt_secret_key'] 
				: ''
			) );

		// Each
			$npuswc_token_id_holder = npuswc_get_post_meta( $this->get_id(), '_npuswc_registered_token_ids' );
			if ( ! is_array( $npuswc_token_id_holder ) || 0 >= count( $npuswc_token_id_holder ) ) {
				$npuswc_token_id_holder = array();
			}
			$npuswc_token_id_holder = array();
			foreach ( $all_token_params as $token_params ) {

				// Product ID
				$product_id = intval( $token_params['product_id'] );

				// Init the token id holder
				if ( ! isset( $npuswc_token_id_holder[ $product_id ] )
					|| ! is_array( $npuswc_token_id_holder[ $product_id ] )
				) {
					$npuswc_token_id_holder[ $product_id ] = array();
				}

				$purchased_number = intval( $this->get_token_item_quantity( $product_id ) );
				if ( 0 >= $purchased_number ) {
					continue;
				}

				for ( $index = 0; $index < $purchased_number; $index++ ) {

					$data_token = NPUSWC_Data_Token::get_instance(
						$this->get_id(),
						$product_id,
						$index
					);

					$token_id = $data_token->get_id();
					$npuswc_token_id_holder[ $product_id ][ $index ] = $token_id;

				}

			}
			$npuswc_token_ids_in_json = json_encode( $npuswc_token_id_holder, JSON_UNESCAPED_UNICODE );
			update_post_meta( $this->get_id(), '_npuswc_registered_token_ids', $npuswc_token_ids_in_json );

		return $this->get_id();

	}

	/**
	 * Get Download Params
	 * @usedby 
	 * @return array[]
	**/
	public function get_downloadable_params()
	{

		// NPUSWC_Order
		$order_data = $this->get_data();
		$order_key = $order_data['order_key'];

		// User ID
		$user_id = intval( $this->get_user_id() );
		if ( 0 === $user_id ) {
			return;
		}

		// Holder
		$downloadable_products = array();
		$downloadable_items = $this->get_downloadable_items();
		foreach ( $downloadable_items as $downloadable_item ) {

			// Check if the item is applied WP Content
				$downloadable_product_id = $downloadable_item['product_id'];
				$_npuswc_product_package_type = get_post_meta( $downloadable_product_id, '_npuswc_product_package_type', true );
				$_npuswc_product_package_type = empty( $_npuswc_product_package_type ) ? 'none' : $_npuswc_product_package_type;
				if ( 'none' === $_npuswc_product_package_type ) {
					continue;
				}

			// Version
				$_npuswc_product_package_version = get_post_meta( $downloadable_product_id, '_npuswc_product_package_version', true );
				$_npuswc_product_package_version = empty( $_npuswc_product_package_version ) ? '' : $_npuswc_product_package_version;

			// Expiry
				// Download Expiry in Days
					$_download_expiry_in_day = intval( get_post_meta( $downloadable_product_id, '_download_expiry', true ) );

				// Expiry
					$exp = '-1';
					$current_time = current_time( 'timestamp', true );
					$exp_datetime = $downloadable_item['access_expires'];
					if ( null !== $exp_datetime
						&& is_subclass_of( $exp_datetime, 'DateTime' ) 
					) {
						$exp = ( string ) $exp_datetime->getTimestamp();
						if ( $current_time > $exp ) {
							continue;
						}
					}

			// Token ID
				$token_id = $downloadable_item['order_id'] . $downloadable_item['download_id'];

			// Date created
				$product_id = intval( $downloadable_item['product_id'] );
				$date_created = npuswc_get_product( $product_id )->get_date_created();
				$date_created_timestamp = ( string ) ( 
					null !== $date_created
					&& is_subclass_of( $date_created, 'DateTime' ) 
					? $date_created->getTimestamp() 
					: 0 
				);
			// Date modified
				$date_modified = npuswc_get_product( $product_id )->get_date_modified();
				$date_modified_timestamp = ( string ) ( 
					null !== $date_modified
					&& is_subclass_of( $date_modified, 'DateTime' ) 
					? $date_modified->getTimestamp() 
					: 0 
				);

			// Tested Environment version
				$_npuswc_tested_environment_version = get_post_meta( $downloadable_product_id, '_npuswc_tested_environment_version', true );
				$_npuswc_tested_environment_version = (
					( is_string( $_npuswc_tested_environment_version ) 
						&& '' !== $_npuswc_tested_environment_version
					)
					? ( string ) $_npuswc_tested_environment_version 
					: '1.0.0'
				);

			// Signer index
				$download_key = $downloadable_item['download_id'];
				$signer_index = 0;
				$jwt_signers = json_decode( get_post_meta( $this->get_id(), '_npuswc_order_jwt_signers', true ), true );
				if ( isset( $jwt_signers[ $download_key ] )
					&& is_array( $jwt_signers[ $download_key ] )
					&& 0 < count( $jwt_signers[ $download_key ] )
				) {
					$signer_index = count( $jwt_signers[ $download_key ] );
				}

			// File Extension
				$file_type = wp_check_filetype( $downloadable_item['file']['file'] );
				$file_ext  = $file_type['ext'];

			// Params
			$product_params = array(
				// User ID
				'user_id'                    => $user_id,
				// Order ID
				'order_id'                   => $downloadable_item['order_id'],
				// Order key ( $_GET['order'] )
				'order_key'                  => $downloadable_item['order_key'],
				// Product ID ( $_GET['download_file'] )
				'product_id'                 => $downloadable_item['product_id'],
				// Product ID ( $_GET['download_file'] )
				'product_date_created'       => $date_created_timestamp,
				// Product ID ( $_GET['download_file'] )
				'product_date_modified'      => $date_modified_timestamp,
				// Product ID ( $_GET['download_file'] )
				'tested_environment_version' => $_npuswc_tested_environment_version,
				// Download key ( $_GET['key'] )
				'download_key'               => $downloadable_item['download_id'],
				// Name
				'file_name'                  => $downloadable_item['download_name'],
				// File Extension
				'file_ext'                   => $file_ext,
				// File URL
				'file_url'                   => $downloadable_item['download_url'],
				// WP content type
				'package_type'               => $_npuswc_product_package_type,
				// WP content type
				'product_package_version'    => $_npuswc_product_package_version,
				// Download Limit : "-1" in unlimited case
				'downloads_remaining'        => $downloadable_item['downloads_remaining'],
				// Access Expire : "-1" in unlimited case
				'access_expiry'              => $exp,
				// Download expiry
				'download_expiry_in_day'     => $_download_expiry_in_day,
				// jwt ID just in case
				'token_id'                   => $token_id,
				// Signer index
				'signer_index'               => $signer_index,
			);

			// Set to the holder
			array_push( $downloadable_products, $product_params );

		}

		// End
		return $downloadable_products;

	}
	/**
	 * Init specified Auth by $type
	 * @return array
	**/
	public function get_all_token_params()
	{

		// Holder
		$download_holder = array();
		$download_items = $this->get_downloadable_items();
		if ( is_array( $download_items ) && 0 < count( $download_items ) ) {
		foreach ( $download_items as $download_item ) {
			$download_params = $this->generate_token_params_by_downloadable_item( $download_item );
			if ( false === $download_params ) {
				continue;
			}

			// Set to the holder
			$download_product_id = intval( $download_item['product_id'] );
			$download_holder[ $download_product_id ] = $download_params;

		}
		}

		// End
		return $download_holder;

	}

		/**
		 * Init specified Auth by $type
		 * @return array
		**/
		public function generate_token_params_by_downloadable_item( $token_item = array() )
		{

			if ( ! is_array( $token_item ) 
				|| ! isset( $token_item['order_key'] )
				|| ! isset( $token_item['product_id'] )
			) {
				return false;
			}

			// Product ID
				$token_product_id = $token_item['product_id'];
				$wc_product = WC()->product_factory->get_product( $token_product_id );

				$downloadable_item = wp_parse_args( $this->get_downloadable_item_by_product_id( $token_product_id ), $token_item );

			// Token ID
				$token_id = $this->generate_token_id_by_product( $token_product_id );

			// Date completed
				$product_id = intval( $downloadable_item['product_id'] );
				$date_completed = $this->get_date_completed();
				$date_completed_timestamp = intval( 
					null !== $date_completed
					&& is_subclass_of( $date_completed, 'DateTime' ) 
					? $date_completed->getTimestamp() 
					: 0 
				);

			// Expiry
				$exp = -1;
				$access_expires = $downloadable_item['access_expires'];
				if ( null !== $access_expires 
					&& method_exists( $access_expires, 'getTimestamp' )
					&& 0 < intval( $access_expires->getTimestamp() )
				) {
					$exp = $access_expires->getTimestamp();
				}

			// File Extension
				$file_type = wp_check_filetype( $downloadable_item['file']['file'] );
				$file_ext  = $file_type['ext'];

			// Params
				$token_params = wp_parse_args( array(
					// Token ID just in case
					//'user_id'         => $this->get_customer_id(),
					// Order ID
					'order_id'        => $this->get_id(),
					// Order key
					'order_key'       => $this->get_order_key(),
					// Product ID
					'product_id'      => $wc_product->get_id(),
					// Product Name
					'product_name'    => $wc_product->get_name(),
					// Token ID just in case
					'token_id'        => $token_id,
					// Download ID
					'download_id'     => $downloadable_item['download_id'],
					// File Extension
					'file_ext'        => $file_ext,
					// Date Completed
					'date_completed'  => $date_completed_timestamp,
					// Access Expire : -1 in unlimited case
					'access_expiry'   => $exp,
					// Restrict Access : 
					//'restrict_access' => $token_item['restrict_access'],
				), $downloadable_item );

				return apply_filters( 'npuswc_filter_order_token_params', $token_params, $this );

		}

		/**
		 * Init specified Auth by $type
		 * @param int $product_id
		 * @return array
		**/
		public function generate_token_params_by_product_id( int $product_id )
		{

			$purchased_token = new NPUSWC_Data_Purchased_Token( $this->get_id(), $product_id );
			$token_data = $purchased_token->get_data();
			$token_params = $this->generate_token_params_by_downloadable_item( $token_data );
			return wp_parse_args( $token_params, $token_data );

		}

	/**
	 * Delete token id
	 * @param mixed $product
	 * @param int   $index
	 * @return bool|int Retruns false if error occured
	**/
	public function delete_registered_token_id_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return esc_html__( 'Wrong target to be set.' );
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return esc_html__( 'Wrong target to be deleted.' );
		}

		$token_ids = npuswc_get_post_meta( $this->get_id(), '_npuswc_registered_token_ids' );
		$product_id = $product->get_id();
		if ( isset( $token_ids[ $product_id ] ) 
			&& is_array( $token_ids[ $product_id ] ) 
			&& 0 < count( $token_ids[ $product_id ] )
			&& is_numeric( $token_ids[ $product_id ][ $index ] ) 
			&& 0 < intval( $token_ids[ $product_id ][ $index ] ) 
		) {
			$token_ids[ $product_id ][ $index ] = null;
			$token_ids_json = json_encode( $token_ids, JSON_UNESCAPED_UNICODE );
			$result = update_post_meta( $this->get_id(), '_npuswc_registered_token_ids', $token_ids_json );
			return $result;
		}

			return esc_html__( 'Target not found.' );

	}

	/**
	 * Get registered token id
	 * @param mixed $product
	 * @param int   $index
	 * @return bool|int Retruns false if error occured
	**/
	public function get_registered_token_id_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return false;
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$token_ids = npuswc_get_post_meta( $this->get_id(), '_npuswc_registered_token_ids' );
		$product_id = $product->get_id();
		if ( isset( $token_ids[ $product_id ] ) 
			&& is_array( $token_ids[ $product_id ] ) 
			&& 0 < count( $token_ids[ $product_id ] )
			&& is_numeric( $token_ids[ $product_id ][ $index ] ) 
			&& 0 < intval( $token_ids[ $product_id ][ $index ] ) 
		) {
			return intval( $token_ids[ $product_id ][ $index ] );
		}

		return false;

	}

	/**
	 * Get the token
	 * @param mixed $product
	 * @return bool|string Retruns false if error occured
	**/
	public function get_token_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return false;
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$token_id = $this->get_registered_token_id_by_product( $product->get_id(), $index );
		if ( false === $token_id ) {
			return false;
		}

		try {
			$data_token = NPUSWC_Data_Token::get_instance( $token_id );
		} catch ( Exception $e ) {
			return false;
		}

		return $data_token->get_the_latest_purchased_token();

	}

	/**
	 * Has downloadable item
	 * @return [bool] Returns false if the target doesn't exist.
	**/
	public function has_downloadable_item()
	{

		$downloadable_items = parent::get_downloadable_items();
		if ( is_array( $downloadable_items ) && 0 < count( $downloadable_items ) ) {
			return true;
		}

		return false;

	}


	/**
	 * Get token item data
	 * @return [array]
	**/
	public function get_token_items()
	{

		$tokens = array();
		$downloadable_items = parent::get_downloadable_items();
		foreach ( $downloadable_items as $item ) {

			$product = $item->get_product();
			if ( false === $product ) {
				continue;
			}

			$product_id = intval( $item['product_id'] );

			$purchased_token = new NPUSWC_Data_Purchased_Token( $this->get_id(), $product_id );
			$tokens[ $product_id ] = $purchased_token->get_data();

		}

		return $tokens;

	}

		/**
		 * Get download item data
		 * 
		 * @param [int] $product_id
		 * 
		 * @return [bool|array] Returns false if the target doesn't exist.
		**/
		protected function get_downloadable_item_by_product_id( $product_id )
		{

			if ( ! is_numeric( $product_id ) || 0 >= intval( $product_id ) ) {
				return false;
			}

			$tokens = array();
			$downloadable_items = $this->get_downloadable_items();
			foreach ( $downloadable_items as $item ) {

				if ( intval( $product_id ) === intval( $item['product_id'] ) ) {
					return $item;
				}

			}

			return false;

		}

	/**
	 * Is the product token type
	 * @param [mixed] $product
	 * @return [bool]
	**/
	public function is_the_product_token_type( $product )
	{

		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$is_npuswc_token = get_post_meta( $product->get_id(), '_npuswc_type_token', true );
		if ( ! is_string( $is_npuswc_token ) || 'yes' !== $is_npuswc_token ) {
			return false;
		}

		if ( is_string( $is_npuswc_token ) && 'yes' === $is_npuswc_token ) {
			return true;
		}

		return false;

	}

	/**
	 * Get token item data
	 * @param int $target_product_id
	 * @return [bool|int]
	**/
	public function get_token_item_quantity( $target_product_id )
	{

		foreach ( $this->get_items() as $item ) {

			if ( $item->is_type( 'line_item' ) ) {

				$product = $item->get_product();
				if ( false !== $product 
					&& $target_product_id === $product->get_id()
				) {
					return $item->get_quantity();
				}

			}

		}

		return false;

	}

	/**
	 * Get token index
	 * 
	 * @param [int|WC_Product] $product
	 * 
	 * @return [bool|string] Returns false if the target doesn't exist.
	**/
	public function generate_token_id_by_product( $product )
	{
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}
		return $this->get_order_key() . '_' . $product->get_id();
	}

	/**
	 * Get token signer data
	 * @param string $product_id
	 * @return array
	**/
	public function get_token_signer_by_token_id( $product_id )
	{

		$token_signers = json_decode( get_post_meta( $this->get_id(), '_npuswc_purchased_token_signers', true ), true );
		if ( isset( $jwt_signers[ $product_id ] )
			&& is_array( $jwt_signers[ $product_id ] )
			&& 0 < count( $jwt_signers[ $product_id ] )
		) {
			$token_signer = $jwt_signers[ $product_id ];
			return $token_signer;
		}

		return false;

	}

	/**
	 * Getters
	**/
		/**
		 * Values
		**/
			/**
			 * Get all used token values
			 * @return array
			**/
			public function get_all_used_token_values()
			{
				$token_values = npuswc_get_post_meta( $this->get_id(), '_npuswc_download_keys' );
				return $token_values;
			}

			/**
			 * Get used values
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_used_token_values( int $product_id )
			{

				$values = $this->get_all_used_token_values();
				if ( isset( $values[ $product_id ] ) 
					&& is_array( $values[ $product_id ] )
					&& 0 < count( $values[ $product_id ] )
				) {
					return $values[ $product_id ];
				}

				return array();

			}

			/**
			 * Get the used value
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_used_token_value( int $product_id, $index )
			{

				$values = $this->get_the_used_token_values( $product_id );
				if ( is_array( $values )
					&& 0 < count( $values )
					&& isset( $values[ $index ] )
				) {
					return $values[ $index ];
				}

				return '';

			}

		/**
		 * Secret
		**/
			/**
			 * Secrets
			 * @return string[]
			**/
			public function get_all_used_secrets()
			{

				$secrets = npuswc_get_post_meta( $this->get_id(), '_npuswc_used_secrets' );
				return $tokens;

			}

			/**
			 * Target Secret History
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_used_secrets( int $product_id )
			{

				$secrets = $this->get_all_used_secrets();
				if ( isset( $secrets[ $product_id ] ) 
					&& is_array( $secrets[ $product_id ] )
					&& 0 < count( $secrets[ $product_id ] )
				) {
					return $secrets[ $product_id ];
				}

				return array();

			}

			/**
			 * The Latest Secret
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_latest_used_secret( int $product_id )
			{

				$secrets = $this->get_the_used_secrets( $product_id );
				$latest_version_index = count( $secrets ) - 1;
				if ( is_string( $secrets[ $latest_version_index ] ) 
					&& '' !== $secrets[ $latest_version_index ]
				) {
					return $secrets[ $latest_version_index ];
				}

				return '';

			}

		/**
		 * Tokens
		**/
			/**
			 * Contexted Handler
			**/
				/**
				 * Get the token history related to the product_id.
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return string[] : Returns empty array if there is no tokens registered related to the product id.
				**/
				public function get_the_purchased_tokens_by_handler( $token_handler )
				{

					// Vars
						$product_id = intval( $token_handler->wc_product->get_id() );

						return $this->get_the_purchased_tokens( $product_id );
				}

				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return string|Token : Returns string for some errors.
				**/
				public function get_the_latest_token( $token_handler )
				{

					// Token history
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );
						$the_latest_index = $this->get_the_latest_token_index( $token_handler );

					// Post Meta
						if ( ! isset( $target_purchased_tokens[ $the_latest_index ] )
							|| ! is_string( $target_purchased_tokens[ $the_latest_index ] )
							|| '' === $target_purchased_tokens[ $the_latest_index ]
						) {
							return esc_html__( 'The Latest Token is not found.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
						}
						$the_latest_token = $target_purchased_tokens[ $the_latest_index ];
						$validator = new NPUSWC_Token_Validator( $the_latest_token );
						$token_obj = $validator->validate();

						return $token_obj;
				}

			/**
			 * Index
			**/
				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param string $token
				 * @return null|int|string : Returns null for some errors.
				**/
				protected function get_the_token_index_by_token( $token )
				{

					// Check the param
					if ( is_string( $token ) && '' !== $token ) {
					} elseif ( ! is_string( $token ) 
						&& is_object( $token ) 
						&& method_exists( $token, '__toString' )
					) {
						$token = $token->__toString();
					} else {
						return null;
					}

					// Tokens
						$all_purchased_tokens = $this->get_all_purchased_tokens();
						if ( ! is_array( $all_purchased_tokens )
							|| 0 >= count( $all_purchased_tokens )
						) {
							return null;
						}

						foreach ( $all_purchased_tokens as $product_id => $each_purchased_tokens ) {
							if ( in_array( $token, $each_purchased_tokens ) ) {
								break;
							}
						}

					// Search
						$token_index = array_search( $token, $each_purchased_tokens );

					// End
						return $token_index;

				}

				/**
				 * Get the token index
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return null|int|string
				**/
				public function get_the_token_index( $token_handler )
				{

					$token = $token_handler->get_token()->__toString();

					// Tokens
						$token_index = $this->get_the_token_index_by_token( $token );

					// End
						return $token_index;

				}

				/**
				 * Check if the token has updated version
				 *  This is for the client who purchased 2 or more tokens
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return bool
				**/
				protected function has_later_version( $token_handler )
				{

					$target_token_histroy = $this->get_the_purchased_tokens( $token_handler->wc_product->get_id() );
					$latest_token_index = $this->get_the_latest_token_index( $token_handler );

					// Compare the token index
					if ( $this->get_the_token_index( $token_handler ) < $latest_token_index ) {
						return true;
					}
					$this->is_the_latest = false;
					return false;

				}


				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return bool|int|string : Returns false for some errors.
				**/
				protected function get_the_latest_token_index( $token_handler )
				{

					// Tokens
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );
						if ( ! is_array( $target_purchased_tokens )
							|| 0 >= count( $target_purchased_tokens )
						) {
							return false;
						}

					// Last key
						$the_latest_index = npuswc_array_key_last( $target_purchased_tokens );

					// End
						return $the_latest_index;

				}

				/**
				 * Check if the token exists, registered for the order
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return bool
				**/
				public function token_exists( $token_handler )
				{

					// Get the history
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );

					// Post Meta
						if ( in_array( $this->token->__toString(), $target_purchased_tokens ) ) {
							return true;
						}

						return false;

				}

				/**
				 * Check if the token is the latest
				 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
				 * @return bool
				**/
				public function is_latest_token( $token_handler )
				{

					// Post Meta
						if ( $token_handler->get_token()->__toString() === $this->get_the_latest_token( $token_handler )->__toString() ) {
							return true;
						}

					// End with no found
						return false;

				}

		/**
		 * Signers
		**/
			/**
			 * Signers
			 * @param NPUSWC_Token_Handler|NPUSWC_Token_Validator $token_handler
			 * @return array[]
			**/
			public function get_purchased_token_signers( $token_handler )
			{

				$signers = npuswc_get_post_meta( $this->get_id(), '_npuswc_purchased_token_signers' );
				return $signers;

			}

			/**
			 * Target Token History
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_purchased_token_signers( int $product_id )
			{

				$the_token_signers = $this->get_purchased_token_signers();
				if ( isset( $the_token_signers[ $product_id ] ) 
					&& is_array( $the_token_signers[ $product_id ] )
					&& 0 < count( $the_token_signers[ $product_id ] )
				) {
					return $the_token_signers[ $product_id ];
				}

				return array();

			}

			/**
			 * The Latest Token
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_latest_purchased_token_signer( int $product_id )
			{

				$the_token_signers = $this->get_the_purchased_token_signers( $product_id );
				$latest_version_index = count( $the_token_signers ) - 1;
				if ( array( $the_token_signers[ $latest_version_index ] ) 
					&& 0 < count( $the_token_signers[ $latest_version_index ] )
				) {
					return $the_token_signers[ $latest_version_index ];
				}

				return array();

			}



	/**
	 * Version
	**/

}
}

