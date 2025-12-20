<?php

/** Quiz frontend template */
if (! defined('ABSPATH')) exit;

$labels = Exms_Core_Functions::get_options('labels');
$exms_quizzes  = isset($labels['exms_quizzes']) ?     $labels['exms_quizzes']  : __('Quiz', 'exms');
$exms_questions  = isset($labels['exms_questions']) ?     $labels['exms_questions']  : __('Questions', 'exms');
?>

<!-- main Container -->
<div class="exms-quiz-main-container">

    <div class="exms-bread-crumb">
        <?php
        $last_index = count($breadcrumb_items) - 1;

        foreach ($breadcrumb_items as $index => $item) {
            if ($index !== $last_index) {
        ?>
                <a class="exms-breadcrumbs" href="<?php echo esc_url(get_permalink($item['id'])); ?>" target="_blank">
                    <?php echo esc_html($item['title']); ?>
                </a> <span class="exms-quiz-breadcrumbs-sprater"> › </span>
            <?php
            } else {
            ?>
                <span class="exms-breadcrumb-title"><?php echo esc_html($item['title']); ?></span>
        <?php
            }
        }
        ?>
    </div>

    <!-- Quiz Detail -->
    <?php  
    if( ! $is_attempted ) {
        ?>
        <div class="exms-quiz-detail">
            <input type="hidden" value="<?php echo  $quiz_id ?>" class="exms-quiz-id">

            <!-- Quiz Title  -->
            <?php
            require_once EXMS_TEMPLATES_DIR . '/frontend/exms-quiz-header-template.php';
            ?>
            <div class="exms-quiz-desc-wrapper">
                <div class="exms-left-content">
                    <div class="exms-legacy-thumbnail-wrapper">
                        <?php if ( empty( $video_url ) && empty( $thumbnail_url ) ) { ?>
                            <?php 
                                $img_src = empty( $thumbnail_url ) ? EXMS_ASSETS_URL . 'imgs/no-feature-image.jpg' : $thumbnail_url;
                                ?>
                                <img src="<?php echo esc_url( $img_src ); ?>" class="exms-quiz-thumbnail" alt="">
                        <?php } elseif ( empty( $video_url ) ) { ?>
                            <img src="<?php echo esc_url( $thumbnail_url ); ?>" class="exms-legacy-thumbnail" alt="">
                        <?php } else { ?>
                            <div class="exms-legacy-video-wrapper">
                                <?php
                                if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {
                                    if ( strpos( $video_url, 'youtu.be' ) !== false ) {
                                        $video_id = basename( $video_url );
                                    } else {
                                        parse_str( parse_url( $video_url, PHP_URL_QUERY ), $params );
                                        $video_id = isset( $params['v'] ) ? $params['v'] : '';
                                    }
                                    if ( $video_id ) {
                                        echo '<iframe src="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '" allowfullscreen></iframe>';
                                    }
                                } elseif ( strpos( $video_url, 'vimeo.com' ) !== false ) {
                                    $video_id = (int) substr( parse_url( $video_url, PHP_URL_PATH ), 1 );
                                    if ( $video_id ) {
                                        echo '<iframe src="https://player.vimeo.com/video/' . esc_attr( $video_id ) . '" allowfullscreen></iframe>';
                                    }
                                }
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                    <!-- Quiz Description  -->
                    <div class="exms-quiz-desc-container">
                        <h2 class="exms-quiz-desc-title"><?php echo __('Description', 'exms'); ?></h2>
                        <?php if (! empty(trim(strip_tags($result->post_content)))) : ?>
                            <div class="exms-quiz-desc-text">
                                <?php echo wpautop(wp_kses_post($result->post_content)); ?>
                            </div>
                        <?php else : ?>
                            <div class="exms-empty-message">
                                <p> <?php echo __('No description available.', 'exms'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Toggle Sidebar Button for Tablet -->
                <button id="toggleQuizSidebarBtn" class="toggle-sidebar-btn" aria-label="Toggle Course Sidebar">
                    <i class="dashicons dashicons-welcome-learn-more"></i>
                </button>
                <!-- Quiz Sidebar -->
                <div class="exms-quiz-info-container">
                    <div class="exms-quiz-info-block">
                        <span class="exms-quiz-info-container-title">
                            <?php echo $exms_quizzes . __(' Details', 'exms'); ?>
                        </span>
                        <hr>
                        <?php
                        if ($show_quiz_percentage_val === 'on') {
                            ?>
                            <div class="exms-quiz-info-wrapper">
                                <span class="exms-quiz-info-text">
                                    <?php echo __('Required Passing Score : ', 'exms'); ?>
                                </span>
                                <span class="exms-quiz-number "><?php echo esc_html($quiz_percentage_val); ?>%</span>
                            </div>
                            <hr>
                            <?php
                        }
                        ?>
                        <div class="exms-quiz-info-wrapper">
                            <span class="exms-quiz-info-text">
                                <?php echo __('Number of ', 'exms') . $exms_questions . ' :'; ?>
                            </span>
                            <span class="exms-quiz-number "><?php echo $active_question_count ?></span>
                        </div>
                        <hr>
                        <?php if ( !exms_timer_is_empty($formatted_timer) && !empty($timer_type)) { ?>
                            <div class="exms-quiz-info-wrapper">
                                <span class="exms-quiz-info-text exms-start-time" data-start-time="<?php echo $formatted_timer; ?>">
                                    <?php esc_html_e('Allowed Time :', 'exms'); ?>
                                </span>
                                <span class="exms-quiz-number"><?php echo esc_html($formatted_timer); ?></span>
                            </div>
                            <hr>
                        <?php } ?>
                        <?php if ($quiz_total_seats_val > 0) { ?>
                            <div class="exms-quiz-info-wrapper">
                                <div>
                                    <span class="exms-quiz-info-text">
                                        <?php echo __('Seat Capacity:', 'exms'); ?>
                                    </span>
                                    <div class="exms-quiz-assign-seats">
                                        <div class="exms-circular-progress">
                                            <svg width="80" height="80">
                                                <circle cx="40" cy="40" r="35" stroke="#e0e0e0" stroke-width="8" fill="none" />
                                                <circle cx="40" cy="40" r="35" stroke="#552CA8" stroke-width="8" fill="none"
                                                stroke-dasharray="<?php echo $circumference; ?>"
                                                stroke-dashoffset="<?php echo $offset; ?>"
                                                stroke-linecap="round"
                                                transform="rotate(-90 40 40)" />
                                                <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="10">
                                                    <?php echo  __('Limit: ', 'exms') . esc_html($total_seat); ?>
                                                </text>
                                            </svg>
                                        </div>
                                        <div class="">
                                            <p class="exms-quiz-info-text"><?php echo __('Assigned :', 'exms') . ' ' . esc_html($assigned_seats); ?></p>
                                            <p class="exms-quiz-info-text"><?php echo __('Available :', 'exms') . ' ' . esc_html($avaliable_seats); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php  }  ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>

    <!-- Quiz Questions  -->
    <div class="exms-quiz-box">

        <!-- Timmer for Mobile -->
        <div class="exms-mobile-timmer">
            <?php /* if  ( $formatted_timer !== '00:00' && $formatted_timer !== '00:00:00' ) : */ ?>
            <div class="exms-question-container">
                <div class="exms-question-header">
                    <h2 class="exms-question-title"><?php echo __('Question', 'exms'); ?></h2>
                </div>

                <div class="exms-question-progress">
                    <div class="exms-progress-bar">
                        <div class="exms-progress-fill"></div>
                    </div>
                    <?php if ($display_questions !== 'exms_all_at_once') { ?>
                        <span class="exms-question-title">
                            <span class="current-question"></span>/<?php echo esc_html($active_question_count); ?>
                        </span>
                    <?php } ?>
                </div>

                <div class="exms-time-remaining">
                    <span class="exms-time-remain" data-initial-time="<?php echo esc_attr($formatted_timer); ?>">
                        <?php echo esc_html($formatted_timer); ?>
                    </span>
                    <?php echo __('min remaining', 'exms'); ?>
                </div>
            </div>
            <?php /* else :  */ ?>
            <?php if ($display_questions !== 'exms_all_at_once') { ?>
                <div class="exms-question-container">
                    <div class="exms-question-header">
                        <h2 class="exms-question-title"><?php echo __('Question', 'exms'); ?> <span class="current-question"></span>/<?php echo esc_html($active_question_count); ?></h2>
                    </div>
                </div>
            <?php } ?>
            <?php /* endif; */ ?>
        </div>

        <!-- Question Section -->
        <div class="exms-question-section">
            <div class="exms-question-wrapper"
                data-question=""
                data-question-type=""
                data-question-id=""
                data-question-score="">
                <div class="exms-question-title-wrapper">
                    <div class="exms-quiz-mobile-timer-area">
                        <span class="exms-quiz-timer-icon dashicons dashicons-clock"></span>
                        <span>
                        </span>
                    </div>
                </div>
                <div class="exms-question-number-count-wrapper">
                    <div class="exms-question-number-count-container">
                        <span class="exms-question-number-count"></span>
                    </div>
                    <div class="exms-question-text-cntainer">

                    </div>
                    <div>
                        <button class="exms-question-hint-btn fullwidth-hint" data-question-hint=""><?php echo __('Hint', 'exms'); ?></button>
                    </div>
                </div>
                <div class="exms-hint-sidebar">
                    <span class="exms-hint-close">&times;</span>
                    <p class="exms-hint-text"></p>
                </div>
                <div class="exms-quiz-buttons">
                    <a href="javascript:void(0);" class="exms-quiz-nav-btn exms-prev-btn" style="">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php echo __('Previous', 'exms'); ?>
                    </a>
                    <div class="exms-question-num-nav">
                        <span class="current-question"></span>
                    </div>
                    <a href="javascript:void(0);" class="exms-quiz-nav-btn exms-next-btn" data-question_time="">
                        <?php echo __('Next', 'exms'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
        </div>
        <!-- Question Deatil & Timmer -->
        <aside class="exms-sidebar">
            <?php /* if ( $formatted_timer !== '00:00' && $formatted_timer !== '00:00:00' ) : */ ?>
            <div class="exms-timer-box">
                <div class="exms-timer-container">
                    <div class="exms-timer">
                        <div class="exms-timer-fill" id="wpetimer" data-timer-type="<?php echo $timer_type; ?>" data-initial-time="<?php echo $formatted_timer ?>">
                            <div class="exms-quiz-timer-area">
                                <span class="exms-quiz-timer-icon dashicons dashicons-clock"></span>
                                <span>
                                    <?php echo $formatted_timer ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="exms-quiz-remain-time">
                    <?php echo $remaining_time; ?>
                </p>

            </div>
            <?php /* endif; */ ?>

            <!-- <?php if ($display_questions !== 'exms_all_at_once') { ?>
                        <div class="exms-question-list">
                            <h2 class="exms-question-list-heading"><?php echo __('Questions', 'exms') ?> <span class="current-question"></span> / <?php echo $active_question_count ?></h2>
                            <ul id="exms-questionNav">
                                <?php $question = 1;
                                foreach ($quiz_questions as $que) { ?> 
                                    <li><?php echo __('Question', 'exms') . ' ' . $question; ?> </li>
                                <?php
                                    $question++;
                                } ?>
                                
                            </ul>
                        </div>
                    <?php } ?> -->
        </aside>
    </div>

<!-- Quiz Compleated Model  -->
<div id="exms-modal" class="exms-modal-overlay">
    <div class="exms-modal">
        <h2 class="exms-modal-title"><?php echo __('Are you sure?', 'exms') ?></h2>
        <p><?php echo __('Are you sure you want to submit this? This process cannot be undone.', 'exms') ?></p>
        <div class="exms-modal-buttons">
            <button class="exms-cancel-btn"><?php echo __('No', 'exms') ?></button>
            <button class="exms-submit-btn exms-model-qiz-submit-btn"><?php echo __('Yes', 'exms') ?></button>
        </div>
    </div>
</div>

<div class="exms-quiz-result" style="display: <?php echo $is_attempted && $is_assigned ? 'block' : 'none'; ?>;">
    <h2 class="exms-quiz-result-title"><?php echo $exms_quizzes . __(' Result', 'exms') ?></h2>

    <?php 
    $quiz_option = exms_get_quiz_data( $quiz_id, 'passing_percentage,pass_quiz_message,fail_quiz_message,pending_quiz_message' );
    $quiz_percentage = isset( $quiz_option->passing_percentage ) ? floatval( $quiz_option->passing_percentage ) : 0;
    if( $quiz_percentage ) {

        $pass_msg = isset( $quiz_option->pass_quiz_message ) ? $quiz_option->pass_quiz_message : '';
        $fail_msg = isset( $quiz_option->fail_quiz_message ) ? $quiz_option->fail_quiz_message : '';
        $pending_msg = isset( $quiz_option->pending_quiz_message ) ? $quiz_option->pending_quiz_message : '';
        $admin_message = '';
        $quiz_status = '';

        if( $quiz_percentage <= $percentage && $user_pending_question_count < 1 ) {

            if( $pass_msg ) {
                $admin_message = $pass_msg;
            }
            $quiz_status = __( 'Pass', 'exms' );

        } elseif( $quiz_percentage > $percentage && $user_pending_question_count < 1 ) {

            if( $fail_msg ) {
                $admin_message = $fail_msg;
            }
            $quiz_status = __( 'Fail', 'exms' );
        
        } elseif( $user_pending_question_count > 0 ) {

            if( $pending_msg ) {
                $admin_message = $pending_msg;
            }
            $quiz_status = __( 'Pending', 'exms' );
        }
        $course_instructor = "";
        if( $admin_message ) {

            $course_id = exms_get_course_id();
            $course_instructor_ids = exms_get_assign_instructor_ids( $course_id );
            $instructor_names = '';
          
            if ( ! empty( $course_instructor_ids ) && is_array( $course_instructor_ids ) ) {
                $instructor_names = implode( ', ', array_map( 'exms_get_user_name', $course_instructor_ids ) );
            } 
            /**
             * Replace message to short tags
             */
            $replace_tags = [
                '{quiz_name}'               => get_the_title( $quiz_id ),
                '{course_name}'             => get_the_title( $course_id ), 
                '{group_name}'              => '',
                '{result}'                  => $quiz_status,
                '{score}'                   => $user_obtained_score,
                '{percentage}'              => $percentage,
                '{rank}'                    => '',
                '{correct_answers}'         => $user_correct_question_count,
                '{wrong_answers}'           => $user_wrong_question_count,
                '{pending_review}'          => $user_pending_question_count,
                '{user_name}'               => exms_get_user_name( $current_user_id ),
                '{instructor_name}'         => exms_get_user_name( $course_instructor ),
                '{required_percentage}'     => $quiz_percentage               
            ];

            $admin_message = str_replace( array_keys( $replace_tags ), array_values( $replace_tags ), $admin_message );
            ?>
            <div class="exms-quiz-message-wrapper">
                <?php echo $admin_message; ?>
            </div>
            <?php
        }
    } 
    ?>

    <div class="exms-result-container">

        <!-- Rank Card -->
        <div class="exms-card">
            <div><span class="dashicons dashicons-awards exms-icons"></span></div>
            <div>
                <h3><?php echo __('Rank', 'exms') ?></h3>
                <div class="exms-participants"><span class="dashicons dashicons-groups exms-participants-icon"></span><span class="exms-participants-count"></span><?php echo exms_get_quiz_rank( $quiz_id, $current_user_id ) .'/'. $total_rank; ?></div>
            </div>
        </div>

        <!-- Accuracy Card -->
        <div class="exms-card">
            <div><span class="dashicons dashicons-yes-alt exms-icons"></span></div>
            <div>
                <h3> <?php echo __('Percentage', 'exms') ?></h3>
                <div class="exms-value exms-quiz-percentage"> <?php echo round( $percentage, 2 ) . '%'.' ('.' '.$quiz_status.' '.')'; ?></div>
            </div>
        </div>

        <!-- Score Card -->
        <div class="exms-card">
            <div><span class="dashicons dashicons-star-half exms-icons"></span></div>
            <div>
                <h3><?php echo __('Score', 'exms') ?></h3>
                <div class="exms-value exms-quiz-score"><?php echo $user_obtained_score; ?></div>
            </div>
        </div>

        <!-- Time Card -->
        <div class="exms-card">
            <div><span class="dashicons dashicons-clock exms-icons"></span></div>
            <div>
                <h3><?php echo __('Time taken', 'exms') ?></h3>
                <div class="exms-value">
                    <span class="exms-quiz-time-taken">
                        <?php echo $user_time_taken; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Attempt Card -->
        <div class="exms-card exms-bottom-section">
            <div class="exms-chart"></div>
            <div class="exms-stats">
                <div class="exms-answer-detail"><span class="dashicons dashicons-saved exms-correct">
                    </span><?php echo __('Correct answer:', 'exms') ?>
                    <span class="exms-correct-question"><?php echo $user_correct_question_count; ?></span>
                </div>
                <div class="exms-answer-detail">
                    <span class="dashicons dashicons-no-alt exms-wrong"></span><?php echo __('Wrong answer:', 'exms') ?><span class="exms-wrong-question"><?php echo $user_wrong_question_count ?></span>
                </div>
                <div class="exms-answer-detail">
                    <span class="dashicons dashicons-no-alt exms-not"></span><?php echo __('Not Attempt:', 'exms') ?> <span class="exms-not-attempt"><?php echo $user_not_attempt_question_count; ?></span>
                </div>
                <div class="exms-answer-detail">
                    <span class="dashicons dashicons-text-page exms-review"></span><?php echo __('For Review:', 'exms') ?> <span class="exms-for-review"><?php echo $user_pending_question_count; ?></span> 
                </div>
            </div>
            <div class="exms-attempt-card">
                <span class="dashicons dashicons-editor-ul exms-attempt-icon"></span>
                <span class="exms-attempt-text">
                    <?php echo __('Attempt  ', 'exms') . $exms_questions ?>
                <p>
                    <span><?php echo $user_attempted_count.' / '. $active_question_count; ?></span>
                </p>
            </div>
        </div>
    </div>

    <div class="exms-actions">
        <?php
        if ( 'yes' == $quiz_reattempt_option ) {
            $reattempt_is_available = exms_is_reattempt_available( $current_user_id, $quiz_id );
            if( $reattempt_is_available ) {

                ?>
                <a href="javascript:void(0)" class="exms-btn exms-quiz-reattempt-btn">
                    <?php echo __('Reattempt ', 'exms') . $exms_quizzes; ?>
                </a>
                <?php
            }
        }

        if( 'on' == $view_answer_option ) {
            ?>
            <a href="#" class="exms-btn exms-view-answer" data-quiz-id="<?php echo esc_attr($quiz_id); ?>">
                <?php echo __('View Answer →', 'exms'); ?>
            </a>
            <?php
        }
        ?>
    </div>
    <?php
    echo exms_get_skeleton_loader();
    ?>
</div>

<!-- Quiz Submitted Answer -->
<div class="exms-answer-review-box" style="display:none;"></div>
</div>