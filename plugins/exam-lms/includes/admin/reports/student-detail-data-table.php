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
 * Class EXMS_Student_Detail_Table
 */
class EXMS_Student_Detail_Table extends WP_List_Table {

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
			<h2><?php _e( 'Enrolled Quizzes', WP_EXAMS ); ?></h2>
		<?php

		$data_array = [];

		$curr_user_by = isset( $_GET['exms_user_by'] ) ? $_GET['exms_user_by'] : false;
		$curr_user = isset( $_GET['exms_report_user'] ) ? $_GET['exms_report_user'] : '';
		$user = get_user_by( $curr_user_by, $curr_user );
		$quizzes = exms_get_user_enrolled_quizzes( $user->ID );

		$data_array = [];

		if( $quizzes ) {

			foreach( $quizzes as $quiz_id ) {

				$post_exists = post_exists( get_the_title( $quiz_id ) );
				if( 0 == $post_exists ) {
					continue;
				}

				$instructors = exms_get_quiz_instructors( $quiz_id ) ? exms_get_quiz_instructors( $quiz_id ) : '';
				$all_ins = [];

				if( $instructors ) {

					foreach( $instructors as $ins ) {

						$all_ins[] = get_userdata( $ins )->user_nicename;
					}
				}

				$enrolled_date = exms_get_user_quiz_enroll_date( $user->ID, $quiz_id );
				$complete_date = exms_get_user_quiz_complete_date( $user->ID, $quiz_id );
				$status = exms_get_user_quiz_status( $user->ID, $quiz_id );

				$data_array[] = [
			    	'quiz_name'			=> '<a href="'.get_edit_post_link( $quiz_id ).'">'.get_the_title( $quiz_id ).'</a>',
			    	'exms_instructors'	=> $all_ins ? implode( ', ', $all_ins ) : '-',
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
			'quiz_name'			=> __( 'Quiz Name', WP_EXAMS ),
			'exms_instructors'	=> __( 'Instructors', WP_EXAMS ),
			'exms_status'		=> __( 'Status', WP_EXAMS ),
			'exms_enrolled_date'	=> __( 'Enrolled Date', WP_EXAMS ),
			'exms_complete_date'	=> __( 'Complete Date', WP_EXAMS ),
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
			case 'quiz_name':
			case 'exms_instructors':
			case 'exms_status':
			case 'exms_enrolled_date':
			case 'exms_complete_date':
			return $item[$column_name];
			default:
			return 'No data found';
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

	$exms_quiz_report_data_table = new EXMS_Student_Detail_Table();
	$exms_quiz_report_data_table->prepare_items();
	$exms_quiz_report_data_table->display();
}
exms_quiz_reports_data_table();