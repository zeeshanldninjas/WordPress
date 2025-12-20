<?php
/**
 * Display stripe settings
 */
if( ! defined( 'ABSPATH' ) ) exit;

$stripe_settings = Exms_Core_Functions::get_options( 'payment_settings' );
$stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
$sandbox = isset( $stripe_settings['stripe_sandbox'] ) ? $stripe_settings['stripe_sandbox'] : 'off';
$redirect_url = isset( $stripe_settings['stripe_redirect_url'] ) ? $stripe_settings['stripe_redirect_url'] : '';
$currency = isset( $stripe_settings['stripe_currency'] ) ? $stripe_settings['stripe_currency'] : '';
$vender_email = isset( $stripe_settings['stripe_vender_email'] ) ? $stripe_settings['stripe_vender_email'] : '';
$stripe_api = isset( $stripe_settings['stripe_api_key'] ) ? $stripe_settings['stripe_api_key'] : '';
$client_secret = isset( $stripe_settings['stripe_client_secret'] ) ? $stripe_settings['stripe_client_secret'] : '';

?>
<div class="exms-email-settings-wrap form-table">
    <div class="exms-settings-container">

    	<!-- display mode -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child2">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Stripe Payment :', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data">
                    <!-- <label class="switch">
                        <input type="checkbox" <?php echo $stripe_on == 'on' ? 'checked="checked"' : ''; ?> class="wpeq-checkbox standard exms-stripe-enable wpeq-input-field" name="exms_stripe_enable"/>
                        <span class="slider round"></span>
                    </label> -->
                    <div class="payment-toggle-switch">
                        <input type="radio" class="wpeq-checkbox standard exms-stripe-enable wpeq-input-field toggle_radio" name="exms_stripe_enable" id="rb21" value="on" <?php echo $stripe_on == 'on' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb21">
                            <?php _e( 'On', 'exms' ); ?>
                        </label>

                        <input type="radio" class="wpeq-checkbox standard exms-stripe-enable wpeq-input-field toggle_radio" name="exms_stripe_enable" id="rb22" value="off" <?php echo $stripe_on == 'off' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb22">
                            <?php _e( 'Off', 'exms' ); ?>
                        </label>
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Enable stripe payment option.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child2">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Sandbox :', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data">
                    <!-- <label class="switch">
                        <input type="checkbox" <?php echo $sandbox == 'on' ? 'checked="checked"' : ''; ?> class="wpeq-checkbox standard exms-stripe-sandbox wpeq-input-field" name="exms_stripe_sandbox"/>
                        <span class="slider round"></span>
                    </label> -->
                    <div class="payment-toggle-switch">
                        <input type="radio" class="toggle_radio exms-stripe-sandbox" name="exms_stripe_sandbox" id="rb25" value="on" <?php echo $sandbox == 'on' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb25">
                            <?php _e( 'On', 'exms' ); ?>
                        </label>

                        <input type="radio" class="toggle_radio exms-stripe-sandbox" name="exms_stripe_sandbox" id="rb26" value="off" <?php echo $sandbox == 'off' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb26">
                            <?php _e( 'Off', 'exms' ); ?>
                        </label>
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Sandbox Testing.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End display mode -->

        <!-- Redirect URL -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Complete URL', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <div class="wpeq-checkout-mode-select-div">
                        <input type="text" class="wpeq-input-field exms-stripe-redirects-complete wpeq-input-field" name="exms_stripe_redirects[complete_url]" placeholder="<?php _e( 'Redirect on payment successfully completed', 'exms' ); ?>" value="<?php echo isset( $redirect_url['complete_url'] ) ? $redirect_url['complete_url'] : ''; ?>"  />
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Redirect on payment successfully completed.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End Redirect URL -->

        <!-- Redirect URL -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Cancel URL', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <div class="wpeq-checkout-mode-select-div">
                        <input type="text" class="wpeq-input-field exms-stripe-redirects-cancel wpeq-input-field" name="exms_stripe_redirects[cancel_url]" placeholder="<?php _e( 'Redirect on payment successfully completed', 'exms' ); ?>" value="<?php echo isset( $redirect_url['cancel_url'] ) ? $redirect_url['cancel_url'] : ''; ?>" />
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Redirect on payment successfully completed.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End Redirect URL -->

        <!-- Paypal Currency -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Currency Code', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <div class="wpeq-checkout-mode-select-div">
                        <input type="text" class="wpeq-input-field exms-stripe-currency" name="exms_stripe_currency" placeholder="<?php _e( 'Stripe currency code e.g. USD', 'exms' ); ?>" value="<?php echo $currency; ?>" />
                    <br>
                    </div>
                    <div class="exms-display-link-text">
                        <p class="exms-instruction-message"><?php _e( 'For list of stripe supported currencies see.', 'exms' ); ?></p>
                        <a class="exms-active-sub-tab" href="https://stripe.com/docs/currencies"><?php _e( 'stripe currencies', 'exms' ); ?></a>  
                    </div>
                </div>
            </div>
        </div>
        <!-- End Paypal Currency -->

        <!-- Payment Email -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Stripe Email Address', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="email" class="wpeq-input-field exms-stripe-payee-email" name="exms_stripe_payee_email" placeholder="<?php _e( 'Vendor email', 'exms' ); ?>" value="<?php echo $vender_email; ?>" />
                    <p class="exms-instruction-message"><?php _e( 'Enter vender email address.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End Payment Email -->
        <div class="exms-settings-row">
            <label><?php _e( '<b>Note:</b> Please, fill in all of the mandatory fields.', 'exms' ); ?></label>
        </div>
    </div>
</div>