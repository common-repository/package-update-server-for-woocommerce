<?php

$order_id = intval( $order->ID );

$token_holder  = json_decode( get_post_meta( $order_id, '_npuswc_registered_token_ids', true ), true );


$hashed_urls = get_post_meta( $order_id, '_npuswc_hashed_user_site_urls', true );
$hashed_urls = is_string( $hashed_urls ) && '' !== $hashed_urls ? $hashed_urls : '{}';
$hashed_urls = json_decode( $hashed_urls, true );
$hashed_urls = (
	is_array( $hashed_urls )
	? $hashed_urls
	: array()
);


//npuswc_test_var_dump( $jwt_holder );
//npuswc_test_var_dump( $jwt_signers );

if ( is_array( $token_holder ) && 0 < count( $token_holder ) ) {
?>
<table id="package_update_server-settings" class="form-table" style="border: solid #eee 1px;">
	<thead>
		<tr>
			<th style="padding: 10px;"><?php esc_html_e( 'Product', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Number of the purchased product', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Registered hashed URLs', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ); ?></th>
		</tr>
	</thead>

	<tbody>
<?php
	foreach ( $token_holder as $product_id => $registered_token_ids ) {

		if ( is_array( $registered_token_ids ) && 0 < count( $registered_token_ids ) ) {
		foreach ( $registered_token_ids as $registered_token_index => $registered_token_id ) {

			$data_token = NPUSWC_Data_Token::get_instance( $registered_token_id );

			$token = $data_token->get_the_latest_purchased_token();
			$download_key = $data_token->get_prop( 'download_id' );

			$purchased_number = npuswc_get_purchased_downloadable_number( $order_id, $download_key );

			$file = $data_token->get_prop( 'file' );
			$file_name = $file['name'];


			echo '<tr>';
				echo '<th style="padding: 10px;">' . esc_html( $file_name ) . '</th>';
				echo '<td style="padding: 10px;"><input type="text" value="' . esc_html( $token ) . '" class="regular-text" style="width: 200px; overflow: scrolled;" disabled></td>';
				echo '<td style="padding: 10px;">' . esc_html( $purchased_number ) . '</td>';

				echo '<td style="padding: 10px;">';
					if ( isset( $hashed_urls[ $download_key ] )
						&& is_array( $hashed_urls[ $download_key ] )
						&& 0 < count( $hashed_urls[ $download_key ] )
					) {
					foreach ( $hashed_urls[ $download_key ] as $hashed_url ) {
						echo '<input type="text" value="' . $hashed_url . '" disabled><br>';
					}
					}
				echo '</td>';
			echo '</tr>';

		}
		}

	}
}

?>
	</tbody>
</table>
