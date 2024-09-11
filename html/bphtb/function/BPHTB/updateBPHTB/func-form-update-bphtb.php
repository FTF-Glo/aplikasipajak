<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'updateBPHTB', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/uuid.php");

error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";

echo "<link rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\" type=\"text/css\">\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo '<script type="text/javascript" src="inc/js/json-new.js"></script>';
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script language=\"javascript\">var uname = '".$uname."';</script>";
function getConfigValue ($id,$key) {
	global $DBLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$id = $_REQUEST['a'];
	$qry = "select * from central_app_config where CTR_AC_AID = '".$id."' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ( $res === false ){
		echo $qry ."<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
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

function getSelectedData($id,$dt) {
	global $DBLink;
	
	$query = sprintf("SELECT * FROM cppmod_ssb_doc A inner join cppmod_ssb_tranmain B on A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID 
						WHERE 
						A.CPM_SSB_ID='%s' and
						B.CPM_TRAN_FLAG='0'",
						mysqli_real_escape_string($DbLink, $id));
	//echo $query;
	$res = mysqli_query($DBLink, $query);
	$dt = mysqli_fetch_object($res);
	return true;	
}
function jenishak($js){
	global $DBLink;
	
	$texthtml= "<select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();hidepasar();cekAPHB();\" style=\"height: 30px\">";
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

function aphb($aphb){
	global $DBLink;
	
	$texthtml= " Hamparan <select name=\"pengurangan-aphb\" id=\"pengurangan-aphb\" onchange=\"checkTransLast();\">
				    <option value=\"\">Pilih</option>
				    ";
	$qry = "select * from cppmod_ssb_aphb ORDER BY CPM_APHB_KODE asc";
					//echo $qry;exit;
					$res = mysqli_query($DBLink, $qry);
						if(($aphb!=$data['CPM_APHB'])||($aphb=="")){
								 $selected= "";
							}else{
								$selected= "selected";
							}
						while($data = mysqli_fetch_assoc($res)){
							
							$texthtml .= "<option value=\"".$data['CPM_APHB']."\" ".$selected." >".str_pad($data['CPM_APHB_KODE'],2,"0",STR_PAD_LEFT).":".$data['CPM_APHB']."</option>";
						}
$texthtml .="			      </select>";
return $texthtml;
	
}
function formSSBKB($data) {
	global $data,$uname,$a,$DBLink;
    /* $value = array();
      $errMsg = array();
      $j = count($val);
      $err="";
      for ($i=0; $i<$j ; $i++) {
      if ((substr($val[$i],0,5)=='Error') || ($val[$i]=="") ) {
      $errMsg[$i] = $val[$i];
      $value[$i] = "";
      }  else {
      $errMsg[$i] = "";
      $value[$i] = $val[$i];
      }
      } */
    echo "<script src=\"function/BPHTB/notaris/func-kurang-bayar.js?ver=0\"></script>\n";
	echo "<script language=\"javascript\">var axx='".base64_encode($a)."';</script>\n";
    $idssb = @isset($_REQUEST["idssb"]) ? $_REQUEST["idssb"] : "";
    $idt = @isset($_REQUEST["idtid"]) ? $_REQUEST["idtid"] : "";
	$getdatagw = getSPPTInfo($dat->CPM_SSB_ID);
	$getgwssb = $getdatagw['PAYMENT_FLAG'];
	$bphtb_dibayar = $getdatagw['bphtb_dibayar'];
	// var_dump($getgwssb);die;
   
    
        //$dat =  getDataReject($idssb,$idt);
        //$data = $dat->data[0];
        $typeR = $data->CPM_OP_JENIS_HAK;
        $sel = $data->CPM_PAYMENT_TIPE_SURAT;
        $rej = "1";
        if ($sel == '1')
            $sel1 = "selected=\"selected\"";
        if ($sel == '2')
            $sel2 = "selected=\"selected\"";
        if ($sel == '3')
            $sel3 = "selected=\"selected\"";
        

    if(($typeR==33)||($typeR==7)){
		$APHB = $data->CPM_APHB;
	}
	$nop=$data->CPM_OP_NOMOR ? $data->CPM_OP_NOMOR : getConfigValue('', 'PREFIX');
	$bphtb_before =$data->CPM_KURANG_BAYAR_SEBELUM;
	$pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
	$type = $data->CPM_PAYMENT_TIPE;
	if ($type == '2') {
        $c2 = "checked=\"checked\"";
        $r2 = "";
    }
	if(getConfigValue("1", 'DENDA')=='1'){
		$c_denda="$(\"#denda-value\").val(0);
				$(\"#denda-percent\").val(0);
				$(\"#denda-percent\").focus(function() {
					if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(\"\");
					}
				  
				});
				$(\"#denda-value\").blur(function() {
						if($(\"#denda-value\").val()==0){
						$(\"#denda-value\").val(0);
					}
					  
					});
					
				$(\"#denda-percent\").blur(function() {
						if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(0);
					}
					  
					});
					";
		$kena_denda="<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"".$data->DENDA."\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"".$data->CPM_PERSEN_DENDA."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
		$kena_denda2="";
	}else{
		$c_denda="$(\"#denda-value\").val(0);
					$(\"#denda-percent\").val(0);";
		$kena_denda="";
		$kena_denda2="<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
					  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
	}
	$configAPHB = getConfigValue("1",'CONFIG_APHB');
	$configPengenaan = getConfigValue("1",'CONFIG_PENGENAAN');
	
	($configAPHB=="1") ? $display_aphb= "" : $display_aphb="style=\"display:none\"";
	($configPengenaan=="1") ? $display_pengenaan= "" : $display_pengenaan="style=\"display:none\"";
    $html = "
<script language=\"javascript\">
var edit = false;
var hitungaphb = ".$hitungAPHB.";
var configaphb = ".$configAPHB.";
var configpengenaan = ".$configPengenaan.";
$(function(){
        $(\"#name2\").mask(\"" . str_pad(getConfigValue('', 'PREFIX_NOP') . "?", 19, "9", STR_PAD_RIGHT) . "\");
	$(\"#noktp\").focus(function() {
	  $(\"#noktp\").val(\"" . getConfigValue('', 'PREFIX') . "\");
	});
	".$c_denda."
	if($('#right-land-build').val()==7 || $('#right-land-build').val()==33){
		$('#pengurangan-aphb').removeAttr('disabled');
	}else{
		$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
	}
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
function setForm(d){
		$(\"#name\").val(d.CPM_WP_NAMA);
		$(\"#address\").val(d.CPM_WP_ALAMAT);
		$(\"#rt\").val(d.CPM_WP_RT);
		$(\"#rw\").val(d.CPM_WP_RW);
		//$(\"#WP_PROPINSI\").val(PROV);
		$(\"#kabupaten\").val(\"CIANJUR\");
		$(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
		$(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
	}

function checkDukcapil(){
		var appID	= '".$_REQUEST['a']."';	
		var noKTP 	= $('#noktp').val();
		$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
		$('#loaderCek').show();
		$.ajax({
			type: 'POST',
			data: '&noKTP='+noKTP+'&appID='+appID,
			url: './function/BPHTB/notaris/svcCheckDukcapil.php',
			success: function(res){  
				d=jQuery.parseJSON(res);
				if(d.res==1){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							Ya: function() {
								$(this).dialog( \"close\" );
								setForm(d.dat);
							},
							Tidak: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				} else if(d.res==0){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				}
			}	
		});
	}
</script>
<div id=\"main-content\"><form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
		  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td colspan=\"2\" align=\"center\" style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan<br>(SSPD-BPHTB)</font></strong>
			</tr>
			
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">NOP PBB</td>
				  <td width=\"30%\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $nop . "\" onBlur=\"checkNOP(this);autoNOP(this);\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\" title=\"NOP PBB\" \"/></td>
				  <td>Nama WP Lama : </td>
				  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"" . $data->CPM_WP_NAMA_LAMA . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Lama\"/>
				  </td>			  
				  </tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">2.</div></td>
				  <td valign=\"top\">Lokasi Objek Pajak</td>
				  <td><textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" title=\"Lain-lain\">" . str_replace("<br />", "\n", $data->CPM_OP_LETAK) . "</textarea>
				  <td valign=\"top\">Nama WP Sesuai Sertifikat : </td>
				  <td valign=\"top\"><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"" . $data->CPM_WP_NAMA_CERT . "\" size=\"35\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/></td>				  
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" value=\"" . $data->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $data->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"" . $data->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"" . $data->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $data->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			  
			  </td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
			  <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td>Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $data->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"16\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();checkTransaksi();\"/></td>
				  <input type=\"hidden\" id=\"trsid\" value=\"" . $data->CPM_SSB_ID . "\">
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $data->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\" title=\"NPWP\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td width=\"18%\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" value=\"" . $data->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" title=\"Nama Wajib Pajak\"/></td>
				   <td colspan=\"2\"><input type=\"hidden\" id=\"idssb-lama\" name =\"idssb-lama\" value=\"" . $idssb . "\"></td>
				</tr>
				<tr>
				  <td valign=\"top\"><div align=\"right\">4.</div></td>
				  <td valign=\"top\">Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"35\" rows=\"4\" title=\"Alamat\">" . str_replace("<br />", "\n", $data->CPM_WP_ALAMAT) . "</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" value=\"" . $data->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $data->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  value=\"" . $data->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\" title=\"Kecamatan Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\"  value=\"" . $data->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $data->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			  
				<tr>
				  <td width=\"14\"><div align=\"right\">15.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
               <tr>
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">".jenishak($typeR)."</td>
			    <tr>
				<tr id=\"aphb\" ".$display_aphb.">
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">".aphb($APHB)."</td>
			    <tr>
					
				<tr>
				  <td><div align=\"right\">16.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $data->CPM_OP_NMR_SERTIFIKAT . "\" size=\"30\" maxlength=\"30\" title=\"Nomor Sertifikat Tanah\"/></td>
				</tr>
			  </table>
			  <table width=\"900\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
		  <tr>
			<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></td>
			</tr>
		  <tr>
			<th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
			<th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
			<th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak 
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $data->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\"/></th>
			<th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
			</tr>
			</thead>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"land-area\" id=\"land-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_TANAH), 0, '', '')  . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"" . $data->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"" . number_format(strval($data->CPM_OP_LUAS_BANGUN ), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"" . $data->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		   <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">Harga Transaksi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">14.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">
			<input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $data->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"    onkeyup=\"checkTransaction()\" title=\"Harga Transaksi\"/></td>
		  </tr>
			  </table>
			  </td>
			</tr>
			<tr style=\"display:none\">
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\">" . number_format(strval($data->CPM_SSB_AKUMULASI), 0, '.', ',') . "</td></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"xNJOP\" align=\"right\"><input type=\"text\" name=\"tNPOP\" id=\"tNPOP\"  value=\"" . getBPHTBPayment_all(0) . "\"onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"checkTransaction();\" title=\"Nilai Perolehan Objek Pajak (NPOP)\"/></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"xNPOPTKP\" align=\"right\"><input type=\"text\" name=\"tNPOPTKP\" id=\"tNPOPTKP\"  value=\"" . $data->CPM_OP_NPOPTKP . "\" onKeyPress=\"return numbersonly(this, event)\"  \" title=\"Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)\"/></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" align=\"right\">" . number_format(getBPHTBPayment_all(1), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
					<td id=\"tBPHTBTS\" align=\"right\">" . number_format(getBPHTBPayment_all(2), 0, '.', ',') . "</td>
				  </tr>
				  <tr ".$display_aphb.">
					<td>APHB &nbsp;&nbsp;</td>
					<td id=\"tAPHB\" align=\"right\">" . number_format(getBPHTBPayment_all(4), 0, '.', ',') . "</td>
				  </tr>
				  <tr ".$display_pengenaan.">
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"".$pengenaan."\" readonly=\"readonly\"/>%</td>
					<td id=\"tPengenaan\" align=\"right\">" . number_format(getBPHTBPayment_all(3), 0, '.', ',') . "</td>
				  </tr>
				  ".$kena_denda."".$kena_denda2."
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harusnya dibayar</td>
					<td id=\"harusbayar\" align=\"right\">" . number_format(getBPHTBPayment_all(5), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Pajak yang Dibayar sebelumnya</td>
					<td id=\"xBPHTB_BAYAR\" align=\"right\"><input type=\"text\" name=\"tBPHTB_BAYAR\" id=\"tBPHTB_BAYAR\"  value=\"" . $bphtb_before . "\" onKeyPress=\"return numbersonly(this, event);\" onkeyup=\"checkTransaction();\" title=\"BPHTB sebelumnya\" /></td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan Kurang Bayar</td>
					<td id=\"xBPHTBT\" align=\"right\"><input type=\"text\" name=\"bphtbtu\" id=\"tBPHTBTU\" value=\"" . $data->CPM_KURANG_BAYAR . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"\" title=\"Bea Perolehan atas Hak Tanah dan Bangunan yang terutang\" readonly=\"readonly\"/></td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong></td>
				  </tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" onclick=\"enableE(this,1);\" ".$c2." /></td>
				  <td align=\"right\" valign=\"top\"></td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" " . $r2 . ">
					<!-- <option " . $sel1 . " >STPD BPHTB</option> -->
					<option value=\"".$sel2."\" >SKPD Kurang Bayar</option>
					<option value=\"".$sel3."\" >SKPD Kurang Bayar Tambahan</option>
				  </select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"" . $data->CPM_NO_KURANG_BAYAR . "\" title=\"Nomor Surat Pengurangan\" readonly=\"readonly\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" value=\"" . $data->CPM_PAYMENT_TIPE_SURAT_TANGGAL . "\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . date("d/m/Y") . "\" title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				
			  </table></td>
			</tr>
			
			<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\">
			<input type=\"hidden\" id=\"reject-data\" name =\"reject-data\" value=\"" . $rej . "\">
			<input type=\"hidden\" id=\"ver-doc\" value=\"" . $data->CPM_TRAN_SSB_VERSION . "\" name=\"ver-doc\">
			<input type=\"hidden\" id=\"trsid\" value=\"" . $data->CPM_TRAN_ID . "\" name=\"trsid\">
			<input type=\"hidden\" value=\"" . $data->CPM_OP_ZNT . "\" id=\"znt\" name=\"op-znt\">
			<input type=\"hidden\" value=\"" . $data->CPM_OP_BPHTB_TU . "\" id=\"bphtbtu\" name=\"tBPHTBTU\">
			
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : " . number_format(getBPHTBPayment_all(6), 0, '.', ',') . "</td>
			</tr>";
			
			$arrStatus = array(1=>"Notaris",2=>"Verifikasi",3=>"Persetujuan",5=>"Final");
			$html .= "<tr>
							<td class=\"result-analisis\">&nbsp;</td>
							<td colspan=\"3\" class=\"result-analisis\"><strong>Status Dokumen</strong> : 
							<input type='hidden' name='statusDokumenCurrent' value='".base64_encode($data->CPM_TRAN_STATUS)."'>
							<input type='hidden' name='idDokumen' value='".base64_encode($data->CPM_TRAN_SSB_ID)."'>
							<input type='hidden' name='ver-doc' value='".base64_encode($data->CPM_SSB_VERSION)."'>
							<select name='statusDokumen'>
							<option value=\"99\" selected>--Pilih Status---</option>
							";
							if ($getgwssb!=1) {
								foreach($arrStatus as $val => $status){
									$html .= ($data->CPM_TRAN_STATUS==$val)? "<option value='".$val."' selected>".$status."</option>" :
																				"<option value='".$val."'>".$status."</option>";												
								}
							}
							$html .= "	</select>
							</td>
						</tr>
						<tr>
							<td class=\"result-analisis\">&nbsp;</td>
							<td align=\"right\" class=\"result-analisis\">
								<input type=\"reset\" value=\"Reset Perubahan\">";
								
							if ($getgwssb!=1) {
								$html .= "<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Update Dokumen\">";
							}
							$html .="</td>
						</tr>
			<tr>
				<td colspan=\"2\" style=\"border-radius: 0px 0px 10px 10px;\"></td>
			</tr>
		  </table>
		</form></div>";
    return $html;
}

function formSSB($dat,$edit) {
	global $data,$uname,$a,$DBLink;
	
	echo "<script language=\"javascript\">var axx='".base64_encode($a)."';</script>\n";
	echo "<script src=\"function/BPHTB/updateBPHTB/func-form-update-bphtb.js?v.0.0.0.9\"></script>\n";
	echo "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">";

        $json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);


        $cData = (@isset($_COOKIE['centraldata']) ? $_COOKIE['centraldata'] : '');
        $cookiies=null;
        if (!empty($cData)) {
            $decData = base64_decode($cData);
            if ($decData) {
                $cookiies = $json->decode($decData);
            }
        }
        // var_dump($cookiies->uid);
        $javascript_lunas = "event.preventDefault();alert(`Pelaporan Sudah Terbayar tidak dapat di perbaharui`)";
    if (in_array($cookiies->uid, ['taqya','BONEMA TRI PRASETYO'])) {
        $javascript_lunas = "";
    		# code...
	}	
	$getdatagw = getSPPTInfo($dat->CPM_SSB_ID);
	$getgwssb = $getdatagw['PAYMENT_FLAG'];
	$bphtb_dibayar = $getdatagw['bphtb_dibayar'];

	$a = strval($dat->CPM_OP_LUAS_BANGUN)*strval($dat->CPM_OP_NJOP_BANGUN)+strval($dat->CPM_OP_LUAS_TANAH)*strval($dat->CPM_OP_NJOP_TANAH);
	$b = strval($dat->CPM_OP_HARGA);
	$npop = 0;
	$type = $dat->CPM_PAYMENT_TIPE;
	$sel = $dat->CPM_PAYMENT_TIPE_SURAT;
	$sel_min = $dat->CPM_PAYMENT_TIPE_PENGURANGAN;
	$info = $dat->CPM_PAYMENT_TIPE_OTHER;
	$typeR = $dat->CPM_OP_JENIS_HAK;
	$BPHTB_TU = $dat->CPM_OP_BPHTB_TU;
	//$NPOPTKP =  getConfigValue("1",'NPOPTKP_STANDAR');
	$tAPHB = 0;
	if(($typeR==33)||($typeR==7)){
		$tAPHB = $dat->CPM_APHB;
	}
	if ($typeR==5){
		//$NPOPTKP =  getConfigValue("1",'NPOPTKP_WARIS');
	} else {
		
	}

	if ($bphtb_dibayar < 1) {
		$input_readonly = 'readonly';
	}else{
		$input_readonly = '';
	}

	$NPOPTKP =  strval($dat->CPM_OP_NPOPTKP);
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
	if($sel_min!=0){
		$option_pengurangan="<option value=\"".$dat->CPM_KODE_PENGURANGAN .".".$dat->CPM_PENGURANGAN ."\">Kode ".$dat->CPM_KODE_PENGURANGAN ." : ".$dat->CPM_PENGURANGAN ."%</option>";
	}else{
		$option_pengurangan="<option value=\"0\">0</option>";
	}
	if ($sel_min=='1') $sel4 = "selected=\"selected\"";
	if ($sel_min=='2') $sel5 = "selected=\"selected\"";
	
	if ($sel=='1') $sel1 = "selected=\"selected\"";
	if ($sel=='2') $sel2 = "selected=\"selected\"";
	if ($sel=='3') $sel3 = "selected=\"selected\"";
	$kb = "false";
	if ($type=='1') {
		$c1 = "checked=\"checked\"";
		$r1 = "";
		$kb = "true";
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
	$ccc = getBPHTBPayment($dat->CPM_OP_LUAS_BANGUN, $dat->CPM_OP_NJOP_BANGUN, $dat->CPM_OP_LUAS_TANAH, $dat->CPM_OP_NJOP_TANAH, $dat->CPM_OP_HARGA, $dat->CPM_PAYMENT_TIPE_PENGURANGAN, $dat->CPM_OP_JENIS_HAK, $dat->CPM_OP_NPOPTKP, $dat->CPM_PENGENAAN, 0, 0);
	if ($b < $a) $npop = $a; else $npop = $b;
	$npop = $dat->CPM_SSB_AKUMULASI;
	$readonly="";
	$btnSave = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />";
	$btnSaveFinal = "<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" /></td>";
	$msgClaim = "";
	$pengenaan = getConfigValue("1",'PENGENAAN_HIBAH_WARIS');
	$npopkp=($npop-strval($NPOPTKP));
	if($npopkp<=0){
		$npopkp=0;
	}

	/*
	| --------------------------------------
	| add by d3Di
	| latitude & longitude
	*/ 
	$latitude = 0;
	$longitude = 0;
	if(isset($dat->KOORDINAT) && $dat->KOORDINAT!=''){
		$k = explode(', ', $dat->KOORDINAT);
		if(count($k)==2){
			$latitude = $k[0];
			$longitude = $k[1];
			$latitude = str_replace(',','.',$latitude);
			$longitude = str_replace(',','.',$longitude);
			$dat->KOORDINAT = $latitude . ', ' . $longitude;
		}elseif(count($k)<2){
			$k = explode(',', $dat->KOORDINAT);
			if(count($k)==2){
				$latitude = $k[0];
				$longitude = $k[1];
				$latitude = str_replace(',','.',$latitude);
				$longitude = str_replace(',','.',$longitude);
				$dat->KOORDINAT = $latitude . ', ' . $longitude;
			}
		}
	}
	//	--------------------------------------
// var_dump($dat);die;
	$vedit = "false";
	if ($edit) $vedit = "true";
	$param = "{\'id\':\'".$dat->CPM_SSB_ID."\',\'draf\':1,\'uname\':\'".$uname."\',\'axx\':\'".base64_encode($_REQUEST['a'])."\'}";
	if(getConfigValue("1", 'DENDA')=='1'){
		$c_denda="$(\"#denda-value\").val(0);
				$(\"#denda-percent\").val(0);
				$(\"#denda-percent\").focus(function() {
					if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(\"\");
					}
				  
				});
				$(\"#denda-value\").blur(function() {
						if($(\"#denda-value\").val()==0){
						$(\"#denda-value\").val(0);
					}
					  
					});
					
				$(\"#denda-percent\").blur(function() {
						if($(\"#denda-percent\").val()==0){
						$(\"#denda-percent\").val(0);
					}
					  
					});
					";
		$kena_denda="<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" value=\"".$dat->DENDA."\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" value=\"".$dat->CPM_PERSEN_DENDA."\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
		$kena_denda2="";
	}else{
		$c_denda="$(\"#denda-value\").val(0);
					$(\"#denda-percent\").val(0);";
		$kena_denda="";
		$kena_denda2="<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
					  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
	}
	$hitungAPHB = getConfigValue("aBPHTB",'HITUNG_APHB');
	$configAPHB = getConfigValue("aBPHTB",'CONFIG_APHB');
	$configPengenaan = getConfigValue("aBPHTB",'CONFIG_PENGENAAN');
	
	($configAPHB=="1") ? $display_aphb= "" : $display_aphb="style=\"display:none\"";
	($configPengenaan=="1") ? $display_pengenaan= "" : $display_pengenaan="style=\"display:none\"";
	$ppdf = "<div align=\"right\">Print to PDF
			<img src=\"./image/icon/adobeacrobat.png\" width=\"16px\" height=\"16px\" 
			title=\"Dokumen PDF\" onclick=\"printToPDF('$param');\" ></div>";
	$html = "
	<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">
	<script language=\"javascript\">
	var kb = ".$kb.";
	var edit = ".$vedit.";
	var hitungaphb = ".$hitungAPHB.";
	var configaphb = ".$configAPHB.";
	var configpengenaan = ".$configPengenaan.";
	$(function(){
		$('#loaderCek').hide();
		$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
		var jh=$(\"select#right-land-build option:selected\").val();
		if(jh==33){
			$('#pengurangan-aphb').removeAttr(\"disabled\", \"disabled\");
		}
		
		//$(\"#name2\").mask(\"" . getConfigValue('', 'PREFIX_NOP') . "?99999999999999\");
		$(\"#noktp\").focus(function() {
		  $(\"#noktp\").val(\"" . getConfigValue('', 'PREFIX') . "\");
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
		".$c_denda."
	});
		function setForm(d){
		$(\"#name\").val(d.CPM_WP_NAMA);
		$(\"#address\").val(d.CPM_WP_ALAMAT);
		$(\"#rt\").val(d.CPM_WP_RT);
		$(\"#rw\").val(d.CPM_WP_RW);
		//$(\"#WP_PROPINSI\").val(PROV);
		$(\"#kabupaten\").val(\"CIANJUR\");
		$(\"#kecamatan\").val(d.CPM_WP_KECAMATAN);
		$(\"#kelurahan\").val(d.CPM_WP_KELURAHAN);
	}

	function checkDukcapil(){
		var appID	= '".$_REQUEST['a']."';	
		var noKTP 	= $('#noktp').val();
		
		$('#loaderCek').show();
		$.ajax({
			type: 'POST',
			data: '&noKTP='+noKTP+'&appID='+appID,
			url: './function/BPHTB/notaris/svcCheckDukcapil.php',
			success: function(res){  
				d=jQuery.parseJSON(res);
				if(d.res==1){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							Ya: function() {
								$(this).dialog( \"close\" );
								setForm(d.dat);
							},
							Tidak: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				} else if(d.res==0){
					$('#loaderCek').hide();
					$(\"<div>\"+d.msg+\"</div>\").dialog({
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog( \"close\" );
							}
						}
					});
				}
			}	
		});
	}
	</script>
	<style>
	.myButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffffff), color-stop(1, #f6f6f6));
	background:-moz-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
	background:-webkit-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
	background:-o-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
	background:-ms-linear-gradient(top, #ffffff 5%, #f6f6f6 100%);
	background:linear-gradient(to bottom, #ffffff 5%, #f6f6f6 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f6f6f6',GradientType=0);
	background-color:#ffffff;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:6px;
	border:2px solid #dcdcdc;
	display:inline-block;
	cursor:pointer;
	color:#666666;
	font-family:Arial;
	font-size:11px;
	font-weight:bold;
	padding:6px 6px;
	text-decoration:none;
	text-shadow:0px 1px 0px #ffffff;
	}
	.myButton:hover {
		background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #f6f6f6), color-stop(1, #ffffff));
		background:-moz-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
		background:-webkit-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
		background:-o-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
		background:-ms-linear-gradient(top, #f6f6f6 5%, #ffffff 100%);
		background:linear-gradient(to bottom, #f6f6f6 5%, #ffffff 100%);
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f6f6f6', endColorstr='#ffffff',GradientType=0);
		background-color:#f6f6f6;
	}
	.myButton:active {
		position:relative;
		top:1px;
	}

	}
	</style> ";

	if ($getgwssb != 1) {
		if ($bphtb_dibayar < 1) {
			$onsubmit_action = $javascript_lunas;
		} else {
			$onsubmit_action = 'return checkform()';
		}
	} else {
		$onsubmit_action = 'return checkform()';
	}
	$html .= "<div id=\"main-content\"><form id=\"form-notaris\" name=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"".$onsubmit_action."\">
                    <input type=\"hidden\" name=\"author\" value=\"".$dat->CPM_SSB_AUTHOR."\">
                    <table width=\"850\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
			<tr>
			  <td colspan=\"2\" align=\"center\" style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan<br>(SSPD-BPHTB)</font></strong></td>
			</tr>
			<tr>
			   <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div></td>
			  <td><table width=\"850\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"10\"><div align=\"right\">1.</div></td>
				  <td width=\"200\">NOP PBB</td>
				  <td width=\"220\"><input type=\"text\" name=\"name2\" id=\"name2\" value=\"" . $dat->CPM_OP_NOMOR . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"25\"  " . $readonly . " title=\"NOP PBB\"/></td>
				  <td width=\"100\">Nama WP Lama : </td>
				  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_WP_NAMA_LAMA . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Lama\"/>
				  </td>
				  </tr>
				<tr valign=\"top\">
				  <td><div align=\"right\">2.</div></td>
				  <td>Lokasi Objek Pajak</td>
				  <td>
				  	<textarea  name=\"address2\" id=\"address2\" cols=\"35\" rows=\"4\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" title=\"Lokasi Objek Pajak\" 
					" . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_OP_LETAK) . "</textarea>
					</td>
                                  <td width=\"100\">Nama WP Sesuai Sertifikat: </td>
				  <td><input type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_WP_NAMA_CERT . "\" size=\"30\" maxlength=\"30\" title=\"Nama WP Sesuai Sertifikat\"/>
				  
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan2\" id=\"kelurahan2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $dat->CPM_OP_KELURAHAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kelurahan/Desa\"/></td>
				  <td>&nbsp;</td>
				  <td><input type=\"text\" name=\"op-znt\" id=\"op-znt\"  value=\"" . $dat->CPM_OP_ZNT . "\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" hidden /></td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td colspan=\"3\"><input type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RT . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RT\"/>
					/
					<input type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"3\" value=\"" . $dat->CPM_OP_RW . "\" onKeyPress=\"return nextFocus(this, event)\" " . $readonly . " title=\"RW\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td colspan=\"3\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" name=\"kecamatan2\" id=\"kecamatan2\" value=\"" . $dat->CPM_OP_KECAMATAN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kecamatan\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td colspan=\"3\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" name=\"kabupaten2\" id=\"kabupaten2\" value=\"" . $dat->CPM_OP_KABUPATEN . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"20\" maxlength=\"20\" " . $readonly . " title=\"Kabupaten/Kota\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td colspan=\"3\"><input type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $dat->CPM_OP_KODEPOS . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>KOORDINAT</td>
				  <td colspan=\"3\"><input type=\"text\" name=\"koordinat\" id=\"koordinat\" value=\"" . $dat->KOORDINAT . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"35\" maxlength=\"10\" " . $readonly . " title=\"Koordinat\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" id=\"certificate-number\" value=\"" . $dat->CPM_OP_NMR_SERTIFIKAT . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"40\" maxlength=\"50\" title=\"Nomor Sertifikat Tanah\"/></td>
				</tr>
			  </table>
			  
			  </td>
			</tr>
			<tr>
			   <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
			  <td width=\"97%\"><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"10\"><div align=\"right\">1.</div></td>
				  <td>Nomor KTP</td>
				  <td><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $dat->CPM_WP_NOKTP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"24\" maxlength=\"24\" title=\"No KTP wajib di isi\" onblur=\"checkTransLast();\"/>&nbsp;&nbsp;<input type=\"button\" name=\"checkKTP\" id=\"checkKTP\" value=\"Ambil Data Dukcapil\" class=\"myButton\" onclick=\"checkDukcapil();checkTransLast();\"><img src=\"./image/icon/loading.gif\" id=\"loaderCek\"><div id=\"newl\"></div></td>
				</tr>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $dat->CPM_WP_NPWP . "\" onkeypress=\"return nextFocus(this,event)\" size=\"15\" maxlength=\"15\"  " . $readonly . " title=\"NPWP\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td width=\"200\">Nama Wajib Pajak</td>
				  <td width=\"\"><input type=\"text\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" name=\"name\" id=\"name\" value=\"" . $dat->CPM_WP_NAMA . "\" onkeypress=\"return nextFocus(this,event)\" size=\"60\" maxlength=\"60\" " . $readonly . " title=\"Nama Wajib Pajak\"/></td>
				</tr>
				<tr valign=\"top\">
				  <td><div align=\"right\">4.</div></td>
				  <td>Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" cols=\"35\" rows=\"4\" title=\"Alamat Wajib pajak\" " . $readonly . ">" . str_replace("<br />", "\n", $dat->CPM_WP_ALAMAT) . "</textarea></td>				  
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\" name=\"kelurahan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kelurahan\" value=\"" . $dat->CPM_WP_KELURAHAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . "  title=\"Kelurahan\"/></td>				 
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RT . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . "  title=\"RT\"/>/<input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"3\"  value=\"" . $dat->CPM_WP_RW . "\" onkeypress=\"return nextFocus(this,event)\"  " . $readonly . " title=\"RW\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kecamatan</td>
				  <td><input type=\"text\" name=\"kecamatan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kecamatan\"  value=\"" . $dat->CPM_WP_KECAMATAN . "\" onkeypress=\"return nextFocus(this,event)\" size=\"20\" maxlength=\"20\"  " . $readonly . "  title=\"Kecamatan\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"kabupaten\"  value=\"" . $dat->CPM_WP_KABUPATEN . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"20\" maxlength=\"20\"  " . $readonly . " title=\"Kabupatan/Kota\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $dat->CPM_WP_KODEPOS . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"5\" maxlength=\"5\" " . $readonly . " title=\"Kode POS\"/></td>
				</tr>
			  </table>
			  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">			  
				<tr>
				  <td width=\"14\"><div align=\"right\">10.</div></td>
				  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
			    </tr>
                <tr>
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">".jenishak($typeR)."</td>
			    <tr>
				<tr id=\"aphb\" ".$display_aphb.">
				  <td><div align=\"right\"></div></td>
				  <td colspan=\"2\">".aphb($APHB)."</td>
			    <tr>
			  </table>
			  <table width=\"900\" border=\"1\" cellspacing=\"1\" cellpadding=\"4\" style=\"border: 1px solid black;border-collapse: collapse;\" class=\"pure-table\"><thead>
		  <tr>
			<td colspan=\"5\"><strong>Penghitungan NJOP PBB:</strong></th>
			</tr>
		  <tr>
			<th width=\"77\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Objek pajak</th>
			<th width=\"176\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Diisi luas tanah atau bangunan yang haknya diperoleh</th>
			<th width=\"197\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Diisi
				berdasarkan SPPT PBB terakhir sebelum terjadinya peralihan hak  
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $dat->CPM_OP_THN_PEROLEH . "\" onKeyPress=\"return numbersonly(this, event)\" " . $readonly . " title=\"Tahun SPPT PBB\"/></th>
			<th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
			</tr></thead>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">7. Luas Tanah (Bumi)</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">9. NJOP Tanah (Bumi) /m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (7x9)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">
				<input type=\"text\" name=\"land-area\" id=\"land-area\"  value=\"" . number_format(strval($dat->CPM_OP_LUAS_TANAH ), 0, '', ''). "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . "  title=\"Luas Tanah\"/ ".$input_readonly.">
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"land-njop\" id=\"land-njop\"  value=\"" . $dat->CPM_OP_NJOP_TANAH . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" " . $readonly . " title=\"NJOP Tanah\"/ ".$input_readonly."></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">" . number_format(strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">8. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">10. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (8x10)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input type=\"text\" name=\"building-area\" id=\"building-area\" value=\"" .number_format(strval($dat->CPM_OP_LUAS_BANGUN), 0, '', '') . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"Luas Bangunan\"/ ".$input_readonly.">
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input type=\"text\" name=\"building-njop\" id=\"building-njop\"   value=\"" . $dat->CPM_OP_NJOP_BANGUN . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" " . $readonly . " title=\"NJOP Bangunan\"/ ".$input_readonly."></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">12.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN), 0, '.', ',') . "</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">" . number_format(strval($dat->CPM_OP_LUAS_BANGUN) * strval($dat->CPM_OP_NJOP_BANGUN) + strval($dat->CPM_OP_LUAS_TANAH) * strval($dat->CPM_OP_NJOP_TANAH), 0, '.', ',') . "</td>
		  </tr>
		  
		</table>
		<div id=\"nilai-pasar\">
				</div>
				<br>
		11. Harga Transaksi Rp. <input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $dat->CPM_OP_HARGA . "\" onKeyPress=\"return numbersonly(this, event)\"  onchange=\"checkTransaction()\" title=\"Harga Transaksi\"/ onblur=\"loadLaikPasar();\" ".$input_readonly.">
			  </td>
			</tr>
			<tr style=\"display:none\">
			   <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"188\" id=\"akumulasi\" >".number_format(strval($dat->CPM_SSB_AKUMULASI), 2, '.', ',')."</td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"443\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"188\" align=\"center\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"tNJOP\" align=\"right\">" . number_format(getBPHTBPayment_all(0), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"NPOPTKP\" align=\"right\">" . number_format($NPOPTKP, 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\"  align=\"right\">" . number_format(getBPHTBPayment_all(1), 0, '.', ',') . "</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
					<td id=\"tBPHTBTS\" align=\"right\">" . number_format(getBPHTBPayment_all(2), 0, '.', ',') . "</td>
				  </tr>
				  </tr>
				   <tr ".display_pengenaan.">
					<td >Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"".$pengenaan."\" readonly=\"readonly\"/>%</td>
					<td id=\"tPengenaan\" align=\"right\">" . number_format(getBPHTBPayment_all(3), 0, '.', ',') . "</td>
				  </tr>
				  <tr ".display_aphb.">
					<td>APHB &nbsp;&nbsp;</td>
					<td id=\"tAPHB\" align=\"right\">" . number_format(getBPHTBPayment_all(4), 0, '.', ',') . "</td>
				  </tr>
				  ".$kena_denda."".$kena_denda2."
				  <tr>
					<td><strong>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</strong></td>
					<td id=\"tBPHTBT\" align=\"right\">" . number_format(getBPHTBPayment_all(5), 0, '.', ',') . "</td>
				  </tr>
			  </table></td>
			</tr>
			<tr>
			   <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"650\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong> 	</td>
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
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" disabled onclick=\"enableE(this,1);\" ".$c2." /></td>
				  <td align=\"right\" valign=\"top\">b.</td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\" ".$r2.">
					<option ".$sel1." >STPD BPHTB</option>
					<option ".$sel2." >SKPD Kurang Bayar</option>
					<option ".$sel3." >SKPD Kurang Bayar Tambahan</option>
				  </select><font size=\"2\" color=\"red\">*hanya bisa dilakukan di menu kurang bayar</font></td>
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
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\" ".$c3."/></td>
				  <td align=\"right\" valign=\"top\">c.</td>
				  <td valign=\"top\">Pengurangan dihitung sendiri menjadi <select name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" onchange=\"checkTransLast();\">".$option_pengurangan."
				    ";
					$qry = "select * from cppmod_ssb_pengurangan ORDER BY CPM_KODE_PENGURANGAN asc";
					//echo $qry;exit;
					$res = mysqli_query($DBLink, $qry);
					
						while($data = mysqli_fetch_assoc($res)){
							$html .= "<option value=\"".$data['CPM_KODE_PENGURANGAN'].".".$data['CPM_PENGURANGAN']."\">Kode ".$data['CPM_KODE_PENGURANGAN']." : ".$data['CPM_PENGURANGAN']."%</option>";
						}
			$html .="</select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\"><!-- Berdasakan peraturan KDH No : --> 
					<input type=\"text\" name=\"jsb-choose-role-number\" id=\"jsb-choose-role-number\" size=\"30\" maxlength=\"30\" value=\"-\" title=\"Peraturan KHD No\" hidden=\"hidden\" /></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\" ".$c4." /></td>
				  <td align=\"right\" valign=\"top\">d.</td>
				  <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" ".$readonly." ".$r4." title=\"Lain-lain\">".$info."</textarea>
				  <input type=\"hidden\" id=\"ver-doc\" value=\"".$dat->CPM_TRAN_SSB_VERSION."\" name=\"ver-doc\">
				  </td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"5\" id=\"RadioGroup1_8\"  onclick=\"enableE(this,4);\" ".$c5." hidden=\"hidden\" /></td>
				  <td align=\"right\" valign=\"top\"></td>
				  <td valign=\"top\"><input type=\"text\" name=\"jsb-choose-fraction1\" id=\"jsb-choose-fraction1\" size=\"1\" maxlength=\"2\" value=\"".$typePecahan[0]."\" ".$readonly." ".$r5." title=\"pecahan 1\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/><input type=\"text\" name=\"jsb-choose-fraction2\" id=\"jsb-choose-fraction2\" size=\"1\" maxlength=\"2\" value=\"".$typePecahan[1]."\" ".$readonly." ".$r6." title=\"pecahan 2\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/></td>
				</tr>
				<tr>
				  <td colspan=\"2\" align=\"center\" valign=\"middle\"><input type=\"hidden\" name=\"role\" id=\"role\" name=\"role\" value=\"".getRole()."\"></td>
				</tr>
				
				<tr><input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\"><input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\" value=\"" . $dat->CPM_OP_NPOPTKP . "\"></tr>
			  </table>
			  </td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\" style=\"border-radius: 0px 0px 10px 10px;\">Jumlah yang dibayarkan : " . number_format(getBPHTBPayment_all(6), 0, '.', ',') . "</td>
			</tr>";
			$arrStatus = array(1=>"Notaris",2=>"Verifikasi",3=>"Persetujuan");
			$html .= "<tr>
							<td class=\"result-analisis\">&nbsp;</td>
							<td colspan=\"3\" class=\"result-analisis\"><strong>Status Dokumen</strong> : 
							<input type='hidden' name='statusDokumenCurrent' value='".base64_encode($dat->CPM_TRAN_STATUS)."'>
							<input type='hidden' name='idDokumen' value='".base64_encode($dat->CPM_TRAN_SSB_ID)."'>
							<input type='hidden' name='ver-doc' value='".base64_encode($dat->CPM_SSB_VERSION)."'>
							<select name='statusDokumen'>
									<option value='99' selected>-Pilih Status-</option>";
							if ($getgwssb!=1) {
								foreach($arrStatus as $val => $status){
									$html .= ($dat->CPM_TRAN_STATUS==$val)? "<option value='".$val."' selected>".$status."</option>" :
																				"<option value='".$val."'>".$status."</option>";												
								}
							}
							$html .= "	</select>
							</td>
						</tr>
						<tr>
							<td class=\"result-analisis\">&nbsp;</td>
							<td align=\"right\" class=\"result-analisis\" >								
								<input type=\"reset\" value=\"Reset Perubahan\">
								<input type=\"submit\" name=\"submit\" id=\"submit\" value=\"Update Dokumen\">
				  				<input type=\"hidden\" id=\"trsid\" value=\"" . $dat->CPM_SSB_ID . "\">
							</td>
						</tr>
			<tr>
				<td colspan=\"2\" style=\"border-radius: 0px 0px 10px 10px;\"></td>
			</tr>						
		  </table>
		  <input type=\"hidden\" id=\"docauthor\" name=\"docauthor\" value=\"" . $dat->CPM_SSB_AUTHOR . "\">
		</form></div>";
 	return $html;
}
function getRole(){
    global $DBLink, $data;
    $id = $_REQUEST['a'];
    $qry = "select * from central_user_to_app where CTR_APP_ID = '" . $id . "' and CTR_USER_ID = '" . $data->uid . "'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_RM_ID'];
    }
}
function getBPHTBPayment_all($no) {
		global $data;
		$configAPHB = getConfigValue("aBPHTB",'CONFIG_APHB');
		$hitungaphb = getConfigValue("aBPHTB",'HITUNG_APHB');
		$configPengenaan = getConfigValue("aBPHTB",'CONFIG_PENGENAAN');
		$lb = $data->CPM_OP_LUAS_BANGUN;
		$nb = $data->CPM_OP_NJOP_BANGUN;
		$lt = $data->CPM_OP_LUAS_TANAH;
		$nt = $data->CPM_OP_NJOP_TANAH;
		$h  = $data->CPM_OP_HARGA;
		$p  = $data->CPM_PAYMENT_TIPE_PENGURANGAN;
		$jh = $data->CPM_OP_JENIS_HAK;
		$NPOPTKP = $data->CPM_OP_NPOPTKP;
		$phw = $data->CPM_PENGENAAN;
		$denda = $data->CPM_DENDA;
		$aphbt = $data->CPM_APHB;
		
		$a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
		$b = strval($h);
		$npop = 0;
		if($jh=='15'){
			$npop=$b;
		}else{
			if ($b <= $a) $npop = $a; else $npop = $b;
		}
		$npkp = $npop-strval($NPOPTKP);
		if($npkp<=0){
			$npkp = 0;
		}
		$jmlByr = ($npop-strval($NPOPTKP))*0.05;
		$hbphtb = ($npop-strval($NPOPTKP))*0.05;
		$aphb=0;
		$hbphtb_pengenaan = 0;
		$hbphtb_aphb = 0;
		if(($jh==4)||($jh==5)||($jh==31)){
			if($configPengenaan=='1'){
				$hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
				$jmlByr= $hbphtb-($hbphtb_pengenaan);
			}else{
				$hbphtb_pengenaan = 0;
				$jmlByr= $hbphtb;
			}
			
		}else if($jh==7){
			if($configAPHB=='1'){
				$p=explode("/",$aphbt);
				$aphb=$p[0]/$p[1];
				$hbphtb_pengenaan = 0;
				if($hitungaphb=='1'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
				}else if($hitungaphb=='2'){
					$hbphtb_aphb = (($npop-strval($NPOPTKP))*0.05)-(($npop-strval($NPOPTKP))*0.05 * $aphb);
				}else if($hitungaphb=='3'){
					$hbphtb = $npop*$aphb;
					$hbphtb_aphb = ($hbphtb-strval($NPOPTKP))* 0.05;
				}else if($hitungaphb=='0'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
				}
			}else{
				$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
			}
			$jmlByr= $hbphtb_aphb;
		}
		
		$tp = strval($p);
		if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
		
		if($denda>0){
			$jmlByr = $jmlByr+$denda;
		}else{
			$jmlByr = $jmlByr;
			$hbphtb = 0;
		}
		if ($jmlByr < 0) $jmlByr = 0;
		$total_temp = $jmlByr;
		$hasil = $npop.",".$npkp.",".$hbphtb.",".$hbphtb_pengenaan.",".$hbphtb_aphb.",".$total_temp.",".$jmlByr;
		$pilihhitung=explode(",",$hasil);
		
		//echo $hasil;exit;
		return $pilihhitung[$no];
	}
function getBPHTBPayment($lb,$nb,$lt,$nt,$h,$p,$jh,$NPOPTKP,$phw,$aphbt,$denda) {
		//$a = $_REQUEST['a'];
		/*$NPOPTKP =  getConfigValue($a,'NPOPTKP_STANDAR');
		
		$typeR = $jh;
		
		if (($typeR==4) || ($typeR==6)){
			$NPOPTKP =  getConfigValue($a,'NPOPTKP_WARIS');
		} else {
			
		}*/
		
		/*if($this->getNOKTP($noktp,$nop,$tgl)) {	
			$NPOPTKP = 0;
		}*/
		$configAPHB = getConfigValue("aBPHTB",'CONFIG_APHB');
		$hitungaphb = getConfigValue("aBPHTB",'HITUNG_APHB');
		$configPengenaan = getConfigValue("aBPHTB",'CONFIG_PENGENAAN');
		$a = strval($lb)*strval($nb)+strval($lt)*strval($nt);
		$b = strval($h);
		$npop = 0;
		if($jh=='15'){
			$npop=$b;
		}else{
			if ($b <= $a) $npop = $a; else $npop = $b;
		}
		$npkp = $npop-strval($NPOPTKP);
		$jmlByr = ($npop-strval($NPOPTKP))*0.05;
		$hbphtb = ($npop-strval($NPOPTKP))*0.05;
		$aphb=0;
		$hbphtb_pengenaan = 0;
		$hbphtb_aphb = 0;
		if(($jh==4)||($jh==5)||($jh==31)){
			if($configPengenaan=='1'){
				$hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
				$jmlByr= $hbphtb-($hbphtb_pengenaan);
			}else{
				$hbphtb_pengenaan = 0;
				$jmlByr= $hbphtb;
			}
			
		}else if($jh==7){
			if($configAPHB=='1'){
				$p=explode("/",$aphbt);
				$aphb=$p[0]/$p[1];
				$hbphtb_pengenaan = 0;
				if($hitungaphb=='1'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05 * $aphb;
				}else if($hitungaphb=='2'){
					$hbphtb_aphb = (($npop-strval($NPOPTKP))*0.05)-(($npop-strval($NPOPTKP))*0.05 * $aphb);
				}else if($hitungaphb=='3'){
					$hbphtb_aphb = ($npop*$aphb)-(strval($NPOPTKP)* 0.05);
				}else if($hitungaphb=='0'){
					$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
				}
			}else{
				$hbphtb_aphb = ($npop-strval($NPOPTKP))*0.05;
			}
				$jmlByr= $hbphtb_aphb;
		}
		$total_temp = $jmlByr;
		$tp = strval($p);
		if ($tp!=0) $jmlByr = $jmlByr-($jmlByr*($tp*0.01));
		
		if($denda>0){
			$jmlByr = $jmlByr+$denda;
		}else{
			$jmlByr = $jmlByr;
		}
		if ($jmlByr < 0) $jmlByr = 0;
		return $jmlByr;
	}

	function getSPPTInfo($idssb) {
		$a='aBPHTB';
		$iErrCode=0;
		$DbName = getConfigValue($a, 'BPHTBDBNAME');
		$DbHost = getConfigValue($a, 'BPHTBHOSTPORT');
		$DbPwd = getConfigValue($a, 'BPHTBPASSWORD');
		$DbTable = getConfigValue($a, 'BPHTBTABLE');
		$DbUser = getConfigValue($a, 'BPHTBUSERNAME');
	
		SCANPayment_ConnectToDB($LDBLink, $LDBConn, $DbHost, $DbUser, $DbPwd, $DbName, true);
		if ($iErrCode != 0) {
			$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
			if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
				error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
			exit(1);
		}
		
		$query = "SELECT bphtb_dibayar,PAYMENT_FLAG, PAYMENT_PAID FROM $DbTable WHERE id_switching = '".$idssb."' ORDER BY saved_date DESC limit 1  ";
		$res = mysqli_query($LDBLink, $query);
		if ( $res === false ){
			print_r(mysqli_error($LDBLink));
			return "Tidak Ditemukan"; 
		}
		if (mysqli_num_rows($res)>0) {
			while ($row = mysqli_fetch_assoc($res)) {
					return [
				'PAYMENT_FLAG' => $row['PAYMENT_FLAG'] ? "1" : "0",
				'PAYMENT_PAID' => $row['PAYMENT_PAID'],
				'bphtb_dibayar' => $row['bphtb_dibayar']
			];
			}
		}
	}
?>