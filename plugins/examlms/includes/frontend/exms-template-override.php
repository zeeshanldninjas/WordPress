<?php

/**
 * template override 
 */
class EXMS_TEMPLATE_OVERRIDE {
    
    /**
     * Define of instance
     */
    private static $instance = null;

    /**
     * Summary of atts
     */
    private $atts = [];

    /**
      * Define the instance
     */
    public static function instance(): EXMS_TEMPLATE_OVERRIDE {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_TEMPLATE_OVERRIDE ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     *  Hooks that are used in the class
     */
    private function hooks() {
        add_filter( 'the_content', [ $this, 'exms_override_content' ] );
        add_filter( 'template_include', [$this, 'exms_custom_template_include'] );
    }

    /**
     * override templates
     */
    public function exms_override_content( $content ) {

        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        $current_post_type = get_post_type();
        $post_id = get_the_ID();

        if( is_array( $post_types ) && ! empty( $post_types ) && 'exms-quizzes' != $current_post_type ) {

            $post_types = array_column( $post_types, 'post_type_name' );
            $course_post_type = isset( $post_types[0] ) ? $post_types[0] : '';
            $lesson_post_type = isset( $post_types[1] ) ? $post_types[1] : '';

            if( $current_post_type == $course_post_type ) {
                $content = do_shortcode( '[exms_course id='.$post_id.']' );
            } elseif( $current_post_type == $lesson_post_type ) {
                $content = do_shortcode( '[exms_lesson id='.$post_id.']' );
            }
        }
        
        if( 'exms-quizzes' == $current_post_type ) {
            $content = do_shortcode( '[exms_quiz id='.$post_id.']' );
        }
        
        return $content;
    }
    

    public function exms_custom_template_include( $template ) {

        $post_type = get_post_type();

        $user_id = get_current_user_id();
        $post_id = get_the_ID();

        if( $post_type === 'exms-courses' ) {

            // $course_type = exms_get_post_settings( $post_id );
            // $course_type = isset( $course_type['parent_post_type'] ) ? $course_type['parent_post_type'] : '';
            // $user_is_assigned = exms_is_user_in_post( $user_id, $post_id );

            return EXMS_TEMPLATES_DIR . 'frontend/course/course-overview-template.php';
        }
        
        if( $post_type === 'exms-groups' ) {

            return EXMS_TEMPLATES_DIR . 'frontend/course/course-overview-template.php';
        }

        if( strpos( $post_type, 'exms-' ) === 0 && ! in_array( $post_type, [ 'exms-courses', 'exms-quizzes' ], true ) ) {
            return EXMS_TEMPLATES_DIR . 'frontend/common-steps/exms-common-template.php';
        }
        
        return $template;
    }

}

EXMS_TEMPLATE_OVERRIDE::instance();
