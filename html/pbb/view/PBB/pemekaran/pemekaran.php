<?php
$uid     = $data->uid;
$uname   = $data->uname;
$param   = $_REQUEST['param'];

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$paramDB = '&host=' . $appConfig['ADMIN_DBHOST'] . '&port=' . $appConfig['ADMIN_DBPORT'] . '&user=' . $appConfig['ADMIN_DBUSER'] . '&pass=' . $appConfig['ADMIN_DBPWD'] . '&dbgw=' . $appConfig['ADMIN_GW_DBNAME'] . '&dbsw=' . $appConfig['ADMIN_SW_DBNAME'];
$paramUname = '&uname=' . $uname;

$idexec = null;
if (isset($_REQUEST['idexec'])) {
	$idexec = $_REQUEST['idexec'];
	include('showtable.php');
} else {
	// CONFIG 
	$sql     = "SELECT CTR_AC_KEY `name`, CTR_AC_VALUE `value` 
						FROM  `central_app_config` 
						WHERE `CTR_AC_AID` = 'aPBB' AND CTR_AC_KEY IN ('KODE_KOTA','KODE_PROVINSI','NAMA_KOTA','NAMA_PROVINSI')";
	$result  = mysqli_query($DBLink, $sql);
	while ($row = mysqli_fetch_array($result)) {
		if ($row['name'] == 'KODE_KOTA')          $kd_kota = $row['value'];
		else if ($row['name'] == 'KODE_PROVINSI') $kd_prov = $row['value'];
		else if ($row['name'] == 'NAMA_KOTA')     $nm_kota = $row['value'];
		else if ($row['name'] == 'NAMA_PROVINSI') $nm_prov = $row['value'];
	}

	// LOAD KECAMATAN
	$arrKec  = " var arrKec = new Array(); ";
	$sql     = " SELECT CPC_TKC_ID `kode`, CPC_TKC_KECAMATAN `nama` 
						 FROM cppmod_tax_kecamatan 
						 WHERE CPC_TKC_KKID = '$kd_kota' ORDER BY nama ";
	$result  = mysqli_query($DBLink, $sql);
	while ($row = mysqli_fetch_array($result)) {
		$digit3 = " - " . substr($row['kode'], 4, 3);
		$arrKec .= " arrKec.push({ kode : '" . $row['kode'] . "', nama: '" . $row['nama'] . $digit3 . "'});";
	}

	$sqlTable = "((SELECT CPM_NOP FROM cppmod_pbb_sppt) UNION
					  (SELECT CPM_NOP FROM cppmod_pbb_sppt_final) UNION
					  (SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan)) ";

	// LOAD KELURAHAN
	$arrKel  = " var arrKel = new Array(); ";
	$sql     = " SELECT A.CPC_TKL_ID AS `kode`, 
							   A.CPC_TKL_KCID AS `kd_kec`, 
							   A.CPC_TKL_KELURAHAN AS `nama`, 
							   COUNT(AA.NOP) `jumlah` 
						 FROM `cppmod_tax_kelurahan` A LEFT JOIN
							  (SELECT DISTINCT LEFT(B.CPM_NOP,10) NOP FROM $sqlTable B WHERE B.CPM_NOP LIKE '$kd_kota%') AA ON A.CPC_TKL_ID = AA.NOP
						 WHERE A.`CPC_TKL_KCID` LIKE '$kd_kota%' 
						 GROUP BY A.CPC_TKL_ID ";
	$result  = mysqli_query($DBLink, $sql);
	while ($row = mysqli_fetch_array($result)) {
		$digit3 = " - " . substr($row['kode'], 7, 3);

		$arrKel .= "arrKel.push({ kode: '" . $row['kode'] . "', nama: '" . $row['nama'] . $digit3 . "', kd_kec : '" . $row['kd_kec'] . "', jumlah : '" . $row['jumlah'] . "'});";
	}

	include('view.php');
}
?>

<style type="text/css">
	#load-mask,
	#load-content {
		display: none;
		position: fixed;
		height: 100%;
		width: 100%;
		top: 0;
		left: 0;
	}

	#load-mask {
		background-color: #000000;
		filter: alpha(opacity=70);
		opacity: 0.7;
		z-index: 1;
	}

	#load-content {
		z-index: 2;
	}

	#loader {
		margin-right: auto;
		margin-left: auto;
		background-color: #ffffff;
		width: 100px;
		height: 100px;
		margin-top: 200px;
	}
</style>

<div id="load-content">
	<div id="loader">
		<img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
	</div>
</div>
<div id="load-mask"></div>