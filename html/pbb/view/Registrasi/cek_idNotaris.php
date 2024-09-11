<?php
include "library.php";
$konek=new konek();
$konek->koneksiHost('192.168.168.192','sw_user_devel','sw_pwd_devel');
$konek->konekDb('VSI_SWITCHER_DEVEL');
$koneksi=mysqli_connect($konek->h,$konek->u,$konek->p,$konek->d);

if (! $koneksi) {
					echo "Mengalami kegagalan koneksi";
					mysqli_error($DBLink);
				}

//$connectDb=mysql_select_db($konek->d);
/*if(!$connectDb){
					die ("Database tidak ada".mysqli_error($DBLink));
				}*/


		if(isset($_POST['userId']))//Jika username telah disubmit
		{
			$username = mysqli_real_escape_string($koneksi, $_POST['userId']);//Some clean up :)

			$check_for_username = mysqli_query($koneksi, "SELECT userId FROM TBL_REG_USER WHERE userId='$username'");
			$check_for_username2 = mysqli_query($koneksi, "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$username'");
			//Query untuk mengecek apakah username tersedia atau tidak

			if(trim($username)=="") {
				echo "<font size='2' color='#FFFFFF'>&nbsp;Maaf, Nama ID harus diisi dahulu sebelumnya!</font>";
			}

			else if(mysqli_num_rows($check_for_username)||mysqli_num_rows($check_for_username2))
			{
				//Jika terdapat record yang sesuai dalam databaese, maka tidak tersedia
				echo "<font size='2' color='#FFFFFF'>&nbsp;Maaf, Nama ID sudah terpakai. Silahkan gunakan Nama ID yang lain!</font>";
			}
			else
			{
				//Tak ada record yang sesuai dalam database, maka Username tersedia
				echo "<font size='2' color='#FFFFFF'>&nbsp;Nama ID tersedia!</font>";
			}
		}

?>