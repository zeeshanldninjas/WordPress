<?php 
/**
 * Template for wp exam setup wizard "settings" page
 */

if( ! defined( 'ABSPATH' ) ) exit;

$settings = Exms_Core_Functions::get_options( 'general_settings' );
$settings_json = !empty( $settings ) && is_array( $settings ) ? json_encode( $settings ) : '{}';
?>
<div class="exms-setup-start exms-setup-settings exms-setup-p3 exms-setup-wizard-general-settings" data-general="<?php echo esc_attr( $settings_json ); ?>">
    <?php
        $uninstall_val = isset( $settings['exms_uninstall'] ) ? sanitize_text_field( $settings['exms_uninstall'] ) : '';
        $dash_val = isset( $settings['dashboard_page'] ) ? ( int ) $settings['dashboard_page'] : 0;
        
        $checked = '';
        $default_checked = '';
        if( 'on' == $uninstall_val ) {
            
            $checked = 'checked';
        }

        if( 'off' == $uninstall_val || ! $uninstall_val ) {

            $default_checked = 'checked';
        }
    ?>
    <div class="exms-row exms-flex">
        <div class="exms-setting">
            <?php _e( 'Delete data on uninstallation', 'exms' ); ?>
        </div>

        <div class="exms-general-setting-option">
                <div class="toggle-switch">
                    <input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_uninstall_wizard" id="rb8" value="on" <?php echo $checked; ?> />
                    <label class='toggle_label' for="rb8">
                        <?php _e( 'Yes', 'exms' ); ?>
                    </label>

                    <input type="radio" class="toggle_radio exms-reattempts-quiz-toggle" name="exms_uninstall_wizard" id="rb9" value="off" <?php echo $default_checked; ?> />
                    <label class='toggle_label' for="rb9">
                        <?php _e( 'No', 'exms' ); ?>
                    </label>
                </div>
            <?php exms_add_info_title( 'Enable this option will delete all saved data on plugin uninstall.' ); ?>
        </div>
    </div>

    <!-- add dashboard page option in general tab -->
    
    <div class="exms-row exms-flex">
        <div class="exms-setting">
            <?php _e( 'Dashboard Page', 'exms' ); ?>
        </div>

        <div class="exms-dashboard-page-setting">
        <?php
            $dash_pages = get_pages();
            
            ?><select name="dashboard_page" class="exms-select-dashboard" id="exms-select-dashboard-wizard">
                <option value=""><?php echo __( 'Select a page', 'exms' ) ?></option>
                <?php
                if( $dash_pages ) {
                    foreach( $dash_pages as $dash_page ) {
                ?>
                        <option value="<?php echo $dash_page->ID; ?>" <?php echo $dash_val == $dash_page->ID ? 'selected="selected"' : ''; ?>> <?php echo get_the_title( $dash_page->ID ); ?></option>
                        <?php
                    }
                }
            ?>
                </select>
            <?php
            exms_add_info_title( 'Select a page to display plugin dashboard.' );
        ?>
        </div>
    </div>		
</div>