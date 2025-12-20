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
class EXMS_DB_USER_POST_RELATIONS extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'user_post_relations';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id 				INT AUTO_INCREMENT PRIMARY KEY,
							post_id				INT NOT NULL,
							post_type			varchar(200) NOT NULL,
							user_id				INT NOT NULL,
							time				INT NOT NULL';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}