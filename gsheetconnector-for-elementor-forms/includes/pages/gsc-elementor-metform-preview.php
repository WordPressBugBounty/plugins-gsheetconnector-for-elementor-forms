<?php
/**
 * Bare page shown inside the MetForm preview iframe on the GDPR & Privacy tab.
 *
 * Loaded through the `template_include` filter (see GSC_Elementor_Privacy_Notices::use_metform_preview_template()),
 * so the whole front-end pipeline runs: the form is rendered by MetForm's own the_content filter with
 * Elementor's and MetForm's real scripts and styles, only without the theme's header, footer and sidebar.
 *
 * @package GSheetConnector_Elementor_Forms
 */

if (!defined('ABSPATH')) {
    exit();
}

// Render the content first, like block themes do: Elementor and MetForm then know a form is on
// the page by the time wp_head() prints their configuration and queued assets.
ob_start();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- The preview nonce was verified before this template was chosen.
$gselef_element = isset($_GET['gselef_element']) ? sanitize_text_field(wp_unslash($_GET['gselef_element'])) : '';
while (have_posts()) {
    the_post();
    echo '<div class="gselef-preview-canvas">';
    if ('' !== $gselef_element) {
        // One Elementor form on its own (Atomic Form), without the rest of the page.
        // Elementor generates and enqueues the page's CSS (the Atomic Form's layout) while it renders the whole
        // document. Let it do that once, as on a normal page view, and show only the requested form.
        if (class_exists('\Elementor\Plugin')) {
            ob_start();
            \Elementor\Plugin::$instance->frontend->get_builder_content_for_display(get_the_ID(), true);
            ob_end_clean();
        }
        echo '<div class="elementor elementor-' . (int) get_the_ID() . '">' . GSC_Elementor_Privacy_Notices::render_requested_element(get_the_ID(), $gselef_element) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } else {
        the_content();
    }
    echo '</div>';
}
$gselef_page_content = ob_get_clean();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex, nofollow" />
<?php wp_head(); ?>
</head>
<body <?php body_class('gselef-metform-preview'); ?>>
<?php
echo $gselef_page_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content() output.
wp_footer();
?>
</body>
</html>
