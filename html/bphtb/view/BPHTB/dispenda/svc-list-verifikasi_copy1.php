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

class stafDispenda extends modBPHTBApprover {

    function __construct($userGroup, $user) {
        $this->userGroup = $userGroup;
        $this->user = $user;
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

    function getTotalRows($query) {
        global $DBLink;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            echo $query . "<br>";
            echo mysqli_error($DBLink);
        }

        $row = mysqli_fetch_array($res);
        return $row['TOTALROWS'];
    }

    function getAllDocument(&$dat) {
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris;

        $srcTxt = $find;
        $where = "";

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%')";

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


            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
			<input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td><td class=\"" . $class . "\" align=\"center\">" .
                    number_format(intval($ccc), 0, ".", ",") . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            //$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusdoc."</td>\n";
            //$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
            $HTML .= "\t</tr></div>\n";
        }

        $dat = $HTML;
        return true;
    }

    function getDocumentInfoText($sts, &$dat) {
        global $data, $DBLink, $json, $a, $m, $find, $find_notaris;

        $srcTxt = $find;
        $where = "";

		if($sts == 4){
					if ($srcTxt != "")
            $where .= " AND (cppmod_ssb_doc.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR cppmod_ssb_doc.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
            		if ($find_notaris != "")
            $where .= " AND (cppmod_ssb_doc.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%')";
		}else{
		        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
            	if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%')";
		}



		if($sts == 3){
		//yang lama
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;
		}


		if($sts == 4)
		{
		$query = "SELECT * FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 5 $where ORDER BY cppmod_ssb_tranmain.CPM_TRAN_DATE DESC LIMIT 0,20 ";
		}
		
		//var_dump($query);
		//die;
				
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }
        #echo $query;
		
		if($sts == 3){
		//yang lama
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 $where ";
		}

         
         if($sts == 4){
         		$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS=4 AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 5 ";
         }       

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

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
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td>\n";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format(intval($ccc), 0, ".", ",") . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                    $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            //$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
            $HTML .= "\t</tr>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function getSPPTInfo($noktp, $nop, &$paid) {
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
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($LDBLink) . "' );</script>";
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
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($LDBLink) . "' );</script>";
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

    function getDocument($sts, &$dat) {
        global $DBLink, $json, $a, $m, $find, $find_notaris, $page;
        $srcTxt = $find;
        $where = "";

		if($sts ==5){
				        if ($srcTxt != "")
            $where .= " AND (cppmod_ssb_doc.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR cppmod_ssb_doc.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (cppmod_ssb_doc.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%')";
		}else{
		        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '" . $find_notaris . "%')";
		}

		
		//yang lama
        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

		if($sts ==5){
		$query = "SELECT * FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS IN (3)  AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 6 $where ORDER BY cppmod_ssb_tranmain.CPM_TRAN_DATE DESC LIMIT 0,20";}
		
		//yang lama
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 $where";


         if($sts == 5){
         		$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc JOIN cppmod_ssb_tranmain ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_tranmain.CPM_TRAN_SSB_ID JOIN cppmod_ssb_log ON cppmod_ssb_doc.CPM_SSB_ID=cppmod_ssb_log.CPM_SSB_ID WHERE cppmod_ssb_tranmain.CPM_TRAN_STATUS IN (3)  AND cppmod_ssb_log.CPM_SSB_LOG_ACTION = 6 ";}   
         		
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

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";
            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }
            $HTML .= "\t<div class=\"container\"><tr>\n";
            if (($sts == 4) || ($sts == 5) || ($sts == 1))
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
			<input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td><td class=\"" . $class . "\" align=\"right\">" .
                    number_format($ccc, 0, ".", ",") . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $data->data[$i]->CPM_TRAN_DATE . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function headerContent($sts) {
        global $find, $find_notaris;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP :</b>  
				<input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>
				<b>User :</b>
				<input type=\"text\" id=\"src-notaris-$sts\" name=\"src-notaris\" size=\"30\" value=\"$find_notaris\"/>
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>\n
				</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User</td>\n";
        if ($sts == 5)
            $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB yang harus dibayar</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }

        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentApprove($sts, $draf = false) {
        global $find, $find_notaris;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(3);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
				Pencarian Berdasarkan <b>Nama WP :</b> 
				<input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>				
				<b>User :</b>
				<input type=\"text\" id=\"src-notaris-$sts\" name=\"src-notaris\" size=\"30\" value=\"$find_notaris\"/>
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
				<!--<input type=\"button\" value=\"Download\" id=\"btn-download\" name=\"btn-download\" onclick=\"toExcel();\" />-->\n</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div class=\"container\">
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User Pelaporan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB yang harus dibayar</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContentReject($sts) {
        global $find, $find_notaris;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan 
				<b>Nama WP :</b> 
				<input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/> 
				<b>User :</b>
				<input type=\"text\" id=\"src-notaris-$sts\" name=\"src-notaris\" size=\"30\" value=\"$find_notaris\"/>
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\"  width=\"170\">User Pelaporan</td><td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"130\" > Tanggal</td>\n";
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

    public function headerContentAll() {
        global $s, $find, $find_notaris,$tgl1, $tgl2;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pilih Tanggal Cetak
                    <input type=\"text\" id=\"src-tgl1\" size=\"20\" value='$tgl1'> s.d
                    <input type=\"text\" id=\"src-tgl2\" size=\"20\" value='$tgl2'>
                    <input type=\"button\" value=\"Cetak xls\" id=\"btn-print-xls\" name=\"btn-print\" 
				onclick=\"printDataToXLS(0);\"/>&nbsp;&nbsp;&nbsp;&nbsp;Pencarian Berdasarkan 
				<b>Nama WP :</b>
				<input type=\"text\" id=\"src-approved-$s\" name=\"src-approved\" size=\"30\" value=\"$find\"/>
				<b>User :</b>
				<input type=\"text\" id=\"src-notaris-$s\" name=\"src-notaris\" size=\"30\" value=\"$find_notaris\"/> 
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($s);\"/>\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div class=\"container\">
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\" width=\"170\">User</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Status Pembayaran</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    function paging() {
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

    public function displayDataNotaris() {
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

$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : "";
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : "";
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
?>
