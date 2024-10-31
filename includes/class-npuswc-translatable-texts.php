<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NPUSWC_Translatable_Texts' ) ) {
/**
 * Data formats
**/
class NPUSWC_Translatable_Texts extends NPUSWC_Unique {

	#
	# Properties
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

			/**
			 * General texts
			 * 
			 * @var array
			**/
			protected $data = array();

			/**
			 * Admin texts
			 * 
			 * @var array
			**/
			protected $admin = array();

			/**
			 * Public texts
			 * 
			 * @var array
			**/
			protected $public = array();

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

		// Init Data
		$this->data = array(
			'plugin_name' => esc_html__( 'Package Update Server for WooCommerce', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
		);
		$this->admin = array(
			'copy' => esc_html__( 'Copy', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
			'close' => esc_html__( 'Close', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
		);
		$this->public = array(
			'token' => esc_html__( 'Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
			'get_a_token' => esc_html__( 'Get a token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
		);

	}

	/**
	 * Get the Texts
	 * 
	 * @return array
	**/
	public function get_data()
	{
		/**
		 * Translated texts
		 * 
		 * 
		**/
		return apply_filters( 'npuswc_filter_translatable_texts', $this->data );

	}

	/**
	 * Get the Texts
	 * 
	 * @return array
	**/
	public function get_admin_texts()
	{
		/**
		 * Translated texts
		 * 
		 * 
		**/
		return apply_filters( 'npuswc_filter_translatable_texts_admin', $this->admin );

	}

	/**
	 * Get the Texts
	 * 
	 * @return array
	**/
	public function get_public_texts()
	{
		/**
		 * Translated texts
		 * 
		 * 
		**/
		return apply_filters( 'npuswc_filter_translatable_texts_public', $this->public );

	}

	/**
	 * Get the Prop
	 * 
	 * @param string $key
	 * 
	 * @return string
	**/
	public function get_prop( $key )
	{

		// Check the required param
		if ( ! npuswc_is_string_and_not_empty( $key ) ) {
			return '';
		}

		// Case : Has
		if ( $this->has_prop( $key ) ) {
			return $this->data[ $key ];
		}

		// End : Failed
		return '';

	}

	/**
	 * Check if data has property related to $key
	 * 
	 * @param string $key
	 * 
	 * @return bool
	**/
	public function has_prop( $key )
	{

		// Check the required param
		if ( ! npuswc_is_string_and_not_empty( $key ) ) {
			return false;
		}

		// Case : Has the property
		if ( isset( $this->data[ $key ] )
			&& npuswc_is_string_and_not_empty( $key ) 
		) {
			return true;
		}

		// End : Failed
		return false;

	}

	/**
	 * Set the Prop
	 * 
	 * @param string $key
	 * @param string $text
	 * 
	 * @return bool
	**/
	public function set_prop( $key, $text )
	{

		// Check the required param
		if ( ! npuswc_is_string_and_not_empty( $key )
			|| ! npuswc_is_string_and_not_empty( $text )
		) {
			// End : Failed
			return false;
		}

		// Set
		$this->data[ $key ] = $text;

		// End : Result
		return true;

	}

}
}