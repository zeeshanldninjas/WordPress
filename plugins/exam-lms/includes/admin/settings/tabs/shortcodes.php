<?php
/**
 * Display all shortcodes
 */
if( ! defined( 'ABSPATH' ) ) exit; 

class EXMS_Shortcodes {

    /**
     * @var self
     */
    private static $instance;
    private $shortcodes_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Shortcodes ) ) {
            self::$instance = new EXMS_Shortcodes;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'shortcodes' ) {
                self::$instance->shortcodes_page = true;
            }
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        $this->exms_shortcodes_html_content();
    }

    /**
     * Main function to call html content and tabs
     */
    private function exms_shortcodes_html_content() {

        if( !$this->shortcodes_page ) {
            return false;
        }

        /**
         * Include report data table
         */
		if( file_exists( EXMS_TEMPLATES_DIR . 'tabs/exms-shortcodes-settings-template.php' ) ) {

            $pages = get_pages();
            $selected_page = get_option( 'exms_quiz_review_page', '' );
			require_once EXMS_TEMPLATES_DIR . 'tabs/exms-shortcodes-settings-template.php';
		}            
    }
}

EXMS_Shortcodes::instance();