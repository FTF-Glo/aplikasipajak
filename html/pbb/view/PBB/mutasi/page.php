<?php

session_start();
//ini_set("display_errors", 1); error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
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


echo "<script type=\"text/javascript\" src=\"function/PBB/consol/script.js?v.0.0.0.3\"></script>";
echo "<script language=\"javascript\">$(\"input:submit, input:button\").button();</script>";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
    exit(1);
}


//mulai program

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page     = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$np     = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$srch     = @isset($_REQUEST['srch']) ? $_REQUEST['srch'] : "";
$kel     = @isset($_REQUEST['kel']) ? $_REQUEST['kel'] : "";
$jumlah = @isset($_REQUEST['jumlah']) ? $_REQUEST['jumlah'] : "";
$kec     = @isset($_REQUEST['kel']) ? substr($_REQUEST['kel'], 0, 7) : "";

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$f = $q->f;
$t = $q->t;
$s = $q->s;

//set new page
if (isset($_SESSION['stSPOP'])) {
    if ($_SESSION['stSPOP'] != $s) {
        $_SESSION['stSPOP'] = $s;
        $kel = "";
        $kec = "";
        $srch = "";
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

$defaultPage = 1;

$uid = $data->uid;
//$userArea = $dbUtils->getUserDetailPbb($uid);
$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);
$aKecamatan = $dbUtils->getKecamatan(null, array("CPC_TKC_KKID" => $appConfig['KODE_KOTA']));
$aKelurahan = $dbUtils->getKelOnKota($appConfig['KODE_KOTA']);

function displayContent($type, $status)
{
    // var_dump($status);
    global $aKecamatan, $aKelurahan, $kec, $kel, $s, $a, $appConfig;
    echo "<form method=\"post\">";
    // echo "<div class=\"ui-widget consol-main-content\">";
    // echo "<div class=\"ui-widget-content consol-main-content-inner\">";
    // echo "<table border=0 width=100%><tr><td>";
    echo "<div class=\"row\">";
    echo "<div class=\"col-md-12\" style=\"margin-bottom:10px\">";

    echo "<p style=\"margin-bottom:20px; display:flex; align-items:center;justify-content:end;\">
            <button class=\"btn btn-primary\" style=\"border-radius:8px;\" type=\"button\" data-toggle=\"collapse\" data-target=\"#collapseMutasi\" aria-expanded=\"false\" aria-controls=\"collapseMutasi\">
           Filter Data
            </button>
           </p>
        <div class=\"collapse\" id=\"collapseMutasi\">
            <div class=\"card card-body\">
                <div class=\"row\">
            
                    <div class=\"form-group col-md-3\">
						<label> Nomor/Nama/NOP </label>
						<div style=\"display: flex; align-items: center;\">
                        <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $status . "," . $status . ");\" id=\"srch-" . $status . "\" name=\"srch-" . $status . "\" placeholder=\"Nomor/Nama/NOP\" class=\"form-control\"\" style=\"flex-grow: 1; margin-right: 10px;\"/>

                        <button type=\"button\" onclick=\"setTabs(" . $status . "," . $status . ")\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-info\">Cari</button>
						</div>
					</div>

                    <div class=\"form-group col-md-3\">
                        <label>Kecamatan</label>
                            <select name=\"kec\" id=\"kec\" onchange=\"showKel(this)\" class=\"form-control\" \">
                                <option value=\"\">Kecamatan</option>";
                                foreach ($aKecamatan as $row) {
                                    $digit3 = substr($row['CPC_TKC_ID'], 4, 3);
                                    echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . " - $digit3" . "</option>";
                                }
                            echo " </select>
                    </div>
                
                    <div class=\"form-group col-md-3\">
                        <label>Kelurahan</label>
                        <div id=\"sKel" . $s . "\">
                            <select name=\"kec\" id=\"kec\" onchange=\"showKel(this)\" class=\"form-control\">
                                <option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>
                                </select>
                        </div>
                    </div>
                
                    
        
                </div>
            </div>";

    // echo "<div class=\"d-flex\"><span style=\"margin-right:10px\">Masukkan Kata Kunci Pencarian</span>
    // <input type=\"text\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $status . "," . $status . ");\" id=\"srch-" . $status . "\" name=\"srch-" . $status . "\" placeholder=\"Nomor/Nama/NOP\" size=\"60\" class=\"form-control\" style=\"width:150px;display:inline\"/>
    // <button type=\"button\" onclick=\"setTabs(" . $status . "," . $status . ")\" value=\"Cari\" id=\"btn-src\" class=\"btn btn-primary btn-orange\">Cari</button></div>";

    // echo "</div>";


    // echo "<div class=\"col-md-12\" style=\"display:flex;margin-bottom:10px\">";
    // echo "<span style=\"margin-right:10px\">Filter</span>
    // <select name=\"kec\" id=\"kec\" onchange=\"showKel(this)\" class=\"form-control\" style=\"width:150px\">";
    // echo "<option value=\"\">Kecamatan</option>";
    // foreach ($aKecamatan as $row) {
    //     $digit3 = substr($row['CPC_TKC_ID'], 4, 3);
    //     echo "<option value='" . $row['CPC_TKC_ID'] . "' " . ((isset($kec) && $kec == $row['CPC_TKC_ID']) ? "selected" : "") . ">" . $row['CPC_TKC_KECAMATAN'] . " - $digit3" . "</option>";
    // }
    // echo "</select>";

    // echo "<div id=\"sKel" . $s . "\" style=\"margin-left:5px; display:inline-block;\" >";
    // echo "    <select name=\"kel\" id=\"kel\" onchange=\"filKel(" . $s . ",this)\" class=\"form-control\" style=\"width:150px\">";
    // echo "        <option value=\"\">" . $appConfig['LABEL_KELURAHAN'] . "</option>";
    // echo "    </select>";
    // echo "</div>";
    // echo "</div>";

    // echo "</td><td align=\"right\">";
    // echo "</td></tr></table>";
    echo "</div>"; // tutup row
    if ($status == 1 && $type) {
        echo "<input type=\"submit\" value=\"Kirim ke Verifikasi\" class=\"btn btn-primary btn-orange\" name=\"btn-kirim\"/ onClick=\"return confirm('Anda yakin akan mengirim data ini ke verifikasi?')\" style=\"margin-bottom:10px\">";
    } else if ($status == 4 && $type) {
        echo "<input type=\"button\" value=\"Kirim Notifikasi\" class=\"btn btn-primary btn-orange\" name=\"btn-kirim-notifikasi\"/ onClick=\"return actionSendNotification('" . $PenilaianParam . "', '" . $type . "', '" . $a . "');\">";
    }
    echo "<div class=\"row\">";
    echo "<div class=\"col-md-12\">";
    echo "<div class=\"table-responsive\">";
    echo "<table class=\"table table-bordered\">";
    echo "<tr>";
    if (($status == 1 || $status == 4) && $type) {
        echo "<td width=\"20\" class=\"tdheader\"><input name=\"all-check-button\" id=\"all-check-button\" type=\"checkbox\" \"/></td>";
    } else {
        echo "<td width=\"20\" class=\"tdheader\">&nbsp;</td>";
    }
    echo createHeader($type, $status);
    echo "</tr>";
    echo printData($type, $status);
    echo "</table>";
    // echo "</div>";
    echo "</div>";
    /*
      echo "<div class=\"ui-widget-header consol-main-content-footer\"> Data terakhir yang ditampilkan sebanyak :";
      echo "<select id=\"perItems\">";
      echo "<option value=\"10\">10</option>";
      echo "<option value=\"25\">25</option>";
      echo "<option value=\"50\">50</option>";
      echo "<option value=\"75\">75</option>";
      echo "<option value=\"100\">100</option>";
      echo "<option value=\"150\">150</option>";
      echo "</select>";
     */
    // echo "<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\">";
    // echo "</div>";
    echo "<div style=\"float:right\">" . paging() . "</div>";
    // echo "</div>";
    echo "</div>"; //tutup col
    echo "</div>"; //tutup row
    echo "</form>";
}

function createHeader($type, $status)
{
    global $appConfig;
    $hBasic = "<td class=\"tdheader\"> Nomor </td>
        <td class=\"tdheader\"> Nama WP </td>
	<td class=\"tdheader\"> Kecamatan </td>
	<td class=\"tdheader\"> " . $appConfig['LABEL_KELURAHAN'] . " </td>
	<td class=\"tdheader\"> NOP </td>
	<td class=\"tdheader\"> Tanggal Terima </td>
	<td class=\"tdheader\"> Penerima </td>";
    $hJenis = "<td class=\"tdheader\"> Jenis Berkas </td>";
    $hProses = "<td class=\"tdheader\"> Status Verifikasi </td>";
    $hTolak = "<td class=\"tdheader\"> Ditolak di </td>
        <td class=\"tdheader\"> Alasan </td>";

    if ($type) {
        $header = $hBasic;
    } else {
        $header = $hJenis .= $hBasic;
    }

    switch ($status) {
        case 23:
            $header .= $hProses;
            break;
        case 56:
            $header .= $hTolak;
        default:
            break;
    }

    return $header;
}

function printData($type, $status)
{

    $HTML = "";
    $aData = getData($type, $status);

    $i = 0;
    if (count($aData) > 0) {
        foreach ($aData as $data) {
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "<tr>";
            if (($status == 1 || $status == 4) && $type) {
                $HTML .= "<td width=\"20\" class=\"" . $class . "\" align=\"center\"><input name=\"check-all[]\" class=\"check-all\" type=\"checkbox\" value=\"" . $data['CPM_ID'] . "\" /></td>";
            } else {
                $HTML .= "<td width=\"20\" class=\"" . $class . "\" align=\"center\">&nbsp;</td>";
            }
            $HTML .= parseData($data, $type, $status, $class);
            $HTML .= "</tr>";
            $i++;
        }
    }
    return $HTML;
}

function getData($type, $status)
{
    global $dbServices, $totalrows, $perpage, $appConfig, $page, $srch, $kel;
    $data;

    $filter = array();
    $filter['CPM_TYPE'] = $type;
    switch ($status) {
        case 23:
            $filter['CPM_STATUS'][] = 2;
            break;
        case 33:
            $filter['CPM_STATUS'][] = 3;
            break;
        case 56:
            $filter['CPM_STATUS'][] = 5;
            $filter['CPM_STATUS'][] = 6;
            break;
        default:
            $filter['CPM_STATUS'][] = $status;
            break;
    }

    $qWhere = "";
    if ($srch != "") {
        $qWhere = "(CPM_ID LIKE '%$srch%' OR CPM_WP_NAME LIKE '%$srch%' OR CPM_OP_NUMBER LIKE '%$srch%')";
    }
    if ($kel) $filter['CPM_OP_KELURAHAN'] = $kel;
    $perpage = $appConfig['ITEM_PER_PAGE'];
    $data = $dbServices->getMutasi("", $filter, $qWhere, $perpage, $page);
    $totalrows = $dbServices->totalrows;

    // print_r($data); exit;

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

function parseData($data, $type, $status, $class)
{
    global $arConfig, $a, $m, $f;

    $arrVer = array(
        2 => "Verifikasi",
        3 => "Persetujuan",
        5 => "Verifikasi",
        6 => "Persetujuan"
    );
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

    if ($data['CPM_TYPE'] == 3) {
        $params = "a=$a&m=$m&f=" . $arConfig['id_penggabungan_form'] . "&svcid=" . $data['CPM_ID'];
    } elseif ($data['CPM_TYPE'] == 4) {
        $params = "a=$a&m=$m&f=" . $arConfig['id_mutasi_form'] . "&svcid=" . $data['CPM_ID'];
    } elseif ($data['CPM_TYPE'] == 5) {
        $params = "a=$a&m=$m&f=" . $arConfig['id_perubahan_form'] . "&svcid=" . $data['CPM_ID'];
    } elseif ($data['CPM_TYPE'] == 7) {
        $params = "a=$a&m=$m&f=" . $arConfig['id_salinan_form'] . "&svcid=" . $data['CPM_ID'];
    } elseif ($data['CPM_TYPE'] == 8) {
        $params = "a=$a&m=$m&f=" . $arConfig['id_penghapusan_form'] . "&svcid=" . $data['CPM_ID'];
    }
    if ($status == 23 || $status == 5 || $status == 6 || ($status == 3 && $arConfig['usertype'] == "verifikator") || $status == 4) {
        $params .= "&nobutton=true";
    }

    if ($status == 23 || $status == 33 || $status == 5 || $status == 6 || $status == 3 || $status == 4) {
        $params .= "&readonly=true";
    }

    $dBasic = "<td class=\"$class\"> <a href='main.php?param=" . base64_encode($params) . "'>" . $data['CPM_ID'] . "</a> </td>
        <td class=\"$class\"> " . $data['CPM_WP_NAME'] . "</td>
	<td class=\"$class\"> " . kecShow($data['CPM_OP_KECAMATAN']) . " </td>
        <td class=\"$class\"> " . kelShow($data['CPM_OP_KELURAHAN']) . " </td>
        <td class=\"$class\" align=\"center\"> " . $data['CPM_OP_NUMBER'] . " </td>
        <td class=\"$class\" align=\"center\"> " . substr($data['CPM_DATE_RECEIVE'], 8, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 5, 2) . "-" . substr($data['CPM_DATE_RECEIVE'], 0, 4) . " </td>
        <td class=\"$class\"> " . $data['CPM_RECEIVER'] . " </td>";
    $dJenis = "<td class=\"$class\"> " . $arrType[$data['CPM_TYPE']] . " </td>";
    $dProses = "<td class=\"$class\"> " . (isset($arrVer[$data['CPM_STATUS']]) ? $arrVer[$data['CPM_STATUS']] : '') . " </td>";
    $dTolak = "<td class=\"$class\"> " . (isset($arrVer[$data['CPM_STATUS']]) ? $arrVer[$data['CPM_STATUS']] : '') . " </td>
               <td class=\"$class\"> " . ((strlen($data['CPM_REFUSAL_REASON']) > 25) ? "<label class=\"tipclass\" title=\"" . $data['CPM_REFUSAL_REASON'] . "\">" . substr($data['CPM_REFUSAL_REASON'], 0, 25) . "...</label>" : $data['CPM_REFUSAL_REASON']) . " </td>";

    if ($type) {
        $parse = $dBasic;
    } else {
        $parse = $dJenis .= $dBasic;
    }

    switch ($status) {
        case 23:
            $parse .= $dProses;
            break;
        case 56:
            $parse .= $dTolak;
            break;
        default:
            break;
    }

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

<script type="text/javascript">
    $(document).ready(function() {
        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
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

    // function showKel2(val) {
    //     var s = <?php echo $s ?>;
    //     <?php foreach ($aKecamatan as $row) { ?>
    //         if(val=="<?php echo $row['CPC_TKC_ID']; ?>"){
    //             document.getElementById('sKel'+s).innerHTML="<?php
                                                                //             echo "<select name='kel' id='kel' onchange='filKel(".$s.",this);'><option value=''>".$appConfig['LABEL_KELURAHAN']."</option>";
                                                                //             foreach($aKelurahan as $row2){
                                                                //                 if($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID'] ){
                                                                //                     echo "<option value='".$row2['CPC_TKL_ID']."' ".((isset($kel) && $kel==$row2['CPC_TKL_ID']) ? "selected" : "").">".$row2['CPC_TKL_KELURAHAN']."</option>";
                                                                //                 }
                                                                //             } echo"</select>"; 
                                                                ?>";
    //         }
    //     <?php } ?>
    // }
    function showKel2(val) {
        var s = <?php echo $s ?>;
        <?php foreach ($aKecamatan as $row) { ?>
            if (val == "<?php echo $row['CPC_TKC_ID']; ?>") {
                document.getElementById('sKel' + s).innerHTML = "<?php echo "<select class='form-control' name='kel' id='kel' onchange='filKel(" . $s . ",this);'><option value=''>" . $appConfig['LABEL_KELURAHAN'] . "</option>";
                                                                    foreach ($aKelurahan as $row2) {
                                                                        if ($row['CPC_TKC_ID'] == $row2['CPC_TKL_KCID']) {
                                                                            $digit3 = substr($row2['CPC_TKL_ID'], 7, 3);
                                                                            echo "<option value='" . $row2['CPC_TKL_ID'] . "' " . ((isset($kel) && $kel == $row2['CPC_TKL_ID']) ? "selected" : "") . ">" . $row2['CPC_TKL_KELURAHAN'] . " - $digit3" . "</option>";
                                                                        }
                                                                    }
                                                                    echo "</select>"; ?>";
            }
        <?php } ?>
    }
</script>