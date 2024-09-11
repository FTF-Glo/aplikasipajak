<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pembayaran_va', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once("classCollective.php");
$DBLink = NULL;
$DBConn = NULL;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $DBLink);
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$dbUtils = new DbUtils($dbSpec);

if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$setting 	= new SCANCentralSetting(0, LOG_FILENAME, $DBLink);
$q 			= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$nop 		= @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$status 	= @isset($_REQUEST['st']) ? $_REQUEST['st'] : "";

// print_r($_REQUEST);
// exit;
// echo "2k2k2";

// if ($q=="") exit(1);


$q 			= base64_decode($q);
$j 			= $json->decode($q);
$uid 		= $j->uid;
$area 		= $j->a;
$moduleIds 	= $j->m;

$arConfig 	= $User->GetModuleConfig($moduleIds);
// $appConfig 	= $User->GetAppConfig($area);
$appConfig 	= $User->GetAppConfig("aPBB");
$tahun		= $appConfig['tahun_tagihan'];
// print_r($appConfig);
$host 	= $appConfig['GW_DBHOST'];
$port 	= $appConfig['GW_DBPORT'];
$user 	= $appConfig['GW_DBUSER'];
$pass 	= $appConfig['GW_DBPWD'];
$dbname = $appConfig['GW_DBNAME'];
// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
// exit;
$svcCollective = new classCollective($dbSpec, $dbUtils);

$svcCollective->C_HOST_PORT = $host;
$svcCollective->C_USER = $user;
$svcCollective->C_PWD = $pass;
$svcCollective->C_DB = $dbname;
$svcCollective->C_PORT = $port;
// var_dump($host);
$message = "";
$nop = trim($_REQUEST['data-nop']);
if ($nop == "" && $_REQUEST['data-tahun-pajak'] != '' && $_REQUEST['data-kelurahan'] && $_REQUEST['data-buku'] != '') {
	// echo "masuk";
	$copyGroup = false;
	$copyGroup = $svcCollective->copyToGroup($_REQUEST['data-kelurahan'], $_REQUEST['data-group-id'], $_REQUEST['data-tahun-pajak'], $_REQUEST['data-buku']);
	if ($copyGroup) {
		$count = $svcCollective->getCountMemberTempByIDArray($_REQUEST['data-group-id']);
		$countKel = $svcCollective->getCountMemberKelByIDArray($_REQUEST['data-kelurahan'], $_REQUEST['data-tahun-pajak'], $_REQUEST['data-buku']);
		if ($count > 0) {
			$data = array();
			$data['masal'] = true;
			$data['success'] = true;
			$data['message'] = " $count dari $countKel NOP Berhasil di Mausukan ke Group ";
		} else {
			$data = array();
			$data['masal'] = true;
			$data['success'] = false;
			$data['message'] = "NOP yang terpilih terdapat pada Group yang telah di Finalkan, Sehingga tidak dapat dimasukan ke dalam draft, Silahkan ulangi kembali ..  ";
		}

		echo json_encode($data);
	}

	// var_dump("copyGroup: {$copyGroup}");
} else { // JIKA BUKAN PER KELURAHAN


	$table = "";
	$isFinalExist = $svcCollective->isFinalExist($_REQUEST['data-nop'], $_REQUEST['data-tahun-pajak']);
	if ($isFinalExist) {
		$table = "final";
	} else {
		$isSusulanExist = $svcCollective->isSusulanExist($_REQUEST['data-nop'], $_REQUEST['data-tahun-pajak']);
		if ($isSusulanExist) {
			$table = "susulan";
		} else {
			$table = "123";
		}
	}
	
	//echo $table;

	$multipleNop = explode(',', $_REQUEST['data-nop']);
	if(count($multipleNop) > 1) {
		// proses multiple nop
		$newData = $svcCollective->addMultipleNopCollective($_REQUEST);
		die(json_encode($newData));
	}

	if (!empty($table)) { // jika berada didalam table final atau susulan 
		$data = $svcCollective->isMemberTempExist($_REQUEST['data-nop'], $_REQUEST['data-tahun-pajak'], $_REQUEST['data-kelurahan']);

		$match = $svcCollective->isKelurahanMatch($_REQUEST['data-nop'], $_REQUEST['data-tahun-pajak'], $_REQUEST['data-kelurahan']);

		if (!$match) {
			echo json_encode(array("success" => false, "message" => " NOP tersebut tidak berada pada kelurahan yang dipilih "));
			exit;
		}


		// var_dump($data);
		// exit;
		if ($data == false) { // jika belum  ada maka
			$data = $svcCollective->isPaid($_REQUEST['data-nop'], $_REQUEST['data-tahun-pajak']);
			// var_dump($data);
			// exit;
			// exit;
			if (!empty($data)) {
				if ($data['PAYMENT_FLAG'] != 1 || $data['PAYMENT_FLAG'] === NULL) { // jika belum bayar maka 

					// $status = array("success"=>true);

					//simpan ke table member 
					$param = array();
					$param['CPM_CGTM_ID'] = $_REQUEST['data-group-id'];
					$param['CPM_CGTM_NOP'] = $data['NOP'];
					$param['CPM_CGTM_TAX_YEAR'] = $data['SPPT_TAHUN_PAJAK'];

					$save = $svcCollective->saveMember($param);
					if ($save) {
						$data['success'] = true;
						// $data['PBB_DENDA'] = 0;
						// $data['PBB_TOTAL_BAYAR'] = 0;
						$data['masal'] = false;

						// array_push($data, array())
						echo json_encode($data);
					} else {
						// returnf
						$status = array("success" => false);
						// echo "gagal";
					}
				} else {
					// echo "masuk";
					// $status = array("success"=>false);
					echo json_encode(array("success" => false, "message" => "NOP tersebut telah membayar PBB Tahun " . $_REQUEST['data-tahun-pajak']));
				}
			} else {
				echo json_encode(array("success" => false, "message" => "Tidak Terdapat Data Tagihan " . $_REQUEST['data-tahun-pajak']));
			}
		} else {
			if($data['CPM_CG_ID'] != $_REQUEST['data-group-id']) {
				echo json_encode(array("success" => false, "message" => "NOP Telah ada pada Group {$data['CPM_CG_NAME']}"));
			}else {
				echo json_encode(array("success" => false, "message" => "NOP Telah ada pada Group ini  "));
			}
			// echo json_encode(array("success"=>false,"message"=>"NOP Telah ada pada Group Final  " ));
			// $status = array("success"=>false);

		}
	}
}
