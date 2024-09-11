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

$arConfig = $User->GetModuleConfig($m);
//echo $arConfig['usertype'];

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/PBB/dbServices.php");
$dbServices = new DbServices($dbSpec);

//Kirim Massal
if (isset($_REQUEST['btn-kirim']) && isset($_REQUEST['check-all'])) {
	foreach ($_REQUEST['check-all'] as $id) {
		$today = date("Y-m-d");
		$bVal['CPM_STATUS'] = 2;
		$bVal['CPM_VALIDATOR'] = $uid;
		$bVal['CPM_DATE_VALIDATE'] = $today;
		$dbServices->editServices($id, $bVal);
	}
}
?>

<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />

<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
	var userType = '<?php echo $arConfig['usertype']; ?>';

	function filKel(sel, sts) {
		if (userType == 'pendata') {
			if (sel == 1) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 56) sel = 2;
		}

		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 5) sel = 2;
			if (sel == 3) sel = 3;
		}
		if (userType == 'penyetuju') {
			if (sel == 33) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 6) sel = 2;
			if (sel == 4) sel = 3;
		}
		var kel = sts.value;
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: {
				kel: kel
			}
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);

	}

	function setTabs(sel, sts) {
		if (userType == 'pendata') {
			if (sel == 1) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 56) sel = 2;
		}
		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 5) sel = 2;
			if (sel == 3) sel = 3;
		}
		if (userType == 'penyetuju') {
			if (sel == 33) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 6) sel = 2;
			if (sel == 4) sel = 3;
		}
		var srch = $("#srch-" + sts).val();
		//alert(srch);
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: {
				srch: srch
			}
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}

	function setPage(sel, sts, np) {
		if (np == 1) page++;
		else page--;
		if (userType == 'pendata') {
			if (sel == 1) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 56) sel = 2;
		}
		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 5) sel = 2;
			if (sel == 3) sel = 3;
		}
		if (userType == 'penyetuju') {
			if (sel == 33) sel = 0;
			if (sel == 23) sel = 1;
			if (sel == 6) sel = 2;
			if (sel == 4) sel = 3;
		}
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: {
				page: page,
				np: np
			}
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}

	var page = 1;
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

	function printdata() {
		$("input:checkbox[name='check-all\\[\\]']").each(function() {
			if ($(this).is(":checked")) {
				printCommand('<?php echo $a; ?>', $(this).val());
			}
		});
	}
</script>
<div class="col-md-12">
	<div id="tabsContent">
		<ul>
			<?php if ($arConfig['usertype'] == "pendata") { ?>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'1'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'1'}"); 
														?>">Tertunda</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); //echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); 
														?>">Dalam Proses</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'56'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'56'}"); 
														?>">Ditolak</a></li>
			<?php } elseif ($arConfig['usertype'] == "verifikator") { ?>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'2'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'2'}"); 
														?>">Tertunda</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); 
														?>">Dalam Proses</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'5'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'5'}"); 
														?>">Ditolak</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'3'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'3'}"); 
														?>">Disetujui</a></li>
			<?php } elseif ($arConfig['usertype'] == "penyetuju") { ?>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'33'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'33'}"); 
														?>">Tertunda</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'23'}"); 
														?>">Dalam Proses</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'6'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'6'}"); 
														?>">Ditolak</a></li>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'4'}"); //base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'4', 's':'4'}"); 
														?>">Disetujui</a></li>
			<?php } ?>
		</ul>
	</div>
</div>