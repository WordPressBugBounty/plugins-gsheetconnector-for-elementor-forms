<?php

/**
 * Extension class for GS Elementor Forms Google Sheet Connector extensions operations
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GS_Extension Class
 * @since 1.0.0
 */

class gselef_ElementorForm_Extensions
{

    /**
     *  Set things up.
     *
     *  @since 1.0.0
     */

    public function __construct()
    {
        // Install Elementor Forms plugin
        add_action('wp_ajax_gselef_install_plugin', array($this, 'gselef_install_plugin'));

        // Activate Elementor Forms plugin
        add_action('wp_ajax_gselef_activate_plugin', array($this, 'gselef_activate_plugin'));

        // Deactivate Elementor Forms plugin
        add_action('wp_ajax_gselef_deactivate_plugin', array($this, 'gselef_deactivate_plugin'));
    }

    /**
     * Deactivate Elementor Forms plugin
     *
     * @since 1.0.0
     */

    public function gselef_deactivate_plugin()
    {
        // nonce check
        check_ajax_referer('gselef-ajax-nonce', 'security');

        if (!current_user_can('activate_plugins')) {
            wp_send_json_error('You do not have permission to deactivate plugins.');
        }

        if (!isset($_POST['plugin_slug'])) {
            wp_send_json_error('Plugin slug is missing.');
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        if (empty($plugin_slug)) {
            wp_send_json_error('Invalid plugin.');
        }

        // Ensure plugin exists before attempting to deactivate
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            wp_send_json_error('Plugin not found.');
        }

        deactivate_plugins($plugin_slug);

        if (is_plugin_active($plugin_slug)) {
            wp_send_json_error('Failed to deactivate plugin');
        }

        wp_send_json_success('Plugin deactivated successfully');
    }

    /**
     * Installs or upgrades a plugin via AJAX using provided slug and download URL.
     *
     * @access public
     * @since 1.0.0
     */

    public function gselef_install_plugin()
    {

        // 🔐 Nonce verify
        if (! check_ajax_referer('gselef-ajax-nonce', 'security', false)) {
            wp_send_json_error([
                'message' => __('Invalid security token', 'gsheetconnector-for-elementor-forms')
            ]);
        }

      // Permission check
        if (!current_user_can('install_plugins')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to install plugin', 'gsheetconnector-for-elementor-forms')
            ));
        }

        if (empty($_POST['plugin_slug']) || empty($_POST['download_url'])) {
            wp_send_json_error(array(
                'message' => __('Missing required parameters', 'gsheetconnector-for-elementor-forms')
            ));
        }

        $plugin_slug  = sanitize_text_field(wp_unslash($_POST['plugin_slug']));
        $download_url = esc_url_raw(wp_unslash($_POST['download_url']));

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/update.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());

        $result = $upgrader->install($download_url);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => __('Installation failed: ', 'gsheetconnector-for-elementor-forms') . $result->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => __('Plugin installed successfully', 'gsheetconnector-for-elementor-forms')
        ));

    }

    /**
     * Activates a plugin via AJAX using the provided plugin slug.
     *
     * @access public
     * @since 1.0.0
     */
    public function gselef_activate_plugin()
    {

        // 🔐 Verify nonce
        if (! check_ajax_referer('gselef-ajax-nonce', 'security', false)) {
            wp_send_json_error(array(
                'message' => __('Invalid security token', 'gsheetconnector-for-elementor-forms')
            ));
        }

        // 🔐 Permission check
        if (! current_user_can('activate_plugins')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to activate plugin', 'gsheetconnector-for-elementor-forms')
            ));
        }

        // 🔎 Check plugin slug
        if (empty($_POST['plugin_slug'])) {
            wp_send_json_error(array(
                'message' => __('Plugin slug is missing', 'gsheetconnector-for-elementor-forms')
            ));
        }

        $plugin_slug = sanitize_text_field(wp_unslash($_POST['plugin_slug']));

        // Load required file
        if (! function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // ✅ Check if already active
        if (is_plugin_active($plugin_slug)) {
            wp_send_json_success(array(
                'message' => __('Plugin is already activated', 'gsheetconnector-for-elementor-forms')
            ));
        }

        // 🚀 Activate plugin
        $result = activate_plugin($plugin_slug);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => __('Plugin activated successfully', 'gsheetconnector-for-elementor-forms')
        ));
    }
}
$gselef_ElementorForm_Extensions = new gselef_ElementorForm_Extensions();
