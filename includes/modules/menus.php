<?php
/**
 * Menu Functions
 *
 * @package     EDD\ContentRestriction\Modules\Menus
 * @copyright	Copyright (c) 2013-2014, Pippin Williamson
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function edd_cr_hide_menu_items( $items, $args ) {
	if( edd_get_option( 'edd_content_restriction_hide_menu_items', false ) ) {
		foreach( $items as $item_id => $item_data ) {
			$restricted = edd_cr_is_restricted( $item_data->object_id );

			if( ! empty( $restricted ) ) {
				if( ! edd_has_user_purchased( get_current_user_id(), $restricted ) && ! current_user_can( 'edit_post', $restricted ) ) {
					unset( $items[$item_id] );
				}
			}
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'edd_cr_hide_menu_items', 10, 2 );