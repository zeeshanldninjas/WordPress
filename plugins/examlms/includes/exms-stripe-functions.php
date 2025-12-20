<?php

use \Stripe\Stripe;
use \Stripe\Customer;
use \Stripe\ApiOperations\Create;
use \Stripe\Charge;

require_once __DIR__ . '/lib/stripe-php/init.php';
class StripePayment
{
    private $apiKey;
    private $stripeService;

    public function __construct()
    {
        global $wpdb;
        $exms_options = get_option( 'exms_settings' );

        // $this->apiKey = 'sk_test_51IpdyWAiLaIEgYsThGMb25PIGS7sX9EsRgEngSjdPB9nXbPWTuAVGkL369Tscc0lo04cfFUYB7DsOWhuhNXoq7zk00zj5U6Hht';
        if( isset( $exms_options ) && is_array( $exms_options ) && array_key_exists( 'stripe_client_secret', $exms_options ) ) {
            $this->apiKey = $exms_options['stripe_client_secret'];
            $this->stripeService = new \Stripe\Stripe();
            $this->stripeService->setVerifySslCerts(false);
            $this->stripeService->setApiKey($this->apiKey);
            
            $user_id = get_current_user_id();
            $post_array = $_POST;
            
            if ( ! empty( $post_array["token"]) ) {
                $array = [];
                $array['name']          = $post_array['name'];
                $array['email']         = $post_array['email'];
                $array['card-number']   = $post_array['card-number'];
                $array['month']         = $post_array['month'];
                $array['year']          = $post_array['year'];
                $array['cvc']           = $post_array['cvc'];
                $array['token']         = $post_array['token'];
                $array['item_number']   = $post_array['item_number'];
                $array['item_name']     = get_the_title($array['item_number']);
                $array['currency_code'] = ! empty( $exms_options['stripe_currency'] ) ? $exms_options['stripe_currency'] : 'USD';
                $array['amount']        = $post_array['amount'];
                $stripeResponse = $this->chargeAmountFromCard($array);
                
                $amount = $stripeResponse["amount"] /100;
                
                if ($stripeResponse['amount_refunded'] == 0 && empty($stripeResponse['failure_code']) && $stripeResponse['paid'] == 1 && $stripeResponse['captured'] == 1 && $stripeResponse['status'] == 'succeeded') {
                     
                    $table_name = $wpdb->prefix.'exms_payment_transaction';;
    
                    $wpdb->insert( $table_name, [
                        'order_id'      => $stripeResponse["id"],
                        'user_id'       => $user_id,
                        'product_id'    => $array['item_number'],
                        'price'         => $amount,
                        'receiver'      => $stripeResponse["receipt_email"],
                        'payer'         => $array['email'],
                        'status'        => $stripeResponse['status'],
                        'create_time'   => $stripeResponse['created']
                    ] );
    
                    exms_enroll_user_on_post( $user_id, $array['item_number'], time() );
    
                    header( 'location: '.(! empty( $exms_options['complete_url'] ) ? $exms_options['complete_url'] : get_permalink($array['item_number'])) ); exit;
                } else {
                    header( 'location: '.$exms_options['cancel_url'] ); exit;
                }
            }
        }
    }

    public function addCustomer($customerDetailsAry)
    {
        
        $customer = new Customer();
        
        $customerDetails = $customer->create( $customerDetailsAry );
        
        return $customerDetails;
    }

    public function chargeAmountFromCard($cardDetails)
    {
        $customerDetailsAry = array(
            'email' => $cardDetails['email'],
            'source' => $cardDetails['token']
        );

        $customerResult = $this->addCustomer($customerDetailsAry);
        $charge = new Charge();
        $cardDetailsAry = array(
            'customer' => $customerResult->id,
            'amount' => $cardDetails['amount']*100,
            'currency' => $cardDetails['currency_code'],
            'description' => $cardDetails['item_name'],
            'metadata' => array(
                'order_id' => $cardDetails['item_number']
            )
        );
        
        $result = $charge->create($cardDetailsAry);

        return $result->jsonSerialize();
    }
}


add_action( 'init', function() {
    new StripePayment();
});