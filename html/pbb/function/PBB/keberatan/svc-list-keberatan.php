<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
echo '<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>';

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

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getKecamatanNama($kode)
{
	global $DBLink;
	$query = "SELECT * FROM `cppmod_tax_kecamatan` WHERE CPC_TKC_ID = '" . $kode . "';";
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_array($res);
	return $row['CPC_TKC_KECAMATAN'];
}
function getKelurahanNama($kode)
{
	global $DBLink;
	$query = "SELECT * FROM `cppmod_tax_kelurahan` WHERE CPC_TKL_ID = '" . $kode . "';";
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_array($res);
	return $row['CPC_TKL_KELURAHAN'];
}

function hitung($aValue)
{

	global $DBLink, $minimum_njoptkp, $minimum_sppt_pbb_terhutang;

	$NJOPTKP = $minimum_njoptkp;
	$minPBBHarusBayar = $minimum_sppt_pbb_terhutang;


	$NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'];

	if ($NJOP > $NJOPTKP)
		$NJKP = $NJOP - $NJOPTKP;
	else $NJKP = 0;

	$aValue['OP_NJOP'] = $NJOP;
	$aValue['OP_NJKP'] = $NJKP;
	$aValue['OP_NJOPTKP'] = $NJOPTKP;

	$cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                        CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND
                        CPM_TRF_NILAI_ATAS >= " . $NJKP;
	$resTarif = mysqli_query($DBLink, $cari_tarif);
	if (!$resTarif) {
		echo mysqli_error($DBLink);
		echo $cari_tarif;
	}

	$dataTarif = mysqli_fetch_array($resTarif);
	$op_tarif = $dataTarif['CPM_TRF_TARIF'];
	$aValue['OP_TARIF'] = $op_tarif;
	$PBB_HARUS_DIBAYAR = $NJKP * ($op_tarif / 100);

	if ($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
		$PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
	$aValue['SPPT_PBB_HARUS_DIBAYAR'] = $PBB_HARUS_DIBAYAR;

	return $aValue;
}

function headerPengurangan($mod, $nama)
{
	global $appConfig;

	$model = ($mod == 0) ? "KECAMATAN" : $appConfig['LABEL_KELURAHAN'];
	$dl = "";
	if ($mod == 0) {
		$dl = $appConfig['C_KABKOT'] . ' ' . $appConfig['NAMA_KOTA'];
	} else {
		$dl = $nama;
	}
	$html = "
	<table cellspacing=\"0\" cellpadding=\"4\" border =\"1\"><tr><td colspan=\"13\" align=\"center\" height=\"35\"><b>{$dl}<b></td></tr>
		<tr>
			<td rowspan=\"2\" width=\"auto\" height=\"35\" align=\"center\"><b>NO</b></td>
			<td rowspan=\"2\" width=\"auto\" align=\"center\"><b>NOP</b></td>
			<td rowspan=\"2\" width=\"150\" align=\"center\"><b>NAMA</b></td>
			<td rowspan=\"2\" width=\"300\" align=\"center\"><b>ALAMAT</b></td>
			<td rowspan=\"2\" width=\"150\" align=\"center\"><b>KECAMATAN</b></td>
			<td rowspan=\"2\" width=\"150\" align=\"center\"><b>" . strtoupper($appConfig['LABEL_KELURAHAN']) . "</b></td>
			<td colspan=\"2\" width=\"auto\" align=\"center\"><b>NJOP BUMI/M2</b></td>
			<td rowspan=\"2\" width=\"auto\" align=\"center\"><b>NJOP BANGUNAN/M2</b></td>
			<td colspan=\"2\" width=\"auto\" align=\"center\"><b>TOTAL NJOP</b></td>
			<td colspan=\"2\" width=\"auto\" align=\"center\"><b>KETETAPAN</b></td>
		</tr>
		<tr>
			<td width=\"100\" align=\"center\"><b>SEMULA</b></td>
			<td width=\"100\" align=\"center\"><b>MENJADI</b></td>
			<td width=\"100\" align=\"center\"><b>SEMULA</b></td>
			<td width=\"100\" align=\"center\"><b>MENJADI</b></td>
			<td width=\"100\" align=\"center\"><b>SEMULA</b></td>
			<td width=\"100\" align=\"center\"><b>MENJADI</b></td>
		</tr>
	";
	return $html;
}

function getKecamatan($p)
{
	global $DBLink;
	$return = array();
	$query = "SELECT * FROM cppmod_tax_kecamatan WHERE CPC_TKC_KKID ='" . $p . "' ORDER BY CPC_TKC_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKC_ID"];
		$data[$i]["name"] = $row["CPC_TKC_KECAMATAN"];
		$i++;
	}

	return $data;
}

function getKelurahan($p)
{
	global $DBLink;
	$query = "SELECT * FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID like '{$p}%' ORDER BY CPC_TKL_URUTAN";
	$res = mysqli_query($DBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}
	$data = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		$data[$i]["id"] = $row["CPC_TKL_ID"];
		$data[$i]["name"] = $row["CPC_TKL_KELURAHAN"];
		$i++;
	}
	return $data;
}

function showTable($mod = 0, $nama = "")
{
	global $kd, $kecamatan, $kelurahan, $kab, $dt, $page, $perpage, $totalrows, $sum;

	$c = $dt['JML_DATA'];
	//echo $c;
	//print_r($dt);
	$number = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$html = "";
	$a = $number + 1;
	$html = headerPengurangan($mod, $nama);
	$summary = array('name' => 'JUMLAH', 'njop_awal' => 0, 'ketetapan_awal' => 0, 'njop_final' => 0, 'ketetapan_final' => 0, 'njop_bangunan' => 0, 'tagihan_semula' => 0, 'tagihan_menjadi' => 0);
	$vObjection	= array();
	$totalTagihan = 0;
	for ($i = 0; $i < $c; $i++) {

		$nop  	 	   						= $dt[$i]['CPM_OP_NUMBER'];
		$name 	 	   						= $dt[$i]['CPM_WP_NAME'];
		$alamat	 	   						= $dt[$i]['CPM_OP_ADDRESS'];
		$kecamatan 	   						= getKecamatanNama($dt[$i]['CPM_OP_KECAMATAN']);
		$kelurahan 	   						= getKelurahanNama($dt[$i]['CPM_OP_KELURAHAN']);
		$luasTanah							= ($dt[$i]['CPM_OB_LUAS_TANAH'] != 0 ? $dt[$i]['CPM_OB_LUAS_TANAH'] : 1);
		$NJOPAwal 	   						= floor(($dt[$i]['CPM_OB_NJOP_TANAH'] / $luasTanah));
		$NJOPFinal	   						= floor(($dt[$i]['CPM_OB_NJOP_TANAH_APP'] / $luasTanah));
		$luasBangunan						= ($dt[$i]['CPM_OB_LUAS_BANGUNAN'] != 0 ? $dt[$i]['CPM_OB_LUAS_BANGUNAN'] : 1);
		$NJOPBangunan  						= floor(($dt[$i]['CPM_OB_NJOP_BANGUNAN'] / $luasBangunan));
		$ketetapanAwal 						= $dt[$i]['CPM_OB_NJOP_TANAH'] + $dt[$i]['CPM_OB_NJOP_BANGUNAN'];
		$ketetapanFinal                 	= $dt[$i]['CPM_OB_NJOP_TANAH_APP'] + $dt[$i]['CPM_OB_NJOP_BANGUNAN'];
		$tagihanSemula						= $dt[$i]['CPM_SPPT_DUE'];

		$vObjection['CPM_NJOP_TANAH']		= $dt[$i]['CPM_OB_NJOP_TANAH_APP'];
		$vObjection['CPM_NJOP_BANGUNAN']   	= $dt[$i]['CPM_OB_NJOP_BANGUNAN'];
		$tagihanMenjadi						= hitung($vObjection);

		$totalTagihan					+=  $tagihanMenjadi['SPPT_PBB_HARUS_DIBAYAR'];
		$html .= "<tr>
					<td align=\"right\">{$a}</td>
					<td align=\"center\">{$nop}</td>
					<td align=\"left\">{$name}</td>
					<td align=\"left\">{$alamat}</td>
					<td align=\"left\">" . $kecamatan . "</td>
					<td align=\"left\">" . $kelurahan . "</td>
					<td align=\"right\">" . number_format($NJOPAwal, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($NJOPFinal, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($NJOPBangunan, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($ketetapanAwal, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($ketetapanFinal, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($tagihanSemula, 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($tagihanMenjadi['SPPT_PBB_HARUS_DIBAYAR'], 0, ",", ".") . "</td>
				  </tr>";

		/* $summary['njop_awal'] 	 	     += $NJOPAwal;
		 $summary['njop_final'] 	     += $NJOPFinal;
		 $summary['njop_bangunan'] 	     += $NJOPBangunan;
		 $summary['ketetapan_awal'] 	 += $ketetapanAwal;
		 $summary['ketetapan_final'] 	 += $ketetapanFinal;
		 $summary['tagihan_semula'] 	 += $tagihanSemula;
		 $summary['tagihan_menjadi'] 	 += $tagihanMenjadi['SPPT_PBB_HARUS_DIBAYAR']; */



		$a++;
	}

	$html .= "<tr>
					<td align=\"center\" colspan=\"6\">JUMLAH PERMOHONAN : " . $totalrows . "</td>
					<td align=\"right\">" . number_format($sum['NJOP_AWAL'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($sum['NJOP_AKHIR'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($sum['NJOP_BNG'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($sum['TNJOP_AWAL'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($sum['TNJOP_AKHIR'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($sum['KET_AWAL'], 0, ",", ".") . "</td>
					<td align=\"right\">" . number_format($totalTagihan, 0, ",", ".") . "</td>	
				  </tr>";

	return $html . "</table><div align=\"center\">" . paging() . "</div>";
}

function getSummary($where)
{
	global $DBLink;

	$whr = "";
	if ($where) {
		$whr = " AND {$where}";
	}
	$query = "SELECT ROUND(SUM(CPM_OB_NJOP_TANAH/IF(CPM_OB_LUAS_TANAH,CPM_OB_LUAS_TANAH,1))) AS NJOP_AWAL,
				ROUND(SUM(CPM_OB_NJOP_TANAH_APP/IF(CPM_OB_LUAS_TANAH,CPM_OB_LUAS_TANAH,1))) AS NJOP_AKHIR,
				ROUND(SUM(CPM_OB_NJOP_BANGUNAN/IF(CPM_OB_LUAS_BANGUNAN,CPM_OB_LUAS_BANGUNAN,1))) AS NJOP_BNG, 
				SUM(IFNULL(CPM_OB_NJOP_TANAH,0)+IFNULL(CPM_OB_NJOP_BANGUNAN,0)) AS TNJOP_AWAL,
				SUM(IFNULL(CPM_OB_NJOP_TANAH_APP,0)+IFNULL(CPM_OB_NJOP_BANGUNAN,0)) AS TNJOP_AKHIR,
				SUM(CPM_SPPT_DUE) AS KET_AWAL
				FROM cppmod_pbb_services A JOIN cppmod_pbb_service_objection B WHERE A.CPM_ID=B.CPM_OB_SID {$whr}";
	//echo $query;
	$res   = mysqli_query($DBLink, $query);
	$row   = mysqli_fetch_assoc($res);
	//print_r($row);
	return $row;
}

function getData($where, $page, $perpage)
{
	global $DBLink, $kd, $thn, $bulan, $sum;
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$return = array();
	$return["CPM_OP_NUMBER"] = '';
	$return["CPM_WP_NAME"] = '';
	$return["CPM_OP_ADDRESS"] = '';
	$return["CPM_OP_KECAMATAN"] = '';
	$return["CPM_OP_KELURAHAN"] = '';
	$return["CPM_OB_NJOP_TANAH"] = 0;
	$return["CPM_SPPT_DUE"] = 0;
	$return["CPM_OB_NJOP_TANAH_APP"] = 0;
	$return["CPM_OB_NJOP_BANGUNAN"] = 0;
	$return["CPM_OB_LUAS_BANGUNAN"] = 0;
	$return["CPM_OB_LUAS_TANAH"] = 0;
	$whr = "";
	if ($where) {
		$whr = " AND {$where}";
	}
	$query = "SELECT * FROM cppmod_pbb_services A JOIN cppmod_pbb_service_objection B WHERE A.CPM_ID = B.CPM_OB_SID AND A.CPM_STATUS='4' {$whr} "; //echo $query.'<br/>';

	if ($perpage) {
		$query .= " ORDER BY CPM_DATE_RECEIVE DESC LIMIT $hal, $perpage ";
	}
	//echo $query.'<br/>';
	$qRows   = "SELECT * FROM cppmod_pbb_services A JOIN cppmod_pbb_service_objection B WHERE A.CPM_ID = B.CPM_OB_SID AND A.CPM_STATUS='4' {$whr}";
	$resRows = mysqli_query($DBLink, $qRows);
	$return['JML_ROWS'] = mysqli_num_rows($resRows);

	$res = mysqli_query($DBLink, $query);
	$return['JML_DATA'] = mysqli_num_rows($res);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = array();
	$i = 0;
	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return[$i]["CPM_OP_NUMBER"] 		  = ($row["CPM_OP_NUMBER"] != "") ? $row["CPM_OP_NUMBER"] : '-';
		$return[$i]["CPM_WP_NAME"]	 		  = ($row["CPM_WP_NAME"] != "") ? $row["CPM_WP_NAME"] : '-';
		$return[$i]["CPM_OP_ADDRESS"]		  = ($row["CPM_OP_ADDRESS"] != "") ? $row["CPM_OP_ADDRESS"] : '-';
		$return[$i]["CPM_OP_KECAMATAN"]		  = ($row["CPM_OP_KECAMATAN"] != "") ? $row["CPM_OP_KECAMATAN"] : 0;
		$return[$i]["CPM_OP_KELURAHAN"]		  = ($row["CPM_OP_KELURAHAN"] != "") ? $row["CPM_OP_KELURAHAN"] : 0;
		$return[$i]["CPM_OB_NJOP_TANAH"] 	  = ($row["CPM_OB_NJOP_TANAH"] != "") ? $row["CPM_OB_NJOP_TANAH"] : 0;
		$return[$i]["CPM_SPPT_DUE"]			  = ($row["CPM_SPPT_DUE"] != "") ? $row["CPM_SPPT_DUE"] : 0;
		$return[$i]["CPM_OB_NJOP_TANAH_APP"]  = ($row["CPM_OB_NJOP_TANAH_APP"] != "") ? $row["CPM_OB_NJOP_TANAH_APP"] : 0;
		$return[$i]["CPM_OB_NJOP_BANGUNAN"]   = ($row["CPM_OB_NJOP_BANGUNAN"] != "") ? $row["CPM_OB_NJOP_BANGUNAN"] : 0;
		$return[$i]["CPM_OB_LUAS_BANGUNAN"]	  = ($row["CPM_OB_LUAS_BANGUNAN"] != "") ? $row["CPM_OB_LUAS_BANGUNAN"] : 0;
		$return[$i]["CPM_OB_LUAS_TANAH"]	  = ($row["CPM_OB_LUAS_TANAH"] != "") ? $row["CPM_OB_LUAS_TANAH"] : 0;
		$i++;
	}
	return $return;
}

function paging()
{
	global $s, $page, $np, $perpage, $defaultPage, $totalrows;

	$html = "<div>";
	$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
	$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;
	/* echo $rowlast."<br>";
		echo $totalrows."<br>"; */
	if ($page != 1) {
		//$page--;
		$html .= "&nbsp;<a onclick=\"showPenguranganPage(" . ($page - 1) . ")\"><span id=\"navigator-left\"></span></a>";
	}
	if ($rowlast < $totalrows) {
		//$page++;
		$html .= "&nbsp;<a onclick=\"showPenguranganPage(" . ($page + 1) . ")\"><span id=\"navigator-right\"></span></a>";
	}
	$html .= "</div>";
	return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//print_r($q);

$User 							 = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 						 = $User->GetAppConfig($a);
$kd 							 = $appConfig['KODE_KOTA'];
$minimum_njoptkp 			 	 = $appConfig['minimum_njoptkp'];
$minimum_sppt_pbb_terhutang 	 = $appConfig['minimum_sppt_pbb_terhutang'];

$kab  			  = @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$kecamatan 		  = @isset($_REQUEST['kc']) ? $_REQUEST['kc'] : "";
$thn 			  = @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$nama	 		  = @isset($_REQUEST['n']) ? $_REQUEST['n'] : "";
$page 			  = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

$perpage 	= $appConfig['ITEM_PER_PAGE'];

$arrWhere = array();

if ($kecamatan != "") {
	array_push($arrWhere, " CPM_OP_NUMBER LIKE '" . $kecamatan . "%'");
}
if ($thn != "") {
	array_push($arrWhere, " CPM_SPPT_YEAR='{$thn}'");
}
$where = implode(" AND ", $arrWhere);

//echo $where;


$dt    		= getData($where, $page, $perpage);
$sum		= getSummary($where);
$totalrows	= $dt['JML_ROWS'];

if ($kecamatan == "") {
	echo showTable();
} else {
	echo showTable(1, $nama);
}
