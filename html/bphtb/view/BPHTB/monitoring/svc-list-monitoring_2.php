<?php

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

    #-

    function getAUTHOR($nop) {
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

    function getNOP($author) {
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

    private $jml_transaksi = 0;
    private $total_transaksi = 0;
    private $jml_transaksi_select = 0;
    private $total_transaksi_select = 0;

    function getDocumentPembayaran($sts, &$dat) {
        global $json, $a, $m, $find, $find_notaris, $tgl1, $tgl2, $kec, $kel;
        $srcTxt = $find;



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
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }

        $where = " WHERE PAYMENT_FLAG = 1"; #pembayaran            


        if ($kec != "")
            $where .= " AND wp_kecamatan LIKE '" . mysqli_escape_string($LDBLink, $kec) . "%'";

        if ($kel != "")
            $where .= " AND wp_kelurahan LIKE '" . mysqli_escape_string($LDBLink, $kel) . "%'";


        if ($srcTxt != "")
            $where .= " AND wp_nama LIKE '" . mysqli_escape_string($LDBLink, $srcTxt) . "%'";
		if($sts == 2){
			if ($find_notaris != "")
				$where .= " AND (wp_nama like '%" . mysqli_real_escape_string($LDBLink, $find_notaris) . "%')";
		}else{
			if ($find_notaris != "")
				$where .= " AND (author like '%" . mysqli_real_escape_string($LDBLink, $find_notaris) . "%')";
		}
        if ($tgl1 != "" && $tgl2 != "")
            $where .= " AND  (payment_paid between '" . mysqli_escape_string($LDBLink, $tgl1) . "' and '" . mysqli_escape_string($LDBLink, $tgl2) . " 23:59:59')";
        elseif ($tgl1 != "")
            $where .= " AND  (payment_paid = '" . mysqli_real_escape_string($LDBLink, $tgl1) . "')";
        elseif ($tgl2 != "")
            $where .= " AND  (payment_paid = '" . mysqli_real_escape_string($LDBLink, $tgl2) . "')";



        if ($sts == 1) {#berdasarkan user
            $query = "SELECT *,count(id_ssb) as jml_transaksi, sum(bphtb_dibayar) as jml_nilai_transaksi
                     FROM $DbTable $where GROUP BY bphtb_notaris 
                     ORDER BY saved_Date DESC LIMIT " . $this->page . "," . $this->perpage;
            $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where} GROUP BY bphtb_notaris";
        } else {#per user
            $query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC LIMIT " . $this->page . "," . $this->perpage;
            $qry = "SELECT sum(bphtb_dibayar) as TOTALBAYAR, count(*) as TOTALROWS FROM $DbTable {$where}";
        }
		// echo $query;
        $res = mysqli_query($LDBLink, $query);
        if ($res === false) {
             print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($LDBLink) . "' );</script>";
            return false;
        }
		// echo $query;
        #untuk total                 
        $dataSelect = mysqli_query($LDBLink, $qry);
        if ($ds = mysqli_fetch_assoc($dataSelect)) {
            if ($sts == 1) {#berdasarkan user
                $this->totalRows = mysqli_num_rows($dataSelect);
            } else {#per user
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
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . ($hal) . ".</td> \n";
            if ($sts == 1) { #berdasarkan user
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->op_nomor . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->author . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $data->data[$i]->jml_transaksi . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format($data->data[$i]->jml_nilai_transaksi, 0, ".", ",") . "</td>\n";
            } else { #per user
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->id_ssb . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->op_nomor . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->wp_nama . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->payment_paid . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . number_format($data->data[$i]->bphtb_dibayar, 0, ".", ",") . "</td>\n";
            }
			if(($sts ==2)||($sts == 4)||($sts == 5)){
				
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->author . "</td>\n";
            
			}
            $HTML .= "\t</tr></div>\n";
            $hal++;
        }

        $dat = $HTML;
        #total transaksi dan penerimaan
        $query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1";

        $dataTotal = mysqli_query($LDBLink, $query);
        if ($d = mysqli_fetch_object($dataTotal)) {
            $this->jml_transaksi = $d->jml_transaksi;
            $this->total_transaksi = $d->total_transaksi;
        }
        return true;
    }

    function getDocumentApproval($sts, &$dat) {
        global $DBLink, $json, $a, $m, $find_notaris, $tgl1, $tgl2;

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
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

            $qry = "SELECT sum(a.CPM_OP_BPHTB_TU) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where} GROUP BY b.CPM_TRAN_OPR_NOTARIS ";
        } else if ($sts == 4) {#per user
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

            $query = "SELECT * , (select c.CPM_TRAN_DATE from cppmod_ssb_tranmain c where a.CPM_SSB_ID=c.CPM_TRAN_SSB_ID and c.CPM_TRAN_STATUS='3') as VERIFIKASI_DATE FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID $where and b.CPM_TRAN_SSB_ID in $whereIn 
                     ORDER BY b.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

            $qry = "SELECT sum(a.CPM_OP_BPHTB_TU) as TOTALBAYAR, count(a.CPM_SSB_ID) as TOTALROWS 
                    FROM cppmod_ssb_doc a inner join cppmod_ssb_tranmain b on
                     a.CPM_SSB_ID=b.CPM_TRAN_SSB_ID {$where} and CPM_TRAN_SSB_ID in $whereIn ";
        }
		//echo $query;
        $res = mysqli_query($DBLink, $query);
        if ($res === false) {
             print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($DBLink) . "' );</script>";
            return false;
        }

        #untuk total                 
        $dataSelect = mysqli_query($DBLink, $qry);
        if ($ds = mysqli_fetch_assoc($dataSelect)) {
            if ($sts == 3) {#berdasarkan user
                $this->totalRows = mysqli_num_rows($dataSelect);
            } else {#per user
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
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . ($hal) . ".</td> \n";
            if ($sts == 3) {
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_NOTARIS . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . $data->data[$i]->jml_transaksi . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\" align=\"right\">" . number_format($data->data[$i]->jml_nilai_transaksi, 0, ".", ",") . "</td>\n";
            } else {
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_ID . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_WP_NAMA . "</td> \n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_CREATED . "</td>\n";
                
                $verifydate = ($sts==5)? $data->data[$i]->VERIFIKASI_DATE : $data->data[$i]->CPM_TRAN_DATE;
                
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $verifydate . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>\n";
                
                $claimdate = ($sts==5)? $data->data[$i]->CPM_TRAN_DATE : $data->data[$i]->CPM_TRAN_CLAIM_DATETIME;
                
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $claimdate . "</td>\n";
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 . "</td>\n";
                
                    $bphtb_terutang = $data->data[$i]->CPM_BPHTB_BAYAR;
               
                    
                
                $HTML .= "\t\t<td class=\"" . $class . "\">" . number_format($bphtb_terutang, 0, ".", ",") . "</td>\n";
            }
			if(($sts == 4)||($sts == 5)){
				
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_NOTARIS . "</td>\n";
            
			}
            $HTML .= "\t</tr></div>\n";
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
                error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
            exit(1);
        }
        #total transaksi dan penerimaan
        $query = "SELECT SUM(bphtb_dibayar) total_transaksi, COUNT(*) jml_transaksi FROM $DbTable WHERE payment_flag = 1";

        $dataTotal = mysqli_query($LDBLink, $query);
        if ($d = mysqli_fetch_object($dataTotal)) {
            $this->jml_transaksi = $d->jml_transaksi;
            $this->total_transaksi = $d->total_transaksi;
        }
        return true;
    }

    public function headerBerdasarUser($sts) {
        global $a, $find, $find_notaris, $tgl1, $tgl2;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "'}");
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";

        $HTML .= "
        <input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" 
            onclick=\"printToXLS('" . $j . "');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            Seleksi Berdasarkan 
                            <b>Tanggal Bayar</b> : 
                            <input type=\"text\" id=\"src-tgl1-$sts\" size=\"20\" value='$tgl1'> s.d
                            <input type=\"text\" id=\"src-tgl2-$sts\" size=\"20\" value='$tgl2'> 
                            <input type=\"hidden\" id=\"src-approved-$sts\"/> 
                            <input type=\"hidden\" id=\"src-notaris-$sts\"/> 
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nama User </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Jumlah Transaksi </td>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> Jumlah Rupiah Transaksi </td>\n";
		if(($sts == 4)||($sts == 5)){
			$HTML .= "\t\t<td class=\"tdheader\"> Notaris </td>\n";
		}
        $HTML .= "\t</tr>\n";

        if ($sts == 1 || $sts == 2) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 3 || $sts == 4) {
            if ($this->getDocumentApproval($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>\n";
        return $HTML;
    }

    public function headerPerUser($sts) {
        global $a, $m, $find, $find_notaris, $tgl1, $tgl2;
        $srcTxt = $find;

        $j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','find_notaris':'" . $find_notaris . "','tgl1':'" . $tgl1 . "','tgl2':'" . $tgl2 . "'}");
        $link = "view/BPHTB/monitoring/svc-list-notaris.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'4'}");
        $HTML = "

            <script>\n
            jQuery(document).ready(function($){\n
                $('#src-notaris-" . $sts . "').autocomplete({source:'" . $link . "', minLength:2});\n
            });</script>\n

                <form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        $HTML .= "<input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" 
            onclick=\"printToXLS('" . $j . "');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            Seleksi Berdasarkan 
                            <b>Tanggal Bayar</b> : 
                            <input type=\"text\" id=\"src-tgl1-$sts\" size=\"20\" value='$tgl1'> s.d
                            <input type=\"text\" id=\"src-tgl2-$sts\" size=\"20\" value='$tgl2'> 
                            <input type=\"hidden\" id=\"src-approved-$sts\"/> 
                            <b>Nama User</b> : <input type=\"text\" id=\"src-notaris-$sts\" value='$find_notaris' name=\"src-notaris\" size=\"30\"/>                                
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";
        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> ID SSPD </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nama Wajib Pajak</td> \n";

        if ($sts == 2) {
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Bayar </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Jumlah Pembayaran </td>\n";
			$HTML .= "\t\t<td class=\"tdheader\"> User / Notaris </td>\n";
        } else {
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Input </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Verifikasi </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Petugas Verifikasi </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Persetujuan </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Pejabat Persetujuan </td>\n";
            $HTML .= "\t\t<td class=\"tdheader\"> Jumlah Pembayaran </td>\n";
        }
		if(($sts == 4)||($sts == 5)){
			$HTML .= "\t\t<td class=\"tdheader\">User / Notaris </td>\n";
		}

        $HTML .= "\t</tr>\n";

        if ($sts == 1 || $sts == 2) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
        } elseif ($sts == 3 || $sts == 4 || $sts == 5) {
            if ($this->getDocumentApproval($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>\n";
        return $HTML;
    }


    public function headerRekapPerDesa($sts) {
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
        $HTML = "<script>\n
            jQuery(document).ready(function($){\n
                $('#kecamatan2').autocomplete({source:'" . $link . "', minLength:1});\n
                $('#kelurahan2').autocomplete({source:'" . $link2 . "', minLength:1});\n
            });\n
            </script>\n
                <form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
        // Seleksi Berdasarkan 
        //                     <b>Kecamatan</b> : 
        //                     <select name=\"kecamatan\" id=\"kecamatan\">
        //                     </select>

        //                     <b>Desa</b> : 
        //                     <select name=\"kelurahan\" id=\"kelurahan\">
        //                     </select>
        $HTML .= "
                    <b>Kecamatan</b> :
                    <input type =\"text\" name=\"kecamatan2\" id=\"kecamatan2\" placeholder=\"Kecamatan\">
                    
                    <b>Desa</b> : 
                    <input type =\"text\" name=\"kelurahan2\" id=\"kelurahan2\" placeholder=\"Desa\">

                            <input type=\"hidden\" id=\"src-approved-$sts\"/>                        
                            <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Nama Wajib Pajak</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Notaris</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Verifikasi</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Tanggal Pembayaran</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Jumlah Pembayaran</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan</td> \n";
        $HTML .= "\t\t<td class=\"tdheader\"> Desa</td> \n";


        $HTML .= "\t</tr>\n";

        if ($sts == 6) { #pembayaran
            if ($this->getDocumentPembayaran($sts, $dt)) {
                $HTML .= $dt;
            } else {
                $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
            }
        }
        $HTML .= "</table>\n";
        $HTML .="<script>
                    $(\"select#kecamatan\").change(function () {
                        showKelurahan();
                    })
                </script>";
        return $HTML;
    }
    
    public function getContent($sts) {
        $HTML = "";
        if ($sts == 1 || $sts == 3) {
            $HTML = $this->headerBerdasarUser($sts);
        }else if($sts == 6){
            $HTML = $this->headerRekapPerDesa($sts);
        } else {
            $HTML = $this->headerPerUser($sts);
        }
        return $HTML;
    }

    public function displayDataMonitoring($sts) {
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
		});
		
		</script>";
        echo "<div id=\"notaris-main-content\">\n";
        echo "\t<div id=\"notaris-main-content-inner\">\n";
        echo $konten;
        echo "\t</div>\n";
        echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
        echo $this->paging();
        echo "</div>\n";
    }

    function paging() {
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
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : "";
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : "";
$kec = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : "";
$kel = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";

$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;

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
?>
