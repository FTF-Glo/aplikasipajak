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
$no = @isset($_REQUEST['no']) ? $_REQUEST['no'] : "";
$id_check = @isset($_REQUEST['id_check']) ? $_REQUEST['id_check'] : "";

$thn_berkas = explode(".", $id_berkas);
$dir_tahun = 'berkas/' . $thn_berkas[0];

if (!is_dir($dir_tahun)) {
	//Directory does not exist, so lets create it.
	mkdir($dir_tahun, 0777);
}
$dir_berkas = $dir_tahun . '/' . $id_berkas;
if (!is_dir($dir_berkas)) {
	//Directory does not exist, so lets create it.
	mkdir($dir_berkas, 0777);
}
if ($_FILES["file"]["name"] != '') {
	$jenis_gambar = $_FILES['file']['type'];
	if ($jenis_gambar == "image/jpeg" || $jenis_gambar == "image/jpg" || $jenis_gambar == "image/png" || $jenis_gambar == "application/pdf") {

		$file_ext = substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.')); // get file name
		$gambar = $dir_berkas . '/' . $no . $file_ext;
		// print_r($gambar);
		// exit;
		$file_size = $_FILES['file']['size'];
		// var_dump($_FILES['file']['tmp_name'], $gambar);
		// exit;
		if (move_uploaded_file($_FILES['file']['tmp_name'], $gambar)) {
			$qry = "select * from cppmod_ssb_upload_file where CPM_SSB_ID = '" . $ssb_id . "' AND CPM_KODE_JNS_PEROLEHAN= '" . $jp . "' AND CPM_KODE_LAMPIRAN='" . $no . "' AND CPM_BERKAS_ID='" . $id_berkas . "'";
			$result = mysqli_query($DBLink, $qry);
			$row = mysqli_num_rows($result);
			if ($row >= 1) {
				$query = "UPDATE cppmod_ssb_upload_file SET CPM_FILE_NAME='" . $no . $file_ext . "' where  CPM_KODE_JNS_PEROLEHAN='" . $jp . "' AND CPM_BERKAS_ID='" . $id_berkas . "' AND CPM_KODE_LAMPIRAN='" . $no . "' AND CPM_SSB_ID='" . $ssb_id . "'";
			} else {

				$query = "insert into cppmod_ssb_upload_file(CPM_KODE_JNS_PEROLEHAN,CPM_BERKAS_ID,CPM_KODE_LAMPIRAN,CPM_FILE_NAME,CPM_SSB_ID) values ('" . $jp . "','" . $id_berkas . "','" . $no . "','" . $no . $file_ext . "','" . $ssb_id . "')";

				$query_cek_data = "SELECT CPM_BERKAS_LAMPIRAN AS BERKAS FROM cppmod_ssb_berkas WHERE CPM_BERKAS_NOPEL = '" . $id_berkas . "' AND CPM_SSB_DOC_ID='" . $ssb_id . "'";
				//echo $query_cek_data;exit();
				$result_cek_data = mysqli_query($DBLink, $query_cek_data);
				$row_cek_data = mysqli_fetch_array($result_cek_data);
				if ($row_cek_data['BERKAS'] == "") {
					$query_berkas = "UPDATE cppmod_ssb_berkas SET CPM_BERKAS_LAMPIRAN=';" . $no . "' WHERE CPM_SSB_DOC_ID='" . $ssb_id . "'";
					//echo "123";exit();
				} else {
					$stringArray = explode(';', $row_cek_data['BERKAS']);
					if (!in_array($no, $stringArray)) {
						$query_berkas = "UPDATE cppmod_ssb_berkas SET CPM_BERKAS_LAMPIRAN=CONCAT(CPM_BERKAS_LAMPIRAN,';','" . $no . "') WHERE CPM_SSB_DOC_ID='" . $ssb_id . "'";
					}
				}

				//echo $query_berkas;exit;
				mysqli_query($DBLink, $query_berkas);
			}

			//echo $query;
			mysqli_query($DBLink, $query);
			if ($jenis_gambar == "image/jpeg" || $jenis_gambar == "image/jpg" || $jenis_gambar == "image/png") {
				if ($file_size > 200000) {
					compress($gambar);
				}
			}
			echo " &nbsp;<img src='function/BPHTB/uploadberkas/success.png' height='20px' width='20px'>&nbsp;&nbsp;<a href ='function/BPHTB/uploadberkas/{$gambar}' target='_blank' id='a_{$id_check}'>Download/view</a>";
			echo "";
			//echo '<meta http-equiv="refresh" content="0; url=./../../../../../main.php?param='.base64_encode('a='.$a.'&m='.$m.'&tab=4').'" />';
		} else {
			//echo '<script>alert("Berkas Tidak Boleh Kosong !");</script>';
			echo '<font size="2" color="red"><b> Upload Gagal </b></font>';
			// echo " Error: " . $_FILES['file']['error'];
			//echo $dir_berkas;
		}
	}
}
