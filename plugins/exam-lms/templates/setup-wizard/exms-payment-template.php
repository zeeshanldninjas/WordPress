<?php 

/** Payment template in setup wizard */
if( ! defined( 'ABSPATH' ) ) exit;

$settings = Exms_Core_Functions::get_options( 'payment_settings' );
$stripe_on = isset( $settings['stripe_enable'] ) ? $settings['stripe_enable'] : 'off';
$paypal_on = isset( $settings['paypal_enable'] ) ? $settings['paypal_enable'] : 'off';
?>
<div class="exms-setup-start exms-setup-payment exms-setup-p2">
    <div class="exms-payment-setup">
        <div class="exms-payment-setup-child">
            <img src="<?php echo EXMS_ASSETS_URL . 'imgs/stripe.png' ?>" alt="Stripe Logo">
            <p> <?php _e( 'Set up Stripe as your payment gateway for WP Exams. Secure <br> transactions and seamless integration await!', 'exms' ) ?></p>
        </div>
        <?php 
        if( $stripe_on == 'on' ) {
        ?>
            <div class="exms-payment-buttons exms-stripe-button" data-payment-method="exms-stripe"><?php _e( 'Enabled', 'exms' ); ?></div>
            <?php
        } else {
            ?>
            <div class="exms-payment-buttons exms-stripe-button" data-payment-method="exms-stripe"><?php _e( 'Configure', 'exms' ); ?></div>
        <?php
        }
        ?>
    </div>
    <div class="exms-payment-setup">
        <div class="exms-payment-setup-child">
            <img src="<?php echo EXMS_ASSETS_URL . 'imgs/paypal.png' ?>" alt="Paypal Logo">
            <p><?php _e( 'Enable PayPal payments for WP Exams. Let your users make <br> secure transactions with ease.', 'exms' ) ?></p>
        </div>
        <?php 
        if( $paypal_on == 'on' ) {
        ?>
        <div class="exms-payment-buttons exms-paypal-button" data-payment-method="exms-paypal"><?php _e( 'Enabled', 'exms' ); ?></div>
        <?php
        } else {
        ?>
            <div class="exms-payment-buttons exms-paypal-button" data-payment-method="exms-paypal"><?php _e( 'Configure', 'exms' ); ?></div>
        <?php
        }
        ?>
    </div>
</div>