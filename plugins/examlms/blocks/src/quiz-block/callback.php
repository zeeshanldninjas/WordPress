<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * WP_QUIZ_Block
 */
class WP_QUIZ_Block {
    
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && ! ( self::$instance instanceof WP_QUIZ_Block ) ) {
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
        
        $args['category'] = 'exms-exams-blocks';
        $args['attributes'] = array(
                            "quiz_id" => array(
                                "type" => "string",
                                "default" => ""
                            )
                        );
                       
        register_block_type( EXMS_DIR . 'blocks/build/quiz-block', $args );
    }

    /**
     * Plugin Constants
     */
    private function hooks() {
        
        add_action( 'init', [ $this, 'block_init' ] );
    }
}

WP_QUIZ_Block::instance();