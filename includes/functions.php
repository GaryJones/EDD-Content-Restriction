<?php
/**
 * Add Helper Functions and Template Overrides
 *
 * @package     EDD\ContentRestriction\Functions
 * @copyright	Copyright (c) 2013-2014, Pippin Williamson
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Filter content to handle restricted posts/pages
 *
 * @since		1.0.0
 * @param		string $content The content to filter
 * @global		object $post The post we are editing
 * @return		string $content The filtered content
 */
function edd_cr_filter_content( $content ) {
	global $post;

	// If $post isn't an object, we aren't handling it!
	if( ! is_object( $post ) ) {
		return $content;
	}

	$restricted_to       = edd_cr_is_restricted( $post->ID );
	$restricted_variable = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices
	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	if( $restricted_to ) {
		$content = edd_cr_filter_restricted_content( $content, $restricted_to, $restricted_variable, null, $post->ID );
	}

	return $content;
}
add_filter( 'the_content', 'edd_cr_filter_content' );


/**
 * Filter restricted content
 *
 * @since		1.0.0
 * @param		string $content The content to filter
 * @param		int $download_id The ID of the referenced download
 * @param		int $price_id The (optional) ID of a variably priced item
 * @param		string $message The message to display to users
 * @param		int $post_id The ID of the current post/page
 * @param		string $class Additional classes for the displayed error
 * @global		int $user_ID The ID of the current user
 * @return		string $content The content to display to the user
 */
function edd_cr_filter_restricted_content( $content = '', $download_id = 0, $price_id = null, $message = null, $post_id = 0, $class = '' ) {
	global $user_ID;

	// If the current user can edit this post, it can't be restricted!
	if( ! current_user_can( 'edit_post', $post_id ) ) {
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

			foreach( $download_id as $id ) {
				if( edd_has_user_purchased( $user_ID, $id, $price_id ) ) {
					$is_restricted = false;
				}
			}
		} elseif( $download_id && edd_has_user_purchased( $user_ID, $download_id, $price_id ) ) {
			$is_restricted = false;

			if( is_null( $message ) ) {
				$message = $single_message;
			}
		}

		// Guests can't view restricted content... period
		if( ! is_user_logged_in() ) {
			$is_restricted = true;
		}

		// Allow extensions to modify the restriction conditions
		$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $post_id, $download_id, $user_ID, $price_id );

		$message = '<div class="edd_cr_message ' . $class . '">' . $message . '</div>';

		if( $is_restricted ) {
			$content = $message;
		}
	}

	return do_shortcode( $content );
}


/**
 * Check if a post/page is restricted
 *
 * @since		1.0.0
 * @param		int $post_id the ID of the post to check
 * @return		bool True if post is restricted, false otherwise
 */
function edd_cr_is_restricted( $post_id ) {
	$restricted = get_post_meta( $post_id, '_edd_cr_restricted_to', true );

	return $restricted;
}


/**
 * Check post variations for restriction
 *
 * @since		1.0.0
 * @return		void
 */
function edd_cr_check_for_variations() {
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'edd-cr-nonce' ) ) {
		$download_id = isset( $_POST['download_id'] ) ? absint( $_POST['download_id'] ) : 0;

		if ( edd_has_variable_prices( $download_id ) ) {
			$prices = get_post_meta( $download_id, 'edd_variable_prices', true );

			if( $prices ) {
				$response  = '<select name="edd_cr_download_price">';
				$response .= '<option value="all">' . __( 'All Variants', 'edd_cr' ) . '</option>';

				foreach( $prices as $key => $price ) {
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
 * @since		1.3.0
 * @param		object $payment The payment we are processing
 * @param		array $edd_receipt_args The args for a given receipt
 * @return		void
 */
function edd_cr_add_to_receipt( $payment, $edd_receipt_args ) {
	// Get the array of restricted pages for this payment
	$meta = edd_cr_get_restricted_pages( $payment->ID );

	// No pages? Quit!
	if( empty( $meta ) ) {
		return;
	}

	echo '</tbody></table><h3>' . __( 'Pages', 'edd_cr' ) . '</h3><table><tbody>';

	echo '<tr><td>';
	echo '<ul class="edd-cr-receipt">';

	foreach( $meta as $post ) {
		echo '<li>';
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
 * @return		void
 */
function edd_cr_register_email_tags() {
	edd_add_email_tag( 'page_list', __( 'Shows a list of restricted pages the customer has access to', 'edd_cr' ), 'edd_cr_add_template_tags' );
}
add_action( 'edd_add_email_tags', 'edd_cr_register_email_tags' );


/**
 * Add email template tags
 *
 * @since		1.3.0
 * @param		int $payment_id The payment ID
 * @return		string $page_list The list of accessible pages
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
 * Get posts/pages restricted to the purchased files
 *
 * @since		1.3.0
 * @param		int $payment_id The ID of this payment
 * @return		array $meta The list of accessible files
 */
function edd_cr_get_restricted_pages( $payment_id ) {
	// If $payment_id isn't an array, bail!
	if( is_array( $payment_id ) ) {
		$payment_id = $payment_id['id'];
	}

	$files = edd_get_payment_meta_downloads( $payment_id );

	$ids = wp_list_pluck( $files, 'id' );
	$ids = array_unique( $ids );

	$args = array(
		'post_type'				=> 'any',
		'meta_key'				=> '_edd_cr_restricted_to',
		'meta_value'			=> $ids,
		'meta_compare'			=> 'IN',
		'ignore_sticky_posts'	=> true
	);

	$meta_std = new WP_Query( $args );
	$meta_std = $meta_std->posts;

	$args = array(
		'post_type'				=> 'any',
		'meta_key'				=> '_edd_cr_restricted_to_variable',
		'meta_value'			=> $ids,
		'meta_compare'			=> 'IN',
		'ignore_sticky_posts'	=> true
	);

	$meta_var = new WP_Query( $args );
	$meta_var = $meta_var->posts;

	$meta = array_merge( $meta_std, $meta_var );

	return $meta;
}