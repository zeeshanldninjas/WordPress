<?php
/**
 * Template to display [exms_instructor] shortcode content
 *
 * This template can be overridden by copying it to yourtheme/wp-exams/shortcodes/exms-instructor-shortcode.php.
 *
 * @param $atts     All shortcode attributes
 *
 */
if( ! defined( 'ABSPATH' ) ) exit;

ini_set('display_errors', 'On');
error_reporting(E_ALL);

$parent = isset( $atts['parent'] ) ? $atts['parent'] : '';
$limit = isset( $atts['limit'] ) ? ( int ) $atts['limit'] : 10;
echo '<pre>';
$post_types = EXMS_Setup_Functions::get_setup_post_types();
print_r($post_types);

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