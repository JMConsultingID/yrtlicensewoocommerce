<?php
/**
 * Plugin functions and definitions for Global.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Include admin functions
require dirname(__FILE__) . '/admin/yrtlicensewoocommerce_function_admin_woocommerce.php';

// Include helper functions
require dirname(__FILE__) . '/helper/yrtlicensewoocommerce_function_helper.php';

// Include public functions
require dirname(__FILE__) . '/public/yrtlicensewoocommerce_function_public_woocommerce.php';
require dirname(__FILE__) . '/public/yrtlicensewoocommerce_function_public_woocommerce_email.php';
require dirname(__FILE__) . '/public/yrtlicensewoocommerce_function_public_woocommerce_api.php';
//require dirname(__FILE__) . '/public/yrtlicensewoocommerce_function_public_woocommerce_google_sheet.php';