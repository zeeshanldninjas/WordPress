<?php
/**
 * WP EXAMS - Quiz
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Student
 *
 * Base class to define all student functions
 */
class EXMS_Student {

	private static $instance;

	/**
     * Connect to wpdb
     */
    private static $wpdb;

    /**
     * @var         array $user_detail Registered
     * @since       1.0.0
     */
    public $user;
    
    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Student ) ) {

        	self::$instance = new EXMS_Student;

        	global $wpdb;
            self::$wpdb = $wpdb;
            self::$instance->includes();
        }

        return self::$instance;
    }

    /**
     * File includes
     */
    private function includes() {

        if( file_exists( EXMS_INCLUDES_DIR . 'classes/student-functions.php' ) ) {

            require EXMS_INCLUDES_DIR . 'classes/student-functions.php';
            $this->user = new EXMS_Student_Functions();
        }
    }
}

/**
 * Initialize EXMS_Student
 */
function EXMS_Student() {
    return EXMS_Student::instance();
}

EXMS_Student();