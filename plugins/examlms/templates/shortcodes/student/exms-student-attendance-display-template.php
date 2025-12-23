<?php

/**
 * Template to display [exms_student_dashboard] shortcode attendance content
 */
if (! defined('ABSPATH')) exit;
?>
<div class="exms-std-attendance-content">
    <div class="exms-std-attendance-btn-header">
        <a href="<?php echo $current_page_link; ?>?exms_active_tab=exms_dashboard&exms_db_type=student" class="exms-std-back-btn">
            <span class="dashicons dashicons-arrow-left-alt"></span>
            <?php echo __( 'Back', 'exms' ); ?>
        </a>
        <a href="#" class="exms-std-download-btn"> <?php echo __('Download', 'exms'); ?></a>
    </div>
    <div class="exms-std-attendance-filter">
        <h4> <?php echo __('Select Time Period') ?></h4>
        <div class="exms-std-attendance-filter-fields">
            <select name="" id="">
                <option value="today"><?php echo __('Today', 'exms'); ?></option>
                <option value="yesterday"><?php echo __('Yesterday', 'exms'); ?></option>
                <option value="this week"><?php echo __('This week', 'exms'); ?></option>
                <option value="last week"><?php echo __('Last week', 'exms'); ?></option>
                <option value="this month"><?php echo __('This month', 'exms'); ?></option>
                <option value="last month"><?php echo __('Last month', 'exms'); ?></option>
                <option value="this year"><?php echo __('This year', 'exms'); ?></option>
                <option value="last year"><?php echo __('Last year', 'exms'); ?></option>
            </select>
            <button> <?php echo __('Filter', 'exms'); ?></button>
        </div>
    </div>
    <div class="exms-std-attendance-wrap">
        <table class="exms-std-attendance-table">
            <thead>
                <tr>
                    <th><?php echo __('Student Name', 'exms'); ?></th>
                    <th><?php echo __('Attendance Type', 'exms'); ?></th>
                    <th><?php echo __('Comments', 'exms'); ?></th>
                    <th><?php echo __('Date', 'exms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr class="exms-std-attendance-row">
                    <td class="exms-std-attendance-student">
                        <span class="exms-std-attendance-avatar dashicons dashicons-admin-users"></span>
                        <span class="exms-std-attendance-student-name">Imran Asif</span>
                    </td>

                    <td class="exms-std-attendance-type">
                        <div class="exms-std-attendance-select-wrap">
                            <select name="attendance_type[]">
                                <option value="present"><?php echo __('Present', 'exms'); ?></option>
                                <option value="absent" selected><?php echo __('Absent', 'exms'); ?></option>
                                <option value="late"><?php echo __('Late', 'exms'); ?></option>
                                <option value="excused"><?php echo __('Excused', 'exms'); ?></option>
                            </select>
                        </div>
                    </td>

                    <td class="exms-std-attendance-comments">
                        <button type="button" class="exms-std-attendance-comment-btn">
                            <?php echo __('View Comments', 'exms'); ?>
                        </button>
                    </td>

                    <td class="exms-std-attendance-date">
                        12-Jan-2025
                    </td>
                </tr>
                <tr class="exms-std-attendance-row">
                    <td class="exms-std-attendance-student">
                        <span class="exms-std-attendance-avatar dashicons dashicons-admin-users"></span>
                        <span class="exms-std-attendance-student-name">Manahil Anum </span>
                    </td>

                    <td class="exms-std-attendance-type">
                        <div class="exms-std-attendance-select-wrap">
                            <select name="attendance_type[]">
                                <option value="present"><?php echo __('Present', 'exms'); ?></option>
                                <option value="absent" selected><?php echo __('Absent', 'exms'); ?></option>
                                <option value="late"><?php echo __('Late', 'exms'); ?></option>
                                <option value="excused"><?php echo __('Excused', 'exms'); ?></option>
                            </select>
                        </div>
                    </td>

                    <td class="exms-std-attendance-comments">
                        <button type="button" class="exms-std-attendance-comment-btn">
                            <?php echo __('View Comments', 'exms'); ?>
                        </button>
                    </td>

                    <td class="exms-std-attendance-date">
                        12-Jan-2025
                    </td>
                </tr>

                <!-- Duplicate <tr> for more students in your loop -->
            </tbody>
        </table>
        <div class="exms-std-attendance-submit-wrap">
            <button type="submit" class="exms-std-attendance-submit-btn">
                <?php echo __('Submit', 'exms'); ?>
            </button>
        </div>

    </div>

</div>