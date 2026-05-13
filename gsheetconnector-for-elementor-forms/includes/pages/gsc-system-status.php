<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
  exit;
}

// 🔒 Prevent Subscribers from seeing sensitive info
if (! current_user_can('manage_options')) {
  wp_die(
    esc_html__('You do not have permission to access this page.', 'gsheetconnector-for-elementor-forms')
  );
}

$elementorForms_gs_tools_service = new GSC_Elementor_Init();

?>


<div class="wrap w-100 m-0">
  <div class="info-container inner-wrap w-100 bg-white p-40">
    <div class="heading mt-0"><?php esc_html_e('System Information', 'gsheetconnector-for-elementor-forms'); ?></div>
    <p><?php echo esc_html__('View detailed information about your plugin, server, and WordPress setup for troubleshooting and support.
    ', 'gsheetconnector-for-elementor-forms'); ?></p>
    <div class="text-right d-flex justify-end">
      <button id="gselef-free-system-copy" class="btn btn-primary d-flex align-center gap-10"><svg width="18"
        height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
        class="gsc-copy-icon">
        <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"></rect>
        <rect x="2" y="2" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"></rect>
        </svg><?php esc_html_e('Copy System Info', 'gsheetconnector-for-elementor-forms'); ?>
      </button>
    </div>

    <div id="system-info-wrapper">
      <?php
      global $wpdb;

    // Get WordPress version.
      $wp_version = get_bloginfo('version');

    // Get theme info.
      $theme_data = wp_get_theme();
      $theme_name_version = $theme_data->get('Name') . ' ' . $theme_data->get('Version');
      $parent_theme = $theme_data->get('Template');

      if (!empty($parent_theme)) {
        $parent_theme_data = wp_get_theme($parent_theme);
        $parent_theme_name_version = $parent_theme_data->get('Name') . ' ' . $parent_theme_data->get('Version');
      } else {
        $parent_theme_name_version = 'N/A';
      }

    // Check plugin version and subscription plan.
      $plugin_version = defined('GS_CONN_ELE_VERSION') ? GS_CONN_ELE_VERSION : 'N/A';

      $subscription_plan = 'FREE';

      $api_token_auto = get_option('elefgs_token');
      $auth = "";
      $gsc_gf_is_valid = get_option('elefgs_verify');

      if ((!empty($api_token_auto) && $gsc_gf_is_valid == 'valid')) {
        $google_sheet_auto = new GSC_Elementor_Free();
        $email_account_auto = $google_sheet_auto->gsheet_print_google_account_email();
        $connected_email = !empty($email_account_auto) ? esc_html($email_account_auto) : 'Not Auth';

        $auth = 'Authenticated Using Existing Method';
      } else {
      // Auto authentication is the  method available.
        $connected_email = 'Not Connected';
        $auth = 'No Method';
      }

      /*  Check Google Permission. */
      $gsc_verify_status = get_option('elefgs_verify');
      $search_permission = ($gsc_verify_status === 'valid') ? 'Granted' : 'Denied';
      /*  Create the system info HTML. */
      ?>

      <div class="system-statuswc">
        <div class="mb-20 mt-20">
          <button id="gselef-free-show-info-button" class="info-button">
            <?php echo esc_html__('GSheetConnector Status', 'gsheetconnector-for-elementor-forms'); ?>
            <span class="dashicons dashicons-arrow-down"></span>
          </button>
        </div>

        <div id="gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
          <table>
            <tr>
              <td><?php echo esc_html__('Plugin Name', 'gsheetconnector-for-elementor-forms'); ?></td>
              <td class="fw-600 common-badge-table info-name-blue">
                <?php echo esc_html('GSheetConnector For Elementor Forms'); ?> </td>
              </tr>
              <tr>
                <td><?php echo esc_html__('Plugin Version', 'gsheetconnector-for-elementor-forms'); ?></td>
                <td class="fw-600 common-badge-table info-name-blue">
                  <?php echo esc_html($plugin_version); ?>
                </td>
              </tr>

              <tr>
                <td><?php echo esc_html__('Plugin Subscription Plan', 'gsheetconnector-for-elementor-forms'); ?></td>
                <td class="fw-600 common-badge-table pro-badge">
                  <?php echo esc_html($subscription_plan); ?>
                </td>
              </tr>

              <tr>
                <td><?php echo esc_html__('Connected Email Account', 'gsheetconnector-for-elementor-forms'); ?></td>
                <td class="fw-600">
                  <?php echo esc_html($connected_email); ?>
                </td>
              </tr>

              <tr>
                <td><?php echo esc_html__('Authentication method for connecting to Google Sheets', 'gsheetconnector-for-elementor-forms'); ?>
              </td>
              <td class="fw-600">
                <?php echo esc_html($auth); ?>
              </td>
            </tr>
            <?php
            $permission_class = ($search_permission === 'Granted') ? 'permission-given' : 'permission-not-given';
            ?>

            <tr>
              <td> <?php echo esc_html__('Google Drive Permission', 'gsheetconnector-for-elementor-forms'); ?></td>
              <td class="fw-700 permission-badge <?php echo esc_attr($permission_class); ?>">
                <?php echo esc_html($search_permission); ?>
              </td>
            </tr>

            <tr>
              <td><?php echo esc_html__('Google Sheet Permission', 'gsheetconnector-for-elementor-forms'); ?></td>
              <td class="fw-700 permission-badge <?php echo esc_attr($permission_class); ?>">
                <?php echo  esc_html($search_permission); ?>
              </td>
            </tr>
          </table>

        </div>



        <div class="mb-20 mt-20">
          <button id="gselef-free-show-wordpress-info-button" class="info-button">
            <?php echo esc_html__('WordPress', 'gsheetconnector-for-elementor-forms'); ?>
            <span class="dashicons dashicons-arrow-down"></span>
          </button>
        </div>

        <div id="wordpress-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30"
        style="display:none;">
        <table>

          <tr>
            <td><?php echo esc_html__('Version', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
              <?php echo esc_html(get_bloginfo('version')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Site Language', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600">
              <?php echo esc_html(get_bloginfo('language')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Debug Mode', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600 common-badge-table info-name-yellow">
              <?php echo esc_html(WP_DEBUG ? __('Enabled', 'gsheetconnector-for-elementor-forms') : __('Disabled', 'gsheetconnector-for-elementor-forms')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Home URL', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
              <?php echo esc_url(get_home_url()); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Site URL', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600 common-badge-table info-name-blue">
              <?php echo esc_url(get_site_url()); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Permalink structure', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600">
              <?php echo esc_html(get_option('permalink_structure')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Is this site using HTTPS?', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600">
              <?php echo esc_html(is_ssl() ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Is this a multisite?', 'gsheetconnector-for-elementor-forms'); ?></td>
            <td class="fw-600">
              <?php echo esc_html(is_multisite() ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
            </td>
          </tr>

          <tr>
            <td><?php echo esc_html__('Can anyone register on this site?', 'gsheetconnector-for-elementor-forms'); ?>
          </td>
          <td class="fw-600">
            <?php echo esc_html(get_option('users_can_register') ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
          </td>
        </tr>

        <tr>
          <td><?php echo esc_html__('Is this site discouraging search engines?', 'gsheetconnector-for-elementor-forms'); ?>
        </td>
        <td class="fw-600">
          <?php echo esc_html(get_option('blog_public') ? __('No', 'gsheetconnector-for-elementor-forms') : __('Yes', 'gsheetconnector-for-elementor-forms')); ?>
        </td>
      </tr>

      <tr>
        <td><?php echo esc_html__('Default comment status', 'gsheetconnector-for-elementor-forms'); ?></td>
        <td class="fw-600">
          <?php echo esc_html(get_option('default_comment_status')); ?>
        </td>
      </tr>
      <?php
      $server_ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP) : '';

      if (filter_var($server_ip, FILTER_VALIDATE_IP) === false) {
        $environment_type = __('Unknown', 'gsheetconnector-for-elementor-forms');
      } else {
        $known_local_ips = array('127.0.0.1', '::1');
        $isLocalhost = in_array($server_ip, $known_local_ips, true);
        $environment_type = $isLocalhost
        ? __('Localhost', 'gsheetconnector-for-elementor-forms')
        : __('Production', 'gsheetconnector-for-elementor-forms');
      }
      ?>

      <tr>
        <td><?php echo esc_html__('Environment type', 'gsheetconnector-for-elementor-forms'); ?></td>
        <td class="fw-600 common-badge-table info-name-yellow">
          <?php echo esc_html($environment_type); ?>
        </td>
      </tr>

      <?php
      $user_count  = count_users();
      $total_users = isset($user_count['total_users']) ? (int) $user_count['total_users'] : 0;
      ?>

      <tr>
        <td><?php echo esc_html__('User Count', 'gsheetconnector-for-elementor-forms'); ?></td>
        <td class="fw-600">
          <?php echo esc_html($total_users); ?>
        </td>
      </tr>

      <tr>
        <td><?php echo esc_html__('Communication with WordPress.org', 'gsheetconnector-for-elementor-forms'); ?>
      </td>
      <td class="fw-600">
        <?php echo esc_html(get_option('blog_publicize') ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>
  </table>
</div>
<!-- Active Theme Information -->
<?php
$active_theme = wp_get_theme();
?>
<div class="mb-20 mt-20">
  <button id="gselef-free-show-active-theme-button"
  class="info-button"><?php echo esc_html__('Active Theme', 'gsheetconnector-for-elementor-forms'); ?>
  <span class="dashicons dashicons-arrow-down"></span>
</button>
</div>

<div id="active-gselef-free-theme-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30"
style="display:none;">
<table>
  <tr>
    <td><?php echo esc_html__('Name', 'gsheetconnector-for-elementor-forms'); ?></td>
    <td class="fw-600 common-badge-table info-name-blue">
      <?php echo esc_html($active_theme->get('Name')); ?>
    </td>
  </tr>

  <tr>
    <td><?php echo esc_html__('Version', 'gsheetconnector-for-elementor-forms'); ?></td>
    <td class="fw-600 common-badge-table info-name-blue">
      <?php echo esc_html($active_theme->get('Version')); ?>
    </td>
  </tr>

  <tr>
    <td><?php echo esc_html__('Author', 'gsheetconnector-for-elementor-forms'); ?></td>
    <td class="fw-600">
      <?php echo wp_kses_post($active_theme->get('Author')); ?>
    </td>
  </tr>

  <tr>
    <td><?php echo esc_html__('Author website', 'gsheetconnector-for-elementor-forms'); ?></td>
    <td class="fw-600">
      <?php echo esc_url($active_theme->get('AuthorURI')); ?>
    </td>
  </tr>

  <tr>
    <td><?php echo esc_html__('Theme directory location', 'gsheetconnector-for-elementor-forms'); ?></td>
    <td class="fw-600">
      <?php echo esc_html($active_theme->get_template_directory()); ?>
    </td>
  </tr>

</table>
</div>
<?php
    // Get a list of other plugins you want to check compatibility with.
$other_plugins = array(
      'plugin-folder/plugin-file.php', // Replace with the actual plugin slug
      // Add more plugins as needed.
    );
$active_plugins = get_option('active_plugins', array());
    // Network Active Plugins.
if (is_multisite()) {
  $network_active_plugins = get_site_option('active_sitewide_plugins', array());
  if (!empty($network_active_plugins)) { ?>
    <div class="mb-20 mt-20"><button id="gscgff-show-netplug-info-button" class="info-button">Network Active
      plugins<span class="dashicons dashicons-arrow-down"></span></button></div>';
      <div id="netplug-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
        ';
        <table>
          <?php
          foreach ($network_active_plugins as $plugin => $plugin_data) {

            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

            echo '<tr>
            <td>' . esc_html($plugin_data['Name']) . '</td>
            <td>' . esc_html($plugin_data['Version']) . '</td>
            </tr>';
          }
          ?>
        </table>
      </div>
      <?php
    }
  }

  $total_active_plugins = is_array($active_plugins) ? count($active_plugins) : 0;
  ?>
  <div class="mb-20 mt-20">
    <button id="gselef-free-show-acplug-info-button" class="info-button">
      <?php echo esc_html__('Active Plugins', 'gsheetconnector-for-elementor-forms'); ?>
      (<?php echo esc_html($total_active_plugins); ?>)
      <span class="dashicons dashicons-arrow-down"></span>
    </button>
  </div>

  <div id="acplug-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
    <table>
      <?php
      /* Retrieve all active plugins data */
      $active_plugins_data = array();
      $active_plugins = get_option('active_plugins', array());

      foreach ($active_plugins as $plugin) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
        $active_plugins_data[$plugin] = array(
          'name' => $plugin_data['Name'],
          'version' => $plugin_data['Version'],
          'count' => 0, /* Initialize the count to zero */
        );
      }

      /* Count the number of active installations for each plugin */
      $all_plugins = get_plugins();
      foreach ($all_plugins as $plugin_file => $plugin_data) {
        if (array_key_exists($plugin_file, $active_plugins_data)) {
          $active_plugins_data[$plugin_file]['count']++;
        }
      }

      /* Sort plugins based on the number of active installations (descending order) */
      uasort($active_plugins_data, function ($a, $b) {
        return $b['count'] - $a['count'];
      });

      foreach ($active_plugins_data as $plugin_data) { ?>
        <tr>
          <td><?php echo esc_html($plugin_data['name']); ?></td>
          <td class="fw-600 common-badge-table info-name-blue">
            <?php echo esc_html($plugin_data['version']); ?>
          </td>
        </tr>
      <?php } ?>
    </table>
  </div>

    <!-- Webserver Configuration
    -->

    <div class="mb-20 mt-20">
      <button id="gselef-free-show-server-info-button" class="info-button">
        <?php echo esc_html__('Server', 'gsheetconnector-for-elementor-forms'); ?>
        <span class="dashicons dashicons-arrow-down"></span>
      </button>
    </div>

    <div id="server-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">

      <p class="text-dark">
        <b>
          <?php echo esc_html__(
            'The options shown below relate to your server setup. If changes are required, you may need your web host’s assistance.',
            'gsheetconnector-for-elementor-forms'
          ); ?>
        </b>
      </p>

      <table>
        <tr>
          <td><?php echo esc_html__('Server Architecture', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(php_uname('s')); ?></td>
        </tr>

        <?php
        $server_software = isset($_SERVER['SERVER_SOFTWARE'])
        ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))
        : '';
        ?>
        <tr>
          <td><?php echo esc_html__('Web Server', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html($server_software); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP Version', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600 common-badge-table info-name-blue"><?php echo esc_html(PHP_VERSION); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP SAPI', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(php_sapi_name()); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP Max Input Variables', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(ini_get('max_input_vars')); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP Time Limit', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600">
            <?php echo esc_html(ini_get('max_execution_time')); ?>
            <?php echo esc_html__('seconds', 'gsheetconnector-for-elementor-forms'); ?>
          </td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP Memory Limit', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(ini_get('memory_limit')); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('Max Input Time', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600">
            <?php echo esc_html(ini_get('max_input_time')); ?>
            <?php echo esc_html__('seconds', 'gsheetconnector-for-elementor-forms'); ?>
          </td>
        </tr>

        <tr>
          <td><?php echo esc_html__('Upload Max Filesize', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('PHP Post Max Size', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html(ini_get('post_max_size')); ?></td>
        </tr>

        <?php
        $curl_version = function_exists('curl_version') ? curl_version() : array();
        $curl_display = isset($curl_version['version']) ? $curl_version['version'] : __('Not Installed', 'gsheetconnector-for-elementor-forms');
        ?>

        <tr>
          <td><?php echo esc_html__('cURL Version', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600"><?php echo esc_html($curl_display); ?></td>
        </tr>

        <tr>
          <td><?php echo esc_html__('Is SUHOSIN Installed?', 'gsheetconnector-for-elementor-forms'); ?></td>
          <td class="fw-600">
            <?php echo esc_html(extension_loaded('suhosin') ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
          </td>
        </tr>

        <tr>
          <td><?php echo esc_html__('Is the Imagick Library Available?', 'gsheetconnector-for-elementor-forms'); ?>
        </td>
        <td class="fw-600">
          <?php echo esc_html(extension_loaded('imagick') ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
        </td>
      </tr>

      <tr>
        <td><?php echo esc_html__('Are Pretty Permalinks Supported?', 'gsheetconnector-for-elementor-forms'); ?>
      </td>
      <td class="fw-600">
        <?php echo esc_html(get_option('permalink_structure') ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <?php
    global $wp_filesystem;

    if (empty($wp_filesystem)) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
      WP_Filesystem();
    }

    $htaccess_path = ABSPATH . '.htaccess';
    $is_writable = (isset($wp_filesystem) && $wp_filesystem->exists($htaccess_path) && $wp_filesystem->is_writable($htaccess_path));
    ?>

    <tr>
      <td><?php echo esc_html__('.htaccess Rules', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($is_writable ? __('Writable', 'gsheetconnector-for-elementor-forms') : __('Not Writable', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Current Time', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600"><?php echo esc_html(current_time('mysql')); ?></td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Current UTC Time', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600"><?php echo esc_html(current_time('mysql', true)); ?></td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Current Server Time', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600"><?php echo esc_html(gmdate('Y-m-d H:i:s')); ?></td>
    </tr>

  </table>
</div>

<!-- Database Configuration -->

<div class="mb-20 mt-20">
  <button id="gselef-free-show-database-info-button" class="info-button">
    <?php echo esc_html__('Database', 'gsheetconnector-for-elementor-forms'); ?>
    <span class="dashicons dashicons-arrow-down"></span>
  </button>
</div>

<div id="database-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
  <table>
    <?php
    $database_extension = 'mysqli';
    $database_server_version = $wpdb->get_var('SELECT VERSION() as version');
    $database_client_version = $wpdb->db_version();
    $database_username = DB_USER;
    $database_host = DB_HOST;
    $database_name = DB_NAME;
    $table_prefix = $wpdb->prefix;
    $database_charset = $wpdb->charset;
    $database_collation = $wpdb->collate;
    $max_allowed_packet_size = $wpdb->get_var("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $max_connections_number = $wpdb->get_var("SHOW VARIABLES LIKE 'max_connections'");
    ?>
    <tr>
      <td><?php echo esc_html__('Extension', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($database_extension); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Server Version', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600 common-badge-table info-name-blue">
        <?php echo esc_html($database_server_version); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Client Version', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600 common-badge-table info-name-blue">
        <?php echo esc_html($database_client_version); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Database Username', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($database_username); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Database Host', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600 common-badge-table info-name-yellow">
        <?php echo esc_html($database_host); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Database Name', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600 common-badge-table info-name-blue">
        <?php echo esc_html($database_name); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Table Prefix', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($table_prefix); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Database Charset', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($database_charset); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Database Collation', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($database_collation); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Max Allowed Packet Size', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($max_allowed_packet_size); ?>
      </td>
    </tr>

    <tr>
      <td><?php echo esc_html__('Max Connections Number', 'gsheetconnector-for-elementor-forms'); ?></td>
      <td class="fw-600">
        <?php echo esc_html($max_connections_number); ?>
      </td>
    </tr>
  </table>
</div>

<!-- WordPress constants -->
<div class="mb-20 mt-20">
  <button id="gselef-free-show-wrcons-info-button" class="info-button">
    <?php echo esc_html__('WordPress Constants', 'gsheetconnector-for-elementor-forms'); ?>
    <span class="dashicons dashicons-arrow-down"></span>
  </button>
</div>

<div id="wrcons-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">
  <table>

    <tr>
      <td>ABSPATH</td>
      <td class="fw-600"><?php echo esc_html(ABSPATH); ?></td>
    </tr>

    <tr>
      <td>WP_HOME</td>
      <td class="fw-600 common-badge-table info-name-blue">
        <?php echo esc_url(home_url()); ?>
      </td>
    </tr>

    <tr>
      <td>WP_SITEURL</td>
      <td class="fw-600"><?php echo esc_url(site_url()); ?></td>
    </tr>

    <tr>
      <td>WP_CONTENT_DIR</td>
      <td class="fw-600"><?php echo esc_html(WP_CONTENT_DIR); ?></td>
    </tr>

    <tr>
      <td>WP_PLUGIN_DIR</td>
      <td class="fw-600"><?php echo esc_html(WP_PLUGIN_DIR); ?></td>
    </tr>

    <tr>
      <td>WP_MEMORY_LIMIT</td>
      <td class="fw-600"><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
    </tr>

    <tr>
      <td>WP_MAX_MEMORY_LIMIT</td>
      <td class="fw-600"><?php echo esc_html(WP_MAX_MEMORY_LIMIT); ?></td>
    </tr>

    <tr>
      <td>WP_DEBUG</td>
      <td class="fw-600">
        <?php echo esc_html((defined('WP_DEBUG') && WP_DEBUG) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>WP_DEBUG_DISPLAY</td>
      <td class="fw-600">
        <?php echo esc_html((defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>SCRIPT_DEBUG</td>
      <td class="fw-600">
        <?php echo esc_html((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>WP_CACHE</td>
      <td class="fw-600">
        <?php echo esc_html((defined('WP_CACHE') && WP_CACHE) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>CONCATENATE_SCRIPTS</td>
      <td class="fw-600">
        <?php echo esc_html((defined('CONCATENATE_SCRIPTS') && CONCATENATE_SCRIPTS) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>COMPRESS_SCRIPTS</td>
      <td class="fw-600">
        <?php echo esc_html((defined('COMPRESS_SCRIPTS') && COMPRESS_SCRIPTS) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>COMPRESS_CSS</td>
      <td class="fw-600">
        <?php echo esc_html((defined('COMPRESS_CSS') && COMPRESS_CSS) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <?php
    $environment_type = function_exists('wp_get_environment_type')
    ? wp_get_environment_type()
    : 'production';
    ?>

    <tr>
      <td>WP_ENVIRONMENT_TYPE</td>
      <td class="fw-600"><?php echo esc_html($environment_type); ?></td>
    </tr>

    <tr>
      <td>WP_DEVELOPMENT_MODE</td>
      <td class="fw-600">
        <?php echo esc_html((defined('WP_DEVELOPMENT_MODE') && WP_DEVELOPMENT_MODE) ? __('Yes', 'gsheetconnector-for-elementor-forms') : __('No', 'gsheetconnector-for-elementor-forms')); ?>
      </td>
    </tr>

    <tr>
      <td>DB_CHARSET</td>
      <td class="fw-600"><?php echo esc_html(DB_CHARSET); ?></td>
    </tr>

    <tr>
      <td>DB_COLLATE</td>
      <td class="fw-600"><?php echo esc_html(DB_COLLATE); ?></td>
    </tr>

  </table>
</div>

<!--Filesystem Permission -->


<div class="mb-20 mt-20">
  <button id="gselef-free-show-ftps-info-button" class="info-button">
    <?php echo esc_html__('Filesystem Permission', 'gsheetconnector-for-elementor-forms'); ?>
    <span class="dashicons dashicons-arrow-down"></span>
  </button>
</div>

<div id="ftps-gselef-free-info-container" class="info-content shadow-box pt-20 pb-20 pl-30 pr-30" style="display:none;">

  <p class="text-dark"><b><?php echo
  esc_html__(
    'Shows whether WordPress is able to write to the directories it needs access to.',
    'gsheetconnector-for-elementor-forms'
  ); ?>
</b></p>

<table>
  <?php
  global $wp_filesystem;

  if (empty($wp_filesystem)) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
  }

  /* Safer uploads directory */
  $upload_dir = wp_get_upload_dir();
  $uploads_path = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';

  $paths = array(
    __('The main WordPress directory', 'gsheetconnector-for-elementor-forms') => ABSPATH,
    __('The wp-content directory', 'gsheetconnector-for-elementor-forms') => WP_CONTENT_DIR,
    __('The uploads directory', 'gsheetconnector-for-elementor-forms') => $uploads_path,
    __('The plugins directory', 'gsheetconnector-for-elementor-forms') => WP_PLUGIN_DIR,
    __('The themes directory', 'gsheetconnector-for-elementor-forms') => get_theme_root(),
  );

  foreach ($paths as $label => $path) {

    $writable = (
      isset($wp_filesystem) &&
      $wp_filesystem->exists($path) &&
      $wp_filesystem->is_writable($path)
    );
    ?>
    <tr>
      <td><?php echo esc_html($label); ?></td>
      <td><?php echo  esc_html($path); ?></td>
      <td class="fw-600"><?php echo
      esc_html(
        $writable
        ? __('Writable', 'gsheetconnector-for-elementor-forms')
        : __('Not Writable', 'gsheetconnector-for-elementor-forms')
      ); ?>
    </td>
  </tr>
<?php } ?>
</table>
</div>
</div>
</div>
</div>

<?php
$has_logs = $elementorForms_gs_tools_service->display_error_log(false);
?>
<div class="system-error shadow-box mt-40 p-30">
  <div class="error-container">
    <div class="error-log-head flex-wrap gap-20">
      <div class="heading mt-0 mb-0"><?php esc_html_e('Debug Log', 'gsheetconnector-for-elementor-forms'); ?>
    </div>
    <?php if ($has_logs) : ?>
      <div class="errorlog-button-list">
        <span class="clear-content-logs-msg-elemnt"></span>

        <span class="clear-loading-sign-logs-elemnt"></span>


        <button type="button" class="button btn-logs gselef-free-clear-content-logs">
          <?php esc_html_e('Clear Logs', 'gsheetconnector-for-elementor-forms'); ?></button>

          <button type="button" class="button button-primary"
          id="gselef-free-csv-info"><?php esc_html_e('Download CSV', 'gsheetconnector-for-elementor-forms'); ?></button>

          <button type="button" class="button btn-logs"
          id="gselef-free-copy-logs-info"><?php esc_html_e('Copy Logs', 'gsheetconnector-for-elementor-forms'); ?></button>
          <div class="gsc-copy-msg d-none"></div>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- <div class="gscgff-validation-message"></div> -->
  <input type="hidden" name="gs-ajax-nonce-ele" id="gs-ajax-nonce-ele"
  value="<?php echo esc_attr(wp_create_nonce('gs-ajax-nonce-ele')); ?>" />
  <div class="gsc-copy-msg d-none">
    <?php esc_html_e('copied successfully.', 'gsheetconnector-for-elementor-forms'); ?>
  </div>

  <!-- Log  Start-->
  <?php
  $has_logs = 
  $elementorForms_gs_tools_service->display_error_log(true);

  ?>
  <!--Log End -->
</div>
</div>