<!-- tab extension page  -->
<div class="wrap extension-wrap">
   <h2>Extensions</h2>
    <?php

    $all_plugins = get_plugins();
    $active_theme = wp_get_theme();

    $plugins = [
         'pro-elements/pro-elements.php' => [
            'connector' => 'gsheetconnector-for-elementor-forms/gsheetconnector-for-elementor-forms.php',
            'connector-pro' => 'gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php',
            'name' => 'Elementor Forms Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/elementor-gsc.webp',
            'text' => 'A bridge between your WordPress-based Elementor Forms or Metform Elementor Builder to Google Sheets.',
            'pro_plugin_active' => 'gsheetconnector-for-elementor-forms-pro/gsheetconnector-for-elementor-forms-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-for-elementor-forms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-for-elementor-forms.zip',
            'buyLink' => 'https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro',
            'mainPlugin' => ['pro-elements/pro-elements.php', 'elementor-pro/elementor-pro.php'],
            'theme' => 'elements'

        ],
        'gravityforms/gravityforms.php' => [
            'connector' => 'gsheetconnector-gravityforms/gsheetconnector-gravityforms.php',
            'connector-pro' => 'gsheetconnector-gravityforms-pro/gsheetconnector-gravityforms-pro.php',
            'name' => 'Gravity Forms Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/gravity-forms-google-sheet-connector',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/grvity-gsc.webp',
            'text' => 'Gravity Forms Google Sheets Connector Plugin is a bridge between your Gravity Forms and Google Sheets.',
            'pro_plugin_active' => 'gsheetconnector-gravityforms-pro/gsheetconnector-gravityforms-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-gravity-forms/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-gravity-forms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-gravity-forms.zip',
            'buyLink' => 'https://www.gsheetconnector.com/gravity-forms-google-sheet-connector',
            'mainPlugin' => 'gravityforms/gravityforms.php',
            'theme' => 'gravityforms'

        ],
        'contact-form-7/wp-contact-form-7.php' => [
            'connector' => 'cf7-google-sheets-connector/google-sheet-connector.php',
            'connector-pro' => 'cf7-google-sheets-connector-pro/google-sheet-connector-pro.php',
            'name' => 'CF7 Google Sheets Connector',
            'link' => 'https://www.gsheetconnector.com/cf7-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/cf7-gsc.webp',
            'text' => 'CF7 Google Sheets Connector is an addon plugin, a bridge between Contact Form 7 and Google Sheets.',
            'pro_plugin_active' => 'cf7-google-sheets-connector-pro/google-sheet-connector-pro.php',
            'url' => 'https://wordpress.org/plugins/cf7-google-sheets-connector/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/cf7-google-sheets-connector/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/cf7-google-sheets-connector.zip',
            'buyLink' => 'https://www.gsheetconnector.com/cf7-google-sheet-connector-pro',
            'mainPlugin' => 'contact-form-7/wp-contact-form-7.php',
            'theme' => 'contact-form-7'

        ],
        'woocommerce/woocommerce.php' => [
            'connector' => 'wc-gsheetconnector/wc-gsheetconnector.php',
            'connector-pro' => 'wc-gsheetconnector-pro/wc-gsheetconnector-pro.php',
            'name' => 'WooCommerce Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/woo-gsc.webp',
            'text' => 'WooCommerce is a powerful and customizable eCommerce platform for building online stores.',
            'pro_plugin_active' => 'wc-gsheetconnector-pro/wc-gsheetconnector-pro.php',
            'url' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/wc-gsheetconnector/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/wc-gsheetconnector.zip',
            'buyLink' => 'https://www.gsheetconnector.com/woocommerce-google-sheet-connector-pro',
            'mainPlugin' => 'woocommerce/woocommerce.php',
            'theme' => 'woocommerce'
        ],
       

        'wpforms-lite/wpforms.php' => [
            'connector' => 'gsheetconnector-wpforms/gsheetconnector-wpforms.php',
            'connector-pro' => 'gsheetconnector-wpforms-pro/gsheetconnector-wpforms-pro.php',
            'name' => 'WPForms Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/wpforms-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/wpforms-gsc.webp',
            'text' => 'A bridge between your WordPress-based WPForms and Google Sheets.',
            'pro_plugin_active' => 'gsheetconnector-wpform-pro/gsheetconnector-wpforms-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-wpforms/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-wpforms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-wpforms.zip',
            'buyLink' => 'https://www.gsheetconnector.com/wpforms-google-sheet-connector-pro',
            'mainPlugin' => 'wpforms-lite/wpforms.php',
            'theme' => 'wpforms'

        ],
        'ninja-forms/ninja-forms.php' => [
            'connector' => 'gsheetconnector-ninja-forms/gsheetconnector-ninjaforms.php',
            'connector-pro' => 'gsheetconnector-ninja-forms-pro/gsheetconnector-ninjaform-pro.php',
            'name' => 'Ninja Forms Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/ninja-forms-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/ninja-gsc.webp',
            'text' => 'Ninja Forms, a powerful tool to connect your forms with Google Sheets in real-time.',
            'pro_plugin_active' => 'gsheetconnector-ninja-forms-pro/gsheetconnector-ninjaforms-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-ninjaforms/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-ninja-forms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-ninja-forms.zip',
            'buyLink' => 'https://www.gsheetconnector.com/ninja-forms-google-sheet-connector',
            'mainPlugin' => 'ninja-forms/ninja-forms.php',
            'theme' => 'ninja-forms'
        ],

        'easy-digital-downloads/easy-digital-downloads.php' => [
            'connector' => 'gsheetconnector-easy-digital-downloads/gsheetconnector-easy-digital-downloads.php',
            'connector-pro' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
            'name' => 'Easy Digital Downloads Google Sheet Connector',
            'link' => 'https://www.gsheetconnector.com/easy-digital-downloads-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/edd-gsc.webp',
            'text' => 'Easy Digital Downloads Google Sheet Connector sends order entries to Google Sheets in real-time.',
            'pro_plugin_active' => 'gsheetconnector-easy-digital-downloads-pro/gsheetconnector-easy-digital-downloads-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/',
            'button' => 'Install Now',
            'badge' => 'PRO',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-easy-digital-downloads/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-easy-digital-downloads.zip',
            'buyLink' => 'https://www.gsheetconnector.com/edd-google-sheet-connector-pro',
            'mainPlugin' => 'easy-digital-downloads/easy-digital-downloads.php',
            'theme' => 'easy-digital-downloads'

        ],
        'avada/avada.php' => [
            'connector' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
            'connector-pro' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
            'name' => 'Avada Forms Google Sheet Connector Pro',
            'link' => 'https://www.gsheetconnector.com/avada-forms-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/avada-gsc.webp',
            'text' => 'Avada Theme Fusion Builder addon simplifies real-time data transmission from Avada theme-based forms to Google Sheets with premium features.',
            'pro_plugin_active' => 'avada-forms-google-sheet-connector-pro/avada-forms-google-sheet-connector-pro.php',
            'url' => '',
            'button' => 'Install Now',
            'badge' => 'Available PRO Version',
            'freeLink' => '',
            'downloadLink' => '',
            'buyLink' => 'https://www.gsheetconnector.com/avada-forms-google-sheet-connector-pro',
            'mainPlugin' => 'avada',
            'theme' => 'Avada'

        ],
        'divi/divi.php' => [
            'connector' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
            'connector-pro' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
            'name' => 'Divi Forms Google Sheet Connector Pro',
            'link' => 'https://www.gsheetconnector.com/divi-forms-db-google-sheet-connector-pro',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/divi-gsc.webp',
            'text' => 'Divi Forms GSheetConnector, an addon for DIVI Theme/Builder, simplifies real-time syncing of data from DIVI theme forms to both the database and Google Sheets.',
            'pro_plugin_active' => 'divi-forms-db-google-sheet-connector-pro/divi-forms-db-google-sheet-connector-pro.php',
            'url' => '',
            'button' => 'Install Now',
            'badge' => 'Available PRO Version',
            'freeLink' => '',
            'downloadLink' => '',
            'buyLink' => 'https://www.gsheetconnector.com/divi-forms-db-google-sheet-connector-pro',
            'mainPlugin' => 'Divi',
            'theme' => 'Divi'
        ],
        'forminator/forminator.php' => [
            'connector' => 'gsheetconnector-forminator/gsheetconnector-forminator.php',
            'connector-pro' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
            'name' => 'GSheetConnector for Forminator Forms',
            'link' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/forminator-gsc.webp',
            'text' => 'GSheetConnector for Forminator Forms is an addon plugin, a bridge between Forminator Forms and Google Sheets.',
            'pro_plugin_active' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
            'button' => 'Install Now',
            'badge' => 'Free',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-forminator/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-forminator.zip',
            'buyLink' => '',
            'mainPlugin' => 'forminator/forminator.php',
            'theme' => 'forminator'
        ],
        'formidable/formidable.php' => [
            'connector' => 'gsheetconnector-formidable-forms/gsheetconnector-formidable-forms.php',
            'connector-pro' => 'gsheetconnector-formidable-forms-pro/gsheetconnector-formidable-forms-pro.php',
            'name' => 'GSheetConnector for Formidable Forms',
            'link' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
            'img' => 'https://www.gsheetconnector.com/wp-content/uploads/wpplugin-org/formidable-gsc.webp',
            'text' => 'Formidable Forms, a bridge between your Formidable Forms and Google Sheets.',
            'pro_plugin_active' => 'gsheetconnector-forminator-pro/gsheetconnector-forminator-pro.php',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
            'button' => 'Install Now',
            'badge' => 'Free',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-formidable-forms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-formidable-forms.zip',
            'buyLink' => '',
            'mainPlugin' => 'formidable/formidable.php',
            'theme' => 'formidable'
        ],
        'caldera-forms/caldera-forms.php' => [
            'connector' => '',
            'connector-pro' => '',
            'name' => 'Caldera Forms Google Sheets Connector',
            'link' => 'https://wordpress.org/plugins/gsheetconnector-caldera-forms/',
            'img' => 'https://ps.w.org/gsheetconnector-caldera-forms/assets/icon-128x128.jpg?rev=2399438',
            'text' => 'Caldera Forms Closure, No more updates on this plugin due to Caldera Forms being sunset/retired on December 31, 2021.',
            'pro_plugin_active' => '',
            'url' => 'https://wordpress.org/plugins/gsheetconnector-caldera-forms/',
            'button' => '',
            'badge' => 'Free',
            'freeLink' => 'https://wordpress.org/plugins/gsheetconnector-caldera-forms/',
            'downloadLink' => 'https://downloads.wordpress.org/plugin/gsheetconnector-caldera-forms.zip',
            'buyLink' => '',
            'mainPlugin' => 'caldera-forms/caldera-forms.php',
            'theme' => 'caldera-forms'
        ]
    ];

    ?>


    <h2>Install and Activated Plugins</h2>
    <div class="gsheetconnector-addons-list">
        <?php
        // Iterate through each plugin and verify if both the main plugin and its connector are active
        foreach ($plugins as $plugin => $details) {
            $is_main_installed = false;
            $is_main_active = false;
            if (!empty($details['mainPlugin']) && is_array($details['mainPlugin'])) {
                foreach ($details['mainPlugin'] as $main_plugin) {
                    if (isset($all_plugins[$main_plugin])) {
                        $is_main_installed = true;
                    }
                    if (is_plugin_active($main_plugin)) {
                        $is_main_active = true;
                        break; // Stop checking if at least one is active
                    }
                }
            } elseif (!empty($details['mainPlugin']) && is_string($details['mainPlugin'])) {
                // If `mainPlugin` is a single string (not an array), check directly
                $is_main_installed = isset($all_plugins[$details['mainPlugin']]);
                $is_main_active = is_plugin_active($details['mainPlugin']);
            }
            $active_theme = wp_get_theme();
            $active_theme_slug = $active_theme->get_stylesheet();

            $is_pro_installed = isset($details['pro_plugin_active']) && isset($all_plugins[$details['pro_plugin_active']]);
            $is_pro_active = is_plugin_active($details['pro_plugin_active']);
            $is_free_installed = isset($details['connector']) && isset($all_plugins[$details['connector']]);
            $is_free_active = is_plugin_active($details['connector']);


            if (isset($details['pro_plugin_active']) && $is_main_active) {
                if (!is_plugin_active($details['pro_plugin_active'])) {
                    if ($is_free_active || $is_pro_installed) {
                        if ($is_pro_installed) { ?>
                            <div class="gsheetconnector-list-item">
                                <div class="addon-item-header">
                                    <div class="plugin-premium">PRO</div>
                                    <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank">
                                        <img src="<?php echo esc_url($details['img']); ?>" alt="<?php echo esc_attr($details['name']); ?>"
                                            loading="plugin-img">
                                    </a>
                                    <div class="addon-item-header-meta">
                                        <div class="addon-item-meta-title">
                                            <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank" class="addon-link">
                                                <?php echo esc_html($details['name']); ?>
                                            </a>
                                        </div>
                                        <div class="addon-item-header-meta-excerpt">

                                            <?php echo esc_html($details['text']); ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="addon-item-footer">
                                    <div class="button-bar">
                                        <button class="ele-activate-plugin-btn-pro button button-free proactive"
                                            data-plugin="<?php echo esc_attr($details['pro_plugin_active']); ?>">
                                            <?php esc_attr_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                        </button>
                                        <span class="loading-sign-active"></span>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="gsheetconnector-list-item">
                                <div class="activated">
                                    <a href="#" class="button button-free ele-deactivate-plugin-pro"
                                        data-download="<?php echo esc_url($details['connector']); ?>"
                                        data-plugin="<?php echo esc_attr($details['connector']); ?>"> <?php esc_attr_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?></a>
                                </div>
                                <div class="addon-item-header">
                                    <div class="plugin-free">Free</div>
                                    <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank">
                                        <img src="<?php echo esc_url($details['img']); ?>" alt="<?php echo esc_attr($details['name']); ?>">
                                    </a>
                                    <div class="addon-item-header-meta">
                                        <div class="addon-item-meta-title">
                                            <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank" class="addon-link">
                                                <?php echo esc_html($details['name']); ?>
                                            </a>
                                        </div>
                                        <div class="addon-item-header-meta-excerpt">
                                            <a href="<?php echo esc_url($details['buyLink']); ?>" target="_blank" class="addon-link">
                                                <?php esc_attr_e('Upgrade to PRO', 'gsheetconnector-for-elementor-forms'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="addon-item-footer">
                                    <div class="button-bar">

                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                } else { ?>
                    <div class="gsheetconnector-list-item">
                        <div class="activated">
                            <a href="#" class="button button-free ele-deactivate-plugin-pro"
                                data-download="<?php echo esc_url($details['connector-pro']); ?>"
                                data-plugin="<?php echo esc_attr($details['connector-pro']); ?>"><?php esc_attr_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?></a>
                        </div>
                        <div class="addon-item-header">
                            <div class="plugin-premium">PRO</div>
                            <a href="<?php echo esc_url($details['link']); ?>" target="_blank">
                                <img src="<?php echo esc_url($details['img']); ?>" alt="<?php echo esc_attr($details['name']); ?>">
                            </a>
                            <div class="addon-item-header-meta">
                                <div class="addon-item-meta-title">
                                    <a href="<?php echo esc_url($details['link']); ?>" target="_blank" class="addon-link">
                                        <?php echo esc_html($details['name'] . ' Pro'); ?>
                                    </a>
                                </div>
                                <div class="addon-item-header-meta-excerpt">
                                    <strong><?php esc_attr_e('Already using PRO version', 'gsheetconnector-for-elementor-forms'); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="addon-item-footer">
                            <div class="button-bar"></div>
                        </div>
                    </div>
                <?php }
            }
        }
        ?>
    </div>

    <!-- second section -->
    <h2>Recommended Plugins</h2>
    <div class="gsheetconnector-addons-list">
        <?php foreach ($plugins as $plugin => $data):
            $is_main_active = false;

            if (!empty($data['mainPlugin']) && is_array($data['mainPlugin'])) {
                foreach ($data['mainPlugin'] as $main_plugin) {
                    if (is_plugin_active($main_plugin)) { // Use WordPress function directly
                        $is_main_active = true;
                        break; // Stop checking once one is active
                    }
                }
            } elseif (!empty($data['mainPlugin']) && is_string($data['mainPlugin'])) {
                // If `mainPlugin` is a single string, check directly
                $is_main_active = is_plugin_active($data['mainPlugin']);
            }
            $active_theme = wp_get_theme();
            $active_theme_slug = $active_theme->get_stylesheet();

            $is_pro_installed = isset($data['pro_plugin_active']) && isset($all_plugins[$data['pro_plugin_active']]);
            $is_pro_active = is_plugin_active($data['pro_plugin_active']);
            $is_free_installed = isset($data['connector']) && isset($all_plugins[$data['connector']]);
            $is_free_active = is_plugin_active($data['connector']);
            if (!($active_theme_slug === $data['theme'])) {
                if ($is_main_active && !$is_pro_installed && !$is_pro_active && !$is_free_active): ?>
                    <div class="gsheetconnector-list-item">
                        <div class="addon-item-header">
                            <div class="plugin-free">Free</div>
                            <a href="<?php echo esc_url($data['url']); ?>" target="_blank">
                                <img src="<?php echo esc_url($data['img']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                            </a>
                            <div class="addon-item-header-meta">
                                <div class="addon-item-meta-title">
                                    <a href="<?php echo esc_url($data['url']); ?>" target="_blank" class="addon-link ">
                                        <?php echo esc_html($data['name']); ?>
                                    </a>
                                </div>
                                <div class="addon-item-header-meta-excerpt">
                                    <?php echo esc_html($data['text']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="addon-item-footer">
                            <div class="button-bar">
                                <?php if ($is_free_active): ?>
                                    <button class="button button-secondary" disabled>
                                        <?php esc_attr_e('Activated', 'gsheetconnector-for-elementor-forms'); ?>
                                    </button>
                                <?php elseif ($is_free_installed && !$is_free_active): ?>
                                    <button class="ele-activate-plugin-btn-pro button button-free"
                                        data-plugin="<?php echo esc_attr($data['connector']); ?>">
                                        <?php esc_attr_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                    </button>
                                    <span class="loading-sign-active"></span>

                                <?php else: ?>
                                    <button class="ele-install-plugin-btn-pro button "
                                        data-download="<?php echo esc_url($data['downloadLink']); ?>"
                                        data-plugin="<?php echo esc_attr($plugin); ?>">
                                        <?php echo esc_html($data['button']); ?>
                                    </button>
                                    <span class="loading-sign-install"></span>
                                    <!-- Ensure Activate button exists but is hidden -->
                                    <button class="ele-activate-plugin-btn-pro button button-free"
                                        data-plugin="<?php echo esc_attr($data['connector']); ?>" style="display: none;">
                                        <?php esc_attr_e('Activate', 'gsheetconnector-for-elementor-forms'); ?>
                                    </button>
                                    <span class="loading-sign-active"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php } else { ?>
                <div class="gsheetconnector-list-item">
                    <div class="addon-item-header">

                        <a href="<?php echo esc_url($data['buyLink']); ?>" target="_blank">
                            <img src="<?php echo esc_url($data['img']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                        </a>
                        <div class="addon-item-header-meta">
                            <div class="plugin-premium">PRO</div>
                            <div class="addon-item-meta-title">
                                <a href="<?php echo esc_url($data['buyLink']); ?>" target="_blank" class="addon-link">
                                    <?php echo esc_html($data['name']); ?>
                                </a>
                            </div>
                            <div class="addon-item-header-meta-excerpt">
                                <?php echo esc_html($data['text']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="addon-item-footer">
                        <div class="button-bar">
                            <div class="addon-item-meta-title">
                                <a href="<?php echo esc_url($data['link']); ?>" target="_blank" class="button">
                                    <?php echo esc_html($data['button']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php endforeach; ?>
    </div>


    <!-- 3th section-->
    <h2> Our Other Plugins</h2>
    <div class="gsheetconnector-addons-list">
        <?php
        // Loop through the array and generate HTML
        foreach ($plugins as $plugin => $data) {
            $is_main_active = false;

            if (!empty($data['mainPlugin']) && is_array($data['mainPlugin'])) {
                foreach ($data['mainPlugin'] as $main_plugin) {
                    if (is_plugin_active($main_plugin)) {
                        $is_main_active = true;
                        break; // Stop checking once one is active
                    }
                }
            } elseif (!empty($data['mainPlugin']) && is_string($data['mainPlugin'])) {
                // If `mainPlugin` is a single string, check directly
                $is_main_active = is_plugin_active($data['mainPlugin']);
            }

            // Check if the required theme is active
            $active_theme = wp_get_theme();
            $active_theme_slug = $active_theme->get_stylesheet(); // Get active theme slug
            $is_pro_installed = isset($data['pro_plugin_active']) && isset($all_plugins[$data['pro_plugin_active']]);
            $is_free_installed = isset($data['connector']) && isset($all_plugins[$data['connector']]);
            $is_pro_active = is_plugin_active($data['pro_plugin_active']);
            $is_free_active = is_plugin_active($data['connector']);
            $is_pro_active = is_plugin_active($data['pro_plugin_active']);
            // Check if the main plugin(main plugin) is active
            if (!($active_theme_slug === $data['theme'])):
                if (!$is_main_active): ?>
                    <div class="gsheetconnector-list-item">
                        <div class="addon-item-header">
                            <?php if (('Avada' === $data['theme']) || ('Divi' === $data['theme'])) { ?>
                                <div class="plugin-premium">PRO</div>
                            <?php } else { ?>
                                <div class="plugin-free">Free</div>
                            <?php } ?>
                            <a href="<?php echo esc_url($data['link']); ?>" target="_blank">
                                <img src="<?php echo esc_html($data['img']); ?>" alt="logo">
                            </a>
                            <div class="addon-item-header-meta">
                                <div class="addon-item-meta-title">
                                    <a href="<?php echo esc_url($data['link']); ?>" target="_blank">
                                        <?php echo esc_html($data['name']); ?>
                                    </a>
                                </div>
                                <div class="addon-item-header-meta-excerpt">
                                    <?php echo esc_html($data['text']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="addon-item-footer">

                            <div class="button-bar">

                            </div>
                        </div>
                    </div>
                <?php endif;
            endif;
        }

        ?>
    </div>
    <?php wp_nonce_field('gsele_ajax_nonce', 'gsele_ajax_nonce'); ?>

</div>
<!-- wrap #end -->