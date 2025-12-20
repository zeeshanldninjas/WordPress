<?php

if ( !defined( 'ABSPATH' ) ) exit;

if ( !is_array( $table_names ) ) {
    $table_names = array( $table_names );
}

global $pagenow;
$class = 'notice notice-error';
$content_class = '';
$success_content_class = '';
if ( $pagenow === 'post-new.php' || ( $pagenow === 'post.php' && isset( $_GET['post'] ) ) ) {
    $class = '';
    $success_content_class = 'exms-notice-success';
    $content_class = 'exms-notice-error';
}

?>

<div class="exms-table-creation-message <?php echo esc_attr( $class ); ?>">
    <p class="exms-para-content <?php echo $success_content_class; ?>"></p>
    <p class="exms-para <?php echo esc_attr( $content_class ); ?>">
        <?php 
        $tables_list = implode( ', ', array_map( 'esc_html', $table_names ) );
        echo sprintf( __( 'The following table(s) do not exist: %s. Click to create them.', 'wp_exams' ), $tables_list ); 
        ?>
        <a href="" 
           class="create-tables-link" 
           data-action="<?php echo esc_attr( $ajax_action ); ?>" 
           data-tables='<?php echo json_encode( $table_names ); ?>'>
            <?php echo __( 'Create Tables', 'wp_exams' ); ?>
        </a>
    </p>
</div>
