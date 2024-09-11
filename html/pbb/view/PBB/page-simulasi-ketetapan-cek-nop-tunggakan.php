<?php
session_start();
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbServices.php");

// 
//echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

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
function showKec()
{
	global $aKecamatan, $kec;
	foreach ($aKecamatan as $row)
		echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . "</option>";
}

function displayContent($selected)
{
	global $isSusulan, $tahun, $jumlah, $srch, $PenilaianParam, $appConfig, $module, $m, $aKecamatan, $aKelurahan, $a, $dbUtils, $dbGwCurrent, $tahun, $uid;
	echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . (isset($kel) ? $kel : '') . "\">";
	echo "<div class=\"row\">\n";
	echo "\t<div class=\"col-md-2\"><input type=\"button\" class=\"btn btn-primary bg-orange\" value=\"Proses Pengecekan Tunggakan\" name=\"btn-cek-tunggakan\"/ onClick=\"return actionPengecekanTunggakan();\"></div>";
	echo "<div class=\"col-md-2 text-right\" style=\"padding-top: 5px\">Masukan Kata Kunci Pencarian</div><div class=\"col-md-3\"><input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" size=\"30\" placeholder=\"NOP/Nama\" value=\"" . $srch . "\"/></div> ";
	echo "&nbsp;&nbsp;<div class=\"col-md-1 text-right\" style=\"padding-top: 5px\">Tahun</div><div class=\"col-md-2\"><select class=\"form-control\" name=\"tahun-" . $selected . "\" id=\"tahun-" . $selected . "\">";
	echo "<option value=\"\">Semua Tahun</option>";

	for ($x = date('Y') - 10; $x <= date('Y'); $x++) {
		$slcted = ($tahun == $x) ? "selected" : "";
		echo "<option value='" . $x . "' " . $slcted . " >" . $x . "</option>";
	}

	echo "</select></div><div class=\"col-md-1\"><input type=\"button\" class=\"btn btn-primary bg-blue\" onclick=\"setTabs(" . $selected . "," . $selected . ")\" value=\"Cari\" id=\"btn-src-tunggakan\"/></div></div>";
	echo "<div class=\"row\" style=\"margin-top: 20px;\"><div class=\"col-md-12\"><div class=\"table-responsive\">";
	echo "<table class=\"table table-hover\">\n";
	echo "\t<tr>\n";

	echo "\t\t<td width=\"20\" class=\"tdheader\"></td>\n";
	echo createHeader($selected);
	echo "\t</tr>\n";
	echo printData($selected);
	echo "</table>\n";
	echo "\t</div></div><div class=\"col-md-12\">\n";

	//echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";
	//echo "\t\t</div>\n";
	echo "\t\t<div style=\"float:right\">" . paging() . "</div>\n";
	echo "\t</div>\n";
	echo "</div>\n";
	echo "</form>\n";
}

function createHeader($selected)
{
	global $appConfig;
	//variable header set
	$header =
		"\t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Tahun </td> \n
		 \t\t<td class=\"tdheader\"> Tanggal Terbit </td> \n
		 \t\t<td class=\"tdheader\"> Tanggal Jatuh Tempo </td> \n
		 \t\t<td class=\"tdheader\"> Tunggakan </td> \n";

	return $header;
}

function printData($selected)
{
	global $isSusulan;

	$HTML = "";
	$aData = getData($selected);

	$i = 0;

	if (count($aData) > 0)
		foreach ($aData as $data) {

			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			$HTML .= "\t<tr>\n";

			$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"></td>\n";
			$HTML .= parseData($data, $selected, $class);

			$HTML .= "\t</tr>\n";
			$i++;
		}
	return $HTML;
}

function get_where($filter, $srch, $jumlah, $perpage, $page)
{
	global $appConfig;
	$GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']);
	if (!$GWDBLink) {
		$res3 = false;
		echo mysqli_error($GWDBLink);
	}
	//mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) or die(mysqli_error($DBLink));

	$queryCount = "SELECT count(*) as total FROM PBB_SPPT_TUNGGAKAN ";

	if (count($filter) > 0) {
		$queryCount .= "WHERE ";
		$last_key = end(array_keys($filter));

		foreach ($filter as $key => $value) {
			$queryCount .= " $key='" . mysql_real_escape_string(trim($value)) . "' ";
			if ($key != $last_key)
				$queryCount .= " AND ";
		}
	}
	if ($srch) {
		if (count($filter) > 0)
			$queryCount .= " AND ";
		else
			$queryCount .= " WHERE ";
		$queryCount .= " (A.NOP LIKE '%$srch%')";
	}

	$res = mysqli_query($GWDBLink, $queryCount);
	$data = mysqli_fetch_assoc($res);
	$jumlah = $data['total'];

	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$query = "SELECT B.* FROM PBB_SPPT_TUNGGAKAN A INNER JOIN PBB_SPPT B ON A.NOP=B.NOP AND A.SPPT_TAHUN_PAJAK=B.SPPT_TAHUN_PAJAK ";

	if (count($filter) > 0) {
		$query .= "WHERE ";
		$last_key = end(array_keys($filter));

		foreach ($filter as $key => $value) {
			$query .= " $key='" . mysql_real_escape_string(trim($value)) . "' ";
			if ($key != $last_key)
				$query .= " AND ";
		}
	}
	if ($srch) {
		if (count($filter) > 0)
			$query .= " AND ";
		else
			$query .= " WHERE ";
		$query .= " (A.NOP LIKE '%$srch%')";
	}

	if ($perpage) {
		$query .= " LIMIT $hal, $perpage ";
	}

	$res = mysqli_query($GWDBLink, $query);
	$arr = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$arr[] = $row;
	}
	return $arr;
}

function getData($selected)
{
	global $dbSpptTran, $dbFinalSppt, $dbGwCurrent, $dbServices, $srch, $arConfig, $appConfig, $isSusulan,
		$data, $kec, $kel, $custom, $jumlah, $totalrows, $perpage, $page, $dbUtils, $tahun;

	$perpage = $appConfig['ITEM_PER_PAGE'];

	$filter = array();
	if (!empty($tahun)) $filter['A.SPPT_TAHUN_PAJAK'] = $tahun;

	$data = get_where($filter, $srch, $jumlah, $perpage, $page);

	$totalrows = $jumlah;

	return $data;
}

function parseData($data, $selected, $class)
{
	global $arConfig, $appConfig, $a, $m;

	$parse =
		"\t\t<td class=\"$class\">" . $data['NOP'] . " </td> \n
	 \t\t<td class=\"$class\"> " . $data['SPPT_TAHUN_PAJAK'] . "</td> \n
	 \t\t<td class=\"$class\"> " . $data['SPPT_TANGGAL_TERBIT'] . "</td> \n
	 \t\t<td class=\"$class\"> " . $data['SPPT_TANGGAL_JATUH_TEMPO'] . "</td> \n
	 \t\t<td class=\"$class\"> " . $data['SPPT_PBB_HARUS_DIBAYAR'] . " </td> \n";

	return $parse;
}
function paging()
{
	global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage, $totalrows;

	$params = "a=" . $a . "&m=" . $m;

	$html = "<div>";
	$row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
	$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
	$html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

	if ($page != 1) {
		$html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
	}
	if ($rowlast < $totalrows) {
		$html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
	}
	$html .= "</div>";
	return $html;
}
//mulai program
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$tahun 	= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if (isset($_SESSION['stSPOP'])) {
	if ($_SESSION['stSPOP'] != $s) {
		$_SESSION['stSPOP'] = $s;
		$kel = "";
		$kec = "";
		$srch = "";
		$tahun = "";
		$jumlah = 10;
		$page = 1;
		$np = 1;
		echo "<script language=\"javascript\">page=1;</script>";
	}
} else {
	$_SESSION['stSPOP'] = $s;
}

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

/* === Get cookie data === */
$cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
$data = null;
if (strlen(trim($cData)) > 0) {
	$data = $json->decode(base64_decode($cData));
}

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbServices = new DbServices($dbSpec);

$PenilaianParam = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');

$defaultPage = 1;
$perpage = $appConfig['ITEM_PER_PAGE'];

$uid = $data->uid;
//$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

?>
<script type="text/javascript">
	function actionPengecekanTunggakan() {
		var tahun_cek_tagihan = '<?php echo ((int)$appConfig['tahun_tagihan'] - 1) ?>';
		if (confirm('Anda yakin akan melakukan cek tunggakan tahun ' + tahun_cek_tagihan + ' ?') === true) {
			$("#load-mask").css("display", "block");
			$("#load-content").fadeIn();

			var params = {};
			params.action = 'cek_tunggakan';
			params.a = '<?php echo $a ?>';
			params.m = '<?php echo $m ?>';
			params.tahun = tahun_cek_tagihan;
			$.ajax({
				url: 'inc/PBB/svc-cek-tunggakan.php',
				data: params,
				type: 'post',
				dataType: 'json',
				timeout: 180000,
				success: actionPengecekanTunggakanSuccess,
				failure: actionPengecekanTunggakanFailure
			})
		}
	}

	function actionPengecekanTunggakanSuccess(params) {
		$("#load-content").css("display", "none");
		$("#load-mask").css("display", "none");

		if (params.result == '00') {

			if (params.total > 0) {
				alert('Sebanyak ' + params.total + ' NOP tahun ' + params.tahun + ' tertunggak.');
				$('#btn-src-tunggakan').trigger('click');
			} else {
				alert('Tidak ada NOP tahun ' + params.tahun + ' yang tertunggak.');
			}
		} else {
			alert('Gagal melakukan pengecekan. Terjadi kesalahan server');
		}
	}

	function actionPengecekanTunggakanFailure(params) {
		$("#load-content").css("display", "none");
		$("#load-mask").css("display", "none");
		alert('Gagal melakukan pengecekan. Terjadi kesalahan server');
	}
</script>



<?php
displayContent($s);
?>
<div class="sppt_detail"></div>