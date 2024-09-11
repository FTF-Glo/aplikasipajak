<?php
// error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
// ini_set('display_errors', 1);
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
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

class stafDispenda extends modBPHTBApprover
{

    function __construct($userGroup, $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
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
    function get_jenis_hak($kd)
    {
        global $DBLink;
        $query = "SELECT * FROM cppmod_ssb_jenis_hak WHERE cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = $kd";
        // var_dump($kd);
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['CPM_JENIS_HAK'];
    }
    function get_alasan_penolakan($kd)
    {
        global $DBLink;
        $query = "SELECT * FROM cppmod_ssb_tranmain WHERE cppmod_ssb_tranmain.CPM_TRAN_SSB_ID = '$kd' ORDER BY CPM_TRAN_SSB_NEW_VERSION DESC LIMIT 1";
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['CPM_TRAN_INFO'];
    }
    function getAllDocument(&$dat)
    {
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris, $find_nop, $find_kcmtn, $find_klrhn, $page, $find_jnshak;
        $srcTxt = $find;
        $where = "";

        if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  
                                        OR A.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                        )";

        if ($find_kcmtn != "")
            $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
        if ($find_klrhn != "")
            $where .= " AND A.CPM_OP_KElurahan LIKE '%" . $find_klrhn . "%'";
        if ($find_nop != "")
            $where .= "AND A.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";

        if ($find_jnshak != "")
            $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
		B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }
        #echo $query;
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
		AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1  $where";

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $PAID = "";
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
            $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
            $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);
            $config = $this->getConfigure($a);
            //print_r($config);
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

            $HTML .= "\t<div><tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
			<input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td><td class=\"" . $class . "\" align=\"center\">" .
                number_format(intval($ccc), 0, ".", ",") . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            // $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$."</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }

        $dat = $HTML;
        return true;
    }

    function getDocumentInfoText($sts, &$dat)
    {
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris, $find_nop, $find_kcmtn, $find_klrhn, $page, $find_jnshak;

        $srcTxt = $find;
        $where = "";

        if ($sts == 4) {
            if ($srcTxt != "") $where .= " AND (cppmod_ssb_doc.CPM_WP_NAMA LIKE '" . $srcTxt . "%'
                                OR cppmod_ssb_doc.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR cppmod_ssb_doc.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                            )";
            if ($find_nop != "")
                $where .= "AND cppmod_ssb_doc.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";

            if ($find_kcmtn != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
            if ($find_klrhn != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
            if ($find_jnshak != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_JENIS_HAK = $find_jnshak";
        } else {
            if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%'
                                OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR A.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                            )";

            if ($find_kcmtn != "")
                $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
            if ($find_klrhn != "")
                $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
            if ($find_nop != "")
                $where .= "AND A.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";
            if ($find_jnshak != "")
                $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";
        }



        if ($sts == 3) {
            //yang lama
            $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        }


        if ($sts == 4) {
            $query = "SELECT * FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 5 $where ORDER BY cppmod_ssb_tranmain.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        }

        //var_dump($query);
        //die;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }
        #echo $query;

        if ($sts == 3) {
            //yang lama
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 $where ";
        }


        if ($sts == 4) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 5 $where";
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;
        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID . "&sts=" . $sts;
            if ($this->userGroup == 2)
                $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            if ($this->userGroup == 3)
                $par1 = $params . "&f=f340-func--dispenda-pjb&idssb=" . $data->data[$i]->CPM_SSB_ID;
            //$par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";
            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";
            if ($this->userGroup == 3)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == ""))
                    $class = "tdbodyNew";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td>\n";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format(intval($ccc), 0, ".", ",") . "</td>
                            <td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            //$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>\n";
            $HTML .= "\t</tr>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function getSPPTInfo($noktp, $nop, &$paid, &$payment_code)
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
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
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
            $payment_code = $data->data[$i]->payment_code;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE op_nomor = '" . $nop . "'";
        $paid = "";
        $payment_code = "";
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
            $payment_code = $data->data[$i]->payment_code;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        SCANPayment_CloseDB($LDBLink);
        return "Tidak Ditemukan";
    }

    function getDocument($sts, &$dat)
    {

        global $DBLink, $json, $a, $m, $find, $find_notaris, $page, $find_nop, $find_kcmtn, $find_klrhn, $find_jnshak;
        $srcTxt = $find;
        $where = "";



        if ($sts == 5) {
            if ($srcTxt != "") $where .= " AND (cppmod_ssb_doc.CPM_WP_NAMA LIKE '" . $srcTxt . "%'
                                OR cppmod_ssb_doc.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR cppmod_ssb_doc.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                            )";
            if ($find_kcmtn != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
            if ($find_klrhn != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
            if ($find_nop != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";
            if ($find_jnshak != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_JENIS_HAK = $find_jnshak";
        } else {
            if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' 
                    OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR A.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                    )";
            if ($find_kcmtn != "")
                $where .= " AND A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
            if ($find_klrhn != "")
                $where .= " AND A.CPM_OP_KELURAHAN LIKE '%" . $find_kcmtn . "%'";
            if ($find_nop != "")
                $where .= " AND A.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";
            if ($find_jnshak != "")
                $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";
        }
        //yang lama
        // ODER BY
        if ($sts == 2) {
            $orderyby = 'ORDER BY B.CPM_TRAN_DATE ASC';
        } else {
            $orderyby = 'ORDER BY B.CPM_TRAN_DATE DESC';
        }
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
            $sts . " AND  B.CPM_TRAN_FLAG=0 $where {$orderyby} LIMIT " . $this->page . "," . $this->perpage;
        // die(var_dump($query));
        if ($sts == 5) {
            $query = "SELECT * FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS IN (3) $where ORDER BY cppmod_ssb_tranmain.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        }

        //yang lama
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
            $sts . " AND  B.CPM_TRAN_FLAG=0 $where";

        if ($sts == 5) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS IN (3) AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 6 $where";
        }
        // echo $query;
        // exit;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }
        // die(var_dump($qry));

        $this->totalRows = $this->getTotalRows($qry);
        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;


        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;
        for ($i = 0; $i < count($data->data); $i++) {

            // pengurangan SPN
            $sqlPenguranganBphtb = "Select * FROM cppmod_ssb_doc_pengurangan where CPM_SSB_ID ='{$data->data[$i]->CPM_SSB_ID}'";
            $res = mysqli_query($DBLink, $sqlPenguranganBphtb);
            $row = mysqli_fetch_assoc($res);
            if (mysqli_num_rows($res) >= 1) {
                $penguranganBayar = $row['nilaipengurangan'];
            }
            // end pengurangan
            // var_dump($row);
            // die;
            $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";
            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA) - $penguranganBayar;

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR - $penguranganBayar;
            }
            $HTML .= "\t<div><tr>\n";
            if (($sts == 4) || ($sts == 5) || ($sts == 1))
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
        	<input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            if ($sts == 5) {
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>\n";
            }
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format($ccc, 0, ".", ",") . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            if (($sts == 4) || ($sts == 1)) {
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_INFO_DISETUJUI . "</td>\n";
            }
            // if ($sts == 2){
            //     if ($data->data[$i]->CPM_TRAN_INFO =='') {
            //         $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $this->get_alasan_penolakan($data->data[$i]->CPM_SSB_ID) . "</td>\n";
            //     }
            //     else{
            //         $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_INFO . "</td>\n";
            //     }
            // }
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function headerContent($sts)
    {

        global $find, $find_notaris, $find_nop, $find_kcmtn;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP/Users :</b>
                <input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>            
                <b>Kecamatan\n&nbsp;&nbsp;&nbsp;\n:</b>
                " . $this->dropdown_kecamatan($sts) . "
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>NOP/NIK :</b>
                <input type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" size=\"30\" value=\"$find_nop\"/>
                <b>Kelurahan :</b>
                " . $this->dropdown_kelurahan($sts) . "
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                <input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(3);\"/>
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>Jenis Perolehan :</b>
                " . $this->dropdown_jnshak($sts) . "
				</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";

        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak sdsd</td><td class=\"tdheader\" width=\"170\">User</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Jenis Hak</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kecamatan</td>\n";
        if ($sts == 5)
            $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB yang harus dibayar</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        if ($sts != 2) {
            $HTML .= "\t\t<td class=\"tdheader\" >Alasan Penolakan</td>\n";
        }
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Bangunan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Tanah</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentApprove($sts, $draf = false)
    {
        global $find, $find_notaris, $find_nop, $find_kcmtn;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP/Users :</b>
				<input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>		     
                <b>Kecamatan\n&nbsp;&nbsp;&nbsp;\n:</b>
                
                " . $this->dropdown_kecamatan($sts) . "
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                <input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(3);\"/>
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>NOP/NIK :</b>
                <input type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" size=\"30\" value=\"$find_nop\"/>
                <b>Kelurahan :</b>
                " . $this->dropdown_kelurahan($sts) . "
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>Jenis Perolehan :</b>
                " . $this->dropdown_jnshak($sts) . "
				<!--<input type=\"button\" value=\"Download\" id=\"btn-download\" name=\"btn-download\" onclick=\"toExcel();\" />-->\n</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";

        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User Pelaporan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >User Verifikasi</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Jenis Hak</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kecamatan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB yang harus dibayar</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        // $HTML .= "\t\t<td class=\"tdheader\" >Alasan Disetujui</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Bangunan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Tanah</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentReject($sts)
    {
        global $find, $find_notaris, $find_nop, $find_kcmtn;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP/Users :</b>
                <input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>            
                <b>Kecamatan \n&nbsp;&nbsp;&nbsp;\n:</b>
                " . $this->dropdown_kecamatan($sts) . "
                
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>NOP/NIK :</b>
                <input type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" size=\"30\" value=\"$find_nop\"/>
                <b>Kelurahan :</b>
                " . $this->dropdown_kelurahan($sts) . "
                
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>Jenis Perolehan :</b>
                " . $this->dropdown_jnshak($sts) . "\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";

        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td>";
        $HTML .= "\t\t<td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td>";
        $HTML .= "\t\t<td class=\"tdheader\"  width=\"170\">User Pelaporan</td>";
        $HTML .= "\t\t<td class=\"tdheader\" >Jenis Hak</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kecamatan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"130\" > Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Bangunan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Tanah</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
        $HTML .= "\t</tr>\n";
        if ($this->getDocumentInfoText($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr>";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentAll()
    {
        global $s, $find, $find_notaris, $tgl1, $tgl2, $find_kcmtn, $find_nop;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pilih Tanggal Cetak
                    <input type=\"text\" id=\"src-tgl1\" size=\"20\" value='$tgl1'> s.d
                    <input type=\"text\" id=\"src-tgl2\" size=\"20\" value='$tgl2'>
                    <input type=\"button\" value=\"Cetak xls\" id=\"btn-print-xls\" name=\"btn-print\" 
				onclick=\"printDataToXLS(0);\"/>&nbsp;&nbsp;&nbsp;&nbsp;<br>
                Pencarian Berdasarkan 
				<b>Nama WP/Users :</b>
				<input type=\"text\" id=\"src-approved-$s\" name=\"src-approved\" size=\"30\" value=\"$find\"/>
				<b>Kecamatan :</b>
                " . $this->dropdown_kecamatan($s) . "
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($s);\"/>
                 <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;\n
                <b>NOP/NIK :</b>
                <input type=\"text\" id=\"src-nop-$s\" name=\"src-nop\" size=\"30\" value=\"$find_nop\"/>
                <b>Kelurahan :</b>
                " . $this->dropdown_kelurahan($s) . "
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
                <b>Jenis Perolehan :</b>
                " . $this->dropdown_jnshak($s) . "
                \n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\" width=\"170\">User</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Jenis Hak</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Kecamatan</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Status Pembayaran</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Bangunan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Luas Tanah</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }
    function dropdown_kecamatan($sts)
    {
        global $DBLink, $find_kcmtn;
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
    function dropdown_jnshak($sts)
    {
        global $DBLink, $find_jnshak;
        $qry = "SELECT * FROM cppmod_ssb_jenis_hak order by CPM_KD_JENIS_HAK";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select name=\"src-jnshak\" id=\"src-jnshak-{$sts}\" style=\"width:200px;height:26px;\">";

        $return .= "<option value=\"\">--Pilih Jenis Perolehan--</option>";
        while ($row = mysqli_fetch_assoc($res)) {
            if ($find_jnshak == $row['CPM_KD_JENIS_HAK']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $return .= "<option value=\"" . $row['CPM_KD_JENIS_HAK'] . "\" $selected>" . $row['CPM_JENIS_HAK'] . "</option>";
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

    public function displayDataNotaris()
    {
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
                    $( '#src-tgl1' ).datepicker({ dateFormat: 'yy-mm-dd'});
                    $( '#src-tgl2' ).datepicker({ dateFormat: 'yy-mm-dd'});								
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
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$find_notaris = @isset($_REQUEST['find_notaris']) ? $_REQUEST['find_notaris'] : "";
$find_kcmtn = @isset($_REQUEST['find_kcmtn']) ? $_REQUEST['find_kcmtn'] : "";
$find_klrhn = @isset($_REQUEST['find_klrhn']) ? $_REQUEST['find_klrhn'] : "";
$find_nop = @isset($_REQUEST['find_nop']) ? $_REQUEST['find_nop'] : "";

$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : "";
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : "";
$find_jnshak = @isset($_REQUEST['jnshak']) ? $_REQUEST['jnshak'] : "";
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


#echo $a."-".$m."-".$n."-".$s."-".$uname; #~
if (isset($_SESSION['stVerifikasi'])) {

    if ($_SESSION['stVerifikasi'] != $s) {
        $_SESSION['stVerifikasi'] = $s;
        $find = "";
        $find_notaris = "";
        $find_nop = "";
        $find_kcmtn = "";
        $find_klrhn = "";
        $page = 1;
        $np = 1;
        $tgl1 = '';
        $tgl2 = '';
        $find_jnshak = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stVerifikasi'] = $s;
}

$modNotaris = new stafDispenda(1, $uname);
$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setStatus($s);
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);
$modNotaris->displayDataNotaris();
