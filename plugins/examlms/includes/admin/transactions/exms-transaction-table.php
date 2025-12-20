<?php 
/**
 * Student's reports quizzes data table
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include WP List Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class EXMS_Transaction_Table
 */
class EXMS_Transaction_Table extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';
		$exms_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';
		$datas = $this->exms_transaction_table_data( $url_order_by, $url_order, $exms_search_term );
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count( $datas );
		
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		) );

		$this->items = array_slice( $datas, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$exms_columns = $this->get_columns();
		$exms_hidden = $this->get_hidden_columns();
		$exms_sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $exms_columns, $exms_hidden, $exms_sortable );
	}

	/**
	 * Display columns datas
	 *
	 * @param $url_order_by, $url_order, $exms_search_term
	 * @return Array
	 */
	public function exms_transaction_table_data( $url_order_by = '', $url_order = '', $exms_search_term = '' ) {

		global $wpdb;

		?>
		<section class="exms-transaction-data-table">
			<h2><?php _e( 'Transactions', 'exms' ); ?></h2>
		<?php

		$data_array = [];

		$table_name = EXMS_Payment_Func::exms_payment_transaction_table();

		$transactions = $wpdb->get_results( " SELECT * From $table_name " );
		if( empty( $transactions || is_null( $transactions ) ) ) {
			$data_array = [];
		}

		foreach( $transactions as $transaction ) {

			$order_id = isset( $transaction->order_id ) ? $transaction->order_id : 0;
			$user_id = isset( $transaction->user_id ) ? $transaction->user_id : 0;
			$product_id = isset( $transaction->product_id ) ? $transaction->product_id : 0;
			$price = isset( $transaction->price ) ? $transaction->price : 0;
			$receiver = isset( $transaction->receiver ) ? $transaction->receiver : 0;
			$payer = isset( $transaction->payer ) ? $transaction->payer : 0;
			$status = isset( $transaction->status ) ? $transaction->status : 0;
			$create_time = isset( $transaction->create_time ) ? $transaction->create_time : 0;
		
			$data_array[] = [
		    	'order_id'			=> '<span class="exms-transaction-id" data-row-id="'.$transaction->id.'">'.$order_id.'</span>',
		    	'user_id'			=> $user_id,
		    	'product_id'		=> $product_id,
		    	'price'				=> '$'.$price,
		    	'receiver'			=> $receiver,
		    	'payer'				=> $payer,
		    	'status'			=> ucwords( strtolower( $status ) ),
		    	'time'				=> $create_time,
		    ];
		}

		return $data_array;
		?></section><?php
	}

	/**
	 * Gets a list of all, hidden and sortable columns
	 */
	public function get_hidden_columns() {

		return array();
	}

	/**
	 * Gets a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'cb'       		=> '<input class="exms-transaction-allcheck" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />',
			'order_id'		=> __( 'Order ID', 'exms' ),
			'user_id'		=> __( 'User ID', 'exms' ),
			'product_id'	=> __( 'Product ID', 'exms' ),
			'price'			=> __( 'Price', 'exms' ),
			'receiver'		=> __( 'Receiver', 'exms' ),
			'payer'			=> __( 'Payer', 'exms' ),
			'status'		=> __( 'Status', 'exms' ),
			'time'			=> __( 'Time', 'exms' ),
		];
		return $columns;
	}

	/**
	 * Checkbox column markup.
	 *
	 * @param array $item A singular item (one full row).
	 */
	function column_cb( $item ) {
		
		$top_checkbox = '<input class="exms-selected-transaction" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />';
		return $top_checkbox;
	}

	/**
	 * Return column value
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'order_id':
			case 'user_id':
			case 'product_id':
			case 'price':
			case 'receiver':
			case 'payer':
			case 'status':
			case 'time':
			return $item[$column_name];
			default:
			return __( 'No Transactions found.', 'exms' );
		}
	}

	/**
	 * Add bulk action buttons
	 */
	public function get_bulk_actions() {

        return [
                'delete' => '<span class="exms-delete-bulk-transaction">'.__( 'Delete', 'exms' ).'</span>',
        ];
    }

	/**
	 * Add row actions
	 */
	public function handle_row_actions( $link, $column_name, $primary ) {
		
		if ( $primary !== $column_name ) {
			return '';
		}

		$actions           	= [];
		$actions['delete'] 	= '<a class="exms-delete-transaction">' . __( 'Delete', 'exms' ) . '</a>';
		$actions['view'] 	= '<a class="exms-views-transaction">' . __( 'View', 'exms' ) . '</a>';

		return $this->row_actions( $actions );
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		_e( 'No Transactions found.', 'exms' );
	}
}

/**
 * WP_list_table instance
 */
function exms_quiz_reports_data_table() {

	$exms_quiz_report_data_table = new EXMS_Transaction_Table();
	$exms_quiz_report_data_table->prepare_items();
	$exms_quiz_report_data_table->display();
}
exms_quiz_reports_data_table();