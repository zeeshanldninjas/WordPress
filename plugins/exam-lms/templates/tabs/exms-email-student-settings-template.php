<?php

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($b_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($b_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_buying_checkbox" id="rb8" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb8"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_buying_checkbox" id="rb9" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb9"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" name="exms_buying_sub" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" value="<?php echo $b_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Buying Quiz:', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($b_quiz_content, 'exms_buying_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
    <?php echo exms_create_email_tags(); ?>
</div>
<!-- end student buying quiz email html -->

<!-- student passing quiz email html -->
<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($p_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($p_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_pass_checkbox" id="rb10" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb10"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_pass_checkbox" id="rb11" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb11"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" name="exms_pass_sub" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" value="<?php echo $p_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Passing Quiz :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($p_quiz_content, 'exms_passing_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
</div>
<!-- end student passing quiz email html -->

<!-- student failing quiz email html -->
<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($p_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($p_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_fail_checkbox" id="rb12" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb12"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_fail_checkbox" id="rb13" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb13"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" name="exms_fail_sub" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" value="<?php echo $f_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Failing Quiz:', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($f_quiz_content, 'exms_falling_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
</div>
<!-- end student failing quiz email html -->

<!-- student achivement quiz email html -->
<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <?php
                $checked_on  = ($p_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($p_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_achive_checkbox" id="rb14" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb14"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_achive_checkbox" id="rb15" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb15"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" name="exms_achive_sub" placeholder="<?php _e('Type your subject here', WP_EXAMS); ?>" value="<?php echo $a_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Achievements :', WP_EXAMS); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($a_quiz_content, 'exms_achievement_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
</div>