<?php

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
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['CPM_JENIS_HAK'];
    }

    function dropdown_kecamatan($sts)
    {
        global $DBLink, $find_kcmtn;
        $qry = "SELECT * FROM cppmod_tax_kecamatan2 ORDER BY CPC_TKC_KECAMATAN";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select name=\"src-kcmtn\" id=\"src-kcmtn-{$sts}\" onchange=\"changekecmatan(this,$sts)\" class=\"form-control\">";

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
        $return = "<select name=\"src-klrhn\" id=\"src-klrhn-{$sts}\" class=\"form-control\">";

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
    function dropdown_jnshak($sts)
    {
        global $DBLink, $find_jnshak;
        $qry = "SELECT * FROM cppmod_ssb_jenis_hak order by CPM_KD_JENIS_HAK";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select name=\"src-jnshak\" id=\"src-jnshak-{$sts}\" class=\"form-control\">";

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

    function getAllDocument(&$dat)
    {
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris, $find_kcmtn, $find_klrhn, $find_nop, $page, $find_jnshak;

        $srcTxt = $find;
        $where = "";


        if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '%" . $srcTxt . "%' 
                                OR A.CPM_WP_NOKTP LIKE '%" . $srcTxt . "%' 
                            )";
        if ($find_kcmtn != "")
            $where .= " AND (A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%')";
        if ($find_klrhn != "")
            $where .= " AND (A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%')";
        if ($find_notaris != "")
            $where .= " AND A.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%'";
        if ($find_jnshak != "")
            $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
        B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 AND B.CPM_TRAN_STATUS<>2 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }
        #echo $query;
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
        AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 AND B.CPM_TRAN_STATUS<>2 $where";

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


            $HTML .= "<div><tr>";
            $HTML .= "<td width=20 class=$class align=center>
            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>";
            $HTML .= "<td width=20 class=$class align=center>" . (++$no) . ".</td>";

            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_LETAK . "</td><td class=$class align=center>" .
                number_format(intval($ccc), 0, ".", ",") . "</td><td class=$class>" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>";
            $HTML .= "<td class=$class align=right>" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class align=center>" . $statusSPPT . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_TRAN_DATE . "</td>";
            //$HTML .= "<td class=\"".$class."\" align=center>".$statusdoc."</td>";
            //$HTML .= "<td class=\"".$class."\" align=center>".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>";
            $HTML .= "</tr></div>";
        }

        $dat = $HTML;
        return true;
    }

    function getDocumentInfoText($sts, &$dat)
    {
       
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris, $find_nop, $find_kcmtn, $page, $find_jnshak;

        $srcTxt = $find;
        $where = "";

        if ($sts == 4) {

            if ($srcTxt != "") $where .= " AND (cppmod_ssb_doc.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR cppmod_ssb_doc.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' 
                                OR cppmod_ssb_doc.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'
                            )";

            if ($find_notaris != "")
                $where .= " AND (cppmod_ssb_doc.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%')";
        } else {
            if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' 
                                OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'
                            )";
        }
        if ($sts == 3) {

            if ($find_notaris != "")
                $where .= " AND (A.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%')";
            if ($find_kcmtn != "")
                $where .= " AND (A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%')";
            if ($find_nop != "")
                $where .= " AND (A.CPM_OP_NOMOR LIKE '%" . $find_nop . "%')";
            if ($find_jnshak != "")
                $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";
            $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
                AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
        }

        if ($sts == 4) {
            if ($find_kcmtn != "")
                $where .= " AND (cppmod_ssb_doc.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%')";
            if ($find_nop != "")
                $where .= " AND (cppmod_ssb_doc.CPM_OP_NOMOR LIKE '%" . $find_nop . "%')";
            if ($find_jnshak != "")
                $where .= " AND cppmod_ssb_doc.CPM_OP_JENIS_HAK = $find_jnshak";
            $query = "SELECT * FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 7 $where ORDER BY cppmod_ssb_tranmain.CPM_TRAN_DATE DESC LIMIT 0,20";
        }
        // die(var_dump($query));
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
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 7";
        }


        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f340-func--dispenda-pjb&idssb=" . $data->data[$i]->CPM_SSB_ID . "&sts=" . $sts;
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
            $HTML .= "<tr>";
            $HTML .= "<td width=20 class=$class align=center>" . (++$no) . ".</td>";

            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_LETAK . "</td>";

            if ($data->data[$i]->CPM_SSB_ID == '0a47f6b79fddd134857b2d3ebcb7b2f6' || $data->data[$i]->CPM_SSB_ID == '8ebb877e961c42c4fd38bc9517a40155' || $data->data[$i]->CPM_SSB_ID == '0649878e22b159e4b996d11a133bb1bc' ) {
                $ccc = 0;
            }else{
           
                $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
            }

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "<td class=$class align=right>" . number_format(intval($ccc), 0, ".", ",") . "</td><td class=$class>" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>";
            # code...
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>";
            $HTML .= "<td class=$class align=right>" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>";
            $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>";

            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_TRAN_DATE . "</td>";
            //$HTML .= "<td class=\"".$class."\" align=center>".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>";
            $HTML .= "</tr>";
        }
        $dat = $HTML;
        return true;
    }

    public function getSPPTInfo($noktp, $nop, &$paid, &$payment_code)
    {
        // var_dump($sts);die;
        global $a;
        // var_dump($noktp . ' ' . $nop . ' ' . $paid . '' . $payment_code);
        // die;
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

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
        // var_dump($query);
        // die;
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
            $payment_code = $data->data[$i]->PAYMENT_CODE;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        $query = "SELECT PAYMENT_FLAG, PAYMENT_PAID, PAYMENT_CODE FROM $DbTable WHERE op_nomor = '" . $nop . "'";
        // var_dump($query);
        // die;
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
            $payment_code = $data->data[$i]->PAYMENT_CODE;
            return $data->data[$i]->PAYMENT_FLAG ? "Sudah Dibayar" : "Siap Dibayar";
        }

        SCANPayment_CloseDB($LDBLink);
        return "Tidak Ditemukan";
    }

    function getDocument($sts, &$dat)
    {
        // echo $sts;
        // die;
        global $DBLink, $json, $a, $m, $find, $find_notaris, $page, $find_kcmtn, $find_klrhn, $find_nop, $page, $find_kdbyr, $find_jnshak, $tgl1, $tgl2;
        $srcTxt = $find;
        $where = "";

        $tgl1 = !$tgl1 ? date('Y').'-01-01' : $tgl1;
        $tgl2 = !$tgl2 ? date('Y-m-d') : $tgl2;

        $where .= " AND (DATE(B.CPM_TRAN_DATE)>='$tgl1' AND DATE(B.CPM_TRAN_DATE)<='$tgl2')";
        
        $where .= " AND C.payment_flag = 1";

        if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%' 
                                OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR A.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                            )";
        $where .= " AND (A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%')";
        if ($find_klrhn != "")
            $where .= " AND (A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%')";
        if ($find_jnshak != "")
            $where .= " AND A.CPM_OP_JENIS_HAK = $find_jnshak";
        if ($find_notaris != "")
            $where .= " AND A.CPM_SSB_AUTHOR LIKE '%$find_notaris%'";

        if ($find_kdbyr != '') {

            $DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
            $DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
            $DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
            $DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
            $DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');;
            SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);

            $query_get_data_backend = "select id_switching from $DbTable where payment_code LIKE '%" . $find_kdbyr . "%'";
            $resBE = mysqli_query($LDBLink, $query_get_data_backend);
            $whereIn = array();
            while ($dtBE = mysqli_fetch_array($resBE)) {
                $whereIn[] = $dtBE['id_switching'];
            }
            $where = "AND A.CPM_SSB_ID IN ('" . implode("','", $whereIn) . "')";
        }
        if ($sts == 5 ) {
            $where .= "AND B.CPM_TRAN_FLAG=1 ";
            $status = 6;
        }elseif ($sts == 3){
            $where .= "AND B.CPM_TRAN_FLAG=0 ";
            $status = 3;
        }
        $query="SELECT DISTINCT A.*, B.*, C.*
                FROM sw_ssb.cppmod_ssb_doc A
                JOIN sw_ssb.cppmod_ssb_tranmain B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
                JOIN gw_ssb.ssb C ON B.CPM_TRAN_SSB_ID = C.id_switching
                WHERE 
                    
                    B.CPM_TRAN_STATUS=$status 
                    
                $where 
                ORDER BY B.CPM_TRAN_DATE DESC 
                LIMIT " . $this->page . "," . $this->perpage;
        $qry = "SELECT COUNT(*) AS TOTALROWS 
                FROM sw_ssb.cppmod_ssb_doc A
                JOIN sw_ssb.cppmod_ssb_tranmain B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
                JOIN gw_ssb.ssb C ON B.CPM_TRAN_SSB_ID = C.id_switching
                WHERE 
                    B.CPM_TRAN_STATUS=$status 
                   
                $where";
        // print_r($query);
        // die;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        #echo $query;       

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;
        $no = ($page - 1) * $this->perpage;

        $arrID = [];
        for ($i = 0; $i < count($data->data); $i++) {
            $arrID[] = "'".$data->data[$i]->CPM_SSB_ID."'";
            $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";
                    if ($data->data[$i]->CPM_SSB_ID == '0a47f6b79fddd134857b2d3ebcb7b2f6' || $data->data[$i]->CPM_SSB_ID == '8ebb877e961c42c4fd38bc9517a40155' ||  $data->data[$i]->CPM_SSB_ID == '0649878e22b159e4b996d11a133bb1bc' ) {
                        $ccc = 0;
                    }else{
                
                        $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);
                    }
            $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID, $payment_code);
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            // var_dump($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2);
            // die;
            $HTML .= "<div><tr stts=$sts>";
            if (($sts == 4) || ($sts == 5) || ($sts == 1))
                $HTML .= "<td width=20 class=$class align=center>
            <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>";
            $HTML .= "<td width=9 class=$class align=right>" . (++$no) . ".</td>";

            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td>";
            $HTML .= "<td class=$class><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_LETAK . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class>" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>";
            if ($sts != 3) $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>";
            if ($sts == 5) {
                $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 . "</td>";
            }
            $HTML .= "<td class=$class align=right>" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>";
            if ($sts != 3) $HTML .= "<td class=$class align=right>" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>";
            $HTML .= "<td class=$class align=right>" . number_format($ccc, 0, ".", ",") . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_TRAN_DATE . "</td>";
            if ($sts == 5) {

                $HTML .= "<td class=$class align=center>" . $payment_code . "</td>";
                if ($statusSPPT != "Sudah Dibayar") {
                    $HTML .= "<td class=$class align=center><input type=button value='Batalkan' onclick=\"updatelaporan('" . $data->data[$i]->CPM_SSB_ID . "','" . $data->data[$i]->CPM_TRAN_ID . "')\"></td>";
                } else {
                    $HTML .= "<td class=$class align=center>Sudah Dibayar</td>";
                }
                $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_TRAN_INFO_DISETUJUI . "</td>";
            }
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_BANGUN . "</td>";
            $HTML .= "<td class=$class align=center>" . $data->data[$i]->CPM_OP_LUAS_TANAH . "</td>";
            $HTML .= "</tr></div>";
        }
        $arrID = implode(',',$arrID);
        $dat = $HTML . "<div data-array=\"".$arrID."\"></div>";
        return true;
    }

    public function headerContent($sts)
    {
        global $find, $find_notaris, $noktp, $tgl1, $tgl2;
        
        $tgl1 = !$tgl1 ? date('Y').'-01-01' : $tgl1;
        $tgl2 = !$tgl2 ? date('Y-m-d') : $tgl2;

        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
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
                                            <label>Nama WP/NOP</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-approved-$sts\" name=\"src-approved\" value=\"$find\"/>
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>User</label>
                                            <input type==\"text\" class=\"form-control\" id=\"src-notaris-$sts\" name=\"src-notaris\" value=\"$find_notaris\"/>
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>KTP</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-noktp\" name=\"src-noktp\" value='$noktp'/>
                                        </div>

                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Awal </label>
                                            <div style=\"display: flex; align-items: center;\">
                                                <input type=\"text\" class=\"form-control\" id=\"src-tgl1\" name=\"src-tgl1\" value='".$tgl1."'>
                                                <p style=\"margin-left:10px; margin-bottom: 0rem;\">s/d</p>
                                            </div>
                                        </div>

                                        <div class=\"form-group col-md-4\">
                                            <label>Tanggal Akhir</label>
                                            <div>
                                                <input type=\"text\" class=\"form-control\" id=\"src-tgl2\" name=\"src-tgl2\"value='".$tgl2."'>
                                            </div>
                                        </div>

                                        <div class=\" form-group col-md-12\"> 
                                            <input type=\"button\" class=\"btn btn-success\" value=Cari id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs($sts);\"/>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> 
                    
                    </div>
                </div>
            </form>";
        $HTML .= "<table cellpadding=4 cellspacing=1 border=0 width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader> No.</td>";

        $HTML .= "<td class=tdheader>Nomor Objek Pajak </td>";
        $HTML .= "<td class=tdheader>Wajib Pajak </td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>";
        $HTML .= "<td class=tdheader>Kecamatan</td>";
        $HTML .= "<td class=tdheader>User</td>";
        if ($sts == 5)  $HTML .= "<td class=tdheader>Tanggal</td>";
        $HTML .= "<td class=tdheader>Jenis Hak</td>";
        $HTML .= "<td class=tdheader>BPHTB yang harus dibayar</td>";
        $HTML .= "<td class=tdheader>Tanggal</td>";
        $HTML .= "<td class=tdheader>luas Bangunan</td>";
        $HTML .= "<td class=tdheader>luas Tanah</td>";
        $HTML .= "</tr>";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>";
        return $HTML;
    }

    public function headerContentApprove($sts, $draf = false)
    {
        global $find, $find_notaris, $noktp, $find_kcmtn, $find_nop, $find_kdbyr;
        $HTML = "<script>
                    function updatelaporan(ssbid,tranid){      
                    
                    
                    if(confirm(\"Apakah Anda Yakin Ingin Membatalkan Persetujuan untuk Data ini ?\")){
                        
                            $.ajax({
                                url: './function/BPHTB/dispenda/svc-batal-persetujuan.php',
                                method: 'POST',
                                data: {ssbid: ssbid,tranid:tranid},
                                success: function(res) {
                                    
                                    if(res==1){
                                        alert('Pembatalan Persetujuan Berhasil, Silahkan lanjutkan kembali pada tab tertunda!');
                                        location.reload();
                                    }else{
                                        alert('Pembatalan Persetujuan Gagal dilakukan');
                                        location.reload();
                                    }
                                }
                            });
                        
                    }
                }
                
                
                </script>";
        $HTML .= "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
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

                                            <div class=\"form-group col-md-4\" >
                                                <label>Nama WP/Users</label>
                                                <input type=\"text\" class=\"form-control\" id=\"src-approved-$sts\" name=\"src-approved\" value=\"$find\"/>  
                                            </div>

                                            <div class=\"form-group col-md-4\" >
                                                <label>User</label>
                                                <input type=\"text\" class=\"form-control\" id=\"src-notaris-$sts\" name=\"src-notaris\" value=\"$find_notaris\"/>
                                            </div>
                                            <div class=\"form-group col-md-4\" >
                                                <label>Kode Bayar</label>
                                                <input type=\"text\" class=\"form-control\" id=\"src-kdbyr-$sts\" name=\"src-kdbyr\" value=\"$find_kdbyr\"/> 
                                            </div>
                                        
                                            <div class=\"form-group col-md-4\" >
                                                <label>Kecamatan</label>
                                                " . $this->dropdown_kecamatan($sts) . "
                                            </div>
                                        
                                            <div class=\"form-group col-md-4\" >
                                                <label>Kelurahan</label>
                                                " . $this->dropdown_kelurahan($sts) . "
                                            </div> 

                                            <div class=\"form-group col-md-4\" >
                                                <label>Jenis Perolehan</label>
                                                " . $this->dropdown_jnshak($sts) . "
                                            </div>                      
                                            
                                            <div class=\" form-group col-md-12\"> 
                                                <input type=\"button\" class=\"btn btn-success\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                                                <input type=\"button\" class=\"btn btn-success\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(3);\"/>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div> 
                        
                        </div>
                    </div>   
               </form>";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td width=20 class=tdheader><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>";
        $HTML .= "<td class=tdheader> No.</td>";

        $HTML .= "<td class=tdheader> Nomor Objek Pajak</td>";
        $HTML .= "<td class=tdheader> Wajib Pajak </td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td><td class=tdheader width=\"170\">User Pelaporan</td>";
        $HTML .= "<td class=tdheader  width=\"170\">User Verifikasi</td>";
        $HTML .= "<td class=tdheader  width=\"170\">User Persetujuan</td>";
        $HTML .= "<td class=tdheader  width=\"170\">Jenis Hak</td>";
        $HTML .= "<td class=tdheader  width=\"170\">Kecamatan</td>";
        $HTML .= "<td class=tdheader >BPHTB yang harus dibayar</td>";
        $HTML .= "<td class=tdheader >Tanggal</td>";
        if ($sts == 5) {
            $HTML .= "<td class=tdheader >Kode Bayar</td>";
            $HTML .= "<td class=tdheader >Pembatalan Persetujuan</td>";
            $HTML .= "<td class=tdheader >Alasan Disetujui</td>";
        }
        $HTML .= "<td class=tdheader >luas Bangunan</td>";
        $HTML .= "<td class=tdheader >luas Tanah</td>";
        $HTML .= "</tr>";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerContentReject($sts)
    {
        global $find, $find_notaris, $noktp, $find_kcmtn, $find_nop, $find_kcmtn, $find_nop;
        $HTML = "
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
                                            <label>Nama WP/NOP</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-approved-$sts\" name=\"src-approved\"  value=\"$find\"/> 
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>User</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-notaris-$sts\" name=\"src-notaris\" value=\"$find_notaris\"/>  
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>Jenis Perolehan</label>
                                            " . $this->dropdown_jnshak($sts) . "
                                        </div>   
                                    
                                        <div class=\"form-group col-md-4\" >
                                            <label>Kecamatan</label>
                                            " . $this->dropdown_kecamatan($sts) . "
                                        </div>
                                    
                                        <div class=\"form-group col-md-4\" >
                                            <label>Kelurahan</label>
                                            " . $this->dropdown_kelurahan($sts) . "
                                        </div> 

                                                          
                                        
                                        <div class=\" form-group col-md-12\"> 
                                            <input type=\"button\" class=\"btn btn-success\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> 
                    
                    </div>
                </div> 
                </form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td class=tdheader> No.</td>";

        $HTML .= "<td class=tdheader> Nomor Objek Pajak</td>";
        $HTML .= "<td class=tdheader> Wajib Pajak </td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td>
                    <td class=tdheader  width=\"170\">BPHTB yang harus dibayar</td>
                    <td class=tdheader  width=\"170\">User Pelaporan</td>
                    <td class=tdheader  width=\"170\">User Verifikasi</td>
                    <td class=tdheader  width=\"170\">Jenis Hak</td>
                    <td class=tdheader  width=\"170\">Kecamatan</td>";
        # code...
        $HTML .= "<td class=tdheader  width=\"200\">Keterangan</td>";
        $HTML .= "<td class=tdheader width=\"130\" > Tanggal</td>";
        $HTML .= "<td class=tdheader >luas Bangunan</td>";
        $HTML .= "<td class=tdheader >luas Tanah</td>";
        //$HTML .= "<td class=tdheader >Versi</td>";
        $HTML .= "</tr>";
        if ($this->getDocumentInfoText($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr>";
        }
        $HTML .= "</table>";
        return $HTML;
    }

    public function headerContentAll()
    {
        global $s, $find, $find_notaris, $noktp, $find_kcmtn, $find_nop;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
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
                                            <label>Nama WP/NOP</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-approved-$s\" name=\"src-approved\" value=\"$find\"/> 
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>User</label>
                                            <input type=\"text\"  class=\"form-control\" id=\"src-notaris-$s\" name=\"src-notaris\" value=\"$find_notaris\"/>
                                        </div>

                                        <div class=\"form-group col-md-4\" >
                                            <label>Jenis Perolehan</label>
                                            " . $this->dropdown_jnshak($sts) . "
                                        </div>   
                                    
                                        <div class=\"form-group col-md-4\" >
                                            <label>Kecamatan</label>
                                            " . $this->dropdown_kecamatan($sts) . "
                                        </div>
                                    
                                        <div class=\"form-group col-md-4\" >
                                            <label>Kelurahan</label>
                                            " . $this->dropdown_kelurahan($sts) . "
                                        </div> 

                                                        
                                        
                                        <div class=\" form-group col-md-12\"> 
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($s);\"/>
                                        <input type=\"button\" class=\"btn btn-success\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(3);\"/>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> 
                    
                    </div>
                </div> 
            </form>";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
        $HTML .= "<tr>";
        $HTML .= "<td width=20 class=tdheader><div>
            <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>";
        $HTML .= "<td class=tdheader> No.</td>";

        $HTML .= "<td class=tdheader> Nomor Objek Pajak</td>";
        $HTML .= "<td class=tdheader> Wajib Pajak </td>";
        $HTML .= "<td class=tdheader>Alamat Objek Pajak</td><td class=tdheader width=\"170\">BPHTB yang harus dibayar</td><td class=tdheader width=\"170\">User</td>";
        $HTML .= "<td class=tdheader  width=\"170\">Jenis Hak</td>";
        $HTML .= "<td class=tdheader  width=\"170\">Kecamatan</td>";
        //$HTML .= "<td class=tdheader >Tanggal</td>";
        //$HTML .= "<td class=tdheader >Status Pembayaran</td>";
        $HTML .= "<td class=tdheader >Status</td>";
        $HTML .= "<td class=tdheader >Tanggal</td>";
        $HTML .= "<td class=tdheader >luas Bangunan</td>";
        $HTML .= "<td class=tdheader >luas Tanah</td>";
        //$HTML .= "<td class=tdheader >Versi</td>";
        $HTML .= "</tr>";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>";
        return $HTML;
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
                $('#src-tgl1').datepicker({ dateFormat: 'yy-mm-dd'});
                $('#src-tgl2').datepicker({ dateFormat: 'yy-mm-dd'});                          
            });
        </script>";
        echo "<div id=\"notaris-main-content\">";
        echo "<div id=\"notaris-main-content-inner\">";
        echo $this->getContent();
        echo "</div>";
        echo "<div id=\"notaris-main-content-footer\" align=right> ";
        echo $this->paging();
        echo "</div>";
    }
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$find_notaris = @isset($_REQUEST['find_notaris']) ? $_REQUEST['find_notaris'] : "";
$noktp = @isset($_REQUEST['src-noktp']) ? $_REQUEST['src-noktp'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : false;
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : false;
$find_nop = @isset($_REQUEST['find_nop']) ? $_REQUEST['find_nop'] : "";
$find_kcmtn = @isset($_REQUEST['find_kcmtn']) ? $_REQUEST['find_kcmtn'] : "";
$find_klrhn = @isset($_REQUEST['find_klrhn']) ? $_REQUEST['find_klrhn'] : "";
$find_kdbyr = @isset($_REQUEST['find_kdbyr']) ? $_REQUEST['find_kdbyr'] : "";
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
        $page = 1;
        $np = 1;
        $tgl1 = '';
        $tgl2 = '';
        $find_kcmtn = '';
        $find_klrhn = '';
        $find_nop = '';
        $find_kdbyr = '';
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
