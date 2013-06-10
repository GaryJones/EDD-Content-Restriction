<?php
/*
Plugin Name: Easy Digital Downloads - Content Restriction
Plugin URL: http://easydigitaldownloads.com/extension/content-restriction
Description: Allows you to restrict content from posts, pages, and custom post types to only those users who have purchased certain products. Also includes bbPress support.
Version: 1.2
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Contributors: mordauk, ghost1227
*/

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Content_Restriction' ) ) {

	class EDD_Content_Restriction {

		private static $instance;


		/**
		 * Get active instance
		 *
		 * @since		1.3
		 * @access		public
		 * @static
		 * @return		object self::$instance
		 */
		public static function get_instance() {
			if( !self::$instance )
				self::$instance = new EDD_Content_Restriction();

			return self::$instance;
		}


		/**
		 * Class constructor
		 *
		 * @since		1.3
		 * @access		public
		 * @return		void
		 */
		public function __construct() {
			if( !defined( 'EDD_CR_PLUGIN_DIR' ) )
				define( 'EDD_CR_PLUGIN_DIR', dirname( __FILE__ ) );

			if( !defined( 'EDD_CR_PLUGIN_URL' ) )
				define( 'EDD_CR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		 
			define( 'EDD_CR_STORE_API_URL', 'https://easydigitaldownloads.com' );
			define( 'EDD_CR_PRODUCT_NAME', 'Content Restriction' );
			define( 'EDD_CR_VERSION', '1.3' );

			// Load our custom updater
			if( !class_exists( 'EDD_SL_Plugin_Updater' ) )
				include( dirname( __FILE__ ) . '/includes/EDD_SL_Plugin_Updater.php' );

			$this->init();
			$this->includes();
		}


		/**
		 * Run action and filter hooks
		 *
		 * @since		1.3
		 * @access		private
		 * @return		void
		 */
		private function init() {
			// Make sure EDD is active
			if( !class_exists( 'Easy_Digital_Downloads' ) ) return;

			global $edd_options;

			// Internationalization
			add_action( 'init', array( $this, 'textdomain' ) );

			// Get license key from DB
			$edd_cr_license_key = isset( $edd_options['edd_cr_license_key'] ) ? trim( $edd_options['edd_cr_license_key'] ) : '';

			// Setup the updater
			$edd_cr_updater = new EDD_SL_Plugin_Updater( EDD_CR_STORE_API_URL, __FILE__, array(
					'version' 	=> EDD_CR_VERSION, 		// current version number
					'license' 	=> $edd_cr_license_key, // license key (used get_option above to retrieve from DB)
					'item_name' => EDD_CR_PRODUCT_NAME, // name of this plugin
					'author' 	=> 'Pippin Williamson'  // author of this plugin
				)
			);
		}


		/**
		 * Internationalization
		 *
		 * @since		1.0
		 * @access		public
		 * @static
		 * @return		void
		 */
		public static function textdomain() {
			// Set filter for plugin's languages directory
			$edd_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$edd_lang_dir = apply_filters( 'edd_cr_languages_directory', $edd_lang_dir );

			// Load the translations
			load_plugin_textdomain( 'edd_cr', false, $edd_lang_dir );
		}


		/**
		 * Includes
		 *
		 * @since		1.3
		 * @access		public
		 * @return		void
		 */
		public function includes() {
			include( EDD_CR_PLUGIN_DIR . '/includes/settings.php');
			include( EDD_CR_PLUGIN_DIR . '/includes/functions.php');
			include( EDD_CR_PLUGIN_DIR . '/includes/metabox.php');
			include( EDD_CR_PLUGIN_DIR . '/includes/scripts.php');
			include( EDD_CR_PLUGIN_DIR . '/includes/shortcodes.php');

			if ( class_exists( 'bbPress' ) ) {
				// bbPress forum / topic restriction
				include( EDD_CR_PLUGIN_DIR . '/includes/bbpress.php');
			}
		}
	}
}


function edd_content_restriction_load() {
	$edd_content_restriction = new EDD_Content_Restriction();
}
add_action( 'plugins_loaded', 'edd_content_restriction_load' );
