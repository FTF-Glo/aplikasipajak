<br>
   		<div class="row">
		<div class="col-sm-4"></div>
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-body">
				<?php 
				// var_dump($kode_va);
				// print_r($_REQUEST);
				?>
					<!-- <img class="img-responsive" src="head.png" alt="Logo"> -->
					<span align="center"><h4>Cetak Struk STTS (Bukti Bayar)</h4></span>
					<hr>
					<form class="form-horizontal" method="POST">
					  <input type='hidden' maxlength='32' name='fungsi' value='cek-stts'>
					  <input type='hidden' maxlength='32' name='area' value='<?php echo $area; ?>'>
					  <input type='hidden' maxlength='32' name='client' value='<?php echo $client; ?>'>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="kode_va">Kode VA Bank</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" id="kode_va" name="kode_va" 
							value="<?php echo $_REQUEST[kode_va] ?>" placeholder="Masukan Kode VA Bank">
						</div>
					  </div>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="kode_bayar">Kode Bayar</label>
						<div class="col-sm-7"> 
							<input type="text" class="form-control" id="kode_bayar" name="kode_bayar" 
							value="<?php echo $_REQUEST[kode_bayar] ?>" placeholder="Masukan Kode Bayar">
						</div>
					  </div>
					   <div class="form-group">
						<label class="control-label col-sm-5" for="id_transaksi">ID Transaksi</label>
						<div class="col-sm-7"> 
							<input type="text" class="form-control" id="id_transaksi" name="id_transaksi" 
							value="<?php echo $_REQUEST[id_transaksi] ?>"  placeholder="Masukan ID Transaksi">
						</div>
					  </div>	
					  <div class="form-group">
						<label class="control-label col-sm-5" for="nop">Kode Verifikasi</label>
						<div class="col-sm-7"> 
								<img src='image-2018/securimage_show.php?namespace=kolektif' alt='Captcha Image' id='captcha-image2' />
						</div>
					  </div>
					  <div class="form-group"> 
						<div class="col-sm-offset-5 col-sm-7">
							<input type="text" class="form-control" name="cImage_2" id="cImage_2" value="" maxlength="10" autocomplete="off" required="" placeholder="Masukan kode verifikasi">
						</div>
					  </div>
					  <div class="form-group">
						<div class="col-sm-12"> 
							<button type="submit" class="col-sm-5 btn btn-success" style="margin-right:20px">Lihat</button>
							<button type="reset" class="col-sm-5 btn btn-danger">Reset</button>
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
				$kode_va = $_REQUEST['kode_va'];
				$kode_bayar = $_REQUEST['kode_bayar'];
				$id_transaksi = $_REQUEST['id_transaksi'];
				

			$img2 = new Securimage();
			$img2->namespace = "kolektif";
			$equal2 = ($img2->check($cImage_2) );
			// var_dump($cImage_2);
			// var_dump($equal2);
			
			if ($fungsi=="cek-stts"){
				// $img2->code_length = 6;
				// $img2->num_lines   = 5;
				// $img2->noise_level = 5;
				// var_dump($equal2);
				if($equal2){
						$tipe_kode = substr($kode_bayar, 0,1);
						// var_dump($tipe_kode);exit;
				       if ($tipe_kode=="1"){ // jika bayar nya single perorangan
							$dt_stts = cetakSTTS($kode_va,$kode_bayar,$id_transaksi);
							include('view_stts.php');
						}else if ($tipe_kode=="2"){
							$dt_stts = cetakSTTSCollective($kode_va,$kode_bayar,$id_transaksi);
							include('view_stts_collective.php');
						}
						// print_r($dt_stts)
					// $dt = GetListByNOP($nop,$idwp,"","");
					// require_once('view_data.php');
					// displayChecker();
				}else{
					echo '<div class="alert alert-danger">
							<strong>Kode Verifikasi Harus Benar </strong>
						  </div>';
				}
					}
			?>
		</div>
		<div class="col-sm-2"></div>
	</div>
