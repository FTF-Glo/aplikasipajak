<?php

//session_start();
if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'nop' . DIRECTORY_SEPARATOR . 'sk', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbSpptHistory.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "function/PBB/gwlink.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbUtils = new DbUtils($dbSpec);

// Get User Area Config
$userArea = $dbUtils->getUserDetailPbb($uid);

if ($userArea == null) {
    echo "Aplikasi tidak dapat digunakan karena anda tidak terdaftar sebagai user PBB pada area manapun";
    return false;
} else {
    $userArea = $userArea[0];
}

function displayMenuPencetakanSK()
{
    global $arConfig, $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'81'}") . "\">Susulan</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'82'}") . "\">Masal</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.min.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
    var page = 1;

    function filKel(sel, sts) {
        if (sel == 81) sel = 0;
        if (sel == 82) sel = 1;
        var kel = sts.value;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: kel
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function setTabs(sel, sts, np) {
        if (sel == 81) sel = 0;
        if (sel == 82) sel = 1;
        var srch = $("#srch-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srch: srch
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sel, sts, np) {
        if (np == 1) page++;
        else page--;
        if (sel == 81) sel = 0;
        if (sel == 82) sel = 1;
        var srch = $("#srch-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                srch: srch
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }
    $(document).ready(function() {
        $("input:submit, input:button").button();
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
        $(".selectAll").click(function() {
            if ($(".selectAll").attr("checked")) {
                $(".the_checkbox:checkbox").attr("disabled", true);
            } else {
                $(".the_checkbox:checkbox").removeAttr("disabled");
            }
        });

    });

    function printSK() {
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            var nop = '';
            var idx = 0;
            $("input:checkbox[name='check-all\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ',';
                    nop = nop + $(this).val();
                    idx++;
                }
            });
            printToPDF(nop);
        }
    }

    function printSKMasal() {
        x = 0;
        $("input:checkbox[name='check-all-masal\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            var nop = '';
            var idx = 0;
            $("input:checkbox[name='check-all-masal\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ',';
                    nop = nop + $(this).val();
                    idx++;
                }
            });
            printToPDF(nop);
        }
    }

    function printSKPrev() {
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            var nop = '';
            var idx = 0;
            $("input:checkbox[name='check-all\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ',';
                    nop = nop + $(this).val();
                    idx++;
                }
            });
            printToPDFPrev(nop);
        }
    }

    function printSKPrevMasal() {
        x = 0;
        $("input:checkbox[name='check-all-masal\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan dicetak!");
        else {
            var nop = '';
            var idx = 0;
            $("input:checkbox[name='check-all-masal\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    if (idx > 0) nop = nop + ',';
                    nop = nop + $(this).val();
                    idx++;
                }
            });
            printToPDFPrev(nop);
        }
    }

    function printToPDF(nop) {
        var params = {
            nop: nop,
            uname: '<?php echo $a; ?>',
            appID: '<?php echo $a; ?>'
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/nop/sk/sk-print.php?q=' + params, '_newtab');
    }

    function printToPDFPrev(nop) {
        var params = {
            nop: nop,
            uname: '<?php echo $a; ?>',
            appID: '<?php echo $a; ?>'
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/nop/sk/sk-print-preview.php?q=' + params, '_newtab');
    }

    // function printToPDF(nop,nosk) {
    // var params = {nop:nop,nosk:nosk, appID:'<?php echo $a; ?>'};
    // params = Base64.encode(Ext.encode(params));
    // window.open('function/PBB/nop/sk/sk-print.php?q='+params, '_newtab');
    // }

    function searchMultiNOP(sel) {

        if (sel == 82) sel = 1;

        // alert(sel);
        var kel = $("#kelNOP").val();

        var blok1 = $("#blok1").val();
        var nourut1 = $("#nourut1").val();
        var jnsNOP1 = $("#jnsNOP1").val();

        var blok2 = $("#blok2").val();
        var nourut2 = $("#nourut2").val();
        var jnsNOP2 = $("#jnsNOP2").val();

        var nop1 = blok1 + nourut1 + jnsNOP1;
        var nop2 = blok2 + nourut2 + jnsNOP2;

        var param = {};
        param.nop = $("#daftarNOP").val();
        param.filterType = $("input[name=tipeFilter]:checked").val();
        // param.tahun		 = $("#tahun").val()
        // alert(JSON.stringify(param));
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: param
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }
</script>

<div class="col-md-12">
    <div id="tabsContent">
        <?php
        displayMenuPencetakanSK();
        ?>
    </div>
</div>