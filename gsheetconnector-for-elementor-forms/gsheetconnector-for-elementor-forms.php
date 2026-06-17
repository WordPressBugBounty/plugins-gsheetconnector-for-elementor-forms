<?php
/**
 * Plugin Name: GSheetConnector for Elementor Forms
 * Plugin URI: https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro
 * Description: Send your Elementor Form data to your Google Spreadsheet.
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Version: 1.3.2
 * Author: GSheetConnector
 * Author URI: https://www.gsheetconnector.com/
 * Text Domain: gsheetconnector-for-elementor-forms
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals

// Defined Global Variable for plugin activation
global $gsc_activate_the_plugin;
$gsc_activate_the_plugin = false;

$plugin = plugin_basename(__FILE__);
$gsc_parent_plugins = 'elementor-pro/elementor-pro.php';
$gsc_parent_plugins_free = 'elementor/elementor.php';
$gsc_parent_plugins_metform = 'metform/metform.php';
$parent_plugin_pro_elements = 'pro-elements/pro-elements.php'; // Correct definition for Pro Elements

/**
 * Fixed multisite activation issue
 * @since 1.0.11
 */

$gsc_current_site_id = get_current_blog_id();

// Check if Multisite and single site activated plugin code
if ((is_multisite() && !empty($current_site_id))) {
    function gsc_get_activated_plugins_for_site($site_id)
    {
        // Switch to the specific site
        switch_to_blog($site_id);

        // Get the list of activated plugins for the current site
        $activated_plugins = get_option('active_plugins');

        // Restore the current site
        restore_current_blog();

        return $activated_plugins;
    }

    $active_plugins = gsc_get_activated_plugins_for_site($current_site_id);

    if ((in_array($gsc_parent_plugins, $active_plugins)) && (in_array($gsc_parent_plugins_free, $active_plugins)) || (in_array($gsc_parent_plugins_metform, $active_plugins)) || (in_array($parent_plugin_pro_elements, $active_plugins))) {
        $gsc_activate_the_plugin = true;
    }
}

// Check if Multisite and network activated plugin code
if (is_multisite()) {
    $active_plugins = get_site_option('active_sitewide_plugins');

    if ((array_key_exists($gsc_parent_plugins, $active_plugins)) && (array_key_exists($gsc_parent_plugins_free, $active_plugins)) || (array_key_exists($gsc_parent_plugins_metform, $active_plugins)) || (array_key_exists($parent_plugin_pro_elements, $active_plugins))) {
        $gsc_activate_the_plugin = true;
    }
}
// Check if Singlesite activation of plugin code
else {
    $active_plugins = get_option('active_plugins');

    if ((in_array($gsc_parent_plugins, $active_plugins)) && (in_array($gsc_parent_plugins_free, $active_plugins)) || (in_array($gsc_parent_plugins_metform, $active_plugins)) || (in_array($parent_plugin_pro_elements, $active_plugins))) {
        $gsc_activate_the_plugin = true;
    }
}

// If Elementor Pro version of plugin is active, stop execution
if (is_plugin_active('gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php')) {
    return; // Exit function
}

if ($gsc_activate_the_plugin) {
    /* Freemius  Start */
    if (!function_exists('gfef_fs')) {

        // Create a helper function for easy SDK access.
        function gfef_fs()
        {
            global $gfef_fs;

            if (!isset($gfef_fs)) {
                // Include Freemius SDK.
                require_once dirname(__FILE__) . '/lib/vendor/freemius/start.php';

                $gfef_fs = fs_dynamic_init(array(
                    'id' => '11322',
                    'slug' => 'gsheetconnector-for-elementor-forms',
                    'type' => 'plugin',
                    'public_key' => 'pk_ad6fbd1729351c72fb0d9db6220ec',
                    'is_premium' => false,
                    'has_addons' => false,
                    'has_paid_plans' => false,
                    'menu' => array(
                        'slug' => 'gsheetconnector-elementor-config',
                        'account' => false,
                        'support' => false,
                    ),
                ));
            }

            return $gfef_fs;
        }

        // Init Freemius.
        gfef_fs();
        // Signal that SDK was initiated.
        do_action('gfef_fs_loaded');
    }
}

/* Freemius End */

// Declare some global constants
define('GS_CONN_ELE_VERSION', '1.3.2');
define('GS_CONN_ELE_DB_VERSION', '1.3.2');
define('GS_CONN_ELE_ROOT', dirname(__FILE__));
define('GS_CONN_ELE_URL', plugins_url('/', __FILE__));
define('GS_CONN_ELE_BASE_FILE', basename(dirname(__FILE__)) . '/gsheetconnector-for-elementor-forms.php');
define('GS_CONN_ELE_BASE_NAME', plugin_basename(__FILE__));
define('GS_CONN_ELE_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('GS_CONN_ELE_PRODUCT_NAME', 'Google Sheet Connector Elementor');
define('GS_CONN_ELE_CURRENT_THEME', get_stylesheet_directory());
define('GS_CONN_ELE_API_URL', 'https://oauth.gsheetconnector.com/api-cred.php');
define('GS_CONN_ELE_STORE_URL', 'https://gsheetconnector.com');
define('GS_CONN_ELE_TEXTDOMAIN', 'gsheetconnector-for-elementor-forms');

include_once(GS_CONN_ELE_ROOT . '/lib/google-sheets.php');

if (!class_exists('GsEl_Connector_Utility')) {
    include(GS_CONN_ELE_ROOT . '/includes/gsc-elementor-utility.php');
}

/*
 * Main connector class
 * @class GSC_Elementor_Init
 * @since 1.0
 */

class GSC_Elementor_Init
{
    public $_gfgsc_googlesheet;
    /**
     *  Set things up.
     *  @since 1.0.0
     */
    public function __construct()
    {
        //run on activation of plugin
        register_activation_hook(__FILE__, array($this, 'gsc_elementor_activate'));


        register_uninstall_hook(__FILE__, array('GSC_Elementor_Init', 'gs_elemnt_uninstall_free'));

        // validate is eleforms plugin exist
        add_action('admin_init', array($this, 'gsc_elementor_validate_parent_plugin_exists'));

         // run upgradation
        add_action('admin_init', array($this, 'run_on_upgrade'));

         // ADD THIS: Run manual upgrade from query string
        add_action('admin_init', array($this, 'handle_manual_upgrade_fix'));

       // load the classes
        add_action('init', array($this, 'load_all_classes'));

        // load the js and css files
        add_action('init', array($this, 'load_css_and_js_files'));

        // Load text domain
        // add_action('init', array($this, 'gsheetconnector_elementor_load_plugin_textdomain'));

        add_action('elementor/editor/before_enqueue_scripts', array($this, 'add_js_files'));

        add_action('elementor_pro/init', array($this, 'gsc_elementor_widget'));

        add_action('elementor/editor/after_save', array($this, 'gsc_elementor_after_save_settings'), 9999, 2);
        // Display widget to dashboard
        add_action('wp_dashboard_setup', array($this, 'add_gsc_elementor_connector_summary_widget'), 10000, 3);

        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);

    }


/**
 * Load plugin textdomain for translation support.
 *
 * This function registers the plugin's textdomain so that
 * translation files from the /languages directory can be loaded.
 *
 * @since 1.0.0
 * @return void
 */
// public function gsheetconnector_elementor_load_plugin_textdomain()
// {
//     load_plugin_textdomain(
//         'gsheetconnector-for-elementor-forms',
//         false,
//         plugin_basename(dirname(__FILE__)) . '/languages'
//     );
// }

/**
 * Handle manual upgrade fix trigger via URL.
 *
 * This function checks for a specific query parameter (`run_upgrade_fix`)
 * along with a valid nonce and user capability. If all conditions are met,
 * it executes the plugin's upgrade/activation routine manually.
 *
 * This is useful for running database updates or migration tasks
 * without reactivating the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function handle_manual_upgrade_fix()
{
    if (
        isset($_GET['run_upgrade_fix']) &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'run_upgrade_fix_action') &&
        current_user_can('manage_options')
    ) {
        $this->run_on_activation();
    }
}

/**
 * Execute plugin uninstall cleanup process.
 *
 * This static function is triggered during plugin uninstall and is responsible
 * for removing all plugin-related data from the database.
 *
 * It supports both single-site and multisite environments:
 * - In multisite, it loops through all sites and performs cleanup for each.
 * - In single-site, it directly runs the cleanup for the current site.
 *
 * It ensures that all stored options, metadata, and custom data created
 * by the plugin are properly deleted.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
public static function gs_elemnt_uninstall()
{
    global $wpdb;
    self::run_on_uninstall();
    if (function_exists('is_multisite') && is_multisite()) {
        /*  Get all blog ids; foreach of them call the uninstall procedure */
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for multisite uninstall cleanup.
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
        /* Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found */
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            self::delete_for_site();
            restore_current_blog();
        }
        return;
    }
    self::delete_for_site();
}

/**
 * Add custom plugin row meta links in the plugins list table.
 *
 * This function adds additional links such as Documentation and Support
 * to the plugin's row meta section on the Plugins page in WordPress admin.
 *
 * It checks if the current plugin matches the target plugin file and
 * then appends custom links to the existing plugin metadata.
 *
 * @since 1.1.4
 *
 * @param array  $plugin_meta Existing plugin meta links (version, author, etc.).
 * @param string $plugin_file Plugin file path relative to the plugins directory.
 *
 * @return array Modified plugin meta links including custom entries.
 */
public function plugin_row_meta($plugin_meta, $plugin_file)
{
    if (GS_CONN_ELE_BASE_NAME === $plugin_file) {
        $row_meta = [
            'docs' => '<a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector" aria-label="' . esc_attr(esc_html__('View Documentation', 'gsheetconnector-for-elementor-forms')) . '" target="_blank">' . esc_html__('Docs', 'gsheetconnector-for-elementor-forms') . '</a>',
            'ideo' => '<a href="https://www.gsheetconnector.com/support" aria-label="' . esc_attr(esc_html__('Get Support', 'gsheetconnector-for-elementor-forms')) . '" target="_blank">' . esc_html__('Support', 'gsheetconnector-for-elementor-forms') . '</a>',
        ];

        $plugin_meta = array_merge($plugin_meta, $row_meta);
    }

    return $plugin_meta;
}

/**
 * Get or initialize Google Sheets API object.
 *
 * This function returns a singleton instance of the Google Sheets handler.
 * If the object is already initialized, it reuses the existing instance.
 * Otherwise, it creates a new instance, performs authentication, and stores it
 * for future use.
 *
 * It ensures that the Google Sheets connection is established only once
 * per request to improve performance.
 *
 * @since 1.0.0
 *
 * @return object|null Google Sheets object instance or null on failure.
 */
public function get_googlesheet_object()
{
    try {
        if ($this->_gfgsc_googlesheet) {
            return $this->_gfgsc_googlesheet;
        }

        $google_sheet = new GSC_Elementor_Free();
        $google_sheet->auth();

        $this->_gfgsc_googlesheet = $google_sheet;
        return $google_sheet;
    } catch (Exception $e) {
    }
}

/**
 * Validate required parent plugins before activation.
 *
 * This function checks whether any of the required parent plugins
 * (Elementor Pro, Pro Elements, or MetForm) are active.
 *
 * If none of the required plugins are active:
 * - It displays an admin notice informing the user.
 * - Deactivates the current plugin to prevent malfunction.
 * - Removes the "plugin activated" flag from the URL.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function gsc_elementor_validate_parent_plugin_exists()
{
    $plugin = plugin_basename(__FILE__);

    if (
        !is_plugin_active('elementor-pro/elementor-pro.php') &&
        !is_plugin_active('pro-elements/pro-elements.php') &&
        !is_plugin_active('metform/metform.php')
    ) {
        add_action('admin_notices', array($this, 'eleform_missing_notice'));
        add_action('network_admin_notices', array($this, 'eleform_missing_notice'));
        deactivate_plugins($plugin);
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Activation context, handled by WordPress core
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

/**
 * Display admin notice when required parent plugins are missing.
 *
 * This function outputs an error message in the WordPress admin panel
 * if none of the required plugins (Elementor Pro, Pro Elements, or MetForm)
 * are installed or activated.
 *
 * It uses the plugin's utility method to render a styled admin notice.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function eleform_missing_notice()
{
    $plugin_error = GsEl_Connector_Utility::instance()->admin_notice(array(
        'type' => 'error',
        'message' => esc_html__('Google Sheet Connector Elementor Add-on requires Elementor Pro or Pro Elements or MetForm Plugin to be installed and activated.', 'gsheetconnector-for-elementor-forms')
    ));
    echo wp_kses_post($plugin_error);
}

/**
 * Handle plugin activation process.
 *
 * This function runs necessary setup tasks when the plugin is activated.
 * It initializes required data and ensures proper setup for both
 * single-site and multisite environments.
 *
 * - Executes common activation tasks.
 * - If multisite and network-wide activation is enabled, it loops through
 *   all sites and runs setup for each site individually.
 * - Otherwise, it runs setup only for the current site.
 *
 * @since 1.0.0
 *
 * @param bool $network_wide Whether the plugin is activated network-wide.
 *
 * @return void
 */
public function gsc_elementor_activate($network_wide)
{
    global $wpdb;
    $this->run_on_activation();

    if (function_exists('is_multisite') && is_multisite()) {
        if ($network_wide) {
            $sites = get_sites(array('fields' => 'ids'));
            foreach ($sites as $blog_id) {
                switch_to_blog($blog_id);
                $this->run_for_site();
                restore_current_blog();
            }
            return;
        }
    }

    $this->run_for_site();
}

/**
 * Process Elementor form settings after save and sync with Google Sheets.
 *
 * This function is triggered after Elementor form settings are saved.
 * It parses the submitted Elementor data, detects form widgets, and
 * prepares Google Sheets configuration including spreadsheet creation,
 * sheet selection (manual/auto), and header mapping.
 *
 * Key responsibilities:
 * - Validate required plugin dependency (Elementor Pro).
 * - Parse and iterate through Elementor widget data.
 * - Create a new Google Sheet if required.
 * - Handle manual and automatic sheet/tab configuration.
 * - Build and store header mappings based on selected fields.
 * - Save updated settings in post meta and options.
 *
 * It ensures that the form configuration is properly synced with
 * Google Sheets for future submissions.
 *
 * @since 1.0.0
 *
 * @param int   $gsc_elementor_post_id The Elementor post ID.
 * @param array $gsc_elementor_formdata Raw Elementor form data.
 *
 * @return void
 */
public static function gsc_elementor_after_save_settings($gsc_elementor_post_id, $gsc_elementor_formdata)
{
    global $gsc_elementor_header_list, $gsc_elementor_spreadsheetid, $gsc_elementor_exclude_headertype;


    if (!is_plugin_active('elementor-pro/elementor-pro.php')) {
        return;
    }
   // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Elementor internal request, nonce not available
    if (!isset($_REQUEST['actions']) || empty($_REQUEST['actions'])) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $gsc_elementor_data = json_decode(sanitize_text_field(wp_unslash($_REQUEST['actions'])), true);
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Elementor internal request
    $elementor_post_id = isset( $_REQUEST['editor_post_id'] ) ? absint( wp_unslash( $_REQUEST['editor_post_id'] ) ): 0;

    $gsc_elementor_data = \ElementorPro\Plugin::elementor()->db->iterate_data(
        $gsc_elementor_data,
        function ($gsc_elementor_element) use (&$gsc_elementor_spreadsheetid, &$gsc_elementor_header_list) {
            $get_create_sheet_name = get_option('gsc_create_sheet_elementor_settings');
            $gsc_elementor_settings = $gsc_elementor_element['settings'];

            $widget_type = $gsc_elementor_element['widgetType'] ?? 'N/A';


            if (in_array($widget_type, ['form', 'global'], true)) {
                $gsc_elemetor_new_create_sheet_name = $gsc_elementor_settings['gs_elementor_setting_create_sheet'] ?? '';

                    // Create new sheet if changed
                if (!empty($gsc_elemetor_new_create_sheet_name) && $get_create_sheet_name !== $gsc_elemetor_new_create_sheet_name) {
                    update_option('gsc_create_sheet_elementor_settings', $gsc_elemetor_new_create_sheet_name);

                    $doc = new GSC_Elementor_Pro();
                    $doc->auth();

                    $spreadsheet = $doc->gsheet_create_google_sheet($gsc_elemetor_new_create_sheet_name);
                    if ($spreadsheet && $spreadsheet['result']) {
                        $selected_sheet_id = $spreadsheet['spreadsheet']['spreadsheet_id'] ?? '';
                        $selected_sheet_name = $spreadsheet['spreadsheet']['spreadsheet_name'] ?? '';

                        $sheet_data = get_option('elefgs_sheetId');
                        $sheet_data[$selected_sheet_id] = $selected_sheet_name;
                        update_option('elefgs_sheetId', $sheet_data);

                        $gsc_elementor_spreadsheetid = $selected_sheet_id;
                        $gsc_elementor_sheetname = 'Sheet1';
                        $sheetName = 'Sheet1';
                    } else {

                        return;
                    }
                }

                    // === Sheet Settings (Manual > Auto) ===
                $manual_enabled = $gsc_elementor_settings['enable_manual_sheet_settings'] ?? '';
                if ($manual_enabled === 'yes') {
                    $gsc_elementor_spreadsheetid = $gsc_elementor_settings['manual_sheet_id'] ?? '';
                    $gsc_elementor_sheetname = $gsc_elementor_settings['manual_tab_id'] ?? 'Sheet1';
                    $sheetName = $gsc_elementor_settings['manual_tab_name'] ?? $gsc_elementor_sheetname;
                } elseif (!empty($gsc_elementor_settings['gs_spreadsheet_id']) && !empty($gsc_elementor_settings['gs_spreadsheet_tab_name'])) {
                    $gsc_elementor_spreadsheetid = $gsc_elementor_settings['gs_spreadsheet_id'];
                    $gsc_elementor_sheetname = $gsc_elementor_settings['gs_spreadsheet_tab_name'];
                    $sheet_tabId = get_option('elefgs_tabsId');
                    $sheetName = $sheet_tabId[$gsc_elementor_spreadsheetid][$gsc_elementor_sheetname] ?? $gsc_elementor_sheetname;
                } else {
                    $gsc_elementor_spreadsheetid = '';
                    $gsc_elementor_sheetname = '';
                    $sheetName = 'Sheet1';
                }

                    // === Proceed Only if Sheet ID and Tab ID exist ===
                if (!empty($gsc_elementor_spreadsheetid) && $gsc_elementor_sheetname !== '' && $gsc_elementor_sheetname !== null) {
                        // Header building
                    $headerOptions = [
                        'Entry ID' => 'headers[Entry ID]',
                        'Entry Date' => 'headers[Entry Date]',
                        'Post ID' => 'headers[Post ID]',
                        'User Name' => 'headers[User Name]',
                        'User IP' => 'headers[User IP]',
                        'User ID' => 'headers[User ID]',
                        'Referrer' => 'headers[Referrer]',
                        'User Agent' => 'headers[User Agent]',
                    ];
                    $gsc_elementor_header_list = ['Entry ID'];
                    foreach ($headerOptions as $headerLabel => $settingKey) {
                        if (($gsc_elementor_settings[$settingKey] ?? '') === 'yes') {
                            $gsc_elementor_header_list[] = $headerLabel;
                        }
                    }
                    $formField = $gsc_elementor_settings['form_fields'] ?? [];
                    foreach ($formField as $gsc_elementor_form_fields) {
                        $header_index = $gsc_elementor_form_fields['field_label'] ?? ucfirst($gsc_elementor_form_fields['custom_id'] ?? '');
                        if (($gsc_elementor_settings["headers[$header_index]"] ?? '') === 'yes') {
                            $gsc_elementor_header_list[] = $header_index;
                        }
                    }
                    $gsc_elementor_header_list = array_values(array_unique($gsc_elementor_header_list));
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    update_post_meta($elementor_post_id,'gs_elementor_settings',$gsc_elementor_header_list);

                    $gsc_ef_settings = [
                        'spreadsheet_id' => $gsc_elementor_spreadsheetid,
                        'tab_id' => $gsc_elementor_sheetname,
                        'tab_name' => $sheetName,
                    ];
                    update_option('gsc_elementor_settings', $gsc_ef_settings);
                }
            }
        }
    );
}

/**
 * Load admin CSS and JavaScript files.
 *
 * This function hooks custom styles and scripts into the WordPress
 * admin panel by attaching them to the appropriate admin actions.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function load_css_and_js_files()
{
    add_action('admin_print_styles', array($this, 'add_css_files'));
    add_action('admin_print_scripts', array($this, 'add_js_files'));
}

/**
 * Load required plugin classes conditionally.
 *
 * This function includes necessary class files only when the plugin
 * is activated and ready to initialize. It ensures that classes are
 * loaded only once by checking if they already exist.
 *
 * It helps optimize performance and prevents class redeclaration errors.
 *
 * @since 1.0.0
 *
 * @return void
 */
public function load_all_classes()
{

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $GLOBALS['activate_the_plugin'] is internally controlled, not user input
    if ($GLOBALS['gsc_activate_the_plugin'] == true) {

        if (!class_exists('GSC_Elementor_Integration')) {
            include(GS_CONN_ELE_PATH . 'includes/class-gsc-elementor-integration.php');
        }

        if (!class_exists('gsc_elementor_sidemenu')) {
            include(GS_CONN_ELE_PATH . 'includes/gsc-elementor-sidemenu.php');
        }
        if (!class_exists('Elementor_Extensions')) {
            include(GS_CONN_ELE_PATH . 'includes/pages/extensions/gs-elementor-extension-service.php');
        }
        if (!class_exists('gscelef_error_logs')) {
            include GS_CONN_ELE_PATH . '/includes/gsc-elementor-error-logs.php';
        }
    }
}

/**
 * Enqueue admin CSS files for the plugin settings page.
 *
 * Loads all required stylesheet files only on the
 * Google Sheet Connector Elementor admin settings page.
 *
 * @since 1.0
 *
 * @return void
 */
public function add_css_files()
{
    if (is_admin() && (
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe usage for enqueue logic based on admin page check
        isset($_GET['page']) && $_GET['page'] == 'gsheetconnector-elementor-config')) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('gsc-elementor', GS_CONN_ELE_URL . 'assets/css/gsc-elementor.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-header', GS_CONN_ELE_URL . 'assets/css/header.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-footer', GS_CONN_ELE_URL . 'assets/css/footer.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-extras', GS_CONN_ELE_URL . 'assets/css/extra-style.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-global', GS_CONN_ELE_URL . 'assets/css/global.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-pro-feature', GS_CONN_ELE_URL . 'assets/css/pro-feature.css', GS_CONN_ELE_VERSION, true);
        wp_enqueue_style('gsc-responsive', GS_CONN_ELE_URL . 'assets/css/responsive.css', GS_CONN_ELE_VERSION, true);

        wp_enqueue_style('gsc-fontawesome', GS_CONN_ELE_URL . 'assets/css/fontawesome.css', GS_CONN_ELE_VERSION, true);
    }
}

/**
 * Enqueue admin and editor JavaScript files.
 *
 * Loads required JavaScript files for:
 * - Plugin admin settings page
 * - Elementor editor page
 *
 * Also localizes AJAX data for frontend editor scripts.
 *
 * @since 1.0
 *
 * @return void
 */
public function add_js_files()
{
    if (is_admin() && (
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe usage for enqueue logic based on admin page check
        isset($_GET['page']) && $_GET['page'] == 'gsheetconnector-elementor-config')) {

        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('gsc-elementor-integration-settings', GS_CONN_ELE_URL . 'assets/js/gsc-elementor-integration-settings.js', array('jquery', 'wp-color-picker'), GS_CONN_ELE_VERSION, true);

        wp_enqueue_script(
            'gsc-elementor-popup-js',
            GS_CONN_ELE_URL . 'assets/js/gsc-elementor-popup.js',
            array('jquery'),
            GS_CONN_ELE_VERSION,
            true
        );

        wp_enqueue_script(
            'system-debug-js',
            GS_CONN_ELE_URL . 'assets/js/system-debug.js',
            array('jquery'),
            GS_CONN_ELE_VERSION,
            true
        );
        wp_enqueue_script(
            'gsc-elementor-extension',
            GS_CONN_ELE_URL . 'assets/js/gs-connector-extensions.js',
            array('jquery'),
            GS_CONN_ELE_VERSION,
            true
        );
    }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe usage for enqueue logic based on admin page check
    if (isset($_GET['action']) && 'elementor' === (string) sanitize_text_field(wp_unslash($_GET['action']))) {
        wp_register_script('gsc-elementor-front-settings', GS_CONN_ELE_URL . 'assets/js/gsc-elementor-front-settings.js', false, GS_CONN_ELE_VERSION, true);
        wp_localize_script(
            'gsc-elementor-front-settings',
            'elefgs_customadmin_ajax_object',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'elefgs_sync_nonce_token' => wp_create_nonce('elefgs_sync_nonce'),
            )
        );
        wp_enqueue_script('gsc-elementor-front-settings');
    }

    /** make condition for only elementor preview */
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (isset($_GET['elementor-preview']) || isset($_GET['action']) && $_GET['action'] === 'elementor') {
        wp_enqueue_style('gsc-elementor-inner-sidebar-css', GS_CONN_ELE_URL . 'assets/css/gsc-elementor-inner-sidebar.css', [], GS_CONN_ELE_VERSION);
    }
}

/**
 * Register custom Google Sheet action for Elementor forms.
 *
 * This function integrates the plugin with Elementor Pro form widget
 * by registering a custom form action. It first checks if the PRO version
 * of the plugin is active to avoid duplicate functionality.
 *
 * If PRO is not active:
 * - Includes the action class file
 * - Instantiates the action class
 * - Registers the action with Elementor form module
 *
 * @since 1.0.0
 *
 * @return void
 */
public function gsc_elementor_widget()
{

        // If Elementor Pro version of plugin is active, stop execution
    if (is_plugin_active('gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php')) {
            return; // Exit function
        }


        // Here its safe to include our action class file.
        include_once dirname(__FILE__) . '/includes/gsc-elementor-actions.php';

        // Instantiate the action class.
        $gsc_elementor_actions = new GSC_Elementor_Actions_Free;
        // Register the action with form widget.
        \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($gsc_elementor_actions->get_name(), $gsc_elementor_actions);
    }

/**
 * Called during plugin upgrade process.
 * Checks the stored plugin version and runs required upgrade routines.
 *
 * @since 1.0
 * @return void
 */
public function run_on_upgrade()
{
 $plugin_options = get_site_option('elefgs_info');

 if (
    is_array($plugin_options) &&
    isset($plugin_options['version']) &&
    $plugin_options['version'] === '1.0.23'
) {
    $this->upgrade_database_18();
}

        // update the version value
$google_sheet_info = array(
    'version'     => GS_CONN_ELE_VERSION,
    'db_version'  => GS_CONN_ELE_DB_VERSION
);

        // check if debug log file exists or not
$logFilePathToDelete = GS_CONN_ELE_PATH . "logs/log.txt";
if (file_exists($logFilePathToDelete)) {
    wp_delete_file($logFilePathToDelete);
}

update_site_option('elefgs_info', $google_sheet_info);
}

/**
 * Handles database upgrade routines for version 1.8 changes.
 * Ensures compatibility across multisite installations.
 *
 * @return void
 */
public function upgrade_database_18()
{
        // look through each of the blogs and upgrade the DB
    if (function_exists('is_multisite') && is_multisite()) {
            // Use core function to get all blog IDs (cached and safe)
        $blog_ids = get_sites(array('fields' => 'ids'));

        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            $this->upgrade_helper_18();
            restore_current_blog();
        }
    }

        // Run on current site (non-multisite or base site)
    $this->upgrade_helper_18();
}

/**
 * Helper function for DB upgrade process (version 1.8).
 * Updates/saves required API credentials after upgrade.
 *
 * @return void
 */
public function upgrade_helper_18()
{
        // Fetch and save the API credentails.
    GsEl_Connector_Utility::instance()->save_api_credentials();
}

/**
 * Registers Elementor Connector summary widget in WordPress dashboard.
 *
 * @since 1.0
 */
public function add_gsc_elementor_connector_summary_widget()
{
    $img_src = GS_CONN_ELE_URL . "assets/img/elementor-gsc.svg";
    $title = __("GSheetConnector For Elementor Forms", 'gsheetconnector-for-elementor-forms');
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin asset used in dashboard widget
    $widget_title = "<img style='width:30px;margin-right: 10px;' src='" . esc_url($img_src) . "'><span>" . esc_html($title) . "</span>";

    wp_add_dashboard_widget('elementro_gs__dashboard', $widget_title, array($this, 'elementor_gs_connector_summary_dashboard'));
}

/**
* Display widget conetents
* @since 1.0
*/
public function elementor_gs_connector_summary_dashboard()
{
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin asset used in dashboard widget
    echo '<img src="' . esc_url(GS_CONN_ELE_URL . 'assets/img/elementor-gsc.svg') . '" alt="' . esc_attr__('GSheetConnector Elementor', 'gsheetconnector-for-elementor-forms') . '" style="width:30px;">';
}

/**
 * Renders Elementor Connector dashboard widget content.
 *
 * @since 1.0
 */
public function elementor_gs_connector_pro_plugin_action_links($links)
{
        // Define the text for the "Get Pro" link
    $go_pro_text = esc_html__('Get Elementor Google Sheet Pro', 'gsheetconnector-for-elementor-forms');

        // Check if the Pro version of the plugin is installed and activated
    if (is_plugin_active('gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php')) {
            // If Pro version is active, return the links without adding the "Get Pro" link
        return $links;
    }

        // Add the action link to the plugin page with green color styling
    $links['go_pro'] = sprintf(
        '<a href="%s" target="_blank" class="gsheetconnector-pro-link" style="color: green;">%s</a>',
        esc_url('https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro'),
        $go_pro_text
    );

    return $links;
}

/**
 * Displays the WordPress debug log in a formatted admin table.
 * Shows the latest 100 log entries in reverse chronological order.
 *
 * @return bool
 */
public function display_error_log($display = true)
{
    if (!current_user_can('manage_options')) {
        return false;
    }

    $debug_log_file = WP_CONTENT_DIR . '/debug.log';

    if (!file_exists($debug_log_file)) {
        return false;
    }

    $log_lines = file(
        $debug_log_file,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    $log_lines = array_slice(array_reverse($log_lines), 0, 100);

    $valid_logs = array();

    if (!empty($log_lines)) {

        foreach ($log_lines as $line) {

            if (preg_match('/\[(.*?)\]\s(.*?):\s(.*)/', $line)) {
                $valid_logs[] = $line;
            }
        }
    }

    // No logs
// No logs
    if (empty($valid_logs)) {

        if ($display) {

            echo '<div style="max-height:500px; overflow:auto;">';
            echo '<table class="gselef-free-error-table widefat striped mt-30">';

            echo '<thead>
            <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Message</th>
            <th>File</th>
            </tr>
            </thead>';

            echo '<tbody>';

            echo '<tr>';
            echo '<td colspan="4" class="text-center">';
            echo esc_html__('No error logs found.', 'gsheetconnector-for-elementor-forms');
            echo '</td>';
            echo '</tr>';

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        }

        return false;
    }

    // Only checking logs
    if (!$display) {
        return true;
    }

    echo '<div style="max-height:500px; overflow:auto;">';
    echo '<table class="gselef-free-error-table widefat striped mt-30">';

    echo '<thead>
    <tr>
    <th>Date</th>
    <th>Type</th>
    <th>Message</th>
    <th>File</th>
    </tr>
    </thead>';

    echo '<tbody>';

    foreach ($valid_logs as $line) {

        preg_match('/\[(.*?)\]\s(.*?):\s(.*)/', $line, $matches);

        $date    = str_replace(' UTC', '', $matches[1]);
        $type    = str_replace('PHP ', '', $matches[2]);
        $message = $matches[3];

        $file = '-';

        if (preg_match('/in (.*?) on line/', $message, $file_match)) {
            $file = $file_match[1];
        }

        echo '<tr>';
        echo '<td>' . esc_html($date) . '</td>';
        echo '<td>' . esc_html($type) . '</td>';
        echo '<td>' . esc_html($message) . '</td>';
        echo '<td>' . esc_html($file) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    return true;
}
/**
 * Delete site-specific plugin data during uninstall.
 *
 * This function is responsible for cleaning up all plugin-related data
 * for a single site. It checks the uninstall setting option and only
 * proceeds with deletion if the user has opted to remove data ("Yes").
 *
 * It removes:
 * - Stored options (sheet data, tokens, settings, API credentials)
 * - Post meta related to Elementor feeds and configurations
 * - Custom database table used for storing submission data
 *
 * This ensures a complete cleanup of plugin data when uninstalled.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
private static function delete_for_site()
{

    /*  Get the saved value from the options table */
    $saved_value = get_option('gscele_elementor_uninstall_settings_free', 'No');
    if ($saved_value === 'Yes') {
        /*  db version */
        delete_site_option('elefgs_info');

        /*  sheet details */
        delete_option('elefgs_sheetId');
        delete_option('elefgs_feeds');
        delete_option('elefgs_tabsId');
        delete_option('gsc_create_sheet_elementor_settings');

        /* method save */
        delete_option('elefgs_manual_setting');

        /* auto method */
        delete_option('elefgs_access_code');
        delete_option('elefgs_verify');
        delete_option('elefgs_token');
        delete_option('is_new_client_secret_elefgscpro');
        delete_option('gsc_elementor_email_account');


        /*  elementor feeds settings */
        delete_post_meta('gscele_form_feeds', true);
        delete_post_meta('gscele_sheet_header_names', true);
        delete_post_meta('gscele_sheet_header', true);
        delete_post_meta('gscele_sort_order', true);
        delete_post_meta('gselef_status ', true);
        
        /*  delete auto api credentails */
        delete_option('Elegsc_api_creds');
        delete_site_option('Elegsc_api_creds');

        delete_option('elefgs_free_notice_review');
        delete_option('elefgs_free_notice_review_time');

        /* delete elementor Feeds table */
        global $wpdb;

        $feed_table = $wpdb->prefix . 'elementor_gsheet_submissions_values';

        $cache_key = 'table_exists_' . md5($feed_table);
        $table_exists = wp_cache_get($cache_key, 'gsheetconnector');

        if ( false === $table_exists ) {

            $like_table = $wpdb->esc_like($feed_table);

      // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
            $table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $like_table
                )
            );

            wp_cache_set($cache_key, $table_exists, 'gsheetconnector', 3600);
        }

        if ( $table_exists === $feed_table ) {

            $sql = "DROP TABLE IF EXISTS `$feed_table`";

         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( $sql );
        }

        $error_log_table = $wpdb->prefix . 'gscelef_error_logs';

       // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                'SHOW TABLES LIKE %s',
                $error_log_table
            )
        );

        if ( $table_exists === $error_log_table ) {
           
          $sql = "DROP TABLE IF EXISTS `$error_log_table`";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
          $wpdb->query( $sql );
      }

  }
}

/**
 * Delete network-level (site) options during plugin uninstall.
 *
 * This function ensures that global (multisite-level) options
 * created by the plugin are removed when the plugin is uninstalled.
 *
 * It also includes safety checks to prevent direct access and
 * ensures the code runs only in a proper WordPress uninstall context.
 *
 * @since 1.3.0
 *
 * @return void
 */
private static function run_on_uninstall()
{
    if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
        exit();
    }
    delete_site_option('elefgs_info');
}

/**
 * Runs on plugin activation.
 *
 * This function handles initial setup tasks:
 * - Creates or updates plugin site options (version & DB version)
 * - Triggers upgrade routine if DB version mismatch is detected
 * - Saves API credentials required for Google Sheets integration
 * - Creates debug/error log table for logging system events
 *
 * Supports multisite via site_options.
 *
 * @since 1.0.0
 *
 * @return void
 */
private function run_on_activation()
{
    $plugin_options = get_site_option('elefgs_info');
    if (false === $plugin_options) {

        $google_sheet_info = array(
            'version' => GS_CONN_ELE_VERSION,
            'db_version' => GS_CONN_ELE_DB_VERSION
        );
        update_site_option('elefgs_info', $google_sheet_info);
    } else if (GS_CONN_ELE_DB_VERSION != $plugin_options['version']) {
        $this->run_on_upgrade();
    } else {
    }

        // Always fetch and save API credentials
    GsEl_Connector_Utility::instance()->save_api_credentials();

        // create debug log table
    $this->gselef_create_error_log_table();
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
private function gselef_create_error_log_table(){
    global $wpdb;

    $gselef_table = $wpdb->prefix . 'gscelef_error_logs';

    // Check whether the debug log table already exists.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $gselef_table_exists = $wpdb->get_var(
        $wpdb->prepare( 'SHOW TABLES LIKE %s', $gselef_table )
    );

    // Create the table only if it does not exist.
    if ( $gselef_table_exists !== $gselef_table ) {
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
    dbDelta( $sql );
}
}

/**
 * Called on plugin activation (per site basis).
 * Initializes required plugin options for each site.
 *
 * @since 1.0.0
 */
private function run_for_site()
{
    if (!get_option('elefgs_access_code')) {
        update_option('elefgs_access_code', '');
    }
    if (!get_option('elefgs_verify')) {
        update_option('elefgs_verify', '');
    }
    if (!get_option('elefgs_token')) {
        update_option('elefgs_token', '');
    }

    /** save date for plugin activation  */
    if(!get_option('elefgs_free_plugin_activated_at')){
      update_option('elefgs_free_plugin_activated_at',time());
  }


}

}

// Initialize the google sheet connector class
$gsc_init = new GSC_Elementor_Init();

// Add custom link for our plugin
add_filter('plugin_action_links_' . GS_CONN_ELE_BASE_NAME, array($gsc_init, 'elementor_gs_connector_pro_plugin_action_links'));
