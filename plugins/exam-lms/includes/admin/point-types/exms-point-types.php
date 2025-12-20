<?php
/**
 * WP EXAMS - Point Type
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Point_Type
 *
 * Base class to define all point type related hooks
 */
class EXMS_Point_Type {

	private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Point_Type ) ) {

        	self::$instance = new EXMS_Point_Type;
        	self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Create hooks
	 */
	public function hooks() {

        add_action( 'wp_ajax_exms_point_balance', [ $this, 'exms_save_point_type' ] );
        add_action( 'admin_init', [ $this, 'exms_remove_unwanted_meta_boxes' ] );
        add_action( 'save_post', [ $this, 'exms_save_point_type_data' ], 99, 3 );
        add_action( 'admin_head-post-new.php', [ $this, 'exms_hide_post_view_preview' ] );
        add_action( 'admin_head-post.php', [ $this, 'exms_hide_post_view_preview' ] );
        add_filter( 'post_row_actions', [ $this, 'exms_remove_row_actions' ], 10, 1 );
        add_action( 'admin_head', [ $this, 'exms_hide_view_post_on_notification' ] );
	}

    /**
     * Remove view link on post update notification
     */
    public function exms_hide_view_post_on_notification() {

        $post_id = get_the_id();
        $post_type = get_post_type( $post_id );
        
        if( empty( $post_type ) || 'exms_points' != $post_type ) {
            return false;
        }

        ?>
        <style type="text/css">
            .updated.notice.notice-success.is-dismissible a {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Remove view button on point types
     */
    public function exms_remove_row_actions( $actions ) {

        if( get_post_type() === 'exms_points' ) {

            unset( $actions['view'] ); 
        }

        return $actions;
    }

    /**
     * Hide post preview on point types
     */
    public function exms_hide_post_view_preview() {

        $my_post_type = 'exms_points';
        global $post;
        if( $post->post_type == $my_post_type ) {
            echo '
             <style type="text/css">
                #edit-slug-box,
                #minor-publishing-actions{
                    display: none;
                }
                .row-actions .view {
                    display: none !important;
                }
             </style>
            ';
        }
    }

    /**
     * Save point type data
     * 
     * @param $post_id
     * @param $post_type
     * @param $update
     */
    public function exms_save_point_type_data( $post_id, $post, $update ) {

        global $wpdb;
        $table_name = $wpdb->prefix.'posts';

        if( ! $update ) {
            return false;
        }   

        $post_type = get_post_type( $post_id );

        if( 'exms_points' != $post_type ) {
            return false;
        }

        $post_title = isset( $_POST['exms_point_type_singular'] ) ? sanitize_text_field( $_POST['exms_point_type_singular'] ) : '';
        $plural_name = isset( $_POST['exms_point_type_plural'] ) ? sanitize_text_field( $_POST['exms_point_type_plural'] ) : '';
        if( ! empty( $plural_name ) ) {
            update_post_meta( $post_id, 'exms_point_type_plural', $plural_name );
        }
        $slug = isset( $_POST['exms_point_type_slug'] ) ? sanitize_text_field( $_POST['exms_point_type_slug'] ) : '';
    
        remove_action( 'save_post', [ $this, 'exms_save_point_type_data' ], 99, 3 );

        if ( ! wp_is_post_revision( $post_id ) ) {
            $my_post = array(
                  'ID'           => $post_id,
                  'post_status'  => 'publish',
                  'post_title'   => $post_title,
                  'post_name'    => $slug,
            );
        }
        
        wp_update_post( $my_post );
        add_action( 'save_post', [ $this, 'exms_save_point_type_data' ], 99, 3 );
    }

    /**
     * Removed unwanter metaboxes on point type
     */
    public function exms_remove_unwanted_meta_boxes() {

        remove_meta_box( 'commentstatusdiv', 'exms_points', 'normal' );
        remove_meta_box( 'commentsdiv', 'exms_points', 'normal' );
        remove_meta_box( 'postcustom', 'exms_exms_points', 'normal' );
    }

    /**
     * Save point balance
     */
    public function exms_save_point_type() {

        $response = [];
        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : '';
        if( empty( $user_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'User ID Not found.', WP_EXAMS );
            echo json_encode( $response );
            wp_die();
        }

        $total_points = isset( $_POST['total_points'] ) ? intval( $_POST['total_points'] ) : '';
        if( empty( $total_points ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Total Point Not found.', WP_EXAMS );
            echo json_encode( $response );
            wp_die();
        }

        $point_type = isset( $_POST['point_type'] ) ? intval( $_POST['point_type'] ) : '';
        if( empty( $point_type ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Point Type Not found.', WP_EXAMS );
            echo json_encode( $response );
            wp_die();
        }

        $manual_points = isset( $_POST['manual_points'] ) ? intval( $_POST['manual_points'] ) : '';
        if( empty( $manual_points ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Manual points Not found.', WP_EXAMS );
            echo json_encode( $response );
            wp_die();
        }

        /**
         * Save quiz result
         */
        wp_exams()->dbquizres->exms_db_insert( 'quizzes_results', array(
            'user_id'           => $user_id,
            'quiz_id'           => 0,
            'parent_posts'      => 'manual_'.rand( 10, 7841 ),
            'total_points'      => $manual_points,
            'obtained_points'   => 0,
            'points_type'       => $point_type,
            'total_questions'   => 0,
            'correct_questions' => 0,
            'passed'            => 0,
            'percentage'        => 0,
            'essay_ids'         => []
        ) );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Create function to get point
     *
     * @param $type  define point type
     * @param $option
     */
    public static function exms_get_all_point_type( $type, $options, $is_parent = false ) {

        $question_val = isset( $options['exms_point_type_question'] ) ? $options['exms_point_type_question'] : ''; 

        $name = 'exms_point_type_' . $type;
        $selected_p_type = isset( $options[$name] ) ? $options[$name] : 0;

        $point_types = get_posts( [
            'posts_per_page' => -1,
            'post_type'      => 'exms_points',
            'post_status'    => 'publish'
        ] );

        ?>
        <!-- create point type dropdown -->
        <div class="exms-sub-title <?php echo $is_parent && true === $is_parent ? 'exms-remove-padding' : ''; ?>">
            <?php _e( 'Points type', WP_EXAMS ); ?>
        </div>
        <div>
            <select class="exms-points-dropdown" name="exms_point_type_<?php echo $type; ?>">
                <option value="select_point_type"><?php _e( 'Select point type', WP_EXAMS ); ?></option><?php
                if( $point_types ) {

                    foreach( $point_types as $point_type ) {

                        ?><option value="<?php echo $point_type->ID; ?>" <?php echo $point_type->ID == $selected_p_type ? 'selected="selected"' : ''; ?> >
                            <?php echo $point_type->post_title; ?>
                        </option><?php
                    }
                }
            ?></select>
        </div><?php
    }

    /**
     * HTML for point type data
     * 
     * @param $post
     */
    public static function exms_point_type_data_html( $post ) {

        $post_id = $post->ID;
        // $options = exms_get_post_options( $post_id );
        // $plural_name = isset( $options['exms_point_type_plural'] ) ? $options['exms_point_type_plural'] : '';

        $plural_name = get_post_meta( $post_id, 'exms_point_type_plural', true );
        $post_type = get_post_type( $post_id );
        if( 'publish' != $post_type ) {
            $post_id = 0;
        }

        ?>
        <div class="exms-point-type-data-wrap">
            <div class="exms-point-type-rows">
                <label class="exms-point-type-lable"><?php _e( 'Singular Name', WP_EXAMS ); ?></label>
                <input type="text" name="exms_point_type_singular" class="exms-point-type-input" value="<?php echo get_the_title( $post_id ); ?>">
                <span class="exms-point-type-desc"><?php _e( 'The singular name for this points type.', WP_EXAMS ); ?></span>
            </div>

            <div class="exms-point-type-rows">
                <label class="exms-point-type-lable"><?php _e( 'Plural Name', WP_EXAMS ); ?></label>
                <input type="text" name="exms_point_type_plural" class="exms-point-type-input" value="<?php echo $plural_name; ?>">
                <span class="exms-point-type-desc"><?php _e( 'The plural name for this points type.', WP_EXAMS ); ?></span>
            </div>

            <div class="exms-point-type-rows exms-remove-border">
                <label class="exms-point-type-lable"><?php _e( 'Slug', WP_EXAMS ); ?></label>
                <input type="text" name="exms_point_type_slug" class="exms-point-type-input" value="<?php echo $post->post_name; ?>">
                <span class="exms-point-type-slug"><?php _e( 'Slug is used for internal references.', WP_EXAMS ); ?></span>
            </div>

        </div>
        <?php
    }
}

/**
 * Initialize EXMS_Point_Type
 */
EXMS_Point_Type::instance();