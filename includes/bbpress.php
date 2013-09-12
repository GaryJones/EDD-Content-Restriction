<?php

// hides all topics in a restricted forum for non active users
function edd_cr_filter_bbp_topics_list( $query ) {

	global $user_ID;

	if ( current_user_can( 'manage_options' ) )
		return $query;

	if ( bbp_is_single_forum() ) {

		$is_restricted = false;

		$restricted_to = edd_cr_is_restricted( bbp_get_forum_id() );

		$restricted_variable = get_post_meta( bbp_get_forum_id(), '_edd_cr_restricted_to_variable', true ); // for variable prices

		$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

		$is_restricted = ( $restricted_to && !edd_has_user_purchased( $user_ID, $restricted_to, $restricted_variable ) );

		$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, bbp_is_single_forum(), $restricted_to, $user_ID, $restricted_variable );

		if ( $is_restricted ) {
			return array(); // return an empty query
		}
	}

	return $query;
}
add_filter( 'bbp_has_topics_query', 'edd_cr_filter_bbp_topics_list' );


// hides the content of replies
function edd_cr_filter_replies( $content, $reply_id ) {
	global $user_ID, $post;

	if ( current_user_can( 'manage_options' ) )
		return $content;

	$has_access = true;

	$restricted_to = edd_cr_is_restricted( bbp_get_topic_id() );

	$restricted_id = bbp_get_topic_id();

	if ( ! $restricted_to ) {
		$restricted_to = edd_cr_is_restricted( bbp_get_forum_id() ); // check for parent forum restriction
		$restricted_id = bbp_get_forum_id();
	}
	$restricted_variable = get_post_meta( $restricted_id, '_edd_cr_restricted_to_variable', true ); // for variable prices

	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	$is_restricted = ( $restricted_to && !edd_has_user_purchased( $user_ID, $restricted_to, $restricted_variable ) );
	$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $restricted_id, $restricted_to, $user_ID, $restricted_variable );

	if( $is_restricted ) {

		if ( $restricted_variable ) {
			$return = '<div class="edd_cr_message">' . sprintf( __( 'This content is restricted to buyers of %s %s.', 'edd_cr' ),
				edd_get_price_option_name( $restricted_to, $restricted_variable ),
				'<a href="' . get_permalink( $restricted_to ) . '">' . get_the_title( $restricted_to ) . '</a>'
			) . '</div>';
		} else {
			$return = '<div class="edd_cr_message">' . sprintf(
				__( 'This content is restricted to buyers of %s.', 'edd_cr' ),
				'<a href="' . get_permalink( $restricted_to ) . '">' . get_the_title( $restricted_to ) . '</a>'
			) . '</div>';
		}

		return $return;

	}

	return $content; // not restricted
}
add_filter( 'bbp_get_reply_content', 'edd_cr_filter_replies', 2, 999 );


// hides the new reply form
function edd_cr_hide_new_topic_form( $can_access ) {
	global $user_ID;

	if ( current_user_can( 'manage_options' ) )
		return $can_access;

	$is_restricted = false;

	$restricted_to = edd_cr_is_restricted( bbp_get_forum_id() ); // check for parent forum restriction
	$restricted_id = bbp_get_forum_id();

	$restricted_variable = get_post_meta( $restricted_id, '_edd_cr_restricted_to_variable', true ); // for variable prices

	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	if ( $restricted_to && ! edd_has_user_purchased( $user_ID, $restricted_to, $restricted_variable ) ) {
		$is_restricted = true;
	}

	$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $restricted_id, $restricted_to, $user_ID, $restricted_variable );

	return $is_restricted ? false : true;
}
add_filter( 'bbp_current_user_can_access_create_topic_form', 'edd_cr_hide_new_topic_form' );


// hides the new reply form
function edd_cr_hide_new_replies_form( $can_access ) {
	global $user_ID;

	if ( current_user_can( 'manage_options' ) )
		return $can_access;

	$is_restricted = false;

	$restricted_to = edd_cr_is_restricted( bbp_get_topic_id() );

	$restricted_id = bbp_get_topic_id();

	if ( ! $restricted_to ) {
		$restricted_to = edd_cr_is_restricted( bbp_get_forum_id() ); // check for parent forum restriction
		$restricted_id = bbp_get_forum_id();
	}

	$restricted_variable = get_post_meta( $restricted_id, '_edd_cr_restricted_to_variable', true ); // for variable prices

	$restricted_variable = ( $restricted_variable !== false && $restricted_variable != 'all' ) ? $restricted_variable : null;

	if ( $restricted_to && ! edd_has_user_purchased( $user_ID, $restricted_to, $restricted_variable ) ) {
		$is_restricted = true;
	}

	$is_restricted = apply_filters( 'edd_cr_is_restricted', $is_restricted, $restricted_id, $restricted_to, $user_ID, $restricted_variable );

	return $is_restricted ? false : true;

}
add_filter( 'bbp_current_user_can_access_create_reply_form', 'edd_cr_hide_new_replies_form' );
add_filter( 'bbp_current_user_can_access_create_topic_form', 'edd_cr_hide_new_replies_form' ); // this is required for it to work with the default theme


function edd_cr_topic_feedback_messages( $translated_text, $text, $domain ) {

	switch ( $translated_text ) {
	case 'You cannot reply to this topic.':
		$translated_text = __( 'Topic creation is restricted to buyers.', 'edd_cr' );
		break;
	}
	return $translated_text;
}

function edd_cr_forum_feedback_messages( $translated_text, $text, $domain ) {

	switch ( $translated_text ) {
	case 'Oh bother! No topics were found here!':
		$translated_text = __( 'This forum is restricted to buyers.', 'edd_cr' );
		break;
	case 'You cannot create new topics at this time.':
		$translated_text = __( 'Only buyers can create topics.', 'edd_cr' );
		break;
	}
	return $translated_text;
}


function edd_cr_apply_feedback_messages() {
	global $user_ID;

	if ( bbp_is_single_topic() ) {
		add_filter( 'gettext', 'edd_cr_topic_feedback_messages', 20, 3 );
	} else if ( bbp_is_single_forum() && edd_cr_is_restricted( bbp_get_forum_id() ) ) {
			add_filter( 'gettext', 'edd_cr_forum_feedback_messages', 20, 3 );
		}
}
add_action( 'template_redirect', 'edd_cr_apply_feedback_messages' );
