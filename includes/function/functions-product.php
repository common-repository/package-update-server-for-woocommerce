<?php

if ( ! function_exists( 'npuswc_get_product' ) ) {
	/**
	 * Get product by ID
	 * @param  mixed $product_id 
	 * @return WC_Product
	 */
	function npuswc_get_product( $product_id )
	{
		if ( ! did_action( 'woocommerce_init' ) ) {
			return false;
		}
		return WC()->product_factory->get_product( $product_id );
	}
}

