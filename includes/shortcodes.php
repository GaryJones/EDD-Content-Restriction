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
			'id' => null,
			'price_id' => null,
			'message' => null
		), $atts )
	);

	if( is_null($id) )
		return $content;

	$ids = explode(',', $id);

	return edd_cr_filter_restricted_content( $content, $ids, $price_id, $message );

}
add_shortcode('edd_restrict', 'edd_cr_shortcode');
