<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
$quiz_id = "";
if( isset( $attributes['quiz_id'] ) && !empty( $attributes['quiz_id'] ) ) { 
	$quiz_id = $attributes['quiz_id'];
}

if( ! empty( $quiz_id ) ) {
	echo do_shortcode('[exms_quiz id="'.$quiz_id.'"]');
} else {
	echo __( 'Quiz is not configured. Please, contact the site admin.', WP_EXAMS );
}
