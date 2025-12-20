<?php
/**
 * WP EXAMS data deletion on plugin uninstall
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

$uninstall_settings = get_option( 'exms_settings' );

/**
 * Check if uninstall option is enable
 */
if( ! isset( $uninstall_settings['exms_uninstall'] ) ||
    'on' != $uninstall_settings['exms_uninstall'] ||
    ! file_exists( plugin_dir_path ( __FILE__ ) . 'includes/exms-db-functions.php' ) ) {

	return;
}
/**
 * Add WP Exams database functions
 */
require_once plugin_dir_path ( __FILE__ ) . 'includes/exms-db-functions.php';

$db = class_exists( 'WP_EXAMS_DB' ) ? new WP_EXAMS_DB : false;

/**
 * Check if database class exists
 */
if( ! $db ) {

	return;
}

/**
 * Delete all database tables
 */
$tables = array( 'exms_uploads', 'exms_questions_results', 'exms_quizzes_results' );

foreach( $tables as $table ) {

	$db->exms_db_query( 'drop', $table, [] );
}

/**
 * Delete all WP Exams post types
 */
$post_types = array( 'exms_quizzes', 'exms_questions', 'exms_groups', 'exms_points', 'exms_badges' );
foreach( $post_types as $post_type ) {
	$params = [];
	$params[] = [ 'field' => 'post_type', 'value' => $post_type, 'operator' => '=', 'type'=> '%s'];
	$db->exms_db_query( 'delete', 'posts', $params );
}

/**
 * Delete all WP Exams postmeta
 */
$params = [];
$params[] = [ 'field' => 'meta_key', 'value' => 'exms_%', 'operator' => 'like', 'type'=> '%s'];
$db->exms_db_query( 'delete', 'postmeta', $params );

/**
 * Delete all WP Exams options
 */
$params = [];
$params[] = [ 'field' => 'option_name', 'value' => 'exms_%', 'operator' => 'like', 'type'=> '%s'];
$db->exms_db_query( 'delete', 'options', $params );