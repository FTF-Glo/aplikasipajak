<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'bpn', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display.php");
require_once("svc-bpn-lookup.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<link rel=\"stylesheet\" href=\"view/BPHTB/bpn/mod-bpn.css?0001\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/bpn/mod-bpn.js\" type=\"text/javascript\"></script>\n";

class modBPN extends modBPHTBApprover {
    var $owner;
	function getAUTHOR($nop) {
		global $data,$DBLink;
		
		$query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '".$nop."'";
	
		$res = mysqli_query($DBLink, $query);
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
	
	function getFromGateway ($id) {
		global $DBLink;
		$query = "SELECT CPM_TRAN_STATUS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=6 
				AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID ='{$id}'";
		$resStatus = mysqli_query($DBLink, $query);
		if ( $resStatus === false ){
			return "Tidak Ditemukan"; 
		}
		$sts = false;
		$rowStatus = mysqli_fetch_array($resStatus);
		//if ($rowStatus != "") $app = $rowStatus['CPM_TRAN_STATUS'];
		if ($rowStatus != "") $sts = true;
		return $sts;
	}
	
	function getDocument($sts,&$dat) {
		global $DBLink,$json,$page;
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		$srcPcd = @isset($_REQUEST['src-paycode'])?$_REQUEST['src-paycode']:"";
		$srcNik = @isset($_REQUEST['src-nik'])?$_REQUEST['src-nik']:"";

		$where = " WHERE PAYMENT_FLAG = 1";
		if ($srcTxt != "") $where .= " AND (wp_nama LIKE '".$srcTxt."%' OR op_nomor LIKE '".$srcTxt."%')";
		if ($srcPcd != "") $where .= " AND (payment_code LIKE '%".$srcPcd."%')";
		if ($srcNik != "") $where .= " AND (wp_noktp LIKE '%".$srcNik."%')";
		$iErrCode=0;

		$a = $_REQUEST['a'];
		$DbName = $this->getConfigValue($a,'BPHTBDBNAME');
		$DbHost = $this->getConfigValue($a,'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue($a,'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue($a,'BPHTBTABLE');
		$DbUser = $this->getConfigValue($a,'BPHTBUSERNAME');
		$tw = $this->getConfigValue($a,'TENGGAT_WAKTU');
		
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
			 print_r("Pengambilan data Gagal");
			 echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error($LDBLink) . "' );</script>";
			 return false; 
		}
		
		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
		$ss = true;
		$tw = 0;
        $no = ($page - 1) * $this->perpage;

		for ($i=0;$i<count($data->data);$i++) {
			if ($this->getFromGateway ($data->data[$i]->id_switching)) {
				$app = "Disetujui";
			} else {
				$app = "Belum";
			}
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$HTML .= "\t<div class=\"container\"><tr>\n";
            $HTML .= "\t\t<td width=\"20\" class=\"" . $class . "\" align=\"center\">" . (++$no) . ".</td>\n";

			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->op_nomor."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->wp_nama."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->op_letak."</td><td class=\"".$class."\" align=\"center\">".$this->getAUTHOR($data->data[$i]->op_nomor)."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($data->data[$i]->bphtb_dibayar,0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"  align=\"center\">".$data->data[$i]->payment_paid."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\"  align=\"center\">".$data->data[$i]->payment_code."</td> \n";
			if ($this->getConfigValue($a,'TYPE_PROSES')=='1') $HTML .= "\t\t<td class=\"".$class."\"  align=\"center\">".$app."</td> \n";
			$HTML .= "\t</tr></div>\n";
			$tw ++;
		}
		
		#ardi total row
		$allRows= mysqli_query($LDBLink, "SELECT * FROM $DbTable $where");
		$this->totalRows = mysqli_num_rows($allRows);
		
		$dat = $HTML;
		return true;
	}
	
	public function headerContentAll($sts) {
		$a = $_REQUEST['a'];
		$srcTxt = @isset($_REQUEST['src-approved'])?$_REQUEST['src-approved']:"";
		
		$j = base64_encode("{'sts':'".$sts."','app':'".$a."','src':'".$srcTxt."'}");
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
					<style>
					.form-filtering {
						background-color: #fff;
						padding: 20px 20px;
						
						box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
				
					}
					</style>

					<div  class=\"p-2\">
						<div class=\"row\"> 
							<div class=\"col-12 pb-1\" style=\"display:flex; align-items:center;justify-content:end;\"> 
								<button class=\"btn btn-info\" type=\"button\" style=\"border-radius:8px\" data-toggle=\"collapse\" data-target=\"#collapsFilter\" aria-expanded=\"false\" aria-controls=\"collapsFilter\">
									Filter Data
								</button>
							</div>
						</div>
					</div>
					<div class=\"col-12\"> 
						<div class=\"collapse\" id=\"collapsFilter\">
							<div class=\"form-filtering\">
								<form>
									<div class=\"row\">
										<div class=\"form-group col-md-4\" >
											
											<label>NOP / Nama WP </label>
											<input class=\"form-control\" type=\"text\" id=\"src-approved\" name=\"src-approved\"/>
											
										</div>
											
										<div class=\" form-group col-md-4\"> 
											<label>NIK</label>
											<input class=\"form-control\" type=\"text\" id=\"src-nik\" name=\"src-nik\" /> 
										
										</div>
					
										<div class=\" form-group col-md-4\"> 
											<label>Kode Bayar</label>
											<input class=\"form-control\" type=\"text\" id=\"src-paycode\" name=\"src-paycode\" />
										</div>

										<div class=\" form-group col-md-12\">    
											<input type=\"submit\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />\n
											<input type=\"button\" class=\"btn btn-info\" value=\"Cetak PDF\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToPDF('".$j."');\"/>
											<input type=\"button\" class=\"btn btn-info\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('".$j."');\"/>

											
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
		
				</form>\n";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
		$HTML .= "\t<tr>\n";
        $HTML .= "\t\t<td class=\"tdheader\"> No.</td> \n";

		$HTML .= "\t\t<td class=\"tdheader\"> Nomor Objek Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\"> Wajib Pajak </td> \n";
		$HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Tanggal Pembayaran</td>\n";
		$HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Kode Bayar</td>\n";
		if ($this->getConfigValue($a,'TYPE_PROSES')=='1') $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Disetujui</td>\n";
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
		$HTML = $this->headerContentAll($this->status);
		
		return $HTML;
	}
    function showData() {
		echo "<div id=\"notaris-main-content\">\n";
		echo "\t<div id=\"notaris-main-content-inner\">\n";
		echo $this->getContent();
		echo "\t</div>\n";
		echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
		echo $this->paging();
		echo "</div>\n";
	}
}

$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;

$modBPN = new  modBPN(1,$data->uname);

$pages =  $modBPN->getConfigValue("aBPHTB","ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);

$modBPN->setDefaultPage($page);


echo $modBPN->showData();

?> 
