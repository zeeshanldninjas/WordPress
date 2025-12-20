<?php
/**
 * WP Exam - Post types
 *
 * All post type related functions
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Post_Types_Functions
 *
 * Base class to create post type & meta boxes
 */
class Exms_Post_Types_Functions {

    /**
     * Function to create post types
     *
     * @param string $singular_name
     * @param string $plural_name
     * @param string $slug
     * @param string $post_type_name
     * @param bool $show_in_menu
     */
    private static function create_post_type( $singular_name, $plural_name, $slug, $post_type_name, $show_in_menu ) {
        
        $public_option = ( 'exms-questions' === $post_type_name ) ? false : true;

        $labels = array(
            'name'                  => _x( $plural_name, 'Post type general name', 'exms' ),
            'singular_name'         => _x( $singular_name, 'Post type singular name', 'exms' ),
            'menu_name'             => _x( $plural_name, 'Admin Menu text', 'exms' ),
            'name_admin_bar'        => _x( $singular_name, 'Add New on Toolbar', 'exms' ),
            'add_new'               => __( 'Add New', 'exms' ),
            'add_new_item'          => __( 'Add New '.$singular_name, 'exms' ),
            'new_item'              => __( 'New '.$singular_name, 'exms' ),
            'edit_item'             => __( 'Edit '.$singular_name, 'exms' ),
            'view_item'             => __( 'View '.$singular_name, 'exms' ),
            'all_items'             => __( 'All '.$plural_name, 'exms' ),
            'search_items'          => __( 'Search '.$plural_name, 'exms' ),
            'parent_item_colon'     => __( 'Parent '.$plural_name.':', 'exms' ),
            'not_found'             => __( 'No '.$plural_name.' found.', 'exms' ),
            'not_found_in_trash'    => __( 'No '.$plural_name.' found in Trash.', 'exms' ),
            'archives'              => __( $singular_name.' Archives', 'exms' ),
            'insert_into_item'      => __( 'Insert into '.$singular_name, 'exms' ),
            'uploaded_to_this_item' => __( 'Uploaded to this '.$singular_name, 'exms' ),
            'filter_items_list'     => __( 'Filter '.$plural_name.' list', 'exms' ),
            'items_list_navigation' => __( $plural_name.' list navigation', 'exms' ),
            'items_list'            => __( $plural_name.' list', 'exms' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => $public_option,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'rest_base'          => $slug,
            'has_archive'        => true,
            'rewrite'            => array( 'slug' => $slug, 'with_front' => false ),
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields' ),
            'menu_icon'          => 'dashicons-welcome-learn-more',
            'show_in_admin_bar' => true
        );

        register_post_type( $post_type_name, $args );

        if( 'exms_points' == $post_type_name ) {
            return false;
        }

        /**
         * Add categories taxonomy
         */
        register_taxonomy( $post_type_name.'_categories', [$post_type_name], [
            'hierarchical'      => true,
            'label'             => $singular_name . __( ' Categories' ),
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'categories' ],
        ] );

        /**
         * Add tags taxonomy
         */
        register_taxonomy( $post_type_name.'_tags', [$post_type_name], [
            'hierarchical'       => false,
            'label'              => $singular_name . __( ' Tags' ),
            'show_ui'            => true,
            'show_admin_column'  => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'tags' ],
        ] );
    }

    /**
     * Function to create post types
     *
     * @param string $singular_name
     * @param string $plural_name
     * @param string $slug
     * @param string $post_type_name
     * @param bool $show_in_menu
     */
    public static function create_exms_post_type( $singular_name, $plural_name, $slug, $post_type_name, $show_in_menu ) {

        Exms_Post_Types_Functions::create_post_type( $singular_name, $plural_name, $slug, $post_type_name, $show_in_menu );
    }

    /**
     * Adds meta boxes
     *
     * @param string $id
     * @param string $title
     * @param $callback_func
     * @param string $screen
     * @param string $context
     * @param int $priority
     * @param array $callback_args
     */
    public static function add_exms_meta_box( $id, $title, $callback_func, $screen, $context, $priority, $callback_args ) {
        add_meta_box( $id, $title, $callback_func, $screen, $context, $priority, $callback_args );
    }

    /**
     * Saves post meta value
     *
     * @param Int       $post_id        Post ID
     * @param Array     $meta_data      Meta key
     * @return false on error
     */
    public static function save_exms_meta_boxes( $post_id, $meta_key ) {
        
        if( ! is_array( $meta_key ) ) {

            return false;
        }

        $opts = [];

        foreach( $meta_key as $key ) {

            if( ! isset( $_POST[$key] ) ) {

                continue;
            }

            // if( stripos( get_post_type( $post_id ), 'exms_questions' ) !== false ) {
               
            //     $saved_quizzes = exms_get_question_quizzes( $post_id );

            //     if( $saved_quizzes ) {

            //         foreach( $saved_quizzes as $quiz_id ) {
                        
            //             delete_post_meta( $post_id, $key.'_'.$quiz_id ); 
            //         }
            //     }
            // }

            // if( stripos( $key, 'exms_answers_' ) !== false ) {

            //     $opts['exms_answers'][] = sanitize_meta( 'exms_answers', $_POST[$key], 'post' );

            // } else {

            //     $opts[$key] = sanitize_meta( $key, $_POST[$key], 'post' );

            //     if( 'exms_quiz_type' == $key ) {

            //         update_post_meta( $post_id, $key, $_POST[$key] );
                  
            //     } elseif( 'exms_attached_quizzes' == $key && isset( $_POST[$key] ) ) {

            //         foreach( $_POST[$key] as $quiz_id ) {

            //             update_post_meta( $post_id, $key.'_'.$quiz_id, $quiz_id );
            //         }
            //     }
            // }
        }
        
        if( $opts ) {

            /**
             * Remove/reset previous meta data
             */
            delete_post_meta( $post_id, get_post_type( $post_id ) . '_opts' );

            /**
             * Add new meta data
             */
            update_post_meta( $post_id, get_post_type( $post_id ) . '_opts', $opts );
        }
    }

    /**
     * create a function to get post meta
     * 
     * @param $post_id
     * @param $meta_key
     */
    public static function exms_get_post_data( $post_id, $meta_key ) {

        return get_post_meta( $post_id, $meta_key, true );
    }
}