<?php
//session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "status-dok";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    echo "\t<ul>\n";    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':'x'}") . "\">Pajak Air</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'','mod':'x'}") . "\">Pajak Hiburan</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'','mod':'x'}") . "\">Pajak Hotel</a></li>\n";
    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'4','f':'','mod':'x'}") . "\">Pajak Mineral</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'5','f':'','mod':'x'}") . "\">Pajak Parkir</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'6','f':'','mod':'x'}") . "\">Pajak Jalan</a></li>\n";
    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'7','s':'7','f':'','mod':'x'}") . "\">Pajak Reklame</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'8','s':'8','f':'','mod':'x'}") . "\">Pajak Restoran</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'9','s':'9','f':'','mod':'x'}") . "\">Pajak Walet</a></li>\n";
    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/skpdkb/svc-list-skpdkb.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'10','s':'10','f':'fPatdaPenetapan','mod':'ver'}") . "\">SKPDKB</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/monitoring/status-dok/stpd/svc-list-stpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'11','s':'11','f':'fPatdaPenagihan','mod':'per'}") . "\">STPD</a></li>\n";
    echo "\t</ul>\n";
}
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#tabsContent").tabs({
            beforeLoad: function(event, ui) {
                ui.panel.html('<img src="image/large-loading.gif" />');
            }
        });
        
        $("#closeCBox").click(function () {
            $("#cBox").css("display", "none");
        })
    });
	
	function toExcel(sts) {
		
		var label = $('#CPM_TRAN_STATUS-'+sts+' option:selected').text();
		var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";        
        var nmfileAll = 'sptpd-'+label.toLowerCase() + "-<?php echo date("dmYhis") ?>";
        var nmfile = nmfileAll + '-part-';

        var arr_field = ['CPM_NPWPD', 'CPM_NO', 'CPM_TAHUN_PAJAK', 'CPM_MASA_PAJAK', 'CPM_TGL_LAPOR1', 'CPM_TGL_LAPOR2', 'CPM_TRAN_STATUS', 'CPM_KECAMATAN', 'CPM_KELURAHAN']
        var str_data = "";

        for (x = 0; x < arr_field.length; x++) {
            str_data += "&" + arr_field[x] + "=" + $("#" + arr_field[x] + "-" + sts).val();
        }
        str_data += "&i=" + sts;

        $("#loadlink-" + sts).show();

        $.ajax({
            type: "POST",
            url: "./view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-countforlink.php",
            data: "q=" + q + str_data,
            success: function (msg) {
                console.log(msg)
                var sumOfPage = Math.ceil(msg / 20000);
                var strOfLink = "";
                if (msg < 20000){
					str_data += "&nmfile=" + nmfileAll;
                    strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                }else {
                    for (var page = 1; page <= sumOfPage; page++) {
						var data = str_data + "&nmfile=" + nmfile + page;
                        strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel.php?q=' + q + data + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");
                $("#loadlink-" + sts).hide();
            }
        });
    }
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

<div id="cBox" class="animate" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>            
    </div>        
    <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
</div>
