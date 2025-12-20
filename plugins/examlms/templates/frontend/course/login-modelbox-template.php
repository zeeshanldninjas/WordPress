<div id="exms-login-popup" class="exms-login-box">
    <div class="exms-login-header">
        <h2><?php _e( 'Welcome', 'exms' ); ?></h2>
        <span class="exms-login-close">&times;</span>
    </div>

    <div class="exms-login-tabs">
        <button class="exms-login-tab-button active" data-tab="exms-login-tab-login"><?php _e( 'Login', 'exms' ); ?></button>
        <button class="exms-login-tab-button" data-tab="exms-login-tab-register"><?php _e( 'Register', 'exms' ); ?></button>
    </div>

    <div class="exms-login-content">
        <!-- LOGIN TAB -->
        <div id="exms-login-tab-login" class="exms-login-tab-content active">
            <iframe src="<?php echo wp_login_url(); ?>" width="100%" height="400" style="border:none;"></iframe>
        </div>

        <!-- REGISTER TAB -->
        <div id="exms-login-tab-register" class="exms-login-tab-content">
            <iframe src="<?php echo wp_registration_url(); ?>" width="100%" height="500" style="border:none;"></iframe>
        </div>
    </div>
</div>

<div id="exms-login-popup-overlay"></div>