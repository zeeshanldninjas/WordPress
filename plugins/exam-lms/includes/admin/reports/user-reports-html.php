<?php
/**
 * User detailed report page content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$curr_user_by = isset( $_GET['exms_user_by'] ) ? $_GET['exms_user_by'] : false;
$curr_user = isset( $_GET['exms_report_user'] ) ? $_GET['exms_report_user'] : false;
$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
$url = admin_url( 'admin.php?page=exms_user_report' );
$user = $curr_user_by && $curr_user ? get_user_by( $curr_user_by, $curr_user ) : false;
$username = isset( $user->user_nicename ) ? $user->user_nicename : '';
$resp = ! $user && $curr_user ? 'Unable to find user <b>'.$curr_user.'</b>' : 'Search a user to see report.';
$user_last_login_date = get_user_meta( $user->ID, 'exms_last_login', true );
$last_login = $user && $user_last_login_date ? date( 'Y-m-d h:i:s', $user_last_login_date ) : false;
$last_login = $last_login ? $last_login : '-';
$enrolled_quizzes = function_exists( 'exms_get_user_enrolled_quizzes' ) ? exms_get_user_enrolled_quizzes( $user->ID ) : [];
$completed_quizzes = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user->ID ) : [];
$enrolled_count = !empty( $enrolled_quizzes ) && is_array( $enrolled_quizzes ) ? count( $enrolled_quizzes ) : 0;
$completed_count = !empty( $completed_quizzes ) && is_array( $completed_quizzes ) ? count( $completed_quizzes ) : 0;
$inprogress_count = $enrolled_count - $completed_count;
// $points = function_exists( 'exms_get_user_all_points' ) ? exms_get_user_all_points( $user->ID ) : [];
$points_html = isset( $points['html'] ) ? $points['html'] : '';
?>
<!-- User reports HTML content -->
<div class="exms-report-wrapper">
	<h2><?php _e( 'User Report', WP_EXAMS ); ?></h2>
	<form action="<?php echo $url; ?>" method="get">
		<div class="exms-user-row">
			<select name="exms_user_by">
				<option value="login"><?php _e( 'Username', WP_EXAMS ); ?></option>
				<option value="email"><?php _e( 'Email', WP_EXAMS ); ?></option>
				<option value="ID"><?php _e( 'User ID', WP_EXAMS ); ?></option>
			</select>
			<input type="text" name="exms_report_user" placeholder="<?php _e( 'Enter a username', WP_EXAMS ); ?>" value="<?php echo $username; ?>" required />
			<input type="submit" class="button button-primary" value="<?php _e( 'Get Report', WP_EXAMS ); ?>" />
		</div>
		<input type="hidden" name="page" value="<?php echo $page; ?>">
	</form>
	<div class="exms-report-res">
		<?php  
			if( ! $user ) { ?>

				<div class="exms-main-error"><?php _e( $resp, WP_EXAMS ); ?></div>
		<?php
			} else { ?>

				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Last login', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $last_login; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Enrolled Quizzes', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $enrolled_count; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'In-progress Quizzes', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $inprogress_count; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Completed Quizzes', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $completed_count; ?></div>
				</div>
				<div class="exms-row">
					<div class="exms-title"><?php _e( 'Points', WP_EXAMS ); ?></div>
					<div class="exms-data"><?php echo $points_html; ?></div>
				</div>
			<?php
				if( $enrolled_count ) {

					$labels = [ __( 'Enrolled Quizzes', WP_EXAMS ), __( 'In-progress', WP_EXAMS ), __( 'Completed', WP_EXAMS ) ];
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
if( file_exists( EXMS_DIR . 'includes/admin/reports/student-detail-data-table.php' ) ) {

    require_once EXMS_DIR . 'includes/admin/reports/student-detail-data-table.php';
}