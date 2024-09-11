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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
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

if (isset($_SESSION['printerName'])) {
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
}

function displayMenuPencetakan()
{
    global $arConfig, $a, $m, $srch;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'70'}") . "\">SPPT</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<!-- <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js"></script>-->
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.min.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
    var page = 1;

    function searchMultiNOP(sel) {
        let oriSel = sel;
        //Penetapan 9290202
        if (sel == 70) sel = 0;
        var displayDat = $("#tampilkan_data").val();

        var param = {};
        param.nop = $("#daftarNOP").val();
        param.filterType = $("input[name=tipeFilter]:checked").val();
        param.tahun = $("#tahun").val();
        param.displayDat = displayDat;
        param.buku = $('.buku' + oriSel).val();
        param.kel = $('.kel' + oriSel).val();

        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: param
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function filKel(sel, sts) {
        let oriSel = sel;
        let isMultipleNop = $('#daftarNOP').is(':visible');
        if (sel == 70) sel = 0;
        var kel = sts.value;
        var displayDat = $("#tampilkan_data").val();
        var srch = $("#srch-" + oriSel).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: kel,
                tahun: $('#tahun').val(),
                buku: $('#buku').val(),
                displayDat: displayDat,
                srch: !isMultipleNop ? srch : '',
                nop: isMultipleNop ? $("#daftarNOP").val() : ''
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);
        
    }

    function filBook(sel, sts) {
        let oriSel = sel;
        let isMultipleNop = $('#daftarNOP').is(':visible');
        if (sel == 70) sel = 0;
        var buku = sts.value;
        var displayDat = $("#tampilkan_data").val();
        var srch = $("#srch-" + oriSel).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                kel: $("#kel").val(),
                buku: buku,
                tahun: $('#tahun').val(),
                displayDat: displayDat,
                srch: !isMultipleNop ? srch : '',
                nop: isMultipleNop ? $("#daftarNOP").val() : ''
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function setTabs(sel, sts, np) {
        let oriSel = sel;
        let isMultipleNop = $('#daftarNOP').is(':visible');
        if (sel == 70) sel = 0;
        var srch = $("#srch-" + oriSel).val();
        var tahun = $("#tahun").val();
        var displayDat = $("#tampilkan_data").val();
        var kel = $('.kel' + oriSel).val();
		var kec = $('.kec' + oriSel).val();

        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srch: srch,
                tahun: tahun,
                displayDat: displayDat,
                buku: $('.buku' + oriSel).val(),
                kel: kel ? kel : kec
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function displayDat(sel, sts, np) {
        let oriSel = sel;
        let isMultipleNop = $('#daftarNOP').is(':visible');
        if (sel == 70) sel = 0;
        var srch = $("#srch-" + oriSel).val();
        var tahun = $("#tahun").val();
        var displayDat = $("#tampilkan_data").val();
        var kel = $('.kel' + oriSel).val();
		var kec = $('.kec' + oriSel).val();

        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                srch: !isMultipleNop ? srch : '',
                nop: isMultipleNop ? $("#daftarNOP").val() : '',
                tahun: tahun,
                displayDat: displayDat,
                buku: $('.buku' + oriSel).val(),
                kel: kel ? kel : kec
            }
        });
        $("#tabsContent").tabs("option", "selected", sel);
        $("#tabsContent").tabs('load', sel);

    }

    function setPage(sel, sts, np) {
        let oriSel = sel;
        let isMultipleNop = $('#daftarNOP').is(':visible');
        if (np == 1) page++;
        else page--;

        var kel = '';
        var tahun = '';
        if (sel == 70) {
            sel = 0;
            kel = $('#kel').val();
            tahun = $('#tahun').val();

        }
        var srch = $("#srch-" + oriSel).val();
        $("#tabsContent").tabs("option", "ajaxOptions", {
            async: false,
            data: {
                page: page,
                np: np,
                srch: !isMultipleNop ? srch : '',
                kel: kel,
                tahun: tahun,
                buku: $('.buku' + oriSel).val(),
                nop: isMultipleNop ? $("#daftarNOP").val() : '',
                displayDat: $("#tampilkan_data").val()
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
                printSttsCommand('<?php echo $a; ?>', $(this).val());
            }
        });
    }

    function printdhkpdata() {
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                printDhkpCommand('<?php echo $a; ?>', $(this).val());
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

    function changePrinterSuccess(params) {

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
            appID: '<?php echo $a; ?>',
            tahun: $('#tahun').val()
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-preview.php?req=' + params, '_newtab');
    }
	
	function printpreviewdataESPPT() {
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
            printToPDFESPPT(nop);
        }
    }
	
	function printToPDFESPPT(nop) {
        var params = {
            NOP: nop,
            appID: '<?php echo $a; ?>',
            tahun: $('#tahun').val()
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-preview-e-sppt.php?req=' + params, '_newtab');
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
            appID: '<?php echo $a; ?>',
            tahun: $('#tahun').val()
        };
        params = Base64.encode(Ext.encode(params));
        window.open('function/PBB/print/svc-print-preview-double.php?req=' + params, '_newtab');
    }
    // newcetak by aldes
    function exportPDF(newCetak = false, printDouble = false) {
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
            printToPDF2(nop, newCetak, printDouble);
        }
    }

    function printToPDF2(nop, newCetak, printDouble = false) {
        var params = {
            NOP: nop,
            appID: '<?php echo $a; ?>',
            tahun: $('#tahun').val(),
            newCetak: newCetak,
            typePrint: 'html',
            printDouble: printDouble
        };
        params = Base64.encode(Ext.encode(params));

        // aldes

        var formCetak = document.createElement('form');
        var reqForm = document.createElement('input');

        formCetak.method = "POST";
        formCetak.action = "function/PBB/print/svc-print-pdf.php";
        formCetak.target = "_blank";

        reqForm.name = "req";
        reqForm.value = params;
        formCetak.appendChild(reqForm);

        document.body.appendChild(formCetak);
        formCetak.submit();
        formCetak.remove();


        // window.open('function/PBB/print/svc-print-pdf.php?req=' + params, '_newtab');
    }

    function generateAll(cetakFasum = false) {
        var classCheckFasum = cetakFasum ? '.check-all-fasum' : '';
        x = 0;
        $("input"+ classCheckFasum +":checkbox[name='check-all\\[\\]']").each(function() {
            if($(this).is(":checked")) x++;
        });

        if (x == 0) alert("Pilih ceklis data yang akan di generate!");
        else {
            var nopnop = [];
            $("input"+ classCheckFasum +":checkbox[name='check-all\\[\\]']").each(function() {
                if ($(this).is(":checked")) nopnop.push($(this).val());
            });
            hitloopthem(nopnop);
        }
    }

    function hitloopthem(nopnop) {
        if(confirm("Proses ini akan men-generate QRIS code \nSilakan Tunggu hingga proses terakhir selesai\n\nApakah mau lanjut ?\n")){
            hitloop(nopnop,0);
        }
    }

    function hitloop(nopnop,urut) {
        let lennop = (nopnop.length - 1);
        if(urut<=lennop){
            var nop = nopnop[urut];
            var elem = document.getElementById("divico"+nop);
            urut = parseInt(urut) + 1;
            if(elem){
                var elmA = elem.firstElementChild;
                var onklik = elmA.getAttribute("onclick");
                let repword = onklik.replace("getQRCode('", "");
                    repword = repword.replace("')", "");
                    repword = repword.replaceAll("'", "");
                    repword = repword.split(",");
                var sha1 = repword[1];
                var year = repword[2];
                var exp  = repword[3];
                document.getElementById("idico"+nop).src = "./image/large-loading.gif";
                document.getElementById("divico"+nop).parentElement.parentElement.classList.add("loading");
                Ext.Ajax.request({
                    url: "function/PBB/func-get-qris.php",
                    method: "POST",
                    params: {
                        nop:nop,
                        sha1:sha1,
                        year:year,
                        exp:exp
                    },
                    success: function(result, request) {
                        var respon = JSON.parse(result.responseText);
                        if(respon.status) {
                            document.getElementById("idico"+nop).src = "./image/icon/qr.png";
                            document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                            elmA.removeAttribute("onclick");
                            elmA.removeAttribute("href");;
                        }else{
                            document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                            document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                            elmA.setAttribute("onclick",onklik);
                        }
                        hitloop(nopnop,urut);
                    },
                    failure: function(result, request) {
                        document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                        document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                        elmA.setAttribute("onclick",onklik);
                        hitloop(nopnop,urut);
                    }
                });
            }else{
                hitloop(nopnop,urut);
            }
        }else{
            alert("Selesai");
        }
    }


    function repGenerate(cetakFasum = false) {
        var classCheckFasum = cetakFasum ? '.check-all-fasum' : '';
        x = 0;
        $("input"+ classCheckFasum +":checkbox[name='check-all\\[\\]']").each(function() {
            if($(this).is(":checked")) x++;
        });

        if (x == 0) alert("Ceklis salah satu atau lebih data yang akan di generate ulang!");
        else {
            var nopnop = [];
            $("input"+ classCheckFasum +":checkbox[name='check-all\\[\\]']").each(function() {
                if ($(this).is(":checked")) nopnop.push($(this).val());
            });
            hitloopthemregen(nopnop);
        }
    }

    function hitloopthemregen(nopnop) {
        if(confirm("Generate ulang QRIS code \nSilakan Tunggu hingga selesai\n\nApakah mau lanjut ?\n")){
            hitloop2(nopnop,0);
        }
    }

    function hitloop2(nopnop,urut) {
        let lennop = (nopnop.length - 1);
        if(urut<=lennop){
            var nop = nopnop[urut];
            var ico = document.getElementById("idico"+nop);
            urut = parseInt(urut) + 1;
            if(ico){
                var elem = document.getElementById("divico"+nop);
                if(!elem){
                    ico.src = "./image/large-loading.gif";
                    ico.parentElement.parentElement.classList.add("loading");
                    let repword = ico.getAttribute("data-re");
                        repword = repword.replace("')", "");
                        repword = repword.replaceAll("'", "");
                        repword = repword.split(",");
                    var sha1 = repword[1];
                    var year = repword[2];
                    var exp  = repword[3];

                    Ext.Ajax.request({
                        url: "function/PBB/func-get-qris.php",
                        method: "POST",
                        params: {
                            nop:nop,
                            sha1:sha1,
                            year:year,
                            exp:exp,
                            regen:true
                        },
                        success: function(result, request) {
                            var respon = JSON.parse(result.responseText);
                            if(respon.status) {
                                ico.src = "./image/icon/qr.png";
                            }else{
                                ico.src = "./image/icon/qr_disable.png";
                            }
                            ico.parentElement.parentElement.classList.remove("loading");
                            hitloop2(nopnop,urut);
                        },
                        failure: function(result, request) {
                            ico.src = "./image/icon/qr_disable.png";
                            ico.parentElement.parentElement.classList.remove("loading");
                            hitloop2(nopnop,urut);
                        }
                    });
                }else{
                    hitloop2(nopnop,urut);
                }
            }else{
                hitloop2(nopnop,urut);
            }
            
        }else{
            alert("Selesai");
        }
    }

    ///// HIT REST API 
    var repeat = 0;

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function getQRCode(nop,sha1,year,exp) { /// getQRCode 21032023
        if(repeat==0){
            if(confirm("Proses ini akan men-generate QRIS code \n\nApakah mau lanjut ?\n")){
                repeat = 1;
                document.getElementById("idico"+nop).src = "./image/large-loading.gif";
                document.getElementById("divico"+nop).parentElement.parentElement.classList.add("loading");
                hitit(nop,sha1,year,exp);
            }else{
                document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                repeat = 0;
            }
        }
    }

    function hitit(nop,sha1,year,exp) {
        if(repeat!=0) {
            Ext.Ajax.request({
                url: "function/PBB/func-get-qris.php",
                method: "POST",
                params: {
                    nop:nop,
                    sha1:sha1,
                    year:year,
                    exp:exp
                },
                success: function(result, request) {
                    var respon = JSON.parse(result.responseText);
                    if(respon.status) {
                        repeat=0;
                        alert("Berhasil");
                        document.getElementById("idico"+nop).src = "./image/icon/qr.png";
                        document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                        let elem = document.getElementById("divico"+nop).firstElementChild;
                            elem.removeAttribute("onclick");
                            elem.removeAttribute("href");
                    }else if(respon.msg=="repeat"){
                        repeat++;
                        if(repeat>=4) {
                            repeat=0;
                            document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                            document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                            alert("Gagal koneksi ke Server REST API");
                        }
                        sleep(2000).then(() => { hitit(nop,sha1,year,exp); });
                    }else{
                        repeat=0;
                        alert(respon.msg);
                        document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                        document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                        let elem = document.getElementById("divico"+nop).firstElementChild;
                            elem.removeAttribute("onclick");
                            elem.removeAttribute("href");
                    }
                },
                failure: function(result, request) {
                    repeat=0;
                    alert("Gagal Mengambil Data");
                    document.getElementById("idico"+nop).src = "./image/icon/qr_disable.png";
                    document.getElementById("divico"+nop).parentElement.parentElement.classList.remove("loading");
                }
            });
        }
    }
</script>

<div id="tabsContent">
    <?php
    displayMenuPencetakan();
    ?>
</div>
<?php
echo "<div id=\"tab-result\"></div>
			<!-- applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
				<param name='printer' id='printer' value='" . $printername . "'>
				<param name='sleep' value='200'>
			</applet -->
		</div>";
?>
<!-- <script>
    // function listPrinter() {
    //     var applet = document.jZebra;
    //     if (applet != null) {
    //         if (!applet.isDoneFinding()) window.setTimeout('listPrinter()', 1000);
    //         else {
    //             var listing = applet.getPrinters();
    //             var printers = listing.split(',');
    //             var printerslist = document.getElementById('selectedPrinter');

    //             for (var i in printers) {
    //                 printerslist.options[i] = new Option(printers[i]);
    //                 if (printers[i] == '<?php //echo $printername ?>') {
    //                     document.getElementById('selectedPrinter').selectedIndex = i;
    //                 }
    //             }
    //             document.getElementById('printer').value = selectedPrinter.options[printerslist.selectedIndex].value;
    //         }
    //     } else alert('Applet not loaded!');
    // }
</script> -->
<script>
    // ALDES
	// https://stackoverflow.com/a/51174956
	function enableGroupSelection( selector ) {
	  let lastChecked = null;
	  const checkboxes = Array.from( document.querySelectorAll( selector ) );

	  checkboxes.forEach( checkbox => checkbox.addEventListener( 'click', event => {
		if ( !lastChecked ) {
		  lastChecked = checkbox;

		  return;
		}

		if ( event.shiftKey ) {
		  const start = checkboxes.indexOf( checkbox );
		  const end   = checkboxes.indexOf( lastChecked );

		  checkboxes
			.slice( Math.min( start, end ), Math.max( start, end ) + 1 )
			.forEach( checkbox => checkbox.checked = lastChecked.checked );
		}

		lastChecked = checkbox;
	  } ) );
	}
	
    function printDHKP() {
        var tahun = $("#tahun").val();
        var kecamatan = $("#kec").val();
        var kelurahan = $("#kel").val();
        var namakec = $("#kec option:selected").text().replace(/[^a-zA-Z\s]+/g, '').trim();
        var namakel = $("#kel option:selected").text().replace(/[^a-zA-Z\s]+/g, '').trim();
        var stsPenetapan = '';
        var buku = $("#buku").val();
        var sts = 1;
        var printer = $('#selectedPrinterNew').val();

        var nop = '';
        var idx = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                if (idx > 0) nop = nop + ',';
                nop = nop + $(this).val();
                idx++;
            }
        });

        if (kecamatan == "" && nop == "") {
            alert("Silahkan pilih kecamatan!");
            return;
        }

        // if (kelurahan == "") {
        //     alert('Kelurahan wajib dipilih.');
        //     return;
        // }

        if (confirm('Apakah anda ingin melihat preview ?')) {
            // var fullParams = "&th=" + tahun + "&st=" + sts + "&kc=" + kecamatan + "&kl=" + kelurahan + "&n=" + namakec + "&nn=" + namakel + "&stsPenetapan=" + stsPenetapan + "&buku=" + buku + "&cetakNew=true&displayHtml=true"+ "&nop=" + nop;
            // var fullUrl = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>&"+fullParams;
            
            var formCetak = document.createElement('form');

            formCetak.method = "POST";
            formCetak.action = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php";
            formCetak.target = "_blank";

            var reqForm = document.createElement('input');
            reqForm.name = "q";
            reqForm.value = "<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>";
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "th";
            reqForm.value = tahun;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "st";
            reqForm.value = sts;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "kc";
            reqForm.value = kecamatan;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "kl";
            reqForm.value = kelurahan;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "n";
            reqForm.value = namakec;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "nn";
            reqForm.value = namakel;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "stsPenetapan";
            reqForm.value = stsPenetapan;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "buku";
            reqForm.value = buku;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "cetakNew";
            reqForm.value = true;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "displayHtml";
            reqForm.value = true;
            formCetak.appendChild(reqForm);

            var reqForm = document.createElement('input');
            reqForm.name = "nop";
            reqForm.value = nop;
            formCetak.appendChild(reqForm);

            document.body.appendChild(formCetak);
            formCetak.submit();
            formCetak.remove();

            // window.open(fullUrl);
            return false;
        }else {
            if(!confirm('Apakah anda ingin lanjut cetak ?')) {
                return false;
            }
        }

        if(!$('#selectedPrinterNew option').length) {
            alert('Aplikasi QZ Belum aktif, silakan jalankan aplikasi nya terlebih dahulu.');
            initQZ();
            return;
        }

        if (!printer) {
            alert('Pilih printer yang tesedia');
            return;
        }

        var url = "view/PBB/monitoring/svc-monitoring-rekap-dhkp.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'6','uid':'$uid'}"); ?>";
        $.post(url, {
            th: tahun,
            st: sts,
            kc: kecamatan,
            kl: kelurahan,
            n: namakec,
            nn: namakel,
            stsPenetapan: stsPenetapan,
            buku: buku,
            cetakNew: true,
            displayHtml: false,
            nop: nop
        }, function(data) {
            if (!data) {
                alert('Tidak ada data.');
                return;
            }
            if (!qz.websocket.isActive()) {
                alert('QZ Websocket belum aktif');
                return;
            }
            var printer = $('#selectedPrinterNew').val();
            if (!printer) {
                alert('Pilih printer yang tesedia');
                return;
            } else {
                qz.printers.find(printer).then((validPrinter) => {
                    var config = qz.configs.create(validPrinter);
                    var printData = [{
                        type: 'raw',
                        format: 'command',
                        flavor: 'plain',
                        data: data
                    }];
                    qz.print(config, printData).then((e) => {
                        alert('Data sudah dikirim ke printer');
                    }).catch((e) => {
                        console.error(e);
                    });
                }).catch(function(e) {
                    alert('Printer tidak valid');
                    console.error(e);
                });
            }
        }).fail(function() {
            alert('Terjadi kesalahan, silahkan coba lagi.');
            console.error(e);
        });
        // showRekapDHKP(true);
    }

    function newPrintData(el, printDouble = false) {
        var nop = '';
        var idx = 0;
        $("input:checkbox[name='check-all\\[\\]']").each(function() {
            if ($(this).is(":checked")) {
                if (idx > 0) nop = nop + ',';
                nop = nop + $(this).val();
                idx++;
            }
        });

        if(!$('#selectedPrinterNew option').length) {
            initQZ();
            return;
        }

        if (!qz.websocket.isActive()) {
            initQZ();
            alert('QZ Websocket telah aktif, silakan pilih printer');
            return;
        }

        if (idx == 0) {
            return alert("Pilih data yang akan dicetak!");
        }

        var params = {
            NOP: nop,
            appID: '<?php echo $a; ?>',
            tahun: $('#tahun').val(),
            newCetak: true,
            typePrint: 'text',
            printDouble: printDouble
        };
        params = Base64.encode(Ext.encode(params));

        $.ajax({
            url: 'function/PBB/print/svc-print-pdf.php',
            type: 'POST',
            data: {
                req: params
            },
            async: true,
            beforeSend: function() {
                $(el).prop('disabled', true);
            },
            success: function(data) {
                if (!data) {
                    alert('Tidak ada data.');
                    return;
                }
                if (!qz.websocket.isActive()) {
                    initQZ();
                    alert('QZ Websocket telah aktif, silakan pilih printer');
                    return;
                }
                var printer = $('#selectedPrinterNew').val();
                if (!printer) {
                    alert('Pilih printer yang tesedia');
                    return;
                } else {
                    qz.printers.find(printer).then((validPrinter) => {
                        var config = qz.configs.create(validPrinter);
                        var printData = [
                            {
                                type: 'raw',
                                format: 'command',
                                flavor: 'plain',
                                data: data
                            }
                        ];
                        qz.print(config, printData).then((e) => {
                            alert('Data sudah dikirim ke printer');
                        }).catch((e) => {
                            console.error(e);
                        });
                    }).catch(function(e) {
                        alert('Printer tidak valid');
                        console.error(e);
                    });
                }
            },
            error: function(e) {
                alert('Terjadi kesalahan, silahkan coba lagi.');
                console.error(e);
            }

        }).done(function() {
            $(el).removeAttr('disabled');
        });

        return;

    }

    function initQZ() {
        if (qz.websocket.isActive()) {
            findPrinters();
            return;
        }
        return qz.websocket.connect().then(function() {
            findPrinters();
        }).catch((e) => {
            // showDialog('Error', 'Software QZ belum aktif atau belum terinstal, <a href="https://qz.io/download/" target="_blank">Download</a>', 'error', false, false);
            console.error(e);
        });
    }

    function findPrinters() {
        qz.printers.find().then(function(data) {
            var list = '<option value="" disabled selected>Pilih printer</option>';
            for (var i = 0; i < data.length; i++) {
                list += "<option value=\"" + data[i] + "\">" + data[i] + "</option>";
            }
            $('#selectedPrinterNew').html(list);
            console.log(data);
        }).catch(function(e) {
            console.error(e);
        });
    }

    initQZ();
</script>