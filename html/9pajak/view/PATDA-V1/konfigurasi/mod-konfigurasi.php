<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME,$DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/umum/svc-list-konfigurasi.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':'pel'}") . "\">Umum</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/tarif/svc-list-konfigurasi.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'','mod':'pel'}") . "\">Tarif</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/tarif/svc-list-konfigurasi-reklame.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'','mod':'pel'}") . "\">Tarif Khusus Reklame</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/sanksi/svc-list-konfigurasi.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'4','f':'','mod':'pel'}") . "\">Sanksi Terlambat Lapor</a></li>\n";
    // tambahan untuk target pajak
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/target/svc-list-konfigurasi.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'5','f':'','mod':'pel'}") . "\">Target Pajak</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/konfigurasi/umum/svc-list-konfigurasi-pejabat.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'6','f':'','mod':'pel'}") . "\">Pejabat</a></li>\n";
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
    });
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

