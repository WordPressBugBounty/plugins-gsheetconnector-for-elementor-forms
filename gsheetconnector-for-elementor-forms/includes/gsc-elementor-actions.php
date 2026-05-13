<?php

/**
 * Action after submit to add a records to Google Spreadsheet
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use ElementorPro\Plugin;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;
use ElementorPro\Modules\Forms\Controls\Fields_Map;
use ElementorPro\Modules\Forms\Submissions\Database\Query as ele_submission_db;


/**
 * Class GSC_Elementor_Actions_Free
 */
class GSC_Elementor_Actions_Free extends \ElementorPro\Modules\Forms\Classes\Action_Base
{

    /**
     * Get Name
     *
     * Return the action name
     *
     * @access public
     * @return string
     */
    public function get_name()
    {
        return esc_html('gsc_elementorentor');
    }

    /**
     * Get Label
     *
     * Returns the action label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return esc_html__('GSheetConnector', 'gsheetconnector-for-elementor-forms');
    }

    /**
     * Run
     *
     * Runs the action after submit
     *
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record Record.
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler Ajax handler.
     */
    public function run($record, $ajax_handler)
    {
        try {
            // Safety check for record object
            if (!is_object($record) || !method_exists($record, 'get')) {
                return;
            }

            $gs_ele_settings = $record->get('form_settings');

            // Ensure settings is array
            if (!is_array($gs_ele_settings)) {
                $gs_ele_settings = [];
            }

            // Get tab mapping safely
            $tab_mapping = get_option('elefgs_tabsId');
            if (!is_array($tab_mapping)) {
                $tab_mapping = [];
            }

            // Step 1: Initialize values safely
            $spreadsheetsId = isset($gs_ele_settings['gs_spreadsheet_id'])
            ? $gs_ele_settings['gs_spreadsheet_id']
            : '';

            $tab_id = isset($gs_ele_settings['gs_spreadsheet_tab_name'])
            ? $gs_ele_settings['gs_spreadsheet_tab_name']
            : '';

            $tab_name = '';

            // Step 2: Manual mode check
            if (
                isset($gs_ele_settings['enable_manual_sheet_settings']) &&
                $gs_ele_settings['enable_manual_sheet_settings'] === 'yes'
            ) {

                if (!empty($gs_ele_settings['manual_sheet_id'])) {
                    $spreadsheetsId = $gs_ele_settings['manual_sheet_id'];
                }

                if (
                    !empty($gs_ele_settings['manual_tab_id']) &&
                    isset($tab_mapping[$spreadsheetsId]) &&
                    isset($tab_mapping[$spreadsheetsId][$gs_ele_settings['manual_tab_id']])
                ) {
                    $tab_name = $tab_mapping[$spreadsheetsId][$gs_ele_settings['manual_tab_id']];
                } elseif (!empty($gs_ele_settings['manual_tab_name'])) {
                    $tab_name = $gs_ele_settings['manual_tab_name'];
                }
            } else {
                // Auto mode
                if (
                    isset($tab_id) &&
                    $tab_id !== '' &&
                    isset($tab_mapping[$spreadsheetsId]) &&
                    array_key_exists($tab_id, $tab_mapping[$spreadsheetsId])
                ) {
                    $tab_name = $tab_mapping[$spreadsheetsId][$tab_id];
                }
            }




            // Step 3: Validate essentials
            if ($spreadsheetsId !== '' && $tab_name !== '') {

                // Step 4: Prepare data
                $latest_id = wp_cache_get('gsc_latest_elementor_id');
                if (false === $latest_id) {
                    global $wpdb;
                    $result = $wpdb->get_results("SELECT MAX(id) as latest_id FROM {$wpdb->prefix}e_submissions");
                    $latest_id = isset($result[0]->latest_id) ? $result[0]->latest_id : '';
                    wp_cache_set('gsc_latest_elementor_id', $latest_id, '', 300); // Cache for 5 minutes
                }

                $gsele_value_data = ['Entry ID' => $latest_id];
                $gsele_raw_fields = $record->get('fields');

                $local_date = date_i18n(get_option('date_format'));
                $local_time = date_i18n(get_option('time_format'));

                foreach ($gsele_raw_fields as $field) {
                    $key = trim($field['title']);
                    $gsele_value_data[$key] = $field['value'];
                }
                $gsele_value_data['Entry Date'] =  $local_date;
                $gsele_value_data['date'] =  $local_date;
                $gsele_value_data['Date'] =  $local_date;
                $gsele_value_data['Submission Date'] =  $local_date;
                $gsele_value_data['time'] =  $local_time;
                
                // Step 5: Send to sheet
                if (!empty($gsele_value_data)) {
                    $doc = new GSC_Elementor_Free();
                    $doc->auth();
                    // $doc->setSpreadsheetId($spreadsheetsId);
                    // $doc->setWorkTabId($tab_name); // ✅ This MUST be the actual sheet name
                    $doc->add_row_feed($spreadsheetsId, $tab_name, $gsele_value_data, false);
                    // $doc->add_row($gsele_value_data);
                }
            } else {
                GsEl_Connector_Utility::ele_gs_debug_log('[GSC Debug] Missing spreadsheet ID or tab name.');
            }

            return;
        } catch (Exception $e) {
            GsEl_Connector_Utility::ele_gs_debug_log('[GSC Error] ' . $e->getMessage());
            return;
        }
    }


    /**
     * Register Settings Section
     *
     * Registers the Action controls
     *
     * @access public
     * @param \Elementor\Widget_Base $widget settings.
     */
    public function register_settings_section($widget)
    {
        $gsc_elementor_document = Plugin::elementor()->documents->get(get_the_ID());
        global $gsc_elementor_headers, $gsc_elementor_exclude_headertype;
        global $gsc_elementor_spreadsheetid, $gsc_elementor_sheetname, $gsc_elementor_sheet_headers, $gsc_elementor_sheetheaders, $existincurrentpage, $gsc_elementor_sheetheaders_new, $gsc_elementor_form_fields;

        // Get the verification and token options.
        $elefgs_verify = get_option('elefgs_verify');
        $elefgs_token = get_option('elefgs_token');


        // Check if token is empty OR verification is invalid OR both conditions are true
        if (empty($elefgs_token) || $elefgs_verify == "invalid-auth") {
            $elefgs_verify = 'invalid-auth';
        }

        if ($gsc_elementor_document) {
            $gsc_elementor_data = $gsc_elementor_document->get_elements_data();
            $gsc_elementor_data_global = $gsc_elementor_data;

            $gsc_elementor_data = Plugin::elementor()->db->iterate_data(
                $gsc_elementor_data,
                function ($element) use (&$do_update) {
                    if (isset($element['widgetType']) && 'form' === (string) $element['widgetType']) {
                        global $gsc_elementor_headers, $gsc_elementor_exclude_headertype;
                        global $gsc_elementor_spreadsheetid, $gsc_elementor_sheetname, $gsc_elementor_sheet_headers;
                        $gsc_elementor_exclude_headertype = array('honeypot', 'recaptcha', 'recaptcha_v3', 'html');
                        if (isset($element['settings']['enable_manual_sheet_settings']) && $element['settings']['enable_manual_sheet_settings'] === 'yes') {

                            $gsc_elementor_spreadsheetid = !empty($element['settings']['manual_sheet_id']) ? $element['settings']['manual_sheet_id'] : '';
                            if (!empty($element['settings']['manual_tab_id'])) {
                                $gsc_elementor_sheetname = $element['settings']['manual_tab_id'];
                            } elseif (!empty($element['settings']['manual_tab_name'])) {
                                $gsc_elementor_sheetname = $element['settings']['manual_tab_name'];
                            } else {
                                $gsc_elementor_sheetname = '';
                            }
                        } else {

                            if (isset($element['settings']['gs_spreadsheet_id'])) {
                                $gsc_elementor_spreadsheetid = $element['settings']['gs_spreadsheet_id'];
                            }
                            if (isset($element['settings']['gs_spreadsheet_tab_name'])) {
                                $gsc_elementor_sheetname = $element['settings']['gs_spreadsheet_tab_name'];
                            }
                        }

                        // Add "Entry ID" to the headers unconditionally
                        // $gsc_elementor_headers['Entry ID'] = 'Entry ID';

                        foreach ($element['settings']['form_fields'] as $formdata) {
                            if (!isset($formdata['field_type']) || (isset($formdata['field_type']) && !in_array($formdata['field_type'], $gsc_elementor_exclude_headertype, true))) {
                                $gsc_elementor_headers[$formdata['custom_id']] = isset($formdata['field_label']) && !empty($formdata['field_label']) ? $formdata['field_label'] : ucfirst($formdata['custom_id']);
                                // $gsc_elementor_headers[$formdata['custom_id']] = $formdata['field_label'] ? $formdata['field_label'] : ucfirst($formdata['custom_id']);
                            }
                        }
                        return $gsc_elementor_headers;
                    }
                }
            );
            if (empty($gsc_elementor_headers)) {
                Plugin::elementor()->db->iterate_data(
                    $gsc_elementor_data_global,
                    function ($element) use (&$do_update) {
                        if (isset($element['widgetType']) && 'global' === (string) $element['widgetType']) {
                            if (!empty($element['templateID'])) {
                                $global_form = get_post_meta($element['templateID'], '_elementor_data', true);
                                $global_form_meta = json_decode($global_form, true);
                                if ($global_form_meta) {
                                    global $gsc_elementor_headers, $gsc_elementor_exclude_headertype;
                                    global $gsc_elementor_spreadsheetid, $gsc_elementor_sheetname, $gsc_elementor_sheet_headers;
                                    $gsc_elementor_exclude_headertype = array('honeypot', 'recaptcha', 'recaptcha_v3', 'html');
                                    if (isset($global_form_meta[0]['settings']['gs_spreadsheet_id'])) {
                                        $gsc_elementor_spreadsheetid = $global_form_meta[0]['settings']['gs_spreadsheet_id'];
                                    }
                                    if (isset($global_form_meta[0]['settings']['gs_spreadsheet_tab_name'])) {
                                        $gsc_elementor_sheetname = $global_form_meta[0]['settings']['gs_spreadsheet_tab_name'];
                                    }
                                    if (is_array($global_form_meta[0]['settings']['form_fields'])) {


                                        foreach ($global_form_meta[0]['settings']['form_fields'] as $formdata) {
                                            if (!isset($formdata['field_type']) || (isset($formdata['field_type']) && !in_array($formdata['field_type'], $gsc_elementor_exclude_headertype, true))) {
                                                $gsc_elementor_headers[$formdata['custom_id']] = $formdata['field_label'] ? $formdata['field_label'] : ucfirst($formdata['custom_id']);
                                            }
                                        }
                                    }
                                    return $gsc_elementor_headers;
                                }
                            }
                        }
                    }
                );
            }
        }
        $widget->start_controls_section(
            'section_gsce',
            array(
                'label' => esc_attr__('GSheetConnector', 'gsheetconnector-for-elementor-forms'),
                'condition' => array(
                    'submit_actions' => $this->get_name(),
                ),
            )
        );
        // Fetch and display Sheet details
        $sheet_data = get_option('elefgs_sheetId');
        $sheetId_array = isset($sheet_data) ? $sheet_data : array();
        $tabId_data = get_option('elefgs_tabsId');;
        $tabId_array = isset($tabId_data) ? $tabId_data : array();

        $sheet_id_name = array(
            '' => esc_html__('Select Google Spreadsheet', 'gsheetconnector-for-elementor-forms'),
        );

        $widget->add_control(
            'auth_integration_verify',
            array(
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => $elefgs_verify,
            )
        );
        $token = get_option('elefgs_token');
        $email_account = '';

        if (!empty($token)) {
            $google_sheet  = new GSC_Elementor_Free();
            $email_account = $google_sheet->gsheet_print_google_account_email();
        }

        if ($email_account) {
            $widget->add_control(
                'gselef_connected_email_account',
                [
                    'type' => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'  => '<div class="gsc-connect-head">Connected Email Account ' . esc_html($email_account) . '</div>',
                    'condition' => [
                        'auth_integration_verify' => 'valid',
                    ],
                ]
            );
        }

        // Step 1: Add the toggle to enable manual sheet settings
        $widget->add_control(
            'enable_manual_sheet_settings',
            [
                'label' => __('Enable Manual Google Sheets Configuration', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'classes' => 'main-heading-text-controls',
                'label_on' => __('Yes', 'gsheetconnector-for-elementor-forms'),
                'label_off' => __('No', 'gsheetconnector-for-elementor-forms'),
                'return_value' => 'yes',
                'default' => '',
                'separator' => 'before',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],

            ]
        );

        // Step 2: Manual input fields (visible only if toggle is enabled)

        $widget->add_control(
            'default_form_fields_heading1',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="gsc-heading">Manual Google Sheets Configuration : </div>',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                    'enable_manual_sheet_settings' => 'yes',


                ],
            ]
        );

        $widget->add_control(
            'manual_sheet_name',
            [
                'label' => __('Sheet Name', 'gsheetconnector-for-elementor-forms'),
                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elementor-text-controls',
                'event' => 'selectspreadsheet',
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => false, // ✅ ADD THIS LINE
                'condition' => [
                    'enable_manual_sheet_settings' => 'yes',
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );

        $widget->add_control(
            'manual_sheet_id',
            [
                'label' => __('Sheet ID', 'gsheetconnector-for-elementor-forms'),
                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elementor-text-controls',
                'event' => 'selectspreadsheet',
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => false, // ✅ ADD THIS LINE
                'condition' => [
                    'enable_manual_sheet_settings' => 'yes',
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );

        $widget->add_control(
            'manual_tab_name',
            [
                'label' => __('Tab Name', 'gsheetconnector-for-elementor-forms'),
                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elementor-text-controls',
                'event' => 'selectspreadsheet',
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => false, // ✅ ADD THIS LINE
                'condition' => [
                    'enable_manual_sheet_settings' => 'yes',
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );

        $widget->add_control(
            'manual_tab_id',
            [
                'label' => __('Tab ID', 'gsheetconnector-for-elementor-forms'),
                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elementor-text-controls',
                'event' => 'selectspreadsheet',
                'type' => \Elementor\Controls_Manager::TEXT,
                'ai' => false, // ✅ ADD THIS LINE
                'condition' => [
                    'enable_manual_sheet_settings' => 'yes',
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );


        $widget->add_control(
            'gsc_form_heading_1',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="gsc-heading">Automatic Google Sheets Configuration:</div>',
                'content_classes' => 'gsc-heading-wrapper',
                'condition' => [
                    'enable_manual_sheet_settings' => '',
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );



        // Step 3: Modify your default controls to show only when manual toggle is NOT enabled
        $widget->add_control(
            'gs_spreadsheet_id',
            [
                'label' => esc_attr__('Sheet Name', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $sheetId_array,
                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elefgs_sheet_id',
                'event' => 'selectspreadsheet',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                    'enable_manual_sheet_settings!' => 'yes', // <-- show only if manual mode is NOT enabled
                ],
            ]
        );

        $tab_arr = isset($tabId_array[$gsc_elementor_spreadsheetid]) ? $tabId_array[$gsc_elementor_spreadsheetid] : array();

        // same for gs_spreadsheet_tab_name:
        $widget->add_control(
            'gs_spreadsheet_tab_name',
            [
                'label' => esc_attr__('Sheet Tab Name', 'gsheetconnector-for-elementor-forms') . '<span class="elementor-state-icon tabselectionloading" style="display:none; margin-left: 15px;"><i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i></span>',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => !empty($tab_arr) ? $tab_arr : array('0' => 'Loading...'),

                'label_block' => true,
                'separator' => 'before',
                'classes' => 'elefgs_sheet_id',
                'event' => 'selectspreadsheet',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                    'enable_manual_sheet_settings!' => 'yes', // <-- show only if manual mode is NOT enabled
                ],
            ]
        );


        $widget->add_control(
            'gs_spreadsheet_selected_tab_name',
            array(
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => "Sheet1",
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],
            )
        );

        $widget->add_control(
            'gs_view_spreadsheet',
            [
                'type' => \Elementor\Controls_Manager::BUTTON,
                'button_type' => 'viewspreadsheet',
                'classes' => 'btn-secondary',
                'text' => __('View Spreadsheet', 'gsheetconnector-for-elementor-forms'),
                'event' => 'namespace:editor:gsceviewsheet',

                'conditions' => [
                    'auth_integration_verify' => 'valid',
                    'relation' => 'and',
                    'terms' => [

                        [
                            'name' => 'auth_integration_verify',
                            'operator' => '==',
                            'value' => 'valid',
                        ],


                        [
                            'relation' => 'or',
                            'terms' => [

                                /*  Manual Mode */
                                [
                                    'relation' => 'and',
                                    'terms' => [
                                        [
                                            'name' => 'enable_manual_sheet_settings',
                                            'operator' => '==',
                                            'value' => 'yes',
                                        ],
                                        [
                                            'name' => 'manual_sheet_name',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                        [
                                            'name' => 'manual_sheet_id',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                        [
                                            'name' => 'manual_tab_name',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                        [
                                            'name' => 'manual_tab_id',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                    ],
                                ],

                                /* Select Mode (IMPORTANT PART)*/
                                [
                                    'relation' => 'and',
                                    'terms' => [
                                        [
                                            'name' => 'enable_manual_sheet_settings',
                                            'operator' => '!=',
                                            'value' => 'yes',
                                        ],
                                        [
                                            'name' => 'gs_spreadsheet_id',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                        [
                                            'name' => 'gs_spreadsheet_tab_name',
                                            'operator' => '!=',
                                            'value' => '',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $widget->add_control(
            'gs_view_fetchsheet',
            array(
                //'label' => 'SPREAD SHEET FETCHING',
                'type' => \Elementor\Controls_Manager::BUTTON,
                'classes' => 'gsc-sheet-card-status',
                'text' => __('Fetch Sheets', 'gsheetconnector-for-elementor-forms') . '<p class="gsc-fetch-text"><span class="blue-text-here">Click here</span> to retrieve all accessible Google Sheets and update the dropdown in Form Feed settings. Use this after creating a new sheet or renaming a spreadsheet or tab.</p>' . '<span class="elementor-state-icon fetchsheetloading" style="display:none; margin-right: 15px;">
                <i class="eicon-loading eicon-animation-spin" aria-hidden="true"></i>
                </span>',
                'event' => 'namespace:editor:gscfetchsheet',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                    'enable_manual_sheet_settings!' => 'yes', // <-- show only if manual mode is NOT enabled
                ],

            )
        );

        $tab_data_json = json_encode($tabId_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT);

        $widget->add_control(
            'gs_elmentor_all_sheet_data',
            array(
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => isset($tabId_array) ? json_encode($tabId_array) : "",
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],
            )
        );


        $widget->add_control(
            'tab_selection_process_log',
            array(
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => '0',
            )
        );

        $widget->add_control(
            'gs-ajax-nonce-ele',
            array(
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => wp_create_nonce('gs-ajax-nonce-ele'),
            )
        );

        // $widget->add_control(
        //     'gs_view_spreadsheet',
        //     array(
        //         'label' => 'VIEW SPREAD SHEET',
        //         'type' => \Elementor\Controls_Manager::BUTTON,
        //         'button_type' => 'viewspreadsheet',
        //         'text' => __('VIEW SPREAD SHEET', 'gsheetconnector-for-elementor-forms'),
        //         'event' => 'namespace:editor:gsceviewsheet',
        //         'condition' => [
        //             'auth_integration_verify' => 'valid',
        //             'enable_manual_sheet_settings!' => 'yes'
        //         ],

        //     )
        // );
        //     $widget->add_control(
        //         'gs_view_spreadsheet_manual',
        //         array(
        //             //'label' => 'VIEW SPREAD SHEET11',
        //             'type' => \Elementor\Controls_Manager::BUTTON,
        //             'button_type' => 'viewspreadsheet',
        // 'classes' => 'btn-secondary',
        //             'text' => __('Get Sheet URL', 'gsheetconnector-for-elementor-forms'),
        //             'event' => 'namespace:editor:gsceviewsheetmanual',
        //             'condition' => [
        //                 'auth_integration_verify' => 'valid',
        //                  'manual_sheet_name!' => '',
        // 	'manual_sheet_id!' => '',
        // 	'manual_tab_name!' => '',
        // 	'manual_tab_id!' => '',

        //             ],

        //         )
        //     );

        //        $widget->add_control(
        //            'gs_view_process_fetchsheet',
        //            array(
        //                'label' => '',
        //                'type' => \Elementor\Controls_Manager::RAW_HTML,
        //                'raw' => '<span class="loading-sign-process-fetch"></span>',
        //                'condition' => [
        //                    'auth_integration_verify' => 'valid',
        //                ],
        //
        //            )
        //        );

        $widget->add_control(
            'gs_feed_spreadsheet',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'classes' => 'feed-list-link',
                'text' => __('Click here to configure Form Feeds', 'gsheetconnector-for-elementor-forms'),
                'raw' => '<a href=' . esc_url(admin_url("admin.php?page=gsheetconnector-elementor-config&tab=form_feed_settings")) . ' " target="_blank">Click here to Setup form feeds settings to easily configure multiple feeds simultaneously</a>',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );

        // Add a title for "Unlock More Features"
        $widget->add_control(
            'unlock_more_features_title',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="gsc-heading-box">PRO Sync Features </div>',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );



        $widget->add_control(
            'default_form_text',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="line-height:22px;">Unlock advanced automation, better control, and powerful syncing tools to manage your Google Sheets integration more efficiently.</p>',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );


        $widget->add_control(
            'gs_view_auth_license_pending',
            [
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'text' => __('Upgrade to GSheetConnector Pro', 'gsheetconnector-for-elementor-forms'),
                'raw' => '<span class="edit-gs-upgrade-btn"><a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank">Get PRO Access</a></span>',
                'condition' => [
                    'auth_integration_verify' => 'valid',
                ],
            ]
        );



        // Add a heading for "Default Form Fields"
        $widget->add_control(
            'default_form_fields_heading',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="gsc-heading">Select Fields to Sync <span class="ele-pro">PRO</span></div>',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );
        $widget->add_control(
            'gsc_form_heading_0',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="field-list-pill">Field List</div>',
                'content_classes' => 'gsc-heading-wrapper',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'gsc_form_heading',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="field-type-system">Submission Info</div>',
                'content_classes' => 'gsc-heading-wrapper',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[Entry ID]',
            [
                'label' => esc_attr__('Entry ID', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );


        // Add switcher controls for each header
        if (!empty($gsc_elementor_headers)) {
            foreach ($gsc_elementor_headers as $key => $value) {
                $widget->add_control(
                    'headers[' . sanitize_key($value) . ']',
                    [
                        'label' => esc_attr($value), // ✅ Escaped but not translated
                        'type' => \Elementor\Controls_Manager::SWITCHER,
                        'classes' => 'elementor-field-list-control field_list_bg',
                        'condition' => [
                            'auth_integration_verify' => 'valid',

                        ],
                    ]
                );
            }
        }

        // Add switcher controls for each header
        $widget->add_control(
            'headers[Entry Date]',
            [
                'label' => esc_attr__('Entry Date', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'raw' => '',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[Post ID]',
            [
                'label' => esc_attr__('Post ID', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'raw' => '',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[User Name]',
            [
                'label' => esc_attr__('User Name', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[User IP]',
            [
                'label' => esc_attr__('User IP', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[User Agent]',
            [
                'label' => esc_attr__('User Agent', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[User ID]',
            [
                'label' => esc_attr__('User ID', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'headers[Referrer]',
            [
                'label' => esc_attr__('Referrer', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'disabled'    => true,
                'readonly'    => true,
                'classes' => 'elementor-field-list-control special_mail_tags_bg gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );


        // Add a heading for "Form Fields"
        //        $widget->add_control(
        //            'form_fields_heading',
        //            [
        //                'type' => \Elementor\Controls_Manager::RAW_HTML,
        //                'raw' => '<h3>Form Fields (Headers)</h3>',
        //                'condition' => [
        //                    'auth_integration_verify' => 'valid',
        //                ],
        //            ]
        //        );



        $widget->add_control(
            'gsc_form_heading_3',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div class="gsc-heading">Header Settings <span class="ele-pro">PRO</div></div>',
                'content_classes' => 'gsc-heading-wrapper',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            ]
        );

        $widget->add_control(
            'gs_freezheader',
            array(
                'label' => esc_attr__('Freeze Header', 'gsheetconnector-for-elementor-forms'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'classes' => 'gsc-switch-disabled',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            )
        );

        $widget->add_control(
            'gs_header_color_elem',
            array(
                'label' => 'Header Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'classes' => 'gsc-header-color-',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            )
        );

        $widget->add_control(
            'gs_odd_color_elem',
            array(
                'label' => 'Odd Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'classes' => 'gsc-header-color-',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            )
        );

        $widget->add_control(
            'gs_even_color_elem',
            array(
                'label' => 'Even Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'classes' => 'gsc-header-color-',
                'condition' => [
                    'auth_integration_verify' => 'valid',

                ],
            )
        );


        //			$widget->add_control(
        //            'default_form_fields_heading2',
        //            [
        //                'type' => \Elementor\Controls_Manager::RAW_HTML,
        //                'raw' => '<div class="gsc-heading">Upgrade to GSheetConnector Pro</div>',
        //                'condition' => [
        //                    'auth_integration_verify' => 'valid',
        //
        //                ],
        //            ]
        //        );


        $widget->add_control(
            'gs_view_auth_pending_url',
            array(
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => "<div class='feed-alert-header'>Google Sheets Setup Required</div><p class='elementor-gs-display-note'<strong>Your selected Method is: </strong> Existing <p class='elementor-gs-display-note'>To start sending form entries to Google Sheets, please connect your Google account first.</p>

                <ul class='inner-ele-list'>
                <li>✔ Click on the Sign in with Google button</li>
                <li>✔ Log in using your Google account</li>
                <li>✔ Select the Google account where your Sheets are stored</li>
                <li>✔ Grant access to: Google Drive & Google Sheets</li>
                <li>✔ Save the authentication code if prompted</li>
                </ul>
                <a class='inner-ele-btn' href='admin.php?page=gsheetconnector-elementor-config&tab=integration' target='_blank'>Go to Integration Setup</a>
                ",
                'condition' => [
                    'auth_integration_verify' => 'invalid-auth',
                ],

            )
        );



        $widget->end_controls_section();
    }

    /**
     * On Export
     *
     * Clears form settings on export
     *
     * @access Public
     * @param array $element_sheets clear settings.
     */
    public function on_export($element_sheets) {}
}

$gsc_elementor_actions = new GSC_Elementor_Actions_Free;
