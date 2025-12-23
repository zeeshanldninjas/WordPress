<?php

if( ! defined( 'ABSPATH' ) ) exit;

$post_type_data = [];
if( $post_type == "exms-courses" ) {
    $post_type_data = get_query_var( 'course_data' );
}
if( $post_type == "exms-groups" ) {
    $post_type_data = get_query_var( query_var: 'group_data' );
}

if ( ! empty( $post_type_data ) ) {
    extract( $post_type_data ); 
}

$latest_enrollment_date = exms_get_latest_enrollment_date( $post_id );
$course_type = exms_get_post_settings( $post_id );
$post_type = get_post_type( $post_id );

if( $post_type == "exms-courses" ) {

    if( is_array( $course_type ) && ! empty( $course_type ) ) {
        $course_type = isset( $course_type['parent_post_type'] ) ? $course_type['parent_post_type'] : '';
    }
}
if( $post_type == "exms-groups" ) {

    if( is_array( $course_type ) && ! empty( $course_type ) ) {
        $course_type = isset( $course_type['group_type'] ) ? $course_type['group_type'] : '';
    }
}

$user_id = get_current_user_id();
$enrollment_user_progress = exms_get_user_progress( $user_id, $post_id );

$status = isset( $enrollment_user_progress['status'] ) ? $enrollment_user_progress['status'] : '';

if ( $status == 'enrolled' ) {
    $status = 'Not Started';
    $button_text_to_show = __( 'Start learning', 'exms' );
}

$progress = exms_calculate_course_progress( $post_id, $user_id );

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
            <div class="exms-course-template-wrapper">
                <div class="course-overview-container <?php echo esc_attr( $dynamic_class ); ?>">
                    <div class="exms-course-breadcrumb-header">
                        <?php if ( ! empty( $breadcrumb ) ) : ?>
                            <nav class="course-breadcrumb">
                                <?php foreach ( $breadcrumb as $index => $item ) : ?>
                                    <a href="<?php echo esc_url( $item['url'] ); ?>">
                                        <?php echo esc_html( $item['title'] ); ?>
                                    </a>
                                    <?php if ( $index !== array_key_last( $breadcrumb ) ) : ?>
                                        <span class="breadcrumb-separator">›</span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>

                        <?php if ( ! empty( $is_enrolled ) ) { 
    
                            global $wpdb;
                            $user_id = get_current_user_id();

                            $progress_value = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT progress_percent
                                    FROM {$wpdb->prefix}exms_user_enrollments
                                    WHERE user_id = %d 
                                    AND post_id = %d
                                    LIMIT 1",
                                    $user_id,
                                    $post_id
                                )
                            );

                            $course_marked_complete = ! empty( $progress_value ) 
                            && $progress_value !== 'in progress' 
                            && $progress_value !== '0';

                            if( $course_marked_complete ) { ?>
                                
                                <div class="course-complete-text">
                                    <?php _e( '🎉 Course Completed', 'exms' ); ?>
                                </div>

                            <?php } elseif( $progress >= 100 ) { ?>
                                
                                <form class="mark-complete-wrapper" method="post">
                                    <input type="hidden" name="exms_action" value="mark_complete_course">
                                    <input type="hidden" name="course_id" value="<?php echo esc_attr( $post_id ); ?>">
                                    <?php wp_nonce_field( 'exms_mark_complete_course', 'exms_nonce' ); ?>
                                    
                                    <button type="button" class="mark-complete-btn open-confirm-popup">
                                        <?php _e( '✓ Mark as Complete', 'exms' ); ?>
                                    </button>
                                </form>
                                
                                <!-- Confirmation Popup -->
                                <div id="exms-confirm-popup" class="exms-popup-overlay">
                                    <div class="exms-popup-box">
                                        <h3><?php echo __( 'Confirm Completion', 'exms'); ?></h3>
                                        <p><?php echo __( 'Are you sure you want to mark this course as complete?', 'exms' ); ?></p>
                                        <div class="popup-actions">
                                            <button class="popup-btn confirm"><?php echo __( 'Yes, Complete', 'exms'); ?></button>
                                            <button class="popup-btn cancel"><?php echo __( 'Cancel', 'exms'); ?></button>
                                        </div>
                                    </div>
                                </div>
                                
                            <?php } 
                        } ?>
                    </div>

                    <h2 class="course-title"><?php echo esc_html( $title ); ?></h2>
                    <div class="exms-course-page-wrapper"> 
                        <div class="exms-course-page-left">
                            <div class="exms-course-content-wrapper">
                                <div class="exms-course-thumbnail-wrapper">
                                    <?php if( empty( $video_url ) ) { ?>
                                        <?php 
                                        $img_src = empty( $thumbnail_url ) ? EXMS_ASSETS_URL . 'imgs/no-feature-image.jpg' : $thumbnail_url;
                                        ?>
                                        <img src="<?php echo esc_url( $img_src ); ?>" class="exms-course-thumbnail" alt="">
                                    <?php } else { ?>
                                        <div class="exms-course-video-wrapper">
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
                                <div class="exms-course-steps-wrapper">
                                    <div class="exms-course-info-tabs">
                                        <button class="course-content active-tab">
                                            <span class="dashicons dashicons-welcome-learn-more"></span>
                                            <?php echo __( ucfirst( $post_label ) . ' Content', 'exms' ); ?>
                                        </button>
                                        <button class="course-description">
                                            <span class="dashicons dashicons-media-text"></span>
                                            <?php echo __('Description', 'exms'); ?>
                                        </button>
                                    </div>

                                    <!-- Course Content -->
                                    <div class="exms-course-steps course-tab-content" id="course-content-tab" style="display: block;">
                                        <?php
                                        $steps = exms_render_all_course_steps( $post_id, $post_type );
                                        echo ! empty( $steps ) ? $steps : '
                                            <div class="exms-empty-message">
                                                <p>' . __( 'No content added yet.', 'exms' ) . '</p>
                                            </div>';
                                        ?>
                                    </div>

                                    <!-- Description -->
                                    <div class="course-description-tab" style="display: none;">
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
                        <!-- Toggle Sidebar Button for Tablet -->
                        <button id="toggleSidebarBtn" class="toggle-sidebar-btn" aria-label="Toggle Course Sidebar">
                            <img data-icon-right="<?php echo esc_url( EXMS_ASSETS_URL . 'imgs/rightbar-right-arrow.svg' ); ?>"
                            data-icon-left="<?php echo esc_url( EXMS_ASSETS_URL . 'imgs/rightbar-left-arrow.svg' ); ?>" class="toggle-side-bar-icon" src="<?php echo EXMS_ASSETS_URL . 'imgs/rightbar-left-arrow.svg' ;?>" alt="Toggle Sidebar" />
                        </button>
                        <!-- RIGHT PANEL -->
                        <div class="exms-course-page-right">
                            <?php
                            $is_enrolled = $is_enrolled ?? false;
                            $button_text_to_show = $is_enrolled
                            ? ( $status === 'Not Started' ? __( 'Start learning', 'exms' ) : __( 'Continue learning', 'exms' ) )
                            : esc_html( $button_text );
                            $button_class = 'exms-start-course ' . esc_attr( $dynamic_class );
                            $button_attrs = 'data_course_id="' . esc_attr( $post_id ) . '"';
                            ?>

                            <div class="<?php echo $is_enrolled ? 'exms-course-status-wrapper' : 'exms-start-course-wrapper'; ?>">

                                <?php if ( ! $is_enrolled ) { ?>
                                    <div class="exms-get-started-text"><?php echo __( 'Get Started', 'exms' ); ?></div>
                                    <div class="exms-price-type"><?php echo esc_html( $type ); ?></div>
                                <?php } else { ?>
                                    <div class="course-status-label">
                                        <strong><?php echo __( ucfirst( $post_label ) . ' Status:', 'exms' ); ?></strong>
                                        <span class="course-status-badge"><?php echo ucwords( $status); ?></span>
                                    </div>
                                    <div class="exms-progress-percent">
                                        <div class="exms-progress-percent"><?php echo $progress. '%';?> <span><?php echo ucwords( $status ); ?></span></div>
                                        <div class="exms-progress-bar">
                                            <div class="exms-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                        </div>
                                    </div>
                                <?php }
                                if (  $status == 'completed' ) {
                                    ?>
                                    <div class="exms-explore-courses-btn-wrapper">
                                        <a href="<?php echo site_url( '/courses' ); ?>" class="exms-course-complete-btn">
                                          <?php echo __( 'Explore Courses', 'wp_exam' );?>
                                        </a>
                                    </div>
                                    <?php
                                }else {
                                    ?>
                                    <a href="#" <?php if ( ! $is_enrolled )?>>
                                        <button data-course_status="<?php echo $status;?>" data-course_type="<?php echo $course_type; ?>" class="<?php echo $button_class; ?>" <?php if ( ! $is_enrolled ) { echo $button_attrs; } ?>>
                                            <?php echo $button_text_to_show; ?>
                                        </button>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="exms-course-detail-wrapper">
                                <div class="exms-course-title-wrapper"><?php echo __( ucfirst( $post_label ).' Details:', 'exms'); ?></div>
                                <div class="exms-course-detail-inner-wrap">
                                    <?php
                                    $has_data = false;

                                    if ( ! empty( $post_includes ) && ! empty( $structure ) ) {
                                        foreach ( $post_includes as $post_type => $ids ) {
                                            if ( empty( $ids ) ) {
                                                continue;
                                            }

                                            $has_data = true; 

                                            $count = count( array_unique( $ids ) );
                                            $label  = $structure[$post_type]['singular_name'] ?? ucfirst( $post_type );
                                            $first_letter = strtoupper( substr( $label, 0, 1 ) );
                                            ?>
                                            <div class="exms-course-<?php echo esc_attr( $post_type ); ?>" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                <span class="exms-icon-circle"><?php echo esc_html( $first_letter ); ?></span>
                                                <span class="exms-<?php echo esc_attr( $post_type ); ?>-text" style="margin-left: 8px;">
                                                    <?php echo esc_html( $count . ' ' . strtolower( $label ) ); ?>
                                                </span>
                                            </div>
                                            <?php
                                        }
                                    }

                                    if ( ! $has_data ) {
                                        ?>
                                        <div class="exms-empty-message" style="justify-content: center;">
                                            <p><?php _e('No '.ucfirst( $post_label ). ' details available.', 'exms'); ?></p>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>

                                <hr>

                                <div class="enrollment-box">
                                    <div class="enrollment-header"><?php echo __('Total Enrollments', 'exms'); ?></div>
                                    <div class="enrollment-content">
                                        <div class="circular-progress">
                                            <svg width="80" height="80">
                                                <circle cx="40" cy="40" r="35" stroke="#e0e0e0" stroke-width="8" fill="none"/>
                                                <circle cx="40" cy="40" r="35" stroke="#6a1b9a" stroke-width="8" fill="none"
                                                    stroke-dasharray="<?php echo $circumference; ?>"
                                                    stroke-dashoffset="<?php echo $offset; ?>"
                                                    stroke-linecap="round"
                                                    transform="rotate(-90 40 40)"/>
                                                <text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="10">
                                                    <?php echo __('Limit: ', 'exms') . $total_seat; ?>
                                                </text>
                                            </svg>
                                        </div>
                                        <div class="enrollment-info">
                                            <p><?php echo __('Total student: ', 'exms') . $post_member_count; ?></p>
                                            <p><?php echo __('Available seat: ', 'exms') . $seat_left; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="exms-course-info-container">
                                    <?php if ( !empty( $course_instructor['avatars'] ) ) { ?>
                                        <div class="exms-course-info-item">
                                            <div class="exms-course-info-text">
                                                <div class="exms-instructor-avatars">
                                                    <?php echo $course_instructor['avatars']; ?>
                                                </div>
                                                <span class="exms-label"><?php _e( 'Instructor', 'exms' ); ?></span>
                                                <span class="exms-value"><?php echo esc_html( $course_instructor['names'] ); ?></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="exms-course-info-item">
                                        <i class="dashicons dashicons-calendar-alt"></i>
                                        <div class="exms-course-info-text">
                                            <span class="exms-label"><?php _e( 'Last Enrollments date', 'exms' ); ?></span>
                                            <span class="exms-value"><?php echo esc_html( $latest_enrollment_date ); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sticky Button for Mobile -->
                    <div class="mobile-sticky-container">
                        <div class="mobile-sticky-inner <?php echo $is_enrolled ? 'enrolled' : 'not-enrolled'; ?>">
                            <?php if ( $is_enrolled ) : ?>
                                <div class="mobile-progress-text">
                                    <strong><?php echo __( 'In Progress', 'exms' ); ?></strong>
                                    <span><?php echo $progress.'%'; ?></span>
                                </div>
                                <div class="mobile-progress-bar">
                                    <div class="mobile-progress-fill" style="width: <?php echo $progress.'%'; ?>"></div>
                                </div>
                            <?php else: ?>
                                <div class="exms-get-started-text"><?php echo __( 'Get Started', 'exms' ); ?></div>
                                <div class="exms-price-type"><?php echo esc_html( $type ); ?></div>
                            <?php endif;
                            if (  $status == 'completed' ) {
                                ?>
                                <div class="exms-explore-courses-btn-wrapper">
                                    <a href="<?php echo site_url( '/courses' ); ?>" class="exms-course-complete-btn">
                                      <?php echo __( 'Explore Courses', 'wp_exam' );?>
                                    </a>
                                </div>
                                <?php
                            }else {
                                ?>
                                <a href="#" <?php if ( ! $is_enrolled )?>>
                                    <button data-course_status="<?php echo $status;?>" data-course_type="<?php echo $course_type; ?>" class="<?php echo $button_class; ?>" <?php if ( ! $is_enrolled ) { echo $button_attrs; } ?>>
                                        <?php echo $button_text_to_show; ?>
                                    </button>
                                </a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php echo $block_spacer; ?>
            </div>
            <?php
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