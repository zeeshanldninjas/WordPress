<?php 

if( ! defined( 'ABSPATH' ) ) exit; 

?>

<div class="exms-tab-data-heading"><span class="dashicons dashicons-shortcode exms-icon"></span><?php _e( 'Shortcodes', 'exms' ); ?></div>

<div class="exms_settings_wrapper">	

	<!-- Quiz -->

	<div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Quiz', 'exms' ); ?>
		</div>

		<div class="exms-data">
			<code>[exms_quiz id="1"]</code>
			<p class="exms-instruction-message"><?php _e( 'Display any quiz. Can be found on any quiz post edit page.' ); ?></p>
			<b><?php _e( 'Parameters:', 'exms' ); ?></b>
			<p><code>id</code> <?php _e( '(Required) Quiz id.', 'exms' ); ?></p>
		</div>
	</div>

	<!-- Leaderboard -->

	<div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Leaderboard', 'exms' ); ?>
		</div>

		<div class="exms-data">
			<code>[exms_leaderboard]</code>
			<p class="exms-instruction-message"><?php _e( 'Display points leaderboard.' ); ?></p>
			<b><?php _e( 'Parameters:', 'exms' ); ?></b>
			<p><code>quiz_id</code> <?php _e( '(Optional) Quiz id.', 'exms' ); ?></p>
			<p><code>from</code> <?php _e( '(Optional) Search from a specific date. e.g  2021-05-01.', 'exms' ); ?></p>
			<p><code>to</code> <?php _e( '(Optional) Search to a specific date. e.g 2021-05-10.', 'exms' ); ?></p>
		</div>
	</div>

	<!-- Students Reports -->

	<div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Students Report', 'exms' ); ?>
		</div>

		<div class="exms-data">
			<code>[exms_instructor]</code>
			<p class="exms-instruction-message"><?php _e( 'Display instructor\'s students report.' ); ?></p>
			<b><?php _e( 'Parameters:', 'exms' ); ?></b>
			<p><code>userid</code> <?php _e( '(Optional) User id. Default current logged in user\'s id.', 'exms' ); ?></p>
		</div>
	</div>

	<!-- User dashboard -->

	<div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Student Dashboard', 'exms' ); ?>
		</div>

		<div class="exms-data">
			<code>[exms_student_dashboard]</code>
			<p class="exms-instruction-message"><?php _e( 'Display dashboard for student/instructor.' ); ?></p>
			<b><?php _e( 'Parameters:', 'exms' ); ?></b>
			<p><code>userid</code> <?php _e( '(Optional) User id. Default current logged in user\'s id.', 'exms' ); ?></p>
		</div>
	</div>
	<div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Quiz Review', 'exms' ); ?>
		</div>

		<div class="exms-data">
			<code>[exms_quiz_review]</code>
			<p class="exms-instruction-message"><?php _e( 'Display All Quizes for Instructor to review.' ); ?></p>
		</div>
	</div>
</div>