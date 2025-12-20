<?php 

/** Payment modalbox configure template */
if( ! defined( 'ABSPATH' ) ) exit;
?>

<div id="exms-payment-modal" class="exms-payment-modal">
    <div id="exms-payment-modal-form" class="exms-payment-modal-form">
        <div class="payment-heading">
            <h2> <?php _e( 'Setup Payment Gateway', 'exms' ); ?></h2>
        </div>
        <div class="exms-setup-wizard-paypal">
            <?php
            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-paypal-settings.php' ) ) {
                require EXMS_TEMPLATES_DIR . '/tabs/exms-paypal-settings.php';	
            }
            ?>	
        </div>
        <div class="exms-setup-wizard-stripe">
            <?php
            if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-stripe-settings.php' ) ) {
                require EXMS_TEMPLATES_DIR . '/tabs/exms-stripe-settings.php';	
            }
            ?>	
        </div>
        <div class="exms-payment-update-button"> <button class="exms-update-button" name="exms-update-button"><?php _e( 'Update', 'exms' ); ?> </button></div>
        <div id="exms-payment-close-btn">X</div>
    </div>
</div>