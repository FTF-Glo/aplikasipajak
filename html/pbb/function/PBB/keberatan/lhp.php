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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';
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
$dbServices = new DbServices($dbSpec);
$userArea = $dbUtils->getUserDetailPbb($uid);

if ($userArea == null) {
    echo "Aplikasi tidak dapat digunakan karena anda tidak terdaftar sebagai user PBB pada area manapun";
    return false;
} else {
    $userArea = $userArea[0];
}

if (isset($_REQUEST['btn-finalize']) && isset($_REQUEST['check-all'])) {
    //print_r ($_REQUEST['check-all']);
    $aVal['CPM_STATUS'] = 2;
    foreach ($_REQUEST['check-all'] as $id) {
        $dbServices->edit($id, $aVal);
    }
}

#Penerimaan
function displayMenuLHP()
{
    global $a, $m;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/keberatan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'90'}") . "\">LHP</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>
<style type="text/css">
    #btnClose {
        cursor: pointer;
    }

    .linkInputNomor:hover {
        color: #ce7b00;
    }

    .linkInputNomor {
        text-decoration: underline;
        cursor: pointer;
    }

    .kirim:hover {
        color: #ce7b00;
    }

    .kirim {
        text-decoration: underline;
        cursor: pointer;
    }

    #contsetnomor1,
    #contsetnomor2 {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #contsetnomor1 {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #contsetnomor2 {
        z-index: 2;
    }

    #closednomor {
        cursor: pointer;
    }
</style>
<div id="contsetnomor2">
    <div align="center" id="setnomor" style="width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div id="closednomor" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <table class="table table-bordered">
                <tr>
                    <!-- <td align="left">No Nota Dinas<br><input type="text" name="nomorP" id="nomorP"/></td> -->
                    <td>Nomor LHP<br><input type="text" name="nomorV" id="nomorV" /></td>
                </tr>
                <tr>
                    <!-- <td>Tanggal Nota Dinas<br><input type="text" name="tanggalP" id="tanggalP"/></td> -->
                    <td>Tanggal LHP<br><input type="text" name="tanggalV" id="tanggalV" /></td>
                <tr>
                    <td colspan="2">
                        <div align="center"><button id="simpannomor">Simpan</button></div>
                    </td>
                <tr>
                    <input type="hidden" id="nspop" />
            </table>
        </div>
    </div>
</div>
<div id="contsetnomor1"></div>
<script type="text/javascript">
    function filKel(sel, sts) {
        if (sel == 90) sel = 0;

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

    function setTabs(sel, sts) {
        if (sel == 90) sel = 0;

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

        if (sel == 90) sel = 0;
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
<div class="col-md-12">
    <div id="tabsContent">
        <?php
        displayMenuLHP();
        ?>
    </div>
</div>