<?php
/**
 * WP EXAMS - Admin User Profile Page
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class EXMS - Edit Profile
 *
 * Add options on admin user profile page
 */
class EXMS_Edit_Profile {
    
    private static $instance;

    /**
     * Connect to wpdb
     */
    private static $wpdb;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Edit_Profile ) ) {

            self::$instance = new EXMS_Edit_Profile;
            self::$instance->hooks();

            global $wpdb;
            self::$wpdb = $wpdb;
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    public function hooks() {

        add_action( 'show_user_profile', [ $this, 'exms_user_edit_profile'] );
        add_action( 'edit_user_profile', [ $this, 'exms_user_edit_profile'] );
        add_action( 'personal_options_update', [ $this, 'exms_save_assign_posts' ], 10, 1 );
        add_action( 'edit_user_profile_update', [ $this, 'exms_save_assign_posts' ], 10, 1 );
    }

    /**
     * Save assign quizzes to exms_assign_quizzes table
     * 
     * @param $user_id
     */
    public function exms_save_assign_posts( $user_id ) {

        if( isset( $_POST['exms_completed_steps'] ) ) {

            $course_steps = $_POST['exms_completed_steps'];

            foreach( $course_steps as $course_step ) {
                foreach( $course_step as $post_id => $steps ) {
                    foreach( $steps as $parent_ids => $is_checked  ) {

                        if( get_post_type( $post_id ) == 'exms_quizzes' ) {

                            $is_completed = exms_is_quiz_complete_with_parents( $user_id, $post_id, $parent_ids );
                            if( 'on' == $is_checked && ! $is_completed ) {
                                
                                echo exms_quiz_mark_complete( $user_id, $post_id, true, $parent_ids );
                            }

                            if( 'off' == $is_checked && $is_completed ) {

                                echo exms_quiz_mark_incomplete( $user_id, $post_id, $parent_ids );
                            }
                            
                        } else {

                            $is_completed = exms_is_post_completed( $post_id, $parent_ids, $user_id );
                            if( 'on' == $is_checked && ! $is_completed ) {
                                
                                echo exms_post_mark_complete( $post_id, $parent_ids, $user_id );
                            }

                            if( 'off' == $is_checked && $is_completed ) {

                                echo exms_post_mark_incomplete( $post_id, $parent_ids, $user_id );
                            }
                        }
                    }
                }
            }
        }

        $table_name = EXMS_PR_Fn::exms_user_post_relations_table();
        $assign_ids = isset( $_POST['exms_assign_items']['current'] ) ? $_POST['exms_assign_items']['current'] : [];

        /* Deleted un assing meta on parent post relation */
        $unassign_ids = isset( $_POST['exms_unassign_items']['current'] ) ? $_POST['exms_unassign_items']['current'] : [];

        if( $unassign_ids && is_array( $unassign_ids ) ) {

            foreach( $unassign_ids as $unassign_id ) {

                exms_unenroll_user_on_post( $user_id, $unassign_id );
            }

            /**
             * Fires after the elements un-assigned successfully
             * 
             * @param $unassign_ids ( Ids of the un-assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $unassign_ids, false );
        }
        /* End deleted un assing meta on parent post relation */

        /* Insert or update un assign meta on parent relation */
        if( $assign_ids && is_array( $assign_ids ) ) {
            foreach( $assign_ids as $order => $assign_id ) {

                $quiz_ids = [];
                $relation_meta = self::$wpdb->get_results( " SELECT post_id FROM $table_name 
                    WHERE user_id = $user_id AND post_id = $assign_id " );
                if( ! empty( $relation_meta ) && ! is_null( $relation_meta ) ) {
                    $quiz_ids = array_map( 'intval', array_column( $relation_meta, 'post_id' ) );
                }

                if( in_array( $assign_id, $quiz_ids ) ) {
                    continue;
                }

                echo exms_enroll_user_on_post( $user_id, $assign_id );
            }

            /**
             * Fires after the elements assigned successfully
             * 
             * @param $assign_id ( Ids of the assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $assign_ids, true );
        }

        /* End insert or update un assign meta on parent relation */
    }
    
    /**
     * Display options table on admin user profile page
     */
    public function exms_user_edit_profile( $user ) {
        
        $user_id = intval( $user->ID );

        ?>
        <hr>
        <?php
        $points = function_exists( 'exms_get_user_all_points' ) ? exms_get_user_all_points( $user_id ) : [];
        $points_html = isset( $points['html'] ) ? $points['html'] : 'No point type created';
        $enrolled_quizzes = function_exists( 'exms_get_user_enrolled_quizzes' ) ? exms_get_user_enrolled_quizzes( $user_id ) : [];
        
        $eq_html = '';
      
        if( $enrolled_quizzes ) {

            foreach( $enrolled_quizzes as $quiz_id ) {

                $status_text = exms_get_user_quiz_status( $user_id, $quiz_id );
                $status_text = 'completed' == strtolower( $status_text ) ? '<span class="dashicons dashicons-saved exms-prg-icon"></span>Completed' : '<span class="dashicons dashicons-hourglass exms-prg-icon"></span>In Progress';
                $quiz_title = ucwords( get_the_title( $quiz_id ) );
                $eq_html .= '<div class="exms-row exms-admin-q-row exms-50">'.$quiz_title.' <div class="exms-row-icon">'.$status_text.'</div></div>';
            }
        } else {
            $eq_html .= '<div class="exms-row-icon">'.__( 'No quizzess yet', WP_EXAMS ).'</div>';
        }
        ?>
        <hr>

        <?php
        if( current_user_can( 'manage_options' ) ) {
            
            /* Assign and un-assign quizzes to the user */ 
            echo exms_post_assign_to_user_html( $user_id, 'exms_quizzes' );
            /* /Assign and un-assign quizzes to the user */

            /* Assign and un-assign parent post to the user */
            echo exms_post_assign_to_user_html( $user_id, EXMS_PR_Fn::exms_get_parent_post_type() );
            /* /Assign and un-assign parent post to the user */
        }

        ob_start();

        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label><?php echo __( 'Point Types', WP_EXAMS ); ?></label></th>
                    <td><?php echo $points_html; ?></td>
                </tr>
                <tr>
                    <th><label><?php echo __( 'Enrolled Quizzes', WP_EXAMS ); ?></label></th>
                    <td><?php echo $eq_html; ?></td>
                </tr>
            </tbody>
        </table>

        <?php
        echo EXMS_Progress_Detail::exms_progressions_html( $user_id );

        $content = ob_get_contents();
        ob_get_clean();

        echo $content;
    }
}

/**
 * Initialize EXMS_Edit_Profile
 */
EXMS_Edit_Profile::instance();