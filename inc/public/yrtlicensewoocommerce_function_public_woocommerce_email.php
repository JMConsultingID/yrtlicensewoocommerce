<?php
/**
 * Plugin functions and definitions for Email.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Display Account ID and License Key on the order received (thank you) page
function license_yrt_display_license_info_on_thank_you_page($order_id) {
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }
    
    $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
    $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);

    if ($account_id && $license_key) {
        echo '<h2>' . __('Your License Details') . '</h2>';
        echo '<p><strong>' . __('Account ID') . ':</strong> ' . esc_html($account_id) . '</p>';
        echo '<p><strong>' . __('License Key') . ':</strong> ' . esc_html($license_key) . '</p>';
    }
}
add_action('woocommerce_thankyou', 'license_yrt_display_license_info_on_thank_you_page');

// Add Account ID and License Key to the order completed email
function license_yrt_add_license_info_to_email($order, $sent_to_admin, $plain_text, $email) {
    $order_id = $order->get_id();
    $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
    $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);

    if ($account_id && $license_key) {
        echo '<h2>' . __('Your License Details') . '</h2>';
        echo '<p><strong>' . __('Account ID') . ':</strong> ' . esc_html($account_id) . '</p>';
        echo '<p><strong>' . __('License Key') . ':</strong> ' . esc_html($license_key) . '</p>';
    }
}
add_action('woocommerce_email_order_meta', 'license_yrt_add_license_info_to_email', 10, 4);

// Add Account ID and License Key to the admin email when the order is completed
function license_yrt_add_license_info_to_admin_email($order, $sent_to_admin, $plain_text, $email) {
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }
    
    if ($sent_to_admin && $email->id === 'customer_completed_order') {
        $order_id = $order->get_id();
        $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
        $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);

        if ($account_id && $license_key) {
            if ($plain_text) {
                echo "Account ID: " . $account_id . "\n";
                echo "License Key: " . $license_key . "\n";
            } else {
                echo '<h3>' . __('Your License Details') . '</h3>';
                echo '<p><strong>' . __('Account ID') . ':</strong> ' . esc_html($account_id) . '</p>';
                echo '<p><strong>' . __('License Key') . ':</strong> ' . esc_html($license_key) . '</p>';
            }
        }
    }
}
add_action('woocommerce_email_order_meta', 'license_yrt_add_license_info_to_admin_email', 10, 4);