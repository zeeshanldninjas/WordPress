<?php

/**
 * Template for Post Relation functions
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_PR_Fn {

    /**
     * @var self
     */
    private static $instance;

    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_PR_Fn ) ) {

            self::$instance = new EXMS_PR_Fn;

            global $wpdb;
            self::$wpdb = $wpdb;
        }

        return self::$instance;
    }

    /**
     * Get assign quizzes table name
     */
    public static function exms_user_post_relations_table() {

        return self::$wpdb->prefix.'exms_user_post_relations';
    }
    
    /**
     * Get assign quizzes table name
     */
    public static function exms_user_post_table() {

        return self::$wpdb->prefix.'exms_user_enrollments';
    }

    /**
     * Get post relation table name
     */
    public static function exms_relation_table_name() {

        return self::$wpdb->prefix.'exms_post_relationship';
    }

    /**
     * Get post completion table name
     */
    public static function exms_completions_table_name() {

        return self::$wpdb->prefix.'exms_post_completions';
    } 

    /**
     * Get quiz result table name
     */
    public static function exms_quiz_result_table_name() {

        return self::$wpdb->prefix.'exms_quizzes_results';
    } 

    /**
     * Get parent post type
     */
    public static function exms_get_parent_post_type() {

        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        $parent_post = '';
        if( ! empty( $post_types ) && is_array( $post_types ) ) {

            $array_reverse = array_reverse( $post_types );
            $parent_posts = end( $array_reverse );
            $parent_post = isset( $parent_posts['post_type_name'] ) ? $parent_posts['post_type_name'] : '';
        }

        return $parent_post;
    }

    /**
     * Get parent post type
     */
    public static function exms_get_parent_post_type_singular_name() {

        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        $parent_post = '';
        if( ! empty( $post_types ) && is_array( $post_types ) ) {

            $array_reverse = array_reverse( $post_types );
            $parent_posts = end( $array_reverse );
            $parent_post = isset( $parent_posts['singular_name'] ) ? $parent_posts['singular_name'] : '';
        }

        return $parent_post;
    }

    /**
     * Insert in quizzes assign db table
     * 
     * @param $quiz_id, $user_id, $time
     */
    public static function exms_insert_user_assign_post( $post_id, $user_id, $time ) {

        $table_name = self::exms_user_post_relations_table();
        $post_type = get_post_type( $post_id );

        self::$wpdb->insert( $table_name, [
            'post_id'       => $post_id,
            'post_type'     => $post_type,
            'user_id'       => $user_id,
            'time'          => $time
        ] );

        /**
         * Fires after user assign to the post
         * 
         * @param $user_id
         * @param $post_id
         * @param $time
         * @param true ( means user assign in the post )
         */
        do_action( 'exms_assign_user_on_post', $user_id, $post_id, $time, true );
    }

    /**
     * Enrolls a user to a course, lesson, or any post type.
     *
     * This function inserts a new record into the custom user-post relationship table,
     * only if the user is not already enrolled in the specified post.
     *
     * @param int    $user_id          The ID of the user to enroll.
     * @param int    $post_id          The ID of the post (e.g., course or lesson).
     * @param string $post_type        The type of the post (e.g., 'course', 'lesson').
     * @param int    $enrolled_by      The ID of the user who performed the enrollment (admin, manager, etc.).
     * @param string $status           Enrollment status. Default is 'enrolled'.
     * @param int    $progress_percent Progress percentage. Default is 0.
     * @param string $start_date       Optional start date. If not provided, current time is used.
     * @param string $end_date         Optional end date. Default is empty.
     *
     * @return bool Returns true if the user is enrolled successfully, false if already enrolled.
     */
    public static function exms_enroll_user_to_content( $user_id, $post_id, $post_type, $enrolled_by, $status = 'enrolled', $progress_percent = 0, $start_date = '', $end_date = '' ) {

        global $wpdb;

        $table_name = self::exms_user_post_table();

        $created_timestamp = current_time( 'timestamp' );
        $updated_timestamp = current_time( 'timestamp' );

        $type = '';
        $user = get_userdata( $user_id );
        if( $user && ! empty( $user->roles ) ) {
            $roles = (array) $user->roles;

            if( in_array( 'exms_student', $roles, true ) ) {
                $type = 'student';
            } elseif( in_array( 'exms_intructor', $roles, true ) || in_array( 'exms_instructor', $roles, true ) ) {
                $type = 'instructor';
            } elseif( in_array( 'exms_group_leader', $roles, true ) ) {
                $type = 'leader';
            }
        }

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND post_id = %d AND post_type = %s",
                $user_id,
                $post_id,
                $post_type
            )
        );

        if ( $exists ) {
            return false; 
        }

        $start_date = $start_date ? $start_date : current_time( 'timestamp' );

        $wpdb->insert(
            $table_name,
            [
                'user_id'           => $user_id,
                'post_id'           => $post_id,
                'post_type'         => $post_type,
                'enrolled_by'       => $enrolled_by,
                'status'            => $status,
                'progress_percent'  => $progress_percent,
                'start_date'        => $start_date,
                'end_date'          => $end_date,
                'type'              => $type,
                'created_timestamp' => $created_timestamp,
                'updated_timestamp' => $updated_timestamp,
            ],
            [
                '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s'
            ]
        );

        return true;
    }

    /**
     * Enrolls a user to a course, lesson, or any post type.
     *
     * This function inserts a new record into the custom user-post relationship table,
     * only if the user is not already enrolled in the specified post.
     *
     * @param int    $user_id          The ID of the user to enroll.
     * @param int    $post_id          The ID of the post (e.g., course or lesson).
     * @param string $post_type        The type of the post (e.g., 'course', 'lesson').
     * @param int    $enrolled_by      The ID of the user who performed the enrollment (admin, manager, etc.).
     * @param string $status           Enrollment status. Default is 'enrolled'.
     * @param int    $progress_percent Progress percentage. Default is 0.
     * @param string $start_date       Optional start date. If not provided, current time is used.
     * @param string $end_date         Optional end date. Default is empty.
     *
     * @return bool Returns true if the user is enrolled successfully, false if already enrolled.
     */ 
    public static function exms_unenroll_user_from_content( $user_id, $post_id, $post_type ) {

        global $wpdb;

        $table_name = self::exms_user_post_table();

        $wpdb->delete(
            $table_name,
            [
                'user_id'   => $user_id,
                'post_id'   => $post_id,
                'post_type' => $post_type,
            ],
            [
                '%d', '%d', '%s'
            ]
        );

        return true;
    }
    
    /**
     * Insert in post relation db table
     * 
     * @param $post_id, $post_type, $relation_id, $relation_type, $time, $order
     */
    public static function exms_insert_post_relation( $post_id, $post_type, $relation_id, $relation_type, $time, $order ) {

        if( empty( $relation_type ) ) {
            return false;
        }
        global $wpdb;

        $table_name = $wpdb->prefix . 'exms_quiz_questions';

        self::$wpdb->insert( $table_name, [
            'quiz_id'           => $post_id,
            'question_id'       => $relation_id,
        ] );
    }

    /**
     * HTML for post relation heading
     * 
     * @param $post_type
     */
    public static function get_post_relation_heading( $post_type ) {

        ob_start();

        ?>
        <div class="exms-assign-heading-wrap">
            <!-- Unassign Heading -->
            <div class="exms-assign-heading-left">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot yellow"></span>
                        <span><?php echo __( 'Unassign ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="unassign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-unassign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn">
                                <?php echo __( 'Assign All ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Heading -->
            <div class="exms-assign-heading-right">
                <div class="exms-header">
                    <div class="exms-title">
                        <span class="exms-status-dot green"></span>
                        <span><?php echo __( 'Assigned ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) ); ?></span>
                    </div>
                    <div class="exms-actions">
                        <span class="exms-dots exms-dots-toggle" data-type="assign">⋯</span>
                        <div class="exms-dropdown-menu exms-dropdown-assign" style="display: none;">
                            <button type="button" class="exms-dropdown-btn">
                                <?php echo __( 'Unassign All ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="exms-clear-both"></div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Get Search input html content
     * 
     * @param $post_id, $post_type, $search_post_type, $data_type
     */
    public static function exms_search_input_html( $post_id, $post_type, $search_post_type, $data_type, $relations = '', $post_type_labels = "" ) {

        ob_start();

        ?>
        <input type="text" name="" class="exms-post-search-input" placeholder="<?php echo __( 'Search All ', 'exms' ) . ( $post_type_labels !== '' ? $post_type_labels : ucwords( str_replace( [ 'exms-', 'exms_' ], '', $search_post_type ) ) ); ?>" 
        data-current-post-type="<?php echo $post_type; ?>" 
        data-search-post-type="<?php echo $search_post_type; ?>" 
        data-type="<?php echo $data_type; ?>" 
        data-post-id="<?php echo $post_id; ?>"
        data-parent-relation="<?php echo $relations; ?>">
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Sortable un assign html content
     * 
     * @param $post_ids, $post_type, $name_key
     */
    public static function exms_sortable_unassign_html( $post_ids, $post_type, $name_key,$target, $post_type_name = '', $name = '', $total_post = "" ) {
        if ( $name === 'post-relation' ) {
            $class = 'exms-post-sortable-items-wrap-left exms-post-sortable-pagination-wrap-left';
        } else {
            $class = 'exms-sortable-items-wrap-left exms-sortable-pagination-wrap-left';
        }
        
        ob_start();
        ?>
        <div class="exms-sortable-items-wrap <?php echo $class; ?>" data-post-type="<?php echo $post_type; ?>" data-name="exms_unassign_items">
                
            <?php 
            if( is_array( $post_ids ) && !empty( $post_ids ) && !empty($total_post) ) {
                foreach( $post_ids as $post_id ) {

                    echo self::exms_sortable_item_html( $post_id, $post_type, 'exms_unassign_items', $name_key, 'left', $target ); 
                }
            } else {
                
                $update_post_type = str_replace( 'exms_', 'exms-', $post_type );
                $create_link = admin_url( 'post-new.php?post_type=' . $update_post_type );
                $pretty_name = ucwords( str_replace( [ 'exms-', 'exms_' ], '', $post_type ) );
                ?>
                <div class="exms-post-not-found">
                    <?php echo __( 'No ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type_name ) ) . __( ' Unassigned.', 'exms' ); 
                    if( $total_post == 0 ) {
                    ?>
                    <a href="<?php echo esc_url( $create_link ); ?>" target="_blank">
                        <?php echo sprintf( esc_html__( 'Create New %s', 'exms' ), $pretty_name ); ?>
                    </a>
                    <?php
                    } ?>
                </div>
                <?php
            }
            ?>

        </div>

        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Sortable assign html content
     * 
     * @param $post_ids, $post_type, $name_key
     */
    public static function exms_sortable_assign_html( $post_ids, $post_type, $name_key, $target, $post_type_name = '' , $name = '', $total_post = "" ) {
        if ( $name === 'post-relation' ) {
            $class = 'exms-post-sortable-items-wrap-right exms-post-sortable-pagination-wrap-right';
        } else {
            $class = 'exms-sortable-items-wrap-right exms-sortable-pagination-wrap-right';
        }
        ob_start();

        ?>
        <div class="exms-post-sortable-items-wrap-right exms-sortable-items-wrap <?php echo $class; ?>" data-post-type="<?php echo $post_type; ?>" data-name="exms_assign_items">

            <?php 
            if( $post_ids && is_array( $post_ids ) && !empty($total_post) ) {
                foreach( $post_ids as $order => $post_id ) {

                    echo self::exms_sortable_item_html( $post_id, $post_type, 'exms_assign_items', $name_key, 'right', $target );
                }
            } else {
                
                $update_post_type = str_replace( 'exms_', 'exms-', $post_type );
                $create_link = admin_url( 'post-new.php?post_type=' . $update_post_type );
                $pretty_name = ucwords( str_replace( [ 'exms-', 'exms_' ], '', $post_type ) );
                ?>
                <div class="exms-post-not-found">
                    <?php echo __( 'No ', 'exms' ) . ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type_name ) ) . __( ' Assigned.', 'exms' ); 
                    if( $total_post == 0 ) {
                    ?>
                    <a href="<?php echo esc_url( $create_link ); ?>" target="_blank">
                        <?php echo sprintf( esc_html__( 'Create New %s', 'exms' ), $pretty_name ); ?>
                    </a>
                    <?php
                    } ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Sortable items html content
     * 
     * @param $post_id, $post_type, $name, $name_key
     */
    public static function exms_sortable_item_html( $post_id, $post_type, $name, $name_key, $icon, $target ) {
        ob_start();
        ?>
        <div class="exms-sortable-item"
             data-name="<?php echo esc_attr($name); ?>" 
             data-name-key="<?php echo esc_attr($name_key); ?>">
             <?php
            $icon_right_html = '<button class="exms-drag-icon" data-name="' . esc_attr($name) . '" data-target="' . esc_attr( $target ) . '"><span>&#8594;</span></button>';
            $icon_left_html = '<button class="exms-drag-icon" data-name="' . esc_attr($name) . '" data-target="' . esc_attr( $target ) . '"><span>&#8592;</span></button>';
            ?>
            <span class="exms-post-title">
                <?php
                if( $icon == 'right' ) {
                    echo $icon_left_html;
                }
                ?>
                <a href="<?php echo esc_url(get_permalink( $post_id )); ?>" target="_blank" class="exms-sortable-ui-link">
                <?php echo esc_html(get_the_title( $post_id )); ?>
                </a>
            </span>
            <?php
            if( $icon == 'left' ) {
                echo $icon_right_html;
            }
            ?>
            <!-- Actual input with proper name attribute -->
            <input type="hidden" 
                   name="<?php echo $name; ?>[<?php echo esc_attr($name_key); ?>][]" 
                   class="exms-assign-unassign-id" 
                   value="<?php echo esc_attr($post_id); ?>">
                   
            <input type="hidden" 
                   name="exms_<?php echo esc_attr($name_key); ?>_relation" 
                   value="<?php echo esc_attr($post_type); ?>">
        </div>
        <?php
        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }
    

    /**
     * EXMS get query post
     * 
     * @param $post_type, $post_not_in
     */
    public static function exms_get_query_posts( $post_type, $paged = '' ) {

        $query_post = [
            'post_type'     => $post_type, 
            'post_status'   => 'publish',
            'order'         => 'ASC',
            'posts_per_page'=> 10
        ];

        //var_dump( $post_type);
        
        if( ! empty( $post_not_in ) ) {
            $query_post['post__not_in'] = $post_not_in;
        }
        
        if( ! empty( $paged ) ) {
            $query_post['paged'] = $paged;   
        }
        
        $query_post = new WP_Query( $query_post );
        //var_dump( $query_post);

        return $query_post;
    }

    /**
     * Sortable unassign pagination html content
     * 
     * @param int $total_pages
     * @param int $post_id
     * @param int $current_page (optional)
     */
    public static function exms_sortable_unassign_pagination_html( $total_pages, $post_id, $current_page = 1, $post_type = "", $user = '' ) {
        if ( $user === 'user' ) {
            $class = 'exms-user-sortable-pagination-wrap-left exms-parent-user-sortable-pagination-wrap-left';
        } elseif ( $user === 'post-relation' ) {
            $class = 'exms-post-sortable-pagination-wrap-left';
        } elseif ( $user === 'group-user' ) {
            $class = 'exms-group-user-sortable-pagination-wrap-left';
        } else {
            $class = 'exms-sortable-pagination-wrap-left';
        }

        if( $total_pages > 0 ) {
        ob_start();
        ?>
        <div class="exms-sortable-pagination-wrap <?php echo $class; ?>" data-post-type="<?php echo $post_type; ?>" data-user='<?php echo $user; ?>' data-post-id='<?php echo $post_id; ?>'>

            <div class="exms-sortable-paginate exms-sortable-back" data-value="back">
                <div class="exms-icon-button">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <span class="exms-label"><?php _e( 'Back', 'exms' ); ?></span>
            </div>
            <div class="exms-sortable-pages">
                <span class="exms-start-page"><?php echo $current_page; ?></span>
                <span class="exms-of-text"><?php echo __( 'of', 'exms' ); ?></span>
                <span class="exms-total-page"><?php echo $total_pages == 0 ? 1 : $total_pages ?></span>
            </div>
            <div class="exms-sortable-paginate exms-sortable-next" data-value="next">
                <span class="exms-label"><?php _e( 'Next', 'exms' ); ?></span>
                <div class="exms-icon-button">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </div>
            </div>

        </div>
        <?php
        $content = ob_get_clean();
        return $content;
        }
    }

    /**
     * Sortable assign pagination html content
     * 
     * @param int $total_pages
     * @param int $post_id
     * @param int $current_page (optional)
     */
    public static function exms_sortable_assign_pagination_html( $total_pages, $post_id, $current_page = 1, $post_type = "", $user = '' ) {
        if ( $user === 'user' ) {
            $class = 'exms-user-sortable-pagination-wrap-right exms-parent-user-sortable-pagination-wrap-right';
        } elseif ( $user === 'post-relation' ) {
            $class = 'exms-post-sortable-pagination-wrap-right';
        } elseif ( $user === 'group-user' ) {
            $class = 'exms-group-user-sortable-pagination-wrap-right';
        } else {
            $class = 'exms-sortable-pagination-wrap-right';
        }

        if( $total_pages > 0 ) {
        ob_start();
        ?>
        <div class="exms-sortable-pagination-wrap <?php echo $class; ?>" data-post-type="<?php echo $post_type; ?>" data-user='<?php echo $user; ?>' data-post-id='<?php echo $post_id; ?>'>

            <div class="exms-sortable-paginate exms-sortable-back" data-value="back">
                <div class="exms-icon-button">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </div>
                <span class="exms-label"><?php _e( 'Back', 'exms' ); ?></span>
            </div>

            <div class="exms-sortable-pages">
                <span class="exms-start-page"><?php echo $current_page; ?></span>
                <span class="exms-of-text"><?php echo __( 'of', 'exms' ); ?></span>
                <span class="exms-total-page"><?php echo $total_pages == 0 ? 1 : $total_pages ?></span>
            </div>
            <div class="exms-sortable-paginate exms-sortable-next" data-value="next">
                <span class="exms-label"><?php _e( 'Next', 'exms' ); ?></span>
                <div class="exms-icon-button">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </div>
            </div>

        </div>
        <?php
        $content = ob_get_clean();
        return $content;
        }
    }

    /**
     * Sortable pagination html content
     * 
     * @param $style ( optional )
     */
    public static function exms_sortable_pagination_html( $style = '' ) {

        ob_start();

        ?>
        <div class="exms-sortable-pagination-wrap" data-pages="1" <?php echo $style; ?> >

            <div class="exms-sortable-paginate exms-sortable-back" data-value="back">
                <div class="dashicons dashicons-arrow-left-alt2"></div>
                <div><?php _e( 'Back', WP_EXAMS ); ?></div>
            </div>

            <div class="exms-sortable-pages"><?php _e( 'Page 1', WP_EXAMS ); ?></div>

            <div class="exms-sortable-paginate exms-sortable-next" data-value="next">
                <div><?php _e( 'Next', WP_EXAMS ); ?></div>
                <div class="dashicons dashicons-arrow-right-alt2"></div>
            </div>
            
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Get assinged post ids by post type
     * 
     * @param $current_post_type, $post_type, $post_id
     * @param $limits ( optional )
     */
    public static function exms_current_assigned_post_ids( $post_id, $status = '', $paged = 0 ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'exms_quiz_questions';
    $limit_clause = '';

    // Handle pagination
    if ( is_numeric( $paged ) && $paged >= 0 ) {
        $offset = (int) $paged * 10;
        $limit_clause = $wpdb->prepare( 'LIMIT %d, 10', $offset );
    }

    $where = $wpdb->prepare( 'quiz_id = %d', $post_id );

    if ( ! empty( $status ) ) {
        $where .= $wpdb->prepare( ' AND status = %s', $status );
    }

    $query = "SELECT question_id FROM $table_name WHERE $where ORDER BY question_order ASC $limit_clause";

    $results = $wpdb->get_results( $query );

    $question_ids = [];
    if ( ! empty( $results ) ) {
        $question_ids = array_map( 'intval', array_column( $results, 'question_id' ) );
    }

    return $question_ids;
}


    /**
     * Get parent assigned post ids by post types
     * 
     * @param $parent_post_type, $current_post_type, $post_id
     * @param $limits ( optional )
     */
    public static function exms_parent_assigned_post_ids( $parent_post_type, $current_post_type, $post_id, $paged = '' ) {

        $table_name = self::exms_relation_table_name();

        $limit = '';
        if ( ! empty( $paged ) && $paged >= 0 ) {
            $limit = 'LIMIT ' . $paged . ',10';
        }

        $relation_parent_ids = [];
        $relation_meta = self::$wpdb->get_results( "
            SELECT parent_post_id FROM $table_name 
            WHERE parent_post_type = '$parent_post_type' 
            AND assigned_post_type = '$current_post_type'
            AND child_post_id = $post_id
            ORDER BY id ASC $limit
        " );
        
        if ( ! empty( $relation_meta ) ) {
            $relation_parent_ids = array_map( 'intval', array_column( $relation_meta, 'parent_post_id' ) );
        }

        return $relation_parent_ids;
    }


    /**
     * Get child relation ids
     * 
     * @param $post_id, $post_type
     */
    public static function exms_child_relation_ids( $post_id, $post_type ) {

        $table_name = self::exms_relation_table_name();

        $relation_ids = [];
        $relation_meta = self::$wpdb->get_results( "
            SELECT child_post_id FROM $table_name 
            WHERE parent_post_id = $post_id
            AND parent_post_type = '$post_type'
            ORDER BY assigned_post_type ASC
        " );

        if ( ! empty( $relation_meta ) ) {
            $relation_ids = array_map( 'intval', array_column( $relation_meta, 'child_post_id' ) );
        }

        return apply_filters( 'exms_child_relation_ids', $relation_ids );
    }


    /**
     * Get Child post type of current post type
     * 
     * @param $post_types
     * @param $post_type
     */
    public static function exms_get_child_post_type( $post_type, $post_id ) {

        $table_name = self::exms_relation_table_name();
        $child_post_types = [];

        $relation_meta = self::$wpdb->get_results( "
            SELECT assigned_post_type FROM $table_name 
            WHERE parent_post_type = '$post_type'
            AND parent_post_id = $post_id
            GROUP BY assigned_post_type
            ORDER BY id ASC
        " );

        if ( ! empty( $relation_meta ) ) {
            $child_post_types = array_column( $relation_meta, 'assigned_post_type' );
        }

        return apply_filters( 'exms_child_post_type', $child_post_types );
    }


    /**
     * Get child post list count with post types
     * 
     * @param $post_id
     */
    public static function exms_child_post_list_count( $post_id ) {

        $table_name = self::exms_relation_table_name();

        $relations = [];
        $relation_meta = self::$wpdb->get_results( "
            SELECT COUNT(assigned_post_type) as post_count, 
            assigned_post_type as post_name 
            FROM $table_name 
            WHERE parent_post_id = $post_id
            GROUP BY assigned_post_type
        " );

        if ( ! empty( $relation_meta ) ) {
            $relations = $relation_meta;
        }

        return apply_filters( 'exms_child_post_list_count', $relations );
    }


    /**
     * Get child post list count html
     * 
     * @param $relation_id
     */
    public static function exms_child_count_html( $relation_id ) {

        $datas = self::exms_child_post_list_count( $relation_id );

        ob_start();

        if( ! empty( $datas ) && is_array( $datas ) ) {
            ?>
            <div class="exms-child-post-list-counts">
            <?php
            foreach( $datas as $index => $data ) {

                $post_count = isset( $data->post_count ) ? intval( $data->post_count ) : '';
                $post_name = isset( $data->post_name ) ? $data->post_name : '';
                if( ! post_type_exists( $post_name ) ) {
                    continue;
                }

                if( 'exms_questions' == $post_name ) {
                    continue;
                }

                ?>
                <div class="exms-child-post-count">
                    <?php echo $post_count.' '.exms_post_name( $post_name ); ?>
                </div>
                <?php
            }
            ?>
            </div>
            <?php
        }

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }
}

EXMS_PR_Fn::instance();