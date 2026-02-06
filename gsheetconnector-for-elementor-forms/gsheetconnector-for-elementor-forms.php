<?php
/**
 * Plugin Name: GSheetConnector for Elementor Forms
 * Plugin URI: https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro
 * Description: Send your Elementor Form data to your Google Spreadsheet.
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Version: 1.2.6
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

// Defined Global Variable for plugin activation
global $activate_the_plugin;
$activate_the_plugin = false;

$plugin = plugin_basename(__FILE__);
$parent_plugins = 'elementor-pro/elementor-pro.php';
$parent_plugins_free = 'elementor/elementor.php';
$parent_plugins_metform = 'metform/metform.php';
$parent_plugin_pro_elements = 'pro-elements/pro-elements.php'; // Correct definition for Pro Elements

/**
 * Fixed multisite activation issue
 * @since 1.0.11
 */

$current_site_id = get_current_blog_id();

// Check if Multisite and single site activated plugin code
if ((is_multisite() && !empty($current_site_id))) {
    function get_activated_plugins_for_site($site_id)
    {
        // Switch to the specific site
        switch_to_blog($site_id);

        // Get the list of activated plugins for the current site
        $activated_plugins = get_option('active_plugins');

        // Restore the current site
        restore_current_blog();

        return $activated_plugins;
    }

    $active_plugins = get_activated_plugins_for_site($current_site_id);

    if ((in_array($parent_plugins, $active_plugins)) && (in_array($parent_plugins_free, $active_plugins)) || (in_array($parent_plugins_metform, $active_plugins)) || (in_array($parent_plugin_pro_elements, $active_plugins))) {
        $activate_the_plugin = true;
    }
}

// Check if Multisite and network activated plugin code
if (is_multisite()) {
    $active_plugins = get_site_option('active_sitewide_plugins');

    if ((array_key_exists($parent_plugins, $active_plugins)) && (array_key_exists($parent_plugins_free, $active_plugins)) || (array_key_exists($parent_plugins_metform, $active_plugins)) || (array_key_exists($parent_plugin_pro_elements, $active_plugins))) {
        $activate_the_plugin = true;
    }
}
// Check if Singlesite activation of plugin code
else {
    $active_plugins = get_option('active_plugins');

    if ((in_array($parent_plugins, $active_plugins)) && (in_array($parent_plugins_free, $active_plugins)) || (in_array($parent_plugins_metform, $active_plugins)) || (in_array($parent_plugin_pro_elements, $active_plugins))) {
        $activate_the_plugin = true;
    }
}

//$pro_plugins = 'gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php';
//
//if (in_array($pro_plugins, $active_plugins)) {
//    return;
//}

if ($activate_the_plugin) {
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
define('GS_CONN_ELE_VERSION', '1.2.6');
define('GS_CONN_ELE_DB_VERSION', '1.2.6');
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

load_plugin_textdomain('gsheetconnector-for-elementor-forms', false, basename(dirname(__FILE__)) . '/languages');

//Include Library Files
require_once GS_CONN_ELE_ROOT . '/lib/vendor/autoload.php';

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

        // validate is eleforms plugin exist
        add_action('admin_init', array($this, 'gsc_elementor_validate_parent_plugin_exists'));

        // load the classes
        add_action('init', array($this, 'load_all_classes'));

        // load the js and css files
        add_action('init', array($this, 'load_css_and_js_files'));
        // run upgradation
        add_action('admin_init', array($this, 'run_on_upgrade'));

        add_action('elementor/editor/before_enqueue_scripts', array($this, 'add_js_files'));

        add_action('elementor_pro/init', array($this, 'gsc_elementor_widget'));

        add_action('elementor/editor/after_save', array($this, 'gsc_elementor_after_save_settings'), 9999, 2);
        // Display widget to dashboard
        add_action('wp_dashboard_setup', array($this, 'add_gsc_elementor_connector_summary_widget'), 10000, 3);

        add_filter('plugin_row_meta', [$this, 'plugin_row_meta'], 10, 2);

        // Delete Feed
        add_action('wp_ajax_delete_feed', array($this, 'delete_feed'));

        // ADD THIS: Run manual upgrade from query string
        add_action('admin_init', array($this, 'handle_manual_upgrade_fix'));
    }

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
     * Deleting Feed.
     *
     * @since 1.0.0
     */
    public function delete_feed()
    {
        try {
            check_ajax_referer('elementorform-ajax-nonce', 'security');

            if (isset($_POST['feed_id'])) {
                $feedId = intval($_POST['feed_id']);

                if ($feedId) {
                    $deleted  = delete_metadata('post', $feedId, 'gscele_form_feeds');
                    $deleted1 = delete_metadata_by_mid('post', $feedId);

                    echo $deleted1 ? 'success' : 'error';
                }
            }

            wp_die();
        } catch (Exception $e) {
            GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
        }
    }


    /**
     * Plugin row meta.
     *
     * Adds row meta links to the plugin list table
     *
     * Fired by `plugin_row_meta` filter.
     *
     * @since 1.1.4
     * @access public
     *
     * @param array  $plugin_meta An array of the plugin's metadata, including
     *                            the version, author, author URI, and plugin URI.
     * @param string $plugin_file Path to the plugin file, relative to the plugins
     *                            directory.
     *
     * @return array An array of plugin row meta links.
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
     * If eleForms plugin is not installed or activated then throw the error
     *
     * @access public
     * @return mixed error_message, an array containing the error message
     *
     * @since 1.0 initial version
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
     * Do things on plugin activation
     * @since 1.0.0
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
     * Action fire after Save from Elementor Editor.
     *
     * @param int   $gsc_elementor_post_id Post ID.
     * @param array $gsc_elementor_formdata Template Data.
     */
    public static function gsc_elementor_after_save_settings($gsc_elementor_post_id, $gsc_elementor_formdata)
    {
        global $gsc_elementor_header_list, $gsc_elementor_spreadsheetid, $gsc_elementor_exclude_headertype;

        // Only run if Elementor Pro is active
        if (!is_plugin_active('elementor-pro/elementor-pro.php')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Cannot verify nonce, source is Elementor internal save hook
        if (!isset($_REQUEST['actions']) || empty($_REQUEST['actions'])) {
            return;
        }

        // Safely decode and sanitize form config JSON from Elementor
        // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $gsc_elementor_data = json_decode(
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
            sanitize_text_field(wp_unslash($_REQUEST['actions'])),
            true
        );
        // phpcs:enable

        // Iterate over saved data to collect header and sheet details
        $gsc_elementor_data = \ElementorPro\Plugin::elementor()->db->iterate_data(
            $gsc_elementor_data,
            function ($gsc_elementor_element) {
                if (
                    isset($gsc_elementor_element['widgetType']) &&
                    in_array((string) $gsc_elementor_element['widgetType'], ['form', 'global'], true)
                ) {
                    global $gsc_elementor_header_list, $gsc_elementor_spreadsheetid;

                    $gsc_elementor_settings = $gsc_elementor_element['settings'] ?? [];

                    if (
                        empty($gsc_elementor_settings['submit_actions']) ||
                        !in_array('gsc_elementorentor', $gsc_elementor_settings['submit_actions'], true)
                    ) {
                        return;
                    }

                    // Manual Sheet settings
                    if (
                        !empty($gsc_elementor_settings['enable_manual_sheet_settings']) &&
                        $gsc_elementor_settings['enable_manual_sheet_settings'] === 'yes'
                    ) {
                        $gsc_elementor_spreadsheetid = $gsc_elementor_settings['manual_sheet_id'] ?? '';
                        $gsc_elementor_sheetname     = $gsc_elementor_settings['manual_tab_id'] ?? '';
                        $sheetName                   = $gsc_elementor_settings['manual_tab_name'] ?? 'Sheet1';

                    } elseif (
                        !empty($gsc_elementor_settings['gs_spreadsheet_id']) &&
                        !empty($gsc_elementor_settings['gs_spreadsheet_tab_name'])
                    ) {
                        $gsc_elementor_spreadsheetid = $gsc_elementor_settings['gs_spreadsheet_id'];
                        $gsc_elementor_sheetname     = $gsc_elementor_settings['gs_spreadsheet_tab_name'];
                        $sheet_tabId = get_option('elefgs_tabsId');
                        $sheetName = $sheet_tabId[$gsc_elementor_spreadsheetid][$gsc_elementor_sheetname] ?? 'Sheet1';

                    } else {
                        return;
                    }

                    // Build headers
                    $gsc_elementor_header_list = ['Entry ID'];
                    $formField = $gsc_elementor_settings['form_fields'] ?? [];

                    foreach ($formField as $gsc_elementor_form_fields) {
                        $label = $gsc_elementor_form_fields['field_label'] ??
                                 ucfirst($gsc_elementor_form_fields['custom_id'] ?? '');
                        $gsc_elementor_header_list[] = $label;
                    }

                    $gsc_elementor_header_list = array_values(array_unique($gsc_elementor_header_list));

                    if (!empty($gsc_elementor_header_list)) {
                        $doc = new GSC_Elementor_Free();
                        $doc->auth();
                        // $doc->add_header($gsc_elementor_spreadsheetid, $sheetName, $gsc_elementor_header_list, true);
                    }
                }
            }
        );
    }

    public function load_css_and_js_files()
    {
        add_action('admin_print_styles', array($this, 'add_css_files'));
        add_action('admin_print_scripts', array($this, 'add_js_files'));
    }

    public function load_all_classes()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- $GLOBALS['activate_the_plugin'] is internally controlled, not user input
        if ($GLOBALS['activate_the_plugin'] == true) {

            if (!class_exists('GSC_Elementor_Integration')) {
                include(GS_CONN_ELE_PATH . 'includes/class-gsc-elementor-integration.php');
            }

            if (!class_exists('gsc_elementor_sidemenu')) {
                include(GS_CONN_ELE_PATH . 'includes/gsc-elementor-sidemenu.php');
            }
             if (!class_exists('Elementor_Extensions')) {
                include(GS_CONN_ELE_PATH . 'includes/pages/extensions/gs-elementor-extension-service.php');
            }
        }
    }

    /**
     * enqueue CSS files
     * @since 1.0
     */
    public function add_css_files()
    {
        if (is_admin() && (
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe usage for enqueue logic based on admin page check
            isset($_GET['page']) && $_GET['page'] == 'gsheetconnector-elementor-config')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('gsc-elementor-css', GS_CONN_ELE_URL . 'assets/css/gsc-elementor.css', GS_CONN_ELE_VERSION, true);
			wp_enqueue_style('gsc-fontawesome-css', GS_CONN_ELE_URL . 'assets/css/fontawesome.css', GS_CONN_ELE_VERSION, true);

            wp_enqueue_style(
                'system-debug-css',
                GS_CONN_ELE_URL . 'assets/css/system-debug.css',
                [],
                GS_CONN_ELE_VERSION,
                'all'
            );
            
        }
    }

    /**
     * enqueue JS files
     * @since 1.0
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
    }

    public function gsc_elementor_widget()
    {
        // Here its safe to include our action class file.
        include_once dirname(__FILE__) . '/includes/gsc-elementor-actions.php';

        // Instantiate the action class.
        $gsc_elementor_actions = new GSC_Elementor_Actions_Free;
        // Register the action with form widget.
        \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($gsc_elementor_actions->get_name(), $gsc_elementor_actions);
    }

    /**
     * Called on activation.
     * Creates the site_options (required for all the sites in a multi-site setup)
     * If the current version doesn't match the new version, runs the upgrade
     * @since 1.0.0
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
    }

    /**
     * called on upgrade. 
     * checks the current version and applies the necessary upgrades from that version onwards
     * @since 1.0
     */
    public function run_on_upgrade()
    {
        $plugin_options = get_site_option('elefgs_info');
        if ($plugin_options['version'] == '1.0.23') {
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

    public function upgrade_helper_18()
    {
        // Fetch and save the API credentails.
        GsEl_Connector_Utility::instance()->save_api_credentials();
    }


    /**
     * Called on activation.
     * Creates the options and DB (required by per site)
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
    }

    /**
     * Add widget to the dashboard
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
     * Add custom link for the plugin beside activate/deactivate links
     * @param array $links Array of links to display below our plugin listing.
     * @return array Amended array of links.    * 
     * @since 1.5
     */
    // public function elementor_gs_connector_pro_plugin_action_links($links) {
    //     // We shouldn't encourage editing our plugin directly.
    //     unset($links['edit']);

    //     // Add our custom links to the returned array value.
    //     return array_merge($links, array(
    //         '<a href="' . admin_url('admin.php?page=gsheetconnector-elementor-config') . '" target="_blank">' . __('Settings', 'gsheetconnector-for-elementor-forms') . '</a>',
    //         // '<a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector" target="_blank">' . __('Docs', GS_CONN_ELE_TEXTDOMAIN) . '</a>',
    //         '<a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank">' . __(' <span style="color: #ff0000; font-weight: bold;">Upgrade to PRO</span>', 'gsheetconnector-for-elementor-forms') . '</a>',
    //         // '<a href="https://www.gsheetconnector.com/support" target="_blank">' . __('Support', GS_CONN_ELE_TEXTDOMAIN) . '</a>'
    //     ));
    // }

    public function elementor_gs_connector_pro_plugin_action_links($links)
    {
        // Define the text for the "Get Pro" link
        $go_pro_text = esc_html__('Get GSheetConnector Elementor Pro', 'gsheetconnector-for-elementor-forms');

        // Check if the Pro version of the plugin is installed and activated
        if (is_plugin_active('gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php')) {
            // If Pro version is active, return the links without adding the "Get Pro" link
            return $links;
        }

        // Add the action link to the plugin page with green color styling
        $links['go_pro'] = sprintf(
            '<a href="%s" target="_blank" class="gsheetconnector-pro-link" style="color: green; font-weight: bold;">%s</a>',
            esc_url('https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro'),
            $go_pro_text
        );

        return $links;
    }


    /**
     * Build System Information String
     * @global object $wpdb
     * @return string
     * @since 1.2
     */
    public function get_eleforms_system_info()
    {
        global $wpdb;

        // Get WordPress version
        $wp_version = get_bloginfo('version');

        // Get theme info
        $theme_data = wp_get_theme();
        $theme_name_version = $theme_data->get('Name') . ' ' . $theme_data->get('Version');
        $parent_theme = $theme_data->get('Template');

        if (!empty($parent_theme)) {
            $parent_theme_data = wp_get_theme($parent_theme);
            $parent_theme_name_version = $parent_theme_data->get('Name') . ' ' . $parent_theme_data->get('Version');
        } else {
            $parent_theme_name_version = 'N/A';
        }

        // Check plugin version and subscription plan
        $plugin_version = defined('GS_CONN_ELE_VERSION') ? GS_CONN_ELE_VERSION : 'N/A';
        $subscription_plan = 'FREE';

        // Check Google Account Authentication
        $api_token_auto = get_option('elefgs_token');

        if (!empty($api_token_auto)) {
            // The user is authenticated through the auto method
            $google_sheet_auto = new GSC_Elementor_Free();
            $email_account_auto = $google_sheet_auto->gsheet_print_google_account_email();
            $connected_email = !empty($email_account_auto) ? esc_html($email_account_auto) : 'Not Auth';
        } else {
            // Auto authentication is the only method available
            $connected_email = 'Not Auth';
        }

        // Check Google Permission
        $gs_verify_status = get_option('elefgs_verify');
        $search_permission = ($gs_verify_status === 'valid') ? 'Given' : 'Not Given';

        // Create the system info HTML
        $system_info = '<div class="system-statuswc">';
        $system_info .= '<h4><button id="show-info-button" class="info-button">GSheetConnector<span class="dashicons dashicons-arrow-down"></span></h4>';
        $system_info .= '<div id="info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>GSheetConnector</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Plugin Name</td><td>GSheetConnector for Elementor Forms</td></tr>';
        $system_info .= '<tr><td>Plugin Version</td><td>' . esc_html($plugin_version) . '</td></tr>';
        $system_info .= '<tr><td>Plugin Subscription Plan</td><td>' . esc_html($subscription_plan) . '</td></tr>';
        $system_info .= '<tr><td>Connected Email Account</td><td>' . $connected_email . '</td></tr>';

        $gscpclass = 'gscpermission-notgiven';
        if ($search_permission == "Given") {
            $gscpclass = 'gscpermission-given';
        }
        $system_info .= '<tr><td>Google Drive Permission</td><td class="' . $gscpclass . '">' . esc_html($search_permission) . '</td></tr>';

        $system_info .= '<tr><td>Google Sheet Permission</td><td class="' . $gscpclass . '">' . esc_html($search_permission) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';
        // Add WordPress info
        // Create a button for WordPress info
        $system_info .= '<h2><button id="show-wordpress-info-button" class="info-button">WordPress Info<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wordpress-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Info</h3>';
        $system_info .= '<table>';

        $system_info .= '<tr><td>Version</td><td>' . esc_html(get_bloginfo('version')) . '</td></tr>';
        $system_info .= '<tr><td>Site Language</td><td>' . esc_html(get_bloginfo('language')) . '</td></tr>';
        $system_info .= '<tr><td>Debug Mode</td><td>' . (WP_DEBUG ? 'Enabled' : 'Disabled') . '</td></tr>';
        $system_info .= '<tr><td>Home URL</td><td>' . esc_url(get_home_url()) . '</td></tr>';
        $system_info .= '<tr><td>Site URL</td><td>' . esc_url(get_site_url()) . '</td></tr>';
        $system_info .= '<tr><td>Permalink structure</td><td>' . esc_html(get_option('permalink_structure')) . '</td></tr>';
        $system_info .= '<tr><td>Is this site using HTTPS?</td><td>' . (is_ssl() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this a multisite?</td><td>' . (is_multisite() ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Can anyone register on this site?</td><td>' . (get_option('users_can_register') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is this site discouraging search engines?</td><td>' . (get_option('blog_public') ? 'No' : 'Yes') . '</td></tr>';
        $system_info .= '<tr><td>Default comment status</td><td>' . esc_html(get_option('default_comment_status')) . '</td></tr>';

        // Validate and sanitize $_SERVER['REMOTE_ADDR']
        $server_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $environment_type = ( $server_ip === '127.0.0.1' || $server_ip === '::1' ) ? 'localhost' : 'production';
        $system_info .= '<tr><td>Environment type</td><td>' . esc_html($environment_type) . '</td></tr>';

        // User count
        $user_count = count_users();
        $total_users = $user_count['total_users'];
        $system_info .= '<tr><td>User Count</td><td>' . esc_html($total_users) . '</td></tr>';

        // Safe fallback for blog_publicize option
        $system_info .= '<tr><td>Communication with WordPress.org</td><td>' . (get_option('blog_publicize') ? 'Yes' : 'No') . '</td></tr>';

        // Validate and sanitize $_SERVER['SERVER_SOFTWARE']
        $server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unavailable';
        $system_info .= '<tr><td>Web Server</td><td>' . esc_html($server_software) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        // info about active theme
        $active_theme = wp_get_theme();

        $system_info .= '<h2><button id="show-active-info-button" class="info-button">Active Theme<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="active-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active Theme</h3>';
        $system_info .= '<table>';
        $system_info .= '<tr><td>Name</td><td>' . $active_theme->get('Name') . '</td></tr>';
        $system_info .= '<tr><td>Version</td><td>' . $active_theme->get('Version') . '</td></tr>';
        $system_info .= '<tr><td>Author</td><td>' . $active_theme->get('Author') . '</td></tr>';
        $system_info .= '<tr><td>Author website</td><td>' . $active_theme->get('AuthorURI') . '</td></tr>';
        $system_info .= '<tr><td>Theme directory location</td><td>' . $active_theme->get_template_directory() . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // Get a list of other plugins you want to check compatibility with
        $other_plugins = array(
            'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
            // Add more plugins as needed
        );

        // Network Active Plugins
        if (is_multisite()) {
            $network_active_plugins = get_site_option('active_sitewide_plugins', array());
            if (!empty($network_active_plugins)) {
                $system_info .= '<h2><button id="show-netplug-info-button" class="info-button">Network Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
                $system_info .= '<div id="netplug-info-container" class="info-content" style="display:none;">';
                $system_info .= '<h3>Network Active plugins</h3>';
                $system_info .= '<table>';
                foreach ($network_active_plugins as $plugin => $plugin_data) {
                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                    $system_info .= '<tr><td>' . $plugin_data['Name'] . '</td><td>' . $plugin_data['Version'] . '</td></tr>';
                }
                // Add more network active plugin statuses here...
                $system_info .= '</table>';
                $system_info .= '</div>';
            }
        }
        // Active plugins
        $system_info .= '<h2><button id="show-acplug-info-button" class="info-button">Active plugins<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="acplug-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Active plugins</h3>';
        $system_info .= '<table>';

        // Retrieve all active plugins data
        $active_plugins_data = array();
        $active_plugins = get_option('active_plugins', array());
        foreach ($active_plugins as $plugin) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
            $active_plugins_data[$plugin] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'count' => 0, // Initialize the count to zero
            );
        }

        // Count the number of active installations for each plugin
        $all_plugins = get_plugins();
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            if (array_key_exists($plugin_file, $active_plugins_data)) {
                $active_plugins_data[$plugin_file]['count']++;
            }
        }

        // Sort plugins based on the number of active installations (descending order)
        uasort($active_plugins_data, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        // Display the top 5 most used plugins
        $counter = 0;
        foreach ($active_plugins_data as $plugin_data) {
            $system_info .= '<tr><td>' . $plugin_data['name'] . '</td><td>' . $plugin_data['version'] . '</td></tr>';
            // $counter++;
            // if ($counter >= 5) {
            //     break;
            // }
        }
        $system_info .= '</table>';
        $system_info .= '</div>';
        // Webserver Configuration
        // Load WP_Filesystem for file permission check
        if ( ! function_exists('WP_Filesystem') ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        // Check .htaccess writable status using WP_Filesystem
        $htaccess_path = ABSPATH . '.htaccess';
        $htaccess_writable = $wp_filesystem->is_writable( $htaccess_path ) ? 'Writable' : 'Non Writable';

        // Get current server time using gmdate() (timezone-safe)
        $current_server_time = gmdate('Y-m-d H:i:s');

        $system_info .= '<h2><button id="show-server-info-button" class="info-button">Server<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="server-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Server</h3>';
        $system_info .= '<table>';
        $system_info .= '<p>The options shown below relate to your server setup. If changes are required, you may need your web host’s assistance.</p>';

        $system_info .= '<tr><td>Server Architecture</td><td>' . esc_html(php_uname('s')) . '</td></tr>';
        $web_server = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Unavailable';
        $system_info .= '<tr><td>Web Server</td><td>' . esc_html($web_server) . '</td></tr>';
        $system_info .= '<tr><td>PHP Version</td><td>' . esc_html(phpversion()) . '</td></tr>';
        $system_info .= '<tr><td>PHP SAPI</td><td>' . esc_html(php_sapi_name()) . '</td></tr>';
        $system_info .= '<tr><td>PHP Max Input Variables</td><td>' . esc_html(ini_get('max_input_vars')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Time Limit</td><td>' . esc_html(ini_get('max_execution_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>PHP Memory Limit</td><td>' . esc_html(ini_get('memory_limit')) . '</td></tr>';
        $system_info .= '<tr><td>Max Input Time</td><td>' . esc_html(ini_get('max_input_time')) . ' seconds</td></tr>';
        $system_info .= '<tr><td>Upload Max Filesize</td><td>' . esc_html(ini_get('upload_max_filesize')) . '</td></tr>';
        $system_info .= '<tr><td>PHP Post Max Size</td><td>' . esc_html(ini_get('post_max_size')) . '</td></tr>';
        $system_info .= '<tr><td>cURL Version</td><td>' . esc_html(curl_version()['version']) . '</td></tr>';
        $system_info .= '<tr><td>Is SUHOSIN Installed?</td><td>' . (extension_loaded('suhosin') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Is the Imagick Library Available?</td><td>' . (extension_loaded('imagick') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>Are Pretty Permalinks Supported?</td><td>' . (get_option('permalink_structure') ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>.htaccess Rules</td><td>' . esc_html($htaccess_writable) . '</td></tr>';
        $system_info .= '<tr><td>Current Time</td><td>' . esc_html(current_time('mysql')) . '</td></tr>';
        $system_info .= '<tr><td>Current UTC Time</td><td>' . esc_html(current_time('mysql', true)) . '</td></tr>';
        $system_info .= '<tr><td>Current Server Time</td><td>' . esc_html($current_server_time) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';


        // Database Configuration
        $system_info .= '<h2><button id="show-database-info-button" class="info-button">Database<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="database-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Database</h3>';
        $system_info .= '<table>';

        $database_extension = 'mysqli';

        // Cached queries to avoid PHPCS warnings
        $database_server_version = wp_cache_get('gs_db_server_version', 'gsc');
        if (false === $database_server_version) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Safe, read-only server info
            $database_server_version = $wpdb->get_var("SELECT VERSION() as version");
            wp_cache_set('gs_db_server_version', $database_server_version, 'gsc', 3600);
        }

        $max_allowed_packet_size = wp_cache_get('gs_max_allowed_packet', 'gsc');
        if (false === $max_allowed_packet_size) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Safe, used for diagnostics only
            $max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
            wp_cache_set('gs_max_allowed_packet', $max_allowed_packet_size, 'gsc', 3600);
        }

        $max_connections_number = wp_cache_get('gs_max_connections', 'gsc');
        if (false === $max_connections_number) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Safe, used for diagnostics only
            $max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");
            wp_cache_set('gs_max_connections', $max_connections_number, 'gsc', 3600);
        }

        $database_client_version = $wpdb->db_version();
        $database_username = DB_USER;
        $database_host = DB_HOST;
        $database_name = DB_NAME;
        $table_prefix = $wpdb->prefix;
        $database_charset = $wpdb->charset;
        $database_collation = $wpdb->collate;

        $system_info .= '<tr><td>Extension</td><td>' . esc_html($database_extension) . '</td></tr>';
        $system_info .= '<tr><td>Server Version</td><td>' . esc_html($database_server_version) . '</td></tr>';
        $system_info .= '<tr><td>Client Version</td><td>' . esc_html($database_client_version) . '</td></tr>';
        $system_info .= '<tr><td>Database Username</td><td>' . esc_html($database_username) . '</td></tr>';
        $system_info .= '<tr><td>Database Host</td><td>' . esc_html($database_host) . '</td></tr>';
        $system_info .= '<tr><td>Database Name</td><td>' . esc_html($database_name) . '</td></tr>';
        $system_info .= '<tr><td>Table Prefix</td><td>' . esc_html($table_prefix) . '</td></tr>';
        $system_info .= '<tr><td>Database Charset</td><td>' . esc_html($database_charset) . '</td></tr>';
        $system_info .= '<tr><td>Database Collation</td><td>' . esc_html($database_collation) . '</td></tr>';
        $system_info .= '<tr><td>Max Allowed Packet Size</td><td>' . esc_html($max_allowed_packet_size) . '</td></tr>';
        $system_info .= '<tr><td>Max Connections Number</td><td>' . esc_html($max_connections_number) . '</td></tr>';
        $system_info .= '</table>';
        $system_info .= '</div>';

        // wordpress constants
        $system_info .= '<h2><button id="show-wrcons-info-button" class="info-button">WordPress Constants<span class="dashicons dashicons-arrow-down"></span></h2>';
        $system_info .= '<div id="wrcons-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>WordPress Constants</h3>';
        $system_info .= '<table>';
        // Add WordPress Constants information
        $system_info .= '<tr><td>ABSPATH</td><td>' . esc_html(ABSPATH) . '</td></tr>';
        $system_info .= '<tr><td>WP_HOME</td><td>' . esc_html(home_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_SITEURL</td><td>' . esc_html(site_url()) . '</td></tr>';
        $system_info .= '<tr><td>WP_CONTENT_DIR</td><td>' . esc_html(WP_CONTENT_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_PLUGIN_DIR</td><td>' . esc_html(WP_PLUGIN_DIR) . '</td></tr>';
        $system_info .= '<tr><td>WP_MEMORY_LIMIT</td><td>' . esc_html(WP_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_MAX_MEMORY_LIMIT</td><td>' . esc_html(WP_MAX_MEMORY_LIMIT) . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG</td><td>' . (defined('WP_DEBUG') && WP_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_DEBUG_DISPLAY</td><td>' . (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>SCRIPT_DEBUG</td><td>' . (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>WP_CACHE</td><td>' . (defined('WP_CACHE') && WP_CACHE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>CONCATENATE_SCRIPTS</td><td>' . (defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_SCRIPTS</td><td>' . (defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>COMPRESS_CSS</td><td>' . (defined('COMPRESS_CSS') && COMPRESS_CSS ? 'Yes' : 'No') . '</td></tr>';
        // Manually define the environment type (example values: 'development', 'staging', 'production')
        $environment_type = 'development';

        // Display the environment type
        $system_info .= '<tr><td>WP_ENVIRONMENT_TYPE</td><td>' . esc_html($environment_type) . '</td></tr>';

        $system_info .= '<tr><td>WP_DEVELOPMENT_MODE</td><td>' . (defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE ? 'Yes' : 'No') . '</td></tr>';
        $system_info .= '<tr><td>DB_CHARSET</td><td>' . esc_html(DB_CHARSET) . '</td></tr>';
        $system_info .= '<tr><td>DB_COLLATE</td><td>' . esc_html(DB_COLLATE) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        // Filesystem Permission
        // Load WP_Filesystem if not already available
        if ( ! function_exists('WP_Filesystem') ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        // Get directory paths
        $upload_dir = wp_upload_dir()['basedir'];
        $theme_root = get_theme_root();

        // Check writability using WP_Filesystem
        $main_dir_writable     = $wp_filesystem->is_writable( ABSPATH ) ? 'Writable' : 'Not Writable';
        $wp_content_writable   = $wp_filesystem->is_writable( WP_CONTENT_DIR ) ? 'Writable' : 'Not Writable';
        $upload_dir_writable   = $wp_filesystem->is_writable( $upload_dir ) ? 'Writable' : 'Not Writable';
        $plugin_dir_writable   = $wp_filesystem->is_writable( WP_PLUGIN_DIR ) ? 'Writable' : 'Not Writable';
        $theme_dir_writable    = $wp_filesystem->is_writable( $theme_root ) ? 'Writable' : 'Not Writable';

        $system_info .= '<h2><button id="show-ftps-info-button" class="info-button">Filesystem Permission <span class="dashicons dashicons-arrow-down"></span></button></h2>';
        $system_info .= '<div id="ftps-info-container" class="info-content" style="display:none;">';
        $system_info .= '<h3>Filesystem Permission</h3>';
        $system_info .= '<p>Shows whether WordPress is able to write to the directories it needs access to.</p>';
        $system_info .= '<table>';

        $system_info .= '<tr><td>The main WordPress directory</td><td>' . esc_html(ABSPATH) . '</td><td>' . esc_html($main_dir_writable) . '</td></tr>';
        $system_info .= '<tr><td>The wp-content directory</td><td>' . esc_html(WP_CONTENT_DIR) . '</td><td>' . esc_html($wp_content_writable) . '</td></tr>';
        $system_info .= '<tr><td>The uploads directory</td><td>' . esc_html($upload_dir) . '</td><td>' . esc_html($upload_dir_writable) . '</td></tr>';
        $system_info .= '<tr><td>The plugins directory</td><td>' . esc_html(WP_PLUGIN_DIR) . '</td><td>' . esc_html($plugin_dir_writable) . '</td></tr>';
        $system_info .= '<tr><td>The themes directory</td><td>' . esc_html($theme_root) . '</td><td>' . esc_html($theme_dir_writable) . '</td></tr>';

        $system_info .= '</table>';
        $system_info .= '</div>';

        return $system_info;
    }

    public function display_error_log()
    {
        // Define the path to your debug log file
        $debug_log_file = WP_CONTENT_DIR . '/debug.log';

        // Check if the debug log file exists
        if (file_exists($debug_log_file)) {
            // Read the contents of the debug log file
            $debug_log_contents = file_get_contents($debug_log_file);

            // Split the log content into an array of lines
            $log_lines = explode("\n", $debug_log_contents);

            // Get the last 100 lines in reversed order
            $last_100_lines = array_slice(array_reverse($log_lines), 0, 100);

            // Join the lines back together with line breaks
            $last_100_log = implode("\n", $last_100_lines);

            // Output the last 100 lines in reversed order in a textarea
            ?>
            <textarea class="errorlog" rows="20" cols="80"><?php echo esc_textarea($last_100_log); ?></textarea>
            <?php
        } else {
            echo 'Debug log file not found.';
        }
    }
}

// Initialize the google sheet connector class
$init = new GSC_Elementor_Init();

// Add custom link for our plugin
add_filter('plugin_action_links_' . GS_CONN_ELE_BASE_NAME, array($init, 'elementor_gs_connector_pro_plugin_action_links'));

