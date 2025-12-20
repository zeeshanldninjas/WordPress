<?php

/**
 * Template for wp exam setup wizard
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Setup_Wizard {

    /**
     * @var self
     */
    private static $instance;
    private $wizard_page = false;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Setup_Wizard ) ) {

            self::$instance = new EXMS_Setup_Wizard;
            if( isset( $_GET['page'] ) && $_GET['page'] === 'exms-setup-wizard' ) {
                self::$instance->wizard_page = true;
            }
            self::$instance->hooks();
        }
        return self::$instance;
    }

    private function hooks() {

        add_action( 'admin_enqueue_scripts' , [ $this,'exms_setup_wizard_enqueue'] );
        add_action( 'wp_ajax_exms_add_new_post_type', [ $this, 'exms_add_new_post_type' ] );
        add_action( 'wp_ajax_exms_save_course_structure_callback', [ $this, 'exms_save_course_structure_callback' ] );
        add_action('wp_ajax_exms_delete_custom_structure', [ $this, 'exms_delete_custom_structure' ]);
        add_action( 'wp_ajax_exms_wizard_save_general', [ $this, 'exms_wizard_save_general' ] );
        add_action( 'wp_ajax_exms_wizard_save_labels', [ $this, 'exms_wizard_save_labels' ] );
        add_action( 'init', [ $this, 'exms_create_exms_setup_new_post_type' ], 9 );
        add_action( 'wp_ajax_exms_delete_post_type', [ $this, 'exms_delete_setup_post_types' ] );
        add_action( 'wp_ajax_exms_save_setup_payments', [ $this, 'exms_save_setup_payments_data' ] );
        add_action( 'wp_ajax_exms_rename_post_type', [ $this, 'exms_rename_post_type_name' ] );
    }

    /**
     * WP Exam setup wizard menu page callback
     */
    public static function exms_display_setup_wizard() {

        if( !self::$instance->wizard_page ) {
            return false;
        }

        if( file_exists( EXMS_TEMPLATES_DIR . 'setup-wizard/exms-setup-template.php' ) ) {
            require EXMS_TEMPLATES_DIR . 'setup-wizard/exms-setup-template.php';
        }

        if( file_exists( EXMS_TEMPLATES_DIR . 'setup-wizard/exms-payment-modalbox-template.php' ) ) {
            require EXMS_TEMPLATES_DIR . 'setup-wizard/exms-payment-modalbox-template.php';
        }
        
        if( file_exists( EXMS_TEMPLATES_DIR . 'setup-wizard/course-structure-modalbox-template.php' ) ) {
            require EXMS_TEMPLATES_DIR . 'setup-wizard/course-structure-modalbox-template.php';
        }
        
        if( file_exists( EXMS_TEMPLATES_DIR . 'setup-wizard/course-structure-name-template.php' ) ) {
            require EXMS_TEMPLATES_DIR . 'setup-wizard/course-structure-name-template.php';
        }
    }

    public static function get_dynamic_structure_steps( $option_post_key ) {

        if( !self::$instance->wizard_page ) {
            return false;
        }
    
        $allowed_structure = get_option( 'exms_selected_structure' );
    
        if( empty( $allowed_structure ) ) {
            return false;
        }
    
        $post_types = get_option( $option_post_key, [] );
    
        if ( empty( $post_types ) ) {
            return false;
        }
    
        unset( $post_types['exms_quizzes'] );
    
        $steps = [];
        
        foreach ( $post_types as $post_type ) {
            $steps[] = $post_type['singular_name'] ?? '';
        }
    
        return ! empty( $steps ) ? $steps : false;
    }
  
    /**
     * Setup Functionality Files added and nonce creation
     */
    public function exms_setup_wizard_enqueue() {
        
        if ( !$this->wizard_page ) {
            return false;
        }
        
        wp_enqueue_style( 'exms-setup-wizard-css', EXMS_ASSETS_URL . '/css/admin/setup-wizard/exms-setup-wizard.css', [], EXMS_VERSION, null );
        wp_enqueue_script( 'exms-setup-wizard-js', EXMS_ASSETS_URL . '/js/admin/setup-wizard/exms-setup-wizard.js', [ 'jquery' ], false, true );

        wp_localize_script( 'exms-setup-wizard-js', 'EXMS_SETUP_WIZARD', 
            [ 
                'ajaxURL'   => admin_url( 'admin-ajax.php' ),
                'security'  => wp_create_nonce( 'exms_ajax_nonce' ),
                'customMessage' => __( 'Custom structure deleted. Please select one of the available course structures to continue.', 'exms' ),
                'customConfirmMessage' => __( 'Are you sure you want to delete the custom course structure?', 'exms' ),
                'structureDeleteMessage' => __( 'Error deleting structure.', 'exms' ),
                'ajaxErrorMessage' => __( 'Something went wrong. Try again.', 'exms' ),
                'paymentSaveMessage' => __( 'Payment Settings Save Successfully', 'exms' ),
                'enabled' => __( 'Enabled', 'exms' ),
                'configure' => __( 'Configure', 'exms' ),
                'requiredFields' => __( 'Please fill all required fields.', 'exms' ),
                'configurePayment' => __( 'To proceed, please configure at least one payment gateway or skip this step.', 'exms' ),
                'unChange' => __( 'No changes detected. Your payment settings are already up to date.', 'exms' ),
                'admin_url' => admin_url(),
            ] 
        );
    }

    /**
     * Rename post type name : Ajax
     */
    public function exms_rename_post_type_name() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $post_type = isset( $_POST['post_type_slug'] ) ? $_POST['post_type_slug'] : '';
        if( empty( $post_type ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Post Type not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $singular_name = isset( $_POST['post_type_name'] ) ? $_POST['post_type_name'] : '';
        if( empty( $singular_name ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Post Type name not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        if( isset( $post_types[$post_type] ) ) {

            $post_types[$post_type]['singular_name'] = $singular_name;
        }

        update_option( 'exms_post_types', $post_types );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    } 

    /**
     * Save payment setting data on db : Ajax
     */
    public function exms_save_setup_payments_data() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $paypal_data = isset( $_POST['paypal_data'] ) ? $_POST['paypal_data'] : '';
        $stripe_data = isset( $_POST['stripe_data'] ) ? $_POST['stripe_data'] : '';

        if( empty( $paypal_data ) && empty( $stripe_data ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Form data not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $data = Exms_Core_Functions::get_options( 'payment_settings' );
        if( empty( $data ) ) {
            $data = [];
        }

        if( $paypal_data && is_array( $paypal_data ) ) {
            foreach( $paypal_data as $paypal_key => $paypal_value ) {

                if( empty( $paypal_value ) ) {
                    continue;
                }

                $data[$paypal_key] = $paypal_value;
            }
        }

        if( $stripe_data && is_array( $stripe_data ) ) {

            foreach( $stripe_data as $stripe_key => $stripe_value ) {

                if( empty( $stripe_value ) ) {
                    continue;
                }

                $data[$stripe_key] = $stripe_value;
            }
        }

        Exms_Core_Functions::save_options( 'payment_settings', $data );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }   

    /**
     * Delete Post type on setup wizard : Ajax
     */
    public function exms_delete_setup_post_types() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );
        
        $response = [];

        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : [];
        if( empty( $post_type ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Post type not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }
    
        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        if( is_array( $post_types ) && isset( $post_types[$post_type] ) ) {

            unset( $post_types[$post_type] );
        }
        
        update_option( 'exms_post_types', $post_types );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Shows the existing post type in the menu, created in the setup wizard
     */
    public function exms_create_exms_setup_new_post_type() {

        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        if( empty( $post_types ) || ! is_array( $post_types ) ) {
            return false;
        }

        foreach( $post_types as $post_name => $post_type ) {

            $singular_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
            $plural_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
            $slug = isset( $post_type['slug'] ) ? $post_type['slug'] : '';
            $post_type_name = isset( $post_type['post_type_name'] ) ? $post_type['post_type_name'] : '';
            $show_in_menu = isset( $post_type['show_in_menu'] ) ? $post_type['show_in_menu'] : '';
        
            echo Exms_Post_Types_Functions::create_exms_post_type( $singular_name, $plural_name, $slug, $post_type_name, $show_in_menu );
        }
    }

    /**
     * Save new Post Type meta on option db : Ajax
     */
    public function exms_wizard_save_labels() {
        
        $data = Exms_Core_Functions::get_options( 'labels' );
        $bug_data = Exms_Core_Functions::get_options( 'bug' );
        $dynamic_labels = Exms_Core_Functions::get_options( 'dynamic_labels' );
        if( empty( $data ) ) {
            $data = [];
        }

        if ( !is_array( $bug_data ) ) {
            $bug_data = [];
        }
        
        if( isset( $_POST['exms_submitted_essays'] ) ) {
            $data['exms_submitted_essays'] = sanitize_text_field( $_POST['exms_submitted_essays'] );
        }
        
        if( isset( $_POST['exms_user_report'] ) ) {
            $data['exms_user_report'] = sanitize_text_field( $_POST['exms_user_report'] );
        }
        
        if( isset( $_POST['exms_quiz_report'] ) ) {
            $data['exms_quiz_report'] = sanitize_text_field( $_POST['exms_quiz_report'] );
        }

        if( isset( $_POST['exms_quizzes'] ) ) {
            $data['exms_quizzes'] = sanitize_text_field( $_POST['exms_quizzes'] );
            $post_types = get_option( 'exms_post_types', [] );
            if ( is_array( $post_types ) && isset( $post_types['exms-quizzes'] ) ) {
                $label = $data['exms_quizzes'];
                $post_types['exms-quizzes']['singular_name'] = $label;
                $post_types['exms-quizzes']['plural_name']   = $label . 's';
                update_option( 'exms_post_types', $post_types );
            }
        }

        if( isset( $_POST['exms_certificates'] ) ) {
            $data['exms_certificates'] = sanitize_text_field( $_POST['exms_certificates'] );
        }

        if( isset( $_POST['exms_questions'] ) ) {
            $data['exms_questions'] = sanitize_text_field( $_POST['exms_questions'] );
        }

        if( isset( $_POST['exms_qroup'] ) ) {
            $data['exms_qroup'] = sanitize_text_field( $_POST['exms_qroup'] );
        }
        
        if( isset( $_POST['dfce_lesson_enable'] ) ) {
            $bug_data['dfce_lesson_enable'] = sanitize_text_field( $_POST['dfce_lesson_enable'] );
        }

       if ( isset( $_POST['dynamic_labels'] ) && is_array( $_POST['dynamic_labels'] ) ) {
            $new_dynamic_labels = [];
            foreach ( $_POST['dynamic_labels'] as $key => $val ) {
                $new_dynamic_labels[ sanitize_key( $key ) ] = sanitize_text_field( $val );
            }
            Exms_Core_Functions::save_options( 'dynamic_labels', $new_dynamic_labels );
            $post_types = get_option( 'exms_post_types', [] );
            if ( is_array( $post_types ) && ! empty( $post_types ) ) {
                foreach ( $new_dynamic_labels as $key => $label ) {
                    if ( isset( $post_types[ $key ] ) ) {
                        $label = sanitize_text_field( $label );
                        $post_types[ $key ]['singular_name'] = $label;
                        $post_types[ $key ]['plural_name']   = $label . 's';
                    }
                }
                update_option( 'exms_post_types', $post_types );
            }
        } else {
            Exms_Core_Functions::save_options( 'dynamic_labels', [] );
        }

        Exms_Core_Functions::save_options( 'bug', $bug_data );
        Exms_Core_Functions::save_options( 'labels', $data );
        echo json_encode( $data );
        exit;
    }

    /**
     * Save new Post Type meta on option db : Ajax
     */
    public function exms_wizard_save_general() {
        
        $data = Exms_Core_Functions::get_options( 'general_settings' );
        if( empty( $data ) ) {
            $data = [];
        }

        if( isset( $_POST['uninstall'] ) ) {
            $data['exms_uninstall'] = ( 'on' == $_POST['uninstall'] ) ? 'on' : 'off';
        } else {
            $data['exms_uninstall'] = 'off';
        }

        if( isset( $_POST['dashboard'] ) ) {
            $data['dashboard_page'] = (int) sanitize_text_field( $_POST['dashboard'] );
        }

        Exms_Core_Functions::save_options( 'general_settings', $data );
        echo json_encode($data);
        exit;
    }

    /**
     * Save new Post Type meta on option db: Ajax
     * Defined course structure & 
     * custom course structure post types will be created according to the steps
     */
    public function exms_add_new_post_type() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];
        $structure = isset( $_POST['structure'] ) ? sanitize_text_field( $_POST['structure'] ) : 'default';
        $steps = isset( $_POST['steps'] ) && is_array( $_POST['steps'] ) ? array_map( 'sanitize_text_field', $_POST['steps'] ) : ['Quizzes'];
        
        if ( empty( $structure ) || empty( $steps  )) {
            wp_send_json(['status' => 'false', 'message' => 'Invalid structure or steps.']);
        }

        $dynamic_labels = [];
        if ( isset( $_POST['dynamic_labels'] ) && is_array( $_POST['dynamic_labels'] ) ) {
            foreach ( $_POST['dynamic_labels'] as $key => $label ) {
                $dynamic_labels[ sanitize_key( $key ) ] = sanitize_text_field( $label );
            }
        }

        update_option( 'exms_dynamic_labels', $dynamic_labels );

        $new_post_types = [];
        $existing_post_types = EXMS_Setup_Functions::get_setup_post_types();
        $existing_post_types = is_array($existing_post_types) ? $existing_post_types : [];

        $non_structure_post_types = [];
        foreach ( $existing_post_types as $slug => $data ) {
            if ( strpos( $slug, 'exms-') !== 0) {
                $non_structure_post_types[$slug] = $data;
            }
        }

        $fixed_slugs = ['exms-courses', 'exms-lessons', 'exms-topics'];
        $step_pointer = 0;

        if( $structure === 'custom' ) {
            $original_steps = $steps;

            $quiz_label = 'Quizzes';
            foreach ( $original_steps as $step_label ) {
                if ( in_array( strtolower( trim( $step_label ) ), [ 'quiz', 'quizzes' ], true ) ) {
                    $quiz_label = sanitize_text_field( $step_label );
                    break;
                }
            }

            $steps = array_filter( $steps, function( $step_label ) {
                $label = strtolower( trim( $step_label ) );
                return $label !== 'quiz' && $label !== 'quizzes';
            });
            $steps = array_values( $steps );

            $steps_clean = [];
            $new_post_types = [];
            $step_pointer = 0;

            foreach ( $fixed_slugs as $index => $fixed_slug ) {
                if ( ! isset( $steps[ $step_pointer ] ) ) {
                    break;
                }

                $label = sanitize_text_field( $steps[ $step_pointer ] );
                $steps_clean[] = $label;

                $existing = $existing_post_types[ $fixed_slug ] ?? [];

                $new_post_types[ $fixed_slug ] = [
                    'singular_name'  => $label,
                    'plural_name'    => $label . 's',
                    'slug'           => $fixed_slug,
                    'post_type_name' => $existing['post_type_name'] ?? str_replace( '_', '-', $fixed_slug ),
                    'show_in_menu'   => EXMS_MENU,
                    'parent'         => ( $index === 0 ) ? '' : $fixed_slugs[ $index - 1 ]
                ];

                $step_pointer++;
            }

            $remaining_steps = array_slice( $steps, $step_pointer );
            $existing_custom_post_types = [];

            foreach ( $existing_post_types as $slug => $data ) {
                if ( ! in_array( $slug, $fixed_slugs ) && strpos( $slug, 'exms-' ) === 0 && $slug !== 'exms-quizzes' ) {
                    $existing_custom_post_types[] = $slug;
                }
            }

            $i = 0;
            $prev_slug = end( $fixed_slugs );

            foreach ( $remaining_steps as $index => $label ) {
                $label = sanitize_text_field( $label );
                $steps_clean[] = $label;

                if ( isset( $existing_custom_post_types[ $i ] ) ) {
                    $existing_slug = $existing_custom_post_types[ $i ];
                    $existing = $existing_post_types[ $existing_slug ] ?? [];

                    $new_post_types[ $existing_slug ] = [
                        'singular_name'  => $label,
                        'plural_name'    => $label . 's',
                        'slug'           => $existing_slug,
                        'post_type_name' => $existing['post_type_name'] ?? str_replace( '_', '-', $existing_slug ),
                        'show_in_menu'   => EXMS_MENU,
                        'parent'         => $prev_slug
                    ];

                    $prev_slug = $existing_slug;
                } else {
                    $custom_slug = 'exms-' . sanitize_title( $label );

                    $new_post_types[ $custom_slug ] = [
                        'singular_name'  => $label,
                        'plural_name'    => $label . 's',
                        'slug'           => $custom_slug,
                        'post_type_name' => str_replace( '_', '-', $custom_slug ),
                        'show_in_menu'   => EXMS_MENU,
                        'parent'         => $prev_slug
                    ];

                    $prev_slug = $custom_slug;
                }

                $i++;
            }

            $existing_quiz = $existing_post_types['exms-quizzes'] ?? [];

            $new_post_types['exms-quizzes'] = [
                'singular_name'  => $quiz_label,
                'plural_name'    => $quiz_label . 's',
                'slug'           => 'exms-quizzes',
                'post_type_name' => $existing_quiz['post_type_name'] ?? 'exms-quizzes',
                'show_in_menu'   => EXMS_MENU,
                'parent'         => $prev_slug
            ];

            foreach ( $non_structure_post_types as $slug => $data ) {
                $new_post_types[ $slug ] = $data;
            }

            update_option( 'exms_post_types', $new_post_types );
            update_option( 'exms_custom_post_types', $new_post_types );
            update_option( 'exms_selected_structure', 'custom' );
        } else {

            $quiz_label = 'Quizzes';
            foreach( $steps as $i => $step ) {
                if( in_array( strtolower( trim( $step ) ), [ 'quiz', 'quizzes' ], true ) ) {
                    $quiz_label = sanitize_text_field( $step );
                    unset( $steps[$i] );
                    $steps = array_values($steps);
                    break;
                }
            }

            if( !in_array( 'quizzes', array_map( 'strtolower', $steps ), true ) ) {
                $steps[] = $quiz_label;
            }

            $step_pointer = 0;
            $last_slug = '';
            foreach( $fixed_slugs as $index => $fixed_slug ) {
                if( $step_pointer >= count( $steps ) - 1 ) break;

                $label = $steps[$step_pointer];
                $step_pointer++;
                $post_type_name = str_replace( '_', '-', $fixed_slug );
                $new_post_types[$fixed_slug] = [
                    'singular_name'   => $label,
                    'plural_name'     => $label . 's',
                    'slug'            => $fixed_slug,
                    'post_type_name'  => $post_type_name,
                    'show_in_menu'    => EXMS_MENU,
                    'parent'          => ( $index === 0 ) ? '' : $last_slug
                ];

                $last_slug = $fixed_slug;
            }

            $quizzes_slug = 'exms-quizzes';
            $post_type_name = 'exms-quizzes';

            $new_post_types[$quizzes_slug] = [
                'singular_name'   => $quiz_label,
                'plural_name'     => $quiz_label . 's',
                'slug'            => $quizzes_slug,
                'post_type_name'  => $post_type_name,
                'show_in_menu'    => EXMS_MENU,
                'parent'          => $last_slug
            ];

            foreach( $non_structure_post_types as $slug => $data ) {
                if( isset( $new_post_types[ $slug ] ) ) {
                    $new_post_types[ $slug ] = array_merge(
                        $new_post_types[ $slug ],
                        array_diff_key( $data, array_flip( [ 'singular_name', 'plural_name' ] ) )
                    );
                } else {
                    $new_post_types[ $slug ] = $data;
                }
            }

            update_option( 'exms_post_types', $new_post_types );
            update_option( 'exms_selected_structure', $structure );
        }
        $content = EXMS_Setup_Functions::get_create_post_types_html($new_post_types);

        $quiz_singular = '';
        if( isset( $new_post_types['exms-quizzes'] ) ) {
            $quiz_singular = $new_post_types['exms-quizzes']['singular_name'];
            $existing_labels = Exms_Core_Functions::get_options('labels');

            if( is_array( $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
                $existing_labels['exms_quizzes']        = $quiz_singular;
                Exms_Core_Functions::save_options( 'labels', $existing_labels );
            }
        }

        wp_send_json([
            'status'  => 'true',
            'content' => $content,
            'post_types'  => $new_post_types,
            'quiz_name'  => $quiz_singular,
        ]);
    }

    /**
     * Custom course structure post type will be created
     * Quizzes will always add in the end of the steps
     * @return void
     */
    public function exms_save_course_structure_callback() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $structure_data = isset( $_POST['structure_data'] ) ? $_POST['structure_data'] : [];
        if( empty( $structure_data ) || !is_array( $structure_data ) ) {
            wp_send_json( [ 'status' => 'false', 'message' => 'Invalid structure data.' ] );
        }
        
        $dynamic_labels = [];
        if ( isset( $_POST['dynamic_labels'] ) && is_array( $_POST['dynamic_labels'] ) ) {
            foreach ( $_POST['dynamic_labels'] as $key => $label ) {
                $dynamic_labels[ sanitize_key( $key ) ] = sanitize_text_field( $label );
            }
        }

        update_option( 'exms_dynamic_labels', $dynamic_labels );

        $steps = [];
        $new_post_types = [];
        $selected_structure = 'custom';
        update_option( 'exms_selected_structure', $selected_structure );

        $fixed_slugs = [ 'exms-courses', 'exms-lessons', 'exms-topics' ];

        $previous_slug = '';
        foreach( $structure_data as $index => $step ) {
            $name = sanitize_text_field( $step['name'] );
            if ( empty( $name ) ) continue;

            $steps[] = $name;

            if( $index < 3 ) {
                $fixed_slug = $fixed_slugs[ $index ];
                $post_type_name = str_replace( '_', '-', $fixed_slug );

                $new_post_types[ $fixed_slug ] = [
                    'singular_name'  => $name,
                    'plural_name'    => $name . 's',
                    'slug'           => $fixed_slug,
                    'post_type_name' => $post_type_name,
                    'show_in_menu'   => EXMS_MENU,
                    'parent'         => $previous_slug,
                ];

                $previous_slug = $fixed_slug;
            } else {
                $custom_slug = 'exms-' . sanitize_title( $name );
                $post_type_name = str_replace( '_', '-', $custom_slug );

                $new_post_types[ $custom_slug ] = [
                    'singular_name'  => $name,
                    'plural_name'    => $name . 's',
                    'slug'           => $custom_slug,
                    'post_type_name' => $post_type_name,
                    'show_in_menu'   => EXMS_MENU,
                    'parent'         => $previous_slug,
                ];

                $previous_slug = $custom_slug;
            }
        }

        $quiz_key = 'exms-quizzes';
        $quiz_found_in_steps = false;
        foreach( $steps as $i => $step_name ) {
            if( strtolower( $step_name ) === 'quiz' || strtolower( $step_name ) === 'quizzes' ) {
                unset( $steps[ $i ] );
                $quiz_found_in_steps = true;
            }
        }
        $steps = array_values( $steps );

        if( isset( $new_post_types[ $quiz_key ] ) ) {
            unset( $new_post_types[ $quiz_key ] );
        }

        if ( ! empty( $new_post_types ) ) {
            $keys = array_keys( $new_post_types );
            $last_step_slug = end( $keys );
        } else {
            $last_step_slug = '';
        }

        $steps[] = 'Quiz';

        $new_post_types[ $quiz_key ] = [
            'singular_name'  => 'Quiz',
            'plural_name'    => 'Quizzes',
            'slug'           => $quiz_key,
            'post_type_name' => $quiz_key,
            'show_in_menu'   => EXMS_MENU,
            'parent'         => $last_step_slug,
        ];

        update_option( 'exms_post_types', $new_post_types );
        update_option( 'exms_custom_post_types', $new_post_types );

        $quiz_singular = '';
        if( isset( $new_post_types['exms-quizzes'] ) ) {
            $quiz_singular = $new_post_types['exms-quizzes']['singular_name'];
            $existing_labels = Exms_Core_Functions::get_options('labels');

            if( is_array( $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
                $existing_labels['exms_quizzes']        = $quiz_singular;
                Exms_Core_Functions::save_options( 'labels', $existing_labels );
            }
        }

        wp_send_json( [
            'status' => 'true',
            'steps'  => $steps,
            'post_types'  => $new_post_types,
            'quiz_name'  => $quiz_singular,
        ] );
    }

    /**
     * Delete custom course structure from the options table
     * @return void
     */
    public function exms_delete_custom_structure() {
        
        check_ajax_referer( 'exms_ajax_nonce', 'security' );
        delete_option( 'exms_custom_post_types' );
        $structure = get_option( 'exms_selected_structure' );
        wp_send_json([
            'status' => 'success',
            'structure' => $structure,
            'message' => 'Custom structure removed successfully.'
        ]);
    }

}

EXMS_Setup_Wizard::instance();