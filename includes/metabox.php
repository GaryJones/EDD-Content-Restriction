<?php
/**
 * Metabox
 *
 * @package		EDD Content Restriction
 * @subpackage	Metabox
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Add metabox
 *
 * @global		$post
 * @return		void
 */
function edd_cr_submitbox() {

	global $post;

	if ( !is_object( $post ) )
		return;

	$post_types = get_post_types( array( 'show_ui' => true ) );

	$excluded_types = array( 'download', 'edd_payment', 'reply', 'acf', 'deprecated_log' );

	if ( !in_array( get_post_type( $post->ID ), apply_filters( 'edd_cr_excluded_post_types', $excluded_types ) ) ) {

		$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

		$restricted_to = get_post_meta( $post->ID, '_edd_cr_restricted_to', true );
		$restricted_variable = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices

		if ( $downloads ) {

			echo '<div id="edd-cr-options" style="margin: 0 0 8px;">';
				echo '<label for="edd_cr_download_id">' . sprintf( __( 'Restrict this content to buyers of a %s.', 'edd_cr' ), edd_get_label_singular() ) . '</label>';
				echo '<select name="edd_cr_download_id" id="edd_cr_download_id">';
					echo '<option value="0">' . __( 'Not Restricted', 'edd_cr' ) . '</option>';
					foreach ( $downloads as $download ) {
						echo '<option value="' . absint( $download->ID ) . '" ' . selected( $restricted_to, $download->ID, false ) . '>' . esc_html( get_the_title( $download->ID ) ) . '</option>';
					}
				echo '</select>';
				echo '&nbsp;<img src="' . admin_url( '/images/wpspin_light.gif' ) . '" class="waiting" id="edd_cr_loading" style="display:none;"/>';
				echo '<div id="edd_download_variables">';
				if ( edd_has_variable_prices( $restricted_to ) ) {
					$prices = get_post_meta( $restricted_to, 'edd_variable_prices', true );
					echo '<select name="edd_cr_download_price">';
						echo '<option value="all">' . __( 'All Prices', 'edd_cr' ) . '</option>';
						foreach ( $prices as $key => $price ) {
							echo '<option value="' . absint( $key ) . '" ' . selected( $key, $restricted_variable, false ) . '>' .esc_html( $price['name'] )  . '</option>';
						}
					echo '</select>';
				}
				echo '</div>';
				do_action( 'edd_cr_metabox', $post->ID, $restricted_to, $restricted_variable );
				echo wp_nonce_field( 'edd-cr-nonce', 'edd-cr-nonce' );
			echo '</div>';

		}

	}
}
add_action( 'post_submitbox_start', 'edd_cr_submitbox', 0 );


/**
 * Save metabox data
 *
 * @since		1.0
 * @param		$post_id the ID of this post
 * @return		void
 */
function edd_cr_save_meta_data( $post_id ) {
	if ( isset( $_POST['edd-cr-nonce'] ) && wp_verify_nonce( $_POST['edd-cr-nonce'], 'edd-cr-nonce' ) ) {
		$restricted_to = sanitize_text_field( $_POST['edd_cr_download_id'] );
		$price_option = isset( $_POST['edd_cr_download_price'] ) ? sanitize_text_field( $_POST['edd_cr_download_price'] ) : false;

		update_post_meta( $post_id, '_edd_cr_restricted_to', $restricted_to );

		if ( $price_option !== false ) {
			update_post_meta( $post_id, '_edd_cr_restricted_to_variable', $price_option );
		}

		do_action( 'edd_cr_save_meta_data', $post_id, $_POST );

	}
}
add_action( 'save_post', 'edd_cr_save_meta_data' );
