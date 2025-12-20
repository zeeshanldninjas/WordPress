<?php
/**
 * Template to display [exms_instructor] shortcode content
 *
 * This template can be overridden by copying it to yourtheme/wp-exams/shortcodes/exms-instructor-shortcode.php.
 *
 * @param $atts     All shortcode attributes
 *
 */

if( ! defined( 'ABSPATH' ) ) exit;

if( ! is_user_logged_in() ) {
    return __( 'You need to loggin to access this page', 'exms' );
}

$instructor_id = isset( $atts['id'] ) ? ( int ) $atts['id'] : get_current_user_id();
$limit = isset( $atts['limit'] ) ? ( int ) $atts['limit'] : 10;

/* ALso check for group leader progress..... */
if ( ! current_user_can( 'manage_options' ) ) {
    return __( 'You need to be a group leader to access this page', 'exms' );
}

$search_users = isset( $_POST['exms_search_users'] ) ? array_map( 'intval', $_POST['exms_search_users'] ) : [];
$quizzes = exms_get_quiz_ids_has_users( $limit, 0, $search_users );
if( empty( $quizzes ) ) {
    return __( 'The Quizzes has no assign users', 'exms' );
}

$total_quizzes = intval( count( exms_get_quiz_ids_has_users( '', 0 ) ) );
$total_pages = intval( ceil( $total_quizzes / $limit ) );

$hide_next_button = '';
if( $total_pages == 1 ) {
    $hide_next_button = 'exms-inst-hide-btn';
}

?>
<div class="exms-wrapper">
    <img src="<?php echo EXMS_ASSETS_URL.'imgs/ajax-loader.gif' ?>" class="exms-inst-loader" />
    <form method="post">
        <div class="exms-instructor-select2">
            <select multiple name="exms_search_users[]" class='exms-inst-select2' id='student'>
                <?php 
                if( $search_users && is_array( $search_users ) ) {
                    foreach( $search_users as $search_user ) {

                        ?>
                        <option value="<?php echo $search_user; ?>" <?php echo selected( true, true, true ); ?> >
                            <?php echo ucwords( exms_get_user_name( $search_user ) ); ?>
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
            <button class="exms-search"><?php _e( 'Search', 'exms' ); ?></button>
        </div>

        <div class="exms-instructor-table">
            <table class="load-more">
                <thead class="exms-inst-table-head">
                    <tr>
                        <th class="exms-heading"><?php echo __( 'Student Name', 'exms' ); ?></th>
                        <th class="exms-heading"><?php echo __( 'Quiz Name', 'exms' ); ?></th>
                        <th class="exms-heading"><?php echo __( 'Quiz Status', 'exms' ); ?></th>
                        <th class="exms-date exms-heading"><?php echo __( 'Enroll Date', 'exms' ); ?></th>
                        <th class="exms-heading"><?php echo __( 'Complete Date', 'exms' ); ?></th>
                        <th class="exms-heading"><?php echo __( 'Action', 'exms' ); ?></th>
                    </tr>
                </thead>
                <tbody class="exms-inst-table-body">
                    <?php echo WP_Exam_Shortcodes::exms_inst_table_data( $quizzes ); ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination html -->

        <?php 
        if( empty( $search_users ) ) {

            ?>
            <div class="exms-ins-paginations" data-page="0" data-limit="<?php echo $limit; ?>">
                <div class="exms-ins-back-btn exms-ins-paginate" data-target="back">
                    <span class="dashicons dashicons-arrow-left-alt2 ins-direction-btn"></span>
                </div>
                <div class="exms-ins-paged">
                    <span class="exms-inst-current-page">1</span>
                    <span class="exms-inst-out-off"><?php _e( 'Of', 'exms' ); ?></span>
                    <span class="exms-inst-total-page"><?php echo $total_pages; ?></span>
                </div>
                <div class="exms-ins-next-btn exms-ins-paginate <?php echo $hide_next_button; ?>" data-target="next">
                    <span class="dashicons dashicons-arrow-right-alt2 ins-direction-btn"></span>
                </div>
            </div>
            <?php
        }   
        ?>
        <!-- Pagination html -->

    </form>
</div>