<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

    if(!empty($_POST['nop']) && !empty($_POST['thnpajak']) && !empty($_POST['tgl'])  && !empty($_POST['sp']) && !empty($_POST['dbhost']) && !empty($_POST['dbuser']) && !empty($_POST['dbpwd']) && !empty($_POST['dbname'])){
        //akses database gateway devel
        SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $_POST['dbhost'],$_POST['dbuser'],$_POST['dbpwd'],$_POST['dbname']);
        
        $sql = "SELECT COUNT(*) AS TOTAL FROM PBB_SPPT_PENAGIHAN WHERE NOP='".$_POST['nop']."' AND SPPT_TAHUN_PAJAK='".$_POST['thnpajak']."'";
        $result = mysqli_query($DBLinkLookUp, $sql);
        if($result){
            $rowCount = mysqli_fetch_array($result);
            if($rowCount['TOTAL'] == 0){
                $sql = "INSERT INTO PBB_SPPT_PENAGIHAN (NOP, SPPT_TAHUN_PAJAK) VALUES ('".$_POST['nop']."' , '".$_POST['thnpajak']."')";
                $result = mysqli_query($DBLinkLookUp, $sql);
                if(!$result) {echo mysqli_error($DBLink); exit();}
            }
            
            $field = "";
            switch ($_POST['sp']){
                case "SP1" : $field = "TGL_SP1"; break;
                case "SP2" : $field = "TGL_SP2"; break;
                case "SP3" : $field = "TGL_SP3"; break;
                case "STPD" : $field = "TGL_STPD"; break;
            }
            
            if($_POST['sp'] != "STPD")
                $sql = "UPDATE PBB_SPPT_PENAGIHAN SET $field='".$_POST['tgl']."', STATUS_SP='CLOSED' WHERE NOP='".$_POST['nop']."' AND SPPT_TAHUN_PAJAK='".$_POST['thnpajak']."'";
            else $sql = "UPDATE PBB_SPPT_PENAGIHAN SET $field='".$_POST['tgl']."' WHERE NOP='".$_POST['nop']."' AND SPPT_TAHUN_PAJAK='".$_POST['thnpajak']."'";
            $result = mysqli_query($DBLinkLookUp, $sql);
            if(!$result){
                echo mysqli_error($DBLink);
                //exit(1);
            }else{
                echo "1";
            }
        }else echo mysqli_error($DBLink);
        
        
    }else{
        echo "0";
    }

?>