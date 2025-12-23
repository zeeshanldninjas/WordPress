<?php

/**
 * Template to display [exms_student_dashboard] shortcode attendance content
 */
if (! defined('ABSPATH')) exit;
?>
<h3 class="exms-std-dashboard-heading"> <?php echo __('Attendance', 'exms'); ?></h3>
<div class="exms-std-attendance-display-content">
    <div class="exms-std-attendance-display-btn-header">
        <div class="exms-attendance-tabs">
            <a class="exms-attendance-tab is-active" href="#">
                <?php echo __('User Attendance','exms'); ?>
            </a>
            <a class="exms-attendance-tab" href="#">
                <?php echo __('Teacher Attendance','exms'); ?>
            </a>
        </div>
    </div>
    <div class="exms-std-attendance-display-filters">
        <div class="exms-std-attendance-display-filters-fields">
            <h4> <?php echo __( 'Select Group', 'exms' ) ?></h4>
            <select name="exms_std_attendance_select_group" id="exms-std-attendance-select-group">
                <option value=""><?php echo __('Select a group', 'exms'); ?></option>
                <?php 
                if( is_array( $group_ids ) && ! empty( $group_ids ) ) {
                    foreach( $group_ids as $group_id ) {
                        ?>
                        <option value="<?php echo $group_id; ?>"><?php echo get_the_title( $group_id ); ?></option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
        <div class="exms-std-attendance-display-filters-fields">
            <h4> <?php echo __( 'Select Course', 'exms' ) ?></h4>
            <select name="exms_std_attendance_select_course" id="exms-std-attendance-select-course">
                <option value=""><?php echo __('Select a course', 'exms'); ?></option>
            </select>
        </div>
    </div>
    <div class="exms-std-attendance-ui">

        <!-- Exclude Users -->
        <div class="exms-att-field">
          <label class="exms-att-label"><?php echo __('Exclude Users', 'exms'); ?></label>

          <div class="exms-att-input exms-att-input--tags" id="exmsExcludeUsers">

            <div class="exms-att-tags">
                <span class="exms-att-tag" data-value="asad ahmed">
                    <button type="button" class="exms-att-tag-remove" aria-label="Remove">×</button>
                    <span class="exms-att-tag-text">Asad Ahmed</span>
                    <input type="hidden" name="exms_exclude_users[]" value="Asad Ahmed">
                </span>
            </div>

            <input type="text"
              class="exms-att-tag-input"
              placeholder="<?php echo __('Type name & press Enter', 'exms'); ?>"
              autocomplete="off" />

            <button type="button" class="exms-att-clear-all" aria-label="Clear all">×</button>
          </div>
        </div>


        <!-- Include Users -->
        <div class="exms-att-field">
            <label class="exms-att-label"><?php echo __('Include Users', 'exms'); ?></label>

            <div class="exms-att-select">
              <button type="button" class="exms-att-select-head" id="exmsIncludeUsersBtn" aria-expanded="true">
                <span><?php echo __('Select Users', 'exms'); ?></span>
              </button>

              <div class="exms-att-select-menu" id="exmsIncludeUsersMenu">
                <button type="button" class="exms-att-option">Kaman aslam</button>
                <button type="button" class="exms-att-option">Asad Imran</button>
                <button type="button" class="exms-att-option">Tarikh Hussain</button>
                <button type="button" class="exms-att-option">Alam Ibrar</button>
              </div>
            </div>
        </div>

        <!-- Proceed -->
        <div class="exms-att-actions">
            <button type="button" class="exms-att-proceed-btn"><?php echo __('Proceed', 'exms'); ?></button>
        </div>
    </div>
</div>