<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = '/var/www/html/bphtb/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

function get_data(){
	global $DBLink;
	$query = "SELECT a.cpm_ssb_created,a.cpm_ssb_id, d.cpm_tran_id FROM
  				cppmod_ssb_doc a
				  LEFT JOIN cppmod_ssb_berkas b ON a.cpm_ssb_id = b.cpm_ssb_doc_id
				  LEFT JOIN cppmod_ssb_upload_file c ON a.cpm_ssb_id = c.cpm_ssb_id
				  LEFT JOIN cppmod_ssb_tranmain d ON a.cpm_ssb_id = d.cpm_tran_ssb_id
				WHERE 
				DATE_ADD(DATE(d.cpm_tran_date), INTERVAL 3 DAY) < DATE(NOW()) AND
				d.cpm_tran_flag = 0 AND 
				d.cpm_tran_status = 2 AND 
				c.cpm_id_upload IS NULL";

	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		echo $query ."<br>";
		echo mysqli_error($DBLink);
	}
	return $res;
}

$getdata = get_data();
$tran_id = '';
$ssb_id = '';
while ($row = mysqli_fetch_assoc($getdata)) {
	$tran_id .= "\"".$row['cpm_tran_id']."\",";
	$ssb_id .= "\"".$row['cpm_ssb_id']."\",";
}
if ($tran_id=='') {
	die('Tidak ada data yang diperbaharui');
}
$updt_date = date("Y-m-d H:i:s");
$query_update_tranmain = 'UPDATE cppmod_ssb_tranmain
						SET cpm_tran_status = 1, cpm_tran_claim = 0, cpm_tran_read = 0, 
						cpm_tran_info = "Pelaporan tidak ada kegiatan upload, update by system",
						cpm_tran_date = "'.$updt_date.'"
						WHERE cpm_tran_id IN ('.rtrim($tran_id,",").')';
$ex_1 = mysqli_query($DBLink, $query_update_tranmain);
if ($ex_1 === false) {
	echo $query_update_tranmain ."<br>";
	die(mysqli_error($DBLink));
}
else{
	echo 'Success UPDATE SSB_TRANMAIN';
}
$query_delete_berkas = 'DELETE FROM cppmod_ssb_berkas WHERE cpm_ssb_doc_id IN ('.rtrim($ssb_id,",").')';
$ex_2 = mysqli_query($DBLink, $query_delete_berkas);
if ($ex_2 === false) {
	echo $query_delete_berkas ."<br>";
	echo mysqli_error($DBLink);
}
else{
	echo 'TERJADI KESALAHAN';
}