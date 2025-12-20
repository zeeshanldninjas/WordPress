<?php
/**
 * Manage Email settings
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Email {

    /**
     * @var self
     */
    private static $instance;
    private $email_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Email ) ) {
            self::$instance = new EXMS_Email;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'email' ) {
                self::$instance->email_page = true;
            }
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        $this->manage_category_taxonomy();
    }

    /**
     * Main function to call html content and tabs
     */
    private function manage_category_taxonomy() {

        if( !$this->email_page ) {
            return false;
        }
                
        /**
         * Create email sub tabs
         */

        $tabs = [ 
            'exms_general' => 'General', 
            'exms_user' => 'Students', 
            'exms_instructor' => 'Instructor', 
            'exms_admin' => 'Admin' 
        ];
        exms_create_submenu_tabs( $tabs, false );

        /**
         * Email setting html
         */
        $email_settings = Exms_Core_Functions::get_options( 'settings' );
        $taxo_type = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : '';
        if( 'exms_general_email' == $taxo_type || NULL == $taxo_type && 'email' == $_GET['tab'] ) {

            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-email-general-settings-template.php' ) ) {
                    
                require_once EXMS_TEMPLATES_DIR . '/tabs/exms-email-general-settings-template.php';
            }
            
        } elseif( isset( $taxo_type ) && 'exms_instructor_email' == $taxo_type ) {

            $dafault_msg_assign = __( 'Dear {instructor_name}, <br> {user_name} has been enrolled in your assigned group. <br> Regard, <br>{admin_name}', 'exms' );
            $dafault_msg_unassign = __( 'Dear {instructor_name}, <br> {user_name} has been unenrolled from your assigned group. <br> Regard, <br>{admin_name}', 'exms' );

            /**
             * Get istructor assign email subject and content
             */
            $assign_quiz_subject = isset( $email_settings[ 'exms_instructor_assign_subject' ] ) ? $email_settings[ 'exms_instructor_assign_subject' ] : '';
            $assign_quiz_content = isset( $email_settings[ 'exms_instructor_assign_content' ] ) ? $email_settings[ 'exms_instructor_assign_content' ] : $dafault_msg_assign;
            $assign_quiz_option = isset( $email_settings[ 'exms_instructor_assign_option' ] ) ? $email_settings[ 'exms_instructor_assign_option' ] : '';
            
            /**
             * Get istructor unassign email subject and content
             */
            $unassign_quiz_subject = isset( $email_settings[ 'exms_instructor_unassign_subject' ] ) ? $email_settings[ 'exms_instructor_unassign_subject' ] : '';
            $unassign_quiz_content = isset( $email_settings[ 'exms_instructor_unassign_content' ] ) ? $email_settings[ 'exms_instructor_unassign_content' ] : $dafault_msg_unassign;
            $unassign_quiz_option = isset( $email_settings[ 'exms_instructor_unassign_option' ] ) ? $email_settings[ 'exms_instructor_unassign_option' ] : '';
            
            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-email-instructor-settings-template.php' ) ) {
                    
                require_once EXMS_TEMPLATES_DIR . '/tabs/exms-email-instructor-settings-template.php';
            }

        } elseif( isset( $taxo_type ) && 'exms_user_email' == $taxo_type ) {

            $dafault_std_buying = __( 'Dear {user_name}, <br> WELCOME!!! You have been enrolled in {quiz_name}. <br> Wish you best of luck for {quiz_name} assessment. <br> Regard, <br>{admin_name}', 'exms' );
            $dafault_std_passing = __( 'Dear {user_name}, <br> CONGRATULATION!!! You have successfully passed {quiz_name}. Keep it up! <br> Regard, <br>{admin_name}', 'exms' );
            $dafault_std_failing = __( 'Dear {user_name}, <br> SORRY!!! You could not pass {quiz_name}. <br> Practise more, work hard and try it again. <br> Regard, <br>{admin_name}', 'exms' );
            $dafault_std_achived = __( 'Dear {user_name}, <br> HURRAH!!! You have been awarded {badge_name}.<br> Regard, <br>{admin_name}', 'exms' );

            /**
             * Get buying email subject and content
             */
            $b_quiz_subject = isset( $email_settings[ 'exms_buying_subject' ] ) ? $email_settings[ 'exms_buying_subject' ] : '';
            $b_quiz_content = isset( $email_settings[ 'exms_buying_content' ] ) ? $email_settings[ 'exms_buying_content' ] : $dafault_std_buying;
            $b_quiz_option = isset( $email_settings[ 'exms_buying_option' ] ) ? $email_settings[ 'exms_buying_option' ] : '';

            /**
             * Get passing email subject and content
             */
            $p_quiz_subject = isset( $email_settings[ 'exms_passing_subject' ] ) ? $email_settings[ 'exms_passing_subject' ] : '';
            $p_quiz_content = isset( $email_settings[ 'exms_passing_content' ] ) ? $email_settings[ 'exms_passing_content' ] : $dafault_std_passing;
            $p_quiz_option = isset( $email_settings[ 'exms_passing_option' ] ) ? $email_settings[ 'exms_passing_option' ] : '';

            /**
             * Get failing email subject and content
             */
            $f_quiz_subject = isset( $email_settings[ 'exms_failing_subject' ] ) ? $email_settings[ 'exms_failing_subject' ] : '';
            $f_quiz_content = isset( $email_settings[ 'exms_failing_content' ] ) ? $email_settings[ 'exms_failing_content' ] : $dafault_std_failing;
            $f_quiz_option = isset( $email_settings[ 'exms_failing_option' ] ) ? $email_settings[ 'exms_failing_option' ] : '';

            /**
             * Get achievement email subject and content
             */
            $a_quiz_subject = isset( $email_settings[ 'exms_achive_subject' ] ) ? $email_settings[ 'exms_achive_subject' ] : '';
            $a_quiz_content = isset( $email_settings[ 'exms_achive_content' ] ) ? $email_settings[ 'exms_achive_content' ] : $dafault_std_achived;
            $a_quiz_option = isset( $email_settings[ 'exms_achive_option' ] ) ? $email_settings[ 'exms_achive_option' ] : '';
            
            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-email-student-settings-template.php' ) ) {
                    
                require_once EXMS_TEMPLATES_DIR . '/tabs/exms-email-student-settings-template.php';
            }

        } elseif( isset( $taxo_type ) && 'exms_admin_email' == $taxo_type ) {

            /**
             * Get admin email subject and content
             */
            $dafault_std_buying = __( 'Dear {admin_name}, <br> {user_name} has just purchased {quiz_name}.', 'exms' );

            $admin_quiz_subject = isset( $email_settings[ 'exms_admin_subject' ] ) ? $email_settings[ 'exms_admin_subject' ] : '';
            $admin_quiz_content = isset( $email_settings[ 'exms_admin_content' ] ) ? $email_settings[ 'exms_admin_content' ] : $dafault_std_buying;
            $admin_quiz_option = isset( $email_settings[ 'exms_admin_option' ] ) ? $email_settings[ 'exms_admin_option' ] : '';
            
            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-email-admin-settings-template.php' ) ) {
                    
                require_once EXMS_TEMPLATES_DIR . '/tabs/exms-email-admin-settings-template.php';
            }
        }
    }
}

EXMS_Email::instance();

