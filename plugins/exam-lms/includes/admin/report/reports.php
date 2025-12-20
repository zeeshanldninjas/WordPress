<?php
/**
 * Display reports table
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_All_Reports {

    /**
     * @var self
     */
    private static $instance;
    private $report_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_All_Reports ) ) {
            self::$instance = new EXMS_All_Reports;

            if ( isset( $_GET['page'] ) && $_GET['page'] === 'exms-reports' ) {
                self::$instance->report_page = true;
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        add_action( 'admin_enqueue_scripts', [ $this, 'exms_settings_scripts' ] );
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
     * Main function to call html content and tabs
     */
    public function exms_report_html_content() {
        if ( ! $this->report_page ) return false;

        if ( isset($_GET['page']) && $_GET['page'] === 'exms-reports' && empty($_GET['tab_type']) ) {
        $_GET['tab'] = 'reports';
        $_GET['tab_type'] = 'exms_quizzes_reports';
    }
    
        exms_render_admin_sub_tabs([
            'page' => 'exms-reports',
        ]);
        if ( file_exists( EXMS_DIR . 'includes/admin/report/exms-quiz-report-data.php' ) ) {
            require_once EXMS_DIR . 'includes/admin/report/exms-quiz-report-data.php';
        }
    }

}

EXMS_All_Reports::instance();
