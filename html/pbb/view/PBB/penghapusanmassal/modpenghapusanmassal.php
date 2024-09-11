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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penghapusanmassal', '', dirname(__FILE__))) . '/';
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

/*if (isset($_SESSION['printerName'])) {
    $printername = $_SESSION['printerName'];
    $printername = mysqli_escape_string($dbSpec->getDBLink(), $printername);
} else {
    $printerList = explode(';', $appConfig['PRINTER_NAME']);
    $userPrinter = $dbUtils->getPrinterName($uid, $m);
    if ($userPrinter == null) {
        $printername = $printerList[0];
    } else {
        $printername = $userPrinter[0]['CPM_PRINTERNAME'];
    }
    $printername = mysqli_escape_string($dbSpec->getDBLink(), $printername);
    $_SESSION['printerName'] = $printername;
}*/

function displayMenuPencetakan()
{
    global $arConfig, $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/penghapusanmassal/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'70'}") . "\">SPPT</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<!-- <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>-->
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.min.js"></script>
<!--<script type="text/javascript" src="inc/PBB/svc-print.js"></script>-->

<script type="text/javascript">
    var page = 1;

    function searchMultiNOP(sel) {
        //Penetapan
        if (sel == 70) sel = 0;

        var param = {};
        param.nop = $("#daftarNOP").val();
        param.filterType = $("input[name=tipeFilter]:checked").val();
        param.tahun = $("#tahun").val();
        param.kel = $("#kel").val();
        param.buku = $("#buku").val();

        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: param
        });

        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function filKel(sel, sts) {
        if (sel == 70) sel = 0;
        var kel = sts.value;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: kel,
                tahun: $('#tahun').val(),
                buku: $('#buku').val(),
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function filBook(sel, sts) {
        if (sel == 70) sel = 0;
        var buku = sts.value;
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: $("#kel").val(),
                buku: buku,
                tahun: $('#tahun').val()
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function setTabs(sel, sts, np) {
        if (sel == 70) sel = 0;
        var srch = $("#srch-" + sts).val();
        var tahun = $("#tahun").val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srch: srch,
                kel: $('#kel').val(),
                tahun: tahun,
                buku: $('#buku').val(),
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
    }

    function setPage(sel, sts, np) {
        if (np == 1) page++;
        else page--;

        var kel = '';
        var tahun = '';
        var buku = '';
        if (sel == 70) {
            sel = 0;
            kel = $('#kel').val();
            tahun = $('#tahun').val();
            buku = $('#buku').val();
        }
        var srch = $("#srch-" + sts).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                srch: srch,
                kel: kel,
                tahun: tahun,
                buku: buku,
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
        //listPrinter();
    });

    function printdata() {
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                printCommand('<?php echo $a; ?>', $(this).val());
            }
        });
    }

    function printsttsdata() {
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                printCommand('<?php echo $a; ?>', $(this).val());
            }
        });
    }

    function printdataDouble() {
        // $("input:checkbox[name='check-all\\[\\]']").each(function(){
        // if($(this).is(":checked")){			
        // printCommandDouble('<?php echo $a; ?>',$(this).val());
        // }
        // });
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan diprint!");
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

            var ids = nop.split(",");
            var max = ids.length;
            for (i = 0; i < max; i += 2) {
                var nop0 = ids[i];
                var nop1 = ids[i + 1];
                printCommandDouble('<?php echo $a; ?>', nop0, nop1);
            }
        }
    }

    function hapusNOP() {
        var result = confirm("Apakah anda yakin ingin menghapus data-data tersebut?");
        if (result) {
            x = 0;
            $("input:checkbox[name='check-all\\[\\]']").each(function() {
                if ($(this).is(":checked")) {
                    x++;
                }
            });

            if (x == 0) alert("Pilih data yang akan dihapus!");
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
                hapusDataNOP(nop);
            }
        }
    }

    function hapusDataNOP(nop) {
        var params = {
            NOP: nop,
            appID: 'aPBB',
            tahun: $('#tahun').val()
        };
        //params = Base64.encode(Ext.encode(params));
        //('function/PBB/penghapusanmassal/page.php?req=' + params, '_newtab');
        $.ajax({
            type: 'POST',
            url: './function/PBB/penghapusanmassal/page.php',
            data: params,
            success: function(msg) {
                //console.log(msg);
                //alert(msg);
                let ms = JSON.parse(msg);
                alert(ms.message);
                location.reload();
            }
        });
    }

    /*function changePrinterSuccess(params) {

        if (params.responseText) {
            if (params.responseText == "sukses") {
                alert('Sukses melakukan pengaturan printer.');
                document.location.reload(true);
            } else {
                alert('Gagal melakukan pengaturan printer');
            }
        } else {
            alert('Gagal melakukan pengaturan printer');
        }
    }

    function changePrinterFailure(params) {
        alert('Gagal melakukan pengaturan printer');
    }

    function changePrinter(printername, uid, m) {

        var params = "{\"PRINTER\":\"" + printername + "\", \"UID\":\"" + uid + "\", \"MODULE\":\"" + m + "\"}";

        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'function/PBB/print/svc-changeprinter.php',
            success: changePrinterSuccess,
            failure: changePrinterFailure,
            params: {
                req: params
            }
        });

    }

    function printpreviewdata() {
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan ditinjau!");
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

    function printToPDF(nop) {
        var params = {
            NOP: nop,
            appID: '<?php //echo $a; 
                    ?>',
            tahun: $('#tahun').val()
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-preview.php?req=' + params, '_newtab');
    }

    function printpreviewdataDouble() {
        x = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                x++;
            }
        });

        if (x == 0) alert("Pilih data yang akan ditinjau!");
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
            printToPDFDouble(nop);
        }
    }

    function printToPDFDouble(nop) {
        var params = {
            NOP: nop,
            appID: '<?php //echo $a; 
                    ?>',
            tahun: $('#tahun').val()
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-preview-double.php?req=' + params, '_newtab');
    }
    // newcetak by aldes
    function exportPDF(newCetak = false) {
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
            printToPDF2(nop, newCetak);
        }
    }

    function printToPDF2(nop, newCetak) {
        var params = {
            NOP: nop,
            appID: '<?php //echo $a; 
                    ?>',
            tahun: $('#tahun').val(),
            newCetak: newCetak
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-pdf.php?req=' + params, '_newtab');
    }*/
</script>

<div id="tabsContent">
    <?php
    displayMenuPencetakan();
    ?>
</div>
<?php
/*echo "<div id=\"tab-result\"></div>
			<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
				<param name='printer' id='printer' value='" . $printername . "'>
				<param name='sleep' value='200'>
			</applet>
		</div>";*/
?>
<script>
    /*function listPrinter() {
        var applet = document.jZebra;
        if (applet != null) {
            if (!applet.isDoneFinding()) window.setTimeout('listPrinter()', 1000);
            else {
                var listing = applet.getPrinters();
                var printers = listing.split(',');
                var printerslist = document.getElementById('selectedPrinter');

                for (var i in printers) {
                    printerslist.options[i] = new Option(printers[i]);
                    if (printers[i] == '<?php echo $printername ?>') {
                        document.getElementById('selectedPrinter').selectedIndex = i;
                    }
                }
                document.getElementById('printer').value = selectedPrinter.options[printerslist.selectedIndex].value;
            }
        } else alert('Applet not loaded!');
    }*/
</script>