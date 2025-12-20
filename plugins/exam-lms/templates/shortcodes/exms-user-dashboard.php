<?php
/**
 * Template to display [exms_student_dashboard] shortcode content
 *
 * This template can be overridden by copying it to yourtheme/wp-exams/shortcodes/exms-user-dashboard.php.
 *
 * @param $atts 	All shortcode attributes
 */
if( ! defined( 'ABSPATH' ) ) exit;

$user_id = isset( $atts['userid'] ) ? $atts['userid'] : get_current_user_id();
$user_type = isset( $atts['user_type'] ) ? $atts['user_type'] : ''; 
$active_tab = isset( $_GET['exms_active_tab'] ) ? $_GET['exms_active_tab'] : 'exms_enrolled_quizzes';
$dashboard_type = isset( $_GET['exms_db_type'] ) ? $_GET['exms_db_type'] : $user_type;
$selected_quiz = isset( $_GET['exms_sel_quiz'] ) ? $_GET['exms_sel_quiz'] : '';
$current_page_link = get_permalink();
$active_class = 'exms-active-menu-item';
$links = array( 'exms_enrolled_quizzes' => __( 'Quizzes', 'exms' ), 'exms_progress' => __( 'Progress', 'exms' ) );

if( 'student' == $user_type ) {

	$links['exms_achievements'] = __( 'Achievements', 'exms' );
}
$all_links = apply_filters( 'exms_dashboard_sidebar_links', $links );
?>
<div class="exms-std-dashboard">
	<div class="exms-row exms-dashboard-box">
		<div class="exms-title exms-left-sidebar">
	        <ul class="exms-sidebar-menu">
	        	<?php
	        		if( $all_links ) {

	        			foreach( $all_links as $tab_name => $link ) { ?>

        					<li><a href="<?php echo $current_page_link; ?>?exms_active_tab=<?php echo $tab_name; ?>&exms_db_type=<?php echo $user_type; ?>" class="<?php echo $tab_name == $active_tab ? $active_class : ''; ?>"><span class="exms-menu-text"><?php _e( $link , 'exms' ); ?></span></a></li>
	        	<?php
	        			}
	        		}
	        	?>
	        </ul>
		</div>
		<div class="exms-data">
		<?php
		/**
		 * For instructor users
		 */
			if( 'instructor' == $dashboard_type ) {

				if( 'exms_enrolled_quizzes' == $active_tab ) {

					$quizzes = exms_get_user_enrolled_quizzes( $user_id );
			?>
					<table class="exms-data-table">
						<tr>
							<th><?php _e( 'S.NO#', 'exms' ); ?></th>
							<th><?php _e( 'Quiz', 'exms' ); ?></th>
							<th><?php _e( 'Students', 'exms' ); ?></th>
							<th><?php _e( 'Progress', 'exms' ); ?></th>
							<th><?php _e( 'Action', 'exms' ); ?></th>
						</tr>
			<?php
					if( $quizzes ) { 

						foreach( $quizzes as $index => $quiz_id ) { 

							$q_title = get_the_title( $quiz_id );
							$count = $index + 1;
							$students = exms_quiz_enrolled_users_count( $quiz_id );
							$completed_students = exms_quiz_complete_users_count( $quiz_id );
			?>
							<tr>
								<td><?php echo $count; ?></td>
								<td><?php echo $q_title; ?></td>
								<td><?php echo $students; ?></td>
								<td><?php echo $completed_students; ?></td>
								<td><a href="<?php echo $current_page_link; ?>?exms_active_tab=exms_progress&exms_db_type=instructor&exms_sel_quiz=<?php echo $quiz_id; ?>"><?php _e( 'See Progress', 'exms' ); ?></a></td>
							</tr>
			<?php
						}
					} else { ?>

						<tr><td colspan="4"><?php _e( 'Not enroll in any quiz yet.', 'exms' ); ?></td></tr>
			<?php
					} ?>
					</table>
			<?php
				} elseif( 'exms_progress' == $active_tab ) { 

					$quizzes = exms_get_user_enrolled_quizzes( $user_id );
					if( $quizzes ) {
				?>
						<form action="<?php echo $current_page_link; ?>?exms_active_tab=exms_progress&exms_db_type=<?php echo $user_type; ?>">
							<input type="hidden" name="exms_active_tab" value="exms_progress" />
							<input type="hidden" name="exms_db_type" value="<?php echo $user_type; ?>" />
							<select name="exms_sel_quiz">
				<?php
						if( $quizzes ) {

							foreach( $quizzes as $quiz ) { ?>

								<option value="<?php echo $quiz; ?>"><?php echo get_the_title( $quiz ); ?></option>
				<?php
							}
						}
				?>
							</select>
							<input type="submit" value="<?php _e( 'Show Progress', 'exms' ); ?>" />
						</form>
				<?php
						$enrolled_students = exms_quiz_enrolled_users_count( $selected_quiz );
						$completed_students = exms_quiz_complete_users_count( $selected_quiz );
						$in_prog_students = $enrolled_students - $completed_students;
						$labels = [ __( 'Total Students', 'exms' ), __( 'In-progress Quizzes', 'exms' ), __( 'Completed Quizzes', 'exms' ) ];
						$values = [ $enrolled_students, $in_prog_students, $completed_students ];
						exms_create_user_quiz_progress_chart( $labels, $values );
					}else {
						echo __( 'Progress not found', 'exms' );
					}
				}
			} 
			/**
			 * For student users
			 */
			elseif( 'student' == $dashboard_type ) {

				if( 'exms_enrolled_quizzes' == $active_tab ) {

					$quizzes = exms_get_user_enrolled_quizzes( $user_id );
			?>
					<table class="exms-data-table">
						<tr>
							<th><?php _e( 'S.NO#', 'exms' ); ?></th>
							<th><?php _e( 'Quiz', 'exms' ); ?></th>
							<th><?php _e( 'Instructor(s)', 'exms' ); ?></th>
							<th><?php _e( 'Status', 'exms' ); ?></th>
							<th><?php _e( 'Enrolled On', 'exms' ); ?></th>
							<th><?php _e( 'Completed On', 'exms' ); ?></th>
						</tr>
			<?php
					if( $quizzes ) { 

						foreach( $quizzes as $index => $quiz_id ) { 

							$instructors = exms_get_quiz_instructors( $quiz_id );
							$q_title = get_the_title( $quiz_id );
							$count = $index + 1;
							$all_ins = [];
							$status = exms_get_user_quiz_status( $user_id, $quiz_id );
							$enroll_datetime = exms_get_user_quiz_enroll_date( $user_id, $quiz_id );
							$com_datetime = exms_get_user_quiz_complete_date( $user_id, $quiz_id );

							if( $instructors ) {

								foreach( $instructors as $ins ) {

									$all_ins[] = get_userdata( $ins )->user_nicename;
								}
							}
			?>
							<tr>
								<td><?php echo $count; ?></td>
								<td><?php echo $q_title; ?></td>
								<td><?php echo implode( ', ', $all_ins ); ?></td>
								<td><?php echo $status; ?></td>
								<td><?php echo $enroll_datetime; ?></td>
								<td><?php echo $com_datetime; ?></td>
							</tr>
			<?php
						}
					} else { ?>

						<tr><td colspan="4"><?php _e( 'Not enroll in any quiz yet.', 'exms' ); ?></td></tr>
			<?php
					} ?>
					</table>
			<?php
				} 

				elseif( 'exms_achievements' == $active_tab ) { 

					$user_points = exms_get_user_all_points( $user_id );
					$user_points = isset( $user_points['html'] ) ? $user_points['html'] : __( 'No point types yet.', 'exms' );
					echo $user_points;
					$user_badges = function_exists( 'exms_get_user_awarded_badges' ) ? exms_get_user_awarded_badges( $user_id ) : [];
					echo '<div class="exms-user-badges">'.$user_badges.'</div>';
				}

				elseif( 'exms_progress' == $active_tab ) { 

					$enrolled_quizzes = function_exists( 'exms_get_user_enrolled_quizzes' ) ? exms_get_user_enrolled_quizzes( $user_id ) : [];
					$completed_quizzes = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user_id ) : [];
					$enrolled_count = ! empty( $enrolled_quizzes ) ? count( $enrolled_quizzes ) : 0;
					$completed_count = ! empty( $completed_quizzes ) ? count( $completed_quizzes ) : 0; 
					
					$inprogress_count = $enrolled_count - $completed_count;
					?>
					<div class="exms-row">
						<div class="exms-title exms-50"><?php _e( 'Enrolled Quizzes', 'exms' ); ?></div>
						<div class="exms-data"><?php echo $enrolled_count; ?></div>
					</div>
					<div class="exms-row">
						<div class="exms-title exms-50"><?php _e( 'In-progress Quizzes', 'exms' ); ?></div>
						<div class="exms-data"><?php echo $inprogress_count; ?></div>
					</div>
					<div class="exms-row">
						<div class="exms-title exms-50"><?php _e( 'Completed Quizzes', 'exms' ); ?></div>
						<div class="exms-data"><?php echo $completed_count; ?></div>
					</div>
					<?php
					$labels = [ __( 'Enrolled Quizzes', 'exms' ), __( 'In-progress Quizzes', 'exms' ), __( 'Completed Quizzes', 'exms' ) ];
					$values = [ $enrolled_count, $inprogress_count, $completed_count ];
					exms_create_user_quiz_progress_chart( $labels, $values );
				}
			}
			do_action( 'exms_dashboard_data_tab' );
		?>
		</div>
	</div>
</div>