<?php
/**
 * WP EXAMS - Question option metabox content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$existing_labels = Exms_Core_Functions::get_options('labels');
$question_singular = '';
$quiz_singular = '';
if ( is_array( $existing_labels ) && array_key_exists( 'exms_questions', $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
    $question_singular = $existing_labels['exms_questions'];
    $quiz_singular = $existing_labels['exms_quizzes'];
}

?>
<!-- Question Options HTML -->
<div class="wpeq-question-options-wrapper">
<div class="exms-quiz-type-content">
	    <?php
    if ( !self::$instance->table_check ) {
    ?>
        <?php
        $ajax_action = 'create_exms_question_table';
        $table_names = $table_exists;
        require EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
        ?>
        <?php
    }
        ?>

	<!-- Points option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php echo __( 'Points for ', 'exms' ). $question_singular; ?>
		</div>
		<p class="exms-instruction-message"><?php _e( 'Points for choosing the correct answer.', 'exms' ); ?> </p>
		<div class="exms-data">
			<input type="number" name="exms_points" placeholder="<?php echo __( 'points for ', 'exms' ) . $question_singular; ?>" value="<?php echo $ques_points; ?>" class="exms-question-point"/>
		</div>
	</div>
	
	<!-- Hint option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php echo __( 'Hint for ', 'exms' ) . $question_singular; ?>
		</div>
		<div class="exms-data">
			<?php wp_editor( $ques_hint, 'exms_hint', [ 'textarea_rows' => 5 ] ); ?>
		</div>
	</div>

	<!-- Correct answer option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php _e( 'Message for correct answer', 'exms' ); ?>
		</div>
		<div class="exms-data">
			<?php wp_editor( $corr_ans_msg, 'exms_corr_ans_msg', [ 'textarea_rows' => 5 ] ); ?>
		</div>
	</div>

	<!-- Incorrect answer option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php _e( 'Message for incorrect answer', 'exms' ); ?>
		</div>
		<div class="exms-data">
			<?php wp_editor( $incorr_ans_msg, 'exms_incorr_ans_msg', [ 'textarea_rows' => 5 ] ); ?>
		</div>
	</div>
	<!-- /incorrect answer option -->

	<!-- Shuffle answers option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php _e( 'Shuffle answers', 'exms' ); ?>
		</div>
		<p class="exms-instruction-message"><?php echo sprintf( __( 'Shuffle %s %s.', 'exms' ), $quiz_singular, $question_singular ); ?> </p>
		<div class="toggle-switch shuffle-toggle-switch">
			<input type="radio" class="toggle_radio" name="exms_shuffle" id="exms_shuffle_on" value="on" <?php echo $shuffle_ans_on; ?> />
			<label class="toggle_label" for="exms_shuffle_on">
				<?php _e( 'On', 'exms' ); ?>
			</label>

			<input type="radio" class="toggle_radio" name="exms_shuffle" id="exms_shuffle_off" value="off" <?php echo $shuffle_ans_off; ?> />
			<label class="toggle_label" for="exms_shuffle_off">
				<?php _e( 'Off', 'exms' ); ?>
			</label>
		</div>
	</div>

	<!-- Question timer option -->
	<div class="exms-row exms-question-settings-row">
		<div class="exms-title">
			<?php _e( 'Timer', 'exms' ); ?>
		</div>
		<p class="exms-instruction-message"><?php echo sprintf( __( 'Set the expected time duration for this %s.', 'exms' ), $question_singular ); ?> </p>
		<div class="exms-data">
			<?php function_exists( 'exms_display_timer_field' ) ? exms_display_timer_field( $ques_timer ) : ''; ?>
		</div>
	</div>
	</div>
</div>