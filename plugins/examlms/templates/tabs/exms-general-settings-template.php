<?php

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="exms-tab-data-heading"><span class="dashicons dashicons-admin-generic exms-icon"></span><?php _e('General Settings', 'exms' ); ?></div>

<div class="exms_settings_wrapper">
    <?php

    $settings = Exms_Core_Functions::get_options('general_settings');
    $quiz_review = Exms_Core_Functions::get_options('quiz_review_page');
    $uninstall_val = isset($settings['exms_uninstall']) ? sanitize_text_field($settings['exms_uninstall']) : '';
    $dash_val = isset($settings['dashboard_page']) ? (int) $settings['dashboard_page'] : 0;

    $checked = '';
    $default_checked = '';
    if ('on' == $uninstall_val) {

        $checked = 'checked';
    }

    if ('off' == $uninstall_val || ! $uninstall_val) {

        $default_checked = 'checked';
    }
    ?>
    <div class="exms-row exms-flex">
        <div class="exms-setting">
            <?php _e('Delete data on uninstallation', 'exms' ); ?>
        </div>

        <div class="exms-general-setting-option">
            <div class="toggle-switch">
                <input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_uninstall" id="rb8" value="on" <?php echo $checked; ?> />
                <label class='toggle_label' for="rb8">
                    <?php _e( 'Yes', 'exms' ); ?>
                </label>

                <input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_uninstall" id="rb9" value="off" <?php echo $default_checked; ?> />
                <label class='toggle_label' for="rb9">
                    <?php _e( 'No', 'exms' ); ?>
                </label>
            </div>
            <p class="exms-instruction-message"><?php _e( 'Enable this option will delete all saved data on plugin uninstall.'); ?></p>
        </div>
    </div>

    <!-- add dashboard page option in general tab -->

    <div class="exms-row exms-flex">
        <div class="exms-setting">
            <?php _e('Dashboard Page', 'exms' ); ?>
        </div>

        <div class="exms-dashboard-page-setting">
            <?php
            $dash_pages = get_pages();
            if ($dash_pages) {
            
            ?><select name="dashboard_page" class="exms-select-dashboard">
                <option value=""><?php echo __('Select a page', 'exms' ) ?></option>
                    <?php
                    foreach ($dash_pages as $dash_page) {
                    ?>
                        <option value="<?php echo $dash_page->ID; ?>" <?php echo $dash_val == $dash_page->ID ? 'selected="selected"' : ''; ?>> <?php echo get_the_title($dash_page->ID); ?></option>
                    <?php
                    }
                    ?>
                </select>
                <?php
                if (! empty($dash_val)) {

                    $link = get_permalink($dash_val);
                ?>
                    <span class="dashicons dashicons-admin-links exms-link-icon"></span>
                    <span><a href="<?php echo $link; ?>" target="_blank"><?php echo $link; ?></a></span>
            <?php
                }
            } else {
                ?>
                <select name="dashboard_page" class="exms-select-dashboard">
                <option value=""><?php echo __('Select a page', 'exms' ) ?></option>
                </select>
                <?php
            }
            ?>
            <p class="exms-instruction-message"><?php _e( 'Enable this option will delete all saved data on plugin uninstall.'); ?></p>
        </div>
    </div>

    <div class="exms-row exms-flex">
		<div class="exms-setting">
			<?php _e( 'Quiz Review', 'exms' ); ?>
		</div>

		<div class="exms-general-setting-option">
            <div >
                <select name="exms_selected_page" class="exms-select-dashboard">
                    <option value=""><?php echo esc_attr( __( 'Select Quiz review page', 'exms' ) ); ?></option>
                    <?php
                        foreach ( $dash_pages as $page ) {
                            $selected = ( $quiz_review == $page->ID ) ? 'selected' : '';
                            echo '<option value="' . esc_attr( $page->ID ) . '" ' . esc_html( $selected ). '>' . esc_html( $page->post_title ) . '</option>';
                        }
                    ?>
                </select>
                <?php if ( ! empty( $quiz_review ) ) { 
                    $link = get_permalink( $quiz_review );
                ?>
                    <span class="dashicons dashicons-admin-links exms-link-icon"></span>
                    <span><a href="<?php echo esc_url( $link ); ?>" target="_blank"><?php echo esc_html( $link ); ?></a></span>
                <?php } ?>
            </div>
			<p class="exms-instruction-message"><?php _e( 'Select Page to display quiz review.' ); ?></p>
		</div>
	</div>

    <div class="exms-row exms-flex">
        <div class="exms-setting">
            <?php _e('WP Exam Setup Wizard', 'exms' ); ?>
        </div>
        <div class="exms-setting exms-setup-wizard-link">
            <a href="<?php echo admin_url('admin.php?page=exms-setup-wizard'); ?>"><?php _e('Setup Wizard', 'exms' ); ?></a>
        </div>
    </div>

</div>