<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'perubahan_znt_massal', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$tab  = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 0;
function displayMenuPelayanan() {  // srch
    global $a, $m, $data;
    
	echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/PBB/perubahan_znt_massal/svc-perubahan-znt-massal.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Perubahan ZNT Massal</a></li>\n";

      echo "\t\t<li><a href=\"view/PBB/perubahan_znt_massal/svc-perubahan-znt-massal-multi.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 'tab':'0', 'n':'1', 'u':'$data->uname'}") . "\">Perubahan ZNT Massal CSV</a></li>\n";
    echo "\t</ul>\n";
}
?>

<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<link href="view/PBB/pembatalan-sppt/monitoring.css" rel="stylesheet" type="text/css"/> 

<script type="text/javascript">
	var page = 1;
	
	function setTabs (tab) {
		$( "#tabsContent" ).tabs( "option", "selected", tab );
		$( "#tabsContent" ).tabs('load', tab);
	}
	
	$(document).ready(function() {        
        $("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });	
        $("#tabsContent").tabs({
            load: function (e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },
            select: function (e, ui) {
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
		if(isset($tab)){
			echo "<script language='javascript'>setTabs(".$tab.")</script>";
		}        
		echo displayMenuPelayanan() 
	?>
</div>
