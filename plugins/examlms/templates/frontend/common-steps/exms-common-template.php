<?php

if( ! defined( 'ABSPATH' ) ) exit;

$legacy_id = get_the_ID();
$post_type = get_post_type( $legacy_id );
$breadcrumb = get_quiz_breadcrumb( $legacy_id );
$thumbnail_url       = get_the_post_thumbnail_url( $legacy_id, 'full' );
$common_label        = get_post_type_object( get_post_type( $legacy_id ) )->labels->singular_name ?? 'Legacy';
$course_id = exms_get_course_id();
$course_title = get_the_title( $course_id );
$user_id = get_current_user_id();
$course_progress = exms_calculate_course_progress( $course_id, $user_id );
$is_enrolled = exms_is_user_in_post( $user_id, $course_id );
$status = '';
if ( $status == 'enrolled' ) {
    $status = 'Not Started';
    $button_text_to_show = __( 'Start learning', 'exms' );
}
$has_children = exms_has_children( $legacy_id );
$legacy_info = exms_get_post_settings( $legacy_id );
$video_url = isset( $legacy_info['video_url'] ) ? $legacy_info['video_url'] : '';
$current_page_url = exms_get_current_url();
$legacy_progress = exms_calculate_progress($legacy_id, $user_id, $course_id, $current_page_url);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php
        $title = get_the_title();
        $site_name = get_bloginfo('name');
        ?>
        <title><?php echo esc_html($title . ' - ' . $site_name); ?></title>
        <?php
        $header_html = do_blocks(exms_get_template_part('header'));
        $footer_html = do_blocks(exms_get_template_part('footer'));
        $block_spacer = do_blocks(
            '<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>'
        );

        wp_head();
        ?>
    </head>
    <body>
		<div class="wp-site-blocks443432">
        	<?php
            if ( ! empty( $header_html ) ) {
                ?>
                <header class="wp-block-template-part">
                    <?php echo $header_html;?>
                </header>
                <?php  
            } else {
                get_header();
            }
            ?>
            <div class="exms-legacy-step-wrapper">
            	<!-- BreadCrumb -->
			    <div class="exms-legacy-breadcrumb-header">
			        <?php if ( ! empty( $breadcrumb ) ) { ?>
			            <nav class="legacy-breadcrumb">
			                <?php foreach ( $breadcrumb as $index => $item ) { ?>
			                    <a href="<?php echo esc_url( $item['url'] ); ?>">
			                        <?php echo esc_html( $item['title'] ); ?>
			                    </a>
			                    <?php if ( $index !== array_key_last( $breadcrumb ) ) { ?>
			                        <span class="breadcrumb-separator">›</span>
			                    <?php } ?>
			                <?php } ?>
			            </nav>
			        <?php } 
                    $all_children_completed = true;

                    if ( $has_children ) {
                        $children = exms_get_children( $legacy_id ); 
                        foreach ( $children as $child_id ) {
                            if ( ! exms_is_step_complete( $legacy_id, $user_id, $course_id ) ) {
                                $all_children_completed = false;
                                break;
                            }
                        }
                    }

                    if ( $is_enrolled && ( 
                            ( ! $has_children && ! exms_is_step_complete( $legacy_id, $user_id, $course_id ) ) 
                            || 
                            ( $has_children && $legacy_progress >= 99 && ! exms_is_step_complete( $legacy_id, $user_id, $course_id ) ) 
                        ) ) { ?>
                        <form class="mark-complete-wrapper" method="post">
                            <input type="hidden" name="exms_action" value="mark_complete">
                            <input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
                            <input type="hidden" name="step_id" value="<?php echo esc_attr( $legacy_id ); ?>">
                            <?php wp_nonce_field( 'exms_mark_complete', 'exms_nonce' ); ?>
                            <button type="button" class="exms-mark-step-complete-btn open-confirm-popup">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php _e( 'Mark as Complete', 'exms' ); ?>
                            </button>
                        </form>
                        <!-- Confirmation Popup -->
                        <div id="exms-confirm-popup" class="exms-popup-overlay">
                            <div class="exms-popup-box">
                                <h3><?php echo __( 'Confirm Completion', 'exms');?></h3>
                                <p><?php echo __( 'Are you sure you want to mark this step as complete?', 'exms' ); ?></p>
                                <div class="popup-actions">
                                    <button class="popup-btn confirm"><?php echo __( 'Yes, Complete', 'exms' );?></button>
                                    <button class="popup-btn cancel"><?php echo __( 'Cancel', 'exms');?></button>
                                </div>
                            </div>
                        </div>
                    <?php } elseif ( exms_is_step_complete( $legacy_id, $user_id, $course_id ) ) { ?>
                        <span class="exms-mark-step-complete-btn completed">
                            <span class="dashicons dashicons-yes"></span> 
                            <?php _e( 'Completed', 'exms' ); ?>
                        </span>
                    <?php } ?>
			    </div>
			    <!-- BreadCrumb -->
			    <h2 class="exms-legacy-step-title"><?php echo esc_html( $title ); ?></h2>
			    <div class="exms-legacy-page-wrapper">
			    	<div class="exms-legacy-page-left">
			    		<div class="exms-legacy-content-wrapper">
                            <div class="exms-legacy-thumbnail-wrapper">
                                <?php if ( empty( $video_url ) ) { ?>
                                    <?php 
                                    $img_src = empty( $thumbnail_url ) ? EXMS_ASSETS_URL . 'imgs/no-feature-image.jpg' : $thumbnail_url;
                                    ?>
                                    <img src="<?php echo esc_url( $img_src ); ?>" class="exms-legacy-thumbnail" alt="">
                                <?php } else { ?>
                                    <div class="exms-legacy-video-wrapper">
                                        <?php

                                        if ( strpos( $video_url, 'youtube.com' ) !== false || strpos( $video_url, 'youtu.be' ) !== false ) {

                                            if ( strpos( $video_url, 'youtu.be' ) !== false ) {
                                                $video_id = basename( $video_url );
                                            } else {
                                                parse_str( parse_url( $video_url, PHP_URL_QUERY ), $params );
                                                $video_id = isset( $params['v'] ) ? $params['v'] : '';
                                            }

                                            if ( $video_id ) {
                                                echo '<iframe src="https://www.youtube.com/embed/' . esc_attr( $video_id ) . '" allowfullscreen></iframe>';
                                            }

                                        } elseif ( strpos( $video_url, 'vimeo.com' ) !== false ) {

                                            $video_id = (int) substr( parse_url( $video_url, PHP_URL_PATH ), 1 );

                                            if ( $video_id ) {
                                                echo '<iframe src="https://player.vimeo.com/video/' . esc_attr( $video_id ) . '" allowfullscreen></iframe>';
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <!-- Tabs -->
                            <div class="exms-legacy-steps-wrapper">
                                <div class="exms-legacy-info-tabs">
                                    <button class="legacy-content active-tab">
                                        <span class="dashicons dashicons-welcome-learn-more"></span>
                                        <?php echo __( ucfirst( $common_label ) . ' Content', 'exms' ); ?>
                                    </button>
                                    <button class="legacy-description">
                                        <span class="dashicons dashicons-media-text"></span>
                                        <?php echo __('Description', 'exms'); ?>
                                    </button>
                                </div>

                                <!-- legacy Content -->
                                <div class="exms-legacy-steps legacy-tab-content" id="legacy-content-tab" style="display: block;">
                                    <?php
                                    $steps = exms_render_all_course_steps( $legacy_id, $post_type );
                                    echo ! empty( $steps ) ? $steps : '
                                        <div class="exms-empty-message">
                                            <p>' . __( 'No content added yet.', 'exms' ) . '</p>
                                        </div>';
                                    ?>
                                </div>

                                <!-- Description -->
                                <div class="legacy-description-tab" style="display: none;">
                                    <?php
                                    $description = isset( $post->post_content ) ? trim( $post->post_content ) : '';
                                    echo ! empty( $description )
                                        ? wpautop( $description )
                                        : '
                                        <div class="exms-empty-message">
                                            <p>' . __( 'No description available.', 'exms' ) . '</p>
                                        </div>';
                                    ?>
                                </div>
                            </div>
                        </div>
			    	</div>
			    	<div class="exms-legacy-page-right">
                        <div class="exms-legacy-course">
                            <div class="exms-legacy-course__header">
                                <div class="exms-legacy-course__icon dashicons dashicons-book-alt"></div>
                                <div class="exms-legacy-course__title">
                                    <?php
                                    $course_name = get_the_title( $course_id );
                                    echo ucfirst( $course_name );
                                    ?>
                                </div>
                            </div>

                            <div class="exms-legacy-course__progress">
                                <div class="exms-legacy-course__progress-text">
                                    <?php echo $course_progress; ?>%<span><?php echo $status; ?></span>
                                </div>
                                <div class="exms-legacy-course__progress-bar">
                                    <div class="exms-legacy-course__progress-fill" style="width: <?php echo $course_progress; ?>%;" data-percent="<?php echo $course_progress; ?>"></div>
                                </div>
                            </div>

                            <!-- Legacy steps -->
                            <?php
                            $steps = exms_render_all_course_steps( $course_id, 'exms-courses', true );
                                        echo ! empty( $steps ) ? $steps : '
                                            <div class="exms-empty-message">
                                                <p>' . __( 'No content added yet.', 'exms' ) . '</p>
                                            </div>';
                                        ?>
                        </div>
                    </div>

			    </div>
			</div>
        	<?php echo $block_spacer;
            if ( ! empty( $footer_html ) ) {
                ?>
                <footer class="wp-block-template-part site-footer">
                    <?php echo $footer_html; ?>
                </footer>
                <?php  
            } else {
                get_footer();
            }
            	?>
        </div>
    <?php wp_footer(); ?>
    </body>
</html>