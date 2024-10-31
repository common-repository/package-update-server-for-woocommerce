<?php

if ( ! function_exists( 'npuswc_get_post_meta' ) ) {
	/**
	 * Get post meta array
	 * @param int    $post_id
	 * @param string $post_meta_key
	 * @return mixed
	**/
	function npuswc_get_post_meta( int $post_id, string $post_meta_key = '' )
	{

		$post_meta = get_post_meta( $post_id, $post_meta_key, true );

		if ( is_string( $post_meta ) && '' !== $post_meta ) {

			$json_decoded = json_decode( $post_meta, true );

			if ( null === $json_decoded ) {

				return $post_meta;

			}

			return $json_decoded;

		}

		return $post_meta;

	}
}


