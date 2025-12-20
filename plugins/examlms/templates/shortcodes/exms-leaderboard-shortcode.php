<?php
/**
 * Template to display [exms_leaderboard] shortcode content
 *
 * This template can be overridden by copying it to yourtheme/wp-exams/shortcodes/exms-leaderboard-shortcode.php.
 *
 * @param $atts     All shortcode attributes
 *
 */
if( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$quiz_id = isset( $atts['quiz_id'] ) ? $atts['quiz_id'] : false;
$from_attr = isset( $atts['from'] ) ? $atts['from'] : false;
$to_attr = isset( $atts['to'] ) ? $atts['to'] : false;
$where_keyword = $quiz_id || $from_attr ? 'WHERE' : '';
$between = '';
$today_date = $to_attr ? $to_attr : date( 'Y-m-d' );
$tomorrow = date( 'Y-m-d', strtotime( '+1 day' ) );
$quiz_id_filter = $quiz_id ? " quiz_id='".$quiz_id."'" : '';
$from_date_filter = '';
$and_keyword = $quiz_id ? ' AND' : '';

/**
 * Get include parameter if exits
 */
$include_user_ids = '';
$exclude_user_ids = '';
if( isset( $atts['include'] ) ) {

    $include_user_ids = explode( ',', $atts['include'] );
}

/**
 * Get exclude parameter if exits
 */
if( isset( $atts['exclude'] ) ) {

    $exclude_user_ids = explode( ',', $atts['exclude'] );
}

/**
 * Get leaderboard shortcode parameter if exits
 */
if( $from_attr ) {

    if( 'today' == $from_attr ) {

        $from_date_filter = $and_keyword." result_date BETWEEN '$today_date' AND '$tomorrow'";

    } elseif( 'yesterday' == $from_attr ) {

        $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
        $from_date_filter = $and_keyword." result_date BETWEEN '$yesterday' AND '$today_date'";

    } elseif( 'week' == $from_attr ) {

        $last_week = date( 'Y-m-d', strtotime( '-7 day' ) );
        $from_date_filter = $and_keyword." result_date BETWEEN '$last_week' AND '$today_date'";

    } elseif( 'month' == $from_attr ) {

        $last_month = date( 'Y-m-d',strtotime( '-1 month' ) );
        $from_date_filter = $and_keyword." result_date BETWEEN '$last_month' AND '$today_date'";

    } elseif( 'quarter' == $from_attr ) {

        $quarter = date('Y-m-d',strtotime( '-3 month' ) );
        $from_date_filter = $and_keyword." result_date BETWEEN '$quarter' AND '$today_date'";

    } elseif( 'year' == $from_attr ) {

        $year = date( 'Y-m-d',strtotime( '-1 year' ));
        $from_date_filter = $and_keyword." result_date BETWEEN '$year' AND '$today_date'";
    } else {

        $from_date_filter = $and_keyword." result_date BETWEEN '$from_attr' AND '$today_date'";
    }
}

$user_id_array = [];
if( isset( $_POST['students'] ) ) {

    $users = $_POST['students'];
    
    foreach( $users as $user ) {

        $user_id = get_user_by( 'login', $user );
        $user_id_array[] = ( string )$user_id->ID;
    }
}

$stds = '';
if( $user_id_array ) {

    $stds = implode( ',', $user_id_array );
}
$stds_filter = $stds ? ' user_id IN( '.$stds.' )' : '';

$stds_filter = ! $where_keyword && $stds_filter ? ' WHERE '.$stds_filter : $stds_filter;

$u_obt_points = $wpdb->get_results( "SELECT user_id, SUM(obtained_points) as obtained_points FROM {$wpdb->prefix}exms_quizzes_results $where_keyword $quiz_id_filter $from_date_filter $stds_filter GROUP BY user_id ORDER BY obtained_points DESC" );

if( isset( $_POST['students'] ) ) {

    $hide_clear_btn = 'exms-hide-clear-btn';
}

?>
<div class="exms-wrapper">
    <form method="post">

    <div class="exms-leaderboard-select2">
        <select multiple name='students[]' class="exms-select2" id='student'>
        </select>
        <button class="exms-search"><?php _e( 'Search', 'exms' ); ?></button>
        <button class="exms-clear" id="<?php echo $hide_clear_btn ?>"><?php _e( 'Clear', 'exms' ); ?></button>
    </div>

    <div class="exms-leaderboard-table">
        <table class="exms-leaderboard">
            <tr>
                <th><?php echo __( 'S.NO#' , 'exms' ); ?></th>
                <th><?php echo __( 'Students' , 'exms' ); ?></th>
                <th><?php echo __( 'Points' , 'exms' ); ?></th>
            </tr>
        <?php
        if( $u_obt_points ) {

            $sno = 1;
            foreach( $u_obt_points as $u_obt_point ) {

                $obtain_points = $u_obt_point->obtained_points;
                $id = ( int ) $u_obt_point->user_id;
                $user_name = get_userdata( $id )->data->display_name;

                if( $include_user_ids && ! in_array( $id, $include_user_ids ) || $exclude_user_ids && in_array( $id, $exclude_user_ids ) ) {

                    continue;
                }
                ?>
                <tr>
                    <td><?php echo $sno; ?></td>
                    <td><?php echo ucwords( $user_name ); ?></td>
                    <td><?php echo $obtain_points; ?></td>
                </tr>
                <?php
                $sno++;
            }

        } else{ ?>

            <tr><td colspan="3"><?php _e( 'No records found.', 'exms' ); ?></td></tr>
        <?php
              } ?>
            </table>
        </div>
    </form>
</div>