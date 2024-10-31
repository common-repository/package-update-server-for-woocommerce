<?php
/**
 * 
 * Defined vars :
 * $options
 * 
 * 
 * 
**/
?>
<div class="metabox-holder npuswc-metabox-holder" style="padding-right: 10px;">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'Introduction', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></h3>
		<div class="inside"><div class="main">

			<p><?php esc_html_e( 'This plugin enables you to update downloadable products (like WordPress themes and plugins ) sold by WooCommerce.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?> <?php esc_html_e( 'Then, clients can update the theme or the plugin in admin page like themes and plugins in WordPress repository while the download permission of the purchase is not expired.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?> <?php esc_html_e( 'Please read the how-to below.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></p>

		</div></div>
	</div>
</div>

<div class="metabox-holder" style="padding-right: 10px;">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle">
			<?php esc_html_e( 'How to Use', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?>
		</h3>
		<div class="inside"><div class="main">

			<p><?php
				$guide_page_url = __( 'https://package-update-server.com/how-to-use-our-plugins/', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				$format = __( 'Please read how-to in the page below.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				printf( $format );
				$format = ' <a href="%1$s" class="%2$s" target="_blank">' . __( 'Package Update Server', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</a>' . ' ( Preparing )';
				printf( $format, esc_url( $guide_page_url ), 'guide-page-link' );
			?></p>

			<?php npuswc_print_list( array(
				array(
					'html' => '<p>' . sprintf( 
						__( 'If your %1$s is not setup yet, go to %2$s and press the button "Save changes".', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						sprintf(
							'<a href="%1$s">%2$s</a>',
							wc_get_account_endpoint_url( 'npuswc-tokens' ),
							__( 'Download Tokens Page', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
						),
						sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url( add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'advanced' ), admin_url( 'admin.php' ) ) ),
							__( 'WooCommerce advance settings page', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
						)
					) . '</p>'
					. '<p>' . __( 'Just press the button to add the endpoint for purchased tokens of update checker to client\'s account page.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				), 
				array(
					'html' => '<p>' . __( 'Package should request the package data, then request the file in a proper way in order to work with updating service by the shop.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
					. '<p>' . __( 'If you are selling WordPress Theme or Plugin, go to tool tab to download "NPUSWC Client" and include it in the package.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				),
				array(
					'html' => '<p>' . __( 'Setup the downloadable product with "Package Type ( Theme, Plugin or Others )". ', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>' .
					npuswc_print_list( array(
						array(
							'html' => '<p>' . __( 'Downloadable file name should be directory name of the theme or the plugin.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
						),
						array(
							'html' => '<p>' . __( 'Set the "Package Type", "Package Version" and "Tested Environment Version".', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
						),
					), 'ul', false )
				),
				array(
					'html' => '<p>' . __( '* Please notify or indicate your clients to get the generated token to copy it to the update checker setting page.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				),
			), 'ol' ); ?>

		</div></div>
	</div>
</div>

