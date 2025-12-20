<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * EXMS_INSTRUCTOR_Block
 */
class EXMS_INSTRUCTOR_Block {
    
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_INSTRUCTOR_Block ) ) {
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
                            "user_id" => array(
                                "type" => "string",
                                "default" => ""
                            )
                        );

        register_block_type( EXMS_DIR . 'blocks/build/instructor-block', $args );
    }
    
    /**
     * Plugin Constants
     */
    private function hooks() {
        add_action( 'init', [ $this, 'block_init' ] );
    }
}

EXMS_INSTRUCTOR_Block::instance();