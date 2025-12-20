<?php 
/** Course structure modalbox template */
if( ! defined( 'ABSPATH' ) ) exit;

$post_type_html = EXMS_Setup_Functions::get_create_post_types_html();
$hide = '';
$post_type_css = '';

if( ! empty( $post_type_html ) ) {
    $post_type_css = 'exms-post-types-exists';
    $hide = 'exms-setup-hidden';
}
?>

<div id="exms-course-structure-modal" class="exms-course-structure-modal">
    <div id="exms-course-structure-modal-form" class="exms-course-structure-modal-form">
        <div class="course-structure-heading">
            <h2><?php _e( 'Course Structure', 'exms' ); ?></h2>
        </div>

        <div class="exms-post-types-html <?php echo esc_attr( $post_type_css ); ?>"></div>

        <div class="exms-create-post-wrap <?php echo esc_attr( $hide ); ?>">
            <a href="#" class="exms-post-add-new exms-modalbox-add-new">
                <span class="exms-post-add-new-icon dashicons dashicons-plus-alt2"></span>
                <span class="exms-post-add-new-text"><?php _e( 'Add New', 'exms' ); ?></span>
            </a>
        </div>
        <div class="exms-finish-p2-setup"></div>
        <div class="exms-save-button">
            <a href="#">
            <?php _e( 'Save', 'exms' ); ?>
            </a>
        </div>

        <div id="exms-course-structure-close-btn">X</div>
    </div>
</div>
