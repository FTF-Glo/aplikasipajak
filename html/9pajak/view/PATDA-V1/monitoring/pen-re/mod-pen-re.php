<?php
//session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "pen-re";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/pen-re/penre-list-opr.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':''}") . "\">Penetapan Dan Realisasi</a></li>\n";
    // echo "\t<li>Penetapan Dan Realisasi</li>\n";
    // echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-status-bayar.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaLapor2','mod':'pel'}") . "\">Sudah Bayar</a></li>\n";
    // echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-status-bayar.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaLapor2','mod':'pel'}") . "\">Belum Bayar</a></li>\n";
    // echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-belum-lapor.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'fPatdaLapor2','mod':'pel'}") . "\">Belum Lapor</a></li>\n";
    // echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-grafik-filter.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'4','f':'fPatdaLapor2','mod':'pel'}") . "\">Grafik Laporan</a></li>\n";
    echo "\t</ul>\n";
}
?>

<script LANGUAGE="Javascript" src="inc/FusionCharts/FusionCharts.js"></script> 
<script type="text/javascript">
    $(document).ready(function () {
        $("#tabsContent").tabs({
            beforeLoad: function (event, ui) {
                ui.panel.html('<img src="image/large-loading.gif" />');
            }
        });

        $("#closeCBox").click(function () {
            $("#cBox").css("display", "none");
        })
    });

    function toExcel(sts) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";        
        var nmfileAll = "penetapandanrealisasi" + "<?php echo date("dmYhis") ?>";//(sts === 1 ? "sudahbayar" : "belumbayar") + "<?php echo date("dmYhis") ?>"
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["JNS_PAJAK", "CPM_TAHUN_PAJAK", "CPM_MASA_PAJAK", "CPM_KECAMATAN", "CPM_KELURAHAN", "CPM_TGL_LAPOR1", "CPM_TGL_LAPOR2", "CPM_JENIS_RESTORAN"];
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
                var sumOfPage = Math.ceil(msg / 20000);
                var strOfLink = "";
                console.log('<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>');
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
    <?php 
    echo displayMenu();
    ?>
</div>
<div id="cBox" class="animate" style="width: 205px; height: 300px; position: absolute; right: 500px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
    <div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
        <div style="float: left;">
            <span style="font-size: 12px;">Link Download</span>
        </div>
        <div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>            
    </div>        
    <div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
</div>
