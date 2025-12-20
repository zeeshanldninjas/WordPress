<?php 
/**
 * Create sumitted essay record table
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include WP List Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class EXMS_Essay_Table
 */
class EXMS_Essay_Table extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';
		$exms_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';
		$datas = $this->exms_submit_essay_records( $url_order_by, $url_order, $exms_search_term );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		if( NULL != $datas ) {
			$total_items = count( $datas );

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = array_slice( $datas, ( ( $current_page - 1 ) * $per_page ), $per_page );
		}

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
	public function exms_submit_essay_records( $url_order_by = '', $url_order = '', $exms_search_term = '' ) {

		?>
		<section class="exms-submit-essay-table">
			<div class="exms-essay-title">
				<span class="dashicons dashicons-welcome-learn-more exms-wp-logo"></span>
				<h2><?php _e( 'WP Exams Essay(s)', 'exms' ); ?></h2>
			</div>
		<?php

		$data_array = [];

		/**
		 * Filter by multiple values
		 */
		$params = [];
		
		$filter_by = '';
		$filter_u_name = '';
		if( isset( $_POST['exms_filter_essays'] ) 
			&& 'exms_essay_action' == $_POST['action'] 
        	&& current_user_can( 'manage_options' ) 
        	&& check_admin_referer( 'exms_essay_nonce', 'exms_essay_nonce_field' ) ) {

			$filter_by = isset( $_POST['exms_filter_essay_rocord'] ) ? $_POST['exms_filter_essay_rocord'] : '';
			$filter_u_id = isset( $_POST['exms_filter_by_id'] ) ? $_POST['exms_filter_by_id'] : '';
			$filter_u_name = isset( $_POST['exms_filter_by_name'] ) ? sanitize_text_field( $_POST['exms_filter_by_name'] ) : '';
			$filter_q_id = isset( $_POST['exms_filter_by_quiz_id'] ) ? $_POST['exms_filter_by_quiz_id'] : '';

			if( 'user_id' == $filter_by ) {

				$params[] = [ 'field' => 'user_id', 'value' => $filter_u_id, 'operator' => '=', 'type'=> '%d'];
			} elseif( 'user_name' == $filter_by ) {

				$remove_quiz_id = 'exms-hide-essay';
				$remove_user_id = 'exms-hide-essay';
				$show_user_name = 'exms-show-essay';
				if( ! empty( $filter_u_name ) ) {

					$user = get_user_by( 'login', $filter_u_name );
					$params[] = [ 'field' => 'user_id', 'value' => $user->ID, 'operator' => '=', 'type'=> '%d'];
				}
			} elseif( 'quiz_id' == $filter_by ) {

				$show_quiz_id = 'exms-show-essay';
				$remove_user_id = 'exms-hide-essay';
				$params[] = [ 'field' => 'quiz_id', 'value' => $filter_q_id, 'operator' => '=', 'type'=> '%d'];
			}
		}
		?>

		<div class="exms-essay-bulk-actions">

			<!-- Added bulk actions -->
			<select class="exms-selected-rows">
				<option value="bult-action"><?php echo __( 'Bulk action', 'exms' ); ?></option>
				<option value="delete"><?php echo __( 'Delete', 'exms' ); ?></option>
			</select>
			<button type="button" class="button-secondary exms-delete-multiple-essay"><?php echo __( 'Apply', 'exms' ); ?></button>
			<!-- End bulk actions -->

			<!-- Added filters html -->
			<div class="exms-essay-filters-options">
				<div class="exms-filter-option">
					<form method="post" action="<?php echo $_SERVER['PHP_SELF'].'?page='.$_GET['page'].''; ?>">
						<select name="exms_filter_essay_rocord" class="exms-filter-options">
							<option value="choose_filters"><?php _e( 'Choose filters', 'exms' ); ?></option>
							<option value="user_id" <?php echo $filter_by == 'user_id' ? 'selected="selected"' : ''; ?> ><?php _e( 'User Id', 'exms' ); ?></option>
							<option value="user_name" <?php echo $filter_by == 'user_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'User Name', 'exms' ); ?></option>
							<option value="quiz_id" <?php echo $filter_by == 'quiz_id' ? 'selected="selected"' : ''; ?> ><?php _e( 'Quiz Id', 'exms' ); ?></option>
						</select>
						<span class="exms-essay-filter-inputs">
							<input value="<?php echo $filter_u_id; ?>" class="exms-e-f-by-id <?php echo $remove_user_id; ?>" type="number" name="exms_filter_by_id" placeholder="<?php _e( 'Filter by user id', 'exms' ); ?>">
							<input value="<?php echo $filter_u_name; ?>" class="exms-e-f-by-name <?php echo $show_user_name; ?>" type="text" name="exms_filter_by_name" placeholder="<?php _e( 'Filter by user name', 'exms' ); ?>">
							<input value="<?php echo $filter_q_id; ?>" class="exms-e-f-by-q-id <?php echo $show_quiz_id . $remove_quiz_id; ?>" type="number" name="exms_filter_by_quiz_id" placeholder="<?php _e( 'Filter by quiz id', 'exms' ); ?>">
						</span>
						<?php wp_nonce_field( 'exms_essay_nonce', 'exms_essay_nonce_field' );?>
						<input type="hidden" name="action" value="exms_essay_action">
						<input type="submit" class="button-secondary" value="<?php _e( 'Filter', 'exms' ); ?>" name="exms_filter_essays">
					</form>
				</div>
			</div>
			<!-- End filters html -->
		</div> 
		<?php
		$params[] = [ 'field' => 'upload_type', 'value' => 'essay', 'operator' => '=', 'type'=> '%s'];
		$essays = isset( wp_exams()->db ) && wp_exams()->db ? wp_exams()->db->exms_db_query( 'select', 'exms_uploads', $params ) : [];
		if( $essays ) {
			
			foreach( $essays as $key => $essay ) {

				$user_profile_url = add_query_arg( 'user_id', $essay->user_id, self_admin_url( 'user-edit.php') );

				if( function_exists( 'exms_get_user_name' ) ) {
					$user_name = exms_get_user_name( $essay->user_id );
				}

				if( $essay->question_id ) {
					$content_post = get_post( $essay->question_id );
				}

				$approved = get_user_meta( $essay->user_id, 'exms_essay_approved', true );
				if( empty( $approved ) ) {
					$approved = [];
				}	
				$aprroved_success = isset( $approved['approved_' . $essay->essay_ids ] ) ? $approved['approved_' . $essay->essay_ids ] : '';

				$approved_btn = '<a class="exms-essay-approve">'.__( 'Approve' ).'</a>';
				if( $aprroved_success ) {
					$approved_btn = __( 'Approved', 'exms' );
				}

				$data_array[] = [
			    	'user_id'		=> '<a 
			    						data-essay-content="'.$essay->content.'" data-user-id="'.$essay->user_id.'" 
			    						data-quiz-id="'.$essay->quiz_id.'" data-essay-id="'.$essay->essay_ids.'" 
			    						data-question="'.$content_post->post_content.'" data-id="'.$essay->id.'" 
			    						class="exms-essay-user" href="'.$user_profile_url.'">'. $essay->user_id .'</a>',
			    	'submit_by'		=> '<a class="exms-essay-by" href="'.$user_profile_url.'">'. $user_name .'</a>',
			    	'topic'			=> '<a href="'.get_edit_post_link( $essay->quiz_id ).'">'. get_the_title( $essay->quiz_id ) .'</a>',
			    	'content'		=> wp_trim_words( $essay->content, 3, '<span class="exms-doted"> ....</span>' ),
			    	'points'		=> $essay->points,
			    	'status'		=> $approved_btn,
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
			'cb'       		=> '<input class="exms-selected-essays" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />',
			'user_id'		=> __( 'User id', 'exms' ),
			'submit_by'		=> __( 'Submitted By', 'exms' ),
			'topic'			=> __( 'Topic', 'exms' ),
			'content'		=> __( 'Content', 'exms' ),
			'points'		=> __( 'Points', 'exms' ),
			'status'		=> __( 'Status', 'exms' ),
		];

		return $columns;
	}

	/**
	 * Checkbox column markup.
	 *
	 * @since BuddyPress 1.6.0
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row).
	 */
	function column_cb( $item ) {
		
		$top_checkbox = '<input class="exms-selected-essays" type="checkbox" id="image_%3$s" name="post[%3$s][]" value="%2$s" />';
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
			case 'user_id':
			case 'submit_by':
			case 'topic':
			case 'content':
			case 'points':
			case 'status':
			return $item[$column_name];
			default:
			return 'no lists found';
		}
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		_e( 'No essays found', 'exms' );
	}

	public function handle_row_actions( $link, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$actions           	= [];
		$actions['edit']   	= '<a href="#TB_inline?&width=600&height=350&inlineId=exms_essay_thickbox" class="thickbox exms-edit-essay"> '.__( 'Edit', 'exms' ). '</a>';
		$actions['delete'] 	= '<a class="exms-delete-essay">' . __( 'Delete' ) . '</a>';
		$actions['view'] 	= '<a href="#TB_inline?&width=600&height=350&inlineId=exms_essay_view" class="thickbox exms-views-essay">' . __( 'View' ) . '</a>';

		return $this->row_actions( $actions );
	}

	/**
	 * Gets a list of sortable columns.
	 * @since 3.1.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {

		$s_columns = [
	         'user_id' 	=> [ 'Name', true], 
	         'content' 	=> [ 'Description', true],
	         'slug' 	=> [ 'Slug', true],
	         'count' 	=> [ 'Count', true]
	    ];

	    return $s_columns;
	}
}

/**
 * WP_list_table instance
 */
function exms_submit_essay_data_table() {

	$exms_taxonomy_table = new EXMS_Essay_Table();
	$exms_taxonomy_table->prepare_items();
	$exms_taxonomy_table->display();
}
exms_submit_essay_data_table();