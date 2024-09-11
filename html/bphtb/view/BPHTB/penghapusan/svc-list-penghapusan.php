<?php
if($_SERVER ['HTTP_USER_AGENT']=='Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0'){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'penghapusan', '', dirname(__FILE__))) . '/';

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

    #-

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
        $return = "<select id=\"kelurahan2-{$sts}\" style=\"height:28px\">";
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
        $qry = "SELECT * FROM cppmod_tax_kecamatan2";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
        $return = "<select id=\"kecamatan2-{$sts}\" style=\"height:28px\" onchange=\"changekecmatan(this,$sts)\">";
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

    
    // function getSPPTInfo($noktp, $nop, &$paid)
    // {
    //     global $a;

    //     $iErrCode = 0;
    //     $a = $a;
    //     //LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
    //     $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
    //     $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
    //     $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
    //     $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
    //     $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');

    //     SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
    //     if ($iErrCode != 0) {
    //         $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    //         if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    //             error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    //         exit(1);
    //     }

    //     $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
    //     $paid = "";
    //     $res = mysqli_query($LDBLink, $query);
    //     if ($res === false) {
    //         print_r("Pengambilan data Gagal");
    //         echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
    //         return "Tidak Ditemukan";
    //     }
    //     $json = new Services_JSON();
    //     $data = $json->decode($this->mysql2json($res, "data"));
    //     for ($i = 0; $i < count($data->data); $i++) {
    //         $paid = $data->data[$i]->PAYMENT_PAID;
    //         return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
    //     }

    //     $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE op_nomor = '" . $nop . "'";
    //     $paid = "";
    //     $res = mysqli_query($LDBLink, $query);
    //     if ($res === false) {

    //         print_r("Pengambilan data Gagal");
    //         echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
    //         return "Tidak Ditemukan";
    //     }
    //     $json = new Services_JSON();
    //     $data = $json->decode($this->mysql2json($res, "data"));
    //     for ($i = 0; $i < count($data->data); $i++) {
    //         $paid = $data->data[$i]->PAYMENT_PAID;
    //         return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
    //     }

    //     SCANPayment_CloseDB($LDBLink);
    //     return "Tidak Ditemukan";
    // }


    private $jml_transaksi = 0;
    private $total_transaksi = 0;
    private $jml_transaksi_select = 0;
    private $total_transaksi_select = 0;

    function getSPPTInfo2($noktp, $nop, &$payment_code, &$exprd, &$paid)
    {
        global $a, $kd_byar;

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

        // $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, payment_code, expired_date FROM $DbTable WHERE id_switching = '" . $noktp . "' AND payment_code LIKE \"%$kd_byar%\" ORDER BY saved_date DESC limit 1  ";
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
        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, payment_code, expired_date FROM $DbTable WHERE op_nomor = '" . $nop . "' AND payment_code LIKE \"%$kd_byar%\" LIMIT 1";
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
            // echo "Pembayaran";
            // exit;

            $where = " WHERE PAYMENT_FLAG = 0 AND sdelete = 0";
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
            $query = "SELECT * FROM ssb $where ORDER BY saved_Date DESC LIMIT " . $this->page . "," . $this->perpage;
            $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM ssb {$where}";
        }
        // var_dump($query);
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
        //var_dump($qry);
        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        // var_dump($data);
        // die;
        $hal = $this->page + 1;
        for ($i = 0; $i < count($data->data); $i++) {

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<div class=container><tr>";
            $HTML .= "<td width=20 class=$class align=center>
            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->id_switching . "\" /></td>";
            // var_dump($data->data[$i]->id_switching);
            // die;
            $HTML .= "<td class=$class align=right>" . ($hal) . ".</td>";
            // $HTML .= "<td class=$class align=right>" . $data->data[$i]->id_switching . ".</td>";
            if ($sts == 1) { #berdasarkan user
                $HTML .= "<td class=$class> " . $data->data[$i]->op_nomor . "</td>";
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
                    // echo "Pembayaran2;
                    // exit;

                    //isi dari tabel penghapusan
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_noktp . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_letak . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_kecamatan . "</td>";
                    $HTML .= "<td class=$class>" . $data->data[$i]->op_kelurahan . "</td>";
                    // $HTML .= "<td class=$class>" . $data->data[$i]->op_kelurahan . "</td>";
                    // $HTML .= "<td class=$class>" . $data->data[$i]->op_kelurahan . "</td>";

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
                    //   $HTML .= "<td class=$class>" . date('Y-m-d', time($data->data[$i]->saved_date)) . "</td>";
                    $approvalStatus = $data->data[$i]->approval_status;
                    if ($approvalStatus == 1) {
                        $approvalStatus = 'Disetujui';
                    } else if ($approvalStatus == 2) {
                        $approvalStatus = 'Ditolak';
                    } else {
                        $approvalStatus = 'Sudah Tervalidasi';
                    }
                    $HTML .= "<td class=$class>" . $approvalStatus . "</td>";
                    $HTML .= "<td class=$class>-</td>";
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
                $HTML .= "<td class=$class align=right>" . number_format((float)$data->data[$i]->jml_nilai_transaksi, 0, ",", ".") . "</td>";
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
            // $HTML .= "<td class=\"".$class."\">".$data->data[$i]->id_ssb."</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->wp_nama . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->wp_alamat . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->op_nomor . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_NMR_SERTIFIKAT . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->payment_code . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($data->data[$i]->bphtb_collectible, 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class align=center>" . number_format($data->data[$i]->CPM_DENDA, 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_JENIS_HAK . "</td>";

            $HTML .= "</tr></div>";
            $tw++;
            $total_bayar = $total_bayar + $data->data[$i]->bphtb_collectible;
            $total_denda = $total_denda + $data->data[$i]->CPM_DENDA;
            $total_njop = $total_njop + (($data->data[$i]->CPM_OP_LUAS_TANAH * $data->data[$i]->CPM_OP_NJOP_TANAH) + ($data->data[$i]->CPM_OP_LUAS_BANGUN * $data->data[$i]->CPM_OP_NJOP_BANGUN));
            $total_njopperm = $total_njopperm + $data->data[$i]->CPM_OP_NJOP_TANAH;
            $total_trans = $total_trans + $data->data[$i]->CPM_OP_HARGA;
        }
        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=$class colspan=\"10\">&nbsp;</td>";
        $HTML .= "</tr></div>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=tdheader colspan=\"5\"></td>";
        $HTML .= "<td class=tdheader align=\"left\">TOTAL Berkas</td>";
        $HTML .= "<td class=tdheader style='text-align:center;'>$berkas</td>";
        $HTML .= "<td class=tdheader colspan=\"4\"></td>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=$class colspan=\"10\">&nbsp;</td>";
        $HTML .= "</tr></div>";

        $HTML .= "<div class=container><tr style='height:25px;' >";
        $HTML .= "<td class=tdheader colspan=\"5\"></td>";
        $HTML .= "<td class=tdheader align=\"left\">TOTAL</td>";
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
    function dropdown_kecamatan($sts)
    {
        global $DBLink, $find_kcmtn;
        // var_dump( $find_kcmtn);
        $qry = "SELECT * FROM cppmod_tax_kecamatan2 ORDER BY CPC_TKC_KECAMATAN";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select name=\"src-kcmtn\" id=\"src-kcmtn-{$sts}\" onchange=\"changekecmatan(this,$sts)\" style=\"width:200px;height:26px;\">";

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
        $return = "<select name=\"src-klrhn\" id=\"src-klrhn-{$sts}\" style=\"width:200px;height:26px;\">";

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

    public function headerBerdasarUser($sts)
    {
        global $a, $find, $find_notaris, $tgl1, $tgl2, $kd_byar, $kel, $kec;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "'}");
        $HTML = "<form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        $HTML .= "<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"40\" value=\"{$find}\"/>
        <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />
        <input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />";

        $HTML .= "<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;
            <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
            <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>";

        $HTML .= "&nbsp;&nbsp;&nbsp;
                    <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('" . $j . "');\"/></form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader> No. </td>";
        $HTML .= "<td class=tdheader> NOP </td>";
        $HTML .= "<td class=tdheader> Nama User </td>";
        $HTML .= "<td class=tdheader> Jumlah Transaksi </td>";
        $HTML .= "<td class=tdheader> Jumlah Rupiah Transaksi </td>";
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

                <form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .=

            "<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"40\" value=\"{$find}\"/>
            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"printToXLS('" . $j . "')\" />
            <input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />";

        $HTML .= "<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <b>Kecamatan</b> :" . $this->select_option_kecamatan($kec, $sts) . "
                <b>kelurahan</b> :" . $this->select_option_kelurahan($kec, $sts, $kel) .
            "&nbsp;&nbsp;&nbsp;</form>";





        //     var_dump($kec);

        if ($sts != 8 && $sts != 5) {
            $HTML .= "<b>Nama User</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/>
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />";
        } else {
            $HTML .= "<b>Nama WP/NIK/NOP</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/>
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />";
        }
        if ($sts == 8) {

            $HTML .= "<br><input type=\"button\" value=\"Cetak DBH\" id=\"btn-print\" name=\"btn-print\" 
                onclick=\"printToXLS2('" . $j . "');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Kode Bayar</b><input type=\"text\" id=\"src-kdbyr-$sts\" value='$kd_byar' name=\"src-kdbyr\" size=\"30\"/>
                <b>Kecamatan</b> :" . $this->select_option_kecamatan($kec, $sts) . "
                <b>kelurahan</b> :" . $this->select_option_kelurahan($kec, $sts, $kel);
        }
        $HTML .= "</form>";
        $HTML .= "<div class=\"table-responsive\">";
        $HTML .= "<table cellpadding=4 cellspacing=1 border=0 width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader>No.</td>";
        if ($sts != 5 && $sts != 8) {
            $HTML .= "<td class=tdheader>ID SSPD</td>";
        }
        $HTML .= "<td class=tdheader>NOP </td>";
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
            $HTML .= "<td class=tdheader>Jumlah Pembayaran </td>";
        }
        if (($sts == 4) || ($sts == 5)) {
            $HTML .= "<td class=tdheader>User / Notaris </td>";
        }
        if ($sts == 2) {
            $HTML .= "<td class=tdheader>Kode Bayar </td>";
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
    //untuk header penghapusan

    public function headerContentReject($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel, $nm_wp, $kd_byar;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','kec':'" . $kec . "' ,'kel':'" . $kel . "','wp_nama' : '" . $nm_wp . "'}");
        // var_dump($sel);
        $link = "view/BPHTB/monitoring/svc-list-kecamatan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $link2 = "view/BPHTB/monitoring/svc-list-kelurahan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");

        // $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML = "<script>
        </script>

            <form autocomplete=\"off\" id=\"form-hapus\" name=\"form-hapus\" method=\"post\" action=\"\" >
            <input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-print\" onclick=\"dataHapus_reject(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $HTML .= "Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> 
                    <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" value=\"{$find}\"size=\"40\"/> 
                    <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>";


        $HTML .= "<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;
                <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>";

        // $HTML .= "<form autocomplete=\"off\" id=\"form-hapus\" name=\"form-hapus\" method=\"post\" action=\"\" >";

        // if ($sts == 9 || $sts == 4 || $sts == 1) {
        //     $namawpnopnik = ($sts == 9) ? "Nama WP/NOP/NIK" : "Nama User";
        //     $HTML .= "Seleksi Berdasarkan
        //                     <input type=\"hidden\" id=\"src-approved-$sts\"/> 
        //                     <b>Nama User</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/> 
        //                     <b>Nama WP/NOP/NIK</b> : <input type=\"text\" id=\"nm_wp-$sts\" value='$nm_wp' name=\"nm_wp\" size=\"30\"/> 
        //                     <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />
        //                     <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        //                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        //                     &nbsp;&nbsp;&nbsp;
        //                     <b>Kode Bayar</b> :<input type=\"text\" id=\"src-kdbyr-$sts\" value='$kd_byar'/>
        //                     <b>Kecamatan</b> :" . $this->select_option_kecamatan($kec, $sts) . "
        //                     <b>kelurahan</b> :" . $this->select_option_kelurahan($kec, $sts, $kel) . "
        //                     </form>";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td width=20 class=tdheader><div>
        <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>";
        $HTML .= "<td class=tdheader> No.</td>";

        $HTML .= "<td class=tdheader>Nomor Objek Pajak</td>";
        $HTML .= "<td class=tdheader>No. KTP</td>";
        $HTML .= "<td class=tdheader>Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader width=170>BPHTB yang harus dibayar</td><td class=tdheader width=200>Alasan Penolakan</td>";
        $HTML .= "</tr>";

        if ($this->getDocumentInfoText($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr>";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    function getDocumentInfoText($sts, &$dat)
    {
        global $data, $DBLink, $json, $a, $m, $page, $find, $ktp, $kec, $kel, $nm_wp, $kd_byar, $find_notaris,   $tgl1, $tgl2;

        // var_dump($nm_wp);

        $srcTxt = $find;
        $srcKTP = $ktp;
        $where = "";
        // $where = " AND sdelete = 0";

        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        if ($nm_wp != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $nm_wp . "%' OR A.CPM_OP_NOMOR LIKE '" . $nm_wp . "%' OR A.CPM_WP_NOKTP LIKE '" . $nm_wp . "%')";
        if ($kec != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '" . $kec . "%'";
        if ($kel != "")
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '" . $kel . "%'";
        if ($find_notaris != "")
            $where .= " AND A.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%'";

        if ($this->userGroup == 1) {
            if ($_SESSION['role'] != 'rm1') {
                $where .= " AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "'";
            }
        }

        if ($_SESSION['role'] == 'rmBPHTBNotaris') {
            $where .= " AND A.CPM_SSB_AUTHOR ='" . $this->user . "'";
        }
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID  AND B.CPM_TRAN_STATUS=" . $sts . " 
                AND B.CPM_TRAN_FLAG=0 AND (A.sdelete IS NULL OR A.sdelete = '') $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
        $res = mysqli_query($DBLink, $query);
        // echo $query;
        //         $query = "SELECT * FROM sw_ssb.cppmod_ssb_doc A, sw_ssb.cppmod_ssb_tranmain B,gw_ssb.ssb C WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND A.CPM_SSB_ID = C.id_switching AND B.CPM_TRAN_STATUS=" . $sts . " 
        //         AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
        // $res = mysqli_query($DBLink, $query);

        if ($res === false) {
            return false;
        }
        // var_dump( $where);

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

            $HTML .= "<tr>";
            $HTML .= "<td width=20 class=$class align=center>
            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>";
            $HTML .= "<td width=20 class=$class align=center>" . (++$no) . ".</td>";
            $HTML .= "<td width=20 class=$class align=center>" . $data->data[$i]->CPM_SSB_ID . "</td>";

            if ($data->data[$i]->CPM_TRAN_FERIF_LAPANGAN == 0) {
                $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
                $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td>";
            } else {

                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_NOMOR . "</td>";
                $HTML .= "<td class=$class>" . $data->data[$i]->CPM_WP_NOKTP . "</td>";
            }
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KELURAHAN . "</td>";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "<td class=$class align=right>" . number_format(intval($ccc), 0, ",", ".") . "</td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) .
                "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>";
            $HTML .= "</tr>";
        }
        $dat = $HTML;
        return true;
    }


    public function headerContentApprove($sts, $draf = false)
    {
        global $find, $ktp, $kd_byar;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        $HTML = "<script>
        </script>

            <form autocomplete=\"off\" id=\"form-hapus\" name=\"form-hapus\" method=\"post\" action=\"\" >
            <input type=\"button\" value=\"Hasxdspus\" id=\"btn-print\" name=\"btn-print\" onclick=\"dataHapus(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          
                <input type=\"hidden\" value=\"Cetak Pengantar Bayar\" id=\"btn-print-cpb\" name=\"btn-print-cpb\" onclick=\"printBeritaAcara(2);\"/>&nbsp;
                Masukan Pencarian Berdasarkan <b>Nama WP/NOP/NIK</b> &nbsp;
                <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"40\" value=\"{$find}\"/> 
                        <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ({$sts});\" />";
        $HTML .= "<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>Kode Bayar :</b> <input type=\"text\" id=\"src-kdbyr-{$sts}\" name=\"src-kdbyr\" size=\"15\" value=\"{$kd_byar}\"/> &nbsp;
                <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;";
        $HTML .= "</form>";


        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";

        $HTML .= "<td width=20 class=tdheader><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>";
        $HTML .= "<td class=tdheader>&nbsp;</td>";
        $HTML .= "<td class=tdheader>No.</td>";
        $HTML .= "<td class=tdheader>Nomor Objek Pajak ss</td>";
        $HTML .= "<td class=tdheader>No. KTP</td>";
        $HTML .= "<td class=tdheader>Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader width=170>BPHTB yang harus dibayar</td>";
        if ($sts == 1) {
            # code...
            $HTML .= "<td class=tdheader>Status</td>";
        }
        if ($sts == 5) {
            $HTML .= "<td class=tdheader width=150>Tanggal Bayar</td>";
            $HTML .= "<td class=tdheader width=150>Tanggal Expired</td>";
            $HTML .= "<td class=tdheader width=150>Kode Bayar</td>";
            $HTML .= "<td class=tdheader width=130>Status Persetujuan</td>";
            $HTML .= "<td class=tdheader width=200>Keterangan</td>";
        }

        //$HTML .= "<td class=tdheader >Versi</td>";
        $HTML .= "</tr>";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>";
        return $HTML;
    }


    function getDocument($sts, &$dat)
    {
        global $data, $DBLink, $json, $a, $m, $page, $find, $ktp, $find_klrhn, $find_kcmtn, $nm_wp, $kd_byar, $find_notaris, $tgl1, $tgl2;
        // var_dump($find_klrhn, $find_kcmtn, $nm_wp);
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
        $where .= " AND (A.CPM_WP_NAMA LIKE '" . $nm_wp . "%' OR A.CPM_OP_NOMOR LIKE '" . $nm_wp . "%' OR A.CPM_WP_NOKTP LIKE '" . $nm_wp . "%')";
        // $where .= " AND (A.sdelete != '1')";
        $where .= " AND (A.sdelete IS NULL OR A.sdelete = '')";
        // $where .= " AND A.sdelete = 0";
        if ($kel != "")
            $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
        if ($kec != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";

        if ($kd_byar != '') {
            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);

            if ($iErrCode != 0) {
                $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
                if (CTOOLS_IsInFlag(DEBcUG, DEBUG_ERROR))
                    error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
                exit(1);
            }

            $query = "SELECT * FROM $DbTable WHERE PAYMENT_CODE like '%" . $kd_byar . "%' ORDER BY saved_date";
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
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR B.CPM_TRAN_STATUS=3) AND 
                B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR
                 B.CPM_TRAN_STATUS=3) AND B.CPM_TRAN_FLAG=0 $where";
            } else {
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 $where";
            }
        }
        // var_dump($query);
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

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }

            $HTML .= "<div><tr>";
            if (($sts == 4) || ($sts == 5) || ($sts == 1) || ($sts == 2)) {
                if ($data->data[$i]->CPM_PAYMENT_TIPE != 2) {
                    $HTML .= "<td width=20 class=$class align=center>
                            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>";
                } else {
                    $HTML .= "<td width=20 class=$class align=center></td>";
                }
            }

            /// cari ada tidaknya qris di table, condition not expired ==========================
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

            // $HTML .= "<td width=5 class=$class>$imgQRIS</td>";
            $HTML .= "<td width=20 class=$class>" . (++$no) . ".</td>";
            $HTML .= "<td width=20 class=$class>" . $ssbid . "</td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KELURAHAN . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($ccc, 0, ",", ".") . "</td>";

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

                $HTML .= "<td class=$class align=center>$PAID</td>";
                $HTML .= "<td class=$class align=center>$exprd</td>";
                $HTML .= "<td class=$class align=center>$PAYMENT_CODE</td>";
            }
            if ($data->data[$i]->CPM_PAYMENT_TIPE != 5) {
                // $HTML .= "<td class=$class align=center>" . $statusSPPT . "</td>";
            } else {
                $HTML .= "<td class=$class align=center><font size=2>" . $statusSPPT . " (SPDKB)</font></td>";
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

                $HTML .= "<td class=$class align=center>" . $approvalStatus . "</td>";
                $HTML .= "<td class=$class align=center>" . $approvalMsg . "</td>";
                $this->extraFieldGw = null;
            }

            $HTML .= "</tr></div>";
        }
        $dat = $HTML;
        return true;
    }


    public function headerByme($sts)
    {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2, $find_kcmtn, $find_klrhn, $nm_wp, $kd_byar;
        $srcTxt = $find;
// var_dump( $find_kcmtn, $find_klrhn);
        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','src-kcmtn':'" . $find_kcmtn . "' ,'src-klrhn':'" . $find_klrhn . "','wp_nama' : '" . $nm_wp . "'}");
        //  $j = "{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "','kec':'" . $kec . "' ,'kel':'" . $kel . "','wp_nama' : '" . $nm_wp . "'}";
        // echo "$j";
        // if($kec == true){
        //     echo "string";
        // }
        // ini
        // var_dump($sts);

        $link = "view/BPHTB/monitoring/svc-list-kecamatan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $link2 = "view/BPHTB/monitoring/svc-list-kelurahan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'6', 'n':'6'}");
        $HTML = "<script>
            </script>
           <!-- <form autocomplete=\"off\" id=\"form-hapus\" name=\"form-hapus\" method=\"post\" action=\"hapus.php\">
            <input type=\"button\" value=\"Hapus\" id=\"btn-hapus\" name=\"btn-hapus\" 
            onclick=\"printToXLS('" . $j . "');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <form> -->
                <form autocomplete=\"off\" id=\"form-hapus\" name=\"form-hapus\" method=\"post\" action=\"\" >";
        if ($sts == 1) {
            $HTML .= " <input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-print\" onclick=\"dataHapus(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        } elseif ($sts == 4) {
            $HTML .= "<input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-print\" onclick=\"dataHapus_reject(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        } elseif ($sts == 2) {
            $HTML .= "<input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-print\" onclick=\"dataHapus_tunda(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        } elseif ($sts == 9) {
            $HTML .= "<input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ";
        }
        if ($sts == 9 || $sts == 4 || $sts == 1 || $sts == 2) {
            $namawpnopnik = ($sts == 9 || $sts == 4 || $sts == 1 || $sts == 2) ? "Nama WP/NOP/NIK" : "Nama User";
            $HTML .= "Seleksi Berdasarkan
                            <input type=\"hidden\" id=\"src-approved-$sts\"/> 
                            <b>Nama User</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/> 
                            <b>Nama WP/NOP/NIK</b> : <input type=\"text\" id=\"nm_wp-$sts\" value='$nm_wp' name=\"nm_wp\" size=\"30\"/> 
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />
                            <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;
                            <b>Kode Bayar</b> :<input type=\"text\" id=\"src-kdbyr-$sts\" value='$kd_byar'/>
                            <b>Kecamatan :</b> " . $this->dropdown_kecamatan($sts) . " &nbsp;
                <b>Kelurahan :</b>" . $this->dropdown_kelurahan($sts) . "&nbsp;&nbsp;&nbsp;</form>";
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
        $HTML .= "<td width=20 class=tdheader><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>";
        $HTML .= "<td class=tdheader>No.</td>";
        if ($sts == 1 || $sts == 2 || $sts == 4) {
            $HTML .= "<td class=tdheader>ID</td>";
        }
        $HTML .= "<td class=tdheader>NOP</td>";
        $HTML .= "<td class=tdheader>No. KTP</td>";
        $HTML .= "<td class=tdheader>Wajib Pajak</td>";
        $HTML .= "<td class=tdheader>Alamat OP</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>Kelurahan</td>";
        $HTML .= "<td class=tdheader>BPHTB Yang Harus Dibayar</td>";
        if ($sts == 1) {
            $HTML .= "<td class=tdheader>status</td>";
        }
        if ($sts == 4) {
            $HTML .= "<td class=tdheader>Alasan Penolakan</td>";
        } elseif ($sts == 9) {

            $HTML .= "<td class=tdheader>Kode Bayar</td>";
            $HTML .= "<td class=tdheader>Status Persetujuan</td>";
            $HTML .= "<td class=tdheader>Keterangan</td>";
            if ($sts != 9) {
                if ($sts == 10) {
                    $HTML .= "<td class=tdheader>Tanggal Expired</td>";
                } else {
                    $HTML .= "<td class=tdheader>Tanggal Pembayaran</td>";
                }
            }
        }
        $HTML .= "</tr>";
// var_dump($sts);die;
        if ($sts == 9 || $sts == 10 || $sts == 11) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 4) {
            if ($this->getDocumentInfoText($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr>";
            }
        } elseif ($sts == 1 || $sts == 2) {
            if ($this->getDocument($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=3>Data Kosong !</td></tr>";
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
        $HTML .= "<td class=tdheader> No. </td>";
        $HTML .= "<td class=tdheader> Kode </td>";
        $HTML .= "<td class=tdheader> Jenis Perolehan </td>";
        $HTML .= "<td class=tdheader> Harga Transaksi </td>";
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
        // var_dump($sts);die;
        // if ($sts == 3) {
        //     $HTML = $this->headerBerdasarUser($sts);
        // if ($sts == 203) {
        //     $HTML = $this->headerContentApprove($sts);
        //     // add by Taufiq
        if ($sts == 9 || $sts == 4  || $sts == 1) {
            $HTML = $this->headerByme($sts);
            // } else if ($sts == 4 ) {
            //     $HTML = $this->headerContentReject($sts);
            //
        }elseif ($sts == 2) {
            $HTML = $this->headerByme($sts);
        } else {
            $HTML = $this->headerPerUser($sts);
        }
        return $HTML;
    }

    public function displayDataMonitoring($sts)
    {
        // var_dump($sts);die;
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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? trim($_REQUEST['find']) : "";
$find_notaris = @isset($_REQUEST['find_notaris']) ? trim($_REQUEST['find_notaris']) : "";
$nm_wp = @isset($_REQUEST['nm_wp']) ? trim($_REQUEST['nm_wp']) : "";
$kd_byar = @isset($_REQUEST['kd_byar']) ? trim($_REQUEST['kd_byar']) : "";
$jh = @isset($_REQUEST['jh']) ? trim($_REQUEST['jh']) : "";
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : "";
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : "";
$kec = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : "";

$kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$tagihan_awal = @isset($_REQUEST['tagihan_awal']) ? $_REQUEST['tagihan_awal'] : "";
$tagihan_akhir = @isset($_REQUEST['tagihan_akhir']) ? $_REQUEST['tagihan_akhir'] : "";

// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);
//var_dump($q);
//die;
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;

// var_dump($uname);
// die;


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
// var_dump($s);
// die;
$modNotaris = new BPHTBService(1, $uname);
$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);

$modNotaris->displayDataMonitoring($s);
