<?php
/**
 * Plugin functions and definitions for Admin.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Add a settings section to WooCommerce settings
function yrt_license_woocommerce_settings($settings) {
    $settings[] = array(
        'title' => __('YRT EA License Settings', 'yrtlicensewoocommerce'),
        'type'  => 'title',
        'id'    => 'yrt_license_settings'
    );

    $settings[] = array(
        'title'    => __('Enable YRT EA License', 'yrtlicensewoocommerce'),
        'desc'     => __('Enable YRT EA License integration with WooCommerce', 'yrtlicensewoocommerce'),
        'id'       => 'enable_yrt_license',
        'type'     => 'checkbox',
        'default'  => 'no',
        'desc_tip' => __('Enable or disable the YRT EA License feature.', 'yrtlicensewoocommerce'),
    );

    $settings[] = array(
        'title'    => __('YRT API Base Endpoint URL', 'yrtlicensewoocommerce'),
        'desc'     => __('Enter the base endpoint URL for the YRT API', 'yrtlicensewoocommerce'),
        'id'       => 'yrt_api_base_endpoint_url',
        'type'     => 'text',
        'default'  => '',
        'desc_tip' => __('Base URL for the YRT API (e.g., https://license.yourrobotrader.com/api/v1/yrt-license/)', 'yrtlicensewoocommerce'),
    );

    $settings[] = array(
        'title'    => __('YRT API Authorization Key', 'yrtlicensewoocommerce'),
        'desc'     => __('Enter the authorization key for the YRT API', 'yrtlicensewoocommerce'),
        'id'       => 'yrt_api_authorization_key',
        'type'     => 'text',
        'default'  => '',
        'desc_tip' => __('Authorization key for the YRT API (e.g., Bearer token)', 'yrtlicensewoocommerce'),
    );

    $settings[] = array(
        'type' => 'sectionend',
        'id'   => 'yrt_license_settings'
    );

    return $settings;
}
add_filter('woocommerce_get_settings_general', 'yrt_license_woocommerce_settings');

// Save the custom setting values
function yrt_license_save_woocommerce_settings() {
    woocommerce_update_options(array(
        array(
            'id'       => 'enable_yrt_license',
            'type'     => 'checkbox',
            'default'  => 'no',
        ),
        array(
            'id'       => 'yrt_api_base_endpoint_url',
            'type'     => 'text',
            'default'  => '',
        ),
        array(
            'id'       => 'yrt_api_authorization_key',
            'type'     => 'text',
            'default'  => '',
        )
    ));
}
add_action('woocommerce_update_options_general', 'yrt_license_save_woocommerce_settings');