<?php
/**
 * WP Exam - Admin Hooks
 *
 * All admin panel related hooks
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Admin_Hooks
 *
 * Base class to define hooks related to admin panel 
 */
class Exms_Admin_Hooks {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Admin_Hooks ) ) {

            self::$instance = new Exms_Admin_Hooks;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define Hooks
     */
    public function hooks() {

        add_action( 'init', [ $this, 'exms_add_user_roles' ] );
        add_action( 'admin_notices', [ $this, 'exms_delete_admin_notice' ] );
        add_filter( 'admin_footer_text', [ $this, 'exms_change_footer_text' ] );
        add_filter( 'plugin_action_links_'. EXMS_BASE_DIR, [ $this, 'exms_plugin_setting_links' ] );      
        add_action( 'activated_plugin', [ $this, 'exms_activation_redirect' ] );
        add_action( 'admin_notices', [ $this, 'exms_configure_payment_notice' ] );
        add_filter( 'block_categories_all', [ $this, 'register_block_category' ], 10, 2);
    }

    /**
     * Registers the block category
     *
     * @param $categories
     * @param $post
     *
     * @return none
     */
    function register_block_category( $block_categories, $block_editor_context ) {

        $new_cats = array_merge(
            array(
                array(
                    'slug'  => 'exms-exams-blocks',
                    'title' => __( 'WP-Exams', 'exms' ),
                    'icon'  => ''
                ),
            ),

            $block_categories
        );

        return $new_cats;
    }

    /**
     * Redirect to setup page once plugin is activated
     * 
     * @param $plugin
     */
    public function exms_activation_redirect( $plugin ) {

        if( $plugin == EXMS_BASE_DIR ) {

            wp_redirect( admin_url( 'admin.php?page=exms-setup-wizard' ) );
            exit();
        }
    }

    /**
     * Added admin notices when no payment gateway configred 
     */
    public function exms_configure_payment_notice() {
        
        $stripe_settings = Exms_Core_Functions::get_options( 'settings' );
        $paypal_settings = Exms_Core_Functions::get_options( 'settings' );
        $stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
        $paypal_on = isset( $paypal_settings['paypal_enable'] ) ? $paypal_settings['paypal_enable'] : 'off';
        if( $stripe_on == 'off' && $paypal_on == 'off' ) {
            $setting_page_url = admin_url( 'admin.php?page=exms-settings&tab=payment-integration' );
            $class = 'notice notice-error is-dismissible';
            $message = __( 'Please, click <a href='.$setting_page_url.'>here</a> to configure the wpexams payment gateway.', 'exms' );
            printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
        }

        

    }

    /**
     * Added admin notices when admin select delete option 
     */
    public function exms_delete_admin_notice() {

        $exms_current_screen = get_current_screen();
        if( 'plugins' != $exms_current_screen->id ) {
            return;
        }

        $settings = Exms_Core_Functions::get_options( 'settings' );
        $uninstall_val = isset( $settings['exms_uninstall'] ) ? $settings['exms_uninstall']: '';

        if( 'on' != $uninstall_val ) {
            return;
        }

        $setting_page_url = admin_url( 'admin.php?page=exms-settings' );
        $class = 'notice notice-error is-dismissible';
        $message = __( 'You have selected "delete data on uninstallation" from <a href='.$setting_page_url.'>wp exams settings</a>.', 'exms' );
        printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }

    /**
     * Add Settings option on plugin activation
     *
     * @param $links
     * @return mixed
     */
    public function exms_plugin_setting_links( $links ) {

        $settings_link = '<a href="'. admin_url( 'admin.php?page=exms-settings' ) .'">'. __( 'Settings', 'exms' ) .'</a>';
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Add user roles Instructor or Student
     */
    public function exms_add_user_roles() {

        $user_roles = apply_filters( 'add_exms_user_roles', [
            'exms_student'       => __( 'Student', 'exms' ),
            'exms_instructor'    => __( 'Instructor', 'exms' ),
            'exms_group_leader'    => __( 'Group Leader', 'exms' ),
        ] );

        foreach( $user_roles as $user_key => $user_role ) {

            add_role( $user_key, $user_role, [ 'read' => true, 'level_0' => true ] );
        }
    }

    /**
     * Change admin panel footer text
     */
    public function exms_change_footer_text( $text ) {
        
        $post_type = isset( $_GET['post_type'] ) && ! empty( $_GET['post_type'] ) ? $_GET['post_type'] : '';
        
        if( isset( $_GET['page'] ) && $_GET['page'] == 'exms-settings' ) {

            $post_type = 'exms-settings';
        }

        $pages = [ 'exms_quizzes', 'exms_questions', 'exms_groups', 'exms-settings' ];
        if( ! in_array( $post_type, $pages ) ) {
            
            return $text;
        }

        return _e( 'Fueled by <a href="http://www.wordpress.org" target="_blank">WordPress</a> | developed and designed by <a href="https://wpexams.com/">WP Exams</a>', 'exms' );
    }
}

/**
 * Initialize Exms_Admin_Hooks
 */
Exms_Admin_Hooks::instance();