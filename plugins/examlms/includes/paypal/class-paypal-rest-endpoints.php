<?php
/**
 * PayPal REST Endpoints
 * 
 * WordPress REST API endpoints for secure server-side PayPal operations
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_PayPal_REST_Endpoints {

    private static $instance;
    private $paypal_api;

    /**
     * Create class instance
     */
    public static function instance() {
        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_PayPal_REST_Endpoints ) ) {
            self::$instance = new EXMS_PayPal_REST_Endpoints();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->paypal_api = EXMS_PayPal_API::instance();
        $this->register_hooks();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
    }

    /**
     * Register REST API endpoints
     */
    public function register_endpoints() {
        
        // Create order endpoint
        register_rest_route( 'paypal/v1', '/create-order', array(
            'methods' => 'POST',
            'callback' => array( $this, 'create_order' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'course_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ),
                'nonce' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ) );

        // Capture order endpoint
        register_rest_route( 'paypal/v1', '/capture-order', array(
            'methods' => 'POST',
            'callback' => array( $this, 'capture_order' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'order_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'course_id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint'
                ),
                'nonce' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ) );
    }

    /**
     * Check permissions for API access
     * 
     * @param WP_REST_Request $request Request object
     * @return bool True if allowed
     */
    public function check_permissions( $request ) {
        
        // Verify nonce for security
        $nonce = $request->get_param( 'nonce' );
        if( ! wp_verify_nonce( $nonce, 'exms_ajax_nonce' ) ) {
            return false;
        }

        // Allow both logged-in and guest users (for guest checkout)
        return true;
    }

    /**
     * Create PayPal order endpoint
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function create_order( $request ) {
        
        $course_id = $request->get_param( 'course_id' );

        // Validate course exists and is paid
        $course_validation = $this->validate_course( $course_id );
        if( is_wp_error( $course_validation ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => $course_validation->get_error_message()
            ), 400 );
        }

        // Get course information from database (server-side, tamper-proof)
        $course_info = exms_get_post_settings( $course_id );
        $course_price = isset( $course_info['parent_post_price'] ) ? floatval( $course_info['parent_post_price'] ) : 0;
        $course_title = get_the_title( $course_id );

        if( $course_price <= 0 ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Course price is not valid'
            ), 400 );
        }

        // Get PayPal settings (server-side, tamper-proof)
        $payment_settings = get_option( 'exms_payment_settings', array() );
        $payee_email = isset( $payment_settings['paypal_vender_email'] ) ? $payment_settings['paypal_vender_email'] : '';
        $currency = isset( $payment_settings['paypal_currency'] ) ? $payment_settings['paypal_currency'] : 'USD';

        if( empty( $payee_email ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'PayPal vendor email is not configured'
            ), 500 );
        }

        // Prepare order data
        $order_data = array(
            'amount' => $course_price,
            'currency' => $currency,
            'description' => 'Course: ' . $course_title,
            'course_id' => $course_id,
            'payee_email' => $payee_email
        );

        // Create PayPal order
        $paypal_response = $this->paypal_api->create_order( $order_data );

        if( is_wp_error( $paypal_response ) ) {
            error_log( 'PayPal Create Order Error: ' . $paypal_response->get_error_message() );
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to create PayPal order'
            ), 500 );
        }

        // Store order data temporarily for verification during capture
        $this->store_order_data( $paypal_response['id'], $order_data );

        return new WP_REST_Response( array(
            'success' => true,
            'order_id' => $paypal_response['id'],
            'course_title' => $course_title,
            'amount' => $course_price,
            'currency' => $currency
        ), 200 );
    }

    /**
     * Capture PayPal order endpoint
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function capture_order( $request ) {
        
        $order_id = $request->get_param( 'order_id' );
        $course_id = $request->get_param( 'course_id' );

        // Validate course
        $course_validation = $this->validate_course( $course_id );
        if( is_wp_error( $course_validation ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => $course_validation->get_error_message()
            ), 400 );
        }

        // Check for duplicate processing
        if( $this->is_order_already_processed( $order_id ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Order has already been processed'
            ), 400 );
        }

        // Get stored order data for verification
        $stored_order_data = $this->get_stored_order_data( $order_id );
        if( ! $stored_order_data ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Order data not found or expired'
            ), 400 );
        }

        // Capture the order
        $capture_response = $this->paypal_api->capture_order( $order_id );

        if( is_wp_error( $capture_response ) ) {
            error_log( 'PayPal Capture Order Error: ' . $capture_response->get_error_message() );
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to capture PayPal order'
            ), 500 );
        }

        // Verify payment against stored data
        $verification = $this->paypal_api->verify_payment( $capture_response, $stored_order_data );

        if( ! $verification['success'] ) {
            error_log( 'PayPal Payment Verification Failed: ' . implode( ', ', $verification['errors'] ) );
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Payment verification failed'
            ), 400 );
        }

        // Mark order as processed to prevent duplicates
        $this->mark_order_as_processed( $order_id );

        // Process the enrollment and transaction
        $enrollment_result = $this->process_course_enrollment( $course_id, $verification['payment_data'] );

        if( is_wp_error( $enrollment_result ) ) {
            error_log( 'Course Enrollment Error: ' . $enrollment_result->get_error_message() );
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Payment successful but enrollment failed. Please contact support.'
            ), 500 );
        }

        // Clean up stored order data
        $this->cleanup_order_data( $order_id );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Payment completed successfully',
            'transaction_id' => $verification['payment_data']['transaction_id'],
            'user_id' => $enrollment_result['user_id']
        ), 200 );
    }

    /**
     * Validate course for payment
     * 
     * @param int $course_id Course ID
     * @return true|WP_Error True if valid, error if not
     */
    private function validate_course( $course_id ) {
        
        if( empty( $course_id ) ) {
            return new WP_Error( 'invalid_course', 'Course ID is required' );
        }

        $post = get_post( $course_id );
        if( ! $post ) {
            return new WP_Error( 'course_not_found', 'Course not found' );
        }

        // Check if course is published
        if( $post->post_status !== 'publish' ) {
            return new WP_Error( 'course_not_available', 'Course is not available' );
        }

        return true;
    }

    /**
     * Store order data temporarily for verification
     * 
     * @param string $order_id PayPal order ID
     * @param array $order_data Order data
     */
    private function store_order_data( $order_id, $order_data ) {
        $transient_key = 'exms_paypal_order_' . $order_id;
        set_transient( $transient_key, $order_data, 3600 ); // Store for 1 hour
    }

    /**
     * Get stored order data
     * 
     * @param string $order_id PayPal order ID
     * @return array|false Order data or false
     */
    private function get_stored_order_data( $order_id ) {
        $transient_key = 'exms_paypal_order_' . $order_id;
        return get_transient( $transient_key );
    }

    /**
     * Clean up stored order data
     * 
     * @param string $order_id PayPal order ID
     */
    private function cleanup_order_data( $order_id ) {
        $transient_key = 'exms_paypal_order_' . $order_id;
        delete_transient( $transient_key );
    }

    /**
     * Check if order has already been processed
     * 
     * @param string $order_id PayPal order ID
     * @return bool True if already processed
     */
    private function is_order_already_processed( $order_id ) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'exms_payment_transaction';
        
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE paypal_order_id = %s",
            $order_id
        ) );

        return $count > 0;
    }

    /**
     * Mark order as processed
     * 
     * @param string $order_id PayPal order ID
     */
    private function mark_order_as_processed( $order_id ) {
        $transient_key = 'exms_paypal_processed_' . $order_id;
        set_transient( $transient_key, true, 86400 ); // Store for 24 hours
    }

    /**
     * Process course enrollment after successful payment
     * 
     * @param int $course_id Course ID
     * @param array $payment_data Payment data from PayPal
     * @return array|WP_Error Enrollment result or error
     */
    private function process_course_enrollment( $course_id, $payment_data ) {
        
        // Get or create user
        $user_id = $this->get_or_create_user( $payment_data );
        
        if( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        // Save transaction to database
        $transaction_data = array(
            'order_id' => $payment_data['order_id'],
            'transaction_id' => $payment_data['transaction_id'],
            'amount' => $payment_data['amount'],
            'currency' => $payment_data['currency'],
            'status' => $payment_data['status'],
            'payer' => array(
                'email_address' => $payment_data['payer_email'],
                'name' => array(
                    'given_name' => $payment_data['payer_name']
                )
            )
        );

        try {
            EXMS_Payment_Func::exms_insert_into_payment_transaction( $transaction_data, $user_id, $course_id, $payment_data['amount'] );
        } catch( Exception $e ) {
            error_log( 'Transaction Save Error: ' . $e->getMessage() );
            return new WP_Error( 'transaction_failed', 'Failed to save transaction' );
        }

        // Enroll user in course
        $post_type = get_post_type( $course_id );
        $enrollment_result = EXMS_PR_Fn::exms_enroll_user_to_content(
            $user_id,
            $course_id,
            $post_type,
            $user_id,
            'enrolled',
            0,
            current_time( 'timestamp' ),
            ''
        );

        if( ! $enrollment_result ) {
            return new WP_Error( 'enrollment_failed', 'Failed to enroll user in course' );
        }

        return array(
            'user_id' => $user_id,
            'enrollment_id' => $enrollment_result
        );
    }

    /**
     * Get or create user from payment data
     * 
     * @param array $payment_data Payment data
     * @return int|WP_Error User ID or error
     */
    private function get_or_create_user( $payment_data ) {
        
        $current_user_id = get_current_user_id();
        
        // If user is logged in, use their ID
        if( $current_user_id > 0 ) {
            return $current_user_id;
        }

        // For guest checkout, create/find user from PayPal data
        if( empty( $payment_data['payer_email'] ) ) {
            return new WP_Error( 'no_user_data', 'No user information available' );
        }

        $payer_email = sanitize_email( $payment_data['payer_email'] );
        $payer_name = sanitize_text_field( $payment_data['payer_name'] );

        // Check if user already exists
        $existing_user = get_user_by( 'email', $payer_email );
        if( $existing_user ) {
            return $existing_user->ID;
        }

        // Create new user
        $username = sanitize_user( $payer_email );
        $password = wp_generate_password();

        // Split name into first and last name
        $name_parts = explode( ' ', trim( $payer_name ), 2 );
        $first_name = isset( $name_parts[0] ) ? $name_parts[0] : '';
        $last_name = isset( $name_parts[1] ) ? $name_parts[1] : '';

        $user_data = array(
            'user_login'    => $username,
            'user_email'    => $payer_email,
            'user_pass'     => $password,
            'display_name'  => $payer_name,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'role'          => 'subscriber'
        );

        $user_id = wp_insert_user( $user_data );

        if( is_wp_error( $user_id ) ) {
            error_log( 'User Creation Error: ' . $user_id->get_error_message() );
            return new WP_Error( 'user_creation_failed', 'Failed to create user account' );
        }

        return $user_id;
    }
}

