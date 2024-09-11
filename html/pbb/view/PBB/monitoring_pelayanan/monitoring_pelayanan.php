<?php
// prevent direct access
if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);
$appConfig = $User->GetAppConfig($application);

//prevent access to not accessible module
if (!$bOK) {
	return false;
}

if (!isset($opt)) {
?>
	<!-- <link href="view/PBB/monitoring/monitoring.css?v0001" rel="stylesheet" type="text/css"/> -->
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script>
		var loadedSts = 1;

		function onSubmit(sts) {
			var keyword = $("#keyword" + sts).val();

			$("#monitoring-content-" + sts).html("<div align='center'><img src='/image/icon/loading-big.gif'></div>");
			$("#monitoring-content-" + sts).load("view/PBB/monitoring_pelayanan/svc-tracking-loket.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>", {
				keyword: keyword
			}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
				}
			});
		}

		$(function() {
			$("#tabs").tabs({
				select: function(event, ui) {
					console.log(event, ui)
				}
			});

			$("#monitoring-content-1").html("<div align='center'><img src='/image/icon/loading-big.gif'></div>");
			$("#monitoring-content-1").load("view/PBB/monitoring_pelayanan/svc-monitoring-pelayanan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>", {}, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
					loadedSts = sts;
				}
			});

			$('body').on('click', '#monitoring-search-btn', function() {
				let from_date = $('#monitoring-from-date'),
				to_date = $('#monitoring-to-date');
				
				if(from_date.val() || to_date.val()) {
					reload({
						from_date: from_date.val(),
						to_date: to_date.val()
					});
				}
			})

			$("#keyword2").keypress(function(e) {
				var key = e.which;
				if (key == 13) // the enter key code
				{
					onSubmit(2);
					event.preventDefault();
				}
			});

		});

		function reload(data = {}) {
			$("#monitoring-content-1").html("<div align='center'><img src='/image/icon/loading-big.gif'></div>");
			$("#monitoring-content-1").load("view/PBB/monitoring_pelayanan/svc-monitoring-pelayanan.php?q=<?php echo base64_encode("{'a':'$a', 'm':'$m', 's':'1','uid':'$uid'}"); ?>", data, function(response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$("#monitoring-content-" + sts).html(msg + xhr.status + " " + xhr.statusText);
					loadedSts = sts;
				}
			});
		}

	</script>

	<body>
		<div id="div-search">
			<div id="tabs">
				<ul>
					<li><a href="#tabs-1">Monitoring</a></li>
					<li><a href="#tabs-2">Tracking Loket</a></li>
				</ul>
				<div id="tabs-1">
					<button type="button" name="reload" id="reload" value="Refresh" onClick="reload(1)" class="btn btn-primary btn-orange">Refresh</button><br><br>
					<!-- <fieldset>
						Coming Soon
					</fieldset> -->
					<div id="monitoring-content-1" class="monitoring-content">
					</div>
				</div>
				<div id="tabs-2">
					<fieldset>
						<form id="TheForm-2" method="post" action="#" target="TheWindow">
							<table width="800" border="0" cellspacing="0" cellpadding="2">
								<tr>
									<td width="200" style="background-color: transparent">Masukan kata kunci pencarian </td>
									<td width="3" style="background-color: transparent">:</td>
									<td style="background-color: transparent">
										<input type="text" size="60" name="keyword2" id="keyword2" placeholder=" NOP/Nama " class="form-control">
									</td>
									<td style="background-color: transparent;padding-left:10px">
										<button type="button" name="button2" id="button2" value="Cari" onClick="onSubmit(2)" class="btn btn-primary btn-orange">Cari</button>
										<span id="loadlink" style="font-size: 10px; display: none;">Loading...</span>
									</td>
								</tr>
							</table>
						</form>
					</fieldset>
					<div id="monitoring-content-2" class="monitoring-content">
					</div>
				</div>
			</div>
		</div>
	<?php
}
	?>

	<div id="cBox" style="width: 205px; height: 300px; position: absolute; right: 50px; top: 200px; border: 1px solid gray; background-color: #eaeaea; display: none; overflow: auto;">
		<div style="overflow: auto; background-color: #c0c0c0; width: 198px; padding: 3px;">
			<div style="float: left;">
				<span style="font-size: 12px;">Link Download</span>
			</div>
			<div id="closeCBox" style=" padding: 3px; background-color: #eaeaea; border: 1px solid gray; float: right; cursor: pointer; margin-right: 5px;">Close</div>
		</div>
		<div id="contentLink" style="padding: 3px; width: 196px; height: 260px; overflow: auto;"></div>
	</div>
	</body>

	<script language="javascript">
		$(document).ready(function() {
			$("#closeCBox").click(function() {
				$("#cBox").css("display", "none");
			})
		})
	</script>