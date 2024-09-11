<?php
// error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);
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
            return '';
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
        // var_dump($sts);
        global $DBLink, $json, $a, $m, $s, $find, $find_notaris, $page, $find_nop, $find_kcmtn, $find_klrhn, $find_jnshak, $tgl1, $tgl2;
        $sts = $s;
        $srcTxt = $find;

        $tgl1 = !$tgl1 ? date('Y') . '-01-01' : $tgl1;
        $tgl2 = !$tgl2 ? date('Y-m-d') : $tgl2;

        $where = [];

        if ($srcTxt != "") $where[] = "(A.CPM_WP_NAMA LIKE '" . $srcTxt . "%'
                                OR A.CPM_WP_NOKTP LIKE '" . $srcTxt . "%'  OR A.CPM_SSB_AUTHOR LIKE '" . $srcTxt . "%'
                            )";
        if ($find_kcmtn != "")
            $where[] = "A.CPM_OP_KECAMATAN LIKE '%" . $find_kcmtn . "%'";
        if ($find_klrhn != "")
            $where[] = "A.CPM_OP_KELURAHAN LIKE '%" . $find_klrhn . "%'";
        if ($find_nop != "")
            $where[] = "A.CPM_OP_NOMOR LIKE '" . $find_nop . "%' ";
        if ($find_jnshak != "")
            $where[] = "A.CPM_OP_JENIS_HAK = $find_jnshak";

        $where[] = "B.CPM_TRAN_FLAG='0'";


        if ($sts == 2) {
            $where[] = "(A.CPM_STATUS_NTPD = 1)";
        } else {
            $where[] = "(A.CPM_STATUS_NTPD = 0)";
        }

        $where[] = "A.CPM_WP_NAMA IS NOT NULL";

        $where = (count($where) > 0) ? "AND " . implode(" AND ", $where) : '';
        //yang lama
        // ODER BY
        $orderyby = 'ORDER BY B.CPM_TRAN_DATE DESC';


        // yang lama ridwan
        // $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" .
        //     $sts . " AND  B.CPM_TRAN_FLAG=0 $where {$orderyby} LIMIT " . $this->page . "," . $this->perpage;

        // $query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=" . $sts . " $where {$orderyby} LIMIT " . $this->page . "," . $this->perpage;

        $query = "SELECT * FROM sw_ssb.cppmod_ssb_doc A JOIN  sw_ssb.cppmod_ssb_tranmain B ON A.CPM_SSB_ID=B.CPM_TRAN_SSB_ID INNER JOIN gw_ssb.ssb C ON C.id_switching = A.CPM_SSB_ID  WHERE B.CPM_TRAN_STATUS= 5 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT " . $this->page . "," . $this->perpage;

        // var_dump($query);
        // die($query);

        //yang lama
        $qry = "SELECT COUNT(*) AS TOTALROWS FROM sw_ssb.cppmod_ssb_doc A JOIN  sw_ssb.cppmod_ssb_tranmain B ON A.CPM_SSB_ID=B.CPM_TRAN_SSB_ID INNER JOIN gw_ssb.ssb C ON C.id_switching = A.CPM_SSB_ID  WHERE B.CPM_TRAN_STATUS= 5 $where";

        if ($sts == 5) {
            $qry = "SELECT COUNT(*) AS TOTALROWS FROM sw_ssb.cppmod_ssb_doc A JOIN  sw_ssb.cppmod_ssb_tranmain B ON A.CPM_SSB_ID=B.CPM_TRAN_SSB_ID INNER JOIN gw_ssb.ssb C ON C.id_switching = A.CPM_SSB_ID  WHERE B.CPM_TRAN_STATUS= 5 $where";
        }

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
            // var_dump($data->data[$i]);
            // die;
            $par1 = $params . "&f=f338-mod-display-dispenda&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $kurang_bayar = $params . "a=aBPHTB&m=modNotarisBPHTB&f=funcKurangBayar&validasikb=1&idssb=" . $data->data[$i]->CPM_SSB_ID;
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            if ($this->userGroup == 2)
                if (($data->data[$i]->CPM_TRAN_READ == "") || ($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == ""))
                    $class = "tdbodyNew";
            if (
                $data->data[$i]->CPM_SSB_ID == '0a47f6b79fddd134857b2d3ebcb7b2f6' ||
                $data->data[$i]->CPM_SSB_ID == '8ebb877e961c42c4fd38bc9517a40155'
            ) {
                $ccc = 0;
            } else {
                $ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN, $data->data[$i]->CPM_OP_NJOP_BANGUN, $data->data[$i]->CPM_OP_LUAS_TANAH, $data->data[$i]->CPM_OP_NJOP_TANAH, $data->data[$i]->CPM_OP_HARGA, $data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN, $data->data[$i]->CPM_OP_JENIS_HAK, $data->data[$i]->CPM_OP_NPOPTKP, $data->data[$i]->CPM_PENGENAAN, $data->data[$i]->CPM_APHB, $data->data[$i]->CPM_DENDA) - $penguranganBayar;
            }
            if ($data->data[$i]->CPM_PAYMENT_TIPE == '2') {
                $ccc = $data->data[$i]->CPM_KURANG_BAYAR - $penguranganBayar;
            }
            $HTML .= "\t<div><tr>\n";
            if ($sts == 2 || $sts == 5)
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">
        	        <input id=\"check-all-" . $i . "\" name=\"check-all\" type=\"checkbox\" value=\"" . $data->data[$i]->CPM_SSB_ID . "\" /></td>\n";
            if (($sts == 2) || ($sts == 5))
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_OP_NOMOR . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($par1) . "\">" . $data->data[$i]->CPM_WP_NAMA . "</a></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_LETAK . "</td>\n";

            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_SSB_AUTHOR . "</td>\n";
            if ($sts == 5) {
                $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 . "</td>\n";
            }
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $this->get_jenis_hak($data->data[$i]->CPM_OP_JENIS_HAK) . "</td>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $data->data[$i]->CPM_OP_KECAMATAN . "</td>\n";
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
            if ($sts == 5) {
                if ($data->data[$i]->bphtb_collectible <  $data->data[$i]->bphtb_dibayar) {

                    // $HTML .= "\t\t<td class=\"" . $class . "\"><a href=\"main.php?param=" . base64_encode($kurang_bayar) . "\">  <input type=\"button\" value=\"Kurang Bayar\" /></a></td> \n";
                } else {
                    $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">-</td> \n";
                }
            }
            $HTML .= "\t</tr></div>\n";
        }
        $dat = $HTML;
        return true;
    }


    public function headerContentApprove($sts, $draf = false)
    {

        global $find, $find_notaris, $find_nop, $find_kcmtn;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
                <style>
                    .form-filtering {
                        background-color: #fff;
                        padding: 20px 20px;
                        
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                
                    }
                </style>

                <div class=\"p-2\">
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
                                        <label>Nama WP/Users</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-approved-$sts\" name=\"src-approved\" value=\"$find\"/>
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>NOP/NIK</label>
                                        <input class=\"form-control\" type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" value=\"$find_nop\"/>
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Kecamatan</label>
                                        " . $this->dropdown_kecamatan($sts) . " 
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Kelurahan</label>
                                        " . $this->dropdown_kelurahan($sts) . " 
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Jenis Hak</label>
                                        " . $this->dropdown_jnshak($sts) . " 
                                            
                                    </div>
                                    

                                    <div class=\" form-group col-md-12\"> 
                                        <input type=\"button\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>   
                                        <input type=\"button\" class=\"btn btn-info\" value=\"Terbitkan NTPD\" id=\"btn-print\" name=\"btn-print\" onclick=\"submitNtpd(3);\"/>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                </form>
        ";
        // $HTML .= "Pencarian Berdasarkan <b>Nama WP/Users :</b>
		// 		<input type=\"text\" id=\"src-approved-$sts\" name=\"src-approved\" size=\"30\" value=\"$find\"/>		     
        //         <b>Kecamatan\n&nbsp;&nbsp;&nbsp;\n:</b>
                
        //         " . $this->dropdown_kecamatan($sts) . "
        //         <input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
               
        //         <br>
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         <b>NOP/NIK :</b>
        //         <input type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" size=\"30\" value=\"$find_nop\"/>
        //         <b>Kelurahan :</b>
        //         " . $this->dropdown_kelurahan($sts) . "
        //         <br>
        //         <input type=\"button\" value=\"Terbitkan NTPD\" id=\"btn-print\" name=\"btn-print\" onclick=\"submitNtpd(3);\"/>
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
        //         <b>Jenis Perolehan :</b>
        //         " . $this->dropdown_jnshak($sts) . " <br>
		// 		<!--<input type=\"button\" value=\"Download\" id=\"btn-download\" name=\"btn-download\" onclick=\"toExcel();\" />-->\n</form>\n";

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
        if ($sts == 5) {
            // $HTML .= "\t\t<td class=\"tdheader\" >Kurang Bayar</td>\n";
        }
        $HTML .= "\t</tr>\n";

        if ($this->getDocument($sts, $dt)) {
            $HTML .= $dt;
        } else {
            $HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
        }
        $HTML .= "</table>\n";
        return $HTML;
    }


    public function headerContent($sts)
    {
        // var_dump($sts);die;  
        global $find, $find_notaris, $find_nop, $find_kcmtn, $tgl1, $tgl2;
        $tgl1 = !$tgl1 ? date('Y') . '-01-01' : $tgl1;
        $tgl2 = !$tgl2 ? date('Y-m-d') : $tgl2;
        $HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
                <style>
                    .form-filtering {
                        background-color: #fff;
                        padding: 20px 20px;
                        
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                
                    }
                </style>

                <div class=\"p-2\">
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
                                        <label>Nama WP/Users</label>
                                            <input type=\"text\" class=\"form-control\" id=\"src-approved-$sts\" name=\"src-approved\" value=\"$find\"/> 
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>NOP/NIK</label>
                                        <input class=\"form-control\" type=\"text\" id=\"src-nop-$sts\" name=\"src-nop\" value=\"$find_nop\"/>
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Kecamatan</label>
                                        " . $this->dropdown_kecamatan($sts) . " 
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Kelurahan</label>
                                        " . $this->dropdown_kelurahan($sts) . " 
                                            
                                    </div>
                                    <div class=\" form-group col-md-3\"> 
                                        <label>Jenis Hak</label>
                                        " . $this->dropdown_jnshak($sts) . " 
                                            
                                    </div>

                                    <div class=\"form-group col-md-3\">
										<label>Tanggal Awal </label>
										<div style=\"display: flex; align-items: center;\">
                                            <input type=\"text\" class=\"form-control\" id=\"src-tgl1\" name=\"src-tgl1\"value='" . $tgl1 . "'>
											<p style=\"margin-left:10px; margin-bottom: 0rem;\">s/d</p>
										</div>
									</div>
									<div class=\"form-group col-md-3\">
										<label>Tanggal Akhir</label>
										<div>
											
                                            <input type=\"text\" class=\"form-control\" id=\"src-tgl2\" name=\"src-tgl2\" size=10 value='" . $tgl2 . "'>
										</div>
									</div>

                                    

                                    <div class=\" form-group col-md-12\"> 
                                        <input type=\"button\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\"/>
                                        <input type=\"button\" class=\"btn btn-info\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataToPDF(3);\"/>
                                        <input type=\"button\" class=\"btn btn-info\" value=\"Cetak NTPD\" id=\"btn-print\" name=\"btn-print\" onclick=\"printDataNTPD(3);\"/>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </form>";
     

        $HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        $HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div>
                 <div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
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
    function dropdown_jnshak($sts)
    {
        global $DBLink, $find_jnshak;
        $qry = "SELECT * FROM cppmod_ssb_jenis_hak order by CPM_KD_JENIS_HAK";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo mysqli_error($DBLink);
        }
        $return = "<select class=\"form-control\" name=\"src-jnshak\" id=\"src-jnshak-{$sts}\">";

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
$tgl1 = @isset($_REQUEST['tgl1']) ? $_REQUEST['tgl1'] : false;
$tgl2 = @isset($_REQUEST['tgl2']) ? $_REQUEST['tgl2'] : false;
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
