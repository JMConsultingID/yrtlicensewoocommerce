<?php
/**
 * Plugin functions and definitions for Public.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */
// Add custom fields on the checkout page

add_filter('woocommerce_add_to_cart_redirect', 'license_yrt_add_to_cart_redirect');
add_filter( 'wc_add_to_cart_message_html', '__return_false' );
add_filter( 'woocommerce_add_cart_item_data', '_empty_cart' );
add_filter( 'woocommerce_adjust_non_base_location_prices', '__return_false' );
add_filter('woocommerce_enable_order_notes_field', '__return_false');
add_filter( 'woocommerce_checkout_fields' , 'license_yrt_modify_woocommerce_billing_fields' );

function license_yrt_modify_woocommerce_billing_fields( $fields ) {
    $fields['billing']['billing_email']['priority'] = 5;
    return $fields;
}

function _empty_cart( $cart_item_data ) {
    WC()->cart->empty_cart();
    return $cart_item_data;
}

function license_yrt_add_to_cart_redirect() {
    return wc_get_checkout_url();
}

function license_yrt_account_number_field_after_billing_form($checkout) {
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }

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

// Save custom fields to order meta
function license_yrt_checkout_field_update_order_meta($order_id) {
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }
    
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
    if (!is_yrt_license_enabled()) {
        return; // Exit if the feature is not enabled
    }
    
    echo '<p><strong>' . __('Account ID') . ':</strong> ' . get_post_meta($order->get_id(), '_yrt_license_account_number', true) . '</p>';
    echo '<p><strong>' . __('License Key') . ':</strong> ' . get_post_meta($order->get_id(), '_yrt_license_license_key', true) . '</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'license_yrt_display_admin_order_meta', 10, 1);


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