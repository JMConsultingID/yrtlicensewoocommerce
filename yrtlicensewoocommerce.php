<?php
/**
 * @link              https://yourrobotrader.com
 * @since             1.1.1.0
 * @package           yrtlicensewoocommerce
 * GitHub Plugin URI: https://github.com/JMConsultingID/your-propfirm-addon
 * GitHub Branch: develop
 * @wordpress-plugin
 * Plugin Name:       YRT License EA
 * Plugin URI:        https://yourrobotrader.com
 * Description:       A plugin to connect WooCommerce with YRT License API.
 * Version:           1.1.1.0
 * Author:            YourRoboTrader Team
 * Author URI:        https://yourrobotrader.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       yrtlicensewoocommerce
 * Domain Path:       /languages
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define( 'YRTLICENSE_VERSION', '1.1.1.0' );

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . '/wp-admin/includes/plugin.php');
}

require plugin_dir_path( __FILE__ ) . 'inc/yrtlicensewoocommerce_functions.php';