<?php
/**
 * Plugin Name:     Easy Digital Downloads - Content Restriction
 * Plugin URI:      https://easydigitaldownloads.com/extension/content-restriction/
 * Description:     Allows you to restrict content from posts, pages, and custom post types to only those users who have purchased certain products. Also includes bbPress support.
 * Version:         1.6.0
 * Author:          Pippin Williamson and Daniel J Griffiths
 * Author URI:      https://easydigitaldownloads.com
 * Text Domain:     edd_cr
 *
 * @package         EDD\ContentRestriction
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'EDD_Content_Restriction' ) ) {

    /**
     * Main EDD_Content_Restriction class
     *
     * @since       1.4.0
     */
    class EDD_Content_Restriction {


        /**
         * @var         EDD_Content_Restriction $instance The one true EDD_Content_Restriction
         * @since       1.4.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @since       1.3.0
         * @access      public
         * @static
         * @return      object self::$instance
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_Content_Restriction();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.6.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_CONTENT_RESTRICTION_VER', '1.6.0' );

            // Plugin path
            define( 'EDD_CONTENT_RESTRICTION_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_CONTENT_RESTRICTION_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Includes
         *
         * @access      public
         * @since       1.3.0
         * @return      void
         */
        public function includes() {
            require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/functions.php';
            require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/metabox.php';
            require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/scripts.php';
            require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/shortcodes.php';

            // Check for bbPress
            if( class_exists( 'bbPress' ) ) {
                require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/modules/bbpress.php';
            }

            require_once EDD_CONTENT_RESTRICTION_DIR . 'includes/modules/menus.php';
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_CONTENT_RESTRICTION_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_cr_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd_cr' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd_cr', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-content-restriction/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-content-restriction/ folder
                load_textdomain( 'edd_cr', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-content-restriction/languages/ folder
                load_textdomain( 'edd_cr', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd_cr', false, $lang_dir );
            }
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.1
         * @return      void
         */
        private function hooks() {
            // Handle licensing
            if( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Content Restriction', EDD_CONTENT_RESTRICTION_VER, 'Pippin Williamson' );
            }

            // Register settings
            add_filter( 'edd_settings_extensions', array( $this, 'settings' ), 1 );
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {
            $new_settings = array(
                array(
                    'id'    => 'edd_content_restriction_settings',
                    'name'  => '<strong>' . __( 'Content Restriction Settings', 'edd_cr' ) . '</strong>',
                    'desc'  => __( 'Configure Content Restriction Settings', 'edd_cr' ),
                    'type'  => 'header',
                ),
                array(
                    'id'    => 'edd_content_restriction_hide_menu_items',
                    'name'  => __( 'Hide Menu Items', 'edd_cr' ),
                    'desc'  => __( 'Should we hide menu items a user doesn\'t have access to?', 'edd_cr' ),
                    'type'  => 'checkbox',
                )
            );

            $settings = array_merge( $settings, $new_settings );

            return $settings;
        }
    }
}


/**
 * The main function responsible for returning the one true EDD_Content_Restriction
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Content_Restriction The one true EDD_Content_Restriction
 */
function EDD_Content_Restriction_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Content_Restriction::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Content_Restriction_load' );