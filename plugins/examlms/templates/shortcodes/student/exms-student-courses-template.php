<?php

/**
 * Template to display [exms_student_dashboard] shortcode courses content
 */
if (! defined('ABSPATH')) exit;
?>
<h3 class="exms-std-dashboard-heading"> <?php echo __('My Courses', 'exms'); ?></h3>
<div class="exms-std-course-content">
    <div class="exms-std-course-filters">
        <div class="exms-std-course-filters-fields">
            <h4> <?php echo __( 'Select Group', 'exms' ) ?></h4>
            <select name="" id="">
                <option value=""><?php echo __('Select a Group', 'exms'); ?></option>
            </select>
        </div>
        <div class="exms-std-course-filters-fields">
            <h4> <?php echo __( 'Select User', 'exms' ) ?></h4>
            <select name="" id="">
            </select>
        </div>
    </div>
    <button class="exms-std-apply-btn"> <?php echo __('Apply Filters', 'exms'); ?></button>
    <div class="exms-std-course-data">
        <div class="exms-std-course-data-header">
            <span class="exms-course-search-icon dashicons dashicons-search"></span>
            <button>
                <span class="dashicons dashicons-arrow-down-alt2"></span>
                <?php echo __( 'Expand All', 'exms' ); ?>
            </button>
        </div>
        <div class="exms-std-course-display">
            <!-- Course 1 - Active / Selected -->
            <div class="exms-std-course-item exms-std-course-item-active">
                <div class="exms-std-course-item-left">
                    <span class="exms-std-course-status exms-status-active"></span>
                    <span class="exms-std-course-title">Introduction to Accounting</span>
                </div>
                <button type="button" class="exms-std-course-toggle">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>

            <!-- Course 2 -->
            <div class="exms-std-course-item">
                <div class="exms-std-course-item-left">
                    <span class="exms-std-course-status exms-status-complete"></span>
                    <span class="exms-std-course-title">GCSE Mathematics</span>
                </div>
                <button type="button" class="exms-std-course-toggle">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>

            <!-- Same structure for more courses… -->
            <div class="exms-std-course-item">
                <div class="exms-std-course-item-left">
                    <span class="exms-std-course-status exms-status-complete"></span>
                    <span class="exms-std-course-title">GCSE Mathematics</span>
                </div>
                <button type="button" class="exms-std-course-toggle">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
            </div>

        </div>

    </div>
</div>