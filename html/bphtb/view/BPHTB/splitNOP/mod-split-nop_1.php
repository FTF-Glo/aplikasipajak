<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'splitNOP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\">var axx='".base64_encode($_REQUEST['a'])."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/notaris/mod-notaris.js\" type=\"text/javascript\"></script>\n";

$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;
//print_r($_REQUEST);

$par1 = $params."&n=1&s=5";
$par2 = $params."&n=2&s=4";
$par3 = $params."&n=3&s=2";
$par4 = $params."&n=4&s=1";
$par5 = $params."&n=5&s=100";

if ($sel==1) $sts=5;
if ($sel==2) $sts=4;
if ($sel==3) $sts=2;
if ($sel==4) $sts=1;
if ($sel==5) $sts=100;

class modBPHTB_NOPSPLIT extends modBPHTBApprover {

	function getDocument($sts,&$dat) {
		global $data,$DBLink,$json;
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$srcKTP = @isset($_REQUEST['src-ktp'])?$_REQUEST['src-ktp']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		if ($srcKTP != "") $where .= " AND A.CPM_WP_NOKTP LIKE '%".$srcKTP."%'";
		$query ="";
		//print_r("<br>ss".$this->userGroup);
		if ($this->userGroup == 1) {
			if ($sts==2) {
				$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR B.CPM_TRAN_STATUS=3) AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
				$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR
				 B.CPM_TRAN_STATUS=3) AND B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."'";
			} else {
				$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
						$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
				$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
						$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."'";
			}
		}
		if ($this->userGroup == 2) {
			$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
					
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0";
		}
		if ($this->userGroup==3) {
			$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
			AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
			
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0";
		}
		
		#echo $query;		
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f-split-NOP-Edit&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			if (($sts==2) || ($sts==5)) {
				$par1 = $params."&f=funcDetailRejectSplitNOP&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			}
			if ($this->userGroup == 2) $par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			//echo  $par1;
			if ($this->userGroup==1) if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
			if ($this->userGroup==3) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == "")) $class = "tdbodyNew";
		
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			$HTML .= "\t<div class=\"container\"><tr>\n";
			if (($sts==4) || ($sts==5)|| ($sts==1)) $HTML .= "\t\t<td width=\"20\" class=\"".$class."\" align=\"center\">
			<input id=\"check-all-".$i."\" name=\"check-all\" type=\"checkbox\" value=\"".$data->data[$i]->CPM_SSB_ID."\" /></td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NOKTP."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT))."</td><td class=\"".$class."\" align=\"right\">".
					number_format($ccc,0,".",",")."</td>\n";
					
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			
			$statusSPPT="";
			if ($sts ==1) $statusSPPT = "Sementara";
			if ($sts ==2) $statusSPPT = "Tertunda";
			else if ($sts ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			$config = $this->getConfigure ($_REQUEST['a']);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
			
			if ($sts ==5) $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$PAID."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr></div>\n";
		}
		$dat = $HTML;
		return true;
	}
	
	function getAllDocument(&$dat) {
		global $data,$DBLink,$json ;
		
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$srcKTP = @isset($_REQUEST['src-ktp'])?$_REQUEST['src-ktp']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		if ($srcKTP != "") $where .= " AND A.CPM_WP_NOKTP LIKE '%".$srcKTP."%'";
		if ($this->userGroup == 1) {
			$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
		}
		//echo $query;
		
		if ($this->userGroup == 2)  {
			$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1  $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
		}
		
		if ($this->userGroup == 3) {
			$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 AND B.CPM_TRAN_STATUS<>2  $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
		}
		//echo $query;
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		if ($this->userGroup == 1) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
			 AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."'";
		}
		
		if ($this->userGroup == 2) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
			 AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 ";
		}
		
		if ($this->userGroup == 3) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
			 AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 AND B.CPM_TRAN_STATUS<>2 ";
		}
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$statusdoc = "";
			if ($data->data[$i]->CPM_TRAN_STATUS == 1) $statusdoc = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS == 2) $statusdoc = "Tertunda";
			if ($data->data[$i]->CPM_TRAN_STATUS == 3) $statusdoc = "Proses";
			if ($data->data[$i]->CPM_TRAN_STATUS == 4) $statusdoc = "Ditolak";
			if ($data->data[$i]->CPM_TRAN_STATUS == 5) $statusdoc = "Disetujui";
			if ($this->userGroup == 1) $par1 = $params."&f=f-split-NOP-Edit&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 2) $par1 = $params."&f=f-split-NOP-Edit&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($this->userGroup==1) if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
			if ($this->userGroup==3) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == "")) $class = "tdbodyNew";
			
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			$statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			$config = $this->getConfigure ($_REQUEST['a']);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
		
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			$statusSPPT="";
			if ($data->data[$i]->CPM_TRAN_STATUS==1) $statusSPPT = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS ==2) $statusSPPT = "Tertunda";
			else if ($data->data[$i]->CPM_TRAN_STATUS ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NOKTP."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT))."</td><td class=\"".$class."\" align=\"center\">".
					number_format(intval($ccc),0,".",",")."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusdoc."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr></div>\n";
		}
		
		$dat = $HTML;
		return true;
	}
	
	function getDocumentInfoText($sts,&$dat) {
		global $data,$DBLink,$json;
		$srcTxt =@isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$srcKTP = @isset($_REQUEST['src-ktp'])?$_REQUEST['src-ktp']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		if ($srcKTP != "") $where .= " AND A.CPM_WP_NOKTP LIKE '%".$srcKTP."%'";
		
		if ($this->userGroup == 1) {
				$where = " AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."'";
		}
		

		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
	
		
		
		$res = mysqli_query($DBLink, $query);
		
		//echo $query;
		
		if ( $res === false ){
			return false; 
		}
		
		if ($this->userGroup == 1) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$data->uname."'";
		}
		if (($this->userGroup == 2) || ($this->userGroup == 3)) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0";
		}
		
		//print_r($qry);	
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f-split-NOP-Edit&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			if ($this->userGroup == 2) $par1 = $params."&f=f-split-NOP-Edit&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($sts==4) $par1 = $params."&f=funcDetailRejectSplitNOP&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
			if ($this->userGroup==3) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == "")) $class = "tdbodyNew";
			$HTML .= "\t<tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NOKTP."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT))."</td>\n";
						
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format(intval($ccc),0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\"><span title=\"".$data->data[$i]->CPM_TRAN_INFO."\" class=\"span-title\">".
						$this->splitWord($data->data[$i]->CPM_TRAN_INFO,5)."</span></a></td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr>\n";
		}
		$dat = $HTML;
		return true;
	}
	
}

$modNotaris = new  modBPHTB_NOPSPLIT(1,$data->uname);
$modNotaris->addMenu("Disetujui","app-menu",base64_encode($par1));
$modNotaris->addMenu("Ditolak","rej-menu",base64_encode($par2));
$modNotaris->addMenu("Tertunda","dil-menu",base64_encode($par3));
$modNotaris->addMenu("Sementara","tmp-menu",base64_encode($par4));
$modNotaris->addMenu("Semua Data","all-menu",base64_encode($par5));
//$modNotaris->addMenu("Form SSB(PDF)","pdf-menu","","printNFPDF()");
$modNotaris->setSelectedMenu($sel);
$modNotaris->setStatus($sts);

$pages =  $modNotaris->getConfigValue("aBPHTB","ITEM_PER_PAGE");
$modNotaris->setDataPerPage($pages);
$modNotaris->setDefaultPage($page);

$del = @isset($_REQUEST['del'])?$_REQUEST['del']:"";

if ($del) {
	$json = new Services_JSON();
	$del = $json->decode($del);
	$c = count($del);
	
	for ($i=0;$i<$c;$i++) {
		$qry = "DELETE FROM cppmod_ssb_doc where CPM_SSB_ID = '{$del[$i]->id}'";
		
		$res = mysqli_query($DBLink, $qry);
		if ( $res === false ){
			print_r(mysqli_error($DBLink));
		}
	}
}
echo $modNotaris->showData();

?>

