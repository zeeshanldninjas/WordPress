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
class EXMS_USER_QUESTION_ATTEMPTS extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'exam_user_question_attempts';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id BIGINT UNSIGNED NOT NULL,
							question_id BIGINT UNSIGNED NOT NULL,
							quiz_id BIGINT UNSIGNED NOT NULL,
							question_type VARCHAR(50),
                            answer JSON,
							attempt_date BIGINT,
							score DECIMAL(5,2),
							total_possible_score INT UNSIGNED,
							completion_status VARCHAR(50),
							is_correct VARCHAR(50),
							file_url TEXT,
							time_taken INT,
							attempt_number INT,
							comment VARCHAR(255)
							';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

