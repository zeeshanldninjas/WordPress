<?php
/**
 * Plugin Name: WooCommerce Subscription Price Sync
 * Plugin URI: https://github.com/zeeshanldninjas/WordPress
 * Description: Automatically updates subscription prices when product prices change. Customers will be charged the new price in their next billing cycle.
 * Version: 1.0.0
 * Author: Codegen
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * Text Domain: wc-subscription-price-sync
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_SUBSCRIPTION_PRICE_SYNC_VERSION', '1.0.0');
define('WC_SUBSCRIPTION_PRICE_SYNC_PLUGIN_FILE', __FILE__);
define('WC_SUBSCRIPTION_PRICE_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_SUBSCRIPTION_PRICE_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class WC_Subscription_Price_Sync {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Check if WooCommerce Subscriptions is active
        if (!class_exists('WC_Subscriptions')) {
            add_action('admin_notices', array($this, 'wc_subscriptions_missing_notice'));
            return;
        }
        
        // Initialize hooks
        $this->init_hooks();
        
        // Load text domain
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Hook into product price updates
        add_action('woocommerce_product_object_updated_props', array($this, 'handle_product_price_update'), 10, 2);
        
        // Hook into variation price updates
        add_action('woocommerce_product_variation_object_updated_props', array($this, 'handle_variation_price_update'), 10, 2);
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add settings link
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Handle product price update
     */
    public function handle_product_price_update($product, $updated_props) {
        // Check if price-related properties were updated
        $price_props = array('regular_price', 'sale_price', 'price');
        $price_updated = false;
        
        foreach ($price_props as $prop) {
            if (in_array($prop, $updated_props)) {
                $price_updated = true;
                break;
            }
        }
        
        if (!$price_updated) {
            return;
        }
        
        $this->update_subscriptions_for_product($product->get_id());
    }
    
    /**
     * Handle variation price update
     */
    public function handle_variation_price_update($variation, $updated_props) {
        // Check if price-related properties were updated
        $price_props = array('regular_price', 'sale_price', 'price');
        $price_updated = false;
        
        foreach ($price_props as $prop) {
            if (in_array($prop, $updated_props)) {
                $price_updated = true;
                break;
            }
        }
        
        if (!$price_updated) {
            return;
        }
        
        $this->update_subscriptions_for_product($variation->get_id());
    }
    
    /**
     * Update subscriptions for a specific product
     */
    private function update_subscriptions_for_product($product_id) {
        // Get plugin settings
        $settings = get_option('wc_subscription_price_sync_settings', array());
        $enabled = isset($settings['enabled']) ? $settings['enabled'] : 'yes';
        $update_mode = isset($settings['update_mode']) ? $settings['update_mode'] : 'next_payment';
        $notify_customers = isset($settings['notify_customers']) ? $settings['notify_customers'] : 'yes';
        
        if ($enabled !== 'yes') {
            return;
        }
        
        // Get the product
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        // Find all active subscriptions containing this product
        $subscriptions = $this->get_subscriptions_with_product($product_id);
        
        if (empty($subscriptions)) {
            return;
        }
        
        $updated_count = 0;
        $new_price = $product->get_price();
        
        foreach ($subscriptions as $subscription) {
            try {
                $updated = $this->update_subscription_price($subscription, $product_id, $new_price, $update_mode);
                if ($updated) {
                    $updated_count++;
                    
                    // Log the update
                    $this->log_price_update($subscription, $product_id, $new_price);
                    
                    // Notify customer if enabled
                    if ($notify_customers === 'yes') {
                        $this->notify_customer($subscription, $product, $new_price);
                    }
                }
            } catch (Exception $e) {
                // Log error
                error_log('WC Subscription Price Sync Error: ' . $e->getMessage());
            }
        }
        
        // Log summary
        if ($updated_count > 0) {
            $message = sprintf(
                'Updated %d subscription(s) for product %s (ID: %d) with new price: %s',
                $updated_count,
                $product->get_name(),
                $product_id,
                wc_price($new_price)
            );
            error_log('WC Subscription Price Sync: ' . $message);
        }
    }
    
    /**
     * Get subscriptions containing a specific product
     */
    private function get_subscriptions_with_product($product_id) {
        $subscriptions = array();
        
        // Get all active subscriptions
        $args = array(
            'subscription_status' => array('active', 'on-hold'),
            'posts_per_page' => -1,
        );
        
        $subscription_posts = wcs_get_subscriptions($args);
        
        foreach ($subscription_posts as $subscription) {
            $items = $subscription->get_items();
            
            foreach ($items as $item) {
                $item_product_id = $item->get_product_id();
                $item_variation_id = $item->get_variation_id();
                
                // Check if this item matches our product (including variations)
                if ($item_product_id == $product_id || $item_variation_id == $product_id) {
                    $subscriptions[] = $subscription;
                    break; // Found the product in this subscription, move to next subscription
                }
            }
        }
        
        return $subscriptions;
    }
    
    /**
     * Update subscription price
     */
    private function update_subscription_price($subscription, $product_id, $new_price, $update_mode) {
        $items = $subscription->get_items();
        $updated = false;
        
        foreach ($items as $item_id => $item) {
            $item_product_id = $item->get_product_id();
            $item_variation_id = $item->get_variation_id();
            
            // Check if this item matches our product
            if ($item_product_id == $product_id || $item_variation_id == $product_id) {
                $old_price = $item->get_total();
                $quantity = $item->get_quantity();
                $new_total = $new_price * $quantity;
                
                // Update the line item
                $item->set_total($new_total);
                $item->set_subtotal($new_total);
                $item->save();
                
                // Add order note
                $subscription->add_order_note(
                    sprintf(
                        __('Price updated for %s: %s → %s (Product ID: %d)', 'wc-subscription-price-sync'),
                        $item->get_name(),
                        wc_price($old_price),
                        wc_price($new_total),
                        $product_id
                    )
                );
                
                $updated = true;
            }
        }
        
        if ($updated) {
            // Recalculate subscription totals
            $subscription->calculate_totals();
            $subscription->save();
            
            // Update next payment amount if mode is set to immediate
            if ($update_mode === 'immediate') {
                // This would update the next payment immediately
                // For most cases, we want to update from next billing cycle
                do_action('wc_subscription_price_sync_immediate_update', $subscription, $product_id, $new_price);
            }
        }
        
        return $updated;
    }
    
    /**
     * Log price update
     */
    private function log_price_update($subscription, $product_id, $new_price) {
        $log_entry = array(
            'subscription_id' => $subscription->get_id(),
            'product_id' => $product_id,
            'new_price' => $new_price,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        );
        
        // Store in custom table or meta
        update_post_meta($subscription->get_id(), '_price_sync_log', $log_entry);
        
        // Also store in a transient for recent updates display
        $recent_updates = get_transient('wc_subscription_price_sync_recent') ?: array();
        $recent_updates[] = $log_entry;
        
        // Keep only last 50 updates
        if (count($recent_updates) > 50) {
            $recent_updates = array_slice($recent_updates, -50);
        }
        
        set_transient('wc_subscription_price_sync_recent', $recent_updates, DAY_IN_SECONDS);
    }
    
    /**
     * Notify customer about price change
     */
    private function notify_customer($subscription, $product, $new_price) {
        $customer_email = $subscription->get_billing_email();
        $customer_name = $subscription->get_billing_first_name();
        
        $subject = sprintf(
            __('Price Update for Your Subscription - %s', 'wc-subscription-price-sync'),
            get_bloginfo('name')
        );
        
        $message = sprintf(
            __('Hello %s,

We wanted to inform you that the price for one of the products in your subscription has been updated.

Product: %s
New Price: %s
Subscription ID: #%d

This new price will be applied to your next billing cycle on %s.

If you have any questions, please don\'t hesitate to contact us.

Best regards,
%s', 'wc-subscription-price-sync'),
            $customer_name,
            $product->get_name(),
            wc_price($new_price),
            $subscription->get_id(),
            $subscription->get_date('next_payment') ? $subscription->get_date('next_payment') : __('your next billing date', 'wc-subscription-price-sync'),
            get_bloginfo('name')
        );
        
        wp_mail($customer_email, $subject, $message);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Subscription Price Sync', 'wc-subscription-price-sync'),
            __('Price Sync', 'wc-subscription-price-sync'),
            'manage_woocommerce',
            'wc-subscription-price-sync',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Add settings link to plugin page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=wc-subscription-price-sync">' . __('Settings', 'wc-subscription-price-sync') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wc_subscription_price_sync_settings', 'wc_subscription_price_sync_settings');
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            $settings = array(
                'enabled' => isset($_POST['enabled']) ? 'yes' : 'no',
                'update_mode' => sanitize_text_field($_POST['update_mode']),
                'notify_customers' => isset($_POST['notify_customers']) ? 'yes' : 'no',
            );
            update_option('wc_subscription_price_sync_settings', $settings);
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'wc-subscription-price-sync') . '</p></div>';
        }
        
        $settings = get_option('wc_subscription_price_sync_settings', array());
        $enabled = isset($settings['enabled']) ? $settings['enabled'] : 'yes';
        $update_mode = isset($settings['update_mode']) ? $settings['update_mode'] : 'next_payment';
        $notify_customers = isset($settings['notify_customers']) ? $settings['notify_customers'] : 'yes';
        
        ?>
        <div class="wrap">
            <h1><?php _e('WooCommerce Subscription Price Sync', 'wc-subscription-price-sync'); ?></h1>
            
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Price Sync', 'wc-subscription-price-sync'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="yes" <?php checked($enabled, 'yes'); ?> />
                                <?php _e('Automatically update subscription prices when product prices change', 'wc-subscription-price-sync'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Update Mode', 'wc-subscription-price-sync'); ?></th>
                        <td>
                            <select name="update_mode">
                                <option value="next_payment" <?php selected($update_mode, 'next_payment'); ?>><?php _e('Apply from next billing cycle', 'wc-subscription-price-sync'); ?></option>
                                <option value="immediate" <?php selected($update_mode, 'immediate'); ?>><?php _e('Apply immediately', 'wc-subscription-price-sync'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose when the new price should take effect.', 'wc-subscription-price-sync'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Customer Notifications', 'wc-subscription-price-sync'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="notify_customers" value="yes" <?php checked($notify_customers, 'yes'); ?> />
                                <?php _e('Send email notifications to customers when their subscription prices are updated', 'wc-subscription-price-sync'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2><?php _e('Recent Price Updates', 'wc-subscription-price-sync'); ?></h2>
            <?php $this->display_recent_updates(); ?>
        </div>
        <?php
    }
    
    /**
     * Display recent updates
     */
    private function display_recent_updates() {
        $recent_updates = get_transient('wc_subscription_price_sync_recent') ?: array();
        
        if (empty($recent_updates)) {
            echo '<p>' . __('No recent price updates.', 'wc-subscription-price-sync') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Date', 'wc-subscription-price-sync') . '</th>';
        echo '<th>' . __('Subscription', 'wc-subscription-price-sync') . '</th>';
        echo '<th>' . __('Product ID', 'wc-subscription-price-sync') . '</th>';
        echo '<th>' . __('New Price', 'wc-subscription-price-sync') . '</th>';
        echo '<th>' . __('Updated By', 'wc-subscription-price-sync') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        // Reverse to show newest first
        $recent_updates = array_reverse($recent_updates);
        
        foreach ($recent_updates as $update) {
            $user = get_user_by('id', $update['user_id']);
            $user_name = $user ? $user->display_name : __('System', 'wc-subscription-price-sync');
            
            echo '<tr>';
            echo '<td>' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($update['timestamp'])) . '</td>';
            echo '<td><a href="' . admin_url('post.php?post=' . $update['subscription_id'] . '&action=edit') . '">#' . $update['subscription_id'] . '</a></td>';
            echo '<td>' . $update['product_id'] . '</td>';
            echo '<td>' . wc_price($update['new_price']) . '</td>';
            echo '<td>' . esc_html($user_name) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('wc-subscription-price-sync', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . __('WooCommerce Subscription Price Sync', 'wc-subscription-price-sync') . '</strong> ' . __('requires WooCommerce to be installed and active.', 'wc-subscription-price-sync') . '</p></div>';
    }
    
    /**
     * WooCommerce Subscriptions missing notice
     */
    public function wc_subscriptions_missing_notice() {
        echo '<div class="error"><p><strong>' . __('WooCommerce Subscription Price Sync', 'wc-subscription-price-sync') . '</strong> ' . __('requires WooCommerce Subscriptions to be installed and active.', 'wc-subscription-price-sync') . '</p></div>';
    }
}

// Initialize the plugin
WC_Subscription_Price_Sync::get_instance();

// Activation hook
register_activation_hook(__FILE__, function() {
    // Set default options
    $default_settings = array(
        'enabled' => 'yes',
        'update_mode' => 'next_payment',
        'notify_customers' => 'yes',
    );
    
    if (!get_option('wc_subscription_price_sync_settings')) {
        update_option('wc_subscription_price_sync_settings', $default_settings);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up transients
    delete_transient('wc_subscription_price_sync_recent');
});
