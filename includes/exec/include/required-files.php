<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 3rds
	// JWT
	if ( ! class_exists( '\Lcobucci\JWT\Token' ) ) {
		require_once( NPUSWC_DIR_PATH . 'includes/3rd/jwt/autoload.php' );
	}

// Functions
	// General
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-general.php' );
	// Detect
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-detect.php' );
	// Option
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-option.php' );
	// Post Meta
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-post-meta.php' );
	// Sanitizer
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-sanitizer.php' );
	// Notice
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-notice.php' );
	// Product
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-product.php' );
	// File
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-file.php' );
	// Order
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-order.php' );
	// Rest API
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-rest-api.php' );
	// HTML
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-html.php' );
	// Template
	require_once( NPUSWC_DIR_PATH . 'includes/function/functions-template.php' );

// Interfaces
	// Data
	require_once( NPUSWC_DIR_PATH . 'includes/interface/class-npuswc-data-interface.php' );
	// Data Store
	require_once( NPUSWC_DIR_PATH . 'includes/interface/class-npuswc-data-store-interface.php' );
	// Data Store Option
	require_once( NPUSWC_DIR_PATH . 'includes/interface/class-npuswc-data-store-option-interface.php' );

// Abstract
	// Endpoint
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-endpoint-abstract.php' );
	// Data
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-data-abstract.php' );
	// Data CPT
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-data-cpt-abstract.php' );
	// Data Store
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-data-store-abstract.php' );
	// Mail
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-mail-abstract.php' );
	// Unique
	require_once( NPUSWC_DIR_PATH . 'includes/abstract/class-npuswc-unique-abstract.php' );

// Global
	// Data formats
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-data-formats.php' );
	// Translatable Texts
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-translatable-texts.php' );
	// Sanitize Methods
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-sanitize-methods.php' );
	// Option Manager
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-option-manager.php' );
	// Token Methods
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-token-methods.php' );
	// Data Store Loader
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-data-store-loader.php' );
	// NPUSWC_REST_API_Loader
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-rest-api-loader.php' );
	// NPUSWC_Token_Manager
	require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc-token-manager.php' );

// Endpoint
	// Purchased Tokens
	require_once( NPUSWC_DIR_PATH . 'includes/endpoint/class-npuswc-endpoint-purchased-tokens.php' );

// Data
	// Option
	require_once( NPUSWC_DIR_PATH . 'includes/data/class-npuswc-data-option.php' );
	// NPUSWC_Data_Product_Token
	require_once( NPUSWC_DIR_PATH . 'includes/data/class-npuswc-data-product-token.php' );
	// NPUSWC_Data_Purchased_Token
	require_once( NPUSWC_DIR_PATH . 'includes/data/class-npuswc-data-purchased-token.php' );

// Data Store
	// Option
	require_once( NPUSWC_DIR_PATH . 'includes/data-store/class-npuswc-data-store-option.php' );

// Notification
	// Exception
	require_once( NPUSWC_DIR_PATH . 'includes/notification/class-npuswc-exception.php' );
	// Exception Data
	require_once( NPUSWC_DIR_PATH . 'includes/notification/class-npuswc-exception-data.php' );
	// Notice
	require_once( NPUSWC_DIR_PATH . 'includes/notification/class-npuswc-notices.php' );
	// Mail
	require_once( NPUSWC_DIR_PATH . 'includes/notification/class-npuswc-mail.php' );

// Order
	// NPUSWC_Order
	require_once( NPUSWC_DIR_PATH . 'includes/order/class-npuswc-order.php' );

// Token
	// NPUSWC_Data_Token
	require_once( NPUSWC_DIR_PATH . 'includes/token/class-npuswc-data-token.php' );
	// NPUSWC_Post_Type_Token
	require_once( NPUSWC_DIR_PATH . 'includes/token/class-npuswc-post-type-token.php' );
	// NPUSWC_Token_Handler
	require_once( NPUSWC_DIR_PATH . 'includes/token/class-npuswc-token-handler.php' );
	// NPUSWC_Token_Validator
	require_once( NPUSWC_DIR_PATH . 'includes/token/class-npuswc-token-validator.php' );

// Managers

// Admin
	// NPUSWC_Admin
	require_once( NPUSWC_DIR_PATH . 'includes/admin/class-npuswc-admin.php' );
	// NPUSWC_Admin_Pages
	require_once( NPUSWC_DIR_PATH . 'includes/admin/class-npuswc-admin-pages.php' );
	// NPUSWC_Product_Metabox
	require_once( NPUSWC_DIR_PATH . 'includes/admin/class-npuswc-product-metabox.php' );
	// NPUSWC_Order_Metabox
	require_once( NPUSWC_DIR_PATH . 'includes/admin/class-npuswc-order-metabox.php' );
	// NPUSWC_User_Meta
	require_once( NPUSWC_DIR_PATH . 'includes/admin/class-npuswc-user-meta-manager.php' );

	// File
		// NPUSWC_Filesystem_Methods
		require_once( NPUSWC_DIR_PATH . 'includes/admin/file/class-npuswc-filesystem-methods.php' );

// Auth
	// NPUSWC_REST_API
	require_once( NPUSWC_DIR_PATH . 'includes/rest-api/class-npuswc-rest-api.php' );
	// NPUSWC_REST_API_JWT
	require_once( NPUSWC_DIR_PATH . 'includes/rest-api/class-npuswc-rest-api-jwt.php' );
	// NPUSWC_REST_API_Endpoints
	require_once( NPUSWC_DIR_PATH . 'includes/rest-api/class-npuswc-rest-api-endpoints.php' );
	// NPUSWC_REST_API_Endpoints_JWT
	require_once( NPUSWC_DIR_PATH . 'includes/rest-api/class-npuswc-rest-api-endpoints-jwt.php' );

