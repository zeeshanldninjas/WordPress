<?php
/**
 * Template to display [exms_quiz] shortcode content
 *
 * This template can be overridden by copying it to yourtheme/wp-exams/shortcodes/exms-quiz-shortcode.php.
 *
 * @param $atts 	All shortcode attributes
 */

if( ! defined( 'ABSPATH' ) ) exit;

$quiz_id = isset( $atts['id'] ) ?  intval( $atts['id'] ) : '' ; 
$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : get_current_user_id();
$post_type = get_post_type( $quiz_id );

//echo exms_is_user_in_post( $user_id, $quiz_id );
$parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type() ;
$is_attached_with_parent = exms_is_quiz_in_parent_post( EXMS_PR_Fn::exms_get_parent_post_type(), $quiz_id ) ;
$parent_id = exms_get_parent_structure_id( EXMS_PR_Fn::exms_get_parent_post_type(), $quiz_id ) ;
$is_enrolled_in_parent = exms_get_user_enrolled_posts( $user_id, $parent_id );

$quiz_settings = exms_get_post_options( $quiz_id );

if( $is_attached_with_parent || empty( $parent_post_type ) || is_admin() ) { 

	if( ! $is_enrolled_in_parent && ! empty( $parent_post_type && is_admin() ) ) {
		?>
			<div class="exms-quiz-box-not-allowed">
				<?php echo sprintf(__( 'You are not enrolled in the parent course. Please, click <a href="%s">here</a> to enroll in the course first.' ), get_permalink($parent_id) ); ?>
			</div>
		<?php

		return;
	}

	$questions = exms_get_questions_for_a_quiz( $quiz_id, 0, 'publish' ); 
	$exms_question_result_summary = isset( $quiz_settings['exms_question_result_summary'] ) ? $quiz_settings['exms_question_result_summary'] : 'summary_at_end';
	$exms_question_answer_summary = isset( $quiz_settings['exms_question_answer_summary'] ) ? $quiz_settings['exms_question_answer_summary'] : 'no';
	$exms_question_correct_incorrect = isset( $quiz_settings['exms_question_correct_incorrect'] ) ? $quiz_settings['exms_question_correct_incorrect'] : 'no';

	$ques_class = 'exms-active-ques';
	$quiz_shuffle = isset( $quiz_settings['exms_shuffle_ques'] ) ? $quiz_settings['exms_shuffle_ques'] : false ; 
	$quiz_timer = isset( $quiz_settings['exms_timer'] ) ? $quiz_settings['exms_timer'] : 0;
	$quiz_timer_toggle = isset( $quiz_settings['exms_quiz_timer_toggle'] ) && 'on' == $quiz_settings['exms_quiz_timer_toggle'] ? $quiz_settings['exms_quiz_timer_toggle'] : false;
	$data_max = is_array( $questions ) ? count( $questions ) : 0;
	$sub_btn_class = $data_max < 2 ? 'exms-finish-quiz' : '';
	$quiz_type = isset( $quiz_settings['exms_quiz_type'] ) ? $quiz_settings['exms_quiz_type'] : 'free';
	$quiz_price = isset( $quiz_settings['exms_quiz_price'] ) ? $quiz_settings['exms_quiz_price'] : 0;
	$signup_fee = isset( $quiz_settings['exms_quiz_sign_up'] ) ? intval( $quiz_settings['exms_quiz_sign_up'] ) : 0;
	$question_display = isset( $quiz_settings['exms_question_display'] ) ? $quiz_settings['exms_question_display'] : 'all';
	$show_answer = isset( $quiz_settings['exms_show_answer'] ) ? $quiz_settings['exms_show_answer'] : 'no';

	if( ! empty( $signup_fee ) ) {
		$quiz_price = floatval($quiz_price) + floatval($signup_fee);
	}

	$enrolled_quizzes = exms_get_user_enrolled_quizzes( $user_id );
	$quiz_option = get_post_meta( $quiz_id, 'exms_quizzes_opts', true );
	$exms_options = get_option( 'exms_settings' );
	$complete_url = isset( $exms_options['paypal_redirect_url']['complete_url'] ) ? $exms_options['paypal_redirect_url']['complete_url'] : '';
	$cancel_url = isset( $exms_options['paypal_redirect_url']['cancel_url'] ) ? $exms_options['paypal_redirect_url']['cancel_url'] : '';
	$paypal_currency = isset( $exms_options['paypal_currency'] ) ? $exms_options['paypal_currency'] : '';
	$paypal_payee_email = isset( $exms_options['paypal_vender_email'] ) ? $exms_options['paypal_vender_email'] : '';
	$paypal_client_secret = isset( $exms_options['paypal_client_secret'] ) ? $exms_options['paypal_client_secret'] : '';
	$paypal_client_id = isset( $exms_options['paypal_client_id'] ) ? $exms_options['paypal_client_id'] : '';
	$get_quiz_users = exms_get_post_user_ids( $quiz_id );
	$quiz_sub_days = isset( $quiz_option['exms_quiz_sub_days'] ) ? $quiz_option['exms_quiz_sub_days'] : '';
	$quiz_close_url = isset( $quiz_option['exms_quiz_close_url'] ) ? $quiz_option['exms_quiz_close_url'] : '';

	/**
	 * Check quiz reattempts 
	 */
	$can_user_attempt = exms_can_user_attempt_quiz( $user_id, $quiz_id );

	if( isset( $can_user_attempt['result'] ) && ! $can_user_attempt['result'] ) {

		$resp = isset( $can_user_attempt['response'] ) ? $can_user_attempt['response'] : '';
		_e( $resp, WP_EXAMS );
		return false;
	}

	/**
	 * Shuffle all question 
	 */

	if( 'on' == $quiz_shuffle ) {
		shuffle( $questions );
	}

	$rand_nums = rand( 0, 10000000 );
	?>
	<div class="exms-quiz-box exms-quiz-box-<?php echo $rand_nums; ?>" data-max="<?php echo $data_max; ?>">
		<img class="exms-quiz-loader" src="<?php echo EXMS_ASSETS_URL.'imgs/spinner.gif'; ?>">
	<?php

		if( $quiz_timer_toggle ) {
	?>
		<div class="exms-timer-main">
			<div class="exms-timer-cover">
				<span class="exms-timer exms-timer-<?php echo $rand_nums; ?>" data-time="<?php echo $quiz_timer; ?>" data-id="<?php echo $rand_nums; ?>"></span>
				<span class="exms-timer-text exms-timer-text-<?php echo $rand_nums; ?>"><?php _e( 'Loading Timer...' ); ?></span>
			</div>
		</div>
	<?php 
		}
		if( $questions ) {
			
			foreach( $questions as $q_index => $question_id ) {

				$ques = get_post( $question_id );
				$opts = exms_get_question_options( $question_id );
				$timer = isset( $opts['exms_timer'] ) ? $opts['exms_timer'] : '';
				$type = exms_get_question_type( $question_id, false );
				$question = $ques->post_content;
				$answers = exms_get_question_answers( $question_id );
				// echo '<pre>';
				// print_r($answers);
				// //print_r($questions);
				// echo '</pre>';
				$answer_hint = isset( $opts['exms_hint'] ) ? $opts['exms_hint'] : '';

				if( 'fill_blank' == $type ) {
					
					$matches = exms_get_question_all_blanks( $question_id );

					if( $matches ) {

						foreach( $matches as $match ) {
							
							foreach( $match as $blank ) {

								$question = str_replace( $blank, '<input type="text" class="exms-ques-blanks" />', $question );
							}
						}
					}
				}
			?>
				<div class="exms-question-box exms-question-box-<?php echo $question_id; ?> <?php echo $ques_class; ?> exms-ques-<?php echo ( $q_index + 1 ); ?>" data-index="<?php echo ( $q_index + 1 ); ?>" data-id="<?php echo $question_id; ?>" data-qid="<?php echo $quiz_id; ?>" data-type="<?php echo $type; ?>">
				
			<?php
					if( $timer && ! $quiz_timer_toggle ) {
			?>
						<div class="exms-timer-main">
							<div class="exms-timer-cover">
								<span class="exms-timer exms-timer-<?php echo  $question_id; ?>" data-time="<?php echo $timer; ?>" data-id="<?php echo  $question_id; ?>"></span>
								<span class="exms-timer-text exms-timer-text-<?php echo  $question_id; ?>"><?php _e( 'Loading Timer...' ); ?></span>
							</div>
						</div>
			<?php
					}
			?>
			<?php 
					if( ! empty( $answer_hint ) ) {

			?>		<div class="exms-hint-main">
						<img title="<?php _e( 'Hint', WP_EXAMS ); ?>" class="exms-answer-hint" src="<?php echo EXMS_ASSETS_URL.'imgs/exms-hint-img.png'; ?>">
						<span class="exms-hint"><?php echo $answer_hint; ?></span>
					</div>
			<?php
					}
			?>
					<div class="exms-question-row"><?php _e( $question, WP_EXAMS ) ?></div>
			<?php
					/**
					 * Single/Multi choice answers
					 */
					if( $answers && ( 'single_choice' == $type || 'multiple_choice' == $type || 'sorting_choice' == $type ) ) {

						$type == 'sorting_choice' ? shuffle( $answers ) : $answers;
						foreach( $answers as $index => $ans ) { 

							$ans_txt = isset( $ans['answer'] ) ? $ans['answer'] : '';
							$inp_type = ''; 
							$sorting_draggable = 'sorting_choice' == $type ? 'exms-sorting-ans exms-sorting-ans-droppable' : '';

							if( 'single_choice' == $type ) {

								$inp_type = '<input type="radio" name="exms_answers" class="exms-answer-sel" value="'.$ans_txt.'" data-id="'.$index.'" />';

							} elseif( 'multiple_choice' == $type ) {

								$inp_type = '<input type="checkbox" name="exms_answers" class="exms-answer-sel" value="'.$ans_txt.'" data-id="'.$index.'" />';

							} elseif( 'sorting_choice' == $type ) {

								$inp_type = '<input type="hidden" name="exms_answers" class="exms-answer-sel" value="'.$ans_txt.'" data-id="'.$index.'" />';
							}
				?>

							<div class="exms-answer-row <?php echo $sorting_draggable; ?>">
								<?php echo $inp_type; ?>
								<?php _e( $ans_txt, WP_EXAMS ) ?>
							</div>
				<?php
						}
					} 

					/**
					 * Matrix sorting answers
					 */
					elseif( $answers && 'matrix_sorting' == $type ) {

						$table_rows = '';
						$matrix_boxes = '';
						$add_to_table = true;

						foreach( $answers as $index => $ans ) {

							foreach( $ans['answer'] as $matrix_ans ) {
								
								if( $add_to_table ) {

									$table_rows .= '<tr><th class="exms-matrix-fcol">'.$matrix_ans.'</th><th class="exms-matrix-tcol"></th></tr>';
								} else {

									$matrix_boxes .= '<div class="exms-answer-row exms-matrix-ans">'.__( $matrix_ans, WP_EXAMS ).'</div>';
								}
								$add_to_table = $add_to_table ? false : true;
							}
						}
				?>	
						<table class="exms-matrix-table"><?php echo $table_rows; ?></table>
						<div class="exms-matrix-boxes-row"><?php echo $matrix_boxes; ?></div>
				<?php
					} 

					/**
					 * File upload
					 */
					elseif( 'file_upload' == $type ) {

						exms_create_upload_form( $type, $question_id, $quiz_id );
					} 

					/**
					 * Range answers
					 */
					elseif( $answers && 'range' == $type ) {
						
						$rand = rand( 0, 10000000 );
					?>	
						<p>
						<input type="text" id="exms-range-number" readonly>
						</p>
						<div data-min-value="<?php echo $answers['min']; ?>" data-max-value="<?php echo $answers['max']; ?>" id="exms-slider-range"></div>
					<?php
					}

					/**
					 * Range answers
					 */
					elseif( 'free_choice' == $type ) {
					?>
						<textarea rows="5" cols="50" class="exms-fc-textarea"></textarea>
					<?php
					} elseif( 'essay' == $type ) {

						exms_create_upload_form( $type, $question_id, $quiz_id );
					}

					if( $exms_question_result_summary == 'result_after_each_question' ) {
						echo '<div class="exms-question-answer-submit"></div>';
					}
				?>
				</div>
			<?php
				$ques_class = 'exms-hide';
			}
	?>	
		<div class="exms-sub-ques-resp"></div>
		<div class="exms-show-correct-answer"></div>
		<div class="exms-quiz-message"></div>
		<div class="exms-quiz-badges"></div>
		<div class="exms-quiz-status-correct"><?php _e( 'Correct', WP_EXAMS ); ?></div>
		<div class="exms-quiz-status-incorrect"><?php _e( 'Incorrect', WP_EXAMS ); ?></div>
		<button class="button button-primary exms-submit-answer <?php echo $sub_btn_class; ?> exms-sub-btn-<?php echo $rand_nums; ?>" data-id="<?php echo $rand_nums; ?>" data-quizid="<?php echo $quiz_id; ?>"><?php _e( 'Submit', WP_EXAMS ); ?></button>
		<button class="button button-primary exms-next-ques exms-hide" data-id="<?php echo $rand_nums; ?>" data-quizid="<?php echo $quiz_id; ?>"><?php _e( 'Next', WP_EXAMS ); ?></button>
		<?php
		$exms_ques_msg = '';
		} elseif( ! $questions ) { ?>

			<div class="<?php echo $exms_ques_msg; ?>"><?php _e( 'No questions found in this quiz.', WP_EXAMS ); ?></div>
		<?php
		}
		?>
	</div> 
	<button class="<?php echo $exms_ques_msg; ?> button-primary exms-str-quiz" data-id="<?php echo $rand_nums; ?>"><?php _e( 'Start Quiz' ); ?></button>
<?php 
} else {
?>
	<div class="exms-quiz-box-not-allowed">
		<?php _e( 'Direct attempt to this quiz is not allowed. Please, contact the site admin.' ); ?>
	</div>

<?php
}