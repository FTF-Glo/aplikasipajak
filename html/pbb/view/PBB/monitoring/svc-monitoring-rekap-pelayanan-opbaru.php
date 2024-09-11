<?php
error_reporting(E_ERROR);
ini_set('display_errors', 1);

session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/central/user-central.php");

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

$filterFromDate = isset($_REQUEST['fromDate']) && $_REQUEST['fromDate'] ? $_REQUEST['fromDate'] : '';
$filterToDate = isset($_REQUEST['toDate']) && $_REQUEST['toDate'] ? $_REQUEST['toDate'] : '';

$filterDate = array(
	mysqli_escape_string($DBLink, $filterFromDate), 
	mysqli_escape_string($DBLink, $filterToDate)
);

list($fromDate, $toDate) = $filterDate;
	
$queryDate = "";
if ($fromDate || $toDate) {
	$queryDate = array();
	$queryDate[] = $fromDate ? "A.CPM_DATE_RECEIVE >= '{$fromDate}'" : false;
	$queryDate[] = $toDate ? "A.CPM_DATE_RECEIVE <= '{$toDate}'" : false;

	$queryDate = array_filter($queryDate, function($value) { return $value; });
	$queryDate = 'AND ('. implode(' AND ', $queryDate) .')';
}

// print_r($_POST); exit;

/*proses simpan / tampil data */
if(isset($_POST['action'])){
	$response['msg'] = 'Proses data berhasil.';
	
	$jnsBerkas 	= $_POST['jnsBerkas'];
	$thn 		= $_POST['thn'];
	$kec 		= $_POST['kecamatan'];
	$kel		= $_POST['kelurahan'];
	
	if($thn==$appConfig['tahun_tagihan']){
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_".$thn;
	}

	$where = '';
	if ($kec != "") {
		if ($kel != "" && $kel != "null") $where .= "AND A.CPM_OP_KELURAHAN = '$kel'";
		else $where .= "AND A.CPM_OP_KECAMATAN = '$kec'";
	}
		
	if($_POST['action'] == 'btn-cari'){
		$query = "SELECT
					CPM_DATE_RECEIVE,
					CPM_ID,
					CPM_OP_KECAMATAN,
					C.CPC_TKC_KECAMATAN,
					CPM_OP_KELURAHAN,
					D.CPC_TKL_KELURAHAN,
					B.CPM_NEW_NOP,
					CPM_WP_NAME,
					CPM_WP_ADDRESS,
					CPM_OP_ADDRESS,
					CPM_SPPT_YEAR,
					E.OP_LUAS_BUMI,
					E.OP_LUAS_BANGUNAN,
					E.SPPT_PBB_HARUS_DIBAYAR,
					E.SPPT_TANGGAL_CETAK
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_new_op B ON A.CPM_ID = B.CPM_NEW_SID
				LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID
				LEFT JOIN {$tableCetak} E ON B.CPM_NEW_NOP = E.NOP AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS = '4' {$queryDate}
					{$where}
				ORDER BY 
					CPM_ID";
		
		// echo $query;exit;
		$res = mysqli_query($DBLink, $query);
		$rowsData = "<table class=\"table table-bordered table-striped table-hover\">
					<thead>
						<tr>
							<th width=10>NO</th>
							<th>TANGGAL REGISTRASI</th>
							<th>NO PELAYANAN</th>
							<th>KECAMATAN</th>
							<th>KELURAHAN</th>
							<th>NOP HASIL</th>
							<th>NAMA</th>
							<th>ALAMAT WP</th>
							<th>ALAMAT OP</th>
							<th>LUAS TANAH</th>
							<th>LUAS BANGUNAN</th>
							<th>PBB TERHUTANG</th>
							<th>TANGGAL CETAK</th>
						</tr>
					</thead>";
		$no=0;
		while($row = mysqli_fetch_object($res)){
			$dt = getData($row->CPM_NEW_NOP);
			$lt = ($row->OP_LUAS_BUMI!=0 || $row->OP_LUAS_BUMI!=null ? $row->OP_LUAS_BUMI : (!empty($dt) ? $dt->CPM_OP_LUAS_TANAH : 0));
			$lb = ($row->OP_LUAS_BANGUNAN!=0 || $row->OP_LUAS_BANGUNAN!=null ? $row->OP_LUAS_BANGUNAN : (!empty($dt) ? $dt->CPM_OP_LUAS_BANGUNAN : 0));
			$rowsData .= "
			<tr class=tright>
				<td>".(++$no)."</td>
				<td class=tcenter>{$row->CPM_DATE_RECEIVE}</td>
				<td class=tcenter>{$row->CPM_ID}</td>
				<td class=tleft>{$row->CPC_TKC_KECAMATAN}</td>
				<td class=tleft>{$row->CPC_TKL_KELURAHAN}</td>
				<td class=tcenter>{$row->CPM_NEW_NOP}</td>
				<td class=tleft>{$row->CPM_WP_NAME}</td>
				<td class=tleft>{$row->CPM_WP_ADDRESS}</td>
				<td class=tleft>{$row->CPM_OP_ADDRESS}</td>
				<td>".number_format($lt,0)."</td>
				<td>".number_format($lb,0)."</td>
				<td>".number_format($row->SPPT_PBB_HARUS_DIBAYAR,0)."</td>
				<td class=tcenter>{$row->SPPT_TANGGAL_CETAK}</td>
			</tr>";
		}
		$rowsData .= "</table>";
		$response['totalRows'] 	= $no;
		$response['table'] 		= $rowsData;
		// print_r($dt);exit;
	}

	$json = $json->encode($response);
	$json = str_replace('\n','',$json);
	$json = str_replace('\t','',$json);
	
	exit($json);
}	

function getData($nop){
	$data = getFinal($nop);
	if(empty($data)){
		$data = getSusulan($nop);
		if(empty($data)){
			$data = getPendataan($nop);
		}
	}
	return $data;
}

function getFinal($nop){
	global $DBLink;
	
	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt_final
			WHERE CPM_NOP = '{$nop}' ";
			
	$res = mysqli_query($DBLink, $query);
	if($res===false){
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res); 
	
	return $row;
}

function getSusulan($nop){
	global $DBLink;
	
	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt_susulan
			WHERE CPM_NOP = '{$nop}' ";
			
	$res = mysqli_query($DBLink, $query);
	if($res===false){
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res); 
	
	return $row;
}

function getPendataan($nop){
	global $DBLink;
	
	$query = "SELECT
				CPM_OP_LUAS_TANAH,
				CPM_OP_LUAS_BANGUNAN
			FROM
				cppmod_pbb_sppt
			WHERE CPM_NOP = '{$nop}' ";
			
	$res = mysqli_query($DBLink, $query);
	if($res===false){
		mysqli_error($DBLink);
		exit;
	}
	$row = mysqli_fetch_object($res); 
	
	return $row;
}
