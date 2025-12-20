<?php
/**
 * Question functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get quizzes of a question
 *
 * @param Mixed     $ques_id               Question id
 * @param Array     $submitted_answers     Submitted answers in array format
 *
 */
function exms_check_submitted_answer( $ques_id, $submitted_answers ) {

    $ques_opts = exms_get_question_options( $ques_id );
    $all_answers = isset( $ques_opts['exms_answers'] ) ? $ques_opts['exms_answers'] : [];
    $ques_type = isset( $ques_opts['exms_question_type'] ) ? $ques_opts['exms_question_type'] : [];
    
    if( ! $ques_type || ! $submitted_answers || ! is_array( $submitted_answers ) ) {

        return false;
    }
    $answers = exms_get_question_answers( $ques_id );
    
    $correct_ans = 0;
    $total_answers = count( $submitted_answers );
    $total_points = 0;
    $gained_points = 0;
    $question_points = isset( $ques_opts['exms_points'] ) ? (int) $ques_opts['exms_points'] : 0;
    $show_correct_answer = [];

    /**
     * Check single/multiple choice answers
     */
    if( $answers && ( 'single_choice' == $ques_type || 'multiple_choice' == $ques_type ) ) {

        foreach( $answers as $index => $ans ) {

            if( in_array( $ans['answer'], $submitted_answers ) && isset( $answers[$index]['type'] ) && $answers[$index]['type'] == 'correct' ) {

                $correct_ans++;
                $gained_points += isset( $answers[$index]['points'] ) && $answers[$index]['points'] > 0 ? $answers[$index]['points'] : $question_points;
            }

            if( isset( $ans['type'] ) && 'correct' == $ans['type'] ) {

                $total_points += isset( $ans['points'] ) && $ans['points'] ? $ans['points'] : $question_points;

                $show_correct_answer = $ans['answer'];
            
            } 
        }
    }

    /**
     * Check sorting choice answers
     */
    elseif( $answers && 'sorting_choice' == $ques_type ) {

        foreach( $answers as $sr_index => $ans ) {

            if( isset( $submitted_answers[$sr_index] ) && $ans['answer'] == $submitted_answers[$sr_index] ) {

                $correct_ans++;
                $gained_points += isset( $answers[$sr_index]['points'] ) && $answers[$sr_index]['points'] > 0 ? $answers[$sr_index]['points'] : $question_points;
            }
            $total_points += isset( $ans['points'] ) && $ans['points'] ? $ans['points'] : 0;

            $show_correct_answer[] = $ans['answer'];
        }
        $total_points = $total_points > 0 ? $total_points : $question_points;
    }

    /**
     * Check sorting choice answers
     */
    elseif( $answers && 'matrix_sorting' == $ques_type ) {

        foreach( $answers as $sr_index => $ans ) {

            $show_correct_answer[] = $ans['answer'];

            if( isset( $submitted_answers[$sr_index] ) && isset( $ans['answer'][1] ) && $ans['answer'][1] == $submitted_answers[$sr_index] ) {

                $correct_ans++;
                $gained_points += isset( $answers[$sr_index]['points'] ) && $answers[$sr_index]['points'] > 0 ? $answers[$sr_index]['points'] : $question_points;
            }

            $total_points += isset( $ans['points'] ) && $ans['points'] ? $ans['points'] : 0;
        }
        $total_points += isset( $ans['points'] ) && $ans['points'] ? $ans['points'] : $question_points;
    }

    /**
     * Check range type answers
     */
    elseif( $answers && 'range' == $ques_type && isset( $submitted_answers[0] ) ) {

        $ques_min_max = isset( $answers['correct'] ) ? $answers['correct'] : '';
        $ques_min = explode( "-", $ques_min_max, 2 );
        $ques_max = (int) substr( $ques_min_max, strpos( $ques_min_max, "-" ) + 1 );    
        
        $ans_min = explode( "-", $submitted_answers[0], 2 );
        $ans_max = (int) substr( $submitted_answers[0], strpos( $submitted_answers[0], "-" ) + 1 ); 

        if( (int) $ans_min[0] >= (int) $ques_min[0] && $ans_max <= $ques_max ) {

            $correct_ans++;
            $gained_points += isset( $answers['points'] ) && $answers['points'] > 0 ? $answers['points'] : $question_points;
        }

        if( isset( $answers['points'] ) ) {

            $total_points += $answers['points'] ? $answers['points'] : $question_points;
        }

        if( isset( $answers['correct'] ) ) {
            $show_correct_answer = $answers['correct'];
        }
    }

    /**
     * Check free choice answers
     */
    elseif( 'free_choice' == $ques_type ) {

        if( isset( $answers['answers'] ) && isset( $submitted_answers[0] ) && in_array( $submitted_answers[0], $answers['answers'] ) ) {
            
            $correct_ans++;
            $gained_points += isset( $answers['points'] ) && ! empty( $answers['points'] ) && $answers['points'] > 0 ? $answers['points'] : $question_points;
        } 
        
        if( isset( $answers['points'] ) ) {

            $total_points += $answers['points'] ? $answers['points'] : $question_points;
        }

        if( isset( $answers['answers'] ) ) {
            foreach( $answers['answers'] as $ans ) {
                $show_correct_answer[] = $ans;
            }
        }
    }

    /**
     * Check fill blank answers
     */
    elseif( 'fill_blank' == $ques_type ) {

        $matches = exms_get_question_all_blanks( $ques_id );
        
        $b_index = 0;
        if( $matches ) {

            foreach( $matches as $match ) {
                
                foreach( $match as $blank ) {

                    $blank = str_replace( [ '{', '}' ], '', $blank );

                    if( isset( $submitted_answers[$b_index] ) && $blank == $submitted_answers[$b_index] ) {

                        $correct_ans++;
                    }
                    $b_index++;

                    $show_correct_answer = $blank;
                }
            }
        }
    }

    $has_passed = $correct_ans == $total_answers ? true : false;
    $resp_msg = exms_get_question_response_message( $ques_id, $has_passed );

    return array( 'question_id' => $ques_id, 'answers' => $answers, 'correct_answers' => $correct_ans, 'total_answers' => $total_answers, 'total_points' => $total_points, 'obtained_points' => $gained_points, 'passed' => $has_passed, 'response' => $resp_msg, 'show_correct_answer' => $show_correct_answer, 'question_type' => $ques_type );
}

/**
 * Get questions answers
 *
 * @param Mixed   $ques_id    Questions id
 *
 * @return Aray
 */
function exms_get_question_answers( $ques_id ) {

    $opts = get_post_meta( $ques_id, get_post_type( $ques_id ) . '_opts', true );
    $q_answers = [];
    $ques_type = isset( $opts['exms_question_type'] ) ? $opts['exms_question_type'] : false;
    $answers = isset( $opts['exms_answers'] ) ? $opts['exms_answers'] : [];
    $points = isset( $opts['exms_ques_ans_points'] ) ? $opts['exms_ques_ans_points'] : [];
    $types = isset( $opts['wpeq_ques_ans_type'] ) ? $opts['wpeq_ques_ans_type'] : [];

    /**
     * Single/Multiple/Sorting answers
     */
    if( $answers && ( 'single_choice' == $ques_type || 'multiple_choice' == $ques_type || 'sorting_choice' == $ques_type ) ) {

        foreach( $answers as $index => $answer ) {

            $q_answers[$index]['answer'] = wp_strip_all_tags( $answer );
            $q_answers[$index]['points'] = isset( $points[$index] ) ? $points[$index] : 0;
            $q_answers[$index]['type'] = isset( $types[$index] ) ? $types[$index] : '';
        }
    }

    /**
     * Matrix answers
     */
    elseif( $answers && 'matrix_sorting' == $ques_type ) {

        $ans_count = count( $answers ) > 0 ? ( count( $answers ) / 2 ) : 0;
        $index_inc = 0;

        for( $x = 0; $x < $ans_count; $x++ ) {

            $q_answers[] = array(
                'answer'    => [ wp_strip_all_tags( $answers[$x+$index_inc] ), wp_strip_all_tags( $answers[$x+1+$index_inc] ) ],
                'points'    => isset( $points[$x] ) ? $points[$x] : 0,
            );
            $index_inc++;
        }
    }

    /**
     * Range answers
     */
    elseif( $answers && 'range' == $ques_type ) {

        $q_answers = $answers;
    }

    /**
     * Range answers
     */
    elseif( $answers && 'free_choice' == $ques_type ) {

        $choices['answers'] = explode( PHP_EOL, $answers );
        $choices['points'] = $points;
        $q_answers = $choices;
    }

    return $q_answers;
}

/**
 * Get questions submitted response
 *
 * @param Mixed     $ques_id   Questions id
 * @param String    $type      Whether to return "correct" or "wrong" response message
 *
 */
function exms_get_question_response_message( $ques_id, $type ) {

    $opts = get_post_meta( $ques_id, get_post_type( $ques_id ) . '_opts', true );
    
    if( 'correct' == $type || $type ) {

        return isset( $opts['exms_corr_ans_msg'] ) ? $opts['exms_corr_ans_msg'] : '';

    } elseif( 'wrong' == $type || ! $type ) {

        return isset( $opts['exms_incorr_ans_msg'] ) ? $opts['exms_incorr_ans_msg'] : '';
    }
}

/**
 * Get questions options
 *
 * @param Mixed     $ques_id   Questions id
 *
 */
function exms_get_question_options( $ques_id ) {

    $opts = get_post_meta( $ques_id, get_post_type( $ques_id ) . '_opts', true );
    return $opts;
}

/**
 * Return all blanks in a question only use for fill blank type question
 *
 * @param Mixed     $ques_id   Questions id
 */
function exms_get_question_all_blanks( $ques_id ) {

    $question = get_the_content( false, false, $ques_id );
    preg_match_all( '/{\w*}/', $question, $matches );
    return $matches;
}

/**
 * Create file upload form
 *
 * @param Mixed     $ques_id    Questions id
 * @param Mixed     $quiz_id    Quiz id
 */
function exms_create_upload_form( $ques_type, $ques_id, $quiz_id ) {

?>
    <form class="exms-fu-form" method="post" enctype="multipart/form-data">
        <?php 
        if( 'essay' != $ques_type ) {

            ?>
            <input type="hidden" name="question_id" value="<?php echo $ques_id; ?>" />
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>" />
            <input type="file" name="exms_file_upload" class="exms-file-upd" />
            <input type="hidden" name="action" value="exms_submit_quiz_answer" />
            <?php

        } else {

            ?>
            <textarea class="exms-fc-textarea" rows="5" cols="50" name="answer" placeholder="<?php _e( 'Write here....', WP_EXAMS ); ?>"></textarea>
            <p class="exms-fc-message"><?php _e( 'This response will be reviewed and graded after submission.', WP_EXAMS ); ?></p>
            <?php

        }?>
    </form>
<?php
}

/**
 * Get quizzes for a question
 * 
 * @param $question_id
 * @param $paged ( optional ) limits to question pages count Like ( 0, 10
 */
function exms_get_quizzes_for_a_question( $question_id, $paged = '' ) {

    $post_type = get_post_type( $question_id );
    if( 'exms_questions' != $post_type ) {
        return false;
    }

    $quiz_ids = EXMS_PR_Fn::exms_parent_assigned_post_ids( 'exms_quizzes', $post_type, $question_id, $paged );

    if( ! empty( $quiz_ids ) && is_array( $quiz_ids ) ) {
        return $quiz_ids;
    }
}

/** working functions **/

/**
 * create a function to get question correct answer
 *
 * @param $question_id
 */
function exms_get_question_correct_answer( $question_id, $question_type ) {

    global $wpdb;

    if( ! $question_id || ! $question_type ) {
        return;
    }

    $table_name = $wpdb->prefix . 'exms_answer';

    // $is_correct = ( $question_type == 'sorting_choice' ) ? 0 : 1;
    $is_correct = ( $question_type == 'sorting_choice' || $question_type == 'fill_blank' ) ? 0 : 1;

    
    $correct_answer_query = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT answer_text
            FROM {$table_name}
            WHERE question_id = %d
            AND is_correct = %d",
            $question_id,
            $is_correct
        )
    );

    if( 'single_choice' == $question_type ) {
        $correct_answer = isset( $correct_answer_query[0] ) ? $correct_answer_query[0] : 0;
    } elseif( 'free_choice' == $question_type ) {
        $correct_answer = isset( $correct_answer_query[0] ) ? unserialize(  $correct_answer_query[0] ) : [];
    } elseif( 'fill_blank' == $question_type ) {
        $correct_answer = isset( $correct_answer_query[0] ) ? $correct_answer_query[0] : [];
    } else {
        $correct_answer = $correct_answer_query;
    }
    return $correct_answer;
}

/**
 * create a function to get question coulmn data
 *  
 * @param $question_id
 * @param $coulmn_name
 */
function exms_get_question_settings( $question_id, $coulmn_name = '*' ) {

    global $wpdb;

    if ( ! $question_id || ! $coulmn_name ) {
        return;
    }

    $table_name = $wpdb->prefix . 'exms_questions';
    
    $column_data_query = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT $coulmn_name FROM {$table_name} WHERE question_id = %d",
            $question_id
        )
    );

    $column_data = isset( $column_data_query[0] ) ? $column_data_query[0] : '';
    return $column_data;
} 

/**
 * create a function to update question answer
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $user_answer
 * @param $question_id
 * @param $question_type
 */  
function exms_update_user_answer( $user_id, $quiz_id, $answer, $question_id, $question_type, $question_time = 0 ) {
    
    global $wpdb;

    $correct_answer      = exms_get_question_correct_answer( $question_id, $question_type );
    $points_for_question = exms_get_question_settings( $question_id, 'points_for_question' );
    $attempted_at        = strtotime( current_time( 'mysql' ) );
    $is_correct          = 0;
    $award_points        = 0;

    $data = [ 'answer' => $answer ];

    // Attempt status
    if ( ! $answer || 'undefined' === $answer ) {
        $is_correct = 'not-attempt';
    }

    if ( 'file_upload' === $question_type && $answer ) {
        $is_correct = 'pending';
    }

    // Correct answer check
    if ( ( $answer && $correct_answer ) 
        && ( $answer == $correct_answer ) 
        && ( 'single_choice' === $question_type || 'sorting_choice' === $question_type ) ) {
        
        $is_correct   = 1;
        $award_points = $points_for_question;

    } elseif ( ( $answer && $correct_answer ) && 'multiple_choice' === $question_type ) {

        $difference = array_diff( $answer, $correct_answer );

        if ( empty( $difference ) ) {
            $is_correct   = 1;
            $award_points = $points_for_question;
        }

    } elseif ( 'free_choice' === $question_type ) {
        if ( ! empty( $correct_answer ) && is_array( $correct_answer ) ) {
            foreach ( $correct_answer as $c_ans ) {
                $c_parts    = explode( '|', $c_ans );
                $answer_txt = $c_parts[0] ?? '';

                if ( $answer_txt === $answer ) {
                    $is_correct   = 1;
                    $award_points = isset( $c_parts[1] ) ? intval( $c_parts[1] ) : 1;
                }
            }
        }

    } elseif ( 'fill_blank' === $question_type ) {

        preg_match_all( '/\{([^}]*)\}/', $correct_answer, $blanks );
        if ( $blanks[1] == $answer ) {
            $is_correct   = 1;
            $award_points = $points_for_question;
        }
    }

    // Save answer in DB
    $json_answer    = wp_json_encode( $data );
    $attempt_number = exms_get_submmited_question( intval( $user_id ), intval( $quiz_id ), intval( $question_id) );
    $submitted_question_count = exms_get_user_submitted_question_count( $user_id, $quiz_id, $attempt_number );
    $quiz_question_count = count( exms_get_quiz_questions( $quiz_id ) );
    
    if( $submitted_question_count == $quiz_question_count ) {
        $attempt_number = $attempt_number + 1;
    }

    if( ! $attempt_number ) {
        $attempt_number = 1;
    }

    $is_question_submitted = exms_check_question_submission( intval( $user_id ), intval( $quiz_id ), intval( $question_id ), $attempt_number );
    $attempt_quiz_number = exms_get_submmited_quiz_attempt( intval( $user_id ), intval( $quiz_id ) );

    if( ( intval( $is_question_submitted ) == intval( $attempt_quiz_number ) ) && ( $submitted_question_count == $quiz_question_count ) ) {
        $is_question_submitted = '';
    }

    if( ! $is_question_submitted || empty( $is_question_submitted ) ) {

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}exms_exam_user_question_attempts
                (user_id, question_id, quiz_id, question_type, answer, attempt_date, score, total_possible_score, is_correct, attempt_number, time_taken)
                VALUES (%d, %d, %d, %s, %s, %s, %f, %f, %s, %d, %d)",
                intval( $user_id ),
                $question_id,
                intval( $quiz_id ),
                $question_type,
                $json_answer,
                $attempted_at,
                $points_for_question,
                $award_points,
                $is_correct,
                $attempt_number,
                $question_time
            )
        );

        // Handle file upload separately

        if ( 'file_upload' === $question_type && isset( $answer['tmp_name'] ) ) {
            exms_handle_file_upload( $answer, $quiz_id, $question_id, $user_id, $attempt_number );
        }
    }
}

function exms_handle_file_upload( $file, $quiz_id, $question_id, $user_id, $attempt_number ) {
    global $wpdb;

    if ( $file['error'] === UPLOAD_ERR_OK ) {
        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/exms-exams/question-uploads/';

        if ( ! file_exists( $custom_dir ) ) {
            wp_mkdir_p( $custom_dir );
        }

        // Generate unique name
        $filename = wp_unique_filename( $custom_dir, $file['name'] );
        $filename = $quiz_id . '_' . $question_id . '_' . $user_id . '_' . $filename;
        $target   = $custom_dir . '/' . $filename;

        if ( move_uploaded_file( $file['tmp_name'], $target ) ) {
            $file_url = $upload_dir['baseurl'] . '/exms-exams/question-uploads/' . $filename;

            $wpdb->update(
                "{$wpdb->prefix}exms_exam_user_question_attempts",
                [ 'file_url' => $file_url ],
                [
                    'quiz_id'           => $quiz_id,
                    'question_id'       => $question_id,
                    'user_id'           => $user_id,
                    'attempt_number'    => $attempt_number
                ]
            );
        }
    }
}

/**
 * create a function to check question is already submitted or not
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $question_id
 */
function exms_get_submmited_question( $user_id, $quiz_id, $question_id ) {

    global $wpdb;

    if( ! $user_id || ! $quiz_id || ! $question_id ) {
        return;
    }

    $table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

    $latest_attempt = $wpdb->get_var( $wpdb->prepare(
        "SELECT attempt_number
        FROM {$table_name}
        WHERE user_id = %d
        AND quiz_id = %d
        AND question_id = %d
        ORDER BY attempt_number DESC
        LIMIT 1",
        $user_id,
        $quiz_id,
        $question_id
    ) );

    // if ( is_null($latest_attempt) ) {
    //     $latest_attempt = 1;
    // } else {
    //     $latest_attempt = $latest_attempt;
    // }

    return $latest_attempt;
}

/**
 * create a function to get question type
 * 
 * @param $question_id
 */
function exms_get_question_type( $question_id ) {

    global $wpdb;
    $table_name  = $wpdb->prefix . 'exms_questions';

    $question_type = $wpdb->get_var(
        "SELECT question_type FROM {$table_name} WHERE question_id = {$question_id}"
    );

    return $question_type;
}

/**
 * create a function to check question is already update
 */
function exms_check_question_submission( $user_id, $quiz_id, $question_id, $attempt_number ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

    $data_exists = $wpdb->get_var( 
        $wpdb->prepare(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE user_id = %d 
            AND quiz_id = %d 
            AND question_id = %d 
            AND attempt_number = %d",
            $user_id, 
            $quiz_id, 
            $question_id, 
            $attempt_number
        )
    );

    return $data_exists;
}

/**
 * create a function to get user submitted question count
 * 
 * @param $user_id
 * @param $quiz_id
 * @param $attempt_number
 */
function exms_get_user_submitted_question_count( $user_id, $quiz_id, $attempt_number ) {

    global $wpdb;

    $table_name = $wpdb->prefix . 'exms_exam_user_question_attempts';

    $count = $wpdb->get_var( 
        $wpdb->prepare(
            "SELECT COUNT(*) 
            FROM $table_name 
            WHERE user_id = %d 
            AND quiz_id = %d 
            AND attempt_number = %d",
            $user_id, 
            $quiz_id, 
            $attempt_number
        )
    );

    return $count;
}