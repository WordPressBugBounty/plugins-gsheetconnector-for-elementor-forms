<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading admin page parameter.
$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'integration';

$active_tab_name = '';
if ($active_tab == 'settings') {
    $active_tab_name = 'Settings';
}
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading admin page parameter.
$sub_tab = isset($_GET['sub_tab']) ? sanitize_text_field(wp_unslash($_GET['sub_tab'])) : 'general_settings';
?>
<?php
switch ($active_tab) {
    case 'settings':
        // Render sub-navigation
    echo '<div class="gsc-settings-tabs d-flex gap-15 pl-15 pr-15">';
    $sub_tabs = array(
        'general_settings' => esc_html__('General Settings', 'gsheetconnector-for-elementor-forms'),
        'role_permissions' => esc_html__('Role Permissions', 'gsheetconnector-for-elementor-forms'),
        'version_control'  => esc_html__('Version Control', 'gsheetconnector-for-elementor-forms'),
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

    }

    echo '</div>';
}
?>
<!---Start Support  ticket--->

<div class="gsc-support-card mt-30 welcome-wrapper ml-15">

    <!-- LEFT SIDE -->
    <div class="gsc-support-left">

        <div class="gsc-support-icon d-flex justify-center align-center">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.8335 14V3.50004C19.8335 3.19062 19.7106 2.89388 19.4918 2.67508C19.273 2.45629 18.9762 2.33337 18.6668 2.33337H3.50016C3.19074 2.33337 2.894 2.45629 2.6752 2.67508C2.45641 2.89388 2.3335 3.19062 2.3335 3.50004V19.8334L7.00016 15.1667H18.6668C18.9762 15.1667 19.273 15.0438 19.4918 14.825C19.7106 14.6062 19.8335 14.3095 19.8335 14ZM24.5002 7.00004H22.1668V17.5H7.00016V19.8334C7.00016 20.1428 7.12308 20.4395 7.34187 20.6583C7.56066 20.8771 7.85741 21 8.16683 21H21.0002L25.6668 25.6667V8.16671C25.6668 7.85729 25.5439 7.56054 25.3251 7.34175C25.1063 7.12296 24.8096 7.00004 24.5002 7.00004Z" fill="#141B38"></path>
            </svg>
        </div>

        <div class="gsc-content">
            <div class="support-headings"><?php echo esc_html__('Need more support? We\'re here to help.', 'gsheetconnector-for-elementor-forms'); ?></div>

            <a href="https://wordpress.org/support/plugin/gsheetconnector-for-elementor-forms/" target="_blank" class="btn btn-primary mt-10 link-hover-white text-decoration-none">
                <?php echo esc_html__('Submit a Support Ticket', 'gsheetconnector-for-elementor-forms'); ?>
                <svg width="10" height="10" viewBox="0 0 6 8" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.66681 0L0.726807 0.94L3.78014 4L0.726807 7.06L1.66681 8L5.66681 4L1.66681 0Z"></path>
                </svg>
            </a>
        </div>

    </div>

    <!-- RIGHT SIDE -->
    <div class="gsc-support-right">

        <div class="gsc-avatars justify-center">
            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-2.jfif" alt="">
            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-3.png" alt="">
            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-5.jfif" alt="">
            <img src=" <?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-4.png" alt="">
            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar.jpeg" alt="">
        </div>

        <p class="text-center"><?php echo esc_html__('Our fast and friendly support team is always happy to help!', 'gsheetconnector-for-elementor-forms'); ?></p>

    </div>

</div>

<!---End Support  ticket--->