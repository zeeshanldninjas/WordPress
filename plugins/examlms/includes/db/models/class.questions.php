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
class EXMS_DB_Questions extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'questions';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
							question_id int(11) NOT NULL,
							points_for_question int(11) DEFAULT 0,
							hint_for_question TEXT DEFAULT NULL,
							msg_for_correct_ans varchar(200) NOT NULL,
							msg_for_incorrect_ans varchar(200) NOT NULL,
							shuffle_answer varchar(200) NOT NULL,
							timer varchar(200) NOT NULL,
							question_type varchar(200) NOT NULL';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

