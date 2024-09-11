<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pemeliharaan-data-piutang', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);


$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;
function displayMenuPelayanan()
{
    // srch
    /** @var object $data */
    global $a, $m, $data;

    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/pemeliharaan-data-piutang/svc-pembentukan-daftar-nop.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname', 'uid':'$data->uid'}") . "\">Pembentukan Daftar NOP</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pemeliharaan-data-piutang/svc-perekaman-kategori-op.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'1', 'n':'2', 'u':'$data->uname', 'uid':'$data->uid'}") . "\">Perekaman Kategori OP</a></li>\n";
    echo "\t\t<li><a href=\"view/PBB/pemeliharaan-data-piutang/svc-list-nop.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'2', 'n':'3', 'u':'$data->uname', 'uid':'$data->uid'}") . "\">Daftar NOP</a></li>\n";
    echo "\t</ul>\n";
}
?>

<!-- <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script> -->
<!-- <script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script> -->

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> -->

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs/jq-3.6.0/dt-1.11.3/datatables.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jq-3.6.0/dt-1.11.3/datatables.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>

<script type="text/javascript">
    var page = 1;
    var tabsContent = $("#tabsContent").tabs();

    function setTabs(tab) {
        tabsContent.tabs('option', 'selected', tab);
        tabsContent.tabs('load', tab);
    }

    $(document).ready(function() {
        $("#all-check-button").click(function() {
            $('.check-all').each(function() {
                this.checked = $("#all-check-button").is(':checked');
            });
        });
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
<div id="tabsContent">
    <?php
    if (isset($tab)) {
        echo "<script language='javascript'>setTabs(" . $tab . ")</script>";
    }
    echo displayMenuPelayanan()
    ?>
</div>