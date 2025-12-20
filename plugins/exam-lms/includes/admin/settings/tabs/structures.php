<?php

/**
 * WP EXAMS - Structures Setting tab content
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Structures {

    /**
     * @var self
     */
    private static $instance;
    private $structures_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Structures ) ) {
            self::$instance = new EXMS_Structures;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'structures' ) {
                self::$instance->structures_page = true;
            }
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    public function hooks() {

        add_action( 'admin_enqueue_scripts' , [ $this,'exms_structure_enqueue'] );
        $this->exms_structures_html_content();
    }
    
    public function exms_structure_enqueue() {
        if ( !$this->structures_page ) {
            return false;
        }
        wp_enqueue_style( 'exms-structure-css', EXMS_ASSETS_URL . '/css/admin/setup-wizard/exms-setup-wizard.css', [], EXMS_VERSION, null );
        wp_enqueue_script( 'exms-structure-js', EXMS_ASSETS_URL . '/js/admin/setup-wizard/exms-setup-wizard.js', [ 'jquery' ], false, true );

        wp_localize_script( 'exms-structure-js', 'EXMS_SETUP_WIZARD', 
            [ 
                'ajaxURL'   => admin_url( 'admin-ajax.php' ),
                'security'  => wp_create_nonce( 'exms_ajax_nonce' ) ,
            ] 
        );
    }

    /**
     * Main function to call html content and tabs
     */
    private function exms_structures_html_content() {

        if( !$this->structures_page ) {
            return false;
        }

        /**
         * Include report data table
         */
        if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-structures-settings-template.php' ) ) {

            require_once EXMS_TEMPLATES_DIR . '/tabs/exms-structures-settings-template.php';
        }
    }
}

EXMS_Structures::instance();
