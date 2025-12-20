<?php

/**
 * EXMS Quiz Review Shortcode
 */
class EXMS_QUIZ_REVIEW {
    
    /**
     * Summary of instance
     * @var 
     */
    private static $instance = null;

    /**
     * Summary of instance
     * @return EXMS_QUIZ_REVIEW
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_QUIZ_REVIEW) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * hooks
     * @return void
     */
    private function hooks() {

        add_shortcode( 'exms_quiz_review', [ $this, 'render_exms_quiz_review_shortcode' ] );
        add_filter( 'the_content', [ $this , 'exms_override_selected_page_content' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_scripts' ] );
        add_action( 'wp_ajax_exms_filter_quiz', [ $this, 'exms_filter_quiz' ] );
        add_action( 'wp_ajax_nopriv_exms_filter_quiz', [ $this, 'exms_filter_quiz' ] );
        add_action( 'wp_ajax_exms_quiz_questions', [ $this, 'exms_quiz_questions' ] );
        add_action( 'wp_ajax_nopriv_exms_quiz_questions', [ $this, 'exms_quiz_questions' ] );
        add_action( 'wp_ajax_exms_quiz_question_detail', [ $this, 'exms_quiz_question_detail' ] );
        add_action( 'wp_ajax_nopriv_exms_quiz_question_detail', [ $this, 'exms_quiz_question_detail' ] );
        add_action( 'wp_ajax_exms_save_review', [ $this, 'exms_save_review' ] );
        add_action( 'wp_ajax_nopriv_exms_save_review', [ $this, 'exms_save_review' ] );
        add_action( 'wp_ajax_exms_quiz_review_submit', [ $this, 'exms_quiz_review_submit' ] );
        add_action( 'wp_ajax_nopriv_exms_quiz_review_submit', [ $this, 'exms_quiz_review_submit' ] );
        add_action( 'wp_ajax_exms_get_group_courses', [ $this, 'exms_get_group_courses' ] );
        add_action( 'wp_ajax_nopriv_exms_get_group_courses', [ $this, 'exms_get_group_courses' ] );
        add_action( 'wp_ajax_exms_get_courses_lessons', [ $this, 'exms_get_courses_lessons' ] );
        add_action( 'wp_ajax_nopriv_exms_get_courses_lessons', [ $this, 'exms_get_courses_lessons' ] );
        add_action( 'wp_ajax_exms_get_lessons_quiz', [ $this, 'exms_get_lessons_quiz' ] );
        add_action( 'wp_ajax_nopriv_exms_get_lessons_quiz', [ $this, 'exms_get_lessons_quiz' ] );
        add_action( 'wp_ajax_exms_get_quiz_student', [ $this, 'exms_get_quiz_student' ] );
        add_action( 'wp_ajax_nopriv_exms_get_quiz_student', [ $this, 'exms_get_quiz_student' ] );
    }
    /**
     * Register scripts
     */
    public function exms_enqueue_scripts() {

        wp_register_style( 'exms-quiz-review-style', EXMS_ASSETS_URL. '/css/frontend/exms-quiz-review.css' ); 
        wp_register_script( 'exms-quiz-review-script', EXMS_ASSETS_URL . '/js/frontend/exms-quiz-review.js', [ 'jquery' ], false, true );

        wp_localize_script( 'exms-quiz-review-script', 'exms_quiz_review', [
            'ajax_url'                 => admin_url( 'admin-ajax.php' ),
            'filter_nonce'             => wp_create_nonce( 'exms_quiz_filter_nonce' ),
            'questions_nonce'          => wp_create_nonce( 'exms_quiz_questions_nonce' ),
            'question_detail_nonce'    => wp_create_nonce( 'exms_quiz_question_detail_nonce' ),
            'save_review_nonce'        => wp_create_nonce( 'exms_save_review_nonce' ),
            'quiz_review_submit_nonce' => wp_create_nonce( 'exms_quiz_review_submit_nonce' ),
            'get_group_courses_nonce'  => wp_create_nonce( 'exms_get_group_courses_nonce' ),
            'get_courses_lessons_nonce'=> wp_create_nonce( 'exms_get_courses_lessons_nonce' ),
            'get_lessons_quiz_nonce'   => wp_create_nonce( 'exms_get_lessons_quiz_nonce' ),
            'get_quiz_student_nonce'   => wp_create_nonce( 'exms_get_quiz_student_nonce' ),
            'more_info_btn'            => __( 'More details', 'exms' ),
            'quiz_detail'              => __( 'Quiz details -', 'exms' ),
            'no_result'                => __( 'No results found', 'exms' ),
            'time_title'               => __( 'Time taken:', 'exms' ),
            'submit_quiz'              => __( 'Submit Quiz', 'exms' ),
            'review_answer'            => __( 'Review', 'exms' ),
            'selected_correct_answer'  => __( 'Selected Correct', 'exms' ),
            'selected_wrong_answer'    => __( 'Selected Wrong', 'exms' ),
            'correct_answer'           => __( 'Correct', 'exms' ),
            'courses'                  => __( 'Select Courses', 'exms' ),
            'lessons'                  => __( 'Select Lessons', 'exms' ),
            'quiz'                     => __( 'Select Quiz', 'exms' ),
            'student'                     => __( 'Select Student', 'exms' ),
        ] );
    }
    
    /**
     * exms_quiz_review_shortcode
     * @return bool|string
     */
    public function render_exms_quiz_review_shortcode() {

    global $wpdb;

    wp_enqueue_style( 'exms-quiz-review-style' );
    wp_enqueue_script( 'exms-quiz-review-script' );
    wp_enqueue_style( 'dashicons' );

    if ( ! is_user_logged_in() ) {
        return '<p class="exms-info-box">' . __( 'You must be logged in to review the quiz.', 'exms' ) . ' <a href="' . esc_url( wp_login_url() ) . '">' . __( 'Login here', 'exms' ) . '</a></p>' ;
    }

    $current_user = wp_get_current_user();
    $user_id = get_current_user_id();
    $student = false;
    $roles = ( array ) $current_user->roles;

    $groups = [];
    $quizzes = [];

    if ( in_array( 'administrator', $roles ) ) {
        $query  = "SELECT id, post_title FROM {$wpdb->prefix}posts WHERE post_type = 'exms-groups'";
        $groups = $wpdb->get_results( $query );
    }

    if ( in_array( 'exms_group_leader', $roles ) ) {
        $query = $wpdb->prepare(
            "SELECT p.id, p.post_title 
             FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}exms_user_enrollments u ON u.post_id = p.id
             WHERE p.post_type = 'exms-groups' AND u.user_id = %d",
            $user_id
        );
        $groups = $wpdb->get_results( $query );
    }

    if ( in_array( 'exms_student', $roles ) ) {
        $student = true;
        $query = $wpdb->prepare(
            "SELECT p.id, p.post_title 
             FROM {$wpdb->prefix}posts p 
             JOIN {$wpdb->prefix}exms_user_enrollments u ON u.post_id = p.id
             WHERE p.post_type = 'exms-quizzes' AND u.user_id = %d",
            $user_id
        );
        $quizzes = $wpdb->get_results( $query );
    }

    ob_start();
    $template_path = EXMS_TEMPLATES_DIR . '/frontend/exms-quiz-review-template.php';
    if ( file_exists( $template_path ) ) {
        include $template_path;
    }

    return ob_get_clean();
}


    /**
     * Filter the Quiz
     */
    public function exms_filter_quiz() {
        global $wpdb;

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_filter_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }


        $groups_id   = isset( $_POST['groups'] ) ? absint( $_POST['groups'] ) : 0;
        $courses_id  = isset( $_POST['courses'] ) ? absint( $_POST['courses'] ) : 0;
        $lessons_id  = isset( $_POST['lessons'] ) ? absint( $_POST['lessons'] ) : 0;
        $quiz_id     = isset( $_POST['quiz'] ) ? absint( $_POST['quiz'] ) : 0;
        $student_id  = isset( $_POST['student'] ) ? absint( $_POST['student'] ) : 0;
        $user_id     = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

        if ( $user_id > 0 && $quiz_id > 0 ) {

            $query = $wpdb->prepare(
                "SELECT 
                    q.time_taken AS time,
                    q.quiz_id AS id,
                    p.post_title AS quiz_title,
                    p.post_content AS quiz_content,
                    u.display_name AS user_name,
                    u.ID AS user_id
                FROM {$wpdb->prefix}exms_quizzes_results q
                INNER JOIN {$wpdb->prefix}posts p ON q.quiz_id = p.ID
                INNER JOIN {$wpdb->prefix}users u ON q.user_id = u.ID
                WHERE q.user_id = %d AND q.quiz_id = %d AND p.post_type = 'exms-quizzes'",
                $user_id,
                $quiz_id
            );

            $results = $wpdb->get_results( $query );

            wp_send_json_success([
                'data' => $results, 
                'student' => true
            ]);
        }

        if ( $groups_id === 0 && $courses_id === 0 && $lessons_id === 0 && $quiz_id === 0 && $student_id === 0 ) {
            wp_send_json_error( [ 'message' => 'Please select at least one filter before submitting.' ] );
        }

        $filter_log = [];
        $params     = [];
        $queries    = [];

        /**
         *  Deep relationship query (group > course > lesson > quiz)
         */
        $sql_deep = "
            SELECT 
                q.time_taken AS time,
                q.quiz_id AS id,
                p.post_title AS quiz_title,
                p.post_content AS quiz_content,
                u.display_name AS user_name,
                u.ID AS user_id
            FROM {$wpdb->prefix}exms_quizzes_results q
            INNER JOIN {$wpdb->prefix}posts p ON q.quiz_id = p.ID
            INNER JOIN {$wpdb->prefix}users u ON q.user_id = u.ID
        ";

        $joins_deep = [];
        $where_deep = [ "p.post_type = 'exms-quizzes'" ];
        $params_deep = [];

        if ( $groups_id > 0 ) {
            $joins_deep[] = "
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_group 
                    ON pr_group.parent_post_id = %d
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_course 
                    ON pr_course.parent_post_id = pr_group.child_post_id
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_lesson 
                    ON pr_lesson.parent_post_id = pr_course.child_post_id
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_quiz 
                    ON pr_quiz.parent_post_id = pr_lesson.child_post_id
                    AND pr_quiz.relationship_type = 'courses-quizzes'
                    AND pr_quiz.child_post_id = q.quiz_id
            ";
            $params_deep[] = $groups_id;
            $filter_log[] = "Group filter applied: $groups_id";
        }

        if ( $courses_id > 0 ) {
            $joins_deep[] = "
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_course2 
                    ON pr_course2.parent_post_id = %d
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_lesson2 
                    ON pr_lesson2.parent_post_id = pr_course2.child_post_id
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_quiz2 
                    ON pr_quiz2.parent_post_id = pr_lesson2.child_post_id
                    AND pr_quiz2.relationship_type = 'courses-quizzes'
                    AND pr_quiz2.child_post_id = q.quiz_id
            ";
            $params_deep[] = $courses_id;
            $filter_log[] = "Course filter applied: $courses_id";
        }

        if ( $lessons_id > 0 ) {
            $joins_deep[] = "
                INNER JOIN {$wpdb->prefix}exms_post_relationship pr_quiz3 
                    ON pr_quiz3.parent_post_id = %d
                    AND pr_quiz3.relationship_type = 'courses-quizzes'
                    AND pr_quiz3.child_post_id = q.quiz_id
            ";
            $params_deep[] = $lessons_id;
            $filter_log[] = "Lesson filter applied: $lessons_id";
        }

        if ( $quiz_id > 0 ) {
            $where_deep[] = "q.quiz_id = %d";
            $params_deep[] = $quiz_id;
            $filter_log[] = "Quiz filter applied: $quiz_id";
        }

        if ( $student_id > 0 ) {
            $where_deep[] = "q.user_id = %d";
            $params_deep[] = $student_id;
            $filter_log[] = "Student filter applied: $student_id";
        }

        if ( !empty( $joins_deep ) ) {
            $sql_deep .= ' ' . implode( ' ', $joins_deep );
        }

        $sql_deep .= ' WHERE ' . implode( ' AND ', $where_deep );
        $queries[] = [ 'sql' => $sql_deep, 'params' => $params_deep ];

        /**
         * Direct relationship query (quiz assigned directly to group/course)
         */
        $sql_direct = "
            SELECT 
                q.time_taken AS time,
                q.quiz_id AS id,
                p.post_title AS quiz_title,
                p.post_content AS quiz_content,
                u.display_name AS user_name,
                u.ID AS user_id
            FROM {$wpdb->prefix}exms_quizzes_results q
            INNER JOIN {$wpdb->prefix}posts p ON q.quiz_id = p.ID
            INNER JOIN {$wpdb->prefix}users u ON q.user_id = u.ID
            INNER JOIN {$wpdb->prefix}exms_post_relationship pr_direct 
                ON pr_direct.child_post_id = q.quiz_id
                AND pr_direct.relationship_type = 'courses-quizzes'
        ";

        $where_direct = [ "p.post_type = 'exms-quizzes'" ];
        $params_direct = [];

        if ( $groups_id > 0 ) {
            $where_direct[] = "pr_direct.parent_post_id = %d";
            $params_direct[] = $groups_id;
        }

        if ( $courses_id > 0 ) {
            $where_direct[] = "pr_direct.parent_post_id = %d";
            $params_direct[] = $courses_id;
        }

        if ( $lessons_id > 0 ) {
            $where_direct[] = "pr_direct.parent_post_id = %d";
            $params_direct[] = $lessons_id;
        }

        if ( $quiz_id > 0 ) {
            $where_direct[] = "q.quiz_id = %d";
            $params_direct[] = $quiz_id;
        }

        if ( $student_id > 0 ) {
            $where_direct[] = "q.user_id = %d";
            $params_direct[] = $student_id;
        }

        if ( count( $where_direct ) > 1 ) {
            $sql_direct .= ' WHERE ' . implode( ' AND ', $where_direct );
            $queries[] = [ 'sql' => $sql_direct, 'params' => $params_direct ];
            $filter_log[] = "Direct assignment filter added.";
        }

        $all_results = [];

        try {
            foreach ( $queries as $query ) {
                $prepared_sql = !empty( $query['params'] ) ? $wpdb->prepare( $query['sql'], $query['params'] ) : $query['sql'];
                $results = $wpdb->get_results( $prepared_sql );
                $filter_log[] = 'Executed SQL: ' . $prepared_sql;

                if ( $wpdb->last_error ) {
                    throw new Exception( 'Database error: ' . $wpdb->last_error );
                }

                if ( !empty( $results ) ) {
                    $all_results = array_merge( $all_results, $results );
                }
            }

            if ( empty( $all_results ) ) {
                wp_send_json_success( [
                    'data'    => [],
                    'message' => 'No quiz results found for given filters.',
                    'debug'   => $filter_log
                ] );
            }

            wp_send_json_success( [
                'data'    => $all_results,
                'message' => 'Filtered successfully.',
                'debug'   => $filter_log
            ] );

        } catch ( Exception $e ) {
            wp_send_json_error( [
                'message' => 'An error occurred while fetching quiz results.',
                'error'   => $e->getMessage(),
                'debug'   => $filter_log
            ] );
        }
    }

    /**
    *  Fetch quiz results
    */
    public function exms_quiz_questions() {

        global $wpdb;

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_questions_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $quiz_id = sanitize_text_field( $_POST['quiz_id'] );
        $user_id = sanitize_text_field( $_POST['user_id'] );
        $status  = 'active';

        $quiz_questions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    q.quiz_id, 
                    q.question_id, 
                    q_post.post_content AS quiz_content,
                    p_post.post_title AS question_title, 
                    p_post.post_content AS question_content,
                    q_s.answer AS user_answer,

                    qq.points_for_question, 
                    qq.question_type,
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
                    SELECT t1.question_id, t1.answer
                    FROM {$wpdb->prefix}exms_exam_user_question_attempts t1
                    INNER JOIN (
                        SELECT question_id, MIN(id) AS min_id
                        FROM {$wpdb->prefix}exms_exam_user_question_attempts
                        WHERE user_id = %d
                        GROUP BY question_id
                    ) t2 ON t1.id = t2.min_id
                ) q_s ON q.question_id = q_s.question_id
                LEFT JOIN {$wpdb->prefix}exms_answer qa
                    ON q.question_id = qa.question_id  
                WHERE q.quiz_id = %d
                    AND q.status = %s
                    AND p_post.post_type = 'exms-questions'
                GROUP BY q.quiz_id, q.question_id, q.status, 
                        qq.points_for_question, qq.question_type, 
                        p_post.post_title, p_post.post_content, 
                        q_post.post_title, q_post.post_content, 
                        q_s.answer",
                $user_id,
                $quiz_id,
                $status
            )
        );


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
     * Fetch quiz question detail
     */
    public function exms_quiz_question_detail() {
        global $wpdb;

        if ( !isset( $_POST['nonce'] ) || !check_ajax_referer( 'exms_quiz_question_detail_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $question_id = intval( $_POST['question_id'] );
        $quiz_id     = intval( $_POST['quiz_id'] );
        $user_id     = intval( $_POST['user_id'] );

        if ( $question_id <= 0 || $quiz_id <= 0 || $user_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Invalid IDs' ] );
        }

        $answer_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT answer, score 
                FROM {$wpdb->prefix}exms_exam_user_question_attempts
                WHERE question_id = %d AND quiz_id = %d AND user_id = %d 
                LIMIT 1",
                $question_id,
                $quiz_id,
                $user_id
            )
        );

        if ( ! $answer_row ) {
            wp_send_json_error( [ 'message' => 'Answer not found' ] );
        }

        $answer = json_decode( $answer_row->answer, true );

        $remark        = $answer['remark'] ?? '';
        $points        = $answer_row->score ?? '';

        wp_send_json_success( [
            'remark'        => $remark,
            'points'        => $points,

        ] );
    }

    /**
     * Save the current Question review 
     */
    public function exms_save_review() {
        global $wpdb;

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_save_review_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        $question_id = intval( $_POST['question_id'] );
        $quiz_id     = intval( $_POST['quiz_id'] );
        $user_id     = intval( $_POST['user_id'] );
        $remark      = sanitize_text_field( $_POST['remarks'] );
        $points      = floatval( $_POST['points'] );

        if ( $question_id <= 0 || $quiz_id <= 0 || $user_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Invalid IDs' ] );
        }

        $table = $wpdb->prefix . 'exms_exam_user_question_attempts';

        $answer_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, answer 
                FROM $table 
                WHERE question_id = %d AND quiz_id = %d AND user_id = %d 
                LIMIT 1",
                $question_id,
                $quiz_id,
                $user_id
            )
        );

        if ( ! $answer_row ) {
            wp_send_json_error( [ 'message' => 'Answer not found' ] );
        }

        $answer = json_decode( $answer_row->answer, true );

        $answer['remark']     = $remark;
        $answer['for_review'] = false;

        $updated_answer_json = wp_json_encode( $answer );

        $updated = $wpdb->update(
            $table,
            [
                'answer' => $updated_answer_json,
                'score'  => $points,
            ],
            [
                'id' => $answer_row->id
            ],
            [ '%s', '%f' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            wp_send_json_error( [ 'message' => 'Failed to update review' ] );
        }

        wp_send_json_success( [
            'message'       => 'Review saved and marked as complete'
        ] );
    }

    /**
     * Submit the quiz
     */
    public function exms_quiz_review_submit() {

        global $wpdb;

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_quiz_review_submit_nonce', 'nonce', false ) ) {
                wp_send_json_error( [ 'message' => 'Verification Failed ', 'received_nonce' => $nonce ] );
        }

        $quiz_id = intval( $_POST['quiz_id'] );
        $user_id = intval( $_POST['user_id'] );

        if ( $quiz_id <= 0 || $user_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Invalid IDs' ] );
        }

        $table = $wpdb->prefix . 'exms_quizzes_results';

        $result_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT total_points 
                FROM $table 
                WHERE quiz_id = %d AND user_id = %d 
                LIMIT 1",
                $quiz_id,
                $user_id
            )
        );

        if ( ! $result_row ) {
            wp_send_json_error( [ 'message' => 'Quiz result not found' ] );
        }

        $total_points = floatval( $result_row->total_points );

        $get_question_points = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(score) 
                FROM {$wpdb->prefix}exms_exam_user_question_attempts 
                WHERE quiz_id = %d AND user_id = %d",
                $quiz_id,
                $user_id
            )
        );
        $obtained_points = floatval( $get_question_points );

        $percentage = ($total_points > 0) ? ( $obtained_points / $total_points ) * 100 : 0;
        $percentage = round( $percentage, 2 );

        $updated = $wpdb->update(
            $table,
            [
                'obtained_points' => $obtained_points,
                'percentage'      => $percentage,
            ],
            [
                'quiz_id' => $quiz_id,
                'user_id' => $user_id
            ],
            [ '%f', '%f' ],
            [ '%d', '%d' ]
        );

        if ( false === $updated ) {
            wp_send_json_error( [ 'message' => 'Failed to update quiz result' ] );
        }

        wp_send_json_success( [
            'message'          => 'Quiz submitted successfully',
            'obtained_points'  => $obtained_points,
            'total_points'     => $total_points,
            'percentage'       => $percentage,
        ] );
    }

    /**
     * Fetch courses according to groups
     */
    public function exms_get_group_courses() { 

        global $wpdb;

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_get_group_courses_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Verification Failed', 'received_nonce' => $nonce ] );
        }

        $group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;

        if ( $group_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Group ID is invalid' ] );
        }

        $query = $wpdb->prepare(
            "SELECT c.ID as id, c.post_title 
            FROM {$wpdb->prefix}posts c
            LEFT JOIN {$wpdb->prefix}exms_post_relationship pr ON pr.child_post_id = c.ID
            WHERE c.post_type = 'exms-courses' 
            AND c.post_status = 'publish' 
            AND pr.parent_post_id = %d",
            $group_id
        );

        $courses = $wpdb->get_results( $query );

        if ( empty( $courses ) ) {
            wp_send_json_success( [
                'message' => 'No courses found for this group',
                'courses' => []
            ] );
        }

        wp_send_json_success( [
            'message' => 'Courses fetched successfully',
            'courses' => $courses
        ] );
    }

    /**
     * Fetch lessons according to courses 
     */
    public function  exms_get_courses_lessons() { 

        global $wpdb;

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_get_courses_lessons_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Verification Failed', 'received_nonce' => $nonce ] );
        }

        $courses_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

        if ( $courses_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Courses ID is invalid' ] );
        }

        $query = $wpdb->prepare(
            "SELECT c.ID as id, c.post_title 
            FROM {$wpdb->prefix}posts c
            LEFT JOIN {$wpdb->prefix}exms_post_relationship pr ON pr.child_post_id = c.ID
            WHERE c.post_type = 'exms-lessons' 
            AND c.post_status = 'publish' 
            AND pr.parent_post_id = %d",
            $courses_id
        );

        $lessons = $wpdb->get_results( $query );

        if ( empty( $lessons ) ) {
            wp_send_json_success( [
                'message' => 'No lessons found for this courses',
                'lessons' => []
            ] );
        }

        wp_send_json_success( [
            'message' => 'Lessons fetched successfully',
            'lessons' => $lessons
        ] );
    }

    /**
     * Fetch quiz according to lessons
     */
    public function exms_get_lessons_quiz() { 

        global $wpdb;

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_get_lessons_quiz_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Verification Failed', 'received_nonce' => $nonce ] );
        }

        $lesson_id = isset( $_POST['lesson_id'] ) ? intval( $_POST['lesson_id'] ) : 0;

        if ( $lesson_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Lesson ID is invalid' ] );
        }

        $query = $wpdb->prepare(
            "SELECT c.ID as id, c.post_title 
            FROM {$wpdb->prefix}posts c
            LEFT JOIN {$wpdb->prefix}exms_post_relationship pr ON pr.child_post_id = c.ID
            WHERE c.post_type = 'exms-quizzes' 
            AND c.post_status = 'publish' 
            AND pr.parent_post_id = %d",
            $lesson_id
        );

        $quiz = $wpdb->get_results( $query );

        if ( empty( $quiz ) ) {
            wp_send_json_success( [
                'message' => 'No Quiz found for this Lesson',
                'quiz'    => []
            ] );
        }

        wp_send_json_success( [
            'message' => 'Quiz fetched successfully',
            'quiz'    => $quiz
        ] );
    }

    /**
     * Fetch student according to quiz
     */
    public function exms_get_quiz_student() { 

        global $wpdb;

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

        if ( ! isset( $_POST['nonce'] ) || ! check_ajax_referer( 'exms_get_quiz_student_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Verification Failed', 'received_nonce' => $nonce ] );
        }

        $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;

        if ( $quiz_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Quiz ID is invalid' ] );
        }

        $query = $wpdb->prepare(
            "SELECT 
                sq.user_id AS student_id, 
                u.display_name AS student_name  
            FROM {$wpdb->prefix}exms_user_enrollments AS sq
            INNER JOIN {$wpdb->prefix}users AS u ON sq.user_id = u.ID
            WHERE sq.post_id =  %d",
            $quiz_id
        );

        $student = $wpdb->get_results( $query );

        if ( empty( $student ) ) {
            wp_send_json_success( [
                'message' => 'No Student found for this Lesson',
                'student'    => []
            ] );
        }

        wp_send_json_success( [
            'message' => 'Student fetched successfully',
            'student'    => $student
        ] );
    }

    /**
     * override selected page with content
     */
    public function exms_override_selected_page_content( $content ) {

        $selected_page_id = get_option( 'exms_quiz_review_page' );
    
        if ( is_page() && get_the_ID() == $selected_page_id ) {

            return do_shortcode( '[exms_quiz_review]' );
        }
    
        return $content;
    } 

}
EXMS_QUIZ_REVIEW::instance();