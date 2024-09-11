<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_bangunan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-dms-c.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");
// echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";


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

class PriceFacility
{
	function __construct($userGroup, $user)
	{
		$this->userGroup = $userGroup;
		$this->user = $user;
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

	function getDataACSplit()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('01')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data = mysqli_fetch_assoc($res);
		return $data;
	}

	function getDataACWindow()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('02')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data = mysqli_fetch_assoc($res);
		return $data;
	}

	function getDataACCentralKantor()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS = '03'
				AND A.KD_JPB = '02'
				AND A.KLS_BINTANG IN ('1', '3')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataACCentralHotel()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS IN ('04','05')
				AND A.KD_JPB = '07'
				AND A.KLS_BINTANG IN ('4', '3')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataACCentralToko()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS = '06'
				AND A.KD_JPB = '04'
				AND A.KLS_BINTANG IN ('1', '2', '3')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataACCentralRS()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND ((A.KD_FASILITAS = '07'
				AND A.KD_JPB = '05'
				AND A.KLS_BINTANG IN ('1', '3')) 
				OR (A.KD_FASILITAS = '08'
				AND A.KD_JPB = '05'
				AND A.KLS_BINTANG = '1')) ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataACCentralApt()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS IN ('09','10')
				AND A.KD_JPB = '13'
				AND A.KLS_BINTANG = '1' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataACCentralBngLain()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS = '11' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data = mysqli_fetch_assoc($res);
		return $data;
	}

	function getDataKlmRenang()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_min_max A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_MIN_MAX = '{$tahun}'
				AND B.KD_FASILITAS IN ('12','13') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataPerkerasan()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('14','15','16','17') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataLapTenis()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('18','19','20','21','22','23','24','25','26','27','28','29') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataLift()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_min_max A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_MIN_MAX = '{$tahun}'
				AND B.KD_FASILITAS IN ('30','31','32') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataEscalator()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('33','34') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataPagar()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('35','36') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataProtApi()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS IN ('37','38','39') ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}

	function getDataGenset()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_min_max A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_MIN_MAX = '{$tahun}'
				AND B.KD_FASILITAS = '40' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getDataPABX()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS= '41' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getDataAirArt()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS= '42' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getDataBoilerHotel()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS ='43'
				AND A.KD_JPB = '07'
				AND A.KLS_BINTANG IN ('5','4','2')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getDataBoilerApart()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_dep_jpb_kls_bintang A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
				A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_DEP_JPB_KLS_BINTANG = '{$tahun}'
				AND A.KD_FASILITAS ='45'
				AND A.KD_JPB = '13'
				AND A.KLS_BINTANG IN ('1','3')";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getDataListrik()
	{
		global $DBLink, $json, $appConfig;

		$propinsi 	= substr($appConfig['KODE_KOTA'], 0, 2);
		$kota		= substr($appConfig['KODE_KOTA'], 2, 2);
		$tahun		= $appConfig['tahun_tagihan'];

		$query = "SELECT * FROM
				cppmod_pbb_fas_non_dep A
				JOIN cppmod_pbb_fasilitas B ON A.KD_FASILITAS = B.KD_FASILITAS
				WHERE
					A.KD_PROPINSI = '{$propinsi}'
				AND A.KD_DATI2 = '{$kota}'
				AND A.THN_NON_DEP = '{$tahun}'
				AND B.KD_FASILITAS= '44' ";
		// echo $query; exit;

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return false;
		}
		$data	= array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function getHTMLAC()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$ACSplit			= $this->getDataACSplit();
		$ACWindow			= $this->getDataACWindow();
		$ACCentralKantor	= $this->getDataACCentralKantor();
		$ACCentralHotel		= $this->getDataACCentralHotel();
		$ACCentralToko		= $this->getDataACCentralToko();
		$ACCentralRS		= $this->getDataACCentralRS();
		$ACCentralApt		= $this->getDataACCentralApt();
		$ACCentralBngLain	= $this->getDataACCentralBngLain();

		// echo "<pre>";
		// print_r($ACCentralApt);

		$nilainonDep = isset($ACSplit['NILAI_NON_DEP']) && $ACSplit['NILAI_NON_DEP'] != null ? number_format($ACSplit['NILAI_NON_DEP'], '0', ',', '') : '';
		$nilaiacWindow = isset($ACWindow['NILAI_NON_DEP']) && $ACWindow['NILAI_NON_DEP'] != null ? number_format($ACWindow['NILAI_NON_DEP'], '0', ',', '') : '-';
		$nilaiacCentral[0] = isset($ACCentralKantor[0]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralKantor[0]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralKantor[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';
		$nilaiacCentral[1] = isset($ACCentralKantor[1]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralKantor[1]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralKantor[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';
		$nilaiacHotel[0] = isset($ACCentralHotel[0]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralHotel[0]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralHotel[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';
		$nilaiacHotel[1] = isset($ACCentralHotel[1]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralHotel[1]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralHotel[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';
		$nilaiacHotel[2] = isset($ACCentralHotel[2]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralHotel[2]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralHotel[2]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';
		$nilaiacHotel[3] = isset($ACCentralHotel[3]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralHotel[3]['NILAI_FASILITAS_KLS_BINTANG'] != null ? number_format($ACCentralHotel[3]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '';

		$HTML = "";
		//$HTML .= "\t<div class=\"container\">\n";
		$HTML .= "
				<div class=\"row\">
					<div class=\"col-md-12\">
						<label><b>A. AC SPLIT</b></label><hr>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACSplit['KD_FASILITAS']) && $ACSplit['KD_FASILITAS'] != null ? $ACSplit['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACSplit['NM_FASILITAS']) && $ACSplit['NM_FASILITAS'] != null ? $ACSplit['NM_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACSplit['SATUAN_FASILITAS']) && $ACSplit['SATUAN_FASILITAS'] !=  null ? $ACSplit['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACSplit['STATUS_FASILITAS']) && $ACSplit['STATUS_FASILITAS'] != null ? $ACSplit['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACSplit['KETERGANTUNGAN']) && $ACSplit['KETERGANTUNGAN'] != null ? $ACSplit['KETERGANTUNGAN'] : '-') . "</td>
									<td align=\"right\"><input type=\"text\" class=\"form-control\" id=\"nilaiACSplit\" name=\"nilaiACSplit\" size=\"25\" maxlength=\"20\" value=\"" . $nilainonDep . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>B. AC Window</b></label><hr>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACWindow['KD_FASILITAS']) && $ACWindow['KD_FASILITAS'] != '' ? $ACWindow['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACWindow['NM_FASILITAS']) && $ACWindow['NM_FASILITAS'] != '' ? $ACWindow['NM_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACWindow['SATUAN_FASILITAS']) && $ACWindow['SATUAN_FASILITAS'] != '' ? $ACWindow['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACWindow['STATUS_FASILITAS']) && $ACWindow['STATUS_FASILITAS'] != '' ? $ACWindow['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACWindow['KETERGANTUNGAN']) && $ACWindow['KETERGANTUNGAN'] != '' ? $ACWindow['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACWindow\" id=\"nilaiACWindow\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacWindow . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>C. AC Central</b></label><hr>
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;a. Kantor</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralKantor[0]['KD_FASILITAS']) && $ACCentralKantor[0]['KD_FASILITAS'] != '' ? $ACCentralKantor[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralKantor[0]['NM_FASILITAS']) && $ACCentralKantor[0]['NM_FASILITAS'] != '' ? $ACCentralKantor[0]['NM_FASILITAS'] . ' KLS ' . $ACCentralKantor[0]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[0]['SATUAN_FASILITAS']) && $ACCentralKantor[0]['SATUAN_FASILITAS'] != '' ? $ACCentralKantor[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[0]['STATUS_FASILITAS']) && $ACCentralKantor[0]['STATUS_FASILITAS'] != '' ? $ACCentralKantor[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[0]['KETERGANTUNGAN']) && $ACCentralKantor[0]['KETERGANTUNGAN'] != '' ? $ACCentralKantor[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralKantor1\" id=\"nilaiACCentralKantor1\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacCentral[0] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralKantor[1]['KD_FASILITAS']) && $ACCentralKantor[1]['KD_FASILITAS'] != '' ? $ACCentralKantor[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralKantor[1]['NM_FASILITAS']) && $ACCentralKantor[1]['NM_FASILITAS'] != '' ? $ACCentralKantor[1]['NM_FASILITAS'] . ' KLS ' . $ACCentralKantor[1]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[1]['SATUAN_FASILITAS']) && $ACCentralKantor[1]['SATUAN_FASILITAS'] != '' ? $ACCentralKantor[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[1]['STATUS_FASILITAS']) && $ACCentralKantor[1]['STATUS_FASILITAS'] != '' ? $ACCentralKantor[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralKantor[1]['KETERGANTUNGAN']) && $ACCentralKantor[1]['KETERGANTUNGAN'] != '' ? $ACCentralKantor[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralKantor2\" id=\"nilaiACCentralKantor2\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacCentral[1] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;b. Hotel</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralHotel[0]['KD_FASILITAS']) && $ACCentralHotel[0]['KD_FASILITAS'] != '' ? $ACCentralHotel[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralHotel[0]['NM_FASILITAS']) && $ACCentralHotel[0]['NM_FASILITAS'] != '' ? $ACCentralHotel[0]['NM_FASILITAS'] . ' KLS ' . $ACCentralHotel[0]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[0]['SATUAN_FASILITAS']) && $ACCentralHotel[0]['SATUAN_FASILITAS'] != '' ? $ACCentralHotel[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[0]['STATUS_FASILITAS']) && $ACCentralHotel[0]['STATUS_FASILITAS'] != '' ? $ACCentralHotel[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[0]['KETERGANTUNGAN']) && $ACCentralHotel[0]['KETERGANTUNGAN'] != '' ? $ACCentralHotel[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralHotel1\" id=\"nilaiACCentralHotel1\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacHotel[0] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralHotel[1]['KD_FASILITAS']) && $ACCentralHotel[1]['KD_FASILITAS'] != '' ? $ACCentralHotel[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralHotel[1]['NM_FASILITAS']) && $ACCentralHotel[1]['NM_FASILITAS'] != '' ? $ACCentralHotel[1]['NM_FASILITAS'] . ' KLS ' . $ACCentralHotel[1]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[1]['SATUAN_FASILITAS']) && $ACCentralHotel[1]['SATUAN_FASILITAS'] != '' ? $ACCentralHotel[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[1]['STATUS_FASILITAS']) && $ACCentralHotel[1]['STATUS_FASILITAS'] != '' ? $ACCentralHotel[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[1]['KETERGANTUNGAN']) && $ACCentralHotel[1]['KETERGANTUNGAN'] != '' ? $ACCentralHotel[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralHotel2\" id=\"nilaiACCentralHotel2\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacHotel[1] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralHotel[2]['KD_FASILITAS']) && $ACCentralHotel[2]['KD_FASILITAS'] != '' ? $ACCentralHotel[2]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralHotel[2]['NM_FASILITAS']) && $ACCentralHotel[2]['NM_FASILITAS'] != '' ? $ACCentralHotel[2]['NM_FASILITAS'] . ' KLS ' . $ACCentralHotel[2]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[2]['SATUAN_FASILITAS']) && $ACCentralHotel[2]['SATUAN_FASILITAS'] != '' ? $ACCentralHotel[2]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[2]['STATUS_FASILITAS']) && $ACCentralHotel[2]['STATUS_FASILITAS'] != '' ? $ACCentralHotel[2]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[2]['KETERGANTUNGAN']) && $ACCentralHotel[2]['KETERGANTUNGAN'] != '' ? $ACCentralHotel[2]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralHotel3\" id=\"nilaiACCentralHotel3\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacHotel[2] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralHotel[3]['KD_FASILITAS']) && $ACCentralHotel[3]['KD_FASILITAS'] != '' ? $ACCentralHotel[3]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralHotel[3]['NM_FASILITAS']) && $ACCentralHotel[3]['NM_FASILITAS'] != '' ? $ACCentralHotel[3]['NM_FASILITAS'] . ' KLS ' . $ACCentralHotel[3]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[3]['SATUAN_FASILITAS']) && $ACCentralHotel[3]['SATUAN_FASILITAS'] != '' ? $ACCentralHotel[3]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[3]['STATUS_FASILITAS']) && $ACCentralHotel[3]['STATUS_FASILITAS'] != '' ? $ACCentralHotel[3]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralHotel[3]['KETERGANTUNGAN']) && $ACCentralHotel[3]['KETERGANTUNGAN'] != '' ? $ACCentralHotel[3]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralHotel4\" id=\"nilaiACCentralHotel4\" size=\"25\" maxlength=\"20\" value=\"" . $nilaiacHotel[3] . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;c. Toko</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralToko[0]['KD_FASILITAS']) && $ACCentralToko[0]['KD_FASILITAS'] != '' ? $ACCentralToko[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralToko[0]['NM_FASILITAS']) && $ACCentralToko[0]['NM_FASILITAS'] != '' ? $ACCentralToko[0]['NM_FASILITAS'] . ' KLS ' . $ACCentralToko[0]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[0]['SATUAN_FASILITAS']) && $ACCentralToko[0]['SATUAN_FASILITAS'] != '' ? $ACCentralToko[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[0]['STATUS_FASILITAS']) && $ACCentralToko[0]['STATUS_FASILITAS'] != '' ? $ACCentralToko[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[0]['KETERGANTUNGAN']) && $ACCentralToko[0]['KETERGANTUNGAN'] != '' ? $ACCentralToko[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralToko1\" id=\"nilaiACCentralToko1\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralToko[0]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralToko[1]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralToko[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralToko[1]['KD_FASILITAS']) && $ACCentralToko[1]['KD_FASILITAS'] != '' ? $ACCentralToko[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralToko[1]['NM_FASILITAS']) && $ACCentralToko[1]['NM_FASILITAS'] != '' ? $ACCentralToko[1]['NM_FASILITAS'] . ' KLS ' . $ACCentralToko[1]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[1]['SATUAN_FASILITAS']) && $ACCentralToko[1]['SATUAN_FASILITAS'] != '' ? $ACCentralToko[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[1]['STATUS_FASILITAS']) && $ACCentralToko[1]['STATUS_FASILITAS'] != '' ? $ACCentralToko[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[1]['KETERGANTUNGAN']) && $ACCentralToko[1]['KETERGANTUNGAN'] != '' ? $ACCentralToko[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralToko2\" id=\"nilaiACCentralToko2\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralToko[1]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralToko[1]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralToko[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralToko[2]['KD_FASILITAS']) && $ACCentralToko[2]['KD_FASILITAS'] != '' ? $ACCentralToko[2]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralToko[2]['NM_FASILITAS']) && $ACCentralToko[2]['NM_FASILITAS'] != '' ? $ACCentralToko[2]['NM_FASILITAS'] . ' KLS ' . $ACCentralToko[2]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[2]['SATUAN_FASILITAS']) && $ACCentralToko[2]['SATUAN_FASILITAS'] != '' ? $ACCentralToko[2]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[2]['STATUS_FASILITAS']) && $ACCentralToko[2]['STATUS_FASILITAS'] != '' ? $ACCentralToko[2]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralToko[2]['KETERGANTUNGAN']) && $ACCentralToko[2]['KETERGANTUNGAN'] != '' ? $ACCentralToko[2]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralToko3\" id=\"nilaiACCentralToko3\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralToko[2]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralToko[2]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralToko[2]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;d. Rumah Sakit</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralRS[0]['KD_FASILITAS']) && $ACCentralRS[0]['KD_FASILITAS'] != '' ? $ACCentralRS[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralRS[0]['NM_FASILITAS']) && $ACCentralRS[0]['NM_FASILITAS'] != '' ? $ACCentralRS[0]['NM_FASILITAS'] . ' KLS ' . $ACCentralRS[0]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[0]['SATUAN_FASILITAS']) && $ACCentralRS[0]['SATUAN_FASILITAS'] != '' ? $ACCentralRS[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[0]['STATUS_FASILITAS']) && $ACCentralRS[0]['STATUS_FASILITAS'] != '' ? $ACCentralRS[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[0]['KETERGANTUNGAN']) && $ACCentralRS[0]['KETERGANTUNGAN'] != '' ? $ACCentralRS[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralRS1\" id=\"nilaiACCentralRS1\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralRS[0]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralRS[0]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralRS[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralRS[1]['KD_FASILITAS']) && $ACCentralRS[1]['KD_FASILITAS'] != '' ? $ACCentralRS[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralRS[1]['NM_FASILITAS']) && $ACCentralRS[1]['NM_FASILITAS'] != '' ? $ACCentralRS[1]['NM_FASILITAS'] . ' KLS ' . $ACCentralRS[1]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[1]['SATUAN_FASILITAS']) && $ACCentralRS[1]['SATUAN_FASILITAS'] != '' ? $ACCentralRS[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[1]['STATUS_FASILITAS']) && $ACCentralRS[1]['STATUS_FASILITAS'] != '' ? $ACCentralRS[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[1]['KETERGANTUNGAN']) && $ACCentralRS[1]['KETERGANTUNGAN'] != '' ? $ACCentralRS[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralRS2\" id=\"nilaiACCentralRS2\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralRS[1]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralRS[1]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralRS[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralRS[2]['KD_FASILITAS']) && $ACCentralRS[2]['KD_FASILITAS'] != '' ? $ACCentralRS[2]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralRS[2]['NM_FASILITAS']) && $ACCentralRS[2]['NM_FASILITAS'] != '' ? $ACCentralRS[2]['NM_FASILITAS'] . ' KLS ' . $ACCentralRS[2]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[2]['SATUAN_FASILITAS']) && $ACCentralRS[2]['SATUAN_FASILITAS'] != '' ? $ACCentralRS[2]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[2]['STATUS_FASILITAS']) && $ACCentralRS[2]['STATUS_FASILITAS'] != '' ? $ACCentralRS[2]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralRS[2]['KETERGANTUNGAN']) && $ACCentralRS[2]['KETERGANTUNGAN'] != '' ? $ACCentralRS[2]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralRS3\" id=\"nilaiACCentralRS3\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralRS[2]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralRS[2]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($ACCentralRS[2]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;e. Apartemen</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralApt[0]['KD_FASILITAS']) && $ACCentralApt[0]['KD_FASILITAS'] != '' ? $ACCentralApt[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralApt[0]['NM_FASILITAS']) && $ACCentralApt[0]['NM_FASILITAS'] != '' ? $ACCentralApt[0]['NM_FASILITAS'] . ' KLS ' . $ACCentralApt[0]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[0]['SATUAN_FASILITAS']) && $ACCentralApt[0]['SATUAN_FASILITAS'] != '' ? $ACCentralApt[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[0]['STATUS_FASILITAS']) && $ACCentralApt[0]['STATUS_FASILITAS'] != '' ? $ACCentralApt[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[0]['KETERGANTUNGAN']) && $ACCentralApt[0]['KETERGANTUNGAN'] != '' ? $ACCentralApt[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralApt1\" id=\"nilaiACCentralApt1\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralApt[0]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralApt[0]['NILAI_FASILITAS_KLS_BINTANG'] ? number_format($ACCentralApt[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '')  . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralApt[1]['KD_FASILITAS']) && $ACCentralApt[1]['KD_FASILITAS'] != '' ? $ACCentralApt[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralApt[1]['NM_FASILITAS']) && $ACCentralApt[1]['NM_FASILITAS'] != '' ? $ACCentralApt[1]['NM_FASILITAS'] . ' KLS ' . $ACCentralApt[1]['KLS_BINTANG'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[1]['SATUAN_FASILITAS']) && $ACCentralApt[1]['SATUAN_FASILITAS'] != '' ? $ACCentralApt[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[1]['STATUS_FASILITAS']) && $ACCentralApt[1]['STATUS_FASILITAS'] != '' ? $ACCentralApt[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralApt[1]['KETERGANTUNGAN']) && $ACCentralApt[1]['KETERGANTUNGAN'] != '' ? $ACCentralApt[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralApt2\" id=\"nilaiACCentralApt2\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralApt[1]['NILAI_FASILITAS_KLS_BINTANG']) && $ACCentralApt[1]['NILAI_FASILITAS_KLS_BINTANG'] != "" ? number_format($ACCentralApt[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
					</div>
					<div class=\"col-md-12\">
						<label><b>&nbsp;&nbsp;&nbsp;&nbsp;f. Bangunan Lain</b></label>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($ACCentralBngLain['KD_FASILITAS']) && $ACCentralBngLain['KD_FASILITAS'] != '' ? $ACCentralBngLain['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($ACCentralBngLain['NM_FASILITAS']) && $ACCentralBngLain['NM_FASILITAS'] != '' ? $ACCentralBngLain['NM_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralBngLain['SATUAN_FASILITAS']) && $ACCentralBngLain['SATUAN_FASILITAS'] != '' ? $ACCentralBngLain['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralBngLain['STATUS_FASILITAS']) && $ACCentralBngLain['STATUS_FASILITAS'] != '' ? $ACCentralBngLain['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($ACCentralBngLain['KETERGANTUNGAN']) && $ACCentralBngLain['KETERGANTUNGAN'] != '' ? $ACCentralBngLain['KETERGANTUNGAN'] : '-') . "</td>
									<td><input type=\"text\" class=\"form-control\" name=\"nilaiACCentralBngLain\" id=\"nilaiACCentralBngLain\" size=\"25\" maxlength=\"20\" value=\"" . (isset($ACCentralBngLain['NILAI_NON_DEP']) && $ACCentralBngLain['NILAI_NON_DEP'] != '' ? number_format($ACCentralBngLain['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
						<hr />
						<div style=\"float: right\">
							<span id=\"loading-1\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-1\"\">Simpan</button>
						</div>
					</div>
				</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLKlmRenang()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$KlmRenang			= $this->getDataKlmRenang();

		// echo "<pre>";
		// print_r($KlmRenang);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
				<div class=\"row\">
					<div class=\"col-md-12\">
						<label><b>KOLAM RENANG</b></label><hr>
						<div class=\"table-responsive\">
							<table class=\"table table-bordered\">
								<tr>
									<td>Kode</td>
									<td>Fasilitas</td>
									<td>Satuan</td>
									<td>Status</td>
									<td>Ketergantungan</td>
									<td>Nilai</td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[0]['KD_FASILITAS']) && $KlmRenang[0]['KD_FASILITAS'] != '' ? $KlmRenang[0]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[0]['NM_FASILITAS']) && $KlmRenang[0]['NM_FASILITAS'] != '' ? $KlmRenang[0]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[0]['KLS_DEP_MIN'] . '-' . $KlmRenang[0]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[0]['SATUAN_FASILITAS']) && $KlmRenang[0]['SATUAN_FASILITAS'] != '' ? $KlmRenang[0]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[0]['STATUS_FASILITAS']) && $KlmRenang[0]['STATUS_FASILITAS'] != '' ? $KlmRenang[0]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[0]['KETERGANTUNGAN']) && $KlmRenang[0]['KETERGANTUNGAN'] != '' ? $KlmRenang[0]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[0]['NILAI_DEP_MIN_MAX']) && $KlmRenang[0]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[0]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang1\" id=\"nilaiKlmRenang1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[1]['KD_FASILITAS']) && $KlmRenang[1]['KD_FASILITAS'] != '' ? $KlmRenang[1]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[1]['NM_FASILITAS']) && $KlmRenang[1]['NM_FASILITAS'] != '' ? $KlmRenang[1]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[1]['KLS_DEP_MIN'] . '-' . $KlmRenang[1]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[1]['SATUAN_FASILITAS']) && $KlmRenang[1]['SATUAN_FASILITAS'] != '' ? $KlmRenang[1]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[1]['STATUS_FASILITAS']) && $KlmRenang[1]['STATUS_FASILITAS'] != '' ? $KlmRenang[1]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[1]['KETERGANTUNGAN']) && $KlmRenang[1]['KETERGANTUNGAN'] != '' ? $KlmRenang[1]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[1]['NILAI_DEP_MIN_MAX']) && $KlmRenang[1]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[1]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang2\" id=\"nilaiKlmRenang2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[2]['KD_FASILITAS']) && $KlmRenang[2]['KD_FASILITAS'] != '' ? $KlmRenang[2]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[2]['NM_FASILITAS']) && $KlmRenang[2]['NM_FASILITAS'] != '' ? $KlmRenang[2]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[2]['KLS_DEP_MIN'] . '-' . $KlmRenang[2]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[2]['SATUAN_FASILITAS']) && $KlmRenang[2]['SATUAN_FASILITAS'] != '' ? $KlmRenang[2]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[2]['STATUS_FASILITAS']) && $KlmRenang[2]['STATUS_FASILITAS'] != '' ? $KlmRenang[2]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[2]['KETERGANTUNGAN']) && $KlmRenang[2]['KETERGANTUNGAN'] != '' ? $KlmRenang[2]['KETERGANTUNGAN'] : '-') . "</td>
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[2]['NILAI_DEP_MIN_MAX']) && $KlmRenang[2]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[2]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang3\" id=\"nilaiKlmRenang3\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[3]['KD_FASILITAS']) && $KlmRenang[3]['KD_FASILITAS'] != '' ? $KlmRenang[3]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[3]['NM_FASILITAS']) && $KlmRenang[3]['NM_FASILITAS'] != '' ? $KlmRenang[3]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[3]['KLS_DEP_MIN'] . '-' . $KlmRenang[3]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[3]['SATUAN_FASILITAS']) && $KlmRenang[3]['SATUAN_FASILITAS'] != '' ? $KlmRenang[3]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[3]['STATUS_FASILITAS']) && $KlmRenang[3]['STATUS_FASILITAS'] != '' ? $KlmRenang[3]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[3]['KETERGANTUNGAN']) && $KlmRenang[3]['KETERGANTUNGAN'] != '' ? $KlmRenang[3]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[3]['NILAI_DEP_MIN_MAX']) && $KlmRenang[3]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[3]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang4\" id=\"nilaiKlmRenang4\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[4]['KD_FASILITAS']) && $KlmRenang[4]['KD_FASILITAS'] != '' ? $KlmRenang[4]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[4]['NM_FASILITAS']) && $KlmRenang[4]['NM_FASILITAS'] != '' ? $KlmRenang[4]['NM_FASILITAS'] . ' LUAS > ' . ($KlmRenang[4]['KLS_DEP_MIN'] - 1) : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[4]['SATUAN_FASILITAS']) && $KlmRenang[4]['SATUAN_FASILITAS'] != '' ? $KlmRenang[4]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[4]['STATUS_FASILITAS']) && $KlmRenang[4]['STATUS_FASILITAS'] != '' ? $KlmRenang[4]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[4]['KETERGANTUNGAN']) && $KlmRenang[4]['KETERGANTUNGAN'] != '' ? $KlmRenang[4]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[4]['NILAI_DEP_MIN_MAX']) && $KlmRenang[4]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[4]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang5\" id=\"nilaiKlmRenang5\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[5]['KD_FASILITAS']) && $KlmRenang[5]['KD_FASILITAS'] != '' ? $KlmRenang[5]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[5]['NM_FASILITAS']) && $KlmRenang[5]['NM_FASILITAS'] != '' ? $KlmRenang[5]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[5]['KLS_DEP_MIN'] . '-' . $KlmRenang[5]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[5]['SATUAN_FASILITAS']) && $KlmRenang[5]['SATUAN_FASILITAS'] != '' ? $KlmRenang[5]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[5]['STATUS_FASILITAS']) && $KlmRenang[5]['STATUS_FASILITAS'] != '' ? $KlmRenang[5]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[5]['KETERGANTUNGAN']) && $KlmRenang[5]['KETERGANTUNGAN'] != '' ? $KlmRenang[5]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[5]['NILAI_DEP_MIN_MAX']) && $KlmRenang[5]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[5]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang6\" id=\"nilaiKlmRenang6\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[6]['KD_FASILITAS']) && $KlmRenang[6]['KD_FASILITAS'] != '' ? $KlmRenang[6]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[6]['NM_FASILITAS']) && $KlmRenang[6]['NM_FASILITAS'] != '' ? $KlmRenang[6]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[6]['KLS_DEP_MIN'] . '-' . $KlmRenang[6]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[6]['SATUAN_FASILITAS']) && $KlmRenang[6]['SATUAN_FASILITAS'] != '' ? $KlmRenang[6]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[6]['STATUS_FASILITAS']) && $KlmRenang[6]['STATUS_FASILITAS'] != '' ? $KlmRenang[6]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[6]['KETERGANTUNGAN']) && $KlmRenang[6]['KETERGANTUNGAN'] != '' ? $KlmRenang[6]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[6]['NILAI_DEP_MIN_MAX']) && $KlmRenang[6]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[6]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang7\" id=\"nilaiKlmRenang7\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[7]['KD_FASILITAS']) && $KlmRenang[7]['KD_FASILITAS'] != '' ? $KlmRenang[7]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[7]['NM_FASILITAS']) && $KlmRenang[7]['NM_FASILITAS'] != '' ? $KlmRenang[7]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[7]['KLS_DEP_MIN'] . '-' . $KlmRenang[7]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[7]['SATUAN_FASILITAS']) && $KlmRenang[7]['SATUAN_FASILITAS'] != '' ? $KlmRenang[7]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[7]['STATUS_FASILITAS']) && $KlmRenang[7]['STATUS_FASILITAS'] != '' ? $KlmRenang[7]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[7]['KETERGANTUNGAN']) && $KlmRenang[7]['KETERGANTUNGAN'] != '' ? $KlmRenang[7]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($KlmRenang[7]['NILAI_DEP_MIN_MAX']) && $KlmRenang[7]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[7]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang8\" id=\"nilaiKlmRenang8\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[8]['KD_FASILITAS']) && $KlmRenang[8]['KD_FASILITAS'] != '' ? $KlmRenang[8]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[8]['NM_FASILITAS']) && $KlmRenang[8]['NM_FASILITAS'] != '' ? $KlmRenang[8]['NM_FASILITAS'] . ' LUAS ' . $KlmRenang[8]['KLS_DEP_MIN'] . '-' . $KlmRenang[8]['KLS_DEP_MAX'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[8]['SATUAN_FASILITAS']) && $KlmRenang[8]['SATUAN_FASILITAS'] != '' ? $KlmRenang[8]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[8]['STATUS_FASILITAS']) && $KlmRenang[8]['STATUS_FASILITAS'] != '' ? $KlmRenang[8]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[8]['KETERGANTUNGAN']) && $KlmRenang[8]['KETERGANTUNGAN'] != '' ? $KlmRenang[8]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[8]['NILAI_DEP_MIN_MAX']) && $KlmRenang[8]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[8]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang9\" id=\"nilaiKlmRenang9\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
								<tr>
									<td align=\"center\">" . (isset($KlmRenang[9]['KD_FASILITAS']) && $KlmRenang[9]['KD_FASILITAS'] != '' ? $KlmRenang[9]['KD_FASILITAS'] : '-') . "</td>
									<td align=\"left\">" . (isset($KlmRenang[9]['NM_FASILITAS']) && $KlmRenang[9]['NM_FASILITAS'] != '' ? $KlmRenang[9]['NM_FASILITAS'] . ' LUAS > ' . ($KlmRenang[9]['KLS_DEP_MIN'] - 1) : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[9]['SATUAN_FASILITAS']) && $KlmRenang[9]['SATUAN_FASILITAS'] != '' ? $KlmRenang[9]['SATUAN_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[9]['STATUS_FASILITAS']) && $KlmRenang[9]['STATUS_FASILITAS'] != '' ? $KlmRenang[9]['STATUS_FASILITAS'] : '-') . "</td>
									<td align=\"center\">" . (isset($KlmRenang[9]['KETERGANTUNGAN']) && $KlmRenang[9]['KETERGANTUNGAN'] != '' ? $KlmRenang[9]['KETERGANTUNGAN'] : '-') . "</td>	
									<td><input class=\"form-control\" type=\"text\" value=\"" . (isset($KlmRenang[9]['NILAI_DEP_MIN_MAX']) && $KlmRenang[9]['NILAI_DEP_MIN_MAX'] != '' ? number_format($KlmRenang[9]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiKlmRenang10\" id=\"nilaiKlmRenang10\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
								</tr>
							</table>
						</div>
						<hr>
						<div style=\"float: right\">
							<span id=\"loading-2\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-2\"\">Simpan</button>
						</div>
					</div>
				</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLPerkerasan()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$perkerasan			= $this->getDataPerkerasan();

		// echo "<pre>";
		// print_r($perkerasan);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>PERKERASAN</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($perkerasan[0]['KD_FASILITAS']) && $perkerasan[0]['KD_FASILITAS'] != '' ? $perkerasan[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($perkerasan[0]['NM_FASILITAS']) && $perkerasan[0]['NM_FASILITAS'] != '' ? $perkerasan[0]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[0]['SATUAN_FASILITAS']) && $perkerasan[0]['SATUAN_FASILITAS'] != '' ? $perkerasan[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[0]['STATUS_FASILITAS']) && $perkerasan[0]['STATUS_FASILITAS'] != '' ? $perkerasan[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[0]['KETERGANTUNGAN']) && $perkerasan[0]['KETERGANTUNGAN'] != '' ? $perkerasan[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($perkerasan[0]['NILAI_NON_DEP']) && $perkerasan[0]['NILAI_NON_DEP'] != '' ? number_format($perkerasan[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPerkerasan1\" id=\"nilaiPerkerasan1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($perkerasan[1]['KD_FASILITAS']) && $perkerasan[1]['KD_FASILITAS'] != '' ? $perkerasan[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($perkerasan[1]['NM_FASILITAS']) && $perkerasan[1]['NM_FASILITAS'] != '' ? $perkerasan[1]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[1]['SATUAN_FASILITAS']) && $perkerasan[1]['SATUAN_FASILITAS'] != '' ? $perkerasan[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[1]['STATUS_FASILITAS']) && $perkerasan[1]['STATUS_FASILITAS'] != '' ? $perkerasan[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[1]['KETERGANTUNGAN']) && $perkerasan[1]['KETERGANTUNGAN'] != '' ? $perkerasan[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($perkerasan[1]['NILAI_NON_DEP']) && $perkerasan[1]['NILAI_NON_DEP'] != '' ? number_format($perkerasan[1]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPerkerasan2\" id=\"nilaiPerkerasan2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($perkerasan[2]['KD_FASILITAS']) && $perkerasan[2]['KD_FASILITAS'] != '' ? $perkerasan[2]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($perkerasan[2]['NM_FASILITAS']) && $perkerasan[2]['NM_FASILITAS'] != '' ? $perkerasan[2]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[2]['SATUAN_FASILITAS']) && $perkerasan[2]['SATUAN_FASILITAS'] != '' ? $perkerasan[2]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[2]['STATUS_FASILITAS']) && $perkerasan[2]['STATUS_FASILITAS'] != '' ? $perkerasan[2]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[2]['KETERGANTUNGAN']) && $perkerasan[2]['KETERGANTUNGAN'] != '' ? $perkerasan[2]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($perkerasan[2]['NILAI_NON_DEP']) && $perkerasan[2]['NILAI_NON_DEP'] != '' ? number_format($perkerasan[2]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPerkerasan3\" id=\"nilaiPerkerasan3\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($perkerasan[3]['KD_FASILITAS']) && $perkerasan[3]['KD_FASILITAS'] != '' ? $perkerasan[3]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($perkerasan[3]['NM_FASILITAS']) && $perkerasan[3]['NM_FASILITAS'] != '' ? $perkerasan[3]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[3]['SATUAN_FASILITAS']) && $perkerasan[3]['SATUAN_FASILITAS'] != '' ? $perkerasan[3]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[3]['STATUS_FASILITAS']) && $perkerasan[3]['STATUS_FASILITAS'] != '' ? $perkerasan[3]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($perkerasan[3]['KETERGANTUNGAN']) && $perkerasan[3]['KETERGANTUNGAN'] != '' ? $perkerasan[3]['KETERGANTUNGAN'] : '-') . "</td>	
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($perkerasan[3]['NILAI_NON_DEP']) && $perkerasan[3]['NILAI_NON_DEP'] != '' ? number_format($perkerasan[3]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPerkerasan4\" id=\"nilaiPerkerasan4\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-3\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-3\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLlapTenis()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$lapTenis			= $this->getDataLapTenis();

		// echo "<pre>";
		// print_r($lapTenis);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>LAPANGAN TENIS</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>";

		for ($x = 0; $x < 12; $x++) {
			$HTML .= "
							<tr>
								<td align=\"center\">" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['KD_FASILITAS'] != '' ? $lapTenis[$x]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['NM_FASILITAS'] != '' ? $lapTenis[$x]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['SATUAN_FASILITAS'] != '' ? $lapTenis[$x]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['STATUS_FASILITAS'] != '' ? $lapTenis[$x]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['KETERGANTUNGAN'] != '' ? $lapTenis[$x]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($lapTenis[$x]['KD_FASILITAS']) && $lapTenis[$x]['NILAI_NON_DEP'] != '' ? number_format($lapTenis[$x]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilailapTenis1\" id=\"nilailapTenis1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>";
		}
		$HTML .= "
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-4\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-4\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLlift()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$lift				= $this->getDataLift();

		// echo "<pre>";
		// print_r($lift);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>LIFT</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>";
		for ($x = 0; $x < 12; $x++) {
			$HTML .= "
							<tr>
								<td align=\"center\">" . (isset($lift[$x]['KD_FASILITAS']) && $lift[$x]['KD_FASILITAS'] != '' ? $lift[$x]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($lift[$x]['NM_FASILITAS']) && $lift[$x]['NM_FASILITAS'] != '' ? $lift[$x]['NM_FASILITAS'] . ' LUAS ' . $lift[$x]['KLS_DEP_MIN'] . '-' . $lift[$x]['KLS_DEP_MAX'] : '-') . "</td>
								<td align=\"center\">" . (isset($lift[$x]['SATUAN_FASILITAS']) && $lift[$x]['SATUAN_FASILITAS'] != '' ? $lift[$x]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($lift[$x]['STATUS_FASILITAS']) && $lift[$x]['STATUS_FASILITAS'] != '' ? $lift[$x]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($lift[$x]['KETERGANTUNGAN']) && $lift[$x]['KETERGANTUNGAN'] != '' ? $lift[$x]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($lift[$x]['NILAI_DEP_MIN_MAX']) && $lift[$x]['NILAI_DEP_MIN_MAX'] != '' ? number_format($lift[$x]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilailift1\" id=\"nilailift1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>";
		}

		$HTML .= "
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-5\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-5\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLEscalator()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$escalator			= $this->getDataEscalator();

		// echo "<pre>";
		// print_r($escalator);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>TANGGA BERJALAN</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($escalator[0]['KD_FASILITAS']) && $escalator[0]['KD_FASILITAS'] != '' ? $escalator[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($escalator[0]['NM_FASILITAS']) && $escalator[0]['NM_FASILITAS'] != '' ? $escalator[0]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[0]['SATUAN_FASILITAS']) && $escalator[0]['SATUAN_FASILITAS'] != '' ? $escalator[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[0]['STATUS_FASILITAS']) && $escalator[0]['STATUS_FASILITAS'] != '' ? $escalator[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[0]['KETERGANTUNGAN']) && $escalator[0]['KETERGANTUNGAN'] != '' ? $escalator[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($escalator[0]['NILAI_NON_DEP']) && $escalator[0]['NILAI_NON_DEP'] != '' ? number_format($escalator[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiEsc1\" id=\"nilaiEsc1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($escalator[1]['KD_FASILITAS']) && $escalator[1]['KD_FASILITAS'] != '' ? $escalator[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($escalator[1]['NM_FASILITAS']) && $escalator[1]['NM_FASILITAS'] != '' ? $escalator[1]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[1]['SATUAN_FASILITAS']) && $escalator[1]['SATUAN_FASILITAS'] != '' ? $escalator[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[1]['STATUS_FASILITAS']) && $escalator[1]['STATUS_FASILITAS'] != '' ? $escalator[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($escalator[1]['KETERGANTUNGAN']) && $escalator[1]['KETERGANTUNGAN'] != '' ? $escalator[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($escalator[1]['NILAI_NON_DEP']) && $escalator[1]['NILAI_NON_DEP'] != '' ? number_format($escalator[1]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiEsc2\" id=\"nilaiEsc2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-6\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-6\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLPagar()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataPagar();

		// echo "<pre>";
		// print_r($escalator);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>PAGAR</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['KETERGANTUNGAN']) && $dt[0]['KETERGANTUNGAN'] != '' ? $dt[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_NON_DEP']) && $dt[0]['NILAI_NON_DEP'] != '' ? number_format($dt[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPagar1\" id=\"nilaiPagar1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[1]['KD_FASILITAS']) && $dt[1]['KD_FASILITAS'] != '' ? $dt[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[1]['NM_FASILITAS']) && $dt[1]['NM_FASILITAS'] != '' ? $dt[1]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['SATUAN_FASILITAS']) && $dt[1]['SATUAN_FASILITAS'] != '' ? $dt[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['STATUS_FASILITAS']) && $dt[1]['STATUS_FASILITAS'] != '' ? $dt[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['KETERGANTUNGAN']) && $dt[1]['KETERGANTUNGAN'] != '' ? $dt[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[1]['NILAI_NON_DEP']) && $dt[1]['NILAI_NON_DEP'] != '' ? number_format($dt[1]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPagar2\" id=\"nilaiPagar2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-7\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-7\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLProtApi()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataProtApi();

		// echo "<pre>";
		// print_r($dt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>PROTEKSI API</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['KETERGANTUNGAN']) && $dt[0]['KETERGANTUNGAN'] != '' ? $dt[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_NON_DEP']) && $dt[0]['NILAI_NON_DEP'] != '' ? number_format($dt[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiProtApi1\" id=\"nilaiProtApi1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[1]['KD_FASILITAS']) && $dt[1]['KD_FASILITAS'] != '' ? $dt[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[1]['NM_FASILITAS']) && $dt[1]['NM_FASILITAS'] != '' ? $dt[1]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['SATUAN_FASILITAS']) && $dt[1]['SATUAN_FASILITAS'] != '' ? $dt[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['STATUS_FASILITAS']) && $dt[1]['STATUS_FASILITAS'] != '' ? $dt[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['KETERGANTUNGAN']) && $dt[1]['KETERGANTUNGAN'] != '' ? $dt[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[1]['NILAI_NON_DEP']) && $dt[1]['NILAI_NON_DEP'] != '' ? number_format($dt[1]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiProtApi2\" id=\"nilaiProtApi2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[2]['KD_FASILITAS']) && $dt[2]['KD_FASILITAS'] != '' ? $dt[2]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[2]['NM_FASILITAS']) && $dt[2]['NM_FASILITAS'] != '' ? $dt[2]['NM_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['SATUAN_FASILITAS']) && $dt[2]['SATUAN_FASILITAS'] != '' ? $dt[2]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['STATUS_FASILITAS']) && $dt[2]['STATUS_FASILITAS'] != '' ? $dt[2]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['KETERGANTUNGAN']) && $dt[2]['KETERGANTUNGAN'] != '' ? $dt[2]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[2]['NILAI_NON_DEP']) && $dt[2]['NILAI_NON_DEP'] != '' ? number_format($dt[2]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiProtApi3\" id=\"nilaiProtApi3\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-8\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-8\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}

	function getHTMLGenset()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataGenset();

		// echo "<pre>";
		// print_r($dt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>GENSET</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] . ' LUAS ' . $dt[0]['KLS_DEP_MIN'] . '-' . $dt[0]['KLS_DEP_MAX'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[0]['KETERGANTUNGAN']) && $dt[0]['KETERGANTUNGAN'] != '' ? $dt[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_DEP_MIN_MAX']) && $dt[0]['NILAI_DEP_MIN_MAX'] != '' ? number_format($dt[0]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiGenset1\" id=\"nilaiGenset1\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[1]['KD_FASILITAS']) && $dt[1]['KD_FASILITAS'] != '' ? $dt[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[1]['NM_FASILITAS']) && $dt[1]['NM_FASILITAS'] != '' ? $dt[1]['NM_FASILITAS'] . ' LUAS ' . $dt[1]['KLS_DEP_MIN'] . '-' . $dt[1]['KLS_DEP_MAX'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['SATUAN_FASILITAS']) && $dt[1]['SATUAN_FASILITAS'] != '' ? $dt[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['STATUS_FASILITAS']) && $dt[1]['STATUS_FASILITAS'] != '' ? $dt[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[1]['KETERGANTUNGAN']) && $dt[1]['KETERGANTUNGAN'] != '' ? $dt[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[1]['NILAI_DEP_MIN_MAX']) && $dt[1]['NILAI_DEP_MIN_MAX'] != '' ? number_format($dt[1]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiGenset2\" id=\"nilaiGenset2\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[2]['KD_FASILITAS']) && $dt[2]['KD_FASILITAS'] != '' ? $dt[2]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[2]['NM_FASILITAS']) && $dt[2]['NM_FASILITAS'] != '' ? $dt[2]['NM_FASILITAS'] . ' LUAS ' . $dt[2]['KLS_DEP_MIN'] . '-' . $dt[2]['KLS_DEP_MAX'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['SATUAN_FASILITAS']) && $dt[2]['SATUAN_FASILITAS'] != '' ? $dt[2]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['STATUS_FASILITAS']) && $dt[2]['STATUS_FASILITAS'] != '' ? $dt[2]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[2]['KETERGANTUNGAN']) && $dt[2]['KETERGANTUNGAN'] != '' ? $dt[2]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[2]['NILAI_DEP_MIN_MAX']) && $dt[2]['NILAI_DEP_MIN_MAX'] != '' ? number_format($dt[2]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiGenset3\" id=\"nilaiGenset3\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($dt[3]['KD_FASILITAS']) && $dt[3]['KD_FASILITAS'] != '' ? $dt[3]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($dt[3]['NM_FASILITAS']) && $dt[3]['NM_FASILITAS'] != '' ? $dt[3]['NM_FASILITAS'] . ' LUAS ' . $dt[3]['KLS_DEP_MIN'] . '-' . $dt[3]['KLS_DEP_MAX'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[3]['SATUAN_FASILITAS']) && $dt[3]['SATUAN_FASILITAS'] != '' ? $dt[3]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[3]['STATUS_FASILITAS']) && $dt[3]['STATUS_FASILITAS'] != '' ? $dt[3]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($dt[3]['KETERGANTUNGAN']) && $dt[3]['KETERGANTUNGAN'] != '' ? $dt[3]['KETERGANTUNGAN'] : '-') . "</td>	
								<td><input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[3]['NILAI_DEP_MIN_MAX']) && $dt[3]['NILAI_DEP_MIN_MAX'] != '' ? number_format($dt[3]['NILAI_DEP_MIN_MAX'], '0', ',', '') : '0') . "\" name=\"nilaiGenset4\" id=\"nilaiGenset4\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-9\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-9\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}
	function getHTMLPABX()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataPABX();

		// echo "<pre>";
		// print_r($dt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>Saluran Pesawat PABX</b></label><hr>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Kode: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Fasilitas: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Satuan: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Status: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Nilai: </label></div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_NON_DEP']) && $dt[0]['NILAI_NON_DEP'] != '' ? number_format($dt[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPABX\" id=\"nilaiPABX\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\">
						</div>
					</div>
					<hr>
					<div style=\"float: right;\">
						<span id=\"loading-10\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-10\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}
	function getHTMLAirArt()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataAirArt();

		// echo "<pre>";
		// print_r($dt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>Air Art</b></label><hr>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Kode: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Fasilitas: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Satuan: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Status: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Nilai: </label></div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_NON_DEP']) && $dt[0]['NILAI_NON_DEP'] != '' ? number_format($dt[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPABX\" id=\"nilaiPABX\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\">
						</div>
					</div>
					<hr>
					<div style=\"float: right;\">
						<span id=\"loading-11\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-11\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}
	function getHTMLBoiler()
	{
		global $a, $m, $tab, $appConfig;

		$params 			= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$boihotel			= $this->getDataBoilerHotel();
		$boiapart			= $this->getDataBoilerApart();


		// echo "<pre>";
		// print_r($ACCentralApt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>Boiler</b></label><br />
					<label><b>&nbsp;&nbsp;&nbsp;&nbsp;a. Hotel</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($boihotel[0]['KD_FASILITAS']) && $boihotel[0]['KD_FASILITAS'] != '' ? $boihotel[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($boihotel[0]['NM_FASILITAS']) && $boihotel[0]['NM_FASILITAS'] != '' ? $boihotel[0]['NM_FASILITAS'] . ' BINTANG 4-5' : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[0]['SATUAN_FASILITAS']) && $boihotel[0]['SATUAN_FASILITAS'] != '' ? $boihotel[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[0]['STATUS_FASILITAS']) && $boihotel[0]['STATUS_FASILITAS'] != '' ? $boihotel[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[0]['KETERGANTUNGAN']) && $boihotel[0]['KETERGANTUNGAN'] != '' ? $boihotel[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" name=\"nilaiboihotel1\" id=\"nilaiboihotel1\" size=\"25\" maxlength=\"20\" value=\"" . (isset($boihotel[0]['NILAI_FASILITAS_KLS_BINTANG']) && $boihotel[0]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($boihotel[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($boihotel[1]['KD_FASILITAS']) && $boihotel[1]['KD_FASILITAS'] != '' ? $boihotel[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($boihotel[1]['NM_FASILITAS']) && $boihotel[1]['NM_FASILITAS'] != '' ? $boihotel[1]['NM_FASILITAS'] . ' BINTANG <4' : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[1]['SATUAN_FASILITAS']) && $boihotel[1]['SATUAN_FASILITAS'] != '' ? $boihotel[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[1]['STATUS_FASILITAS']) && $boihotel[1]['STATUS_FASILITAS'] != '' ? $boihotel[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[1]['KETERGANTUNGAN']) && $boihotel[1]['KETERGANTUNGAN'] != '' ? $boihotel[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" name=\"nilaiboihotel2\" id=\"nilaiboihotel2\" size=\"25\" maxlength=\"20\" value=\"" . (isset($boihotel[1]['NILAI_FASILITAS_KLS_BINTANG']) && $boihotel[1]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($boihotel[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($boihotel[2]['KD_FASILITAS']) && $boihotel[2]['KD_FASILITAS'] != '' ? $boihotel[2]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($boihotel[2]['NM_FASILITAS']) && $boihotel[2]['NM_FASILITAS'] != '' ? $boihotel[0]['NM_FASILITAS'] . ' Non Bintang' : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[2]['SATUAN_FASILITAS']) && $boihotel[2]['SATUAN_FASILITAS'] != '' ? $boihotel[2]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[2]['STATUS_FASILITAS']) && $boihotel[2]['STATUS_FASILITAS'] != '' ? $boihotel[2]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boihotel[2]['KETERGANTUNGAN']) && $boihotel[2]['KETERGANTUNGAN'] != '' ? $boihotel[2]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" name=\"nilaiboihotel3\" id=\"nilaiboihotel3\" size=\"25\" maxlength=\"20\" value=\"" . (isset($boihotel[2]['NILAI_FASILITAS_KLS_BINTANG']) && $boihotel[2]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($boihotel[2]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
						</table>
					</div>
					<label><b>&nbsp;&nbsp;&nbsp;&nbsp;b. Apartemen</b></label><hr>
					<div class=\"table-responsive\">
						<table class=\"table table-bordered\">
							<tr>
								<td>Kode</td>
								<td>Fasilitas</td>
								<td>Satuan</td>
								<td>Status</td>
								<td>Ketergantungan</td>
								<td>Nilai</td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($boiapart[0]['KD_FASILITAS']) && $boiapart[0]['KD_FASILITAS'] != '' ? $boiapart[0]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($boiapart[0]['NM_FASILITAS']) && $boiapart[0]['NM_FASILITAS'] != '' ? $boiapart[0]['NM_FASILITAS'] . ' KLS 1-2' : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[0]['SATUAN_FASILITAS']) && $boiapart[0]['SATUAN_FASILITAS'] != '' ? $boiapart[0]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[0]['STATUS_FASILITAS']) && $boiapart[0]['STATUS_FASILITAS'] != '' ? $boiapart[0]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[0]['KETERGANTUNGAN']) && $boiapart[0]['KETERGANTUNGAN'] != '' ? $boiapart[0]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" name=\"nilaiboiapart1\" id=\"nilaiboiapart1\" size=\"25\" maxlength=\"20\" value=\"" . (isset($boiapart[0]['NILAI_FASILITAS_KLS_BINTANG']) && $boiapart[0]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($boiapart[0]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
							<tr>
								<td align=\"center\">" . (isset($boiapart[1]['KD_FASILITAS']) && $boiapart[1]['KD_FASILITAS'] != '' ? $boiapart[1]['KD_FASILITAS'] : '-') . "</td>
								<td align=\"left\">" . (isset($boiapart[1]['NM_FASILITAS']) && $boiapart[1]['NM_FASILITAS'] != '' ? $boiapart[1]['NM_FASILITAS'] . ' KLS 3' : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[1]['SATUAN_FASILITAS']) && $boiapart[1]['SATUAN_FASILITAS'] != '' ? $boiapart[1]['SATUAN_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[1]['STATUS_FASILITAS']) && $boiapart[1]['STATUS_FASILITAS'] != '' ? $boiapart[1]['STATUS_FASILITAS'] : '-') . "</td>
								<td align=\"center\">" . (isset($boiapart[1]['KETERGANTUNGAN']) && $boiapart[1]['KETERGANTUNGAN'] != '' ? $boiapart[1]['KETERGANTUNGAN'] : '-') . "</td>
								<td><input type=\"text\" class=\"form-control\" name=\"nilaiboiapart2\" id=\"nilaiboiapart2\" size=\"25\" maxlength=\"20\" value=\"" . (isset($boiapart[1]['NILAI_FASILITAS_KLS_BINTANG']) && $boiapart[1]['NILAI_FASILITAS_KLS_BINTANG'] != '' ? number_format($boiapart[1]['NILAI_FASILITAS_KLS_BINTANG'], '0', ',', '') : '') . "\" placeholder=\"Nilai\" onkeypress=\"return iniAngka(event, this)\"></td>
							</tr>
						</table>
					</div>
					<hr>
					<div style=\"float: right\">
						<span id=\"loading-12\">&nbsp;</span><button value=\"Simpan\" id=\"simpan-12\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}
	function getHTMLListrik()
	{
		global $a, $m, $tab, $appConfig;

		$params 	= "a=" . $a . "&m=" . $m . "&tab=" . $tab;
		$dt			= $this->getDataListrik();

		// echo "<pre>";
		// print_r($dt);

		$HTML = "";
		//$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "
			<div class=\"row\">
				<div class=\"col-md-12\">
					<label><b>Listrik</b></label><hr>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Kode: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['KD_FASILITAS']) && $dt[0]['KD_FASILITAS'] != '' ? $dt[0]['KD_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Fasilitas: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['NM_FASILITAS']) && $dt[0]['NM_FASILITAS'] != '' ? $dt[0]['NM_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Satuan: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['SATUAN_FASILITAS']) && $dt[0]['SATUAN_FASILITAS'] != '' ? $dt[0]['SATUAN_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Status: </label></div>
						<div class=\"col-md-5\">" . (isset($dt[0]['STATUS_FASILITAS']) && $dt[0]['STATUS_FASILITAS'] != '' ? $dt[0]['STATUS_FASILITAS'] : '-') . "</div>
					</div>
					<div class=\"row\">
						<div class=\"col-md-2\"><label for=\"\">Nilai: </label></div>
						<div class=\"col-md-5\">
							<input type=\"text\" class=\"form-control\" value=\"" . (isset($dt[0]['NILAI_NON_DEP']) && $dt[0]['NILAI_NON_DEP'] != '' ? number_format($dt[0]['NILAI_NON_DEP'], '0', ',', '') : '0') . "\" name=\"nilaiPABX\" id=\"nilaiPABX\" size=\"25\" maxlength=\"20\" placeholder=\"Nilai\">
						</div>
					</div>
					<hr>
					<div style=\"float: right;\">
						<span id=\"loading-13\">&nbsp;</span><button class=\"btn btn-primary btn-orange\" value=\"Simpan\" id=\"simpan-13\"\">Simpan</button>
					</div>
				</div>
			</div>";

		//$HTML .= "\t</tr></div>\n";

		return $HTML;
	}
	public function content()
	{
		global $find, $a, $m, $tab;

		$HTML = "";

		switch ($tab) {
			case 0:
				$HTML .= $this->getHTMLAC();
				break;
			case 1:
				$HTML .= $this->getHTMLKlmRenang();
				break;
			case 2:
				$HTML .= $this->getHTMLPerkerasan();
				break;
			case 3:
				$HTML .= $this->getHTMLLapTenis();
				break;
			case 4:
				$HTML .= $this->getHTMLLift();
				break;
			case 5:
				$HTML .= $this->getHTMLEscalator();
				break;
			case 6:
				$HTML .= $this->getHTMLPagar();
				break;
			case 7:
				$HTML .= $this->getHTMLProtApi();
				break;
			case 8:
				$HTML .= $this->getHTMLGenset();
				break;
			case 9:
				$HTML .= $this->getHTMLPABX();
				break;
			case 10:
				$HTML .= $this->getHTMLAirArt();
				break;
			case 11:
				$HTML .= $this->getHTMLBoiler();
				break;
			case 12:
				$HTML .= $this->getHTMLListrik();
				break;
		}

		return $HTML;
	}

	public function displayData()
	{
		echo "<div class=\"ui-widget consol-main-content\">\n";
		echo "\t<div style=\"border: 1px solid grey; padding: 15px;\">\n";
		echo $this->content();
		echo "\t</div>\n";
	}
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a 		= $q->a;
$m 		= $q->m;
$n 		= $q->n;
$tab 	= $q->tab;
$uname 	= $q->u;
$uid 	= isset($q->uid) ? $q->uid : '';

// echo $tab;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);
$modNotaris = new  PriceFacility(1, $uname);
$tahun		= $appConfig['tahun_tagihan'];

$modNotaris->displayData();

?>
<div id="myDialog"></div>

<script type="text/javascript">
	var tahun = <?php echo $tahun; ?>;

	$(document).ready(function() {

		$("#simpan-1").click(function() {
			var vACSplit = $('#nilaiACSplit').val();
			var vACWindow = $('#nilaiACWindow').val();
			var vKantor1 = $('#nilaiACCentralKantor1').val();
			var vKantor2 = $('#nilaiACCentralKantor2').val();
			var vHotel1 = $('#nilaiACCentralHotel1').val();
			var vHotel2 = $('#nilaiACCentralHotel2').val();
			var vHotel3 = $('#nilaiACCentralHotel3').val();
			var vHotel4 = $('#nilaiACCentralHotel4').val();
			var vToko1 = $('#nilaiACCentralToko1').val();
			var vToko2 = $('#nilaiACCentralToko2').val();
			var vToko3 = $('#nilaiACCentralToko3').val();
			var vRS1 = $('#nilaiACCentralRS1').val();
			var vRS2 = $('#nilaiACCentralRS2').val();
			var vRS3 = $('#nilaiACCentralRS3').val();
			var vApt1 = $('#nilaiACCentralApt1').val();
			var vApt2 = $('#nilaiACCentralApt2').val();
			var vBngLain = $('#nilaiACCentralBngLain').val();

			document.getElementById("loading-1").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=1&tahun=" + tahun + "&vACSplit=" + vACSplit + "&vACWindow=" + vACWindow + "&vKantor1=" + vKantor1 + "&vKantor2=" + vKantor2 + "&vHotel1=" + vHotel1 + "&vHotel2=" + vHotel2 + "&vHotel3=" + vHotel3 + "&vHotel4=" + vHotel4 + "&vToko1=" + vToko1 + "&vToko2=" + vToko2 + "&vToko3=" + vToko3 + "&vRS1=" + vRS1 + "&vRS2=" + vRS2 + "&vRS3=" + vRS3 + "&vApt1=" + vApt1 + "&vApt2=" + vApt2 + "&vBngLain=" + vBngLain,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 0);
					document.getElementById("loading-1").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-1").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});

		$("#simpan-2").click(function() {
			var vKlmRenang1 = $('#nilaiKlmRenang1').val();
			var vKlmRenang2 = $('#nilaiKlmRenang2').val();
			var vKlmRenang3 = $('#nilaiKlmRenang3').val();
			var vKlmRenang4 = $('#nilaiKlmRenang4').val();
			var vKlmRenang5 = $('#nilaiKlmRenang5').val();
			var vKlmRenang6 = $('#nilaiKlmRenang6').val();
			var vKlmRenang7 = $('#nilaiKlmRenang7').val();
			var vKlmRenang8 = $('#nilaiKlmRenang8').val();
			var vKlmRenang9 = $('#nilaiKlmRenang9').val();
			var vKlmRenang10 = $('#nilaiKlmRenang10').val();

			document.getElementById("loading-2").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=2&tahun=" + tahun + "&vKlmRenang1=" + vKlmRenang1 + "&vKlmRenang2=" + vKlmRenang2 + "&vKlmRenang3=" + vKlmRenang3 + "&vKlmRenang4=" + vKlmRenang4 + "&vKlmRenang5=" + vKlmRenang5 + "&vKlmRenang6=" + vKlmRenang6 + "&vKlmRenang7=" + vKlmRenang7 + "&vKlmRenang8=" + vKlmRenang8 + "&vKlmRenang9=" + vKlmRenang9 + "&vKlmRenang10=" + vKlmRenang10,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 1);
					document.getElementById("loading-2").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-2").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-3").click(function() {
			var vPerkerasan1 = $('#nilaiPerkerasan1').val();
			var vPerkerasan2 = $('#nilaiPerkerasan2').val();
			var vPerkerasan3 = $('#nilaiPerkerasan3').val();
			var vPerkerasan4 = $('#nilaiPerkerasan4').val();

			document.getElementById("loading-3").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=3&tahun=" + tahun + "&vPerkerasan1=" + vPerkerasan1 + "&vPerkerasan2=" + vPerkerasan2 + "&vPerkerasan3=" + vPerkerasan3 + "&vPerkerasan4=" + vPerkerasan4,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 2);
					document.getElementById("loading-3").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-3").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-4").click(function() {
			var vlapTenis1 = $('#nilailapTenis1').val();
			var vlapTenis2 = $('#nilailapTenis2').val();
			var vlapTenis3 = $('#nilailapTenis3').val();
			var vlapTenis4 = $('#nilailapTenis4').val();
			var vlapTenis5 = $('#nilailapTenis5').val();
			var vlapTenis6 = $('#nilailapTenis6').val();
			var vlapTenis7 = $('#nilailapTenis7').val();
			var vlapTenis8 = $('#nilailapTenis8').val();
			var vlapTenis9 = $('#nilailapTenis9').val();
			var vlapTenis10 = $('#nilailapTenis10').val();
			var vlapTenis11 = $('#nilailapTenis11').val();
			var vlapTenis12 = $('#nilailapTenis12').val();

			document.getElementById("loading-4").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=4&tahun=" + tahun + "&vlapTenis1=" + vlapTenis1 + "&vlapTenis2=" + vlapTenis2 + "&vlapTenis3=" + vlapTenis3 + "&vlapTenis4=" + vlapTenis4 + "&vlapTenis5=" + vlapTenis5 + "&vlapTenis6=" + vlapTenis6 + "&vlapTenis7=" + vlapTenis7 + "&vlapTenis8=" + vlapTenis8 + "&vlapTenis9=" + vlapTenis9 + "&vlapTenis10=" + vlapTenis10 + "&vlapTenis11=" + vlapTenis11 + "&vlapTenis12=" + vlapTenis12,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 3);
					document.getElementById("loading-4").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-4").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-5").click(function() {
			var vlift1 = $('#nilailift1').val();
			var vlift2 = $('#nilailift2').val();
			var vlift3 = $('#nilailift3').val();
			var vlift4 = $('#nilailift4').val();
			var vlift5 = $('#nilailift5').val();
			var vlift6 = $('#nilailift6').val();
			var vlift7 = $('#nilailift7').val();
			var vlift8 = $('#nilailift8').val();
			var vlift9 = $('#nilailift9').val();
			var vlift10 = $('#nilailift10').val();
			var vlift11 = $('#nilailift11').val();
			var vlift12 = $('#nilailift12').val();

			document.getElementById("loading-5").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=5&tahun=" + tahun + "&vlift1=" + vlift1 + "&vlift2=" + vlift2 + "&vlift3=" + vlift3 + "&vlift4=" + vlift4 + "&vlift5=" + vlift5 + "&vlift6=" + vlift6 + "&vlift7=" + vlift7 + "&vlift8=" + vlift8 + "&vlift9=" + vlift9 + "&vlift10=" + vlift10 + "&vlift11=" + vlift11 + "&vlift12=" + vlift12,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 4);
					document.getElementById("loading-5").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-5").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-6").click(function() {
			var vEsc1 = $('#nilaiEsc1').val();
			var vEsc2 = $('#nilaiEsc2').val();


			document.getElementById("loading-6").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=6&tahun=" + tahun + "&vEsc1=" + vEsc1 + "&vEsc2=" + vEsc2,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 5);
					document.getElementById("loading-6").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-6").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-7").click(function() {
			var vpagar1 = $('#nilaiPagar1').val();
			var vpagar2 = $('#nilaiPagar2').val();


			document.getElementById("loading-7").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=7&tahun=" + tahun + "&vpagar1=" + vpagar1 + "&vpagar2=" + vpagar2,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 6);
					document.getElementById("loading-7").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-7").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-8").click(function() {
			var vProtApi1 = $('#nilaiProtApi1').val();
			var vProtApi2 = $('#nilaiProtApi2').val();
			var vProtApi3 = $('#nilaiProtApi3').val();


			document.getElementById("loading-8").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=8&tahun=" + tahun + "&vProtApi1=" + vProtApi1 + "&vProtApi2=" + vProtApi2 + "&vProtApi3=" + vProtApi3,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 7);
					document.getElementById("loading-8").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-8").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-9").click(function() {
			var vGenset1 = $('#nilaiGenset1').val();
			var vGenset2 = $('#nilaiGenset2').val();
			var vGenset3 = $('#nilaiGenset3').val();
			var vGenset4 = $('#nilaiGenset4').val();


			document.getElementById("loading-9").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=9&tahun=" + tahun + "&vGenset1=" + vGenset1 + "&vGenset2=" + vGenset2 + "&vGenset3=" + vGenset3 + "&vGenset4=" + vGenset4,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 8);
					document.getElementById("loading-9").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-9").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-10").click(function() {
			var vPABX = $('#nilaiPABX').val();



			document.getElementById("loading-10").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=10&tahun=" + tahun + "&vPABX=" + vPABX,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 9);
					document.getElementById("loading-10").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-10").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-11").click(function() {
			var vAirArt = $('#nilaiairart').val();



			document.getElementById("loading-11").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=11&tahun=" + tahun + "&vAirArt=" + vAirArt,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 10);
					document.getElementById("loading-11").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-11").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-12").click(function() {
			var vboihotel1 = $('#nilaiboihotel1').val();
			var vboihotel2 = $('#nilaiboihotel2').val();
			var vboihotel3 = $('#nilaiboihotel3').val();
			var vboiapart1 = $('#nilaiboiapart1').val();
			var vboiapart2 = $('#nilaiboiapart2').val();


			document.getElementById("loading-12").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=12&tahun=" + tahun + "&vboihotel1=" + vboihotel1 + "&vboihotel2=" + vboihotel2 + "&vboihotel3=" + vboihotel3 + "&vboiapart1=" + vboiapart1 + "&vboiapart2=" + vboiapart2,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 11);
					document.getElementById("loading-12").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-12").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});
		$("#simpan-13").click(function() {
			var vListrik = $('#nilaiListrik').val();



			document.getElementById("loading-13").innerHTML = "Loading.. ";
			$.ajax({
				type: "POST",
				url: "./function/PBB/penilaian_bangunan/harga_fasilitas_proses_simpan.php",
				data: "tab=13&tahun=" + tahun + "&vListrik=" + vListrik,
				dataType: "json",
				success: function(data) {

					$("#tabsContent").tabs('load', 12);
					document.getElementById("loading-13").innerHTML = "<font style=\"color : #008A2E;\">Berhasil disimpan..  </font>";
				},
				error: function() {
					document.getElementById("loading-13").innerHTML = "<font style=\"color : #993030;\">Penyimpanan data gagal!  </font>";
				}
			});
		});

	});

	function iniAngka(evt, x) {
		var charCode = (evt.which) ? evt.which : event.keyCode;

		if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
			return true;
		} else {
			alert("Input hanya boleh angka!");
			return false;
		}
	}
</script>