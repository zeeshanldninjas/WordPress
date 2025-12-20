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
class EXMS_DB_POST_RELATIONSHIP extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'post_relationship';

	/**
	 * Initiate new background process.
	 */

	private $table_script = "id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		parent_post_id BIGINT UNSIGNED NOT NULL,
		child_post_id BIGINT UNSIGNED NOT NULL,
		parent_post_type VARCHAR(200) NOT NULL,
		assigned_post_type VARCHAR(200) NOT NULL,
		relationship_type VARCHAR(50) NOT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		KEY parent_idx (parent_post_id),
		KEY child_idx (child_post_id),
		KEY relationship_idx (relationship_type)";

		
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}