<?php

/**
 * Quiz Shortcode rerenders the quiz page.
 */
class EXMS_QUIZ_Shortcode {
    
    /**
     * Define of instance
     */
    private static $instance = null;

    /**
     * Summary of atts
     */
    private $atts = [];

    /**
      * Define the instance
     */
    public static function instance(): EXMS_QUIZ_Shortcode {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_QUIZ_Shortcode ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     *  Hooks that are used in the class
     */
    private function hooks() {

        add_shortcode( 'exms_quiz', [ $this, 'exms_render_quiz_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_scripts' ] );
        add_action( 'wp_ajax_exms_quiz_submit', [ $this, 'exms_quiz_submit' ] );
        add_action( 'wp_ajax_exms_answer_submit', [ $this, 'exms_answer_submit' ] );
        add_action( 'wp_ajax_exms_quiz_answer_view', [ $this, 'exms_quiz_answer_view' ] );
        add_action( 'wp_ajax_nopriv_exms_quiz_answer_view', [ $this, 'exms_quiz_answer_view' ] );
        add_action( 'wp_ajax_exms_buy_quiz', [ $this, 'exms_buy_quiz' ] );
        add_action( 'wp_ajax_nopriv_exms_buy_quiz', [ $this, 'exms_buy_quiz' ] );
        add_filter( 'the_content', [ $this, 'exms_quiz_content' ] );
        add_action( 'wp_footer', [ $this, 'exms_add_loader' ] );
        add_action( 'wp_ajax_exms_save_quiz_data_to_transient', [ $this, 'exms_save_quiz_data_to_transient' ] );
        add_action( 'exms_quiz_expire_event', [ $this, 'exms_quiz_expire_event' ], 10, 2 );
        add_action( 'wp_ajax_exms_save_question_answer_to_transient', [ $this, 'exms_save_question_answer_to_transient' ] );
        add_action( 'wp_ajax_exms_enroll_as_an_admin', [ $this, 'exms_enroll_as_an_admin' ] );
    }

    /**
     * enroll admin on course / quiz 
     */
    public function exms_enroll_as_an_admin() {

        $response = [];
        
        $user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0;
        $quiz_id = isset( $_POST['quiz_id'] ) ? $_POST['quiz_id'] : 0;

        if( ! $user_id || ! $quiz_id ) {
            wp_die();
        }

        exms_assign_user_into_post( $quiz_id, $user_id );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * save question answer to transient
     */
    public function exms_save_question_answer_to_transient() {

        global $wpdb;

        $user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : 0;
        $question_number = isset( $_POST['question_number'] ) ? intval( $_POST['question_number'] ) + 1 : '';
        $question_id = isset( $_POST['question_id'] ) ? $_POST['question_id'] : 0;
        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
        $question_type = isset( $_POST['question_type'] ) ? $_POST['question_type'] : '';

        if( 'sorting_choice' == $question_type || 'fill_blank' == $question_type || 'multiple_choice' == $question_type ) {
            $user_answer = isset( $_POST['user_answer'] ) ? json_decode( str_replace( '\\', '', $_POST['user_answer'] ) ) : '';
        } elseif( isset( $_FILES['user_answer'] ) && 'file_upload' == $question_type ) {
            $user_answer = $_FILES['user_answer'];
        } else {
            $user_answer = isset( $_POST['user_answer'] ) ? $_POST['user_answer'] : '';
        }

        $is_transient_available = get_transient( 'exms_quiz_data_'.$user_id.'_'.$quiz_id );
        if( $is_transient_available ) {

            $user_quiz_data = [];

            $user_quiz_data['question_answer_'.$question_id] = $user_answer;
            $user_quiz_data['question_number_'.$question_id] = $question_number;
            $user_quiz_data['question_quiz_id_'.$question_id] = $quiz_id;
            $user_quiz_data['question_type_'.$question_id] = $question_type;
            $transient_key = 'exms_quiz_data_' . $user_id . '_' . $quiz_id;
            if( 'exms_quiz_data' == $is_transient_available ) {
                set_transient( $transient_key, $user_quiz_data );
            } else {
                set_transient( $transient_key, $is_transient_available + $user_quiz_data );  
            }
        }
        wp_die();
    }

    /**
     * set crone that runs once the quiz is expire
     */
    public function exms_quiz_expire_event( $user_id, $quiz_id ) {

        global $wpdb;

        $transient_data = get_transient( 'exms_quiz_data_'.$user_id.'_'.$quiz_id );
        $quiz_questions = exms_get_quiz_questions( $quiz_id );
        $total_questions = count( $quiz_questions );

        if( ! empty( $quiz_questions ) && is_array( $quiz_questions ) ) {
            foreach( $quiz_questions as $question_id ) {

                $question_id = intval( $question_id );
                $question_answer = isset( $transient_data['question_answer_'.$question_id] ) ? $transient_data['question_answer_'.$question_id] : '';

                $question_type = isset( $transient_data['question_type_'.$question_id] ) ? $transient_data['question_type_'.$question_id] : '';

                if( ! $question_type ) {
                    $question_type = exms_get_question_type($question_id);
                }

                exms_update_user_answer( $user_id, $quiz_id, $question_answer, intval( $question_id ), $question_type );
            }
        }

        $obtained_points = exms_get_quiz_possible_score( $user_id, $quiz_id );
        $correct_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 1 );
        $wrong_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 0 );
        $not_attempt = exms_get_quiz_answer_count( $user_id, $quiz_id, 'not-attempt' );
        $review_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 'pending' );
        $total_questions = count( exms_get_quiz_questions( $quiz_id ) );
        $percentage = ( $correct_questions / $total_questions ) * 100;
        $percentage = round( $percentage, 2 );
        $rank_data = get_post_meta( $quiz_id, 'exms-quiz-rank', true );
        $attempt_number = exms_get_submmited_quiz_attempt( $user_id, $quiz_id );
        if( $attempt_number ) {
            $attempt_number = $attempt_number + 1;
        } else {
            $attempt_number = 1;
        }
        if( is_array( $rank_data ) && ! empty( $rank_data ) ) {
            $rank_data['quiz_'.$user_id] = $percentage;
        } else {
            $rank_data = [ 'quiz_'.$user_id => $percentage ];
        }

        $time_taken = exms_get_quiz_data( $quiz_id, 'quiz_timer' );
        if( $time_taken ) {

            list($hours, $minutes, $seconds) = explode( ':', $time_taken );
            $time_taken = ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        update_post_meta( $quiz_id, 'exms-quiz-rank', $rank_data);
        
        $quiz_detail_transient = get_transient( 'exms_quiz_parent_detail_'.$user_id.'_'.$quiz_id );
        $url = isset( $quiz_detail_transient['url'] ) ? $quiz_detail_transient['url'] : '';
        $post_type = isset( $quiz_detail_transient['post_type'] ) ? $quiz_detail_transient['post_type'] : '';

        $inserted = $wpdb->insert(
            "{$wpdb->prefix}exms_quizzes_results",
            [
                'user_id'           => intval( $user_id ),
                'quiz_id'           => intval( $quiz_id ),
                'course_id'         => 0,
                'parent_posts'      => exms_get_post_parent( $post_type, $url ),
                'obtained_points'   => intval( $obtained_points ),
                'points_type'       => '',
                'correct_questions' => intval( $correct_questions ),
                'wrong_questions'   => intval( $wrong_questions ),
                'not_attempt'       => intval( $not_attempt ),
                'review_questions'  => intval( $review_questions ),
                'passed'            => '',
                'percentage'        => intval( $percentage ),
                'time_taken'        => $time_taken,
                'result_date'       => current_time('timestamp'),
                'attempt_number'    => intval( $attempt_number )
            ],
            [ '%d','%d', '%d', '%s','%d','%s','%d','%d','%d','%d','%s','%d','%s','%s', '%d' ]
        );

        delete_transient( 'exms_quiz_data_' . $user_id . '_' . $quiz_id );
        delete_transient( 'exms_quiz_expire_time_'.$user_id.'_'.$quiz_id );
        delete_transient( 'exms_quiz_parent_detail_'.$user_id.'_'.$quiz_id );
    }

    /**
     * save quiz data to transient
     */
    public function exms_save_quiz_data_to_transient() {

        $quiz_time = isset( $_POST['quiz_time'] ) ? $_POST['quiz_time'] : 0;
        $is_quiz_time_enabled = isset( $_POST['is_quiz_timer_enabled'] ) ? $_POST['is_quiz_timer_enabled'] : '';
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
        $current_url = isset( $_POST['current_url'] ) ? $_POST['current_url'] : '';
        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : ''; 

        $transient_data = [];

        if( 'on' == $is_quiz_time_enabled && $quiz_time && $user_id && $quiz_id ) {

            list($hours, $minutes, $seconds) = explode(":", $quiz_time);
            $time_in_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            $current_timestamp = time();
            $future_timestamp = $current_timestamp + $time_in_seconds;
            $get_transient_data = get_transient( 'exms_quiz_data_'.$user_id.'_'.$quiz_id );

            if( false === $get_transient_data ) {
                
                $quiz_detail = [];

                $quiz_detail['url'] = $current_url;
                $quiz_detail['post_type'] = $post_type;
                
                set_transient( 'exms_quiz_parent_detail_'.$user_id.'_'.$quiz_id, $quiz_detail );
                set_transient( 'exms_quiz_data_'.$user_id.'_'.$quiz_id, 'exms_quiz_data', $future_timestamp );
                set_transient( 'exms_quiz_expire_time_'.$user_id.'_'.$quiz_id, $future_timestamp );
                if ( ! wp_next_scheduled( 'exms_quiz_expire_event', [ $user_id, $quiz_id ] ) ) {
                    wp_schedule_single_event( $future_timestamp, 'exms_quiz_expire_event', [ $user_id, $quiz_id ] );
                }
            } 
        } 
        wp_die();
    }

    /**
     * add loader
     */
    public function exms_add_loader() {

        /**
         * require footer
         */
        require_once EXMS_TEMPLATES_DIR . '/frontend/exms-page-loader.php';
    }

    /**
     * Register scripts
     */
    public function exms_enqueue_scripts() {

        wp_register_style( 'exms-quiz-style', EXMS_ASSETS_URL. '/css/frontend/exms-quiz-shortcode.css' ); 
        
    }

    /**
     * render_quiz_shortcode
     */
    public function exms_render_quiz_shortcode( $atts ) {

        global $wpdb;

        if( ! is_user_logged_in() ) {
            return '<p class="exms-info-box">' . __( 'You must be logged in to take this quiz.', 'exms' ) . ' <a href="' . esc_url( wp_login_url() ) . '">' . __( 'Login here', 'exms' ) . '</a></p>';
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_style( 'exms-quiz-style' );
        wp_enqueue_script( 'exms-quiz-script' );
        wp_enqueue_style( 'dashicons' );

        $table_name = $wpdb->prefix . 'posts';
        $quiz_result_table = $wpdb->prefix . 'exms_quizzes_results';
        $quiz_reattempt_table = $wpdb->prefix . 'exms_quiz_reattempt_settings';
        
        $sanitized_atts = shortcode_atts(
            [ 'id' => ''],
            $atts,
            'exms_quiz'
        );

        $current_user_id = get_current_user_id();
        $quiz_id = isset( $sanitized_atts[ 'id' ] ) ? intval( $sanitized_atts[ 'id' ] ) : get_the_ID();
        $end_time = get_transient( 'exms_quiz_expire_time_'.$current_user_id.'_'.$quiz_id );
        $attempt_number = exms_get_latest_attempt( $current_user_id, $quiz_id );
        $submitted_question_count = exms_get_user_submitted_question_count( $current_user_id, $quiz_id, $attempt_number );
        $quiz_question_count = count( exms_get_quiz_questions( $quiz_id ) );
        $is_local_storage_delete_able = false;
        
        if( $submitted_question_count == $quiz_question_count ) {
            $is_local_storage_delete_able = true;
        }
        /**
         * delete transient if end time is smaller than current time
         */
        $current_time = time(); 
        if ( $end_time && $end_time < $current_time ) {

            delete_transient( 'exms_quiz_data_' . $current_user_id . '_' . $quiz_id );
            delete_transient( 'exms_quiz_expire_time_'.$current_user_id.'_'.$quiz_id );
            wp_clear_scheduled_hook( 'exms_quiz_expire_event', [ $current_user_id, $quiz_id ] );
        }

        $status = 'active';

        if( $quiz_id <= 0 ) {
            return '<p class="exms-info-box">' . __( 'Invalid ID provided.', 'exms' ) . '</p>';
        }

        $quiz_questions = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT 
                q.quiz_id, 
                q.question_id, 
                p.post_title AS question_title, 
                p.post_content AS question_content, 
                qq.points_for_question, 
                qq.question_type,
                qq.hint_for_question AS hint,
                qq.timer,
                GROUP_CONCAT(qa.answer_text) AS answers
                FROM {$wpdb->prefix}exms_quiz_questions q 
                LEFT JOIN {$wpdb->prefix}exms_questions qq 
                ON q.question_id = qq.question_id
                LEFT JOIN {$wpdb->prefix}posts p 
                ON q.question_id = p.ID  
                LEFT JOIN {$wpdb->prefix}exms_answer qa
                ON q.question_id = qa.question_id  
                WHERE q.quiz_id = %d
                AND q.status = %s
                AND p.post_type = 'exms-questions'
                AND p.post_status = 'publish'
                GROUP BY q.quiz_id, q.question_id, q.status, 
                qq.points_for_question, qq.question_type, 
                p.post_title, p.post_content, qq.hint_for_question
                ",
                $quiz_id,
                $status
            )
        );


        $quiz_option = exms_get_quiz_data( $quiz_id, 'shuffle_questions,quiz_status,quiz_timer' );
        $quiz_time = isset( $quiz_option->quiz_timer ) ? $quiz_option->quiz_timer : 0;
        $quiz_shuffle_option = isset( $quiz_option->shuffle_questions ) ? $quiz_option->shuffle_questions : 'off';
        $is_quiz_timer_enabled = isset( $quiz_option->quiz_status ) ? $quiz_option->quiz_status : '';

        if( 'on' === $quiz_shuffle_option ) {
            shuffle( $quiz_questions );
        }

        $max_range = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT pm.meta_value
                FROM {$wpdb->prefix}exms_quiz_questions q
                INNER JOIN {$wpdb->prefix}posts p ON q.question_id = p.ID
                INNER JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
                WHERE q.quiz_id = %d
                AND q.status = %s
                AND p.post_type = 'exms-questions'
                AND pm.meta_key = 'exms_range_max'
                LIMIT 1",
                $quiz_id,
                $status
            )
        );

        $min_range = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT pm.meta_value
                FROM {$wpdb->prefix}exms_quiz_questions q
                INNER JOIN {$wpdb->prefix}posts p ON q.question_id = p.ID
                INNER JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
                WHERE q.quiz_id = %d
                AND q.status = %s
                AND p.post_type = 'exms-questions'
                AND pm.meta_key = 'exms_range_min'
                LIMIT 1",
                $quiz_id,
                $status
            )
        );

        $data = exms_get_quiz_data( $quiz_id, 'quiz_timer, quiz_status, question_display' );
        $quiz_result_setting = exms_get_quiz_result_setting( $quiz_id, 'result' );
        $quiz_result_summary = isset( $quiz_result_setting->result ) ? $quiz_result_setting->result : '';
        wp_register_script( 'exms-quiz-script', EXMS_ASSETS_URL . '/js/frontend/exms-quiz.js', [ 'jquery', 'jquery-ui-sortable' ], false, true );

        wp_localize_script( 'exms-quiz-script', 'exms_quiz', [
            'ajax_url'             => admin_url( 'admin-ajax.php' ),
            'nonce'                => wp_create_nonce( 'exms_quiz_nonce' ),
            'answer_nonce'         => wp_create_nonce( 'exms_quiz_answer_nonce' ),
            'answer_view_nonce'    => wp_create_nonce( 'exms_quiz_answer_view_nonce' ),
            'quiz_buy_nonce'       => wp_create_nonce( 'exms_quiz_buy_nonce' ),
            'next'                 => __( 'Next', 'exms' ),
            'previous'             => __( 'Previous', 'exms' ),
            'back_to_quiz'         => __( 'Back to Quiz', 'exms' ),
            'correct'              => __( 'Correct', 'exms' ),
            'select_correct'       => __( 'Selected Correct', 'exms' ),
            'select_wrong'         => __( 'Selected Wrong'  ),
            'submit'               => __( 'Submit', 'exms' ),
            'question'             => __( 'Question', 'exms' ),
            'all_data'             => $quiz_questions,
            'min_range'            => $min_range,
            'max_range'            => $max_range,
            'user_id'              => get_current_user_id(),
            'quiz_id'              => get_the_ID(),
            'question_display'     => isset( $data->question_display ) ? $data->question_display : "exms_one_at_time" ,
            'correct_answer'       => __( 'Correct Answer', 'exms' ),
            'wrong_answer'         => __( 'Wrong Answer', 'exms' ),
            'not_attempt'          => __( 'Not Attempted', 'exms' ),
            'quiz_timer'           => $is_quiz_timer_enabled,
            'course_id'            => exms_get_course_id(),
            'current_url'          => exms_get_current_url(),
            'post_type'            => get_post_type(),
            'quiz_time'            => $quiz_time,
            'quiz_summary_result'  => $quiz_result_summary,
            'quiz_end_time'  => $end_time,
            'is_delete_able'    => $is_local_storage_delete_able,
            'quiz_end_message'  => __( 'The time has ended for this quiz, and your attempt has been automatically closed.', 'exms' ),
            'quiz_end_btn'      => __( 'Ok', 'exms' )
        ] );

        $reattempt_query = $wpdb->prepare( "SELECT * FROM $quiz_reattempt_table WHERE quiz_id = %d", $quiz_id );
        $reattempt_data = $wpdb->get_row( $reattempt_query );
        $view_answer_option = exms_get_quiz_data( $quiz_id, 'show_answer' );
        $quiz_reattempt_option = isset( $reattempt_data->quiz_reattempts ) ? $reattempt_data->quiz_reattempts : '';
        $is_assigned = exms_is_user_assigned_to_quiz( $current_user_id, $quiz_id );
        $query  = $wpdb->prepare( "SELECT * FROM $table_name WHERE ID = %d", $quiz_id );
        $result = $wpdb->get_row( $query );
        $active_question_count = count( exms_get_quiz_questions( $quiz_id ) );
        $first_question_id = isset( exms_get_quiz_questions($quiz_id)[0] ) ? intval( exms_get_quiz_questions($quiz_id)[0] ) : 0;
        $total_rank = get_post_meta( $quiz_id, 'exms-quiz-rank', true );
        if( is_array( $total_rank ) && ! empty( $total_rank ) ) {
            $total_rank = count( $total_rank );
        }

        $formatted_timer = 0;
        $remaining_time  = '';
        $timer_type      = '';
        $timer_format    = '';
        $timer_check     = false;

        $quiz_status       = $data->quiz_status ?? 'off';
        $question_display  = $data->question_display ?? 'off';
        $quiz_timer        = isset($data->quiz_timer) ? trim($data->quiz_timer) : '';
        if ( $quiz_status === 'on' ) {
            $formatted_timer = $quiz_timer;
            $remaining_time  = __( 'Quiz Remaining Time', 'exms' );
            $timer_type      = 'quiz_timer';
            $timer_check     = exms_timer_is_empty( $formatted_timer );
        }

        if ( ( $quiz_status === 'off' || $timer_check ) && $question_display !== 'exms_all_at_once' ) {
            $formatted_timer = exms_get_question_data( $first_question_id, 'timer' );
            $remaining_time  = __( 'Question Remaining Time', 'exms' );
            $timer_type      = 'question_timer';
        }

        $is_attempted = exms_user_has_quiz_attempt( $quiz_id, $current_user_id );

        $user_obtained_score = 0;
        $user_correct_question_count = 0;
        $user_wrong_question_count = 0;
        $user_not_attempt_question_count = 0;
        $user_pending_question_count = 0;
        $user_attempted_count = 0;
        $percentage = 0;
        $user_time_taken = 0;

        if ( $is_attempted ) {

            $total_questions = count( exms_get_quiz_questions( $quiz_id ) );
            $user_submitted_data = exms_fetch_user_quiz_data( $current_user_id, $quiz_id );
            $user_obtained_score = isset( $user_submitted_data->obtained_points ) ? intval( $user_submitted_data->obtained_points ) : 0;
            $user_correct_question_count = isset( $user_submitted_data->correct_questions ) ? intval( $user_submitted_data->correct_questions ) : 0;
            $user_wrong_question_count = isset( $user_submitted_data->wrong_questions ) ? intval( $user_submitted_data->wrong_questions ) : 0;
            $user_not_attempt_question_count = isset( $user_submitted_data->not_attempt ) ? intval( $user_submitted_data->not_attempt ) : 0;        
            $user_pending_question_count = isset( $user_submitted_data->review_questions ) ? intval( $user_submitted_data->review_questions ) : 0;
            $user_attempted_count = $user_wrong_question_count + $user_correct_question_count + $user_pending_question_count;
            $percentage = ( $user_correct_question_count / $total_questions ) * 100;
            $user_time_taken = isset( $user_submitted_data->time_taken ) ? exms_convert_second_into_time_format( $user_submitted_data->time_taken ) : 0;
        }   

        if ( ! $result || $result->post_type !== 'exms-quizzes' ) {
            return '<p class="exms-info-box">' . __( 'No quiz found for the given ID.', 'exms' ) . '</p>';
        } 

        $quiz_total_seats     = exms_get_quiz_data( $quiz_id, 'quiz_seat_limit' , 'passing_percentage' , 'display_passing_percentage' );
        $quiz_assign_seats    = exms_get_quiz_assign_count( $quiz_id );
        
        $quiz_total_seats_val     = isset( $quiz_total_seats ) ? (int) $quiz_total_seats : 0;
        $quiz_percentage_val      = isset( $quiz_total_seats->passing_percentage ) ? $quiz_total_seats->passing_percentage : 0;
        $show_quiz_percentage_val = isset( $quiz_total_seats->display_passing_percentage ) ? $quiz_total_seats->display_passing_percentage : false;
        $avaliable_seats          = $quiz_total_seats_val - ( isset( $quiz_assign_seats ) ? $quiz_assign_seats : 0 );

        $total_seat = (int) $quiz_total_seats_val;
        $assigned_seats = isset( $quiz_assign_seats ) ? (int) $quiz_assign_seats : 0;
        $avaliable_seats = $total_seat - $assigned_seats;

        $radius = 35;
        $circumference = 2 * pi() * $radius;
        $progress = $total_seat > 0 ? ($assigned_seats / $total_seat) : 0;
        $offset = $circumference * (1 - $progress);
        $video_url = exms_get_quiz_data( $quiz_id, 'video_url' );
        $thumbnail_url       = get_the_post_thumbnail_url( $quiz_id, 'full' );
        
        if( is_array( $quiz_questions ) && ! empty( $quiz_questions ) ) {

            foreach( $quiz_questions as $key => $question ) {

                $quiz_questions[ $key ]->answers = !empty( $question->answers ) 
                ? explode(',', $question->answers ) 
                : [];
            }
        }

        $display_questions = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT question_display
                FROM {$wpdb->prefix}exms_quiz
                WHERE quiz_id = %d",
                $quiz_id
            )
        );

        $breadcrumb_items = get_quiz_breadcrumb( $quiz_id );
        $quiz_shuffle_option = exms_get_quiz_data( $quiz_id, 'shuffle_questions' );

        if( 'on' == $quiz_shuffle_option ) {
            shuffle( $quiz_questions );
        }

        ob_start();
            
        require_once EXMS_TEMPLATES_DIR . '/frontend/exms-quiz-template.php';

        return ob_get_clean();
    }

    /**
     * Submit the Answer
     */
    public function exms_answer_submit() {

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_answer_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $user_id        = get_current_user_id();
        $quiz_id        = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
        $question_id    = isset( $_POST['question_id'] ) ? intval( $_POST['question_id'] ) : 0;
        $question_type  = isset( $_POST['question_type'] ) ? sanitize_text_field( $_POST['question_type'] ) : '';
        $get_quiz_timer = exms_get_quiz_data( $quiz_id, 'quiz_timer' );
        $user_taken_time = 0;
        
        if( ! $get_quiz_timer ) {
            $user_taken_time = isset( $_POST['user_taken_time'] ) ? time_to_seconds( $_POST['user_taken_time'] ) :'';            
        }

        $question_time = time_to_seconds( exms_get_question_settings( $question_id, 'timer' ) );
        $user_taken_time = $question_time - $user_taken_time;
        if( 'sorting_choice' == $question_type || 'fill_blank' == $question_type || 'multiple_choice' == $question_type ) {
            $answer = isset( $_POST['answer'] ) ? json_decode( str_replace( '\\', '', $_POST['answer'] ) ) : '';
        } elseif( isset( $_FILES['answer'] ) && 'file_upload' == $question_type ) {
            $answer = $_FILES['answer'];
        } else {
            $answer = isset( $_POST['answer'] ) ? $_POST['answer'] : '';
        }

        exms_update_user_answer( $user_id, $quiz_id, $answer, $question_id , $question_type, $user_taken_time );
        wp_die();
    }

    /**
     *  Submit the quiz
     */
    public function exms_quiz_submit() {

        global $wpdb;

        if ( !isset( $_POST[ 'nonce' ] ) || !check_ajax_referer( 'exms_quiz_nonce', 'nonce', false ) ) {

            wp_send_json_error( [ 'message' => __( 'Nonce verification failed' , 'exms' ) ] );

        }

        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;

        $user_id = get_current_user_id();
        $course_id = isset( $_POST['course_id'] ) ? $_POST['course_id'] : 0;
        $current_url = isset( $_POST['current_url'] ) ? $_POST['current_url'] : '';
        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
        
        if ( $quiz_id <= 0 ) {
            return '<p class="exms-info-box">' . __( 'Invalid ID provided.', 'exms' ) . '</p>';
        }

        $quiz_result_setting = exms_get_quiz_result_setting( $quiz_id, 'result' );
        $quiz_setting_data = exms_get_quiz_data( $quiz_id, 'quiz_status,quiz_timer,question_display' );

        if( 'summary_at_end' == $quiz_result_setting->result && 'exms_one_at_time' == $quiz_setting_data->question_display ) {

            $transient_data = get_transient( 'exms_quiz_data_'.$user_id.'_'.$quiz_id );

            if( $transient_data ) {

                $quiz_questions = exms_get_quiz_questions( $quiz_id );

                if( ! empty( $quiz_questions ) && is_array( $quiz_questions ) ) {
                    foreach( $quiz_questions as $question_id ) {

                        $question_id = intval( $question_id );
                        $question_answer = isset( $transient_data['question_answer_'.$question_id] ) ? $transient_data['question_answer_'.$question_id] : '';

                        $question_type = isset( $transient_data['question_type_'.$question_id] ) ? $transient_data['question_type_'.$question_id] : '';

                        exms_update_user_answer( $user_id, $quiz_id, $question_answer, intval( $question_id ), $question_type );
                    }
                }
            }
        }

        $quiz_status = isset( $quiz_setting_data->quiz_status ) ? $quiz_setting_data->quiz_status : 'off';

        if( 'off' == $quiz_status ) {
            $total_time_taken = exms_get_question_time_total( $quiz_id, $user_id );
        } else {

            $quiz_timer = isset( $quiz_setting_data->quiz_timer ) ? $quiz_setting_data->quiz_timer : 0;
            $total_time_taken = 0;

            if( $quiz_timer ) {

                $quiz_timer = time_to_seconds( $quiz_timer );
                $total_time_taken = isset( $_POST['time_taken'] ) ? time_to_seconds( $_POST['time_taken'] ) : 0;
                $total_time_taken = $quiz_timer - $total_time_taken;
            }
        }

        $quiz_data = isset( $_POST['quiz_data'] ) ? array_filter( json_decode( stripslashes( $_POST['quiz_data'] ), true ) ) : '';  

        if( is_array( $quiz_data ) && ! empty( $quiz_data ) ) {

            foreach( $quiz_data as $index => $data ) {

                $question_id = $index;
                $question_type = isset( $data['question_type'] ) ? $data['question_type'] : '';
                $answer = isset( $data['user_answer'] ) ? $data['user_answer'] : '';

                if( 'file_upload' == $question_type ) {
                    $answer = $_FILES['upload_quiz_data_'.$question_id];
                }

                if( ! $index || ! $question_id || ! $question_type ) {
                    continue;
                }

                exms_update_user_answer( $user_id, $quiz_id, $answer, $question_id, $question_type );
            }
        }

        $obtained_points = exms_get_quiz_possible_score( $user_id, $quiz_id );
        $correct_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 1 );
        $wrong_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 0 );
        $not_attempt = exms_get_quiz_answer_count( $user_id, $quiz_id, 'not-attempt' );
        $review_questions = exms_get_quiz_answer_count( $user_id, $quiz_id, 'pending' );
        $total_questions = count( exms_get_quiz_questions( $quiz_id ) );
        $percentage = ( $correct_questions / $total_questions ) * 100;
        $percentage = round( $percentage, 2 );
        $rank_data = get_post_meta( $quiz_id, 'exms-quiz-rank', true );
        $attempt_number = exms_get_submmited_quiz_attempt( $user_id, $quiz_id );

        if( $attempt_number ) {
            $attempt_number = $attempt_number + 1;
        } else {
            $attempt_number = 1;
        }

        if( is_array( $rank_data ) && ! empty( $rank_data ) ) {
            $rank_data['quiz_'.$user_id] = $percentage;
        } else {
            $rank_data = [ 'quiz_'.$user_id => $percentage ];
        }

        update_post_meta( $quiz_id, 'exms-quiz-rank', $rank_data);
        
        $inserted = $wpdb->insert(
            "{$wpdb->prefix}exms_quizzes_results",
            [
                'user_id'           => $user_id,
                'quiz_id'           => $quiz_id,
                'course_id'         => $course_id,
                'parent_posts'      => exms_get_post_parent( $post_type, $current_url ),
                'obtained_points'   => $obtained_points,
                'points_type'       => '',
                'correct_questions' => $correct_questions,
                'wrong_questions'   => $wrong_questions,
                'not_attempt'       => $not_attempt,
                'review_questions'  => $review_questions,
                'passed'            => 0,
                'percentage'        => $percentage,
                'time_taken'        => $total_time_taken,
                'result_date'       => current_time('timestamp'),
                'attempt_number'    => $attempt_number
            ],
            [ '%d','%d', '%d', '%s','%d','%s','%d','%d','%d','%d','%s','%d','%s','%s', '%d' ]
        );
        
        delete_transient( 'exms_quiz_data_' . $user_id . '_' . $quiz_id );
        delete_transient( 'exms_quiz_expire_time_'.$user_id.'_'.$quiz_id );
        wp_clear_scheduled_hook( 'exms_quiz_expire_event', [ $user_id, $quiz_id ] );
        wp_die();
    }

    /**
     * View the Submit Quiz Result
     */
    public function exms_quiz_answer_view(){

        global $wpdb;

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_answer_view_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
        $user_id = get_current_user_id();

        $quiz_questions = exms_get_user_quiz_data( $user_id, $quiz_id );
        
        if ( empty( $quiz_questions ) ) {
            wp_send_json_error( [ 'message' => __( 'No questions found for this quiz.', 'exms' ) ] );
        }
        
        foreach ( $quiz_questions as $key => $question ) {
            $answers_texts  = !empty( $question->answers ) ? explode( ',', $question->answers ) : [];
            $correct_flags  = !empty( $question->correct_answers ) ? explode( ',', $question->correct_answers ) : [];

            $formatted_answers = [];

            foreach ( $answers_texts as $i => $text ) {
                $formatted_answers[] = [
                    'text'    => trim( $text ),
                    'correct' => isset( $correct_flags[$i] ) && $correct_flags[$i] == '1'
                ];
            }

            $quiz_questions[ $key ]->user_answer = json_decode( $question->user_answer, true );
            $quiz_questions[ $key ]->answers = $formatted_answers;

            unset( $quiz_questions[ $key ]->correct_answers );
        }

        wp_send_json_success( [ 
            'data' => $quiz_questions 
        ] );
    }

    /**
    * Quiz content
    */
    public function exms_quiz_content( $content ){

        $request_uri = $_SERVER[ 'REQUEST_URI' ]; 
        $quiz_id = 0;
        $segments =  array_filter( explode( '/', $request_uri ) );
        $slug = end( $segments );
        if( $segments ) {
            $quiz_post = get_page_by_path( $slug , OBJECT, 'exms_quizzes');
            if ( $quiz_post ) {
                $quiz_id = $quiz_post->ID;
            }
        }
        if( ! $quiz_id ) {
            return $content;
        }
        return do_shortcode('[exms_quiz id='. $quiz_id .']');
    }

    /**
     * callback function for Quiz Buy 
     */
    public function exms_buy_quiz() {

        global $wpdb;

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_buy_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $user_id     = get_current_user_id();
        $quiz_id   = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;

        if ( empty( $quiz_id ) ) {
            wp_send_json( array(
                'status'  => 'error',
                'message' => __( 'Invalid Qiz details.', 'exms' ),
            ) );
        }

        $quiz_type = $wpdb->get_var( 
            $wpdb->prepare(
                "SELECT quiz_type FROM {$wpdb->prefix}exms_quiz_type WHERE quiz_id = %d",
                $quiz_id
            )
        );

        if ( $quiz_type === 'paid' ) {

            ob_start();
            include EXMS_TEMPLATES_DIR . '/frontend/course/buy-course-modelbox-template.php';
            $popup_html = ob_get_clean();

            wp_send_json( array(
                'status'       => 'show_payment_popup',
                'message'      => __( 'Please choose a payment method to proceed.', 'exms' ),
                'popup_html'   => $popup_html,
            ) );
        }

        wp_send_json( array(
            'status'  => 'error',
            'message' => __( 'Unable to process your request.', 'exms' ),
        ) );
    }
}

EXMS_QUIZ_Shortcode::instance();