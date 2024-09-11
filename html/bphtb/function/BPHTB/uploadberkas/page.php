<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
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

require_once($sRootPath . "inc/BPHTB/dbFinalSppt.php");
require_once($sRootPath . "inc/BPHTB/dbUtils.php");

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

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch = @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$srchAlmt = @isset($_REQUEST['srchAlmt']) ? $_REQUEST['srchAlmt'] : "";
$kec = @isset($_REQUEST['kec']) ? $_REQUEST['kec'] : "";
$jumlah = @isset($_REQUEST['jumlah']) ? $_REQUEST['jumlah'] : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$f = $q->f;
$t = $q->t;
$s = $q->s;

if (isset($_SESSION['stSPOP'])) {
    if ($_SESSION['stSPOP'] != $s) {
        $_SESSION['stSPOP'] = $s;
        $srch = "";
		$srchAlmt = "";
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
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbUtils = new DbUtils($dbSpec);

$defaultPage = 1;

$uid = $data->uid;
$userArea = $dbUtils->getUserDetailPbb($uid);

// echo "<pre>"; print_r($_REQUEST); echo "</pre>";
function displayContent($type, $status) {
    echo "<form method=\"post\">";
    echo "<div class=\"ui-widget consol-main-content\">\n";
    echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
    echo "\t<table border=0 width=100%><tr><td>";
    echo "<input type=\"text\" id=\"srch-" . $status . "\" name=\"srch-" . $status . "\" placeholder=\"Nama\" size=\"35\"/>&nbsp;<input type=\"text\" id=\"srchAlmt-" . $status . "\" name=\"srchAlmt-" . $status . "\" placeholder=\"Alamat\" size=\"60\"/> <input type=\"button\" onclick=\"setTabs(" . $status . "," . $status . ")\" value=\"Cari\" id=\"btn-src\"/>";
    echo "\t</td><td align=\"right\">";
    echo "</td></tr></table>";
    echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    echo "\t<tr>\n";
    echo createHeader($type, $status);
    echo "\t</tr>\n";
    echo printData($type, $status);
    echo "</table>\n";
    echo "\t</div>\n";
    echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";
    echo "\t\t</div>\n";
    echo "\t\t<div style=\"float:right\">" . paging() . "</div>\n";
    echo "</div>\n";
    echo "</form>\n";
}

function createHeader($type, $status) {
    $hBasic = "\t\t<td class=\"tdheader\"> NOP </td> \n
				\t\t<td class=\"tdheader\"> Nama WP </td> \n
				\t\t<td class=\"tdheader\"> Alamat OP </td> \n
				\t\t<td class=\"tdheader\"> Kecamatan </td> \n
				\t\t<td class=\"tdheader\"> kelurahan </td> \n";

    $header = $hBasic;

    return $header;
}

function printData($type, $status) {
    $HTML = "";
    $aData = getData($type, $status);

    $i = 0;
    if (count($aData) > 0) {
        foreach ($aData as $data) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<tr>\n";
            $HTML .= parseData($data, $type, $status, $class);
            $HTML .= "\t</tr>\n";
            $i++;
        }
    }
    return $HTML;
}

function getData($type, $status) {
    global $dbFinalSppt, $totalrows, $perpage, $appConfig, $page, $srch, $srchAlmt;
    $data;
	
	$perpage = $appConfig['ITEM_PER_PAGE'];
    $qSearch = "";
    if (($srch != "") || ($srchAlmt != "")) {
        $qSearch = $srch;
		$qSearchAlmt = $srchAlmt;
		$data = $dbFinalSppt->getFinal($perpage, $page, $qSearch, $qSearchAlmt);
		$totalrows = $dbFinalSppt->totalrows;
    }

    return $data;
}

function parseData($data, $type, $status, $class) {
    global $arConfig, $a, $m, $f;

    $dBasic = "\t\t<td class=\"$class\"> " . $data['CPM_NOP'] . "</td> \n
		\t\t<td class=\"$class\"> " . $data['CPM_WP_NAMA'] . "</td> \n
		\t\t<td class=\"$class\"> " . $data['CPM_OP_ALAMAT'] . " " . $data['CPM_OP_NOMOR'] . "</td> \n
		\t\t<td class=\"$class\"> " . $data['CPC_TKC_KECAMATAN'] . " </td> \n
        \t\t<td class=\"$class\"> " . $data['CPC_TKL_KELURAHAN'] . " </td> \n";
	
	$parse = $dBasic;

    return $parse;
}

function kecShow($kode) {
    global $dbSpec;
    $dbUtils = new DbUtils($dbSpec);
    return $dbUtils->getKecamatanNama($kode);
}

function kelShow($kode) {
    global $dbSpec;
    $dbUtils = new DbUtils($dbSpec);
    return $dbUtils->getKelurahanNama($kode);
}

function paging() {
    global $a, $m, $n, $s, $page, $np, $perpage, $defaultPage, $totalrows;

    $params = "a=" . $a . "&m=" . $m;

    $html = "<div>";
    $row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
    $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
    $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

    if ($page != 1) {
        //$page--;
        $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','0')\"><span id=\"navigator-left\"></span></a>";
    }
    if ($rowlast < $totalrows) {
        //$page++;
        $html .= "&nbsp;<a onclick=\"setPage('" . $s . "','" . $s . "','1')\"><span id=\"navigator-right\"></span></a>";
    }
    $html .= "</div>";
    return $html;
}

displayContent($t, $s);
?>