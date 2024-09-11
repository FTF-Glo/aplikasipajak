<?php
session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
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
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
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
function showKec(){
	global $aKecamatan, $kec;
	foreach($aKecamatan as $row)  
			echo "<option value='".$row['CPC_TKC_ID']."' ".((isset($kec) && $kec==$row['CPC_TKC_ID']) ? "selected" : "").">".$row['CPC_TKC_KECAMATAN']."</option>";
}
function displayContent($selected) {
    global $isSusulan, $kec, $jumhal, $srch;

    echo "<form name=\"mainform\" method=\"post\">";
	echo "<input type=\"hidden\" name=\"kecamatan\" value=\"".$kec."\">";
    echo "<div class=\"ui-widget consol-main-content\">\n";
    echo "\t<div class=\"ui-widget-content consol-main-content-inner\">\n";
    echo "\t<table border=0 width=100%><tr><td>";
    if ($selected == 10) {
        echo "<input type=\"submit\" value=\"Finalkan\" name=\"btn-finalize\"/ onClick=\"return confirm('Anda yakin akan memfinalisasi data ini? Data akan langsung terkirim ke kelurahan')\">&nbsp;<input type=\"submit\" value=\"Hapus\" name=\"btn-delete\"/ onClick=\"return confirm('Anda yakin akan menghapus seluruh dokumen ini?')\">&nbsp;&nbsp;&nbsp;\n";
    } 
    echo "Masukan Query Pencarian <input type=\"text\" id=\"srch-".$selected."\" name=\"srch-".$selected."\" size=\"60\"/> <input type=\"button\" onclick=\"setTabs(".$selected.",".$selected.")\" value=\"Cari\" id=\"btn-src\"/>";
    echo "\t</td><td align=\"right\">";
    echo "</td></tr></table>";
    echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    echo "\t<tr>\n";
    if ($selected == 10) {
        echo "\t\t<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>\n";
    } else {
        echo "\t\t<td width=\"20\" class=\"tdheader\">&nbsp;</td>\n";
    }
	echo createHeader($selected);
    echo "\t</tr>\n";
    echo printData($selected);
    echo "</table>\n";
    echo "\t</div>\n";
	echo "\t<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">\n";		
    echo "\t\t</div>\n";
	echo "\t\t<div style=\"float:right\">".paging()."</div>\n";
    echo "\t</div>\n";
    echo "</div>\n";
    echo "</form>\n";
}

function createHeader($selected) {
    //variable header set
    $hBasic =
            "\t\t<td class=\"tdheader\"> Nomor </td> \n
		 \t\t<td class=\"tdheader\"> Nama WP </td> \n
		 \t\t<td class=\"tdheader\"> Kecamatan </td> \n
		 \t\t<td class=\"tdheader\"> Kelurahan </td> \n
		 \t\t<td class=\"tdheader\"> NOP </td> \n
		 \t\t<td class=\"tdheader\"> Tanggal Terima </td> \n
		 \t\t<td class=\"tdheader\"> Penerima </td> \n";
		 
    // $hBasic .=
    // "\t\t<td class=\"tdheader\"> NJOP </td> \n";

    /* $hTolak =
            "\t\t<td class=\"tdheader\"> Ditolak di </td> \n
		 \t\t<td class=\"tdheader\"> Alasan </td> \n";

    $hVerifikasi =
            "\t\t<td class=\"tdheader\"> Status Verifikasi </td> \n";

    $hAdv =
            "\t\t<td class=\"tdheader\"> Kec-Kel</td> \n
		 \t\t<td class=\"tdheader\"> ID Pendata </td> \n"; */

    $header = $hBasic;
	//echo $selected;
	//echo htmlentities($header); echo"<br>";
    switch ($selected) {
        case 10:
		case 11:
		case 12:
            break;
    }
	 
    //echo htmlentities($header);
    return $header;
}

function printData($selected) {
    global $isSusulan;

    $HTML = "";
    $aData = getData($selected);

    $i = 0;

    // echo "<pre>";
    // print_r ($aData);
    // echo "</pre>";
    if (count($aData) > 0)
        foreach ($aData as $data) {
			
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<tr>\n";
            if ($selected == 10 || $selected == 42) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_TRAN_ID'] . "\" /></td>\n";
            } else if (($selected == 60 && $data['FLAG'] == 2) || $selected== 70) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['NOP'] . "\" /></td>\n";
            } else if (($selected == 24 && $isSusulan)) {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_SPPT_DOC_ID'] . "\" /></td>\n";
            } else {
                $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>\n";
            }
			
            $HTML .= parseData($data, $selected, $class);

            $HTML .= "\t</tr>\n";
            $i++;
        }
    return $HTML;
}

function getData($selected) {
    global $dbServices, $srch, $arConfig, $appConfig, $data, $kec, $custom, $jumhal, $totalrows, $perpage, $page;

	//Seleksi Status dan jenis Berkas
	$filter['CPM_TYPE'][] = 9;
	$filter['CPM_TYPE'][] = 10;
    if ($selected == 10) {
        $filter['CPM_STATUS'][] = 1;
    } else if ($selected == 11) {
		$filter['CPM_STATUS'][] = 2;
		$filter['CPM_STATUS'][] = 3;
    }
	
	$perpage = $appConfig['ITEM_PER_PAGE'];
    if ($selected == 10 || $selected==11 || $selected==12){
        $data = $dbServices->get($filter,$srch,$jumhal,$perpage,$page);
		$totalrows = $dbServices->totalrows;
	} 
    return $data;
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
	
function parseData($data, $selected, $class) {
    global $arConfig, $appConfig, $a, $m;

    //menentukan jenis tampilan, form input atau view biasa
    if ($selected != 60 && $selected != 70) {
        if (($selected == 10))
            $params = "a=$a&m=$m&f=f573";
        else {
			$params = "a=$a&m=$m&f=" . $arConfig['id_view_spop'] . "&idt=" . $data['CPM_TRAN_ID'];
            if ($selected != 20 && (
                    ($arConfig["usertype"] == "consol" && ($data['CPM_STATUS'] == "0" || $data['CPM_STATUS'] == "6" || $data['CPM_STATUS'] == "7" || $data['CPM_STATUS'] == "8")) ||
                    ($arConfig["usertype"] == "kelurahan" && $data['CPM_STATUS'] == "1")
                    )) {
                $params = "a=$a&m=$m&f=" . $arConfig['id_spop'] . "&idt=" . $data['CPM_TRAN_ID'];
            }
        }
		$status = array(
            0 => "Draft",
            1 => "Verifikasi I",
            2 => "Verifikasi II",
			3 => "Verifikasi III",
            4 => "Penetapan",
            5 => "Finalisasi",
            6 => "Verifikasi I",
            7 => "Verifikasi II",
			8 => "Verifikasi III",
			9 => "Penetapan"
        );
		
        $dBasic =
                "\t\t<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params."&svcid=".$data['CPM_ID']) . "'>" . $data['CPM_ID'] . "</a> </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td> \n
		 \t\t<td class=\"$class\"> " . kecShow($data['CPM_WP_KECAMATAN']) . " </td> \n
		 \t\t<td class=\"$class\"> " . kelShow($data['CPM_WP_KELURAHAN']) . " </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_OP_NUMBER'] . " </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_DATE_RECEIVE'] . " </td> \n
		 \t\t<td class=\"$class\"> " . $data['CPM_RECEIVER'] . " </td> \n";

        $dTolak = $dBasic;
               /*  "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n
		 \t\t<td class=\"$class\"> " . ((strlen($data['CPM_TRAN_INFO']) > 25) ? "<label class=\"tipclass\" title=\"" . $data['CPM_TRAN_INFO'] . "\">" . substr($data['CPM_TRAN_INFO'], 0, 25) . "...</label>" : $data['CPM_TRAN_INFO']) . " </td> \n";
		*/       
		$dVerifikasi = $dBasic;
                /* "\t\t<td class=\"$class\"> " . $status[$data['CPM_STATUS']] . " </td> \n"; */

        $parse = $dBasic;
    }
    switch ($selected) {
        case 10:
		case 11:
		case 12:
            break;
        case 20:
        case 21:
        case 22:
		case 24:
		case 25:
            $parse .= $dAdv . $dVerifikasi;
            break;
        case 30:
            $parse .= $dTolak;
            break;
        case 31:
        case 32:
		case 35:
            $parse .= $dAdv . $dTolak;
            break;
        case 24:
        case 41:
        case 42:
		case 45:
        case 50:
            $parse .= $dAdv;
            break;
        case 60:
		case 70:
            if ($data['FLAG'] == 0)
                $sStatus = "Belum disahkan";
            else if ($data['FLAG'] == 1)
                $sStatus = "Siap diprint";
            else if ($data['FLAG'] == 2)
                $sStatus = "Sudah diprint";
            else if ($data['FLAG'] == 3)
                $sStatus = "SPPT dibatalkan";
            else
                $sStatus = "Sudah masuk daftar tagihan";

            $parse = "";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_ID'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_KECAMATAN'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_WP_KELURAHAN'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> " . $data['CPM_OP_NUMBER'] . " </td> \n";
            $parse .= "\t\t<td class=\"$class\"> $sStatus </td> \n";
            break;
    }
    return $parse;
}
function paging() {
		global $a,$m,$n,$s,$page,$np,$perpage,$defaultPage,$totalrows;
		
		$params = "a=".$a."&m=".$m;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $perpage;
		$rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
		$html .= ($row+1)." - ".($rowlast). " dari ".$totalrows;

		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $totalrows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','".$s."','1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
//mulai program
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;
$srch 	= @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$jumhal = @isset($_REQUEST['jumhal']) ? $_REQUEST['jumhal'] : "";

$q = base64_decode($q);
$q = $json->decode($q);
//echo "<pre>"; print_r($q); echo "</pre>";

$a = $q->a;
$m = $q->m;
$s = $q->s;

//set new page
if(isset($_SESSION['stSPOP'])){
    if($_SESSION['stSPOP'] != $s){
        $_SESSION['stSPOP'] = $s;
        $srch = "";
		$jumhal = 10;
		$page = 1;
		$np = 1;
        echo "<script language=\"javascript\">page=1;</script>";
    }
}else{
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

$arConfig = $User->GetModuleConfig($m);print_r ($arconfig);
$appConfig = $User->GetAppConfig($a);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbServices = new DbServices($dbSpec);
$dbUtils = new DbUtils($dbSpec);

$defaultPage = 1;

$uid = $data->uid;
$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $userArea[0]['kota']));
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });
        $(".tipclass").tooltip({
            track: false,
            delay: 0,
            showBody: " - ",
            bodyHandler: function() { 
                var value = $(this)[0].tooltipText.replace(/\n/g, '<br />');
                return value;
            },
            fade: 250,
            extraClass: "fix",
            opacity: 0 
        })
    });
</script>
<?php
displayContent($s);
?>