<?php
if (!isset($data)) {
    die();
}

//NEW: Check is terminal accessible
$arAreaConfig = $User->GetAreaConfig($area);
if (isset($arAreaConfig['terminalColumn'])) {
    $terminalColumn = $arAreaConfig['terminalColumn'];
    $accessible = $User->IsAccessible($uid, $area, $p, $terminalColumn);
    if (!$accessible) {
        echo "Illegal access";
        return;
    }
}

$arConfig = $User->GetModuleConfig($module);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
/* require_once($sRootPath . "inc/PBB/dbServices.php");
$dbServices = new DbServices($dbSpec); */
?>

<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">

	function setTabs (sel,sts) {
		if (sel==1) sel = 0;
        var srch = $("#srch-"+sts).val();
		var srchAlmt = $("#srchAlmt-"+sts).val();
		//alert(srch);
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {srch:srch,srchAlmt:srchAlmt} } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel); 
	}
	
	function setPage (sel,sts,np) {
		if (np==1) page++;
		else page--;
		if (sel==1) sel = 0;
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: {page:page,np:np} } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel); 
	}
	
    var page = 1;
    $(document).ready(function() {
 	
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
    <ul>
       <li><a href="function/PBB/loket/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'1', 'srch':'$srch'}"); ?>">Data Wajib Pajak</a></li>
    </ul>
</div>