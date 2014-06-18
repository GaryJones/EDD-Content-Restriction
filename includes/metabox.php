<?php
/**
 * Metabox
 *
 * @package		EDD Content Restriction
 * @subpackage	Metabox
 * @copyright	Copyright (c) 2013, Pippin Williamson
 * @since		1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class EDD_CR_Metabox {

	/**
	 * Get things started
	 *
	 * @since		2.0
	 * @return		void
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  ) );
		add_action( 'save_post',      array( $this, 'save_metabox' ) );

	}

	/**
	 * Register our metabox
	 *
	 * @since		2.0
	 * @return		void
	 */
	public function add_metabox() {

		$post_types     = get_post_types( array( 'show_ui' => true ) );
		$excluded_types = apply_filters( 'edd_cr_excluded_post_types', array( 'download', 'edd_payment', 'reply', 'acf', 'deprecated_log' ) );
		
		// Remove excluded post types
		foreach( $post_types as $key => $post_type ) {
			if( in_array( $post_type, $excluded_types ) ) {
				unset( $post_types[ $key ] );
			}
		}

		$post_types = explode( ',', $post_types );

		add_meta_box( 'edd_content_restriction', __( 'Content Restriction', 'edd_cr' ),  array( $this, 'render_metabox' ), $post_types, 'normal', 'high' );
	}

	/**
	 * Render the metamox
	 *
	 * @since		2.0
	 * @return		void
	 */
	public function render_metabox( $post_id ) {	

		$downloads = edd_get_bundled_products( $post_id );
?>
		<div id="edd_cr_downloads">
			<div id="edd_cr_download_fields" class="edd_meta_table_wrap">
				<table class="widefat" width="100%" cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th><?php printf( __( 'Restrict this content to buyers of one or more %s.', 'edd_cr' ), edd_get_label_plural() ); ?></th>
							<?php do_action( 'edd_cr_downloads_table_head', $post_id ); ?>
						</tr>
					</thead>
					<tbody>
						<tr class="edd_repeatable_product_wrapper">
							<td colspan="2">
								<?php
								echo EDD()->html->product_dropdown( array(
									'name'     => '_edd_bundled_products[]',
									'id'       => 'edd_bundled_products',
									'selected' => $products,
									'multiple' => true,
									'chosen'   => true
								) );
								?>
							</td>
							<?php do_action( 'edd_cr_downloads_table_row', $post_id ); ?>
						</tr>
						<tr>
							<td>
								<?php
								echo EDD()->html->select( array(
									'name'     => '_edd_cr_condition',
									'id'       => '_edd_cr_condition',
									'selected' => $condition,
									'options'  => array( 
										'any'  => sprintf( __( 'Customer has purchased one more %s', 'edd_cr' ), edd_get_label_plural() ),
										'all'  => sprintf( __( 'Customer has purchased all %s', 'edd_cr' ), edd_get_label_plural() ),
									)
								) );
								?>
								<?php _e( 'Condition', 'edd_cr' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
<?php

		do_action( 'edd_cr_metabox', $post_id, $downloads, $deprecated = null );
		echo wp_nonce_field( 'edd-cr-nonce', 'edd-cr-nonce' );

	}

	/**
	 * Save metabox data
	 *
	 * @since		2.0
	 * @param		$post_id the ID of this post
	 * @return		void
	 */
	public function save_metabox( $post_id ) {
		if ( isset( $_POST['edd-cr-nonce'] ) && wp_verify_nonce( $_POST['edd-cr-nonce'], 'edd-cr-nonce' ) ) {
			$restricted_to = sanitize_text_field( $_POST['edd_cr_download_id'] );
			$price_option = isset( $_POST['edd_cr_download_price'] ) ? sanitize_text_field( $_POST['edd_cr_download_price'] ) : false;

			update_post_meta( $post_id, '_edd_cr_restricted_to', $restricted_to );

			if ( $price_option !== false ) {
				update_post_meta( $post_id, '_edd_cr_restricted_to_variable', $price_option );
			}

			do_action( 'edd_cr_save_meta_data', $post_id, $_POST );

		}
	}
}


/**
 * Add metabox
 *
 * @global		$post
 * @return		void
 */
function edd_cr_submitbox() {

	global $post;

	if ( !is_object( $post ) )
		return;

	$post_types = get_post_types( array( 'show_ui' => true ) );

	$excluded_types = array( 'download', 'edd_payment', 'reply', 'acf', 'deprecated_log' );

	if ( ! in_array( get_post_type( $post->ID ), apply_filters( 'edd_cr_excluded_post_types', $excluded_types ) ) ) {

		$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1 ) );

		$restricted_to = get_post_meta( $post->ID, '_edd_cr_restricted_to', true );
		$restricted_variable = get_post_meta( $post->ID, '_edd_cr_restricted_to_variable', true ); // for variable prices

		if ( $downloads ) {
			echo '<div id="edd-cr-options" style="margin: 0 0 8px;">';
				echo '<label for="edd_cr_download_id">' . sprintf( __( 'Restrict this content to buyers of a %s.', 'edd_cr' ), edd_get_label_singular() ) . '</label>';
				echo '<select name="edd_cr_download_id" id="edd_cr_download_id">';
					echo '<option value="0">' . __( 'Not Restricted', 'edd_cr' ) . '</option>';
					foreach ( $downloads as $download ) {
						echo '<option value="' . absint( $download->ID ) . '" ' . selected( $restricted_to, $download->ID, false ) . '>' . esc_html( get_the_title( $download->ID ) ) . '</option>';
					}
				echo '</select>';
				echo '&nbsp;<img src="' . admin_url( '/images/wpspin_light.gif' ) . '" class="waiting" id="edd_cr_loading" style="display:none;"/>';
				echo '<div id="edd_download_variables">';
				if ( edd_has_variable_prices( $restricted_to ) ) {
					$prices = get_post_meta( $restricted_to, 'edd_variable_prices', true );
					echo '<select name="edd_cr_download_price">';
						echo '<option value="all">' . __( 'All Prices', 'edd_cr' ) . '</option>';
						foreach ( $prices as $key => $price ) {
							echo '<option value="' . absint( $key ) . '" ' . selected( $key, $restricted_variable, false ) . '>' .esc_html( $price['name'] )  . '</option>';
						}
					echo '</select>';
				}
				echo '</div>';
				do_action( 'edd_cr_metabox', $post->ID, $restricted_to, $restricted_variable );
				echo wp_nonce_field( 'edd-cr-nonce', 'edd-cr-nonce' );
			echo '</div>';

		}

	}
}
add_action( 'post_submitbox_start', 'edd_cr_submitbox', 0 );