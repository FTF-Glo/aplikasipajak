<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'dispenda', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/dispenda/mod-dispenda.css?0001\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var dispenda=1;</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/dispenda/mod-dispenda.js\" type=\"text/javascript\"></script>\n";

$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;
//print_r($_REQUEST);

class stafDispenda extends modBPHTBApprover {
	
	function getAllDocument(&$dat) {
		global $data,$appDbLink,$json ;
		
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%' OR A.CPM_WP_NOKTP = '".$srcTxt."')";
		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND 
		B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1  $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
		
		$res = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
		AND  B.CPM_TRAN_FLAG=0 AND B.CPM_TRAN_STATUS<>1 $where ";
		
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
			$par1 = $params."&f=f338-mod-display-dispenda&idssb=".$data->data[$i]->CPM_SSB_ID;
			$class = $i%2==0 ? "tdbody1":"tdbody2";

			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
						
			$dateDiff = time()-strtotime($data->data[$i]->CPM_TRAN_DATE);
			$fullDays = floor($dateDiff/(60*60*24));
			$fullHours = floor(($dateDiff-($fullDays*60*60*24))/(60*60));
			$fullMinutes = floor(($dateDiff-($fullDays*60*60*24)-($fullHours*60*60))/60);
			$statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			$config = $this->getConfigure ($_REQUEST['a']);
			//print_r($config);
			if (($fullDays>intval($config['TENGGAT_WAKTU'])) && ($this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$data->data[$i]->CPM_OP_NOMOR,$PAID)!="Sudah Dibayar")) $statusSPPT = "Kadaluarsa";
		
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			if (($data->data[$i]->CPM_PAYMENT_TIPE == '2') && (!is_null($data->data[$i]->CPM_OP_BPHTB_TU))) {
				$ccc  = $data->data[$i]->CPM_OP_BPHTB_TU;
			}
			
			$statusSPPT="";
			if ($data->data[$i]->CPM_TRAN_STATUS==1) $statusSPPT = "Sementara";
			if ($data->data[$i]->CPM_TRAN_STATUS ==2) $statusSPPT = "Tertunda";
			else if ($data->data[$i]->CPM_TRAN_STATUS ==5) $statusSPPT = $this->getSPPTInfo($data->data[$i]->CPM_OP_NOMOR,$data->data[$i]->CPM_OP_NOMOR,$PAID);
			
			
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_OP_NOMOR."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\">".$data->data[$i]->CPM_WP_NAMA."</a></td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_LETAK."</td><td class=\"".$class."\" align=\"center\">".
					number_format(intval($ccc),0,".",",")."</td><td class=\"".$class."\">".$data->data[$i]->CPM_SSB_AUTHOR."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusSPPT."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$statusdoc."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr></div>\n";
		}
		
		$dat = $HTML;
		return true;
	}
	
	function getDocumentInfoText($sts,&$dat) {
		global $data,$appDbLink,$json;
		$srcTxt =@isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%' OR A.CPM_WP_NOKTP = '".$srcTxt."')";
		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".$sts." 
				AND B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
	
		$res = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			return false; 
		}
		
		if (($this->userGroup == 2) || ($this->userGroup == 3)) {
			$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
					$sts." AND  B.CPM_TRAN_FLAG=0 $where ";
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
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_LETAK."</td>\n";
						
			$ccc = $this->getBPHTBPayment($data->data[$i]->CPM_OP_LUAS_BANGUN,$data->data[$i]->CPM_OP_NJOP_BANGUN,$data->data[$i]->CPM_OP_LUAS_TANAH,
			$data->data[$i]->CPM_OP_NJOP_TANAH,$data->data[$i]->CPM_OP_HARGA,$data->data[$i]->CPM_PAYMENT_TIPE_PENGURANGAN,$data->data[$i]->CPM_OP_JENIS_HAK,$data->data[$i]->CPM_OP_NPOPTKP);
			
			if (($data->data[$i]->CPM_PAYMENT_TIPE == '2') && (!is_null($data->data[$i]->CPM_OP_BPHTB_TU))) {
				$ccc  = $data->data[$i]->CPM_OP_BPHTB_TU;
			}
			
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format(intval($ccc),0,".",",")."</td><td class=\"".$class."\">".$data->data[$i]->CPM_SSB_AUTHOR."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"><a href=\"main.php?param=".base64_encode($par1)."\"><span title=\"".$data->data[$i]->CPM_TRAN_INFO."\" class=\"span-title\">".
						$this->splitWord($data->data[$i]->CPM_TRAN_INFO,5)."</span></a></td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";
			//$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_SSB_VERSION."</td>\n";
			$HTML .= "\t</tr>\n";
		}
		$dat = $HTML;
		return true;
	}
	function getDocument($sts,&$dat) {
		global $data,$appDbLink,$json;
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		//if ($srcTxt != "") $where = " AND A.CPM_WP_NAMA LIKE '".$srcTxt."%'";
		if ($srcTxt != "") $where .= " AND (A.CPM_WP_NAMA LIKE '".$srcTxt."%' OR A.CPM_OP_NOMOR LIKE '".$srcTxt."%' OR A.CPM_WP_NOKTP = '".$srcTxt."')";
		$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
				$sts." AND  B.CPM_TRAN_FLAG=0 $where ORDER BY B.CPM_TRAN_DATE DESC LIMIT ".$this->page.",".$this->perpage;
				
		$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=".
				$sts." AND  B.CPM_TRAN_FLAG=0 $where ";
		
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
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			if ($this->userGroup==2) if (($data->data[$i]->CPM_TRAN_READ == "") ||($data->data[$i]->CPM_TRAN_OPR_DISPENDA_1 == "")) $class = "tdbodyNew";
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
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->CPM_OP_LETAK."</td><td class=\"".$class."\">".$data->data[$i]->CPM_SSB_AUTHOR."</td><td class=\"".$class."\" align=\"right\">".
					number_format($ccc,0,".",",")."</td>\n";	
			$HTML .= "\t\t<td class=\"".$class."\" align=\"center\">".$data->data[$i]->CPM_TRAN_DATE."</td>\n";		
			$HTML .= "\t</tr></div>\n";
		}
		$dat = $HTML;
		return true;
	}
	
	public function headerContent($sts) {
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP/NOP/NOKTP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\" value=\"{$srcTxt}\"/> 
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"150\">User</td>\n";
		if ($sts ==5) $HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >BPHTB harus dibayar</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
		$HTML .= "\t</tr>\n";

		if ($this->getDocument($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Data Kosong !</td></tr> ";
		}
		
		$HTML .= "</table>\n";
		return $HTML;
	}
	public function headerContentApprove($sts) {
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "<input type=\"button\" value=\"Cetak\" id=\"btn-print\" name=\"btn-print\" 
				onclick=\"printDataToPDF(3);\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
						Masukan Pencarian Berdasarkan Nama WP/NOP/NOKTP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\" value =\"{$srcTxt}\"/> 
						<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td width=\"20\" class=\"tdheader\"><div class=\"container\">
			<div class=\"all\"><input name=\"all\" id=\"all\" type=\"checkbox\" value=\"\"/></div></div></td>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"100\">User Pelaporan</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >BPHTB harus dibayar</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"150\" >Tanggal</td>\n";
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
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP/NOP/NOKTP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\" value=\"{$srcTxt}\"/> <input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\"  width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\"  width=\"170\">User Pelaporan</td><td class=\"tdheader\"  width=\"200\">Alasan Penolakan</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"130\" > Tanggal</td>\n";
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
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "Masukan Pencarian Berdasarkan Nama WP/NOP/NOKTP <input type=\"text\" id=\"src-approved\" name=\"src-approved\" value=\"{$srcTxt}\" size=\"60\"/> 
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak</td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td><td class=\"tdheader\" width=\"170\">User</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
		//$HTML .= "\t\t<td class=\"tdheader\" >Status Pembayaran</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Status</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" >Tanggal</td>\n";
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
}

$par1 = $params."&n=1&s=5";
$par2 = $params."&n=2&s=4";
$par3 = $params."&n=3&s=2";
$par4 = $params."&n=4&s=3";
$par5 = $params."&n=5&s=100";

if ($sel==1) $sts=5;
if ($sel==2) $sts=4;
if ($sel==3) $sts=2;
if ($sel==4) $sts=3;
if ($sel==5) $sts=100;

$modNotaris = new stafDispenda(2,$data->uname);

$modNotaris->addMenu("Disetujui","app-menu",base64_encode($par1));
$modNotaris->addMenu("Ditolak","rej-menu",base64_encode($par2));
$modNotaris->addMenu("Tertunda","dil-menu",base64_encode($par3));
$modNotaris->addMenu("Proses","pro-menu",base64_encode($par4));
$modNotaris->addMenu("Semua Data","all-menu",base64_encode($par5));

$modNotaris->setSelectedMenu($sel);
$modNotaris->setStatus($sts);
$modNotaris->setDataPerPage(60);
$modNotaris->setDefaultPage($page);


echo $modNotaris->showData();

?>
