<?php 
ini_set("display_errors",1);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'updateNOPTKP', '', dirname(__FILE__)))."/";
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");
//require_once($sRootPath . "function/BPHTB/updateNOPTKP/func-update.php");
require_once($sRootPath . "function/BPHTB/updateNOPTKP/ps_pagination.php");

$pager="";
$per_page=30;
$page = 1;
$perpage = 30;
$totalRows=0;
$defaultPage=0;

function getConfigValue($key) {
	global $appDbLink,$a;
	
	$qry = "select * from central_app_config where CTR_AC_AID = '".$a."' and CTR_AC_KEY = '{$key}'";

	$res = mysqli_query($appDbLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($appDbLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function getConfig(&$DbHost,&$DbUser,&$DbPwd,&$DbName) {
	$DbName = getConfigValue('BPHTBDBNAME');
	$DbHost = getConfigValue('BPHTBHOSTPORT');
	$DbPwd = getConfigValue('BPHTBPASSWORD');
	$DbTable = getConfigValue('BPHTBTABLE');
	$DbUser = getConfigValue('BPHTBUSERNAME');
}

function getDataFromSwitcher() {
	global $appDbLink,$a,$m,$noktp,$nop,$page,$perpage,$totalRows;
	$page = @isset($_REQUEST['p'])?$_REQUEST['p']:0;
	$start = $page * $perpage ;
	$sql = "SELECT * FROM cppmod_ssb_doc WHERE CPM_WP_NOKTP='{$noktp}' or  CPM_OP_NOMOR='{$nop}' ORDER BY  CPM_SSB_CREATED ASC LIMIT ".$start.",".$perpage;

	//$pager = new PS_Pagination($appDbLink, $sql, $per_page, 5, "a={$a}&m={$m}&noktp={$noktp}&nop={$nop}");
	//$pager->setDebug(true);
	//$rs = $pager->paginate();
	$qry = "SELECT COUNT(*) AS TOTALROWS FROM cppmod_ssb_doc WHERE CPM_WP_NOKTP='{$noktp}' or  CPM_OP_NOMOR='{$nop}'";
	$totalRows = getTotalRows($qry)	;			
	$rs = mysqli_query($appDbLink, $sql);
	if(!$rs) die("Error euy ".mysqli_error($appDbLink));
	return $rs;
}

function getConnectionToGateway() {
	getConfig($DbHost,$DbUser,$DbPwd,$DbName);
	
    SCANPayment_ConnectToDB($xLDBLink, $xLDBConn, $DbHost, $DbUser, $DbPwd, $DbName);
	return $xLDBLink;
}

function getDataFromGateway($idsw,&$tgl_setuju,&$tgl_exp,&$bphtb) {
	$conGateway = getConnectionToGateway();
	$sql="SELECT * FROM ssb where id_switching = '{$idsw}'";
//print_r($conGateway);
	$xqu=mysqli_query($conGateway, $sql) or die("#er 02 ->".mysqli_error($conGateway));
	$r=mysqli_fetch_assoc($xqu);
	$tgl_setuju = $r['saved_date'];
	$tgl_exp = $r['expired_date'];
	$bphtb = $r['bphtb_dibayar'];
	//mysql_close($conGateway);
}	

function show_header_update () {
	global $a,$m,$no_ktp,$nop;
	$html = "<script type=\"text/javascript\" src=\"inc/js/jquery-1.3.2.min.js\"></script>";
	$html .= "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>";
	$html .= "<script type=\"text/javascript\" src=\"function/BPHTB/updateNOPTKP/javascript-update.js\"></script>";
	$html .= "<link rel='stylesheet' href='view/BPHTB/updateNOPTKP/mod-update.css' type='text/css'/>";
	$html .= "<form method=\"post\" action=\"main.php?param=".base64_encode("a=$a&m=$m")."\">
			<!--<h3>Form pencarian</h3>-->
			<table class='transparent'>
				<tr>
					<td>NO KTP</td>
					<td><input name=\"noktp\" type=\"text\" id=\"noktp\" autocomplete=\"off\" 
					value=\"{$no_ktp}\" size=\"40\" maxlength=\"40\" /></td>
					<td>NOP</td>
					<td><input name=\"nop\" type=\"text\" id=\"nop\" autocomplete=\"off\" 
					value=\"{$nop}\" size=\"40\" maxlength=\"40\" /></td>
					<td>
					<td>
					<input type=\"Submit\" id=\"submit\" name=\"submit\" value=\"Cari\" /></td>
				</tr>
			</table></form>";
	return $html;
}

function changeFormatDate($str) {
	if(!empty($str)){
		$ts = explode(" ",$str);
		$_ts = explode("-",$ts[0]);
		$str = $_ts[2]."-".$_ts[1]."-".$_ts[0];
	}
	return $str;
}

function form_Update($np,$noptkp,$date,$idsw,$noktp,$nop) {
  global $a,$m;
  $npp = ($np - $noptkp) > 0 ? ($np - $noptkp):0;
  $bpthtb = $npp * 0.05;
  $form  = "<div id=\"show_hide_form_{$idsw}\" class=\"hide_form\">";
  $form .= "<form method=\"post\" action=\"main.php?param=".base64_encode("a=$a&m=$m")."\">";
  $form .= "<table width='100%' border='0' cellspacing='0' cellpadding='1' class=''>";
  $form .= "<tr><td colspan='2'><font size='-4'>&nbsp;</font></td></tr>";
  $form .= "<tr><td width='17%'>&nbsp;&nbsp;Nilai Perolehan OP (NIlai Pajak)</td>";
  $form .= "<td width='83%'>:&nbsp;<input name='f_nilai_pajak' type='text' id='f_nilai_pajak' value=\"{$np}\"/></td></tr>";
  $form .= "<tr><td>&nbsp;&nbsp;NOPTKP</td><td>:&nbsp;<input name='f_noptk' type='text' id='f_noptk' value=\"{$noptkp}\"/></td></tr>";
  $form .= "<tr><td>&nbsp;&nbsp;Nilai Pajak - NOPTKP</td><td>:&nbsp;<input name='f_pengurangan' type='text' id='f_pengurangan' value=\"{$npp}\" />";
  $form .= "<div id=\"show_msg\"></div>";
  $form .= "</td></tr>";
  $form .= "<tr><td>&nbsp;&nbsp;BPHTB (5%)</td><td>:&nbsp;<input name='f_bphtb' type='text' id='f_bphtb' value=\"{$bpthtb}\"/></td></tr>";
  $form .= "<tr><td>&nbsp;</td><td>&nbsp;&nbsp;<input type='submit' name='bt_submit' id='bt_submit' value='Submit' class=\"clsbtn\"/>&nbsp;";
  $form .= "<input name=\"up_no_ktp\" id=\"up_no_ktp\" type=\"hidden\" /> <input name=\"up_nop\" id=\"up_nop\" type=\"hidden\" />";
  $form .= "<input type=\"button\" name=\"btn_default\" id=\"btn_default\" value=\"Default\" class=\"clsbtn\" onclick=\"defaValue('{$idsw}','{$nop}','{$noktp}','{$date}','{$a}')\"/>&nbsp;&nbsp;";
  $form .= "<input type=\"button\" name=\"btn_hide\" id=\"button\" value=\"Close\" class=\"clsbtn\" onclick=\"hideDiv('{$idsw}')\"/></td></tr>";
  $form .= "<tr><td colspan='2'><font size='-4'>&nbsp;</font><input name=\"idsw\" id=\"idsw\" type=\"hidden\" value=\"{$idsw}\"/> </td></tr>";
  $form .= "</table><input name=\"date_sv\" id=\"date_sv\" type=\"hidden\" value=\"{$date}\"/> "; 
  $form .= "</form>";
  $form .= "</div>";
  return $form;
}

function formSSB ($value,$bphtb=0) {
	global $a,$m;
	$idsw = $value['CPM_SSB_ID'];
	$nop  = $value['CPM_OP_NOMOR'];
	$noktp = $value['CPM_WP_NOKTP'];
	$date = $value['CPM_SSB_CREATED'];
	$npp = ($value['CPM_SSB_AKUMULASI'] - $value['CPM_OP_NPOPTKP']) > 0 ? ($value['CPM_SSB_AKUMULASI'] - $value['CPM_OP_NPOPTKP']):0;
	if (!$bphtb) $bphtb = $npp * 0.05;
$html = "
<script language=\"javascript\">
var edit = false;
$(function(){
	//$(\"#name2\").mask(\"9999999999999999999999\");
	$(\"#noktp\").focus(function() {
	  $(\"#noktp\").val(\"3202\");
	});
	$(\"#noktp\").keyup(function() {
		var input = $(this),
		text = input.val().replace(/[^0-9-_\s]/g, \"\");
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
<div id=\"show_hide_form_{$value['CPM_SSB_ID']}\" class=\"hide_form\"><form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"main.php?param=".base64_encode("a=$a&m=$m")."\">
		  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">A</font></strong></td>
			  <td width=\"97%\"><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\" class=\"black_color\">1.</div></td>
				  <td width=\"18%\" class=\"black_color\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" value=\"".$value['CPM_WP_NAMA']."\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" title=\"Nama Wajib Pajak\"  class=\"black_color\"/></td>
				   <td colspan=\"2\">&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">2.</div></td>
				  <td class=\"black_color\">NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"".$value['CPM_WP_NPWP']."\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\" title=\"NPWP\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>`
				  <td><div align=\"right\" class=\"black_color\">3.</div></td>
				  <td class=\"black_color\">Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"".$value['CPM_WP_NOKTP']."\" onkeypress=\"return nextFocus(this,event)\" size=\"16\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">4.</div></td>
				  <td class=\"black_color\">Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Lain-lain\" class=\"black_color\">".$value['CPM_WP_ALAMAT']."</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">5.</div></td>
				  <td class=\"black_color\">Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" value=\"".$value['CPM_WP_KELURAHAN']."\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">6.</div></td>
				  <td class=\"black_color\">RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"".$value['CPM_WP_RT']."\" onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\" class=\"black_color\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"".$value['CPM_WP_RW']."\" onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">7.</div></td>
				  <td class=\"black_color\">Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  value=\"".$value['CPM_WP_KECAMATAN']."\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">8.</div></td>
				  <td class=\"black_color\">Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  value=\"".$value['CPM_WP_KABUPATEN']."\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">9.</div></td>
				  <td class=\"black_color\">Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"".$value['CPM_WP_KODEPOS']."\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">B</font></strong></td>
			  <td><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\" class=\"black_color\">1.</div></td>
				  <td width=\"18%\" class=\"black_color\">NOP PBB</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"".$value['CPM_OP_NOMOR']."\" onKeyPress=\"return nextFocus(this, event)\" size=\"18\" title=\"NOP PBB\" \" class=\"black_color\"/></td>
				  <td colspan=\"2\"></td>
				  </tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">2.</div></td>
				  <td class=\"black_color\">Lokasi Objek Pajak</td>
				  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\Alamat Objek Pajak$\" class=\"black_color\">".$value['CPM_OP_LETAK']."</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">3.</div></td>
				  <td class=\"black_color\">Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" value=\"".$value['CPM_OP_KELURAHAN']."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">4.</div></td>
				  <td class=\"black_color\">RT/RW</td>
				  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"".$value['CPM_OP_RT']."\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\" class=\"black_color\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"".$value['CPM_OP_RW']."\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">5.</div></td>
				  <td class=\"black_color\">Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"".$value['CPM_OP_KECAMATAN']."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">6.</div></td>
				  <td class=\"black_color\">Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"".$value['CPM_OP_KABUPATEN']."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">7.</div></td>
				  <td class=\"black_color\">Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"".$value['CPM_OP_KODEPOS']."\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\" class=\"black_color\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table><table width=\"747\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\">
		  <tr>
			<td colspan=\"5\" class=\"black_color\"><strong>Penghitungan NJOP PBB:</strong></td>
			</tr>
		  <tr>
			<td width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"black_color\">Objek pajak</td>
			<td width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"black_color\">Diisi luas tanah atau bangunan yang haknya diperoleh</td>
			<td width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"black_color\">Diisi
berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"".$value['CPM_OP_THN_PEROLEH']."\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\"/ class=\"black_color\"></td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\" class=\"black_color\">Luas x NJOP PBB /m²</td>
			</tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"black_color\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" class=\"black_color\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" value=\"".$value['CPM_OP_LUAS_TANAH']."\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\" class=\"black_color\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"".$value['CPM_OP_NJOP_TANAH']."\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\" class=\"black_color\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\" class=\"black_color\">".($value['CPM_OP_LUAS_TANAH']*$value['CPM_OP_NJOP_TANAH'])."</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\" class=\"black_color\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\" class=\"black_color\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"".$value['CPM_OP_LUAS_BANGUN']."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\" class=\"black_color\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"".$value['CPM_OP_NJOP_BANGUN']."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\" class=\"black_color\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\" class=\"black_color\">".($value['CPM_OP_LUAS_BANGUN']*$value['CPM_OP_NJOP_BANGUN'])."</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\" class=\"black_color\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\" class=\"black_color\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\" class=\"black_color\">&nbsp;</td>
		  </tr>
			  </table>
			  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  <tr>
				  <td><div align=\"right\" class=\"black_color\">14.</div></td>
				  <td class=\"black_color\">Harga Transaksi</td>
				  <td class=\"black_color\">Rp. 
					<input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"".$value['CPM_OP_HARGA']."\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\" class=\"black_color\"/></td>
				</tr>
				<tr>
				  <td width=\"14\"><div align=\"right\" class=\"black_color\">15.</div></td>
				  <td colspan=\"2\" class=\"black_color\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
                <tr>
				  <td><div align=\"right\" class=\"black_color\">.</div></td>
				  <td colspan=\"2\"><select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();\" class=\"black_color\">
				    <option value=\"1\" ".($value['CPM_OP_JENIS_HAK']=='1'?"selected=\"selected\"":"").">Jual Beli</option>
				    <option value=\"2\" ".($value['CPM_OP_JENIS_HAK']=='2'?"selected=\"selected\"":"").">Tukar Menukar</option>
				    <option value=\"3\" ".($value['CPM_OP_JENIS_HAK']=='3'?"selected=\"selected\"":"").">Hibah</option>
				    <option value=\"4\" ".($value['CPM_OP_JENIS_HAK']=='4'?"selected=\"selected\"":"").">Hibah Wasiat Sedarah Satu Derajat</option>
				    <option value=\"5\" ".($value['CPM_OP_JENIS_HAK']=='5'?"selected=\"selected\"":"").">Hibah Wasiat Non Sedarah Satu Derajat</option>
				    <option value=\"6\" ".($value['CPM_OP_JENIS_HAK']=='6'?"selected=\"selected\"":"").">Waris</option>
				    <option value=\"7\" ".($value['CPM_OP_JENIS_HAK']=='7'?"selected=\"selected\"":"").">Pemasukan dalam perseroan/badan hukum lainnya</option>
				    <option value=\"8\" ".($value['CPM_OP_JENIS_HAK']=='8'?"selected=\"selected\"":"").">Pemisahan hak yang mengakibatkan peralihan</option>
				    <option value=\"9\" ".($value['CPM_OP_JENIS_HAK']=='9'?"selected=\"selected\"":"").">Penunjukan pembeli dalam lelang</option>
				    <option value=\"10\" ".($value['CPM_OP_JENIS_HAK']=='10'?"selected=\"selected\"":"").">Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap</option>
				    <option value=\"11\" ".($value['CPM_OP_JENIS_HAK']=='11'?"selected=\"selected\"":"").">Penggabungan usaha</option>
				    <option value=\"12\" ".($value['CPM_OP_JENIS_HAK']=='12'?"selected=\"selected\"":"").">Peleburan usaha</option>
				    <option value=\"13\" ".($value['CPM_OP_JENIS_HAK']=='13'?"selected=\"selected\"":"").">Pemekaran usaha</option>
				    <option value=\"14\" ".($value['CPM_OP_JENIS_HAK']=='14'?"selected=\"selected\"":"").">Hadiah</option>
				    <option value=\"15\" ".($value['CPM_OP_JENIS_HAK']=='15'?"selected=\"selected\"":"").">Jual beli khusus perolehan hak Rumah Sederhana dan Rumah Susun Sederhana melalui KPR bersubsidi</option>
				    <option value=\"16\" ".($value['CPM_OP_JENIS_HAK']=='16'?"selected=\"selected\"":"").">Pemberian hak baru sebagai kelanjutan pelepasan hak</option>
				    <option value=\"17\" ".($value['CPM_OP_JENIS_HAK']=='17'?"selected=\"selected\"":"").">Pemberian hak baru diluar pelepasan hak</option>
			      </select></td>
			    <tr>
				<tr>
				  <td><div align=\"right\" class=\"black_color\">16.</div></td>
				  <td class=\"black_color\">Nomor Sertifikasi</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"".$value['CPM_OP_NMR_SERTIFIKAT']."\" size=\"30\" maxlength=\"30\" title=\"Nomor Sertifikasi Tanah\" class=\"black_color\"/></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
			  <td><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\" class=\"black_color\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\" class=\"black_color\"></td></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\" class=\"black_color\"><strong><font size=\"+2\">D</font></strong></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\" class=\"black_color\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\" class=\"black_color\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td class=\"black_color\">Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"tNJOP\" align=\"right\" class=\"black_color\"><input name='f_nilai_pajak' type='text' id='f_nilai_pajak' value=\"".$value['CPM_SSB_AKUMULASI']."\" class=\"black_color\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"hitungBPHTB()\"/></td>
				  </tr>
				  <tr>
					<td class=\"black_color\">Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"NPOPTKP\" align=\"right\" class=\"black_color\"><input name='f_noptk' type='text' id='f_noptk' value=\"".$value['CPM_OP_NPOPTKP']."\" class=\"black_color\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"hitungBPHTB()\"/></td>
				  </tr>
				  <tr>
					<td class=\"black_color\">Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" align=\"right\" class=\"black_color\"><input name='f_pengurangan' type='text' id='f_pengurangan' value=\"{$npp}\"  class=\"black_color\" onKeyPress=\"return numbersonly(this, event)\"/></td>
				  </tr>
				  <tr>
					<td class=\"black_color\">Bea Perolehan atas Hak Tanah dan Bangunan yang terutang</td>
					<td id=\"tBPHTBT\" align=\"right\" class=\"black_color\"><input name='f_bphtb' type='text' id='f_bphtb' value=\"{$bphtb}\" class=\"black_color\" onKeyPress=\"return numbersonly(this, event)\"/></td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\" class=\"black_color\"><input type='submit' name='bt_submit' id='bt_submit' value='Submit' class=\"clsbtn\"/>&nbsp;<input type=\"button\" name=\"btn_default\" id=\"btn_default\" value=\"Default\" class=\"clsbtn\" onclick=\"defaValue('{$idsw}','{$nop}','{$noktp}','{$date}','{$a}')\"/>&nbsp;&nbsp;<input type=\"button\" name=\"btn_hide\" id=\"button\" value=\"Close\" class=\"clsbtn\" onclick=\"hideDiv('{$idsw}')\"/><input name=\"idsw\" id=\"idsw\" type=\"hidden\" value=\"{$idsw}\"/><input name=\"nop\" id=\"nop\" type=\"hidden\" value=\"".$value['CPM_OP_NOMOR']."\"/></td>
			</tr>
			
		  </table>
		</form></div>";
 	return $html;
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
	
function paging() {
	global $totalRows,$perpage,$page,$totalRows,$defaultPage;
	
	$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
	$sel = @isset($_REQUEST['n'])?$_REQUEST['n']:1;
	$sts = @isset($_REQUEST['s'])?$_REQUEST['s']:5;
	
	$noktp = @isset($_REQUEST['noktp'])? $_REQUEST['noktp']:""; 
	$nop = @isset($_REQUEST['nop'])? $_REQUEST['nop']:""; 
	
	$html = "<div>";
	
	$page = @isset($_REQUEST['p'])?$_REQUEST['p']:0;

	$row = $page ? ($page * $perpage) +1 : 1;
	$rowlast = $row+$perpage-1;
	$rowlast = $totalRows < $rowlast ? $totalRows : $rowlast;
	$html .= $row." - ".$rowlast. " dari ".$totalRows;
	
	$defaultPage = $page;
	$parl = $params."&n=".$sel."&s=".$sts."&p=".($defaultPage-1)."&noktp={$noktp}&nop={$nop}";
	$paramsl = base64_encode($parl);
	
	$parr = $params."&n=".$sel."&s=".$sts."&p=".($defaultPage+1)."&noktp={$noktp}&nop={$nop}";
	$paramsr = base64_encode($parr);
	//echo $this->defaultPage;
	if($page!=0) $html .= "&nbsp;<a href=\"main.php?param=".$paramsl."\"><span id=\"navigator-left\"></span></a>";
	if ($rowlast < $totalRows ) $html .= "&nbsp;<a href=\"main.php?param=".$paramsr."\"><span id=\"navigator-right\"></span></a>";
	$html .= "</div>";
	return $html;
}	

function show_body_update() {
	global $page,$perpage,$totalRows;
	$form ="";
	$html = "\n<div id=\"table\">\n<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
          <tr>\n
            <td width=\"1%\" scope=\"col\" align=\"center\" class=\"class_header\">No</td>\n
            <td width=\"10%\" scope=\"col\" align=\"center\" class=\"class_header\">No KTP</td>\n
            <td width=\"20%\" scope=\"col\" align=\"center\" class=\"class_header\">Nama WP</td>\n
            <td width=\"6%\" scope=\"col\" align=\"center\" class=\"class_header\">NOP</td>\n
            <td width=\"7%\" scope=\"col\" align=\"center\" class=\"class_header\">BPHTB</td>\n
            <td width=\"7%\" scope=\"col\" align=\"center\" class=\"class_header\">Nilai Pajak</td>\n
            <td width=\"7%\" scope=\"col\" align=\"center\" class=\"class_header\">NOPTKP</td>\n
            <td width=\"5%\" scope=\"col\" align=\"center\" class=\"class_header\">Tgl Lapor</td>\n
            <td width=\"5%\" scope=\"col\" align=\"center\" class=\"class_header\">Tgl Setuju</td>\n
            <td width=\"5%\" scope=\"col\" align=\"center\" class=\"class_header\">Tgl Exp</td>\n
            <td width=\"3%\" scope=\"col\" align=\"center\" class=\"class_header\">Update</td>\n
          </tr>\n";
	
    $data = getDataFromSwitcher();
	$i=0;
	$page =(isset($_REQUEST['p']))?$_REQUEST['p']:0;
	$no=($page*$perpage);
	
	while($row= mysqli_fetch_array($data)) {
		$html .= "<tr valign=\"top\">\n";
		$no++;
		$c_color ="";
		//print_r($row['CPM_OP_NOMOR']."-".$_REQUEST['nop']." <br>");
		//echo ($row['CPM_OP_NOMOR']=='32040310100170222') ? "sama":"";
		getDataFromGateway($row['CPM_SSB_ID'],$tgl_setuju,$tgl_exp,$bphtb);
		if($row['CPM_OP_NOMOR']===$_REQUEST['nop'] ){
			$c_color="red_color";
			$btUp=true;
			$val = array();
			//$form =  form_Update($row['CPM_SSB_AKUMULASI'],$row['CPM_OP_NPOPTKP'],$row['CPM_SSB_CREATED'],$row['CPM_SSB_ID'],$row['CPM_WP_NOKTP'],$row['CPM_OP_NOMOR']);
			$form = formSSB($row,$bphtb);
		}else{ 
			$c_color = $i%2==0 ? "black_color":"grey_bg";
			$btUp=false;
		}
		
		$i++;
		
		
		
		$html .= " <td class={$c_color} align=\"right\">{$no}</td>\n";
		$html .= " <td class={$c_color} align=\"center\">{$row['CPM_WP_NOKTP']}</td>\n";
		$html .= " <td class={$c_color}>{$row['CPM_WP_NAMA']}</td>\n";
		$html .= " <td class={$c_color} align=\"center\">{$row['CPM_OP_NOMOR']}</td>";
		//$html .= " <td class={$c_color} align=\"right\">".number_format($row['CPM_OP_BPHTB_TU'],0,",",".")."</td>\n";
		$html .= " <td class={$c_color} align=\"right\">".number_format($bphtb,0,",",".")."</td>\n";
		$html .= " <td class={$c_color} align=\"right\">".number_format($row['CPM_SSB_AKUMULASI'],0,",",".")."</td>\n";
		$html .= " <td class={$c_color} align=\"right\">".number_format($row['CPM_OP_NPOPTKP'],0,",",".")."</td>\n";
		$html .= " <td class={$c_color} align=\"center\">".changeFormatDate($row['CPM_SSB_CREATED'])."</td>\n";
		
		$ts = (!empty($tgl_setuju)) ?changeFormatDate($tgl_setuju):"____";
		$te = (!empty($tgl_exp))?changeFormatDate($tgl_exp):"____";
		$html .= " <td class={$c_color} align=\"center\">".$ts."</td>\n";
		$html .= " <td class={$c_color} align=\"center\">".$te."</td>\n";
		if($btUp){ 
			$html .= " <td class={$c_color} align=\"center\">
			<input type=\"button\" name=\"bt_update\" id=\"button\" value=\"Update\" onclick=\"showDiv('".$row['CPM_SSB_ID']."')\" class=\"clsbtn\" /></td>\n";
		}else{  
			$html .= " <td class={$c_color}></td>"; 
		}
		$html .="</tr>";
	}
	$html .= "<tr><td colspan='12'  align='center'>";
	//$html .=  $pager->renderFullNav(); 
	$html .=  paging($totalRows);
	$html .=  "</td>\n</tr>\n</table>\n</div>";
	$form .= $html;
	return $form;
}

function prosesUpdate(){
	global $dbSpec,$data,$appDbLink;
	$idsw =  @isset($_REQUEST['idsw'])?$_REQUEST['idsw']:"";
	$f_nilai_pajak = @isset($_REQUEST['f_nilai_pajak']) ? $_REQUEST['f_nilai_pajak'] : "";
	$bphtb =  @isset($_REQUEST['f_bphtb'])?$_REQUEST['f_bphtb']:"";
	$up_noptk =  @isset($_REQUEST['f_noptk'])?$_REQUEST['f_noptk']:"";
	$up_no_ktp  =  @isset($_REQUEST['up_no_ktp'])?$_REQUEST['up_no_ktp']:"";
	$up_nop =  @isset($_REQUEST['up_nop'])?$_REQUEST['up_nop']:"";
	$name = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
	$noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp']: "";
	$npwp = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp']: "";
	$address = @isset($_REQUEST['address']) ?  $_REQUEST['address']:"";
	$kelurahan = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan']:"";
	$rt = @isset($_REQUEST['rt']) ? $_REQUEST['rt'] : "";
	$rw = @isset($_REQUEST['rw']) ? $_REQUEST['rt'] : "";
	$kecamatan = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "";
	$kabupaten = @isset($_REQUEST['kabupaten']) ? $_REQUEST['kabupaten'] : "";
	$kodepos = @isset($_REQUEST['zip-code']) ? $_REQUEST['zip-code'] : "";
	$opnomor =  @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "";
	$alamat_op = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "";
	$rt2 = @isset($_REQUEST['rt2']) ? $_REQUEST['rt2'] : "";
	$rw2 = @isset($_REQUEST['rw2']) ? $_REQUEST['rw2'] : "";
	$kelurahan2 = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "";
	$kecamatan2 = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "";
	$kabupaten2 = @isset($_REQUEST['kabupaten2']) ? $_REQUEST['kabupaten2'] : "";
	$zip_code2 = @isset($_REQUEST['zip-code2']) ? $_REQUEST['zip-code2'] : "";
	$rightYear = @isset($_REQUEST['right-year']) ? $_REQUEST['right-year']: "";
	$landArea = @isset($_REQUEST['land-area']) ? $_REQUEST['land-area']: "";
	$landNjop = @isset($_REQUEST['land-njop']) ? $_REQUEST['land-njop']: "";
	$buildArea = @isset($_REQUEST['building-area']) ? $_REQUEST['building-area'] : "";
	$buildNJOP = @isset($_REQUEST['building-njop']) ? $_REQUEST['building-njop'] : ""; 
	$jnsPerolehan = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
	$sertifikat = @isset($_REQUEST['certificate-number']) ? $_REQUEST['certificate-number'] : "";
	$trdate=date("Y-m-d H:i:s"); 
	$opr=$data->uname;
  	//echo "<pre>"; print_r($req); echo"</pre>";
	
	$query = sprintf("UPDATE cppmod_ssb_doc SET CPM_WP_NAMA ='%s',CPM_WP_NPWP ='%s',CPM_WP_ALAMAT ='%s',
		CPM_WP_RT='%s',CPM_WP_RW='%s',CPM_WP_KELURAHAN='%s',CPM_WP_KECAMATAN='%s',CPM_WP_KABUPATEN='%s',CPM_WP_KODEPOS='%s',
		CPM_OP_NOMOR='%s',CPM_OP_LETAK='%s',CPM_OP_RT='%s',CPM_OP_RW='%s',CPM_OP_KELURAHAN='%s',CPM_OP_KECAMATAN='%s',CPM_OP_KABUPATEN='%s',
		CPM_OP_KODEPOS='%s',CPM_OP_THN_PEROLEH='%s',CPM_OP_LUAS_TANAH='%s',CPM_OP_LUAS_BANGUN='%s',CPM_OP_NJOP_TANAH='%s',CPM_OP_NJOP_BANGUN='%s',
		CPM_OP_JENIS_HAK='%s',CPM_OP_NMR_SERTIFIKAT='%s',CPM_OP_NPOPTKP='%s',
		CPM_SSB_AKUMULASI='%s',
		CPM_OP_BPHTB_TU='%s' 
		WHERE CPM_SSB_ID ='%s'",
		mysqli_real_escape_string($appDbLink, $name),
		mysqli_real_escape_string($appDbLink, $npwp),mysqli_real_escape_string($appDbLink, nl2br($address)),mysqli_real_escape_string($appDbLink, $rt),
		mysqli_real_escape_string($appDbLink, $rw),mysqli_real_escape_string($appDbLink, $kelurahan),mysqli_real_escape_string($appDbLink, $kecamatan),mysqli_real_escape_string($appDbLink, $kabupaten),
		mysqli_real_escape_string($appDbLink, $kodepos),mysqli_real_escape_string($appDbLink, $opnomor),mysqli_real_escape_string($appDbLink, nl2br($alamat_op)),
		mysqli_real_escape_string($appDbLink, $rt2),mysqli_real_escape_string($appDbLink, $rw2),mysqli_real_escape_string($appDbLink, $kelurahan2),mysqli_real_escape_string($appDbLink, $kecamatan2),
		mysqli_real_escape_string($appDbLink, $kabupaten2),mysqli_real_escape_string($appDbLink, $zip_code2),mysqli_real_escape_string($appDbLink, $rightYear),mysqli_real_escape_string($appDbLink, $landArea),
		mysqli_real_escape_string($appDbLink, $buildArea),mysqli_real_escape_string($appDbLink, $landNjop),mysqli_real_escape_string($appDbLink, $buildNJOP),mysqli_real_escape_string($appDbLink, $jnsPerolehan),
		mysqli_real_escape_string($appDbLink, $sertifikat),mysqli_real_escape_string($appDbLink, $up_noptk),
		mysqli_real_escape_string($appDbLink, $f_nilai_pajak),mysqli_real_escape_string($appDbLink, $bphtb),
		mysqli_real_escape_string($appDbLink, $idsw));
	//echo $query;
	//$bOK = $dbSpec->sqlQuery($query, $result);
	$xqu=mysqli_query($appDbLink, $query) or die("#er 01".mysqli_error($appDbLink));	
	//$sqlTampil="UPDATE VSI_SWITCHER_DEVEL.cppmod_ssb_doc SET CPM_OP_BPHTB_TU='{$bphtb}', CPM_OP_NPOPTKP='{$up_noptk}' WHERE CPM_SSB_ID='{$idsw}'";
	//$bOK = $dbSpec->sqlQuery($sqlTampil, $result);
	//$DbHost="192.168.30.2:7306";  $DbUser="root"; $DbPwd="rahasia"; $DbName="bphtb";
	//getConfig($DbHost,$DbUser,$DbPwd,$DbName);
	//SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName);
	
	//$sql="UPDATE bphtb.ssb SET bphtb_dibayar='{$bphtb}' where id_switching = '{$idsw}'";
	$name = mysqli_real_escape_string($conGateway, $name);
	$address = mysqli_real_escape_string($conGateway, nl2br($address));
	$rt = mysqli_real_escape_string($conGateway, $rt);
	$rw = mysqli_real_escape_string($conGateway, $rw);
	$kelurahan = mysqli_real_escape_string($conGateway, $kelurahan);
	$kecamatan = mysqli_real_escape_string($conGateway, $kecamatan);
	$kabupaten = mysqli_real_escape_string($conGateway, $kabupaten);
	$kodepos = mysqli_real_escape_string($conGateway, $kodepos);
	$opnomor = mysqli_real_escape_string($conGateway, $opnomor);
	$letak = mysqli_real_escape_string($conGateway, nl2br($alamat_op));
	$rt2 = mysqli_real_escape_string($conGateway, $rt2);
	$rw2 = mysqli_real_escape_string($conGateway, $rw2);
	$kelurahan2 = mysqli_real_escape_string($conGateway, $kelurahan2);
	$kecamatan2 = mysqli_real_escape_string($conGateway, $kecamatan2);
    $kabupaten2 = mysqli_real_escape_string($conGateway, $kabupaten2);
	$bphtb = mysqli_real_escape_string($conGateway, $bphtb);
	$conGateway = getConnectionToGateway();
	$sql = "UPDATE ssb SET wp_nama = '{$name}', wp_alamat = '{$address}', wp_rt = '{$rt}', wp_rw = '{$rw}', wp_kelurahan= '{$kelurahan}', ";
	$sql .= "wp_kecamatan = '{$kecamatan}', wp_kabupaten = '{$kabupaten}', wp_kodepos = '{$kodepos}', wp_noktp = '{$noktp}', op_letak = '{$letak}',";
	$sql .= "op_rt = '{$rt2}', op_rw = '{$rw2}', op_kelurahan = '{$kelurahan2}', op_kecamatan = '{$kecamatan2}', op_kabupaten = '{$kabupaten2}',";
	$sql .= "bphtb_dibayar = '{$bphtb}' where id_switching = '{$idsw}'";
	$qu=mysqli_query($conGateway, $sql) or die("#er 02".mysqli_error($conGateway));		
	//mysql_close($conGateway);
	//if($bOK and $qu) echo"<b style='color:red;'>Data berhasil di update</b>";
}

if ($data) {
	$a = $_REQUEST['a'];
	$m = $_REQUEST['m'];
	$no_ktp= @isset($_REQUEST['noktp'])?$_REQUEST['noktp']:"";
	$nop = @isset($_REQUEST['nop'])?$_REQUEST['nop']:"";

	$uid = $data->uid;	
	//	
	$bOk = $User->GetModuleInArea($uid, $area, $moduleIds);
	if (!$bOK) {
		return false;
	}
	
	if(@isset($_REQUEST['bt_submit'])){ 
		
		prosesUpdate(); 
	} else {
		
	}
	
	echo show_header_update();
	echo Show_body_update ();

}