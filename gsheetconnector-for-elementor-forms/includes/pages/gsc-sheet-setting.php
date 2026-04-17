<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$authenticated = get_option('elefgs_token');
$per = get_option('elefgs_verify');
$show_setting = 0;
$selected_method = '';


$selected_method = esc_html(__('Existing', 'gsheetconnector-for-elementor-forms'));


if (!empty($authenticated) && $per == "valid") {

    $show_setting = 1;
} else { ?>
    <div class="wrap w-100 m-0">
        <div class="wrap w-100 m-0">
            <div class="inner-wrap w-100 bg-white p-40">
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
            </div>
        </div>
    <?php }

    if ($show_setting == 1) {


        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'integration';


        $active_tab_name = '';
        if ($active_tab == 'settings') {
            $active_tab_name = 'Settings';
        }
        $sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field(wp_unslash($_GET['sub_tab'])) : 'general_settings';


        ?>

        <?php
        switch ($active_tab) {
            case 'settings':
            // Render sub-navigation
            echo '<div class="gsc-settings-tabs d-flex gap-15 pl-15 pr-15">';
            $sub_tabs = array(
                'general_settings' => __('General Settings', 'gsheetconnector-for-elementor-forms'),
                'role_permissions' => __('Role Permissions', 'gsheetconnector-for-elementor-forms'),
                'version_control'  => __('Version Control', 'gsheetconnector-for-elementor-forms'),
                'system_status'    => __('System Status', 'gsheetconnector-for-elementor-forms'),
            );



            foreach ($sub_tabs as $sub => $label) {
                $class = ($sub === $sub_tab) ? 'gsc-tab active' : 'gsc-tab';
                echo '<a class="' . esc_attr($class) . '" href="' . esc_url(admin_url('admin.php?page=gsheetconnector-elementor-config&tab=settings&sub_tab=' . urlencode($sub))) . '">' . esc_html($label) . '</a>';
            }
            echo '</div> <div class="gsc-settings-card">';

            // Load correct sub-tab content
            switch ($sub_tab) {
                case 'general_settings':
                include(GS_CONN_ELE_PATH . "includes/pages/gsc-general-setting.php");
                break;

                case 'role_permissions':
                include(GS_CONN_ELE_PATH . "includes/pages/gsc-role-setting.php");
                break;


                case 'version_control':
                include(GS_CONN_ELE_PATH . "includes/pages/gsc-beta-version.php");
                break;


                case 'system_status':
                include(GS_CONN_ELE_PATH . "includes/pages/gsc-system-status.php");
                break;
            }

            echo '</div>';

            echo '
            <div class="gsc-support-card d-flex justify-between flex-wrap gap-20 align-center bg-white">

            <div class="gsc-support-left d-flex align-center gap-20">

            <div class="gsc-support-icon d-flex justify-center align-center">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19.8335 14V3.50004C19.8335 3.19062 19.7106 2.89388 19.4918 2.67508C19.273 2.45629 18.9762 2.33337 18.6668 2.33337H3.50016C3.19074 2.33337 2.894 2.45629 2.6752 2.67508C2.45641 2.89388 2.3335 3.19062 2.3335 3.50004V19.8334L7.00016 15.1667H18.6668C18.9762 15.1667 19.273 15.0438 19.4918 14.825C19.7106 14.6062 19.8335 14.3095 19.8335 14ZM24.5002 7.00004H22.1668V17.5H7.00016V19.8334C7.00016 20.1428 7.12308 20.4395 7.34187 20.6583C7.56066 20.8771 7.85741 21 8.16683 21H21.0002L25.6668 25.6667V8.16671C25.6668 7.85729 25.5439 7.56054 25.3251 7.34175C25.1063 7.12296 24.8096 7.00004 24.5002 7.00004Z" fill="#141B38"></path></svg>
            </div>

            <div class="gsc-support-text">
            <div class="support-header">' . esc_html__('Need more support? We’re here to help.', 'gsheetconnector-for-elementor-forms') . '</div>

            <p>
            ' . esc_html__('Our support team is ready to assist you with setup, troubleshooting, and advanced configuration.', 'gsheetconnector-for-elementor-forms') . '
            </p>
            </div>

            </div>

            <div class="gsc-support-right">

            <a href="https://www.gsheetconnector.com/support"
            target="_blank"
            class="btn btn-primary text-decoration-none link-hover-white">

            ' . esc_html__('Submit Support Ticket', 'gsheetconnector-for-elementor-forms') . '

            </a>

            </div>

            </div>';
            break;
        }
    }
