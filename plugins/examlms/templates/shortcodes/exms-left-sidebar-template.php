<?php

/**
 * Template to display [exms_student_dashboard] shortcode left sidebar
 */
if (! defined('ABSPATH')) exit;

?>
<div class="exms-title exms-left-sidebar">

    <div class="exms-sidebar-mobile-card">
        <button type="button" class="exms-sidebar-toggle">
            <span class="dashicons dashicons-menu"></span>
            <span class="exms-sidebar-toggle-label">
                <?php _e( 'Menu', 'exms' ); ?>
            </span>
        </button>

        <div class="exms-sidebar-mobile-welcome">
            <?php
            printf(
                esc_html__('Welcome, %s', 'exms'),
                esc_html($current_user->display_name)
            );
            ?>
        </div>
    </div>

    <ul class="exms-sidebar-menu">
        <?php
        if ($all_links) {
            $menu_icons = [
                'exms_dashboard' => 'dashicons-dashboard',
                'exms_attendance' => 'dashicons-clipboard',
                'exms_my_courses' => 'dashicons-welcome-learn-more',
                'exms_my_groups'  => 'dashicons-groups',
                'exms_my_account' => 'dashicons-admin-users',
                'exms_my_reports' => 'dashicons-chart-bar',
            ];

            foreach ($all_links as $tab_name => $link) { 
                $icon_class = isset($menu_icons[$tab_name]) ? $menu_icons[$tab_name] : 'dashicons-menu';
                ?>
                <li>
                    <a href="<?php echo esc_url($current_page_link); ?>?exms_active_tab=<?php echo esc_attr($tab_name); ?>&exms_db_type=<?php echo esc_attr($user_type); ?>"
                        class="<?php echo $tab_name == $active_tab ? esc_attr($active_class) : ''; ?>">
                        <span class="dashicons <?php echo $icon_class; ?>"></span>
                        <span class="exms-menu-text"><?php _e($link, 'exms'); ?></span>
                    </a>
                </li>
        <?php }
        }
        ?>
    </ul>
</div>