<?php
/**
 * Functions
 *
 * @package		EDD Content Restriction
 * @subpackage	Functions
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Filter content
 *
 * @since		1.0
 * @global		$post
 * @param		string $content the content to filter
 * @return		string
 */
function edd_cr_filter_content( $content ) {

	global $post;

	if( ! is_object( $post ) )
		return $content;

	$restricted_to       = edd_cr_is_restricted( $post->ID );
	$restricted_variable = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices
	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	if( $restricted_to ) {
		return edd_cr_filter_restricted_content( $content, $restricted_to, $restricted_variable, null, $post->ID );
	}

	return $content;
}
add_filter( 'the_content', 'edd_cr_filter_content' );


/**
 * Filter restricted content
 *
 * @since		1.0
 * @global		$user_ID
 * @param		string $content the content to filter
 * @param		int $download_id the ID of the referenced download
 * @param		int $price_id
 * @param		string $message
 * @param		int $post_id
 * @return		string
 */
function edd_cr_filter_restricted_content( $content = '', $download_id = 0, $price_id = null, $message = null, $post_id = 0, $class = '' ) {

	global $user_ID;

	if( current_user_can( 'edit_post', $post_id ) ) {
		return do_shortcode( $content );
	}

	$is_restricted  = true;
	$multi_message  = __( 'This content is restricted to buyers.', 'edd_cr' );

	if( ! empty( $price_id ) ) {
		$single_message = sprintf(
			__( 'This content is restricted to buyers of the %s for %s.', 'edd_cr' ),
			edd_get_price_option_name( $download_id, $price_id ),
			'<a href="' . get_permalink( $download_id ) . '">' . get_the_title( $download_id ) . '</a>'
		);
	} elseif( ! is_array( $download_id ) ) {
		$single_message = sprintf(
			__( 'This content is restricted to buyers of %s.', 'edd_cr' ),
			'<a href="' . get_permalink( $download_id ) . '">' . get_the_title( $download_id ) . '</a>'
		);
	}

	if( ! empty( $single_message ) && is_null( $message ) && count( $download_id ) <= 1 ) {
		$message = $single_message;
	}

	if ( is_array( $download_id ) && count( $download_id ) > 1 ) {

		if( is_null( $message ) ) {
			$message = $multi_message;
		}

		foreach ( $download_id as $id ) {

			if ( edd_has_user_purchased( $user_ID, $id, $price_id ) ) {

				$is_restricted = false;
			}

		}

	} elseif ( $download_id && edd_has_user_purchased( $user_ID, $download_id, $price_id ) ) {

		$is_restricted = false;

		if( is_null( $message ) ) {
			$message = $single_message;
		}

	}

	if( ! is_user_logged_in() ) {
		$is_restricted = true;
	}

	$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $post_id, $download_id, $user_ID, $price_id );


	$message = '<div class="edd_cr_message ' . $class . '">' . $message . '</div>';

	if( $is_restricted ) {
		return do_shortcode( $message );
	} else {
		return do_shortcode( $content );
	}
}


/**
 * Check if a post is restricted
 *
 * @since		1.0
 * @param		int $post_id the ID of the post to check
 */
function edd_cr_is_restricted( $post_id ) {
	$restricted = get_post_meta( $post_id, '_edd_cr_restricted_to', true );

	return $restricted;
}


/**
 * Check post variations for restriction
 *
 * @since		1.0
 */
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


/**
 * Add restricted content to confirmation page
 *
 * @since		1.3
 * @param		array $edd_receipt_args
 * @return		void
 */
function edd_cr_add_to_receipt( $payment, $edd_receipt_args ) {

	// Get the array of restricted pages for this payment
	$meta = edd_cr_get_restricted_pages( $payment->ID );

	// No pages? Quit!
	if( empty( $meta ) ) return;

	echo '</tbody></table><h3>' . __( 'Pages', 'edd_cr' ) . '</h3><table><tbody>';

	echo '<tr><td>';
	echo '<ul style="margin: 0; padding: 0;">';

	foreach( $meta as $post ) {
		echo '<li style="list-style: none; margin: 0 0 8px 10px;">';
		echo '<a href="' . get_permalink( $post->ID ) . '" class="edd_download_file_link">' . $post->post_title . '</a>';
		echo '</li>';
	}

	echo '</ul>';
	echo '</td></tr>';
}
add_action( 'edd_payment_receipt_after', 'edd_cr_add_to_receipt', 1, 2 );

/**
 * Registers our email tags
 *
 * @since		1.5.4
 */
function edd_cr_register_email_tags() {
	edd_add_email_tag( 'page_list', __( 'Shows a list of restricted pages the customer has access to', 'edd_cr' ), 'edd_cr_add_template_tags' );
}
add_action( 'edd_add_email_tags', 'edd_cr_register_email_tags' );

/**
 * Add email template tag
 *
 * @since		1.3
 * @param		string $message the content of the email message
 * @param		array $payment_data the information on this payment
 * @param		int $payment_id the payment ID
 * @return		string $message the updated email message
 */
function edd_cr_add_template_tags( $payment_id ) {

	// Get the array of restricted pages for this payment
	$meta = edd_cr_get_restricted_pages( $payment_id );

	// No pages? Quit!
	if( empty( $meta ) ) {
		return '';
	}

	$page_list = '<div class="edd_cr_accessible_pages">' . __( 'Pages', 'edd_cr' ) . '</div>';
	$page_list .= '<ul>';

	foreach( $meta as $post ) {
		$page_list .= '<li><a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a></li>';
	}

	$page_list .= '</ul>';
	$page_list .= '</li>';

	return $page_list;
}

/**
 * Get pages restricted to the purchased files
 *
 * @since		1.3
 * @access		public
 * @param		mixed $payment_id
 * @return		array $meta
 */
function edd_cr_get_restricted_pages( $payment_id ) {
	if( is_array( $payment_id ) )
		$payment_id = $payment_id['id'];

	$files = edd_get_payment_meta_downloads( $payment_id );

	$ids = wp_list_pluck( $files, 'id' );
	$ids = array_unique( $ids );

	$args = array(
		'post_type'		=> 'any',
		'meta_key'		=> '_edd_cr_restricted_to',
		'meta_value'	=> $ids,
		'meta_compare'	=> 'IN',
		'ignore_sticky_posts' => true
	);


	$meta_std = new WP_Query( $args );
	$meta_std = $meta_std->posts;

	$args = array(
		'post_type'		=> 'any',
		'meta_key'		=> '_edd_cr_restricted_to_variable',
		'meta_value'	=> $ids,
		'meta_compare'	=> 'IN',
		'ignore_sticky_posts' => true
	);

	$meta_var = new WP_Query( $args );
	$meta_var = $meta_var->posts;

	$meta = array_merge( $meta_std, $meta_var );

	return $meta;
}
