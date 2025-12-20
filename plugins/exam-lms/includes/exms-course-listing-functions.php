<?php
/**
 * course listing functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get total course members excluding administrators and exms_instructors
 */
function exms_get_course_member( $course_id ) {
    global $wpdb;

    if ( ! $course_id ) {
        return 0;
    }

    $results = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT e.user_id)
             FROM {$wpdb->prefix}exms_user_enrollments AS e
             LEFT JOIN {$wpdb->prefix}usermeta AS um ON e.user_id = um.user_id
             WHERE e.post_id = %d
               AND um.meta_key = %s
               AND um.meta_value NOT LIKE %s
               AND um.meta_value NOT LIKE %s",
            $course_id,
            $wpdb->prefix . 'capabilities',
            '%administrator%',
            '%exms_instructor%'
        )
    );

    return (int) $results;
}

/**
 * Get Step  related information
 */
function exms_get_post_settings( $post_id ) {

    global $wpdb;

    if ( ! $post_id ) {
        return null;
    }

    $result = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT parent_post_type, parent_post_price, subscription_days, redirect_url , seat_limit, video_url, progress_type
                FROM {$wpdb->prefix}exms_post_settings 
                WHERE parent_post_id = %d",
            $post_id
        ),
		ARRAY_A
    );

    return $result;
}
/**
 * Get lessons associated with a course.
 */
function exms_get_course_lessons( $course_id ) {

	global $wpdb;

	if ( ! $course_id ) {
		return [];
	}

	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}exms_post_relationship WHERE parent_post_id = %d AND relationship_type = 'courses-lessons'",
			$course_id
		)
	);

	if ( ! $results ) {
		return [];
	}

	$lesson_ids = wp_list_pluck( $results, 'id' );
	return $lesson_ids;
}