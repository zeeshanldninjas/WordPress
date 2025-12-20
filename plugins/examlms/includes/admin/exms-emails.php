<?php
/**
 * WP Exam - Emails
 *
 * All emails functionality
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Emails
 *
 * Base class to define wp exam email function
 */
class Exms_Emails {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Emails ) ) {

            self::$instance = new Exms_Emails;
        }

        return self::$instance;
    }

    /**
     * Send email to users/instructor and admin  
     */
    public static function exms_send_email( $email, $subject, $message ) {

        wp_mail( $email, $subject, $message );
    }
}

/**
 * Initialize Exms_Emails
 */
function Wpe_Email() {

    return new Exms_Emails;
}