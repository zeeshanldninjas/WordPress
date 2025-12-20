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
class EXMS_DB_QUESTION_RESULTS extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'questions_results';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id 				INT AUTO_INCREMENT PRIMARY KEY,
							user_id				INT(200) NOT NULL,
							quiz_id				INT(200) NOT NULL,
							question_id			INT(200) NOT NULL,
							max_points			INT(200) NOT NULL,
							correct_answers		INT(200) NOT NULL,
							total_answers		INT(200) NOT NULL,
							parent_posts		varchar(200) NOT NULL,
							total_points		INT(200) NOT NULL,
							obtained_points		INT(200) NOT NULL,
							points_type			varchar(200) NOT NULL,
							total_questions		INT(200) NOT NULL,
							correct_questions	INT(200) NOT NULL,
							passed				varchar(200) NOT NULL,
							percentage			INT(200) NOT NULL,
							essay_ids			varchar(200) NOT NULL,
							result_date			timestamp';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}