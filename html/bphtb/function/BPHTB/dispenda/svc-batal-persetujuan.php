<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

$ssbid = @isset($_REQUEST['ssbid']) ? $_REQUEST['ssbid'] : "";
$tranid = @isset($_REQUEST['tranid']) ? $_REQUEST['tranid'] : "";

function getConfigValue ($key) {
	global $DBLink,$idapp;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getRowTranmain($ssbid) {
	global $DBLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select count(*) AS jml from cppmod_ssb_tranmain where CPM_TRAN_SSB_ID = '$ssbid'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['jml'];
	}
}

function BatalkanPersetujuan() {
	global $DBLink, $ssbid, $data, $tranid;
	
		$dbName  = getConfigValue('BPHTBDBNAME');
        $dbHost  = getConfigValue('BPHTBHOSTPORT');
        $dbPwd   = getConfigValue('BPHTBPASSWORD');
        $dbTable = getConfigValue('BPHTBTABLE');
        $dbUser  = getConfigValue('BPHTBUSERNAME');
        $dbLimit = getConfigValue('TENGGAT_WAKTU');
		
		$jml = getRowTranmain($ssbid);
		
		$opr = $data->uname;
		$final = '15';
		SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
		
		$deletessb = "delete from ssb WHERE id_switching='".$ssbid."'";
		//echo $queryinserthistory;exit();
		 
		$resultinserthistory = mysqli_query($DBLinkLookUp, $deletessb);
		 if ($resultinserthistory === false) {
			//echo mysqli_error($DBLink) . $query;
			$respon = "0";
			//$respon_data = "Gagal Update ke ssb / Tagihan";
		}else{
			// $querydeletetranmain = "delete from cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID='".$ssbid."' AND (CPM_TRAN_STATUS!=1 OR CPM_TRAN_STATUS!=2)";
			// //echo $query_update_GW;exit();
			// $resultdeletedoc = mysqli_query($DBLink, $querydeletetranmain);
			if ($resultdeletedoc === false) {
				//echo mysqli_error($DBLink) . $query;
				$respon = "0";
			}else{
				if($jml>1){
							// $querydeletetranmain2 = "delete from cppmod_ssb_tranmain WHERE CPM_TRAN_SSB_ID='".$ssbid."' AND CPM_TRAN_STATUS!=1";
							// $resultdeletetranmain2 = mysqli_query($DBLink, $querydeletetranmain);
							if ($resultdeletetranmain2 === false) {
								//echo mysqli_error($DBLink) . $query;
								$respon = "0";
							}else{
								$queryupdatetranmain = "UPDATE cppmod_ssb_tranmain SET CPM_TRAN_FLAG='0', CPM_TRAN_STATUS='3' WHERE CPM_TRAN_ID ='".$tranid."'";
								//echo $query_update_GW;exit();
								$resultupdatedoc = mysqli_query($DBLink, $queryupdatetranmain);
								if ($resultupdatedoc === false) {
									//echo mysqli_error($DBLink) . $query;
									$respon = "0";
								}else{
									$select_data = "SELECT * FROM cppmod_ssb_doc WHERE CPM_TRAN_SSB_ID='".$ssbid."'";
									
									//echo $query_update_GW;exit();
									$resultselect_data = mysqli_query($DBLink, $select_data);
									$row = mysqli_fetch_array(resultselect_data);
									
									$log_input = "insert into cppmod_ssb_log(
													CPM_SSB_ID,
													CPM_SSB_LOG_ACTOR,
													CPM_SSB_LOG_ACTION,
													CPM_OP_NOMOR,
													CPM_WP_NAMA,
													CPM_SSB_AUTHOR) 
											values ('" . mysqli_real_escape_string($DBLink, $ssbid) . "',
													'" . mysqli_real_escape_string($DBLink, $opr) . "',                                   
													'" . mysqli_real_escape_string($DBLink, $final) . "',
													'" . mysqli_real_escape_string($DBLink, $row['CPM_OP_NOMOR']) . "',
													'" . mysqli_real_escape_string($DBLink, $row['CPM_WP_NAMA']) . "',
													'" . mysqli_real_escape_string($DBLink, $row['CPM_SSB_AUTHOR']) . "')";
													
									$resulinputlog = mysqli_query($DBLink, $log_input);				
									
											if ($resulinputlog === false) {
												//echo mysqli_error($DBLink) . $query;
												$respon = "0";
											}else{
												
												$respon = "1";
											}
								}
							}
				}else{
							$queryupdatetranmain = "UPDATE cppmod_ssb_tranmain SET CPM_TRAN_FLAG='0',CPM_TRAN_STATUS='1' WHERE CPM_TRAN_SSB_ID='".$ssbid."'";
							//echo $query_update_GW;exit();
							$resultupdatedoc = mysqli_query($DBLink, $queryupdatetranmain);
							if ($resultupdatedoc === false) {
								//echo mysqli_error($DBLink) . $query;
								$respon = "0";
							}else{
								$select_data = "SELECT * FROM cppmod_ssb_doc WHERE CPM_TRAN_SSB_ID='".$ssbid."'";
								
								//echo $query_update_GW;exit();
								$resultselect_data = mysqli_query($DBLink, $select_data);
								$row = mysqli_fetch_array(resultselect_data);
								
								$log_input = "insert into cppmod_ssb_log(
												CPM_SSB_ID,
												CPM_SSB_LOG_ACTOR,
												CPM_SSB_LOG_ACTION,
												CPM_OP_NOMOR,
												CPM_WP_NAMA,
												CPM_SSB_AUTHOR) 
										values ('" . mysqli_real_escape_string($DBLink, $ssbid) . "',
												'" . mysqli_real_escape_string($DBLink, $opr) . "',                                   
												'" . mysqli_real_escape_string($DBLink, $final) . "',
												'" . mysqli_real_escape_string($DBLink, $row['CPM_OP_NOMOR']) . "',
												'" . mysqli_real_escape_string($DBLink, $row['CPM_WP_NAMA']) . "',
												'" . mysqli_real_escape_string($DBLink, $row['CPM_SSB_AUTHOR']) . "')";
												
								$resulinputlog = mysqli_query($DBLink, $log_input);				
								
										if ($resulinputlog === false) {
											//echo mysqli_error($DBLink) . $query;
											$respon = "0";
										}else{
											
											$respon = "1";
										}
							}
				}
				
				
			}
		}
	
	return $respon;
}


$response = BatalkanPersetujuan();
		
echo $response;


?>
