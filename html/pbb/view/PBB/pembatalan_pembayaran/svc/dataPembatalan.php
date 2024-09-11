<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pembatalan_pembayaran'.DIRECTORY_SEPARATOR.'svc', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/uuid.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

//for GW connection
function openMysql() {
    $host = getConfig('GW_DBHOST');
    $port = '3306';
    $user = getConfig('GW_DBUSER');
    $pass = getConfig('GW_DBPWD');
    $dbname = getConfig('GW_DBNAME');
    $myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
    if (!$myDBLink) {
        echo mysqli_error($myDBLink);
    }
    
    return $myDBLink;
}

function closeMysql($con) {
    // mysqli_close($con);
}

function getConfig($key){    
    global $DBLink;	
    
    $qry = "SELECT CTR_AC_VALUE FROM `CENTRAL_APP_CONFIG` WHERE CTR_AC_KEY = '{$key}'";
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        $row = mysqli_fetch_assoc($res);
        return $row["CTR_AC_VALUE"];
    }
}

function getDataPembayaran($mode, $nop, $kode, $tahun){
    $GWDBLink = openMysql();

    if ($mode == "kode_bayar") {
        // $qry = "SELECT * FROM CPPMOD_COLLECTIVE_GROUP where CPM_CG_PAYMENT_CODE = '{$kode}'";
        $group_id = getGroupID($kode);
        $qry = "SELECT
                    * 
                FROM
                    PBB_SPPT A
                    INNER JOIN CPPMOD_CG_MEMBER BB ON A.NOP = BB.CPM_CGM_NOP 
                    AND A.SPPT_TAHUN_PAJAK = BB.CPM_CGM_TAX_YEAR
                    INNER JOIN CPPMOD_COLLECTIVE_GROUP C ON C.CPM_CG_ID = BB.CPM_CGM_ID -- SET BB.CPM_CGM_PENALTY_FEE = 0    
                WHERE
                    C.CPM_CG_ID = '{$group_id}'
                LIMIT 0,1";
    }else {
        $qry = "SELECT * FROM PBB_SPPT where NOP = '{$nop}' AND SPPT_TAHUN_PAJAK = '{$tahun}'";
    }
    // echo $qry;exit;
    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($GWDBLink);
    }else{
        while($row = mysqli_fetch_assoc($res)){
            $data["NOP_KODE"]       = ($mode == "nop")?$row["NOP"]:$row["CPM_CG_PAYMENT_CODE"];
            $data["TAHUN"]          = ($mode == "nop")?$row["SPPT_TAHUN_PAJAK"]:date("Y");
            $data["BANK_CODE"]      = ($mode == "nop")?$row["PAYMENT_BANK_CODE"]:$row["PAYMENT_BANK_CODE"];
            $data["REFERENCE"]      = ($mode == "nop")?$row["REFERENCE"]:$row["CPM_REFERENCE"];
            $data["JML_NOP"]        = ($mode == "nop")?1:$row["CPM_CG_PAY_NOP"];

            $data["NAMA"]           = ($mode == "nop")?$row["WP_NAMA"]:$row["CPM_CG_COLLECTOR"];
            $data["ALAMAT"]         = ($mode == "nop")?$row["WP_ALAMAT"]:"";
            $data["RT_RW"]          = ($mode == "nop")?$row["WP_RT"]."/".$row["WP_RT"]:"";
            $data["KECAMATAN"]      = ($mode == "nop")?$row["WP_KECAMATAN"]:$row["WP_KECAMATAN"];
            $data["KELURAHAN"]      = ($mode == "nop")?$row["WP_KELURAHAN"]:$row["WP_KELURAHAN"];
            $data["KOTA"]           = ($mode == "nop")?$row["WP_KOTAKAB"]:$row["WP_KOTAKAB"];
            $data["KODE_POS"]       = ($mode == "nop")?$row["WP_KODEPOS"]:$row["WP_KODEPOS"];
            $data["TGL_PEMBAYARAN"] = ($mode == "nop")?$row["PAYMENT_PAID"]:$row["CPM_CG_PAY_DATE"];
            $data["TAGIHAN"]        = ($mode == "nop")?$row["SPPT_PBB_HARUS_DIBAYAR"]:$row["CPM_CG_ORIGINAL_AMOUNT"];
            $data["DENDA"]          = ($mode == "nop")?$row["PBB_DENDA"]:$row["CPM_CG_PENALTY_FEE"];
            $data["TOTAL"]          = ($mode == "nop")?$row["SPPT_PBB_HARUS_DIBAYAR"]+$row["PBB_DENDA"]:$row["CPM_CG_ORIGINAL_AMOUNT"]+$row["CPM_CG_PENALTY_FEE"];
            $data["STATUS"]         = ($mode == "nop")?$row["PAYMENT_FLAG"]:$row["CPM_CG_PAY_FLAG"];

            $data["TAGIHAN"]        = ($data["TAGIHAN"] == Null || $data["TAGIHAN"] == "" )?"0":$data["TAGIHAN"] ;
            $data["DENDA"]          = ($data["DENDA"] == Null || $data["DENDA"] == "" )?"0":$data["DENDA"] ;
            $data["TOTAL"]          = ($data["TOTAL"] == Null || $data["TOTAL"] == "" )?"0":$data["TOTAL"] ;

        }
        return $data;
    }
}

function getGroupID($kode){
    $GWDBLink = openMysql();

        $qry = "SELECT CPM_CG_ID FROM CPPMOD_COLLECTIVE_GROUP where CPM_CG_PAYMENT_CODE = '{$kode}'";

    // echo $qry;exit;
    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($GWDBLink);
    }else{
        while($row = mysqli_fetch_assoc($res)){
           return $row['CPM_CG_ID'];
        }
    }
}


function setPembatalan($mode, $nop, $kode, $tahun, $tgl, $ket,$uname){
    $GWDBLink = openMysql();

    if ($mode == "kode_bayar") {
        // $qry = "UPDATE PBB_SPPT SET PAYMENT_FLAG ='2' where COLL_PAYMENT_CODE = '{$kode}'";
        $group_id = getGroupID($kode);
        $qry = "UPDATE PBB_SPPT A
                INNER JOIN CPPMOD_CG_MEMBER BB ON A.NOP = BB.CPM_CGM_NOP 
                AND A.SPPT_TAHUN_PAJAK = BB.CPM_CGM_TAX_YEAR
                INNER JOIN CPPMOD_COLLECTIVE_GROUP C ON C.CPM_CG_ID = BB.CPM_CGM_ID 
                SET A.PAYMENT_FLAG = 2, C.CPM_CG_STATUS = 1, C.CPM_CG_PAY_FLAG = 2 
                WHERE
                    C.CPM_CG_ID = '{$group_id}'";
    }else {
        $qry = "UPDATE PBB_SPPT SET PAYMENT_FLAG ='2' where NOP = '{$nop}' AND SPPT_TAHUN_PAJAK = '{$tahun}'";
    }

    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($GWDBLink);
    }else{
        $data = getDataPembayaran($mode, $nop, $kode, $tahun);
        $result = insertlog($data,$tgl,$ket,$uname);

        return $result;
    }
}

function insertLog($data,$tgl,$ket,$uname){
    $GWDBLink = openMysql();
    $id = c_uuid();
    $qry = "INSERT INTO PBB_PEMBATALAN_PEMBAYARAN_LOG
    VALUES ('".$id."',
            '".$data['NOP_KODE']."',
            '".$data['TAHUN']."',
            '".$data['NAMA']."',
            '".$data['TAGIHAN']."',
            '".$data['DENDA']."',
            '".($data['TAGIHAN'] + $data['DENDA'])."',
            '".$data['TGL_PEMBAYARAN']."',
            '".$data['BANK_CODE']."',
            '".$data['REFERENCE']."',
            '".$data['JML_NOP']."',
            '{$tgl}',
            '{$ket}',
            '{$uname}'
            )";
    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($GWDBLink);
    }else{
        return $res;
    }
}

//init 
foreach ($_REQUEST as $key => $value) {
    $$key = $value;
}
$response = array();

$dataCheck = getDataPembayaran($mode, $nop, $kode_bayar, $tahun);
// var_dump($dataCheck);exit();
if ($func == "inquiry") {
    $data = getDataPembayaran($mode, $nop, $kode_bayar, $tahun);
    $msg = "00";
}elseif ($func == "execute") {
    if($dataCheck['BANK_CODE'] == null){
        $data = setPembatalan($mode, $nop, $kode_bayar, $tahun, $tgl_pembatalan, $ket,$uname);
        $msg = "00";
    }else{
        $data = "[Pembayaran dari bank tidak bisa di cancel]";
        $msg = "11";
    }
}else{
    $data = "[unknown data mode]";
}

if ($msg == "00") {
    $response["msg"] = "00";
    $response["data"] = $data;
}else if($msg == "11") {
    $response["msg"] = "11";
    $response["data"] = $data;
}else{
    $response["msg"] = "99";
    $response["data"] = null;
}
echo json_encode($response);
