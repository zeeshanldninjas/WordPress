<?php

/**
 * Template for EXMS Post Relations
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Post_Paginations {

    /**
     * @var self
     */
    private static $instance;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Post_Paginations ) ) {

            self::$instance = new EXMS_Post_Paginations;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'wp_ajax_exms_search_post_items', [ $this, 'exms_search_post_relation_items' ] );
        add_action( 'wp_ajax_exms_next_back_sortable_page', [ $this, 'exms_next_back_sortable_page' ] );
    }

    /**
     * Get next sortable page list
     */
    public function exms_next_back_sortable_page() {

        global $wpdb;
        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $table_name = $wpdb->prefix . 'exms_quiz_questions';

        $posts_table = $wpdb->prefix . 'posts';
        $post_relation_table = $wpdb->prefix . 'exms_post_relationship';
        $paged = isset( $_POST['page'] ) ? $_POST['page'] : '';
        $post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';
        $pagination_type = isset( $_POST['pagination_type'] ) ? $_POST['pagination_type'] : 'assigned';
        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
        $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
        $user = isset( $_POST['user'] ) ? $_POST['user'] : '';
        $per_page = 6;
        $offset = ( $paged - 1 ) * $per_page;
        
        if( empty( $paged ) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Page ID Not found.','exms');
            echo json_encode( $response );
            wp_die();
        }

        $results = [];

        if ( $user == "user" ) {

           if ( $post_type === $parent_post_type ) {

                $all_user_ids = get_users([
                    'role'   => 'exms_instructor',
                    'fields' => 'ID',
                ]);
            } elseif( $post_type === "exms_groups" ) {

                $all_user_ids = get_users([
                    'role'   => 'exms_group_leader',
                    'fields' => 'ID',
                ]);
            } else {
                $all_user     = get_users( [ 'fields' => 'ids' ] );
                $all_user_ids = array_filter( $all_user, function( $user_id ) {
                    $user = get_userdata( $user_id );
                    return !in_array( 'exms_group_leader', (array) $user->roles ) &&
                        !in_array( 'exms_instructor', (array) $user->roles );
                } );
            }

            $assigned_users   = exms_get_post_user_ids( $post_id );
            $unassigned_users = array_diff( $all_user_ids, $assigned_users );

            $paged_users = $pagination_type === 'assigned'
                ? array_slice( $assigned_users, $offset, $per_page )
                : array_slice( $unassigned_users, $offset, $per_page );

            if ( empty( $paged_users ) ) {
                wp_send_json_error([
                    'message' => __( 'No users found for this page.', 'exms' )
                ]);
            }

            $results = array_map( function( $user_id ) {
                return (object) [
                    'ID'         => $user_id,
                    'post_title' => exms_get_user_name( $user_id ),
                    'avatar'     => get_avatar_url( $user_id ),
                ];
            }, $paged_users );
        } elseif ( $user === 'post-relation' ) {

            $assigned_ids = $wpdb->prepare(
                "SELECT p.ID, p.post_title
                FROM $post_relation_table r
                JOIN {$wpdb->posts} p ON r.child_post_id = p.ID
                WHERE r.parent_post_id = %d 
                AND r.assigned_post_type = %s
                AND p.post_status = 'publish'
                LIMIT %d OFFSET %d",
                $post_id,
                $post_type,
                $per_page,
                $offset
            );

            $assign_results = $wpdb->get_results( $assigned_ids );

            foreach ( $assign_results as $row ) {
                $row->href = get_permalink( $row->ID );
            }

            $query = "
                SELECT DISTINCT p.ID, p.post_title
                FROM $posts_table p
                LEFT JOIN $post_relation_table qq 
                    ON qq.child_post_id = p.ID 
                    AND qq.parent_post_id = %d 
                    AND qq.assigned_post_type = %s
                WHERE p.post_type = %s 
                    AND p.post_status = 'publish'
                    AND qq.child_post_id IS NULL
                LIMIT %d OFFSET %d
            ";
            $prepared_query = $wpdb->prepare( $query, $post_id, $post_type,$post_type, $per_page, $offset );
            $unassigned_query = $wpdb->get_results( $prepared_query );
            $unassigned_ids = array_column( $unassigned_query, 'ID' );

            $unassign_results = get_posts([
                'post_type'      => $post_type,
                'post__in'       => ! empty( $unassigned_ids ) ? $unassigned_ids : [0],
                'post_status'    => 'publish',
                'orderby'        => 'post__in',
                'numberposts'    => -1,
            ]);
            $results = $pagination_type === 'assigned' ? $assign_results  : $unassign_results;
        }

        if ( $post_type === 'exms-questions' && $user == "" ) {

            if ( $pagination_type === 'assigned' ) {
                $query = $wpdb->prepare("
                    SELECT p.ID, p.post_title
                    FROM {$table_name} qq
                    INNER JOIN {$posts_table} p ON p.ID = qq.question_id
                    WHERE qq.quiz_id = %d
                    AND qq.status = %s
                    AND p.post_status = 'publish'
                    LIMIT %d OFFSET %d
                ", $post_id, 'active', $per_page, $offset );

                $results = $wpdb->get_results( $query );
            } else {

                $query = $wpdb->prepare("
                    SELECT DISTINCT p.ID, p.post_title
                    FROM {$posts_table} p
                    LEFT JOIN {$table_name} qq 
                        ON qq.question_id = p.ID 
                        AND qq.quiz_id = %d 
                        AND qq.status = 'active'
                    WHERE p.post_type = %s
                    AND qq.question_id IS NULL
                    AND p.post_status = 'publish'
                    LIMIT %d OFFSET %d
                ", $post_id, 'exms-questions', $per_page, $offset );

                $results = $wpdb->get_results( $query );
            }
            if ( empty( $results ) ) {
                $response['status'] = 'false';
                $response['message'] = __( 'No questions found for this page.', 'exms' );
                echo json_encode( $response );
                wp_die();
            }
        }
        
        if( $post_type === 'exms-quizzes' && $user == "" ){

            if( $pagination_type === 'assigned' ) {

                $status = esc_sql('active');
                $query = $wpdb->prepare("
                    SELECT p.ID, p.post_title
                    FROM {$table_name} qq
                    INNER JOIN {$posts_table} p ON p.ID = qq.quiz_id
                    WHERE qq.question_id = %d
                    AND qq.status = %s
                    AND p.post_status = 'publish'
                    LIMIT %d OFFSET %d
                ", $post_id, $status, $per_page, $offset);
                
                $results = $wpdb->get_results($query);
                
            } else {

                $query = $wpdb->prepare("
                    SELECT DISTINCT p.ID, p.post_title
                    FROM {$posts_table} p
                    LEFT JOIN {$table_name} qq ON qq.quiz_id = p.ID 
                    AND qq.question_id = %d
                    WHERE p.post_type = %s
                    AND (qq.status IS NULL OR qq.status != %s)
                    AND p.post_status = 'publish'
                    LIMIT %d OFFSET %d
                ", $post_id, 'exms-quizzes', 'active', $per_page, $offset);
                
                $results = $wpdb->get_results($query);
            }

            if ( empty( $results ) ) {
                $response['status'] = 'false';
                $response['message'] = __( 'No quizzes found for this page.', 'exms' );
                echo json_encode( $response );
                wp_die();
            }
        }
        $response['content'] = [];
        foreach ( $results as $row ) {
            $response['content'][] = [
                'id'    => $row->ID,
                'title' => $row->post_title,
                'href'  => get_edit_post_link($row->ID, ''),
                'avatar' => isset( $row->avatar ) ? $row->avatar : '',
            ];
        }
  
        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Search post relation items
     */
    public function exms_search_post_relation_items() {    

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $table_name = EXMS_PR_Fn::exms_relation_table_name();

        $response = [];

        $search_key = isset( $_POST['search_key'] ) ? $_POST['search_key'] : '';

        $search_post_type = isset( $_POST['search_post_type'] ) ? $_POST['search_post_type'] : '';
        if( empty( $search_post_type ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Search Post Type not found.','exms');
            echo json_encode( $response );
            wp_die();
        }

        $data_type = isset( $_POST['data_type'] ) ? $_POST['data_type'] : '';
        if( empty( $data_type ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Relation Type not found.','exms');
            echo json_encode( $response );
            wp_die();
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : '';
        $current_post_type = isset( $_POST['current_post_type'] ) ? $_POST['current_post_type'] : '';
        $name_key = isset( $_POST['name_key'] ) ? $_POST['name_key'] : '';
        $name = isset( $_POST['name'] ) ? $_POST['name'] : '';

        $parent_relation = isset( $_POST['parent_relation'] ) ? $_POST['parent_relation'] : '';
        if( ! empty( $parent_relation ) && $parent_relation == 'true' ) {

            $relation_ids = EXMS_PR_Fn::exms_parent_assigned_post_ids( $search_post_type, $current_post_type, $post_id );

        } else {

            $relation_ids = EXMS_PR_Fn::exms_current_assigned_post_ids( $current_post_type, $search_post_type, $post_id );
        }

        if( 'exms_quizzes' == $search_post_type ) {

            $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : '';
            if( ! empty( $user_id ) ) {
                $relation_ids = exms_get_user_post_ids( $user_id, 'exms_quizzes' );
            }
        }

        if( 'search_users' == $current_post_type ) {

            $assign_user_ids = exms_get_post_user_ids( $post_id );
            $user_ids = exms_get_all_user_ids( $assign_user_ids, '', $search_key, $data_type );

            if( $user_ids && is_array( $user_ids ) ) {
                foreach( $user_ids as $user_id ) {

                    $data_name = '';
                    if( 'un-assigned' == $data_type ) {
                        $data_name = 'exms_unassign_items';
                    } else {
                        $data_name = 'exms_assign_items';
                    }

                    ?>
                    <div class="exms-sortable-item" data-name="<?php echo $data_name; ?>" data-name-key="current">
                        <div class="exms-post-title"><?php echo exms_get_user_name( $user_id ); ?></div>
                        <input type="hidden" name="" class="exms-assign-unassign-id" value="<?php echo $user_id; ?>">
                        <input type="hidden" name="exms_current_relation" value="<?php echo $post_type; ?>">
                    </div>
                    <?php
                }

            } else {

                ?>
                <div class="exms-post-not-found">
                    <?php echo __( 'Users not found.','exms'); ?>
                </div>
                <?php
            }

        } else {

            $args = [
                'posts_per_page'    => -1,
                'post_type'         => $search_post_type,
                'post_status'       => 'publish',
                'posts_per_page'    => 10
            ];

            if( ! empty( $search_key ) ) {
                $args['s'] = $search_key;
            }

            if( ! empty( $relation_ids ) ) {

                if( 'un-assigned' == $data_type ) {
                    $args['post__not_in'] = $relation_ids;
                    $args['order'] = 'ASC';

                } else {
                    $args['post__in'] = $relation_ids;
                }
            }

            $exms_query = new WP_Query( $args );

            ob_start();

            if( $exms_query->have_posts() ) {

                while( $exms_query->have_posts() ) {

                    $exms_query->the_post();

                    echo EXMS_PR_Fn::exms_sortable_item_html( get_the_ID(), $search_post_type, $name, $name_key );
                }

            } else {
                
                echo __( 'No Result Found.','exms');
            }
        }

        $content = ob_get_contents();
        ob_get_clean();

        $response['content'] = $content;
        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }
}

EXMS_Post_Paginations::instance();