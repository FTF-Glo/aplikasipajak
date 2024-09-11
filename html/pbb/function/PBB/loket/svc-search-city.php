<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getKabkota($idProv=""){    
    global $DBLink;	
    
    $qwhere = "";
    if($idProv){
        $qwhere = " WHERE CPC_TK_PID='$idProv'";
    }
    
    $qry = "SELECT * FROM cppmod_tax_kabkota ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TK_ID'],
                'pid' => $row['CPC_TK_PID'],
                'name' => $row['CPC_TK_KABKOTA']
            );
            $data[] = $tmp;
        }        
        return $data;
    }
}

function getKecamatan($idKab=""){    
    global $DBLink;	
    
    $qwhere = "";
    if($idKab){
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
    }
    
    $qry = "SELECT * FROM cppmod_tax_kecamatan ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TKC_ID'],
                'pid' => $row['CPC_TKC_KKID'],
                'name' => $row['CPC_TKC_KECAMATAN']
            );
            $data[] = $tmp;
        }        
        return $data;
    }
}

function getKelurahan($idKec=""){    
    global $DBLink;	
    
    $qwhere = "";
    if($idKec){
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
    }
    
    $qry = "SELECT * FROM cppmod_tax_kelurahan ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $digit3 = substr($row['CPC_TKL_ID'],7,3) . " - ";

            $tmp = array(
                'id' => $row['CPC_TKL_ID'],
                'pid' => $row['CPC_TKL_KCID'],
                'name' => $digit3 . $row['CPC_TKL_KELURAHAN']
            );
            $data[] = $tmp;
        }        
        return $data;
    }
}

switch($_REQUEST['type']){
    case 1  : $data = getKabkota($_REQUEST['id']);
              $optionKab = "";
              foreach($data as $row){
                    $optionKab .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              
              $data = getKecamatan($data[0]['id']);              
              $optionKec = "";
              foreach($data as $row){
                    $optionKec .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              
              $optionKab .= "|".$optionKec;
              
              $data = getKelurahan($data[0]['id']);
              $optionKel = "";
              foreach($data as $row){
                    $optionKel .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              
              $optionKab .= "|".$optionKel;              
              echo $optionKab;
              break;
              
    case 2  : $data = getKecamatan($_REQUEST['id']);
              $optionKec = "";
              foreach($data as $row){
                    $optionKec .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              
              $data = getKelurahan($data[0]['id']);
              $optionKel = "";
              foreach($data as $row){
                    $optionKel .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              
              $optionKec .= "|".$optionKel;
              echo $optionKec;              
              break;
              
    case 3  : $data = getKelurahan($_REQUEST['id']);
              $optionKel = "";
              foreach($data as $row){
                    $optionKel .= "<option value=".$row['id'].">".$row['name']."</option>";            
              }
              echo $optionKel;
              break;
}

?>