<?php

/**
 * Get full hierarchy chain of IDs and post types from current URL dynamically
 * (Filter out "post" and "page" types so they never go into DB)
 */
function exms_get_url_chain() {
    $current_url = home_url( add_query_arg( null, null ) );
    $path  = trim( parse_url( $current_url, PHP_URL_PATH ), '/' );
    $parts = explode( '/', $path );
    $post_types = get_post_types( [ 'public' => true ], 'names' );

    $items_in_url = [];

    foreach ( $parts as $slug ) {
        $post = get_page_by_path( $slug, OBJECT, $post_types );
        if ( $post ) {
            $ptype = $post->post_type;

            if ( in_array( $ptype, [ 'page', 'post' ], true ) ) {
                continue;
            }

            $items_in_url[] = [
                'id'   => $post->ID,
                'type' => $ptype,
            ];
        }
    }

    return $items_in_url;
}