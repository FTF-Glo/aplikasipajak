<?php
// prevent direct access
// ini_set("display_errors", 1); error_reporting(E_ALL);
// echo mysqli_error($DBLink);

if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

// prevent access to not accessible module
if (!$bOK) {
	return false;
}

$arConfig = $User->GetModuleConfig($module);
?>

<!-- <link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/> -->

<!-- <link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/> -->
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>

<script type="text/javascript">
	var userType = '<?php echo $arConfig['usertype']; ?>';

	function filKel(sel, sts) {
		if (userType == 'pendata') {
			if (sel == 1) sel = 0;
		}

		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
		}
		if (userType == 'penyetuju') {
			if (sel == 3) sel = 0;
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
		}

		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
		}
		if (userType == 'penyetuju') {
			if (sel == 3) sel = 0;
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
		}

		if (userType == 'verifikator') {
			if (sel == 2) sel = 0;
		}
		if (userType == 'penyetuju') {
			if (sel == 3) sel = 0;
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
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'', 's':'1'}"); ?>">Penerimaan Laporan</a></li>
			<?php } elseif ($arConfig['usertype'] == "verifikator") { ?>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'', 's':'2'}"); ?>">Penerimaan Verifikasi</a></li>
			<?php } elseif ($arConfig['usertype'] == "penyetuju") { ?>
				<li><a href="view/PBB/mutasi/page.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 'f':'$f', 't':'', 's':'3'}"); ?>">Penerimaan Persetujuan</a></li>
			<?php } ?>
		</ul>
	</div>
</div>