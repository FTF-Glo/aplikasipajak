<?php


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';
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

function mysql2json($mysql_result,$name){
	 $json="{\n'$name': [\n";
	 $field_names = array();
	 $fields = mysql_num_fields($mysql_result);
	 for($x=0;$x<$fields;$x++){
		  $field_name = mysql_fetch_field($mysql_result, $x);
		  if($field_name){
			   $field_names[$x]=$field_name->name;
		  }
	 }
	 $rows = mysql_num_rows($mysql_result);
	 for($x=0;$x<$rows;$x++){
		  $row = mysql_fetch_array($mysql_result);
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
	
	$query = "SELECT CPM_SSB_AUTHOR FROM CPPMOD_SSB_DOC WHERE CPM_OP_NOMOR = '".$nop."'";

	$res = mysql_query($query, $DBLink);
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
			
function getDocument($sts,&$dat) {
	global $DBLink,$json,$app,$src,$src2,$src3,$src4,$src5;
	$srcTxt = $src;
	$srcTxt2 = $src2;
	$srcTxt3 = $src3;
	$srcTxt4 = $src4;
	$srcTxt5 = $src5;
	
	$where = "";
	$where2 = "";
	$where3 = "";
	$where4 = "";
	$where5 = "";
    
	$a = 'aBPHTB';
	$DbName = getConfigValue($a,'BPHTBDBNAME');
	$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
	$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
	$DbTable = getConfigValue($a,'BPHTBTABLE');
	$DbUser = getConfigValue($a,'BPHTBUSERNAME');
	$tw = getConfigValue($a,'TENGGAT_WAKTU');
	$DbNameSW = getConfigValue($a,'BPHTBDBNAMESW');
	
	if ($srcTxt != "") $where .= " WHERE $DbName.ssb.payment_paid >= '".$srcTxt." 00:00:00'";

	if ($srcTxt3 != ""){
		if ($srcTxt != ""){
			$where2 .= " AND $DbName.ssb.payment_paid <= '".$srcTxt3." 23:59:59'";
		}else{
			$where2 .= " WHERE $DbName.ssb.payment_paid <= '".$srcTxt3." 23:59:59'";
		}
	} 

	if($srcTxt2 != ""){
		if ($srcTxt == "" && $srcTxt3 == "") {
			$where3 .= " WHERE $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = '".$srcTxt2."'";
		}else{		
			$where3 .= " AND $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = '".$srcTxt2."'";
		}
	}
		
	if($srcTxt4 != ""){
		if ($srcTxt == "" && $srcTxt3 == "" && $srcTxt2 == "") {
			$where4 .= " WHERE $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA >= '".$srcTxt4."'";
		}else{
			$where4 .= " AND $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA >= '".$srcTxt4."'";
		}
	}
		

	if($srcTxt5 != ""){
		if ($srcTxt == "" && $srcTxt3 == "" && $srcTxt2 == "" && $srcTxt4 == "") {
			$where5 .= " WHERE $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA <= '".$srcTxt5."'";
		}else{
			$where5 .= " AND $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA <= '".$srcTxt5."'";
		}
	}

	$iErrCode=0;
	
	
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	SCANPayment_ConnectToDB($XDBLink, $XDBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, $DbNameSW, true);

	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	$query = "SELECT $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK, $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_JENIS_HAK, SUM($DbNameSW.CPPMOD_SSB_DOC.CPM_OP_HARGA) AS CPM_OP_HARGA FROM $DbName.ssb
					INNER JOIN
			        $DbNameSW.CPPMOD_SSB_DOC
			   		ON
			        $DbNameSW.CPPMOD_SSB_DOC.CPM_SSB_ID = $DbName.ssb.id_switching
			        INNER JOIN
			        $DbNameSW.CPPMOD_SSB_JENIS_HAK
			        ON
			        $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK = $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_KD_JENIS_HAK 
			        $where
			        $where2
			        $where3
			        $where4
			        $where5
			        GROUP BY $DbNameSW.CPPMOD_SSB_DOC.CPM_OP_JENIS_HAK, $DbNameSW.CPPMOD_SSB_JENIS_HAK.CPM_KD_JENIS_HAK
			        ORDER BY $DbName.ssb.payment_paid DESC";
	//echo $query;exit();
	$res = mysql_query($query);
	if ( $res === false ){
		 print_r($query.mysql_error());
		 return false; 
	}
	
	$d =  $json->decode(mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	//$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	$ss = true;
	$tw = 0;
	$dataRow = array();

	for ($i=0;$i<count($data->data);$i++) {
		$dataRow[$tw]['NO'] = ($i + 1);
		$dataRow[$tw]['KODE'] = $data->data[$i]->CPM_OP_JENIS_HAK;
		$dataRow[$tw]['JENIS_HAK'] = $data->data[$i]->CPM_JENIS_HAK;
		$dataRow[$tw]['TRANSAKSI'] = $data->data[$i]->CPM_OP_HARGA;
		// $dataRow[$tw]['TANGGAL'] = $srcTxt;
		// $dataRow[$tw]['TANGGAL2'] = $srcTxt3;
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
$src3 = $q->src3;
$src4 = $q->src4;
$src5 = $q->src5;
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

HeaderingExcel('bphtb-daily-report.xls');

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
if (getDocument($sts,$dat)) {
	foreach ($dat as $row) {
		$worksheet1->set_column($baris,0,25);
		$worksheet1->set_column($baris,1,25);
		$worksheet1->set_column($baris,2,25);
		$worksheet1->set_column($baris,3,25);
		$worksheet1->write_number($baris,0,$row['NO'],$fDtlCenter);
		$worksheet1->write_string($baris,1,$row['KODE'],$fDtlCenter);
		$worksheet1->write_string($baris,2,$row['JENIS_HAK'],$fBiasa);
		$worksheet1->write_string($baris,3,$row['TRANSAKSI'],$fBiasa);

		$baris++;
	}


	$tanggal = $src;
	$tanggal2 = $src3;
	$date = explode("-",$tanggal);
	$date2 = explode("-",$tanggal2);
	$month = '';
	$month2 = '';

	if($tanggal != ''){
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
	}
	
	if($tanggal2 != ''){
		if($date2[1] == '01'){
		$month2 = "JANUARI";
		}else if($date2[1] == '02'){
			$month2 = "FEBRUARI";
		}else if($date2[1] == '03'){
			$month2 = "MARET";
		}else if($date2[1] == '04'){
			$month2 = "APRIL";
		}else if($date2[1] == '05'){
			$month2 = "MEI";
		}else if($date2[1] == '06'){
			$month2 = "JUNI";
		}else if($date2[1] == '07'){
			$month2 = "JULI";
		}else if($date2[1] == '08'){
			$month2 = "AGUSTUS";
		}else if($date2[1] == '09'){
			$month2 = "SEPTEMBER";
		}else if($date2[1] == '10'){
			$month2 = "OKTOBER";
		}else if($date2[1] == '11'){
			$month2 = "NOVEMBER";
		}else if($date2[1] == '12'){
			$month2 = "DESEMBER";
		}

		$tanggal2 = $date2[2].' '.$month2.' '.$date2[0];
	}

	//$header = $p->header;
	$worksheet1->write_string(0,0,"REKAPITULASI DATA BPHTB ",$fBesar);
	$worksheet1->set_row(3,30); 
	$worksheet1->set_column(0,0,25);
	//sesuaikan dengan judul kolom pada table anda
	$worksheet1->write_string(1,0," ",$fDtlHead);
	// $worksheet1->write_string(1,1," ".$hari,$fDtlHead);
	$worksheet1->write_string(2,0,"TANGGAL ",$fDtlHead);
	
	if($tanggal == ''){
		if($tanggal2 == ''){
			$worksheet1->write_string(2,1," : ALL DATE ",$fDtlHead);
		}else{
			$worksheet1->write_string(2,1," : Dibawah Tanggal ".$tanggal2,$fDtlHead);
		}
	}else if ($tanggal2 == '') {
		$worksheet1->write_string(2,1," : Diatas Tanggal ".$tanggal,$fDtlHead);
	}else{
		$worksheet1->write_string(2,1," : ".$tanggal." s/d ".$tanggal2,$fDtlHead);
	}
	

	$worksheet1->write_string(3,0,"NO",$fDtlHead);
	$worksheet1->write_string(3,1,"KODE",$fDtlHead);
	$worksheet1->write_string(3,2,"JENIS PEROLEHAN",$fDtlHead);
	$worksheet1->write_string(3,3,"HARGA TRANSAKSI",$fDtlHead);

	$worksheet1->merge_cells(0,0, 0, 5);
	$worksheet1->merge_cells(1,0, 1, 5);
	// $worksheet1->merge_cells(2,0, 2, 5);
}

$workbook->close();

?>
