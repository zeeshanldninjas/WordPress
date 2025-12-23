<?php

/**
 * Template to display [exms_student_dashboard] shortcode notifiation content
 */
if (! defined('ABSPATH')) exit;
?>
<h3 class="exms-std-dashboard-heading">
  <?php echo __('Notification', 'exms'); ?>
</h3>
<div class="exms-std-notification-content">
<div class="exms-dashboard-grid">

  <div class="exms-exam-card">

    <div class="exms-exam-head">
      <h4>11PLUS EXAMINATION DATES</h4>
      <p>
        Discover what life is really like as a Myrtle student, including
        the courses we offer and how to apply.
      </p>
    </div>

    <div class="exms-exam-body">

      <h5 class="exms-exam-sub">Start Times for Lessons</h5>

      <p>
        Discover what life is really like as a Myrtle student, including the
        courses we offer and how to apply.
      </p>

      <p>
        We at Myrtle Learning are concerned about students joining lessons late
        and not being ready for the lessons, especially online lessons.
      </p>

      <p><strong>Henceforth, all students are expected to adhere to the following steps:</strong></p>

      <ul class="exms-exam-list">
        <li>Join their lessons 5 mins before the start time for online lessons</li>
        <li>Ensure you use the toilet before to avoid interruptions during the sessions</li>
        <li>Have all your equipment ready; pencil sharpened, pens and rulers ready</li>
        <li>We also encourage all students to review the powerpoint for the session before it starts</li>
        <li>All students must have books for each subject they take with us</li>
      </ul>

      <p>
        There will be an exercise book audit in 3 weeks time and we will be
        looking at presentation and the amount of work completed!
      </p>

      <p class="exms-exam-sign">The Myrtle Learning Team</p>

    </div>
  </div>

  <div class="exms-notify-card">

    <div class="exms-notify-head">
      <span class="exms-notify-heading">Other Notifications</span>
      <span class="dashicons dashicons-bell exms-notify-bell-icon"></span>
    </div>

    <div class="exms-notify-list">
      <?php for($i=0;$i<5;$i++): ?>
        <div class="exms-notify-item">
          <span class="dashicons dashicons-megaphone"></span>
          <div>
            <strong>2024 Myrtle Learning Events</strong>
            <small>Today, 11:59 AM</small>
          </div>
        </div>
      <?php endfor; ?>
    </div>

    <div class="exms-notify-foot">
      <span class="dashicons dashicons-arrow-left-alt2"></span>
      <span class="dashicons dashicons-arrow-right-alt2"></span>
    </div>

  </div>
</div>
</div>