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

    // Get the Google Script Web APP Url
    $yrt_api_google_app_url = get_option('yrt_api_google_app_url');

    if ($new_status == 'completed') {
        $full_name = $order->get_formatted_billing_full_name();
        $email_billing = $order->get_billing_email();
        $items = $order->get_items();
        
        if (!empty($items)) {
            $product_id = reset($items)->get_product_id(); // Safely get the first product ID
        } else {
            $product_id = ''; // Handle if no items
        }

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

            $response = wp_remote_post($yrt_api_google_app_url, array(
                'method'    => 'POST',
                'body'      => json_encode($data), // Ensure JSON encoding
                'headers'   => array(
                    'Content-Type' => 'application/json', // Correct Content-Type
                ),
            ));

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $logger->error('Google Sheets API error: ' . $error_message, $context);
            } else {
                $response_body = wp_remote_retrieve_body($response);
                $logger->info('Google Sheets API response: ' . $response_body, $context);
            }
        } else {
            $logger->warning('Google Sheets API: Missing account_number or license_key', $context);
        }
    }
}
add_action('woocommerce_order_status_changed', 'license_yrt_send_data_to_google_sheets', 10, 4);
