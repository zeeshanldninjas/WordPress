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
class EXMS_DB_EXAMS_STRUCTURE_TYPE extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'post_settings';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
							parent_post_id int(11) NOT NULL,
							parent_post_type varchar(30) NOT NULL,
							parent_post_price int(11) NOT NULL,
							subscription_days varchar(30) NOT NULL,
							redirect_url varchar(255) NOT NULL,
							parent_achievement_points int(11) NOT NULL,
							seat_limit int(11) NOT NULL,
							progress_type varchar(50) NOT NULL,
							video_url varchar(255) NOT NULL,
							post_type_slug varchar(255) NOT NULL
							';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

