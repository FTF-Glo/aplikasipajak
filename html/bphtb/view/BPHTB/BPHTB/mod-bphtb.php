<?php
require_once($sRootPath."inc/payment/prefs-payment.php");
class taxClass {
	
	private function getArea(&$data,$cid) {
		$OK = false;
		$sQry = "SELECT A.* FROM cscmod_tax_area_list A, cscmod_tax_cid_area B WHERE B.CSM_CA_CID='".$cid."' AND A.CSM_AREA_CODE=B.CSM_CA_AREA_CODE";
		$res = mysqli_query($sQry);
		if ($res) {
			$data = $res;
			$OK = true;
		}
		return $OK;
	}
	
	
	private function createBody() {
		$body = "<div id=\"body-tax\" name=\"body-tax\">\n";
		$body .= "\t <table border=\"0\" cellpadding=\"2\" cellspacing=\"1\" width=\"780px\">\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td colspan=\"3\"  style=\"background-color:transparent;\"><font color=\"#999999\"><b>Informasi Wajib Pajak</font><b></td>\n";
		$body .= "\t\t\t <td colspan=\"3\"  style=\"background-color:transparent;\"><font color=\"#999999\"><b>Informasi Pajak<b></font></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td width=\"20%\" style=\"background-color:transparent;\">Nama</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td width=\"35%\" style=\"background-color:transparent;\"><span id='wp-name' name='wp-name'></span></td>\n";
		$body .= "\t\t\t <td width=\"20%\" style=\"background-color:transparent;\">Tagihan</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td width=\"25%\" style=\"background-color:transparent;\" align=\"right\"><span id='info-oriBil' name='info-oriBil'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td  style=\"background-color:transparent;\">Alamat</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-address' name='wp-address'></span></td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Tagihan lain</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\" align=\"right\"><span id='info-miscBill' name='info-miscBill'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kelurahan/Desa</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-kelurahan' name='wp-kelurahan'></span></td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Biaya Denda</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\" align=\"right\"><span id='info-penaltyBill' name='info-penaltyBill'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">RT/RW</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-rtRw' name='wp-rtRw'></span></td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Biaya Admin</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\" align=\"right\"><span id='info-adminFee' name='info-adminFee'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kecamatan</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-kecamatan' name='wp-kecamatan'></span></td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Total Tagihan</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\" align=\"right\"><span id='info-totalAmount' name='info-totalAmount'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kabupaten/Kota</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-kabupaten' name='wp-kabupaten'></span></td>\n";
		$body .= "\t\t\t <td colspan=\"3\" style=\"background-color:transparent;\"><font color=\"#999999\"><b>Rincian Tagihan</b></font></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kode Pos</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='wp-kdPos' name='wp-kdPos'></span></td>\n";
		$body .= "\t\t\t <td colspan=\"3\" rowspan=\"7\" style=\"background-color:transparent;\">".$this->createRincian()."</td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td colspan=\"3\" style=\"background-color:transparent;\"><font color=\"#999999\"><b>Informasi Objek Pajak</b></font></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Letak Tanah/Bangunan</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='infoObj-address' name='infoObj-address'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kelurahan/Desa</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='infoObj-kelurahan' name='infoObj-kelurahan'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kecamatan</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='infoObj-kecamatan' name='infoObj-kecamatan'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">Kabupaten Kota</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$body .= "\t\t\t <td style=\"background-color:transparent;\"><span id='infoObj-kabupaten' name='infoObj-kabupaten'></span></td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t\t <tr>\n";
		$body .= "\t\t\t <td colspan=\"3\" style=\"background-color:transparent;\">&nbsp;</td>\n";
		$body .= "\t\t </tr>\n";
		$body .= "\t </table>";
		$body .= "</div>";
		return $body;
	}
	
	private function createRincian($type=array(),$amount=array()) {
		$rincian = "\t\t\t\t\t\t <table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"100%\">\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td width=\"10%\" style=\"background-color:#cfcfcf\">No</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td width=\"40%\" style=\"background-color:#cfcfcf\">Type</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td width=\"40%\" style=\"background-color:#cfcfcf\">Tagihan</td>";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">1.</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"center\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"type_0\" >".$type[0]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"amount_0\">".$amount[0]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">2.</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"center\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"type_1\">".$type[1]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"amount_1\">".$amount[1]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">3.</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"center\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"type_2\">".$type[2]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"amount_2\">".$amount[2]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">4.</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"center\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"type_3\">".$type[3]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"amount_3\">".$amount[3]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t\t <tr>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">5.</td>";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"center\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"type_4\">".$type[4]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t\t <td align=\"right\">\n";
		$rincian .= "\t\t\t\t\t\t\t\t\t <span id=\"amount_4\">".$amount[4]."</span>\n";
		$rincian .= "\t\t\t\t\t\t\t\t </td>\n";
		$rincian .= "\t\t\t\t\t\t\t </tr>\n";
		$rincian .= "\t\t\t\t\t\t </table>\n";
		return $rincian;
	}
	
	private function createHeader() {
		global $cid,$application;
		$header = "<script language='javascript' src='view/bphtb/mod-bphtb.js'></script>\n"; 
		$header .= "<script language='javascript' src='view/bphtb/date.format.js'></script>\n
					<script language='javascript' src='inc/js/fTextField.js'></script>\n
					<script language=\"javascript\"> var aBon = '".$_REQUEST['a']."' ;</script>\n
		"; 
		$option = "";
		if ($this->getArea($rows,$cid)) {
			while ($row = mysqli_fetch_array($rows)) {
				$option .= "<option value='".$row['CSM_AREA_CODE']."'>".$row['CSM_AREA_NAME']."</option>\n";
			}
		}
		//$header .= "\n<h2> Bea Perolehan Hak atas Tanah dan Bangunan (BPHTB) </h2><br>\n";
		$header .= "<div id=\"header-tax\" name=\"header-tax\">\n\t<form action=\"\" method=\"post\" id=\"inqform\" name=\"inqform\">\n";
		$header .= "\t\t <table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"780px\">\n";
		$header .= "\t\t\t <tr><td colspan=\"4\" style=\"background-color:transparent;\"><b>Inquiry</b></td><tr>\n";
		$header .= "\t\t\t <tr>\n";
		$header .= "\t\t\t\t <td width=\"10%\" style=\"background-color:transparent;\"><input type=\"radio\" name=\"radiogroup\" value=\"0\" id=\"radiogroup0\" /> NOP </td>\n";
		$header .= "\t\t\t\t <td width=\"10%\" style=\"background-color:transparent;\"><input type=\"radio\" name=\"radiogroup\" value=\"1\" id=\"radiogroup1\" /> NPWP </td>\n";
		$header .= "\t\t\t\t <td width=\"10%\" style=\"background-color:transparent;\"><input type=\"text\" name=\"nop_npwp\" id=\"nop_npwp\" maxlength=\"32\" size=\"32\"/></td>\n";
		$header .= "\t\t\t\t <td width=\"25%\" style=\"background-color:transparent;\">Area : <select name=\"area\" id=\"area\"> \n";
		$header .= "\t\t\t\t".$option."\n";
		$header .= "\t\t\t\t\t </select>\n";
		$header .= "\t\t\t\t </td>\n";
		$header .= "\t\t\t\t <td width=\"45%\" style=\"background-color:transparent;\"><input type=\"button\" name=\"inquiry\" value=\"Inquiry\" id=\"inquiry\" onclick=\"sendInquiry('".$application."');\"/></td>\n";
		$header .= "\t\t\t </tr>\n";
		$header .= "\t\t </table>\n";
		$header .= "\t<form>\n";
		$header .= "</div>\n";
		return $header;
	}
	
	private function createFooter() {
		global $cid,$application;
		$option = "";
		$footer = "<div id=\"footer-tax\" name=\"footer-tax\">\n\t<form action=\"\" method=\"post\">\n";
		$footer .= "\t\t <table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"780px\">\n";
		$footer .= "\t\t\t <tr><td colspan=\"10\" style=\"background-color:transparent;\"><b>Pembayaran</b></td><tr>\n";
		$footer .= "\t\t\t <tr>\n";
		$footer .= "\t\t\t\t <td width=\"15%\"style=\"background-color:transparent;\"> Jumlah Pembayaran </td>\n";
		$footer .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$footer .= "\t\t\t\t <td width=\"15%\" style=\"background-color:transparent;\" ><input type=\"text\" name=\"jml-bayar\" value=\"\" id=\"jml-bayar\" readonly=\"readonly\"/></td>\n";
		$footer .= "\t\t\t\t <td width=\"15%\" style=\"background-color:transparent;\">Uang </td>\n";
		$footer .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$footer .= "\t\t\t\t <td width=\"15%\" style=\"background-color:transparent;\" ><input type=\"text\" name=\"jml-uang\" value=\"\" id=\"jml-uang\" onKeyPress=\"return(currencyFormatI(this,'.',event));\" onSelect=\"return(onSelectClearFormat(this, '.'))\" onBlur=\"return(currencyFormatIC(this,'.'))\" disabled=\"disabled\" onkeyup=\"jml();\"/></td>\n";
		$footer .= "\t\t\t\t <td width=\"15%\" style=\"background-color:transparent;\">Kembali </td>\n";
		$footer .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$footer .= "\t\t\t\t <td width=\"15%\"  style=\"background-color:transparent;\"><input type=\"text\" name=\"jml-kembali\" value=\"\" id=\"jml-kembali\" readonly=\"readonly\"/></td>\n";
		$footer .= "\t\t\t\t <td style=\"background-color:transparent;\"><input type=\"button\" name=\"payment\" value=\"Bayar\" id=\"payment\" disabled=\"disabled\" onclick=\"sendPayment('".$application."');\"/></td>\n";
		$footer .= "\t\t\t </tr>\n";
		$footer .= "\t\t </table>\n";
		$footer .= "\t<form>\n";
		$footer .= "</div>\n";
		return $footer;
	}
	
	private function createSummary() {
		$option = "";
		$summary = "<div id=\"summary-tax\" name=\"summary-tax\">\n\t<form action=\"\" method=\"post\">\n";
		$summary .= "\t\t <table border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"480px\">\n";
		$summary .= "\t\t\t <tr><td colspan=\"3\" style=\"background-color:transparent;\"><b>Total Transaksi</b></td><tr>\n";
		$summary .= "\t\t\t <tr>\n";
		$summary .= "\t\t\t\t <td width=\"50%\" style=\"background-color:transparent;\">Total Transaksi Hari Ini </td>\n";
		$summary .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$summary .= "\t\t\t\t <td width=\"50%\"  style=\"background-color:transparent;\" align=\"right\"><span name=\"sum-curTrs\"id=\"sum-curTrs\" /> </span></td>\n";
		$summary .= "\t\t\t </tr>\n";
		$summary .= "\t\t\t\t <td width=\"25%\" style=\"background-color:transparent;\">Total Admin Hari Ini </td>\n";
		$summary .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$summary .= "\t\t\t\t <td width=\"75%\" style=\"background-color:transparent;\" align=\"right\" ><span name=\"sum-admTrs\"id=\"sum-admTrs\" /> </span></td>\n";
		$summary .= "\t\t\t </tr>\n";
		$summary .= "\t\t\t\t <td width=\"25%\" style=\"background-color:transparent;\">Jumlah Transaksi Hari Ini </td>\n";
		$summary .= "\t\t\t\t <td style=\"background-color:transparent;\">:</td>\n";
		$summary .= "\t\t\t\t <td width=\"75%\"  style=\"background-color:transparent;\" align=\"right\"><span name=\"sum-totTrs\"id=\"sum-totTrs\" /> </span></td>\n";
		$summary .= "\t\t </table>\n";
		$summary .= "\t<form>\n";
		$summary .= "</div><br><br>\n";
		return $summary;
	}
	
	public function taxFormdisplay($printername) {
		echo $this->createHeader();
		echo $this->createBody();
		echo $this->createFooter();
		echo $this->createSummary();
		echo "<div id=\"tab-result\"></div>
				<applet name='jZebra' code='jzebra.RawPrintApplet.class' archive='inc/jzebra/jzebra.jar' width='0' height='0'>
					<param name='printer' value='".$printername."'>
					<param name='sleep' value='200'>
				</applet>
			</div>";
	}
}
if ($data) {
	$uid = $data->uid;
	$uname = $data->uname;
	$ppid = $data->ppid;
	$cid = $User->getCIDFromPPID($ppid);
	$config = $User->GetModuleConfig($module);
	$ppinfo = $User->getPPIDInfo($ppid);
	$summaryParam["PPID"]=$data->ppid;
	$summaryParam["TRAN_DT"]=date("Y-m-d");
	$summaryParam=base64_encode($json->encode($summaryParam));

	SCANPayment_Pref_GetAllWithFilter($appDbLink,$data->ppid.".PP.bphtb.PC.print.%",$PPID_setting);
	
	$printername = $PPID_setting[$data->ppid.".PP.bphtb.PC.print.printer"];
	if(!$printername) $printername="Epson Lx-300+";
	
	$bphtb = new taxClass();
	$bphtb->taxFormdisplay($printername);
}

?>
