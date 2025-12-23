<?php

/**
 * Template to display [exms_student_dashboard] shortcode my report student details content
 */
if (! defined('ABSPATH')) exit;
?>
<div class="exms-std-report-detail-content">
  <div class="exms-std-attendance-btn-header">
    <button class="exms-std-back-btn exms-report-back-btn">
      <span class="dashicons dashicons-arrow-left-alt"></span>
      <?php echo __('Back', 'exms'); ?>
    </button>
  </div>

  <div class="exms-std-report-ui">
    <div class="exms-report-card exms-report-left">
      <div class="exms-report-left-head">
        <h4><?php _e('Full Reports', 'exms'); ?></h4>
        <button type="button" class="exms-icon-btn" aria-label="<?php esc_attr_e('Download', 'exms'); ?>">
          <span class="dashicons dashicons-download"></span>
        </button>
      </div>

      <div class="exms-report-left-body">
        <div class="exms-report-meta">
          <div class="exms-report-title">
            <div class="exms-report-class">26GS Myrtle I</div>
            <div class="exms-report-pill">
              <?php _e('Courses :', 'exms'); ?>
              <span class="exms-report-course-count"></span>
            </div>
          </div>

          <div class="exms-report-sub">
            <div class="exms-report-subject">GCSE Biology (AQA Single Science)</div>
            <span class="exms-check-badge" title="Completed">
              <span class="dashicons dashicons-yes-alt"></span>
            </span>
          </div>
        </div>

        <div class="exms-metrics">
          <div class="exms-metric">
            <div class="exms-ring exms-ring-green"><span>100%</span></div>
            <div class="exms-metric-text">
              <div class="exms-metric-title"><?php _e('AVG score', 'exms'); ?></div>
              <div class="exms-metric-sub"><?php _e('Lorem ipsum content', 'exms'); ?></div>
            </div>
          </div>

          <div class="exms-metric">
            <div class="exms-ring exms-ring-blue"><span>68%</span></div>
            <div class="exms-metric-text">
              <div class="exms-metric-title"><?php _e('Target score', 'exms'); ?></div>
              <div class="exms-metric-sub"><?php _e('Lorem ipsum content', 'exms'); ?></div>
            </div>
          </div>

          <div class="exms-metric">
            <div class="exms-ring exms-ring-pink"><span>75%</span></div>
            <div class="exms-metric-text">
              <div class="exms-metric-title"><?php _e('Cohort Avg Score', 'exms'); ?></div>
              <div class="exms-metric-sub"><?php _e('Nabungg jang imah dekah', 'exms'); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="exms-report-card exms-report-right">
      <div class="exms-report-right-inner">
        <div class="exms-chart-area">
          <div class="exms-chart-placeholder" id="exmsAnalyzeChart">
            <div class="exms-arc exms-arc-green"></div>
            <div class="exms-arc exms-arc-blue"></div>
            <div class="exms-arc exms-arc-pink"></div>

            <div class="exms-chart-center">
              <div class="exms-chart-title"><?php _e('Analyze', 'exms'); ?></div>
              <div class="exms-chart-sub"><?php _e('Label', 'exms'); ?></div>
            </div>

            <div class="exms-chart-tooltip">
              <span class="exms-dot exms-dot-green"></span>
              <span class="exms-tip-text"><?php _e('Avg score', 'exms'); ?></span>
              <span class="exms-tip-val">100%</span>
            </div>

            <div class="exms-chart-scale">
              <span>0%</span>
              <span>100%</span>
            </div>
          </div>

          <div class="exms-chart-legend">
            <div class="exms-legend-item"><span class="exms-dot exms-dot-green"></span><?php _e('Avg Score', 'exms'); ?></div>
            <div class="exms-legend-item"><span class="exms-dot exms-dot-blue"></span><?php _e('Target score', 'exms'); ?></div>
            <div class="exms-legend-item"><span class="exms-dot exms-dot-pink"></span><?php _e('Cohort avg score', 'exms'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="exms-report-lower-ui">

    <!-- LEFT: Comments -->
    <div class="exms-lower-card exms-comments-card">
      <div class="exms-lower-head">
        <h4><?php _e('Comments', 'exms'); ?></h4>
      </div>

      <div class="exms-comments-body">

        <!-- Academic Comments -->
        <div class="exms-accordion is-open exms-comment-parent exms-academic-comment">
          <button type="button" class="exms-accordion-head">
            <span class="exms-acc-title"><?php _e('Academic Comments', 'exms'); ?></span>
            <span class="exms-acc-icon dashicons dashicons-plus"></span>
          </button>

          <div class="exms-accordion-body">
            <div class="exms-subtitle exms-what-went-well-wrapper">
              <p><?php _e('What went well', 'exms'); ?></p>
            </div>

            <div class="exms-subtitle exms-even-if-better-wrapper">
              <p><?php _e('Even Better If', 'exms'); ?></p>
            </div>
          </div>
        </div>

        <!-- Behaviour Comments -->
        <div class="exms-accordion is-open exms-comment-parent exms-behaviour-comment">
          <button type="button" class="exms-accordion-head">
            <span class="exms-acc-title"><?php _e('Behaviour Comments', 'exms'); ?></span>
            <span class="exms-acc-icon dashicons dashicons-plus"></span>
          </button>

          <div class="exms-accordion-body">
            <div class="exms-subtitle exms-what-went-well-wrapper">
              <p><?php _e('What went well', 'exms'); ?></p>
            </div>
            <div class="exms-subtitle exms-even-if-better-wrapper">
              <p><?php _e('Even Better If', 'exms'); ?></p>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- RIGHT: Uploaded works + My Courses -->
    <div class="exms-lower-card exms-courses-card">
      <div class="exms-lower-head exms-lower-head-wide">
        <h4><?php _e('Uploaded works', 'exms'); ?></h4>
        <div class="exms-head-actions">
          <button type="button" class="exms-head-icon" title="Menu">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
          </button>
        </div>
      </div>

      <div class="exms-courses-body">
        <h3 class="exms-courses-title"><?php _e('My Courses', 'exms'); ?></h3>
        <?php echo do_shortcode( '[exms_course_hierarchy]' ); ?>
      </div>
    </div>

  </div>
</div>