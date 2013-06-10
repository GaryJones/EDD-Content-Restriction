<?php
/**
 * Settings
 *
 * @package		EDD Content Restriction
 * @subpackage	Settings
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.1
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Registers the new Content Restriction license options in Misc
 *
 * @access      private
 * @since       1.1
 * @param 		$settings array the existing plugin settings
 * @return      array
*/
function edd_cr_license_settings( $settings ) {

	$license_settings = array(
		array(
			'id' => 'edd_cr_header',
			'name' => '<strong>' . __('Content Restriction', 'edd_cr') . '</strong>',
			'desc' => '',
			'type' => 'header',
			'size' => 'regular'
		),
		array(
			'id' => 'edd_cr_license_key',
			'name' => __('License Key', 'edd_cr'),
			'desc' => __('Enter your license for Content Restriction to receive automatic upgrades', 'edd_cr'),
			'type' => 'text',
			'size' => 'regular'
		)
	);

	return array_merge( $settings, $license_settings );

}
add_filter('edd_settings_misc', 'edd_cr_license_settings');


function edd_cr_activate_license() {
	global $edd_options;
	if( ! isset( $_POST['edd_settings_misc'] ) )
		return;
	if( ! isset( $_POST['edd_settings_misc']['edd_cr_license_key'] ) )
		return;

	if( get_option( 'edd_cr_license_active' ) == 'valid' )
		return;

	$license = sanitize_text_field( $_POST['edd_settings_misc']['edd_cr_license_key'] );

	// data to send in our API request
	$api_params = array( 
		'edd_action'=> 'activate_license', 
		'license' 	=> $license, 
		'item_name' => urlencode( EDD_CR_PRODUCT_NAME ) // the name of our product in EDD
	);
	
	// Call the custom API.
	$response = wp_remote_get( add_query_arg( $api_params, EDD_CR_STORE_API_URL ) );

	// make sure the response came back okay
	if ( is_wp_error( $response ) )
		return false;

	// decode the license data
	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	update_option( 'edd_cr_license_active', $license_data->license );

}
add_action( 'admin_init', 'edd_cr_activate_license' );
