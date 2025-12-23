<?php
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Questions
 *
 * class to define all exms-questions post type hooks
 */
class EXMS_Questions extends EXMS_DB_Main {

	private static $instance;
    private $question_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Questions ) ) {

        	self::$instance = new EXMS_Questions;
            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'exms-questions' || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' && get_post_type( $_GET['post'] ) === 'exms-questions' ) ) {
				self::$instance->question_page = true;
			}
        	self::$instance->hooks();
        }
    	
    	return self::$instance;
    }

    private function hooks() {

        add_action( 'deleted_post', [ $this, 'custom_action_on_permanent_delete' ] );
		add_action( 'admin_notices', callback: [ $this, 'exms_show_missing_table_notice' ] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_question_enqueue'] );
    	add_filter( 'manage_posts_columns', [ $this, 'exms_add_attach_quizzes_column' ], 10, 2 );
		add_action( 'manage_posts_custom_column', [ $this, 'exms_display_attach_quizzes_column' ], 10, 2 );
		add_action( 'wp_ajax_create_exms_question_table', [ $this , 'create_exms_question_table' ] );
        add_action( 'add_meta_boxes', [ $this, 'exms_admin_metaboxes' ], 10, 2 );
        add_action( 'save_post', [ $this, 'exms_save_metaboxes_meta_data' ], 10, 3 );
        add_action( 'save_post', [ $this, 'exms_save_assigned_quizzes_to_question' ], 20, 2 );
    }

    public function custom_action_on_permanent_delete( $post_id ) {
        
        global $wpdb;
        $post = get_post( $post_id );
        if( $post && isset( $post->post_type ) ) {
            $post_type = $post->post_type;
            if( 'exms-questions' === $post_type ) {
                $sql = $wpdb->prepare(
                    "DELETE FROM {$wpdb->prefix}exms_quiz_questions WHERE question_id = %d",
                    $post_id
                );
                $query = $wpdb->query( $sql );
            }
        }
    }


	/**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->question_page ) {
            return false;
        }

        $table_exists = $this->exms_validate_table();
        if( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        if( !self::$instance->table_check ) {
            $ajax_action = 'create_exms_question_table';
            $table_names = $table_exists;
            require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
        }
    }

    /**
     * Question Functionality Files added and nonce creation
     */
    public function exms_question_enqueue() {
        
        if ( !$this->question_page ) {
            return false;
        }
        
         /**
         * Add questions post type CSS
         */
        wp_enqueue_style( 'exms-question-posttype', EXMS_ASSETS_URL . '/css/admin/questions/exms-question-post-type.css', [], EXMS_VERSION, null );
        wp_enqueue_style( 'exms-post-relations-css', EXMS_ASSETS_URL . '/css/admin/post-type-structures/exms-post-relations.css', [], EXMS_VERSION, null );
        /**
         * Question post type JS
         */
        wp_enqueue_script( 'exms-question-posttype', EXMS_ASSETS_URL . '/js/admin/questions/exms-question-post-type.js', ['jquery'], false, true );
        
        wp_enqueue_script( 'exms-post-relations-js', EXMS_ASSETS_URL . '/js/admin/post-type-structures/exms-post-relations.js', [ 'jquery' ], EXMS_VERSION, true );

        /**
         * jQuery UI CSS
         */
        wp_enqueue_style( 'EXMS_jqueryui_style', EXMS_ASSETS_URL . '/css/jquery-ui.css', [], EXMS_VERSION, null );

        /**
         * jQuery UI JS
         */
        wp_enqueue_script( 'EXMS_jquery_ui', EXMS_ASSETS_URL . '/js/jquery-ui.js', [], EXMS_VERSION, true );


        /**
         * Localize script of ( exms-question-posttype )
         */
        wp_localize_script( 'exms-question-posttype', 'EXMS_QUESTION', 
            [ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_question_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
                'processing'                        => __( 'processing...', 'exms' ),
                'create_table'                      => __( 'Create tables', 'exms' ),
                'error_text'                        => __( 'Error', 'exms' ),
            ] 
        );
    }

    /**
     * If table not exist will pass in the array
     */
    public function exms_validate_table() {
        
        global $wpdb;
        
		$questions_table_name = $wpdb->prefix.'exms_questions';
		$quiz_questions_table_name = $wpdb->prefix.'exms_quiz_questions';
		$answer_table_name = $wpdb->prefix.'exms_answer';
		$exam_question_types = $wpdb->prefix.'exms_exam_question_types';
		$exam_user_attempts = $wpdb->prefix.'exms_exam_user_attempts';
		$exam_user_question_attempts = $wpdb->prefix.'exms_exam_user_question_attempts';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $questions_table_name ) ) {
            $not_exist_tables[] = 'questions';
        }
    
        if ( !$this->exms_table_exists( $quiz_questions_table_name ) ) {
            $not_exist_tables[] = 'quiz_questions';
        }
    
        if ( !$this->exms_table_exists( $answer_table_name ) ) {
            $not_exist_tables[] = 'answer';
        }
    
        if ( !$this->exms_table_exists( $exam_question_types ) ) {
            $not_exist_tables[] = 'exam_question_types';
        }
    
        if ( !$this->exms_table_exists( $exam_user_attempts ) ) {
            $not_exist_tables[] = 'exam_user_attempts';
        }
    
        if ( !$this->exms_table_exists( $exam_user_question_attempts ) ) {
            $not_exist_tables[] = 'exam_user_question_attempts';
        }
    
        return $not_exist_tables;
    }

	/**
     * Create question tables
     */
    public function create_exms_question_table() {

        check_ajax_referer( 'create_question_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        case 'questions':
                            if ( !class_exists( 'EXMS_DB_Questions' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.questions.php';     
                            }
                            $questions = new EXMS_DB_Questions();
                            $questions->run_table_create_script();
                            break;
                        
                        case 'quiz_questions':
                            if ( !class_exists( 'WP_EXAMS_DB_QUESTION_QUIZ_MAPPING' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz_questions.php';     
                            }
                            $quiz_questions = new EXMS_DB_QUIZ_QUESTION();
                            $quiz_questions->run_table_create_script();
                            break;
                        
                        case 'answer':
                            if ( !class_exists( 'EXMS_DB_ANSWER' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.answer.php';     
                            }
                            $answer = new EXMS_DB_ANSWER();
                            $answer->run_table_create_script();
                            break;
                        
                        case 'exam_question_types':
                            if ( !class_exists( 'EXMS_DB_QUESTION_TYPES' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.exam_question_types.php';     
                            }
                            $exam_question_types = new EXMS_DB_QUESTION_TYPES();
                            $exam_question_types->run_table_create_script();
                            break;
                        
                        case 'exam_user_attempts':
                            if ( !class_exists( 'EXMS_USER_ATTEMPTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.exam_user_attempts.php';     
                            }
                            $exam_user_attempts = new EXMS_USER_ATTEMPTS();
                            $exam_user_attempts->run_table_create_script();
                            break;
                        
                        case 'exam_user_question_attempts':
                            if ( !class_exists( 'EXMS_USER_QUESTION_ATTEMPTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.exam_user_question_attempts.php';     
                            }
                            $exam_user_question_attempts = new EXMS_USER_QUESTION_ATTEMPTS();
                            $exam_user_question_attempts->run_table_create_script();
                            break;

                        default:
                            wp_send_json_error( [ 'message' => sprintf( __( 'Unknown table: %s', 'exms' ), esc_html( $table_name ) ) ] );
                            return;
                    }
                }
                
                wp_send_json_success( __( 'Tables created successfully.', 'exms' ) );
            } else {
                wp_send_json_error( [ 'message' => __( 'Invalid table names format.', 'exms' ) ] );
            }
        } else {
            wp_send_json_error( [ 'message' => __( 'No table names provided.', 'exms') ] );
        }
    
        wp_die();
    }

    /**
     * Assign quizzes to question
     * @param $post
     */
    public static function exms_quizzes_assign_metabox_html( $post ) {

        echo exms_parent_assign_post_html( $post, 'exms-quizzes' );
    }

    /**
     * Added Attach Quizzes Column on Question post type data table
     * 
     * @param array $post_columns
     * @param string $post_type
     * @return array
     */
    public function exms_add_attach_quizzes_column( $post_columns, $post_type ) {

        if( !$this->question_page ) {
            return $post_columns;
        }

        if ( 'exms-questions' === $post_type && is_array( $post_columns ) ) {

            $new_columns = [];

            foreach ( $post_columns as $key => $value ) {
                if ( $key === 'author' ) {
                    $new_columns['attach_quizzes'] = __( 'Attach Quizzes', 'exms' );
                }

                $new_columns[ $key ] = $value;
            }

            return $new_columns;
        }

        return is_array( $post_columns ) ? $post_columns : [];
    }

    /**
     * Show attach quizzes name on attach quiz column
     * 
     * @param $column
     * @param $post_id
     */
    public function exms_display_attach_quizzes_column( $column, $post_id ) {

        if( !$this->question_page ) {
            return false;
        }

        if ( $column === 'attach_quizzes' && get_post_type( $post_id ) === 'exms-questions' ) {

            $params = [
                [
                    'field' => 'question_id',
                    'value' => $post_id,
                    'operator' => '=',
                    'type' => '%d'
                ],
                [
                    'field' => 'status',
                    'value' => 'active',
                    'operator' => '=',
                    'type' => '%s'
                ]
            ];
    
            $columns = [ 'quiz_id' ];
            $results = $this->exms_db_query( 'select', 'exms_quiz_questions', $params, $columns );
            
                
            $quiz_ids = [];
            foreach ( $results as $row ) {
                $quiz_ids[] = (int) $row->quiz_id;
            }

            $quiz_titles = [];
    
            foreach ( $quiz_ids as $quiz_id ) {

                $title = get_the_title( $quiz_id );
                if ( $title ) {

                    $quiz_titles[] = $title;
                }
            }
            echo ! empty( $quiz_titles ) ? esc_html( implode( ', ', $quiz_titles ) ) : '—';
        }
    }
    

	/**
	 * Create question option meta box content
	 */
	public static function exms_question_option_content_html( $post, $args ) {

        global $wpdb;
        
        if( !self::$instance->question_page ) {
            return false;
        }

        $table_exists = self::$instance->exms_validate_table();
        if ( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        $metabox_name = isset( $args['args'] ) ? $args['args'] : '';
        if( 'exms-questions' != $post->post_type || 'exms_question_options' != $metabox_name ) {
            return;
        }
        
        $table = $wpdb->prefix . 'exms_questions';
        $question_data = $wpdb->get_row(
            $wpdb->prepare( "SELECT points_for_question, hint_for_question, msg_for_correct_ans, msg_for_incorrect_ans, shuffle_answer, timer  FROM $table WHERE question_id = %d", $post->ID ),
            ARRAY_A
        );
        
        $ques_points      = isset( $question_data['points_for_question'] ) ? $question_data['points_for_question'] : '';
        $ques_hint        = isset( $question_data['hint_for_question'] ) ? $question_data['hint_for_question'] : '';
        $corr_ans_msg     = isset( $question_data['msg_for_correct_ans'] ) ? $question_data['msg_for_correct_ans'] : '';
        $incorr_ans_msg   = isset( $question_data['msg_for_incorrect_ans'] ) ? $question_data['msg_for_incorrect_ans'] : '';
        $shuffle_value    = isset( $question_data['shuffle_answer'] ) ? $question_data['shuffle_answer'] : 'off';
        $ques_timer       = isset( $question_data['timer'] ) ? $question_data['timer'] : '';
        
        $shuffle_ans_on   = $shuffle_value === 'on' ? 'checked' : '';
        $shuffle_ans_off  = $shuffle_value === 'off' ? 'checked' : '';
        
        $params = [];
        $params[] = [ 'field' => 'post_type', 'value' => 'exms_quizzes', 'operator' => '=', 'type'=> '%s' ];
        $params[] = [ 'field' => 'post_status', 'value' => 'publish', 'operator' => '=', 'type'=> '%s' ];
        $saved_quizzes = wp_exams()->db->exms_db_query( 'select', 'posts', $params );

		if( file_exists( EXMS_TEMPLATES_DIR . 'questions/exms-question-options-metabox.php' ) ) {

            require_once EXMS_TEMPLATES_DIR . 'questions/exms-question-options-metabox.php';
        }
		
	}

	/**
	 * Question answer meta box content
	 */
	public static function exms_question_answer_content_html( $post, $args ) {

        if ( !self::$instance->question_page ) {
            return false;
        }

        global $wpdb;

        $metabox_name = isset( $args['args'] ) ? $args['args'] : '';

        if ( 'exms-questions' !== get_post_type( $post->ID ) || 'exms_question_answers' !== $metabox_name ) {
            return;
        }

        $answer_params = [
            [
                'field'    => 'question_id',
                'value'    => $post->ID,
                'operator' => '=',
                'type'     => '%d'
            ]
        ];

        $question_columns = [ 'question_type' ];
        $get_question_type = self::$instance->exms_db_query( 'select', 'exms_questions', $answer_params, $question_columns );
        $question_type = $get_question_type[0]->question_type ?? '';
        
        $question_data = self::$instance->exms_db_query( 'select', 'exms_answer', $answer_params );

        $answers = [];
        $range_min = '';
        $range_max = '';
        $correct_answer = '';

        if ( ! empty( $question_data ) ) {
            foreach ( $question_data as $answer_row ) {
                $answers[] = [
                    'answer'         => $answer_row->answer_text,
                    'correct_answer' => $answer_row->is_correct ? 'correct' : 'wrong',
                ];
            }
        }

        if ( $question_type === 'range' ) {

            $range_min = get_post_meta( $post->ID, 'exms_range_min', true );
            $range_max = get_post_meta( $post->ID, 'exms_range_max', true );
            $correct_answer = !empty( $question_data[0]->answer_text ) ? $question_data[0]->answer_text : '';
        }

        $lock_button = in_array( $question_type, [ 'essay', 'file_upload', 'range' ], true ) ? 'disabled' : '';

        $question_types = $wpdb->get_results(
            "SELECT type_name FROM {$wpdb->prefix}exms_exam_question_types ORDER BY type_name ASC",
            ARRAY_A
        );

        $table = $wpdb->prefix . 'exms_questions';
        $question_data = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM $table WHERE question_id = %d", $post->ID ),
            ARRAY_A
        );
        $sel_ques_type    = isset( $question_data['question_type'] ) ? $question_data['question_type'] : '';

        if ( file_exists( EXMS_TEMPLATES_DIR . '/questions/exms-question-answers-metabox.php' ) ) {
            require_once EXMS_TEMPLATES_DIR . '/questions/exms-question-answers-metabox.php';
        }

	}

    public function exms_admin_metaboxes( $post_type, $post ) {

        /**
         * Question options
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms_questions_options', __( 'Options', 'exms'), [ 'EXMS_Questions', 'exms_question_option_content_html' ], 'exms-questions', 'normal', 'high', 'exms_question_options' );

        /**
         * Questions answers
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms_questions_answers', __( 'Answer Options', 'exms'), [ 'EXMS_Questions', 'exms_question_answer_content_html' ], 'exms-questions', 'normal', 'high', 'exms_question_answers' );

        /**
         * Metabox for assign quizzes to question
         */
        
        $existing_labels = Exms_Core_Functions::get_options('labels');
        $question_singular = '';
        $quiz_singular = '';
        if ( is_array( $existing_labels ) && array_key_exists( 'exms_questions', $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) ) {
            $question_singular = $existing_labels['exms_questions'];
            $quiz_singular = $existing_labels['exms_quizzes'];
        }
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-quizzes-to-question', $quiz_singular . __( ' Assign/Unassign to ' . $question_singular, 'exms'), [ 'EXMS_Questions', 'exms_quizzes_assign_metabox_html' ], 'exms-questions', 'normal', 'high', '' );
    }

    /**
     * Save assigned quizzes to question post meta
     *
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function exms_save_assigned_quizzes_to_question( $post_id, $post ) {
        global $wpdb;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( $post->post_type !== 'exms-questions' ) return;

        $table_name = $wpdb->prefix . 'exms_quiz_questions';
        $current_user_id = get_current_user_id();
        $now = current_time('timestamp');

        $existing_quizzes = $wpdb->get_results(
            $wpdb->prepare("SELECT quiz_id, status FROM $table_name WHERE question_id = %d", $post_id),
            ARRAY_A
        );

        $existing_quiz_map = [];
        foreach ( $existing_quizzes as $row ) {
            $existing_quiz_map[ $row['quiz_id'] ] = $row['status'];
        }

        $new_assigned_quizzes = [];
        if (
            isset( $_POST['exms_assign_items'] ) &&
            isset( $_POST['exms_assign_items']['parent'] ) &&
            is_array( $_POST['exms_assign_items']['parent'] )
        ) {
            $new_assigned_quizzes = array_map( 'intval', $_POST['exms_assign_items']['parent'] );
        }

        $new_unassigned_quizzes = [];
        if (
            isset( $_POST['exms_unassign_items'] ) &&
            isset( $_POST['exms_unassign_items']['parent'] ) &&
            is_array( $_POST['exms_unassign_items']['parent'] )
        ) {
            $new_unassigned_quizzes = array_map( 'intval', $_POST['exms_unassign_items']['parent'] );
        }

        foreach ( $new_unassigned_quizzes as $quiz_id ) {
            if ( isset( $existing_quiz_map[ $quiz_id ] ) && $existing_quiz_map[ $quiz_id ] === 'active' ) {
                $wpdb->update(
                    $table_name,
                    [
                        'status'      => 'inactive',
                        'updated_at'  => $now,
                        'updated_by'  => $current_user_id
                    ],
                    [ 'question_id' => $post_id, 'quiz_id' => $quiz_id ],
                    [ '%s', '%s', '%d' ],
                    [ '%d', '%d' ]
                );
            }
        }

        foreach ( $new_assigned_quizzes as $quiz_id ) {
            if ( isset( $existing_quiz_map[ $quiz_id ] ) ) {
                if ( $existing_quiz_map[ $quiz_id ] === 'inactive' ) {
                    
                    $wpdb->update(
                        $table_name,
                        [
                            'status'      => 'active',
                            'updated_at'  => $now,
                            'updated_by'  => $current_user_id
                        ],
                        [ 'question_id' => $post_id, 'quiz_id' => $quiz_id ],
                        [ '%s', '%s', '%d' ],
                        [ '%d', '%d' ]
                    );
                }
            } else {
                
                $wpdb->insert(
                    $table_name,
                    [
                        'quiz_id'     => $quiz_id,
                        'question_id' => $post_id,
                        'question_order' => 1,
                        'status'      => 'active',
                        'created_at'  => $now,
                        'updated_at'  => $now,
                        'created_by'  => $current_user_id,
                        'updated_by'  => $current_user_id
                    ],
                    [ '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d' ]
                );
            }
        }
    }    
    

    public function exms_save_metaboxes_meta_data( $post_id, $post ) {
        global $wpdb;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( $post->post_type !== 'exms-questions' ) return;
        if ( ! isset( $_POST ) || empty( $_POST ) ) return;

        $question_type = sanitize_text_field( $_POST['exms_question_type'] ?? '' );
        $answers_raw   = $_POST['exms_answers'] ?? [];
        $ans_types     = $_POST['exms_ques_ans_type'] ?? [];
        if ( ! is_array( $answers_raw ) ) {
            $answers_raw = [ $answers_raw ];
        }

        $table        = $wpdb->prefix . 'exms_questions';
        $answer_table = $wpdb->prefix . 'exms_answer';

        $question_data = [
            'question_id'           => $post_id,
            'points_for_question'   => intval( $_POST['exms_points'] ?? 0 ),
            'hint_for_question'     => sanitize_text_field( $_POST['exms_hint'] ?? '' ),
            'msg_for_correct_ans'   => sanitize_text_field( $_POST['exms_corr_ans_msg'] ?? '' ),
            'msg_for_incorrect_ans' => sanitize_text_field( $_POST['exms_incorr_ans_msg'] ?? '' ),
            'shuffle_answer'        => sanitize_text_field( $_POST['exms_shuffle'] ?? 'off' ),
            'timer'                 => sanitize_text_field( $_POST['exms_timer'] ?? 'off' ),
            'question_type'         => $question_type,
        ];

        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE question_id = %d", $post_id ) );

        if ( $exists ) {
            $wpdb->update( $table, $question_data, [ 'question_id' => $post_id ] );
        } else {
            $this->exms_db_insert( 'questions', $question_data );
        }

        $params = [
                [
                    'field' => 'question_id',
                    'value' => $post_id,
                    'operator' => '=',
                    'type' => '%d'
                ]
            ];
    
            $columns = [ 'id' ];
            $existing_answers = $this->exms_db_query( 'select', 'exms_answer', $params, $columns );

        $existing_ids = wp_list_pluck( $existing_answers, 'id' );

        foreach ( $existing_ids as $id ) {
            $delete_params = [
                [
                    'field' => 'id',
                    'value' => $id,
                    'operator' => '=',
                    'type' => '%d'
                ]
            ];

            $this->exms_db_query( 'delete', 'exms_answer', $delete_params );
        }

        if ( in_array( $question_type, [ 'single_choice', 'multiple_choice', 'sorting_choice', 'matrix_sorting' ], true ) ) {

            foreach ( $answers_raw as $index => $answer_text ) {
                $answer_text_clean = wp_kses_post( $answer_text );
                $is_correct = ( isset( $ans_types[$index] ) && $ans_types[$index] === 'correct' ) ? 1 : 0;

                $wpdb->insert( $answer_table, [
                    'question_id' => $post_id,
                    'answer_text' => $answer_text_clean,
                    'is_correct'  => $is_correct,
                    'updated_by'  => get_current_user_id()
                ]);
            }

        } elseif ( $question_type === 'range' ) {

            $range_min = sanitize_text_field( $_POST['exms_answers']['min'] ?? '' );
            $range_max = sanitize_text_field( $_POST['exms_answers']['max'] ?? '' );
            $correct   = sanitize_text_field( $_POST['exms_answers']['correct'] ?? '' );
            
            update_post_meta( $post_id, 'exms_range_min', $range_min );
            update_post_meta( $post_id, 'exms_range_max', $range_max );

            $wpdb->insert( $answer_table, [
                'question_id' => $post_id,
                'answer_text' => $correct,
                'is_correct'  => 1,
                'updated_by'  => get_current_user_id()
            ] );

        } elseif ( $question_type === 'free_choice' ) {
            foreach ( $answers_raw as $answer_text ) {

                $answer_text_clean = wp_kses_post( $answer_text );
                $answer_text_clean = preg_replace( '/\s*\|\s*/', '|', $answer_text_clean );
                $answer_text_clean = preg_split( '/\s+/', trim( $answer_text_clean ) );
                $answer_text_clean = array_filter( $answer_text_clean );
                $answer_text_clean = serialize( array_values($answer_text_clean) );

                $wpdb->insert( $answer_table, [
                    'question_id' => $post_id,
                    'answer_text' => $answer_text_clean,
                    'is_correct'  => 1,
                    'updated_by'  => get_current_user_id()
                ]);
            }
        } elseif ( $question_type === 'fill_blank' ) {

            foreach ( $answers_raw as $index => $answer_text ) {
                $answer_text_clean = wp_kses_post( $answer_text );
                $is_correct = ( isset( $ans_types[$index] ) && $ans_types[$index] === 'correct' ) ? 1 : 0;

                $wpdb->insert( $answer_table, [
                    'question_id' => $post_id,
                    'answer_text' => $answer_text_clean,
                    'is_correct'  => $is_correct,
                    'updated_by'  => get_current_user_id()
                ]);
            }
        }
    }

}

/**
 * Initialize EXMS_Questions
 */
EXMS_Questions::instance();