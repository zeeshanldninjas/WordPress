<?php
/**
 * WP Exam - Post types
 *
 * All post type related hooks
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Post_Types
 *
 * Base class to create post type & meta boxes
 */
class Exms_Post_Types {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Post_Types ) ) {

            self::$instance = new Exms_Post_Types;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define Hooks
     */
    public function hooks() {

        add_action( 'init', [ $this, 'exms_create_post_types' ] );
        add_action( 'add_meta_boxes', [ $this, 'exms_admin_metaboxes' ], 10, 2 );
        add_action( 'save_post', [ $this, 'exms_save_metaboxes_meta_data' ], 10, 3 );
        add_action( 'save_post', [ $this, 'exms_reset_posts' ], 99999 );
        add_action( 'init', [ $this, 'exms_set_rewrite_rule' ] );
    }

    /**
     * set rewrite rules
     */
    public function exms_set_rewrite_rule() {

        $current_permalink = exms_get_current_url();
        $current_permalink = array_filter( explode( '/', $current_permalink ) );
        $post_slug = end( $current_permalink ); 
        $slug_post_type = exms_get_post_type_by_slug( $post_slug );

        if( 'exms-quizzes' != $slug_post_type ) {

            $post_types = EXMS_Setup_Functions::get_setup_post_types();

            if ( is_array( $post_types ) && ! empty( $post_types ) ) {
                foreach ( $post_types as $key => $post_type ) {

                    if ( in_array( $key, [ 'exms-courses', 'exms-quizzes' ] ) ) {
                        continue;
                    }

                    $pattern = '^exms-courses/';
                    $depth   = 1;
                    $parent  = $post_type['parent'];
                    $chain   = [];

                    while ( $parent && isset( $post_types[$parent] ) ) {
                        array_unshift( $chain, $post_types[$parent]['slug'] );
                        $parent = $post_types[$parent]['parent'];
                    }

                    foreach ( $chain as $slug ) {
                        $pattern .= '([^/]+)/';
                        $depth++;
                    }

                    $pattern .= '([^/]+)/?$';
                    $name_match = '$matches[' . $depth . ']';
                    
                    add_rewrite_rule(
                        $pattern,
                        'index.php?post_type=' . $post_type['post_type_name'] . '&name=' . $name_match,
                        'top'
                    );
                }
            }

        } else {

            // 3. Course → Lesson → Quiz
            add_rewrite_rule(
                '^exms-courses/([^/]+)/([^/]+)/([^/]+)/?$',
                'index.php?post_type=exms-quizzes&name=$matches[3]',
                'top'
            );

            // 4. Course → Lesson → Topic → Quiz
            add_rewrite_rule(
                '^exms-courses/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$',
                'index.php?post_type=exms-quizzes&name=$matches[4]',
                'top'
            );

            // 5. Course → Quiz (direct quiz inside course)
            add_rewrite_rule(
                '^exms-courses/([^/]+)/([^/]+)/?$',
                'index.php?post_type=exms-quizzes&name=$matches[2]',
                'bottom'
            );
        }
        flush_rewrite_rules();
    }

    /**
     * Reset permlinks after save
     */
    public function exms_reset_posts() {
        flush_rewrite_rules();
    }

    /**
     * Create/Load all post types 
     */
    public function exms_create_post_types() {

        if( method_exists( 'Exms_Post_Types_Functions', 'create_exms_post_type' ) ) {
        
            $labels = Exms_Core_Functions::get_options( 'labels' );
            $exms_group  = isset( $labels[ 'exms_qroup' ] ) ?     $labels[ 'exms_qroup' ]  : __( 'Groups', 'exms' );
            $exms_questions  = isset( $labels[ 'exms_questions' ] ) ?     $labels[ 'exms_questions' ]  : __( 'Questions', 'exms' );
            $exms_quizzes  = isset( $labels[ 'exms_quizzes' ] ) ?     $labels[ 'exms_quizzes' ]  : __( 'Quizzes', 'exms' );

            $post_types = [
                [ $exms_questions, $exms_questions, 'exms_question', 'exms-questions', 'exms_menu' ],
                [ $exms_group, $exms_group, 'exms-groups', 'exms-groups', 'exms_menu' ],
                [ $exms_quizzes, $exms_quizzes, 'exms-quizzes', 'exms-quizzes', 'exms_menu' ]
            ];

            if( is_array( $post_types ) && ! empty( $post_types ) ) {

                foreach( $post_types as $pt ) {

                    Exms_Post_Types_Functions::create_exms_post_type( $pt[0], $pt[1], $pt[2], $pt[3], $pt[4] );
                }
            }
        } 
    }

    /**
     * Add metabox to create questions on post type page
     */
    public function exms_admin_metaboxes( $post_type, $post ) {

        /**
         * Metabox for badge PDF options
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-certificates-pdf-opts', __( 'WP Examps Certificate Options', 'exms' ),[ 'EXMS_certificates', 'exms_pdf_options' ], 'exms_certificates', 'normal', 'high', 'exms_quiz_certificates' );

        /**
         * Metabox for point type names
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-point-type-data', __( 'Point Type Data', 'exms' ), [ 'EXMS_Point_Type', 'exms_point_type_data_html' ], 'exms_points', 'normal', 'high', '' );

        /**
         * Metabox for assign quiz to certificate
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-certificate-to-quiz', __( 'Certificate Assign/Unassign to Quiz', 'exms' ),[ 'EXMS_certificates', 'exms_quiz_certificate_callback_html' ], 'exms_certificates', 'normal', 'high', 'exms_quiz_certificates' );
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-certificate-to-course', __( 'Certificate Assign/Unassign to Course', 'exms' ),[ 'EXMS_certificates', 'exms_course_certificate_callback_html' ], 'exms_certificates', 'normal', 'high', 'exms_course_certificates' );
    }

    /**
     * Save metaboxes meta data to post meta
     *
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function exms_save_metaboxes_meta_data( $post_id, $post, $update ) {

        if( method_exists( 'Exms_Post_Types_Functions', 'save_exms_meta_boxes' ) ) {

            $meta_keys = [];

            if( isset( $_POST ) && ! empty( $_POST ) ) {

                foreach( $_POST as $key => $meta_data ) {

                    if( stripos( $key, 'exms' ) !== false ) {

                        $meta_keys[] = $key; 
                    }
                }
            } 

            Exms_Post_Types_Functions::save_exms_meta_boxes( $post_id, $meta_keys );
        }
    }
}

/**
 * Initialize Exms_Post_Types
 */
Exms_Post_Types::instance();