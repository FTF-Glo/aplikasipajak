<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'dbkb', '', dirname(__FILE__))) . '/';
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

$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q 		= base64_decode($q);
$q 		= $json->decode($q);
$appID 	= @$q->appId;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($appID);
$arConfig 	= $User->GetModuleConfig('mLkt');

$kdPropinsi = $appConfig['KODE_PROVINSI'];
$kdDati2 	= substr($appConfig['KODE_KOTA'], 2, 2);
$tahun		= $appConfig['tahun_tagihan'];

$dataNotaris = "";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getAuthor($uname)
{
	global $DBLink, $appID;
	$id = $appID;
	$qry = "select nm_lengkap from TBL_REG_USER_NOTARIS where userId = '" . $uname . "'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo mysqli_error($DBLink);
	}

	$num_rows = mysqli_num_rows($res);
	if ($num_rows == 0) return $uname;
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['nm_lengkap'];
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

function getData($idssb)
{
	global $data, $DBLink, $dataNotaris;

	$query = "SELECT BS.*, TKEC.CPC_TKC_KECAMATAN, DATE_FORMAT(BS.CPM_DATE_RECEIVE,'%d-%m-%Y') AS TGL_MASUK, TKEL.CPC_TKL_KELURAHAN FROM cppmod_pbb_services BS, cppmod_tax_kecamatan TKEC, cppmod_tax_kelurahan TKEL
	WHERE TKEC.CPC_TKC_ID = BS.CPM_OP_KECAMATAN AND  TKEL.CPC_TKL_ID = BS.CPM_OP_KELURAHAN AND CPM_ID='$idssb'";

	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo $query . "<br>";
		echo mysqli_error($DBLink);
	}
	$json = new Services_JSON();
	$dataNotaris =  $json->decode(mysql2json($res, "data"));
	$dt = $dataNotaris->data[0];
	return $dt;
}
function qStandard()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KD_JPB as JPB,
				CPM_TIPE_BNG as TIPE_BNG,
				CPM_KD_BNG_LANTAI as LANTAI,
				ROUND(CPM_NILAI_DBKB_STANDARD) AS NILAI
			FROM
				cppmod_pbb_dbkb_standard
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_STANDARD = '{$tahun}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JPB']][$row['TIPE_BNG']][$row['LANTAI']] = $row['NILAI'];
		}
	}
	return $data;
}
function qBengkelGudangPertanian()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_TINGGI_KOLOM_MIN_DBKB_JPB8 as TMIN,
				CPM_TINGGI_KOLOM_MAX_DBKB_JPB8 as TMAX,
				CPM_LBR_BENT_MIN_DBKB_JPB8 as LMIN,
				CPM_LBR_BENT_MAX_DBKB_JPB8 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB8) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb8
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB8 = '{$tahun}'";
	#echo $qry;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TMIN']][$row['TMAX']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qPabrik()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_TINGGI_KOLOM_MIN_DBKB_JPB3 as TMIN,
				CPM_TINGGI_KOLOM_MAX_DBKB_JPB3 as TMAX,
				CPM_LBR_BENT_MIN_DBKB_JPB3 as LMIN,
				CPM_LBR_BENT_MAX_DBKB_JPB3 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB3) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb3
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB3 = '{$tahun}'";
	# echo $qry;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TMIN']][$row['TMAX']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qKantor()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB2 as KLS,
				CPM_LANTAI_MIN_JPB2 as LMIN,
				CPM_LANTAI_MAX_JPB2 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB2) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb2
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB2 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qPertokoan()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB4 as KLS,
				CPM_LANTAI_MIN_DBKB_JPB4 as LMIN,
				CPM_LANTAI_MAX_DBKB_JPB4 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB4) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb4
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB4 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qRmhSakit()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB5 as KLS,
				CPM_LANTAI_MIN_JPB5 as LMIN,
				CPM_LANTAI_MAX_JPB5 as LMAX,
				ROUND(CPM_NILAI_DBKB_JPB5) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb5
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB5 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qOlahRaga()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB6 as KLS,
				ROUND(CPM_NILAI_DBKB_JPB6) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb6
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB6 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']] = $row['NILAI'];
		}
	}
	return $data;
}

function qHotelNonResort()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_JNS_DBKB_JPB7 AS JNS,
				CPM_LANTAI_MIN_JPB7 as LMIN,
				CPM_LANTAI_MAX_JPB7 as LMAX,
				CPM_BINTANG_DBKB_JPB7 as BINTANG,
				ROUND(CPM_NILAI_DBKB_JPB7) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb7
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB7 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['LMIN']][$row['LMAX']][$row['BINTANG']] = $row['NILAI'];
		}
	}
	return $data;
}

function qHotelResort()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_JNS_DBKB_JPB7 AS JNS,
				CPM_LANTAI_MIN_JPB7 as LMIN,
				CPM_LANTAI_MAX_JPB7 as LMAX,
				CPM_BINTANG_DBKB_JPB7 as BINTANG,
				ROUND(CPM_NILAI_DBKB_JPB7) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb7
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB7 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['LMIN']][$row['LMAX']][$row['BINTANG']] = $row['NILAI'];
		}
	}
	return $data;
}

function qParkir()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_TYPE_DBKB_JPB12 AS TYPE,
				ROUND(CPM_NILAI_DBKB_JPB12) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb12
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB12 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TYPE']] = $row['NILAI'];
		}
	}
	return $data;
}

function qApartemen()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB13 AS KLS,
			     CPM_LANTAI_MIN_JPB13 AS LMIN,
			     CPM_LANTAI_MAX_JPB13 AS LMAX,
				ROUND(CPM_NILAI_DBKB_JPB13) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb13
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB13 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qSekolah()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KLS_DBKB_JPB16 AS KLS,
			     CPM_LANTAI_MIN_JPB16 AS LMIN,
			     CPM_LANTAI_MAX_JPB16 AS LMAX,
				ROUND(CPM_NILAI_DBKB_JPB16) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb16
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB16 = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KLS']][$row['LMIN']][$row['LMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qMezanin()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;

	$qry = "SELECT
				ROUND(CPM_NILAI_DBKB_MEZANIN) AS NILAI
			FROM
				cppmod_pbb_dbkb_mezanin
			WHERE
				CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_MEZANIN = '{$tahun}' ";

	// echo $qry;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$nilai = $row['NILAI'];
		}
		return isset($nilai) ? $nilai : 0;
	}
}

function qKanopiBensin()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;

	$qry = "SELECT
				ROUND(CPM_NILAI_DBKB_JPB14) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb14
			WHERE
				CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB14 = '{$tahun}' ";

	// echo $qry;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$nilai = $row['NILAI'];
		}
		return isset($nilai) ? $nilai : 0;
	}
}

function qDayaDukung()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_TYPE_KONSTRUKSI AS TYPE,
				ROUND(CPM_NILAI_DBKB_DAYA_DUKUNG) AS NILAI
			FROM
				cppmod_pbb_dbkb_daya_dukung
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_DAYA_DUKUNG = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['TYPE']] = $row['NILAI'];
		}
	}
	return $data;
}

function qTangkiBawahTanah()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_JNS_TANGKI_DBKB_JPB15 AS JNS,
			     CPM_KAPASITAS_MIN_DBKB_JPB15 AS KMIN,
			     CPM_KAPASITAS_MAX_DBKB_JPB15 AS KMAX,
				ROUND(CPM_NILAI_DBKB_JPB15) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb15
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB15 = '{$tahun}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[(int) $row['JNS']][(int) $row['KMIN']][(int) $row['KMAX']] = $row['NILAI'];
		}
	}
	return $data;
}

function qTangkiAtasTanah()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_JNS_TANGKI_DBKB_JPB15 AS JNS,
			     CPM_KAPASITAS_MIN_DBKB_JPB15 AS KMIN,
			     CPM_KAPASITAS_MAX_DBKB_JPB15 AS KMAX,
				ROUND(CPM_NILAI_DBKB_JPB15) AS NILAI
			FROM
				cppmod_pbb_dbkb_jpb15
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_JPB15 = '{$tahun}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['JNS']][$row['KMIN']][$row['KMAX']] = $row['NILAI'];
		}
	}
	return $data;
}
function qFasNonDep()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				KD_FASILITAS AS KODE,
				ROUND(NILAI_NON_DEP) AS NILAI
			FROM
				cppmod_pbb_fas_non_dep
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_NON_DEP = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KODE']] = $row['NILAI'];
		}
	}

	return $data;
}
function qNilaiFasDepKlsBintang($KODE, $JPB, $KLS)
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				KD_FASILITAS AS KODE,
				KD_JPB AS JPB,
				KLS_BINTANG AS KLS,
				ROUND(NILAI_FASILITAS_KLS_BINTANG) as NILAI
			FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
			AND KD_FASILITAS='{$KODE}'
			AND KD_JPB='{$JPB}'
			AND KLS_BINTANG='{$KLS}'";
	$res = mysqli_query($DBLink, $qry);

	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data = $row['NILAI'];
			//print_r($data);exit;
		}
	}

	return $data;
}

function qNilaiFasDepMinMax()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				KD_FASILITAS AS KODE,
				KLS_DEP_MIN AS KMIN,
                    KLS_DEP_MAX AS KMAX,
				ROUND(NILAI_DEP_MIN_MAX) AS NILAI
			FROM
				cppmod_pbb_fas_dep_min_max
			WHERE
			KD_PROPINSI = '{$kdPropinsi}'
			AND KD_DATI2 = '{$kdDati2}'
			AND THN_DEP_MIN_MAX = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['KODE']][$row['KMIN']][$row['KMAX']] = $row['NILAI'];
			//print_r($data);
		}
	}
	//print_r($data);exit;
	return $data;
}
function qNilaiMaterial()
{
	global $DBLink, $tahun, $kdPropinsi, $kdDati2;
	$data = array();

	$qry = "SELECT
				CPM_KD_PEKERJAAN AS PEKERJAAN,
				CPM_KD_KEGIATAN AS KEGIATAN,
				ROUND(CPM_NILAI_DBKB_MATERIAL) AS NILAI
			FROM
				cppmod_pbb_dbkb_material
			WHERE
			CPM_KD_PROPINSI = '{$kdPropinsi}'
			AND CPM_KD_DATI2 = '{$kdDati2}'
			AND CPM_THN_DBKB_MATERIAL = '{$tahun}'";
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$data[$row['PEKERJAAN']][$row['KEGIATAN']] = $row['NILAI'];
		}
	}
	return $data;
}
function getHTML()
{
	global $uname, $appID, $arConfig, $appConfig;

	$tahun		= $appConfig['tahun_tagihan'];
	$propinsi = $appConfig['NAMA_PROVINSI'];
	$kota = $appConfig['NAMA_KOTA'];
	$kanwil = $appConfig['KANWIL'];
	$kpp = $appConfig['KPP'];
	// qApartemen('3','1','2'); exit;

	//Q14 Bengkel/Gudang/Pertanian
	$qBengkelGudangPertanian = qBengkelGudangPertanian();
	$qBGP01 = @$qBengkelGudangPertanian['0']['4']['0']['9'];
	$qBGP02 = @$qBengkelGudangPertanian['0']['4']['10']['13'];
	$qBGP03 = @$qBengkelGudangPertanian['0']['4']['14']['17'];
	$qBGP04 = @$qBengkelGudangPertanian['0']['4']['18']['21'];
	$qBGP05 = @$qBengkelGudangPertanian['0']['4']['22']['25'];
	$qBGP06 = @$qBengkelGudangPertanian['0']['4']['26']['29'];
	$qBGP07 = @$qBengkelGudangPertanian['0']['4']['30']['33'];
	$qBGP08 = @$qBengkelGudangPertanian['0']['4']['34']['37'];
	$qBGP09 = @$qBengkelGudangPertanian['0']['4']['38']['99'];

	$qBGP11 = @$qBengkelGudangPertanian['5']['7']['0']['9'];
	$qBGP12 = @$qBengkelGudangPertanian['5']['7']['10']['13'];
	$qBGP13 = @$qBengkelGudangPertanian['5']['7']['14']['17'];
	$qBGP14 = @$qBengkelGudangPertanian['5']['7']['18']['21'];
	$qBGP15 = @$qBengkelGudangPertanian['5']['7']['22']['25'];
	$qBGP16 = @$qBengkelGudangPertanian['5']['7']['26']['29'];
	$qBGP17 = @$qBengkelGudangPertanian['5']['7']['30']['33'];
	$qBGP18 = @$qBengkelGudangPertanian['5']['7']['34']['37'];
	$qBGP19 = @$qBengkelGudangPertanian['5']['7']['38']['99'];

	$qBGP21 = @$qBengkelGudangPertanian['8']['10']['0']['9'];
	$qBGP22 = @$qBengkelGudangPertanian['8']['10']['10']['13'];
	$qBGP23 = @$qBengkelGudangPertanian['8']['10']['14']['17'];
	$qBGP24 = @$qBengkelGudangPertanian['8']['10']['18']['21'];
	$qBGP25 = @$qBengkelGudangPertanian['8']['10']['22']['25'];
	$qBGP26 = @$qBengkelGudangPertanian['8']['10']['26']['29'];
	$qBGP27 = @$qBengkelGudangPertanian['8']['10']['30']['33'];
	$qBGP28 = @$qBengkelGudangPertanian['8']['10']['34']['37'];
	$qBGP29 = @$qBengkelGudangPertanian['8']['10']['38']['99'];

	$qBGP31 = @$qBengkelGudangPertanian['11']['99']['0']['9'];
	$qBGP32 = @$qBengkelGudangPertanian['11']['99']['10']['13'];
	$qBGP33 = @$qBengkelGudangPertanian['11']['99']['14']['17'];
	$qBGP34 = @$qBengkelGudangPertanian['11']['99']['18']['21'];
	$qBGP35 = @$qBengkelGudangPertanian['11']['99']['22']['25'];
	$qBGP36 = @$qBengkelGudangPertanian['11']['99']['26']['29'];
	$qBGP37 = @$qBengkelGudangPertanian['11']['99']['30']['33'];
	$qBGP38 = @$qBengkelGudangPertanian['11']['99']['34']['37'];
	$qBGP39 = @$qBengkelGudangPertanian['11']['99']['38']['99'];

	//Q15 Pabrik
	$qPabrik = qPabrik();
	$qPabrik01 = @$qPabrik['0']['4']['0']['9'];
	$qPabrik02 = @$qPabrik['0']['4']['10']['13'];
	$qPabrik03 = @$qPabrik['0']['4']['14']['17'];
	$qPabrik04 = @$qPabrik['0']['4']['18']['21'];
	$qPabrik05 = @$qPabrik['0']['4']['22']['25'];
	$qPabrik06 = @$qPabrik['0']['4']['26']['29'];
	$qPabrik07 = @$qPabrik['0']['4']['30']['33'];
	$qPabrik08 = @$qPabrik['0']['4']['34']['37'];
	$qPabrik09 = @$qPabrik['0']['4']['38']['99'];

	$qPabrik11 = @$qPabrik['5']['7']['0']['9'];
	$qPabrik12 = @$qPabrik['5']['7']['10']['13'];
	$qPabrik13 = @$qPabrik['5']['7']['14']['17'];
	$qPabrik14 = @$qPabrik['5']['7']['18']['21'];
	$qPabrik15 = @$qPabrik['5']['7']['22']['25'];
	$qPabrik16 = @$qPabrik['5']['7']['26']['29'];
	$qPabrik17 = @$qPabrik['5']['7']['30']['33'];
	$qPabrik18 = @$qPabrik['5']['7']['34']['37'];
	$qPabrik19 = @$qPabrik['5']['7']['38']['99'];

	$qPabrik21 = @$qPabrik['8']['10']['0']['9'];
	$qPabrik22 = @$qPabrik['8']['10']['10']['13'];
	$qPabrik23 = @$qPabrik['8']['10']['14']['17'];
	$qPabrik24 = @$qPabrik['8']['10']['18']['21'];
	$qPabrik25 = @$qPabrik['8']['10']['22']['25'];
	$qPabrik26 = @$qPabrik['8']['10']['26']['29'];
	$qPabrik27 = @$qPabrik['8']['10']['30']['33'];
	$qPabrik28 = @$qPabrik['8']['10']['34']['37'];
	$qPabrik29 = @$qPabrik['8']['10']['38']['99'];

	$qPabrik31 = @$qPabrik['11']['99']['0']['9'];
	$qPabrik32 = @$qPabrik['11']['99']['10']['13'];
	$qPabrik33 = @$qPabrik['11']['99']['14']['17'];
	$qPabrik34 = @$qPabrik['11']['99']['18']['21'];
	$qPabrik35 = @$qPabrik['11']['99']['22']['25'];
	$qPabrik36 = @$qPabrik['11']['99']['26']['29'];
	$qPabrik37 = @$qPabrik['11']['99']['30']['33'];
	$qPabrik38 = @$qPabrik['11']['99']['34']['37'];
	$qPabrik39 = @$qPabrik['11']['99']['38']['99'];

	//Q16 KANTOR
	$qKantor = qKantor();
	$qKantor01 = @$qKantor['3']['1']['2'];
	$qKantor02 = @$qKantor['4']['1']['2'];
	$qKantor03 = @$qKantor['3']['3']['5'];
	$qKantor04 = @$qKantor['4']['3']['5'];
	$qKantor05 = @$qKantor['1']['6']['12'];
	$qKantor06 = @$qKantor['2']['6']['12'];
	$qKantor07 = @$qKantor['3']['6']['12'];
	$qKantor08 = @$qKantor['1']['13']['19'];
	$qKantor09 = @$qKantor['2']['13']['19'];
	$qKantor10 = @$qKantor['3']['13']['19'];
	$qKantor11 = @$qKantor['1']['20']['24'];
	$qKantor12 = @$qKantor['2']['20']['24'];
	$qKantor13 = @$qKantor['1']['25']['99'];
	$qKantor14 = @$qKantor['2']['25']['99'];

	//Q 1.7 PERTOKOAN
	$qPertokoan = qPertokoan();
	$qPertokoan01 = @$qPertokoan['2']['1']['2'];
	$qPertokoan02 = @$qPertokoan['3']['1']['2'];
	$qPertokoan03 = @$qPertokoan['1']['3']['4'];
	$qPertokoan04 = @$qPertokoan['2']['3']['4'];
	$qPertokoan05 = @$qPertokoan['1']['5']['99'];
	$qPertokoan06 = @$qPertokoan['2']['5']['99'];

	//Q 1.8 RUMAH SAKIT
	$qRmhSakit = qRmhSakit();
	$qRmhSakit01 = @$qRmhSakit['2']['1']['2'];
	$qRmhSakit02 = @$qRmhSakit['3']['1']['2'];
	$qRmhSakit03 = @$qRmhSakit['4']['1']['2'];
	$qRmhSakit04 = @$qRmhSakit['1']['3']['5'];
	$qRmhSakit05 = @$qRmhSakit['2']['3']['5'];
	$qRmhSakit06 = @$qRmhSakit['3']['3']['5'];
	$qRmhSakit07 = @$qRmhSakit['1']['6']['99'];
	$qRmhSakit08 = @$qRmhSakit['2']['6']['99'];

	//Q 1.9 OLAHRAGA
	$qOlahRaga = qOlahRaga();
	$qOlahRaga01 = @$qOlahRaga['1'];
	$qOlahRaga02 = @$qOlahRaga['2'];

	//Q 1.10 HOTEL NON RESORT
	$qHotelNonResort = qHotelNonResort();
	$qHotelNonResort01 = @$qHotelNonResort['1']['1']['2']['4'];
	$qHotelNonResort02 = @$qHotelNonResort['1']['1']['2']['4'];
	$qHotelNonResort03 = @$qHotelNonResort['1']['1']['2']['3'];
	$qHotelNonResort04 = @$qHotelNonResort['1']['1']['2']['5'];
	$qHotelNonResort05 = @$qHotelNonResort['1']['3']['5']['4'];
	$qHotelNonResort06 = @$qHotelNonResort['1']['3']['5']['4'];
	$qHotelNonResort07 = @$qHotelNonResort['1']['3']['5']['3'];
	$qHotelNonResort08 = @$qHotelNonResort['1']['3']['5']['2'];
	$qHotelNonResort09 = @$qHotelNonResort['1']['3']['5']['5'];
	$qHotelNonResort10 = @$qHotelNonResort['1']['6']['12']['4'];
	$qHotelNonResort11 = @$qHotelNonResort['1']['6']['12']['4'];
	$qHotelNonResort12 = @$qHotelNonResort['1']['6']['12']['3'];
	$qHotelNonResort13 = @$qHotelNonResort['1']['6']['12']['2'];
	$qHotelNonResort14 = @$qHotelNonResort['1']['6']['12']['1'];
	$qHotelNonResort15 = @$qHotelNonResort['1']['6']['12']['4'];
	$qHotelNonResort16 = @$qHotelNonResort['1']['6']['12']['5'];
	$qHotelNonResort17 = @$qHotelNonResort['1']['13']['20']['3'];
	$qHotelNonResort18 = @$qHotelNonResort['1']['13']['20']['2'];
	$qHotelNonResort19 = @$qHotelNonResort['1']['13']['20']['1'];
	$qHotelNonResort20 = @$qHotelNonResort['1']['21']['24']['2'];
	$qHotelNonResort21 = @$qHotelNonResort['1']['21']['24']['1'];
	$qHotelNonResort22 = @$qHotelNonResort['1']['25']['99']['2'];
	$qHotelNonResort23 = @$qHotelNonResort['1']['25']['99']['1'];


	//Q 1.11 HOTEL RESORT
	$qHotelResort = qHotelResort();
	$qHotelResort01 = @$qHotelResort['2']['1']['2']['4'];
	$qHotelResort02 = @$qHotelResort['2']['1']['2']['4'];
	$qHotelResort03 = @$qHotelResort['2']['1']['2']['3'];
	$qHotelResort04 = @$qHotelResort['2']['1']['2']['2'];
	$qHotelResort05 = @$qHotelResort['2']['1']['2']['1'];
	$qHotelResort06 = @$qHotelResort['2']['1']['2']['5'];

	$qHotelResort07 = @$qHotelResort['2']['3']['5']['4'];
	$qHotelResort08 = @$qHotelResort['2']['3']['5']['4'];
	$qHotelResort09 = @$qHotelResort['2']['3']['5']['3'];
	$qHotelResort10 = @$qHotelResort['2']['3']['5']['2'];
	$qHotelResort11 = @$qHotelResort['2']['3']['5']['1'];
	$qHotelResort12 = @$qHotelResort['2']['3']['5']['5'];

	$qHotelResort13 = @$qHotelResort['2']['6']['12']['4'];
	$qHotelResort14 = @$qHotelResort['2']['6']['12']['4'];
	$qHotelResort15 = @$qHotelResort['2']['6']['12']['3'];
	$qHotelResort16 = @$qHotelResort['2']['6']['12']['2'];
	$qHotelResort17 = @$qHotelResort['2']['6']['12']['1'];

	//Q 1.12 PARKIR
	$qParkir = qParkir();
	$qParkir01 = @$qParkir['1'];
	$qParkir02 = @$qParkir['2'];
	$qParkir03 = @$qParkir['3'];
	$qParkir04 = @$qParkir['4'];

	//Q 1.13 Apartemen
	$qApartemen = qApartemen();
	$qApartemen01 = @$qApartemen['3']['1']['2'];
	$qApartemen02 = @$qApartemen['4']['1']['2'];
	$qApartemen03 = @$qApartemen['2']['3']['5'];
	$qApartemen04 = @$qApartemen['3']['3']['5'];
	$qApartemen05 = @$qApartemen['4']['3']['5'];
	$qApartemen06 = @$qApartemen['1']['6']['12'];
	$qApartemen07 = @$qApartemen['2']['6']['12'];
	$qApartemen08 = @$qApartemen['3']['6']['12'];
	$qApartemen09 = @$qApartemen['4']['6']['12'];
	$qApartemen10 = @$qApartemen['1']['13']['20'];
	$qApartemen11 = @$qApartemen['2']['13']['20'];
	$qApartemen12 = @$qApartemen['3']['13']['20'];
	$qApartemen13 = @$qApartemen['1']['21']['24'];
	$qApartemen14 = @$qApartemen['2']['21']['24'];
	$qApartemen15 = @$qApartemen['1']['25']['99'];
	$qApartemen16 = @$qApartemen['2']['25']['99'];

	//Q 1.14
	$qSekolah = qSekolah();
	$qSekolah01 = @$qSekolah['1']['1']['2'];
	$qSekolah02 = @$qSekolah['2']['1']['2'];
	$qSekolah03 = @$qSekolah['1']['3']['5'];
	$qSekolah04 = @$qSekolah['2']['3']['5'];
	$qSekolah05 = @$qSekolah['1']['6']['99'];

	//Q 1.15
	$qMezanin = qMezanin();

	//Q 1.16
	$qKanopiBensin = qKanopiBensin();

	//Q 1.17
	$qDayaDukung = qDayaDukung();
	$qDayaDukung01 = @$qDayaDukung['1'];
	$qDayaDukung02 = @$qDayaDukung['2'];
	$qDayaDukung03 = @$qDayaDukung['3'];
	$qDayaDukung04 = @$qDayaDukung['4'];
	$qDayaDukung05 = @$qDayaDukung['5'];

	//Q 1.18
	$qTangkiBawahTanah = qTangkiBawahTanah();
	$qTangkiBawahTanah01 = @$qTangkiBawahTanah['2']['0']['1'];
	$qTangkiBawahTanah02 = @$qTangkiBawahTanah['2']['2']['3'];
	$qTangkiBawahTanah03 = @$qTangkiBawahTanah['2']['4']['5'];
	$qTangkiBawahTanah04 = @$qTangkiBawahTanah['2']['6']['7'];
	$qTangkiBawahTanah05 = @$qTangkiBawahTanah['2']['8']['10'];
	$qTangkiBawahTanah06 = @$qTangkiBawahTanah['2']['11']['13'];
	$qTangkiBawahTanah07 = @$qTangkiBawahTanah['2']['14']['16'];
	$qTangkiBawahTanah08 = @$qTangkiBawahTanah['2']['17']['20'];
	$qTangkiBawahTanah09 = @$qTangkiBawahTanah['2']['21']['25'];
	$qTangkiBawahTanah10 = @$qTangkiBawahTanah['2']['26']['30'];
	$qTangkiBawahTanah11 = @$qTangkiBawahTanah['2']['31']['40'];
	$qTangkiBawahTanah12 = @$qTangkiBawahTanah['2']['41']['50'];
	$qTangkiBawahTanah13 = @$qTangkiBawahTanah['2']['51']['60'];
	$qTangkiBawahTanah14 = @$qTangkiBawahTanah['2']['61']['80'];
	$qTangkiBawahTanah15 = @$qTangkiBawahTanah['2']['81']['99999'];

	//Q 1.19
	$qTangkiAtasTanah = $qTangkiBawahTanah;
	$qTangkiAtasTanah01 = @$qTangkiAtasTanah['1']['0']['50'];
	$qTangkiAtasTanah02 = @$qTangkiAtasTanah['1']['51']['75'];
	$qTangkiAtasTanah03 = @$qTangkiAtasTanah['1']['76']['100'];
	$qTangkiAtasTanah04 = @$qTangkiAtasTanah['1']['101']['150'];
	$qTangkiAtasTanah05 = @$qTangkiAtasTanah['1']['151']['200'];
	$qTangkiAtasTanah06 = @$qTangkiAtasTanah['1']['201']['250'];
	$qTangkiAtasTanah07 = @$qTangkiAtasTanah['1']['251']['500'];
	$qTangkiAtasTanah08 = @$qTangkiAtasTanah['1']['501']['750'];
	$qTangkiAtasTanah09 = @$qTangkiAtasTanah['1']['751']['1250'];
	$qTangkiAtasTanah10 = @$qTangkiAtasTanah['1']['1251']['1500'];
	$qTangkiAtasTanah11 = @$qTangkiAtasTanah['1']['1501']['1750'];
	$qTangkiAtasTanah12 = @$qTangkiAtasTanah['1']['1751']['2000'];
	$qTangkiAtasTanah13 = @$qTangkiAtasTanah['1']['2001']['2250'];
	$qTangkiAtasTanah14 = @$qTangkiAtasTanah['1']['2251']['2500'];
	$qTangkiAtasTanah15 = @$qTangkiAtasTanah['1']['2501']['2700'];
	$qTangkiAtasTanah16 = @$qTangkiAtasTanah['1']['2701']['3000'];
	$qTangkiAtasTanah17 = @$qTangkiAtasTanah['1']['3001']['3500'];
	$qTangkiAtasTanah18 = @$qTangkiAtasTanah['1']['3501']['4000'];
	$qTangkiAtasTanah19 = @$qTangkiAtasTanah['1']['4001']['4500'];
	$qTangkiAtasTanah20 = @$qTangkiAtasTanah['1']['4501']['5000'];
	$qTangkiAtasTanah21 = @$qTangkiAtasTanah['1']['5001']['6000'];
	$qTangkiAtasTanah22 = @$qTangkiAtasTanah['1']['6001']['7000'];
	$qTangkiAtasTanah23 = @$qTangkiAtasTanah['1']['7001']['8000'];
	$qTangkiAtasTanah24 = @$qTangkiAtasTanah['1']['8001']['9000'];
	$qTangkiAtasTanah25 = @$qTangkiAtasTanah['1']['9001']['10000'];
	$qTangkiAtasTanah26 = @$qTangkiAtasTanah['1']['10001']['12500'];
	$qTangkiAtasTanah27 = @$qTangkiAtasTanah['1']['12501']['15000'];
	$qTangkiAtasTanah28 = @$qTangkiAtasTanah['1']['15001']['17500'];
	$qTangkiAtasTanah29 = @$qTangkiAtasTanah['1']['17501']['99999'];

	//Q 2.1 A
	$qFasNonDep = qFasNonDep();
	$qACSplit = @$qFasNonDep['01'];
	//Q 2.1 B
	$qACWindow = @$qFasNonDep['02'];
	//Q 2.1 A.a
	//$qNilaiFasDepKlsBintang = qNilaiFasDepKlsBintang();
	$qACcentkantor01 = qNilaiFasDepKlsBintang('03', '02', '1');
	$qACcentkantor02 = qNilaiFasDepKlsBintang('03', '02', '2');
	$qACcentkantor03 = qNilaiFasDepKlsBintang('03', '02', '3');
	$qACcentkantor04 = qNilaiFasDepKlsBintang('03', '02', '4');

	$qACCentHotelkamar01 = qNilaiFasDepKlsBintang('04', '07', '1');
	$qACCentHotelkamar02 = qNilaiFasDepKlsBintang('04', '07', '2');
	$qACCentHotelkamar03 = qNilaiFasDepKlsBintang('04', '07', '3');
	$qACCentHotelkamar04 = qNilaiFasDepKlsBintang('04', '07', '4');
	$qACCentHotelkamar05 = qNilaiFasDepKlsBintang('04', '07', '5');

	$qACCentHotelRlain01 = qNilaiFasDepKlsBintang('05', '07', '1');
	$qACCentHotelRlain02 = qNilaiFasDepKlsBintang('05', '07', '2');
	$qACCentHotelRlain03 = qNilaiFasDepKlsBintang('05', '07', '3');
	$qACCentHotelRlain04 = qNilaiFasDepKlsBintang('05', '07', '4');
	$qACCentHotelRlain05 = qNilaiFasDepKlsBintang('05', '07', '5');

	$qACCentToko01 = qNilaiFasDepKlsBintang('06', '04', '1');
	$qACCentToko02 = qNilaiFasDepKlsBintang('06', '04', '2');
	$qACCentToko03 = qNilaiFasDepKlsBintang('06', '04', '3');

	$qACCentRSKamar01 = qNilaiFasDepKlsBintang('07', '05', '1');
	$qACCentRSKamar02 = qNilaiFasDepKlsBintang('07', '05', '2');
	$qACCentRSKamar03 = qNilaiFasDepKlsBintang('07', '05', '3');

	$qACCentRSRLain01   = qNilaiFasDepKlsBintang('08', '05', '1');
	$qACCentRSRLain02   = qNilaiFasDepKlsBintang('08', '05', '2');
	$qACCentRSRLain03   = qNilaiFasDepKlsBintang('08', '05', '3');

	$qACApartKamar01    = qNilaiFasDepKlsBintang('09', '13', '1');
	$qACApartKamar02    = qNilaiFasDepKlsBintang('09', '13', '2');

	$qACApartRlain01    = qNilaiFasDepKlsBintang('10', '13', '1');
	$qACApartRlain02    = qNilaiFasDepKlsBintang('10', '13', '2');

	$qACCentBangLain   = @$qFasNonDep['11'];

	$qBoilerHotel1    = qNilaiFasDepKlsBintang('43', '07', '1');
	$qBoilerHotel2    = qNilaiFasDepKlsBintang('43', '07', '2');
	$qBoilerHotel3    = qNilaiFasDepKlsBintang('43', '07', '3');
	$qBoilerHotel4    = qNilaiFasDepKlsBintang('43', '07', '4');
	$qBoilerHotel5    = qNilaiFasDepKlsBintang('43', '07', '5');

	$qBoilerApart01    = qNilaiFasDepKlsBintang('45', '13', '1');
	$qBoilerApart02    = qNilaiFasDepKlsBintang('45', '13', '2');
	$qBoilerApart03    = qNilaiFasDepKlsBintang('45', '13', '3');

	$qNilaiFasDepMinMax = qNilaiFasDepMinMax();

	$kolplest1         = @$qNilaiFasDepMinMax['12']['0']['50'];
	$kolplest2         = @$qNilaiFasDepMinMax['12']['51']['100'];
	$kolplest3         = @$qNilaiFasDepMinMax['12']['101']['200'];
	$kolplest4         = @$qNilaiFasDepMinMax['12']['201']['400'];
	$kolplest5         = @$qNilaiFasDepMinMax['12']['401']['999999'];


	$kolpelapis1         = @$qNilaiFasDepMinMax['13']['0']['50'];
	$kolpelapis2         = @$qNilaiFasDepMinMax['13']['51']['100'];
	$kolpelapis3         = @$qNilaiFasDepMinMax['13']['101']['200'];
	$kolpelapis4         = @$qNilaiFasDepMinMax['13']['201']['400'];
	$kolpelapis5         = @$qNilaiFasDepMinMax['13']['401']['999999'];

	$kerasringan    = @$qFasNonDep['14'];
	$kerasberat     = @$qFasNonDep['15'];
	$kerassedang    = @$qFasNonDep['16'];
	$keraspenutup   = @$qFasNonDep['17'];

	$tenissatulamp1  = @$qFasNonDep['18'];
	$tenissatulamp2  = @$qFasNonDep['19'];
	$tenissatulamp3  = @$qFasNonDep['20'];

	$tenisnolamp1  = @$qFasNonDep['21'];
	$tenisnolamp2  = @$qFasNonDep['22'];
	$tenisnolamp3  = @$qFasNonDep['23'];

	$tenislsatulamp1  = @$qFasNonDep['24'];
	$tenislsatulamp2  = @$qFasNonDep['25'];
	$tenislsatulamp3  = @$qFasNonDep['26'];

	$tenisksatulamp1  = @$qFasNonDep['27'];
	$tenisksatulamp2  = @$qFasNonDep['28'];
	$tenisksatulamp3  = @$qFasNonDep['29'];

	$liftbiasa1 = @$qNilaiFasDepMinMax['30']['0']['4'];
	$liftbiasa2 = @$qNilaiFasDepMinMax['30']['5']['9'];
	$liftbiasa3 = @$qNilaiFasDepMinMax['30']['10']['19'];
	$liftbiasa4 = @$qNilaiFasDepMinMax['30']['20']['99'];

	$liftkapsul1 = @$qNilaiFasDepMinMax['31']['0']['4'];
	$liftkapsul2 = @$qNilaiFasDepMinMax['31']['5']['9'];
	$liftkapsul3 = @$qNilaiFasDepMinMax['31']['10']['19'];
	$liftkapsul4 = @$qNilaiFasDepMinMax['31']['20']['99'];

	$liftbarang1 = @$qNilaiFasDepMinMax['32']['0']['4'];
	$liftbarang2 = @$qNilaiFasDepMinMax['32']['5']['9'];
	$liftbarang3 = @$qNilaiFasDepMinMax['32']['10']['19'];
	$liftbarang4 = @$qNilaiFasDepMinMax['32']['20']['99'];

	$tanggajalan1 = @$qFasNonDep['33'];
	$tanggajalan2 = @$qFasNonDep['34'];

	$pagar1       = @$qFasNonDep['35'];
	$pagar2       = @$qFasNonDep['36'];

	$protek1 = @$qFasNonDep['37'];
	$protek2 = @$qFasNonDep['38'];
	$protek3 = @$qFasNonDep['39'];

	$genset = @$qFasNonDep['40'];

	$pabx = @$qFasNonDep['41'];

	$artesis = @$qFasNonDep['42'];

	$listrik = @$qFasNonDep['44'];
	$qNilaiMaterial = qNilaiMaterial();
	$atap1 = @$qNilaiMaterial['23']['01'];
	$atap2 = @$qNilaiMaterial['23']['02'];
	$atap3 = @$qNilaiMaterial['23']['03'];
	$atap4 = @$qNilaiMaterial['23']['04'];
	$atap5 = @$qNilaiMaterial['23']['05'];

	$dinding1 = @$qNilaiMaterial['21']['01'];
	$dinding2 = @$qNilaiMaterial['21']['09'];
	$dinding3 = @$qNilaiMaterial['21']['02'];
	$dinding4 = @$qNilaiMaterial['21']['03'];
	$dinding5 = @$qNilaiMaterial['21']['07'];
	$dinding6 = @$qNilaiMaterial['21']['08'];

	$lantai1 = @$qNilaiMaterial['22']['01'];
	$lantai2 = @$qNilaiMaterial['22']['02'];
	$lantai3 = @$qNilaiMaterial['22']['03'];
	$lantai4 = @$qNilaiMaterial['22']['04'];
	$lantai5 = @$qNilaiMaterial['22']['05'];

	$langit1 = @$qNilaiMaterial['24']['01'];
	$langit2 = @$qNilaiMaterial['24']['02'];

	$qStandard  = qStandard();
	$qStandardrmh1  = @$qStandard['01']['045']['1_1_045'];
	$qStandardrmh2  = @$qStandard['01']['076']['1_1_076'];
	$qStandardrmh3  = @$qStandard['01']['145']['1_1_145'];
	$qStandardrmh4  = @$qStandard['01']['150']['1_1_150'];
	$qStandardrmh5  = @$qStandard['01']['295']['1_1_295'];
	$qStandardrmh6  = @$qStandard['01']['314']['1_1_314'];
	$qStandardrmh7  = @$qStandard['01']['500']['1_1_500'];
	$qStandardrmh8  = @$qStandard['01']['656']['1_1_656'];
	$qStandardrmh9  = @$qStandard['01']['045']['1_1_045'];
	$qStandardrmh10 = @$qStandard['01']['076']['1_2_076'];
	$qStandardrmh11 = @$qStandard['01']['134']['1_2_134'];
	$qStandardrmh12 = @$qStandard['01']['218']['1_2_218'];
	$qStandardrmh13 = @$qStandard['01']['257']['1_2_257'];
	$qStandardrmh14 = @$qStandard['01']['375']['1_2_375'];
	$qStandardrmh15 = @$qStandard['01']['474']['1_2_474'];
	$qStandardrmh16 = @$qStandard['01']['555']['1_2_555'];

	$qStandardkantor1 = @$qStandard['02']['045']['2_1_045'];
	$qStandardkantor2 = @$qStandard['02']['076']['2_1_076'];
	$qStandardkantor3 = @$qStandard['02']['134']['2_1_134'];
	$qStandardkantor4 = @$qStandard['02']['216']['2_1_216'];
	$qStandardkantor5 = @$qStandard['02']['262']['2_1_262'];
	$qStandardkantor6 = @$qStandard['02']['432']['2_1_432'];
	$qStandardkantor7 = @$qStandard['02']['648']['2_1_648'];
	$qStandardkantor8 = @$qStandard['02']['864']['2_1_864'];
	$qStandardkantor9 = @$qStandard['02']['045']['2_2_045'];
	$qStandardkantor10 = @$qStandard['02']['076']['2_2_076'];
	$qStandardkantor11 = @$qStandard['02']['134']['2_2_134'];
	$qStandardkantor12 = @$qStandard['02']['216']['2_2_216'];
	$qStandardkantor13 = @$qStandard['02']['262']['2_2_262'];
	$qStandardkantor14 = @$qStandard['02']['432']['2_2_432'];
	$qStandardkantor15 = @$qStandard['02']['648']['2_2_648'];
	$qStandardkantor16 = @$qStandard['02']['864']['2_2_864'];

	$qStandardRmhSkt1 = @$qStandard['05']['045']['5_1_045'];
	$qStandardRmhSkt2 = @$qStandard['05']['076']['5_1_076'];
	$qStandardRmhSkt3 = @$qStandard['05']['134']['5_1_134'];
	$qStandardRmhSkt4 = @$qStandard['05']['216']['5_1_216'];
	$qStandardRmhSkt5 = @$qStandard['05']['262']['5_1_262'];
	$qStandardRmhSkt6 = @$qStandard['05']['432']['5_1_432'];
	$qStandardRmhSkt7 = @$qStandard['05']['648']['5_1_648'];
	$qStandardRmhSkt8 = @$qStandard['05']['864']['5_1_864'];
	$qStandardRmhSkt9 = @$qStandard['05']['045']['5_2_045'];
	$qStandardRmhSkt10 = @$qStandard['05']['076']['5_2_076'];
	$qStandardRmhSkt11 = @$qStandard['05']['134']['5_2_134'];
	$qStandardRmhSkt12 = @$qStandard['05']['216']['5_2_216'];
	$qStandardRmhSkt13 = @$qStandard['05']['262']['5_2_262'];
	$qStandardRmhSkt14 = @$qStandard['05']['432']['5_2_432'];
	$qStandardRmhSkt15 = @$qStandard['05']['648']['5_2_648'];
	$qStandardRmhSkt16 = @$qStandard['05']['864']['5_2_864'];


	$html = "
	<html>
	<table border=\"0\" cellpadding=\"5\" width=\"500\">
		<tr>
			<td width=\"450\">
			</td>
			<td>
				<table border=\"0\">
					<tr>
						<td colspan=\"2\">LAMPIRAN II : </td>
					</tr>
					<tr>
						<td colspan=\"2\">KEPUTUSAN BUPATI PESAWARAN</td>
					</tr>
					<tr>
						<td width=\"60\">Nomor292/KEP/DPKAD/VI/2016</td><td>:</td> 
					</tr>
					<tr>
						<td>Tanggal 06 JUNI 2019</td><td>:</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : 19 - {$propinsi}<br>
				KAB/KOTA : 71 - {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"3\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!--
					<tr>
						<td align=\"center\">(1)</td>
						<td align=\"center\">(2)</td>
						<td align=\"center\">(3)</td>
						<td align=\"center\">(4)</td>
						<td align=\"center\">(5)</td>
					</tr>
					-->
					<tr>
						<td align=\"center\">1.</td>
						<td align=\"left\">KOMPONEN UTAMA</td>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
					</tr>
					<!-- ===================================PERUMAHAN==================================== -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.1 Perumahan</td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh1}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh2}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh3}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh4}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh5}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh6}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 549</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh7}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=550</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardrmh8}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh9}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh10}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh11}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh12}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh13}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh14}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 549</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh15}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=550</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardrmh16}</td>
					</tr>
					<!-- END PERUMAHAN -->
					<!-- KANTOR -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.1 Kantor,Apotik,Toko,Pasar,Ruko,Restoran,Hotel,Wisma,Gedung Pemerintah</td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor1}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor2}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor3}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor4}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor5}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor6}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 549</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor7}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=550</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardkantor8}</td>
					</tr>
					<!--  ============2 s.d 4============= -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor9}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor10}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor11}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor12}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor13}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor14}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 549</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor15}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=650</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardkantor16}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- PERUMAHAN -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.3 Rumah Sakit</td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt1}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt2}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt3}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt4}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt5}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt6}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 549</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt7}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=550</td>
						<td align=\"center\">1</td>
						<td align=\"right\">{$qStandardRmhSkt8}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1 s.d. 69</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt9}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">70 s.d. 99</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt10}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">100 s.d. 149</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt11}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">150 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt12}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">255 s.d. 299</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt13}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">300 s.d. 449</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt14}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">450 s.d. 649</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt15}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>=650</td>
						<td align=\"center\">2 s.d. 4</td>
						<td align=\"right\">{$qStandardRmhSkt16}</td>
					</tr>
					<!-- BENGKEL -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.4 Bengkel/Gudang/Pertanian</td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP01 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP02 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP03 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP04 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP05 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP06 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP07 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP08 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qBGP09 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP11 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP12 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP13 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP14 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP15 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP16 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP17 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP18 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qBGP19 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP21 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP22 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP23 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP24 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP25 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP26 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP27 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP28 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qBGP29 . "</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br><br><br><br>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- PERUMAHAN -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.4 Bengkel/Gudang/Pertanian</td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP31 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP32 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP33 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP34 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP35 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP36 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP37 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP38 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qBGP39 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.5 Pabrik</td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik01 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik02 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik03 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik04 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik05 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik06 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik07 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik08 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">&lt;5</td>
						<td align=\"right\">" . $qPabrik09 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik11 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik12 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik13 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik14 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik15 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik16 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik17 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik18 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">5 s.d 7</td>
						<td align=\"right\">" . $qPabrik19 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik21 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik22 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik23 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik24 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik25 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik26 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik27 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik28 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">8 s.d 10</td>
						<td align=\"right\">" . $qPabrik29 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&lt;10</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik31 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10 sd 13</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik32 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 sd 17</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik33 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">18 sd 21</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik34 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">22 sd 25</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik35 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 sd 29</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik36 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">30 sd 33</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik37 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">34 sd 37</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik38 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">&gt;37</td>
						<td align=\"center\">&gt;10</td>
						<td align=\"right\">" . $qPabrik39 . "</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br><br><br><br>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<td width=\"40\" align=\"center\" rowspan=\"2\">NO</td>
						<td width=\"180\" align=\"center\" rowspan=\"2\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</td>
						<td align=\"center\" rowspan=\"2\">JUMLAH LANTAI</td>
						<td width=\"400\" align=\"center\" colspan=\"6\">KELAS/TYPE/<br>BINTANG</td>
					</tr>
					<!-- KANTOR -->
					<tr>
						<td align=\"center\">1</td>
						<td align=\"center\">2</td>
						<td align=\"center\">3</td>
						<td align=\"center\">4</td>
						<td align=\"center\">5</td>
						<td align=\"center\">T.A</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.6 Kantor</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qKantor01 . "</td>
						<td align=\"right\">" . $qKantor02 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qKantor03 . "</td>
						<td align=\"right\">" . $qKantor04 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6 s.d 12</td>
						<td align=\"right\">" . $qKantor05 . "</td>
						<td align=\"right\">" . $qKantor06 . "</td>
						<td align=\"right\">" . $qKantor07 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">13 s.d 20</td>
						<td align=\"right\">" . $qKantor08 . "</td>
						<td align=\"right\">" . $qKantor09 . "</td>
						<td align=\"right\">" . $qKantor10 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">21 s.d 24</td>
						<td align=\"right\">" . $qKantor11 . "</td>
						<td align=\"right\">" . $qKantor12 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>24</td>
						<td align=\"right\">" . $qKantor13 . "</td>
						<td align=\"right\">" . $qKantor14 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- PERTOKOAN -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.7 Pertokoan</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qPertokoan01 . "</td>
						<td align=\"right\">" . $qPertokoan02 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 4</td>
						<td align=\"right\">" . $qPertokoan03 . "</td>
						<td align=\"right\">" . $qPertokoan04 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>4</td>
						<td align=\"right\">" . $qPertokoan05 . "</td>
						<td align=\"right\">" . $qPertokoan06 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- Rumah Sakit -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.8 Rumah Sakit</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qRmhSakit01 . "</td>
						<td align=\"right\">" . $qRmhSakit02 . "</td>
						<td align=\"right\">" . $qRmhSakit03 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">" . $qRmhSakit04 . "</td>
						<td align=\"right\">" . $qRmhSakit05 . "</td>
						<td align=\"right\">" . $qRmhSakit06 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>5</td>
						<td align=\"right\">" . $qRmhSakit07 . "</td>
						<td align=\"right\">" . $qRmhSakit08 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- OLAG RAGA -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.9 Olah Raga</td>
						<td align=\"center\">-</td>
						<td align=\"right\">" . $qOlahRaga01 . "</td>
						<td align=\"right\">" . $qOlahRaga02 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- HOTEL NON RESORT -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.10 Hotel Non Resort</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">" . $qHotelNonResort01 . "</td>
						<td align=\"right\">" . $qHotelNonResort02 . "</td>
						<td align=\"right\">" . $qHotelNonResort03 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qHotelNonResort04 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">" . $qHotelNonResort05 . "</td>
						<td align=\"right\">" . $qHotelNonResort06 . "</td>
						<td align=\"right\">" . $qHotelNonResort07 . "</td>
						<td align=\"right\">" . $qHotelNonResort08 . "</td>
						<td align=\"right\">" . $qHotelNonResort09 . "</td>
						<td align=\"right\">" . $qHotelNonResort10 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6 s.d 12</td>
						<td align=\"right\">" . $qHotelNonResort11 . "</td>
						<td align=\"right\">" . $qHotelNonResort12 . "</td>
						<td align=\"right\">" . $qHotelNonResort13 . "</td>
						<td align=\"right\">" . $qHotelNonResort14 . "</td>
						<td align=\"right\">" . $qHotelNonResort15 . "</td>
						<td align=\"right\">" . $qHotelNonResort16 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">13 s.d 20</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qHotelNonResort17 . "</td>
						<td align=\"right\">" . $qHotelNonResort18 . "</td>
						<td align=\"right\">" . $qHotelNonResort19 . "</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">21 s.d 24</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qHotelNonResort20 . "</td>
						<td align=\"right\">" . $qHotelNonResort21 . "</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>24</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qHotelNonResort22 . "</td>
						<td align=\"right\">" . $qHotelNonResort23 . "</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- Hotel -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.11 Hotel Resort</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">" . $qHotelResort01 . "</td>
						<td align=\"right\">" . $qHotelResort02 . "</td>
						<td align=\"right\">" . $qHotelResort03 . "</td>
						<td align=\"right\">" . $qHotelResort04 . "</td>
						<td align=\"right\">" . $qHotelResort05 . "</td>
						<td align=\"right\">" . $qHotelResort06 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">" . $qHotelResort07 . "</td>
						<td align=\"right\">" . $qHotelResort08 . "</td>
						<td align=\"right\">" . $qHotelResort09 . "</td>
						<td align=\"right\">" . $qHotelResort10 . "</td>
						<td align=\"right\">" . $qHotelResort11 . "</td>
						<td align=\"right\">" . $qHotelResort12 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6 s.d 12</td>
						<td align=\"right\">" . $qHotelResort13 . "</td>
						<td align=\"right\">" . $qHotelResort14 . "</td>
						<td align=\"right\">" . $qHotelResort15 . "</td>
						<td align=\"right\">" . $qHotelResort16 . "</td>
						<td align=\"right\">" . $qHotelResort17 . "</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- Parkir -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.12 Parkir</td>
						<td align=\"center\">-</td>
						<td align=\"right\">" . $qParkir01 . "</td>
						<td align=\"right\">" . $qParkir02 . "</td>
						<td align=\"right\">" . $qParkir03 . "</td>
						<td align=\"right\">" . $qParkir04 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- APARTEMEN -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.13 Apartemen</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qApartemen01 . "</td>
						<td align=\"right\">" . $qApartemen02 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">-</td>
						<td align=\"right\">" . $qApartemen03 . "</td>
						<td align=\"right\">" . $qApartemen04 . "</td>
						<td align=\"right\">" . $qApartemen05 . "</td>
						<td align=\"right\"></td>
						<td align=\"right\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6 s.d 12</td>
						<td align=\"right\">" . $qApartemen06 . "</td>
						<td align=\"right\">" . $qApartemen07 . "</td>
						<td align=\"right\">" . $qApartemen08 . "</td>
						<td align=\"right\">" . $qApartemen09 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">13 s.d 20</td>
						<td align=\"right\">" . $qApartemen10 . "</td>
						<td align=\"right\">" . $qApartemen11 . "</td>
						<td align=\"right\">" . $qApartemen12 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">21 s.d 24</td>
						<td align=\"right\">" . $qApartemen13 . "</td>
						<td align=\"right\">" . $qApartemen14 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>24</td>
						<td align=\"right\">" . $qApartemen15 . "</td>
						<td align=\"right\">" . $qApartemen16 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- Sekolah -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.14 Sekolah</td>
						<td align=\"center\">1 s.d 2</td>
						<td align=\"right\">" . $qSekolah01 . "</td>
						<td align=\"right\">" . $qSekolah02 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3 s.d 5</td>
						<td align=\"right\">" . $qSekolah03 . "</td>
						<td align=\"right\">" . $qSekolah04 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>5</td>
						<td align=\"right\">" . $qSekolah05 . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>
					<!-- MEZANIN -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.15 Mezanin</td>
						<td align=\"center\">-</td>
						<td align=\"right\">" . $qMezanin . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>	
					<!-- Kanopi Pompa Bensin -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.16 Kanopi Pompa Bensin</td>
						<td align=\"center\">-</td>
						<td align=\"right\">" . $qKanopiBensin . "</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
						<td align=\"right\">-</td>
					</tr>					
				</table>
			</td>
		</tr>
	</table>
	<br><br><br><br><br><br><br><br><br><br><br><br>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- Daya Dukung Lantai -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.17 Daya Dukung Lantai</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;A. Ringan</td>
						<td align=\"center\">1 s.d. 600</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qDayaDukung01 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;B. Sedang</td>
						<td align=\"center\">601 s.d. 1200</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qDayaDukung02 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;C. Menengah</td>
						<td align=\"center\">1201 s.d. 2400</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qDayaDukung03 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;D. Berat</td>
						<td align=\"center\">2401 s.d. 5000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qDayaDukung04 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;E. Sangat Berat</td>
						<td align=\"center\">>5000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qDayaDukung05 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.18 Tangki Dibawah Tanah</td>
						<td align=\"center\">&lt;2</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah01 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">2 s.d. 3</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah02 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">4 s.d. 5</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah03 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6 s.d. 7</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah04 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">8 s.d. 10</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah05 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">11 s.d. 13</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah06 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">14 s.d. 16</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah07 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">17 s.d. 20</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah08 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">21 s.d. 25</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah09 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">26 s.d. 30</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah10 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">31 s.d. 40</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah11 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">41 s.d. 50</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah12 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">51 s.d. 60</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah13 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">61 s.d. 80</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah14 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>80</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $qTangkiBawahTanah15 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">1.19 Tangki Diatas Tanah</td>
						<td align=\"center\">&lt;51</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah01}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">51 s.d. 75</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah02}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">76 s.d. 100</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah03}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">101 s.d. 150</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah04}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">151 s.d. 200</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah05}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">201 s.d. 250</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah06}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">251 s.d. 500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah07}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">501 s.d. 750</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah08}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">751 s.d. 1250</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah09}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1251 s.d. 1500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah10}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1501 s.d. 1750</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah11}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">1751 s.d. 2000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah12}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">2001 s.d. 2250</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah13}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">2251 s.d. 2500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah14}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">2501 s.d. 2750</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah15}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">2751 s.d. 3000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah16}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3001 s.d. 3500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah17}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">3501 s.d. 4000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah18}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">4001 s.d. 4500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah19}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">4501 s.d. 5000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah20}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">5001 s.d. 6000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah21}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">6001 s.d. 7000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah22}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">7001 s.d. 8000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah23}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">8001 s.d. 9000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah24}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">9001 s.d. 10000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah25}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">10001 s.d. 12500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah26}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">12501 s.d. 15000</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah27}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">15001 s.d. 17500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah28}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>17500</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$qTangkiAtasTanah29}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<td width=\"40\" align=\"center\">NO</td>
						<td width=\"180\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</td>
						<td align=\"center\">JUMLAH LANTAI</td>
						<td width=\"400\" align=\"center\" colspan=\"6\">KELAS/TYPE/<br>BINTANG</td>
					</tr>
					<!-- KANTOR -->
					<tr>
						<td align=\"center\">2.</td>
						<td align=\"left\">FASILITAS</td>
						<td align=\"center\"></td>
						<td align=\"center\">1</td>
						<td align=\"center\">2</td>
						<td align=\"center\">3</td>
						<td align=\"center\">4</td>
						<td align=\"center\">5</td>
						<td align=\"center\">T.A</td>
					</tr>	
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.1 Air Condition (AC)</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>	
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. AC - Split</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACSplit . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. AC - Window</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACWindow . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C. AC - Central</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Kantor</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACcentkantor01 . "</td>
						<td align=\"center\">{$qACcentkantor02}</td>
						<td align=\"center\">" . $qACcentkantor03 . "</td>
						<td align=\"center\">{$qACcentkantor04}</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Hotel</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Kamar</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentHotelkamar04 . "</td>
						<td align=\"center\">" . $qACCentHotelkamar04 . "</td>
						<td align=\"center\">" . $qACCentHotelkamar03 . "</td>
						<td align=\"center\">" . $qACCentHotelkamar02 . "</td>
						<td align=\"center\">" . $qACCentHotelkamar01 . "</td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Ruangan Lain</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentHotelRlain04 . "</td>
						<td align=\"center\">" . $qACCentHotelRlain04 . "</td>
						<td align=\"center\">" . $qACCentHotelRlain03 . "</td>
						<td align=\"center\">" . $qACCentHotelRlain02 . "</td>
						<td align=\"center\">" . $qACCentHotelkamar01 . "</td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Pertokoan</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentToko01 . "</td>
						<td align=\"center\">" . $qACCentToko02 . "</td>
						<td align=\"center\">" . $qACCentToko03 . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Rumah Sakit</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Kamar</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentRSKamar01 . "</td>
						<td align=\"center\">" . $qACCentRSKamar02 . "</td>
						<td align=\"center\">" . $qACCentRSKamar03 . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Ruangan Lain</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentRSRLain01 . "</td>
						<td align=\"center\">" . $qACCentRSRLain02 . "</td>
						<td align=\"center\">" . $qACCentRSRLain03 . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;e. Apartemen</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Kamar</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACApartKamar01 . "</td>
						<td align=\"center\">" . $qACApartKamar02 . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Ruangan Lain</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACApartRlain01 . "</td>
						<td align=\"center\">" . $qACApartRlain02 . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;f. Bangunan Lain</td>
						<td align=\"center\"></td>
						<td align=\"center\">" . $qACCentBangLain . "</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.2 Boiler</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Per Kamar Hotel</td>
						<td align=\"center\"></td>
						<td align=\"center\">{$qBoilerHotel4}</td>
						<td align=\"center\">{$qBoilerHotel4}</td>
						<td align=\"center\">{$qBoilerHotel3}</td>
						<td align=\"center\">{$qBoilerHotel2}</td>
						<td align=\"center\">{$qBoilerHotel1}</td>
						<td align=\"center\">{$qBoilerHotel5}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Per Kamar Apart</td>
						<td align=\"center\"></td>
						<td align=\"center\">{$qBoilerApart01}</td>
						<td align=\"center\">{$qBoilerApart02}</td>
						<td align=\"center\">{$qBoilerApart03}</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- Kolam Renang -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.3 Kolam Renang</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Diplester</td>
						<td align=\"center\">&lt;51</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolplest1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">51 s.d. 100</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolplest2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">101 s.d. 200</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolplest3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">201 s.d. 400</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolplest4 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>400</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolplest5 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Dengan Pelapis</td>
						<td align=\"center\">&lt;51</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolpelapis1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">51 s.d. 100</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolpelapis2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">101 s.d. 200</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolpelapis3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">201 s.d. 400</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolpelapis4 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\">>400</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kolpelapis5 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.4 Perkerasan</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Ringan</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kerasringan . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Sedang</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kerasberat . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C. Berat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $kerassedang . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D. Penutup</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $keraspenutup . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.5 Lapangan Tenis</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Satu ban dengan lampu</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Beton</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenissatulamp1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Aspal</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenissatulamp2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Tanah Liat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenissatulamp3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Satu ban tanpa lampu</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Beton</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisnolamp1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Aspal</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisnolamp2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Tanah Liat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisnolamp3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C. >1 ban dengan lampu</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Beton</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenislsatulamp1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Aspal</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenislsatulamp2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Tanah Liat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenislsatulamp3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;D. >1 ban tanpa lampu</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Beton</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisksatulamp1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Aspal</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisksatulamp2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Tanah Liat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tenisksatulamp3 . "</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- LIFT -->
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.6 Lift</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Penumpang Biasa</td>
						<td align=\"center\"></td>
						<td align=\"center\">&lt;5</td>
						<td align=\"center\">" . $liftbiasa1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">5 s.d. 9</td>
						<td align=\"center\">" . $liftbiasa2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">10 s.d. 19</td>
						<td align=\"center\">" . $liftbiasa3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">>19</td>
						<td align=\"center\">" . $liftbiasa4 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Kapsul</td>
						<td align=\"center\"></td>
						<td align=\"center\">&lt;5</td>
						<td align=\"center\">" . $liftkapsul1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">5 s.d. 9</td>
						<td align=\"center\">" . $liftkapsul2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">10 s.d. 19</td>
						<td align=\"center\">" . $liftkapsul3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">>19</td>
						<td align=\"center\">" . $liftkapsul4 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C. Barang</td>
						<td align=\"center\"></td>
						<td align=\"center\">&lt;5</td>
						<td align=\"center\">" . $liftbarang1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">5 s.d. 9</td>
						<td align=\"center\">" . $liftbarang2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">10 s.d. 19</td>
						<td align=\"center\">" . $liftbarang3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\"></td>
						<td align=\"center\"></td>
						<td align=\"center\">>19</td>
						<td align=\"center\">" . $liftbarang4 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.7 Tangga Berjalan/Esc</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
                                       
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Lebar &lt;= 80 cm</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tanggajalan1 . "</td>
					</tr>
					
                                        <tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Lebar > 80 cm</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $tanggajalan2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.8 Pagar</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Bata/Batako</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $pagar1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Baja/Besi</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $pagar2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.9 Proteksi Api</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A. Hydrant</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $protek1 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;B. Fire Alarm</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $protek2 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;C. Sprinkler</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $protek3 . "</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.10 Saluran Pes. PABX</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$pabx}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.11 Sumur Artesis</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$artesis}</td>
					</tr>
					<tr>
						<td align=\"left\"></td>
						<td align=\"left\">2.12 Listrik/KVA</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">{$listrik}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br><br><br><br><br>
	<table border=\"0\" cellpadding=\"5\" width=\"700\">
		<tr>
			<td colspan=\"2\" align=\"center\">DAFTAR BIAYA KOMPONEN BANGUNAN (DBKB)<br>TAHUN " . $appConfig['tahun_tagihan'] . "</td>
		</tr>
		<tr>
			<td width=\"350\">
				{$kanwil}<br>
				{$kpp}
			</td>
			<td width=\"350\">
				PROPINSI : {$propinsi}<br>
				KAB/KOTA : {$kota}
			</td>
		</tr>
		<tr>
			<td colspan=\"2\">
				<table border=\"1\" cellpadding=\"1\">
					<tr>
						<th width=\"40\" align=\"center\">NO</th>
						<th width=\"240\" align=\"center\">KOMPONEN JENIS PENGGUNAAN BANGUNAN</th>
						<th align=\"center\">LUAS/TYPE/VOL./LBR BTG</th>
						<th align=\"center\">LANTAI/<br>TINGGI KLM</th>
						<th align=\"center\">NILAI<br>(RP 1.000,-)</th>
					</tr>
					<!-- LIFT -->
					<tr>
						<td align=\"center\">3</td>
						<td align=\"left\">KOMPONEN MATERIAL</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">3.1 ATAP</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Dec/Beton/Gt Glat</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $atap1 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Gt. Beton/Alm</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $atap2 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Gt. Biasa/Sirap</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $atap3 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Asbes</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $atap4 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;e. Seng</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $atap5 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">3.2 DINDING</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Kaca</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding1 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Alm./Spandex</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding2 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Beton</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding3 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Batu-bata</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding4 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;e. Kayu</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding5 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;f. Seng</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $dinding6 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">3.3 LANTAI</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Marmer</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $lantai1 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Keramik</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $lantai2 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c. Teraso</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $lantai3 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d. Ubin PC/Papan</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $lantai4 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;e. Semen</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $lantai5 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">3.4 LANGIT-LANGIT</td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
						<td align=\"center\"></td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a. Akustik/Jati</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $langit1 . "</td>
					</tr>
					<tr>
						<td align=\"center\"></td>
						<td align=\"left\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b. Trip/Asbes/Bambu</td>
						<td align=\"center\">-</td>
						<td align=\"center\">-</td>
						<td align=\"center\">" . $langit2 . "</td>
					</tr>
				</table>
                               
			</td>
		</tr>
	</table>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<table border=\"0\" cellpadding=\"5\" width=\"200\">
		<tr >
			<td colspan=\"2\" ><br><br><br><br><br><br><br><br><br><br><br>BUPATI PESAWARAN<br><br><br><br><br><br><br>" . $appConfig['WALIKOTA_NAMA'] . "<br> " . $appConfig['WALIKOTA_NIP'] . "</td>
		</tr>
		
                </table>
</html>";
	return $html;
}

function getInitData($id = "")
{
	global $DBLink;

	if ($id == '') return getDataDefault();

	$qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
		return getDataDefault();
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
			return $row;
		}
	}
}

function getDataDefault()
{
	$default = array(
		'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
		'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
		'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
	);
}
// print_r($q); exit;

class MYPDF extends TCPDF
{
	public function Footer()
	{
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', '', 8);
		// Page number
		$this->Cell(0, 10, 'HAL. ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('vpost');
$pdf->SetTitle('Alfa System');
$pdf->SetSubject('Alfa System spppd');
$pdf->SetKeywords('Alfa System');

// remove default header/footer
$pdf->setPrintHeader(false);
//$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(5, 14, 5);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetProtection($permissions = array('modify'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);

$HTML = "";
$pdf->AddPage('P', 'A4');
$HTML = getHTML();
$pdf->writeHTML($HTML, true, false, false, false, '');

//Close and output PDF document
$pdf->Output('SK_DBKB.pdf', 'I');
