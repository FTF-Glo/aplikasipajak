<?php
$konek=new konek();
$konek->koneksiHost('127.0.0.1','sw_user','sw_pwd');
$konek->konekDb('SW_SSB_O2W');
$koneksi=mysqli_connect($konek->h,$konek->u,$konek->p,$konek->d);

	if (! $koneksi) {
				echo "Mengalami kegagalan koneksi";
				mysqli_error($koneksi);
			}

	/*$connectDb=mysql_select_db($konek->d);
	if(!$connectDb){
				die ("Database tidak ada".mysqli_error($DBLink));
			}*/
?>