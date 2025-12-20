<?php

/**
 * Template for wp exam setup wizard
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Setup_Functions {

    /**
     * @var self
     */
    private static $instance;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Setup_Functions ) ) {

            self::$instance = new EXMS_Setup_Functions;
        }

        return self::$instance;
    }

    /**
     * Created course structure html content
     * 
     * @param $heading ( bool )
     */
    public static function exms_course_structures_html( $heading = '' ) {

        $hide = '';
        $post_type_css = '';
        if( self::get_create_post_types_html() ) {
            $hide = 'exms-setup-hidden';
            $post_type_css = 'exms-post-types-exists';
        }

        if( $heading ) {

            ?>
            <div class="exms-structure-heading">
                <h2><?php _e( 'Setup Course Structures', 'exms' ); ?></h2>
                <a href="#" class="exms-post-add-new">
                    <span class="exms-post-add-new-icon dashicons dashicons-plus-alt2"></span>
                    <span class="exms-post-add-new-text"><?php _e( 'Add New', 'exms' ); ?></span> 
                </a>
            </div>
            <?php
        }
    }

    /**
     * Get Setup Post Types
     */
    public static function get_setup_post_types() {

        $post_types = get_option( 'exms_post_types' );
        
        if( empty( $post_types ) || ! is_array( $post_types ) ) {
            return false;
        }

        return $post_types;
    } 

    /**
     * Get Create post type html content
     */
    public static function get_create_post_types_html( $post_types = array() ) {

        ob_start();

        if( empty( $post_types ) ) {
            $post_types = self::get_setup_post_types();
        }
        
        $count = 0;
        $top_border = '';
        $post_type_array = [];
        if( $post_types && is_array( $post_types ) ) {

            ?><div class="exms-setup-created-post-types"><?php

            foreach( $post_types as $post_name => $post_type ) {

                $singular_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
                $plural_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
                $slug = isset( $post_type['slug'] ) ? $post_type['slug'] : '';
                $post_type_name = isset( $post_type['post_type_name'] ) ? $post_type['post_type_name'] : '';
                $show_in_menu = isset( $post_type['show_in_menu'] ) ? $post_type['show_in_menu'] : '';

                $parent_post_type = isset( $post_type['parent_post_type'] ) ? $post_type['parent_post_type'] : '';

                $child_margin = ( $count * 10 );
                if( $child_margin >= 10 ) {
                    $top_border = '1px solid #d4d4d4';
                }

                $count++;

                $hide_class = '';
                if( $count > 1 ) {
                    $hide_class = 'exms-setup-hidden';
                }

                ?>
                <div class="exms-post-type-list-wrap <?php echo $hide_class; ?>" style="border-top: <?php echo $top_border; ?>; margin-left: <?php echo $child_margin.'px'; ?>" data-post-type="<?php echo $post_name; ?>" date-parent-post-type="<?php echo $parent_post_type; ?>" >
                    <div class="exms-post-type-list" data-post-type="<?php echo $post_name; ?>">
                        <div class="exms-post-type-name"><?php echo $singular_name; ?></div>
                        <div class="exms-post-type-edit"><?php _e( 'Edit' ); ?></div>
                        <div class="exms-post-type-delete"><?php _e( 'Delete' ); ?></div>
                        <div class="exms-post-type-expand dashicons dashicons-arrow-down-alt2"></div>
                    </div>
                </div>
                <?php
            }

            ?></div><?php
        }

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }
    
    /**
     * Get Create post type html content
     */
    public static function get_custom_post_types_html( $post_types = array() ) {

        ob_start();

        if( empty( $post_types ) ) {
            $post_types = self::get_setup_post_types();
        }
        
        $count = 0;
        $top_border = '';
        $post_type_array = [];
        if( $post_types && is_array( $post_types ) ) {

            ?><div class="exms-setup-created-post-types"><?php

            foreach( $post_types as $post_name => $post_type ) {

                $singular_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
                $plural_name = isset( $post_type['singular_name'] ) ? $post_type['singular_name'] : '';
                $slug = isset( $post_type['slug'] ) ? $post_type['slug'] : '';
                $post_type_name = isset( $post_type['post_type_name'] ) ? $post_type['post_type_name'] : '';
                $show_in_menu = isset( $post_type['show_in_menu'] ) ? $post_type['show_in_menu'] : '';

                $parent_post_type = isset( $post_type['parent_post_type'] ) ? $post_type['parent_post_type'] : '';

                $child_margin = ( $count * 10 );
                if( $child_margin >= 10 ) {
                    $top_border = '1px solid #d4d4d4';
                }

                $count++;

                $hide_class = '';
                if( $count > 1 ) {
                    $hide_class = 'exms-setup-hidden';
                }

                ?>
                <div class="exms-post-type-list-wrap <?php echo $hide_class; ?>" style="border-top: <?php echo $top_border; ?>; margin-left: <?php echo $child_margin.'px'; ?>" data-post-type="<?php echo $post_name; ?>" date-parent-post-type="<?php echo $parent_post_type; ?>" >
                    <div class="exms-post-type-list" data-post-type="<?php echo $post_name; ?>">
                        <div class="exms-post-type-name"><?php echo $singular_name; ?></div>
                        <div class="exms-post-type-edit"><?php _e( 'Edit' ); ?></div>
                        <div class="exms-post-type-delete"><?php _e( 'Delete' ); ?></div>
                        <div class="exms-post-type-expand dashicons dashicons-arrow-down-alt2"></div>
                    </div>
                </div>
                <?php
            }

            ?></div><?php
        }

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }
}

EXMS_Setup_Functions::instance();