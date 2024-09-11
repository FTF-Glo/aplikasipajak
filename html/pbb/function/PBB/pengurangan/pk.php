<?php
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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbSpptHistory.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "function/PBB/gwlink.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbServices = new DbServices($dbSpec);
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
#Penerimaan
function displayMenuPenerimaan()
{
    global $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10'}") . "\">Tertunda</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'11'}") . "\">Dalam Proses</a></li>\n";
    //echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'12'}") . "\">Ditolak</a></li>\n";
    echo "\t</ul>\n";
}

#Verifikasi
function displayMenuVerifikasi()
{
    global $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Tertunda</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'21'}") . "\">Dalam Proses</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'22'}") . "\">Ditolak</a></li>\n";
    echo "\t</ul>\n";
}
#Persetujuan
function displayMenuPersetujuan()
{
    global $arConfig, $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'30'}") . "\">Tertunda</a>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'31'}") . "\">Dalam Proses</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'32'}") . "\">Ditolak</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
    function setTabs(sel, sts) {
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        //if (sel==12) sel = 2;
        var srch = $("#srch-" + sts).val();
        //alert(srch);
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
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        //if (sel==12) sel = 2;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
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

    });

    function printdata() {
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                printCommand('<?php echo $a; ?>', $(this).val());
            }
        });
    }
</script>

<div id="tabsContent">
    <?php
    displayMenuPenerimaan();
    if ($arConfig['usertype'] == "penerimaan") {
        displayMenuPenerimaan();
    } else if ($arConfig['usertype'] == "verifikasi") {
        displayMenuVerifikasi();
    } else if ($arConfig['usertype'] == "persetujuan") {
        displayMenuPersetujuan();
    }
    ?>
</div>