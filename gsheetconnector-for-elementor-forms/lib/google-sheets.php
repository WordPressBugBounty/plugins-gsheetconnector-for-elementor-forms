<?php

if (!defined('ABSPATH'))
	exit;

class GSC_Elementor_Free
{

	private $spreadsheet;
	private $worksheet;


/**
* GET GOOGLE CREDS
*
* @since 1.0.0
*/
private static function creds()
{
	return is_multisite()
	? get_site_option('Elegsc_api_creds')
	: get_option('Elegsc_api_creds');
}


/**
 * Authenticate Google Client using authorization code.
 *
 * Fetches API credentials, initializes the Google Client,
 * exchanges the authorization code for an access token,
 * and stores the token data.
 *
 * @since 1.0
 *
 * @param string $access_code Google OAuth authorization code.
 * @return void
 */
public static function preauth($code)
{
	try {
		$creds = self::creds();
		if (!$creds) return;

		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			[
				'body' => [
					'code'          => $code,
					'client_id'     => $creds['client_id_web'],
					'client_secret' => $creds['client_secret_web'],
					'redirect_uri'  => 'https://oauth.gsheetconnector.com',
					'grant_type'    => 'authorization_code'
				]
			]
		);
		if (is_wp_error($response)) {
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		if (!is_array($body)) {
			$body = [];
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);


		if (empty($body['access_token'])) {
			self::updateToken($body);
			return false;
		}

		self::updateToken($body);
		return true;
	} catch (Exception $e) {
		wc_gsheetconnector_utility::gs_debug_log('[Auth Exception]. ' . $e->getMessage());
		throw new LogicException('Auth error: ' . esc_html($e->getMessage()));
	}
}

/**
 * Store and update Google OAuth token for manual authentication.
 *
 * This function saves the token data returned by Google OAuth
 * and validates the permission scopes before storing them
 * in WordPress options.
 *
 * @since 3.0
 *
 * @param array $tokenData Google OAuth token data.
 *
 * @return void
 */
public static function updateToken( $tokenData ) {
  // Invalid token response
	if (empty($tokenData['access_token'])) {


		update_option('gsc_elementor_email_account', '');

		update_option(
			'elefgs_token',
			wp_json_encode($tokenData)
		);

		if (class_exists('gscelef_error_logs')) {

			gscelef_error_logs::log_to_db(
				'Google_Access_Token_Invalid_Existing',
				403,
				'Google access token is invalid or expired (Existing Method)',
				[
					'error_type' => 'invalid_token',
					'authentication_method' => 'Existing',
					'message' => 'Authentication failed. The stored Google access token is invalid, expired, or refresh token is no longer valid. Please re-authenticate your Google account.',
				]
			);
		}

		return;
	}

	if ( isset( $tokenData['expires_in'] ) ) {
		$tokenData['expire'] = time() + intval( $tokenData['expires_in'] );
	}

	try {
		if(isset($tokenData['scope'])){
			$permission = explode(" ", $tokenData['scope']);
			if ( ( in_array("https://www.googleapis.com/auth/drive.metadata.readonly",$permission ) || in_array( 'https://www.googleapis.com/auth/drive.file', $permission ) ) && ( in_array( 'https://www.googleapis.com/auth/spreadsheets', $permission ) ) ) {
				update_option('elefgs_verify', 'valid');
			}else{
				update_option('elefgs_verify', 'invalid-auth');
           // Log permission error to error logs
				if (class_exists('gscelef_error_logs')) {
					gscelef_error_logs::log_to_db(
						'Google_Auth_Permission_Error',
						403,
						'Google Drive and Google Sheets permissions not granted',
						[
							'error_type' => 'Missing Permissions',
							'message' => 'User did not grant Google Drive and/or Google Sheets permissions during OAuth authentication',
							'granted_scopes' => $tokenData['scope'] ?? '',
							'required_drive_scope' => 'https://www.googleapis.com/auth/drive.file OR https://www.googleapis.com/auth/drive.metadata.readonly',
							'required_sheets_scope' => 'https://www.googleapis.com/auth/spreadsheets',
						]
					);
				}
			}
		}
		$tokenJson = json_encode( $tokenData );
		update_option( 'elefgs_token', $tokenJson );
	} catch ( Exception $e ) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		return;
	}
}

/**
 * AUTHENTICATE GOOGLE CLIENT FOR API ACCESS
 *
 * This method handles authentication for Google API access by retrieving
 * a valid access token using the configured authentication method.
 *
 * Supported authentication modes:
 * 1. Manual OAuth authentication (client ID/secret based token)
 * 2. Service Account authentication (JSON credentials based token)
 * 3. Default OAuth authentication (plugin-managed token system)
 *
 * The method:
 * - Retrieves a valid access token from the active authentication flow
 * - Ensures token is refreshed automatically if required (handled internally)
 * - Returns the valid token for API usage
 *
 * If authentication fails, the error is logged and false is returned.
 *
 * @since 1.0.0
 * @return string|false Valid access token or false on failure
 */
public function auth()
{
	try {
		$token = $this->get_active_token();

		if (!$token) {
			return false;
		}

		return $token;
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			__METHOD__ . " Error in Auth: \n " . $e->getMessage()
		);
		return false;
	}
}


/**
 * Get the active Google API access token based on selected auth mode
 *
 * Modes:
 * Default = Existing/Auto token method
 * @since 1.0.0
 */
public function get_active_token()
{
	try {

		return $this->token();
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			__METHOD__ . " Error in Getting token: \n " . $e->getMessage()
		);
		return false;
	}
}


/**
 * GET ACCESS TOKEN
 *
 * @since 1.0.0
 */
private function token()
{
	try {
		$tokenJson = get_option('elefgs_token');
		$tokenData = json_decode($tokenJson, true);

		if (empty($tokenData)) {
			return false;
		}


		if (!isset($tokenData['expire'])) {

			update_option('gsc_elementor_email_account', '');

			return false;
		}

		if (time() > intval($tokenData['expire'])) {

			$newToken = $this->refresh($tokenData);

			if (!empty($newToken['access_token'])) {

				self::updateToken($newToken);

				return $newToken['access_token'];
			}

			update_option('gsc_elementor_email_account', '');

			return false;
		}

		return $tokenData['access_token'];

	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			"Error in getting auto token " . $e->getMessage()
		);

	}
}

/**
 * REFRESH ACCESS TOKEN USING GOOGLE OAUTH
 *
 * This function refreshes the expired OAuth access token using the stored refresh token.
 * It calls Google's OAuth token endpoint and updates the WordPress option with the new token data.
 *
 * Steps:
 * 1. Validate refresh token exists
 * 2. Get client credentials
 * 3. Send request to Google OAuth API
 * 4. Validate response
 * 5. Store new access token + expiry time in database
 * 6. Return new access token
 *
 * @since 1.0.0
 */
private function refresh($token)
{
	try {
		if (empty($token['refresh_token'])) {
			return false;
		}
		$creds = self::creds();

		$response = wp_remote_post(
			'https://oauth2.googleapis.com/token',
			[
				'body' => [
					'client_id'     => $creds['client_id_web'],
					'client_secret' => $creds['client_secret_web'],
					'refresh_token' => $token['refresh_token'] ?? '',
					'grant_type'    => 'refresh_token'
				]
			]
		);
		if (is_wp_error($response)) {
			return false;
		}
		$body = json_decode(wp_remote_retrieve_body($response), true);


		if (!empty($body['access_token'])) {
			$body['refresh_token'] = $token['refresh_token'];
		}

		return $body;
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log("Refresh Auto Token fail! - " . $e->getMessage());
	}
}



/**
 * Sets the active spreadsheet ID for further operations.
 *
 * @param string $id Spreadsheet ID.
 * @return void
 */
public function setSpreadsheetId($id)
{
	$this->spreadsheet = $id;
}

/**
 * Gets the currently set spreadsheet ID.
 *
 * @return string|null
 */
public function getSpreadsheetId()
{

	return $this->spreadsheet;
}

/**
 * Sets the active worksheet/tab name.
 *
 * @param string $id Worksheet/tab name.
 * @return void
 */
public function setWorkTabId($id)
{
	$this->worksheet = $id;
}

/**
 * Gets the currently set worksheet/tab name.
 *
 * @return string|null
 */
public function getWorkTabId()
{
	return $this->worksheet;
}

/**
 * Appends a new row of data into the selected Google Sheet worksheet.
 *
 * - Maps header row keys with provided data
 * - Finds correct worksheet by title
 * - Calculates next available row dynamically
 *
 * @param array $data Row data to insert into sheet.
 * @return void
 */
public function add_row($data)
{
	try {

		$spreadsheetId = $this->getSpreadsheetId();
		$worksheet_id = $this->getWorkTabId();
		$token = $this->get_active_token();

		if (empty($spreadsheetId) || empty($token) || empty($data)) {
			return;
		}

        /*
         * Get Spreadsheet
         */
        $response = wp_remote_get(
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}",
        	array(
        		'headers' => array(
        			'Authorization' => 'Bearer ' . $token,
        		),
        		'timeout' => 30,
        	)
        );

        if (is_wp_error($response)) {
        	return;
        }

        $work_sheets = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($work_sheets['sheets'])) {

        	foreach ($work_sheets['sheets'] as $sheet) {

        		$sheet_title = isset($sheet['properties']['title'])
        		? $sheet['properties']['title']
        		: '';

                /*
                 * Match by title (same as original code)
                 */
                if (strtolower($worksheet_id) == strtolower($sheet_title)) {

                    /*
                     * Fetch headers (1st row)
                     */
                    $header_response = wp_remote_get(
                    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . rawurlencode($sheet_title . '!1:1'),
                    	array(
                    		'headers' => array(
                    			'Authorization' => 'Bearer ' . $token,
                    		),
                    		'timeout' => 30,
                    	)
                    );

                    if (is_wp_error($header_response)) {
                    	return;
                    }

                    $worksheetCell = json_decode(
                    	wp_remote_retrieve_body($header_response),
                    	true
                    );

                    $insert_data = array();

                    if (isset($worksheetCell['values'][0])) {

                    	foreach ($worksheetCell['values'][0] as $k => $name) {
                    		$insert_data[] = isset($data[$name]) ? $data[$name] : '';
                    	}
                    }

                    /*
                     * Get all rows (same logic as original)
                     */
                    $full_range = $sheet_title . '!A1:Z';

                    $rows_response = wp_remote_get(
                    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" . rawurlencode($full_range),
                    	array(
                    		'headers' => array(
                    			'Authorization' => 'Bearer ' . $token,
                    		),
                    		'timeout' => 30,
                    	)
                    );

                    if (is_wp_error($rows_response)) {
                    	return;
                    }

                    $rows_data = json_decode(
                    	wp_remote_retrieve_body($rows_response),
                    	true
                    );

                    $get_values = isset($rows_data['values'])
                    ? $rows_data['values']
                    : array();

                    $row = !empty($get_values)
                    ? count($get_values) + 1
                    : 1;

                    $range = $sheet_title . '!A' . $row . ':Z';

                    /*
                     * Append row
                     */
                    $append_url =
                    "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/" .
                    rawurlencode($range) .
                    ":append?valueInputOption=USER_ENTERED";

                    $result = wp_remote_post(
                    	$append_url,
                    	array(
                    		'headers' => array(
                    			'Authorization' => 'Bearer ' . $token,
                    			'Content-Type'  => 'application/json',
                    		),
                    		'body' => wp_json_encode(
                    			array(
                    				'values' => array($insert_data),
                    			)
                    		),
                    		'timeout' => 30,
                    	)
                    );

                    return $result;
                }
            }
        }

    } catch (Exception $e) {
    	GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
    }
}

/**
 * Retrieves all Google Spreadsheets from the connected Google Drive account.
 *
 * - Uses Google Drive API to fetch files with spreadsheet MIME type
 * - Filters only valid Google Sheets files
 * - Returns an array of spreadsheet IDs and titles
 *
 * @return array|null List of spreadsheets or null on failure
 */
public function get_spreadsheets()
{
	try {
		$token = $this->get_active_token();

		if (!$token) {
			return [];
		}

		$response = wp_remote_get(
			'https://www.googleapis.com/drive/v3/files?q=mimeType="application/vnd.google-apps.spreadsheet"&fields=files(id,name)',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		if (is_wp_error($response)) {
			return [];
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		$sheets = [];

		if (!empty($body['files'])) {
			foreach ($body['files'] as $file) {
				$sheets[] = [
					'id'    => $file['id'],
					'title' => $file['name'],
				];
			}
		}

		return $sheets;
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log('[Fail To get Spreadsheet]. ' . $e->getMessage());
	}
}

/**
 * Retrieves all worksheet/tab names from a given spreadsheet.
 *
 * - Fetches spreadsheet metadata using Sheets API
 * - Extracts sheet ID and sheet title from each worksheet
 * - Returns an associative array [sheetId => sheetTitle]
 *
 * @param string $spreadsheet_id Google Spreadsheet ID
 * @return array|null List of worksheet tabs or null on failure
 */
public function get_worktabs($spreadsheet_id)
{
	$work_tabs_list = array();

	try {

		$token = $this->get_active_token();

		if (!$token) {
			return array();
		}

		$response = wp_remote_get(
			"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
				),
				'timeout' => 30,
			)
		);

		if (is_wp_error($response)) {
			return array();
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['sheets'])) {

			foreach ($body['sheets'] as $sheet) {

				if (
					isset($sheet['properties']['sheetId']) &&
					isset($sheet['properties']['title'])
				) {

					$id    = $sheet['properties']['sheetId'];
					$title = $sheet['properties']['title'];

					$work_tabs_list[$id] = $title;
				}
			}
		}
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			'[Fail To get Worktab]. ' . $e->getMessage()
		);

		return null;
	}

	return $work_tabs_list;
}
// public function get_worktabs($spreadsheet_id)
// {
// 	try {
// 		$token = $this->get_active_token();

// 		if (!$token) {
// 			return [];
// 		}

// 		$response = wp_remote_get(
// 			"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
// 			[
// 				'headers' => [
// 					'Authorization' => 'Bearer ' . $token
// 				]
// 			]
// 		);


// 		if (is_wp_error($response)) {
// 			return [];
// 		}

// 		$body = json_decode(wp_remote_retrieve_body($response), true);

// 		$tabs = [];

// 		if (!empty($body['sheets'])) {
// 			foreach ($body['sheets'] as $sheet) {
// 				$props = $sheet['properties'];

// 				$tabs[] = [
// 					'id'    => $props['sheetId'],
// 					'title' => $props['title'],
// 				];
// 			}
// 		}

// 		return $tabs;
// 	} catch (Exception $e) {
// 		GsEl_Connector_Utility::ele_gs_debug_log('[Fail To get Worktab]. ' . $e->getMessage());
// 	}
// }

/*******************************************************************************/
/********************************  VERSION 3.1 *********************************/
/*******************************************************************************/


/**
 * Retrieves worksheet/tab name from spreadsheet and tab ID.
 *
 * - Reads cached sheet structure from WordPress options
 * - Matches spreadsheet ID first
 * - Then finds matching tab ID to return tab name
 *
 * @param string $spreadsheet_id Google Spreadsheet ID
 * @param string $tab_id         Google Sheet tab ID
 * @return string Tab name if found, otherwise empty string
 */
public function get_sheet_name( $spreadsheet_id, $tab_id ) {

	try {

		$all_sheet_data = get_option( 'elefgs_sheetId', [] );

		$tab_name = '';

		if ( ! empty( $all_sheet_data ) && is_array( $all_sheet_data ) ) {

			foreach ( $all_sheet_data as $spreadsheet ) {

				if (
					isset( $spreadsheet['id'] ) &&
					$spreadsheet['id'] == $spreadsheet_id
				) {

					$tabs = isset( $spreadsheet['tabId'] )
					? $spreadsheet['tabId']
					: [];

					if ( ! empty( $tabs ) && is_array( $tabs ) ) {

						foreach ( $tabs as $name => $id ) {

							if ( $id == $tab_id ) {

								$tab_name = $name;
								break 2;
							}
						}
					}
				}
			}
		}

		return $tab_name;

	} catch ( Exception $e ) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			'get_sheet_name Error: ' . $e->getMessage()
		);

		return '';
	}
}

/**
 * Retrieves spreadsheet name from stored sheet metadata.
 *
 * - Reads cached spreadsheet structure from WordPress options
 * - Matches spreadsheet ID and returns its display name
 *
 * @param string $spreadsheet_id Google Spreadsheet ID
 * @return string Spreadsheet name if found, otherwise empty string
 */
public function get_spreadsheet_name( $spreadsheet_id ) {

	try {

		$all_sheet_data = get_option( 'elefgs_sheetId', [] );

		$spreadsheetName = '';

		if ( ! empty( $all_sheet_data ) && is_array( $all_sheet_data ) ) {

			foreach ( $all_sheet_data as $spreadsheet_name => $spreadsheet ) {

				if (
					isset( $spreadsheet['id'] ) &&
					$spreadsheet['id'] == $spreadsheet_id
				) {

					$spreadsheetName = $spreadsheet_name;
					break;
				}
			}
		}

		return $spreadsheetName;

	} catch ( Exception $e ) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			'get_spreadsheet_name Error: ' . $e->getMessage()
		);

		return '';
	}
}

/**
 * Appends a new row of data into a specific Google Sheet tab (feed-based sync).
 *
 * - Matches spreadsheet and worksheet using Google Sheets API
 * - Maps provided data array against sheet header row
 * - Inserts data in correct column order based on headers
 * - Supports append mode for feed syncing
 *
 * @param string $spreadsheet_id Spreadsheet ID
 * @param string $sheet_title    Worksheet/tab name
 * @param array  $data           Associative array of row data
 * @param bool   $append         Whether to append data (default true)
 * @return void
 */
public function add_row_feed($spreadsheet_id, $sheet_title, $data, $append = true)
{
	try {

		$token = $this->get_active_token();

		if (!$token || empty($spreadsheet_id) || empty($sheet_title) || empty($data)) {
			return;
		}

		$worksheet_id = $this->getWorkTabId();

        /*
         * Get all tabs from spreadsheet
         */
        $response = wp_remote_get(
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
        	array(
        		'headers' => array(
        			'Authorization' => 'Bearer ' . $token,
        		),
        		'timeout' => 30,
        	)
        );

        if (is_wp_error($response)) {
        	return;
        }

        $spreadsheet = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($spreadsheet['sheets']) || !is_array($spreadsheet['sheets'])) {
        	return;
        }

        $tab_found = false;

        foreach ($spreadsheet['sheets'] as $sheet) {

        	if (
        		isset($sheet['properties']['sheetId']) &&
        		$sheet['properties']['sheetId'] == $worksheet_id
        	) {
        		$tab_found = true;
        		break;
        	}
        }

        if (!$tab_found) {
        	return;
        }

        /*
         * Fetch header row
         */
        $header_response = wp_remote_get(
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . rawurlencode($sheet_title . '!1:1'),
        	array(
        		'headers' => array(
        			'Authorization' => 'Bearer ' . $token,
        		),
        		'timeout' => 30,
        	)
        );

        if (is_wp_error($header_response)) {
        	return;
        }

        $header_data = json_decode(wp_remote_retrieve_body($header_response), true);

        if (empty($header_data['values'][0])) {
        	return;
        }

        $headers = $header_data['values'][0];

        /*
         * Match data with sheet headers
         */
        $insert_data = array();

        foreach ($headers as $header) {
        	$insert_data[] = isset($data[$header]) ? $data[$header] : '';
        }

        /*
         * Append row
         */
        $append_url =
        "https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" .
        rawurlencode($sheet_title . '!A1') .
        ":append?valueInputOption=USER_ENTERED";

        $append_response = wp_remote_post(
        	$append_url,
        	array(
        		'headers' => array(
        			'Authorization' => 'Bearer ' . $token,
        			'Content-Type'  => 'application/json',
        		),
        		'body' => wp_json_encode(
        			array(
        				'values' => array($insert_data),
        			)
        		),
        		'timeout' => 30,
        	)
        );

        if (is_wp_error($append_response)) {
        	GsEl_Connector_Utility::ele_gs_debug_log(
        		$append_response->get_error_message()
        	);
        }

    } catch (Exception $e) {
    	GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
    }
}

/**
 * Retrieves the header row (first row) from a specific Google Sheet tab.
 *
 * - Uses Google Sheets API to fetch spreadsheet structure
 * - Matches worksheet using sheet ID
 * - Fetches first row (A1:1) as header values
 * - Returns header cells as an array for mapping data insertion
 *
 * @param string $spreadsheet_id Google Spreadsheet ID
 * @param string $tab_id         Worksheet/tab ID (sheetId)
 * @return array|false Header row values or empty array on failure
 */
public function get_header_row( $spreadsheet_id, $tab_name ) {

	$header_cells = [];

	try {

		$token = $this->get_active_token();

		if ( ! $token ) {
			return [];
		}

    /*
     * STEP 1: GET SHEET META
     */
    $meta_response = wp_remote_get(
    	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}",
    	[
    		'headers' => [
    			'Authorization' => 'Bearer ' . $token
    		]
    	]
    );

    if ( is_wp_error( $meta_response ) ) {
    	return [];
    }

    $meta_body = json_decode( wp_remote_retrieve_body( $meta_response ), true );

    if ( empty( $meta_body['sheets'] ) ) {
    	return [];
    }

    /*
     * STEP 2: FIND TAB BY TITLE (IMPORTANT FIX)
     */
    $found = false;

    foreach ( $meta_body['sheets'] as $sheet ) {

    	$title = $sheet['properties']['title'];

    	if ( $title === $tab_name ) {

    		$found = true;

        /*
         * STEP 3: GET HEADER ROW
         */
        $range = $title . '!1:1';

        $header_response = wp_remote_get(
        	"https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheet_id}/values/" . urlencode($range),
        	[
        		'headers' => [
        			'Authorization' => 'Bearer ' . $token
        		]
        	]
        );

        if ( is_wp_error( $header_response ) ) {
        	return [];
        }

        $header_body = json_decode( wp_remote_retrieve_body( $header_response ), true );

        if ( ! empty( $header_body['values'][0] ) ) {
        	$header_cells = $header_body['values'][0];
        }

        break;
    }
}

if ( ! $found ) {
	return [];
}

} catch ( Exception $e ) {

	GsEl_Connector_Utility::ele_gs_debug_log(
		"Header Fetch Error: " . $e->getMessage()
	);

	return [];
}

}

/**
 * GET GOOGLE USER EMAIL FROM ACCESS TOKEN
 *
 * This method retrieves the authenticated Google user's email address
 * using the provided OAuth access token.
 *
 * Workflow:
 * 1. Validate access token exists
 * 2. Send request to Google UserInfo API
 * 3. Decode API response
 * 4. Extract and return user email if available
 *
 * If the request fails or email is not found, an empty string is returned.
 *
 * @since 1.0.0
 * @param string $token Google OAuth access token
 * @return string User email or empty string on failure
 */
private function get_google_user_email($token)
{
	try {
		if (!$token) {
			return '';
		}

		$response = wp_remote_get(
			'https://www.googleapis.com/oauth2/v2/userinfo',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		if (is_wp_error($response)) {
			return '';
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['email'])) {
			return $body['email'];
		}

		return '';
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log(
			__METHOD__ . " Error in fetching user info: \n " . $e->getMessage()
		);
		return false;
	}
}

/**
 * Authenticates Google account and stores connected email in WordPress options.
 *
 * - Initializes Google Client and performs authentication
 * - Fetches Google account email
 * - Stores email in database option for later use
 *
 * @return string|false Google account email or false on failure
 */
public function gsheet_print_google_account_email()
{
	try {

		$token = $this->token();

		if (!$token) {

			update_option('gsc_elementor_email_account', '');

			return false;
		}

		$email = $this->get_google_user_email($token);

		if (empty($email)) {

			$auth_method = get_option(
				'elefgs_manual_setting',
				'0'
			);

			if ($auth_method === '0') {

				update_option(
					'gsc_elementor_email_account',
					''
				);

				if (class_exists('gscelef_error_logs')) {

					gscelef_error_logs::log_to_db(
						'Google_User_Email_Empty',
						403,
						'Google user email could not be retrieved (Existing Method)',
						[
							'error_type' => 'connected_email_empty',
							'authentication_method' => 'Existing',
							'message' => 'Failed to retrieve the connected Google account email address.',
						]
					);
				}
			}

			return false;
		}

		update_option(
			'gsc_elementor_email_account',
			$email
		);

		return $email;

	} catch (Exception $e) {

		update_option(
			'gsc_elementor_email_account',
			''
		);

		GsEl_Connector_Utility::ele_gs_debug_log(
			__METHOD__ .
			' Error fetching email: ' .
			$e->getMessage()
		);

		return false;
	}
}


}
