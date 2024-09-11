<?php
session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";    
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'','mod':'x'}") . "\">Pajak Air</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'','mod':'x'}") . "\">Pajak Hiburan</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'3','s':'3','f':'','mod':'x'}") . "\">Pajak Hotel</a></li>\n";
    
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'4','s':'4','f':'','mod':'x'}") . "\">Pajak Mineral</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'5','s':'5','f':'','mod':'x'}") . "\">Pajak Parkir</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'6','s':'6','f':'','mod':'x'}") . "\">Pajak Jalan</a></li>\n";
    
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'7','s':'7','f':'','mod':'x'}") . "\">Pajak Reklame</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'8','s':'8','f':'','mod':'x'}") . "\">Pajak Restoran</a></li>\n";
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-sptpd.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'9','s':'9','f':'','mod':'x'}") . "\">Pajak Walet</a></li>\n";
    
    echo "\t\t<li><a href=\"view/{$DIR}/pembatalan/sptpd/svc-list-log.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'10','s':'10','f':'','mod':'x'}") . "\">LOG PEMBATALAN</a></li>\n";
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
		$('.start-date').datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			changeMonth: true,
			showOn: "button",
			buttonImageOnly: false,
			buttonText: "...",
			onSelect: function(dateText) {
				$(this).change();
			}
		});
		$('.end-date').datepicker({
			dateFormat: 'dd/mm/yy',
			changeYear: true,
			changeMonth: true,
			showOn: "button",
			buttonImageOnly: false,
			buttonText: "...",
			onSelect: function(dateText) {
				$(this).change();
			}
		});		
    });
</script>
<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>

