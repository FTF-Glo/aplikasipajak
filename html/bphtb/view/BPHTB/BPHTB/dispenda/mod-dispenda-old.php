<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\">var dispenda=1;</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/dispenda/mod-dispenda.js\" type=\"text/javascript\"></script>\n";

function displayMenu ($selected) {
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	$par1 = $params."&n=1";
	$par2 = $params."&n=2";
	$par3 = $params."&n=3";
	$par4 = $params."&n=4";
	
	$selected1 = "";
	$selected2 = "";
	$selected3 = "";
	$selected4 = "";
	
	switch ($selected){
		case 2:
			$selected2 = "id=\"selected\"";
			break;
		case 3:
			$selected3 = "id=\"selected\"";
			break;
		case 4:
			$selected4 = "id=\"selected\"";
			break;
		default:
			$selected1 = "id=\"selected\"";
			break;
	}
	echo "<div id=\"notaris-main-menu\">\n";
	echo "\t<ul>\n";
	echo "\t\t<a href=\"main.php?param=".base64_encode($par1)."\"><li ".$selected1." ><span id=\"dil-menu\">Tunda</span></li></a>\n";
	echo "\t\t<a href=\"main.php?param=".base64_encode($par2)."\"><li ".$selected2." ><span id=\"pro-menu\">Proses</span></li></a>\n";
	echo "\t\t<a href=\"main.php?param=".base64_encode($par3)."\"><li ".$selected3." ><span id=\"app-menu\">Disetujui</span></li></a>\n";
	echo "\t\t<a href=\"main.php?param=".base64_encode($par4)."\"><li ".$selected4." ><span id=\"rej-menu\">Ditolak</span></li></a>\n";
	echo "\t</ul>\n";
	echo "</div>\n";
}

function getDocument($sts,&$dat) {
	global $data,$appDbLink;
	$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
	$where = "";
	if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
	$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
			AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT 0,200";

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
		$par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
		$class = $i%2==0 ? "tdbody1":"tdbody2";
		if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_ALAMAT."</td><td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
		$HTML .= "\t</tr>\n";
	}
	$dat = $HTML;
	return true;
}

function splitWord ($txt,$tot) {
	if($txt!="") {
		$w = split(" ",$txt);
		$w1 = array();
		for ($i=0;$i<$tot;$i++) {
			$w1[$i] = $w[$i];
		}
		$wr = implode(" ",$w1);
		if (count($w) > $tot) $wr .= "...";
		return $wr;
	}
}

function getDocumentInfoText($sts,&$dat) {
	global $data,$appDbLink;
	$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
	$where = "";
	if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
	$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
			AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT 0,200";

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
		$par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
		$class = $i%2==0 ? "tdbody1":"tdbody2";
		if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_WP_ALAMAT."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\"><span title=\"".str_replace("<br />"," \n",$data->data[$i]->CPM_TRAN_INFO)."\" class=\"span-title\">".
					splitWord($data->data[$i]->CPM_TRAN_INFO,5)."</span></a></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
		$HTML .= "\t</tr>\n";
	}
	$dat = $HTML;
	return true;
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

function contentDispendaDelay() {
	$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
	$HTML .= "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"> Versi </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	$json = new Services_JSON();
	if (getDocument(2,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "Data Kosong !";
	}
	$HTML .= "</table>\n";
	return $HTML;
}
function contentDispendaProcess() {
	$HTML = "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" size=\"60\"/> <input type=\"button\" value=\"Cari\" id=\"btn-src\"/>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\">Alasan Penolakan</td><td class=\"tdheader\"> Versi </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	$json = new Services_JSON();
	if (getDocumentInfoText(3,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "Data Kosong !";
	}
	$HTML .= "</table>\n";
	return $HTML;
}

function contentDispendaReject() {
	$HTML = "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" size=\"60\"/> <input type=\"button\" value=\"Cari\" id=\"btn-src\"/>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\">Alasan Penolakan</td><td class=\"tdheader\"> Versi </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	$json = new Services_JSON();
	if (getDocumentInfoText(4,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "Data Kosong !";
	}
	$HTML .= "</table>\n";
	return $HTML;
}

function contentNotarisDelay() {
	$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
	$HTML .= "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"> Versi </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	$json = new Services_JSON();
	if (getDocument(1,$dt)) {
		$HTML .= $dt;
	} else {
		$HTML .= "Data Kosong !";
	}
	$HTML .= "</table>\n";
	return $HTML;
}

function contentDispendaApprove() {
	$HTML = "Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" size=\"60\"/> <input type=\"button\" value=\"Cari\" id=\"btn-src\"/>\n";
	$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
	$HTML .= "\t<tr>\n";
	$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
	$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\">Alasan Penolakan</td><td class=\"tdheader\"> Versi </td>\n";
	$HTML .= "\t\t<td class=\"tdheader\" > Tanggal</td>\n";
	$HTML .= "\t</tr>\n";
	$json = new Services_JSON();
	if (getDocumentInfoText(5,$dt)) {
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
	
	if ($selected == 1) {
		echo contentDispendaDelay();
	}
	if ($selected == 2) {
		echo contentDispendaProcess();
	}
	if ($selected == 3) {
		echo contentDispendaApprove();
	}
	if ($selected == 4) {
		echo contentDispendaReject();
	}
	echo "\t</div>\n";
	echo "\t<div id=\"notaris-main-content-footer\">\n";
	/*echo "\t<div id=\"notaris-main-content-footer\"> Data terakhir yang ditampilkan sebanyak : \n";
	echo "\t\t<select id=\"perItems\">\n";
	echo "\t\t\t<option value=\"10\">10</option>\n";
	echo "\t\t\t<option value=\"25\">25</option>\n";
	echo "\t\t\t<option value=\"50\">50</option>\n";
	echo "\t\t\t<option value=\"75\">75</option>\n";
	echo "\t\t\t<option value=\"100\">100</option>\n";
	echo "\t\t\t<option value=\"150\">150</option>\n";
	echo "\t\t</select>\n";*/
	echo "\t</div>\n";
	echo "</div>\n";
}
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;

displayMenu($sel);

displayContent($sel);
?>
