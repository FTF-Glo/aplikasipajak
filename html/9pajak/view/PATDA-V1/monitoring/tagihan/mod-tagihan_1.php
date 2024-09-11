<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/tagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':'per'}") . "\">Daftar Tagihan</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/tagihan/svc-list-tunggakan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'','mod':'per'}") . "\">Daftar Tunggakan (Belum Bayar)</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/monitoring/status-bayar/svc-list-belum-lapor.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'','mod':'per'}") . "\">Daftar Tunggakan (Belum Lapor)</a></li>\n";
    echo "\t</ul>\n";
}

$modul = "monitoring";
$submodul = "status-bayar";
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#tabsContent").tabs({
            beforeLoad: function (event, ui) {
                ui.panel.html('<img src="image/large-loading.gif" />');
            }
        });
        $.ajax({
            type: "post",
            data: "function=read_dokumen_notif&tab=tertunda;ditolak_ver;disetujui_ver",
            url: "function/<?php echo $DIR ?>/hotel/lapor/svc-lapor.php",
            dataType: "json",
            success: function (res) {
                $('.tertunda').html(res.tertunda + " new");
                $('.ditolak_ver').html(res.ditolak_ver + " new");
                $('.disetujui_ver').html(res.disetujui_ver + " new");

                if (res.tertunda == 0)
                    $('.tertunda').hide();
                if (res.ditolak_ver == 0)
                    $('.ditolak_ver').hide();
                if (res.disetujui_ver == 0)
                    $('.disetujui_ver').hide();
            }
        });
        $("#closeCBox").click(function () {
            $("#cBox").css("display", "none");
        })
    });
    
    function toExcel(sts) {
        var q = "<?php echo base64_encode("{'a':'{$a}', 'm':'{$m}', 's':'23','uid':'{$uid}'}"); ?>";
        var nmfileAll = "tagihan" + "<?php echo date("dmYhis") ?>";
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["sptpd", "wp_alamat", "wp_nama", "simpatda_dibayar", "jenis", "expired_date1", "expired_date2", "simpatda_tahun_pajak", "simpatda_bulan_pajak", "kecamatan", "kelurahan"]
        var str_data = "";

        for (x = 0; x < arr_field.length; x++) {
			if($("#" + arr_field[x] + "-" + sts).length){
				str_data += "&" + arr_field[x] + "=" + $("#" + arr_field[x] + "-" + sts).val();
			}
		}
		// sts 1 = tagihan, 2 = tunggakan
		// diclass 4 = tagihan, 5 = tunggakan
		str_data += "&i=" + (sts == 1? 4 : 5);
		str_data += "&s=" + (sts == 1? 4 : 5);

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
					strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-tunggakan-tagihan.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
				}else {
					for (var page = 1; page <= sumOfPage; page++) {
						var data = str_data + "&nmfile=" + nmfile + page;
						strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-tunggakan-tagihan.php?q=' + q + data + '&p=' + page + '">' + nmfile + page + '</a><br/>';
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
        var nmfileAll = '<?php echo date('yymdhmi'); ?>';
        var nmfile = nmfileAll + '-part-';

        var arr_field = ["CPM_TAHUN_PAJAK", "CPM_MASA_PAJAK", "CPM_JENIS_PAJAK", "CPM_NPWPD", "CPM_NAMA_WP", "kecamatan", "kelurahan"]
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
					str_data += '&nmfile=' + nmfileAll;
                    strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-belumlapor.php?q=' + q + str_data + '&p=all&total=' + msg + '">' + nmfileAll + '</a><br/>';
                }else {
                    for (var page = 1; page <= sumOfPage; page++) {
						str_data += "&nmfile=" + nmfile + page;
                        strOfLink += '<a href="view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-belumlapor.php?q=' + q + str_data + '&p=' + page + '">' + nmfile + page + '</a><br/>';
                    }
                }
                $("#contentLink").html(strOfLink);
                $("#cBox").css("display", "block");
                $("#loadlink-" + sts).hide();
            }
        });
    }

    function download_excel(id) {
        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", "./view/<?php echo "{$DIR}/{$modul}/{$submodul}" ?>/svc-toexcel-tagihan.php");

        var h = $("#hidden-" + id);
        var mod = h.attr('mod');
        var id_pajak = h.attr('id_pajak');
        var s = h.attr('s');

        var modul = document.createElement("input");
        modul.setAttribute("type", "hidden");
        modul.setAttribute("name", 'mod');
        modul.setAttribute("value", mod);
        form.appendChild(modul);

        var jenis_pajak = document.createElement("input");
        jenis_pajak.setAttribute("type", "hidden");
        jenis_pajak.setAttribute("name", 'CPM_JENIS_PAJAK');
        jenis_pajak.setAttribute("value", $('#CPM_JENIS_PAJAK-' + id).val());
        form.appendChild(jenis_pajak);

        var idTab = document.createElement("input");
        idTab.setAttribute("type", "hidden");
        idTab.setAttribute("name", 'i');
        idTab.setAttribute("value", id);
        form.appendChild(idTab);

        var status = document.createElement("input");
        status.setAttribute("type", "hidden");
        status.setAttribute("name", 's');
        status.setAttribute("value", s);
        form.appendChild(status);

        var app = document.createElement("input");
        app.setAttribute("type", "hidden");
        app.setAttribute("name", 'a');
        app.setAttribute("value", '<?php echo $a ?>');
        form.appendChild(app);

        var npwpd = document.createElement("input");
        npwpd.setAttribute("type", "hidden");
        npwpd.setAttribute("name", 'CPM_NPWPD');
        npwpd.setAttribute("value", $('#CPM_NPWPD-' + id).val());
        form.appendChild(npwpd);

        var stpd = document.createElement("input");
        stpd.setAttribute("type", "hidden");
        stpd.setAttribute("name", 'CPM_NO_STPD');
        stpd.setAttribute("value", $('#CPM_NO_STPD-' + id).val());
        form.appendChild(stpd);

        document.body.appendChild(form);
        form.submit();
        form.parentNode.removeChild(form);
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
