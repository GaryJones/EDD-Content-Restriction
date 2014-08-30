<?php
/**
 * Add Ajax Functions
 *
 * @package     EDD\ContentRestriction\AjaxFunctions
 * @copyright   Copyright (c) 2013-2014, Pippin Williamson
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Check for download price variations
 *
 * @since       1.6.0
 * @return      void
 */
function edd_cr_check_for_download_price_variations() {
    if( ! current_user_can( 'edit_products' ) ) {
        die( '-1' );
    }

    $download_id = absint( $_POST['download_id'] );
    $key         = ( isset( $_POST['key'] ) ? absint( $_POST['key'] ) : 0 );
    $download    = get_post( $download_id );
    
    if( 'download' != $download->post_type ) {
        die( '-2' );
    }

    if ( edd_has_variable_prices( $download_id ) ) {
        $variable_prices = edd_get_variable_prices( $download_id );
        if ( $variable_prices ) {
            $ajax_response = '<select class="edd_price_options_select edd-select edd-select" name="edd_cr_download[' . $key . '][price_id]">';
            foreach ( $variable_prices as $price_id => $price ) {
                $ajax_response .= '<option value="' . esc_attr( $price_id ) . '">' . esc_html( $price['name'] ) . '</option>';
            }
            $ajax_response .= '</select>';
            echo $ajax_response;
        }
    }

    edd_die();
}
add_action( 'wp_ajax_edd_cr_check_for_download_price_variations', 'edd_cr_check_for_download_price_variations' );

