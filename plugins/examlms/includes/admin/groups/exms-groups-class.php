<?php
/**
 * WP EXAMS - Groups
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Groups
 *
 * Base class to define all groups functions
 */
class EXMS_Groups extends EXMS_DB_Main {

	private static $instance;
    private $group_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Groups ) ) {

        	self::$instance = new EXMS_Groups;
            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'exms-groups' || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' && get_post_type( $_GET['post'] ) === 'exms-groups' ) ) {
				self::$instance->group_page = true;
			}
            self::$instance->hooks();
        }

        return self::$instance;
    }

    public function hooks() {
		
        add_action( 'admin_notices', [$this, 'exms_show_missing_table_notice'] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_group_enqueue'] );
        add_action( 'wp_ajax_create_exms_group_table', [ $this , 'create_exms_group_table' ] );
        add_action( 'add_meta_boxes', [ $this, 'exms_admin_metaboxes' ], 10, 2 );
        add_action( 'save_post', [ $this, 'exms_save_post_relations' ], 11, 3 );
        add_action( 'save_post', [ $this, 'exms_save_post_users' ], 11, 3 );
        add_action( 'save_post', [ $this, 'exms_save_group' ], 11, 3 );
	}

    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->group_page ) {
            return false;
        }

        $table_exists = $this->exms_validate_table();
            if( empty( $table_exists ) ) {
                self::$instance->table_check = true;
            }

            if( !self::$instance->table_check ) {
                $ajax_action = 'create_exms_group_table';
                $table_names = $table_exists;
                require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
            }
    }

    /**
     * Group Functionality Files added and nonce creation
     */
    public function exms_group_enqueue() {
        
        if ( !$this->group_page ) {
            return false;
        }
        
        wp_enqueue_style( 'exms-post-relations-css', EXMS_ASSETS_URL . '/css/admin/post-type-structures/exms-post-relations.css', [], EXMS_VERSION, null );
        wp_enqueue_script( 'EXMS_group_settings_js', EXMS_ASSETS_URL . 'js/admin/group/exms-group-settings.js', [ 'jquery' ], false, true );
        
        wp_localize_script( 'EXMS_group_settings_js', 'EXMS_GROUP', 
        [ 
            'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
            'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
            'create_table_nonce'                => wp_create_nonce( 'create_group_tables_nonce' ),
            'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
            'processing'                        => __( 'processing...', 'exms' ),
            'create_table'                      => __( 'Create tables', 'exms' ),
            'error_text'                        => __( 'Error', 'exms' ),
            ] 
        );
        wp_enqueue_script( 'exms-post-relations-js', EXMS_ASSETS_URL . '/js/admin/post-type-structures/exms-post-relations.js', [ 'jquery' ], EXMS_VERSION, true );
    }

    /**
     * If table not exist will pass in the array
     */
    public function exms_validate_table() {
        
        global $wpdb;
        
		$group = $wpdb->prefix.'exms_group';
		$group_post = $wpdb->prefix.'exms_group_post';
		$post_relationship = $wpdb->prefix.'exms_post_relationship';
        $user_enrollments = $wpdb->prefix.'exms_user_enrollments';

        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $group_post ) ) {
            $not_exist_tables[] = 'group_post';
        }

        if ( !$this->exms_table_exists( $post_relationship ) ) {
            $not_exist_tables[] = 'post_relationship';
        }

        if ( !$this->exms_table_exists( $user_enrollments ) ) {
            $not_exist_tables[] = 'user_enrollments';
        }

        if ( !$this->exms_table_exists( $group ) ) {
            $not_exist_tables[] = 'group';
        }
        return $not_exist_tables;
    }

	/**
     * Create attendance tables
     */
    public function create_exms_group_table() {

        check_ajax_referer( 'create_group_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        case 'group_post':
                            if ( !class_exists( 'EXMS_DB_GROUP_POST' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.group_post.php';     
                            }
                            $group_post = new EXMS_DB_GROUP_POST();
                            $group_post->run_table_create_script();
                            break;

                        case 'post_relationship':
                            if ( !class_exists( 'EXMS_DB_POST_RELATIONSHIP' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.post_relationship.php';     
                            }
                            $post_relationship = new EXMS_DB_POST_RELATIONSHIP();
                            $post_relationship->run_table_create_script();
                            break;

                        case 'user_enrollments':
                            if ( !class_exists( 'EXMS_DB_USER_ENROLLMENTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.user_enrollments.php';     
                            }
                            $user_enrollments = new EXMS_DB_USER_ENROLLMENTS();
                            $user_enrollments->run_table_create_script();
                            break;

                        case 'group':
                            if ( !class_exists( 'EXMS_DB_GROUP' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.group.php';     
                            }
                            $group = new EXMS_DB_GROUP();
                            $group->run_table_create_script();
                            break;

                        default:
                            wp_send_json_error( [ 'message' => sprintf(__( 'Unknown table: %s', 'cafe-sultan-management' ), esc_html( $table_name ) ) ] );
                            return;
                    }
                }
                
                wp_send_json_success( __( 'Tables created successfully.', 'cafe-sultan-management' ) );
            } else {
                wp_send_json_error( [ 'message' => __( 'Invalid table names format.', 'cafe-sultan-management' ) ] );
            }
        } else {
            wp_send_json_error( [ 'message' => __( 'No table names provided.', 'cafe-sultan-management') ] );
        }
    
        wp_die();
    }

    public function exms_admin_metaboxes( $post_type, $post ) {

        $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
        $existing_labels = Exms_Core_Functions::get_options('labels');
        $group_singular = '';
        $quiz_singular = '';

        if ( is_array( $existing_labels ) && array_key_exists( 'exms_qroup', $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
            $group_singular = $existing_labels['exms_qroup'];
            $quiz_singular = $existing_labels['exms_quizzes'];
        }

        Exms_Post_Types_Functions::add_exms_meta_box( 'exms_settings_metabox', $group_singular . __( ' Settings', 'exms' ), [ 'EXMS_Groups', 'exms_group_settings' ], 'exms-groups', 'normal', 'high', 'exms_create_group_settings' );
        if ( $parent_post_type !== '' && $parent_post_type !== 'exms-quizzes' ) {

            Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-parent-post-to-group', __( 'Post Assign/Unassign to ' . $group_singular, 'exms' ), [ 'EXMS_Groups', 'exms_parent_group_metabox_html' ], 'exms-groups', 'normal', 'high', '' );
        }

        /**
         * Metabox for assign quiz to the group
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-quizzes-to-group', $quiz_singular . __( ' Assign/Unassign to ' . $group_singular, 'exms' ), [ 'EXMS_Groups', 'exms_quizzes_group_metabox_html' ], 'exms-groups', 'normal', 'high', '' );

        /**
         * Metabox for assign leader to group
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-group-users', __( 'Leaders Assign/Unassign to ' . $group_singular, 'exms' ), [ 'EXMS_Groups', 'exms_user_assign_group_metabox_html' ], 'exms-groups', 'normal', 'high', '' );

        /**
         * Metabox for assign User to group
         */
        Exms_Post_Types_Functions::add_exms_meta_box(
            'exms-assign-group-users-to-parent',
            __( 'Users Assign/Unassign to ' . $group_singular, 'exms' ),
            [ 'EXMS_Groups', 'exms_group_user_assign_to_parent_html' ],
            'exms-groups',
            'normal',
            'high',
            ''
        );
    }

    /**
     * Assign users to the group
     * 
     * @param $post
     */
    public static function exms_user_assign_group_metabox_html( $post ) {

        echo exms_user_assign_to_post_html( $post );
    }

    /**
     * Assign quizzes to the group
     * 
     * @param $post
     */
    public static function exms_quizzes_group_metabox_html( $post ) {

        echo exms_parent_post_assign_html( $post, 'exms-quizzes' );
    }

    /**
     * User assign to only group student metabox html
     * 
     * @param $post
     */
    public static function exms_group_user_assign_to_parent_html( $post ) {

        echo exms_group_user_assign_to_post_html( $post );
    }

    /**
     * Assign parent post type to the group
     * 
     * @param $post
     */
    public static function exms_parent_group_metabox_html( $post ) {

        $parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();
        echo exms_parent_post_assign_html( $post, $parent_post_type  );
    }

    /**
     * Saving relations in the custom table
     * @param mixed $post_id
     * @param mixed $post
     * @param mixed $update
     * @return bool
     */
    public function exms_save_post_relations( $post_id, $post, $update ) {
    
        global $wpdb;
        if ( ! $update ) return false;
        $post_type = get_post_type( $post_id );

        if ( $post_id !== 'exms-groups' ) {
            return;
        }
        
        $table_name = EXMS_PR_Fn::exms_relation_table_name();

        if ( isset( $_POST['exms_assign_items']['parent'] ) ) {
            $assign_ids   = array_map( 'intval', $_POST['exms_assign_items']['parent'] );
            $unassign_ids = isset( $_POST['exms_unassign_items']['parent'] ) ? array_map( 'intval', $_POST['exms_unassign_items']['parent'] ) : [];
            $assigned_post_type = sanitize_text_field( $_POST['exms_parent_relation'] );

            $is_assigning_to_current = ( $post_type === 'exms-groups' && $assigned_post_type === 'exms-quizzes' );

            foreach ( $unassign_ids as $related_id ) {
                $parent_id = $is_assigning_to_current ? $post_id : $related_id;
                $child_id  = $is_assigning_to_current ? $related_id : $post_id;
                
                $this->exms_db_query( 'delete', 'exms_post_relationship', [
                    [ 'field' => 'parent_post_id', 'value' => $parent_id, 'operator' => '=', 'type' => '%d' ],
                    [ 'field' => 'child_post_id',  'value' => $child_id,  'operator' => '=', 'type' => '%d' ],
                ]);
            }

            foreach ( $assign_ids as $related_id ) {
                if ( ! get_post_type( $related_id ) ) continue;

                $parent_id = $is_assigning_to_current ? $post_id : $related_id;
                $child_id  = $is_assigning_to_current ? $related_id : $post_id;

                $existing = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE parent_post_id = %d AND child_post_id = %d",
                    $parent_id, $child_id
                ));

                if ( $existing ) continue;

                $parent_type = get_post_type( $parent_id );
                $child_type  = get_post_type( $child_id );

                $data = [
                    'parent_post_id'     => $parent_id,
                    'child_post_id'      => $child_id,
                    'parent_post_type'   => $parent_type,
                    'assigned_post_type' => $child_type,
                    'relationship_type'  => str_replace( 'exms_', '', $parent_type ) . '-' . str_replace( 'exms_', '', $child_type ),
                    'created_at'         => current_time( 'timestamp' ),
                ];

                $this->exms_db_insert( 'post_relationship', $data );
            }
        }

        if ( isset( $_POST['exms_unassign_items']['parent'] ) ) {
            $unassign_ids = isset( $_POST['exms_unassign_items']['parent'] ) ? array_map( 'intval', $_POST['exms_unassign_items']['parent'] ) : [];
            $assigned_post_type = sanitize_text_field( $_POST['exms_parent_relation'] );

            $is_assigning_to_current = ( $post_type === 'exms-groups' && $assigned_post_type === 'exms-quizzes' );

            foreach ( $unassign_ids as $related_id ) {
                $parent_id = $is_assigning_to_current ? $post_id : $related_id;
                $child_id  = $is_assigning_to_current ? $related_id : $post_id;
                
                $this->exms_db_query( 'delete', 'exms_post_relationship', [
                    [ 'field' => 'parent_post_id', 'value' => $parent_id, 'operator' => '=', 'type' => '%d' ],
                    [ 'field' => 'child_post_id',  'value' => $child_id,  'operator' => '=', 'type' => '%d' ],
                ]);
            }
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
        if( 'exms-groups' != $post_type ) {
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

            $course_ids = exms_get_group_course_ids( $post_id );
            if( ! empty( $course_ids ) ) {

                $course_ids_sql = implode( ',', array_map( 'intval', $course_ids ) );
                foreach( $unassign_ids as $uid ) {

                    $uid = (int) $uid;
                    $ud  = get_userdata( $uid );

                    $is_student = ( $ud && ! empty( $ud->roles ) && is_array( $ud->roles ) ) &&
                                ( in_array( 'exms_student', $ud->roles, true ) || in_array( 'exms-student', $ud->roles, true ) );

                    if( ! $is_student ) {
                        continue;
                    }
                    $wpdb->query(
                        "DELETE FROM {$table_name}
                        WHERE user_id = {$uid}
                        AND post_type = 'exms-courses'
                        AND enrolled_by = " . (int) $post_id . "
                        AND post_id IN({$course_ids_sql})"
                    );
                }
            }

            $quiz_ids = exms_get_group_quiz_ids( $post_id );
            if( ! empty( $quiz_ids ) ) {

                $quiz_ids_sql = implode( ',', array_map( 'intval', $quiz_ids ) );
                foreach( $unassign_ids as $uid ) {

                    $uid = (int) $uid;
                    $ud  = get_userdata( $uid );

                    $is_student = ( $ud && ! empty( $ud->roles ) && is_array( $ud->roles ) ) &&
                                ( in_array( 'exms_student', $ud->roles, true ) || in_array( 'exms-student', $ud->roles, true ) );

                    if( ! $is_student ) {
                        continue;
                    }
                    $wpdb->query(
                        "DELETE FROM {$table_name}
                        WHERE user_id = {$uid}
                        AND post_type = 'exms-quizzes'
                        AND enrolled_by = " . (int) $post_id . "
                        AND post_id IN({$quiz_ids_sql})"
                    );
                }
            }

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
                    if( in_array( 'exms_group_leader', $ud->roles, true ) || in_array( 'exms-group-leader', $ud->roles, true ) ) {
                        $user_type = 'leader';
                    }
                    if( in_array( 'exms_student', $ud->roles, true ) || in_array( 'exms-student', $ud->roles, true ) ) {
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

                $is_student = false;
                $ud2 = get_userdata( $assign_id );
                if ( $ud2 && ! empty( $ud2->roles ) && is_array( $ud2->roles ) ) {
                    if ( in_array( 'exms_student', $ud2->roles, true ) || in_array( 'exms-student', $ud2->roles, true ) ) {
                        $is_student = true;
                    }
                }

                if( $is_student ) {

                    $course_ids = exms_get_group_course_ids( $post_id ); 
                    $quiz_ids = exms_get_group_quiz_ids( $post_id ); 
                    if( ! empty( $course_ids ) ) {
                        foreach( $course_ids as $course_id ) {
                            $exists_params = [
                                [
                                    'field' => 'post_id',
                                    'value' => $course_id,
                                    'operator' => '=',
                                    'type' => '%d'
                                ],
                                [
                                    'field' => 'user_id',
                                    'value' => $assign_id,
                                    'operator' => '=',
                                    'type' => '%d'
                                ],
                                [
                                    'field' => 'post_type',
                                    'value' => 'exms-courses',
                                    'operator' => '=',
                                    'type' => '%s'
                                ],
                            ];

                            $exists_cols = [ 'user_id' ];
                            $exists = $this->exms_db_query( 'select', 'exms_user_enrollments', $exists_params, $exists_cols );

                            if ( ! empty( $exists ) ) {
                                continue;
                            }

                            $course_data = [
                                'post_id'           => (int) $course_id,
                                'user_id'           => (int) $assign_id,
                                'created_timestamp' => current_time('timestamp'),
                                'updated_timestamp' => current_time('timestamp'),
                                'type'              => 'group-student',
                                'post_type'         => 'exms-courses',
                                'enrolled_by'       => (int) $post_id,
                            ];
                            $this->exms_db_insert( 'user_enrollments', $course_data );
                        }
                    }
                    if( ! empty( $quiz_ids ) ) {
                        foreach( $quiz_ids as $quiz_id ) {
                            $exists_params = [
                                [
                                    'field' => 'post_id',
                                    'value' => $quiz_id,
                                    'operator' => '=',
                                    'type' => '%d'
                                ],
                                [
                                    'field' => 'user_id',
                                    'value' => $assign_id,
                                    'operator' => '=',
                                    'type' => '%d'
                                ],
                                [
                                    'field' => 'post_type',
                                    'value' => 'exms-quizzes',
                                    'operator' => '=',
                                    'type' => '%s'
                                ],
                            ];

                            $exists_cols = [ 'user_id' ];
                            $exists = $this->exms_db_query( 'select', 'exms_user_enrollments', $exists_params, $exists_cols );

                            if ( ! empty( $exists ) ) {
                                continue;
                            }

                            $course_data = [
                                'post_id'           => (int) $quiz_id,
                                'user_id'           => (int) $assign_id,
                                'created_timestamp' => current_time('timestamp'),
                                'updated_timestamp' => current_time('timestamp'),
                                'type'              => 'group-student',
                                'post_type'         => 'exms-quizzes',
                                'enrolled_by'       => (int) $post_id,
                            ];
                            $this->exms_db_insert( 'user_enrollments', $course_data );
                        }
                    }
                }
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

    /**
	 * Quiz settings html
	 */
	public static function exms_group_settings( $post, $args ) {

		if ( !self::$instance->group_page ) {
            return false;
        }

        global $wpdb;

        $table_exists = self::$instance->exms_validate_table();
        if ( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        $group_id = $post->ID;
        $group_table                   = $wpdb->prefix . 'exms_group';

        $group_data = $wpdb->get_row(
            $wpdb->prepare("
                SELECT 
                    video_url,
                    group_type,
                    group_price,
                    subscription_days,
                    seat_limit,
                    redirect_url
                FROM $group_table
                WHERE group_id = %d
            ", $group_id),
            ARRAY_A
        );
        
        $group_video_url         = "";
        $group_type         = "";
        $group_price         = "";
        $subscription         = "";
        $redirect_url         = "";
        $seat_limit         = "";
        if ( $group_data ) {
            $group_video_url         = $group_data['video_url'];
            $group_type         = $group_data['group_type'];
            $group_price         = $group_data['group_price'];
            $subscription         = $group_data['subscription_days'];
            $redirect_url         = $group_data['redirect_url'];
            $seat_limit         = $group_data['seat_limit'];
        }

		/**
         * Quiz metaboxes
         */
        if( file_exists( EXMS_TEMPLATES_DIR . '/group/exms-group-settings.php' ) ) {

            require_once EXMS_TEMPLATES_DIR . '/group/exms-group-settings.php';
        }
	}

    /**
     * Save group settings to custom table `group`
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     */
    public function exms_save_group( $post_id, $post, $update ) {

        global $wpdb;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if ( $update !== true ) {
            return false;
        }

        $post_type = get_post_type( $post_id );
        $parent_post = EXMS_PR_Fn::exms_get_parent_post_type();
        if( 'exms-quizzes' != $post_type && 'exms-groups' != $post_type && $parent_post != $post_type ) {
        	return false;
        }
        
        $group_table = $wpdb->prefix . 'exms_group';

        $group_video_url        = isset( $_POST['exms_group_video_url'] ) ? $_POST['exms_group_video_url'] : '';
        $seat_limit        = isset( $_POST['exms_group_seat_limit'] ) ? $_POST['exms_group_seat_limit'] : '';
        $group_type        = isset( $_POST['exms_group_type'] ) ? $_POST['exms_group_type'] : '';
        $group_price        = isset( $_POST['exms_group_price'] ) ? $_POST['exms_group_price'] : '';
        $group_sub_days        = isset( $_POST['exms_group_sub_days'] ) ? $_POST['exms_group_sub_days'] : '';
        $group_close_url        = isset( $_POST['exms_group_close_url'] ) ? $_POST['exms_group_close_url'] : '';

        $group_params = [
            [
                'field' => 'group_id',
                'value' => $post_id,
                'operator' => '=',
                'type' => '%d'
            ]
        ];

        $columns = [ 'id' ];
        $existing_quiz = $this->exms_db_query( 'select', 'exms_group', $group_params, $columns );
        
        $data = [
            'group_id'          => (int) $post_id,
            'seat_limit'        => (int) $seat_limit,
            'group_type'        => sanitize_text_field($group_type),
            'group_price'       => (int) $group_price,
            'subscription_days' => sanitize_text_field($group_sub_days),
            'redirect_url'      => esc_url_raw($group_close_url),
            'video_url'         => esc_url_raw($group_video_url),
        ];

        
        if( $existing_quiz ) {
            $wpdb->update( $group_table, $data, [ 'group_id' => $post_id ] );
        } else {
            $this->exms_db_insert( 'group', $data );
        }

        do_action( 'exms_group_settings_saved', $post_id );
    }
}

/**
 * Initialize EXMS_Groups
 */
EXMS_Groups::instance();