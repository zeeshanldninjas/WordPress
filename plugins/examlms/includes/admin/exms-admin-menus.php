<?php
/**
 * WP Exam - Admin Hooks
 *
 * All admin panel related hooks
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Admin_Menus
 *
 * Base class to define wp exam admin menus
 */
class Exms_Admin_Menus {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Admin_Menus ) ) {

            self::$instance = new Exms_Admin_Menus;
            self::$instance->constant();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define menu page constant
     */
    private function constant() {

        define( 'EXMS_MENU', 'exms_menu' );
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'admin_menu', [ $this, 'exms_admin_menu_pages' ] );
    }

    /**
     * Adding WP exam menu/submenu pages
     */
    public function exms_admin_menu_pages() {
        
        $user = wp_get_current_user();
        if( in_array( 'subscriber', (array) $user->roles ) || in_array( 'exms_student', (array) $user->roles ) ) {
            return;
        }
        global $submenu;

        $labels = Exms_Core_Functions::get_options( 'labels' );
        $exms_wp_exams  = isset( $labels[ 'exms_wp_exams' ] ) ? $labels[ 'exms_wp_exams' ] : __( 'WP Exams', 'exms' );
        $exms_settings   = isset( $labels[ 'exms_settings' ] ) ? $labels[ 'exms_settings' ] : __( 'Settings', 'exms' );
        $exms_submitted_essays  = isset( $labels[ 'exms_submitted_essays' ] ) ? $labels[ 'exms_submitted_essays' ]  : __( 'Submitted Essays', 'exms' );
        $exms_user_report  = isset( $labels[ 'exms_user_report' ] ) ? $labels[ 'exms_user_report' ]  : __( 'User Report', 'exms' );
        $exms_report  = isset( $labels[ 'exms_report' ] ) ? $labels[ 'exms_report' ]  : __( 'Report', 'exms' );
        $exms_transactions  = isset( $labels[ 'exms_transactions' ] ) ? $labels[ 'exms_transactions' ]  : __( 'Transactions', 'exms' );
        $exms_import_export  = isset( $labels[ 'exms_import_export' ] ) ? $labels[ 'exms_import_export' ]  : __( 'Import/Export', 'exms' );
        $exms_setup_wizard  = isset( $labels[ 'exms_setup_wizard' ] ) ? $labels[ 'exms_setup_wizard' ]  : __( 'Setup Wizard', 'exms' );

        /**
         * Add Admin main page
         */
        add_menu_page( __( 'WP Exams', 'exms' ), __( 'WP Exams', 'exms' ), 'manage_options', EXMS_MENU, false, 'dashicons-welcome-learn-more', 10 );

        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        $exms_quizzes  = isset( $labels[ 'exms_quizzes' ] ) ?     $labels[ 'exms_quizzes' ]  : __( 'Quizzes', 'wp_exams' );
        $exms_qroup  = isset( $labels[ 'exms_qroup' ] ) ?     $labels[ 'exms_qroup' ]  : __( 'Groups', 'wp_exams' );
        $exms_questions  = isset( $labels[ 'exms_questions' ] ) ?     $labels[ 'exms_questions' ]  : __( 'Questions', 'wp_exams' );

        $defautl_post_types = [
            [ $exms_questions, $exms_questions, 'exms-question', 'exms-questions', 'exms_menu' ],
            [ $exms_qroup, $exms_qroup, 'exms-groups', 'exms-groups', 'exms_menu' ],
        ];

        $formatted_post_types = [];

        foreach ( $defautl_post_types as $type ) {
            $formatted_post_types[ $type[3] ] = [
                'singular_name'  => $type[0],
                'plural_name'    => $type[1],
                'slug'           => $type[2],
                'post_type_name' => $type[3],
                'show_in_menu'   => $type[4],
            ];
        }

        if( ( is_array( $post_types ) && ! empty( $post_types ) ) && ( is_array( $formatted_post_types ) && ! empty( $formatted_post_types ) ) ) {

            $post_types = $post_types + $formatted_post_types;
        } else {
            
            $formatted_post_types['exms_quizzes'] = [
                'singular_name'  => $exms_quizzes,
                'plural_name'    =>  $exms_quizzes . 's',
                'slug'           => 'exms-quizzes',
                'post_type_name' => 'exms-quizzes',
                'show_in_menu'   => 'exms_menu',
            ];
            $post_types = $formatted_post_types;
        }
        
        if( is_array( $post_types ) && ! empty( $post_types ) ) {
            foreach( $post_types as $post_type ) {

                $post_type_name = isset( $post_type['post_type_name'] ) ? $post_type['post_type_name'] : '';
                $singular_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
                // Make sure Courses CPT submenu shows under WP Exams
                $submenu[EXMS_MENU][] = array(
                    $singular_name,
                    'edit_posts',
                    'edit.php?post_type='.$post_type_name
                );

                // Optional: Remove default Courses submenu if needed
                unset($submenu['edit.php?post_type='.$post_type_name]);
            }
        }

        /**
         * Add settings page under main menu
         */
        add_submenu_page( EXMS_MENU, $exms_settings, $exms_settings, 'manage_options', 'exms-settings', [ 'EXMS_Settings', 'exms_settings_page_output'] );

        /**
         * Add submitted essay page under main menu
         */
        // add_submenu_page( EXMS_MENU, $exms_submitted_essays, $exms_submitted_essays, 'manage_options', 'exms_submitted_essays', [ 'WP_EXAMS_Submitted_Essays', 'exms_submitted_essays' ] );

        /**
         * Add user detailed report page 
         */
        // add_submenu_page( EXMS_MENU, $exms_user_report, $exms_user_report, 'manage_options', 'exms_user_report', [ 'WP_EXAMS_user_report', 'exms_user_report' ] );

        /**
         * Add quiz detailed report page
         */
        add_submenu_page( EXMS_MENU, $exms_report, $exms_report, 'manage_options', 'exms-reports', [ EXMS_All_Reports::instance() , 'exms_report_html_content' ] );

        /**
         * Add transaction page under main menu
         */
        add_submenu_page( EXMS_MENU, $exms_transactions, $exms_transactions, 'manage_options', 'exms-transaction', [ 'EXMS_Transactions', 'exms_transaction_page_html' ] );
        
        /**
         * Add import/export page under main menu
         */
        // add_submenu_page( EXMS_MENU, $exms_import_export, $exms_import_export, 'manage_options', 'import_export', [ 'WP_EXAMS_Import_Export', 'exms_import_export_content'] );

        /**
         * Add setup wizard page under main menu
         */
        add_submenu_page( EXMS_MENU, $exms_setup_wizard, $exms_setup_wizard, 'manage_options', 'exms-setup-wizard', [ 'EXMS_Setup_Wizard', 'exms_display_setup_wizard' ] );
    }
}

/**
 * Initialize Exms_Admin_Menus
 */
// add_action( 'plugins_loaded', [ 'Exms_Admin_Menus', 'instance' ] );
Exms_Admin_Menus::instance();