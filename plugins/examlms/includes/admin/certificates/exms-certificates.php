<?php
/**
 * WP Exam - Certificates
 *
 * All certificate related functions
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class EXMS_certificates 
 */
class EXMS_certificates extends EXMS_DB_Main {
 
    private static $instance;

	/**
     * Connect to wpdb
     */
    private static $wpdb;
	private $certificate_page = false;
	private $table_check = false;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_certificates ) ) {

        	self::$instance = new EXMS_certificates;

        	global $wpdb;
            self::$wpdb = $wpdb;

			if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'exms_certificates' || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) ) {
				self::$instance->certificate_page = true;
			}
        	self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
	 * Create hooks
	 */
	public function hooks() {
		
        //add_action( 'admin_notices', [ $this, 'exms_show_missing_table_notice' ] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_certificates_enqueue'] );
        add_action( 'wp_ajax_create_exms_certificates_table', [ $this , 'create_exms_certificates_table' ] );

	}

    /**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->certificate_page ) {
            return false;
        }

        $table_exists = $this->csm_validate_table();
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
     * If table not exist will pass in the array
     */
    public function csm_validate_table() {
        
        global $wpdb;
        
		$user_post_relations_table_name = $wpdb->prefix.'exms_user_post_relations';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $user_post_relations_table_name ) ) {
            $not_exist_tables[] = 'user_post_relations';
        }
    
        return $not_exist_tables;
    }

    /**
     * Quiz Functionality Files added and nonce creation
     */
    public function exms_certificates_enqueue() {
        
        if ( !$this->certificate_page ) {
            return false;
        }
        
        /**
         * Custom radio buttons
         */
        wp_enqueue_style( 'EXMS_custom_radio_buttons', EXMS_ASSETS_URL . 'css/custom_radio_buttons.css', [], EXMS_VERSION, null );

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ),[ 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ], false, 1);
        wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), [ 'iris' ], false,1 );
        $colorpicker_arr = array( 
            'clear' => __( 'Clear' ), 
            'defaultString' => __( 'Default' ), 
            'pick' => __( 'Select Color' ) 
        );
        wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_arr ); 
    }

    /**
     * Create certificates tables
     */
    public function create_exms_certificates_table() {

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
     * create a function to get specific index array
     */
    public static function exms_get_specific_index_array( $array, $specific_word ) {
 
        $specific_index_array = [];

        if( $array && is_array( $array ) ) {

            foreach( $array as $key => $arr ) {
                
                $specific_text = preg_replace( '/\d+/u', '', $key );
                
                if( $specific_text == $specific_word ) { 
                    
                    $specific_index_array[] = $arr;
                }
            }
        }
        return $specific_index_array;
    }
 
    /**
     * Badges PDF options
     */
    public static function exms_pdf_options() {
        
        global $post;

        $post_id = isset( $_GET['post'] ) ? $_GET['post'] : '';
        $opts = exms_get_post_options( $post_id, 'exms_' );
        $selected_certificate = isset( $opts['exms_selected-certificate'] ) ? intval( $opts['exms_selected-certificate'] ) : '';
        $certificate_title = isset( $opts['exms_certificate-title'] ) ? $opts['exms_certificate-title'] : '';
        $certificate_user_name = isset( $opts['exms_certificate-user-name'] ) ? $opts['exms_certificate-user-name'] : '';
        $certificate_content = isset( $opts['exms_certificate-content'] ) ? $opts['exms_certificate-content'] : '';
        $page_type = isset( $opts['exms_pdf_page_type'] ) ? $opts['exms_pdf_page_type'] : 'single';
        $cert_page_size = isset( $opts['exms_pdf-page-size'] ) ? $opts['exms_pdf-page-size'] : '';
        $cert_page_orientation = isset( $opts['exms_pdf-page-orientation'] ) ? $opts['exms_pdf-page-orientation'] : '';
        $admin_sign = isset( $opts['exms_certificate-sign'] ) ? $opts['exms_certificate-sign'] : '';
        $pdf_title_color = isset( $opts['exms_title-color'] ) ? $opts['exms_title-color'] : '#000000';
        $pdf_username_color = isset( $opts['exms_user-name-color'] ) ? $opts['exms_user-name-color'] : '#000000';
        $pdf_title_size = isset( $opts['exms_title-f-size'] ) ? $opts['exms_title-f-size'] : '24';
        $pdf_username_size = isset( $opts['exms_user-f-size'] ) ? $opts['exms_user-f-size'] : '20';
        $cer_title = isset( $opts['exms_certificate-title'] ) ? $opts['exms_certificate-title'] : 'certificate of achivement';
        $cer_inner_title = isset( $opts['exms_certificate-user-name'] ) ? $opts['exms_certificate-user-name'] : 'proudly awarded to: {user_name}';
        $cert_signed = isset( $opts['exms_certificate-signed'] ) ? $opts['exms_certificate-signed'] : '';

        $page_orientation = isset( $opts['exms_pdf-page-orientation'] ) ? $opts['exms_pdf-page-orientation'] : '';

        // $wp_cert_height = '800px';
        // $wp_cert_width = '100%';

        // if( 'l' == $page_orientation ) {
        //     $wp_cert_height = '500px';
        //     $wp_cert_width = '100%';
        // }
        ?>
        <div class="exms-row">
            <div class="exms-title"><?php _e( 'Page Type', WP_EXAMS ); ?></div>
            <div class="exms-data exms-row"> 

                <!-- Single type radio -->
                <input type="radio" class="custom_radio wpeq_quiz_type" name="exms_pdf_page_type" id="rb3" value="single" <?php echo 'single' == $page_type ? 'checked="checked"' : ''; ?> />
                <label class='custom_radio_label' for="rb3">
                    <?php _e( 'Single', WP_EXAMS ); ?>
                </label>

                <!-- Multiple type radio -->
                <input type="radio" class="custom_radio wpeq_quiz_type" name="exms_pdf_page_type" id="rb4" value="multiple" <?php echo 'multiple' == $page_type ? 'checked="checked"' : ''; ?> />
                <label class='custom_radio_label' for="rb4">
                    <?php _e( 'Multiple', WP_EXAMS ); ?>
                </label>
            </div>
        </div>

        <!-- PDF page size -->

        <div class="exms-row">
            <div class="exms-title"><?php _e( 'PDF Page Size', WP_EXAMS ); ?></div>
            <div class="exms-data exms-row"> 
                <select name="exms_pdf-page-size" class="exms-pdf-page-size">
                    <option value="LETTER" <?php selected( 'letter', $cert_page_size, true ); ?>><?php echo __( 'Letter / USLetter (default)', WP_EXAMS ); ?></option>
                    <option value="A4" <?php selected( 'A4', $cert_page_size, true ); ?>><?php echo __( 'A4', WP_EXAMS ); ?></option>
                </select>
            </div>
        </div>

        <!-- PDF page Orientation -->

        <div class="exms-row">
            <div class="exms-title"><?php _e( 'Page Type', WP_EXAMS ); ?></div>
            <div class="exms-data exms-row"> 
                <select name="exms_pdf-page-orientation" class="exms-pdf-orientation">
                    <option value="l" <?php selected( 'l', $cert_page_orientation, true ); ?>><?php echo __( 'Landscape (default)', WP_EXAMS ); ?></option>
                    <option value="p" <?php selected( 'p', $cert_page_orientation, true ); ?>><?php echo __( 'Portrait', WP_EXAMS ); ?></option>
                </select>
            </div>
        </div>

        <!-- PDF Designs -->

        <div class="exms-certificate-design-row">
            <div class="exms-title"><?php _e( 'Select PDF Designs', WP_EXAMS ); ?></div>
            <?php

            if( 'l' == $page_orientation ) {
                ?>
                <style type="text/css">

                    #exms-pdf-template-wrap {
                        height: 85vh;
                        width: 100%;
                    }
                    .exms-pdf-header {
                        padding: 15% 10% 0;
                    }
                    .exms-pdf-footer {
                        padding: 6% 0;
                    }

                </style>
                <?php
            } else {
                ?>
                <style type="text/css">

                    #exms-pdf-template-wrap {
                        height: 100vh;
                        width: 85%;
                    }
                    .exms-pdf-header {
                        padding: 20% 20% 14% 20%;
                    }
                    .exms-pdf-footer {
                        padding: 13% 0;
                    }
                </style>
                <?php
            }

            $certificates = [ 1, 2, 3, 4, 5, 6, 7, 8 ];
            foreach( $certificates as $certificate ) {
                
                $extension = 'jpg';

                if( 3 == $certificate || 5 == $certificate || 6 == $certificate || 7 == $certificate ) {

                    $extension = 'png';
                }
                
                $updated_certificate = '';
                $uploaded_certificate = '';

                if( $selected_certificate == $certificate ) {
                    $updated_certificate = 'exms-updated-dertificate';
                    $uploaded_certificate = 'exms_selected-certificate';
                }

                ?>
                <div class="exms-cert-img <?php echo $updated_certificate; ?>"> 
                    <a href="#exms-pdf-template-wrap">
                        <img src="<?php echo EXMS_ASSETS_URL.'imgs/'.$certificate.'.'.$extension.''; ?>" bgimage="<?php echo EXMS_ASSETS_URL.'imgs/CERT-'.$certificate.'.png'; ?>">
                    </a>
                    <input type="hidden" class="exms-certificate-hidden" value="<?php echo $certificate; ?>" name="<?php echo $uploaded_certificate; ?>">
                </div> 
                <?php
            }
            ?>
        </div>
        <?php
        echo self::exms_certificate_image( $post_id, 'exms-set-certificate', 'Upload Certificate Image' );
        ?>
        <div class="exms-clear-both"></div>
        
        <!-- pdh html design -->

        <div id="exms-pdf-template-wrap">

            <div class="exms-pdf-header">
                <div class="exms-pdf-title" style="color:<?php echo $pdf_title_color; ?>; font-size:<?php echo $pdf_title_size.'px'; ?>;"><?php echo $cer_title; ?></div>
                <div class="exms-pdf-iner-title" style="color:<?php echo $pdf_username_color; ?>; font-size:<?php echo $pdf_username_size.'px'; ?>;"><?php echo $cer_inner_title; ?></div>
            </div>
            <div class="exms-pdf-content">
                dummy content dummy content dummy content dummy content dummy content dummy content dummy content dummy content
            </div>
            <div class="exms-pdf-footer">
                <div class="exms-pdf-date">
                    <img src="<?php echo $cert_signed; ?>">
                </div>
                <div class="exms-clear-both"></div>
            </div>
        </div>
        <!-- -->

        <div class="exms-certificate-design-row exms-certificate-title">
            <div class="exms-title"><?php _e( 'Certificate Title', WP_EXAMS ); ?></div>
            <input name="exms_certificate-title" value="<?php echo $certificate_title; ?>" class="exms-title-input">
            <input type="text" class="exms-color-picker" data-target="title" name="exms_title-color" value="<?php echo $pdf_title_color; ?>">
            <input type="number" name="exms_title-f-size" class="exms-t-f-size" value="<?php echo $pdf_title_size; ?>" title="<?php echo __( 'Select Font size', WP_EXAMS ); ?>">
        </div>

        <div class="exms-certificate-design-row exms-certificate-user-content">
            <div class="exms-title"><?php _e( 'User Name', WP_EXAMS ); ?></div>
            <input name="exms_certificate-user-name" value="<?php echo $certificate_user_name; ?>" class="exms-name-input">
            <input type="text" class="exms-color-picker" data-target="username" name="exms_user-name-color" value="<?php echo $pdf_username_color; ?>">
            <input type="number" name="exms_user-f-size" class="exms-u-f-size" value="<?php echo $pdf_username_size; ?>" title="<?php echo __( 'Select Font size', WP_EXAMS ); ?>">
        </div>

        <div class="exms-row">
            <div class="exms-title"><?php _e( 'Certificate Sign', WP_EXAMS ); ?></div>
            <div class="exms-data exms-row">
                <div class="exms-certificate-sign-wrap">
                    <span class="dashicons dashicons-no-alt exms-certificate-remove"></span>
                    <img src="<?php echo $cert_signed; ?>">
                    <input type="hidden" name="exms_certificate-signed" value="<?php echo $cert_signed; ?>">
                </div>
                <?php
                echo self::exms_certificate_image( $post_id, 'exms-certificate-sign', 'Upload Certificate Sign' );
                ?>
            </div>
        </div>

        <div class="exms-clear-both"></div>
        <?php
    }

    /**
     * Badges images quizzes content HTML
     */
    public static function exms_certificate_image( $post_id, $class, $text  ) {
         
        $options = exms_get_post_options( $post_id );
        $certificate_image_url = isset( $options['exms_certificate_image_url'] ) ? $options['exms_certificate_image_url'] : '';
        $certificate_main_wrapper = 'exms-set-certificate' ==  $class ? 'backimg' : 'signed';
        ?>
        <div class="exms-metabox-container-<?php echo $certificate_main_wrapper; ?>">
            <?php
            if( 'exms-set-certificate' == $class ) {

                if( $certificate_image_url && is_array( $certificate_image_url ) ) {

                    foreach( $certificate_image_url as $certificate_image ) {
                        ?>
                        <div class="exms-cert-img exms-save-image-value-<?php echo $post_id; ?>">
                        <span class="dashicons dashicons-no-alt exms-certificate-remove"></span>
                        <?php echo '<a href="#exms-pdf-template-wrap"><img src="'.$certificate_image.'" bgimage="'.$certificate_image.'"></a>'; ?>
                        <input type="hidden" class="exms-hidden-certificate-image-<?php echo $post_id; ?>" name="exms_certificate_image_url[]" value="<?php echo $certificate_image; ?>">
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="exms-cert-img exms-save-image-value-<?php echo $post_id; ?>">
                    </div>
                    <?php
                }
            }
            ?>
        </div>  
        <div class="exms-clear-both"></div>

        <div class="exms-certificate-btn">
            <button data-post_id="<?php echo $post_id; ?>" class="<?php echo $class; ?> button button-primary" type="button">
                <?php echo $text; ?>
            </button>
        </div>    
        <?php
    }

    /**
     * assign certificate to Course
     */
    public static function exms_course_certificate_callback_html( $post ) {

        echo exms_current_assign_post_html( $post, EXMS_PR_Fn::exms_get_parent_post_type() );
    }

    /**
     * assign certificate to quiz
     */
    public static function exms_quiz_certificate_callback_html( $post ) {
        
        echo exms_current_assign_post_html( $post, 'exms_quizzes' );
    }
}

EXMS_certificates::instance();