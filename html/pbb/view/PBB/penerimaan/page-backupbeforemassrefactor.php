<?php
session_start();
/*
ini_set('display_errors',1);
error_reporting(E_ALL);
*/
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penerimaan', '', dirname(__FILE__))) . '/';
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
	foreach ($aKecamatan as $row) {
		$digit3 = " - " . substr($row["CPC_TKC_ID"], 4, 3);

		echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . $digit3 . "</option>";
	}
}

function displayContent($selected)
{
	global $isSusulan, $kec, $kel, $jumlah, $srch, $PenilaianParam, $appConfig, $module, $m, $aKecamatan, $aKelurahan, $a, $dbUtils, $dbGwCurrent, $tahun, $uid;
	echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"" . $kel . "\">";
	echo "<div class=\"ui-widget consol-main-content\">\n";
	echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
	echo "\t<table border=0 width=100%><tr><td>";

	/** REFACTORED BY ALDES */

	$thnTagihan = $appConfig['tahun_tagihan'];
	$minThn = 2009;
	$rangeThn = range($thnTagihan, $minThn);
	// $rangeKel = !$kec ? $aKelurahan : array_filter($aKelurahan, function($v) use ($kec) { return $v['CPC_TKL_KCID'] == $kec; });

	echo '<div class="form-inline">
			<div class="form-group">
				<label for="srch-'. $selected .'">Pencarian:</label>
				<input class="form-control" type="text" onkeydown="Javascript: if (event.keyCode==13) setTabs('. $selected .', '. $selected .');" id="srch-'. $selected .'" name="srch-'. $selected .'" size="30" placeholder="NOP/Nama" value="">
			</div>
			<div class="form-group">
				<label for="tahun">Tahun:</label>
				<select class="form-control tahun'. $selected .'" name="tahun" id="tahun" onchange="setTabs('. $selected .', '. $selected .')">
				'.
					implode(PHP_EOL, array_map(function($v) use ($thnTagihan) { return '<option value="'. $v .'" '. ($v == $thnTagihan ? 'selected' : '') .'>'. $v .'</option>'; }, $rangeThn))
				.'
				</select>
			</div>
			<div class="form-group">
				<label for="kec">Kec:</label>
				<select class="form-control kec'. $selected .'" name="kec" id="kec" onchange="showKel(this)">
					<option value="">Kecamatan</option>'.
					implode(PHP_EOL, array_map(function($v) use ($kec) { return '<option value="'. $v['CPC_TKC_ID'] .'" '. ($v['CPC_TKC_ID'] == $kec ? 'selected' : '') .'>'. $v['CPC_TKC_KECAMATAN'] .' - '. substr($v["CPC_TKC_ID"], 4, 3) .'</option>'; }, $aKecamatan))
				.'</select>
			</div>
			<div class="form-group">
				<label for="kel">Kel:</label>
				<select class="form-control kel'. $selected .'" name="kel" id="kel" onchange="filKel('. $selected .', this);">
					<option value="">'. $appConfig['LABEL_KELURAHAN'] .'</option>'.
					implode(PHP_EOL, array_map(function($v) use ($kec, $kel) { return '<option value="'. $v['CPC_TKL_ID'] .'" data-kcid="'. $v['CPC_TKL_KCID'] .'" class="'. ($kec != $v['CPC_TKL_KCID'] ? 'hidden' : '') .'" '. ($v['CPC_TKL_ID'] == $kel ? 'selected' : '') .'>'. $v['CPC_TKL_KELURAHAN'] .' - '. substr($v["CPC_TKL_ID"], 7, 3) .'</option>'; }, $aKelurahan))
				.'</select>
			</div>
			<button type="submit" class="btn btn-primary btn-green">Cari</button>
		</div>';
	
	// echo "Masukan Kata Kunci Pencarian <input class='form-control' type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $selected . "," . $selected . ");\" id=\"srch-" . $selected . "\" name=\"srch-" . $selected . "\" size=\"30\" placeholder=\"NOP/Nama\" value=\"" . $srch . "\"/> <input class='btn btn-primary btn-green' type=\"button\" onclick=\"setTabs(" . $selected . "," . $selected . ")\" value=\"Cari\" id=\"btn-src\"/>";
	// echo "&nbsp;&nbsp;Tahun : <select class='form-control' name=\"tahun\" id=\"tahun\" onchange=\"setTabs(" . $selected . "," . $selected . ")\">";
	// $sql = "SELECT REPLACE(table_name,'cppmod_pbb_sppt_cetak_','') as `table` 
	// 			FROM information_schema.tables 
	// 			WHERE `table_name` LIKE 'cppmod_pbb_sppt_cetak%' AND table_schema = '" . $appConfig['ADMIN_SW_DBNAME'] . "' ORDER BY 1 DESC";
	// // echo $sql;
	// $result = mysqli_query($DBLink, $sql);
	// echo "<option value='" . date('Y') . "'>" . date('Y') . "</option>";
	// while ($r = mysqli_fetch_array($result)) {
	// 	if ($r[0] == $tahun) $selected70 = 'selected';
	// 	else $selected70 = '';

	// 	echo "<option value='$r[0]' $selected70>$r[0]</option>";
	// }
	// echo "</select>";


	// echo "&nbsp;&nbsp;Filter <select class='form-control' name=\"kec\" id=\"kec\" onchange=\"showKel(this)\">";
	// echo "<option value=\"\">Kecamatan</option>";

	// foreach ($aKecamatan as $row) {
	// 	$digit3 = " - " . substr($row["CPC_TKC_ID"], 4, 3);

	// 	echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . $digit3 . "</option>";
	// }
	// echo "</select>";
	// echo "<div id=\"sKel" . $selected . "\">";
	// echo "<select class='form-control' name=\"kel\" id=\"kel\" onchange=\"filKel(" . $selected . ",this)\">";
	// echo "<option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>";

	// if ($kel) {
	// 	foreach ($aKecamatan as $row) {
	// 		if ($kec == $row['CPC_TKC_ID']) {
	// 			foreach ($aKelurahan as $row2) {
	// 				if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
	// 					$digit3 = " - " . substr($row2["CPC_TKL_ID"], 7, 3);

	// 					echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . $digit3 . "</option>";
	// 				}
	// 			}
	// 		}
	// 	}
	// }

	// echo "</select>";
	// echo "</div>";

	echo "</td><td align=\"right\">";
	echo "</td></tr></table>";
	// echo "<div class=\"table-responsive\">";
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\" class=\"table table-bordered\">\n";
	echo "\t<tr>\n";
	echo "\t\t<td width=\"20\" class=\"tdheader\">&nbsp;</td>\n";

	echo createHeader($selected);
	echo "\t</tr>\n";
	echo printData($selected);
	echo "</table>\n";
	echo "\t</div>\n";

	echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";
	echo "\t\t</div>\n";
	echo "\t\t<div style=\"float:right\">" . paging() . "</div>\n";
	echo "\t</div>\n";
	echo "</div>\n";
	echo "</form>\n";
}

function createHeader($selected)
{
	global $appConfig;

	//variable header set
	$hBasic =
		"\t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Tahun Pajak </td> \n
		 \t\t<td class=\"tdheader\"> Nama </td> \n
		 \t\t<td class=\"tdheader\"> Alamat Objek Pajak </td> \n
		 \t\t<td class=\"tdheader\"> Tanggal Penerimaan </td> \n
		 \t\t<td class=\"tdheader\"> Nama Penerima </td> \n
		 \t\t<td class=\"tdheader\"> Kontak Penerima </td> \n";
	$header = $hBasic;
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
			$HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
			$HTML .= parseData($data, $selected, $class);
			$HTML .= "\t</tr>\n";
			$i++;
		}
	return $HTML;
}

function getDetail($id = "", $filter = "", $custom = "", $jumhal, $perpage, $page, &$totalrows)
{
	global $dbSpec, $appConfig, $tahun;

	if (trim($id) != '') $filter['CPM_TRAN_ID'] = mysql_real_escape_string(trim($id));
	$hal = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;

	$tahun = ($tahun == "") ? $appConfig['tahun_tagihan'] : $tahun;
	$TABLE_SPPT = ($tahun == $appConfig['tahun_tagihan']) ? "cppmod_pbb_sppt_current" : "cppmod_pbb_sppt_cetak_{$tahun}";

	$queryCount = "SELECT COUNT(*) AS TOTAL FROM {$TABLE_SPPT} A LEFT JOIN cppmod_pbb_sppt_penerimaan B ON A.NOP = B.CPM_NOP ";
	$query = "SELECT A.NOP, A.WP_NAMA, A.SPPT_TAHUN_PAJAK, A.OP_ALAMAT, B.CPM_TANGGAL_PENERIMAAN, B.CPM_NAMA_PENERIMA, B.CPM_KONTAK_PENERIMA 
	FROM {$TABLE_SPPT} A LEFT JOIN cppmod_pbb_sppt_penerimaan B ON A.NOP = B.CPM_NOP ";

	$whereClause = " WHERE 1=1 ";

	if (count($filter) > 0) {
		$whereClause .= "AND ";
		$last_key = end(array_keys($filter));

		foreach ($filter as $key => $value) {
			if ($key == 'CPM_TRAN_ID' || $key == 'CPM_TRAN_STATUS') {
				if (is_array($value)) {
					$tlast_key = end(array_keys($value));
					$whereClause .= " ( ";
					foreach ($value as $tkey => $val) {
						$whereClause .= " $key = '" . $val . "' ";
						if ($tkey != $tlast_key) {
							$whereClause .= " OR ";
						}
					}
					$whereClause .= " ) ";
				} else {
					$whereClause .= " $key = '$value' ";
				}
			} else {
				$whereClause .= " $key LIKE '%$value%' ";
			}
			if ($key != $last_key) $whereClause .= " AND ";
		}
	}

	if ($custom != "") {
		$whereClause .= "AND " . $custom;
	}
	$queryCount .= $whereClause;
	$dbSpec->sqlQueryRow($queryCount, $total);
	$totalrows = $total[0]['TOTAL'];

	$query .= $whereClause;
	if ($perpage) {
		$query .= " LIMIT $hal, $perpage ";
	}

	if ($dbSpec->sqlQueryRow($query, $res)) {
		return $res;
	}
}

function getData($selected)
{
	global $dbSpptTran, $dbFinalSppt, $dbGwCurrent, $dbServices, $srch, $arConfig, $appConfig, $isSusulan,
		$data, $kec, $kel, $custom, $jumlah, $totalrows, $perpage, $page, $dbUtils, $tahun;

	//Jika ada keyword pencarian
	if ($srch) {
		$custom = "(A.NOP LIKE '%$srch%' OR A.WP_NAMA LIKE '%$srch%')";
	}

	$perpage = $appConfig['ITEM_PER_PAGE'];
	$filter = null;
	if ($kel) $filter['OP_KELURAHAN_KODE'] = $kel;
	if ($tahun) $filter['SPPT_TAHUN_PAJAK'] = $tahun;
	$data = getDetail("", $filter, $custom, $jumlah, $perpage, $page, $totalrows);
	return $data;
}

function kecShow($kode)
{
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKecamatanNama($kode);
}
function kelShow($kode)
{
	global $dbSpec;
	$dbUtils = new DbUtils($dbSpec);
	return $dbUtils->getKelurahanNama($kode);
}

function parseData($data, $selected, $class)
{
	global $arConfig, $appConfig, $a, $m;

	$bSlash = "\'";
	$ktip = "'";

	$params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&nop=" . $data['NOP'] . "&tahun=" . $data['SPPT_TAHUN_PAJAK'];

	#A.NOP, A.WP_NAMA, A.SPPT_TAHUN_PAJAK, A.OP_ALAMAT, B.CPM_TANGGAL_PENERIMAAN, B.CPM_NAMA_PENERIMA, B.CPM_KONTAK_PENERIMA
	$dBasic =
		"\t\t<td class=\"$class\"><a href='main.php?param=" . base64_encode($params) . "'>" . $data['NOP'] . "</a> </td> \n
	 \t\t<td class=\"$class\"> " . $data['SPPT_TAHUN_PAJAK'] . "</td> \n
	 \t\t<td class=\"$class\"> " . $data['WP_NAMA'] . "</td> \n
	 \t\t<td class=\"$class\"> " . $data['OP_ALAMAT'] . " </td> \n
	 \t\t<td class=\"$class\"> " . $data['CPM_TANGGAL_PENERIMAAN'] . " </td> \n
	 \t\t<td class=\"$class\"> " . $data['CPM_NAMA_PENERIMA'] . " </td> \n
	 \t\t<td class=\"$class\"> " . $data['CPM_KONTAK_PENERIMA'] . " </td> \n";

	$parse = $dBasic;
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
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$tahun 	= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] : "";
$kel 	= @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$jumlah = @isset($_REQUEST['jumlah']) ? $_REQUEST['jumlah'] : "";
$kec 	= @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'], 0, 7) : "";

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
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

?>
<script type="text/javascript">
	$(document).ready(function() {
		<?php
		if ($kec != '') {
			echo "showKel2(" . $kec . ");";
		}
		?>
	});

	function showKel(x) {
		var val = x.value;
		showKel2(val);
	}

	function showKel2(val) {
		var s = <?php echo $s ?>;
		<?php foreach ($aKecamatan as $row) { ?>
			if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
				document.getElementById('sKel' + s).innerHTML = "<?php echo "<select name='kel' id='kel' onchange='filKel(" . $s . ",this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
																	foreach ($aKelurahan as $row2) {
																		if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
																			$digit3 = " - " . substr($row2["CPC_TKL_ID"], 7, 3);

																			echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . $digit3 . "</option>";
																		}
																	}
																	echo "</select>"; ?>";
			}
		<?php } ?>
	}

	/** by aldes */
	function showKel3(val) {

	}
</script>

<?php
displayContent($s);
?>
<div class="sppt_detail"></div>