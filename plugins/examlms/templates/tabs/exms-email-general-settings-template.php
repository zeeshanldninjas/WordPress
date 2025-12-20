<?php
/**
 * Manage Email settings
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create general email setting html
 */
$general_settings = Exms_Core_Functions::get_options( 'settings' );
$email_logo = isset( $general_settings['exms_email_logo_url'] ) ? $general_settings['exms_email_logo_url'] : ''; 
$from_name = isset( $general_settings['exms_email_from_name'] ) ? $general_settings['exms_email_from_name'] : ''; 
$from_address = isset( $general_settings['exms_email_from_address'] ) ? $general_settings['exms_email_from_address'] : ''; 
$footer_text = isset( $general_settings['exms_email_footer_text'] ) ? $general_settings['exms_email_footer_text'] : '"'.__( 'Learndash - Powered by Ldninjas', 'exms' ).'"'; 
$image_id = rand( 10, 10000 );
$email_logo_url = isset( $general_settings['exms_email_logo_url'] ) ? $general_settings['exms_email_logo_url'] : '';

$upload_button = '';
$remove_button = '';
$get_image = '';

if( $email_logo_url && 'undefined_images' != $email_logo_url ) {

    $upload_button = 'style="display: none"';
    $remove_button = 'style="display: inherit"';
    $get_image = '<img class="exms-display-image" src="'.$email_logo_url.'">';

} else {

    $upload_button = 'style="display: inline"';
    $remove_button = 'style="display: none"';
} ?>    


<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">
        <!-- General setting logo container -->
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'Email Logo :' ); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input placeholder="<?php _e( 'Upload or paste URL of the logo', 'exms' ); ?>" type="text" class="exms-hidden-badges-image-<?php echo $image_id; ?>" name="exms_email_logo_url" value="<?php echo $email_logo; ?>">
                <p class="exms-instruction-message"><?php _e( 'Upload or paste the URL of the logo to be displayed at the top of the email.' ); ?></p>
                <div class="exms-display-upload-btn">
                    <button data-post_id="<?php echo $image_id; ?>" <?php echo $upload_button; ?> class="exms-set-setting-image" type="button">
                        <?php _e( 'Upload Logo', 'exms' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="exms-settings-row exms-email-logo-wrap" <?php echo $remove_button; ?> >
            <div class="exms-setting-lable">
                <label></label>
            </div>
            <div class="exms-setting-data">
                <div class="exms-save-image-value exms-save-image-value-<?php echo $image_id; ?>">
                    <?php echo $get_image; ?>
                    <button <?php echo $remove_button; ?> data-post_id="<?php echo $image_id; ?>" class="exms-remove-image button-secondary" type="button">
                        <?php _e( 'Remove Logo', 'exms' ); ?>
                    </button>
                </div>
            </div>   
        </div>
        <!-- End general setting logo container -->

        <!-- General setting other email content -->
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'From Name :' ); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" placeholder="<?php _e( 'Enter Form Name', 'exms' ); ?>" name="exms_email_from_name" value="<?php echo $from_name; ?>">
                <p class="exms-instruction-message"><?php _e( 'Name to display as sender.' ); ?></p>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'From Address :' ); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" placeholder="<?php _e( 'Enter Form Address', 'exms' ); ?>" name="exms_email_from_address" value="<?php echo $from_address; ?>">
                <p class="exms-instruction-message"><?php _e( 'Text to be shown at email footer.' ); ?></p>
            </div>
        </div>
        <div class="exms-email-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'Footer Text :' ); ?></label>
            </div>
            <div class="exms-email-setting-data">
                <input type="text" placeholder="<?php _e( 'Enter Footer text', 'exms' ); ?>" name="exms_email_footer_text" value="<?php echo $footer_text; ?>">
                <p class="exms-instruction-message"><?php _e( 'Text to be shown at email footer.' ); ?></p>
            </div>
        </div>
        <!-- End general setting other email content -->
    </div>
</div>