<?php
// prevent direct access
// ini_set("display_errors", 1); error_reporting(E_ALL);

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

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penerimaan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbSpptHistory.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "function/PBB/gwlink.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbUtils = new DbUtils($dbSpec);

// Get User Area Config
$userArea = $dbUtils->getUserDetailPbb($uid);

if ($userArea == null) {
	echo "Aplikasi tidak dapat digunakan karena anda tidak terdaftar sebagai user PBB pada area manapun";
	return false;
} else {
	$userArea = $userArea[0];
}

function displayMenu()
{
	global $a, $m, $srch;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/penerimaan/page.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10'}") . "\">Data SPPT</a></li>\n";
	echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>
<script type="text/javascript" src="function/PBB/consol/script.js"></script>

<script type="text/javascript">
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
		$(window).keydown(function(event) {
			if (event.keyCode == 13) {
				event.preventDefault();
				return false;
			}
		});
	});

	function filKel(sel, sts) {
		var params = getParams(sel);
		loadTab(params);
	}

	function filTahun(sel, sts) {
		var params = getParams(sts);
		loadTab(params);
	}

	function setTabs(sel, sts, np) {
		var params = getParams(sts);
		loadTab(params);
	}

	function getParams(sts) {
		var kec = $('#kec').val();
		var kel = $('#kel').val();
		var thn = $('#tahun').val();
		var srch = $('#srch-' + sts).val();
		return params = {
			srch: srch,
			kec: kec,
			kel: kel,
			tahun: thn
		};
	}

	function loadTab(params) {
		sel = 0;
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: params
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}
</script>
<div class="col-md-12"></div>
<div id="tabsContent">
	<?php
	displayMenu();
	?>
</div>
</div>
<div id="load-content">
	<div id="loader">
		<img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
	</div>
</div>
<div id="load-mask"></div>