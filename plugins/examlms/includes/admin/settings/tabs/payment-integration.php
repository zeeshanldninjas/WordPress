<?php

/**
 * WP EXAMS - Setting Paypal integration tab
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Payment_Integration {

	/**
	 * @var self
	 */
	private static $instance;
	private $payment_integration_page = false;

	/**
	 * Instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Payment_Integration ) ) {
			self::$instance = new EXMS_Payment_Integration;

			if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'payment-integration' ) {
                self::$instance->payment_integration_page = true;
            }
			self::$instance->hooks();
		}

		return self::$instance;
	}

	private function hooks() {

		$this->exms_payment_integration_content_html();
	}

	/**
	 * Calling Html template
	 * @return bool
	 */
	public function exms_payment_integration_content_html() {

		if ( !self::$instance->payment_integration_page ) {
			return false;
		}
		
		$payment_modes = exms_get_enabled_payment_modes() ? exms_get_enabled_payment_modes() : ( object ) [];

		$tabs = [ 'exms_paypal' => 'Paypal', 'exms_stripe' => 'Stripe' ];
		exms_create_submenu_tabs( $tabs, false );

		/**
		 * Include stripe settings to payment integration tab
		 */
		if( isset( $_GET['tab_type'] ) && $_GET['tab_type'] == 'exms_stripe_payment-integration' ) {

			if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-stripe-settings.php' ) ) {
				require_once EXMS_TEMPLATES_DIR . '/tabs/exms-stripe-settings.php';	
			}
		}

		/**
		 * Include paypal settings to payment integration tab
		 */
		if( ( isset( $_GET['tab_type'] ) ) 
			&& $_GET['tab_type'] == 'exms_paypal_payment-integration' 
			|| ! isset( $_GET['tab_type'] ) && $_GET['tab'] == 'payment-integration' ) {

			if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-paypal-settings.php' ) ) {
				require_once EXMS_TEMPLATES_DIR . '/tabs/exms-paypal-settings.php';	
			}
		}

	}
}

EXMS_Payment_Integration::instance();