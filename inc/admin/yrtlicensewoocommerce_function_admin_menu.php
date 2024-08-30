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
        'order_by' => 'id', // Order by 'id' or 'account_creation_date'
        'order'    => 'desc' // Descending order
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
            <a href="<?php echo esc_url(admin_url('admin.php?page=yrt-license')); ?>" class="button"><?php _e('Clear Search', 'yrtlicensewoocommerce'); ?></a>
        </form>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Email', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Full Name', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Order ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Product ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Product Name', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Account ID', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('License Key', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('License Expiration', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Source', 'yrtlicensewoocommerce'); ?></th>
                    <th><?php _e('Creation Date', 'yrtlicensewoocommerce'); ?></th>
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
                    $response_body = wp_remote_retrieve_body($response);
                    $data = json_decode($response_body, true);

                    // Ensure that 'data' exists in the response and is an array
                    if (isset($data['data']) && is_array($data['data'])) {
                        $licenses = $data['data'];
                        if (!empty($licenses)) {
                            // Extract source information from the license data
                            $source = isset($license['source']) ? json_decode($license['source'], true) : array();
                            $source_ip_user = isset($source['ip_user']) ? $source['ip_user'] : __('N/A', 'yrtlicensewoocommerce');
                            $source_browser = isset($source['browser']) ? $source['browser'] : __('N/A', 'yrtlicensewoocommerce');
                            $source_domain = isset($source['domain']) ? $source['domain'] : __('N/A', 'yrtlicensewoocommerce');
                            foreach ($licenses as $license) {
                                echo '<tr>';
                                echo '<td>' . esc_html($license['email']) . '</td>';
                                echo '<td>' . esc_html($license['full_name']) . '</td>';
                                echo '<td>' . esc_html($license['order_id']) . '</td>';
                                echo '<td>' . esc_html($license['product_id']) . '</td>';
                                echo '<td>' . esc_html($license['product_name']) . '</td>';
                                echo '<td>' . esc_html($license['account_id']) . '</td>';
                                echo '<td>' . esc_html($license['license_key']) . '</td>';
                                echo '<td>' . esc_html($license['license_expiration']) . '</td>';
                                echo '<td>' . esc_html($domain) . '</td>';
                                echo '<td>' . esc_html($license['account_creation_date']) . '</td>';
                                echo '<td>' . esc_html($license['license_status']) . '</td>';
                                echo '<td><a href="' . esc_url(admin_url('admin.php?page=yrt-license&edit_id=' . $license['id'])) . '">' . __('Edit', 'yrtlicensewoocommerce') . '</a></td>';
                                echo '</tr>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="10">' . __('No licenses found', 'yrtlicensewoocommerce') . '</td></tr>';
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
        $total_items = isset($data['total']) ? intval($data['total']) : 0;
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
    // If an edit ID is set, display the edit form
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        display_license_edit_form($edit_id);
    }
}

function display_license_edit_form($edit_id) {
    // Fetch the license details using the edit_id
    $api_base_endpoint = get_option('yrt_api_base_endpoint_url');
    $api_version = get_option('yrt_api_version', 'v2');
    $api_authorization_key = get_option('yrt_api_authorization_key');

    $api_endpoint = trailingslashit($api_base_endpoint) . $api_version . '/yrt-license/' . $edit_id;

    $headers = array(
        'Authorization' => 'Bearer ' . $api_authorization_key,
        'Content-Type'  => 'application/json'
    );

    $response = wp_remote_get($api_endpoint, array('headers' => $headers));

    if (is_wp_error($response)) {
        echo '<div class="error"><p>' . __('Error fetching license details.', 'yrtlicensewoocommerce') . '</p></div>';
        return;
    }

    $license = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($license)) {
        echo '<div class="error"><p>' . __('No license details found.', 'yrtlicensewoocommerce') . '</p></div>';
        return;
    }

    // Extract source information from the license data
    $source = isset($license['source']) ? json_decode($license['source'], true) : array();
    $ip_user = isset($source['ip_user']) ? $source['ip_user'] : __('N/A', 'yrtlicensewoocommerce');
    $browser = isset($source['browser']) ? $source['browser'] : __('N/A', 'yrtlicensewoocommerce');
    $domain = isset($source['domain']) ? $source['domain'] : __('N/A', 'yrtlicensewoocommerce');

    // Display the edit form
    ?>
    <div class="wrap">
        <h2><?php _e('Edit License', 'yrtlicensewoocommerce'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="yrt_update_license">
            <input type="hidden" name="license_id" value="<?php echo esc_attr($edit_id); ?>">

            <!-- Add nonce field -->
            <?php wp_nonce_field('yrt_update_license_nonce', '_wpnonce'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="email"><?php _e('Email', 'yrtlicensewoocommerce'); ?></label></th>
                    <td><input type="email" name="email" id="email" value="<?php echo esc_attr($license['email']); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="full_name"><?php _e('Full Name', 'yrtlicensewoocommerce'); ?></label></th>
                    <td><input type="text" name="full_name" id="full_name" value="<?php echo esc_attr($license['full_name']); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="account_id"><?php _e('Account ID', 'yrtlicensewoocommerce'); ?></label></th>
                    <td><input type="text" name="account_id" id="account_id" value="<?php echo esc_attr($license['account_id']); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="license_key"><?php _e('License Key', 'yrtlicensewoocommerce'); ?></label></th>
                    <td><input type="text" name="license_key" id="license_key" value="<?php echo esc_attr($license['license_key']); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="license_status"><?php _e('License Status', 'yrtlicensewoocommerce'); ?></label></th>
                    <td>
                        <select name="license_status" id="license_status" class="regular-text">
                            <option value="active" <?php selected($license['license_status'], 'active'); ?>><?php _e('Active', 'yrtlicensewoocommerce'); ?></option>
                            <option value="inactive" <?php selected($license['license_status'], 'inactive'); ?>><?php _e('Inactive', 'yrtlicensewoocommerce'); ?></option>
                            <option value="blocked" <?php selected($license['license_status'], 'blocked'); ?>><?php _e('Blocked', 'yrtlicensewoocommerce'); ?></option>
                        </select>
                    </td>
                </tr>
                <!-- Display source information below license status -->
                <tr>
                    <th><?php _e('IP Address', 'yrtlicensewoocommerce'); ?></th>
                    <td><p><?php echo esc_html($ip_user); ?></p></td>
                </tr>
                <tr>
                    <th><?php _e('Browser', 'yrtlicensewoocommerce'); ?></th>
                    <td><p><?php echo esc_html($browser); ?></p></td>
                </tr>
                <tr>
                    <th><?php _e('Domain', 'yrtlicensewoocommerce'); ?></th>
                    <td><p><?php echo esc_html($domain); ?></p></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Update License', 'yrtlicensewoocommerce'); ?>">
            </p>
        </form>
    </div>
    <?php
}

function yrt_handle_license_update() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Validate nonce for security
    if (!check_admin_referer('yrt_update_license_nonce', '_wpnonce')) {
        wp_die(__('Security check failed. The link you followed has expired.', 'yrtlicensewoocommerce'));
    }

    $license_id = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';
    $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
    $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';
    $license_status = isset($_POST['license_status']) ? sanitize_text_field($_POST['license_status']) : '';

    // Update data via the REST API
    $api_base_endpoint = get_option('yrt_api_base_endpoint_url');
    $api_version = get_option('yrt_api_version', 'v2');
    $api_authorization_key = get_option('yrt_api_authorization_key');

    $api_endpoint = trailingslashit($api_base_endpoint) . $api_version . '/yrt-license/edit';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_authorization_key,
        'Content-Type'  => 'application/json'
    );

    $body = json_encode(array(
        'id' => $license_id,
        'email' => $email,
        'full_name' => $full_name,
        'account_id' => $account_id,
        'license_key' => $license_key,
        'license_status' => $license_status
        // Add other fields as necessary
    ));

    $response = wp_remote_post($api_endpoint, array(
        'method' => 'PUT',
        'body' => $body,
        'headers' => $headers
    ));

    // Check for errors in the response
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_die(__('Failed to update the license: ', 'yrtlicensewoocommerce') . $error_message);
    } else {
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        // Check the response code and body for success or failure
        if ($response_code == 200) {
            wp_redirect(add_query_arg(array('page' => 'yrt-license', 'updated' => 'success'), admin_url('admin.php')));
        } else {
            $response_data = json_decode($response_body, true);
            $error_message = isset($response_data['message']) ? $response_data['message'] : __('An unknown error occurred.', 'yrtlicensewoocommerce');
            wp_die(__('Failed to update the license: ', 'yrtlicensewoocommerce') . esc_html($error_message));
        }
    }
    exit;
}
add_action('admin_post_yrt_update_license', 'yrt_handle_license_update');

// Function to display admin notice after a successful update
function yrt_license_admin_notice() {
    if (isset($_GET['updated']) && $_GET['updated'] === 'success') {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . __('License updated successfully.', 'yrtlicensewoocommerce') . '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'yrt_license_admin_notice');



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
