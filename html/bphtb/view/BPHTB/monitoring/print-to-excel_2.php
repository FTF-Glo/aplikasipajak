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

function getDocumentPembayaran($sts, &$dat) {
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2;
    $srcTxt = $src;
    $where = "";

    $a = $app;
    $DbName = getConfigValue($a, 'BPHTBDBNAME');
    $DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($a, 'BPHTBTABLE');
    $DbUser = getConfigValue($a, 'BPHTBUSERNAME');
    $tw = getConfigValue($a, 'TENGGAT_WAKTU');

    $where = " WHERE PAYMENT_FLAG = 1"; #pembayaran

    if ($srcTxt != "")
        $where .= " AND wp_nama LIKE '" . mysqli_escape_string($DBLink, $srcTxt) . "%'";
    if ($find_notaris != "")
        $where .= " AND author like '%" . mysqli_escape_string($DBLink, $find_notaris) . "%'";

    if ($tgl1 != "" && $tgl2 != "")
        $where .= " AND  (payment_paid between '" . mysqli_escape_string($DBLink, $tgl1) . "' and '" . mysqli_escape_string($DBLink, $tgl2) . " 23:59:59')";
    elseif ($tgl1 != "")
        $where .= " AND  (payment_paid = '" . mysqli_escape_string($DBLink, $tgl1) . "')";
    elseif ($tgl2 != "")
        $where .= " AND  (payment_paid = '" . mysqli_escape_string($DBLink, $tgl2) . "')";


    $iErrCode = 0;

    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    if ($sts == 1) {#berdasarkan user
        $query = "SELECT *,count(id_ssb) as jml_transaksi, sum(bphtb_dibayar) as jml_nilai_transaksi
                 FROM $DbTable $where GROUP BY bphtb_notaris 
                 ORDER BY saved_Date DESC ";
        $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where} GROUP BY bphtb_notaris";
    } else {#per user
        $query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC ";
        $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where}";
    }
	//echo $query;
    $res = mysqli_query($LDBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($LDBLink));
        return false;
    }

    // $d = $json->decode(mysql2json($res, "data"));
    // $HTML = "";
    // $data = $d;

		
	$i=0;
    while($row=mysqli_fetch_assoc($res)){
        
        if ($sts == 1) {
            $dataRow[$i]['OP_NOMOR'] = $row["op_nomor"];
            $dataRow[$i]['NOTARIS'] = $row['bphtb_notaris'];
            $dataRow[$i]['JML_TRANSAKSI'] = $row['jml_transaksi'];
            $dataRow[$i]['JML_NILAI_TRANSAKSI'] = $row['jml_nilai_transaksi'];
        } else {
            $dataRow[$i]['ID_SSB'] = $row['id_ssb'];
            $dataRow[$i]['OP_NOMOR'] = $row['op_nomor'];
            $dataRow[$i]['WP_NAMA'] = $row['wp_nama'];
            $dataRow[$i]['PAYMENT_PAID'] = $row['payment_paid'];
            $dataRow[$i]['BPHTB_DIBAYAR'] = $row['bphtb_dibayar'];
			$dataRow[$i]['USER_NOTARIS'] = $row['author'];
           
        }
       $i++; 
    }
    
    $dat = $dataRow;
    return $dat;
}

function getDocumentApproval($sts, &$dat) {
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2;
    $srcTxt = $src;

    $where = " WHERE b.CPM_TRAN_STATUS = '5'"; #disetujui

    if ($find_notaris != "")
        $where .= " AND (b.CPM_TRAN_OPR_NOTARIS like '%" . mysqli_real_escape_string($LDBLink, $find_notaris) . "%')";

    if ($tgl1 != "" && $tgl2 != "")
        $where .= " AND  (b.CPM_TRAN_DATE between '" . mysqli_escape_string($LDBLink, $tgl1) . "' and '" . mysqli_escape_string($LDBLink, $tgl2) . " 23:59:59')";
    elseif ($tgl1 != "")
        $where .= " AND  (b.CPM_TRAN_DATE = '" . mysqli_real_escape_string($LDBLink, $tgl1) . "')";
    elseif ($tgl2 != "")
        $where .= " AND  (b.CPM_TRAN_DATE = '" . mysqli_real_escape_string($LDBLink, $tgl2) . "')";

    if ($sts == 3) {#berdasarkan user
        $query = "SELECT *,count(a.CPM_SSB_ID) as jml_transaksi, sum(a.CPM_OP_BPHTB_TU) as jml_nilai_transaksi
                 FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
                 $where GROUP BY b.CPM_TRAN_OPR_NOTARIS 
                 ORDER BY b.CPM_TRAN_DATE DESC";
    } else if ($sts == 4) {#per user
        $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where
                 ORDER BY b.CPM_TRAN_DATE DESC";
    } else {
        $DbName = getConfigValue($app, 'BPHTBDBNAME');
        $DbHost = getConfigValue($app, 'BPHTBHOSTPORT');
        $DbPwd = getConfigValue($app, 'BPHTBPASSWORD');
        $DbTable = getConfigValue($app, 'BPHTBTABLE');
        $DbUser = getConfigValue($app, 'BPHTBUSERNAME');

        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }

        $query_get_data_backend = "select id_switching from $DbTable where payment_flag=0";
        $resBE = mysqli_query($LDBLink, $query_get_data_backend);
        $whereIn = array();
        while ($dtBE = mysqli_fetch_array($resBE)) {
            $whereIn[] = $dtBE['id_switching'];
        }
        $whereIn = "('" . implode("','", $whereIn) . "')";

        $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
                 ORDER BY b.CPM_TRAN_DATE DESC";
    }
	//echo $query;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($DBLink));
        return false;
    }

//    $d = $json->decode(mysql2json($res, "data"));
//    $HTML = "";
//    $data = $d;
//    
//
//    for ($i = 0; $i < count($data->data); $i++) {
//        if ($sts == 3) {
//            $dataRow[$i]['CPM_OP_NOMOR'] = $data->data[$i]->CPM_OP_NOMOR;
//            $dataRow[$i]['CPM_TRAN_OPR_NOTARIS'] = $data->data[$i]->CPM_TRAN_OPR_NOTARIS;
//            $dataRow[$i]['JML_TRANSAKSI'] = $data->data[$i]->jml_transaksi;
//            $dataRow[$i]['JML_NILAI_TRANSAKSI'] = $data->data[$i]->jml_nilai_transaksi;
//        } else {
//            $dataRow[$i]['CPM_SSB_ID'] = $data->data[$i]->CPM_SSB_ID;
//            $dataRow[$i]['CPM_OP_NOMOR'] = $data->data[$i]->CPM_OP_NOMOR;
//            $dataRow[$i]['CPM_WP_NAMA'] = $data->data[$i]->CPM_WP_NAMA;
//            $dataRow[$i]['CPM_SSB_CREATED'] = $data->data[$i]->CPM_SSB_CREATED;
//            $dataRow[$i]['CPM_TRAN_DATE'] = $data->data[$i]->CPM_TRAN_DATE;
//            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_1'] = $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1;
//            $dataRow[$i]['CPM_TRAN_CLAIM_DATETIME'] = $data->data[$i]->CPM_TRAN_CLAIM_DATETIME;
//            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_2'] = $data->data[$i]->CPM_TRAN_OPR_DISPENDA_2;
//            $dataRow[$i]['CPM_OP_BPHTB_TU'] = $data->data[$i]->CPM_OP_BPHTB_TU;
//        }
//    }
    $i=0;
    while($row=mysqli_fetch_assoc($res)){
        
        if ($sts == 3) {
            $dataRow[$i]['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
            $dataRow[$i]['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
            $dataRow[$i]['JML_TRANSAKSI'] = $row['jml_transaksi'];
            $dataRow[$i]['JML_NILAI_TRANSAKSI'] = $row['jml_nilai_transaksi'];
        } else {
            $dataRow[$i]['CPM_SSB_ID'] = $row['CPM_SSB_ID'];
            $dataRow[$i]['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
            $dataRow[$i]['CPM_WP_NAMA'] = $row['CPM_WP_NAMA'];
            $dataRow[$i]['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
            $dataRow[$i]['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
            $dataRow[$i]['CPM_TRAN_CLAIM_DATETIME'] = $row['CPM_TRAN_CLAIM_DATETIME'];
            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
            $dataRow[$i]['CPM_OP_BPHTB_TU'] = $row['CPM_OP_BPHTB_TU'];
			$dataRow[$i]['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
        }
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

function createStrDate($sd) {
    if ($sd != '') {
        $date = explode("/", $sd);
        $dt = $date[2] . $date[1] . $date[0];
        return $dt;
    } else {
        return $sd;
    }
}

function formatDate($sd) {
    if ($sd != '') {
        $yr = substr($sd, 0, 4);  // returns "cde"
        $mt = substr($sd, 4, 2);  // returns "cde"
        $dy = substr($sd, 6, 2);  // returns "cde"
        $dt = $dy . "/" . $mt . "/" . $yr;
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
$arrJudul = array(1 => "Rekapitulasi Pembayaran Berdasarkan User", 2 => "Rekapitulasi Pembayaran Per User",
    3 => "Rekapitulasi Approval Berdasarkan User",
    4 => "Rekapitulasi Approval Per User", 5 => "Rekapitulasi Persetujuan Siap Bayar");
$worksheet1->write_string(1, 0, $arrJudul[$sts], $fBesar);

$filter = "";
if ($sts == 1 || $sts == 3) {
    if ($tgl1 != "" && $tgl2 != "")
        $filter .="Tanggal  $tgl1 s.d $tgl2";
    elseif ($tgl1 != "")
        $filter .= "Tanggal $tgl1";
    elseif ($tgl2 != "")
        $filter .= "Tanggal $tgl2";
}elseif ($sts == 2 || $sts == 4 || $sts == 5) {
    if ($find_notaris != "")
        $filter .= "Nama User : " . $find_notaris . " ";
    if ($tgl1 != "" && $tgl2 != "")
        $filter .="Tanggal $tgl1 s.d $tgl2";
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
if ($sts == 1 || $sts == 3) {#berdasarkan user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama User", $fDtlHead);
    $worksheet1->write_string(3, 3, "Jumlah Transaksi", $fDtlHead);
    $worksheet1->write_string(3, 4, "Jumlah Rupiah Transaksi", $fDtlHead);
} elseif ($sts == 2) {#pembayaran per user
    $worksheet1->write_string(3, 1, "ID SSB", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 4, "Tanggal Bayar", $fDtlHead);
    $worksheet1->write_string(3, 5, "Jumlah Pembayaran", $fDtlHead);
	$worksheet1->write_string(3, 6, "User / Notaris", $fDtlHead);
} else {#approval per user
    $worksheet1->write_string(3, 1, "ID SSB", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 4, "Tanggal Input", $fDtlHead);
    $worksheet1->write_string(3, 5, "Tanggal Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 6, "Petugas Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 7, "Tanggal Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 8, "Pejabat Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 9, "Jumlah Pembayaran", $fDtlHead);
	if($sts == 4 || $sts == 5) {
		$worksheet1->write_string(3, 10, "User / Notaris", $fDtlHead);
	}
}

$worksheet1->merge_cells(0, 0, 0, 5);
$worksheet1->merge_cells(1, 0, 1, 5);
$worksheet1->merge_cells(2, 0, 2, 5);

$baris = 4;

if ($sts == 1 || $sts == 2) {#pembayaran
    getDocumentPembayaran($sts, $dat);
    $hal = 0;
    foreach ($dat as $row) {
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);
        if ($sts == 1) {
            $worksheet1->write_string($baris, 1, $row['OP_NOMOR'], $fDtlCenter);
            $worksheet1->write_string($baris, 2, $row['NOTARIS'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['JML_TRANSAKSI'], $fBiasa);
            $worksheet1->write_string($baris, 4, $row['JML_NILAI_TRANSAKSI'], $fDtlCenter);
        } else {
            $worksheet1->write_string($baris, 1, $row['ID_SSB'], $fDtlCenter);
            $worksheet1->write_string($baris, 2, $row['OP_NOMOR'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['WP_NAMA'], $fBiasa);
            $worksheet1->write_string($baris, 4, $row['PAYMENT_PAID'], $fDtlCenter);
            $worksheet1->write_number($baris, 5, $row['BPHTB_DIBAYAR'], $fDtlNumber);
        }
		 if ($sts == 2) {
			$worksheet1->write_string($baris, 6, $row['USER_NOTARIS'], $fBiasa);
		 }
        $baris++;
    }
} elseif ($sts == 3 || $sts == 4 || $sts == 5) {#approval
    getDocumentApproval($sts, $dat);
    $hal = 0;
    foreach ($dat as $row) {
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);
        if ($sts == 3) {
            $worksheet1->write_string($baris, 1, $row['CPM_OP_NOMOR'], $fDtlCenter);
            $worksheet1->write_string($baris, 2, $row['CPM_TRAN_OPR_NOTARIS'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['JML_TRANSAKSI'], $fBiasa);
            $worksheet1->write_string($baris, 4, $row['JML_NILAI_TRANSAKSI'], $fDtlCenter);
        } else {
            $worksheet1->write_string($baris, 1, $row['CPM_SSB_ID'], $fDtlCenter);
            $worksheet1->write_string($baris, 2, $row['CPM_OP_NOMOR'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['CPM_WP_NAMA'], $fBiasa);
            $worksheet1->write_string($baris, 4, $row['CPM_SSB_CREATED'], $fDtlCenter);
            $worksheet1->write_number($baris, 5, $row['CPM_TRAN_DATE'], $fDtlNumber);
            $worksheet1->write_string($baris, 6, $row['CPM_TRAN_OPR_DISPENDA_1'], $fDtlCenter);
            $worksheet1->write_string($baris, 7, $row['CPM_TRAN_CLAIM_DATETIME'], $fBiasa);
            $worksheet1->write_string($baris, 8, $row['CPM_TRAN_OPR_DISPENDA_2'], $fBiasa);
            $worksheet1->write_string($baris, 9, $row['CPM_OP_BPHTB_TU'], $fDtlCenter);
        }
		 if (($sts == 4)||($sts == 5)) {
			$worksheet1->write_string($baris, 10, $row['CPM_TRAN_OPR_NOTARIS'], $fBiasa);
		 }
        $baris++;
    }
}


$workbook->close();
?>
