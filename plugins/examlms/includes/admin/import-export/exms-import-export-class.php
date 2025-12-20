<?php
/**
 * WP Exam - Import/Export
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class EXMS_Import_Export
 *
 * class to handles all import/export
 */
class EXMS_Import_Export {

	/**
     * @var self
     */
    private static $instance;
    private $import_export_page = false;

	public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Import_Export ) ) {

            self::$instance = new EXMS_Import_Export;
            if( isset( $_GET['page'] ) && $_GET['page'] === 'import_export' ) {
                self::$instance->import_export_page = true;
            }
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'admin_enqueue_scripts' , [ $this,'exms_import_export_enqueue'] );
        add_action( 'init', [ $this, 'exms_export_csv_file' ] );
		add_action( 'init', [ $this, 'exms_import_csv_file' ] );
		add_action( 'wp_ajax_exms_generate_exp_post_type', [ $this, 'exms_generate_exp_post_type' ] );
    }

	/**
     * Import & Export Functionality Files added and nonce creation
     */
    public function exms_import_export_enqueue() {
        
        if ( !$this->import_export_page ) {
            return false;
        }
        
		wp_enqueue_style( 'exms-import-export-css', EXMS_ASSETS_URL . '/css/admin/import-export/wpeq_import_export.css', [], EXMS_VERSION, null );
    }

	/**
	 * Change export post type and get post in select post field
	 */
	public function exms_generate_exp_post_type() {

		$post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : '';
		if( ! $post_type ) {
			echo __( 'Post type not found.', WP_EXAMS );

			wp_die();
		}

		$args = array(
		  'numberposts' => -1,
		  'post_type'   => $post_type,
		  'post_status'	=> 'publish',
		  'fields' 		=> 'ids'
		);

		$query = get_posts( $args );
		?>
		<option value=""><?php echo 'Select '. ucwords( str_replace( 'exms_', '', $post_type ) ); ?></option>
		<?php
		if( $query ) {
			foreach( $query as $post_id ) {
				?>
				<option value="<?php echo $post_id; ?>"><?php echo get_the_title( $post_id ); ?></option>
				<?php
			}
		}

		wp_die();
	}

	/**
	 * Import WP Exam Quiz data
	 */
	public function exms_import_csv_file() {

		if( isset( $_POST['exms_import'] ) && 'exms_import_action' == $_POST['action']  ) {

			$row = 1;
			if( ( $handle = fopen( $_FILES['csv_file']['tmp_name'], 'r' ) ) !== FALSE ) {

			    while( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {

			    	if ( ! function_exists( 'post_exists' ) ) {
					    require_once( ABSPATH . 'wp-admin/includes/post.php' );
					}

					$post_id = 0;
			        $fount_post = post_exists( $data[0] );
					if( $fount_post ) {
						
						$quiz_post = exms_get_page_by_title( $data[0], '', 'exms_quizzes' );
				        if( $quiz_post ) {
				            $post_id = intval( $quiz_post->ID );
				        }
					} else {

						$my_post = [
							'post_type'		=> 'exms_quizzes',
						    'post_title'    => $data[0],
						    'post_status'   => 'publish',
						    'post_author'   => get_current_user_id(),
						];

						$post_id = wp_insert_post( $my_post );
					}

					$categories = array_map( 'intval', explode( ',', $data[2] ) );
					$tags = array_map( 'intval', explode( ',', $data[3] ) );

					wp_set_object_terms( $post_id, $categories, 'exms_quizzes_categories', true );
        			wp_set_object_terms( $post_id, $tags, 'exms_quizzes_tags', true );
					$post_type = get_post_type( $post_id );

					$quiz_options = isset( $data[4] ) ? unserialize( $data[4] ) : [];

					update_post_meta( $post_id, $post_type.'_opts', $quiz_options );
					update_post_meta( $post_id, 'exms_quiz_type', $data[5] );
					
					$question_data = isset( $data[6] ) ? unserialize( $data[6] ) : '';
					if( $question_data && is_array( $question_data ) ) {

						foreach( $question_data as $ques ) {

							$question_title = isset( $ques['question_title'] ) ? $ques['question_title'] : '';
							$question_option = isset( $ques['question_options'] ) ? $ques['question_options'] : '';

							$fount_post = post_exists( $question_title );
							if( $fount_post ) {
								
						        $post = exms_get_page_by_title( $question_title, '', 'exms_questions' );
						        if( $post ) {
						            $question_id = $post->ID;
						        }

							} else {

								$question_post = [
									'post_type'		=> 'exms_questions',
								    'post_title'    => $question_title,
								    'post_status'   => 'publish',
								    'post_author'   => get_current_user_id(),
								];

								$question_id = wp_insert_post( $question_post );
							}

							$question_post_type = get_post_type( $question_id );
							
							update_post_meta( $question_id, 'exms_attached_quizzes_'.$post_id, $post_id );
							update_post_meta( $question_id, $question_post_type.'_opts', $question_option );
						}
					}
			    }

			    fclose( $handle );
			}
		}
	}

	/**
	 * Exports WP Exams quiz datas
	 */
	public function exms_export_csv_file() {

		/**
		 * Export quiz data
		 */
		if( isset( $_POST['exms_export'] ) 
			&& 'exms_export_action' == $_POST['action'] 
			&& check_admin_referer( 'exms_export_nonce', 'exms_export_nonce_field' ) ) {

			$post_type = isset( $_POST['exms_exp_post_type'] ) ? $_POST['exms_exp_post_type'] : '';
			$post_id = isset( $_POST['exms_select_post_to_export'] ) ? (int) $_POST['exms_select_post_to_export'] : '';
			if( $post_id ) {

				$filter_name = str_replace( 'exms_', '', $post_type );
				$filename = $filter_name .'_' . date("Y-m-d") . '.csv';

	            header('Content-type: text/csv');
	            header('Content-Disposition: attachment; filename="'.$filename.'"');
	            header('Pragma: no-cache');
	            header('Expires: 0');
	  
	            $file = fopen( 'php://output', 'w' );

				$post_category = $post_type.'_categories';
				$post_tags = $post_type.'_tags';

				setup_postdata( $post_id );

				$post_type = get_post_type( $post_id );

				$cat_ids = [];
				$cat_terms = get_terms( $post_category, [ 
					'hide_empty' => 0,
					'object_ids' => $post_id,
				] );
				if( $cat_terms ) {
					foreach( $cat_terms as $cat ) {

						$cat_ids[] = $cat->term_id;
					}
				}

				$tags_ids = [];
				$tag_terms = get_terms( $post_tags, [ 
					'hide_empty' => 0,
					'object_ids' => $post_id,
				] );
				if( $tag_terms ) {
					foreach( $tag_terms as $tag ) {

						$tags_ids[] = $tag->term_id;
					}
				}

				$post_options = get_post_meta( $post_id, $post_type.'_opts', true );
				$price_type = get_post_meta( $post_id, 'exms_quiz_type', true );

				/**
				 * Get quiz attach questions
				 */
				$question_data = [];
				$question_options = [];
				$question_ids = exms_get_questions_for_a_quiz( $post_id );
				if( $question_ids && is_array( $question_ids ) ) {
					foreach( $question_ids as $question_id ) {

						$question_title = get_the_title( $question_id );
						$question_post_type = get_post_type( $question_id );

						$question_data[] = [
							'question_title'	=> get_the_title( $question_id ),
							'question_options'	=> get_post_meta( $question_id, $question_post_type.'_opts', true )
						];
					}
				}

				$post_args = [ 
					[ 
						'post_title'		=> get_the_title( $post_id ),
						'permalink'			=> get_the_permalink( $post_id ),
						'categories'		=> implode( ',', $cat_ids ),
						'tags'				=> implode( ',', $tags_ids ),
						'post_options'		=> serialize( $post_options ),
						'post_price_type'	=> $price_type,
						'question_data'  	=> serialize( $question_data )
					]
				];

				foreach( $post_args as $post_arg ) {

					fputcsv( $file, $post_arg );
				}
				exit();
			}
		}
	}

	/**
	 * Import/Export page content
	 */
	public static function exms_import_export_content() {

		if ( !self::$instance->import_export_page ) {
            return false;
        }

		if( file_exists( EXMS_INCLUDES_DIR . '/admin/import-export/exms-import-export.php' ) ) {

			require_once EXMS_INCLUDES_DIR . '/admin/import-export/exms-import-export.php';
		}
	}
}

EXMS_Import_Export::instance();
