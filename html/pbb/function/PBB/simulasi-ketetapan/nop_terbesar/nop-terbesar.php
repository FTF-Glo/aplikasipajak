<?php 
/* 
Nama Developer (email) 				: Jajang Apriansyah (jajang@vsi.co.id)
Tanggal Development					: 18/11/2016
Tanggal Revisi (list) + Perubahan	: -
*/

$User 				= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appConfig 			= $User->GetAppConfig($a);
$_SESSION['uname'] 	= $data->uname;

function displayMenu() {
    global $a, $m, $data;
	echo '
		<div id="accordion">
		<h3>Pencarian NOP Terbesar</h3>
			<div style="width:619px;">
				<table>
					<tr>
						<td>Kecamatan</td>
						<td>
							<select name="OP_KECAMATAN" id="OP_KECAMATAN" onchange="showKel(this)"">
								<option value="">Kecamatan</option>
							</select>
						</td>
						<td>Kecamatan</td>
						<td>
							<div id="sKel">
								<select name="OP_KELURAHAN" id="OP_KELURAHAN">
									<option value="">Kelurahan</option>
								</select>
							</div>
						</td>
						<td>Kecamatan</td>
						<td>
							<div id="sBlok">
								<select name="BLOK" id="BLOK">
									<option value="">Blok</option>
								</select>
							</div>
						</td>
					</tr>
			</div>
		</div>';
}
?>

<div id="tabsContent">
    <?php echo displayMenu() ?>
</div>
<script type="text/javascript" src="inc/js/jquery-1.9.1.min.js"></script>	
<script type="text/javascript" src="inc/js/jquery-ui-1.9.1.js"></script>
<script type="text/javascript">
	$( function() {
		$( "#accordion" ).accordion();
	});
</script>