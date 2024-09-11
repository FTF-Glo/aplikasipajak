<?php
if (!isset($data)) {
    die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
    $terminalColumn = $arAreaConfig['terminalColumn'];
    $accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
    if (!$accessible) {
        echo "Illegal access";
        return;
    }
}

$arConfig = $User->GetModuleConfig($module);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
/* require_once($sRootPath . "inc/PBB/dbServices.php");
$dbServices = new DbServices($dbSpec); */
?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
    function setTabs(tab) {
        if (tab == 1) tab = 0;
        var srcTglAwal = $("#srcTglAwal-" + tab).val();
        var srcTglAkhir = $("#srcTglAkhir-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srcNama: srcNama,
                srcTglAwal: srcTglAwal,
                srcTglAkhir: srcTglAkhir
            }
        });
        $("#tabsContent").tabs("option", "selected", tab);
        $("#tabsContent").tabs('load', tab);
    }

    function exportToExcel(tab) {
        var srcTglAwal = $("#srcTglAwal-" + tab).val();
        var srcTglAkhir = $("#srcTglAkhir-" + tab).val();
        var srcNama = $("#srcNama-" + tab).val();
        window.open("function/PBB/loket/svc-toexcel-user-rekap.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'0'}"); ?>&srcTglAwal=" + srcTglAwal + "&srcTglAkhir=" + srcTglAkhir + "&srcNama=" + srcNama);
    }

    $(document).ready(function() {
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
    });
</script>
<div class="col-md-12">
    <div id="tabsContent">
        <ul>
            <li><a href="view/PBB/loket/svc-rekap-user-pelayanan.php?q=<?=base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}")?>">Rekapitulasi Loket Pelayanan</a></li>
        </ul>
    </div>
</div>