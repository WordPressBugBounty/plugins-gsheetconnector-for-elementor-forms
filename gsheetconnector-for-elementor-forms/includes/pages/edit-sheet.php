<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Elementor Feed Edit Page
 */

$gsc_elementor_integration = new GSC_Elementor_Integration();

// -----------------------------------------------------------------------------
// Request vars
// -----------------------------------------------------------------------------
$feed_id = isset($_GET['feed_id']) ? absint(wp_unslash($_GET['feed_id'])) : 0;
$form_id = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;
$met_form_id = isset($_GET['form_id']) ? absint(wp_unslash($_GET['form_id'])) : 0;

// -----------------------------------------------------------------------------
// Safe defaults for variables used later
// -----------------------------------------------------------------------------
$class                       = isset($class) ? $class : '';
$success_message             = isset($success_message) ? $success_message : '';
$saved_header                = isset($saved_header) && is_array($saved_header) ? $saved_header : array();
$saved_header_names          = isset($saved_header_names) && is_array($saved_header_names) ? $saved_header_names : array();
$saved_headers_metfrom       = isset($saved_headers_metfrom) && is_array($saved_headers_metfrom) ? $saved_headers_metfrom : array();
$sheet_header_names_metfrom  = isset($sheet_header_names_metfrom) && is_array($sheet_header_names_metfrom) ? $sheet_header_names_metfrom : array();
$fields_feed                 = isset($fields_feed) && is_array($fields_feed) ? $fields_feed : array();

// -----------------------------------------------------------------------------
// Get saved feed data
// -----------------------------------------------------------------------------
$feed_data = get_post_meta($feed_id, 'gscele_form_feeds', true);
// $feed_data = is_array($feed_data) ? $feed_data : array();



$sheet_name = isset($feed_data['sheet-name']) ? esc_attr($feed_data['sheet-name']) : '';
$sheet_id   = isset($feed_data['sheet-id']) ? esc_attr($feed_data['sheet-id']) : '';
$tab_name   = isset($feed_data['sheet-tab-name']) ? esc_attr($feed_data['sheet-tab-name']) : '';
$tab_id     = isset($feed_data['tab-id']) ? esc_attr($feed_data['tab-id']) : '';

// -----------------------------------------------------------------------------
// Fetch Elementor form data
// -----------------------------------------------------------------------------
$form_data = get_post_meta($form_id, '_elementor_data', true);

// -----------------------------------------------------------------------------
// Build Elementor field labels
// -----------------------------------------------------------------------------
$field_labels = array();
$field_labels['Entry ID'] = 'Entry ID';

if (!empty($form_data)) {
    $decoded_form_data = json_decode($form_data, true);

    if (!empty($decoded_form_data)) {
        $elements = $this->get_elements_form_data($decoded_form_data, 'form_fields');

        if (!empty($elements) && is_array($elements)) {

            foreach ($elements as $field) {

                if (isset($field['field_label']) && !empty($field['field_label'])) {

                    $field_label = htmlspecialchars_decode(
                        wp_strip_all_tags($field['field_label'])
                    );

                    $field_labels[$field_label] = $field_label;
                } elseif (isset($field['placeholder']) && !empty($field['placeholder'])) {

                    $field_label = htmlspecialchars_decode(
                        wp_strip_all_tags($field['placeholder'])
                    );

                    $field_labels[$field_label] = $field_label;
                } elseif (isset($field['custom_id']) && !empty($field['custom_id'])) {

                    $field_label = htmlspecialchars_decode(
                        wp_strip_all_tags($field['custom_id'])
                    );

                    $field_labels[$field_label] = $field_label;
                }
            }
        }
    }
}

// -----------------------------------------------------------------------------
// Default mail tags / special fields
// -----------------------------------------------------------------------------
$default_mail_tags = array(
    'Entry Date' => 'Entry Date',
    'Post ID'    => 'Post ID',
    'User Name'  => 'User Name',
    'User IP'    => 'User IP',
    'User Agent' => 'User Agent',
    'User ID'    => 'User ID',
    'Referrer'   => 'Referrer',
    'Form Name'  => 'Form Name',
);

// Merge standard fields + special mail tags
$merged_fields = array_merge($field_labels, $default_mail_tags);

// Keep saved header order if available
$final_array = array();
if (!empty($saved_header_names)) {
    foreach ($saved_header_names as $key => $value) {
        if (isset($merged_fields[$key])) {
            $final_array[$key] = $saved_header_names[$key];
        }
    }

    foreach ($merged_fields as $key => $value) {
        if (!isset($final_array[$key])) {
            $final_array[$key] = $merged_fields[$key];
        }
    }
}

if (!empty($final_array)) {
    $merged_fields = $final_array;
}

// -----------------------------------------------------------------------------
// MetForm support - keep original logic
// -----------------------------------------------------------------------------
if (is_plugin_active('metform/metform.php')) {
    if ($form_id === $met_form_id) {
        $met_form_post_type = 'metform-form';

        if (get_post_type($form_id) === $met_form_post_type) {
            $input_widgets_feed    = \Metform\Widgets\Manifest::instance()->get_input_widgets();
            $widget_input_data_feed = get_post_meta($met_form_id, '_elementor_data', true);
            $widget_input_data_feed = json_decode($widget_input_data_feed);

            $fieldDetails_feed = \MetForm\Core\Entries\Map_El::data($widget_input_data_feed, $input_widgets_feed)->get_el();
            $fields_feed = array();

            foreach ($fieldDetails_feed as $key => $field) {
                $widgetType_feed = $field->widgetType;
                $type_feed       = substr($widgetType_feed, 3);

                $withoutText_feed = array(
                    'radio',
                    'checkbox',
                    'select',
                    'date',
                    'time',
                    'attachment',
                    'email',
                    'poll',
                    'signature',
                    'file',
                    'file-upload',
                    'multi-select'
                );

                if ($type_feed === 'file-upload') {
                    $type_feed = 'file';
                } elseif (!in_array($type_feed, $withoutText_feed, true)) {
                    $type_feed = 'text';
                }

                $fields_feed[$key] = array(
                    'name'  => $key,
                    'type'  => $type_feed,
                    'label' => $field->mf_input_label,
                );
            }
        }
    }
}

// Reset Google Sheet Settings
if (isset($_POST['execute-reset'])) {

    // Security nonce check
    $nonce = isset($_POST['gs-ajax-nonce'])
    ? sanitize_text_field(wp_unslash($_POST['gs-ajax-nonce']))
    : '';

    if (! wp_verify_nonce($nonce, 'gs-ajax-nonce')) {
        return;
    }

    $feed_id = isset($_POST['feed_id']) ? absint($_POST['feed_id']) : 0;

    if ($feed_id) {

        // Get existing feed data
        $feed_data = get_post_meta($feed_id, 'gscele_form_feeds', true);

        if (is_array($feed_data)) {

            // Reset sheet settings
            $feed_data['sheet-name'] = '';
            $feed_data['sheet-id'] = '';
            $feed_data['sheet-tab-name'] = '';
            $feed_data['tab-id'] = '';

            // Update database
            update_post_meta($feed_id, 'gscele_form_feeds', $feed_data);
        }

        $success_message = __('Google Sheet configuration reset successfully.', 'gsheetconnector-for-elementor-forms');
    }
}

// Build MetForm header list
$gsc_elementor_all_header_list_feed = array();
if (!empty($fields_feed)) {
    foreach ($fields_feed as $ff => $fs) {
        $gsc_elementor_all_header_list_feed[$ff] = $fs['name'];
    }
}

// Final MetForm output
$metformOutput = array();
if (!empty($sheet_header_names_metfrom)) {
    foreach ($sheet_header_names_metfrom as $key => $value) {
        $metformOutput[$key] = $value;
    }

    foreach ($gsc_elementor_all_header_list_feed as $key => $value) {
        if (!isset($metformOutput[$key])) {
            $metformOutput[$key] = $value;
        }
    }
} else {
    $metformOutput = $gsc_elementor_all_header_list_feed;
}



// -----------------------------------------------------------------------------
// Sync date range for Elementor submissions
// -----------------------------------------------------------------------------
global $wpdb;

if ($form_id) {
    $from_date = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MIN(`created_at`) FROM {$wpdb->prefix}e_submissions WHERE `post_id` = %d",
            $form_id
        )
    );

    $to_date = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(`created_at`) FROM {$wpdb->prefix}e_submissions WHERE `post_id` = %d",
            $form_id
        )
    );
} else {
    $from_date = current_time('Y-m-d');
    $to_date   = current_time('Y-m-d');
}
$from_date = $from_date
? gmdate('Y-m-d', strtotime($from_date))
: current_time('Y-m-d');

$to_date = $to_date
? gmdate('Y-m-d', strtotime($to_date))
: current_time('Y-m-d');
?>


<?php
// Get Feed ID & Form ID
$feed_id = isset($_GET['feed_id']) ? absint($_GET['feed_id']) : 0;
$form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

// Get feed meta data
$feed_data = get_post_meta($feed_id, 'gscele_form_feeds', true);

$feed_name = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_value = %s",
        'gscele_form_feeds'
    )
);


// Get Elementor Form Name
$form_name = '';
if ($form_id) {
    $form_post = get_post($form_id);
    if ($form_post) {
        $form_name = $form_post->post_title;
    }
}

// Timestamp
$fetch_date = get_post_meta($feed_id, 'gscele_feed_created', true);

if (empty($fetch_date)) {
    $fetch_date = current_time('mysql');
}

// Timezone
$timezone_string = get_option('timezone_string');

if (!empty($timezone_string)) {
    $display_timezone = $timezone_string;
} else {

    $offset = get_option('gmt_offset');

    $hours   = (int) $offset;
    $minutes = abs(($offset - $hours) * 60);

    $sign = ($offset < 0) ? '-' : '+';

    $display_timezone = 'UTC' . $sign . abs($hours);

    if ($minutes > 0) {
        $display_timezone .= ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }
}
?>




<div class="gsc-elementor-main-div">

    <!-- Global messages -->
    <div class="gsc-msg gsc-success d-none fw-400 text-dark text-center pt-10 pb-10"></div>
    <div class="gsc-msg gsc-error d-none fw-400 text-dark text-center pt-10 pb-10"></div>

    <!-- Main form start -->
    <form id="edit-feed-form" method="post" action="">

        <?php wp_nonce_field('gsc_edit_feed_action', 'gsc_edit_feed_nonce'); ?>

        <?php if (isset($_POST['execute-edit-feed-elementor']) && !empty($success_message)) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Hidden IDs -->
    <input type="hidden" id="feed-id" name="feed_id" value="<?php echo esc_attr($feed_id); ?>">
    <input type="hidden" id="form-id" name="form_id" value="<?php echo esc_attr($form_id); ?>">

    <!-- ========================================================================= -->
    <!-- Breadcrumb -->
    <!-- ========================================================================= -->
    <div class="gscfff-bread-crumb">
        <a href="?page=gsheetconnector-elementor-config&tab=form_feed_settings" class="back-btn btn-spacer btn btn-primary text-decoration-none d-inline-flex align-center gap-10">
            <svg width="10" height="10" viewBox="0 0 6 8" fill="#fff" xmlns="http://www.w3.org/2000/svg" class="back-icon">
                <path d="M5.27203 0.94L4.33203 0L0.332031 4L4.33203 8L5.27203 7.06L2.2187 4L5.27203 0.94Z" fill="#fff"></path>
            </svg>
            <?php esc_html_e('Back to Feeds List', 'gsheetconnector-for-elementor-forms'); ?>
        </a>
    </div>



    <div class="feed-informtion-inner shadow-box mt-40 p-30">

        <div class="heading mt-0">
            <?php esc_html_e('Feed Details', 'gsheetconnector-for-elementor-forms'); ?>
        </div>

        <p>
            <?php esc_html_e('Displays the form and feed information used for Google Sheets synchronization.', 'gsheetconnector-for-elementor-forms'); ?>
        </p>

        <div class="gsc-info-grid mt-20">

            <!-- Form ID -->
            <div>
                <div class="feed-info-heading mt-20">
                    <?php esc_html_e('Form ID', 'gsheetconnector-for-elementor-forms'); ?>
                </div>

                <div class="feed-info-sub-head fw-700">
                    <?php echo esc_html($form_id); ?>
                </div>
            </div>

            <!-- Feed Name -->
            <div>
                <div class="feed-info-heading mt-20">
                    <?php esc_html_e('Feed Name', 'gsheetconnector-for-elementor-forms'); ?>
                </div>

                <div class="feed-info-sub-head fw-700">
                    <?php echo esc_html($feed_name); ?>
                </div>
            </div>

            <!-- Form Name -->
            <div>

                <div class="feed-info-heading mt-20">
                    <?php esc_html_e('Form Name', 'gsheetconnector-for-elementor-forms'); ?>
                </div>

                <div class="form-name-value feed-info-sub-head fw-700">
                    <?php echo esc_html($form_name); ?>
                </div>

            </div>

            <!-- Timestamp -->
            <div>

                <div class="feed-info-heading mt-20">
                    <?php esc_html_e('Timestamp', 'gsheetconnector-for-elementor-forms'); ?>
                </div>

                <div class="form-name-value feed-info-sub-head fw-700">
                    <?php
                        /*
                        echo esc_html(
                            date_i18n(
                                get_option('date_format') . ' ' . get_option('time_format'),
                                strtotime($fetch_date)
                            )
                        );
                        */
                        echo ' (' . esc_html($display_timezone) . ')';
                        ?>
                    </div>

                </div>

            </div>

        </div> <!-- feed information #end -->



        <!-- ========================================================================= -->
        <!-- Manual Google Sheets Configuration -->
        <!-- ========================================================================= -->
        <div class="shadow-box mt-40 p-30 manual-section-elementorgsc">
            <div class="heading mt-0">
                <?php esc_html_e('Manually Google Sheets Configuration', 'gsheetconnector-for-elementor-forms'); ?>
            </div>

            <p>
                <?php esc_html_e('Manually connect your Elementor Form with Google Sheets by entering the Sheet Name, Sheet ID, Tab Name, and Tab ID. This ensures that form submissions are stored directly in your chosen Google Sheet.', 'gsheetconnector-for-elementor-forms'); ?>
            </p>

            <div class="row">
                <!-- Sheet Name -->
                <div class="col-6 res-top-20">
                    <div class="form-group field-row mr-10">
                        <label for="edit-sheet-name">
                            <?php esc_html_e('Sheet Name', 'gsheetconnector-for-elementor-forms'); ?>
                            <span class="tooltip"
                            data-tooltip="<?php echo esc_attr__('Go to your Google account, open Google Sheets, and select or create the sheet you want to connect.', 'gsheetconnector-for-elementor-forms'); ?>"
                            data-tooltip-pos="right"
                            data-tooltip-length="medium">
                            <i class="fa-solid fa-circle-question help-icon"></i>
                        </span>
                    </label>

                    <input type="text"
                    class="form-control"
                    id="edit-sheet-name"
                    name="elementor-gs[sheet-name-custom]"
                    value="<?php echo esc_attr($sheet_name); ?>">
                </div>
            </div>

            <!-- Sheet ID -->
            <div class="col-6 res-top-20">
                <div class="form-group field-row mr-10">
                    <label for="edit-sheet-id">
                        <?php esc_html_e('Sheet ID', 'gsheetconnector-for-elementor-forms'); ?>
                        <span class="tooltip"
                        data-tooltip="<?php echo esc_attr__('You can get the Sheet ID from your Google Sheet URL.', 'gsheetconnector-for-elementor-forms'); ?>"
                        data-tooltip-pos="right"
                        data-tooltip-length="medium">
                        <i class="fa-solid fa-circle-question help-icon"></i>
                    </span>
                </label>

                <input type="text"
                class="form-control"
                id="edit-sheet-id"
                name="elementor-gs[sheet-id-custom]"
                value="<?php echo esc_attr($sheet_id); ?>">
            </div>
        </div>

        <!-- Tab Name -->
        <div class="col-6 mt-20">
            <div class="form-group field-row mr-10">
                <label for="edit-tab-name">
                    <?php esc_html_e('Tab Name', 'gsheetconnector-for-elementor-forms'); ?>
                    <span class="tooltip"
                    data-tooltip="<?php echo esc_attr__('Open your Google Sheet and copy the tab name shown at the bottom where you want entries stored.', 'gsheetconnector-for-elementor-forms'); ?>"
                    data-tooltip-pos="right"
                    data-tooltip-length="medium">
                    <i class="fa-solid fa-circle-question help-icon"></i>
                </span>
            </label>

            <input type="text"
            class="form-control"
            id="edit-tab-name"
            name="elementor-gs[sheet-tab-name-custom]"
            value="<?php echo esc_attr($tab_name); ?>">
        </div>
    </div>

    <!-- Tab ID -->
    <div class="col-6 mt-20">
        <div class="form-group field-row mr-10">
            <label for="edit-tab-id">
                <?php esc_html_e('Tab ID', 'gsheetconnector-for-elementor-forms'); ?>
                <span class="tooltip"
                data-tooltip="<?php echo esc_attr__('You can get the Tab ID from your Google Sheet URL after gid=.', 'gsheetconnector-for-elementor-forms'); ?>"
                data-tooltip-pos="right"
                data-tooltip-length="medium">
                <i class="fa-solid fa-circle-question help-icon"></i>
            </span>
        </label>

        <input type="text"
        class="form-control"
        id="edit-tab-id"
        name="elementor-gs[tab-id-custom]"
        value="<?php echo esc_attr($tab_id); ?>">

    </div>
</div>


<!-- Sheet URL -->
<div class="sheet-url field-row" id="sheet-url">
    <?php if (!empty($sheet_id) && !empty($tab_id)) : ?>
    <a class="sheet-url-elementor common-sheet-url btn mr-10 text-dark text-decoration-none mt-30 blinking-button"
    href="<?php echo esc_url('https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit#gid=' . $tab_id); ?>"
    target="_blank">
    <?php esc_html_e('Get Sheet URL', 'gsheetconnector-for-elementor-forms'); ?>
</a>
<?php endif; ?>

</div>


<div class="sheet-url field-row mt-30">
    <input type="hidden" name="gs-ajax-nonce" id="gs-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('gs-ajax-nonce')); ?>" />

    <input type="submit"
    name="gsele-execute-edit-feed"
    id="gsele-execute-save"
    class="btn btn-primary"

    value="<?php echo esc_attr__('Save', 'gsheetconnector-for-elementor-forms'); ?>">
    <?php if (!empty($sheet_id) && !empty($tab_id)) : ?>
    <input type="submit"
    name="execute-reset"
    id="execute-reset-free"
    class="gscfff-reset btn btn-reset ml-5"
    value="<?php esc_attr_e('Reset', 'gsheetconnector-for-elementor-forms'); ?>">
<?php endif; ?>
<div class="loading-sign-reset"></div>
</div>




</div> <!-- row #end -->

</div>

<div class="feed-informtion-inner shadow-box mt-40 p-30">
    <!----Start Pro Feed Setting Features----->

    <div class="gsc-pro-promo">

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
                <div class="unlock-header"><?php echo esc_html(__('Unlock Advanced Features with Form Feeds', 'gsheetconnector-for-elementor-forms')); ?></div>
                <span class="gsc-pro-badge"><?php echo esc_html(__('FREE users get special upgrade pricing', 'gsheetconnector-for-elementor-forms')); ?></span>
            </div>
        </div>

        <!-- Feature Tabs -->
        <div class="gsc-pro-tabs pt-20 pb-20 pl-20 pr-20">
            <div>
                <div class="mb-20"><a href="#auto-googlesheet-configuration"><?php echo esc_html(__('Automatically Google Sheet Configuration', 'gsheetconnector-for-elementor-forms')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Auto fetch Google Sheets list', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Auto detect sheet tabs', 'gsheetconnector-for-elementor-forms'); ?> </li>
                        <li><?php esc_html_e('One-click configuration', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Real-time entry sync', 'gsheetconnector-for-elementor-forms'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#field-mapping"><?php echo esc_html(__('Select Fields to Sync', 'gsheetconnector-for-elementor-forms')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Drag & drop field reordering', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Rename column headers', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Select specific fields to sync', 'gsheetconnector-for-elementor-forms'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#header-settings-sheet-sorting"><?php echo esc_html(__('Header Settings & Sheet Sorting', 'gsheetconnector-for-elementor-forms')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Freeze header row', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Custom font styling', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Header & row color control', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Sort by any column', 'gsheetconnector-for-elementor-forms'); ?></li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="mb-20"><a href="#spreadsheet-download-sync"><?php echo esc_html(__('Spreadsheet Download & Google Sheets Sync', 'gsheetconnector-for-elementor-forms')); ?></a></div>
                <div class="gsc-pro-grid">
                    <ul>
                        <li><?php esc_html_e('Download spreadsheet as file', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Bulk sync past entries', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Date range sync', 'gsheetconnector-for-elementor-forms'); ?></li>
                        <li><?php esc_html_e('Real-time entry updates', 'gsheetconnector-for-elementor-forms'); ?></li>
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
<!----End Pro Feed Setting Features----->



<!-- ========================================================================= -->
<!-- Auto Google Sheet Settings (PRO) -->
<!-- ========================================================================= -->
<div class="system-debug-logs blur-pro-feature" id="opener">
    <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" class="pro-link" target="_blank" style="text-decoration: none;"></a>

    <div class="auto-section shadow-box mt-40 p-30" style="display:block;">
        <div class="heading mt-0">
            <?php esc_html_e('Auto Google Sheet Settings', 'gsheetconnector-for-elementor-forms'); ?>
            <span class="gsc-pro-badge ml-10"><?php esc_html_e('PRO', 'gsheetconnector-for-elementor-forms'); ?></span>
        </div>

        <p>
            <?php esc_html_e('Automatically fetch spreadsheets and tabs for faster Google Sheets configuration.', 'gsheetconnector-for-elementor-forms'); ?>
        </p>

        <div class="gs-fields">
            <div class="sheet-details <?php echo esc_attr($class); ?>">
                <div class="row">
                    <div class="col-6 res-top-20">
                        <div class="form-group field-row mr-10">
                            <label for="gs-elementor-sheet-id">
                                <?php esc_html_e('Google Spreadsheet Name', 'gsheetconnector-for-elementor-forms'); ?>
                                <span class="tooltip"
                                data-tooltip="<?php echo esc_attr__('If sheets are not fetched, you can still add sheet and tab details manually.', 'gsheetconnector-for-elementor-forms'); ?>"
                                data-tooltip-pos="right"
                                data-tooltip-length="medium">
                                <i class="fa-solid fa-circle-question help-icon"></i>
                            </span>
                        </label>

                        <select name="elementor-gs[gs-elementor-sheet-id]" id="gs-elementor-sheet-id" class="auto-select-display w-100 mt-5">
                            <option value=""><?php esc_html_e('Select', 'gsheetconnector-for-elementor-forms'); ?></option>
                            <option value="create_new"><?php esc_html_e('Create New', 'gsheetconnector-for-elementor-forms'); ?></option>
                        </select>

                        <span class="error_msg" id="error_spread"></span>
                        <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    </div>
                </div>

                <div class="col-6 res-top-20">
                    <div class="form-group field-row mr-10">
                        <label for="gs-sheet-tab-name">
                            <?php esc_html_e('Google Sheet Tab Name', 'gsheetconnector-for-elementor-forms'); ?>
                            <span class="tooltip"
                            data-tooltip="<?php echo esc_attr__('If tabs are not fetched, you can still add the tab name manually.', 'gsheetconnector-for-elementor-forms'); ?>"
                            data-tooltip-pos="right"
                            data-tooltip-length="medium">
                            <i class="fa-solid fa-circle-question help-icon"></i>
                        </span>
                    </label>

                    <select name="elementor-gs[gs-sheet-tab-name]" id="gs-sheet-tab-name" class="auto-select-display w-100 mt-5">
                        <option value=""><?php esc_html_e('Select', 'gsheetconnector-for-elementor-forms'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <i class="errorSelect errorSelectsheet"></i>
        <i class="errorSelect errorSelecttabs"></i>

        <!-- Create spreadsheet UI -->
        <div class="create-ss-wrapper mt-20" style="display: none;">
            <label for="_gs_elementor_setting_create_sheet">
                <?php esc_html_e('Create Spreadsheet', 'gsheetconnector-for-elementor-forms'); ?>
            </label>
            <input type="text" name="_gs_elementor_setting_create_sheet" value="" id="_gs_elementor_setting_create_sheet" class="form-control">
            <span class="error_msg" id="error_new_spread"></span>
        </div>

        <p id="gs-validation-message"></p>
        <p id="gs-valid-message"></p>

        <?php if (!empty(get_option('elefgs_verify')) && get_option('elefgs_verify') === 'valid') : ?>
        <p class="gscelementorform-sync-row mt-20">
            <?php esc_html_e('Spreadsheet Name and URL not showing?', 'gsheetconnector-for-elementor-forms'); ?>
            <a id="gscelementorform-sync" data-init="yes" class="sync-button">
                <?php esc_html_e('Click Here', 'gsheetconnector-for-elementor-forms'); ?>
            </a>
            <?php esc_html_e('to fetch sheets.', 'gsheetconnector-for-elementor-forms'); ?>
            <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </p>
    <?php endif; ?>
</div>
</div>
</div>

<!-- ========================================================================= -->
<!-- Field Mapping / Select Fields to Sync -->
<!-- ========================================================================= -->
<div class="feed-setting-pro-wrapper">
    <div class="form-fields-list gscfff-list-set shadow-box mt-40 p-30" id="field-mapping" name="field-mapping">
        <div class="gscfff-color-code">
            <div class="color-ffgs">
                <div class="heading gsc-pro-header mt-0">
                    <?php esc_html_e('Select Fields to Sync', 'gsheetconnector-for-elementor-forms'); ?>
                </div>
                <p class="gsc-pro-desc">
                    <?php esc_html_e('Enable the fields you want to send to Google Sheets and rename columns if needed.', 'gsheetconnector-for-elementor-forms'); ?>
                    <span class="gsc-pro-badge ml-10"><?php esc_html_e('PRO', 'gsheetconnector-for-elementor-forms'); ?></span>
                </p>
            </div>

            <div class="gscfff-color-code d-flex align-center gap-20 pt-10">
                <div class="color-ffgs field-type-form">
                    <span class="field-list-pill align-center fw-700">
                        <?php esc_html_e('Field List', 'gsheetconnector-for-elementor-forms'); ?>
                    </span>
                </div>
                <div class="color-ffgs field-type-system">
                    <span class="align-center fw-700">
                        <?php esc_html_e('Special Mail Tags', 'gsheetconnector-for-elementor-forms'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Select all toggle -->
        <div class="toggle-button select-all-toggle gsc-pro-content pt-30">
            <label class="switch">
                <input type="checkbox" id="select-all-checkbox">
                <span class="slider round button-toggle"></span>
            </label>
            <span class="label-text">
                <?php esc_html_e('Select All', 'gsheetconnector-for-elementor-forms'); ?>
            </span>
        </div>

        <!-- Sortable field list -->
        <div id="sortable" class="ui-sortable">
            <?php
            if (!empty($merged_fields)) {
                foreach ($merged_fields as $key => $label) {
                    $is_selected       = '';
                    $disabled          = '';
                    $header_name_value = '';
                    $header_name       = '';
                    $cursor_not_allow  = '';

                                    // Entry ID always checked and disabled
                    if ($key === 'Entry ID') {
                        $is_selected = 'checked';
                        $disabled    = 'disabled';
                        $cursor_not_allow = 'disable-cursor';
                    } else {
                        if (!empty($saved_header) && array_key_exists($key, $saved_header) && (int) $saved_header[$key] === 1) {
                            $is_selected = 'checked';
                        }
                    }

                    if (!empty($saved_header_names) && array_key_exists($key, $saved_header_names)) {
                        $header_name_value = $saved_header_names[$key];
                    }

                    $header_name = !empty($header_name_value) ? $header_name_value : $label;

                    $non_sortable_class = ($key === 'Entry ID') ? ' non-sortable disable-submission-id ui-sortable-handle' : '';

                    $special_tags = array(
                        'Entry ID',
                        'Entry Date',
                        'Post ID',
                        'User Name',
                        'User IP',
                        'User Agent',
                        'User ID',
                        'Referrer',
                        'Form Name',
                    );

                    echo '<div class="card form-field-toggle' . esc_attr($non_sortable_class) . '">';
                    echo '<div class="card-content">';

                    if (in_array($key, $special_tags, true)) {
                        echo '<div class="toggle-button special_mail_tags_bg">';
                    } else {
                        echo '<div class="toggle-button field_list_bg">';
                    }

                    echo '<label class="switch">';
                    echo '<input type="checkbox" name="sheet_header[' . esc_attr($key) . ']" class="toggle-input" value="1" ' . esc_attr($is_selected) . ' ' . esc_attr($disabled) . '>';
                    echo '<span class="slider round button-toggle"></span>';
                    echo '</label>';

                    echo '<span class="label-text card-label">' . esc_html($key) . '</span>';

                    echo '<input type="text"
                    class="field-input ' . esc_attr($cursor_not_allow) . '"
                    name="sheet_header_names[' . esc_attr($key) . ']"
                    value="' . esc_attr($header_name) . '"
                    placeholder="' . esc_attr($label) . '">';

                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            ?>
        </div>

        <!-- MetForm fields -->
        <?php
        $metform_active = class_exists('Metform\Widgets\Manifest') && method_exists('Metform\Widgets\Manifest', 'instance');
        if ($metform_active && !empty($metformOutput)) :
            ?>
            <ul class="metform-header-feed mt-30">
                <?php foreach ($metformOutput as $mfo => $gl) :
                    $field_list_checked_feed = '';
                    $saved_val = isset($sheet_header_names_metfrom[$gl]) ? $sheet_header_names_metfrom[$gl] : $gl;
                    $field_list_checked_feed = isset($saved_headers_metfrom[$mfo]) && (int) $saved_headers_metfrom[$mfo] === 1 ? 'checked' : '';
                    ?>
                    <li class="li-metform-header-feed field-item">
                        <div class="toggle-buttom-pos-feed">
                            <label
                            class="button-metform-toggle1-feed <?php echo ($field_list_checked_feed === '' ? 'button-tog-inactive-feed' : 'button-tog-active-feed'); ?> sheet_headers_metfrom-lbl-feed"
                            id="button-metform-toggle1-click-feed"
                            data-id="<?php echo esc_attr($gl); ?>-"></label>
                        </div>

                        <div class="switch-label-metform-feed">
                            <label>
                                <span class="label">
                                    <span class="label_text-metform-feed"><?php echo esc_html($mfo); ?></span>
                                </span>
                            </label>
                        </div>

                        <input type="radio"
                        id="<?php echo esc_attr($gl); ?>-one"
                        name="sheet_headers_metfrom[<?php echo esc_attr($mfo); ?>]"
                        value="1"
                        <?php echo ($field_list_checked_feed !== '' ? 'checked' : ''); ?>
                        class="header_name_1-metform sheet_headers-one radio-btn-hide">

                        <input type="radio"
                        id="<?php echo esc_attr($gl); ?>-two"
                        name="sheet_headers_metfrom[<?php echo esc_attr($mfo); ?>]"
                        value="0"
                        <?php echo ($field_list_checked_feed === '' ? 'checked' : ''); ?>
                        class="header_name_0-metform sheet_headers_metfrom-two radio-btn-hide">

                        <div>
                            <input type="text"
                            name="sheet_header_names_metfrom[<?php echo esc_attr($mfo); ?>]"
                            value="<?php echo esc_attr($saved_val); ?>"
                            placeholder="<?php echo esc_attr($gl); ?>">
                            <span class="edit_col_name-metform-feed"></span>
                            <span class="update_col_name-metform-feed" style="display:none">
                                <i class="fa fa-check"></i>
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- ========================================================================= -->
    <!-- Header Management / Styling / Sync -->
    <!-- ========================================================================= -->
    <div class="freez_order_sort form-fields-list gscfff-list-set shadow-box mt-40 p-30">
        <div id="header-settings-sheet-sorting" name="header-settings-sheet-sorting">
            <div class="heading mt-0">
                <?php esc_html_e('Header Management', 'gsheetconnector-for-elementor-forms'); ?>
            </div>

            <p>
                <?php esc_html_e('Manage header row behavior, styling, sheet background colors, and submission sync options.', 'gsheetconnector-for-elementor-forms'); ?>
            </p>

            <div class="header-styling-sheet">
                <!-- Freeze Header -->
                <div class="settings-card mb-20 w-100 bg-white">
                    <div class="mt-0 header-settings-ineer-size fw-600 mb-20">
                        <?php esc_html_e('Header Behavior', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>

                    <div class="gscfrmnt-cards gscfrmnt-card setting-row">
                        <div class="toggle-button freeze-header-toggle d-flex align-items-center justify-between mb-15">
                            <span class="label-text fw-400">
                                <?php esc_html_e('Freeze Header Row', 'gsheetconnector-for-elementor-forms'); ?>
                            </span>

                            <label class="switch" for="freeze-header-option">
                                <input type="checkbox"
                                name="elementor-gs[freeze_header]"
                                id="freeze-header-option"
                                class="check-toggle-elemgsc"
                                value="">
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Header Font Settings -->
                    <div class="sheet_formatting">
                        <div class="elemgsc-sheet_formatting elemgsc-sheet_formatting">
                            <div class="toggle-button sheet_formatting-header-toggle d-flex align-items-center justify-between mb-15">
                                <span class="label-text fw-400">
                                    <?php esc_html_e('Header - Font Settings', 'gsheetconnector-for-elementor-forms'); ?>
                                </span>

                                <label class="switch" for="sheet_formatting-header-checkbox">
                                    <input type="checkbox"
                                    id="sheet_formatting-header-checkbox"
                                    name="elementor-gs[sheet_formatting_header]"
                                    value="1">
                                    <span class="slider round button-toggle"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Sheet Background -->
                    <div class="back-formating">
                        <div class="toggle-button sheet-bg-toggle d-flex align-items-center justify-between">
                            <span class="label-text fw-400">
                                <?php esc_html_e('Sheet Background Color', 'gsheetconnector-for-elementor-forms'); ?>
                            </span>

                            <label class="switch" for="sheet-bg-toggle-checkbox">
                                <input type="checkbox"
                                id="sheet-bg-toggle-checkbox"
                                name="elementor-gs[sheet_bg]"
                                value="1">
                                <span class="slider round button-toggle"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Row Font Settings -->
                    <div class="sheet_formatting mt-15">
                        <div class="elemgsc-sheet_formatting_row elemgsc-sheet_formatting_row">
                            <div class="toggle-button sheet_formatting-row-toggle d-flex align-items-center justify-between">
                                <span class="label-text fw-400">
                                    <?php esc_html_e('Row - Font Settings', 'gsheetconnector-for-elementor-forms'); ?>
                                </span>

                                <label class="switch" for="sheet_formatting-row-checkbox">
                                    <input type="checkbox"
                                    id="sheet_formatting-row-checkbox"
                                    name="elementor-gs[sheet_formatting_row]"
                                    value="1">
                                    <span class="slider round button-toggle"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sync Entries -->
                <div class="settings-card mb-20 w-100 bg-white">
                    <div class="heading mt-0">
                        <?php esc_html_e('Sync form submissions with Google Sheets', 'gsheetconnector-for-elementor-forms'); ?>
                        <span class="gsc-pro-badge ml-10"><?php esc_html_e('PRO', 'gsheetconnector-for-elementor-forms'); ?></span>
                    </div>

                    <p>
                        <?php esc_html_e('Select a date range to sync Elementor form entries to Google Sheets.', 'gsheetconnector-for-elementor-forms'); ?>
                    </p>

                    <span class="sync-elemgsc-msg"></span>

                    <div class="sync-date">
                        <div class="d-flex gap-10">
                            <div class="form-group w-100">
                                <label for="sync-from-date">
                                    <?php esc_html_e('From Date', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                                <input type="date"
                                id="sync-from-date"
                                name="sync_from_date"
                                value="<?php echo esc_attr($from_date); ?>"
                                min="<?php echo esc_attr($from_date); ?>"
                                max="<?php echo esc_attr($to_date); ?>"
                                class="wpgs-date-picker">
                            </div>

                            <div class="form-group w-100">
                                <label for="sync-to-date">
                                    <?php esc_html_e('To Date', 'gsheetconnector-for-elementor-forms'); ?>
                                </label>
                                <input type="date"
                                id="sync-to-date"
                                name="sync_to_date"
                                value="<?php echo esc_attr($to_date); ?>"
                                min="<?php echo esc_attr($from_date); ?>"
                                max="<?php echo esc_attr($to_date); ?>"
                                class="wpgs-date-picker">
                            </div>
                        </div>

                        <div class="sync_div_design syncronous_elemgsc_form_entry_gsheet mt-20">
                            <span><?php esc_html_e('Sync Entries', 'gsheetconnector-for-elementor-forms'); ?></span>
                            <span class="sync-elemgsc-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </div>

                    <input type="hidden" id="form-id-sync" value="<?php echo esc_attr($form_id); ?>">
                    <input type="hidden" id="feed-id-sync" value="<?php echo esc_attr($feed_id); ?>">

                    <input type="hidden"
                    name="elementor-sync-gs-ajax-nonce"
                    id="elementor-sync-gs-ajax-nonce"
                    value="<?php echo esc_attr(wp_create_nonce('elementor-sync-gs-ajax-nonce')); ?>" />
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- End blurred pro area -->

</form>
<!-- Main form end -->

</div>

</div>