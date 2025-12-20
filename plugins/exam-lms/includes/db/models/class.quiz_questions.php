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
class EXMS_DB_QUIZ_QUESTION extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'quiz_questions';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
                            quiz_id int(11) NOT NULL,
                            question_id int(11) NOT NULL,
                            question_order int(11) NOT NULL,
							status varchar(30) NOT NULL,
                            created_at varchar(50) NOT NULL, 
                            updated_at varchar(50) NOT NULL,
                            created_by varchar(50) NOT NULL,
                            updated_by varchar(50) NOT NULL
							';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

