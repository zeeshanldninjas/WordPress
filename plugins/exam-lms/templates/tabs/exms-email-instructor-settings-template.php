<?php

if (! defined('ABSPATH')) exit;

?>

<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($assign_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($assign_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_instructor_assign" id="rb8" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb8"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_instructor_assign" id="rb9" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb9"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" name="exms_instructor_assign_sub" value="<?php echo $assign_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Assign Group :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($assign_quiz_content, 'exms_instructor_assign_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
    <?php echo exms_create_email_tags(); ?>
</div>
<!-- End instructor email setting html -->

<!-- Instructor unassgin email setting html -->
<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($unassign_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($unassign_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_instructor_unassign" id="rb10" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb10"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_instructor_unassign" id="rb11" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb11"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" name="exms_instructor_unassign_sub" value="<?php echo $unassign_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Unassign Group :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($unassign_quiz_content, 'exms_instructor_unassign_content', ['textarea_rows' => 6]); ?>
            </div>
        </div>
    </div>
</div>