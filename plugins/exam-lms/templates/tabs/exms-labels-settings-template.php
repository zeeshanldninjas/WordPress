<?php 

if ( ! defined( 'ABSPATH' ) ) exit;

$labels = Exms_Core_Functions::get_options( 'labels' );
$dynamic_labels = Exms_Core_Functions::get_options( 'dynamic_labels' );
$settings_json = !empty( $dynamic_labels ) && is_array( $dynamic_labels ) ? json_encode( $dynamic_labels ) : '{}';
$exms_submitted_essays  = isset( $labels[ 'exms_submitted_essays' ] ) ?     $labels[ 'exms_submitted_essays' ]  : __( 'Submitted Essays', 'exms');
$exms_user_report  = isset( $labels[ 'exms_user_report' ] ) ?     $labels[ 'exms_user_report' ]  : __( 'User Report', 'exms');
$exms_quiz_report  = isset( $labels[ 'exms_quiz_report' ] ) ?     $labels[ 'exms_quiz_report' ]  : __( 'Quiz Report', 'exms');
$exms_quizzes  = isset( $labels[ 'exms_quizzes' ] ) ?     $labels[ 'exms_quizzes' ]  : __( 'Quizzes', 'exms');
$exms_qroup  = isset( $labels[ 'exms_qroup' ] ) ?     $labels[ 'exms_qroup' ]  : __( 'Groups', 'exms');
$exms_questions  = isset( $labels[ 'exms_questions' ] ) ?     $labels[ 'exms_questions' ]  : __( 'Questions', 'exms');

$exms_certificates  = isset( $labels[ 'exms_certificates' ] ) ?     $labels[ 'exms_certificates' ]  : __( 'Certificates', 'exms');
?>
<div class="exms-tab-data-heading"><span class="dashicons dashicons-shortcode exms-icon"></span><?php _e( 'Labels ', 'exms'); ?></div>

<div class="exms_settings_wrapper exms-settings-labels" data-dynamic-labels='<?php echo esc_attr( $settings_json ); ?>'>	
    <div class="exms-email-settings-wrap form-table">
        <div class="exms-settings-container">

            <?php if ( !empty( $dynamic_labels ) && is_array( $dynamic_labels ) ): ?>
                <?php foreach ( $dynamic_labels as $key => $label ): ?>
                    <div class="exms-email-settings-row">
                        <div class="exms-setting-lable">
                            <label for="<?php echo esc_attr( $key ); ?>">
                                <?php echo esc_html( ucwords( str_replace( '-', ' ', str_replace( 'exms-', '', $key ) ) ) ) . ' Label:'; ?>
                            </label>
                        </div>
                        <div class="exms-email-setting-data">
                            <input
                                type="text"
                                id="<?php echo esc_attr( $key ); ?>"
                                name="<?php echo esc_attr( $key ); ?>"
                                placeholder="<?php echo esc_attr( ucwords( str_replace( '-', ' ', str_replace( 'exms_', '', $key ) ) ) ); ?>"
                                value="<?php echo esc_attr( $label ); ?>"
                            >
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Quizzes label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_quizzes" placeholder="<?php _e( 'Quizzes', 'exms'); ?>" value="<?php echo $exms_quizzes; ?>">
                </div>
            </div>
            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Questions label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_questions" placeholder="<?php _e( 'Questions', 'exms'); ?>" value="<?php echo $exms_questions; ?>">
                </div>
            </div>
            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Group label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_qroup" placeholder="<?php _e( 'Group', 'exms'); ?>" value="<?php echo $exms_qroup; ?>">
                </div>
            </div>
            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Certificates label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_certificates" placeholder="<?php _e( 'Certificates', 'exms'); ?>" value="<?php echo $exms_certificates; ?>">
                </div>
            </div>

            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Submitted Essays label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_submitted_essays" placeholder="<?php _e( 'Submitted Essays', 'exms'); ?>" value="<?php echo $exms_submitted_essays; ?>">
                </div>
            </div>
            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'User Report label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_user_report" placeholder="<?php _e( 'User Report', 'exms'); ?>" value="<?php echo $exms_user_report; ?>">
                </div>
            </div>
            <div class="exms-email-settings-row">
                <div class="exms-setting-lable">
                    <label><?php _e( 'Quiz Report label:', 'exms'); ?></label>
                </div>
                <div class="exms-email-setting-data">
                    <input type="text" name="exms_quiz_report" placeholder="<?php _e( 'Quiz Report', 'exms'); ?>" value="<?php echo $exms_quiz_report; ?>">
                </div>
            </div>
        </div>
    </div>
</div>    
