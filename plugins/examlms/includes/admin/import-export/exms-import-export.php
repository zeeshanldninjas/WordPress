<?php
/**
 * Import/export template content HTML
 */
if( ! defined( 'ABSPATH' ) ) exit;

$args = array(
  'numberposts' => -1,
  'post_type'   => 'exms_quizzes',
  'post_status'	=> 'publish',
  'fields' 		=> 'ids'
);
$query = get_posts( $args );
?>
<div class="exms-essay-title">
	<span class="dashicons dashicons-welcome-learn-more exms-wp-logo"></span>
	<h2><?php _e( 'WP Exams Import/Export', 'exms' ); ?></h2>
</div>
<div class="exms-import-export">

	<!-- Import html -->
	<form method="post" enctype="multipart/form-data">
		<div class="exms-import">
			<ul class="exms-sub-tabs exms-add-tab-padding">
				<li><?php _e( 'WP Exams Import' ); ?></li>
			</ul>
			<div class="exms-settings-container">
		  	<div class="exms-settings-row">
		      <div class="exms-setting-lable">
		          <label><?php _e( 'Select a file :', 'exms' ); ?></label>
		      </div>
		      <div class="exms-setting-data">
		          <input type="file" name="csv_file"  required="required" class="imp_file" />
		        	<p><?php exms_add_info_title( 'Select a file to import.', 'exms' ); ?></p>
		    	</div>
		    </div>
		    <div class="exms-settings-row">
		      <div class="exms-setting-lable">
		          <label><?php _e( 'Import file :', 'exms' ); ?></label>
		      </div>
		      <div class="exms-setting-data">
							<button class="button button-secondary imp-btn" type="submit" name="exms_import">
								<?php _e( 'Import File', 'exms' ); ?>
								<span class='dashicons dashicons-download import-btn'></span>
							</button>
							<input type="hidden" name="action" value="exms_import_action">
		        	<p><?php exms_add_info_title( 'Import WP Exams data.', 'exms' ); ?></p>
		    	</div>
		    </div>
		  </div>
		</div>
	</form>
	<!-- End import html -->

	<!-- Export html -->
	<form method='post' enctype='multipart/form-data'>
		<div class="exms-export">
			<ul class="exms-sub-tabs exms-add-tab-padding">
				<li><?php _e( 'WP Exams Export' ); ?></li>
			</ul>
			<div class="exms-settings-container">

				<!-- select post type drop down -->
		  	<div class="exms-settings-row">
		      <div class="exms-setting-lable">
		          <label><?php _e( 'Select a Post :', 'exms' ); ?></label>
		      </div>
		      <div class="exms-setting-data">
		          <select name="exms_exp_post_type" class="exms-select-post-type">
		          	<option value="exms_post_type"><?php _e( 'Select Post', 'exms' ); ?></option>
		          	<option value="exms_quizzes"><?php _e( 'Quizzes', 'exms' ); ?></option>
		          	<option value="exms_questions"><?php _e( 'Questions', 'exms' ); ?></option>
		          </select>
		        	<p><?php exms_add_info_title( 'Select post type to export.', 'exms' ); ?></p>
		    	</div>
		    </div>
		    <!-- End post type dropdown -->

		    <!-- Select multiple post to export -->
		    <div class="exms-settings-row">
		      <div class="exms-setting-lable">
		          <label><?php _e( 'Export Posts :', 'exms' ); ?></label>
		      </div>
		      <div class="exms-setting-data">
		      	<img class="exms-export-loader" src="<?php echo EXMS_ASSETS_URL.'imgs/spinner.gif'; ?>">
		          <select name="exms_select_post_to_export" class="exms-post-to-export">
		          	<option value=""><?php _e( 'Select Export Post', 'exms' ); ?></option>
		          </select>
		        	<p><?php exms_add_info_title( 'Select multiple post to export.', 'exms' ); ?></p>
		    	</div>
		    </div>
		    <!-- End select multiple post html -->

		    <!-- Add export button to export file -->
		    <div class="exms-settings-row">
		      <div class="exms-setting-lable">
		          <label><?php _e( 'Export file :', 'exms' ); ?></label>
		      </div>
		      <div class="exms-setting-data">
		      	<button class="button button-secondary" type="submit" name="exms_export"> 
							<?php _e( 'Export File', 'exms' ); ?> 
							<span class="dashicons dashicons-upload export-btn"></span>
						</button>
						<?php wp_nonce_field( 'exms_export_nonce', 'exms_export_nonce_field' ); ?>
						<input type="hidden" name="action" value="exms_export_action">
		        <p><?php exms_add_info_title( 'Export WP Exam data.', 'exms' ); ?></p>
		    	</div>
		    </div>
		    <!-- End export button html -->

		  </div>
		</div>
	</form>
	<!-- End export html -->
</div>

<?php /* ?>
<div>
	<h3><?php _e( 'Import/Export Quizzes', 'exms' ); ?></h3>
</div>
<form method='post' enctype='multipart/form-data'>
	<table class='wp-list-table widefat fixed striped posts import_export_table'>
<?php
	if( count( $query ) > 0 ) { 
?>
		<tr>
			<th class="manage-column column-cb check-column" width="2%">
				<input type="checkbox" class="check_all" />
			</th>
			<th>
				<label class="checkbox_label"><?php _e( 'Select All' ); ?></label>
			</th>
		</tr>
<?php
		foreach ( $query as $quiz_id ) {
		?>
			<tr>
				<th class="manage-column column-cb check-column" width="2%">
					<input class="check_box_exp" type="checkbox" value='<?php echo $quiz_id; ?>' name="quiz_id[]" />
				</th>
				<th>
					<label class="checkbox_label checkbox_label_posttitle">
						<?php _e( ucwords( get_the_title( $quiz_id ) ), 'exms' ); ?>
					</label>
				</th>
			</tr>

		<?php
		}	
	}
?>
	</table>
	<div class="exms_export_button_div">
		<button class="button button-primary" type="submit" name="exms_export"> 
			<?php _e( 'Export', 'exms' ); ?> 
			<span class="dashicons dashicons-upload"></span>
		</button>
		<input type="hidden" name="action" value="exms_export_action">
	</div>
</form>

<form method="post" enctype="multipart/form-data">
	<div class="exms_import_div">
	
	<input type="file" name="csv_file"  required="required" class="imp_file" />
	<br/>
	<button class="button button-primary imp-btn" type="submit" name="exms_import">
		<?php _e( 'Import', 'exms' ); ?>
		<span class='dashicons dashicons-download import-btn'></span>
	</button>
	<input type="hidden" name="action" value="exms_import_action">
	
	</div>
</form>

?> <?php */