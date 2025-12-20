<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
$quiz_id = "";
if( isset( $attributes['quiz_id'] ) && !empty( $attributes['quiz_id'] ) ) { 
	$quiz_id = $attributes['quiz_id'];
}

$startdate = "";
if( isset( $attributes['startdate'] ) && !empty( $attributes['startdate'] ) ) { 
	$startdate = $attributes['startdate'];
}

$enddate = "";
if( isset( $attributes['enddate'] ) && !empty( $attributes['enddate'] ) ) { 
	$enddate = $attributes['enddate'];
}

if( intval( $quiz_id ) > 0 ) {
	echo do_shortcode('[exms_leaderboard quiz_id="'.$quiz_id.'" from="'.$startdate.'" to="'.$enddate.'"]');
} else {
	echo __( 'Quiz is not configured. Please, contact the site admin.', 'wp_exams' );
}
