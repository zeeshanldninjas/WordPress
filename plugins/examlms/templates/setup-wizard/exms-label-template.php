<?php 

/**
 * Label template for the setup wizard page
 */
if( ! defined( 'ABSPATH' ) ) exit;

$settings = Exms_Core_Functions::get_options( 'labels' );
$dynamic_settings = Exms_Core_Functions::get_options( 'dynamic_labels' );
$settings_json = !empty( $settings ) && is_array( $settings ) ? json_encode( $settings ) : '{}';
$dynamic_settings_json = !empty( $dynamic_settings ) && is_array( $dynamic_settings ) ? json_encode( $dynamic_settings ) : '{}';
?>
<div class="exms-setup-start exms-setup-label exms-setup-p4">
    <div class="exms-row exms-flex">
        <div class="exms-dashboard-page-setting exms-label-setup-page" data-dynamic-label='<?php echo esc_attr( $dynamic_settings_json ); ?>' data-label='<?php echo esc_attr( $settings_json ); ?>'>
            <?php
                $exms_submitted_essays  = isset( $settings[ 'exms_submitted_essays' ] ) ?     $settings[ 'exms_submitted_essays' ]  : __( 'Submitted Essays', 'exms' );
                $exms_user_report  = isset( $settings[ 'exms_user_report' ] ) ?     $settings[ 'exms_user_report' ]  : __( 'User Report', 'exms' );
                $exms_quiz_report  = isset( $settings[ 'exms_quiz_report' ] ) ?     $settings[ 'exms_quiz_report' ]  : __( 'Quiz Report', 'exms' );
                $exms_quizzes  = isset( $settings[ 'exms_quizzes' ] ) ?     $settings[ 'exms_quizzes' ]  : __( 'Quizzes', 'exms' );
                $exms_qroup  = isset( $settings[ 'exms_qroup' ] ) ?     $settings[ 'exms_qroup' ]  : __( 'Groups', 'exms' );
                $exms_questions  = isset( $settings[ 'exms_questions' ] ) ?     $settings[ 'exms_questions' ]  : __( 'Questions', 'exms' );
                $exms_certificates  = isset( $settings[ 'exms_certificates' ] ) ?     $settings[ 'exms_certificates' ]  : __( 'Certificates', 'exms' );
            
            if( !empty( $dynamic_settings ) && is_array( $dynamic_settings ) ) {
                foreach( $dynamic_settings as $key => $label ) {
                    $input_id = esc_attr( $key );
                    $input_value = esc_attr( $label );
                    $input_placeholder = esc_attr( $label );
                    $label_text = ucfirst( $label );
                    ?>
                    <div class="exms-setup-settings-row exms-dynamic-label-row">
                        <div class="exms-setting-lable">
                            <label><?php echo esc_html($label_text . ' label:'); ?></label>
                        </div>
                        <div class="exms-setup-setting-data">
                            <input type="text" id="<?php echo $input_id; ?>" name="<?php echo $input_id; ?>" placeholder="<?php echo $input_placeholder; ?>" value="<?php echo $input_value; ?>">
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Quizzes label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_quizzes" name="exms_quizzes" placeholder="<?php _e( 'Quizzes', 'exms' ); ?>" value="<?php echo $exms_quizzes; ?>">
                </div>
            </div>
            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Questions label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_questions" name="exms_questions" placeholder="<?php _e( 'Questions', 'exms' ); ?>" value="<?php echo $exms_questions; ?>">
                </div>
            </div>
            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Group label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_qroup" name="exms_qroup" placeholder="<?php _e( 'Group', 'exms' ); ?>" value="<?php echo $exms_qroup; ?>">
                </div>
            </div>
            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Certificates label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_certificates" name="exms_certificates" placeholder="<?php _e( 'Certificates', 'exms' ); ?>" value="<?php echo $exms_certificates; ?>">
                </div>
            </div>

            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Submitted Essays label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_submitted_essays" name="exms_submitted_essays" placeholder="<?php _e( 'Submitted Essays', 'exms' ); ?>" value="<?php echo $exms_submitted_essays; ?>">
                </div>
            </div>
            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'User Report label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_user_report" name="exms_user_report" placeholder="<?php _e( 'User Report', 'exms' ); ?>" value="<?php echo $exms_user_report; ?>">
                </div>
            </div>
            <div class="exms-setup-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Quiz Report label:', 'exms' ); ?></label>
                </div>
                <div class="exms-setup-setting-data">
                    <input type="text" id="exms_quiz_report" name="exms_quiz_report" placeholder="<?php _e( 'Quiz Report', 'exms' ); ?>" value="<?php echo $exms_quiz_report; ?>">
                </div>
            </div>
        </div>	
    </div>	
</div>