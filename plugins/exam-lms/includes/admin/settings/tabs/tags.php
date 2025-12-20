<?php
/**
 * Manage tags taxonomy
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Tags {

    /**
     * @var self
     */
    private static $instance;
    private $tags_page = false;

    /**
     * Instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Tags ) ) {
            self::$instance = new EXMS_Tags;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'tags' ) {
                self::$instance->tags_page = true;
            }
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        $this->manage_tags_taxonomy();
    }

    /**
     * Main function to handle category taxonomy setup
     */
    private function manage_tags_taxonomy() {

        if ( ! $this->tags_page ) {
            return false;
        }

        $tabs = [ 
            'exms-quizzes'   => 'Quizzes', 
            'exms-questions' => 'Questions', 
            'exms-groups'    => 'Groups' 
        ];

        $post_types_setup = EXMS_Setup_Functions::get_setup_post_types();
        $post_types_setup = is_array( $post_types_setup ) ? $post_types_setup : [];
        $post_types = array_keys( $post_types_setup );

        foreach ( $post_types as $post_type ) {
            $tabs[ $post_type ] = ucwords( str_replace( [ 'exms_', 'exms-' ], '', $post_type ) );
        }

        exms_create_submenu_tabs( 
            $tabs, 
            [ 
                'taxonomy'       => 'tags', 
                'taxonomy_frame' => true 
            ] 
        );
        exms_add_taxonomies_tags_category( 'tags' );
    }

}

EXMS_Tags::instance();