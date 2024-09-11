<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/user-central.php");

require_once($sRootPath . "inc/phptoexcel/OLEwriter.php");
require_once($sRootPath . "inc/phptoexcel/BIFFwriter.php");
require_once($sRootPath . "inc/phptoexcel/Worksheet.php");
require_once($sRootPath . "inc/phptoexcel/Workbook.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$a="aBPHTB";
$DbName = getConfigValue($a, 'BPHTBDBNAME');
$DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
$DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
$DbTable = getConfigValue($a, 'BPHTBTABLE');
$DbUser = getConfigValue($a, 'BPHTBUSERNAME');

$iErrCode = 0;
SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
function mysql2json($mysql_result, $name) {
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getConfigValue($id, $key) {
    global $DBLink;
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);

$sts = $q->sts;
$app = $q->app;
$src = $q->src;
$find_notaris = $q->find_notaris;
$tgl1 = $q->tgl1;
$tgl2 = $q->tgl2;

function HeaderingExcel($filename) {
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=$filename");
    header("Expires:0");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");
}
function get_kecamatan(){
    global $app,$LDBLink,$DbTable;

    $query_get_data_backend = "select op_kecamatan from $DbTable where payment_flag = 1 group by op_kecamatan";
    $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    $return = array();
    $i = 0;
    while ($dtBE = mysqli_fetch_array($resBE)) {
        $return[$i]['kecamatan'] = $dtBE['op_kecamatan'];
        $i++;
    }
    return $return;
}
// var_dump($DbTable);exit;
function get_kelurahan($kecamatan){
    global $app,$LDBLink,$DbTable;
    $query_get_data_backend = "select op_kelurahan from $DbTable where op_kecamatan ='{$kecamatan}' and payment_flag = 1 group by op_kelurahan";

    $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    $return = array();
    $i=0;
    while ($dtBE = mysqli_fetch_array($resBE)) {
        $return[$i]['kelurahan'] = $dtBE['op_kelurahan'];
        $i++;
    }
    return $return;
}

function get_triwulan($kelurahan,$kecamatan,$no){
    global $app,$LDBLink,$DbTable;
    $triwulan =['BETWEEN 1 AND 3','BETWEEN 4 AND 6','BETWEEN 7 AND 9','BETWEEN 10 AND 12'];
    // var_dump($triwulan);
    $query_get_data_backend = "SELECT SUM(bphtb_dibayar) total_bayar FROM ssb WHERE op_kecamatan='{$kelurahan}' AND op_kelurahan = '$kecamatan' AND YEAR(payment_paid) = 2021 AND MONTH(payment_paid) ".$triwulan[$no];
    $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    $return = array();
    // $i=0;
    while ($dtBE = mysqli_fetch_array($resBE)) {
        $return['total_bayar'] = ($dtBE['total_bayar'] != '') ? $dtBE['total_bayar'] : 0;
        // $i++;
    }
    // die(var_dump($return));
    return $return;
}

function get_code_payment($id){
    global $app;
    $a=$app;
    $DbName = getConfigValue($a, 'BPHTBDBNAME');
    $DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($a, 'BPHTBTABLE');
    $DbUser = getConfigValue($a, 'BPHTBUSERNAME');

    $iErrCode = 0;
    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    $query_get_data_backend = "select id_switching,payment_code,payment_paid from $DbTable where id_switching ='{$id}'";
    $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    $return = array();
    while ($dtBE = mysqli_fetch_array($resBE)) {
        $return['payment_code'] = $dtBE['payment_code'];
        $return['payment_paid'] = $dtBE['payment_paid'];
    }
    return $return;
}
HeaderingExcel('bphtb-report.xls');

//membuat area kerja
$workbook = new Workbook("-");
//class untuk mencetak tulisan besar dan tebal
$fBesar = & $workbook->add_format();
$fBesar->set_size(14);
$fBesar->set_align("center");
$fBesar->set_bold();

$fBiasa = & $workbook->add_format();
$fBiasa->set_align("left");
//class untuk mencetak tulisan tanpa border (untuk judul laporan)
$fList = & $workbook->add_format();
$fList->set_border(0);
//class untuk mencetak tulisan dengan border dan ditengah kolom (untuk judul kolom)
$fDtlHead = & $workbook->add_format();
$fDtlHead->set_border(1);
$fDtlHead->set_align("center");
$fDtlHead->set_align("vcentre");
$fDtlHead->set_text_wrap(1);

$fDtlCenter = & $workbook->add_format();
$fDtlCenter->set_border(1);
$fDtlCenter->set_align("center");
$fDtlCenter->set_align("vcentre");
$fDtlCenter->set_text_wrap(1);

//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai string)
$fDtl = & $workbook->add_format();
$fDtl->set_border(1);
//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai numerik)
$fDtlNumber = & $workbook->add_format();
$fDtlNumber->set_border(1);
$fDtlNumber->set_align("right");
$fDtlNumber->set_num_format(3);
//class untuk men-zoom laporan 75%
$worksheet1 = & $workbook->add_worksheet("Halaman 1");
$worksheet1->set_zoom(100);

//$header = $p->header;
$worksheet1->write_string(0, 0, "BPHTB DBH", $fBesar);
$arrJudul = "RINCIAN PERHITUNGAN DBH BPHTB JANUARI S/D DESEMBER 2021";
$worksheet1->write_string(1, 0, $Judul, $fBesar);

$worksheet1->set_row(3, 30);
$worksheet1->set_column(0, 0, 2.5);
// $worksheet1->set_column(0, 1, 20);
$worksheet1->set_column(0, 2, 20);
$worksheet1->set_column(0, 3, 20);
$worksheet1->set_column(0, 4, 20);
$worksheet1->set_column(0, 5, 20);
$worksheet1->set_column(0, 6, 20);
//sesuaikan dengan judul kolom pada table anda
$worksheet1->write_string(3, 0, "No.", $fDtlHead);
if ($sts == 8) {#berdasarkan user
    $worksheet1->write_string(3, 1, "KECAMATAN", $fDtlHead);
    $worksheet1->write_string(3, 2, "DESA", $fDtlHead);
    $worksheet1->write_string(3, 3, "TRIWULAN 1", $fDtlHead);
    $worksheet1->write_string(3, 4, "TRIWULAN 2", $fDtlHead);
    $worksheet1->write_string(3, 5, "TRIWULAN 3", $fDtlHead);
    $worksheet1->write_string(3, 6, "TRIWULAN 4", $fDtlHead);
}
$worksheet1->merge_cells(0, 0, 0, 7);

$baris = 4;
if ($sts ==8) {
    $dat = get_kecamatan();
    $hal = 0;
    $totalbayar = 0;
    
    foreach ($dat as $row) {
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);

        $worksheet1->write_string($baris, 1, $row['kecamatan'], $fBiasa);
        $kelurahan = get_kelurahan($row['kecamatan']);
        $kelurahanshow = '';
        foreach ($kelurahan as $key) {
            $kelurahanshow = $key['kelurahan'];
            $triwulan1 = get_triwulan($row['kecamatan'],$key['kelurahan'],0);
            $triwulan2 = get_triwulan($row['kecamatan'],$key['kelurahan'],1);
            $triwulan3 = get_triwulan($row['kecamatan'],$key['kelurahan'],2);
            $triwulan4 = get_triwulan($row['kecamatan'],$key['kelurahan'],3);
            $worksheet1->write_string($baris, 2, $kelurahanshow, $fBiasa);
            $worksheet1->write_string($baris, 3, $triwulan1['total_bayar'], $fDtlNumber);
            $worksheet1->write_string($baris, 4, $triwulan2['total_bayar'], $fDtlNumber);
            $worksheet1->write_string($baris, 5, $triwulan3['total_bayar'], $fDtlNumber);
            $worksheet1->write_string($baris, 6, $triwulan4['total_bayar'], $fDtlNumber);
            $baris++;
        }
        $baris++;
    }
}


$workbook->close();
?>
