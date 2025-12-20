<?php
/**
 * WP EXAMS - Quiz
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Student_Functions
 *
 * Base class to define all student functions
 */
class EXMS_Student_Functions {

	private static $instance;

	/**
     * Connect to wpdb
     */
    private static $wpdb;

    /**
     * @var         array $user_detail Registered
     * @since       1.0.0
     */
    public $user = [];
    
    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Student_Functions ) ) {

        	self::$instance = new EXMS_Student_Functions;

        	global $wpdb;
            self::$wpdb = $wpdb;
        }

        return self::$instance;
    }

    /**
     * Get user enroll quizzes
     * 
     * @param $user_id
     */
    function get_user_enroll_quizzes( $user_id ) {

        $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

        $quiz_ids = [];
        $meta = self::$wpdb->get_results( " SELECT post_id FROM $table_name 
            WHERE user_id = $user_id AND post_type = 'exms_quizzes' ORDER BY time ASC " );

        if( ! empty( $meta ) && ! is_null( $meta ) ) {
            $quiz_ids = array_map( 'intval', array_column( $meta, 'post_id' ) );
        }

        return $quiz_ids;
    }
}

/**
 * Initialize EXMS_Student_Functions
 */
function EXMS_Student_Functions() {
    return EXMS_Student_Functions::instance();
}

EXMS_Student_Functions();