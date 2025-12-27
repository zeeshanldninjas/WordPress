<?php
/**
 * WooCommerce Subscription Price Sync - Usage Examples
 * 
 * This file demonstrates how the plugin works and provides examples
 * for developers who want to extend or customize the functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example 1: Programmatically trigger price sync for a specific product
 */
function example_trigger_price_sync_for_product($product_id) {
    // Get the plugin instance
    $plugin = WC_Subscription_Price_Sync::get_instance();
    
    // Get the product
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }
    
    // Simulate a price update by calling the update method directly
    // This would normally be triggered automatically when a product price changes
    $plugin->update_subscriptions_for_product($product_id);
    
    return true;
}

/**
 * Example 2: Hook into the immediate update action
 */
add_action('wc_subscription_price_sync_immediate_update', 'handle_immediate_price_update', 10, 3);
function handle_immediate_price_update($subscription, $product_id, $new_price) {
    // Custom logic for immediate price updates
    // For example, you might want to:
    
    // 1. Send a different type of notification
    $customer_email = $subscription->get_billing_email();
    wp_mail(
        $customer_email,
        'Immediate Price Update - Action Required',
        sprintf(
            'Your subscription #%d has been updated with immediate effect. New price: %s',
            $subscription->get_id(),
            wc_price($new_price)
        )
    );
    
    // 2. Log to a custom system
    error_log(sprintf(
        'IMMEDIATE UPDATE: Subscription %d, Product %d, New Price: %s',
        $subscription->get_id(),
        $product_id,
        $new_price
    ));
    
    // 3. Update external systems (CRM, analytics, etc.)
    // your_custom_crm_update($subscription, $product_id, $new_price);
}

/**
 * Example 3: Custom filter to modify notification email content
 * (This would need to be added to the main plugin as a filter)
 */
function custom_price_update_email_content($message, $subscription, $product, $new_price) {
    // Customize the email message
    $custom_message = sprintf(
        'Dear %s,

We hope this message finds you well. We wanted to personally inform you about an important update to your subscription.

Product: %s
Previous Price: %s
New Price: %s
Subscription: #%d

This change reflects our commitment to providing you with the best value and service. The new pricing will take effect on your next billing date: %s.

We appreciate your continued trust in our services. If you have any questions or concerns, please don\'t hesitate to reach out to our customer support team.

Thank you for being a valued customer.

Best regards,
The %s Team',
        $subscription->get_billing_first_name(),
        $product->get_name(),
        wc_price($product->get_regular_price()), // You might want to store the old price
        wc_price($new_price),
        $subscription->get_id(),
        $subscription->get_date('next_payment') ?: 'your next billing date',
        get_bloginfo('name')
    );
    
    return $custom_message;
}

/**
 * Example 4: Bulk price update for multiple products
 */
function example_bulk_price_update($product_price_map) {
    // $product_price_map should be an array like:
    // array(
    //     123 => 29.99,  // Product ID => New Price
    //     456 => 39.99,
    //     789 => 19.99,
    // )
    
    foreach ($product_price_map as $product_id => $new_price) {
        $product = wc_get_product($product_id);
        if ($product) {
            // Update the product price
            $product->set_regular_price($new_price);
            $product->set_price($new_price);
            $product->save();
            
            // The plugin will automatically detect this change and update subscriptions
            // due to the 'woocommerce_product_object_updated_props' hook
        }
    }
}

/**
 * Example 5: Get all subscriptions that would be affected by a price change
 */
function example_preview_price_change_impact($product_id, $new_price) {
    $plugin = WC_Subscription_Price_Sync::get_instance();
    
    // Get subscriptions that contain this product
    $subscriptions = $plugin->get_subscriptions_with_product($product_id);
    
    $impact_data = array();
    
    foreach ($subscriptions as $subscription) {
        $items = $subscription->get_items();
        
        foreach ($items as $item) {
            if ($item->get_product_id() == $product_id || $item->get_variation_id() == $product_id) {
                $current_total = $item->get_total();
                $quantity = $item->get_quantity();
                $new_total = $new_price * $quantity;
                $difference = $new_total - $current_total;
                
                $impact_data[] = array(
                    'subscription_id' => $subscription->get_id(),
                    'customer_email' => $subscription->get_billing_email(),
                    'current_price' => $current_total,
                    'new_price' => $new_total,
                    'difference' => $difference,
                    'next_payment' => $subscription->get_date('next_payment'),
                );
            }
        }
    }
    
    return $impact_data;
}

/**
 * Example 6: Custom admin notice for price changes
 */
add_action('admin_notices', 'example_price_change_admin_notice');
function example_price_change_admin_notice() {
    // Check if we're on a product edit page and if there are recent price updates
    global $post;
    
    if (!$post || $post->post_type !== 'product') {
        return;
    }
    
    $recent_updates = get_transient('wc_subscription_price_sync_recent') ?: array();
    $product_updates = array_filter($recent_updates, function($update) use ($post) {
        return $update['product_id'] == $post->ID;
    });
    
    if (!empty($product_updates)) {
        $count = count($product_updates);
        echo '<div class="notice notice-info">';
        echo '<p>' . sprintf(
            _n(
                'This product has %d recent subscription price update.',
                'This product has %d recent subscription price updates.',
                $count,
                'wc-subscription-price-sync'
            ),
            $count
        ) . '</p>';
        echo '</div>';
    }
}

/**
 * Example 7: Export subscription price update history
 */
function example_export_price_update_history() {
    $recent_updates = get_transient('wc_subscription_price_sync_recent') ?: array();
    
    if (empty($recent_updates)) {
        return false;
    }
    
    $csv_data = array();
    $csv_data[] = array('Date', 'Subscription ID', 'Product ID', 'New Price', 'Updated By');
    
    foreach ($recent_updates as $update) {
        $user = get_user_by('id', $update['user_id']);
        $user_name = $user ? $user->display_name : 'System';
        
        $csv_data[] = array(
            $update['timestamp'],
            $update['subscription_id'],
            $update['product_id'],
            $update['new_price'],
            $user_name
        );
    }
    
    // Generate CSV content
    $csv_content = '';
    foreach ($csv_data as $row) {
        $csv_content .= implode(',', $row) . "\n";
    }
    
    return $csv_content;
}

/**
 * Example 8: Conditional price updates based on subscription status
 */
function example_conditional_price_update($product_id, $new_price) {
    $plugin = WC_Subscription_Price_Sync::get_instance();
    $subscriptions = $plugin->get_subscriptions_with_product($product_id);
    
    foreach ($subscriptions as $subscription) {
        $status = $subscription->get_status();
        $customer_id = $subscription->get_customer_id();
        
        // Only update for active subscriptions of VIP customers
        if ($status === 'active' && user_can($customer_id, 'vip_customer')) {
            // Apply a discount for VIP customers
            $vip_price = $new_price * 0.9; // 10% discount
            
            // Update with custom price
            $plugin->update_subscription_price($subscription, $product_id, $vip_price, 'next_payment');
        }
    }
}
