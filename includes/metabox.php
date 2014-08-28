<?php
/**
 * Add Meta Boxes
 *
 * @package     EDD\ContentRestriction\Metabox
 * @copyright   Copyright (c) 2013-2014, Pippin Williamson
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Register meta box
 *
 * @since       1.6.0
 * @global      object $post The post/page we are editing
 * @return      void
 */
function edd_cr_add_meta_box() {
    global $post;

    $post_types     = get_post_types( array( 'show_ui' => true ) );
    $excluded_types = array( 'download', 'edd_payment', 'reply', 'acf', 'deprecated_log' );

    if( ! in_array( get_post_type( $post->ID ), apply_filters( 'edd_cr_excluded_post_types', $excluded_types ) ) ) {
        add_meta_box(
            'content-restriction',
            __( 'Content Restriction', 'edd_cr' ),
            'edd_cr_render_meta_box',
            '',
            'normal',
            'default'
        );
    }
}
add_action( 'add_meta_boxes', 'edd_cr_add_meta_box' );


/**
 * Render metabox
 *
 * @since       1.6.0
 * @global      object $post The post/page we are editing
 * @return      void
 */
function edd_cr_render_meta_box( $post_id ) {
    global $post;

    $downloads              = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );
    $restricted_to          = get_post_meta( $post->ID, '_edd_cr_restricted_to', true );
    $restricted_variable    = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices

    if ( $downloads ) {
        ?>
        <div id="edd-cr-options" class="edd_meta_table_wrap">
            <p><strong><?php echo sprintf( __( 'Restrict this content to buyers of one or more %s.', 'edd_cr' ), strtolower( edd_get_label_plural() ) ); ?></strong></p>
            <table class="widefat edd_repeatable_table" width="100%" cellpadding="0" cellspacing="0">
                <thead>
                    <th><?php echo edd_get_label_singular(); ?></th>
                    <th><?php echo sprintf( __( '%s Variation', 'edd_cr' ), edd_get_label_singular() ); ?></th>
                    <?php do_action( 'edd_cr_table_head', $post_id ); ?>
                    <th style="width: 2%"></th>
                </thead>
                <tbody>
                    <tr class="edd-cr-option-wrapper edd_repeatable_row">
                        <?php do_action( 'edd_cr_render_option_row', 0, array(), $post_id ); ?>
                    </tr>
                    <tr>
                        <td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
                            <a class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add New Download', 'edd_cr' ); ?></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}


/**
 * Individual Option Row
 *
 * Used to output a table row for each download.
 * Can be called directly, or attached to an action.
 *
 * @since       1.6.0
 * @param       object $post The post we are editing
 */
function edd_cr_render_option_row( $key, $args = array(), $post ) {
    $downloads              = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );
    $restricted_to          = get_post_meta( $post->ID, '_edd_cr_restricted_to', true );
    $restricted_variable    = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices
    ?>
    <td>
        <select name="edd_cr_download_id[<?php echo $key; ?>]" id="edd_cr_download_id[<?php echo $key; ?>]" class="edd_cr_download_id">
            <option value='' disabled selected style='display:none;'><?php echo sprintf( __( 'Select A %s'), edd_get_label_singular() ); ?></option>
            <?php
                foreach ( $downloads as $download ) {
                    echo '<option value="' . absint( $download->ID ) . '" ' . selected( $restricted_to[$key], $download->ID, false ) . '>' . esc_html( get_the_title( $download->ID ) ) . '</option>';
                }
            ?>
        </select>
    </td>
    <td>
        <?php
            if( isset( $restricted_to[$key] ) && edd_has_variable_prices( $restricted_to[$key] ) ) {
                $prices = get_post_meta( $restricted_to[$key], 'edd_variable_prices', true );
                echo '<select class="edd_price_options_select" name="edd_cr_download_price[' . $key . ']">';
                echo '<option value="all">' . __( 'All Variants', 'edd_cr' ) . '</option>';
                foreach ( $prices as $key => $price ) {
                    echo '<option value="' . absint( $key ) . '" ' . selected( $key, $restricted_variable[$key], false ) . '>' . esc_html( $price['name'] )  . '</option>';
                }
                echo '</select>';
            } else {
                echo '<p class="edd_cr_variable_none">' . __( 'None', 'edd_cr' ) . '</p>';
            }
        ?>
        <img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting edd_cr_loading" style="display:none;"/>
    </td>
    <td>
        <a href="#" class="edd_remove_repeatable" data-type="price" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">&times;</a>
    </td>
    <?php

    do_action( 'edd_cr_metabox', $post->ID, $restricted_to, $restricted_variable );
    echo wp_nonce_field( 'edd-cr-nonce', 'edd-cr-nonce' );
}
add_action( 'edd_cr_render_option_row', 'edd_cr_render_option_row', 10, 3 );


/**
 * Save metabox data
 *
 * @since       1.0.0
 * @param       int $post_id The ID of this post
 * @return      void
 */
function edd_cr_save_meta_data( $post_id ) {
    if( isset( $_POST['edd-cr-nonce'] ) && wp_verify_nonce( $_POST['edd-cr-nonce'], 'edd-cr-nonce' ) ) {
        $restricted_to  = sanitize_text_field( $_POST['edd_cr_download_id'] );
        $price_option   = isset( $_POST['edd_cr_download_price'] ) ? sanitize_text_field( $_POST['edd_cr_download_price'] ) : false;

        update_post_meta( $post_id, '_edd_cr_restricted_to', $restricted_to );

        if( $price_option !== false ) {
            update_post_meta( $post_id, '_edd_cr_restricted_to_variable', $price_option );
        }

        do_action( 'edd_cr_save_meta_data', $post_id, $_POST );
    }
}
add_action( 'save_post', 'edd_cr_save_meta_data' );