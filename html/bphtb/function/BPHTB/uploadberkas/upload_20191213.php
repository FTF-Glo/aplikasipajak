<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'uploadberkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "function/BPHTB/uploadberkas/image.php");
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
  
$jp = @isset($_REQUEST['jp']) ? $_REQUEST['jp'] : "";
$ssb_id = @isset($_REQUEST['ssb_id']) ? $_REQUEST['ssb_id'] : "";
$id_berkas = @isset($_REQUEST['id_berkas']) ? $_REQUEST['id_berkas'] : "";
$dir_tahun='berkas/'.date('Y');
$no = @isset($_REQUEST['no']) ? $_REQUEST['no'] : "";

if(!is_dir($dir_tahun)){
    //Directory does not exist, so lets create it.
    mkdir($dir_tahun, 0755);
		
}
$dir_berkas=$dir_tahun.'/'.$id_berkas;
if(!is_dir($dir_berkas)){
		//Directory does not exist, so lets create it.
		mkdir($dir_berkas, 0755);
	
	}
	
		if($_FILES["file"]["name"] != '')
		{     
			$jenis_gambar=$_FILES['file']['type'];        
			if($jenis_gambar=="image/jpeg" || $jenis_gambar=="image/jpg" || $jenis_gambar=="image/png" || $jenis_gambar=="application/pdf"){ 
				
				$file_ext = substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.')); // get file name
				$gambar = $dir_berkas .'/'. $no . $file_ext; 
				$file_size = $_FILES['file']['size'];
				if (move_uploaded_file($_FILES['file']['tmp_name'], $gambar)){
					$qry="select * from cppmod_ssb_upload_file where CPM_SSB_ID = '".$ssb_id."' AND CPM_KODE_JNS_PEROLEHAN= '".$jp."' AND CPM_KODE_LAMPIRAN='".$no."' AND CPM_BERKAS_ID='".$id_berkas."'";
					$result = mysqli_query($DBLink, $qry);
					$row = mysqli_num_rows($result);
					if($row>=1){
						$query = "UPDATE cppmod_ssb_upload_file SET CPM_FILE_NAME='".$no . $file_ext."' where  CPM_KODE_JNS_PEROLEHAN='".$jp."' AND CPM_BERKAS_ID='".$id_berkas."' AND CPM_KODE_LAMPIRAN='".$no."' AND CPM_SSB_ID='".$ssb_id."'";
					}else{
						
						$query = "insert into cppmod_ssb_upload_file(CPM_KODE_JNS_PEROLEHAN,CPM_BERKAS_ID,CPM_KODE_LAMPIRAN,CPM_FILE_NAME,CPM_SSB_ID) values ('".$jp."','".$id_berkas."','".$no."','".$no . $file_ext."','".$ssb_id."')";
						$query_berkas = "UPDATE cppmod_ssb_berkas SET CPM_BERKAS_LAMPIRAN=CONCAT(CPM_BERKAS_LAMPIRAN,';','".$no."') WHERE CPM_SSB_DOC_ID='".$ssb_id."'";
						//echo $query_berkas;exit;
						mysqli_query($DBLink, $query_berkas);
					}
						
					//echo $query;
					mysqli_query($DBLink, $query);
					if($jenis_gambar=="image/jpeg" || $jenis_gambar=="image/jpg" || $jenis_gambar=="image/png"){
						if($file_size > 200000){
							compress($gambar);
						}
					}
					echo " &nbsp;<img src='function/BPHTB/uploadberkas/success.png' height='20px' width='20px'>&nbsp;&nbsp;<a href ='function/BPHTB/uploadberkas/{$gambar}' target='_blank'>Download/view</a>";
					echo "";
					//echo '<meta http-equiv="refresh" content="0; url=./../../../../../main.php?param='.base64_encode('a='.$a.'&m='.$m.'&tab=4').'" />';
				}else{
					//echo '<script>alert("Berkas Tidak Boleh Kosong !");</script>';
					echo '<font size="2" color="red"><b> Upload Gagal </b></font>';
					//echo $dir_berkas;
				}   
			} 
		}
		
	

?>