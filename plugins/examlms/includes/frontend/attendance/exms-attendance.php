<?php

/**
 * EXMS Attendance Frontend
 */
class EXMS_ATTENDANCE_FRONTEND {
    
    /**
     * Summary of instance
     * @var 
     */
    private static $instance = null;

    /**
     * Summary of instance
     * @return EXMS_ATTENDANCE_FRONTEND
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_ATTENDANCE_FRONTEND) ) {
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
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_attendance_files' ] );
        add_action( 'wp_ajax_attendance_group_on_change', [ $this, 'exms_attendance_group_on_change' ] );
    }

    /**
     * attendance group on change
     */
    public function exms_attendance_group_on_change() {

        $response = [];

        $group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;

        $group_courses = exms_get_group_courses( $group_id );

        ob_start();
        if( is_array( $group_courses ) && ! empty( $group_courses ) ) {

            ?>
            <option value=""><?php echo __( 'Select a course', 'exms' ); ?></option>
            <?php
            foreach( $group_courses as $course_id ) {

                $course_id = intval( $course_id );
                ?>
                <option value="<?php echo $course_id; ?>"><?php echo get_the_title( $course_id ); ?></option>
                <?php
            }
        }
        ?>

        <?php

        $content = ob_get_contents();
        ob_get_clean();

        $response['status'] = 'true';
        $response['content'] = $content;
        echo json_encode( $response );
        wp_die();
    }
    /**
     * enqueue frontend report tab
     */
    public function exms_enqueue_attendance_files() {

        wp_enqueue_script( 'exms-attendance-frontend-js', EXMS_ASSETS_URL . 'js/frontend/exms-attendance.js', [ 'jquery' ], EXMS::VERSION, true );

        wp_enqueue_style( 'exms-attendance-frontend-css', EXMS_ASSETS_URL . 'css/frontend/exms-frontend-attendance.css', [], EXMS::VERSION, null );

        wp_localize_script( 'exms-attendance-frontend-js', 'EXMS_ATTENDANCE', [ 
            'ajaxURL'                       => admin_url( 'admin-ajax.php' ),
            'security'                      => wp_create_nonce( 'exms_ajax_nonce' )
        ] );
    }
}
EXMS_ATTENDANCE_FRONTEND::instance();