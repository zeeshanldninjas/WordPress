<?php

/**
 * Template for EXMS Post Relations
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Post_Relations extends EXMS_DB_Main {

    /**
     * @var self
     */
    private static $instance;
    private static $wpdb;
	private $structure_post_type = false;
	private $table_check = false;
	public static $existing_parent_result;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Post_Relations ) ) {

            self::$instance = new EXMS_Post_Relations;
            $post_types = EXMS_Setup_Functions::get_setup_post_types();
            $count = 0;
            if ( $post_types && is_array( $post_types ) ) {
                foreach ( $post_types as $post_name => $s_post_type ) {
                    $count++;
                    $post_type_name = str_replace('_', '-', $post_name );
                    if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] === $post_type_name ) || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) ) {
                        self::$instance->structure_post_type = $post_type_name;
                        break;
                    }
                }
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'admin_notices', [ $this, 'exms_show_missing_table_notice' ] );
        add_action( 'save_post', [ $this, 'exms_save_post_users' ], 11, 3 );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_post_structure_enqueue'] );
        add_action( 'wp_ajax_create_exms_post_structure_table', [ $this , 'create_exms_post_structure_table' ] );
        add_action( 'add_meta_boxes', [ $this, 'exms_admin_metaboxes' ], 10, 2 );
        add_action( 'save_post', [ $this, 'exms_save_post_type_structure' ], 11, 3 );
        add_action( 'save_post', [ $this, 'exms_save_post_relations' ], 11, 3 );
    }

    /**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->structure_post_type ) {
            return false;
        }

        $table_exists = $this->exms_validate_table();
        if( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        if( !self::$instance->table_check ) {
            $ajax_action = 'create_exms_post_structure_table';
            $table_names = $table_exists;
            require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
        }
    }

    /**
     * If table not exist will pass in the array
     */
    public function exms_validate_table() {
        
        global $wpdb;
        
		$user_enrollments = $wpdb->prefix.'exms_user_enrollments';
		$post_settings = $wpdb->prefix.'exms_post_settings';
		$post_relation_table = $wpdb->prefix.'exms_post_relationship';
		$user_tracking = $wpdb->prefix.'exms_user_progress_tracking';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $user_enrollments ) ) {
            $not_exist_tables[] = 'user_enrollments';
        }

        if ( !$this->exms_table_exists( $post_settings ) ) {
            $not_exist_tables[] = 'post_settings';
        }

        if ( !$this->exms_table_exists( $post_relation_table ) ) {
            $not_exist_tables[] = 'post_relationship';
        }

        if ( !$this->exms_table_exists( $user_tracking ) ) {
            $not_exist_tables[] = 'user_progress_tracking';
        }
        return $not_exist_tables;
    }

    /**
     * Post structure Functionality Files added and nonce creation
     */
    public function exms_post_structure_enqueue() {
        
        if ( !$this->structure_post_type ) {
            return false;
        }
        
        wp_enqueue_style( 'exms-post-relations-css', EXMS_ASSETS_URL . '/css/admin/post-type-structures/exms-post-relations.css', [], EXMS_ASSETS_URL, null );

        wp_enqueue_script( 'exms-post-relations-js', EXMS_ASSETS_URL . '/js/admin/post-type-structures/exms-post-relations.js', [ 'jquery' ], EXMS_ASSETS_URL, true );
        wp_localize_script( 'exms-post-relations-js', 'EXMS_POST_STRUCTURE', 
            [ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_post_structure_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
                'processing'                        => __( 'processing...', 'exms' ),
                'create_table'                      => __( 'Create tables', 'exms' ),
                'error_text'                        => __( 'Error', 'exms' ),
            ] 
        );
    }

    /**
     * Create post structure tables
     */
    public function create_exms_post_structure_table() {

        check_ajax_referer( 'create_post_structure_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        
                        case 'user_enrollments':
                            if ( !class_exists( 'EXMS_DB_USER_ENROLLMENTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.user_enrollments.php';     
                            }
                            $user_enrollments = new EXMS_DB_USER_ENROLLMENTS();
                            $user_enrollments->run_table_create_script();
                            break;
                        
                        case 'post_settings':
                            if ( !class_exists( 'EXMS_DB_EXAMS_STRUCTURE_TYPE' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.post_settings.php';     
                            }
                            $post_settings = new EXMS_DB_EXAMS_STRUCTURE_TYPE();
                            $post_settings->run_table_create_script();
                            break;
                        
                        case 'post_relationship':
                            if ( !class_exists( 'EXMS_DB_POST_RELATIONSHIP' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.post_relationship.php';     
                            }
                            $post_relationship = new EXMS_DB_POST_RELATIONSHIP();
                            $post_relationship->run_table_create_script();
                            break;
                        
                        case 'user_progress_tracking':
                            if ( !class_exists( 'EXMS_DB_USER_PROGRESS_TRACKING' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.user_progress_tracking.php';     
                            }
                            $post_relationship = new EXMS_DB_USER_PROGRESS_TRACKING();
                            $post_relationship->run_table_create_script();
                            break;
                        
                        default:
                            wp_send_json_error( [ 'message' => sprintf(__( 'Unknown table: %s', 'exms' ), esc_html( $table_name ) ) ] );
                            return;
                    }
                }
                
                wp_send_json_success(__( 'Tables created successfully.', 'exms' ) );
            } else {
                wp_send_json_error( [ 'message' => __( 'Invalid table names format.', 'exms' ) ] );
            }
        } else {
            wp_send_json_error( [ 'message' => __( 'No table names provided.', 'exms') ] );
        }
    
        wp_die();
    }

    /**
     * Instructors assign to only parent post metabox html
     * 
     * @param $post
     */
    public static function exms_user_assign_to_parent_html( $post ) {

        echo exms_post_user_assign_to_post_html( $post );
        
    }
    /**
     * User assign to only parent post metabox html
     * 
     * @param $post
     */
    public static function exms_post_user_assign_to_parent_html( $post ) {

        echo exms_user_assign_to_post_html( $post );
    }

    /**
     * Post parent relations metabox content html
     * 
     * @param $post
     */
    public static function exms_post_parent_relation_html( $post ) {

        $post_id = $post->ID;
        $post_type = get_post_type( $post_id );

        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        $parent_meta = isset( $post_types[$post_type] ) ? $post_types[$post_type] : '';
        $parent_post_type = isset( $parent_meta['parent_post_type'] ) ? $parent_meta['parent_post_type'] : '';
        echo exms_parent_post_assign_html( $post, $parent_post_type );
    }

    /**
     * Post Assign and unassign metabox content HTML
     * Hierarchy of parent to child post relations
     * @param $post ( Object )
     */
    public static function exms_post_relation_metabox_html( $post ) {

        $post_id    = $post->ID;
        $post_type  = get_post_type( $post_id );
        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        if( ! $post_types || ! is_array( $post_types ) ) {
            return;
        }
        
        $post_type_keys = array_keys( $post_types );
        $current_index  = array_search( $post_type, $post_type_keys );
        
        $is_empty       = 0;

        if( $current_index === false ) {
            echo '<div class="exms-error-notification">' . esc_html__( 'Post type not part of setup hierarchy.', 'exms' ) . '</div>';
            return;
        }
        $next_child_slug = $post_type_keys[ $current_index + 1 ] ?? '';
        
        foreach( $post_type_keys as $child_slug ) {
            
            if( $child_slug !== $next_child_slug && $child_slug !== 'exms-quizzes' ) {
                continue;
            }

            echo exms_parent_post_assign_html( $post, $child_slug );
            $is_empty++;
        }

        if( $is_empty === 0 ) {
            echo '<div class="exms-error-notification">' .
                sprintf(
                    __( 'The %s does not have any assignable child content.', 'exms' ),
                    ucwords( str_replace( '_', '-', $post_type ) )
                ) .
                '</div>';
        }
    }


    /**
	 * Save group assign/un-assign user id
	 * @param $post_id, $post, $update
	 */
	public function exms_save_post_users( $post_id, $post, $update ) {

		if( $update != true ) {
            return false;
        }

        global $wpdb;
        $post_type = get_post_type( $post_id );
        $parent_post = EXMS_PR_Fn::exms_get_parent_post_type();
        if( 'exms-quizzes' == $post_type || 'exms-groups' == $post_type || $parent_post != $post_type ) {
        	return false;
        }

        $assign_ids = isset( $_POST['exms_assign_items']['current'] ) ? $_POST['exms_assign_items']['current'] : [];
        $unassign_ids = isset( $_POST['exms_unassign_items']['current'] ) ? $_POST['exms_unassign_items']['current'] : [];

        $table_name = EXMS_PR_Fn::exms_user_post_table();

        if( $unassign_ids && is_array( $unassign_ids ) ) {

            $wpdb->query( 
                "DELETE FROM $table_name
                 WHERE post_id = $post_id 
                 AND user_id IN('" . implode( "', '", $unassign_ids ) . "') "
            );

            /**
             * Fires after the elements un-assigned successfully
             * 
             * @param $unassign_ids ( Ids of the un-assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $unassign_ids, false );
        }

        if( $assign_ids && is_array( $assign_ids ) ) {
            foreach( $assign_ids as $order => $assign_id ) {
                if( ! exms_user_id_exists( $assign_id ) ) {
                    continue;
                }

                $user_ids = [];
                $params = [
                    [
                        'field' => 'post_id',
                        'value' => $post_id,
                        'operator' => '=',
                        'type' => '%d'
                    ],
                    [
                        'field' => 'user_id',
                        'value' => $assign_id,
                        'operator' => '=',
                        'type' => '%d'
                    ]
                ];

                $columns = [ 'user_id' ];
                $quiz_meta = $this->exms_db_query( 'select', 'exms_user_enrollments', $params, $columns );

                if( ! empty( $quiz_meta ) && ! is_null( $quiz_meta ) ) {
                    $user_ids = array_map( 'intval', array_column( $quiz_meta, 'user_id' ) );
                }

                if( in_array( $assign_id, $user_ids ) ) {
                    continue;
                }

                $user_type = '';
                $ud = get_userdata( $assign_id );
                if ( $ud && ! empty( $ud->roles ) && is_array( $ud->roles ) ) {
                    if( in_array( 'exms_instructor', $ud->roles, true ) || in_array( 'exms-instructor', $ud->roles, true ) ) {
                        $user_type = 'instructor';
                    } else if( in_array( 'exms_student', $ud->roles, true ) || in_array( 'exms-student', $ud->roles, true ) ) {
                        $user_type = 'student';
                    }
                }

                $post_type_value = get_post_type( $post_id );
                $enrolled_by = get_current_user_id();

                $data = [
                    'post_id'           => $post_id,
                    'user_id'           => $assign_id,
                    'created_timestamp' => current_time('timestamp'),
                    'updated_timestamp' => current_time('timestamp'),
                    'type'              => $user_type,
                    'post_type'         => $post_type_value,
                    'enrolled_by'       => $enrolled_by,
                ];

                $this->exms_db_insert( 'user_enrollments', $data );

                /**
                 * Fires after user assign to the post
                 * 
                 * @param $user_id
                 * @param $post_id
                 * @param $time
                 * @param true ( means user assign in the post )
                 */
                do_action( 'exms_assign_user_on_post', $assign_id, $post_id, time(), true );
            }

            /**
             * Fires after the elements assigned successfully
             * 
             * @param $assign_id ( Ids of the assigned elements )
             * @param $results ( bool )
             */
            do_action( 'exms_assign_elements', $assign_ids, true );
        }
	}

    public function exms_admin_metaboxes( $post_type, $post ) {

        if( 'exms-quizzes' == $post_type) {
            return false;
        }

        $post_types = EXMS_Setup_Functions::get_setup_post_types();
        
        if( $post_types && is_array( $post_types ) ) {
            foreach( $post_types as $post_name => $s_post_type ) {
                $post_name = isset($s_post_type['post_type_name']) ? $s_post_type['post_type_name'] : "";

                Exms_Post_Types_Functions::add_exms_meta_box(
                    'exms-'.$post_name.'-settings',
                    ucwords( str_replace('exms-', '', $post_name) ) . __( ' Settings', 'exms' ),
                    [ 'EXMS_Post_Relations', 'exms_parent_post_setting_html' ],
                    $post_name,
                    'normal',
                    'high',
                    ''
                );

                static $count = 0;
                $count++;
                if( $count == 1 ) {
                    Exms_Post_Types_Functions::add_exms_meta_box(
                        'exms-assign-users-to-parent',
                        __( 'Instructor Assign/Unassign', 'exms' ),
                        [ 'EXMS_Post_Relations', 'exms_user_assign_to_parent_html' ],
                        $post_name,
                        'normal',
                        'high',
                        ''
                    );
                    
                    Exms_Post_Types_Functions::add_exms_meta_box(
                        'exms-assign-post-users-to-parent',
                        __( 'Users Assign/Unassign', 'exms' ),
                        [ 'EXMS_Post_Relations', 'exms_post_user_assign_to_parent_html' ],
                        $post_name,
                        'normal',
                        'high',
                        ''
                    );
                }

                Exms_Post_Types_Functions::add_exms_meta_box(
                    'exms-post-relations',
                    __( 'Post Relations', 'exms' ),
                    [ 'EXMS_Post_Relations', 'exms_post_relation_metabox_html' ],
                    $post_name,
                    'normal',
                    'high',
                    ''
                );
            }
        }
    }


    /**
     * Save parent post settings result display settings to custom table `exms_post_settings`
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     */
    public function exms_save_post_type_structure( $post_id, $post, $update ) {

        global $wpdb;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if ( $update !== true ) {
            return false;
        }

        $post_id = isset( $post->ID ) ? $post->ID : 0;
        $post_type = get_post_type( $post_id );

        if ( $post_type ) {
            $post_type_obj = get_post_type_object( $post_type );
            if ( $post_type_obj ) {
                $post_name = $post_type_obj->labels->singular_name;
            }
        }

        if ( $post_type === 'exms-quizzes' ) {
            return false;
        }

        $post_settings_table = $wpdb->prefix . 'exms_post_settings';
        $course_url = isset( $_POST['exms_' . $post_name . '_video_url'] ) ? $_POST['exms_' . $post_name . '_video_url'] : '';
        $progress_type = isset( $_POST['exms_'.$post_name.'_progress_type'] ) ? $_POST['exms_'.$post_name.'_progress_type'] : '';

        if ( $post_type === 'exms-courses' ) {

            $achievement_points = isset( $_POST['exms_' . $post_name . '_points'] ) ? $_POST['exms_' . $post_name . '_points'] : 0;
            $purchase_type      = isset( $_POST['exms_' . $post_name . '_type'] ) ? $_POST['exms_' . $post_name . '_type'] : 'free';
            $price              = isset( $_POST['exms_' . $post_name . '_price'] ) ? $_POST['exms_' . $post_name . '_price'] : 0;
            $subscription       = isset( $_POST['exms_' . $post_name . '_sub_days'] ) ? $_POST['exms_' . $post_name . '_sub_days'] : 0;
            $close_url          = isset( $_POST['exms_' . $post_name . '_close_url'] ) ? $_POST['exms_' . $post_name . '_close_url'] : '';
            $seat_limit         = isset( $_POST['exms_' . $post_name . '_seat_limit'] ) ? $_POST['exms_' . $post_name . '_seat_limit'] : '';

            $post_settings_data = [
                'parent_post_id'             => $post_id,
                'parent_post_type'           => $purchase_type,
                'parent_post_price'          => $price,
                'subscription_days'          => $subscription,
                'redirect_url'               => $close_url,
                'parent_achievement_points'  => $achievement_points,
                'seat_limit'                 => $seat_limit,
                'progress_type'              => $progress_type,
                'video_url'                  => $course_url,
                'post_type_slug'             => $post_type,
            ];
        } else {

            $post_settings_data = [
                'parent_post_id' => $post_id,
                'video_url'      => $course_url,
                'post_type_slug' => $post_type,
            ];
        }

        $post_settings_params = [
            [
                'field'    => 'parent_post_id',
                'value'    => $post_id,
                'operator' => '=',
                'type'     => '%d'
            ]
        ];

        $columns = [ 'id' ];
        $existing_post_settings_type = $this->exms_db_query( 'select', 'exms_post_settings', $post_settings_params, $columns );

        if ( $existing_post_settings_type ) {
            $wpdb->update( $post_settings_table, $post_settings_data, [ 'parent_post_id' => $post_id ] );
        } else {
            $result = $this->exms_db_insert( 'post_settings', $post_settings_data );
        }

        do_action( 'exms_exam_structure_settings_saved', $post_id );
    }


    /**
	 * Parent Post settings html
	 */
	public static function exms_parent_post_setting_html( $post, $args ) {

		if ( !self::$instance->structure_post_type ) {
            return false;
        }

        global $wpdb;

        $table_exists = self::$instance->exms_validate_table();
        if ( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        $parent_post_id = $post->ID;
        $post_settings_table = $wpdb->prefix . 'exms_post_settings';

        $post_settings_params = [
            [
                'field'    => 'parent_post_id',
                'value'    => $parent_post_id,
                'operator' => '=',
                'type'     => '%d'
            ]
        ];

        self::$existing_parent_result = self::$instance->exms_db_query( 'select', 'exms_post_settings', $post_settings_params );

		/**
         * Parent Post metaboxes
         */
        if( file_exists( EXMS_TEMPLATES_DIR . '/post-type-structures/exms-parent-settings.php' ) ) {
            require_once EXMS_TEMPLATES_DIR . '/post-type-structures/exms-parent-settings.php';
        }
	}

    /**
     * Saving relations in the custom table
     * @param mixed $post_id
     * @param mixed $post
     * @param mixed $update
     * @return bool
     */
    public function exms_save_post_relations( $post_id, $post, $update ) {
        
        if ( ! $update ) {
            return false;
        }

        global $wpdb;
        $post_type = get_post_type( $post_id );
        $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
        $all_post_types = get_option( 'exms_post_types');
        $current_parent_post_type = '';
        $child_post_types = [];
        if( is_array($all_post_types) && !empty( $all_post_types) ) {

            $first_key = array_key_first($all_post_types);
            $current_parent_post_type = $all_post_types[$first_key]['post_type_name'];
            unset($all_post_types[$first_key]);
            $child_post_types = array_column($all_post_types, 'post_type_name');
        }
        
        if ( $current_parent_post_type != $parent_post_type || 'exms-quizzes' == $post_type || 'exms-questions' == $post_type && !in_array($post_type,$child_post_types) ) {
            return false;
        }

        $table_name = EXMS_PR_Fn::exms_relation_table_name();

        if ( isset( $_POST['exms_assign_items']['parent'] ) ) {
            
            $assign_ids   = array_map( 'intval', $_POST['exms_assign_items']['parent'] );
            $parent_post_type_clean = str_replace( 'exms-', '', $post_type );
            if ( ! empty( $assign_ids ) ) {
                foreach ( $assign_ids as $assign_id ) {
                    
                    $child_type  = get_post_type( $assign_id );
                    $post_type_clean        = str_replace( 'exms-', '', $child_type );

                    $existing = $wpdb->get_var( $wpdb->prepare(
                        "SELECT COUNT(*) FROM $table_name WHERE parent_post_id = %d AND child_post_id = %d",
                        $post_id, $assign_id
                    ));

                    if ( $existing ) continue;
                    
                    $data = [
                        'parent_post_id'   => $post_id,
                        'child_post_id' => $assign_id,
                        'parent_post_type'  => $post_type,
                        'assigned_post_type'  => $child_type,
                        'relationship_type'  => $parent_post_type_clean . '-' .  $post_type_clean,
                        'created_at'         => current_time('timestamp'),
                    ];
                    $this->exms_db_insert( 'post_relationship', $data );
                }
            }
        } 
        if ( isset( $_POST['exms_unassign_items']['parent'] ) ) {
            $unassign_ids = isset( $_POST['exms_unassign_items']['parent'] ) ? array_map( 'intval', $_POST['exms_unassign_items']['parent'] ) : [];
            if ( ! empty( $unassign_ids ) ) {
                foreach ( $unassign_ids as $unassign_id ) {
                    $delete_params = [
                        [
                            'field' => 'parent_post_id',
                            'value' => $post_id,
                            'operator' => '=',
                            'type' => '%d'
                        ],
                        [
                            'field' => 'child_post_id',
                            'value' => $unassign_id,
                            'operator' => '=',
                            'type' => '%d'
                        ]
                    ];
                    $result = $this->exms_db_query( 'delete', 'exms_post_relationship', $delete_params );
                }
            }
        }
    }
}

EXMS_Post_Relations::instance();