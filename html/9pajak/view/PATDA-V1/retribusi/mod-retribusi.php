<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu()
{
    global $DIR, $a, $m, $data;
// var_dump('asdsa');die;
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/retribusi/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaPelayanan7','mod':'pel'}") . "\">Data Retribusi <b class='notif draf_ply'></b></a></li>\n";
    // echo "\t\t<li><a class='tab' href=\"view/{$DIR}/retribusi/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaPelayanan7','mod':'pel'}") . "\">Proses <b class='notif proses_ply'></b></a></li>\n";
    // echo "\t\t<li><a class='tab' href=\"view/{$DIR}/retribusi/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'4','f':'fPatdaPelayanan7','mod':'pel'}") . "\">Ditolak <b class='notif ditolak_ply'></b></a></li>\n";
    // echo "\t\t<li><a class='tab' href=\"view/{$DIR}/retribusi/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'5','f':'fPatdaPelayanan7','mod':'pel'}") . "\">Disetujui <b class='notif disetujui_ply'></b></a></li>\n";
    
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
            data: "function=read_dokumen_notif&tab=draf_ply;proses_ply;ditolak_ply;disetujui_ply",
            url: "function/<?php echo $DIR ?>/retribusi/lapor/svc-lapor.php",
            dataType: "json",
            success: function(res) {
                $('.draf_ply').html(res.draf_ply + " new");
                $('.proses_ply').html(res.proses_ply + " new");
                $('.ditolak_ply').html(res.ditolak_ply + " new");
                $('.disetujui_ply').html(res.disetujui_ply + " new");

                if (res.draf_ply == 0)
                    $('.draf_ply').hide();
                if (res.proses_ply == 0)
                    $('.proses_ply').hide();
                if (res.ditolak_ply == 0)
                    $('.ditolak_ply').hide();
                if (res.disetujui_ply == 0)
                    $('.disetujui_ply').hide();
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


        var idTab = document.createElement("input");
        idTab.setAttribute("type", "hidden");
        idTab.setAttribute("name", 'i');
        idTab.setAttribute("value", id);
        form.appendChild(idTab);

        var app = document.createElement("input");
        app.setAttribute("type", "hidden");
        app.setAttribute("name", 'a');
        app.setAttribute("value", '<?php echo $a ?>');
        form.appendChild(app);

        var bulan = document.createElement("input");
        bulan.setAttribute("type", "hidden");
        bulan.setAttribute("name", 'BULAN');
        bulan.setAttribute("value", $('#BULAN-' + id).val());
        form.appendChild(bulan);

        var tahun = document.createElement("input");
        tahun.setAttribute("type", "hidden");
        tahun.setAttribute("name", 'TAHUN');
        tahun.setAttribute("value", $('#TAHUN-' + id).val());
        form.appendChild(tahun);


        var jenis_retribusi = document.createElement("input");
        jenis_retribusi.setAttribute("type", "hidden");
        jenis_retribusi.setAttribute("name", 'JENIS_RETRIBUSI');
        jenis_retribusi.setAttribute("value", $('#JENIS_RETRIBUSI-' + id).val());
        form.appendChild(jenis_retribusi);

        var jenis_penerimaan = document.createElement("input");
        jenis_penerimaan.setAttribute("type", "hidden");
        jenis_penerimaan.setAttribute("name", 'JENIS_PENERIMAAN');
        jenis_penerimaan.setAttribute("value", $('#JENIS_PENERIMAAN-' + id).val());
        form.appendChild(jenis_penerimaan);


        document.body.appendChild(form);
        form.submit();
        form.parentNode.removeChild(form);
    }
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>