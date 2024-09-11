<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sRootPath = '/var/www/html/bphtb-lamsel/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "function/BPHTB/uploadberkas/image.php");
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$id_berkas = @isset($_REQUEST['id_berkas']) ? $_REQUEST['id_berkas'] : "";
$nobukti = @isset($_REQUEST['nobukti']) ? $_REQUEST['nobukti'] : "";
$dir_tahun='berkas/'.date('Y');

if(!is_dir($dir_tahun)){
    //Directory does not exist, so lets create it.
    mkdir($dir_tahun, 0777);
		
}
$dir_berkas=$dir_tahun.'/'.$id_berkas;
if(!is_dir($dir_berkas)){
		//Directory does not exist, so lets create it.
	mkdir($dir_berkas, 0777);

}
if (!isset($_FILES["file"]["name"])) {
die;
}
if($_FILES["file"]["name"] != '')
{     
	$jenis_gambar=$_FILES['file']['type'];        
	if($jenis_gambar=="image/jpeg" || $jenis_gambar=="image/jpg" || $jenis_gambar=="image/png" || $jenis_gambar=="application/pdf"){ 
		
		$file_ext = substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.')); // get file name
		$gambar = $dir_berkas .'/'. $nobukti . $file_ext; 
		$file_size = $_FILES['file']['size'];
		if (move_uploaded_file($_FILES['file']['tmp_name'], $gambar)){
			$set = "CPM_UPLOAD_UPT{$nobukti} ='{$gambar}'";
			$query = "UPDATE cppmod_ssb_berkas SET $set WHERE CPM_BERKAS_ID=".$id_berkas;

			//echo $query;
			mysqli_query($DBLink, $query);
			if($jenis_gambar=="image/jpeg" || $jenis_gambar=="image/jpg" || $jenis_gambar=="image/png"){
				if($file_size > 200000){
					compress($gambar);
				}
			}
			echo " &nbsp;<img src='function/BPHTB/uploadberkas/success.png' height='20px' width='20px'>&nbsp;&nbsp;<a href ='function/BPHTB/VerifikasiLpgn/{$gambar}' target='_blank'>Download./view</a>";
			echo "";
		}else{
			echo '<font size="2" color="red"><b> Upload Gagal </b></font>';
		}   
	} 
}
		
	

?>