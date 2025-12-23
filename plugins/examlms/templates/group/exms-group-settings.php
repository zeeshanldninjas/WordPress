<?php

/**
 * Quiz settings content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$hide_class = 'exms-hide';
$show_class = 'exms-show';

$existing_labels = Exms_Core_Functions::get_options('labels');
$stripe_settings = Exms_Core_Functions::get_options( 'payment_settings' );
$stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
$paypal_on = isset( $stripe_settings['paypal_enable'] ) ? $stripe_settings['paypal_enable'] : 'off';
$group_singular = '';
if ( is_array( $existing_labels ) && array_key_exists( 'exms_qroup', $existing_labels ) ) {
    $group_singular = $existing_labels['exms_qroup'];
}
$subscription_days = $group_type == 'subscribe' ? 'exms-show' : '';
$close_field = $group_type == 'close' ? 'exms-show' : '';
$display_price_field = $group_type == 'paid' || $group_type == 'subscribe' ? 'exms-show' : '';

?>
<div class="exms-group-setting-tab-wrapper">
	<div class="exms-tab-button">
		<button type="button" class="exms-tab-title exms-active-tab" value="group-type">
            <span class="dashicons dashicons-tag exms-icon"></span>
            <span><?php echo $group_singular . __( ' Type', 'exms' ); ?></span>
        </button>
		<button type="button" class="exms-tab-title" value="quiz-video-url">
            <span class="dashicons dashicons-video-alt3 exms-icon"></span>
            <span><?php echo $group_singular . __( ' Video', 'exms' ); ?></span>
        </button>
	</div>

	<div class="exms-tab-content">
		<div class="exms-group-type-content">
            <?php 
			$disabled = ($stripe_on == 'off' && $paypal_on == 'off') ? 'disabled' : ''; 
			$disable = ($group_type == 'free' || $group_type == 'close') ? 'disabled' : ''; 
            if ( !self::$instance->table_check ) {
            ?>
                    <div class="exms-row exms-quiz-settings-row">
                    <?php
                    $ajax_action = 'create_exms_group_table';
                    $table_names = $table_exists;
                    require EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
                    ?>
                    </div>
                    <?php
                }
                    ?>
            <div class="exms-row exms-quiz-settings-row">
                <?php 
                $settings_url = admin_url( 'admin.php?page=exms-settings&tab=payment-integration' );
                $message = ( $stripe_on == 'off' && $paypal_on == 'off' ) 
                    ? sprintf(
                        __('No payment method is enabled. <a href="%s" target="_blank">Click here</a> to configure.', 'exms'),
                        esc_url( $settings_url )
                    )
                    : "";
                ?>
                <?php if ( !empty( $message ) ) { ?>
                        <p class="exms-instruction-message"> 
                            <?php echo $message; ?>
                        </p>
            <?php } ?>
                <div class="exms-data">
                    <div class="exms-quiz-points-row">
                        <div class="exms-sub-title exms-remove-padding">
                            <?php echo ucwords( $group_singular ).__( ' Seat Limit', 'exms'); ?>
                        </div>
                        <div class="exms-quiz-point">
                            <input type="number" class="" name="exms_group_seat_limit" value="<?php echo $seat_limit; ?>" placeholder="<?php echo __( 'Seat Limit For ', 'exms') .$group_singular; ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz types -->
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php echo ucwords( $group_singular ) .__( ' Type', 'exms'); ?>
                </div>
                <div class="exms-data">
                    <div class="wpeq-quiz-type-group">
    
                        <input type="radio" class="custom_radio exms_group_type" name="exms_group_type" id="rb1" value="free" <?php echo $group_type == 'free' ? 'checked="checked"' : ''; ?> <?php echo $group_type == '' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb1">
                            <?php _e( 'Free', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_group_type" name="exms_group_type" id="rb2" value="paid" <?php echo $group_type == 'paid' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb2">
                            <?php _e( 'Paid', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_group_type" name="exms_group_type" id="rb3" value="subscribe" <?php echo $group_type == 'subscribe' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb3">
                            <?php _e( 'Subscribe', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_group_type" name="exms_group_type" id="rb4" value="close" <?php echo $group_type == 'close' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb4">
                            <?php _e( 'Close', 'exms'); ?>
                        </label>
                    </div>
                </div>
                            <div class="exms-price-row <?php echo $display_price_field; ?>">
                                <div class="exms-sub-title exms-remove-padding">
                                    <?php _e( 'Price', 'exms'); ?>
                                </div>
                                <div class="exms-quiz-price">
                                    <input type="number" min="0" class="settings_input_field exms-main-field exms-price-field" name="exms_group_price" value="<?php echo $group_price; ?>" placeholder="<?php _e( 'Price for quiz', 'exms'); ?>" />
                                </div>
                            </div>
                            <div class="exms-subs-row <?php echo $subscription_days; ?>">
                                <div class="exms-sub-title exms-remove-padding">
                                    <?php _e( 'Subscription Days', 'exms'); ?>
                                </div>
                                <div class="exms-subscription">  
                                    <input type="number" min="0"  class="settings_input_field exms-main-field exms-subscription-field" name="exms_group_sub_days" value="<?php echo $subscription; ?>" placeholder="<?php _e( 'Valid for X days', 'exms'); ?>" />
                                </div>
                            </div>
                        <div class="exms-close-row <?php echo $close_field; ?>">
                            <div class="exms-sub-title exms-remove-padding">
                                <?php _e( 'Enter Redirect URL', 'exms'); ?>
                            </div>
                            <div class="exms-quiz-close">    
                                <input type="url" class="settings_input_field exms-main-field exms-close-field" name="exms_group_close_url" value="<?php echo $redirect_url; ?>" placeholder="<?php _e( 'Enter a valid URL', 'exms'); ?>" />
                            </div>
                        </div>    
                <p class="exms-instruction-message">Set type of <?php echo $group_singular ?></p>
            </div>
        </div>
		<div class="exms-course-video-content">
			<?php
			if ( !self::$instance->table_check ) {
            ?>
				<div class="exms-row exms-quiz-settings-row">
				<?php
				$ajax_action = 'create_exms_group_table';
				$table_names = $table_exists;
				require EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
				?>
				</div>
				<?php
			}
				?>
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php _e( ucwords( $group_singular ).' Video Link', 'exms' ); ?>
                </div>
                <div class="exms-data">
                    <input type="url"
                           class="settings_input_field exms-main-field exms-video-field"
                           name="exms_group_video_url"
                           value="<?php echo $group_video_url; ?>"
                           placeholder="<?php _e( 'Enter YouTube/Vimeo video link', 'exms' ); ?>" />
                </div>
                <p class="exms-instruction-message">
                    <?php _e( 'If you want to replace the quiz image with a video, please enter the video link here. Leave it empty if you want to display the course image instead.', 'exms' ); ?>
                </p>
            </div>
        </div>
	</div>
</div>