<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'splitNOP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/uuid.php");
require_once($sRootPath."inc/payment/json.php");
echo "<script src=\"inc/js/jquery-1.3.2.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";
echo "<script src=\"inc/js/jquery.alerts.js\" type=\"text/javascript\"></script>";
echo "<link href=\"inc/js/jquery.alerts.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />";
echo "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>\n";


//print_r($_REQUEST);
error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1); 


function getConfigValue ($key) {
		global $appDbLink;	
		$id = $_REQUEST['a'];
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

function number_pad($number,$n) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

function updateCounter($nop) {
	global $appDbLink;
	
	$qry = "INSERT INTO cppmod_ssb_nop_split (CPM_SSB_SP_NOP, CPM_SSB_SP_NO) VALUES ('".$nop."','1')
	ON DUPLICATE KEY UPDATE CPM_SSB_SP_NO = CPM_SSB_SP_NO + 1;";
	
	$result = mysqli_query($appDbLink, $qry);
}

function getNOPSplit($nop,&$snop) {
	global $appDbLink;
	$nop = str_replace("_", "", $nop);

	if (strlen($nop) == 18) {
		$qry = "SELECT * FROM cppmod_ssb_nop_split WHERE CPM_SSB_SP_NOP ='".$nop."'";
		$result = mysqli_query($appDbLink, $qry);
		while ($row = mysqli_fetch_assoc($result)) {
			updateCounter($nop);
			$snop = number_pad(strval($row["CPM_SSB_SP_NO"])+1,3);
			return true;	
		};
		updateCounter($nop);
		$snop = "001";
		return true;
	}
	return false;
}
	
function formSSB ($val=array()) {
$nopfs = @isset($_REQUEST['nopfs']) ? $_REQUEST['nopfs']:getConfigValue('PREFIX');

$vp= "{'vnop':'','vaddres':'','vkelurahan':'','vrt':''
				,'vrw':'','vkecamatan':'','vkabupaten':'','vzip','',
				,'vryear':'','vzip':'','vwplama':'','vwpcert':''}";
				
$vpar = @isset($_REQUEST['vpar']) ? $_REQUEST['vpar']:$vp;
$splitNOP = "";
if (strlen($nopfs) == 18) getNOPSplit($nopfs,$splitNOP);
if ($vpar) { 
	$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
	$jpar = $json->decode($vpar);
}
				
$html = "<script src=\"function/BPHTB/splitNOP/func-split-nop.js?ver=1\"></script>\n
<script language=\"javascript\">
var edit = false;
$(function(){
	$(\"#name2\").mask(\"".getConfigValue('PREFIX')."?99999999999999\");
	$(\"#noktp\").focus(function() {
	  $(\"#noktp\").val(\"".getConfigValue('PREFIX')."\");
	});
	$(\"#noktp\").keyup(function() {
		var input = $(this),
		text = input.val().replace(/[^./0-9-_\s]/g, \"\");
		if(/_|\s/.test(text)) {
			text = text.replace(/_|\s/g, \"\");
			// logic to notify user of replacement
		}
		input.val(text);
	});
	/*$(\"#certificate-number\").keyup(function() {
		var input = $(this),
		text = input.val().replace(/[^./0-9-_\s]/g, \"\");
		if(/_|\s/.test(text)) {
			text = text.replace(/_|\s/g, \"\");
			// logic to notify user of replacement
		}
		input.val(text);
	});*/
});
</script>
<div id=\"main-content\"><form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"\" >
		  <table width=\"850\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td colspan=\"2\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong><br /><br />
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">A</font></strong></td>
			  <td width=\"97%\"><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\"  onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" title=\"Nama Wajib Pajak\"/></td>
				   <td colspan=\"2\">&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\"  onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\" title=\"NPWP\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" onkeypress=\"return nextFocus(this,event)\" size=\"16\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Lain-lain\"></textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\"  onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">B</font></strong></td>
			  <td><table width=\"780\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">NOP PBB</td>
				  <td width=\"30%\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"".$nopfs."\" onBlur=\"checkNOP(this);\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\" title=\"NOP PBB\" onblur=\"getNOPSplit();\"/>
				  <span name=\"nop-split\" id=\"nop-split\"/>".$splitNOP."</span></td>
				  <td>Nama WP Lama : </td>
				  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" size=\"35\" maxlength=\"30\" title=\"Nama WP Lama\" value=\"{$jpar->vwplama}\"/>
				  </td>
				</tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">2.</div></td>
				  <td valign=\"top\">Lokasi Objek Pajak</td>
				  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\"Lain-lain\">{$jpar->vaddres}</textarea>
				  <td valign=\"top\">Nama WP Sesuai Sertifikat : </td>
				  <td valign=\"top\"><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" size=\"35\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\" value=\"{$jpar->vwpcert}\"/>
				  </td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\"  onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\" value=\"{$jpar->vkelurahan}\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\" value=\"{$jpar->vrt}\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\" value=\"{$jpar->vrw}\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\" value=\"{$jpar->vkecamatan}\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\" value=\"{$jpar->vkabupaten}\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\" value=\"{$jpar->vzip}\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table><table width=\"747\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
		  <tr>
			<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></td>
			</tr>
		  <tr>
			<td width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</td>
			<td width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</td>
			<td width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\" value=\"{$jpar->vryear}\"/></td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</td>
			</tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">&nbsp;</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">&nbsp;</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">&nbsp;</td>
		  </tr>
			  </table>
			  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  <tr>
				  <td><div align=\"right\">14.</div></td>
				  <td>Harga Transaksi</td>
				  <td>Rp. 
					<input type=\"text\" name=\"trans-value\" id=\"trans-value\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\"/></td>
				</tr>
				<tr>
				  <td width=\"14\"><div align=\"right\">15.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
                <tr>
				  <td><div align=\"right\">.</div></td>
				  <td colspan=\"2\"><select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();\">
				    <option value=\"1\">Jual Beli</option>
				    <option value=\"2\">Tukar Menukar</option>
				    <option value=\"3\">Hibah</option>
				    <option value=\"4\">Hibah Wasiat Sedarah Satu Derajat</option>
				    <option value=\"5\">Hibah Wasiat Non Sedarah Satu Derajat</option>
				    <option value=\"6\">Waris</option>
				    <option value=\"7\">Pemasukan dalam perseroan/badan hukum lainnya</option>
				    <option value=\"8\">Pemisahan hak yang mengakibatkan peralihan</option>
				    <option value=\"9\">Penunjukan pembeli dalam lelang</option>
				    <option value=\"10\">Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap</option>
				    <option value=\"11\">Penggabungan usaha</option>
				    <option value=\"12\">Peleburan usaha</option>
				    <option value=\"13\">Pemekaran usaha</option>
				    <option value=\"14\">Hadiah</option>
				    <option value=\"15\">Jual beli khusus perolehan hak Rumah Sederhana dan Rumah Susun Sederhana melalui KPR bersubsidi</option>
				    <option value=\"16\">Pemberian hak baru sebagai kelanjutan pelepasan hak</option>
				    <option value=\"17\">Pemberian hak baru diluar pelepasan hak</option>
			      </select></td>
			    <tr>
				<tr>
				  <td><div align=\"right\">16.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" size=\"50\" maxlength=\"50\" title=\"Nomor Sertifikat Tanah\"/></td>
				</tr>				
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
			  <td><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\"></td></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">D</font></strong></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"tNJOP\" align=\"right\">&nbsp;</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"NPOPTKP\" align=\"right\"></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" align=\"right\">&nbsp;</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
					<td id=\"tBPHTBT\" align=\"right\">&nbsp;</td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">E</font></strong></td>
			  <td><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
				  </tr>
				<tr>
				  <td width=\"24\" align=\"center\" valign=\"top\"><p>
					<label>
					  <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\"  onclick=\"enableE(this,0);\"/>
					</label>
					<br />
					<br />
				  </p></td>
				  <td width=\"15\" align=\"right\" valign=\"top\">a.</td>
				  <td width=\"583\" valign=\"top\">Penghitungan Wajib Pajak</td>
				</tr>
                                <tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\"/></td>
				  <td align=\"right\" valign=\"top\">b.</td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\">
					<option value=\"1\">STPD BPHTB</option>
					<option value=\"2\">SKPD Kurang Bayar</option>
					<option value=\"3\">SKPD Kurang Bayar Tambahan</option>
				  </select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" title=\"Nomor Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\"  title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\"/></td>
				  <td align=\"right\" valign=\"top\">c.</td>
				  <td valign=\"top\">Pengurangan dihitung sendiri menjadi <input type=\"text\" name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" size=\"10\" maxlength=\"10\" title=\"Pengurangan dihitung sendiri menjadi\" onkeyup=\"checkTransaction()\"/> %</td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Berdasakan peraturan KHD No : 
					<input type=\"text\" name=\"jsb-choose-role-number\" id=\"jsb-choose-role-number\" size=\"30\" maxlength=\"30\" title=\"Peraturan KHD No\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\"/></td>
				  <td align=\"right\" valign=\"top\">d.</td>
				  <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" title=\"Lain-lain\"></textarea>
				  </td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : </td>
			</tr>
			<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\">
			<input type=\"hidden\" id=\"nop-for-split\" name=\"nop-for-split\" value=\"{$splitNOP}\">
			<td colspan=\"2\" align=\"center\" valign=\"middle\"><input type=\"button\" name=\"btn-save-x\" id=\"btn-save-x\" value=\"Simpan\" onClick = \"submitForm(1);\"/>
			  &nbsp;&nbsp;&nbsp;
			  <input type=\"button\" name=\"btn-save-x\" id=\"btn-save-x\" value=\"Simpan dan Finalkan\" onClick = \"submitForm(2);\"/>
			  <input type=\"hidden\" id=\"btn-save\" name=\"btn-save\"><input type=\"hidden\" id=\"h-nop-split\" name=\"h-nop-split\" value=\"{$splitNOP}\"></td>
			</tr>
			<tr>
			  <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
			</tr>
		  </table>
		</form></div><div id=\"bdy-jalert\"></div>";
 	return $html;
}

function getNOPBPHTB($nop) {
	global $data,$appDbLink;
	$Ok = false;
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
	$dbLimit = getConfigValue('TENGGAT_WAKTU');
	
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
		//payment_flag, mysqli_real_escape_string($payment_flag),
	$query2 = "select * from {$dbTable} where op_nomor ='{$nop}' and DATE_ADD(saved_date,INTERVAL {$dbLimit} day) > CURDATE()";
		
	$r = mysqli_query($DBLinkLookUp, $query2);
	if ( $r === false ){
		$Ok = false;
		setDOCReject($nop);
	}
	if (mysqli_num_rows ($r)) {
		while($row = mysqli_fetch_assoc($r)){
			$Ok = true;
		}
	}
		 
	$qry = "select max(DATE(B.CPM_TRAN_DATE)) AS DT, A.CPM_SSB_ID from cppmod_ssb_doc A, cppmod_ssb_tranmain B where B.CPM_TRAN_SSB_ID = A.CPM_SSB_ID AND A.CPM_OP_NOMOR = '{$nop}' and (B.CPM_TRAN_FLAG <> '5' or B.CPM_TRAN_FLAG <> '4') and DATE_ADD(A.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) > CURDATE() GROUP BY A.CPM_SSB_ID ORDER BY B.CPM_TRAN_DATE DESC LIMIT 1 ";

	$res = mysqli_query($appDbLink, $qry);
	if ( $res === false ){
		$Ok = false;
	}

	if (mysqli_num_rows ($res)) {
		
		$num_rows = mysqli_num_rows($res);
		while($row = mysqli_fetch_assoc($res)){
		 if (!$row["DT"]) {
			 $Ok = false;
		 }
		 else {
			 $Ok=true;
			 $dt = $row["CPM_SSB_ID"];
			 //jika sudah terbayar maka data dengan NOP yang sama bisa dilakukan transaksi kembali
			 $query2 = "select * from {$dbTable} where id_switching = '{$dt}' and payment_flag = 1";
	
			 $rx = mysqli_query($DBLinkLookUp, $query2);
			 if (mysqli_num_rows ($rx)) {
				  $Ok = false;
			 } 
		 }	 	
		}
	}
	
	return $Ok;
}

function getNOKTP ($noktp,$nop="") {
	global $DBLink;

	$N1= getConfigValue('NPOPTKP_STANDAR');
	$N2= getConfigValue('NPOPTKP_WARIS');
	$day = getConfigValue("BATAS_HARI_NPOPTKP");
	$dbLimit = getConfigValue('TENGGAT_WAKTU');
	
	$dbName = getConfigValue('BPHTBDBNAME');
	$dbHost = getConfigValue('BPHTBHOSTPORT');
	$dbPwd = getConfigValue('BPHTBPASSWORD');
	$dbTable = getConfigValue('BPHTBTABLE');
	$dbUser = getConfigValue('BPHTBUSERNAME');
	// Connect to lookup database
	SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
	//payment_flag, mysqli_real_escape_string($payment_flag),
		
	$qry = "select sum(A.CPM_SSB_AKUMULASI) AS mx from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
where A.CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 6 AND A.CPM_OP_JENIS_HAK <> 4";

//AND B.CPM_TRAN_STATUS <> 1
//print_r($qry); 
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		return false;
	}
	
	if (mysqli_num_rows ($res)) {
		$num_rows = mysqli_num_rows($res);
		while($row = mysqli_fetch_assoc($res)){
			if(($row["mx"]) && ($row["mx"] >= $N1)) {
				/*$query2 = "SELECT  * FROM ssb WHERE op_nomor = '{$nop}'  and wp_noktp ='{$noktp}' 
				and payment_flag = 0 and ADDDATE(DATE(saved_date),INTERVAL 7 day) < CURDATE()";*/
				
				$query2 = "SELECT  * FROM ssb WHERE op_nomor = '{$nop}'  and wp_noktp ='{$noktp}' 
				and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
				//print_r ($query2);
				$r = mysqli_query($DBLinkLookUp, $query2);
				if ( $r === false ){
					die("Error Insertxx: ".mysqli_error($DBLinkLookUp));
				}
				//if (mysqli_num_rows ($r)) return false;
				while($row = mysqli_fetch_assoc($r)){
					$d1 = explode(" ",$row['save_date']);
					$d2 = explode("-",$d1[0]);
					$sd = date("Y-m-d",mktime(0,0,0,$d[1],$d2[2]+7,$d2[0]));
					if (date("Y-m-d") > $sd) {
						return false;
					}
				}
				//print_r($row["mx"]);
				return true;
			}
		}
		
	}
	return false;
}

function save ($final) {
	global $data,$appDbLink;
	$dat = $data;

	$nxt = @isset($_REQUEST['next-nop']) ? $_REQUEST['next-nop']:"";
	$nfs = @isset($_REQUEST['nop-for-split']) ? $_REQUEST['nop-for-split']:"";
	$data = array();
	$data[0] = "-";
	$data[1] = "-";
	$data[2] = @isset($_REQUEST['name'])? $_REQUEST['name']:"Error: Nama Wajib Pajak tidak boleh dikosongkan!";
	$data[3] = @isset($_REQUEST['npwp'])? $_REQUEST['npwp']:"Error: NPWP tidak boleh dikosongkan!";
	$data[4] = @isset($_REQUEST['address'])? $_REQUEST['address']:"Error: Alamat tidak boleh dikosongkan!";
	$data[5] = "-";
	$data[6] = @isset($_REQUEST['kelurahan'])? $_REQUEST['kelurahan']:"Error: Kelurahan tidak boleh dikosongkan!"; 
	$data[7] = @isset($_REQUEST['rt'])? $_REQUEST['rt']:"Error: RT tidak boleh dikosongkan!";
	$data[8] = @isset($_REQUEST['rw'])? $_REQUEST['rw']:"Error: RW tidak boleh dikosongkan!";
	$data[9] = @isset($_REQUEST['kecamatan'])? $_REQUEST['kecamatan']:"Error: Kecamatan tidak boleh dikosongkan!";
	$data[10] = @isset($_REQUEST['kabupaten'])? $_REQUEST['kabupaten']:"Error: Kabupaten tidak boleh dikosongkan!";
	$data[11] = @isset($_REQUEST['zip-code'])? $_REQUEST['zip-code']:"Error: Kode POS tidak boleh dikosongkan!";
	$data[12] = @isset($_REQUEST['name2'])? $_REQUEST['name2']:"Error: NOP PBB tidak boleh dikosongkan!";
	$data[12] .= @isset($_REQUEST['h-nop-split'])? $_REQUEST['h-nop-split'] : "Error: NOP PBB tidak boleh dikosongkan!";
	$data[13] = @isset($_REQUEST['address2'])? $_REQUEST['address2']:"Error: Alamat Objek Pajak tidak boleh dikosongkan!";
	$data[14] = "-";
	$data[15] = @isset($_REQUEST['kelurahan2'])? $_REQUEST['kelurahan2']:"Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
	$data[16] = @isset($_REQUEST['rt2'])? $_REQUEST['rt2']:"Error: RT Objek Pajak tidak boleh dikosongkan!";
	$data[17] = @isset($_REQUEST['rw2'])? $_REQUEST['rw2']:"Error: RW Objek Pajak tidak boleh dikosongkan!";
	$data[18] = @isset($_REQUEST['kecamatan2'])? $_REQUEST['kecamatan2']:"Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
	$data[19] = @isset($_REQUEST['kabupaten2'])? $_REQUEST['kabupaten2']:"Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
	$data[20] = @isset($_REQUEST['zip-code2'])? $_REQUEST['zip-code2']:"Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
	$data[21] = @isset($_REQUEST['right-year'])? $_REQUEST['right-year']:"Error: Tahun SPPT PBB tidak boleh dikosongkan!";
	$data[22] = @isset($_REQUEST['land-area'])? $_REQUEST['land-area']:"Error: Luas Tanah tidak boleh dikosongkan!";
	$data[23] = @isset($_REQUEST['land-njop'])? $_REQUEST['land-njop']:"Error: NJOP Tanah tidak boleh dikosongkan!";
	$data[24] = @isset($_REQUEST['building-area'])? $_REQUEST['building-area']:"Error: Luas Bangunan tidak boleh dikosongkan!";
	$data[25] = @isset($_REQUEST['building-njop'])? $_REQUEST['building-njop']:"Error: NJOP Bangunan tidak boleh dikosongkan!";
	$data[26] = @isset($_REQUEST['right-land-build'])? $_REQUEST['right-land-build']:"";
	$data[27] = @isset($_REQUEST['trans-value'])? $_REQUEST['trans-value']:"Error: Harga transasksi tidak boleh dikosongkan!";
	$data[28] = @isset($_REQUEST['certificate-number'])? $_REQUEST['certificate-number']:"Error: Nomor sertifikat tidak boleh dikosongkan!";
	$data[29] = @isset($_REQUEST['hd-npoptkp'])? $_REQUEST['hd-npoptkp']:"NPOPTKP";
	$data[30] = @isset($_REQUEST['RadioGroup1'])? $_REQUEST['RadioGroup1']:"Error: Pilihan Jumlah Setoran tidak dipilih!";
	$data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose']:"Error: Pilihan jenis tidak dipilih!";
	$data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
	$data[33] = @isset($_REQUEST['jsb-choose-date'])? $_REQUEST['jsb-choose-date']:"Error: Tanggal surat tidak boleh dikosongkan!";
	$data[34] = "-";//$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
	$data[35] = @isset($_REQUEST['jsb-etc'])? $_REQUEST['jsb-etc']:"Error: Keterangan lain-lain tidak boleh dikosongkan!";
	$data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before']:"Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
	$data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] :"Error: No Aturan KHD tidak boleh di kosongkan!";
	$data[38] = @isset($_REQUEST['noktp'])? $_REQUEST['noktp']:"Error: Nomor KTP tidak boleh dikosongkan!";
	$data[39] = @isset($_REQUEST['jsb-choose-percent'])? $_REQUEST['jsb-choose-percent']:"Error: persentase tidak boleh dikosongkan!";
	$data[40] = @isset($_REQUEST['nama-wp-lama'])? $_REQUEST['nama-wp-lama']:"Error: Nama WP lama tidak boleh dikosongkan!";
        $data[41] = @isset($_REQUEST['nama-wp-cert'])? $_REQUEST['nama-wp-cert']:"Error: Nama WP Sesuai Sertifikat tidak boleh dikosongkan!";
	
	/*if (($data[29]=="0") || ($data[29]==0)) {
		if (!getNOKTP ($_REQUEST['noktp'])) {
			//print_r($_REQUEST['right-land-build']);
			if (($_REQUEST['right-land-build'] == 6) || ($_REQUEST['right-land-build'] == 4)) {
				$data[29] = getConfigValue('NPOPTKP_WARIS');
			} else {
				$data[29] = getConfigValue('NPOPTKP_STANDAR');
			} 	
		}

		if ((intval($_REQUEST['right-land-build']) == 6) || (intval($_REQUEST['right-land-build']) == 4)) {
			print_r($_REQUEST['right-land-build']);
			$data[29] = getConfigValue('NPOPTKP_WARIS');
		}
	}*/
	
	if (($data[29]=="0") || ($data[29]==0) || ($data[29]=="NPOPTKP")) {
		if (!getNOKTP ($_REQUEST['noktp'],$data[12])) {
			if (($_REQUEST['right-land-build'] == 6) || ($_REQUEST['right-land-build'] == 4)) {
				$data[29] = getConfigValue('NPOPTKP_WARIS');
			} else {
				$data[29] = getConfigValue('NPOPTKP_STANDAR');
			} 	
		}

		if ((intval($_REQUEST['right-land-build']) == 6) || (intval($_REQUEST['right-land-build']) == 4)) {
			$data[29] = getConfigValue('NPOPTKP_WARIS');
		}
	}
	
	$typeSurat='';
	$typeSuratNomor='';
	$typeSuratTanggal='';
	$typePengurangan='';
	$typeLainnya='';
	$trdate=date("Y-m-d H:i:s"); 
	$opr=$dat->uname;
	$version='1.0';
	$nokhd="";
	if ($data[30]==2) {
		$typeSurat=$data[31];
		$typeSuratNomor=$data[32];
		$typeSuratTanggal=$data[33];	
	} else if ($data[30]==3){
		$typePengurangan=$data[34];
		$nokhd=$data[37];
	} else if ($data[30]==4){
		$typeLainnya = $data[35];
	}
	
	$iddoc = c_uuid();
	$refnum = c_uuid();
	$tranid = c_uuid();
		
	// please note %d in the format string, using %s would be meaningless
	$query = sprintf("INSERT INTO cppmod_ssb_doc (
		CPM_SSB_ID,CPM_KPP,
		CPM_KPP_ID,CPM_WP_NAMA,CPM_WP_NPWP,CPM_WP_ALAMAT,CPM_WP_RT,CPM_WP_RW,CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
		CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
		CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
		CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
		CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT) 
		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
		'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
		mysqli_real_escape_string($appDbLink, $iddoc),'','',mysqli_real_escape_string($appDbLink, $data[2]),
		mysqli_real_escape_string($appDbLink, $data[3]),mysqli_real_escape_string($appDbLink, nl2br($data[4])),mysqli_real_escape_string($appDbLink, $data[7]),
		mysqli_real_escape_string($appDbLink, $data[8]),mysqli_real_escape_string($appDbLink, $data[6]),mysqli_real_escape_string($appDbLink, $data[9]),mysqli_real_escape_string($appDbLink, $data[10]),
		mysqli_real_escape_string($appDbLink, $data[11]),mysqli_real_escape_string($appDbLink, $data[12]),mysqli_real_escape_string($appDbLink, nl2br($data[13])),
		mysqli_real_escape_string($appDbLink, $data[16]),mysqli_real_escape_string($appDbLink, $data[17]),mysqli_real_escape_string($appDbLink, $data[15]),mysqli_real_escape_string($appDbLink, $data[18]),
		mysqli_real_escape_string($appDbLink, $data[19]),mysqli_real_escape_string($appDbLink, $data[20]),mysqli_real_escape_string($appDbLink, $data[21]),mysqli_real_escape_string($appDbLink, $data[22]),
		mysqli_real_escape_string($appDbLink, $data[24]),mysqli_real_escape_string($appDbLink, $data[23]),mysqli_real_escape_string($appDbLink, $data[25]),mysqli_real_escape_string($appDbLink, $data[26]),
		mysqli_real_escape_string($appDbLink, $data[27]),mysqli_real_escape_string($appDbLink, $data[28]),mysqli_real_escape_string($appDbLink, $data[29]),mysqli_real_escape_string($appDbLink, $data[30]),
		mysqli_real_escape_string($appDbLink, $typeSurat),mysqli_real_escape_string($appDbLink, $typeSuratNomor),mysqli_real_escape_string($appDbLink, $typeSuratTanggal),mysqli_real_escape_string($appDbLink, $data[39]),
		mysqli_real_escape_string($appDbLink, $typeLainnya),mysqli_real_escape_string($appDbLink, $trdate),mysqli_real_escape_string($appDbLink, $opr),mysqli_real_escape_string($appDbLink, $version),
		mysqli_real_escape_string($appDbLink, $data[36]),mysqli_real_escape_string($appDbLink, $nokhd),mysqli_real_escape_string($appDbLink, $data[38]),mysqli_real_escape_string($appDbLink, $data[40]),mysqli_real_escape_string($appDbLink, $data[41]));
				 
	$result = mysqli_query($appDbLink, $query);
	if ( $result === false ){
		 //handle the error here
		print_r(mysqli_error($appDbLink).$query); 
	}
		
	$query = "INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,CPM_TRAN_DATE,CPM_TRAN_CLAIM,
		CPM_TRAN_OPR_NOTARIS) VALUES
		 ('".$tranid."','".$refnum."','".$iddoc."','".$version."','".$final."','0','".$trdate."','0','".$opr."')";
		
	$result = mysqli_query($appDbLink, $query);
	if ( $result === false ){
		echo mysqli_error($appDbLink); 
	} else {
		//print_r($data);
		echo "Data Berhasil disimpan ...! ";
		$vnop = @isset($_REQUEST['name2'])? $_REQUEST['name2']:"";
		$vaddres = @isset($_REQUEST['address2'])? $_REQUEST['address2']:"";
		$vkelurahan = @isset($_REQUEST['kelurahan2'])? $_REQUEST['kelurahan2']:"";
		$vrt = @isset($_REQUEST['rt2'])? $_REQUEST['rt2']:"";
		$vrw = @isset($_REQUEST['rw2'])? $_REQUEST['rw2']:"";
		$vkecematan = @isset($_REQUEST['kecamatan2'])? $_REQUEST['kecamatan2']:"";
		$vkabupaten = @isset($_REQUEST['kabupaten2'])? $_REQUEST['kabupaten2']:"";
		$vzip = @isset($_REQUEST['zip-code2'])? $_REQUEST['zip-code2']:"";
		$vryear = @isset($_REQUEST['right-year'])? $_REQUEST['right-year']:"";
                $vwplama = @isset($_REQUEST['nama-wp-lama'])? $_REQUEST['nama-wp-lama']:""; 
                $vwpcert = @isset($_REQUEST['nama-wp-cert'])? $_REQUEST['nama-wp-cert']:""; 
		
		$vparams = "{'vnop':'{$vnop}','vaddres':'{$vaddres}','vkelurahan':'{$vkelurahan}','vrt':'{$vrt}'
				,'vrw':'{$vrw}','vkecamatan':'{$vkecematan}','vkabupaten':'{$vkabupaten}','vzip','{$vryear}',
				,'vryear':'{$vryear}','vzip':'{$vzip}','vwplama':'{$vwplama}','vwpcert':'{$vwpcert}'}";
				
		$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
		if ($nxt) $params .= "&n=4&f=".$_REQUEST['f']."&nopfs=".$nfs."&vpar=".$vparams;
				
		//echo $nfs; 
		$address = $_SERVER['HTTP_HOST']."payment/pc/svr/central/main.php?param=".base64_encode($params);
		echo "\n<script language=\"javascript\">\n";
		echo "	function delayer(){\n";
		echo "		window.location = \"./main.php?param=".base64_encode($params)."\"\n";
		echo "	}\n";
		echo "	Ext.onReady(function(){\n";
		echo "		setTimeout('delayer()', 2000);\n";
		echo "	});\n";
		echo "</script>\n";
	}
}

$save = @isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : "";

if ($save == 'Simpan') {
	if (!getNOPBPHTB($_REQUEST['name2'].$_REQUEST['nop-for-split'])) save (1);
	else {
			echo "Maaf data anda tidak bisa ditindaklanjuti, karena sudah ditransaksikan sebelumnya. <br>Silahkan hubungi Administrator (DPPKAD)!";
			$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&n=4";
			$address = $_SERVER['HTTP_HOST']."payment/pc/svr/central/main.php?param=".base64_encode($params);
			echo "\n<script language=\"javascript\">\n";
			echo "	function delayer(){\n";
			echo "		window.location = \"./main.php?param=".base64_encode($params)."\"\n";
			echo "	}\n";
			echo "	Ext.onReady(function(){\n";
			echo "		setTimeout('delayer()', 5000);\n";
			echo "	});\n";
			echo "</script>\n";
	}
} else if ($save == 'Simpan dan Finalkan') {
	if (!getNOPBPHTB($_REQUEST['name2'].$_REQUEST['nop-for-split'])) save (2);
	else {
		
			echo "Maaf data anda tidak bisa ditindaklanjuti, karena sudah ditransaksikan sebelumnya. <br>Silahkan hubungi Administrator (DPPKAD)!";
			$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&n=4";
			$address = $_SERVER['HTTP_HOST']."payment/pc/svr/central/main.php?param=".base64_encode($params);
			echo "\n<script language=\"javascript\">\n";
			echo "	function delayer(){\n";
			echo "		window.location = \"./main.php?param=".base64_encode($params)."\"\n";
			echo "	}\n";
			echo "	Ext.onReady(function(){\n";
			echo "		setTimeout('delayer()', 5000);\n";
			echo "	});\n";
			echo "</script>\n";
	}
} else {
	echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
	echo formSSB();
	
}
?>
