<?php
$konek=new konek();
//$konek->koneksiHost('127.0.0.1','root','getpass');
$konek->koneksiHost('localhost','root','pesawaran2@24');
$konek->konekDb('sw_ssb');
$koneksi=mysqli_connect($konek->h,$konek->u,$konek->p,$konek->d);
if (mysqli_connect_errno()) {
	echo "Mengalami kegagalan koneksi";
	mysqli_connect_error();
	exit();
}

/*$connectDb=mysql_select_db($konek->d);
if(!$connectDb){
	die ("Database tidak ada".mysql_error());
}*/
?>