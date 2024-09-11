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
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
	
		$res = mysqli_query($DBLink, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
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
	$qry = "select * from cppmod_ssb_upload_file WHERE CPM_KODE_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_ID='{$ssbid}' AND CPM_KODE_LAMPIRAN='{$no}' ORDER BY CPM_KODE_LAMPIRAN ASC";
	//echo $qry;
	$res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$row=mysqli_num_rows($res);
	if($row>=1){
		while ($rows = mysqli_fetch_assoc($res)) {
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
	$qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_DOC_ID='{$ssbid}' ORDER BY CPM_BERKAS_ID";
	//echo $qry;
	$res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$row=mysqli_num_rows($res);
	if($row>=1){
		$rows = mysqli_fetch_assoc($res); 
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
	$qry = "select * from cppmod_ssb_berkas A JOIN cppmod_ssb_upload_file B ON A.CPM_SSB_DOC_ID = B.CPM_SSB_ID WHERE CPM_BERKAS_JNS_PEROLEHAN='{$jp}' AND CPM_SSB_DOC_ID='{$ssbid}'";
	//echo $qry;
	$res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$row=mysqli_num_rows($res);
	if($row>=1){
		$status="1";
	}else{
		$status="0";
	}
	return $status;
}
if(getstatus()!="0"){
	$lamp1= "<td>-</td><td width=\"60%\">SSPD Yang Sudah Ditanda Tangani Oleh Wajib Pajak Dan PPAT</td><td width=\"37%\" align=\"center\">".getberkas(1)."</td>";
	$lamp2= "<td>-</td><td> SSPD-BPHTB</td><td  align=\"center\">".getberkas(2)."</td>";
	$lamp3= "<td>-</td><td> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</td><td  align=\"center\">".getberkas(3)."</td>";
	$lamp4= "<td>-</td><td> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td><td align=\"center\">".getberkas(4)."</td>";
	$lamp5= "<td>-</td><td> Fotocopy SPPT PBB</td><td align=\"center\">".getberkas(5)."</td>";
	$lamp6= "<td>-</td><td> Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang</td><td align=\"center\">".getberkas(6)."</td>";
	$lamp7= "<td>-</td><td> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td><td align=\"center\">".getberkas(7)."</td>";
	$lamp8= "<td>-</td><td> Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td><td align=\"center\">".getberkas(8)."</td>";
	$lamp9= "<td>-</td><td> Fotocopy Surat Keterangan jual/beli</td><td align=\"center\">".getberkas(9)."</td>";
	$lamp10= "<td>-</td><td> Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak</td><td align=\"center\">".getberkas(10)."</td>";
	$lamp11= "<td>-</td><td> Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku</td><td align=\"center\">".getberkas(11)."</td>";
	$lamp12= "<td>-</td><td> Fotocopy Surat Keterangan Waris atau Akta Hibah</td><td align=\"center\">".getberkas(12)."</td>";
	$lamp13= "<td>-</td><td> Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat</td><td align=\"center\">".getberkas(13)."</td>";
	$lamp14= "<td>-</td><td> Fotocopy Surat/Keterangan Kematian</td><td align=\"center\">".getberkas(14)."</td>";
	$lamp15= "<td>-</td><td> Fotocopy Surat Pernyataan Waris</td><td align=\"center\">".getberkas(15)."</td>";
	$lamp16= "<td>-</td><td> Fotocopy Surat Kuasa Waris dalam hal Dikuasakan</td><td align=\"center\">".getberkas(16)."</td>";
	$lamp17= "<td>-</td><td> Fotocopy Akta Pendirian Perusahaan yang terbaru</td><td align=\"center\">".getberkas(17)."</td>";
	$lamp18= "<td>-</td><td> Fotocopy NPWP Perusahaan</td><td align=\"center\">".getberkas(18)."</td>";
	$lamp19= "<td>-</td><td> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td><td align=\"center\">".getberkas(19)."</td>";
	$lamp20= "<td>-</td><td> Fotocopy KTP para ahli waris</td><td align=\"center\">".getberkas(20)."</td>";
	$lamp21= "<td>-</td><td> Fotocopy Surat/keterangan Kematian</td><td align=\"center\">".getberkas(21)."</td>";
	$lamp22= "<td>-</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas(22)."</td>";
	$lamp23= "<td>-</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas(23)."</td>";
	$lamp24= "<td>-</td><td> Fotocopy Kwitansi lelang/Risalah Lelang</td><td align=\"center\">".getberkas(24)."</td>";
	$lamp25= "<td>-</td><td> Fotocopy Keputusan Hakim/Pengadilan</td><td align=\"center\">".getberkas(25)."</td>";
	$lamp26= "<td>-</td><td> Surat Pernyataan Hadiah dari yang mengalihkan hak</td><td align=\"center\">".getberkas(26)."</td>";
	$lamp27= "<td>-</td><td> Fotocopy Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</td><td align=\"center\">".getberkas(27)."</td>";
	$lamp28= "<td>-</td><td> Surat Pelepasan Hak Atas Tanah dari BPN</td><td align=\"center\">".getberkas(28)."</td>";
	
	$lamp30= "<td>-</td><td> Fotocopy KTP Penjual</td><td align=\"center\">".getberkas(30)."</td>";
	$lamp31= "<td>-</td><td> Fotocopy KTP Pembeli</td><td align=\"center\">".getberkas(31)."</td>";
	$lamp32= "<td>-</td><td> Fotocopy Kartu Keluarga Penjual</td><td align=\"center\">".getberkas(32)."</td>";
	$lamp33= "<td>-</td><td> Fotocopy Surat Keterangan Kepemilikan</td><td align=\"center\">".getberkas(33)."</td>";
	$lamp34= "<td>-</td><td> Fotocopy Kartu Keluarga WP</td><td align=\"center\">".getberkas(34)."</td>";
	$lamp35= "<td>-</td><td> Harga Sudah Tercantum Surat Hadiah</td><td align=\"center\">".getberkas(35)."</td>";
	$lamp36= "<td>-</td><td> Fotocopy Keterangan Badan Hukum, Hadiah, Lelang</td><td align=\"center\">".getberkas(36)."</td>";
	$lamp37= "<td>-</td><td> Harga Transaksi Tercantum Dalam Risalah Lelang</td><td align=\"center\">".getberkas(37)."</td>";
	
	$lamp38= "<td>-</td><td> Foto Rumah/Objek</td><td align=\"center\">".getberkas(38)."</td>";
	$lamp39= "<td>-</td><td> Foto Lokasi Google Map/Denah</td><td align=\"center\">".getberkas(39)."</td>";
	$lamp40= "<td>-</td><td> - Sertifikat Lampiran 1</td><td align=\"center\">".getberkas(40)."</td>";
	$lamp41= "<td>-</td><td> - Sertifikat Lampiran 2</td><td align=\"center\">".getberkas(41)."</td>";
	$lamp42= "<td>-</td><td> - Sertifikat Lampiran 3</td><td align=\"center\">".getberkas(42)."</td>";
	$lamp43= "<td>-</td><td> - Sertifikat Lampiran 4</td><td align=\"center\">".getberkas(43)."</td>";
	$lamp44= "<td>-</td><td> - Sertifikat Lampiran 5</td><td align=\"center\">".getberkas(44)."</td>";
	
	$lamp45= "<td>-</td><td> Surat Risalah Lelang</td><td align=\"center\">".getberkas(45)."</td>";
	$lamp46= "<td>-</td><td> Upload Berkas NPWP</td><td align=\"center\">".getberkas(46)."</td>";
	$lamp47= "<td>-</td><td> Lain-Lain (Document Pendukung)</td><td align=\"center\">".getberkas(47)."</td>";
	
	$lampskk= "<td>-</td><td> Fotocopy Surat Keterangan Kepemilikan</td>";

}else{
	$lamp1= "<td>-</td><td width=\"60%\">SSPD Yang Sudah Ditanda Tangani Oleh Wajib Pajak Dan PPAT</td><td width=\"37%\" align=\"center\">".getberkas_manual(1)."</td>";
	$lamp2= "<td>-</td><td> SSPD-BPHTB</td><td  align=\"center\">".getberkas_manual(2)."</td>";
	$lamp3= "<td>-</td><td> Fotocopy KTP WP yang masih berlaku/Keterangan domisili</td><td  align=\"center\">".getberkas_manual(3)."</td>";
	$lamp4= "<td>-</td><td> Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</td><td align=\"center\">".getberkas_manual(4)."</td>";
	$lamp5= "<td>-</td><td> Fotocopy SPPT PBB</td><td align=\"center\">".getberkas_manual(5)."</td>";
	$lamp6= "<td>-</td><td> Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang</td><td align=\"center\">".getberkas_manual(6)."</td>";
	$lamp7= "<td>-</td><td> Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</td><td align=\"center\">".getberkas_manual(7)."</td>";
	$lamp8= "<td>-</td><td> Daftar harga/Pricelist dalam hal pembelian dan pengembangan</td><td align=\"center\">".getberkas_manual(8)."</td>";
	$lamp9= "<td>-</td><td> Fotocopy Surat Keterangan jual/beli</td><td align=\"center\">".getberkas_manual(9)."</td>";
	$lamp10= "<td>-</td><td> Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak</td><td align=\"center\">".getberkas_manual(10)."</td>";
	$lamp11= "<td>-</td><td> Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku</td><td align=\"center\">".getberkas_manual(11)."</td>";
	$lamp12= "<td>-</td><td> Fotocopy Surat Keterangan Waris atau Akta Hibah</td><td align=\"center\">".getberkas_manual(12)."</td>";
	$lamp13= "<td>-</td><td> Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat</td><td align=\"center\">".getberkas_manual(13)."</td>";
	$lamp14= "<td>-</td><td> Fotocopy Surat/Keterangan Kematian</td><td align=\"center\">".getberkas_manual(14)."</td>";
	$lamp15= "<td>-</td><td> Fotocopy Surat Pernyataan Waris</td><td align=\"center\">".getberkas_manual(15)."</td>";
	$lamp16= "<td>-</td><td> Fotocopy Surat Kuasa Waris dalam hal Dikuasakan</td><td align=\"center\">".getberkas_manual(16)."</td>";
	$lamp17= "<td>-</td><td> Fotocopy Akta Pendirian Perusahaan yang terbaru</td><td align=\"center\">".getberkas_manual(17)."</td>";
	$lamp18= "<td>-</td><td> Fotocopy NPWP Perusahaan</td><td align=\"center\">".getberkas_manual(18)."</td>";
	$lamp19= "<td>-</td><td> Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya</td><td align=\"center\">".getberkas_manual(19)."</td>";
	$lamp20= "<td>-</td><td> Fotocopy KTP para ahli waris</td><td align=\"center\">".getberkas_manual(20)."</td>";
	$lamp21= "<td>-</td><td> Fotocopy Surat/keterangan Kematian</td><td align=\"center\">".getberkas_manual(21)."</td>";
	$lamp22= "<td>-</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas_manual(22)."</td>";
	$lamp23= "<td>-</td><td> Fotocopy Surat Pernyataan waris</td><td align=\"center\">".getberkas_manual(23)."</td>";
	$lamp24= "<td>-</td><td> Fotocopy Kwitansi lelang/Risalah Lelang</td><td align=\"center\">".getberkas_manual(24)."</td>";
	$lamp25= "<td>-</td><td> Fotocopy Keputusan Hakim/Pengadilan</td><td align=\"center\">".getberkas_manual(25)."</td>";
	$lamp26= "<td>-</td><td> Surat Pernyataan Hadiah dari yang mengalihkan hak</td><td align=\"center\">".getberkas_manual(26)."</td>";
	$lamp27= "<td>-</td><td> Fotocopy Surat Penegasan Persetujuan Pemberian Kredit (SP3K) dari bank</td><td align=\"center\">".getberkas_manual(27)."</td>";
	$lamp28= "<td>-</td><td> Surat Pelepasan Hak Atas Tanah dari BPN</td><td align=\"center\">".getberkas_manual(28)."</td>";
	
	$lamp30= "<td>-</td><td> Fotocopy KTP Penjual</td><td align=\"center\">".getberkas(30)."</td>";
	$lamp31= "<td>-</td><td> Fotocopy KTP Pembeli</td><td align=\"center\">".getberkas(31)."</td>";
	$lamp32= "<td>-</td><td> Fotocopy Kartu Keluarga Penjual</td><td align=\"center\">".getberkas(32)."</td>";
	$lamp33= "<td>-</td><td> Fotocopy Surat Keterangan Kepemilikan</td><td align=\"center\">".getberkas(33)."</td>";
	$lamp34= "<td>-</td><td> Fotocopy Kartu Keluarga WP</td><td align=\"center\">".getberkas(34)."</td>";
	$lamp35= "<td>-</td><td> Harga Sudah Tercantum Surat Hadiah</td><td align=\"center\">".getberkas(35)."</td>";
	$lamp36= "<td>-</td><td> Fotocopy Keterangan Badan Hukum, Hadiah, Lelang</td><td align=\"center\">".getberkas(36)."</td>";
	$lamp37= "<td>-</td><td> Harga Transaksi Tercantum Dalam Risalah Lelang</td><td align=\"center\">".getberkas(37)."</td>";
	
	$lamp38= "<td>-</td><td> Foto Rumah/Objek</td><td align=\"center\">".getberkas(38)."</td>";
	$lamp39= "<td>-</td><td> Foto Lokasi Google Map/Denah</td><td align=\"center\">".getberkas(39)."</td>";
	$lamp40= "<td>-</td><td> - Sertifikat Lampiran 1</td><td align=\"center\">".getberkas(40)."</td>";
	$lamp41= "<td>-</td><td> - Sertifikat Lampiran 2</td><td align=\"center\">".getberkas(41)."</td>";
	$lamp42= "<td>-</td><td> - Sertifikat Lampiran 3</td><td align=\"center\">".getberkas(42)."</td>";
	$lamp43= "<td>-</td><td> - Sertifikat Lampiran 4</td><td align=\"center\">".getberkas(43)."</td>";
	$lamp44= "<td>-</td><td> - Sertifikat Lampiran 5</td><td align=\"center\">".getberkas(44)."</td>";
	
	$lamp45= "<td>-</td><td> Surat Risalah Lelang</td><td align=\"center\">".getberkas(45)."</td>";
	$lamp46= "<td>-</td><td> Upload Berkas NPWP</td><td align=\"center\">".getberkas(46)."</td>";
	$lamp47= "<td>-</td><td> Lain-Lain (Document Pendukung)</td><td align=\"center\">".getberkas(47)."</td>";
	
	$lampskk= "<td>-</td><td> Fotocopy Surat Keterangan Kepemilikan</td>";
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
	$result['result']="";
	$result['result'] .="<h2>Berkas-berkas yang sudah diupload</h2><br>";
	if($jp==1){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table border=\"0\"  cellpadding=\"1\">
				<tr>
					".$lamp1."
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp8}
				</tr>
				<tr>
					{$lamp9}
				</tr>
			</table>
			
		";
	}else if($jp==2){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp10}
				</tr>
			</table>
			
		";
	}else if($jp==3){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp11}
				</tr>
				<tr>
					{$lamp12}
				</tr>
			</table>
			
		";
	}else if(($jp==4)||($jp==5)){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp13}
				</tr>
				<tr>
					{$lamp14}
				</tr>
				<tr>
					{$lamp15}
				</tr>
				<tr>
					{$lamp16}
				</tr>
			</table>
			
		";
	}else if(($jp==6)||($jp==10)||($jp==11)||($jp==12)){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp17}
				</tr>
				<tr>
					{$lamp18}
				</tr>
				<tr>
					{$lamp19}
				</tr>
			</table>
			
		";
	}else if($jp==7){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp20}
				</tr>
				<tr>
					{$lamp21}
				</tr>
				<tr>
					{$lamp22}
				</tr>
				<tr>
					{$lamp23}
				</tr>
			</table>
			
		";
	}else if($jp==8){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp24}
				</tr>
			</table>
			
		";
	}else if($jp==9){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp25}
				</tr>
			</table>
			
		";
	}else if($jp==13){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp26}
				</tr>
			</table>
			
		";
	}else if($jp==14){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp27}
				</tr>
			</table>
			
		";
	}else if($jp==21){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
				<tr>
					{$lamp28}
				</tr>
			</table>
			
		";
	}else if($jp==22){
		$result['result'] .="<span onclick=\"document.getElementById('id01').style.display='none'\" class=\"w3-button w3-display-topright\">&times;</span>
			<table >
				<tr>
					{$lamp1}
				</tr>
				<tr>
					{$lamp2}
				</tr>
				<tr>
					{$lamp3}
				</tr>
				<tr>
					{$lamp4}
				</tr>
				<tr>
					{$lamp5}
				</tr>
				<tr>
					{$lamp6}
				</tr>
				<tr>
					{$lamp7}
				</tr>
			</table>
			
		";
	}
	$result['success']=true;
	$sResponse = $json->encode($result);
	echo $sResponse;
	
SCANPayment_CloseDB($DBLink);
?>
