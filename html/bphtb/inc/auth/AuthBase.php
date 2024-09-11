<?php

// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();
error_reporting(E_ALL);
// includes
// $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'pc'.DIRECTORY_SEPARATOR.'svr'.DIRECTORY_SEPARATOR.'central', '', dirname(__FILE__))).'/';
require_once("inc/payment/constant.php");
require_once("inc/payment/db-payment.php");
require_once("inc/payment/ctools.php");
require_once("inc/payment/json.php");
require_once("inc/payment/inc-payment-c.php");
require_once("inc/payment/inc-payment-db-c.php");
require_once("inc/central/session-central.php");
require_once("inc/central/user-central.php");

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	$iStart = microtime(true);
}

// global variables
$iCentralTS = time();
$iErrCode = 0;
$sErrMsg = '';
$sResponse = '';
$DBLink = NULL;
$DBConn = NULL;
$aCentralPrefs = NULL;

// Payment related initialization
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$Session = new SCANCentralDBSession(DEBUG, LOG_FILENAME, $DBLink, ONPAYS_SESSION_INTERVAL);
$User = new SCANCentralUser(DEBUG, LOG_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] aCentralPrefs [".print_r($aCentralPrefs, true)."]\n", 3, LOG_FILENAME);

/**
 * Abstract class for Authentication process.
 */
abstract class AuthBase {
	/**
	 * Return element rendered by login page, with the following key and example value:
	 *     - label          : Username
	 *     - id             : usr
	 *     - initvalue		: true/false
	 *     - input          : <input type='text' id='usr' name='usr' value='' autocomplete='off'></input>
	 *     - td (optional)  : align='left'
	 *     - type			: Type input
	 *     - autocomplete	: on/off
	 * return array element object
	 */
	abstract function element();
	
	/**
	 * Authentication process
	 * parameter $input: array with key 'id's element, and its value from login page
	 *     e.g. : array[2] {
	 *                "usr" => "myusername"
	 *                "pwd" => "mypassword"
	 *            }
	 * parameter (output) $arResponse: array value with authentication response
	 *     if process failed, response array with key "error" with value error message is required
	 *     e.g. : array[1] {
	 *                "error" => "wrong password"
	 *            }
	 * return true if authentication succeed, false otherwise
	 */
	abstract function auth(&$input, &$arResponse);
}

?>
