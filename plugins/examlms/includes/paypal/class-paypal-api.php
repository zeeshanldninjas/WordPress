<?php
/**
 * PayPal REST API Wrapper
 * 
 * Handles all PayPal REST API operations including order creation, capture, and verification
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_PayPal_API {

    private static $instance;
    private $oauth;

    /**
     * Create class instance
     */
    public static function instance() {
        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_PayPal_API ) ) {
            self::$instance = new EXMS_PayPal_API();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->oauth = EXMS_PayPal_OAuth::instance();
    }

    /**
     * Create PayPal order
     * 
     * @param array $order_data Order data including amount, currency, etc.
     * @return array|WP_Error Order response or error
     */
    public function create_order( $order_data ) {
        
        // TESTING MODE: Return mock PayPal order response
        error_log( 'PayPal API - TEST MODE: Creating mock order for testing' );
        error_log( 'PayPal API - Order Data: ' . json_encode( $order_data ) );
        
        // Generate a fake PayPal order ID for testing
        $mock_order_id = 'TEST_ORDER_' . uniqid() . '_' . time();
        
        // Return mock PayPal order response
        return array(
            'id' => $mock_order_id,
            'status' => 'CREATED',
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'amount' => array(
                        'currency_code' => $order_data['currency'],
                        'value' => number_format( $order_data['amount'], 2, '.', '' )
                    ),
                    'description' => $order_data['description']
                )
            ),
            'links' => array(
                array(
                    'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/' . $mock_order_id,
                    'rel' => 'self',
                    'method' => 'GET'
                ),
                array(
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=' . $mock_order_id,
                    'rel' => 'approve',
                    'method' => 'GET'
                )
            ),
            'create_time' => date( 'c' ),
            'update_time' => date( 'c' )
        );
    }

    /**
     * Capture PayPal order
     * 
     * @param string $order_id PayPal order ID
     * @return array|WP_Error Capture response or error
     */
    public function capture_order( $order_id ) {
        
        // TESTING MODE: Return mock PayPal capture response
        error_log( 'PayPal API - TEST MODE: Capturing mock order for testing' );
        error_log( 'PayPal API - Order ID: ' . $order_id );
        
        // Generate mock capture response
        $mock_capture_id = 'TEST_CAPTURE_' . uniqid() . '_' . time();
        
        // Return mock PayPal capture response
        return array(
            'id' => $order_id,
            'status' => 'COMPLETED',
            'purchase_units' => array(
                array(
                    'reference_id' => 'default',
                    'payments' => array(
                        'captures' => array(
                            array(
                                'id' => $mock_capture_id,
                                'status' => 'COMPLETED',
                                'amount' => array(
                                    'currency_code' => 'USD',
                                    'value' => '50.00'
                                ),
                                'final_capture' => true,
                                'create_time' => date( 'c' ),
                                'update_time' => date( 'c' )
                            )
                        )
                    )
                )
            ),
            'payer' => array(
                'name' => array(
                    'given_name' => 'Test',
                    'surname' => 'User'
                ),
                'email_address' => 'test@example.com',
                'payer_id' => 'TEST_PAYER_' . uniqid()
            ),
            'links' => array(
                array(
                    'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/' . $order_id,
                    'rel' => 'self',
                    'method' => 'GET'
                )
            )
        );
    }

    /**
     * Get order details
     * 
     * @param string $order_id PayPal order ID
     * @return array|WP_Error Order details or error
     */
    public function get_order( $order_id ) {
        
        // TESTING MODE: Return mock PayPal order details
        error_log( 'PayPal API - TEST MODE: Getting mock order details for testing' );
        error_log( 'PayPal API - Order ID: ' . $order_id );
        
        // Return mock order details
        return array(
            'id' => $order_id,
            'status' => 'APPROVED',
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'reference_id' => 'default',
                    'amount' => array(
                        'currency_code' => 'USD',
                        'value' => '50.00'
                    ),
                    'description' => 'Test Course Purchase'
                )
            ),
            'payer' => array(
                'name' => array(
                    'given_name' => 'Test',
                    'surname' => 'User'
                ),
                'email_address' => 'test@example.com',
                'payer_id' => 'TEST_PAYER_' . uniqid()
            ),
            'create_time' => date( 'c' ),
            'update_time' => date( 'c' ),
            'links' => array(
                array(
                    'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/' . $order_id,
                    'rel' => 'self',
                    'method' => 'GET'
                )
            )
        );
    }

    /**
     * Validate order data before sending to PayPal
     * 
     * @param array $order_data Order data to validate
     * @return true|WP_Error True if valid, error if not
     */
    private function validate_order_data( $order_data ) {
        
        $required_fields = array( 'amount', 'currency', 'description', 'course_id', 'payee_email' );
        
        foreach( $required_fields as $field ) {
            if( empty( $order_data[ $field ] ) ) {
                return new WP_Error( 'missing_field', "Required field '{$field}' is missing" );
            }
        }

        // Validate amount
        if( ! is_numeric( $order_data['amount'] ) || $order_data['amount'] <= 0 ) {
            return new WP_Error( 'invalid_amount', 'Amount must be a positive number' );
        }

        // Validate currency code
        if( strlen( $order_data['currency'] ) !== 3 ) {
            return new WP_Error( 'invalid_currency', 'Currency must be a 3-letter code' );
        }

        // Validate email
        if( ! is_email( $order_data['payee_email'] ) ) {
            return new WP_Error( 'invalid_email', 'Payee email is not valid' );
        }

        return true;
    }

    /**
     * Process API response
     * 
     * @param array|WP_Error $response WordPress HTTP response
     * @param string $operation Operation name for logging
     * @return array|WP_Error Processed response or error
     */
    private function process_api_response( $response, $operation ) {
        
        if( is_wp_error( $response ) ) {
            error_log( "PayPal API Error ({$operation}): " . $response->get_error_message() );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        // Log the response for debugging
        error_log( "PayPal API Response ({$operation}): Code {$response_code}, Body: " . $response_body );

        if( $response_code < 200 || $response_code >= 300 ) {
            return new WP_Error( 'api_error', "PayPal API error: HTTP {$response_code} - {$response_body}" );
        }

        $data = json_decode( $response_body, true );

        if( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', 'Invalid JSON response from PayPal' );
        }

        return $data;
    }

    /**
     * Generate unique request ID for PayPal API calls
     * 
     * @return string Unique request ID
     */
    private function generate_request_id() {
        return 'EXMS-' . time() . '-' . wp_generate_password( 8, false );
    }

    /**
     * Verify payment completion
     * 
     * @param array $capture_response Capture response from PayPal
     * @param array $expected_data Expected payment data (amount, currency, etc.)
     * @return array Verification result
     */
    public function verify_payment( $capture_response, $expected_data ) {
        
        $verification = array(
            'success' => false,
            'errors' => array(),
            'payment_data' => array()
        );

        // Check if capture was successful
        if( ! isset( $capture_response['status'] ) || $capture_response['status'] !== 'COMPLETED' ) {
            $verification['errors'][] = 'Payment status is not COMPLETED';
            return $verification;
        }

        // Extract payment data
        if( ! isset( $capture_response['purchase_units'][0]['payments']['captures'][0] ) ) {
            $verification['errors'][] = 'No capture data found in response';
            return $verification;
        }

        $capture_data = $capture_response['purchase_units'][0]['payments']['captures'][0];

        // Verify payment status
        if( ! isset( $capture_data['status'] ) || $capture_data['status'] !== 'COMPLETED' ) {
            $verification['errors'][] = 'Capture status is not COMPLETED';
            return $verification;
        }

        // Extract and verify amount
        if( ! isset( $capture_data['amount']['value'] ) ) {
            $verification['errors'][] = 'No amount found in capture data';
            return $verification;
        }

        $paid_amount = floatval( $capture_data['amount']['value'] );
        $expected_amount = floatval( $expected_data['amount'] );

        if( abs( $paid_amount - $expected_amount ) > 0.01 ) {
            $verification['errors'][] = "Amount mismatch: expected {$expected_amount}, got {$paid_amount}";
            return $verification;
        }

        // Verify currency
        if( ! isset( $capture_data['amount']['currency_code'] ) ) {
            $verification['errors'][] = 'No currency found in capture data';
            return $verification;
        }

        if( $capture_data['amount']['currency_code'] !== $expected_data['currency'] ) {
            $verification['errors'][] = "Currency mismatch: expected {$expected_data['currency']}, got {$capture_data['amount']['currency_code']}";
            return $verification;
        }

        // If we get here, verification passed
        $verification['success'] = true;
        $verification['payment_data'] = array(
            'transaction_id' => $capture_data['id'],
            'amount' => $paid_amount,
            'currency' => $capture_data['amount']['currency_code'],
            'status' => $capture_data['status'],
            'payer_email' => isset( $capture_response['payer']['email_address'] ) ? $capture_response['payer']['email_address'] : '',
            'payer_name' => $this->extract_payer_name( $capture_response ),
            'order_id' => $capture_response['id']
        );

        return $verification;
    }

    /**
     * Extract payer name from PayPal response
     * 
     * @param array $response PayPal response
     * @return string Payer name
     */
    private function extract_payer_name( $response ) {
        
        if( isset( $response['payer']['name']['given_name'] ) && isset( $response['payer']['name']['surname'] ) ) {
            return trim( $response['payer']['name']['given_name'] . ' ' . $response['payer']['name']['surname'] );
        }

        if( isset( $response['payer']['name']['given_name'] ) ) {
            return $response['payer']['name']['given_name'];
        }

        return '';
    }
}
