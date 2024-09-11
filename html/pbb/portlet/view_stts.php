<?php if(count($dt_stts)>0){ 
		if($nop==""){
			$th = "NOP";
		} else{
			$th = "NAMA WAJIB PAJAK";
		}
?>
<?php
	$thn1	=isset($_POST["tahun-pajak-1"])?$_POST["tahun-pajak-1"]:"";
	$thn2	=isset($_POST["tahun-pajak-2"])?$_POST["tahun-pajak-2"]:"";
?>
<!-- <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css"> 
<script src="bootstrap/js/jquery-3.1.1.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script> -->
<script>
// function viewtahun(){
// 		var nop= $("#nop").val();
// 		var idwp=$("#idwp").val();
// 		var thn1=$("#tahun-pajak-1").val();
// 		var thn2=$("#tahun-pajak-2").val();
//         $.ajax({
//             type: "POST",
//             url: "view_thn.php",
// 			data: {nop: nop, idwp: idwp, thn1:thn1, thn2:thn2}
//             }).done(function(data) {
//             $('#content').html(data);
//         });
//     }
</script>

<?php 
	// exit;
?>
<!-- <div class="form-inline pull-left">
	<div class="form-group">
		<select name="tahun-pajak-1" id="tahun-pajak-1" class="form-control">
          <option value="">Semua</option>
		  <?php
		  for ($t = $thn; $t > 1993; $t--) {
          ?> 
				<option value="<?php echo $t ?>"><?php echo $t ?></option>
          <?php    
               }
		  ?>
		  </select>
		  S/D
		  <select name="tahun-pajak-2" id="tahun-pajak-2" class="form-control">
          <option value="">Semua</option>
		  <?php
		  for ($t = $thn; $t > 1993; $t--) {
          ?> 
				<option value="<?php echo $t ?>"><?php echo $t ?></option>
          <?php    
               }
		  ?>
		  </select>
	  </div>
	  <div class="form-group">
		<button onclick="viewtahun()" class="col-sm-12 btn btn-success">Cari</button>
	  </div>
	</div> -->	

 	<div class="form-inline pull-right">
	  <div class="form-group">
		<!-- <button onclick="printToPDFSTTS('<?php echo $nop ?>','<?php echo $idwp ?>','cetak_ulang','budi','27-12-2017')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak STTS</button> -->
	  </div>
	  <!-- <div class="form-group">
		<button onclick="printToExcel('<?php echo $nop?>','<?php echo $idwp?>')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> Cetak Excel</button>
	  </div> -->
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
				<th class="text-center">STTS</th>
			</tr>
			</thead>
			<tbody>
			<?php 
				$i		= 1;
				$total  = 0;
				foreach($dt_stts as $dt_stts){
					if($nop==""){
						$dt_sttsNOP = substr($dt_stts['NOP'],0,2).'.'.substr($dt_stts['NOP'],2,2).'.'.substr($dt_stts['NOP'],4,3).'.'.substr($dt_stts['NOP'],7,3).'.'.substr($dt_stts['NOP'],10,3).'-'.substr($dt_stts['NOP'],13,4).'.'.substr($dt_stts['NOP'],17,1);
					} else {
						$dt_sttsNOP = $dt_stts['WP_NAMA'];
					}
					?>
					<tr>
						<td class="text-right"><?php echo $i; ?></td>
						<td><?php echo $dt_sttsNOP; ?></td>
						<td class="text-center"><?php echo $dt_stts['SPPT_TAHUN_PAJAK']; ?></td>
						<td class="text-right">Rp <?php echo $dt_stts['SPPT_PBB_HARUS_DIBAYAR']; ?></td>
						<td class="text-right">Rp <?php echo $dt_stts['DENDA']; ?></td>
						<td class="text-right">Rp <?php echo $dt_stts['SPPT_PBB_HARUS_DIBAYAR']+$dt_stts['PBB_DENDA']; ?></td>
						<td><?php

						echo $dt_stts['STATUS'];
						$date =  date("d-m-Y",strtotime($dt_stts['PAYMENT_PAID']));

						 ?></td>
						<td>
						<?php if ($dt_stts['STATUS']!="-"):  ?>
							<button onclick="printToPDFSTTS('<?php echo $dt_stts['NOP'] ?>','<?php echo $dt_stts['SPPT_TAHUN_PAJAK'] ?>','cetak_ulang','budi','$date')" class="col-sm-12 btn btn-success"><img src="./image/printer.png"/> </button>
						<?php endif; ?>

						</td>
					</tr>
					<?php
					$total += ($dt_stts['DENDA_PLUS_PBB']); 
					$i++;
				}
			?>
			<tr>
				<th class="text-right" colspan="5">TOTAL</th>
				<th class="text-right">Rp <?php echo $total; ?></th>
				<th class="text-center"></th>
				<th class="text-center"></th>
			</tr>
		</tbody>
	</table>
	</div>
	<div class="alert alert-info">
	  *Perhitungan Denda : 2% setiap bulan, maksimal 24 bulan.
	</div>
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

function printToPDFSTTS(nop,year,mode,uname,tgl) {
    var params = {nop:nop,year:year,mode:mode,uname:uname,tgl:tgl};
    params = Base64.encode(Ext.encode(params));
    window.open('stts-pdf.php?req='+params, '_newtab');
}
	// function printToPDF(nop,idwp){
	// 	var thn1=$("#tahun-pajak-1").val();
	// 	var thn2=$("#tahun-pajak-2").val();
	// 	console.log("print ...");
	// 	window.open("./print-pdf.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	// }
	// function printToExcel(nop,idwp){
	// 	var thn1=$("#tahun-pajak-1").val();
	// 	var thn2=$("#tahun-pajak-2").val();
	// 	console.log("print ...");
	// 	window.open("./print-excel.php?nop="+nop+"&idwp="+idwp+"&thn1="+thn1+"&thn2="+thn2, "_newtab");
	// }
</script>