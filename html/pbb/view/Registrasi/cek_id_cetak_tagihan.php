<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'Registrasi', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/dbspec-central.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

error_reporting(E_ALL);
ini_set("display_errors", 1); 

//REquest
$a=$_REQUEST['app'];
$m=$_REQUEST['mod'];
$idUser=$_POST['userId'];

//Get user, dbconection
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$appDbLink = $User->GetDbConnectionFromApp($a);
$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);

/*
include "library.php";
$konek=new konek();
$konek->koneksiHost('192.168.168.192','sw_user_devel','sw_pwd_devel');
$konek->konekDb('VSI_SWITCHER_DEVEL');
$koneksi=mysql_connect($konek->h,$konek->u,$konek->p);

if (! $koneksi) {
					echo "Mengalami kegagalan koneksi";
					mysqli_error($DBLink);
				}

$connectDb=mysql_select_db($konek->d);
if(!$connectDb){
					die ("Database tidak ada".mysqli_error($DBLink));
				}

*/
		if(isset($idUser))//Jika username telah disubmit
		{
			$username = mysql_real_escape_string($idUser);//Some clean up :)
			$check_for_username = $dbSpec->sqlQuery("SELECT userId FROM TBL_REG_USER_CETAK_TAGIHAN WHERE userId='$username'", $result);
			$check_for_username2 = $dbSpec->sqlQuery("SELECT CPC_U_UID FROM CPCCORE_USER WHERE CPC_U_UID='$username'", $result2);
			//Query untuk mengecek apakah username tersedia atau tidak

			if(trim($username)=="") {
				echo "<font size='2' color='#FF0000'>Maaf, Nama ID harus diisi dahulu sebelumnya!</font>";
			}
			else if(stristr($username,"'"))
			{
				echo "<font size='2' color='#FF0000'>&nbsp;Maaf, Nama ID tidak boleh mengandung tanda kutip (') !</font>";
			}
			else if(mysqli_num_rows($result)||mysqli_num_rows($result2))
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