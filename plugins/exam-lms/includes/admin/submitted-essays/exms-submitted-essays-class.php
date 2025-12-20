<?php
/**
 * WP EXAMS - Submitted Essays
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Submitted_Essays
 *
 * Handles all actions related essays
 */
class EXMS_Submitted_Essays extends EXMS_DB_Main {

	private static $instance;
	private $submit_essay_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

    	if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Submitted_Essays ) ) {

    		self::$instance = new EXMS_Submitted_Essays;
			if( isset( $_GET['page'] ) && $_GET['page'] === 'exms_submitted_essays' ) {
                self::$instance->submit_essay_page = true;
            }
    		self::$instance->hooks();
    	}

    	return self::$instance;
    }

	/**
	 * Create Hooks
	 */
	private function hooks() {

		add_action( 'admin_notices', [$this, 'exms_show_missing_table_notice'] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_quiz_enqueue'] );
		add_action( 'wp_ajax_exms_delete_essay_rows', [ $this, 'exms_delete_essay_rows' ] );
		add_action( 'wp_ajax_exms_update_essay_rows', [ $this, 'exms_update_essay_rows' ] );
		add_action( 'wp_ajax_exms_approve_essay_answer', [ $this, 'exms_approved_essay_answers' ] );
		add_action( 'wp_ajax_create_exms_submit_essay_table', [ $this , 'create_exms_submit_essay_table' ] );
	}

	/**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->submit_essay_page ) {
            return false;
        }

        $table_exists = $this->csm_validate_table();
            if( empty( $table_exists ) ) {
                self::$instance->table_check = true;
            }

            if( !self::$instance->table_check ) {
                $ajax_action = 'create_exms_submit_essay_table';
                $table_names = $table_exists;
                require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
            }
    }

    /**
     * Quiz Functionality Files added and nonce creation
     */
    public function exms_quiz_enqueue() {
        
        if ( !$this->submit_essay_page ) {
            return false;
        }
        
        wp_enqueue_style( 'wpeq_submitted_essay', EXMS_ASSETS_URL . '/css/admin/submit-essay/exms_submitted_essay.css', [], EXMS_VERSION, null );
            
        wp_enqueue_script( 'wpeq_submitted_essayJS', EXMS_ASSETS_URL . '/js/admin/submit-essay/wpeq_submitted_essay.js', [ 'jquery' ], false, true );
            
        wp_localize_script( 'wpeq_submitted_essayJS', 'EXMS_SUBMIT_ESSAY', 
			[ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_submit_essay_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'cafe-sultan-management' ),
                'processing'                        => __( 'processing...', 'cafe-sultan-management' ),
                'create_table'                      => __( 'Create tables', 'cafe-sultan-management' ),
                'error_text'                        => __( 'Error', 'cafe-sultan-management' ),
            ]  
		);

        
    }

	/**
     * If table not exist will pass in the array
     */
    public function csm_validate_table() {
        
        global $wpdb;
        
		$quiz_result_table_name = $wpdb->prefix.'exms_quizzes_results';
		$uploads_table_name = $wpdb->prefix.'exms_uploads';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $quiz_result_table_name ) ) {
            $not_exist_tables[] = 'quizzes_results';
        }
        
		if ( !$this->exms_table_exists( $uploads_table_name ) ) {
            $not_exist_tables[] = 'uploads';
        }
    
        return $not_exist_tables;
    }

    /**
     * Create quiz tables
     */
    public function create_exms_submit_essay_table() {

        check_ajax_referer( 'create_submit_essay_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        case 'quizzes_results':
                            if ( !class_exists( 'EXMS_DB_QUIZ_RESULTS' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.quizzes_results.php';     
                            }
                            $quiz_results = new EXMS_DB_QUIZ_RESULTS();
                            $quiz_results->run_table_create_script();
                            break;

                        case 'uploads':
                            if ( !class_exists( 'EXMS_DB_Uploads' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.uploads.php';     
                            }
                            $uploads = new EXMS_DB_Uploads();
                            $uploads->run_table_create_script();
                            break;

                        default:
                            wp_send_json_error( [ 'message' => sprintf(__( 'Unknown table: %s', 'cafe-sultan-management' ), esc_html( $table_name ) ) ] );
                            return;
                    }
                }
                
                wp_send_json_success(__( 'Tables created successfully.', 'cafe-sultan-management' ) );
            } else {
                wp_send_json_error( [ 'message' => __( 'Invalid table names format.', 'cafe-sultan-management' ) ] );
            }
        } else {
            wp_send_json_error( [ 'message' => __( 'No table names provided.', 'cafe-sultan-management') ] );
        }
    
        wp_die();
    }

	/**
	 * Approved essay answers : Ajax
	 */
	public function exms_approved_essay_answers() {

		global $wpdb;

		$essay_id = isset( $_POST['exms_essay_id'] ) ? (int) $_POST['exms_essay_id'] : 0;
		if( ! $essay_id ) {
			echo __( 'Essay id not found', 'exms' );

			wp_die();
		}

		$quiz_id = isset( $_POST['exms_quiz_id'] ) ? (int) $_POST['exms_quiz_id'] : 0;
		if( ! $quiz_id ) {
			echo __( 'Quiz id not found', 'exms' );

			wp_die();
		}

		$user_id = isset( $_POST['exms_user_id'] ) ? (int) $_POST['exms_user_id'] : 0;
		if( ! $user_id ) {
			echo __( 'User id not found', 'exms' );

			wp_die();
		}

		$points = isset( $_POST['exms_points'] ) ? (int) $_POST['exms_points'] : 0;
		
		$params = [];
		$params[] = [ 'field' => 'user_id', 'value' => $user_id, 'operator' => '=', 'type'=> '%d'];
		$params[] = [ 'field' => 'quiz_id', 'value' => $quiz_id, 'operator' => '=', 'type'=> '%d'];
		$results = isset( wp_exams()->db ) && wp_exams()->db ? wp_exams()->db->exms_db_query( 'select', 'quizzes_results', $params ) : [];

		if( $results ) {

			foreach( $results as $result ) {

				$essay_ids = unserialize( $result->essay_ids );
				if( ! in_array( $essay_id, $essay_ids ) ) {
					continue;
				}
				
				$total_points = $result->total_points;
				$total_ques = $result->total_questions;
				$correct_ques = $result->correct_questions;

				/**
				 * Calculate obtained points
				 */
				$new_obt_points = ( $total_points / $total_ques );
				$obtained_points = $result->obtained_points + $new_obt_points;

				/**
				 * Calculate percentage
				 */
				$new_percentage = ( 100 / $total_ques );
				$percentage = $result->percentage + $new_percentage;

				/**
				 * Get quiz passing percentage from backend
				 */
				$quiz_opts = exms_get_post_options( $result->quiz_id );
				$passing_percentage = isset( $quiz_opts['exms_passing_per'] ) ? ( float ) $quiz_opts['exms_passing_per'] : 50;
				$is_quiz_passed = $percentage >= $passing_percentage ? 'true' : 'false';

				$correct_questions = $result->correct_questions + 1;
				
				/**
				 * Update exms_quizzes_results table row.
				 * according to quiz new update results.
				 */
				$table = $wpdb->prefix . 'exms_quizzes_results';
				$wpdb->query(
		          	$wpdb->prepare(
		                	"Update $table set obtained_points = '%d', correct_questions = '%d', percentage = '%d', passed = '%s' where essay_ids = %s",$obtained_points,$correct_questions,$percentage,$is_quiz_passed,$result->essay_ids
		            	)
		        	);

				/**
				 * Save essays approvements from database.
				 * When essay answers are approved.
				 */
				$approved = get_user_meta( $result->user_id, 'exms_essay_approved', true );
				if( empty( $approved ) ) {
					$approved = [];
				}
				$approved['approved_' . $essay_id ] = __( 'Approved', 'exms' );
		        	update_user_meta( $result->user_id, 'exms_essay_approved', $approved );

		        	/**
		        	 * Award points to user quiz/questions
		        	 * According to backend option
		        	 */
		        	$award_type = isset( $quiz_opts['exms_points_award_type'] ) ? $quiz_opts['exms_points_award_type'] : 'quiz';
		        	if( $award_type && 'question' == $award_type ) {

		        		exms_award_points_to_user( $result->user_id, $points, $result->points_type );

		        	} elseif( 'true' == $is_quiz_passed ) {
						
		        		$has_awarded = exms_user_has_awarded_quiz_points( $result->user_id, $result->quiz_id, $total_points );
		        		if( false == $has_awarded ) {

		        			exms_award_points_to_user( $result->user_id, $total_points, $result->points_type );
		        			$awarded_key = 'exms_has_awarded_' . $total_points . '_' . $result->quiz_id;
						update_user_meta( $user_id, $awarded_key, 'already_awarded' );
		        		}
				}
			}
		}

		wp_die();
	}

	/**
	 * Load submitted essay HTML
	 */
	public static function exms_submitted_essays() {

		if( file_exists( EXMS_INCLUDES_DIR . '/admin/submitted-essays/exms-submitted-essays.php' ) ) {

			require_once EXMS_INCLUDES_DIR . '/admin/submitted-essays/exms-submitted-essays.php';
		}
	}

	/**
	 * Update rows from essay's table
	 */
	public function exms_update_essay_rows() {

		global $wpdb;

		$row_id = isset( $_POST['exms_row_id'] ) ? (int) $_POST['exms_row_id'] : 0;
		if( ! $row_id ) {
			echo __( 'Row ID not found.' );

			wp_die();
		}

		$content = isset( $_POST['exms_content'] ) ? sanitize_textarea_field( $_POST['exms_content'] ) : '';
		if( ! $content ) {
			echo __( 'Content ID not found.' );

			wp_die();
		}

		$table = $wpdb->prefix . 'exms_uploads';

		/**
		 * Update content for upload table
		 */
		$wpdb->query(
          	$wpdb->prepare(
                	"Update $table set content = '%s' where id = %d",$content,$row_id
            	)
        	);

		wp_die();		
	}

	/**
	 * Remove rows from essay's table
	 */
	public function exms_delete_essay_rows() {

		$single_id = isset( $_POST['row_id'] ) ? (int) $_POST['row_id'] : 0;
		$multiple_ids = isset( $_POST['exms_multiple_ids'] ) ? $_POST['exms_multiple_ids'] : [];

		if( $single_id ) {

			$essay_id = isset( $_POST['essay_id'] ) ? (int) $_POST['essay_id'] : 0;
			if( ! $essay_id ) {
				echo __( 'Essay ID not found', 'exms' );

				wp_die();
			}

			$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
			if( ! $user_id ) {
				echo __( 'User ID not found', 'exms' );

				wp_die();
			}

			$approved = get_user_meta( $user_id, 'exms_essay_approved', true );
			if( empty( $approved ) ) {
				$approved = [];
			}

			/**
			 * Remove exms_essay_approved array index
			 * When essay rows is delete
			 */
			$single_approved = 'approved_' . $essay_id;
			if( isset( $approved[$single_approved] ) ) {

				unset( $approved[$single_approved] );
				update_user_meta( $user_id, 'exms_essay_approved', $approved );
			}

			if( isset( wp_exams()->db ) && wp_exams()->db ) {
				$params = [];
        		$params[] = [ 'field' => 'id', 'value' => $single_id, 'operator' => '=', 'type'=> '%d'];
				wp_exams()->dbuploads->exms_db_query( 'delete', 'uploads', $params );
			}	

		} elseif( $multiple_ids ) {

			$user_id = isset( $_POST['exms_user_id'] ) ? (int) $_POST['exms_user_id'] : 0;
			if( ! $user_id ) {
				echo __( 'User ID not found', 'exms' );

				wp_die();
			}

			$essay_ids = isset( $_POST['exms_essay_ids'] ) ? $_POST['exms_essay_ids'] : [];
			if( empty( $essay_ids ) ) {
				echo __( 'Essay IDs not found', 'exms' );

				wp_die();
			}

			$approved = get_user_meta( $user_id, 'exms_essay_approved', true );
			if( empty( $approved ) ) {
				$approved = [];
			}

			/**
			 * Remove selected index form exms_essay_approved array
			 * When selected row is deleted
			 */
			if( $essay_ids ) {
				foreach( $essay_ids as $essay_id ) {

					$single_approved = 'approved_' . $essay_id;
					if( isset( $approved[$single_approved] ) ) {

						unset( $approved[$single_approved] );
						update_user_meta( $user_id, 'exms_essay_approved', $approved );
					}
				}
			}

			foreach( $multiple_ids as $row_id ) {

				if( isset( wp_exams()->db ) && wp_exams()->db ) {
					$params = [];
					$params[] = [ 'field' => 'id', 'value' => $row_id, 'operator' => '=', 'type'=> '%d'];
					
					wp_exams()->db->exms_db_query( 'delete', 'uploads', $params );
				}
			}
		}
		wp_die();
	}
}

/**
 * Initialize/Load WP_EXAMS_Quiz
 */
EXMS_Submitted_Essays::instance();