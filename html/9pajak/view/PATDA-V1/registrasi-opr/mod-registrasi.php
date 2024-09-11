<?php
//session_start();
$DIR = "PATDA-V1";
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$styleFolder = $User->GetLayoutUser($uid);
$_SESSION['uname'] = $data->uname;

function displayMenu() {
    global $DIR, $a, $m, $data;
    
    echo "\t<ul>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/registrasi-opr/svc-list-opr.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'1','s':'1','f':'fPatdaRegOpr','mod':''}") . "\">Operator Aktif</a></li>\n";
    echo "\t\t<li><a  class='tab' href=\"view/{$DIR}/registrasi-opr/svc-list-opr.php?q=" . base64_encode("{'a':'$a', 'm':'$m','u':'$data->uname','i':'2','s':'2','f':'fPatdaRegOpr','mod':''}") . "\">Operator Blokir</a></li>\n";
    
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

