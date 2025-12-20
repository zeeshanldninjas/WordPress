<?php 

if( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="exms-tab-data-heading">
	<span class="dashicons dashicons-admin-generic exms-icon"></span>
	<?php _e( 'Setup Course Structures', 'exms' ); ?>
</div>

<div class="exms_settings_wrapper">

	<div class="exms-course-structures-wrap">
		
		<div class="exms-structure-heading"><?php _e( 'Setup Course Structures', WP_EXAMS ); ?></div>

		<!-- Course structures html content -->
		<?php echo EXMS_Setup_Functions::exms_course_structures_html( false ); ?>
		<!-- /course structures html content -->

	</div>

</div>