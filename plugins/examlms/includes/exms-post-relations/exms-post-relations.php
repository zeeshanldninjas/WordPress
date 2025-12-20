<?php

/**
 * Template for EXMS Post Relations
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Post_Relations {

    /**
     * @var self
     */
    private static $instance;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Post_Relations ) ) {

            self::$instance = new EXMS_Post_Relations;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {}

    /**
     * User assign to only parent post metabox html
     * 
     * @param $post
     */
    public static function exms_user_assign_to_parent_html( $post ) {

        echo exms_user_assign_to_post_html( $post );
    }

    /**
     * Added Quiz Relation metabox html
     * 
     * @param $post
     */
    public static function exms_quiz_relation_metabox_html( $post ) {

        echo exms_current_assign_post_html( $post, 'exms_quizzes' );
    }

    /**
     * Post parent relations metabox content html
     * 
     * @param $post
     */
    public static function exms_post_parent_relation_html( $post ) {

        $post_id = $post->ID;
        $post_type = get_post_type( $post_id );

        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        $parent_meta = isset( $post_types[$post_type] ) ? $post_types[$post_type] : '';
        $parent_post_type = isset( $parent_meta['parent_post_type'] ) ? $parent_meta['parent_post_type'] : '';

        echo exms_parent_assign_post_html( $post, $parent_post_type );
    }

    /**
     * Post Assign and unassign metabox content HTML
     * 
     * @param $post ( Object )
     */
    public static function exms_post_relation_metabox_html( $post ) {

        $post_id = $post->ID;
        $post_type = get_post_type( $post_id );
        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        $child_post_type = isset( $post_types[$post_type] ) ? $post_types[$post_type] : '';
        $post_count = 0;

        $is_empty = 0;

        if( $post_types && is_array( $post_types ) ) {
            foreach( $post_types as $post_name => $setup_post_type ) {

                $parent_post_type = isset( $setup_post_type['parent_post_type'] ) ? $setup_post_type['parent_post_type'] : '';
                if( $post_type != $parent_post_type ) {
                    continue;
                }

                $child_post_type = isset( $setup_post_type['post_type_name'] ) ? $setup_post_type['post_type_name'] : '';
                
                echo exms_current_assign_post_html( $post, $child_post_type );

                $is_empty++;
            }
        }

        if( $is_empty == 0 ) {

            ?>
            <div class="exms-error-notification">
                <?php
                echo __( 'The '.ucwords( str_replace( [ 'exms-', 'exms_' ], [ '', '' ], $post_type ) ).' does no have any child Relation' );
                ?>
            </div>
            <?php
        }
    }
}

EXMS_Post_Relations::instance();