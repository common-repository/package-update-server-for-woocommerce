<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'NPUSWC_File_System_Methods' ) ) {
class NPUSWC_File_System_Methods {

	const DOWNLOADS_DIR_PATH = NPUSWC_DIR_PATH . 'downloads/';
	const DOWNLOADS_DIR_URL = NPUSWC_DIR_URL . 'downloads/';

	/**
	 * Init
	**/
		/**
		 * File System Init
		 * 
		 * @param string $url
		 * @param string $nonce
		 * @param string $current_user_can : Default "manage_options"
		 * 
		 * @return bool
		**/
		public static function init_wp_filesystem( $url, $nonce, $current_user_can = 'manage_options' )
		{

			// Init Filesystem
				if ( ! current_user_can( $current_user_can ) ) {
					return false;
				}
				
			// Check if Writable
				$nonce_url = esc_url( wp_nonce_url( $url, $nonce ) );
				if( false === ( $creds = request_filesystem_credentials( $nonce_url, '', false, '', array() ) ) ) {
					return false;
				}
				
			// Init WP_Filesystem_Base
				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $nonce_url, '', true, false, null );
					return false;
				}

			return true;

		}

		/**
		 * AJAX Init File System
		 * 
		 * @param string $url
		 * @param string $nonce
		 * @param string $input_access_type : Default "direct"
		 * 
		 * @return bool
		**/
		public static function init_wp_filesystem_for_ajax( $url, $nonce, $input_access_type = 'direct' )
		{

			$access_type = get_filesystem_method();
			if( $access_type === $input_access_type ) {

				$nonce_url = esc_url( wp_nonce_url( $url, $nonce ) );

				$creds = request_filesystem_credentials( $nonce_url, '', false, false, array() );

				if ( ! WP_Filesystem( $creds ) ) {
					return false;
				}	

				return true;

			}

			return false;

		}

	/**
	 * Read
	**/


	/**
	 * Write
	**/
		/**
		 * Upload Files
		 * 
		 * @param object $wp_filesystem   : Should be Init
		 * @param string $uplaod_dir      : Default  ""
		 * @param array  $file_extensions : Defauult array()
		 * 
		 * @return array $file_list
		**/
		public static function upload_files( $wp_filesystem, $upload_dir = '', $file_extensions = array() )
		{

			// Check if FILES not empty and User Authority
			if( ! ( count( $_FILES ) > 0 && current_user_can( 'manage_options' ) ) ) {
				return false;
			}

			// Vars
				// Returned File List
				$file_list = array();

				// File Extensions
				$file_extensions_str = implode( '|', $file_extensions );

			// Each File
			foreach( $_FILES as $index => $file ) {

				if( preg_match(
					'/([^\s]+)\.(' . $file_extensions_str . ')$/',
					$file['name'],
					$matched
				) ) {

					// File Name
						$file_name = sanitize_file_name( $matched[1] );
						$file_name_with_extension = sanitize_file_name( $file[ 'name' ] );

					// Check if it has Store/Seller ID
						$is_ok_to_upload = apply_filters( 'npuswc_filter_is_ok_to_upload', true, $file_name_with_extension, $filename, $file_extensions_str );

						if( strpos( $file_name, $seller_id ) === false ) {
							$file_list[$file_name] = "File Name Does NOT include Seller ID.";
							continue;
						}

					// File Path
						$file_tail = $matched[2];
						$file_path = $upload_dir . $file['name'];

					// Change Mode
						//$wp_filesystem->chmod( $file_path, 0755 );

					// For the Date
						touch( $file_path );

					// File Upload
						$wp_filesystem->move(
							$file['tmp_name'],
							$file_path
						);

					// File Last Modified Time
						$file_mtime = $wp_filesystem->mtime( $file_path );
						$file_list[ $file_name_with_extension ] = array(
							'file_name' => $file_name,
							'file_tail' => $file_tail,
							'file_mtime' => date( "F d Y H:i:s", absint( $file_mtime ) ),
							'file_is_imported' => ( 
								isset( $imported_file_list[ $file_name_with_extension ] )
								&& $imported_file_list[ $file_name_with_extension ]
							)
						);

					// Change Mode
						//$wp_filesystem->chmod( $file_path, 0600 );

				} else {

					$file_name = sanitize_file_name( $file['name'] );
					$file_list[ $file_name ] = "Not Key or CRT Files.";

				}

			}

			// End
			return $file_list;

		}

	/**
	 * Make
	**/
		/**
		 * 
		 * @param [string] $directory 
		 * @param [string] $file_name : Default "file.zip"
		 * @param [string] $where     : Default null 
		**/
		public static function make_zip_from_directory( $directory, $file_name = 'file.zip', $where = null )
		{

			// Vars
			$admin_url = add_query_arg( array( 'page' => 'npuswc_admin_page' ), admin_url( 'admin.php' ) );
			$nonce     = 'download-zip-files';
			$result    = self::init_wp_filesystem( $admin_url, $nonce, 'manage_options' );

			// Prepare for init wp filesystem
			if ( ! $result ) {
				return;
			}

			// Init
			global $wp_filesystem;


			// Vars
			$zip = new ZipArchive();
			$temp_download_dir_path = is_string( $where ) && '' !== $where ? $where : NPUSWC_File_System_Methods::DOWNLOADS_DIR_PATH;
			$temp_download_file_path = $temp_download_dir_path . $file_name;

			// Open
			$result = $zip->open(
				$temp_download_file_path,
				ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE
			);

			if ( $result !== true ) {
				return false;
			}

			// Add to the zip
			set_time_limit( 0 );
			NPUSWC_File_System_Methods::add_to_the_zip( $zip, $directory ); 
			$zip->close();

			return true;

		}


		/**
		 * 
		 * @param [ZipArchive] &$zip : reference
		 * @param [string] $path description
		**/
		public static function add_to_the_zip( &$zip, $path, $dir_root = null )
		{

			if ( null === $dir_root ) {
				$dir_root = $path;
			}

			if ( is_dir( $path ) ) {

				$files = array_diff( scandir( $path ), array( '.', '..' ) );

				foreach ( $files as $file ) {

					NPUSWC_File_System_Methods::add_to_the_zip( $zip, "$path/$file", $dir_root );

				}

			} else {

				$will_remove = $dir_root . '/';
				$added_path = str_replace( $will_remove, '', $path );
				$zip->addFromString( $added_path, file_get_contents( $path ) );

			}

		}

	/**
	 * Download
	**/
		/**
		 * 
		 * @param [string] $directory 
		 * @param [string] $file_name    : Default "file.zip"
		 * @param [string] $where        : Default null 
		 * @param [bool]   $is_temp_file : Default true 
		**/
		public static function download_zip_from_directory( $directory, $file_name = 'file.zip', $where = null, $is_temp_file = true )
		{

			// Vars
			$temp_download_dir_path = is_string( $where ) && '' !== $where ? $where : NPUSWC_File_System_Methods::DOWNLOADS_DIR_PATH;
			$temp_download_file_path = $temp_download_dir_path . $file_name;

			// Make zip file
			$result = NPUSWC_File_System_Methods::make_zip_from_directory( $directory, $file_name, $where );

			if ( false === $result ) {
				printf(
					__( 'Wrong', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ),
					''
				);
				return;
			}

			// ストリームに出力
			header( 'Content-Type: application/zip; name="' . $file_name . '"' );
			header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
			header( 'Content-Length: ' . filesize( $temp_download_file_path ) );
			// Clean
			ob_clean();
			flush();
			ob_end_flush();

			// Read file
			readfile( $temp_download_file_path );

			if ( $is_temp_file ) {
				unlink( $temp_download_file_path );
			}

		}


}
}


