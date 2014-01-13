<?php
/**
 * Shortcodes
 *
 * @package		EDD Content Restriction
 * @subpackage	Shortcodes
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Add edd_restrict shortcode
 *
 * @since		1.0
 * @param		array $atts the attributes to pass to the shortcode
 * @param		string $content
 */
function edd_cr_shortcode($atts, $content = null ) {
	extract( shortcode_atts( array(
			'id'       => null,
			'price_id' => null,
			'message'  => null,
			'class'    => ''
		), $atts )
	);

	if( is_null( $id ) )
		return $content;

	$ids = explode(',', $id);

	return edd_cr_filter_restricted_content( $content, $ids, $price_id, $message, 0, $class );

}
add_shortcode('edd_restrict', 'edd_cr_shortcode');



/**
 * Displays a list of restricted pages the currently logged-in user has access to
 *
 * @since		1.5
 * @param		array $atts the attributes to pass to the shortcode
 * @param		string $content
 */
function edd_cr_pages_shortcode($atts, $content = null ) {
	extract( shortcode_atts( array(
			'class'    => ''
		), $atts )
	);

	if( is_user_logged_in() ) {

		$pages     = array();
		$purchases = edd_get_users_purchases( get_current_user_id(), -1 );
		if( $purchases ) {
			foreach( $purchases as $purchase ) {

				$restricted = edd_cr_get_restricted_pages( $purchase->ID );
				if( empty( $restricted ) ) {
					continue;
				}

				$page_ids = wp_list_pluck( $restricted, 'ID' );
				$pages    = array_unique( array_merge( $page_ids, $pages ) );
			}

			if( ! empty( $pages ) ) {

				$content = '<ul class="edd_cr_pages">';

				foreach( $pages as $page_id ) {
					$content .= '<li><a href="' . esc_url( get_permalink( $page_id ) ) . '">' . get_the_title( $page_id ) . '</a></li>';
				}

				$content .= '</ul>';

			} else {
				$content = '<div class="edd_cr_no_pages">' . __( 'You have not purchased access to any content.', 'edd' ) . '</div>';
			}

		} else {
			$content = '<div class="edd_cr_no_pages">' . __( 'You have not purchased access to any content.', 'edd' ) . '</div>';
		}

	} else {
		$content = '<div class="edd_cr_not_logged_in">' . __( 'You must be logged in to access your purchased content.', 'edd' ) . '</div>';
	}

	return $content;
}
add_shortcode('edd_restricted_pages', 'edd_cr_pages_shortcode');