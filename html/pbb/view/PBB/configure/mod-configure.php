<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'configure', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
//require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		exit(1);
	}

error_reporting(E_ALL);
ini_set('display_errors', 1);
$path = $sRootPath."view/PBB/configure/";
echo "<script language=\"javascript\" src=\"view/PBB/configure/mod-configure.js\" type=\"text/javascript\"></script>\n";

//print_r($_REQUEST);
function getConfigValue ($id,$key) {
	global $DBLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'"; //echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
		 echo "<br>". mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getConfigModValue ($id,$key) {
	global $DBLink;
	$qry = "select * from central_module_config where CTR_CFG_MID = '".$id."' and CTR_CFG_MKEY = '$key'"; //echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
		 echo "<br>". mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_CFG_MVALUE'];
	}
}

function insertToTable ($id,$key,$value) {
	global $DBLink;
	$qry = "INSERT INTO central_app_config (CTR_AC_AID,CTR_AC_KEY,CTR_AC_VALUE) VALUES ('$id','$key','$value') ON DUPLICATE KEY UPDATE CTR_AC_AID='$id', CTR_AC_KEY = '$key', CTR_AC_VALUE ='$value'" ; //echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
	 echo "<br>". mysqli_error($DBLink);
	}
}

function insertToTableMod ($id,$key,$value) {
	global $DBLink;
	$qry = "INSERT INTO central_module_config (CTR_CFG_MID,CTR_CFG_MKEY,CTR_CFG_MVALUE) VALUES ('$id','$key','$value') ON DUPLICATE KEY UPDATE CTR_CFG_MID='$id', CTR_CFG_MKEY = '$key', CTR_CFG_MVALUE ='$value'" ; //echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
	 echo "<br>". mysqli_error($DBLink);
	}
}

function insertData ($imgB) {
	global $DBLink,$img;
	$a = getConfigValue ('aAdmPajakKabSKB','pbbApp');
	//print_r($_FILES["logo_file"]["name"]);
	if (!$img) $img = $_FILES["logo_file"]["name"];
	
	insertToTable ($a,'cetak_date',$_REQUEST['cetak_date']);
	insertToTable ($a,'expired_days',$_REQUEST['expired_days']);
	insertToTable ($a,'jumlah_verifikasi',$_REQUEST['jumlah_verifikasi']);
	insertToTable ($a,'KODE_KOTA',$_REQUEST['KODE_KOTA']);
	insertToTable ($a,'KODE_PROVINSI',$_REQUEST['KODE_PROVINSI']);
	insertToTable ($a,'NAMA_KOTA',strtoupper($_REQUEST['NAMA_KOTA']));
	insertToTable ($a,'kota',ucfirst(strtolower($_REQUEST['NAMA_KOTA'])));
	insertToTable ($a,'NAMA_PROVINSI',$_REQUEST['NAMA_PROVINSI']);
	insertToTable ($a,'minimum_njoptkp',$_REQUEST['minimum_njoptkp']);
	insertToTable ($a,'minimum_sppt_pbb_terhutang',$_REQUEST['minimum_sppt_pbb_terhutang']);
	insertToTable ($a,'NAMA_PEJABAT_SK',$_REQUEST['NAMA_PEJABAT_SK']);
	insertToTable ($a,'PEJABAT_SK',$_REQUEST['PEJABAT_SK']);
	insertToTable ($a,'NAMA_PEJABAT_SK2',$_REQUEST['NAMA_PEJABAT_SK2']);
	insertToTable ($a,'PEJABAT_SK2',$_REQUEST['PEJABAT_SK2']);
	insertToTable ($a,'NAMA_PEJABAT_SK2_JABATAN',$_REQUEST['NAMA_PEJABAT_SK2_JABATAN']);
	insertToTable ($a,'NAMA_PEJABAT_SK2_NIP',$_REQUEST['NAMA_PEJABAT_SK2_NIP']);
	insertToTable ($a,'KABID_JABATAN',$_REQUEST['KABID_JABATAN']);
	insertToTable ($a,'KABID_NAMA',$_REQUEST['KABID_NAMA']);
	insertToTable ($a,'KABID_NIP',$_REQUEST['KABID_NIP']);
	insertToTable ($a,'PRINTER_NAME',$_REQUEST['PRINTER_NAME']);
	insertToTable ($a,'susulan_start',$_REQUEST['susulan_start']);
	insertToTable ($a,'susulan_end',$_REQUEST['susulan_end']);
	insertToTable ($a,'tahun_tagihan',$_REQUEST['tahun_tagihan']);
	insertToTable ($a,'TEMPAT_PEMBAYARAN',$_REQUEST['TEMPAT_PEMBAYARAN']);
	if ($imgB) insertToTable ($a,'LOGO_CETAK_SK_WALKOT',$img);

}

function insertDataMod () {
	global $DBLink;
	$m = 'mPenagihan';
	
	insertToTableMod ($m,'KODE_BIDANG',$_REQUEST['KODE_BIDANG']);
	insertToTableMod ($m,'KODE_DISPENDA',$_REQUEST['KODE_DISPENDA']);
	insertToTableMod ($m,'LIMIT_TAHUN_PAJAK',$_REQUEST['LIMIT_TAHUN_PAJAK']);
	insertToTableMod ($m,'SP1',$_REQUEST['SP1']);
	insertToTableMod ($m,'SP2',$_REQUEST['SP2']);
	insertToTableMod ($m,'SP3',$_REQUEST['SP3']);
}

$a = getConfigValue ('aAdmPajakKabSKB','pbbApp');
$m = 'mPenagihan';
$img = @isset($_REQUEST['logo_file']["name"]) ? $_REQUEST['logo_file']["name"] : "";
print_r($img);
if($_SERVER['REQUEST_METHOD']=='POST') {
  if (!$img) {
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$temp = explode(".", $_FILES["logo_file"]["name"]);
		$extension = end($temp);
		if ((($_FILES["logo_file"]["type"] == "image/gif")
		|| ($_FILES["logo_file"]["type"] == "image/jpeg")
		|| ($_FILES["logo_file"]["type"] == "image/jpg")
		|| ($_FILES["logo_file"]["type"] == "image/pjpeg")
		|| ($_FILES["logo_file"]["type"] == "image/x-png")
		|| ($_FILES["logo_file"]["type"] == "image/png"))
		&& ($_FILES["logo_file"]["size"] < 20000)
		&& in_array($extension, $allowedExts))
		  {
		  if ($_FILES["logo_file"]["error"] > 0)
			{
			echo "Return Code: " . $_FILES["logo_file"]["error"] . "<br>";
			}
		  else
			{
			/* echo "Upload: " . $_FILES["logo_file"]["name"] . "<br>";
			echo "Type: " . $_FILES["logo_file"]["type"] . "<br>";
			echo "Size: " . ($_FILES["logo_file"]["size"] / 1024) . " kB<br>";
			echo "Temp file: " . $_FILES["logo_file"]["tmp_name"] . "<br>"; */
			if (file_exists($path."logo/" . $_FILES["logo_file"]["name"]))
			  {
			  echo $_FILES["logo_file"]["name"] . " already exists. ";
			  }
			else
			  {
			  move_uploaded_file($_FILES["logo_file"]["tmp_name"],
			  $path."logo/" . $_FILES["logo_file"]["name"]);
			  //echo "Stored in: " .$path."logo/" . $_FILES["logo_file"]["name"];
			  }
			  insertData (true);  
			  insertDataMod();
			}
		  }
		else
		  {
		  //echo "Invalid file";
		  insertData (false);
		  insertDataMod();		  
		  }
  }
	 
}


?>

<form enctype="multipart/form-data" id="upload_form" 
      action="" method="POST" onsubmit="return validateForm();">

<input type="hidden" name="img-logo" id="img-logo" value="<?php echo getConfigValue($a,'FILE_LOGO')?>"><br/>
<table width="700" border="0" cellpadding="5">
  <tr>
   <td colspan="4" align="center"><font size="4"><b>KONFIGURASI PBB</b></font></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Logo Kabupaten / Kota</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="logo_file" name="logo_file"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="150" src="view/PBB/configure/logo/<?php echo getConfigValue($a,'FILE_LOGO')?>"
    </td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Logo untuk PDF</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="LOGO_CETAK_PDF" name="LOGO_CETAK_PDF"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="150" src="view/PBB/configure/logo/<?php echo getConfigValue($a,'LOGO_CETAK_PDF')?>"
   </td>
  </tr>
   <tr>
    <td colspan="2" valign="top">Logo untuk SK Walkot</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="LOGO_CETAK_SK_WALKOT" name="LOGO_CETAK_SK_WALKOT"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="150" src="view/PBB/configure/logo/<?php echo getConfigValue($a,'LOGO_CETAK_SK_WALKOT')?>"
  </td>
  </tr>
   <tr>
    <td colspan="2" valign="top">Logo untuk SK Kadin</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="LOGO_CETAK_SK" name="LOGO_CETAK_SK"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="150" src="view/PBB/configure/logo/<?php echo getConfigValue($a,'LOGO_CETAK_SK')?>"
  </td>
  </tr>
  <tr>
    <td colspan="2">Tengat Waktu</td>
    <td align="center">:</td>
    <td><label for="textfield"></label>
      <input type="text" name="cetak_date" id="cetak_date" size="5" value="<?php echo getConfigValue($a,'cetak_date')?>"></td>
  </tr>
   <tr>
    <td colspan="2">Expired Days</td>
    <td align="center">:</td>
    <td><input type="text" name="expired_days" id="expired_days" size="5" value="<?php echo getConfigValue($a,'expired_days')?>"></td>
  </tr>
  <tr>
    <td colspan="2">Jumlah Verifikasi</td>
    <td align="center">:</td>
    <td><input type="text" name="jumlah_verifikasi" id="jumlah_verifikasi" size="5" value="<?php echo getConfigValue($a,'jumlah_verifikasi')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Kode Kabupaten/Kota (= 4 digit pertama NOP)</td>
    <td align="center" valign="top">:</td>
    <td><input name="KODE_KOTA" type="text" id="KODE_KOTA" size="5"  value="<?php echo getConfigValue($a,'KODE_KOTA')?>"></td>
  </tr> 
  <tr>
    <td colspan="2" valign="top">Kode Propinsi (= 2 digit pertama NOP)</td>
    <td align="center" valign="top">:</td>
    <td><input name="KODE_PROVINSI" type="text" id="KODE_PROVINSI" size="3"  value="<?php echo getConfigValue($a,'KODE_PROVINSI')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Kota</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_KOTA" type="text" id="NAMA_KOTA" size="40"  value="<?php echo getConfigValue($a,'NAMA_KOTA')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Propinsi</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_PROVINSI" type="text" id="NAMA_PROVINSI" size="60"  value="<?php echo getConfigValue($a,'NAMA_PROVINSI')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Minimum NJOPTKP</td>
    <td align="center" valign="top">:</td>
    <td><input name="minimum_njoptkp" type="text" id="minimum_njoptkp" size=""  value="<?php echo getConfigValue($a,'minimum_njoptkp')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Minimum SPPT PBB Terutang </td>
    <td align="center" valign="top">:</td>
    <td><input name="minimum_sppt_pbb_terhutang" type="text" id="minimum_sppt_pbb_terhutang" size=""  value="<?php echo getConfigValue($a,'minimum_sppt_pbb_terhutang')?>"></td>
  </tr><tr>
    <td colspan="2" valign="top">Nama Walkot</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_PEJABAT_SK" type="text" id="NAMA_PEJABAT_SK" size="60"  value="<?php echo getConfigValue($a,'NAMA_PEJABAT_SK')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Jabatan Walkot</td>
    <td align="center" valign="top">:</td>
    <td><input name="PEJABAT_SK" type="text" id="PEJABAT_SK" size="60"  value="<?php echo getConfigValue($a,'PEJABAT_SK')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Kadin</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_PEJABAT_SK2" type="text" id="NAMA_PEJABAT_SK2" size="60"  value="<?php echo getConfigValue($a,'NAMA_PEJABAT_SK2')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Jabatan Kadin</td>
    <td align="center" valign="top">:</td>
    <td><input name="PEJABAT_SK2" type="text" id="PEJABAT_SK2" size="60"  value="<?php echo getConfigValue($a,'PEJABAT_SK2')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Jabatan Kadin 2</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_PEJABAT_SK2_JABATAN" type="text" id="NAMA_PEJABAT_SK2_JABATAN" size="60"  value="<?php echo getConfigValue($a,'NAMA_PEJABAT_SK2_JABATAN')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">NIP Kadin</td>
    <td align="center" valign="top">:</td>
    <td><input name="NAMA_PEJABAT_SK2_NIP" type="text" id="NAMA_PEJABAT_SK2_NIP" size="60"  value="<?php echo getConfigValue($a,'NAMA_PEJABAT_SK2_NIP')?>"></td>
  </tr>
  <tr>
    <td colspan="2">Jabatan KABID</td>
    <td align="center">:</td>
    <td><input type="text" name="KABID_JABATAN" id="KABID_JABATAN" size="60" value="<?php echo getConfigValue($a,'KABID_JABATAN')?>"></td>
  </tr>
   <tr>
    <td colspan="2">Nama KABID</td>
    <td align="center">:</td>
    <td><input type="text" name="KABID_NAMA" id="KABID_NAMA" size="60" value="<?php echo getConfigValue($a,'KABID_NAMA')?>"></td>
  </tr>
   <tr>
    <td colspan="2">NIP KABID</td>
    <td align="center">:</td>
    <td><input type="text" name="KABID_NIP" id="KABID_NIP" size="60" value="<?php echo getConfigValue($a,'KABID_NIP')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Printer</td>
    <td align="center" valign="top">:</td>
    <td><input name="PRINTER_NAME" type="text" id="PRINTER_NAME" size="60"  value="<?php echo getConfigValue($a,'PRINTER_NAME')?>"><br>
	<div><i>Jika lebih dari satu pisahkan dengan titik koma (;)</i></div></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Periode Awal Penetapan Susulan</td>
    <td align="center" valign="top">:</td>
    <td><input name="susulan_start" type="text" id="susulan_start" size="5"  value="<?php echo getConfigValue($a,'susulan_start')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Periode Akhir Penetapan Susulan</td>
    <td align="center" valign="top">:</td>
    <td><input name="susulan_end" type="text" id="susulan_end" size="5"  value="<?php echo getConfigValue($a,'susulan_end')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Tahun Pajak</td>
    <td align="center" valign="top">:</td>
    <td><input name="tahun_tagihan" type="text" id="tahun_tagihan" size="5"  value="<?php echo getConfigValue($a,'tahun_tagihan')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Tempat Pembayaran</td>
    <td align="center" valign="top">:</td>
    <td><input name="TEMPAT_PEMBAYARAN" type="text" id="TEMPAT_PEMBAYARAN" size="40"  value="<?php echo getConfigValue($a,'TEMPAT_PEMBAYARAN')?>"></td>
  </tr>
  <tr>
    <td colspan="4" valign="top"><strong>Konfigurasi Modul Penagihan</strong></td>
  </tr> 
  <tr>
    <td colspan="2" valign="top">Kode Bidang</td>
    <td align="center" valign="top">:</td>
    <td><input name="KODE_BIDANG" type="text" id="KODE_BIDANG" size="40"  value="<?php echo getConfigModValue($m,'KODE_BIDANG')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Kode Dispenda</td>
    <td align="center" valign="top">:</td>
    <td><input name="KODE_DISPENDA" type="text" id="KODE_DISPENDA" size="40"  value="<?php echo getConfigModValue($m,'KODE_DISPENDA')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Batas Tahun Pajak</td>
    <td align="center" valign="top">:</td>
    <td><input name="LIMIT_TAHUN_PAJAK" type="text" id="LIMIT_TAHUN_PAJAK" size="40"  value="<?php echo getConfigModValue($m,'LIMIT_TAHUN_PAJAK')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">SP 1</td>
    <td align="center" valign="top">:</td>
    <td><input name="SP1" type="text" id="SP1" size="40"  value="<?php echo getConfigModValue($m,'SP1')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">SP 2</td>
    <td align="center" valign="top">:</td>
    <td><input name="SP2" type="text" id="SP2" size="40"  value="<?php echo getConfigModValue($m,'SP2')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">SP 3</td>
    <td align="center" valign="top">:</td>
    <td><input name="SP3" type="text" id="SP3" size="40"  value="<?php echo getConfigModValue($m,'SP3')?>"></td>
  </tr>
  <tr>
    <td colspan="4" valign="top"><input type="submit" value="Submit"/>
      <input type="reset" name="Reset" id="button" value="Reset"></td>
    </tr>
</table>
</form>
