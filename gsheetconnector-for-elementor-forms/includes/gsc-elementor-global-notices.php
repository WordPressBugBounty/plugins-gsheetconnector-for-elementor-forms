<?php

/**
 * Global admin notices (Google authentication + review) for GSheetConnector Elementor Forms.
 *
 * - Auth notice: shows on every admin screen except the plugin's own page
 *   (which has its own copy) until the Google account is authenticated.
 *   Closing it hides it for 3 days.
 * - Review notice: shows once authenticated (no waiting period).
 *   "Maybe Later" / close hides it for 15 days (same window the plugin page
 *   uses); "I already did" / the review link dismisses it permanently. It
 *   shares its options with the plugin page's review notice, so dismissing it
 *   in one place dismisses it in the other.
 *
 * No other notice of this plugin is shown globally.
 *
 * @since 1.3.9
 */

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_notices', 'gselef_free_global_notices');
add_action('admin_enqueue_scripts', 'gselef_free_global_enqueue_assets');
add_action('wp_ajax_gselef_free_global_notice_action', 'gselef_free_global_notice_ajax');

/**
 * Clear the cached auth state whenever an auth-related option changes.
 */
function gselef_free_global_clear_auth_cache()
{
    delete_transient('gselef_free_global_auth_state');
}

foreach (array('elefgs_token', 'elefgs_verify', 'elefgs_manual_setting', 'elefgs_auth_failed', 'gsc_elementor_email_account') as $gselef_free_global_opt) {
    add_action('add_option_' . $gselef_free_global_opt, 'gselef_free_global_clear_auth_cache');
    add_action('update_option_' . $gselef_free_global_opt, 'gselef_free_global_clear_auth_cache');
    add_action('delete_option_' . $gselef_free_global_opt, 'gselef_free_global_clear_auth_cache');
}

/**
 * Same logic as the plugin's own page (live token, valid verification and a
 * connected email), cached so Google is not called on every admin page load.
 */
function gselef_free_global_is_authenticated()
{
    $cached = get_transient('gselef_free_global_auth_state');
    if (false !== $cached) {
        return 'yes' === $cached;
    }

    $is_authenticated = false;

    if (
        class_exists('GsEl_Connector_Utility')
        && GsEl_Connector_Utility::has_live_token()
        && 'valid' === get_option('elefgs_verify')
        && class_exists('GSC_Elementor_Free')
    ) {
        // Resolves (and stores) the connected email; empty when the token no longer works.
        $google_sheet = new GSC_Elementor_Free();
        $google_sheet->gsheet_print_google_account_email();
        $is_authenticated = !empty(get_option('gsc_elementor_email_account'));
    }

    set_transient('gselef_free_global_auth_state', $is_authenticated ? 'yes' : 'no', 10 * MINUTE_IN_SECONDS);

    return $is_authenticated;
}

function gselef_free_global_is_snoozed($key, $days)
{
    $time = get_option('elefgs_free_notice_' . $key . '_time');
    return $time && (time() - (int) $time < absint($days) * DAY_IN_SECONDS);
}

/**
 * Which notice should show on this request: 'auth', 'review' or ''.
 */
function gselef_free_global_get_active_notice()
{
    static $notice = null;
    if (null !== $notice) {
        return $notice;
    }
    $notice = '';

    if (!current_user_can('manage_options')) {
        return $notice;
    }

    // The plugin shows its own notices on its settings page.
    $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ('gsheetconnector-elementor-config' === $page) {
        return $notice;
    }

    // 1) Auth notice has priority.
    if (!gselef_free_global_is_authenticated()) {
        $notice = gselef_free_global_is_snoozed('auth', 3) ? '' : 'auth';
        return $notice;
    }

    if (get_option('elefgs_free_notice_auth_time')) {
        delete_option('elefgs_free_notice_auth_time');
    }

    // 2) Review notice, only once authenticated (no waiting period, as in Fluent Forms).
    if ('dismissed' === get_option('elefgs_free_notice_review')) {
        return $notice;
    }

    if (gselef_free_global_is_snoozed('review', 15)) {
        return $notice;
    }

    $notice = 'review';
    return $notice;
}

/**
 * Load CSS and JS only when a notice is going to be printed.
 */
function gselef_free_global_enqueue_assets($hook = '')
{
    if ('' === gselef_free_global_get_active_notice()) {
        return;
    }

    $css_path = GS_CONN_ELE_PATH . 'assets/css/gselef-free-notices.css';
    $js_path  = GS_CONN_ELE_PATH . 'assets/js/gselef-free-notices.js';

    wp_enqueue_style(
        'gselef-free-global-notices',
        GS_CONN_ELE_URL . 'assets/css/gselef-free-notices.css',
        array(),
        GS_CONN_ELE_VERSION . (file_exists($css_path) ? '.' . filemtime($css_path) : '')
    );

    // Clear the floated Screen Options / Help tabs on Dashboard, Plugins and Themes (same as Fluent Forms).
    if (in_array($hook, array('index.php', 'plugins.php', 'themes.php'), true)) {
        wp_add_inline_style(
            'gselef-free-global-notices',
            '.gselef-free .gselef-activate-banner,.gselef-free .gselef-review-banner { margin: 40px 20px 20px 20px !important; }'
        );
    }

    wp_enqueue_script(
        'gselef-free-global-notices',
        GS_CONN_ELE_URL . 'assets/js/gselef-free-notices.js',
        array(),
        GS_CONN_ELE_VERSION . (file_exists($js_path) ? '.' . filemtime($js_path) : ''),
        true
    );

    wp_localize_script(
        'gselef-free-global-notices',
        'gselefFreeGlobal',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('gselef_free_global_notice'),
        )
    );
}

/**
 * Print the active notice.
 */
function gselef_free_global_notices()
{
    $notice = gselef_free_global_get_active_notice();

    if ('auth' === $notice) {
        gselef_free_global_render_auth_notice();
    } elseif ('review' === $notice) {
        gselef_free_global_render_review_notice();
    }
}

/**
 * Auth notice markup (same message as the plugin page).
 */
function gselef_free_global_render_auth_notice()
{
    ?>
    <div class="gselef-free gselef-global-notice">
        <div class="gselef-activate-banner">
            <div class="gselef-global-logo">
                <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/pro-elemntor-gsc.webp'); ?>" alt="<?php esc_attr_e('GSheetConnector', 'gsheetconnector-for-elementor-forms'); ?>" />
            </div>
            <div class="gselef-activate-content">
                <div class="gselef-activate-content-header">
                    <?php esc_html_e('Authenticate with Your Google Account', 'gsheetconnector-for-elementor-forms'); ?>
                </div>
                <p><?php esc_html_e('Your connection has expired or hasn’t been set up yet.', 'gsheetconnector-for-elementor-forms'); ?></p>
                <p><?php echo wp_kses_post(__('Your Google connection for <strong>GSheetConnector for Elementor Forms</strong> is missing or expired. Please authenticate to continue syncing data without interruptions.', 'gsheetconnector-for-elementor-forms')); ?></p>
                <div class="gselef-activate-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gsheetconnector-elementor-config&tab=integration')); ?>" class="gselef-btn-activate">
                        <?php esc_html_e('Authenticate Now', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                    <a href="<?php echo esc_url('https://www.gsheetconnector.com/docs/elementor-google-sheet-connector'); ?>" target="_blank" rel="noopener noreferrer" class="gselef-btn-secondary">
                        <?php esc_html_e('Learn How', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                </div>
            </div>
            <button type="button" class="gselef-global-close" data-do="snooze" data-notice="auth" aria-label="<?php esc_attr_e('Dismiss', 'gsheetconnector-for-elementor-forms'); ?>">✕</button>
        </div>
    </div>
    <?php
}

/**
 * Review notice markup (same message and links as the plugin page).
 */
function gselef_free_global_render_review_notice()
{
    ?>
    <div class="gselef-free gselef-global-notice">
        <div class="gselef-review-banner" id="gselef-review-notice">
            <div class="gselef-global-logo">
                <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/pro-elemntor-gsc.webp'); ?>" alt="<?php esc_attr_e('GSheetConnector', 'gsheetconnector-for-elementor-forms'); ?>" />
            </div>
            <div class="gselef-review-content">
                <div class="gselef-review-heading">
                    <?php esc_html_e('Enjoying the Plugin?', 'gsheetconnector-for-elementor-forms'); ?>
                </div>
                <p><?php echo wp_kses_post(__('If you are enjoying <strong>GSheetConnector for Elementor Forms</strong>, please consider leaving a 5-star review. Your support helps us improve and grow.', 'gsheetconnector-for-elementor-forms')); ?></p>
                <div class="gselef-notification-actions">
                    <a href="<?php echo esc_url('https://wordpress.org/support/plugin/gsheetconnector-for-elementor-forms/reviews/'); ?>" target="_blank" rel="noopener noreferrer" class="gselef-btn-review" data-do="dismiss" data-notice="review">
                        <?php esc_html_e('Ok, you deserve it!', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                    <button type="button" class="gselef-dismiss-btn" data-do="dismiss" data-notice="review">
                        <?php esc_html_e('I already did', 'gsheetconnector-for-elementor-forms'); ?>
                    </button>
                    <a href="<?php echo esc_url('https://www.gsheetconnector.com/docs/elementor-google-sheet-connector'); ?>" target="_blank" rel="noopener noreferrer" class="gselef-btn-secondary">
                        <?php esc_html_e('I need help', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                    <button type="button" class="gselef-btn-later" data-do="snooze" data-notice="review">
                        <?php esc_html_e('Maybe Later', 'gsheetconnector-for-elementor-forms'); ?>
                    </button>
                </div>
            </div>
            <button type="button" class="gselef-global-close" data-do="snooze" data-notice="review" aria-label="<?php esc_attr_e('Dismiss', 'gsheetconnector-for-elementor-forms'); ?>">✕</button>
        </div>
    </div>
    <?php
}

/**
 * AJAX: snooze (auth 3 days / review 15 days) or permanently dismiss (review only).
 */
function gselef_free_global_notice_ajax()
{
    check_ajax_referer('gselef_free_global_notice', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error();
        return;
    }

    $notice = isset($_POST['notice']) ? sanitize_key(wp_unslash($_POST['notice'])) : '';
    $do     = isset($_POST['do']) ? sanitize_key(wp_unslash($_POST['do'])) : 'snooze';

    if (!in_array($notice, array('auth', 'review'), true)) {
        wp_send_json_error();
        return;
    }

    if ('dismiss' === $do && 'review' === $notice) {
        update_option('elefgs_free_notice_review', 'dismissed');
    } else {
        update_option('elefgs_free_notice_' . $notice . '_time', time());
    }

    wp_send_json_success();
}
