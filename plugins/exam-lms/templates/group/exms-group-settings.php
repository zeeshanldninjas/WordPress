<?php

/**
 * Quiz settings content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$hide_class = 'exms-hide';
$show_class = 'exms-show';

$existing_labels = Exms_Core_Functions::get_options('labels');
$group_singular = '';
if ( is_array( $existing_labels ) && array_key_exists( 'exms_qroup', $existing_labels ) ) {
    $group_singular = $existing_labels['exms_qroup'];
}

?>
<div class="exms-group-setting-tab-wrapper">
	<div class="exms-tab-button">
		<button type="button" class="exms-tab-title exms-active-tab" value="quiz-video-url"><span class="dashicons dashicons-video-alt3 exms-icon"></span><span><?php echo $group_singular . __( ' Video', 'exms' ); ?></span></button>
	</div>

	<div class="exms-tab-content">
		<div class="exms-course-video-content">
			<?php
			if ( !self::$instance->table_check ) {
            ?>
				<div class="exms-row exms-quiz-settings-row">
				<?php
				$ajax_action = 'create_exms_group_table';
				$table_names = $table_exists;
				require EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
				?>
				</div>
				<?php
			}
				?>
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php _e( ucwords( $group_singular ).' Video Link', 'exms' ); ?>
                </div>
                <div class="exms-data">
                    <input type="url"
                           class="settings_input_field exms-main-field exms-video-field"
                           name="exms_group_video_url"
                           value="<?php echo $group_video_url; ?>"
                           placeholder="<?php _e( 'Enter YouTube/Vimeo video link', 'exms' ); ?>" />
                </div>
                <p class="exms-instruction-message">
                    <?php _e( 'If you want to replace the quiz image with a video, please enter the video link here. Leave it empty if you want to display the course image instead.', 'exms' ); ?>
                </p>
            </div>
        </div>
	</div>
</div>