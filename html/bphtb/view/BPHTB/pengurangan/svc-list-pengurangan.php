<?php

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

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

class BPHTBService extends modBPHTBApprover
{

    // aldes
    public $extraFieldGw;

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    function getTotalRows($query)
    {
        global $DBLink;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['TOTALROWS'];
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


    function dropdown_kecamatan($sts)
    {
        global $DBLink, $find_kcmtn;
        $qry = "SELECT * FROM cppmod_tax_kecamatan2 ORDER BY CPC_TKC_KECAMATAN";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select class=\"form-control\" name=\"src-kcmtn\" id=\"src-kcmtn-{$sts}\" onchange=\"changekecmatan(this,$sts)\">";

        $return .= "<option value=\"\">--Pilih Kecamatan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            if ($find_kcmtn == $row['CPC_TKC_KECAMATAN']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $return .= "<option value=\"" . $row['CPC_TKC_KECAMATAN'] . "\" $selected>" . $row['CPC_TKC_KECAMATAN'] . "</option>";
        }
        $return .= "</select>";
        return $return;
    }
    function dropdown_kelurahan($sts)
    {
        global $DBLink, $find_klrhn, $find_kcmtn;
        $qry = "SELECT cppmod_tax_kelurahan2.* FROM cppmod_tax_kelurahan2
                  JOIN cppmod_tax_kecamatan2
                    ON cppmod_tax_kecamatan2.CPC_TKC_ID = cppmod_tax_kelurahan2.CPC_TKL_KCID
                WHERE cppmod_tax_kecamatan2.CPC_TKC_KECAMATAN = \"{$find_kcmtn}\"
                ORDER BY CPC_TKL_KELURAHAN";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select class=\"form-control\" name=\"src-klrhn\" id=\"src-klrhn-{$sts}\">";

        $return .= "<option value=\"\">--Pilih Kelurahan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            if ($find_klrhn == $row['CPC_TKL_KELURAHAN']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $return .= "<option value=\"" . $row['CPC_TKL_KELURAHAN'] . "\" $selected>" . $row['CPC_TKL_KELURAHAN'] . "</option>";
        }
        $return .= "</select>";
        return $return;
    }
    function getSPPTInfo($noktp, $nop, &$paid)
    {
        // return 'test';
        global $a;

        $iErrCode = 0;
        $a = $a;
        //LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        // return $DbName;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBcUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }

        // aldes
        $extrafield = '';
        if ($this->extraFieldGw) {
            $extrafield = ',approval_status, approval_msg, approval_qr_text';
        }

        // $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID {$extrafield} FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, expired_date FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        // die($query);
        $paid = "";

        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal 3");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $paid = $data->data[$i]->PAYMENT_PAID;

            // aldes
            if ($this->extraFieldGw) {
                $paid = (array)$data->data[$i];
                return (array)$data->data[$i];
            }

            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, expired_date {$extrafield} FROM $DbTable WHERE op_nomor = '" . $nop . "'";
        // echo $query;
        $paid = "";

        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal 4");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $paid = $data->data[$i]->PAYMENT_PAID;


            // aldes
            if ($this->extraFieldGw) {
                $paid = (array)$data->data[$i];
                return (array)$data->data[$i];
            }

            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        SCANPayment_CloseDB($LDBLink);
        return "Tidak Ditemukan";
    }

    function getSPPTInfo2($noktp, $nop, &$payment_code, &$exprd, &$paid)
    {
        global $a, $kd_byr;

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
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }

        // $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, payment_code, expired_date FROM $DbTable WHERE id_switching = '" . $noktp . "' AND payment_code LIKE \"%$kd_byr%\" ORDER BY saved_date DESC limit 1  ";
        $payment_code = "";
        $exprd = "";
        $paid = "";
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
            // var_dump($LDBLink);
        }

        // var_dump($DbHost);
        // var_dump($DbUser);
        // var_dump($DbPwd); 
        // var_dump($DbName);
        // die;
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $paid = $data->data[$i]->PAYMENT_PAID;
            $payment_code = $data->data[$i]->payment_code;
            $exprd = $data->data[$i]->expired_date;
            return $paid;
            return $payment_code;
            return $exprd;
        }

        // $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE op_nomor = '" . $nop . "'";
        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, payment_code, expired_date FROM $DbTable WHERE op_nomor = '" . $nop . "' AND payment_code LIKE \"%$kd_byr%\" LIMIT 1";
        $payment_code = "";
        $exprd = "";

        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
            print_r("Pengambilan data Gagal");
            echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }
        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res, "data"));
        $paid = "";
        for ($i = 0; $i < count($data->data); $i++) {
            // $payment_code = $data->data[$i]->PAYMENT_CODE;
            $paid = $data->data[$i]->PAYMENT_PAID;
            $payment_code = $data->data[$i]->payment_code;
            $exprd = $data->data[$i]->expired_date;
            return $paid;
            return $payment_code;
            return $exprd;
        }

        SCANPayment_CloseDB($LDBLink);
        return "Tidak Ditemukan";
    }

    function getDocument($sts, &$dat)
    {

        global $DBLink, $json, $a, $m, $find, $page, $ktp, $find_klrhn, $find_kcmtn, $kd_byr;

        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        // return $DbName;

        $srcTxt = $find;
        $srcKTP = $ktp;
        $where = "";
        $id_switchingIN = '';
        $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%')";
        $where .= " AND (A.sdelete IS NULL OR A.sdelete = '')";
        if ($find_klrhn != "")
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
        if ($find_kcmtn != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";

        if ($kd_byr != '') {
            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);

            if ($iErrCode != 0) {
                $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
                if (CTOOLS_IsInFlag(DEBcUG, DEBUG_ERROR))
                    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
                exit(1);
            }

            $query = "SELECT * FROM $DbTable WHERE PAYMENT_CODE like '%" . $kd_byr . "%' ORDER BY saved_date";
            $paid = "";

            $res = mysqli_query($LDBLink, $query);
            if ($res === false) {
                print_r("Pengambilan data Gagal XXXXX");
                echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
                return "Tidak Ditemukan";
            }
            $json = new Services_JSON();
            $data = $json->decode($this->mysql2json($res, "data"));
            for ($i = 0; $i < count($data->data); $i++) {

                $id_switchingIN .= "\"" . $data->data[$i]->id_switching . "\",";
            }
        }
        if ($id_switchingIN != "") {
            $where .= "AND CPM_SSB_ID IN(" . rtrim($id_switchingIN, ",") . ")";
        }
        $query = "";

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        // echo $_SESSION['role'];
        if ($_SESSION['role'] == 'rmBPHTBNotaris') {
            $where .= " AND A.CPM_SSB_AUTHOR ='" . $this->user . "'";
        }
        if ($this->userGroup == 1) {
            if ($sts == 2) {
                // var_dump('sini');
                // die;
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR B.CPM_TRAN_STATUS=3) AND 
                B.CPM_TRAN_FLAG=0 AND A.statuspengurangan='1' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR
                 B.CPM_TRAN_STATUS=3) AND B.CPM_TRAN_FLAG=0 AND A.statuspengurangan='1' $where ";
            } else {
                // var_dump("bukan");
                // die;
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 AND A.statuspengurangan='1' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 AND A.statuspengurangan='1' $where ";
            }
        }
        // var_dump($query);
        // die;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;
        for ($i = 0; $i < count($data->data); $i++) {

            $par1 = $params . "&f=f314-det-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID;
            if (($sts == 2) || ($sts == 5)) {
                $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID;
            }
            if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
                if ($data->data[$i]->CPM_PAYMENT_TIPE == "2")
                    $par1 = $params . "&f=funcKurangBayar&idssb=" . $data->data[$i]->CPM_SSB_ID . "&idtid=" . $data->data[$i]->CPM_TRAN_ID;
            }

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
            // var_dump($data->data[$i]->CPM_APHB);
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }

            $HTML .= "\t<div><tr>\n";
            if (($sts == 4) || ($sts == 5) || ($sts == 1) || ($sts == 2)) {
                if ($data->data[$i]->CPM_PAYMENT_TIPE != 2) {
                    $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
                            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
                } else {
                    $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"></td>\n";
                }
            }

            /// add by d3Di --- cari ada tidaknya qris di table, condition not expired ==========================
            $ssbid = trim($data->data[$i]->CPM_SSB_ID);
            $nop = trim($data->data[$i]->CPM_OP_NOMOR);

            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
            $datenow = date('Y-m-d H:i:s');
            $queryw = "SELECT id FROM qris WHERE id_switching ='$ssbid' AND expired_date_time>='$datenow'";
            $rescari = mysqli_query($LDBLink, $queryw);
            $adaqris = mysqli_num_rows($rescari) > 0 ? true : false;
            //================================================================================
            $statusBayar = $this->getSPPTInfo($ssbid, $nop, $PAID);
            $get_paymentcode = $this->getSPPTInfo2($ssbid, $nop, $PAYMENT_CODE, $exprd, $PAID);
            if ($adaqris && $statusBayar != 'Sudah Dibayar') {
                $imgQRIS = '<img id="idico' . $ssbid . '" src="./image/icon/qr.png" width="20px" height="20px">';
            } elseif ($adaqris) {
                $imgQRIS = '.';
            } else {
                $exprdx  = $exprd . ' 23:59:59';
                $datenow = date('Y-m-d H:i:s');
                $datenow = new DateTime($datenow);
                $dateexp = new DateTime($exprdx);
                if (($ccc > 0 && $ccc <= 10000000 && $statusBayar != 'Sudah Dibayar' && $PAYMENT_CODE != '' && $dateexp >= $datenow)) {
                    $token     = password_hash('LAMPUNGSELATANBPHTB' . date('ymd'), PASSWORD_DEFAULT);
                    $stop_date = date('Y-m-d H:i:s', strtotime($exprdx . ' -24 hours'));
                    $param     = "'$token','$ssbid','$PAYMENT_CODE','$stop_date','$nop'";
                    $imgQRIS = '<div id="divico' . $ssbid . '"><a href="javascript:;" onclick="getQRCode(' . $param . ')"><img id="idico' . $ssbid . '" src="./image/icon/qr_disable.png" width="20px" height="20px"></a>';
                } else {
                    $imgQRIS = '';
                }
            }

            $HTML .= "<td width=5 class=$class>$imgQRIS</td>";
            $HTML .= "<td width=20 class=$class>" . (++$no) . ".</td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KELURAHAN . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->CPM_BPHTB_BAYAR, 0, ",", ".") . "</td>";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
            $statusSPPT = "";
            if ($sts == 1)
                $statusSPPT = "Sementara";
            if ($sts == 2)
                if ($data->data[$i]->CPM_TRAN_STATUS == 2)
                    $statusSPPT = "Tertunda Verifikasi";
                else if ($data->data[$i]->CPM_TRAN_STATUS == 3)
                    $statusSPPT = "Tertunda Persetujuan";
                else if ($sts == 5)
                    $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR);
            $statusSPPT2 = $this->getSPPTInfo2($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAYMENT_CODE, $exprd, $PAID);
            $config = $this->getConfigure($a);
            if (($fullDays > intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID) != "Sudah Dibayar"))
                $statusSPPT = "Kadaluarsa";

            if ($sts == 5) {

                $HTML .= "\t\t<td class=$class align=center>$PAID</td>\n";
                $HTML .= "\t\t<td class=$class align=center>$exprd</td>\n";
                $HTML .= "\t\t<td class=$class align=center>$PAYMENT_CODE</td>\n";
            }
            if ($data->data[$i]->CPM_PAYMENT_TIPE != 5) {
                // $HTML .= "\t\t<td class=$class align=center>" . $statusSPPT . "</td>\n";
            } else {
                $HTML .= "\t\t<td class=$class align=center><font size=2>" . $statusSPPT . " (SPDKB)</font></td>\n";
            }

            // aldes
            if ($sts == 5) {
                $this->extraFieldGw = [
                    'approval_status',
                    'approval_msg',
                    'approval_qr_text'
                ];
                $extraJOS = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $gw_approval);

                $approvalStatus = $gw_approval['approval_status'];
                if ($approvalStatus == 1) {
                    $approvalStatus = 'Disetujui';
                } else if ($approvalStatus == 2) {
                    $approvalStatus = 'Ditolak';
                } else {
                    $approvalStatus = 'Sudah Tervalidasi';
                }
                $approvalMsg = $gw_approval['approval_msg'];
                if (!$approvalMsg) {
                    $approvalMsg = '-';
                }

                $dataPersetujuan = $data->data[$i]->CPM_TRAN_INFO_DISETUJUI;
                if (empty($dataPersetujuan)) {
                    $hasPersetujuan = " - ";
                } else {
                    $hasPersetujuan = $dataPersetujuan;
                }

                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $approvalStatus . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $hasPersetujuan . "</td>\n";
                $this->extraFieldGw = null;
            }

            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }

    function getDocumentHistory($sts, &$dat)
    {
        global $DBLink, $json, $a, $m, $find, $page, $ktp, $find_klrhn, $find_kcmtn;

        $where = "";
        // if ($find != "")
        // $where .= " AND CPM_OP_NOMOR LIKE '" . mysqli_real_escape_string($DBLink, $find) . "%'";
        $where .= " (b.CPM_WP_NAMA LIKE '" . $find . "%' OR b.CPM_OP_NOMOR LIKE '" . $find . "%' OR b.CPM_WP_NOKTP LIKE '" . $find . "%')";

        if ($find_klrhn != "")
            $where .= " AND b.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
        if ($find_kcmtn != "")
            $where .= " AND b.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";

        // if ($ktp != "")
        // $where .= " AND b.CPM_WP_NOKTP LIKE '%" . mysqli_real_escape_string($ktp) . "%'";
        // echo $_SESSION['uname'];
        if ($this->user != "ftfuser") {
            $where .= "AND a.CPM_SSB_AUTHOR=\"$this->user\"";
        }
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        $query = "  select a.*
                    from cppmod_ssb_log a
                    join cppmod_ssb_doc b on b.CPM_SSB_ID = a.CPM_SSB_ID
                    where 
                        $where 
                    order by CPM_SSB_LOG_ID desc limit $hal," . $this->perpage;
        // echo $query;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $qry = "  select a.CPM_OP_NOMOR 
                    from cppmod_ssb_log a
                    join cppmod_ssb_doc b on b.CPM_SSB_ID = a.CPM_SSB_ID
                    where 
                        $where ";

        $count = mysqli_query($DBLink, $qry);
        if ($count === false) {
            return false;
        }
        $jum = mysqli_num_rows($count);
        $this->totalRows = $jum;
        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $arrActions = array(
            1 => "Membuat Dokumen Sementara",
            2 => "Membuat Dokumen Final",
            3 => "Simpan Dokumen Sementara",
            4 => "Finalkan Dokumen Sementara",
            5 => "Menolak dokumen (Verifikasi)",
            6 => "Menyetujui dokumen (Verifikasi)",
            7 => "Menolak dokumen (Persetujuan)",
            8 => "Menyetujui dokumen (Persetujuan)",
            9 => "Menghapus Dokumen",
            10 => "Reversal Dokumen kembali ke Notaris",
            11 => "Reversal Dokumen kembali ke Verifikasi",
            12 => "Reversal Dokumen kembali ke Persetujuan",
            13 => "Reversal Dokumen menjadi ditolak",
            14 => "Reversal Dokumen menjadi final",
            15 => "Persetujuan dokumen dibatalkan",
        );

        $no = ($page - 1) * $this->perpage;
        for ($i = 0; $i < count($data->data); $i++) {

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<div><tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_LOG_DATE . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_LOG_ACTOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $arrActions[$data->data[$i]->CPM_SSB_LOG_ACTION] . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }
    //nggak
    function getAllDocument(&$dat)
    {
        global $data, $DBLink, $json, $a, $m, $find, $page, $ktp, $find_kcmtn, $find_klrhn;

        $srcTxt = $find;
        $srcKTP = $ktp;
        $where = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%')";
        if ($find_kcmtn != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
        if ($find_klrhn != "")
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";


        if ($_SESSION['role'] == 'rmBPHTBNotaris') {
            $where .= " AND A.CPM_SSB_AUTHOR ='" . $this->user . "'";
        }

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
            B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        //echo $query;
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
         AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' $where";

        $this->totalRows = $this->getTotalRows($qry);
        // var_dump($query);
        // die;
        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;

        for ($i = 0; $i < count($data->data); $i++) {
            $statusdoc = "";
            if ($data->data[$i]->CPM_TRAN_STATUS == 1)
                $statusdoc = "Sementara";
            if ($data->data[$i]->CPM_TRAN_STATUS == 2)
                $statusdoc = "Tertunda";
            if ($data->data[$i]->CPM_TRAN_STATUS == 3)
                $statusdoc = "Proses";
            if ($data->data[$i]->CPM_TRAN_STATUS == 4)
                $statusdoc = "Ditolak";
            if ($data->data[$i]->CPM_TRAN_STATUS == 5)
                $statusdoc = "Disetujui";
            if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
                if ($data->data[$i]->CPM_PAYMENT_TIPE == "2")
                    $par1 = $params . "&f=funcKurangBayar&idssb=" . $data->data[$i]->CPM_SSB_ID . "&idtid=" . $data->data[$i]->CPM_TRAN_ID;
            }
            $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
            $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);
            $statusSPPT2 = $this->getSPPTInfo2($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAYMENT_CODE);
            $config = $this->getConfigure($a);

            if (($fullDays > intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID) != "Sudah Dibayar"))
                $statusSPPT = "Kadaluarsa";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $statusSPPT = "";
            if ($data->data[$i]->CPM_TRAN_STATUS == 1)
                $statusSPPT = "Sementara";
            if ($data->data[$i]->CPM_TRAN_STATUS == 2)
                $statusSPPT = "Tertunda";
            else if ($data->data[$i]->CPM_TRAN_STATUS == 5)
                $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);

            $statusSPPT2 = $this->getSPPTInfo2($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAYMENT_PAID);
            $HTML .= "\t<div><tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KELURAHAN . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . number_format(intval($ccc), 0, ".", ",") . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }

        $dat = $HTML;
        return true;
    }
    function getDocumentExprd($sts, &$dat)
    {
        global $DBLink, $json, $a, $m, $find, $page, $find_klrhn, $find_kcmtn;
        $srcTxt = $find;
        $where = "";
        if ($srcTxt != "") {
            $where .= " AND (A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%' OR A.CPM_WP_NOKTP LIKE '%" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '%" . $srcTxt . "%')";
        }
        if ($find_kcmtn != "") {
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
        }
        if ($find_klrhn != "") {
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
        }

        // if($srcKTP != "")
        //     $where .= " AND A.CPM_WP_NOKTP LIKE '%" . $srcKTP . "%'";
        // $query = "";
        $iErrCode = 0;
        $a = $a;
        $id_switching = '';
        //LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
        $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
        $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
        $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
        $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
        $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
        // return $DbName;
        SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
        if ($iErrCode != 0) {
            $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
            if (CTOOLS_IsInFlag(DEBcUG, DEBUG_ERROR))
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }
        $querygetssb = "SELECT * FROM $DbTable WHERE payment_flag = 0 AND expired_date < DATE(NOW())";
        // die($query);
        $paid = "";

        $res2 = mysqli_query($LDBLink, $querygetssb);
        if ($res2 === false) {
            print_r("Pengambilan data Gagal 3");
            echo "<script>console.log( 'Debug Objects: " . $querygetssb . ":" . mysqli_error($LDBLink) . "' );</script>";
            return "Tidak Ditemukan";
        }

        $json = new Services_JSON();
        $data = $json->decode($this->mysql2json($res2, "data"));
        for ($i = 0; $i < count($data->data); $i++) {
            $id_switching .= "\"" . $data->data[$i]->id_switching . "\",";
        }
        ////// batas
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        // echo $_SESSION['role'];
        if ($_SESSION['role'] == 'rmBPHTBNotaris') {
            $where .= " AND A.CPM_SSB_AUTHOR ='" . $this->user . "'";
        }

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND  B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID IN (" . rtrim($id_switching, ",") . ") $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND  B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID IN (" . rtrim($id_switching, ",") . ")$where";

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;

        for ($i = 0; $i < count($data->data); $i++) {
            // die(var_dump($data->data[$i]));

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $statusSPPT2 = $this->getSPPTInfo2($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAYMENT_CODE, $exprd, $PAID);
            if (!empty($exprd)) {
                $HTML .= "\t<div><tr>\n";


                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NAMA . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_ALAMAT . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KELURAHAN . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . number_format($data->data[$i]->CPM_BPHTB_BAYAR, 0, ".", ",") . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $exprd . "</td> \n";

                $HTML .= "\t</tr></div>\n";
            }
        }
        $dat = $HTML;
        return true;
    }
    public function headerContentReject($sts)
    {
        global $find, $ktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> 
                    <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" value=\"{$find}\"size=\"40\"/> 
        <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n";

        $HTML .= "<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";

        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak</td>";
        $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan</td>";
        $HTML .= "\t\t<td class=\"tdheader\"> Kelurahan</td>";
        $HTML .= "\t\t<td class=\"tdheader\"  width=\"170\">BPHTB Sebelumnya</td><td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocumentInfoText($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr>";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentAll($sts)
    {
        global $find, $ktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"40\" value=\"{$find}\"/> 
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n
                ";

        $HTML .= "<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Kelurahan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">BPHTB Sebelumnya</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB Pengurangan</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContent($sts)
    {
        global $find, $ktp;
        $chk = "";
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        if ($sts) {
            $HTML .= "
            <style>
            .form-filtering {
                background-color: #fff;
                padding: 20px 20px;
                
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        
            }
        </style>

        <div  class=\"p-2\">
            <div class=\"row\"> 
                <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
                    <button class=\"btn btn-info\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$sts}\">
                        Filter Data
                    </button>
                </div>
            </div>
        </div>
        <div class=\"col-12\"> 
            <div class=\"collapse\" id=\"collapsFilter-{$sts}\">
                <div class=\"form-filtering\">
                    <form>
                        <div class=\"row\">

                            <div class=\"form-group col-md-4\"> 
                                <label>Nama WP/NOP/NIK</label>
                                    <input class=\"form-control\" type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\"value=\"{$find}\"/>
                            </div>
                            <div class=\" form-group col-md-4\"> 
                                <label>Kecamatan</label>
                                " . $this->dropdown_kecamatan($sts) . " 
                                    
                            </div>
                            <div class=\" form-group col-md-4\"> 
                                <label>Kelurahan</label>
                                " . $this->dropdown_kelurahan($sts) . " 
                                    
                            </div>
                            

                            <div class=\" form-group col-md-12\">    
                                <input type=\"button\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />
                                <input type=\"button\"  class=\"btn btn-info\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(1);\"/>								
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>";
        } else {
            $HTML .= "Masukan Pencarian Berdasarkan Nama WP/NOP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"40\" value=\"{$find}\"/> 
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";
        }

        $HTML .= " </form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak </td><td class=\"tdheader\" width=\"170\">BPHTB Sebelumnya</td>\n";
        if ($sts == 5)
            $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kecamatan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kelurahan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerHistory($sts)
    {
        global $find;
        $chk = "";

        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "
                Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> 
                <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" maxlength=\"18\" size=\"40\" value=\"{$find}\"/>
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n\n";

        $HTML .= "<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;
            <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
            <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Waktu </td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Petugas</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Proses</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocumentHistory($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>\n";
        return $HTML;
    }
    function headerContentkadaluarsa($sts)
    {
        global $find;
        $chk = "";

        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama Wp/NOP/NIK</b> 
                <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" maxlength=\"18\" size=\"40\" value=\"{$find}\"/>
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;
                \n</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak </td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan </td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Kelurahan </td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> BPHTB Sebelumnya</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal expired</td>\n";
        $HTML .= "\t</tr>\n";


        if ($this->getDocumentExprd($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>\n";
        return $HTML;
    }
    function getContent()
    {
        $HTML = "";
        switch ($this->status) {
            case 100:
                $HTML .= $this->headerContentAll(100);
                break;
            case 5:
                $HTML .= $this->headerContentApprove(5);
                break;
            case 4:
                $HTML .= $this->headerContentReject(4);
                break;
            case 3:
                $HTML .= $this->headerContentReject(3);
                break;
            case 2:
                $HTML .= $this->headerContent(2);
                break;
            case 1:
                $HTML .= $this->headerContentApprove(1, true);
                break;
            case 99;
                $HTML .= $this->headerContentkadaluarsa(99);
                break;
            case 6: #history
                $HTML .= $this->headerHistory($this->status);
                break;
        }
        return $HTML;
    }

    public function headerContentApprove($sts, $draf = false)
    {
        global $find, $ktp, $kd_byr;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
                    <style>
                    .form-filtering {
                        background-color: #fff;
                        padding: 20px 20px;
                        
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                
                    }
                </style>";
        if ($draf) {
            $HTML .= "
            <div  class=\"p-2\">
                <div class=\"row\"> 
                    <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
                        <button class=\"btn btn-info\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$sts}\">
                            Filter Data
                        </button>
                    </div>
                </div>
            </div>
            <div class=\"col-12\"> 
                <div class=\"collapse\" id=\"collapsFilter-{$sts}\">
                    <div class=\"form-filtering\">
                        <form>
                            <div class=\"row\">

                                <div class=\"form-group col-md-4\"> 
                                    <label>Nama WP/NOP/NIK</label>
                                        <input type=\"text\"  class=\"form-control\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"20\" value=\"{$find}\"/>
                                        <input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />
                                </div>
                                <div class=\" form-group col-md-4\"> 
                                    <label>Kecamatan</label>
                                    " . $this->dropdown_kecamatan($sts) . " 
                                        
                                </div>
                                <div class=\" form-group col-md-4\"> 
                                    <label>Kelurahan</label>
                                    " . $this->dropdown_kelurahan($sts) . " 
                                        
                                </div>
                                

                                <div class=\" form-group col-md-12\"> 
                                    <input type=\"button\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />   
                                    <input type=\"button\" class=\"btn btn-info\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\"onclick=\"printDataToPDF(1);\"/>
                                    <input type=\"hidden\" value=\"Cetak Pengantar Bayar\" id=\"btn-print-cpb\" name=\"btn-print-cpb\"onclick=\"printBeritaAcara(2);\"/>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>";


        } else {
            $HTML .= "
            
            <div  class=\"p-2\">
                <div class=\"row\"> 
                    <div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
                        <button class=\"btn btn-info\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter-{$sts}\" aria-expanded=\"false\" aria-controls=\"collapsFilter-{$sts}\">
                            Filter Data
                        </button>
                    </div>
                </div>
            </div>
            <div class=\"col-12\"> 
                <div class=\"collapse\" id=\"collapsFilter-{$sts}\">
                    <div class=\"form-filtering\">
                        <form>
                            <div class=\"row\">

                                <div class=\"form-group col-md-3\"> 
                                    <label>Nama WP/NOP/NIK</label>
                                        <input type=\"text\" class=\"form-control\" id=\"src-approved-{$sts}\" name=\"src-approved\" value=\"{$find}\"/> 

                                        <input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />
                                </div>
                                <div class=\"form-group col-md-3\"> 
                                    <label>Kode Bayar</label>
                                        <input type=\"text\"  class=\"form-control\"id=\"src-kdbyr-{$sts}\" name=\"src-kdbyr\" size=\"15\" value=\"{$kd_byr}\"/>
                                </div>
                                <div class=\" form-group col-md-3\"> 
                                    <label>Kecamatan</label>
                                    " . $this->dropdown_kecamatan($sts) . " 
                                        
                                </div>
                                <div class=\" form-group col-md-3\"> 
                                    <label>Kelurahan</label>
                                    " . $this->dropdown_kelurahan($sts) . " 
                                        
                                </div>
                                

                                <div class=\" form-group col-md-12\"> 
                                    <input type=\"button\"  class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ({$sts});\" />
                                    <input type=\"button\" class=\"btn btn-info\" value=\"Cetak Salinan\" id=\"btn-print\" name=\"btn-print\"onclick=\"printDataToPDF(2);\"/>
                                    <input type=\"hidden\" value=\"Cetak Pengantar Bayar\" id=\"btn-print-cpb\" name=\"btn-print-cpb\" onclick=\"printBeritaAcara(2);\"/>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>";
          
        }
        $HTML .= "</form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\">&nbsp;</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td>";
        $HTML .= "\t\t<td class=\"tdheader\">Kecamatan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\">Kelurahan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">BPHTB Sebelumnya</td>\n";
        if ($sts == 1) {
            # code...
            $HTML .= "\t\t<td class=\"tdheader\">Status</td>\n";
        }
        if ($sts == 5) {
            $HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Tanggal Bayar</td>\n";
            $HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Tanggal Expired</td>\n";
            $HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Kode Bayar</td>\n";
            $HTML .= "\t\t<td class=\"tdheader\" width=\"130\">Status Persetujuan</td>\n";
            $HTML .= "\t\t<td class=\"tdheader\" width=\"200\">Keterangan</td>\n";
        }

        //$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    function getDocumentInfoText($sts, &$dat)
    {
        global $data, $DBLink, $json, $a, $m, $page, $find, $ktp, $find_kcmtn, $find_klrhn;

        $srcTxt = $find;
        $srcKTP = $ktp;
        $where = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%')";
        if ($find_kcmtn != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '" . $find_kcmtn . "%'";
        if ($find_klrhn != "")
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '" . $find_klrhn . "%'";

        if ($this->userGroup == 1) {
            if ($_SESSION['role'] != 'rm1') {
                $where .= " AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "'";
            }
        }

        if ($_SESSION['role'] == 'rmBPHTBNotaris') {
            $where .= " AND A.CPM_SSB_AUTHOR ='" . $this->user . "'";
        }
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
                AND B.CPM_TRAN_FLAG=0 AND (A.sdelete IS NULL OR A.sdelete = '') $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
        $res = mysqli_query($DBLink, $query);

        if ($res === false) {
            return false;
        }

        #echo $query;
        if ($this->userGroup == 1) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "'";
        }
        if (($this->userGroup == 2) || ($this->userGroup == 3)) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0";
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID . "&sts=" . $sts;

            if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
                if ($data->data[$i]->CPM_PAYMENT_TIPE == "2")
                    $par1 = $params . "&f=funcKurangBayar&idssb=" . $data->data[$i]->CPM_SSB_ID . "&idtid=" . $data->data[$i]->CPM_TRAN_ID;
            }
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";
            if ($data->data[$i]->CPM_TRAN_FERIF_LAPANGAN == 0) {
                $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
            } else {

                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</td> \n";
            }
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KELURAHAN . "</td> \n";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format(intval($ccc), 0, ".", ",") . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) .
                "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>\n";
            $HTML .= "\t</tr>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function displayDataNotaris($sts)
    {
        global $uname;
        echo "<script>
        function deleteChekedNotaris() {
            var r=confirm(\"Anda yakin data akan dihapus!\");
            if (r==true)
              {
                 var val = [];
                $(':checkbox:checked').each(function(i){
                  val[i] = $(this).val();
                });
                var t = JSON.stringify(val);
                //.log(t);
                $.ajax({
                    type: \"POST\",
                    url: \"./view/BPHTB/notaris/svc-delete-notaris.php\",
                    // The key needs to match your method's input parameter (case-sensitive).
                    data: 'ids='+t+'&usr=" . base64_encode($uname) . "',
                    //dataType: \"json\",
                    success: function(data) {
                                                data = $.parseJSON(data)
                                                //console.log(data.success)
                                                // 'data' is a JSON object which we can access directly.
                                                // Evaluate the data.success member and do something appropriate...
                                                if (data.success == true){
                                                        alert (\"Data berhasil di hapus!\");
                                                        setTabs ('{$sts}')
                                                }
                                                else{
                                                        $('#section2').html(data.message);
                                                }
                                        },
                    failure: function(errMsg) {
                                                //console.log(errMsg)
                        alert(errMsg);
                    }
                });
              }
        }
        
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
        </script>";
        echo "<div id=\"notaris-main-content\">\n";
        echo "\t<div id=\"notaris-main-content-inner\">\n";
        echo $this->getContent();
        echo "\t</div>\n";
        echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
        echo $this->paging();
        echo "</div>\n";
    }

    function paging()
    {
        global $a, $m, $n, $s, $page, $np;
        //$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
        //$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
        //$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;

        $params = "a=" . $a . "&m=" . $m;
        $sel = $n;
        $sts = $s;

        $html = "<div>";
        $row = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        $rowlast = (($page) * $this->perpage) < $this->totalRows ? ($page) * $this->perpage : $this->totalRows;
        //$rowlast = $this->totalRows < $rowlast ? $this->totalRows : $rowlast;
        $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $this->totalRows;

        $parl = $params . "&n=" . $sel . "&s=" . $sts . "&p=" . ($this->defaultPage - 1);
        $paramsl = base64_encode($parl);

        $parr = $params . "&n=" . $sel . "&s=" . $sts . "&p=" . ($this->defaultPage + 1);
        $paramsr = base64_encode($parr);

        //if ($np) $page++;
        //else $page--;
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$ktp = @isset($_REQUEST['ktp']) ? $_REQUEST['ktp'] : "";
$kd_byr = @isset($_REQUEST['kd_byr']) ? $_REQUEST['kd_byr'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$find_kcmtn = @isset($_REQUEST['find_kcmtn']) ? $_REQUEST['find_kcmtn'] : "";
$find_klrhn = @isset($_REQUEST['find_klrhn']) ? $_REQUEST['find_klrhn'] : "";

// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);

//echo "<pre>"; print_r($q); echo "</pre>";
//sprint_r($_REQUEST);
$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;

if (isset($_SESSION['stPelaporan'])) {

    if ($_SESSION['stPelaporan'] != $s) {
        $_SESSION['stPelaporan'] = $s;
        $find = "";
        $ktp = "";
        $kd_byr = "";
        $find_notaris = "";
        $page = 1;
        $find_kcmtn = "";
        $find_klrhn = "";
        $np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stPelaporan'] = $s;
}

$modNotaris = new BPHTBService(1, $uname);

$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage(1);

$modNotaris->displayDataNotaris($s);
