<?php
/**
 * Package Update Server for WooCommerce
 *
 * @package     Package Update Server for WooCommerce
 * @author      Nora
 * @copyright   2018 Nora https://package-update-server.com
 * @license     GPL-2.0+
 * 
 * @wordpress-plugin
 * Plugin Name: Package Update Server for WooCommerce
 * Plugin URI: https://package-update-server.com
 * Description: Extension for Plugins "WooCommerce".
 * Version: 1.0.12
 * Author: nora0123456789
 * Author URI: https://wp-works.com
 * Text Domain: npuswc
 * Domain Path: /i18n/languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
**/


// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Degine Plugin Dir Path
if( ! defined( 'NPUSWC_MAIN_FILE' ) ) define( 'NPUSWC_MAIN_FILE', __FILE__ );
if( ! defined( 'NPUSWC_DIR_PATH' ) ) define( 'NPUSWC_DIR_PATH', plugin_dir_path( __FILE__ ) );
if( ! defined( 'NPUSWC_DIR_URL' ) ) define( 'NPUSWC_DIR_URL', plugin_dir_url( __FILE__ ) );

// Define Class Nora_Package_Update_Server_For_WooCommerce
require_once( NPUSWC_DIR_PATH . 'includes/class-npuswc.php' );

/**
 * Init Nora_Package_Update_Server_For_WooCommerce
**/
function npuswc()
{
	global $npuswc;
	if ( ! $npuswc instanceof Nora_Package_Update_Server_For_WooCommerce ) {
		$npuswc = Nora_Package_Update_Server_For_WooCommerce::get_instance();
	}
	return $npuswc;
}
global $npuswc;
$npuswc = npuswc();
