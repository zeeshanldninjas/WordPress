<?php 
while ( $query->have_posts() ) {
	
	$query->the_post();

	$user_id = get_current_user_id();
	$course_id = get_the_id();
	$thumbnail_url = get_the_post_thumbnail_url( $course_id, 'medium' );
	$course_member_count = exms_get_course_member( $course_id ) ;
	$is_enrolled = exms_is_user_in_post( $user_id, $course_id );
	$course_type = exms_get_post_settings( $course_id );
	$course_lesson_count = count( exms_get_course_lessons( $course_id ) );

	?>
	<div class="exms-course-listing-wrapper">
		<div class="exms-course-listing-thumbnail-wrapper">
			<img src="<?php echo $thumbnail_url; ?>" class="exms-course-listing-thumbnail" onerror="this.style.display='none'">
		</div>
		<div class="rating-stars">
			<div class="filled-stars" style="width: 50%;">★★★★★</div>
		</div>
		<div class="course-title"><?php echo get_the_title(); ?></div>
		<div class="course-part-main-wrapper">
			<div class="course-part-wrapper">
				<div class="course-lesson">
					<span class="dashicons dashicons-book exms-course-lesson"></span>
					<span class="exms-middle"><?php echo $course_lesson_count . __( ' Lesson', 'exms' ); ?></span>
				</div>
				<div class="course-students">
					<span class="dashicons dashicons-admin-users exms-course-users"></span>
					<span class="exms-middle"><?php echo $course_member_count . __( ' Students', 'exms' ); ?></span>
				</div>
			</div>
			<hr>
			<div class="progress-container">
				<div class="progress-bar" style="width: 75%;"></div>
			</div>
			<div class="course-info">
				<div class="course-assign">
					<?php 
					if( $is_enrolled ) {
						echo __( 'Enrolled', 'exms' );
						?>
						<span class="dashicons dashicons-yes"></span>
						<?php
					} else {
						echo ucwords( $course_type['parent_post_type'] );
					}
					?>
				</div>
				<div class="course-status">
					<?php 
					if( $is_enrolled ) { ?>
						<button class="course-enroll-btn"><?php echo __( 'Continue', 'exms' ); ?></button>
						<?php
					} else { ?>
						<button class="course-enroll-btn"><?php echo __( 'Enroll', 'exms' ); ?></button>
					<?php }
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>