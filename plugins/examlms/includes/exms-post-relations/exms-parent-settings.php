<?php

/**
 * Template for Parent post settings
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Parent_Settings {

    /**
     * @var self
     */
    private static $instance;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Parent_Settings ) ) {

            self::$instance = new EXMS_Parent_Settings;

            global $wpdb;
            self::$wpdb = $wpdb;
        }

        return self::$instance;
    }

    /**
     * Parent post setting html
     * 
     * @param $post
     */
    public static function exms_parent_post_setting_html( $post ) {
        
        $hide_class = 'exms-hide';
        $show_class = 'exms-show';

        $post_id = isset( $post->ID ) ? $post->ID : 0;
        $post_type = get_post_type( $post_id );
        $post_name = str_replace( 'exms-', '', $post_type );
        $options = exms_get_post_options( $post_id );

        $sign_up = isset( $options['exms_'.$post_name.'_sign_up'] ) ? $options['exms_'.$post_name.'_sign_up'] : '';
        $purchase_type = isset( $options['exms_'.$post_name.'_type'] ) ? $options['exms_'.$post_name.'_type'] : 'free';
        $price = isset( $options['exms_'.$post_name.'_price'] ) ? $options['exms_'.$post_name.'_price'] : 0;
        $subscription = isset( $options['exms_'.$post_name.'_sub_days'] ) ? $options['exms_'.$post_name.'_sub_days'] : 0;
        $close_url = isset( $options['exms_'.$post_name.'_close_url'] ) ? $options['exms_'.$post_name.'_close_url'] : '';
        $subscription_field = $purchase_type == 'subscribe' ? 'exms-show' : '';
        $close_field = $purchase_type == 'close' ? 'exms-show' : '';
        $display_price_field = $purchase_type == 'paid' || $purchase_type == 'subscribe' ? 'exms-show' : '';
        $exms_points = isset( $options['exms_'.$post_name.'_points'] ) ? $options['exms_'.$post_name.'_points'] : '';

        $stripe_settings = Exms_Core_Functions::get_options( 'settings' );
        $paypal_settings = Exms_Core_Functions::get_options( 'settings' );
        $stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
        $paypal_on = isset( $paypal_settings['paypal_enable'] ) ? $paypal_settings['paypal_enable'] : 'off';
        
        ?>
        <div class="exms-setting-tab-wrapper">
            <div class="exms-tab-button">        
                <button type="button" class="exms-tab-title exms-active-tab" value="quiz-type"><span class="dashicons dashicons-tag exms-icon"></span><span><?php echo ucwords( $post_name ). __( ' Type', 'exms' ); ?></span></button>
                <button type="button" class="exms-tab-title" value="quiz-achivement"><span class="dashicons dashicons-awards exms-icon"></span><span><?php echo ucwords( $post_name ). __( ' Achievements', 'exms' ); ?></span></button>
            </div>

            <div class="exms-tab-content">
                <div class="exms-quiz-type-content">
                    <?php if( $stripe_on == 'on' || $paypal_on == 'on' ) { ?>
                        <!-- Sign up fee -->
                        <div class="exms-row exms-quiz-settings-row">
                            <div class="exms-title">
                                <?php _e( 'Sign up Fee', 'exms' ); ?>
                            </div>

                            <div class="exms-data quiz_sign_up">  
                                <input type="number" min="0" class="wpeq_quiz_sign settings_input_field exms-main-field" name="exms_<?php echo $post_name; ?>_sign_up"  placeholder="<?php echo __( 'Sign up Fee', 'exms' ); ?>" value="<?php echo $sign_up; ?>"/>
                                <?php exms_add_info_title( 'Add quiz sign up fee.' ); ?>
                            </div>
                        </div>
                    <?php } ?>
                    <!-- Sign up fee -->

                    <!-- Quiz types -->
                    <div class="exms-row exms-quiz-settings-row">
                        <div class="exms-title">
                            <?php echo ucwords( $post_name ) .__( ' Type', 'exms' ); ?>
                        </div>
                        <div class="exms-data">

                            <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb1" value="free" <?php echo $purchase_type == 'free' ? 'checked="checked"' : ''; ?> />
                            <label class='custom_radio_label' for="rb1">
                                <?php _e( 'Free', 'exms' ); ?>
                            </label>

                            <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb2" value="paid" <?php echo $purchase_type == 'paid' ? 'checked="checked"' : ''; ?> />
                            <label class='custom_radio_label' for="rb2">
                                <?php _e( 'Paid', 'exms' ); ?>
                            </label>

                            <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb3" value="subscribe" <?php echo $purchase_type == 'subscribe' ? 'checked="checked"' : ''; ?> />
                            <label class='custom_radio_label' for="rb3">
                                <?php _e( 'Subscribe', 'exms' ); ?>
                            </label>

                            <input type="radio" class="custom_radio exms_purchase_type" name="exms_<?php echo $post_name; ?>_type" id="rb4" value="close" <?php echo $purchase_type == 'close' ? 'checked="checked"' : ''; ?> />
                            <label class='custom_radio_label' for="rb4">
                                <?php _e( 'Close', 'exms' ); ?>
                            </label>
                            <?php if( $stripe_on == 'on' || $paypal_on == 'on' ) { ?>
                                <div class="exms-price-row <?php echo $display_price_field; ?>">
                                    <div class="exms-sub-title exms-remove-padding">
                                        <?php _e( 'Price', 'exms' ); ?>
                                    </div>
                                    <div class="exms-quiz-price">
                                        <input type="number" min="0" class="settings_input_field exms-main-field exms-price-field" name="exms_<?php echo $post_name; ?>_price" value="<?php echo $price; ?>" placeholder="<?php _e( 'Price for quiz', 'exms' ); ?>" />
                                    </div>
                                </div>
                                <div class="exms-subs-row <?php echo $subscription_field; ?>">
                                    <div class="exms-sub-title exms-remove-padding">
                                        <?php _e( 'Subscription Days', 'exms' ); ?>
                                    </div>
                                    <div class="exms-subscription">  
                                        <input type="number" min="0"  class="settings_input_field exms-main-field exms-subscription-field" name="exms_<?php echo $post_name; ?>_sub_days" value="<?php echo $subscription; ?>" placeholder="<?php _e( 'Valid for X days', 'exms' ); ?>" />
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="exms-close-row <?php echo $close_field; ?>">
                                <div class="exms-sub-title exms-remove-padding">
                                    <?php _e( 'Enter Redirect URL', 'exms' ); ?>
                                </div>
                                <div class="exms-quiz-close">    
                                    <input type="url" class="settings_input_field exms-main-field exms-close-field" name="exms_<?php echo $post_name; ?>_close_url" value="<?php echo $close_url; ?>" placeholder="<?php _e( 'Enter a valid URL', 'exms' ); ?>" />
                                </div>
                            </div>
                            <?php exms_add_sub_info_title( 'Set type of '.$post_name ); ?>

                        </div>
                    </div>
                </div>

                <div class="exms-quiz-achievement-content">
                
                    <!-- Award points -->
                    <div class="exms-row exms-quiz-settings-row">
                        <div class="exms-data">
                        
                            <div class="exms-quiz-points-row">
                                <div class="exms-sub-title exms-remove-padding">
                                    <?php echo ucwords( $post_name ).__( ' Point', 'exms' ); ?>
                                </div>
                                <div class="exms-quiz-point">
                                    <input type="number" class="wpeq-quiz-settings-input-field exms_quiz_points" name="exms_<?php echo $post_name; ?>_points" value="<?php echo $exms_points; ?>" placeholder="<?php echo __( 'Point for ', 'exms' ) .$post_name; ?>" />
                                </div>
                            </div>

                            <div class="exms-quiz-points-rows">
                                <?php WP_EXAMS_Point_Type::exms_get_all_point_type( $post_name, $options, true ); ?>
                            </div>

                            <?php
                            echo exms_add_sub_info_title( 'Award points for '.ucwords( $post_name ) ); 
                            ?>
                        </div>
                    </div>
                    <!-- /Award points -->

                    <?php 
                    /**
                     * Assign badges into quiz
                     */
                    wp_exams()->selector->exms_create_multiple_tags( 'exms_badges', $post_id, 'exms_attached_badges', 'badges' );
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}

EXMS_Parent_Settings::instance();