<?php
/*
 * Google Sheet configuration and settings page
 * @since 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Needed to fetch all Elementor-designed posts
$args = array(
    'post_type' => array('post', 'page', 'elementor_library', 'e-landing-page'),
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
    $wpdb->prepare("SELECT * FROM {$feed} WHERE meta_value = %s", 'gscele_form_feeds')
);
// $gselef_manual_setting = get_option('elefgs_manual_setting');
$authenticated = get_option('elefgs_token');
$per = get_option('elefgs_verify');
$show_setting = 0;
$selected_method = '';


$selected_method = esc_html(__('Existing', 'gsheetconnector-for-elementor-forms'));


if (!empty($authenticated) && $per == "valid") {

    $show_setting = 1;
} else { ?>


    <div class="gsc-setup-alert">
        <div class="gsc-alert-icon">
            <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#9a3412" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#9a3412" />
            </svg>
        </div>
        <div class="gsc-alert-content">
            <div class="feed-alert-header"><?php esc_html_e('Google Sheets Setup Required', 'gsheetconnector-for-elementor-forms'); ?></div>
            <p><?php esc_html_e('your selected Method is : ', 'gsheetconnector-for-elementor-forms'); ?><?php echo esc_html($selected_method); ?></p>
            <p><?php esc_html_e('To start sending form entries to Google Sheets, please connect your Google account first.', 'gsheetconnector-for-elementor-forms'); ?></p>
            <ul>
                <li><?php esc_html_e('✔ Click on the Sign in with Google button', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('✔ Log in using your Google account', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('✔ Select the Google account where your Sheets are stored', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('✔ Grant access to: Google Drive & Google Sheets', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('✔ Save the authentication code if prompted', 'gsheetconnector-for-elementor-forms'); ?></li>
            </ul>
            <a href="admin.php?page=gsheetconnector-elementor-config&tab=integration" class="gsc-alert-btn link-hover-white">
                <?php esc_html_e('Go to Integration Setup', 'gsheetconnector-for-elementor-forms'); ?>
            </a>
        </div>
    </div>


<?php }

if ($show_setting == 1) {
    ?>
    <div class="feed-error-message gsc-msg gsc-error fw-400 text-dark text-center pt-10 pb-10 manual-margin d-none"></div>
    <div class="feed-success-message gsc-msg gsc-success fw-400 text-dark text-center pt-10 pb-10 manual-margin d-none"></div>



    <div class="heading mt-0"><?php echo esc_html__('Sheet Sync Feeds', 'gsheetconnector-for-elementor-forms'); ?></div>
    <p><?php echo esc_html__('Create, connect, manage, and monitor how your form submissions are automatically synced to Google Sheets in real time.', 'gsheetconnector-for-elementor-forms'); ?></p>


    <div class="elementor-main">
        <div class="elementor-row">
            <div>
                <button class="elementor-btn btn btn-primary mt-20" id="add-new-feed">
                    <?php echo esc_html__('+ Add Form Feed', 'gsheetconnector-for-elementor-forms'); ?>
                </button>
                <button class="elementor-close-btn btn btn-primary mt-20 d-none" id="close-feed">
                    <?php echo esc_html__('- Close Form Feed', 'gsheetconnector-for-elementor-forms'); ?>
                </button>
            </div>

            <div class="add-feed-form shadow-box mt-50 p-30">
                <div class="heading"><?php echo esc_html__('Create Sheet Sync Feeds', 'gsheetconnector-for-elementor-forms'); ?></div>
                <p><?php echo esc_html__('Select a form and give your feed a name to start syncing form submissions with Google Sheets in real time.', 'gsheetconnector-for-elementor-forms'); ?></p>


                <form method="post">

                    <div class="row">
                        <div class="col-4">
                            <div class="mr-10">
                                <label for="feed_name"><?php echo esc_html__('Feed Name', 'gsheetconnector-for-elementor-forms'); ?></label>
                                <input type="text" id="feed_name" class="feedName form-control mt-5" name="feed_name" />
                                <div class="input-msg d-none"><?php echo esc_html('Please fill out this field', 'gsheetconnector-for-elementor-forms'); ?></div>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="mr-10">
                                <label for="elementor_form_select"><?php echo esc_html__('Select Form', 'gsheetconnector-for-elementor-forms'); ?></label>
                                <div class="auto-select mt-5 w-100">
                                    <select id="elementor_form_select" name="elementorforms" class="elementorForms gsc-select">
                                        <option value=""><?php echo esc_html__('Select Form', 'gsheetconnector-for-elementor-forms'); ?></option>
                                        <?php
                                        function extract_elementor_forms($data)
                                        {
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
                                    <div class="input-msg d-none"><?php echo esc_html('Please fill out this field', 'gsheetconnector-for-elementor-forms'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-4 mt-25">
                            <div class="mr-10">
                                <input type="hidden" name="elementorform-ajax-nonce" id="elementorform-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('elementorform-ajax-nonce')); ?>" />
                                <!-- phpcs:ignore WordPress.Security.NonceVerification.Recommended -->
                                <input type="button" name="execute-submit-feed-elementor" class="elementor-gs-sub-btn btn btn-primary" value="<?php echo esc_attr__('Submit', 'gsheetconnector-for-elementor-forms'); ?>">
                                <span class="fld-fetch-load"></span>
                            </div>
                        </div>
                    </div> <!-- row #end -->
                </form>
            </div>
        </div>

        <div class="elementor-feeds-list form-feed-table-common mt-30">
            <table id="elementorformtable" class="full-table">
                <?php
                $feeds_per_page = 10;
                $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $total_feeds = count($feedList);
                $total_pages = ceil($total_feeds / $feeds_per_page);
                $start_index = ($current_page - 1) * $feeds_per_page;
                $current_feeds = array_slice($feedList, $start_index, $feeds_per_page);

                if (!empty($current_feeds)) {

                    echo '<tr>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Sr No', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Page ID', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Feed Name', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Form Name', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Page Name', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Timestamp', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Actions', 'gsheetconnector-for-elementor-forms') . '</th>
                    <th class="pt-15 pb-15 pl-20 pr-20 text-left bg-white text-dark fw-600">' . esc_html__('Status', 'gsheetconnector-for-elementor-forms') . '</th>
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

                        /* timezone */
                        $timezone_string = get_option('timezone_string');
                        if (!empty($timezone_string)) {
                            $display_timezone = $timezone_string;
                        } else {
                            $offset = get_option('gmt_offset');
                            $hours = (int)$offset;
                            $minutes = abs(($offset - $hours) * 60);
                            $sign = ($offset < 0) ? '-' : '+';
                            $display_timezone = 'UTC' . $sign . abs($hours);
                            if ($minutes > 0) {
                                $display_timezone .= ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
                            }
                        }

                        /* status */
                        $status = get_post_meta($value->meta_id, 'gselef_status', true);

                        /* row class */
                        $row_class = ($status == 0) ? 'row-disabled' : '';

                        echo '<tr id="feed-' . esc_attr($value->meta_id) . '" class="' . esc_attr($row_class) . '">

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">' . esc_html($sr_no) . '</td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">' . esc_html($value->post_id) . '</td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">
                        <div class="feed-info">
                        <div class="feed-title">' . esc_html($value->meta_key) . '</div>
                        </div>
                        </td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">' . esc_html($form_title) . '</td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">' . esc_html($post_title) . '</td>

                        <td class="pt-15 pb-15 pl-20 pr-20 text-left">' . esc_html($display_timezone) . '</td>

                        <td>
                        <div class="feed-info">
                        <div class="feed-edit-option">

                        <a href="' . esc_url(get_permalink($value->post_id)) . '" target="_blank">
                        <i class="fa-regular fa-eye"></i></a>

                        <a href="?page=gsheetconnector-elementor-config&tab=form_feed_settings&form_id=' . esc_attr($value->post_id) . '&feed_id=' . esc_attr($value->meta_id) . '">
                        <i class="fa-regular fa-pen-to-square"></i></a>

                        <a href="#" class="delete elementor-gs-btn delete-feed" data-form-id="' . esc_attr($value->post_id) . '" data-feed-id="' . esc_attr($value->meta_id) . '">
                        <i class="fa-regular fa-trash-can"></i></a>

                        </div>

                        <span class="loading-sign-delete-feed-elegs' . esc_attr($value->meta_id) . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                        </td>

                        <td class="gselef-free_fluentform_status">
                        <div class="custom-check">

                        <input type="checkbox"
                        class="check-toggle gselef-status-toggle"
                        id="gselef-free_fluentform_status_' . esc_attr($value->meta_id) . '"
                        data-feed-id="' . esc_attr($value->meta_id) . '"
                        value="1"
                        ' . checked($status, 1, false) . '>

                        <label for="gselef-free_fluentform_status_' . esc_attr($value->meta_id) . '" class="button-toggle"></label>

                        </div>
                        </td>

                        </tr>';
                    }
                }
                ?>
            </table>
            <?php if (!empty($current_feeds)) {?>
                <div class="pagination">
                    <?php if ($total_feeds == 10) { ?>
                        <span class="total-items">
                            <?php
                        // phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
                            echo sprintf(
                                esc_html__(
                                    'Total %d items',
                                    'gsheetconnector-for-elementor-forms'
                                ),
                                esc_html($total_feeds)
                            );
                        // phpcs:enable
                            ?>
                        </span>
                    <?php } ?>

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
                    esc_html($current_page),
                    esc_html($total_pages)
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
<?php } ?>

</div>
</div>
<?php }

function get_form_name($data)
{
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