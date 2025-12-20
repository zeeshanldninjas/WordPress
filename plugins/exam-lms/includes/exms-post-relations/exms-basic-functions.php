<?php 

/**
 * Template function for wp exam
 */
if( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

/**
 * Check if post is completed
 * 
 * @param $post_id
 * @param $parent_posts
 * @param $user_id
 */
function exms_is_post_completed( $post_id, $parent_posts, $user_id ) {

    global $wpdb;

    $table_name = EXMS_PR_Fn::exms_completions_table_name();
    $is_completed = false;

    $relation_meta = [];

    if( ! empty( $post_id ) && ! empty( $parent_posts ) && ! empty( $user_id ) ) {
        
        $relation_meta = $wpdb->get_results( " SELECT completed as post_completed
        FROM $table_name 
        WHERE post_id = $post_id
        AND parent_posts = '$parent_posts'
        ORDER BY time DESC " );
    }

    if( ! empty( $relation_meta ) && ! is_null( $relation_meta ) ) {
        $is_completed = true;
    }

    return $is_completed;
}

/**
 * Mark complete post
 * 
 * @param $post_id
 * @param $parent_post_id
 * @param $user_id
 */
function exms_post_mark_complete( $post_id, $parent_post_ids, $user_id ) {

    global $wpdb;

    $table_name = EXMS_PR_Fn::exms_completions_table_name();
    $post_type = get_post_type( $post_id );

    $wpdb->insert( $table_name, [
        'post_id'           => $post_id,
        'post_type'         => $post_type,
        'parent_posts'      => $parent_post_ids,
        'user_id'           => $user_id,
        'completed'         => true,
        'time'              => time(),
    ] );

    /**
     * Fires after the post mark completed
     * 
     * @param $user_id
     * @param $post_id
     * @param $parent_posts
     * @param $is_completed ( true )
     */
    do_action( 'exms_post_completed', true, $user_id, $post_id, $parent_post_ids );
}

/**
 * Mark incomplete post
 * 
 * @param $post_id
 * @param $parent_post_id
 * @param $user_id
 */
function exms_post_mark_incomplete( $post_id, $parent_post_ids, $user_id ) {

    global $wpdb;

    $table_name = EXMS_PR_Fn::exms_completions_table_name();
    $post_type = get_post_type( $post_id );

    $wpdb->query( 
        "DELETE FROM $table_name
        WHERE post_id = $post_id 
        AND post_type = '$post_type'
        AND parent_posts = '$parent_post_ids' "
    );

    /**
     * Fires after the post mark incompleted
     * 
     * @param $user_id
     * @param $post_id
     * @param $parent_posts
     * @param $is_completed ( false )
     */
    do_action( 'exms_post_completed', false, $user_id, $post_id, $parent_post_ids );
}

/**
 * Get post name by post type
 * 
 * @param $post_type
 */
function exms_post_name( $post_type ) {

    $post_name = ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) );

    return apply_filters( 'exms_post_name', $post_name );
}

/**
 * Get post permalinks
 * 
 * @param $relation_id
 * @param $post_id
 */
function exms_step_permalinks( $relation_id, $parent_ids ) {

	$permalink = get_permalink( $relation_id ).'?parent_posts='.$parent_ids.'';
	$parent_post = isset( $_GET['parent_posts'] ) ? $_GET['parent_posts'] : '';
	if( $parent_post ) {

		$permalink = $permalink.'-'.$parent_post.'';
	}

	return $permalink;
}

/**
 * Check weather all post child relation is completed
 * 
 * @param $post_id
 * @param $relation_id
 */
function exms_is_childs_completed( $post_id, $relation_ids ) {

	$is_completed = false;
	$post_completions = exms_completion_post_count( $post_id, $relation_ids );

    if( $post_completions == count( $relation_ids ) ) {
    	$is_completed = true;

    } elseif( is_bool( $post_completions ) && $post_completions === true ) {
		$is_completed = true;    	
    }

    return $is_completed;
}

/**
 * Get post completions meta
 * 
 * @param $post_id
 * @param $relation_ids
 */
function exms_completion_post_count( $post_id, $relations, $parent_posts = '', $progress_type = '' ) {

    global $wpdb;

    if( 'straight' == $progress_type ) {

        if( isset( $_GET['parent_posts'] ) ) {
            $parent_posts = isset( $_GET['parent_posts'] ) ? $post_id.'-'.$_GET['parent_posts'] : '';
        } elseif( ! empty( $parent_id ) ) {
            $parent_posts = $post_id.'-'.$parent_id;
        } else {
            $parent_posts = $post_id;
        }

    } else {

        if( isset( $_GET['parent_posts'] ) && ! empty( $parent_posts ) ) {
            $parent_posts = $parent_posts.'-'.$_GET['parent_posts'];
        } elseif( isset( $_GET['parent_posts'] ) ) {
            $parent_posts = $post_id.'-'.$_GET['parent_posts'];
        }
    }

    $parent_posts = array_filter( array_unique( explode( '-', $post_id.'-'.$parent_posts ) ) );
    $parent_posts = implode( '-', $parent_posts );

	$table_name = EXMS_PR_Fn::exms_completions_table_name();
    $relation_quizzes = [];
	$relation_ids = [];
	if( ! empty( $relations ) && is_array( $relations ) ) {
		foreach( $relations as $relation_id ) {

			if( 'exms_quizzes' == get_post_type( $relation_id ) ) {
			     
                $quiz_complete = exms_is_quiz_complete_with_parents( get_current_user_id(), $relation_id, $parent_posts );
                if( $quiz_complete ) {
                    $relation_quizzes[] = $quiz_complete;
                }
			}

			$relation_ids[] = $relation_id;
		}
	}

    $complete_quiz_count = count( $relation_quizzes );
	$relation_ids = array_unique( $relation_ids );

	$relations = implode( ',', $relation_ids );
	if( empty( $relations ) ) {
		return true;
	}

    $relation_meta = $wpdb->get_results( " SELECT completed as post_completed
    FROM $table_name 
    WHERE parent_posts = '$parent_posts' 
    AND post_id IN( $relations )
    AND completed = 1
    ORDER BY time DESC " );

    if( ( empty( $relation_meta ) || is_null( $relation_meta ) ) && empty( $relation_quizzes ) ) {
        return false;
    }

    $post_completions = array_filter( array_map( 'intval', array_column( $relation_meta, 'post_completed' ) ) );
    $post_completetion_count = is_array( $post_completions ) ? count( $post_completions ) : 0;
    
    if( ! empty( $complete_quiz_count ) ) {
        $post_completetion_count = $post_completetion_count + $complete_quiz_count;   
    }

    return $post_completetion_count;
}

/**
 * Check the child posts are complete on not
 * 
 * @param $post_id
 * @param $relation_ids
 */
function exms_post_progress( $post_id, $relation_ids, $parent_posts = '', $progress_type = '' ) {

	$progress_data = [];

	$progress_count = exms_completion_post_count( $post_id, $relation_ids, $parent_posts, $progress_type );
    $complete_count = is_array( $relation_ids ) ? count( $relation_ids ) : 0;

    $progress_data['complete_count'] = $complete_count;
	$progress_data['progress_count'] = $progress_count;

    if( is_bool( $progress_count ) && $progress_count === true ) {

        if( isset( $_GET['parent_posts'] ) ) {

            if( ! empty( $parent_posts ) ) {
                $parent_posts = $parent_posts.'-';
            }
            
            $parent_posts = $parent_posts.$_GET['parent_posts'];
        }

        $is_completed = exms_is_post_completed( $post_id, $parent_posts, get_current_user_id() );
        
        if( $is_completed ) {

            $progress_data['complete_count'] = 100;
            $progress_data['progress_count'] = 100;   
        }
    }

    if( empty( $relation_ids ) ) {
        if( empty( $parent_posts ) ) {
            $parent_posts = isset( $_GET['parent_posts'] ) ? $_GET['parent_posts'] : '';
        }

        $is_completed = exms_is_post_completed( $post_id, $parent_posts, get_current_user_id() );
        if( $is_completed ) {

            $progress_data['complete_count'] = 100;
            $progress_data['progress_count'] = 100;   
        }
    }

	return $progress_data;
}

/**
 * Get post coming structures
 * 
 * @param $post_id
 */
function exms_post_serialize_str( $post_id ) {

    $target_come = [];
    if( isset( $_GET['parent_posts'] ) ) {
        $target_come = array_map( 'intval', array_reverse( explode( '-', $_GET['parent_posts'] ) ) );
        array_push( $target_come, $post_id );
    } else {
        array_push( $target_come, $post_id );
    }
    $titles = [];
    foreach( $target_come as $target_id ) {
        $titles[] = get_the_title( $target_id );
    }

    $structures = implode( ' > ', $titles );
    return $structures;
}

/**
 * Check if quiz complete with parent
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $parent_posts
 */
function exms_is_quiz_complete_with_parents( $user_id, $quiz_id, $parent_posts ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_quiz_result_table_name();

    $is_completed = false;

    $quiz_meta = $wpdb->get_results( " SELECT passed
    FROM $table_name 
    WHERE quiz_id = $quiz_id 
    AND user_id = $user_id
    AND parent_posts = '$parent_posts' 
    ORDER BY id DESC LIMIT 1 " ); 

    if( ! empty( $quiz_meta ) && ! is_null( $quiz_meta ) ) {
        $is_completed = true;
    }

    return $is_completed;
}

/**
 * Get post first parent ID using post id
 * 
 * @param $post_id
 */
function exms_get_top_parent_id() {

    $parent_posts = isset( $_GET['parent_posts'] ) ? $_GET['parent_posts'] : '';
    if( empty( $parent_posts ) ) {
        return false;
    }

    $top_parent_id = 0;
    $parents = array_map( 'intval', explode( '-', $parent_posts ) );
    if( ! empty( $parents ) && is_array( $parents ) ) {

        $top_parent_id = end( $parents );
    }
    
    return $top_parent_id;
}

/**
 * Get post assign/unassign to current post html
 * ( Current Relations )
 * 
 * @param $post
 * @param $assign_post_type
 */
function exms_current_assign_post_html( $post, $assign_post_type ) {

    global $wpdb;
    $post_id   = intval( $post->ID );
    $post_type = get_post_type( $post_id );
    $table_name = $wpdb->prefix . 'exms_quiz_questions';
    $posts_table = $wpdb->prefix . 'posts';

    $paged = isset($_GET['page'] ) ? max(1, intval( $_GET['page'] ) ) : 1;
    $unassign_paged = isset( $_GET['unassign_paged'] ) ? max(  1, intval($_GET['unassign_paged'] ) ) : 1;
    $per_page = 6;

    $offset = ( $paged - 1 ) * $per_page;
    $assigned_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT question_id FROM $table_name WHERE quiz_id = %d AND status = %s LIMIT %d OFFSET %d",
            $post_id,
            'active',
            $per_page,
            $offset
        )
    );
    
    $assigned_total = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE quiz_id = %d AND status = %s",
        $post_id,
        'active'
    ) );
        
    $assigned_total_pages = ceil( $assigned_total / $per_page );
    $unassign_offset = ( $unassign_paged - 1 ) * $per_page;

    $query = "
        SELECT DISTINCT p.*
        FROM $posts_table p
        LEFT JOIN $table_name qq
            ON qq.question_id = p.ID
            AND qq.quiz_id = %d
            AND qq.status = 'active'
        WHERE p.post_type = %s
        AND p.post_status = 'publish'
        AND qq.question_id IS NULL
        LIMIT %d OFFSET %d
    ";

    $prepared_query = $wpdb->prepare( $query, $post_id, 'exms-questions', $per_page, $unassign_offset );
    $unassigned_query = $wpdb->get_results( $prepared_query );
    $unassigned_query = array_column( $unassigned_query, 'ID' );

    $count_query = "
        SELECT COUNT(DISTINCT p.ID)
        FROM $posts_table p
        LEFT JOIN $table_name qq
            ON qq.question_id = p.ID
            AND qq.quiz_id = %d
            AND qq.status = 'active'
        WHERE p.post_type = %s
        AND qq.question_id IS NULL
    ";
    $total_count = $wpdb->get_var( $wpdb->prepare( $count_query, $post_id, 'exms-questions' ) );
    $total_unassign_pages = ceil( $total_count / $per_page );

    $next_page_query = new WP_Query( array_merge( $unassigned_query, [ 'paged' => 2 ] ) );
    $post_count      = $next_page_query->have_posts() ? $next_page_query->post_count : 0;

    $total_posts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $posts_table WHERE post_type = %s AND post_status = 'publish'",
        $assign_post_type
    ) );
    
    $existing_labels = Exms_Core_Functions::get_options('labels');
    $question_singular = '';
    if ( is_array( $existing_labels ) && array_key_exists( 'exms_questions', $existing_labels ) ) {
        $question_singular = $existing_labels['exms_questions'];
    }

    ?>
    <div class="exms-sortable-box-wrap">

        <div class="exms-sortable-lists exms-assign-box-left exms-sortable-lists-current" data-relation="current">
            
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot yellow"></span>
                        <span><?php echo __( 'Unassign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $question_singular ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="question" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                                data-users='<?php echo esc_attr( wp_json_encode( $unassigned_query ) ); ?>'
                                data-type="assigned">
                                <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $question_singular ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Search input html -->
                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $assign_post_type, 'un-assigned', $question_singular ); ?>
                <!-- /Search input html -->

                <!-- Sortable un-assign html -->
                <?php echo EXMS_PR_Fn::exms_sortable_unassign_html( $unassigned_query, $assign_post_type, 'current', 'question', $assign_post_type, "", $total_posts ); ?>
                <!-- Sortable un-assign html -->

            </div>
                <?php echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $total_unassign_pages, $post_id, $unassign_paged, $assign_post_type ); ?>

        </div>
        <div class="exms-sortable-lists exms-assign-box-right exms-sortable-lists-current" data-relation="current">
                
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot green"></span>
                        <span><?php echo __( 'Assign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $question_singular ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="question" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                                data-users='<?php echo esc_attr( wp_json_encode( $unassigned_query ) ); ?>'
                                data-type="unassigned">
                                <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $question_singular ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php 
                $relation_ids = EXMS_PR_Fn::exms_current_assigned_post_ids( $post_type, $assign_post_type, $post_id, 0 );
                ?>

                <!-- Search input html -->
                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $assign_post_type, 'assigned', $question_singular ); ?>
                <!-- /Search input html -->

                <!-- Sortable un-assign html -->
                <?php echo EXMS_PR_Fn::exms_sortable_assign_html( $assigned_ids, $assign_post_type, 'current', 'question', $assign_post_type, "", $total_posts ); ?>
                <!-- Sortable un-assign html -->

            </div>

            <!-- Sortable pagination html -->
             <?php echo EXMS_PR_Fn::exms_sortable_assign_pagination_html($assigned_total_pages, $post_id,$paged, $assign_post_type ); ?>
            <!-- /Sortable pagination html -->

        </div>
        <div class="exms-clear-both"></div>
    </div>
    <?php 
}

/**
 * Get post assign/unassign to current post html
 * ( Current Relations )
 * 
 * @param $post
 * @param $assign_post_type
 */
function exms_parent_assign_post_html( $post, $parent_post_type ) {
    
    global $wpdb;

    $post_id   = $post->ID;
    $post_type = get_post_type( $post_id );
    $table_name = $wpdb->prefix . 'exms_quiz_questions';
    $paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    $unassign_paged = isset($_GET['unassign_paged']) ? max(1, intval($_GET['unassign_paged'])) : 1;
    $per_page = 6;
    $offset = ($paged - 1) * $per_page;
    $unassign_offset = ($unassign_paged - 1) * $per_page;

    $post_id = intval($post_id);
    $status  = esc_sql('active');
    $query = "
        SELECT quiz_id
        FROM $table_name
        WHERE question_id = $post_id AND status = '$status'
        LIMIT $per_page OFFSET $offset
    ";

    $assigned_ids = $wpdb->get_col( $query );

    $total = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE question_id = %d AND status = %s",
        $post_id,
        'active'
    ) );

    $total_pages = ceil( $total / $per_page );

    if ( ! is_array( $assigned_ids ) ) {
        $assigned_ids = [];
    }

    $posts_table = $wpdb->prefix . 'posts';

        $query = "
        SELECT DISTINCT p.*
        FROM $posts_table p
        LEFT JOIN $table_name qq
            ON qq.quiz_id = p.ID
            AND qq.question_id = %d
        WHERE p.post_type = %s
            AND p.post_status = 'publish'
        AND (qq.status IS NULL OR qq.status != %s)
        LIMIT %d OFFSET %d
    ";

    $prepared_query = $wpdb->prepare($query, $post_id, 'exms-quizzes', 'active', $per_page, $offset );
    $unassigned_query = $wpdb->get_results( $prepared_query );
    $unassigned_query = array_column( $unassigned_query, 'ID' );

    $count_query = "
        SELECT COUNT(DISTINCT p.ID)
        FROM $posts_table p
        LEFT JOIN $table_name qq ON qq.quiz_id = p.ID
            AND qq.question_id = %d
        WHERE p.post_type = %s
        AND p.post_status = 'publish'
        AND (qq.status IS NULL OR qq.status != %s)
    ";
    $total_count = $wpdb->get_var( $wpdb->prepare( $count_query, $post_id, 'exms-quizzes', 'active' ) );
    $total_unassign_pages = ceil( $total_count / $per_page );

    $next_page_query = new WP_Query( array_merge( $unassigned_query, [ 'paged' => 2 ] ) );
    $post_count      = $next_page_query->have_posts() ? $next_page_query->post_count : 0;
    $total_posts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $posts_table WHERE post_type = %s AND post_status = 'publish'",
        $parent_post_type
    ) );

    $existing_labels = Exms_Core_Functions::get_options('labels');
    $quiz_singular = '';
    if ( is_array( $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
        $quiz_singular = $existing_labels['exms_quizzes'];
    }

    ?>
    <div class="exms-sortable-box-wrap">

        <!-- Unassigned (Left) -->
        <div class="exms-sortable-lists exms-assign-box-left exms-sortable-lists-parent" data-relation="parent">
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot yellow"></span>
                        <span><?php echo __( 'Unassign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $quiz_singular ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="quizzes" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                                data-users='<?php echo esc_attr( wp_json_encode( $unassigned_query ) ); ?>'
                                data-type="assigned">
                                <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $quiz_singular ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $parent_post_type, 'un-assigned', 'true', $quiz_singular ); ?>

                <?php echo EXMS_PR_Fn::exms_sortable_unassign_html( $unassigned_query, $parent_post_type, 'parent', 'quizzes', $parent_post_type, "", $total_posts ); ?>

            </div>
            <?php echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $total_unassign_pages, $post_id, $unassign_paged, $parent_post_type ); ?>
        </div>

        <!-- Assigned (Right) -->
        <div class="exms-sortable-lists exms-assign-box-right exms-sortable-lists-parent" data-relation="parent">
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot green"></span>
                        <span><?php echo __( 'Assign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $quiz_singular ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="quizzes" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                                data-users='<?php echo esc_attr( wp_json_encode( $unassigned_query ) ); ?>'
                                data-type="unassigned">
                                <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $quiz_singular ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $parent_post_type, 'assigned', 'true', $quiz_singular ); ?>

                <?php echo EXMS_PR_Fn::exms_sortable_assign_html( $assigned_ids, $parent_post_type, 'parent', 'quizzes', $parent_post_type, "", $total_posts ); ?>

            </div>

            <?php echo EXMS_PR_Fn::exms_sortable_assign_pagination_html($total_pages, $post_id,$paged, $parent_post_type ); ?>
        </div>

        <div class="exms-clear-both"></div>
    </div>
    <?php
}

/**
 * Get post assign/unassign to current post html
 * ( Current Relations )
 * 
 * @param $post
 * @param $parent_post_type
 */
function exms_parent_post_assign_html( $post, $parent_post_type ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_relation_table_name();
    $posts_table = $wpdb->prefix . 'posts';
    $post_id = $post->ID;
    $post_type = get_post_type( $post_id );
    $per_page = 6;
    $paged        = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
    $unassign_paged = isset($_GET['unassign_paged']) ? max(1, intval($_GET['unassign_paged'])) : 1;
    $offset       = ( $paged - 1 ) * $per_page;
    $unassign_offset = ($unassign_paged - 1) * $per_page;
    $post_types = EXMS_Setup_Functions::get_setup_post_types();
    if ( ! $post_types || ! is_array( $post_types ) ) {
        return;
    }
    $label = "";
    foreach ( $post_types as $slug => $data ) {
        $update_slug = str_replace( 'exms_', 'exms-', $slug );
        if ( $update_slug === $parent_post_type ) {
            $label = $data['singular_name'] ?? $slug;
        }
    }
    

    $assigned_ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT child_post_id FROM $table_name WHERE parent_post_id = %d AND assigned_post_type = %s LIMIT %d OFFSET %d",
        $post_id,
        $parent_post_type,
        $per_page,
        $offset
    ) );

    $total_items = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE parent_post_id = %d AND assigned_post_type = %s",
        $post_id, $parent_post_type
    ) );
    $total_pages = ceil( $total_items / $per_page ); 
    
    if ( ! is_array( $assigned_ids ) ) {
        $assigned_ids = [];
    }
    
    $count_query = $wpdb->prepare("
        SELECT COUNT(DISTINCT p.ID)
        FROM $posts_table p
        LEFT JOIN $table_name qq 
            ON qq.child_post_id = p.ID 
            AND qq.parent_post_id = %d 
            AND qq.assigned_post_type = %s
        WHERE p.post_type = %s 
            AND p.post_status = 'publish'
            AND qq.child_post_id IS NULL
    ", $post_id, $parent_post_type, $parent_post_type);

    $total_unassign_items = $wpdb->get_var($count_query);
    $total_unassign_pages = ceil($total_unassign_items / $per_page);

    $query = "
        SELECT DISTINCT p.ID, p.post_title
        FROM $posts_table p
        LEFT JOIN $table_name qq 
            ON qq.child_post_id = p.ID 
            AND qq.parent_post_id = %d 
            AND qq.assigned_post_type = %s
        WHERE p.post_type = %s 
            AND p.post_status = 'publish'
            AND qq.child_post_id IS NULL
        LIMIT %d OFFSET %d
    ";

    $prepared_query = $wpdb->prepare( $query, $post_id,$parent_post_type, $parent_post_type, $per_page, $unassign_offset );
    $unassigned_query = $wpdb->get_results( $prepared_query );
    $unassigned_query = array_column( $unassigned_query, 'ID' );

    $assigned_query = new WP_Query([
        'post_type'      => $parent_post_type,
        'post_status'    => 'publish',
        'post__in'       => !empty($assigned_ids) ? $assigned_ids : [0],
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC'
    ]);
    $assigned_post_ids = wp_list_pluck( $assigned_query->posts, 'ID' );

    $total_posts = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM $posts_table WHERE post_type = %s AND post_status = 'publish'",
        $parent_post_type
    ) );

    ?>
    <div class="exms-sortable-box-wrap">

        <div class="exms-sortable-lists exms-assign-box-left exms-sortable-lists-parent" data-relation="parent">
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot yellow"></span>
                        <span><?php echo __( 'Unassign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $label ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="post" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $unassigned_query ) ); ?>'
                        data-type="assigned">
                                <?php echo __( 'Assign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $label ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $parent_post_type, 'un-assigned', 'true', $label ); ?>
                <?php echo EXMS_PR_Fn::exms_sortable_unassign_html( $unassigned_query, $parent_post_type, 'parent', 'post', $parent_post_type, 'post-relation', $total_posts ); ?>
            </div>
            <?php echo EXMS_PR_Fn::exms_sortable_unassign_pagination_html( $total_unassign_pages, $post_id, $unassign_paged, $parent_post_type, 'post-relation' ); ?>
        </div>

        <div class="exms-sortable-lists exms-assign-box-right exms-sortable-lists-parent" data-relation="parent">
            <div class="exms-input-sortable-wrap">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot green"></span>
                        <span><?php echo __( 'Assign ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $label ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn" data-target="post" data-post-id="<?php echo esc_attr( $post_id ); ?>"
                        data-users='<?php echo esc_attr( wp_json_encode( $assigned_query ) ); ?>'
                        data-type="unassigned">
                                <?php echo __( 'Unassign All ', 'wp_exams' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], '', $label ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo EXMS_PR_Fn::exms_search_input_html( $post_id, $post_type, $parent_post_type, 'assigned', 'true', $label ); ?>
                <?php echo EXMS_PR_Fn::exms_sortable_assign_html( $assigned_post_ids, $parent_post_type, 'parent', 'post', $parent_post_type, 'post-relation', $total_posts ); ?>
            </div>
            <?php echo EXMS_PR_Fn::exms_sortable_assign_pagination_html($total_pages, $post_id,$paged, $parent_post_type, 'post-relation' ); ?>

        </div>
        <div class="exms-clear-both"></div>
    </div>
    <?php
}


