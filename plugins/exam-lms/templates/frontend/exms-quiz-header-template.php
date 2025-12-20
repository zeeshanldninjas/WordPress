<div class="exms-quiz-container">
    <div class="exms-quiz-detail-area">
        <h2 class="exms-quiz-title"><?php echo $result->post_title ? $result->post_title : ""; ?></h2>
        <p class="exms-quiz-short-text"><?php echo $result->post_excerpt ? wp_trim_words( $result->post_excerpt, 15, '...' )  : ""; ?>
        </p>
    </div>
    <div class="exms-quiz-start-btn-container">
        <?php

            if ( ! $is_assigned ) {
                ?>
                <a href="javascript:void(0);" id="quiz-buynow-button" data-quiz-id="<?php echo $quiz_id ? $quiz_id : ""; ?>" >
                    <?php echo __( 'Buy Now', 'exms' ); ?>
                </a>
                <?php
                if( exms_is_admin_user() ) {
                    ?>
                    <a href="javascript:void(0);" id="exms-quiz-enroll-as-admin" data-quiz-id="<?php echo $quiz_id ? $quiz_id : ""; ?>" >
                        <?php echo __( 'Enroll as an Admin', 'exms' ); ?>
                    </a>
                    <?php
                } 
            } elseif ( empty( $quiz_questions ) ) {
                ?>
                <div class="exms-quiz-no-questions">
                    <p class="exms-no-questions-info">
                        <?php echo __( 'No ', 'exms' ) . $exms_questions . __( ' have been assigned to this ', 'exms' ) . $exms_quizzes . ' .'; ?>
                    </p>
                    <a href="javascript:void(0);" id="quiz-start-button" class="exms-quiz-start-btn" style="pointer-events: none; opacity: 0.8;">
                        <?php echo __( 'Start ', 'exms' ) . $exms_quizzes; ?>
                    </a>
                </div>
                <?php
            } else {
                ?>
                <a href="javascript:void(0);" id="quiz-start-button" class="exms-quiz-start-btn">
                    <?php echo __( 'Start ', 'exms' ) . $exms_quizzes; ?>
                </a>
                <?php
            }
        ?>
    </div>
</div>