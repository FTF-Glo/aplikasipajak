<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'dispenda', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/dispenda/mod-dispenda.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var dispenda=2;</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/dispenda/mod-dispenda.js\" type=\"text/javascript\"></script>\n";

$params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
$sel = @isset($_REQUEST['n']) ? $_REQUEST['n'] : 1;
//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
$page = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;

//print_r($_REQUEST);

class pejabatDispenda extends modBPHTBApprover {

    function getAllDocument(&$dat) {
        global $data, $DBLink, $json, $srcTxt, $find_notaris, $noktp;

        $where = "";
        if ($srcTxt != "")
            $where = " AND A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%'";
        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '%" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%')";
		if ($noktp != "")
            $where .= " AND (A.CPM_WP_NOKTP LIKE '%" . $noktp . "%')";

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 AND B.CPM_TRAN_STATUS<>2  $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

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
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];

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
            $par1 = $params . "&f=f340-func--dispenda-pjb&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
            $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);
            $config = $this->getConfigure($_REQUEST['a']);

            if (($fullDays > intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR, $data->data[$i]->CPM_OP_NOMOR, $PAID) != "Sudah Dibayar"))
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
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_OP_LETAK)) . "</td><td class=\"" . $class . "\" align=\"center\">" .
                    number_format(intval($ccc), 0, ".", ",") . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";

            //$HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }

        $dat = $HTML;
        return true;
    }

    function getDocumentInfoText($sts, &$dat) {
        global $data, $DBLink, $json, $srcTxt, $find_notaris, $noktp;
        $where = "";

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '%" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%')";
		if ($noktp != "")
            $where .= " AND (A.CPM_WP_NOKTP LIKE '%" . $noktp . "%')";

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        #echo $query;
        if (($this->userGroup == 2) || ($this->userGroup == 3)) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 $where";
        }
        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];

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
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_OP_LETAK)) . "</td>\n";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }

            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format(intval($ccc), 0, ".", ",") . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\"><span title=\"" . $data->data[$i]->CPM_TRAN_INFO . "\" class=\"span-title\">" .
                    $this->splitWord($data->data[$i]->CPM_TRAN_INFO, 5) . "</span></a></td>\n";
            $HTML .= "\t</tr>\n";
        }
        $dat = $HTML;
        return true;
    }
	
	function getSPPTInfo($noktp,$nop,&$paid) {
		global $a;
		
		$iErrCode=0;
		$a = $a;
		//LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
		$DbName = $this->getConfigValue($a,'BPHTBDBNAME');
		$DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue($a,'BPHTBTABLE');
		$DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
		
		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
		if ($iErrCode != 0)
		{
		  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		  exit(1);
		}
		
		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '".$noktp."' ORDER BY saved_date DESC limit 1  ";
	   $paid = "";
		$res = mysqli_query($LDBLink, $query);
		if ( $res === false ){
			print_r(mysqli_error($LDBLink));
			return "Tidak Ditemukan"; 
		}
		$json = new Services_JSON();
		$data =  $json->decode($this->mysql2json($res,"data"));	
		for ($i=0;$i<count($data->data);$i++) {
			$paid = $data->data[$i]->PAYMENT_PAID;
			return $data->data[$i]->PAYMENT_FLAG?"Sudah Dibayar":"Siap Dibayar";
		}
		
		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE op_nomor = '".$nop."'";
	   $paid = "";
		$res = mysqli_query($LDBLink, $query);
		if ( $res === false ){
			print_r(mysqli_error($LDBLink));
			return "Tidak Ditemukan"; 
		}
		$json = new Services_JSON();
		$data =  $json->decode($this->mysql2json($res,"data"));	
		for ($i=0;$i<count($data->data);$i++) {
			$paid = $data->data[$i]->PAYMENT_PAID;
			return $data->data[$i]->PAYMENT_FLAG?"Sudah Dibayar":"Siap Dibayar";
		}
		
		SCANPayment_CloseDB($LDBLink);
		return "Tidak Ditemukan";
	}
	
    function getDocument($sts, &$dat) {
        global $data, $DBLink, $json, $srcTxt, $find_notaris, $noktp;

        $where = "";

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '%" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '%" . $srcTxt . "%')";
        if ($find_notaris != "")
            $where .= " AND (A.CPM_SSB_AUTHOR LIKE '%" . $find_notaris . "%')";
		if ($noktp != "")
            $where .= " AND (A.CPM_WP_NOKTP LIKE '%" . $noktp . "%')";

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
			AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                $sts . " AND  B.CPM_TRAN_FLAG=0 $where";

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        #echo $query;
        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f340-func--dispenda-pjb&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			$statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			$config = $this->getConfigure ($_REQUEST['a']);
			
			$statusSPPT="";
			if ($sts ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
            if ($this->userGroup == 3)
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
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_OP_LETAK)) . "</td><td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td><td class=\"" . $class . "\" align=\"right\">" .
                    number_format(intval($ccc), 0, ".", ",") . "</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }

    public function headerContent($sts) {
        global $srcTxt, $find_notaris, $noktp ;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP :</b>
				<input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"30\" value='$srcTxt'/>
				<b>User : </b> 
				<input type=\"text\" id=\"src-approved\" name=\"src-notaris\" size=\"30\" value='$find_notaris'/>
				<b>No KTP : </b> 
				<input type=\"text\" id=\"src-noktp\" name=\"src-noktp\" size=\"30\" value='$noktp'/>
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objesk Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Verifikator</td>\n";
        if ($sts == 5)
            $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
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

    public function headerContentApprove($sts, $draf = false) {
        global $srcTxt, $find_notaris, $noktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(3);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
				Pencarian Berdasarkan <b>Nama WP :</b> 
				<input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"30\" value='$srcTxt'/>
				<b>User : </b> 
				<input type=\"text\" id=\"src-approved\" name=\"src-notaris\" size=\"30\" value='$find_notaris'/> 
				<b>No KTP : </b> 
				<input type=\"text\" id=\"src-noktp\" name=\"src-noktp\" size=\"30\" value='$noktp'/>
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div class=\"container\">
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User Pelaporan</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Verifikator</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >BPHTB yang harus dibayar</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
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
        global $srcTxt, $find_notaris, $noktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP : </b>
				 <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"30\" value='$srcTxt'/> 
				 <b>User : </b> 
				 <input type=\"text\" id=\"src-approved\" name=\"src-notaris\" size=\"30\" value='$find_notaris'/>
				 <b>No KTP : </b> 
				<input type=\"text\" id=\"src-noktp\" name=\"src-noktp\" size=\"30\" value='$noktp'/>
				 <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td>";
        $HTML .= "\t\t<td class=\"tdheader\"  width=\"170\">User Pelaporan</td>";
        //$HTML .= "\t\t<td class=\"tdheader\" >Verifikator</td>\n";
        $HTML .= "<td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";

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
        global $srcTxt, $find_notaris, $noktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Pencarian Berdasarkan <b>Nama WP</b> 
				<input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"30\" value='$srcTxt'/>
				<b>User : </b> 
				<input type=\"text\" id=\"src-approved\" name=\"src-notaris\" size=\"30\" value='$find_notaris'/>
				<b>No KTP : </b> 
				<input type=\"text\" id=\"src-noktp\" name=\"src-noktp\" size=\"30\" value='$noktp'/>				
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">User Pelaporan</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" >Verifikator</td>\n";

        //$HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

}

$par1 = $params . "&n=1&s=5";
$par2 = $params . "&n=2&s=4";
$par4 = $params . "&n=3&s=3";
$par5 = $params . "&n=4&s=100";

if ($sel == 1)
    $sts = 5;
if ($sel == 2)
    $sts = 4;
if ($sel == 3)
    $sts = 3;
if ($sel == 4)
    $sts = 100;

/* $par1 = $params."&n=1&s=2";
  $par3 = $params."&n=2&s=5";
  $par4 = $params."&n=3&s=4";
  $par5 = $params."&n=4&s=100";

  if ($sel==1) $sts=3;
  if ($sel==2) $sts=5;
  if ($sel==3) $sts=4;
  if ($sel==4) $sts=100; */

$srcTxt = @isset($_REQUEST['src-approved']) ? $_REQUEST['src-approved'] : "";
$find_notaris = @isset($_REQUEST['src-notaris']) ? $_REQUEST['src-notaris'] : "";
$noktp = @isset($_REQUEST['src-noktp']) ? $_REQUEST['src-noktp'] : "";

$modNotaris = new pejabatDispenda(3, $data->uname);
$modNotaris->addMenu("Disetujui", "app-menu", base64_encode($par1));
$modNotaris->addMenu("Ditolak", "rej-menu", base64_encode($par2));
$modNotaris->addMenu("Tertunda", "dil5-menu", base64_encode($par4));
$modNotaris->addMenu("Semua Data", "all-menu", base64_encode($par5));
$modNotaris->setSelectedMenu($sel);
$modNotaris->setStatus($sts);

$pages = $modNotaris->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);


echo $modNotaris->showData();
?>
