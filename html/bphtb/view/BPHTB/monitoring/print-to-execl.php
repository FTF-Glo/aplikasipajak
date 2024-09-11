<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

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

function getNOP($author){
	global $DBLink;
	
	$query = "SELECT CPM_OP_NOMOR FROM cppmod_ssb_doc WHERE  CPM_SSB_AUTHOR like '%".$author."%'";	
	$res = mysqli_query($DBLink, $query);
	$arr = array();
	while($d = mysqli_fetch_object($res)){
		$arr[] = $d->CPM_OP_NOMOR;	
	}
	return "'".implode("','",$arr)."'";		
}
			
function getDocument($sts,&$dat) {
	global $DBLink,$json,$app,$src,$find_notaris,$tgl1,$tgl2;
	$srcTxt = $src;
	$where = "";
	
	$a = $app;
	$DbName = getConfigValue($a,'BPHTBDBNAME');
	$DbHost = getConfigValue($a,'BPHTBHOSTPORT');
	$DbPwd = getConfigValue($a,'BPHTBPASSWORD');
	$DbTable = getConfigValue($a,'BPHTBTABLE');
	$DbUser = getConfigValue($a,'BPHTBUSERNAME');
	$tw = getConfigValue($a,'TENGGAT_WAKTU');
	
	if ($sts == 1) $where = " WHERE PAYMENT_FLAG = 1";
	if ($sts == 2) $where = " WHERE PAYMENT_FLAG = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) > NOW()";
	if ($sts == 3) $where = " WHERE PAYMENT_FLAG = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) < NOW()";
	if ($sts == 4) $where = " WHERE bphtb_dibayar = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) > NOW()";
	
	if ($srcTxt != "") $where .= " AND wp_nama LIKE '".mysqli_escape_string($DBLink, $srcTxt)."%'";
	if ($find_notaris != "") $where .= " AND (op_nomor in (".getNOP($find_notaris)."))";
	
	if($tgl1 !="" && $tgl2 !="") $where .= " AND  (payment_paid between '".mysqli_escape_string($DBLink, $tgl1)."' and '".mysqli_escape_string($DBLink, $tgl2)."')";
	elseif($tgl1 !="") $where .= " AND  (payment_paid = '".mysqli_escape_string($DBLink, $tgl1)."')";
	elseif($tgl2 !="") $where .= " AND  (payment_paid = '".mysqli_escape_string($DBLink, $tgl2)."')";
	
	$iErrCode=0;

	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	$query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC"; 
   
	$res = mysqli_query($LDBLink, $query);
	if ( $res === false ){
		 print_r($query.mysqli_error($LDBLink));
		 return false; 
	}
	
	$d =  $json->decode(mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	//$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	$ss = true;
	$tw = 0;
	for ($i=0;$i<count($data->data);$i++) {
		$dataRow[$tw]['OP_NOMOR'] = $data->data[$i]->op_nomor;
		$dataRow[$tw]['WP_NAMA'] = $data->data[$i]->wp_nama;
		$dataRow[$tw]['WP_ALAMAT'] = $data->data[$i]->wp_alamat;
		$dataRow[$tw]['AUTHOR'] = getAUTHOR($data->data[$i]->op_nomor);
		$dataRow[$tw]['PAID'] = $data->data[$i]->bphtb_dibayar;
		$dataRow[$tw]['PAID_DATE'] = $data->data[$i]->payment_paid;
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
$find_notaris = $q->find_notaris;
$tgl1= $q->tgl1;
$tgl2= $q->tgl2;

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

HeaderingExcel('bphtb-report.xls');

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

//$header = $p->header;
$worksheet1->write_string(0,0,"DAFTAR BPHTB ",$fBesar);
$worksheet1->set_row(3,30); 
$worksheet1->set_column(0,0,10);
//sesuaikan dengan judul kolom pada table anda
$worksheet1->write_string(3,0,"Nomor Objek Pajak",$fDtlHead);
$worksheet1->write_string(3,1,"Wajib Pajak",$fDtlHead);
$worksheet1->write_string(3,2,"Alamat Wajib Pajak",$fDtlHead);
$worksheet1->write_string(3,3,"User",$fDtlHead);
$worksheet1->write_string(3,4,"BPHTB Yang Harus Dibayar",$fDtlHead);
$worksheet1->write_string(3,5,"Tanggal Pembayaran",$fDtlHead);

$worksheet1->merge_cells(0,0, 0, 5);
$worksheet1->merge_cells(1,0, 1, 5);
$worksheet1->merge_cells(2,0, 2, 5);

$baris = 4;

if($this->getDocument($sts, $dat)) {
	foreach ($dat as $row) {
		$worksheet1->set_column($baris,0,25);
		$worksheet1->set_column($baris,1,25);
		$worksheet1->set_column($baris,2,25);
		$worksheet1->set_column($baris,3,15);
		$worksheet1->set_column($baris,4,15);
		$worksheet1->set_column($baris,5,15);
		$worksheet1->write_string($baris,0,$row['OP_NOMOR'],$fDtlCenter);
		$worksheet1->write_string($baris,1,$row['WP_NAMA'],$fBiasa);
		$worksheet1->write_string($baris,2,$row['WP_ALAMAT'],$fBiasa);
		$worksheet1->write_string($baris,3,$row['AUTHOR'],$fDtlCenter);
		$worksheet1->write_number($baris,4,$row['PAID'],$fDtlNumber);
		$worksheet1->write_string($baris,5,$row['PAID_DATE'],$fDtlCenter);
		$baris++;
	}
}

$workbook->close();

?>
