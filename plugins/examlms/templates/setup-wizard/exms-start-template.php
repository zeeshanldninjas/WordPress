<?php 
/**
 * Template for wp exam setup wizard start page
 */

if( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="exms-setup-start exms-setup-p0" data-start-page=0>
    <div class="exms-congrs-icon"><img src="<?php echo EXMS_ASSETS_URL . 'imgs/congrs.png' ?>"></div>
    <h2 class="exms-welcom-wpexam"><?php _e( 'Welcome to Exam LMS', 'exms' ); ?></h2>
    <div class="exms-content-desc">
        <?php _e( "Thank you for choosing WP Exams – the ultimate LMS plugin for WordPress. Let's set up your Learning system in a few simple steps.", 'exms' ); ?>
    </div>
    <div class="exms-setup-buttons">
        <input type="button" name="exms_start_setup" class="exms-start-setup" value="<?php _e( "Let's Get Started", 'exms' ); ?>">
    </div>
</div>
<a href="<?php echo admin_url(); ?>" class="exms-exit-setup"><?php _e( 'Dismiss Setup Wizard', 'exms' ); ?></a>