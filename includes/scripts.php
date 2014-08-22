<?php
/**
 * Add scripts and styles
 *
 * @package     EDD\ContentRestriction\Scripts
 * @copyright	Copyright (c) 2013-2014, Pippin Williamson
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Enqueue scripts if necessary
 *
 * @since		1.0.0
 * @global		object $post The post/page we are editing
 * @return		void
 */
function edd_cr_admin_scripts() {
	global $post;

	// Only enqueue if this is the add/edit post/page screen
	if( is_object( $post ) && isset( $post->ID ) ) {
		wp_enqueue_script( 'edd-cr', EDD_CONTENT_RESTRICTON_DIR . 'assets/js/admin.js', array( 'jquery' ), 'EDD_CONTENT_RESTRICTON_VER' );
	}
}
add_action( 'admin_enqueue_scripts', 'edd_cr_admin_scripts' );