<?php
//session_start();
$DIR = "PATDA-V1";
$modul = "monitoring";
$submodul = "status-bayar";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-status-bayar.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaLapor2','mod':'pel'}") . "\">Sudah Bayar</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-status-bayar.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaLapor2','mod':'pel'}") . "\">Belum Bayar</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-belum-lapor.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'fPatdaLapor2','mod':'pel'}") . "\">Belum Lapor</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-grafik-filter.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'4','f':'fPatdaLapor2','mod':'pel'}") . "\">Grafik Laporan</a></li>\n";
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

    function toPdf(sts) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";

        var nmfileAll = (sts === 1 ? "sudahbayar" : "belumbayar") + "<?php echo date("dmYhis") ?>";
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["sptpd", "wp_alamat", "wp_nama", "simpatda_dibayar", "jenis", "expired_date1", "expired_date2", "simpatda_tahun_pajak", "simpatda_bulan_pajak", "bank"]
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
                var sumOfPage = Math.ceil(msg / 500);
                var strOfLink = "";
                if (msg < 500) {
                    str_data += "&nmfile=" + nmfileAll;
                    strOfLink += '<a target="'+nmfileAll+'" href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-topdf.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '.pdf</a><br/>';
                } else {
					
                    for (var page = 1; page <= sumOfPage; page++) {
                        var data = str_data + "&nmfile=" + nmfile + page;
                        strOfLink += '<a target="'+nmfile + page+'" href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-topdf.php?q=' + q + data + '&p=' + page + '">' + nmfile + page + '.pdf</a><br/>';
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");
                $("#loadlink-" + sts).hide();
            }
        });
    }
    
    function toExcel(sts) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";        
        var nmfileAll = (sts === 1 ? "sudahbayar" : "belumbayar") + "<?php echo date("dmYhis") ?>";
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["sptpd", "wp_alamat", "wp_nama", "simpatda_dibayar", "jenis", "expired_date1", "expired_date2", "simpatda_tahun_pajak", "simpatda_bulan_pajak", "bank"]
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

    function toExcelBelumLapor(sts) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";
        var nmfileAll = 'belumlapor<?php echo date('yymdhmi'); ?>';
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["CPM_TAHUN_PAJAK", "CPM_MASA_PAJAK", "CPM_JENIS_PAJAK", "CPM_NPWPD", "CPM_NAMA_WP"]
        var str_data = "";

        for (x = 0; x <= 8; x++) {
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
                    strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-belumlapor.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                }else {
                    for (var page = 1; page <= sumOfPage; page++) {
						var data = str_data + "&nmfile=" + nmfile + page;
                        strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-belumlapor.php?q=' + q + data + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");
                $("#loadlink-" + sts).hide();
            }
        });
    }

    function updateCount() {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";
        $("#ketAkm").html('<span style="font-size: 12px;">Loading...</span>');
        $.ajax({
            type: "POST",
            url: "./view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-countforsummary.php",
            data: "q=" + q,
            dataType: "json",
            success: function (res) {
                $("#ketAkm").html('<span style="font-size: 13px">Tahun Berjalan (<b>' + res.thnberjalan + '</b>) + Tunggakan (<b>' + res.tunggakan + '</b>) = Total Pembayaran (<b>' + res.total + '</b>)</span>');
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
<div id="cAkumulasi" style="position: absolute; top: 138px; right: 23px; display: block; overflow: auto;margin-top: 0px;">
    <div style="float: left; padding-top: 5px;" id="ketAkm"><span style="font-size: 13px; color:#1a1c20">Tahun Berjalan (<b>0</b>) + Tunggakan (<b>0</b>) = Total Pembayaran (<b>0</b>)</span></div>&nbsp;&nbsp;
    <input style="float: right;" type="button" name="updateCount" id="updateCount" value="Update" onClick="updateCount()"/>
</div>
