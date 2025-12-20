<?php

/**
 * WP EXAMS - General Setting tab content
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class EXMS_General {

	/**
	 * @var self
	 */
	private static $instance;
	private $general_page = false;

	/**
	 * Instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_General ) ) {
			self::$instance = new EXMS_General;

			if ( isset( $_GET['page'] ) && $_GET['page'] === 'exms-settings' ) {
				self::$instance->general_page = true;
			}
			self::$instance->hooks();
		}

		return self::$instance;
	}

	private function hooks() {

		$this->exms_general_content_html();
	}

	public function exms_general_content_html() {

		if ( !self::$instance->general_page ) {
			return false;
		}
		
		if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-general-settings-template.php' ) ) {
                    
			require_once EXMS_TEMPLATES_DIR . '/tabs/exms-general-settings-template.php';
		}

	}
}

EXMS_General::instance();
