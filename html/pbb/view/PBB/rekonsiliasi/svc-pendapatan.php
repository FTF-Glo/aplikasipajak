<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'rekonsiliasi', '', dirname(__FILE__))) . '/';
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

error_reporting(E_ALL);
ini_set('display_errors', 1);


$myDBLink = "";

function headerPendapatan($mod)
{
	global $appConfig;

	if ($mod == 0) {
		$th = "BULAN";
		$thDetail = "";
	} else {
		$th = "TANGGAL";
		$thDetail = "<th width=\"137\" align=\"center\">DETAIL</td>";
	}
	$html = "<table cellspacing=\"0\" cellpadding=\"4\" border=\"1\">
	  <tr>
		<th width=\"117\" align=\"center\">{$th}</td>
		<th width=\"136\" align=\"center\">JUMLAH OP</td>
		<th width=\"136\" align=\"center\">POKOK</td>
		<th width=\"136\" align=\"center\">DENDA</td>
		<th width=\"137\" align=\"center\">TOTAL</td>
		" . $thDetail . "
	  </tr>
	";
	return $html;
}

// koneksi mysql
function openMysql()
{
	global $appConfig;
	$host = $appConfig['GW_DBHOST'];
	$port = isset($appConfig['GW_DBPORT']) ? $appConfig['GW_DBPORT'] : '3306';
	$user = $appConfig['GW_DBUSER'];
	$pass = $appConfig['GW_DBPWD'];
	$dbname = $appConfig['GW_DBNAME'];
	$myDBLink = mysqli_connect($host, $user, $pass, $dbname, $port);
	if (!$myDBLink) {
		echo mysqli_error($myDBLink);
		//exit();
	}
	//$database = mysql_select_db($dbname,$myDBLink);
	return $myDBLink;
}

function closeMysql($con)
{
	mysqli_close($con);
}

function getData($mod)
{
	global $thn, $bulan;

	$arrBulan = array(1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember");
	if ($mod == 0) {
		$nama_bulan =  $arrBulan;
		$c 			= count($nama_bulan);
	} else {
		$c 			= jumlah_hari($bulan, $thn);
		for ($x = 0; $x <= $c; $x++) {
			$nama_bulan[] = $x;
		}
	}

	$data 	= array();
	for ($i = 0; $i < $c; $i++) {
		$data[$i]["nama_bulan"] = $nama_bulan[$i + 1];
		$bulan					= sprintf("%02d", $bulan);
		$tgl					= sprintf("%02d", ($i + 1));
		$pendapatan				= getPendapatan($tgl, $bulan, $thn);

		$data[$i]["JML_OP"] 	= $pendapatan['JML_OP'];
		$data[$i]["POKOK"] 		= $pendapatan['POKOK'];
		$data[$i]["DENDA"] 		= $pendapatan['DENDA'];
		$data[$i]["TOTAL"] 		= $pendapatan['TOTAL'];
	}

	return $data;
}

function jumlah_hari($bulan = 0, $tahun = 0)
{
	$bulan = $bulan > 0 ? $bulan : date("m");
	$tahun = $tahun > 0 ? $tahun : date("Y");

	switch ($bulan) {
		case 1:
		case 3:
		case 5:
		case 7:
		case 8:
		case 10:
		case 12:
			return 31;
			break;
		case 4:
		case 6:
		case 9:
		case 11:
			return 30;
			break;
		case 2:
			return $tahun % 4 == 0 ? 29 : 28;
			break;
	}
}

function showTable()
{
	global $bulan, $thn;

	$dt 		= getData($bulan);
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$a = 1;
	$html 		.= headerPendapatan($bulan);
	$summary = array('name' => 'TOTAL', 'SUM_JML_OP' => 0, 'SUM_POKOK' => 0, 'SUM_DENDA' => 0, 'GRAND_TOTAL' => 0);
	for ($i = 0; $i < $c; $i++) {

		$detail = ($dt[$i]['JML_OP'] > 0 ? "<td class=\"linkLihatDetail\" align=\"center\" id=\"" . $dt[$i]['nama_bulan'] . "+" . $bulan . "+" . $thn . "\">Lihat Detail</td>" : "<td align=\"center\">-</td>");
		$html .= " <tr>
	            <td>" . $dt[$i]['nama_bulan'] . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['JML_OP'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['POKOK'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['DENDA'], 0, ',', '.') . "</td>
	            <td align=\"right\">" . number_format($dt[$i]['TOTAL'], 0, ',', '.') . "</td>
				" . ($c > 12 ? $detail : "") . "
	          </tr>";

		$summary['SUM_JML_OP']	+= $dt[$i]['JML_OP'];
		$summary['SUM_POKOK'] 	+= $dt[$i]['POKOK'];
		$summary['SUM_DENDA'] 	+= $dt[$i]['DENDA'];
		$summary['GRAND_TOTAL']	+= $dt[$i]['TOTAL'];

		$a++;
	}
	$html .= " <tr>
            <td>" . $summary['name'] . "</td>
            <td align=\"right\">" . number_format($summary['SUM_JML_OP'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['SUM_POKOK'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['SUM_DENDA'], 0, ',', '.') . "</td>
            <td align=\"right\">" . number_format($summary['GRAND_TOTAL'], 0, ',', '.') . "</td>
			" . ($c > 12 ? $detail : "") . "
          </tr>";

	return $html . "</table>";
}

function getPendapatan($tgl, $bulan, $thn)
{
	global $myDBLink, $appConfig;

	$myDBLink 		= openMysql();
	$return			= array();
	$db_gw 			= $appConfig['GW_DBNAME'];
	$settle_date	= '';

	if ($bulan == 0)
		$bulan = "";

	if ($thn == '' && $bulan == '') {
		$settle_date = "%" . $bulan . $tgl . "%";
	} else if ($thn == '' && $bulan != '') {
		$settle_date = "%" . $bulan . $tgl;
	} else {
		$settle_date = $thn . $bulan . $tgl . "%";
	}

	$return["PENDAPATAN"] = 0;

	$query = "SELECT
				COUNT(NOP) AS JML_OP,
				SUM(SPPT_PBB_HARUS_DIBAYAR) AS POKOK,
				SUM(PBB_DENDA) AS DENDA,
				SUM(PBB_TOTAL_BAYAR) AS TOTAL
			FROM
				PBB_SPPT
			WHERE
				PAYMENT_FLAG = '1'
			AND PAYMENT_SETTLEMENT_DATE LIKE '$settle_date' ";
	//echo $query.'<br/>';exit;
	$res = mysqli_query($myDBLink, $query);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	while ($row = mysqli_fetch_assoc($res)) {
		//print_r($row);
		$return["JML_OP"]	= ($row["JML_OP"] != "") ? $row["JML_OP"] : 0;
		$return["POKOK"]	= ($row["POKOK"] != "") ? $row["POKOK"] : 0;
		$return["DENDA"]	= ($row["DENDA"] != "") ? $row["DENDA"] : 0;
		$return["TOTAL"]	= ($row["TOTAL"] != "") ? $row["TOTAL"] : 0;
	}
	closeMysql($myDBLink);
	return $return;
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;
$uid = $q->uid;

//echo $s;

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$kd 				= $appConfig['KODE_KOTA'];
$kab  				= @isset($_REQUEST['kb']) ? $_REQUEST['kb'] : $appConfig['KODE_KOTA'];
$thn 				= @isset($_REQUEST['th']) ? $_REQUEST['th'] : "";
$bulan 				= @isset($_REQUEST['bln']) ? $_REQUEST['bln'] : "";

// print_r($_REQUEST);exit;

echo showTable();
?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">
	var app = "<?php echo $a; ?>";
	$(".linkLihatDetail").click(function() {
		var id = $(this).attr("id");
		var v_id = id.split("+");

		tgl = v_id[0];
		bln = v_id[1];
		thn = v_id[2];

		$.ajax({
			type: "POST",
			url: "./view/PBB/rekonsiliasi/svc-pendapatan-detail.php",
			data: "app=" + app + "&tgl=" + tgl + "&bln=" + bln + "&thn=" + thn,
			success: function(data) {
				console.log(data)
				$("#content1").fadeIn(500);
				$("#content2").fadeIn(500);
				$("#showTable").html(data);
				$("#tgl").attr("value", v_id[0]);
				$("#bln").attr("value", v_id[1]);
				$("#thn").attr("value", v_id[2]);
			},
			error: function(data) {
				$("#content1").html("Loading...");
				console.log(data)
			}
		});
	});
	$("#closedcontent").click(function() {
		$("#content1").fadeOut(500);
		$("#content2").fadeOut(500);
	});

	function toExcelDetail() {
		var thn = $("#thn").val();
		var bln = $("#bln").val();
		var tgl = $("#tgl").val();

		window.open("view/PBB/rekonsiliasi/svc-toexcel-pendapatan-detail.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>&app=" + app + "&thn=" + thn + "&bln=" + bln + "&tgl=" + tgl);
	}
</script>
<div id="content2">
	<div align="center" id="detail" style="width: 1024px; height: auto; margin: auto; margin-top: 120px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
		<div style="width: 1024px; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto; vertical-align: middle; align:left">
			<div id="closedcontent" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
		</div>
		<div id="showTable" style="margin: 10px;margin-left: 10px;">
		</div>
		<div align="right" style="margin: 10px;margin-left: 10px;">
			<input type="hidden" name="tgl" id="tgl" />
			<input type="hidden" name="bln" id="bln" />
			<input type="hidden" name="thn" id="thn" />
			<input type="button" name="buttonToExcel" id="buttonToExcel" value="Ekspor ke xls" onClick="toExcelDetail()" />
		</div>
	</div>
</div>
<div id="content1"></div>
<style type="text/css">
	.linkLihatDetail:hover {
		color: #ce7b00;
	}

	.linkLihatDetail {
		text-decoration: underline;
		cursor: pointer;
	}

	#content1,
	#content2 {
		display: none;
		position: fixed;
		height: 100%;
		width: 100%;
		top: 0;
		left: 0;
	}

	#content1 {
		background-color: #000000;
		filter: alpha(opacity=70);
		opacity: 0.7;
		z-index: 1;
	}

	#content2 {
		z-index: 2;
	}

	#closedcontent {
		cursor: pointer;
	}
</style>