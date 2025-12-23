<?php

/**
 * EXMS Report Frontend
 */
class EXMS_REPORT_FRONTEND {
    
    /**
     * Summary of instance
     * @var 
     */
    private static $instance = null;

    /**
     * Summary of instance
     * @return EXMS_REPORT_FRONTEND
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_REPORT_FRONTEND) ) {
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
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_report_files' ] );
        add_action( 'wp_ajax_exms_report_group_dropdown', [ $this, 'exms_group_dropdown' ] );
        add_action( 'wp_ajax_exms_user_report_data', [ $this, 'exms_user_report_data_function' ] );
        add_action( 'wp_ajax_exms_user_specific_report_data', [ $this, 'exms_user_specific_report_data_func' ] );
    }

    /**
     * get user specific report data
     */
    public function exms_user_specific_report_data_func() {

        $response = [];

        $course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
        $group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;
        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        if( ! $course_id || ! $group_id || ! $user_id ) {
            wp_die();
        }

        $courses_count = count( exms_get_group_courses( $group_id ) );

        $response['status'] = 'true';
        $response['exms_group_name'] = get_the_title($group_id);
        $response['exms_course_name'] = get_the_title($course_id);
        $response['exms_course_count'] = $courses_count;
        echo json_encode( $response );
        wp_die();
    }

    /**
     * create user report data
     */
    public function exms_user_report_data_function() {

        $response = [];

        $group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;
        $course_ids = isset( $_POST['course_id'] ) ? $_POST['course_id'] : 0;
        if( $course_ids ) {
            $course_ids = [$course_ids];    
        } else {
            $course_ids = exms_get_group_courses( $group_id );
        }
        
        $user_id = get_current_user_id();
        $user_data = exms_get_user_data( $course_ids, 'exms-courses' );
        ob_start();

        if( is_array( $user_data ) && ! empty( $user_data ) ) {
            foreach( $user_data as $key => $data ) {

                $user_id = isset( $data['user_id'] ) ? intval( $data['user_id'] ) : 0;
                $course_id = isset( $data['post_id'] ) ? intval( $data['post_id'] ) : 0;
                ?>
                <tr>
                    <td class="col-no"><?php echo $key + 1 ?></td>
                    <td class="col-course"><?php echo get_the_title( $course_id ); ?></td>
                    <td class="col-student exms-student-col" data-course_id="<?php echo $course_id; ?>" data-user_id="<?php echo $user_id; ?>"><?php echo exms_get_user_name( $user_id ); ?></td>

                    <td class="col-score">
                      <span class="exms-score-pill">90</span>
                    </td>

                    <td class="col-target">90%</td>
                    <td class="col-academic">100</td>
                    <td class="col-behaviour">30</td>

                    <td class="col-actions">
                        <div class="exms-actions-wrap">
                            <button type="button" class="exms-row-action-btn" aria-label="<?php esc_attr_e('More', 'exms'); ?>">
                                <span class="dashicons dashicons-ellipsis"></span>
                            </button>

                            <ul class="exms-actions-dropdown">
                              <li><a href="#" class="exms-action-view"><?php _e('View', 'exms'); ?></a></li>
                              <li><a href="#" class="exms-action-edit"><?php _e('Edit', 'exms'); ?></a></li>
                              <li><a href="#" class="exms-action-remove"><?php _e('Remove', 'exms'); ?></a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }

        $report_content = ob_get_contents();
        ob_get_clean();

        $response['status'] = 'true';
        $response['report_content'] = $report_content;
        echo json_encode( $response );
        wp_die();
    }

    /**
     * group dropdown on report page 
     */
    public function exms_group_dropdown() {

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
    public function exms_enqueue_report_files() {

        wp_enqueue_script( 'exms-report-frontend-js', EXMS_ASSETS_URL . 'js/frontend/exms-report.js', [ 'jquery' ], EXMS::VERSION, true );

        wp_enqueue_style( 'exms-report-frontend-css', EXMS_ASSETS_URL . 'css/frontend/exms-frontend-report.css', [], EXMS::VERSION, null );

        wp_localize_script( 'exms-report-frontend-js', 'EXMS_REPORT', [ 
            'ajaxURL'                       => admin_url( 'admin-ajax.php' ),
            'dropdown_error_msg'            => __( 'Both the group and the course must be selected.', 'exms' ),
            'security'                      => wp_create_nonce( 'exms_ajax_nonce' )
        ] );
    }
}
EXMS_REPORT_FRONTEND::instance();