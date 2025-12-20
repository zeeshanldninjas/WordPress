<?php
/**
 * Display reports table
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Reports {

    /**
     * @var self
     */
    private static $instance;
    private $reports_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Reports ) ) {
            self::$instance = new EXMS_Reports;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'reports' ) {
                self::$instance->reports_page = true;
            }
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        $this->exms_report_html_content();
    }

    /**
     * Main function to call html content and tabs
     */
    private function exms_report_html_content() {

        if( !$this->reports_page ) {
            return false;
        }

        /**
         * Submenu tabs to see reports
         */
        $tabs = [ 'Quizzes', 'Students', 'Instructors' ];
        exms_create_submenu_tabs( $tabs, false, '' );

        /**
         * Include report data table
         */
        if( file_exists( EXMS_DIR . 'includes/admin/settings/tabs/reports-data-table.php' ) ) {

            require_once EXMS_DIR . 'includes/admin/settings/tabs/reports-data-table.php';
        }       
    }
}

EXMS_Reports::instance();
