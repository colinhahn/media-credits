<?php
/**
 * Plugin Name: Media Credits
 * Plugin URI: https://github.com/colinhahn/media-credits
 * Description: Stores and displays media credit metadata
 * Author: Colin J. Hahn
 * Version: 1.0
 */

/**
 * Add metadata fields to media uploader
 * New fields: 	cjh_mc_author_name, cjh_mc_author_url
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function cjh_mc_attachment_field_credit( $form_fields, $post ) {
	$form_fields['cjh_mc_author_name'] = array(
		'label' => 'Author Name',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'cjh_mc_author_name', true ),
		'helps' => 'Name that will be credited with the media',
	);

	$form_fields['cjh_mc_author_url'] = array(
		'label' => 'Media URL',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'cjh_mc_author_url', true ),
		'helps' => 'URL for media source',
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'cjh_mc_attachment_field_credit', 10, 2 );

/**
 * Save metadata fields in media uploader
 * New fields:	cjh_mc_author_name, cjh_mc_author_url
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function cjh_mc_attachment_field_credit_save( $post, $attachment ) {
	if( isset( $attachment['cjh_mc_author_name'] ) )
		update_post_meta( $post['ID'], 'cjh_mc_author_name', sanitize_text_field( $attachment['cjh_mc_author_name'] ) );

	if( isset( $attachment['cjh_mc_author_url'] ) )
		update_post_meta( $post['ID'], 'cjh_mc_author_url', esc_url( $attachment['cjh_mc_author_url'] ) );

	return $post;
}

add_filter( 'attachment_fields_to_save', 'cjh_mc_attachment_field_credit_save', 10, 2 );

/**
 * Shortcode to display metadata fields
 * Must be called within the loop
 * @param $atts array, user defined attributes in shortcode tag
 * @param $content string, content enclosed in shortcode tags (default null)
 * @return $cjh_mc_media_credit_box, string containing div of media credit metadata
 */

 function cjh_mc_mediacredit($atts, $content = null) {
 
 	// Get the id of the media element from the shortcode attribute; default sets the id to the thumbnail of the current post
    extract( shortcode_atts( array(
        "id" => get_post_meta( get_the_ID(), '_thumbnail_id', true )
    ), $atts ) );
 
    if ( $id > 0 ) {
        $cjh_mc_current_author_name = get_post_meta( $id, 'cjh_mc_author_name', true);
        $cjh_mc_current_author_url = get_post_meta( $id, 'cjh_mc_author_url', true);
    } else {
	    return null; // No media object id found
    } 

    if ( strlen( trim( $cjh_mc_current_author_url ) ) == 0 ) {
    	if ( strlen( trim( $cjh_mc_current_author_name ) ) == 0 ) { // No media metadata set
    		return null;
    	} else { // No URL, yes author
    		$cjh_mc_media_credit_box = '<div class="media-credit">Media by '.$cjh_mc_current_author_name.'</div>';
    	}
    } else {
    	if ( strlen( trim( $cjh_mc_current_author_name ) ) == 0 ) { // Yes URL, no author
    		$cjh_mc_safe_url = esc_url( $cjh_mc_current_author_url );
    		$cjh_mc_media_credit_box = '<div class="media-credit">Media by <a href="'.$cjh_mc_safe_url.'">'.$cjh_mc_safe_url.'</a></div>';
    	} else { // Both URL and author
		$cjh_mc_safe_url = esc_url( $cjh_mc_current_author_url );
		$cjh_mc_media_credit_box = '<div class="media-credit">Media by <a href="'.$cjh_mc_safe_url.'">'.$cjh_mc_current_author_name.'</a></div>';
    	}
    }

    return $cjh_mc_media_credit_box;
}

add_shortcode("mediacredit", "cjh_mc_mediacredit"); 

?>
