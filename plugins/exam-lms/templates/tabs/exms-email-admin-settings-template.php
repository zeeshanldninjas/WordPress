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
                $checked_on  = ($admin_quiz_option === 'yes') ? 'checked="checked"' : '';
                $checked_off = ($admin_quiz_option !== 'yes') ? 'checked="checked"' : '';
                ?>
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio" name="exms_admin" id="rb8" value="yes" <?php echo $checked_on; ?> />
                    <label class='toggle_label' for="rb8"><?php _e( 'On', 'exms' ); ?></label>

                    <input type="radio" class="toggle_radio" name="exms_admin" id="rb9" value="off" <?php echo $checked_off; ?> />
                    <label class='toggle_label' for="rb9"><?php _e( 'Off', 'exms' ); ?></label>
                </div>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Email Subject :', 'exms'); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input placeholder="<?php _e('Type your content here', 'exms'); ?>" type="text" name="exms_admin_sub" value="<?php echo $admin_quiz_subject; ?>">
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e('Quiz sold :', 'exms'); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <?php wp_editor($admin_quiz_content, 'exms_admin_content', ['textarea_rows' => 5]); ?>
            </div>
        </div>
    </div>
    <?php echo exms_create_email_tags(); ?>
</div>