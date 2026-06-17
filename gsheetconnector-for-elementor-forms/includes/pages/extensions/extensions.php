<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals
?>
<div class="wrap w-100 m-0">
    <div class="inner-wrap  w-100 bg-white p-40">
        <!-- Start About Section -->
        <div class="gsc-about-wrapper animated-border">

            <!-- Hero -->
            <div class="gsc-about-hero">

                <div class="gsc-about-hero-content">

                    <div class="about-us-heading fw-500 mb-20 text-center">
                        <?php esc_html_e('About GSheetConnector', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>
                    <div class="about-us-subheading fw-300 mb-20 text-center">
                        <?php esc_html_e('Built by Experts. Trusted by Thousands.', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>



                    <p class="text-dark about-us-descs text-center mb-10">
                        <?php esc_html_e(
                            'Led by experienced developers and powered by a passionate team, GSheetConnector helps WordPress users automate their workflows with confidence.',
                            'gsheetconnector-for-elementor-forms'
                        ); ?>
                    </p>

                    <p class="text-dark about-us-descs text-center">
                        <?php esc_html_e(
                            'We focus on building tools that are fast, secure, and easy to use - so you can spend less time managing data.',
                            'gsheetconnector-for-elementor-forms'
                        ); ?>
                    </p>

                    <div class="gsc-connection-box flex-wrap gsc-founder-card mt-30 justify-between">
                        <div class="d-flex flex-wrap align-center gap-10 responsive-center">
                            <div>
                                <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/founder.webp" alt="Founder">
                            </div>
                            <div class="responsive-text-center">
                                <p class="gsc-name">Wordpress Plugin Founder</p>
                                <p class="gsc-role mb-0">Product Lead &amp; Visionary</p>
                            </div>
                        </div>
                        <div class="gs-signature text-center">
                            <div class="founder-heading fw-100 text-dark mb-10">Abdullah Kaludi</div>
                            <span>Founder &amp; Lead Developer</span>
                        </div>
                    </div>
                    <div class="heading mb-20 mt-20 text-center">
                        <?php esc_html_e('Meet Our Core Team', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>
                    <!-- Team Avatars -->
                    <div class="gsc-about-avatars d-flex align-center flex-wrap gap-10 mt-30 justify-center">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-2.jfif" alt="">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-3.png" alt="">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-5.jfif" alt="">
                        <img src=" <?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar-4.png" alt="">
                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/avatar.jpeg" alt="">
                    </div>

                </div>

            </div>


            <!-- Content -->
            <div class=" gsc-about-content">


                <!-- Left -->
                <div class="gsc-about-text">

                    <div class="heading mt-0">
                        <?php esc_html_e('Our Mission', 'gsheetconnector-for-elementor-forms'); ?>
                    </div>

                    <p>
                        <?php esc_html_e(
                            'Our mission is to make form automation simple, secure, and reliable for every WordPress user - from beginners to enterprise teams.',
                            'gsheetconnector-for-elementor-forms'
                        ); ?>
                    </p>


                    <ul class="gsc-about-list">

                        <li><?php esc_html_e('Fast & Reliable Data Sync', 'gsheetconnector-for-elementor-forms'); ?></li>

                        <li><?php esc_html_e('100% Secure Authentication', 'gsheetconnector-for-elementor-forms'); ?></li>

                        <li><?php esc_html_e('Real-Time Google Sheets Updates', 'gsheetconnector-for-elementor-forms'); ?></li>

                        <li><?php esc_html_e('Easy Setup & Configuration', 'gsheetconnector-for-elementor-forms'); ?></li>

                        <li><?php esc_html_e('Dedicated Support Team', 'gsheetconnector-for-elementor-forms'); ?></li>


                    </ul>

                </div>


                <!-- Right -->
                <div class="gsc-about-stats">

                    <div class="gsc-stat-card d-flex align-center justify-center bg-white text-white">
                        <div class="gsc-stat-card-header">
                            <span class="elementor-free-counter" data-count="11">0</span><span class="suffix">+</span>
                        </div>
                        <span><?php esc_html_e('Forms Successfully Synced', 'gsheetconnector-for-elementor-forms'); ?></span>
                    </div>

                    <div class="gsc-stat-card d-flex align-center justify-center bg-white text-white">
                        <div class="gsc-stat-card-header">
                            <span class="elementor-free-counter" data-count="50">0</span><span class="suffix">+</span>
                        </div>
                        <span><?php esc_html_e('Trusted by Active Users', 'gsheetconnector-for-elementor-forms'); ?></span>
                    </div>

                    <div class="gsc-stat-card d-flex align-center justify-center bg-white text-white">
                        <div class="gsc-stat-card-header">
                            <span class="elementor-free-counter" data-count="99.9">0</span><span class="suffix">%</span>
                        </div>
                        <span><?php esc_html_e('Reliable Uptime', 'gsheetconnector-for-elementor-forms'); ?></span>
                    </div>

                    <div class="gsc-stat-card d-flex align-center justify-center bg-white text-white">
                        <div class="gsc-stat-card-header">
                            <span class="elementor-free-counter" data-count="24">0</span><span class="suffix">/7</span>
                        </div>
                        <span><?php esc_html_e('Dedicated Support', 'gsheetconnector-for-elementor-forms'); ?></span>
                    </div>

                </div>

            </div>

            <div class="extensions-list">
                <div class="heading mt-40"><?php esc_html_e('Add-ons & Extensions', 'gsheetconnector-for-elementor-forms'); ?></div>
                <p><?php echo esc_html__('Extend your Google Sheets integration with powerful add-ons. Install, manage, and activate extensions to unlock advanced features.', 'gsheetconnector-for-elementor-forms'); ?></p>

                <?php
                $all_plugins = get_plugins();
                $active_theme = wp_get_theme();
                $plugins = [
                    'contact-form-7/wp-contact-form-7.php' => [
                        'connector' => 'cf7-google-sheets-connector/google-sheet-connector.php',
                        'connector-pro' => 'cf7-google-sheets-connector-pro/google-sheet-connector-pro.php',

                        'name' => __('CF7 Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),

                        'link' => 'https://www.gsheetconnector.com/cf7-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-cf7-gsc.webp',

                        'text' => __('Easily connect Contact Form 7 to Google Sheets and automatically sync form submissions in real-time. Every entry is instantly added to your selected spreadsheet, helping you manage leads, inquiries, and customer data efficiently without manual export.', 'gsheetconnector-for-elementor-forms'),

                        'pro_plugin_active' => 'cf7-google-sheets-connector-pro/google-sheet-connector-pro.php',
                        'url' => 'https://wordpress.org/plugins/cf7-google-sheets-connector/',

                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge'  => __('PRO', 'gsheetconnector-for-elementor-forms'),

                        'freeLink' => 'https://wordpress.org/plugins/cf7-google-sheets-connector/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/cf7-google-sheets-connector.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/cf7-google-sheet-connector-pro',
                        'mainPlugin' => 'contact-form-7/wp-contact-form-7.php',
                        'theme' => 'contact-form-7',
                        'docs' => 'https://www.gsheetconnector.com/docs/cf7-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/cf7-google-sheet-connector-pro'
                    ],
                    'wpforms-lite/wpforms.php' => [
                        'connector' => 'gsheetconnector-wpforms/gsheetconnector-wpforms.php',
                        'connector-pro' => 'gsheetconnector-wpforms-pro/gsheetconnector-wpforms-pro.php',
                        'name' => __('WPForms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/wpforms-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-wpf-gsc.webp',
                        'text' => __('Integrate WPForms with Google Sheets and send form entries directly to your spreadsheet automatically. Track leads, manage customer data, and streamline reporting with seamless Google Sheets integration and real-time sync.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-wpform-pro/gsheetconnector-wpforms-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-wpforms/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-wpforms/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-wpforms.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/wpforms-google-sheet-connector-pro',
                        'mainPlugin' => ['wpforms-lite/wpforms.php', 'wpforms/wpforms.php'],
                        'theme' => 'wpforms',
                        'docs' => 'https://www.gsheetconnector.com/docs/gsheetconnnector-for-wpforms',
                        'demo' => 'https://demo.gsheetconnector.com/wpforms-google-sheet-connector-pro'
                    ],

                    'gravityforms/gravityforms.php' => [
                        'connector' => 'gsheetconnector-gravity-forms/gsheetconnector-gravityforms.php',
                        'connector-pro' => 'gsheetconnector-gravity-forms-pro/gsheetconnector-gravity-forms-pro.php',
                        'name' => __('Gravity Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/gravity-forms-google-sheet-connector',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-gravity-gsc.webp',
                        'text' => __('Connect Gravity Forms to Google Sheets and automatically push form submissions to your spreadsheet. Simplify data management, improve workflow automation, and keep all entries organized in one secure location.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-gravity-forms-pro/gsheetconnector-gravity-forms-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-gravity-forms/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-gravity-forms/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-gravity-forms.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/gravity-forms-google-sheet-connector',
                        'mainPlugin' => 'gravityforms/gravityforms.php',
                        'theme' => 'gravityforms',
                        'docs' => 'https://www.gsheetconnector.com/docs/gravity-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/gravityforms-google-sheet-connector-pro'
                    ],

                    'ninja-forms/ninja-forms.php' => [
                        'connector' => 'gsheetconnector-ninja-forms/gsheetconnector-ninjaforms.php',
                        'connector-pro' => 'gsheetconnector-ninja-forms-pro/gsheetconnector-ninjaform-pro.php',
                        'name' => __('Ninja Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/ninja-forms-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-ninjaf-gsc.webp',
                        'text' => __('Automatically sync Ninja Forms submissions to Google Sheets with one-click integration. Capture leads, monitor responses, and manage data efficiently with instant spreadsheet updates and easy reporting.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-ninja-forms-pro/gsheetconnector-ninjaforms-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-ninjaforms/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-ninja-forms/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-ninja-forms.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/ninja-forms-google-sheet-connector',
                        'mainPlugin' => 'ninja-forms/ninja-forms.php',
                        'theme' => 'ninja-forms',
                        'docs' => 'https://gsheetconnector.com/docs/ninja-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/ninjaforms-google-sheet-connector-pro'
                    ],

                    'forminator/forminator.php' => [
                        'connector' => 'gsheetconnector-forminator/gsheetconnector-forminator.php',
                        'connector-pro' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
                        'name' => __('Forminator Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/forminator-addon.webp',
                        'text' => __('Integrate Forminator Forms with Google Sheets to automatically store form entries in your spreadsheet. Eliminate manual data entry and ensure accurate, real-time syncing for better data tracking and analysis.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('Free', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-forminator.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro',
                        'mainPlugin' => 'forminator/forminator.php',
                        'theme' => 'forminator',
                        'docs' => 'https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/forminator-google-sheet-connector'
                    ],
                    'formidable/formidable.php' => [
                        'connector' => 'gsheetconnector-formidable-forms/gsheetconnector-formidable-forms.php',
                        'connector-pro' => 'gsheetconnector-formidable-forms-pro/gsheetconnector-formidable-forms-pro.php',
                        'name' => __('Formidable Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/formidable-addon.webp',
                        'text' => __('Automatically sync Formidable Forms entries to Google Sheets. Manage leads, track submissions, and simplify data reporting with real-time integration.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('Free', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-formidable-forms.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/formidable-forms-google-sheet-connector-pro',
                        'mainPlugin' => 'formidable/formidable.php',
                        'theme' => 'formidable',
                        'docs' => 'https://www.gsheetconnector.com/docs/formidable-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/formidable-forms-google-sheet-connector'
                    ],

                    'fluentform/fluentform.php' => [
                        'connector' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'connector-pro' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'name' => __('Fluent Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/fluent-forms-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-fluent-forms-gsc.webp',
                        'text' => __('Automatically sync Fluent Forms submissions to Google Sheets in real-time. Every form entry is instantly added to your selected spreadsheet, helping you manage leads, customer inquiries, and business data without manual export. Streamline your workflow, improve reporting accuracy, and keep all submissions organized with seamless Google Sheets integration.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'url' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('Free', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
                        'downloadLink' => '"https://downloads.wordpress.org/plugin/wc-gsheetconnector.1.0.8.zip"',
                        'buyLink' => 'https://www.gsheetconnector.com/fluent-forms-google-sheet-connector-pro',
                        'mainPlugin' => 'fluentform/fluentform.php',
                        'theme' => '',
                        'docs' => 'https://www.gsheetconnector.com/docs/fluent-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/fluent-gsheet-connector'
                    ],

                    'pro-elements/pro-elements.php' => [
                        'connector' => 'gsheetconnector-for-elementor-forms/gsheetconnector-for-elementor-forms.php',
                        'connector-pro' => 'gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php',
                        'name' => __('Elementor Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-elemntor-gsc.webp',
                        'text' => __('Sync Elementor Forms with Google Sheets instantly. Automatically add new form submissions to your spreadsheet, manage leads efficiently, and simplify reporting with real-time data synchronization.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-for-elementor-forms.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro',
                        'mainPlugin' => ['pro-elements/pro-elements.php', 'elementor-pro/elementor-pro.php'],
                        'theme' => 'elements',
                        'docs' => 'https://www.gsheetconnector.com/docs/elementor-google-sheet-connector',
                        'demo' => 'https://demo.gsheetconnector.com/elementor-forms-gsheetconnector-pro'
                    ],
                    'avada/avada.php' => [
                        'connector' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
                        'connector-pro' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
                        'name' => __('Avada Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/avada-forms-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-avada-gsc.webp',
                        'text' => __('Connect Avada Forms to Google Sheets and automatically transfer form entries to your spreadsheet. Streamline lead management, automate data collection, and improve business reporting with seamless integration.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
                        'url' => '',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('Available PRO Version', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => '',
                        'downloadLink' => '',
                        'buyLink' => 'https://www.gsheetconnector.com/avada-forms-google-sheet-connector-pro',
                        'mainPlugin' => 'avada',
                        'theme' => 'Avada',
                        'docs' => 'https://www.gsheetconnector.com/docs/avada-forms-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/avadaforms-google-sheet-connector-pro'
                    ],

                    'divi/divi.php' => [
                        'connector' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
                        'connector-pro' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
                        'name' => __('Divi Forms Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/divi-forms-db-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-divi-gsc.webp',
                        'text' => __('Integrate Divi Forms with Google Sheets and automatically sync submissions in real-time. Store customer data securely, track inquiries efficiently, and eliminate manual spreadsheet updates.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
                        'url' => '',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('Available PRO Version', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => '',
                        'downloadLink' => '',
                        'buyLink' => 'https://www.gsheetconnector.com/divi-forms-db-google-sheet-connector-pro',
                        'mainPlugin' => 'Divi',
                        'theme' => 'Divi',
                        'docs' => 'https://www.gsheetconnector.com/docs/divi-forms-google-sheet-connector',
                        'demo' => 'https://demo.gsheetconnector.com/divi-forms-gsheetconnector-pro'
                    ],

                    'gsheetconnector-for-wp-core/gsheetconnector-for-wp-core.php' => [
                        'connector' => 'gsheetconnector-easy-digital-downloads/gsheetconnector-easy-digital-downloads.php',
                        'connector-pro' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
                        'name' => __('WP Core Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/gsheetconnector-for-wp-core',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/gsc-wp-core.webp',
                        'text' => __('Automatically send WordPress core data (like comments or custom submissions) to Google Sheets. Simplify website data tracking, automate record keeping, and centralize important information in one organized spreadsheet.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => '',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-easy-digital-downloads.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/gsheetconnector-for-wp-core',
                        'mainPlugin' => 'gsheetconnector-for-wp-core',
                        'theme' => 'gsheetconnector-for-wp-core',
                        'docs' => 'https://www.gsheetconnector.com/docs/gsheetconnector-for-wp-core',
                        'demo' => ''
                    ],

                    'woocommerce/woocommerce.php' => [
                        'connector' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'connector-pro' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'name' => __('WooCommerce Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-woo-gsc.webp',
                        'text' => __('Sync WooCommerce orders with Google Sheets automatically. Store order details, customer information, and transaction data in real-time for easy reporting, accounting, and business analysis.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'wc-gsheetconnector/wc-gsheetconnector.php',
                        'url' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/wc-gsheetconnector.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro',
                        'mainPlugin' => 'woocommerce/woocommerce.php',
                        'theme' => 'woocommerce',
                        'docs' => 'https://www.gsheetconnector.com/docs/woocommerce-gsheetconnector',
                        'demo' => 'https://woogsheets.gsheetconnector.com'
                    ],

                    'easy-digital-downloads/easy-digital-downloads.php' => [
                        'connector' => 'gsheetconnector-easy-digital-downloads/gsheetconnector-easy-digital-downloads.php',
                        'connector-pro' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
                        'name' => __('Easy Digital Downloads Google Sheet Connector', 'gsheetconnector-for-elementor-forms'),
                        'link' => 'https://www.gsheetconnector.com/easy-digital-downloads-google-sheet-connector-pro',
                        'img' => esc_url(GS_CONN_ELE_URL) . '/assets/img/pro-edd-gsc.webp',
                        'text' => __('Automatically send Easy Digital Downloads (EDD) order details to Google Sheets. Track digital product sales, monitor transactions, and manage customer data effortlessly with real-time spreadsheet integration.', 'gsheetconnector-for-elementor-forms'),
                        'pro_plugin_active' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
                        'url' => 'https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/',
                        'button' => __('Install', 'gsheetconnector-for-elementor-forms'),
                        'badge' => __('PRO', 'gsheetconnector-for-elementor-forms'),
                        'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/',
                        'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-easy-digital-downloads.zip',
                        'buyLink' => 'https://www.gsheetconnector.com/edd-google-sheet-connector-pro',
                        'mainPlugin' => 'easy-digital-downloads/easy-digital-downloads.php',
                        'theme' => 'easy-digital-downloads',
                        'docs' => 'https://www.gsheetconnector.com/docs/edd-gsheetconnector',
                        'demo' => 'https://demo.gsheetconnector.com/easydigitaldownloads-google-sheet-connector-pro'
                    ],
                ];
                ?>
                <div class="gsc-market-header">
                    <div class="extensions-sub-heading"><?php echo esc_html(__('Installed & Active Plugins', 'gsheetconnector-for-elementor-forms')); ?></div>
                    <div>
                        <p><?php echo esc_html(__('View and manage all currently installed and active connectors on your site. You can activate, deactivate, or upgrade plugins from here.', 'gsheetconnector-for-elementor-forms')); ?>
                    </p>
                </div>
            </div>
            <div class="gsheetconnector-addons-list gsc-ext-grid">

                <?php
                foreach ($plugins as $plugin => $details) {

                        // =========================
                        // MAIN PLUGIN CHECK
                        // =========================
                    $is_main_installed = false;
                    $is_main_active    = false;

                    if (!empty($details['mainPlugin']) && is_array($details['mainPlugin'])) {

                        foreach ($details['mainPlugin'] as $main_plugin) {

                            if (isset($all_plugins[$main_plugin])) {
                                $is_main_installed = true;
                            }

                            if (is_plugin_active($main_plugin)) {
                                $is_main_active = true;
                                break;
                            }
                        }
                    } elseif (!empty($details['mainPlugin']) && is_string($details['mainPlugin'])) {

                        $is_main_installed = isset($all_plugins[$details['mainPlugin']]);

                        $is_main_active = is_plugin_active($details['mainPlugin']);
                    }

                        // =========================
                        // FREE / PRO STATUS
                        // =========================
                    $is_pro_installed = !empty($details['pro_plugin_active']) &&
                    isset($all_plugins[$details['pro_plugin_active']]);

                    $is_pro_active = !empty($details['pro_plugin_active']) &&
                    is_plugin_active($details['pro_plugin_active']);

                    $is_free_installed = !empty($details['connector']) &&
                    isset($all_plugins[$details['connector']]);

                    $is_free_active = !empty($details['connector']) &&
                    is_plugin_active($details['connector']);

                        // =========================
                        // MAIN INACTIVE → HIDE
                        // =========================
                    if (!$is_main_active) {
                        continue;
                    }

                        // =========================
                        // PRO ACTIVE → SHOW PRO
                        // =========================
                    if ($is_pro_active) {
                        ?>

                        <div class="gsheetconnector-list-item gsc-ext-card">

                            <div class="addon-item-header">
                                <div class="extension-bg-color-set">
                                    <div class="gsc-ext-top">
                                        <a href="<?php echo esc_url($details['link']); ?>" target="_blank">
                                            <img src="<?php echo esc_url($details['img']); ?>"
                                            alt="<?php echo esc_attr($details['name']); ?>"
                                            class="gsc-ext-icon">
                                        </a>
                                        <div class="plugin-premium gsc-ext-badge pro-badge">
                                            <?php esc_html_e('Pro', 'gsheetconnector-for-elementor-forms'); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="addon-item-header-meta">

                                    <div class="addon-item-meta-title">

                                        <a href="<?php echo esc_url($details['link']); ?>"
                                            target="_blank"
                                            class="addon-link">

                                            <?php echo esc_html($details['name']); ?>

                                        </a>

                                    </div>

                                    <div class="addon-item-header-meta-excerpt">

                                        <?php echo esc_html($details['text']); ?>

                                    </div>

                                    <div class="activated gsc-ext-actions">

                                        <a href="<?php echo esc_url($details['docs']); ?>"
                                            target="_blank"
                                            class="addon-link btn btn-default link-hover-white">

                                            <?php esc_attr_e('View Docs', 'gsheetconnector-for-elementor-forms'); ?>

                                        </a>

                                        <a
                                        class="btn deactivate-btn gselef-deactivate-plugin"
                                        data-download="<?php echo esc_attr($details['connector-pro']); ?>"
                                        data-plugin="<?php echo esc_attr($details['connector-pro']); ?>">

                                        <?php esc_html_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>

                                    </a>

                                </div>

                            </div>

                        </div>
                    </div>

                    <?php

                            // =========================
                            // FREE ACTIVE → SHOW FREE
                            // =========================
                } elseif ($is_free_active) {
                    ?>

                    <div class="gsheetconnector-list-item gsc-ext-card">

                        <div class="addon-item-header">
                            <div class="extension-bg-color-set">
                                <div class="gsc-ext-top">

                                    <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank">

                                        <img src="<?php echo esc_url($details['img']); ?>"
                                        alt="<?php echo esc_attr($details['name']); ?>"
                                        class="gsc-ext-icon">

                                    </a>

                                    <div class="plugin-free gsc-ext-badge free-green">
                                        <?php esc_html_e('Free', 'gsheetconnector-for-elementor-forms'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="addon-item-header-meta">

                                <div class="addon-item-meta-title">

                                    <a href="<?php echo esc_url($details['buyLink']); ?>"
                                        target="_blank"
                                        class="addon-link">

                                        <?php echo esc_html($details['name']); ?>

                                    </a>

                                </div>

                                <div class="addon-item-header-meta-excerpt">

                                    <?php echo esc_html($details['text']); ?>

                                </div>

                                <div class="activated gsc-ext-actions">

                                    <a href="<?php echo esc_url($details['docs']); ?>"
                                        target="_blank"
                                        class="addon-link btn btn-default link-hover-white">

                                        <?php esc_attr_e('View Docs', 'gsheetconnector-for-elementor-forms'); ?>

                                    </a>

                                    <a href="javascript:void(0);"
                                    class="btn deactivate-btn gselef-deactivate-plugin"
                                    data-download="<?php echo esc_attr($details['connector']); ?>"
                                    data-plugin="<?php echo esc_attr($details['connector']); ?>">

                                    <?php esc_html_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

                <?php
            }
        }
        ?>

    </div>

    <!-- Second section -->
    <div class="gsc-market-header">
        <div class="extensions-sub-heading"><?php echo esc_html(__('Recommended Plugins', 'gsheetconnector-for-elementor-forms')); ?></div>
        <div>
            <p><?php echo esc_html(__('Discover recommended plugins that enhance your workflow and help you get more value from your Google Sheets integration.', 'gsheetconnector-for-elementor-forms')); ?>
        </p>
    </div>
</div>
<div class="gsheetconnector-addons-list gsc-ext-grid">
    <?php foreach ($plugins as $plugin => $data):
        $is_main_active = false;
        if (!empty($data['mainPlugin'])) {
            $main_plugins = is_array($data['mainPlugin']) ? $data['mainPlugin'] : [$data['mainPlugin']];
            foreach ($main_plugins as $main_plugin) {
                if (is_plugin_active($main_plugin)) {
                    $is_main_active = true;
                    break;
                }
            }
        }
        $active_theme      = wp_get_theme();
        $active_theme_slug = $active_theme->get_stylesheet();
        $is_theme_match    = ($active_theme_slug === $data['theme']);
        $is_pro_installed  = !empty($data['pro_plugin_active']) && isset($all_plugins[$data['pro_plugin_active']]);
        $is_pro_active     = !empty($data['pro_plugin_active']) && is_plugin_active($data['pro_plugin_active']);
        $is_free_installed = !empty($data['connector']) && isset($all_plugins[$data['connector']]);
        $is_free_active    = !empty($data['connector']) && is_plugin_active($data['connector']);
        if (!$is_theme_match) {
            if (!$is_main_active)                        continue;
            if ($is_pro_active)                          continue;
            if ($is_free_active && !$is_pro_installed)   continue;
        }
        ?>

        <?php if ($is_theme_match): ?>
            <div class="gsheetconnector-list-item gsc-ext-card">
                <div class="addon-item-header">
                    <div class="extension-bg-color-set">
                        <a href="<?php echo esc_url($data['buyLink']); ?>" target="_blank">
                            <img src="<?php echo esc_url($data['img']); ?>" class="gsc-ext-icon"
                            alt="<?php echo esc_attr($data['name']); ?>">
                        </a>
                    </div>
                    <div class="addon-item-header-meta">
                        <div class="plugin-premium gsc-ext-badge pro-badge">
                            <?php echo esc_html(__('Pro', 'gsheetconnector-for-elementor-forms')); ?>
                        </div>
                        <div class="addon-item-meta-title">
                            <a href="<?php echo esc_url($data['buyLink']); ?>" target="_blank" class="addon-link">
                                <?php echo esc_html($data['name']); ?>
                            </a>
                        </div>
                        <div class="addon-item-header-meta-excerpt">
                            <?php echo esc_html($data['text']); ?>
                        </div>

                        <div class="addon-item-footer">
                            <div class="button-bar">
                                <div class="addon-item-meta-title gsc-ext-actions">
                                    <a href="<?php echo esc_url($data['link']); ?>" target="_blank" class="btn activate">
                                        <?php echo esc_html($data['button']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php else: ?>
                <div class="gsheetconnector-list-item gsc-ext-card">
                    <div class="addon-item-header">
                        <div class="extension-bg-color-set">
                            <div class="gsc-ext-top">
                                <a href="<?php echo esc_url($data['url']); ?>" target="_blank">
                                    <img src="<?php echo esc_url($data['img']); ?>"
                                    alt="<?php echo esc_attr($data['name']); ?>"
                                    class="gsc-ext-icon">
                                </a>
                                <?php if ($is_pro_installed && !$is_pro_active): ?>
                                    <div class="plugin-premium gsc-ext-badge pro-badge">
                                        <?php echo esc_html(__('Pro', 'gsheetconnector-for-elementor-forms')); ?>
                                    </div>
                                    <?php else: ?>
                                        <div class="plugin-free gsc-ext-badge free-green">
                                            <?php echo esc_html(__('Free', 'gsheetconnector-for-elementor-forms')); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="addon-item-header-meta">
                                <div class="addon-item-meta-title">
                                    <a href="<?php echo esc_url($data['url']); ?>" target="_blank" class="addon-link">
                                        <?php echo esc_html($data['name']); ?>
                                    </a>
                                </div>
                                <div class="addon-item-header-meta-excerpt">
                                    <?php echo esc_html($data['text']); ?>
                                </div>

                                <div class="addon-item-footer">
                                    <div class="button-bar gsc-ext-actions">
                                        <a href="<?php echo esc_url($data['docs']); ?>" target="_blank"
                                            class="btn btn-default link-hover-white"><?php echo esc_html(__('View Docs', 'gsheetconnector-for-elementor-forms')); ?>
                                        </a>
                                        <?php if ($is_pro_installed && !$is_pro_active): ?>
                                            <button class="gselef-activate-plugin-btn btn activate button-pro"
                                            data-plugin="<?php echo esc_attr($data['pro_plugin_active']); ?>">
                                            <?php esc_html_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                        </button>
                                        <span class="loading-sign-active mt-25"></span>
                                        <?php elseif ($is_free_installed && !$is_free_active): ?>
                                            <button class="gselef-activate-plugin-btn btn activate button-free"
                                            data-plugin="<?php echo esc_attr($data['connector']); ?>">
                                            <?php esc_html_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                        </button>
                                        <span class="loading-sign-active mt-25"></span>
                                        <?php else: ?>
                                            <button class="gselef-install-plugin-btn btn btn-default"
                                            data-download="<?php echo esc_url($data['downloadLink']); ?>"
                                            data-plugin="<?php echo esc_attr($plugin); ?>">
                                            <?php echo esc_html($data['button']); ?>
                                        </button>
                                        <span class="loading-sign-install mt-25"></span>
                                        <button class="gselef-activate-plugin-btn btn activate button-free"
                                        data-plugin="<?php echo esc_attr($data['connector']); ?>"
                                        style="display:none;">
                                        <?php esc_html_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                    </button>
                                    <span class="loading-sign-active mt-25"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>


<!-- 3th section-->
<!--NEW-->

<div class="gsc-marketplace">

    <div class="gsc-market-header">
        <div>
            <div class="extensions-sub-heading"><?php echo esc_html(__('Explore More Plugins', 'gsheetconnector-for-elementor-forms')); ?></div>
            <div>
                <p class="mt-15 mb-0"><?php echo esc_html(__('Discover the right connector for your workflow and automate data syncing.', 'gsheetconnector-for-elementor-forms')); ?>
            </p>
        </div>
    </div>

    <div class="gsc-market-tabs d-flex flex-wrap gap-10 pt-15 pb-15 pl-15 pr-15">

        <button class="market-tab active fw-600" data-filter="all"><?php echo esc_html(__('All', 'gsheetconnector-for-elementor-forms')); ?></button>
        <button class="market-tab fw-600" data-filter="forms"><?php echo esc_html(__('Forms', 'gsheetconnector-for-elementor-forms')); ?></button>
        <button class="market-tab fw-600" data-filter="builders"><?php echo esc_html(__('Page Builders', 'gsheetconnector-for-elementor-forms')); ?></button>
        <button class="market-tab fw-600" data-filter="shop"><?php echo esc_html(__('eCommerce', 'gsheetconnector-for-elementor-forms')); ?></button>

    </div>

</div>

<div class="gsheetconnector-addons-list gsc-ext-grid">

    <?php foreach ($plugins as $plugin => $data):

                            // ---------------------------------
                            // MAIN PLUGIN ACTIVE CHECK
                            // ---------------------------------
        $is_main_active = false;

        if (!empty($data['mainPlugin'])) {
            if (is_array($data['mainPlugin'])) {
                foreach ($data['mainPlugin'] as $main_plugin) {
                    if (is_plugin_active($main_plugin)) {
                        $is_main_active = true;
                        break;
                    }
                }
            } elseif (is_string($data['mainPlugin'])) {
                $is_main_active = is_plugin_active($data['mainPlugin']);
            }
        }

                            // ---------------------------------
                            // FREE / PRO INSTALLED CHECK
                            // ---------------------------------
        $is_free_installed = !empty($data['connector'])
        && isset($all_plugins[$data['connector']]);

        $is_pro_installed  = !empty($data['pro_plugin_active'])
        && isset($all_plugins[$data['pro_plugin_active']]);

                            /**
                             *  HIDE ADDON ONLY WHEN:
                             * - Main plugin not ACTIVE
                             * - AND Free OR Pro plugin is INSTALLED
                             */
                            if ($is_main_active) {
                                continue;
                            }

                            // ---------------------------------
                            // CATEGORY (FILTERING SAFE)
                            // ---------------------------------
                            $category = 'forms';

                            if (in_array($data['theme'], ['Avada', 'Divi', 'elements'], true)) {
                                $category = 'builders';
                            } elseif (in_array($data['theme'], ['woocommerce', 'easy-digital-downloads'], true)) {
                                $category = 'shop';
                            }
                            ?>

                            <div class="gsheetconnector-list-item gsc-ext-card gsc-market-item <?php echo esc_attr($category); ?>">

                                <div class="gsc-ext-top">
                                    <div class="extension-bg-color-set">
                                        <a href="<?php echo esc_url($data['link']); ?>" target="_blank">
                                            <img src="<?php echo esc_url($data['img']); ?>" class="gsc-ext-icon" alt="">
                                        </a>

                                        <?php if (!empty($data['freeLink'])): ?>
                                            <span class="plugin-free gsc-ext-badge free-green"><?php echo esc_html(__('Free', 'gsheetconnector-for-elementor-forms')); ?></span>

                                            <?php else: ?>
                                                <span class="plugin-premium gsc-ext-badge pro-badge"><?php echo esc_html(__('Pro', 'gsheetconnector-for-elementor-forms')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="addon-item-header-meta">

                                        <div class="addon-item-meta-title">
                                            <a href="<?php echo esc_url($data['link']); ?>"
                                                target="_blank"
                                                class="addon-link">
                                                <?php echo esc_attr($data['name']); ?>
                                            </a>
                                        </div>

                                        <div class="addon-item-header-meta-excerpt">
                                            <?php echo esc_attr($data['text']); ?>
                                        </div>

                                        <div class="addon-item-footer">
                                            <div class="button-bar gsc-ext-actions">

                                            <?php //if (!empty($data['downloadLink'])): 
                                            ?>


                                            <a href="<?php echo esc_url($data['docs']); ?>" target="_blank"
                                                class="btn btn-default link-hover-white"><?php echo esc_html(__('View Docs', 'gsheetconnector-for-elementor-forms')); ?>
                                            </a>

                                            <?php //endif; 
                                            ?>


                                            <?php if (!empty($data['demo'])): ?>

                                                <a href="<?php echo esc_url($data['demo']); ?>" target="_blank"
                                                    class="btn buy text-decoration-none"><?php echo esc_html(__('View Demo', 'gsheetconnector-for-elementor-forms')); ?>
                                                </a>

                                            <?php endif; ?>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>
                <!--NEW-->
                <?php wp_nonce_field('gselef-ajax-nonce', 'gselef-ajax-nonce'); ?>
            </div>
            <!--popup start-->
            <div id="gselef-confirm-dective-popup-free" class="gselef-popup-overlay d-none">
                <div class="gselef-popup  text-center">
                    <div class="gsc-modal-icon">
                        <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#d97706"></path>
                        </svg>
                    </div>
                    <div class="gsc-modal-title"><?php echo esc_html__('Deactivate plugin', 'gsheetconnector-for-elementor-forms'); ?></div>
                    <p class="gsc-modal-text"><?php echo esc_html__(' Are you sure you want to deactivate this plugin?', 'gsheetconnector-for-elementor-forms'); ?>
                </p>
                <div class="popup-actions d-flex justify-center gap-10">
                    <button type="button" class="btn deactivate-btn" id="gselef-dective-popup-cancel-free">
                        <?php echo esc_html__('Cancel', 'gsheetconnector-for-elementor-forms'); ?>
                    </button>
                    <button type="button" class="btn btn-primary" id="gselef-deactive-popup-confirm-free">
                        <?php echo esc_html__('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>
                    </button>
                </div>
            </div>
        </div>
        <div id="gselef-confirm-active-popup-free" class="gselef-popup-overlay d-none">
            <div class="gselef-popuptext-center">

                <div class="gsc-modal-icon">
                    <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#d97706"></path>
                    </svg>
                </div>


                <div class="popup-actions-active justify-center">
                    <div class="popup-actions-active-msg-free mb-20"></div>

                    <button type="button"
                    class="btn btn-active"
                    id="gselef-popup-active-free">
                    <?php echo esc_html__('Cancel', 'gsheetconnector-for-elementor-forms'); ?>
                </button>

            </div>
        </div>
    </div>
</div>
</div>
</div>
<!--end popup start-->