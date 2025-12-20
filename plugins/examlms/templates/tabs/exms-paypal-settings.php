<?php
/**
 * Display paypal settings content
 */
if( ! defined( 'ABSPATH' ) ) exit;

$paypal_settings = Exms_Core_Functions::get_options( 'payment_settings' );
$paypal_on = isset( $paypal_settings['paypal_enable'] ) ? $paypal_settings['paypal_enable'] : 'off';
$sandbox = isset( $paypal_settings['paypal_sandbox'] ) ? $paypal_settings['paypal_sandbox'] : 'off';
$transaction_mode = isset( $paypal_settings['paypal_transaction_mode'] ) ? $paypal_settings['paypal_transaction_mode'] : '';
$checkout_modes = isset( $paypal_settings['checkour_mode'] ) ? $paypal_settings['checkour_mode'] : '';
$redirect_url = isset( $paypal_settings['paypal_redirect_url'] ) ? $paypal_settings['paypal_redirect_url'] : '';
$currency = isset( $paypal_settings['paypal_currency'] ) ? $paypal_settings['paypal_currency'] : '';
$vender_email = isset( $paypal_settings['paypal_vender_email'] ) ? $paypal_settings['paypal_vender_email'] : '';
$client_id = isset( $paypal_settings['paypal_client_id'] ) ? $paypal_settings['paypal_client_id'] : '';

?>
<div class="exms-email-settings-wrap form-table exms-paypal-settings-parent" data-payment="<?php echo esc_attr(json_encode($paypal_settings)); ?>">
    <div class="exms-settings-container">

    	<!-- Paypal peyment -->
    	<div class="exms-settings-row">
            <div class="exms-settings-row-child2">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Paypal Payment :', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data">
                    <div class="payment-toggle-switch">
                        <input type="radio" class="toggle_radio exms-paypal-enable" name="exms_paypal_enable" id="rb30" value="on" <?php echo $paypal_on == 'on' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb30">
                            <?php _e( 'On', 'exms' ); ?>
                        </label>

                        <input type="radio" class="toggle_radio exms-paypal-enable" name="exms_paypal_enable" id="rb31" value="off" <?php echo $paypal_on == 'off' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb31">
                            <?php _e( 'Off', 'exms' ); ?>
                        </label>
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Enable payment mode.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
    	
        <div class="exms-settings-row">
            <div class="exms-settings-row-child2">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Sandbox:', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data">
                    <div class="payment-toggle-switch">
                        <input type="radio" class="toggle_radio exms-paypal-sandbox" name="exms_paypal_sandbox" id="rb10" value="on" <?php echo $sandbox == 'on' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb10">
                            <?php _e( 'On', 'exms' ); ?>
                        </label>

                        <input type="radio" class="toggle_radio exms-paypal-sandbox" name="exms_paypal_sandbox" id="rb11" value="off" <?php echo $sandbox == 'off' ? 'checked="checked"' : ''; ?> />
                        <label class='toggle_label' for="rb11">
                            <?php _e( 'Off', 'exms' ); ?>
                        </label>
                    </div>
                    <p class="exms-instruction-message"><?php _e( 'Sandbox Testing.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End paypal payment -->

        <!-- Transaction mode --> <?php /*
        <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'Transaction Mode :', 'exms' ); ?></label>
            </div>
            <div class="exms-setting-data">
                <input type="radio" class="custom_radio" name="exms_paypal_transaction_mode" id="rb3" value="live" <?php echo $transaction_mode && $transaction_mode == 'live' ? 'checked' : ''; ?> />
				<label class='custom_radio_label' for="rb3">
					<?php _e( 'Live', 'exms' ); ?>
				</label>

				<input type="radio" class="custom_radio" name="exms_paypal_transaction_mode" id="rb4" value="sandbox" <?php echo $transaction_mode && $transaction_mode == 'sandbox' ? 'checked' : ''; ?> />
				<label class='custom_radio_label' for="rb4">
					<?php _e( 'Sandbox', 'exms' ); ?>
				</label>
            </div>
        </div>
        <!-- End transaction mod --> */?>

        <!-- Checkout Mode -->
        <!-- <div class="exms-settings-row">
            <div class="exms-setting-lable">
                <label><?php _e( 'Checkout Modes :', 'exms' ); ?></label>
            </div>
            <div class="exms-setting-data">
                <div class="wpeq-checkout-mode-select-div">
					<input type="checkbox" class="wpeq-checkbox2 standard exms-paypal-checkout-standard" name="exms_paypal_checkout_mode[]" value="standard" <?php echo $checkout_modes && in_array( 'standard', $checkout_modes ) ? 'checked="checked"' : ''; ?> />
					<b class="wpeq-payment-modes-select-label"><?php _e( 'Standard', 'exms' ); ?></b>

					<input type="checkbox" class="wpeq-checkbox2 exms-paypal-checkout-express" name="exms_paypal_checkout_mode[]" value="express" <?php echo $checkout_modes && in_array( 'express', $checkout_modes ) ? 'checked="checked"' : ''; ?> />
					<b class="wpeq-payment-modes-select-label"><?php _e( 'Express', 'exms' ); ?></b>
				</div>
                <p><?php exms_add_info_title( 'Select checkout modes.', 'exms' ); ?></p>
            </div>
        </div> -->
        <!-- End checkout Mode -->

        <!-- Redirect complete URL -->
		<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Complete URL', 'exms' );?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="text" class="wpeq-input-field exms-paypal-redirects-complete" name="exms_paypal_redirects[complete_url]" placeholder="<?php _e( 'Redirect on payment successfully completed', 'exms' ); ?>" value="<?php echo isset( $redirect_url['complete_url'] ) ? $redirect_url['complete_url'] : ''; ?>"  />
                    <p class="exms-instruction-message"><?php _e( 'Redirect on payment successfully completed.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End Redirect complete URL -->

        <!-- Redirect complete URL -->
		<div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Cancel URL', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="text" class="wpeq-input-field exms-paypal-redirects-cancel" name="exms_paypal_redirects[cancel_url]" placeholder="<?php _e( 'Redirect on payment successfully completed', 'exms' ); ?>" value="<?php echo isset( $redirect_url['cancel_url'] ) ? $redirect_url['cancel_url'] : ''; ?>" />
                    <p class="exms-instruction-message"><?php _e( 'Redirect on payment successfully completed.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End Redirect complete URL -->

        <!-- Paypal Currency -->
        <div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Currency Code', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="text" class="wpeq-input-field exms-paypal-currency" name="exms_paypal_currency" placeholder="<?php _e( 'Paypal currency code e.g. usd', 'exms' ); ?>" value="<?php echo $currency; ?>" />
                    <br>
                    <div class="exms-display-link-text">
                        <p class="exms-instruction-message"><?php _e( 'For list of paypal supported currencies see.', 'exms' ); ?></p>
                        <a class="exms-active-sub-tab" href="https://developer.paypal.com/docs/api/reference/currency-codes/"><?php _e( 'paypal currencies', 'exms' ); ?></a>  
                    </div>
                </div>
            </div>
        </div>
        <!-- End paypal Currency -->

        <!-- Payment email -->
        <div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Paypal Email Address', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="email" class="wpeq-input-field exms-paypal-payee-email" name="exms_paypal_payee_email" placeholder="<?php _e( 'Vendor email', 'exms' ); ?>" value="<?php echo $vender_email; ?>" />
                    <p class="exms-instruction-message"><?php _e( 'Enter Vendor email address.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End payment email -->

        <!-- PayPal Client ID -->
        <div class="exms-settings-row">
            <div class="exms-settings-row-child">
                <div class="exms-setting-lable">
                    <label><?php _e( 'PayPal Client ID', 'exms' ); ?></label>
                </div>
                <div class="exms-setting-data2">
                    <input type="text" class="wpeq-input-field exms-paypal-client-id" name="exms_paypal_client_id" placeholder="<?php _e( 'PayPal Client ID', 'exms' ); ?>" value="<?php echo $client_id; ?>" />
                    <p class="exms-instruction-message"><?php _e( 'Enter your PayPal Client ID from PayPal Developer Dashboard.', 'exms' ); ?></p>
                </div>
            </div>
        </div>
        <!-- End PayPal Client ID -->
        <div class="exms-settings-row2 ">
            <label>
                <?php _e( '<b>Note:</b> Please, fill in all of the mandatory fields.', 'exms' ); ?>
            </label>
        </div>
    </div>
</div>
