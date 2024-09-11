<?php
// echo "test"; exit;
require_once("tab-cetak.php");

// prevent direct access
if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK 		= $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig 	= $User->GetAppConfig($application);

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

// print_r($_SESSION); exit;
// if(isset($_SESSION['printerName'])){
    // $printername = $_SESSION['printerName'];
    // $printername = mysql_escape_string($printername);
// }else{
    // $printerList = explode(';', $appConfig['PRINTER_NAME']);
    // $userPrinter = $dbUtils->getPrinterName($uid,$m);
    // if ($userPrinter == null) {
        // $printername = $printerList[0];
    // } else {
        // $printername = $userPrinter[0]['CPM_PRINTERNAME'];
    // }
    // $printername = mysql_escape_string($printername);
    // $_SESSION['printerName'] = $printername;
// }

$tabCetak = new TabCetak($appConfig,$uid,$moduleIds);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

if (!isset($opt)) {
    ?>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
    <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script type="text/javascript" src="view/PBB/penilaian_individu/print/svc-print.js"></script>
    <script>
		var page = 1;
        function setPage(tab, np) {
			if (np==1) page++;
			else page--;
            var tahun 	= $("#tahun-pajak-" + tab).val();
			var kc 		= $("#kecamatan-" + tab).val();
			var kl 		= $("#kelurahan-" + tab).val();
				
			$("#content-1").html("loading ...");
			$("#content-1").load("view/PBB/penilaian_individu/svc-cetak.php?q=<?php echo  base64_encode("{'a':'$a', 'm':'$m', 's':'0','uid':'$uid'}"); ?>",
			{th: tahun, kc: kc, kl: kl, page:page}, function (response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#content" + tab).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
        }

        function showKelurahan(tab) {
            var id = $('select#kecamatan-' + tab).val()
            var request = $.ajax({
                url: "view/PBB/monitoring/svc-kecamatan.php",
                type: "POST",
                data: {id: id, kel: 1},
                dataType: "json",
                success: function (data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kelurahan-" + tab).html(options);
                    }
                }
            });
        }

        $(function () {
            $("#tabs").tabs();
			$("input:submit, input:button").button();
        });

        function showKecamatan(tab) {
            var request = $.ajax({
                url: "view/PBB/monitoring/svc-kecamatan.php",
                type: "POST",
                data: {id: "<?php echo  $appConfig['KODE_KOTA'] ?>"},
                dataType: "json",
                success: function (data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Pilih Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                        $("select#kecamatan-" + tab).html(options);
                    }
                }
            });

        }

        function showKecamatanAll() {
            var request = $.ajax({
                url: "view/PBB/monitoring/svc-kecamatan.php",
                type: "POST",
                data: {id: "<?php echo  $appConfig['KODE_KOTA'] ?>"},
                dataType: "json",
                success: function (data) {
                    var c = data.msg.length;
                    var options = '';
                    options += '<option value="">Semua</option>';
                    for (var i = 0; i < c; i++) {
                        options += '<option value="' + data.msg[i].id + '">' + data.msg[i].name + '</option>';
                    }
                        $("select#kecamatan-1").html(options);
                }
            });

        }

        $(document).ready(function () {
            showKecamatanAll();
            $('#tabs').tabs({
                select: function (event, ui) { // select event
                    $(ui.tab); // the tab selected
                    if (ui.index == 2) {
                        //showModelE2();
                    }
                }
            });
        });

    </script>
    <body>
        <div id="div-search" >
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1"><?php echo $tabCetak->tabCetakLabel; ?></a></li>
                </ul>
                <div id="tabs-1">
                    <?php $tabCetak->printTabCetak(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</body>
<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif"  style="margin-right: auto;margin-left: auto;"/>
    </div>
</div>
<div id="load-mask"></div>

<!-- <div id="tab-result"></div>
	<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
		<param name='printer' id='printer' value='".$printername."'>
		<param name='sleep' value='200'>
	</applet>
</div> -->
<script>
	// function changePrinterSuccess(params){
        // if(params.responseText){
            // if (params.responseText == "sukses") {
                // alert('Sukses melakukan pengaturan printer.');
                // document.location.reload(true);
            // } else {
                // alert('Gagal melakukan pengaturan printer');
            // }
        // } else {
            // alert('Gagal melakukan pengaturan printer');
        // }
    // }

    // function changePrinterFailure(params){
            // alert('Gagal melakukan pengaturan printer');
    // }

    // function changePrinter(printername, uid, m) {

            // var params = "{\"PRINTER\":\""+printername+"\", \"UID\":\""+uid+"\", \"MODULE\":\""+m+"\"}";
            
            // params = Base64.encode(params);
            // Ext.Ajax.request({
                    // url : 'function/PBB/print/svc-changeprinter.php',
                    // success: changePrinterSuccess,
                    // failure: changePrinterFailure,			
                    // params :{req:params}
            // });   

    // }
	
	// function listPrinter() {
		// var applet = document.jZebra;
		// if (applet != null) {
			// if (!applet.isDoneFinding()) window.setTimeout('listPrinter()', 1000);
			// else {
				// var listing = applet.getPrinters();
				// var printers = listing.split(',');
				// var printerslist=document.getElementById('selectedPrinter');

				// for(var i in printers){
					// printerslist.options[i]=new Option(printers[i]);
					// if(printers[i]=='<?php echo $printername?>'){
						// document.getElementById('selectedPrinter').selectedIndex = i;
                    // }
				// }
				// document.getElementById('printer').value = selectedPrinter.options[printerslist.selectedIndex].value;
			// }
		// } 
		// else  alert('Applet not loaded!');
	// }
	
	// function printdata(){
        // printCommand('<?php echo $application; ?>');
    // }
</script>