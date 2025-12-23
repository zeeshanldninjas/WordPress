<?php

/**
 * Template to display [exms_student_dashboard] shortcode my account content
 */
if (! defined('ABSPATH')) exit;
?>

<h3 class="exms-std-dashboard-heading"><?php echo __('My Account', 'exms'); ?></h3>

<div class="exms-std-account-content">
  <div class="exms-std-account-data">
    <div class="exms-account-heading">
      <h4><?php echo __( "Personal Information", 'exms' ); ?></h4>
    </div>

    <div class="exms-account-body">

      <!-- Profile -->
      <div class="exms-profile-wrap">
        <button type="button" class="exms-profile-btn" id="exmsProfileBtn" aria-label="Change profile photo">
          <img
            id="exmsProfilePreview"
            class="exms-profile-img"
            src="<?php echo esc_url( get_avatar_url( get_current_user_id(), ['size'=>160] ) ); ?>"
            alt="<?php echo esc_attr__('Profile photo', 'exms'); ?>"
          />
          <span class="exms-profile-camera" aria-hidden="true"></span>
        </button>

        <input
          type="file"
          id="exmsProfileInput"
          class="exms-profile-input"
          confirm="false"
          accept="image/png,image/jpeg,image/webp"
        />
      </div>

      <!-- Form -->
      <form class="exms-pi-form" method="post">
        <div class="exms-pi-grid">
          <div class="exms-field">
            <label for="exms_staff_name"><?php echo __('Student Name', 'exms'); ?></label>
            <input type="text" id="exms_staff_name" name="exms_staff_name" placeholder="<?php echo esc_attr__('Enter Student name', 'exms'); ?>" value="<?php echo $user_email; ?>">
          </div>

          <div class="exms-field">
            <label for="exms_dob"><?php echo __('Date of birth', 'exms'); ?></label>
            <div class="exms-date-wrap">
              <input type="date" id="exms_dob" name="exms_dob" placeholder="mm/dd/yyyy">
            </div>
          </div>

          <div class="exms-field">
            <label for="exms_personal_email"><?php echo __('Personal Email', 'exms'); ?></label>
            <input type="email" id="exms_personal_email" name="exms_personal_email" placeholder="example@gmail.com">
          </div>

          <div class="exms-field">
            <label for="exms_alt_dob"><?php echo __('Date of birth', 'exms'); ?></label>
            <input type="date" id="exms_alt_dob" name="exms_alt_dob" placeholder="mm/dd/yyyy">
          </div>

          <div class="exms-field exms-field-full">
            <label for="exms_home_address"><?php echo __('Home Address', 'exms'); ?></label>
            <textarea id="exms_home_address" name="exms_home_address" rows="4" placeholder="<?php echo esc_attr__('Enter Your Home address', 'exms'); ?>"></textarea>
          </div>

          <div class="exms-field">
            <label for="exms_contact_1"><?php echo __('Contact # 1', 'exms'); ?></label>
            <input type="tel" id="exms_contact_1" name="exms_contact_1" placeholder="0000000000000">
          </div>

          <div class="exms-field">
            <label for="exms_contact_2"><?php echo __('Contact # 2', 'exms'); ?></label>
            <input type="tel" id="exms_contact_2" name="exms_contact_2" placeholder="0000000000000">
          </div>
        </div>
      </form>

    </div>
  </div>
</div>
