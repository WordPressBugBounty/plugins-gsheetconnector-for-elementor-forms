<?php

/**
 * Extension class for GS gravirty Google Sheet Connector Pro extensions operations
 * @since 1.0.2
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GS_Extension Class
 * @since 1.0
 */
class Elementor_Extensions
{
    /**
     *  Set things up.
     *
     *  @since 1.5
     */
    public function __construct()
    {
        // Install Fluent Forms plugin
        add_action('wp_ajax_gsele_install_plugin', array($this, 'gsele_install_plugin'));

        // Activate Fluent Forms plugin
        add_action('wp_ajax_gsele_activate_plugin', array($this, 'gsele_activate_plugin'));

        // Deactivate Fluent Forms plugin
        add_action("wp_ajax_gsele_deactivate_plugin", array($this, "gsele_deactivate_plugin"));

    }
    function gsele_deactivate_plugin()
    {
        // nonce check
        check_ajax_referer('gsele_ajax_nonce', 'security');
        if (!current_user_can('activate_plugins')) {
            GsEl_Connector_Utility::ele_gs_debug_log('Error: User lacks permission.');
            wp_send_json_error('You do not have permission to deactivate plugins.');
        }

        if (!isset($_POST['plugin_slug'])) {
            GsEl_Connector_Utility::ele_gs_debug_log('Error: Plugin slug missing.');
            wp_send_json_error('Plugin slug is missing.');
        }

        $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';

        if (empty($plugin_slug)) {
            GsEl_Connector_Utility::ele_gs_debug_log('Error: Plugin slug is empty.');
            wp_send_json_error('Invalid plugin.');
        }

        // Ensure plugin exists before attempting to deactivate
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
            GsEl_Connector_Utility::ele_gs_debug_log("Error: Plugin file does not exist - " . $plugin_slug);
            wp_send_json_error('Plugin not found.');
        }

        deactivate_plugins($plugin_slug);

        if (is_plugin_active($plugin_slug)) {
            GsEl_Connector_Utility::ele_gs_debug_log("Error: Plugin deactivation failed - " . $plugin_slug);
            wp_send_json_error('Failed to deactivate plugin.');
        }

        //error_log("Success: Plugin deactivated - " . $plugin_slug);
        wp_send_json_success('Plugin deactivated successfully.');
    }



    function gsele_install_plugin()
    {
        // nonce check
        check_ajax_referer('gsele_ajax_nonce', 'security');
        if (!isset($_POST['plugin_slug'], $_POST['download_url'])) {
            wp_send_json_error(['message' => 'Missing required parameters.']);
        }

        $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';
        $download_url = isset($_POST['download_url']) ? esc_url_raw(wp_unslash($_POST['download_url'])) : '';

        if (empty($plugin_slug) || empty($download_url)) {
            wp_send_json_error(['message' => 'Invalid plugin data.']);
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/update.php';

        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());

        // Get the list of installed plugins
        $installed_plugins = get_plugins();
        $plugin_path = '';

        // Find the correct plugin file path
        foreach ($installed_plugins as $path => $details) {
            if (strpos($path, $plugin_slug . '/') === 0) {
                $plugin_path = $path;
                break;
            }
        }

        // Check if the plugin is already installed
        if ($plugin_path) {
            // Plugin is installed, check for updates
            $update_plugins = get_site_transient('update_plugins');

            if (isset($update_plugins->response[$plugin_path])) {
                // Upgrade the plugin
                $result = $upgrader->upgrade($plugin_path);

                if (is_wp_error($result)) {
                    wp_send_json_error(['message' => 'Upgrade failed: ' . $result->get_error_message()]);
                }

                wp_send_json_success(['message' => 'Plugin upgraded successfully.']);
            } else {
                wp_send_json_error(['message' => 'No updates available for this plugin.']);
            }
        } else {
            // Plugin is NOT installed, install it
            $result = $upgrader->install($download_url);

            if (is_wp_error($result)) {
                wp_send_json_error(['message' => 'Installation failed: ' . $result->get_error_message()]);
            }

            wp_send_json_success();
        }
    }



    function gsele_activate_plugin()
    {
        // nonce check
        check_ajax_referer('gsele_ajax_nonce', 'security');
        if (!current_user_can('activate_plugins')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }

        if (!isset($_POST['plugin_slug'])) {
            wp_send_json_error(['message' => 'Missing plugin slug.']);
        }

        $plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $activated = activate_plugin($plugin_slug);

        if (is_wp_error($activated)) {
            wp_send_json_error(['message' => $activated->get_error_message()]);
        }

        wp_send_json_success();
    }
    


}
$Elementor_Extensions = new Elementor_Extensions();
