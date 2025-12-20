<?php

if( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="exms-payment-popup-overlay" class="exms-popup-overlay">
    <div id="exms-payment-popup" class="exms-popup-container">
        <button class="exms-close-popup">&times;</button>
        <h3 class="exms-popup-title"><?php echo __( 'Select Payment Method', 'exms' ); ?></h3>
        
        <div class="exms-tab-buttons">
            <button class="exms-tab active" data-target="paypal-content"><?php _e( 'PayPal', 'exms' ); ?></button>
            <button class="exms-tab" data-target="stripe-content"><?php _e( 'Stripe', 'exms' ); ?></button>
        </div>

        <div id="paypal-content" class="exms-tab-content">
            <form id="exms-paypal-form">
                <div class="exms-form-group">
                    <label for="paypal-name"><?php _e( 'Full Name', 'exms' ); ?></label>
                    <input type="text" id="paypal-name" name="paypal_name" required />
                </div>
                <div class="exms-form-group">
                    <label for="paypal-email"><?php _e( 'Email', 'exms' ); ?></label>
                    <input type="email" id="paypal-email" name="paypal_email" required />
                </div>
                <!-- Hidden fields for course data -->
                <input type="hidden" id="exms-course-id" name="course_id" value="" />
                <input type="hidden" id="exms-course-price" name="course_price" value="" />
                <input type="hidden" id="exms-course-title" name="course_title" value="" />
                <input type="hidden" id="exms-paypal-payee" name="paypal_payee" value="" />
                <!-- PayPal button container -->
                <div id="exms-paypal-button-container"></div>
            </form>
        </div>

        <div id="stripe-content" class="exms-tab-content" style="display: none;">
            <form id="exms-stripe-form">
                <div class="exms-form-group">
                    <label for="stripe-name"><?php _e( 'Full Name', 'exms' ); ?></label>
                    <input type="text" id="stripe-name" name="stripe_name" required />
                </div>
                <div class="exms-form-group">
                    <label for="stripe-email"><?php _e( 'Email', 'exms' ); ?></label>
                    <input type="email" id="stripe-email" name="stripe_email" required />
                </div>
                <div class="exms-form-group">
                    <label for="stripe-card"><?php _e( 'Card Number', 'exms' ); ?></label>
                    <input type="text" id="stripe-card" name="stripe_card" placeholder="<?php esc_attr_e( '1234 5678 9012 3456', 'exms' ); ?>" required />
                </div>
                <div class="exms-form-group">
                    <label for="stripe-expiry"><?php _e( 'Expiry Date', 'exms' ); ?></label>
                    <input type="text" id="stripe-expiry" name="stripe_expiry" placeholder="<?php esc_attr_e( 'MM/YY', 'exms' ); ?>" required />
                </div>
                <div class="exms-form-group">
                    <label for="stripe-cvc"><?php _e( 'CVC', 'exms' ); ?></label>
                    <input type="text" id="stripe-cvc" name="stripe_cvc" placeholder="<?php esc_attr_e( '123', 'exms' ); ?>" required />
                </div>
                <button type="submit" class="exms-submit-btn"><?php _e( 'Proceed with Stripe', 'exms' ); ?></button>
            </form>
        </div>
    </div>
</div>
