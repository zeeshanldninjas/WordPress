<?php
/**
 * WP EXAMS - Quiz
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Quiz
 *
 * Base class to define all quiz related hooks
 */
class EXMS_Quiz extends EXMS_DB_Main {

	private static $instance;

	/**
     * Connect to wpdb
     */
    private static $wpdb;
	private $quiz_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Quiz ) ) {

        	self::$instance = new EXMS_Quiz;

        	global $wpdb;
            self::$wpdb = $wpdb;

			if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'exms-quizzes' ) || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' && get_post_type( $_GET['post'] ) === 'exms-quizzes' ) ) {
				self::$instance->quiz_page = true;
			}
        	self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Create hooks
	 */
	public function hooks() {
		
        add_action( 'admin_notices', [$this, 'exms_show_missing_table_notice'] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_quiz_enqueue'] );
		add_filter( 'manage_posts_columns', [ $this, 'exms_sort_post_type_table_columns' ], 10, 2 );
		add_action( 'manage_posts_custom_column', [ $this, 'exms_insert_data_to_shortcode_column' ], 10, 2 );
		add_action( 'save_post', [ $this, 'exms_save_post_users' ], 11, 3 );
		add_action( 'save_post', [ $this, 'exms_save_quiz' ], 11, 3 );
        add_action( 'save_post', [ $this, 'exms_save_assigned_questions_to_quiz' ], 20, 3 );
        add_action( 'wp_ajax_create_exms_quiz_table', [ $this , 'create_exms_quiz_table' ] );
        add_action( 'add_meta_boxes', [ $this, 'exms_admin_metaboxes' ], 10, 2 );
	}

    /**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->quiz_page ) {
            return false;
        }

        $table_exists = $this->exms_validate_table();
        if( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        if( !self::$instance->table_check ) {
            $ajax_action = 'create_exms_quiz_table';
            $table_names = $table_exists;
            require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
        }
    }

    /**
     * Quiz Functionality Files added and nonce creation
     */
    public function exms_quiz_enqueue() {
        
        if ( !$this->quiz_page ) {
            return false;
        }
        
        /**
         * Custom radio buttons
         */
        wp_enqueue_style( 'EXMS_custom_radio_buttons', EXMS_ASSETS_URL . 'css/custom_radio_buttons.css', [], EXMS_VERSION, null );

        wp_enqueue_style( 'exms-post-relations-css', EXMS_ASSETS_URL . '/css/admin/post-type-structures/exms-post-relations.css', [], EXMS_VERSION, null );
        wp_enqueue_script( 'exms-post-relations-js', EXMS_ASSETS_URL . '/js/admin/post-type-structures/exms-post-relations.js', [ 'jquery' ], EXMS_VERSION, true );
        
        /**
         * jQuery UI for admin panel
         */
        wp_enqueue_style( 'EXMS_jqueryui_style', EXMS_ASSETS_URL . 'css/jquery-ui.css', [], EXMS_VERSION, null );
        wp_enqueue_script( 'EXMS_quiz_settings_js', EXMS_ASSETS_URL . 'js/admin/quiz/wpeq-quiz-settings.js', [ 'jquery' ], false, true );

        wp_localize_script( 'EXMS_quiz_settings_js', 'EXMS_QUIZ', 
            [ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_quiz_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
                'processing'                        => __( 'processing...', 'exms' ),
                'create_table'                      => __( 'Create tables', 'exms' ),
                'error_text'                        => __( 'Error', 'exms' ),
            ] 
        );
    }

    /**
     * Create quiz tables
     */
    public function create_exms_quiz_table() {

        check_ajax_referer( 'create_quiz_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        case 'user_post_relations':
                            if ( !class_exists( 'EXMS_DB_USER_POST_RELATIONS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.user_post_relations.php';     
                            }
                            $employee = new EXMS_DB_USER_POST_RELATIONS();
                            $employee->run_table_create_script();
                            break;
                        
                        case 'user_enrollments':
                            if ( !class_exists( 'EXMS_DB_USER_ENROLLMENTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.user_enrollments.php';     
                            }
                            $user_enrollments = new EXMS_DB_USER_ENROLLMENTS();
                            $user_enrollments->run_table_create_script();
                            break;
                        
                        case 'quiz_result_settings':
                            if ( !class_exists( 'EXMS_DB_QUIZ_SETTINGS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz_result_settings.php';     
                            }
                            $quiz_result_settings = new EXMS_DB_QUIZ_SETTINGS();
                            $quiz_result_settings->run_table_create_script();
                            break;
                        
                        case 'quiz':
                            if ( !class_exists( 'EXMS_DB_QUIZ' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz.php';     
                            }
                            $quiz = new EXMS_DB_QUIZ();
                            $quiz->run_table_create_script();
                            break;
                        
                        case 'quiz_type':
                            if ( !class_exists( 'EXMS_DB_QUIZ_TYPE' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz_type.php';     
                            }
                            $quiz_type = new EXMS_DB_QUIZ_TYPE();
                            $quiz_type->run_table_create_script();
                            break;
                        
                        case 'quiz_reattempt_settings':
                            if ( !class_exists( 'EXMS_DB_QUIZ_REATTEMPT_SETTINGS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz_reattempt_settings.php';     
                            }
                            $quiz_reattempt_settings = new EXMS_DB_QUIZ_REATTEMPT_SETTINGS();
                            $quiz_reattempt_settings->run_table_create_script();
                            break;

                        case 'exam_user_question_attempts':
                            if ( !class_exists( 'EXMS_USER_QUESTION_ATTEMPTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.exam_user_question_attempts.php';     
                            }
                            $exam_user_question_attempts = new EXMS_USER_QUESTION_ATTEMPTS();
                            $exam_user_question_attempts->run_table_create_script();
                            break;

                        case 'exam_user_attempts':
                            if ( !class_exists( 'EXMS_USER_ATTEMPTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.exam_user_attempts.php';     
                            }
                            $exam_user_attempts = new EXMS_USER_ATTEMPTS();
                            $exam_user_attempts->run_table_create_script();
                            break;

                        case 'quiz_questions':
                            if ( !class_exists( 'WP_EXAMS_DB_QUESTION_QUIZ_MAPPING' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quiz_questions.php';     
                            }
                            $quiz_questions = new EXMS_DB_QUIZ_QUESTION();
                            $quiz_questions->run_table_create_script();
                            break;

                        case 'quizzes_results':
                            if ( !class_exists( 'EXMS_DB_QUIZ_RESULTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quizzes_results.php';     
                            }
                            $quizzes_results = new EXMS_DB_QUIZ_RESULTS();
                            $quizzes_results->run_table_create_script();
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
	 * Save quiz assign/un-assign user id
	 * @param $post_id, $post, $update
	 */
	public function exms_save_post_users( $post_id, $post, $update ) {
        
		if( $update != true ) {
            return false;
        }

        $post_type = get_post_type( $post_id );
        $parent_post = EXMS_PR_Fn::exms_get_parent_post_type();
        if( 'exms-quizzes' != $post_type && $parent_post != $post_type ) {
        	return false;
        }

        $assign_ids = isset( $_POST['exms_assign_items']['current'] ) ? $_POST['exms_assign_items']['current'] : [];
        $unassign_ids = isset( $_POST['exms_unassign_items']['current'] ) ? $_POST['exms_unassign_items']['current'] : [];

        $table_name = EXMS_PR_Fn::exms_user_post_table();

        if( $unassign_ids && is_array( $unassign_ids ) ) {

            self::$wpdb->query( 
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

                $data = [
                    'post_id'           => $post_id,
                    'user_id'           => $assign_id,
                    'created_timestamp' => date( 'l j F Y' ),
                    'updated_timestamp' => date( 'l j F Y' )
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

        /* End insert or update un assign meta on parent relation */
	}

    /**
     * Save quiz result display settings to custom table `quiz_settings`
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     */
    public function exms_save_quiz( $post_id, $post, $update ) {

        global $wpdb;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

        if ( $update !== true ) {
            return false;
        }

        $post_type = get_post_type( $post_id );
        if ( $post_type !== 'exms-quizzes' ) {
            return false;
        }
        
        $quiz_table = $wpdb->prefix . 'exms_quiz';
        $quiz_type_table = $wpdb->prefix . 'exms_quiz_type';
        $quiz_reattempt_settings_table = $wpdb->prefix . 'exms_quiz_reattempt_settings';
        $table_name = $wpdb->prefix . 'exms_quiz_result_settings';

        $quiz_type = isset( $_POST['exms_quiz_type'] ) ? $_POST['exms_quiz_type'] : 'free';

        $quiz_price = 0;
        $quiz_subs = '';
        $quiz_close_url = '';
        if ( $quiz_type === 'paid' ) {
            $quiz_price = isset( $_POST['exms_quiz_price'] ) ? intval($_POST['exms_quiz_price']) : 0;
        } else if ( $quiz_type === 'subscribe' ) {
            $quiz_price = isset( $_POST['exms_quiz_price'] ) ? intval($_POST['exms_quiz_price']) : 0;
            $quiz_subs = isset( $_POST['exms_quiz_sub_days'] ) ? $_POST['exms_quiz_sub_days'] : '';
        } else if ( $quiz_type === 'close' ) {
            $quiz_close_url = isset( $_POST['exms_quiz_close_url'] ) ? $_POST['exms_quiz_close_url'] : '';
        }

        $quiz_reattempts_no  = isset( $_POST['exms_reattempts_numbers'] ) ? intval($_POST['exms_reattempts_numbers']) : 0;
        $quiz_reattempts_type = isset( $_POST['exms_reattempt_type'] ) ? sanitize_text_field($_POST['exms_reattempt_type']) : '';
        $quiz_reattempts_field = isset( $_POST['exms_reattempt_type_value'] ) ? sanitize_text_field($_POST['exms_reattempt_type_value']) : '';

        $quiz_timer        = isset( $_POST['exms_timer'] ) ? $_POST['exms_timer'] : '';
        $quiz_status       = isset( $_POST['exms_quiz_timer_toggle'] ) ? $_POST['exms_quiz_timer_toggle'] : '';
        $show_answer       = isset( $_POST['exms_show_answer'] ) ? $_POST['exms_show_answer'] : 'off';
        $shuffle_questions = isset( $_POST['exms_shuffle_ques'] ) ? $_POST['exms_shuffle_ques'] : 'off';
        $quiz_reattempts   = isset( $_POST['exms_quiz_reattempts_toggle'] ) ? $_POST['exms_quiz_reattempts_toggle'] : 'no';
        $passing_percentage= isset( $_POST['exms_passing_per'] ) ? $_POST['exms_passing_per'] : '';
        $display_passing_percentage = isset( $_POST['exms_show_passing_percentage'] ) ? $_POST['exms_show_passing_percentage'] : '';
        $question_display  = isset( $_POST['exms_question_display'] ) ? $_POST['exms_question_display'] : '';
        $pass_msg          = isset( $_POST['exms_message_for_passing_quiz'] ) ? strip_tags( trim( $_POST['exms_message_for_passing_quiz'] ) ) : '';
        $fail_msg          = isset( $_POST['exms_message_for_failing_quiz'] ) ? strip_tags( trim( $_POST['exms_message_for_failing_quiz'] ) ) : '';
        $pending_msg       = isset( $_POST['exms_message_for_pending_quiz'] ) ? strip_tags( trim( $_POST['exms_message_for_pending_quiz'] ) ) : '';
        
        $point_achievement_type          = isset( $_POST['exms_points_award_type'] ) ? $_POST['exms_points_award_type'] : '';
        $achievement_point          = isset( $_POST['exms_quiz_points'] ) ? $_POST['exms_quiz_points'] : '';
        $deduct_point_type          = isset( $_POST['exms_points_deduct_type'] ) ? $_POST['exms_points_deduct_type'] : '';
        $deduct_point_on_fail          = isset( $_POST['exms_deduct_point_on_failing'] ) ? $_POST['exms_deduct_point_on_failing'] : '';
        $deduct_point_wrong_answer          = isset( $_POST['exms_deduct_points_wrong_answer'] ) ? $_POST['exms_deduct_points_wrong_answer'] : '';
        $deduct_fail_point          = isset( $_POST['exms_deduct_failing_points'] ) ? $_POST['exms_deduct_failing_points'] : '';
        $deduct_wrong_point          = isset( $_POST['exms_wrong_answer_deduct_point'] ) ? $_POST['exms_wrong_answer_deduct_point'] : '';
        $quiz_seat_limit          = isset( $_POST['exms_quiz_seat_limit'] ) ? $_POST['exms_quiz_seat_limit'] : 0;
        $quiz_video_url          = isset( $_POST['exms_quiz_video_url'] ) ? $_POST['exms_quiz_video_url'] : "";
        $achievement_point_type = '';
        if( $point_achievement_type == 'quiz' ) {
            $achievement_point_type          = isset( $_POST['exms_point_type_quiz'] ) ? $_POST['exms_point_type_quiz'] : '';
        } else {
            $achievement_point_type          = isset( $_POST['exms_point_type_question'] ) ? $_POST['exms_point_type_question'] : '';
        }

        $result_setting            = isset($_POST['exms_question_result_summary'])        ? sanitize_text_field($_POST['exms_question_result_summary'])        : 'summary_at_end';
        $question_answer_summary   = isset($_POST['exms_question_answer_summary'])        ? sanitize_text_field($_POST['exms_question_answer_summary'])        : 'no';
        $correct_incorrect_status  = isset($_POST['exms_question_correct_incorrect'])     ? sanitize_text_field($_POST['exms_question_correct_incorrect'])     : 'no';

        $quiz_params = [
            [
                'field' => 'quiz_id',
                'value' => $post_id,
                'operator' => '=',
                'type' => '%d'
            ]
        ];

        $columns = [ 'id' ];
        $existing_quiz = $this->exms_db_query( 'select', 'exms_quiz', $quiz_params, $columns );
        $existing_quiz_type = $this->exms_db_query( 'select', 'exms_quiz_type', $quiz_params, $columns );
        $existing_quiz_reattempt = $this->exms_db_query( 'select', 'exms_quiz_reattempt_settings', $quiz_params, $columns );
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE quiz_id = %d",
            $post_id
        ) );
        
        $data = [
            'quiz_id'               => $post_id,
            'quiz_timer'            => $quiz_timer,
            'quiz_status'           => $quiz_status,
            'show_answer'           => $show_answer,
            'shuffle_questions'     => $shuffle_questions,
            'passing_percentage'    => $passing_percentage,
            'display_passing_percentage' => $display_passing_percentage,
            'question_display'      => $question_display,
            'pass_quiz_message'     => $pass_msg,
            'fail_quiz_message'     => $fail_msg,
            'pending_quiz_message'  => $pending_msg,
            'point_achievement_type'=> $point_achievement_type,
            'achievement_point'     => $achievement_point,
            'deduct_point_type'     => $deduct_point_type,
            'deduct_point_on_fail'  => $deduct_point_on_fail,
            'deduct_fail_point'     => $deduct_fail_point,
            'deduct_point_wrong_answer' => $deduct_point_wrong_answer,
            'deduct_wrong_point'    => $deduct_wrong_point,
            'quiz_seat_limit'       => $quiz_seat_limit,
            'video_url'             => $quiz_video_url,
        ];
        
        if ( $existing_quiz ) {
            $wpdb->update( $quiz_table, $data, [ 'quiz_id' => $post_id ] );
        } else {
            $this->exms_db_insert( 'quiz', $data );
        }

        $quiz_type_data = [
            'quiz_id'               => $post_id,
            'quiz_type'             => $quiz_type,
            'quiz_price'            => $quiz_price,
            'subscription_days'     => $quiz_subs,
            'redirect_url'          => $quiz_close_url,
        ];
        
        if ( $existing_quiz_type ) {
            $wpdb->update( $quiz_type_table, $quiz_type_data, [ 'quiz_id' => $post_id ] );
        } else {
            
            $this->exms_db_insert( 'quiz_type', $quiz_type_data );
        }

        $quiz_reattempt_data = [
            'quiz_id'               => $post_id,
            'quiz_reattempts'       => $quiz_reattempts,
            'quiz_reattempts_no'    => $quiz_reattempts_no,
            'quiz_reattempts_type' => $quiz_reattempts_type,
            'quiz_reattempts_field' => $quiz_reattempts_field,
        ];
        
        if ( $existing_quiz_reattempt ) {

            $wpdb->update( $quiz_reattempt_settings_table, $quiz_reattempt_data, [ 'quiz_id' => $post_id ] );
        } else {
            $this->exms_db_insert( 'quiz_reattempt_settings', $quiz_reattempt_data );
        }

        $quiz_settings_data = [
            'quiz_id'                 => $post_id,
            'result'                  => $result_setting,
            'question_answer_summary' => $question_answer_summary,
            'correct_incorrect_status'=> $correct_incorrect_status,
        ];

        if ( $exists ) {
            $wpdb->update( $table_name, $quiz_settings_data,[ 'quiz_id' => $post_id ] );
        } else {
            $this->exms_db_insert( 'quiz_result_settings', $quiz_settings_data );
        }

        do_action( 'exms_quiz_settings_saved', $post_id );
    }


	/**
	 * Assign questions to the quiz
	 * 
	 * @param $post
	 */
	public static function exms_questions_assign_metabox_html( $post ) {

		echo exms_current_assign_post_html( $post, 'exms-questions' );
	}

	/**
	 * Users assign to single quiz metabox html
	 * 
	 * @param $post
	 */
	public static function exms_user_assign_metabox_html( $post ) {

		$quiz_id = isset( $post->ID ) ? $post->ID : 0;
		$parent_post_type = EXMS_PR_Fn::exms_get_parent_post_type();

		if( exms_is_quiz_in_parent_post( $parent_post_type, $quiz_id ) ) {
			return;

		} else {

			echo exms_user_assign_to_post_html( $post );
		}
	}

	/**
	 * Display shortcode on quiz edit page ( Admin panel )
	 */
	public function exms_insert_data_to_shortcode_column( $columns, $post_id ) {
		
		if ( !$this->quiz_page ) {
            return false;
        }

		$post = get_post( $post_id );

		if( 'exms-quizzes' == $post->post_type ) {

			switch( $columns ) {

				case 'shortcode':
					echo '[exms_quiz id='.$post->ID.']';
				break;
			}	
		}

		return $columns;
	}

	/**
	 * Add shortcode column to post type table 
	 */
	public function exms_sort_post_type_table_columns( $post_columns, $post_type ) {

		if ( !$this->quiz_page ) {
            return $post_columns;
        }

		if( 'exms-quizzes' == $post_type ) {

			$new_columns = [];

			foreach( $post_columns as $key => $value ) {
				
				if( $key == 'author' ) {

					$new_columns['shortcode'] = __( 'Shortcode', 'exms' );
				}

				$new_columns[$key] = $value;
			}
			return $new_columns;	
		}
		return $post_columns;
	}

	/**
	 * Quiz settings html
	 */
	public static function exms_quiz_settings( $post, $args ) {

		if ( !self::$instance->quiz_page ) {
            return false;
        }

        global $wpdb;

        $table_exists = self::$instance->exms_validate_table();
        if ( empty( $table_exists ) ) {
            self::$instance->table_check = true;
        }

        $quiz_id = $post->ID;
        $quiz_table                   = $wpdb->prefix . 'exms_quiz';
        $quiz_type_table             = $wpdb->prefix . 'exms_quiz_type';
        $quiz_reattempt_table        = $wpdb->prefix . 'exms_quiz_reattempt_settings';
        $quiz_result_settings_table  = $wpdb->prefix . 'exms_quiz_result_settings';

        $quiz_data = $wpdb->get_row(
            $wpdb->prepare("
                SELECT 
                    q.quiz_timer, 
                    q.quiz_status, 
                    q.show_answer, 
                    q.shuffle_questions,
                    q.passing_percentage, 
                    q.display_passing_percentage, 
                    q.question_display, 
                    q.pass_quiz_message, 
                    q.fail_quiz_message,
                    q.pending_quiz_message,
                    q.point_achievement_type,
                    q.achievement_point,
                    q.deduct_point_type,
                    q.deduct_point_on_fail,
                    q.deduct_fail_point,
                    q.deduct_point_wrong_answer,
                    q.deduct_wrong_point,
                    q.quiz_seat_limit,
                    q.video_url,
                    qt.quiz_type, 
                    qt.quiz_price, 
                    qt.subscription_days, 
                    qt.redirect_url,
                    qr.quiz_reattempts, 
                    qr.quiz_reattempts_no, 
                    qr.quiz_reattempts_type, 
                    qr.quiz_reattempts_field,
                    rs.result, 
                    rs.question_answer_summary, 
                    rs.correct_incorrect_status
                FROM $quiz_table q
                LEFT JOIN $quiz_type_table qt ON q.quiz_id = qt.quiz_id
                LEFT JOIN $quiz_reattempt_table qr ON q.quiz_id = qr.quiz_id
                LEFT JOIN $quiz_result_settings_table rs ON q.quiz_id = rs.quiz_id
                WHERE q.quiz_id = %d
            ", $quiz_id),
            ARRAY_A
        );

        $exms_question_result_summary    = 'summary_at_end';
        $exms_question_answer_summary    = 'no';
        $exms_question_correct_incorrect = 'no';
        
        $quiz_type           = 'free';
        $quiz_timer          = '';
        $is_quiz_timer_disabled = '';
        $show_answer         = 'off';
        $shuffle                = '';
        $quiz_reattempts     = 'no';
        $quiz_reattempts_no  = 0;
        $quiz_reattempts_type   = '';
        $quiz_reattempts_field = '';
        $quiz_price          = 0;
        $quiz_passing_per       = '';
        $display_passing_percentage = '';
        $question_display    = '';
        $pass_msg               = '';
        $fail_msg               = '';
        $pending_msg            = '';
        $point_achievement_type = '';
        $achievement_point     = '';
        $achievement_point_type   = '';
        $deduct_point_type = '';
        $deduct_point_on_fail = '';
        $deduct_fail_point = '';
        $deduct_point_wrong_answer = '';
        $deduct_wrong_point = '';
        $quiz_skip_time         = '';
        $quiz_close_url         = '';
        $quiz_seat_limit         = 0;
        $quiz_video_url         = "";

        if ( $quiz_data ) {
            $exms_question_result_summary    = $quiz_data['result'];
            $exms_question_answer_summary    = $quiz_data['question_answer_summary'];
            $exms_question_correct_incorrect = $quiz_data['correct_incorrect_status'];
        
            $quiz_type               = $quiz_data['quiz_type'];
            $quiz_skip_time          = $quiz_data['quiz_timer'];
            $is_quiz_timer_disabled  = $quiz_data['quiz_status'];
            $show_answer             = $quiz_data['show_answer'];
            $shuffle                 = $quiz_data['shuffle_questions'];
            $quiz_reattempts         = $quiz_data['quiz_reattempts'];
            $quiz_reattempts_no      = $quiz_data['quiz_reattempts_no'];
            $quiz_reattempts_type    = $quiz_data['quiz_reattempts_type'];
            $quiz_reattempts_field   = $quiz_data['quiz_reattempts_field'];
            $quiz_price              = $quiz_data['quiz_price'];
            $quiz_passing_per        = $quiz_data['passing_percentage'];
            $display_passing_percentage = $quiz_data['display_passing_percentage'];
            $question_display        = $quiz_data['question_display'];
            $pass_msg                = $quiz_data['pass_quiz_message'];
            $fail_msg                = $quiz_data['fail_quiz_message'];
            $pending_msg             = $quiz_data['pending_quiz_message']; 
            $point_achievement_type  = $quiz_data['point_achievement_type'];
            $achievement_point       = $quiz_data['achievement_point'];
            $deduct_point_type       = $quiz_data['deduct_point_type'];
            $deduct_point_on_fail    = $quiz_data['deduct_point_on_fail'];
            $deduct_fail_point       = $quiz_data['deduct_fail_point'];
            $deduct_wrong_point      = $quiz_data['deduct_wrong_point'];
            $deduct_point_wrong_answer= $quiz_data['deduct_point_wrong_answer'];
            $quiz_close_url          = $quiz_data['redirect_url'];
            $quiz_subs               = $quiz_data['subscription_days'];
            $quiz_seat_limit         = $quiz_data['quiz_seat_limit'];
            $quiz_video_url         = $quiz_data['video_url'];
        }
        
        $display_price_field = $quiz_type == 'paid' || $quiz_type == 'subscribe' ? 'exms-show' : '';
        $subscription_field = $quiz_type == 'subscribe' ? 'exms-show' : '';
        $quiz_close_field = $quiz_type == 'close' ? 'exms-show' : '';
        $stripe_settings = Exms_Core_Functions::get_options( 'payment_settings' );
        $stripe_on = isset( $stripe_settings['stripe_enable'] ) ? $stripe_settings['stripe_enable'] : 'off';
        $paypal_on = isset( $stripe_settings['paypal_enable'] ) ? $stripe_settings['paypal_enable'] : 'off';

		/**
         * Quiz metaboxes
         */
        if( file_exists( EXMS_TEMPLATES_DIR . '/quiz/exms-quiz-settings.php' ) ) {

            require_once EXMS_TEMPLATES_DIR . '/quiz/exms-quiz-settings.php';
        }
	}

	/**
     * If table not exist will pass in the array
     */
    public function exms_validate_table() {
        
        global $wpdb;
        
		$user_enrollments = $wpdb->prefix.'exms_user_enrollments';
		$quiz_result_settings = $wpdb->prefix.'exms_quiz_result_settings';
		$quiz = $wpdb->prefix.'exms_quiz';
		$quiz_type = $wpdb->prefix.'exms_quiz_type';
		$quiz_reattempt_settings = $wpdb->prefix.'exms_quiz_reattempt_settings';
		$exam_user_question_attempts = $wpdb->prefix.'exms_exam_user_question_attempts';
		$exam_user_attempts = $wpdb->prefix.'exms_exam_user_attempts';
		$quiz_questions = $wpdb->prefix.'exms_quiz_questions';
		$quizzes_results = $wpdb->prefix.'exms_quizzes_results';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $user_enrollments ) ) {
            $not_exist_tables[] = 'user_enrollments';
        }
        
        if ( !$this->exms_table_exists( $quiz_result_settings ) ) {
            $not_exist_tables[] = 'quiz_result_settings';
        }
        
        if ( !$this->exms_table_exists( $quiz ) ) {
            $not_exist_tables[] = 'quiz';
        }
        
        if ( !$this->exms_table_exists( $quiz_type ) ) {
            $not_exist_tables[] = 'quiz_type';
        }
        
        if ( !$this->exms_table_exists( $quiz_reattempt_settings ) ) {
            $not_exist_tables[] = 'quiz_reattempt_settings';
        }
        
        if ( !$this->exms_table_exists( $exam_user_question_attempts ) ) {
            $not_exist_tables[] = 'exam_user_question_attempts';
        }
        
        if ( !$this->exms_table_exists( $exam_user_attempts ) ) {
            $not_exist_tables[] = 'exam_user_attempts';
        }
        
        if ( !$this->exms_table_exists( $quiz_questions ) ) {
            $not_exist_tables[] = 'quiz_questions';
        }
        
        if ( !$this->exms_table_exists( $quizzes_results ) ) {
            $not_exist_tables[] = 'quizzes_results';
        }
        return $not_exist_tables;
    }

	/**
	 * Quiz Shorcode content
	 */
	public static function exms_quiz_shortcode( $post, $args ) {

		$post_id = $post->ID;
        $metabox_name = isset( $args['args'] ) ? $args['args'] : '';

        if( isset( $post->post_type ) && 'exms_quiz' != $post->post_type && 'exms_quiz_shortcode' != $metabox_name ) {

			return;	
        }
		
        echo '<b>[exms_quiz id='.$post_id.']</b>'; 
	}
    
    /**
     * Save assigned questions to quiz post meta
     *
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function exms_save_assigned_questions_to_quiz( $post_id, $post, $update ) {

        global $wpdb;
        if ( ! $update ) {
            return false;
        }

        if ( get_post_type( $post_id ) !== 'exms-quizzes' ) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'exms_quiz_questions';
        $current_user_id = get_current_user_id();
        $now = current_time('timestamp');

        $existing_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT question_id, status FROM $table_name WHERE quiz_id = %d",
                $post_id
            )
        );

        $existing_question_map = [];
        foreach ( $existing_rows as $row ) {
            $existing_question_map[ (int)$row->question_id ] = $row->status;
        }

        $new_assigned_questions = isset( $_POST['exms_assign_items'] ) && isset( $_POST['exms_assign_items']['current'] ) && is_array( $_POST['exms_assign_items']['current'] )
            ? array_map( 'intval', $_POST['exms_assign_items']['current'] )
            : [];

        $new_unassigned_questions = [];
        if (
            isset( $_POST['exms_unassign_items'] ) &&
            isset( $_POST['exms_unassign_items']['current'] ) &&
            is_array( $_POST['exms_unassign_items']['current'] )
        ) {
            $new_unassigned_questions = array_map( 'intval', $_POST['exms_unassign_items']['current'] );
        }

        foreach ( $new_unassigned_questions as $question_id ) {
            if ( isset( $existing_question_map[ $question_id ] ) && $existing_question_map[ $question_id ] === 'active' ) {
                $wpdb->update(
                    $table_name,
                    [
                        'status'     => 'inactive',
                        'updated_at' => $now,
                        'updated_by' => $current_user_id,
                    ],
                    [
                        'quiz_id'     => $post_id,
                        'question_id' => $question_id,
                    ],
                    [ '%s', '%s', '%d' ],
                    [ '%d', '%d' ]
                );
            }
        }

        foreach ( $new_assigned_questions as $question_id ) {
            if ( isset( $existing_question_map[ $question_id ] ) ) {
                if ( $existing_question_map[ $question_id ] === 'inactive' ) {
                    $wpdb->update(
                        $table_name,
                        [
                            'status'     => 'active',
                            'updated_at' => $now,
                            'updated_by' => $current_user_id,
                        ],
                        [
                            'quiz_id'     => $post_id,
                            'question_id' => $question_id,
                        ],
                        [ '%s', '%s', '%d' ],
                        [ '%d', '%d' ]
                    );
                }
            } else {
                $data = [
                    'quiz_id'     => $post_id,
                    'question_id' => $question_id,
                    'status'      => 'active',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                    'created_by'  => $current_user_id,
                    'updated_by'  => $current_user_id,
                ];

                if( get_post_type( $question_id ) == 'exms-questions' ) {
                    $this->exms_db_insert( 'quiz_questions', $data );
                }
            }
        }

        do_action( 'exms_assign_elements', $new_assigned_questions, true );
    }
    

    public function exms_admin_metaboxes( $post_type, $post ) {

        $existing_labels = Exms_Core_Functions::get_options('labels');
        $quiz_singular = '';
        $question_singular = '';
        if ( is_array( $existing_labels ) && array_key_exists( 'exms_quizzes', $existing_labels ) && array_key_exists( 'exms_questions', $existing_labels ) ) {
            $quiz_singular = $existing_labels['exms_quizzes'];
            $question_singular = $existing_labels['exms_questions'];
        }
        
        /**
         * Quiz settings
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms_settings_metabox', $quiz_singular . __( ' Settings', 'exms' ), [ 'EXMS_Quiz', 'exms_quiz_settings' ], 'exms-quizzes', 'normal', 'high', 'exms_create_quiz_settings' );

        /**
         * Metabox for assign users to quiz
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-users', __( 'Users Assign/Unassign to ' . $quiz_singular, 'exms' ), [ 'EXMS_Quiz', 'exms_user_assign_metabox_html' ], 'exms-quizzes', 'normal', 'high', '' );
        
        /**
         * Metabox for assign questions to quiz
         */
        Exms_Post_Types_Functions::add_exms_meta_box( 'exms-assign-questions', $question_singular . __( ' Assign/Unassign to ' . $quiz_singular, 'exms' ), [ 'EXMS_Quiz', 'exms_questions_assign_metabox_html' ], 'exms-quizzes', 'normal', 'high', '' );
    }
}


/**
 * Initialize EXMS_Quiz
 */
EXMS_Quiz::instance();