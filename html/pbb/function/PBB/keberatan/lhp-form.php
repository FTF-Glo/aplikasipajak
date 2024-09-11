<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "function/PBB/gwlink.php");

//echo "<link href=\"view/PBB/spop.css\" rel=\"stylesheet\" type=\"text/css\"/>";

//echo "<link href=\"inc/PBB/jquery-tooltip/jquery.tooltip.css\" rel=\"stylesheet\" type=\"text/css\"/>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
//

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.validate.min.js\"></script>\n";
//echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/tax/mod-pelayanan/func-mod-pelayanan.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/js/jquery.autocomplete.css\"></script>\n";
echo "<script src=\"inc/js/jquery.autocomplete.js\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig  = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbServices = new DbServices($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);

function getConfigValue($id, $key)
{
	global $DBLink;
	$qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
	$res = mysqli_query($DBLink, $qry);
	if ($res === false) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	}
	while ($row = mysqli_fetch_assoc($res)) {
		return $row['CTR_AC_VALUE'];
	}
}

function formPenerimaan($initData, $initDataRed, $dataLHP, $dataLHPP)
{
	global $a, $m, $appConfig, $arConfig, $dis, $tab, $DBLink, $svcid, $modus;

	$hasilEx  	= explode("##", $dataLHP['CPM_LHP_RESULT']);
	$jmlHasil 	= count($hasilEx);
	$jmlPetugas	= mysqli_num_rows($dataLHPP);

	if ($modus == 'edit') {
		//echo $modus;
		//echo $arConfig['lhp'];
		$j = $jmlHasil + 1;
		$formHasil = "";
		$i = 1;
		$idrow = 2;
		foreach ($hasilEx as $val) {
			//$formHasil .= "<div><input type=\"text\" class=\"result\" maxlength=\"90\" size=\"90\" name=\"result[]\" placeholder=\"Hasil ".$i."\" id=\"res".$i."\" value=\"".$val."\"></div><br>";
			$formHasil .= "<div><textarea  class='result form-control' rows='2' cols='90' name='result[]' placeholder='Hasil " . $i . "' id='res" . $i . "'>" . $val . "</textarea></div><br>";
			$i++;
		}

		if ($jmlPetugas != 0) {
			$formPeneliti = "";
			$x = 1;
			while ($data = mysqli_fetch_array($dataLHPP)) {
				$formPeneliti .= "<tr><td><input  type ='text' name='nama[]' id='nama' size='50' onblur='getPeneliti2(" . $x . ")' value='" . $data['CPM_LHP_PE_NAMA'] . "'></td>
								  <td><input  class=\"form-control\" type ='text' name='jabatan[]' id='jabatan' size='30' value='" . $data['CPM_LHP_PE_JABATAN'] . "'></td>
								  <td><input  class=\"form-control\" type ='text' name='nip[]' id='nip' size='30' value='" . $data['CPM_LHP_PE_NIP'] . "'></td></tr>";
				$x++;
			}
			$idrow = $x;
		} else {
			$formPeneliti .= "<tr>
					<td><input  class=\"form-control\"type='text' name='nama[]' id='nama1' size='50' onblur='getPeneliti(1)'></td>
					<td><input  class=\"form-control\"type ='text' name='jabatan[]' id='jabatan1' size='30'></td>
					<td><input  class=\"form-control\"type ='text' name='nip[]' id='nip1' size='30'></td>
					</tr>";
		}
	} else if ($modus == 'input') {
		//echo $modus;
		$j = 2;
		$idrow = 2;
		$formPenelitiIn = "<tr>
					<td><input  class=\"form-control\" type='text' name='nama[]' id='nama1' size='50' onblur='getPeneliti(1)'></td>
					<td><input  class=\"form-control\" type ='text' name='jabatan[]' id='jabatan1' size='30'></td>
					<td><input  class=\"form-control\" type ='text' name='nip[]' id='nip1' size='30'></td>
					</tr>";
		//$formHasilIn = "<div><input type='text' class='result' maxlength='90' size='90' name='result[]' placeholder='Hasil 1' id='res1'></div><br>";
		$formHasilIn = "<div><textarea class=\"form-control\" class='result' rows='2' cols='90' name='result[]' placeholder='Hasil 1' id='res1'></textarea></div><br>";
	}
	$formPersetujuan = '';
	if ($arConfig['usertype'] == 'persetujuan-keberatan ') {
		$statusVerifikasi = '';
		if ($dataLHP['CPM_LHP_VERIFICATION_STATUS'] == '1') $statusVerifikasi = 'Disetujui';
		else if ($dataLHP['CPM_LHP_VERIFICATION_STATUS'] == '2') $statusVerifikasi = 'Ditolak';
		$alasanVerifikasi = '';
		if (trim($dataLHP['CPM_LHP_VERIFICATION_REASON']) != '') $alasanVerifikasi = ', dengan alasan ' . trim($dataLHP['CPM_LHP_VERIFICATION_REASON']);

		$checked1 = ($dataLHP['CPM_LHP_APPROVAL_STATUS'] == '1') ? "checked=checked" : "";
		$checked2 = ($dataLHP['CPM_LHP_APPROVAL_STATUS'] == '2') ? "checked=checked" : "";
		$alasan = ($dataLHP['CPM_LHP_APPROVAL_REASON'] == null) ? "" : $dataLHP['CPM_LHP_APPROVAL_REASON'];
		$formPersetujuan = '
            <table class=\"table table-borderless\" cellpadding="5" border="0">
                <tbody><tr><td class="tbl-rekomen" colspan="2"><b>Informasi Verifikasi</b></td></tr>
                <tr><td valign="top" colspan="2" class="tbl-rekomen">
                        <label>' . $statusVerifikasi . $alasanVerifikasi . '</label>
                        </td>	
                </tr>
                <tr><td class="tbl-rekomen" colspan="2"><b>Masukkan rekomendasi anda</b></td></tr>
                <tr><td valign="top" colspan="2" class="tbl-rekomen">
                        <input type="radio" value="1" name="rekomendasi" ' . $checked1 . '><label>Setuju</label><br>
                        <input type="radio" value="2" name="rekomendasi" ' . $checked2 . '><label>Tolak</label>
                        </td>	
                </tr>
                <tr><td valign="top" class="tbl-rekomen">
                        Alasan<br><textarea title="&lt;br&gt;Alasan wajib diisi" class="required" rows="5" cols="70" id="alasan" name="alasan">' . $alasan . '</textarea></td></tr>
        </tbody></table>    
        ';
	} else if ($arConfig['usertype'] == 'persetujuan-keberatan') {
		$formPersetujuan = '
            <table class=\"table table-borderless\" cellpadding="5" border="0">
                <tbody><tr><td class="tbl-rekomen" colspan="2"><b>Masukkan rekomendasi anda</b></td></tr>
                <tr><td valign="top" colspan="2" class="tbl-rekomen">
                        <input type="radio" value="1" name="rekomendasi"><label>Setuju</label><br>
                        <input type="radio" value="2" name="rekomendasi"><label>Tolak</label>
                        </td>	
                </tr>
                <tr><td valign="top" class="tbl-rekomen">
                        Alasan<br><textarea title="&lt;br&gt;Alasan wajib diisi" class="required" rows="5" cols="70" id="alasan" name="alasan"></textarea></td></tr>
        </tbody></table>    
        ';
	}
	$html = "
    <style>
	table { border-collapse:collapse; }
	
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }

    </style>
    <script language=\"javascript\">
		var idrow = " . $idrow . ";
		function getPeneliti()
		{
			var nama = $.trim($(\"#nama1\").val());
			$.ajax({
				type: 'POST',
				data: 'nama='+nama,
				url: './function/PBB/pengurangan/lhp-peneliti-data.php',
				success: function(res){
				//console.log(res)
				d=jQuery.parseJSON(res);                                                
					if(d.r == true){							
						$('#nip1').val(d.dataP.nip);
						$('#jabatan1').val(d.dataP.jabatan);
					} else {
						alert(d.errstr);
						$('#nip1').val('');
						$('#jabatan1').val('');
						}  
					}	
				});
		}
		
		function getPeneliti2(idrow)
		{
			var nama = $.trim($(\"#nama\"+idrow).val());
			$.ajax({
				type: 'POST',
				data: 'nama='+nama,
				url: './function/PBB/pengurangan/lhp-peneliti-data.php',
				success: function(res){
				//console.log(res)
				d=jQuery.parseJSON(res);                                                
					if(d.r == true){							
						$(\"#nip\"+idrow).val(d.dataP.nip);
						$(\"#jabatan\"+idrow).val(d.dataP.jabatan);
					} else {
						alert(d.errstr);
						$(\"#nip\"+idrow).val('');
						$(\"#jabatan\"+idrow).val('');
						}  
					}	
				});
		}
		
		$().ready(function() {
			$(\"#nama1\").autocomplete(\"function/PBB/pengurangan/lhp-peneliti-nm.php\", {
				width: 275,
				matchContains: true,
				selectFirst: true
			});
		});
		
		function tambah(){ 
			var x=document.getElementById('datatable').insertRow(idrow); 
			var td1=x.insertCell(0); 
			var td2=x.insertCell(1); 
			var td3=x.insertCell(2); 
			td1.innerHTML='<input type =\"text\" name=\"nama[]\" id=\"nama'+idrow+'\" size=\"50\" onblur=\"getPeneliti2('+idrow+')\">'; 
			td2.innerHTML='<input type =\"text\" name=\"jabatan[]\" id=\"jabatan'+idrow+'\" size=\"30\">'; 
			td3.innerHTML='<input type =\"text\" name=\"nip[]\" id=\"nip'+idrow+'\" size=\"30\">'; 
			$().ready(function() {
				$(\"#nama\"+idrow).autocomplete(\"function/PBB/pengurangan/lhp-peneliti-nm.php\", {
					width: 275,
					matchContains: true,
					selectFirst: true
				});
			});
			idrow++; 
		} 
	
		function hapus(){ 
			if(idrow>2){ 
				var x=document.getElementById('datatable').deleteRow(idrow-1); 
				idrow--; 
			} 
		} 
        $(document).ready(function(){
            $( \"input:submit, input:button\").button();
			var j = " . $j . "; 
			$('#add2').click(function() {
				//$('<div><input type=\"text\" class=\"result\" maxlength=\"90\" size=\"90\" name=\"result[]\" placeholder=\"Hasil '+j+'\" id=\"res'+j+'\"></div><br>').fadeIn('fast').appendTo('.inputs2');
				$('<div><textarea class=\"result\" rows=\"3\" cols=\"90\" name=\"result[]\" placeholder=\"Hasil '+j+'\" id=\"res'+j+'\"></textarea></div><br>').fadeIn('fast').appendTo('.inputs2');
				j++;
			});
			 
			$('#remove').click(function() {
				if(j > 2) {
					$('.result:last').remove();
					j--;
				}
			});
		
			$('#tglPenelitian').datepicker({dateFormat: 'dd-mm-yy'});
			";


	$html .= "//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });  
			$(\"#form-penerimaan\").validate({
                rules : {
                    tglPenelitian : \"required\"
                },
                messages : {
                    tglPenelitian : \"Tanggal wajib diisi\"
                }
            });
        })

    </script>
    <div id=\"main-content-pengurangan\" class=\"col-md-12\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
                      <table class=\"table table-borderless\" border=\"0\">  
							  <table class=\"table table-borderless\" border=\"0\" cellpadding=\"10\">
								<tr>
								  <td colspan=\"3\" align=\"center\"><strong><font size=\"+2\">LHP</font></strong><br><br><hr /></td>
								</tr>
								<tr>
								   <td width=\"120\">Tanggal Penelitian</td>
								   <td width=\"2\">:</td>
                                   <td>
									 <input class=\"form-control\" type=\"text\" name=\"tglPenelitian\" id=\"tglPenelitian\" value=\"" . (($dataLHP['CPM_LHP_DATE'] != '') ? $dataLHP['CPM_LHP_DATE'] : '') . "\" size=\"12\" maxlength=\"12\" placeholder=\"Tgl Penelitian\"/>                                      
                                   </td>
								</tr>
								<tr>
								  <td align=\"left\" colspan=\"3\">
									  <strong>Data Peneliti</strong><br><br>
									  <table class=\"table table-borderless\" id=datatable border=0> 
										<tr> 
											<td>Nama</td> 
											<td>Jabatan</td> 
											<td>NIP</td> 
										</tr>  
											" . (($modus == 'edit') ? $formPeneliti : $formPenelitiIn) . "											
										</table>
										<br>
										<button class=\"btn btn-primary bg-maka\" type=button value=\"Tambah\" onclick=tambah()>Tambah</button> 
										<!-- <input type=button value=delete onclick=hapus()> -->
										<br> 
								  </td>
								</tr>
								<tr>
									<td colspan=\"3\"><strong>Hasil Penelitian</strong></td>
								</tr>
								<tr>
									<td colspan=\"3\">
										<div class=\"inputs2\">
											" . (($modus == 'edit') ? $formHasil : $formHasilIn) . "
										</div>
										<button class=\"btn btn-primary bg-maka\" type=\"button\" id=\"add2\" value=\"Tambah\">Tambah</button>
									</td>
								</tr>
                                                                <tr>
									<td colspan=\"3\" align=\"center\"><hr>
                                                                        " . $formPersetujuan . "<br>
                                                                        <button class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-simpan\" id=\"btn-simpan\" value=\"Simpan\">Simpan</button>
									<button class=\"btn btn-primary bg-maka\" type=\"button\" name=\"btn-batal\" id=\"btn-batal\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['lhp']) . "\"' />Batal</button></td>
								</tr> 
                              </table>								
                      </table>
                    </form>
				</div>";
	return $html;
}

function getInitData($id = "")
{
	global $DBLink;

	if ($id == '') return getDataDefault();

	$qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
		return getDataDefault();
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
			return $row;
		}
	}
}

function getReduce($id = "")
{
	global $DBLink;

	$qry = "select * from cppmod_pbb_service_reduce where CPM_RE_SID='{$id}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_RE_DATE_SPPT'] = substr($row['CPM_RE_DATE_SPPT'], 8, 2) . '-' . substr($row['CPM_RE_DATE_SPPT'], 5, 2) . '-' . substr($row['CPM_RE_DATE_SPPT'], 0, 4);
			return $row;
		}
	}
}

function getLHP($nomor = "")
{
	global $DBLink;
	$qry = "SELECT * FROM cppmod_pbb_service_lhp WHERE CPM_LHP_SID='{$nomor}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_LHP_DATE'] = substr($row['CPM_LHP_DATE'], 8, 2) . '-' . substr($row['CPM_LHP_DATE'], 5, 2) . '-' . substr($row['CPM_LHP_DATE'], 0, 4);
			return $row;
		}
	}
}

function getLHPPetugas($nomor = "")
{
	global $DBLink;
	$qry = "SELECT * FROM cppmod_pbb_service_lhp_petugas WHERE CPM_LHP_PE_SID='{$nomor}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			return $row;
		}
	}
}

function getLHPPetugasFx($nomor = "")
{
	global $DBLink;
	$qry = "SELECT * FROM cppmod_pbb_service_lhp_petugas WHERE CPM_LHP_PE_SID='{$nomor}'";
	//echo $qry;exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else
		return $res;
}

function getDataDefault()
{
	$default = array(
		'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
		'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
		'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
	);
}

function isExistSID($nomor = "")
{
	global $DBLink;
	$query = "SELECT CPM_LHP_SID FROM cppmod_pbb_service_lhp WHERE CPM_LHP_SID='$nomor'";
	$res = mysqli_query($DBLink, $query);
	$nRes = mysqli_num_rows($res);
	return $nRes;
}

function isExistSIDPetugas($nomor = "")
{
	global $DBLink;
	$query = "SELECT CPM_LHP_PE_SID FROM cppmod_pbb_service_lhp_petugas WHERE CPM_LHP_PE_SID='$nomor'";
	$res = mysqli_query($DBLink, $query);
	$nRes = mysqli_num_rows($res);
	return $nRes;
}

function getLastLHPNumber()
{
	global $DBLink;

	$qry = "SELECT MAX(CPM_LHP_NO) AS LHP_NUMBER FROM cppmod_pbb_generate_lhp_number";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else {
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['LHP_NUMBER'];
		}
		return "0";
	}
}

function generateLHPNumber()
{
	global $appConfig;

	$lastNumber = getLastLHPNumber();
	$newNumber = $lastNumber + 1;
	if ($appConfig['NOMOR_LHP_OTOMATIS'] == '1') {
		return $newNumber . $appConfig['FORMAT_NOMOR_LHP'];
	} else
		return NULL;
}

function updateObjection($nomor = '', $noLHP = '', $dateLHP = '')
{
	global $DBLink;

	$qry = "UPDATE cppmod_pbb_service_objection SET CPM_OB_LHP_NUMBER = '$noLHP', CPM_OB_LHP_DATE = '$dateLHP' WHERE CPM_OB_SID='$nomor'";
	// echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
	} else
		return $res;
}

function isHaveLHPNumber($svcid)
{
	global $DBLink;

	$qry = "SELECT CPM_OB_LHP_NUMBER AS LHP_NUMBER FROM cppmod_pbb_service_objection WHERE CPM_OB_SID='{$svcid}'";

	$res = mysqli_query($DBLink, $qry);
	if (!$res) {
		echo $qry . "<br>";
		echo mysqli_error($DBLink);
		return false;
	} else {
		$lhpNumber = '';
		while ($row = mysqli_fetch_assoc($res)) {
			$lhpNumber = $row['LHP_NUMBER'];
		}
		if ($lhpNumber != null && $lhpNumber != '') return true;
		else return false;
	}
}

function save()
{
	global $data, $DBLink, $uname, $dis, $tab, $validator, $svcid, $modus, $arConfig, $appConfig;

	$tglPenelitian 	= substr($_REQUEST['tglPenelitian'], 6, 4) . '-' . substr($_REQUEST['tglPenelitian'], 3, 2) . '-' . substr($_REQUEST['tglPenelitian'], 0, 2);
	function filterArray($value)
	{
		return ($value != '');
	}
	$hasilFilter	= array_filter($_POST['result'], 'filterArray');
	$hasil 			= join("##", $hasilFilter);

	if ($modus == 'edit') {
		//echo $modus;

		$qUpLHP = "UPDATE cppmod_pbb_service_lhp SET CPM_LHP_DATE = '{$tglPenelitian}', CPM_LHP_RESULT = '{$hasil}'";
		if (isset($_REQUEST['rekomendasi']) && $_REQUEST['rekomendasi'] != '') {
			if ($arConfig['usertype'] == 'verifikasi')
				$qUpLHP .= ", CPM_LHP_VERIFICATION_STATUS = '" . $_REQUEST['rekomendasi'] . "', CPM_LHP_VERIFICATION_REASON = '" . $_REQUEST['alasan'] . "' ";
			else $qUpLHP .= ", CPM_LHP_APPROVAL_STATUS = '" . $_REQUEST['rekomendasi'] . "', CPM_LHP_APPROVAL_REASON = '" . $_REQUEST['alasan'] . "' ";
		}
		$qUpLHP .= "WHERE CPM_LHP_SID = '{$svcid}'";

		$resUp  = mysqli_query($DBLink, $qUpLHP);
		if (!$resUp) {
			echo $qUpLHP . "<br>";
			echo mysqli_error($DBLink);
			exit;
		}

		$qDelLHPP = "DELETE FROM cppmod_pbb_service_lhp_petugas WHERE CPM_LHP_PE_SID = '{$svcid}'";

		$resDel   = mysqli_query($DBLink, $qDelLHPP);
		if (!$resDel) {
			echo $qDelLHPP . "<br>";
			echo mysqli_error($DBLink);
			exit;
		}

		$resIn = true;

		foreach ($_POST['nama'] as $key => $value) {
			if (trim($value) != '') {
				$qry1 = "INSERT INTO cppmod_pbb_service_lhp_petugas (CPM_LHP_PE_ID,CPM_LHP_PE_SID,CPM_LHP_PE_NAMA,CPM_LHP_PE_JABATAN,CPM_LHP_PE_NIP) 
					     VALUES ('" . c_uuid() . "','{$svcid}','" . $_POST['nama'][$key] . "','" . $_POST['jabatan'][$key] . "','" . $_POST['nip'][$key] . "')";
				$resIn = mysqli_query($DBLink, $qry1);
			}
		}

		if (($resUp) && ($qDelLHPP) && ($resIn)) {
			echo 'Data berhasil disimpan...!';
			$params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['lhp'];
			echo "<script language='javascript'>
					$(document).ready(function(){
					window.location = \"./main.php?param=" . base64_encode($params) . "\"
					})
				</script>";
		} else {
			echo mysqli_error($DBLink);
		}

		if (isset($_REQUEST['rekomendasi']) && $_REQUEST['rekomendasi'] != '' && $appConfig['NOMOR_LHP_OTOMATIS'] == '1') {
			//Insert to GENERATE_LHP_NUMBER
			if (!isHaveLHPNumber($svcid)) {
				$LHPNumber 			= generateLHPNumber();
				$LHPDate 			= date('Y-m-d');
				$upObj				= updateObjection($svcid, $LHPNumber, $LHPDate);
				if ($upObj) {
					$tmp      = str_replace($appConfig['FORMAT_NOMOR_LHP'], "", $LHPNumber);
					$qryLHP   = "INSERT INTO cppmod_pbb_generate_lhp_number (CPM_LHP_ID, CPM_LHP_NO, CPM_CREATOR, CPM_DATE_CREATED) VALUES ('{$LHPNumber}', '{$tmp}','{$uname}', '{$LHPDate}')";
					$resLHP   = mysqli_query($DBLink, $qryLHP);
					if ($resLHP === false) {
						echo $qryLHP . "<br>";
						echo mysqli_error($DBLink);
					}
				}
			}
		}
	} else if ($modus == 'input') {
		//echo $modus;
		$res1 = true;
		foreach ($_POST['nama'] as $key => $value) {
			if ($value) {
				$qry1 = "INSERT INTO cppmod_pbb_service_lhp_petugas (CPM_LHP_PE_ID,CPM_LHP_PE_SID,CPM_LHP_PE_NAMA,CPM_LHP_PE_JABATAN,CPM_LHP_PE_NIP) 
							 VALUES ('" . c_uuid() . "','{$svcid}','" . $_POST['nama'][$key] . "','" . $_POST['jabatan'][$key] . "','" . $_POST['nip'][$key] . "')";
				$res1 = mysqli_query($DBLink, $qry1);
			}
		}

		$qry2 = "INSERT INTO cppmod_pbb_service_lhp (CPM_LHP_SID,CPM_LHP_DATE,CPM_LHP_RESULT) 
					 VALUES ('{$svcid}','{$tglPenelitian}','{$hasil}')";
		if (isset($_REQUEST['rekomendasi']) && $_REQUEST['rekomendasi'] != '') {
			if ($arConfig['usertype'] == 'verifikasi')
				$qry2 = "INSERT INTO cppmod_pbb_service_lhp (CPM_LHP_SID,CPM_LHP_DATE,CPM_LHP_RESULT, CPM_LHP_VERIFICATION_STATUS, CPM_LHP_VERIFICATION_REASON) 
					 VALUES ('{$svcid}','{$tglPenelitian}','{$hasil}', '" . $_REQUEST['rekomendasi'] . "', '" . $_REQUEST['alasan'] . "')";
			else $qry2 = "INSERT INTO cppmod_pbb_service_lhp (CPM_LHP_SID,CPM_LHP_DATE,CPM_LHP_RESULT, CPM_LHP_APPROVAL_STATUS, CPM_LHP_APPROVAL_REASON) 
					 VALUES ('{$svcid}','{$tglPenelitian}','{$hasil}', '" . $_REQUEST['rekomendasi'] . "', '" . $_REQUEST['alasan'] . "')";
		}
		$res2 = mysqli_query($DBLink, $qry2);

		if (($res1 === false) || ($res2 === false)) {
			echo $qry1 . "<br>";
			echo $qry2 . "<br>";
			echo mysqli_error($DBLink);
			exit;
		}

		if (($res1) && ($res2)) {
			echo 'Data berhasil disimpan...!';
			$params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['lhp'];
			echo "<script language='javascript'>
						$(document).ready(function(){
							window.location = \"./main.php?param=" . base64_encode($params) . "\"
						})
					  </script>";
		} else {
			echo mysqli_error($DBLink);
		}

		if (isset($_REQUEST['rekomendasi']) && $_REQUEST['rekomendasi'] != '' && $appConfig['NOMOR_LHP_OTOMATIS'] == '1') {
			//Insert to GENERATE_LHP_NUMBER
			if (!isHaveLHPNumber($svcid)) {
				$LHPNumber 			= generateLHPNumber();
				$LHPDate 			= date('Y-m-d');
				$upObj				= updateObjection($svcid, $LHPNumber, $LHPDate);
				if ($upObj) {
					$tmp      = str_replace($appConfig['FORMAT_NOMOR_LHP'], "", $LHPNumber);
					$qryLHP   = "INSERT INTO cppmod_pbb_generate_lhp_number (CPM_LHP_ID, CPM_LHP_NO, CPM_CREATOR, CPM_DATE_CREATED) VALUES ('{$LHPNumber}', '{$tmp}','{$uname}', '{$LHPDate}')";
					$resLHP   = mysqli_query($DBLink, $qryLHP);
					if ($resLHP === false) {
						echo $qryLHP . "<br>";
						echo mysqli_error($DBLink);
					}
				}
			}
		}
	}
}
if ((isExistSID($svcid)) || (isExistSIDPetugas($svcid))) {
	$modus = 'edit';
} else {
	$modus = 'input';
}
$save 		 = $_REQUEST['btn-simpan'];

if ($save == 'Simpan') {
	save();
} else {
	$svcid  		= @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
	$tab			= @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : "";
	$initData 	 	= getInitData($svcid);
	$initDataRed 	= getReduce($svcid);
	$dataLHP		= getLHP($svcid);
	$dataLHPP		= getLHPPetugasFx($svcid);
	//print_r($dataLHP);
	//print_r($dataLHPP);
	//echo $svcid;
	echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
	echo formPenerimaan($initData, $initDataRed, $dataLHP, $dataLHPP);
}
