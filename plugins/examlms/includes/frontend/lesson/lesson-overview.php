<?php
/**
 * Lesson overview page
 */
class EXMS_LESSON {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function instance(): EXMS_LESSON {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_LESSON ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_lesson_scripts' ] );
        add_action( 'template_redirect', [$this, 'exms_handle_mark_complete'] );
    }

    /**
     * Enqueue lesson assets
     */
    public function exms_enqueue_lesson_scripts() {
        wp_enqueue_style( 'exms-lesson-style', EXMS_ASSETS_URL . 'css/frontend/exms-lesson.css', [], EXMS::VERSION );
        wp_enqueue_script( 'exms-lesson-js', EXMS_ASSETS_URL . 'js/frontend/exms-lesson.js', [ 'jquery' ], EXMS::VERSION, true );

        wp_localize_script( 'exms-lesson-js', 'EXMS', [
            'ajaxURL'  => admin_url( 'admin-ajax.php' ),
            'security' => wp_create_nonce( 'exms_ajax_nonce' ),
        ] );
    }

    /**
     * Handle mark as complete (course + step)
     */
    public function exms_handle_mark_complete() {

        if ( isset( $_POST['exms_action'] ) ) {

            global $wpdb;

            $is_course_complete_action = false;

            if ( $_POST['exms_action'] === 'mark_complete' ) {
                $nonce_action = 'exms_mark_complete';
            } elseif ( $_POST['exms_action'] === 'mark_complete_course' ) {
                $nonce_action = 'exms_mark_complete_course';
                $is_course_complete_action = true;
            } else {
                return;
            }

            if ( ! isset( $_POST['exms_nonce'] ) || ! wp_verify_nonce( $_POST['exms_nonce'], $nonce_action ) ) {
                wp_die( 'Security check failed.' );
            }

            $user_id   = get_current_user_id();
            $course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
            $item_id   = isset( $_POST['step_id'] ) ? intval( $_POST['step_id'] ) : $course_id;
            $item_type = exms_get_item_type( $item_id );

            if ( ! $user_id || ! $course_id || ! $item_id || empty( $item_type ) ) {
                wp_die( 'Invalid data provided.' );
            }

            $table = $wpdb->prefix . 'exms_user_progress_tracking';
            $wpdb->insert(
                $table,
                [
                    'user_id'         => $user_id,
                    'course_id'       => $course_id,
                    'item_id'         => $item_id,
                    'item_type'       => $item_type,
                    'status'          => 1,
                    'completion_date' => current_time( 'timestamp' ),
                ],
                [ '%d', '%d', '%d', '%s', '%d', '%s' ]
            );

            $enrollment_table = $wpdb->prefix . 'exms_user_enrollments';

            if ( $is_course_complete_action ) {
                $progress_value = '100';
            } else {
                $progress_value = 'in progress';
            }

            $wpdb->update(
                $enrollment_table,
                [
                    'progress_percent' => $progress_value,
                ],
                [
                    'user_id' => $user_id,
                    'post_id' => $course_id,
                ],
                [ '%s' ],
                [ '%d', '%d' ]
            );

            $current_url = ( ! empty( $_SERVER['REQUEST_URI'] ) )
                ? esc_url_raw( $_SERVER['REQUEST_URI'] )
                : wp_get_referer();

            wp_safe_redirect( $current_url );
            exit;
        }
    }

}

EXMS_LESSON::instance();
