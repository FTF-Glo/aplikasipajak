 <br>
   		<div class="row">
		<div class="col-sm-4"></div>
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-body">
					<span align="center"><h4>Daftar Tagihan SPPT PBB</h4></span>
					<hr>
					<form class="form-horizontal" method="POST">

					  <input type='hidden' maxlength='32' name='fungsi' value='cek-tagihan'>
					  <input type='hidden' maxlength='32' name='area' value='<?php echo $area; ?>'>
					  <input type='hidden' maxlength='32' name='client' value='<?php echo $client; ?>'>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="idwp">ID WAJIB PAJAK</label>
						<div class="col-sm-7">
						<!-- 320109000500600110 -->
							<input type="text" class="form-control" id="idwp" name="idwp" value="<?php echo $idwp;?>" placeholder="Masukan ID WP">
						</div>
					  </div>
					  <div class="form-group">
						<label class="control-label col-sm-5" for="nop">NOP</label>
						<div class="col-sm-7"> 
						<!-- 360119101400601690 -->
							<input type="text" required="" class="form-control" id="nop" name="nop" value="<?php echo $nop;?>" placeholder="Masukan NOP">
						</div>
					  </div>
					 <!-- <div class="form-group">
						<label class="control-label col-sm-5" for="nop">KODE VERIFIKASI</label>
						<div class="col-sm-7"> 
							<img src='image-2018/securimage_show.php?namespace=single' alt='Captcha Image' id='captcha-image' />
						</div>
					  </div>
				  <div class="form-group"> 
					<div class="col-sm-offset-5 col-sm-7">
						<input required="" type="text" class="form-control" name="cImage_1" id="cImage_1" value="" maxlength="10" autocomplete="off" placeholder="Masukan kode verifikasi"/>
					</div>
				  </div>-->
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
			// print_r($_REQUEST);
				$img1 = new Securimage();
				$img1->namespace = "single";
				$equal1 = ($img1->check($cImage_1));
				// var_dump($equal1);
				// var_dump($cImage_1);
				// echo "<pre>";
				// print_r($img1);
				// echo "</pre>";
				// var_dump($img1);
				// var_dump($equal1);
				// var_dump($_REQUEST['fungsi']);
				// var_dump($cImage_2);
				// var_dump($equal);
				$fungsi = $_REQUEST['fungsi'];
				if ($fungsi=="cek-tagihan"){
					//if($equal1){
							// echo "masuk";
							$dt = GetListByNOP($nop,$idwp,$thn1,$thn2);
							// print_r($dt);
							$code_bank = getBankCode();
							$code_bank = $code_bank['CDC_VB_CODE'];
							// print_r($code_bank);

							require_once('view_data.php');
						// displayChecker();
					//}//else{
						//echo '<div class="alert alert-danger">
						//		<strong>Kode Verifikasi Harus Benar </strong>
						//	  </div>';
					//}
				}
			?>
		</div>
		<div class="col-sm-2"></div>
	</div>
