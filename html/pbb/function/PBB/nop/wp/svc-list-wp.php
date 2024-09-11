<?php
session_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop' . DIRECTORY_SEPARATOR . 'wp', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

require_once($sRootPath . "inc/PBB/dbWajibPajak.php");

echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

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



function getDocument(&$dat)
{
    global $DBLink, $json, $a, $m, $tab, $page, $totalrows, $perpage, $arConfig, $srcKTP, $srcAlamat, $srcNama, $dbWajibPajak, $jns;

    $filter = array();
    if ($jns != 0) $filter['CPM_WP_PEKERJAAN'] = $jns;
    if ($srcAlamat != '') $filter['CPM_WP_ALAMAT'] = $srcAlamat;
    if ($srcNama != '') $filter['CPM_WP_NAMA'] = $srcNama;
    if ($srcKTP != '') $filter['CPM_WP_ID'] = $srcKTP;

    $rows         = 0;
    $totalrows     = 0;
    if ($srcAlamat != '' || $srcNama != '' || $srcKTP != '' || $jns != 0) {
        $rows = $dbWajibPajak->get($filter, $perpage, $page);
        $totalrows = $dbWajibPajak->totalrows;
    }

    $params = "a=" . $a . "&m=" . $m . "&tab=" . $tab;
    if (isset($arConfig['form_input'])) {
        $params = "a=" . $a . "&m=" . $m . "&f=" . $arConfig['form_input'] . "&tab=" . $tab;
    }

    $i = 1;
    $HTML = "";
    if (isset($rows) && $rows != 0) {
        foreach ($rows as $row) {
            $paramsEdit = "a=$a&m=$m&f=fWPedit&id=" . $row['CPM_WP_ID'];
            $class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
            $HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $i . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\"><p class=\"link_id\" id=\"" . $row['CPM_WP_ID'] . "\">" . $row['CPM_WP_ID'] . "</p></td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $row['CPM_WP_NAMA'] . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $row['CPM_WP_ALAMAT'] . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $row['CPM_WP_KECAMATAN'] . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\">" . $row['CPM_WP_KELURAHAN'] . "</td> \n";
            $HTML .= "\t\t<td class=\"" . $class . "\" align=\"center\">
                    
                        <a href=\"#\" onclick=\"cetakKartuWP('" . $row['CPM_WP_ID'] . "')\">Cetak Kartu WP</a> | 
                        <a href=\"#\" onclick=\"editWP('" . base64_encode($paramsEdit) . "')\">Edit</a>
                    </td> \n";
            $HTML .= "\t</tr></div>\n";
            $i++;
        }
    }

    if ($totalrows > 0) {
        $dat = $HTML;
    } else {
        $dat = "<tr><td colspan=\"12\" align=\"center\">Klik Cari untuk menampilkan data.</td></tr> ";
    }
    // $dat = $HTML;//<a href=\"main.php?param=" . base64_encode($paramsEdit) . "\" ><input type=\"button\" value=\"Edit\"></a>
}

function headerContent()
{
    global $find, $a, $m, $tab, $srcKTP, $srcAlamat, $srcNama;

    $HTML = "";
    $HTML .= headerContentWP();

    getDocument($dt);

    if ($dt) {
        $HTML .= $dt;
    } else {
        $HTML .= "<tr><td colspan=\"6\">Data Kosong !</td></tr> ";
    }
    $HTML .= "</table>\n";
    return $HTML;
}

function headerContentWP()
{
    global $find, $a, $m, $arConfig, $appConfig, $tab, $srcKTP, $srcAlamat, $srcNama, $jns;

    $params = "a=" . $a . "&m=" . $m;
    $startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&jnsBerkas=1") . "\">";
    if (isset($arConfig['form_input'])) {
        $startLink = "<a style='text-decoration: none;' href=\"main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input'] . "&jnsBerkas=1") . "\">";
    }
    $endLink = "</a>";

    $HTML = "<form id=\"form-laporan\" name=\"form-notaris\" method=\"post\" action=\"\" >";
    $HTML .= "
        <div class=\"row\" style=\"margin-top: 5px; margin-bottom: 20px;\">
            <div class=\"col-md-12\">
                <div class=\"row\">
                    <div class=\"col-md-1\" style=\"margin-top: 5px\">
                        Pencarian
                    </div>
                    <div class=\"col-md-2\">
                        <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcKTP-" . $tab . "\" name=\"srcKTP\" size=\"20\" value=\"" . $srcKTP . "\" placeholder=\"No KTP\"/>
                    </div>
                    <div class=\"col-md-2\">
                        <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcNama-" . $tab . "\" name=\"srcNama\" size=\"30\" value=\"" . $srcNama . "\" placeholder=\"Nama\"/>
                    </div>
                    <div class=\"col-md-3\">
                        <input type=\"text\" class=\"form-control\" onKeydown=\"Javascript: if (event.keyCode==13) setTabs(" . $tab . ");\" id=\"srcAlamat-" . $tab . "\" name=\"srcAlamat\" size=\"30\" value=\"" . $srcAlamat . "\" placeholder=\"Alamat\"/>
                    </div>
                    <div class=\"col-md-1\" style=\"margin-top: 5px;\">Jenis</div>
                    <div class=\"col-md-2\">
                        <select id=\"jns\" name=\"jns\" class=\"form-control\">
                            <option value=\"0\" " . (($jns == '0') ? "selected" : "") . ">Semua</option>
                            <option value=\"1\" " . (($jns == '1') ? "selected" : "") . ">Perseorangan</option>
                            <option value=\"2\" " . (($jns == '2') ? "selected" : "") . ">Badan</option>
                        </select>
                    </div>
                    <div class=\"col-md-1\" style=\"margin-top: 5px;\">
                        <button type=\"button\" class=\"btn btn-primary btn-orange mb5\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs(" . $tab . ")\">Cari</button>
                    </div>
                </div>
            </div>
        </div>
    </form>\n";

    $HTML .= "<div class=\"table-responsive\"><table class=\"table table-bordered\">\n";
    $HTML .= "\t<tr>\n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"50\">No</td> \n";
    $HTML .= "\t\t<td class=\"tdheader\" width=\"210\"> Nomor KTP </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Nama </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Alamat </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Kecamatan </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> " . $appConfig['LABEL_KELURAHAN'] . " </td> \n";
    $HTML .= "\t\t<td class=\"tdheader\"> Aksi </td> \n";
    $HTML .= "\t</tr>\n";

    return $HTML;
}

function displayDataWP()
{
    echo "<div class=\"row\">\n";
    echo "\t<div class=\"col-md-12\">\n";
    echo headerContent();
    echo "\t</div>\n";
    echo "\t<div style=\"float: right; margin-right: 15px;\">  \n";
    echo paging();
    echo "</div>\n";
}

function paging()
{
    global $a, $m, $n, $tab, $page, $np, $perpage, $defaultPage, $totalrows;

    $params = "a=" . $a . "&m=" . $m;
    $html = "<div style=\"font-weight: bold;\">";
    $row = (($page - 1) > 0 ? ($page - 1) : 0) * $perpage;
    $rowlast = (($page) * $perpage) < $totalrows ? ($page) * $perpage : $totalrows;
    $html .= ($row + 1) . " - " . ($rowlast) . " dari " . $totalrows;

    if ($page != 1) {
        //$page--;
        $html .= "&nbsp;<a onclick=\"setPage(" . $tab . ",'0')\"><span id=\"navigator-left\"></span></a>";
    }
    if ($rowlast < $totalrows) {
        //$page++;
        $html .= "&nbsp;<a onclick=\"setPage(" . $tab . ",'1')\"><span id=\"navigator-right\"></span></a>";
    }
    $html .= "</div>";
    return $html;
}

$q         = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page     = @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$find     = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$np     = @isset($_REQUEST['np']) ? $_REQUEST['np'] : 1;
$q = base64_decode($q);
$q = $json->decode($q);
$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab    = $q->tab;
$uname  = $q->u;
$uid    = isset($q->uid) ? $q->uid : '';

$srcAlamat  = @isset($_REQUEST['srcAlamat']) ? $_REQUEST['srcAlamat'] : '';
$srcNama    = @isset($_REQUEST['srcNama']) ? $_REQUEST['srcNama'] : '';
$srcKTP     = @isset($_REQUEST['srcKTP']) ? $_REQUEST['srcKTP'] : '';
$jns        = @isset($_REQUEST['jns']) ? $_REQUEST['jns'] : '';

$User       = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink  = $User->GetDbConnectionFromApp($a);
$dbSpec     = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbWajibPajak = new DbWajibPajak($dbSpec);

$perpage = $appConfig['ITEM_PER_PAGE'];
$defaultPage = 1;

//set new page
if (isset($_SESSION['stWP'])) {
    if ($_SESSION['stWP'] != $tab) {
        $_SESSION['stWP'] = $tab;
        $find = "";
        $page = 1;
        $np = 1;
        $srcAlamat  = '';
        $srcNama  = '';
        $srcKTP  = '';
        $jns  = '';
        echo "<script language=\"javascript\">page=1;</script>";
    }
} else {
    $_SESSION['stWP'] = $tab;
}

?>

<script type="text/javascript">
    var a = "<?php echo $a; ?>";
    var module = "<?php echo $m; ?>";
    $(document).ready(function() {
        $(".link_id").click(function() {
            var f = "<?php echo $arConfig['id_view_spop']; ?>";
            var wpid = $(this).attr("id");
            $("#wpid").attr("value", wpid);
            document.getElementById("nop").innerHTML = "Loading...";

            $.ajax({
                type: "POST",
                url: "./function/PBB/nop/wp/get_nop.php",
                data: "wpid=" + wpid,
                dataType: "json",
                success: function(data) {
                    if (data.respon != false) {
                        // alert(data.respon[0].CPM_NOP);
                        var strNOP = data.respon;
                        var c = strNOP.length;
                        var listNOP = "";
                        for (i = 0; i < c; i++) {
                            // listNOP += "<p id='"+data.respon[i].CPM_NOP+"#"+data.respon[i].CPM_SPPT_DOC_ID+"#"+data.respon[i].CPM_SPPT_DOC_VERSION+"' class='cListNOP'> " + data.respon[i].CPM_NOP + "</p>";
                            var param = Base64.encode("a=" + a + "&m=" + module + "&f=" + f + "&doc_id=" + data.respon[i].CPM_SPPT_DOC_ID + "&doc_vers=" + data.respon[i].CPM_SPPT_DOC_VERSION);
                            listNOP += "<a href='main.php?param=" + param + "' target='_blank'> " + data.respon[i].CPM_NOP + "</a>";
                        }
                        document.getElementById("nop").innerHTML = listNOP;
                    } else
                        document.getElementById("nop").innerHTML = "Tidak ada NOP";

                }
            });

            $("#box1").css("display", "block");
            $("#box2").css("display", "block");
        });
        $("#closednomor").click(function() {
            $("#box2").css("display", "none");
            $("#box1").css("display", "none");
        });

    });

    function cetakKartuWP(wpid) {
        var params = {
            wpid: wpid,
            appid: a
        };
        console.log("print ...");
        params = Base64.encode(Ext.encode(params));
        window.open('./function/PBB/nop/wp/print-kartu-wp.php?q=' + params, '_newtab');
    }

    function editWP(params) {
        //			window.open('main.php?param='+params);
        window.location.href = 'main.php?param=' + params;
    }
</script>

<?php

displayDataWP();

?>