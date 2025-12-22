<?php
/**
 * Plugin Name: WP Exams
 * Version: 1.0
 * Description: WP Exams allows you to create easy exam quizzes. 
 * Author: ldninjas
 * Author URI: http://ldninjas.com/
 * Plugin URI: http://wpexams.com/
 * Text Domain: exms
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add plugin activate hook 
 */
register_activation_hook( __FILE__, [ 'EXMS' , 'exms_activate' ] );

/** 
 * class EXMS
 *
 * Main class to initiate the plugin
 */
class EXMS {

	const VERSION = 9992;//'1.0';

	/**
     * @var self
     */
	private static $instance;

	public static function instance() {

		if( is_null( self::$instance ) && ! ( self::$instance instanceof EXMS ) ) {

			self::$instance = new EXMS;
			self::$instance->constants();
			self::$instance->includes();
		}

		return self::$instance;
	}

	/**
	 * Define constants
	 */
	private function constants() {
        
		/**
		 * Directory
		 */ 
        define( 'EXMS_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'EXMS_DIR_FILE', EXMS_DIR . basename ( __FILE__ ) );
        define( 'EXMS_INCLUDES_DIR', trailingslashit ( EXMS_DIR . 'includes' ) );
        define( 'EXMS_BLOCKS_DIR', trailingslashit ( EXMS_DIR . 'blocks' ) );
        define( 'EXMS_BLOCKS_SRC_DIR', trailingslashit ( EXMS_BLOCKS_DIR . 'src' ) );
        define( 'EXMS_TEMPLATES_DIR', trailingslashit ( EXMS_DIR . 'templates' ) );
        define( 'EXMS_THEME_TEMPLATES_DIR', trailingslashit ( get_template_directory() . '/exms-templates' ) );
        define( 'EXMS_BASE_DIR', plugin_basename(__FILE__) );
        define( 'EXMS_VERSION', self::VERSION );
        
        /**
		 * URLS
		 */ 
        define( 'EXMS_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'EXMS_ASSETS_URL', trailingslashit ( EXMS_URL . 'assets' ) );
        define( 'EXMS_DIR_URL', admin_url() );
		define( 'EXMS_UPLOADS', WP_CONTENT_URL . '/uploads/wp-exams/' );

        /**
		 * Text Domain
		 */  
        define( 'WP_EXAMS', 'exms' );
	}

	/**
	 * Include required files
	 */
	private function includes() {

        require_once EXMS_INCLUDES_DIR . 'exms-post-types-functions.php';
        require_once EXMS_INCLUDES_DIR . 'admin/exms-admin-menus.php';
        require_once EXMS_INCLUDES_DIR . 'admin/exms-post-types-class.php';
        require_once EXMS_INCLUDES_DIR . 'admin/exms-admin-hooks.php';
        require_once EXMS_INCLUDES_DIR . 'exms-setup-wizard/exms-setup-wizard.php';
        require_once EXMS_INCLUDES_DIR . 'exms-question-functions.php';
        require_once EXMS_INCLUDES_DIR . 'exms-core-functions.php';
        require_once EXMS_INCLUDES_DIR . 'exms-common.php';

        $files = [
            EXMS_INCLUDES_DIR . 'classes/exms-student.php',
            EXMS_INCLUDES_DIR . 'db/db.main.php',
            EXMS_INCLUDES_DIR . 'db/models/class.uploads.php',
            EXMS_INCLUDES_DIR . 'db/models/class.payment_transaction.php',
            EXMS_INCLUDES_DIR . 'db/models/class.post_completions.php',
            EXMS_INCLUDES_DIR . 'db/models/class.post_relationship.php',
            EXMS_INCLUDES_DIR . 'db/models/class.questions_results.php',
            EXMS_INCLUDES_DIR . 'db/models/class.quizzes_results.php',
            EXMS_INCLUDES_DIR . 'db/models/class.user_post_relations.php',
            EXMS_BLOCKS_DIR . 'src/blocks.php',
            
            EXMS_INCLUDES_DIR . 'exms-setup-wizard/exms-setup-functions.php',
            EXMS_INCLUDES_DIR . 'post-type-structures/exms-post-relations.php',
            EXMS_INCLUDES_DIR . 'exms-post-relations/exms-save-relations.php',
            EXMS_INCLUDES_DIR . 'exms-post-relations/exms-pr-functions.php',
            EXMS_INCLUDES_DIR . 'exms-post-relations/exms-post-frontend.php',
            EXMS_INCLUDES_DIR . 'exms-post-relations/exms-post-paginations.php',
            EXMS_INCLUDES_DIR . 'exms-post-relations/exms-basic-functions.php',
            EXMS_INCLUDES_DIR . 'exms-settings-functions.php',
            EXMS_INCLUDES_DIR . 'exms-quiz-functions.php',
            EXMS_INCLUDES_DIR . 'exms-certificate-functions.php',
            EXMS_INCLUDES_DIR . 'exms-point-type-functions.php',
            EXMS_INCLUDES_DIR . 'exms-db-functions.php',
            // EXMS_INCLUDES_DIR . 'exms-core-functions.php',
            EXMS_INCLUDES_DIR . 'user-functions.php',
            EXMS_INCLUDES_DIR . 'exms-paypal-functions.php',
            EXMS_INCLUDES_DIR . 'paypal/class-paypal-oauth.php',
            EXMS_INCLUDES_DIR . 'paypal/class-paypal-api.php',
            EXMS_INCLUDES_DIR . 'paypal/class-paypal-rest-endpoints.php',
            EXMS_INCLUDES_DIR . 'exms-stripe-functions.php',
            EXMS_INCLUDES_DIR . 'exms-shortcodes.php',
            EXMS_TEMPLATES_DIR . 'template-hooks.php',
            EXMS_INCLUDES_DIR . 'frontend/exms-quiz.php',
            EXMS_INCLUDES_DIR . 'frontend/exms-template-override.php',
            EXMS_INCLUDES_DIR . 'frontend/exms-quiz-review.php',
            EXMS_INCLUDES_DIR . 'frontend/course/course-listing.php',
            EXMS_INCLUDES_DIR . 'exms-course-listing-functions.php',
            EXMS_INCLUDES_DIR . 'exms-course-overview-functions.php',
            EXMS_INCLUDES_DIR . 'frontend/course/course-page.php',
            EXMS_INCLUDES_DIR . 'frontend/lesson/lesson-overview.php',
            EXMS_INCLUDES_DIR . 'exms-lesson-overview-functions.php',
        ];
        
        $admin_files = [
            EXMS_INCLUDES_DIR . 'admin/assignment-selector/exms-selector.php',
            EXMS_INCLUDES_DIR . 'admin/groups/exms-groups-class.php',
            EXMS_INCLUDES_DIR . 'admin/settings/exms-settings-class.php',
            EXMS_INCLUDES_DIR . 'admin/reports/reports-class.php',
            EXMS_INCLUDES_DIR . 'admin/report/reports.php',
            EXMS_INCLUDES_DIR . 'admin/quiz/exms-quiz-class.php',
            EXMS_INCLUDES_DIR . 'admin/questions/exms-questions-class.php',
            EXMS_INCLUDES_DIR . 'admin/certificates/exms-certificate.php',
            EXMS_INCLUDES_DIR . 'admin/certificates/exms-certificates.php',
            EXMS_INCLUDES_DIR . 'admin/point-types/exms-point-types.php',
            EXMS_INCLUDES_DIR . 'admin/submitted-essays/exms-submitted-essays-class.php',
            EXMS_INCLUDES_DIR . 'admin/import-export/exms-import-export-class.php',
            EXMS_INCLUDES_DIR . 'admin/edit-profile/exms-edit-profile.php',
            EXMS_INCLUDES_DIR . 'admin/edit-profile/exms-progress-detail.php',
            EXMS_INCLUDES_DIR . 'admin/shortcodes/exms-shortcodes.php',
            EXMS_INCLUDES_DIR . 'admin/exms-enqueue-scripts-styles.php',
            EXMS_INCLUDES_DIR . 'admin/exms-emails.php',
            EXMS_INCLUDES_DIR . 'admin/transactions/exms-transaction.php',
            EXMS_INCLUDES_DIR . 'setup-payments/setup-payments.php',
            EXMS_INCLUDES_DIR . 'setup-payments/payment-functions.php'
        ];

        /**
         * Include files
         */
        foreach( $files as $file ) {
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }

        /**
         * Include admin files only if in admin panel
         */
        if( is_admin() ) {
            foreach ( $admin_files as $file ) {
                if ( file_exists( $file ) ) {
                    require_once $file;
                }
            }
        }
	}

	/**
	 * Perform plugin activate actions
	 */
	public static function exms_activate() {

		/**
		 * Add plugin activate class
		 */
		if( file_exists( plugin_dir_path ( __FILE__ ) . '/exms-activate.php' ) ) {

			require_once plugin_dir_path ( __FILE__ ) . '/exms-activate.php';
		}
	}
}
/**
 * Initialize class
 */
EXMS::instance();
