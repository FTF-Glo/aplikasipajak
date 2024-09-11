<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'Registrasi'.DIRECTORY_SEPARATOR.'configure', '', dirname(__FILE__))).'/';
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
$path = $sRootPath."view/Registrasi/configure/";
echo "<script language=\"javascript\" src=\"view/Registrasi/configure/mod-configure.js\" type=\"text/javascript\"></script>\n";

//print_r($_REQUEST);
function getConfigValue ($id,$key) {
	global $DBLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
		 echo "<br>". mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function insertToTable ($id,$key,$value) {
	global $DBLink;
	$qry = "INSERT INTO central_app_config (CTR_AC_AID,CTR_AC_KEY,CTR_AC_VALUE) VALUES ('$id','$key','$value') ON DUPLICATE KEY UPDATE CTR_AC_AID='$id', CTR_AC_KEY = '$key', CTR_AC_VALUE ='$value'" ; 
	$res = mysqli_query($DBLink, $qry);
	if (mysql_errno()) { 
	 echo "<br>". mysqli_error($DBLink);
	}
}
function insertData ($imgB) {
	global $DBLink,$img;
	$a = getConfigValue ($_REQUEST['a'],'bphtbApp');
	//print_r($_FILES["logo_file"]["name"]);
	if (!$img) $img = $_FILES["logo_file"]["name"];
	
	insertToTable ($a,'TENGGAT_WAKTU',$_REQUEST['tengat_waktu']);
	insertToTable ($a,'NPOPTKP_WARIS',$_REQUEST['NPOPTKP_waris']);
	insertToTable ($a,'NPOPTKP_STANDAR',$_REQUEST['NPOPTKP_standar']);
	insertToTable ($a,'NAMA_DINAS',$_REQUEST['nama_dinas']);
	insertToTable ($a,'TARIF_BPHTB',$_REQUEST['tarif_BPHTB']);
	insertToTable ($a,'PRINT_SSPD_BPHTB',@isset($_REQUEST['RadioGroup1']) ? $_REQUEST['RadioGroup1'] : "0"); 
	insertToTable ($a,'ALAMAT',$_REQUEST['alamat']);
	insertToTable ($a,'NAMA_DAERAH',$_REQUEST['nama_daerah']);
	insertToTable ($a,'KODE_POS',$_REQUEST['kode_pos']);
	insertToTable ($a,'NO_TELEPON',$_REQUEST['no_telepon']);
	insertToTable ($a,'NO_FAX',$_REQUEST['no_fax']);
	insertToTable ($a,'EMAIL',$_REQUEST['email']);
	insertToTable ($a,'WEBSITE',$_REQUEST['website']);
	insertToTable ($a,'KODE_DAERAH',$_REQUEST['kode_daerah']);  
	insertToTable ($a,'KEPALA_DINAS',$_REQUEST['kepala_dinas']);
	insertToTable ($a,'NAMA_JABATAN',$_REQUEST['nama_jabatan']);
	insertToTable ($a,'NIP',$_REQUEST['nip']);
	insertToTable ($a,'NAMA_PJB_PENGESAH',$_REQUEST['nama_pjb_pengesah']);
	insertToTable ($a,'JABATAN_PJB_PENGESAH',$_REQUEST['jabatan_pjb_pengesah']);
	insertToTable ($a,'NIP_PJB_PENGESAH',$_REQUEST['nip_pjb_pengesah']);
/*	insertToTable ($a,'GTW_HOST_PORT',$_REQUEST['gtw_host_port']);
	insertToTable ($a,'GTW_DB_NAME',$_REQUEST['gtw_db_name']);
	insertToTable ($a,'GTW_DB_USER',$_REQUEST['gtw_db_user']);
	insertToTable ($a,'GTW_DB_PWD',$_REQUEST['gtw_db_pwd']);
	insertToTable ($a,'GTW_TABLE_NAME',$_REQUEST['gtw_table_name']);*/
	if ($imgB) insertToTable ($a,'FILE_LOGO',$img);

}

$a = getConfigValue ($_REQUEST['a'],'bphtbApp');
$img = @isset($_REQUEST['logo_file']["name"]) ? $_REQUEST['logo_file']["name"] : "";
print_r($img);
if($_SERVER['REQUEST_METHOD']=='POST') {
  if (!$img) {
	  if (($_FILES["logo_file"]["type"] == "image/gif") || ($_FILES["logo_file"]["type"] == "image/png") || ($_FILES["logo_file"]["type"] == "image/jpeg") || ($_FILES["logo_file"]["type"] == "image/pjpeg") &&
		 ($_FILES["logo_file"]["size"] < 20000)) {   
		 if ($_FILES["logo_file"]["error"] > 0) {     
			echo "Return Code: " . $_FILES["logo_file"]["error"] . "<br />";
		 } else {     
		 	/*echo "Upload: " . $_FILES["logo_file"]["name"] . "<br />";
			echo "Type: " . $_FILES["logo_file"]["type"] . "<br />";
			echo "Size: " . ($_FILES["logo_file"]["size"] / 1024) . " Kb<br />";
			echo "Temp file: " . $_FILES["logo_file"]["tmp_name"] . "<br />";*/
			if (file_exists($path."logo/" . $_FILES["logo_file"]["name"])) {}//echo $_FILES["logo_file"]["name"] . " already exists. ";       
			else {       
				move_uploaded_file($_FILES["logo_file"]["tmp_name"],
				$path."logo/" . $_FILES["logo_file"]["name"]);
				//echo "Stored in: " .$path. "logo/" . $_FILES["logo_file"]["name"];
			}
			insertData (true);      
		 } 
			 
		}else{   
		// echo "Invalid file"; 
		insertData (false);   
	  }
  }
	 
}


?>
<form enctype="multipart/form-data" id="upload_form" 
      action="" method="POST" onsubmit="return validateForm();">

<input type="hidden" name="img-logo" 
       id="img-logo"  value="<?php echo getConfigValue($a,'FILE_LOGO')?>"/><br/>
<table width="633" border="0" cellpadding="4">
  <tr>
    <td colspan="2" valign="top">Logo Kabupaten / Kota</td>
    <td width="3" align="center"  valign="top">:</td>
    <td width="300"><input type="file" id="logo_file" name="logo_file"/><br /><i>
    Silahkan isi dengan file ber-extention<script>
document.write(extArray.join("  "));
</script></i>
<img width="200" height="150" src="view/Registrasi/configure/logo/<?php echo getConfigValue($a,'FILE_LOGO')?>"
    </td>
  </tr>
  <tr>
    <td colspan="2">Tengat Waktu</td>
    <td align="center">:</td>
    <td><label for="textfield"></label>
      <input type="text" name="tengat_waktu" id="tengat_waktu" value="<?php echo getConfigValue($a,'TENGGAT_WAKTU')?>"></td>
  </tr>
  <tr>
    <td colspan="2">Nilai NPOPTKP Standar</td>
    <td align="center">:</td>
    <td><input type="text" name="NPOPTKP_standar" id="NPOPTKP_standar"  value="<?php echo getConfigValue($a,'NPOPTKP_STANDAR')?>"></td>
  </tr>
  <tr>
    <td colspan="2">Nilai NPOPTKP waris atau hibah</td>
    <td align="center">:</td>
    <td><input type="text" name="NPOPTKP_waris" id="NPOPTKP_waris"  value="<?php echo getConfigValue($a,'NPOPTKP_WARIS')?>"></td>
  </tr>
  <tr>
    <td colspan="2">Tarif BPHTB</td>
    <td align="center">:</td>
    <td><input name="tarif_BPHTB" type="text" id="tarif_BPHTB" size="5"  value="<?php echo getConfigValue($a,'TARIF_BPHTB')?>">
      %</td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Pilihan Mode Pencetakan SSPD BPHTB</td>
    <td align="center" valign="top">:</td>
    <td><p>
      <label>
        <input type="radio" name="RadioGroup1" value="0" id="RadioGroup1_0" <?php echo getConfigValue($a,'PRINT_SSPD_BPHTB')=="0"? "checked":""?>>
        Formulir Pre-Printed</label>
      <br>
      <label>
        <input type="radio" name="RadioGroup1" value="1" id="RadioGroup1_1" <?php echo getConfigValue($a,'PRINT_SSPD_BPHTB')=="1"? "checked":""?>>
        Kertas Kosong</label>
      <br>
      </p></td>
  </tr>
  <tr>
    <td colspan="4" valign="top"><strong>Info Kontak Kantor Dinas Pendapatan Kabupaten /
      Kota</strong></td>
    </tr>
  <tr>
    <td width="13" valign="top">&nbsp;</td>
    <td width="275" valign="top">Nama Dinas</td>
    <td align="center" valign="top">:</td>
    <td><input type="text" name="nama_dinas" id="nama_dinas"  value="<?php echo getConfigValue($a,'NAMA_DINAS')?>"></td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td valign="top">Alamat</td>
    <td align="center" valign="top">:</td>
    <td><label for="textarea"></label>
      <textarea name="alamat" id="alamat" cols="45" rows="5"><?php echo getConfigValue($a,'ALAMAT')?></textarea></td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td valign="top">Nama Daerah</td>
    <td align="center" valign="top">:</td>
    <td><input name="nama_daerah" type="text" id="nama_daerah" size="50"  value="<?php echo getConfigValue($a,'NAMA_DAERAH')?>"></td>
  </tr>
  <tr>
    <td valign="top">&nbsp;</td>
    <td valign="top">Kode Pos</td>
    <td align="center" valign="top">:</td>
    <td><input name="kode_pos" type="text" id="kode_pos" size="20"  value="<?php echo getConfigValue($a,'KODE_POS')?>"></td>
  </tr>
  <tr>
  	<td valign="top">&nbsp;</td>
    <td valign="top">No. Telepon</td>
    <td align="center" valign="top">:</td>
    <td><input name="no_telepon" type="text" id="no_telepon" size="40"  value="<?php echo getConfigValue($a,'NO_TELEPON')?>"></td>
  </tr>
  <tr>
  	<td valign="top">&nbsp;</td>
    <td valign="top">No. Fax</td>
    <td align="center" valign="top">:</td>
    <td><input name="no_fax" type="text" id="no_fax" size="40" value="<?php echo getConfigValue($a,'NO_FAX')?>"></td>
  </tr>
  <tr>
  	<td valign="top">&nbsp;</td>
    <td valign="top">Alamat Email</td>
    <td align="center" valign="top">:</td>
    <td><input name="email" type="text" id="email" size="40"  value="<?php echo getConfigValue($a,'EMAIL')?>"></td>
  </tr>
  <tr>
  	<td valign="top">&nbsp;</td>
    <td valign="top">Alamat Website</td>
    <td align="center" valign="top">:</td>
    <td><input name="website" type="text" id="website" size="40"  value="<?php echo getConfigValue($a,'WEBSITE')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Kode Daerah (= 4 digit pertama NOP)</td>
    <td align="center" valign="top">:</td>
    <td><input name="kode_daerah" type="text" id="kode_daerah" size="40"  value="<?php echo getConfigValue($a,'KODE_DAERAH')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Kepala Dinas Pendapatan</td>
    <td align="center" valign="top">:</td>
    <td><input name="kepala_dinas" type="text" id="kepala_dinas" size="40"  value="<?php echo getConfigValue($a,'KEPALA_DINAS')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Jabatan Kepala Dinas Pendapatan</td>
    <td align="center" valign="top">:</td>
    <td><input name="nama_jabatan" type="text" id="nama_jabatan" size="40" value="<?php echo getConfigValue($a,'NAMA_JABATAN')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">NIP Kepala Dinas Pendapatan</td>
    <td align="center" valign="top">:</td>
    <td><input name="nip" type="text" id="nip" size="40"  value="<?php echo getConfigValue($a,'NIP')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Pejabat yang mengesahkan SSPD-BPHTB</td>
    <td align="center" valign="top">:</td>
    <td><input name="nama_pjb_pengesah" type="text" id="nama_pjb_pengesah" size="40" value="<?php echo getConfigValue($a,'NAMA_PJB_PENGESAH')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Jabatan Pejabat yang mengesahkan SSPDBPHTB</td>
    <td align="center" valign="top">:</td>
    <td><input name="jabatan_pjb_pengesah" type="text" id="jabatan_pjb_pengesah" size="40" value="<?php echo getConfigValue($a,'JABATAN_PJB_PENGESAH')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">NIP Pejabat yang mengesahkan SSPD-BPHTB</td>
    <td align="center" valign="top">:</td>
    <td><input name="nip_pjb_pengesah" type="text" id="nip_pjb_pengesah" size="40" value="<?php echo getConfigValue($a,'NIP_PJB_PENGESAH')?>"></td>
  </tr>
    <!--
  <tr>
    <td colspan="4" valign="top"><B>Konesi Database server BPHTB</B></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Host:Port</td>
    <td align="center" valign="top">:</td>
    <td><input name="gtw_host_port" type="text" id="gtw_host_port" size="40" value="<?php echo getConfigValue($a,'GTW_HOST_PORT')?>"></td>
  </tr>

  <tr>
    <td colspan="2" valign="top">Nama Database</td>
    <td align="center" valign="top">:</td>
    <td><input name="gtw_db_name" type="text" id="gtw_db_name" size="40" value="<?php echo getConfigValue($a,'GTW_DB_NAME')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama User</td>
    <td align="center" valign="top">:</td>
    <td><input name="gtw_db_user" type="text" id="gtw_db_user" size="40" value="<?php echo getConfigValue($a,'GTW_DB_USER')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Password</td>
    <td align="center" valign="top">:</td>
    <td><input name="gtw_db_pwd" type="password" id="gtw_db_pwd" size="40" value="<?php echo getConfigValue($a,'GTW_DB_PWD')?>"></td>
  </tr>
  <tr>
    <td colspan="2" valign="top">Nama Tabel</td>
    <td align="center" valign="top">:</td>
    <td><input name="gtw_table_name" type="text" id="gtw_table_name" size="40" value="<?php echo getConfigValue($a,'GTW_TABLE_NAME')?>"></td>
  </tr>
  -->
  <tr>
    <td colspan="4" valign="top"><input type="submit" value="Submit"/>
      <input type="reset" name="Reset" id="button" value="Reset"></td>
    </tr>
</table>
</form>
