<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';

require_once($sRootPath."view/BPHTB/mod-display.php");

function displayMenuMonitoring() {  // srch
    global $a, $m, $data; 
    echo "\t<ul>\n";
    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'1', 'n':'1', 'u':'$data->uname'}") . "\">Rekapitulasi Pembayaran <br>Berdasarkan User</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'2', 'n':'2', 'u':'$data->uname'}") . "\">Rekapitulasi Pembayaran <br>Per User</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'3', 'n':'3', 'u':'$data->uname'}") . "\">Rekapitulasi Approval <br>Berdasarkan User</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'4', 'n':'4', 'u':'$data->uname'}") . "\">Rekapitulasi Approval <br>Per User</a></li>\n";
    echo "\t\t<li><a href=\"view/BPHTB/monitoring/svc-list-monitoring.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5', 'n':'5', 'u':'$data->uname'}") . "\">Rekapitulasi Persetujuan <br>yang siap bayar</a></li>\n";
    echo "\t</ul>\n";
}

?>
<link rel="stylesheet" href="view/BPHTB/notaris/mod-notaris.css" type="text/css">
<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script language="javascript">var uname='<?php echo $data->uname?>';</script>
<script language="javascript">var ap='<?php echo $a?>';</script>
<script type="text/javascript" src="view/BPHTB/monitoring/mod-monitoring.js"></script>

<script type="text/javascript">
	var page = 1;
	var axx='<?php echo base64_encode($a)?>';
	function setTabs (sts) {
		//setter
		var sel = 0;
		if (sts==1) sel = 0;
		if (sts==2) sel = 1;
		if (sts==3) sel = 2;
		if (sts==4) sel = 3;
                if (sts==5) sel = 4;
		
		var tgl1 = $("#src-tgl1-"+sts).val();
		var tgl2 = $("#src-tgl2-"+sts).val();
		var find = $("#src-approved-"+sts).val();
		var find_notaris = $("#src-notaris-"+sts).val();
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: { find: find,find_notaris:find_notaris,tgl1:tgl1,tgl2:tgl2 } } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel);		
	}
	
	function setPage (sts,np) {
		var sel = 0;
		if (sts==1) sel = 0;
		if (sts==2) sel = 1;
		if (sts==3) sel = 2;
		if (sts==4) sel = 3;
                if (sts==5) sel = 4;
                
		if (np==1) page++;
		else page--;
                var tgl1 = $("#src-tgl1-"+sts).val();
		var tgl2 = $("#src-tgl2-"+sts).val();
		var find = $("#src-approved-"+sts).val();
		var find_notaris = $("#src-notaris-"+sts).val();
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: { find: find,page:page,np:np,s :sts, find_notaris:find_notaris,tgl1:tgl1,tgl2:tgl2} } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel);
		
	}
	
	function getCheckedValue(buttonGroup,draf) {
	   // Go through all the check boxes. return an array of all the ones
	   // that are selected (their position numbers). if no boxes were checked,
	   // returned array will be empty (length will be zero)
	   var retArr = new Array();
	   
	   var lastElement = 0;
	   if (buttonGroup[0]) { // if the button group is an array (one check box is not an array)
		  for (var i=0; i<buttonGroup.length; i++) {
			 if (buttonGroup[i].checked) {
				retArr.length = lastElement;
				var arrObj = new Object ();
				arrObj.id = buttonGroup[i].value;
				arrObj.draf = draf;
				arrObj.axx = axx;
				arrObj.uname = "";
				retArr[lastElement] = arrObj;
				lastElement++;
			 }
		  }
	   } else { // There is only one check box (it's not an array)
		  if (buttonGroup.checked) { // if the one check box is checked
			 retArr.length = lastElement;
			 var arrObj = new Object ();
			 arrObj.id = buttonGroup[i].value;
				arrObj.draf = draf;
				arrObj.axx = axx;
				retArr[lastElement] = arrObj; // return zero as the only array value
		  }
	   }
	   return retArr;
	}
	
	function printDataToPDF (d) {
		var dt = getCheckedValue(document.getElementsByName('check-all'),d);
		var s = "";
		if (dt!="") {
			s = Ext.util.JSON.encode(dt);
		}
		//console.log(s);
		printToPDF(s)
	}

	
	$(document).ready(function() {
        /*$("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });*/
		
		
		
		

        $("#tabsContent").tabs({
            load: function (e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },

            select: function (e, ui) {
                var $panel = $(ui.panel);
				var d = $('#select-all').val();
                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });
		
		//getter
		//var selected = $( "#tabsContent" ).tabs( "option", "selected" );
		//console.log(selected);
		
	});
</script>

<div id="tabsContent">
	<?php echo displayMenuMonitoring() ?>
</div>