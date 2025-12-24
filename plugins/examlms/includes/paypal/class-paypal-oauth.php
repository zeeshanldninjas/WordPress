<?php
/**
 * PayPal OAuth Token Management
 * 
 * Handles OAuth token generation, caching, and refresh for PayPal REST API
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_PayPal_OAuth {

    private static $instance;
    private $client_id;
    private $client_secret;
    private $sandbox_mode;
    private $base_url;

    /**
     * Create class instance
     */
    public static function instance() {
        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_PayPal_OAuth ) ) {
            self::$instance = new EXMS_PayPal_OAuth();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_settings();
    }

    /**
     * Load PayPal settings
     */
    private function load_settings() {
        $payment_settings = get_option( 'exms_payment_settings', array() );
        
        $this->client_id = isset( $payment_settings['paypal_client_id'] ) ? trim( $payment_settings['paypal_client_id'] ) : '';
        $this->client_secret = isset( $payment_settings['paypal_client_secret'] ) ? trim( $payment_settings['paypal_client_secret'] ) : '';
        $this->sandbox_mode = isset( $payment_settings['paypal_sandbox'] ) && $payment_settings['paypal_sandbox'] === 'on';
        
        // Set base URL based on sandbox mode
        $this->base_url = $this->sandbox_mode 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
            
        // Debug logging
        error_log( 'PayPal Settings Debug:' );
        error_log( '- Client ID: ' . ( ! empty( $this->client_id ) ? 'SET (' . strlen( $this->client_id ) . ' chars)' : 'EMPTY' ) );
        error_log( '- Client Secret: ' . ( ! empty( $this->client_secret ) ? 'SET (' . strlen( $this->client_secret ) . ' chars)' : 'EMPTY' ) );
        error_log( '- Sandbox Mode: ' . ( $this->sandbox_mode ? 'YES' : 'NO' ) );
        error_log( '- Base URL: ' . $this->base_url );
    }

    /**
     * Get OAuth access token
     * 
     * @return string|WP_Error Access token or error
     */
    public function get_access_token() {
        
        // Check if we have valid credentials
        if( empty( $this->client_id ) || empty( $this->client_secret ) ) {
            return new WP_Error( 'missing_credentials', 'PayPal Client ID and Secret are required' );
        }

        // Check for cached token first
        $cached_token = $this->get_cached_token();
        if( $cached_token && ! $this->is_token_expired( $cached_token ) ) {
            return $cached_token['access_token'];
        }

        // Generate new token
        return $this->generate_new_token();
    }

    /**
     * Generate new OAuth token from PayPal
     * 
     * @return string|WP_Error Access token or error
     */
    private function generate_new_token() {
        
        $url = $this->base_url . '/v1/oauth2/token';
        
        $headers = array(
            'Accept' => 'application/json',
            'Accept-Language' => 'en_US',
            'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        $body = 'grant_type=client_credentials';

        $response = wp_remote_post( $url, array(
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30
        ) );

        if( is_wp_error( $response ) ) {
            error_log( 'PayPal OAuth Error: ' . $response->get_error_message() );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );

        if( $response_code !== 200 ) {
            error_log( 'PayPal OAuth HTTP Error: ' . $response_code . ' - ' . $response_body );
            return new WP_Error( 'oauth_failed', 'Failed to get OAuth token: ' . $response_body );
        }

        $token_data = json_decode( $response_body, true );

        if( ! isset( $token_data['access_token'] ) ) {
            error_log( 'PayPal OAuth Invalid Response: ' . $response_body );
            return new WP_Error( 'invalid_response', 'Invalid OAuth response from PayPal' );
        }

        // Cache the token
        $this->cache_token( $token_data );

        return $token_data['access_token'];
    }

    /**
     * Get cached token
     * 
     * @return array|false Cached token data or false
     */
    private function get_cached_token() {
        return get_transient( 'exms_paypal_oauth_token' );
    }

    /**
     * Cache OAuth token
     * 
     * @param array $token_data Token data from PayPal
     */
    private function cache_token( $token_data ) {
        
        // Cache for slightly less than the actual expiry time to be safe
        $expires_in = isset( $token_data['expires_in'] ) ? intval( $token_data['expires_in'] ) : 3600;
        $cache_duration = max( 300, $expires_in - 300 ); // At least 5 minutes, but 5 minutes less than actual expiry

        $cache_data = array(
            'access_token' => $token_data['access_token'],
            'token_type' => isset( $token_data['token_type'] ) ? $token_data['token_type'] : 'Bearer',
            'expires_at' => time() + $expires_in,
            'scope' => isset( $token_data['scope'] ) ? $token_data['scope'] : ''
        );

        set_transient( 'exms_paypal_oauth_token', $cache_data, $cache_duration );
    }

    /**
     * Check if cached token is expired
     * 
     * @param array $token_data Cached token data
     * @return bool True if expired
     */
    private function is_token_expired( $token_data ) {
        if( ! isset( $token_data['expires_at'] ) ) {
            return true;
        }
        
        // Consider expired if less than 5 minutes remaining
        return ( $token_data['expires_at'] - time() ) < 300;
    }

    /**
     * Clear cached token (useful for testing or error recovery)
     */
    public function clear_cached_token() {
        delete_transient( 'exms_paypal_oauth_token' );
    }

    /**
     * Get authorization header for API calls
     * 
     * @return string|WP_Error Authorization header or error
     */
    public function get_auth_header() {
        $access_token = $this->get_access_token();
        
        if( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        return 'Bearer ' . $access_token;
    }

    /**
     * Check if PayPal is properly configured
     * 
     * @return bool True if configured
     */
    public function is_configured() {
        return ! empty( $this->client_id ) && ! empty( $this->client_secret );
    }

    /**
     * Get PayPal base URL
     * 
     * @return string Base URL for API calls
     */
    public function get_base_url() {
        return $this->base_url;
    }

    /**
     * Test OAuth connection
     * 
     * @return array Test result with success status and message
     */
    public function test_connection() {
        
        if( ! $this->is_configured() ) {
            return array(
                'success' => false,
                'message' => 'PayPal Client ID and Secret are not configured'
            );
        }

        $access_token = $this->get_access_token();
        
        if( is_wp_error( $access_token ) ) {
            return array(
                'success' => false,
                'message' => 'OAuth failed: ' . $access_token->get_error_message()
            );
        }

        return array(
            'success' => true,
            'message' => 'PayPal OAuth connection successful',
            'sandbox_mode' => $this->sandbox_mode
        );
    }
}
