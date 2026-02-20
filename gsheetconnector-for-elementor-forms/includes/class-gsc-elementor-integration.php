<?php

/**
 * Integration class for Google Sheet Connector
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Gs_Connector_Service Class
 *
 * @since 1.0.0
 */
class GSC_Elementor_Integration
{

    /**
     *  Set things up.
     *  @since 1.0
     */
    public function __construct()
    {
        add_action('wp_ajax_verify_gscelementor_integation', array($this, 'verify_gscelementor_integation'));
        add_action('wp_ajax_deactivate_gscelementor_integation', array($this, 'deactivate_gscelementor_integation'));

        add_action('wp_ajax_get_google_tab_list_by_sheetname', array($this, 'get_google_tab_list_by_sheetname'));

        //deactivate auth token
        add_action('wp_ajax_deactivate_auth_gscelementor', array($this, 'deactivate_auth_gscelementor'));

        // Add Feed
        add_action('wp_ajax_save_gscelementor_feed', array($this, 'save_gscelementor_feed'));

        add_action('admin_init', array($this, 'execute_post_data_gscelementor'));

        // form feed submit entry in google sheet
        add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_feed'), 10, 2);
        add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_free'), 10, 2);


        // metform: send to entry in the sheet
        add_action('metform_after_store_form_data', array($this, 'send_metform_submission_to_google_sheets_feed'), 10, 2);

        add_action('wp_ajax_sync_google_account_gscelementor', array($this, 'sync_google_account_gscelementor_unified'));

        add_action('wp_ajax_sync_google_account_gscelementor_page', array($this, 'sync_google_account_gscelementor_unified'));

        add_action('wp_ajax_gscelementor_clear_debug_log', array($this, 'gscelementor_clear_debug_log'));

        add_action('wp_ajax_gscelementor_log_elementor_systeminfo', array($this, 'gscelementor_log_elementor_systeminfo'));
    }

    /**
     * AJAX function - clear log file for system status tab
     * @since 2.1
     */
    public function gscelementor_log_elementor_systeminfo()
    {
        // nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        // Initialize WP_Filesystem
        if (! function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        $log_file = WP_CONTENT_DIR . '/debug.log';

        // Clear the log file using WP_Filesystem
        if ($wp_filesystem->exists($log_file) || $wp_filesystem->put_contents($log_file, '', FS_CHMOD_FILE)) {
            $wp_filesystem->put_contents($log_file, '', FS_CHMOD_FILE);
        }

        wp_send_json_success();
    }

    public function gscelementor_clear_debug_log()
    {
        // nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        // Initialize WP_Filesystem
        if (! function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        $existDebugFile = get_option('ele_gs_debug_log_file');
        $clear_file_msg = '';

        // check if debug unique log file exists
        if (! empty($existDebugFile) && $wp_filesystem->exists($existDebugFile)) {
            $wp_filesystem->put_contents($existDebugFile, '', FS_CHMOD_FILE);
            $clear_file_msg = 'Logs are cleared.';
        } else {
            $clear_file_msg = 'No log file exists to clear logs.';
        }

        wp_send_json_success($clear_file_msg);
    }


    /**
     * Function - sync with google account to fetch sheet and tab name
     * Called from multiple contexts (settings page, Elementor editor).
     * modified in version 1.2.3
     * @since 1.0
     */
    public function sync_google_account_gscelementor_unified()
    {
        // Always check nonce
        if (empty($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'gs-ajax-nonce-ele')) {
            wp_send_json_error(array('message' => 'Invalid or missing nonce.'), 403);
        }

        // Always check capability (Admins only by default)
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'), 403);
        }

        // Handle init flag
        $init = isset($_POST['isinit']) ? sanitize_text_field(wp_unslash($_POST['isinit'])) : 'no';

        // Include and auth Google client
        include_once(GS_CONN_ELE_ROOT . '/lib/google-sheets.php');
        $doc = new GSC_Elementor_Free();
        $doc->auth();

        // Fetch spreadsheets
        $spreadsheetFeed = $doc->get_spreadsheets();
        $sheetId_array   = ! empty($spreadsheetFeed) ? array_column($spreadsheetFeed, 'title', 'id') : array();

        update_option('elefgs_sheetId', $sheetId_array);

        // Response
        if ($init === 'yes') {
            wp_send_json_success(array("success" => 'yes'));
        } else {
            wp_send_json_success(array("success" => 'no'));
        }
    }

    /**
     * AJAX function - deactivate activation - Manual
     * @since 1.2
     */
    public function deactivate_auth_gscelementor()
    {
        // nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        if (get_option('elefgs_token') !== '') {
            delete_option('elefgs_feeds');
            delete_option('elefgs_sheetId');
            delete_option('elefgs_token');
            delete_option('elefgs_access_manual_code');
            delete_option('elefgs_verify');

            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function send_metform_submission_to_google_sheets_feed($form_id, $form_data)
    {
        if (empty($form_id) || empty($form_data) || !is_array($form_data)) {
            GsEl_Connector_Utility::ele_gs_debug_log("MetForm GSheet: Invalid form submission — form_id or form_data missing.");
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'postmeta';

        // Fetch feeds linked to this form
        $feeds = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s AND post_id = %d", 'gscele_form_feeds', $form_id)
        );

        if (empty($feeds)) {
            GsEl_Connector_Utility::ele_gs_debug_log("MetForm GSheet: No feeds found for form ID: $form_id");
            return;
        }

        // Clean form data
        $excluded_keys = ['action', 'form_nonce', 'id'];
        $data = [];

        foreach ($form_data as $key => $value) {
            if (in_array($key, $excluded_keys)) {
                continue;
            }

            $label = sanitize_text_field($key);

            if (is_array($value)) {
                $value = implode(',', array_map('esc_html', $value));
            } else {
                $value = esc_html($value);
            }

            $data[$label] = $value;
        }

        foreach ($feeds as $feed) {
            $feed_id = $feed->meta_id;

            $spreadsheetDataRaw = get_post_meta($feed_id, 'gscele_form_feeds', true);
            $spreadsheetData = maybe_unserialize($spreadsheetDataRaw);

            $spreadsheet_id = esc_attr($spreadsheetData['sheet-id'] ?? '');
            $tab_name = esc_attr($spreadsheetData['sheet-tab-name'] ?? '');
            $tab_id = esc_attr($spreadsheetData['tab-id'] ?? '');

            if ($spreadsheet_id != "" && $tab_name != "" && $tab_id != "") {
                try {
                    include_once GS_CONN_ELE_ROOT . '/lib/google-sheets.php';

                    $doc = new GSC_Elementor_Free();
                    $doc->auth();
                    $doc->setSpreadsheetId($spreadsheet_id);
                    $doc->setWorkTabId($tab_id);

                    $local_date = date_i18n(get_option('date_format'));
                    $local_time = date_i18n(get_option('time_format'));

                    // Check if the user has manually added a header for date and time
                    $manual_date_header = isset($meta_values['date_header']) ? $meta_values['date_header'] : 'date';
                    $manual_time_header = isset($meta_values['time_header']) ? $meta_values['time_header'] : 'time';

                    // Pass the date and time to the data array using the headers
                    $data[$manual_date_header] = $local_date;
                    $data[$manual_time_header] = $local_time;

                    $doc->add_row_feed($spreadsheet_id, $tab_name, $data, false);
                } catch (Exception $e) {
                    GsEl_Connector_Utility::ele_gs_debug_log("MetForm GSheet ERROR [Feed ID: $feed_id]: " . $e->getMessage());
                }
            } else {
                GsEl_Connector_Utility::ele_gs_debug_log("MetForm GSheet: Missing Google Sheets config for Feed ID: $feed_id");
            }
        }
    }

    /**
     * form feed submit entry in google sheet.
     *
     * @since 1.0.0
     */
    public function send_form_submission_to_google_sheets_feed($record, $handler)
    {

     
            // Get Elementor form settings and fields
        $gs_ele_settings = $record->get('form_settings');
        $gsele_raw_fields = $record->get('fields');

            $form_id = $gs_ele_settings['form_post_id']; // Get the form ID from Elementor form settings

            global $wpdb;
            $table = $wpdb->prefix . 'postmeta';

            // Fetch associated feeds for the form
            $feeds = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table WHERE meta_value = %s AND post_id = %d", 'gscele_form_feeds', $form_id)
            );

            if (empty($feeds)) {
                return; // Exit early if no feeds are found
            }

            $spreadsheetData = [];
            foreach ($feeds as $feed) {
                $feed_id = $feed->meta_id;

                // Fetch feed configuration data
                $spreadsheetDataRaw = get_post_meta($feed_id, 'gscele_form_feeds', true);
                $spreadsheetData = maybe_unserialize($spreadsheetDataRaw);

                $data = [];

                foreach ($gsele_raw_fields as $field_key => $field_value) {

                    $field_label = $field_value['title'] ?? $field_key;
                    $field_data  = $field_value['value'] ?? '';

                    if (is_array($field_data)) {
                        // File upload or multiple values
                        $data[$field_label] = implode(',', array_map('esc_url_raw', $field_data));
                    } else {
                        // IMPORTANT: remove Elementor/WP slashes and sanitize
                        $field_data = wp_unslash($field_data);
                        $data[$field_label] = sanitize_text_field($field_data);
                    }
                }



                // Extract Google Sheets configuration
                $spreadsheet_id = esc_attr($spreadsheetData['sheet-id'] ?? '');
                $tab_name = esc_attr($spreadsheetData['sheet-tab-name'] ?? '');
                $tab_id = esc_attr($spreadsheetData['tab-id'] ?? '');

                // Send data to Google Sheets for each feed (different tab per feed)
                if ($spreadsheet_id != "" && $tab_name != "" && $tab_id != "") {
                    try {
                        include_once GS_CONN_ELE_ROOT . '/lib/google-sheets.php';
                        $doc = new GSC_Elementor_Free();
                        $doc->auth();
                        $doc->setSpreadsheetId($spreadsheet_id);
                        $doc->setWorkTabId($tab_id);

                        $local_date = date_i18n(get_option('date_format'));
                        $local_time = date_i18n(get_option('time_format'));

                        // Check if the user has manually added a header for date and time
                        $manual_date_header = isset($meta_values['date_header']) ? $meta_values['date_header'] : 'date';
                        $manual_time_header = isset($meta_values['time_header']) ? $meta_values['time_header'] : 'time';

                        // Pass the date and time to the data array using the headers
                        $data[$manual_date_header] = $local_date;
                        $data[$manual_time_header] = $local_time;

                        // Send data to the appropriate tab (ensure correct tab name and tab ID per feed)
                        // error_log("Row Successfully Sent to Google Sheet for Feed ID: " .  $data);
                        $doc->add_row_feed($spreadsheet_id, $tab_name, $data, false);
                    } catch (Exception $e) {
                        GsEl_Connector_Utility::ele_gs_debug_log("Error sending data to Google Sheets for feed ID: $feed_id. " . $e->getMessage());
                    }
                } else {
                    GsEl_Connector_Utility::ele_gs_debug_log("Missing spreadsheet configuration for feed ID: $feed_id");
                }
            }
            
        }

        
        public function send_form_submission_to_google_sheets_free($record, $handler)
        {
            if (is_plugin_active('gsheetconnector-for-elementor-forms/gsheetconnector-for-elementor-forms.php')) {
             

            // Check if run() already executed
                if (class_exists('GSC_Elementor_Actions') && GSC_Elementor_Actions::has_run()) {
                return; // stop further execution
            }

            try {
                // $form_name = $record->get_form_settings('form_id');
                $gs_ele_settings = $record->get('form_settings');
                $form_settings = $record->get('form_settings'); // Form metadata
                $gsele_raw_fields = $record->get('fields');

                $form_id = $gs_ele_settings['form_post_id'];

                global $wpdb;
                $table = $wpdb->prefix . 'postmeta';
                $feeds = $wpdb->get_results("SELECT * FROM $table WHERE meta_value = 'gscele_form_feeds' AND `post_id`= $form_id");

                if (empty($feeds)) {
                    return; // Exit early if no feeds found
                }

                // Get entry ID value
                $query = "SELECT MAX(id) as latest_id FROM {$wpdb->prefix}e_submissions";

                $result = $wpdb->get_results($query);

                $entry_id = $result[0]->latest_id;

                // $form_data = $record->get_formatted_data();

                // Extract field labels and placeholders
                $field_metadata = [];


                if (!empty($form_settings['form_fields'])) {

                    foreach ($form_settings['form_fields'] as $field) {
                        $custom_id = $field['custom_id'] ?? ''; // Field ID
                        $label = $field['field_label'] ?? ''; // Field label
                        $placeholder = $field['placeholder'] ?? ''; // Placeholder


                        // Use label if available, otherwise fallback to placeholder
                        $final_label = !empty($label) ? strip_tags($label) : (!empty($placeholder) ? strip_tags($placeholder) : $custom_id);

                        if (!empty($custom_id)) {
                            $field_metadata[$custom_id] = $final_label;
                        }
                    }
                }


                // Sanitize form data and replace "No Label" with placeholders
                // $form_data = $this->sanitize_form_data($form_data, $field_metadata);
                // Map raw form data keys to their correct labels
                $mapped_form_data = [];
                foreach ($gsele_raw_fields as $key => $field) {
                    $custom_id = $field['id'] ?? $key; // Get correct field identifier
                    $value = $field['value'] ?? ''; // Raw value from form submission

                    // Get the corresponding metadata for this field
                    $final_key = isset($field_metadata[$custom_id]) ? $field_metadata[$custom_id] : $key;

                    foreach ($gsele_raw_fields as $key => $field) {
                        $custom_id = $field['id'] ?? $key; // Get correct field identifier
                        $value = $field['value'] ?? ''; // Raw value from form submission

                        // Get the corresponding metadata for this field
                        $final_key = isset($field_metadata[$custom_id]) ? $field_metadata[$custom_id] : $key;

                        // Log the raw value for debugging


                        // Check if it's a select type
                        // ahmed code 1.0.23
                        if ($field['type'] === 'select' && !empty($form_settings['form_fields'])) {

                            // Get all form fields from metadata
                            foreach ($form_settings['form_fields'] as $meta_field) {
                                // Check if this is the current field
                                if (isset($meta_field['custom_id']) && $meta_field['custom_id'] === $custom_id) {

                                    // Extract options
                                    $options = $meta_field['field_options'] ?? '';

                                    // Log field options before processing


                                    // Handle both array and string cases
                                    if (is_string($options)) {
                                        // Split string by newlines into an array
                                        $options_array = array_filter(array_map('trim', explode("\n", $options)));
                                    } else if (is_array($options)) {
                                        $options_array = $options;
                                    } else {
                                        $options_array = [];
                                    }

                                    // If value is numeric, treat as an index
                                    if (is_numeric($value) && isset($options_array[$value])) {
                                        $final_value = $options_array[$value]; // Get label by index

                                    } else {
                                        // Fallback to raw value if no match
                                        $final_value = $value;
                                    }
                                }
                            }
                        } else {
                            // For non-select fields, just sanitize and use the raw value
                            $final_value = strip_tags($value);
                        }

                        // Map final processed value
                        $mapped_form_data[$final_key] = $final_value;
                    }
                }



                // set the default character set and collation for the table
                $charset_collate = $wpdb->get_charset_collate();
                // Define the table name
                $table_name = $wpdb->prefix . 'elementor_gsheet_submissions_values';

                // SQL query to check if the table exists
                $tableCheck = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
                // Check if the table exists
                $table_exists = $wpdb->get_var($tableCheck);
                if ($table_exists === $table_name) {
                    // Table exists
                    // Check if form_id and feed_id columns exist
                    $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'form_id'");
                    $form_id_exists = !empty($columnsCheck);

                    $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'feed_id'");
                    $feed_id_exists = !empty($columnsCheck);

                    $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'actual_field_name'");
                    $actual_field_exists = !empty($columnsCheck);

                    // Add missing columns
                    if (!$form_id_exists || !$feed_id_exists || !$actual_field_exists) {
                        if (!$form_id_exists) {
                            $wpdb->query("ALTER TABLE $table_name ADD form_id bigint(20) NOT NULL");
                        }
                        if (!$feed_id_exists) {
                            $wpdb->query("ALTER TABLE $table_name ADD feed_id bigint(20) NULL");
                        }
                        if (!$actual_field_exists) {
                            $wpdb->query("ALTER TABLE $table_name ADD actual_field_name varchar(255) NOT NULL");
                        }
                    }
                } else {
                    // Table does not exist
                    // SQL query to create the table
                    $sql = "CREATE TABLE $table_name (
                    id bigint(20) NOT NULL AUTO_INCREMENT,
                    submission_id bigint(20) NOT NULL,
                    form_id bigint(20) NOT NULL,
                    feed_id bigint(20) NULL,
                    field_id varchar(255) NOT NULL,
                    actual_field_name varchar(255) NOT NULL,
                    value varchar(255) NOT NULL,
                    field_label varchar(255) NOT NULL,
                    PRIMARY KEY  (id)
                ) $charset_collate;";

                    // Include the upgrade file
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

                    // Create the table
                dbDelta($sql);
            }

            if (!empty($feeds)) {
                foreach ($feeds as $f) {
                    $meta_id = $f->meta_id;
                    $post_id = $f->post_id;

                    if ($meta_id != "") {
                        $saved_fields = get_post_meta($meta_id, 'gscele_sheet_header');

                        $saved_fields = isset($saved_fields[0]) ? $saved_fields[0] : array();

                        $data = array();

                        if (is_array($saved_fields) && isset($saved_fields['Entry ID'])) {
                            $entryID = array('Entry ID' => $saved_fields['Entry ID']);
                            unset($saved_fields['Entry ID']);
                            $saved_fields = array_merge($entryID, $saved_fields);
                        }

                        foreach ($saved_fields as $field_id => $field_label) {


                            if ($field_id == 'Entry ID' && $field_label == 1) {
                                $data[] = $entry_id;
                            } elseif ($field_id == 'Post ID' && $field_label == 1) {
                                $data[] = isset($f->post_id) ? $f->post_id : "";
                            } elseif ($field_id == 'User IP' && $field_label == 1) {
                                $ip = "";
                                if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
                                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                                } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                                } else {
                                    $ip = $_SERVER['REMOTE_ADDR'];
                                }
                                $data[] = $ip;
                            } elseif ($field_id == 'Entry Date' && $field_label == 1) {
                                $data[] = current_datetime()->format('Y-m-d H:i:s');
                                    // ate_i18n('n/j/Y g:i:s', current_time('timestamp'));

                            } elseif ($field_id == 'User Agent' && $field_label == 1) {
                                $user_agent = "";
                                if (isset($_SERVER['HTTP_USER_AGENT'])) {
                                    $user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
                                }
                                $data[] = $user_agent;
                            } elseif ($field_id == 'User Name' && $field_label == 1) {
                                $current_user = wp_get_current_user();
                                $user_name = $current_user->display_name;

                                $data[] = $user_name;
                            } elseif ($field_id == 'Referrer' && $field_label == 1) {
                                $referrer = '';
                                if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                                    $referrer = $_SERVER['HTTP_REFERER'];
                                } else {
                                    $referrer = $_SERVER['REMOTE_ADDR'];
                                }

                                $data[] = $referrer;
                            } elseif ($field_id == 'User ID' && $field_label == 1) {
                                $user_id = '';
                                if (is_user_logged_in()) {
                                    $current_user = wp_get_current_user();
                                    $user_id = $current_user->ID;
                                }
                                $data[] = $user_id;
                            } elseif ($field_id == 'Form Name' && $field_label == 1) {
                                $form_name = '';

                                if (isset($f->post_id)) {
                                    $elementor_data = get_post_meta($f->post_id, '_elementor_data', true);
                                    $elementor_data = json_decode($elementor_data, true);

                                    if (method_exists($this, 'get_form_name')) {
                                        $form_info = $this->get_form_name($elementor_data);
                                        $form_name = $form_info['form_name'] ?? '';
                                    }
                                }

                                $data[] = $form_name;
                            } else {
                                $data[] = isset($mapped_form_data[$field_id]) ? $mapped_form_data[$field_id] : '';
                            }
                        }

                        if (!empty($data)) {
                            $saved_header_names = get_post_meta($meta_id, 'gscele_sheet_header_names');
                            $saved_header_names = isset($saved_header_names[0]) ? $saved_header_names[0] : array();
                                // Initialize field placeholders array
                            $field_placeholders = [];
                            if (!empty($form_settings['form_fields'])) {
                                foreach ($form_settings['form_fields'] as $field) {
                                    $custom_id = $field['custom_id'] ?? '';
                                    $placeholder = $field['placeholder'] ?? '';

                                    if (!empty($custom_id)) {
                                        $field_placeholders[$custom_id] = $placeholder;
                                    }
                                }
                            }

                                // Initialize matched fields array
                            $matched_fields = [];



                                // Loop through $saved_header_names to find matches
                            foreach ($saved_header_names as $saved_key => $saved_value) {
                                foreach ($gsele_raw_fields as $field_key => $field_data) {
                                        $value = $field_data['value'] ?? ''; // Raw value from form submission

                                        if ($field_data['type'] === 'select' && !empty($form_settings['form_fields'])) {

                                            // Use the field's own ID as the reference for comparison
                                            $current_field_id = $field_data['id'] ?? '';

                                            foreach ($form_settings['form_fields'] as $meta_field) {
                                                if (isset($meta_field['custom_id']) && $meta_field['custom_id'] === $current_field_id) {

                                                    // Get options (string or array)
                                                    $options = $meta_field['field_options'] ?? '';

                                                    if (is_string($options)) {
                                                        $options_array = array_filter(array_map('trim', explode("\n", $options)));
                                                    } elseif (is_array($options)) {
                                                        $options_array = $options;
                                                    } else {
                                                        $options_array = [];
                                                    }

                                                    $final_value = $value; // default fallback

                                                    // Handle both "Option A|1" and plain options
                                                    foreach ($options_array as $opt) {
                                                        $parts = array_map('trim', explode('|', $opt));
                                                        $label = $parts[0] ?? '';
                                                        $opt_value = $parts[1] ?? $label;

                                                        if ((string)$value === (string)$opt_value) {
                                                            // Only append pipe if label and value differ
                                                            $final_value = ($label !== $opt_value)
                                                            ? $label . '|' . $opt_value
                                                            : $label;
                                                            break;
                                                        }
                                                    }

                                                    // Update the field_data with final readable value
                                                    $field_data['value'] = $final_value;
                                                    break;
                                                }
                                            }
                                        }








                                        // Get the field title (label), placeholder, or custom_id
                                        $field_label = !empty($field_data['title']) ? $field_data['title'] : (!empty($field_placeholders[$field_key]) ? $field_placeholders[$field_key] : (!empty($field_data['id']) ? $field_data['id'] : 'Unnamed Field'));

                                        if ($saved_key == $field_label) {
                                            $matched_fields[$saved_value] = $field_data;
                                        }
                                    }
                                }



                                if (!empty($matched_fields)) {
                                    foreach ($matched_fields as $field_name => $field_value) {
                                        // Ensure we get a meaningful field name
                                        $actual_field_name = htmlspecialchars_decode(strip_tags(
                                            !empty($field_value['title']) ? $field_value['title'] : (!empty($field_placeholders[$field_value['id']]) ? $field_placeholders[$field_value['id']] : (!empty($field_value['id']) ? $field_value['id'] : 'Unnamed Field'))
                                        ));

                                        // Insert the field value into the table
                                        $wpdb->insert($table_name, array(
                                            'submission_id' => $entry_id,
                                            'form_id' => $post_id,
                                            'feed_id' => $meta_id,
                                            'field_id' => $field_value['id'],
                                            'actual_field_name' => $actual_field_name,
                                            'value' => $field_value['value'],
                                            'field_label' => $field_name,
                                        ));
                                    }
                                }

                                $spreadsheetData = get_post_meta($meta_id, 'gscele_form_feeds', true);
                                $sort_order = get_post_meta($meta_id, 'gscele_sort_order', true) ?: 'bottom';

                                $spreadsheet_id = isset($spreadsheetData['sheet-id']) ? esc_attr($spreadsheetData['sheet-id']) : '';
                                $tab_name = isset($spreadsheetData['sheet-tab-name']) ? esc_attr($spreadsheetData['sheet-tab-name']) : '';
                                $tab_id = isset($spreadsheetData['tab-id']) ? esc_attr($spreadsheetData['tab-id']) : '';
                                if ($spreadsheet_id != "" && $tab_name != "" && $tab_id != "") {
                                    include_once GS_CONN_ELE_PRO_ROOT . '/lib/google-sheets.php';
                                    $doc = new GSC_Elementor_Free();
                                    $doc->auth();
                                    $doc->setSpreadsheetId($spreadsheet_id);
                                    $doc->setWorkTabId($tab_id);
                                    $sort_order = get_post_meta($meta_id, 'gscele_sort_order', true) ?: 'bottom';

                                    $doc->add_row_to_sheet($spreadsheet_id, $tab_name, $data, false, $sort_order);
                                }
                            }
                        }
                    }
                }
            } catch (Exeption $e) {
                $data['ERROR_MSG'] = $e->getMessage();
                $data['TRACE_STK'] = $e->getTraceAsString();
                GsEl_Connector_Utility::ele_gs_debug_log($data);
                return;
            }
        }
    }


    /**
     * Save feed settings in the database.
     *
     * @since 1.0.0
     */
    public function execute_post_data_gscelementor()
    {
        try {
            if (isset($_POST['execute-edit-feed-elementor'])) {
                // Nonce check
                if (!wp_verify_nonce($_POST['gs-ajax-nonce'], 'gs-ajax-nonce')) {
                    wp_die('Invalid nonce'); // Die with an error message if nonce fails verification
                }

                // Check if the user is logged in and has permissions to edit feeds
                if (!is_user_logged_in() || !current_user_can('edit_posts')) {
                    echo 'You do not have permission to edit feeds.';
                    exit;
                }

                // Get the feed ID and form ID from the form
                $feed_id = isset($_POST['feed_id']) ? sanitize_text_field($_POST['feed_id']) : "";
                $form_id = isset($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : "";

                // Get custom sheet name and tab name as per manual checkbox selection
                $sheet_name_custom = isset($_POST['elementor-gs']['sheet-name-custom']) ? stripslashes($_POST['elementor-gs']['sheet-name-custom']) : "";
                $tab_name_custom = isset($_POST['elementor-gs']['sheet-tab-name-custom']) ? stripslashes($_POST['elementor-gs']['sheet-tab-name-custom']) : "";
                $sheet_id_custom = isset($_POST['elementor-gs']['sheet-id-custom']) ? $_POST['elementor-gs']['sheet-id-custom'] : "";
                $tab_id_custom = isset($_POST['elementor-gs']['tab-id-custom']) ? $_POST['elementor-gs']['tab-id-custom'] : "";



                // Update the feed data in the database
                if ($feed_id !== "" && $sheet_name_custom !== "" && $sheet_id_custom !== "" && $tab_name_custom !== "" && $tab_id_custom !== "") {
                    $meta_key = 'gscele_form_feeds';
                    $meta_value = array(
                        'sheet-name' => $sheet_name_custom,
                        'sheet-id' => $sheet_id_custom,
                        'sheet-tab-name' => $tab_name_custom,
                        'tab-id' => $tab_id_custom,
                    );

                    update_post_meta($feed_id, $meta_key, $meta_value);

                    $success_message = __('Settings saved successfully.', 'gsheetconnector-for-elementor-forms');
                }
            }
        } catch (Exception $e) {
            GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
            throw new LogicException("Error saving feed: " . $e->getMessage());
        }
    }

    // Add Feed Name Function
    public function save_gscelementor_feed()
    {

        // nonce checksave_avadaforms_gs_settings
        check_ajax_referer('elementorform-ajax-nonce', 'security');

        /* sanitize incoming data */
        $feedName = sanitize_text_field($_POST['feed_name']);
        $elementorForms = sanitize_text_field($_POST['elementorForms']);

        $message = '';
        if (isset($feedName) && isset($elementorForms) && !empty($feedName) && !empty($elementorForms)) {
            /*check same name feed exist or not */
            $feed_check = get_post_meta($_POST['elementorForms'], $_POST['feed_name'], 'gscele_form_feeds');

            if (empty($feed_check)) {
                update_post_meta($_POST['elementorForms'], $_POST['feed_name'], 'gscele_form_feeds');
                $message .= 'Feed has been successfully created.';
            } else {
                $message .= 'Feed name already exists in the list, Please enter unique name of feed.';
            }
            wp_send_json_success($message);
        }
    }


    /**
     * Deleting Feed.
     *
     * @since 1.0.0
     */
    public function delete_feed()
    {
        try {
            // Nonce verification
            check_ajax_referer('elementorform-ajax-nonce', 'security');

            // Validate and sanitize input
            $feed_id = isset($_POST['feed_id']) ? intval(wp_unslash($_POST['feed_id'])) : 0;

            if ($feed_id) {
                $deleted  = delete_metadata('post', $feed_id, 'gscele_form_feeds');
                $deleted1 = delete_metadata_by_mid('post', $feed_id);

                if ($deleted1) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            } else {
                echo 'invalid';
            }

            wp_die();
        } catch (Exception $e) {
            // Logging the exception properly
            if (class_exists('GsEl_Connector_Utility')) {
                GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
            }
            wp_die('error');
        }
    }

    /**
     * AJAX function - deactivate activation
     * @since 1.4
     */
    public function deactivate_gscelementor_integation()
    {
        // nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        if (get_option('elefgs_token') !== '') {
            //delete_option('gs_feeds');
            delete_option('elefgs_sheetId');
            delete_option('elefgs_token');
            delete_option('elefgs_access_code');
            delete_option('elefgs_verify');

            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * AJAX function - verifies the token
     * @since 1.0
     */
    public function verify_gscelementor_integation()
    {
        // nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        /* validate and sanitize incoming data */
        if (isset($_POST['code'])) {
            $Code = sanitize_text_field(wp_unslash($_POST['code']));
        } else {
            wp_send_json_error('Missing code.');
            return;
        }

        if (! empty($Code)) {
            update_option('elefgs_access_code', $Code);
        } else {
            wp_send_json_error('Empty code.');
            return;
        }

        if (get_option('elefgs_access_code') !== '') {
            include_once(GS_CONN_ELE_ROOT . '/lib/google-sheets.php');
            GSC_Elementor_Free::preauth(get_option('elefgs_access_code'));
            wp_send_json_success();
        } else {
            update_option('elefgs_verify', 'invalid');
            wp_send_json_error();
        }
    }



    // old settings get forms list
    // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
    public function get_forms_connected_to_sheet()
    {
        global $wpdb;

        $cache_key = 'gsc_ele_connected_forms';
        $results = wp_cache_get($cache_key, 'gsc_plugin');

        if (false === $results) {
            $table_posts    = $wpdb->prefix . 'posts';
            $table_postmeta = $wpdb->prefix . 'postmeta';

            $query = "
            SELECT p.ID, p.post_title, pm.meta_value, pm.meta_key
            FROM {$table_posts} AS p
            JOIN {$table_postmeta} AS pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '__elementor_forms_snapshot'
            AND p.post_type = 'page'
            ";

            $results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $results, 'gsc_plugin', 300);
        }

        return $results;
    }
    // phpcs:enable

    // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
    public function get_forms_feeds_connected_to_sheet()
    {
        global $wpdb;

        $cache_key = 'gsc_ele_feed_connected_forms';
        $results = wp_cache_get($cache_key, 'gsc_plugin');

        if (false === $results) {
            $table_posts    = $wpdb->prefix . 'posts';
            $table_postmeta = $wpdb->prefix . 'postmeta';

            $query = "
            SELECT p.ID, p.post_title, pm.meta_value, pm.meta_key, pm.meta_id
            FROM {$table_posts} AS p
            JOIN {$table_postmeta} AS pm ON p.ID = pm.post_id
            WHERE pm.meta_value = 'gscele_form_feeds'
            AND p.post_type = 'page'
            ";

            $results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $results, 'gsc_plugin', 300);
        }

        return $results;
    }
    // phpcs:enable


    /**
     * AJAX function - Fetch tab list by sheet name
     * @since 1.0
     */
    public function get_google_tab_list_by_sheetname()
    {

        // Nonce check
        check_ajax_referer('gs-ajax-nonce-ele', 'security');

        // Validate and sanitize POST data
        $spreadsheet_id = isset($_POST['sheetname']) ? sanitize_text_field(wp_unslash($_POST['sheetname'])) : '';
        $refresh        = isset($_POST['refresh']) ? sanitize_text_field(wp_unslash($_POST['refresh'])) : '';

        $temp1           = array();
        $TabsId_array1   = array();
        $TabsId_array    = array();
        $divifgs_sheetTabs = get_option('elefgs_tabsId');

        include_once(GS_CONN_ELE_ROOT . "/lib/google-sheets.php");
        $doc = new GSC_Elementor_Free();
        $doc->auth();

        $TabsId_array = $doc->get_worktabs($spreadsheet_id);

        // Refresh logic
        if ($refresh == '1') {
            $temp1[$spreadsheet_id] = $divifgs_sheetTabs;
            update_option('elefgs_tabsId', $temp1);
        } else {
            if (empty($divifgs_sheetTabs)) {
                $temp1[$spreadsheet_id] = $TabsId_array;
                update_option('elefgs_tabsId', $temp1);
            } else {
                $TabsId_array1[$spreadsheet_id] = $TabsId_array;
                $temp = array_merge($divifgs_sheetTabs, $TabsId_array1);
                update_option('elefgs_tabsId', $temp);
            }
        }

        // Return the final updated data
        $divifgs_sheetTabs = get_option('elefgs_tabsId');
        wp_send_json_success($divifgs_sheetTabs);
    }
}

$gsc_elementor_integration = new GSC_Elementor_Integration();