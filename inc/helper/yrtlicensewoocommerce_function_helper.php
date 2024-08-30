<?php
/**
 * Plugin functions and definitions for Helper.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Function to check if YRT EA License feature is enabled
function is_yrt_license_enabled() {
    $enable_yrt_license = get_option('enable_yrt_license');
    return !empty($enable_yrt_license);
}


// Function to initialize the logger
function license_yrt_connection_response_logger() {
    $logger = wc_get_logger();
    $context = array('source' => 'license_yrt_connection_response_log');
    return array('logger' => $logger, 'context' => $context);
}

// Generate a random license key
function license_yrt_generate_license_key() {
    $segments = array();
    for ($i = 0; $i < 4; $i++) {
        $segments[] = strtoupper(bin2hex(random_bytes(2))); // Generates 4 characters
    }
    return implode('-', $segments); // Returns in XXXX-XXXX-XXXX-XXXX format
}
