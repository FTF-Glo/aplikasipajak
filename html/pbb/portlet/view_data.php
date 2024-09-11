
<?php if(count($dt)>0){ 
		if($nop==""){
			$th = "NOP";
		} else{
			$th = "NAMA WAJIB PAJAK";
		}
?>
<?php
	$thn1	=isset($_POST["thn1"])?$_POST["thn1"]:"";
	$thn2	=isset($_POST["thn2"])?$_POST["thn2"]:"";
?>
<script>
function viewtahun(){
		var nop= $("#nop").val();
		var idwp=$("#idwp").val();
		var thn1=$("#tahun-pajak-1").val();
		var thn2=$("#tahun-pajak-2").val();
        $.ajax({
            type: "POST",
            url: "view_data.php",
			data: {nop: nop, idwp: idwp, thn1:thn1, thn2:thn2}
            }).done(function(data) {
            $('#content').html(data);
        });
    }
</script>

<?php 
	// exit;
?>
<div class="form-inline pull-left">
	<form action="portlet.php" method="post">
		<input type='hidden' maxlength='32' name='fungsi' value='cek-tagihan'>
		<input type='hidden' maxlength='32' name='area' value='<?php echo $area; ?>'>
		<input type='hidden' maxlength='32' name='client' value='<?php echo $client; ?>'>
		<input type="hidden" id="idwp" name="idwp" value="<?php echo $idwp;?>">
		<input type="hidden" id="nop" name="nop" value="<?php echo $nop;?>">


		<div class="form-group">
			<select name="thn1" id="tahun-pajak-1" class="form-control">
			<option value="">Semua</option>
			<?php
			for ($t = $thn; $t > 1993; $t--) {
			?> 
					<option value="<?php echo $t ?>" <?= ($thn1 == $t) ? "selected" : "" ?>><?php echo $t ?></option>
			<?php    
				}
			?>
			</select>
			S/D
			<select name="thn2" id="tahun-pajak-2" class="form-control">
			<option value="">Semua</option>
			<?php
			for ($t = $thn; $t > 1993; $t--) {
			?> 
					<option value="<?php echo $t ?>" <?= ($thn2 == $t) ? "selected" : "" ?>><?php echo $t ?></option>
			<?php    
				}
			?>
			</select>
		</div>
		<div class="form-group">
			<button type="submit" class="col-sm-12 btn btn-success">Cari</button>
		</div>
	</form>
</div>	
	<div class="form-inline pull-right">
	  <div class="form-group">
		<button onclick="printToPDF('<?php echo $nop?>','<?php echo $idwp?>')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak PDF</button>
	  </div>
	  <div class="form-group">
		<button onclick="printToExcel('<?php echo $nop?>','<?php echo $idwp?>')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak Excel</button>
	  </div>
	</div>
	<br>
	<br>
	<div id="content">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center">NO</th>
				<th class="text-center"><?php echo $th; ?></th>
				<th class="text-center">TAHUN PAJAK</th>
				<th class="text-center">PBB</th>
				<th class="text-center">DENDA (*)</th>
				<th class="text-center">KURANG BAYAR</th>
				<th class="text-center">STATUS BAYAR</th>
				<th class="text-center">KODE BAYAR</th>
			</tr>
			</thead>
			<tbody>
			<?php 
				$i		= 1;
				$total  = 0;
				foreach($dt as $dt){
					if($nop==""){
						$dtNOP = substr($dt['NOP'],0,2).'.'.substr($dt['NOP'],2,2).'.'.substr($dt['NOP'],4,3).'.'.substr($dt['NOP'],7,3).'.'.substr($dt['NOP'],10,3).'-'.substr($dt['NOP'],13,4).'.'.substr($dt['NOP'],17,1);
					} else {
						$dtNOP = $dt['WP_NAMA'];
					}
					?>
					<tr>
						<td class="text-right"><?php echo $i; ?></td>
						<td><?php echo $dtNOP; ?></td>
						<td class="text-center"><?php echo $dt['SPPT_TAHUN_PAJAK']; ?></td>
						<td class="text-right">Rp <?php echo $dt['SPPT_PBB_HARUS_DIBAYAR']; ?></td>
						<td class="text-right">Rp <?php echo $dt['DENDA']; ?></td>
						<td class="text-right">Rp <?php echo $dt['DENDA_PLUS_PBB']; ?></td>
						<td><?php echo $dt['STATUS']; ?></td>
						<td class="text-center"><?php 
						if (empty($dt['PAYMENT_FLAG']) || $dt['PAYMENT_FLAG']===NULL ){	
							echo $dt['PAYMENT_CODE'];
						}else{
							// var_dump($dt['PAYMENT_CODE']);
							echo "<i>LUNAS</i>";
						}

						 ?></td>
					</tr>
					<?php
					$total += ($dt['DENDA_PLUS_PBB_XLS']); 
					$i++;
				}
			?>
			<tr>
				<th class="text-right" colspan="5">TOTAL</th>
				<th class="text-right">Rp <?php echo number_format($total); ?></th>
				<th class="text-center"></th>
				<th class="text-center"></th>
			</tr>
		</tbody>
	</table>
	</div>
	<!-- <div id="content"> -->
	<div class="alert alert-info">
	  *Untuk Pembayaran menggunakan Bank Lampung<br>
	  **Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.
	</div>
	<!-- <div class="alert alert-info"></div> -->
<?php 
	} else {
		?>
		<div class="alert alert-danger">
			<strong>Perhatian!</strong> Data tidak ditemukan.
		</div>
		<?php
	}
?>
<script type="text/javascript">
	 function printToPDF(nop,idwp){
	 	var thn1=$("#tahun-pajak-1").val();
	 	var thn2=$("#tahun-pajak-2").val();
	 	console.log("print ...");
	 	window.open("./print-pdf.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	 }
	 function printToExcel(nop,idwp){
	 	var thn1=$("#tahun-pajak-1").val();
	 	var thn2=$("#tahun-pajak-2").val();
	 	console.log("print ...");
	 	window.open("./print-excel.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	 }
</script>
