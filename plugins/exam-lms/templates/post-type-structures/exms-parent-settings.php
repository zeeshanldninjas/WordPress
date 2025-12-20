<?php

/**
 * Template for Parent post settings
 */

if( ! defined( 'ABSPATH' ) ) exit;
     
$hide_class = 'exms-hide';
$show_class = 'exms-show';

$existing_result = EXMS_Post_Relations::$existing_parent_result;

if ( !empty( $existing_result ) && isset( $existing_result[0] ) ) {
    $data = $existing_result[0];
} else {
    $data = null;
}

$post_id = isset( $post->ID ) ? $post->ID : 0;
$post_type = get_post_type( $post_id );

if ( $post_type ) {
    $post_type_obj = get_post_type_object( $post_type );
    if ( $post_type_obj ) {
        $post_name = $post_type_obj->labels->singular_name;
    }
}
$purchase_type = isset( $data->parent_post_type ) ? $data->parent_post_type : 'free';
$price = isset( $data->parent_post_price ) ? $data->parent_post_price : 0;
$subscription = isset( $data->subscription_days ) ? $data->subscription_days : 0;
$close_url = isset( $data->redirect_url ) ? $data->redirect_url : '';

$exms_points = isset( $data->parent_achievement_points ) ? $data->parent_achievement_points : 0;
$seat_limit = isset( $data->seat_limit ) ? $data->seat_limit : 0;
$subscription_field = $purchase_type == 'subscribe' ? 'exms-show' : '';
$close_field = $purchase_type == 'close' ? 'exms-show' : '';
$display_price_field = $purchase_type == 'paid' || $purchase_type == 'subscribe' ? 'exms-show' : '';

$stripe_settings = Exms_Core_Functions::get_options( 'payment_settings' );
$stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
$paypal_on = isset( $stripe_settings['paypal_enable'] ) ? $stripe_settings['paypal_enable'] : 'off';
$progress_type = isset( $data->progress_type ) ? $data->progress_type : '';

?>

<div class="exms-setting-tab-wrapper">
    <div class="exms-tab-button">     
        <?php 
        if ( $post_type === 'exms-courses' ) {
        ?>  
        <button type="button" class="exms-tab-title exms-active-tab" value="quiz-type"><span class="dashicons dashicons-tag exms-icon"></span><span><?php echo ucwords( $post_name ). __( ' Type', 'exms'); ?></span></button>
        <button type="button" class="exms-tab-title" value="progress-settings">
            <span class="dashicons dashicons-chart-bar exms-icon"></span>
            <span><?php echo __( 'Progress Settings', 'exms' ); ?></span>
        </button>
        <button type="button" class="exms-tab-title" value="quiz-achivement"><span class="dashicons dashicons-awards exms-icon"></span><span><?php echo ucwords( $post_name ). __( ' Achievements', 'exms'); ?></span></button>
        <?php } ?>
        <button type="button" class="exms-tab-title <?php echo $post_type !== 'exms-courses' ? 'exms-active-tab' : ''; ?>" value="course-video">
            <span class="dashicons dashicons-video-alt3 exms-icon"></span>
            <span><?php echo $post_name . __( ' Video', 'exms' ); ?></span>
        </button>
    </div>
    

    <div class="exms-tab-content">
        <?php 
        if ( $post_type === 'exms-courses' ) {
        ?>
        <div class="exms-quiz-type-content">
            <?php 
			$disabled = ($stripe_on == 'off' && $paypal_on == 'off') ? 'disabled' : ''; 
			$disable = ($purchase_type == 'free' || $purchase_type == 'close') ? 'disabled' : ''; 
            if ( !self::$instance->table_check ) {
            ?>
                    <div class="exms-row exms-quiz-settings-row">
                    <?php
                    $ajax_action = 'create_exms_post_structure_table';
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
                            <?php echo ucwords( $post_name ).__( ' Seat Limit', 'exms'); ?>
                        </div>
                        <div class="exms-quiz-point">
                            <input type="number" class="" name="exms_<?php echo $post_name; ?>_seat_limit" value="<?php echo $seat_limit; ?>" placeholder="<?php echo __( 'Seat Limit For ', 'exms') .$post_name; ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quiz types -->
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php echo ucwords( $post_name ) .__( ' Type', 'exms'); ?>
                </div>
                <div class="exms-data">
                    <div class="wpeq-quiz-type-group">
    
                        <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb1" value="free" <?php echo $purchase_type == 'free' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb1">
                            <?php _e( 'Free', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb2" value="paid" <?php echo $purchase_type == 'paid' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb2">
                            <?php _e( 'Paid', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb3" value="subscribe" <?php echo $purchase_type == 'subscribe' ? 'checked="checked"' : ''; ?> />
                        <label class='custom_radio_label' for="rb3">
                            <?php _e( 'Subscribe', 'exms'); ?>
                        </label>

                        <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb4" value="close" <?php echo $purchase_type == 'close' ? 'checked="checked"' : ''; ?> />
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
                                    <input type="number" min="0" class="settings_input_field exms-main-field exms-price-field" name="exms_<?php echo $post_name; ?>_price" value="<?php echo $price; ?>" placeholder="<?php _e( 'Price for quiz', 'exms'); ?>" />
                                </div>
                            </div>
                            <div class="exms-subs-row <?php echo $subscription_field; ?>">
                                <div class="exms-sub-title exms-remove-padding">
                                    <?php _e( 'Subscription Days', 'exms'); ?>
                                </div>
                                <div class="exms-subscription">  
                                    <input type="number" min="0"  class="settings_input_field exms-main-field exms-subscription-field" name="exms_<?php echo $post_name; ?>_sub_days" value="<?php echo $subscription; ?>" placeholder="<?php _e( 'Valid for X days', 'exms'); ?>" />
                                </div>
                            </div>
                        <div class="exms-close-row <?php echo $close_field; ?>">
                            <div class="exms-sub-title exms-remove-padding">
                                <?php _e( 'Enter Redirect URL', 'exms'); ?>
                            </div>
                            <div class="exms-quiz-close">    
                                <input type="url" class="settings_input_field exms-main-field exms-close-field" name="exms_<?php echo $post_name; ?>_close_url" value="<?php echo $close_url; ?>" placeholder="<?php _e( 'Enter a valid URL', 'exms'); ?>" />
                            </div>
                        </div>    
                <p class="exms-instruction-message">Set type of <?php echo $post_name ?></p>
            </div>
        </div>
        <!-- General setting -->
        <div class="exms-progress-type-content">
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php _e( ucwords( $post_name ).' Progress Option', 'exms' ); ?>
                </div>
                <div class="toggle-switch wpeq-course-progress-switch">
                    <input type="radio" 
                           class="toggle_radio wpeq_course_progress" 
                           name="exms_<?php echo esc_attr( $post_name ); ?>_progress_type" 
                           id="progress_auto_on_<?php echo esc_attr( $post_name ); ?>" 
                           value="on" 
                           <?php echo $progress_type == 'on' ? 'checked' : ''; ?> />
                    <label class="toggle_label" for="progress_auto_on_<?php echo esc_attr( $post_name ); ?>">
                        <?php _e( 'On', 'exms' ); ?>
                    </label>

                    <input type="radio" 
                           class="toggle_radio wpeq_course_progress" 
                           name="exms_<?php echo esc_attr( $post_name ); ?>_progress_type" 
                           id="progress_auto_off_<?php echo esc_attr( $post_name ); ?>" 
                           value="off" 
                           <?php echo $progress_type == 'off' || $progress_type == '' ? 'checked' : ''; ?> />
                    <label class="toggle_label" for="progress_auto_off_<?php echo esc_attr( $post_name ); ?>">
                        <?php _e( 'Off', 'exms' ); ?>
                    </label>
                </div>

                <div class="exms-instruction-message">
                    <?php _e( 'When enabled (On), completing a child item will also mark its parent as complete automatically. When disabled (Off), only the child item will be marked complete.', 'exms' ); ?>
                </div>
            </div>
        </div>
        <!-- general settings -->
        <div class="exms-quiz-achievement-content">
        
            <!-- Award points -->
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-data">
                
                    <div class="exms-quiz-points-row">
                        <div class="exms-sub-title exms-remove-padding">
                            <?php echo ucwords( $post_name ).__( ' Point', 'exms'); ?>
                        </div>
                        <div class="exms-quiz-point">
                            <input type="number" class="wpeq-quiz-settings-input-field exms_quiz_points" name="exms_<?php echo $post_name; ?>_points" value="<?php echo $exms_points; ?>" placeholder="<?php echo __( 'Point for ', 'exms') .$post_name; ?>" />
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Award points -->

            <?php 
            /**
             * Assign badges into quiz
             */
            //wp_exams()->selector->exms_create_multiple_tags( 'exms_badges', $post_id, 'exms_attached_badges', 'badges' );
            ?>
        </div>
        <?php } ?>
        <div class="exms-course-video-content">
            <div class="exms-row exms-quiz-settings-row">
                <div class="exms-title">
                    <?php _e( ucwords( $post_name ).' Video Link', 'exms' ); ?>
                </div>
                <div class="exms-data">
                    <?php
                        $video_url = isset( $data->video_url ) ? esc_url( $data->video_url ) : '';
                    ?>
                    <input type="url"
                           class="settings_input_field exms-main-field exms-video-field"
                           name="exms_<?php echo $post_name; ?>_video_url"
                           value="<?php echo $video_url; ?>"
                           placeholder="<?php _e( 'Enter YouTube/Vimeo video link', 'exms' ); ?>" />
                </div>
                <p class="exms-instruction-message">
                    <?php _e( 'If you want to replace the course image with a video, please enter the video link here. Leave it empty if you want to display the course image instead.', 'exms' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>