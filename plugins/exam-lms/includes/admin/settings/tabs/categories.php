<?php
/**
 * Manage category taxonomy using a singleton class structure
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Categories {

    /**
     * @var self
     */
    private static $instance;
    private $categories_page = false;

    /**
     * instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Categories ) ) {
            self::$instance = new EXMS_Categories;

            if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'categories' ) {
                self::$instance->categories_page = true;
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Register hooks
     */
    private function hooks() {

        $this->manage_category_taxonomy();
    }

    /**
     * Main function to handle category taxonomy setup
     */
    private function manage_category_taxonomy() {

        if( !$this->categories_page ) {
            return false;
        }
        
        $tabs = $this->get_tabs();

        exms_create_submenu_tabs( $tabs, [
            'taxonomy'       => 'categories',
            'taxonomy_frame' => true
        ]);

        exms_add_taxonomies_tags_category( 'categories' );
    }

    /**
     * Generate tabs for taxonomy submenu
     * 
     * @return array
     */
    private function get_tabs() {
        $tabs = [
            'exms-quizzes'   => 'Quizzes',
            'exms-questions' => 'Questions',
            'exms-groups'    => 'Groups'
        ];

        $post_types_setup = EXMS_Setup_Functions::get_setup_post_types();

        if ( is_array( $post_types_setup ) ) {
            $post_types = array_keys( $post_types_setup );

            foreach ( $post_types as $post_type ) {
                $tabs[ $post_type ] = ucwords( str_replace( [ 'exms_', 'exms-' ], '', $post_type ) );
            }
        }

        return $tabs;
    }
}

EXMS_Categories::instance();
