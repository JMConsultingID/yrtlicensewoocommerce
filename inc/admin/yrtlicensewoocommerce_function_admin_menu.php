<?php
/**
 * Plugin functions and definitions for Admin.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * @package yrtlicensewoocommerce
 */

// Add the admin menu for YRT License
function yrt_license_add_admin_menu() {
    add_menu_page(
        __('YRT License', 'yrtlicensewoocommerce'),
        __('YRT License', 'yrtlicensewoocommerce'),
        'manage_options',
        'yrt-license',
        'yrt_license_manage_license_page',
        'dashicons-admin-tools',
        6
    );

    add_submenu_page(
        'yrt-license',
        __('Manage License', 'yrtlicensewoocommerce'),
        __('Manage License', 'yrtlicensewoocommerce'),
        'manage_options',
        'yrt-license',
        'yrt_license_manage_license_page'
    );

    add_submenu_page(
        'yrt-license',
        __('Settings', 'yrtlicensewoocommerce'),
        __('Settings', 'yrtlicensewoocommerce'),
        'manage_options',
        'yrt-license-settings',
        'yrt_license_settings_page'
    );
}
add_action('admin_menu', 'yrt_license_add_admin_menu');

// Function to fetch data from REST API and display it in a table with pagination and search
function yrt_license_manage_license_page() {
    // Get the API base endpoint URL, API Version, and Authorization Key from settings
    $api_base_endpoint = get_option('yrt_api_base_endpoint_url');
    $api_version = get_option('yrt_api_version', 'v2'); // Default to 'v2' if not set
    $api_authorization_key = get_option('yrt_api_authorization_key');

    // Construct the full API endpoint URL based on the base URL and version
    $api_endpoint = trailingslashit($api_base_endpoint) . $api_version . '/yrt-license/';

    // Handle search and pagination parameters
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $items_per_page = 10; // Set the number of items per page

    // Set up headers for the API request
    $headers = array(
        'Authorization' => 'Bearer ' . $api_authorization_key,
        'Content-Type'  => 'application/json'
    );

    // Set up query parameters for pagination and search
    $query_args = array(
        'page' => $current_page,
        'limit' => $items_per_page,
        'search' => $search_query,
    );

    // Build the full API URL with query parameters
    $api_url = add_query_arg($query_args, $api_endpoint);

    ?>
    <div class="wrap">
        <h1><?php _e('Manage License', 'yrtlicensewoocommerce'); ?></h1>

        <!-- Search Form -->
        <form method="get" action="">
            <input type="hidden" name="page" value="yrt-license">
            <input type="text" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="<?php _e('Search licenses...', 'yrtlicensewoocommerce'); ?>">
            <input type="submit" class="button" value="<?php _e('Search', 'yrtlicensewoocommerce'); ?>">
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Email', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Full Name', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Order ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Product ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Product Name', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Account ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('License Key', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('License Status', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Actions', 'yrtlicensewoocommerce'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch data from REST API with Authorization header
                $response = wp_remote_get($api_url, array('headers' => $headers));
                
                if (is_wp_error($response)) {
                    echo '<tr><td colspan="10">' . __('Error fetching licenses', 'yrtlicensewoocommerce') . '</td></tr>';
                } else {
                    $licenses = json_decode(wp_remote_retrieve_body($response), true);
                    if (!empty($licenses['data'])) {
                        foreach ($licenses['data'] as $license) {
                            echo '<tr>';
                            echo '<td>' . esc_html($license['id']) . '</td>';
                            echo '<td>' . esc_html($license['email']) . '</td>';
                            echo '<td>' . esc_html($license['full_name']) . '</td>';
                            echo '<td>' . esc_html($license['order_id']) . '</td>';
                            echo '<td>' . esc_html($license['product_id']) . '</td>';
                            echo '<td>' . esc_html($license['product_name']) . '</td>';
                            echo '<td>' . esc_html($license['account_id']) . '</td>';
                            echo '<td>' . esc_html($license['license_key']) . '</td>';
                            echo '<td>' . esc_html($license['license_status']) . '</td>';
                            echo '<td><a href="#">' . __('Edit', 'yrtlicensewoocommerce') . '</a></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="10">' . __('No licenses found', 'yrtlicensewoocommerce') . '</td></tr>';
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        $total_items = isset($licenses['total']) ? intval($licenses['total']) : 0;
        $total_pages = ceil($total_items / $items_per_page);
        $pagination_args = array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'current' => $current_page,
            'total' => $total_pages,
            'prev_text' => __('&laquo; Previous', 'yrtlicensewoocommerce'),
            'next_text' => __('Next &raquo;', 'yrtlicensewoocommerce'),
        );
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo paginate_links($pagination_args);
        echo '</div></div>';
        ?>
    </div>
    <?php
}


// Function to display settings page
function yrt_license_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('YRT License Settings', 'yrtlicensewoocommerce'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('yrt_license_settings_group');
            do_settings_sections('yrt_license_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings and fields
function yrt_license_register_settings() {
    register_setting('yrt_license_settings_group', 'enable_yrt_license');
    register_setting('yrt_license_settings_group', 'yrt_api_base_endpoint_url');
    register_setting('yrt_license_settings_group', 'yrt_api_authorization_key');
    register_setting('yrt_license_settings_group', 'yrt_api_version');
    register_setting('yrt_license_settings_group', 'yrt_google_script_web_app_url');

    add_settings_section('yrt_license_main_section', __('YRT License Main Settings', 'yrtlicensewoocommerce'), null, 'yrt_license_settings');

    add_settings_field('enable_yrt_license', __('Enable YRT EA License', 'yrtlicensewoocommerce'), 'yrt_license_enable_yrt_license_callback', 'yrt_license_settings', 'yrt_license_main_section');
    add_settings_field('yrt_api_base_endpoint_url', __('YRT API Base Endpoint URL', 'yrtlicensewoocommerce'), 'yrt_license_api_base_endpoint_url_callback', 'yrt_license_settings', 'yrt_license_main_section');
    add_settings_field('yrt_api_authorization_key', __('YRT API Authorization Key', 'yrtlicensewoocommerce'), 'yrt_license_api_authorization_key_callback', 'yrt_license_settings', 'yrt_license_main_section');
    add_settings_field('yrt_api_version', __('YRT API Version', 'yrtlicensewoocommerce'), 'yrt_license_api_version_callback', 'yrt_license_settings', 'yrt_license_main_section');
    add_settings_field('yrt_google_script_web_app_url', __('YRT Google Script Web APP Url (under development)', 'yrtlicensewoocommerce'), 'yrt_license_google_script_web_app_url_callback', 'yrt_license_settings', 'yrt_license_main_section');
}
add_action('admin_init', 'yrt_license_register_settings');

// Callbacks for settings fields
function yrt_license_enable_yrt_license_callback() {
    $checked = get_option('enable_yrt_license') ? 'checked' : '';
    echo '<input type="checkbox" id="enable_yrt_license" name="enable_yrt_license" value="1" ' . $checked . ' />';
}

function yrt_license_api_base_endpoint_url_callback() {
    $value = esc_attr(get_option('yrt_api_base_endpoint_url'));
    echo '<input type="text" id="yrt_api_base_endpoint_url" name="yrt_api_base_endpoint_url" value="' . $value . '" class="regular-text" />';
}

function yrt_license_api_authorization_key_callback() {
    $value = esc_attr(get_option('yrt_api_authorization_key'));
    echo '<input type="text" id="yrt_api_authorization_key" name="yrt_api_authorization_key" value="' . $value . '" class="regular-text" />';
}

// Callback for YRT API Version setting field
function yrt_license_api_version_callback() {
    // Get the current option value, defaulting to 'v2'
    $selected_version = get_option('yrt_api_version', 'v2');

    // Define the select options
    $options = array(
        'v1' => __('Version 1', 'yrtlicensewoocommerce'),
        'v2' => __('Version 2', 'yrtlicensewoocommerce')
    );

    // Start the select dropdown
    echo '<select id="yrt_api_version" name="yrt_api_version">';

    // Loop through options and set the selected attribute
    foreach ($options as $value => $label) {
        $selected = ($selected_version === $value) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($value) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }

    // Close the select dropdown
    echo '</select>';
}

function yrt_license_google_script_web_app_url_callback() {
    $value = esc_attr(get_option('yrt_api_google_app_url'));
    echo '<input type="text" id="yrt_api_google_app_url" name="yrt_api_google_app_url" value="' . $value . '" class="regular-text" />';
}
