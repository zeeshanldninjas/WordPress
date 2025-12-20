<?php

/**
 * Template for Transactions
 */

if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Transactions extends EXMS_DB_Main {

    /**
     * @var self
     */
    private static $instance;
    private $transaction_page = false;
	private $table_check = false;
    
    /**
     * Connect to wpdb
     */
    private static $wpdb;

    public static function instance() {

        if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Transactions ) ) {

            self::$instance = new EXMS_Transactions;

            global $wpdb;
            self::$wpdb = $wpdb;
            if( isset( $_GET['page'] ) && $_GET['page'] === 'exms-transaction' ) {
                self::$instance->transaction_page = true;
            }

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Define hooks
     */
    private function hooks() {

        add_action( 'admin_notices', [ $this, 'exms_show_missing_table_notice' ] );
        add_action( 'admin_enqueue_scripts' , [ $this,'exms_transaction_enqueue'] );
        add_action( 'wp_ajax_create_exms_transaction_table', [ $this , 'create_exms_transaction_table' ] );
        add_action( 'wp_ajax_exms_delete_single_transaction', [ $this, 'exms_delete_single_transaction' ] );
        add_action( 'wp_ajax_exms_delete_multiple_transactions', [ $this, 'exms_delete_multiple_transactions' ] );
    }

    /**
     * Showing table notification on top of the page
     * @param mixed $post
     * @return bool
     */
    public function exms_show_missing_table_notice( $post ) {

        if ( !$this->transaction_page ) {
            return false;
        }

        $table_exists = $this->csm_validate_table();
            if( empty( $table_exists ) ) {
                self::$instance->table_check = true;
            }

            if( !self::$instance->table_check ) {
                $ajax_action = 'create_exms_transaction_table';
                $table_names = $table_exists;
                require_once EXMS_TEMPLATES_DIR . 'exms-table-exist.php';
            }
    }

    /**
     * Transaction Functionality Files added and nonce creation
     */
    public function exms_transaction_enqueue() {
        
        if ( !$this->transaction_page ) {
            return false;
        }
        
        wp_enqueue_script( 'exms-transaction-js', EXMS_ASSETS_URL . '/js/admin/transaction/exms-transactions.js', [ 'jquery' ], false, true );
        wp_localize_script( 'exms-transaction-js', 'EXMS_TRANSACTION', 
            [ 
                'ajaxURL'                           => admin_url( 'admin-ajax.php' ),
                'security'                          => wp_create_nonce( 'exms_ajax_nonce' ) ,
                'create_table_nonce'                => wp_create_nonce( 'create_transaction_tables_nonce' ),
                'confirmation_text'                 => __( 'Make sure to take db back first before doing the process.', 'exms' ),
                'processing'                        => __( 'processing...', 'exms' ),
                'create_table'                      => __( 'Create tables', 'exms' ),
                'error_text'                        => __( 'Error', 'exms' ),
            ] 
        );
    }

    /**
     * If table not exist will pass in the array
     */
    public function csm_validate_table() {
        
        global $wpdb;
        
		$user_post_relations_table_name = $wpdb->prefix.'exms_payment_transaction';
    
        $not_exist_tables = [];

        if ( !$this->exms_table_exists( $user_post_relations_table_name ) ) {
            $not_exist_tables[] = 'payment_transaction';
        }
    
        return $not_exist_tables;
    }

    /**
     * Create quiz tables
     */
    public function create_exms_transaction_table() {

        check_ajax_referer( 'create_transaction_tables_nonce', 'nonce' );

        if ( isset( $_POST['tables'] ) && !empty( $_POST['tables'] ) ) {
            
            $table_names = json_decode( stripslashes( $_POST['tables'] ), true );
    
            if ( is_array( $table_names ) ) {
                foreach ( $table_names as $table_name ) {
                    switch ( $table_name ) {
                        case 'payment_transaction':
                            if ( !class_exists( 'EXMS_DB_Payment_transation' ) ) {
                                require_once EXMS_INCLUDES_DIR . 'db/models/class.payment_transaction.php';     
                            }
                            $employee = new EXMS_DB_Payment_transation();
                            $employee->run_table_create_script();
                            break;

                        default:
                            wp_send_json_error( [ 'message' => sprintf(__( 'Unknown table: %s', 'exms' ), esc_html( $table_name ) ) ] );
                            return;
                    }
                }
                
                wp_send_json_success(__( 'Tables created successfully.', 'exms' ) );
            } else {
                wp_send_json_error( [ 'message' => __( 'Invalid table names format.', 'exms' ) ] );
            }
        } else {
            wp_send_json_error( [ 'message' => __( 'No table names provided.', 'exms') ] );
        }
    
        wp_die();
    }

    /**
     * 
     */
    public function exms_delete_multiple_transactions() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $row_ids = isset( $_POST['row_ids'] ) ? $_POST['row_ids'] : 0;
        if( empty( $row_ids ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Please select rows to perform this action.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }

        foreach( $row_ids as $row_id ) {
            $params = [];
            $params[] = [ 'field' => 'id', 'value' => $row_id, 'operator' => '=', 'type' => '%d'];
            wp_exams()->db->exms_db_query( 'delete', 'exms_payment_transaction', $params );
        }
        
        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Delete single transaction : AJax
     */
    public function exms_delete_single_transaction() {

        check_ajax_referer( 'exms_ajax_nonce', 'security' );

        $response = [];

        $row_id = isset( $_POST['row_id'] ) ? intval( $_POST['row_id'] ) : 0;
        if( empty( $row_id ) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Row ID Not found.', 'exms' );
            echo json_encode( $response );
            wp_die();
        }
        $params = [];
        $params[] = [ 'field' => 'id', 'value' => $row_id, 'operator' => '=', 'type' => '%d'];
        wp_exams()->dbpmttran->exms_db_query( 'delete', 'payment_transaction', $params );

        $response['status'] = 'true';
        echo json_encode( $response );
        wp_die();
    }

    /**
     * Transaction menu page content html
     */
    public static function exms_transaction_page_html() {

        if ( !self::$instance->transaction_page ) {
            return false;
        }

        if( file_exists( EXMS_DIR . 'includes/admin/transactions/exms-transaction-table.php' ) ) {
            require_once EXMS_DIR . 'includes/admin/transactions/exms-transaction-table.php';
        }
    }
}

EXMS_Transactions::instance();