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
class EXMS_DB_QUIZ_RESULTS extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'quizzes_results';

	/**
	 * Initiate new background process.
	 */

	private $table_script = "id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	quiz_id INT NOT NULL,
	course_id INT NOT NULL,
	parent_posts VARCHAR(200) NOT NULL,
	obtained_points INT NOT NULL,
	points_type VARCHAR(50) NOT NULL,
	correct_questions INT NOT NULL,
	wrong_questions INT NOT NULL,
	not_attempt INT NOT NULL,
	review_questions INT NOT NULL,
	passed VARCHAR(20) NOT NULL,
	percentage INT NOT NULL,
	time_taken VARCHAR(50) NOT NULL,
	result_date BIGINT(20),
	attempt_number BIGINT(20)";


	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}