<?php 
/**
 * Users/Quizzes reports data table
 *
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Include WP List Table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class EXMS_Reports_Data_Table
 */
class EXMS_Reports_Data_Table extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$per_page = 20;
		$current_page = $this->get_pagenum();

		$tab_type = '';
		$tab_name = "";
		$filter = "";
		$datas = "";
		if( ( isset( $_GET['tab'] ) ) && ( isset( $_GET['tab_type'] ) && 'exms_students_reports' == $_GET['tab_type'] ) ) {

			$tab_type = 'exms_student';
			$tab_name = 'Student';

		} elseif( ( isset( $_GET['tab'] ) ) && ( isset( $_GET['tab_type'] ) && 'exms_instructors_reports' == $_GET['tab_type'] ) ) {

			$tab_type = 'exms_nstructor';
			$tab_name = 'Instructor';
		
		} elseif( ( isset( $_GET['tab'] ) ) && $_GET['tab'] == 'reports' || ( isset( $_GET['tab_type'] ) && $_GET['tab_type'] == 'exms_quizzes_reports' ) ) {

			$tab_type = 'exms-quizzes';
			$tab_name = 'Quizzes';
		}

		if( 'exms-quizzes' == $tab_type ) {
			$filter = $this->exms_quiz_filter_records();
			$datas = $this->exms_quiz_reports_table_records( $current_page, $per_page, $tab_name );
		} else if( 'exms_student' == $tab_type ) {
			$filter = $this->exms_student_filter_records();
			$datas = $this->exms_student_reports_table_records( $current_page, $per_page, $tab_name );

		}

		$total_items = $datas['total'];
		$items = $datas['items'];

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page,
		]);

		$this->items = $items;
		$exms_columns  = $this->get_columns();
		$exms_hidden   = $this->get_hidden_columns();
		$exms_sortable = $this->get_sortable_columns();

		$this->_column_headers = [$exms_columns, $exms_hidden, $exms_sortable];
	}


	/**
	 * Display columns datas
	 *
	 * @param $url_order_by, $url_order, $exms_search_term
	 * @return Array
	 */
	public function exms_reports_table_data( $url_order_by = '', $url_order = '', $exms_search_term = '' ) {

		$data_array = [];

		$save_value = '';

		$f_search_query = isset( $_GET['search_query'] ) ? $_GET['search_query'] : '';
		$f_usernames = isset( $_GET['user_name'] ) ? explode( ',', $_GET['user_name'] ) : [];
		$f_quiz_name = isset( $_GET['quiz_name'] ) ? explode( ',', $_GET['quiz_name'] ) : [];
		
		$f_start_date = isset( $_GET['start_date'] ) ? $_GET['start_date'] : '';
		$f_end_date = isset( $_GET['end_date'] ) ? $_GET['end_date'] : '';

		$tab_type = '';
		if( ( isset( $_GET['tab'] ) ) && ( isset( $_GET['tab_type'] ) && 'exms_students_reports' == $_GET['tab_type'] ) ) {

			$tab_type = 'exms_student';

		} elseif( ( isset( $_GET['tab'] ) ) && ( isset( $_GET['tab_type'] ) && 'exms_instructors_reports' == $_GET['tab_type'] ) ) {

			$tab_type = 'exms_instructor';
		
		} elseif( ( isset( $_GET['tab'] ) ) && $_GET['tab'] == 'reports' || ( isset( $_GET['tab_type'] ) && $_GET['tab_type'] == 'exms_quizzes_reports' ) ) {

			$tab_type = 'quizzes';
		}

		?>
		<div class="exms-seach-form">
			<h2><?php echo ucwords( str_replace( 'exms_', ' ', $tab_type ) ) . ' '.__( 'Records', 'exms' ).' '; ?></h2>
		
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<select class="exms-filter-reports" name="exms_search_query">
					<option value="choose_filters"><?php _e( 'Choose filters', 'exms' ); ?></option>
					
					<?php if( 'quizzes' != $tab_type ) { ?>
					<option value="user_name" <?php echo $f_search_query == 'user_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'User name', 'exms' ); ?></option>
					<?php } ?>
					<option value="quiz_name" <?php echo $f_search_query == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Quiz name', 'exms' ); ?></option>
					<?php if( 'quizzes' == $tab_type ) { ?>
						<option value="quiz_name" <?php echo $f_search_query == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Filter by name', 'exms' ); ?></option>
						<option value="quiz_name" <?php echo $f_search_query == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Filter by Group', 'exms' ); ?></option>
						<option value="quiz_name" <?php echo $f_search_query == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Filter by Course', 'exms' ); ?></option>
						<option value="quiz_name" <?php echo $f_search_query == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Filter by Students', 'exms' ); ?></option>
						<option value="date" <?php echo $f_search_query == 'date' ? 'selected="selected"' : ''; ?> ><?php _e( 'Filter by Date', 'exms' ); ?></option>
					<?php } ?>

				</select>
				<?php if( 'quizzes' == $tab_type ) { ?>
				<input type="date" name="exms_start_date" class="exms-filter-dates <?php echo $f_start_date ? 'filter-show' : ''; ?>" value="<?php echo $f_start_date; ?>">
				<input type="date" name="exms_end_date" class="exms-filter-dates <?php echo $f_start_date ? 'filter-show' : ''; ?>" value="<?php echo $f_end_date; ?>">
				<?php } ?>

				<span class="exms-filter-field <?php echo $f_start_date ? 'exms-hide' : ''; ?>">
					<select name="exms_filter_search_by[]" class="form-control exms-select2" multiple="multiple">
						<?php foreach( $f_quiz_name as $qname ) { ?>
							<option value="<?php echo $qname;?>" data-select2-tag="true"><?php echo $qname;?></option>
						<?php } ?>
					</select>
				</span>

				<?php wp_nonce_field( 'exms_filter_nonce', 'exms_filter_nonce_field' );?>
				<input type="hidden" name="action" value="exms_filter_action">
				<input class="button button-primary" type="submit" name="exms_search_submit" value="<?php _e( 'Filter', 'exms' ); ?>">
			</form>
		</div>
		<section class="exms-report-data-table">
		<?php

		$user_args = '';

		if( ! $f_usernames && ! $f_quiz_name && 'quizzes' != $tab_type ) {

			$user_args = get_users( array(
		        'fields'	=> 'ID',
		        'role'      => $tab_type,
		        'number'    => -1,
		    ) );
		}

	    /**
		 * Filter by user name
		 */
		if( $f_usernames ) {

			foreach( $f_usernames as $user_name ) {

				$user = get_user_by( 'login', $user_name );
	            if( $user ) {

	            	$user_meta = get_userdata( $user->ID );
					$user_roles = $user_meta->roles;

					if( in_array( $tab_type, $user_roles ) ) {

						$user_args['fields'][] = (int) $user->ID;
					}
	            }
			}
        } 

        $args = isset( $user_args['fields'] ) ? $user_args['fields'] : $user_args;

		if( $args ) {

			foreach( $args as $user_id ) {
				
				$enrolled_quizzes = exms_get_user_enrolled_quizzes( $user_id );
				$quizzes_count = count( $enrolled_quizzes );
				$progress_count = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user_id ) : [];
				$user_name = ucwords( get_userdata( (int) $user_id )->user_nicename );
            	$user_profile_url = add_query_arg( 'user_id', $user_id, self_admin_url( 'user-edit.php') );
            	
        		$data_array[] = [
			    	'exms_name'		=> '<a href="'.$user_profile_url.'">'. $user_name .'</a>',
			    	'exms_quizzes'	=> $quizzes_count ? $quizzes_count : '-',
			    	'exms_progress'	=> $progress_count ? count( $progress_count ) : '-',
			    	'exms_action'	=> '<a class="exms-action-col" href="'.admin_url( 'admin.php?exms_user_by=ID&exms_report_user='.$user_id.'&page=exms_user_report' ).'">' .__( 'View More', 'exms' ) . '</a>'
			    ];
			}

		} elseif( $f_quiz_name ) {

			/**
			 * handle filtration with quiz name
			 */
			$existing_users = [];
			foreach( $f_quiz_name as $quiz_name ) {

				$quiz = exms_get_page_by_title( $quiz_name, '', 'exms_quizzes' );
				if( ! $quiz ) {
					continue;
				}

				$options = exms_get_post_options( $quiz->ID );
        		$enrolled_users = isset( $options['exms_quiz_'.$tab_type.'s_assigned'] ) ? $options['exms_quiz_'.$tab_type.'s_assigned'] : [];
        		if( ! is_array( $enrolled_users ) ) {
        			continue;
        		}

        		foreach( $enrolled_users as $user_id ) {

        			$enrolled_quizzes = function_exists( 'exms_get_user_enrolled_quizzes' ) ? exms_get_user_enrolled_quizzes( $user_id ) : [];
					$quizzes_count = count( $enrolled_quizzes );

					$progress_count = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user_id ) : [];

        			$user_name = ucwords( get_userdata( (int) $user_id )->user_nicename );

            		if( in_array( $user_id, $existing_users ) ) {

            			continue;
            		}

            		$user_profile_url = add_query_arg( 'user_id', $user_id, self_admin_url( 'user-edit.php') );

            		$existing_users[] = $user_id;
        			$data_array[] = [
				    	'exms_name'		=> '<a href="'.$user_profile_url.'">'. $user_name .'</a>',
				    	'exms_quizzes'	=> $quizzes_count ? $quizzes_count : '-',
				    	'exms_progress'	=> $progress_count ? count( $progress_count ) : '-',
				    	'exms_action'	=> '<a href="'.admin_url( 'admin.php?exms_user_by=ID&exms_report_user='.$user_id.'&page=exms_user_report' ).'">' .__( 'See details', 'exms' ) . '</a>'
				    ];
        		}

        		if( 'quizzes' == $tab_type ) {

					$enrolled_users = exms_quiz_enrolled_users_count( $quiz->ID );
					$user_progress = exms_quiz_complete_users_count( $quiz->ID );

					$data_array[] = [
				    	'exms_name'		=> '<a href="'.get_edit_post_link( $quiz->ID ).'">'. get_the_title( $quiz->ID ) .'</a>',
				    	'exms_quizzes'	=> $enrolled_users ? $enrolled_users : '-',
				    	'exms_progress'	=> $user_progress ? $user_progress : '-',
				    	'exms_action'	=> '<a href="'.admin_url( 'admin.php?exms_quiz_by=quiz_id&exms_report_quiz='.$quiz->ID.'&page=exms_quiz_report' ).'">' .__( 'See details', 'exms' ) . '</a>'
				    ];
				}
			}

		} elseif( 'quizzes' == $tab_type ) {

			/**
			 * Filter by custom dates
			 */
			$args = [];
			if( $f_start_date && $f_end_date ) {

				$args['date_query'] = array(
			        'after'   => $f_start_date,
			        'before'  => $f_end_date
			    );
			}

			$quizzes = exms_get_quizzes( $args ? $args : '' );

			if( $quizzes ) {

				foreach( $quizzes as $quiz ) {

	        		$enrolled_users = exms_quiz_enrolled_users_count( $quiz->ID );
					$user_progress = exms_quiz_complete_users_count( $quiz->ID );

					$data_array[] = [
				    	'exms_name'		=> '<a href="'.get_edit_post_link( $quiz->ID ).'">'. get_the_title( $quiz->ID ) .'</a>',
				    	'exms_quizzes'	=> $enrolled_users ? $enrolled_users : '-',
				    	'exms_progress'	=> $user_progress ? $user_progress : '-',
				    	'exms_result'	=> $user_progress ? $user_progress : '-',
				    	'exms_date'	=> $user_progress ? $user_progress : '-',
				    	'exms_action'	=> '<a href="'.admin_url( 'admin.php?exms_quiz_by=quiz_id&exms_report_quiz='.$quiz->ID.'&page=exms_quiz_report' ).'">' .__( 'See details', 'exms' ) . '</a>'
				    ];
				}
			}
		}

		return $data_array;

		?></section><?php
	}

	/**
	 * Filters
	 * @return void
	 */
	public function exms_quiz_filter_records() {

		$exms_post_types = get_option('exms_post_types', array());	
		?>
			<div class="exms-search-content">
				<button class="exms-filter-popup"> 
					<span class="dashicons dashicons-filter"></span> 
					<?php echo __( 'Filters ', 'exms' ); ?>
				</button>
				<div class="exms-search-wrapper">
				<span class="dashicons dashicons-search"></span>
				<form method="get" action="">
					<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? 'exms-reports'); ?>">
					<input class="exms-search-input" type="search" name="s" value="<?php echo esc_attr($_GET['s'] ?? ''); ?>" placeholder="Search Users by Name and Date">
				</form>
			</div>

			</div>

		<div class="exms-search-form">
			<div class="exms-search-modal-form">
				<div class="exms-filter-modal-wrapper">
					<span class="exms-filter-modal-close">&times;</span>
				</div>
				<form method="post" action="" id="exms-filter-form">
					<div class="exms-filters-fields">
						<div class="exms-filter-options">
							<div class="exms-filter-options-header">
								<div class="exms-filter-options-update">
								<h4> <?php _e( 'Filter Option', 'exms' ); ?> </h4>
								<button class="exms-filter-reset-btn"> 
									<span class="dashicons dashicons-update-alt"></span>	
									<?php _e( 'Reset', 'exms' ); ?>
								</button>
								</div>
								<div class="exms-filter-options-update">
									<select class="exms-filter-reports exms-search-select" name="exms_search_query" id="exms_search_query">
										<option value="choose_filters"><?php _e( 'Choose filters', 'exms' ); ?></option>
										<option value="exms_students"><?php _e( 'Filter by Students', 'exms' ); ?></option>
										<option value="exms_quiz_name"><?php _e( 'Quiz name', 'exms' ); ?></option>
										<option value="exms_group_name"><?php _e( 'Filter by Group', 'exms' ); ?></option>
										<option value="exms_course_name"><?php _e( 'Filter by Course', 'exms' ); ?></option>
										<option value="exms_tags"><?php _e( 'Filter by Tags', 'exms' ); ?></option>
										<option value="exms_category"><?php _e( 'Filter by Category', 'exms' ); ?></option>
										<option value="exms_date"><?php _e( 'Filter by Date', 'exms' ); ?></option>
									</select>
									<div class="exms-filter-field exms-dynamic-field">
										<select name="exms_dynamic_filter" id="exms_dynamic_filter" class="exms-search-select">
											<option value=""><?php _e( 'Select Option', 'exms' ); ?></option>
										</select>
									</div>
								</div>
							</div>
							
						</div>
					</div>
					<div class="exms-filter-field exms-custom-date-field" style="display:none;">
						<input disabled type="date" name="exms_start_date">
						<input disabled type="date" name="exms_end_date">
					</div>
					<?php if( !empty( $exms_post_types ) ) { ?>
						<div class="exms-filter-field exms-hierarchy-field" style="display:none;">
							<?php foreach( $exms_post_types as $slug => $type ) { ?>
								<select disabled name="<?php echo esc_attr($slug); ?>[]" class="exms-hierarchy-select exms-search-select">
									<option value=""><?php echo sprintf( __( 'Select %s', 'exms' ), $type['singular_name'] ); ?></option>
								</select>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if(!empty($exms_post_types)) { ?>
						<div class="exms-filter-field exms-course-hierarchy-field" style="display:none;">
							<?php foreach($exms_post_types as $slug => $type) { ?>
								<?php if($slug === 'exms-courses') continue; ?>
								<select disabled name="<?php echo esc_attr($slug); ?>[]" class="exms-hierarchy-select exms-search-select">
									<option value=""><?php echo sprintf(__('Select %s', 'exms'), $type['singular_name']); ?></option>
								</select>
							<?php } ?>
						</div>
					<?php } ?>
					<div class="exms-filter-option-btn">
						<button class="exms-all-filter-reset-btn"><?php _e( 'Reset All', 'exms' ); ?></button>
						<button class="exms-filter-btn" name="exms_search_submit"><?php _e( 'Apply Filters', 'exms' ); ?></button>
					</div>
				</form>
			</div>
		</div>

		<div class="exms-overlay"></div>
		<div class="exms-reports-modal">
			<div class="exms-reports-modal-content">
				<div class="exms-reports-header">
					<h2><span class="exms-quiz-heading"></span> <?php _e( ' - Report Data', 'exms' );?> </h2>
					<span class="exms-modal-close">X</span>
				</div>

				<div class="exms-reports-content">
					<div class="exms-quiz-message"></div>
					<div class="exms-quiz-report-result">
						<div class="exms-quiz-report-details">
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Enrolled Course: ", "wp_exams") ?></span> 
								<span class="exms-course-name exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Course Instructor: ", "wp_exams") ?></span> 
								<span class="exms-course-instructor exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("User: ", "wp_exams") ?></span> 
								<span class="exms-username exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Attempt #: ", "wp_exams") ?></span> 
								<span class="exms-attempt exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Result: ", "wp_exams") ?></span>
								<span class="exms-quiz-pass exms-report-values"></span>
							</div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Total Wrong Questions: ", "wp_exams") ?></span> <span class="exms-wrong-answers exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Total Correct Questions: ", "wp_exams") ?></span> <span class="exms-correct-answers exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("In Review Questions: ", "wp_exams") ?></span> <span class="exms-review exms-report-values"></span></div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Quiz Passing Percentage: ", "wp_exams") ?></span>
								<span class="exms-quiz-passing-percentage exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Percentage: ", "wp_exams") ?></span> 
								<span class="exms-percentage exms-report-values"></span></div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Quiz Total Time: ", "wp_exams") ?></span>
								<span class="exms-quiz-total-time exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Time Taken By Student: ", "wp_exams") ?></span>
								<span class="exms-time exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Obtained Marks: ", "wp_exams") ?></span> 
								<span class="exms-obtained exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label exms-date-label"><?php _e("Attempted Date:", "wp_exams") ?></span> 
								<span class="exms-date exms-report-values"></span>
							</div>
						</div>
					</div>
					<table class="exms-attempts-table widefat striped">
						<thead>
							<tr>
								<th><?php _e( 'Question', 'exms' );?></th>
								<th><?php _e( 'Question Type', 'exms' );?></th>
								<th><?php _e( 'Submitted Answer', 'exms' );?></th>
								<th><?php _e( 'Correct Answer', 'exms' );?></th>
								<th><?php _e( 'File', 'exms' );?></th>
								<th><?php _e( 'Status', 'exms' );?></th>
								<th></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="exms-user-history-header">
					<h2>
						<button class="exms-back-report">
							<span class="dashicons dashicons-arrow-left-alt"></span>
						</button>
						<span class="exms-quiz-heading"></span> 
						<?php _e( ' - Report Data', 'exms' );?> </h2>
					<span class="exms-user-history-modal-close">X</span>
				</div>
				<div class="exms-user-history-content">
					<table class="exms-attempts-table widefat striped">
						<thead>
							<tr>
								<th><?php _e( 'Course Name', 'exms' );?></th>
								<th><?php _e( 'Quiz Name', 'exms' );?></th>
								<th><?php _e( 'Percentage Status', 'exms' );?></th>
								<th><?php _e( 'Attempt Date', 'exms' );?></th>
								<th><?php _e( 'Passed', 'exms' );?></th>
								<th><?php _e( 'Attempt Number', 'exms' );?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
					<input type="hidden" class="exms-user-history-id">
				</div>
			</div>
		</div>
		<div class="exms-comments-modal">
			<div class="exms-comments-content">
				<div class="exms-comment-header">
				<h2><span class="exms-comment-heading"></span> <?php _e( 'Comment(s)', 'exms' ); ?></h2>
				<span class="exms-comment-modal-close">&times;</span>
				</div>

				<div class="exms-new-comment">
					<?php wp_editor( '', 'exms_comment_editor', [
						'textarea_name' => 'exms_comment_text',
						'textarea_rows' => 5,
						'media_buttons' => true,
						'teeny'         => false,
						'quicktags'     => true,
						'editor_height' => 200,
					]  ); ?>
					<div class="exms-comment-actions">
						<input type="hidden" class="exms-comment-file-url">
						<button type="button" class="exms-submit-comment exms-comment-submit">Comment</button>
						<input type="hidden" class="exms-qid">
						<input type="hidden" class="exms-uid">
						<input type="hidden" class="exms-quiz">
						<input type="hidden" class="exms-attempt-number">
					</div>
				</div>

				<div class="exms-comment-list">
					<div class="exms-comment-item">
						<p></p>
					</div>
				</div>
				<div class="exms-save-message"></div>
				<div class="exms-unsave-message"></div>
			</div>
		</div>

		<div class="exms-file-overlay"></div>
		<div class="exms-file-sidebar">
			<div class="exms-file-sidebar-content">
				<span class="exms-file-sidebar-close">X</span>
				<a class="exms-download-file" href="#" download> <span class="dashicons dashicons-download"></span></a>
				<img class="exms-file-frame" src="" alt="Preview" />
			</div>
		</div>

		<?php
	}
	
	/**
	 * Student Filters
	 * @return void
	 */
	public function exms_student_filter_records() {

		$exms_post_types = get_option('exms_post_types', array());	
		?>
			<div class="exms-search-content">
				<button class="exms-filter-popup"> 
					<span class="dashicons dashicons-filter"></span> 
					<?php echo __( 'Filters ', 'exms' ); ?>
				</button>
				<div class="exms-search-wrapper">
				<span class="dashicons dashicons-search"></span>
				<form method="get" action="">
					<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page'] ?? 'exms-reports'); ?>">
					<input class="exms-student-search-input" type="search" name="s" value="<?php echo esc_attr($_GET['s'] ?? ''); ?>" placeholder="Search Students by Name, Enrolled By and Date ">
				</form>
			</div>

			</div>

		<div class="exms-search-form">
			<div class="exms-search-modal-form">
				<div class="exms-filter-modal-wrapper">
					<span class="exms-filter-modal-close">&times;</span>
				</div>
				<form method="post" action="" id="exms-filter-form">
					<div class="exms-filters-fields">
						<div class="exms-filter-options">
							<div class="exms-filter-options-header">
								<div class="exms-filter-options-update">
								<h4> <?php _e( 'Filter Option', 'exms' ); ?> </h4>
								<button class="exms-filter-reset-btn"> 
									<span class="dashicons dashicons-update-alt"></span>	
									<?php _e( 'Reset', 'exms' ); ?>
								</button>
								</div>
								<div class="exms-filter-options-update">
									<select class="exms-filter-reports exms-search-select" name="exms_search_query" id="exms_search_query">
										<option value="choose_filters"><?php _e( 'Choose filters', 'exms' ); ?></option>
										<option value="exms_students"><?php _e( 'Filter by Students', 'exms' ); ?></option>
										<option value="exms_quiz_name"><?php _e( 'Quiz name', 'exms' ); ?></option>
										<option value="exms_group_name"><?php _e( 'Filter by Group', 'exms' ); ?></option>
										<option value="exms_course_name"><?php _e( 'Filter by Course', 'exms' ); ?></option>
										<option value="exms_tags"><?php _e( 'Filter by Tags', 'exms' ); ?></option>
										<option value="exms_category"><?php _e( 'Filter by Category', 'exms' ); ?></option>
										<option value="exms_date"><?php _e( 'Filter by Date', 'exms' ); ?></option>
									</select>
									<div class="exms-filter-field exms-dynamic-field">
										<select name="exms_dynamic_filter" id="exms_dynamic_filter" class="exms-search-select">
											<option value=""><?php _e( 'Select Option', 'exms' ); ?></option>
										</select>
									</div>
								</div>
							</div>
							
						</div>
					</div>
					<div class="exms-filter-field exms-custom-date-field" style="display:none;">
						<input disabled type="date" name="exms_start_date">
						<input disabled type="date" name="exms_end_date">
					</div>
					<?php if( !empty( $exms_post_types ) ) { ?>
						<div class="exms-filter-field exms-hierarchy-field" style="display:none;">
							<?php foreach( $exms_post_types as $slug => $type ) { ?>
								<select disabled name="<?php echo esc_attr($slug); ?>[]" class="exms-hierarchy-select exms-search-select">
									<option value=""><?php echo sprintf( __( 'Select %s', 'exms' ), $type['singular_name'] ); ?></option>
								</select>
							<?php } ?>
						</div>
					<?php } ?>
					<?php if(!empty($exms_post_types)) { ?>
						<div class="exms-filter-field exms-course-hierarchy-field" style="display:none;">
							<?php foreach($exms_post_types as $slug => $type) { ?>
								<?php if($slug === 'exms-courses') continue; ?>
								<select disabled name="<?php echo esc_attr($slug); ?>[]" class="exms-hierarchy-select exms-search-select">
									<option value=""><?php echo sprintf(__('Select %s', 'exms'), $type['singular_name']); ?></option>
								</select>
							<?php } ?>
						</div>
					<?php } ?>
					<div class="exms-filter-option-btn">
						<button class="exms-all-filter-reset-btn"><?php _e( 'Reset All', 'exms' ); ?></button>
						<button class="exms-filter-btn" name="exms_search_submit"><?php _e( 'Apply Filters', 'exms' ); ?></button>
					</div>
				</form>
			</div>
		</div>

		<div class="exms-overlay"></div>
		<div class="exms-reports-modal">
			<div class="exms-reports-modal-content">
				<div class="exms-reports-header">
					<h2><span class="exms-quiz-heading"></span> <?php _e( ' - Report Data', 'exms' );?> </h2>
					<span class="exms-modal-close">X</span>
				</div>

				<div class="exms-reports-content">
					<div class="exms-quiz-message"></div>
					<div class="exms-quiz-report-result">
						<div class="exms-quiz-report-details">
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Enrolled Course: ", "wp_exams") ?></span> 
								<span class="exms-course-name exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Course Instructor: ", "wp_exams") ?></span> 
								<span class="exms-course-instructor exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("User: ", "wp_exams") ?></span> 
								<span class="exms-username exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Attempt #: ", "wp_exams") ?></span> 
								<span class="exms-attempt exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Result: ", "wp_exams") ?></span>
								<span class="exms-quiz-pass exms-report-values"></span>
							</div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Total Wrong Questions: ", "wp_exams") ?></span> <span class="exms-wrong-answers exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Total Correct Questions: ", "wp_exams") ?></span> <span class="exms-correct-answers exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("In Review Questions: ", "wp_exams") ?></span> <span class="exms-review exms-report-values"></span></div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Quiz Passing Percentage: ", "wp_exams") ?></span>
								<span class="exms-quiz-passing-percentage exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Percentage: ", "wp_exams") ?></span> 
								<span class="exms-percentage exms-report-values"></span></div>
							
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Quiz Total Time: ", "wp_exams") ?></span>
								<span class="exms-quiz-total-time exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Time Taken: ", "wp_exams") ?></span>
								<span class="exms-time exms-report-values"></span>
							</div>
							<div class="exms-detail">
								<span class="exms-label"><?php _e("Obtained Marks: ", "wp_exams") ?></span> 
								<span class="exms-obtained exms-report-values"></span></div>
							<div class="exms-detail">
								<span class="exms-label exms-date-label"><?php _e("Date:", "wp_exams") ?></span> 
								<span class="exms-date exms-report-values"></span>
							</div>
						</div>
					</div>
					<table class="exms-attempts-table widefat striped">
						<thead>
							<tr>
								<th><?php _e( 'Question', 'exms' );?></th>
								<th><?php _e( 'Question Type', 'exms' );?></th>
								<th><?php _e( 'Submitted Answer', 'exms' );?></th>
								<th><?php _e( 'Correct Answer', 'exms' );?></th>
								<th><?php _e( 'File', 'exms' );?></th>
								<th><?php _e( 'Status', 'exms' );?></th>
								<th></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>

				<div class="exms-user-history-header">
					<h2>
						<button class="exms-back-report">
							<span class="dashicons dashicons-arrow-left-alt"></span>
						</button>
						<span class="exms-quiz-heading"></span> 
						<?php _e( ' - Report Data', 'exms' );?> </h2>
					<span class="exms-user-history-modal-close">X</span>
				</div>
				<div class="exms-user-history-content">
					<table class="exms-attempts-table widefat striped">
						<thead>
							<tr>
								<th><?php _e( 'Course Name', 'exms' );?></th>
								<th><?php _e( 'Quiz Name', 'exms' );?></th>
								<th><?php _e( 'Percentage Status', 'exms' );?></th>
								<th><?php _e( 'Attempt Date', 'exms' );?></th>
								<th><?php _e( 'Passed', 'exms' );?></th>
								<th><?php _e( 'Attempt Number', 'exms' );?></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
					<input type="hidden" class="exms-user-history-id">
				</div>
			</div>
		</div>
		<div class="exms-comments-modal">
			<div class="exms-comments-content">
				<div class="exms-comment-header">
				<h2><span class="exms-comment-heading"></span> <?php _e( 'Comment(s)', 'exms' ); ?></h2>
				<span class="exms-comment-modal-close">&times;</span>
				</div>

				<div class="exms-new-comment">
					<?php wp_editor( '', 'exms_comment_editor', [
						'textarea_name' => 'exms_comment_text',
						'textarea_rows' => 5,
						'media_buttons' => true,
						'teeny'         => false,
						'quicktags'     => true,
						'editor_height' => 200,
					]  ); ?>
					<div class="exms-comment-actions">
						<input type="hidden" class="exms-comment-file-url">
						<button type="button" class="exms-submit-comment exms-comment-submit">Comment</button>
						<input type="hidden" class="exms-qid">
						<input type="hidden" class="exms-uid">
						<input type="hidden" class="exms-quiz">
						<input type="hidden" class="exms-attempt-number">
					</div>
				</div>

				<div class="exms-comment-list">
					<div class="exms-comment-item">
						<p></p>
					</div>
				</div>
				<div class="exms-save-message"></div>
				<div class="exms-unsave-message"></div>
			</div>
		</div>

		<div class="exms-file-overlay"></div>
		<div class="exms-file-sidebar">
			<div class="exms-file-sidebar-content">
				<span class="exms-file-sidebar-close">X</span>
				<a class="exms-download-file" href="#" download> <span class="dashicons dashicons-download"></span></a>
				<img class="exms-file-frame" src="" alt="Preview" />
			</div>
		</div>

		<?php
	}
	
	/**
	 * Display quiz columns datas
	 */
	public function exms_quiz_reports_table_records( $tab_name, $page = 1, $per_page = 20 ) {

		?>
		<section class="exms-report-data-table">
			<div class="exms-report-data-header">
				<div class="exms-report-heading">
					<h3> <?php echo $tab_name . __( ' Reports', 'exms' ); ?></h3>
				</div>
				<div class="exms-report-content-btn">
					<!-- <a class="exms-email-btn" href="#">
						<span class="dashicons dashicons-email"></span>
						<?php _e( 'Send Email', 'exms' ); ?>
					</a> -->
					<a class="exms-download-btn" href="#" id="exms-download-btn">
						<?php _e( 'Download', 'exms' ); ?>
					</a>
				</div>
			</div>
		<?php

			global $wpdb;

			$offset = ($page - 1) * $per_page;
			$table = $wpdb->prefix . 'exms_quizzes_results';

			$total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT r.id, r.user_id, r.quiz_id, r.course_id,
							r.obtained_points, r.correct_questions, r.wrong_questions, 
							r.not_attempt, r.review_questions, r.passed, r.percentage, 
							r.time_taken, r.result_date, r.attempt_number,
							qa.question_id, qa.question_type, qa.attempt_date, qa.file_url, qa.is_correct, qa.answer, qa.comment,
							wq.quiz_timer, wq.passing_percentage, wq.pass_quiz_message, wq.fail_quiz_message
					FROM {$table} AS r
					LEFT JOIN {$wpdb->users} AS u ON u.ID = r.user_id
					LEFT JOIN {$wpdb->posts} AS q ON q.ID = r.quiz_id
					LEFT JOIN wp_exms_exam_user_question_attempts AS qa 
							ON qa.user_id = r.user_id 
							AND qa.quiz_id = r.quiz_id
							AND qa.attempt_number = r.attempt_number
					LEFT JOIN wp_exms_quiz AS wq ON wq.quiz_id = r.quiz_id
					ORDER BY r.id DESC
					LIMIT %d OFFSET %d",
					$per_page,
					$offset
				),
				ARRAY_A
			);


			$data_array = [];
			$temp = [];

			if ($rows) {
				foreach ($rows as $row) {
					$rid = $row['id'] ?? 0;

					if (!isset($temp[$rid])) {
						$user = !empty($row['user_id']) ? get_userdata($row['user_id']) : null;
						$quiz = !empty($row['quiz_id']) ? get_post($row['quiz_id']) : null;
						$course = !empty($row['course_id']) ? get_post($row['course_id']) : null;
						$course_instructor_ids = $course ? exms_get_assign_instructor_ids($course->ID) : [];
						$instructor_names = [];

						if ( !empty($course_instructor_ids) && is_array($course_instructor_ids) ) {
							foreach ( $course_instructor_ids as $iid ) {
								$instructor_names[] = exms_get_user_name($iid);
							}
						} elseif ( !empty($course_instructor_ids) && is_numeric($course_instructor_ids) ) {
							$instructor_names[] = exms_get_user_name($course_instructor_ids);
						}

						$course_instructor_name = !empty($instructor_names)
							? implode(', ', $instructor_names)
							: __('-', 'exms');

						$temp[$rid] = [
							'id'              => $row['id'] ?? 0,
							'user_id'         => $row['user_id'] ?? 0,
							'quiz_id'         => $row['quiz_id'] ?? 0,
							'obtained_points' => $row['obtained_points'] ?? 0,
							'points_type'     => $row['points_type'] ?? '',
							'correct_questions' => $row['correct_questions'] ?? 0,
							'wrong_questions'   => $row['wrong_questions'] ?? 0,
							'not_attempt'       => $row['not_attempt'] ?? 0,
							'review_questions'  => $row['review_questions'] ?? 0,
							'passed'            => $row['passed'] ?? 0,
							'time_taken'        => $row['time_taken'] ?? '',
							'result_date'       => $row['result_date'] ?? '',
							'attempt_number'    => $row['attempt_number'] ?? 0,
							'quiz_timer'          => $row['quiz_timer'] ?? '',
							'passing_percentage'  => $row['passing_percentage'] ?? 0,
							'pass_quiz_message'   => $row['pass_quiz_message'] ?? '',
							'fail_quiz_message'   => $row['fail_quiz_message'] ?? '',

							'user'        => $user ? '<a href="' . add_query_arg('user_id', $user->ID, self_admin_url('user-edit.php')) . '">' . $user->display_name . '</a>' : __('Unknown User', 'exms'),
							'user_name'   => $user ? $user->display_name : __('Unknown User', 'exms'),
							'quiz'        => $quiz ? '<a href="' . get_edit_post_link($quiz->ID) . '">' . get_the_title($quiz->ID) . '</a>' : __('Unknown Quiz', 'exms'),
							'quiz_name'   => $quiz ? get_the_title($quiz->ID) : __('Unknown Quiz', 'exms'),
							'course'      => $course ? '<a href="' . get_edit_post_link($course->ID) . '">' . get_the_title($course->ID) . '</a>' : __('-', 'exms'),
							'instructor_id'   => $course_instructor_ids,
							'instructor_name' => $course_instructor_name,
							'course_name' => $course ? get_the_title($course->ID) : __('-', 'exms'),
							'percentage'  => ($row['percentage'] ?? 0) . '%',
							'date'        => !empty($row['result_date'])
												? date(get_option('date_format') . ' ' . get_option('time_format'), (int) $row['result_date'])
												: '',
							'attempts'    => []
						];
					}

					if(!empty($row['question_id'])) {
						$question_post = get_post($row['question_id']);
						$question_desc = $question_post
							? wp_trim_words(wp_strip_all_tags($question_post->post_content), 30, '...')
							: __('No Question', 'exms');

						$comment_value = '';
						if( !empty($row['comment']) ) {
							$comment_obj = get_comment( intval($row['comment']) );
							if ( $comment_obj ) {
								$comment_value = $comment_obj->comment_content;
							} else {
								$comment_value = $row['comment'];
							}
						}
						$temp[$rid]['attempts'][] = [
							'question_id'    => $row['question_id'],
							'question_desc'  => $question_desc,
							'question_type'  => $row['question_type'] ?? '',
							'attempt_number' => $row['attempt_number'] ?? 0,
							'file_url'       => $row['file_url'] ?? '',
							'is_correct'     => $row['is_correct'] ?? '',
							'answer'         => $row['answer'] ?? '',
							'comment'         => $comment_value,
							'attempt_date'   => !empty($row['attempt_date'])
								? wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $row['attempt_date'])
								: '',
							'correct_answer'=> exms_get_question_correct_answer( $row['question_id'], $row['question_type'] )
						];
					}
				}

				foreach ($temp as $res) {
					$data_array[] = [
						'id'            => $res['id'],
						'cb'            => sprintf(
							'<input type="checkbox" class="exms-select" name="bulk-select[]" value="%s" />',
							esc_attr($res['id'])
						),
						'exms_user'       => $res['user'],
						'exms_name'       => $res['quiz'],
						'exms_percentage' => '
							<div class="exms-percentage-wrapper" 
								data-user="' . esc_attr($res['user_id']) . '" 
								data-quiz="' . esc_attr($res['quiz_id']) . '" 
								data-attempt="' . esc_attr($res['attempts'][0]['attempt_number'] ?? 0) . '">

								<div class="exms-percentage-header">
									<span class="exms-status-badge ' . ($res['passed'] ? 'exms-pass' : 'exms-fail') . '">
										' . ($res['passed'] ? __('Pass', 'exms') : __('Fail', 'exms')) . '
									</span>
									<span class="exms-percentage-value">' . esc_html($res['percentage']) . '</span>
								</div>

								<div class="exms-percentage-bar">
									<div class="exms-percentage-fill" style="width:' . intval($res['percentage']) . '%"></div>
								</div>
							</div>
						',

						'exms_date'       => $res['date'],
						'exms_action' => '<a class="exms-action-col exms-report-action" data-attempts=\'' . esc_attr(wp_json_encode([
							'attempts' => $res['attempts'],
							'quiz_name'   => $res['quiz_name'],
							'user_name'   => $res['user_name'],
							'course_name'   => $res['course_name'],
							'percentage'  => $res['percentage'],
							'additional'  => [
								'id'               => $res['id'],
								'user_id'          => $res['user_id'],
								'quiz_id'          => $res['quiz_id'],
								'obtained_points'  => $res['obtained_points'],
								'points_type'      => $res['points_type'],
								'correct_questions'=> $res['correct_questions'],
								'wrong_questions'  => $res['wrong_questions'],
								'not_attempt'      => $res['not_attempt'],
								'review_questions' => $res['review_questions'],
								'passed'           => $res['passed'],
								'percentage'       => $res['percentage'],
								'time_taken'       => $res['time_taken'],
								'result_date'      => $res['result_date'],
								'attempt_number'   => $res['attempt_number'],
								'quiz_timer'          => $res['quiz_timer'],
								'passing_percentage'  => $res['passing_percentage'],
								'pass_quiz_message'   => $res['pass_quiz_message'],
								'fail_quiz_message'   => $res['fail_quiz_message'],
								'instructor_id'    => $res['instructor_id'],
   								'instructor_name'  => $res['instructor_name'],
							]
						])) . '\'>' . __('View More', 'exms') . '</a>'
					];
				}
			}

			return [
				'total' => $total_items,
				'items' => $data_array
			];
		?>
		</section>
		<?php
	}
	
	/**
	 * Display student columns datas
	 */
	public function exms_student_reports_table_records( $tab_name, $page = 1, $per_page = 20 ) {

		?>
		<section class="exms-report-data-table">
			<div class="exms-report-data-header">
				<div class="exms-report-heading">
					<h3><?php echo esc_html($tab_name . __( ' Reports', 'exms' )); ?></h3>
				</div>
				<div class="exms-report-content-btn">
					<a class="exms-student-download-btn" href="#" id="exms-student-download-btn">
						<?php _e( 'Download', 'exms' ); ?>
					</a>
				</div>
			</div>
		<?php

		global $wpdb;

		$offset = ($page - 1) * $per_page;
		$table  = $wpdb->prefix . 'exms_user_enrollments';

		$total_items = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$table} AS e
				INNER JOIN {$wpdb->users} AS u ON u.ID = e.user_id
				INNER JOIN {$wpdb->posts} AS p ON p.ID = e.post_id
				LEFT JOIN {$wpdb->users} AS en ON en.ID = e.enrolled_by
				INNER JOIN {$wpdb->usermeta} AS um 
					ON um.user_id = u.ID 
					AND um.meta_key = '{$wpdb->prefix}capabilities'
				WHERE um.meta_value LIKE %s",
				'%exms_student%'
			)
		);

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
					e.id,
					e.user_id,
					u.display_name AS student_name,
					e.post_id,
					p.post_title AS post_name,
					e.post_type,
					e.enrolled_by,
					en.display_name AS enrolled_by_name,
					e.progress_percent,
					e.start_date
				FROM {$table} AS e
				INNER JOIN {$wpdb->users} AS u ON u.ID = e.user_id
				INNER JOIN {$wpdb->posts} AS p ON p.ID = e.post_id
				LEFT JOIN {$wpdb->users} AS en ON en.ID = e.enrolled_by
				INNER JOIN {$wpdb->usermeta} AS um 
					ON um.user_id = u.ID 
					AND um.meta_key = '{$wpdb->prefix}capabilities'
				WHERE um.meta_value LIKE %s
				ORDER BY e.id DESC
				LIMIT %d OFFSET %d",
				'%exms_student%',
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$data_array = [];

		if ($rows) {
			foreach ($rows as $row) {
				$student_link = '<a href="' . add_query_arg('user_id', $row['user_id'], self_admin_url('user-edit.php')) . '">' . esc_html($row['student_name']) . '</a>';

				$enrolled_by_link = !empty($row['enrolled_by']) 
					? '<a href="' . add_query_arg('user_id', $row['enrolled_by'], self_admin_url('user-edit.php')) . '">' . esc_html($row['enrolled_by_name']) . '</a>' 
					: __('-', 'exms');

				$post_link = !empty($row['post_id'])
					? '<a href="' . get_edit_post_link($row['post_id']) . '">' . esc_html($row['post_name']) . '</a>'
					: __('Unknown', 'exms');

				$progress_status = intval($row['progress_percent']) >= 50 ? 'Pass' : 'Fail';
				$progress_class  = $progress_status === 'Pass' ? 'exms-pass' : 'exms-fail';

				$progress_html = '
					<div class="exms-percentage-wrapper">
						<div class="exms-percentage-header">
							<span class="exms-status-badge ' . $progress_class . '">
								' . __($progress_status, 'exms') . '
							</span>
							<span class="exms-percentage-value">' . esc_html($row['progress_percent']) . '%</span>
						</div>
						<div class="exms-percentage-bar">
							<div class="exms-percentage-fill" style="width:' . intval($row['progress_percent']) . '%"></div>
						</div>
					</div>
				';

				$start_date = !empty($row['start_date'])
					? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row['start_date']))
					: __('-', 'exms');

				$data_array[] = [
					'id' => $row['id'],
					'cb' => sprintf(
						'<input type="checkbox" class="exms-select" name="bulk-select[]" value="%s" />',
						esc_attr($row['id'])
					),
					'exms_student'             => $student_link,
					'exms_enrolled_by'         => $enrolled_by_link,
					'exms_progress_percentage' => $progress_html,
					'exms_start_date'          => esc_html($start_date),
					'exms_action'              => '<a class="exms-action-col exms-report-action" data-id="' . esc_attr($row['id']) . '">' . __('View More', 'exms') . '</a>',
				];
			}
		}

		return [
			'total' => $total_items,
			'items' => $data_array
		];

		?>
		</section>
		<?php
	}

	/**
	 * Gets a list of all, hidden and sortable columns
	 */
	public function get_hidden_columns() {
		return array();
	}

	public function get_columns() {
		
		$tab_type = isset($_GET['tab_type']) ? sanitize_text_field($_GET['tab_type']) : '';

		if($tab_type === 'exms_students_reports') {
			return $this->get_student_columns();
		} elseif ($tab_type === 'exms_quizzes_reports') {
			return $this->get_quiz_columns();
		}
		return $this->get_quiz_columns();
	}


	/**
	 * Gets a list of quiz columns.
	 *
	 * @return array
	 */
	public function get_quiz_columns() {
		$columns = [
			'cb'                   => '<input type="checkbox" id="cb-select-all" />',
			'exms_user'              => __( 'Name', 'exms' ),
			'exms_name'              => __( 'Quiz Name', 'exms' ),
			'exms_percentage'        => __( 'Percentage Status', 'exms' ),
			'exms_date'       => __( 'Attempt Date', 'exms' ),
			'exms_action'            => __( '', 'exms' ),
		];

		return $columns;
	}
	
	/**
	 * Gets a list of columns.
	 *
	 * @return array
	 */
	public function get_student_columns() {
		$columns = [
			'cb'                   => '<input type="checkbox" id="cb-select-all" />',
			'exms_student'          => __( 'Student Name', 'exms' ),
			'exms_enrolled_by'      => __( 'Total Enrolled Course', 'exms' ),
			'exms_progress_percentage'        => __( 'Completed Course', 'exms' ),
			'exms_incomplete'        => __( 'Incomplete Course', 'exms' ),
			'exms_start_date'       => __( 'Start Date', 'exms' ),
			'exms_action'            => __( '', 'exms' ),
		];

		return $columns;
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" class="exms-select" name="bulk-select[]" value="%s" />',
        esc_attr($item['id'])
		);
	}

	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'exms-select', 'manage-column', "column-$column_key" );

			echo '<th scope="col" id="' . $column_key . '" class="' . implode( ' ', $class );
			if ( 'cb' === $column_key ) {
				echo ' check-column';
			}
			echo '">';
			echo $column_display_name;
			echo '</th>';
		}
	}

	public function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "exms-select $column_name column-$column_name";
			$data    = 'data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '"';

			echo '<td class="' . $classes . '" ' . $data . '>';

			if ( $column_name === 'cb' ) {
				echo $this->column_cb( $item );
			} else {
				echo $this->column_default( $item, $column_name );
			}

			echo '</td>';
		}
	}



	/**
	 * Return column value
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			
			case 'exms_user':
			case 'exms_name':
			case 'exms_percentage':
			case 'exms_date':
			case 'exms_action':
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';

			case 'exms_student':
			case 'exms_enrolled_by':
			case 'exms_progress_percentage':
			case 'exms_start_date':
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';

			default:
				return '';
		}
	}



	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		_e( 'No Recordes found', 'exms' );
	}
}

/**
 * WP_list_table instance
 */
function exms_reports_data_table_layout() {

	$exms_reports_data_table = new EXMS_Reports_Data_Table();
	$exms_reports_data_table->prepare_items();
	$exms_reports_data_table->display();
}
exms_reports_data_table_layout();