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
class EXMS_DB_GROUP extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'group';

	/**
	 * Initiate new background process.
	 */
	private $table_script = "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		group_id BIGINT UNSIGNED NOT NULL,
		video_url varchar(255) NOT NULL";

	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

