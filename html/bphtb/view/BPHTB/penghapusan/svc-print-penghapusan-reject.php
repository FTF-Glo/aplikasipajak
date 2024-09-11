<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'penghapusan', '', dirname(__FILE__))) . '/';
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
// QR Library Milon Added By d3Di
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/DNS2D.php");
require_once($sRootPath . "phpqrcode/src/Milon/Barcode/QRcode.php");

// aldes
require_once($sRootPath . "approval-bphtb/qrcode.php");

use \Milon\Barcode\DNS2D;

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
SCANPayment_ConnectToDB($DBLink_gw, $DBConn_gw, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

// var_dump($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

// exit;
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
$q = isset($_GET['q']) ? $_GET['q'] : "";
$q = base64_decode($q);
$data = json_decode($q, true);
//var_dump($data);

$data = array_map("unserialize", array_unique(array_map("serialize", $data)));
foreach ($data as $item) {
	$reason = isset($_GET['reason']) ? $_GET['reason'] : "";
	$reasonn = $_GET['reason'];
	$id = $item['id'];
	$pengguna = $item['uname'];
	$alasan = 'apa';

	//	$jam = date('');
	$query = "UPDATE sw_ssb.cppmod_ssb_doc SET  sdelete = '1', delete_by='$pengguna' ,delete_at = NOW() , keterangan = '$reasonn'  WHERE CPM_SSB_ID = '$id'";

	$res = mysqli_query($DBLink, $query);
	if ($res !== false) {
		$query1 = "INSERT INTO sw_ssb.cppmod_ssb_doc_hapus 
			SELECT *
			FROM sw_ssb.cppmod_ssb_doc where CPM_SSB_ID='$id'";

		$res1 = mysqli_query($DBLink, $query1);

		if ($res1 !== false) {
			$query3 = "DELETE 
			FROM sw_ssb.cppmod_ssb_doc where CPM_SSB_ID='$id'";

			$res3 = mysqli_query($DBLink, $query3);

			if ($res3 === false) {
				echo $query3 . "<br>";
				echo mysqli_error($DBLink);
			}
		}
		// if ($res1 !== false) {
		// }
	} else {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	//	var_dump($query);
	// die;
}
foreach ($data as $item) {
	$reason = isset($_GET['reason']) ? $_GET['reason'] : "";
	$reasonn = $_GET['reason'];
	$id = $item['id'];
	$pengguna = $item['uname'];
	$alasan = 'apa';

	//	$jam = date('');
	$query = "UPDATE gw_ssb.ssb SET  sdelete = '1', delete_by='$pengguna' ,delete_at = NOW() , keterangan = '$reasonn'  WHERE id_switching = '$id'";

	$res = mysqli_query($DBLink, $query);

	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	//	var_dump($query);
	// die;
}

//$q = base64_decode($q);


// var_dump();
// die;
// function encode64_base($data)
// {
// 	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
// }

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
function getbphtbBankLampung($billing_code)
{
	date_default_timezone_set('Asia/Jakarta');

	$expires_on      = strtotime(date('Y-m-t H:i:s', strtotime(date('Y') . '-12-01 23:59:59')));
	$now             = strtotime(date('Y-m-d H:i:s'));
	$diff            = ($expires_on - $now);
	$diff_in_minutes = round(abs($diff) / 60);
	$city_code       = '1801';
	// $url			=
	$url             = "http://117.53.45.7/mst/bank/services/inquiryqr";
	$url            .= "?city_code={$city_code}";
	$url            .= "&expired_duration={$diff_in_minutes}";
	$url            .= "&billing_code={$billing_code}";
	$url            .= "&type_tax_code=02";

	// var_dump($url);exit;
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL            => $url,
		CURLOPT_HTTPHEADER     => array("Channel-Id: QRIS"),
		CURLOPT_RETURNTRANSFER => 1,
	));

	// var_dump($url);
	// exit;
	$response = curl_exec($curl);
	curl_close($curl);
	$data = json_decode($response, true);
	// var_dump($response);
	return $data;
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
	// var_dump($iddoc);
	// die;
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
	// var_dump($dbHost);
	// var_dump($dbUser);
	// var_dump($dbPwd);
	// var_dump($dbName);
	// die;
	$query2 = "select payment_code from $dbTable where id_switching='" . $idssb . "'";
	// echo $query2;exit;
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
function getGwssb($a, $idssb)
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

	$query2 = "select * from $dbTable where id_switching='" . $idssb . "'";

	//echo $query2;exit;
	$r = mysqli_query($DBLinkLookUp, $query2);
	if ($r === false) {
		echo "Error select1:" . $query2;
		die("Error");
	} else {
		$hasil = mysqli_fetch_array($r);
		//$dok = str_pad($hasil['id_ssb'],6,'0',STR_PAD_LEFT);
		//$dok = $dok.'-11';
		$dok['payment_code'] = $hasil['payment_code'];
		$dok['expired_date'] = $hasil['expired_date'];
		$dok['bphtb_dibayar'] = $hasil['bphtb_dibayar'];
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
		return str_pad($row['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . ". " . $row['CPM_JENIS_HAK'];
	}
}
function getpaymentCode($id_ssb)
{
	global $DBLink_gw;
	// $query2 = "select payment_code from $dbTable where id_switching='" . $idssb . "'";
	$qry = "select * from ssb where id_switching  = '" . $id_ssb . "'";
	$res = mysqli_query($DBLink_gw, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink_gw);
	}
	$row = mysqli_fetch_assoc($res);
	// var_dump($qry);exit;
	return $row['payment_code'];
}

function getBPHTBPayment($no, $tipe_pecahan = null, $bphtb_bayar = null)
{
	global $idssb;
	$hitungaphb = getConfigValue("1", 'HITUNG_APHB');
	$configAPHB = getConfigValue("1", 'CONFIG_APHB');
	$configPengenaan = getConfigValue("1", 'CONFIG_PENGENAAN');
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

	$a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
	$b = strval($h);
	$npop = 0;
	if ($jh == '15') {
		$npop = $b;
	} else if ($jh == '8') {
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
			$npkp = ($npop * $aphb) - strval($NPOPTKP);
			if ($npkp <= 0) {
				$npkp = 0;
			}
			if ($hitungaphb == '1') {
				// $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05 * $aphb;
				$hbphtb_aphb = (($npop * $aphb) - strval($NPOPTKP)) * 0.05;
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
	// if ($jmlByr < 0) $jmlByr = 0;

	if ($jmlByr < 0) {
		$jmlByr = 0;
		$hbphtb = 0;
	}
	$total_temp = $jmlByr;
	if ($tipe_pecahan == 33) {
		$total_temp = $bphtb_bayar;
	}
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
	$dataGwssb = getGwssb($ids, $iddoc);
	// die(var_dump($data));
	$tanggal_dibuat = date("d-m-Y", strtotime($data->CPM_SSB_CREATED));

	$querys = "SELECT nm_lengkap FROM tbl_reg_user_notaris WHERE userId = '$data->CPM_SSB_AUTHOR'";
	$author_fullname = mysqli_query($DBLink, $querys);
	$check_notaris = mysqli_num_rows($author_fullname);

	if ($check_notaris == 0) {
		$nm_lengkap_notaris = $data->CPM_SSB_AUTHOR;
	} else {
		while ($rows = mysqli_fetch_assoc($author_fullname)) {
			if ($data->CPM_OP_JENIS_HAK == 8) {
				$nm_lengkap_notaris = '';
			} else {
				$nm_lengkap_notaris = $rows['nm_lengkap'];
			}
		}
	}


	$jenishak = "<span class=\"document-x\">Jual Beli</span>";
	$npop = 0;
	$pwaris = "-";
	$jenishakprint = getjenishak($data->CPM_OP_JENIS_HAK) . ($data->CPM_OP_JENIS_HAK == 7 ? " " . $data->CPM_APHB : "");
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

	$html = "<table width=\"900\" border=\"1\" cellpadding=\"2\">
  <tr>
    <td width=\"142\" rowspan=\"2\">&nbsp;</td>
    <td colspan=\"2\" align=\"center\" width=\"425\" height=\"100\" style=\"vertical-align:middle\" rowspan=\"2\"><strong><font size=\"+2\">SURAT SETORAN PAJAK DAERAH</font><font size=\"+2\"><br />
	BEA PEROLEHAN HAK ATAS TANAH DAN BANGUNAN <br />
	    </font></strong><strong><font size=\"+1\">(SSPD-BPHTB)</font><br/><font size=\"+2\">BADAN PENGELOLA PAJAK DAN RETRIBUSI <br/>DAERAH KABUPATEN PESAWARAN</font></strong></td>
	     <td width=\"142\" align=\"center\" style=\"vertical-align:center\">
			<table border=\"0\" width=\"100%\">
				<tr>
					<td bgcolor=\"#F0F0F0\" style=\"border-bottom:1px solid black\">
						<strong>" . $lampiran . "</strong>
					</td>
				</tr>
				<tr>
					<td>
						<font size=\"-2\"><strong>" . $ket_lamp . "</strong></font>
					</td>
				</tr>
			</table>
		 </td>
	  </tr>
	  <tr>
	    <td colspan=\"2\" align=\"center\"><br/><b>KODE BAYAR </b><font size=\"+1\"><br/><strong>" . $kode_bayar . "</font></strong></td>
	  </tr>
	  
	  
	  <tr>
		<td colspan=\"4\">
			<table width=\"100%\" border=\"0\" cellpadding=\"1\">
				<tr>
					<td colspan=\"2\" ><b><font size=\"-2\">" . strtoupper("Badan Pengelola Pajak dan Retribusi Daerah Kabupaten Lampung Selatan")   . "</font></b></td>
					 <td colspan=\"2\" border=\"0\" align=\"right\"><b><font size=\"-2\"> No. Pendaftaran : " . $no_pendaftaran . " </font></b></td> 
				</tr>
			</table>
		</td>
	  </tr>
	  
	  <tr>
	    <td colspan=\"4\">
		
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
	
	<table width=\"780\" border=\"0\" cellpadding=\"1\">
      <tr>
        <td width=\"18\" rowspan=\"6\" align=\"center\" valign=\"top\"><font size=\"+1\"><strong>B.</strong></font></td>
        <td width=\"18\" align=\"right\">1.</td>
        <td width=\"185\">Nomor Objek Pajak (NOP) PBB</td>
        <td width=\"8\">:</td>
        <td width=\"155\" colspan=\"6\" ><span class=\"document-x\"><b>" . $data->CPM_OP_NOMOR . "</b></span></td>		
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
        <td width=\"155\"><span class=\"document-x\">" . $data->CPM_OP_KELURAHAN . "</span></td>       
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
						<td width=\"130\">" . number_format(intval($data->CPM_OP_LUAS_TANAH), 0, ',', '.') . "</td>
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
						<td width=\"130\">" . number_format(intval($data->CPM_OP_LUAS_BANGUN), 0, ',', '.') . "</td>
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
						<td width=\"135\" align=\"right\">" . number_format(intval($data->CPM_OP_HARGA), 0, ',', '.') . "</td>
					</tr>
				</table>
			</td>
          </tr>
        </table>
		</td>
      </tr>     
      <tr>
        <td align=\"center\" valign=\"top\">&nbsp;</td>
        <td width=\"25\" align=\"right\">15.</td>
        <td width=\"270\" ><font>Jenis perolehan hak atas tanah atau bangunan</font></td>
        <td>:</td>
        <td colspan=\"11\" width=\"380\" >" . $jenishakprint . "</td>
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
					<td width=\"90\" align=\"right\">" . number_format(strval($data->CPM_SSB_AKUMULASI), 0, ',', '.') . "</td>
				</tr>
			</table>			
		</td>
      </tr>      
    </table></td>
  </tr>-->
  
	<tr>
		<td colspan=\"4\">
			<strong>C. PENGHITUNGAN BPHTB</strong> (Hanya diisi berdasarkan penghitungan Wajib Pajak)
		</td>
	</tr>
	<tr>
		<td colspan=\"2\" width=\"490\">
			 &nbsp; &nbsp;1. Nilai Perolehan Objek Pajak (NPOP)
		</td>
		<td width=\"25\">
			1
		</td>
		<td width=\"195\">
			Rp. " . number_format(getBPHTBPayment(0), 0, ',', '.') . "
		</td>
	</tr>
	<tr>
		<td colspan=\"2\" width=\"490\">
			 &nbsp; &nbsp;2. Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)
		</td>
		<td width=\"25\">
			2
		</td>
		<td width=\"195\">
			Rp. " . number_format($NPOPTKP, 0, ',', '.') . "
		</td>
	</tr>
	<tr>
		<td colspan=\"1\" width=\"380\">
			 &nbsp; &nbsp;3. Nilai Perolehan Objek Pajak Kena Pajak (NPOPKP)
		</td>
		<td width=\"110\">
			Angka 1 - angka 2
		</td>
		<td width=\"25\">
			3
		</td>
		<td width=\"195\">
			Rp. " . number_format(getBPHTBPayment(1), 0, ',', '.') . "
		</td>
	</tr>
	  <tr>
		<td colspan=\"1\" width=\"380\">
			 &nbsp; &nbsp;4. Bea Perolehan Hak atas Tanah dan Bangunan yang terutang
		</td>
		<td width=\"110\">
			5% x angka 3
		</td>
		<td width=\"25\">
			4
		</td>
		<td width=\"195\">
			Rp. " . number_format(getBPHTBPayment(5, $data->CPM_PAYMENT_TIPE_PECAHAN, $data->CPM_BPHTB_BAYAR), 0, ',', '.') . "
		</td>
	</tr>
  

  <tr>
    <td colspan=\"4\">
	<table width=\"100%\" border=\"0\" cellpadding=\"1\">
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
		<td width=\"150\">Nomor :" . $data->CPM_PAYMENT_TIPE_SURAT_NOMOR . "</td>
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
    <td>Bedasarkan peraturan KDH No  : " . $data->CPM_PAYMENT_TIPE_KHD_NOMOR . "</td>
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
				  <font size=\"-2\">...........................,tgl " . $tanggal_dibuat . "</font><br> 
				  <font size=\"-2\">WAJIB PAJAK/PENYETOR</font>  
				  </td>
		        </tr>
				<tr>
			      <td align=\"left\" colspan=\"3\" style=\"height:105px;\">
				  <font size=\"-3\">&nbsp;&nbsp;&nbsp;</font>			  	  
				  </td>
		        </tr>
				<tr>
			      <td width=\"10\"></td>
				  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"165\"><b>" . strtoupper($data->CPM_WP_NAMA) . "</b>			  	
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
		<td width=\"185\">
			<table width=\"100%\" border=\"0\">		  
				<tr>
			      <td align=\"center\" height=\"10\" colspan=\"3\">
				  	<font size=\"-3\">MENGETAHUI:<br>PPAT/NOTARIS<br><br></font>		  
				  </td>
		        </tr>
				<tr>
			      <td align=\"left\" colspan=\"3\" style=\"height:87px;\">
				  <font size=\"-3\">&nbsp;&nbsp;&nbsp;</font>			  	  
				  </td>
		        </tr>
				<tr>
			      <td width=\"10\"></td>
				  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"165\"><b>" . strtoupper($nm_lengkap_notaris) . "</b>			  	
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
				  <font size=\"-2\">Diterima Oleh:<br>Tempat Pembayaran BPHTB</font>			  	  
				  </td>
		        </tr>
				<tr>
			      <td align=\"left\" colspan=\"3\"  style=\"height:108px;\">
				  <font size=\"-3\">&nbsp;&nbsp;&nbsp;Tanggal :&nbsp;</font>			  	  
				  </td>
		        </tr>			
				<tr>
			      <td width=\"10\"></td>
			      
				  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"140\">
				  	
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
				  <font size=\"-2\">TELAH DIVERIFIKASI <br>BADAN PENGELOLA PAJAK DAN<br> RETRIBUSI DAERAH KABUPATEN PESAWARAN</font>			  
				  </td>
		        </tr>
				<tr>
				  <td align=\"center\" colspan=\"3\" style=\"height:91px;\">";
	if ((int)$dataGW['approval_status'] == 1) {
		$imageGenerator = new QRCode(urldecode($dataGW['approval_qr_text']), ['s' => 'qr']);
		$imageQr = $imageGenerator->render_image();
		imagepng($imageQr, 'qrcode.png', 9);
		$html .= '<img src="qrcode.png" style="width:90px;height:90px;display:block">';
	}
	$html .= "</td>
		        </tr>	
				<tr>
			      <td width=\"10\"></td>
				  <td align=\"center\" valign=\"top\" style=\"border-bottom:1px #000 solid\" width=\"140\">
				  	<font size=\"-2\" ><b>" . strtoupper(getConfigValue("1", 'NAMA_PJB_PENGESAH')) . "</b></font>
				  </td>
				  <td width=\"10\"></td>
		        </tr>
				<tr>
				  <td width=\"10\"></td>
				  <td align=\"center\" valign=\"top\">
				  <font size=\"-3\" >
					<b>NIP: " . getConfigValue("1", 'NIP_PJB_PENGESAH') . "</b>
				</font>
				</td>
				  <td width=\"10\"></td>
		        </tr>
			</table>
		</td>
	  </tr>  
	</table><br><br>
	<table width=\"720\" border=\"0\" cellpadding=\"0\">";

	if ($data->CPM_TRAN_STATUS == '5') {
		$label_baris1 = "Tanggal Kadaluarsa";
		if ($dataGwssb['expired_date'] != '') {
			$isi_baris1 = $dataGwssb['expired_date'];
		} else {
			$isi_baris1 = $data->EXPIRED;
		}

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
	if ($data->CPM_TRAN_STATUS == '5') {
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

// $icoQRIS = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="210mm" height="77.5mm" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd;" viewBox="0 0 21000 7750" xmlns:xlink="http://www.w3.org/1999/xlink">
// 				<defs> <style type="text/css"> <![CDATA[ .fil0 {fill:black;fill-rule:nonzero} ]]> </style> </defs>
// 				<g id="__x0023_Layer_x0020_1">
// 					<metadata id="CorelCorpID_0Corel-Layer"/>
// 					<path class="fil0" d="M20140 4750l0 -667 0 -1333 -2000 0 -1333 0 0 -667 3333 0 0 -1333 -3333 0 -2000 0 0 1333 0 667 0 1333 2000 0 1333 0 0 667 -3333 0 0 1333 3333 0 2000 0 0 -1333zm527 -417l0 2167c0,44 -18,87 -49,118 -31,31 -74,49 -118,49l-2167 0 0 333 2500 0c44,0 87,-18 118,-49 31,-31 49,-74 49,-118l0 -2500 -333 0zm-18000 -4333l-2500 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 2500 333 0 0 -2167c0,-44 18,-87 49,-118 31,-31 74,-49 118,-49l2167 0 0 -333zm2140 7750l1333 0 0 -3000 -1333 0 0 3000zm1167 -7000l-3167 0 0 1333 2000 0 0 2000 1333 0 0 -3167c0,-44 -18,-87 -49,-118 -31,-31 -74,-49 -118,-49zm-3833 0l-1167 0c-44,0 -87,18 -118,49 -31,31 -49,74 -49,118l0 5000c0,44 18,87 49,118 31,31 74,49 118,49l3167 0 0 -1333 -2000 0 0 -4000zm667 3333l1333 0 0 -1333 -1333 0 0 1333zm333 -1000l0 0 667 0 0 667 -667 0 0 -667zm3667 -2333l0 1333 4000 0 0 667 -2667 0 -1333 0 0 1333 0 2000 1333 0 0 -1980 2000 1980 2000 0 -2087 -2000 753 0 1333 0 0 -1333 0 -667 0 -1333 -1333 0 -4000 0zm6000 5333l1333 0 0 -5333 -1333 0 0 5333z"/>
// 				</g>
// 			</svg>';

// // die(getHTML());
// $q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

// $q = base64_decode($q);
// $q = $json->decode($q);
// $v = count($q);

// $NOP = "";
// // create new PDF document
// $pagelayout = array(210, 500);
// $pdf = new TCPDF("P", PDF_UNIT, $pagelayout, true, 'UTF-8', false);

// // set document information
// $pdf->SetCreator(PDF_CREATOR);
// $pdf->SetAuthor('vpost');
// $pdf->SetTitle('');
// $pdf->SetSubject('');
// $pdf->SetKeywords('');

// // remove default header/footer
// $pdf->setPrintHeader(false);
// $pdf->setPrintFooter(false);

// // set default monospaced font
// $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// //set margins
// //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
// $pdf->SetMargins(8, 9, 2);
// //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
// //$pdf->SetFooterMargin(5);
// //set auto page breaks
// //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// //set image scale factor
// $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// //set some language-dependent strings
// //$pdf->setLanguageArray($l);
// // ---------------------------------------------------------
// // set font
// //$pdf->SetFont('helvetica', 'B', 20);
// // add a page
// //$pdf->AddPage();
// //$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

// $pdf->SetFont('helvetica', '', 10);
// $pdf->SetFont('segoeui', '', 10);
// $pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
// $HTML = "";
// $style = array(
// 	'position' => '', #for barcode
// 	'align' => 'C',
// 	'stretch' => true,
// 	'fitwidth' => true,
// 	'cellfitalign' => '',
// 	'border' => false,
// 	'hpadding' => 'auto',
// 	'vpadding' => 'auto',
// 	'fgcolor' => array(0, 0, 0),
// 	'bgcolor' => false, //array(255,255,255),
// 	'text' => true,
// 	'font' => 'helvetica',
// 	'fontsize' => 8,
// 	'stretchtext' => 4
// );
// // print_r($q);exit;
// for ($i = 0; $i < $v; $i++) {
// 	$idssb = $q[$i]->id;
// 	$uname = ""; //$q[0]->uname;
// 	$draf = $q[$i]->draf;
// 	$appID = base64_decode($q[$i]->axx);
// 	$fileLogo = getConfigValue("1", 'FILE_LOGO');
// 	$resolution = array(215, 350);
// 	$pdf->AddPage('P', $resolution);
// 	//$pdf->setPageBoxes($i+1,'MediaBox',760,1300,0,0,true);

// 	$lampiran_wp = "Lembar 1";
// 	$lampiran_wp_ket = "Untuk<br>WAJIB PAJAK";
// 	$lampiran_bpprd = "Lembar 2";
// 	$lampiran_bpprd_ket = "Untuk<br>Notaris/PPAT/PPATS<br>sebagai arsip";
// 	$lampiran_bpprd_pen = "Lembar 3";
// 	$lampiran_bpprd_pen_ket = "Untuk<br>Kepala Kantor<br>ATR/BPN";
// 	$lampiran_bpprd_BPN = "Lembar 4";
// 	$lampiran_bpprd_BPN_ket = "Untuk<br>BPPRD Arsip<br>";
// 	$lampiran_bpprd_2 = "Lembar 5";
// 	$lampiran_bpprd_ket_2 = "Untuk<br>Bendahara penerima<br>BPPRD";
// 	$lampiran_notaris = "Lembar 6";
// 	$lampiran_notaris_ket = "Untuk<br>Bank Persepsi atau Bank Tempat Lain<br>yang di tunjuk oleh Bupati";
// 	$HTML1 = getHTML($idssb, $draf, $lampiran_wp, $lampiran_wp_ket);
// 	$HTML2 = getHTML($idssb, $draf, $lampiran_bpprd, $lampiran_bpprd_ket);
// 	$HTML3 = getHTML($idssb, $draf, $lampiran_bpprd_pen, $lampiran_bpprd_pen_ket);
// 	$HTML4 = getHTML($idssb, $draf, $lampiran_bpprd_BPN, $lampiran_bpprd_BPN_ket);
// 	$HTML5 = getHTML($idssb, $draf, $lampiran_bpprd_2, $lampiran_bpprd_ket_2);
// 	$HTML6 = getHTML($idssb, $draf, $lampiran_notaris, $lampiran_notaris_ket);

// 	// $qrcode = getbphtbBankLampung(getpaymentCode($idssb));
// 	// // $paymentcode = getpaymentCode($idssb);
// 	// // $bank_lampung = getbphtbBankLampung('1801221000030102');

// 	// // var_dump($idssb);
// 	// // var_dump($qrcode['data']['qr-image']);
// 	// // var_dump($paymentcode);
// 	// // var_dump($bank_lampung);


// 	// // var_dump($qrcode['data']['qr-image'], getpaymentCode($idssb) );
// 	// // exit;
// 	// if (isset($qrcode['data']['qr-image'] )) {
// 	// 	$pdf->Image( $qrcode['data']['qr-image'], 13, 1, 12, '', '', '', '', false, 300, '', false);
// 	// }

// 	// add by dedi  ==================================================
// 	$dbName = getConfigValue($a, 'BPHTBDBNAME');
// 	$dbHost = getConfigValue($a, 'BPHTBHOSTPORT');
// 	$dbPwd = getConfigValue($a, 'BPHTBPASSWORD');
// 	$dbUser = getConfigValue($a, 'BPHTBUSERNAME');
// 	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

// 	$datetimenow = date('Y-m-d H:i:s');

// 	// $idssbx = '86246f27746268346983d325da5d1f3a'; //dummy
// 	$query4 = "SELECT q.qr, IFNULL(s.payment_flag,999) AS flag 
// 				FROM qris q 
// 				LEFT JOIN ssb s ON s.id_switching=q.id_switching 
// 				WHERE q.id_switching='$idssb' AND q.expired_date_time>='$datetimenow'
// 				ORDER BY q.id DESC 
// 				LIMIT 0, 1"; //DB real
// 	$query4 = "SELECT qr, 0 AS flag FROM qris WHERE id_switching='$idssb' AND expired_date_time>='$datetimenow' ORDER BY id DESC LIMIT 0, 1"; //DB DUMMY

// 	$r = mysqli_query($DBLinkLookUp, $query4);
// 	$nx = mysqli_num_rows($r);
// 	$QRCodeSVG = false;
// 	if ($nx > 0) {
// 		$r = mysqli_fetch_array($r);
// 		$d = new DNS2D();
// 		$d->setStorPath(__DIR__ . '/cache/');
// 		$QRCodeSVG = ($r['flag'] == 0) ? $d->getBarcodeSVG($r['qr'], 'QRCODE', 3, 3) : false;
// 	}
// 	// ================================================================

// 	$pdf->writeHTML($HTML1, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	if ($QRCodeSVG) {
// 		$pdf->ImageSVG('@' . $QRCodeSVG, $x = 112, $y = 279, $w = 32, $h = 32, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
// 		$pdf->ImageSVG('@' . $icoQRIS, $x = 146, $y = 300, $w = 16, $h = 16, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
// 	}
// 	// $pdf->write1DBarcode($bill,$city_code,$e 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	$pdf->AddPage('P', $resolution);
// 	$pdf->writeHTML($HTML2, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	$pdf->AddPage('P', $resolution);
// 	$pdf->writeHTML($HTML3, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	$pdf->AddPage('P', $resolution);
// 	$pdf->writeHTML($HTML4, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	$pdf->AddPage('P', $resolution);
// 	$pdf->writeHTML($HTML5, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	$pdf->AddPage('P', $resolution);
// 	$pdf->writeHTML($HTML6, true, false, false, false, '');
// 	$pdf->Image($sRootPath . 'view/Registrasi/configure/logo/' . $fileLogo, 19, 11, 18, '', '', '', '', false, 300, '', false);
// 	$pdf->SetAlpha(0.3);
// 	$pdf->ln(1);
// 	$pdf->SetAlpha(1);
// 	$pdf->write1DBarcode($NOP, 'C128', '', '', '', 17, 0.4, $style, 'N');
// 	//$pdf->AddPage('P', $resolution);
// 	if ($draf == 1) {
// 		$bottomMargin = 0;
// 		for ($x = 1; $x <= 9; $x++) {
// 			$rightMargin = 0;
// 			for ($y = 1; $y <= 7; $y++) {
// 				$pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 15, 35, '', '', '', true, false, 0, '', false);
// 				$rightMargin += 35;
// 			}
// 			$bottomMargin += 35;
// 		}
// 		$rightMargin = 0;
// 		for ($y = 1; $y <= 7; $y++) {
// 			$pdf->Image($sRootPath . 'image/DRAF.png', $rightMargin - 5, $bottomMargin - 17, 35, '', '', '', true, false, 0, '', false);
// 			$rightMargin += 35;
// 		}
// 	} else if ($draf == 0) {
// 		#$pdf->Image($sRootPath . 'image/FINAL.png', 50, 70, 100, '', '', '', '', false, 300, '', false); // dimatikan untuk tulisan salinan dulu
// 	}
// }

// // -----------------------------------------------------------------------------
// //Close and output PDF document
// // ob_clean();
// $pdf->Output($NOP . '.pdf', 'I');

//echo getHTML($idssb);
//============================================================+
// END OF FILE                                                
//============================================================+
