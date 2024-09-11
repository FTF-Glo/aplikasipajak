<?php
error_reporting(E_ERROR);
ini_set('display_errors', 1);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
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

/* inisiasi parameter */
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

/*proses simpan / tampil data */
if (isset($_POST['action'])) {
	$response['msg'] = 'Proses data berhasil.';

	$nama = isset($_POST['nama']) ? addslashes(trim($_POST['nama'])) : '';
	$serti = isset($_POST['serti']) ? addslashes(trim($_POST['serti'])) : '';
	$kel = isset($_POST['kel'])  && $_POST['kel']!='' ? (int)$_POST['kel'] : '';

	$nop1 = $_POST['nop1'];
	$nop2 = $_POST['nop2'];
	$nop3 = $_POST['nop3'];
	$nop4 = $_POST['nop4'];
	$nop5 = $_POST['nop5'];
	$nop6 = $_POST['nop6'];
	$nop7 = $_POST['nop7'];

	$blok_awal = isset($_POST['blok_awal']) ? $_POST['blok_awal'] : '';
	$blok_akhir = isset($_POST['blok_akhir']) ? $_POST['blok_akhir'] : '';

	$join = '';
	$asSertifikat = " '' AS CPM_NOMOR_SERTIFIKAT";

	if ($_POST['action'] == 'btn-cari') {
		$where = ' 1=1 ';
		$where .= ($nama!='') ? " AND (f.CPM_WP_NAMA LIKE '%$nama%' OR f.CPM_OP_ALAMAT LIKE '%$nama%') " : "";
		$join = "LEFT JOIN cppmod_pbb_sppt_sertifikat s ON f.CPM_NOP=s.CPM_NOP";
		if($serti!=''){
			$where .= " AND s.CPM_NOMOR_SERTIFIKAT = '$serti' ";
		}
		$asSertifikat = "s.CPM_NOMOR_SERTIFIKAT";
		
		if(!empty($kel)){
			$where .= " AND LEFT(f.CPM_NOP,10)='$kel' ";
			if($blok_awal!='') {
				$blok_awal = $kel . $blok_awal;
				$where .= " AND SUBSTR(f.CPM_NOP,1,13)>='$blok_awal' ";
			}
			if($blok_akhir!='') {
				$blok_akhir = $kel . $blok_akhir;
				$where .= " AND SUBSTR(f.CPM_NOP,1,13)<='$blok_akhir' ";
			}
		}

		$where .= empty($nop1) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 1, 2) = '%s'", $nop1);
		$where .= empty($nop2) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 3, 2) = '%s'", $nop2);
		$where .= empty($nop3) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 5, 3) = '%s'", $nop3);
		$where .= empty($nop4) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 8, 3) = '%s'", $nop4);
		$where .= empty($nop5) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 11, 3) = '%s'", $nop5);
		$where .= empty($nop6) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 14, 4) = '%s'", $nop6);
		$where .= empty($nop7) ? '' : sprintf("AND SUBSTR(f.CPM_NOP, 18, 1) = '%s'", $nop7);
		$query = "SELECT A.* FROM (SELECT 
				f.CPM_NOP, f.CPM_OP_ALAMAT, f.CPM_OP_RT, f.CPM_OP_RW, f.CPM_OT_ZONA_NILAI, f.CPM_OP_LUAS_TANAH, 
				f.CPM_OP_LUAS_BANGUNAN, f.CPM_NJOP_TANAH, f.CPM_NJOP_BANGUNAN, f.CPM_WP_NAMA, $asSertifikat
				FROM cppmod_pbb_sppt_final f 
				$join
				WHERE {$where}

				UNION

				SELECT 
				f.CPM_NOP, f.CPM_OP_ALAMAT, f.CPM_OP_RT, f.CPM_OP_RW, f.CPM_OT_ZONA_NILAI, f.CPM_OP_LUAS_TANAH, 
				f.CPM_OP_LUAS_BANGUNAN, f.CPM_NJOP_TANAH, f.CPM_NJOP_BANGUNAN, f.CPM_WP_NAMA, $asSertifikat
				FROM cppmod_pbb_sppt_susulan f 
				$join 
				WHERE {$where}

				UNION 

				SELECT 
				f.CPM_NOP, f.CPM_OP_ALAMAT, f.CPM_OP_RT, f.CPM_OP_RW, f.CPM_OT_ZONA_NILAI, f.CPM_OP_LUAS_TANAH, 
				f.CPM_OP_LUAS_BANGUNAN, f.CPM_NJOP_TANAH, f.CPM_NJOP_BANGUNAN, f.CPM_WP_NAMA, $asSertifikat
				FROM cppmod_pbb_sppt f 
				$join 
				WHERE {$where} 
			) AS A
			ORDER BY A.CPM_NOP ASC";

		// echo $query;exit;
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no = 0;
		$ungu = 'style="background:#f1fdf6"';
		$biru = 'style="background:#fff"';
		while ($row = mysqli_fetch_object($res)) {
			$row->CPM_NOP = substr($row->CPM_NOP, 10, 3) . "-" . substr($row->CPM_NOP, 13, 4) . "." . substr($row->CPM_NOP, 17, 1);
			$bgcolor = ($no%2==0) ? $ungu : $biru;
			$rowsData .= "
			<tr class='text-right'>
				<td $bgcolor rowspan='2'>" . (++$no) . "</td>
				<td $bgcolor rowspan='2'>{$row->CPM_NOP}</td>
				<td $bgcolor class='text-left'>{$row->CPM_OP_ALAMAT}</td>
				<td $bgcolor class='text-center'>{$row->CPM_OP_RT}</td>
				<td $bgcolor rowspan='2' class='text-center'>{$row->CPM_OT_ZONA_NILAI}</td>
				<td $bgcolor>" . number_format($row->CPM_OP_LUAS_TANAH, 0) . "</td>
				<td $bgcolor>" . number_format($row->CPM_NJOP_TANAH, 0) . "</td>
				<td $bgcolor rowspan='2'>" . number_format($row->CPM_NJOP_TANAH + $row->CPM_NJOP_BANGUNAN, 0) . "</td>
				<td $bgcolor rowspan='2'>" . $row->CPM_NOMOR_SERTIFIKAT . "</td>
			</tr>
			<tr class='text-right'>
				<td $bgcolor class='text-left'>{$row->CPM_WP_NAMA}</td>
				<td $bgcolor class='text-center'>{$row->CPM_OP_RW}</td>
				<td $bgcolor>" . number_format($row->CPM_OP_LUAS_BANGUNAN, 0) . "</td>
				<td $bgcolor>" . number_format($row->CPM_NJOP_BANGUNAN, 0) . "</td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
	}

	exit($json->encode($response));
}
