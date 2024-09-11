<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);
ini_set("memory_limit", "1024M");

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

function mysql2json($mysql_result, $name)
{
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
        $json .= "{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json .= "\n";
            } else {
                $json .= ",\n";
            }
        }
        if ($x == $rows - 1) {
            $json .= "\n}\n";
        } else {
            $json .= "\n},\n";
        }
    }
    $json .= "]\n}";
    return ($json);
}

function getConfigValue($id, $key)
{
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

function getDocumentPembayaran($sts, &$dat)
{
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2, $wp_nama;
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

    if ($sts == 9 || $sts == 10)
        $where = " WHERE PAYMENT_FLAG = 0";
    if ($sts == 11)
        $where = " WHERE bphtb_dibayar = 0";

    if ($sts == 10)
        // $where .= " AND DATE(expired_date) < '".date('Y-m-d')."'";
        $where .= " AND (PAYMENT_FLAG <> 1 OR ISNULL(PAYMENT_FLAG)) AND DATE_ADD(expired_date, INTERVAL 30 DAY) < NOW()";

    if ($srcTxt != "")
        $where .= " AND (wp_nama LIKE '" . mysqli_escape_string($DBLink, $srcTxt) . "%' OR wp_noktp LIKE '" . mysqli_escape_string($DBLink, $srcTxt) . "%' 
                    OR op_nomor LIKE '" . mysqli_escape_string($DBLink, $srcTxt) . "%')";

    if ($wp_nama != "") {
        $where .= " AND (wp_nama LIKE '" . mysqli_escape_string($DBLink, $wp_nama) . "%' OR wp_noktp LIKE '" . mysqli_escape_string($DBLink, $wp_nama) . "%' 
                    OR op_nomor LIKE '" . mysqli_escape_string($DBLink, $wp_nama) . "%')";
    }
    if ($find_notaris != "") {
        if ($sts == 2) {
            $where .= " AND wp_nama like '%" . mysqli_escape_string($DBLink, $find_notaris) . "%'";
        } else {
            $where .= " AND author like '%" . mysqli_escape_string($DBLink, $find_notaris) . "%'";
        }
    }

    if ($tgl1 != "" && $tgl2 != "")
        $where .= " AND  (DATE(payment_paid) between '" . mysqli_escape_string($DBLink, $tgl1) . "' and '" . mysqli_escape_string($DBLink, $tgl2) . "')";
    elseif ($tgl1 != "")
        $where .= " AND  (DATE(payment_paid) = '" . mysqli_escape_string($DBLink, $tgl1) . "')";
    elseif ($tgl2 != "")
        $where .= " AND  (DATE(payment_paid) = '" . mysqli_escape_string($DBLink, $tgl2) . "')";


    $iErrCode = 0;

    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }

    if ($sts == 1) { #berdasarkan user
        $query = "SELECT *,count(id_ssb) as jml_transaksi, sum(bphtb_dibayar) as jml_nilai_transaksi
                 FROM $DbTable $where GROUP BY bphtb_notaris 
                 ORDER BY saved_Date DESC ";
        $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where} GROUP BY bphtb_notaris";
    } else { #per user
        $query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC ";
        $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where}";
    }

    $res = mysqli_query($LDBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($LDBLink));
        return false;
    }



    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        if ($sts == 1) {
            $dataRow[$i]['OP_NOMOR'] = $row["op_nomor"];
            $dataRow[$i]['NOTARIS'] = $row['bphtb_notaris'];
            $dataRow[$i]['JML_TRANSAKSI'] = $row['jml_transaksi'];
            $dataRow[$i]['JML_NILAI_TRANSAKSI'] = $row['jml_nilai_transaksi'];
        } else {
            $dataRow[$i]['ID_SSB'] = $row['id_ssb'];
            $dataRow[$i]['CPM_SSB_ID'] = $row['id_switching'];
            $dataRow[$i]['OP_NOMOR'] = $row['op_nomor'];
            $dataRow[$i]['WP_NAMA'] = $row['wp_nama'];
            $dataRow[$i]['PAYMENT_PAID'] = $row['payment_paid'];
            $dataRow[$i]['BPHTB_DIBAYAR'] = $row['bphtb_dibayar'];
            $dataRow[$i]['USER_NOTARIS'] = $row['author'];
            $dataRow[$i]['NOTARIS'] = $row['bphtb_notaris'];
            $dataRow[$i]['PAYMENT_CODE'] = $row['payment_code'];
            $dataRow[$i]['wp_kecamatan'] = $row['wp_kecamatan'];
            $dataRow[$i]['wp_kelurahan'] = $row['wp_kelurahan'];
            $dataRow[$i]['op_kecamatan'] = $row['op_kecamatan'];
            $dataRow[$i]['op_kelurahan'] = $row['op_kelurahan'];
            $dataRow[$i]['saved_date'] = $row['saved_date'];
            $dataRow[$i]['alamat_op'] = $row['op_letak'];
            $dataRow[$i]['payment_code'] = $row['payment_code'];
        }
        $i++;
    }

    // if ($sts == 8) {
    //     $query_get_data_backend = "select id_switching from $DbTable where payment_flag=0";
    //     $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    //     $whereIn = array();
    //     while ($dtBE = mysqli_fetch_array($resBE)) {
    //         $whereIn[] = $dtBE['id_switching'];
    //     }
    //     $whereIn = "('" . implode("','", $whereIn) . "')";

    //     $query = "SELECT * , b.CPM_TRAN_DATE as VERIFIKASI_DATE FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
    //                  a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
    //                  ORDER BY b.CPM_TRAN_DATE DESC";
    //     $res = mysqli_query($DBLink, $query);
    //     while($row=mysqli_fetch_assoc($res)){
    //         $dataRow[$i]['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
    //         $dataRow[$i]['VERIFIKASI_DATE'] = $row['VERIFIKASI_DATE'];
    //         $dataRow[$i]['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
    //         $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
    //         $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
    //        $i++; 
    //     }
    // }
    // var_dump($query);exit;
    $dat = $dataRow;
}
function funcgetssbv2($sts, &$dat)
{
    global $DBLink, $app;


    $a = $app;
    $DbName = getConfigValue($a, 'BPHTBDBNAME');
    $DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($a, 'BPHTBTABLE');
    $DbUser = getConfigValue($a, 'BPHTBUSERNAME');
    $tw = getConfigValue($a, 'TENGGAT_WAKTU');
    if ($sts == 8) {
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        $query = "SELECT id_switching FROM gw_ssb.ssb where PAYMENT_FLAG = 1";
        $wheressb = "";
        $res = mysqli_query($LDBLink, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $wheressb .= "'" . $row['id_switching'] . "',";
        }
        $where .= "AND a.CPM_SSB_ID IN (" . rtrim($wheressb, ',') . ")";
    }
    $query = "SELECT a.*,b.*, c.CPM_JENIS_HAK,e.nm_lengkap nm_notaris_lengkap FROM cppmod_ssb_doc a 
                inner join cppmod_ssb_tranmain b on a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
                INNER JOIN cppmod_ssb_jenis_hak c on a.CPM_OP_JENIS_HAK = c.CPM_KD_JENIS_HAK
                LEFT JOIN central_user d ON a.CPM_SSB_AUTHOR = d.CTR_U_UID
                LEFT JOIN tbl_reg_user_notaris e ON (d.CTR_U_ID = e.uuid OR d.CTR_U_UID = e.userId)
                WHERE (b.CPM_TRAN_STATUS = 5 OR b.CPM_TRAN_STATUS = 3)
                {$where}

                ORDER BY b.CPM_TRAN_DATE DESC";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($DBLink));
        return false;
    }
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        // while($row=mysqli_fetch_assoc($res)){
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_SSB_ID'] = $row['CPM_SSB_ID'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_WP_NAMA'] = $row['CPM_WP_NAMA'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_WP_NAMA_LAMA'] = $row['CPM_WP_NAMA_LAMA'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_WP_KELURAHAN'] = $row['CPM_WP_KELURAHAN'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_WP_KECAMATAN'] = $row['CPM_WP_KECAMATAN'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_OP_KELURAHAN'] = $row['CPM_OP_KELURAHAN'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_OP_KECAMATAN'] = $row['CPM_OP_KECAMATAN'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_JENIS_HAK'] = $row['CPM_JENIS_HAK'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['nm_notaris_lengkap'] = $row['nm_notaris_lengkap'];

        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_TRAN_CLAIM_DATETIME'] = $row['CPM_TRAN_CLAIM_DATETIME'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_OP_BPHTB_TU'] = $row['CPM_OP_BPHTB_TU'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_BPHTB_BAYAR'] = $row['CPM_BPHTB_BAYAR'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_SSB_AKUMULASI'] = $row['CPM_SSB_AKUMULASI'];
        $dataRow[$row['CPM_SSB_ID']][$row['CPM_TRAN_STATUS']]['CPM_OP_HARGA'] = $row['CPM_OP_HARGA'];
        $i++;
    }
    $dat = $dataRow;
    return $dat;
}


function funcgetssb($idssb, $sts_trnmain, &$dat)
{
    global $DBLink;

    $query = "SELECT a.*,b.*, c.CPM_JENIS_HAK,e.nm_lengkap nm_notaris_lengkap FROM cppmod_ssb_doc a 
                inner join cppmod_ssb_tranmain b on a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
                INNER JOIN cppmod_ssb_jenis_hak c on a.CPM_OP_JENIS_HAK = c.CPM_KD_JENIS_HAK
                LEFT JOIN central_user d ON a.CPM_SSB_AUTHOR = d.CTR_U_UID
                LEFT JOIN tbl_reg_user_notaris e ON (d.CTR_U_ID = e.uuid OR d.CTR_U_UID = e.userId)
                WHERE a.`CPM_SSB_ID` = \"$idssb\" 
                AND b.`CPM_TRAN_STATUS` = $sts_trnmain
                ORDER BY b.CPM_TRAN_DATE DESC";
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($DBLink));
        return false;
    }
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $dataRow['CPM_SSB_ID'] = $row['CPM_SSB_ID'];
        $dataRow['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
        $dataRow['CPM_WP_NAMA'] = $row['CPM_WP_NAMA'];
        $dataRow['CPM_WP_NAMA_LAMA'] = $row['CPM_WP_NAMA_LAMA'];
        $dataRow['CPM_WP_KELURAHAN'] = $row['CPM_WP_KELURAHAN'];
        $dataRow['CPM_WP_KECAMATAN'] = $row['CPM_WP_KECAMATAN'];
        $dataRow['CPM_OP_KELURAHAN'] = $row['CPM_OP_KELURAHAN'];
        $dataRow['CPM_OP_KECAMATAN'] = $row['CPM_OP_KECAMATAN'];
        $dataRow['CPM_JENIS_HAK'] = $row['CPM_JENIS_HAK'];

        $dataRow['nm_notaris_lengkap'] = $row['nm_notaris_lengkap'];
        $dataRow['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
        $dataRow['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
        $dataRow['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
        $dataRow['CPM_TRAN_CLAIM_DATETIME'] = $row['CPM_TRAN_CLAIM_DATETIME'];
        $dataRow['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
        $dataRow['CPM_OP_BPHTB_TU'] = $row['CPM_OP_BPHTB_TU'];
        $dataRow['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
        $dataRow['CPM_BPHTB_BAYAR'] = $row['CPM_BPHTB_BAYAR'];
        $dataRow['CPM_SSB_AKUMULASI'] = $row['CPM_SSB_AKUMULASI'];
        $dataRow['CPM_OP_HARGA'] = $row['CPM_OP_HARGA'];
        $i++;
    }
    // var_dump($query);exit;
    $dat = $dataRow;
    return $dat;
}
function getDocumentgwssb($sts, &$dat)
{
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2, $kec, $kel;
    $srcTxt = $src;

    $DbName = getConfigValue($app, 'BPHTBDBNAME');
    $DbHost = getConfigValue($app, 'BPHTBHOSTPORT');
    $DbPwd = getConfigValue($app, 'BPHTBPASSWORD');
    $DbTable = getConfigValue($app, 'BPHTBTABLE');
    $DbUser = getConfigValue($app, 'BPHTBUSERNAME');

    $wheressb = '1=1 ';

    if ($tgl1 != "" && $tgl2 != "")
        $wheressb .= " AND  (date(a.payment_paid) between '" . $tgl1 . "' and '" . $tgl2 . "')";
    elseif ($tgl1 != "")
        $wheressb .= " AND  (date(a.payment_paid) = '" . $tgl1 . "')";
    elseif ($tgl2 != "")
        $wheressb .= " AND  (date(a.payment_paid) = '" . $tgl2 . "')";

    if ($kec != '')
        $wheressb .= " AND  a.op_kecamatan = '" . $kec . "'";
    if ($kel != '')
        $wheressb .= " AND  a.op_kelurahan = '" . $kel . "'";

    $iErrCode = 0;
    SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    if ($iErrCode != 0) {
        $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
            error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
        exit(1);
    }
    // $query_get_data_backend = "select * from $DbTable where payment_flag=1 $wheressb GROUP BY op_kelurahan ORDER BY payment_paid ASC";
    // var_dump($query_get_data_backend);die;
    $query_get_data_backend = "SELECT a.*
    FROM $DbTable a
    JOIN (SELECT op_kelurahan 
    FROM $DbTable 
    GROUP BY op_kelurahan) b
    ON a.op_kelurahan = b.op_kelurahan
    where payment_flag=1 AND {$wheressb}
    ORDER BY b.op_kelurahan ASC";

    // $query_get_data_backend = "select * from $DbTable where payment_flag=1 $wheressb ORDER BY payment_paid ASC";
    
    $resBE = mysqli_query($LDBLink, $query_get_data_backend);
    $i = 0;
    while ($dtBE = mysqli_fetch_assoc($resBE)) {
        $dataRow[$i]['CPM_SSB_ID'] = $dtBE['id_switching'];
        $dataRow[$i]['op_nomor'] = $dtBE['op_nomor'];
        $dataRow[$i]['wp_nama'] = $dtBE['wp_nama'];
        $dataRow[$i]['op_letak'] = $dtBE['op_letak'];
        $dataRow[$i]['op_kelurahan'] = $dtBE['op_kelurahan'];
        $dataRow[$i]['op_kecamatan'] = $dtBE['op_kecamatan'];
        $dataRow[$i]['payment_paid'] = $dtBE['payment_paid'];
        $dataRow[$i]['payment_code'] = $dtBE['payment_code'];
        $dataRow[$i]['bphtb_collectible'] = $dtBE['bphtb_collectible'];
        $dataRow[$i]['NOTARIS'] = $dtBE['bphtb_notaris'];
        $i++;
    }
    $dat = $dataRow;
    return $dat;
}

function getDocumentApproval($sts, &$dat)
{
    global $DBLink, $json, $app, $src, $find_notaris, $tgl1, $tgl2;
    $srcTxt = $src;

    $where = " WHERE b.CPM_TRAN_STATUS = '5'"; #disetujui
    $wheressb = '';
    if ($find_notaris != "")
        $where .= " AND (b.CPM_TRAN_OPR_NOTARIS like '%" . $find_notaris . "%')";
    if ($tgl1 != "" && $tgl2 != "")
        $where .= " AND  (date(b.CPM_TRAN_DATE) between '" . $tgl1 . "' and '" . $tgl2 . "')";
    elseif ($tgl1 != "")
        $where .= " AND  (date(b.CPM_TRAN_DATE) = '" . $tgl1 . "')";
    elseif ($tgl2 != "")
        $where .= " AND  (date(b.CPM_TRAN_DATE) = '" . $tgl2 . "')";

    if ($sts == 3) { #berdasarkan user
        $query = "SELECT *,count(a.CPM_SSB_ID) as jml_transaksi, sum(a.CPM_OP_BPHTB_TU) as jml_nilai_transaksi
                 FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
                 $where GROUP BY b.CPM_TRAN_OPR_NOTARIS 
                 ORDER BY b.CPM_TRAN_DATE DESC";
    } else if ($sts == 4) { #per user
        $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where
                 ORDER BY b.CPM_TRAN_DATE DESC";
    } else if ($sts == 5) { #siap bayar per user
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

        $query_get_data_backend = "select id_switching from $DbTable where payment_flag=0 ";
        $resBE = mysqli_query($LDBLink, $query_get_data_backend);
        $whereIn = array();
        while ($dtBE = mysqli_fetch_array($resBE)) {
            $whereIn[] = $dtBE['id_switching'];
        }
        $whereIn = "('" . implode("','", $whereIn) . "')";
        $query = "SELECT * , b.CPM_TRAN_DATE as VERIFIKASI_DATE FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
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

        $query_get_data_backend = "select id_switching from $DbTable where payment_flag=1 $wheressb";
        // echo $query_get_data_backend;
        $resBE = mysqli_query($LDBLink, $query_get_data_backend);
        $whereIn = array();
        while ($dtBE = mysqli_fetch_array($resBE)) {
            $whereIn[] = $dtBE['id_switching'];
        }
        $whereIn = "('" . implode("','", $whereIn) . "')";

        $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
                 GROUP BY b.CPM_TRAN_STATUS 
                 ORDER BY b.CPM_TRAN_DATE DESC";
    }
    // echo $query;
    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        print_r($query . mysqli_error($DBLink));
        return false;
    }
    $i = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        if ($sts == 3) {
            $dataRow[$i]['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
            $dataRow[$i]['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
            $dataRow[$i]['JML_TRANSAKSI'] = $row['jml_transaksi'];
            $dataRow[$i]['JML_NILAI_TRANSAKSI'] = $row['jml_nilai_transaksi'];
        } else {
            $dataRow[$i]['CPM_SSB_ID'] = $row['CPM_SSB_ID'];
            $dataRow[$i]['CPM_OP_NOMOR'] = $row['CPM_OP_NOMOR'];
            $dataRow[$i]['CPM_WP_NAMA'] = $row['CPM_WP_NAMA'];
            $dataRow[$i]['CPM_WP_KELURAHAN'] = $row['CPM_WP_KELURAHAN'];
            $dataRow[$i]['CPM_WP_KECAMATAN'] = $row['CPM_WP_KECAMATAN'];
            $dataRow[$i]['CPM_OP_KELURAHAN'] = $row['CPM_OP_KELURAHAN'];
            $dataRow[$i]['CPM_OP_KECAMATAN'] = $row['CPM_OP_KECAMATAN'];

            $dataRow[$i]['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
            $dataRow[$i]['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
            $dataRow[$i]['CPM_TRAN_CLAIM_DATETIME'] = $row['CPM_TRAN_CLAIM_DATETIME'];
            $dataRow[$i]['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
            $dataRow[$i]['CPM_OP_BPHTB_TU'] = $row['CPM_OP_BPHTB_TU'];
            $dataRow[$i]['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
            $dataRow[$i]['CPM_BPHTB_BAYAR'] = $row['CPM_BPHTB_BAYAR'];
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
$wp_nama = $q->wp_nama;
$kec = $q->kec;
$kel = $q->kel;

function createStrDate($sd)
{
    if ($sd != '') {
        $date = explode("/", $sd);
        $dt = $date[2] . $date[1] . $date[0];
        return $dt;
    } else {
        return $sd;
    }
}

function formatDate($sd)
{
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

function HeaderingExcel($filename)
{
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=$filename");
    header("Expires:0");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");
}
function get_code_payment($id, $sts)
{
    global $app;
    $a = $app;
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
    if ($sts == 8) {
        $where = "AND payment_flag = 1";
    }
    $query_get_data_backend = "select id_switching,payment_code,payment_paid from $DbTable where id_switching ='{$id}' $where";
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
$fBesar = &$workbook->add_format();
$fBesar->set_size(14);
$fBesar->set_align("center");
$fBesar->set_bold();

$fBiasa = &$workbook->add_format();
$fBiasa->set_align("left");
//class untuk mencetak tulisan tanpa border (untuk judul laporan)
$fList = &$workbook->add_format();
$fList->set_border(0);
//class untuk mencetak tulisan dengan border dan ditengah kolom (untuk judul kolom)
$fDtlHead = &$workbook->add_format();
$fDtlHead->set_border(1);
$fDtlHead->set_align("center");
$fDtlHead->set_align("vcentre");
$fDtlHead->set_text_wrap(1);

$fDtlCenter = &$workbook->add_format();
$fDtlCenter->set_border(1);
$fDtlCenter->set_align("center");
$fDtlCenter->set_align("vcentre");
$fDtlCenter->set_text_wrap(1);

//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai string)
$fDtl = &$workbook->add_format();
$fDtl->set_border(1);
//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai numerik)
$fDtlNumber = &$workbook->add_format();
$fDtlNumber->set_border(1);
$fDtlNumber->set_align("right");
$fDtlNumber->set_num_format(3);
//class untuk men-zoom laporan 75%
$worksheet1 = &$workbook->add_worksheet("Halaman 1");
$worksheet1->set_zoom(100);

//$header = $p->header;
$worksheet1->write_string(0, 0, "Monitoring BPHTB ", $fBesar);
$arrJudul = array(
    1 => "Rekapitulasi Pembayaran Berdasarkan User",
    2 => "Rekapitulasi Pembayaran Per User",
    3 => "Rekapitulasi Approval Berdasarkan User",
    4 => "Rekapitulasi Approval Per User",
    5 => "Rekapitulasi Persetujuan Siap Bayar",
    9 => "Rekapitulasi Belum Bayar",
    8 => "Rekapitulasi Sudah Bayar"
);
$worksheet1->write_string(1, 0, $arrJudul[$sts], $fBesar);

$filter = "";
if ($sts == 1 || $sts == 3) {
    if ($tgl1 != "" && $tgl2 != "")
        $filter .= "Tanggal  $tgl1 s.d $tgl2";
    elseif ($tgl1 != "")
        $filter .= "Tanggal $tgl1";
    elseif ($tgl2 != "")
        $filter .= "Tanggal $tgl2";
} elseif ($sts == 2 || $sts == 4 || $sts == 5) {
    if ($find_notaris != "")
        $filter .= "Nama User : " . $find_notaris . " ";
    if ($tgl1 != "" && $tgl2 != "")
        $filter .= "Tanggal $tgl1 s.d $tgl2";
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
if ($sts == 1 || $sts == 3) { #berdasarkan user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama User", $fDtlHead);
    $worksheet1->write_string(3, 3, "Jumlah Transaksi", $fDtlHead);
    $worksheet1->write_string(3, 4, "Jumlah Rupiah Transaksi", $fDtlHead);
} elseif ($sts == 2) { #pembayaran per user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Tanggal Bayar", $fDtlHead);
    $worksheet1->write_string(3, 4, "Jumlah Pembayaran", $fDtlHead);
    $worksheet1->write_string(3, 5, "User / Notaris", $fDtlHead);
} elseif ($sts == 4) { #approval per user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Tanggal Input", $fDtlHead);
    $worksheet1->write_string(3, 4, "Tanggal Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 5, "Petugas Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 6, "Pejabat Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 7, "Jumlah Pembayaran", $fDtlHead);
    $worksheet1->write_string(3, 8, "Notaris", $fDtlHead);
} elseif ($sts == 5) { #approval per user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Tanggal Input", $fDtlHead);
    $worksheet1->write_string(3, 4, "Tanggal Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 5, "Petugas Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 6, "Tanggal Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 7, "Pejabat Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 8, "Kode Bayar", $fDtlHead);
    $worksheet1->write_string(3, 9, "Jumlah Pembayaran", $fDtlHead);
} elseif ($sts == 10 || $sts == 9) { #approval per user
    $worksheet1->write_string(3, 1, "Nomor Objek Pajak", $fDtlHead);
    $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
    $worksheet1->write_string(3, 3, "Alamat OP", $fDtlHead);
    $worksheet1->write_string(3, 4, "Kelurahan", $fDtlHead);
    $worksheet1->write_string(3, 5, "Kecamatan", $fDtlHead);
    $worksheet1->write_string(3, 6, "Nama Penjual/Pengembang", $fDtlHead);
    $worksheet1->write_string(3, 7, "Notaris", $fDtlHead);
    $worksheet1->write_string(3, 8, "Jenis BPHTB", $fDtlHead);
    $worksheet1->write_string(3, 9, "Tanggal Pelaporan", $fDtlHead);
    $worksheet1->write_string(3, 10, "Tanggal Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 11, "Petugas Verifikasi", $fDtlHead);
    $worksheet1->write_string(3, 12, "Tanggal Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 13, "Petugas Persetujuan", $fDtlHead);
    $worksheet1->write_string(3, 14, "Kode Bayar", $fDtlHead);
    $worksheet1->write_string(3, 15, "Harga Transaksi", $fDtlHead);
    $worksheet1->write_string(3, 16, "BPHTB Harus Dibayar", $fDtlHead);
} else { #approval per user
    if ($sts == 6) {
        $worksheet1->write_string(3, 1, "NOP", $fDtlHead);
        $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
        $worksheet1->write_string(3, 3, "Notaris", $fDtlHead);
        $worksheet1->write_string(3, 4, "Tanggal Pembayaran", $fDtlHead);
        $worksheet1->write_string(3, 5, "Jumlah Pembayaran", $fDtlHead);
        $worksheet1->write_string(3, 6, "Kecamatan", $fDtlHead);
        $worksheet1->write_string(3, 7, "Desa", $fDtlHead);
    } else if ($sts == 11) { #pembayaran
        $worksheet1->write_string(3, 1, "NOP", $fDtlHead);
        $worksheet1->write_string(3, 2, "Nama Wajib Pajak", $fDtlHead);
        $worksheet1->write_string(3, 3, "BPHTB Yang Harus Dibayar", $fDtlHead);
        if ($sts == 9) {
            $worksheet1->write_string(3, 4, "Kode Bayar", $fDtlHead);
        } else {
            $worksheet1->write_string(3, 4, "Tanggal Pembayaran", $fDtlHead);
        }
        $worksheet1->write_string(3, 5, "Tanggal Pelaporan", $fDtlHead);
        $worksheet1->write_string(3, 6, "USER", $fDtlHead);
    } else {
        $spesial = 1;
        if ($sts == 8) {
            $worksheet1->write_string(3, $spesial, "Kecamatan", $fDtlHead);
            $spesial++;
            $worksheet1->write_string(3, $spesial, "Kelurahan", $fDtlHead);
            $spesial++;
        }
        $worksheet1->write_string(3, $spesial, "Nomor Objek Pajak", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Nama Wajib Pajak", $fDtlHead);
        $spesial++;
        if ($sts == 8) {
            $worksheet1->write_string(3, $spesial, "Alamat OP", $fDtlHead);
            $spesial++;
            $worksheet1->write_string(3, $spesial, "Nama Penjual", $fDtlHead);
            $spesial++;
            $worksheet1->write_string(3, $spesial, "Notaris", $fDtlHead);
            $spesial++;
            $worksheet1->write_string(3, $spesial, "Jenis BPHTB", $fDtlHead);
            $spesial++;
        }

        $worksheet1->write_string(3, $spesial, "Tanggal Input", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Tanggal Verifikasi", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Petugas Verifikasi", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Tanggal Persetujuan", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Pejabat Persetujuan", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Tanggal Bayar", $fDtlHead);
        $spesial++;
        $worksheet1->write_string(3, $spesial, "Kode Bayar", $fDtlHead);
        $spesial++;
        if ($sts == 8) {
            $worksheet1->write_string(3, $spesial, "Harga Transaksi", $fDtlHead);
            $spesial++;
        }
        $worksheet1->write_string(3, $spesial, "Jumlah Pembayaran", $fDtlHead);
        $spesial++;
    }
}
if ($sts == 4 || $sts == 5) {
    $worksheet1->write_string(3, 10, "User / Notaris", $fDtlHead);
}

$worksheet1->merge_cells(0, 0, 0, 5);
$worksheet1->merge_cells(1, 0, 1, 5);
$worksheet1->merge_cells(2, 0, 2, 5);

$baris = 4;

if ($sts == 1 || $sts == 2 || $sts == 6 || $sts == 11) { #pembayaran
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
            if ($sts == 6) {
                $worksheet1->write_string($baris, 1, $row['OP_NOMOR'], $fBiasa);
                $worksheet1->write_string($baris, 2, $row['WP_NAMA'], $fBiasa);
                $worksheet1->write_string($baris, 3, $row['USER_NOTARIS'], $fBiasa);
                $worksheet1->write_string($baris, 4, $row['PAYMENT_PAID'], $fDtlCenter);
                $worksheet1->write_string($baris, 5, $row['BPHTB_DIBAYAR'], $fDtlNumber);
                $worksheet1->write_string($baris, 6, $row['wp_kecamatan'], $fBiasa);
                $worksheet1->write_string($baris, 7, $row['wp_kelurahan'], $fBiasa);
            } else if ($sts == 9 || $sts == 10 || $sts == 11) {
                $worksheet1->write_string($baris, 1, $row['OP_NOMOR'], $fDtlCenter);
                $worksheet1->write_string($baris, 2, $row['WP_NAMA'], $fBiasa);
                $worksheet1->write_string($baris, 3, $row['BPHTB_DIBAYAR'], $fBiasa);
                if ($sts == 9) {
                    $worksheet1->write_string($baris, 4, $row['PAYMENT_CODE'], $fDtlNumber);
                } else {
                    $worksheet1->write_string($baris, 4, $row['PAYMENT_PAID'], $fDtlCenter);
                }

                $worksheet1->write_string($baris, 5, $row['saved_date'], $fDtlCenter);
                $worksheet1->write_string($baris, 6, $row['USER_NOTARIS'], $fDtlNumber);
            } else {

                $verifydate = ($sts == 5) ? $row['VERIFIKASI_DATE'] : $row['CPM_TRAN_DATE'];
                $claimdate = ($sts == 5) ? $row['CPM_TRAN_DATE'] : $row['CPM_TRAN_CLAIM_DATETIME'];

                $worksheet1->write_string($baris, 1, $row['OP_NOMOR'], $fBiasa);
                $worksheet1->write_string($baris, 2, $row['WP_NAMA'], $fBiasa);
                $worksheet1->write_string($baris, 3, $row['PAYMENT_PAID'], $fBiasa);
                $worksheet1->write_string($baris, 4, $row['BPHTB_DIBAYAR'], $fBiasa);
                $worksheet1->write_string($baris, 5, $row['USER_NOTARIS'], $fBiasa);
            }
        }
        $baris++;
    }
} elseif ($sts == 3 || $sts == 4 || $sts == 5) { #approval
    getDocumentApproval($sts, $dat);
    $hal = 0;
    foreach ($dat as $row) {
        $no__ = 6;
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);
        if ($sts == 3) {
            $worksheet1->write_string($baris, 1, $row['CPM_OP_NOMOR'], $fDtlCenter);
            $worksheet1->write_string($baris, 2, $row['CPM_TRAN_OPR_NOTARIS'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['JML_TRANSAKSI'], $fBiasa);
            $worksheet1->write_string($baris, 4, $row['JML_NILAI_TRANSAKSI'], $fDtlCenter);
        } else {
            $worksheet1->write_string($baris, 1, $row['CPM_OP_NOMOR'], $fBiasa);
            $worksheet1->write_string($baris, 2, $row['CPM_WP_NAMA'], $fBiasa);
            $worksheet1->write_string($baris, 3, $row['CPM_SSB_CREATED'], $fDtlCenter);
            $worksheet1->write_string($baris, 4, $row['CPM_TRAN_DATE'], $fDtlCenter);
            $worksheet1->write_string($baris, 5, $row['CPM_TRAN_OPR_DISPENDA_1'], $fDtlCenter);
            if ($sts == 5) {
                $no__ = 6;

                $getssb = funcgetssb($row['CPM_SSB_ID'], 5);
                $worksheet1->write_string($baris, $no__, $getssb['CPM_SSB_CREATED'], $fDtlCenter);
                $no__++;
            }
            $worksheet1->write_string($baris, $no__++, $row['CPM_TRAN_OPR_DISPENDA_2'], $fDtlCenter);
            if ($sts == 5) {
                $getdtssb = get_code_payment($row['CPM_SSB_ID'], 5);
                $worksheet1->write_string($baris, $no__++, $getdtssb['payment_code'], $fDtlCenter);
            }
            $worksheet1->write_string($baris, $no__++, $row['CPM_BPHTB_BAYAR'], $fDtlCenter);
        }
        if (($sts == 4) || ($sts == 5)) {
            $worksheet1->write_string($baris, $no__++, $row['CPM_TRAN_OPR_NOTARIS'], $fBiasa);
        }
        $baris++;
    }
} elseif ($sts == 8) {
    getDocumentgwssb($sts, $dat);
    $hal = 0;

    funcgetssbv2($sts, $dattt);
    foreach ($dat as $row) {
        // var_dump($dattt[$row['CPM_SSB_ID']][3]);exit;
        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);

        // $getssb = funcgetssb($row['CPM_SSB_ID'],5);
        $getssb = $dattt[$row['CPM_SSB_ID']][5];
        // $ssbdocverif = funcgetssb($row['CPM_SSB_ID'],3);
        $ssbdocverif = $dattt[$row['CPM_SSB_ID']][3];
        $verifydate = $ssbdocverif['CPM_TRAN_DATE'];
        $claimdate = $getssb['CPM_TRAN_DATE'];

        $worksheet1->write_string($baris, 1, $row['op_kecamatan'], $fDtlHead);
        $worksheet1->write_string($baris, 2, $row['op_kelurahan'], $fDtlHead);
        $worksheet1->write_string($baris, 3, $row['op_nomor'], $fDtlHead);
        $worksheet1->write_string($baris, 4, $row['wp_nama'], $fDtlHead);
        $worksheet1->write_string($baris, 5, $row['op_letak'], $fDtlHead);
        $worksheet1->write_string($baris, 6, $getssb['CPM_WP_NAMA_LAMA'], $fDtlHead);

        if ($row['NOTARIS'] != "") {
            # code...
            $worksheet1->write_string($baris, 7, $row['NOTARIS'], $fDtlHead);
        } else {
            $worksheet1->write_string($baris, 7, $getssb['nm_notaris_lengkap'], $fDtlHead);
        }
        $worksheet1->write_string($baris, 8, $getssb['CPM_JENIS_HAK'], $fDtlHead);

        $worksheet1->write_string($baris, 9, $getssb['CPM_SSB_CREATED'], $fDtlHead);
        $worksheet1->write_string($baris, 10, $verifydate, $fDtlHead);
        $worksheet1->write_string($baris, 11, $getssb['CPM_TRAN_OPR_DISPENDA_1'], $fDtlHead);
        $worksheet1->write_string($baris, 12, $claimdate, $fList);
        $worksheet1->write_string($baris, 13, $getssb['CPM_TRAN_OPR_DISPENDA_2'], $fList);
        $worksheet1->write_string($baris, 14, $row['payment_paid'], $fDtlCenter);
        $worksheet1->write_string($baris, 15, $row['payment_code'], $fDtlCenter);
        $worksheet1->write_number($baris, 16, $getssb['CPM_OP_HARGA'], $fDtlNumber);
        $worksheet1->write_number($baris, 17, $row['bphtb_collectible'], $fDtlNumber);

        $totalbayar += $row['bphtb_collectible'];
        $worksheet1->write(4, 0, True);
        $baris++;
    }
    $judul_jumlah = "Total Bayar";

    $worksheet1->write_string($baris, 2, $judul_jumlah, $fBiasa);
    $worksheet1->write_number($baris, 17, $totalbayar, $fDtlNumber);
} elseif ($sts == 10 || $sts == 9) {
    getDocumentPembayaran($sts, $dat);
    $hal = 0;
    foreach ($dat as $row) {


        $getssb = funcgetssb($row['CPM_SSB_ID'], 5);
        $ssbdocverif = funcgetssb($row['CPM_SSB_ID'], 3);

        $worksheet1->write_string($baris, 0, (++$hal) . ".", $fDtlCenter);
        $worksheet1->write_string($baris, 1, $row['OP_NOMOR'], $fDtlCenter);
        $worksheet1->write_string($baris, 2, $row['WP_NAMA'], $fBiasa);
        $worksheet1->write_string($baris, 3, $row['alamat_op'], $fBiasa);
        $worksheet1->write_string($baris, 4, $row['op_kelurahan'], $fBiasa);
        $worksheet1->write_string($baris, 5, $row['op_kecamatan'], $fBiasa);
        $worksheet1->write_string($baris, 6, $getssb['CPM_WP_NAMA_LAMA'], $fBiasa);
        if ($row['NOTARIS'] != "") {
            $worksheet1->write_string($baris, 7, $row['NOTARIS'], $fBiasa);
        } else {
            $worksheet1->write_string($baris, 7, $getssb['nm_notaris_lengkap'], $fBiasa);
        }
        $worksheet1->write_string($baris, 8, $getssb['CPM_JENIS_HAK'], $fBiasa);
        $worksheet1->write_string($baris, 9, $getssb['CPM_SSB_CREATED'], $fBiasa);
        $worksheet1->write_string($baris, 10, $ssbdocverif['CPM_TRAN_DATE'], $fBiasa);
        $worksheet1->write_string($baris, 11, $getssb['CPM_TRAN_OPR_DISPENDA_1'], $fBiasa);
        $worksheet1->write_string($baris, 12, $getssb['CPM_TRAN_DATE'], $fBiasa);
        $worksheet1->write_string($baris, 13, $getssb['CPM_TRAN_OPR_DISPENDA_2'], $fBiasa);
        $worksheet1->write_string($baris, 14, $row['payment_code'], $fBiasa);
        $worksheet1->write_string($baris, 15, $getssb['CPM_SSB_AKUMULASI'], $fBiasa);
        $worksheet1->write_string($baris, 16, $row['BPHTB_DIBAYAR'], $fBiasa);
        $totalbayar += $row['BPHTB_DIBAYAR'];
        $baris++;
    }
    $judul_jumlah = "Total Bayar";
    $worksheet1->write_string($baris, 2, $judul_jumlah, $fBiasa);
    $worksheet1->write_number($baris, 16, $totalbayar, $fDtlNumber);
}


$workbook->close();
