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
function displayMenuPenerimaan()
{
    global $a, $m;

    echo "<ul>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10'}") . "\">Tertunda</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'11'}") . "\">Dalam Proses</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'12'}") . "\">Ditolak</a></li>";
    echo "</ul>";
}

function displayMenuVerifikasi()
{
    global $a, $m;

    echo "<ul>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Tertunda</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'11'}") . "\">Dalam Proses</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'22'}") . "\">Ditolak</a></li>";
    echo "</ul>";
}

function displayMenuPersetujuan()
{
    global $a, $m;

    echo "<ul>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'30'}") . "\">Tertunda</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'11'}") . "\">Dalam Proses</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'32'}") . "\">Ditolak</a></li>";
    echo "<li><a href=\"view/PBB/pengurangan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'33'}") . "\">Disetujui</a></li>";
    echo "</ul>";
}

?>
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>
<style type="text/css">
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

    .inputNoSKKosong:hover {
        color: #ce7b00;
    }

    .inputNoSKKosong {
        text-decoration: underline;
        cursor: pointer;
    }

    .inputNoSK:hover {
        color: #ce7b00;
    }

    .inputNoSK {
        text-decoration: underline;
        cursor: pointer;
    }

    .isiKeterangan:hover {
        color: #ce7b00;
    }

    .isiKeterangan {
        text-decoration: underline;
        cursor: pointer;
    }

    #content1,
    #content2,
    #content3,
    #content4,
    #content5,
    #content6,
    #contentKeterangan1,
    #contentKeterangan2 {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #content1,
    #content3,
    #content5,
    #contentKeterangan1 {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #content2,
    #content4,
    #content6,
    #contentKeterangan2 {
        z-index: 2;
    }

    #closednomorSK,
    #closednomorSK2,
    #closedKeterangan {
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

<div id="content2">
    <div align="center" id="setnomor" style="width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div id="closednomorSK" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <table class="table table-bordered">
                <tr>
                    <td>Nomor SK<br><input type="text" name="nomorSK" id="nomorSK" /></td>
                </tr>
                <tr>
                    <td>Tanggal SK<br><input type="text" name="tanggalSK" id="tanggalSK" /></td>
                <tr>
                    <td colspan="2">
                        <div align="center"><input type="button" value="Simpan" id="simpannomorsk"></div>
                    </td>
                <tr>
                    <input type="hidden" id="nspop" name="nspop" />
                    <input type="hidden" id="nop" name="nop" />
                    <input type="hidden" id="tahun" name="tahun" />
            </table>
        </div>
    </div>
</div>
<div id="content1"></div>

<div id="content4">
    <div align="center" id="setnomor" style="width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div id="closednomorSK2" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <table class="table table-bordered">
                <tr>
                    <td align="center">Maaf, proses tidak bisa dilanjutkan karena NOP sudah melakukan pembayaran.</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div id="content3"></div>

<div id="content6">
    <div align="center" id="setnomor" style="width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div id="closednomorSK3" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <table class="table table-bordered">
                <tr>
                    <td align="center">Maaf, terjadi kegagalan server. Coba ulangi lagi atau hubungi admin.</td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div id="content5"></div>

<div id="contentKeterangan2">
    <div align="center" id="setnomor" style="width: 400px; height: auto; margin: auto; margin-top: 200px; border: 1px solid #eaeaea; background-color: #fff; z-index: 10;">
        <div style="width: 398; height: 30px; border-bottom: 1px solid #eaeaea; overflow: auto;">
            <div id="closedKeterangan" style="float: right; margin: 3px; padding: 3px; border: 1px solid #eaeaea;">X</div>
        </div>
        <div style="margin: 10px;margin-left: 10px;">
            <table class="table table-bordered">
                <tr>
                    <td>Keterangan<br><textarea cols="40" rows="4" name="keterangan" id="keterangan"></textarea></td>
                </tr>
                <td colspan="2">
                    <div align="center"><input type="button" value="Simpan" id="simpanketerangan"></div>
                </td>
                <tr>
                    <input type="hidden" id="nspop" name="nspop" />
            </table>
        </div>
    </div>
</div>
<div id="contentKeterangan1"></div>

<script type="text/javascript">
    function filKel(sel, sts) {
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 12) sel = 2;

        if (sel == 20) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 22) sel = 2;

        if (sel == 30) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 32) sel = 2;
        if (sel == 33) sel = 3;
        var kel = sts.value;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: kel,
                buku: $("#buku").val(),
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function filBook(sel, sts) {
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 12) sel = 2;

        if (sel == 20) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 22) sel = 2;

        if (sel == 30) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 32) sel = 2;
        if (sel == 33) sel = 3;
        var buku = sts.value;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                buku: buku
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function setTabs(sel, sts) {
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 12) sel = 2;

        if (sel == 20) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 22) sel = 2;

        if (sel == 30) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 32) sel = 2;
        if (sel == 33) sel = 3;
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
        var page = np;
        if (np == 1) page++;
        else page--;
        if (sel == 10) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 12) sel = 2;

        if (sel == 20) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 22) sel = 2;

        if (sel == 30) sel = 0;
        if (sel == 11) sel = 1;
        if (sel == 32) sel = 2;
        if (sel == 33) sel = 3;
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
        if ($arConfig['usertype'] == "pengurangan") {
            displayMenuPenerimaan();
        } else if ($arConfig['usertype'] == "verifikasi") {
            displayMenuVerifikasi();
        } else if ($arConfig['usertype'] == "persetujuan") {
            displayMenuPersetujuan();
        }

        ?>
    </div>
</div>