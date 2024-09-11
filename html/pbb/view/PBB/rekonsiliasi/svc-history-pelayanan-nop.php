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
$arrType = array(
	1 => "OP Baru",
	2 => "Pemecahan",
	3 => "Penggabungan",
	4 => "Mutasi",
	5 => "Perubahan",
	6 => "Pembatalan",
	7 => "Salinan",
	8 => "Penghapusan",
	9 => "Pengurangan",
	10 => "Keberatan"
);

function headerPendapatan()
{
	$html = "
	<div class=\"row\">
		<div class=\"col-md-12\">
			<div class=\"table-responsive\">
				<table class=\"table table-hover\">
					<tr>
						<th width=\"50\" align=\"center\">NO</td>
						<th width=\"117\" align=\"center\">TANGGAL</td>
						<th width=\"136\" align=\"center\">NOMOR PELAYANAN</td>
						<th width=\"136\" align=\"center\">NOP</td>
						<th width=\"136\" align=\"center\">NAMA WP</td>
						<th width=\"136\" align=\"center\">JENIS PELAYANAN</td>
					</tr>
	";
	return $html;
}

function getData($nop1, $nop2, $nop3, $nop4, $nop5, $nop6, $nop7)
{
	global $DBLink;
	/*$query = sprintf("SELECT 
	IF(CPM_OP_NUMBER IS NULL or CPM_OP_NUMBER = '', CPM_NEW_NOP, CPM_OP_NUMBER) as CPM_OP_NUMBER,
	CPM_ID,
	CPM_TYPE,
	CPM_WP_NAME,
	CPM_DATE_RECEIVE,
	CPM_DATE_APPROVER,
	CPM_DATE_VALIDATE,
	CPM_DATE_VERIFICATION
	FROM cppmod_pbb_services A LEFT JOIN cppmod_pbb_service_new_op B ON A.CPM_ID = B.CPM_NEW_SID
	WHERE (CPM_OP_NUMBER = '%s' or CPM_NEW_NOP= '%s')
	ORDER BY CPM_DATE_RECEIVE ASC, CPM_ID ASC", $nop, $nop);*/
	$query = "SELECT 
	IF(CPM_OP_NUMBER IS NULL or CPM_OP_NUMBER = '', CPM_NEW_NOP, CPM_OP_NUMBER) as CPM_OP_NUMBER,
	CPM_ID,
	CPM_TYPE,
	CPM_WP_NAME,
	CPM_DATE_RECEIVE,
	CPM_DATE_APPROVER,
	CPM_DATE_VALIDATE,
	CPM_DATE_VERIFICATION
	FROM cppmod_pbb_services A LEFT JOIN cppmod_pbb_service_new_op B ON A.CPM_ID = B.CPM_NEW_SID
	WHERE ((SUBSTR(CPM_OP_NUMBER, 1, 2) = '" . $nop1 . "' and SUBSTR(CPM_OP_NUMBER, 3, 2) = '" . $nop2 . "' and SUBSTR(CPM_OP_NUMBER, 5, 3) = '" . $nop3 . "' and SUBSTR(CPM_OP_NUMBER, 8, 3) = '" . $nop4 . "' and SUBSTR(CPM_OP_NUMBER, 11, 3) = '" . $nop5 . "' and SUBSTR(CPM_OP_NUMBER, 14, 4) = '" . $nop6 . "' and SUBSTR(CPM_OP_NUMBER, 18, 1) = '" . $nop7 . "') or (SUBSTR(CPM_NEW_NOP, 1, 2) = '" . $nop1 . "' and SUBSTR(CPM_NEW_NOP, 3, 2) = '" . $nop2 . "' and SUBSTR(CPM_NEW_NOP, 5, 3) = '" . $nop3 . "' and SUBSTR(CPM_NEW_NOP, 8, 3) = '" . $nop4 . "' and SUBSTR(CPM_NEW_NOP, 11, 3) = '" . $nop5 . "' and SUBSTR(CPM_NEW_NOP, 14, 4) = '" . $nop6 . "' and SUBSTR(CPM_NEW_NOP, 18, 1) = '" . $nop7 . "'))
	ORDER BY CPM_DATE_RECEIVE ASC, CPM_ID ASC";

	//echo $query;
	$res = mysqli_query($DBLink, $query);
	$data = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$data[] = $row;
	}
	return $data;
}


function showTable()
{
	global $nop1, $nop2, $nop3, $nop4, $nop5, $nop6, $nop7, $arrType;

	$dt 		= getData($nop1, $nop2, $nop3, $nop4, $nop5, $nop6, $nop7);
	$c 			= count($dt);
	$html 		= "<div id=\"frame-tbl-monitoring\" class=\"tbl-monitoring\">";
	$html 		.= headerPendapatan();

	$no = 1;
	foreach ($dt as $row) {
		$type = isset($arrType[$row['CPM_TYPE']]) ? $arrType[$row['CPM_TYPE']] : $row['CPM_TYPE'];

		$html .= " <tr>
		<td>" . ($no++) . "</td>
		<td align=\"right\">{$row['CPM_DATE_RECEIVE']}</td>
		<td align=\"right\">{$row['CPM_ID']}</td>
		<td align=\"right\">{$row['CPM_OP_NUMBER']}</td>
		<td align=\"right\">{$row['CPM_WP_NAME']}</td>
		<td align=\"right\">{$type}</td>
	</tr>";
	}

	return $html . "</table></div></div></div>";
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
//$nop 					= @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$nop1 				= @isset($_REQUEST['nop1']) ? $_REQUEST['nop1'] : "";
$nop2 				= @isset($_REQUEST['nop2']) ? $_REQUEST['nop2'] : "";
$nop3 				= @isset($_REQUEST['nop3']) ? $_REQUEST['nop3'] : "";
$nop4 				= @isset($_REQUEST['nop4']) ? $_REQUEST['nop4'] : "";
$nop5 				= @isset($_REQUEST['nop5']) ? $_REQUEST['nop5'] : "";
$nop6 				= @isset($_REQUEST['nop6']) ? $_REQUEST['nop6'] : "";
$nop7 				= @isset($_REQUEST['nop7']) ? $_REQUEST['nop7'] : "";

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