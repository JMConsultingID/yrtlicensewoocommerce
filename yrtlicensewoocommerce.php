<?php
/*
Plugin Name: YRT License EA
Description: A plugin to connect WooCommerce with YRT License API.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add custom fields on the checkout page
function license_yrt_account_number_field_after_billing_form($checkout) {
    echo '<div id="yrt_license_account_number"><h3>' . __('Account Number') . '</h3>';

    woocommerce_form_field('yrt_license_account_number', array(
        'type'          => 'text',
        'class'         => array('yrt-license-account-number form-row-wide'),
        'label'         => __('Account Number'),
        'placeholder'   => __('Enter your Account Number'),
        'required'      => true,
    ), $checkout->get_value('yrt_license_account_number'));

    echo '</div>';
}
add_action('woocommerce_after_checkout_billing_form', 'license_yrt_account_number_field_after_billing_form');

// Generate a random license key
function license_yrt_generate_license_key() {
    $segments = array();
    for ($i = 0; $i < 4; $i++) {
        $segments[] = strtoupper(bin2hex(random_bytes(2))); // Generates 4 characters
    }
    return implode('-', $segments); // Returns in XXXX-XXXX-XXXX-XXXX format
}

// Save custom fields to order meta
function license_yrt_checkout_field_update_order_meta($order_id) {
    if ($_POST['yrt_license_account_number']) {
        update_post_meta($order_id, '_yrt_license_account_number', sanitize_text_field($_POST['yrt_license_account_number']));
    }

    // Generate and save license key
    $license_key = license_yrt_generate_license_key();
    update_post_meta($order_id, '_yrt_license_license_key', $license_key);
}
add_action('woocommerce_checkout_update_order_meta', 'license_yrt_checkout_field_update_order_meta');

// Display custom fields in the WooCommerce admin order details
function license_yrt_display_admin_order_meta($order) {
    echo '<p><strong>' . __('Account ID') . ':</strong> ' . get_post_meta($order->get_id(), '_yrt_license_account_number', true) . '</p>';
    echo '<p><strong>' . __('License Key') . ':</strong> ' . get_post_meta($order->get_id(), '_yrt_license_license_key', true) . '</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'license_yrt_display_admin_order_meta', 10, 1);

// Send data to API when order status changes to 'completed'
function license_yrt_send_api_on_order_status_change($order_id, $old_status, $new_status, $order) {
    if ($new_status == 'completed') {
        $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
        $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);

        // Initialize logger
        $logger_info = license_yrt_connection_response_logger();
        $logger = $logger_info['logger'];
        $context = $logger_info['context'];

        if ($account_id && $license_key) {
            $data = array(
                'license_key' => $license_key,
                'account_id'  => $account_id
            );

            $response = wp_remote_post('https://license.yourrobotrader.com/api/v1/yrt-license/', array(
                'method'    => 'POST',
                'body'      => json_encode($data),
                'headers'   => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer 5102bb65-c3a7-41dd-a1b3-ba3d61c3b762', // Add Bearer token here
                ),
            ));

            if (is_wp_error($response)) {
                // Log error
                $error_message = $response->get_error_message();
                $logger->error('YRT License API error: ' . $error_message, $context);
            } else {
                // Log success response
                $response_body = wp_remote_retrieve_body($response);
                $logger->info('YRT License API response: ' . $response_body, $context);
            }
        } else {
            // Log missing account_id or license_key
            $logger->warning('YRT License API: Missing account_id or license_key', $context);
        }
    }
}
add_action('woocommerce_order_status_changed', 'license_yrt_send_api_on_order_status_change', 10, 4);

// Function to initialize the logger
function license_yrt_connection_response_logger() {
    $logger = wc_get_logger();
    $context = array('source' => 'license_yrt_connection_response_log');
    return array('logger' => $logger, 'context' => $context);
}

// Display Account ID and License Key on the order received (thank you) page
function license_yrt_display_license_info_on_thank_you_page($order_id) {
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


// Send data to Google Sheets when order status changes to 'completed'
function license_yrt_send_data_to_google_sheets($order_id, $old_status, $new_status, $order) {
    if ($new_status == 'completed') {
        $full_name = $order->get_formatted_billing_full_name();
        $email_billing = $order->get_billing_email();
        $product_id = $order->get_items()[0]->get_product_id(); // Assuming single product order
        $account_number = get_post_meta($order_id, '_yrt_license_account_number', true);
        $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);

        // Initialize logger
        $logger_info = license_yrt_connection_response_logger();
        $logger = $logger_info['logger'];
        $context = $logger_info['context'];

        if ($account_number && $license_key) {
            $data = array(
                'full_name'       => $full_name,
                'email_billing'   => $email_billing,
                'order_id'        => $order_id,
                'product_id'      => $product_id,
                'account_number'  => $account_number,
                'license_key'     => $license_key
            );

            $response = wp_remote_post('https://script.google.com/macros/s/AKfycbyoDAN2ClkKlu36xb1KwEsoyfhoP0i3WBzRX2gU3RAksU6sASDF1nEQMzCbZgnA1RryQw/exec', array(
                'method'    => 'POST',
                'body'      => json_encode($data),
                'headers'   => array(
                    'Content-Type' => 'application/json',
                ),
            ));

            if (is_wp_error($response)) {
                // Log error
                $error_message = $response->get_error_message();
                $logger->error('Google Sheets API error: ' . $error_message, $context);
            } else {
                // Log success response
                $response_body = wp_remote_retrieve_body($response);
                $logger->info('Google Sheets API response: ' . $response_body, $context);
            }
        } else {
            // Log missing account_number or license_key
            $logger->warning('Google Sheets API: Missing account_number or license_key', $context);
        }
    }
}
add_action('woocommerce_order_status_changed', 'license_yrt_send_data_to_google_sheets', 10, 4);
