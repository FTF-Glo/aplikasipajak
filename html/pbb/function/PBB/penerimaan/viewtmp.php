<?php echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">"; ?>
<?php
$OP_LUAS_TANAH_VIEW = '0';
if (isset($OP_LUAS_TANAH)) {
	if (strrchr($OP_LUAS_TANAH, '.') != '') {
		$OP_LUAS_TANAH_VIEW = number_format($OP_LUAS_TANAH, 2, ',', '.');
	} else {
		$OP_LUAS_TANAH_VIEW = number_format($OP_LUAS_TANAH, 0, ',', '.');
	}
}

$OP_LUAS_BANGUNAN_VIEW = '0';
if (isset($OP_LUAS_BANGUNAN)) {
	if (strrchr($OP_LUAS_BANGUNAN, '.') != '') {
		$OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN, 2, ',', '.');
	} else $OP_LUAS_BANGUNAN_VIEW = number_format($OP_LUAS_BANGUNAN, 0, ',', '.');
}
?>
<div class="col-md-12">
	<form method="post" id="form-penerimaan">
		<input type="hidden" name="CPM_NOP" value="<?php echo isset($NOP) ? $NOP : "" ?>">
		<input type="hidden" name="CPM_TAHUN" value="<?php echo isset($SPPT_TAHUN_PAJAK) ? $SPPT_TAHUN_PAJAK : "" ?>">

		<div class="table-responsive">
			<table cellspacing=0 cellpadding=2 class="table table-bordered">
				<tr>
					<td colspan=3 align='center'>
						<h2>Surat Pemberitahuan Objek Pajak</h2>
					</td>
				</tr>
				<tr>
					<td colspan=3 class="title-header"><br>A. Data Objek Pajak</td>
				</tr>
				<tr>
					<td>1.</td>
					<td>NOP</td>
					<td><?php echo isset($NOP) ? $NOP : "" ?></td>
				</tr>
				<tr>
					<td>2.</td>
					<td>NOP Bersama</td>
					<td><?php echo isset($NOP_BERSAMA) ? $NOP_BERSAMA : "" ?></td>
				</tr>
				<tr>
					<td>3.</td>
					<td>Nama Jalan</td>
					<td><?php echo isset($OP_ALAMAT) ? $OP_ALAMAT : "" ?></td>
				</tr>
				<!-- <tr><td>4.</td><td>Blok/Kav/Nomor</td>
		<td><?#=isset($OP_NOMOR)?$OP_NOMOR:""?></td></tr> -->
				<tr>
					<td>4.</td>
					<td>RT</td>
					<td><?php echo isset($OP_RT) ? $OP_RT : "" ?></td>
				</tr>
				<tr>
					<td>5.</td>
					<td>RW</td>
					<td><?php echo isset($OP_RW) ? $OP_RW : "" ?></td>
				</tr>
				<tr>
					<td>6.</td>
					<td><?php echo $appConfig['LABEL_KELURAHAN']; ?></td>
					<td><?php echo isset($OP_KELURAHAN) ? $aOPKelurahan[0]['CPC_TKL_KELURAHAN'] : "" ?></td>
				</tr>
				<tr>
					<td></td>
					<td>Kecamatan</td>
					<td><?php echo isset($OP_KECAMATAN) ? $aOPKecamatan[0]['CPC_TKC_KECAMATAN'] : "" ?></td>
				</tr>
				<tr>
					<td></td>
					<td>Kab/kodya</td>
					<td><?php echo isset($OP_KOTAKAB) ? $aOPKabKota[0]['CPC_TK_KABKOTA'] : "" ?></td>
				</tr>

				<tr>
					<td colspan="3" class="title-header"><br>B. Data Subjek Pajak</td>
				</tr>
				<tr>
					<td>7.</td>
					<td>Status</td>
					<td><?php echo isset($WP_STATUS) ? $WP_STATUS : "" ?></td>
				</tr>
				<tr>
					<td>8.</td>
					<td>Pekerjaan</td>
					<td><?php echo isset($WP_PEKERJAAN) ? $WP_PEKERJAAN : "" ?></td>
				</tr>
				<tr>
					<td>9.</td>
					<td>Nama Subjek Pajak</td>
					<td><?php echo isset($WP_NAMA) ? $WP_NAMA : "" ?></td>
				</tr>
				<tr>
					<td>10.</td>
					<td>Nama Jalan</td>
					<td><?php echo isset($WP_ALAMAT) ? $WP_ALAMAT : "" ?></td>
				</tr>
				<tr>
					<td>11.</td>
					<td>RT</td>
					<td><?php echo isset($WP_RT) ? $WP_RT : "" ?></td>
				</tr>
				<tr>
					<td>12.</td>
					<td>RW</td>
					<td><?php echo isset($WP_RW) ? $WP_RW : "" ?></td>
				</tr>
				<tr>
					<td>13.</td>
					<td><?php echo $appConfig['LABEL_KELURAHAN']; ?></td>
					<td><?php echo isset($WP_KELURAHAN) ? $WP_KELURAHAN : "" ?></td>
				</tr>
				<tr>
					<td>14.</td>
					<td>Kecamatan</td>
					<td><?php echo isset($WP_KECAMATAN) ? $WP_KECAMATAN : "" ?></td>
				</tr>
				<tr>
					<td>15.</td>
					<td>Kab/kodya</td>
					<td><?php echo isset($WP_KOTAKAB) ? $WP_KOTAKAB : "" ?></td>
				</tr>
				<tr>
					<td>16.</td>
					<td>Kode Pos</td>
					<td><?php echo isset($WP_KODEPOS) ? $WP_KODEPOS : "" ?></td>
				</tr>
				<tr>
					<td>17.</td>
					<td>Nomor KTP</td>
					<td><?php echo isset($WP_NO_KTP) ? $WP_NO_KTP : "" ?></td>
				</tr>
				</tr>
				<tr>
					<td colspan=3 class="title-header"></td>
				</tr>
				<?php
				echo "<tr>
		  <td>&nbsp;</td>
		  <td width=\"39%\"><label for=\"tglPenerimaan\">Tanggal Penerimaan</label></td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"tglPenerimaan\" value=\"" . (isset($CPM_TANGGAL_PENERIMAAN) ? $CPM_TANGGAL_PENERIMAAN : date("d-m-Y")) . "\" id=\"tglPenerimaan\" size=\"50\" placeholder=\"Tanggal Penerimaan\"/>
		  </td>
		</tr>";
				echo "<tr>
	      <td>&nbsp;</td>
		  <td width=\"39%\"><label for=\"namaPenerima\">Nama Penerima</label></td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"namaPenerima\" value=\"" . (isset($CPM_NAMA_PENERIMA) ? $CPM_NAMA_PENERIMA : "") . "\" id=\"namaPenerima\" size=\"50\" placeholder=\"Nama Penerima\"/>
		  </td>
		</tr>";
				echo "<tr>
		  <td>&nbsp;</td>
		  <td width=\"39%\"><label for=\"noPenerima\">Nomor Kontak Penerima</label></td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"noPenerima\"  value=\"" . (isset($CPM_KONTAK_PENERIMA) ? $CPM_KONTAK_PENERIMA : "") . "\" id=\"noPenerima\" size=\"50\" placeholder=\"Nomor Kontak Penerima\"/>
		  </td>
		</tr>";

				echo "<tr><td colspan=3 class=\"title-header\"></td></tr>
			<tr>
			  <td colspan=\"4\" align=\"center\" valign=\"middle\"><br>
				<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" />&nbsp;
				<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Kembali\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"' />
			  </td>
			</tr>
		</table>";
				?>
			</table>
		</div>
	</form>
</div>


<script type="text/javascript">
	$('#tglPenerimaan').datepicker({
		dateFormat: 'dd-mm-yy'
	});
</script>
<style type="text/css">
	#btnClose {
		cursor: pointer;
	}

	.linkto:hover,
	.linkstpd:hover,
	.linkdate:hover {
		color: #ce7b00;
	}

	.linkto,
	.linkstpd,
	.linkdate {
		text-decoration: underline;
		cursor: pointer;
	}

	#load-mask,
	#load-content {
		display: none;
		position: fixed;
		height: 100%;
		width: 100%;
		top: 0;
		left: 0;
	}

	#load-mask {
		background-color: #000000;
		filter: alpha(opacity=70);
		opacity: 0.7;
		z-index: 1;
	}

	#load-content {
		z-index: 2;
	}

	#closeddate {
		cursor: pointer;
	}

	#loader {
		margin-right: auto;
		margin-left: auto;
		background-color: #ffffff;
		width: 100px;
		height: 100px;
		margin-top: 200px;
	}

	.table-penilaian th {
		background-color: #ffffff;
		color: #000000;
		padding-bottom: 4px;
		padding-top: 5px;
		text-align: center;
	}

	.table-penilaian td,
	.table-penilaian th {
		border: 1px solid #000000;
		padding: 3px 7px 2px;
		cellspacing: 0px;
	}

	.table-penilaian {
		border-collapse: collapse;
		width: 100%;
	}
</style>

<div id="load-content">
	<div id="loader">
		<img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
	</div>
</div>
<div id="load-mask"></div>