<?php
include_once("inc-config.php");
//include_once("image/captcha/securimage.php");
$nop	=isset($_POST["nop"])?$_POST["nop"]:"";
$idwp	=isset($_POST["idwp"])?$_POST["idwp"]:"";
$thn1	=isset($_POST["thn1"])?$_POST["thn1"]:"";
$thn2	=isset($_POST["thn2"])?$_POST["thn2"]:"";
$dt = GetListByNOP($nop,$idwp,$thn1,$thn2);
?>
<?php if(count($dt)>0){ 
		if($nop==""){
			$th = "NOP";
		} else{
			$th = "NAMA WAJIB PAJAK";
		}
?>
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
					</tr>
					<?php
					$total += ($dt['DENDA_PLUS_PBB']); 
					$i++;
				}
			?>
			<tr>
				<th class="text-right" colspan="5">TOTAL</th>
				<th class="text-right">Rp <?php echo $total; ?></th>
				<th class="text-center"></th>
			</tr>
		</tbody>
	</table>
<?php } ?>