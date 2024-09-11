<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'laporanRekapitulasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/c8583.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "view/BPHTB/mod-display-laporan-harian-baru.php");

//require_once("svc-bpn-lookup.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

?>

<link href="view/PBB/spop.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-ui-1.8.18.custom/css/ui-lightness/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript">
	var page = 1;
	// var axx='<?php echo base64_encode($a) ?>';
	function setTabs(sts) {
		var tanggal = $("#tanggal").val();
		var jenis_hk = $("#jenis_hk").val();
		var sel = 0;
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: {
				tanggal: tanggal,
				jenis_hk: jenis_hk
			}
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}

	$(document).ready(function() {
		/*$("#all-check-button").click(function() {
		    $('.check-all').each(function(){ 
		        this.checked = $("#all-check-button").is(':checked'); 
		    });
		});*/

		$("#tabsContent").tabs({
			load: function(e, ui) {
				$(ui.panel).find(".tab-loading").remove();
			},

			select: function(e, ui) {
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
echo "<link rel=\"stylesheet\" href=\"view/BPHTB/laporanRekapitulasi/mod-bpn.css?0001\" type=\"text/css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>";
echo "<script language=\"javascript\">var uname='" . $data->uname . "';</script>";
echo "<script language=\"javascript\" src=\"view/BPHTB/laporanRekapitulasi/mod-bpn.js\" type=\"text/javascript\"></script>";

class modBPN extends modBPHTBApprover
{
	var $owner;
	function getAUTHOR($nop)
	{
		global $data, $DBLink;

		$query = "SELECT CPM_SSB_AUTHOR FROM cppmod_ssb_doc WHERE CPM_OP_NOMOR = '" . $nop . "'";

		$res = mysqli_query($DBLink, $query);
		if ($res === false) {
			return "Tidak Ditemukan";
		}
		$json = new Services_JSON();
		$data =  $json->decode($this->mysql2json($res, "data"));
		for ($i = 0; $i < count($data->data); $i++) {
			return $data->data[$i]->CPM_SSB_AUTHOR;
		}
		return "Tidak Ditemukan";
	}

	function getFromGateway($id)
	{
		global $DBLink;
		$query = "SELECT CPM_TRAN_STATUS FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=6 
				AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID ='{$id}'";
		$resStatus = mysqli_query($DBLink, $query);
		if ($resStatus === false) {
			return "Tidak Ditemukan";
		}
		$sts = false;
		$rowStatus = mysqli_fetch_array($resStatus);
		//if ($rowStatus != "") $app = $rowStatus['CPM_TRAN_STATUS'];
		if ($rowStatus != "") $sts = true;
		return $sts;
	}

	function jenishak($js)
	{
		global $DBLink;

		$texthtml = "<select class=\"form-control\" name=\"jenis_hk\" style=\"height:30px\" id=\"jenis_hk\">";
		$texthtml .= "<option value=\"\" >Pilih Jenis Hak</option>";
		$qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
		//echo $qry;exit;
		$res = mysqli_query($DBLink, $qry);

		while ($data = mysqli_fetch_assoc($res)) {
			if ($js == $data['CPM_KD_JENIS_HAK']) {
				$selected = "selected";
			} else {
				$selected = "";
			}
			$texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\" " . $selected . " >" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
		}
		$texthtml .= "			      </select>";
		return $texthtml;
	}

	function getConfigValue($id, $key)
	{
		global $DBLink;
		$id = $_REQUEST['a'];
		$qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
		$res = mysqli_query($DBLink, $qry);
		if ($res === false) {
			echo $qry . "<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	}

	function getDataNotaris($id)
	{
		global $DBLink;

		$qry = "select * from tbl_reg_user_notaris where userId = '" . $id . "'";
		$res = mysqli_query($DBLink, $qry);
		if ($res === false) {
			echo $qry . "<br>";
			echo mysqli_error($DBLink);
		}
		$data = array();
		while ($row = mysqli_fetch_assoc($res)) {
			$data['almt_jalan'] = $row['almt_jalan'];
		}

		return $data;
	}

	function getDocument($sts, &$dat)
	{
		global $data, $DBLink, $json;
		$srcTxt = @isset($_REQUEST['tanggal']) ? $_REQUEST['tanggal'] : date('Y-m-d');
		$srcTxt2 = @isset($_REQUEST['jenis_hk']) ? $_REQUEST['jenis_hk'] : "";
		$srcTgl2 = @isset($_REQUEST['tanggal2']) ? $_REQUEST['tanggal2'] : "";
		if ($srcTxt == '' && $srcTxt2 == '') {
			$srcTxt = date('Y-m-d');
			$srcTgl2 = date('Y-m-d');
		}
		$a = 'aBPHTB';
		$DbName = $this->getConfigValue($a, 'BPHTBDBNAME');
		$DbHost = $this->getConfigValue($a, 'BPHTBHOSTPORT');
		$DbPwd = $this->getConfigValue($a, 'BPHTBPASSWORD');
		$DbTable = $this->getConfigValue($a, 'BPHTBTABLE');
		$DbUser = $this->getConfigValue($a, 'BPHTBUSERNAME');
		$tw = $this->getConfigValue($a, 'TENGGAT_WAKTU');
		$DbNameSW = $this->getConfigValue($a, 'BPHTBDBNAMESW');
		// $where = " WHERE PAYMENT_FLAG = 1";
		$where = "";
		$where2 = "";
		if ($srcTxt != "") $where .= " AND $DbName.ssb.payment_paid >= '" . $srcTxt . " 00:00:00' ";
		if ($srcTgl2 != "") $where .= " AND $DbName.ssb.payment_paid <= '" . $srcTgl2 . " 23:59:59'";
		if ($srcTxt2 != "") $where2 .= " AND $DbNameSW.cppmod_ssb_jenis_hak.CPM_KD_JENIS_HAK = '" . $srcTxt2 . "'";
		$iErrCode = 0;

		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
		SCANPayment_ConnectToDB($XDBLink, $XDBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, $DbNameSW, true);

		if ($iErrCode != 0) {
			$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
				error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg", 3, LOG_FILENAME);
			exit(1);
		}

		$querycount = "SELECT count(*) RecordCount FROM $DbName.ssb
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

		$result = mysqli_query($XDBLink, $querycount);
		$row = mysqli_fetch_assoc($result);
		$berkas = $row['RecordCount'];
		// var_dump($berkas);exit;
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
		// ORDER BY $DbName.ssb.id_ssb DESC LIMIT ".$this->page.",".$this->perpage; 

		$result = mysqli_query($XDBLink, $query);
		if ($res === false) {
			print_r("Pengambilan data Gagal");
			echo "<script>console.log( 'Debug Objects: " . $query . ":" . mysqli_error() . "' );</script>";
			return false;
		}
		$params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
		$ss = true;
		$tw = 0;

		$total_bayar = 0;
		$total_denda = 0;
		$total_seluruh = 0;
		$total_njop = 0;
		$total_njopperm = 0;
		$total_njopbang = 0;
		$total_njoppermbang = 0;
		$total_trans = 0;
		$i = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			// HITUNGAN
			$njoptanah = $row['CPM_OP_LUAS_TANAH'] * $row['CPM_OP_NJOP_TANAH'];
			$njopbang = $row['CPM_OP_LUAS_BANGUN'] * $row['CPM_OP_NJOP_BANGUN'];
			// echo "<pre>";
			// var_dump($row);
			// die;
			$class = $i % 2 == 0 ? "tdbody1" : "tdbody2";
			$HTML .= "<div class=container><tr>";
			$HTML .= "<td class=$class>" . ($i + 1) . "</td>";
			$HTML .= "<td class=$class>" . $row['payment_paid'] . "</td>";
			$HTML .= "<td class=$class>" . $row['saved_date'] . "</td>";
			$HTML .= "<td class=$class>" . $row['op_nomor'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['payment_code'] . "</td>";
			$HTML .= "<td class=$class>" . $row['wp_nama'] . "</td>";
			$HTML .= "<td class=$class>" . $row['wp_alamat'] . "</td>
					  <td class=$class align=center>" . $row['wp_noktp'] . "</td>";

			$HTML .= "<td class=$class>" . $row['CPM_WP_NAMA_LAMA'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_SSB_AUTHOR'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_OP_NMR_SERTIFIKAT'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_OP_LETAK'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_OP_KECAMATAN'] . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_OP_LUAS_TANAH'] . "</td>";
			$HTML .= "<td class=$class align=center>" . number_format($njoptanah, 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class align=center>" . number_format($row['CPM_OP_NJOP_TANAH'], 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class align=center>" . $row['CPM_OP_LUAS_BANGUN'] . "</td>";
			$HTML .= "<td class=$class align=center>" . number_format($njopbang, 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class align=center>" . number_format($row['CPM_OP_NJOP_BANGUN'], 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class align=right>" . number_format($row['bphtb_dibayar'], 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class align=right>" . number_format($row['CPM_OP_HARGA'], 0, ",", ".") . "</td>";
			$HTML .= "<td class=$class>" . $row['CPM_JENIS_HAK'] . "</td>";
			$HTML .= "</tr></div>";
			$tw++;
			$total_bayar = $total_bayar + $row['bphtb_dibayar'];
			$total_denda = $total_denda + $row['CPM_DENDA'];
			$total_njop = $total_njop + $njoptanah;
			$total_njopperm = $total_njopperm + $row['CPM_OP_NJOP_TANAH'];
			// $total_njopbang = 0;
			// $total_njoppermbang = 0;
			$total_njopbang = $total_njopbang + $njopbang;
			$total_njoppermbang = $total_njoppermbang + $row['CPM_OP_NJOP_BANGUN'];
			$total_trans = $total_trans + $row['CPM_OP_HARGA'];
			if ($i == (count($data->data) - 1)) {
				$HTML .= "<div class=container><tr style='height:25px;' >";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td>";
				$HTML .= "<td class=$class></td><td class=$class align=center></td>";
				$HTML .= "<td class=$class align=center></td>";
				$HTML .= "<td class=$class align=right></td>";
				$HTML .= "<td class=$class align=right></td>";
				$HTML .= "<td class=$class align=right></td>";
				$HTML .= "<td class=$class align=right></td>";
				$HTML .= "</tr></div>";
			}
			$i++;
		}
		$total_seluruh = $total_bayar + $total_denda;
		$HTML .= "<div class=container><tr>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td>TOTAL</td><td align=center></td>";
		$HTML .= "<td align=center></td>";
		$HTML .= "<td align=right>" . number_format($total_njop, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right>" . number_format($total_njopperm, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=center>" . number_format($total_njopbang, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right>" . number_format($total_njoppermbang, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right>" . number_format($total_bayar, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right>" . number_format($total_trans, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "</tr></div>";
		$HTML .= "<div class=container><tr style='height:25px;' >";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td><td align=center></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td align=center></td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "</tr></div>";
		$HTML .= "<div class=container><tr>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td></td><td colspan=2 align=center>TOTAL BERKAS</td>";
		$HTML .= "<td></td>";
		// $HTML .= "<td></td>";
		$HTML .= "<td align=center>" . $berkas . " BERKAS</td>";
		$HTML .= "<td></td><td colspan=2 align=center>TOTAL BPHTB + Denda</td>";
		// $HTML .= "<td></td>";
		$HTML .= "<td></td>";
		$HTML .= "<td align=right>" . number_format($total_seluruh, 0, ",", ".") . "</td>";
		$HTML .= "<td align=right></td>";
		$HTML .= "<td align=right></td>";

		$HTML .= "</tr></div>";
		#ardi total row
		// $allRows= mysqli_query("SELECT * FROM $DbTable $where");
		// $this->totalRows = mysqli_num_rows($allRows);

		$dat = $HTML;
		$total = $berkas;
		SCANPayment_CloseDB($LDBLink);

		if ($total == 0) {
			return false;
		} else {
			return true;
		}
	}

	public function headerContentAll($sts)
	{
		global $data, $DBLink, $json;
		$a = $_REQUEST['a'];
		$srcTxt = @isset($_REQUEST['tanggal']) ? $_REQUEST['tanggal'] : date('Y-m-d');
		$srcTxt2 = @isset($_REQUEST['jenis_hk']) ? $_REQUEST['jenis_hk'] : "";
		$srcTgl2 = @isset($_REQUEST['tanggal2']) ? $_REQUEST['tanggal2'] : date('Y-m-d');
		if ($srcTxt == '' && $srcTxt2 == '') {
			$srcTxt = date('Y-m-d');
			$srcTgl2 = date('Y-m-d');
		}

		$j = base64_encode("{'sts':'" . $sts . "','app':'" . $a . "','src':'" . $srcTxt . "','src2':'" . $srcTxt2 . "','srcTgl2':'" . $srcTgl2 . "'}");
		$HTML = "<form autocomplete=\"off\" id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" >
		
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

									<div class=\"form-group col-md-4\">
										<label>Tanggal Pembayaran Awal </label>
										<div style=\"display: flex; align-items: center;\">
											<input type=\"text\"  class=\"form-control\" id=\"tanggal\" name=\"tanggal\"value='" . $srcTxt . "'>
											<p style=\"margin-left:10px; margin-bottom: 0rem;\">s/d</p>
										</div>
									</div>
									<div class=\"form-group col-md-4\">
										<label>Tanggal Pembayaran Akhir</label>
										<div>
											<input type=\"text\" class=\"form-control date\" id=\"tanggal2\" name=\"tanggal2\" value='" . $srcTgl2 . "'>
											
										</div>
									</div>

										<div class=\" form-group col-md-4\"> 
											<label>Jenih Hak</label>
											" . $this->jenishak($srcTxt2) . "
										</div>

										<div class=\" form-group col-md-12\">    
											<input type=\"submit\" class=\"btn btn-info\" value=\"Cari\" id=\"btn-src\" name=\"btn-src\" />
											<input type=\"button\" class=\"btn btn-info\" value=\"Cetak Excel\" id=\"btn-print\" name=\"btn-print\" onclick=\"printToXLS('" . $j . "');\"/>

											
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>

                </form>";
		$HTML .= "<div style=\"overflow-x:scroll\">";
		$HTML .= "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">";
		$HTML .= "<tr>";
		$HTML .= "<td class=\"tdheader\"> NO. </td>";
		$HTML .= "<td class=\"tdheader\"> TGL. BAYAR </td>";
		$HTML .= "<td class=\"tdheader\"> TGL. VERIFIKASI </td>";
		$HTML .= "<td class=\"tdheader\"> NO. OBJEK PAJAK </td>";
		$HTML .= "<td class=\"tdheader\"> KODE BAYAR </td>";
		$HTML .= "<td class=\"tdheader\"> NAMA PEMBELI </td>";
		$HTML .= "<td class=\"tdheader\"> ALAMAT PEMBELI </td>";
		$HTML .= "<td class=\"tdheader\"> KTP PEMBELI </td>";
		$HTML .= "<td class=\"tdheader\"> NAMA PENJUAL </td>";
		$HTML .= "<td class=\"tdheader\"> NOTARIS/PPAT </td>";
		$HTML .= "<td class=\"tdheader\"> NO. SERTIFIKAT </td>";
		$HTML .= "<td class=\"tdheader\"> LETAK OP </td>";
		$HTML .= "<td class=\"tdheader\"> KECAMATAN OP </td>";
		$HTML .= "<td class=\"tdheader\"> LUAS TANAH </td>";
		$HTML .= "<td class=\"tdheader\"> NJOP TANAH</td>";
		$HTML .= "<td class=\"tdheader\"> NJOP PER/METER TANAH</td>";
		$HTML .= "<td class=\"tdheader\"> LUAS BANGUNAN </td>";
		$HTML .= "<td class=\"tdheader\"> NJOP BANGUNAN</td>";
		$HTML .= "<td class=\"tdheader\"> NJOP PER/METER BANGUNAN</td>";
		$HTML .= "<td class=\"tdheader\"> BPHTB </td>";
		$HTML .= "<td class=\"tdheader\"> TRANSAKSI </td>";
		$HTML .= "<td class=\"tdheader\"> KET. </td>";
		// $HTML .= "<td class=\"tdheader\">Alamat Objek Pajak</td><td class=\"tdheader\" width=\"170\">User</td><td class=\"tdheader\" width=\"170\">BPHTB yang harus dibayar</td>";
		// $HTML .= "<td class=\"tdheader\" width=\"170\">Tanggal Pembayaran</td>";
		if ($this->getConfigValue($a, 'TYPE_PROSES') == '1') $HTML .= "<td class=\"tdheader\" width=\"170\">Disetujui</td>";
		$HTML .= "</tr>";

		if ($this->getDocument($sts, $dt)) {
			$HTML .= $dt;
		} else {
			$HTML .= "<tr><td colspan=\"3\">Pada Tanggal " . $srcTxt . " Data Kosong !</td></tr> ";
		}
		$HTML .= "</table>";
		$HTML .= "</div>";
		return $HTML;
	}

	function getContent()
	{
		$HTML = "";
		$HTML = $this->headerContentAll($this->status);

		return $HTML;
	}

	function showData()
	{
		echo "<script>
				$(function(){
                    $( '#tanggal' ).datepicker({ dateFormat: 'yy-mm-dd'});
					$( '#tanggal2' ).datepicker({ dateFormat: 'yy-mm-dd'});
                });
              </script>";
		echo "<div id=\"notaris-main-content\">";
		echo "<div id=\"notaris-main-content-inner\">";
		echo $this->getContent();
		echo "</div>";
		// echo "<div id=\"notaris-main-content-footer\" align=right> ";
		// echo $this->paging();
		// echo "</div>";

	}
}

$page = @isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;

$modBPN = new  modBPN(1, $data->uname);

$pages =  $modBPN->getConfigValue("aBPHTB", "ITEM_PER_PAGE");
$modBPN->setDataPerPage($pages);

$modBPN->setDefaultPage($page);

?>

<div id="tabsContent">
	<?php echo $modBPN->showData(); ?>
</div>