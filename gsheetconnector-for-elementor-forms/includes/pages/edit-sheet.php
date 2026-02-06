<?php

$gsc_elementor_integration = new GSC_Elementor_Integration();

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$feed_id = isset($_GET['feed_id']) ? filter_var(wp_unslash($_GET['feed_id']), FILTER_SANITIZE_NUMBER_INT) : '';
$form_id = isset($_GET['form_id']) ? filter_var(wp_unslash($_GET['form_id']), FILTER_SANITIZE_NUMBER_INT) : '';
// phpcs:enable

// Get the saved feed data from the database
$feed_data = get_post_meta($feed_id, 'gscele_form_feeds', true);

$sheet_name = isset($feed_data['sheet-name']) ? esc_attr($feed_data['sheet-name']) : '';
$sheet_id = isset($feed_data['sheet-id']) ? esc_attr($feed_data['sheet-id']) : '';
$tab_name = isset($feed_data['sheet-tab-name']) ? esc_attr($feed_data['sheet-tab-name']) : '';
$tab_id = isset($feed_data['tab-id']) ? esc_attr($feed_data['tab-id']) : '';
$met_form_id = isset($_GET['form_id']) ? filter_var($_GET['form_id'], FILTER_SANITIZE_NUMBER_INT) : "";

?>

<div class="frmn-main-div">
    <div class="frmn-bread-crumb">
        <ul class="breadcrumb_frmntr">
            <li>
                <a href="?page=gsheetconnector-elementor-config&tab=form_feed_settings">
                    <button class="button button-secondary">
                        <span class="back-icon">&#8592;</span> <?php esc_html_e( 'Back to Feeds List', 'gsheetconnector-for-elementor-forms' ); ?>
                   </button>
                </a>
            </li>
        </ul>
    </div>
    <!-- form feed sheet settigns -->
    <form id="edit-feed-form" method="post" action="">

        <?php if (isset($_POST['execute-edit-feed-elementor']) && !empty($success_message)): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($success_message); ?></p>
            </div>
        <?php endif; ?>

        <input type="hidden" id="feed-id" name="feed_id" value="<?php echo $feed_id; ?>">
        <input type="hidden" id="form-id" name="form_id" value="<?php echo $form_id; ?>">

        <!-- MANUAL METHOD OF 1st DiV -->
        <div class="manual-section-elementorgsc">
             <h2 class="info-headers"><?php echo esc_html(__('Edit Feed and Integrate with Google Sheets', 'gsheetconnector-for-elementor-forms')); ?></h2>
			<p class="sub-desc"><?php echo esc_html(__('Manually connect your Elementor Form with Google Sheets by entering the Sheet Name, Sheet ID, Tab Name, and Tab ID. This ensures that form submissions are stored directly in your chosen Google Sheet. Fill in the required details and  ', 'gsheetconnector-for-elementor-forms')); ?>
<strong><?php echo esc_html__('click Save Changes', 'gsheetconnector-for-elementor-forms'); ?></strong>
<?php echo esc_html__(' to complete the integration.', 'gsheetconnector-for-elementor-forms'); ?>
</p>
			
			
                <div class="field-row row">
                    <label for="edit-sheet-name"><?php echo esc_html(__('Sheet Name', 'gsheetconnector-for-elementor-forms')); ?></label> 
                    <input type="text" id="edit-sheet-name" name="elementor-gs[sheet-name-custom]" value="<?php echo esc_attr($sheet_name); ?>">
                    <div class="tooltip-new">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                        <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("Go to your google account and click on Google apps icon and than click Sheet, Select the name of the appropriate sheet you want to link your contact form or create new sheet.", "gsheetconnector-for-elementor-forms")); ?></span>
                    </div>
                </div> <!-- field row #end -->

            <div class="field-row row">
                <label for="edit-sheet-id"><?php echo esc_html(__('Sheet ID', 'gsheetconnector-for-elementor-forms')); ?></label> 
               <input type="text" id="edit-sheet-id" name="elementor-gs[sheet-id-custom]" value="<?php echo esc_attr($sheet_id); ?>">
                <div class="tooltip-new">
                    <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                    <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("you can get sheet id from your sheet URL", "gsheetconnector-for-elementor-forms")); ?></span>
                </div>
           </div> <!-- field row #end -->

           <div class="field-row row">
                <label for="edit-tab-name"><?php echo esc_html(__('Tab Name', 'gsheetconnector-for-elementor-forms')); ?></label> 
                <input type="text" id="edit-tab-name" name="elementor-gs[sheet-tab-name-custom]" value="<?php echo esc_attr($tab_name); ?>">
                <div class="tooltip-new">
                    <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                    <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("Open your Google Sheet with which you want to link your contact form . You will notice a tab names at bottom of the screen. Copy the tab name where you want to have an entry of contact form.", "gsheetconnector-for-elementor-forms")); ?></span>
                </div>
            </div> <!-- field row #end -->

            <div class="field-row row">

                <label for="edit-tab-id"><?php echo esc_html(__('Tab ID', 'gsheetconnector-for-elementor-forms')); ?></label> 
                <input type="text" id="edit-tab-id" name="elementor-gs[tab-id-custom]" value="<?php echo esc_attr($tab_id); ?>">
                    <div class="tooltip-new">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                        <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("you can get tab id from your sheet URL", "gsheetconnector-for-elementor-forms")); ?></span>
                    </div>
                </div> <!-- field row #end -->
              <div class="sheet-url" id="sheet-url" style="display: flex;">
            <?php if ((isset($sheet_id) && $sheet_id!="") && (isset($tab_id) && $tab_id!="")) {
                                     ?>
           
			<div class="row">
			<label>
          <?php esc_html_e( 'Google Sheet URL', 'gsheetconnector-for-elementor-forms' ); ?>
           </label>

           <a class="sheet-url-elementor" 
   href="<?php echo esc_url( 'https://docs.google.com/spreadsheets/d/' . $sheet_id . '/edit#gid=' . $tab_id ); ?>" 
   target="_blank">
   <?php esc_html_e( 'Sheet URL', 'gsheetconnector-for-elementor-forms' ); ?>
</a> </div>	  
<?php
               }
               ?>
        </div>
	
		<div class="row"> <label></label>
       <input type="hidden" name="gs-ajax-nonce" id="gs-ajax-nonce" value="<?php echo esc_attr(wp_create_nonce('gs-ajax-nonce')); ?>" />
      <input type="submit" 
       name="execute-edit-feed-elementor" 
       id="execute-save" 
       class="button button-primary" 
       value="<?php echo esc_attr__( 'Save Changes', 'gsheetconnector-for-elementor-forms' ); ?>">
		</div>	

</div>
 
    </form>
<!-- Free setting End -->

<!-- <div class="" id=""> -->
            <div class="auto-section" style="display:block;">
                
                <h2 class="oneoforall"><span><?php echo esc_html(__('Auto Google Sheet Settings', 'gsheetconnector-for-elementor-forms')); ?></span><span class="pro-ver"><?php echo __('PRO', 'gsheetconnector-for-elementor-forms'); ?></span></h2>
                
                <div class="gs-fields">
                    
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                     <div class="sheet-details <?php echo $class; ?>">
                        <div class="row">
                            <label><?php echo esc_html(__('Google Spreadsheet Name', 'gsheetconnector-for-elementor-forms')); ?></label>
                            <select name="elementor-gs[gs-elementor-sheet-id]" id="gs-elementor-sheet-id">
                                <option value=""><?php echo esc_html(__('Select', 'gsheetconnector-for-elementor-forms')); ?></option>
                                
                                <option value="create_new"><?php echo esc_html(__('Create New', 'gsheetconnector-for-elementor-forms')); ?></option>
                            </select>

                            <span class="tooltip-new">
                                <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                                <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("Not fetching sheet details to the dropdown than add sheet and tab name manually.", "gsheetconnector-for-elementor-forms")); ?></span>
                            </span>
                            <span class="error_msg" id="error_spread"></span>
                            <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                            
                        </div>
                        <i class="errorSelect errorSelectsheet"></i>
                    
                    <div class="row">
                        <label><?php echo esc_html(__('Google Sheet Tab Name', 'gsheetconnector-for-elementor-forms')); ?></label>
                        <select name="elementor-gs[gs-sheet-tab-name]" id="gs-sheet-tab-name">
                            <option value=""><?php echo esc_html(__('Select', 'gsheetconnector-for-elementor-forms')); ?></option>
                            
                        </select>

                        <span class="tooltip-new">
                            <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                            <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("Not fetching sheet Tab details to the dropdown than add sheet and tab name manually.", "gsheetconnector-for-elementor-forms")); ?></span>
                        </span>
                    </div>
         
                        </div>
                    
                     <!-- <input type="submit" name=""  class="button button-primary google-setting" value="Save Changes"> -->
                    
                    <!-- <div class="" id=""> -->
                    <i class="errorSelect errorSelecttabs"></i>

                    <div class="create-ss-wrapper" style="display: none;">
                        <label>
                            <?php echo esc_html(__('Create Spreadsheet', 'gsheetconnector-for-elementor-forms')); ?>
                        </label>
                        <input type="text" name="_gs_elementor_setting_create_sheet" value="" id="_gs_elementor_setting_create_sheet">
                        <span class="error_msg" id="error_new_spread"></span>
                    </div>

                    <p id="gs-validation-message"></p>
                    <p id="gs-valid-message"></p>

                    <?php if (!empty(get_option('elefgs_verify')) && (get_option('elefgs_verify') == "valid")) { ?>
                       <p class="gscelementorform-sync-row"> <?php esc_html_e( 'Spreadsheet Name and URL not showing?', 'gsheetconnector-for-elementor-forms' ); ?>
    <a id="gscelementorform-sync" data-init="yes" class="sync-button">
        <?php esc_html_e( 'Click Here', 'gsheetconnector-for-elementor-forms' ); ?>
    </a>
    <?php esc_html_e( 'to fetch sheets.', 'gsheetconnector-for-elementor-forms' ); ?>
    <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
</p>

                    <?php } ?>
               <!--  </div> -->
            </div>

        </div>      

        <?php
        // Retrieve form data from post meta using form ID
        $form_data = get_post_meta($form_id, '_elementor_data', true);


        $field_labels = [];
        $field_labels['Entry ID'] = 'Entry ID';

        // Ensure $form_data is valid and has expected structure
        if ($form_data) {
            $form_data = json_decode($form_data, true);

            $elements = $this->get_elements_form_data($form_data, "form_fields");

            if (!empty($elements)) {
                foreach ($elements as $field) {
                    if (isset($field['field_label'])) {
                        // Decode HTML entities and strip tags for safe display
                        $field_label = htmlspecialchars_decode(strip_tags($field['field_label']));
                        $field_labels[$field_label] = $field_label;
                    } elseif (isset($field['placeholder']) && !empty($field['placeholder'])) {
                        // Decode HTML entities and strip tags for safe display
                        $field_label = htmlspecialchars_decode(strip_tags($field['placeholder']));
                        $field_labels[$field_label] = $field_label;
                    } elseif (isset($field['custom_id']) && !empty($field['custom_id'])) {
                        // Decode HTML entities and strip tags for safe display
                        $field_label = htmlspecialchars_decode(strip_tags($field['custom_id']));
                        $field_labels[$field_label] = $field_label;
                    }
                }
            }
        }

        // Array of default WordPress mail tags
        $default_mail_tags = [
            'Entry Date' => 'Entry Date',
            'Post ID' => 'Post ID',
            'User Name' => 'User Name',
            'User IP' => 'User IP',
            'User Agent' => 'User Agent',
            'User ID' => 'User ID',
            'Referrer' => 'Referrer',
            'Form Name' => 'Form Name',
            // Add more default mail tags as needed
        ];

        $merged_fields = array_merge($field_labels, $default_mail_tags);

        $final_array = [];
        if (!empty($saved_header_names)) {
            // Loop through $saved_header_names and keep its order
            foreach ($saved_header_names as $key => $value) {
                if (isset($merged_fields[$key])) {
                    $final_array[$key] = $saved_header_names[$key];
                }
            }

            // Add any additional fields from $merged_fields that are not in $saved_header_names
            foreach ($merged_fields as $key => $value) {
                if (!isset($final_array[$key])) {
                    $final_array[$key] = $merged_fields[$key];
                }
            }
        }

        if (!empty($final_array)) {
            $merged_fields = $final_array;
        }

        // Check if the current form is a MetForm
		if(is_plugin_active( 'metform/metform.php' )) {
        if ($form_id === $met_form_id) {
            $met_form_post_type = 'metform-form'; // Change this based on your actual post type
        
            if (get_post_type($form_id) === $met_form_post_type) {
                // MetForm specific processing

				$input_widgets_feed = \Metform\Widgets\Manifest::instance()->get_input_widgets();
                $widget_input_data_feed = get_post_meta($met_form_id, '_elementor_data', true);
                $widget_input_data_feed = json_decode($widget_input_data_feed);

                $fieldDetails_feed = \MetForm\Core\Entries\Map_El::data($widget_input_data_feed, $input_widgets_feed)->get_el();
                $fields_feed = [];

                foreach ($fieldDetails_feed as $key => $field) {
                    $widgetType_feed = $field->widgetType;
                    $type_feed = substr($widgetType_feed, 3);
                    $withoutText_feed = [
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
                    ];

                    if ($type_feed == 'file-upload') {
                        $type_feed = 'file';
                    } elseif (!in_array($type_feed, $withoutText_feed)) {
                        $type_feed = 'text';
                    }

                    $fields_feed[$key] = [
                        'name' => $key,
                        'type' => $type_feed,
                        'label' => $field->mf_input_label,
                    ];
                }
            }
        }
			
		}

        $gsc_elementor_all_header_list_feed = [];

        if (!empty($fields_feed)) {
            foreach ($fields_feed as $ff => $fs) {
                $gsc_elementor_all_header_list_feed[$ff] = $fs['name'];
            }
        }


        // Initialize output array
        $metformOutput = [];

        // Check if $sheet_header_names_metfrom is not empty
        if (!empty($sheet_header_names_metfrom)) {
            // Use the order of $sheet_header_names_metfrom
            foreach ($sheet_header_names_metfrom as $key => $value) {
                $metformOutput[$key] = $value;
            }

            // Include any extra keys from $gsc_elementor_all_header_list_feed that are not in $sheet_header_names_metfrom
            foreach ($gsc_elementor_all_header_list_feed as $key => $value) {
                if (!isset($metformOutput[$key])) {
                    $metformOutput[$key] = $value;
                }
            }
        } else {
            // If $sheet_header_names_metfrom is empty, use $gsc_elementor_all_header_list_feed
            $metformOutput = $gsc_elementor_all_header_list_feed;
        }
        ?>

        <div class="form-fields-list elemgsc-list-set1">

            <div class="elementorgs-color-code">
                <div class="color-elementorgs">
                   <h2>
    <?php esc_html_e( 'Field List | Special Mail Tags', 'gsheetconnector-for-elementor-forms' ); ?>
    <span class="pro-ver">
        <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
    </span>
</h2>

                </div>
               
            </div>
          

            <div class="toggle-button select-all-toggle">
                <label class="switch">
                    <input type="checkbox" id="select-all-checkbox">
                    <span class="slider round"></span>
                </label>
                <span class="label-text">
    <?php esc_html_e( 'Select All', 'gsheetconnector-for-elementor-forms' ); ?>
</span>

            </div>

            <div id="sortable">
                <?php
                if (!empty($merged_fields)) {
                    foreach ($merged_fields as $key => $label) {
                        $is_selected = $header_name_value = $header_name = "";

                        // Always keep "Entry ID" checkbox checked and disabled
                        if ($key == "Entry ID") {
                            $is_selected = 'checked';
                            $disabled = 'disabled';
                        } else {
                            $disabled = '';
                            if (!empty($saved_header) && array_key_exists($key, $saved_header) && $saved_header[$key] == 1) {
                                $is_selected = 'checked';
                            }
                        }

                        if (!empty($saved_header_names) && array_key_exists($label, $saved_header_names)) {
                            $header_name_value = $saved_header_names[$label];
                        }

                        if (!empty($header_name_value)) {
                            $header_name = $header_name_value;
                        } else {
                            $header_name = $label;
                        }

                        $non_sortable_class = ($key == "Entry ID") ? ' non-sortable' : '';

                        if ($key == "Entry ID" || $key == "Entry Date" || $key == "Post ID" || $key == "User Name" || $key == "User IP" || $key == "User Agent" || $key == "User ID" || $key == "Referrer" || $key == "Form Name") {
                            echo '<div class="field-item special_mail_tags_bg' . esc_attr($non_sortable_class) . '">';
                        } else {
                            echo '<div class="field-item field_list_bg' . esc_attr($non_sortable_class) . '">';
                        }

                        echo '<label class="switch">';
                        echo '<input type="checkbox" name="sheet_header[' . $key . ']" value="1" ' . $is_selected . ' ' . $disabled . '>';
                        echo '<span class="slider round"></span>';
                        echo '</label>';
                        echo '<span class="label-text">' . esc_html($key) . '</span>';
                        echo '<input type="text" name="sheet_header_names[' . esc_html($key) . ']" placeholder="' . esc_html($label) . '" value="' . esc_html($header_name) . '">';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <?php
            // Check if MetForm is active
            $metform_active = class_exists('Metform\Widgets\Manifest') && method_exists('Metform\Widgets\Manifest', 'instance');

            if ($metform_active) {
                ?>
                <ul class="metform-header-feed">
                    <!-- MetForm Headers -->
                    <?php
                    if (!empty($metformOutput)) {
                        foreach ($metformOutput as $mfo => $gl) {
                            $field_list_checked_feed = "";
                            $saved_val = isset($sheet_header_names_metfrom[$gl]) ? $sheet_header_names_metfrom[$gl] : $gl;
                            $field_list_checked_feed = isset($saved_headers_metfrom[$mfo]) && $saved_headers_metfrom[$mfo] == 1 ? "checked" : "";
                            ?>
                            <li class="li-metform-header-feed field-item">
                                <div class="toggle-buttom-pos-feed">
                                    <label
                                        class="button-metform-toggle1-feed <?php echo ($field_list_checked_feed == "" ? "button-tog-inactive-feed" : "button-tog-active-feed"); ?> sheet_headers_metfrom-lbl-feed"
                                        id="button-metform-toggle1-click-feed" data-id="<?php echo esc_attr($gl); ?>-"></label>
                                </div>

                                <div class="switch-label-metform-feed">
                                    <label>
                                        <span class="label">
                                            <span class="label_text-metform-feed"><?php echo esc_html($mfo); ?></span>
                                        </span>
                                    </label>
                                </div>

                                <input type="radio" id="<?php echo esc_attr($gl); ?>-one"
                                    name="sheet_headers_metfrom[<?php echo esc_attr($mfo); ?>]" value="1" <?php echo ($field_list_checked_feed != "" ? "checked" : ""); ?>
                                    class="header_name_1-metform sheet_headers-one radio-btn-hide">

                                <input type="radio" id="<?php echo esc_attr($gl); ?>-two"
                                    name="sheet_headers_metfrom[<?php echo esc_attr($mfo); ?>]" value="0" <?php echo ($field_list_checked_feed == "" ? "checked" : ""); ?>
                                    class="header_name_0-metform sheet_headers_metfrom-two radio-btn-hide">

                                <div>
                                    <input type="text" name="sheet_header_names_metfrom[<?php echo esc_attr($mfo); ?>]"
                                        value="<?php echo esc_attr($saved_val); ?>" placeholder="<?php echo esc_attr($gl); ?>">
                                    <span class="edit_col_name-metform-feed"></span>
                                    <span class="update_col_name-metform-feed" style="display:none"><i
                                            class="fa fa-check"></i></span>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </div>

 <!-- Field List #end -->         
                
                
        <div class="form-fields-list elementor-list-set">
                
            <div class="header-manage field-list">

                <h2 class="elementor-title lbl_heading">
                    <?php echo esc_html(__('Header Management ', 'gsheetconnector-for-elementor-forms')); ?></h2>

                <!-- Freeze header START-->
                <div class="misc-head">
                    
                    <div class="toggle-button sheet_formatting-header-toggle">
                        <label for="freeze-header-option" class="switch"  >
                        <input type="checkbox" name="elementor-gs[freeze_header]" id="freeze-header-option"  class="check-toggle-elemgsc" value="">

                            <span class="slider round"></span>
                        </label>
                            <span class="label-text"><?php esc_html_e( 'Freeze Header Row', 'gsheetconnector-for-elementor-forms' ); ?>
                              </span>
                           <span class="pro-ver">
                             <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
                           </span>

                    </div>
                
                    <span class="tooltip-new">
                        <img src="<?php echo GS_CONN_ELE_URL; ?>assets/img/help.png" class="help-icon">
                        <span class="tooltiptext tooltip-right-msg">
    <?php esc_html_e( 'Freeze First Header Row.', 'gsheetconnector-for-elementor-forms' ); ?>
</span>

                    </span>
                </div>
                <!-- Freeze header END-->

                    <!-- Colors START-->
                    <div class="sheet_formatting">
                        <div class="elemgsc-sheet_formatting elemgsc-sheet_formatting">
                            <div class="toggle-button sheet_formatting-header-toggle">
                                <label class="switch" for="sheet_formatting-header-checkbox">
                                    <input type="checkbox" id="sheet_formatting-header-checkbox" name="elementor-gs[sheet_formatting_header]" value="1" >
                                    <span class="slider round"></span>
                                </label>
                                <span class="label-text">
                                  <?php esc_html_e( 'Header - Font Settings', 'gsheetconnector-for-elementor-forms' ); ?>
                                   </span>

                           <span class="pro-ver">
                          <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
                           </span>

                  <span class="tooltip-new">
                  <img src="<?php echo esc_url( GS_CONN_ELE_URL . 'assets/img/help.png' ); ?>" 
         class="help-icon" 
         alt="<?php esc_attr_e( 'Help', 'gsheetconnector-for-elementor-forms' ); ?>">
         
              <span class="tooltiptext tooltip-right-msg">
              <?php esc_html_e( 
            'This feature locks the top row in your selected sheet, providing a consistent reference point as you scroll through your data.', 
            'gsheetconnector-for-elementor-forms' 
        ); ?>
      </span>
   </span>

                            </div>
                        </div>
                    </div>
                    <!-- Colors END-->

                    <!-- 4th Div OF PRO Sheet Background Colors -->
                    <div class="back-formating">
                        
                            <div class="toggle-button sheet-bg-toggle">
                                <label class="switch">
                                    <input type="checkbox" id="sheet-bg-toggle-checkbox" name="elementor-gs[sheet_bg]" value="1" >
                                    <span class="slider round"></span>
                                </label>
                                <span class="label-text">
                                <?php esc_html_e( 'Sheet Background Color', 'gsheetconnector-for-elementor-forms' ); ?>
                                </span>

                      <span class="pro-ver">
                  <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
                   </span>

                                <span class="tooltip-new">
                                    <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                                    <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("Apply colors to the entire sheet, distinguishing odd and even rows for enhanced readability.", 'gsheetconnector-for-elementor-forms')); ?></span>
                                </span>
                            </div>
                    </div>

                    <!--Row Font Setings 4th div of header settigns  -->
                    <!-- FOR DATA OF FORM STYLES -->
                    <div class="sheet_formatting">  
                        <div class="elemgsc-sheet_formatting_row elemgsc-sheet_formatting_row">
                            <div class="toggle-button sheet_formatting-row-toggle">
                                <label class="switch" for="sheet_formatting-row-checkbox">
                                    <input type="checkbox" id="sheet_formatting-row-checkbox" name="elementor-gs[sheet_formatting_row]" value="1" >
                                    <span class="slider round"></span>
                                </label>
                                <span class="label-text">
                                <?php esc_html_e( 'Row - Font Settings', 'gsheetconnector-for-elementor-forms' ); ?>
                                 </span>

                        <span class="pro-ver">
                        <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
                        </span>

                                <span class="tooltip-new">
                                    <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon" alt="Help">
                                    <span class="tooltiptext tooltip-right-msg"><?php echo esc_html(__("This feature locks the top row in your selected sheet, providing a consistent reference point as you scroll through your data.", 'gsheetconnector-for-elementor-forms')); ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
            </div>
             
            <div class="header-manage">
        <?php

        global $wpdb;

        // Ensure form ID is passed
        $form_id = isset($_GET['form_id']) ? filter_var($_GET['form_id'], FILTER_SANITIZE_NUMBER_INT) : "";


        if ($form_id) {
            // Fetch the earliest entry date for the specific form ID
            $from_date = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT MIN(`created_at`) FROM {$wpdb->prefix}e_submissions WHERE `post_id` = %d",
                    $form_id
                )
            );

            // Fetch the latest entry date for the specific form ID
            $to_date = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT MAX(`created_at`) FROM {$wpdb->prefix}e_submissions WHERE `post_id` = %d",
                    $form_id
                )
            );
        } else {
            // Default to current date if no form ID or entries exist
            $from_date = $to_date = date('Y-m-d');
        }

        // Format the dates or set defaults if no entries exist
        $from_date = $from_date ? date('Y-m-d', strtotime($from_date)) : date('Y-m-d');
        $to_date = $to_date ? date('Y-m-d', strtotime($to_date)) : date('Y-m-d');
        ?>
				
				<br /><br />

        <h2>
            <?php echo esc_html(__('Sync form submissions with Google Sheets.', 'gsheetconnector-for-elementor-forms')); ?><span class="pro-ver">
    <?php esc_html_e( 'PRO', 'gsheetconnector-for-elementor-forms' ); ?>
     </span>

        </h2>

        <span class="sync-elemgsc-msg"></span>

        <div class="sync-date">

            <label for="sync-from-date">
            <?php esc_html_e( 'From Date', 'gsheetconnector-for-elementor-forms' ); ?>
           </label>

            <span class="tooltip">
                <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                <!-- Tooltip for From Date -->
                <span class="tooltiptext tooltip-right-msg">
                    <?php echo esc_html(__("Select the starting date for the sync. The From Date determines the lower limit of the date range.", "gsheetconnector-for-elementor-forms")); ?>
                </span>
            </span>
            <input type='date' id='sync-from-date' name='sync_from_date' value="<?php echo $from_date; ?>" min="<?php echo $from_date; ?>" max="<?php echo $to_date; ?>" class='wpgs-date-picker'>

            <label for='sync-to-date'><?php esc_html_e( 'To Date', 'gsheetconnector-for-elementor-forms' ); ?>
              </label>
            <span class="tooltip">
                <img src="<?php echo esc_url(GS_CONN_ELE_URL . 'assets/img/help.png'); ?>" class="help-icon">
                <!-- Tooltip for To Date -->
                <span class="tooltiptext tooltip-right-msg">
                    <?php echo esc_html(__("Select the ending date for the sync. The To Date determines the upper limit of the date range.", "gsheetconnector-for-elementor-forms")); ?>
                </span>
            </span>
            <input type='date' id='sync-to-date' name='sync_to_date' value="<?php echo $to_date; ?>" min="<?php echo $from_date; ?>" max="<?php echo $to_date; ?>" class='wpgs-date-picker'>

            <div class="sync_div_design syncronous_elemgsc_form_entry_gsheet">
                 <span>
        <?php esc_html_e( 'Sync Entries', 'gsheetconnector-for-elementor-forms' ); ?>
         </span>
    <span class="sync-elemgsc-load">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
     </div>

        </div> 

        <input type="hidden" id="form-id" value="<?php echo esc_attr($form_id); ?>">
        <input type="hidden" id="feed-id" value="<?php echo esc_attr($feed_id); ?>">

        <input type="hidden" 
       name="elementor-sync-gs-ajax-nonce" 
       id="elementor-sync-gs-ajax-nonce" 
       value="<?php echo esc_attr( wp_create_nonce( 'elementor-sync-gs-ajax-nonce' ) ); ?>" />

    
    <?php ?>

    <?php ?>
    </div> <!-- header management #end -->      
            
    </form>
     
<!-- popup file include herre -->
<?php include( GS_CONN_ELE_PATH . "includes/pages/pro-popup.php" ) ;?>