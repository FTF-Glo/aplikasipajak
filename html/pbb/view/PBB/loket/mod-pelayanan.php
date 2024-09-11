<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$printername = "Epson Lx-300+";
$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;

function displayMenuPelayanan()
{  // srch
    global $a, $m, $data;
    

    $notifid  = @isset($_REQUEST['notifid']) ? $_REQUEST['notifid'] : "";

    echo "<ul>";
    echo "<li><a href=\"view/PBB/loket/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname', 'uid':'$data->uid'}") . "\">Penerimaan Laporan</a></li>";
	//echo "<li><a href=\"view/PBB/loket/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Penerimaan Laporan</a></li>";
    echo "<li><a href=\"view/PBB/loket/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'1', 'n':'2', 'u':'$data->uname', 'uid':'$data->uid'}") . "\">Dalam Proses</a></li>";
    echo "<li><a href=\"view/PBB/loket/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'2', 'n':'3', 'u':'$data->uname'}") . "\">Selesai</a></li>";
    echo "<li><a href=\"view/PBB/loket/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'3', 'n':'4', 'u':'$data->uname'}") . "\">Laporan Harian</a></li>";
    echo "</ul>";
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

        var srcTahun = '';
        if (tab == 2) {
            srcTahun = $("#srcTahun-" + tab).val();
        }
        var data = {
            srcTglAwal: srcTglAwal,
            srcTglAkhir: srcTglAkhir,
            srcNomor: srcNomor,
            srcNama: srcNama,
            srcJnsBerkas: srcJnsBerkas,
            srcTahun: srcTahun
        };

        /*ARD+ : menambah search untuk status*/
        if ($("#srcStatus-" + tab).size() > 0) {
            data.srcStatus = $("#srcStatus-" + tab).val();
        }

        /*ARD+- : mengubah parameter data menjadi var json*/
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: data
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
        var srcTahun = '';
        if (tab == 2) {
            srcTahun = $("#srcTahun-" + tab).val();
        }

        var data = {
            page: page,
            np: np,
            srcTglAwal: srcTglAwal,
            srcTglAkhir: srcTglAkhir,
            srcNomor: srcNomor,
            srcNama: srcNama,
            srcJnsBerkas: srcJnsBerkas,
            srcTahun: srcTahun
        };

        /*ARD+ : menambah search untuk status*/
        if ($("#srcStatus-" + tab).size() > 0) {
            data.srcStatus = $("#srcStatus-" + tab).val();
        }

        /*ARD+- : mengubah parameter data menjadi var json*/
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: data
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