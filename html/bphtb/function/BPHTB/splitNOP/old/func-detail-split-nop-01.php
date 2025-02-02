<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'splitNOP', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/uuid.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<script src=\"inc/js/jquery-1.3.2.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>";
echo "<script src=\"function/BPHTB/splitNOP/func-split-nop.js?ver=2\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\" type=\"text/css\">\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname = '".$data->uname."';</script>";

function getConfigValue ($id,$key) {
	global $appDbLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
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

function getConfigure ($appID) {
  $config = array();
  $a=$appID;
  $config['TENGGAT_WAKTU'] = getConfigValue($a,'TENGGAT_WAKTU');
  $config['NPOPTKP_STANDAR'] = getConfigValue($a,'NPOPTKP_STANDAR');
  $config['NPOPTKP_WARIS'] = getConfigValue($a,'NPOPTKP_WARIS');
  $config['TARIF_BPHTB'] = getConfigValue($a,'TARIF_BPHTB');
  $config['PRINT_SSPD_BPHTB'] = getConfigValue($a,'PRINT_SSPD_BPHTB');
  $config['NAMA_DINAS'] = getConfigValue($a,'NAMA_DINAS');
  $config['ALAMAT'] = getConfigValue($a,'ALAMAT');
  $config['NAMA_DAERAH'] = getConfigValue($a,'NAMA_DAERAH');
  $config['KODE_POS'] = getConfigValue($a,'KODE_POS');
  $config['NO_TELEPON'] = getConfigValue($a,'NO_TELEPON');
  $config['NO_FAX'] = getConfigValue($a,'NO_FAX');
  $config['EMAIL'] = getConfigValue($a,'EMAIL');
  $config['WEBSITE'] = getConfigValue($a,'WEBSITE');
  $config['KODE_DAERAH'] = getConfigValue($a,'KODE_DAERAH');
  $config['KEPALA_DINAS'] = getConfigValue($a,'KEPALA_DINAS');
  $config['NAMA_JABATAN'] = getConfigValue($a,'NAMA_JABATAN');
  $config['NIP'] = getConfigValue($a,'NIP');
  $config['NAMA_PJB_PENGESAH'] = getConfigValue($a,'NAMA_PJB_PENGESAH');
  $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a,'JABATAN_PJB_PENGESAH');
  $config['NIP_PJB_PENGESAH'] = getConfigValue($a,'NIP_PJB_PENGESAH');
  return $config;
}

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
               $json.="'$field_names[$y]' :	'".addslashes($row[$y])."'";
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

function getSelectedData($id,$sts=1,&$dt) {
	global $appDbLink;
	$maxtime = '00:20:00';
//	$query = "SELECT * FROM cppmod_ssb_doc A, cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID AND B.CPM_TRAN_STATUS=0 AND A.CPM_SSB_ID='".$id."'";
	
	$query = sprintf("SELECT *,IF((TIMEDIFF(NOW(), B.CPM_TRAN_CLAIM_DATETIME))<'%s','1','0') AS CLAIM 
					FROM cppmod_ssb_doc A,cppmod_ssb_tranmain B WHERE A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
					AND B.CPM_TRAN_STATUS=%s AND B.CPM_TRAN_FLAG=0 AND A.CPM_SSB_ID='%s'",
					$maxtime,$sts,mysqli_real_escape_string($appDbLink, $id));

	$res = mysqli_query($appDbLink, $query);
	if ( $res === false ){
		print_r($query);
		return false; 
	}
	
	$json = new Services_JSON();
	$dt =  $json->decode(mysql2json($res,"data"));
	
	for ($i=0;$i<count($dt->data);$i++) {
		if ($dt->data[$i]->CLAIM!='1'){
			$query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_CLAIM='%s', CPM_TRAN_CLAIM_DATETIME='%s' 
				WHERE CPM_TRAN_SSB_ID='%s'","1",date('Y-m-d H:i:s'),mysqli_real_escape_string($appDbLink, $id));
			$result = mysqli_query($appDbLink, $query);
			if ( $res === false ){
				return false; 
			}
		}
		$query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_READ=1 
					WHERE CPM_TRAN_SSB_ID='%s'",mysqli_real_escape_string($id));
		$result = mysqli_query($appDbLink, $query);
		if ( $res === false ){
			echo "3:".$query;
		} 
	}
	
	

	return true;	
}


function getNOKTP ($noktp,$nop) {
	global $appDbLink;

	$day = getConfigValue("1","BATAS_HARI_NPOPTKP");
	$qry = "select max(CPM_SSB_CREATED) as mx from cppmod_ssb_doc  where 
	CPM_WP_NOKTP = '{$noktp}' and (DATE_ADD(DATE(CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
	and CPM_OP_NOMOR <> '{$nop}'";
	
	$res = mysqli_query($appDbLink, $qry);
	if ( $res === false ){
		print_r($qry);
		return false;
	}
	
	if (mysqli_num_rows ($res)) {
		$num_rows = mysqli_num_rows($res);
		while($row = mysqli_fetch_assoc($res)){
			if($row["mx"]) {
				
				return true;
			}
		}
		
	}

	
	return false;
}	
function formSSB ($dat,$edit) {
	global $data;
	$a = strval($dat->CPM_OP_LUAS_BANGUN)*strval($dat->CPM_OP_NJOP_BANGUN)+strval($dat->CPM_OP_LUAS_TANAH)*strval($dat->CPM_OP_NJOP_TANAH);
	$b = strval($dat->CPM_OP_HARGA);
	$npop = 0;
	$type = $dat->CPM_PAYMENT_TIPE;
	$sel = $dat->CPM_PAYMENT_TIPE_SURAT;
	$sel_min = $dat->CPM_PAYMENT_TIPE_PENGURANGAN;
	$info = $dat->CPM_PAYMENT_TIPE_OTHER;
	$typeR = $dat->CPM_OP_JENIS_HAK;
	$NPOPTKP =  getConfigValue("1",'NPOPTKP_STANDAR');
	
	//echo $typeR."<br>".$NPOPTKP;
	
	if (($typeR==4) || ($typeR==6)){
		$NPOPTKP =  getConfigValue("1",'NPOPTKP_WARIS');
	} else {
		
	}
	if(getNOKTP($dat->CPM_WP_NOKTP,$dat->CPM_OP_NOMOR)) {
			$NPOPTKP = 0;
	}

	$sel1 = "";
	$sel2 = "";
	$sel3 = "";
	$sel4 = "";
	$sel5 = "";
	$c1="";
	$c2="";
	$c3="";
	$c4="";
	$r1="disabled=\"disabled\"";
	$r2="disabled=\"disabled\"";
	$r3="disabled=\"disabled\"";
	$r4="disabled=\"disabled\"";
	
	if ($sel_min=='1') $sel4 = "selected=\"selected\"";
	if ($sel_min=='2') $sel5 = "selected=\"selected\"";
	
	if ($sel=='1') $sel1 = "selected=\"selected\"";
	if ($sel=='2') $sel2 = "selected=\"selected\"";
	if ($sel=='3') $sel3 = "selected=\"selected\"";
	
	if ($type=='1') {
		$c1 = "checked=\"checked\"";
		$r1 = "";
	}
	if ($type=='2') {
		$c2 = "checked=\"checked\"";
		$r2 = "";
	}
	if ($type=='3') {
		$c3 = "checked=\"checked\"";
		$r3 = "";
	}
	if ($type=='4') {
		$c4 = "checked=\"checked\"";
		$r4 = "";
	}
	
	if ($b < $a) $npop = $a; else $npop = $b;
	
	$readonly="";
	$btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />";
	$btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" /></td>";
	$msgClaim = "";
	if (($dat->CLAIM !='0')&&($dat->CPM_TRAN_OPR_NOTARIS != $data->uname)) {
		$readonly="readonly=\"readonly\"";
		$btnSave = "";
		$btnSaveFinal = "";
		$msgClaim = "<div id=\"msg-claim\">Data ini sedang di akses oleh user lain, mohon tunggu sebentar !</div><br>";
	}
	$vedit = "false";
	if ($edit) $vedit = "true";
	$param = "{\'id\':\'".$dat->CPM_SSB_ID."\',\'draf\':1,\'uname\':\'".$data->uname."\',\'axx\':\'".base64_encode($_REQUEST['a'])."\'}";
	$ppdf = "<div align=\"right\">Print to PDF
			<img src=\"./image/icon/adobeacrobat.png\" width=\"16px\" height=\"16px\" 
			title=\"Dokumen PDF\" onclick=\"printToPDF('$param');\" ></div>";
        
        $dat->CPM_OP_LUAS_TANAH = number_format($dat->CPM_OP_LUAS_TANAH, 0,'','');
        $dat->CPM_OP_NJOP_TANAH = number_format($dat->CPM_OP_NJOP_TANAH, 0,'','');
        $dat->CPM_OP_LUAS_BANGUN = number_format($dat->CPM_OP_LUAS_BANGUN, 0,'','');
        $dat->CPM_OP_NJOP_BANGUN = number_format($dat->CPM_OP_NJOP_BANGUN, 0,'','');
        
	$html = "<script language=\"javascript\">
	var edit = ".$vedit.";
	$(function(){
		$(\"#name2\").mask(\"".getConfigValue('1','PREFIX')."?99999999999999999\");
		$(\"#noktp\").focus(function() {
		  $(\"#noktp\").val(\"3202\");
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
		});
	
	</script>
	<div id=\"main-content\"><form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
		  <table width=\"850\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td colspan=\"2\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong>$ppdf</td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">A</font></strong></td>
			  <td width=\"97%\"><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" value=\"".$dat->CPM_WP_NAMA."\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" ".$readonly." title=\"Nama Wajib Pajak\"/></td>
				   <td colspan=\"2\">&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"".$dat->CPM_WP_NPWP."\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\"  ".$readonly." title=\"NPWP\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"".$dat->CPM_WP_NOKTP."\" onkeypress=\"return nextFocus(this,event)\" size=\"24\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">4.</div></td>
				  <td valign=\"top\">Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Alamat Wajib pajak\" ".$readonly.">".str_replace("<br />","\n",$dat->CPM_WP_ALAMAT)."</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" value=\"".$dat->CPM_WP_KELURAHAN."\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  ".$readonly."  title=\"Kelurahan\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"".$dat->CPM_WP_RT."\" onkeypress=\"return nextFocus(this,event)\"  ".$readonly."  title=\"RT\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"".$dat->CPM_WP_RW."\" onkeypress=\"return nextFocus(this,event)\"  ".$readonly." title=\"RW\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  value=\"".$dat->CPM_WP_KECAMATAN."\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  ".$readonly."  title=\"Kecamatan\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  value=\"".$dat->CPM_WP_KABUPATEN."\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\"  ".$readonly." title=\"Kabupatan/Kota\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"".$dat->CPM_WP_KODEPOS."\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" ".$readonly." title=\"Kode POS\"/></td>
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
				  <td width=\"30%\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"".$dat->CPM_OP_NOMOR."\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\"  readonly=\"readonly\" title=\"NOP PBB\"/></td>
				  <td>Nama WP Lama : </td>
				  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"".$dat->CPM_WP_NAMA_LAMA."\" size=\"35\" maxlength=\"30\" title=\"Nama WP Lama\"/>  
				  </td>
				</tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">2.</div></td>
				  <td valign=\"top\">Lokasi Objek Pajak</td>
				  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\"Lokasi Objek Pajak\" ".$readonly.">".str_replace("<br />","\n",$dat->CPM_OP_LETAK)."</textarea>
				  <td valign=\"top\">Nama WP Sesuai Sertifikat : </td>
				  <td valign=\"top\"><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"".$dat->CPM_WP_NAMA_CERT."\" size=\"35\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/>  
				  </td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" value=\"".$dat->CPM_OP_KELURAHAN."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" ".$readonly." title=\"Kelurahan/Desa\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"".$dat->CPM_OP_RT."\" onKeyPress=\"return nextFocus(this, event)\" ".$readonly." title=\"RT\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"".$dat->CPM_OP_RW."\" onKeyPress=\"return nextFocus(this, event)\" ".$readonly." title=\"RW\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"".$dat->CPM_OP_KECAMATAN."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" ".$readonly." title=\"Kecamatan\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"".$dat->CPM_OP_KABUPATEN."\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" ".$readonly." title=\"Kabupaten/Kota\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"".$dat->CPM_OP_KODEPOS."\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" ".$readonly." title=\"Kode POS\"/></td>
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
			<td width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak  
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"".$dat->CPM_OP_THN_PEROLEH."\" onKeyPress=\"return numbersonly(this, event)\" ".$readonly." title=\"Tahun SPPT PBB\"/></td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</td>
			</tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" value=\"".number_format(strval($dat->CPM_OP_LUAS_TANAH),0,'','')."\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" ".$readonly."  title=\"Luas Tanah\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"".$dat->CPM_OP_NJOP_TANAH."\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" ".$readonly." title=\"NJOP Tanah\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">".number_format(strval($dat->CPM_OP_LUAS_TANAH)*strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',')."</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"".number_format(strval($dat->CPM_OP_LUAS_BANGUN),0,'','')."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" ".$readonly." title=\"Luas Bangunan\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"".$dat->CPM_OP_NJOP_BANGUN."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" ".$readonly." title=\"NJOP Bangunan\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">".number_format(strval($dat->CPM_OP_LUAS_BANGUN)*strval($dat->CPM_OP_NJOP_BANGUN), 0, '.', ',')."</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">".number_format(strval($dat->CPM_OP_LUAS_BANGUN)*strval($dat->CPM_OP_NJOP_BANGUN)+strval($dat->CPM_OP_LUAS_TANAH)*strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',')."</td>
		  </tr>
			  </table>
			  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  <tr>
				  <td><div align=\"right\">14.</div></td>
				  <td>Harga Transaksi</td>
				  <td>Rp. 
					<input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"".$dat->CPM_OP_HARGA."\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\"/></td>
				</tr>
				<tr>
				  <td width=\"14\"><div align=\"right\">15.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
                <tr>
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\"><select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();\">
				    <option value=\"1\" ".($typeR==1?"selected=\"selected\"":"").">Jual Beli</option>
				    <option value=\"2\" ".($typeR==2?"selected=\"selected\"":"").">Tukar Menukar</option>
				    <option value=\"3\" ".($typeR==3?"selected=\"selected\"":"").">Hibah</option>
				    <option value=\"4\" ".($typeR==4?"selected=\"selected\"":"").">Hibah Wasiat Sedarah Satu Derajat</option>
				    <option value=\"5\" ".($typeR==5?"selected=\"selected\"":"").">Hibah Wasiat Non Sedarah Satu Derajat</option>
				    <option value=\"6\" ".($typeR==6?"selected=\"selected\"":"").">Waris</option>
				    <option value=\"7\" ".($typeR==7?"selected=\"selected\"":"").">Pemasukan dalam perseroan/badan hukum lainnya</option>
				    <option value=\"8\" ".($typeR==8?"selected=\"selected\"":"").">Pemisahan hak yang mengakibatkan peralihan</option>
				    <option value=\"9\" ".($typeR==9?"selected=\"selected\"":"").">Penunjukan pembeli dalam lelang</option>
				    <option value=\"10\" ".($typeR==10?"selected=\"selected\"":"").">Pelaksanaan putusan hakim yang mempunyai kekuatan hukum tetap</option>
				    <option value=\"11\" ".($typeR==12?"selected=\"selected\"":"").">Penggabungan usaha</option>
				    <option value=\"12\" ".($typeR==13?"selected=\"selected\"":"").">Peleburan usaha</option>
				    <option value=\"13\" ".($typeR==14?"selected=\"selected\"":"").">Pemekaran usaha</option>
				    <option value=\"14\" ".($typeR==15?"selected=\"selected\"":"").">Hadiah</option>
				    <option value=\"15\" ".($typeR==16?"selected=\"selected\"":"").">Jual beli khusus perolehan hak Rumah Sederhana dan Rumah Susun Sederhana melalui KPR bersubsidi</option>
				    <option value=\"16\" ".($typeR==17?"selected=\"selected\"":"").">Pemberian hak baru sebagai kelanjutan pelepasan hak</option>
				    <option value=\"17\" ".($typeR==18?"selected=\"selected\"":"").">Pemberian hak baru diluar pelepasan hak</option>
			      </select></td>
			    <tr>
				<tr>
				  <td><div align=\"right\">16.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"".$dat->CPM_OP_NMR_SERTIFIKAT."\" onKeyPress=\"return numbersonly(this, event)\"  size=\"50\" maxlength=\"50\" title=\"Nomor Sertifikasi Tanah\"/></td>
				</tr>				
			  </table>
			  </td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">C</font></strong></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\" >".number_format(strval($dat->CPM_SSB_AKUMULASI), 0, '.', ',')."</td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">D</font></strong></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\" align=\"center\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"tNJOP\" align=\"right\">".number_format($npop, 0, '.', ',')."</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"NPOPTKP\" align=\"right\">".number_format($NPOPTKP, 0, '.', ',')."</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\"  align=\"right\">".number_format($npop-strval($NPOPTKP), 0, '.', ',')."</td>
				  </tr>
				  <tr>
					<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</strong></td>
					<td id=\"tBPHTBT\" align=\"right\">".number_format(($npop-strval($NPOPTKP))*0.05, 0, '.', ',')."</td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td align=\"center\" valign=\"middle\"><strong><font size=\"+2\">E</font></strong></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
				  </tr>
				<tr>
				  <td width=\"24\" align=\"center\" valign=\"top\"><p>
					<label>
					  <input type=\"radio\" name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\"  onclick=\"enableE(this,0);\" ".$c1." />
					</label>
					<br />
					<br />
				  </p></td>
				  <td width=\"15\" align=\"right\" valign=\"top\">a.</td>
				  <td width=\"583\" valign=\"top\">Penghitungan Wajib Pajak</td>
				</tr>
                                <tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\" ".$c2." /></td>
				  <td align=\"right\" valign=\"top\">b.</td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" ".$r2.">
					<option ".$sel1." >STPD BPHTB</option>
					<option ".$sel2." >SKPD Kurang Bayar</option>
					<option ".$sel3." >SKPD Kurang Bayar Tambahan</option>
				  </select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"".$dat->CPM_PAYMENT_TIPE_SURAT_NOMOR."\" ".$readonly."\" ".$r2." title=\"Nomor Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"".$dat->CPM_PAYMENT_TIPE_SURAT_TANGGAL."\" ".$readonly."\" ".$r2." title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\" ".$c3." /></td>
				  <td align=\"right\" valign=\"top\">c.</td>
				  <td valign=\"top\">Pengurangan dihitung sendiri menjadi <input type=\"text\" name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" size=\"10\" maxlength=\"10\" value=\"".$dat->CPM_PAYMENT_TIPE_PENGURANGAN."\" ".$readonly."\" ".$r3." title=\"Pengurangan dihitung sendiri menjadi\" onkeyup=\"checkTransaction()\"/> %</td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Berdasakan peraturan KHD No : 
					<input type=\"text\" name=\"jsb-choose-role-number\" id=\"jsb-choose-role-number\" size=\"30\" maxlength=\"30\" value=\"".$dat->CPM_PAYMENT_TIPE_KHD_NOMOR."\" ".$readonly."\" ".$r3." title=\"Peraturan KHD No\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\" ".$c4." /></td>
				  <td align=\"right\" valign=\"top\">d.</td>
				  <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" ".$readonly." ".$r4." title=\"Lain-lain\">".$info."</textarea>
				  <input type=\"hidden\" id=\"ver-doc\" value=\"".$dat->CPM_TRAN_SSB_VERSION."\" name=\"ver-doc\">
				  <input type=\"hidden\" id=\"trsid\" value=\"".$dat->CPM_TRAN_ID."\" name=\"trsid\">
				  </td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : </td>
			</tr>
			<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\" value=\"".$dat->CPM_OP_NPOPTKP."\">
			<td colspan=\"2\" align=\"center\" valign=\"middle\">".$btnSave."
			  &nbsp;&nbsp;&nbsp;".$btnSaveFinal."
			</tr>
		  </table>
		</form></div>";
 	return $html;
}

function validation ($str,&$err) {
 	$OK = true;
	$j = count($str);
	$err="";
	for ($i=0; $i<$j ; $i++) {
		if (($i!=31) && ($i!=32) && ($i!=33) && ($i!=34) && ($i!=35) && ($i!=37) && ($i!=39)) {
			if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
				$err .= $str[$i] ."<br>\n";
				$OK = false;//print_r("1 $i");
			} 
		}
		if ($str[30]==2) {
			if (($i==31) || ($i==32) || ($i==33)) {
				if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
					$err .= $str[$i] ."<br>\n";
					$OK = false;//print_r("2");
				} 
			}
		}
		if ($str[30]==3) {
			if (($i==39)   || ($i==37)){
				if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
					$err .= $str[$i] ."<br>\n";
					$OK = false;//print_r("3");
				} 
			}
		}
		if ($str[30]==4) {
			if ($i==35) {
				if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
					$err .= $str[$i] ."<br>\n";
					$OK = false;
					//print_r("4");
				} 
			}
		}
	}
	
	return $OK;
}

function save ($final,$x) {
	
	global $data,$appDbLink;
	$dat = $data;
	//print_r($_REQUEST);
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
	$data[40] = @isset($_REQUEST['nama-wp-lama'])? $_REQUEST['nama-wp-lama']:"Error: Nama WP Lama tidak boleh dikosongkan!";
        $data[41] = @isset($_REQUEST['nama-wp-cert'])? $_REQUEST['nama-wp-cert']:"Error: Nama WP Sesuai Sertifikat tidak boleh dikosongkan!";
	
	$typeSurat='';
	$typeSuratNomor='';
	$typeSuratTanggal='';
	$typePengurangan='';
	$typeLainnya='';
	$trdate=date("Y-m-d H:i:s"); 
	$opr=$dat->uname;
	$version=$_REQUEST['ver-doc'];
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
	
//	if (validation($data,$err)) {
/*
$data[22] = $_REQUEST['land-area']? $_REQUEST['land-area']:"Error: Luas Tanah tidak boleh dikosongkan!";
	$data[23] = $_REQUEST['land-njop']? $_REQUEST['land-njop']:"Error: NJOP Tanah tidak boleh dikosongkan!";
	$data[24] = $_REQUEST['building-area']? $_REQUEST['building-area']:"Error: Luas Bangunan tidak boleh dikosongkan!";
	$data[25] = $_REQUEST['building-njop']? $_REQUEST['building-njop']:"Error: NJOP Bangunan tidak boleh dikosongkan!";
*/
		$query = sprintf("UPDATE cppmod_ssb_doc SET CPM_KPP ='%s',CPM_KPP_ID ='%s',CPM_WP_NAMA ='%s',CPM_WP_NPWP ='%s',CPM_WP_ALAMAT ='%s',
		CPM_WP_RT='%s',CPM_WP_RW='%s',CPM_WP_KELURAHAN='%s',CPM_WP_KECAMATAN='%s',CPM_WP_KABUPATEN='%s',CPM_WP_KODEPOS='%s',
		CPM_OP_NOMOR='%s',CPM_OP_LETAK='%s',CPM_OP_RT='%s',CPM_OP_RW='%s',CPM_OP_KELURAHAN='%s',CPM_OP_KECAMATAN='%s',CPM_OP_KABUPATEN='%s',
		CPM_OP_KODEPOS='%s',CPM_OP_THN_PEROLEH='%s',CPM_OP_LUAS_TANAH='%s',CPM_OP_LUAS_BANGUN='%s',CPM_OP_NJOP_TANAH='%s',CPM_OP_NJOP_BANGUN='%s',
		CPM_OP_JENIS_HAK='%s',CPM_OP_HARGA='%s',CPM_OP_NMR_SERTIFIKAT='%s',CPM_OP_NPOPTKP='%s',CPM_PAYMENT_TIPE='%s',		
		CPM_PAYMENT_TIPE_SURAT='%s',CPM_PAYMENT_TIPE_SURAT_NOMOR='%s',CPM_PAYMENT_TIPE_SURAT_TANGGAL='%s',CPM_PAYMENT_TIPE_PENGURANGAN='%s',
		CPM_PAYMENT_TIPE_OTHER='%s',CPM_SSB_CREATED='%s',CPM_SSB_AUTHOR='%s',CPM_SSB_VERSION='%s',CPM_SSB_AKUMULASI='%s',CPM_PAYMENT_TIPE_KHD_NOMOR='%s',CPM_WP_NOKTP='%s',CPM_WP_NAMA_LAMA='%s',
                CPM_WP_NAMA_CERT='%s'
		WHERE CPM_SSB_ID ='%s'",
		'','',mysqli_real_escape_string($appDbLink, $data[2]),
		mysqli_real_escape_string($appDbLink, $data[3]),mysqli_real_escape_string($appDbLink, nl2br($data[4])),mysqli_real_escape_string($appDbLink, $data[7]),
		mysqli_real_escape_string($appDbLink, $data[8]),mysqli_real_escape_string($appDbLink, $data[6]),mysqli_real_escape_string($appDbLink, $data[9]),mysqli_real_escape_string($appDbLink, $data[10]),
		mysqli_real_escape_string($appDbLink, $data[11]),mysqli_real_escape_string($appDbLink, $data[12]),mysqli_real_escape_string($appDbLink, nl2br($data[13])),
		mysqli_real_escape_string($appDbLink, $data[16]),mysqli_real_escape_string($appDbLink, $data[17]),mysqli_real_escape_string($appDbLink, $data[15]),mysqli_real_escape_string($appDbLink, $data[18]),
		mysqli_real_escape_string($appDbLink, $data[19]),mysqli_real_escape_string($appDbLink, $data[20]),mysqli_real_escape_string($appDbLink, $data[21]),mysqli_real_escape_string($appDbLink, $data[22]),
		mysqli_real_escape_string($appDbLink, $data[24]),mysqli_real_escape_string($appDbLink, $data[23]),mysqli_real_escape_string($appDbLink, $data[25]),mysqli_real_escape_string($appDbLink, $data[26]),
		mysqli_real_escape_string($appDbLink, $data[27]),mysqli_real_escape_string($appDbLink, $data[28]),mysqli_real_escape_string($appDbLink, $data[29]),mysqli_real_escape_string($appDbLink, $data[30]),
		mysqli_real_escape_string($appDbLink, $typeSurat),mysqli_real_escape_string($appDbLink, $typeSuratNomor),mysqli_real_escape_string($appDbLink, $typeSuratTanggal),mysqli_real_escape_string($appDbLink, $data[39]),
		mysqli_real_escape_string($appDbLink, $typeLainnya),mysqli_real_escape_string($appDbLink, $trdate),mysqli_real_escape_string($appDbLink, $opr),mysqli_real_escape_string($appDbLink, $version),
		mysqli_real_escape_string($appDbLink, $data[36]),mysqli_real_escape_string($appDbLink, $nokhd),mysqli_real_escape_string($appDbLink, $data[38]),mysqli_real_escape_string($appDbLink, $data[40]),mysqli_real_escape_string($appDbLink, $data[41]),mysqli_real_escape_string($appDbLink, $x));
				 
		$result = mysqli_query($appDbLink, $query);
		
		if ( $result === false ){
			 //handle the error here
			echo "Error 1".mysqli_error($appDbLink); 
		}
		
		$query = sprintf("UPDATE cppmod_ssb_tranmain SET CPM_TRAN_STATUS='%s', CPM_TRAN_FLAG='%s', CPM_TRAN_DATE='%s', 
		CPM_TRAN_OPR_NOTARIS='%s' WHERE CPM_TRAN_ID='%s'","1","1",mysqli_real_escape_string($appDbLink, $trdate),
		mysqli_real_escape_string($appDbLink, $opr),mysqli_real_escape_string($appDbLink, $_REQUEST['trsid']));
		$result = mysqli_query($appDbLink, $query);
		
		$idtran = c_uuid();
		$refnum = c_uuid();
		
		$query = sprintf("INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,
		CPM_TRAN_DATE,CPM_TRAN_CLAIM,CPM_TRAN_OPR_NOTARIS,CPM_TRAN_OPR_DISPENDA_1,CPM_TRAN_OPR_DISPENDA_2,CPM_TRAN_INFO) 
		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",$idtran,$refnum,$x,$version,$final,'0',mysqli_real_escape_string($appDbLink, $trdate),
		'',mysqli_real_escape_string($appDbLink, $opr),'','','');	
		$result = mysqli_query($appDbLink, $query);
				
		if ( $result === false ){
			 //handle the error here
			echo "Error 2".mysqli_error($appDbLink); 
		} else {
			echo "Data Berhasil disimpan ...! ";
			$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&n=4";
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

//	} else {
	//	echo "Kesalahan ".$err;
		
		//echo formSSB($data,true);
//	}
}
$sts = @isset($_REQUEST['sts']) ? $_REQUEST['sts'] : "1";

getSelectedData($_REQUEST['idssb'],$sts,$xdata);

$save = @isset($_REQUEST['btn-save'])?$_REQUEST['btn-save']:"";
//print_r($_REQUEST);
if ($save == 'Simpan') {
	
	save (1,$xdata->data[0]->CPM_SSB_ID);
} else if ($save == 'Simpan dan Finalkan') {
	save (2,$xdata->data[0]->CPM_SSB_ID);
} else {
	for ($i=0;$i<count($xdata->data);$i++) {
		if (base64_encode($xdata->data[$i]->CPM_SSB_ID)==base64_encode($_REQUEST['idssb'])){
			echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
			echo formSSB ($xdata->data[$i],true);		
		}
	}
}
?>