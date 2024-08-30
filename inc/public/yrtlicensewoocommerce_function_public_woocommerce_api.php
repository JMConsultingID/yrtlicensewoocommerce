<?php
/**
 * Plugin functions and definitions for Send API.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Send data to API when order status changes to 'completed'
function license_yrt_send_api_on_order_status_change($order_id, $old_status, $new_status, $order) {
    // Use the reusable function to check if the feature is enabled
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }

    // Get the API base endpoint URL, Authorization Key, and API Version from settings
    $api_base_endpoint = get_option('yrt_api_base_endpoint_url');
    $api_authorization_key = get_option('yrt_api_authorization_key');
    $api_version = get_option('yrt_api_version', 'v2'); // Default to 'v2' if not set

    // Construct the full API endpoint URL based on the base URL and version
    $api_endpoint = trailingslashit($api_base_endpoint) . $api_version . '/yrt-license/';

    if ($new_status == 'completed' && !empty($api_base_endpoint) && !empty($api_authorization_key)) {
        $account_id = get_post_meta($order_id, '_yrt_license_account_number', true);
        $license_key = get_post_meta($order_id, '_yrt_license_license_key', true);
        $email = $order->get_billing_email();
        $full_name = $order->get_formatted_billing_full_name();
        $order_id = $order->get_id();
        $product_id = '';
        $product_name = '';

        // Fetch product details (assuming a single product order)
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product_name = $item->get_name();
            break; // Stop after the first item (assuming single product order)
        }

        // Set license expiration and source
        $license_expiration = 'Lifetime';
        $source = 'woocommerce yourrobotrader.com';

        // Initialize logger
        $logger_info = license_yrt_connection_response_logger();
        $logger = $logger_info['logger'];
        $context = $logger_info['context'];

        if ($account_id && $license_key) {
            $data = array(
                'email' => $email,
                'full_name' => $full_name,
                'order_id' => $order_id,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'account_id' => $account_id,
                'license_key' => $license_key,
                'license_expiration' => $license_expiration,
                'source' => $source
            );

            $response = wp_remote_post($api_endpoint, array(
                'method'    => 'POST',
                'body'      => json_encode($data),
                'headers'   => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_authorization_key, // Use the saved Authorization Key
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