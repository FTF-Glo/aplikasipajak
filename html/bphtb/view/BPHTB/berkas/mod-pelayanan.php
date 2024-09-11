<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

$printername = "Epson Lx-300+";
$tab = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;
$_SESSION['username'] = $data->uname;

function displayMenuPelayanan() {  // srch
    global $a, $m, $data;
    $notifid = @isset($_REQUEST['notifid']) ? $_REQUEST['notifid'] : "";

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/BPHTB/berkas/svc-list-penerimaanLaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Penerimaan Laporan</a></li>\n";
    echo "\t</ul>\n";
}
?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css?0002" type="text/css">
<link rel="stylesheet" href="view/BPHTB/berkas/table-pelayanan.css" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>
<script type="text/javascript">
    var page = 1;

    function setTabs(tab) {
        var srcTglAwal = $("#srcTglAwal-" + tab).val();
        var srcTglAkhir = $("#srcTglAkhir-" + tab).val();
        var srcNomor = $("#srcNomor-" + tab).val();
        var srcNoPel = $("#srcNoPel-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
		var srcNoKTP = $("#srcNoKTP-" + tab).val();

        $("#tabsContent").tabs("option", "ajaxOptions", {async: false, data: {srcTglAwal: srcTglAwal, srcTglAkhir: srcTglAkhir, srcNomor: srcNomor, srcNoPel: srcNoPel, srcNama: srcNama, srcNoKTP: srcNoKTP}});
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);
    }

    function setPage(tab, np) {
        if (np == 1)
            page++;
        else
            page--;
//		var find = $("#src-approved-"+tab).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {async: false, data: {page: page, np: np}});
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
<div id="tabsContent">
    <?php
    if (isset($tab)) {
        echo "<script language='javascript'>setTabs(" . $tab . ")</script>";
    }
    echo displayMenuPelayanan()
    ?>
</div>
