<?php
$konek=new konek();
//$konek->koneksiHost('localhost','root','getpass');
$konek->koneksiHost('localhost','root','');
$konek->konekDb('SW_SSB');
$koneksi=mysql_connect($konek->h,$konek->u,$konek->p);

	if (! $koneksi) {
				echo "Mengalami kegagalan koneksi";
				mysqli_error();
			}

	$connectDb=mysql_select_db($konek->d);
	if(!$connectDb){
				die ("Database tidak ada".mysqli_error());
			}
?>