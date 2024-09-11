<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");

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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);


$dataNotaris = "";
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

function getAuthor($uname) {
    global $DBLink, $appID;
    $id = $appID;
    $qry = "select nm_lengkap from tbl_reg_user_notaris where userId = '" . $uname . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo mysqli_error($DBLink);
    }

    $num_rows = mysqli_num_rows($res);
    if ($num_rows == 0)
        return $uname;
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['nm_lengkap'];
    }
}

function getConfigValue($id, $key) {
    global $DBLink, $appID;
    $id = $appID;
    //$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
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
function getUrutKB() {
    global $DBLink;
    $id = $_REQUEST['a'];
    $qry = "select MAX(CPM_NO_KURANG_BAYAR) from cppmod_ssb_doc where CPM_KURANG_BAYAR IS NOT NULL";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$urut="";
    while ($row = mysqli_fetch_assoc($res)) {
	if ($row['CPM_NO_KURANG_BAYAR']!=null){
		$urut=$row['CPM_NO_KURANG_BAYAR']+1;
	}else{
	    $urut=1;
	}
        return $urut ;
    }
}
function mysql2json($mysql_result, $name) {
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json.="{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json.="'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json.="\n";
            } else {
                $json.=",\n";
            }
        }
        if ($x == $rows - 1) {
            $json.="\n}\n";
        } else {
            $json.="\n},\n";
        }
    }
    $json.="]\n}";
    return($json);
}

function getData($iddoc) {
    global $data, $DBLink, $dataNotaris;
    $query = sprintf("SELECT * , DATE_FORMAT(A.CPM_SSB_CREATED, '%%d-%%m-%%Y') as COM_SSB_CREATED,
					DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'", getConfigValue("1", 'TENGGAT_WAKTU_KB'), $iddoc);

    $res = mysqli_query($DBLink, $query);
    if ($res === false) {
        echo $query . "<br>";
        echo mysqli_error($DBLink);
    }
    $json = new Services_JSON();
    $dataNotaris = $json->decode(mysql2json($res, "data"));
    $dt = $dataNotaris->data[0];
    return $dt;
}

function getNOKTP($noktp, $nop) {
    global $DBLink;
    $day = getConfigValue("1", "BATAS_HARI_NPOPTKP");
    $qry = "select max(CPM_SSB_CREATED) as mx from cppmod_ssb_doc  where 
	CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
	and CPM_OP_NOMOR <> '{$nop}'";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        print_r($qry);
        return false;
    }

    if (mysqli_num_rows($res)) {
        $num_rows = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            if ($row["mx"]) {

                return true;
            }
        }
    }
    return false;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$idssb = $q->id;
$uname = $q->uname;
$draf = $q->draf;
$setuju = isset($q->setuju) ? $q->setuju : 0;

function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP) {
       
        $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
        $b = strval($h);
        $npop = 0;
        if ($b < $a)
            $npop = $a;
        else
            $npop = $b;

        $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
        $tp = strval($p);
        if ($tp != 0)
            $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

        if ($jmlByr < 0)
            $jmlByr = 0;

        return $jmlByr;
    }
	
	function getsudahdibayar($idssb) {
    global $DBLink;
    $qry = "select * from cppmod_ssb_doc where CPM_SSB_ID ='". $idssb ."'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$before="";
    while ($rw = mysqli_fetch_assoc($res)) {
		$NPOPTKP = strval($rw['CPM_OP_NPOPTKP']);
        $a = strval($rw['CPM_OP_LUAS_BANGUN']) * strval($rw['CPM_OP_NJOP_BANGUN']) + strval($rw['CPM_OP_LUAS_TANAH']) * strval($rw['CPM_OP_NJOP_TANAH']);
        $b = strval($rw['CPM_OP_HARGA']);
        $npop = 0;
			if ($b < $a)
                $npop = $a;
            else
                $npop = $b;

        $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
        $tp = strval($rw['CPM_PAYMENT_TIPE_PENGURANGAN']);
            if ($tp != 0)
                $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

            $ccc =getBPHTBPayment($rw['CPM_OP_LUAS_BANGUN'], $rw['CPM_OP_NJOP_BANGUN'], $rw['CPM_OP_LUAS_TANAH'], $rw['CPM_OP_NJOP_TANAH'], $rw['CPM_OP_HARGA'], $rw['CPM_PAYMENT_TIPE_PENGURANGAN'], $rw['CPM_OP_JENIS_HAK'], $rw['CPM_OP_NPOPTKP']);
            if (($rw['CPM_PAYMENT_TIPE'] == '2') && (!is_null($rw['CPM_OP_BPHTB_TU']))) {
                $ccc = $rw['CPM_OP_BPHTB_TU'];
            }
	
	}
	$before=$ccc;
	return $before;
}
function bulanromawi($bulan)
{
$bulan_angka=array('01','02','03','04','05','06','07','08','09','10','11','12');
$bulankite=array('I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII');
$konversi=str_ireplace($bulan_angka,$bulankite,$bulan);
return $konversi;
}	
function getBPHTBPayment_all($no) {
		global $idssb;
		$hitungaphb = getConfigValue("1", 'HITUNG_APHB');
		$configAPHB = getConfigValue("1",'CONFIG_APHB');
		$configPengenaan = getConfigValue("1",'CONFIG_PENGENAAN');
		$data = getData($idssb);
		$lb = $data->CPM_OP_LUAS_BANGUN;
		$nb = $data->CPM_OP_NJOP_BANGUN;
		$lt = $data->CPM_OP_LUAS_TANAH;
		$nt = $data->CPM_OP_NJOP_TANAH;
		$h  = $data->CPM_OP_HARGA;
		$p  = $data->CPM_PAYMENT_TIPE_PENGURANGAN;
		$jh = $data->CPM_OP_JENIS_HAK;
		$NPOPTKP = $data->CPM_OP_NPOPTKP;
		$phw = $data->CPM_PENGENAAN;
		$denda = $data->CPM_DENDA;
		$aphbt = $data->CPM_APHB;
		
		$a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
		$b = strval($h);
		$npop = 0;
		if($jh=='15'){
			$npop=$b;
		}else{
			if ($b <= $a) $npop = $a; else $npop = $b;
		}
		$npkp = $npop-strval($NPOPTKP);
		if($npkp<=0){
			$npkp = 0;
		}
		$jmlByr = ($npop-strval($NPOPTKP))*0.05;
		$hbphtb = ($npop-strval($NPOPTKP))*0.05;
		$aphb=0;
		$hbphtb_pengenaan = 0;
		$hbphtb_aphb = 0;
		if(($jh==4)||($jh==5)||($jh==31)){
			if($configPengenaan=='1'){
				$hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
				$jmlByr= $hbphtb-($hbphtb_pengenaan);
			}else{
				$hbphtb_pengenaan = 0;
				$jmlByr= $hbphtb;
			}
			
		}else if($jh==7){
			if($configAPHB=='1'){
				$p=explode("/",$aphbt);
				$aphb=$p[0]/$p[1];
				$hbphtb_pengenaan = 0;
				if($hitungaphb=='1'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
				}else if($hitungaphb=='2'){
					$hbphtb_aphb = (($npop-strval($NPOPTKP))*0.05)-(($npop-strval($NPOPTKP))*0.05 * $aphb);
				}else if($hitungaphb=='3'){
					$hbphtb = $npop*$aphb;
					$hbphtb_aphb = ($hbphtb-strval($NPOPTKP))* 0.05;
				}else if($hitungaphb=='0'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
				}
			}else{
				$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
			}
			$jmlByr= $hbphtb_aphb;
		}
		
		$tp = strval($p);
		if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
		
		if($denda>0){
			$jmlByr = $jmlByr+$denda;
		}else{
			$jmlByr = $jmlByr;
			$hbphtb = 0;
		}
		if ($jmlByr < 0) $jmlByr = 0;
		$total_temp = $jmlByr;
		$hasil = $npop.",".$npkp.",".$hbphtb.",".$hbphtb_pengenaan.",".$hbphtb_aphb.",".$total_temp.",".$jmlByr;
		$pilihhitung=explode(",",$hasil);
		
		//echo $hasil;exit;
		return $pilihhitung[$no];
	}
	
function getjenishak($js){
	global $DBLink;
    $id = $appID;
    $qry = "select * from cppmod_ssb_jenis_hak where CPM_KD_JENIS_HAK = '" . $js . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return str_pad($row['CPM_KD_JENIS_HAK'],2,"0",STR_PAD_LEFT).". ".$row['CPM_JENIS_HAK'];
    }
}
function getHTML($iddoc) {
    global $uname, $NOP, $setuju, $draf;

    $data = getData($iddoc);
    $jenishak = "<span class=\"document-x\">Jual Beli</span>";
	$jenishakprint = getjenishak($data->CPM_OP_JENIS_HAK);
    $npop = 0;
    $pwaris = "-";
    //print_r($data);
    $a = strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH);
    $b = strval($data->CPM_OP_HARGA);
    //$NPOPTKP =  getConfigValue("1",'NPOPTKP_STANDAR');
    $NPOPTKP = $data->CPM_OP_NPOPTKP;
    $typeR = $data->CPM_OP_JENIS_HAK;
    $type = $data->CPM_PAYMENT_TIPE;
    $NOP = $data->CPM_OP_NOMOR;
    $c1 = "";
    $c2 = "";
    $c3 = "";
    $c4 = "";
	$nmpjbsah="";
	$nippjbsah="";
	$nourutKB="";
	//$nourutKB=getUrutKB();
	if($data->CPM_KURANG_BAYAR!=null){
		$nmpjbsah=strtoupper(getConfigValue("1", 'NAMA_PJB_PENGESAH'));
		$nippjbsah="NIP: ". getConfigValue("1", 'NIP_PJB_PENGESAH');
	}
	else{
		$nmpjbsah="";
		$nippjbsah="";
	}
    if ($type == '1')
        $c1 = "X";
    if ($type == '2')
        $c2 = "X";
    if ($type == '3')
        $c3 = "X";
    if ($type == '4')
        $c4 = "X";

    /* if (($typeR==4) || ($typeR==6)){
      $NPOPTKP =  getConfigValue("1",'NPOPTKP_WARIS');
      }

      if(getNOKTP($data->CPM_WP_NOKTP,$data->CPM_OP_NOMOR)) {
      $NPOPTKP = 0;
      } */


   $pengenaan_config=getConfigValue("1",'PENGENAAN_HIBAH_WARIS');

    if ($b <= $a)
        $npop = $a;
    else
        $npop = $b;
    $npop = $data->CPM_OP_NPOP;
    $n = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);
    $a = $npop - strval($NPOPTKP) > 0 ? $npop - strval($NPOPTKP) : 0;
    $m = ($a) * 0.05;
    $a = $a * 0.05;

    if ($n != 0)
        $m = $m - $m * ($n * 0.01);
    $b = $npop - $NPOPTKP;
    if ($b < 0)
        $b = 0;
    if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_BPHTB_TU))) {
        $a = $data->CPM_OP_BPHTB_TU;
        $m = $a;
    }

    if ($data->CPM_OP_JENIS_HAK == '1')
        $jenishak = "<span class=\"document-x\">01. Jual beli</span>";
    if ($data->CPM_OP_JENIS_HAK == '2')
        $jenishak = "<span class=\"document-x\">02. Tukar Menukar</span>";
    if ($data->CPM_OP_JENIS_HAK == '3')
        $jenishak = "<span class=\"document-x\">03. Hibah</span>";
    if ($data->CPM_OP_JENIS_HAK == '4')
        $jenishak = "<span class=\"document-x\">04. Hibah Wasiat</span>";
    if ($data->CPM_OP_JENIS_HAK == '5')
        $jenishak = "<span class=\"document-x\">05. Waris</span>";
    if ($data->CPM_OP_JENIS_HAK == '6')
        $jenishak = "<span class=\"document-x\">06. Pemasukan dalam perseroan</span>";
    if ($data->CPM_OP_JENIS_HAK == '7')
        $jenishak = "<span class=\"document-x\">07. APHB</span>";
    if ($data->CPM_OP_JENIS_HAK == '8')
        $jenishak = "<span class=\"document-x\">08. Lelang</span>";
    if ($data->CPM_OP_JENIS_HAK == '9')
        $jenishak = "<span class=\"document-x\">09. Putusan hakim</span>";
    if ($data->CPM_OP_JENIS_HAK == '10')
        $jenishak = "<span class=\"document-x\">10. Penggabungan usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '11')
        $jenishak = "<span class=\"document-x\">11. Peleburan usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '12')
        $jenishak = "<span class=\"document-x\">12. Pemekaran usaha</span>";
    if ($data->CPM_OP_JENIS_HAK == '13')
        $jenishak = "<span class=\"document-x\">13. Hadiah</span>";
    if ($data->CPM_OP_JENIS_HAK == '14')
        $jenishak = "<span class=\"document-x\">14. Jual Beli Khusus perolehan hak RSS dan KPR Bersubsidi</span>";
    if ($data->CPM_OP_JENIS_HAK == '21')
        $jenishak = "<span class=\"document-x\">21. Pemberian hak baru sebagai kelanjutan pelepasan hak</span>";
    if ($data->CPM_OP_JENIS_HAK == '22')
        $jenishak = "<span class=\"document-x\">22. Pemberian hak baru diluar pelepasan hak</span>";

    if (($data->CPM_OP_JENIS_HAK == '4') || ($data->CPM_OP_JENIS_HAK == '5')) {
        $pwaris = number_format((($npop - strval($data->CPM_OP_NPOPTKP)) * 0.05) * 0.5, 2, '.', ',');
    }
    $typepayment = "<span  class=\"document-x\">Penghitungan Wajib Pajak</span>";
    $fieldTambahan = "";
    if ($data->CPM_PAYMENT_TIPE == 2) {
        if ($data->CPM_PAYMENT_TIPE_SURAT == 1)
            $typepayment = "<span class=\"document-x\">STPD BPHTB</span>";
        if ($data->CPM_PAYMENT_TIPE_SURAT == 2)
            $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar</span>";
        if ($data->CPM_PAYMENT_TIPE_SURAT == 3)
            $typrpayment = "<span class=\"document-x\">SKPD Kurang Bayar Tambahan</span>";
        $fieldTambahan = "<tr>
			   <td valign=\"top\" class=\"document-x\">Nomor : " . $data->CPM_PAYMENT_TIPE_SURAT_NOMOR . "</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Tanggal : " . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Bedasarkan peraturan Bupati Kab. Cianjur No. 6 Tahun 2011</td>
			</tr>";
    }
    $infoReject = "";
    if ($data->CPM_TRAN_STATUS == '4') {
        $infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena :</strong>
						<br>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . "</div>\n";
    }
    $data->CPM_OP_LUAS_TANAH = number_format($data->CPM_OP_LUAS_TANAH, 0, '', '');
    $data->CPM_OP_NJOP_TANAH = number_format($data->CPM_OP_NJOP_TANAH, 0, '', '');
    $data->CPM_OP_LUAS_BANGUN = number_format($data->CPM_OP_LUAS_BANGUN, 0, '', '');
    $data->CPM_OP_NJOP_BANGUN = number_format($data->CPM_OP_NJOP_BANGUN, 0, '', '');
	$bphtb_before=getsudahdibayar($data->CPM_IDSSB_KURANG_BAYAR);
	$kode_bayar = getDocId("aBPHTB",$iddoc);
	
	$pAPHB=1;
	$tampilAPHB=0;
	if(($data->CPM_OP_JENIS_HAK == '33')||($data->CPM_OP_JENIS_HAK == '7')){
		
			$aphbt=$data->CPM_APHB;
			$p=explode("/",$aphbt);
			$pAPHB=$p[0]/$p[1];
			$tampilAPHB=$data->CPM_APHB;
		if(($data->CPM_PENGENAAN!=null) || ($data->CPM_PENGENAAN!=0)){
		$pengenaanAPHB=number_format((intval($m))*$pAPHB, 0, ',', '.');
		}else{
		$pengenaanAPHB=number_format((intval($m*$data->CPM_PENGENAAN*0.01))*$pAPHB, 0, ',', '.');
		}
	}
	$pengenaanAPHB=0;
	if(getConfigValue("1",'DENDA')=="1"){
	$ket_denda="<tr>
		<td align=\"left\"></td>
        <td align=\"left\" width=\"400\">Denda</td>
        <td><font size=\"-2\">".$data->CPM_PERSEN_DENDA."% angka 4</font></td>
        <td align=\"left\">10.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format($data->CPM_DENDA, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
     </tr>";
	}else{
		$ket_denda="";
	}
	
    $html = "<table width=\"900\" border=\"1\" cellpadding=\"2\">
  <tr>
    <td width=\"142\" rowspan=\"2\">&nbsp;</td>
    <td colspan=\"2\" align=\"center\" width=\"425\" height=\"65\" style=\"vertical-align:middle\"><strong><font size=\"+2\">SURAT SETORAN PAJAK DAERAH</font><font size=\"+2\"><br />
BEA PEROLEHAN HAK ATAS TANAH DAN BANGUNAN <br />
    </font></strong><strong><font size=\"+1\">(SSPD-BPHTB)</font></strong></td>
    <td width=\"142\" align=\"center\" style=\"vertical-align:center\" rowspan=\"2\">Jenis Perolehan: &nbsp;<br><strong>".$jenishakprint."<br>KURANG BAYAR</strong></td>
  </tr>
  <tr>
    <td colspan=\"2\" align=\"center\">BERFUNGSI SEBAGAI SURAT PEMBERITAHUAN OBJEK PAJAK<br />
    PAJAK BUMI DAN BANGUNAN (SPOP PBB)</td>
  </tr>
  <tr>
    <td colspan=\"4\">
	<br><br>
	<table width=\"100%\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"7\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>A.</strong></font></td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"120\">Nama Wajib Pajak</td>
        <td width=\"8\">:</td>
        <td width=\"400\" colspan=\"11\" ><span class=\"document-x\"><b>" . strtoupper($data->CPM_WP_NAMA) . "</b></span></td>
      </tr>	  
      <tr>
        <td align=\"right\">2.</td>
        <td>NPWP</td>
        <td>:</td>
        <td  colspan=\"11\"><span class=\"document-x\">" . $data->CPM_WP_NPWP . "</span></td>
      </tr>
      <tr>
        <td align=\"right\">3.</td>
        <td>Alamat Wajib Pajak</td>
        <td>:</td>
        <td colspan=\"11\"><span class=\"document-x\">" . trim(strip_tags($data->CPM_WP_ALAMAT)) . "</span></td>
      </tr>
      <tr>
        <td align=\"right\">4.</td>
        <td>No. KTP</td>
        <td>:</td>
        <td width=\"225\" colspan=\"11\"><span class=\"document-x\">" . $data->CPM_WP_NOKTP . "</span></td>		
      </tr>
      <tr>
        <td align=\"right\">5.</td>
        <td>Kelurahan/Desa</td>
        <td>:</td>
        <td width=\"150\"><span class=\"document-x\">" . $data->CPM_WP_KELURAHAN . "</span></td>
		
		<td align=\"right\" width=\"18\">6.</td>
        <td width=\"50\"> RT/RW</td>
        <td width=\"8\">:</td>
		<td width=\"50\"><span class=\"document-x\">" . $data->CPM_WP_RT . "/" . $data->CPM_WP_RW . "</span></td>
		
		
        <td align=\"right\" width=\"18\">7.</td>
        <td width=\"100\"> Kecamatan</td>
        <td width=\"8\">:</td>
        <td width=\"145\"><span class=\"document-x\">" . $data->CPM_WP_KECAMATAN . "</span></td>
				
      </tr>
      <tr>
        <td align=\"right\">8.</td>
        <td>Kabupaten/Kota</td>
        <td>:</td>
        <td width=\"150\" ><span class=\"document-x\">" . $data->CPM_WP_KABUPATEN . "</span></td>
		<td>&nbsp;</td>		
		<td>&nbsp;</td>		
		<td>&nbsp;</td>		
		<td>&nbsp;</td>		
        <td align=\"right\" width=\"18\">9.</td>
        <td> Kode Pos</td>
        <td>:</td>
        <td><span class=\"document-x\">" . $data->CPM_WP_KODEPOS . "</span></td>
      </tr>      
    </table>	
	</td>
  </tr>
  <tr>
    <td colspan=\"4\">
	<br><br>
	<table width=\"780\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>B.</strong></font></td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"185\">Nomor Objek Pajak (NOP) PBB</td>
        <td width=\"8\">:</td>
        <td width=\"155\" colspan=\"6\"><span class=\"document-x\">" . $data->CPM_OP_NOMOR . "</span></td>		
        <td width=\"150\" colspan=\"2\"> Nama Wajib Pajak Lama</td>
        <td width=\"8\">:</td>
        <td width=\"175\" colspan=\"6\"><span class=\"document-x\"><b>" . $data->CPM_WP_NAMA_LAMA . "</b></span></td>
      </tr>
      <tr>
        <td align=\"right\">2.</td>
        <td>Letak tanah dan atau bangunan</td>
        <td>:</td>
        <td colspan=\"6\" ><span class=\"document-x\">" . trim(strip_tags($data->CPM_OP_LETAK)) . "</span></td>
        <td width=\"150\" colspan=\"2\"> Nama WP sesuai Sertifikat</td>
        <td width=\"8\">:</td>
        <td width=\"175\" colspan=\"6\"><span class=\"document-x\"><b>" . $data->CPM_WP_NAMA_CERT . "</b></span></td>
      </tr>
      <tr>
        <td align=\"right\">3.</td>
        <td>Kelurahan/Desa</td>
        <td>:</td>
        <td width=\"210\"><span class=\"document-x\">" . $data->CPM_OP_KELURAHAN . "</span></td>       
        <td width=\"18\" align=\"right\">4. </td>
        <td width=\"100\"> RT/RW</td>
        <td width=\"8\">:</td>
        <td width=\"145\" colspan=\"6\"><span class=\"document-x\">" . $data->CPM_OP_RT . "/" . $data->CPM_OP_RW . "</span></td>
      </tr>
      <tr>
        <td align=\"right\">5.</td>
        <td>Kecamatan</td>
        <td>:</td>
        <td><span class=\"document-x\">" . $data->CPM_OP_KECAMATAN . "</span></td>       
        <td align=\"right\">6. </td>
        <td> Kabupaten/Kota</td>
        <td>:</td>
        <td colspan=\"6\" ><span class=\"document-x\">" . $data->CPM_OP_KABUPATEN . "</span></td>
      </tr>
	  <tr>
	  	<td colspan=\"14\">&nbsp;</td>
	  </tr>
	  <tr>
        <td colspan=\"14\">Penghitungan NJOP</td>        
      </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td colspan=\"14\" valign=\"middle\" height=\"110\"><br>
		<table width=\"665\" border=\"1\" cellspacing=\"0\" cellpadding=\"4\">          
          <tr>
            <td align=\"center\" valign=\"middle\" width=\"100\">Uraian</td>
            <td align=\"center\" valign=\"middle\" width=\"185\" colspan=\"2\">Luas </td>
            <td align=\"center\" valign=\"middle\" width=\"185\" colspan=\"2\">NJOP PBB /m² </td>
            <td colspan=\"2\" align=\"center\" valign=\"middle\" width=\"195\" colspan=\"2\">Luas x NJOP PBB /m²</td>
          </tr>
          <tr>
            <td align=\"center\" valign=\"middle\">Tanah / Bumi</td>
			<td align=\"right\" valign=\"middle\" width=\"25\">7.</td>
            <td align=\"center\" valign=\"middle\" width=\"160\">			
				<table border=\"0\">
					<tr>
						<td width=\"130\">" . number_format(($data->CPM_OP_LUAS_TANAH), 0, ',', '.') . "</td>
						<td width=\"25\" align=\"center\"> m²&nbsp;</td>
					</tr>
				</table>
			</td>
			<td align=\"right\" valign=\"middle\" width=\"25\">9.</td>
            <td align=\"right\" valign=\"middle\" width=\"160\">
				<table>
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"140\" align=\"center\">" . number_format(intval($data->CPM_OP_NJOP_TANAH), 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>            
			<td align=\"right\" valign=\"middle\" width=\"25\">11.</td>
			<td align=\"right\" valign=\"middle\" width=\"170\">
				<table border=\"0\">
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"135\" align=\"right\">" . number_format(intval($data->CPM_OP_NJOP_TANAH) * ($data->CPM_OP_LUAS_TANAH), 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>
          </tr>     
		  <tr>
            <td align=\"center\" valign=\"middle\">Bangunan</td>
			<td align=\"right\" valign=\"middle\">8.</td>
            <td align=\"center\" valign=\"middle\">
				<table border=\"0\">
					<tr>
						<td width=\"130\">" . number_format(($data->CPM_OP_LUAS_BANGUN), 0, ',', '.') . "</td>
						<td width=\"25\" align=\"center\"> m²&nbsp;</td>
					</tr>
				</table>
			</td>
            <td align=\"right\" valign=\"middle\">10.</td>			
			<td align=\"right\" valign=\"middle\">
				<table>
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"140\" align=\"center\">" . number_format(intval($data->CPM_OP_NJOP_BANGUN), 0, ',', '.') . "</td>
					</tr>
				</table>				
			</td>            
			<td align=\"right\" valign=\"middle\">12.</td>			
			<td align=\"right\" valign=\"middle\">
				<table>
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"135\" align=\"right\">" . number_format(intval($data->CPM_OP_NJOP_BANGUN) * ($data->CPM_OP_LUAS_BANGUN), 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>
          </tr>     
		  <tr>
            <td colspan=\"5\" align=\"right\" valign=\"middle\">NJOP PBB &nbsp;</td>
            <td align=\"right\" valign=\"middle\">13.</td>
            <td align=\"right\" valign=\"middle\" id=\"t3\" class=\"document-x\">
				<table>
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"135\" align=\"right\">" . number_format(intval($data->CPM_OP_NJOP_BANGUN) * ($data->CPM_OP_LUAS_BANGUN) + intval($data->CPM_OP_NJOP_TANAH) * ($data->CPM_OP_LUAS_TANAH), 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>
          </tr>
		  <tr>
            <td colspan=\"5\" align=\"right\" valign=\"middle\">Harga Transaksi / Nilai Pasar &nbsp;</td>
            <td align=\"right\" valign=\"middle\">14.</td>
            <td align=\"right\" valign=\"middle\" id=\"t3\" class=\"document-x\">
				<table>
					<tr>
						<td width=\"25\">Rp. </td>
						<td width=\"135\" align=\"right\">" . number_format($data->CPM_OP_HARGA, 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>
          </tr>
        </table>
		<br>
		</td>
      </tr>     
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td width=\"25\" align=\"right\">15.</td>
        <td width=\"270\" ><font>Jenis perolehan hak atas tanah atau bangunan</font></td>
        <td>:</td>
        <td colspan=\"11\" width=\"380\" >$jenishak</td>
      </tr>
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td align=\"right\">16.</td>
        <td>Nomor sertifikat tanah</td>
        <td>:</td>
        <td colspan=\"11\">" . $data->CPM_OP_NMR_SERTIFIKAT . "</td>
      </tr>     
    </table>	
	</td>
  </tr>
  <!--<tr>
    <td colspan=\"4\">	
	<table width=\"500\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>C.</strong></font></td>
        <td width=\"528\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
        <td width=\"22\" align=\"left\">:</td>
        <td width=\"135\" align=\"right\">
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"90\" align=\"right\">" . number_format(strval($data->CPM_OP_NPOP), 0, ',', '.') . "</td>
				</tr>
			</table>			
		</td>
      </tr>      
    </table></td>
  </tr>-->
  <tr>
    <td colspan=\"4\">
	<br>
	<table width=\"800\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>C.</strong></font></td>
        <td colspan=\"4\" align=\"left\"><strong>Perhitungan BPHTB</strong></td>
        </tr>
      <tr>
        <td width=\"400\" align=\"left\"><font size=\"-1\">Nilai Perolehan Objek Pajak (NPOP) memperhatikan nilai pada B.13 dan B.14</font></td>
        <td width=\"100\">&nbsp;</td>
        <td width=\"25\" align=\"left\">1.</td>
        <td width=\"80\" align=\"right\">
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\"> " . number_format(getBPHTBPayment_all(0), 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>
      <tr>
        <td align=\"left\" width=\"400\">Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
        <td width=\"100\">&nbsp;</td>
        <td align=\"left\">2.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\"> " . number_format($NPOPTKP, 0, ',', '.') . "</td>
				</tr>
			</table>			
		</td>
      </tr>
      <tr>
        <td width=\"400\" align=\"left\">Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
        <td width=\"100\" ><font size=\"-2\">angka 1 - angka 2</font></td>
        <td align=\"left\">3.</td>
        <td>
			<table border=\"0\">
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format(getBPHTBPayment_all(1), 0, ',', '.') . "</td>
				</tr>
			</table>	
		</td>
      </tr>
      <tr>
        <td align=\"left\">Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
        <td><font size=\"-2\">5% angka 3</font></td>
        <td align=\"left\">4.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format(getBPHTBPayment_all(2), 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>
	  <tr>
        <td align=\"left\"><font size=\"-1\">Pengenaan ".$pengenaan_config."% karena Waris/Hibah wasiat/pemberian hak pengelolaan</font></td>
        <td><font size=\"-2\">".$pengenaan_config."% angka 4</font></td>
        <td align=\"left\">5.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format(getBPHTBPayment_all(3), 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>
	  <tr><td></td>
        <td align=\"left\"><font size=\"-1\">APHB  ".$tampilAPHB."</font></td>
        <td><font size=\"-2\"></font></td>
        <td align=\"left\">7.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format(getBPHTBPayment_all(4), 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>
	  
	 <tr><td></td>
        <td align=\"left\" width=\"400\">Bea Perolehan atas Hak Tanah dan Bangunan yang sudah dibayar</td>
        <td><font size=\"-2\"></font></td>
        <td align=\"left\">8.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format($data->CPM_KURANG_BAYAR_SEBELUM, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
     </tr>
	 <tr>
		<td align=\"left\"></td>
        <td align=\"left\" width=\"400\">Bea Perolehan atas Hak Tanah dan Bangunan yang masih harus dibayar</td>
        <td><font size=\"-2\">angka 7 - angka 8</font></td>
        <td align=\"left\">9.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"120\" align=\"right\">" . number_format($data->CPM_KURANG_BAYAR, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
     </tr>
	 ".$ket_denda."
	  
    </table>	
	</td>
  </tr>
  <tr>
    <td colspan=\"4\">
	<table width=\"100%\" border=\"0\" cellpadding=\"1\">
	<tr>
		<td width=\"18\">&nbsp;</td>
		<td colspan=\"3\" width=\"390\">&nbsp;</td>
		<td align=\"left\"><font size=\"-1\">Dengan Angka</font></td>
		<td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"137\" align=\"right\">" . number_format($data->CPM_KURANG_BAYAR, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>	
	  </tr>
	 <tr>
		<td width=\"20\">&nbsp;</td>
		<td colspan=\"5\">
			<br>
			<table width=\"650\" border=\"0\" cellpadding=\"1\">
			  <tr>
				<td align=\"left\" width=\"90\">(Dengan huruf)</td>
				<td>
					<table border=\"1\" width=\"575\">
						<tr>
							<td>&nbsp;&nbsp;<font size=\"-2\">" . strtoupper(SayInIndonesian(number_format($data->CPM_KURANG_BAYAR, 0, ',', ''))) . " RUPIAH</font></td>
						</tr>
					</table>
				</td>        
			  </tr>
			</table>			
		</td>
	  </tr>
	  <tr>
		<td width=\"18\"><font size=\"+1\"><strong>D.</strong></font></td>
		<td colspan=\"3\" width=\"432\"><strong>Jumlah Setoran Berdasarkan</strong> :</td>
		<td align=\"left\">&nbsp;</td>
		<td>&nbsp;</td>	
	  </tr>
	  <tr>
		<td>&nbsp;</td>
		<td width=\"30\"><table width=\"100%\" border=\"1\" cellpadding=\"1\">
		  <tr>
			<td align=\"center\">$c1</td>
		  </tr>
		</table></td>
		<td width=\"18\">a.</td>
		<td colspan=\"3\">Perhitungan Wajib Pajak</td>
	  </tr>
	  <tr>
		<td>&nbsp;</td>
		<td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
		  <tr>
			<td align=\"center\">$c2</td>
		  </tr>
		</table></td>
		<td>b.</td>
		<td width=\"220\">STPD BPHTB/SKPDBKB/SKPDBKBT</td>
		<td width=\"200\">Nomor :" . substr(getConfigValue("1", 'NO_KETETAPAN_KB'),0,4). "" . $data->CPM_NO_KURANG_BAYAR . "" . substr(getConfigValue("1", 'NO_KETETAPAN_KB'),4,9) . "" . bulanromawi(substr($data->CPM_SSB_CREATED,5,2)) . "/" . date('Y') . "</td>
		<td width=\"200\">Tanggal : " . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "</td>
	  </tr>
	  <tr>
		<td>&nbsp;</td>
		<td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
		  <tr>
			<td align=\"center\">$c3</td>
		  </tr>
    </table>
	</td>
    <td>c.</td>
    <td>Pengurangan menjadi </td>
    <td>" . $data->CPM_PAYMENT_TIPE_PENGURANGAN . "%</td>
    <td>Bedasarkan peraturan Bupati Kab. Cianjur No. 16 Tahun 2011</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><table width=\"100%\" border=\"1\" cellpadding=\"1\">
      <tr>
        <td align=\"center\">$c4</td>
      </tr>
    </table></td>
    <td>d.</td>
    <td colspan=\"3\">" . $data->CPM_PAYMENT_TIPE_OTHER . "</td>
  </tr> 
</table>
<br><br>
</td>
  </tr>
  <tr>
    <td width=\"190\" height=\"90\">				
		<table width=\"100%\" border=\"0\">
		    <tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  <font size=\"-2\">TELAH DIVERIFIKASI <br>BADAN PENGELOLAAN PAJAK DAERAH<br> KAB. CIANJUR</font>			  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"50\">&nbsp;</td>
	        </tr>	
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"165\">
			  	<font size=\"-2\" ><b>" . $nmpjbsah . "</b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-2\" ><b>" . $nippjbsah . "</b></font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
		
	</td>
	<td width=\"185\">
		<table width=\"100%\" border=\"0\">		  
			<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  	<font size=\"-3\">PPAT/NOTARIS/<br>KEPALA KANTOR LELANG/PEJABAT LELANG/<br>KEPALA KANTOR PERTANAHAN KOTA</font>		  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"54\">
			  <font size=\"-3\">&nbsp;&nbsp;&nbsp;</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"165\">&nbsp;			  	
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-5\" >Nama Lengkap, stempel dan tandatangan</font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
	</td>
	<td width=\"168\">
		<table width=\"100%\" border=\"0\">
			<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  <font size=\"-2\">DITERIMA OLEH <br>TEMPAT PEMBAYARAN BPHTB</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"65\">
			  <font size=\"-3\">&nbsp;&nbsp;&nbsp;Tanggal :</font>			  	  
			  </td>
	        </tr>			
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"145\">&nbsp;			  	
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-5\" >Nama Lengkap, stempel dan tandatangan</font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
	</td>
	<td width=\"165\">
		<table width=\"100%\" border=\"0\">
		 	<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  <font size=\"-2\">...........................,tgl........................</font><br> 
			  <font size=\"-2\">WAJIB PAJAK/PENYETOR</font>  
			  </td>
	        </tr>
			<tr>
		      <td align=\"left\" colspan=\"3\" height=\"60\">
			  <font size=\"-3\">&nbsp;&nbsp;&nbsp;</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td width=\"10\"></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"143\">&nbsp;			  	
			  </td>
			  <td width=\"10\"></td>
	        </tr>
			<tr>
			  <td width=\"10\"></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"-5\" >Nama Lengkap, stempel dan tandatangan</font>
			  </td>
			  <td width=\"10\"></td>
	        </tr>
		</table>
	</td>
  </tr> 
</table><br>
<table width=\"720\" border=\"0\" cellpadding=\"0\">";

    if ($setuju == 1) {
        $label_baris1 = "Tanggal Kadaluarsa";
        $isi_baris1 = $data->EXPIRED;

        $label_baris2 = "Tempat Pembayaran";
        $titikdua_baris2 = ":";
        $isi_baris2 = getConfigValue("1", "LOKET_PEMBAYARAN");
    } else {
        $label_baris1 = "Tempat Pembayaran";
        $isi_baris1 = getConfigValue("1", "LOKET_PEMBAYARAN");

        $label_baris2 = "";
        $titikdua_baris2 = "";
        $isi_baris2 = "";
    }

    $html .= "<tr>
			  <td width=\"130\"><font size=\"-2\">" . $label_baris1 . "</font></td>
			  <td width=\"10\"><font size=\"-2\">:</font></td>
			  <td width=\"350\"><font size=\"-2\">" . $isi_baris1 . "</font></td>
			  <td width=\"250\" align=\"left\"><font size=\"-3\"><i>No Reg " . $data->CPM_TRAN_SSB_ID . "</i></font></td>
			</tr>
			<tr>
			  <td width=\"130\"><font size=\"-2\">" . $label_baris2 . "</font></td>
			  <td width=\"10\"><font size=\"-2\">" . $titikdua_baris2 . "</font></td>
			  <td width=\"350\"><font size=\"-2\">" . $isi_baris2 . "</font></td>
			  <td width=\"250\" align=\"left\"><font size=\"-3\"><i>User ID : " . $data->CPM_SSB_AUTHOR . "</i></font></td>
			</tr>
			<tr>
			  <td width=\"130\"></td>
			  <td width=\"10\"></td>
			  <td width=\"350\"></td>
			  <td width=\"250\" align=\"left\"><font size=\"-3\"><i>Tanggal Cetak : " . date("d-m-Y") . "</i></font></td>
                          
			</tr>";
	if ($data->CPM_TRAN_STATUS=='5') {
	$html .= "	
			<tr>
              <td width=\"130\"></td>
			  <td width=\"10\"></td>
			  <td width=\"350\"></td>
              <td width=\"250\" align=\"left\"><font size=\"9\"><b><i>Kode bayar : " . $kode_bayar . "</i></b></font></td>    
            </tr>";
	}
	$html .= "
	      </table>";

    return $html;
}
function getDocId($a,$idssb){
     
        $dbName = getConfigValue($a, 'BPHTBDBNAME');
        $dbHost = getConfigValue($a, 'BPHTBHOSTPORT');
        $dbPwd = getConfigValue($a, 'BPHTBPASSWORD');
        $dbTable = getConfigValue($a, 'BPHTBTABLE');
        $dbUser = getConfigValue($a, 'BPHTBUSERNAME');
        $dbLimit = getConfigValue($a, 'TENGGAT_WAKTU');
        // Connect to lookup database
        SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
        //UNDZ --
		//$query2 = "select id_ssb from $dbTable where wp_nama='".$nama."'";
        
        $query2 = "select id_ssb from $dbTable where id_switching='".$idssb."'";
		//echo $query2;exit;
		$r = mysqli_query($DBLinkLookUp, $query2);
        if ($r === false) {
            echo "Error select1:" . $query2;
            die("Error");
        }else{
            $hasil = mysqli_fetch_array($r);
            $dok = str_pad($hasil['id_ssb'],8,'0',STR_PAD_LEFT);
        }
        return $dok;
}

$appID = base64_decode($q->axx);
$v = count($idssb);
$NOP = "";
// create new PDF document
$pagelayout = array(210, 500);
$pdf = new TCPDF("P", PDF_UNIT, $pagelayout, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('-');
$pdf->SetSubject('-');
$pdf->SetKeywords('-');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 1, 5);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(5);
//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);
// ---------------------------------------------------------
// set font
//$pdf->SetFont('helvetica', 'B', 20);
// add a page
//$pdf->AddPage();
//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetFont('segoeui', '', 10);
#$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";

for ($i = 0; $i < $v; $i++) {
    $fileLogo = getConfigValue("1", 'FILE_LOGO');
    $resolution = array(215, 350);
    $pdf->AddPage('P', $resolution);

    #$pdf->AddPage('P', 'FOLIO');
    //$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);
    $HTML = getHTML($idssb);
    //echo $HTML;

    $style = array('position' => 'fixed',
        'align' => 'C',
        'stretch' => false,
        'fitwidth' => true,
        'cellfitalign' => '',
        'border' => false,
        'hpadding' => 'auto',
        'vpadding' => 'auto',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false, //array(255,255,255),
        'text' => true,
        'font' => 'helvetica',
        'fontsize' => 8,
        'stretchtext' => 4
    );

    $pdf->writeHTML($HTML, true, false, false, false, '');
    $pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 13, -1, 25, '', '', '', '', false, 300, '', false);
    $pdf->SetAlpha(0.3);
    if (($draf == 1) && ($setuju!=1)) {
        $bottomMargin = 0;
        for ($x = 1; $x <= 9; $x++) {
            $rightMargin = 0;
            for ($y = 1; $y <= 7; $y++) {
                $pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 15, 35, '', '', '', true, false, 300, '', false);
                $rightMargin +=35;
            }
            $bottomMargin +=35;
        }
        $rightMargin = 0;
        for ($y = 1; $y <= 7; $y++) {
            $pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 17, 35, '', '', '', true, false, 300, '', false);
            $rightMargin +=35;
        }
    } 
    $pdf->ln(1);
    $pdf->SetAlpha(1);
    $pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
}

// -----------------------------------------------------------------------------
//Close and output PDF document
$pdf->Output($NOP . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
?>
