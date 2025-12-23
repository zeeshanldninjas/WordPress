<?php

/**
 * Quiz settings content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$hide_class = 'exms-hide';
$show_class = 'exms-show';

$existing_labels = Exms_Core_Functions::get_options('labels');
$quiz_singular = '';
if ( is_array( $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
    $quiz_singular = $existing_labels['exms_quizzes'];
}

?>
<div class="exms-setting-tab-wrapper">
	<div class="exms-tab-button">		
		<button type="button" class="exms-tab-title exms-active-tab" value="quiz-type"><span class="dashicons dashicons-tag exms-icon"></span><span><?php echo $quiz_singular . __( ' Type', 'exms' ); ?></span></button>
		<button type="button" class="exms-tab-title" value="quiz-setting"><span class="dashicons dashicons-admin-generic exms-icon"></span><span><?php echo $quiz_singular . __( ' Settings', 'exms' ); ?></span></button>
		<button type="button" class="exms-tab-title" value="quiz-achivement"><span class="dashicons dashicons-awards exms-icon"></span><span><?php echo $quiz_singular . __( ' Achievements', 'exms' ); ?></span></button>
		<button type="button" class="exms-tab-title" value="quiz-message"><span class="dashicons dashicons-testimonial exms-icon"></span><span><?php echo $quiz_singular . __( ' Messages', 'exms' ); ?></span></button>
		<button type="button" class="exms-tab-title" value="quiz-result"><span class="dashicons dashicons-testimonial exms-icon"></span><span><?php echo $quiz_singular . __( ' Result', 'exms' ); ?></span></button>
		<button type="button" class="exms-tab-title" value="quiz-video-url"><span class="dashicons dashicons-video-alt3 exms-icon"></span><span><?php echo $quiz_singular . __( ' Video', 'exms' ); ?></span></button>
	</div>

	<div class="exms-tab-content">
		<div class="exms-quiz-type-content">
			<?php 
			$disabled = ($stripe_on == 'off' && $paypal_on == 'off') ? 'disabled' : ''; 
			$disable = ($quiz_type == 'free' || $quiz_type == 'close') ? 'disabled' : ''; 
			?>
			<!-- Sign up fee -->
			<?php
			$settings_url = admin_url( 'admin.php?page=exms-settings&tab=payment-integration' );
			$message = ( $stripe_on == 'off' && $paypal_on == 'off' ) 
				? sprintf(
					__('No payment method is enabled. <a href="%s" target="_blank">Click here</a> to configure.', 'exms'),
					esc_url( $settings_url )
				)
				: "";
			if ( !empty( $message ) ) { ?>
				<div class="exms-payment-settings-row">
					<p class="exms-instruction-message"> 
						<?php echo $message; ?>
					</p>
				</div>
			<?php }

			if ( !self::$instance->table_check ) {
            ?>
				<div class="exms-row exms-quiz-settings-row">
				<?php
				$ajax_action = 'create_exms_quiz_table';
				$table_names = $table_exists;
				require EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
				?>
				</div>
				<?php
			}
				?>
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Seat Limit', 'exms' ); ?>
				</div>

				<div class="exms-data">  
					<input 
						type="number" min="0" 
						name="exms_quiz_seat_limit"  
						placeholder="<?php echo __( 'Seat Limit For Quiz', 'exms' ); ?>" 
						value="<?php echo esc_attr($quiz_seat_limit  ); ?>" />

					<p class="exms-instruction-message"> 
						<?php echo sprintf( __( 'Add %s seat limit.', 'exms' ), $quiz_singular ); ?>
					</p>
				</div>
			</div>

			<!-- Quiz types -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php echo $quiz_singular . __( ' Type', 'exms' ); ?>
				</div>
				<div class="exms-data">
					<div class="wpeq-quiz-type-group">
						<input type="radio" class="custom_radio exms_quiz_type" name="exms_quiz_type" id="rb1" value="free" <?php echo $quiz_type == 'free' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb1">
							<?php _e( 'Free', 'exms' ); ?>
						</label>

						<input type="radio" class="custom_radio exms_quiz_type" name="exms_quiz_type" id="rb2" value="paid" <?php echo $quiz_type == 'paid' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb2">
							<?php _e( 'Paid', 'exms' ); ?>
						</label>

						<input type="radio" class="custom_radio exms_quiz_type" name="exms_quiz_type" id="rb3" value="subscribe" <?php echo $quiz_type == 'subscribe' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb3">
							<?php _e( 'Subscribe', 'exms' ); ?>
						</label>

						<input type="radio" class="custom_radio exms_quiz_type" name="exms_quiz_type" id="rb4" value="close" <?php echo $quiz_type == 'close' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb4">
							<?php _e( 'Close', 'exms' ); ?>
						</label>
					</div>
					
					<!-- Payment fields -->
					<div class="exms-quiz-price-row <?php echo $display_price_field; ?>">
						<div class="exms-sub-title">
							<?php echo $quiz_singular . __( ' Price', 'exms' ); ?>
						</div>
						<div class="exms-quiz-price">
							<input type="number" min="0" class="wpeq-quiz-settings-inputfield exms-quiz-price-field" name="exms_quiz_price" value="<?php echo $quiz_price; ?>" placeholder="<?php _e( 'Price for quiz', 'exms' ); ?>" />
						</div>
					</div>
					<div class="exms-quiz-subs-row <?php echo $subscription_field; ?>">
						<div class="exms-sub-title">
							<?php _e( 'Subscription Days', 'exms' ); ?>
						</div>
						<div class="exms-subscription">	
							<input type="number" min="0" class="wpeq-quiz-settings-inputfield exms-subscription-field" name="exms_quiz_sub_days" value="<?php echo $quiz_subs; ?>" placeholder="<?php _e( 'Valid for X days', 'exms' ); ?>" />
						</div>
					</div>
				  <div class="exms-quiz-close-row <?php echo $quiz_close_field; ?>">
				  	<div class="exms-sub-title">
				  		<?php _e( 'Enter Redirect URL', 'exms' ); ?>
				  	</div>
				  	<div class="exms-quiz-close">	
				  		<input type="url" class="wpeq-quiz-settings-inputfield exms-quiz-close-field" name="exms_quiz_close_url" value="<?php echo $quiz_close_url; ?>" placeholder="<?php _e( 'Enter a valid URL', 'exms' ); ?>" />
					</div>
				  </div>
				<p class="exms-instruction-message"> <?php echo sprintf( __( 'Set type of %s.', 'exms' ), $quiz_singular ); ?></p>
				</div>
			</div>

		</div>

		<div class="exms-quiz-setting-content">
			
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php echo $quiz_singular . __( ' Timer', 'exms' ); ?>
				</div>

				<div class="exms-data timer-div">
					<div id="div_timing_opts">
					<div class="input-group clockpicker">
						<div class="toggle-switch wpeq-quiz-timer-switch">
							<input type="radio" class="toggle_radio exms_quiz_timer" name="exms_quiz_timer_toggle" id="rb10" value="on" <?php echo $is_quiz_timer_disabled == 'on' ? 'checked' : ''; ?> />
							<label class="toggle_label" for="rb10">
							<?php _e( 'On', 'exms' ); ?>
							</label>

							<input type="radio" class="toggle_radio exms_quiz_timer" name="exms_quiz_timer_toggle" id="rb11" value="off" <?php echo $is_quiz_timer_disabled == 'off' ? 'checked' : ''; ?> <?php echo $is_quiz_timer_disabled == '' ? 'checked' : ''; ?> />
							<label class="toggle_label" for="rb11">
								<?php _e( 'Off', 'exms' ); ?>
							</label>
						</div>
						
						<div class="exms-quiz-timer-fields" style="<?php echo $is_quiz_timer_disabled == 'on' ? '' : 'display:none;'; ?>">
							<?php
								function_exists( 'exms_display_timer_field' ) ? exms_display_timer_field( $quiz_skip_time ) : '';
							?>
						</div>

						
					</div>
					<p class="exms-instruction-message"><?php echo sprintf( __( 'Timer to end %s.', 'exms' ), $quiz_singular ); ?></p>
					</div>
				</div>
			</div>

			<!-- Show Correct Answer -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Show Answer' , 'exms' ); ?>
				</div>
				<div class="toggle-switch">
					<input type="radio" class="toggle_radio" name="exms_show_answer" id="rb6" value="on" <?php echo $show_answer == 'on' ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="rb6"><?php _e('On', 'exms'); ?></label>

					<input type="radio" class="toggle_radio" name="exms_show_answer" id="rb7" value="off" <?php echo $show_answer == 'off' ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="rb7"><?php _e('Off', 'exms'); ?></label>
				</div>
			</div>

			<!-- Shuffle Answers -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Shuffle Questions' , 'exms' ); ?>
				</div>
				<div class="toggle-switch">
				  <input type="radio" class="toggle_radio exms_show_ans" name="exms_shuffle_ques" id="rb06" value="on" <?php echo $shuffle == 'on' ? 'checked="checked"' : ''; ?> />
				  <label class='toggle_label' for="rb06">
				  	<?php _e( 'On', 'exms' ); ?>
				  </label>

				  <input type="radio" class="toggle_radio exms_show_ans" name="exms_shuffle_ques" id="rb07" value="off" <?php echo $shuffle == 'off' ? 'checked="checked"' : ''; ?> <?php echo $shuffle == '' ? 'checked="checked"' : ''; ?> />
				  <label class='toggle_label' for="rb07">
				  	<?php _e( 'Off', 'exms' ); ?>
				  </label>
				</div>
				<p class="exms-instruction-message">
					<?php echo sprintf( __( 'Shuffle %s questions.', 'exms' ), $quiz_singular ); ?>
				</p>
			</div>

			<!-- Quiz reattempts -->
			<?php 

			$quiz_attempt_html = '';
			$quiz_attempt_field = '';
			if( $quiz_reattempts == 'yes' ) {

				$quiz_attempt_html = 'exms-show';
			}elseif( $quiz_reattempts == 'no' ) {

				$quiz_attempt_field = $hide_class;
			}
			?>	

			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-data">
					<div class="exms-title">
						<?php echo $quiz_singular . __( ' Reattempts', 'exms' ); ?>
					</div>
					<div class="toggle-switch quiz_reattempts">

						<input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_quiz_reattempts_toggle" id="rb8" value="yes" <?php echo $quiz_reattempts == 'yes' ? 'checked="checked"' : ''; ?> />
						<label class='toggle_label' for="rb8">
							<?php _e( 'Yes', 'exms' ); ?>
						</label>

						<input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_quiz_reattempts_toggle" id="rb9" value="no" <?php echo $quiz_reattempts == 'no' ? 'checked="checked"' : ''; ?> />
						<label class='toggle_label' for="rb9">
							<?php _e( 'No', 'exms' ); ?>
						</label>
					</div>
					<div class="exms-reattempt-row <?php echo $quiz_attempt_html; ?>">
						<div class="exms-sub-title">
							<?php _e( 'Number of attempts', 'exms' ); ?>
						</div>
						<div class="exms-reattempt">
							<input class="exms-reattempts-number" name="exms_reattempts_numbers" type="number" placeholder="<?php echo __( 'Reattempts', 'exms' ); ?>" value="<?php echo $quiz_reattempts_no; ?>">
						</div>
					</div>

					<div class="div_reattps_opts <?php echo $quiz_attempt_html; ?>">	
						<div class="exms-sub-title" id="exms-reattempt-select">
							<?php _e( 'Set Day/time', 'exms' ); ?>
						</div>
						<select class="exms-reattempts-quiz-opts" name="exms_reattempt_type">
							<option value="select_x_options"><?php _e( 'Select Options' ); ?></option>
							<option value="x-days" <?php echo 'x-days' == $quiz_reattempts_type ? 'selected="selected"' : ''; ?> ><?php echo _e( 'X days', 'exms' ); ?></option>
							<option value="x-hours" <?php echo 'x-hours' == $quiz_reattempts_type ? 'selected="selected"' : ''; ?> ><?php echo _e( 'X hours', 'exms' ); ?></option>
							<option value="x-minutes" <?php echo 'x-minutes' == $quiz_reattempts_type ? 'selected="selected"' : ''; ?> ><?php echo _e( 'X minutes', 'exms' ); ?></option>
							<option value="x-date" <?php echo 'x-date' == $quiz_reattempts_type ? 'selected="selected"' : ''; ?> ><?php echo _e( 'X date', 'exms' ); ?></option>
						</select>
					</div>	
					<div class="exms-x-extra-field">
						<?php 
						if( $quiz_reattempts_type && $quiz_reattempts_type == 'x-days' ) {

							?>
							<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>">
								<div class="exms-sub-title">
									<?php _e( 'Number of days', 'exms' ); ?>
								</div>
								<div class="exms-reattempt-value">
									<input name="exms_reattempt_type_value" value="<?php echo $quiz_reattempts_field; ?>" type="number" placeholder="<?php _e( 'Number of days', 'exms' ); ?>" class="exms_reattempt_type_value">
								</div>
							</div>		
							<?php
							
						} else if( $quiz_reattempts_type && $quiz_reattempts_type == 'x-hours' ) {

							?>
							<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>">
								<div class="exms-sub-title">
									<?php _e( 'Number of hours', 'exms' ); ?>
								</div>
								<div class="exms-reattempt-value">
									<input name="exms_reattempt_type_value" value="<?php echo $quiz_reattempts_field; ?>" type="number" placeholder="<?php _e( 'Number of hours', 'exms' ); ?>" class="exms_reattempt_type_value">
								</div>
							</div>
							<?php
							
						} else if( $quiz_reattempts_type && $quiz_reattempts_type == 'x-minutes' ) {

							?>
							<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>">
								<div class="exms-title">
									<?php _e( 'Number of minutes', 'exms' ); ?>
								</div>
								<div class="exms-reattempt-value">
									<input name="exms_reattempt_type_value" value="<?php echo $quiz_reattempts_field; ?>" type="number" placeholder="<?php _e( 'Number of minutes', 'exms' ); ?>" class="exms_reattempt_type_value">
								</div>
							</div>
							<?php
							
						} else if( $quiz_reattempts_type && $quiz_reattempts_type == 'x-date' ) {

							?>
							<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>">
								<div class="exms-title">
									<?php _e( 'Select of date', 'exms' ); ?>
								</div>
								<div class="exms-reattempt-value">
									<input name="exms_reattempt_type_value" value="<?php echo $quiz_reattempts_field; ?>" type="date" placeholder="<?php _e( 'Number of date', 'exms' ); ?>" class="exms_reattempt_type_value">
								</div>
							</div>
							<?php
						}?>
					</div>
					<p class="exms-instruction-message">
						<?php echo sprintf( __( 'How many times user can reattempt this %s after failed once.', 'exms' ), $quiz_singular ); ?>
					</p>
				</div>
			</div>

			<!-- Passing percentage -->
			<div class="exms-row exms-display-content exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Passing Percentage', 'exms' ); ?>
				</div>

				<div class="exms-data quiz_passing_per">
				  
			  		<input type="number" class="exms_pass_per settings_input_field exms-main-field" name="exms_passing_per"  placeholder="<?php __( 'passing %', 'exms' ); ?>" <?php echo ! empty( $quiz_passing_per ) ? 'value='.$quiz_passing_per : 'value="50"'; ?> />
				  	<p class="exms-instruction-message"><?php echo sprintf( __( 'Passing percentage for this %s.', 'exms' ), $quiz_singular ); ?></p>
				</div>
			</div>

			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Show Passing Percentage' , 'exms' ); ?>
				</div>
				<div class="toggle-switch">
					<input type="radio" class="toggle_radio" name="exms_show_passing_percentage" id="rb36" value="on" <?php echo $display_passing_percentage == 'on' ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="rb36"><?php _e('On', 'exms'); ?></label>

					<input type="radio" class="toggle_radio" name="exms_show_passing_percentage" id="rb37" value="off"
    				<?php echo ($display_passing_percentage === 'off' || $display_passing_percentage === '') ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="rb37"><?php _e('Off', 'exms'); ?></label>
				</div>
				<p class="exms-instruction-message"><?php echo sprintf( __( 'Toggle on to display the %s’s passing percentage to users on the frontend.', 'exms' ), $quiz_singular ); ?></p>
			</div>

			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Question Display', 'exms' ); ?>
				</div>
				<div class="exms-data quiz_question_display">
					<select name="exms_question_display" class="exms-question-display-option">
						<option value="exms_one_at_time" <?php echo $question_display == 'exms_one_at_time' ? 'selected="selected"' : ''; ?>>
							<?php echo __( 'One question at a time', 'exms' ); ?>
						</option>
						<option value="exms_all_at_once" <?php echo $question_display == 'exms_all_at_once' ? 'selected="selected"' : ''; ?>>
							<?php echo __( 'All questions at once', 'exms' ); ?>
						</option>
					</select>
					<p class="exms-instruction-message"><?php _e( 'Select questions display option.', 'exms' ); ?></p>
				</div>
			</div>

		</div>

		<div class="exms-quiz-achievement-content">
			<!-- Award points -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title"><?php echo __( 'Points' , 'exms' ); ?></div>
				<div class="exms-data">
					<div class="wpeq-general-type-group">
				  	<input type="radio" class="custom_radio exms-quiz-points" name="exms_points_award_type" id="rb13" value="quiz" <?php echo $point_achievement_type == 'quiz' ? 'checked="checked"' : 'checked="checked"'; ?> />
				  	<label class='custom_radio_label' for="rb13">
				  	<?php echo $quiz_singular . __( ' Point', 'exms' ); ?>
				  	</label>

				  	<input type="radio" class="custom_radio exms-quiz-points" name="exms_points_award_type" id="rb14" value="question" <?php echo $point_achievement_type == 'question' ? 'checked="checked"' : ''; ?> />
				  	<label class='custom_radio_label' for="rb14">
				  	<?php _e( 'Question Point', 'exms' ); ?>
				  	</label>
					</div>
					<?php if( $point_achievement_type == 'question' ) {
				 		$h_q_point_row = $hide_class;
					}?>
				  	<div class="exms-quiz-points-row <?php echo $h_q_point_row; ?>">
				  		<div class="exms-sub-title">
				  		<?php echo $quiz_singular . __( ' Point', 'exms' ); ?>
						<p class="exms-instruction-message"><?php echo sprintf( __( 'Award points for %s and questions.', 'exms' ), $quiz_singular ); ?></p>
				  		</div>
				  		<div class="exms-quiz-point">
					  		<input type="number" class="wpeq-quiz-settings-inputfield exms_quiz_points" name="exms_quiz_points" value="<?php echo $achievement_point; ?>" placeholder="<?php _e( 'Point for quiz', 'exms' ); ?>" />
						</div>
				  	</div>
				  	<?php 
					if( $achievement_point_type && $achievement_point_type != 'select_point_type' ) {
						$hide_quiz_fields = $hide_class;
					}?>
				  	<div class="exms-quiz-points-rows <?php echo $hide_quiz_fields; ?>">
						<?php //WP_EXAMS_Point_Type::exms_get_all_point_type( 'quiz', 'h' ); ?>
					</div>
					<?php 
					if( $achievement_point_type && $achievement_point_type != 'select_point_type' ) {
						$show_question_fields = $show_class;
					}?>
					<div class="exms-question-points-row <?php echo $show_question_fields; ?>">
						<?php //WP_EXAMS_Point_Type::exms_get_all_point_type( 'question', 'h' ); ?>
					</div>
				</div>
			</div>

			<!-- Deduct points field -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Deduct Points Type' , 'exms' ); ?>
				</div>
				<p class="exms-instruction-message"><?php _e( 'Displayed points deduct options.', 'exms' ); ?></p>
				<div class="toggle-switch">
				  <input type="radio" class="toggle_radio exms_deduct_points" name="exms_points_deduct_type" id="rb19" value="yes" <?php echo $deduct_point_type == 'yes' ? 'checked="checked"' : ''; ?> />
				  <label class='toggle_label' for="rb19">
				  	<?php _e( 'Yes', 'exms' ); ?>
				  </label>

				  <input type="radio" class="toggle_radio exms_deduct_points" name="exms_points_deduct_type" id="rb20" value="no" <?php echo $deduct_point_type == 'yes' ? '' : 'checked="checked"'; ?> />
				  <label class='toggle_label' for="rb20">
				  	<?php _e( 'No', 'exms' ); ?>
				  </label>
				</div>
			</div>

			<?php 

			$deduct_show = '';
			$checked = '';
			$hide = '';
			if( $deduct_point_type == 'yes' ) {

				$deduct_show = $show_class;
			
			} else {

				$checked = 'checked="checked"';
				$hide = 'exms-hide';
			}
			?>

			<!-- Deduct points on quiz fail -->
			<div class="exms-row exms-quiz-settings-row p-deduct-on-failing <?php echo $deduct_show; ?>">
				<div class="exms-title">
					<?php _e( 'Deduct Points on fail' , 'exms' ); ?>
				</div>
				<div class="exms-data">
					<div class="wpeq-general-type-group">
						<input type="radio" class="custom_radio exms-point-deduct-failing" name="exms_deduct_point_on_failing" id="rb15" value="on" <?php echo $deduct_point_on_fail == 'on' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb15">
							<?php _e( 'On', 'exms' ); ?>	
						</label>
						<input type="radio" class="custom_radio exms-point-deduct-failing" name="exms_deduct_point_on_failing" id="rb16" value="off" <?php echo $deduct_point_on_fail == 'on' ? $checked : 'checked="checked"'; ?> />
						<label class='custom_radio_label' for="rb16">
							<?php _e( 'Off', 'exms' ); ?>
						</label>
					</div>
					<?php if( $deduct_point_on_fail == 'on' ) {

					  	$failing_row_show = $show_class;
					}?>
					<div class="exms-quiz-points-deduct-row <?php echo $hide; ?> <?php echo $failing_row_show; ?>">
					  	<?php //WP_EXAMS_Point_Type::exms_get_all_point_type( 'deduct', '$options' ); ?>
					</div>
					<div class="exms-quiz-points-deduct-row <?php echo $hide; ?> <?php echo $failing_row_show; ?>">
					  	<div class="exms-sub-title">
					  		<?php _e( 'Points', 'exms' ); ?>
					  	</div>
					  	<div class="exms-fail-deduct">
						  	<input type="number" class="wpeq-quiz-settings-inputfield exms_points_duducts_f" name="exms_deduct_failing_points" value="<?php echo $deduct_fail_point; ?>" placeholder="<?php _e( 'Point for deduct', 'exms' ); ?>" />
						</div>
					</div>
					<p class="exms-instruction-message"> <?php echo sprintf( __( 'Points deduct for failing %s.', 'exms' ), $quiz_singular ); ?></p>
				 </div>
			</div>

			<!-- Deduct point on wrong answer -->
			<div class="exms-row exms-quiz-settings-row p-deduct-on-wrg-answer <?php echo $deduct_show; ?>">
				<div class="exms-title">
					<?php _e( 'Deduct point on wrong answer' , 'exms' ); ?>
				</div>

				<div class="exms-data">
					<div class="wpeq-general-type-group">
						<input type="radio" class="custom_radio exms-point-deduct-wrong-answer" name="exms_deduct_points_wrong_answer" id="rb17" value="on" <?php echo $deduct_point_wrong_answer == 'on' ? 'checked="checked"' : ''; ?> />
						<label class='custom_radio_label' for="rb17">
						<?php _e( 'On', 'exms' ); ?>
						</label>
						<?php if( $deduct_point_wrong_answer == 'on' ) {
							$failing_wrong_row_show = $show_class;
						}?>
						<input type="radio" class="custom_radio exms-point-deduct-wrong-answer" name="exms_deduct_points_wrong_answer" id="rb18" value="off" <?php echo $deduct_point_wrong_answer == 'on' ? $checked : 'checked="checked"'; ?> />
						<label class='custom_radio_label' for="rb18">
						<?php _e( 'Off', 'exms' ); ?>
						</label>
					</div>
				  	<div class="exms-quiz-points-wrong-answer-row <?php echo $hide; ?> <?php echo $failing_wrong_row_show; ?>" >
					  	<?php //WP_EXAMS_Point_Type::exms_get_all_point_type( 'wrong_answer', '$options' ); ?>
					</div>
					<div class="exms-quiz-points-wrong-answer-row <?php echo $hide; ?> <?php echo $failing_wrong_row_show; ?>">
					  	<div class="exms-sub-title">
					  		<?php _e( 'Points', 'exms' ); ?>
					  	</div>
					  	<div class="exms-wrong-deducts">
						  	<input type="number" class="wpeq-quiz-settings-inputfield exms_points_duducts_wrng" name="exms_wrong_answer_deduct_point" value="<?php echo $deduct_wrong_point; ?>" placeholder="<?php _e( 'Point for deduct', 'exms' ); ?>" />
						</div>
					</div>

				  	<p class="exms-instruction-message"> <?php _e( 'Point deduct for submitting the wrong answer.', 'exms' ); ?></p>
				</div>
			</div>

			<?php 
			/**
			 * Assign badges into quiz
			 */
			//wp_exams()->selector->exms_create_multiple_tags( 'exms_badges', $post->ID, 'exms_attached_badges', 'badges' );
			?>
		</div>

		<div class="exms-quiz-message-content">
			<!-- display tags -->
			<div class="exms-quiz-wrap">
				<span class="exms-tags-title"><?php _e( 'Available Tags :', 'exms' ); ?></span>
				<div class="exms-available-tags">
					<ul>
						<li><code>{quiz_name}</code><span><?php _e( ' Display the Quiz name.', 'exms' ); ?></span></li>
						<li><code>{course_name}</code><span><?php _e( ' Display the Course Name.', 'exms' ); ?></span></li>
						<li><code>{result}</code><span><?php _e( ' Display the result.', 'exms' ); ?></span></li>
						<li><code>{score}</code><span><?php _e( ' Display the score.', 'exms' ); ?></span></li>
						<li><code>{percentage}</code><span><?php _e( ' Display the percentage.', 'exms' ); ?></span></li>
						<li><code>{correct_answers}</code><span><?php _e( ' Display the correct answer.', 'exms' ); ?></span></li>
						<li><code>{wrong_answers}</code><span><?php _e( ' Display the wrong answer.', 'exms' ); ?></span></li>
						<li><code>{pending_review}</code><span><?php _e( ' Display the pending review.', 'exms' ); ?></span></li>
						<li><code>{user_name}</code><span><?php _e( ' Display the user name', 'exms' ); ?></span></li>
						<li><code>{instructor_name}</code><span><?php _e( ' Display the instructor name.', 'exms' ); ?></span></li>
						<li><code>{required_percentage}</code><span><?php _e( ' Display required percentage.', 'exms' ); ?></span></li>
					</ul>
				</div>
			</div>
			<!-- end display tags -->

			<!-- Message for pass quiz -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php echo __( 'Message on passing a ', 'exms' ) . $quiz_singular; ?>
				</div>

				<div class="exms-data">
				  	<?php wp_editor( $pass_msg, 'exms_message_for_passing_quiz', [ 'textarea_rows' => 4 ] ); ?>
				</div>
			</div>
			<!-- end pass message div -->

			<!-- Message for quiz fail -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php echo __( 'Message on failing a ', 'exms' ) . $quiz_singular; ?>
				</div>

				<div class="exms-data">
				  	<?php wp_editor( $fail_msg, 'exms_message_for_failing_quiz', [ 'textarea_rows' => 4 ] ); ?>
				</div>
			</div>
			<!-- end fail message div-->

			<!-- Message for quiz pending -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php echo __( 'Message on pending a ', 'exms' ) . $quiz_singular; ?>
				</div>
				<div class="exms-data">
				  	<?php wp_editor( $pending_msg, 'exms_message_for_pending_quiz', [ 'textarea_rows' => 4 ] ); ?>
				</div>
			</div>
			<!-- end pending message div-->
		</div>
		<div class="exms-quiz-result-content">
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Question Results', 'exms' ); ?>
				</div>

				<div class="exms-data"> 
					<select class="exms-question-result-summary" name="exms_question_result_summary" <?php echo ($question_display == 'exms_all_at_once') ? 'disabled' : ''; ?>>
						<option value="summary_at_end" <?php echo ($question_display == 'exms_all_at_once') ? 'selected="selected"' : ''; ?>><?php _e( 'Summary At End' ); ?></option>
						<option value="result_after_each_question" <?php echo 'result_after_each_question' == $exms_question_result_summary ? 'selected="selected"' : ''; ?> ><?php echo _e( 'Result after each question', 'exms' ); ?></option>
					</select>
					<p class="exms-instruction-message exms-disbaled-message"><?php _e( 'Disabled due to "All Questions at Once" is selected', 'exms' ); ?></p>
				</div>
			</div>
			<!-- end pass message div -->

			<!-- Message for quiz fail -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Question/Answer Summary', 'exms' ); ?>
				</div>
				<div class="toggle-switch">
					<input type="radio" class="toggle_radio" name="exms_question_answer_summary" id="summary_yes" value="yes" <?php echo $exms_question_answer_summary == 'yes' ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="summary_yes"><?php _e('Yes', 'exms'); ?></label>

					<input type="radio" class="toggle_radio" name="exms_question_answer_summary" id="summary_no" value="no" <?php echo $exms_question_answer_summary == 'no' || empty($exms_question_answer_summary) ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="summary_no"><?php _e('No', 'exms'); ?></label>
				</div>

			</div>
			<!-- Message for quiz fail -->
			<div class="exms-row exms-quiz-settings-row">
				<div class="exms-title">
					<?php _e( 'Correct/Incorrect Status', 'exms' ); ?>
				</div>
				<div class="toggle-switch">
					<input type="radio" class="toggle_radio" name="exms_question_correct_incorrect" id="correct_incorrect_yes" value="yes" <?php echo $exms_question_correct_incorrect == 'yes' ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="correct_incorrect_yes"><?php _e('Yes', 'exms'); ?></label>

					<input type="radio" class="toggle_radio" name="exms_question_correct_incorrect" id="correct_incorrect_no" value="no" <?php echo $exms_question_correct_incorrect == 'no' || empty($exms_question_correct_incorrect) ? 'checked="checked"' : ''; ?> />
					<label class="toggle_label" for="correct_incorrect_no"><?php _e('No', 'exms'); ?></label>
				</div>

			</div>
			<!-- end fail message div-->
		</div>
		<div class="exms-course-video-content">
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php _e( ucwords( $quiz_singular ).' Video Link', 'exms' ); ?>
                </div>
                <div class="exms-data">
                    <?php
                        $video_url = isset( $data->video_url ) ? esc_url( $data->video_url ) : '';
                    ?>
                    <input type="url"
                           class="settings_input_field exms-main-field exms-video-field"
                           name="exms_quiz_video_url"
                           value="<?php echo $quiz_video_url; ?>"
                           placeholder="<?php _e( 'Enter YouTube/Vimeo video link', 'exms' ); ?>" />
                </div>
                <p class="exms-instruction-message">
                    <?php _e( 'If you want to replace the quiz image with a video, please enter the video link here. Leave it empty if you want to display the course image instead.', 'exms' ); ?>
                </p>
            </div>
        </div>
	</div>
</div>