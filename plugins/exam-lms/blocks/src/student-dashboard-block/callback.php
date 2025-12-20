<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * EXMS_STUDENT_DASHBOARD_Blocks
 */
class EXMS_STUDENT_DASHBOARD_Blocks {
    
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_STUDENT_DASHBOARD_Blocks ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }
        
        return self::$instance;
    }

    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see https://developer.wordpress.org/reference/functions/register_block_type/
     */
    function block_init() {
        $args = array();
        
        //$args['render_callback'] = 'ldngt_render_spacer_block';
        $args['category'] = 'exms-exams-blocks';
        $args['attributes'] = array(
                            "userid" => array(
                                "type" => "string",
                                "default" => ""
                            )
                        );
        register_block_type( EXMS_DIR . 'blocks/build/student-dashboard-block', $args );
    }
    
    /**
     * Plugin Constants
     */
    private function hooks() {
        
        add_action( 'init', [ $this, 'block_init' ] );
    }
}

EXMS_STUDENT_DASHBOARD_Blocks::instance();