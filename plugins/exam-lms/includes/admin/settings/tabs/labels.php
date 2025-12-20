<?php
/**
 * Manage Email settings
 */
if( ! defined( 'ABSPATH' ) ) exit;

class EXMS_Labels {

	/**
	 * @var self
	 */
	private static $instance;
	private $label_page = false;

	/**
	 * Instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS_Labels ) ) {
			self::$instance = new EXMS_Labels;

			if ( isset( $_GET['page'], $_GET['tab'] ) && $_GET['page'] === 'exms-settings' && $_GET['tab'] === 'labels' ) {
                self::$instance->label_page = true;
            }
			self::$instance->hooks();
		}

		return self::$instance;
	}

	private function hooks() {

		$this->exms_labels_content_html();
	}

	public function exms_labels_content_html() {

		if ( !self::$instance->label_page ) {
			return false;
		}
		
		if( file_exists( EXMS_TEMPLATES_DIR . '/tabs/exms-labels-settings-template.php' ) ) {
                    
			require_once EXMS_TEMPLATES_DIR . '/tabs/exms-labels-settings-template.php';
		}

	}
}

EXMS_Labels::instance();