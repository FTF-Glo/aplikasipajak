<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
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

// aldes
require_once($sRootPath . "approval-bphtb/qrcode.php");


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
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

function getAuthor($uname)
{
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

function getConfigValue($id, $key)
{
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

function mysql2json($mysql_result, $name)
{
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
		$json .= "{\n";
		for ($y = 0; $y < count($field_names); $y++) {
			$json .= "'$field_names[$y]' :	'" . addslashes($row[$y]) . "'";
			if ($y == count($field_names) - 1) {
				$json .= "\n";
			} else {
				$json .= ",\n";
			}
		}
		if ($x == $rows - 1) {
			$json .= "\n}\n";
		} else {
			$json .= "\n},\n";
		}
	}
	$json .= "]\n}";
	return ($json);
}

function getData($iddoc)
{
	global $data, $DBLink, $dataNotaris;
	$query = sprintf("SELECT * , DATE_FORMAT(DATE_ADD(B.CPM_TRAN_DATE, INTERVAL %s DAY), '%%d-%%m-%%Y') as EXPIRED
                            FROM cppmod_ssb_doc A inner join cppmod_ssb_tranmain B on 
                            A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
                            WHERE  
                            B.CPM_TRAN_FLAG=0 AND 
                            A.CPM_SSB_ID='%s'", getConfigValue("1", 'TENGGAT_WAKTU'), $iddoc);


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
function getDocId($a, $idssb)
{

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

	$query2 = "select payment_code from $dbTable where id_switching='" . $idssb . "'";
	//echo $query2;exit;
	$r = mysqli_query($DBLinkLookUp, $query2);
	if ($r === false) {
		echo "Error select1:" . $query2;
		die("Error");
	} else {
		$hasil = mysqli_fetch_array($r);
		//$dok = str_pad($hasil['id_ssb'],6,'0',STR_PAD_LEFT);
		//$dok = $dok.'-11';
		$dok = $hasil['payment_code'];
	}
	return $dok;
}

function getjenishak($js)
{
	global $DBLink;
	$id = $appID;
	$qry = "select * from cppmod_ssb_jenis_hak where CPM_KD_JENIS_HAK = '" . $js . "'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CPM_JENIS_HAK'];
	}
}

function getBPHTBPayment($no)
{
	global $idssb, $DBLink;
	$hitungaphb = getConfigValue("1", 'HITUNG_APHB');
	$configAPHB = getConfigValue("1", 'CONFIG_APHB');
	$configPengenaan = getConfigValue("1", 'CONFIG_PENGENAAN');
	$data = getData($idssb);
	
	$querys = "SELECT nm_lengkap FROM tbl_reg_user_notaris WHERE userId = '$data->CPM_SSB_AUTHOR'";
	$author_fullname = mysqli_query($DBLink, $querys);
	$check_notaris = mysqli_num_rows($author_fullname);
	
	if($check_notaris == 0){
		$nm_lengkap_notaris = '';

	}else{
		while($rows = mysqli_fetch_assoc($author_fullname)){
		$nm_lengkap_notaris = $rows['nm_lengkap'];
		}
	}
	
	
	//var_dump($nm_lengkap_notaris);die;
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

	$a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
	$b = strval($h);
	$npop = 0;
	if ($jh == '15') {
		$npop = $b;
	} else {
		if ($b <= $a) $npop = $a;
		else $npop = $b;
	}
	$npkp = $npop - strval($NPOPTKP);
	if ($npkp <= 0) {
		$npkp = 0;
	}
	$jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
	$hbphtb = ($npop - strval($NPOPTKP)) * 0.05;
	$aphb = 0;
	$hbphtb_pengenaan = 0;
	$hbphtb_aphb = 0;
	if (($jh == 4) || ($jh == 5) || ($jh == 31)) {
		if ($configPengenaan == '1') {
			$hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
			$jmlByr = $hbphtb - ($hbphtb_pengenaan);
		} else {
			$hbphtb_pengenaan = 0;
			$jmlByr = $hbphtb;
		}
	} else if ($jh == 7) {
		if ($configAPHB == '1') {
			$p = explode("/", $aphbt);
			$aphb = $p[0] / $p[1];
			$hbphtb_pengenaan = 0;
			if ($hitungaphb == '1') {
				$hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05 * $aphb;
			} else if ($hitungaphb == '2') {
				$hbphtb_aphb = (($npop - strval($NPOPTKP)) * 0.05) - (($npop - strval($NPOPTKP)) * 0.05 * $aphb);
			} else if ($hitungaphb == '3') {
				$hbphtb = $npop * $aphb;
				$hbphtb_aphb = ($hbphtb - strval($NPOPTKP)) * 0.05;
			} else if ($hitungaphb == '0') {
				$hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
			}
		} else {
			$hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
		}
		$jmlByr = $hbphtb_aphb;
	}

	$tp = strval($p);
	if ($tp != 0) $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

	if ($denda > 0) {
		$jmlByr = $jmlByr + $denda;
	} else {
		$jmlByr = $jmlByr;
		$hbphtb = 0;
	}
	if ($jmlByr < 0) $jmlByr = 0;
	$total_temp = $jmlByr;
	$hasil = $npop . "," . $npkp . "," . $hbphtb . "," . $hbphtb_pengenaan . "," . $hbphtb_aphb . "," . $total_temp . "," . $jmlByr;
	$pilihhitung = explode(",", $hasil);

	//echo $hasil;exit;
	return $pilihhitung[$no];
}

// aldes
function getGWNew($noktp, $nop, &$paid)
{
	// return 'test';
	global $a;

	$iErrCode = 0;
	$a = $a;
	//LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
	$DbName = getConfigValue($a, 'BPHTBDBNAME');
	$DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
	$DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
	$DbTable = getConfigValue($a, 'BPHTBTABLE');
	$DbUser = getConfigValue($a, 'BPHTBUSERNAME');
	// return $DbName;
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
	// aldes	
	$extrafield = ',approval_status, approval_msg, approval_qr_text';
	$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID {$extrafield} FROM $DbTable WHERE id_switching = '" . $noktp . "' ORDER BY saved_date DESC limit 1  ";
	$paid = "";
	$res = mysqli_query($LDBLink, $query);
	if ($res === false) {
		print_r("Pengambilan data Gagal");
		echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
		return "Tidak Ditemukan";
	}
	$json = new Services_JSON();
	$data = $json->decode(mysql2json($res, "data"));
	for ($i = 0; $i < count($data->data); $i++) {
		$paid = (array)$data->data[$i];
		return (array)$data->data[$i];
	}

	$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID {$extrafield} FROM $DbTable WHERE op_nomor = '" . $nop . "'";
	$paid = "";
	$res = mysqli_query($LDBLink, $query);
	if ($res === false) {
		print_r("Pengambilan data Gagal");
		echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error($LDBLink) . "' );</script>";
		return "Tidak Ditemukan";
	}
	$json = new Services_JSON();
	$data = $json->decode(mysql2json($res, "data"));
	for ($i = 0; $i < count($data->data); $i++) {
		$paid = (array)$data->data[$i];
		return (array)$data->data[$i];
	}

	// SCANPayment_CloseDB($LDBLink);
	return "Tidak Ditemukan";
}
// end aldes

function getHTML($iddoc, $draf, $lampiran, $ket_lamp)
{
	global $uname, $NOP, $sRootPath, $DBLink;
	$data = getData($iddoc);
	$kode_bayar = getDocId($ids, $iddoc);
	$tanggal_dibuat = date("d-m-Y", strtotime($data->CPM_SSB_CREATED));
	
	$querys = "SELECT nm_lengkap FROM tbl_reg_user_notaris WHERE userId = '$data->CPM_SSB_AUTHOR'";
	$author_fullname = mysqli_query($DBLink, $querys);
	$check_notaris = mysqli_num_rows($author_fullname);
	
	if($check_notaris == 0){
		$nm_lengkap_notaris = '';

	}else{
		while($rows = mysqli_fetch_assoc($author_fullname)){
			$nm_lengkap_notaris = $rows['nm_lengkap'];
		}
	}
	
	
	//tambahan no pendaftaran
	$no_pendaftaran = $data->CPM_NO_PENDAFTARAN ? $data->CPM_NO_PENDAFTARAN : ' - ';
	//var_dump($check_notaris);die;

	$jenishak = "<span class=\"document-x\">Jual Beli</span>";
	$npop = 0;
	$pwaris = "-";
	$jenishakprint = getjenishak($data->CPM_OP_JENIS_HAK);
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
      } */
	$pengenaan_config = getConfigValue("1", 'PENGENAAN_HIBAH_WARIS');
	#$npop = $b;
	if ($b < $a)
		$npop = $a;
	else
		$npop = $b;

	$n = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);
	$tf = $data->CPM_PAYMENT_TIPE_PECAHAN;
	$a = $npop - strval($NPOPTKP) > 0 ? $npop - strval($NPOPTKP) : 0;
	$m = ($a) * 0.05;
	$a = $a * 0.05;

	if ($n != 0) {
		$m = $m - $m * ($n * 0.01);
	} else if (!empty($tf)) {
		$tfp = explode('/', $tf);
		$m = $m * ($tfp[0] / $tfp[1]);
	}
	$b = $npop - $NPOPTKP;
	if ($b < 0)
		$b = 0;
	if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_BPHTB_TU))) {
		$a = $data->CPM_OP_BPHTB_TU;
		$m = floatval($a);
	}

	if (($data->CPM_OP_JENIS_HAK == '4') || ($data->CPM_OP_JENIS_HAK == '5') || ($data->CPM_OP_JENIS_HAK == '3') || ($data->CPM_OP_JENIS_HAK == '31')) {
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
			  <td valign=\"top\" class=\"document-x\">Berdasakan peraturan KDH No : " . $data->CPM_PAYMENT_TIPE_KHD_NOMOR . "</td>
			</tr>";
	}
	$fieldkhususwaris = "";
	if ($data->CPM_PAYMENT_TIPE == 5) {
		//$pecahan=explode('/', $data->CPM_PAYMENT_TIPE_PECAHAN);
		$fieldkhususwaris = "<tr>
        <td align=\"left\">Khusus untuk Waris dan Hibah BPHTB yang terutang </td>
        <td><font size=\"-2\">" . $data->CPM_PAYMENT_TIPE_PECAHAN . " X angka 4</font></td>
        <td align=\"left\">5.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"133\" align=\"right\">" . number_format($m, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>";
	}
	$infoReject = "";
	if ($data->CPM_TRAN_STATUS == '4') {
		$infoReject = "\n<br><br><div id=\"info-reject\" class=\"msg-info\"> <strong>Ditolak karena :</strong>
						<br>" . str_replace("\n", "<br>", $data->CPM_TRAN_INFO) . "</div>\n";
	}

	$data->CPM_OP_LUAS_TANAH = number_format(intval($data->CPM_OP_LUAS_TANAH), 0, '', '');
	$data->CPM_OP_NJOP_TANAH = number_format(intval($data->CPM_OP_NJOP_TANAH), 0, '', '');
	$data->CPM_OP_LUAS_BANGUN = number_format(intval($data->CPM_OP_LUAS_BANGUN), 0, '', '');
	$data->CPM_OP_NJOP_BANGUN = number_format(intval($data->CPM_OP_NJOP_BANGUN), 0, '', '');

	$pAPHB = 1;
	$tampilAPHB = 0;
	if (($data->CPM_OP_JENIS_HAK == '33') || ($data->CPM_OP_JENIS_HAK == '7')) {

		$aphbt = $data->CPM_APHB;
		$p = explode("/", $aphbt);
		$pAPHB = $p[0] / $p[1];
		$tampilAPHB = $data->CPM_APHB;
		if (($data->CPM_PENGENAAN != null) || ($data->CPM_PENGENAAN != 0)) {
			$pengenaanAPHB = number_format((intval($m)) * $pAPHB, 0, ',', '.');
		} else {
			$pengenaanAPHB = number_format((intval($m * $data->CPM_PENGENAAN * 0.01)) * $pAPHB, 0, ',', '.');
		}
	}
	$pengenaanAPHB = 0;

	// ALDES
	$susah = getGWNew($data->CPM_SSB_ID, $data->CPM_OP_NOMOR, $dataGW);
	// die(var_dump($dataGW));
	// ALDES
	
	//tambahan no pendaftaran
	$no_pendaftaran = $data->CPM_NO_PENDAFTARAN ? $data->CPM_NO_PENDAFTARAN : ' - ';
	
	$tahun_sekarang = date("Y");

	if (getConfigValue("1", 'DENDA') == "1") {
		$ket_denda = "<tr>
		<td></td>
        <td align=\"left\">Denda</td>
        <td><font size=\"-2\">" . $data->CPM_PERSEN_DENDA . "% angka 4</font></td>
        <td align=\"left\">7.</td>
        <td>
			<table>
				<tr>
					<td width=\"20\" align=\"left\">Rp. </td>
					<td width=\"133\" align=\"right\">" . number_format($data->CPM_DENDA, 0, ',', '.') . "</td>
				</tr>
			</table>
		</td>
      </tr>";
	} else {
		$ket_denda = "";
	}

	$html = "<table width=\"900\" border=\"0\" cellpadding=\"2\">
  <tr>
    <td colspan=\"3\" align=\"center\" width=\"720\" height=\"100\" style=\"vertical-align:middle\" ><strong><font size=\"+4\"><br />PEMERINTAH KABUPATEN PRINGSEWU</font><font size=\"+2\"><br />

    </font></strong><strong><font size=\"+14\">BADAN PENDAPATAN DAERAH </font><br/> <font size=\"-2\">Kompleks Perkantoran Pemerintah Daerah Kabupaten Pringsewu</font><br/></strong></td>
     
  </tr>

  

	
  <tr>
    <td colspan=\"4\">
	
	<table width=\"100%\" border=\"0\" cellpadding=\"1\">
		<tr>
			<td align=\"center\" colspan=\"4\" >
				<strong><font size=\"+2\" style=\"text-decoration:underline\">BERITA ACARA PENELITIAN SSPD BPHTB</font></strong>
			</td >	
			
		</tr>
		<tr>
			<td align=\"center\" colspan=\"4\" width=\"500\">
				<font>No: </font>
			</td >	
		</tr>
		<br>
		
		<tr>
			<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
			<td align=\"left\" colspan=\"4\" width=\"658\">
				<p width=\"500\">Pada hari ini <font style=\"color:white\">________</font> Tanggal <font style=\"color:white\">________</font> Bulan <font style=\"color:white\">________</font> Tahun " . $tahun_sekarang . " telah dilakukan penelitian atas Surat Setoran Pajak Daerah (SSPD) Bea Perolehan Hak atas Tanah dan Bangunan (BPHYB) dengan rician sebagai berikut.</p>
			</td >	
		</tr>

	<br>
      <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>No. Registrasi SSPD BPPHTB : " . strtoupper($no_pendaftaran) . "</b></td>
      </tr>	
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>Nama Wajib Pajak : " . strtoupper($data->CPM_WP_NAMA) . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>NOP : " . strtoupper($data->CPM_OP_NOMOR) . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>PBB Atas Nama : " . strtoupper($data->CPM_WP_NAMA) . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>Letak Objek Pajak : " . trim(strip_tags($data->CPM_OP_LETAK)) . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>Jenis BPHTB : " . $jenishakprint . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>SERTIPIKAT : " . $data->CPM_OP_NMR_SERTIFIKAT . "</b></td>
      </tr>
	<br>	  
	  <tr>
	  		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
        <td width=\"658\" align=\"left\"><b>PPAT : " . strtoupper($nm_lengkap_notaris) . "</b></td>
      </tr>	  

	  
    </table>	
	</td>
  </tr>
  
	<tr>
		<br>
		<td align=\"left\" colspan=\"4\" width=\"50\"></td >	
		<td align=\"left\" colspan=\"4\" width=\"658\">
			<p width=\"500\">Harga Transaksi/ Nilai pasar yang tercantum dalam SSPD sebesar Rp. " . number_format(getBPHTBPayment(1), 0, ',', '.') . "</p>
			<p width=\"500\">Pajak Bea Perolehan Hak atas Tanah dan Bagunan (BPHTB) sebesar Rp. " . number_format(intval(getBPHTBPayment(5)), 0, ',', '.') . "</p>
			<br>
			<p width=\"500\">Batas Waktu berlakunya Berita Acara ini selama 1 (satu) Bulan, terhitung mulai dari tanggal ditandatangani Berita Acara ini.</p>
		</td >	
	</tr>

<tr>
	<td width=\"708\">
			<br>
			<br>
		<table width=\"100%\" border=\"0\">		  
			<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  	<font size=\"+1\">PETUGAS PENELITI,<font style=\"color:white\">__________</font></font>		  
			  </td>
	        </tr>
			<tr>
		      <td align=\"center\" colspan=\"3\" style=\"height:87px;\">
			  <font size=\"+1\">&nbsp;&nbsp;&nbsp;</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"200\">		
			  	<font size=\"+1\" ><b>RESTI ALFINA, A.AMd</b></font>			  
			  </td>
	        </tr>
			<tr>
			  <td ></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"+1\" ><b>NIP. 19950111 201902 2 008</b></font>
			  </td>
			  <td ></td>
	        </tr>
		</table>
	</td>

	<br>
	<br>
  </tr>  

  
  <tr>
    <td width=\"354\" height=\"90\">				
		<table width=\"100%\" border=\"0\">		  
			<br>
			<br>
			<br>

			<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  	<font size=\"+1\"><font style=\"color:white\">_____________</font>KEPALA BIDANG PENDATAN<br><font style=\"color:white\">_____________</font>BADAN PENDAPATAN DAERAH<br><font style=\"color:white\">_____________</font>KABUPATENG PRINGSEWU,</font>		  
			  </td>
	        </tr>
			<tr>
		      <td align=\"center\" colspan=\"3\" style=\"height:87px;\">
			  <font size=\"+1\">&nbsp;&nbsp;&nbsp;</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"200\">		
			  	<font size=\"+1\" ><b>ALI ALHAMIDI, SP</b></font>			  
			  </td>
	        </tr>
			<tr>
			  <td ></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"+1\" ><b>NIP. 19950111 201902 2 008</b></font>
			  </td>
			  <td ></td>
	        </tr>
		</table>
	</td>

	
	<td width=\"354\">
		<br>
		<br>
		<br>

		<table width=\"100%\" border=\"0\">		  
			<tr>
		      <td align=\"center\" height=\"10\" colspan=\"3\">
			  	<font size=\"+1\"><font style=\"color:white\">_____________</font>KASUBID PENDATAAN,<br><font style=\"color:white\">_____________</font>PENDAFTARAN,<br><font style=\"color:white\">_____________</font>DAN PENETAPAN,</font>		  
			  </td>
	        </tr>
			<tr>
		      <td align=\"center\" colspan=\"3\" style=\"height:87px;\">
			  <font size=\"+1\">&nbsp;&nbsp;&nbsp;</font>			  	  
			  </td>
	        </tr>
			<tr>
		      <td></td>
			  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"200\">		
			  	<font size=\"+1\" ><b>YUSUF HABIBI UMAR, S.Kom</b></font>			  
			  </td>
	        </tr>
			<tr>
			  <td ></td>
		      <td align=\"center\" valign=\"top\">
			  	<font size=\"+1\" ><b>NIP. 19950111 201902 2 008</b></font>
			  </td>
			  <td ></td>
	        </tr>
		</table>
	</td>
  </tr>  
</table>
<table width=\"720\" border=\"0\" cellpadding=\"0\">";


	$html .= "";

	$html .= "
	      </table>";

	return $html;
}
// die(getHTML());
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);
$v = count($q);

$NOP = "";
// create new PDF document
$pagelayout = array(210, 500);
$pdf = new TCPDF("P", PDF_UNIT, $pagelayout, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('');
$pdf->SetSubject('');
$pdf->SetKeywords('');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(5, 5, 5);
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

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFont('segoeui', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
$HTML = "";

$style = array(
	'position' => '', #for barcode
	'align' => 'C',
	'stretch' => true,
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
//print_r($q);exit;
for ($i = 0; $i < $v; $i++) {
	$idssb = $q[$i]->id;
	$uname = ""; //$q[0]->uname;
	$draf = $q[$i]->draf;
	$appID = base64_decode($q[$i]->axx);
	$fileLogo = getConfigValue("1", 'FILE_LOGO');
	$resolution = array(215, 350);
	$pdf->AddPage('P', $resolution);
	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);

	$lampiran_wp = "Lembar 1";
	$lampiran_wp_ket = "Untuk<br>WAJIB PAJAK";
	$lampiran_bpprd = "Lembar 2";
	$lampiran_bpprd_ket = "Untuk<br>PPAT/Notaris<br>sebagai arsip";
	$lampiran_bpprd_pen = "Lembar 3";
	$lampiran_bpprd_pen_ket = "Untuk<br>Kepala Kantor<br>Pertanahan";
	$lampiran_bpprd_BPN = "Lembar 4";
	$lampiran_bpprd_BPN_ket = "Untuk<br>BPPRD<br>dalam proses penilaian";
	$lampiran_bpprd_2 = "Lembar 5";
	$lampiran_bpprd_ket_2 = "Untuk<br>Bank Yang Ditunjuk/<br>Bendahara Penerimaan";
	$lampiran_notaris = "Lembar 6";
	$lampiran_notaris_ket = "Untuk<br>Bank yang<br>ditunjuk/Bendahara";
	$HTML1 = getHTML($idssb, $draf, $lampiran_wp, $lampiran_wp_ket);
	$pdf->writeHTML($HTML1, true, false, false, false, '');

	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 17, 8, 18, '', '', '', '', false, 300, '', false);
	$pdf->SetAlpha(0.3);
	$pdf->ln(1);
	$pdf->SetAlpha(1);
	
	
	//$pdf->AddPage('P', $resolution);
	if ($draf == 1) {
		$bottomMargin = 0;
		for ($x = 1; $x <= 9; $x++) {
			$rightMargin = 0;
			for ($y = 1; $y <= 7; $y++) {
				$pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 15, 35, '', '', '', true, false, 0, '', false);
				$rightMargin += 35;
			}
			$bottomMargin += 35;
		}
		$rightMargin = 0;
		for ($y = 1; $y <= 7; $y++) {
			$pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 17, 35, '', '', '', true, false, 0, '', false);
			$rightMargin += 35;
		}
	} else if ($draf == 0) {
		#$pdf->Image($sRootPath . 'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false); // dimatikan untuk tulisan salinan dulu
	}
}

// -----------------------------------------------------------------------------
//Close and output PDF document
$pdf->Output($NOP . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
