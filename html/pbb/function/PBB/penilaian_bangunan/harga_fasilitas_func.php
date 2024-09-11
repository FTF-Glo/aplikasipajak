<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_bangunan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;

function displayMenuPelayanan()
{  // srch
    global $a, $m, $data;

    $notifid  = @isset($_REQUEST['notifid']) ? $_REQUEST['notifid'] : "";

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">AC</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'1', 'n':'2', 'u':'$data->uname'}") . "\">Kolam Renang</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'2', 'n':'3', 'u':'$data->uname'}") . "\">Perkerasan</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'3', 'n':'4', 'u':'$data->uname'}") . "\">Lap. Tenis</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'4', 'n':'5', 'u':'$data->uname'}") . "\">Lift</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'5', 'n':'6', 'u':'$data->uname'}") . "\">Tangga Berjalan</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'6', 'n':'7', 'u':'$data->uname'}") . "\">Pagar</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'7', 'n':'8', 'u':'$data->uname'}") . "\">Proteksi Api</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'8', 'n':'9', 'u':'$data->uname'}") . "\">Genset</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'9', 'n':'10', 'u':'$data->uname'}") . "\">PABX</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'10', 'n':'11', 'u':'$data->uname'}") . "\">Air Ar.</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'11', 'n':'12', 'u':'$data->uname'}") . "\">Boiler</a></li>\n";
    echo "\t\t<li><a href=\"function/PBB/penilaian_bangunan/harga_fasilitas_svc.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'12', 'n':'13', 'u':'$data->uname'}") . "\">Listrik</a></li>\n";
    echo "\t</ul>\n";
}

?>

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>

<script type="text/javascript">
    var page = 1;

    function setTabs(tab) {
        page = 1;
        var srcTglAwal = $("#srcTglAwal-" + tab).val();
        var srcTglAkhir = $("#srcTglAkhir-" + tab).val();
        var srcNomor = $("#srcNomor-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        var srcJnsBerkas = $("#jnsBerkas").val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srcTglAwal: srcTglAwal,
                srcTglAkhir: srcTglAkhir,
                srcNomor: srcNomor,
                srcNama: srcNama,
                srcJnsBerkas: srcJnsBerkas
            }
        });
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);
    }

    function setPage(tab, np) {
        if (np == 1) page++;
        else page--;

        var srcTglAwal = $("#srcTglAwal-" + tab).val();
        var srcTglAkhir = $("#srcTglAkhir-" + tab).val();
        var srcNomor = $("#srcNomor-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        var srcJnsBerkas = $("#jnsBerkas").val();

        //		var find = $("#src-approved-"+tab).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                srcTglAwal: srcTglAwal,
                srcTglAkhir: srcTglAkhir,
                srcNomor: srcNomor,
                srcNama: srcNama,
                srcJnsBerkas: srcJnsBerkas
            }
        });
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);

    }

    $(document).ready(function() {

        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
                this.checked = $("#all-check-button").is(':checked');
            });
        });

        $("#tabsContent").tabs({
            load: function(e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function(e, ui) {
                var $panel = $(ui.panel);

                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });


        $(".datepicker").datepicker();

    });
</script>
<div class="col-md-12">
    <div id="tabsContent">
        <?php
        if (isset($tab)) {
            echo "<script language='javascript'>setTabs(" . $tab . ")</script>";
        }
        echo displayMenuPelayanan()
        ?>
    </div>
</div>