<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/bpn/svc-bpn-lookup.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

$json = new Services_JSON();
	
class modBPHTBApprover  {
	
	public $userGroup;
	private $user;
	private $arrMenu = array();
	private $selMenu = 0;
	public $status = 0;
	public $perpage = 20;
	public $page = 1;
	public $defaultPage = 1;
	public $totalRows = 0;
	
	function __construct ($userGroup,$user) {
		$this->userGroup = $userGroup;
		$this->user = $user;
		
	}
	
	function addMenu ($menuName,$id,$linkMenu,$onclick='') {
		global $json;
		$obj = array('name'=>$menuName,'id'=>$id,'link'=>$linkMenu,'onclick'=>$onclick);
		$jObj = $json->encode($obj); 
		array_push($this->arrMenu,$jObj);
	}
	
	function setSelectedMenu($sel) {
		$this->selMenu = $sel;
	}
	
	function setStatus($stat) {
		$this->status = $stat;
	}
	
	function setDefaultPage($page) {
		$this->page = ($page-1) * $this->perpage;
		$this->defaultPage = $page;
	}
	
	function setDataPerPage($perpage) {
		$this->perpage = $perpage;
	}
	
	function getConfigValue ($id,$key) {
		global $appDbLink;	
		$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
		$res = mysqli_query($appDbLink, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($appDbLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	}
	
	function getConfigure ($appID) {
	  $config = array();
	  $a=$appID;
	  $config['TENGGAT_WAKTU'] = $this->getConfigValue($a,'TENGGAT_WAKTU');
	  $config['NPOPTKP_STANDAR'] =$this-> getConfigValue($a,'NPOPTKP_STANDAR');
	  $config['NPOPTKP_WARIS'] = $this->getConfigValue($a,'NPOPTKP_WARIS');
	  $config['TARIF_BPHTB'] = $this->getConfigValue($a,'TARIF_BPHTB');
	  $config['PRINT_SSPD_BPHTB'] = $this->getConfigValue($a,'PRINT_SSPD_BPHTB');
	  $config['NAMA_DINAS'] = $this->getConfigValue($a,'NAMA_DINAS');
	  $config['ALAMAT'] = $this->getConfigValue($a,'ALAMAT');
	  $config['NAMA_DAERAH'] = $this->getConfigValue($a,'NAMA_DAERAH');
	  $config['KODE_POS'] = $this->getConfigValue($a,'KODE_POS');
	  $config['NO_TELEPON'] = $this->getConfigValue($a,'NO_TELEPON');
	  $config['NO_FAX'] = $this->getConfigValue($a,'NO_FAX');
	  $config['EMAIL'] = $this->getConfigValue($a,'EMAIL');
	  $config['WEBSITE'] = $this->getConfigValue($a,'WEBSITE');
	  $config['KODE_DAERAH'] = $this->getConfigValue($a,'KODE_DAERAH');
	  $config['KEPALA_DINAS'] = $this->getConfigValue($a,'KEPALA_DINAS');
	  $config['NAMA_JABATAN'] = $this->getConfigValue($a,'NAMA_JABATAN');
	  $config['NIP'] = $this->getConfigValue($a,'NIP');
	  $config['NAMA_PJB_PENGESAH'] = $this->getConfigValue($a,'NAMA_PJB_PENGESAH');
	  $config['JABATAN_PJB_PENGESAH'] = $this->getConfigValue($a,'JABATAN_PJB_PENGESAH');
	  $config['NIP_PJB_PENGESAH'] = $this->getConfigValue($a,'NIP_PJB_PENGESAH');
	  return $config;
	}
	
	function getSPPTInfo($nop,&$paid) {
		$whereClause = "NOP = '".$nop."'";
		$iErrCode=0;
		$a = $_REQUEST['a'];
		//LOOKUP_BPHTB ($whereClause, $DbName, $DbHost, $DbUser, $DbPwd, $DbTable);
		$DbName = $this->getConfigValue($a,'BPHTBDBNAME');
		$DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue($a,'BPHTBTABLE');
		$DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
		
		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
		if ($iErrCode != 0)
		{
		  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		  exit(1);
		}
		
		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE OP_NOMOR = '".$nop."'";
	    $paid = "";
		$res = mysqli_query($LDBLink, $query);
		if ( $res === false ){
			print_r(mysqli_error($LDBLink));
			return "Tidak Ditemukan"; 
		}
		$json = new Services_JSON();
		$data =  $json->decode($this->mysql2json($res,"data"));	
		for ($i=0;$i<count($data->data);$i++) {
			$paid = $data->data[$i]->PAYMENT_PAID;
			return $data->data[$i]->PAYMENT_FLAG?"Sudah Dibayar":"Siap Dibayar";
		}
		SCANPayment_CloseDB($LDBLink);
		return "Tidak Ditemukan";
	}
	
	function displayMenu () {
		global $json;
		$n = count ($this->arrMenu);
		$html = "<div id=\"notaris-main-menu\">\n";
		$html .= "\t<ul>\n";
		for ($i=1;$i<$n+1;$i++) {
			$selected = "";
			$ex = $json->decode($this->arrMenu[$i-1]);
			if ($this->selMenu == $i)  $selected = "id=\"selected\"";	
			$html .= "\t\t<a href=\"main.php?param=".$ex->link."\" onclick=\"".$ex->onclick."\"><li ".$selected."><span id=\"".
			$ex->id."\">".$ex->name."</span></li></a>\n";
		}
		$html .= "\t</ul>\n";
		$html .= "</div>\n";
		return $html;
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
	
	function getTotalRows($query) {
		global $appDbLink;
		$res = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			echo $query ."<br>";
			echo mysqli_error($appDbLink);
		}
		
		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
	}
	
	function getMinCreated($noktp,$nop) {
		global $appDbLink;
		$a = $_REQUEST['a'];
		$day = $this->getConfigValue($a,"BATAS_HARI_NPOPTKP");
		$qry = "select min(CPM_SSB_CREATED) as mx from cppmod_ssb_doc  where 
		CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) ";
		
		$res = mysqli_query($appDbLink, $qry);
		if ( $res === false ){
			print_r(mysqli_error($appDbLink));
			return false;
		}
		
		if (mysqli_num_rows ($res)) {
			$num_rows = mysqli_num_rows($res);
			while($row = mysqli_fetch_assoc($res)){
				if($row["mx"]) {
					
					return $row["mx"];
				}
			}
			
		}
	}
	
	function getNOKTP ($noktp,$nop,$tgl) {
		global $appDbLink;
		$a = $_REQUEST['a'];
		$day = $this->getConfigValue($a,"BATAS_HARI_NPOPTKP");
		$N1= $this->getConfigValue($a,'NPOPTKP_STANDAR');
		$qry = "select sum(A.CPM_SSB_AKUMULASI) as mx from cppmod_ssb_doc A, cppmod_ssb_tranmain B where A.CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) AND A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_STATUS <> 1";
		
		$res = mysqli_query($appDbLink, $qry);
		if ( $res === false ){
			print_r(mysqli_error($appDbLink));
			return false;
		}
		
		if (mysqli_num_rows ($res)) {
			$num_rows = mysqli_num_rows($res);
			while($row = mysqli_fetch_assoc($res)){
				if(($row["mx"]) && ($row["mx"] >= $N1)) {
					print_r($row["mx"]);
					return true;
				}
			}
		}
		return false;
	}	
	function getBPHTBPayment($lb,$nb,$lt,$nt,$h,$p,$jh,$NPOPTKP) {
		$a = $_REQUEST['a'];
		/*$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');
		
		$typeR = $jh;
		
		if (($typeR==4) || ($typeR==6)){
			$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
		} else {
			
		}*/
		
		/*if($this->getNOKTP($noktp,$nop,$tgl)) {	
			$NPOPTKP = 0;
		}*/
		
		$a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
		$b = strval($h);
		$npop = 0;
		if ($b < $a) $npop = $a; else $npop = $b;
		
		$jmlByr = ($npop-strval($NPOPTKP))*0.05;
		$tp = strval($p);
		if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
		
		if ($jmlByr < 0) $jmlByr = 0;
		return $jmlByr;
	}
	
	function getDocument($sts,&$dat) {
		global $data,$appDbLink,$json;
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		$query ="";
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
		
		//echo $query;		
		$res = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f314-det-notaris&idssb=".$data->data[$i]->CPM_SSB_ID;
			if (($sts==2) || ($sts==5)) {
				$par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID;
			}
			if ($this->userGroup == 2) $par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID;
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
			else if ($sts ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			$config = $this->getConfigure ($_REQUEST['a']);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
			
			if ($sts ==5) $HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$PAID."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr></div>\n";
		}
		$dat = $HTML;
		return true;
	}
	
	function getAllDocument(&$dat) {
		global $data,$appDbLink,$json ;
		
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
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
		$res = mysqli_query($appDbLink, $query);
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
			if ($this->userGroup == 1) $par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 2) $par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($this->userGroup==1) if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
			if ($this->userGroup==3) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == "")) $class = "tdbodyNew";
			
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			$statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$PAID);
			$config = $this->getConfigure ($_REQUEST['a']);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
		
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			$statusSPPT="";
			if ($data->data[$i]->CPM_TRAN_STATUS==1) $statusSPPT = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS ==2) $statusSPPT = "Tertunda";
			else if ($data->data[$i]->CPM_TRAN_STATUS ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
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
	
	public function splitWord ($txt,$tot) {
		$w = split(" ",$txt);
		$w1 = array();
		$wr=$txt;
		if (count($w) > $tot) {
			for ($i=0;$i<$tot;$i++) {
				$w1[$i] = $w[$i];
			}
			$wr = implode(" ",$w1);
			if (count($w) > $tot) $wr .= "...";
		}
		return $wr;
	}
	
	function getDocumentInfoText($sts,&$dat) {
		global $data,$appDbLink,$json;
		$srcTxt =@isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		
		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
	
		$res = mysqli_query($appDbLink, $query);
		
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
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			if ($this->userGroup == 2) $par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
			if ($this->userGroup == 3) $par1 = $params."&f=f340-func--dispenda-pjb&idssb=".$data->data[$i]->CPM_SSB_ID;
			//$par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
			if ($this->userGroup==3) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_2 == "")) $class = "tdbodyNew";
			$HTML .= "\t<tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
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
	
	public function headerContentApprove($sts,$draf=false) {
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		if (($draf) && ($this->userGroup == 1)) {
			$HTML .= "<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
						<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		} else if($this->userGroup == 1)  {
			$HTML .= "<input type=\"button\" value=\"Cetak Salinan\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(0);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
						<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		}
		else {
			$HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(3);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
						<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		}
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div class=\"container\">
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		if ($sts ==5) $HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Tanggal</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"130\">Status</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
		$HTML .= "\t</tr>\n";

		if ($this->getDocument($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
		}
		$HTML .= "</table>\n";
		return $HTML;
	}
	
	public function headerContentReject($sts) {
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" width=\"130\" > Tanggal</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
		$HTML .= "\t</tr>\n";
		if ($this->getDocumentInfoText($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr>";
		}
		$HTML .= "</table>\n";
		return $HTML;
	}
	
	public function headerContentAll() {
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
		onclick=\"printDataToPDF();\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
				Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Status Pembayaran</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
		$HTML .= "\t</tr>\n";

		if ($this->getAllDocument($dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "Data Kosong !";
		}
		$HTML .= "</table>\n";
		return $HTML;
	}
	
	public function headerContent($sts) {
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		if ($sts ==5) $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Versi</td>\n";
		$HTML .= "\t</tr>\n";

		if ($this->getDocument($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
		}
		
		$HTML .= "</table>\n";
		return $HTML;
	}
	 
	function getContent() {
		$HTML = "";
		switch ($this->status) {
			case 100 :
				$HTML .= $this->headerContentAll();
				break;
			case 5 :
				$HTML .= $this->headerContentApprove(5);
				break;
			case 4 :
				$HTML .= $this->headerContentReject(4);
				break;
			case 3 :
				$HTML .= $this->headerContentReject(3);
				break;
			case 2 :
				$HTML .= $this->headerContent(2);
				break;
			case 1 :
				$HTML .= $this->headerContentApprove(1,true);
				break;	
		}
		return $HTML;
	}
	
	function paging() {
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
		$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
		$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
		
		$html = "<div>";
		$row = $this->page ? $this->page +1 : 1;
		$rowlast = $row+$this->perpage-1;
		$rowlast = $this->totalRows < $rowlast ? $this->totalRows : $rowlast;
		$html .= $row." - ".$rowlast. " dari ".$this->totalRows;
		
		$parl = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage-1);
		$paramsl = base64_encode($parl);
		
		$parr = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage+1);
		$paramsr = base64_encode($parr);
		//echo $this->defaultPage;
		if ($this->defaultPage != 1) $html .= "&nbsp;<a href=\"main.php?param=".$paramsl."\"><span id=\"navigator-left\"></span></a>";
		if ($rowlast < $this->totalRows ) $html .= "&nbsp;<a href=\"main.php?param=".$paramsr."\"><span id=\"navigator-right\"></span></a>";
		$html .= "</div>";
		return $html;
	}	
	
	function showData() {
		echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
		echo $this->displayMenu();
		echo "<div id=\"notaris-main-content\">\n";
		echo "\t<div id=\"notaris-main-content-inner\">\n";
		echo $this->getContent();
		echo "\t</div>\n";
		echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
		echo $this->paging();
		echo "</div>\n";
	}
}
?>
