<?php
// phpcs:disable
$token = get_option('elefgs_token');
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe: tab selection used for UI only, no sensitive action
$Code = "";
$header = admin_url('admin.php?page=gsheetconnector-elementor-config');
if (isset($_GET['code'])) {
    update_option('is_new_client_secret_elefgscfree', 1);
    if (is_string($_GET['code'])) {
        $Code = sanitize_text_field($_GET["code"]);
    }
}
// phpcs:enable

?>
<div class="heading mt-0 mb-0"><?php echo esc_html(__('Google Sheet Integration - GSheetConnector For Elementor Forms', 'gsheetconnector-for-elementor-forms')); ?></div>



<input type="hidden" name="redirect_auth_wc" id="redirect_auth_wc" value="<?php echo isset($header) ? esc_attr($header) : ''; ?>">
<div class="elem-gs-form">
    <div class="card-wp  border-select-box row shadow-box mt-40 p-30" id="elem-googlesheet">

        <div class="col-6">
            <div class="form-group">
                <label for="ele_dro_option" class="ele_gapi"><?php echo esc_html(__('Choose Google API Setting', 'gsheetconnector-for-elementor-forms')); ?></label>
                <select id="ele_dro_option" name="ele_dro_option" class="gsc-select">
                    <option value="elegs_html_existing" selected><?php echo esc_html__('Existing Client / Secret Key (Auto Setup)', 'gsheetconnector-for-elementor-forms'); ?></option>
                    <option value="elegs_manual"><?php echo esc_html__('Manual Client/Secret Key (Use Your Google API Configuration)', 'gsheetconnector-for-elementor-forms'); ?></option>
                    <option value="elegs_service"><?php echo esc_html__('Service Account (Recommended)', 'gsheetconnector-for-elementor-forms'); ?></option>
                </select>
            </div>
            <p class="api-select-help mb-0"><?php echo esc_html(__('Select how Elementor Forms should authenticate with Google Sheets.', 'gsheetconnector-for-elementor-forms')); ?></p>
        </div> <!-- col 6 #end -->
    </div> <!-- card #end -->
    <div class="oauth-method row justify-between shadow-box mt-40 p-30">
        <div class="col-7">
            <div class="existing-method mr-20">
                <div class="heading mt-0"> <?php echo esc_html(__('Automatic Google Sheets Integration', 'gsheetconnector-for-elementor-forms')); ?>
                <span class="badge"><?php echo esc_html(__('Auto Setup', 'gsheetconnector-for-elementor-forms')); ?></span>
            </div>
            <p><?php echo esc_html(__('Automatically connect Elementor Forms with Google Sheets using built-in API setup. By authorizing your Google account, the plugin will handle API setup and authentication automatically, enabling seamless form data sync. Learn more in the documentation', 'gsheetconnector-for-elementor-forms')); ?>
            <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank"><?php echo esc_html(__('click here', 'gsheetconnector-for-elementor-forms')); ?></a>.
        </p>
        <?php
                // Generate nonce for later verification
        $nonce = wp_create_nonce('gs-ajax-nonce-ele');
        ?>
        <!-- Input: redirect_auth_eleforms -->
                <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized 
                ?>
                <input type="hidden" name="redirect_auth_eleforms" id="redirect_auth_eleforms"
                value="<?php echo esc_url($header ?? ''); ?>">

                <!-- Input: get_code -->
                <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated 
                ?>
                <?php
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $get_code = ! empty($_GET['code']) ? '1' : '0';
                ?>

                <input type="hidden" name="get_code" id="get_code" value="<?php echo esc_attr($get_code); ?>">
                <!-- Nonce Input -->
                <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash 
                ?>
                <input type="hidden" name="gs-ajax-nonce-ele" id="gs-ajax-nonce-ele"
                value="<?php echo esc_attr($nonce); ?>">

                <div class="card-wp ele_apiesc_html_existing_setting">
                    <div class="elem-inside">
                        <?php if (empty($token) && $token == "") { ?>
                            <?php if (empty($Code)) { ?>

                                <div class="gsc-auth-steps mt-30">

                                    <div class="authentication-heading">
                                        <?php echo esc_html(__('Authenticate with Your Google Account', 'gsheetconnector-for-elementor-forms')); ?>
                                    </div>

                                    <ul>
                                        <li>
                                            <?php
                                            echo wp_kses_post(
                                                __('Click on the <strong>Sign in with Google</strong> button.', 'gsheetconnector-for-elementor-forms')
                                            );
                                            ?>
                                        </li>
                                        <li>
                                            <?php echo esc_html(__('Log in using your Google account.', 'gsheetconnector-for-elementor-forms')); ?>
                                        </li>
                                        <li>
                                            <?php echo esc_html(__('Select the Google account where your Sheets are stored.', 'gsheetconnector-for-elementor-forms')); ?>
                                        </li>
                                        <li>
                                            <?php echo esc_html(__('Grant access to:', 'gsheetconnector-for-elementor-forms')); ?>
                                            <ul>
                                                <li>
                                                    <?php echo esc_html(__('Google Drive', 'gsheetconnector-for-elementor-forms')); ?>
                                                </li>
                                                <li>
                                                    <?php echo esc_html(__('Google Sheets', 'gsheetconnector-for-elementor-forms')); ?>
                                                </li>
                                            </ul>
                                        </li>
                                        <li>
                                            <?php
                                            echo wp_kses_post(
                                                __('Click <strong>Allow</strong> to finish authorization.', 'gsheetconnector-for-elementor-forms')
                                            );
                                            ?>
                                        </li>
                                        <li>
                                            <?php echo esc_html(__('Save the authentication code if prompted.', 'gsheetconnector-for-elementor-forms')); ?>
                                        </li>
                                    </ul>
                                    <p class="gsc-auth-note mb-0">
                                        <?php echo esc_html(__('This allows the plugin to securely sync your form data with Google Sheets.', 'gsheetconnector-for-elementor-forms')); ?>
                                    </p>
                                </div>


                            <?php } ?>
                        <?php }
                        if (!empty(get_option('elefgs_verify')) && (get_option('elefgs_verify') == "invalid-auth")) {
                            ?>
                            <div class="gsc-msg gsc-error fw-400 text-dark text-center pt-10 pb-10 manual-margin">
                                <?php echo esc_html__(
                                    'Google Drive and Google Sheets permissions are not granted. Please deactivate and re-authorize with full permissions.',
                                    'gsheetconnector-for-elementor-forms'
                                ); ?>

                            </div>
                            <?php
                        } else {

                            ?>
                            <div class="gselef-integration-box">
                                <div class="gsc-google-auth-card d-flex flex-wrap gap-20 justify-between align-center mt-30 mb-30">

                                    <div class="gsc-google-auth-left d-flex flex-wrap align-center gap-15">

                                        <?php
                                        $email_account = ''; // initialize to avoid undefined variable
                                        if (! empty($token)) {
                                            $google_sheet  = new GSC_Elementor_Free();
                                            $email_account = $google_sheet->gsheet_print_google_account_email();
                                        }
                                        ?>

                                        <?php if ($email_account) : ?>

                                            <div class="gsc-google-icon">G</div>
                                            <div class="connected-account">
                                                <div class="gsc-connected-left d-flex">

                                                    <span class="gsc-connected-label">
                                                        <?php esc_html_e('Connected Email Account', 'gsheetconnector-for-elementor-forms'); ?>
                                                    </span>

                                                    <span class="connected-account-manual gsc-connected-email">
                                                        <?php echo esc_html($email_account); ?>
                                                    </span>

                                                </div>
                                            </div>

                                            <?php else : ?>

                                                <?php
                                            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                                                if (! empty($_GET['code'])) :
                                                    ?>

                                                    <div class="gsc-google-icon">G</div>
                                                    <div class="gsc-google-auth-text">
                                                        <strong><?php esc_html_e('Client Token', 'gsheetconnector-for-elementor-forms'); ?></strong>
                                                    </div>

                                                    <?php else : ?>

                                                        <div class="gsc-google-icon">G</div>
                                                        <div class="gsc-google-auth-text">
                                                            <strong><?php esc_html_e('Connect Your Google Account', 'gsheetconnector-for-elementor-forms'); ?></strong>
                                                            <p><?php esc_html_e('Securely link your Google account to start syncing form entries automatically.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                                        </div>

                                                    <?php endif; ?>

                                                <?php endif; ?>

                                            </div> <!-- gsc google auth left -->

                                            <div class="gsc-google-auth-right">
                                                <?php if (!empty($token) && $token !== "") { ?>

                                                    <div class="gsc-connected-pill">
                                                        <span class="dot"></span>
                                                        <?php echo esc_html(__('Connected', 'gsheetconnector-for-elementor-forms')); ?>
                                                    </div>

                                                <?php } else {
                                                    $redirct_uri = admin_url('admin.php?page=gsheetconnector-elementor-config');
                                                    ?>

                                                    <?php if (! empty($_GET['code'])) :  ?>

                                                        <div class="token-box-width-exist">
                                                            <input type="password" class="form-control" name="ele-code" id="ele-code" value="<?php echo esc_attr($Code); ?>"
                                                            placeholder="<?php echo esc_html(__('Click Sign in with Google ->', 'gsheetconnector-for-elementor-forms')); ?>" disabled />
                                                        </div>
                                                        <?php else : ?><?php endif; ?>


                                                        <?php if (empty($Code)) { ?>
                                                            <a href="<?php echo esc_url('https://oauth.gsheetconnector.com/index.php?client_admin_url=' . urlencode($redirct_uri) . '&plugin=woocommercegsheetconnector'); ?>"
                                                                class="gsc-google-btn link-hover-white">
                                                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage 
                                                        ?>
                                                        <img src="<?php echo esc_url(GS_CONN_ELE_URL . '/assets/img/g-logo.png'); ?>"> <?php echo esc_html(__('Sign in with Google', 'gsheetconnector-for-elementor-forms')); ?>
                                                    </a>

                                                <?php } ?>
                                            <?php } ?>

                                        </div> <!-- gsc google auth right #end -->
                                    </div> <!-- gsc google auth card #end -->
                                </div> <!-- gsc integration box #end -->

                            <?php }
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback, nonce not required
                            if (!empty($_GET['code'])) { ?>

                                <div class="button-container mt-30">
                                    <button type="button" name="save-ele-code" class="btn btn-primary btn-pulse" id="save-ele-code"><?php echo esc_html(__('Save', 'gsheetconnector-for-elementor-forms')); ?></button>
                                    <span class="loading-sign"></span>
                                    </div> <?php } ?>


                                    <?php if (!empty($token) && $token !== "") { ?>

                                        <input type="text" style="display: none" name="ele-code" id="ele-code" value="" disabled
                                        placeholder="<?php echo esc_html(__('Currently Active', 'gsheetconnector-for-elementor-forms')); ?>" />
                                        <input type="button" name="deactivate-log-ele" id="deactivate-log-ele"
                                        value="<?php esc_html_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>" class="gsc-btn gsc-btn-gray btn deactivate-btn" />

                                        <span class="loading-sign-deactive"></span>

                                    <?php } ?>
                                    <div>
                                        <p id="gs-validation-message"></p>
                                        <span id="deactivate-message"></span>
                                    </div>

                                    <div class="gsc-privacy-note mt-30 pt-10 pb-10 text-dark d-flex gap-5">
                                        <div class="gsc-privacy-note-image">
                                            <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <?php echo esc_html(__('We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. Learn more in the documentation', 'gsheetconnector-for-elementor-forms')); ?>
                                            <a href="https://gsheetconnector.com/usage-tracking/" target="_blank"
                                            rel="noopener noreferrer"><?php echo esc_html(__('click here.', 'gsheetconnector-for-elementor-forms')); ?>
                                        </a>
                                    </div>
                                </div>
                                <?php
                                $google_sheet  = new GSC_Elementor_Free();
                                $email_account = $google_sheet->gsheet_print_google_account_email();


                                if (($email_account)) { ?>
                                    <div class="gsc-connection-box mt-20">

                                        <div class="heading mt-0">
                                            <?php echo esc_html(__('Connection Status & Next Steps', 'gsheetconnector-for-elementor-forms')); ?>
                                        </div>

                                        <p class="gsc-desc">
                                            <?php
                                            echo esc_html(__(
                                                'Your Google account has been successfully connected. You are now ready to sync your form submissions with Google Sheets securely and automatically.',
                                                'gsheetconnector-for-elementor-forms'
                                            ));
                                            ?>
                                        </p>

                                        <div class="gsc-steps mb-0">

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Create New Feed', 'gsheetconnector-for-elementor-forms')); ?>
                                            </div>

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Select Google Spreadsheet', 'gsheetconnector-for-elementor-forms')); ?>
                                            </div>

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Add Labels in Sheet Headers Manually', 'gsheetconnector-for-elementor-forms')); ?>
                                            </div>

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Enable Fields to Sync', 'gsheetconnector-for-elementor-forms')); ?><span class="gsc-pro-badge spacing-bdg-pro"><?php echo esc_html(__('PRO', 'gsheetconnector-for-elementor-forms')); ?></span>
                                            </div>

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Customize the Headers Appearance', 'gsheetconnector-for-elementor-forms')); ?><span class="gsc-pro-badge spacing-bdg-pro"><?php echo esc_html(__('PRO', 'gsheetconnector-for-elementor-forms')); ?>
                                            </div>

                                            <div class="gsc-step d-flex align-center gap-5">
                                                <svg fill:#999999; width="12px" height="12px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0 16q0-3.232 1.28-6.208t3.392-5.12 5.12-3.392 6.208-1.28q3.264 0 6.24 1.28t5.088 3.392 3.392 5.12 1.28 6.208q0 3.264-1.28 6.208t-3.392 5.12-5.12 3.424-6.208 1.248-6.208-1.248-5.12-3.424-3.392-5.12-1.28-6.208zM8 16q0 3.328 2.336 5.664t5.664 2.336 5.664-2.336 2.336-5.664-2.336-5.632-5.664-2.368-5.664 2.368-2.336 5.632z"></path>
                                                </svg>
                                                <?php echo esc_html(__('Use Sync Settings (Past Entries)', 'gsheetconnector-for-elementor-forms')); ?><span class="gsc-pro-badge spacing-bdg-pro"><?php echo esc_html(__('PRO', 'gsheetconnector-for-elementor-forms')); ?>
                                            </div>

                                        </div>

                                        <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none mt-30 link-hover-white"><?php echo esc_html(__('Upgrade to unlock', 'gsheetconnector-for-elementor-forms')); ?></a>

                                    </div>

                                <?php } ?>
                            </div> <!-- elem inside #end -->
                        </div> <!-- card wp #end -->

                    </div> <!-- existing method #end -->
                </div> <!-- col 7 #end -->
                <div class="col-5">
                    <div class="step-guide-col ml-20">
                        <div class="heading mt-0"> <?php echo esc_html(__('Connection Guide', 'gsheetconnector-for-elementor-forms')); ?>
                        <span class="badge"><?php echo esc_html(__('Step-by-Step', 'gsheetconnector-for-elementor-forms')); ?></span>
                    </div>
                    <p><?php echo esc_html__('Follow these steps to connect your Google account and start syncing your form data with Google Sheets.', 'gsheetconnector-for-elementor-forms'); ?>
                </p>
                <div class="gsc-slider-wrapper mt-30">
                    <div class="gsc-slider">

                        <div class="gsc-slide">

                            <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-1 Connect Your Google Account', 'gsheetconnector-for-elementor-forms'); ?>
                            <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Sign in with your Google account to start the automatic Google Sheets integration.', 'gsheetconnector-for-elementor-forms'); ?>">
                                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                                </svg>
                            </a>
                        </div>
                        <a href="https://gmail.com/" target="_blank" class="link"><?php echo esc_html(__('Sign in with Google', 'gsheetconnector-for-elementor-forms')); ?></a>

                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step1.png" alt="" />


                    </div>

                    <div class="gsc-slide">
                        <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-2 Choose Google Account', 'gsheetconnector-for-elementor-forms'); ?> <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Choose the Google account where your Sheets are stored.', 'gsheetconnector-for-elementor-forms'); ?>">
                            <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                            </svg></a>
                        </div>
                        <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank" class="link">
                            <?php esc_html_e('check our detailed guideline', 'gsheetconnector-for-elementor-forms'); ?></a>
                            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step2.png" alt="Step-2 Choose Google Account" />
                        </div>

                        <div class="gsc-slide">
                            <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-3 Review Access Information', 'gsheetconnector-for-elementor-forms'); ?> <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Google will show what information the plugin can access.', 'gsheetconnector-for-elementor-forms'); ?>">
                                <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                                </svg></a>
                            </div>
                            <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank" class="link">
                                <?php esc_html_e('check our detailed guideline', 'gsheetconnector-for-elementor-forms'); ?></a>


                                <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step3.png" alt="Step-3 Review Access Information" />
                            </div>

                            <div class="gsc-slide">
                                <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-4 Grant Required Permissions', 'gsheetconnector-for-elementor-forms'); ?> <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Allow required permissions for Google Sheets and Drive access.', 'gsheetconnector-for-elementor-forms'); ?>">
                                    <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                                    </svg></a>
                                </div>
                                <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank" class="link">
                                    <?php esc_html_e('check our detailed guideline', 'gsheetconnector-for-elementor-forms'); ?></a>


                                    <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step4.png" alt="Step-4 Grant Required Permissions" />
                                </div>

                                <div class="gsc-slide">
                                    <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-5 Save Authentication Code', 'gsheetconnector-for-elementor-forms'); ?> <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Save the authentication code to complete the Google account connection.', 'gsheetconnector-for-elementor-forms'); ?>">
                                        <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                                        </svg></a>
                                    </div>
                                    <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank" class="link">
                                        <?php esc_html_e('check our detailed guideline', 'gsheetconnector-for-elementor-forms'); ?></a>


                                        <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step5.png" alt="Step-5 Save Authentication Code" />
                                    </div>


                                    <div class="gsc-slide">
                                        <div class="gsc-slider-headers fw-600 mb-10 text-dark"><?php esc_html_e('Step-6 Integration Completed', 'gsheetconnector-for-elementor-forms'); ?> <a href="#" class="i-help" hover-tooltip="<?php echo esc_html__('Your Google account is now successfully connected.', 'gsheetconnector-for-elementor-forms'); ?>">
                                            <svg width="800px" height="800px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 19.5C16.1421 19.5 19.5 16.1421 19.5 12C19.5 7.85786 16.1421 4.5 12 4.5C7.85786 4.5 4.5 7.85786 4.5 12C4.5 16.1421 7.85786 19.5 12 19.5ZM12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM12.75 15V16.5H11.25V15H12.75ZM10.5 10.4318C10.5 9.66263 11.1497 9 12 9C12.8503 9 13.5 9.66263 13.5 10.4318C13.5 10.739 13.3151 11.1031 12.9076 11.5159C12.5126 11.9161 12.0104 12.2593 11.5928 12.5292L11.25 12.7509V14.25H12.75V13.5623C13.1312 13.303 13.5828 12.9671 13.9752 12.5696C14.4818 12.0564 15 11.3296 15 10.4318C15 8.79103 13.6349 7.5 12 7.5C10.3651 7.5 9 8.79103 9 10.4318H10.5Z" fill="#080341" />
                                            </svg></a>
                                        </div>
                                        <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-existing-method" target="_blank" class="link">
                                            <?php esc_html_e('check our detailed guideline', 'gsheetconnector-for-elementor-forms'); ?></a>
                                            <img src="<?php echo esc_url(GS_CONN_ELE_URL); ?>/assets/img/existing-step6.png" alt="Step-6 Integration Completed" />
                                        </div>

                                    </div>

                                    <button class="gsc-nav prev">❮</button>
                                    <button class="gsc-nav next">❯</button>
                                </div>

                            </div> <!-- step-guide-col #end -->
                        </div>
                    </div> <!-- oauth method div #end -->



                    <?php
                    if (class_exists('gscelef_error_logs')) {
                        $gselef_logs = new gscelef_error_logs();
                        $gselef_logs->gselef_render_page_html();
                    }
                    ?>

                    <div id="gselef-confirm-manual-popup-pro" class="gselef-popup-overlay d-none">
                        <div class="gselef-popups position-relative-popup text-center">
                            <button class="gselef-popup-close-pro gsc-pro-close">×</button>
                            <div class="gsc-pro-section">
                                <div class="gsc-pro-card">
                                    <div class="gsc-pro-headers">
                                        <span class="gsc-pro-badge">PRO</span>
                                        <div class="main-popup-heading mb-20 fw-600"><?php esc_html_e('Manual Google Sheets Integration', 'gsheetconnector-for-elementor-forms'); ?></div>
                                        <p class="mb-0 text-center"><?php echo esc_html__('Connect Elementor Forms to Google Sheets using your own Google Cloud project.
                                            Ideal for advanced users who need full API control, custom OAuth setup,
                                            and independent credential management.', 'gsheetconnector-for-elementor-forms'); ?>
                                        </p>
                                    </div>

                                    <div class="gsc-pro-features">

                                        <!-- Feature 1 -->
                                        <div class="gsc-feature-item">
                                            <div class="gsc-feature-icon">
                                                <!-- API Key SVG -->
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                    <path d="M21 10h-6l-2-2H3v8h10l2-2h6v-4z" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                            <div class="gsc-feature-content">
                                                <div class="gsc-popup-header"><?php esc_html_e('Custom API Credentials', 'gsheetconnector-for-elementor-forms'); ?></div>
                                                <p><?php echo esc_html__('Connect using your own Google Cloud Client ID and Client Secret for full authentication control.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                            </div>
                                        </div>

                                        <!-- Feature 2 -->
                                        <div class="gsc-feature-item">
                                            <div class="gsc-feature-icon">
                                                <!-- Shield OAuth SVG -->
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z" stroke="#000" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="gsc-feature-content">
                                                <div class="gsc-popup-header"><?php esc_html_e('Secure Authentication', 'gsheetconnector-for-elementor-forms'); ?></div>
                                                <p><?php echo esc_html__('Authenticate directly with Google using a secure Authentication flow without third-party dependency.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                            </div>
                                        </div>

                                        <!-- Feature 3 -->
                                        <div class="gsc-feature-item">
                                            <div class="gsc-feature-icon">
                                                <!-- Settings SVG -->
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 8a4 4 0 100 8 4 4 0 000-8z" stroke="#000" stroke-width="2" />
                                                    <path d="M2 12h2M20 12h2M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M19.1 4.9l-1.4 1.4M6.3 17.7l-1.4 1.4"
                                                    stroke="#000" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="gsc-feature-content">
                                                <div class="gsc-popup-header"><?php esc_html_e('Advanced Configuration', 'gsheetconnector-for-elementor-forms'); ?></div>
                                                <p><?php echo esc_html__('Set custom Redirect URIs, manage scopes, and configure API settings based on your project needs.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                            </div>
                                        </div>

                                        <!-- Feature 4 -->
                                        <div class="gsc-feature-item">
                                            <div class="gsc-feature-icon">
                                                <!-- Support SVG -->
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                                    <path d="M18 8a6 6 0 10-12 0v4a2 2 0 002 2h1v-4H8a4 4 0 118 0h-1v4h1a2 2 0 002-2V8z"
                                                    stroke="#000" stroke-width="2" />
                                                </svg>
                                            </div>
                                            <div class="gsc-feature-content">
                                                <div class="gsc-popup-header"><?php esc_html_e('Priority Technical Support', 'gsheetconnector-for-elementor-forms'); ?></div>
                                                <p><?php echo esc_html__('Get fast assistance from our expert team for setup, troubleshooting, and optimization.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                            </div>
                                        </div>

                                    </div>


                                    <div class="gsc-pro-actions justify-center d-flex flex-wrap gap-20 mt-20">
                                        <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none link-hover-white"><?php echo esc_html__('Upgrade to Unlock', 'gsheetconnector-for-elementor-forms'); ?></a>
                                        <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/integration-with-google-manual-method" target="_blank" class="btn deactivate-btn text-decoration-none"><?php echo esc_html__('View Pro Features', 'gsheetconnector-for-elementor-forms'); ?></a>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                    <div id="gselef-confirm-service-popup-pro" class="gselef-popup-overlay d-none">
                        <div class="gselef-popups position-relative-popup text-center">
                            <button class="gselef-popup-service-close-pro gsc-pro-close">×</button>
                            <div class="gsc-pro-section">
                                <div class="gsc-pro-card">
                                    <div class="gsc-pro-headers">
                                        <span class="gsc-pro-badge">PRO</span>
                                        <div class="main-popup-heading mb-20 fw-600"><?php esc_html_e('Service Account Google Sheets Integration', 'gsheetconnector-for-elementor-forms'); ?></div>
                                        <p class="mb-0 text-center"><?php echo esc_html__('Securely connect Elementor Forms to Google Sheets using a Google Cloud Service Account JSON key. Ideal for automated server-to-server syncing without requiring manual Google login.', 'gsheetconnector-for-elementor-forms'); ?>
                                    </p>
                                </div>

                                <div class="gsc-pro-features">

                                    <!-- Feature 1 -->
                                    <div class="gsc-feature-item">
                                        <div class="gsc-feature-icon">
                                            <!-- API Key SVG -->
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" />
                                                <circle cx="12" cy="12" r="2" />
                                                <path d="M14 12h4" />
                                            </svg>

                                        </div>
                                        <div class="gsc-feature-content">
                                            <div class="gsc-popup-header"><?php esc_html_e('Secure JSON Authentication', 'gsheetconnector-for-elementor-forms'); ?></div>
                                            <p><?php echo esc_html__('Authenticate using a secure Google Cloud JSON key file for direct and encrypted communication with Google Sheets.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                        </div>
                                    </div>

                                    <!-- Feature 2 -->
                                    <div class="gsc-feature-item">
                                        <div class="gsc-feature-icon">
                                            <!-- Shield OAuth SVG -->
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 2l7 4v6c0 5-3.5 9-7 10-3.5-1-7-5-7-10V6l7-4z" />
                                                <circle cx="12" cy="10" r="3" />
                                                <path d="M9 16c1-1 5-1 6 0" />
                                            </svg>

                                        </div>
                                        <div class="gsc-feature-content">
                                            <div class="gsc-popup-header"><?php esc_html_e('No Authentication Login Required', 'gsheetconnector-for-elementor-forms'); ?></div>
                                            <p><?php echo esc_html__('Enable automatic background syncing without requiring Google account sign-in during setup.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                        </div>
                                    </div>

                                    <!-- Feature 3 -->
                                    <div class="gsc-feature-item">
                                        <div class="gsc-feature-icon">
                                            <!-- Settings SVG -->
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 3h12l4 4v14H4z" />
                                                <path d="M16 3v4h4" />
                                                <line x1="8" y1="13" x2="16" y2="13" />
                                                <line x1="8" y1="17" x2="16" y2="17" />
                                            </svg>

                                        </div>
                                        <div class="gsc-feature-content">
                                            <div class="gsc-popup-header"><?php esc_html_e('Direct Spreadsheet Access', 'gsheetconnector-for-elementor-forms'); ?></div>
                                            <p><?php echo esc_html__('Share your Google Sheet with the service account email to enable automatic data transfer.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                        </div>
                                    </div>

                                    <!-- Feature 4 -->
                                    <div class="gsc-feature-item">
                                        <div class="gsc-feature-icon">
                                            <!-- Support SVG -->
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="6" rx="2" />
                                                <rect x="3" y="14" width="18" height="6" rx="2" />
                                                <circle cx="7" cy="7" r="1" />
                                                <circle cx="7" cy="17" r="1" />
                                            </svg>

                                        </div>
                                        <div class="gsc-feature-content">
                                            <div class="gsc-popup-header"><?php esc_html_e('Production-Ready & Reliable', 'gsheetconnector-for-elementor-forms'); ?></div>
                                            <p><?php echo esc_html__('Built for stable, uninterrupted syncing in professional and high-traffic environments.', 'gsheetconnector-for-elementor-forms'); ?></p>
                                        </div>
                                    </div>

                                </div>


                                <div class="gsc-pro-actions justify-center d-flex flex-wrap gap-20 mt-20">
                                    <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank" class="btn btn-primary text-decoration-none link-hover-white"><?php echo esc_html__('Upgrade to Unlock', 'gsheetconnector-for-elementor-forms'); ?></a>
                                    <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/service-account-setting-pro-version" target="_blank" class="btn deactivate-btn text-decoration-none"><?php echo esc_html__('View Pro Features', 'gsheetconnector-for-elementor-forms'); ?></a>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div id="gselef-confirm-deactive-popup-free" class="gselef-popup-overlay d-none">
                    <div class="gselef-popup text-center">
                        <div class="gsc-modal-icon">
                            <svg width="30px" height="30px" viewBox="-0.5 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18.2202 21.25H5.78015C5.14217 21.2775 4.50834 21.1347 3.94373 20.8364C3.37911 20.5381 2.90402 20.095 2.56714 19.5526C2.23026 19.0101 2.04372 18.3877 2.02667 17.7494C2.00963 17.111 2.1627 16.4797 2.47015 15.92L8.69013 5.10999C9.03495 4.54078 9.52077 4.07013 10.1006 3.74347C10.6804 3.41681 11.3346 3.24518 12.0001 3.24518C12.6656 3.24518 13.3199 3.41681 13.8997 3.74347C14.4795 4.07013 14.9654 4.54078 15.3102 5.10999L21.5302 15.92C21.8376 16.4797 21.9907 17.111 21.9736 17.7494C21.9566 18.3877 21.7701 19.0101 21.4332 19.5526C21.0963 20.095 20.6211 20.5381 20.0565 20.8364C19.4919 21.1347 18.8581 21.2775 18.2202 21.25V21.25Z" stroke="#d97706" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M10.8809 17.15C10.8809 17.0021 10.9102 16.8556 10.9671 16.7191C11.024 16.5825 11.1074 16.4586 11.2125 16.3545C11.3175 16.2504 11.4422 16.1681 11.5792 16.1124C11.7163 16.0567 11.8629 16.0287 12.0109 16.03C12.2291 16.034 12.4413 16.1021 12.621 16.226C12.8006 16.3499 12.9398 16.5241 13.0211 16.7266C13.1023 16.9292 13.122 17.1512 13.0778 17.3649C13.0335 17.5786 12.9272 17.7745 12.7722 17.9282C12.6172 18.0818 12.4203 18.1863 12.2062 18.2287C11.9921 18.2711 11.7703 18.2494 11.5685 18.1663C11.3666 18.0833 11.1938 17.9426 11.0715 17.7618C10.9492 17.5811 10.8829 17.3683 10.8809 17.15ZM11.2409 14.42L11.1009 9.20001C11.0876 9.07453 11.1008 8.94766 11.1398 8.82764C11.1787 8.70761 11.2424 8.5971 11.3268 8.5033C11.4112 8.40949 11.5144 8.33449 11.6296 8.28314C11.7449 8.2318 11.8697 8.20526 11.9959 8.20526C12.1221 8.20526 12.2469 8.2318 12.3621 8.28314C12.4774 8.33449 12.5805 8.40949 12.6649 8.5033C12.7493 8.5971 12.8131 8.70761 12.852 8.82764C12.8909 8.94766 12.9042 9.07453 12.8909 9.20001L12.7609 14.42C12.7609 14.6215 12.6808 14.8149 12.5383 14.9574C12.3957 15.0999 12.2024 15.18 12.0009 15.18C11.7993 15.18 11.606 15.0999 11.4635 14.9574C11.321 14.8149 11.2409 14.6215 11.2409 14.42Z" fill="#d97706"></path>
                            </svg>
                        </div>
                        <div class="gsc-modal-title"><?php echo esc_html__('Deactivate Integration', 'gsheetconnector-for-elementor-forms'); ?></div>
                        <p class="gsc-modal-text"><?php echo esc_html__('Are you sure you want to deactivate Google Sheets integration?
                        This will stop syncing your form entries.', 'gsheetconnector-for-elementor-forms'); ?>
                    </p>

                    <div class="popup-actions d-flex justify-center gap-10">
                        <button type="button" class="btn btn-deactivate" id="gselef-deactive-popup-free-cancel">
                            <?php echo esc_html__('Cancel', 'gsheetconnector-for-elementor-forms'); ?>
                        </button>
                        <button type="button" class="btn btn-primary" id="gselef-deactive-popup-free-confirm">
                            <?php echo esc_html__('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>