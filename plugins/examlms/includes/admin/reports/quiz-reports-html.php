<?php 
/**
 * Quiz detail report page content 
 */
if( ! defined( 'ABSPATH' ) ) exit;

$curr_quiz_by = isset( $_GET['exms_quiz_by'] ) ? $_GET['exms_quiz_by'] : false;
$curr_quiz = isset( $_GET['exms_report_quiz'] ) ? $_GET['exms_report_quiz'] : '';
$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
$url = admin_url( 'admin.php?page=exms_quiz_report' ); 
$quiz = $curr_quiz_by && $curr_quiz ? $curr_quiz_by && $curr_quiz : false;
$resp = ! $quiz && $curr_quiz ? 'Unable to find quiz <b>'.$curr_quiz.'</b>' : 'Search a quiz to see report.';
$quizzes = exms_get_page_by_title( $curr_quiz, '', 'exms_quizzes' );
$quiz_id = 'quiz_name' == $curr_quiz_by ? $quizzes->ID : (int) $curr_quiz;
$enrolled_count = function_exists( 'exms_quiz_enrolled_users_count' ) ? exms_quiz_enrolled_users_count( $quiz_id ) : '';
$completed_count = function_exists( 'exms_quiz_complete_users_count' ) ? exms_quiz_complete_users_count( $quiz_id ) : '';
$inprogress_count = $enrolled_count - $completed_count;
?>
<!-- Quiz reports HTML content -->
<div class="exms-report-wrapper">
	<h2><?php _e( 'Quiz Report', WP_EXAMS ); ?></h2>
	<form action="<?php echo $url; ?>" method="get">
		<div class="exms-user-row">
			<select name="exms_quiz_by">
				<option value="quiz_name" <?php echo $curr_quiz_by == 'quiz_name' ? 'selected="selected"' : ''; ?> ><?php _e( 'Quizname', WP_EXAMS ); ?></option>
				<option value="quiz_id" <?php echo $curr_quiz_by == 'quiz_id' ? 'selected="selected"' : ''; ?> ><?php _e( 'Quiz ID', WP_EXAMS ); ?></option>
			</select>
			<input value="<?php echo $curr_quiz; ?>" type="text" name="exms_report_quiz" placeholder="<?php _e( 'Enter a quizname', WP_EXAMS ); ?>" value="<?php echo $username; ?>" required />
			<input type="submit" class="button button-primary" value="<?php _e( 'Get Report', WP_EXAMS ); ?>" />
		</div>
		<input type="hidden" name="page" value="<?php echo $page; ?>">
	</form>
	<div class="exms-report-res">
		<?php  
			if( ! $quiz ) { ?>

				<div class="exms-main-error"><?php _e( $resp, WP_EXAMS ); ?></div>
		<?php
			} else { ?>

				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Enrolled Users', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $enrolled_count; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'In-Progress', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $inprogress_count; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Completed', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $completed_count; ?></div>
				</div>
			<?php
				if( $enrolled_count ) {

					$labels = [ __( 'Enrolled Users', WP_EXAMS ), __( 'Users progress', WP_EXAMS ), __( 'Completed', WP_EXAMS ) ];
					$values = [ $enrolled_count, $inprogress_count, $completed_count ];
					exms_create_user_quiz_progress_chart( $labels, $values );
					
				} else { ?>

					<div class="exms-main-error"><?php _e( 'No enrolled quizzes yet.', WP_EXAMS ); ?></div>
		<?php
				}
			}
		?>
	</div>
</div>
<?php
/**
 * Add quiz detail data table
 */
if( file_exists( EXMS_DIR . 'includes/admin/reports/quiz-detail-data-table.php' ) ) {

    require_once EXMS_DIR . 'includes/admin/reports/quiz-detail-data-table.php';
}