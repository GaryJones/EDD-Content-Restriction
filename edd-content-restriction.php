<?php
/*
Plugin Name: Easy Digital Downloads - Content Restriction
Plugin URL: http://easydigitaldownloads.com/extension/content-restriction
Description: Allows you to restrict content from posts, pages, and custom post types to only those users who have purchased certain products. Also includes bbPress support.
Version: 1.2
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Contributors: mordauk
*/

if(!defined('EDD_CR_PLUGIN_DIR')) {
	define('EDD_CR_PLUGIN_DIR', dirname(__FILE__));
}
// plugin folder url
if(!defined('EDD_CR_PLUGIN_URL')) {
	define('EDD_CR_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}

define( 'EDD_CR_STORE_API_URL', 'http://easydigitaldownloads.com' );
define( 'EDD_CR_PRODUCT_NAME', 'Content Restriction' );
define( 'EDD_CR_VERSION', '1.2' );

/*
|--------------------------------------------------------------------------
| INTERNATIONALIZATION
|--------------------------------------------------------------------------
*/

function edd_cr_textdomain() {

	// Set filter for plugin's languages directory
	$edd_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$edd_lang_dir = apply_filters( 'edd_cr_languages_directory', $edd_lang_dir );

	// Load the translations
	load_plugin_textdomain( 'edd_cr', false, $edd_lang_dir );
}
add_action('init', 'edd_cr_textdomain');


/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

include( EDD_CR_PLUGIN_DIR . '/includes/settings.php');
include( EDD_CR_PLUGIN_DIR . '/includes/functions.php');
include( EDD_CR_PLUGIN_DIR . '/includes/metabox.php');
include( EDD_CR_PLUGIN_DIR . '/includes/scripts.php');
include( EDD_CR_PLUGIN_DIR . '/includes/shortcodes.php');

if ( class_exists( 'bbPress' ) ) {
	// bbPress forum / topic restriction
	include( EDD_CR_PLUGIN_DIR . '/includes/bbpress.php');
}

function edd_cr_updater() {

	global $edd_options;

	// retrieve our license key from the DB
	$edd_cr_license_key = isset( $edd_options['edd_cr_license_key'] ) ? trim( $edd_options['edd_cr_license_key'] ) : '';

	// setup the updater
	$edd_cr_updater = new EDD_SL_Plugin_Updater( EDD_CR_STORE_API_URL, __FILE__, array(
			'version' 	=> EDD_CR_VERSION, 		// current version number
			'license' 	=> $edd_cr_license_key, // license key (used get_option above to retrieve from DB)
			'item_name' => EDD_CR_PRODUCT_NAME, // name of this plugin
			'author' 	=> 'Pippin Williamson'  // author of this plugin
		)
	);
}
add_action( 'admin_init', 'edd_cr_updater' );