<?php
include "library.php";
include "koneksi.php";

		if(isset($_POST['userId']))//Jika username telah disubmit
		{
			$username = mysqli_real_escape_string($koneksi, $_POST['userId']);//Some clean up :)
			$check_for_username = mysqli_query($koneksi, "SELECT userId FROM tbl_reg_user_notaris WHERE userId='".$username."'");
			$check_for_username2 = mysqli_query($koneksi, "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$username'");
			//Query untuk mengecek apakah username tersedia atau tidak

			if(trim($username)=="") {
				echo "<font size='2' color='red'>Maaf, Nama ID harus diisi dahulu sebelumnya!</font>";
			}
			else if(stristr($username,"'"))
			{
				echo "<font size='2' color='red'>Maaf, Nama ID tidak boleh mengandung tanda kutip (') !</font>";
			}

			else if(mysqli_num_rows($check_for_username)||mysqli_num_rows($check_for_username2))
			{
				//Jika terdapat record yang sesuai dalam databaese, maka tidak tersedia
				// echo mysqli_num_rows($check_for_username2);
				echo "<font size='2' color='red'>Maaf, Nama ID sudah terpakai. Silahkan gunakan Nama ID yang lain!</font>";
			}
			else
			{
				//Tak ada record yang sesuai dalam database, maka Username tersedia
				echo "<font size='2' color='green'>Nama ID tersedia!</font>";
			}
		}


/*
$userId=$_GET['userId'];
$sqlTampil= "SELECT userId FROM TBL_REG_USER";
$bOK = mysqli_query($sqlTampil, $koneksi);
$dataTampil=mysqli_fetch_array($bOK);
$idAda=$dataTampil['userId'];
if($bOK){
	if(trim($userId)==""){
		echo "<font size='2' color='#FF0000'>&nbsp;&nbsp;Maaf, Nama ID harus diisi dahulu sebelumnya!</font>";
	}
	else {
	
			if($userId==$idAda){

					echo "<font size='2' color='#FF0000'>&nbsp;&nbsp;Maaf, Nama ID sudah terpakai. Silahkan gunakan Nama ID yang lain!</font>";
				}
			else {
			echo "<font size='2' color='#FF0000'>&nbsp;&nbsp;Nama ID tersedia!</font>";
			}
	}

}

*/
?>