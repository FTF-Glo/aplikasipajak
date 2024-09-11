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

function getDocument($sts) {
    global $DBLink, $json, $app, $tgl1, $tgl2, $tahun;

    $DbName = getConfigValue($app, 'BPHTBDBNAME');
    $DbHost = getConfigValue($app, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($app, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($app, 'BPHTBTABLE');
    $DbUser = getConfigValue($app, 'BPHTBUSERNAME');
    $DbNameSW = getConfigValue($app,'BPHTBDBNAMESW');

    $iErrCode = 0;
    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    // GET Kelurahan
    $query = "SELECT
                    kel.CPC_TKL_ID AS kel_code,
                    kel.CPC_TKL_KELURAHAN AS KELURAHAN,
                    kec.CPC_TKC_ID AS kec_code,
                    kec.CPC_TKC_KECAMATAN AS KECAMATAN
                FROM sw_pbb.cppmod_tax_kelurahan kel
                INNER JOIN sw_pbb.cppmod_tax_kecamatan kec ON kel.CPC_TKL_KCID=kec.CPC_TKC_ID";
    $res = mysqli_query($LDBLink, $query);

    $row_kec = [];
    $nkel = 0;
    while($r=mysqli_fetch_assoc($res)){
        $nkel++;
        $kec_code = $r['kec_code'];
        unset($r['kec_code']);
        $arr = json_encode($r);
        $arr = json_decode($arr, true);
        $row_kec[$kec_code][] = $arr;
    }
    // print_r($row_kec);exit;
    // ======================================================================================

    // // Ketetapan Pokok
    $query = "SELECT
                    LEFT(g.op_nomor,10) AS kel_code,
                    COUNT(*) AS pokok,
                    SUM(g.bphtb_dibayar) AS dibayar
                FROM gw_ssb.ssb g
                INNER JOIN sw_ssb.cppmod_ssb_doc d ON d.CPM_SSB_ID=g.id_switching
                WHERE 
                    TIMESTAMP(g.payment_paid) >= '$tgl1' AND 
                    TIMESTAMP(g.payment_paid) <= '$tgl2' AND 
                    d.CPM_OP_THN_PEROLEH = '$tahun'
                GROUP BY LEFT(g.op_nomor,10)";
    $res = mysqli_query($LDBLink, $query);

    $row_pokok = [];
    while($r=mysqli_fetch_assoc($res)){
        $kel_code = $r['kel_code'];
        unset($r['kel_code']);
        $arr = [];
        $arr['BPHTB'] = $r['pokok'];
        $arr['KETETAPAN'] = $r['dibayar'];
        $row_pokok[$kel_code] = $arr;
    }
    
    foreach ($row_kec as $k=>$kel) {
        foreach ($kel as $n=>$kl) {
            $row_kec[$k][$n]['BPHTB'] = (int)$row_pokok[$kl['kel_code']]['BPHTB'];
            $row_kec[$k][$n]['KETETAPAN'] = (float)$row_pokok[$kl['kel_code']]['KETETAPAN'];
        }
    }
    // print_r($row_kec);exit;
    // ======================================================================================

    // Realisasi Bulan ini
    $tglawal = date('Y-m') . '-01';
    $tglakhir = date('Y-m-t');
    $query = "SELECT
                    LEFT(g.op_nomor,10) AS kel_code,
                    COUNT(*) AS pokok,
                    SUM(g.bphtb_dibayar) AS dibayar
                FROM gw_ssb.ssb g
                INNER JOIN sw_ssb.cppmod_ssb_doc d ON d.CPM_SSB_ID=g.id_switching
                WHERE 
                    g.payment_flag = '1' AND 
                    TIMESTAMP(g.payment_paid) >= '$tglawal' AND 
                    TIMESTAMP(g.payment_paid) <= '$tglakhir' AND 
                    d.CPM_OP_THN_PEROLEH = '$tahun'
                GROUP BY LEFT(g.op_nomor,10)";
    $res = mysqli_query($LDBLink, $query);

    $dataini = $d->data;
    $row_ini = [];
    while($r=mysqli_fetch_assoc($res)){
        $kel_code = $r['kel_code'];
        unset($r['kel_code']);
        $arr = [];
        $arr['BPHTB_INI'] = $r['pokok'];
        $arr['DIBAYAR_INI'] = $r['dibayar'];
        $row_ini[$kel_code] = $arr;
    }
    
    foreach ($row_kec as $k=>$kel) {
        foreach ($kel as $n=>$kl) {
            $row_kec[$k][$n]['BPHTB_INI'] = (int)$row_ini[$kl['kel_code']]['BPHTB_INI'];
            $row_kec[$k][$n]['DIBAYAR_INI'] = (float)$row_ini[$kl['kel_code']]['DIBAYAR_INI'];
        }
    }
    // print_r($row_kec);exit;
    // ========================================================

    // Realisasi Bulan LALU
    $tglawal = date('Y-m') . '-15 00:00:00';
    $tglawal = date('Y-m-d H:i:s', strtotime($tglawal . ' -30 day'));
    $tglakhir= date_create($tglawal);
    $tglawal = substr($tglawal,0,7);
    
    $tglawal = $tglawal . '-01';
    $tglakhir = date_format($tglakhir,'Y-m-t');

    $query = "SELECT
                    LEFT(g.op_nomor,10) AS kel_code,
                    COUNT(*) AS pokok,
                    SUM(g.bphtb_dibayar) AS dibayar
                FROM gw_ssb.ssb g
                INNER JOIN sw_ssb.cppmod_ssb_doc d ON d.CPM_SSB_ID=g.id_switching
                WHERE 
                    g.payment_flag = '1' AND 
                    TIMESTAMP(g.payment_paid) >= '$tglawal' AND 
                    TIMESTAMP(g.payment_paid) <= '$tglakhir' AND 
                    d.CPM_OP_THN_PEROLEH = '$tahun'
                GROUP BY LEFT(g.op_nomor,10)";
    $res = mysqli_query($LDBLink, $query);

    $datalalu = $d->data;
    $row_lalu = [];
    while($r=mysqli_fetch_assoc($res)){
        $kel_code = $r['kel_code'];
        unset($r['kel_code']);
        $arr = [];
        $arr['BPHTB_LALU'] = $r['pokok'];
        $arr['DIBAYAR_LALU'] = $r['dibayar'];
        $row_lalu[$kel_code] = $arr;
    }
    
    foreach ($row_kec as $k=>$kel) {
        foreach ($kel as $n=>$kl) {
            $row_kec[$k][$n]['BPHTB_LALU'] = (int)$row_lalu[$kl['kel_code']]['BPHTB_LALU'];
            $row_kec[$k][$n]['DIBAYAR_LALU'] = (float)$row_lalu[$kl['kel_code']]['DIBAYAR_LALU'];
        }
    }
    // print_r(json_encode($row_kec));exit;
    // ========================================================

    // Realisasi Range Tanggal
    $query = "SELECT
                    LEFT(g.op_nomor,10) AS kel_code,
                    COUNT(*) AS pokok,
                    SUM(g.bphtb_dibayar) AS dibayar
                FROM gw_ssb.ssb g
                INNER JOIN sw_ssb.cppmod_ssb_doc d ON d.CPM_SSB_ID=g.id_switching
                WHERE 
                    g.payment_flag = '1' AND 
                    TIMESTAMP(g.payment_paid) >= '$tgl1' AND 
                    TIMESTAMP(g.payment_paid) <= '$tgl2' AND 
                    d.CPM_OP_THN_PEROLEH = '$tahun'
                GROUP BY LEFT(g.op_nomor,10)";
    $res = mysqli_query($LDBLink, $query);

    $datareal = $d->data;
    $row_real = [];
    while($r=mysqli_fetch_assoc($res)){
        $kel_code = $r['kel_code'];
        unset($r['kel_code']);
        $arr = [];
        $arr['BPHTB_REAL'] = $r['pokok'];
        $arr['DIBAYAR_REAL'] = $r['dibayar'];
        $row_real[$kel_code] = $arr;
    }
    
    foreach ($row_kec as $k=>$kel) {
        foreach ($kel as $n=>$kl) {
            $row_kec[$k][$n]['BPHTB_REAL'] = (int)$row_real[$kl['kel_code']]['BPHTB_REAL'];
            $row_kec[$k][$n]['DIBAYAR_REAL'] = (float)$row_real[$kl['kel_code']]['DIBAYAR_REAL'];
        }
    }
    return $row_kec;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);
$sts = $q->sts;
$app = $q->app;
$tgl1 = $q->tgl1;
$tgl2 = $q->tgl2;

$tgl1 = empty($tgl1)?date('Y-m-d'):$tgl1;
$tgl2 = empty($tgl2)?date('Y-m-d'):$tgl2;
$tahun = $q->tahun;

$tgl1 = ($tgl1=='') ? date('Y').'-01-01' : $tgl1;
$tgl2 = ($tgl2=='') ? date('Y-m-d') : $tgl2;
$tahun = ($tahun=='') ? date('Y') : $tahun;

$tgl1 = $tgl1 . " 00:00:00";
$tgl2 = $tgl2 . " 23:59:59";


function createStrDate($sd) {
    if ($sd != '') {
        $date = explode("/", $sd);
        $dt = $date[2] . $date[1] . $date[0];
        return $dt;
    } else {
        return $sd;
    }
}


function HeaderingExcel($filename) {
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=$filename");
    header("Expires:0");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");
}

HeaderingExcel('bphtb-realisasi-'.date('ymdHis').'.xls');

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
$worksheet1->write_string(0, 0, "Monitoring BPHTB ", $fBesar);
$worksheet1->write_string(1, 0, "Realisasi Pembayaran", $fBesar);

$tgl1 = substr($tgl1,0,10);
$tgl2 = substr($tgl2,0,10);

$filter = "";
if ($tgl1 != "" && $tgl2 != "")
    $filter .="Tanggal  $tgl1 s.d $tgl2";
elseif ($tgl1 != "")
    $filter .= "Tanggal $tgl1";
elseif ($tgl2 != "")
    $filter .= "Tanggal $tgl2";

$worksheet1->write_string(2, 0, $filter, $fBesar);

$worksheet1->set_column(0, 0, 10);

// sesuaikan dengan judul kolom pada table anda

$worksheet1->write_string(3, 0, "NO", $fDtlHead);
$worksheet1->write_string(3, 1, "KECAMATAN", $fDtlHead);
$worksheet1->write_string(3, 2, "DESA", $fDtlHead);
$worksheet1->write_string(3, 3, "KETETAPAN", $fDtlHead);
$worksheet1->write_string(3, 5, "REALISASI BULAN LALU", $fDtlHead);
$worksheet1->write_string(3, 7, "REALISASI BULAN INI", $fDtlHead);
$worksheet1->write_string(3, 9, "REALISASI TGL ". $tgl1 . " - " . $tgl2, $fDtlHead);

$worksheet1->write_string(4, 3, "BPHTB", $fDtlHead);
$worksheet1->write_string(4, 4, "TAGIHAN", $fDtlHead);
$worksheet1->write_string(4, 5, "BPHTB", $fDtlHead);
$worksheet1->write_string(4, 6, "DIBAYAR", $fDtlHead);
$worksheet1->write_string(4, 7, "BPHTB", $fDtlHead);
$worksheet1->write_string(4, 8, "DIBAYAR", $fDtlHead);
$worksheet1->write_string(4, 9, "BPHTB", $fDtlHead);
$worksheet1->write_string(4, 10,"DIBAYAR", $fDtlHead);


$worksheet1->merge_cells(0, 0, 0, 10);
$worksheet1->merge_cells(1, 0, 1, 10);
$worksheet1->merge_cells(2, 0, 2, 10);


$data = getDocument($sts);

$i = 1;
$nama_kec = 'XXX';
$totalallBPHTB = 0;
$totalallKetetapan = 0;
$totalallBPHTBblnlalu = 0;
$totalallblnlalu = 0;
$totalallBPHTBblnini = 0;
$totalallblnini = 0;
$totalallBPHTBblnreal = 0;
$totalallblnreal = 0;

$totalBPHTB = 0;
$totalKetetapan = 0;
$totalBPHTBblnlalu = 0;
$totalblnlalu = 0;
$totalBPHTBblnini = 0;
$totalblnini = 0;
$totalBPHTBblnreal = 0;
$totalblnreal = 0;

$baris = 5;
foreach ($data as $kec) {

    foreach ($kec as $r) {

        if(($nama_kec != $r['KECAMATAN']) && $i>1){
            $i = 1;
            $worksheet1->write_string($baris, 0, 'TOTAL', $fDtlNumber);
            $worksheet1->merge_cells($baris, 0, $baris, 2);
            $worksheet1->write_number($baris, 3, $totalBPHTB, $fDtlNumber);
            $worksheet1->write_number($baris, 4, $totalKetetapan, $fDtlNumber);
            $worksheet1->write_number($baris, 5, $totalBPHTBblnlalu, $fDtlNumber);
            $worksheet1->write_number($baris, 6, $totalblnlalu, $fDtlNumber);
            $worksheet1->write_number($baris, 7, $totalBPHTBblnini, $fDtlNumber);
            $worksheet1->write_number($baris, 8, $totalblnini, $fDtlNumber);
            $worksheet1->write_number($baris, 9, $totalBPHTBblnreal, $fDtlNumber);
            $worksheet1->write_number($baris, 10,$totalblnreal, $fDtlNumber);
            $baris++;

            $totalBPHTB = 0;
            $totalKetetapan = 0;
            $totalBPHTBblnlalu = 0;
            $totalblnlalu = 0;
            $totalBPHTBblnini = 0;
            $totalblnini = 0;
            $totalBPHTBblnreal = 0;
            $totalblnreal = 0;

            $totalBPHTB += $r['BPHTB'];
            $totalKetetapan += $r['KETETAPAN'];
            $totalBPHTBblnlalu += $r['BPHTB_LALU'];
            $totalblnlalu += $r['DIBAYAR_LALU'];
            $totalBPHTBblnini += $r['BPHTB_INI'];
            $totalblnini += $r['DIBAYAR_INI'];
            $totalBPHTBblnreal += $r['BPHTB_REAL'];
            $totalblnreal += $r['DIBAYAR_REAL'];

        }else{
            $totalBPHTB += $r['BPHTB'];
            $totalKetetapan += $r['KETETAPAN'];
            $totalBPHTBblnlalu += $r['BPHTB_LALU'];
            $totalblnlalu += $r['DIBAYAR_LALU'];
            $totalBPHTBblnini += $r['BPHTB_INI'];
            $totalblnini += $r['DIBAYAR_INI'];
            $totalBPHTBblnreal += $r['BPHTB_REAL'];
            $totalblnreal += $r['DIBAYAR_REAL'];
        }

        $worksheet1->write_number($baris, 0, $i, $fDtlCenter);
        $worksheet1->write_string($baris, 1, ($nama_kec == $r['KECAMATAN']) ? '':$r['KECAMATAN'], $fBiasa);
        $worksheet1->write_string($baris, 2, $r['KELURAHAN'], $fBiasa);
        $worksheet1->write_number($baris, 3, $r['BPHTB'], $fDtlNumber);
        $worksheet1->write_number($baris, 4, $r['KETETAPAN'], $fDtlNumber);
        $worksheet1->write_number($baris, 5, $r['BPHTB_LALU'], $fDtlNumber);
        $worksheet1->write_number($baris, 6, $r['DIBAYAR_LALU'], $fDtlNumber);
        $worksheet1->write_number($baris, 7, $r['BPHTB_INI'], $fDtlNumber);
        $worksheet1->write_number($baris, 8, $r['DIBAYAR_INI'], $fDtlNumber);
        $worksheet1->write_number($baris, 9, $r['BPHTB_REAL'], $fDtlNumber);
        $worksheet1->write_number($baris, 10,$r['DIBAYAR_REAL'], $fDtlNumber);

        $nama_kec = $r['KECAMATAN'];
        $i++;
        $baris++;
        
        $totalallBPHTB += $r['BPHTB'];
        $totalallKetetapan += $r['KETETAPAN'];
        $totalallBPHTBblnlalu += $r['BPHTB_LALU'];
        $totalallblnlalu += $r['DIBAYAR_LALU'];
        $totalallBPHTBblnini += $r['BPHTB_INI'];
        $totalallblnini += $r['DIBAYAR_INI'];
        $totalallBPHTBblnreal += $r['BPHTB_REAL'];
        $totalallblnreal += $r['DIBAYAR_REAL'];

    }

}

$worksheet1->write_string($baris, 0, 'TOTAL', $fDtlNumber);
$worksheet1->merge_cells($baris, 0, $baris, 2);
$worksheet1->write_number($baris, 3, $totalBPHTB, $fDtlNumber);
$worksheet1->write_number($baris, 4, $totalKetetapan, $fDtlNumber);
$worksheet1->write_number($baris, 5, $totalBPHTBblnlalu, $fDtlNumber);
$worksheet1->write_number($baris, 6, $totalblnlalu, $fDtlNumber);
$worksheet1->write_number($baris, 7, $totalBPHTBblnini, $fDtlNumber);
$worksheet1->write_number($baris, 8, $totalblnini, $fDtlNumber);
$worksheet1->write_number($baris, 9, $totalBPHTBblnreal, $fDtlNumber);
$worksheet1->write_number($baris, 10,$totalblnreal, $fDtlNumber);
$baris++;
$baris++;

$worksheet1->write_string($baris, 0, 'TOTAL KESELURUHAN', $fDtlNumber);
$worksheet1->merge_cells($baris, 0, $baris, 2);
$worksheet1->write_number($baris, 3, $totalallBPHTB, $fDtlNumber);
$worksheet1->write_number($baris, 4, $totalallKetetapan, $fDtlNumber);
$worksheet1->write_number($baris, 5, $totalallBPHTBblnlalu, $fDtlNumber);
$worksheet1->write_number($baris, 6, $totalallblnlalu, $fDtlNumber);
$worksheet1->write_number($baris, 7, $totalallBPHTBblnini, $fDtlNumber);
$worksheet1->write_number($baris, 8, $totalallblnini, $fDtlNumber);
$worksheet1->write_number($baris, 9, $totalallBPHTBblnreal, $fDtlNumber);
$worksheet1->write_number($baris, 10,$totalallblnreal, $fDtlNumber);


$workbook->close();
?>
