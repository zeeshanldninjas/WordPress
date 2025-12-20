<?php

/**
 * Template for Setup Payments functions
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Payment_Func {

    /**
     * @var self
     */
    private static $instance;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Payment_Func ) ) {

            self::$instance = new EXMS_Payment_Func;

            global $wpdb;
            self::$wpdb = $wpdb;

        }

        return self::$instance;
    }

    /**
     * Get payment transaction table name
     */
    public static function exms_payment_transaction_table() {

        return self::$wpdb->prefix.'exms_payment_transaction';
    }

    /**
     * Insert data into payment transaction table
     * 
     * @param $order_data
     * @param $user_id
     * @param $product_id
     * @param $price
     */
    public static function exms_insert_into_payment_transaction( $order_data, $user_id, $product_id, $price ) {

        $table_name = self::exms_payment_transaction_table();

        $order_id = isset( $order_data['id'] ) ? $order_data['id'] : '';
        $status = isset( $order_data['status'] ) ? $order_data['status'] : '';
        $receiver_email = isset( $order_data['purchase_units'][0]['payee']['email_address'] ) ? $order_data['purchase_units'][0]['payee']['email_address'] : '';
        $payer_email = isset( $order_data['payer']['email_address'] ) ? $order_data['payer']['email_address'] : '';
        $create_time = isset( $order_data['create_time'] ) ? $order_data['create_time'] : '';

        self::$wpdb->insert( $table_name, [
            'order_id'      => $order_id,
            'user_id'       => $user_id,
            'product_id'    => $product_id,
            'price'         => $price,
            'receiver'      => $receiver_email,
            'payer'         => $payer_email,
            'status'        => $status,
            'create_time'   => $create_time
        ] );
    }
}

EXMS_Payment_Func::instance();