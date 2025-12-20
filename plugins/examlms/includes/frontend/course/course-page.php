<?php
/**
 * Course page
 */
class EXMS_COURSE {
    
    /**
     * Define of instance
     */
    private static $instance = null;

    /**
      * Define the instance
     */
    public static function instance(): EXMS_COURSE {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_COURSE ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     *  Hooks that are used in the class
     */
    private function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_course_scripts' ] );
        add_action( 'wp_ajax_posts_steps', [ $this, 'exms_posts_steps_call_back' ] );
        add_action( 'wp_ajax_nopriv_posts_steps', [ $this, 'exms_posts_steps_call_back' ] );
        add_action( 'wp_ajax_exms_enrolled_user_to_the_course', [ $this, 'exms_enrolled_user_to_the_course' ] );
        add_action( 'wp_ajax_nopriv_exms_enrolled_user_to_the_course', [ $this, 'exms_enrolled_user_to_the_course' ] );
        add_action( 'template_redirect', [ $this, 'exms_template_redirect_vars' ] );
        // add_action( 'wp_ajax_exms_mark_course_complete', [$this, 'exms_mark_as_course_complete'] );
    }

    /**
     * enqueue course scripts file
     */
    public function exms_enqueue_course_scripts() {

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wp-exams-course', EXMS_ASSETS_URL . 'css/frontend/exms-course.css', [], EXMS::VERSION, null );

        // Enqueue PayPal SDK for course payments
        $exms_payment_options = get_option( 'exms_payment_settings' );
        $paypal_client_id = isset( $exms_payment_options['paypal_client_id'] ) ? $exms_payment_options['paypal_client_id'] : '';
        $paypal_currency = isset( $exms_payment_options['paypal_currency'] ) ? $exms_payment_options['paypal_currency'] : 'USD';
        
        $course_script_deps = [ 'jquery' ];
        
        if ( ! empty( $paypal_client_id ) ) {
            wp_enqueue_script( 'paypal-sdk', 'https://www.paypal.com/sdk/js?disable-funding=credit,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo,card&client-id=' . $paypal_client_id . '&currency=' . $paypal_currency, [], null, false );
            $course_script_deps[] = 'paypal-sdk';
        }

        wp_enqueue_script( 'wp-exams-course-js', EXMS_ASSETS_URL . 'js/frontend/exms-course-page.js', $course_script_deps, '', true );

        wp_localize_script( 'wp-exams-course-js', 'EXMS', [ 
            'ajaxURL'                       => admin_url( 'admin-ajax.php' ),
            'security'                      => wp_create_nonce( 'exms_ajax_nonce' ),
            'user_id'                       => get_current_user_id(),
            'course_detail_icon_right'            => EXMS_ASSETS_URL . 'imgs/rightbar-right-arrow.svg',            
            'course_detail_icon_left'            => EXMS_ASSETS_URL . 'imgs/rightbar-left-arrow.svg',            
        ] );
    }
    /**
     * Ajax callback to render course steps
     */
    public function exms_posts_steps_call_back() {

        global $wpdb;

        $response = [
            'status' => 'false',
            'course_content' => '',
        ];

        $post_id   = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $course_id   = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
        $post_type = get_post_type( $post_id );
        $final_slug = isset( $_POST['final_slug'] ) ? $_POST['final_slug'] : '';

        if ( ! $post_id || ! post_type_exists( $post_type ) || ! $course_id ) {
            wp_send_json( $response );
        }

        $structure = get_option( 'exms_post_types', true );
        $hierarchy = $this->get_hierarchy_from_structure( $structure );
        $ordered_post_types = $this->flatten_hierarchy( $hierarchy, $post_type );
        $assigned_types = $this->get_assigned_child_types( $wpdb, $post_id, $post_type );
        $sorted_types = $this->sort_assigned_types( $assigned_types, $ordered_post_types );

        ob_start();
        $this->render_child_steps( $wpdb, $post_id, $post_type, $sorted_types, $course_id, $final_slug );
        $response['status'] = 'true';
        $response['course_content'] = ob_get_clean();

        wp_send_json( $response );
    }

    /**
     * Builds a parent-child hierarchy array from the exms_post_types structure.
     *
     * @param array $structure The post type structure option from the database.
     * @return array Associative array mapping parent types to their children.
     */
    public function get_hierarchy_from_structure( $structure ) {
        $hierarchy = [];

        foreach ( $structure as $type => $data ) {
            $parent = $data['parent'];
            if ( ! isset( $hierarchy[ $parent ] ) ) {
                $hierarchy[ $parent ] = [];
            }
            $hierarchy[ $parent ][] = $type;
        }

        return $hierarchy;
    }

    /**
     * Performs a depth-first traversal to flatten the hierarchy from a given post type.
     *
     * @param array  $hierarchy   Hierarchy array generated from post type structure.
     * @param string $start_type  The post type to start traversal from.
     * @return array Ordered list of descendant post types.
     */
    public function flatten_hierarchy( $hierarchy, $start_type ) {
        $ordered_post_types = [];

        $walk_hierarchy = function( $parent_type ) use ( &$walk_hierarchy, &$hierarchy, &$ordered_post_types ) {
            if ( isset( $hierarchy[ $parent_type ] ) ) {
                foreach ( $hierarchy[ $parent_type ] as $child ) {
                    $ordered_post_types[] = $child;
                    $walk_hierarchy( $child );
                }
            }
        };

        $walk_hierarchy( $start_type );
        return $ordered_post_types;
    }

    /**
     * Retrieves unique assigned child post types from the relationship table.
     *
     * @param wpdb   $wpdb       WordPress database access object.
     * @param int    $post_id    The parent post ID.
     * @param string $post_type  The parent post type.
     * @return array List of assigned child post types.
     */
    public function get_assigned_child_types( $wpdb, $post_id, $post_type ) {
        return $wpdb->get_col( $wpdb->prepare(
            "SELECT DISTINCT assigned_post_type
             FROM {$wpdb->prefix}exms_post_relationship
             WHERE parent_post_id = %d
               AND parent_post_type = %s",
            $post_id, $post_type
        ) );
    }

    /**
     * Sorts the list of assigned child types based on the hierarchy order.
     *
     * @param array $assigned_types      List of post types from DB.
     * @param array $ordered_post_types  Post types ordered by hierarchy.
     * @return array Sorted list of assigned types.
     */
    public function sort_assigned_types( $assigned_types, $ordered_post_types ) {
        usort( $assigned_types, function ( $a, $b ) use ( $ordered_post_types ) {
            $pos_a = array_search( $a, $ordered_post_types );
            $pos_b = array_search( $b, $ordered_post_types );

            $pos_a = ( $pos_a !== false ) ? $pos_a : PHP_INT_MAX;
            $pos_b = ( $pos_b !== false ) ? $pos_b : PHP_INT_MAX;

            return $pos_a - $pos_b;
        });

        return $assigned_types;
    }

    /**
     * Renders the child post steps for each assigned type under the given parent.
     *
     * This function:
     * - Queries child post IDs based on parent and assigned post type.
     * - Groups and outputs the rendered HTML blocks by post type.
     * - Uses exms_render_child_step_html() to render individual child elements.
     *
     * @param wpdb   $wpdb          WordPress database access object.
     * @param int    $post_id       The parent post ID.
     * @param string $post_type     The parent post type.
     * @param array  $assigned_types Sorted list of assigned post types.
     */
    public function render_child_steps( $wpdb, $post_id, $post_type, $assigned_types, $course_id, $final_slug ) {
        
        foreach ( $assigned_types as $child_type ) {
            if ( ! post_type_exists( $child_type ) ) {
                continue;
            }

            $child_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT child_post_id
                 FROM {$wpdb->prefix}exms_post_relationship
                 WHERE parent_post_id = %d
                   AND parent_post_type = %s
                   AND assigned_post_type = %s",
                $post_id, $post_type, $child_type
            ) );

            if ( ! empty( $child_ids ) ) {
                $clean_type    = str_replace( 'exms-', '', $child_type );
                $wrapper_class = "exms-{$clean_type}-wrapper";
                $group_class   = "exms-{$clean_type}-group";

                echo '<div class="exms-step-content exms-child-step-wrapper ' . esc_attr( $group_class ) . '" data-parent-id="' . esc_attr( $post_id ) . '">';

                foreach ( $child_ids as $child_id ) {
                    echo exms_render_child_step_html( $child_id, $course_id, $wrapper_class, $final_slug );
                }

                echo '</div>';
            }
        }
    }

    /**
     * Get Course type data
     *  
     * @param $course_info array()
     * @param $course_type string 
     */ 
    public function exms_get_course_type_data( $course_info = array(), $course_type = '' ) {

        $type_data = array(
            'free' => array(
                'label'  => __( 'Free', 'exms' ),
                'button' => __( 'Enroll Now', 'exms' ),
            ),
            'paid' => array(
                'label'  => isset( $course_info['parent_post_price'] ) ? sprintf( __( 'Price: $%s', 'exms' ), $course_info['parent_post_price'] ) : __( 'Paid Course', 'exms' ),
                'button' => __( 'Buy Now', 'exms' ),
            ),
            'subscribe' => array(
                'label'  => isset( $course_info['subscription_days'] ) ? sprintf( __( 'Ends on: %s', 'exms' ), date_i18n( 'j F Y', strtotime( '+' . $course_info['subscription_days'] . ' days' ) ) ) : __( 'Subscribe to Access', 'exms' ),
                'button' => __( 'Subscribe', 'exms' ),
            ),
            'close' => array(
                'label'  => __( 'Enrollment Closed', 'exms' ),
                'button' => __( 'Explore Courses', 'exms' ),
            ),
        );

        $label       = __( 'Unknown Type', 'exms' );
        $button_text = __( 'Enroll Now', 'exms' );
        $class       = 'course-type-default';

        if ( isset( $type_data[ $course_type ] ) ) {
            $label       = $type_data[ $course_type ]['label'];
            $button_text = $type_data[ $course_type ]['button'];
            $class       = 'course-type-' . esc_attr( $course_type );
        }

        return array(
            'label'       => $label,
            'button_text' => $button_text,
            'class'       => $class,
        );
    }

    public function exms_template_redirect_vars() {

        if ( is_singular( 'exms-courses' ) ) {
            $course_id = get_the_ID();

            // prepare variables
            $title               = get_the_title( $course_id );
            $thumbnail_url       = get_the_post_thumbnail_url( $course_id, 'full' );
            $course_member_count = exms_get_course_member( $course_id ) ?: 0;
            $course_instructor_ids = exms_get_assign_instructor_ids( $course_id ) ?: [0];
            $instructor_avatars = '';
            $instructor_names   = '';
            
            if ( !empty( $course_instructor_ids ) && is_array( $course_instructor_ids ) ) {
                $display_names = [];

                foreach ( $course_instructor_ids as $user_id ) {
                    $user = get_userdata( $user_id );
                    if ( $user ) {
                        $name = ucwords( strtolower( $user->display_name ) );
                        $display_names[] = $name;

                        $instructor_avatars .= get_avatar( $user_id, 32, '', $name, ['class' => 'exms-instructor-avatar'] );
                    }
                }

                $instructor_names = implode( ', ', $display_names );
            }

            $course_instructor = [
                'avatars' => $instructor_avatars,
                'names'   => $instructor_names,
            ];
            $breadcrumb = exms_course_breadcrumb( $course_id );
            $course_lesson      = exms_get_course_lessons( $course_id ) ;
            $course_date         = exms_get_course_last_enroll( $course_id ) ?: __( 'No enrollments', 'exms' );
            $course_info         = exms_get_post_settings( $course_id );
            $is_enrolled         = exms_is_user_in_post( get_current_user_id(), $course_id );
            $structure           = get_option( 'exms_post_types', true );
            $course_label        = get_post_type_object( get_post_type( $course_id ) )->labels->singular_name ?? 'Course';
            $course_includes     = $structure ? exms_get_child_post_ids( $course_id, 'exms-courses', $structure ) : [];

            $type_data = $this->exms_get_course_type_data( $course_info, $course_info['parent_post_type'] ?? 'unknown' );
            $course_type = isset( $course_info['parent_post_type'] ) ? $course_info['parent_post_type'] : ''; 
            $video_url = isset( $course_info['video_url'] )? $course_info['video_url'] : '';
            $total_seat = isset( $course_info['seat_limit'] )? $course_info['seat_limit'] : 0;
            $seat_left = max( 0, $total_seat - $course_member_count );

            $percentage = $total_seat > 0 ? min( 100, ( $course_member_count / $total_seat ) * 100 ) : 0;
            $circumference = 2 * pi() * 35;
            $offset = $circumference - ( $percentage / 100 ) * $circumference;
            $type         = $type_data['label'];
            $button_text  = $type_data['button_text'];
            $dynamic_class = $type_data['class'];
            set_query_var( 'course_data', compact(
                'course_id', 'title', 'thumbnail_url', 'course_instructor', 'course_member_count',
                'course_date', 'course_info', 'is_enrolled', 'structure', 'course_label',
                'course_includes', 'total_seat', 'seat_left', 'percentage', 'circumference',
                'offset', 'type_data','type', 'button_text', 'dynamic_class', 'video_url','course_type','breadcrumb'
            ) );
        }
    }

    /**
     * Ajax callback: Enroll user to course or show payment popup
     */
    public function exms_enrolled_user_to_the_course() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $user_id     = get_current_user_id();
        $course_type = isset( $_POST['course_type'] ) ? lcfirst( sanitize_text_field( $_POST['course_type'] ) ) : '';
        $course_id   = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;

        if ( empty( $course_type ) || empty( $course_id ) ) {
            wp_send_json( array(
                'status'  => 'error',
                'message' => __( 'Invalid course details.', 'exms' ),
            ) );
        }

        if ( $course_type === 'free' && ! is_user_logged_in() ) {
            
            ob_start();
            include EXMS_TEMPLATES_DIR . '/frontend/course/login-modelbox-template.php';
            $popup_html = ob_get_clean();

            wp_send_json( array(
                'status'     => 'show_login_popup',
                'popup_html' => $popup_html,
            ) );
        }
        if ( $course_type === 'free' ) {

            $post_type = get_post_type( $course_id );
            $enroll = EXMS_PR_Fn::exms_enroll_user_to_content(
                $user_id,
                $course_id,
                $post_type,
                $user_id,
                'enrolled',
                0,
                current_time( 'timestamp' ),
                ''
            );

            if ( $enroll ) {
                wp_send_json( array(
                    'status'  => 'success',
                    'message' => __( 'You are successfully enrolled in the course!', 'exms' ),
                ) );
            } else {
                wp_send_json( array(
                    'status'  => 'already_enrolled',
                    'message' => __( 'You are already enrolled in this course.', 'exms' ),
                ) );
            }
        }

        // Paid course: show payment popup

        if( $course_type === 'paid' ) {

            $user_full_name = '';
            $user_email     = '';

            if( is_user_logged_in() ) {
                $current_user = wp_get_current_user();

                $first_name = get_user_meta( $current_user->ID, 'first_name', true );
                $last_name  = get_user_meta( $current_user->ID, 'last_name', true );

                $user_full_name = trim( $first_name . ' ' . $last_name );
                if( $user_full_name === '' ) {
                    $user_full_name = $current_user->display_name;
                }
                $user_email = $current_user->user_email;
            }

            // Get course price from database
            $course_info = exms_get_post_settings( $course_id );
            $course_price = isset( $course_info['parent_post_price'] ) ? intval( $course_info['parent_post_price'] ) : 0;
            $course_title = get_the_title( $course_id );

            // Get PayPal settings
            $exms_payment_options = get_option( 'exms_payment_settings' );
            $paypal_payee_email = isset( $exms_payment_options['paypal_vender_email'] ) ? $exms_payment_options['paypal_vender_email'] : '';

            ob_start();
            include EXMS_TEMPLATES_DIR . '/frontend/course/buy-course-modelbox-template.php';
            $popup_html = ob_get_clean();

            wp_send_json( array(
                'status'         => 'show_payment_popup',
                'message'        => __( 'Please choose a payment method to proceed.', 'exms' ),
                'popup_html'     => $popup_html,
                'user_name'      => $user_full_name,
                'user_email'     => $user_email,
                'course_id'      => $course_id,
                'course_price'   => $course_price,
                'course_title'   => $course_title,
                'paypal_payee'   => $paypal_payee_email,
            ) );
        }

        wp_send_json( array(
            'status'  => 'error',
            'message' => __( 'Unable to process your request.', 'exms' ),
        ) );
    }

    /**
     * AJAX callback for Mark Course as Complete
     */
    public function exms_mark_as_course_complete() {

        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'exms_ajax_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Invalid request. Nonce failed.' ) );
        }

        $course_id = isset( $_POST['course_id'] ) ? intval( $_POST['course_id'] ) : 0;
        $user_id   = get_current_user_id();

        if ( ! $course_id || ! $user_id ) {
            wp_send_json_error( array( 'message' => 'Invalid course or user.' ) );
        }

        $result = exms_process_course_mark_as_complete( $course_id, $user_id );

        if ( $result ) {
            
            $updated = exms_update_user_enrollment( $user_id, $course_id, 'completed', 100 );

            if ( $updated ) {
                wp_send_json_success( array( 'message' => 'Course marked as complete.' ) );
            } else {
                wp_send_json_error( array( 'message' => 'Enrollment record not found for this user.' ) );
            }
        }
    }

}

EXMS_COURSE::instance();
