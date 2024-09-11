<?php
session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $a, $m, $data, $DIR;    
    echo "\t<ul>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/hotel/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'3','f':'fPatdaPersetujuan3','mod':'per'}") . "\">Tertunda</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/hotel/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'4','f':'fPatdaPersetujuan3','mod':'per'}") . "\">Ditolak</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/hotel/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'5','f':'fPatdaPersetujuan3','mod':'per'}") . "\">Disetujui</a></li>\n";
    echo "\t\t<li><a class='tab' href=\"view/{$DIR}/hotel/svc-list-pelaporan.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'0','f':'fPatdaPersetujuan3','mod':'per'}") . "\">Semua Data</a></li>\n";
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

