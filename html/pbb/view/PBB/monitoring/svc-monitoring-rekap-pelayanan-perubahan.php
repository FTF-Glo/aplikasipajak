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

$jnsBerkas 	= $_POST['jnsBerkas'];
$thn 		= $_POST['thn'];
$kec 		= $_POST['kecamatan'];
$kel		= $_POST['kelurahan'];

$where = '';
if ($kec != "") {
	if ($kel != "" && $kel != "null") $where .= "AND A.CPM_OP_KELURAHAN = '$kel'";
	else $where .= "AND A.CPM_OP_KECAMATAN = '$kec'";
}


$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

// print_r($_POST); exit;

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


/*proses simpan / tampil data */
if(isset($_POST['action'])){
	$response['msg'] = 'Proses data berhasil.';
	
	if($thn==$appConfig['tahun_tagihan']){
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_".$thn;
	}
		
	if($_POST['action'] == 'btn-cari'){
		$query = "SELECT
					CPM_DATE_RECEIVE,
					CPM_ID,
					A.CPM_OP_KECAMATAN,
					C.CPC_TKC_KECAMATAN,
					A.CPM_OP_KELURAHAN,
					D.CPC_TKL_KELURAHAN,
					A.CPM_OP_NUMBER AS NOP,
					E.WP_NAMA AS OLD_NAME,
					E.OP_ALAMAT AS OLD_ADDRESS,
					E.OP_LUAS_BUMI AS OLD_LT,
					E.OP_LUAS_BANGUNAN AS OLD_LB,
					B.CPM_WP_NAMA AS NEW_NAME,
					B.CPM_OP_ALAMAT AS NEW_ADDRESS,
					B.CPM_OP_LUAS_TANAH AS NEW_LT,
					B.CPM_OP_LUAS_BANGUNAN AS NEW_LB,
					E.SPPT_PBB_HARUS_DIBAYAR,
					E.SPPT_TANGGAL_CETAK
				FROM
					cppmod_pbb_services A
				LEFT JOIN cppmod_pbb_service_change_history B ON A.CPM_ID = B.CPM_SID
				LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID
				LEFT JOIN {$tableCetak} E ON A.CPM_OP_NUMBER = E.NOP AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS = '4' {$queryDate}
					{$where} 
				ORDER BY CPM_DATE_RECEIVE DESC, CPM_ID";
						
		// echo $query;exit;
		$res = mysqli_query($DBLink, $query);
		$rowsData = "
			<table class=\"table table-bordered table-striped table-hover\">
				<thead>
				<tr>
					<th rowspan=2 width=10>NO</th>		
					<th rowspan=2>TANGGAL REGISTRASI</th>
					<th rowspan=2>NO PELAYANAN</th>
					<th rowspan=2>KECAMATAN</th>
					<th rowspan=2>KELURAHAN</th>
					<th rowspan=2 width=30>NOP</th>
					<th colspan=2>NAMA</th>
					<th colspan=2>ALAMAT</th>
					<th colspan=2 width=90>LUAS TANAH</th>
					<th colspan=2 width=70>LUAS BANGUNAN</th>
					<th rowspan=2>PBB TERHUTANG</th>
					<th rowspan=2>TANGGAL CETAK</th>
				</tr>
				<tr>
					<th>LAMA</th>
					<th>BARU</th>
					<th>LAMA</th>
					<th>BARU</th>
					<th width=90>LAMA</th>
					<th width=90>BARU</th>
					<th width=70>LAMA</th>
					<th width=70>BARU</th>
				</tr>
				</thead>
		";
		$no=0;	
		while($row = mysqli_fetch_object($res)){
			$rowsData .= "
				<tr>
					<td class=tright>".(++$no)."</td>
					<td class=center>{$row->CPM_DATE_RECEIVE}</td>
					<td class=center>{$row->CPM_ID}</td>
					<td>{$row->CPC_TKC_KECAMATAN}</td>
					<td>{$row->CPC_TKL_KELURAHAN}</td>
					<td>{$row->NOP}</td>
					<td".(($row->OLD_NAME!==$row->NEW_NAME)?' class=svr3':'').">{$row->OLD_NAME}</td>
					<td".(($row->OLD_NAME!==$row->NEW_NAME)?' class=kec':'').">{$row->NEW_NAME}</td>
					<td".(($row->OLD_ADDRESS!==$row->NEW_ADDRESS)?' class=svr3':'').">{$row->OLD_ADDRESS}</td>
					<td".(($row->OLD_ADDRESS!==$row->NEW_ADDRESS)?' class=kec':'').">{$row->NEW_ADDRESS}</td>
					<td".(($row->OLD_LT!==$row->NEW_LT)?' class="svr3 center"':' class=center').">{$row->OLD_LT}</td>
					<td".(($row->OLD_LT!==$row->NEW_LT)?' class="kec center"':' class=center').">{$row->NEW_LT}</td>
					<td".(($row->OLD_LB!==$row->NEW_LB)?' class="svr3 center"':' class=center').">{$row->OLD_LB}</td>
					<td".(($row->OLD_LB!==$row->NEW_LB)?' class="kec center"':' class=center').">{$row->NEW_LB}</td>
					<td class=tright>{$row->SPPT_PBB_HARUS_DIBAYAR}</td>
					<td class=center>{$row->SPPT_TANGGAL_CETAK}</td>
				</tr>";
		}
		$rowsData .= "</table>";
		$response['totalRows'] 	= $no;
		$response['table'] 		= $rowsData;
		
	}
	
	$json = $json->encode($response);
	$json = str_replace('\n','',$json);
	$json = str_replace('\t','',$json);
	
	exit($json);
}	