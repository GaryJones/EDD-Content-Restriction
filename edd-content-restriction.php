<?php
/*
Plugin Name: Easy Digital Downloads - Content Restriction
Plugin URL: http://easydigitaldownloads.com/extension/content-restriction
Description: Allows you to restrict content from posts, pages, and custom post types to only those users who have purchased certain products. Also includes bbPress support.
Version: 1.3.2
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
define( 'EDD_CR_VERSION', '1.3.2' );

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

include( EDD_CR_PLUGIN_DIR . '/includes/settings.php');
include( EDD_CR_PLUGIN_DIR . '/includes/functions.php');
include( EDD_CR_PLUGIN_DIR . '/includes/metabox.php');
include( EDD_CR_PLUGIN_DIR . '/includes/scripts.php');
include( EDD_CR_PLUGIN_DIR . '/includes/shortcodes.php');

if ( class_exists( 'bbPress' ) ) {
	// bbPress forum / topic restriction
	include( EDD_CR_PLUGIN_DIR . '/includes/bbpress.php');
}


if( ! class_exists( 'EDD_License' ) ) {
	include( EDD_CR_PLUGIN_DIR . '/includes/EDD_License_Handler.php' );
}
$eddc_license = new EDD_License( __FILE__, EDD_CR_PRODUCT_NAME, EDD_CR_VERSION, 'Pippin Williamson' );