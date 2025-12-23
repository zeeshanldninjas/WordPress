<?php
/**
 * course overview functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Recursively fetches all child post IDs for a given parent based on structure.
 *
 * @param int $parent_id ID of the parent post.
 * @param string $parent_type Post type of the parent (e.g., 'exms-courses').
 * @param array $structure The LMS structure array from options.
 * @param array &$result Result accumulator array by post type.
 * @param int $depth Recursion depth (for debugging or limits).
 * @return array Collected child post IDs grouped by post type.
 */
function exms_get_child_post_ids( $parent_id, $parent_type, $structure, &$result = [], $depth = 0 ) {
	global $wpdb;

	foreach ( $structure as $child_slug => $item ) {

		if ( ! isset( $item['parent'] ) ) {
			continue;
		}

		$allowed_parents = is_array( $item['parent'] ) ? $item['parent'] : [ $item['parent'] ];
		if ( $child_slug === 'exms-quizzes' && ! in_array( 'exms-courses', $allowed_parents, true ) ) {
			$allowed_parents[] = 'exms-courses';
		}

		if ( in_array( $parent_type, $allowed_parents, true ) ) {

			// Fetch all related child posts
			$query = $wpdb->prepare(
				"SELECT child_post_id 
				 FROM {$wpdb->prefix}exms_post_relationship
				 WHERE parent_post_id = %d
				   AND parent_post_type = %s
				   AND assigned_post_type = %s",
				$parent_id, $parent_type, $child_slug
			);

			$child_posts = $wpdb->get_col( $query );
			$count = count( $child_posts );

			if ( ! isset( $result[ $child_slug ] ) ) {
				$result[ $child_slug ] = [];
			}

			$result[ $child_slug ] = array_merge( $result[ $child_slug ], $child_posts );
			foreach ( $child_posts as $child_id ) {
				exms_get_child_post_ids( $child_id, $child_slug, $structure, $result, $depth + 1 );
			}
		}
	}

	return $result;
}

/**
 * Recursively fetches all child post IDs for a given parent based on structure.
 */
function exms_get_group_child_post_ids( $parent_id, $parent_type, $structure, &$result = [], $depth = 0, &$visited = [] ) {
	global $wpdb;
	$key = $parent_type . ':' . (int) $parent_id;
	if( isset( $visited[ $key ] ) ) {
        return $result;
	}
	$visited[ $key ] = true;
    
	foreach( (array) $structure as $child_slug => $item ) {

		$allowed_parents = is_array( $item['parent'] ) ? $item['parent'] : [ $item['parent'] ];
		if( $child_slug === 'exms-quizzes' && ! in_array( 'exms-courses', $allowed_parents, true ) ) {
			$allowed_parents[] = 'exms-courses';
		}
		if( $child_slug === 'exms-courses' && ! in_array( 'exms-groups', $allowed_parents, true ) ) {
			$allowed_parents[] = 'exms-groups';
		}

		if( in_array( $parent_type, $allowed_parents, true ) ) {
            
			$sql = $wpdb->prepare(
				"SELECT child_post_id
				 FROM {$wpdb->prefix}exms_post_relationship
				 WHERE parent_post_id = %d
				   AND parent_post_type = %s
				   AND assigned_post_type = %s",
				(int) $parent_id,
				(string) $parent_type,
				(string) $child_slug
			);

			$child_posts = (array) $wpdb->get_col( $sql );

			if( ! isset( $result[ $child_slug ] ) ) {
				$result[ $child_slug ] = [];
			}

			$result[ $child_slug ] = array_merge( $result[ $child_slug ], $child_posts );

			foreach( $child_posts as $child_id ) {
				exms_get_child_post_ids( (int) $child_id, $child_slug, $structure, $result, $depth + 1, $visited );
			}
		}
	}

	foreach( $result as $pt => $ids ) {
		$result[ $pt ] = array_values( array_unique( array_map( 'intval', (array) $ids ) ) );
	}

	return $result;
}


/**
 * Get user progress and status for a specific post (course, lesson, assignment, etc.).
 *
 * This function retrieves the enrollment status and progress percentage
 * for a given user and post from the exms_user_enrollments table.
 *
 * @param int    $user_id   The ID of the user.
 * @param int    $post_id   The ID of the post (course, lesson, assignment, etc.).
 */
function exms_get_user_progress( $user_id, $post_id, $post_type = '' ) {

    global $wpdb;

    $table = $wpdb->prefix . 'exms_user_enrollments';

    $query = "SELECT status, progress_percent 
              FROM {$table} 
              WHERE user_id = %d AND post_id = %d";

    $params = array( $user_id, $post_id );

    if ( ! empty( $post_type ) ) {
        $query .= " AND post_type = %s";
        $params[] = $post_type;
    }

    $query .= " LIMIT 1";

    $result = $wpdb->get_row( $wpdb->prepare( $query, $params ) );

    if ( ! $result ) {
        return array(
            'status'   => null,
            'progress' => 0
        );
    }

    return array(
        'status'   => $result->status,
        'progress' => (int) $result->progress_percent
    );
}
/**
 * Update user enrollment progress directly
 *
 * @param int    $user_id
 * @param int    $course_id
 * @param string $status
 * @param int    $progress_percent
 * @return bool
 */
function exms_update_user_enrollment( $user_id, $course_id, $status = 'in-progress', $progress_percent = 0 ) {
    global $wpdb;

    $enrollments_table = $wpdb->prefix . 'exms_user_enrollments';

    return (bool) $wpdb->update(
        $enrollments_table,
        [
            'status'            => $status,
            'progress_percent'  => $progress_percent,
            'end_date'          => ( $status === 'completed' ? current_time( 'mysql' ) : null ),
            'updated_timestamp' => current_time( 'mysql' ),
        ],
        [
            'user_id' => $user_id,
            'post_id' => $course_id,
        ],
        ['%s', '%d', '%s', '%s' ],
        ['%d', '%d' ]
    );
}

/**
 * Recursive function to mark an item and all its children as complete
 */
function exms_mark_children_complete( $parent_id, $course_id, $user_id ) {
    global $wpdb;

    $progress_table = $wpdb->prefix . 'exms_user_progress_tracking';
    $relation_table = $wpdb->prefix . 'exms_post_relationship';

    $children = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT child_post_id, assigned_post_type 
             FROM $relation_table 
             WHERE parent_post_id = %d",
            $parent_id
        )
    );

    if ( $children ) {
        foreach ( $children as $child ) {
            if ( ! post_type_exists( $child->assigned_post_type ) ) {
                continue; 
            }
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO $progress_table 
                        (user_id, course_id, item_id, item_type, parent_id, status, completion_date)
                     VALUES (%d, %d, %d, %s, %d, 1, %s)
                     ON DUPLICATE KEY UPDATE status = 1, completion_date = VALUES(completion_date)",
                    $user_id,
                    $course_id,
                    $child->child_post_id,
                    $child->assigned_post_type,
                    $parent_id,
                    current_time( 'mysql' )
                )
            );

            exms_mark_children_complete( $child->child_post_id, $course_id, $user_id );
        }
    }
}

/**
 * Main function to mark course as complete with all hierarchy
 */
function exms_process_course_mark_as_complete( $course_id, $user_id = 0 ) {

    global $wpdb;

    $progress_table = $wpdb->prefix . 'exms_user_progress_tracking';

    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id || ! $course_id ) {
        return false;
    }

    $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO $progress_table 
                (user_id, course_id, item_id, item_type, parent_id, status, completion_date)
             VALUES (%d, %d, %d, %s, %d, 1, %s)
             ON DUPLICATE KEY UPDATE status = 1, completion_date = VALUES(completion_date)",
            $user_id,
            $course_id,
            $course_id,
            'exms-courses',
            0,
            current_time( 'mysql' )
        )
    );

    exms_mark_children_complete( $course_id, $course_id, $user_id );

    return true;
}

/**
 * Get first coures legacy url
 * 
 * @param $course_id
 */ 
function exms_get_first_step_url( $course_id ) {

    global $wpdb;

    $post_type = get_post_type( $course_id );
    if ( ! $post_type || ! post_type_exists( $post_type ) ) {
        return get_permalink( $course_id );
    }

    $structure        = get_option( 'exms_post_types', true );
    $hierarchy        = EXMS_COURSE::instance()->get_hierarchy_from_structure( $structure );
    $ordered_types    = EXMS_COURSE::instance()->flatten_hierarchy( $hierarchy, $post_type );
    $assigned_types   = EXMS_COURSE::instance()->get_assigned_child_types( $wpdb, $course_id, $post_type );
    $sorted_types     = EXMS_COURSE::instance()->sort_assigned_types( $assigned_types, $ordered_types );

    if ( ! empty( $sorted_types ) ) {
        $first_type = $sorted_types[0];
        $first_step_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT child_post_id
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d
               AND parent_post_type = %s
               AND assigned_post_type = %s
             ORDER BY id ASC
             LIMIT 1",
            $course_id, $post_type, $first_type
        ));

        if ( $first_step_id ) {
            return get_permalink( $first_step_id );
        }
    }

    return get_permalink( $course_id );
}

function exms_course_breadcrumb( $course_id, $post_type ) {

    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path    = trim( parse_url( $req_uri, PHP_URL_PATH ), '/' );
    $is_group_context = ( strpos( $path, 'exms-groups/' ) === 0 );

    $breadcrumb = [];
    if( $is_group_context ) {
        $breadcrumb[] = [
            'url'   => site_url( '/exms-groups' ),
            'title' => __( 'Groups', 'exms' ),
        ];
    } else {
        $breadcrumb[] = [
            'url'   => site_url( '/exms-courses' ),
            'title' => __( 'Courses', 'exms' ),
        ];
    }

    if( $course_id ) {

        if( $is_group_context ) {
            
            $parts = array_values( array_filter( explode( '/', $path ) ) );
            $group_slug  = isset($parts[1]) ? $parts[1] : '';
            $course_slug = isset($parts[2]) ? $parts[2] : '';

            $url = site_url( '/exms-groups/' . $group_slug . '/' . $course_slug . '/' );
        } else {
            $url = get_permalink( $course_id );
        }

        $breadcrumb[] = [
            'url'   => $url,
            'title' => get_the_title( $course_id ),
        ];
    }
    return $breadcrumb;
}


/**
 * Get the assigned instructor IDs for a course
 */
function exms_get_assign_instructor_ids( $course_id ) {

    global $wpdb;

    $results = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT user_id 
             FROM {$wpdb->prefix}exms_user_enrollments 
             WHERE post_id = %d",
            $course_id
        )
    );

    $instructor_ids = array();

    if ( ! empty( $results ) ) {
        foreach ( $results as $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user && in_array( 'exms_instructor', (array) $user->roles, true ) ) {
                $instructor_ids[] = $user_id;
            }
        }
    }

    return $instructor_ids;
}

/**
 * Get course latest Enrollment date
 */
function exms_get_course_last_enroll( $course_id ) {
    global $wpdb;

    if ( ! $course_id ) {
        return null;
    }

    $query = $wpdb->prepare(
        "SELECT created_timestamp
         FROM {$wpdb->prefix}exms_user_enrollments
         WHERE post_id = %d
         ORDER BY created_timestamp DESC
         LIMIT 1",
        $course_id
    );

    $result = $wpdb->get_row( $query );

    if ( ! $result ) {
        return null;
    }
    return date( 'd F Y', strtotime( $result->created_timestamp ) );
}


function exms_get_latest_enrollment_date( $course_id ) {
    global $wpdb;

    $table = $wpdb->prefix . 'exms_user_enrollments';

    $timestamp = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(created_timestamp)
             FROM {$table}
             WHERE post_id = %d",
            $course_id
        )
    );

    return $timestamp ? date( "l j F Y", $timestamp ) : false;
}
