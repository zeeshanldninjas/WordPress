<?php
/**
 * WP EXAMS - User Report
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_user_report
 *
 * Handles all actions related to user report
 */
class EXMS_user_report {

	private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_user_report ) ) {

        	self::$instance = new EXMS_user_report;
        	self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Define hooks
	 */
	public function hooks() {

		add_action( 'wp_login', [ $this, 'exms_save_user_last_login' ], 10, 2 );
	}

	/**
	 * Save user last login
	 */
	public function exms_save_user_last_login( $user_login, $user ) {

		update_user_meta( $user->ID, 'exms_last_login', time() );
	}

	/**
	 * User report page
	 */
	public static function exms_user_report() {

		if( file_exists( EXMS_INCLUDES_DIR . 'admin/reports/user-reports-html.php' ) ) {

			require_once EXMS_INCLUDES_DIR . 'admin/reports/user-reports-html.php';
		}
	}

	/**
	 * Quiz report page
	 */
	public static function exms_quiz_report() {

		if( file_exists( EXMS_INCLUDES_DIR . 'admin/reports/quiz-reports-html.php' ) ) {

			require_once EXMS_INCLUDES_DIR . 'admin/reports/quiz-reports-html.php';
		}
	}
}

/**
 * Initialize EXMS_user_report
 */
EXMS_user_report::instance();
