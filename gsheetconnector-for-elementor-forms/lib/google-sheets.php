<?php

if (!defined('ABSPATH'))
	exit;

include_once(plugin_dir_path(__FILE__) . 'vendor/autoload.php');

class GSC_Elementor_Free
{

	private $token;
	private $spreadsheet;
	private $worksheet;


	private static $instance;

	public function __construct() {}

/**
 * Sets the singleton Google_Client instance.
 *
 * @param Google_Client|null $instance Google Client instance.
 * @return void
 */
public static function setInstance(Google_Client $instance = null)
{
	self::$instance = $instance;
}

/**
 * Returns the singleton Google_Client instance.
 *
 * @throws LogicException If the client instance is not initialized.
 * @return Google_Client
 */
public static function getInstance()
{
	if (is_null(self::$instance)) {

		$obj = new self();
		$obj->auth(); // try to init

		if (is_null(self::$instance)) {
			return false;
		}
	}

	return self::$instance;
}

/**
 * Performs OAuth pre-authentication using authorization code.
 *
 * Builds Google Client, exchanges auth code for access token,
 * and stores token data in plugin options.
 *
 * @param string $access_code OAuth authorization code.
 * @return void
 */
public static function preauth($access_code)
{
		// Fetch API creds
	if (is_multisite()) {
			// Fetch API creds
		$api_creds = get_site_option('Elegsc_api_creds');
	} else {
			// Fetch API creds
		$api_creds = get_option('Elegsc_api_creds');
	}
	$client = new Google_Client();
	$newClientSecret = get_option('is_new_client_secret_elefgscfree');
	$clientId = ($newClientSecret == 1) ? $api_creds['client_id_web'] : $api_creds['client_id_desk'];
	$clientSecret = ($newClientSecret == 1) ? $api_creds['client_secret_web'] : $api_creds['client_secret_desk'];
	$client->setClientId($clientId);
	$client->setClientSecret($clientSecret);
		//   $client->setClientId( GSC_Elementor_Free::clientId );
		//   $client->setClientSecret( GSC_Elementor_Free::clientSecret );
	$client->setRedirectUri('https://oauth.gsheetconnector.com');
	$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
	$client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
	$client->setAccessType('offline');
	$client->fetchAccessTokenWithAuthCode($access_code);
	$tokenData = $client->getAccessToken();

	GSC_Elementor_Free::updateToken($tokenData);
}

/**
 * Updates and stores OAuth token data in WordPress options.
 *
 * Adds expiry timestamp, validates scopes, and logs errors if any occur.
 *
 * @param array $tokenData OAuth token response data.
 * @return void
 */
public static function updateToken($tokenData)
{
	$expires_in = isset($tokenData['expires_in']) ? intval($tokenData['expires_in']) : 0;
	$tokenData['expire'] = time() + $expires_in;
	try {
		if (isset($tokenData['scope'])) {
			$permission = explode(" ", $tokenData['scope']);
			if ((in_array("https://www.googleapis.com/auth/drive.metadata.readonly", $permission)) && (in_array("https://www.googleapis.com/auth/spreadsheets", $permission))) {
				update_option('elefgs_verify', 'valid');
			} else {
				update_option('elefgs_verify', 'invalid-auth');
			}
			$tokenJson = json_encode($tokenData);
			update_option('elefgs_token', $tokenJson);
		}
		
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
	}
}

/**
 * Authenticates Google Client using stored OAuth token (manual or automatic mode).
 *
 * - Supports manual credentials mode and API-based credentials mode.
 * - Refreshes access token using stored refresh token.
 * - Stores updated token and sets Google Client instance.
 *
 * @throws LogicException If refresh token is missing or invalid authentication state.
 * @return void
 */
public function auth()
{

	$maunal_setting = get_option('elefgs_manual_setting') ? get_option('elefgs_manual_setting') : '0';

	if ((isset($maunal_setting)) && ($maunal_setting == '1'))
		$tokenData = json_decode(get_option('elefgs_token_manual'), true);
	else
		$tokenData = json_decode(get_option('elefgs_token'), true);


	if (empty($tokenData) || !isset($tokenData['refresh_token']) || empty($tokenData['refresh_token'])) {

	// stop silently (NO exception)
		return false;
	}

	try {
		$client = new Google_Client();

		if ($maunal_setting == '1') {
			$elefgs_client_id = get_option('elefgs_client_id');
			$elefgs_secret_id = get_option('elefgs_secret_id');
			$client->setClientId($elefgs_client_id);
			$client->setClientSecret($elefgs_secret_id);
		} else {
				// Fetch API creds
			if (is_multisite()) {
					// Fetch API creds
				$api_creds = get_site_option('Elegsc_api_creds');
			} else {
					// Fetch API creds
				$api_creds = get_option('Elegsc_api_creds');
			}
			$newClientSecret = get_option('is_new_client_secret_elefgscfree');
			$clientId = ($newClientSecret == 1) ? $api_creds['client_id_web'] : $api_creds['client_id_desk'];
			$clientSecret = ($newClientSecret == 1) ? $api_creds['client_secret_web'] : $api_creds['client_secret_desk'];
			$client->setClientId($clientId);
			$client->setClientSecret($clientSecret);

			
			$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
			$client->setScopes(Google_Service_Drive::DRIVE_METADATA_READONLY);
			$client->refreshToken($tokenData['refresh_token']);
			$client->setAccessType('offline');
			//GSC_Elementor_Free::updateToken( $tokenData );

			GSC_Elementor_Free::updateToken($tokenData);
		}



		self::setInstance($client);
	} catch (Exception $e) {
			// throw new LogicException("Auth, Error fetching OAuth2 access token, message: " . $e->getMessage());
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		exit();
	}
}
/**
 * Test/debug function to fetch and process Google Sheets data.
 *
 * NOTE: This appears to be a development/testing method.
 *
 * @return void
 */
public function get_user_data()
{
	$client = self::getInstance();

	$results = $this->get_spreadsheets();


	$spreadsheets = $this->get_worktabs('1mRuDMnZveDFQrmzHM9s5YkPA4F_dZkHJ1Gh81BvYB2k');

	$this->setSpreadsheetId('1mRuDMnZveDFQrmzHM9s5YkPA4F_dZkHJ1Gh81BvYB2k');
	$this->setWorkTabId('Foglio1');
	$worksheetTab = $this->list_rows();
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
		$client = self::getInstance();
		$service = new Google_Service_Sheets($client);
		$spreadsheetId = $this->getSpreadsheetId();
		$work_sheets = $service->spreadsheets->get($spreadsheetId);

		if (!empty($work_sheets) && !empty($data)) {
			foreach ($work_sheets->getSheets() as $sheet) {
				$properties = $sheet->getProperties();
				$sheet_title = $properties->getTitle();
					$worksheet_id = $this->getWorkTabId(); // expected to be sheet name

					// ✅ Match by title, not ID
					if (strtolower($worksheet_id) == strtolower($sheet_title)) {
						$worksheetCell = $service->spreadsheets_values->get($spreadsheetId, $sheet_title . "!1:1");
						$insert_data = [];

						if (isset($worksheetCell->values[0])) {
							foreach ($worksheetCell->values[0] as $k => $name) {
								$insert_data[] = $data[$name] ?? '';
							}
						}

						$full_range = $sheet_title . "!A1:Z";
						$response = $service->spreadsheets_values->get($spreadsheetId, $full_range);
						$get_values = $response->getValues();
						$row = $get_values ? count($get_values) + 1 : 1;
						$range = $sheet_title . "!A" . $row . ":Z";

						$valueRange = new Google_Service_Sheets_ValueRange();
						$valueRange->setValues([$insert_data]); // ✅ correct format

						$conf = ["valueInputOption" => "USER_ENTERED"];

						$result = $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $conf);
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
	$all_sheets = array();
	try {
		$client = self::getInstance();

		$service = new Google_Service_Drive($client);

		$optParams = array(
			'q' => "mimeType='application/vnd.google-apps.spreadsheet'"
		);
		$results = $service->files->listFiles($optParams);
		foreach ($results->files as $spreadsheet) {
			if (isset($spreadsheet['kind']) && $spreadsheet['kind'] == 'drive#file') {
				$all_sheets[] = array(
					'id' => $spreadsheet['id'],
					'title' => $spreadsheet['name'],
				);
			}
		}
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		return null;
		exit();
	}
	return $all_sheets;
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
		$client = self::getInstance();
		$service = new Google_Service_Sheets($client);
		$work_sheets = $service->spreadsheets->get($spreadsheet_id);

		foreach ($work_sheets as $sheet) {
			$properties = $sheet->getProperties();
			$id = $properties->getSheetId();
			$title = $properties->getTitle();
			$work_tabs_list[$id] = $title;
		}
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		return null;
		exit();
	}

	return $work_tabs_list;
}

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
public function get_sheet_name($spreadsheet_id, $tab_id)
{

	$all_sheet_data = get_option('elefgs_sheetId');

	$tab_name = "";
	foreach ($all_sheet_data as $spreadsheet) {

		if ($spreadsheet['id'] == $spreadsheet_id) {
			$tabs = $spreadsheet['tabId'];

			foreach ($tabs as $name => $id) {
				if ($id == $tab_id) {
					$tab_name = $name;
				}
			}
		}
	}

	return $tab_name;
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
public function get_spreadsheet_name($spreadsheet_id)
{

	$all_sheet_data = get_option('elefgs_sheetId');

	$spreadsheetName = "";
	foreach ($all_sheet_data as $spreadsheet_name => $spreadsheet) {

		if ($spreadsheet['id'] == $spreadsheet_id) {
			$spreadsheetName = $spreadsheet_name;
		}
	}

	return $spreadsheetName;
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
		$client = self::getInstance();
		$service = new Google_Service_Sheets($client);
		$work_sheets = $service->spreadsheets->get($spreadsheet_id)->getSheets();

		if (!empty($work_sheets) && !empty($data)) {
			foreach ($work_sheets as $sheet) {
				$properties = $sheet->getProperties();
				$sheet_id = $properties->getSheetId();
				$worksheet_id = $this->getWorkTabId();

				if ($sheet_id == $worksheet_id) {
						// Fetch headers
					$worksheetCell = $service->spreadsheets_values->get($spreadsheet_id, $sheet_title . '!1:1');
					$insert_data = [];

					if (isset($worksheetCell->values[0])) {
						$headers = $worksheetCell->values[0];

							// Map the $data to the headers
						foreach ($headers as $header) {
								// Use the header as the key to find the corresponding value
							$insert_data[] = isset($data[$header]) ? $data[$header] : '';
						}
					} else {

						return;
					}

						// Append data to the sheet
					$valueRange = new Google_Service_Sheets_ValueRange();
					$valueRange->setValues([$insert_data]);

					$conf = ['valueInputOption' => 'USER_ENTERED'];
					$response = $service->spreadsheets_values->append($spreadsheet_id, $sheet_title . '!A1', $valueRange, $conf);
				}
			}
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
public function get_header_row($spreadsheet_id, $tab_id)
{

	$header_cells = array();
	try {

		$client = $this->getInstance();

		if (!$client) {
			return false;
		}

		$service = new Google_Service_Sheets($client);

		$work_sheets = $service->spreadsheets->get($spreadsheet_id);

		if ($work_sheets) {

			foreach ($work_sheets as $sheet) {

				$properties = $sheet->getProperties();
				$work_sheet_id = $properties->getSheetId();

				if ($work_sheet_id == $tab_id) {

					$tab_title = $properties->getTitle();
					$header_row = $service->spreadsheets_values->get($spreadsheet_id, $tab_title . "!1:1");

					$header_row_values = $header_row->getValues();

					if (isset($header_row_values[0]) && $header_row_values[0]) {
						$header_cells = $header_row_values[0];
					}
				}
			}
		}
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		$header_cells = array();
		return $header_cells;
	}

	return $header_cells;
}

/**
 * Syncs Google account data and stores spreadsheets + worksheets into WordPress options.
 *
 * - Authenticates Google Client
 * - Fetches all Google Spreadsheets from Drive
 * - Fetches all worksheets (tabs) for each spreadsheet
 * - Stores structured mapping of spreadsheet IDs, names, and tabs
 * - Supports optional AJAX response for frontend initialization
 *
 * Stored Options:
 * - elefgs_sheetId → spreadsheet + tab structure mapping
 * - elefgs_feeds   → simplified sheet-feed mapping
 *
 * @return void|false Returns false if no spreadsheets found
 */
public function sync_with_google_account()
{
	return;

	$return_ajax = false;

	if (isset($_POST['isajax']) && $_POST['isajax'] == 'yes') {
		check_ajax_referer('gf-ajax-nonce', 'security');
		$init = sanitize_text_field($_POST['isinit']);
		$return_ajax = true;
	}

	$worksheet_array = array();
	$sheetdata = array();
	$doc = new GSC_Elementor_Free();
	$doc->auth();
	$spreadsheetFeed = $doc->get_spreadsheets();

	if (!$spreadsheetFeed) {
		return false;
	}

	foreach ($spreadsheetFeed as $sheetfeeds) {
		$sheetId = $sheetfeeds['id'];
		$sheetname = $sheetfeeds['title'];

		$worksheetFeed = $doc->get_worktabs($sheetId);

		foreach ($worksheetFeed as $worksheet) {
			$tab_id = $worksheet['id'];
			$tab_name = $worksheet['title'];
			$worksheet_array[] = $tab_name;
			$worksheet_ids[$tab_name] = $tab_id;
		}

		$sheetId_array[$sheetname] = array(
			"id" => $sheetId,
			"tabId" => $worksheet_ids
		);

		unset($worksheet_ids);
		$sheetdata[$sheetname] = $worksheet_array;
		unset($worksheet_array);
	}

	update_option('elefgs_sheetId', $sheetId_array);
	update_option('elefgs_feeds', $sheetdata);

	if ($return_ajax == true) {
		if ($init == 'yes') {
			wp_send_json_success(array("success" => 'yes'));
		} else {
			wp_send_json_success(array("success" => 'no'));
		}
	}
}


/**
 * Retrieves Google account profile information using OAuth2 service.
 *
 * - Uses authenticated Google Client instance
 * - Fetches user profile data from Google OAuth2 API
 * - Returns full user object on success
 * - Logs error if authentication fails
 *
 * @return object|false Google user object or false on failure
 */
public function gsheet_get_google_account()
{
    $user = false; // ✅ initialize

    try {
    	$client = $this->getInstance();

    	if (!$client) {
    		return false;
    	}

    	$service = new Google_Service_Oauth2($client);
    	$user = $service->userinfo->get();

    } catch (Exception $e) {
    	GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
    }

    return $user;
}


/**
 * Retrieves the email address of the connected Google account.
 *
 * - Calls Google OAuth userinfo API internally
 * - Extracts email from returned user profile object
 *
 * @return string Connected Google account email or empty string
 */
public function gsheet_get_google_account_email()
{
	$google_account = $this->gsheet_get_google_account();

	if ($google_account) {
		return $google_account->email;
	} else {
		return "";
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
		$google_sheet = new GSC_Elementor_Free();
		$google_sheet->auth();
		$email = $google_sheet->gsheet_get_google_account_email();
		update_option('gsc_elementor_email_account', $email);
		return $email;
	} catch (Exception $e) {
		GsEl_Connector_Utility::ele_gs_debug_log($e->getMessage());
		return false;
	}
}

/**
 * Updates stored spreadsheet and feed mapping in WordPress options.
 *
 * - Adds or updates spreadsheet entry in elefgs_sheetId option
 * - Adds default worksheet mapping (Sheet1) in elefgs_feeds option
 * - Ensures option arrays are initialized before update
 *
 * @param string $spreadsheet_id Google Spreadsheet ID
 * @param string $sheet_title    Spreadsheet name/title
 * @return void
 */
public function update_google_spreadsheets_option($spreadsheet_id, $sheet_title)
{

	$gfgs_sheetId = get_option('elefgs_sheetId');
	$gfgs_feeds = get_option('elefgs_feeds');

	if (!$gfgs_sheetId) {
		$gfgs_sheetId = array();
	}
	if (!$gfgs_feeds) {
		$gfgs_feeds = array();
	}

	$gfgs_sheetId[$sheet_title] = array(
		"id" => $spreadsheet_id,
		"tabId" => array(
			"Sheet1" => 0
		),
	);

	$gfgs_feeds[$sheet_title] = array(
		"0" => "Sheet1",
	);

	update_option('elefgs_sheetId', $gfgs_sheetId);
	update_option('elefgs_feeds', $gfgs_feeds);
}
}
