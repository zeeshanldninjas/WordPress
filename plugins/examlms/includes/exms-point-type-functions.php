<?php
/**
 * WP Exams Point Type functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Award points to user
 *
 * @param   Mixed     User id
 * @param             awarded_points ( number of points to be awarded )
 * @param   Mixed     Point type slug or ID 
 */
function exms_award_points_to_user( $user_id, $awarded_points, $point_type ) {

    $point_type = $point_type && isset( get_post( $point_type )->post_name ) ? get_post( $point_type )->post_name : $point_type;
    $points = exms_get_points( $user_id, $point_type );
    $points += $awarded_points;
    update_user_meta( $user_id, 'exms_points_' . $point_type, $points );
    return $points;
}

/**
 * Deduct points to user
 *
 * @param   Mixed     User id
 * @param             number of points to be deducted
 * @param   Mixed     Point type slug or ID 
 */
function exms_deduct_points_to_user( $user_id, $deduct_points, $point_type ) {

    $point_type = $point_type && isset( get_post( $point_type )->post_name ) ? get_post( $point_type )->post_name : $point_type;
    $points = exms_get_points( $user_id, $point_type );
    
    $points = $points - $deduct_points;

    update_user_meta( $user_id, 'exms_points_' . $point_type, $points );
    return $points;
}

/**
 * Get user points
 *
 * @param   Mixed     User id
 * @param   Mixed     Point type slug or ID 
 */
function exms_get_points( $user_id, $point_type ) {

    $point_type = $point_type && isset( get_post( $point_type )->post_name ) ? get_post( $point_type )->post_name : $point_type;
	$points = ( float ) get_user_meta( $user_id, 'exms_points_'.$point_type, true );
	return $points;
}

/**
 * Get user completed quizzes
 *
 * @param Mixed     $user_id    User id 		
 */
function exms_get_user_all_points( $user_id ) {

    ?>
    <!-- Added WP Exams logo at user edit profile page -->
    <div class="exms-logo">    
        <span class="dashicons dashicons-welcome-learn-more exms-wp-logo"></span> 
        <span class="exms-ep-title"><?php echo __( 'WP Exams', WP_EXAMS ); ?></span>
    </div>

    <?php
	$point_types = get_posts( array(
        'post_type'     => 'exms_points',
        'numberposts'   => -1,
        'post_status'   => 'publish',
        'fields'        => 'ids'
    ) );

    ob_start();

    $all_user_points = [];

    if( $point_types && is_array( $point_types ) ) {
        foreach( $point_types as $point_type_id ) {

            $point_type_name = get_the_title( $point_type_id );
            $get_balance = exms_get_user_points( $user_id, $point_type_id );
            $all_user_points[$point_type_name] = $get_balance;

            ?>
            <div class="exms-ptype-wrapper exms-pt-type-box">
                <div class="exms-point-type-box" data-point-balance="<?php echo $get_balance; ?>">
                    <p><?php echo ucwords( $point_type_name ); ?></p>
                    <div class="exms-balance">
                        <span class="exms-display-thumbnail">
                            <img src="<?php echo get_the_post_thumbnail_url( $point_type_id ); ?>" class="exms-thumbnail-img">
                        </span>
                        <span class="exms-display-point-balance"><?php echo $get_balance; ?></span>
                    </div>

                    <?php 
                    if( is_admin() ) {

                        ?>
                        <div type="button" href="#" class="exms-profile-point-toggle"><?php echo __( 'Edit', WP_EXAMS ); ?></div>
                        <input type="hidden" class="exms-point-type" value="<?php echo $point_type_id; ?>">
                        <div class="exms-new-balance">
                            <div class="exms-balance-title"><?php echo __( 'New balance', WP_EXAMS ); ?></div>
                            <input type="number" name="exms-new-balance" class="new-balance" value="<?php echo $get_balance; ?>">
                            <input type="hidden" name="exms-user-id" value="<?php echo $user_id; ?>" class="exms-current-id" />
                            <div class="new-balance-save">
                                <input type="button" name="exms-save" value="<?php echo __( 'Save', WP_EXAMS ); ?>" class="button button-primary exms-save">
                                <input type="button" name="exms-cancel" value="<?php echo __( 'Cancel', WP_EXAMS ); ?>" class="button button-primary exms-cancel">
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        $post_type_html = ob_get_contents();
        ob_get_clean();

        $all_user_points['html'] = $post_type_html;

        return $all_user_points;
    }
}

/**
 * Get all user points with specific point type
 * 
 * @param $point_type
 */
function exms_get_user_points( $user_id, $point_type ) {

    global $wpdb;
    $table_name = EXMS_PR_Fn::exms_quiz_result_table_name();

    $points = [];
    $db_meta = $wpdb->get_results( " SELECT total_points FROM $table_name 
        WHERE user_id = $user_id
        AND points_type = $point_type
        GROUP BY parent_posts
        ORDER BY id DESC " );

    if( ! empty( $db_meta ) && ! is_null( $db_meta ) ) {
        $points = array_map( 'intval', array_column( $db_meta, 'total_points' ) );
    }

    return array_sum( $points );
}