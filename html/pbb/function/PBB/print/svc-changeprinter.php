<?php
session_start();
class SvcPrinter
{
	private $dbSpec = null;

	public function __construct($dbSpec)
	{
		$this->dbSpec = $dbSpec;
	}

	public function setPrinter($printer, $uid, $m)
	{
		$bOK = false;
		$res = null;
		$query = "DELETE FROM cppmod_pbb_user_printer WHERE CPM_UID = '" . $uid . "' AND CPM_MODULE = '" . $m . "' ";
		$printer = str_replace('\\', '\\\\', $printer);

		if ($this->dbSpec->sqlQuery($query, $res)) {
			$query = "INSERT INTO cppmod_pbb_user_printer VALUES('" . $uid . "','" . $m . "','" . $printer . "','epson')";
			$bOK = $this->dbSpec->sqlQuery($query, $res);
		}

		return $bOK;
	}
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'print', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");

require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$svcPrinter = new SvcPrinter($dbSpec);

$getSvcRequest = (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest = base64_decode($getSvcRequest);
$json = new Services_JSON();
$prm = $json->decode($getSvcRequest);

$svr_param = $json->decode(base64_decode($prm->SVR_PRM));
$printer = $prm->PRINTER;
$uid = $prm->UID;
$m = $prm->MODULE;
if (substr($printer, 0, 1) == '\\') $printer = '\\' . $printer;

$bOK = $svcPrinter->setPrinter($printer, $uid, $m);

$_SESSION['printerName'] = $printer;

if ($bOK) {
	echo "sukses";
} else echo "gagal";
