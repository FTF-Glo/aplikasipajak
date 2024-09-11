<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'Administrasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;
// echo $tab;
function displayMenuPelayanan()
{  // srch
    global $a, $m, $data;

    echo "<ul>";
    echo "<li><a href=\"view/Administrasi/jatuh_tempo/svc-list-data.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Jatuh Tempo</a></li>";
    echo "<li><a href=\"view/Administrasi/njoptkp/svc-list-data.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">NJOPTKP</a></li>";
    echo "<li><a href=\"view/Administrasi/tarif/svc-list-data.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Tarif</a></li>";
    echo "</ul>";
}
?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript">
    var page = 1;

    function setTabs(tab) {
        // $( "#tabsContent" ).tabs( "option", "selected", tab );
        // $( "#tabsContent" ).tabs('load', tab);
    }

    $(document).ready(function() {

        $("#tabsContent").tabs({
            load: function(e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function(e, ui) {
                var $panel = $(ui.panel);

                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });

    });
</script>
<div class="col-md-12">
    <div id="tabsContent">
        <?php
        if (isset($tab)) {
            echo "<script language='javascript'>setTabs(" . $tab . ")</script>";
        }
        echo displayMenuPelayanan()
        ?>
    </div>
</div>