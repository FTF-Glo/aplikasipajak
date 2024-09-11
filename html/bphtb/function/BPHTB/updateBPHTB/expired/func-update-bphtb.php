<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'updateBPHTB/expired', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php"); 
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."function/BPHTB/updateBPHTB/expired/func-form-update-bphtb.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
//echo $paytype;
$submit = @isset($_REQUEST['submit'])=='Submit' ? true : false;
if ($submit) {
		$dbName = getConfigValue($a,'BPHTBDBNAME');
		$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
		$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
		$dbTable = getConfigValue($a,'BPHTBTABLE');
		$dbUser = getConfigValue($a,'BPHTBUSERNAME');
		SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	
	
	$data = array();
	
	$id_switching = @isset($_REQUEST['id_switching'])? $_REQUEST['id_switching']:"0";
	$expired = @isset($_REQUEST['expired_date'])? $_REQUEST['expired_date']:"0";
	
	#proses ke table ssb
	$update = "update ssb set expired_date='".$expired."'
                                            where 
                                            id_switching = '".$id_switching."'";
					
	//echo $update;exit;						 
	$qry = mysqli_query($DBLinkLookUp, $update);
	
	if($qry){
		echo 'Update Berhasil ';
	}else{
		echo 'Update Gagal ';
	}
	
		echo "\n<script language=\"javascript\">\n";
	
	echo "	function delayer(){\n";
	echo "		window.location = \"./main.php?param=YT1hQlBIVEImbT1tMTQy\"\n";
	echo "	}\n";
	echo "	Ext.onReady(function(){\n";
	echo "		setTimeout('delayer()', 3500);\n";
	echo "	});\n";
	echo "</script>\n";
}else{
	getSelectedData($idssb,$data);
	if($paytype!=2){
		$dbName = getConfigValue($a,'BPHTBDBNAME');
			$dbHost = getConfigValue($a,'BPHTBHOSTPORT');
			$dbPwd = getConfigValue($a,'BPHTBPASSWORD');
			$dbUser = getConfigValue($a,'BPHTBUSERNAME');
			SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
			
		$query = "SELECT * FROM ssb WHERE id_switching = '".$idssb."'";
		$datas = mysqli_query($DBLinkLookUp, $query);
		$datas = mysqli_fetch_object($datas);
		//var_dump($datas);
		echo formSSB ($datas, true);
	}else{
		echo formSSBKB ($data, true);
	}
	

}
function getConfigValues($key) {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}
function getNOKTP($noktp) {
    global $DBLink;

    $N1 = getConfigValues('NPOPTKP_STANDAR');
    $N2 = getConfigValues('NPOPTKP_WARIS');
    $day = getConfigValues("BATAS_HARI_NPOPTKP");
    $dbLimit = getConfigValues('TENGGAT_WAKTU');

    $dbName = getConfigValues('BPHTBDBNAME');
    $dbHost = getConfigValues('BPHTBHOSTPORT');
    $dbPwd = getConfigValues('BPHTBPASSWORD');
    $dbTable = getConfigValues('BPHTBTABLE');
    $dbUser = getConfigValues('BPHTBUSERNAME');
    // Connect to lookup database
    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
    //payment_flag, mysqli_real_escape_string($payment_flag),
	$tahun = date('Y');
    $qry = "select * 
	        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' and CPM_OP_THN_PEROLEH= '{$tahun}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
			AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 30 AND A.CPM_OP_JENIS_HAK <> 31 
			AND A.CPM_OP_JENIS_HAK <> 32 AND A.CPM_OP_JENIS_HAK <> 33";
//print_r($qry); 
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        return false;
    }

    if (mysqli_num_rows ($res)) {
		$num_rows = mysqli_num_rows($res);
		$query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
				FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
		//print_r($query2);
		$r = mysqli_query($DBLinkLookUp, $query2);
		if ( $r === false ){
			die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
		}
		if(mysqli_num_rows ($r)==0){
		
			$query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
			$r2 = mysqli_query($DBLinkLookUp, $query3);
			//print_r($query3);exit;
			if ( $r2 === false ){
				die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
			}
			if (mysqli_num_rows($r2)) {
				return true;
			}else{
				return false;
			}					
		}else{
			while($rowx = mysqli_fetch_assoc($r)){
				if ($rowx['EXPRIRE']) {
					return false;
				}else{
					return true;
				}
			
			}
		}
	}
	else return false;
}
?>