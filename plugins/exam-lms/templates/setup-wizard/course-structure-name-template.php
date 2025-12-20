<?php 

/** Course structure edit name modal box template */

if( ! defined( 'ABSPATH' ) ) exit;

?>

<div id="exms-edit-course-structure-modal" class="exms-edit-course-structure-modal">
    <div id="exms-edit-course-structure-modal-form" class="exms-edit-course-structure-modal-form">
        <div class="exms-success-message"></div>
        <div class="course-structure-heading">
            <h2><?php _e( 'Edit Course Structure Names', 'exms' ); ?></h2>
        </div>

        <div class="exms-edit-structure-steps-name">;
		</div>
        <div class="exms-save-structure-steps-name">
            <a href="#"><?php _e( 'Save', 'exms'); ?></a>
        </div>

        <div id="exms-edit-course-structure-close-btn">X</div>
    </div>
</div>
