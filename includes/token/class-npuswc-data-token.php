<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Data_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NPUSWC_Data_Token {

	/**
	 * Properties
	**/
		/**
		 * ID
		**/
		protected $id;

		/**
		 * ID
		**/
		protected $post_type = 'npuswc-token';

		/**
		 * Token Data
		**/
		protected $data = array();

		/**
		 * 
		**/
		protected $defaults = array(
			'token_id'         => '',
			'order_id'         => '',
			'order_key'        => '',
			'product_id'       => '',
			'registered_index' => 0
		);

		/**
		 * 
		**/
		protected $registered_data_keys = array(
			'token_id',
			'order_id',
			'order_key',
			'product_id',
			'package_version',
			'download_id',
			'access_expiry',
			'registered_index',
		);

		/**
		 * Post
		**/
		protected $post = null;

		/**
		 * Post Meta Names
		**/
		protected $post_meta_names = array(
			'values'            => '_npuswc_download_keys',
			'secrets'           => '_npuswc_used_secrets',
			'tokens'            => '_npuswc_purchased_tokens',
			'signers'           => '_npuswc_used_signers',
			'order_id'          => '_npuswc_order_id',
			'product_id'        => '_npuswc_product_id',
			'purchased_number'  => '_npuswc_purchased_number',
		);


	/**
	 * Settings
	**/
		/**
		 * Call
		**/
		public function __call( $method, $args )
		{

			// get_{$prop}();
			if ( preg_match( '/^get\_/i', $method ) ) {
				$prop_name = preg_replace( '/^get\_/i', '', $method );
				if ( empty( $args ) && isset( $this->defaults[ $prop_name ] ) ) {
					return $this->get_prop( $prop_name );
				}
			}

		}

	/**
	 * Init
	**/
		/**
		 * Public Init
		 * @param mixed $token_or_order_id
		 * @return string|NPUSWC_Data_Token 
		**/
		public static function get_instance( $token_or_order_id, $product_id = null, $index = 0 )
		{
			try {
				$instance = new Self( $token_or_order_id, $product_id, $index );
			} catch ( NPUSWC_Exception $e ) {
				return $e->getMessage();
			}
			return $instance;
		}

		/**
		 * Constructor
		 * @param int $token_or_order_id : Token type id. but with product_id, can be order_id
		 * @param int $product_id
		 * @throws Exception description
		**/
		protected function __construct( $token_or_order_id, $product_id = null, $index = 0 )
		{

			// Will Register
			if ( is_numeric( $product_id ) ) {
				try {
					$this->read_from_order_and_product( $token_or_order_id, $product_id, $index );
				} catch ( Exception $e ) {
					throw new Exception( 'Wrong Input.', 0, $e );
				}

				try {
					$this->register( $index );
				} catch ( Exception $e ) {
					throw new Exception( 'Something wrong.', 0, $e );
				}

			}
			// Read Registered Data
			elseif ( ! is_numeric( $product_id ) ) {
				$wp_post = WP_Post::get_instance( intval( $token_or_order_id ) );
				if ( in_array( $wp_post, array( null, false ) ) ) {
					throw new Exception( esc_html__( 'Wrong ID.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				} elseif ( 
					'WP_Post' !== get_class( $wp_post )
					|| 'npuswc-token' !== $wp_post->post_type 
				) {
					throw new Exception( sprintf( 
						esc_html__( 'Wrong Post Type: %1$s', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
						$wp_post->post_type
					) );
				}
				$this->id   = intval( $wp_post->ID );
				$this->data = $this->get_token_params();
				$this->read_order();
				$this->read_product();

			}

		}

			/**
			 * Init
			 * @param int $order_id
			 * @param int $product_id
			 * @throws Exception
			**/
			protected function read_from_order_and_product( $order_id, $product_id, $index = 0 )
			{

				if ( ! isset( $order_id ) 
					|| 0 >= intval( $order_id )
					|| ! isset( $product_id )
					|| 0 >= intval( $product_id )
				) {
					return false;					
				}

				$wc_order = WC()->order_factory->get_order( $order_id );
				if ( false === $wc_order ) {
					throw new Exception( esc_html__( 'Wrong Order ID.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}
				$npuswc_order = new NPUSWC_Order( $wc_order->get_id() );

				$wc_product = WC()->product_factory->get_product( $product_id );
				if ( false === $wc_product ) {
					throw new Exception( esc_html__( 'Wrong Product ID.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}
				$_product_package_type = get_post_meta( $product_id, '_npuswc_product_package_type', true );
				if ( ! is_string( $_product_package_type ) || 'none' === $_product_package_type ) {
					throw new Exception( esc_html__( 'This is not downloadable product with update checker.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

				if ( ! $npuswc_order->has_downloadable_item( $product_id ) ) {
					throw new Exception( esc_html__( 'Order does not have such a downloadable product.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

				$this->set_prop( 'order_id', intval( $npuswc_order->get_id() ) );
				$this->set_prop( 'product_id', intval( $wc_product->get_id() ) );

				//$purchased_token_data = new NPUSWC_Data_Purchased_Token( $npuswc_order->get_id(), $wc_product );
				//$token_data = $purchased_token_data->get_data();
				$this->data = $npuswc_order->generate_token_params_by_product_id( $wc_product->get_id() );

				$this->read_order();
				$this->read_product();

			}

		/**
		 * Register as Post type token
		**/
		protected function register( $index = 0 )
		{


			$this->read_order();
			$this->read_product();

			if ( 'NPUSWC_Order' !== get_class( $this->npuswc_order )
				|| ! in_array( get_class( $this->wc_product ), array( 'WC_Product_Simple', 'WC_Product_Variation' ) )
			) {
				return false;
			}

			$order_id = intval( $this->npuswc_order->get_id() );
			$product_id = intval( $this->wc_product->get_id() );

			$token_params = $this->npuswc_order->generate_token_params_by_product_id( $this->wc_product->get_id() );

			// Data 
				// Download ID
					$download_id = $token_params['download_id'];

				// Current secret
					$data_option = npuswc_get_data_option( 'package_update_server' );
					$option_data = $data_option->get_data();
					$current_secret = hash( 'sha256', ( 
						is_string( $option_data['jwt_secret_key'] ) 
						? $option_data['jwt_secret_key'] 
						: ''
					) );

				// Token ID
					$token_params['token_id'] = $token_params['token_id'] . '_' . $index;
					$token_params['registered_index'] = $index;

			$post_author = $this->npuswc_order->get_customer_id();
			$post_title_format = '%1$s-%2$s-%3$d';
			$post_title = sprintf(
				$post_title_format,
				$this->npuswc_order->get_order_key(),
				$this->wc_product->get_id(),
				$index
			);

			$post_arr = array(
				'post_author'    => $post_author,
				'post_content'   => '',
				'post_title'     => $post_title,
				'post_excerpt'   => '',
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_parent'    => 0,
			);

			$post_id = wp_insert_post( $post_arr );
			if ( 0 === $post_id 
				|| is_wp_error( $post_id )
			) {
				return false;
			}
			$this->id = $post_id;
			$this->data = $token_params;
			$this->update_token_params( $token_params );

			$token_params = $this->get_token_params_from_data();

			// Generate
				// Token
					$new_token = NPUSWC_Token_Methods::generate_token( $token_params, $current_secret );
				// Sign String Used for the token
					$sign_str = NPUSWC_Token_Methods::generate_sign_str_from_download_params( $token_params, $current_secret );

			// Used Tokens
			$result = $this->append_purchased_tokens( $new_token->__toString() );
			if ( ! $result ) {
				return false;
			}
			// Used Value
			$this->append_download_keys( $download_id );
			// Used Secret
			$this->append_used_secrets( $current_secret );
			// Used Signer
			$this->append_used_signers( array(
				'signer' => NPUSWC_Token_Methods::get_jwt_signer_type(),
				'string' => $sign_str
			) );

		}

			/**
			 * Register as Post type token
			 * @return array
			**/
			protected function get_token_params_from_data()
			{

				return $this->filter_by_required_keys( $this->data );

			}

			/**
			 * Filter by required param keys
			 * @param array $data
			 * @return array
			**/
			protected function filter_by_required_keys( $data )
			{

				if ( ! is_array( $data ) || 0 >= count( $data ) ) {
					return false;
				}

				$filtered_data = array();
				foreach ( $data as $data_index => $data_value ) {
					if ( in_array( $data_index, $this->registered_data_keys ) ) {
						$filtered_data[ $data_index ] = $data_value;
					}
				}

				return $filtered_data;
			}

		/**
		 * Update Token
		 * @param array $new_params
		 * @return Token
		**/
		public function update_expiry()
		{

			$token_params = wp_parse_args( 
				$this->npuswc_order->generate_token_params_by_product_id( $this->wc_product->get_id() ),
				$this->data
			);
			$result = $this->new_params_is_valid( $token_params );
			if ( ! $result ) {
				return esc_html__( 'Wrong Params.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
			}

			$this->read_order();
			$this->read_product();

			// Vars
				$order_id   = $this->npuswc_order->get_id();
				$product_id = $this->wc_product->get_id();

			// Data 
				// Download ID
					$download_id = $token_params['download_id'];

				// Current secret
					$data_option = npuswc_get_data_option( 'package_update_server' );
					$option_data = $data_option->get_data();
					$current_secret = hash( 'sha256', ( 
						is_string( $option_data['jwt_secret_key'] ) 
						? $option_data['jwt_secret_key'] 
						: ''
					) );

			// Generate
				$filtered_token_params = $this->filter_by_required_keys( $token_params );

				// Token
					$new_token = NPUSWC_Token_Methods::generate_token( $filtered_token_params, $current_secret );

				// Sign String Used for the token
					$sign_str = NPUSWC_Token_Methods::generate_sign_str_from_download_params( $token_params, $current_secret );

			// Append
				$result = $this->append_purchased_tokens( $new_token->__toString() );
				if ( ! $result ) {
					return $new_token;
				}
				$result = $this->append_download_keys( $download_id );
				$result = $this->append_used_secrets( $current_secret );
				$result = $this->append_used_signers( array(
					'signer' => NPUSWC_Token_Methods::get_jwt_signer_type(),
					'string' => $sign_str
				) );

				$this->maybe_set_new_access_exipry();

				$this->update_token_params( $token_params );

				return $new_token;

		}

			/**
			 * Maybe set new access expiry
			**/
			protected function maybe_set_new_access_exipry()
			{

				$update_expiry = $this->get_prop( 'update_expiry' );
				if ( is_string( $update_expiry ) && 'yes' === $update_expiry ) {
					$access_expiry = intval( $this->get_prop( 'access_expiry' ) );
					if ( 0 < $access_expiry ) {
						return true;
					}
				}

			}

			/**
			 * Check the new params
			 * @param array $new_params
			 * @return bool
			**/
			protected function new_params_is_valid( $new_params )
			{

				if ( is_array( $new_params ) 
					&& 0 < count( $new_params )
					&& isset( $new_params['order_id'] ) && ! empty( $new_params['order_id'] )
					&& isset( $new_params['order_key'] ) && ! empty( $new_params['order_key'] )
					&& isset( $new_params['product_id'] ) && ! empty( $new_params['product_id'] )
					&& isset( $new_params['package_type'] ) && ! empty( $new_params['package_type'] )
					&& isset( $new_params['package_version'] ) && ! empty( $new_params['package_version'] )
					&& isset( $new_params['download_id'] ) && ! empty( $new_params['download_id'] )
					&& isset( $new_params['date_completed'] ) && ! empty( $new_params['date_completed'] )
					&& isset( $new_params['access_expiry'] ) && ! empty( $new_params['access_expiry'] )
					&& isset( $new_params['purchased_number'] ) && ! empty( $new_params['purchased_number'] )
				) {
					return true;
				}

				return false;

			}

		/**
		 * Generate content data in json string
		 * 
		 * @param JWT    $jwt           : JWT 
		 * @param string $type          : 'theme' or 'plugin'
		 * @param string $returned_type : 'array' or 'json'
		 * @param bool   $echo          : Will echo or not, Default false
		 * 
		 * @return string
		 * 	
		**/
		public function generate_package_data_for_wp_update_checker( $token_obj = null, $echo = false )
		{

			try {

				if ( $token_obj === null ) {
					$token_obj = NPUSWC_Token_Methods::parse_from_string( $this->get_the_latest_purchased_token() );
				}

				// Attributes
				$package_type         = $this->get_prop( 'package_type' );
				$package_version      = get_post_meta( $this->get_prop( 'product_id' ), '_npuswc_product_package_version', true );
				$environment_version  = $this->get_prop( 'environment_version' );
				$date_package_updated = $this->get_prop( 'date_package_updated' );
				$access_expiry        = $this->get_prop( 'access_expiry' );
				$access_expiry_text   = date_i18n( 'Y-m-d', intval( $access_expiry ) );
				if ( -1 === intval( $access_expiry ) ) {
					$access_expiry_text = __( 'Unlimited.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

				// File
				$file = $this->get_prop( 'file' );
				if ( ! isset( $file['name'] ) ) {
					return false;
				}
				$file_name = $file['name'];

				// Description
				$description         = $this->get_prop( 'description' );
				if ( ! is_string( $description ) ) {
					$description = '';
				}

				// Content data
				$package_data = apply_filters( 'npuswc_product_package_data', array(
					'type'                       => $package_type,
					'name'                       => $file_name,
					'tested_environment_version' => $environment_version,
					'last_modified'              => $date_package_updated,
					'version'                    => $package_version,
					'description'                => $description
				), $package_type );

				// Prepare returned data
				$json_in_array = array(
					'file_name'      => $file_name,
					'package_data'   => $package_data,
					'access_expiry'  => $access_expiry_text,
					'token'          => $token_obj->__toString()
				);

			} catch ( Exception $e ) {
				return $e->getMessage();
			}

			// End
			return $json_in_array;


		}

	/**
	 * Readers
	**/
		/**
		 * Order
		**/
		public function read_order()
		{

			$order_id = $this->get_order_id();
			if ( is_numeric( $order_id ) && 0 < intval( $order_id ) ) {
				$this->npuswc_order = new NPUSWC_Order( intval( $order_id ) );
			}

		}

		/**
		 * Product
		**/
		public function read_product()
		{

			$product_id = $this->get_product_id();
			if ( is_numeric( $product_id ) && 0 < intval( $product_id ) ) {
				$this->wc_product = WC()->product_factory->get_product( intval( $product_id ) );
			}

		}

		/**
		 * Product
		**/
		public function read_data()
		{
			if ( ! is_numeric( $this->get_id() )
				|| 0 >= intval( $this->get_id() )
			) {
				throw new Exception( esc_html__( 'Read Data Error: Invalid ID.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}
			$this->data = npuswc_get_post_meta( $this->get_id(), '_npuswc_token_params' );
		}

	/**
	 * Getters
	**/
		/**
		 * Get $this->id
		 * @return int
		**/
		public function get_id()
		{
			return $this->id;
		}

		/**
		 * Get $this->id
		 * @return array
		**/
		public function get_data()
		{
			$this->read_data();
			return $this->data;
		}

		/**
		 * Prop of $this->data
		 * @param string $key
		 * @return mixed
		**/
		public function get_prop( $key )
		{
			if ( isset( $this->data[ $key ] ) && ! empty( $this->data[ $key ] ) ) {
				return $this->data[ $key ];
			}
			return false;
		}

		/**
		 * Order Object
		 * @return bool|NPUSWC_Order
		**/
		public function get_order()
		{

			$this->read_order();
			if ( isset( $this->npuswc_order ) && null !== $this->npuswc_order ) {
				return $this->npuswc_order;
			}
			return false;

		}

		/**
		 * Product Object
		 * @return bool|WC_Product
		**/
		public function get_product()
		{

			$this->read_product();
			if ( isset( $this->wc_product ) && null !== $this->wc_product ) {
				return $this->wc_product;
			}
			return false;

		}

	/**
	 * Setters
	**/
		/**
		 * Set $this->id
		 * @return int
		**/
		public function set_id( int $id )
		{
			if ( is_numeric( $id ) && 0 < intval( $id ) ) {
				$this->id = $id;
				return true;
			}
			return false;
		}

		public function set_props()
		{

			if ( ! isset( $this->id ) 
				|| is_int( $this->id ) 
				|| 0 < intval( $this->id )
			) {
				return false;
			}

			$this->set_prop();

			return true;

		}

		/**
		 * Set Prop of $this->data
		 * @param strig $key
		 * @param mixed $value
		 * @return bool
		**/
		public function set_prop( $key, $value )
		{

			if ( ! is_string( $key ) 
				|| '' === $key
			) {
				return false;
			}

			if ( isset( $this->defaults[ $key ] ) && ! empty( $value ) ) {
				$this->data[ $key ] = $value;
				return true;
			}

			return false;

		}

	/**
	 * Options
	**/
		/**
		 * Download Keys
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_download_keys()
			{
				$download_keys = npuswc_get_post_meta( $this->id, '_npuswc_download_keys' );
				if ( is_array( $download_keys ) && 0 < count( $download_keys ) ) {
					return $download_keys;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_download_keys( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_npuswc_download_keys', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_download_keys( $new_value )
			{
				$download_keys = $this->get_download_keys();
				$latest_token_index = npuswc_array_key_last( $download_keys );
				$new_token_index = $latest_token_index + 1;
				$download_keys[ $new_token_index ] = $new_value;
				$download_keys_json = json_encode( $download_keys, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_download_keys( $download_keys_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_download_keys( $index = -1 )
			{
				$download_keys = $this->get_download_keys();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $download_keys );
				}
				unset( $download_keys[ $index ] );
				$download_keys_json = json_encode( $download_keys, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_download_keys( $download_keys_json );
				return $update_result;
			}

		/**
		 * Secrets
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_used_secrets()
			{
				$used_secrets = npuswc_get_post_meta( $this->id, '_npuswc_used_secrets' );
				if ( is_array( $used_secrets ) && 0 < count( $used_secrets ) ) {
					return $used_secrets;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_used_secrets( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_npuswc_used_secrets', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_used_secrets( $new_value )
			{
				$used_secrets = $this->get_used_secrets();
				$latest_token_index = npuswc_array_key_last( $used_secrets );
				$new_token_index = $latest_token_index + 1;
				$used_secrets[ $new_token_index ] = $new_value;
				$used_secrets_json = json_encode( $used_secrets, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_secrets( $used_secrets_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_used_secrets( $index = -1 )
			{

				$used_secrets = $this->get_used_secrets();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $used_secrets );
				}
				unset( $used_secrets[ $index ] );
				$used_secrets_json = json_encode( $used_secrets, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_secrets( $used_secrets_json );
				return $update_result;
			}

		/**
		 * Tokens
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_purchased_tokens()
			{
				$purchased_token = npuswc_get_post_meta( $this->id, '_npuswc_purchased_tokens' );
				if ( is_array( $purchased_token ) && 0 < count( $purchased_token ) ) {
					return $purchased_token;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_purchased_tokens( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_npuswc_purchased_tokens', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_purchased_tokens( $new_value )
			{
				$purchased_token = $this->get_purchased_tokens();
				$latest_token_index = npuswc_array_key_last( $purchased_token );
				$new_token_index = $latest_token_index + 1;
				$new_token = $this->sanitize_token( $new_value );
				if ( null === $new_token ) {
					return false;
				}
				$purchased_token[ $new_token_index ] = $new_token;
				$purchased_token_json = json_encode( $purchased_token, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_purchased_tokens( $purchased_token_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_purchased_tokens( $index = -1 )
			{
				$purchased_token = $this->get_purchased_tokens();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $purchased_token );
				}
				unset( $purchased_token[ $index ] );
				$purchased_token_json = json_encode( $purchased_token, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_purchased_tokens( $purchased_token_json );
				return $update_result;
			}

		/**
		 * Signers
		**/
			/**
			 * Get
			 * @return array
			**/
			public function get_used_signers()
			{
				$used_signers = npuswc_get_post_meta( $this->id, '_npuswc_used_signers' );
				if ( is_array( $used_signers ) && 0 < count( $used_signers ) ) {
					return $used_signers;
				}
				return array();
			}

			/**
			 * Update
			 * @param array[] $new_value
			 * @return bool
			**/
			protected function update_used_signers( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_npuswc_used_signers', $new_value );
				return $update_result;
			}

			/**
			 * Update
			 * @param array $new_value
			 * @return bool
			**/
			public function append_used_signers( $new_value )
			{
				$used_signers = $this->get_used_signers();
				$latest_token_index = npuswc_array_key_last( $used_signers );
				$new_token_index = $latest_token_index + 1;
				$used_signers[ $new_token_index ] = $new_value;
				$used_signers_json = json_encode( $used_signers, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_signers( $used_signers_json );
				return $update_result;
			}

			/**
			 * Update
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_used_signers( $index = -1 )
			{
				$used_signers = $this->get_used_signers();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $used_signers );
				}
				unset( $used_signers[ $index ] );
				$used_signers_json = json_encode( $used_signers, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_signers( $used_signers_json );
				return $update_result;
			}

		/**
		 * Properties
		**/
			/**
			 * Update
			 * @return null|array
			**/
			public function get_token_params()
			{

				$token_params_json  = get_post_meta( $this->get_id(), '_npuswc_token_params', true );
				$token_params = json_decode( $token_params_json, true );
				if ( null === $token_params ) {
					return false;
				}
				return $token_params;

			}

			/**
			 * Update
			 * @param array $new_value
			 * @return bool
			**/
			public function update_token_params( $new_value )
			{

				if ( ! is_array( $new_value ) 
					|| 0 >= count( $new_value )
				) {
					return false;
				}

				$new_value_json = json_encode( $new_value, JSON_UNESCAPED_UNICODE );
				$update_result  = update_post_meta( $this->get_id(), '_npuswc_token_params', $new_value_json );
				return $update_result;

			}

		/**
		 * Sanitizers
		**/
			/**
			 * Token
			 * @param string $value
			 * @param mixed $default : Default null
			 * @return string
			**/
			public function sanitize_token( $value, $default = null )
			{
				if ( ! is_string( $value ) || '' === $value ) {
					return $default;
				}
				if ( preg_match( "/^[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?\.([a-zA-Z0-9\-_]+)?$/", $value ) ) {
					return $value;
				}
				return $default;
			}

	/**
	 * Tools
	**/
		/**
		 * Download Keys
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_used_download_key( int $index = -1 )
			{

				$download_keys = $this->get_download_keys();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $download_keys );
				}
				if ( is_array( $download_keys ) 
					&& 0 < count( $download_keys ) 
					&& isset( $download_keys[ $index ] )
					&& is_string( $download_keys[ $index ] )
					&& '' !== $download_keys[ $index ]
				) {
					return $download_keys[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_used_download_key()
			{

				return $this->get_used_download_key( -1 );

			}

		/**
		 * Secret
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_used_secret( int $index = -1 )
			{

				$used_secrets = $this->get_used_secrets();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $used_secrets );
				}
				if ( is_array( $used_secrets ) 
					&& 0 < count( $used_secrets ) 
					&& isset( $used_secrets[ $index ] )
					&& is_string( $used_secrets[ $index ] )
					&& '' !== $used_secrets[ $index ]
				) {
					return $used_secrets[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_used_secret()
			{

				return $this->get_used_secret( -1 );

			}

		/**
		 * Token
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_purchased_token( int $index = -1 )
			{

				$purchased_tokens = $this->get_purchased_tokens();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $purchased_tokens );
				}
				if ( is_array( $purchased_tokens ) 
					&& 0 < count( $purchased_tokens ) 
					&& isset( $purchased_tokens[ $index ] )
					&& is_string( $purchased_tokens[ $index ] )
					&& '' !== $purchased_tokens[ $index ]
				) {
					return $purchased_tokens[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_purchased_token()
			{

				return $this->get_purchased_token( -1 );

			}

			/**
			 * Get the latest purchased token
			 * @param string $token
			 * @return bool
			**/
			public function is_the_latest_purchased_token( string $token = '' )
			{

				return $this->get_purchased_token( -1 ) === $token;

			}

		/**
		 * Signers
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|array
			**/
			public function get_used_signer_by_index( int $index = -1 )
			{

				$used_signers = $this->get_used_signers();
				if ( -1 === $index ) {
					$index = npuswc_array_key_last( $used_signers );
				}
				if ( is_array( $used_signers ) 
					&& 0 < count( $used_signers ) 
					&& isset( $used_signers[ $index ] )
					&& is_array( $used_signers[ $index ] )
					&& isset( $used_signers[ $index ]['signer'] )
					&& isset( $used_signers[ $index ]['string'] )
				) {
					return $used_signers[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|array
			**/
			public function get_the_latest_used_signer()
			{

				return $this->get_used_signer_by_index( -1 );

			}

		/**
		 * Index
		**/
			/**
			 * Get the token index
			 * @param string $token
			 * @return null|int
			**/
			public function get_purchased_token_index( $token = '' )
			{
				if ( ! is_string( $token ) 
					|| '' === $token
				) {
					return null;
				}

				$purchased_tokens = $this->get_purchased_tokens();
				if ( is_array( $purchased_tokens ) && 0 < count( $purchased_tokens ) && in_array( $token, $purchased_tokens ) ) {
					$key = array_search( $token, $purchased_tokens );
					if ( false === $key ) {
						return null;
					}
					return $key;
				}

				return null;

			}

			/**
			 * Get the latest token index
			 * @return null|int
			**/
			public function get_the_latest_purchased_token_index()
			{

				$purchased_tokens = $this->get_purchased_tokens();
				if ( is_array( $purchased_tokens ) && 0 < count( $purchased_tokens ) ) {
					$key = npuswc_array_key_last( $purchased_tokens );
					return $key;
				}

				return null;

			}

	/**
	 * Handling Data
	**/
		/**
		 * Decrease the number of the purchased
		 * @param int $number
		 * @return bool|int 
		**/
		public function decrease_purchased_number( int $number = 1 )
		{

			$params = $this->get_token_params();


			$current_number = intval( $params['purchased_number'] );
			if ( 0 >= $current_number ) {
				return false;
			}

			if ( 0 >= $number ) {
				return false;
			}

			$decreased = $current_number - intval( $number );
			if ( 0 > $decreased ) {
				return false;
			}

			$params['purchased_number'] = intval( $decreased );
			$result = $this->update_token_params( $params );
			if ( ! $result ) {
				return false;
			}

			return $decreased;

		}

		/**
		 * Decrease the number of the purchased
		 * @param mixed $product
		 * @param int   $index
		 * @return bool|string
		**/
		public function delete_the_update_token( $product_id, $index = 0 )
		{

			$this->read_order();
			$this->read_product();

			// Vars
				$order_id   = $this->npuswc_order->get_id();
				$product_id = $this->wc_product->get_id();

				$token_id = $this->npuswc_order->get_registered_token_id_by_product( $product_id, $this->get_registered_index() );
				if ( null === $token_id
					|| $this->get_id() !== $token_id
				) {
				return esc_html__( 'Token ID not found.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

				$result = wp_delete_post( $this->get_id() );
				if ( in_array( $result, array( false, null ) ) ) {
					return esc_html__( 'Something wrong to delete the update token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				}

				do_action( $this->get_prefixed_action_hook( 'npuswc_action_deleted_update_token', $result, $this ) );

				return true;

		}

		/**
		 * Delete the updated validation token
		 * @return bool|string
		**/
		public function cancel_updated_new_token()
		{

			$result = $this->cancel_download_keys();
			$result = $this->cancel_used_secrets();
			$result = $this->cancel_purchased_tokens();
			$result = $this->cancel_used_signers();

			return true;

		}

}
}












