<?php

/**
 * Template to display [exms_student_dashboard] shortcode dashboard content
 */
if (! defined('ABSPATH')) exit;
?>

<h3 class="exms-std-dashboard-heading"> <?php echo __('Dashboard', 'exms'); ?></h3>
<div class="exms-right-sidebar-content">
    <div class="exms-std-chat-section exms-std-main-section">
        <div class="exms-std-child-section-header">
            <h4> <?php echo __('Chats', 'exms'); ?> </h4>
            <a href="#"> <?php echo __('See all', 'exms'); ?></a>
        </div>
    </div>
    <div class="exms-std-calender-section exms-std-main-section">
        <div class="exms-std-child-section-header">
            <h4> <?php echo __('Calender', 'exms'); ?> </h4>
            <a href="#"> <?php echo __('See all', 'exms'); ?></a>
        </div>
        <div class="exms-std-child-section-content">
            <div class="exms-calender-child-section-content exms-calender-child-section-content-icon">
                <span class="dashicons dashicons-rss"></span>
            </div>
            <div class="exms-calender-child-section-content">
                <p class="exms-calender"> <?php echo __('Intensive Revision Practice Questions Paper', 'exms'); ?> </p>
                <p class="exms-calender-date"> <span class="dashicons dashicons-calendar"></span> <?php echo __('17 Feb 2025', 'exms'); ?></p>
            </div>
        </div>
        <div class="exms-std-child-section-content">
            <div class="exms-calender-child-section-content exms-calender-child-section-content-icon">
                <span class="dashicons dashicons-rss"></span>
            </div>
            <div class="exms-calender-child-section-content">
                <p class="exms-calender"> <?php echo __('Intensive Revision Practice Questions Paper', 'exms'); ?> </p>
                <p class="exms-calender-date"> <span class="dashicons dashicons-calendar"></span> <?php echo __('17 Feb 2025', 'exms'); ?></p>
            </div>
        </div>
        <div class="exms-std-child-section-content">
            <div class="exms-calender-child-section-content exms-calender-child-section-content-icon">
                <span class="dashicons dashicons-rss"></span>
            </div>
            <div class="exms-calender-child-section-content">
                <p class="exms-calender"> <?php echo __('Intensive Revision Practice Questions Paper', 'exms'); ?> </p>
                <p class="exms-calender-date"> <span class="dashicons dashicons-calendar"></span> <?php echo __('17 Feb 2025', 'exms'); ?></p>
            </div>
        </div>
    </div>
    <?php
    do_action( 'exms_add_content_on_dashboard' );
    ?>
</div>
<div class="exms-right-sidebar-content">
    <div class="exms-std-course-section exms-std-main-section">
        <div class="exms-std-child-section-header">
            <h4> <?php echo __('My Courses', 'exms'); ?> </h4>
            <a href="#"> <?php echo __('See all', 'exms'); ?></a>            
        </div>
        <p><?php echo do_shortcode( '[exms_course_hierarchy]' ); ?></p>
    </div>
    <div class="exms-std-group-section exms-std-main-section">
        <div class="exms-std-child-section-header">
            <h4> <?php echo __('My Groups', 'exms'); ?> </h4>
            <a href="#"> <?php echo __('See all', 'exms'); ?></a>
        </div>
        <div class="exms-std-group-body">
            <?php 
            if( is_array( $group_ids ) && ! empty( $group_ids ) ) {
                foreach( $group_ids as $group_id ) {
                    ?>
                    <p>
                        <a href="#"><?php echo get_the_title( $group_id ) ?></a>
                    </p>
                    <?php
                }
            } else {
                ?>
                <p class="exms-group-not-enrolled">
                    <?php echo __( 'You are not enrolled in any group.', 'exms' ); ?>
                </p>
                <?php
            }
            ?>
        </div>
    </div>
</div>