<?php
/**
 * Plugin functions and definitions for Send API.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Send data to Google Sheets when order status changes to 'completed'
function license_yrt_send_data_to_google_sheets($order_id, $old_status, $new_status, $order) {
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }
    
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