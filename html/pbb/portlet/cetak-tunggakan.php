<br>
   		<div class="row">
		<div class="col-sm-4"></div>
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-body">
					<!-- <img class="img-responsive" src="head.png" alt="Logo"> -->
					<span align="center"><h4>Daftar Tunggakan</h4></span>
					<form class="form-horizontal" method="POST">
					  <input type='hidden' maxlength='32' name='fungsi' value='cek-tunggakan'>
					  <input type='hidden' maxlength='32' name='area' value='<?php echo $area; ?>'>
					  <input type='hidden' maxlength='32' name='client' value='<?php echo $client; ?>'>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="idwp">Kode VA Bank</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="idwp" name="idwp" value="<?php echo "11046"?>" placeholder="Masukan Kode VA Bank">
						</div>
					  </div>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="kode_bayar">Kode Bayar</label>
						<div class="col-sm-7"> 
							<input type="text" class="form-control" id="kode_bayar" name="kode_bayar" value="<?php echo "1180401798";?>" placeholder="Masukan Kode Bayar">
						</div>
					  </div>
					   <div class="form-group">
						<label class="control-label col-sm-5" for="id_transaksi">ID Transaksi</label>
						<div class="col-sm-7"> 
							<input type="text" class="form-control" id="id_transaksi" name="id_transaksi" value="<?php echo "1180401798" ?>" placeholder="Masukan ID Transaksi">
						</div>
					  </div>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="nop">KODE VERIFIKASI</label>
						<div class="col-sm-7"> 
							<img src='captcha2.php' alt='Captcha Image' id='captcha-image' />
						</div>
					  </div>
					  <div class="form-group"> 
						<div class="col-sm-offset-5 col-sm-7">
							<input type="text" class="form-control" name="cImage_2" id="cImage_2" value="" maxlength="10" autocomplete="off" required="" placeholder="Masukan kode verifikasi">
						</div>
					  </div>
					  <div class="form-group">
						<div class="col-sm-12"> 
							<button type="submit" class="col-sm-12 btn btn-success">Cari</button>
						</div>
					  </div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-sm-4"></div>
	</div>
	<div class="row">
		<div class="col-sm-2"></div>
		<div class="col-sm-8">
			<?php
				
				$fungsi = $_REQUEST['fungsi'];
			
				$img = new Securimage();
				$equal = ($img->check($cImage_2) );
				if($equal){
					if ($fungsi=="cek-tunggakan"){
						echo "masuk cek tunggakan";
						$dt = GetListByNOP($nop,$idwp,"","");
						require_once('view_data.php');
					}
					$dt = GetListByNOP($nop,$idwp,"","");
					require_once('view_data.php');
					displayChecker();
				}else{
					echo '<div class="alert alert-danger">
							<strong>Kode Verifikasi Harus Benar </strong>
						  </div>';
				}
			?>
		</div>
		<div class="col-sm-2"></div>
	</div>
