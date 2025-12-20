<?php
/**
 * WP Exams all plugin activate actions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Activate
 *
 * create database table when is activate
 */
class EXMS_Activate {

	private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Activate ) ) {

        	self::$instance = new EXMS_Activate;
        	self::$instance->inlcude_and_run_script();
        }
    	
    	return self::$instance;
    }

    /**
	 * Add required files
	 */
	public function inlcude_and_run_script() {

		/**
		 * Add plugin activate class
		 */
		if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/db.main.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/db.main.php';
		}

        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.uploads.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.uploads.php';
		}

        $tble_uploads = new EXMS_DB_Uploads();
        $tble_uploads->run_table_create_script();


        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.payment_transaction.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.payment_transaction.php';
		}

        $tble_trans = new EXMS_DB_Payment_transation();
        $tble_trans->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.user_progress_tracking.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.user_progress_tracking.php';
		}

        $tble_post_comp = new EXMS_DB_USER_PROGRESS_TRACKING();
        $tble_post_comp->run_table_create_script();
       
        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.post_relationship.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.post_relationship.php';
		}

        $tble_pstrel = new EXMS_DB_POST_RELATIONSHIP();
        $tble_pstrel->run_table_create_script();

        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.questions_results.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.questions_results.php';
		}

        $tble_qstres = new EXMS_DB_QUESTION_RESULTS();
        $tble_qstres->run_table_create_script();

        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.quizzes_results.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.quizzes_results.php';
		}

        $tble_qres = new EXMS_DB_QUIZ_RESULTS();
        $tble_qres->run_table_create_script();

        if( file_exists( plugin_dir_path ( __FILE__ ) . '/db/models/class.user_post_relations.php' ) ) {
			require_once plugin_dir_path ( __FILE__ ) . '/db/models/class.user_post_relations.php';
		}

        $tble_up_rel = new EXMS_DB_USER_POST_RELATIONS();
        $tble_up_rel->run_table_create_script();
        
        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.questions.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.questions.php';
		}

        $tble_questions = new EXMS_DB_Questions();
        $tble_questions->run_table_create_script();
        
        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.quiz_questions.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.quiz_questions.php';
		}

        $quiz_questions = new EXMS_DB_QUIZ_QUESTION();
        $quiz_questions->run_table_create_script();
        
        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.answer.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.answer.php';
		}

        $tble_answer = new EXMS_DB_ANSWER();
        $tble_answer->run_table_create_script();
        
        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.user_enrollments.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.user_enrollments.php';
		}

        $tble_user_enrollments = new EXMS_DB_USER_ENROLLMENTS();
        $tble_user_enrollments->run_table_create_script();
        
        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.quiz_result_settings.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.quiz_result_settings.php';
		}

        $tble_quiz_result_settings = new EXMS_DB_QUIZ_SETTINGS();
        $tble_quiz_result_settings->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.quiz.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.quiz.php';
		}

        $tble_quiz = new EXMS_DB_QUIZ();
        $tble_quiz->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.quiz_type.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.quiz_type.php';
		}

        $tble_quiz_type = new EXMS_DB_QUIZ_TYPE();
        $tble_quiz_type->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.quiz_reattempt_settings.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.quiz_reattempt_settings.php';
		}

        $tble_quiz_reattempt_settings = new EXMS_DB_QUIZ_REATTEMPT_SETTINGS();
        $tble_quiz_reattempt_settings->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.exam_user_question_attempts.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.exam_user_question_attempts.php';
		}

        $tble_exam_user_question_attempts = new EXMS_USER_QUESTION_ATTEMPTS();
        $tble_exam_user_question_attempts->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.exam_user_attempts.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.exam_user_attempts.php';
		}

        $tble_exam_user_attempts = new EXMS_USER_ATTEMPTS();
        $tble_exam_user_attempts->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.exam_question_types.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.exam_question_types.php';
		}

        $tble_exam_question_types = new EXMS_DB_QUESTION_TYPES();
        $tble_exam_question_types->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.group_post.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.group_post.php';
		}

        $tble_group_post = new EXMS_DB_GROUP_POST();
        $tble_group_post->run_table_create_script();

        if( file_exists( EXMS_INCLUDES_DIR . '/db/models/class.post_settings.php' ) ) {
			require_once EXMS_INCLUDES_DIR . '/db/models/class.post_settings.php';
		}

        $tble_post_settings = new EXMS_DB_EXAMS_STRUCTURE_TYPE();
        $tble_post_settings->run_table_create_script();
	}
}

/**
 * Initialize EXMS_Activate
 */
EXMS_Activate::instance();