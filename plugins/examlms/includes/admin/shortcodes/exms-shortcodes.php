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

        /**
         * Hooks
         */
        add_filter( 'exms_dashboard_sidebar_links', [ $this, 'exms_dashboard_sidebar_links' ] );
        add_action( 'wp_ajax_exms_ins_paginations', [ $this, 'exms_instructor_table_paginations' ] );
        add_action( 'wp_ajax_exms_search_users', [ $this, 'exms_search_with_users' ] );
        add_action( 'wp_ajax_exms_unassing_user_to_quiz', [ $this, 'exms_exms__user_to_quiz' ] );
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
}

WP_Exam_Shortcodes::instance();
