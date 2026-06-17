<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals
?>
<!-- uninstall plugin settings -->
<div class="wrap w-100 m-0">
    <div class="system-general_setting  inner-wrap w-100 bg-white p-40">
        <div class="info-container">
            <form method="post">
                <div class="gsc-access-wrapper">
                    <div>
                        <div class="heading mt-0">
                            <?php echo esc_html__('Plugin Preferences', 'gsheetconnector-for-elementor-forms'); ?>
                        </div>
                        <p><?php echo esc_html(__('Manage how plugin settings and data are handled when the plugin is uninstalled.', 'gsheetconnector-for-elementor-forms')); ?>
                        <div class="gsc-setting-text d-flex justify-between align-center pt-15 pb-15 mt-30 bg-white">
                            <div>
                                <div class="systemifo fw-600 text-dark">
                                    <?php echo esc_html__("Delete Plugin Data on Uninstall", 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label for="gscele_elementor_uninstall_settings_free" class="fw-400">
                                    <?php echo esc_html__("Removes all plugin data (options, metadata) when the plugin is deleted.", 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                            <div>
                                <?php $gscele_uninstall_setting =  get_option('gscele_elementor_uninstall_settings_free');
                                ?>
                                <input type="hidden" name="gscele_elementor_uninstall_settings_free" value="No">
                                <div class="custom-check">
                                    <input type="checkbox" class="check-toggle"
                                        id="gscele_elementor_uninstall_settings_free"
                                        name="gscele_elementor_uninstall_settings_free" value="Yes"
                                        <?php checked('Yes', $gscele_uninstall_setting); ?>>

                                    <label for="gscele_elementor_uninstall_settings_free"
                                        class="button-toggle"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gsc-access-info">
                        <div class='para-heading fw-600 mb-20'>
                            <?php esc_html_e('Uninstall Data Notice', 'gsheetconnector-for-elementor-forms'); ?></div>

                        <ul>
                            <li><?php echo esc_html__("Enable this option only if you want a complete cleanup", 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li><?php echo esc_html__("All plugin settings and data will be permanently removed", 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li><?php echo esc_html__("Incorrect settings can delete all sensitive form and user data", 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                        </ul>

                    </div>
                </div>

                <div class="text-right mt-30">
                    <span class="loading-uninstall-free"></span>
                    <input type="submit" class="btn btn-primary uninstall-settings-save-free"
                        name="gscele_elementor_save_uninstall_settings_free"
                        value="<?php echo esc_html__("Save Settings", "gsheetconnector-for-elementor-forms"); ?>" />
                    <div id="gselef-free-uninstall-msg-free"
                        class="gsc-msg d-none fw-400 text-dark text-center pt-10 pb-10 manual-margin"></div>
                    <input type="hidden" name="gscele-elementor-setting-ajax-nonce" id="gscele-elementor-setting-ajax-nonce"
                        value="<?php echo esc_attr(wp_create_nonce('gscele-elementor-setting-ajax-nonce')); ?>" />
                </div>
            </form>
        </div>
    </div>
</div>



<div id="gselef-confirm-uninstall-data-popup-free" class="gselef-popup-overlay d-none">
    <div class="gselef-popup text-center free-to-pro-data">

        <div class="gsc-modal-title gsc-uninstall-modal-title">
            <?php echo esc_html__('Confirm Data Deletion', 'gsheetconnector-for-elementor-forms'); ?>
        </div>
        <p class="gsc-modal-text">
            <?php echo esc_html__('Enabling this option will permanently delete all plugin data, including settings and integrations, when the plugin is uninstalled.', 'gsheetconnector-for-elementor-forms'); ?>
        </p>
        <p class="gsc-modal-text">
            <?php echo esc_html__('If you plan to upgrade from Free to Pro, you may lose your existing configuration data.', 'gsheetconnector-for-elementor-forms'); ?>
        </p>

        <p class="gsc-modal-text">
            <?php echo esc_html__('Proceed only if you want a complete cleanup. This action cannot be undone.', 'gsheetconnector-for-elementor-forms'); ?>
        </p>


        <div class="popup-actions d-flex justify-center gap-10">
            <button type="button" class="btn deactivate-btn" id="gselef-cancel-uninstall-free">
                <?php echo esc_html__('Cancel', 'gsheetconnector-for-elementor-forms'); ?>
            </button>
            <button type="button" class="btn btn-primary" id="gselef-confirm-enable-uninstall-free">
                <?php echo esc_html__('Enable Deletion', 'gsheetconnector-for-elementor-forms'); ?>
            </button>
        </div>
    </div>
</div>