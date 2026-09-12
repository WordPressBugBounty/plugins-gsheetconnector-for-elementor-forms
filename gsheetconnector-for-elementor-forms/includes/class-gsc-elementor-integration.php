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

        //dashboard pagination
        add_action('wp_ajax_elefgs_free_paginate_feed_list', array($this, 'elefgs_free_paginate_feed_list'));

        add_action('admin_init', array($this, 'execute_post_data_gscelementor'));

        // form feed submit entry in google sheet
        add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_feed'), 10, 2);

        // add_action('elementor_pro/forms/new_record', array($this, 'send_form_submission_to_google_sheets_free'), 10, 2);

        /*
         * Elementor 4 "atomic" form ( <form data-element_type="e-form"> ) support.
         *
         * Atomic forms do NOT fire `elementor_pro/forms/new_record`; they submit
         * to their own AJAX action (`elementor_pro_atomic_forms_send_form`).
         *
         * We sync on `elementor_pro/atomic_forms/spam_check` — the only point
         * Elementor Pro exposes AFTER it has validated the nonce, resolved the
         * form element and run its File_Upload_Handler (so the final uploaded
         * file URLs are already available). Priority 20 is after Elementor's own
         * Akismet check (priority 10): a submission already flagged as spam is
         * skipped. The filter value is ALWAYS returned unchanged, so Elementor's
         * own spam decision, JSON response and actions are never affected, and
         * classic Form widgets are untouched.
         */
        add_filter('elementor_pro/atomic_forms/spam_check', array($this, 'gscelef_atomic_form_capture'), 20, 4);

        /*
         * Record the final URL of every file Elementor uploads during an atomic
         * submission, keyed by the PHP tmp path (unique per file), so it can be
         * mapped back to its form field WITHOUT re-uploading or re-validating.
         * File_Storage::move() runs wp_handle_upload() with the atomic action, so
         * the prefilter name is atomic-specific and `wp_handle_upload` only fires
         * on a successful move with the final URL.
         */
        add_filter('elementor_pro_atomic_forms_send_form_prefilter', array($this, 'gscelef_atomic_capture_upload_src'));
        add_filter('wp_handle_upload', array($this, 'gscelef_atomic_capture_upload_url'), 10, 2);
    }

    /**
     * @var string PHP tmp path of the atomic-form file currently inside wp_handle_upload().
     */
    private $gscelef_atomic_upload_src = '';

    /**
     * @var array<string,string> [ php_tmp_path => final_uploaded_url ] for the current request.
     */
    private $gscelef_atomic_uploaded = array();


/**
* Handles the AJAX request for paginating the dashboard feed list.
*
* Verifies security nonces, processes current page parameters, fetches feed entries 
* from the database, and returns the rendered HTML payload.
*
* @since 1.3.3
*
* @return void Sends a JSON response using wp_send_json_success().
*/
public function elefgs_free_paginate_feed_list()
{

// Nonce check.
    check_ajax_referer( 'gselef-pagination-nonce', 'security' );

    $paged            = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
    $first_page_count = 3;
    $per_page         = 4;

    global $wpdb;

    $query = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT
            pm.meta_id,
            pm.post_id,
            pm.meta_key,
            pm.meta_value,
            p.post_title,
            p.post_type,
            p.post_status
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p
            ON p.ID = pm.post_id
            WHERE pm.meta_value LIKE %s OR pm.meta_key = %s
            ORDER BY pm.meta_id DESC
            ",
            '%gscele_form_feeds%',
            'metform_gs_settings'
        )
    );
    
    foreach ( $query as &$row ) {

        $feed = maybe_unserialize( $row->meta_value );

        if ( ! is_array( $feed ) ) {
            continue;
        }

        $row->feed_name = ! empty( $feed['feed-name'] )
        ? $feed['feed-name']
        : $row->meta_key;

    /*
     * Use the same gscele_form_feeds data source used by edit-sheet.php.
     */
    $feed_data = get_post_meta( $row->meta_id, 'gscele_form_feeds', true );

    if ( ! is_array( $feed_data ) ) {
        $feed_data = array();
    }

    if ( $row->post_type === 'metform-form' ) {

        // Display MetForm feeds with the "MetForm :" prefix.
        $row->form_name = 'MetForm : ' . $row->post_title;

    } else {

        $resolved = $this->elefgs_free_resolve_elementor_form_name(
            $row->post_id,
            $feed['element_id'] ?? ''
        );

        // Display-only: tag Elementor 4 atomic forms so they read the same
        // way they already do on the Form Feeds list and Edit Feed page
        // ("Atomic Form : {name}"). Classic Form widgets are unchanged.
        $row->form_name = ! empty( $resolved['is_atomic'] )
        ? 'Atomic Form : ' . $resolved['form_name']
        : $resolved['form_name'];
    }

    $row->sheet_name = $feed_data['sheet-name'] ?? '';
    $row->sheet_id   = $feed_data['sheet-id'] ?? '';
    $row->tab_id     = ! empty( $feed_data['tab-id'] )
    ? $feed_data['tab-id']
    : '0';
}

unset( $row );

$result = $this->elefgs_free_render_feed_page(
    $query,
    $paged,
    $first_page_count,
    $per_page
);

wp_send_json_success($result);
}

/**
* Resolves the correct Elementor form widget name for a feed.
*
* A single page/post can contain multiple Form widgets, so the page title is
* never used here. Instead, the feed's saved element_id is matched against
* every form widget found on the page. When element_id is unavailable
* (older feeds) or no match is found, the first form widget on the page is
* used instead.
*
* Deliberately self-contained: this does not call the global get_form_name()
* function, since two conflicting global functions with that name already
* exist in other page templates (gsc-feed-google-sheet.php and edit-sheet.php).
*
* Recognises both the classic Elementor Pro Form widget (`widgetType = form`)
* and the Elementor 4 atomic form element (`elType = e-form`) - the same two
* shapes gscelef_form_node_info() / gsc_find_form_name_by_element_id() already
* handle in the other two feed-listing templates.
*
* @since 1.3.3
*
* @param int    $post_id    The post/page ID the feed is attached to.
* @param string $element_id Optional. The saved Elementor widget element_id.
* @return array{form_name:string,is_atomic:bool} Falls back to the post title
*              (is_atomic false) only when the page has no _elementor_data or
*              no form widgets at all.
*/
private function elefgs_free_resolve_elementor_form_name($post_id, $element_id = '')
{
    $elementor_data = get_post_meta($post_id, '_elementor_data', true);
    $data = is_array($elementor_data) ? $elementor_data : json_decode($elementor_data, true);

    if (!is_array($data)) {
        return array('form_name' => get_the_title($post_id), 'is_atomic' => false);
    }

    $forms = $this->elefgs_free_collect_elementor_forms($data);

    if (!empty($element_id)) {
        foreach ($forms as $form) {
            if (($form['element_id'] ?? '') === $element_id) {
                return array(
                    'form_name' => $form['form_name'] ?? '',
                    'is_atomic' => !empty($form['is_atomic']),
                );
            }
        }
    }

    if (!empty($forms)) {
        return array(
            'form_name' => $forms[0]['form_name'] ?? '',
            'is_atomic' => !empty($forms[0]['is_atomic']),
        );
    }

    return array('form_name' => get_the_title($post_id), 'is_atomic' => false);
}

/**
* Recursively collects every Elementor form on a page - both the classic
* Form widget and the Elementor 4 atomic form element.
*
* @since 1.3.3
*
* @param array $elements Elementor element tree (from _elementor_data).
* @param array $forms    Accumulator, passed by reference.
* @return array List of ['form_name' => ..., 'element_id' => ..., 'is_atomic' => bool].
*/
private function elefgs_free_collect_elementor_forms($elements, &$forms = array())
{
    foreach ($elements as $widget) {
        if (is_array($widget)) {

            $widget_type = isset($widget['widgetType']) ? $widget['widgetType'] : '';
            $el_type     = isset($widget['elType']) ? $widget['elType'] : '';

            $is_classic_form = ('form' === $widget_type);
            $is_atomic_form  = ('e-form' === $el_type || 'e-form' === $widget_type);

            if ($is_classic_form || $is_atomic_form) {

                if ($is_atomic_form) {
                    // Atomic prop: settings['form-name'] = ['$$type'=>'string','value'=>'...'].
                    $raw_name = isset($widget['settings']['form-name']) ? $widget['settings']['form-name'] : '';
                    if (is_array($raw_name)) {
                        $raw_name = isset($raw_name['value']) ? $raw_name['value'] : '';
                    }
                    $form_name = is_string($raw_name) ? trim($raw_name) : '';
                } else {
                    $form_name = isset($widget['settings']['form_name']) ? $widget['settings']['form_name'] : '';
                }

                $forms[] = array(
                    'form_name'  => $form_name,
                    'element_id' => $widget['id'] ?? '',
                    'is_atomic'  => $is_atomic_form,
                );
            }

            if (isset($widget['elements']) && is_array($widget['elements'])) {
                $this->elefgs_free_collect_elementor_forms($widget['elements'], $forms);
            }
        }
    }

    return $forms;
}

/**
* Renders the HTML output for the feed table rows and pagination controls.
*
* Offsets results dynamically to accommodate different item counts between page 1 
* and subsequent pages.
*
* @since 1.3.3
*
* @return array {
*     Structured HTML data and state status.
*
*     @type string $rows_html       Rendered HTML for the <tr> table rows.
*     @type string $pagination_html Rendered HTML for pagination buttons.
*     @type bool   $has_feeds       Whether any database rows exist.
* }
*/
public function elefgs_free_render_feed_page($rows, $paged, $first_page_count = 3, $per_page = 4)
{

    $total_rows = count($rows);

            // Calculate total pages with standard + custom first page count
    if ($total_rows <= $first_page_count) {
        $total_pages = 1;
    } else {
        $remaining_rows = $total_rows - $first_page_count;
        $total_pages    = 1 + (int) ceil($remaining_rows / $per_page);
    }

    $paged = max(1, min($paged, $total_pages));

            // Compute dynamic offset based on current page
    if ($paged === 1) {
        $offset       = 0;
        $slice_length = $first_page_count;
    } else {
        $offset       = $first_page_count + (($paged - 2) * $per_page);
        $slice_length = $per_page;
    }

    $paged_rows = array_slice($rows, $offset, $slice_length);

            // 1. Render Table Rows HTML
    ob_start();

    if (empty($paged_rows)) {
        ?>
        <tr>
            <td colspan="3" class="gselef-feed-empty-cell">
                <div class="gselef-feed-empty text-center">
                    <div class="heading">
                        <?php esc_html_e('No Form Feeds Created Yet', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>
                    <p>
                        <?php esc_html_e(
                            'Connect your form to Google Sheets to automatically sync submissions in real time. Create a feed to start sending data to your spreadsheet.',
                            'gsheetconnector-for-elementor-forms'
                        ); ?>
                    </p>
                </div>
            </td>
        </tr>
        <?php
    } else {
        foreach ($paged_rows as $row) {
            ?>
            <tr>
                <td>
                 <a href="<?php echo esc_url(
                    admin_url(
                        'admin.php?page=gsheetconnector-elementor-config'
                        . '&tab=form_feed_settings'
                        . '&form_id=' . absint( $row->post_id )
                        . '&feed_id=' . absint( $row->meta_id )
                    )
                    ); ?>">
                    <?php echo esc_html( $row->form_name ); ?>
                </a>
            </td>
            <td>
                <?php echo esc_html($row->feed_name); ?>
            </td>
            <td>
                <?php if (! empty($row->sheet_id)) : ?>
                 <a target="_blank"
                 href="<?php echo esc_url(
                    'https://docs.google.com/spreadsheets/d/' .
                    rawurlencode( $row->sheet_id ) .
                    '/edit#gid=' .
                    rawurlencode( $row->tab_id )
                    ); ?>">
                    <?php echo esc_html( $row->sheet_name ); ?>
                </a>
            <?php else : ?>
                <span class="gselef-not-connected"><?php echo esc_html__('Not connected', 'gsheetconnector-for-elementor-forms'); ?></span>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}
}

$rows_html = ob_get_clean();

// 2. Render Pagination HTML
ob_start();

if ($total_pages > 1) {
    for ($i = 1; $i <= $total_pages; $i++) {
        ?>
        <a href="javascript:void(0)"
        class="gselef-page-link <?php echo $i == $paged ? 'active' : ''; ?>"
        data-page="<?php echo esc_attr($i); ?>">
        <?php echo esc_html($i); ?>
    </a>
    <?php
}
}

$pagination_html = ob_get_clean();

return array(
    'rows_html'       => $rows_html,
    'pagination_html' => $pagination_html,
    'has_feeds'       => ! empty($rows),
);
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

        /*
         * Refresh the stored worksheet/tab names for spreadsheets we already
         * know about. The "Fetch Sheets" button previously only refreshed the
         * spreadsheet titles, so a renamed tab kept showing its old name in the
         * "Sheet Tab Name" dropdown (which is rebuilt from `elefgs_tabsId` on
         * page reload). We re-query Google for the current tab titles here so the
         * dropdown reflects renamed tabs after the user clicks "Click here".
         */
        $stored_tabs = get_option('elefgs_tabsId');

        if (is_array($stored_tabs) && ! empty($stored_tabs)) {

            foreach (array_keys($stored_tabs) as $stored_spreadsheet_id) {

            // Only refresh spreadsheets that still exist in the connected account.
                if (! isset($sheetId_array[$stored_spreadsheet_id])) {
                    continue;
                }

                $fresh_tabs = $doc->get_worktabs($stored_spreadsheet_id);

            // Never overwrite good data with an empty result (e.g. API error).
                if (is_array($fresh_tabs) && ! empty($fresh_tabs)) {
                    $stored_tabs[$stored_spreadsheet_id] = $fresh_tabs;
                }
            }

            update_option('elefgs_tabsId', $stored_tabs);
        }

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
        // Force-refresh: store the freshly fetched tab list for this spreadsheet,
        // merging with any previously stored data for other spreadsheets.
        $existing_tabs = is_array($divifgs_sheetTabs) ? $divifgs_sheetTabs : array();
        $existing_tabs[$spreadsheet_id] = $TabsId_array;
        update_option('elefgs_tabsId', $existing_tabs);
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
        update_option('elefgs_manual_setting', '0');
        wp_send_json_success();
    } else {
     wp_send_json_error();
 }
}

/**
 * Retrieve Elementor/MetForm feed configurations for a specific form.
 *
 * This method fetches feed records associated with the given form ID.
 * It supports both legacy and current feed structures:
 *
 * - Legacy feeds store 'gscele_form_feeds' directly in meta_value.
 * - Current feeds store 'gscele_form_feeds' inside a serialized array.
 *
 * To reduce repeated database queries, feed data is cached using
 * WordPress Object Cache. If cached data is available, it is returned
 * directly without querying the database.
 *
 * @since 1.0.0
 *
 * @param int $form_id The Elementor or MetForm post ID.
 *
 * @return array Array of feed objects containing meta_id and meta_value.
 */
private function get_form_feeds( $form_id ) {

    global $wpdb;

    $cache_key = 'elementorgsc_form_feeds_' . absint( $form_id );

    $feeds = wp_cache_get( $cache_key );

    if ( false === $feeds ) {

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query result is cached using wp_cache().
        $feeds = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, meta_value
                FROM {$wpdb->postmeta}
                WHERE post_id = %d
                AND meta_value LIKE %s",
                $form_id,
                '%gscele_form_feeds%'
            )
        );

        wp_cache_set(
            $cache_key,
            $feeds,
            '',
            HOUR_IN_SECONDS
        );
    }

    return $feeds;
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
    $feeds = $this->get_form_feeds( $form_id );


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

                     // Pass the date and time to the data array using the headers
                $data['Entry Date'] = $local_date;
                $data['Date'] = $local_date;
                $data['Submission Date'] = $local_date;

                $data['date'] = $local_date;
                $data['time'] = $local_time;

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


    // Get current page/post ID
    $form_id = isset($gs_ele_settings['form_post_id'])
    ? absint($gs_ele_settings['form_post_id'])
    : 0;

    $feeds = $this->get_form_feeds( $form_id );
    // No feeds found
    if (empty($feeds)) {
        return;
    }
    $gsele_raw_fields = $record->get('fields');
     // Get current submitted Elementor form element ID
    $current_element_id = isset($gs_ele_settings['id'])
    ? sanitize_text_field($gs_ele_settings['id'])
    : '';

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

        $gselef_update_status = get_post_meta($feed_id, 'gselef_status', true);

        // Only process enabled feeds
        if (intval($gselef_update_status) !== 1) {
            continue;
        }

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
        // Send data to Google Sheets (shared with the atomic-form handler)
        // -----------------------------------------------------------------
        $this->gscelef_dispatch_feed_row($feed_id, $data);
    }
}

/**
 * Push one mapped submission row into the Google Sheet configured for a feed.
 *
 * This is the single place the plugin talks to Google Sheets for Elementor
 * form feeds. It is shared by:
 *   - send_form_submission_to_google_sheets_feed()  (classic Form widget)
 *   - gscelef_atomic_form_capture()                 (Elementor 4 atomic e-form)
 *
 * @since 1.3.5
 *
 * @param int   $feed_id Feed postmeta meta_id (row that stores 'gscele_form_feeds').
 * @param array $data    Associative array: Google Sheet header label => submitted value.
 * @return void
 */
private function gscelef_dispatch_feed_row($feed_id, array $data)
{
    // Fetch feed configuration data.
    $spreadsheetData = maybe_unserialize(get_post_meta($feed_id, 'gscele_form_feeds', true));

    $spreadsheet_id = is_array($spreadsheetData) ? esc_attr($spreadsheetData['sheet-id'] ?? '') : '';
    $tab_name       = is_array($spreadsheetData) ? esc_attr($spreadsheetData['sheet-tab-name'] ?? '') : '';
    $tab_id         = is_array($spreadsheetData) ? esc_attr($spreadsheetData['tab-id'] ?? '') : '';

    if ($spreadsheet_id === '' || $tab_name === '' || $tab_id === '') {
        GsEl_Connector_Utility::ele_gs_debug_log(
            'Missing spreadsheet configuration for feed ID: ' . $feed_id
        );
        return;
    }

    // Entry ID (best effort — mirrors the classic handler).
    $latest_id = wp_cache_get('gsc_latest_elementor_id');
    if (false === $latest_id) {
        global $wpdb;
        $suppress = $wpdb->suppress_errors(true); // e_submissions may not exist (e.g. Elementor Pro forms unused)
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result    = $wpdb->get_results("SELECT MAX(id) as latest_id FROM {$wpdb->prefix}e_submissions");
        $wpdb->suppress_errors($suppress);
        $latest_id = isset($result[0]->latest_id) ? $result[0]->latest_id : '';
        wp_cache_set('gsc_latest_elementor_id', $latest_id, '', 300); // Cache for 5 minutes
    }

    try {

        include_once GS_CONN_ELE_ROOT . '/lib/google-sheets.php';

        $doc = new GSC_Elementor_Free();

        $doc->auth();

        $doc->setSpreadsheetId($spreadsheet_id);

        $doc->setWorkTabId($tab_id);

        // Local date/time.
        $local_date = date_i18n(get_option('date_format'));
        $local_time = date_i18n(get_option('time_format'));

        // Default date/time headers (added, never overwriting a real field).
        $data['Entry ID']        = $latest_id;
        $data['date']            = $local_date;
        $data['Entry Date']      = $local_date;
        $data['Submission Date'] = $local_date;
        $data['Date']            = $local_date;
        $data['time']            = $local_time;

        // Insert row.
        $doc->add_row_feed($spreadsheet_id, $tab_name, $data, false);

    } catch (Exception $e) {

        GsEl_Connector_Utility::ele_gs_debug_log(
            'Error sending data to Google Sheets for feed ID: ' . $feed_id . '. ' . $e->getMessage()
        );
    }
}

/**
 * Record the tmp path of the atomic-form file currently entering wp_handle_upload().
 *
 * Hooked to `elementor_pro_atomic_forms_send_form_prefilter` — WordPress builds
 * that filter name from the `action` Elementor passes to wp_handle_upload(), so
 * it fires ONLY for atomic-form uploads. Paired with gscelef_atomic_capture_upload_url()
 * (WP calls the two sequentially, once per file).
 *
 * @since 1.3.5
 *
 * @param array $file A single $_FILES-style element (name/type/tmp_name/error/size).
 * @return array $file, unchanged.
 */
public function gscelef_atomic_capture_upload_src($file)
{
    $this->gscelef_atomic_upload_src = (is_array($file) && isset($file['tmp_name']))
    ? (string) $file['tmp_name']
    : '';

    return $file;
}

/**
 * Record the FINAL uploaded URL for the atomic-form file just moved by WordPress.
 *
 * Hooked to the core `wp_handle_upload` filter, which fires only on a successful
 * move. We only act when gscelef_atomic_capture_upload_src() just set the tmp
 * path, so media-library / other-plugin uploads are ignored.
 *
 * @since 1.3.5
 *
 * @param array  $upload  { file, url, type } for the moved file.
 * @param string $context 'upload' or 'sideload'.
 * @return array $upload, unchanged.
 */
public function gscelef_atomic_capture_upload_url($upload, $context = '')
{
    if (
        '' !== $this->gscelef_atomic_upload_src
        && is_array($upload)
        && empty($upload['error'])
        && ! empty($upload['url'])
    ) {
        $this->gscelef_atomic_uploaded[$this->gscelef_atomic_upload_src] = (string) $upload['url'];
    }

    $this->gscelef_atomic_upload_src = '';

    return $upload;
}

/**
 * Map the URLs captured via wp_handle_upload() back to their atomic form fields.
 *
 * `$_FILES['form_fields'][<key>][<field_index>]['value'][<file_index>]` mirrors
 * `$_POST['form_fields'][<field_index>]` (Elementor's own File_Upload_Handler
 * uses the same index -> element-id mapping). The tmp path is the join key.
 *
 * @since 1.3.5
 *
 * @param array $raw_fields Unslashed $_POST['form_fields'].
 * @return array<string,string[]> [ element_id => [ url, ... ] ]
 */
private function gscelef_atomic_collect_file_urls(array $raw_fields)
{
    $out = array();

    if (empty($this->gscelef_atomic_uploaded)) {
        return $out;
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- structural read; tmp paths are compared to our own captured map, never trusted or output.
    $raw = isset($_FILES['form_fields']) && is_array($_FILES['form_fields']) ? $_FILES['form_fields'] : array();
    if (empty($raw['tmp_name']) || ! is_array($raw['tmp_name'])) {
        return $out;
    }

    foreach ($raw_fields as $index => $field) {

        if (! is_array($field)) {
            continue;
        }

        $element_id = isset($field['id']) ? sanitize_text_field($field['id']) : '';
        if ('' === $element_id) {
            continue;
        }

        $branch = isset($raw['tmp_name'][$index]['value']) ? $raw['tmp_name'][$index]['value'] : null;
        if (! is_array($branch)) {
            continue;
        }

        $urls = array();
        foreach ($branch as $tmp) {
            $tmp = is_string($tmp) ? $tmp : '';
            if ('' !== $tmp && isset($this->gscelef_atomic_uploaded[$tmp])) {
                $urls[] = $this->gscelef_atomic_uploaded[$tmp];
            }
        }

        if (! empty($urls)) {
            $out[$element_id] = $urls;
        }
    }

    return $out;
}

/**
 * Capture an Elementor 4 "atomic" form submission and sync it to Google Sheets.
 *
 * Hooked to the `elementor_pro/atomic_forms/spam_check` FILTER (priority 20).
 * That filter is the only point Elementor Pro exposes AFTER it has validated the
 * nonce, resolved the form element and run File_Upload_Handler (so uploaded file
 * URLs already exist, captured via wp_handle_upload). This method reads the
 * filter value, does its sync, and ALWAYS returns the value unchanged — it never
 * echoes, wp_die()s or alters Elementor's spam decision / response / actions.
 *
 * Request shape (Elementor core `atomic-widgets-form-handler.js`, verified):
 *   $_POST['post_id']     = owning document id
 *   $_POST['form_id']     = the e-form element id
 *   $_POST['form_fields'] = [ ['id','type','label','name','options','value'], ... ]
 *   $_FILES['form_fields']= file-upload fields (value key absent in $_POST)
 *
 * @since 1.3.5
 *
 * @param bool  $spam            Incoming spam-check result (returned unchanged).
 * @param array $form_fields     Elementor's sanitised form_fields (submission signal).
 * @param array $widget_settings Resolved form widget settings (unused).
 * @param int   $post_id         Owning document id.
 * @return bool The unchanged $spam value.
 */
public function gscelef_atomic_form_capture($spam = false, $form_fields = array(), $widget_settings = array(), $post_id = 0)
{
    static $processed = array();

    // Another spam check already rejected this submission — do not sync.
    if ($spam) {
        return $spam;
    }

    try {

        // Defensive re-check of the atomic-form nonce (Elementor validates it too).
        $nonce = isset($_POST['_nonce']) ? sanitize_text_field(wp_unslash($_POST['_nonce'])) : '';
        if (! wp_verify_nonce($nonce, 'elementor_pro_atomic_forms_send_form')) {
            return $spam;
        }

        $post_id = $post_id ? absint($post_id)
        : (isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0);
        $form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per-field in gscelef_atomic_fields_to_data().
        $raw_fields = isset($_POST['form_fields']) ? wp_unslash($_POST['form_fields']) : array();

        if (! $post_id || '' === $form_id || ! is_array($raw_fields) || empty($raw_fields)) {
            return $spam;
        }

        // Process a given submission only once per request.
        $dedupe_key = $post_id . '|' . $form_id;
        if (isset($processed[$dedupe_key])) {
            return $spam;
        }
        $processed[$dedupe_key] = true;

        // Same feed lookup used by the classic handler.
        $feeds = $this->get_form_feeds($post_id);
        if (empty($feeds)) {
            return $spam;
        }

        // Grouped checkbox / radio fields do not carry their connected Label in
        // the payload (Elementor's JS sends the group name instead) - resolve it
        // from the saved form so a sheet header built from the visible label matches.
        $group_labels = $this->gscelef_atomic_group_labels($post_id, $form_id);

        // File-upload fields: value is not in $_POST. Elementor has already
        // validated + uploaded the file(s); use the URL(s) it produced.
        $file_urls = $this->gscelef_atomic_collect_file_urls($raw_fields);

        // Build "sheet header => value", mirroring the classic mapping.
        $data = $this->gscelef_atomic_fields_to_data($raw_fields, $group_labels, $file_urls);
        if (empty($data)) {
            return $spam;
        }

        foreach ($feeds as $feed) {

            $feed_meta = maybe_unserialize($feed->meta_value);

            // Atomic forms are always saved as the new element_id feed format.
            $feed_element_id = (is_array($feed_meta) && ! empty($feed_meta['element_id']))
            ? $feed_meta['element_id']
            : '';

            if ('' === $feed_element_id || $feed_element_id !== $form_id) {
                continue;
            }

            if (intval(get_post_meta($feed->meta_id, 'gselef_status', true)) !== 1) {
                continue;
            }

            $this->gscelef_dispatch_feed_row($feed->meta_id, $data);
        }

    } catch (Exception $e) {

        if (class_exists('GsEl_Connector_Utility')) {
            GsEl_Connector_Utility::ele_gs_debug_log('Atomic form Google Sheets sync error: ' . $e->getMessage());
        }
    } catch (\Throwable $t) {

        if (class_exists('GsEl_Connector_Utility')) {
            GsEl_Connector_Utility::ele_gs_debug_log('Atomic form Google Sheets sync fatal: ' . $t->getMessage());
        }
    }

    // Never alter Elementor's spam decision / response / actions.
    return $spam;
}

/**
 * Convert an atomic form's `form_fields` payload into the "header => value" map
 * used for Google Sheet column matching.
 *
 * RECOMMENDED CONVENTION: create the Google Sheet header from the field LABEL
 * (same as the classic Elementor Form widget and Contact Form 7).
 *
 * The catch with Elementor 4 atomic fields: a field only reports a usable
 * `label` in the submission payload when a Label widget is connected to it
 * (field setting "Connected to input ID"), an aria-label is set, or - for text
 * inputs only - a placeholder exists. A <select> (and checkbox / radio) has no
 * placeholder, so a form whose Select has no connected Label sends the field's
 * auto-generated CSS id as the "label", and the selected value never lands in
 * the "Interest"-style column the user created.
 *
 * To make every field type sync without forcing the user to rebuild the form,
 * the value is written under each distinct readable identifier the field
 * exposes - its label, its `name`, and its CSS id - so the column matches
 * whichever the user typed as the header. The primary key (label, or the
 * field name / id when Elementor could not resolve a label) is written first
 * and unconditionally, so sheets that already match on the label are unchanged;
 * the extra aliases only ever fill a header no other field has claimed.
 *
 * The selected value of a <select> is the chosen option's `value` attribute
 * (not its visible text); this is already present in the payload and is passed
 * through unchanged.
 *
 * @since 1.3.5
 *
 * @param array $raw_fields    Unslashed $_POST['form_fields'].
 * @param array $group_labels  Optional [ group_name => connected_label ] for
 *                             checkbox/radio groups (see gscelef_atomic_group_labels()).
 * @param array $file_urls     Optional [ element_id => [url,...] ] for file-upload
 *                             fields (see gscelef_atomic_collect_file_urls()).
 * @return array<string,string>
 */
private function gscelef_atomic_fields_to_data(array $raw_fields, array $group_labels = array(), array $file_urls = array())
{
    $data = array();

    foreach ($raw_fields as $field) {

        if (! is_array($field)) {
            continue;
        }

        $label = isset($field['label']) ? sanitize_text_field($field['label']) : '';
        $name  = isset($field['name'])  ? sanitize_text_field($field['name'])  : '';
        $fid   = isset($field['id'])    ? sanitize_text_field($field['id'])    : '';

        $value = isset($field['value']) ? $field['value'] : '';

        if (is_array($value)) {
            // checkbox / radio groups, multi-selects, file lists
            $value = implode(', ', array_map('sanitize_text_field', $value));
        } else {
            $value = sanitize_text_field($value);
        }

        $field_type = isset($field['type']) ? sanitize_key($field['type']) : '';

        // File-upload field: the value is never in $_POST (the browser puts the
        // File in $_FILES). Use the final URL(s) Elementor produced for this
        // element id, captured via the core wp_handle_upload filter - no second
        // upload. Multiple files -> comma-separated URLs.
        if (('file' === $field_type || isset($file_urls[$fid])) && ! empty($file_urls[$fid]) && is_array($file_urls[$fid])) {
            $value = implode(', ', array_map('esc_url_raw', $file_urls[$fid]));
        }

        // A submitted label that is identical to the field's own `name` means
        // the browser could not resolve a real connected-Label text for it -
        // either a grouped checkbox/radio (Elementor's frontend JS always
        // submits the shared group `name` as the label for these, never the
        // connected Label widget) or a single field with no placeholder
        // fallback, in practice a Select whose Label widget the browser could
        // not associate. Swap in the label gscelef_atomic_group_labels()
        // resolved from the saved form data, if any, so a sheet header made
        // from the visible label still matches. `name` and the element id
        // stay as aliases either way.
        if (
            '' !== $name
            && $label === $name
            && isset($group_labels[$name])
            && '' !== $group_labels[$name]
        ) {
            $label = sanitize_text_field($group_labels[$name]);
        }

        // The atomic "Date picker" is an <input type="date"> and always submits
        // an ISO date (YYYY-MM-DD). Convert it to the site's configured date
        // format (Settings -> General -> Date Format) before it reaches the
        // sheet, so it matches the date columns the plugin already writes with
        // date_i18n(). Never hard-code a format; leave anything that is not a
        // plain ISO date untouched.
        if ('date' === $field_type && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $gscelef_date_ts = strtotime($value . ' 00:00:00');
            if (false !== $gscelef_date_ts) {
                $value = date_i18n(get_option('date_format'), $gscelef_date_ts);
            }
        }

        // Candidate header keys, most human-readable first.
        $keys = array();
        if ('' !== $label) {
            $keys[] = $label;
        }
        if ('' !== $name) {
            $keys[] = $name;
        }
        if ('' !== $fid) {
            $keys[] = $fid;
        }
        $keys = array_values(array_unique($keys));

        if (empty($keys)) {
            continue;
        }

        // Primary key: written unconditionally (existing behaviour for labelled fields).
        $primary = array_shift($keys);
        $data[$primary] = $value;

        // Aliases: only fill a header not already claimed by another field.
        foreach ($keys as $alias) {
            if (! array_key_exists($alias, $data)) {
                $data[$alias] = $value;
            }
        }
    }

    return $data;
}

/**
 * Resolve the connected Label text for atomic-form fields whose submitted
 * `label` is not usable as-is, keyed by the same identifier the field submits
 * as its `name` (so gscelef_atomic_fields_to_data() can look it up directly).
 *
 * Two cases share this one lookup, both solved by the same underlying data —
 * a `_cssid` -> connected-Label-text map read from `_elementor_data` (the same
 * `<label for>` relation the browser uses):
 *
 *  - Grouped checkbox / radio: Elementor's frontend groups these inputs by
 *    their shared `name` attribute and, for a group, submits that `name` as
 *    the field label (the connected Label widget is only resolved by the
 *    browser for single fields).
 *  - A single field (Select, in practice) with a connected Label widget the
 *    browser could not resolve to text: Select has no placeholder fallback,
 *    so when it isn't connected the browser submits the field's own
 *    auto-generated CSS id as both `label` and `name`.
 *
 * Returns [ name_or_group_name => label_text ].
 *
 * @since 1.3.5
 *
 * @param int    $post_id Owning document id (the popup / page).
 * @param string $form_id The e-form element id.
 * @return array<string,string>
 */
private function gscelef_atomic_group_labels($post_id, $form_id)
{
    $labels = array();

    $raw  = get_post_meta($post_id, '_elementor_data', true);
    $tree = is_array($raw) ? $raw : json_decode(is_string($raw) ? $raw : '', true);
    if (! is_array($tree) || '' === (string) $form_id) {
        return $labels;
    }

    // Locate the atomic form node.
    $form_node = null;
    $find = function ($nodes) use (&$find, $form_id, &$form_node) {
        foreach ((array) $nodes as $n) {
            if (! is_array($n)) {
                continue;
            }
            if (('e-form' === ($n['elType'] ?? '')) && ((string) ($n['id'] ?? '') === (string) $form_id)) {
                $form_node = $n;
                return;
            }
            if (! empty($n['elements'])) {
                $find($n['elements']);
                if (null !== $form_node) {
                    return;
                }
            }
        }
    };
    $find($tree);
    if (! is_array($form_node) || empty($form_node['elements'])) {
        return $labels;
    }

    // Collect label text by input-id, the CSS ids that belong to each
    // checkbox/radio group, and - for every OTHER single field type - the
    // identifier it submits as `name` (its own `name` setting, or its `_cssid`
    // when unset, mirroring the browser's own name-fallback).
    $label_by_input = array();
    $cssids_by_group = array();
    $name_by_cssid  = array();
    $single_field_types = array('e-form-input', 'e-form-textarea', 'e-form-select', 'e-form-date-picker', 'e-form-file-upload');
    $walk = function ($nodes) use (&$walk, &$label_by_input, &$cssids_by_group, &$name_by_cssid, $single_field_types) {
        foreach ((array) $nodes as $n) {
            if (! is_array($n)) {
                continue;
            }
            $wt = $n['widgetType'] ?? '';
            $s  = (isset($n['settings']) && is_array($n['settings'])) ? $n['settings'] : array();

            if ('e-form-label' === $wt) {
                $for = $this->gscelef_atomic_prop($s, 'input-id');
                $txt = $this->gscelef_atomic_prop($s, 'text');
                if (is_array($txt)) {
                    $txt = isset($txt['content']['value']) ? $txt['content']['value']
                    : (isset($txt['content']) && is_string($txt['content']) ? $txt['content'] : '');
                }
                $txt = is_string($txt) ? trim(wp_strip_all_tags($txt)) : '';
                if ('' !== $for && '' !== $txt) {
                    $label_by_input[$for] = $txt;
                }
            }

            if ('e-form-checkbox' === $wt || 'e-form-radio-button' === $wt) {
                $group = $this->gscelef_atomic_prop($s, 'name');
                $cssid = $this->gscelef_atomic_prop($s, '_cssid');
                if (is_string($group) && '' !== $group && is_string($cssid) && '' !== $cssid) {
                    $cssids_by_group[$group][] = $cssid;
                }
            }

            if (in_array($wt, $single_field_types, true)) {
                $cssid = $this->gscelef_atomic_prop($s, '_cssid');
                $name  = $this->gscelef_atomic_prop($s, 'name');
                $key   = (is_string($name) && '' !== $name) ? $name : $cssid;
                if (is_string($cssid) && '' !== $cssid && is_string($key) && '' !== $key) {
                    $name_by_cssid[$cssid] = $key;
                }
            }

            if (! empty($n['elements'])) {
                $walk($n['elements']);
            }
        }
    };
    $walk($form_node['elements']);

    foreach ($cssids_by_group as $group => $cssids) {
        foreach ($cssids as $cssid) {
            if (isset($label_by_input[$cssid])) {
                $labels[$group] = $label_by_input[$cssid];
                break;
            }
        }
    }

    // Single fields (e.g. an unconnected Select) whose own cssid has a
    // resolved connected-Label text - only fills a key the group logic above
    // did not already claim.
    foreach ($name_by_cssid as $cssid => $key) {
        if (isset($label_by_input[$cssid]) && '' !== $label_by_input[$cssid] && ! isset($labels[$key])) {
            $labels[$key] = $label_by_input[$cssid];
        }
    }

    return $labels;
}

/**
 * Read a possibly-transformable atomic prop value:
 * [ '$$type' => '<type>', 'value' => X ]  ->  X   (otherwise the value as-is).
 *
 * @since 1.3.5
 *
 * @param array  $settings The element's settings array.
 * @param string $key      Prop key.
 * @return mixed
 */
private function gscelef_atomic_prop(array $settings, $key)
{
    if (! isset($settings[$key])) {
        return '';
    }
    $v = $settings[$key];
    if (is_array($v) && array_key_exists('value', $v)) {
        return $v['value'];
    }
    return $v;
}

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
  $query = $wpdb->get_results("SELECT ID,post_title,meta_value,meta_key from " . $wpdb->prefix . "posts as p JOIN " . $wpdb->prefix . "postmeta as pm on p.ID = pm.post_id where pm.meta_key='__elementor_forms_snapshot' AND p.post_type='page'");


  return $query;

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

    $query = $wpdb->get_results(
        $wpdb->prepare(
            "
            SELECT
            pm.meta_id,
            pm.post_id,
            pm.meta_key,
            pm.meta_value,
            p.post_title,
            p.post_type,
            p.post_status
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p
            ON p.ID = pm.post_id
            WHERE pm.meta_value LIKE %s OR pm.meta_key = %s
            ORDER BY pm.meta_id DESC
            ",
            '%gscele_form_feeds%',
            'metform_gs_settings'
        )
    );

    return !empty($query) ? $query : [];

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