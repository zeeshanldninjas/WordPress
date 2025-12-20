<?php
/**
 * Paypal functions 
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates a new paypal access token
 * @param String 	$client_id 		Paypal client id
 * @param String 	$client_secret 	Paypal client secret
 */
function exms_create_new_access_token( $client_id, $client_secret ) {
	
	$curl = curl_init();
	$paypal_settings = exms_get_paypal_settings() ? exms_get_paypal_settings() : null;
	$paypal_at_url = isset( $paypal_settings->transaction_mode ) && $paypal_settings->transaction_mode == 'live' ? 'https://api.paypal.com/v1/oauth2/token' : 'https://api.sandbox.paypal.com/v1/oauth2/token';

	curl_setopt_array( $curl, array(
		CURLOPT_URL => $paypal_at_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => "grant_type=client_credentials",
		CURLOPT_HTTPHEADER => array(
				'accept: application/json',
				'accept-language: en_US',
				'authorization: basic ' . base64_encode( $client_id . ':' . $client_secret ),
				'content-type: application/x-www-form-urlencoded'
			),
		));

		$response = curl_exec( $curl );
		$err = curl_error( $curl );
		curl_close( $curl );

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
		
		$resp = json_decode( $response );

		if( isset( $resp->access_token ) ) {
			return $resp->access_token;
		}
	}
}