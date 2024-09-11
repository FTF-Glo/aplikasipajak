<?php

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'laporanHarianBaru', '', dirname(__FILE__))).'/';
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
		echo mysqli_error();
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}
			
// function getDocument($sts,&$dat) {
	// global $DBLink,$json,$app,$src,$src2;
	// $srcTxt = $src;
	// $srcTxt2 = $src2;
	// $tanggal = " : ".$src;
	// $where = "";
	// $where2 = "";
	// if ($srcTxt != "") $where .= " WHERE GW_SSB.ssb.payment_paid > '".$srcTxt." 00:00:00' AND GW_SSB.ssb.payment_paid < '".$srcTxt." 23:59:59'";
	// if ($srcTxt2 != "") $where2 .= " AND SW_SSB.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '".$srcTxt2."'";
	// $iErrCode=0;

	// $a = 'abpn';
	// $DbName = getConfigValue($a,'BPHTBDBNAME');
	// $DbHost = getConfigValue($a,'BPHTBHOSTPORT');
	// $DbPwd = getConfigValue($a,'BPHTBPASSWORD');
	// $DbTable = getConfigValue($a,'ssb');
	// $DbUser = getConfigValue($a,'BPHTBUSERNAME');
	// $tw = getConfigValue($a,'TENGGAT_WAKTU');
	
	// SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
	// SCANPayment_ConnectToDB($XDBLink, $XDBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, 'SW_SSB', true);

	// if ($iErrCode != 0)
	// {
	  // $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  // if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		// error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	  // exit(1);
	// }
	
	// $query = "SELECT * FROM GW_SSB.ssb
					// INNER JOIN
			        // SW_SSB.cppmod_ssb_doc
			   		// ON
			        // SW_SSB.cppmod_ssb_doc.CPM_SSB_ID = GW_SSB.ssb.id_switching
			        // INNER JOIN
			        // SW_SSB.cppmod_ssb_jenis_hak
			        // ON
			        // SW_SSB.cppmod_ssb_doc.CPM_OP_JENIS_HAK = SW_SSB.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK 
			        // $where
			        // $where2
			        // ORDER BY GW_SSB.ssb.payment_paid DESC"; 
	// //echo $query;exit;
	// $res = mysqli_query($query);
	// if ( $res === false ){
		 // print_r($query.mysqli_error());
		 // return false; 
	// }
	
	// $d =  $json->decode(mysql2json($res,"data"));	
	// $HTML = "";
	// $data = $d;
	// //$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	// $ss = true;
	// $tw = 0;

	// for ($i=0;$i<count($data->data);$i++) {
		// $dataRow[$tw]['NO'] = ($i + 1);
		// $dataRow[$tw]['ID_SSB'] = $data->data[$i]->id_ssb;
		// $dataRow[$tw]['WP_NAMA'] = $data->data[$i]->wp_nama;
		// $dataRow[$tw]['WP_ALAMAT'] = $data->data[$i]->wp_alamat;
		// $dataRow[$tw]['OP_NOMOR'] = $data->data[$i]->op_nomor;
		// $dataRow[$tw]['NOMOR_SERTIFIKAT'] = $data->data[$i]->CPM_OP_NMR_SERTIFIKAT;
		// $dataRow[$tw]['PAID'] = $data->data[$i]->bphtb_dibayar;
		// $dataRow[$tw]['DENDA'] = $data->data[$i]->CPM_DENDA;
		// $dataRow[$tw]['NOTARIS'] = $data->data[$i]->bphtb_notaris;
		// $dataRow[$tw]['JENIS_HAK'] = $data->data[$i]->CPM_JENIS_HAK;
		// $dataRow[$tw]['TANGGAL'] = $srcTxt;
		// $tw ++;
	// }
	// //totalRows = $tw;
	// $dat = $dataRow;
	// return true;
// }


$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);

$sts = $q->sts;
$app = $q->app;
$src = $q->src;
$src2 = $q->src2;
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

function getDocument(&$dat) {
		global $data,$DBLink,$json,$src;
		$srcTxt = @isset($src)?$src:date('Y-m-d');
		$srcTxt2 = @isset($src2)?$src2:"";
		
		if($srcTxt == ''  && $srcTxt2 == ''){
			$srcTxt = date('Y-m-d');
		}

		// $where = " WHERE PAYMENT_FLAG = 1";
		$where = "";
		$where2 = "";
		if ($srcTxt != "") $where .= " WHERE GW_SSB.ssb.payment_paid > '".$srcTxt." 00:00:00' AND GW_SSB.ssb.payment_paid < '".$srcTxt." 23:59:59'";
		if ($srcTxt2 != "") $where2 .= " AND SW_SSB.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '".$srcTxt2."'";
		$iErrCode=0;
		$a = 'abpn';
		$DbName = getConfigValue("aBPHTB",'BPHTBDBNAME');
		$DbHost = getConfigValue("aBPHTB",'BPHTBHOSTPORT');
		$DbPwd = getConfigValue("aBPHTB",'BPHTBPASSWORD');
		$DbTable = getConfigValue("aBPHTB",'GTW_TABLE_NAME');
		$DbUser = getConfigValue("aBPHTB",'BPHTBUSERNAME');
		$tw = getConfigValue("aBPHTB",'TENGGAT_WAKTU');
		$DbNameSW = getConfigValue("aBPHTB",'BPHTBDBNAMESW');
		
		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
		SCANPayment_ConnectToDB($XDBLink, $XDBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, $DbNameSW, true);

		if ($iErrCode != 0)
		{
		  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		  exit(1);
		}
		
		$query = "SELECT * FROM $DbName.ssb
					INNER JOIN
			        $DbNameSW.cppmod_ssb_doc
			   		ON
			        $DbNameSW.cppmod_ssb_doc.CPM_SSB_ID = $DbName.ssb.id_switching
			        INNER JOIN
			        $DbNameSW.cppmod_ssb_jenis_hak
			        ON
			        $DbNameSW.cppmod_ssb_doc.CPM_OP_JENIS_HAK = $DbNameSW.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK 
			        $where
			        $where2
			        ORDER BY $DbName.ssb.payment_paid DESC"; 
			        // ORDER BY GW_SSB_PEKANBARU.ssb.id_ssb DESC LIMIT ".$this->page.",".$this->perpage; 
		//echo $query;
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			 print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error() . "' );</script>";
			 return false; 
		}

		//$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		//$data = $d;
		//$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
		$ss = true;
		$tw = 0;

		$total_bayar = 0;
		$total_denda = 0;
		$total_seluruh = 0;
		$berkas = mysqli_num_rows($res);
		//for ($i=0;$i<count($data->data);$i++) {
		while($data=mysqli_fetch_object($res)){
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td>".($i+1)."</td> \n";
			$HTML .= "\t\t<td>".$data->id_ssb."</td> \n";
			$HTML .= "\t\t<td>".$data->wp_nama."</td> \n";
			$HTML .= "\t\t<td>".$data->wp_alamat."</td><td class=\"text\" align=\"center\">".$data->op_nomor."</td>\n";
			$HTML .= "\t\t<td  align=\"center\">".$data->CPM_OP_NMR_SERTIFIKAT."</td> \n";
			$HTML .= "\t\t<td align=\"right\">".number_format($data->bphtb_dibayar,0,".",",")."</td>\n";
			$HTML .= "\t\t<td align=\"right\">".number_format($data->CPM_DENDA,0,".",",")."</td>\n";
			$HTML .= "\t\t<td align=\"right\">".$data->bphtb_notaris."</td>\n";
			$HTML .= "\t\t<td align=\"right\">".$data->CPM_JENIS_HAK."</td>\n";
			$HTML .= "\t</tr></div>\n";
			$tw ++;
			$total_bayar = $total_bayar + $data->bphtb_dibayar;
			$total_denda = $total_denda + $data->CPM_DENDA;
			if($i == (mysqli_num_rows($res)-1)){
				$HTML .= "\t<div class=\"container\"><tr style='height:25px;' >\n";
				$HTML .= "\t\t<td></td> \n";
				$HTML .= "\t\t<td></td> \n";
				$HTML .= "\t\t<td></td> \n";
				$HTML .= "\t\t<td></td><td class=\"".$class."\" align=\"center\"></td>\n";
				$HTML .= "\t\t<td align=\"center\"></td> \n";
				$HTML .= "\t\t<td align=\"right\"></td>\n";
				$HTML .= "\t\t<td align=\"right\"></td>\n";
				$HTML .= "\t\t<td align=\"right\"></td>\n";
				$HTML .= "\t\t<td align=\"right\"></td>\n";
				$HTML .= "\t</tr></div>\n";
			}
		}
		$total_seluruh = $total_bayar + $total_denda;
		$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td>TOTAL</td><td class=\"".$class."\" align=\"center\"></td>\n";
		$HTML .= "\t\t<td></td> \n";
		$HTML .= "\t\t<td align=\"right\">".number_format($total_bayar,0,".",",")."</td>\n";
		$HTML .= "\t\t<td align=\"right\">".number_format($total_denda,0,".",",")."</td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";
		$HTML .= "\t<div class=\"container\"><tr style='height:25px;' >\n";
		$HTML .= "\t\t<td></td> \n";
		$HTML .= "\t\t<td></td> \n";
		$HTML .= "\t\t<td></td> \n";
		$HTML .= "\t\t<td></td><td align=\"center\"></td>\n";
		$HTML .= "\t\t<td align=\"center\"></td> \n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";		
		$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td ></td> \n";
		$HTML .= "\t\t<td ></td><td class=\"".$class." tdheader\" align=\"center\">TOTAL BERKAS</td>\n";
		$HTML .= "\t\t<td tdheader \" align=\"center\">".$berkas." BERKAS</td> \n";
		$HTML .= "\t\t<td tdheader \" align=\"right\">".number_format($total_seluruh,0,".",",")."</td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t\t<td align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";
		
		#ardi total row
		// $allRows= mysqli_query("SELECT * FROM $DbTable $where");
		// $this->totalRows = mysqli_num_rows($allRows);

		$dat = $HTML;
		$total = mysqli_num_rows($res);
		SCANPayment_CloseDB($LDBLink);
		
		if($total == 0){
			return false;
		}else{
			return true;
		}
	}

	function headerContentAll() {
		global $srcTxt,$srcTxt2;
		
		
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"1\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. STBP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nama WP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Alamat WP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> NOP </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> No. Sertifikat </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Pembayaran </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Denda </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Notaris </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Ket. </td> \n";
		$HTML .= "\t</tr>\n";

		if (getDocument($dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Pada Tanggal ".$srcTxt." Data Kosong !</td></tr> ";
		}
		$HTML .= "</table>\n";
		return $HTML;
	}
	
	function getContent() {
		$HTML = "";
		$HTML = headerContentAll();
		
		return $HTML;
	}

    function showData() {
		echo "<style>
		
			.text{
				  mso-number-format:\"\@\";/*force text*/
				}
		</style>";
		echo "<div id=\"notaris-main-content\">\n";
		echo "\t<div id=\"notaris-main-content-inner\">\n";
		echo getContent();
		echo "\t</div>\n";
		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=bphtb-daily-report.xls");
		
	}

function HeaderingExcel($filename){
	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=$filename");
	header("Expires:0");
	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");
}
?>
<div id="tabsContent">
	<?php echo showData(); ?>
</div>