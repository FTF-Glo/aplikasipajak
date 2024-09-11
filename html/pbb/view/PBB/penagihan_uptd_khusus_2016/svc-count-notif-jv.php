<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_uptd_khusus_2016', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    //SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

    if(!empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbpwd']) && !empty($_POST['dbname']) && !empty($_POST['sp1']) && !empty($_POST['sp2']) && !empty($_POST['sp3'])){
        //akses database gateway devel
        SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_POST['dbhost'],$_POST['dbuser'],$_POST['dbpwd'],$_POST['dbname']);

        $sp1 = $_POST['sp1'];
        $sp2 = $_POST['sp2'];
        $sp3 = $_POST['sp3'];

        $SP1 = "SELECT COUNT(*) AS TOTALROWS FROM PBB_SPPT WHERE (PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK >= '2007' AND DATEDIFF(CURDATE(), DATE(SPPT_TANGGAL_JATUH_TEMPO)) >= $sp1 AND (TGL_SP1 = '' OR TGL_SP1 IS NULL) AND STATUS_CETAK = 'Belum Tercetak'";
        $SP2 = "SELECT COUNT(*) AS TOTALROWS FROM PBB_SPPT WHERE (PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK >= '2007' AND DATEDIFF(CURDATE(), DATE(TGL_SP1)) >= $sp2 AND (TGL_SP2 = '' OR TGL_SP2 IS NULL) AND STATUS_CETAK = 'Belum Tercetak'";
        $SP3 = "SELECT COUNT(*) AS TOTALROWS FROM PBB_SPPT WHERE (PAYMENT_FLAG != 1 OR PAYMENT_FLAG IS NULL) AND SPPT_TAHUN_PAJAK >= '2007' AND DATEDIFF(CURDATE(), DATE(TGL_SP2)) >= $sp3 AND (TGL_SP3 = '' OR TGL_SP3 IS NULL) AND STATUS_CETAK = 'Belum Tercetak'";

        $resultSP1 = mysqli_query($DBLinkLookUp, $SP1);
        $resultSP2 = mysqli_query($DBLinkLookUp, $SP2);
        $resultSP3 = mysqli_query($DBLinkLookUp, $SP3);

        if($resultSP1 && $resultSP2 && $resultSP3){
            $countSP1 = mysqli_fetch_assoc($resultSP1);
            $countSP2 = mysqli_fetch_assoc($resultSP2);
            $countSP3 = mysqli_fetch_assoc($resultSP3);
            $countAll = $countSP1['TOTALROWS']+$countSP2['TOTALROWS']+$countSP3['TOTALROWS'];
            echo $countSP1['TOTALROWS']."+".$countSP2['TOTALROWS']."+".$countSP3['TOTALROWS']."+".$countAll;
        }else{
            echo mysqli_error($DBLink);
        }
    }else{
        echo "Missing db parameter!";
    }
?>