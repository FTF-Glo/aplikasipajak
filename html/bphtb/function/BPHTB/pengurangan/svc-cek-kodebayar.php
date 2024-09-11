<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue($key)
{
	// var_dump("sini kah?");
	// die;
	global $DBLink, $appID;
	$qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
	//echo $qry;
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getSSBID($kdb)
{
	// var_dump("sini ");
	// die;
	global $a;
	$a = "aBPHTB";
	$DbName = getConfigValue('BPHTBDBNAME');
	$DbHost = getConfigValue('BPHTBHOSTPORT');
	$DbPwd =  getConfigValue('BPHTBPASSWORD');
	$DbTable = getConfigValue('BPHTBTABLE');
	$DbUser = getConfigValue('BPHTBUSERNAME');
	//echo $DbHost. $DbUser. $DbPwd. $DbName;exit;
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}

	$qry = "select * from ssb where payment_code = '" . $kdb . "'";
	// echo $qry;
	$res = mysqli_query($LDBLink, $qry);


	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($LDBLink);
	}

	if (mysqli_num_rows($res) < 1) {
		$result = "Nan";
	} else {
		while ($row = mysqli_fetch_array($res)) {

			$result[] = array(
				'id_switching' => $row['id_switching'],
				'kodebayar' => $row['payment_code']
			);
			// if (($row['payment_flag'] == 0) || ($row['payment_flag'] == "")) {
			// 	$result = "0";
			// } else {
			// $result[] = $row['id_switching'];
			// }
		}
	}

	return $result;
}


$kode_bayar = $_POST['kodebayar'];

$res = getSSBID($kode_bayar);
// var_dump($res);
// echo $res;
$response = json_encode($res);

echo $response;
