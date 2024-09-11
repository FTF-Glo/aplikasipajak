<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'monitoring', '', dirname(__FILE__))) . '/';
//error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

require_once($sRootPath . "inc/phpexcel/Classes/PHPExcel.php");
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

//error_reporting(E_ALL);
ini_set('display_errors', 1);
$tahun = $_POST['tahun'];
$buku = @isset($_POST['buku']) ? $_POST['buku'] : 0;

function headerMonitoringRealisasi() {
    global $appConfig;
   
    $html = "<table class=\"table table-bordered table-striped table-hover\" style=\"width:800px\">	  
	  <tr>
		<th rowspan=2 width=10>NO</th>
		<th rowspan=2>DESA</th>
		<th rowspan=2>KECAMATAN</th>
		<th colspan=2>NILAI NJOP BUMI<br>PERMETER (m<sup>2</sup>)</th>		
		<th colspan=2>NILAI NJOP BANGUNAN<br>PERMETER (m<sup>2</sup>)</th>		
	  </tr>
	  <tr>
		<th width=80>TERENDAH</th>
		<th width=100>TERTINGGI</th>
		<th width=80>TERENDAH</th>
		<th width=100>TERTINGGI</th>
	  </tr>	  
	";
    return $html;
}

function getData() {
	global $DBLink,$appConfig,$tahun,$buku;
	
	$thnTagihan = $appConfig['tahun_tagihan'];
	$return=array();
	$table = ($tahun!=$thnTagihan) ? "cppmod_pbb_sppt_cetak_".$tahun : "cppmod_pbb_sppt_current";
	//$return["RESULT"]=0;
	// $default = array("JUMLAH_AKTIF"=>0, "JUMLAH_FASUM"=>0, "BUMI_AKTIF"=>0, "BUMI_FASUM"=>0, "BANGUNAN_AKTIF"=>0, "BANGUNAN_FASUM"=>0, "NJOP_AKTIF"=>0, "NJOP_FASUM"=>0);
	
	$qBuku = "";
	if ($buku != 0) {
		switch ($buku) {
			case 1:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) ";
				break;
			case 12:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
				break;
			case 123:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 1234:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 12345:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 2:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) ";
				break;
			case 23:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 234:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 2345:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 3:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) ";
				break;
			case 34:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 345:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 4:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) ";
				break;
			case 45:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
			case 5:
				$qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) ";
				break;
		}
	}
	
	
	$queryKecamatan = "select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,sum(tinggi_bumi) tinggi_bumi,sum(rendah_bumi) rendah_bumi,sum(tinggi_bangunan) tinggi_bangunan,sum(rendah_bangunan) rendah_bangunan from (
		select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,max(OP_NJOP_BUMI/OP_LUAS_BUMI) tinggi_bumi, 0 rendah_bumi, 0 tinggi_bangunan, 0 rendah_bangunan 
		from {$table}
		where OP_NJOP_BUMI is not null and OP_NJOP_BUMI!=0 {$qBuku}
		group by OP_KELURAHAN_KODE,OP_KELURAHAN
		union all
		select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi,min(OP_NJOP_BUMI/OP_LUAS_BUMI) rendah_bumi, 0 tinggi_bangunan, 0 rendah_bangunan
		from {$table}
		where OP_NJOP_BUMI is not null and OP_NJOP_BUMI!=0 {$qBuku}
		group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
		union all
		select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi, 0 rendah_bumi, max(OP_NJOP_BANGUNAN/OP_LUAS_BANGUNAN) tinggi_bangunan, 0 rendah_bangunan 
		from {$table}
		where OP_LUAS_BANGUNAN!=0 and OP_NJOP_BANGUNAN!=0 {$qBuku}
		group by OP_KELURAHAN_KODE,OP_KELURAHAN
		union all
		select OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN,0 tinggi_bumi,0 rendah_bumi, 0 tinggi_bangunan, min(OP_NJOP_BANGUNAN/OP_LUAS_BANGUNAN) rendah_bangunan
		from {$table}
		where OP_LUAS_BANGUNAN!=0 and OP_NJOP_BANGUNAN!=0 {$qBuku}
		group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
		) a 
		group by OP_KELURAHAN_KODE,OP_KELURAHAN,OP_KECAMATAN
		order by OP_KECAMATAN,OP_KELURAHAN";
		
	$res = mysqli_query($DBLink, $queryKecamatan);
	if ($res === false) {
		//echo mysqli_error($DBLink);
		echo "Data SPPT untuk tahun {$tahun} tidak tersedia.";
		exit();
	}
	
	while ($row = mysqli_fetch_object($res)) {
		$return[] = $row;		
	}
        
	closeMysql($DBLink);
	return $return;
}

function closeMysql($con) {
    mysqli_close($con);
}

function showTable ($mod=0,$nama="") {
	$html = headerMonitoringRealisasi();
        
        $data = getData();
        $i = 1;
		if(count($data)>0){
			foreach ($data as $data) {
				$html .= "<tr class=tright>
						<td>".$i."</td>
						<td class=tleft>".$data->OP_KELURAHAN."</td>
						<td class=tleft>".$data->OP_KECAMATAN."</td>
						<td>".number_format($data->rendah_bumi,0,',','.')."</td>
						<td>".number_format($data->tinggi_bumi,0,',','.')."</td>
						<td>".number_format($data->rendah_bangunan,0,',','.')."</td>
						<td>".number_format($data->tinggi_bangunan,0,',','.')."</td>
				</tr>";
				$i++;
			}
		}
		else {
			$html .= "<tr><th class=tcenter colspan=7>Tidak ada data untuk ditampilkan</th></tr>";
		}
        $html .= "</table>";
	return $html;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//echo $s;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 	= $User->GetAppConfig($a);

echo showTable();


?>
