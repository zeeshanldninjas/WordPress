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
 * EXMS_DB_Main class.
 */
Abstract class EXMS_DB_Main {
    
    /**
	 * Database Prefix
	 */
	protected $table_prefix = 'exms_';


    /**
	 * Execute Raw Query
	 */ 
	private function exms_db_raw_query( $query_type, $table, $where, $columns = '*' ) {
		global $wpdb;

		$type = false;

		if( 'select' == $query_type ) {

			if ( !empty( $columns ) ) {
				$columns = is_array( $columns ) ? implode( ', ', $columns ) : '*';
			}
	
			$type = "SELECT $columns FROM";
	
		} elseif( 'delete' == $query_type ) {

			$type = 'DELETE FROM';

		} elseif( 'drop' == $query_type ) {

			$type = 'DROP TABLE';

		}

		$table = $wpdb->prefix.$table;

		if( 'select' == $query_type && $table ) {

			$res = $wpdb->get_results( "$type $table $where" );
		
		} else {
			
			$res = $wpdb->query( "$type $table $where" );
		}

		return $res;
	}
    
    /**
	 * Perform a select/delete operation
	 *
	 * @param String 	$type 		Define whether it is a select or a delete query
	 * @param String 	$table 		Database table name
	 * @param Bool 		$prefix 	Prepend default prefix with table.
	 * @param Array 	$ids 		Database table rows ids 
	 * @param String 	$where 		To define WHERE statement in query 
	 */
	private function exms_db_query_private( $query_type, $table, $params = [], $columns = '*', $order_by = '', $limit = '' ) {

		global $wpdb;

		$type = false;

		if( 'select' == $query_type ) {

			if ( !empty( $columns ) ) {
				$columns = is_array( $columns ) ? implode( ', ', $columns ) : '*';
			}
	
			$type = "SELECT $columns FROM";
	
		} elseif( 'delete' == $query_type ) {

			$type = 'DELETE FROM';

		} elseif( 'drop' == $query_type ) {

			$type = 'DROP TABLE';

		}

		$table = $wpdb->prefix.$table;

		$where_params = [];
		$where = '';
        /**
		 * $where_params[] = 
		 * 	[ 
		 * 		'field' => 'id', 
		 * 		'value' => $row_id, 
		 * 		'operator' => '=', 
		 * 		'type' => '%d' 
		 * 	];
		 */
		if ( !empty( $params ) ) {

			$where_clauses = [];
			foreach ( $params as $param ) {
				$where_params[] = $param['value'];
				$where_clauses[] = $param['field'] . ' ' . $param['operator'] . ' ' . $param['type'];
			}
			$where = 'WHERE ' . implode( ' AND ', $where_clauses );
		}


		if( 'select' == $query_type && $table ) {

			$order_by_clause = !empty($order_by) ? ' ORDER BY ' . $order_by : '';
        	$limit_clause = !empty($limit) ? ' LIMIT ' . $limit : '';

			$query = "$type $table $where $order_by_clause $limit_clause";
        	$res = $wpdb->get_results( $wpdb->prepare( $query, $where_params ) );
		
		} else {
			
			$res = $wpdb->query( $wpdb->prepare("$type $table $where", $where_params ));
		}

		return $res;
	}

	/**
	 * Perform a insert operation
	 *
	 * @param String 	$table 		Database table name
	 * @param Array 	$data 		Data to insert 
	 */
	private function exms_db_insert_private( $table, $data ) {

		global $wpdb;
		$table = $wpdb->prefix . $table;
		$result = $wpdb->insert( $table, $data );
	    return $result;
	}

	/**
	 * Perform a insert operation
	 *
	 * @param String 	$table 		Database table name
	 * @param Array 	$data 		Data to insert 
	 */
	public function exms_db_insert( $table, $data ) {

		return $this->exms_db_insert_private( $this->table_prefix.$table, $data );
	}

	/**
	 * Perform a select/delete operation
	 *
	 * @param String 	$type 		Define whether it's "select" or "delete" query
	 * @param String 	$table 		Database table name
	 * @param String 	$where 		To define WHERE statement in query 
	 */
	public function exms_db_query( $query_type, $table, $params = [], $columns = '*', $order_by = '', $limit = '' ) {

		return $this->exms_db_query_private( $query_type, $table, $params, $columns, $order_by, $limit );
	}

	/**
	 * Create a new database table
	 *
	 * @param String 	$table_name 	Name of database table
	 * @param Array 	$columns 		columns of database table
	 */
	protected function exms_create_db_table_private( $table_name, $columns, $is_prefix=true ) {

		global $wpdb;
        
        if( $is_prefix ) {
			/**
			 * wp_exms_xyz
			 */
            $table_name = $wpdb->prefix.$this->table_prefix.$table_name; 
        } else {
            $table_name = $wpdb->prefix.$table_name; 
        }
        
		$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

		if( $table_exists ) {
			
			return __( 'Table already exists', 'exms' );

		} elseif( $table_name && $columns && ! $table_exists ) {
			
			$sql = "CREATE TABLE `$table_name` ( $columns )";
            $wpdb->query( $sql );
        }
    }

    /**
     * Call this table when passing parameters for the table creation ( Public )
     */
    public function exms_create_db_table( $table_name, $columns, $is_prefix=true ) {
		
        return $this->exms_create_db_table_private( $table_name, $columns, $is_prefix );
    }

	/**
	 * Function to check table exist or not
	 * @param String $table_name
	 */
	private function exms_check_table_exists( $table_name ) {
        global $wpdb;
        $query = $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name );
        return $wpdb->get_var( $query ) === $table_name;
    }

	/**
	 * call this function to check table exist or not in the other files ( Public )
	 * @param String $table_name
	 */
	public function exms_table_exists( $table_name ) {
		
        return $this->exms_check_table_exists( $table_name );
    }

	
}