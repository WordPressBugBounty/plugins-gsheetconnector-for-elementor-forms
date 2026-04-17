<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('administrator')) {
    ?>
    <span class="per_not_allo"><?php echo esc_html__("Permission Not Allowed", 'gsheetconnector-for-elementor-forms'); ?></span>
    <?php
    return;
}
$allowed_accress_roles = array('administrator', 'editor', 'author','contributor');
$participating_roles = array();
$editable_roles = get_editable_roles();

foreach ($editable_roles as $role => $details) {

    if (in_array($role, $allowed_accress_roles)) {
        $participating_roles[$role] = $details['name'];
    }
}
?>

<!--Start Pro Setting(Roll Permissions)-->
<div class="gsc-pro-promo ml-15 mr-pro-15">

    <div class="gsc-pro-header">
        <div class="gsc-pro-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 19c-1 1-2 1-3 1 0-1 0-2 1-3l4-4"></path>
            <path d="M14 3l7 7"></path>
            <path d="M9 18l-4 4"></path>
            <path d="M15 3c2 0 6 4 6 6-2 2-6 6-8 8l-6-6c2-2 6-8 8-8z"></path>
            <circle cx="15" cy="9" r="1.5"></circle>
        </svg>

    </div>

    <div>
        <div class="unlock-header">
            <?php echo esc_html(__('Unlock Role-Based Access Control', 'gsheetconnector-for-elementor-forms')); ?>
        </div>
        <span
        class="gsc-pro-badge"><?php echo esc_html(__('FREE users get special upgrade pricing', 'gsheetconnector-for-elementor-forms')); ?></span>
    </div>
</div>

<!-- Feature Tabs -->
<div class="gsc-pro-tabs pt-20 pb-20 pl-20 pr-20">
    <div>
        <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
            <?php echo esc_html(__('Role Permissions', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Allow specific WordPress roles', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Enable/disable integration access', 'gsheetconnector-for-elementor-forms'); ?>
                </li>
                <li><?php esc_html_e('Control form feed visibility', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('Restrict settings management', 'gsheetconnector-for-elementor-forms'); ?></li>
            </ul>
        </div>
    </div>

    <div>
        <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
            <?php echo esc_html(__('Security Control', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div class="gsc-pro-grid">
                <ul>
                    <li><?php esc_html_e('Prevent unauthorized changes', 'gsheetconnector-for-elementor-forms'); ?></li>
                    <li><?php esc_html_e('Secure Google Sheet credentials', 'gsheetconnector-for-elementor-forms'); ?>
                </li>
                <li><?php esc_html_e('Role-based configuration control', 'gsheetconnector-for-elementor-forms'); ?>
            </li>
            <li><?php esc_html_e('Protect integration settings', 'gsheetconnector-for-elementor-forms'); ?></li>
        </ul>
    </div>
</div>

<div>
    <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
        <?php echo esc_html(__('Management Benefits', 'gsheetconnector-for-elementor-forms')); ?></div>
        <div class="gsc-pro-grid">
            <ul>
                <li><?php esc_html_e('Grant access to trusted editors', 'gsheetconnector-for-elementor-forms'); ?>
            </li>
            <li><?php esc_html_e('Hide settings from subscribers', 'gsheetconnector-for-elementor-forms'); ?></li>
            <li><?php esc_html_e('Team-based permission structure', 'gsheetconnector-for-elementor-forms'); ?>
        </li>
        <li><?php esc_html_e('Improved dashboard security', 'gsheetconnector-for-elementor-forms'); ?></li>
    </ul>
</div>
</div>

<div>
    <div class="mb-20 fw-600 text-dark pro-roll-sub-header">
        <?php echo esc_html(__('Audit & Monitoring', 'gsheetconnector-for-elementor-forms')); ?></div>
        <div class="gsc-pro-grid">
            <ul>
                <li><?php esc_html_e('Track role-based changes', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('Monitor integration access', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('Review permission updates', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php esc_html_e('Maintain admin accountability', 'gsheetconnector-for-elementor-forms'); ?></li>
            </ul>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="gsc-pro-footer text-center">
    <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank"
    class="btn btn-primary text-decoration-none link-hover-white">
    <?php echo esc_html(__('Upgrade to Unlock', 'gsheetconnector-for-elementor-forms')); ?>
</a>
</div>

</div>
<!--End Pro Setting(Roll Permissions)-->

<div class="gselef-role-settings" id="gsc-googlesheet">
    <div class="wrap w-100 m-0">
        <div class="inner-wrap w-100 bg-white p-40 blur-pro-feature">
            <div class="heading mt-0"><?php echo esc_html__('Access Control', 'gsheetconnector-for-elementor-forms'); ?>
        </div>
        <p><?php echo esc_html__('Control which user roles are allowed to access and manage the Google Sheets integration for your forms.', 'gsheetconnector-for-elementor-forms'); ?>
    </p>
    <div class="gselef_elementorform-card">
        <?php



        $selected_row = '';
        $selected_row .= "<div class='gsc-access-wrapper mt-30'>";
        $selected_row .= "<div class='gsc-access-box bg-white pt-15 pb-15 pl-15 pr-15'>
        <div class='para-heading fw-600 mb-20'>" . esc_html__('Allowed Roles', 'gsheetconnector-for-elementor-forms') . "</div>";


        $selected_row .= "<div class='gsc-role-card mb-10'>
        <div class='custom-check d-flex justify-between alien-center'>
        <label class='role-label gsc-switch'> ";
        $selected_row .= __("Administrator", 'gsheetconnector-for-elementor-forms');
        $selected_row .= "</label>";
        $selected_row .= "<input type='checkbox' class='check-toggle' disabled='disabled' checked='checked' /><label class='button-toggle'></label>";
        $selected_row .= "
        </div>";
        $selected_row .= "
        </div>";
        foreach ($participating_roles as $role => $display_name) {
            if ($role === "administrator") {
                continue;
            }
                        if (!empty($roles) && is_array($roles) && in_array(esc_attr($role), $roles)) { // preselect specified role
                            $checked = " checked='checked'";
                        } else {
                            $checked = '';
                        }

                        $selected_row .= "<div class='gsc-role-card mb-10'>
                        <div class='custom-check d-flex justify-between alien-center'><label class='role-label gsc-switch'>";
                        $selected_row .= esc_html($display_name, 'gsheetconnector-for-elementor-forms');
                        $selected_row .= "</label><input type='checkbox' class='' check-toggle'
                        name='" . esc_attr($role) . "[]' value='" . esc_attr($role) . "'" . $checked . " />";
                        $selected_row .= "<label class='button-toggle'></label>";
                        $selected_row .= "</div>
                        </div>";
                    }



                    echo wp_kses($selected_row, array(
                        'div' => array('class' => array()),
                        'label' => array('class' => array(), 'for' => array()),
                        'input' => array(
                            'type' => array(),
                            'class' => array(),
                            'name' => array(),
                            'value' => array(),
                            'checked' => array(),
                            'disabled' => array()
                        ),
                        'span' => array('class' => array())
                    ));
                    ?>
                </div>
                <div class="gsc-access-info">
                    <div class="para-heading fw-600 mb-20">
                        <?php echo esc_html__('Permission Guidelines', 'gsheetconnector-for-elementor-forms'); ?></div>
                        <ul>
                            <li><?php echo esc_html__('Grant access only to users you trust', 'gsheetconnector-for-elementor-forms'); ?>Grant
                            access only to users you trust</li>
                            <li><?php echo esc_html__('Editors can manage and modify integrations', 'gsheetconnector-for-elementor-forms'); ?>
                        </li>
                        <li><?php echo esc_html__('Review role permissions on a regular basis', 'gsheetconnector-for-elementor-forms'); ?>
                    </li>
                    <li><?php echo esc_html__('Do not allow access to subscribers', 'gsheetconnector-for-elementor-forms'); ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="">
        <div class="select-info text-right mt-30">
            <input type="submit" class="btn btn-primary button-large" name="gselef_elementorform_settings"
            value="<?php echo esc_html__("Save Settings", 'gsheetconnector-for-elementor-forms'); ?>" />
        </div>
    </div>
</div>
</div>
</div>
</div>