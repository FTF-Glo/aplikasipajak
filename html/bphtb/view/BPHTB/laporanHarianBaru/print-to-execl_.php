<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'laporanHarianBaru', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."view/BPHTB/mod-display-laporan-harian-baru.php");
require_once("svc-bpn-lookup.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=laporan_harian.xls");
?>

<link href="view/PBB/spop.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css"/>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript">
	var page = 1;
	// var axx='<?php echo base64_encode($a)?>';
	function setTabs (sts) {
		var tanggal = $("#tanggal").val();
		var jenis_hk = $("#jenis_hk").val();
		var sel = 0;
		$( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: { tanggal: tanggal, jenis_hk: jenis_hk } } );
		$( "#tabsContent" ).tabs( "option", "selected", sel );
		$( "#tabsContent" ).tabs('load', sel);			
	}

	$(document).ready(function() {
        /*$("#all-check-button").click(function() {
            $('.check-all').each(function(){ 
                this.checked = $("#all-check-button").is(':checked'); 
            });
        });*/
		
        $("#tabsContent").tabs({
            load: function (e, ui) {
                $(ui.panel).find(".tab-loading").remove();
            },

            select: function (e, ui) {
                var $panel = $(ui.panel);
				var d = $('#select-all').val();
                if ($panel.is(":empty")) {
                    $panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
                }
            }
        });
		
		//getter
		//var selected = $( "#tabsContent" ).tabs( "option", "selected" );
		//console.log(selected);
		
	});
</script>

<?php
echo "<link rel=\"stylesheet\" href=\"view/BPHTB/laporanHarianBaru/mod-bpn.css\" type=\"text/css\">\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname='".$data->uname."';</script>\n";
echo "<script language=\"javascript\" src=\"view/BPHTB/laporanHarianBaru/mod-bpn.js\" type=\"text/javascript\"></script>\n";

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
	function jenishak($js){
		global $DBLink;
		
		$texthtml= "<select name=\"jenis_hk\" id=\"jenis_hk\">";
		$texthtml .= "<option value=\"\" >Pilih Jenis Hak</option>";
		$qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
						//echo $qry;exit;
						$res = mysqli_query($DBLink, $qry);
						
							while($data = mysqli_fetch_assoc($res)){
								if($js==$data['CPM_KD_JENIS_HAK']){
									$selected= "selected"; 
								}else{
									$selected= "";
								}
								$texthtml .= "<option value=\"".$data['CPM_KD_JENIS_HAK']."\" ".$selected." >".str_pad($data['CPM_KD_JENIS_HAK'],2,"0",STR_PAD_LEFT)." ".$data['CPM_JENIS_HAK']."</option>";
							}
	$texthtml .="			      </select>";
	return $texthtml;
		
	}
	function getDocument($sts,&$dat) {
		global $data,$DBLink,$json;
		$srcTxt = @isset($_REQUEST['tanggal'])?$_REQUEST['tanggal']:date('Y-m-d');
		$srcTxt2 = @isset($_REQUEST['jenis_hk'])?$_REQUEST['jenis_hk']:"";
		
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
		$DbName = $this->getConfigValue("aBPHTB",'BPHTBDBNAME');
		$DbHost = $this->getConfigValue("aBPHTB",'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue("aBPHTB",'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue("aBPHTB",'GTW_TABLE_NAME');
		$DbUser = $this->getConfigValue("aBPHTB",'BPHTBUSERNAME');
		$tw = $this->getConfigValue("aBPHTB",'TENGGAT_WAKTU');
		$DbNameSW = $this->getConfigValue("aBPHTB",'BPHTBDBNAMESW');
		
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
		echo $query;
		$res = mysqli_query($query);
		if ( $res === false ){
			 print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " .$query.":". mysqli_error() . "' );</script>";
			 return false; 
		}

		$d =  $json->decode($this->mysql2json($res,"data"));	
		$HTML = "";
		$data = $d;
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']; 
		$ss = true;
		$tw = 0;

		$total_bayar = 0;
		$total_denda = 0;
		$total_seluruh = 0;
		$berkas = count($data->data);
		for ($i=0;$i<count($data->data);$i++) {
			$class = $i%2==0 ? "tdbody1":"tdbody2";
			$HTML .= "\t<div class=\"container\"><tr>\n";
			$HTML .= "\t\t<td class=\"".$class."\">".($i+1)."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->id_ssb."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->wp_nama."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\">".$data->data[$i]->wp_alamat."</td><td class=\"".$class."\" align=\"center\">".$data->data[$i]->op_nomor."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\"  align=\"center\">".$data->data[$i]->CPM_OP_NMR_SERTIFIKAT."</td> \n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($data->data[$i]->bphtb_dibayar,0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".number_format($data->data[$i]->CPM_DENDA,0,".",",")."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$data->data[$i]->bphtb_notaris."</td>\n";
			$HTML .= "\t\t<td class=\"".$class."\" align=\"right\">".$data->data[$i]->CPM_JENIS_HAK."</td>\n";
			$HTML .= "\t</tr></div>\n";
			$tw ++;
			$total_bayar = $total_bayar + $data->data[$i]->bphtb_dibayar;
			$total_denda = $total_denda + $data->data[$i]->CPM_DENDA;
			if($i == (count($data->data)-1)){
				$HTML .= "\t<div class=\"container\"><tr style='height:25px;' >\n";
				$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
				$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
				$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
				$HTML .= "\t\t<td class=\"".$class."\"></td><td class=\"".$class."\" align=\"center\"></td>\n";
				$HTML .= "\t\t<td class=\"".$class."\" align=\"center\"></td> \n";
				$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
				$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
				$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
				$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
				$HTML .= "\t</tr></div>\n";
			}
		}
		$total_seluruh = $total_bayar + $total_denda;
		$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class." tdheader \">TOTAL</td><td class=\"".$class."\" align=\"center\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class." tdheader \" align=\"right\">".number_format($total_bayar,0,".",",")."</td>\n";
		$HTML .= "\t\t<td class=\"".$class." tdheader \" align=\"right\">".number_format($total_denda,0,".",",")."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";
		$HTML .= "\t<div class=\"container\"><tr style='height:25px;' >\n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td><td class=\"".$class."\" align=\"center\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"center\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";		
		$HTML .= "\t<div class=\"container\"><tr>\n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td> \n";
		$HTML .= "\t\t<td class=\"".$class."\"></td><td class=\"".$class." tdheader\" align=\"center\">TOTAL BERKAS</td>\n";
		$HTML .= "\t\t<td class=\"".$class." tdheader \" align=\"center\">".$berkas." BERKAS</td> \n";
		$HTML .= "\t\t<td class=\"".$class." tdheader \" align=\"right\">".number_format($total_seluruh,0,".",",")."</td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t\t<td class=\"".$class."\" align=\"right\"></td>\n";
		$HTML .= "\t</tr></div>\n";
		
		#ardi total row
		// $allRows= mysqli_query("SELECT * FROM $DbTable $where");
		// $this->totalRows = mysqli_num_rows($allRows);

		$dat = $HTML;
		$total = count($data->data);
		SCANPayment_CloseDB($LDBLink);
		
		if($total == 0){
			return false;
		}else{
			return true;
		}
	}
	
	public function headerContentAll($sts) {
		global $srcTxt,$srcTxt2;
		$a = $_REQUEST['a'];
		$srcTxt = @isset($_REQUEST['tanggal'])?$_REQUEST['tanggal']:"";
		$srcTxt2 = @isset($_REQUEST['jenis_hk'])?$_REQUEST['jenis_hk']:"";
		
		if($srcTxt == '' && $srcTxt2 == ''){
			$srcTxt = date('Y-m-d');
		}

		$j = base64_encode("{'sts':'".$sts."','app':'".$a."','src':'".$srcTxt."','src2':'".$srcTxt2."'}");
		$HTML = "<form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >";
		$HTML .= "<table>
					<tr>
						<td>
							<b>Pilih Hari Pembayaran</b>
						</td>
						<td> 
							: 
						</td>
						<td> 
							<input type=\"text\" id=\"tanggal\" name=\"tanggal\" size=\"20\" value='".$srcTxt."'>
						</td>
						<td>
							<input type=\"submit\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />
						</td>
						<td>
							<input type=\"button\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('".$j."');\"/>
						</td>
					</tr>
					<tr>
						<td>
							<b>Pilih Jenis Hak</b>
						</td>
						<td>
							:
						</td>
						<td>
							".$this->jenishak($srcTxt2)."
						</td>
						<td>
						</td>
						<td>
						</td>
					</tr>
				  </table> 
                </form>";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
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
		// $HTML .= "\t\t<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>\n";
		// $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Tanggal Pembayaran</td>\n";
		if ($this->getConfigValue($a,'TYPE_PROSES')=='1') $HTML .= "\t\t<td class=\"tdheader\" width=\"170\">Disetujui</td>\n";
		$HTML .= "\t</tr>\n";

		if ($this->getDocument($sts,$dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Pada Tanggal ".$srcTxt." Data Kosong !</td></tr> ";
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
    	echo "<script>
				$(function(){
                    $( '#tanggal' ).datepicker({ dateFormat: 'yy-mm-dd'});
                });
              </script>";
		echo "<div id=\"notaris-main-content\">\n";
		echo "\t<div id=\"notaris-main-content-inner\">\n";
		echo $this->getContent();
		echo "\t</div>\n";
		// echo "\t<div id=\"notaris-main-content-footer\" align=\"right\">  \n";
		// echo $this->paging();
		// echo "</div>\n";
		
	}
}
$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";

$q = base64_decode($q);

$q = $json->decode($q);

$sts = $q->sts;
$app = $q->app;
$srcTxt = $q->src;
$srcTxt2 = $q->src2;
$page = @isset($_REQUEST['p'])?$_REQUEST['p']:1;

$modBPN = new  modBPN(1,$data->uname);

$pages =  $modBPN->getConfigValue("aBPHTB","ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);

$modBPN->setDefaultPage($page);

?> 

<div id="tabsContent">
	<?php echo $modBPN->showData(); ?>
</div>