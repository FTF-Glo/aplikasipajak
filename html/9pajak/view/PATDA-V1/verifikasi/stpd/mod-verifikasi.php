<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaVerifikasiPenagihan','mod':'ver'}") . "\">Tertunda <b class='notif tertunda'></b></a></li>\n";    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'4','f':'fPatdaVerifikasiPenagihan','mod':'ver'}") . "\">Ditolak <b class='notif ditolak'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'5','f':'fPatdaVerifikasiPenagihan','mod':'ver'}") . "\">Disetujui <b class='notif disetujui'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'0','f':'fPatdaVerifikasiPenagihan','mod':'ver'}") . "\">Semua Data</a></li>\n";
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
        $.ajax({
            type: "post",
            data: "function=read_dokumen_notif&tab=tertunda;ditolak;disetujui",
            url: "function/<?php echo $DIR ?>/penagihan/svc-penagihan.php",
            dataType: "json",
            success: function(res) {
                $('.tertunda').html(res.tertunda + " new");
                $('.ditolak').html(res.ditolak + " new");
                $('.disetujui').html(res.disetujui + " new");

                if (res.tertunda == 0)
                    $('.tertunda').hide();
                if (res.ditolak == 0)
                    $('.ditolak').hide();
                if (res.disetujui == 0)
                    $('.disetujui').hide();
            }
        });
    });
	
    function download_excel(id, url) {
        var form = document.createElement("form");
        form.setAttribute("method", 'post');
        form.setAttribute("target", 'excel');
        form.setAttribute("action", url);

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
        jenis_pajak.setAttribute("name", 'idp');
        jenis_pajak.setAttribute("value", id_pajak);
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

        var nama = document.createElement("input");
        nama.setAttribute("type", "hidden");
        nama.setAttribute("name", 'CPM_NAMA_WP');
        nama.setAttribute("value", $('#CPM_NAMA_WP-' + id).val());
        form.appendChild(nama);

        var tahun = document.createElement("input");
        tahun.setAttribute("type", "hidden");
        tahun.setAttribute("name", 'CPM_TAHUN_PAJAK');
        tahun.setAttribute("value", $('#CPM_TAHUN_PAJAK-' + id).val());
        form.appendChild(tahun);

        var bulan = document.createElement("input");
        bulan.setAttribute("type", "hidden");
        bulan.setAttribute("name", 'CPM_MASA_PAJAK');
        bulan.setAttribute("value", $('#CPM_MASA_PAJAK-' + id).val());
        form.appendChild(bulan);

        var tran_date1 = document.createElement("input");
        tran_date1.setAttribute("type", "hidden");
        tran_date1.setAttribute("name", 'CPM_TGL_LAPOR1');
        tran_date1.setAttribute("value", $('#CPM_TGL_LAPOR1-' + id).val());
        form.appendChild(tran_date1);

        var tran_date2 = document.createElement("input");
        tran_date2.setAttribute("type", "hidden");
        tran_date2.setAttribute("name", 'CPM_TGL_LAPOR2');
        tran_date2.setAttribute("value", $('#CPM_TGL_LAPOR2-' + id).val());
        form.appendChild(tran_date2);

        document.body.appendChild(form);
        form.submit();
    }	
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

