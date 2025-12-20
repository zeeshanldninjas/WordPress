<?php 
/**
 * Quiz reports data table
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include WP List Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class EXMS_Quiz_Detail_Table
 */
class EXMS_Quiz_Detail_Table extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';
		$exms_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';
		$datas = $this->exms_quiz_reports_table_data( $url_order_by, $url_order, $exms_search_term );
		$per_page = 10;
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
	public function exms_quiz_reports_table_data( $url_order_by = '', $url_order = '', $exms_search_term = '' ) {

		?>
		<section class="exms-report-data-table">
			<h2><?php _e( 'Enrolled Students', WP_EXAMS ); ?></h2>
		<?php

		$data_array = [];

		$curr_quiz_by = isset( $_GET['exms_quiz_by'] ) ? $_GET['exms_quiz_by'] : false;
		$curr_quiz = isset( $_GET['exms_report_quiz'] ) ? $_GET['exms_report_quiz'] : '';
		$quizzes = exms_get_page_by_title( $curr_quiz, '', 'exms_quizzes' );
		$quiz_id = 'quiz_name' == $curr_quiz_by ? $quizzes->ID : (int) $curr_quiz;
		$student_ids = exms_quiz_student_users( $quiz_id );
		$instructors = exms_get_quiz_instructors( $quiz_id );

		$all_ins = [];
		if( $instructors ) {

			foreach( $instructors as $ins ) {

				$all_ins[] = ucwords( get_userdata( $ins )->user_nicename );
			}
		}

		if( $student_ids ) {

			foreach( $student_ids as $student_id ) {

				$student_name = ucwords( get_userdata( (int) $student_id )->user_nicename );
				$user_profile_url = add_query_arg( 'user_id', $student_id, self_admin_url( 'user-edit.php') );
				$enrolled_date = exms_get_user_quiz_enroll_date( $student_id, $quiz_id );
				$complete_date = exms_get_user_quiz_complete_date( $student_id, $quiz_id );
				$status = exms_get_user_quiz_status( $student_id, $quiz_id );

				$data_array[] = [
			    	'std_name'			=> '<a href="'.$user_profile_url.'">'. $student_name .'</a>',
			    	'exms_instructors'	=> implode( ', ', $all_ins ),
			    	'exms_status'		=> $status ? $status : '-',
			    	'exms_enrolled_date'	=> $enrolled_date ? $enrolled_date : '-',
			    	'exms_complete_date'	=> $complete_date ? $complete_date : '-',
			    ];
			}
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
			'std_name'			=> __( 'Student name', WP_EXAMS ),
			'exms_instructors'	=> __( 'Instructors', WP_EXAMS ),
			'exms_status'		=> __( 'Status', WP_EXAMS ),
			'exms_enrolled_date'	=> __( 'Enrolled date', WP_EXAMS ),
			'exms_complete_date'	=> __( 'Complete date', WP_EXAMS ),
		];
		return $columns;
	}

	/**
	 * Return column value
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'std_name':
			case 'exms_instructors':
			case 'exms_status':
			case 'exms_enrolled_date':
			case 'exms_complete_date':
			return $item[$column_name];
			default:
			return 'no lists found';
		}
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		_e( 'No Records found', WP_EXAMS );
	}
}

/**
 * WP_list_table instance
 */
function exms_quiz_reports_data_table() {

	$exms_quiz_report_data_table = new EXMS_Quiz_Detail_Table();
	$exms_quiz_report_data_table->prepare_items();
	$exms_quiz_report_data_table->display();
}
exms_quiz_reports_data_table();