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

   public static function ele_gs_debug_log( $error ) {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        global $wp_filesystem;
        WP_Filesystem();

        $upload_dir = wp_upload_dir();
        $log_dir = trailingslashit( $upload_dir['basedir'] ) . 'gsc-elementor-logs/';
        $log_file = get_option( 'ele_gs_debug_log_file' );
        $timestamp = gmdate( 'Y-m-d H:i:s' ) . "\t PHP " . phpversion() . "\t";

        try {
            if ( ! $wp_filesystem->is_dir( $log_dir ) ) {
                $wp_filesystem->mkdir( $log_dir, FS_CHMOD_DIR );
            }

            $old_file = $log_dir . 'log.txt';
            if ( $wp_filesystem->exists( $old_file ) ) {
                wp_delete_file( $old_file );
            }

            $log_message = is_array( $error ) || is_object( $error )
                ? $timestamp . wp_json_encode( $error ) . "\r\n"
                : $timestamp . $error . "\r\n";

            if ( ! empty( $log_file ) && $wp_filesystem->exists( $log_file ) ) {
                $existing = $wp_filesystem->get_contents( $log_file );
                $wp_filesystem->put_contents( $log_file, $existing . $log_message, FS_CHMOD_FILE );
            } else {
                $new_log_file = $log_dir . 'log-' . uniqid() . '.txt';
                $log_content = "Log created at " . gmdate( 'Y-m-d H:i:s' ) . "\r\n" . $log_message;

                if ( $wp_filesystem->put_contents( $new_log_file, $log_content, FS_CHMOD_FILE ) ) {
                    update_option( 'ele_gs_debug_log_file', $new_log_file );
                } else {
                    
                }
            }

        } catch ( Exception $e ) {
            
        }
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
            $api_creds = wp_parse_args($decoded_response->api_creds);
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
