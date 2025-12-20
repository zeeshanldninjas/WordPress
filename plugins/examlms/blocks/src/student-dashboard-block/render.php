<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$userid = "";
if( isset( $attributes['userid'] ) && !empty( $attributes['userid'] ) ) { 
	$userid = $attributes['userid'];
}

echo do_shortcode('[exms_student_dashboard userid="'.$userid.'" ]');
