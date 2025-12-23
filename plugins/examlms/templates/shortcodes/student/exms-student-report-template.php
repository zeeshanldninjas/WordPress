<?php

/**
 * Template to display [exms_student_dashboard] shortcode my account content
 */
if (! defined('ABSPATH')) exit;
?>

<h3 class="exms-std-dashboard-heading"><?php echo __('My Report', 'exms'); ?></h3>

<div class="exms-std-report-content">
  <div class="exms-std-report-filters">
    <div class="exms-std-report-filters-fields">
      <h4> <?php echo __('Select Group', 'exms') ?></h4>
      <select name="" id="exms-frontend-report-group-dropdown">
        <option value=""><?php echo __( 'Select a group', 'exms' ); ?></option>
        <?php 
        if( is_array( $group_ids ) && ! empty( $group_ids ) ) {
          foreach( $group_ids as $group_id ) {
            ?>
            <option value="<?php echo $group_id; ?>"><?php echo get_the_title( $group_id ) ?></option>
            <?php
          }
        }
        ?>
      </select>
    </div>
    <div class="exms-std-report-filters-fields">
      <h4> <?php echo __('Select Course', 'exms') ?></h4>
      <select name="" id="exms-frontend-report-course-dropdown">
      </select>
    </div>
  </div>
  <div class="exms-std-report-filters">
    <div class="exms-std-report-filters-fields">
      <h4> <?php echo __('Start Date', 'exms') ?></h4>
      <div class="exms-date-wrap">
        <input type="date" id="exms_report_start_date" name="exms_report_start_date" placeholder="mm/dd/yyyy">
      </div>
    </div>
    <div class="exms-std-report-filters-fields">
      <h4> <?php echo __('End Date', 'exms') ?></h4>
      <div class="exms-date-wrap">
        <input type="date" id="exms_report_end_date" name="exms_report_end_date" placeholder="mm/dd/yyyy">
      </div>
    </div>
  </div>
  <button class="exms-std-report-apply-btn"> 
    <?php echo __('Apply Filters', 'exms'); ?>
    <span class="exms-loader"></span>
  </button>
  <div class="exms-std-report-data">
    <div class="exms-std-report-data-header">
      <h4 class="exms-course-search-icon"> <?php _e('Records', 'exms'); ?></h4>
      <button>
        <span class="dashicons dashicons-cloud-upload"></span>
        <?php echo __('Download Reports', 'exms'); ?>
      </button>
    </div>

    <div class="exms-std-report-table-wrap">
      <table class="exms-std-report-table">
        <thead>
          <tr>
            <th class="col-no"><?php _e('No', 'exms'); ?></th>
            <th class="col-course"><?php _e('Course', 'exms'); ?></th>
            <th class="col-student"><?php _e('Student Name', 'exms'); ?></th>
            <th class="col-score"><?php _e('Score', 'exms'); ?></th>
            <th class="col-target"><?php _e('Target', 'exms'); ?></th>
            <th class="col-academic"><?php _e('Academic', 'exms'); ?></th>
            <th class="col-behaviour"><?php _e('Behaviour', 'exms'); ?></th>
            <th class="col-actions"></th>
          </tr>
        </thead>

        <tbody>
            <tr>
              <td colspan="8" class="exms-empty"><?php _e('No records found.', 'exms'); ?></td>
            </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>