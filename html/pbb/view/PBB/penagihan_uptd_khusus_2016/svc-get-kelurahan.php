<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan_uptd_khusus_2016', '', dirname(__FILE__))).'/';
    require_once($sRootPath."inc/payment/constant.php");
    require_once($sRootPath."inc/payment/db-payment.php");
    require_once($sRootPath."inc/payment/inc-payment-db-c.php");

    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
    $kelurahan = array();

    if(!empty ($_REQUEST['id'])){
        $sql = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_KCID='".$_REQUEST['id']."'";
        $buffer = mysqli_query($DBLink, $sql);
        if(mysqli_num_rows($buffer) > 0){
            while($kel = mysqli_fetch_assoc($buffer)){
                $tmp = array(
                    "id" => $kel['CPC_TKL_ID'],
                    "nama" => $kel['CPC_TKL_KELURAHAN']
                );
                $kelurahan[] = $tmp;
            }

            $optKel="<option value=\"\">--semua--</option>";
            for($ctr=0; $ctr<count($kelurahan); $ctr++){
                $optKel .= "<option value=\"".$kelurahan[$ctr]["id"]."\">".ucfirst(strtolower($kelurahan[$ctr]["nama"]))."</option>";
            }
            echo $optKel;
            
        }else{
            echo mysqli_error($DBLink);
        }
    }else{
            echo $optKel="<option value=\"\">--semua--</option>";
    }
?>