<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'monitoring', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");
//require_once("svc-bpn-lookup.php");


error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/notaris/mod-notaris.css?0002\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/js/jquery-1.3.2.min.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\">var ap='".$_REQUEST['a']."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/monitoring/mod-monitoring.js\" type=\"text/javascript\"></script>\n";

$json = new Services_JSON();

class modMonitoring extends modBPHTBApprover {
	
	function getAUTHOR($nop) {
		global $data,$appDbLink;
		
		$query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '".$nop."'";
	
		$res = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			return "Tidak Ditemukan"; 
		}
		$json = new Services_JSON();
		$data =  $json->decode($this->mysql2json($res,"data"));	
		for ($i=0;$i<count($data->data);$i++) {
			return $data->data[$i]->CPM_SSB_AUTHOR;
		}
		return "Tidak Ditemukan";
	}
	

	function getTotalRows($query,$dbl) {
		$res = mysqli_query($dbl, $query);
		if ( $res === false ){
			echo $query ."<br>";
			echo mysqli_error($dbl);
		}
		
		$row = mysqli_fetch_array($res);
		return $row['TOTALROWS'];
	}
	
	function getDocument($sts,&$dat) {
		global $appDbLink,$json;
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$where = "";
		
		$a = $_REQUEST['a'];
		$DbName = $this->getConfigValue($a,'BPHTBDBNAME');
		$DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue($a,'BPHTBTABLE');
		$DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
		$tw = $this->getConfigValue($a,'TENGGAT_WAKTU');
		
		if ($sts == 1) $where = " WHERE PAYMENT_FLAG = 1";
		if ($sts == 2) $where = " WHERE PAYMENT_FLAG = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) > NOW()";
		if ($sts == 3) $where = " WHERE PAYMENT_FLAG = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) < NOW()";
		if ($sts == 4) $where = " WHERE bphtb_dibayar = 0 AND DATE_ADD(saved_Date, INTERVAL $tw DAY) > NOW()";

		if ($srcTxt != "") $where .= " AND (wp_nama LIKE '".$srcTxt."%' or op_nomor LIKE '".$srcTxt."')";
		$iErrCode=0;

	
		
		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName,true);
		if ($iErrCode != 0)
		{
		  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
		  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		  exit(1);
		}
		
		
		$query = "SELECT * FROM $DbTable $where ORDER BY saved_Date DESC LIMIT ".$this->page.",".$this->perpage; 
	   
		$res = mysqli_query($LDBLink, $query);
		if ( $res === false ){
			 print_r($query.mysqli_error($LDBLink));
			 return false; 
		}
		
		$qry= "SELECT count(*) as TOTALROWS FROM $DbTable {$where}";
		$this->totalRows = $this->getTotalRows($qry,$LDBLink);
			
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
		$ss = true;
		$tw = 0;
		for ($i=0;$i<count($data->data);$i++) {
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->op_nomor."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->wp_nama."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->wp_alamat."</td><td class=\"".$class."\" align=\"center\">".$this->getAUTHOR($data->data[$i]->op_nomor)."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($data->data[$i]->bphtb_dibayar,0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"  align=\"center\">".$data->data[$i]->payment_paid."</td> \n";
			$HTML .= "\t</tr></div>\n";
			$tw ++;
		}
		//$this->totalRows = $tw;
		$dat = $HTML;
		return true;
	}
	
	public function headerContentAll($sts) {
		$a = $_REQUEST['a'];
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$j = base64_encode("{'sts':'".$sts."','app':'".$a."','src':'".$srcTxt."'}");
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "<input type=\"button\" value=\"Cetak PDF\" id=\"btn-print\" name=\"btn-print\" 
		onclick=\"printToPDF('".$j."');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
		<input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" 
		onclick=\"printToXLS('".$j."');\"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n
				Masukan Query Pencarian <input type=\"text\" id=\"src-approved\" name=\"src-approved\" size=\"60\"/> 
				<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Tanggal Pembayaran</td>\n";
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
		$HTML = $this->headerContentAll($this->status);
		
		return $HTML;
	}
}


$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
//$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;
//print_r($_REQUEST);

$par1 = $params."&n=1&s=1";
$par2 = $params."&n=2&s=2";
$par3 = $params."&n=3&s=3";
$par4 = $params."&n=4&s=4";
//$par5 = $params."&n=5&s=5";

if ($sel==1) $sts=1;
if ($sel==2) $sts=2;
if ($sel==3) $sts=3;
if ($sel==4) $sts=4;
//if ($sel==5) $sts=5;

$modNotaris = new  modMonitoring(1,$data->uname);

$modNotaris->addMenu("Sudah Dibayar","rej-menu",base64_encode($par1));
$modNotaris->addMenu("Siap Bayar","dil-menu",base64_encode($par2));
$modNotaris->addMenu("Kadaluarsa","tmp-menu",base64_encode($par3));
$modNotaris->addMenu("Nihil","tmp-menu",base64_encode($par4));
//$modNotaris->addMenu("Semua Data","app-menu",base64_encode($par5));
$modNotaris->setSelectedMenu($sel);
$modNotaris->setStatus($sts);
$modNotaris->setDataPerPage(30);
$modNotaris->setDefaultPage($page);

echo "<div id=\"summary\" style=\"float:right; margin-right:20px; display:block; margin-top:8px; font-weight:bold;\">Total Transaksi : <span id=\"tot-trans\">
</span> | Total Penerimaan : <span id=\"tot-trims\"></span></div><br>";
echo $modNotaris->showData();

?>
