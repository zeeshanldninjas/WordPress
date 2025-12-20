<?php
/**
 * WP EXAMS - Settings
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Settings
 *
 * Handles all actions related to settings page
 */
class EXMS_Settings {

	private static $instance;
    private $settings_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Settings ) ) {

        	self::$instance = new EXMS_Settings;
            if( isset( $_GET['page'] ) && $_GET['page'] === 'exms-settings' ) {
                self::$instance->settings_page = true;
            }
        	self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Define hooks
	 */
	public function hooks() {

		add_action( 'admin_post_wpeq_save_settings', [ $this, 'exms_save_settings' ] );
		add_action( 'exms_add_settings_tabs', [ $this, 'exms_add_settings_tabs' ] );
		add_action( 'exms_add_settings_tabs_data', [ $this, 'exms_add_settings_tabs_data' ] );
		add_action( 'admin_post_exms_filter_action', [ $this, 'exms_search_field_data' ] );
		add_action( 'exms_add_settings_tabs_data', [ $this, 'exms_display_tab_data' ] );
        add_action( 'exms_display_tab_data', [ $this, 'exms_display_tab_data' ] );
        
        add_action( 'admin_notices', [ $this, 'exms_settings_update_notice' ] );
        add_action( 'admin_post_exms_add_taxonomies', [ $this, 'exms_add_taxonomies_in_table' ] );
        add_action( 'in_admin_footer', [ $this, 'exms_update_taxonomy_thick_box' ] );
        add_action( 'wp_ajax_exms_update_taxonomies', [ $this, 'exms_update_taxonomies' ] );
        add_action( 'wp_ajax_exms_delete_taxonomy', [ $this, 'exms_delete_taxonomy' ] );
        add_action( 'wp_ajax_exms_quick_edit_taxonomies', [ $this, 'exms_quick_edit_taxonomies' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'exms_settings_scripts' ] );
        add_action('wp_ajax_exms_filter_action', [ $this, 'exms_filter_action_callback' ] );
        add_action( 'wp_ajax_exms_get_all_child_posts', [ $this, 'exms_get_all_child_posts' ] );
        add_action( 'wp_ajax_exms_update_attempt_status', [ $this, 'exms_update_attempt_status' ] );
        add_action( 'wp_ajax_exms_save_comment', [ $this, 'exms_save_comment' ] );
        add_action('admin_init', [ $this, 'exms_download_reports_data' ] );
        add_action( 'admin_init', [ $this, 'exms_student_download_reports_data' ] );
        add_shortcode( 'exms_dashboard_student', [ $this, 'exms_user_dashboard' ] );
        add_action('wp_ajax_exms_get_user_history', [ $this, 'exms_get_user_history' ] );
	}

    public function exms_get_user_history() {

        global $wpdb;
        check_ajax_referer('exms_ajax_nonce', 'security');

        $user_id = intval($_POST['user_id'] ?? 0);
        $current_page = intval($_POST['current_page'] ?? 1);
        $limit = 20;
        $offset = ($current_page - 1) * $limit;

        if (!$user_id) {
            wp_send_json_error('Invalid user ID');
        }

        $user = get_userdata($user_id);
        $user_name = $user ? $user->display_name : __('Unknown User', 'exms');

        $total_count = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}exms_quizzes_results WHERE user_id = %d", $user_id)
        );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, 
                        q.post_title AS quiz_name, 
                        c.post_title AS course_name
                FROM {$wpdb->prefix}exms_quizzes_results AS r
                LEFT JOIN {$wpdb->posts} AS q ON q.ID = r.quiz_id
                LEFT JOIN {$wpdb->posts} AS c ON c.ID = r.course_id
                WHERE r.user_id = %d
                ORDER BY r.result_date DESC
                LIMIT %d OFFSET %d",
                $user_id, $limit, $offset
            ),
            ARRAY_A
        );

        $data = [];

        foreach ($results as $row) {
            $formatted_date = '';
            if (!empty($row['result_date'])) {
                $timestamp = (int)$row['result_date'];
                $formatted_date = date_i18n('F j, Y g:i a', $timestamp);
            }

            $data[] = [
                'quiz_name'      => $row['quiz_name'] ?? __('-', 'exms'),
                'course_name'    => $row['course_name'] ?? __('-', 'exms'),
                'attempt_number' => intval($row['attempt_number'] ?? 0),
                'percentage'     => floatval($row['percentage'] ?? 0),
                'passed'         => !empty($row['passed']) ? __('Yes', 'exms') : __('No', 'exms'),
                'attempt_date'   => $formatted_date,
            ];
        }

        wp_send_json_success([
            'user_name'     => $user_name,
            'records'       => $data,
            'limit'         => $limit,
            'current_page'  => $current_page,
            'total_records' => intval($total_count),
            'has_more'      => ($offset + $limit) < $total_count
        ]);
    }
    public function exms_user_dashboard($atts) {
        $atts = $atts ? $atts : [];
        $user_id = isset( $atts['userid'] ) ? $atts['userid'] : get_current_user_id();
            $atts['user_type'] = 'instructor';
            $atts['user_type'] = 'student';
            $file = '/shortcodes/exms-user-dashboard.php';
            exms_include_template( $file, $atts );   
        return ob_get_clean();
    }

    public function exms_download_reports_data() {
        if (isset($_GET['exms_download']) && $_GET['exms_download'] == '1') {
            global $wpdb;

            $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];

            $table = $wpdb->prefix . 'exms_quizzes_results';

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));

                $query = $wpdb->prepare("
                    SELECT r.id AS id, r.user_id, r.quiz_id, r.course_id, r.parent_posts, 
                        r.obtained_points, r.points_type, r.correct_questions, r.wrong_questions, 
                        r.not_attempt, r.review_questions, r.passed, r.percentage, 
                        r.time_taken, r.result_date, r.attempt_number,
                        qa.question_id, qa.question_type, qa.attempt_date, qa.file_url, qa.is_correct, qa.answer, qa.comment
                    FROM {$table} AS r
                    LEFT JOIN {$wpdb->prefix}exms_exam_user_question_attempts AS qa 
                        ON qa.user_id = r.user_id 
                        AND qa.quiz_id = r.quiz_id
                        AND qa.attempt_number = r.attempt_number
                    WHERE r.id IN ($placeholders)
                    ORDER BY r.id DESC
                ", $ids);
            } else {
                $query = "
                    SELECT r.id AS id, r.user_id, r.quiz_id, r.course_id, r.parent_posts, 
                        r.obtained_points, r.points_type, r.correct_questions, r.wrong_questions, 
                        r.not_attempt, r.review_questions, r.passed, r.percentage, 
                        r.time_taken, r.result_date, r.attempt_number,
                        qa.question_id, qa.question_type, qa.attempt_date, qa.file_url, qa.is_correct, qa.answer, qa.comment
                    FROM {$table} AS r
                    LEFT JOIN {$wpdb->prefix}exms_exam_user_question_attempts AS qa 
                        ON qa.user_id = r.user_id 
                        AND qa.quiz_id = r.quiz_id
                        AND qa.attempt_number = r.attempt_number
                    ORDER BY r.id DESC
                ";
            }

            $rows = $wpdb->get_results($query, ARRAY_A);

            if (!empty($rows)) {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename=reports.csv');

                $out = fopen('php://output', 'w');
                fputcsv($out, array_keys($rows[0]));

                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }

                fclose($out);
                exit;
            }
        }
    }

    public function exms_student_download_reports_data() {
    if (isset($_GET['exms_student_download']) && $_GET['exms_student_download'] == '1') {
        global $wpdb;

        $ids = !empty($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];

        $table = $wpdb->prefix . 'exms_user_enrollments';

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '%d'));
            $query = $wpdb->prepare("
                SELECT 
                    e.id,
                    e.user_id,
                    u.display_name AS student_name,
                    e.post_id,
                    p.post_title AS course_name,
                    e.post_type,
                    e.enrolled_by,
                    en.display_name AS enrolled_by_name,
                    e.progress_percent,
                    e.start_date
                FROM {$table} AS e
                INNER JOIN {$wpdb->users} AS u ON u.ID = e.user_id
                INNER JOIN {$wpdb->posts} AS p ON p.ID = e.post_id
                LEFT JOIN {$wpdb->users} AS en ON en.ID = e.enrolled_by
                WHERE e.id IN ($placeholders)
                ORDER BY e.id DESC
            ", $ids);
        } else {
            $query = "
                SELECT 
                    e.id,
                    e.user_id,
                    u.display_name AS student_name,
                    e.post_id,
                    p.post_title AS course_name,
                    e.post_type,
                    e.enrolled_by,
                    en.display_name AS enrolled_by_name,
                    e.progress_percent,
                    e.start_date
                FROM {$table} AS e
                INNER JOIN {$wpdb->users} AS u ON u.ID = e.user_id
                INNER JOIN {$wpdb->posts} AS p ON p.ID = e.post_id
                LEFT JOIN {$wpdb->users} AS en ON en.ID = e.enrolled_by
                ORDER BY e.id DESC
            ";
        }

        $rows = $wpdb->get_results($query, ARRAY_A);

        if (!empty($rows)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=student-reports.csv');

            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows[0]));

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
            exit;
        }
    }
}


    public function exms_settings_scripts() {

        wp_enqueue_style( 'exms-report-style', EXMS_ASSETS_URL . 'css/admin/settings/reports-data.css', '', EXMS::VERSION, null );
        wp_enqueue_script( 'EXMS_report_js', EXMS_ASSETS_URL . 'js/admin/settings/reports-data.js', [ 'jquery' ], false, true );
        wp_enqueue_media();
        global $wpdb;
        $users = get_users([
            'role__in' => ['student'],
            'orderby'  => 'display_name',
            'order'    => 'ASC',
            'number'   => 10,
        ]);

        $assigned_quiz_ids = $wpdb->get_col("
            SELECT child_post_id 
            FROM {$wpdb->prefix}exms_post_relationship 
            WHERE assigned_post_type = 'exms-quizzes'
        ");

        $quiz_args = [
            'post_type'      => 'exms-quizzes',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        if( ! empty( $assigned_quiz_ids ) ) {
            $quiz_args['post__not_in'] = $assigned_quiz_ids;
        }
        $quizzes = get_posts( $quiz_args );

        $groups = get_posts([
            'post_type'      => 'exms-groups',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        $assigned_course_ids = $wpdb->get_col("
            SELECT child_post_id 
            FROM {$wpdb->prefix}exms_post_relationship 
            WHERE assigned_post_type = 'exms-courses'
        ");

        $course_args = [
            'post_type'      => 'exms-courses',
            'posts_per_page' => 10,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];
        if( ! empty( $assigned_course_ids ) ) {
            $course_args['post__not_in'] = $assigned_course_ids;
        }
        $courses = get_posts( $course_args );

        $quiz_tags = get_terms([
            'taxonomy'   => 'exms-quizzes_tags',
            'hide_empty' => false,
            'number'     => 10,
        ]);

        $quiz_cats = get_terms([
            'taxonomy'   => 'exms-quizzes_categories',
            'hide_empty' => false,
            'number'     => 10,
        ]);

        $date_filters = [
            [ 'value' => 'yesterday',  'label' => __( 'Yesterday', 'exms' ) ],
            [ 'value' => 'today',      'label' => __( 'Today', 'exms' ) ],
            [ 'value' => 'last_month', 'label' => __( 'Last Month', 'exms' ) ],
            [ 'value' => 'this_month', 'label' => __( 'This Month', 'exms' ) ],
            [ 'value' => 'last_year',  'label' => __( 'Last Year', 'exms' ) ],
            [ 'value' => 'this_year',  'label' => __( 'This Year', 'exms' ) ],
            [ 'value' => 'custom_date','label' => __( 'Custom date', 'exms' ) ],
        ];

        wp_localize_script( 'EXMS_report_js', 'EXMS_REPORTS', 
            [ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_quiz_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
                'processing'                        => __( 'Processing...', 'exms' ),
                'create_table'                      => __( 'Create tables', 'exms' ),
                'error_text'                        => __( 'Error', 'exms' ),
                'no_attempt_text'                        => __( 'No attempts found.', 'exms' ),
                'approve_text'                        => __( 'Approve', 'exms' ),
                'wrong_text'                        => __( 'Wrong', 'exms' ),
                'add_comment_text'                        => __( 'Add Comment', 'exms' ),
                'no_comment_text'                        => __( 'No comments yet.', 'exms' ),
                'comment_saved_text'                        => __( 'Comment Saved!', 'exms' ),
                'error_occur_text'                        => __( 'Error occurred.', 'exms' ),
                'filters' => [
                    'users'   => $users,
                    'quizzes' => $quizzes,
                    'groups'  => $groups,
                    'courses' => $courses,
                    'tags'    => $quiz_tags,
                    'cats'    => $quiz_cats,
                    'dates'   => $date_filters,
                ]
            ] 
        );
    }

    /**
     * Filter/Pagination Ajax
     * @return void
     */
    public function exms_filter_action_callback() {
        global $wpdb;
        $table       = $wpdb->prefix . 'exms_quizzes_results';
        $rel_table   = $wpdb->prefix . 'term_relationships';
        $tax_table   = $wpdb->prefix . 'term_taxonomy';

        $where  = [];
        $params = [];

        if( !empty( $_POST['exms_user_name'] ) ) {
            $where[]  = "r.user_id = %d";
            $params[] = intval($_POST['exms_user_name']);
        }

        if( !empty( $_POST['exms_quiz_name'] ) ) {
            $where[]  = "r.quiz_id = %d";
            $params[] = intval( $_POST['exms_quiz_name'] );
        }

        if( !empty( $_POST['exms_quiz_category'] ) ) {
            $where[] = $wpdb->prepare(
                "EXISTS (
                    SELECT 1
                    FROM $rel_table tr
                    INNER JOIN $tax_table tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.object_id = r.quiz_id
                    AND tt.taxonomy = 'exms-quizzes_categories'
                    AND tt.term_id = %d
                )",
                intval($_POST['exms_quiz_category'])
            );
        }

        if ( !empty( $_POST['exms_quiz_tags'] ) ) {
            $where[] = $wpdb->prepare(
                "EXISTS (
                    SELECT 1
                    FROM $rel_table tr
                    INNER JOIN $tax_table tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    WHERE tr.object_id = r.quiz_id
                    AND tt.taxonomy = 'exms-quizzes_tags'
                    AND tt.term_id = %d
                )",
                intval($_POST['exms_quiz_tags'])
            );
        }

        if(!empty($_POST['exms_date_field'])) {
            $period = sanitize_text_field($_POST['exms_date_field']);
            
            switch ($period) {
                case 'today':
                    $start_date = $end_date = date('Y-m-d');
                    break;
                case 'yesterday':
                    $start_date = $end_date = date('Y-m-d', strtotime('-1 day'));
                    break;
                case 'last_month':
                    $start_date = date('Y-m-01', strtotime('first day of last month'));
                    $end_date   = date('Y-m-t', strtotime('last day of last month'));
                    break;
                case 'this_month':
                    $start_date = date('Y-m-01');
                    $end_date   = date('Y-m-t');
                    break;
                case 'last_year':
                    $start_date = date('Y-01-01', strtotime('-1 year'));
                    $end_date   = date('Y-12-31', strtotime('-1 year'));
                    break;
                case 'this_year':
                    $start_date = date('Y-01-01');
                    $end_date   = date('Y-12-31');
                    break;
                case 'custom_date':
                    $start_date = !empty($_POST['exms_start_date']) ? sanitize_text_field($_POST['exms_start_date']) : null;
                    $end_date   = !empty($_POST['exms_end_date']) ? sanitize_text_field($_POST['exms_end_date']) : null;
                    break;
                default:
                    $start_date = $end_date = null;
                    break;
            }

            if ($start_date && $end_date) {
                $where[]  = "DATE(FROM_UNIXTIME(r.result_date)) BETWEEN %s AND %s";
                $params[] = $start_date;
                $params[] = $end_date;
            }
        }

        if( !empty( $_POST['hierarchy'] ) && is_array( $_POST['hierarchy'] ) ) {
            $hierarchy = $_POST['hierarchy'];

            $course_id = !empty( $hierarchy['exms-courses'] ) ? intval( $hierarchy['exms-courses'] ) : 0;
            $quiz_id   = !empty( $hierarchy['exms-quizzes'] ) ? intval( $hierarchy['exms-quizzes'] ) : 0;
            if( $course_id ) {
                $where[]  = "r.course_id = %d";
                $params[] = $course_id;
            }
            if( $quiz_id ) {

                $where[]  = "r.quiz_id = %d";
                $params[] = $quiz_id;
                $keys = array_keys( $hierarchy );
                $quizIndex = array_search( 'exms-quizzes', $keys, true );

                if( $quizIndex !== false ) {
                    for( $i = $quizIndex - 1; $i >= 0; $i-- ) {
                        $prevKey = $keys[$i];
                        if( !empty($hierarchy[$prevKey] ) && $prevKey !== 'exms-courses' ) {
                            $where[]  = "r.parent_posts = %d";
                            $params[] = intval( $hierarchy[$prevKey] );
                            break;
                        }
                    }
                }
            }
        }

        if( !empty( $_POST['exms_course_name'] ) && empty( $_POST['course_hierarchy'] ) ) {
            $where[]  = "r.course_id = %d";
            $params[] = intval($_POST['exms_course_name']);
        }
        
        if( !empty( $_POST['course_hierarchy'] ) && is_array( $_POST['course_hierarchy'] ) ) {
            $hierarchy = $_POST['course_hierarchy'];

            $course_id = !empty( $_POST['exms_course_name'] ) ? intval( $_POST['exms_course_name'] ) : 0;
            $quiz_id   = !empty( $hierarchy['exms-quizzes'] ) ? intval( $hierarchy['exms-quizzes'] ) : 0;
            if( $course_id ) {
                $where[]  = "r.course_id = %d";
                $params[] = $course_id;
            }
            if( $quiz_id ) {

                $where[]  = "r.quiz_id = %d";
                $params[] = $quiz_id;
                $keys = array_keys( $hierarchy );
                $quizIndex = array_search( 'exms-quizzes', $keys, true );

                if( $quizIndex !== false ) {
                    for( $i = $quizIndex - 1; $i >= 0; $i-- ) {
                        $prevKey = $keys[$i];
                        if( !empty($hierarchy[$prevKey] ) && $prevKey !== 'exms-courses' ) {
                            $where[]  = "r.parent_posts = %d";
                            $params[] = intval( $hierarchy[$prevKey] );
                            break;
                        }
                    }
                }
            }
        }

        $per_page = 20;
        $page     = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $offset   = ($page - 1) * $per_page;

        $count_sql = "SELECT COUNT(*) FROM {$table} r";
        if (!empty($where)) {
            $count_sql .= " WHERE " . implode(" AND ", $where);
        }
        $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $params));

        $sql = "SELECT r.id, r.user_id, r.quiz_id, r.course_id,
            r.obtained_points, r.correct_questions, r.wrong_questions, 
            r.not_attempt, r.review_questions, r.passed, r.percentage, 
            r.time_taken, r.result_date, r.attempt_number,
            qa.question_id, qa.question_type, qa.attempt_date, qa.file_url, qa.is_correct, qa.answer, qa.comment,
            wq.quiz_timer, wq.passing_percentage, wq.pass_quiz_message, wq.fail_quiz_message
        FROM {$table} r
        LEFT JOIN {$wpdb->prefix}exms_exam_user_question_attempts qa
            ON qa.user_id = r.user_id
            AND qa.quiz_id = r.quiz_id
            AND qa.attempt_number = r.attempt_number
        LEFT JOIN {$wpdb->prefix}exms_quiz wq ON wq.quiz_id = r.quiz_id";

        if(!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY r.id DESC LIMIT %d OFFSET %d";
        $params_with_limit = array_merge($params, [$per_page, $offset]);

        $rows = $wpdb->get_results($wpdb->prepare($sql, $params_with_limit), ARRAY_A);

        $data_array = [];
        $temp = [];

        if($rows) {
            foreach($rows as $row) {
                $rid = $row['id'];
                if(!isset($temp[$rid])) {
                    $user = get_userdata($row['user_id']);
                    $quiz = get_post($row['quiz_id']);
                    $course = !empty($row['course_id']) ? get_post($row['course_id']) : null;
                    $course_instructor_ids = $course ? exms_get_assign_instructor_ids($course->ID) : [];
                    $instructor_names = [];

                    if (!empty($course_instructor_ids) && is_array($course_instructor_ids)) {
                        foreach ($course_instructor_ids as $iid) {
                            $instructor_names[] = exms_get_user_name($iid);
                        }
                    } elseif (!empty($course_instructor_ids) && is_numeric($course_instructor_ids)) {
                        $instructor_names[] = exms_get_user_name($course_instructor_ids);
                    }

                    $course_instructor_name = !empty($instructor_names)
                        ? implode(', ', $instructor_names)
                        : __('-', 'exms');

                    $temp[$rid] = [
                        'id'              => $row['id'] ?? 0,
                        'user_id'         => $row['user_id'] ?? 0,
                        'quiz_id'         => $row['quiz_id'] ?? 0,
                        'obtained_points' => $row['obtained_points'] ?? 0,
                        'correct_questions' => $row['correct_questions'] ?? 0,
                        'wrong_questions'   => $row['wrong_questions'] ?? 0,
                        'not_attempt'       => $row['not_attempt'] ?? 0,
                        'review_questions'  => $row['review_questions'] ?? 0,
                        'passed'            => $row['passed'] ?? 0,
                        'time_taken'        => $row['time_taken'] ?? '',
                        'result_date'       => $row['result_date'] ?? '',
                        'attempt_number'    => $row['attempt_number'] ?? 0,
                        'quiz_timer'        => $row['quiz_timer'] ?? '',
                        'passing_percentage'=> $row['passing_percentage'] ?? 0,
                        'pass_quiz_message' => $row['pass_quiz_message'] ?? '',
                        'fail_quiz_message' => $row['fail_quiz_message'] ?? '',

                        'user'        => $user ? '<a href="' . add_query_arg('user_id', $user->ID, self_admin_url('user-edit.php')) . '">' . $user->display_name . '</a>' : __('Unknown User', 'exms'),
                        'user_name'   => $user ? $user->display_name : __('Unknown User', 'exms'),
                        'quiz'        => $quiz ? '<a href="' . get_edit_post_link($quiz->ID) . '">' . get_the_title($quiz->ID) . '</a>' : __('Unknown Quiz', 'exms'),
                        'quiz_name'   => $quiz ? get_the_title($quiz->ID) : __('Unknown Quiz', 'exms'),
                        'course'      => $course ? '<a href="' . get_edit_post_link($course->ID) . '">' . get_the_title($course->ID) . '</a>' : __('-', 'exms'),
						'course_name' => $course ? get_the_title($course->ID) : __('-', 'exms'),
                        'instructor_id'   => $course_instructor_ids,
                        'instructor_name' => $course_instructor_name,
                        'percentage'  => ($row['percentage'] ?? 0) . '%',
                        'date'        => !empty($row['result_date'])
                                            ? date(get_option('date_format') . ' ' . get_option('time_format'), (int) $row['result_date'])
                                            : '',
                        'attempts'    => []
                    ];
                }

                if(!empty($row['question_id'])) {
                    $question_post = get_post($row['question_id']);
                    $question_desc = $question_post
                        ? wp_trim_words(wp_strip_all_tags($question_post->post_content), 30, '...')
                        : __('No Question', 'exms');

                    $comment_value = '';
                    if( !empty($row['comment']) ) {
                        $comment_obj = get_comment( intval($row['comment']) );
                        if ( $comment_obj ) {
                            $comment_value = $comment_obj->comment_content;
                        } else {
                            $comment_value = $row['comment'];
                        }
                    }
                    $temp[$rid]['attempts'][] = [
                        'question_id'    => $row['question_id'],
                        'question_desc'  => $question_desc,
                        'question_type'  => $row['question_type'] ?? '',
                        'attempt_number' => $row['attempt_number'] ?? 0,
                        'file_url'       => $row['file_url'] ?? '',
                        'is_correct'     => $row['is_correct'] ?? '',
                        'answer'         => $row['answer'] ?? '',
                        'comment'         => $comment_value,
                        'attempt_date'   => !empty($row['attempt_date'])
                            ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $row['attempt_date'])
                            : '',
                        'correct_answer'=> exms_get_question_correct_answer( $row['question_id'], $row['question_type'] )
                    ];
                }
            }

            foreach ($temp as $res) {
                $data_array[] = [
                    'id' => $res['id'],
                    'cb' => sprintf(
                        '<input type="checkbox" class="exms-select" name="bulk-select[]" value="%s" />',
                        esc_attr($res['id'])
                    ),
                    'exms_user'       => $res['user'],
                    'exms_name'       => $res['quiz'],
                    'exms_percentage' => '
                        <div class="exms-percentage-wrapper" 
                            data-user="' . esc_attr($res['user_id']) . '" 
                            data-quiz="' . esc_attr($res['quiz_id']) . '" 
                            data-attempt="' . esc_attr($res['attempts'][0]['attempt_number'] ?? 0) . '">

                            <div class="exms-percentage-header">
                                <span class="exms-status-badge ' . ($res['passed'] ? 'exms-pass' : 'exms-fail') . '">
                                    ' . ($res['passed'] ? __('Pass', 'exms') : __('Fail', 'exms')) . '
                                </span>
                                <span class="exms-percentage-value">' . esc_html($res['percentage']) . '</span>
                            </div>

                            <div class="exms-percentage-bar">
                                <div class="exms-percentage-fill" style="width:' . intval($res['percentage']) . '%"></div>
                            </div>
                        </div>
                    ',
                    'exms_date'       => $res['date'],
                    'exms_action'     => '<a class="exms-action-col exms-report-action" data-attempts=\'' . esc_attr(wp_json_encode([
                        'attempts' => $res['attempts'],
                        'quiz_name'   => $res['quiz_name'] ?? '',
                        'user_name'   => $res['user_name'] ?? '',
                        'course_name'   => $res['course_name'] ?? "",
                        'percentage'  => $res['percentage'],
                        'additional'  => [
                            'id'               => $res['id'],
                            'user_id'          => $res['user_id'],
                            'quiz_id'          => $res['quiz_id'],
                            'obtained_points'  => $res['obtained_points'] ?? '',
                            'correct_questions'=> $res['correct_questions'] ?? 0,
                            'wrong_questions'  => $res['wrong_questions'] ?? 0,
                            'not_attempt'      => $res['not_attempt'] ?? 0,
                            'review_questions' => $res['review_questions'] ?? 0,
                            'passed'           => $res['passed'] ?? 0,
                            'percentage'       => $res['percentage'],
                            'time_taken'       => $res['time_taken'] ?? '',
                            'result_date'      => $res['result_date'] ?? '',
                            'attempt_number'   => $res['attempt_number'] ?? 0,
                            'quiz_timer' => $res['quiz_timer'],
                            'passing_percentage' => $res['passing_percentage'],
                            'pass_quiz_message' => $res['pass_quiz_message'],
                            'fail_quiz_message' => $res['fail_quiz_message'],
                            'instructor_id' => $res['instructor_id'],
                            'instructor_name' => $res['instructor_name'],
                        ]
                    ])) . '\'>' . __('View More', 'exms') . '</a>'
                ];
            }
        }

        wp_send_json_success([
            'items'        => $data_array,
            'total'        => $total_items,
            'per_page'     => $per_page,
            'current_page' => $page,
            'total_pages'  => ceil($total_items / $per_page),
            'total_items'  => $total_items,
        ]);
    }

    public function exms_update_attempt_status() {
        global $wpdb;
        check_ajax_referer('exms_ajax_nonce', 'security');

        $question_id    = intval($_POST['question_id'] ?? 0);
        $user_id        = intval($_POST['user_id'] ?? 0);
        $quiz_id        = intval($_POST['quiz_id'] ?? 0);
        $attempt_number = intval($_POST['attempt_number'] ?? 0);
        $decision       = sanitize_text_field($_POST['decision'] ?? '');

        if(!$question_id || !$user_id || !$quiz_id || !$attempt_number || !in_array($decision, ['accept','decline'], true)) {
            wp_send_json_error(__('Invalid request.', 'exms'));
        }

        $is_correct = ($decision === 'accept') ? 1 : 0;
        $points = 0;
        if($is_correct === 1) {
            $points = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT points_for_question FROM wp_exms_questions WHERE id = %d",
                    $question_id
                )
            );
        }

        $updated = $wpdb->update(
            'wp_exms_exam_user_question_attempts',
            [
                'is_correct' => $is_correct,
                'score'      => $points
            ],
            [
                'question_id'    => $question_id,
                'user_id'        => $user_id,
                'quiz_id'        => $quiz_id,
                'attempt_number' => $attempt_number,
            ],
            [ '%d', '%d' ],
            [ '%d', '%d', '%d', '%d' ]
        );

        if($updated === false) {
            wp_send_json_error(__('Failed to update attempt.', 'exms'));
        }
        $result_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * 
                FROM wp_exms_quizzes_results 
                WHERE user_id = %d AND quiz_id = %d AND attempt_number = %d",
                $user_id, $quiz_id, $attempt_number
            ),
            ARRAY_A
        );

        $percentage = 0;
        if($result_row) {
            $correct = (int)$result_row['correct_questions'];
            $wrong   = (int)$result_row['wrong_questions'];
            $review  = (int)$result_row['review_questions'];

            if($is_correct === 1) {
                $correct++;
            } else {
                $wrong++;
            }

            if($review > 0) {
                $review--;
            }

            $total = $correct + $wrong + $review;
            if($total > 0) {
                $percentage = round(($correct / $total) * 100, 2);
            }

            $wpdb->update(
                'wp_exms_quizzes_results',
                [
                    'correct_questions' => $correct,
                    'wrong_questions'   => $wrong,
                    'review_questions'  => $review,
                    'percentage'        => $percentage,
                ],
                [
                    'user_id'        => $user_id,
                    'quiz_id'        => $quiz_id,
                    'attempt_number' => $attempt_number,
                ],
                [ '%d', '%d', '%d', '%f' ],
                [ '%d', '%d', '%d' ]
            );

            $result_row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * 
                    FROM wp_exms_quizzes_results 
                    WHERE user_id = %d AND quiz_id = %d AND attempt_number = %d",
                    $user_id, $quiz_id, $attempt_number
                ),
                ARRAY_A
            );
        }

        $user = get_userdata($user_id);
        $quiz = get_post($quiz_id);

        $user_name = $user ? $user->display_name : __('Unknown User', 'exms');
        $quiz_name = $quiz ? get_the_title($quiz->ID) : __('Unknown Quiz', 'exms');

        wp_send_json_success([
            'user_id'    => $user_id,
            'quiz_id'    => $quiz_id,
            'question_id'=> $question_id,
            'is_correct' => $is_correct,
            'attempt'    => $attempt_number,
            'user_name'  => $user_name,
            'quiz_name'  => $quiz_name,
            'percentage' => $percentage. "%",
            'additional' => [
                'id'               => $result_row['id'] ?? 0,
                'user_id'          => $result_row['user_id'] ?? 0,
                'quiz_id'          => $result_row['quiz_id'] ?? 0,
                'course_id'        => $result_row['course_id'] ?? '',
                'parent_posts'     => $result_row['parent_posts'] ?? '',
                'obtained_points'  => $result_row['obtained_points'] ?? 0,
                'points_type'      => $result_row['points_type'] ?? '',
                'correct_questions'=> $result_row['correct_questions'] ?? 0,
                'wrong_questions'  => $result_row['wrong_questions'] ?? 0,
                'not_attempt'      => $result_row['not_attempt'] ?? 0,
                'review_questions' => $result_row['review_questions'] ?? 0,
                'passed'           => $result_row['passed'] ?? '',
                'percentage'       => $result_row['percentage']. "%" ?? 0,
                'time_taken'       => $result_row['time_taken'] ?? '',
                'result_date'      => $result_row['result_date'] ?? '',
                'attempt_number'   => $result_row['attempt_number'] ?? 0,
            ]
        ]);
    }

    public function exms_save_comment() {
        global $wpdb;
        check_ajax_referer('exms_ajax_nonce', 'security');

        $question_id    = intval($_POST['question_id'] ?? 0);
        $user_id        = intval($_POST['user_id'] ?? 0);
        $quiz_id        = intval($_POST['quiz_id'] ?? 0);
        $attempt_number = intval($_POST['attempt_number'] ?? 0);
        $comment_text   = wp_kses_post($_POST['comment'] ?? '');

        if(!$question_id || !$user_id || !$quiz_id || !$attempt_number || empty($comment_text)) {
            wp_send_json_error(__('Invalid request.', 'exms'));
        }

        $comment_data = array(
            'comment_post_ID'      => $quiz_id,
            'comment_content'      => $comment_text,
            'user_id'              => $user_id,
            'comment_type'         => 'exms_exam_comment',
            'comment_approved'     => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        if(!$comment_id){
            wp_send_json_error(__('Failed to save comment.', 'exms'));
        }

        $updated = $wpdb->update(
            'wp_exms_exam_user_question_attempts',
            [ 'comment' => $comment_id ],
            [
                'question_id'    => $question_id,
                'user_id'        => $user_id,
                'quiz_id'        => $quiz_id,
                'attempt_number' => $attempt_number,
            ],
            [ '%d' ],
            [ '%d', '%d', '%d', '%d' ]
        );

        if($updated === false){
            wp_send_json_error(__('Failed to update attempt with comment ID.', 'exms'));
        }

        $saved_comment = get_comment($comment_id);

        wp_send_json_success([
            'message'     => __('Comment saved successfully.', 'exms'),
            'comment'     => $saved_comment ? $saved_comment->comment_content : $comment_text,
            'comment_id'  => $comment_id
        ]);
    }

    /**
     * Get all post types relationship
     * @return void
     */
    public function exms_get_all_child_posts() {

        global $wpdb;
        $table = $wpdb->prefix . "exms_post_relationship";
        $relations = $wpdb->get_results("
            SELECT parent_post_id, child_post_id, assigned_post_type 
            FROM $table
        ");

        $data = [];
        if( $relations ) {

            foreach( $relations as $rel ) {

                $post = get_post( $rel->child_post_id );
                if( $post && $post->post_status === 'publish' ) {
                    $data[$rel->parent_post_id][$rel->assigned_post_type][] = [
                        'id'    => $post->ID,
                        'title' => $post->post_title,
                    ];
                }
            }
        }
        wp_send_json($data);
    }

    /**
     * Update taxonomies using quick edit :Ajax
     */
    public function exms_quick_edit_taxonomies() {

        $taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( $_POST['taxonomy'] ) : '';
        if( empty( $taxonomy ) ) {
            echo __( 'Taxonomy not found', 'exms' );

            wp_die();
        }

        $taxo_id = isset( $_POST['taxo_id'] ) ? sanitize_text_field( $_POST['taxo_id'] ) : '';
        if( empty( $taxo_id ) ) {
            echo __( 'Taxonomy ID not found', 'exms' );

            wp_die();
        }

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        if( empty( $name ) ) {
            echo __( 'Name ID not found', 'exms' );

            wp_die();
        }

        $slug = isset( $_POST['slug'] ) ? sanitize_text_field( $_POST['slug'] ) : '';
        
        if( empty( $slug ) ) {
            echo __( 'Slug ID not found', 'exms' );

            wp_die();
        }

        wp_update_term( $taxo_id, $taxonomy, [
            'name'          => $name,
            'slug'          => $slug 
        ] );

        wp_die();
    }

    /**
     * Delete single and multiple Taxonomy 
     */
    public function exms_delete_taxonomy() {

        /**
         * Get taxo for single delete
         */
        $taxonomy = isset( $_POST['taxo'] ) ? $_POST['taxo'] : '';
        $taxo_id = isset( $_POST['id'] ) ? (int)( $_POST['id'] ) : 0;

        if( ! is_array( $taxo_id ) && $taxo_id && $taxonomy ) {
            
            add_query_arg( 'message', 'deleted', $_POST['_wp_http_referer'] );
            wp_delete_term( $taxo_id, $taxonomy );
        }
        
        /**
         * Get taxonomy and taxo_id for multiple delete 
         */

        $taxonomy_ids = isset( $_POST['checkedID'] ) ? $_POST['checkedID'] : [];

        if( is_array( $taxonomy_ids ) && ! empty( $taxonomy_ids ) && $taxonomy ) {

            /**
             * Delete multiple taxonomy 
             */ 
            foreach ( $taxonomy_ids as $taxonomy_id ) {

                wp_delete_term( ( int )$taxonomy_id, $taxonomy );
            }
        }
        wp_die();
    }

    /**
     * Edit/Update taxonomy : Ajax
     */
    public function exms_update_taxonomies() {

        $taxonomy = isset( $_POST['exms_taxonomy'] ) ? sanitize_text_field( $_POST['exms_taxonomy'] ) : '';

        if( empty( $taxonomy ) ) {
            echo __( 'Taxonomy not found', 'exms' );

            wp_die();
        }

        $id = isset( $_POST['exms_id'] ) ? (int) $_POST['exms_id'] : 0;
        
        if( ! $id ) {
            echo __( 'ID not found', 'exms' );

            wp_die();
        }

        $name = isset( $_POST['exms_name'] ) ? sanitize_text_field( $_POST['exms_name'] ) : '';
        
        if( empty( $name ) ) {
            echo __( 'Name not found', 'exms' );

            wp_die();
        }

        $slug = isset( $_POST['exms_slug'] ) ? sanitize_text_field( $_POST['exms_slug'] ) : sanitize_title( $name );

        $desc = isset( $_POST['exms_desc'] ) ? sanitize_text_field( $_POST['exms_desc'] ) : '';

        $parent = isset( $_POST['exms_parent'] ) ? (int) $_POST['exms_parent'] : 0;

        wp_update_term( $id, $taxonomy, [
            'name'          => $name,
            'slug'          => $slug,
            'description'   => $desc,
            'parent'        => $parent 
        ] );

        wp_die();
    }

    /**
     * Update taxonomies using thick box
     */
    public function exms_update_taxonomy_thick_box($parent_id ) {

        add_thickbox();
        ?>
        <div id="exms_taxonomy_thickbox" class="exms-thickbox-content">
            <div class="exms-update-texonomy-form">
                <h3><?php _e( 'Edit Taxonomy' ) ?></h3>
                <p>
                    <label><?php _e( 'Name :', 'exms' ); ?></label>
                    <input class="exms-update-name" type="text" value="">
                </p>
                <p>
                    <label><?php _e( 'Slug :', 'exms' ); ?></label>
                    <input class="exms-update-slug" type="text" value="">
                </p>
                <?php
                $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
                $taxonomies = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : 'exms-quizzes_' .$tab;
                $cat_terms = get_terms( $taxonomies, [ 'hide_empty' => 0 ] );
                if( 'tags' != $tab ) {
                    ?>
                    <p class="exms-update-description-paragraph">
                        <label><?php _e( 'Parent Category :', 'exms' ); ?></label>
                        <select class="exms-update-parent-cat">
                            <option value="0"><?php _e( 'None', 'exms' ); ?></option>
                            <?php 

                            if(  $cat_terms ) {
                                foreach( $cat_terms as $cat_term ) {

                                    $term_id = isset( $cat_term->term_id ) ? $cat_term->term_id : 0;
                                    $term_name = isset( $cat_term->name ) ? $cat_term->name : '';
                                    ?>      
                                    <option value="<?php echo $term_id; ?>" <?php echo $term_id ? 'selected="selected"' : ''; ?>><?php echo $term_name; ?></option>
                                    <?php    
                                }   
                            }
                            ?>
                        </select>
                    </p>
                    <?php
                }
                ?>
                <p class="exms-update-description-paragraph">
                    <label><?php _e( 'Description :', 'exms' ); ?></label>
                    <textarea class="exms-update-desc"></textarea>
                </p>
                <input type="hidden" class="exms-taxonomy-id" value="">
                <input type="hidden" name="exms_parent_id" class="exms-parent-id" value="">
                <input type="hidden" class="exms-taxonomy" value="">
                <input type="button" class="exms-update-taxonomy button-primary" value="<?php _e( 'Update', 'exms' ); ?>">
            </div>
        </div>

        <!-- Create essay edit popUp -->
        <div id="exms_essay_thickbox" class="exms-essay-thickbox">
            <div class="exms-update-essay-form">
                <h3><?php _e( 'Update Essay', 'exms' ) ?></h3>
                <div class="exms-update-rows">
                    <label><?php _e( 'Content :', 'exms' ); ?></label>
                    <p><textarea class="exms-essay-content"></textarea></p>
                </div>
                <input type="button" class="exms-update-essay button-primary" value="<?php _e( 'Update', 'exms' ); ?>">
            </div>
        </div>
        <!-- End essay edit popUp -->

        <!-- Creat view essay popUP -->
        <div id="exms_essay_view" class="exms-view-essay">
            <div class="exms-view-essay-form">
                <div class="exms-view-rows">
                    <label><?php _e( 'Question :', 'exms' ); ?></label>
                    <p class="exms-essay-ques"></p>
                </div>
                <div class="exms-view-rows">
                    <label><?php _e( 'Answer :', 'exms' ); ?></label>
                    <p class="exms-essay-ans"></p>
                </div>
            </div>
        </div>
        <!-- End view essay popUp -->
        <?php
    }

    /**
     * Add taxonomies in data table
     */
    public function exms_add_taxonomies_in_table() {

        if( isset( $_POST['exms_submit_taxonomies'] ) 
            && current_user_can( 'manage_options' ) 
            && check_admin_referer( 'exms_taxonomy_nonce', 'exms_taxonomy_nonce_field' ) ) {

            $term_name = isset( $_POST['exms_taxonomy_name'] ) ? sanitize_text_field( $_POST['exms_taxonomy_name'] ) : '';
            if( empty( $term_name )  ) {
                wp_redirect( $_POST['_wp_http_referer'] );
                exit();
            }

            $tab = isset( $_POST['exms_tab'] ) ? $_POST['exms_tab'] : '';
            $post_type = isset( $_POST['exms_post_type'] ) ? $_POST['exms_post_type'] : '';
            $parent = isset( $_POST['exms_taxonomy_parent'] ) ? $_POST['exms_taxonomy_parent'] : 0;
            $description = isset( $_POST['exms_taxonomy_discription'] ) ? $_POST['exms_taxonomy_discription'] : '';
            $slug = isset( $_POST['exms_taxonomy_slug'] ) ? $_POST['exms_taxonomy_slug'] : sanitize_title( $term_name );

            $taxonomy = $post_type.'_'.$tab;

            if( taxonomy_exists( $taxonomy ) ) {

                $term_exists = term_exists( $term_name, $taxonomy );
                
                if( ! $term_exists ) {

                    $inserted = wp_insert_term(
                        $term_name,
                        $taxonomy,
                        [
                            'slug'        => $slug,
                            'parent'      => $parent,
                            'description' => $description,
                        ]
                    );
                }
            }

            wp_redirect( add_query_arg( 'message', 'updated', $_POST['_wp_http_referer'] ) );
            exit();
        }        
    }

    /**
     * Plugin setting update notice
     */
    public function exms_settings_update_notice() {

        if( isset( $_GET['message'] ) && 'updated' == $_GET['message'] ) {

            $class = 'notice notice-success  is-dismissible';
            $message = __( 'Setting Updated.', 'exms' );
            printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
        }
    }

	/**
	 * Display data according to tab selected 
	 */
	public function exms_display_tab_data() {

        $button_background = '';
        if( ( isset( $_GET['tab'] ) ) && ( 'email' == $_GET['tab'] || 'payment-integration' == $_GET['tab'] ) ) {
            $style_class = 'exms-setting-submit';
            $button_background = 'exms-btn-wrap-css';
            
        } else {
            $style_class = 'exms-setting-submit';
        }
	?>

		<div class="exms-setting-datas"><?php
		 	
		 	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : false;

			if( ! $tab || 'payment-integration' == $tab ||  'payment-integration' == $tab || 'email' == $tab || 'labels' == $tab ) {
				
				?><input type="submit" class="button button-primary <?php echo $style_class; ?>" name="exms_submit" value="<?php _e( 'Save Settings', 'exms' ); ?>" /><?php
			} ?>
		</div>
	<?php
	}

	/**
	 * Redirect filters field values in url ( Admin post ) 
	 */
	public function exms_search_field_data() {

        if( isset( $_POST['exms_search_submit'] ) 
        	&& current_user_can( 'manage_options' ) 
        	&& check_admin_referer( 'exms_filter_nonce', 'exms_filter_nonce_field' ) ) {

        	$search_query = isset( $_POST['exms_search_query'] ) ? $_POST['exms_search_query'] : '';
        	$search_name = isset( $_POST['exms_filter_search_by'] ) ? $_POST['exms_filter_search_by'] : [];
        	$start_date = isset( $_POST['exms_start_date'] ) ? $_POST['exms_start_date'] : ''; 
        	$end_date = isset( $_POST['exms_end_date'] ) ? $_POST['exms_end_date'] : ''; 

        	if( $search_query && $search_query == 'user_name' && $search_name ) {

    			$referer_url = remove_query_arg( [ 'quiz_name' ], $_POST['_wp_http_referer'] );
        		wp_redirect( add_query_arg( [ 'search_query' => $search_query, 'user_name' => implode( ',', $search_name ) ], $referer_url ) );
            	exit();

        	} elseif( $search_query && $search_query == 'quiz_name' && $search_name ) {
                //echo '<br>hellow world:';print_r($_POST);
        		$referer_url = remove_query_arg( [ 'user_name', 'start_date', 'end_date' ], $_POST['_wp_http_referer'] );
                //echo add_query_arg( [ 'search_query' => $search_query, 'quiz_name' => implode( ',', $search_name ) ], $referer_url );exit;
        		wp_redirect( add_query_arg( [ 'search_query' => $search_query, 'quiz_name' => implode( ',', $search_name ) ], $referer_url ) );
            	exit();

        	} elseif( $search_query && $search_query == 'date' && $start_date && $end_date ) {

        		$referer_url = remove_query_arg( [ 'quiz_name' ], $_POST['_wp_http_referer'] );
        		wp_redirect( add_query_arg( [ 'search_query' => $search_query, 'start_date' => $start_date, 'end_date' => $end_date ], $referer_url ) );
            	exit();

        	} else {

        		wp_redirect( $_POST['_wp_http_referer'] );
            	exit();
        	}
        }
    }

	/**
 	 * Save plugin settings data
	 */
	public function exms_save_settings() {

		if( isset( $_POST['exms_submit'] ) &&
            current_user_can( 'manage_options' ) &&
            check_admin_referer( 'exms_save_settings_form','exms_save_form' ) ) {

		    $payment_data = Exms_Core_Functions::get_options( 'payment_settings' );
		    $general_data = Exms_Core_Functions::get_options( 'general_settings' );
            $quiz_review_data = Exms_Core_Functions::get_options( 'quiz_review_page' );
		    $data = Exms_Core_Functions::get_options( 'settings' );
		    $label_data = Exms_Core_Functions::get_options( 'labels' );
            $existing_dynamic_labels = Exms_Core_Functions::get_options( 'dynamic_labels' );
		    if( empty( $data ) ) {
		        $data = [];
            }
		    if( empty( $payment_data ) ) {
		        $payment_data = [];
            }
		    if( empty( $general_data ) ) {
		        $general_data = [];
            }
		    if( empty( $label_data ) ) {
		        $label_data = [];
            }
            if( empty( $quiz_review_data ) ) {
		        $quiz_review_data = [];
            }
            
            $exms_tab        = sanitize_text_field( $_POST['exms_tab'] );
            $exms_tab_type   = sanitize_text_field( $_POST['exms_tab_type'] );
            
            if( $exms_tab == 'general' || $exms_tab == '' ) {
                if( isset( $_POST['exms_uninstall'] ) ) {
                    $general_data['exms_uninstall'] = ( 'on' == $_POST['exms_uninstall'] ) ? 'on' : 'off';
                } else {
                    $general_data['exms_uninstall'] = 'off';
                }

                if( isset( $_POST['dashboard_page'] ) ) {
                    $general_data['dashboard_page'] = (int) sanitize_text_field( $_POST['dashboard_page'] );
                }
            }

            if( $exms_tab == 'general' || $exms_tab == '' ) {
                
                if ( isset($_POST['exms_selected_page']) ) {
                    $quiz_review_data = (int) sanitize_text_field($_POST['exms_selected_page']);
                }

            }
            if( $exms_tab == 'labels' || $exms_tab == '' ) {
                
                if( isset( $_POST['exms_submitted_essays'] ) ) {
                    $label_data['exms_submitted_essays'] = sanitize_text_field( $_POST['exms_submitted_essays'] );
                }
                
                if( isset( $_POST['exms_user_report'] ) ) {
                    $label_data['exms_user_report'] = sanitize_text_field( $_POST['exms_user_report'] );
                }
                
                if( isset( $_POST['exms_quizzes'] ) ) {
                    $label_data['exms_quizzes'] = sanitize_text_field( $_POST['exms_quizzes'] );
                    $post_types = get_option( 'exms_post_types', [] );
                    if( is_array( $post_types ) && isset( $post_types['exms-quizzes'] ) ) {
                        $label = sanitize_text_field( $_POST['exms_quizzes'] );
                        $post_types['exms-quizzes']['singular_name'] = $label;
                        $post_types['exms-quizzes']['plural_name']   = $label . 's';
                        update_option( 'exms_post_types', $post_types );
                    }
                    
                }

                if( isset( $_POST['exms_certificates'] ) ) {
                    $label_data['exms_certificates'] = sanitize_text_field( $_POST['exms_certificates'] );
                }

                if( isset( $_POST['exms_questions'] ) ) {
                    $label_data['exms_questions'] = sanitize_text_field( $_POST['exms_questions'] );
                }

                if( isset( $_POST['exms_qroup'] ) ) {
                    $label_data['exms_qroup'] = sanitize_text_field( $_POST['exms_qroup'] );
                }
                $existing_dynamic_labels = is_array( $existing_dynamic_labels ) ? $existing_dynamic_labels : [];

                foreach ( $_POST as $key => $value ) {
                    if ( strpos( $key, 'exms-' ) === 0 ) {
                        $sanitized_key = sanitize_key( $key );
                        $existing_dynamic_labels[ $sanitized_key ] = sanitize_text_field( $value );
                    }
                }
            }

            /**
             * Update paypal data to db
             */
            if( $exms_tab == 'payment-integration' && ($exms_tab_type == '' || $exms_tab_type == 'exms_paypal_payment-integration') ) {
                if( isset( $_POST['exms_paypal_enable'] ) ) {
                    $payment_data['paypal_enable'] = ( 'on' == $_POST['exms_paypal_enable'] ) ? 'on' : 'off';
                } else{
                    $payment_data['paypal_enable'] = 'off';
                }
                $payment_data['exms_paypal_sandbox'] = isset( $_POST['exms_paypal_sandbox'] ) && $_POST['exms_paypal_sandbox'] === 'on' ? 'on' : 'off';

                if( $payment_data['paypal_enable'] == 'on' ) {

                    if( isset( $_POST['exms_paypal_sandbox'] ) ) {
                        $payment_data['exms_paypal_sandbox'] = $_POST['exms_paypal_sandbox'];
                    }
    
                    if( isset( $_POST['exms_paypal_redirects'] ) ) {
                        $payment_data['paypal_redirect_url'] = $_POST['exms_paypal_redirects'];
                    }
    
                    if( isset( $_POST['exms_paypal_currency'] ) ) {
                        $payment_data['paypal_currency'] = sanitize_text_field( $_POST['exms_paypal_currency'] );
                    }
    
                    if( isset( $_POST['exms_paypal_payee_email'] ) ) {
                        $payment_data['paypal_vender_email'] = sanitize_email( $_POST['exms_paypal_payee_email'] );
                    }
                    if( isset( $_POST['exms_paypal_client_id'] ) ) {
                        $payment_data['paypal_client_id'] = sanitize_text_field( $_POST['exms_paypal_client_id'] );
                    }
                } else {
                    $payment_data['paypal_transaction_mode'] = 'sandbox';
                    $payment_data['checkour_mode'] = '';
                    $payment_data['paypal_redirect_url'] = '';
                    $payment_data['paypal_currency'] = '';
                    $payment_data['paypal_vender_email'] = '';
                    $payment_data['paypal_client_id'] = '';
                    $payment_data['paypal_client_secret'] = '';
                }
            }
            /**
             * Update stripe data to db
             */
            if( $exms_tab == 'payment-integration' && $exms_tab_type == 'exms_stripe_payment-integration' ) {
                if( isset( $_POST['exms_stripe_enable'] ) ) {
                    $payment_data['stripe_enable'] = ( 'on' == $_POST['exms_stripe_enable'] ) ? 'on' : 'off';
                } else{
                    $payment_data['stripe_enable'] = 'off';
                }

                $payment_data['exms_stripe_sandbox'] = isset( $_POST['exms_stripe_sandbox'] ) && $_POST['exms_stripe_sandbox'] === 'on' ? 'on' : 'off';
    
                if( $payment_data['stripe_enable'] == 'on' ) {
                    if( isset( $_POST['exms_stripe_redirects'] ) ) {
                        $payment_data['stripe_redirect_url'] = $_POST['exms_stripe_redirects'];
                    }
        
                    if( isset( $_POST['exms_stripe_currency'] ) ) {
                        $payment_data['stripe_currency'] = sanitize_text_field( $_POST['exms_stripe_currency'] );
                    }
        
                    if( isset( $_POST['exms_stripe_payee_email'] ) ) {
                        $payment_data['stripe_vender_email'] = sanitize_email( $_POST['exms_stripe_payee_email'] );
                    }
                } else {
                    $payment_data['stripe_redirect_url'] = '';
                    $payment_data['stripe_currency'] = '';
                    $payment_data['stripe_vender_email'] = '';
                    $payment_data['stripe_api_key'] = '';
                    $payment_data['stripe_client_secret'] = '';
                }
            }
            
            if( $exms_tab == 'email' && $exms_tab_type == 'exms_students_email' ) {
                /**
                 * save buying quiz email setting
                 */
                // if( isset( $_POST['exms_buying_sub'] )
                //     && ! empty( $_POST['exms_buying_sub'] )
                //     && isset( $_POST['exms_buying_content'] )
                //     && ! empty( $_POST['exms_buying_content'] ) ) { 
                        
                    $data['exms_buying_subject'] = $_POST['exms_buying_sub'];
                    $data['exms_buying_option'] = 'yes' == ( $_POST['exms_buying_checkbox'] ) ? 'yes' : 'no';
                    $data['exms_buying_content'] = $_POST['exms_buying_content'];
                //}

                /**
                 * save pass quiz email setting
                 */
                // if( isset( $_POST['exms_pass_sub'] )
                //     && ! empty( $_POST['exms_pass_sub'] )
                //     && isset( $_POST['exms_passing_content'] )
                //     && ! empty( $_POST['exms_passing_content'] ) ) { 
                    
                    $data['exms_passing_subject'] = $_POST['exms_pass_sub'];
                    $data['exms_passing_option'] = 'yes' == ( $_POST['exms_pass_checkbox'] ) ? 'yes' : 'no';
                    $data['exms_passing_content'] = $_POST['exms_passing_content'];
                //}

                /**
                 * save fail quiz email setting
                 */
                // if( isset( $_POST['exms_fail_sub'] )
                //     && ! empty( $_POST['exms_fail_sub'] )
                //     && isset( $_POST['exms_falling_content'] )
                //     && ! empty( $_POST['exms_falling_content'] ) ) { 

                    $data['exms_failing_subject'] = $_POST['exms_fail_sub'];
                    $data['exms_failing_option'] = 'yes' == ( $_POST['exms_fail_checkbox'] ) ? 'yes' : 'no';
                    $data['exms_failing_content'] = $_POST['exms_falling_content'];
                // }

                /**
                 * save achivement quiz email setting
                 */
                // if( isset( $_POST['exms_achive_sub'] )
                //     && ! empty( $_POST['exms_achive_sub'] )
                //     && isset( $_POST['exms_achievement_content'] )
                //     && ! empty( $_POST['exms_achievement_content'] ) ) { 

                    $data['exms_achive_subject'] = $_POST['exms_achive_sub'];
                    $data['exms_achive_option'] = 'yes' == ( $_POST['exms_achive_checkbox'] ) ? 'yes' : 'no';
                    $data['exms_achive_content'] = $_POST['exms_achievement_content'];
                //}
            }
            /**
             * saved instructor assign email setting 
             */
            if( $exms_tab == 'email' && $exms_tab_type == 'exms_instructor_email' ) {
                // if( isset( $_POST['exms_instructor_assign_sub'] )
                //     && ! empty( $_POST['exms_instructor_assign_sub'] )
                //     && isset( $_POST['exms_instructor_assign_content'] )
                //     && ! empty( $_POST['exms_instructor_assign_content'] ) ) { 
                
                    $data['exms_instructor_assign_subject'] = $_POST['exms_instructor_assign_sub'];
                    $data['exms_instructor_assign_option'] = 'yes' == ( $_POST['exms_instructor_assign'] ) ? 'yes' : 'no';
                    $data['exms_instructor_assign_content'] = $_POST['exms_instructor_assign_content'];
                //}
           
                /**
                 * saved instructor unassign email setting 
                 */
            
                // if( isset( $_POST['exms_instructor_unassign_sub'] )
                //     && ! empty( $_POST['exms_instructor_unassign_sub'] )
                //     && isset( $_POST['exms_instructor_unassign_content'] )
                //     && ! empty( $_POST['exms_instructor_unassign_content'] ) ) { 
                    
                    $data['exms_instructor_unassign_subject'] = $_POST['exms_instructor_unassign_sub'];
                    $data['exms_instructor_unassign_option'] = 'yes' == ( $_POST['exms_instructor_unassign'] ) ? 'yes' : 'no';
                    $data['exms_instructor_unassign_content'] = $_POST['exms_instructor_unassign_content'];
                //}
            }
            /**
             * saved admin email setting 
             */
            if( $exms_tab == 'email' && $exms_tab_type == 'exms_admin_email' ) {
                // if( isset( $_POST['exms_admin_sub'] )
                //     && ! empty( $_POST['exms_admin_sub'] )
                //     && isset( $_POST['exms_admin_content'] )
                //     && ! empty( $_POST['exms_admin_content'] ) ) { 
                    
                    
                    $data['exms_admin_subject'] = $_POST['exms_admin_sub'];
                    $data['exms_admin_option'] = 'yes' == ( $_POST['exms_admin'] ) ? 'yes' : 'no';
                    $data['exms_admin_content'] = $_POST['exms_admin_content'];
                //}
            }
            /**
             * Saved email general setting data
             */
            if( $exms_tab == 'email' && ($exms_tab_type == 'exms_general_email' || $exms_tab_type == '' ) ) {
                if( isset( $_POST['exms_email_logo_url'] ) ) {
                    $data['exms_email_logo_url'] = $_POST['exms_email_logo_url'];
                }

                $data['exms_email_from_name'] = sanitize_text_field( $_POST['exms_email_from_name'] );
                

                if( isset( $_POST['exms_email_from_address'] ) ) {
                    $data['exms_email_from_address'] = sanitize_textarea_field( $_POST['exms_email_from_address'] );
                }

                if( isset( $_POST['exms_email_footer_text'] ) ) {
                    $data['exms_email_footer_text'] = sanitize_textarea_field( $_POST['exms_email_footer_text'] );
                }
            }
            
            /**
             * Update data to db
             */
            Exms_Core_Functions::save_options( 'settings', $data );
            Exms_Core_Functions::save_options( 'payment_settings', $payment_data );
            Exms_Core_Functions::save_options( 'general_settings', $general_data );
            Exms_Core_Functions::save_options( 'quiz_review_page', $quiz_review_data );
            Exms_Core_Functions::save_options( 'labels', $label_data );
            Exms_Core_Functions::save_options( 'dynamic_labels', $existing_dynamic_labels );
            $post_types = get_option( 'exms_post_types', [] );
            if ( is_array( $post_types ) && ! empty( $post_types ) ) {
                foreach ( $existing_dynamic_labels as $key => $label ) {
                    if ( isset( $post_types[ $key ] ) ) {
                        $label = sanitize_text_field( $label );
                        $post_types[ $key ]['singular_name'] = $label;
                        $post_types[ $key ]['plural_name']   = $label . 's';
                    }
                }

                update_option( 'exms_post_types', $post_types );
            }
            wp_safe_redirect( add_query_arg( 'message', 'updated', $_POST['_wp_http_referer'] ) );
		}
	}

	/**
	 * Create settings page tabs
	 */
	public function exms_add_settings_tabs() { 

	   $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
	   $active_class = 'exms-active-tab';

	   ?>
        <a class="<?php echo ( '' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings'; ?>"><?php _e( 'General', 'exms' ); ?>
            <span class="dashicons dashicons-admin-generic"></span>
        </a>
        <a class="<?php echo ( 'shortcodes' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=shortcodes'; ?>"><?php _e( 'Shortcodes', 'exms' ); ?>
            <span class="dashicons dashicons-shortcode"></span>
        </a>
        <a class="<?php echo ( 'payment-integration' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=payment-integration'; ?>"><?php _e( 'Payment Integration', 'exms' ); ?>
            <span class="dashicons dashicons-database-export"></span>
        </a>
        <a class="<?php echo ( 'categories' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=categories'; ?>"><?php _e( 'Categories', 'exms' ); ?>
            <span class="dashicons dashicons-image-filter"></span>
        </a>
        <a class="<?php echo ( 'tags' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=tags'; ?>"><?php _e( 'Tags', 'exms' ); ?>
            <span class="dashicons dashicons-tag"></span>
        </a>
        <a class="<?php echo ( 'reports' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=reports'; ?>"><?php _e( 'Reports', 'exms' ); ?>
            <span class="dashicons dashicons-welcome-write-blog"></span>
        </a>
        <a class="<?php echo ( 'email' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=email'; ?>"><?php _e( 'Email', 'exms' ); ?>
            <span class="dashicons dashicons-email"></span>
        </a>
        <!-- <a class="<?php echo ( 'structures' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=structures'; ?>"><?php _e( 'Structures', 'exms' ); ?>
            <span class="dashicons dashicons-email"></span> -->
        </a>
        <a class="<?php echo ( 'labels' == $tab ) ? $active_class : ''; ?>" href="<?php echo EXMS_DIR_URL . 'admin.php?page=exms-settings&tab=labels'; ?>"><?php _e( 'Labels', 'exms' ); ?>
            <span class="dashicons dashicons-email"></span>
        </a>
	   <?php
	}

	/**
	 * Show settings page data according to selected tab
	 */
	public function exms_add_settings_tabs_data( $tab ) {

		if( ! $tab || '' == $tab ) {

			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		}

		/**
		 * Include required tab file
		 */
		if( file_exists( EXMS_DIR . 'includes/admin/settings/tabs/' . $tab . '.php' ) ) {

			require_once EXMS_DIR . 'includes/admin/settings/tabs/'.$tab .'.php';
		}
	}

	/**
	 * Settings page output
	 */
	public static function exms_settings_page_output() { 
       
        $add_class = '';
        $tabs = [ 'payment-integration', 'email', 'reports', 'tags', 'categories' ];
        if( isset( $_GET['tab'] ) && in_array( $_GET['tab'], $tabs ) ) {

            $add_class = 'exms-add-background';
        }

        ?>
		<form id="exms_settings_tabs_form" action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" onkeydown="return event.key != 'Enter';">
			<div class="wpeq-settings-tabs-row">
				<ul class="wpeq-settings-tabs">
			<?php 
				/**
				 * Hook to add tabs
				 */
				do_action( 'exms_add_settings_tabs' );
			?>
				</ul>
			</div>
			<div class="wpeq-settings-tab-data">
			<?php 
				/**
				 * Hook to add tab content data
				 */
				do_action( 'exms_add_settings_tabs_data' ); ?>
                
			</div>
			<?php wp_nonce_field( 'wpeq_save_settings_form', 'wpeq_save_form' ); ?>
			<input type="hidden" id="exms_tab_form" name="exms_tab" value="<?php echo isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; ?>" />
            <input type="hidden" id="exms_tab_type" name="exms_tab_type" value="<?php echo isset( $_GET['tab_type'] ) ? $_GET['tab_type'] : ''; ?>" />
			<input type="hidden" name="action" value="wpeq_save_settings" />
		</form>
	<?php
	}
}

/**
 * Initialize EXMS_Settings
 */
EXMS_Settings::instance();