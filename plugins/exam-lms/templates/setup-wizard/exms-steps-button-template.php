<?php 
/**
 * Template for wp exam setup wizard "steps & buttons"
 */

if( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="exms-form-footer">
    <div class="exms-form-pages">
        <div class="exms-footer-pages">
            
            <div class="exms-page exms-page1">
                <span class="exms-no">1</span>
                <span class="exms-no-text"><?php _e( 'Structure', 'exms' ); ?></span>
                <div class="exms-progress-bar"></div>
            </div>
            
            <div class="exms-page exms-page2">
                <span class="exms-no">2</span>
                <span class="exms-no-text"><?php _e( 'Payments', 'exms' ); ?></span>
                <div class="exms-progress-bar"></div>
            </div>
            
            <div class="exms-page exms-page3">
                <span class="exms-no">3</span>
                <span class="exms-no-text"><?php _e( 'Settings', 'exms' ); ?></span>
                <div class="exms-progress-bar"></div>
            </div>
            <div class="exms-page exms-page4">
                <span class="exms-no">4</span>
                <span class="exms-no-text"><?php _e( 'Labels', 'exms' ); ?></span>
                <div class="exms-progress-bar"></div>
            </div>
            <div class="exms-page exms-page5">
                <span class="exms-no">5</span>
                <span class="exms-no-text"><?php _e( 'Report bugs', 'exms' ); ?></span>
            </div>
        </div>
    </div>

    <div class="exms-back-next-wrap">
        <div class="exms-step-button-child">
            <div class="exms-back-setup-wrap" data-redirect="">
                <div class="exms-flex-content-btn exms-back-btn">
                    <div class="dashicons dashicons-arrow-left-alt"></div>
                    <input type="button" name="exms_back_setup" class="exms-back-setup" value="<?php _e( 'Back', 'exms' ); ?>">
                </div>
            </div>
            <div id="exms-skip-setup-wrap" class="exms-skip-setup-wrap" data-redirect="">
                <div class="exms-flex-content-btn">
                    <input type="button" name="exms_skip_setup" class="exms-skip-setup" value="<?php _e( 'Skip', 'exms' ); ?>">
                </div>
            </div>
            <div id = "exms-next-setup-wrap" class="exms-next-setup-wrap" data-redirect="">
                <div class="exms-flex-content-btn">
                    <input type="button" name="exms_next_setup" class="exms-next-setup" value="<?php _e( 'Next', 'exms' ); ?>">
                    <div class="dashicons dashicons-arrow-right-alt"></div>
                </div>	
            </div>
            <div id = "exms-finish-setup-wrap" style="display:none;" class="exms-finish-setup-wrap" data-redirect="">
                <div class="exms-flex-content-btn">
                    <a href="<?php echo esc_url( EXMS_DIR_URL . 'post-new.php?post_type=exms-courses' ); ?>" name="exms_finish_setup" class="exms-finish-setup">
                        <?php _e( 'Finish', 'exms' ); ?>
                    </a>
                    <!-- <input type="button"  value="<?php _e( 'Finish', 'exms' ); ?>"> -->
                    <div class="dashicons dashicons-arrow-right-alt"></div>
                </div>
            </div>
        </div>
    </div>

</div>