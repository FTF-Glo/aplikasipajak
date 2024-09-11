<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}

class BPHTBService extends modBPHTBApprover
{

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    function getAUTHOR($nop)
    {
        global $data, $DBLink;

        $query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '" . $nop . "'";

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            return $data->data[$i]->CPM_SSB_AUTHOR;
        }
        return "Tidak Ditemukan";
    }

    function getNOP($author)
    {
        global $data, $DBLink;

        $query = "SELECT CPM_OP_NOMOR 
                  FROM cppmod_ssb_doc doc INNER JOIN cppmod_ssb_tranmain tran
                  ON tran.CPM_TRAN_SSB_ID = doc.CPM_SSB_ID
                  WHERE 
                  doc.CPM_SSB_AUTHOR like '%" . $author . "%' and
                  tran.CPM_TRAN_STATUS='5'";

        $res = mysqli_query($DBLink, $query);
        $arr = array();
        while ($d = mysqli_fetch_object($res)) {
            $arr[] = $d->CPM_OP_NOMOR;
        }
        return "'" . implode("','", $arr) . "'";
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

    function select_option_kelurahan($value, $sts, $kel)
    {
        global $DBLink;
        $qry = "SELECT cppmod_tax_kelurahan2.* FROM cppmod_tax_kelurahan2
                JOIN cppmod_tax_kecamatan2 ON cppmod_tax_kecamatan2.CPC_TKC_ID = cppmod_tax_kelurahan2.CPC_TKL_KCID
                WHERE cppmod_tax_kecamatan2.CPC_TKC_KECAMATAN ='{$value}'";
        $res = mysqli_query($DBLink, $qry);

        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $return = "<select id=\"kelurahan2-{$sts}\" class=\"form-control\">";
        $return .= "<option value=''>--Pilih Kelurahan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            $selected = '';

            $kel = strtoupper(str_replace(" ", "", $kel));
            $kelrhn = strtoupper(str_replace(" ", "", $row['CPC_TKL_KELURAHAN']));

            if ($kel == $kelrhn) {
                $selected = 'selected';
            }
            $return .= "<option value=\"{$row['CPC_TKL_KELURAHAN']}\" {$selected}>{$row['CPC_TKL_KELURAHAN']}</option>";
        }
        $return .= "</select>";
        return $return;
    }

    function select_option_kecamatan($value, $sts)
    {
        global $DBLink;
        // var_dump($value, $sts);
        // die;
        $qry = "SELECT * FROM cppmod_tax_kecamatan2";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $return = "<select id=\"kecamatan2-{$sts}\" class=\"form-control\" onchange=\"changekecmatan(this,$sts)\">";
        $return .= "<option value=''>--Pilih Kecamatan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            $selected = '';

            $value = strtoupper(str_replace(" ", "", $value));
            $kecmtn = strtoupper(str_replace(" ", "", $row['CPC_TKC_KECAMATAN']));

            if ($value == $kecmtn) {
                $selected = 'selected';
            }
            $return .= "<option value=\"{$row['CPC_TKC_KECAMATAN']}\" {$selected}>{$row['CPC_TKC_KECAMATAN']}</option>";
        }
        $return .= "</select>";
        return $return;
    }

    function getSPPTInfo($noktp, $nop, &$paid)
    {
        global $a;

        $iErrCode = 0;
        $a = $a;
        //LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        $paid = "";
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $paid = $data->data[$i]->PAYMENT_PAID;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE op_nomor = '" . $nop . "'";
        $paid = "";
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {

            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $paid = $data->data[$i]->PAYMENT_PAID;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        SCANPayment_CloseDB($LDBLink);
        return "Tidak Ditemukan";
    }

    private $jml_transaksi = 0;
    private $total_transaksi = 0;
    private $jml_transaksi_select = 0;
    private $total_transaksi_select = 0;

    function getDocumentPembayaran($sts, &$dat)
    {
        global $json, $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $nm_wp, $kd_byar;
        $srcTxt = $find;



        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbNameSW = $this->getConfigValue($a, 'BPHTBDBNAMESW');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        $tw = $this->getConfigValue($a, 'TENGGAT_WAKTU');

        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }
        # code...
        $where = " WHERE PAYMENT_FLAG = 1"; #pembayaran            
        if ($sts == 9) {
            $where = " WHERE PAYMENT_FLAG = 0 AND SDELETE=  0 ";
            if ($nm_wp != '') {
                $where .= " AND (wp_nama LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' OR op_nomor LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' 
                            OR wp_noktp LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%')";
            }
        }
        if ($sts == 1 || $sts == 3) {
            $where .= " AND payment_code like '%" . mysqli_real_escape_string($LDBLink, $kd_byar) . "%'";
        }
        if ($sts == 11)
            $where = " WHERE bphtb_dibayar = 0";

        if ($sts == 10) {

            // $where .= " AND DATE(expired_date) < '".date('Y-m-d')."'";
            $where = " A JOIN $DbNameSW.CPPMOD_SSB_DOC B ON A.id_switching=B.CPM_SSB_ID WHERE (PAYMENT_FLAG <> 1 OR ISNULL(PAYMENT_FLAG)) AND DATE_ADD(expired_date, INTERVAL $tw DAY) < NOW()";
            if ($nm_wp != '') {
                $where .= " AND (wp_nama LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' OR op_nomor LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' 
                            OR wp_noktp LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%')";
            }
        }

        if ($kec != "")
            $where .= " AND wp_kecamatan LIKE '" . mysqli_escape_string($LDBLink, $kec) . "%'";
        if ($kd_byar != "")
            $where .= " AND payment_code LIKE '%" . mysqli_escape_string($LDBLink, $kd_byar) . "%'";

        if ($kel != "")
            $where .= " AND wp_kelurahan LIKE '" . mysqli_escape_string($LDBLink, $kel) . "%'";


        if ($srcTxt != "")
            $where .= " AND wp_nama LIKE '" . mysqli_escape_string($LDBLink, $srcTxt) . "%'";
        if ($sts == 11 && $nm_wp != '') {
            // $where .= " AND wp_nama LIKE '" . mysqli_escape_string($LDBLink, $nm_wp) . "%'";
            $where .= " AND (wp_nama LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' OR op_nomor LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%' 
                            OR wp_noktp LIKE '%" . mysqli_real_escape_string($LDBLink, $nm_wp) . "%')";
        }
        if ($sts == 2) {
            if ($find_notaris != "")
                $where .= " AND (wp_nama like '%" . mysqli_real_escape_string($LDBLink, $find_notaris) . "%')";
        } else {
            if ($find_notaris != "")
                $where .= " AND (author like '%" . mysqli_real_escape_string($LDBLink, $find_notaris) . "%')";
        }
        if ($tgl1 != "" && $tgl2 != "")
            $where .= " AND  (DATE(payment_paid) between '" . mysqli_escape_string($LDBLink, $tgl1) . "' and '" . mysqli_escape_string($LDBLink, $tgl2) . "')";
        elseif ($tgl1 != "")
            $where .= " AND  (DATE(payment_paid) = '" . mysqli_real_escape_string($LDBLink, $tgl1) . "')";
        elseif ($tgl2 != "")
            $where .= " AND  (DATE(payment_paid) = '" . mysqli_real_escape_string($LDBLink, $tgl2) . "')";

        if ($sts == 1) { #berdasarkan user
            $query = "SELECT *,count(id_ssb) as jml_transaksi, sum(bphtb_dibayar) as jml_nilai_transaksi
                     FROM $DbTable $where GROUP BY bphtb_notaris 
                     ORDER BY saved_Date DESC LIMIT " . $this->page . "," . $this->perpage;
            $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where} GROUP BY bphtb_notaris";
        } else { #per user
            $query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC LIMIT " . $this->page . "," . $this->perpage;
            $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where}";
        }

        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return false;
        }
        #untuk total                 
        $dataSelect = mysqli_query($LDBLink, $qry);
        if ($ds = mysqli_fetch_assoc($dataSelect)) {
            if ($sts == 1) { #berdasarkan user
                $this->totalRows = mysqli_num_rows($dataSelect);
            } else { #per user
                $this->totalRows = $ds['TOTALROWS'];
            }

            $this->jml_transaksi_select = $ds['TOTALROWS'];
            $this->total_transaksi_select = $ds['TOTALBAYAR'];
        }

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

        $hal = $this->page + 1;
        for ($i = 0; $i < count($data->data); $i++) {

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td class=$class align=right>" . ($hal) . ".</td>";
            if ($sts == 1) { #berdasarkan user
                $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->author . "</td>";
                $HTML .= "<td class=$class align=right>" . $data->data[$i]->jml_transaksi . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->jml_nilai_transaksi, 0, ",", ".") . "</td>";
            } else { #per user
                if ($sts == 6) {
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->author . "</td>";

                    $HTML .= "<td class=$class>" . $data->data[$i]->payment_paid . "</td>";
                    $HTML .= "<td class=$class>" . number_format($data->data[$i]->bphtb_dibayar, 0, ",", ".") . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_kecamatan . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_kelurahan . "</td>";
                    // Belum bayar by Taufiq
                } else if ($sts == 9 || $sts == 10 || $sts == 11) {
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
                    $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->bphtb_dibayar, 0, ",", ".") . "</td>";
                    if ($sts != 9) {
                        # code...
                        if ($sts == 10) {
                            # code...
                            $HTML .= "<td class=$class>" . $data->data[$i]->expired_date . "</td>";
                        } else {
                            $HTML .= "<td class=$class>" . $data->data[$i]->payment_paid . "</td>";
                        }
                    }
                    if ($sts == 9 || $sts == 10 || $sts == 11) {
                        $HTML .= "<td class=$class>" . $data->data[$i]->payment_code . "</td>";
                    }
                    $HTML .= "<td class=$class>" . date('Y-m-d', time($data->data[$i]->saved_date)) . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->author . "</td>";
                } else {
                    $HTML .= "<td class=$class>" . $data->data[$i]->id_ssb . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->payment_paid . "</td>";
                    $HTML .= "<td class=$class>" . number_format($data->data[$i]->bphtb_dibayar, 0, ",", ".") . "</td>";
                }
            }
            if (($sts == 2) || ($sts == 4) || ($sts == 5)) {

                $HTML .= "<td class=$class>" . $data->data[$i]->author . "</td>";
            }
            if ($sts == 2) {
                $HTML .= "<td class=$class>" . $data->data[$i]->payment_code . "</td>";
            }
            $HTML .= "</tr></div>";
            $hal++;
        }

        $dat = $HTML;
        #total transaksi dan penerimaan
        $query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1 
                    AND YEAR(saved_date) = YEAR(NOW())";

        $dataTotal = mysqli_query($LDBLink, $query);
        if ($d = mysqli_fetch_object($dataTotal)) {
            $this->jml_transaksi = $d->jml_transaksi;
            $this->total_transaksi = $d->total_transaksi;
        }
        return true;
    }

    function getDocumentSudahBayar($sts, &$dat)
    {
        global $DBLink, $json, $a, $m, $find_notaris, $tgl1, $tgl2, $kec, $kel, $kd_byar;
        $where = "";
        if ($tgl1 != "" && $tgl2 != "")
            $where .= " AND  (date(payment_paid) between '" . $tgl1 . "' and '" . $tgl2 . "')";
        elseif ($tgl1 != "")
            $where .= " AND  (date(payment_paid) = '" . $tgl1 . "')";
        elseif ($tgl2 != "")
            $where .= " AND  (date(payment_paid) = '" . $tgl2 . "')";

        if ($kec != '') {
            $where .= " AND  op_kecamatan like '%" . $kec . "%'";
        }
        if ($kel != '') {
            $where .= " AND  op_kelurahan like '%" . $kel . "%'";
        }
        if ($find_notaris != '') {
            $where .= " AND  (wp_nama like '%" . $find_notaris . "%' OR wp_noktp like '%" . $find_notaris . "%' OR op_nomor like '%" . $find_notaris . "%')";
        }
        if ($kd_byar != '') {
            $where .= " AND  payment_code like '%" . $kd_byar . "%'";
        }

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        // var_dump($DbTable);
        // die;
        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }
        $HTML = "";

        $query_get_data_backend = "select * from $DbTable where payment_flag=1 " . $where . " ORDER BY payment_paid ASC LIMIT " . $this->page . "," . $this->perpage;
        $qry = "select count(*) TOTALROWS, SUM(bphtb_collectible) TOTALBAYAR from $DbTable where payment_flag=1 " . $where;
        // var_dump($query_get_data_backend);
        // exit;
        $res = mysqli_query($LDBLink, $query_get_data_backend);
        $data = $json->decode($this->mysql2json($res, "data"));
        $hal = 1;
        for ($i = 0; $i < count($data->data); $i++) {
            if ($data->data[$i]->payment_bank_code == 1) {
                $bank = 'Bank Lampung';
            } elseif ($data->data[$i]->payment_bank_code == 2) {
                $bank = 'Pos Indonesia';
            }
            // elseif($data->data[$i]->payment_bank_code == 9996471){
            //     $bank = 'Bank Rakyat Indonesia';
            // }elseif($data->data[$i]->payment_bank_code == 1801999){
            //     $bank = 'Bank Mandiri';

            // }
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td class=$class align=right>" . ($hal) . ".</td>";
            $HTML .= "<td class=$class align=right data-payment-paid=\"" . substr($data->data[$i]->payment_paid, 0, 10) . "\">" . $data->data[$i]->op_nomor . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->wp_nama . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->op_letak . ".</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->op_kelurahan . ".</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->op_kecamatan . ".</td>";
            $ssbdoc = $this->funcgetssbdoc($data->data[$i]->id_switching, 5);
            $ssbdocverif = $this->funcgetssbdoc($data->data[$i]->id_switching, 3);
            // var_dump($data->data[$i]->payment_offline_user_id);
            // die;
            $HTML .= "<td class=$class align=right>" . $ssbdocverif['CPM_SSB_CREATED'] . "</td>";
            $HTML .= "<td class=$class align=right>" . $ssbdocverif['CPM_TRAN_DATE'] . "</td>";
            $HTML .= "<td class=$class align=right>" . $ssbdoc['CPM_TRAN_OPR_DISPENDA_1'] . "</td>";
            $HTML .= "<td class=$class align=right>" . $ssbdoc['CPM_TRAN_DATE'] . "</td>";
            $HTML .= "<td class=$class align=right>" . $ssbdoc['CPM_TRAN_OPR_DISPENDA_2'] . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->payment_offline_user_id . "</td>";

            $HTML .= "<td class=$class align=right>" . $data->data[$i]->payment_code . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->payment_paid . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($ssbdoc['CPM_OP_HARGA'], 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->bphtb_collectible, 0, ",", ".") . "</td>";
            $HTML .= "</tr></div>";
            $hal++;
        }
        
        $dataSelect = mysqli_query($LDBLink, $qry);
        if ($ds = mysqli_fetch_assoc($dataSelect)) {
            $this->totalRows = $ds['TOTALROWS'];
            $this->jml_transaksi_select = $ds['TOTALROWS'];
            $this->total_transaksi_select = $ds['TOTALBAYAR'];
        }
        $dat = $HTML;

        $query = "SELECT SUM(bphtb_collectible) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1 AND YEAR(saved_date) = YEAR(NOW())";

        $dataTotal = mysqli_query($LDBLink, $query);
        if ($d = mysqli_fetch_object($dataTotal)) {
            $this->jml_transaksi = $d->jml_transaksi;
            $this->total_transaksi = $d->total_transaksi;
        }
        return true;
    }

    function getDocumentApproval($sts, &$dat)
    {
        global $DBLink, $json, $a, $m, $find_notaris, $tgl1, $tgl2, $kec, $kel;

        $where = " WHERE b.CPM_TRAN_STATUS = '5'"; #disetujui
        if ($sts == 5) {
            if ($find_notaris != "") {
                $where .= " AND (a.CPM_WP_NAMA like '%" . $find_notaris . "%' OR a.CPM_OP_NOMOR like '%" . $find_notaris . "%' OR a.CPM_WP_NOKTP like '%" . $find_notaris . "%')";
            }
        } else {
            if ($find_notaris != "") {
                $where .= " AND (b.CPM_TRAN_OPR_NOTARIS like '%" . $find_notaris . "%')";
            }
        }
        $where_ssb = '';
        if ($sts == 8) {
            if ($tgl1 != "" && $tgl2 != "")
                $where_ssb .= " AND  (date(payment_paid) between '" . $tgl1 . "' and '" . $tgl2 . "')";
            elseif ($tgl1 != "")
                $where_ssb .= " AND  (date(payment_paid) = '" . $tgl1 . "')";
            elseif ($tgl2 != "")
                $where_ssb .= " AND  (date(payment_paid) = '" . $tgl2 . "')";
        } else {
            if ($tgl1 != "" && $tgl2 != "")
                $where .= " AND  (date(b.CPM_TRAN_DATE) between '" . $tgl1 . "' and '" . $tgl2 . "')";
            elseif ($tgl1 != "")
                $where .= " AND  (date(b.CPM_TRAN_DATE) = '" . $tgl1 . "')";
            elseif ($tgl2 != "")
                $where .= " AND  (date(b.CPM_TRAN_DATE) = '" . $tgl2 . "')";
        }
        $kec = str_replace(" ", '', $kec);
        $kel = str_replace(" ", '', $kel);
        if ($kec != '') {
            $where .= " AND  REPLACE(a.CPM_OP_KECAMATAN,\" \",\"\") like '%" . $kec . "%'";
        }
        if ($kel != '') {
            $where .= " AND  REPLACE(a.CPM_OP_KELURAHAN,\" \",\"\") like '%" . $kel . "%'";
        }
        // var_dump($kec);
        if ($sts == 3) { #berdasarkan user
            $query = "SELECT *,count(a.CPM_SSB_ID) as jml_transaksi, sum(a.CPM_OP_BPHTB_TU) as jml_nilai_transaksi
                     FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID
                     $where GROUP BY b.CPM_TRAN_OPR_NOTARIS 
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

            $qry = "SELECT sum(a.CPM_OP_BPHTB_TU) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where} GROUP BY b.CPM_TRAN_OPR_NOTARIS ";
        } else if ($sts == 4) { #per user
            $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;


            $qry = "SELECT sum(a.CPM_OP_BPHTB_TU) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where}";
        } else if ($sts == 5) { #siap bayar per user
            $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
            $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
            $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
            $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
            $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

            $iErrCode = 0;
            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
            if ($iErrCode != 0) {
                $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
                if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
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
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

            $qry = "SELECT sum(a.CPM_OP_BPHTB_TU) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where} and CPM_TRAN_SSB_ID in $whereIn ";
            // }
        } else if ($sts == 8) { #siap bayar per user
            $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
            $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
            $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
            $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
            $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

            $iErrCode = 0;
            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
            if ($iErrCode != 0) {
                $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
                if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
                exit(1);
            }

            $query_get_data_backend = "select id_switching from $DbTable where payment_flag=1 " . $where_ssb;
            $resBE = mysqli_query($LDBLink, $query_get_data_backend);
            $whereIn = array();
            while ($dtBE = mysqli_fetch_array($resBE)) {
                $whereIn[] = $dtBE['id_switching'];
            }
            $whereIn = "('" . implode("','", $whereIn) . "')";
            $query = "SELECT * , b.CPM_TRAN_DATE as VERIFIKASI_DATE FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

            $qry = "SELECT sum(a.CPM_BPHTB_BAYAR) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where} and CPM_TRAN_SSB_ID in $whereIn ";
        }
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($DBLink) . "' );</script>";
            return false;
        }
        #untuk total
        $dataSelect = mysqli_query($DBLink, $qry);
        if ($ds = mysqli_fetch_assoc($dataSelect)) {
            if ($sts == 3) { #berdasarkan user
                $this->totalRows = mysqli_num_rows($dataSelect);
            } else { #per user
                $this->totalRows = $ds['TOTALROWS'];
            }
            $this->jml_transaksi_select = $ds['TOTALROWS'];
            $this->total_transaksi_select = $ds['TOTALBAYAR'];
        }

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

        $hal = $this->page + 1;
        for ($i = 0; $i < count($data->data); $i++) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td class=$class align=right>" . ($hal) . ".</td>";
            if ($sts == 3) {
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NOMOR . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_TRAN_OPR_NOTARIS . "</td>";
                $HTML .= "<td class=$class align=right>" . $data->data[$i]->jml_transaksi . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->jml_nilai_transaksi, 0, ",", ".") . "</td>";
            } else {
                if ($sts != 5 && $sts != 8) {
                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_SSB_ID . "</td>";
                }
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NOMOR . "</td>";

                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NAMA . "</td>";
                if ($sts == 8) {

                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KELURAHAN . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
                }
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_SSB_CREATED . "</td>";

                $verifydate = ($sts == 5) ? $data->data[$i]->VERIFIKASI_DATE : $data->data[$i]->CPM_TRAN_DATE;

                $HTML .= "<td class=$class>" . $verifydate . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>";

                $claimdate = ($sts == 5) ? $data->data[$i]->CPM_TRAN_DATE : $data->data[$i]->CPM_TRAN_CLAIM_DATETIME;

                $ssbdoc = $this->funcgetssbdoc($data->data[$i]->CPM_SSB_ID, 5);
                $HTML .= "<td class=$class>" . $ssbdoc['CPM_SSB_CREATED'] . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 . "</td>";
                if ($sts == 5 || $sts == 8) {
                    $payment_code = $this->get_code_payment($data->data[$i]->CPM_SSB_ID);
                    $get_ssb = $this->get_ssb($data->data[$i]->CPM_SSB_ID);
                    $HTML .= "<td class=$class>" . $payment_code . "</td>";
                    # code...
                }
                $bphtb_terutang = $data->data[$i]->CPM_BPHTB_BAYAR;



                $HTML .= "<td class=$class align=right>" . number_format($bphtb_terutang, 0, ",", ".") . "</td>";
            }
            if (($sts == 4) || ($sts == 5)) {

                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_TRAN_OPR_NOTARIS . "</td>";
            }
            $HTML .= "</tr></div>";
            $hal++;
        }

        $dat = $HTML;

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }
        #total transaksi dan penerimaan
        $query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1 AND YEAR(saved_date) = YEAR(NOW())";

        $dataTotal = mysqli_query($LDBLink, $query);
        if ($d = mysqli_fetch_object($dataTotal)) {
            $this->jml_transaksi = $d->jml_transaksi;
            $this->total_transaksi = $d->total_transaksi;
        }
        return true;
    }

    public function get_code_payment($id)
    {

        $a = 'aBPHTB';
        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }

        $query_get_data_backend = "select id_switching,payment_code from $DbTable where id_switching ='{$id}'";
        $resBE = mysqli_query($LDBLink, $query_get_data_backend);
        $payment_code = '';
        while ($dtBE = mysqli_fetch_array($resBE)) {
            $payment_code = $dtBE['payment_code'];
        }
        return $payment_code;
    }

    public function get_ssb($id)
    {

        $a = 'aBPHTB';
        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

        $iErrCode = 0;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }

        $query_get_data_backend = "select id_switching,payment_code,payment_paid from $DbTable where id_switching ='{$id}'";
        $resBE = mysqli_query($LDBLink, $query_get_data_backend);
        $return = array();
        while ($dtBE = mysqli_fetch_array($resBE)) {
            $return['payment_paid'] = $dtBE['payment_paid'];
        }
        return $return;
    }

    public function funcgetssbdoc($idssb, $sts_trnmain)
    {
        global $DBLink;

        $query = "SELECT * FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                 a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID WHERE a.`CPM_SSB_ID` = \"$idssb\" 
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
            $dataRow['CPM_WP_KELURAHAN'] = $row['CPM_WP_KELURAHAN'];
            $dataRow['CPM_WP_KECAMATAN'] = $row['CPM_WP_KECAMATAN'];
            $dataRow['CPM_OP_KELURAHAN'] = $row['CPM_OP_KELURAHAN'];
            $dataRow['CPM_OP_KECAMATAN'] = $row['CPM_OP_KECAMATAN'];
            $dataRow['CPM_OP_HARGA'] = $row['CPM_OP_HARGA'];

            $dataRow['CPM_SSB_CREATED'] = $row['CPM_SSB_CREATED'];
            $dataRow['CPM_TRAN_DATE'] = $row['CPM_TRAN_DATE'];
            $dataRow['CPM_TRAN_OPR_DISPENDA_1'] = $row['CPM_TRAN_OPR_DISPENDA_1'];
            $dataRow['CPM_TRAN_CLAIM_DATETIME'] = $row['CPM_TRAN_CLAIM_DATETIME'];
            $dataRow['CPM_TRAN_OPR_DISPENDA_2'] = $row['CPM_TRAN_OPR_DISPENDA_2'];
            $dataRow['CPM_OP_BPHTB_TU'] = $row['CPM_OP_BPHTB_TU'];
            $dataRow['CPM_TRAN_OPR_NOTARIS'] = $row['CPM_TRAN_OPR_NOTARIS'];
            $dataRow['CPM_BPHTB_BAYAR'] = $row['CPM_BPHTB_BAYAR'];
        }
        return $dataRow;
    }

    public function getDocumentLaporan($sts, &$dat)
    {
        global $json, $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $jh;

        $srcTxt2 = @isset($jh) ? $jh : "";
        // $srcTgl1 = empty($tgl1)?date('Y-m-d'):$tgl1;
        $srcTgl1 = empty($tgl1) ? date('Y-m-d') : $tgl1;
        // $srcTgl2 = empty($tgl2)?date('Y-m-d'):$tgl2;
        $srcTgl2 = empty($tgl2) ? date('Y-m-d') : $tgl2;

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        $tw = $this->getConfigValue($a, 'TENGGAT_WAKTU');
        $DbNameSW = $this->getConfigValue($a, 'BPHTBDBNAMESW');
        // $where = " WHERE PAYMENT_FLAG = 1";
        $where = "";
        $where2 = "";
        if ($srcTgl1 != "") $where .= " AND $DbName.ssb.payment_paid >= '" . $srcTgl1 . " 00:00:00' ";
        if ($srcTgl2 != "") $where .= " AND $DbName.ssb.payment_paid <= '" . $srcTgl2 . " 23:59:59'";
        if ($srcTxt2 != "") $where2 .= " AND $DbNameSW.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '" . $srcTxt2 . "'";
        $iErrCode = 0;

        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
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
        // ORDER BY $DbName.ssb.id_ssb DESC LIMIT ".$this->page.",".$this->perpage; 
        // var_dump($query);
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }

        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        // die(var_dump($d));
        $data = $d;
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        $ss = true;
        $tw = 0;

        $total_bayar = 0;
        $total_denda = 0;
        $total_seluruh = 0;
        $total_njop = 0;
        $total_njopperm = 0;
        $total_trans = 0;
        $berkas = count($data->data);

        for ($i = 0; $i < count($data->data); $i++) {
            $dataNotaris = $this->getDataNotaris($data->data[$i]->CPM_SSB_AUTHOR);
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td class=$class>" . ($i + 1) . "</td>";
            // $HTML .= "<td class=$class>".$data->data[$i]->id_ssb."</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->wp_alamat . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_NMR_SERTIFIKAT . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->payment_code . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->bphtb_collectible, 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class align=center>" . number_format($data->data[$i]->CPM_DENDA, 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_JENIS_HAK . "</td>";

            $HTML .= "</tr></div>";
            $tw++;
            $total_bayar = $total_bayar + $data->data[$i]->bphtb_collectible;
            $total_denda = $total_denda + $data->data[$i]->CPM_DENDA;
            $total_njop = $total_njop + (($data->data[$i]->CPM_OP_LUAS_TANAH * $data->data[$i]->CPM_OP_NJOP_TANAH) + ($data->data[$i]->CPM_OP_LUAS_BANGUN * $data->data[$i]->CPM_OP_NJOP_BANGUN));
            $total_njopperm = $total_njopperm + $data->data[$i]->CPM_OP_NJOP_TANAH;
            $total_trans = $total_trans + $data->data[$i]->CPM_OP_HARGA;
        }
        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=$class colspan=10>&nbsp;</td>";
        $HTML .= "</tr></div>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=tdheader colspan=\"5\"></td>";
        $HTML .= "<td class=tdheader>TOTAL Berkas</td>";
        $HTML .= "<td class=tdheader align=center>$berkas</td>";
        $HTML .= "<td class=tdheader colspan=\"4\"></td>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=$class colspan=\"10\">&nbsp;</td>";
        $HTML .= "</tr></div>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=tdheader colspan=\"5\"></td>";
        $HTML .= "<td class=tdheader>TOTAL</td>";
        $HTML .= "<td class=tdheader align=right>" . number_format($total_bayar, 0, ",", ".") . "</td>";
        $HTML .= "<td class=tdheader align=center>" . number_format($total_denda, 0, ",", ".") . "</td>";
        $HTML .= "<td class=tdheader colspan=\"2\"></td>";
        $HTML .= "</tr></div>";
        $dat = $HTML;

        return true;
    }

    function getDataNotaris($id)
    {
        global $DBLink;

        $qry = "select * from tbl_reg_user_notaris where userId = '" . $id . "'";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $data['almt_jalan'] = $row['almt_jalan'];
        }

        return $data;
    }

    public function headerBerdasarUser($sts)
    {
        global $a, $find, $find_notaris, $tgl1, $tgl2, $kd_byar, $kel, $kec;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "'}");
        $HTML = "<form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\">
                <style>
                    .form-filtering {
                        background-color: #fff;
                        padding: 20px 20px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

                    }
                </style>
                <div class=\"col-md-12\" style=\"margin-bottom: 10px;margin-top:10px\">
                    <div class=\"row\">
                        <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\">
                            <button class=\"btn btn-primary\" type=\"button\" style=\"border-radius:8px; margin-right:30px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-$sts\">
                                Filter Data
                            </button>
                        </div>

                        <div class=\"col-12\"> 
                            <div class=\"collapse\" id=\"collapsFilter-$sts\">
                                <div class=\"form-filtering\">
                                    <div class=\"row \">

                                        <div class=\"form-group col-md-4\" >
                                            <label>User</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-notaris-$sts\" value'$find_notaris'/>
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>Kode Bayar</label>
                                            <input type=\"text\"  class=\"form-control\" id=\"src-kdbyr-$sts\" value='$kd_byar'/> 
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>Kecamatan</label>
                                            " . $this->select_option_kecamatan($kec, $sts) . "
                                        </div>
                                        <div class=\"form-group col-md-4\" >
                                            <label>Kelurahan</label>
                                            " . $this->select_option_kelurahan($kec, $sts, $kel) . "
                                        </div>

                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Bayar Awal </label>
                                            <div style=\"display: flex; align-items: center;\">
                                            <input  class=\"form-control\" type=\"text\" id=\"src-tgl1-$sts\" size=\"20\" value='$tgl1'>
                                                <p style=\"margin-left:10px; margin-bottom: 0rem;\">s/d</p>
                                            </div>
                                        </div>

                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Bayar Akhir</label>
                                            <div>
                                                <input  class=\"form-control\" type=\"text\" id=\"src-tgl2-$sts\" size=\"20\" value='$tgl2'>
                                                <input type=\"hidden\" id=\"src-tgl2-$sts\" size=\"20\" value='$tgl2'> 
                                            </div>
                                        </div>

                                        <div class=\" form-group col-md-12\"> 
                                            <input type=\"button\" class=\"btn btn-success\"  value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />
                                            <input type=\"button\" class=\"btn btn-success\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('" . $j . "');\"/>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> 
                    
                    </div>
                </div>
            </form>
        ";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No. </td>";
        $HTML .= "<td class=tdheader>NOP </td>";
        $HTML .= "<td class=tdheader>Nama User </td>";
        $HTML .= "<td class=tdheader>Jumlah Transaksi </td>";
        $HTML .= "<td class=tdheader>Jumlah Rupiah Transaksi </td>";
        $HTML .= "</tr>";

        if ($sts == 1 || $sts == 2) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 3 || $sts == 4) {
            if ($this->getDocumentApproval($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        } else {
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerPerUser($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $kd_byar;;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "',kec:'" . $kec . "',kel:'" . $kel . "',kd_byar:'" . $kd_byar . "'}");

        $link = "view/BPHTB/monitoring/svc-list-notaris.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'4'}");
        $HTML = "

            <script>
            jQuery(document).ready(function($){
                $('#src-notaris-" . $sts . "').autocomplete({source:'" . $link . "', minLength:2});
            });</script>

                <form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
            <style>
                .form-filtering {
                    background-color: #fff;
                    padding: 20px 20px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

                }
            </style>
            <div class=\"col-md-12\" style=\"margin-bottom: 10px;margin-top:10px\">
                <div class=\"row\">
                    <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\">
                        <button class=\"btn btn-primary\" type=\"button\" style=\"border-radius:8px; margin-right:30px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-$sts\">
                            Filter Data
                        </button>
                    </div>

                    <div class=\"col-12\"> 
                        <div class=\"collapse\" id=\"collapsFilter-$sts\">
                            <div class=\"form-filtering\">
                                <div class=\"row \">

                                    <div class=\"form-group col-md-3\" >";
                                    if ($sts != 8 && $sts != 5) {
                                        $HTML .= "  <label>Nama User</label>";
                                    }else{
                                        $HTML .= "  <label>Nama WP</label>";
                                    }
                                    $HTML .= "<input type=\"text\" class=\"form-control\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\"/>
                                        <input type=\"hidden\" id=\"src-approved-$sts\"/>
                                    </div>

                                    <div class=\"form-group col-md-3\">
                                        <label>Tanggal Bayar Awal </label>
                                        <div style=\"display: flex; align-items: center;\">
                                        <input  class=\"form-control\" type=\"text\" id=\"src-tgl1-$sts\" value='$tgl1'>
                                            <p style=\"margin-left:10px; margin-bottom: 0rem;\">s/d</p>
                                        </div>
                                    </div>

                                  
                                    <div class=\"form-group col-md-3\">
                                        <label>Tanggal Bayar Akhir</label>
                                        <div>
                                            <input  class=\"form-control\" type=\"text\" id=\"src-tgl2-$sts\" value='$tgl2'>
                                            <input type=\"hidden\" id=\"src-tgl2-$sts\" value='$tgl2'> 
                                        </div>
                                    </div> ";

                                    if ($sts == 8) { 
                                        $HTML .= "<div class=\"form-group col-md-3\" >
                                                    <label>Kode Bayar</label>
                                                    <input class=\"form-control\" type=\"text\" id=\"src-kdbyr-$sts\" value='$kd_byar' name=\"src-kdbyr\"/>
                                                </div>
                                                <div class=\"form-group col-md-3\" >
                                                    <label>Kecamatan</label>
                                                    " . $this->select_option_kecamatan($kec, $sts) . "
                                                </div>
                                                <div class=\"form-group col-md-3\" >
                                                    <label>Kelurahan</label>
                                                    " . $this->select_option_kelurahan($kec, $sts) . "
                                                </div>";
                                    }


                                    $HTML .= " <div class=\" form-group col-md-12\"> 
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('" . $j . "');\"/>";
                                        if ($sts == 8) { 
                                            $HTML .= "  <input type=\"button\" class=\"btn btn-success\" value=\"Cetak DBH\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS2('" . $j . "');\"/>";
                                        }
                        $HTML .= " </div>

                                </div>
                            </div>
                        </div>
                    </div> 
                
                </div>
            </div>
        </form>";
        $HTML .= "<div class=\"table-responsive\">";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        if ($sts != 5 && $sts != 8) {
            $HTML .= "<td class=tdheader>ID SSPD</td>";
        }
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Nama Wajib Pajak</td>";
        if ($sts == 8) {
            $HTML .= "<td class=tdheader>ALamat OP</td>";
            $HTML .= "<td class=tdheader>Kelurahan</td>";
            $HTML .= "<td class=tdheader>Kecamatan</td>";
        }

        if ($sts == 2) {
            $HTML .= "<td class=tdheader>Tanggal Bayar</td>";
            $HTML .= "<td class=tdheader>Jumlah Pembayaran</td>";
            $HTML .= "<td class=tdheader>User / Notaris</td>";
        } else {
            $HTML .= "<td class=tdheader>Tanggal Input</td>";
            $HTML .= "<td class=tdheader>Tanggal Verifikasi</td>";
            $HTML .= "<td class=tdheader>Petugas Verifikasi</td>";
            $HTML .= "<td class=tdheader>Tanggal Persetujuan</td>";
            $HTML .= "<td class=tdheader>Pejabat Persetujuan</td>";
            if ($sts == 8) {
                $HTML .= "<td class=tdheader>Pembayaran Melalui</td>";
            }
            if ($sts == 5 || $sts == 8) {
                $HTML .= "<td class=tdheader>Kode Bayar</td>";
                if ($sts == 8) {
                    $HTML .= "<td class=tdheader>Tanggal Bayar</td>";
                    $HTML .= "<td class=tdheader>Harga Transaksi</td>";
                }
            }
            $HTML .= "<td class=tdheader>Jumlah Pembayaran</td>";
        }
        if (($sts == 4) || ($sts == 5)) {
            $HTML .= "<td class=tdheader>User / Notaris</td>";
        }
        if ($sts == 2) {
            $HTML .= "<td class=tdheader>Kode Bayar</td>";
            # code...
        }
        $HTML .= "</tr>";

        if ($sts == 1 || $sts == 2) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 3 || $sts == 4 || $sts == 5) {
            if ($this->getDocumentApproval($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 8) {
            if ($this->getDocumentSudahBayar($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>";
        $HTML .= "</div>";
        return $HTML;
    }

    public function headerRekapPerDesa($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','kec':'" . $kec . "' ,'kel':'" . $kel . "'}");
        // echo "$j";
        // if($kec == true){
        //     echo "string";
        // }
        // ini
        $link = "view/BPHTB/monitoring/svc-list-kecamatan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $link2 = "view/BPHTB/monitoring/svc-list-kelurahan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $HTML = "
                <form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
                <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" 
            onclick=\"printToXLS('" . $j . "');\"/>&nbsp;";
        // Seleksi Berdasarkan 
        //                     <b>Kecamatan</b> : 
        //                     <select name=\"kecamatan\" id=\"kecamatan\">
        //                     </select>

        //                     <b>Desa</b> : 
        //                     <select name=\"kelurahan\" id=\"kelurahan\">
        //                     </select>
        $HTML .= "
                    <b>Kecamatan</b> :
                    
                    <b>Desa</b> : 
                    <input type =\"text\" name=\"kelurahan2\" id=\"kelurahan2\" placeholder=\"Desa\">

                            <input type=\"hidden\" id=\"src-approved-$sts\"/>                        
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" /></form>";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Nama Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Notaris</td>";
        $HTML .= "<td class=tdheader>Tanggal Pembayaran</td>";
        $HTML .= "<td class=tdheader>Jumlah Pembayaran</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Desa</td>";


        $HTML .= "</tr>";

        if ($sts == 6) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>";
        $HTML .= "<script>
                    $(\"select#kecamatan\").change(function () {
                        showKelurahan();
                    })
                </script>";
        return $HTML;
    }

    // add function headerByme by Taufiq
    public function headerByme($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $nm_wp, $kd_byar;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','kec':'" . $kec . "' ,'kel':'" . $kel . "','wp_nama' : '" . $nm_wp . "'}");
        // echo "$j";
        // if($kec == true){
        //     echo "string";
        // }
        // ini
        $link = "view/BPHTB/monitoring/svc-list-kecamatan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $link2 = "view/BPHTB/monitoring/svc-list-kelurahan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $HTML = "<script>
            </script>
                <form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
                
        if ($sts == 9 || $sts == 11 || $sts == 10) {
            $namawpnopnik = ($sts == 9) ? "Nama WP/NOP/NIK" : "Nama User";
            $HTML .= "
            <style>
                .form-filtering {
                    background-color: #fff;
                    padding: 20px 20px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);

                }
            </style>
            <div class=\"col-md-12\" style=\"margin-bottom: 10px;margin-top:10px\">
                <div class=\"row\">
                    <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\">
                        <button class=\"btn btn-primary\" type=\"button\" style=\"border-radius:8px; margin-right:30px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-$sts\">
                            Filter Data
                        </button>
                    </div>

                    <div class=\"col-12\"> 
                        <div class=\"collapse\" id=\"collapsFilter-$sts\">
                            <div class=\"form-filtering\">
                                <div class=\"row \">

                                    <div class=\"form-group col-md-3\" >
                                        <label>Nama WP/NOP/NIK</label>
                                        <input class=\"form-control\" type=\"text\" id=\"nm_wp-$sts\" value='$nm_wp' name=\"nm_wp\"/> 
                                    </div>

                                    <div class=\"form-group col-md-3\" >
                                        <label>Nama User</label>
                                        <input type=\"text\" class=\"form-control\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\"/>
                                        <input type=\"hidden\" id=\"src-approved-$sts\"/> 
                                    </div>

                                    <div class=\"form-group col-md-3\" >
                                        <label>Kode Bayar</label>
                                        <input  class=\"form-control\" type=\"text\" id=\"src-kdbyr-$sts\" value='$kd_byar'/>
                                    </div>

                      
                                    <div class=\" form-group col-md-12\"> 
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('" . $j . "');\"/>
                                
                                </div>

                            </div>
                        </div>
                    </div>
                </div> 
                
            </div>
        </div> ";
        } else {
            $HTML .= "Seleksi Berdasarkan
                            <input type=\"hidden\" id=\"src-tgl1-$sts\" size=\"20\" value='$tgl1'>
                            <input type=\"hidden\" id=\"src-tgl2-$sts\" size=\"20\" value='$tgl2'> 
                            <input type=\"hidden\" id=\"src-approved-$sts\"/> 
                            <b>Nama User</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/> 
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />";
        }
        $HTML .= "</form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>Nama Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>BPHTB Yang Harus Dibayar</td>";
        if ($sts != 9) {
            if ($sts == 10) {
                $HTML .= "<td class=tdheader>Tanggal Expired</td>";
            } else {
                $HTML .= "<td class=tdheader>Tanggal Pembayaran</td>";
            }
        }
        if ($sts == 9 || $sts == 10 || $sts == 11) {
            # code...
            $HTML .= "<td class=tdheader>Kode Bayar</td>";
        }
        $HTML .= "<td class=tdheader>Tanggal Pelaporan</td>";
        $HTML .= "<td class=tdheader>User</td>";

        $HTML .= "</tr>";

        if ($sts == 9 || $sts == 10 || $sts == 11) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>";
        $HTML .= "<script>
                    $(\"select#kecamatan\").change(function () {
                        showKelurahan();
                    })
                </script>";
        return $HTML;
    }

    function jenishak($js, $sts)
    {
        global $DBLink;

        $texthtml = "<select name=\"jenis_hak\" id=\"jenis_hak\" style=\"height:30px\">";
        $texthtml .= "<option value=\"\" >Pilih Jenis Hak</option>";
        $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
        //echo $qry;exit;
        $res = mysqli_query($DBLink, $qry);

        while ($data = mysqli_fetch_assoc($res)) {
            if ($js == $data['CPM_KD_JENIS_HAK']) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            $texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\" " . $selected . " >" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
        }
        $texthtml .= "</select>";
        return $texthtml;
    }

    public function headerBymeLaporan($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $jh;
        $srcTxt = $jh;
        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','kec':'" . $kec . "' ,'kel':'" . $kel . "',jh:'" . $jh . "'}");

        $link = "view/BPHTB/monitoring/svc-list-notaris.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'4'}");
        $HTML = "

            <script>
            jQuery(document).ready(function($){
                $('#src-notaris-" . $sts . "').autocomplete({source:'" . $link . "', minLength:2});
            });</script>

                <form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        $HTML .= "<table>
                    <tr>
                        <td>
                            <b>Pilih Hari Pembayaran</b>
                        </td>
                        <td> 
                            : 
                        </td>
                        <td> 
                            <input type=\"text\" id=\"src-tgl1-$sts\" name=\"tanggal\" size=\"20\" value='$tgl1' style=\"width:30%\"> s/d <input type=\"text\" id=\"src-tgl2-$sts\" name=\"tanggal2\" size=\"20\" value='$tgl2' style=\"width:30%\">
                        </td>
                        <td>
                            <input type=\"button\" onclick=\"setTabs ($sts);\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />
                        </td>
                        <td>
                            <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLSDaily('" . $j . "');\"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Pilih Jenis Hak</b>
                        </td>
                        <td>
                            :
                        </td>
                        <td style=\"width:250px;\">
                            " . $this->jenishak($jh) . "
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                  </table>
                  </form>";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        // $HTML .= "<td class=tdheader>ID SSPD</td>";
        $HTML .= "<td class=tdheader>Nama Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>WP Alamat</td>";
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>No. Sertifikat</td>";
        $HTML .= "<td class=tdheader>Kode Bayar</td>";
        $HTML .= "<td class=tdheader>Pembayaran</td>";
        $HTML .= "<td class=tdheader>Denda</td>";
        $HTML .= "<td class=tdheader>User</td>";
        $HTML .= "<td class=tdheader>Ket</td>";

        $HTML .= "</tr>";

        if ($sts == 12) { #pembayaran
            if ($this->getDocumentLaporan($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>";
        $HTML .= "<script>
                    $(\"select#kecamatan\").change(function () {
                        showKelurahan();
                    })
                </script>";
        return $HTML;
    }

    public function headerContentRecap($sts)
    {
        global $data, $DBLink, $json, $tgl1, $tgl2, $a, $jh, $tagihan_awal, $tagihan_akhir;
        $srcTxt = $tagihan_awal;
        $srcTxt2 = $jh;
        $srcTxt3 = $tagihan_akhir;
        $srcTxt4 = $tgl1;
        $srcTxt5 = $tgl2;

        if ($srcTxt == '' && $srcTxt2 == '' && $srcTxt3 == '' && $srcTxt4 == '' && $srcTxt5 == '') {
            $srcTxt = date('Y-m-d');
        }

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt4 . "','src2':'" . $srcTxt2 . "','src3':'" . $srcTxt5 . "','src4':'" . $srcTxt . "','src5':'" . $srcTxt3 . "'}");
        $HTML = "<form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<table>
                    <tr>
                        <td>
                            <b>Pilih Hari Pembayaran</b>
                        </td>
                        <td> 
                            : 
                        </td>
                        <td> 
                            <input type=\"text\" id=\"src-tgl1-$sts\" name=\"awal\" size=\"20\" value='$tgl1'>
                            &nbsp;&nbsp; s/d &nbsp;&nbsp;
                            <input type=\"text\" id=\"src-tgl2-$sts\" name=\"akhir\" size=\"20\" value='$tgl2'>
                        </td>
                        <td>
                            <input type=\"button\" value=\"Cari\" onclick=\"setTabs ($sts);\" id=\"btn-src\" name=\"btn-src\" />
                        </td>
                        <td>
                            <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLSRecap('" . $j . "');\"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Nilai Tagihan</b>
                        </td>
                        <td>
                            :
                        </td>
                        <td>
                            <input type=\"tsext\" id=\"tagihan_awal\" name=\"tagihan_awal\" size=\"20\" value='$tagihan_awal' placeholder='contoh: 500000000'>
                            &nbsp;&nbsp; s/d &nbsp;&nbsp;
                            <input type=\"text\" id=\"tagihan_akhir\" name=\"tagihan_akhir\" size=\"20\" value='$tagihan_akhir' placeholder='contoh: 500000000'>
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Pilih Jenis Hak</b>
                        </td>
                        <td>
                            :
                        </td>
                        <td style=\"width:360px;\">
                            " . $this->jenishak($jh) . "
                        </td>
                        <td>
                        </td>
                        <td>
                        </td>
                    </tr>
                  </table> 
                </form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        $HTML .= "<td class=tdheader>Kode</td>";
        $HTML .= "<td class=tdheader>Jenis Perolehan</td>";
        $HTML .= "<td class=tdheader>Harga Transaksi</td>";
        // $HTML .= "<td class=tdheader>Alamat Objek Pajak</td><td class=tdheader width=\"170\">User</td><td class=tdheader width=\"170\">BPHTB yang harus dibayar</td>";
        // $HTML .= "<td class=tdheader width=\"170\">Tanggal Pembayaran</td>";
        // if ($this->getConfigValue($a,'TYPE_PROSES')=='1') $HTML .= "<td class=tdheader width=\"170\">Disetujui</td>";
        $HTML .= "</tr>";

        if ($this->getDocumentRecap($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=3>Data Tidak Ditemukan !</td></tr> ";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerRealisasi($sts)
    {
        global $data, $DBLink, $json, $tgl1, $tgl2, $a, $jh, $tagihan_awal, $tagihan_akhir, $kec;

        $srcTxt = $tagihan_awal;
        $srcTxt2 = $jh;
        $srcTxt3 = $tagihan_akhir;
        $srcTxt4 = $tgl1;
        $srcTxt5 = $tgl2;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','tahun':" . $jh . "}");

        if ($srcTxt == '' && $srcTxt2 == '' && $srcTxt3 == '' && $srcTxt4 == '' && $srcTxt5 == '') {
            $srcTxt = date('Y-m-d');
        }

        if ($tgl1 == '') $tgl1 = date('Y') . "-01-01";
        if ($tgl2 == '') $tgl2 = date('Y-m-d');

        $selectTHN = "<select id='tahun-pajak-14' style='height:30px;width:70px'>";
        for ($i = date('Y'); $i >= 2020; $i--) {
            $selected = ($jh == $i) ? 'selected' : '';
            $selectTHN .= "<option value=$i $selected>$i</option>";
        }
        $selectTHN .= "</select>";

        $HTML = "<form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<table>
                    <tr>
                        <td><b>Tahun Pajak</b> :</td>
                        <td>$selectTHN</td>
                        <td style='padding-left:20px'><b>Tgl Pembayaran</b> :</td>
                        <td> 
                            <input type=\"text\" id=\"src-tgl1-$sts\" name=\"awal\" size=\"15\" value='$tgl1'>
                            &nbsp;&nbsp; s/d &nbsp;&nbsp;
                            <input type=\"text\" id=\"src-tgl2-$sts\" name=\"akhir\" size=\"15\" value='$tgl2'>
                        </td>
                        <td><b>Kecamatan</b> :" . $this->select_option_kecamatan($kec, $sts) . "</td>
                        
                        <td style='padding-left:20px'>
                            <input type=\"button\" value=\"Cari\" onclick=\"setTabs ($sts);\" id=\"btn-src\" name=\"btn-src\" />
                        </td>
                        <td style='padding-left:20px'>
                            <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLSRealisasi('" . $j . "');\"/>
                        </td>
                    </tr>
                  </table> 
                </form>";

        $strtgl1 = date_create($tgl1);
        $strtgl1 = date_format($strtgl1, 'd M Y');
        // var_dump($kec);
        // die;
        $strtgl2 = date_create($tgl2);
        $strtgl2 = date_format($strtgl2, 'd M Y');

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader rowspan=2>NO</td>";
        $HTML .= "<td class=tdheader rowspan=2>KECAMATAN</td>";
        $HTML .= "<td class=tdheader rowspan=2>DESA</td>";
        $HTML .= "<td class=tdheader colspan=2>KETETAPAN</td>";
        $HTML .= "<td class=tdheader colspan=2>REALISASI<br>BULAN LALU</td>";
        $HTML .= "<td class=tdheader colspan=2>REALISASI<br>BULAN INI</td>";
        $HTML .= "<td class=tdheader colspan=2>REALISASI<br>$strtgl1 - $strtgl2</td>";
        $HTML .= "</tr><tr>";
        $HTML .= "<td class=tdheader>BPHTB</td>";
        $HTML .= "<td class=tdheader>TAGIHAN</td>";
        $HTML .= "<td class=tdheader>BPHTB</td>";
        $HTML .= "<td class=tdheader>DIBAYAR</td>";
        $HTML .= "<td class=tdheader>BPHTB</td>";
        $HTML .= "<td class=tdheader>DIBAYAR</td>";
        $HTML .= "<td class=tdheader>BPHTB</td>";
        $HTML .= "<td class=tdheader>DIBAYAR</td>";
        $HTML .= "</tr>";

        if ($this->getDocumentRealisasi($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=3>Data Tidak Ditemukan !</td></tr> ";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    function getDocumentRealisasi($sts, &$dat)
    {
        global $data, $DBLink, $json, $tgl1, $tgl2, $jh, $a, $kec;

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        $tw = $this->getConfigValue($a, 'TENGGAT_WAKTU');
        $DbNameSW = $this->getConfigValue($a, 'BPHTBDBNAMESW');

        $tgl1 = ($tgl1 == '') ? date('Y') . '-01-01' : $tgl1;
        $tgl2 = ($tgl2 == '') ? date('Y-m-d') : $tgl2;
        $tahun = ($jh == '') ? date('Y') : $jh;

        $tgl1 = $tgl1 . " 00:00:00";
        $tgl2 = $tgl2 . " 23:59:59";
        // $wherekec = '';
        if ($kec != '' || $kec != NULL) {
            $wherekec = " AND  d.CPM_OP_KECAMATAN = '$kec'";
        } else {
            $wherekec = '';
        }
        $iErrCode = 0;

        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
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
        if ($res === false) {
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $d =  $json->decode($this->mysql2json($res, "data"));
        $datakel = $d->data;

        $row_kec = [];
        $nkel = 0;
        foreach ($datakel as $r) {
            $nkel++;
            $kec_code = $r->kec_code;
            unset($r->kec_code);
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
                        $wherekec
                    GROUP BY LEFT(g.op_nomor,10)";
        // var_dump($query);
        // die;
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $d =  $json->decode($this->mysql2json($res, "data"));
        $datapokok = $d->data;
        $row_pokok = [];
        foreach ($datapokok as $r) {
            $kel_code = $r->kel_code;
            unset($r->kel_code);
            $arr = [];
            $arr['BPHTB'] = $r->pokok;
            $arr['KETETAPAN'] = $r->dibayar;
            $row_pokok[$kel_code] = $arr;
        }

        foreach ($row_kec as $k => $kel) {
            foreach ($kel as $n => $kl) {
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
                        $wherekec
                    GROUP BY LEFT(g.op_nomor,10)";
        $res = mysqli_query($LDBLink, $query);

        if ($res === false) {
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $d =  $json->decode($this->mysql2json($res, "data"));
        $dataini = $d->data;
        $row_ini = [];
        foreach ($dataini as $r) {
            $kel_code = $r->kel_code;
            unset($r->kel_code);
            $arr = [];
            $arr['BPHTB_INI'] = $r->pokok;
            $arr['DIBAYAR_INI'] = $r->dibayar;
            $row_ini[$kel_code] = $arr;
        }

        foreach ($row_kec as $k => $kel) {
            foreach ($kel as $n => $kl) {
                $row_kec[$k][$n]['BPHTB_INI'] = (int)$row_ini[$kl['kel_code']]['BPHTB_INI'];
                $row_kec[$k][$n]['DIBAYAR_INI'] = (float)$row_ini[$kl['kel_code']]['DIBAYAR_INI'];
            }
        }
        // print_r($row_kec);exit;
        // ========================================================

        // Realisasi Bulan LALU
        $tglawal = date('Y-m') . '-15 00:00:00';
        $tglawal = date('Y-m-d H:i:s', strtotime($tglawal . ' -30 day'));
        $tglakhir = date_create($tglawal);
        $tglawal = substr($tglawal, 0, 7);

        $tglawal = $tglawal . '-01';
        $tglakhir = date_format($tglakhir, 'Y-m-t');

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
                        $wherekec
                    GROUP BY LEFT(g.op_nomor,10)";
        $res = mysqli_query($LDBLink, $query);

        if ($res === false) {
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $d =  $json->decode($this->mysql2json($res, "data"));
        $datalalu = $d->data;
        $row_lalu = [];
        foreach ($datalalu as $r) {
            $kel_code = $r->kel_code;
            unset($r->kel_code);
            $arr = [];
            $arr['BPHTB_LALU'] = $r->pokok;
            $arr['DIBAYAR_LALU'] = $r->dibayar;
            $row_lalu[$kel_code] = $arr;
        }

        foreach ($row_kec as $k => $kel) {
            foreach ($kel as $n => $kl) {
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
                        $wherekec
                    GROUP BY LEFT(g.op_nomor,10)";
        $res = mysqli_query($LDBLink, $query);

        if ($res === false) {
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $kecam = $kec;
        $d =  $json->decode($this->mysql2json($res, "data"));
        $datareal = $d->data;
        $row_real = [];
        foreach ($datareal as $r) {
            $kel_code = $r->kel_code;
            unset($r->kel_code);
            $arr = [];
            $arr['BPHTB_REAL'] = $r->pokok;
            $arr['DIBAYAR_REAL'] = $r->dibayar;
            $row_real[$kel_code] = $arr;
        }

        foreach ($row_kec as $k => $kel) {
            foreach ($kel as $n => $kl) {
                $row_kec[$k][$n]['BPHTB_REAL'] = (int)$row_real[$kl['kel_code']]['BPHTB_REAL'];
                $row_kec[$k][$n]['DIBAYAR_REAL'] = (float)$row_real[$kl['kel_code']]['DIBAYAR_REAL'];
            }
        }
        // print_r($row_kec);exit;
        // ========================================================

        $HTML = "";
        $data = $d;
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        $ss = true;
        $tw = 0;
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

        foreach ($row_kec as $kec) {

            foreach ($kec as $r) {

                if (($nama_kec != $r['KECAMATAN']) && $i > 1) {

                    $i = 1;
                    $HTML .= "<tr>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' colspan=3 align=right>TOTAL</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTB, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalKetetapan, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnlalu, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnlalu, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnini, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnini, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnreal, 0, ",", ".") . "</td>";
                    $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnreal, 0, ",", ".") . "</td>";
                    $HTML .= "</tr>";
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
                } else {
                    $totalBPHTB += $r['BPHTB'];
                    $totalKetetapan += $r['KETETAPAN'];
                    $totalBPHTBblnlalu += $r['BPHTB_LALU'];
                    $totalblnlalu += $r['DIBAYAR_LALU'];
                    $totalBPHTBblnini += $r['BPHTB_INI'];
                    $totalblnini += $r['DIBAYAR_INI'];
                    $totalBPHTBblnreal += $r['BPHTB_REAL'];
                    $totalblnreal += $r['DIBAYAR_REAL'];
                }

                $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

                $HTML .= "<tr>";
                $HTML .= "<td class=$class align=center>$i</td>";
                // if ($kecam != '' ||  $kecam != NULL) {

                // } else {
                // }
                $HTML .= ($nama_kec == $r['KECAMATAN']) ? "<td class=$class></td>" : "<td class=$class>" . $r['KECAMATAN'] . "</td>";
                $HTML .= "<td class=$class>" . $r['KELURAHAN'] . "</td>";
                $HTML .= "<td class=$class align=center>" . number_format($r['BPHTB'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($r['KETETAPAN'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=center>" . number_format($r['BPHTB_LALU'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($r['DIBAYAR_LALU'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=center>" . number_format($r['BPHTB_INI'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($r['DIBAYAR_INI'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=center>" . number_format($r['BPHTB_REAL'], 0, ",", ".") . "</td>";
                $HTML .= "<td class=$class align=right>" . number_format($r['DIBAYAR_REAL'], 0, ",", ".") . "</td>";
                $HTML .= "</tr>";
                $nama_kec = $r['KECAMATAN'];
                $i++;

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

        $HTML .= "<tr>";
        $HTML .= "<td style='background:#bababa;font-weight:700' colspan=3 align=right>TOTAL</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTB, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalKetetapan, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnlalu, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnlalu, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnini, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnini, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalBPHTBblnreal, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalblnreal, 0, ",", ".") . "</td>";
        $HTML .= "</tr>";
        $HTML .= "<tr><td colspan=11 class=tdbody1>&nbsp;</td></tr>";
        $HTML .= "<tr>";
        $HTML .= "<td style='background:#bababa;font-weight:700' colspan=3 align=right>TOTAL KESELURUHAN</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalallBPHTB, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalallKetetapan, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalallBPHTBblnlalu, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalallblnlalu, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalallBPHTBblnini, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalallblnini, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=center>" . number_format($totalallBPHTBblnreal, 0, ",", ".") . "</td>";
        $HTML .= "<td style='background:#bababa;font-weight:700' align=right>" . number_format($totalallblnreal, 0, ",", ".") . "</td>";
        $HTML .= "</tr>";

        #ardi total row
        // $allRows= mysql_query("SELECT * FROM $DbTable $where");
        // $this->totalRows = mysql_num_rows($allRows);

        $dat = $HTML;
        $total = count($row_kec);
        SCANPayment_CloseDB($LDBLink);

        if ($total == 0) {
            return false;
        } else {
            return true;
        }
    }

    function getDocumentRecap($sts, &$dat)
    {
        // global $json, $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $jh;
        global $data, $DBLink, $json, $a, $tgl1, $tgl2, $a, $tagihan_awal, $tagihan_akhir, $jh;
        $srcTxt = $tgl1;
        $srcTxt3 = $tgl2;
        $srcTxt2 = $jh;
        $srcTxt4 = $tagihan_awal;
        $srcTxt5 = $tagihan_akhir;

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        $tw = $this->getConfigValue($a, 'TENGGAT_WAKTU');
        $DbNameSW = $this->getConfigValue($a, 'BPHTBDBNAMESW');

        if ($srcTxt == ''  && $srcTxt2 == '' && $srcTxt3 == '' && $srcTxt4 == '' && $srcTxt5 == '') {
            $srcTxt = date('Y-m-d');
        }

        // $where = " WHERE PAYMENT_FLAG = 1";
        $where = "";
        $where2 = "";
        $where3 = "";
        $where4 = "";
        $where5 = "";

        if ($tgl1 != "") $where .= " WHERE $DbName.ssb.payment_paid >= '" . $tgl1 . " 00:00:00'";

        if ($tgl2 != "") {
            if ($srcTxt != "") {
                $where2 .= " AND $DbName.ssb.payment_paid <= '" . $tgl2 . " 23:59:59'";
            } else {
                $where2 .= " WHERE $DbName.ssb.payment_paid <= '" . $tgl2 . " 23:59:59'";
            }
        }

        if ($srcTxt2 != "") {
            if ($srcTxt == "" && $srcTxt3 == "") {
                $where3 .= " WHERE $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = '" . $srcTxt2 . "'";
            } else {
                $where3 .= " AND $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = '" . $srcTxt2 . "'";
            }
        }

        if ($srcTxt4 != "") {
            if ($srcTxt == "" && $srcTxt3 == "" && $srcTxt2 == "") {
                $where4 .= " WHERE CPM_OP_HARGA >= '" . $srcTxt4 . "'";
            } else {
                $where4 .= " AND CPM_OP_HARGA >= '" . $srcTxt4 . "'";
            }
        }


        if ($srcTxt5 != "") {
            if ($srcTxt == "" && $srcTxt3 == "" && $srcTxt2 == "" && $srcTxt4 == "") {
                $where5 .= " WHERE CPM_OP_HARGA <= '" . $srcTxt5 . "'";
            } else {
                $where5 .= " AND CPM_OP_HARGA <= '" . $srcTxt5 . "'";
            }
        }

        $iErrCode = 0;


        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
            exit(1);
        }

        $query = "SELECT $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK, $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_JENIS_HAK, SUM($DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA) AS CPM_OP_HARGA FROM $DbName.ssb
                    INNER JOIN
                    $DbNameSW.CPPMOD_SSB_DOC
                    ON
                    $DbNameSW.CPPMOD_SSB_DOC.CPM_SSB_ID = $DbName.ssb.id_switching
                    INNER JOIN
                    $DbNameSW.CPPMOD_SSB_JENIS_HAK
                    ON
                    $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_KD_JENIS_HAK 
                    $where
                    $where2
                    $where3
                    $where4
                    $where5
                    GROUP BY $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK, $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_KD_JENIS_HAK
                    ORDER BY $DbName.ssb.payment_paid DESC";
        // ORDER BY GW_SSB_PEKANBARU.ssb.id_ssb DESC LIMIT ".$this->page.",".$this->perpage; 
        // print_r($query);
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
            return false;
        }
        $d =  $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        $ss = true;
        $tw = 0;

        for ($i = 0; $i < count($data->data); $i++) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td class=$class align=center>" . ($i + 1) . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_JENIS_HAK . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_JENIS_HAK . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->CPM_OP_HARGA, 0, ",", ".") . "</td>";
            $HTML .= "</tr></div>";
            $tw++;
        }

        #ardi total row
        // $allRows= mysql_query("SELECT * FROM $DbTable $where");
        // $this->totalRows = mysql_num_rows($allRows);

        $dat = $HTML;
        $total = count($data->data);
        SCANPayment_CloseDB($LDBLink);

        if ($total == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getContent($sts)
    {
        $HTML = "";
        if ($sts == 1 || $sts == 3) {
            $HTML = $this->headerBerdasarUser($sts);
        } else if ($sts == 6) {
            $HTML = $this->headerRekapPerDesa($sts);
            // add by Taufiq
        } else if ($sts == 9 || $sts == 10 || $sts == 11) {
            $HTML = $this->headerByme($sts);
            //
        } else if ($sts == 12) {
            $HTML = $this->headerBymeLaporan($sts);
            //
        } else if ($sts == 13) {
            $HTML = $this->headerContentRecap($sts);
            //
        } else if ($sts == 14) {
            $HTML = $this->headerRealisasi($sts);
            //
        } else {
            $HTML = $this->headerPerUser($sts);
        }
        return $HTML;
    }

    public function displayDataMonitoring($sts)
    {
        global $find, $find_notaris, $tgl1, $tgl2;
        $konten = $this->getContent($sts);
        if ($sts == 1 || $sts == 2) {
            echo "<div id=\"summary\" style=\"float:right; margin-right:20px;  margin-top:8px; font-weight:bold;\">Total Transaksi : 
                  <span id=\"tot-trans\"> " . $this->jml_transaksi . "</span> | Total Penerimaan : <span id=\"tot-trims\">" . number_format($this->total_transaksi) . "</span></div>";
        }

        echo ($find != '' || $find_notaris != '' || $tgl1 != '' || $tgl2 != '') ? "<div id=\"summary\" style=\"float:left; margin-right:20px;  margin-top:8px; font-style:italic; font-weight:bold\">Hasil Seleksi [ Total Transaksi : 
                <span> " . $this->jml_transaksi_select . "</span> | Total Pembayaran : <span>" . number_format($this->total_transaksi_select) . "</span>]</div>" : "";
        echo "<div id=\"summary\" style=\"clear:both;\"></div> ";

        echo "<script>
                $('#select-all-notaris, #all').click(function(event) { 
                        if(this.checked) {
                                // Iterate each checkbox
                                $(':checkbox').each(function() {
                                        this.checked = true;                        
                                });
                        }else {
                                $(':checkbox').each(function() {
                                        this.checked = false;                        
                                });
                        }
                });
            
        $(function() {
                    $( '#src-tgl1-1' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-1' ).datepicker({ dateFormat: 'yy-mm-dd'});               
                    
                    $( '#src-tgl1-2' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-2' ).datepicker({ dateFormat: 'yy-mm-dd'});               
                    
                    $( '#src-tgl1-3' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-3' ).datepicker({ dateFormat: 'yy-mm-dd'});               
                    
                    $( '#src-tgl1-4' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-4' ).datepicker({ dateFormat: 'yy-mm-dd'}); 

                    $( '#src-tgl1-5' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-5' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-6' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-6' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-6' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-6' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    
                    $( '#src-tgl1-7' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-7' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-8' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-8' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-9' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-9' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-10' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-10' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-11' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-11' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-12' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-12' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-13' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-13' ).datepicker({ dateFormat: 'yy-mm-dd'});

                    $( '#src-tgl1-14' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2-14' ).datepicker({ dateFormat: 'yy-mm-dd'});

        });
        
        </script>";
        echo "<div id=\"notaris-main-content\">";
        echo "<div id=\"notaris-main-content-inner\">";
        echo $konten;
        echo "</div>";
        echo "<div id=\"notaris-main-content-footer\" align=right> ";
        echo $this->paging();
        echo "</div>";
    }

    function paging()
    {
        global $s, $page;
        //$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
        //$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
        //$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;

        $html = "<div>";
        $row = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        $rowlast = ($page * $this->perpage) < $this->totalRows ? ($page) * $this->perpage : $this->totalRows;
        //$rowlast = $this->totalRows < $rowlast ? $this->totalRows : $rowlast;
        $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $this->totalRows;

        if ($page != 1) {
            //$page--;
            $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
        }
        if ($rowlast < $this->totalRows) {
            //$page++;
            $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
        }
        $html .= "</div>";
        return $html;
    }
}

$q              = @isset($_REQUEST['q'])            ? $_REQUEST['q'] : "";
$find           = @isset($_REQUEST['find'])         ? trim($_REQUEST['find']) : "";
$find_notaris   = @isset($_REQUEST['find_notaris']) ? trim($_REQUEST['find_notaris']) : "";
$nm_wp          = @isset($_REQUEST['nm_wp'])        ? trim($_REQUEST['nm_wp']) : "";
$kd_byar        = @isset($_REQUEST['kd_byar'])      ? trim($_REQUEST['kd_byar']) : "";
$jh             = @isset($_REQUEST['jh'])           ? trim($_REQUEST['jh']) : "";
$tgl1           = @isset($_REQUEST['tgl1'])         ? $_REQUEST['tgl1'] : "";
$tgl2           = @isset($_REQUEST['tgl2'])         ? $_REQUEST['tgl2'] : "";
$kec            = @isset($_REQUEST['kec'])          ? $_REQUEST['kec'] : "";
$kel            = @isset($_REQUEST['kel'])          ? $_REQUEST['kel'] : "";
$page           = @isset($_REQUEST['page'])         ? $_REQUEST['page'] : 1;
$np             = @isset($_REQUEST['np'])           ? $_REQUEST['np'] : 1;
$tagihan_awal   = @isset($_REQUEST['tagihan_awal']) ? $_REQUEST['tagihan_awal'] : "";
$tagihan_akhir  = @isset($_REQUEST['tagihan_akhir']) ? $_REQUEST['tagihan_akhir'] : "";

// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);

//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;


#echo $a."-".$m."-".$n."-".$s."-".$uname; #~
if (isset($_SESSION['stVerifikasi'])) {
    if ($_SESSION['stVerifikasi'] != $s) {
        $_SESSION['stVerifikasi'] = $s;
        $find = "";
        $find_notaris = "";
        $page = 1;
        $np = 1;
        $tgl1 = '';
        $tgl2 = '';
        $kec = '';
        $kel = '';
        $nm_wp = '';
        $kd_byar = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stVerifikasi'] = $s;
}

$modNotaris = new BPHTBService(1, $uname);
$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);

$modNotaris->displayDataMonitoring($s);
