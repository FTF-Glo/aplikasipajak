<?php

$sRootPath = str_replace('\\', '/', str_replace('/view/Registrasi', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/db-payment.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);

if(isset($_REQUEST['userId']))//Jika username telah disubmit
{
        $username = mysqli_real_escape_string($DBLink, $_REQUEST['userId']);//Some clean up :)
        $check_for_username=mysqli_query($DBLink, "SELECT userId FROM TBL_REG_USER_PBB WHERE userId='$username'") or die("#er01: ".mysqli_error($DBLink));
        $check_for_username2 = mysqli_query($DBLink, "SELECT CTR_U_UID FROM central_user WHERE CTR_U_UID='$username'") or die("#er01: ".mysqli_error($DBLink));
        //Query untuk mengecek apakah username tersedia atau tidak

        if(trim($username)=="") {
                echo "<font size='2' color='#FF0000'>Maaf, Nama ID harus diisi dahulu sebelumnya!</font>";
        }
        else if(stristr($username,"'"))
        {
                echo "<font size='2' color='#FF0000'>&nbsp;Maaf, Nama ID tidak boleh mengandung tanda kutip (') !</font>";
        }
        else if(mysqli_num_rows($check_for_username)||mysqli_num_rows($check_for_username2))
        {
                //Jika terdapat record yang sesuai dalam databaese, maka tidak tersedia
                echo "<font size='2' color='#FF0000'>Maaf, Nama ID sudah terpakai. Silahkan gunakan Nama ID yang lain!</font>";
        }
        else
        {
                //Tak ada record yang sesuai dalam database, maka Username tersedia
                echo "<font size='2' color='#FF0000'>&nbsp;Nama ID tersedia!</font>";
        }
}

?>