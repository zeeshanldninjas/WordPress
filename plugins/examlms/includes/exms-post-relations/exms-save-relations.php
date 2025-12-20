<?php

/**
 * Template for Save post relations data
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Save_Relations {

    /**
     * @var self
     */
    private static $instance;

    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Save_Relations ) ) {

            self::$instance = new EXMS_Save_Relations;

            global $wpdb;
            self::$wpdb = $wpdb;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        //add_action( 'save_post', [ $this, 'exms_save_post_relations_to_db' ], 10, 3 );
        //add_action( 'save_post', [ $this, 'exms_save_post_parent_relations' ], 11, 3 );
    }

    /**
     * Save post parent relationships to db
     * 
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function exms_save_post_parent_relations( $post_id, $post, $update ) {

        if( $update != true ) {
            return false;
        }

        $post_type = get_post_type( $post_id );
        $table_name = EXMS_PR_Fn::exms_relation_table_name();
        
        $assign_ids = isset( $_POST['exms_assign_items']['parent'] ) ? $_POST['exms_assign_items']['parent'] : [];
        $parent_post_type = isset( $_POST['exms_parent_relation'] ) ? $_POST['exms_parent_relation'] : '';

        /* Deleted un assing meta on parent post relation */
        $unassign_ids = isset( $_POST['exms_unassign_items']['parent'] ) ? $_POST['exms_unassign_items']['parent'] : [];
        if( $unassign_ids && is_array( $unassign_ids ) ) {
        
            self::$wpdb->query( 
                "DELETE FROM $table_name
                 WHERE relation_id = $post_id 
                 AND post_id IN('" . implode( "', '", $unassign_ids ) . "') "
            );

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

                $assign_post_type = get_post_type( $assign_id );

                $relation_ids = '';
                $relation_meta = self::$wpdb->get_results( " SELECT post_id FROM $table_name 
                    WHERE post_id = $assign_id AND relation_id = $post_id " );
                if( ! empty( $relation_meta ) && ! is_null( $relation_meta ) ) {
                    $relation_ids = array_map( 'intval', array_column( $relation_meta, 'post_id' ) );
                }

                if( ! empty( $relation_ids ) ) {

                    self::$wpdb->query( self::$wpdb->query( self::$wpdb->prepare( "UPDATE $table_name SET menu_order = %d WHERE post_id = %d", 0, $assign_id ) ) );

                } else {

                    echo EXMS_PR_Fn::exms_insert_post_relation( $assign_id, $assign_post_type, $post_id, $post_type, time(), 0 );
                }
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
     * Save post relationships to db
     * 
     * @param $post_id
     * @param $post
     * @param $update
     */
    public function exms_save_post_relations_to_db( $post_id, $post, $update ) {

        if( $update != true ) {
            return false;
        }

        $post_type = get_post_type( $post_id );
        $table_name = EXMS_PR_Fn::exms_relation_table_name();

        $assign_ids = isset( $_POST['exms_assign_items']['current'] ) ? $_POST['exms_assign_items']['current'] : [];

        /* Delete un assign meta on current post relation */
        $unassign_ids = isset( $_POST['exms_unassign_items']['current'] ) ? $_POST['exms_unassign_items']['current'] : [];

        if( $unassign_ids && is_array( $unassign_ids ) ) {
        
            self::$wpdb->query( 
                "DELETE FROM $table_name
                 WHERE post_id = $post_id 
                 AND relation_id IN('" . implode( "', '", $unassign_ids ) . "') "
            );

            /**
             * Fires after the elements un-assigned successfully
             * 
             * @param $unassign_ids ( Ids of the un-assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $unassign_ids, false );
        }
        /* End delete un assign meta on current post relation */

        /* Insert or update un assign meta on current relation */
        if( $assign_ids && is_array( $assign_ids ) ) {
            foreach( $assign_ids as $order => $assign_id ) {

                $assign_post_type = get_post_type( $assign_id );

                $relation_ids = '';
                $relation_meta = self::$wpdb->get_results( " SELECT relation_id FROM $table_name 
                    WHERE relation_id = $assign_id AND post_id = $post_id " );
                if( ! empty( $relation_meta ) && ! is_null( $relation_meta ) ) {
                    $relation_ids = array_map( 'intval', array_column( $relation_meta, 'relation_id' ) );
                }

                if( ! empty( $relation_ids ) ) {

                    self::$wpdb->query( self::$wpdb->query( self::$wpdb->prepare( "UPDATE $table_name SET menu_order = %d WHERE relation_id = %d", 0, $assign_id ) ) );

                } else {

                    echo EXMS_PR_Fn::exms_insert_post_relation( $post_id, $post_type, $assign_id, $assign_post_type, time(), 0 );
                }
            }
            
            /**
             * Fires after the elements assigned successfully
             * 
             * @param $assign_id ( Ids of the assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $assign_ids, true );
        }

        /* End insert or update un assign meta on current relation */
    }
}

EXMS_Save_Relations::instance();