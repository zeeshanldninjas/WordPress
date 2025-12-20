<?php

/**
 * Template for Setup Payments
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Setup_Payments {

    /**
     * @var self
     */
    private static $instance;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Setup_Payments ) ) {

            self::$instance = new EXMS_Setup_Payments;

            global $wpdb;
            self::$wpdb = $wpdb;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'wp_ajax_exms_save_paypal_transactions', [ $this, 'exms_save_paypal_transactions' ] );   
    }

    /**
     * Save Paypal Payment Transactions
     */
    public function exms_save_paypal_transactions() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $product_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if( empty( $product_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Product ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        if( empty( $user_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'User ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $price = isset( $_POST['price'] ) ? intval( $_POST['price'] ) : 0;
        if( empty( $price ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Price Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $order_data = isset( $_POST['order_data'] ) ? $_POST['order_data'] : '';
        if( empty( $order_data ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Orders Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        $quiz_type = isset( $_POST['quiz_type'] ) ? $_POST['quiz_type'] : '';
        $sub_days = isset( $_POST['sub_days'] ) ? intval( $_POST['sub_days'] ) : '';

        /* Added data on transaction table */
        echo EXMS_Payment_Func::exms_insert_into_payment_transaction( $order_data, $user_id, $product_id, $price );
        /* Added data on transaction table */

        /* Assign user to the quiz */
        echo exms_enroll_user_on_post( $user_id, $product_id );
        /* Assign user to the quiz */

        if( 'subscribe' == $quiz_type ) {

            $after_days = $sub_days * ( 3600 * 24 );
            $execute_time = time() + $after_days;

            $args = [ 
                'user_id'    => $user_id,
                'post_id'    => $product_id,
                'unique_id'  => rand( 1454, 8712 )
            ];
            
            wp_schedule_single_event( $execute_time, 'exms_remove_user_subscription', [ $args ] );
        }

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }
}

EXMS_Setup_Payments::instance();

/**
 * Remove user quiz subscription after quiz 
 * after quiz subscription is expired
 * 
 * @param $args
 */
add_action( 'exms_remove_user_subscription', 'exms_remove_user_quiz_subscription' );
function exms_remove_user_quiz_subscription( $args ) {

    $user_id = $args['user_id'] ? $args['user_id'] : 0;
    $post_id = $args['post_id'] ? $args['post_id'] : 0;

    echo exms_unenroll_user_on_post( $user_id, $post_id );
}   