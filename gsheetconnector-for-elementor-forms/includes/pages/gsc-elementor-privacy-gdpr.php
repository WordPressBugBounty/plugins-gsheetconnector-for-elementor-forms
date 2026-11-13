<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals

$gselef_settings = GSC_Elementor_Privacy_Notices::get_settings();
$gselef_privacy  = $gselef_settings['privacy'];
$gselef_gdpr     = $gselef_settings['gdpr'];
$gselef_branding = $gselef_settings['branding'];
$gselef_forms    = GSC_Elementor_Privacy_Notices::get_forms();

// Until the text is customised, the default text is shown in the editors.
$gselef_privacy_text = '' !== trim(wp_strip_all_tags((string) $gselef_privacy['text'])) ? $gselef_privacy['text'] : GSC_Elementor_Privacy_Notices::default_privacy_text();
$gselef_gdpr_text    = '' !== trim(wp_strip_all_tags((string) $gselef_gdpr['text'])) ? $gselef_gdpr['text'] : GSC_Elementor_Privacy_Notices::default_gdpr_text();

$gselef_editor_args = array(
    'textarea_rows' => 6,
    'media_buttons' => false,
    'teeny'         => false,
    'quicktags'     => false,
    'tinymce'       => array(
        'toolbar1' => 'bold,italic,underline,link,unlink',
        'toolbar2' => '',
    ),
);
?>
<div class="wrap w-100 m-0 gs-form" id="opener">
    <div class="system-general_setting inner-wrap w-100 bg-white p-40" id="googlesheet">
        <div class="gs-form opacity-down">
            <div class="gsc-access-wrapper">
                <div>
                    <div class="heading mt-0">
                        <?php esc_html_e('GDPR & Privacy Policy', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>
                    <p><?php esc_html_e('Manage the notices displayed on your connected forms and your website’s Privacy Policy page. Customize the messages to explain how submitted information may be used and stored.', 'gsheetconnector-for-elementor-forms'); ?></p>

                    <!-- GDPR notice -->
                    <div class="gsc-setting-text justify-between align-center pt-15 pb-15 mt-30 bg-white">
                        <div class="d-flex justify-between">
                            <div>
                                <div class="systemifo fw-600 text-dark">
                                    <?php esc_html_e('Enable GDPR Notice', 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label for="gselef_gdpr_enable" class="fw-400">
                                    <?php esc_html_e('Display a notice below Elementor forms connected to Google Sheets through GSheetConnector.', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                            <div>
                                <div class="custom-check">
                                    <input type="checkbox" class="check-toggle" id="gselef_gdpr_enable" name="gselef_gdpr_enable" value="1" <?php checked((int) $gselef_gdpr['enabled'], 1); ?>>
                                    <label for="gselef_gdpr_enable" class="button-toggle"></label>
                                </div>
                            </div>
                        </div>
                        <div class="gdpr-section <?php echo empty($gselef_gdpr['enabled']) ? 'd-none' : ''; ?>">
                            <div class="mb-30 mt-30">
                                <?php
                                wp_editor(
                                    $gselef_gdpr_text,
                                    'gselefgdprtext',
                                    array_merge($gselef_editor_args, array('textarea_name' => 'gselef_gdpr_text'))
                                );
                                ?>
                                <p class="gsc-hint mt-10">
                                    <?php esc_html_e('You can customize this message using the text editor below. The notice is displayed only when this setting is enabled.', 'gsheetconnector-for-elementor-forms'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Branding credit -->
                    <div class="gsc-setting-text justify-between align-center pt-15 pb-15 mt-30 bg-white">
                        <div class="d-flex justify-between">
                            <div>
                                <div class="systemifo fw-600 text-dark">
                                    <?php esc_html_e('Enable “Powered by GSheetConnector” credit', 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label for="gselef_branding_enable" class="fw-400">
                                    <?php esc_html_e('Show a small credit link below your Elementor forms.', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                            <div>
                                <div class="custom-check">
                                    <input type="checkbox" class="check-toggle" id="gselef_branding_enable" name="gselef_branding_enable" value="1" <?php checked((int) $gselef_branding['enabled'], 1); ?>>
                                    <label for="gselef_branding_enable" class="button-toggle"></label>
                                </div>
                            </div>
                        </div>

                        <div class="gsc-branding-section <?php echo empty($gselef_branding['enabled']) ? 'd-none' : ''; ?>">
                            <div class="mb-30 mt-30 msg-inner gs-info-box-blue text-dark fw-600 mt-10 mb-10">
                                <div class="gsc-branding-preview">
                                    <?php esc_html_e('Powered by', 'gsheetconnector-for-elementor-forms'); ?>
                                    <a href="https://www.gsheetconnector.com/" target="_blank" rel="noopener noreferrer">GSheetConnector ↗</a>
                                </div>
                                <p class="gsc-hint mt-10 mb-0">
                                    <?php esc_html_e('Preview of the branding credit', 'gsheetconnector-for-elementor-forms'); ?>
                                </p>
                            </div>

                            <div class="mb-30 mt-30">
                                <div class="systemifo fw-600 text-dark mb-10">
                                    <?php esc_html_e('Display', 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label class="d-block mb-10">
                                    <input type="radio" name="gselef_branding_display" value="visible" <?php checked($gselef_branding['display'], 'visible'); ?>>
                                    <?php esc_html_e('Visible beneath the form notice', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                                <label class="d-block">
                                    <input type="radio" name="gselef_branding_display" value="hidden" <?php checked($gselef_branding['display'], 'hidden'); ?>>
                                    <?php esc_html_e('Hidden', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>

                            <div class="mb-30 mt-30">
                                <label class="d-block">
                                    <input type="checkbox" name="gselef_branding_nofollow" value="1" <?php checked((int) $gselef_branding['nofollow'], 1); ?>>
                                    <?php esc_html_e('Add rel="nofollow" to the branding link', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Policy notice -->
                    <div class="gsc-setting-text justify-between align-center pt-15 pb-15 mt-30 bg-white">
                        <div class="d-flex justify-between">
                            <div>
                                <div class="systemifo fw-600 text-dark">
                                    <?php esc_html_e('Enable Privacy Policy Notice', 'gsheetconnector-for-elementor-forms'); ?>
                                </div>
                                <label for="gselef_privacy_enable" class="fw-400">
                                    <?php esc_html_e('Choose where to display a privacy notice on your website’s designated Privacy Policy page.', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                            </div>
                            <div>
                                <div class="custom-check">
                                    <input type="checkbox" class="check-toggle" id="gselef_privacy_enable" name="gselef_privacy_enable" value="1" <?php checked((int) $gselef_privacy['enabled'], 1); ?>>
                                    <label for="gselef_privacy_enable" class="button-toggle"></label>
                                </div>
                            </div>
                        </div>
                        <div class="gdpr-privacy-section <?php echo empty($gselef_privacy['enabled']) ? 'd-none' : ''; ?>">
                            <div class="mb-30 mt-30">
                                <div class="gsc-privacy-policy-display">
                                    <div class="systemifo fw-600 text-dark mb-10">
                                        <?php esc_html_e('Display On', 'gsheetconnector-for-elementor-forms'); ?>
                                    </div>
                                    <?php
                                    $gselef_privacy_page_id  = (int) get_option('wp_page_for_privacy_policy');
                                    $gselef_privacy_page_url = $gselef_privacy_page_id ? get_permalink($gselef_privacy_page_id) : '';
                                    ?>
                                    <?php if ($gselef_privacy_page_url) : ?>
                                        <div class="d-flex justify-between">
                                            <p class="gsc-description">
                                                <?php esc_html_e('Shown on your website’s designated Privacy Policy page.', 'gsheetconnector-for-elementor-forms'); ?>
                                            </p>
                                            <a href="<?php echo esc_url($gselef_privacy_page_url); ?>" target="_blank" rel="noopener noreferrer" class="gsc-privacy-policy-link">
                                                <?php esc_html_e('View Privacy Policy Page', 'gsheetconnector-for-elementor-forms'); ?>
                                            </a>
                                        </div>
                                    <?php else : ?>
                                        <p class="gsc-description">
                                            <?php esc_html_e('No Privacy Policy page has been selected. Please configure one in Settings → Privacy.', 'gsheetconnector-for-elementor-forms'); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-30 mt-30">
                                <?php
                                wp_editor(
                                    $gselef_privacy_text,
                                    'gselefprivacytext',
                                    array_merge($gselef_editor_args, array('textarea_name' => 'gselef_privacy_text'))
                                );
                                ?>
                                <p class="gsc-hint mt-10">
                                    <?php esc_html_e('Customize this message to match your website’s data practices. The notice is displayed according to the setting selected above.', 'gsheetconnector-for-elementor-forms'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gsc-access-info">
                    <div class="para-heading fw-700 mb-20"><?php esc_html_e('GDPR Notices', 'gsheetconnector-for-elementor-forms'); ?></div>
                    <div class="gsc-preview-wrap">
                        <div class="mb-10">
                            <label class="mb-10"><?php esc_html_e('Form Preview', 'gsheetconnector-for-elementor-forms'); ?></label>
                            <select id="gselef_preview_form" class="gsc-select">
                                <?php if (!empty($gselef_forms)) : ?>
                                    <?php foreach ($gselef_forms as $gselef_form) : ?>
                                        <option value="<?php echo esc_attr($gselef_form['value']); ?>"><?php echo esc_html($gselef_form['label']); ?></option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option value=""><?php esc_html_e('No forms found', 'gsheetconnector-for-elementor-forms'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div id="gselef_form_preview">
                            <div class="gselef-loader-preview"></div>
                            <?php
                            if (!empty($gselef_forms)) {
                                // Markup is generated and escaped by Elementor; kses would strip form controls.
                                echo GSC_Elementor_Privacy_Notices::render_form_preview($gselef_forms[0]['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            } else {
                                echo '<p>' . esc_html__('No Elementor forms found.', 'gsheetconnector-for-elementor-forms') . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mt-30">
                <span class="gselef_privacy_loder"></span>
                <input type="button" class="btn btn-primary" name="gselef_privacy_save" id="gselef_privacy_save" value="<?php esc_attr_e('Save Settings', 'gsheetconnector-for-elementor-forms'); ?>" />
                <div id="gselef-privacy-popup" class="gsc-msg gsc-success d-none fw-400 text-dark text-center pt-10 pb-10 manual-margin">
                    <?php esc_html_e('Save Settings successfully.', 'gsheetconnector-for-elementor-forms'); ?>
                </div>
            </div>
            <input type="hidden" name="gselef-privacy-setting-ajax-nonce" id="gselef-privacy-setting-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('gselef-privacy-setting-ajax-nonce')); ?>" />
        </div>
    </div>
</div>
