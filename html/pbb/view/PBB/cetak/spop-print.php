<?php

if (!isset($data)) {
    return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

//prevent access to not accessible module
if (!$bOK) {
    return false;
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'cetak', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/PBB/dbLog.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig 	= $User->GetModuleConfig($module);
$appConfig 	= $User->GetAppConfig($application);
$dbLog 		= new DbLog($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbUtils 	= new DbUtils($dbSpec);

// Get User Area Config
$userArea = $dbUtils->getUserDetailPbb($uid);

if ($userArea == null) {
    echo "Aplikasi tidak dapat digunakan karena anda tidak terdaftar sebagai user PBB pada area manapun";
    return false;
} else {
    $userArea = $userArea[0];
}

function displayMenu() {  // srch
    global $a, $m, $srch;

  echo "\t<ul>\n";
  echo "\t\t<li><a href=\"view/PBB/cetak/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10'}") . "\">Pencetakan</a></li>\n";
  echo "\t</ul>\n";
}
?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<!-- 
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script> 
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>
<script type="text/javascript" src="function/PBB/consol/script.js"></script>-->

<script type="text/javascript">
	var userType = '<?php echo $arConfig['usertype']; ?>';
	var page = 1;
	function filKel (sel,sts) {
		if (sel==10) sel = 0;
		var kel = sts.value;
        $( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {kel: kel} } );
        $( "#tabsContent" ).tabs( "option", "selected", sel );
        $( "#tabsContent" ).tabs('load', sel); 
                                
	}
	
	function setTabs (sel,sts,np) {
		if (sel==10) sel = 0;
                var srch = $("#srch-"+sts).val();
                var almt = $("#almt-"+sts).val();
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {srch:srch,almt:almt} } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel); 
	}
	
	function setPage (sel,sts,np) {
		if (np==1) page++;
		else page--;
		if (sel==10) sel = 0; 
                var kel = $("#kel").val();
                var srch = $("#srch-"+sts).val();
                var almt = $("#almt-"+sts).val();
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {page:page,np:np, srch:srch, kel:kel, almt:almt} } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel); 
	}
	
    $(document).ready(function() {
        $( "input:submit, input:button").button();
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
	
	function printdata(){
		$("input:checkbox[name='check-all\\[\\]']").each(function(){
			if($(this).is(":checked")){			
				printCommand('<?php echo $a; ?>',$(this).val());
			}
		});
	}
</script>

<div id="tabsContent">
    <?php
        displayMenu();
    ?>
</div>
<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif"  style="margin-right: auto;margin-left: auto;"/>
    </div>
</div>
<div id="load-mask"></div>
