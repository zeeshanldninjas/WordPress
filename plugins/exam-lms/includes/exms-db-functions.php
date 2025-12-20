<?php
/**
 * Database Functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class EXMS_DB
 *
 * To interact with database tables
 */
class EXMS_DB {

	/**
	 * Perform a select/delete operation
	 *
	 * @param String 	$type 		Define whether it is a select or a delete query
	 * @param String 	$table 		Database table name
	 * @param Bool 		$prefix 	Prepend default prefix with table.
	 * @param Array 	$ids 		Database table rows ids 
	 * @param String 	$params 	To define WHERE statement in query 
	 */
	private function exms_db_query_private( $query_type, $table, $params ) {

		global $wpdb;

		$type = false;

		if( 'select' == $query_type ) {

			$type = 'SELECT * FROM';

		} elseif( 'delete' == $query_type ) {

			$type = 'DELETE FROM';

		} elseif( 'drop' == $query_type ) {

			$type = 'DROP TABLE';

		}

		$table = $wpdb->prefix.$table;

		$where_params = [];
		$where = '';
        
		if( !empty( $params ) ) {
			foreach( $params as $param ) {
				
				$where .= !empty($where)?' and ':'';
				if( strtolower( $param[ 'operator' ] ) == 'like' ) {
					$where .= $param[ 'field' ]." like '".$param[ 'value' ]."'";
				} else {
					$where_params[] = $param[ 'value' ];
					$where .= $param[ 'field' ].' '.$param[ 'operator' ].' '.$param[ 'type' ];
				}
				
			}
		}

		if( !empty( $where ) ) {
			$where = " where ".$where;
		}
		if( 'select' == $query_type && $table ) {
			$res = $wpdb->get_results( $wpdb->prepare("$type $table $where", $where_params ) );
		
		} else {
			
			$res = $wpdb->query( $wpdb->prepare("$type $table $where", $where_params ) );
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

		$table = $wpdb->prefix.$table;
		
		$wpdb->insert( $table, $data );
	}

	/**
	 * Perform a insert operation
	 *
	 * @param String 	$table 		Database table name
	 * @param Array 	$data 		Data to insert 
	 */
	public function exms_db_insert( $table, $data ) {

		return $this->exms_db_insert_private( $table, $data );
	}

	/**
	 * Perform a select/delete operation
	 *
	 * @param String 	$type 		Define whether it's "select" or "delete" query
	 * @param String 	$table 		Database table name
	 * @param String 	$where 		To define WHERE statement in query 
	 */
	public function exms_db_query( $query_type, $table, $where ) {

		return $this->exms_db_query_private( $query_type, $table, $where );
	}

	/**
	 * Create a new database table
	 *
	 * @param String 	$table_name 	Name of database table
	 * @param Array 	$columns 		columns of database table
	 */
	private function exms_create_db_table_private( $table_name, $columns ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $table_name; 
		
		$table_exists = $wpdb->get_results( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

		if( $table_exists ) {
			
			return __( 'Table already exists', WP_EXAMS );

		} elseif( $table_name && $columns && ! $table_exists ) {
			
			$wpdb->query( $wpdb->prepare( "CREATE TABLE `$table_name`( $columns )" ) );
		}
	}

	/**
	 * Create a new database table
	 *
	 * @param String 	$table_name 	Name of database table
	 * @param Array 	$values 		columns/values of database table
	 */
	public function exms_create_db_table( $table_name, $columns ) {
 
		return $this->exms_create_db_table_private( $table_name, $columns );
	}
}