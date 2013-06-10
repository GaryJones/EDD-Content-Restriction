<?php
/**
 * Scripts
 *
 * @package		EDD
 * @subpackage	Scripts
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Enqueue scripts if necessary
 *
 * @since		1.0
 * @global		$post
 * @return		void
 */
function edd_cr_scripts() {
	global $post;

	if( !is_object( $post ) )
		return;

	if( !isset( $post->ID ) )
		return;

	wp_enqueue_script('edd-cr', EDD_CR_PLUGIN_URL . 'js/edd-cr.js', array('jquery'), '1.0');
}
add_action('admin_enqueue_scripts', 'edd_cr_scripts');
