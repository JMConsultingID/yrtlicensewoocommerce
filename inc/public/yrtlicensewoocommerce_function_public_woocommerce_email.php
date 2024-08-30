<?php
/**
 * Plugin functions and definitions for Email.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Add Account ID, License Key, and a download link to the order completed email
function license_yrt_add_license_info_to_email($order, $sent_to_admin, $plain_text, $email) {
    if ($email->id === 'customer_completed_order') {
        $order_id = $order->get_id();
        $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
        $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);
        $download_url = 'https://eastaging.yourrobotrader.com/wp-content/uploads/woocommerce_uploads/2024/08/sample_file_products_EA_YRT-wi2p1r.txt';

        if ($account_id && $license_key) {
            if ($plain_text) {
                echo "Account ID: " . $account_id . "\n";
                echo "License Key: " . $license_key . "\n";
                echo "Download your file here: " . $download_url . "\n";
            } else {
                echo '<h3>' . __('Your License Details') . '</h3>';
                echo '<p><strong>' . __('Account ID') . ':</strong> ' . esc_html($account_id) . '</p>';
                echo '<p><strong>' . __('License Key') . ':</strong> ' . esc_html($license_key) . '</p>';
                echo '<p><a href="' . esc_url($download_url) . '" target="_blank">' . __('Download your file here') . '</a></p>';
            }
        }
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
        $download_url = 'https://eastaging.yourrobotrader.com/wp-content/uploads/woocommerce_uploads/2024/08/sample_file_products_EA_YRT-wi2p1r.txt';

        if ($account_id && $license_key) {
            if ($plain_text) {
                echo "Account ID: " . $account_id . "\n";
                echo "License Key: " . $license_key . "\n";
                echo "Download your file here: " . $download_url . "\n";
            } else {
                echo '<h3>' . __('Your License Details') . '</h3>';
                echo '<p><strong>' . __('Account ID') . ':</strong> ' . esc_html($account_id) . '</p>';
                echo '<p><strong>' . __('License Key') . ':</strong> ' . esc_html($license_key) . '</p>';
                echo '<p><a href="' . esc_url($download_url) . '" target="_blank">' . __('Download your file here') . '</a></p>';
            }
        }
    }
}
add_action('woocommerce_email_order_meta', 'license_yrt_add_license_info_to_admin_email', 10, 4);