<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Needed to fetch all Elementor-designed posts
$args = array(
    'post_type' => array('post', 'page', 'elementor_library'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Needed to fetch all Elementor-designed posts
    'meta_query' => array(
        array(
            'key' => '_elementor_data',
            'compare' => 'EXISTS'
        )
    )
);

global $wpdb;

$forms_query = new WP_Query($args);
$all_elementor_forms = $forms_query->posts;

$feed = $wpdb->prefix . 'postmeta';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safely constructed from $wpdb->prefix
$feedList = $wpdb->get_results(
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is safely constructed from $wpdb->prefix
    $wpdb->prepare( "SELECT * FROM {$feed} WHERE meta_value = %s", 'gscele_form_feeds' )
);

$authenticated = get_option('elefgs_token');
$per = get_option('elefgs_verify');
$show_setting = 0;

if (!empty($authenticated) && $per == "valid") {
    $show_setting = 1;
} else {
    echo '<p class="elementor-gs-display-note">' . wp_kses_post(
        __( '<strong>Authentication Required:</strong> You must have to <a href="admin.php?page=gsheetconnector-elementor-config&tab=integration" target="_blank">Authenticate using your Google Account</a> along with Google Drive and Google Sheets Permissions in order to enable the settings for configuration.', 'gsheetconnector-for-elementor-forms' )
    ) . '</p>';
}

if ($show_setting == 1) {
    ?>
    <div class="feed-error-message" style="display:none;"></div>
    <div class="feed-success-message" style="display:none;"></div>

    <div class="elementor-main">
        <div class="elementor-row">
            <div>
                <button class="elementor-btn" id="add-new-feed">
                   <?php echo esc_html__('Add Feeds', 'gsheetconnector-for-elementor-forms'); ?>
                </button>
                <button class="elementor-close-btn" id="close-feed" style="display:none">
                    <?php echo esc_html__('Close Feeds', 'gsheetconnector-for-elementor-forms'); ?>
                </button>
            </div>

            <div class="add-feed-form">
                <form method="post">
                    <label for="feed_name"><?php echo esc_html__('Feed Name', 'gsheetconnector-for-elementor-forms'); ?></label>
                    <input type="text" id="feed_name" class="feedName" name="feed_name" />

                    <label for="elementor_form_select"><?php echo esc_html__('Select Form', 'gsheetconnector-for-elementor-forms'); ?></label>
                    <select id="elementor_form_select" name="elementorforms" class="elementorForms">
                        <option value=""><?php echo esc_html__('Select Form', 'gsheetconnector-for-elementor-forms'); ?></option>
                        <?php
                        function extract_elementor_forms($data) {
                            $forms = [];
                            foreach ($data as $element) {
                                if (isset($element['widgetType']) && $element['widgetType'] === 'form') {
                                    $forms[] = [
                                        'form_name' => $element['settings']['form_name'] ?? esc_html__('Unnamed Form', 'gsheetconnector-for-elementor-forms'),
                                        'element_id' => $element['id'] ?? esc_html__('Unknown Element ID', 'gsheetconnector-for-elementor-forms')
                                    ];
                                }
                                if (isset($element['elements']) && is_array($element['elements'])) {
                                    $forms = array_merge($forms, extract_elementor_forms($element['elements']));
                                }
                            }
                            return $forms;
                        }

                        foreach ($all_elementor_forms as $f) {
                            $form_id = $f->ID;
                            $elementor_data = get_post_meta($form_id, '_elementor_data', true);
                            $data = is_array($elementor_data) ? $elementor_data : json_decode($elementor_data, true);

                            if ($data && is_array($data)) {
                                $forms = extract_elementor_forms($data);
                                foreach ($forms as $form) {
                                    $form_name = $form['form_name'];
                                    $element_id = $form['element_id'];
                                    $form_source = ($f->post_type == 'elementor_library' && get_post_meta($f->ID, '_elementor_template_type', true) == 'popup') ? 'Popup: ' : 'Page/Post: ';
                                    echo '<option value="' . esc_attr($form_id) . '">' . esc_html($form_source . $form_name . ' (Element ID: ' . $element_id . ')') . '</option>';
                                }
                            }
                        }

                        $metforms = get_posts(array(
                            'post_type' => 'metform-form',
                            'numberposts' => -1
                        ));

                        if (!empty($metforms)) {
                            foreach ($metforms as $metform) {
                                echo '<option value="' . esc_attr($metform->ID) . '">' . esc_html__('MetForm: ', 'gsheetconnector-for-elementor-forms') . esc_html($metform->post_title) . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <input type="hidden" name="elementorform-ajax-nonce" id="elementorform-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('elementorform-ajax-nonce')); ?>" />
                    <!-- phpcs:ignore WordPress.Security.NonceVerification.Recommended -->
                    <input type="button" name="execute-submit-feed-elementor" class="elementor-gs-sub-btn" value="<?php echo esc_attr__('Submit', 'gsheetconnector-for-elementor-forms'); ?>">
                    <span class="fld-fetch-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                </form>
            </div>
        </div>

        <div class="elementor-feeds-list">
            <table border="1" id="elementorformtable">
                <?php
                $feeds_per_page = 10;
                $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $total_feeds = count($feedList);
                $total_pages = ceil($total_feeds / $feeds_per_page);
                $start_index = ($current_page - 1) * $feeds_per_page;
                $current_feeds = array_slice($feedList, $start_index, $feeds_per_page);

                if (!empty($current_feeds)) {
                    echo '<tr>
                        <th>' . esc_html__('Sr No', 'gsheetconnector-for-elementor-forms') . '</th>
                        <th>' . esc_html__('Page ID', 'gsheetconnector-for-elementor-forms') . '</th>
                        <th>' . esc_html__('Feed Name', 'gsheetconnector-for-elementor-forms') . '</th>
                        <th>' . esc_html__('Form Name', 'gsheetconnector-for-elementor-forms') . '</th>
                        <th>' . esc_html__('Page Name', 'gsheetconnector-for-elementor-forms') . '</th>
                    </tr>';
                    foreach ($current_feeds as $key => $value) {
                        $sr_no = $start_index + $key + 1;
                        $post_title = get_the_title($value->post_id);
                        $form_id = $value->post_id;
                        $elementor_data = get_post_meta($form_id, '_elementor_data', true);
                        $form_name = '';
                        if ($elementor_data) {
                            $data = json_decode($elementor_data, true);
                            if (is_array($data)) {
                                $form_result = get_form_name($data);
                                $form_name = is_array($form_result) ? implode(', ', $form_result) : $form_result;
                            }
                        }
                        $form_title = $form_name ? $form_name : 'Unnamed Form';
                        echo '<tr id="feed-' . esc_attr($value->meta_id) . '">
                            <td>' . esc_html($sr_no) . '</td>
                            <td>' . esc_html($value->post_id) . '</td>
                            <td>
                                <div class="feed-info">
                                    <div class="feed-title">' . esc_html($value->meta_key) . '</div>
                                    <div class="feed-edit-option">
                                        <a href="?page=gsheetconnector-elementor-config&tab=form_feed_settings&form_id=' . esc_attr($value->post_id) . '&feed_id=' . esc_attr($value->meta_id) . '">' . esc_html__('Edit', 'gsheetconnector-for-elementor-forms') . '</a>
                                        <a href="#" class="delete elementor-gs-btn delete-feed" data-form-id="' . esc_attr($value->post_id) . '" data-feed-id="' . esc_attr($value->meta_id) . '">' . esc_html__('Delete', 'gsheetconnector-for-elementor-forms') . '</a>
                                        <a href="' . esc_url(get_permalink($value->post_id)) . '" target="_blank">View</a>
                                    </div>
                                    <span class="loading-sign-delete-feed-elegs' . esc_attr($value->meta_id) . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </div>
                            </td>
                            <td>' . esc_html($form_title) . '</td>
                            <td>' . esc_html($post_title) . '</td>
                        </tr>';
                    }
                } else {
                    echo '<tr><td colspan="5"><h3>' . esc_html__('No feeds found', 'gsheetconnector-for-elementor-forms') . '</h3></td></tr>';
                }
                ?>
            </table>

            <div class="pagination">
                <span class="total-items">
                    <?php
                    // phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
                    echo sprintf(
                        esc_html__(
                            'Total %d items',
                            'gsheetconnector-for-elementor-forms'
                        ),
                        esc_html( $total_feeds )
                    );
                    // phpcs:enable
                    ?>
                </span>

                <?php if ($current_page > 1) { ?>
                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'gsheetconnector-elementor-config',
                        'tab' => 'form_feed_settings',
                        'paged' => 1
                    ))); ?>" class="first-page">
                        <?php echo esc_html__('« First', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'gsheetconnector-elementor-config',
                        'tab' => 'form_feed_settings',
                        'paged' => $current_page - 1
                    ))); ?>" class="prev-page">
                        <?php echo esc_html__('‹', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                <?php } ?>

                <span>
                    <?php
                    // phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
                    echo sprintf(
                        esc_html__(
                            'Current Page %1$d of %2$d',
                            'gsheetconnector-for-elementor-forms'
                        ),
                        esc_html( $current_page ),
                        esc_html( $total_pages )
                    );
                    // phpcs:enable
                    ?>
                </span>

                <?php if ($current_page < $total_pages) { ?>
                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'gsheetconnector-elementor-config',
                        'tab' => 'form_feed_settings',
                        'paged' => $current_page + 1
                    ))); ?>" class="next-page">
                        <?php echo esc_html__('›', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(array(
                        'page' => 'gsheetconnector-elementor-config',
                        'tab' => 'form_feed_settings',
                        'paged' => $total_pages
                    ))); ?>" class="last-page">
                        <?php echo esc_html__('Last ›', 'gsheetconnector-for-elementor-forms'); ?>
                    </a>
                <?php } ?>
            </div>

        </div>
    </div>
<?php }

function get_form_name($data) {
    foreach ($data as $widget) {
        if (is_array($widget) || is_object($widget)) {
            if (isset($widget['widgetType']) && $widget['widgetType'] === 'form') {
                $form_info = array();

                // Check for form name
                $form_info['form_name'] = isset($widget['settings']['form_name']) 
                    ? $widget['settings']['form_name'] 
                    : '';

                // Get element_id for the form
                $form_info['element_id'] = isset($widget['id']) 
                    ? $widget['id'] 
                    : '';

                return $form_info;
            }
            
            // If the widget has child elements, search within them
            if (isset($widget['elements']) && is_array($widget['elements'])) {
                $form_info = get_form_name($widget['elements']);
                if ($form_info) {
                    return $form_info;
                }
            }
        }
    }
    return null;
}
?>