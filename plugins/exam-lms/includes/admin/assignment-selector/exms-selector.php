<?php
/**
 * WP Exams Assignment Selector
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms Assignment Selector
 *
 * create functionality to enrolled/unenrolled user/instructor on quizzes
 */
class EXMS_Assignment_Selector {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Assignment_Selector ) ) {

        	self::$instance = new EXMS_Assignment_Selector;
        	self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Initialize Hooks
     */
    public function hooks() {

        add_action( 'wp_ajax_exms_search_multiple_tags', [ $this, 'exms_search_multiple_tags' ] );
    }

    /**
     * Create multiple search tags
     * 
     * @param $post type search post type
     * @param $post_id ( post id to save )
     * @param $name ( name of save field )
     * @param $title
     */
    public function exms_create_multiple_tags( $post_type, $post_id, $name, $title ) {

        $options = exms_get_post_options( $post_id );
        
        $get_certificate_array = [];

        if( $options && is_array( $options ) ) {

            $get_certificate_array = WP_EXAMS_certificates::exms_get_specific_index_array( $options, 'exms_quiz-certificates-' );
        }

        $already_attached = isset( $options[$name] ) ? $options[$name] : '';
        ?>
        <div class="exms-row exms-quiz-settings-row exms-achivement-attched">
            <div class="exms-title">
                <?php echo __( 'Attach to existing certificate', 'exms' ); ?>
            </div>

            <div class="exms-data">
                <?php
                $certificates = exms_get_all_certificate_ids();
                if( $certificates && is_array( $certificates ) ) {
                    ?>
                    <p>
                        <input type="button" class="exms-add-certificate" value="<?php echo __( 'Add Certificate', 'exms' ); ?>">
                    </p>
                    <?php 
                    if( empty( $get_certificate_array ) ) {
                        ?>
                        <div class="exms-certificate-repeater">
                            <select name="quiz-certificates" class="exms-certificates">
                                <option value=""><?php echo __( 'Please select a certificate', 'exms' ); ?></option>
                                <?php
                                foreach( $certificates as $certificate ) {
                                    ?>
                                    <option value="<?php echo $certificate; ?>"><?php echo get_the_title( $certificate ); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                    } else {

                        foreach( $get_certificate_array as $certificate ) {
                            ?>
                            <div class="exms-certificate-repeater exms-certificate-repeater-<?php echo $certificate; ?>">
                                <select name="exms_quiz-certificates-<?php echo $certificate; ?>" class="exms-certificates">
                                    <option value=""><?php echo __( 'Please select a certificate', 'exms' ); ?></option>
                                    <?php 
                                    foreach( $certificates as $certi ) {
                                        ?>
                                        <option value="<?php echo $certi; ?>" <?php selected( $certificate,  $certi, true ); ?>><?php echo get_the_title( $certi ); ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="dashicons dashicons-no exms-delete-certificate" certificate-id="<?php echo $certi; ?>"></span>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
    <?php
}

    /**
     * Filters post type to attached ( AJAX ) 
     */
    public function exms_search_multiple_tags() {

        $search_filters = isset( $_POST['filters_found'] ) ? $_POST['filters_found'] : '';
        if( ! $search_filters ) {

            echo __( 'Search filter not found', 'exms' );
        }

        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
        if( ! $post_type ) {

            echo __( 'Post type not found', 'exms' );
        }

        $exms_query = new WP_Query( 
            [ 
                'posts_per_page'    => -1,
                's'                 => $search_filters,
                'post_type'         => $post_type,
                'post_status'       => 'publish'
            ] 
        );

        if( $exms_query->have_posts() ) {

            while( $exms_query->have_posts() ) {

                $exms_query->the_post();

                ?>
                <div class="exms-tags-search-result">
                    <li value="<?php echo get_the_ID(); ?>"><?php the_title();?></li>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="exms-tags-search-result">
                <li value="no-results-found"><?php _e( 'No results found', 'exms' ); ?></li>
            </div>
            <?php
        }
        wp_die();
    }

    /**
     * Assign instructor/student to the quiz
     *
     * @param $post_id
     * @param $user_id
     * @param $user_role
     */
    private function exms_assign_user( $post_id, $user_id, $user_role ) {

        $post_type = get_post_type( $post_id );

        $user_meta = get_userdata( $user_id );

        $user_roles = $user_meta->roles;
        if( $user_roles ) {

            if( ( 'exms_quizzes' == $post_type || 'exms_groups' == $post_type ) && in_array( $user_role, $user_roles ) ) {

                $opts = get_post_meta( $post_id, $post_type . '_opts', true );
                if( $opts ) {

                    $assigned_users = isset( $opts['exms_quiz_'.$user_role.'s_assigned'] ) ? $opts['exms_quiz_'.$user_role.'s_assigned'] : [];
                    if( ! in_array( $user_id, $assigned_users ) ) {

                        $opts['exms_quiz_'.$user_role.'s_assigned'][] = $user_id;
                        update_post_meta( $post_id, $post_type . '_opts', $opts );
                    }
                }
            }
        }
    }

    /**
     * Supported function to assign instructor
     *
     * @param $post_id
     * @param $instructor_id
     */
    public function exms_assign_instructor( $post_id, $instructor_id ) {

        $user_role = 'exms_instructor';
        $this->exms_assign_user( $post_id, $instructor_id, $user_role );
    }

    /**
     * Supported function to assign student
     *
     * @param $post_id
     * @param $student_id
     */
    public function exms_assign_student( $post_id, $student_id ) {

        $user_role = 'exms_student';
        $this->exms_assign_user( $post_id, $student_id, $user_role );
    }

    /**
     * Function to un-assign instructor/student to the quiz
     *
     * @param $post_id
     * @param $user_id
     * @param $user_role
     */
    private function exms_unassign_users( $post_id, $user_id, $user_role ) {

        $post_type = get_post_type( $post_id );

        $user_meta = get_userdata( $user_id );

        $user_roles = $user_meta->roles;
        if( $user_roles ) {

            if( ( 'exms_quizzes' == $post_type || 'exms_groups' == $post_type ) && in_array( $user_role, $user_roles ) ) {

                $opts = get_post_meta( $post_id, $post_type . '_opts', true );
                if( $opts ) {

                    $assigned_users = isset( $opts['exms_quiz_'.$user_role.'s_assigned'] ) ? $opts['exms_quiz_'.$user_role.'s_assigned'] : [];

                    if( in_array( $user_id, $assigned_users ) ) {

                        if( ( $un_assign_user = array_search( $user_id, $assigned_users ) ) !== false) {

                            unset( $assigned_users[$un_assign_user] );

                            $opts['exms_quiz_'.$user_role.'s_assigned'] = $assigned_users;

                            update_post_meta( $post_id, $post_type . '_opts', $opts );
                        }
                    }
                }
            }
        }
    }

    /**
     * Supported function to un assign instructor
     *
     * @param $post_id
     * @param $instructor_id
     */
    public function exms_unassign_instructor( $post_id, $instructor_id ) {

        $user_role = 'exms_instructor';
        $this->exms_unassign_users( $post_id, $instructor_id, $user_role );
    }

    /**
     * Supported function to un assign student
     *
     * @param $post_id
     * @param $student_id
     */
    public function exms_unassign_student( $post_id, $student_id ) {

        $user_role = 'exms_student';
        $this->exms_unassign_users( $post_id, $student_id, $user_role );
    }
}

/**
 * Initialize EXMS_Assignment_Selector
 */
EXMS_Assignment_Selector::instance();