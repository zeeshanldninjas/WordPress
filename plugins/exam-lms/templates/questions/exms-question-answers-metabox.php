<?php

/**
 * WP EXAMS - Question Answer metabox content
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$existing_labels = Exms_Core_Functions::get_options('labels');
$question_singular = '';
$quiz_singular = '';
if ( is_array( $existing_labels ) && array_key_exists( 'exms_questions', $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
    $question_singular = $existing_labels['exms_questions'];
    $quiz_singular = $existing_labels['exms_quizzes'];
}
?>
<div class="wpeq-question-answers-wrapper">

	<div class="exms-row exms-question-settings-row">
    <!-- Question type option -->
		<div class="exms-title">
			<?php echo $question_singular . __( ' Type', 'exms' ); ?>
		</div>
		<div class="exms-data">
			<select name="exms_question_type" class="wpeq-question-type-dropdown">
				<?php foreach ( $question_types as $type ) { 
					$value = esc_attr( $type['type_name'] );
					?>
					<option value="<?php echo $value; ?>" <?php selected( $sel_ques_type, $value ); ?>>
						<?php echo ucwords( str_replace( '_', ' ', $value ) ); ?>
					</option>
				<?php } ?>
			</select>
		</div>
        <div class="exms-answers-container">

            <?php if ( 'essay' == $question_type ) { ?>
                <div class="exms-instruction-message" > 
                    <div class="exms-instruction-icon"> 
                        <span class="dashicons dashicons-info-outline"></span>
                    </div>
                    <div class="exms-instruction-text"> 
                        <?php echo sprintf( __( 'The box will appear when attempting this %s for writing an essay.', 'exms' ), $question_singular ); ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ( 'file_upload' == $question_type ) { ?>
                <div class="exms-instruction-message" >
                    <div class="exms-instruction-icon"> 
                        <span class="dashicons dashicons-info-outline"></span>
                    </div>
                    <div class="exms-instruction-text"> 
                        <?php echo sprintf( __( 'This %s type does not support any answers.', 'exms' ), $question_singular); ?>
                    </div>
                </div>
            <?php } ?>

            <?php if ( 'range' == $question_type ) {
            ?>
                <div class="wpeq-answer-div wpeq-ans-div" style="border: none;">

                    <div class="wpeq-range-input-title-div"><?php _e( 'Min Value', 'exms' ); ?></div>
                    <div class="wpeq-range-input-div">
                        <input type="number" name="exms_answers[min]" class="wpeq-range-input-fields" placeholder="min value" value="<?php echo esc_attr( $range_min ); ?>" />
                    </div>

                    <div class="wpeq-range-input-title-div"><?php _e( 'Max Value', 'exms' ); ?></div>
                    <div class="wpeq-range-input-div">
                        <input type="number" name="exms_answers[max]" class="wpeq-range-input-fields" placeholder="max value" value="<?php echo esc_attr( $range_max ); ?>" />
                    </div>

                    <div class="wpeq-range-input-title-div"><?php _e( 'Correct Range', 'exms' ); ?></div>
                    <div class="wpeq-range-input-div">
                        <input type="number" name="exms_answers[correct]" class="wpeq-range-input-fields" placeholder="value1 - value2" value="<?php echo esc_attr( $correct_answer ); ?>" />
                    </div>

                    <div class="wpeq-range-input-title-div"><?php _e( 'Point', 'exms' ); ?></div>
                    <div class="wpeq-range-input-div">
                        <input type="number" name="exms_ques_ans_points" class="wpeq-ques-ans-points wpeq-range-input-fields" placeholder="points" value="<?php echo esc_attr( $ans_points ); ?>" />
                    </div>

                </div>
            <?php } ?>

    <?php if ( $answers && is_array( $answers ) ) {

    $index_count = 0;
    $rand        = rand( 0, 10000 );

    if ( in_array( $question_type, ['single_choice', 'multiple_choice', 'sorting_choice', 'matrix_sorting'] ) ) { ?>

        <div class="wpeq-answer-div wpeq-ans-div<?php echo esc_attr( $rand ); ?>">
            <div class="exms-answers-heading">
                <span><?php _e( "Answers", 'exms' ); ?></span>
                <?php if ( $question_type !== 'sorting_choice' ) { ?>
                    <span><?php _e( "Correct Answer", 'exms' ); ?></span>
                <?php } ?>
            </div>

            <?php foreach ( $answers as $k => $answer ) {
                $inner_rand = rand( 0, 10000 );
                $ans_point  = $ans_points[$k] ?? '';
                    $ans_type   = $ans_types[$k] ?? '';
                    $is_checked = isset( $answer['correct_answer'] ) && $answer['correct_answer'] === "correct" ? 'checked' : '';
                    $inputType  = ( $question_type === 'single_choice' ) ? 'radio' : 'checkbox';
                ?>


                    <div class="wpeq-answer-row exms-ans-<?php echo esc_attr( $inner_rand ); ?> exms-get-value wpeq-draggable" data-id="<?php echo esc_attr( $inner_rand ); ?>">
                        <input type="hidden" class="wpeq-ques-ans-type" name="wpeq_ques_ans_type[]" value="<?php echo esc_attr( isset( $answer['correct_answer'] ) && $answer['correct_answer'] === 'correct' ? 'correct' : 'wrong' ); ?>" />
                    <span class="wpeq-drag-icon">⋮⋮</span>

                    <textarea class="exms-textarea-ans"
                              name="exms_answers[<?php echo $index_count; ?>]"
                              id="exms_answers<?php echo $index_count; ?>"
                              rows="1" cols="50"><?php echo esc_textarea( $answer['answer'] ); ?></textarea>
            
                    <?php if ( $question_type !== 'sorting_choice' ) { ?>
                        <div class="wpeq-radio-wrapper">
                            <label class="wpeq-radio-label">
                                <input type="<?php echo esc_attr( $inputType ); ?>"
                                       class="wpeq-custom-radio wpeq-ques-ans-<?php echo $inputType; ?>"
                                       name="exms_ques_ans_radio[]"
                                       <?php echo $is_checked; ?> />
                                <?php if( $question_type == 'sorting_choice' ) { ?>
                                    <span class="dashicons dashicons-trash"></span>
                                <?php } ?>
                                <span class="wpeq-radio-style"></span>
                            </label>
                        </div>
                    <?php } ?>
                    <span class="exms-sorting-delete dashicons dashicons-trash"></span>
                </div>

            <?php
                $index_count++;
            } ?>
        </div>

    <?php } elseif ( $question_type === 'free_choice' ) {

        foreach ( $answers as $k => $answer ) {            
            $decoded_answers = @unserialize( $answer['answer'] );
            $textarea_value = '';
            if( is_array( $decoded_answers ) ) {
                $formatted_answers = array_map( function( $ans ) {
                    return str_replace( '|', ' | ', $ans );
                }, $decoded_answers );
                $textarea_value = implode( "\n", $formatted_answers );
            }
            ?>
            <div class="wpeq-answer-div" style="border: none;">
                <textarea class="exms-free-choice-ans" name="wpeq_ques_ans[]" rows="10" cols="50" placeholder="Write answer"><?php echo esc_textarea( $textarea_value ); ?></textarea>
                <br>
                <b><?php _e( 'Points', 'exms' ); ?></b>
                <input type="number" name="exms_ques_ans_points[]" class="wpeq-ques-ans-points" value="<?php echo esc_attr( $ans_points[$k] ?? '' ); ?>" />
            </div>
            <div class="exms-instruction-message">
                <div class="exms-instruction-icon"> 
                    <span class="dashicons dashicons-info-outline"></span>
                </div>
                <div class="exms-instruction-text"> 
                    <p><b><?php _e( 'How to use the area?', 'exms' ); ?></b></p>
                    <p><?php _e( 'Correct answers (one per line) (answers will be converted to lower case). If mode "Different points for each answer" is activated, you can assign points to each answer using "|". Example: One|15. The default point value is 1.', 'exms' ); ?></p> 
                </div>
            </div>
            <?php
            $index_count++;
        }

    } elseif ( 'fill_blank' == $question_type ) { ?>
        <div class="wpeq-answer-div wpeq-ans-div<?php echo esc_attr( $rand ); ?>">
            <div class="exms-answers-heading">
                <span><?php echo $question_singular . __( " with the blank", 'exms' ); ?></span>
            </div>

            <?php foreach ( $answers as $k => $answer ) {
                $inner_rand = rand( 0, 10000 );
                ?>
                <div class="wpeq-answer-row exms-ans-<?php echo esc_attr( $inner_rand ); ?> exms-get-value wpeq-draggable" data-id="<?php echo esc_attr( $inner_rand ); ?>">
                    <textarea class="exms-textarea-ans"
                            name="exms_answers[<?php echo $index_count; ?>]"
                            id="exms_answers<?php echo $index_count; ?>"
                            rows="1" cols="50"><?php echo esc_textarea( $answer['answer'] ); ?>
                    </textarea>
                </div>

            <?php
                $index_count++;
            } ?>
        </div>
        <div class="exms-instruction-message" >
            <div class="exms-instruction-icon">
                <span class="dashicons dashicons-info-outline"></span>
            </div>
            <div class="exms-instruction-text" > 
                <p><b><?php _e( 'How to use blanks?', 'exms' ); ?></b></p>
                <p><?php _e( 'just write the word inside curly brackets like this: {Example}', 'exms' ); ?></p> 
            </div>
        </div>
    <?php }}
    $text = "Add Answer";
    if( 'fill_blank' == $question_type ) {
        $text = "Insert Blank";
    }
    ?>
    <div class="wpeq-ans-button-div">
        <button class="wpeq-add-answer-btn button" <?php echo $lock_button; ?>><?php echo esc_html( $text ); ?></button>
    </div>
</div>
</div>
</div>