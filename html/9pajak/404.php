<?php 
switch(TRUE){
case	is_readable('setup/security.php'):
		require('setup/security.php');
break; 
default:
	die('<h1 align="center">WEBSITE SEDANG DALAM PROSES PERBAIKAN (MAIN TENIS)
			</h1><br>');
break;};
echo preg_replace('/\r|\n|\t/','','
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta content="" name="descriptison">
<meta content="" name="keywords">
<title>PAGE NOT FOUND</title>
<!--<link href="'.$base['url'].'/style/css/afia1.css?v=1" rel="stylesheet">-->
<body>
	<main id="main">
		<div class="page-wrap d-flex flex-row" style="min-height:650px">
			<div class="container">
				<div class="row" style="height:100%;">
					<div class="col-md-12 text-center" style="margin-bottom:auto; margin-top:auto;">
						<span class="display-4">Page not found!</span>
						<div class="mb-4 lead">Maaf, Halaman yang Anda cari tidak ditemukan silahkan kembali ke Beranda</div>
						<a href="http://36.67.73.69:7001/9pajak_dev/main.php?kunci='.md5($sesi['kunci']).'" class="btn btn-link">Kembali ke Beranda</a>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
');?>