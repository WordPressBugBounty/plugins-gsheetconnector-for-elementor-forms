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
<input type="hidden" name="redirect_auth_wc" id="redirect_auth_wc" value="<?php echo isset($header) ? esc_attr($header) : ''; ?>">
<div class="elem-gs-form">
  <div class="card" id="elem-googlesheet">
	  
	  
	<div class="box-spacer">
    <h2><?php echo esc_html(__('Google Sheet Integration - GSheetConnector For Elementor Forms', 'gsheetconnector-for-elementor-forms')); ?> </h2>
	  
	  <p class="sub-desc"><?php 
echo wp_kses_post( __(
    'Choose your Google API Setting from the dropdown. You can select Use Existing Client/Secret Key (Auto Google API Configuration) or Use Manual Client/Secret Key (Use Your Google API Configuration - Pro Version) or Use Service Account (Recommended- Pro Version) . After saving, the related integration settings will appear, and you can complete the setup.',
    'gsheetconnector-for-elementor-forms'
) );
?></p> 
	   
    <div class="row">
      <label for="ele_dro_option" class="ele_gapi"><?php echo esc_html(__('Choose Google API Setting', 'gsheetconnector-for-elementor-forms')); ?></label>
      <select id="ele_dro_option" name="ele_dro_option">
            <option value="elegs_html_existing" selected><?php echo esc_html__('Use Existing Client/Secret Key (Auto Google API Configuration)', 'gsheetconnector-for-elementor-forms'); ?></option>
            <option value="elegs_manual" disabled=""><?php echo esc_html__('Use Manual Client/Secret Key (Use Your Google API Configuration) (Upgrade To PRO)', 'gsheetconnector-for-elementor-forms'); ?></option>
             <option value="elegs_service" disabled=""><?php echo esc_html__('Service Account (Recommended) (Upgrade To PRO)', 'gsheetconnector-for-elementor-forms'); ?></option>
      </select>
      <p class="int-meth-btn-ele">
        <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank">
            <input type="button" name="save-method-api-element" id="save-method-api-element"
                   value="<?php esc_html_e('Upgrade to PRO', 'gsheetconnector-for-elementor-forms'); ?>" class="upgrade-btn" />
        </a>

        <span class="loading-sign-method-api">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </p>
    </div>
		
		
		</div>  
   <!-- box -->
  <?php
  // Generate nonce for later verification
  $nonce = wp_create_nonce('gs-ajax-nonce-ele');
  ?>
  <!-- Input: redirect_auth_eleforms -->
  <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?>
  <input type="hidden" name="redirect_auth_eleforms" id="redirect_auth_eleforms"
         value="<?php echo esc_url($header ?? ''); ?>">

  <!-- Input: get_code -->
  <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>
  <?php
  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $get_code = ! empty( $_GET['code'] ) ? '1' : '0';
  ?>

  <input type="hidden" name="get_code" id="get_code" value="<?php echo esc_attr( $get_code ); ?>">
  <!-- Nonce Input -->
  <?php // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash ?>
  <input type="hidden" name="gs-ajax-nonce-ele" id="gs-ajax-nonce-ele"
         value="<?php echo esc_attr($nonce); ?>">

    <div class="card-wp ele_apiesc_html_existing_setting">
      <div class="elem-inside">
        <?php if (empty($token) && $token == "") { ?>
        <?php if (empty($Code)) { ?>
        <div class="gscelementor-alert-kk" id="google-drive-msg">
          <p class="gscelementor-alert-heading"><?php echo esc_html__('To authenticate with your Google account, follow these steps:', 'gsheetconnector-for-elementor-forms'); ?></p>
          <ol class="gscelementor-alert-steps">
            <li><?php echo esc_html__('Click on the "Sign In With Google" button.', 'gsheetconnector-for-elementor-forms'); ?></li>
            <li><?php echo esc_html__('Grant permissions for the following:', 'gsheetconnector-for-elementor-forms'); ?>
              <ul class="gscelementor-alert-permissions">
                <li><?php echo esc_html__('Google Drive', 'gsheetconnector-for-elementor-forms'); ?></li>
                <li><?php echo esc_html__('Google Sheets', 'gsheetconnector-for-elementor-forms'); ?> <span><?php echo esc_html__('* Ensure that you enable the checkbox for each of these services.', 'gsheetconnector-for-elementor-forms'); ?></span></li>
              </ul>
            </li>
            <li><?php echo esc_html__('This will allow the integration to access your Google Drive and Google Sheets.', 'gsheetconnector-for-elementor-forms'); ?></li>
          </ol>
        </div>
        <?php } ?>
        <?php } ?>
        <div class="gs-integration-box row">
          <label><?php echo esc_html(__('Google Access Code', 'gsheetconnector-for-elementor-forms')); ?></label>
          <?php if (!empty($token) && $token !== "") { ?>
          <input type="text" name="ele-code" id="ele-code" value="" disabled
                        placeholder="<?php echo esc_html(__('Currently Active', 'gsheetconnector-for-elementor-forms')); ?>" />
          <input type="button" name="deactivate-log-ele" id="deactivate-log-ele"
                        value="<?php esc_html_e('Deactivate', 'gsheetconnector-for-elementor-forms'); ?>" class="button button-primary" />
          
          <span class="loading-sign-deactive">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

          <?php } else { 
                    $redirct_uri = admin_url( 'admin.php?page=gsheetconnector-elementor-config' );
    ?>
          <input type="text" name="ele-code" id="ele-code" value="<?php echo esc_attr($Code); ?>"
                        placeholder="<?php echo esc_html(__('Click Sign in with Google ->', 'gsheetconnector-for-elementor-forms')); ?>"disabled />
          <?php if (empty($Code)) { ?>
          <a href="<?php echo esc_url( 'https://oauth.gsheetconnector.com/index.php?client_admin_url=' . urlencode( $redirct_uri ) . '&plugin=woocommercegsheetconnector' ); ?>"
             style="position:relative; top:3px;">
             <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
              <img src="<?php echo esc_url( GS_CONN_ELE_URL . '/assets/img/btn_google_signin_dark_pressed_web.gif' ); ?>">
          </a>

          <?php } ?>
          <?php } ?>
          <?php 
          // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback, nonce not required
          if (!empty($_GET['code'])) { ?>
          <button type="button" name="save-ele-code" id="save-ele-code"><?php echo esc_html(__('Save & Authenticate', 'gsheetconnector-for-elementor-forms')); ?></button>
          <?php } ?>
          <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> 
		  </div>
		  
		  
		  
        <?php
        //resolved - google sheet permission issues - START
       if (!empty(get_option('elefgs_verify')) && (get_option('elefgs_verify') == "invalid-auth")) {
                        ?>
        <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;">
         <?php echo  esc_html(__('Something went wrong! It looks you have not given the permission of Google Drive and Google Sheets from your google account.Please Deactivate Auth and Re-Authenticate again with the permissions.', 'gsheetconnector-for-elementor-forms')); ?>
        </p>

        <p style="color:#c80d0d;border: 1px solid;padding: 8px;">
          <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
            <img width="350px" src="<?php echo esc_url( GS_CONN_ELE_URL . 'assets/img/permission_screen.png' ); ?>">
        </p>

        <p style="color:#c80d0d; font-size: 14px; border: 1px solid;padding: 8px;"> <?php echo esc_html(__('Also,', 'gsheetconnector-for-elementor-forms')); ?><a href="https://myaccount.google.com/permissions"
                        target="_blank"> 
       <?php echo esc_html(__('Click Here ', 'gsheetconnector-for-elementor-forms')); ?></a> 
       <?php echo esc_html(__('and if it displays "GSheetConnector for WordPress Contact Forms" under Third-party apps with account access then remove it.', 'gsheetconnector-for-elementor-forms')); ?> 
       </p>
        <?php
        } else {
            // connected-email-account
            $token = get_option('elefgs_token');
            if (!empty($token) && $token !== "") {
                $google_sheet = new GSC_Elementor_Free();
                $email_account = $google_sheet->gsheet_print_google_account_email();

                if ($email_account) {
                    ?>
                    <div class="connected-account row">
						<label><?php
                        $raw_output = sprintf(
                            // translators: %s is the connected email address.
                            __( 'Connected Email Account', 'gsheetconnector-for-elementor-forms' ),
                            esc_html( $email_account )
                        );

                        echo wp_kses( $raw_output, array( 'u' => array() ) );
                        ?></label>
                        <?php
                        $raw_output = sprintf(
                            // translators: %s is the connected email address.
                            __( '<span>%s</span>', 'gsheetconnector-for-elementor-forms' ),
                            esc_html( $email_account )
                        );

                        echo wp_kses( $raw_output, array( 'u' => array() ) );
                        ?>
                    </div>
                    <?php
                } else {
                    // If email not returned — show error + activation fix button
                    ?>
                    <p class="notice-gsc" style="color:red">
                        <?php echo esc_html(__('Authentication Error:
                          It seems your authorization code is either incorrect or has expired. Please follow these steps to resolve it:

                          Click the Run Activation button.

                          Deactivate the current authentication.

                          Then, Re-authenticate to generate a new valid token.', 'gsheetconnector-for-elementor-forms')); ?>
                    </p>
                    <p>
                        <?php
                        $fix_url = wp_nonce_url(
                          admin_url('admin.php?page=gsheetconnector-elementor-config&run_upgrade_fix=1'),
                            'run_upgrade_fix_action'
                        );
                        ?>
                        <a href="<?php echo esc_url($fix_url); ?>" class="button button-secondary">
                            <?php echo esc_html(__('Run Activation Fix', 'gsheetconnector-for-elementor-forms')); ?>
                        </a>
                    </p>
                    <?php
                }
            }
        }
        ?>

        <?php 
          if(!empty(get_option('elefgs_verify')) && (get_option('elefgs_verify') =="valid")){ ?>
              <p class="ele-sync-row">
                <?php
                $message = __('Spreadsheet Name and URL not showing? <a id="ele-sync" data-init="yes">Click here</a> to fetch sheets.', 'gsheetconnector-for-elementor-forms');

                echo wp_kses(
                    $message,
                    array(
                        'a' => array(
                            'id' => true,
                            'data-init' => true,
                        ),
                    )
                );
                ?>
                <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </p>
        <?php } ?>

       <div class="msg success-msg">
    <i class="fa-solid fa-lock"></i>
    <p><?php echo esc_html__('We do not store any of the data from your Google account on our servers, everything is processed &amp; stored on your server. We take your privacy extremely seriously and ensure it is never misused.', 'gsheetconnector-for-elementor-forms'); ?>
     <a href="https://gsheetconnector.com/usage-tracking/" target="_blank" rel="noopener noreferrer">
            <?php echo esc_html__('Learn more', 'gsheetconnector-for-elementor-forms'); ?>. </a>
    </p>
</div>



        <p>
          <label><?php esc_html_e( 'Debug Log →', 'gsheetconnector-for-elementor-forms' ); ?></label>

          <button class="elemnt-logs">
            <?php esc_html_e( 'View', 'gsheetconnector-for-elementor-forms' ); ?>
          </button>

         
          <label>
            <a href="#" class="debug-clear-elementor">
              <?php esc_html_e( 'Clear', 'gsheetconnector-for-elementor-forms' ); ?>
            </a>
          </label>

          <span class="clear-loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </p>

        <p id="gs-validation-message"></p>
        <span id="deactivate-message"></span>

        <div class="elemnt-system-Error-logs">
          <button id="copy-logs-btn" class="copy-log" onclick="copyLogs()">
            <?php esc_html_e( 'Copy Logs', 'gsheetconnector-for-elementor-forms' ); ?>
          </button>

          <div class="elemntdisplayLogs">
            <?php
            $existDebugFile = get_option( 'ele_gs_debug_log_file' );

            if ( ! empty( $existDebugFile ) && file_exists( $existDebugFile ) ) {
              $displayelemntfreeLogs = file_get_contents( $existDebugFile );

              if ( ! empty( $displayelemntfreeLogs ) ) {
                // Use esc_html with nl2br to safely display multiline logs
                echo wp_kses_post( nl2br( esc_html( $displayelemntfreeLogs ) ) );
              } else {
                esc_html_e( 'No errors found.', 'gsheetconnector-for-elementor-forms' );
              }
            } else {
              esc_html_e( 'No log file exists as no errors are generated.', 'gsheetconnector-for-elementor-forms' );
            }
            ?>
          </div>
        </div>
</div>
</div>
</div>

<!-- my code -->
 
<div class="two-col ele-free-box-help12">
  <div class="col ele-free-box12">
    <header>
      <h3>
        <?php
        // Translators: Section heading "Next steps…"
        echo esc_html__( 'Next steps…', 'gsheetconnector-for-elementor-forms' );
        ?>
      </h3>
    </header>

    <div class="ele-free-box-content12">
      <ul class="ele-free-list-icon12">
        <li>
          <a href="https://www.gsheetconnector.com/elementor-forms-google-sheet-connector-pro" target="_blank">
            <div>
              <button class="icon-button">
                <span class="dashicons dashicons-star-filled"></span>
              </button>
              <strong><?php echo esc_html__( 'Upgrade to PRO', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'Sync Orders, Order wise data and much more...', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>

        <li>
          <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/requirements" target="_blank">
            <div>
              <button class="icon-button">
                <span class="dashicons dashicons-download"></span>
              </button>
              <strong><?php echo esc_html__( 'Compatibility', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'Compatibility with WooCommerce Third-Party Plugins', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>

        <li>
          <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/plugin-settings-pro-version" target="_blank">
            <div>
              <button class="icon-button">
                <span class="dashicons dashicons-chart-bar"></span>
              </button>
              <strong><?php echo esc_html__( 'Multi Languages', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'This plugin supports multi-languages as well!', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>

        <li>
          <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector/plugin-feed-setting-free-version" target="_blank">
            <div>
              <button class="icon-button">
                <span class="dashicons dashicons-download"></span>
              </button>
              <strong><?php echo esc_html__( 'Support WordPress multisites', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'With the use of a Multisite, you’ll also have a new level of user-available: the Super Admin.', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <!-- 2nd column -->
  <div class="col ele-free-box13">
    <header>
      <h3>
        <?php
        // Translators: Section heading "Product Support"
        echo esc_html__( 'Product Support', 'gsheetconnector-for-elementor-forms' );
        ?>
      </h3>
    </header>

    <div class="ele-free-box-content13">
      <ul class="ele-free-list-icon13">
        <li>
          <a href="https://www.gsheetconnector.com/docs/elementor-google-sheet-connector" target="_blank">
            <div>
              <span class="dashicons dashicons-book"></span>
              <strong><?php echo esc_html__( 'Online Documentation', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'Understand all the capabilities of Woocommerce GsheetConnector', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>

        <li>
          <a href="https://www.gsheetconnector.com/support" target="_blank">
            <div>
              <span class="dashicons dashicons-sos"></span>
              <strong><?php echo esc_html__( 'Ticket Support', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'Direct help from our qualified support team', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>

        <li>
          <a href="https://www.gsheetconnector.com/affiliates" target="_blank">
            <div>
              <span class="dashicons dashicons-admin-links"></span>
              <strong><?php echo esc_html__( 'Affiliate Program', 'gsheetconnector-for-elementor-forms' ); ?></strong>
              <p><?php echo esc_html__( 'Earn flat 30% on every sale!', 'gsheetconnector-for-elementor-forms' ); ?></p>
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>

 <!-- my code end -->
</div>

<script>
     function copyLogs() {
        // Get the log content from the element
        var logContentElement = document.getElementById('log-content');
        if (logContentElement) {
            var logContent = logContentElement.innerText || logContentElement.textContent;

            // Use the clipboard API to copy the log content
            navigator.clipboard.writeText(logContent).then(function() {
                alert('Logs copied to clipboard!');
            }).catch(function(err) {
                alert('Failed to copy logs: ' + err);
            });
        } else {
            alert('No logs to copy!');
        }
    }
</script>