<?php
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'bpn', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/c8583.php");
require_once($sRootPath."inc/payment/comm-central.php");

echo "<script src=\"function/BPHTB/notaris/func-new-ssb.js\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\" type=\"text/css\">\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";

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

function getSelectedData($id,&$dt) {
	global $DBLink;
	$query = sprintf("SELECT * 
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_STATUS=5 AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'",
					mysqli_real_escape_string($DBLink, $id));

	$res = mysqli_query($DBLink, $query);
	if ( $res === false ){
		return false; 
	}
	
	$json = new Services_JSON();
	$dt =  $json->decode(mysql2json($res,"data"));

	return true;	
}

function display ($data) {
	//$this->getData();
	//$data = $this->jsondata->data[0];
	$jenishak= "<span class=\"document-x\">Jual Beli</span>";
	$npop = 0;
	$a = strval($data->CPM_OP_LUAS_BANGUN)*strval($data->CPM_OP_NJOP_BANGUN)+strval($data->CPM_OP_LUAS_TANAH)*strval($data->CPM_OP_NJOP_TANAH);
	$b = strval($data->CPM_OP_HARGA);
	if ($b < $a) $npop = $a; else $npop = $b;
	if ($data->CPM_OP_LUAS_BANGUN==2)$jenishak= "<span class=\"document-x\">Jual Beli Bersubsidi</span>";
	if ($data->CPM_OP_LUAS_BANGUN==3) $jenishak= "<span class=\"document-x\">Tukar Menukar</span>";
	if ($data->CPM_OP_LUAS_BANGUN==4) $jenishak= "<span class=\"document-x\">Hibah</span>";
	if ($data->CPM_OP_LUAS_BANGUN==5) $jenishak= "<span class=\"document-x\">Hibah Wasiat</span>";
	$typepayment = "<span  class=\"document-x\">Penghitungan Wajib Pajak</span>";
	$fieldTambahan = "";
	if ($data->CPM_PAYMENT_TIPE==2) {
		if ($data->CPM_PAYMENT_TIPE_SURAT ==1 ) $typepayment = "<span class=\"document-x\">STPD BPHTB</span>";
		if ($data->CPM_PAYMENT_TIPE_SURAT ==2 ) $typepayment = "<span class=\"document-x\">SKPD Kurang Bayar</span>";
		if ($data->CPM_PAYMENT_TIPE_SURAT ==3 ) $typrpayment = "<span class=\"document-x\">SKPD Kurang Bayar Tambahan</span>";
		$fieldTambahan = "<tr>
			   <td valign=\"top\" class=\"document-x\">Nomor : ".$data->CPM_PAYMENT_TIPE_SURAT_NOMOR."</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Tanggal : ".$data->CPM_PAYMENT_TIPE_SURAT_TANGGAL."</td>
			</tr>
			<tr>
			  <td valign=\"top\" class=\"document-x\">Berdasakan peraturan KHD No : ".$data->CPM_PAYMENT_TIPE_KHD_NOMOR."</td>
			</tr>";
	}
	$infoReject = "";
	
	$html = "<link rel=\"stylesheet\" href=\"./function/BPHTB/dispenda/func-display-dispenda.css\" type=\"text/css\">\n";
	$html .= "<div id=\"main-content\"><form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
	  <table width=\"800\" border=\"0\" cellspacing=\"1\" cellpadding=\"6\">
		<tr>
		  <td colspan=\"2\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>$infoReject</td>
		</tr>
		<tr>
		  <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">A</font></strong></td>
		  <td width=\"97%\"><table width=\"750\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			<tr>
			  <td width=\"3%\"><div align=\"right\">1.</div></td>
			  <td width=\"27%\">Nama Wajib Pajak</td>
			  <td width=\"43%\" class=\"document-x\">".$data->CPM_WP_NAMA."</td>
			   <td width=\"15%\">&nbsp;</td>
			   <td width=\"12%\">&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">2.</div></td>
			  <td>NPWP</td>
			  <td class=\"document-x\">".$data->CPM_WP_NPWP."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">3.</div></td>
			  <td>Alamat Wajib Pajak</td>
			  <td class=\"document-x\">".$data->CPM_WP_ALAMAT."</td>
			  <td></td>
			  <td class=\"document-x\"></td>
			</tr>
			<tr>
			  <td><div align=\"right\">4.</div></td>
			  <td>Kelurahan/Desa</td>
			  <td class=\"document-x\">".$data->CPM_WP_KELURAHAN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">5.</div></td>
			  <td>RT/RW</td>
			  <td class=\"document-x\">".$data->CPM_WP_RT."/".$data->CPM_WP_RW."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">6.</div></td>
			  <td>Kecamatan</td>
			  <td class=\"document-x\">".$data->CPM_WP_KECAMATAN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">7.</div></td>
			  <td>Kabupaten/Kota</td>
			  <td class=\"document-x\">".$data->CPM_WP_KABUPATEN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">8.</div></td>
			  <td>Kode Pos</td>
			  <td class=\"document-x\">".$data->CPM_WP_KODEPOS."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
		  </table></td>
		</tr>
		<tr>
		  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">B</font></strong></td>
		  <td><table width=\"750\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			<tr>
			  <td width=\"3%\"><div align=\"right\">1.</div></td>
			  <td width=\"27%\">NOP PBB</td>
			  <td width=\"43%\" class=\"document-x\">".$data->CPM_OP_NOMOR."</td>
			  <td width=\"15%\">&nbsp;</td>
			   <td width=\"12%\">&nbsp;</td>
			  </tr>
			<tr>
			  <td><div align=\"right\">2.</div></td>
			  <td>Lokasi Objek Pajak</td>
			  <td class=\"document-x\">".$data->CPM_OP_LETAK."</td>
			  <td></td>
			  <td class=\"document-x\"></td>
			</tr>
			<tr>
			  <td><div align=\"right\">3.</div></td>
			  <td>Kelurahan/Desa</td>
			  <td class=\"document-x\">".$data->CPM_OP_KELURAHAN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">4.</div></td>
			  <td>RT/RW</td>
			  <td class=\"document-x\">".$data->CPM_OP_RT."
				/
				".$data->CPM_OP_RW."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">5.</div></td>
			  <td>Kecamatan</td>
			  <td class=\"document-x\">".$data->CPM_OP_KECAMATAN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">6.</div></td>
			  <td>Kabupaten/Kota</td>
			  <td class=\"document-x\">".$data->CPM_OP_KABUPATEN."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
			<tr>
			  <td><div align=\"right\">7.</div></td>
			  <td>Kode Pos</td>
			  <td class=\"document-x\">".$data->CPM_OP_KODEPOS."</td>
			  <td>&nbsp;</td>
			  <td>&nbsp;</td>
			</tr>
		  </table><table width=\"650\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
	  <tr>
		<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></td>
		</tr>
	  <tr>
		<td width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</td>
		<td width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</td>
		<td width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi berdasakan SPPT PBB terjadi perolehan hak tahun 
		  <span class=\"document-x\">".$data->CPM_OP_THN_PEROLEH."</span></td>
		<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</td>
		</tr>
	  <tr>
		<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
		<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
		</tr>
	  <tr>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"document-x\">".$data->CPM_OP_LUAS_TANAH." m²</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"document-x\">".$data->CPM_OP_NJOP_TANAH."</td>
		<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
		<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\" class=\"document-x\">".number_format(strval($data->CPM_OP_LUAS_TANAH)*strval($data->CPM_OP_NJOP_TANAH), 2, '.', ',')."</td>
	  </tr>
	  <tr>
		<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
		<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
		</tr>
	  <tr>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"document-x\">".$data->CPM_OP_LUAS_BANGUN." m²</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"document-x\">".$data->CPM_OP_NJOP_BANGUN."</td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
		<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\" class=\"document-x\">".number_format(strval($data->CPM_OP_LUAS_BANGUN)*strval($data->CPM_OP_NJOP_BANGUN), 2, '.', ',')."</td>
	  </tr>
	  <tr>
		<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
		<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
		<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\" class=\"document-x\">".number_format(strval($data->CPM_OP_LUAS_BANGUN)*strval($data->CPM_OP_NJOP_BANGUN)+strval($data->CPM_OP_LUAS_TANAH)*strval($data->CPM_OP_NJOP_TANAH), 2, '.', ',')."</td>
	  </tr>
		  </table>
		  <table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			<tr>
			  <td width=\"14\"><div align=\"right\">14.</div></td>
			  <td width=\"400\">Jenis perolehan hak atas tanah atau bangunan</td>
			  <td width=\"208\">$jenishak</td>
			</tr>
			<tr>
			  <td><div align=\"right\">15.</div></td>
			  <td>Harga transaksi</td>
			  <td class=\"document-x\">Rp.".$data->CPM_OP_HARGA."</td>
			</tr>
			<tr>
			  <td><div align=\"right\">16.</div></td>
			  <td>Nomor sertifikasi tanah</td>
			  <td class=\"document-x\">".$data->CPM_OP_NMR_SERTIFIKAT."</td>
			</tr>
		  </table></td>
		</tr>
		<tr>
		  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
		  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			<tr>
			  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
			  <td width=\"188\" class=\"document-x\">".$data->CPM_SSB_AKUMULASI."</td>
			</tr>
		  </table></td>
		</tr>
		<tr>
		  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">D</font></strong></td>
		  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  <tr>
				<td width=\"443\"><strong>Penghitungan PBB</strong></td>
				<td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
			  </tr>
			  <tr>
				<td>Nilai Perolehan Objek Pajak (NPOP)</td>
				<td id=\"tNJOP\" class=\"document-x\">".number_format($npop, 2, '.', ',')."</td>
			  </tr>
			  <tr>
				<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
				<td class=\"document-x\">".$data->CPM_OP_NPOPTKP."</td>
			  </tr>
			  <tr>
				<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
				<td id=\"tNPOPKP\" class=\"document-x\">".number_format($npop-strval($data->CPM_OP_NPOPTKP), 2, '.', ',')."</td>
			  </tr>
			  <tr>
				<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
				<td id=\"tBPHTBT\" class=\"document-x\">".number_format(($npop-strval($data->CPM_OP_NPOPTKP))*0.05, 2, '.', ',')."</td>
			  </tr>
			  <tr>
				<td>Pengenaan 50% karena waris/ hibah wasiat/ pemberian hak pengelolaan</td>
				<td id=\"tWasiat\">&nbsp;</td>
			  </tr>
			  <tr>
				<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
				<td id=\"tTotal\" class=\"document-x\">".number_format(($npop-strval($data->CPM_OP_NPOPTKP))*0.05, 2, '.', ',')."</td>
			  </tr>
		  </table></td>
		</tr>
		<tr>
		  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">E</font></strong></td>
		  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			<tr>
			  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan</strong> : $typepayment</td>
			</tr>
			$fieldTambahan
			</table>
		  </td>
		</tr>
	  </table>
	</form></div>";
	return $html;
}

if (@isset($_REQUEST)) {
	if (getSelectedData($_REQUEST['idssb'],$dat))
	echo display ($dat->data[0]);
} else {

};
?>
