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
class EXMS_DB_QUESTION_TYPES extends EXMS_DB_Main {
    
    /**
	 * Database table name.
	 *
	 * @var integer
	 */
	protected $table = 'exam_question_types';

	/**
	 * Initiate new background process.
	 */
	private $table_script = 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
							type_name VARCHAR(50) NOT NULL UNIQUE,
							created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
							updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							updated_by BIGINT UNSIGNED,
							FOREIGN KEY (updated_by) REFERENCES wp_users(ID)
							';
	/**
	 * create table.
	 */
	public function run_table_create_script() {

        $this->exms_create_db_table( $this->table, $this->table_script );

        global $wpdb;
        $table_name = $wpdb->prefix . 'exms_exam_question_types';

        $default_types = apply_filters( 'exms_exam_question_types', [
            'single_choice',
            'multiple_choice',
            'essay',
            'fill_blank',
            'free_choice',
            'sorting_choice',
            'matrix_sorting',
            'file_upload',
            'range',
        ] );

        foreach ( $default_types as $type ) {

            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE type_name = %s",
                $type
            ) );

            if ( !$exists ) {
                $wpdb->insert(
                    $table_name,
                    [
                        'type_name'  => $type,
                        'updated_by' => get_current_user_id() ?: -1,
                    ],
                    [
                        '%s',
                        '%d',
                    ]
                );
            }
        }
    }
}

