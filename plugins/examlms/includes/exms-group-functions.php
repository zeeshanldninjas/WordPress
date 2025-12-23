<?php

/**
 * create a function to get all group enrollments
 */
function exms_get_group_courses( $group_id ) {

	global $wpdb;

	if( ! $group_id ) {
		return;
	}

    $table_name = $wpdb->prefix . 'exms_post_relationship';

    $query = $wpdb->prepare(
    	"SELECT DISTINCT child_post_id
    	FROM {$table_name}
    	WHERE parent_post_id = %d",
    	$group_id
    );

    $course_ids = $wpdb->get_col( $query );
    return $course_ids;
}

/**
 * Get all course IDs attached with a group from wp_exms_post_relationship
 */
function exms_get_group_course_ids( $group_id ) {
    global $wpdb;

    $rel_table = $wpdb->prefix . 'exms_post_relationship';

    $sql = $wpdb->prepare(
        "SELECT child_post_id
         FROM {$rel_table}
         WHERE parent_post_id = %d
           AND parent_post_type = %s
           AND assigned_post_type = %s
           AND relationship_type = %s",
        $group_id,
        'exms-groups',
        'exms-courses',
        'groups-courses'
    );

    $ids = $wpdb->get_col( $sql );

    if ( empty( $ids ) ) {
        return [];
    }
    return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/**
 * Get all quiz IDs attached with a group from wp_exms_post_relationship
 */
function exms_get_group_quiz_ids( $group_id ) {
    global $wpdb;

    $rel_table = $wpdb->prefix . 'exms_post_relationship';

    $sql = $wpdb->prepare(
        "SELECT child_post_id
         FROM {$rel_table}
         WHERE parent_post_id = %d
           AND parent_post_type = %s
           AND assigned_post_type = %s
           AND relationship_type = %s",
        $group_id,
        'exms-groups',
        'exms-quizzes',
        'groups-quizzes'
    );

    $ids = $wpdb->get_col( $sql );

    if ( empty( $ids ) ) {
        return [];
    }
    return array_values( array_unique( array_map( 'intval', $ids ) ) );
}


/**
 * create a function to get user enrolled group
 */
function exms_get_user_enrolled_group( $user_id ) {

    global $wpdb;

    if( ! $user_id ) {
        return;
    }

    $group_ids = [];
    $post_type = 'exms-groups';
    
    if( current_user_can( 'administrator' ) ) {

        $group_ids = get_posts( array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ) );
    } else {

        $table_name = $wpdb->prefix . 'exms_user_enrollments';
        
        $query = $wpdb->prepare(
            "SELECT DISTINCT post_id
            FROM {$table_name}
            WHERE user_id = %d
            AND post_type = %s",
            $user_id,
            $post_type
        );

        $group_ids = $wpdb->get_col( $query );
    }

    return $group_ids;
}