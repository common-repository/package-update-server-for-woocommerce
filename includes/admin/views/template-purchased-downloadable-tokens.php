<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
$customer_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query', array(
	'customer' => get_current_user_id(),
	'page'     => $current_page,
	'paginate' => true,
) ) );

$token_holder = array();
if ( is_array( $customer_orders->orders ) && 0 < count( $customer_orders->orders ) ) {
foreach ( $customer_orders->orders as $customer_order ) {

	$npuswc_order = new NPUSWC_Order( $customer_order->get_id() );
	$all_registered_token_ids = npuswc_get_post_meta( $npuswc_order->get_id(), '_npuswc_registered_token_ids' );
	if ( ! is_array( $all_registered_token_ids ) || 0 >= count( $all_registered_token_ids ) ) {
		continue;
	}

	foreach ( $all_registered_token_ids as $token_product_id => $registered_token_ids ) {

		if ( ! is_array( $registered_token_ids ) || 0 >= count( $registered_token_ids ) ) {
			continue;
		}

		$purchased_number = intval( $npuswc_order->get_token_item_quantity( $token_product_id ) );
		if ( 0 >= $purchased_number ) {
			continue;
		}

		foreach ( $registered_token_ids as $registered_token_index => $registered_token_id ) {

			try { 
				$data_token = NPUSWC_Data_Token::get_instance( $registered_token_id );

				$token_id = $data_token->get_token_id();
				$token = $data_token->get_the_latest_purchased_token();
				if ( ! is_string( $token ) || '' === $token ) {
					throw new Exception( esc_html__( 'Token is empty.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}
				$token_obj = NPUSWC_Token_Methods::parse_from_string( $token );
				if ( in_array( $token_obj, array( false, null, '' ) ) ) {
					throw new Exception( esc_html__( 'Token Object is empty.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

				$result = npuswc()->get_token_manager()->validate_expiry( $token_obj );
				if ( is_string( $result ) ) {
					throw new Exception( esc_html__( 'Expired.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
				}

			} catch ( Exception $e ) {
				continue;
			}

			$token_holder[ $token_id ] = $data_token;

		}

	}
}
}

if ( is_array( $token_holder ) && 0 < count( $token_holder ) ) {
	echo '<section class="woocommerce-order-npuswc-tokens">';
	echo '<table class="woocommerce-table woocommerce-table--order-npuswc-tokens shop_table shop_table_responsive order_details"">';
		echo '<thead>';
			echo '<tr>';
				$format_th = '<td class="token-%1$s"><span class="nobr">%2$s</span></td>';

				$product_name_label       = esc_html__( 'Product Name', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				$product_token_type_label = esc_html__( 'Version', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				$product_expire_label     = esc_html__( 'Expiry', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				//$purchased_quantity_label = esc_html__( 'Quantity', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
				$product_token_label      = esc_html__( 'Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );

				printf( $format_th, 'product', $product_name_label );
				printf( $format_th, 'type', $product_token_type_label );
				printf( $format_th, 'expires', $product_expire_label );
				//printf( $format_th, 'quantity', $purchased_quantity_label );
				printf( $format_th, 'text', $product_token_label );

			echo '</tr>';
		echo '</thead>';

		echo '<tbody>';
			$format = '<td class="token-%1$s" data-title="%2$s">%3$s</td>';
			
			//foreach ( $token_holder as $token_id => $token_obj ) {
			foreach ( $token_holder as $token_id => $data_token ) {

				$token_obj = NPUSWC_Token_Methods::parse_from_string( $data_token->get_the_latest_purchased_token() );

				// Order
				$order_id = intval( $data_token->get_prop( 'order_id' ) );
				$npuswc_order = new NPUSWC_Order( $order_id );

				// Token Type
				$product_id = $data_token->get_prop( 'product_id' );
				//$package_version = $data_token->get_prop( 'package_version' );
				$package_version = get_post_meta( $product_id, '_npuswc_product_package_version', true );
				if ( ! npuswc_is_string_and_version( $package_version ) ) {
					$package_version = '1.0.0';
				}

				$package_version_label = esc_html__( 'Version', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );

				$expire_timestamp = $data_token->get_prop( 'access_expiry' );
				if ( -1 === $expire_timestamp ) {
					$expire_with_format = '&#8734;';
				} elseif ( 0 < $expire_timestamp ) {
					$expire_with_format = date_i18n( 'Y-m-d', $expire_timestamp );
				}


				$token_product_id = intval( $data_token->get_prop( 'product_id' ) );
				$token_text = $token_obj->__toString();

				// Product Name
				$wc_product = WC()->product_factory->get_product( intval( $token_product_id ) );
				$product_name = $data_token->get_prop( 'product_name' );

				// Popup button
				$popup_button_format = '<a id="%1$s" class="%2$s" data-order="%3$s" data-product-id="%4$s" href="javascript: void( 0 );">%5$s</a>';
				$popup_button = sprintf( $popup_button_format,
					'npuswc-customer-token-' . $token_product_id,
					'npuswc-customer-token button alt',
					$customer_order->get_id(),
					$token_product_id,
					esc_html__( 'Get the Token', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
				);

				// Input
				$input_form_with_token = '<input type="hidden" id="npuswc-customer-purchased-token-%1$s" class="npuswc-token" value="%2$s" disabled style="padding: 2px; width: 100px; border-radius: 3px; overflow: scroll;">';
				$input_with_token = sprintf( 
					$input_form_with_token,
					$token_id,
					$token_text
				);

				echo '<tr>';

					// Print
					printf( $format, 'product', $product_name_label, $product_name );
					printf( $format, 'version', $package_version_label, $package_version );
					printf( $format, 'expires', $product_expire_label, $expire_with_format );
					//printf( $format, 'quantity', $purchased_quantity_label, $purchased_number );
					$this->popup_get_the_token( $token_id, $token_text );
					//printf( $format, 'text', $product_token_label, $popup_button . $input_with_token );


				echo '</tr>';

			}

		echo '</tbody>';

	echo '</table>';
	echo '</section>';
} else {
	echo '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">';
		printf(
			'<a class="woocommerce-Button button" href="%1$s">%2$s</a>',
			wc_get_page_permalink( 'shop' ),
			esc_html__( 'To Shop', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN )
		);
		esc_html_e( 'You don\'t have any available purchased token.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN );
	echo '</div>';
}










