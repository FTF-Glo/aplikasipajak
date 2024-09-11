<?php

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'splitNOP', '', dirname(__FILE__))) . '/';
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

class BPHTBService extends modBPHTBApprover {

    function __construct($userGroup, $user) {
        $this->userGroup = $userGroup;
        $this->user = $user;
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

    function getSPPTInfo($noktp, $nop, &$paid) {
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
        global $DBLink, $json, $a, $m, $find, $page, $ktp;
        $srcTxt = $find;
		$srcKTP = $ktp;
        $where = "";

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
		if($srcKTP != "")
			$where .= " AND A.CPM_WP_NOKTP LIKE '%" . $srcKTP . "%'";
        $query = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        if ($this->userGroup == 1) {
            if ($sts == 2) {
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR B.CPM_TRAN_STATUS=3) AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR
				 B.CPM_TRAN_STATUS=3) AND B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18";
            } else {
                $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                        $sts . " AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "'  AND LENGTH(CPM_OP_NOMOR)>18 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
                $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                        $sts . " AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18";
            }
        }

        #echo $query;		
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=f-split-NOP-Edit&idssb=" . $data->data[$i]->CPM_SSB_ID;
            if (($sts == 2) || ($sts == 5)) {
                $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID;
            }
            

            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";

            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";

            $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA);

            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR;
            }

            $HTML .= "\t<div><tr>\n";
            if (($sts == 4) || ($sts == 5) || ($sts == 1)||($sts == 2))
                if($data->data[$i]->CPM_PAYMENT_TIPE!=2){
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
			<input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
				}else{
				$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"></td>\n";
				}
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td><td class=\"" . $class . "\" align=\"right\">" .
                    number_format($ccc, 0, ".", ",") . "</td>\n";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);

            $statusSPPT = "";
            if ($sts == 1)
                $statusSPPT = "Sementara";
            if ($sts == 2)
                $statusSPPT = "Tertunda";
            else if ($sts == 5)
                $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);

            $config = $this->getConfigure($a);

            if (($fullDays > intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID) != "Sudah Dibayar"))
                $statusSPPT = "Kadaluarsa";

            if ($sts == 5)
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $PAID . "</td>\n";
			if($data->data[$i]->CPM_PAYMENT_TIPE!=2){
				$HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
			}else{
				$HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\"><font size=\"2\">" . $statusSPPT . " (SPDKB)</font></td>\n";
			}
			
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }

    function getDocumentHistory($sts, &$dat) {
        global $DBLink, $json, $a, $m, $find, $page, $ktp;

        $where = "";
        if ($find != "")
            $where .= " AND CPM_OP_NOMOR LIKE '" . mysqli_real_escape_string($DBLink, $find) . "%'";
		// if ($ktp != "")
            // $where .= " AND b.CPM_WP_NOKTP LIKE '%" . mysqli_real_escape_string($ktp) . "%'";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
      
        $query = "  select *
                    from cppmod_ssb_log
                    where 
                        CPM_SSB_AUTHOR='$this->user' $where 
                    order by CPM_SSB_LOG_ID desc limit $hal," . $this->perpage;

		//echo $query;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

        $qry = "  select CPM_OP_NOMOR 
                    from cppmod_ssb_log
                    where 
                        CPM_SSB_AUTHOR='$this->user' $where";

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
            14 => "Reversal Dokumen menjadi final");

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

    function getAllDocument(&$dat) {
        global $data, $DBLink, $json, $a, $m, $find, $page, $ktp;

        $srcTxt = $find;
		$srcKTP = $ktp;
        $where = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;
        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
		if ($srcKTP != "")
            $where .= " AND A.CPM_WP_NOKTP LIKE '%" . $srcKTP . "%'";

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
			B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;

        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
            return false;
        }

       //echo $query;
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
		 AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18 $where";

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

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
            
            $par1 = $params . "&f=f337-mod-display-notaris&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";

            $dateDiff = time() - strtotime($data->data[$i]->CPM_TRAN_DATE);
            $fullDays = floor($dateDiff / (60 * 60 * 24));
            $fullHours = floor(($dateDiff - ($fullDays * 60 * 60 * 24)) / (60 * 60));
            $fullMinutes = floor(($dateDiff - ($fullDays * 60 * 60 * 24) - ($fullHours * 60 * 60)) / 60);
            $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID, $data->data[$i]->CPM_OP_NOMOR, $PAID);
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


            $HTML .= "\t<div><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td><td class=\"" . $class . "\" align=\"center\">" .
                    number_format(intval($ccc), 0, ".", ",") . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">" . $statusSPPT . "</td>\n";
            $HTML .= "\t</tr></div>\n";
        }

        $dat = $HTML;
        return true;
    }

    public function headerContentReject($sts) {
        global $find,$ktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" value=\"{$find}\"size=\"60\"/> No KTP <input type=\"text\" id=\"src-ktp-{$sts}\" name=\"src-ktp\" size=\"20\" value=\"{$ktp}\"/>
		<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
        //$HTML .= "\t\t<td class=\"tdheader\" width=\"130\" > Tanggal</td>\n";
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

    public function headerContentAll($sts) {
        global $find,$ktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"60\" value=\"{$find}\"/> No KTP <input type=\"text\" id=\"src-ktp-{$sts}\" name=\"src-ktp\" size=\"20\" value=\"{$ktp}\"/>
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
        $HTML .= "\t</tr>\n";

        if ($this->getAllDocument($dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "Data Kosong !";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerContent($sts) {
        global $find,$ktp;
        $chk = "";
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        if ($sts) {
            $HTML .= "<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\nMasukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"60\" value=\"{$find}\"/> No KTP <input type=\"text\" id=\"src-ktp-{$sts}\" name=\"src-ktp\" size=\"20\" value=\"{$ktp}\"/>
			<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n
			<input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />\n</form>\n";
        } else {
            $HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"60\" value=\"{$find}\"/> 
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";
        }

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Alamat Objek Pajak </td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
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

    public function headerHistory($sts) {
        global $find;
        $chk = "";

        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "
                Pencarian Berdasarkan <b>NOP</b> 
                <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" maxlength=\"18\" size=\"40\" value=\"{$find}\"/>
                <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n
                \n</form>\n";

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

    function getContent() {
        $HTML = "";
        switch ($this->status) {
            case 100 :
                $HTML .= $this->headerContentAll(100);
                break;
            case 5 :
                $HTML .= $this->headerContentApprove(5);
                break;
            case 4 :
                $HTML .= $this->headerContentReject(4);
                break;
            case 3 :
                $HTML .= $this->headerContentReject(3);
                break;
            case 2 :
                $HTML .= $this->headerContent(2);
                break;
            case 1 :
                $HTML .= $this->headerContentApprove(1, true);
                break;
            case 6 : #history
                $HTML .= $this->headerHistory($this->status);
                break;
        }
        return $HTML;
    }

    public function headerContentApprove($sts, $draf = false) {
        global $find,$ktp;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        if ($draf) {
            $HTML .= "
			<input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-delete\" 
				onclick=\"deleteChekedNotaris();\"/>&nbsp;&nbsp;<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"60\" value=\"{$find}\"/> No KTP <input type=\"text\" id=\"src-ktp-{$sts}\" name=\"src-ktp\" size=\"20\" value=\"{$ktp}\"/>
						<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n
						<input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />\n</form>\n";
        } else {
            $HTML .= "<input type=\"button\" value=\"Cetak Salinan\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(2);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"60\" value=\"{$find}\"/> No KTP <input type=\"text\" id=\"src-ktp-{$sts}\" name=\"src-ktp\" size=\"20\" value=\"{$ktp}\"/>
						<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ({$sts});\" />\n</form>\n";
        }

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. KTP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
        if ($sts == 5)
            $HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Tanggal</td>\n";
        $HTML .= "\t\t<td class=\"tdheader\" width=\"130\">Status</td>\n";
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

    function getDocumentInfoText($sts, &$dat) {
        global $data, $DBLink, $json, $a, $m, $page, $find, $ktp;

        $srcTxt = $find;
		$srcKTP = $ktp;
        $where = "";
        $hal = (($page - 1) > 0 ? ($page - 1) : 0) * $this->perpage;

        if ($srcTxt != "")
            $where .= " AND (A.CPM_WP_NAMA LIKE '" . $srcTxt . "%' OR A.CPM_OP_NOMOR LIKE '" . $srcTxt . "%')";
		 if ($srcKTP != "")
            $where .= " AND A.CPM_WP_NOKTP LIKE '" . $srcKTP . "%'";

        if ($this->userGroup == 1) {
            $where .= " AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "'";
        }

        $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " 
				AND B.CPM_TRAN_FLAG=0 AND LENGTH(CPM_OP_NOMOR)>18 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $hal . "," . $this->perpage;
        $res = mysqli_query($DBLink, $query);

        if ($res === false) {
            return false;
        }

        #echo $query;
        if ($this->userGroup == 1) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='" . $this->user . "' AND LENGTH(CPM_OP_NOMOR)>18";
        }
        if (($this->userGroup == 2) || ($this->userGroup == 3)) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
                    $sts . " AND  B.CPM_TRAN_FLAG=0 AND LENGTH(CPM_OP_NOMOR)>18";
        }

        $this->totalRows = $this->getTotalRows($qry);

        $d = $json->decode($this->mysql2json($res, "data"));
        $HTML = "";
        $data = $d;
        $params = "a=" . $a . "&m=" . $m;

        for ($i = 0; $i < count($data->data); $i++) {
            $par1 = $params . "&f=funcDetailRejectSplitNOP&idssb=" . $data->data[$i]->CPM_SSB_ID . "&sts=" . $sts;
            
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($data->data[$i]->CPM_TRAN_READ == "")
                $class = "tdbodyNew";
            $HTML .= "\t<tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
			$HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NOKTP . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT)) . "</td>\n";

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

    public function displayDataNotaris($sts) {
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

}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$ktp = @isset($_REQUEST['ktp']) ? $_REQUEST['ktp'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;

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
        $find_notaris = "";
        $page = 1;
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
?>
