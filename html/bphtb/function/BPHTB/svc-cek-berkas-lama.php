<?php


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB', '', dirname(__FILE__))) . '/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue ($id,$key) {
	global $DBLink;	
	//$qry = "select * from CENTRAL_APP_CONFIG where CTR_AC_KEY = '$key'";
	$qry = "select * from CENTRAL_APP_CONFIG where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	
		$res = mysql_query($qry, $DBLink);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysql_error();
		}
		while ($row = mysql_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	
}

function getConfigure ($appID) {
  $config = array();
  $a=$appID;
  $config['TENGGAT_WAKTU'] = getConfigValue($a,'TENGGAT_WAKTU');
  $config['NPOPTKP_STANDAR'] = getConfigValue($a,'NPOPTKP_STANDAR');
  $config['NPOPTKP_WARIS'] = getConfigValue($a,'NPOPTKP_WARIS');
  $config['TARIF_BPHTB'] = getConfigValue($a,'TARIF_BPHTB');
  $config['PRINT_SSPD_BPHTB'] = getConfigValue($a,'PRINT_SSPD_BPHTB');
  $config['NAMA_DINAS'] = getConfigValue($a,'NAMA_DINAS');
  $config['ALAMAT'] = getConfigValue($a,'ALAMAT');
  $config['NAMA_DAERAH'] = getConfigValue($a,'NAMA_DAERAH');
  $config['KODE_POS'] = getConfigValue($a,'KODE_POS');
  $config['NO_TELEPON'] = getConfigValue($a,'NO_TELEPON');
  $config['NO_FAX'] = getConfigValue($a,'NO_FAX');
  $config['EMAIL'] = getConfigValue($a,'EMAIL');
  $config['WEBSITE'] = getConfigValue($a,'WEBSITE');
  $config['KODE_DAERAH'] = getConfigValue($a,'KODE_DAERAH');
  $config['KEPALA_DINAS'] = getConfigValue($a,'KEPALA_DINAS');
  $config['NAMA_JABATAN'] = getConfigValue($a,'NAMA_JABATAN');
  $config['NIP'] = getConfigValue($a,'NIP');
  $config['NAMA_PJB_PENGESAH'] = getConfigValue($a,'NAMA_PJB_PENGESAH');
  $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a,'JABATAN_PJB_PENGESAH');
  $config['NIP_PJB_PENGESAH'] = getConfigValue($a,'NIP_PJB_PENGESAH');
  
  $config['BPHTBDBNAME'] = getConfigValue($a,'BPHTBDBNAME');
  $config['BPHTBHOSTPORT'] = getConfigValue($a,'BPHTBHOSTPORT');
  $config['BPHTBPASSWORD'] = getConfigValue($a,'BPHTBPASSWORD');
  $config['BPHTBTABLE'] = getConfigValue($a,'BPHTBTABLE');
  $config['BPHTBUSERNAME'] = getConfigValue($a,'BPHTBUSERNAME');
  
  return $config;
}
function getberkas($no){
	global $DBLink;
	$jp = @isset($_REQUEST['jp']) ? $_REQUEST['jp'] : "";
	$ssbid = @isset($_REQUEST['ssbid']) ? $_REQUEST['ssbid']: "";
	$qry = "select * from CPPMOD_SSB_UPLOAD_FILE WHERE CPM_KODE_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_ID='{$ssbid}' AND CPM_KODE_LAMPIRAN='{$no}' ORDER BY CPM_KODE_LAMPIRAN ASC";
	//echo $qry;
	$res = mysql_query($qry, $DBLink);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysql_error();
    }
	$row=mysql_num_rows($res);
	if($row>=1){
		while ($rows = mysql_fetch_assoc($res)) {
			  $thn_berkas=explode(".",$rows['CPM_BERKAS_ID']);
			  $berkas="<a href ='function/BPHTB/uploadberkas/berkas/".$thn_berkas[0]."/".$rows['CPM_BERKAS_ID']."/".$rows['CPM_FILE_NAME']."' target='_blank'>Download/view</a>";
		}
	}else{
		$berkas="-";
	}
	return $berkas;
}

function getberkas_manual($no){
	global $DBLink;
	$jp = @isset($_REQUEST['jp']) ? $_REQUEST['jp'] : "";
	$ssbid = @isset($_REQUEST['ssbid']) ? $_REQUEST['ssbid']: "";
	$qry = "select * from CPPMOD_SSB_BERKAS WHERE CPM_BERKAS_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_DOC_ID='{$ssbid}' ORDER BY CPM_BERKAS_ID";
	//echo $qry;
	$res = mysql_query($qry, $DBLink);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysql_error();
    }
	$row=mysql_num_rows($res);
	if($row>=1){
		$rows = mysql_fetch_assoc($res); 
			  $cpm_berkas_lampiran_replace = str_replace(';','.',$rows['CPM_BERKAS_LAMPIRAN']);
			  //echo $cpm_berkas_lampiran_replace;
			  $cpm_berkas_lampiran_array=explode('.', $cpm_berkas_lampiran_replace );
			  if(in_array($no,$cpm_berkas_lampiran_array)){
				  $berkas_manual = "Ada";
			  }else{
				  $berkas_manual = "-";
			  }
	}
	return $berkas_manual;
}
function getstatus(){
	global $DBLink;
	$jp = @isset($_REQUEST['jp']) ? $_REQUEST['jp'] : "";
	$ssbid = @isset($_REQUEST['ssbid']) ? $_REQUEST['ssbid']: "";
	$qry = "select * from CPPMOD_SSB_BERKAS A JOIN CPPMOD_SSB_UPLOAD_FILE B ON A.CPM_SSB_DOC_ID = B.CPM_SSB_ID WHERE CPM_BERKAS_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_DOC_ID='{$ssbid}'";
	//echo $qry;
	$res = mysql_query($qry, $DBLink);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysql_error();
    }
	$row=mysql_num_rows($res);
	if($row>=1){
		$status="1";
	}else{
		$status="0";
	}
	return $status;
}

function getSyarat($id) {
    global $DBLink;
    $qry = "select * from CPPMOD_SSB_PERSYARATAN where CPM_KD_PERSYARATAN = '" . $id . "'";
    $res = mysql_query($qry, $DBLink);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysql_error();
    }
    while ($row = mysql_fetch_assoc($res)) {
        return $row['CPM_PERSYARATAN'];
    }
}



function getLampiran($ssb_id,$jenis_hak, $status){
	global $DBLink;
	
	$lampiran[] = "";
	
	$qry = "select * from CPPMOD_SSB_JENIS_HAK where CPM_KD_JENIS_HAK = '".$jenis_hak."'";
	
	$res = mysql_query($qry, $DBLink);
	$i=0;
	$j=1;
	$k=1;
	$html="";
	while ($row = mysql_fetch_assoc($res)) {
		//print_r($row);
		echo $row['CPM_BERKAS_NOPEL'];
		$exp_lampiran = explode(",", $row['CPM_PERSYARATAN'].",".$row['CPM_PERSYARATAN_SURVEYOR']);
		
			foreach($exp_lampiran as $syarat){
					
				if($status!="0")
					$lampiran[$i] = "<tr><td width=\"3%\">".$j.". </td><td width=\"60%\">".getSyarat($syarat)."<td width=\"37%\" align=\"center\">".getberkas($syarat)."</td></tr>";
				else
					$lampiran[$i] = "<tr><td width=\"3%\">".$j.". </td><td width=\"60%\">".getSyarat($syarat)."</td><td width=\"37%\" align=\"center\">".getberkas_manual($syarat)."</td></tr>";
				$html .=$lampiran[$i];
				$i++;
				$j++;
				$k++;
			}
		 $j=1;
	}
	
return $html;
}

if(getstatus()!="0"){
	$lamp1= "<td width=\"3%\">1.</td><td width=\"60%\">Formulir penyampaian SSPD BPHTB</td><td width=\"37%\" align=\"center\">".getberkas(1)."</td>";
	$lamp2= "<td>2.</td><td> SSPD-BPHTB</td><td  align=\"center\">".getberkas(2)."</td>";
	$lamp3= "<td>3.</td><td> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</td><td  align=\"center\">".getberkas(3)."</td>";
	$lamp4= "<td>4.</td><td> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td><td align=\"center\">".getberkas(4)."</td>";
	$lamp5= "<td>5.</td><td> Fotocopy SPPT yang sedang berjalan</td><td align=\"center\">".getberkas(5)."</td>";
	$lamp6= "<td>6.</td><td> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</td><td align=\"center\">".getberkas(6)."</td>";
	$lamp7= "<td>7.</td><td> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td><td align=\"center\">".getberkas(7)."</td>";
	$lamp8= "<td>8.</td><td> Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td><td align=\"center\">".getberkas(8)."</td>";
	$lamp9= "<td>9.</td><td> Fotocopy Bukti transaksi/rincian pembayaran</td><td align=\"center\">".getberkas(9)."</td>";
	$lamp10= "<td>10.</td><td> Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak</td><td align=\"center\">".getberkas(10)."</td>";
	$lamp11= "<td>11.</td><td> Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku</td><td align=\"center\">".getberkas(11)."</td>";
	$lamp12= "<td>12.</td><td> Pertanyaan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa</td><td align=\"center\">".getberkas(12)."</td>";
	$lamp13= "<td>13.</td><td> Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat</td><td align=\"center\">".getberkas(13)."</td>";
	$lamp14= "<td>14.</td><td> Fotocopy Surat/Keterangan Kematian</td><td align=\"center\">".getberkas(14)."</td>";
	$lamp15= "<td>15.</td><td> Fotocopy Surat Pernyataan Waris</td><td align=\"center\">".getberkas(15)."</td>";
	$lamp16= "<td>16.</td><td> Fotocopy Surat Kuasa Waris dalam hal Dikuasakan</td><td align=\"center\">".getberkas(16)."</td>";
	$lamp17= "<td>17.</td><td> Fotocopy Akta Pendirian Perusahaan yang terbaru</td><td align=\"center\">".getberkas(17)."</td>";
	$lamp18= "<td>18.</td><td> Fotocopy NPWP Perusahaan</td><td align=\"center\">".getberkas(18)."</td>";
	$lamp19= "<td>19.</td><td> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td><td align=\"center\">".getberkas(19)."</td>";
	$lamp20= "<td>20.</td><td> Fotocopy KTP para ahli waris</td><td align=\"center\">".getberkas(20)."</td>";
	$lamp21= "<td>21.</td><td> Fotocopy Surat/keterangan Kematian</td><td align=\"center\">".getberkas(21)."</td>";
	$lamp22= "<td>22.</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas(22)."</td>";
	$lamp23= "<td>23.</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas(23)."</td>";
	$lamp24= "<td>24.</td><td> Fotocopy Kwitansi lelang/Risalah Lelang</td><td align=\"center\">".getberkas(24)."</td>";
	$lamp25= "<td>25.</td><td> Fotocopy Keputusan Hakim/Pengadilan</td><td align=\"center\">".getberkas(25)."</td>";
	$lamp26= "<td>26.</td><td> Surat Pernyataan Hadiah dari yang mengalihkan hak</td><td align=\"center\">".getberkas(26)."</td>";
	$lamp27= "<td>27.</td><td> Fotocopy Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</td><td align=\"center\">".getberkas(27)."</td>";
	$lamp28= "<td>28.</td><td> Surat Pelepasan Hak Atas Tanah dari BPN</td><td align=\"center\">".getberkas(28)."</td>";
}else{
	$lamp1= "<td width=\"3%\">1.</td><td width=\"60%\">Formulir penyampaian SSPD BPHTB</td><td width=\"37%\" align=\"center\">".getberkas_manual(1)."</td>";
	$lamp2= "<td>2.</td><td> SSPD-BPHTB</td><td  align=\"center\">".getberkas_manual(2)."</td>";
	$lamp3= "<td>3.</td><td> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</td><td  align=\"center\">".getberkas_manual(3)."</td>";
	$lamp4= "<td>4.</td><td> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td><td align=\"center\">".getberkas_manual(4)."</td>";
	$lamp5= "<td>5.</td><td> Fotocopy SPPT yang sedang berjalan</td><td align=\"center\">".getberkas_manual(5)."</td>";
	$lamp6= "<td>6.</td><td> Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</td><td align=\"center\">".getberkas_manual(6)."</td>";
	$lamp7= "<td>7.</td><td> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td><td align=\"center\">".getberkas_manual(7)."</td>";
	$lamp8= "<td>8.</td><td> Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td><td align=\"center\">".getberkas_manual(8)."</td>";
	$lamp9= "<td>9.</td><td> Fotocopy Bukti transaksi/rincian pembayaran</td><td align=\"center\">".getberkas_manual(9)."</td>";
	$lamp10= "<td>10.</td><td> Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak</td><td align=\"center\">".getberkas_manual(10)."</td>";
	$lamp11= "<td>11.</td><td> Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku</td><td align=\"center\">".getberkas_manual(11)."</td>";
	$lamp12= "<td>12.</td><td> Pertanyaan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa</td><td align=\"center\">".getberkas_manual(12)."</td>";
	$lamp13= "<td>13.</td><td> Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat</td><td align=\"center\">".getberkas_manual(13)."</td>";
	$lamp14= "<td>14.</td><td> Fotocopy Surat/Keterangan Kematian</td><td align=\"center\">".getberkas_manual(14)."</td>";
	$lamp15= "<td>15.</td><td> Fotocopy Surat Pernyataan Waris</td><td align=\"center\">".getberkas_manual(15)."</td>";
	$lamp16= "<td>16.</td><td> Fotocopy Surat Kuasa Waris dalam hal Dikuasakan</td><td align=\"center\">".getberkas_manual(16)."</td>";
	$lamp17= "<td>17.</td><td> Fotocopy Akta Pendirian Perusahaan yang terbaru</td><td align=\"center\">".getberkas_manual(17)."</td>";
	$lamp18= "<td>18.</td><td> Fotocopy NPWP Perusahaan</td><td align=\"center\">".getberkas_manual(18)."</td>";
	$lamp19= "<td>19.</td><td> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td><td align=\"center\">".getberkas_manual(19)."</td>";
	$lamp20= "<td>20.</td><td> Fotocopy KTP para ahli waris</td><td align=\"center\">".getberkas_manual(20)."</td>";
	$lamp21= "<td>21.</td><td> Fotocopy Surat/keterangan Kematian</td><td align=\"center\">".getberkas_manual(21)."</td>";
	$lamp22= "<td>22.</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas_manual(22)."</td>";
	$lamp23= "<td>23.</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas_manual(23)."</td>";
	$lamp24= "<td>24.</td><td> Fotocopy Kwitansi lelang/Risalah Lelang</td><td align=\"center\">".getberkas_manual(24)."</td>";
	$lamp25= "<td>25.</td><td> Fotocopy Keputusan Hakim/Pengadilan</td><td align=\"center\">".getberkas_manual(25)."</td>";
	$lamp26= "<td>26.</td><td> Surat Pernyataan Hadiah dari yang mengalihkan hak</td><td align=\"center\">".getberkas_manual(26)."</td>";
	$lamp27= "<td>27.</td><td> Fotocopy Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</td><td align=\"center\">".getberkas_manual(27)."</td>";
	$lamp28= "<td>28.</td><td> Surat Pelepasan Hak Atas Tanah dari BPN</td><td align=\"center\">".getberkas_manual(28)."</td>";
}
//$nop = @isset($_REQUEST['nop']) ? intval($_REQUEST['nop']) : "";
//$role = @isset($_REQUEST['role']) ? intval($_REQUEST['role']) : "";
//$ceknop = substr($nop,0,13);
//$znt = @isset($_REQUEST['znt']) ? $_REQUEST['znt'] : "";
//$harga = @isset($_REQUEST['harga']) ? intval($_REQUEST['harga']) : "";
//$luas_tnh = @isset($_REQUEST['luas_tnh']) ? intval($_REQUEST['luas_tnh']) : "";
//$njop_bgn = @isset($_REQUEST['njop_bgn']) ? intval($_REQUEST['njop_bgn']) : "";
//$luas_bgn = @isset($_REQUEST['luas_bgn']) ? intval($_REQUEST['luas_bgn']) : "";
$jp = @isset($_REQUEST['jp']) ? $_REQUEST['jp'] : "";
$ssbid = @isset($_REQUEST['ssbid']) ? $_REQUEST['ssbid']: "";
//print_r($_REQUEST);
//$appId =base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");

$result = array();

	
	
	
	//echo $qry;exit;
	
	
	//print_r($jenis);
	//print_r($row);
	$status=getstatus();
	$result['result']="";
	$result['result'] .="<h2>Berkas-berkas yang sudah diupload</h2><br>";

		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table border=\"0\"  cellpadding=\"1\">";
		$result['result'] .= getLampiran($ssbid,$jp,$status);
		$result['result'] .="</table>";
	
	$result['success']=true;
	$sResponse = $json->encode($result);
	echo $sResponse;
	
SCANPayment_CloseDB($DBLink);
?>
