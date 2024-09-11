<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dashboard', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

function getConfigValue ($id,$key) {
	
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}
		 
		 
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

$a = $_REQUEST['a'];
$month = $_REQUEST['month'];
$year = $_REQUEST['year'];

$DbName = getConfigValue($a,'BPHTBDBNAME');
$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
$DbTable = getConfigValue($a,'BPHTBTABLE');
$DbUser = getConfigValue($a,'BPHTBUSERNAME');

SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$where = " WHERE PAYMENT_FLAG = 1 AND MONTH(payment_paid) = ".$month;
$query = "SELECT payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY day(DATE(payment_paid))  ORDER BY payment_paid "; 
$res = mysqli_query($LDBLink, $query);
if ( $res === false ){
	print_r(mysqli_error($DBLink).$query);
	return "Tidak Ditemukan"; 
}

$num = cal_days_in_month(CAL_GREGORIAN, date("n"), date("Y"));
$mon = array();

$dat = array();
$v=0;
$arrVal=array();
while ($row=mysqli_fetch_array($res)) {
	$dat[$v]["tanggal"] = $row['payment_paid'];
	$dat[$v]["bayar"] = $row['sum_bphtb_dibayar'];
	$arrVal[$v] = $row['sum_bphtb_dibayar'];
	$v++;
 }
 
$mx = max($arrVal); 
$num_rows = mysqli_num_rows($res); 

$cm = strlen($mx)-2;
$pm = pow(10,$cm);


for($c=0;$c<$num;$c++){
	 $trs = 0;
	 $mon[$c] = 0;
	 
	 for ($f=0;$f<$num_rows;$f++){
			 $m = floatval(substr($dat[$f]["tanggal"],8,2));
			 //if ($m ==($c+1)) $mon[$c] = round(floatval($dat[$f]["bayar"])/$pm,2);
			 if ($m ==($c+1)) $mon[$c] = floatval($dat[$f]["bayar"]);
	 }
}

$ret['success'] = true;
$ret['data']['trs_bulan_ini'] = $mon;


$where = " WHERE PAYMENT_FLAG = 1 AND YEAR(payment_paid) = ".$year;
$query = "SELECT payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY MONTH(DATE(payment_paid))  ORDER BY payment_paid "; 

$resy = mysqli_query($LDBLink, $query);
if ( $resy === false ){
	print_r(mysqli_error($DBLink).$query);
	return "Tidak Ditemukan"; 
}


$mony = array();
$daty = array();
$v=0;
$arrValy=array();
while ($rowy=mysqli_fetch_array($resy)) {
	$daty[$v]["tanggal"] = $rowy['payment_paid'];
	$daty[$v]["bayar"] = $rowy['sum_bphtb_dibayar'];
        $daty[$v]["transaksi"]=$rowy['jml_transaksi'];
	$arrValy[$v] = $rowy['sum_bphtb_dibayar'];
	$v++;
 }
 
$mxy = max($arrValy); 
$num_rowsy = mysqli_num_rows($resy); 

$cmy = strlen($mxy)-2;
$pmy = pow(10,$cmy);


for($c=0;$c<12;$c++){
	 $trs = 0;
	 $mony[$c] = 0;
	 
	 for ($f=0;$f<$num_rowsy;$f++){
			 $m = floatval(substr($daty[$f]["tanggal"],5,2));
			 //if ($m ==($c+1)) $mony[$c] = round(floatval($daty[$f]["bayar"])/$pmy,2);
			 if ($m ==($c+1)){
                         $mony[$c] = floatval($daty[$f]["bayar"]);
                         //$mony[$c] = floatval($daty[$f]["transaksi"]);
                         }
	 }
}

$ret['data']['trs_tahun_ini'] = $mony;
//$ret['data']['transaksi']=$mony;

$where = " WHERE PAYMENT_FLAG = 1 AND DATE(payment_paid) = DATE(NOW())";
$query = "SELECT count(*) as jml_pembayar ,payment_paid,sum(bphtb_dibayar) as sum_bphtb_dibayar FROM $DbTable $where GROUP BY DATE(payment_paid) = DATE(NOW()) ORDER BY payment_paid "; 

$resh = mysqli_query($LDBLink, $query);
if ( $resh === false ){
	print_r(mysqli_error($DBLink).$query);
	return "Tidak Ditemukan"; 
}

$ret['data']['trs_hari_ini'] = 0;
$ret['data']['jml_pembayar'] = 0;
$ret['data']['per'] = $pmy;
while ($rowh=mysqli_fetch_array($resh)) {
	$ret['data']['jml_pembayar'] = $rowh['jml_pembayar'];
	$ret['data']['trs_hari_ini'] = $rowh['sum_bphtb_dibayar'];
}
$json = new Services_JSON(); 
echo $json->encode($ret); 

?>
