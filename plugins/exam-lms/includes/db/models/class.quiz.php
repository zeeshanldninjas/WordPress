<?php
/**
 * Shared logic for WP based data.
 * Contains functions like meta handling for all default data stores.
 * Your own data store doesn't need to use WC_Data_Store_WP -- you can write
 * your own meta handling functions.
 *
 * @version 3.0.0
 * @package WooCommerce\Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Data_Store_WP class.
 */
class EXMS_DB_QUIZ extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'quiz';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
							quiz_id int(11) NOT NULL,
							quiz_timer varchar(30) NOT NULL,
							quiz_status varchar(30) NOT NULL,
							show_answer varchar(30) NOT NULL,
							shuffle_questions varchar(30) NOT NULL,
							passing_percentage varchar(30) NOT NULL,
							display_passing_percentage varchar(30) NOT NULL,
							question_display varchar(30) NOT NULL,
							pass_quiz_message TEXT NOT NULL,
							fail_quiz_message TEXT NOT NULL,
							pending_quiz_message TEXT NOT NULL,
							point_achievement_type varchar(30) NOT NULL,
							achievement_point int(11) NOT NULL,
							deduct_point_type varchar(30) NOT NULL,
							deduct_point_on_fail varchar(30) NOT NULL,
							deduct_fail_point int(11) NOT NULL,
							deduct_point_wrong_answer varchar(30) NOT NULL,
							deduct_wrong_point int(11) NOT NULL,
							quiz_seat_limit int(11) NOT NULL,
							video_url varchar(255) NOT NULL
							';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

