<?php
do_action( 'npuswc_action_tool_page_start' );
?>
<div class="metabox-holder">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'NPUSWC Client', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></h3>
		<div class="inside"><div class="main">
			<table id="general-settings" class="form-table">
				<tbody>

					<tr>
						<th scope="row">
						<p><?php esc_html_e( "NPUSWC Client", Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></p>
						</th>
						<td>
							<a id="button-download-npuswc-client" class="button button-primary" href="https://package-update-server.com/product/npuswc-client/"><?php esc_html_e( 'Download', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></a>
						</td>
						<td>
						</td>
					</tr>

				</tbody>
			</table>

			<p><?php esc_html_e( 'Download "NPUSWC Client( file name : npuswc-client )" above and include it in the product package.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></p>

			<p><?php esc_html_e( 'Example.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></p>

			<pre style="margin: 0; padding: 10px; border: solid 1px #ccc;">
/**
 * Package Update Server Client
 * 
 * @param string $file
 * @param string $textdomain
 * @param string $plugin_dir_name
 * @param string $api_url_base
 */
require_once( 'path/to/npuswc-client/class-npuswc-client-loader.php' );
$this->npuswc_client = NPUSWC_Client_Loader::load(
	__MAINFILE__, // like "path/to/functions.php" of the theme or "path/to/plugin-file.php" of the plugin
	__TEXTDOMAIN__, // 
	__PACKAGE_DIR_NAME__, // Folder name of the package
	"<?php echo esc_url( get_site_url() ); ?>" // URL of WooCommerce site which installed this
);
			</pre>
		</div></div>
	</div>
</div>
<?php
