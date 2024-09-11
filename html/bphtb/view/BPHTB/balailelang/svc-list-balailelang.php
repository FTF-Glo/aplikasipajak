<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'balailelang', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

class BPHTBService extends modBPHTBApprover {
	function __construct ($userGroup,$user) {
		$this->userGroup = $userGroup;
		$this->user = $user;
		
	}
	
	function getTotalRows($query) {
		global $DBLink;
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			echo $query ."<br>";
			echo mysqli_error($DBLink);
		}
		
		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
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
	
	function getSPPTInfo($noktp,$nop,&$paid) {
		global $a;
		
		$iErrCode=0;
		$a = $a;
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
		
		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '".$noktp."' ORDER BY saved_date DESC limit 1  ";
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
		
		$query = "SELECT PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE op_nomor = '".$nop."'";
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
	
	
	function getDocument($sts,&$dat) {
		global $DBLink,$json,$a,$m,$find,$page;
		$srcTxt = $find;
		$where = "";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		$query ="";
		//print_r("<br>ss".$this->userGroup);
		if ($this->userGroup == 1) {
			if ($sts==2) {
				$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR B.CPM_TRAN_STATUS=3) AND 
				B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$hal.",".$this->perpage;
				$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND (B.CPM_TRAN_STATUS=2 OR
				 B.CPM_TRAN_STATUS=3) AND B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."'";
			} else {
				$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
						$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$hal.",".$this->perpage;
				$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
						$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."'";
			}
		}
		
		//echo $query;		
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$a."&m=".$m; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f474-bl-edit&idssb=".$data->data[$i]->CPM_SSB_ID;
			if (($sts==2) || ($sts==5)) {
				$par1 = $params."&f=f475-bl-disp&idssb=".$data->data[$i]->CPM_SSB_ID;
			}
			if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
				if ($data->data[$i]->CPM_PAYMENT_TIPE == "2") $par1 = $params."&f=funcKurangBayar&idssb=".$data->data[$i]->CPM_SSB_ID."&idtid=".$data->data[$i]->CPM_TRAN_ID;
			}
			
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			//echo  $par1;
			if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			if (($data->data[$i]->CPM_PAYMENT_TIPE == '2') && (!is_null($data->data[$i]->CPM_OP_BPHTB_TU))) {
				$ccc  = $data->data[$i]->CPM_OP_BPHTB_TU;
			}
			
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
			else if ($sts ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			$config = $this->getConfigure ($a);
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
		global $data,$DBLink,$json,$a,$m,$find,$page ;
		
		$srcTxt = $find;
		$where = "";
		$hal = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";

		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
			B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."' $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$hal.",".$this->perpage;
		
		$res = mysqli_query($DBLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
		 AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."'";
			
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$a."&m=".$m; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$statusdoc = "";
			if ($data->data[$i]->CPM_TRAN_STATUS == 1) $statusdoc = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS == 2) $statusdoc = "Tertunda";
			if ($data->data[$i]->CPM_TRAN_STATUS == 3) $statusdoc = "Proses";
			if ($data->data[$i]->CPM_TRAN_STATUS == 4) $statusdoc = "Ditolak";
			if ($data->data[$i]->CPM_TRAN_STATUS == 5) $statusdoc = "Disetujui";
			if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
				if ($data->data[$i]->CPM_PAYMENT_TIPE == "2") $par1 = $params."&f=funcKurangBayar&idssb=".$data->data[$i]->CPM_SSB_ID."&idtid=".$data->data[$i]->CPM_TRAN_ID;
			}
			$par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID;
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			$statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			$config = $this->getConfigure ($a);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
		
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			if (($data->data[$i]->CPM_PAYMENT_TIPE == '2') && (!is_null($data->data[$i]->CPM_OP_BPHTB_TU))) {
				$ccc  = $data->data[$i]->CPM_OP_BPHTB_TU;
			}
			$statusSPPT="";
			if ($data->data[$i]->CPM_TRAN_STATUS==1) $statusSPPT = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS ==2) $statusSPPT = "Tertunda";
			else if ($data->data[$i]->CPM_TRAN_STATUS ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_SSB_ID,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT))."</td><td class=\"".$class."\" align=\"center\">".
					number_format(intval($ccc),0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
			$HTML .= "\t</tr></div>\n";
		}
		
		$dat = $HTML;
		return true;
	}
	
	public function headerContentReject($sts) {
		global $find;
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" value=\"{$find}\"size=\"60\"/> 
		<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n</form>\n";
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
	
	public function headerContentAll($sts) {
		global $find;
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
		onclick=\"printDataToPDF();\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
				Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"60\" value=\"{$find}\"/> 
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ($sts);\"/>\n</form>\n";
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
		global $find;
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved-{$sts}\" size=\"60\" value=\"{$find}\"/> 
				<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ($sts);\" />\n</form>\n";
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
				$HTML .= $this->headerContentAll(100);
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
	
	public function headerContentApprove($sts,$draf=false) {
		global $find;
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		if ($draf) {
			$HTML .= "
			<input type=\"button\" value=\"Hapus\" id=\"btn-print\" name=\"btn-delete\" 
				onclick=\"deleteSelected();\"/>&nbsp;&nbsp;<input type=\"button\" value=\"Cetak Sementara\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(1);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"60\" value=\"{$find}\"/> 
						<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\"  onclick=\"setTabs ({$sts});\" />\n
						<input type=\"hidden\" id=\"hidden-delete\" name=\"hidden-delete\" />\n</form>\n";
		} else   {
			$HTML .= "<input type=\"button\" value=\"Cetak Salinan\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(0);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP <input type=\"text\" id=\"src-approved-{$sts}\" name=\"src-approved\" size=\"60\" value=\"{$find}\"/> 
						<input type=\"button\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" onclick=\"setTabs ({$sts});\" />\n</form>\n";
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
	
	function getDocumentInfoText($sts,&$dat) {
		global $data, $DBLink, $json, $a, $m,$page;
		$srcTxt =@isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%')";
		
		if ($this->userGroup == 1) {
				$where = " AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."'";
		}
		
		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".($page-1).",".$this->perpage;
		$res = mysqli_query($DBLink, $query);
		
		if ( $res === false ){
			return false; 
		}
		
		if ($this->userGroup == 1) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_OPR_NOTARIS ='".$this->user."'";
		}
			
		$this->totalRows = $this->getTotalRows($qry);
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$a."&m=".$m; 
	
		for ($i=0;$i<count($data->data);$i++) {
			$par1 = $params."&f=f337-mod-display-notaris&idssb=".$data->data[$i]->CPM_SSB_ID."&sts=".$sts;
			if ($data->data[$i]->CPM_TRAN_STATUS == 4) {
				if ($data->data[$i]->CPM_PAYMENT_TIPE == "2") $par1 = $params."&f=funcKurangBayar&idssb=".$data->data[$i]->CPM_SSB_ID."&idtid=".$data->data[$i]->CPM_TRAN_ID;
			}
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($data->data[$i]->CPM_TRAN_READ == "") $class = "tdbodyNew";
			$HTML .= "\t<tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".trim(strip_tags($data->data[$i]->CPM_WP_ALAMAT))."</td>\n";
						
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,
			$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			if (($data->data[$i]->CPM_PAYMENT_TIPE == '2') && (!is_null($data->data[$i]->CPM_OP_BPHTB_TU))) {
				$ccc  = $data->data[$i]->CPM_OP_BPHTB_TU;
			}
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format(intval($ccc),0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1).
			"\"><span title=\"".$data->data[$i]->CPM_TRAN_INFO."\" class=\"span-title\">".
						$this->splitWord($data->data[$i]->CPM_TRAN_INFO,5)."</span></a></td>\n";
			$HTML .= "\t</tr>\n";
		}
		$dat = $HTML;
		return true;
	}
	
	public function displayDataNotaris () {
		echo "<div id=\"notaris-main-contents\">\n";
		echo "\t<div id=\"notaris-main-content-inners\">\n";
		echo $this->getContent();
		echo "\t</div>\n";
		echo "\t<div id=\"notaris-main-content-footers\" align=\"right\">  \n";
		echo $this->paging();
		echo "</div>\n";
	}
	
	function paging() {
		global $a,$m,$n,$s,$page,$np;
		//$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
		//$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
		//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
		
		$params = "a=".$a."&m=".$m;
		$sel = $n;
		$sts = $s;
		
		$html = "<div>";
		$row = (($page-1) > 0 ? ($page-1) : 0) * $this->perpage;
		$rowlast = (($page) * $this->perpage) < $this->totalRows ? ($page) * $this->perpage : $this->totalRows;
		//$rowlast = $this->totalRows < $rowlast ? $this->totalRows : $rowlast;
		$html .= ($row+1)." - ".($rowlast). " dari ".$this->totalRows;
		
		$parl = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage-1);
		$paramsl = base64_encode($parl);
		
		$parr = $params."&n=".$sel."&s=".$sts."&p=".($this->defaultPage+1);
		$paramsr = base64_encode($parr);

		//if ($np) $page++;
		//else $page--;
		if ($page != 1) {
			//$page--;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','0')\"><span id=\"navigator-left\"></span></a>";
		}
		if ($rowlast < $this->totalRows ) {
			//$page++;
			$html .= "&nbsp;<a onclick=\"setPage('".$s."','1')\"><span id=\"navigator-right\"></span></a>";
		}
		$html .= "</div>";
		return $html;
	}
}

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$find = @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$page = @isset($_REQUEST['page']) ? $_REQUEST['page'] :1;
$np = @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;

// echo "$q:".$q."<br>";

$q = base64_decode($q);
$q = $json->decode($q);
//echo "<pre>"; print_r($q); echo "</pre>";
//print_r($_REQUEST);
$a = $q->a;
$m = $q->m;
$n = $q->n;
$s = $q->s;
$uname = $q->u;
//print_r($q);
$modNotaris = new  BPHTBService(1,$uname);

$modNotaris->setStatus($s);
$modNotaris->setDataPerPage(50);
$modNotaris->setDefaultPage(1); 

$modNotaris->displayDataNotaris();
?>
