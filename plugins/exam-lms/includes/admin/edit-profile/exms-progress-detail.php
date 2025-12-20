<?php

/**
 * Template for Parent post settings
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Progress_Detail {

    /**
     * @var self
     */
    private static $instance;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Progress_Detail ) ) {

            self::$instance = new EXMS_Progress_Detail;

            global $wpdb;
            self::$wpdb = $wpdb;
        }

        return self::$instance;
    }

    /**
     * Progress detail html content
     * 
     * @param $user_id
     */
    public static function exms_progressions_html( $user_id ) {

        $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
        $table_name = EXMS_PR_Fn::exms_user_post_relations_table();

        $relations = self::$wpdb->get_results( "
            SELECT post_id FROM $table_name
            WHERE post_type = '$parent_post_type'
            AND user_id = $user_id
        " );

        if( empty( $relations ) || is_null( $relations ) ) {

            return false;
        }

        $parent_posts = array_map( 'intval', array_column( $relations, 'post_id' ) );

        ob_start();

        ?>
        <div class="exms-progress-detail-wrap">
            <h3><?php echo ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $parent_post_type ) ) .__( ' Progress Detail:', 'exms' ) ?></h3>
            <?php 
            foreach( $parent_posts as $parent_post_id ) {

                $parent_post_type = get_post_type( $parent_post_id );
                $relation_ids = EXMS_PR_Fn::exms_child_relation_ids( $parent_post_id, $parent_post_type );

                $class = '';
                if( ! empty( $relation_ids ) && is_array( $relation_ids ) ) {
                    $class = 'exms-progress-icon';
                }

                $all_are_completed = exms_is_childs_completed( $parent_post_id, $relation_ids );

                ?>
                <div class="exms-progress-post-wrap">
                    <div class="exms-progress-content">
                        <a class="exms-progress-item exms-progress-title" href="<?php echo get_permalink( $parent_post_id ); ?>"><?php echo get_the_title( $parent_post_id ); ?></a>
                        <span>-----------------------------------------------------</span>
                        <a class="exms-progress-item exms-progress-edit-wrap" href="<?php echo get_edit_post_link( $parent_post_id ); ?>"><?php echo __( 'edit', 'exms' ); ?></a>
                        <input type="button" class="exms-progress-item exms-progress-detail <?php echo $class; ?>" value="<?php echo __( '(detail)', 'exms' ); ?>" />
                    </div>
                    <div class="exms-clear-both"></div>

                    <div class="exms-progress-post-check exms-progress-content">
                        <input class="exms-progress-item exms-progress-check-box exms-all-completed" name="" type="checkbox" data-post-id="<?php echo $parent_post_id; ?>" data-parent="0" <?php echo checked( $all_are_completed ); ?>>
                        <span><?php echo __( 'Complete all ', 'exms' ).str_replace( 'exms-', '', $parent_post_type ); ?></span>
                    </div>
                    <?php 

                    $parent_id = 0; 
                    $grand_parent = 0; 
                    $parent_head = 0;
                    $parent_head2 = 0;
                    if( ! empty( $relation_ids ) && is_array( $relation_ids ) ) {
                        echo self::$instance->exms_relation_items_html( $user_id, $relation_ids, $parent_post_id, $parent_id, $grand_parent, $parent_head, $parent_head2 );
                    }
                    ?>

                </div>
                <?php
            }
            ?>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Generate dynamic html of child posts
     * 
     * @param $relation_ids
     */
    public function exms_relation_items_html( $user_id, $relation_ids, $post_id, $parent_id, $grand_parent, $parent_head, $parent_head2 ) {

        foreach( $relation_ids as $index => $relation_id ) {

            $relation_post_type = get_post_type( $relation_id );
            if( ! post_type_exists( $relation_post_type ) ) {
                continue;
            }

            if( 'exms_questions' == $relation_post_type ) {
                continue;
            }

            $progress_padding = '';
            if( $parent_id == 0 ) {
                $progress_padding = 'padding: unset';
            }

            if( empty( $parent_head ) ) {
                $parent_head = $post_id;
            }

            $link_ids = $parent_id.'-'.$grand_parent.'-'.$parent_head.'-'.$parent_head2;
            $link_ids = array_filter( array_unique( explode( '-', $link_ids ) ) );
            $parent_ids = implode( '-', $link_ids );

            $is_completed = false;
            if( $relation_post_type == 'exms_quizzes' ) {

                $is_completed = exms_is_quiz_complete_with_parents( $user_id, $relation_id, $parent_ids );

            } else {

                $is_completed = exms_is_post_completed( $relation_id, $parent_ids, $user_id );
            }

            $class = '';
            $relation_ids = EXMS_PR_Fn::exms_child_relation_ids( $relation_id, $relation_post_type );
            if( ( ! empty( $relation_ids ) && is_array( $relation_ids ) ) && 'exms_quizzes' != $relation_post_type ) {
                $class = 'exms-progress-icon';
            }
                
            $step_completed_name = 'exms_completed_steps[]['.$relation_id.']['.$parent_ids.']';

            ?>
            <div class="exms-progress-post-wrap exms-progress-wrap-padding" style="<?php echo $progress_padding; ?>">
                <div class="exms-progress-content">
                    <img src="<?php echo EXMS_ASSETS_URL.'imgs/gray_arrow_collapse.png'; ?>" data-src="collapse" data-assets-url="<?php echo EXMS_ASSETS_URL; ?>" class="exms-progress-item <?php echo $class; ?>">
                    <input type="hidden" id="status_1_" name="<?php echo $step_completed_name; ?>"  value="off">
                    <input class="exms-progress-item exms-progress-check-box" id="status_1_" name="<?php echo $step_completed_name; ?>" type="checkbox" value="on" data-post-id="<?php echo $relation_id; ?>" data-parent="<?php echo $parent_ids; ?>" <?php echo checked( $is_completed ); ?> >
                    <a class="exms-progress-item" href="<?php echo exms_step_permalinks( $relation_id, $parent_ids ); ?>" ><?php echo get_the_title( $relation_id ); ?></a>
                </div>
                <div class="exms-clear-both"></div>

                <?php 
                if( ! empty( $relation_ids ) && is_array( $relation_ids ) ) {
                    echo self::$instance->exms_relation_items_html( $user_id, $relation_ids, $post_id, $relation_id, $parent_id, $grand_parent, $parent_head );
                }
                ?>
            </div>
            <?php
        }
    }
}

EXMS_Progress_Detail::instance();