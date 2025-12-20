<?php

/**
 * Course listing page
 */
class EXMS_COURSE_LISTING {
    

    /**
     * Define of instance
     */
    private static $instance = null;

    /**
      * Define the instance
     */
    public static function instance(): EXMS_COURSE_LISTING {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_COURSE_LISTING ) ) {
            self::$instance = new self;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     *  Hooks that are used in the class
     */
    private function hooks() {

        add_shortcode( 'exms_course_listing', [ $this, 'exms_course_listing_callback' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_course_listing_scripts' ] );
        add_action( 'wp', [ $this, 'exms_course_search' ] );
        add_filter( 'posts_search', [ $this, 'exms_filter_search_by_title' ], 10, 2 );
    }

    /**
     * enqueu course listing scripts 
     */
    public function exms_course_listing_scripts() {

        wp_enqueue_style( 'wp-exams-course-listing', EXMS_ASSETS_URL . 'css/frontend/exms-course-listing.css', [], EXMS::VERSION, null );
    }

    /**
     *  create a shortcode to display a course listing
     */
    public function exms_course_listing_callback() {

        global $wp_query;

        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $search = isset( $_GET[ 'search' ] ) ? sanitize_text_field( $_GET[ 'search' ] ) : '';

        $args = array(
            'post_type'      => 'exms-courses',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'paged'          => $paged,
            's'              => $search,
        );

        $query = new WP_Query( $args );

        
        ob_start();
        if ( $query->have_posts() ) {

            $current_page = max( 1, get_query_var( 'paged' ) );
            $total_pages = $query->max_num_pages;

            /**
             * require search bar 
             */
            require EXMS_TEMPLATES_DIR . 'frontend/course/course-listing-main-template.php';
        }
        else{
            echo '<p>No courses found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Course Search Form Handler
    */
    public function exms_course_search() {
        
        if ( is_admin() ) return;

        if ( isset( $_POST[ 'search' ] ) && !empty( $_POST[ 'search' ] ) ) {
            $search = sanitize_text_field( $_POST[ 'search' ] );
            $redirect_url = get_permalink();
            $redirect_url = add_query_arg( 'search', urlencode( $search ), $redirect_url );
            $redirect_url = remove_query_arg( 'paged', $redirect_url );
            wp_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Filter search to post title only for 'exms_courses'
     */
    public function exms_filter_search_by_title( $search, $wp_query ) {
        global $wpdb;

        if (
            $wp_query->is_search() &&
            $wp_query->is_main_query() &&
            ( $wp_query->get('post_type') === 'exms-courses' || $wp_query->get('post_type') === ['exms-courses'] )
        ) {
            $search_term = $wp_query->get( 's' );

            if ( !empty( $search_term ) ) {
                $like = '%' . $wpdb->esc_like( $search_term ) . '%';
                $search = $wpdb->prepare(
                    " AND {$wpdb->posts}.post_title LIKE %s ",
                    $like
                );
            }
        }
        return $search;
    }
}

EXMS_COURSE_LISTING::instance();