<?php

/*
 * Utilities class for Google Sheet Connector
 * @since       1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
 exit;
}

/**
 * Utilities class - singleton class
 * @since 1.0
 */
class GsEl_Connector_Utility
{

 private function __construct()
 {
      // Do Nothing
 }

/**
* Get the singleton instance of the GsEl_Connector_Utility class
*
* @return singleton instance of GsEl_Connector_Utility
*/
public static function instance()
{

  static $instance = NULL;
  if (is_null($instance)) {
   $instance = new GsEl_Connector_Utility();
 }
 return $instance;
}

/**
* Prints message (string or array) in the debug.log file
*
* @param mixed $message
*/
public function logger($message)
{
  if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
         // Use your internal logging function instead of error_log
    
  }
}

/**
 * Display error or success message in the admin section
*
* @param array $data containing type and message
* @return string with html containing the error message
* 
* @since 1.0 initial version
*/
public function admin_notice($data = array())
{
  $message = isset($data['message']) ? $data['message'] : '';
  $message_type = isset($data['type']) ? $data['type'] : '';
  
  switch ($message_type) {
   case 'error':
   $admin_notice = '<div id="message" class="error notice is-dismissible">';
   break;
   case 'update':
   $admin_notice = '<div id="message" class="updated notice is-dismissible">';
   break;
   case 'update-nag':
   $admin_notice = '<div id="message" class="update-nag">';
   break;
   case 'upgrade':
   $admin_notice = '<div id="message" class="error notice wpforms-gs-upgrade is-dismissible">';
   break;
   default:
   $message = __('There\'s something wrong with your code...', 'gsheetconnector-for-elementor-forms');
   $admin_notice = "<div id=\"message\" class=\"error\">";
   break;
 }

 $admin_notice .= '<p>' . esc_html( $message ) . '</p>';
 $admin_notice .= "</div>\n";

 return $admin_notice;
}

/**
* Utility function to get the current user's role
*
* @since 1.0.24
*/
public function get_current_user_role()
{
  global $wp_roles;
  foreach ($wp_roles->role_names as $role => $name):
   if (current_user_can($role))
    return $role;
endforeach;
}

/**
 * Logs debug/error messages into the plugin’s custom error log system.
 *
 * If the error log handler class exists, it stores the error in the database table.
 *
 * @param mixed $error Error message or data to be logged.
 * @return void
 */
public static function ele_gs_debug_log( $error ) {
  

  /** Insert error login in table */
  if (class_exists('gscelef_error_logs')) {
   gscelef_error_logs::log_from_debug($error);
 }
}

const CREDS_OPTION = 'Elegsc_api_creds';

/**
 * Live email option => option keeping the last connected email, which
 * survives disconnects so the user knows which account to sign in with.
 *
 * @since 1.3.9
 */
const LAST_EMAIL_OPTION = 'gsc_elementor_last_connected_email';
const LIVE_EMAIL_OPTION = 'gsc_elementor_email_account';

/**
 * One-time upgrade: encrypt the token and API credentials saved as plaintext
 * by older versions. Reads stay safe either way (decrypt() passes plaintext through).
 *
 * @since 1.3.9
 * @return void
 */
public static function maybe_migrate_credentials_encryption()
{
  if (get_option('gselef_credentials_encrypted_v1')) {
    return;
  }

  $token = get_option('elefgs_token');
  if (!empty($token) && is_string($token) && !GSC_Elementor_Credential_Crypto::is_encrypted($token)) {
    update_option('elefgs_token', GSC_Elementor_Credential_Crypto::encrypt($token));
  }

  $creds = is_multisite() ? get_site_option(self::CREDS_OPTION) : get_option(self::CREDS_OPTION);
  if (is_array($creds)) {
    $encrypted = self::encrypt_credentials($creds);
    if ($encrypted !== $creds) {
      if (is_multisite()) {
        update_site_option(self::CREDS_OPTION, $encrypted);
      } else {
        update_option(self::CREDS_OPTION, $encrypted);
      }
    }
  }

  update_option('gselef_credentials_encrypted_v1', 1);
}

/**
 * Encrypts every plaintext string value of a credential set.
 *
 * @since 1.3.9
 * @param array $creds Credential set.
 * @return array
 */
public static function encrypt_credentials($creds)
{
  foreach ($creds as $key => $value) {
    if (is_string($value) && '' !== $value && !GSC_Elementor_Credential_Crypto::is_encrypted($value)) {
      $creds[$key] = GSC_Elementor_Credential_Crypto::encrypt($value);
    }
  }
  return $creds;
}

/**
 * Decrypts every string value of a credential set.
 *
 * @since 1.3.9
 * @param array $creds Stored credential set.
 * @return array
 */
public static function decrypt_credentials($creds)
{
  foreach ($creds as $key => $value) {
    if (is_string($value) && '' !== $value) {
      $creds[$key] = GSC_Elementor_Credential_Crypto::decrypt($value);
    }
  }
  return $creds;
}

/**
 * Whether the stored token (encrypted or plaintext) holds a usable Google
 * token, i.e. an access_token or refresh_token. Rejects OAuth error payloads
 * saved by older versions.
 *
 * @since 1.3.9
 * @return bool
 */
public static function has_live_token()
{
  $value = GSC_Elementor_Credential_Crypto::decrypt(get_option('elefgs_token'));

  if (empty($value) || !is_string($value)) {
    return false;
  }

  $data = json_decode($value, true);

  return is_array($data) && (!empty($data['access_token']) || !empty($data['refresh_token']));
}

/**
 * Option remembering that the last authentication attempt failed, so the
 * failure stays visible after a reload without storing Google's error body
 * as if it were a token.
 *
 * @since 1.3.9
 */
const AUTH_FAILED_OPTION = 'elefgs_auth_failed';

/**
 * Whether authentication is in a failed state: no usable token is stored, but
 * either a failed attempt was recorded or an unusable token (e.g. one entered
 * manually) is still saved. A usable token is never reported as failed.
 *
 * @since 1.3.9
 * @return bool
 */
public static function has_auth_failure()
{
  if (self::has_live_token()) {
    return false;
  }

  return !empty(get_option(self::AUTH_FAILED_OPTION)) || !empty(get_option('elefgs_token'));
}

/**
 * One-time cleanup of a stored token that is an OAuth error payload rather
 * than a token (older versions saved Google's error body there, which made
 * the site look connected and hid the "Sign in with Google" button).
 * A working token is never touched.
 *
 * @since 1.3.9
 * @return void
 */
public static function maybe_repair_invalid_token()
{
  if (get_option('gselef_token_repaired')) {
    return;
  }

  update_option('gselef_token_repaired', 1, false);

  $token = get_option('elefgs_token');
  if (!empty($token) && is_string($token) && !self::has_live_token()) {
    delete_option('elefgs_token');
    delete_option('elefgs_verify');
    update_option(self::LIVE_EMAIL_OPTION, '');
    update_option(self::AUTH_FAILED_OPTION, 1, false);
  }
}

/**
 * Keeps the last connected email before the live email option is cleared.
 *
 * @since 1.3.9
 * @return void
 */
public static function register_last_email_hooks()
{
  add_filter('pre_update_option_' . self::LIVE_EMAIL_OPTION, array(__CLASS__, 'keep_email_on_update'), 10, 2);
  add_action('delete_option', array(__CLASS__, 'keep_email_on_delete'));
}

/**
 * @since 1.3.9
 * @param mixed $value     New value.
 * @param mixed $old_value Current value.
 * @return mixed Unchanged new value.
 */
public static function keep_email_on_update($value, $old_value)
{
  self::remember_connected_email(is_string($value) && '' !== trim($value) ? $value : $old_value);
  return $value;
}

/**
 * @since 1.3.9
 * @param string $option_name Option about to be deleted.
 * @return void
 */
public static function keep_email_on_delete($option_name)
{
  if (self::LIVE_EMAIL_OPTION === $option_name) {
    self::remember_connected_email(get_option($option_name));
  }
}

/**
 * @since 1.3.9
 * @param mixed $email Email address.
 * @return void
 */
private static function remember_connected_email($email)
{
  $email = is_string($email) ? sanitize_email($email) : '';

  if ('' !== $email && get_option(self::LAST_EMAIL_OPTION) !== $email) {
    update_option(self::LAST_EMAIL_OPTION, $email, false);
  }
}

/**
 * Email of the Google account last connected, or an empty string.
 *
 * @since 1.3.9
 * @return string
 */
public static function get_last_connected_email()
{
  $email = trim((string) get_option(self::LIVE_EMAIL_OPTION, ''));

  if ('' === $email) {
    $email = trim((string) get_option(self::LAST_EMAIL_OPTION, ''));
  }

  return $email;
}

/**
 * Prints a line naming the last connected Google account, if one is known.
 *
 * @since 1.3.9
 * @param string $message Translated message with one %s for the email.
 * @param string $class   Extra CSS classes.
 * @return void
 */
public static function render_last_connected_email($message, $class = '')
{
  $email = self::get_last_connected_email();

  if ('' === $email) {
    return;
  }

  printf(
    '<p class="gselef-last-connected-email %1$s">%2$s</p>',
    esc_attr($class),
    wp_kses(sprintf($message, '<strong>' . esc_html($email) . '</strong>'), array('strong' => array()))
  );
}

/**
* Fetch and save Auto Integration API credentials
*
* @since 2.3.10
*/
public function save_api_credentials()
{
      // Create a nonce
  $nonce = wp_create_nonce('Elegsc_api_creds');

      // Prepare parameters for the API call
  $params = array(
   'action' => 'get_data',
   'nonce' => $nonce,
   'plugin' => 'ELEMENTORGSC',
   'method' => 'get',
 );

      // Add nonce and any other security parameters to the API request
  $api_url = add_query_arg($params, GS_CONN_ELE_API_URL);

      // Make the API call using wp_remote_get
  $response = wp_remote_get($api_url);

      // Check for errors
  if (is_wp_error($response)) {
         // Handle error
    
  } else {
         // API call was successful, process the data
   $response = wp_remote_retrieve_body($response);

   $decoded_response = json_decode($response);

   if (isset($decoded_response->api_creds) && (!empty($decoded_response->api_creds))) {
    $api_creds = self::encrypt_credentials(wp_parse_args($decoded_response->api_creds));
    if (is_multisite()) {
               // If it's a multisite, update the site option (network-wide)
     update_site_option('Elegsc_api_creds', $api_creds);
   } else {
               // If it's not a multisite, update the regular option
     update_option('Elegsc_api_creds', $api_creds);
   }
 }
}
}
}
