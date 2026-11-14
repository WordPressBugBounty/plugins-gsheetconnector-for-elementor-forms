<?php

/**
 * GDPR & Privacy Policy notices for Elementor Forms.
 *
 * The admin tab ("GDPR & Privacy Policy") saves the notice settings; on the front
 * end the GDPR notice and optional "Powered by" credit are printed below every
 * Elementor form, and the Privacy Policy notice is added to the site's designated
 * Privacy Policy page.
 *
 * Supported form providers (one shared notice builder, one hook per render path):
 * - Elementor Pro "Form" widget (widget `form`)
 * - Elementor Atomic Form (element `e-form`)
 * - MetForm (widget `metform` and the `[metform]` shortcode)
 *
 * Option and AJAX names carry a "free" marker so they cannot collide with the
 * Pro add-on.
 *
 * @since 1.3.9
 */

if (!defined('ABSPATH')) {
    exit();
}

class GSC_Elementor_Privacy_Notices
{
    const OPTION = 'gselef_free_privacy_settings';

    const METFORM_POST_TYPE = 'metform-form';
    const METFORM_KEY       = 'metform';
    const METFORM_PREVIEW_NONCE = 'gselef-metform-preview';

    // Notices ('privacy' / 'gdpr') already printed during this page load.
    private static $rendered = array();

    public function __construct()
    {
        add_action('wp_ajax_gselef_free_save_privacy_setting', array($this, 'save_settings'));
        add_action('wp_ajax_gselef_free_preview_form', array($this, 'preview_form'));

        // Elementor caches rendered page HTML, so one run is needed for pages cached before this feature.
        add_action('admin_init', array($this, 'maybe_clear_element_cache'));

        add_action('wp_enqueue_scripts', array($this, 'register_frontend_style'));

        // Elementor Pro "Form" widget and the MetForm Elementor widget (both are widgets).
        add_filter('elementor/widget/render_content', array($this, 'append_frontend_notices'), 10, 2);

        // The Elementor 4 Atomic Form is an element, not a widget, so it never reaches the widget filter above.
        add_action('elementor/frontend/e-form/after_render', array($this, 'print_atomic_form_notices'));

        // MetForm placed with the [metform form_id=""] shortcode.
        add_filter('do_shortcode_tag', array($this, 'append_shortcode_notices'), 10, 2);

        // MetForm's own page (and the admin preview iframe of it).
        add_filter('the_content', array($this, 'append_metform_page_notices'), 30);
        add_filter('template_include', array($this, 'use_metform_preview_template'), 99999);

        // Fallback for the Privacy Policy page when it has no Elementor form of its own.
        add_filter('the_content', array($this, 'append_page_notice_fallback'), 20);
    }

    public function register_frontend_style()
    {
        wp_register_style(
            'gselef-frontend',
            GS_CONN_ELE_URL . 'assets/css/gselef-frontend.css',
            array(),
            GS_CONN_ELE_VERSION
        );
    }

    /**
     * Widget names whose rendered HTML gets the notices (widgets only; the
     * Atomic Form is an element and has its own hook).
     *
     * @return string[]
     */
    private static function widget_names()
    {
        return array('form', 'metform');
    }

    /**
     * Notices (privacy, GDPR, branding credit) for the form that was just rendered.
     *
     * @return string HTML, '' when nothing is enabled.
     */
    private function get_notices_html()
    {
        $settings = $this->get_notice_settings();

        $privacy = $this->build_notice_markup($settings['privacy'], 'gselef-frontend-privacy-notice', 'privacy');
        $gdpr    = $this->build_notice_markup($settings['gdpr'], 'gselef-frontend-gdpr-notice', 'gdpr');
        $brand   = $this->get_branding_html();

        $extra = $privacy . $gdpr . $brand;
        if ('' !== $extra) {
            wp_enqueue_style('gselef-frontend');
        }

        // Order: privacy notice, GDPR notice, branding credit. Builders escape their own output.
        return $extra;
    }

    /**
     * Print the notices below a rendered Elementor Form / MetForm widget.
     *
     * @param string $content Widget HTML.
     * @param object $widget  Elementor widget.
     * @return string
     */
    public function append_frontend_notices($content, $widget)
    {
        if (!is_object($widget) || !method_exists($widget, 'get_name')) {
            return $content;
        }
        if (!in_array($widget->get_name(), self::widget_names(), true)) {
            return $content;
        }

        return $content . $this->get_notices_html();
    }

    /**
     * Atomic Form: print the notices right after the closing </form> tag.
     */
    public function print_atomic_form_notices()
    {
        // The notice markup is built from escaped / kses-filtered values.
        echo $this->get_notices_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * MetForm shortcode: append the notices below the form.
     *
     * @param string $output Shortcode output.
     * @param string $tag    Shortcode tag.
     * @return string
     */
    public function append_shortcode_notices($output, $tag)
    {
        if ('metform' !== $tag || !is_string($output) || '' === trim($output)) {
            return $output;
        }
        return $output . $this->get_notices_html();
    }
    public function append_page_notice_fallback($content)
    {
        if (!is_page() || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        if (!empty(self::$rendered['privacy'])) {
            return $content;
        }

        $settings = $this->get_notice_settings();
        $notice   = $this->build_notice_markup($settings['privacy'], 'gselef-frontend-privacy-notice', 'privacy');

        if ($notice) {
            wp_enqueue_style('gselef-frontend');
            return $content . $notice;
        }
        return $content;
    }

    public function get_branding_html()
    {
        $all      = get_option(self::OPTION, array());
        $branding = wp_parse_args(
            isset($all['branding']) && is_array($all['branding']) ? $all['branding'] : array(),
            array('enabled' => 1, 'display' => 'visible', 'nofollow' => 1)
        );

        if (empty($branding['enabled']) || 'visible' !== $branding['display']) {
            return '';
        }

        $rel = !empty($branding['nofollow']) ? 'nofollow' : '';

        return '<p class="gselef-branding">'
            . esc_html__('Powered by', 'gsheetconnector-for-elementor-forms') . ' '
            . '<a href="' . esc_url('https://www.gsheetconnector.com/') . '" target="_blank" rel="' . esc_attr($rel) . '">GSheetConnector</a>'
            . '</p>';
    }

    /**
     * Return the default Privacy Policy notice text.
     *
     * @return string
     */
    public static function default_privacy_text()
    {

        $google_link = '<a href="' . esc_url('https://policies.google.com/privacy') . '" target="_blank" rel="noopener noreferrer">'
            . esc_html__('Privacy Policy', 'gsheetconnector-for-elementor-forms') . '</a>';

        $gsc_link = '<a href="' . esc_url('https://www.gsheetconnector.com/privacy-policy') . '" target="_blank" rel="noopener noreferrer">'
            . esc_html__('GSheetConnector Privacy Policy', 'gsheetconnector-for-elementor-forms') . '</a>';

        $html  = '<p><strong>' . esc_html__('Contact Form Submissions and Google Sheets Integration', 'gsheetconnector-for-elementor-forms') . '</strong></p>';
        $html .= '<p>' . esc_html__('When you submit an inquiry through our website\'s contact forms, we collect the personal information you provide, such as your name, email address, phone number, and message details, for the purpose of processing your submission, responding to your inquiry, and providing relevant support.', 'gsheetconnector-for-elementor-forms') . '</p>';

        $html .= '<p><strong>' . esc_html__('Use of Third-Party Services', 'gsheetconnector-for-elementor-forms') . '</strong></p>';
        $html .= '<p>' . esc_html__('To manage and process form submissions, we may use the following third-party services:', 'gsheetconnector-for-elementor-forms') . '</p>';
        $html .= '<ul>';
        $html .= '<li><strong>' . esc_html__('Google Sheets (Google LLC):', 'gsheetconnector-for-elementor-forms') . '</strong> '
            . esc_html__('When a form is connected to Google Sheets, the submitted information may be transmitted to and stored in the connected Google spreadsheet. This allows us to organize submissions, manage inquiries, and follow up on requests. Google\'s handling of personal information is described in its', 'gsheetconnector-for-elementor-forms')
            . ' ' . $google_link . '.</li>';
        $html .= '<li><strong>' . esc_html__('GSheetConnector:', 'gsheetconnector-for-elementor-forms') . '</strong> '
            . esc_html__('We use GSheetConnector to integrate supported website forms with Google Sheets. The integration facilitates the transfer of form submission data between the website and the connected Google services. For more information about GSheetConnector\'s data handling practices, please review the', 'gsheetconnector-for-elementor-forms')
            . ' ' . $gsc_link . '.</li>';
        $html .= '</ul>';

        $html .= '<p><strong>' . esc_html__('Data Sharing and Retention', 'gsheetconnector-for-elementor-forms') . '</strong></p>';
        $html .= '<p>' . esc_html__('We do not sell personal information collected through our contact forms. Access to submitted information is limited to authorized personnel and service providers as necessary for the purposes described in this policy.', 'gsheetconnector-for-elementor-forms') . '</p>';
        $html .= '<p>' . esc_html__('We retain personal information only for as long as reasonably necessary to fulfill the purpose for which it was collected, provide support, maintain relevant records, or comply with applicable legal obligations. When the information is no longer required, we take appropriate steps to delete it, subject to applicable retention requirements.', 'gsheetconnector-for-elementor-forms') . '</p>';

        $html .= '<p><strong>' . esc_html__('Your Privacy Rights', 'gsheetconnector-for-elementor-forms') . '</strong></p>';
        $html .= '<p>' . esc_html__('Depending on your location and applicable data protection laws, you may have the right to request access to, correction of, or deletion of your personal information, or to object to or restrict certain processing activities. To exercise applicable rights or ask questions about how your information is handled, please contact us using the contact details provided on this website.', 'gsheetconnector-for-elementor-forms') . '</p>';

        return $html;
    }

    /**
     * Return the default GDPR notice text.
     *
     * @return string
     */
    public static function default_gdpr_text()
    {
        return '<span>' . esc_html__('Your form submission will be securely sent to and stored in Google Sheets to help us process your request and respond to your inquiry.', 'gsheetconnector-for-elementor-forms') . '</span>';
    }

    private function build_notice_markup($settings, $css_class, $type)
    {
        if (empty($settings['enabled']) || 1 !== (int) $settings['enabled']) {
            return '';
        }

        // The Privacy Policy notice belongs only on the designated Privacy Policy page.
        if ('privacy' === $type) {
            if (is_admin() || !function_exists('is_privacy_policy') || !is_privacy_policy()) {
                return '';
            }
        }

        // Until the text is customised, the default notice text is shown.
        $text = (string) ($settings['text'] ?? '');
        if ('' === trim(wp_strip_all_tags($text))) {
            $text = 'privacy' === $type ? self::default_privacy_text() : self::default_gdpr_text();
        }
        if ('' === trim(wp_strip_all_tags($text))) {
            return '';
        }

        self::$rendered[$type] = true;

        return sprintf('<div class="%s">%s</div>', esc_attr($css_class), wp_kses_post($text));
    }

    /**
     * Saved notice settings merged with defaults.
     *
     * @return array
     */
    public static function get_settings()
    {
        $saved = get_option(self::OPTION, array());
        if (!is_array($saved)) {
            $saved = array();
        }
        $notice_defaults = array('enabled' => 1, 'text' => '', 'page_id' => 0);

        return array(
            'privacy'  => wp_parse_args(isset($saved['privacy']) && is_array($saved['privacy']) ? $saved['privacy'] : array(), $notice_defaults),
            'gdpr'     => wp_parse_args(isset($saved['gdpr']) && is_array($saved['gdpr']) ? $saved['gdpr'] : array(), $notice_defaults),
            'branding' => wp_parse_args(
                isset($saved['branding']) && is_array($saved['branding']) ? $saved['branding'] : array(),
                array('enabled' => 1, 'display' => 'visible', 'nofollow' => 1)
            ),
        );
    }

    private function get_notice_settings()
    {
        $all = self::get_settings();
        return array('privacy' => $all['privacy'], 'gdpr' => $all['gdpr']);
    }


    /**
     * Elementor stores each page's rendered HTML (including the notices added
     * below the form) in post meta. Clear only that HTML cache so changed
     * notice settings reach the front end straight away; CSS files are kept.
     */
    public static function clear_element_cache()
    {
        delete_post_meta_by_key('_elementor_element_cache');
    }

    public function maybe_clear_element_cache()
    {
        if (get_option('gselef_privacy_cache_cleared_v2')) {
            return;
        }
        self::clear_element_cache();
        update_option('gselef_privacy_cache_cleared_v2', 1, false);
    }
    /**
     * AJAX: save the Privacy Policy, GDPR and branding settings together.
     */
    public function save_settings()
    {
        check_ajax_referer('gselef-privacy-setting-ajax-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'gsheetconnector-for-elementor-forms')));
            return;
        }

        $branding_display = isset($_POST['gselef_branding_display'])
            ? sanitize_key(wp_unslash($_POST['gselef_branding_display']))
            : 'visible';

        $settings = array(
            'privacy'  => array(
                'enabled' => isset($_POST['gselef_privacy_enable']) && 1 === (int) $_POST['gselef_privacy_enable'] ? 1 : 0,
                'text'    => isset($_POST['gselef_privacy_text']) ? wp_kses_post(wp_unslash($_POST['gselef_privacy_text'])) : '',
                'page_id' => 0,
            ),
            'gdpr'     => array(
                'enabled' => isset($_POST['gselef_gdpr_enable']) && 1 === (int) $_POST['gselef_gdpr_enable'] ? 1 : 0,
                'text'    => isset($_POST['gselef_gdpr_text']) ? wp_kses_post(wp_unslash($_POST['gselef_gdpr_text'])) : '',
                'page_id' => 0,
            ),
            'branding' => array(
                'enabled'  => isset($_POST['gselef_branding_enable']) && 1 === (int) $_POST['gselef_branding_enable'] ? 1 : 0,
                'display'  => 'hidden' === $branding_display ? 'hidden' : 'visible',
                'nofollow' => isset($_POST['gselef_branding_nofollow']) && 1 === (int) $_POST['gselef_branding_nofollow'] ? 1 : 0,
            ),
        );

        update_option(self::OPTION, $settings);
        self::clear_element_cache();
        wp_send_json_success(array('message' => __('Privacy, GDPR & Branding settings saved successfully.', 'gsheetconnector-for-elementor-forms')));
    }

    /**
     * Collect every Elementor Form widget / atomic form saved in site content.
     *
     * @return array[] Each: value ("postid|elementid"), label.
     */
    public static function get_forms()
    {
        $post_types = get_post_types(array('public' => true));
        unset($post_types['attachment']);

        $query = new WP_Query(array(
            'post_type'      => array_values($post_types),
            'post_status'    => array('publish', 'private'),
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Needed to find Elementor-designed posts.
            'meta_query'     => array(array('key' => '_elementor_data', 'compare' => 'EXISTS')),
        ));

        $forms = array();
        foreach ($query->posts as $post) {
            $data = self::get_post_elements($post->ID);
            foreach (self::find_form_nodes($data) as $node) {
                $name = isset($node['name']) && '' !== $node['name'] ? $node['name'] : __('Unnamed Form', 'gsheetconnector-for-elementor-forms');
                $forms[] = array(
                    'value' => $post->ID . '|' . $node['id'],
                    'label' => $name . ' (' . get_the_title($post->ID) . ')',
                    'name'  => $name,
                    'page'  => get_the_title($post->ID),
                );
            }
        }
        return array_merge($forms, self::get_metforms());
    }

    /**
     * MetForm forms are their own post type (value "formid|metform"). They are
     * only listed when the MetForm plugin is active.
     *
     * @return array[]
     */
    private static function get_metforms()
    {
        if (!post_type_exists(self::METFORM_POST_TYPE)) {
            return array();
        }

        $posts = get_posts(array(
            'post_type'      => self::METFORM_POST_TYPE,
            'post_status'    => array('publish', 'private'),
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        $forms = array();
        foreach ($posts as $post) {
            $name    = '' !== trim($post->post_title) ? $post->post_title : __('Unnamed Form', 'gsheetconnector-for-elementor-forms');
            $forms[] = array(
                'value' => $post->ID . '|' . self::METFORM_KEY,
                'label' => $name . ' (MetForm)',
                'name'  => $name,
                'page'  => 'MetForm',
            );
        }
        return $forms;
    }

    private static function get_post_elements($post_id)
    {
        $raw  = get_post_meta($post_id, '_elementor_data', true);
        $data = is_array($raw) ? $raw : json_decode((string) $raw, true);
        return is_array($data) ? $data : array();
    }

    private static function find_form_nodes($elements)
    {
        $found = array();
        foreach ($elements as $el) {
            if (!is_array($el)) {
                continue;
            }
            $is_form = (isset($el['widgetType']) && 'form' === $el['widgetType']) || (isset($el['elType']) && 'e-form' === $el['elType']);
            if ($is_form && !empty($el['id'])) {
                $settings = isset($el['settings']) && is_array($el['settings']) ? $el['settings'] : array();
                $name     = isset($settings['form_name']) ? $settings['form_name'] : (isset($settings['form-name']) ? $settings['form-name'] : '');
                if (is_array($name)) {
                    $name = isset($name['value']) ? $name['value'] : '';
                }
                $el['name'] = is_string($name) ? trim($name) : '';
                $found[] = $el;
            }
            if (!empty($el['elements']) && is_array($el['elements'])) {
                $found = array_merge($found, self::find_form_nodes($el['elements']));
            }
        }
        return $found;
    }

    /**
     * Render one saved Elementor form ("postid|elementid") for the admin preview.
     * The notices are added by the render_content filter, exactly as on the front end.
     *
     * @param string $value
     * @return string HTML, or '' when it cannot be rendered.
     */
    public static function render_form_preview($value)
    {
        $parts = explode('|', (string) $value);
        if (2 !== count($parts) || !class_exists('\Elementor\Plugin')) {
            return '';
        }
        $post_id = absint($parts[0]);

        if (self::METFORM_KEY === $parts[1]) {
            return self::render_metform_preview($post_id);
        }

        $node    = null;
        foreach (self::find_form_nodes(self::get_post_elements($post_id)) as $candidate) {
            if ((string) $candidate['id'] === (string) $parts[1]) {
                $node = $candidate;
                break;
            }
        }
        if (!$node || !get_post($post_id)) {
            return '';
        }

        // The Atomic Form's styles are generated per page and loaded by the front-end pipeline, so it
        // is previewed through the real page, like MetForm.
        if (isset($node['elType']) && 'e-form' === $node['elType']) {
            return self::build_preview_frame($post_id, (string) $node['id']);
        }

        $html = self::render_element_markup($post_id, $node);
        return '' === $html ? '' : self::wrap_preview($html, $post_id);
    }

    /**
     * Render one saved Elementor element to HTML (the notices are added by the render hooks).
     *
     * @param int   $post_id Post that owns the element.
     * @param array $node    Element data.
     * @return string
     */
    public static function render_element_markup($post_id, $node)
    {
        try {
            $plugin   = \Elementor\Plugin::$instance;
            $switched = false;
            if (isset($plugin->documents) && method_exists($plugin->documents, 'switch_to_document')) {
                $plugin->documents->switch_to_document($plugin->documents->get($post_id));
                $switched = true;
            }
            $element = $plugin->elements_manager->create_element_instance($node);
            if (!$element) {
                return '';
            }
            ob_start();
            $element->print_element();
            $html = ob_get_clean();
            if ($switched && method_exists($plugin->documents, 'restore_document')) {
                $plugin->documents->restore_document();
            }
            return (string) $html;
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            return '';
        }
    }

    /**
     * Render only the form element named in the preview request (used by the preview page template).
     *
     * @param int    $post_id    Post that owns the form.
     * @param string $element_id Element ID.
     * @return string
     */
    public static function render_requested_element($post_id, $element_id)
    {
        foreach (self::find_form_nodes(self::get_post_elements($post_id)) as $candidate) {
            if ((string) $candidate['id'] === (string) $element_id) {
                return self::render_element_markup($post_id, $candidate);
            }
        }
        return '';
    }

    /**
     * MetForm draws its (classic) forms with its own React app, which needs the
     * full front-end runtime (Elementor frontend, MetForm scripts) and cannot run
     * safely inside wp-admin. The preview is therefore a same-origin iframe of the
     * form's own front-end page, shown without the theme (see print_metform_preview_page()),
     * so the form behaves and looks exactly as it does for visitors.
     *
     * @param int $form_id metform-form post ID.
     * @return string Iframe markup, or '' when the form does not exist.
     */
    private static function render_metform_preview($form_id)
    {
        $post = get_post($form_id);
        if (!$post || self::METFORM_POST_TYPE !== $post->post_type) {
            return '';
        }
        return self::build_preview_frame($form_id);
    }

    /**
     * Iframe of a post's own front-end page, shown as a bare page (see use_metform_preview_template()).
     *
     * @param int    $post_id    Post to load.
     * @param string $element_id Optional Elementor element to show on its own (Atomic Form).
     * @return string
     */
    private static function build_preview_frame($post_id, $element_id = '')
    {
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }

        $permalink = get_permalink($post);
        if (!$permalink) {
            return '';
        }

        $src = add_query_arg(
            array(
                'gselef_metform_preview' => 1,
                '_wpnonce'               => wp_create_nonce(self::METFORM_PREVIEW_NONCE),
            ),
            $permalink
        );
        if ('' !== $element_id) {
            $src = add_query_arg('gselef_element', rawurlencode($element_id), $src);
        }

        // No allow-forms / allow-top-navigation: the framed form can never submit natively or navigate the admin.
        return '<iframe class="gselef-preview-frame" title="' . esc_attr__('Form preview', 'gsheetconnector-for-elementor-forms') . '"'
            . ' src="' . esc_url($src) . '" sandbox="allow-scripts allow-same-origin"'
            . ' style="width:100%;height:320px;border:0;display:block;"></iframe>';
    }

    /**
     * Is this request the admin's MetForm preview iframe?
     *
     * @return bool
     */
    private static function is_metform_preview_request()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The nonce is verified right below.
        if (empty($_GET['gselef_metform_preview']) || !is_singular()) {
            return false;
        }
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        return current_user_can('manage_options') && wp_verify_nonce($nonce, self::METFORM_PREVIEW_NONCE);
    }

    /**
     * Front end: swap the theme template for a bare page when the admin's MetForm preview iframe is
     * loaded. Only reachable for an admin with a valid nonce; every other visit is untouched.
     *
     * @param string $template Template path chosen by WordPress.
     * @return string
     */
    public function use_metform_preview_template($template)
    {
        if (!self::is_metform_preview_request()) {
            return $template;
        }

        nocache_headers();
        // Display-only frame: notices from other plugins must not push the form around.
        // phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted
        @ini_set('display_errors', '0');
        add_action('wp_head', array($this, 'print_metform_preview_head'), 99);
        add_action('wp_footer', array($this, 'print_metform_preview_footer'), 99);
        // The admin bar would sit on top of the form.
        add_filter('show_admin_bar', '__return_false');

        return GS_CONN_ELE_PATH . 'includes/pages/gsc-elementor-metform-preview.php';
    }
    public function print_metform_preview_head()
    {
        ?>
<style>
html, body { margin: 0 !important; padding: 0 !important; background: transparent !important; }
body { padding: 2px !important; }
#wpadminbar, #coming-soon-footer-banner, .woocommerce-store-notice { display: none !important; }
/* Display-only preview (same as the other form types): no submit button and no credit. */
button, input[type="submit"], .gselef-branding { display: none !important; }
</style>
        <?php
    }

    public function print_metform_preview_footer()
    {
        ?>
<script>
(function () {
    // Display-only: never let the framed form submit.
    document.addEventListener('submit', function (e) { e.preventDefault(); e.stopPropagation(); }, true);

    // Keep the iframe as tall as the form (MetForm draws it a moment after load).
    function fit() {
        if (window.frameElement) {
            window.frameElement.style.height = Math.max(document.body.scrollHeight, 60) + 'px';
        }
    }
    window.addEventListener('load', function () {
        fit();
        if (window.ResizeObserver) { new ResizeObserver(fit).observe(document.body); }
        setTimeout(fit, 400);
        setTimeout(fit, 1200);
    });
})();
</script>
        <?php
    }

    /**
     * A MetForm's own page (the form post type is public) shows the form through MetForm's
     * the_content filter, which neither the widget nor the shortcode hook sees. Add the notices there too.
     *
     * @param string $content Post content.
     * @return string
     */
    public function append_metform_page_notices($content)
    {
        if (!is_singular(self::METFORM_POST_TYPE) || !in_the_loop() || !is_main_query() || '' === trim((string) $content)) {
            return $content;
        }
        return $content . $this->get_notices_html();
    }
    /**
     * Wrap a rendered form the way Elementor does on the front end (kit and
     * page classes) and load the same stylesheets the front end uses, so the
     * admin preview looks like the real form. The links sit only inside the
     * preview, so no other admin screen is touched.
     *
     * @param string $html    Rendered form.
     * @param int    $post_id Post that owns the form.
     * @return string
     */
    private static function wrap_preview($html, $post_id)
    {
        $urls = array();
        $kit  = 0;

        if (defined('ELEMENTOR_PATH') && defined('ELEMENTOR_ASSETS_URL')) {
            $file   = file_exists(ELEMENTOR_PATH . 'assets/css/frontend.min.css') ? 'frontend.min.css' : 'frontend.css';
            $urls[] = ELEMENTOR_ASSETS_URL . 'css/' . $file;
        }
        if (defined('ELEMENTOR_PRO_PATH') && defined('ELEMENTOR_PRO_ASSETS_URL') && file_exists(ELEMENTOR_PRO_PATH . 'assets/css/widget-form.min.css')) {
            $urls[] = ELEMENTOR_PRO_ASSETS_URL . 'css/widget-form.min.css';
        }

        try {
            $plugin = \Elementor\Plugin::$instance;
            if (isset($plugin->kits_manager)) {
                $kit = (int) $plugin->kits_manager->get_active_id();
            }
            foreach (array_unique(array_filter(array($kit, (int) $post_id))) as $css_id) {
                $css = \Elementor\Core\Files\CSS\Post::create($css_id);
                $url = $css ? $css->get_url() : '';
                if ($url) {
                    $urls[] = $url;
                }
            }
        } catch (\Throwable $e) {
            // Fall back to the base stylesheets only.
            unset($e);
        }
        $urls[] = GS_CONN_ELE_URL . 'assets/css/gselef-frontend.css?ver=' . GS_CONN_ELE_VERSION;

        $links = '';
        foreach ($urls as $url) {
            $links .= '<link rel="stylesheet" href="' . esc_url($url) . '" />';
        }

        return $links
            . '<div class="gselef-preview-canvas elementor-kit-' . (int) $kit . '">'
            . '<div class="elementor elementor-' . (int) $post_id . '">' . $html . '</div>'
            . '</div>';
    }
    /**
     * AJAX: render a live preview of an Elementor form for the admin screen.
     */
    public function preview_form()
    {
        check_ajax_referer('gselef-privacy-setting-ajax-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'gsheetconnector-for-elementor-forms')));
            return;
        }

        $value = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
        $html  = self::render_form_preview($value);
        if ('' === $html) {
            wp_send_json_error(array('message' => __('Unable to load form preview.', 'gsheetconnector-for-elementor-forms')));
            return;
        }
        wp_send_json_success(array('html' => $html));
    }
}

new GSC_Elementor_Privacy_Notices();
