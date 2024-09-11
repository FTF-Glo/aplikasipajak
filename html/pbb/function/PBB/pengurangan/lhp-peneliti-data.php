<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/payment/json.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

	$json = new Services_JSON();
	$response = array();
	
	function getPeneliti($nama=""){
		global $DBLink;
		$nama = trim($nama);
		$sql = "SELECT jabatan,nip FROM TBL_REG_USER_PBB WHERE nm_lengkap='$nama'";
		$res = mysqli_query($DBLink, $sql);
		if (!$res){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		} else {
			$data = array();
			while ($row = mysqli_fetch_array($res)) {
				$temp = array('nip' => $row['nip'],'jabatan' => $row['jabatan']);
				$data = $temp;
			}
			return $data;
		}
		
	}
	$nama = $_REQUEST['nama'];
	$dataP = getPeneliti($nama);
	$response['r'] = true;
	$response['dataP'] = $dataP;
	$val = $json->encode($response);
	echo $val;
	
?>