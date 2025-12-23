<?php
/**
 * Template Hooks
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * class EXMS_Template
 *
 * Base class related to all template hooks
 */
class EXMS_Template {

	private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Template ) ) {

        	self::$instance = new EXMS_Template;
        	self::$instance->hooks();
        }

        return self::$instance;
    }

	/**
	 * Define Hooks
	 */
	public function hooks() {

		add_action( 'init', [ $this, 'exms_verify_payment' ] );
		add_filter( 'exms_template_include', [ $this, 'exms_template_include' ], 10 );
		add_action( 'wp_ajax_exms_submit_quiz_answer', [ $this, 'exms_submit_quiz_answer' ] );
		add_action( 'wp_ajax_nopriv_exms_submit_quiz_answer', [ $this, 'exms_submit_quiz_answer' ] );
		add_action( 'wp_ajax_exms_quiz_completed', [ $this, 'exms_quiz_complete' ] );
		add_action( 'init', [ $this, 'exms_create_badge_pdf' ] );
		add_action( 'the_content', [ $this, 'exms_display_question_on_quiz_page' ] );
		add_filter( 'the_content', [ $this, 'exms_display_dashboard_page' ] );
		add_action( 'wp', [ $this, 'exms_redirect_user_to_close_url' ], 9 );
	}

	/**
	 * Redirect user to close URL if post price type is close
	 */
	public function exms_redirect_user_to_close_url() {

		$post_id = get_the_ID();
		$post_type = get_post_type( $post_id );

		if( 'exms_quizzes' != $post_type && EXMS_PR_Fn::exms_get_parent_post_type() != $post_type ) {
			return false;
		}

		$post_name = '';
		if( 'exms_quizzes' == $post_type ) {
			$post_name = 'quiz';

		} elseif( EXMS_PR_Fn::exms_get_parent_post_type() == $post_type ) {

			$post_name = str_replace( 'exms-', '', $post_type );
		}

		$post_options = exms_get_post_options( $post_id );
		$price_type = isset( $post_options['exms_'.$post_name.'_type'] ) ? $post_options['exms_'.$post_name.'_type'] : '';
		$close_url = isset( $post_options['exms_'.$post_name.'_close_url'] ) ? $post_options['exms_'.$post_name.'_close_url'] : '';

		if( 'close' != $price_type ) {
			return false;
		}

		wp_redirect( $close_url );
		exit;
	}

	/**
	 * Display dashboard page
	 * 
	 * @param $content
	 */
	public function exms_display_dashboard_page( $content ) {

		/**
		 * return content if user is not logged in
		 */
		if( ! is_user_logged_in() ) {
			return $content;
		}

		$post_id = get_the_ID();
		$user_id = get_current_user_id();
		$post_type = get_post_type();
		$user_role = exms_get_user_role_by_id( $user_id );

		if( 'page' != $post_type ) {
			return $content;
		}

		$dashboard_page = exms_get_option_settings( 'dashboard_page' );
	
		if( ! empty( $dashboard_page ) && ! empty( $user_role ) && is_array( $user_role ) && $post_id == $dashboard_page ) {

			if( in_array( 'exms_instructor', $user_role ) || in_array( 'administrator', $user_role ) ) {

				$content = do_shortcode( '[exms_instructor]' );
			}

			if( in_array( 'exms_student', $user_role ) ) {
				
				$content = do_shortcode( '[exms_user_dashboard]' );
			}
		}
		return $content;
	}

	/**
     * Create PDF for course
     */
    public function exms_create_badge_pdf( $course_data ) {

    	if( ! isset( $_GET['exms_badge_pdf'] ) || empty( $_GET['exms_badge_pdf'] ) || ! file_exists( EXMS_INCLUDES_DIR . '/lib/PDF/tcpdf.php' ) ) {

    		return;
    	}

        ob_start();

        require_once EXMS_INCLUDES_DIR . '/lib/PDF/tcpdf.php';

        $badge = get_post( $_GET['exms_badge_pdf'] );
        $opts = exms_get_post_options( $badge->ID );
        $page_type = isset( $opts['exms_pdf_page_type'] ) ? $opts['exms_pdf_page_type'] : 'multiple';
        $cover_image = get_the_post_thumbnail( $badge, 'post-thumbnail', '' ) ? get_the_post_thumbnail( $badge, 'post-thumbnail', '' ) : '';
        $pdf_content = $cover_image . '<br>' . $badge->post_content;
        $page_height = strlen( $pdf_content ) / 10;
        $pdf_page_format = 'multiple' == $page_type ? PDF_PAGE_FORMAT : PDF_PAGE_FORMAT;
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, $pdf_page_format, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor( 'WP EXAMS' );
        $pdf->SetTitle( $badge->post_title );
        $pdf->SetSubject( 'WP EXAMS BADGE' );
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // add a page
        $pdf->AddPage();

        // output the HTML content
        $pdf->writeHTML( $pdf_content, true, false, true, false, '' );
        // reset pointer to the last page
        $pdf->lastPage();
        ob_clean();
        //Close and output PDF document
        $pdf->Output( $badge->post_title.'.pdf', 'I');
        die();
    }

	/**
	 * Display question on quiz page frontend
	 * 
	 * @param String 	$content 	Post Content
	 */
	public function exms_display_question_on_quiz_page( $content ) {

		$post_id = get_the_ID();
		$post_type = $post_id ? get_post_type( $post_id ) : 0;
		$user_id = get_current_user_id();
		/**
		 * return error message if post is "exms_certificates"
		 */
		$exms_certificate_awarded_user = isset( $_GET['wpd-details'] ) ? $_GET['wpd-details'] : '';
		$exms_certificate_awarded_user = substr( $exms_certificate_awarded_user, 0, -3 );
		$exms_certificate_awarded_user = substr( $exms_certificate_awarded_user, 3 );

		if( 'exms_certificates' == $post_type && $user_id != $exms_certificate_awarded_user ) {

			return "<div>You don't allow to access thios page</div>";
		}

		if( 'exms_quizzes' != $post_type ) {
			return $content;
		}

		return $content . do_shortcode( '[exms_quiz id='.$post_id.']' );
	}

	/**
	 * Save user's completed quiz progress
	 */
	public function exms_quiz_complete() {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL);

		if( isset( $_POST['data'] ) && isset( $_POST['quiz_id'] ) ) {

			$email_settings = Exms_Core_Functions::get_options( 'settings' );

			$user_id = get_current_user_id();
			$user_info = get_userdata( $user_id );
			$quiz_id = $_POST['quiz_id'];
			$quiz_opts = exms_get_post_options( $quiz_id );

			$award_points = isset( $quiz_opts['exms_quiz_points'] ) ? $quiz_opts['exms_quiz_points'] : 0;
			$award_type = isset( $quiz_opts['exms_points_award_type'] ) ? $quiz_opts['exms_points_award_type'] : 'quiz';
			$points_type = isset( $quiz_opts['exms_point_type_quiz'] ) ? $quiz_opts['exms_point_type_quiz'] : '';
			$passing_percentage = isset( $quiz_opts['exms_passing_per'] ) ? ( float ) $quiz_opts['exms_passing_per'] : 50;
			$deduct_type = isset( $quiz_opts['exms_deduct_point_on_failing'] ) ? $quiz_opts['exms_deduct_point_on_failing'] : '';
			$deduct_points_type = isset( $quiz_opts['exms_point_type_deduct'] ) ? $quiz_opts['exms_point_type_deduct'] : '';
			$deduct_points = isset( $quiz_opts['exms_deduct_failing_points'] ) ? $quiz_opts['exms_deduct_failing_points'] : 0;
			$award_badges = isset( $quiz_opts['exms_attached_badges'] ) ? explode( ',', $quiz_opts['exms_attached_badges'] ) : [];
			$pass_msg = isset( $quiz_opts['exms_message_for_passing_quiz'] ) ? $quiz_opts['exms_message_for_passing_quiz'] : '';
			$fail_msg = isset( $quiz_opts['exms_message_for_failing_quiz'] ) ? $quiz_opts['exms_message_for_failing_quiz'] : '';
			$res_data = [];
			$resp_data = [];

			$essay_ids = [];

			foreach( $_POST['data'] as $index => $data ) {
				$total_answers = isset( $data['total_answers'] ) ? intval( $data['total_answers'] ) : 0;
				$correct_answers = isset( $data['correct_answers'] ) ? intval( $data['correct_answers'] ) : 0;
				$total_points = isset( $data['total_points'] ) ? intval( $data['total_points'] ) : 0;
				$obtained_points = isset( $data['obtained_points'] ) ? intval( $data['obtained_points'] ) : 0;
				$question_type = isset( $data['question_type'] ) ? $data['question_type'] : 'file_upload';

				$res_data['total_answers'][] = $total_answers;
				$res_data['correct_answers'][] = $correct_answers;
				$res_data['total_points'][] = $total_points;
				$res_data['obtained_points'][] = $obtained_points;
				unset( $data['response'] );
				$data['max_points'] = $total_points;
				$data['user_id'] = $user_id;
				$data['points_type'] = $points_type;

				$resp_data['question_type'][] = $question_type;

				if( 'quiz' == $award_type ) {

					$data['total_points'] = 0;
					$data['obtained_points'] = 0;
				}

				if( 'essay' == $question_type ) {

					$essay_ids[] = $data['unique_id'];
				}	

				$question_id = isset( $data['question_id'] ) ? intval( $data['question_id'] ) : 0;
				
				$passed = isset( $data['passed'] ) ? $data['passed'] : '';
				$point_type = isset( $data['points_type'] ) ? intval( $data['points_type'] ) : 0;
				$max_points = isset( $data['max_points'] ) ? intval( $data['max_points'] ) : 0;				

				/**
				 * Save question results
				 */
				wp_exams()->dbqstres->exms_db_insert( 'questions_results', array(
					'user_id' 			=> $user_id,
					'question_id'		=> $question_id,
					'quiz_id'			=> $quiz_id,
					'max_points'		=> $max_points,
					'obtained_points'	=> $obtained_points,
					'points_type'		=> $point_type,
					'total_answers'		=> $total_answers,
					'correct_answers'	=> $correct_answers,
					'passed'			=> $passed,
					'result_date'		=> date( 'Y-m-d h:i' )
				) );
			}
			
			$resp_data['total_answers'] = array_sum( $res_data['total_answers'] );
			$resp_data['correct_answers'] = array_sum( $res_data['correct_answers'] );
			$resp_data['total_points'] = array_sum( $res_data['total_points'] );
			$resp_data['obtained_points'] = array_sum( $res_data['obtained_points'] );

			if( 'quiz' == $award_type ) {

				$resp_data['total_points'] = $award_points;
				$resp_data['obtained_points'] = ( $award_points / $resp_data['total_answers'] ) * $resp_data['correct_answers'];
			}

			$gained_percentage = isset( $resp_data['total_answers'] ) && isset( $resp_data['correct_answers'] ) ? number_format( ( $resp_data['correct_answers'] / $resp_data['total_answers'] ) * 100, 2 ) : 0;
			$resp_data['percentage'] = $gained_percentage && ! is_nan( $gained_percentage ) && 'nan' != $gained_percentage ? $gained_percentage : 0;
			$resp_data['html'] = __( 'You\'ve gained : '.$resp_data['percentage'].'%' );

			$is_quiz_passed = $gained_percentage >= $passing_percentage ? 'true' : 'false';
			
			if( 'false' == $is_quiz_passed ) {

				if( ! empty( $deduct_points ) && 0 != $deduct_points ) {

					$resp_data['obtained_points'] = - ( $deduct_points );
				}
			}

			$parent_posts = isset( $_POST['parent_posts'] ) ? $_POST['parent_posts'] : 0;

			/**
			 * Save quiz result
			 */
			echo exms_quiz_mark_complete( $user_id, 
				$quiz_id, 
				$is_quiz_passed,
				$parent_posts, 
				$resp_data['total_points'], 
				$resp_data['obtained_points'], 
				$points_type, 
				$resp_data['total_answers'], 
				$resp_data['correct_answers'],
				$gained_percentage, 
				$essay_ids 
			);

			/**
			 * Award/Deduct user points if user passed or fail
			 */
			if( 'true' == $is_quiz_passed ) {

				if( $quiz_opts && is_array( $quiz_opts ) ) {

					$selected_certificate_ids = EXMS_certificates::exms_get_specific_index_array( $quiz_opts, 'exms_quiz-certificates-' );

					if( $selected_certificate_ids && is_array( $selected_certificate_ids ) ) {
						ob_start();
						?>
						<div>
							<div class="certificate-heading"><?php echo __( 'Certificates', 'exms' ); ?></div>
						<?php
						foreach( $selected_certificate_ids as $certificate_id ) {
							$before_rand = rand( 200,300 );
							$after_rand = rand( 100,200 );
							$url = get_permalink( $certificate_id ).'?wpd-details='.$before_rand.$user_id.$after_rand;
							?>
							<div class="exms-certificate-links">
								<a href="<?php echo $url; ?>"><?php echo get_the_title( $certificate_id ); ?></a>
							</div>
							<?php
						}
						?>
						</div>
						<?php
						$cert_content = ob_get_contents();
						ob_get_clean();
						$resp_data['cert_html'] = $cert_content;
					}
				}
				$completed_quizzes = function_exists( 'exms_get_user_completed_quizzes' ) ? exms_get_user_completed_quizzes( $user_id ) : [];
				$completed_quizzes[] = $quiz_id;
				update_user_meta( $user_id, 'exms_user_completed_quiz_'.$quiz_id, time() );
				update_user_meta( $user_id, 'exms_user_completed_quizzes', $completed_quizzes );
				exms_award_badges_to_user( $user_id, $award_badges );
				$resp_data['quiz_message'] = $pass_msg;
				$resp_data['badges_html'] = exms_get_badges_html( $award_badges, true );
				exms_award_points_to_user( $user_id, $award_points, $points_type );

				/**
				 * Update key from database if points are awarded
				 */
				$awarded_key = 'exms_has_awarded_' . $award_points . '_' . $quiz_id;
				update_user_meta( $user_id, $awarded_key, 'already_awarded' );

				/**
				 * wpe email subject and content
				 */
				$user_email_subject = isset( $email_settings['exms_passing_subject'] ) ? exms_replace_message_with_tags( $user_id, $quiz_id, $email_settings['exms_passing_subject'] ) : '';

				$user_email_msg = isset( $email_settings['exms_passing_content'] ) ? exms_replace_message_with_tags( $user_id, $quiz_id, $email_settings['exms_passing_content'] ) : '';

				/**
				 * Email is sending to user ( if user complete the quiz )
				 */
				if( isset( $email_settings['exms_passing_option'] ) && 'yes' == $email_settings['exms_passing_option'] ) {

					EXMS_Emails::exms_send_email( $user_info->data->user_email, $user_email_subject, $user_email_msg );
				}

			} else {

				/**
				 * wpe email subject and content
				 */
				$user_email_subject = isset( $email_settings['exms_failing_subject'] ) ? exms_replace_message_with_tags( $user_id, $quiz_id, $email_settings['exms_failing_subject'] ) : '';
				$user_email_msg = isset( $email_settings['exms_failing_content'] ) ? exms_replace_message_with_tags( $user_id, $quiz_id, $email_settings['exms_failing_content'] ) : '';

				/**
				 * Email is sending to user ( if user doesn't complete the quiz )
				 */
				if( isset( $email_settings['exms_failing_option'] ) && 'yes' == $email_settings['exms_failing_option'] ) {

					EXMS_Emails::exms_send_email( $user_info->data->user_email, $user_email_subject, $user_email_msg );
				}
			
				if( $deduct_type && 'on' == $deduct_type ) {

					exms_deduct_points_to_user( $user_id, $deduct_points, $deduct_points_type );
				}

				/**
				 * Delete point already award metakey if quiz fail 
				 */
				$awarded_key = 'exms_has_awarded_' . $award_points . '_' . $quiz_id;
				delete_user_meta( $user_id, $awarded_key );

				$resp_data['quiz_message'] = $fail_msg;
			}

			/**
			 * Update user meta for quiz attempt
			 */
			$reattempts = (int) get_user_meta( $user_id, 'exms_quiz_attempts_'.$quiz_id, true );
			$reattempts++;
			update_user_meta( $user_id, 'exms_quiz_attempts_'.$quiz_id, $reattempts );
			exms_update_user_next_attempt_time( $user_id, $quiz_id );

			echo json_encode( $resp_data );
		}
		wp_die();
	}

	/**
	 * Submit quiz answer
	 */
	public function exms_submit_quiz_answer() {

		if( ! isset( $_POST['quiz_id'] ) || ! isset( $_POST['question_id'] ) ) {
			wp_die();
		}

		$user_id = get_current_user_id();
		$quiz_opts = exms_get_post_options( $_POST['quiz_id'] );
		$show_answer = isset( $quiz_opts['exms_show_answer'] ) ? $quiz_opts['exms_show_answer'] : '';
		$deduct_type = isset( $quiz_opts['exms_deduct_points_wrong_answer'] ) ? $quiz_opts['exms_deduct_points_wrong_answer'] : '';
		$deduct_points_type = isset( $quiz_opts['exms_point_type_wrong_answer'] ) ? $quiz_opts['exms_point_type_wrong_answer'] : '';
		$deduct_points = isset( $quiz_opts['exms_wrong_answer_deduct_point'] ) ? $quiz_opts['exms_wrong_answer_deduct_point'] : 0;

		/**
		 * Check user submitted answer
		 */
		if( isset( $_POST['answers'] ) ) {

			$ques_id = isset( $_POST['question_id'] ) ? $_POST['question_id'] : 0;

			$sub_answers = isset( $_POST['answers'] ) ? $_POST['answers'] : '';
			$check_ans = function_exists( 'exms_check_submitted_answer' ) ? exms_check_submitted_answer( $ques_id, $sub_answers ) : false;

			$check_ans['quiz_id'] = $_POST['quiz_id'];

			$q_opts = exms_get_question_options( $ques_id );
			$q_type = isset( $q_opts['exms_question_type'] ) ? $q_opts['exms_question_type'] : '';
			$q_points = isset( $q_opts['exms_points'] ) ? $q_opts['exms_points'] : 0;

			/**
			 * Update question data at exms_questions_results table
			 */

			$total_points = empty( $q_opts['exms_ques_ans_points'][0] ) ? ( int ) $q_opts['exms_points'] : ( int ) $q_opts['exms_ques_ans_points'][0];

			$ob_points = true == $check_ans['passed'] ? $ob_points = $total_points : 0;

			$is_passed = true == $check_ans['passed'] ? 1 : 0;

			if( 1 == $is_passed ) {
				exms_award_points_to_user( $user_id, $ob_points, 166 );
			}

			if( 'single_choice' == $check_ans['question_type'] 
				|| 'multiple_choice' == $check_ans['question_type']
				|| 'sorting_choice' == $check_ans['question_type'] ) {

				wp_exams()->dbqstres->exms_db_insert( 'questions_results', array(
					'user_id'				=> $user_id,
					'question_id'			=> $ques_id,
					'quiz_id'				=> $_POST['quiz_id'],
					'max_points'			=> $total_points,
					'passed'				=> $is_passed,
					'obtained_points'		=> $ob_points
				) );
			}

			if( 'essay' == $check_ans['question_type'] ) {

				$essay_id = rand( 10, 1000000 );

				wp_exams()->dbuploads->exms_db_insert( 'uploads', array(
					'user_id'			=> get_current_user_id(),
					'question_id'		=> $ques_id,
					'quiz_id'			=> $_POST['quiz_id'],
					'content'			=> $sub_answers[0],
					'attachment'		=> '',
					'points'			=> $q_points,
					'essay_ids'			=> $essay_id,
					'upload_type'		=> $q_type,
				) );

				$check_ans['unique_id'] = $essay_id;
			}

			if( 'on' == $show_answer ) {

				$check_ans['show_answer_type'] = $show_answer;
			}

			if( $deduct_type && 'on' == $deduct_type ) {

				if( false == $check_ans['passed'] ) {

					exms_deduct_points_to_user( get_current_user_id(), $deduct_points, $deduct_points_type );
				}
			}

			echo json_encode( $check_ans );
		}

		/**
		 * File upload to WP Exams upload
		 */
		if( isset( $_FILES['exms_file_upload'] ) ) {

			$q_opts = exms_get_question_options( $_POST['question_id'] );
			$q_type = isset( $q_opts['exms_question_type'] ) ? $q_opts['exms_question_type'] : '';
			$q_points = isset( $q_opts['exms_points'] ) ? $q_opts['exms_points'] : 0;
		 	$upload_dir = wp_upload_dir();
	 
	        if ( ! empty( $upload_dir['basedir'] ) ) {

	            $dirname = $upload_dir['basedir'].'/wp-exams/';

	        	wp_mkdir_p( $dirname );
	 	
	 			$exp_file = explode( '.', $_FILES['exms_file_upload']['name'] );
	 			$file = isset( $exp_file[0] ) ? $exp_file[0] : '';
	 			$ext = isset( $exp_file[1] ) ? '.'.$exp_file[1] : '';
	            $file_name = $file . '_' . rand( 0, 10000000 ) . $ext;
	            $uploaded_file = move_uploaded_file( $_FILES['exms_file_upload']['tmp_name'], $dirname . $file_name );

	            /**
	             * Save uploaded file details to database
	             */
	            if( $uploaded_file ) {

	            	$content = isset( $_POST['answer'] ) ? $_POST['answer'] : '';
	            	wp_exams()->dbuploads->exms_db_insert( 'uploads', array(
						'user_id'			=> get_current_user_id(),
						'question_id'		=> $_POST['question_id'],
						'quiz_id'			=> $_POST['quiz_id'],
						'content'			=> $content,
						'attachment'		=> EXMS_UPLOADS . $file_name,
						'points'			=> $q_points,
						'upload_type'		=> $q_type,
					) );	
	            }
	        }

			echo json_encode( [ 'response' => 'Uploaded Successfully.', 'file_dir' => $dirname . $file_name, 'file_path' => EXMS_UPLOADS . $file_name ] );
		}

		wp_die();
	}

	/**
	 * Filter to override any template
	 *
	 * @param String 	$template 	Path of template file
	 */
	public function exms_template_include( $template ) {

		if( file_exists( $template ) ) {

			return $template;
		}
		return false;
	}

	/**
	 * Unlock quiz after verify payment
	 */
	public function exms_verify_payment() {    	

		if( ! is_user_logged_in() ) {
			return;
		}

		/**
		 * Verify paypal
		 */
		if( isset( $_GET['tx'] ) && ! empty( $_GET['tx'] ) || isset( $_POST['action'] ) && $_POST['action'] == 'exms_payment_complete' ) {
			
			$paypal_settings = exms_get_paypal_settings() ? exms_get_paypal_settings() : null;
			$access_token = isset( $paypal_settings->client_id ) && isset( $paypal_settings->client_secret ) ? exms_create_new_access_token( $paypal_settings->client_id, $paypal_settings->client_secret ): '';
			$quiz_id = isset( $_GET['item_number'] ) && ! empty( $_GET['item_number'] ) ? $_GET['item_number']: '';
			$paypal_checking_url = isset( $paypal_settings->transaction_mode ) && $paypal_settings->transaction_mode == 'live' ? 'https://api.paypal.com/v1/payments/capture/' . $_GET['tx'] : 'https://api.sandbox.paypal.com/v1/payments/capture/' . $_GET['tx'];

			/**
			 * if response is from paypal express  
			 */
			if( isset( $_POST['action'] ) && $_POST['action'] == 'exms_payment_complete' ) {
				$paypal_checking_url = isset( $paypal_settings->transaction_mode ) && $paypal_settings->transaction_mode == 'live' ? 'https://api.paypal.com/v2/checkout/orders/' . $_POST['order_id'] : 'https://api.sandbox.paypal.com/v2/checkout/orders/' . $_POST['order_id'];
				$quiz_id = isset( $_POST['quizId'] ) && ! empty( $_POST['quizId'] ) ? $_POST['quizId'] : '';
			}

			$curl = curl_init();
			curl_setopt_array( $curl, array(
			  CURLOPT_URL => $paypal_checking_url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_HTTPHEADER => array(
			    'accept: application/json',
			    'accept-language: en_US',
			    'authorization: bearer ' . $access_token
			  ),
			));

			$resp = curl_exec( $curl );
			$err = curl_error( $curl );
			curl_close($curl);

			if ( $err ) {
			  echo "cURL Error #:" . $err;
			} else {
			  $resp = json_decode( $resp );

			  if( isset( $resp->id ) ) {
			  	
			  	/**
			  	 * if quiz already unlocked then prevent unlock time conflict
			  	 */

			  	if( get_user_meta( wp_get_current_user()->ID, 'exms_unlocked_' . $quiz_id, true ) ) {
			  		return;
			  	}

			  	/**
			  	 * update quiz unlock time
			  	 */
			  	update_user_meta( wp_get_current_user()->ID, 'exms_unlocked_' . $quiz_id, time() );
			  	
			  	/**
			  	 * save subscription days if quiz type is subscribe
			  	 */
			  	if( exms_get_quiz_type( $quiz_id ) == 'subscribe' ) {

			  		$settings = exms_get_quiz_settings( $quiz_id );

			  		$subs_days = isset( $settings['quiz_sub_days'] ) && ! empty( $settings['quiz_sub_days'] ) ? $settings['quiz_sub_days'] : 1;

			  		update_user_meta( wp_get_current_user()->ID, 'exms_subscription_' . $quiz_id, $subs_days );
			  	}
			  }
			}
		}

		/**
		 * Verify stripe
		 */
		if( isset( $_GET['stripe_session_id'] ) && ! empty( $_GET['stripe_session_id'] ) ) {

			$stripe_settings = exms_get_stripe_settings() ? exms_get_stripe_settings() : null;
			$stripe_client_secret = isset( $stripe_settings->client_id ) ? $stripe_settings->client_id : '';

			$curl = curl_init();

			curl_setopt_array( $curl , array(
				CURLOPT_URL => 'https://api.stripe.com/v1/checkout/sessions/' . $_GET['stripe_session_id'],
				CURLOPT_HTTPHEADER => [
					'content-type: application/json',
					'authorization: bearer '. $stripe_client_secret
				],
				CURLOPT_RETURNTRANSFER => true
			) );

			$res = curl_exec( $curl );
			$err = curl_error( $curl );
			curl_close( $curl );

			$res = json_decode( $res );

			if( isset( $res->id ) ) {

				/**
			  	 * if quiz already unlocked then prevent unlock time conflict
			  	 */
			  	if( get_user_meta( wp_get_current_user()->ID, 'exms_unlocked_' . $res->client_reference_id , true ) ) {
			  		return;
			  	}

			  	/**
			  	 * update quiz unlock time
			  	 */
				update_user_meta( wp_get_current_user()->ID, 'exms_unlocked_' . $res->client_reference_id , time() );

				/**
			  	 * save subscription days if quiz type is subscribe
			  	 */
				if( exms_get_quiz_type( $res->client_reference_id ) == 'subscribe' ) {
			  		
			  		$settings = exms_get_quiz_settings( $res->client_reference_id );

			  		$subs_days = isset( $settings['quiz_sub_days'] ) && ! empty( $settings['quiz_sub_days'] ) ? $settings['quiz_sub_days'] : 1;

			  		update_user_meta( wp_get_current_user()->ID, 'exms_subscription_' . $res->client_reference_id, $subs_days );
			  	}
			}
		}
	}
}

/**
 * Initialize EXMS_Template
 */
EXMS_Template::instance();