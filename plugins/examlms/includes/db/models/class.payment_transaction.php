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
class EXMS_DB_Payment_transation extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'payment_transaction';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id INT AUTO_INCREMENT PRIMARY KEY,
							order_id varchar(50) NOT NULL,
							user_id int(11) NOT NULL DEFAULT 0,
							product_id int(11) NOT NULL DEFAULT 0,
							price float NOT NULL DEFAULT 0,
							receiver varchar(200) NOT NULL,
							payer varchar(200) NOT NULL,
							status varchar(30) NOT NULL,
							create_time timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()';
	/**
	 * create table.
	 */
	public function run_table_create_script() {
		
		$this->exms_create_db_table( $this->table, $this->table_script );
	}
}

