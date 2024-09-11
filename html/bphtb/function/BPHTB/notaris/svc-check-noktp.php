<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';
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


function getConfigValue ($key) {
	global $DBLink,$idapp;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = '".$idapp."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getNOKTP ($noktp,$nop="",$jh,$cek_tahun,$cek_jenis_perolehan) {
	global $DBLink;

	$N1= getConfigValue('NPOPTKP_STANDAR');
	$N2= getConfigValue('NPOPTKP_WARIS');
	$day = getConfigValue("BATAS_HARI_NPOPTKP");
	$dbLimit = getConfigValue('TENGGAT_WAKTU');
	
	$CHECK_NPOPTKP_KTP_PAYMENT = getConfigValue('CHECK_NPOPTKP_KTP_PAYMENT');
	$CONFIG_PEMB_NPOPTKP = getConfigValue('CONFIG_PEMB_NPOPTKP');
	
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
	$tahun = date('Y');
	// Connect to lookup database
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	//payment_flag, mysqli_real_escape_string($payment_flag),
		
	
		
	/*$qry = "select sum(A.CPM_SSB_AKUMULASI) AS mx from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
where A.CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 6 AND A.CPM_OP_JENIS_HAK <> 4";*/

	/*$qry = "select if(COUNT(*)<>1,sum(A.CPM_SSB_AKUMULASI),A.CPM_SSB_AKUMULASI) AS mx 
	        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
			AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 6 AND A.CPM_OP_JENIS_HAK <> 4";*/
			
	$qry = "select * 
	        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' $cek_tahun and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
			AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1  $cek_jenis_perolehan ";

//AND B.CPM_TRAN_STATUS <> 1
//print_r($qry); 
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		return false;
	}
	
	if($CHECK_NPOPTKP_KTP_PAYMENT==0){
		if (mysqli_num_rows ($res)) {
		//$num_rows = mysql_num_rows($res);
		// while($row = mysql_fetch_assoc($res)){
				// $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
						// FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
				// //print_r($query2);
				// $r = mysql_query($query2, $DBLinkLookUp);
				// if ( $r === false ){
					// die("Error Insertxx: ".mysql_error());
				// }
				// if(mysql_num_rows ($r)){
					
					// while($rowx = mysql_fetch_assoc($r)){
						// if ($rowx['EXPRIRE']) {
							// return false;
						// }else{
							// $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
							// $r2 = mysql_query($query3, $DBLinkLookUp);
							// if ( $r2 === false ){
								// die("Error Insertxx: ".mysql_error());
							// }
							// if (mysql_num_rows($r2)) {
								// return true;
							// }
						// }
					// }
					// return true;
				// }else return false;
		// }
		return true;


		}else return false;
	}else{
		if (mysqli_num_rows ($res)) {
			$num_rows = mysqli_num_rows($res);
			while($row = mysqli_fetch_assoc($res)){
					$query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
							FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
					//print_r($query2);
					$r = mysqli_query($DBLinkLookUp, $query2);
					if ( $r === false ){
						die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
					}
					if(mysqli_num_rows ($r)){
						
						while($rowx = mysqli_fetch_assoc($r)){
							if ($rowx['EXPRIRE']) {
								return false;
							}else{
								$query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
								$r2 = mysqli_query($DBLinkLookUp, $query3);
								if ( $r2 === false ){
									die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
								}
								if (mysqli_num_rows($r2)) {
									return true;
								}
							}
						}
						return true;
					}else return false;
			}
		}
		else return false;
	}
}




$Response = array();
$Response["success"] = false;
$Response["found"] = false; 
$oRequest = "";
$tahun = date('Y');

//if(isset($_POST["req"])){ 
	$req = $_REQUEST["req"];
	$oRequest = $json->decode(base64_decode($req));
	$idapp = base64_decode($oRequest->axx);

	$CHECK_NPOPTKP_KTP_PAYMENT = getConfigValue('CHECK_NPOPTKP_KTP_PAYMENT');
	$CONFIG_PEMB_NPOPTKP = getConfigValue('CONFIG_PEMB_NPOPTKP');
	if($CONFIG_PEMB_NPOPTKP=='1'){
		$Response["found"] = false; 
	}else if($CONFIG_PEMB_NPOPTKP=='2'){
		$cek_tahun = "";
		$cek_jenis_perolehan ="";
			if(getNOKTP ($oRequest->noktp,$oRequest->n,$oRequest->jh,$cek_tahun,$cek_jenis_perolehan)) {
			$Response['found'] = true;
		}
	}else if($CONFIG_PEMB_NPOPTKP=='3'){
		$cek_tahun = " and SUBSTRING(CPM_SSB_CREATED, 1, 4)= '{$tahun}' ";
		$cek_jenis_perolehan ="";
		
		if(getNOKTP ($oRequest->noktp,$oRequest->n,$oRequest->jh,$cek_tahun,$cek_jenis_perolehan)) {
			$Response['found'] = true;
		}

	}else if($CONFIG_PEMB_NPOPTKP=='4'){
		$cek_tahun = " and CPM_OP_THN_PEROLEH= '{$tahun}' ";
		$cek_jenis_perolehan =" AND A.CPM_OP_JENIS_HAK = {$oRequest->jh} ";
		
		if(getNOKTP ($oRequest->noktp,$oRequest->n,$oRequest->jh,$cek_tahun,$cek_jenis_perolehan)) {
			$Response['found'] = true;
		}
	}
	
	
	$Response["success"] = true;
//}

echo $json->encode($Response);

?>
