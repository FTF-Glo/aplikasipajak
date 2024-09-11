<?php
//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'bpn', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once("svc-bpn-lookup.php");

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/bpn/mod-bpn.css\" type=\"text/css\">\n";


	
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
               $json.="'$field_names[$y]' :	'$row[$y]'";
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

function getSPPTInfo($nop) {
	$whereClause = "NOP = '".$nop."'";

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
		return $data->data[$i]->PAYMENT_FLAG?"Lunas":"Belum Lunas";
	}
	return "Tidak Ditemukan";
}

function getDocument($sts,&$dat) {
	global $data,$appDbLink;
	$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
	$where = "";
	if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
	$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
	AND B.CPM_TRAN_STATUS=".$sts." AND  B.CPM_TRAN_FLAG=0  $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT 0,200";

	$res = mysqli_query($appDbLink, $query);
	if ( $res === false ){
		return false; 
	}
	$json = new Services_JSON();
	$d =  $json->decode(mysql2json($res,"data"));	
	$HTML = "";
	$data = $d;
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 

	for ($i=0;$i<count($data->data);$i++) {
		$a = strval($data->data[$i]->CPM_OP_LUAS_BANGUN)*strval($data->data[$i]->CPM_OP_NJOP_BANGUN)+
			strval($data->data[$i]->CPM_OP_LUAS_TANAH)*strval($data->data[$i]->CPM_OP_NJOP_TANAH);
		$b = strval($data->data[$i]->CPM_OP_HARGA);
		if ($b < $a) $npop = $a; else $npop = $b;
		
		$par1 = $params."&f=f317-bpn&idssb=".$data->data[$i]->CPM_SSB_ID;
		$class = $i%2==0 ? "tdbody1":"tdbody2";
		$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".getSPPTInfo($data->data[$i]->CPM_OP_NOMOR)."</td> \n";
		$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_ALAMAT."</td>
				<td class=\"".$class."\" align=\"center\">".number_format(($npop-strval($data->data[$i]->CPM_OP_NPOPTKP))*0.05, 2, '.', ',')."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
		$HTML .= "\t</tr></div>\n";
	}
	$dat = $HTML;
	return true;
}


function contentBPNApprove() {
	$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
	$HTML .= "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Status Pembayaran</td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"> Total Bayar </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	if (getDocument(5,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "Data Kosong !";
	}
	$HTML .= "</table>\n";
	return $HTML;
}

function displayContent($selected) {
	echo "<div id=\"notaris-main-content\">\n";
	echo "\t<div id=\"notaris-main-content-inner\">\n";	
	echo contentBPNApprove();
	echo "\t</div>\n";
	echo "\t<div id=\"notaris-main-content-footer\">\n";
	/*echo "\t\t<select id=\"perItems\">\n";
	echo "\t\t\t<option value=\"10\">10</option>\n";
	echo "\t\t\t<option value=\"25\">25</option>\n";
	echo "\t\t\t<option value=\"50\">50</option>\n";
	echo "\t\t\t<option value=\"75\">75</option>\n";
	echo "\t\t\t<option value=\"100\">100</option>\n";
	echo "\t\t\t<option value=\"150\">150</option>\n";
	echo "\t\t</select>\n";
	echo "\t</div>\n";*/
	echo "</div>\n";
}
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
displayContent($sel);
?>
