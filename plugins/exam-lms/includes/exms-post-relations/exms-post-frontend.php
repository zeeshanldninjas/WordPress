<?php

/**
 * Template for Post Relation functions
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_PR_Frontend {

    /**
     * @var self
     */
    private static $instance;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_PR_Frontend ) ) {

            self::$instance = new EXMS_PR_Frontend;

            global $wpdb;
            self::$wpdb = $wpdb;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'the_content', [ $this, 'exms_dispay_parent_post_content' ], 10, 1 );
        add_action( 'wp_ajax_exms_post_mark_complete', [ $this, 'exms_post_mark_complete_ajax' ] );
    }

    /**
     * Mark complete current post : Ajax
     */
    public function exms_post_mark_complete_ajax() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : '';
        if( empty( $post_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Post ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $parent_post_ids = isset( $_POST['parent_posts'] ) ? $_POST['parent_posts'] : '';
        if( empty( $parent_post_ids ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Parent Post Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $user_id = get_current_user_id();
        $post_type = get_post_type( $post_id );

        echo exms_post_mark_complete( $post_id, $parent_post_ids, $user_id );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Display parent post content
     * 
     * @param $content
     */
    public function exms_dispay_parent_post_content( $content ) {    
        
        $user_id = get_current_user_id();
        $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );

        $post_types = EXMS_Setup_Functions::get_setup_post_types();

        $post_type_exists = isset( $post_types[$post_type] ) ? $post_types[$post_type] : '';
        if( empty( $post_type_exists ) ) {
            return $content;
        }

        $post_type = get_post_type( $post_id );
        $post_name = str_replace( 'exms-', '', $post_type );
        $post_options = exms_get_post_options( $post_id );

        $sign_up = isset( $post_options['exms_'.$post_name.'_sign_up'] ) ? intval( $post_options['exms_'.$post_name.'_sign_up'] ) : '';
        $price_type = isset( $post_options['exms_'.$post_name.'_type'] ) ? $post_options['exms_'.$post_name.'_type'] : 'free';
        $price = isset( $post_options['exms_'.$post_name.'_price'] ) ? intval( $post_options['exms_'.$post_name.'_price'] ) : 0;
        $subscription = isset( $post_options['exms_'.$post_name.'_sub_days'] ) ? intval( $post_options['exms_'.$post_name.'_sub_days'] ) : 0;
        $exms_points = isset( $post_options['exms_'.$post_name.'_points'] ) ? intval( $post_options['exms_'.$post_name.'_points'] ) : '';

        $exms_options = get_option( 'exms_settings' );
        $complete_url = isset( $exms_options['paypal_redirect_url']['complete_url'] ) ? $exms_options['paypal_redirect_url']['complete_url'] : '';
        $cancel_url = isset( $exms_options['paypal_redirect_url']['cancel_url'] ) ? $exms_options['paypal_redirect_url']['cancel_url'] : '';
        $paypal_currency = isset( $exms_options['paypal_currency'] ) ? $exms_options['paypal_currency'] : '';
        $paypal_payee_email = isset( $exms_options['paypal_vender_email'] ) ? $exms_options['paypal_vender_email'] : '';
        $paypal_client_secret = isset( $exms_options['paypal_client_secret'] ) ? $exms_options['paypal_client_secret'] : '';
        $paypal_client_id = isset( $exms_options['paypal_client_id'] ) ? $exms_options['paypal_client_id'] : '';
        $relation_ids = EXMS_PR_Fn::exms_child_relation_ids( $post_id, $post_type );

        if( ! empty( exms_get_top_parent_id() ) && ! exms_is_user_in_post( $user_id, exms_get_top_parent_id() ) ) {

            $by_link = '<a href="'.get_permalink( exms_get_top_parent_id() ).'" />'.__( ' Click here' ).'</a>';
            
            return __( 'You are not enroll on this ', 'exms' ).get_the_title( exms_get_top_parent_id() ) .$by_link. __( ' to enroll', 'exms' );
        }

        $parent_exists = isset( $post_types[$post_type]['parent_post_type'] ) ? $post_types[$post_type]['parent_post_type'] : '';
        $deep_count = count( $post_types ) - 1;
        $progress_data = exms_post_progress( $post_id, $relation_ids, '', 'straight' );
        $complete_count = isset( $progress_data['complete_count'] ) ? intval( $progress_data['complete_count'] ) : 0;
        $progress_count = isset( $progress_data['progress_count'] ) ? intval( $progress_data['progress_count'] ) : 0;

        $percentage = 0;
        if( $progress_count && $complete_count ) {
            $percentage = round( $progress_count * 100 / $complete_count );
        }

        ob_start();
        
        ?>
        <div class="exms-straight-progress-container" data-parent="<?php echo $parent_exists; ?>">
            <div class="exms-target-come">
                <?php echo exms_post_serialize_str( $post_id ); ?>
            </div>
            <div class="exms-progress-count"><?php echo $percentage.'% Complete'; ?></div>
            <div class="exms-straight-progress">
                <div class="exms-straight-progress-wrap" style="width: <?php echo $percentage.'%'; ?>;"></div>
            </div>
        </div>
        <?php

        $child_post_types = EXMS_PR_Fn::exms_get_child_post_type( $post_type, $post_id );
        if( ! empty( $child_post_types ) && is_array( $child_post_types ) ) {

            ?>
            <div class="exms-content-heading-wrap">
                <h3><?php echo exms_post_name( $post_type ).__( ' Content', 'exms' ); ?></h3>
            </div>
            <?php

            $parent_id = 0; 
            $grand_parent = 0; 
            $parent_head = 0;
            $parent_head2 = 0;
            
            foreach( $child_post_types as $child_post_type ) {

                echo self::$instance->exms_post_items_html( $relation_ids, $post_id, $parent_id, $grand_parent, $parent_head, $parent_head2, $child_post_type );
            }
        }
        
        echo self::$instance->exms_post_action_buttons_html( $post_id, $relation_ids, $parent_exists );

        $post_content = ob_get_contents();
        ob_get_clean();

        /**
         * Return wpe content html
         * 
         * @param $content
         */
        return apply_filters( 'exms_content', $content.$post_content, $post_id, $user_id );
    }

    /**
     * Get post relation items html
     * 
     * @param $relation_ids
     */
    public function exms_post_items_html( $relation_ids, $post_id, $parent_id, $grand_parent, $parent_head, $parent_head2, $child_post_type = '' ) {

        ob_start();

        $user_id = get_current_user_id();

        if( ! empty( $relation_ids ) && is_array( $relation_ids ) ) {
            
            if( ! empty( $child_post_type ) 
                && get_post_type( $relation_ids[0] ) == $child_post_type ) {

                $hide = '';
                $border = 'border: 2px solid #e2e7ed; border-radius: 6px';
                $padding = 'padding: 10px 0 10px 16px';

            } elseif( get_post_type( $relation_ids[0] ) != $child_post_type ) {
                
                $hide = 'display: none;';
                $border = '';
                $padding = '';
            }

            ?>
            <div class="exms-display-content" style="<?php echo $hide; ?>">

                <?php
                $class = '';
                $relation_end = end( $relation_ids );
                foreach( $relation_ids as $index => $relation_id ) {

                    $post_type = get_post_type( $relation_id );
                    if( ! post_type_exists( $post_type ) ) {
                        continue;
                    }

                    if( 'exms_questions' == $post_type ) {
                        continue;
                    }

                    $post_types = EXMS_Setup_Functions::get_setup_post_types();
                    $other_post_type = [
                        'exms_quizzes' => [ 
                            'singular_name' => 'Quizzes',
                            'plural_name'   => 'Quizzes',
                            'slug'          => 'exms_quizzes',
                            'post_type_name'=> 'exms_quizzes',
                            'show_in_menu'  => 'exms_menu'
                        ]
                    ];

                    $post_types = array_merge( $post_types, $other_post_type );
                    if( ( ! in_array( $post_type, array_keys( $post_types ) ) ) ) {
                        continue;
                    }

                    if( $relation_end == $relation_id ) {
                        $class = 'exms-post-item-border';
                    }

                    $relation_ids = EXMS_PR_Fn::exms_child_relation_ids( $relation_id, $post_type );
                    $post_name = exms_post_name( $post_type );

                    if( empty( $parent_head ) ) {
                        $parent_head = $post_id;
                    }

                    $link_ids = $parent_id.'-'.$grand_parent.'-'.$parent_head.'-'.$parent_head2;
                    $link_ids = array_filter( array_unique( explode( '-', $link_ids ) ) );
                    $parent_ids = implode( '-', $link_ids );

                    $progress_data = exms_post_progress( $relation_id, $relation_ids, $parent_ids, 'rounded' );
                    $complete_count = isset( $progress_data['complete_count'] ) ? intval( $progress_data['complete_count'] ) : 0;
                    $progress_count = isset( $progress_data['progress_count'] ) ? intval( $progress_data['progress_count'] ) : 0;

                    $percentage = 0;
                    if( $progress_count && $complete_count ) {
                        $percentage = $progress_count * 100 / $complete_count .'%';
                    }

                    /* The full ids of parent posts */
                    $parent_posts = '';
                    $markcompletcheck = '';
                    $parent_posts = isset( $_GET['parent_posts'] ) ? $_GET['parent_posts'] : '';
                    if( $parent_posts ) {
                        $parent_posts = $parent_ids.'-'.$parent_posts.'';
                    } else {
                        $parent_posts = $parent_ids;
                    }
                    /* /the full ids of parent posts */

                    if( ( exms_is_post_completed( $relation_id, $parent_posts, $user_id ) ) 
                        || ( 'exms_quizzes' == $post_type 
                        && true === exms_is_quiz_complete_with_parents( $user_id, $relation_id, $parent_posts ) ) ) {
                        $markcompletcheck = '<span class="dashicons dashicons-yes exms-mark-complete-check"></span>';
                    }

                    if( 'exms_quizzes' == $post_type 
                        && true === exms_is_quiz_complete_with_parents( $user_id, $relation_id, $parent_posts ) ) {
                        $percentage = '100%';
                    }

                    ?>
                    <div class="exms-post-item <?php echo $class; ?>" data-post-id="<?php echo $relation_id; ?>" style="<?php echo $border.';'.$padding; ?>">

                        <div class="exms-post-link-wrap">
                            <a href="<?php echo exms_step_permalinks( $relation_id, $parent_ids ); ?>" class="exms-post-link">
                                <div class="exms-round-progress-bar">
                                    <div class="exms-round-progress-bar-wrap">
                                        <div id="middle-circle">
                                            <?php echo $markcompletcheck; ?>
                                        </div>
                                        <div id="progress-spinner" style="background: conic-gradient( #552CA8 <?php echo $percentage; ?>, #e2e7ed <?php echo $percentage; ?> );"></div>
                                    </div>
                                </div>
                                <span class="exms-post-title"><?php echo get_the_title( $relation_id ); ?></span>
                            </a>
                            <?php 
                            if( 'exms_quizzes' != $post_type && EXMS_PR_Fn::exms_child_count_html( $relation_id ) ) {

                                ?>
                                <div class="exms-expand-post">
                                    <div class="exms-expand-icon dashicons dashicons-arrow-down"></div>
                                    <div class="exms-expand-text"><?php _e( 'Expand', 'exms' ); ?></div>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="exms-clear-both"></div>
                            <?php echo EXMS_PR_Fn::exms_child_count_html( $relation_id ); ?>
                        </div>  
                        <?php
                        if( 'exms_quizzes' != $post_type && EXMS_PR_Fn::exms_child_count_html( $relation_id ) ) {
                            ?>
                            <div class="exms-progress-structures">
                                <div class="exms-progress-wrap">
                                    <div class="exms-progress-title"><?php echo $post_name.__( ' Content', 'exms' ); ?></div>
                                </div>
                            </div>
                            <?php
                        }

                        if( ! empty( $relation_ids ) && is_array( $relation_ids ) ) {

                            echo self::$instance->exms_post_items_html( $relation_ids, $post_id, $relation_id, $parent_id, $grand_parent, $parent_head );
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * Post action button html content
     * 
     * @param $post_id
     * @param $relation_ids
     * @param $parent_exists
     */
    public function exms_post_action_buttons_html( $post_id, $relation_ids, $parent_exists ) {

        $parent_posts = isset( $_GET['parent_posts'] ) ? $_GET['parent_posts'] : '';
        $user_id = get_current_user_id();
        $is_childs_completed = exms_is_childs_completed( $post_id, $relation_ids );
        $is_completed = exms_is_post_completed( $post_id, $parent_posts, $user_id );

        $prev_post_id = 0;
        $prev_post = get_adjacent_post( false, '', true );
        if( ! empty( $prev_post ) ) {
            $prev_post_id = $prev_post->ID;
        }

        $next_post_id = 0;
        $next_post = get_adjacent_post( false, '', false );
        if( ! empty( $next_post ) ) {
            $next_post_id = $next_post->ID;
        }

        $link = '';
        if( ! empty( $parent_posts ) ) {
            $link = '?parent_posts='.$parent_posts;
        }

        ob_start();

        ?>
        <div class="exms-action-button" data-post-id="<?php echo $post_id; ?>">

            <div class="exms-post-back">
            <?php 
            if( ! empty( $prev_post_id ) ) {

                ?>
                <a href="<?php echo get_permalink( $prev_post_id ).$link; ?>">
                    <span class="dashicons dashicons-arrow-left-alt exms-post-back-button-icon"></span>
                    <span class="exms-post-back-button"><?php _e( 'Back', 'exms' ); ?></span>
                </a>
                <?php
            }
            ?>
            </div>
            <?php

            if( ( ! $is_completed && empty( $relation_ids ) && ! empty( $parent_exists ) ) 
                || ( ! empty( $relation_ids ) && $is_childs_completed && ! $is_completed && ! empty( $parent_exists ) ) ) {
                ?>
                <div class="exms-mark-completed-button">
                    <input type="submit" name="exms_mark_complete" value="<?php _e( 'Mark Complete', 'exms' ); ?>" class="exms-mark-complete-button">
                </div>
                <?php
            }

            ?>
            <div class="exms-post-next">
                <?php 
                if( ! empty( $next_post_id ) ) {

                    ?>
                    <a href="<?php echo get_permalink( $next_post_id ).$link; ?>">
                        <span class="exms-post-next-button"><?php _e( 'Next', 'exms' ); ?></span>
                        <span class="dashicons dashicons-arrow-right-alt exms-post-next-button-icon"></span>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }
}

EXMS_PR_Frontend::instance();