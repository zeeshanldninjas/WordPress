<?php
/** Quiz frontend template */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

?>
<!-- main Container -->
<div class="exms-quiz-main-container">
    <h2 class="exms-page-title"><?php echo __( 'Quiz Details', 'exms' ); ?></h2>
    <!-- filter Quiz -->
    <div  class="exms-filter-wrapper">
        <?php if ( $student ) { ?>
            <div class="exms-filter-section">
               <select name="exms-quiz" id="exms-quiz-select" data-user-id="<?php echo esc_attr( $user_id ); ?>">
                    <option disabled selected><?php echo __( 'Select Quiz', 'exms' ); ?></option>
                    <?php
                        if ( ! empty( $quizzes ) && is_array( $quizzes ) ) {
                            foreach ( $quizzes as $quiz ) {
                                $quiz_id = isset( $quiz->id ) ? esc_attr( $quiz->id ) : '';
                                $quiz_title = isset( $quiz->post_title ) ? esc_html( $quiz->post_title ) :  __( 'Untitled Group', 'exms' );
                                ?>
                                <option value="<?php echo $quiz_id; ?>"><?php echo $quiz_title; ?></option>
                                <?php
                            }
                        }
                    ?>
                </select>
            </div>
        <?php } else{ ?>
        <div class="exms-filter-section">
            <select name="exms-group" id="exms-group-select">
                <option disabled selected value=""><?php echo __( 'Select Group', 'exms' ); ?></option>
                <?php
                    if ( ! empty( $groups ) && is_array( $groups ) ) {
                        foreach ( $groups as $group ) {
                            $group_id = isset( $group->id ) ? esc_attr( $group->id ) : '';
                            $group_title = isset( $group->post_title ) ? esc_html( $group->post_title ) :  __( 'Untitled Group', 'exms' );
                            ?>
                            <option value="<?php echo $group_id; ?>"><?php echo $group_title; ?></option>
                            <?php
                        }
                    }
                ?>
            </select>
            <select name="exms-courses" id="exms-courses-select">
                <option disabled selected><?php echo __( 'Select Courses', 'exms' ); ?></option>
            </select>
            <select name="exms-lessons" id="exms-lesson-select">
                <option disabled selected><?php echo __( 'Select Lessons', 'exms' ); ?></option>
            </select>
            <select name="exms-quiz" id="exms-quiz-select" >
                <option disabled selected><?php echo __( 'Select Quiz', 'exms' ); ?></option>
            </select>
            <select name="exms-student">
                <option disabled selected><?php echo __( 'Select Student', 'exms' ); ?></option>
            </select>
        </div>
        <?php }?>
        <button class="exms-filter-btn" type="button"><?php echo __( 'Filter', 'exms' ); ?></button>
    </div>
    <div class="exms-quiz-list">
        <!-- Quiz Detail -->
        <div class="exms-card">
            <h3 class="exms-card-header">
                <span class="exms-card-header-icon">
                    <span class="dashicons dashicons-dashboard"></span>
                </span>
                <?php echo __( 'Attempted Quizzes', 'exms' ); ?>
            </h3>
            <div id="exms-quiz-lists">
                <div class="exms-quiz-item">
                    <div class="exms-quiz-detail-wrapper">
                        <div class="exms-student-info">
                        <div>
                            <div class="exms-quiz-title"></div>
                                <div>
                                    <h2 class="exms-quiz-title-heading"></h2>
                                    <p class="exms-quiz-title" id="exms-quiz-review-detail" style="font-weight: 500;">
                                    </p>
                                </div>
                                <div class="exms-time-taken"> <span class="dashicons dashicons-clock"></span><span ></span>
                                </div>
                            </div>
                        </div>
                        <button class="exms-more-details-btn"></button>
                    </div>
                </div>
            </div>
        <!-- Questions details -->
        <div class="exms-card exms-quiz-question-list">

        </div>        
        </div>
    </div>
</div>

<!-- PopUp review question -->
<div class="exms-overlay" id="popup">
  <div class="exms-popup-card">
    <div class="exms-popup-header">
      <h3><?php echo __( 'Review Answer', 'exms' ); ?></h3>
      <button class="exms-close-btn exms-pop-close-btn" >×</button>
    </div>
    <div class="exms-popup-body">
      <label for="exms-remarks"><?php echo __( 'Remarks:', 'exms' ); ?></label>
      <input type="text" id="exms-remarks" placeholder="Enter remarks" />

      <label for="exms-points"><?php echo __( 'Points:', 'exms' ); ?></label>
      <input type="number" id="exms-points" placeholder="Enter points" />
    </div>
    <div class="exms-popup-footer">
      <button class="exms-reject-btn exms-pop-close-btn"><?php echo __( 'Reject', 'exms' ); ?></button>
      <button class="exms-accept-btn"><?php echo __( 'Accept', 'exms' ); ?></button>
    </div>
  </div>
</div>
