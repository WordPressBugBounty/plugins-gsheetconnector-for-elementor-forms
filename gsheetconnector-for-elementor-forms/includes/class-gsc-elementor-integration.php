<?php

/**
 * Integration class for Google Sheet Connector
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals
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

        if (! get_option('gselef_debug_migrated_pro')) {
            $this->maybe_migrate_gselef_debug_log();
        }

        if (! get_option('gselef_feed_status_migrated_pro')) {
            $this->maybe_migrate_gselef_feed_status();
        }

        add_action('wp_ajax_verify_gscelementor_integation', array($this, 'verify_gscelementor_integation'));
        add_action('wp_ajax_deactivate_gscelementor_integation', array($this, 'deactivate_gscelementor_integation'));

        add_action('wp_ajax_get_google_tab_list_by_sheetname', array($this, 'get_google_tab_list_by_sheetname'));

        //deactivate auth token
        add_action('wp_ajax_deactivate_auth_gscelementor', array($this, 'deactivate_auth_gscelementor'));

        // Add Feed
        add_action('wp_ajax_save_gscelementor_feed', array($this, 'save_gscelementor_feed'));

        // Delete Feed
        add_action('wp_ajax_gselef_free_delete_feed', array($this, 'gselef_free_delete_feed'));

        // metform: send to entry in the sheet
        add_action('metform_after_store_form_data', array($this, 'send_metform_submission_to_google_sheets_feed'), 10, 2);

        add_action('wp_ajax_sync_google_account_gscelementor', array($this, 'sync_google_account_gscelementor_unified'));

        add_action('wp_ajax_sync_google_account_gscelementor_page', array($this, 'sync_google_account_gscelementor_unified'));

        add_action('wp_ajax_gscelementor_clear_debug_log', array($this, 'gscelementor_clear_debug_log'));

        add_action('wp_ajax_gscelementor_log_elementor_systeminfo', array($this, 'gscelementor_log_elementor_systeminfo'));

        // reset feed
        add_action('wp_ajax_gselef_free_reset_feed', array($this, 'gselef_free_reset_feed'));
        // status update in feed 
        add_action('wp_ajax_gselef_update_status', array($this, 'gselef_update_status'));

        add_action('wp_ajax_gscele_save_uninstall_settings', array($this, 'gscele_save_uninstall_settings'));

        /* dismiss  notification */
        add_action('wp_ajax_gselef_free_dismiss_notice', array($this, 'gselef_free_dismiss_notice_callback'));

        /* snooze notitiacation  */
        add_action('wp_ajax_gselef_free_snooze_notice', array($this, 'gselef_free_snooze_notice_callback'));

        // pro dismiss notice
        add_action('wp_ajax_gselef_dismiss_pro_notice', array($this, 'gselef_dismiss_pro_notice'));

        add_action('admin_init', array($this, 'execute_post_data_gscelementor'));

        // form feed submit entry in google sheet
        add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_feed'), 10, 2);

        // add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_free'), 10, 2);
    }



    /**
     * Handle AJAX request to dismiss the PRO notice.
     *
     * This function:
     * - Verifies the AJAX nonce for security.
     * - Sets a browser cookie to remember that the notice is dismissed.
     * - Cookie is valid for 7 days.
     * - Returns a JSON success or error response.
     *
     * @return void
     */
    public function gselef_dismiss_pro_notice()
    {

       $nonce = isset( $_POST['nonce'] )
       ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) )
       : '';

       if ( ! wp_verify_nonce( $nonce, 'gselef-ajax-nonce' ) ) {
        wp_send_json_error( 'Invalid nonce' );
    }

    setcookie(
        'gselef_pro_notice_dismissed',
        '1',
        time() + (7 * 24 * 60 * 60),
        COOKIEPATH,
        COOKIE_DOMAIN
    );

    wp_send_json_success();
}

    /**
     * Handle dismiss action for admin notices.
     *
     * Verifies AJAX nonce, validates the notice key,
     * and stores the dismissed status in WordPress options.
     *
     * @return void
     */
    public function gselef_free_dismiss_notice_callback() {

        $security = isset( $_POST['security'] )
        ? sanitize_text_field( wp_unslash( $_POST['security'] ) )
        : '';

        if ( ! wp_verify_nonce( $security, 'gselef-ajax-nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if ( ! isset( $_POST['key'] ) ) {
            wp_send_json_error( 'Missing key' );
        }

        $key = sanitize_text_field( wp_unslash( $_POST['key'] ) );

        update_option( 'elefgs_free_notice_' . $key, 'dismissed' );

        wp_send_json_success();
    }

    /**
     * Handle snooze action for admin notices.
     *
     * Verifies AJAX nonce, validates the notice key,
     * and stores the current timestamp to temporarily hide the notice.
     *
     * @return void
     */
    public function gselef_free_snooze_notice_callback()
    {
        $security = isset( $_POST['security'] )
        ? sanitize_text_field( wp_unslash( $_POST['security'] ) )
        : '';

        if ( ! wp_verify_nonce( $security, 'gselef-ajax-nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if (!isset($_POST['key'])) {
            wp_send_json_error('Missing key');
        }
        $key = sanitize_text_field( wp_unslash( $_POST['key'] ) );
        update_option('elefgs_free_notice_' . $key . '_time', time());
        wp_send_json_success();
    }


    /**
     * AJAX handler to save uninstall settings.
     *
     * This function updates the plugin uninstall setting based on user input
     * received via AJAX request from the admin panel.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success or error response.
     */
 /**
 * Save uninstall settings option via AJAX.
 *
 * This function validates the AJAX request, checks user permissions,
 * sanitizes the uninstall setting value, and stores the preference
 * in the WordPress options table.
 *
 * If enabled, plugin settings/data will be removed during uninstall.
 *
 * @since 1.0.0
 *
 * @return void Sends JSON success or error response.
 */
 public function gscele_save_uninstall_settings()
 {

    check_ajax_referer(
        'gscele-elementor-setting-ajax-nonce',
        'security'
    );

    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error(
            esc_html__(
                'You do not have permission to perform this action.',
                'gsheetconnector-for-elementor-forms'
            )
        );
    }

    // Get uninstall setting value
    $value = isset($_POST['uninstall_setting'])
    ? intval(wp_unslash($_POST['uninstall_setting']))
    : 0;

    // Convert value into Yes/No
    $setting = ($value === 1) ? 'Yes' : 'No';

    // Save option
    update_option(
        'gscele_elementor_uninstall_settings_free',
        $setting
    );

    wp_send_json_success(
        esc_html__(
            'Uninstall settings saved successfully.',
            'gsheetconnector-for-elementor-forms'
        )
    );
}

    /**
     * AJAX handler to update feed status.
     *
     * This function updates the status (active/inactive) of a specific feed
     * using AJAX request from the admin panel.
     *
     * @since 1.3.0
     *
     * @return void Sends JSON success or error response.
     */
    public function gselef_update_status()
    {

        check_ajax_referer('elementorform-ajax-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;
        $status  = isset($_POST['status']) ? intval($_POST['status']) : 0;

        if ($feed_id) {

            update_post_meta($feed_id, 'gselef_status', $status);

            wp_send_json_success();
        }

        wp_send_json_error();
    }

    /**
     * AJAX handler to reset feed data.
     *
     * This function deletes the stored feed configuration (post meta)
     * for a specific feed via an AJAX request from the admin panel.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success or error response.
     */
    public function gselef_free_reset_feed()
    {

        check_ajax_referer('gs-ajax-nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;

        if ($feed_id) {

            delete_post_meta($feed_id, 'gscele_form_feeds');

            wp_send_json_success('Feed reset successfully');
        }

        wp_send_json_error('Invalid feed id');
    }

    /**
     * AJAX handler to clear the debug log file from the system status tab.
     *
     * This function verifies the request, initializes the WordPress filesystem API,
     * and clears the contents of the debug.log file located in the wp-content directory.
     *
     * @since 2.1
     *
     * @return void Sends JSON success response.
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

    /**
     * AJAX handler to clear the custom debug log file.
     *
     * This function verifies the AJAX request, initializes the WordPress filesystem API,
     * checks if a custom debug log file exists (stored in options), and clears its contents.
     * Returns a success response with an appropriate message.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success response with status message.
     */
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
     * AJAX handler to sync Google account and fetch spreadsheet data.
     *
     * This function verifies the AJAX request, checks user permissions,
     * authenticates the Google account, retrieves available spreadsheets,
     * and stores their IDs and titles in WordPress options.
     *
     * It supports an initialization flag (`isinit`) to differentiate between
     * initial sync and subsequent sync requests.
     *
     * @since 1.0.0
     * @modified 1.2.3
     *
     * @return void Sends JSON success or error response with sync status.
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
     * AJAX handler to deactivate Google account authentication (manual method).
     *
     * This function verifies the AJAX request, checks if an authentication token exists,
     * and removes all related stored options including token, sheet data, and verification details.
     * It is used to disconnect the manually authenticated Google account from the plugin.
     *
     * @since 1.2.0
     *
     * @return void Sends JSON success if deactivated, otherwise error response.
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

    /**
 * AJAX handler to create a new Elementor feed.
 *
 * This function validates the AJAX request, sanitizes incoming data,
 * checks for duplicate feed names for a specific form, and creates
 * a new feed entry by storing it in post meta.
 *
 * Backward compatibility:
 * - Old feeds store only: 'gscele_form_feeds'
 * - New feeds store:
 *   array(
 *      'type'       => 'gscele_form_feeds',
 *      'element_id' => 'xxxx'
 *   )
 *
 * @since 1.0.0
 *
 * @return void Sends JSON response.
 */
    public function save_gscelementor_feed()
    {
        check_ajax_referer('elementorform-ajax-nonce', 'security');

        if (!current_user_can('manage_options')) {
            echo 'error';
            wp_die();
        }

        $feedName = isset($_POST['feed_name'])
        ? sanitize_text_field(wp_unslash($_POST['feed_name']))
        : '';

        $elementorForms = isset($_POST['elementorForms'])
        ? sanitize_text_field(wp_unslash($_POST['elementorForms']))
        : '';

    /* ------------------------------------------
     * Backward compatibility support
     * Old format  : post_id
     * New format  : post_id|element_id
     * ------------------------------------------ */
    $post_id    = 0;
    $element_id = '';

    if (!empty($elementorForms)) {

        if (strpos($elementorForms, '|') !== false) {

            list($post_id, $element_id) = explode('|', $elementorForms);

            $post_id    = intval($post_id);
            $element_id = sanitize_text_field($element_id);

        } else {

            // Old feeds support
            $post_id = intval($elementorForms);
        }
    }

    if (!empty($feedName) && !empty($post_id)) {

        /* Check existing feed */
        $feed_check = get_post_meta($post_id, $feedName, true);

        if (empty($feed_check)) {

            /* ------------------------------------------
             * New feed structure
             * ------------------------------------------ */
            $meta_value = array(
                'type'       => 'gscele_form_feeds',
                'element_id' => $element_id,
            );

            // Save feed
            update_post_meta($post_id, $feedName, $meta_value);

            global $wpdb;

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
            $meta_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT meta_id FROM {$wpdb->postmeta}
                    WHERE post_id = %d AND meta_key = %s
                    ORDER BY meta_id DESC LIMIT 1",
                    $post_id,
                    $feedName
                )
            );

            // Update feed status
            update_post_meta($meta_id, 'gselef_status', 1);

            echo 'success';

        } else {

            echo 'error';
        }

    } else {

        echo 'error';
    }

    wp_die();
}

    /**
     * AJAX handler to fetch Google Sheets tab list by spreadsheet ID.
     *
     * This function verifies the AJAX request, retrieves the provided
     * spreadsheet ID, authenticates with Google, and fetches the list
     * of tabs (work sheets) for that spreadsheet.
     *
     * It also manages caching of tab data in WordPress options and supports
     * a refresh mechanism to update stored tab information.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success response with updated tab list data.
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
    /**
     * AJAX handler to delete a feed.
     *
     * This function verifies the AJAX request using nonce validation,
     * sanitizes the incoming feed ID, and deletes the associated
     * feed metadata from the database.
     *
     * It attempts to remove both the meta entry by key and by meta ID,
     * and returns a JSON response indicating success or failure.
     * Any exceptions are logged for debugging purposes.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success or error response.
     */
    public function gselef_free_delete_feed()
    {
        try {
            // Nonce verification
            check_ajax_referer('elementorform-ajax-nonce', 'security');

            // Validate and sanitize input
            $feed_id = isset($_POST['feed_id']) ? intval(wp_unslash($_POST['feed_id'])) : 0;

            if ($feed_id) {
                /* Delete post meta using meta ID */
                $deleted = delete_metadata_by_mid('post', $feed_id);

                /* Output response based on deletion result */
                if ($deleted) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            }
            /* Properly terminate AJAX request */
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
     * AJAX handler to deactivate Google Sheets integration.
     *
     * This function verifies the AJAX request, checks if an authentication token exists,
     * and removes all related stored options including sheet data, access token,
     * and verification details to fully disconnect the integration.
     *
     * @since 1.4.0
     *
     * @return void Sends JSON success if deactivated, otherwise error response.
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
     * AJAX handler to verify Google Sheets integration token.
     *
     * This function validates the AJAX request, sanitizes the provided
     * authorization code, stores it in the database, and attempts to
     * authenticate with Google using the provided token.
     *
     * If verification is successful, it initializes the authentication process;
     * otherwise, it marks the verification as invalid and returns an error response.
     *
     * @since 1.0.0
     *
     * @return void Sends JSON success or error response.
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
            update_option('elefgs_verify', 'invalid-auth');
            wp_send_json_error();
        }
    }

    /**
     * Send MetForm submission data to Google Sheets based on configured feeds.
     *
     * This function processes form submission data, maps it into a structured format,
     * retrieves associated Google Sheet feed configurations, and appends the data
     * as a new row in the respective Google Sheet.
     *
     * It also logs errors and handles missing configurations gracefully.
     *
     * @since 1.0.0
     *
     * @param int   $form_id   The ID of the submitted form.
     * @param array $form_data The submitted form data.
     *
     * @return void
     */
    public function send_metform_submission_to_google_sheets_feed($form_id, $form_data)
    {
        if (empty($form_id) || empty($form_data) || !is_array($form_data)) {
            GsEl_Connector_Utility::ele_gs_debug_log("MetForm GSheet: Invalid form submission — form_id or form_data missing.");
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'postmeta';

      // Old users + New users support
      // Old => meta_value = 'gscele_form_feeds'
      // New => serialized array containing type => gscele_form_feeds

       // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching
        $feeds = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->postmeta}
                WHERE post_id = %d
                AND (
                meta_value = %s
                OR meta_value LIKE %s
            )",
            $form_id,
            'gscele_form_feeds',
            '%gscele_form_feeds%'
        )
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

            $gselef_update_status = get_post_meta($feed_id, 'gselef_status', true);

            // Only process if status is enabled (1)
            if (intval($gselef_update_status) !== 1) {
                continue;
            }

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
                    $data['Entry Date'] = $local_date;
                    $data['Date'] = $local_date;
                    $data['Submission Date'] = $local_date;

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
 * Send Elementor form submission data to Google Sheets based on configured feeds.
 *
 * This function retrieves submitted form data from Elementor, processes and sanitizes
 * the field values, maps them into a structured format, and sends the data to the
 * respective Google Sheet and tab configured for each feed.
 *
 * It supports multiple feeds per form, handles file/multi-value fields, and appends
 * date and time fields before inserting the row into Google Sheets.
 *
 * Old users (post_id only) and new users (post_id + element_id) are both supported.
 *
 * @since 1.0.0
 *
 * @param object $record  The Elementor form record object containing submitted data.
 * @param object $handler The Elementor handler object.
 *
 * @return void
 */
  public function send_form_submission_to_google_sheets_feed($record, $handler)
  {

    // Get Elementor form settings and fields
    $gs_ele_settings = $record->get('form_settings');
    $gsele_raw_fields = $record->get('fields');

    // Get current page/post ID
    $form_id = isset($gs_ele_settings['form_post_id'])
    ? absint($gs_ele_settings['form_post_id'])
    : 0;

    // Get current submitted Elementor form element ID
    $current_element_id = isset($gs_ele_settings['id'])
    ? sanitize_text_field($gs_ele_settings['id'])
    : '';

    global $wpdb;

    $table = $wpdb->prefix . 'postmeta';

    // Old users + New users support
    // Old => meta_value = 'gscele_form_feeds'
    // New => serialized array containing type => gscele_form_feeds

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    $feeds = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->postmeta}
            WHERE post_id = %d
            AND (
            meta_value = %s
            OR meta_value LIKE %s
        )",
        $form_id,
        'gscele_form_feeds',
        '%gscele_form_feeds%'
    )
    );

    // No feeds found
    if (empty($feeds)) {
        return;
    }

    foreach ($feeds as $feed) {

        // -----------------------------------------------------------------
        // OLD + NEW USER SUPPORT
        // -----------------------------------------------------------------

        $feed_meta = maybe_unserialize($feed->meta_value);

        // New users => array structure
        if (is_array($feed_meta) && !empty($feed_meta['element_id'])) {

            // Skip non-matching Elementor forms
            if ($feed_meta['element_id'] !== $current_element_id) {
                continue;
            }
        }

        // Old users => no element_id
        // Continue automatically without filtering

        $feed_id = $feed->meta_id;

        // Fetch feed configuration data
        $spreadsheetDataRaw = get_post_meta($feed_id, 'gscele_form_feeds', true);

        $gselef_update_status = get_post_meta($feed_id, 'gselef_status', true);

        // Only process enabled feeds
        if (intval($gselef_update_status) !== 1) {
            continue;
        }

        $spreadsheetData = maybe_unserialize($spreadsheetDataRaw);

        $data = array();

        // -----------------------------------------------------------------
        // Process submitted form fields
        // -----------------------------------------------------------------
        foreach ($gsele_raw_fields as $field_key => $field_value) {

            $field_label = $field_value['title'] ?? $field_key;
            $field_data  = $field_value['value'] ?? '';

            // File upload / checkbox / multi values
            if (is_array($field_data)) {

                $data[$field_label] = implode(
                    ',',
                    array_map('esc_url_raw', $field_data)
                );

            } else {

                $field_data = wp_unslash($field_data);

                $data[$field_label] = sanitize_text_field($field_data);
            }
        }

        // -----------------------------------------------------------------
        // Spreadsheet config
        // -----------------------------------------------------------------
        $spreadsheet_id = esc_attr($spreadsheetData['sheet-id'] ?? '');

        $tab_name = esc_attr($spreadsheetData['sheet-tab-name'] ?? '');

        $tab_id = esc_attr($spreadsheetData['tab-id'] ?? '');


        // Step 4: Prepare data
        $latest_id = wp_cache_get('gsc_latest_elementor_id');
        if (false === $latest_id) {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $result = $wpdb->get_results("SELECT MAX(id) as latest_id FROM {$wpdb->prefix}e_submissions");
            $latest_id = isset($result[0]->latest_id) ? $result[0]->latest_id : '';
            wp_cache_set('gsc_latest_elementor_id', $latest_id, '', 300); // Cache for 5 minutes
        }

        // -----------------------------------------------------------------
        // Send data to Google Sheets
        // -----------------------------------------------------------------
        if ($spreadsheet_id !== '' && $tab_name !== '' && $tab_id !== '') {

            try {

                include_once GS_CONN_ELE_ROOT . '/lib/google-sheets.php';

                $doc = new GSC_Elementor_Free();

                $doc->auth();

                $doc->setSpreadsheetId($spreadsheet_id);

                $doc->setWorkTabId($tab_id);

                          // Local date/time
                $local_date = date_i18n(get_option('date_format'));

                $local_time = date_i18n(get_option('time_format'));

                        // Default headers
                       // Add date & time
                $data['Entry ID'] = $latest_id;
                $data['date'] = $local_date;
                $data['Entry Date'] = $local_date;
                $data['Submission Date'] = $local_date;
                $data['Date'] = $local_date;
                $data['time'] = $local_time;

                // Insert row
                $doc->add_row_feed(
                    $spreadsheet_id,
                    $tab_name,
                    $data,
                    false
                );

            } catch (Exception $e) {

                GsEl_Connector_Utility::ele_gs_debug_log(
                    'Error sending data to Google Sheets for feed ID: '
                    . $feed_id . '. '
                    . $e->getMessage()
                );
            }

        } else {

            GsEl_Connector_Utility::ele_gs_debug_log(
                'Missing spreadsheet configuration for feed ID: '
                . $feed_id
            );
        }
    }
}

    /**
     * Send Elementor form submission data to Google Sheets (Free version compatibility).
     *
     * This function handles Elementor form submissions when the free version of the plugin
     * is active. It retrieves form data, maps fields with proper labels/placeholders,
     * processes special field types (e.g., select fields), and prepares structured data.
     *
     * It ensures:
     * - Duplicate execution is prevented.
     * - Feed configurations are fetched and validated.
     * - Form submission data is sanitized and mapped correctly.
     * - Required database table is created/updated if missing.
     * - Submission data is stored locally for tracking.
     * - Data is sent to the configured Google Sheet and tab.
     *
     * Additionally, it supports advanced fields like Entry ID, User Info, Form Name,
     * and maintains compatibility with saved sheet headers and sort order.
     *
     * @since 1.0.0
     *
     * @param object $record  Elementor form record object containing submission data.
     * @param object $handler Elementor handler object (hook context).
     *
     * @return void
     */
    // public function send_form_submission_to_google_sheets_free($record, $handler)
    // {
    //     if (is_plugin_active('gsheetconnector-for-elementor-forms/gsheetconnector-for-elementor-forms.php')) {


    //         // Check if run() already executed
    //         if (class_exists('GSC_Elementor_Actions') && GSC_Elementor_Actions::has_run()) {
    //             return; // stop further execution
    //         }

    //         try {
    //             // $form_name = $record->get_form_settings('form_id');
    //             $gs_ele_settings = $record->get('form_settings');
    //             $form_settings = $record->get('form_settings'); // Form metadata
    //             $gsele_raw_fields = $record->get('fields');

    //             $form_id = $gs_ele_settings['form_post_id'];

    //             global $wpdb;
    //             $table = $wpdb->prefix . 'postmeta';
    //             // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    //             $feeds = $wpdb->get_results("SELECT * FROM $table WHERE meta_value = 'gscele_form_feeds' AND `post_id`= $form_id");

    //             if (empty($feeds)) {
    //                 return; // Exit early if no feeds found
    //             }
    //             // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    //             $query = $wpdb->prepare(
    //                 "SELECT MAX(id) as latest_id FROM {$wpdb->prefix}e_submissions"
    //             );

    //             $result = $wpdb->get_results($query);

    //             $entry_id = $result[0]->latest_id;

    //             // $form_data = $record->get_formatted_data();

    //             // Extract field labels and placeholders
    //             $field_metadata = [];


    //             if (!empty($form_settings['form_fields'])) {

    //                 foreach ($form_settings['form_fields'] as $field) {
    //                     $custom_id = $field['custom_id'] ?? ''; // Field ID
    //                     $label = $field['field_label'] ?? ''; // Field label
    //                     $placeholder = $field['placeholder'] ?? ''; // Placeholder


    //                     // Use label if available, otherwise fallback to placeholder
    //                     $final_label = !empty($label) ? wp_strip_all_tags($label) : (!empty($placeholder) ? wp_strip_all_tags($placeholder) : $custom_id);

    //                     if (!empty($custom_id)) {
    //                         $field_metadata[$custom_id] = $final_label;
    //                     }
    //                 }
    //             }


    //             // Sanitize form data and replace "No Label" with placeholders
    //             // $form_data = $this->sanitize_form_data($form_data, $field_metadata);
    //             // Map raw form data keys to their correct labels
    //             $mapped_form_data = [];
    //             foreach ($gsele_raw_fields as $key => $field) {
    //                 $custom_id = $field['id'] ?? $key; // Get correct field identifier
    //                 $value = $field['value'] ?? ''; // Raw value from form submission

    //                 // Get the corresponding metadata for this field
    //                 $final_key = isset($field_metadata[$custom_id]) ? $field_metadata[$custom_id] : $key;

    //                 foreach ($gsele_raw_fields as $key => $field) {
    //                     $custom_id = $field['id'] ?? $key; // Get correct field identifier
    //                     $value = $field['value'] ?? ''; // Raw value from form submission

    //                     // Get the corresponding metadata for this field
    //                     $final_key = isset($field_metadata[$custom_id]) ? $field_metadata[$custom_id] : $key;

    //                     // Log the raw value for debugging


    //                     // Check if it's a select type
    //                     // ahmed code 1.0.23
    //                     if ($field['type'] === 'select' && !empty($form_settings['form_fields'])) {

    //                         // Get all form fields from metadata
    //                         foreach ($form_settings['form_fields'] as $meta_field) {
    //                             // Check if this is the current field
    //                             if (isset($meta_field['custom_id']) && $meta_field['custom_id'] === $custom_id) {

    //                                 // Extract options
    //                                 $options = $meta_field['field_options'] ?? '';

    //                                 // Log field options before processing


    //                                 // Handle both array and string cases
    //                                 if (is_string($options)) {
    //                                     // Split string by newlines into an array
    //                                     $options_array = array_filter(array_map('trim', explode("\n", $options)));
    //                                 } else if (is_array($options)) {
    //                                     $options_array = $options;
    //                                 } else {
    //                                     $options_array = [];
    //                                 }

    //                                 // If value is numeric, treat as an index
    //                                 if (is_numeric($value) && isset($options_array[$value])) {
    //                                     $final_value = $options_array[$value]; // Get label by index

    //                                 } else {
    //                                     // Fallback to raw value if no match
    //                                     $final_value = $value;
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         // For non-select fields, just sanitize and use the raw value
    //                         $final_value = wp_strip_all_tags($value);
    //                     }

    //                     // Map final processed value
    //                     $mapped_form_data[$final_key] = $final_value;
    //                 }
    //             }



    //             // set the default character set and collation for the table
    //             $charset_collate = $wpdb->get_charset_collate();
    //             // Define the table name
    //             $table_name = $wpdb->prefix . 'elementor_gsheet_submissions_values';

    //             // SQL query to check if the table exists
    //             $tableCheck = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
    //             // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    //             $table_exists = $wpdb->get_var($tableCheck);
    //             if ($table_exists === $table_name) {
    //                 // Table exists
    //                 // Check if form_id and feed_id columns exist
    //                 $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'form_id'");
    //                 $form_id_exists = !empty($columnsCheck);

    //                 $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'feed_id'");
    //                 $feed_id_exists = !empty($columnsCheck);

    //                 $columnsCheck = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'actual_field_name'");
    //                 $actual_field_exists = !empty($columnsCheck);

    //                 // Add missing columns
    //                 if (!$form_id_exists || !$feed_id_exists || !$actual_field_exists) {
    //                     if (!$form_id_exists) {
    //                         $wpdb->query("ALTER TABLE $table_name ADD form_id bigint(20) NOT NULL");
    //                     }
    //                     if (!$feed_id_exists) {
    //                         $wpdb->query("ALTER TABLE $table_name ADD feed_id bigint(20) NULL");
    //                     }
    //                     if (!$actual_field_exists) {
    //                         $wpdb->query("ALTER TABLE $table_name ADD actual_field_name varchar(255) NOT NULL");
    //                     }
    //                 }
    //             } else {
    //                 // Table does not exist
    //                 // SQL query to create the table
    //                 $sql = "CREATE TABLE $table_name (
    //                 id bigint(20) NOT NULL AUTO_INCREMENT,
    //                 submission_id bigint(20) NOT NULL,
    //                 form_id bigint(20) NOT NULL,
    //                 feed_id bigint(20) NULL,
    //                 field_id varchar(255) NOT NULL,
    //                 actual_field_name varchar(255) NOT NULL,
    //                 value varchar(255) NOT NULL,
    //                 field_label varchar(255) NOT NULL,
    //                 PRIMARY KEY  (id)
    //             ) $charset_collate;";

    //                 // Include the upgrade file
    //             require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    //                 // Create the table
    //             dbDelta($sql);
    //         }

    //         if (!empty($feeds)) {
    //             foreach ($feeds as $f) {
    //                 $meta_id = $f->meta_id;
    //                 $post_id = $f->post_id;

    //                 if ($meta_id != "") {
    //                     $saved_fields = get_post_meta($meta_id, 'gscele_sheet_header');

    //                     $saved_fields = isset($saved_fields[0]) ? $saved_fields[0] : array();

    //                     $data = array();

    //                     if (is_array($saved_fields) && isset($saved_fields['Entry ID'])) {
    //                         $entryID = array('Entry ID' => $saved_fields['Entry ID']);
    //                         unset($saved_fields['Entry ID']);
    //                         $saved_fields = array_merge($entryID, $saved_fields);
    //                     }

    //                     foreach ($saved_fields as $field_id => $field_label) {


    //                         if ($field_id == 'Entry ID' && $field_label == 1) {
    //                             $data[] = $entry_id;
    //                         } elseif ($field_id == 'Post ID' && $field_label == 1) {
    //                             $data[] = isset($f->post_id) ? $f->post_id : "";
    //                         } elseif ($field_id == 'User IP' && $field_label == 1) {

    //                             $ip = '';

    //                             if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    //                                 $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
    //                             } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    //                                 $ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
    //                             } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    //                                 $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    //                             }

    //                             $data[] = $ip;
    //                         } elseif ($field_id == 'Entry Date' && $field_label == 1) {
    //                             $data[] = current_datetime()->format('Y-m-d H:i:s');
    //                                 // ate_i18n('n/j/Y g:i:s', current_time('timestamp'));

    //                         } elseif ($field_id == 'User Agent' && $field_label == 1) {
    //                             $user_agent = "";
    //                             if (isset($_SERVER['HTTP_USER_AGENT'])) {
    //                                 $user_agent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
    //                             }
    //                             $data[] = $user_agent;
    //                         } elseif ($field_id == 'User Name' && $field_label == 1) {
    //                             $current_user = wp_get_current_user();
    //                             $user_name = $current_user->display_name;

    //                             $data[] = $user_name;
    //                         } elseif ($field_id == 'Referrer' && $field_label == 1) {
    //                             $referrer = '';

    //                             if (!empty($_SERVER['HTTP_REFERER'])) {
    //                                 $referrer = esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER']));
    //                             } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    //                                 $referrer = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    //                             }

    //                             $data[] = $referrer;
    //                         } elseif ($field_id == 'User ID' && $field_label == 1) {
    //                             $user_id = '';
    //                             if (is_user_logged_in()) {
    //                                 $current_user = wp_get_current_user();
    //                                 $user_id = $current_user->ID;
    //                             }
    //                             $data[] = $user_id;
    //                         } elseif ($field_id == 'Form Name' && $field_label == 1) {
    //                             $form_name = '';

    //                             if (isset($f->post_id)) {
    //                                 $elementor_data = get_post_meta($f->post_id, '_elementor_data', true);
    //                                 $elementor_data = json_decode($elementor_data, true);

    //                                 if (method_exists($this, 'get_form_name')) {
    //                                     $form_info = $this->get_form_name($elementor_data);
    //                                     $form_name = $form_info['form_name'] ?? '';
    //                                 }
    //                             }

    //                             $data[] = $form_name;
    //                         } else {
    //                             $data[] = isset($mapped_form_data[$field_id]) ? $mapped_form_data[$field_id] : '';
    //                         }
    //                     }

    //                     if (!empty($data)) {
    //                         $saved_header_names = get_post_meta($meta_id, 'gscele_sheet_header_names');
    //                         $saved_header_names = isset($saved_header_names[0]) ? $saved_header_names[0] : array();
    //                             // Initialize field placeholders array
    //                         $field_placeholders = [];
    //                         if (!empty($form_settings['form_fields'])) {
    //                             foreach ($form_settings['form_fields'] as $field) {
    //                                 $custom_id = $field['custom_id'] ?? '';
    //                                 $placeholder = $field['placeholder'] ?? '';

    //                                 if (!empty($custom_id)) {
    //                                     $field_placeholders[$custom_id] = $placeholder;
    //                                 }
    //                             }
    //                         }

    //                             // Initialize matched fields array
    //                         $matched_fields = [];

    //                             // Loop through $saved_header_names to find matches
    //                         foreach ($saved_header_names as $saved_key => $saved_value) {
    //                             foreach ($gsele_raw_fields as $field_key => $field_data) {
    //                                     $value = $field_data['value'] ?? ''; // Raw value from form submission

    //                                     if ($field_data['type'] === 'select' && !empty($form_settings['form_fields'])) {

    //                                         // Use the field's own ID as the reference for comparison
    //                                         $current_field_id = $field_data['id'] ?? '';

    //                                         foreach ($form_settings['form_fields'] as $meta_field) {
    //                                             if (isset($meta_field['custom_id']) && $meta_field['custom_id'] === $current_field_id) {

    //                                                 // Get options (string or array)
    //                                                 $options = $meta_field['field_options'] ?? '';

    //                                                 if (is_string($options)) {
    //                                                     $options_array = array_filter(array_map('trim', explode("\n", $options)));
    //                                                 } elseif (is_array($options)) {
    //                                                     $options_array = $options;
    //                                                 } else {
    //                                                     $options_array = [];
    //                                                 }

    //                                                 $final_value = $value; // default fallback

    //                                                 // Handle both "Option A|1" and plain options
    //                                                 foreach ($options_array as $opt) {
    //                                                     $parts = array_map('trim', explode('|', $opt));
    //                                                     $label = $parts[0] ?? '';
    //                                                     $opt_value = $parts[1] ?? $label;

    //                                                     if ((string)$value === (string)$opt_value) {
    //                                                         // Only append pipe if label and value differ
    //                                                         $final_value = ($label !== $opt_value)
    //                                                         ? $label . '|' . $opt_value
    //                                                         : $label;
    //                                                         break;
    //                                                     }
    //                                                 }

    //                                                 // Update the field_data with final readable value
    //                                                 $field_data['value'] = $final_value;
    //                                                 break;
    //                                             }
    //                                         }
    //                                     }

    //                                     // Get the field title (label), placeholder, or custom_id
    //                                     $field_label = !empty($field_data['title']) ? $field_data['title'] : (!empty($field_placeholders[$field_key]) ? $field_placeholders[$field_key] : (!empty($field_data['id']) ? $field_data['id'] : 'Unnamed Field'));

    //                                     if ($saved_key == $field_label) {
    //                                         $matched_fields[$saved_value] = $field_data;
    //                                     }
    //                                 }
    //                             }

    //                             if (!empty($matched_fields)) {
    //                                 foreach ($matched_fields as $field_name => $field_value) {
    //                                     // Ensure we get a meaningful field name
    //                                     $actual_field_name = htmlspecialchars_decode(wp_strip_all_tags(
    //                                         !empty($field_value['title']) ? $field_value['title'] : (!empty($field_placeholders[$field_value['id']]) ? $field_placeholders[$field_value['id']] : (!empty($field_value['id']) ? $field_value['id'] : 'Unnamed Field'))
    //                                     ));

    //                                     // Insert the field value into the table
    //                                     $wpdb->insert($table_name, array(
    //                                         'submission_id' => $entry_id,
    //                                         'form_id' => $post_id,
    //                                         'feed_id' => $meta_id,
    //                                         'field_id' => $field_value['id'],
    //                                         'actual_field_name' => $actual_field_name,
    //                                         'value' => $field_value['value'],
    //                                         'field_label' => $field_name,
    //                                     ));
    //                                 }
    //                             }

    //                             $spreadsheetData = get_post_meta($meta_id, 'gscele_form_feeds', true);
    //                             $sort_order = get_post_meta($meta_id, 'gscele_sort_order', true) ?: 'bottom';

    //                             $spreadsheet_id = isset($spreadsheetData['sheet-id']) ? esc_attr($spreadsheetData['sheet-id']) : '';
    //                             $tab_name = isset($spreadsheetData['sheet-tab-name']) ? esc_attr($spreadsheetData['sheet-tab-name']) : '';
    //                             $tab_id = isset($spreadsheetData['tab-id']) ? esc_attr($spreadsheetData['tab-id']) : '';
    //                             if ($spreadsheet_id != "" && $tab_name != "" && $tab_id != "") {
    //                                 include_once GS_CONN_ELE_PRO_ROOT . '/lib/google-sheets.php';
    //                                 $doc = new GSC_Elementor_Free();
    //                                 $doc->auth();
    //                                 $doc->setSpreadsheetId($spreadsheet_id);
    //                                 $doc->setWorkTabId($tab_id);
    //                                 $sort_order = get_post_meta($meta_id, 'gscele_sort_order', true) ?: 'bottom';

    //                                 $doc->add_row_to_sheet($spreadsheet_id, $tab_name, $data, false, $sort_order);
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         } catch (Exeption $e) {
    //             $data['ERROR_MSG'] = $e->getMessage();
    //             $data['TRACE_STK'] = $e->getTraceAsString();
    //             GsEl_Connector_Utility::ele_gs_debug_log($data);
    //             return;
    //         }
    //     }
    // }


    /**
     * Handle saving of Elementor feed settings from POST request.
     *
     * This function validates the request using nonce and user capability checks,
     * retrieves submitted feed configuration (sheet and tab details),
     * and updates the corresponding post meta in the database.
     *
     * It ensures only authorized users can modify feed settings and safely
     * stores Google Sheet configuration data for the selected feed.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function execute_post_data_gscelementor()
    {
        try {

            if (isset($_POST['gsele-free-execute-edit-feed'])) {

                // ✅ Nonce check
                $nonce = isset($_POST['gs-ajax-nonce'])
                ? sanitize_text_field(wp_unslash($_POST['gs-ajax-nonce']))
                : '';

                if (!wp_verify_nonce($nonce, 'gs-ajax-nonce')) {
                    wp_die('Invalid nonce');
                }

                // ✅ Capability check
                if (!is_user_logged_in() || !current_user_can('edit_posts')) {
                    wp_die('You do not have permission to edit feeds.');
                }

                // ✅ Basic fields
                $feed_id = isset($_POST['feed_id'])
                ? intval(wp_unslash($_POST['feed_id']))
                : 0;

                $form_id = isset($_POST['form_id'])
                ? intval(wp_unslash($_POST['form_id']))
                : 0;

                // ✅ Nested array safely handled + sanitized
                $elementor_gs = isset($_POST['elementor-gs'])
                ? array_map('sanitize_text_field', wp_unslash((array) $_POST['elementor-gs']))
                : [];

                $sheet_name_custom = isset($elementor_gs['sheet-name-custom'])
                ? sanitize_text_field($elementor_gs['sheet-name-custom'])
                : '';

                $tab_name_custom = isset($elementor_gs['sheet-tab-name-custom'])
                ? sanitize_text_field($elementor_gs['sheet-tab-name-custom'])
                : '';

                $sheet_id_custom = isset($elementor_gs['sheet-id-custom'])
                ? sanitize_text_field($elementor_gs['sheet-id-custom'])
                : '';

                // ✅ Allow 0 value
                $tab_id_custom = isset($elementor_gs['tab-id-custom'])
                ? intval($elementor_gs['tab-id-custom'])
                : '';



                // ✅ Save data
                if (
                    $feed_id &&
                    !empty($sheet_name_custom) &&
                    !empty($sheet_id_custom) &&
                    !empty($tab_name_custom) &&
                    $tab_id_custom !== ''
                ) {

                    $meta_key = 'gscele_form_feeds';

                    $meta_value = [
                        'sheet-name'     => $sheet_name_custom,
                        'sheet-id'       => $sheet_id_custom,
                        'sheet-tab-name' => $tab_name_custom,
                        'tab-id'         => $tab_id_custom,
                    ];

                    update_post_meta($feed_id, $meta_key, $meta_value);
                }
            }
        } catch (Exception $e) {

            GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
        }
    }


    /**
     * Retrieve list of Elementor forms connected to Google Sheets.
     *
     * This function fetches Elementor form data stored in post meta
     * (`__elementor_forms_snapshot`) for pages and returns the associated
     * post IDs, titles, and metadata.
     *
     * It uses WordPress object caching to improve performance and reduce
     * repeated database queries by storing results temporarily.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array List of posts with associated Elementor form metadata.
     */
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
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $results, 'gsc_plugin', 300);
        }

        return $results;
    }

    /**
     * Retrieve list of Elementor form feeds connected to Google Sheets.
     *
     * This function fetches feed-related metadata (`gscele_form_feeds`)
     * associated with Elementor forms stored in post meta for pages.
     *
     * It returns post details along with meta information such as meta_id,
     * which is used to identify individual feed configurations.
     *
     * To improve performance, results are cached using WordPress object cache
     * to avoid repeated database queries.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array List of posts with associated feed metadata.
     */
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
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $results = $wpdb->get_results($query);
            wp_cache_set($cache_key, $results, 'gsc_plugin', 300);
        }

        return $results;
    }

    /**
     * Create Error Logs Table
     *
     * This function creates a custom database table to store
     * error logs generated by the ElementorGSC plugin. It stores
     * error codes, messages, additional details, and timestamps.
     *
     * Table Name: wp_gscelef_error_logs
     *
     * Columns:
     * - id         : Primary key (auto increment)
     * - error_id   : Unique identifier for grouping related errors
     * - code       : Error code number
     * - message    : Error message text
     * - details    : Additional detailed information about the error
     * - created_at : Date and time when the error occurred
     *
     * Uses WordPress dbDelta() to safely create or update the table structure.
     *
     * @return void
     */
    private function maybe_migrate_gselef_debug_log()
    {
        global $wpdb;

        $gselef_table = $wpdb->prefix . 'gscelef_error_logs';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $gselef_table_exists = $wpdb->get_var(
            $wpdb->prepare('SHOW TABLES LIKE %s', $gselef_table)
        );

        if ($gselef_table_exists !== $gselef_table) {
            $charset = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE {$gselef_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            error_id VARCHAR(191) NOT NULL,
            code INT NOT NULL,
            message TEXT NOT NULL,
            details LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY error_id (error_id),
            KEY code (code)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    update_option('gselef_debug_migrated_pro', 1);
}

    /**
     * Migrate existing feed data to add 'gselef_status' meta.
     *
     * This function finds all postmeta records where meta_value
     * is 'gscele_form_feeds' and updates corresponding posts
     * with a new meta key 'gselef_status' set to 1.
     *
     * After successful execution, it sets an option flag
     * to prevent running migration again.
     *
     * @since 1.0.0
     * @return void
     */
    private function maybe_migrate_gselef_feed_status()
    {
        global $wpdb;

        // Run only once
        if (get_option('gselef_feed_status_migrated_pro')) {
            return;
        }


        // Get the postmeta table name safely using WordPress prefix
        $table = $wpdb->postmeta;
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.NoCaching
        $feedList = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id
                FROM {$wpdb->postmeta}
                WHERE meta_value LIKE %s",
                '%gscele_form_feeds%'
            )
        );



        if (empty($feedList)) {
            return;
        }

        // If matching records found, update post meta
        foreach ($feedList as $value) {

            if (!empty($value->meta_id)) {
                update_post_meta($value->meta_id, 'gselef_status', 1);
            }
        }

        // Set option flag so migration runs only once
        update_option('gselef_feed_status_migrated_pro', 1);
    }
}

$gscef_elementor_integration = new GSC_Elementor_Integration();
