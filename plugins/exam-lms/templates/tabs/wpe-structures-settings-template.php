<?php 

if( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wpe-tab-data-heading">
	<span class="dashicons dashicons-admin-generic wpe-icon"></span>
	<?php _e( 'Setup Course Structures', 'exms' ); ?>
</div>

<div class="wpe_settings_wrapper">

	<div class="exms-course-structures-wrap">
		
		<div class="wpe-structure-heading"><?php _e( 'Setup Course Structures', 'exms' ); ?></div>

		<!-- Course structures html content -->
		<?php echo EXMS_Setup_Functions::exms_course_structures_html( false ); ?>
		<!-- /course structures html content -->

	</div>

</div>