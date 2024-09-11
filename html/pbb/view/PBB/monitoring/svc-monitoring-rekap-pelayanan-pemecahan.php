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
					CPM_OP_KECAMATAN,
					C.CPC_TKC_KECAMATAN,
					CPM_OP_KELURAHAN,
					D.CPC_TKL_KELURAHAN,
					CPM_OP_NUMBER AS NOP_INDUK,
					CPM_SPPT_YEAR,
					E.SPPT_TANGGAL_CETAK
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
				LEFT JOIN cppmod_tax_kecamatan C ON A.CPM_OP_KECAMATAN = C.CPC_TKC_ID
				LEFT JOIN cppmod_tax_kelurahan D ON A.CPM_OP_KELURAHAN = D.CPC_TKL_ID
				LEFT JOIN {$tableCetak} E ON B.CPM_SP_NOP = E.NOP AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS >= '1' {$queryDate}
					{$where}
				GROUP BY
					CPM_OP_NUMBER
				ORDER BY 
					CPM_OP_NUMBER, CPM_ID";
						
		// echo $query;exit;
		$res = mysqli_query($DBLink, $query);
		$rowsData = "<table class=\"table table-bordered table-striped table-hover\">
				<thead>
				<tr>
					<th rowspan=2 width=10>NO</th>			
					<th rowspan=2 width=30>NOP INDUK</th>
					<th rowspan=2>KECAMATAN</th>
					<th rowspan=2>KELURAHAN</th>
					<th rowspan=2>TANGGAL REGISTRASI</th>
					<th rowspan=2>NO PELAYANAN</th>
					<th colspan=5>DATA HASIL PEMECAHAN</th>
					<th rowspan=2>PBB TERHUTANG</th>
					<th rowspan=2>TANGGAL CETAK</th>
				</tr>
				<tr>
					<th width=30>NOP BARU</th>
					<th>NAMA</th>
					<th>ALAMAT</th>
					<th width=90>LUAS TANAH</th>
					<th width=70>LUAS BANGUNAN</th>
				</tr>
				</thead>";
		$no=0;	
		while($row = mysqli_fetch_object($res)){
				$listPecah 	 = getListNOPPecahan($row->NOP_INDUK);
				if(!$listPecah){
					$listPecah = getData($row->NOP_INDUK);
				}
				$listId 	 = "";
				$listTgl 	 = "";
				$listNOP 	 = "";
				$listNama 	 = "";
				$listAlamat	 = "";
				$listLT		 = "";
				$listLB		 = "";
				$listTagihan = "";
				// print_r($listP); exit;
				// echo count($listPecah); exit;
				if(count($listPecah)>0){
					$i=1;
					foreach($listPecah as $val){
						$listId 		.= "({$i}).&nbsp;{$val['ID']}<br>";
						$listTgl 		.= "({$i}).&nbsp;{$val['TANGGAL']}<br>";
						$listNOP 		.= "({$i}).&nbsp;{$val['NOP']}<br>";
						$listNama 		.= "({$i}).&nbsp;{$val['NAMA']}<br>";
						$listAlamat 	.= "({$i}).&nbsp;{$val['ALAMAT']}<br>";
						$listLT 		.= "({$i})<span style=\"float:right\">{$val['LT']}</span><br>";
						$listLB 		.= "({$i})<span style=\"float:right\">{$val['LB']}</span><br>";
						$listTagihan	.= "({$i})<span style=\"float:right\">{$val['TAGIHAN']}</span><br>";
						$i++;
					}
				}
				$rowsData .= "
				<tr>
					<td class=tright>".(++$no)."</td>
					<td align=center>{$row->NOP_INDUK}</td>
					<td>{$row->CPC_TKC_KECAMATAN}</td>
					<td>{$row->CPC_TKL_KELURAHAN}</td>
					<td>{$listTgl}</td>
					<td>{$listId}</td>
					<td>{$listNOP}</td>
					<td>{$listNama}</td>
					<td>{$listAlamat}</td>
					<td>{$listLT}</td>
					<td>{$listLB}</td>
					<td>{$listTagihan}</td>
					<td class=tcenter>{$row->SPPT_TANGGAL_CETAK}</td>
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

function getListNOPPecahan($nop){
	global $DBLink,$appConfig,$thn,$jnsBerkas;
	
	if($thn==$appConfig['tahun_tagihan']){
		$tableCetak = "cppmod_pbb_sppt_current";
	} else {
		$tableCetak = "cppmod_pbb_sppt_cetak_".$thn;
	}
	
	$query = "SELECT
					A.CPM_DATE_RECEIVE AS TANGGAL,
					A.CPM_ID AS ID,
					E.NOP,
					E.WP_NAMA AS NAMA,
					E.OP_ALAMAT AS ALAMAT,
					E.OP_LUAS_BUMI AS LT,
					E.OP_LUAS_BANGUNAN AS LB,
					E.SPPT_PBB_HARUS_DIBAYAR AS TAGIHAN
				FROM
					cppmod_pbb_services A
				JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
				JOIN {$tableCetak} E ON B.CPM_SP_NOP = E.NOP
				AND CPM_SPPT_YEAR = E.SPPT_TAHUN_PAJAK
				WHERE 
					CPM_SPPT_YEAR = '{$thn}'
					AND CPM_TYPE = '{$jnsBerkas}'
					AND CPM_STATUS >= '1'
					AND CPM_OP_NUMBER = '{$nop}'
				ORDER BY 
					CPM_OP_NUMBER,CPM_ID ";
						
	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while($row = mysqli_fetch_assoc($res)){
		$dt[] = $row;
	}
	
	return $dt;
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
	global $DBLink,$jnsBerkas;
	
	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt_final E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";
						
	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while($row = mysqli_fetch_assoc($res)){
		$dt[] = $row;
	}
	
	return $dt;
}

function getSusulan($nop){
	global $DBLink,$jnsBerkas;
	
	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt_susulan E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";
						
	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while($row = mysqli_fetch_assoc($res)){
		$dt[] = $row;
	}
	
	return $dt;
}

function getPendataan($nop){
	global $DBLink,$jnsBerkas;
	
	$query = "SELECT
				A.CPM_DATE_RECEIVE AS TANGGAL,
				A.CPM_ID AS ID,
				E.CPM_NOP AS NOP,
				E.CPM_WP_NAMA AS NAMA,
				E.CPM_OP_ALAMAT AS ALAMAT,
				E.CPM_OP_LUAS_TANAH AS LT,
				E.CPM_OP_LUAS_BANGUNAN AS LB,
				0 TAGIHAN
			FROM
				cppmod_pbb_services A
			JOIN cppmod_pbb_service_split B ON A.CPM_ID = B.CPM_SP_SID
			JOIN cppmod_pbb_sppt E ON B.CPM_SP_NOP = E.CPM_NOP
			WHERE
				CPM_TYPE = '{$jnsBerkas}'
			AND CPM_STATUS >= '1'
			AND CPM_OP_NUMBER = '{$nop}'
			ORDER BY
				CPM_OP_NUMBER,
				CPM_ID ";
						
	// echo $query;exit;
	$res 	= mysqli_query($DBLink, $query);
	$dt 	= array();
	while($row = mysqli_fetch_assoc($res)){
		$dt[] = $row;
	}
	
	return $dt;
}
