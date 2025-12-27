# WooCommerce Subscription Price Sync

Automatically updates subscription prices when product prices change. Customers will be charged the new price in their next billing cycle.

## Description

This plugin automatically synchronizes subscription prices with product price changes in WooCommerce. When you update a product's price, all active subscriptions containing that product will be updated to reflect the new pricing.

## Features

- **Automatic Price Synchronization**: Updates subscription prices when product prices change
- **Support for Variable Products**: Handles both simple and variable product price updates
- **Flexible Update Modes**: Choose between applying changes from the next billing cycle or immediately
- **Customer Notifications**: Optional email notifications to customers about price changes
- **Comprehensive Logging**: Track all price updates with detailed logs
- **Admin Interface**: Easy-to-use settings page in WooCommerce admin
- **Subscription Notes**: Automatic order notes added to subscriptions for transparency

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- WooCommerce Subscriptions plugin

## Installation

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-subscription-price-sync/` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to WooCommerce > Price Sync to configure the plugin settings

## Configuration

### Settings

Access the plugin settings via **WooCommerce > Price Sync** in your WordPress admin.

#### Available Options:

1. **Enable Price Sync**
   - Enable or disable automatic price synchronization
   - Default: Enabled

2. **Update Mode**
   - **Apply from next billing cycle** (Recommended): New prices take effect on the customer's next billing date
   - **Apply immediately**: New prices take effect immediately
   - Default: Apply from next billing cycle

3. **Customer Notifications**
   - Send email notifications to customers when their subscription prices are updated
   - Default: Enabled

## How It Works

1. **Price Change Detection**: The plugin monitors product price updates using WooCommerce hooks
2. **Subscription Identification**: Finds all active subscriptions containing the updated product
3. **Price Update**: Updates the subscription line items with the new product price
4. **Logging**: Records the change with timestamp, user, and details
5. **Customer Notification**: Optionally sends email notification to affected customers
6. **Order Notes**: Adds detailed notes to subscription orders for transparency

## Supported Product Types

- Simple Products
- Variable Products (individual variations)
- Subscription Products
- Variable Subscription Products

## Email Notifications

When enabled, customers receive email notifications containing:
- Product name and new price
- Subscription ID
- Next billing date when new price takes effect
- Contact information for questions

## Logging and Tracking

The plugin maintains comprehensive logs of all price updates:
- Subscription ID
- Product ID
- New price
- Timestamp
- User who made the change

Recent updates are displayed in the admin interface for easy monitoring.

## Developer Hooks

### Actions

- `wc_subscription_price_sync_immediate_update`: Fired when immediate price updates are applied
  ```php
  do_action('wc_subscription_price_sync_immediate_update', $subscription, $product_id, $new_price);
  ```

### Filters

The plugin is designed to be extensible. Additional filters may be added in future versions.

## Frequently Asked Questions

### Q: Will existing subscription payments be affected immediately?
A: By default, no. The plugin is configured to apply new prices from the next billing cycle. You can change this behavior in the settings.

### Q: What happens if a customer has multiple subscriptions with the same product?
A: All subscriptions containing the updated product will be updated individually.

### Q: Can I disable notifications for specific price changes?
A: Currently, notifications are controlled globally through the plugin settings. You can disable them entirely or enable them for all price changes.

### Q: Does this work with variable products?
A: Yes, the plugin supports both simple and variable products, including individual variation price updates.

## Changelog

### 1.0.0
- Initial release
- Automatic subscription price synchronization
- Customer email notifications
- Admin settings interface
- Comprehensive logging
- Support for simple and variable products

## Support

For support, feature requests, or bug reports, please create an issue in the plugin repository.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Codegen for seamless WooCommerce subscription management.
