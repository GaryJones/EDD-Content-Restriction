<?php


function edd_cr_filter_content( $content ) {

	global $post;

	if( ! is_object( $post ) )
		return $content;

	$restricted_to       = edd_cr_is_restricted( $post->ID );
	$restricted_variable = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices
	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	if( $restricted_to )
		return edd_cr_filter_restricted_content( $content, $restricted_to, $restricted_variable, null, $post->ID );
	return $content;
}
add_filter( 'the_content', 'edd_cr_filter_content' );


function edd_cr_filter_restricted_content( $content, $download_id, $price_id = null, $message = null, $post_id = 0 ) {

	global $user_ID;

	$is_restricted  = true;
	$multi_message  = '<div class="edd_cr_message">' . __( 'This content is restricted to buyers.', 'edd_cr' ) . '</div>';

	if( ! empty( $price_id ) ) {
		$single_message = '<div class="edd_cr_message">';
		$single_message .= sprintf(
								__( 'This content is restricted to buyers of the %s for %s.', 'edd_cr' ),
								edd_get_price_option_name( $download_id, $price_id ),
								'<a href="' . get_permalink( $download_id ) . '">' . get_the_title( $download_id ) . '</a>'
							);
		$single_message .= '</div>';
	} else {
		$single_message = '<div class="edd_cr_message">';
		$single_message .= sprintf(
							__( 'This content is restricted to buyers of %s.', 'edd_cr' ),
							'<a href="' . get_permalink( $download_id ) . '">' . get_the_title( $download_id ) . '</a>'
						);
		$single_message .= '</div>';
	}

	$message = $single_message;

	if ( is_array( $download_id ) ) {

		$message = $multi_message;

		foreach ( $download_id as $id ) {

			if ( edd_has_user_purchased( $user_ID, $id, $price_id ) ) {

				$is_restricted = false;
			}

		}

	} elseif ( $download_id && edd_has_user_purchased( $user_ID, $download_id, $price_id ) ) {

		$is_restricted = false;
		$message       = $single_message;

	}

	$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $post_id, $download_id, $user_ID, $price_id );

	if( $is_restricted )
		return $message;
	else
		return $content;

}


function edd_cr_is_restricted( $post_id ) {
	return get_post_meta( $post_id, '_edd_cr_restricted_to', true );
}


function edd_cr_check_for_variations() {

	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'edd-cr-nonce' ) ) {

		$download_id = isset( $_POST['download_id'] ) ? absint( $_POST['download_id'] ) : 0;

		if ( edd_has_variable_prices( $download_id ) ) {

			$prices = get_post_meta( $download_id, 'edd_variable_prices', true );
			if ( $prices ) {
				$response = '<select name="edd_cr_download_price">';
				$response .= '<option value="all">' . __( 'All prices', 'edd_cr' ) . '</option>';
				foreach ( $prices as $key => $price ) {
					$response .= '<option value="' . $key . '">' . $price['name']  . '</option>';
				}
				$response .= '</select>';
			}
			echo $response;
		}
	}
	die();

}
add_action( 'wp_ajax_edd_cr_check_for_variations', 'edd_cr_check_for_variations' );