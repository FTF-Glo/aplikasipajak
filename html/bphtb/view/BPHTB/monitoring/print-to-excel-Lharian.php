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

function getDocument($sts, &$dat) {
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2, $jh;
    $srcTxt = $src;


    $DbName = getConfigValue($app, 'BPHTBDBNAME');
    $DbHost = getConfigValue($app, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($app, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($app, 'BPHTBTABLE');
    $DbUser = getConfigValue($app, 'BPHTBUSERNAME');
    $DbNameSW = getConfigValue($app,'BPHTBDBNAMESW');

    $srcTgl1 = empty($tgl1)?date('Y-m-d'):$tgl1;
    $srcTgl2 = empty($tgl2)?date('Y-m-d'):$tgl2;
    $where = "";
    $where2 = "";
    if ($srcTgl1 != "") $where .= " AND $DbName.ssb.payment_paid >= '".$srcTgl1." 00:00:00' ";
    if ($srcTgl2 != "") $where .= " AND $DbName.ssb.payment_paid <= '".$srcTgl2." 23:59:59'";
    if ($jh != "") $where2 .= " AND $DbNameSW.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '".$jh."'";
    $iErrCode=0;

    $iErrCode = 0;
    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    $query = "SELECT * FROM $DbName.ssb
                INNER JOIN
                $DbNameSW.cppmod_ssb_doc
                ON
                $DbNameSW.cppmod_ssb_doc.CPM_SSB_ID = $DbName.ssb.id_switching
                INNER JOIN
                $DbNameSW.cppmod_ssb_jenis_hak
                ON
                $DbNameSW.cppmod_ssb_doc.CPM_OP_JENIS_HAK = $DbNameSW.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK 
                $where
                $where2
                ORDER BY $DbName.ssb.payment_paid DESC";
    $res = mysqli_query($LDBLink, $query);
    $i=0;
    while($row=mysqli_fetch_assoc($res)){
        $dataRow[$i]['wp_nama'] = $row['wp_nama'];
        $dataRow[$i]['wp_alamat'] =  $row['wp_alamat'];
        $dataRow[$i]['op_nomor'] = $row['op_nomor'];
        $dataRow[$i]['CPM_OP_NMR_SERTIFIKAT'] = $row['CPM_OP_NMR_SERTIFIKAT'];
        $dataRow[$i]['payment_code'] = $row['payment_code'];
        $dataRow[$i]['bphtb_dibayar'] = $row['bphtb_dibayar'];
        $dataRow[$i]['cpm_denda'] = $row['cpm_denda'];
        $dataRow[$i]['author'] = $row['author'];
        $dataRow[$i]['CPM_JENIS_HAK'] = $row['CPM_JENIS_HAK'];
       $i++; 
    }
    $dat = $dataRow;
    return $dat;
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

$tgl1 = empty($tgl1)?date('Y-m-d'):$tgl1;
$tgl2 = empty($tgl2)?date('Y-m-d'):$tgl2;
$wp_nama = $q->wp_nama;
$jh = $q->jh;

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
$worksheet1->write_string(0, 0, "Monitoring BPHTB ", $fBesar);
$arrJudul = [1 => "Rekapitulasi Pembayaran Berdasarkan User", 
    2 => "Rekapitulasi Pembayaran Per User",
    3 => "Rekapitulasi Approval Berdasarkan User",
    4 => "Rekapitulasi Approval Per User", 
    5 => "Rekapitulasi Persetujuan Siap Bayar",
    8 => "Rekapitulasi Sudah Bayar",
    12 => "Laporan Harian"];
$worksheet1->write_string(1, 0, $arrJudul[$sts], $fBesar);

$filter = "";
if ($sts == 12) {
    if ($tgl1 != "" && $tgl2 != "")
        $filter .="Tanggal  $tgl1 s.d $tgl2";
    elseif ($tgl1 != "")
        $filter .= "Tanggal $tgl1";
    elseif ($tgl2 != "")
        $filter .= "Tanggal $tgl2";
}
$worksheet1->write_string(2, 0, $filter, $fBesar);

$worksheet1->set_row(3, 30);
$worksheet1->set_column(0, 0, 10);
//sesuaikan dengan judul kolom pada table anda
$worksheet1->write_string(3, 0, "No.", $fDtlHead);
if ($sts == 12) {#berdasarkan user
    $worksheet1->write_string(3, 1, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Alamat", $fDtlHead);
    $worksheet1->write_string(3, 3, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 4, "No. Sertifikat", $fDtlHead);
    $worksheet1->write_string(3, 5, "Kode Bayar", $fDtlHead);
    $worksheet1->write_string(3, 6, "Pembayaran", $fDtlHead);
    $worksheet1->write_string(3, 7, "Denda", $fDtlHead);
    $worksheet1->write_string(3, 8, "User", $fDtlHead);
    $worksheet1->write_string(3, 9, "Ket", $fDtlHead);
}

$worksheet1->merge_cells(0, 0, 0, 5);
$worksheet1->merge_cells(1, 0, 1, 5);
$worksheet1->merge_cells(2, 0, 2, 5);

$baris = 4;
if ($sts ==12) {
    getDocument($sts, $dat);
    $hal = 0;
    $totalbayar = 0;
    $totaldenda = 0;
    foreach ($dat as $row) {
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);
        
        $worksheet1->write_string($baris, 1, $row['wp_nama'], $fBiasa);
        $worksheet1->write_string($baris, 2, $row['wp_alamat'], $fBiasa);
        $worksheet1->write_string($baris, 3, $row['op_nomor'], $fBiasa);
        $worksheet1->write_string($baris, 4, $row['CPM_OP_NMR_SERTIFIKAT'], $fBiasa);
        $worksheet1->write_string($baris, 5, $row['payment_code'], $fBiasa);
        $worksheet1->write_string($baris, 6, $row['bphtb_dibayar'], $fDtlNumber);
        $worksheet1->write_string($baris, 7, $row['cpm_denda'], $fDtlNumber);
        $worksheet1->write_string($baris, 8, $row['author'], $fBiasa);
        $worksheet1->write_string($baris, 9, $row['CPM_JENIS_HAK'], $fBiasa);
        $totalbayar += $row['bphtb_dibayar'];
        $totaldenda += $row['cpm_denda'];
        $baris++;
    }
    $baris++;

    $judul_berkas = "Total Berkas";
    $worksheet1->write_string($baris, 0, $judul_berkas, $fDtlNumber);
    $worksheet1->write_number($baris, 6, $hal, $fDtlNumber);
    $worksheet1->merge_cells($baris, 0, $baris, 5);

    $baris++;
    $baris++;
    $judul_jumlah = "Total";
    $worksheet1->write_string($baris, 0, $judul_jumlah, $fDtlNumber);
    $worksheet1->write_number($baris, 6, $totalbayar, $fDtlNumber);
    $worksheet1->write_number($baris, 7, $totaldenda, $fDtlNumber);
    $worksheet1->merge_cells($baris, 0, $baris, 5);

}


$workbook->close();
?>
