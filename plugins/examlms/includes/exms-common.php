<?php
/**
 * Common page
 */
class EXMS_COMMON {
    
    /**
     * Define of instance
     */
    private static $instance = null;

    /**
      * Define the instance
     */
    public static function instance(): EXMS_COMMON {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_COMMON ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     *  Hooks that are used in the class
     */
    private function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_enqueue_common_scripts' ] );
    }

    /**
     * enqueue course scripts file
     */
    public function exms_enqueue_common_scripts() {

        // wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'wp-exams-common', EXMS_ASSETS_URL . 'css/exms-common.css', [], EXMS::VERSION, null );

        // wp_enqueue_script( 'wp-exams-course-js', EXMS_ASSETS_URL . 'js/frontend/exms-course-page.js', [ 'jquery' ], '', true );

        // wp_localize_script( 'wp-exams-course-js', 'EXMS', [ 
        //     'ajaxURL'                       => admin_url( 'admin-ajax.php' ),
        //     'security'                      => wp_create_nonce( 'exms_ajax_nonce' ),
        //     'course_detail_icon_right'            => EXMS_ASSETS_URL . 'imgs/rightbar-right-arrow.svg',            
        //     'course_detail_icon_left'            => EXMS_ASSETS_URL . 'imgs/rightbar-left-arrow.svg',            
        // ] );
    }
}

EXMS_COMMON::instance();
