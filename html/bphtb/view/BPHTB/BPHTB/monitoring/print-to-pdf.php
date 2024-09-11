<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/tcpdf/tcpdf.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/central/user-central.php");

require_once("svc-bpn-lookup.php");

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

function getSPPTInfo($nop) {
	$whereClause = "NOP = '".$nop."'";
	$iErrCode=0;
	LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}
	
	$query = "SELECT PAYMENT_FLAG FROM PBB_SPPT WHERE NOP = '".$nop."'";

	$res = mysqli_query($LDBLink, $query);
	if ( $res === false ){
		return "Tidak Ditemukan"; 
	}
	$json = new Services_JSON();
	$data =  $json->decode(mysql2json($res,"data"));	
	for ($i=0;$i<count($data->data);$i++) {
		
		return $data->data[$i]->PAYMENT_FLAG?"Sudah Dibayar":"Siap Dibayar";
	}
	return "Tidak Ditemukan";
}

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
			
function getDocument($sts,&$dat) {
	global $DBLink,$json,$app,$src;
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
	if ($srcTxt != "") $where .= " AND wp_nama LIKE '".$srcTxt."%'";
	$iErrCode=0;
	
	SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	if ($iErrCode != 0)
	{
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  exit(1);
	}

	$query = "SELECT * FROM $DbTable $where ORDER BY saved_Date "; 
   
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
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td align=\"center\">".$data->data[$i]->op_nomor."</td> \n";
		$HTML .= "\t\t<td>".$data->data[$i]->wp_nama."</td> \n";
		$HTML .= "\t\t<td>".$data->data[$i]->wp_alamat."</td>
				  <td align=\"center\">".getAUTHOR($data->data[$i]->op_nomor)."</td>\n";
		$HTML .= "\t\t<td align=\"right\">".number_format($data->data[$i]->bphtb_dibayar,0,".",",")."</td>\n";
		$HTML .= "\t\t<td align=\"center\">".substr($data->data[$i]->payment_paid,0,10)."</td> \n";
		$HTML .= "\t</tr>\n";
		$tw ++;
	}
	//totalRows = $tw;
	$dat = $HTML;
	return true;
}

function headerContentAll($sts) {
	$j = base64_encode("{'sts':'".$sts."'}");
	$HTML = "<table width=\"720\" cellpadding=\"4\" cellspacing=\"0\" border=\"1\" >\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<th width=\"100\" align=\"center\"> Nomor Objek Pajak</th> \n";
	$HTML .= "\t\t<th width=\"100\" align=\"center\"> Wajib Pajak </th> \n";
	$HTML .= "\t\t<th width=\"150\" align=\"center\">Alamat Objek Pajak</th>
				<th width=\"100\" align=\"center\">User</th>
				<th width=\"100\" align=\"center\">BPHTB yang harus dibayar</th>\n";
	$HTML .= "\t\t<th width=\"80\" align=\"center\">Tanggal Pembayaran</th>\n";
	$HTML .= "\t</tr>\n";

	if (getDocument($sts,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
	}
	$HTML .= "</table>\n";
	return $HTML;
}
function getContent($sts) {
	$HTML = "";
	$HTML = headerContentAll($sts);
	
	return $HTML;
}
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);

$sts = $q->sts;
$app = $q->app;
$src = $q->src;
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 002');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
//$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//$pdf->SetFont('helvetica', 'B', 20);

// add a page
//$pdf->AddPage();

//$pdf->Write(0, 'Example of HTML tables', '', 0, 'L', true, 0, false, false, 0);

$pdf->SetProtection($permissions=array('modify'), $user_pass='', $owner_pass=null, $mode=0, $pubkeys=null);
$pdf->SetFont('helvetica', '', 7);
$pdf->AddPage('P', 'F4');
$HTML = getContent($sts);
$pdf->writeHTML($HTML, true, false, false, false, '');

$pdf->Output(date("Ymdhis").".pdf", 'I');

//echo getContent($sts);
?>
