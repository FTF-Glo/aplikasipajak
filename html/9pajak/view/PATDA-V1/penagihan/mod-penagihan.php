<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";    
    #echo "\t\t<li><a href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'6','f':'fPatdaPenagihan','mod':'pel'}") . "\">Daftar Tagihan Expire</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaPenagihan','mod':'pel'}") . "\">Daftar WP</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaPenagihan','mod':'pel'}") . "\">Proses <b class='notif proses'></b></a></li>\n";    
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'4','f':'fPatdaPenagihan','mod':'pel'}") . "\">Ditolak <b class='notif ditolak'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'5','f':'fPatdaPenagihan','mod':'pel'}") . "\">Disetujui <b class='notif disetujui'></b></a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/penagihan/svc-list-tagihan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'0','f':'fPatdaPenagihan','mod':'pel'}") . "\">Semua Data</a></li>\n";
    
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
            data: "function=read_dokumen_notif&tab=proses;ditolak;disetujui",
            url: "function/<?php echo $DIR ?>/penagihan/svc-penagihan.php",
            dataType: "json",
            success: function(res) {
                $('.proses').html(res.proses + " new");
                $('.ditolak').html(res.ditolak + " new");
                $('.disetujui').html(res.disetujui + " new");

                if (res.proses == 0)
                    $('.proses').hide();
                if (res.ditolak == 0)
                    $('.ditolak').hide();
                if (res.disetujui == 0)
                    $('.disetujui').hide();
            }
        });
    });
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>


