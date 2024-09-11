<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_uptd_khusus_2016', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");
	require_once($sRootPath."inc/central/user-central.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$appConfig 	= $User->GetAppConfig('aPBB');
	$dbhost 	= $appConfig['GW_DBHOST'];
	$dbuser 	= $appConfig['GW_DBUSER'];
	$dbpwd 		= $appConfig['GW_DBPWD'];
	$dbname 	= $appConfig['GW_DBNAME'];
	//akses database gateway devel
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbhost,$dbuser,$dbpwd,$dbname, true);

    if(!empty($_POST['nop']) && !empty($_POST['thnpajak'])){

        $sql = "SELECT COUNT(*) AS TOTAL FROM PBB_SPPT_PENAGIHAN_KHUSUS WHERE NOP='".$_POST['nop']."' AND SPPT_TAHUN_PAJAK='".$_POST['thnpajak']."'";
        $result = mysqli_query($DBLinkLookUp, $sql);
        if($result){
            $rowCount = mysqli_fetch_array($result);
            if($rowCount['TOTAL'] == 0){
                $sql = "INSERT INTO PBB_SPPT_PENAGIHAN_KHUSUS (NOP, SPPT_TAHUN_PAJAK) VALUES ('".$_POST['nop']."' , '".$_POST['thnpajak']."')";
                $result = mysqli_query($DBLinkLookUp, $sql);
                if(!$result) {echo mysqli_error($DBLink); exit();}
            }
            
            $sql = "UPDATE PBB_SPPT_PENAGIHAN_KHUSUS SET STATUS_CETAK='Telah Tercetak' WHERE NOP='".$_POST['nop']."' AND SPPT_TAHUN_PAJAK='".$_POST['thnpajak']."'";
            $result = mysqli_query($DBLinkLookUp, $sql);
            if(!$result){
                echo mysqli_error($DBLink);
                //exit(1);
            }
        }
    }else{
        echo "Missing nop parameter!";
    }

?>