<?php
/**
* WP_Exam_Shortcodes 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WP_Exam_Shortcodes
 *
 * Base class to define all shortcodes
 */
class WP_Exam_Shortcodes {
	
    private static $instance;

    /**
     * Connect to wpdb
     */
    private static $wpdb;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof WP_Exam_Shortcodes ) ) {

            self::$instance = new WP_Exam_Shortcodes;

            global $wpdb;
            self::$wpdb = $wpdb;

            self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Initialize hooks
	 */
	public function hooks() {
        /**
         * All shortcodes
         */
        // add_shortcode( 'exms_quiz', [ $this, 'exms_shortcode_quiz_display' ] );
        add_shortcode( 'exms_leaderboard', [ $this, 'exms_leaderboard_content' ] );
        add_shortcode( 'exms_instructor', [ $this, 'exms_instructor_content' ] );
        add_shortcode( 'exms_student_dashboard', [ $this, 'exms_user_dashboard' ] );
        add_shortcode( 'exms_structures', [ $this, 'exms_structures' ] );
        add_shortcode( 'my_report_student_details', [ $this, 'exms_report_student_details' ] );
        add_shortcode( 'exms_course_hierarchy', [ $this, 'exms_course_hierarchy_shortcode' ] );

        /**
         * Hooks
         */
        add_filter( 'exms_dashboard_sidebar_links', [ $this, 'exms_dashboard_sidebar_links' ] );
        add_action( 'wp_ajax_exms_ins_paginations', [ $this, 'exms_instructor_table_paginations' ] );
        add_action( 'wp_ajax_exms_search_users', [ $this, 'exms_search_with_users' ] );
        add_action( 'wp_ajax_exms_unassing_user_to_quiz', [ $this, 'exms_exms__user_to_quiz' ] );
        add_filter( 'the_content', [ $this, 'exms_place_student_dashboard'] );
    }

    /**
     * Shortcode: [exms_course_hierarchy course_id="123,456" class=""]
     * Shows Course Title in same EXMS module markup + renders its steps inside.
     * 
     * use user_id parameter if you want display any specific user data
     */
    public function exms_course_hierarchy_shortcode( $atts ) {
        
        global $wpdb;

        $atts = shortcode_atts(
            [
                'course_id'   => '',
                'class'       => '',
                'user_id'     => 0,
                'open_first'  => 'no',
            ],
            $atts,
            'exms_course_hierarchy'
        );

        $user_id = isset( $atts['user_id'] ) ? intval( $atts['user_id'] ) : 0;

        if( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        if( empty( $atts['course_id'] ) ) {

            $course_ids = exms_get_user_enrolled_post_ids( $user_id, 'exms-courses', 'student', 3 );
        }

        if( empty( $course_ids ) ) {
            return __( 'You have not enrolled in any courses yet.', 'exms' );
        }

        $open_first = ( $atts['open_first'] === 'yes' );

        ob_start();
        ?>
        <div class="exms-course-hierarchy-wrapper <?php echo esc_attr( $atts['class'] ); ?>">
            <?php
            $i = 0;

            foreach( $course_ids as $course_id ) {

                if( ! $course_id ) {
                    continue;
                }

                $course_type = get_post_type( $course_id );

                if( ! $course_type ) {
                    continue;
                }

                $top_children = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT child_post_id
                        FROM {$wpdb->prefix}exms_post_relationship
                        WHERE parent_post_id = %d AND parent_post_type = %s",
                        $course_id,
                        $course_type
                    )
                );

                $course_title = get_the_title( $course_id );
                $course_slug  = get_post_field( 'post_name', $course_id );
                $structure   = get_option( 'exms_post_types', true );
                $label       = isset( $structure[ $course_type ]['plural_name'] ) ? $structure[ $course_type ]['plural_name'] : ucfirst( $course_type );
                $icon_label  = strtoupper( substr( $label, 0, 1 ) );

                $is_open     = ( $open_first && $i === 0 );
                $active_cls  = $is_open ? 'exms-active' : '';
                $panel_style = $is_open ? '' : 'style="display:none;"';

                $i++;
                ?>
                <div data-course_id="<?php echo esc_attr( $course_id ); ?>"
                    class="exms-course-module js-exms-course-module exms-course-wrapper <?php echo esc_attr( $active_cls ); ?>"
                    data-post-id="<?php echo esc_attr( $course_id ); ?>">

                    <div class="exms-course-module__header">
                        <div class="exms-legacy-course__lesson-status" style="--percent: 0;"></div>

                        <span class="exms-course-module__icon"><?php echo esc_html( $icon_label ); ?></span>
                        <span class="exms-course-module__title">
                            <?php echo esc_html( $course_title ); ?>
                        </span>
                        <span class="exms-course-module__toggle-icon js-exms-toggle-step" role="button" tabindex="0">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </span>
                    </div>

                    <div class="exms-course-module__lessons js-exms-child-container" <?php echo $panel_style; ?>>
                        <?php
                        foreach ( $top_children as $child_id ) {
                            echo exms_render_step_html( (int) $child_id );
                        }
                        ?>
                    </div>

                </div>
                <?php
            }
            ?>
        </div>
        <?php

        return ob_get_clean();
    }

    public function exms_place_student_dashboard( $content ) {

        if( ! is_page() ) {
            return $content;
        }

        if( strpos( $content, '[exms_student_dashboard' ) !== false ) {
            return $content;
        }

        $general_settings = Exms_Core_Functions::get_options( 'general_settings' );
        $dashboard_page   = isset( $general_settings['dashboard_page'] ) ? (int) $general_settings['dashboard_page'] : 0;

        if( ! $dashboard_page ) {
            return $content;
        }

        if( get_the_ID() !== $dashboard_page ) {
            return $content;
        }
        $dashboard_html = do_shortcode( '[exms_student_dashboard]' );
        return $content . $dashboard_html;
    }


    /**
     * UnAssign user to quiz :Ajax
     */
    public function exms_unassign_user_to_quiz() {

        $response = [];

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        if( empty( $user_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'User ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
        if( empty( $quiz_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Quiz ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

        /**
         * Unenroll user on post
         */
        echo exms_unenroll_user_on_post( $user_id, $quiz_id );
        $content = "";
        $response['content'] = $content;
        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Search with user on intructor table :AJax
     */
    public function exms_search_with_users() {

        global $wpdb;
        $return = [];
        $user_post_relation = EXMS_PR_Fn::exms_user_post_relations_table();

        $search_query = isset( $_GET['q'] ) ? $_GET['q'] : '';
        $get_users = $wpdb->get_results( "SELECT users.ID, users.display_name
        FROM {$wpdb->users} as users INNER JOIN $user_post_relation as u_p_relation
        ON users.ID = u_p_relation.user_id 
        WHERE u_p_relation.post_type = 'exms_quizzes' 
        AND users.user_login LIKE '%{$search_query}%' GROUP BY u_p_relation.user_id LIMIT 5", ARRAY_N );

        if( ! empty( $get_users ) ) {
            $return = $get_users;
        }

        echo json_encode( $return );
        wp_die();
    }

    /**
     * Load more instructor table rows 
     */
    public function exms_instructor_table_paginations() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $paged = isset( $_POST['page'] ) ? $_POST['page'] : 0;
        $limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
        $target = isset( $_POST['target'] ) ? $_POST['target'] : '';

        $quizzes = exms_get_quiz_ids_has_users( $limit, $paged );
        $content = self::exms_inst_table_data( $quizzes );
        
        $response['content'] = $content;
        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Instructor table body html content
     * 
     * @param $quizzes
     */
    public static function exms_inst_table_data( $quizzes ) {

        ob_start();

        foreach( $quizzes as $sno => $quiz_meta ) {

            $sno = $sno + 1;

            $quiz_id = isset( $quiz_meta->post_id ) ? intval( $quiz_meta->post_id ) : 0;
            $user_id = isset( $quiz_meta->user_id ) ? intval( $quiz_meta->user_id ) : 0;
            $enroll_date = isset( $quiz_meta->time ) ? date( 'Y-m-d h:i', intval( $quiz_meta->time ) ) : 0;
            $quiz_name = get_the_title( $quiz_id );   
            $quiz_status = exms_get_user_quiz_status( $user_id, $quiz_id );

            ?>
            <tr class="exms-student-row">
                <td><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $user_id, 'http' ); ?>"><?php echo ucwords( exms_get_user_name( $user_id ) ); ?></a></td>
                <td><a href="<?php echo get_the_permalink( $quiz_id ); ?>" target="_blank"><?php echo ucwords( $quiz_name ); ?></td></a>
                <td><?php echo $quiz_status; ?></td>
                <td><?php echo $enroll_date; ?></td>
                <td><?php echo exms_get_user_quiz_complete_date( $user_id, $quiz_id ); ?></td>
                <td><span class="exms-inst-unenroll" data-quiz-id="<?php echo $quiz_id; ?>" data-user-id="<?php echo $user_id; ?>"><?php echo __( 'Unenroll', 'exms' ); ?></td></span>
            </tr>
            <?php
        }

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Add sidebar links on student dashboard shortcode
     */
    public function exms_dashboard_sidebar_links( $links ) {

        return $links;
    }

    /**
     * Shortcode to display student dashboard
     */
    public function exms_user_dashboard( $atts ) {

        ob_start();
        
        if( ! is_user_logged_in() ) {

            return;
        } elseif( in_array( 'administrator', wp_get_current_user()->roles ) ) {

            return __( 'Only student/instructor can see the dashboard.', 'exms' );
        }
        $atts = $atts ? $atts : [];
        $user_id = isset( $atts['userid'] ) ? $atts['userid'] : get_current_user_id();
        if( in_array( 'exms_instructor', get_userdata( $user_id )->roles ) ) {
            
            $atts['user_type'] = 'instructor';

        } elseif( in_array( 'exms_student', get_userdata( $user_id )->roles ) ) {
            
            $atts['user_type'] = 'student';
        }
        
        if( isset( $atts['user_type'] ) ) {

            $file = '/shortcodes/exms-user-dashboard.php';
            exms_include_template( $file, $atts );   
        }
        return ob_get_clean();
    }

    /**
     * Shortcode to display quiz
     */
    public function exms_shortcode_quiz_display( $atts ) {

        ob_start();
        
        if( ! isset( $atts['id'] ) || empty( $atts['id'] ) ) {

            return ob_get_clean();
        }
        
        $file = '/shortcodes/exms-quiz-shortcode.php';
        exms_include_template( $file, $atts );   
        return ob_get_clean();
    }

    /**
     * Shortcode to display leaderboard
     */
    public function exms_leaderboard_content( $atts ) {

        ob_start();
        
        $file = '/shortcodes/exms-leaderboard-shortcode.php';
        exms_include_template( $file, $atts );
        return ob_get_clean();
    }

    /**
     * Shortcode to display instructor table
     */
    public function exms_instructor_content( $atts ) {

        ob_start();

        $file = '/shortcodes/exms-instructor-shortcode.php';
        exms_include_template( $file, $atts );
        return ob_get_clean();
    }

    /**
     * Shortcode to display structures table
     */
    public function exms_structures($atts) {
        ob_start();

        $file = '/shortcodes/exms-structures-shortcode.php';
        exms_include_template( $file, $atts );
        return ob_get_clean();
    }
    
    /**
     * Shortcode to display structures table
     */
    public function exms_report_student_details($atts) {
        ob_start();

        $file = '/shortcodes/student/exms-student-report-detail-template.php';
        exms_include_template( $file, $atts );
        return ob_get_clean();
    }
}

WP_Exam_Shortcodes::instance();
