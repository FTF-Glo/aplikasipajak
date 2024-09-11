<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'laporanRekapitulasi', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

require_once($sRootPath."inc/phptoexcel/OLEwriter.php");
require_once($sRootPath."inc/phptoexcel/BIFFwriter.php");
require_once($sRootPath."inc/phptoexcel/Worksheet.php");
require_once($sRootPath."inc/phptoexcel/Workbook.php");

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

$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$tanggal = '';
function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysqli_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysqli_fetch_field($mysql_result);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysqli_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysqli_fetch_array($mysql_result);
		  $json.="{\n";
		  for($y=0;$y<count($field_names);$y++) {
			   $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
			   if($y==count($field_names)-1){
					$json.="\n";
			   }
			   else{
					$json.=",\n";
			   }
		  }
		  if($x==$rows-1){
			   $json.="\n}\n";
		  }
		  else{
			   $json.="\n},\n";
		  }
	 }
	 $json.="]\n}";
	 return($json);
}

function getAUTHOR($nop) {
	global $data,$DBLink;
	
	$query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '".$nop."'";

	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		return "Tidak Ditemukan"; 
	}
	$json = new Services_JSON();
	$data =  $json->decode(mysql2json($res,"data"));	
	for ($i=0;$i<count($data->data);$i++) {
		return $data->data[$i]->CPM_SSB_AUTHOR;
	}
	return "Tidak Ditemukan";
}

function getConfigValue ($id,$key) {
	global $DBLink;	
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getDataNotaris($id) {
        global $DBLink;
		
        $qry = "select * from tbl_reg_user_notaris where userId = '".$id."'";
        $res = mysqli_query($DBLink, $qry);
        if ($res === false) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
        }
		$data=array();
        while ($row = mysqli_fetch_assoc($res)) {
             $data['almt_jalan']=$row['almt_jalan'];
        }
		
		return $data;
    }
			
function getDocument($sts,&$dat) {
	global $DBLink,$json,$app,$src,$src2,$srcTgl2;
	$srcTxt = $src;
	$srcTxt2 = $src2;
	$tanggal = " : ".$src;
	$where = " WHERE PAYMENT_FLAG = 1";
	$where2 = "";
	
	$a = 'aBPHTB';
	$DbName = getConfigValue($a,'BPHTBDBNAME');
	$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
	$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
	$DbTable = getConfigValue($a,'ssb');
	$DbUser = getConfigValue($a,'BPHTBUSERNAME');
	$tw = getConfigValue($a,'TENGGAT_WAKTU');
	
	$where = "";
	$where2 = "";
	if ($srcTxt != "") $where .= " AND GW_SSB.ssb.payment_paid >= '".$srcTxt." 00:00:00' ";
	if ($srcTgl2 != "") $where .= " AND GW_SSB.ssb.payment_paid <= '".$srcTgl2." 23:59:59'";
	if ($srcTxt2 != "") $where2 .= " AND SW_SSB.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '".$srcTxt2."'";
	$iErrCode=0;

	
	
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	SCANPayment_ConnectToDB($XDBLink, $XDBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, 'SW_SSB', true);

	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	$query = "SELECT * FROM GW_SSB.ssb
					INNER JOIN
			        SW_SSB.cppmod_ssb_doc
			   		ON
			        SW_SSB.cppmod_ssb_doc.CPM_SSB_ID = GW_SSB.ssb.id_switching
			        INNER JOIN
			        SW_SSB.cppmod_ssb_jenis_hak
			        ON
			        SW_SSB.cppmod_ssb_doc.CPM_OP_JENIS_HAK = SW_SSB.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK 
			        $where
			        $where2
			        ORDER BY GW_SSB.ssb.payment_paid DESC"; 
   
	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		 print_r($query.mysqli_error());
		 return false; 
	}
	
	$d =  $json->decode(mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	//$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	$ss = true;
	$tw = 0;

	for ($i=0;$i<count($data->data);$i++) {
		$dataNotaris=getDataNotaris($data->data[$i]->CPM_SSB_AUTHOR);
		$dataRow[$tw]['NO'] = ($i + 1);
		$dataRow[$tw]['SAVED_DATE'] = $data->data[$i]->saved_date;
		$dataRow[$tw]['ID_SSB'] = str_pad($data->data[$i]->id_ssb,8,'0',STR_PAD_LEFT);
		$dataRow[$tw]['WP_NAMA'] = $data->data[$i]->wp_nama;
		$dataRow[$tw]['WP_ALAMAT'] = $data->data[$i]->wp_alamat;
		$dataRow[$tw]['WP_NOKTP'] = $data->data[$i]->wp_noktp;
		$dataRow[$tw]['WP_NAMA_LAMA'] = $data->data[$i]->CPM_WP_NAMA_LAMA;
		$dataRow[$tw]['CPM_SSB_AUTHOR'] = $data->data[$i]->CPM_SSB_AUTHOR;
		$dataRow[$tw]['ALAMAT_NOTARIS'] = $dataNotaris['almt_jalan'];
		$dataRow[$tw]['CPM_OP_LETAK'] = $data->data[$i]->CPM_OP_LETAK;
		$dataRow[$tw]['CPM_OP_KECAMATAN'] = $data->data[$i]->CPM_OP_KECAMATAN;
		$dataRow[$tw]['CPM_OP_LUAS_TANAH'] = $data->data[$i]->CPM_OP_LUAS_TANAH;
		$dataRow[$tw]['CPM_OP_LUAS_BANGUN'] = $data->data[$i]->CPM_OP_LUAS_BANGUN;
		$dataRow[$tw]['NJOP'] = (($data->data[$i]->CPM_OP_LUAS_TANAH*$data->data[$i]->CPM_OP_NJOP_TANAH)+($data->data[$i]->CPM_OP_LUAS_BANGUN*$data->data[$i]->CPM_OP_NJOP_BANGUN));
		$dataRow[$tw]['PAID'] = $data->data[$i]->bphtb_dibayar;
		$dataRow[$tw]['CPM_OP_HARGA'] = $data->data[$i]->CPM_OP_HARGA;
		$dataRow[$tw]['JENIS_HAK'] = $data->data[$i]->CPM_JENIS_HAK;
		$dataRow[$tw]['TANGGAL'] = $srcTxt;
		$tw ++;
	}
	//totalRows = $tw;
	$dat = $dataRow;
	return true;
}


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);

$sts = $q->sts;
$app = $q->app;
$src = $q->src;
$src2 = $q->src2;
$srcTgl2 = $q->srcTgl2;
function createStrDate($sd) {
		if ($sd != '') {
			$date = explode("/",$sd);
			$dt = $date[2].$date[1].$date[0];
			//$dt = str_replace("/","",$sd);
			return $dt;
		} else {
			return $sd;
		}
	}
function formatDate($sd) {
	if ($sd != '') {
		$yr = substr($sd, 0, 4);  // returns "cde"
		$mt = substr($sd, 4, 2);  // returns "cde"
		$dy = substr($sd, 6, 2);  // returns "cde"
		$dt = $dy."/".$mt."/".$yr;
		return $dt;
	} else {
		return $sd;
	}
}

function HeaderingExcel($filename){
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=$filename");
	header("Expires:0");
	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");
}

HeaderingExcel('laporan_rekapitulasi.xls');

//membuat area kerja
$workbook=new Workbook("-");
//class untuk mencetak tulisan besar dan tebal
$fBesar=& $workbook->add_format();
$fBesar->set_size(14);
$fBesar->set_align("center");
$fBesar->set_bold();

$fBiasa=& $workbook->add_format();
$fBiasa->set_align("left");
//class untuk mencetak tulisan tanpa border (untuk judul laporan)
$fList=& $workbook->add_format();
$fList->set_border(0);
//class untuk mencetak tulisan dengan border dan ditengah kolom (untuk judul kolom)
$fDtlHead=& $workbook->add_format();
$fDtlHead->set_border(1);
$fDtlHead->set_align("center");
$fDtlHead->set_align("vcentre");
$fDtlHead->set_text_wrap(1);

$fDtlCenter=& $workbook->add_format();
$fDtlCenter->set_border(1);
$fDtlCenter->set_align("center");
$fDtlCenter->set_align("vcentre");
$fDtlCenter->set_text_wrap(1);

//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai string)
$fDtl=& $workbook->add_format();
$fDtl->set_border(1);
//class untuk mencetak tulisan dengan border (untuk detil laporan bernilai numerik)
$fDtlNumber=& $workbook->add_format();
$fDtlNumber->set_border(1);
$fDtlNumber->set_align("right");
$fDtlNumber->set_num_format(3);
//class untuk men-zoom laporan 75%
$worksheet1= & $workbook->add_worksheet("Halaman 1");
$worksheet1->set_zoom(100);



$baris = 4;

if (getDocument($sts, $dat)) {
	$total_paid = 0;
	$total_denda = 0;
	$total_all = 0;
	$berkas = 0;
	$hari = '';

	

	foreach ($dat as $row) {
		$worksheet1->set_column($baris,0,25);
		$worksheet1->set_column($baris,1,25);
		$worksheet1->set_column($baris,2,25);
		$worksheet1->set_column($baris,3,25);
		$worksheet1->set_column($baris,4,25);
		$worksheet1->set_column($baris,5,25);
		$worksheet1->set_column($baris,6,25);
		$worksheet1->set_column($baris,7,25);
		$worksheet1->set_column($baris,8,25);
		$worksheet1->set_column($baris,9,25);
		$worksheet1->set_column($baris,10,25);
		$worksheet1->set_column($baris,11,25);
		$worksheet1->set_column($baris,12,25);
		$worksheet1->set_column($baris,13,25);
		$worksheet1->set_column($baris,14,25);
		$worksheet1->set_column($baris,15,25);
		$worksheet1->set_column($baris,15,25);
		$worksheet1->write_string($baris,0,$row['NO'],$fDtlCenter);
		$worksheet1->write_string($baris,1,$row['SAVED_DATE'],$fDtlCenter);
		$worksheet1->write_string($baris,2,$row['ID_SSB'],$fDtlCenter);
		$worksheet1->write_string($baris,3,$row['WP_NAMA'],$fBiasa);
		$worksheet1->write_string($baris,4,$row['WP_ALAMAT'],$fBiasa);
		$worksheet1->write_string($baris,5,$row['WP_NOKTP'],$fDtlCenter);
		$worksheet1->write_string($baris,6,$row['WP_NAMA_LAMA'],$fBiasa);
		$worksheet1->write_string($baris,7,$row['CPM_SSB_AUTHOR'],$fBiasa);
		$worksheet1->write_string($baris,8,$row['ALAMAT_NOTARIS'],$fBiasa);
		$worksheet1->write_string($baris,9,$row['CPM_OP_LETAK'],$fBiasa);
		$worksheet1->write_string($baris,10,$row['CPM_OP_KECAMATAN'],$fDtlCenter);
		$worksheet1->write_string($baris,11,$row['CPM_OP_LUAS_TANAH'],$fDtlCenter);
		$worksheet1->write_string($baris,12,$row['CPM_OP_LUAS_BANGUN'],$fBiasa);
		$worksheet1->write_number($baris,13,$row['NJOP'],$fDtlNumber);
		$worksheet1->write_number($baris,14,$row['PAID'],$fDtlNumber);
		$worksheet1->write_string($baris,15,$row['CPM_OP_HARGA'],$fBiasa);
		$worksheet1->write_string($baris,16,$row['JENIS_HAK'],$fBiasa);
		

		$total_paid = $total_paid + $row['PAID'];
		$total_denda = $total_denda + $row['DENDA'];

		$timestamp = strtotime($row['TANGGAL']);
		$tanggal = $row['TANGGAL'];

		$day = date('D', $timestamp);

		$hari = $day;

		$berkas++;
		$baris++;
	}

	if($hari == 'Sun'){
		$hari = 'MINGGU';
	}else if($hari == 'Mon'){
		$hari = 'SENIN';
	}else if($hari == 'Tue'){
		$hari = 'SELASA';
	}else if($hari == 'Wed'){
		$hari = 'RABU';
	}else if($hari == 'Thu'){
		$hari = 'KAMIS';
	}else if($hari == 'Fri'){
		$hari = 'JUMAT';
	}else if($hari == 'Sat'){
		$hari = 'SABTU';
	}

	$date = explode("-",$tanggal);
	$month = '';
	if($date[1] == '01'){
		$month = "JANUARI";
	}else if($date[1] == '02'){
		$month = "FEBRUARI";
	}else if($date[1] == '03'){
		$month = "MARET";
	}else if($date[1] == '04'){
		$month = "APRIL";
	}else if($date[1] == '05'){
		$month = "MEI";
	}else if($date[1] == '06'){
		$month = "JUNI";
	}else if($date[1] == '07'){
		$month = "JULI";
	}else if($date[1] == '08'){
		$month = "AGUSTUS";
	}else if($date[1] == '09'){
		$month = "SEPTEMBER";
	}else if($date[1] == '10'){
		$month = "OKTOBER";
	}else if($date[1] == '11'){
		$month = "NOVEMBER";
	}else if($date[1] == '12'){
		$month = "DESEMBER";
	}

	$tanggal = $date[2].' '.$month.' '.$date[0];

	//$header = $p->header;
	$worksheet1->write_string(0,0,"REKAPITULASI DATA BPHTB ",$fBesar);
	$worksheet1->set_row(3,30); 
	$worksheet1->set_column(0,0,25);
	//sesuaikan dengan judul kolom pada table anda
	$worksheet1->write_string(1,0,"HARI ",$fDtlHead);
	$worksheet1->write_string(1,1," : ".$hari,$fDtlHead);
	$worksheet1->write_string(2,0,"TANGGAL ",$fDtlHead);
	$worksheet1->write_string(2,1," : ".$tanggal,$fDtlHead);

	$worksheet1->write_string(3,0,"NO",$fDtlHead);
	$worksheet1->write_string(3,1,"TGL VERIFIKASI",$fDtlHead);
	$worksheet1->write_string(3,2,"KODE BAYAR",$fDtlHead);
	$worksheet1->write_string(3,3,"NAMA PEMBELI",$fDtlHead);
	$worksheet1->write_string(3,4,"ALAMAT PEMBELI",$fDtlHead);
	$worksheet1->write_string(3,5,"KTP PEMBELI",$fDtlHead);
	$worksheet1->write_string(3,6,"ALAMAT PENJUAL",$fDtlHead);
	$worksheet1->write_string(3,7,"NOTARIS/PPAT",$fDtlHead);
	$worksheet1->write_string(3,8,"ALAMAT NOTARIS",$fDtlHead);
	$worksheet1->write_string(3,9,"LETAK OP",$fDtlHead);
	$worksheet1->write_string(3,10,"KECAMATAN OP",$fDtlHead);
	$worksheet1->write_string(3,11,"LUAS TANAH",$fDtlHead);
	$worksheet1->write_string(3,12,"LUAS BANGUNAN",$fDtlHead);
	$worksheet1->write_string(3,13,"NJOP",$fDtlHead);
	$worksheet1->write_string(3,14,"BPHTB",$fDtlHead);
	$worksheet1->write_string(3,15,"TRANSAKSI",$fDtlHead);
	$worksheet1->write_string(3,16,"KETERANGAN",$fDtlHead);

	$worksheet1->merge_cells(0,0, 0, 5);
	// $worksheet1->merge_cells(1,0, 1, 5);
	// $worksheet1->merge_cells(2,0, 2, 5);

	$total_all = $total_paid + $total_denda;
	$str_berkas = $berkas.' BERKAS';

	$baris = $baris + 2;
	$worksheet1->merge_cells($baris, 0, $baris, 12);
	$worksheet1->set_column($baris,3,26);
	$worksheet1->set_column($baris,5,26);
	$worksheet1->set_column($baris,6,26);
	$worksheet1->set_column($baris,7,26);
	$worksheet1->write_string($baris,3,'TOTAL',$fBiasa);
	$worksheet1->write_string($baris,14,$total_paid,$fDtlNumber);
	
	$baris = $baris + 2;
	$worksheet1->merge_cells($baris, 0, $baris, 12);
	$worksheet1->set_column($baris,3,26);
	$worksheet1->set_column($baris,5,26);
	$worksheet1->set_column($baris,6,26);
	$worksheet1->set_column($baris,7,26);
	$worksheet1->write_string($baris,3,'TOTAL BERKAS',$fBiasa);
	$worksheet1->write_string($baris,5,$str_berkas,$fBiasa);
	$worksheet1->write_string($baris,14,$total_all,$fDtlNumber);

}

$workbook->close();

?>
