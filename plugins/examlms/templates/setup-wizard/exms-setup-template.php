<?php 

/**
 * Template for WP Exam setup wizard content HTML 
 */

$hide = '';
$post_type_css = '';

?>
<div class="exms-setup-wrapper">
	<div class="exms-logo-parent">
		<div class="exms-logo-child">
			<div class="exms-logo">
				<a href="">
					<img src="<?php echo EXMS_ASSETS_URL . 'imgs/wpexam-logo.png' ?>">
				</a>
			</div>
	
			<div class="exms-logo-links">
				<a href="#"> <?php echo __( 'Documentation', 'exms' ); ?></a>
				<a href="#"> <?php echo __( 'Support', 'exms' ); ?></a>
			</div>
		</div>
	</div>
	<div class="exms-side-logo">
		
	</div>
	<div class="exms-clear-both"></div>

	<div class="exms-content-wrapper">
		<?php
			if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-steps-button-template.php' ) ) {
				require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-steps-button-template.php';	
			}
			if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-start-template.php' ) ) {
				require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-start-template.php';	
			}
		?>	
		<div class="exms-setup-license">
			<form class="exms-setup-license-form" method="post">
				<?php
				if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-course-structure-template.php' ) ) {
					require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-course-structure-template.php';	
				}

				if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-payment-template.php' ) ) {
					require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-payment-template.php';	
				}
				
				if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-settings-template.php' ) ) {
					require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-settings-template.php';	
				}

				if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-label-template.php' ) ) {
					require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-label-template.php';	
				}
				if( file_exists( EXMS_TEMPLATES_DIR . '/setup-wizard/exms-report-bug-template.php' ) ) {
					require_once EXMS_TEMPLATES_DIR . '/setup-wizard/exms-report-bug-template.php';	
				}
				?>	
			</form>
		</div>
	</div>	
</div>

<div class="exms-success-messages">
	<span class="exms-success-close">x</span>
	<span class="exms-success-text"></span>
</div>
<div class="exms-error-message"></div>
<?php
