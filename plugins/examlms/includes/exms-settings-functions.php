<?php
/**
 * Admin settings functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create settings submenu tabs
 */
function exms_create_submenu_tabs( $tabs, $args ) {

	$tab = ( ! empty( $_GET['tab'] ) ) ? $_GET['tab'] : '';
    $tab_type = ( ! empty( $_GET['tab_type'] ) ) ? $_GET['tab_type'] : '';
    $active_class = 'exms-active-sub-tab';
    $taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : '';
    $taxo_type = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : 'exms_quizzes_' .$taxonomy;
    $post_type = isset( $_GET['exms_post_type'] ) ? $_GET['exms_post_type'] : 'exms_quizzes';
    $tab_count = 0;

    /**
     * Create submenu tabs
     */
    if( $tabs ) {

        $tab_styles = [ 'email', 'payment-integration', 'reports', 'categories', 'tags' ];
        if( in_array( $tab, $tab_styles ) ) {
            $addClass = 'exms-add-tab-padding';
        }

        $labels = Exms_Core_Functions::get_options( 'labels' );
        $existing_dynamic_labels = Exms_Core_Functions::get_options( 'dynamic_labels' );
    ?>
    	<ul class="exms-sub-tabs <?php echo $addClass; ?>">
    <?php
    	foreach( $tabs as $tab_value => $tab_title ) {

            if( 'exms-quizzes' == $tab_value ) {
                $tab_title = isset( $labels['exms_quizzes'] ) ? $labels['exms_quizzes'] : __( 'Quizzes', 'exms' );
            } elseif( 'exms-questions' == $tab_value ) {
                $tab_title = isset( $labels['exms_questions'] ) ? $labels['exms_questions'] : __( 'Questions', 'exms' );
            } elseif( 'exms-groups' == $tab_value ) {
                $tab_title = isset( $labels['exms_qroup'] ) ? $labels['exms_qroup'] : __( 'Group', 'exms' );
            } elseif( 'exms-courses' == $tab_value ) {
                $tab_title = isset( $existing_dynamic_labels['exms-courses'] ) ? $existing_dynamic_labels['exms-courses'] : __( 'Course', 'exms' );
            } elseif( 'exms-lessons' == $tab_value ) {
                $tab_title = isset( $existing_dynamic_labels['exms-lessons'] ) ? $existing_dynamic_labels['exms-lessons'] : __( 'Lesson', 'exms' );
            } elseif( 'exms-topics' == $tab_value ) {
                $tab_title = isset( $existing_dynamic_labels['exms-topics'] ) ? $existing_dynamic_labels['exms-topics'] : __( 'Topic', 'exms' );
            }

    		$filtered_title = strtolower( str_replace( ' ', '_', $tab_title ) );
    		$curr_tab_type = 'exms_'.$filtered_title.'_'.$tab;
    		$is_active = $curr_tab_type == $tab_type || ! $tab_type && $tab_count <= 0 ? 'exms-active-sub-tab' : '';
    		$tab_keys = array_keys( $tabs );
    		$seprator = isset( $tab_keys[$tab_count+1] ) ? '|' : '';
    		$link = EXMS_DIR_URL . 'admin.php?page=exms-settings&tab='.$tab.'&tab_type='.$curr_tab_type.'&taxo_type='.$tab_value.'_'.$tab;
	?>
	        <li>
	            <a class="exms-sub-tab <?php echo $is_active; ?>" href="<?php echo $link; ?>"><?php _e( $tab_title, 'exms' ); ?></a>
	        </li>
    <?php
    		echo $seprator;
    		$tab_count++;
    	}
    ?>
    	</ul>
    <?php
    }
}

/**
 * Render Admin Sub Tabs based on page
 */
function exms_render_admin_sub_tabs( $args = [] ) {
    $page = isset( $_GET['page'] ) ? $_GET['page'] : '';
    $tab  = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $tab_type = isset( $_GET['tab_type'] ) ? $_GET['tab_type'] : '';

    if ( $page === 'exms-reports' ) {
        $tabs = [
            'exms_quizzes_reports'     => __( 'Quizzes', 'exms' ),
            'exms_students_reports'    => __( 'Students', 'exms' ),
            'exms_instructors_reports' => __( 'Instructors', 'exms' ),
        ];
    } else {
        $tabs = [
            'exms-quizzes'   => __( 'Quizzes', 'exms' ),
            'exms-questions' => __( 'Questions', 'exms' ),
            'exms-groups'    => __( 'Groups', 'exms' ),
        ];
    }

    if ( empty( $tabs ) ) {
        return;
    }

    $tab_count = 0;
    echo '<ul class="exms-sub-tabs">';
    foreach ( $tabs as $key => $label ) {

        $is_active = ( $tab_type == $key || ( ! $tab_type && $key === 'exms_quizzes_reports' ) ) ? 'exms-active-sub-tab' : '';
        $link = admin_url( 'admin.php?page=' . $page . '&tab=reports&tab_type=' . $key . '&taxo_type=' . $tab_count . '_reports' );

        echo '<li><a class="exms-sub-tab ' . esc_attr( $is_active ) . '" href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a></li>';
        $tab_count++;
    }
    echo '</ul>';
}

/**
 * Add an information title 
 *
 * @param String 	$info_text 	Information text to display
 */
function exms_add_info_title( $info_text ) {
?>
	<div class="div-info exms-info">
  		<span class="dashicons dashicons-info"></span> 
  		<i><?php _e( $info_text, 'exms' ); ?></i>
  	</div>
<?php
}

/**
 * Add an information sub title 
 *
 * @param String    $info_text  Information text to display
 */
function exms_add_sub_info_title( $info_text ) {
    
    ?>
    <div class="exms-sub-info exms-info">
        <span class="dashicons dashicons-info"></span> 
        <i><?php _e( $info_text, 'exms' ); ?></i>
    </div>
    <?php
}

/**
 * Display the success notice
 */
function exms_show_success_notice() { 
?>	
	<div class="notice notice-success wpeq-success-notice is-dismissible" style="padding: 10px"><?php _e( 'Settings Updated', 'exms' ); ?></div>
<?php
}

/**
 * Get enabled payment system
 */
function exms_get_enabled_payment_modes() {

	return isset( get_option( 'exms_settings' )->payment_integration->payment_methods ) ? get_option( 'exms_settings' )->payment_integration->payment_methods : null;
}

/**
 * Get paypal payment settings
 */
function exms_get_paypal_settings() {

	return get_option( 'exms_settings' ) && isset( get_option( 'exms_settings' )->payment_integration->paypal ) ? get_option( 'exms_settings' )->payment_integration->paypal : null;
}

/**
 * Get stripe payment settings
 */
function exms_get_stripe_settings() {

	return get_option( 'exms_settings' ) && isset( get_option( 'exms_settings' )->payment_integration->stripe ) ? get_option( 'exms_settings' )->payment_integration->stripe : null;
}

/**
 * Get Quiz Settings
 *
 * @param Mixed 	$quiz_id 	Post id of quiz
 */
function exms_get_quiz_settings( $quiz_id ) { 

	$quiz_type = get_post_meta( $quiz_id, 'exms_quiz_settings_' . $quiz_id, true );
	if( !empty( $quiz_type ) ) {

		return $quiz_type;
	}
}

/**
 * Get Quiz type
 *
 * @param Mixed 	$quiz_id 	Post id of quiz
 */
// function exms_get_quiz_type( $quiz_id ) { 

// 	$quiz_type = get_post_meta( $quiz_id, 'exms_quiz_settings_' . $quiz_id, true );
// 	return ( isset( $quiz_type['quiz_type'] ) ) ? $quiz_type['quiz_type'] : '';
// }

/**
 * Add timer field
 *
 * @param String 	$time 	Time format( Hours:Minutes:Seconds )
 */
function exms_display_timer_field( $time = 'hh:mm:ss' ) { 
	$break_time = explode( ':', $time );
	$hour = isset( $break_time[0] ) ? $break_time[0] : '00';
	$mins = isset( $break_time[1] ) ? $break_time[1] : '00';
	$secs = isset( $break_time[2] ) ? $break_time[2] : '00';
	?>
	<div class="exms-timer-row">
		<div class="exms-timer-inputs">
			<div class="exms-time-box">
				<input type="number" min="0" max="24" class="wpeq-ques-hours exms-time-input" name="exms_timer_hours" value="<?php echo esc_attr( $hour ); ?>" />
				<span class="exms-time-label">Hrs</span>
			</div>
			<div class="exms-time-box">
				<input type="number" min="0" max="60" class="wpeq-ques-mins exms-time-input" name="exms_timer_mins" value="<?php echo esc_attr( $mins ); ?>" />
				<span class="exms-time-label">Min</span>
			</div>
			<div class="exms-time-box">
				<input type="number" min="0" max="60" class="wpeq-ques-secs exms-time-input" name="exms_timer_secs" value="<?php echo esc_attr( $secs ); ?>" />
				<span class="exms-time-label">Sec</span>
			</div>
			<a href="#" class="wpeq-reset-btn" style="text-decoration: none; background: none; border: none; padding: 0; font-size: 16px; cursor: pointer;"><span class="dashicons dashicons-update" style="font-size: 20px;"></span> Reset</a>
			<input type="hidden" class="wpeq-ques-timer" name="exms_timer" value="<?php echo esc_attr( $time ); ?>" />
		</div>
	</div>
	<?php
}


/**
 * create function to check email option
 */
function exms_email_option( $option ) {

	$checked = '';
	if( 'yes' == $option ) {
		$checked = 'checked="checked"';
	}
	return $checked;
}

/**
 * Create email tags 
 */
function exms_create_email_tags() {

	?>
	<div class="exms-tags-wrap">
        <span class="exms-tags-title"><?php _e( 'Available Tags :', 'exms' ); ?></span>
        <div class="exms-available-tags">
            <ul>
                <strong><?php _e( 'Site Tags' ); ?></strong>
                <li><code>{site_title}</code><span><?php _e( ' Display the website title.', 'exms' ); ?></span></li>
                <li><code>{site_url}</code><span><?php _e( ' Display the website URL.', 'exms' ); ?></span></li>
                <strong><?php _e( 'User Tags' ); ?></strong>
                <li><code>{admin_name}</code><span><?php _e( ' Display the admin username.', 'exms' ); ?></span></li>
                <li><code>{instructor_name}</code><span><?php _e( ' Display the instructor username.', 'exms' ); ?></span></li>
                <li><code>{user_id}</code><span><?php _e( ' Display the current user id.', 'exms' ); ?></span></li>
                <li><code>{user_name}</code><span><?php _e( ' Display the current username.', 'exms' ); ?></span></li>
                <li><code>{user_email}</code><span><?php _e( ' Display the current user email.', 'exms' ); ?></span></li>
                <strong><?php _e( 'Quiz Tags' ); ?></strong>
                <li><code>{quiz_id}</code><span><?php _e( ' Display the quiz id.', 'exms' ); ?></span></li>
                <li><code>{quiz_name}</code><span><?php _e( ' Display the quiz name', 'exms' ); ?></span></li>
                <strong><?php _e( 'Achievements' ); ?></strong>
                <li><code>{badge_name}</code><span><?php _e( ' Display the badge name.', 'exms' ); ?></span></li>
                <strong><?php _e( 'Groups Tags' ); ?></strong>
                <li><code>{group_name}</code><span><?php _e( ' Display the group name.', 'exms' ); ?></span></li>
            </ul>
        </div>
    </div>
	<?php
}

/**
 * Create tags shortcodes
 */
function exms_replace_message_with_tags( $user_id, $quiz_id, $message ) {

    $post_type = get_post_type( $quiz_id );

    $admin_email = get_option('admin_email');
    $admin_user = get_user_by( 'email', $admin_email );
    $admin_name = $admin_user->display_name;
    $user_info = get_userdata( $user_id );

    /**
     * Get instructors
     */
    $instructor_name = '';
    $instructors = exms_get_quiz_instructors( $quiz_id );
    if( $instructors ) {
        foreach( $instructors as $instructor ) {

            $instructor_id = get_userdata( (int) $instructor );
            $instructor_name .= $instructor_id->data->display_name . ',';
        }
    }

    /**
     * Get badges
     */
    $badge_ids = get_user_meta( $user_id, 'exms_user_badges', true );
    $badge_name = '';
    if( ! empty( $badge_ids ) ) {

        foreach( $badge_ids as $badge_id ) {
            $badge_name .= get_the_title( $badge_id ) . ',' ;
        }   
    }

    /**
     * Replace message to short tags
     */
    $replace_tags = [
        '{admin_name}'      => ucwords( $admin_name ),
        '{user_name}'       => ucwords( $user_info->display_name ), 
        '{user_id}'         => $user_id,
        '{user_email}'      => $user_info->user_email,
        '{quiz_id}'         => $quiz_id,
        '{quiz_name}'       => ucwords( get_the_title( $quiz_id ) ),
        '{site_url}'        => site_url(),
        '{site_name}'       => get_bloginfo( 'name' ),
        '{instructor_name}' => $instructor_name,
        '{badge_name}'      => $badge_name,
        '{group_name}'      => $post_type && 'exms_groups' == $post_type ? get_the_title( $quiz_id ) : ''
    ];
    $replace_message = str_replace( array_keys( $replace_tags ), array_values( $replace_tags ), $message );
    return $replace_message;
}

/**
 * Wp exams taxonomies html
 */
function exms_add_taxonomies_tags_category( $taxonomy ) {

    $taxonomy_name = '';
    if( 'categories' == $taxonomy ) {
        $taxonomy_name = str_replace( 'ies', 'y', $taxonomy );
    } else if( 'tags' == $taxonomy ) {
        $taxonomy_name = str_replace( 's', '', $taxonomy );
    }

    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    $taxonomies = isset( $_GET['taxo_type'] ) ? $_GET['taxo_type'] : 'exms-quizzes_' .$taxonomy;
    // $cat_terms = get_terms( $taxonomies, [ 'hide_empty' => 0 ] );
    $post_type = str_replace( '_'.$tab, '', $taxonomies );

    $parent_categories = get_terms([
        'taxonomy'   => $post_type.'_'.$tab,
        'parent'     => 0,
        'hide_empty' => false,
    ] );

    $post_type_name = str_replace( 'exms-', '', $post_type ); 

?>
    <div class="exms-taxonomy-wrap">
        <div class="exms-texonomy-form">
            <div class="exms-email-settings-wrap form-table">
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                    <input type="hidden" name="exms_taxonomy_id" value="<?php echo $taxonomy; ?>">
                    <input type="hidden" name="exms_post_type" value="<?php echo $post_type; ?>">  
                    <input type="hidden" name="exms_texo_type" value="<?php echo $taxonomies; ?>">
                    <input type="hidden" name="exms_tab" value="<?php echo $tab; ?>">
                    <div class="exms-settings-container exms-tags-settings">
                        <div class="exms-settings-row exms-settings-tab-row">
                            <div class="exms-setting-lable exms-settings-tab-label">
                                <label><?php _e( 'Name :', 'exms' ); ?></label>
                            </div>
                            <div class="exms-setting-data exms-settings-tab">
                                <input required="required" type="text" placeholder="<?php _e( 'Enter '.$taxonomy_name.' name', 'exms' ); ?>" name="exms_taxonomy_name" value="">
                                <p class="exms-instruction-message"><?php _e( 'The name is how it appear on your site.' ); ?></p>
                            </div>
                        </div>
                        <div class="exms-settings-row exms-settings-tab-row">
                            <div class="exms-setting-lable exms-settings-tab-label">
                                <label><?php _e( 'Slug :', 'exms' ); ?></label>
                            </div>
                            <div class="exms-setting-data exms-settings-tab">
                                <input type="text" name="exms_taxonomy_slug" placeholder="<?php _e( 'Enter '.$taxonomy_name.' slug', 'exms' ); ?>">
                                <p><?php exms_add_info_title( 'The "slug" is the URL friendly version of the name.' ); ?></p>
                            </div>
                        </div>
                        <?php 
                        if( 'tags' != $tab ) {
                            ?>
                            <div class="exms-settings-row exms-settings-tab-row">
                                <div class="exms-setting-lable exms-settings-tab-label">
                                    <label><?php _e( 'Parent '.$taxonomy_name.' :', 'exms' ); ?></label>
                                </div>
                                <div class="exms-setting-data exms-settings-tab">
                                    <select name="exms_taxonomy_parent">
                                        <option><?php _e( 'Select a Parent Category', 'exms' ); ?></option>
                                        <?php 
                                        if(  $parent_categories ) {
                                            foreach( $parent_categories as $cat ) {
                                                ?>      
                                                <option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
                                                <?php    
                                            }   
                                        }?>
                                    </select>
                                    <p class="exms-instruction-message"><?php _e( 'Assign a parent term to create a hierarchy.' ); ?></p>
                                </div>
                            </div>
                            <?php 
                        }
                        ?>
                        <div class="exms-settings-row exms-settings-tab-row">
                            <div class="exms-setting-lable exms-settings-tab-label">
                                <label><?php _e( 'Description :', 'exms' ); ?></label>
                            </div>
                            <div class="exms-setting-data exms-settings-tab">
                                <textarea name="exms_taxonomy_discription"></textarea>
                                <p><?php exms_add_info_title( 'The description is not prominent by default.' ); ?></p>
                            </div>
                        </div>
                        <div class="exms-settings-row exms-settings-tab-row">
                            <div class="exms-setting-lable exms-settings-tab-label">
                                <label>
                                    <?php wp_nonce_field( 'exms_taxonomy_nonce', 'exms_taxonomy_nonce_field' ); ?>
                                    <input class="button-primary" type="submit" name="exms_submit_taxonomies" value="<?php _e( 'Add New ' . ucwords( $taxonomy ), 'exms' ); ?>">
                                    <input type="hidden" name="action" value="exms_add_taxonomies">
                                </label>
                            </div>
                            <div class="exms-setting-data exms-settings-tab"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="exms-taxonomy-data-table">
            <?php 
            if( file_exists( EXMS_DIR . 'includes/admin/settings/tabs/taxonomy-data-table.php' ) ) {

                require_once EXMS_DIR . 'includes/admin/settings/tabs/taxonomy-data-table.php';
            }?>
        </div>
    </div>
<?php
}

/**
 * create a function to get option settings
 * 
 * @param $key
 */
function exms_get_option_settings( $key ) {

    $settings = get_option( 'exms_settings' );
    $settings = isset( $settings[$key] ) ? $settings[$key] : '';
    return $settings; 
}

/**
 * Get user's role by id
 * 
 * @param $user_id
 */
function exms_get_user_role_by_id( $user_id ) {

    global $wpdb;

    $user_role = get_user_meta( $user_id, $wpdb->prefix.'capabilities', true ) ? get_user_meta( $user_id, 'wp_capabilities', true ) : [];
    if( !empty( $user_role ) ) {
        $user_role = array_keys( $user_role );
        return $user_role;
    }
    
    return false;
}