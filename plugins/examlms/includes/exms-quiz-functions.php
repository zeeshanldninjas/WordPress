<?php
/**
 * Quiz functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Update user reattempt time
 *
 * @param Mixed 	$user_id 	User id
 * @param Mixed 	$quiz_id 	Quiz id
 *
 * @return Array
 */
function exms_can_user_attempt_quiz( $user_id, $quiz_id ) {

	$quiz_settings = exms_get_post_options( $quiz_id );
	$quiz_reattempt_toggle = isset( $quiz_settings['exms_quiz_reattempts_toggle'] ) ? $quiz_settings['exms_quiz_reattempts_toggle'] : 'no';
	$quiz_reattempt_numbers = isset ( $quiz_settings['exms_reattempts_numbers'] ) ? ( int ) $quiz_settings['exms_reattempts_numbers'] : '';
	$user_attempts = (int) get_user_meta( $user_id, 'exms_quiz_attempts_'.$quiz_id, true );
	$can_attempt_after = get_user_meta( $user_id, 'exms_user_can_attempt_'.$quiz_id, true );
	$msg = $can_attempt_after ? 'You can attempt this quiz after '.date( 'Y-m-d H:i:s a', $can_attempt_after ) : '';

	if( 'no' == $quiz_reattempt_toggle || time() >= $can_attempt_after && 'yes' == $quiz_reattempt_toggle && $quiz_reattempt_numbers >= $user_attempts ) {

		return array( 'result' => true );

	} elseif( 'yes' == $quiz_reattempt_toggle && $quiz_reattempt_numbers < $user_attempts ) {

		$msg = 'Reattempts limit exceeded.';
	}
	return array( 'result' => false, 'response' => $msg );
}

/**
 * Update user reattempt time
 *
 * @param Mixed 	$user_id 	User id
 * @param Mixed 	$quiz_id 	Quiz id
 * 
 */
function exms_update_user_next_attempt_time( $user_id, $quiz_id ) {

	$quiz_settings = exms_get_post_options( $quiz_id );
	$quiz_reattempt_toggle = isset( $quiz_settings['exms_quiz_reattempts'] ) ? $quiz_settings['exms_quiz_reattempts'] : 'no';
	$quiz_reattempt_numbers = isset ( $quiz_settings['exms_reattempts_numbers'] ) ? ( int )$quiz_settings['exms_reattempts_numbers'] : '';
	$reattempt_type = isset( $quiz_settings['exms_reattempt_type'] ) ? $quiz_settings['exms_reattempt_type'] : '';
	$quiz_reattempt_value = isset( $quiz_settings['exms_reattempt_type_value'] ) ? $quiz_settings['exms_reattempt_type_value'] : '';
	$next_reattempt_value = false;

	/**
	 * Reattempt after specific date
	 */
	if( 'x-date' == $reattempt_type ) {

		$next_reattempt_value = strtotime( $quiz_reattempt_value );
	}

	/**
	 * Reattempt after x hours
	 */
	if( 'x-hours' == $reattempt_type ) {

		$next_reattempt_value = strtotime( '+'.$quiz_reattempt_value.' hour', time() );
	}

	/**
	 * Reattempt after x minutes
	 */
	if( 'x-minutes' == $reattempt_type ) {

		$next_reattempt_value = strtotime( '+'.$quiz_reattempt_value.' minute', time() );
	}

	/**
	 * Reattempt after x days
	 */
	if( 'x-days' == $reattempt_type ) {

		$next_reattempt_value = strtotime( '+'.$quiz_reattempt_value.' day', time() );

	}

	update_user_meta( $user_id, 'exms_user_can_attempt_'.$quiz_id, $next_reattempt_value );
}

/**
 * Get user quiz complete date/time
 *
 * @param Mixed 	$user_id 	User id
 * @param Mixed 	$quiz_id 	Quiz id
 * 
 */
function exms_get_user_quiz_complete_date( $user_id, $quiz_id ) {

	$time = get_user_meta( $user_id, 'exms_user_completed_quiz_'.$quiz_id, true );
	$time = $time ? date( 'Y-m-d h:i:s', $time ) : false;

	return ( $time ) ? $time : '-';
}

/**
 * Get user quiz enroll date/time
 *
 * @param Mixed 	$user_id 	User id
 * @param Mixed 	$quiz_id 	Quiz id
 *
 */
function exms_get_user_quiz_enroll_date( $user_id, $quiz_id ) {

	global $wpdb;
	$table_name = EXMS_PR_Fn::exms_user_post_relations_table();

	$time = '-';

    $meta = $wpdb->get_results( " SELECT time FROM $table_name 
        WHERE user_id = $user_id AND post_id = $quiz_id ORDER BY time ASC " );

    if( ! empty( $meta ) && ! is_null( $meta ) ) {

        $enrolled_time = array_map( 'intval', array_column( $meta, 'time' ) );
    	$enrolled_time = isset( $enrolled_time[0] ) ? $enrolled_time[0] : '';
    	$time = $enrolled_time ? date( 'Y-m-d h:i:s', $enrolled_time ) : false;
    }

    return $time;
}

/**
 * Create quiz progress chart
 *
 * @param Array 	$labels 	Labels to show above chart
 * @param Array 	$data 		Data according to labels
 *
 * @return HTML
 */
function exms_create_user_quiz_progress_chart( $labels, $data ) {

	$labels = $data ? implode( ',', $labels ) : [];
	$data = $data ? implode( ',', $data ) : [];
?>
	<div class="exms-row">
		<canvas class="exms-quiz-chart" data-labels="<?php echo $labels; ?>" data-values="<?php echo $data; ?>"></canvas>
	</div>
<?php
}

/**
 * Display Quiz Status
 *
 * @param Mixed 	$user_id 	ID of user 	
 *	
 * @return Array 	
 */
function exms_get_user_quiz_status( $user_id, $quiz_id ) {

	$completed_quizzes = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user_id ) : [];

	if( empty( $completed_quizzes ) ) {
		$completed_quizzes = [];
	}

	$completed_quizzes = array_map( 'intval', $completed_quizzes );

	$quiz_progress = '';
	if( is_array( $completed_quizzes ) ) {
		$quiz_progress = in_array( $quiz_id, $completed_quizzes ) ? 'Completed' : 'In-progress';
	}

	return $quiz_progress;
}

/**
 * Get user completed 
 *quizzes
 * @param Mixed 	$user_id 	User ID
 *
 * @return Array
 */
function exms_get_user_completed_quizzes( $user_id ) {

	$quizzes = get_user_meta( $user_id, 'exms_user_completed_quizzes', true );
	if( !empty( $quizzes ) ) {

		return $quizzes;
	}
}

// /**
//  * Get questions of a quiz
//  *
//  * @param Mixed 	$quiz_id 	Quiz id
//  *
//  * @return Array
//  */
function exms_get_post_options( $quiz_id ) {

	$post_type = get_post_type( $quiz_id );
	return get_post_meta( $quiz_id, $post_type.'_opts', true );
}

/**
 * Get all quizzes
 *
 * @param String 	$args 	Type of quiz either "paid" or "subscribe"
 *
 * @return 	Array
 */
function exms_get_quizzes( $args ) {

	$q_args = array(
		'post_type' 	=> 'exms-quizzes',
		'numberposts'	=> -1,
		'post_status'	=> 'publish'
	);

	if( ! empty( $args ) ) {
		
		$q_args = array_merge( $q_args, $args );
	}
	$quizzes = get_posts( $q_args );

	return $quizzes;
}

/**
 * Get all quizzes
 *
 * @param String 	$type 	Type of quiz either "paid" or "subscribe"
 *
 * @return Array
 */
function exms_get_quizzes_by_type( $type ) {

	$quizzes = get_posts( array(
		'post_type' 	=> 'exms-quizzes',
		'numberposts'	=> -1,
		'post_status'	=> 'publish',
		'meta_key'		=> 'exms_quiz_type',
		'meta_value'	=> $type
	) );
	
	return $quizzes;
}

/**
 * Check is user passed quiz
 *
 * @param Mixed 	$user_id 	User ID
 * @param Mixed 	$quiz_id 	Post id of quiz
 *
 * @return 	Array
 */
function exms_is_user_passed_quiz( $user_id, $quiz_id ) {
	$params = [];
	$params[] = [ 'field' => 'user_id', 'value' => $user_id, 'operator' => '=', 'type'=> '%d'];
	$params[] = [ 'field' => 'quiz_id', 'value' => $quiz_id, 'operator' => '=', 'type'=> '%d'];
	$params[] = [ 'field' => 'passed', 'value' => 'true', 'operator' => '=', 'type'=> '%s'];
	$results = isset( wp_exams()->db ) && wp_exams()->db ? wp_exams()->db->exms_db_query( 'select', 'quizzes_results', $params ) : [];
	
	return ( is_array( $results ) && count( $results ) > 0 ) ? true : false;
}
/**
 * Check is user passed quiz
 *
 * @param Mixed 	$user_id 	User ID
 * @param Mixed 	$quiz_id 	Post id of quiz
 *
 * @return 	Array
 */
function exms_get_quiz_instructors( $quiz_id ) {
	$params = [];
	
	return $params;
}

/**
 * Count of quiz enrolled users
 * 
 * @param  Mixed 	$quiz_id 	Quiz id
 * @return Array
 */
function exms_quiz_enrolled_users_count( $quiz_id ) {

	$options = exms_get_post_options( $quiz_id );
	$enrolled_student = isset( $options['exms_quiz_students_assigned'] ) ? $options['exms_quiz_students_assigned'] : [];
	$enrolled_instructor = isset( $options['exms_quiz_instructors_assigned'] ) ? $options['exms_quiz_instructors_assigned'] : [];
	$enrolled_users = count( $enrolled_student ) + count( $enrolled_instructor );

	return $enrolled_users;
}

/**
 * Count of quiz completed user
 * 
 * @param Mixed 	$quiz_id 	Quiz id
 *
 * @return 	Int
 */
function exms_quiz_complete_users_count( $quiz_id ) {

	$options = exms_get_post_options( $quiz_id );
	$enrolled_student = isset( $options['exms_quiz_students_assigned'] ) ? $options['exms_quiz_students_assigned'] : '';
	$enrolled_instructor = isset( $options['exms_quiz_instructors_assigned'] ) ? $options['exms_quiz_instructors_assigned'] : '';

	$student_progress = '';
	if( $enrolled_student ) {

		foreach( $enrolled_student as $student_id ) {

			$quiz_progress = exms_is_user_passed_quiz( $student_id, $quiz_id );
			if( ! $quiz_progress || ! is_array( $quiz_progress ) ) {
				continue;
			}
			$student_progress += count( $quiz_progress );
		}
	}

	$instructor_progress = '';
	if( $enrolled_instructor ) {
		foreach( $enrolled_instructor as $instructor_id ) {

			$quiz_progress = exms_is_user_passed_quiz( $instructor_id, $quiz_id );
			if( ! $quiz_progress || ! is_array( $quiz_progress ) ) {
				continue;
			}
			$instructor_progress += count( $quiz_progress );
		}
	}
	$completed_count = ( int ) $student_progress + ( int ) $instructor_progress;

	return $completed_count;
}

/**
 * Get all quiz student users
 * 
 * @param Mixed 	$quiz_id 	Post id of quiz
 * 
 * @param Array
 */
function exms_quiz_student_users( $quiz_id ) {

	$post_type = get_post_type( $quiz_id );
	$opts = get_post_meta( $quiz_id, ''.$post_type.'_opts', true );
	return isset( $opts['exms_quiz_students_assigned'] ) ? $opts['exms_quiz_students_assigned'] : [];
}

/**
 * Send email to instructors when user enroll in group
 * 
 * @param $user_id 		(int)
 * @param $post_id 		(int)
 * @param $user_roles 	(array)
 * @param $is_assign 	(bool)
 */
function exms_send_email_to_instructors( $user_id, $post_id, $user_roles, $is_assign ) {

	$post_type = get_post_type( $post_id );
	if( 'exms_groups' != $post_type ) {
		return;
	}

	$opts = get_post_meta( $post_id, $post_type . '_opts', true );
	$instructors = isset( $opts['exms_quiz_instructors_assigned'] ) ? $opts['exms_quiz_instructors_assigned'] : ''; 
	if( empty( $instructors ) ) {
		return;
	}
 
    $instructors_email = [];
    foreach( $instructors as $instructor_id ) {

        if( ! in_array( 'exms_student', $user_roles ) ) {
        	 continue;  
        }

        $user_info = get_userdata( (int) $instructor_id );
        $instructors_email[] = $user_info->data->user_email;
    }

	if( empty( $instructors_email ) || in_array( 'exms_instructor', $user_roles ) ) {
		return;
	}

    $email_settings = Exms_Core_Functions::get_options( 'settings' );

    if( $is_assign ) {

    	$instructors_sub = isset( $email_settings['exms_instructor_assign_subject'] ) ? exms_replace_message_with_tags( $user_id, $post_id, $email_settings['exms_instructor_assign_subject'] ) : '';
    	$instructors_content = isset( $email_settings['exms_instructor_assign_content'] ) ? exms_replace_message_with_tags( $user_id, $post_id, $email_settings['exms_instructor_assign_content'] ) : '';
    
    } else {

    	$instructors_sub = isset( $email_settings['exms_instructor_unassign_subject'] ) ? exms_replace_message_with_tags( $user_id, $post_id, $email_settings['exms_instructor_unassign_subject'] ) : '';
    	$instructors_content = isset( $email_settings['exms_instructor_assign_content'] ) ? exms_replace_message_with_tags( $user_id, $post_id, $email_settings['exms_instructor_unassign_content'] ) : '';
    }
    
    Wpe_Email()->exms_send_email( $instructors_email, $instructors_sub, $instructors_content );
}


/**
 * Check if quiz points is already awarded
 * 
 * @param $user_id 			(int)
 * @param $quiz_id			(int)
 * @param $award_points     (int)
 * @return $has_awarded 	(bool)
 */
function exms_user_has_awarded_quiz_points( $user_id, $quiz_id, $award_points ) {

	$awarded_key = 'exms_has_awarded_' . $award_points . '_' . $quiz_id;
	$already_awarded = get_user_meta( $user_id, $awarded_key, true );

	$has_awarded = '';
	if( 'already_awarded' == $already_awarded ) {
		$has_awarded = true;
	} else {
		$has_awarded = false;
	}

	return $has_awarded;
}

/**
 * Get questions for a quiz
 * 
 * @param $quiz_id ( Required )
 * @param $paged ( optional ) limits to question pages count Like ( 0, 10 )
 */
function exms_get_questions_for_a_quiz( $quiz_id, $paged = 0, $status = '' ) {

	$post_type = get_post_type( $quiz_id );
	if( 'exms_quizzes' != $post_type ) {
		return false;
	}

	$question_ids = EXMS_PR_Fn::exms_current_assigned_post_ids( $post_type, 'exmse-questions', $quiz_id, $paged, $status );
	
	if( ! empty( $question_ids ) && is_array( $question_ids ) ) {
		return $question_ids;
	}
}

/**
 * Get quiz user ids by quiz id
 * 
 * @param $post_id quiz/group
 * @param $paged ( Optional ) paged is used to get 10 users in per page.
 * with specfic limit like ( 0, 10 ) by default empty
 */
function exms_post_get_post_user_ids( $post_id, $paged = '' ) {
    global $wpdb;

    $limit = '';
    if ( ! empty( $paged ) && $paged >= 0 ) {
        $limit = 'LIMIT ' . intval( $paged ) . ',10';
    }

    $table_name = EXMS_PR_Fn::exms_user_post_table();
    $user_ids = [];

    $quiz_meta = $wpdb->get_results( "SELECT user_id FROM $table_name WHERE post_id = " . intval( $post_id ) . " ORDER BY ID ASC $limit" );

    if ( ! empty( $quiz_meta ) ) {
        foreach ( $quiz_meta as $meta ) {
            $user = get_user_by( 'id', $meta->user_id );
            if ( $user && in_array( 'exms_instructor', (array) $user->roles ) ) {
                $user_ids[] = intval( $user->ID );
            }
        }
    }

    return $user_ids;
}

function exms_get_post_user_ids( $post_id, $roles = '', $paged = '' ) {

	global $wpdb;

    $limit = '';

    if( ! empty( $paged ) && $paged >= 0 ) {
        $limit = 'LIMIT '.$paged.',10';
    }

    $table_name = EXMS_PR_Fn::exms_user_post_table();

    $user_ids = [];
    $quiz_meta = $wpdb->get_results( " SELECT user_id FROM $table_name 
        WHERE post_id = $post_id ORDER BY ID ASC $limit " );

    if ( ! empty( $quiz_meta ) ) {
		if( $roles == "leader" ) {
			foreach ( $quiz_meta as $meta ) {
				$user = get_user_by( 'id', $meta->user_id );
				if ( $user && !in_array( 'exms_instructor', (array) $user->roles ) && in_array( 'exms_group_leader', (array) $user->roles ) ) {
					$user_ids[] = intval( $user->ID );
				}
			}
		} else {
			foreach ( $quiz_meta as $meta ) {
				$user = get_user_by( 'id', $meta->user_id );
				if ( $user && !in_array( 'exms_instructor', (array) $user->roles ) && !in_array( 'administrator', (array) $user->roles ) && !in_array( 'exms_group_leader', (array) $user->roles ) ) {
					$user_ids[] = intval( $user->ID );
				}
			}
		}
    } 

    return $user_ids;
}

/**
 * Get Quiz ID if the quiz has any user
 */
function exms_get_quiz_ids_has_users( $limit, $paged = '', $search_users = [] ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

    $paginate = '';
    if( ! empty( $limit ) && empty( $search_users ) ) {

        $paginate = 'LIMIT '.$paged.','.$limit.'';
    }

    $users_in = '';
    if( ! empty( $search_users ) ) {
    	$users_in = 'AND user_id IN( '.implode( ',', $search_users ).' )';
    }

    $quiz_ids = [];
    $meta = $wpdb->get_results( " SELECT post_id, user_id, time FROM $table_name 
        WHERE post_type = 'exms-quizzes' $users_in ORDER BY time ASC $paginate " );

    return $meta;
}

/**
 * Check if quiz is a part of parent post
 * 
 * @param $parent_post_type
 * @param $quiz_id
 */
function exms_is_quiz_in_parent_post( $parent_post_type, $quiz_id ) {

	global $wpdb;

	$return = false;
	$post_type = get_post_type( $quiz_id );
	$table_name = EXMS_PR_Fn::exms_relation_table_name();

    $quiz_exists = $wpdb->get_results( "SELECT child_post_id FROM $table_name 
        WHERE parent_post_type = '$parent_post_type'
        AND assigned_post_type = 'exms-quizzes'
        AND child_post_id = $quiz_id " );

    if( ! empty( $quiz_exists ) && ! is_null( $quiz_exists ) ) {
    	$return = true;
    }

	return $return;	
}

/**
 * Mark complete the quiz progress
 * 
 * @param $user_id ( required )
 * @param $quiz_id ( required )
 * @param $is_quiz_passed ( required ) bool
 */
function exms_quiz_mark_complete( $user_id, $quiz_id, $is_quiz_passed, $parent_posts = '', $total_points = '', $obt_points = '', $points_type = '', $total_answers = '', $correct_answer = '', $gained_percentage = '', $essay_ids = [] ) {

	wp_exams()->dbquizres->exms_db_insert( 'quizzes_results', array(
		'user_id' 			=> $user_id,
		'quiz_id'			=> $quiz_id, 
		'parent_posts'		=> $parent_posts,
		'total_points'		=> $total_points,
		'obtained_points'	=> $obt_points,
		'points_type'		=> $points_type,
		'total_questions'	=> $total_answers,
		'correct_questions'	=> $correct_answer,
		'passed'			=> $is_quiz_passed,
		'percentage'		=> $gained_percentage,
		'essay_ids'			=> serialize( $essay_ids )
	) );

	$args = [
		'total_points'		=> $total_points,
		'obtained_points'	=> $obt_points,
		'points_type'		=> $points_type,
		'total_questions'	=> $total_answers,
		'correct_questions'	=> $correct_answer,
		'passed'			=> $is_quiz_passed,
		'percentage'		=> $gained_percentage,
		'essay_ids'			=> serialize( $essay_ids )
	];

	/**
     * Fires after the quiz mark completed
     * 
     * @param $user_id
     * @param $quiz_id
     * @param $parent_posts
     * @param $is_completed ( true )
     */
    do_action( 'exms_quiz_completed', true, $user_id, $quiz_id, $parent_posts, $args );
}

/**
 * Mark incomplete the quiz progress
 * 
 * @param $user_id
 * @param $quiz_id ( required )
 * @param $parent_posts ( optional )
 */
function exms_quiz_mark_incomplete( $user_id, $quiz_id, $parent_posts = '' ) {

	global $wpdb;
    $table_name = $wpdb->prefix.'exms_quizzes_results';

    $wpdb->query( 
        "DELETE FROM $table_name
        WHERE user_id = $user_id 
        AND quiz_id = $quiz_id
        AND parent_posts = '$parent_posts' "
    );

    /**
     * Fires after the quiz mark incompleted
     * 
     * @param $user_id
     * @param $quiz_id
     * @param $parent_posts
     * @param $is_completed ( false )
     */
    do_action( 'exms_quiz_completed', false, $user_id, $quiz_id, $parent_posts, [] );
}

function exms_get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
    $args  = array(
        'title'                  => $page_title,
        'post_type'              => $post_type,
        'post_status'            => get_post_stati(),
        'posts_per_page'         => 1,
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'no_found_rows'          => true,
        'orderby'                => 'post_date ID',
        'order'                  => 'ASC',
    );
    $query = new WP_Query( $args );
    $pages = $query->posts;

    if ( empty( $pages ) ) {
        return null;
    }

    return get_post( $pages[0], $output );
}

/**
 * created a function to check whether a user is assigned to a quiz or not 
 */
function exms_is_user_assigned_to_quiz( $user_id, $quiz_id ) {

	global $wpdb;

	$quiz_type = exms_get_quiz_type( $quiz_id );

	if( 'free' == $quiz_type ) {

		$is_aasign = exms_is_user_in_post( $user_id, $quiz_id );
		
		if( ! $is_aasign ) {
			exms_assign_user_into_post( $quiz_id, $user_id ); 
		}
	} else {

		$course_id = exms_get_course_id();

		if( $course_id && $quiz_id ) {

			$is_course_assign = exms_is_user_in_post( $user_id, $course_id );

			if( $is_course_assign ) {
				exms_assign_user_into_post( $quiz_id, $user_id );
			}
		}
	}

	$is_assigned = (bool) $wpdb->get_var(
	    $wpdb->prepare(
	        "SELECT COUNT(id) FROM {$wpdb->prefix}exms_user_enrollments 
	        WHERE user_id = %d AND post_id = %d",
	        $user_id,
	        $quiz_id
	    )
	);

    return $is_assigned;
}

/**
 * create a function to get quiz timer
 *
 * @param $quiz_id
 */
function exms_get_quiz_data( $quiz_id, $column_name = '*' ) {

	global $wpdb;

	if( ! $quiz_id ) {
		return;
	}

	$quiz_data = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT $column_name 
			FROM {$wpdb->prefix}exms_quiz 
			WHERE quiz_id = %d",
			$quiz_id
		)
	);

	if( strpos( $column_name, ',' ) !== false ) {
        return $quiz_data;
    }

	return isset( $quiz_data->$column_name ) ? $quiz_data->$column_name : 'off';
}

function exms_timer_is_empty( $time ) {
    $time = trim((string) $time);
    return in_array( $time, ['00:00:00', '00:00', '0', '', null], true );
}

function get_quiz_breadcrumb( $post_id, $user_id = 0 ) {
    global $wpdb;

    $breadcrumb = [];
    if ( empty( $post_id ) ) {
        return $breadcrumb;
    }

    $request_uri = trim( $_SERVER['REQUEST_URI'], '/' );
    $path_parts  = explode( '/', $request_uri );
    if ( count( $path_parts ) > 1 ) {
        $built_breadcrumb = [];
        foreach ( $path_parts as $slug ) {
            $post = get_page_by_path( $slug, OBJECT, get_post_types( [ 'public' => true ] ) );
            if ( $post ) {
                $built_breadcrumb[] = [
                    'id'    => $post->ID,
                    'title' => get_the_title( $post->ID ),
                    'type'  => get_post_type( $post->ID ),
                    'url'   => get_permalink( $post->ID ),
                ];
            }
        }
        if ( ! empty( $built_breadcrumb ) ) {
            return $built_breadcrumb;
        }
    }

    $post_types = get_option( 'exms_post_types' );

    $walk_up = function( $child_id, $child_type ) use ( $wpdb, $post_types, &$walk_up ) {
        $crumbs = [[
            'id'    => $child_id,
            'title' => get_the_title( $child_id ),
            'type'  => $child_type,
            'url'   => get_permalink( $child_id ),
        ]];

        $parents = $wpdb->get_results( $wpdb->prepare(
            "SELECT parent_post_id, parent_post_type 
             FROM wp_exms_post_relationship 
             WHERE child_post_id = %d",
            $child_id
        ) );

        if ( ! empty( $parents ) ) {
            foreach ( $parents as $p ) {
                $crumbs = array_merge(
                    $walk_up( $p->parent_post_id, $p->parent_post_type ),
                    $crumbs
                );
            }
        }

        return $crumbs;
    };

    $courses = $wpdb->get_results( $wpdb->prepare(
        "SELECT parent_post_id 
         FROM wp_exms_post_relationship 
         WHERE child_post_id = %d AND parent_post_type = %s",
        $post_id, 'exms-courses'
    ) );

    if ( count( $courses ) > 1 && $user_id ) {
        $last_activity = $wpdb->get_row( $wpdb->prepare(
            "SELECT result_date FROM wp_exms_quizzes_results 
             WHERE quiz_id = %d AND user_id = %d 
             ORDER BY result_date DESC LIMIT 1",
            $post_id, $user_id
        ) );

        if ( $last_activity ) {
            if ( strtolower( $last_activity->passed ) !== 'yes' ) {
                return $walk_up( $post_id, 'exms-quizzes' );
            } else {
                foreach ( $courses as $c ) {
                    if ( $c->parent_post_id != $last_activity->parent_posts ) {
                        return $walk_up( $post_id, 'exms-quizzes' );
                    }
                }
            }
        }
    }

    $assignments = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM wp_exms_post_relationship WHERE child_post_id = %d",
        $post_id
    ) );

    if ( count( $assignments ) > 1 && $user_id ) {
        $last_activity = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM wp_exms_quizzes_results 
             WHERE quiz_id = %d AND user_id = %d 
             ORDER BY result_date DESC LIMIT 1",
            $post_id, $user_id
        ) );

        if ( $last_activity ) {
            return $walk_up( $post_id, 'exms-quizzes' );
        }
    }

    $breadcrumb = $walk_up( $post_id, 'exms-quizzes' );

    return $breadcrumb;
}

/**
 * create a function to get question data
 * 
 * @param question_id
 * @param column_name
 */
function exms_get_question_data( $question_id, $column_name ) {

	global $wpdb;

	if( ! $question_id || ! $column_name ) {
		return;
	}

	$question_data = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT $column_name 
			FROM {$wpdb->prefix}exms_questions 
			WHERE question_id = %d",
			$question_id
		)
	);

	if( 'timer' == $column_name ) {

		$timer = $question_data ?? '00:00:00';

		$parts = array_pad(explode( ':', $timer ), 3, '00' );
		list( $hours, $minutes, $seconds ) = $parts;

		if ( ( int )$hours > 0 ) {
			$data = sprintf( '%02d:%02d:%02d', $hours, $minutes, $seconds );
		} else {
			$data = sprintf( '%02d:%02d', $minutes, $seconds );
		}
	} else {
		$data = $question_data;
	}

	return $data;
}

/**
 * create a function to get count of a correct/wrong answer for a quiz
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $is_correct 0 for wrong answer and 1 for correct answer
 */
function exms_get_quiz_answer_count( $user_id, $quiz_id, $is_correct ) {

    global $wpdb;

    if( ! $user_id || ! $quiz_id ) {
    	return;
    }

	$table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

	$latest_attempt = exms_get_latest_attempt( $user_id, $quiz_id );

	$is_correct_count = $wpdb->get_var( $wpdb->prepare(
	    "SELECT COUNT(*)
	     FROM $table_name
	     WHERE user_id = %d
	       AND quiz_id = %d
	       AND attempt_number = %d
	       AND is_correct = %s",
	    $user_id,
	    $quiz_id,
	    $latest_attempt,
	    $is_correct
	) );

	return intval( $is_correct_count ); 
}

/**
 * create a function to get questions of a quiz
 * @param $quiz_id
 */
function exms_get_quiz_questions( $quiz_id ) {
	global $wpdb;

	if( ! $quiz_id ) {
		return;
	}

	$question_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT q.question_id 
			 FROM {$wpdb->prefix}exms_quiz_questions q
			 INNER JOIN {$wpdb->posts} p ON q.question_id = p.ID
			 WHERE q.quiz_id = %d
			   AND p.post_status = 'publish'
			   AND q.status = 'active'",
			$quiz_id
		)
	);

	return $question_ids;
}

/**
 * create a function to get no attempted count
 * 
 * @param $user_id
 * @param $quiz_id
 */
function exms_get_not_attempt_count( $user_id, $quiz_id ) {

	global $wpdb;

	$table = $wpdb->prefix . 'exms_exam_user_question_attempts';

	$sql = "
	    SELECT COUNT(*) 
	    FROM {$table}
	    WHERE user_id = %d
	      AND quiz_id = %d
	      AND (
	            JSON_UNQUOTE(JSON_EXTRACT(answer, '$.answer')) IS NULL
	            OR JSON_UNQUOTE(JSON_EXTRACT(answer, '$.answer')) = ''
	          )
	";

	$query = $wpdb->prepare($sql, $user_id, $quiz_id);
	$not_attempt_count = $wpdb->get_var($query);
	return $not_attempt_count;
}

/**
 * Get assigned user count for a quiz excluding admins and instructors
 *
 * @param int $quiz_id
 * @return int
 */
function exms_get_quiz_assign_count( $quiz_id ) {
    global $wpdb;

    $user_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(DISTINCT eu.user_id)
             FROM {$wpdb->prefix}exms_user_enrollments eu
             INNER JOIN {$wpdb->users} u ON eu.user_id = u.ID
             INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
             WHERE eu.post_id = %d
             AND um.meta_key = '{$wpdb->prefix}capabilities'
             AND um.meta_value NOT LIKE '%%administrator%%'
             AND um.meta_value NOT LIKE '%%instructor%%'",
            $quiz_id
        )
    );

    return $user_count;
}


/**
 * create a function to get quiz_type
 * 
 * @param $quiz_id
 */
function exms_get_quiz_type( $quiz_id ) {

	global $wpdb;

	if( ! $quiz_id ) {
		return;
	}

	$table_name = $wpdb->prefix . 'exms_quiz_type';

	$quiz_type = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT quiz_type FROM {$table_name} WHERE quiz_id = %d",
			$quiz_id
		)
	);

	return $quiz_type;
}

/**
 * create a function to get total points for a quiz
 * 
 * @param $quiz_id
 */
function exms_get_quiz_total_points( $quiz_id ) {

	global $wpdb;

	if( ! $quiz_id ) {
		return;
	}

	$question_ids = exms_get_quiz_questions( $quiz_id );

	$table_name = "{$wpdb->prefix}exms_questions";

	$ids = implode(',', array_fill(0, count($question_ids), '%d'));

	$query = $wpdb->prepare(
	    "SELECT SUM(points_for_question) as total_points 
	    FROM $table_name 
	    WHERE question_id IN ($ids)",
	    $question_ids
	);

	$total_points = $wpdb->get_var($query);
	return intval( $total_points );
}

/**
 * create a function to get latest user attempt for the quiz
 *
 * @param $user_id
 * @param $quiz_id
 */
function exms_get_latest_attempt( $user_id, $quiz_id ) {

	global $wpdb;

	if( ! $user_id || ! $quiz_id ) {
		return;
	}

	$table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

	$latest_attempt_date = $wpdb->get_var( $wpdb->prepare(
	    "SELECT MAX(attempt_number) 
	     FROM $table_name
	     WHERE user_id = %d AND quiz_id = %d",
	    $user_id,
	    $quiz_id
	) );

	$latest_attempt_date = intval( $latest_attempt_date );

	if( $latest_attempt_date == 0 ) {
		$latest_attempt_date = 1;
	}

	return $latest_attempt_date;
}

/**
 * create a function to get total possible score of a quiz
 * 
 * @param $user_id
 * @param $quiz_id 
 */
function exms_get_quiz_possible_score( $user_id, $quiz_id ) {

	global $wpdb;

	if( ! $user_id || ! $quiz_id ) {
		return;
	}

	$table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

	$latest_attempt_number = exms_get_latest_attempt( $user_id, $quiz_id );

	$total_possible_score = $wpdb->get_var( $wpdb->prepare(
		"SELECT SUM(total_possible_score)
		FROM $table_name
		WHERE user_id = %d AND quiz_id = %d AND attempt_number = %d",
		$user_id,
		$quiz_id,
		$latest_attempt_number
	) );

	return intval( $total_possible_score );
}

/**
 * creare a function to check that quiz is attempted or not
 *
 * @param $quiz_id
 * @param $quiz_id
 */
function exms_user_has_quiz_attempt( $quiz_id, $user_id ) {

    global $wpdb;

    if ( ! $quiz_id || ! $user_id ) {
        return false;
    }

    $current_page_url = exms_get_current_url();
    $post_parent      = exms_get_post_parent( 'exms-quizzes', $current_page_url );
    $course_id        = exms_get_course_id();

    $sql  = "SELECT id FROM {$wpdb->prefix}exms_quizzes_results 
             WHERE user_id = %d AND quiz_id = %d";

    $args = [$user_id, $quiz_id];

    if ( ! empty( $course_id ) && ! empty( $post_parent ) ) {
        $sql .= " AND course_id = $course_id AND parent_posts = $post_parent";
    } else {
        $sql .= " AND (course_id IS NULL OR course_id = 0) 
                  AND (parent_posts IS NULL OR parent_posts = '0')";
    }

    $query = $wpdb->prepare( $sql, $args );
    return (bool) $wpdb->get_var( $query );
}

/**
 * creare a function to get user submitted quiz data
 * 
 * @param $user_id
 * @param $quiz_id
 * @param column_name
 */
function exms_fetch_user_quiz_data( $user_id, $quiz_id, $column_name = '*' ) {

	global $wpdb;

	if( ! $quiz_id || ! $user_id ) {
		return;
	}

	$latest_attempt = exms_get_quiz_latest_attempt( $quiz_id, $user_id );
	$quiz_data = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT $column_name
			FROM {$wpdb->prefix}exms_quizzes_results
			WHERE quiz_id = %d
			AND user_id = %d
			AND attempt_number = %d",
			$quiz_id,
			$user_id,
			$latest_attempt
		)
	);

	return $quiz_data;
}

/**
 * create a function to get quiz all data
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $status
 */
function exms_get_user_quiz_data( $user_id, $quiz_id, $status = 'active' ) {

	global $wpdb;

	$quiz_questions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT 
			q.quiz_id, 
			q.question_id, 
			q_post.post_content AS quiz_content,
			p_post.post_title AS question_title, 
			p_post.post_content AS question_content,
			
			q_s.answer AS user_answer,
			q_s.is_correct AS user_is_correct,
			q_s.file_url AS user_file_url,

			qq.points_for_question, 
			qq.question_type,

			min_meta.meta_value AS min_range,
			max_meta.meta_value AS max_range,

			GROUP_CONCAT(qa.answer_text) AS answers,
			GROUP_CONCAT(qa.is_correct) AS correct_answers

			FROM {$wpdb->prefix}exms_quiz_questions q 
			INNER JOIN {$wpdb->prefix}exms_questions qq 
			ON q.question_id = qq.question_id
			INNER JOIN {$wpdb->prefix}posts p_post 
			ON q.question_id = p_post.ID  
			INNER JOIN {$wpdb->prefix}posts q_post 
			ON q.quiz_id = q_post.ID
			LEFT JOIN (
				SELECT t1.question_id, t1.answer, t1.quiz_id, t1.is_correct, t1.file_url
				FROM {$wpdb->prefix}exms_exam_user_question_attempts t1
				INNER JOIN (
					SELECT quiz_id, question_id, MIN(id) AS min_id
					FROM {$wpdb->prefix}exms_exam_user_question_attempts
					WHERE user_id = %d
					GROUP BY quiz_id, question_id
					) t2 ON t1.id = t2.min_id
				) q_s 
			ON q.question_id = q_s.question_id AND q.quiz_id = q_s.quiz_id
			LEFT JOIN {$wpdb->prefix}exms_answer qa
			ON q.question_id = qa.question_id
			LEFT JOIN {$wpdb->prefix}postmeta min_meta 
			ON min_meta.post_id = q.question_id AND min_meta.meta_key = 'exms_range_min'
			LEFT JOIN {$wpdb->prefix}postmeta max_meta 
			ON max_meta.post_id = q.question_id AND max_meta.meta_key = 'exms_range_max'

			WHERE q.quiz_id = %d
			AND q.status = %s
			AND p_post.post_type = 'exms-questions'

			GROUP BY q.quiz_id, q.question_id, q.status, 
			qq.points_for_question, qq.question_type, 
			min_meta.meta_value, max_meta.meta_value,
			p_post.post_title, p_post.post_content, 
			q_post.post_title, q_post.post_content, 
			q_s.answer, q_s.is_correct, q_s.file_url",
			$user_id,
			$quiz_id,
			$status
		)
	);

	return $quiz_questions;
} 

/**
 * create a function to get quiz rank
 * 
 * @param $quiz_id
 * @param $user_id
 */
function exms_get_quiz_rank( $quiz_id, $user_id ) {

	if( ! $quiz_id || ! $user_id ) {
		return;
	}

	$rank_data = get_post_meta( $quiz_id, 'exms-quiz-rank', true );
	
	if( ! is_array( $rank_data ) || empty( $rank_data ) ) {
		return;
	}

	$keys = array_keys( $rank_data );
	$index_of_user = array_search('quiz_'.$user_id, $keys);
	$index_of_user = $index_of_user + 1;
	return $index_of_user;
}

/**
 * create a function to get question time total
 *
 * @param $quiz_id
 * @param $user_id
 */
function exms_get_question_time_total( $quiz_id, $user_id ) {

	global $wpdb;

	$table_name = $wpdb->prefix . "exms_exam_user_question_attempts";
	$latest_attempt = exms_get_latest_attempt( $user_id, $quiz_id );
	
	$total_time = $wpdb->get_var(
	    $wpdb->prepare(
	        "SELECT SUM(time_taken) 
	         FROM $table_name 
	         WHERE user_id = %d 
	           AND quiz_id = %d 
	           AND attempt_number = %d",
	        $user_id,
	        $quiz_id,
	        $latest_attempt
	    )
	);

	if ( is_null($total_time) ) {
	    $total_time = 0;
	}

	return $total_time;
}

/**
 * create a function to convert second into time format
 * 
 * @param $time in second
 */
function exms_convert_second_into_time_format( $seconds ) {

	if( ! $seconds ) {
		return 0;
	}

    $hours   = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs    = $seconds % 60;

    if ($hours > 0) {
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
    } else {
        return sprintf("%02d:%02d", $minutes, $secs);
    }
}

/**
 * create a function to check question is already submitted or not
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $question_id
 */
function exms_get_submmited_quiz_attempt( $user_id, $quiz_id ) {

	global $wpdb;

	if( ! $user_id || ! $quiz_id ) {
		return;
	}

	$table_name = $wpdb->prefix . 'exms_quizzes_results';

	$latest_attempt = $wpdb->get_var( $wpdb->prepare(
		"SELECT attempt_number
		FROM {$table_name}
		WHERE user_id = %d
		AND quiz_id = %d
		ORDER BY attempt_number DESC
		LIMIT 1",
		$user_id,
		$quiz_id
	) );

	if( is_null($latest_attempt) ) {
		$latest_attempt = 0;
	}

	return $latest_attempt;
}

/**
 * create a function to get attempt results
 * 
 * @param $user_id (int)
 * @param $quiz_id (int)
 * @param $start_time (strtotime)
 * @param $end_date (strtotime)  
 */
function exms_get_quiz_results_by_time( $user_id, $quiz_id, $start_time, $end_time ) {

	global $wpdb;

	$table_name = $wpdb->prefix . "exms_quizzes_results";

	$query = $wpdb->prepare(
		"SELECT COUNT(*)
		 FROM $table_name
		 WHERE user_id = %d
		   AND quiz_id = %d
		   AND result_date BETWEEN %d AND %d",
		$user_id,
		$quiz_id,
		$start_time,
		$end_time
	);

	return $wpdb->get_var( $query );
}

/**
 * create a function to check re-attempt is true or not
 * 
 * @param $user_id (int)
 * @param $quiz_id (int)
 */
function exms_is_reattempt_available( $user_id, $quiz_id ) {

	global $wpdb;

	$is_available = false;
	$quiz_reattempt_table = $wpdb->prefix . 'exms_quiz_reattempt_settings';
	$reattempt_query = $wpdb->prepare( "SELECT * FROM $quiz_reattempt_table WHERE quiz_id = %d", $quiz_id );
	$reattempt_data = $wpdb->get_row( $reattempt_query );
	$reattempt_number = isset( $reattempt_data->quiz_reattempts_no ) ? $reattempt_data->quiz_reattempts_no : '';
	$reattempt_type = isset( $reattempt_data->quiz_reattempts_type ) ? $reattempt_data->quiz_reattempts_type : '';
	$reattempt_gab = isset( $reattempt_data->quiz_reattempts_field ) ? $reattempt_data->quiz_reattempts_field : '';
	$result_submitted_time = exms_fetch_user_quiz_data( $user_id, $quiz_id, 'result_date' );
	$result_submitted_time = isset( $result_submitted_time->result_date ) ? intval( $result_submitted_time->result_date ) : 0;

	if( 'x-days' == $reattempt_type ) {
		$after_time = $result_submitted_time + ( intval( $reattempt_gab ) * 86400 ); 
	} elseif( 'x-hours' == $reattempt_type ) {
		$after_time = $result_submitted_time + ( intval( $reattempt_gab ) * 3600 );
	} elseif( 'x-minutes' == $reattempt_type ) {
		$after_time = $result_submitted_time + ( intval( $reattempt_gab ) * 60 );
	} elseif( 'x-date' == $reattempt_type ) {
		$after_time = strtotime( $reattempt_gab );
	}

	$reattempt_data = intval( exms_get_quiz_results_by_time( $user_id, $quiz_id, $result_submitted_time, $after_time ) );

	$reattempt_data = $reattempt_data - 1;
	$current_timestamp = current_time( 'timestamp' );

	if( ( $current_timestamp < $after_time ) && ( $reattempt_number > $reattempt_data ) ) {
		$is_available = true;
	}

	if( $current_timestamp > $after_time ) {
		$is_available = true;
	}

	return $is_available;
}

/**
 * create a function to get post id using slug
 *
 * @param $slug
 */
function exms_get_post_id_by_slug( $slug ) {

	global $wpdb;

	if( ! $slug ) {
		return 0;
	}

    $post_id = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID 
             FROM $wpdb->posts 
             WHERE post_name = %s 
               AND post_status = 'publish' 
             LIMIT 1",
            $slug
        )
    );

    $post_id = isset( $post_id[0] ) ? $post_id[0] : 0;
    return $post_id;
}

/**
 * create a function to get post parent
 * 
 * @param $post_type 
 */
function exms_get_post_parent( $post_type, $current_permalink ) {

    if( 'exms-quizzes' == $post_type ) {

        $replaced_url = str_replace( site_url(), ' ', $current_permalink );
        $replaced_url_array = explode( '/', $replaced_url );
        $filtered = array_filter( $replaced_url_array, function( $value ) {
            return trim($value) !== '';
        } );

        $filtered = array_values($filtered);
        $wanted_index = count( $filtered ) - 2;
        $wanted_slug = isset( $filtered[$wanted_index] ) ? $filtered[$wanted_index] : '';
        $post_type = exms_get_post_type_by_slug( $wanted_slug );
        $post = get_page_by_path( $wanted_slug, OBJECT, $post_type );
        $post_id = isset( $post->ID ) ? $post->ID : 0;
    }

    return $post_id;
}

/**
 * create a function to quiz result settings
 */
function exms_get_quiz_result_setting( $quiz_id, $column_name = '*' ) {

	global $wpdb;

	if( ! $quiz_id ) {
		return;
	}
	
	$quiz_data = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT $column_name
			FROM {$wpdb->prefix}exms_quiz_result_settings
			WHERE quiz_id = %d",
			$quiz_id
		)
	);

	return $quiz_data;
}

/**
 * create a function to get quiz latest attempt
 */
function exms_get_quiz_latest_attempt( $quiz_id, $user_id ) {

	global $wpdb;

	if ( ! $quiz_id || ! $user_id ) {
		return false;
	}

	$current_page_url = exms_get_current_url();
	$post_parent      = exms_get_post_parent( 'exms-quizzes', $current_page_url );
	$course_id        = exms_get_course_id();

	$sql  = "SELECT attempt_number FROM {$wpdb->prefix}exms_quizzes_results 
	WHERE user_id = %d AND quiz_id = %d";

	$args = [$user_id, $quiz_id];

	if ( ! empty( $course_id ) && ! empty( $post_parent ) ) {
		$sql .= " AND course_id = $course_id AND parent_posts = $post_parent";
	} else {
		$sql .= " AND (course_id IS NULL OR course_id = 0) 
		AND (parent_posts IS NULL OR parent_posts = '0')";
	}

	$query = $wpdb->prepare( $sql, $args );
	return $wpdb->get_var( $query );
}
