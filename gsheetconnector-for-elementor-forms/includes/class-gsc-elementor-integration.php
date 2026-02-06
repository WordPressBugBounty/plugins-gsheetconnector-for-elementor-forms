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
class GSC_Elementor_Integration {

    /**
     *  Set things up.
     *  @since 1.0
     */
    public function __construct() {
        add_action('wp_ajax_verify_gscelementor_integation', array($this, 'verify_gscelementor_integation'));
        add_action('wp_ajax_deactivate_gscelementor_integation', array($this, 'deactivate_gscelementor_integation'));
       
        add_action('wp_ajax_get_google_tab_list_by_sheetname', array($this, 'get_google_tab_list_by_sheetname'));

         //deactivate auth token
        add_action('wp_ajax_deactivate_auth_gscelementor', array($this, 'deactivate_auth_gscelementor'));
        
        // Add Feed
        add_action( 'wp_ajax_save_gscelementor_feed', array($this, 'save_gscelementor_feed') );
        
        add_action( 'admin_init', array($this,'execute_post_data_gscelementor'));

        // form feed submit entry in google sheet
        add_action( 'elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_feed'), 10, 2 );

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
    public function gscelementor_log_elementor_systeminfo() {
        // nonce check
        check_ajax_referer( 'gs-ajax-nonce-ele', 'security' );

        // Initialize WP_Filesystem
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        $log_file = WP_CONTENT_DIR . '/debug.log';

        // Clear the log file using WP_Filesystem
        if ( $wp_filesystem->exists( $log_file ) || $wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE ) ) {
            $wp_filesystem->put_contents( $log_file, '', FS_CHMOD_FILE );
        }

        wp_send_json_success();
    }

    public function gscelementor_clear_debug_log() {
        // nonce check
        check_ajax_referer( 'gs-ajax-nonce-ele', 'security' );

        // Initialize WP_Filesystem
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        $existDebugFile = get_option( 'ele_gs_debug_log_file' );
        $clear_file_msg = '';

        // check if debug unique log file exists
        if ( ! empty( $existDebugFile ) && $wp_filesystem->exists( $existDebugFile ) ) {
            $wp_filesystem->put_contents( $existDebugFile, '', FS_CHMOD_FILE );
            $clear_file_msg = 'Logs are cleared.';
        } else {
            $clear_file_msg = 'No log file exists to clear logs.';
        }

        wp_send_json_success( $clear_file_msg );
    }


/**
 * Function - sync with google account to fetch sheet and tab name
 * Called from multiple contexts (settings page, Elementor editor).
 * modified in version 1.2.3
 * @since 1.0
 */
public function sync_google_account_gscelementor_unified() {
    // Always check nonce
    if ( empty($_POST['security']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['security'])), 'gs-ajax-nonce-ele' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid or missing nonce.' ), 403 );
    }

    // Always check capability (Admins only by default)
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ), 403 );
    }

    // Handle init flag
    $init = isset($_POST['isinit']) ? sanitize_text_field( wp_unslash($_POST['isinit'])) : 'no';

    // Include and auth Google client
    include_once( GS_CONN_ELE_ROOT . '/lib/google-sheets.php');
    $doc = new GSC_Elementor_Free();
    $doc->auth();

    // Fetch spreadsheets
    $spreadsheetFeed = $doc->get_spreadsheets();
    $sheetId_array   = ! empty($spreadsheetFeed) ? array_column($spreadsheetFeed, 'title', 'id') : array();

    update_option('elefgs_sheetId', $sheetId_array);

    // Response
    if ( $init === 'yes' ) {
        wp_send_json_success( array( "success" => 'yes' ) );
    } else {
        wp_send_json_success( array( "success" => 'no' ) );
    }
}

    /**
     * AJAX function - deactivate activation - Manual
     * @since 1.2
    */
    public function deactivate_auth_gscelementor() {
        // nonce check
        check_ajax_referer( 'gs-ajax-nonce-ele', 'security' );

        if ( get_option( 'elefgs_token' ) !== '' ) {
            delete_option( 'elefgs_feeds' );
            delete_option( 'elefgs_sheetId' );
            delete_option( 'elefgs_token' );
            delete_option( 'elefgs_access_manual_code' );
            delete_option( 'elefgs_verify' );

            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public function send_metform_submission_to_google_sheets_feed($form_id, $form_data) {
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
    public function send_form_submission_to_google_sheets_feed($record, $handler) {
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

    if ( is_array( $field_data ) ) {
        // File upload or multiple values
        $data[ $field_label ] = implode( ',', array_map( 'esc_url_raw', $field_data ) );
    } else {
        // IMPORTANT: remove Elementor/WP slashes and sanitize
        $field_data = wp_unslash( $field_data );
        $data[ $field_label ] = sanitize_text_field( $field_data );
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
                    $doc->add_row_feed($spreadsheet_id, $tab_name, $data, false);
                    
                } catch (Exception $e) {
                    GsEl_Connector_Utility::ele_gs_debug_log("Error sending data to Google Sheets for feed ID: $feed_id. " . $e->getMessage());
                }
            } else {
                 GsEl_Connector_Utility::ele_gs_debug_log("Missing spreadsheet configuration for feed ID: $feed_id");
               }
        }
    }

    /**
     * Save feed settings in the database.
     *
     * @since 1.0.0
     */
    public function execute_post_data_gscelementor() {
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
    public function save_gscelementor_feed(){
        
        // nonce checksave_avadaforms_gs_settings
        check_ajax_referer( 'elementorform-ajax-nonce', 'security' );

        /* sanitize incoming data */
        $feedName = sanitize_text_field( $_POST['feed_name'] );
        $elementorForms = sanitize_text_field( $_POST['elementorForms'] );

        $message ='';
        if(isset($feedName) && isset($elementorForms) && !empty($feedName) && !empty($elementorForms)){
            /*check same name feed exist or not */
            $feed_check = get_post_meta($_POST['elementorForms'], $_POST['feed_name'], 'gscele_form_feeds');
                 
            if(empty($feed_check)){
                update_post_meta($_POST['elementorForms'], $_POST['feed_name'], 'gscele_form_feeds');
                $message .='Feed has been successfully created.';                      
            }else{
                $message .='Feed name already exists in the list, Please enter unique name of feed.';
            }
            wp_send_json_success($message);
                  
        }
    }


    /**
     * Deleting Feed.
     *
     * @since 1.0.0
     */
    public function delete_feed() {
        try {
            // Nonce verification
            check_ajax_referer( 'elementorform-ajax-nonce', 'security' );

            // Validate and sanitize input
            $feed_id = isset( $_POST['feed_id'] ) ? intval( wp_unslash( $_POST['feed_id'] ) ) : 0;

            if ( $feed_id ) {
                $deleted  = delete_metadata( 'post', $feed_id, 'gscele_form_feeds' );
                $deleted1 = delete_metadata_by_mid( 'post', $feed_id );

                if ( $deleted1 ) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            } else {
                echo 'invalid';
            }

            wp_die();

        } catch ( Exception $e ) {
            // Logging the exception properly
            if ( class_exists( 'GsEl_Connector_Utility' ) ) {
                GsEl_Connector_Utility::ele_gs_debug_log( $e->getMessage() );
            }
            wp_die( 'error' );
        }
    }

    /**
     * AJAX function - deactivate activation
     * @since 1.4
     */
    public function deactivate_gscelementor_integation() {
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
    public function verify_gscelementor_integation() {
        // nonce check
        check_ajax_referer( 'gs-ajax-nonce-ele', 'security' );

        /* validate and sanitize incoming data */
        if ( isset( $_POST['code'] ) ) {
            $Code = sanitize_text_field( wp_unslash( $_POST['code'] ) );
        } else {
            wp_send_json_error( 'Missing code.' );
            return;
        }

        if ( ! empty( $Code ) ) {
            update_option( 'elefgs_access_code', $Code );
        } else {
            wp_send_json_error( 'Empty code.' );
            return;
        }

        if ( get_option( 'elefgs_access_code' ) !== '' ) {
            include_once( GS_CONN_ELE_ROOT . '/lib/google-sheets.php' );
            GSC_Elementor_Free::preauth( get_option( 'elefgs_access_code' ) );
            wp_send_json_success();
        } else {
            update_option( 'elefgs_verify', 'invalid' );
            wp_send_json_error();
        }
    }
    
    

    // old settings get forms list
    // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
    public function get_forms_connected_to_sheet() {
        global $wpdb;

        $cache_key = 'gsc_ele_connected_forms';
        $results = wp_cache_get( $cache_key, 'gsc_plugin' );

        if ( false === $results ) {
            $table_posts    = $wpdb->prefix . 'posts';
            $table_postmeta = $wpdb->prefix . 'postmeta';

            $query = "
                SELECT p.ID, p.post_title, pm.meta_value, pm.meta_key
                FROM {$table_posts} AS p
                JOIN {$table_postmeta} AS pm ON p.ID = pm.post_id
                WHERE pm.meta_key = '__elementor_forms_snapshot'
                AND p.post_type = 'page'
            ";

            $results = $wpdb->get_results( $query );
            wp_cache_set( $cache_key, $results, 'gsc_plugin', 300 );
        }

        return $results;
    }
    // phpcs:enable

    // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
    public function get_forms_feeds_connected_to_sheet() {
        global $wpdb;

        $cache_key = 'gsc_ele_feed_connected_forms';
        $results = wp_cache_get( $cache_key, 'gsc_plugin' );

        if ( false === $results ) {
            $table_posts    = $wpdb->prefix . 'posts';
            $table_postmeta = $wpdb->prefix . 'postmeta';

            $query = "
                SELECT p.ID, p.post_title, pm.meta_value, pm.meta_key, pm.meta_id
                FROM {$table_posts} AS p
                JOIN {$table_postmeta} AS pm ON p.ID = pm.post_id
                WHERE pm.meta_value = 'gscele_form_feeds'
                AND p.post_type = 'page'
            ";

            $results = $wpdb->get_results( $query );
            wp_cache_set( $cache_key, $results, 'gsc_plugin', 300 );
        }

        return $results;
    }
    // phpcs:enable


    /**
     * AJAX function - Fetch tab list by sheet name
     * @since 1.0
     */
    public function get_google_tab_list_by_sheetname() {

        // Nonce check
        check_ajax_referer( 'gs-ajax-nonce-ele', 'security' );

        // Validate and sanitize POST data
        $spreadsheet_id = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
        $refresh        = isset( $_POST['refresh'] ) ? sanitize_text_field( wp_unslash( $_POST['refresh'] ) ) : '';

        $temp1           = array();
        $TabsId_array1   = array();
        $TabsId_array    = array();
        $divifgs_sheetTabs = get_option( 'elefgs_tabsId' );

        include_once( GS_CONN_ELE_ROOT . "/lib/google-sheets.php" );
        $doc = new GSC_Elementor_Free();
        $doc->auth();

        $TabsId_array = $doc->get_worktabs( $spreadsheet_id );

        // Refresh logic
        if ( $refresh == '1' ) {
            $temp1[ $spreadsheet_id ] = $divifgs_sheetTabs;
            update_option( 'elefgs_tabsId', $temp1 );
        } else {
            if ( empty( $divifgs_sheetTabs ) ) {
                $temp1[ $spreadsheet_id ] = $TabsId_array;
                update_option( 'elefgs_tabsId', $temp1 );
            } else {
                $TabsId_array1[ $spreadsheet_id ] = $TabsId_array;
                $temp = array_merge( $divifgs_sheetTabs, $TabsId_array1 );
                update_option( 'elefgs_tabsId', $temp );
            }
        }

        // Return the final updated data
        $divifgs_sheetTabs = get_option( 'elefgs_tabsId' );
        wp_send_json_success( $divifgs_sheetTabs );
    }
}

$gsc_elementor_integration = new GSC_Elementor_Integration();
