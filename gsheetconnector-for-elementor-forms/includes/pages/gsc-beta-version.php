<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<!--Start Pro Setting(Roll Permissions)-->
<div class="gsc-pro-promo ml-15 mr-pro-15">

    <div class="gsc-pro-header">
        <div class="gsc-pro-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 19c-1 1-2 1-3 1 0-1 0-2 1-3l4-4"></path>
                <path d="M14 3l7 7"></path>
                <path d="M9 18l-4 4"></path>
                <path d="M15 3c2 0 6 4 6 6-2 2-6 6-8 8l-6-6c2-2 6-8 8-8z"></path>
                <circle cx="15" cy="9" r="1.5"></circle>
            </svg>

        </div>

        <div>
            <div class="unlock-header"><?php echo esc_html(__('Unlock Beta Version Control', 'gsheetconnector-for-elementor-forms')); ?></div>
            <span class="gsc-pro-badge"><?php echo esc_html(__('Test upcoming features before official release', 'gsheetconnector-for-elementor-forms')); ?></span>
        </div>
    </div>

    <!-- Feature Tabs -->
    <div class="gsc-pro-tabs pt-20 pb-20 pl-20 pr-20">
        <div>
            <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Early Access', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Get updates before public release', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Test new features in advance', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Try improvements early', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Stay ahead with new changes', 'gsheetconnector-for-elementor-forms'); ?></li>
                </ul>
            </div>
        </div>

        <div>
            <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Testing Purpose', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Features may still be under testing', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Some options may change later', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Minor issues may occur', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Used for feedback and improvements', 'gsheetconnector-for-elementor-forms'); ?></li>
                </ul>
            </div>
        </div>

        <div>
            <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Safety Notice', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Recommended for staging sites', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Avoid enabling on live websites', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Take backup before testing', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Disable anytime if needed', 'gsheetconnector-for-elementor-forms'); ?></li>
                </ul>
            </div>
        </div>

        <div>
            <div class="mb-20 fw-600 text-dark pro-roll-sub-header"><?php echo esc_html(__('Update Control', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Receive beta notifications', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Updates are not installed automatically', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('You control when to update', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Switch on/off anytime', 'gsheetconnector-for-elementor-forms'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="gsc-pro-footer text-center">
        <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro"
            target="_blank"
            class="btn btn-primary text-decoration-none link-hover-white">
            <?php echo esc_html(__('Upgrade to Unlock', 'gsheetconnector-for-elementor-forms')); ?>
        </a>
    </div>

</div>
<!--End Pro Setting(Roll Permissions)-->

<div class="wrap w-100 m-0 blur-pro-feature">
    <div class="system-general_setting  inner-wrap w-100 bg-white p-40">
        <div class="ffinfo-container">
            <form method="post">
                <div class="gsc-access-wrapper">
                    <div>
                        <div class="heading mt-0">
                            <?php echo esc_html__('Beta Program Access', 'gsheetconnector-for-elementor-forms'); ?>
                        </div>
                        <p><?php echo esc_html(__('Get early access to upcoming features and improvements before they are officially released. Beta versions may include experimental updates and should be used for testing purposes only.', 'gsheetconnector-for-elementor-forms')); ?>
                        <div class="gsc-setting-text d-flex justify-between align-center pt-15 pb-15 mt-30 bg-white">
                            <div>
                                <div class="systemifo fw-600 text-dark">
                                    <?php echo esc_html__("Enable Beta Updates", 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label for="gselef_elementorform_beta" class="fw-400">
                                    <?php echo esc_html__("Receive notifications and access to beta releases. Updates will not be installed automatically.", 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                            <div>
                                <input type="hidden" name="gselef_elementorform_beta" value="No">
                                <div class="custom-check">
                                    <input type="checkbox"
                                        class="check-toggle"
                                        id="gselef_elementorform_beta"
                                        name="gselef_elementorform_beta"
                                        value="Yes">
                                    <label for="gselef_elementorform_beta" class="button-toggle"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gsc-access-info">
                        <div class='para-heading fw-600 mb-20'><?php esc_html_e('Beta Usage Notice', 'gsheetconnector-for-elementor-forms'); ?></div>
                        <ul>
                            <li>
                                <?php echo esc_html__('Beta versions may include unfinished or experimental features', 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li>
                                <?php echo esc_html__('Some features may change or be removed in future updates', 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li>
                                <?php echo esc_html__('Minor bugs or performance issues may occur', 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li>
                                <?php echo esc_html__('We recommend enabling beta only on test or staging sites', 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                            <li>
                                <?php echo esc_html__('Beta versions are intended for testing purposes. Do not enable on live production websites without proper backups.', 'gsheetconnector-for-elementor-forms'); ?>
                            </li>
                        </ul>

                    </div>
                </div>
                <div class="text-right mt-30">
                    <input type="button" class="btn btn-primary"
                        name="gselef_elementorform_save_beta" id="gselef_elementorform_save_beta"
                        value="<?php echo esc_html__("Save Settings", 'gsheetconnector-for-elementor-forms'); ?>" />
                    <div id="gselef-beta-popup" class="gselef-beta-popup d-none">
                        <p id="gselef-beta-msg"></p>
                    </div>
                </div>
                <input type="hidden" name="gselef-elementorform-setting-ajax-nonce" id="gselef-elementorform-setting-ajax-nonce"
                    value="<?php echo esc_attr(wp_create_nonce('gselef-elementorform-setting-ajax-nonce')); ?>" />
        </div>
        </form>
    </div>
</div>