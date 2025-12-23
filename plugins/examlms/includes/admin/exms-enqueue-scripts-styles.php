<?php
/**
 * WP Exam - Enqueue Scripts
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Exms_Enqueue_scripts
 *
 * Base class to add backend and frontend css
 */
class Exms_Enqueue_scripts {

    private static $instance;

    /**
     * Create class instance
     */
    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof Exms_Enqueue_scripts ) ) {

            self::$instance = new Exms_Enqueue_scripts;
            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define Hooks
     */
    public function hooks() {

        add_action( 'admin_enqueue_scripts', [ $this, 'exms_admin_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'exms_templates_scripts' ] );
    }

    /**
     * Add admin scripts/css
     */
    public function exms_admin_scripts() {

        global $post;

        $screen = get_current_screen();
        wp_enqueue_media();

        /**
         * Add settings CSS
         */
        wp_enqueue_style( 'exms-settings-style', EXMS_ASSETS_URL . 'css/exms-settings.css', '', EXMS::VERSION, null );

        /**
         * Add customized radio buttons
         */
        wp_enqueue_style( 'exms-settings-custom-radio', EXMS_ASSETS_URL . 'css/custom_radio_buttons.css', '', EXMS::VERSION, null );

        /**
         * Admin panel CSS
         */
        wp_enqueue_style( 'exms-admin-css', EXMS_ASSETS_URL . '/css/exms-admin.css', [], EXMS::VERSION, null );

        /**
         * Select2 CSS
         */
        wp_enqueue_style( 'EXMS_select2-css', EXMS_ASSETS_URL . '/css/select2.min.css', [], EXMS::VERSION, null );

        /**
         * JS related to admin panel
         */
        wp_enqueue_script( 'EXMS-global-js', EXMS_ASSETS_URL . 'js/exms-global.js', [ 'jquery' ], '', true );

        /**
         * Select2 JS
         */
        wp_enqueue_script( 'EXMS_select2-js', EXMS_ASSETS_URL . '/js/select2.min.js', ['jquery'], EXMS::VERSION, true );

        /**
         * Admin panel JS
         */
        wp_enqueue_script( 'exms-admin-js', EXMS_ASSETS_URL . '/js/exms-admin.js?='.time(), ['jquery'], EXMS::VERSION, true );

        /**
         * Display Chart
         */
        wp_enqueue_script( 'exms-chart-js', EXMS_ASSETS_URL . 'js/chart.min.js', [], EXMS::VERSION, true );

        /**
         * Enqueue multi sort js
         */
        wp_enqueue_script( 'exms-multisort-js', EXMS_ASSETS_URL . 'js/jquery.multipleSortable.js', [ 'jquery' ], '', true );

        /**
         * Add CSS/JS for taxonomy iframe
         */
        if( isset( $_GET['taxonomy'] ) 
            && ( 'exms_questions_categories' == $_GET['taxonomy'] 
            || 'exms_quizzes_categories' == $_GET['taxonomy'] 
            || 'exms_groups_categories' == $_GET['taxonomy'] ) 
            || isset( $_GET['taxonomy'] ) 
            && ( 'exms_quizzes_tags' == $_GET['taxonomy'] 
            || 'exms_questions_tags' == $_GET['taxonomy'] 
            || 'exms_groups_tags' == $_GET['taxonomy'] ) ) {

            wp_enqueue_style( 'exms-taxonomy-iframe', EXMS_ASSETS_URL . 'css/exms-taxonomy-iframe.css', '', EXMS::VERSION, null );
            wp_enqueue_script( 'exms-texonomy-js', EXMS_ASSETS_URL . '/js/exms-texonomy-iframe.js', [ 'jquery' ], EXMS::VERSION, true );
        }

        wp_localize_script( 'EXMS-global-js', 'EXMS', 
            [ 
                'ajaxURL'   => admin_url( 'admin-ajax.php' ),
                'security'  => wp_create_nonce( 'exms_ajax_nonce' ) 
            ] 
        );
        
        wp_enqueue_style( 'exms-custom-popup-css-link', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css', [], EXMS::VERSION, null );
        wp_enqueue_script( 'exms-custom-bootstrap-pop-up-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js', ['jquery'], EXMS::VERSION, true );
        
    }

    /**
     * Add template scripts/css
     */
    public function exms_templates_scripts() {
        
        /**
         * Add styles need for WP EXAMS templates
         */
        wp_enqueue_style( 'EXMS-theme-css', EXMS_ASSETS_URL . 'css/exms-theme.css', [], EXMS::VERSION, null );
        wp_enqueue_style( 'EXMS-range-css', EXMS_ASSETS_URL . 'css/jquery-ui.min.css', [], EXMS::VERSION, null );
        /**
         * Select2 CSS
         */
        wp_enqueue_style( 'EXMS-select2-css', EXMS_ASSETS_URL . 'css/select2.min.css', [], '', '' );

        /**
         * Enqueue Paypal SDK
         */
        $exms_options = get_option( 'exms_settings' );

        $paypal_client_id = isset( $exms_options['paypal_client_id'] ) ? $exms_options['paypal_client_id'] : '';
        $paypal_currency = isset( $exms_options['paypal_currency'] ) ? $exms_options['paypal_currency'] : '';

        $stripe_enable          = isset( $exms_options['stripe_enable'] ) ? $exms_options['stripe_enable'] : '';
        $stripe_redirect_url    = isset( $exms_options['stripe_redirect_url'] ) ? $exms_options['stripe_redirect_url'] : '';
        $stripe_currency        = isset( $exms_options['stripe_currency'] ) ? $exms_options['stripe_currency'] : '';
        $stripe_vender_email    = isset( $exms_options['stripe_vender_email'] ) ? $exms_options['stripe_vender_email'] : '';
        $stripe_api_key         = isset( $exms_options['stripe_api_key'] ) ? $exms_options['stripe_api_key'] : '';
        $stripe_client_secret   = isset( $exms_options['stripe_client_secret'] ) ? $exms_options['stripe_client_secret'] : '';

        wp_enqueue_script( 'EXMS-paypal-sdk', 'https://www.paypal.com/sdk/js?disable-funding=credit,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo,card&client-id='.$paypal_client_id.'&currency='.$paypal_currency.'', [], NULL, false );
        wp_enqueue_script( 'EXMS-exms-stripe-js', 'https://js.stripe.com/v3/', [ 'jquery' ], EXMS::VERSION, true );
        wp_enqueue_script( 'EXMS-exms-stripe-custom-js', EXMS_ASSETS_URL . 'js/exms_stripe.js', [ 'jquery' ], EXMS::VERSION, true );
        wp_enqueue_script( 'EXMS-exms-paypal-js', EXMS_ASSETS_URL . 'js/exms_paypal.js', [ 'jquery' ], EXMS::VERSION, true );


        /**
         * Select2 JS
         */
        wp_enqueue_script( 'EXMS-select2-js', EXMS_ASSETS_URL . 'js/select2.min.js', [ 'jquery' ], '', true );

        /**
         * JS related to WP EXAMS templates
         */
        wp_enqueue_script( 'EXMS-global-js', EXMS_ASSETS_URL . 'js/exms-global.js', [ 'jquery' ], '', true );
        wp_enqueue_script( 'EXMS-range-js', EXMS_ASSETS_URL . 'js/jquery-ui.min.js', [ 'jquery' ], '', true );

        /**
         * JS UI for draggable elements
         */
        wp_enqueue_script( 'WP-EXAMS-jquery-ui', EXMS_ASSETS_URL . 'js/jquery-ui.js', [], EXMS::VERSION, true );

        /**
         * JS related to WP EXAMS templates
         */
        wp_enqueue_script( 'WP-EXAMS-theme-js', EXMS_ASSETS_URL . 'js/exms-theme.js?thme='.time(), ['jquery'], EXMS::VERSION, true );

        /**
         * Display chart on WP EXAMS templates
         */
        wp_enqueue_script( 'exms-chart-js', EXMS_ASSETS_URL . 'js/chart.min.js', [], EXMS::VERSION, true );

        /**
         * Enqueue post completion js file
         */
        wp_enqueue_style( 'exms-custom-popup-css-link', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css', [], EXMS::VERSION, null );
        wp_enqueue_script( 'exms-custom-bootstrap-pop-up-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js', ['jquery'], EXMS::VERSION, true );
        wp_enqueue_script( 'exms-post-completion-js', EXMS_ASSETS_URL . 'js/exms-post-completion.js', [], EXMS::VERSION, true );
 
        $stripe_enable          = isset( $exms_options['stripe_enable'] ) ? $exms_options['stripe_enable'] : "";
        $stripe_currency        = isset( $exms_options['stripe_currency'] ) ? $exms_options['stripe_currency'] : "";
        $stripe_vender_email    = isset( $exms_options['stripe_vender_email'] ) ? $exms_options['stripe_vender_email'] : "";
        $stripe_api_key         = isset( $exms_options['stripe_api_key'] ) ? $exms_options['stripe_api_key'] : "";
        $stripe_client_secret   = isset( $exms_options['stripe_client_secret'] ) ? $exms_options['stripe_client_secret'] : "";

        wp_localize_script( 'EXMS-exms-stripe-custom-js', 'EXMSS', [ 
            'ajaxURL'   => admin_url( 'admin-ajax.php' ),
            'security'  => wp_create_nonce( 'exms_ajax_nonce' ) ,
            'stripe_enable'  => $stripe_enable,
            'stripe_currency'  => $stripe_currency,
            'stripe_vender_email'  => $stripe_vender_email,
            'stripe_api_key'  => $stripe_api_key,
            'stripe_complete_url'  => isset( $stripe_redirect_url['complete_url'] ) ? $stripe_redirect_url['complete_url'] : "",
            'stripe_cancel_url'  => isset( $stripe_redirect_url['cancel_url'] ) ? $stripe_redirect_url['cancel_url'] : "",
            'stripe_client_secret'  => $stripe_client_secret
        ] );

        $options = exms_get_post_options( get_the_ID() );
        $exms_question_result_summary    = 'summary_at_end';
        $exms_question_answer_summary    = 'no';
        $exms_question_correct_incorrect = 'no';
        
        if( !empty($options) ) {
            $exms_question_result_summary = isset( $options['exms_question_result_summary'] ) ? $options['exms_question_result_summary'] : 'summary_at_end';
            $exms_question_answer_summary = isset( $options['exms_question_answer_summary'] ) ? $options['exms_question_answer_summary'] : 'no';
            $exms_question_correct_incorrect = isset( $options['exms_question_correct_incorrect'] ) ? $options['exms_question_correct_incorrect'] : 'no';
        }

        wp_localize_script( 'WP-EXAMS-theme-js', 'EXMS', [ 
            'ajaxURL'                       => admin_url( 'admin-ajax.php' ),
            'security'                      => wp_create_nonce( 'exms_ajax_nonce' ) ,
            'result_summary'                => $exms_question_result_summary ,
            'answer_summary'                => $exms_question_answer_summary,
            'question_correct_incorrect'    => $exms_question_correct_incorrect,
            
        ] );
    }
}

/**
 * Initialize Exms_Enqueue_scripts
 */
Exms_Enqueue_scripts::instance();