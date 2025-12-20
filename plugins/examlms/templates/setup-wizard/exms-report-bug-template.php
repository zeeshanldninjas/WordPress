<?php 

/**
 * Report Bug template in the setup wizard page
 */
if( ! defined( 'ABSPATH' ) ) exit;

$settings = Exms_Core_Functions::get_options( 'bug' );
$all_post_types = get_option( 'exms_post_types', [] );
$encoded_post_types = !empty($all_post_types) ? json_encode($all_post_types) : '{}';
$settings_json = !empty( $settings ) && is_array( $settings ) ? json_encode( $settings ) : '{}';
$bug  = isset( $settings[ 'dfce_lesson_enable' ] ) ?    $settings[ 'dfce_lesson_enable' ] : 'off';
?>
<div class="exms-setup-start exms-setup-report exms-setup-p5" data-bug='<?php echo esc_attr( $settings_json ); ?>' data-exms-post-types='<?php echo esc_attr( $encoded_post_types ); ?>'>
    <div class="exms-trake-code-option">
        <?php
        $checked_on  = ($bug === 'enable') ? 'checked="checked"' : '';
        $checked_off = ($bug === 'off') ? 'checked="checked"' : '';
        ?>
        <div class="toggle-switch">
            <input type="radio" class="toggle_radio wpeq-checkbox" name="dfce_lesson_enable" id="rb19" value="enable" <?php echo $checked_on; ?> />
            <label class='toggle_label' for="rb19"><?php _e( 'On', 'exms' ); ?></label>

            <input type="radio" class="toggle_radio wpeq-checkbox" name="dfce_lesson_enable" id="rb20" value="off" <?php echo $checked_off; ?> />
            <label class='toggle_label' for="rb20"><?php _e( 'Off', 'exms' ); ?></label>
        </div>
    </div>

    <!-- <div class="exms-track-desc"><?php echo __( 'Check this option for automatic track code error appear in future.. ', 'exms' ); ?></div> -->
    <div class="exms-track-desc"><?php echo __( 'Enable this button to report bugs automatically.', 'exms' ); ?></div>
    <p class="exms-track-note">
        <span class="dashicons dashicons-info"></span>
        <?php echo __( 'We recommend enabling the button to detect and report any bugs encountered during usage. It helps our team <br> address issues swiftly and improve your experience.', 'exms' ); ?>
    </p>
</div>