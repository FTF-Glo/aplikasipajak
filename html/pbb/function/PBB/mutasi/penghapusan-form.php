<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");

require_once($sRootPath . "inc/PBB/dbServices.php");

//echo "<link href=\"view/PBB/spop.css\" rel=\"stylesheet\" type=\"text/css\"/>";

//echo "<link href=\"inc/PBB/jquery-tooltip/jquery.tooltip.css\" rel=\"stylesheet\" type=\"text/css\"/>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";


echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.validate.min.js\"></script>\n";
//echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/PBB/mutasi/form-aplikasi.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($application);

$dbServices = new DbServices($dbSpec);

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

function getPropinsi()
{
    global $DBLink;

    $qry = "select * from cppmod_tax_propinsi";
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TP_ID'],
                'name' => $row['CPC_TP_PROPINSI']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function getKabkota($idProv = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idProv) {
        $qwhere = " WHERE CPC_TK_PID='$idProv'";
    }

    $qry = "select * from cppmod_tax_kabkota " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TK_ID'],
                'pid' => $row['CPC_TK_PID'],
                'name' => $row['CPC_TK_KABKOTA']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function getKecamatan($idKec = '', $idKab = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idKab) {
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
    } else if ($idKec) {
        $qwhere = " WHERE CPC_TKC_ID='$idKec'";
    }

    $qry = "select * from cppmod_tax_kecamatan " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TKC_ID'],
                'pid' => $row['CPC_TKC_KKID'],
                'name' => $row['CPC_TKC_KECAMATAN']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function getKelurahan($idKel = '', $idKec = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idKec) {
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
    } else if ($idKel) {
        $qwhere = " WHERE CPC_TKL_ID='$idKel'";
    }

    $qry = "select * from cppmod_tax_kelurahan " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TKL_ID'],
                'pid' => $row['CPC_TKL_KCID'],
                'name' => $row['CPC_TKL_KELURAHAN']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function formPenerimaan($initData)
{
    global $a, $m, $arConfig, $appConfig, $nobutton, $readonly;

    $today = date("d-m-Y");

    $cityID = $appConfig["KODE_KOTA"];
    $cityName = $appConfig["NAMA_KOTA"];
    $optionCityOP = "<option valued=$cityID>$cityName</option>";

    $provID = $appConfig["KODE_PROVINSI"];
    $provName = $appConfig["NAMA_PROVINSI"];
    $optionProvOP = "<option valued=$provID>$provName</option>";


    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

    $optionProvWP = "";

    if ($initData['CPM_ID'] != '') {
        if ($initData['CPM_CP_ID'] != '') {
            $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
        }

        //$kabkotaOP = getKabkota($appConfig["KODE_PROVINSI"]);
        $kecOP = getKecamatan($initData['CPM_OP_KECAMATAN']);
        $kelOP = getKelurahan($initData['CPM_OP_KELURAHAN']);

        foreach ($kecOP as $row) {
            if ($initData['CPM_OP_KECAMATAN'] == $row['id'])
                $optionKecOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kelOP as $row) {
            if ($initData['CPM_OP_KELURAHAN'] == $row['id'])
                $optionKelOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
    }

    $html = "
    <style>
    #main-content {
        width: 1100px;
    }
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }

    </style>
    <script language=\"javascript\">
        $(document).ready(function(){
            $( \"input:submit, input:button\").button();
			$(\"#form-penerimaan\").submit(function(e){
				ids = 0;
				$.each($(\".attach:checked\"), function() {
					ids +=  parseInt($(this).val());
				});
				
				$(\"#attachment\").val(ids);
			});
		
			$('#tglMasuk').datepicker({dateFormat: 'dd-mm-yy'});
			
			var jenisBerkas = new Array();
            jenisBerkas[0] = new Array(1,2,3,5,6);
            jenisBerkas[1] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[2] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[3] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[4] = new Array(1,3,5,6,8,9);
            jenisBerkas[5] = new Array(1,3,5,6,8,9);
            jenisBerkas[6] = new Array(4,12,13,10);            
            jenisBerkas[7] = new Array(1,2,3,5,6,7,8);         
            jenisBerkas[8] = new Array(1,3,5,6,8,9);
            jenisBerkas[9] = new Array(1,3,5,6,8,9,10);";

    if ($initData['CPM_TYPE'] != '')
        $html .= "
				var berkas = jenisBerkas[" . $initData['CPM_TYPE'] . "-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
			";


    $html .= "//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });

            $(\"#form-penerimaan\").validate({
                rules : {
                    nmKuasa : \"required\",
                    tglMasuk : \"required\",
                    nop : {
                            required : true,
                            number : true
                          }                    
                },
                messages : {
                    nmKuasa : \"\",
                    tglMasuk : \"\",
                    nop : \"\"
                }
            });
            
            $('#propinsi').change(function(){
                getWilayah(1,$(this).val());
                $('#kecamatan').html(\"<option value=''>--Pilih Kabupaten Dulu--</option>\");
                $('#kelurahan').html(\"<option value=''>--Pilih Kecamatan Dulu--</option>\");
            });
            
            $('#kabupaten').change(function(){
                getWilayah(2,$(this).val());
            });
            
            $('#kecamatan').change(function(){
                getWilayah(3,$(this).val());
            });
            
            $('#kecamatanOP').change(function(){
                $.ajax({
                   type: 'POST',
                   url: './function/PBB/loket/svc-search-city.php',
                   data: 'type=3&id='+$(this).val(),
                   success: function(msg){
                        $('#kelurahanOP').html(msg);
                   }
                 });
            });
            
            function getWilayah(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/PBB/loket/svc-search-city.php',
                   data: 'type='+type+'&id='+val,
                   success: function(msg){
                        var data = msg.split('|');                        
                        switch(type){
                            case 1 : $('#kabupaten').html(data[0]);
                                     $('#kecamatan').html(data[1]);
                                     $('#kelurahan').html(data[2]);
                                     break;
                            case 2 : $('#kecamatan').html(data[0]);
                                     $('#kelurahan').html(data[1]);
                                     break;
                            case 3 : $('#kelurahan').html(msg);break;
                        }
                   }
                 });
            }
        })
    </script>
<div class=\"col-md-12\">
    <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\" style=\"max-width:990px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f\">
        $hiddenModeInput
        <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
        <table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
            <tr>
                <td colspan=\"4\"><strong><font size=\"+2\">Penerimaan Berkas Penghapusan PBB-P2</font></strong><br /><hr><br /></td>
            </tr>
            <tr>
                <td width=\"50%\" style=\"padding-right:15px\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">A. DATA WAJIB PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">1.</div></td>
                            <td width=\"9%\">Nomor</td>
                            <td width=\"10%\">
                                <input type=\"hidden\" name=\"nomor\" value=\"" . ($initData['CPM_ID']) . "\">
                                <input type=\"hidden\" name=\"nopno\" value=\"" . ($initData['CPM_OP_NUMBER']) . "\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nomor\" id=\"nomor\" size=\"24\" maxlength=\"50\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">2.</div></td>
                            <td width=\"9%\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_REPRESENTATIVE'] != '') ? $initData['CPM_REPRESENTATIVE'] : '') . "\" placeholder=\"Nama Kuasa\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">3.</div></td>
                            <td width=\"9%\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmWp\" id=\"nmWp\" readonly=\"readonly\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_NAME'] != '') ? $initData['CPM_WP_NAME'] : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">4.</div></td>
                            <td width=\"9%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . (($initData['CPM_DATE_RECEIVE'] != '') ? $initData['CPM_DATE_RECEIVE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">5.</div></td>
                            <td width=\"9%\"><label for=\"almtWP\">Alamat WP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"almtWP\" id=\"almtWP\" readonly=\"readonly\" size=\"35\" maxlength=\"500\" value=\"" . (($initData['CPM_WP_ADDRESS'] != '') ? $initData['CPM_WP_ADDRESS'] : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">6.</div></td>
                            <td width=\"9%\"><label for=\"rtWP\">RT/RW</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rtWP\" id=\"rtWP\" readonly=\"readonly\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RT'] != '') ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"00\"/>&nbsp;/
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rwWP\" id=\"rwWP\" readonly=\"readonly\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RW'] != '') ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">7.</div></td>
                            <td width=\"9%\"><label for=\"propinsi\">Provinsi</label></td>
                            <td width=\"10%\">
                                <input type=\"text\" " . (($readonly) ? 'disabled' : null) . " name=\"propinsi\" id=\"propinsi\" readonly=\"readonly\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_PROVINCE'] != '') ? $initData['CPM_WP_PROVINCE'] : '') . "\" placeholder=\"Provinsi\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">8.</div></td>
                            <td width=\"9%\"><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input type=\"text\" " . (($readonly) ? 'disabled' : null) . " name=\"kabupaten\" id=\"kabupaten\" readonly=\"readonly\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_KABUPATEN'] != '') ? $initData['CPM_WP_KABUPATEN'] : '') . "\" placeholder=\"Kabupaten\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">9.</div></td>
                            <td width=\"9%\"><label for=\"kecamatan\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input type=\"text\" " . (($readonly) ? 'disabled' : null) . " name=\"kecamatan\" id=\"kecamatan\" readonly=\"readonly\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_KECAMATAN'] != '') ? $initData['CPM_WP_KECAMATAN'] : '') . "\" placeholder=\"Kecamatan\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">10.</div></td>
                            <td width=\"9%\"><label for=\"kelurahan\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                            <td width=\"10%\">
                                <input type=\"text\" " . (($readonly) ? 'disabled' : null) . " name=\"kelurahan\" id=\"kelurahan\" readonly=\"readonly\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_KELURAHAN'] != '') ? $initData['CPM_WP_KELURAHAN'] : '') . "\" placeholder=\"" . $appConfig['LABEL_KELURAHAN'] . "\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">11.</div></td>
                            <td width=\"9%\">No. HP WP</td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"hpWP\" id=\"hpWP\" size=\"15\" maxlength=\"15\" value=\"" . (($initData['CPM_WP_HANDPHONE'] != '') ? $initData['CPM_WP_HANDPHONE'] : '') . "\" placeholder=\"Nomor HP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">12.</div></td>
                            <td width=\"9%\"><label for=\"nop\">NOP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " readonly=\"readonly\" type=\"text\" name=\"nop\" id=\"nop\" size=\"35\" maxlength=\"50\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" placeholder=\"NOP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\" valign=\"top\"><div align=\"right\">&nbsp;</div></td>                                          
                            <td width=\"10%\" valign=\"top\" colspan=\"2\">
                                <p style=\"margin-bottom : 8px\">Kelengkapan Dokumen</p>
                                <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                    <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 1) ? "checked=\"checked\"" : "") . "> Surat Permohonan.</li>
									<li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"2\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 2) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP.</li>
                                    <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 4) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                    <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 16) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Kepemilikan Tanah.</li>
                                    <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 32) ? "checked=\"checked\"" : "") . "> Fotocopi IMB.</li>
                                    <li id=\"berkas7\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"64\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 64) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Pelunasan PBB Tahun Sebelumnya.</li>
                                    <li id=\"berkas8\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"128\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 128) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                                </ol>
                            </td>
                        </tr>
                    </table></td>
                <td width=\"50%\" valign=\"top\" style=\"padding-left:15px\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">B. DATA OBJEK PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">13.</div></td>
                            <td width=\"39%\"><label for=\"almtOP\">Alamat Objek Pajak</label></td>
                            <td width=\"60%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"almtOP\" id=\"almtOP\" readonly=\"readonly\" size=\"35\" maxlength=\"500\" value=\"" . (($initData['CPM_OP_ADDRESS'] != '') ? $initData['CPM_OP_ADDRESS'] : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">14.</div></td>
                            <td width=\"39%\"><label for=\"rtOP\">RT/RW</label></td>
                            <td width=\"60%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rtOP\" id=\"rtOP\" size=\"3\" readonly=\"readonly\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RT'] != '') ? $initData['CPM_OP_RT'] : '') . "\" placeholder=\"00\"/>&nbsp;/
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rwOP\" id=\"rwOP\" size=\"3\" readonly=\"readonly\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RW'] != '') ? $initData['CPM_OP_RW'] : '') . "\" placeholder=\"00\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">15.</div></td>
                            <td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
                            <td width=\"60%\">
                                <select " . (($readonly) ? 'disabled' : null) . " name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">16.</div></td>
                            <td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                            <td width=\"60%\">
                                <select " . (($readonly) ? 'disabled' : null) . " name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">17.</div></td>
                            <td width=\"39%\"><label for=\"kecamatanOP\">Kecamatan</label></td>
                            <td width=\"60%\">
                                <select " . (($readonly) ? 'disabled' : null) . " name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">18.</div></td>
                            <td width=\"39%\"><label for=\"kelurahanOP\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                            <td width=\"60%\">
                                <select " . (($readonly) ? 'disabled' : null) . " name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
                            </td>
                        </tr>
                    </table></td>
            </tr>
            <tr>
                <td colspan=\"4\">&nbsp;</td>
            </tr>";
    $simpan = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
				<hr><br>
                    <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                    &nbsp;
                    <input class=\"btn btn-primary bg-maka\" type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['id_penghapusan']) . "\"' />
                </td>
            </tr>";
    $kirim = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
				<hr><br>
                    <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim ke Verifikasi\" />
                </td>
            </tr>";
    $rekomendasi = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
                    <form method=\"post\">
                        <table border=0 cellpadding=5>
                            <tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
                            <tr><td class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" value=\"y\"> Setuju</label></td><td class=\"tbl-rekomen\">&nbsp;</td></tr>
                            <tr><td valign=\"top\"class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" value=\"n\"> Tolak</label></td>
                                <td class=\"tbl-rekomen\">Alasan<br><textarea name=\"alasan\" cols=70 rows=7></textarea></td></tr>
                            <tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" value=\"" . (($arConfig['usertype'] == 'verifikator') ? 'Submit Verifikasi' : 'Submit Persetujuan') . "\"></td></tr>
                        </table>
                    </form>
                </td>
            </tr>";
    $end = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
        </table>
    </form>
</div>";
    if (!$nobutton) {
        if ($arConfig['usertype'] != "penyetuju") {
            $html .= $simpan;
        }
        if ($arConfig['usertype'] == "pendata") {
            $html .= $kirim;
        } elseif ($arConfig['usertype'] == ("verifikator" || "penyetuju")) {
            $html .= $rekomendasi;
        }
    }
    $html .= $end;
    return $html;
}

function getInitData($id)
{
    global $DBLink;

    $qry = "SELECT * FROM cppmod_pbb_services WHERE CPM_ID = '{$id}'";
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        return mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
            return $row;
        }
    }
}

function save($status)
{
    global $data, $DBLink, $uname, $arConfig, $dbServices, $readonly;

    $today = date("Y-m-d");
    $mode  = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
    $uuid  = c_uuid();
    $nomor = $_REQUEST['nomor'];
    $nopno = $_REQUEST['nopno'];
    if (!$readonly) {
        $bVal['CPM_REPRESENTATIVE'] = mysqli_escape_string($DBLink, $_REQUEST['nmKuasa']);
        $bVal['CPM_WP_NAME']        = mysqli_escape_string($DBLink, $_REQUEST['nmWp']);
        $bVal['CPM_DATE_RECEIVE']   = substr($_REQUEST['tglMasuk'], 6, 4) . '-' . substr($_REQUEST['tglMasuk'], 3, 2) . '-' . substr($_REQUEST['tglMasuk'], 0, 2);
        $bVal['CPM_WP_ADDRESS']     = mysqli_escape_string($DBLink, $_REQUEST['almtWP']);
        $bVal['CPM_WP_RT']          = mysqli_escape_string($DBLink, $_REQUEST['rtWP']);
        $bVal['CPM_WP_RW']          = mysqli_escape_string($DBLink, $_REQUEST['rwWP']);
        $bVal['CPM_WP_PROVINCE']    = $_REQUEST['propinsi'];
        $bVal['CPM_WP_KABUPATEN']   = mysqli_escape_string($DBLink, $_REQUEST['kabupaten']);
        $bVal['CPM_WP_KECAMATAN']   = mysqli_escape_string($DBLink, $_REQUEST['kecamatan']);
        $bVal['CPM_WP_KELURAHAN']   = mysqli_escape_string($DBLink, $_REQUEST['kelurahan']);
        $bVal['CPM_WP_HANDPHONE']   = mysqli_escape_string($DBLink, $_REQUEST['hpWP']);
        $bVal['CPM_OP_NUMBER']      = $_REQUEST['nop'];
        $bVal['CPM_ATTACHMENT']     = $_REQUEST['attachment'];
        $bVal['CPM_OP_ADDRESS']     = $_REQUEST['almtOP'];
        $bVal['CPM_OP_ADDRESS_NO']  = $_REQUEST['almtnoOP'];
        $bVal['CPM_OP_RT']          = $_REQUEST['rtOP'];
        $bVal['CPM_OP_RW']          = $_REQUEST['rwOP'];
        $bVal['CPM_OP_KECAMATAN']   = $_REQUEST['kecamatanOP'];
        $bVal['CPM_OP_KELURAHAN']   = $_REQUEST['kelurahanOP'];
    }
    $bVal['CPM_STATUS'] = $status;
    if ($_REQUEST['btn-save'] == 'Kirim ke Verifikasi') {
        $bVal['CPM_VALIDATOR']     = $uname;
        $bVal['CPM_DATE_VALIDATE'] = $today;
    }
    if ($_REQUEST['btn-save'] == 'Submit Verifikasi') {
        $bVal['CPM_VERIFICATOR']       = $uname;
        $bVal['CPM_DATE_VERIFICATION'] = $today;
    }
    if ($_REQUEST['btn-save'] == 'Submit Persetujuan') {
        $bVal['CPM_APPROVER']      = $uname;
        $bVal['CPM_DATE_APPROVER'] = $today;
    }
    ($_REQUEST['alasan'] != '') ? $bVal['CPM_REFUSAL_REASON'] = $_REQUEST['alasan'] : null;

    $res2 = true;
    if ($status == 4) {
        $filter['CPM_NOP'] = $nopno;
        $rowSppt = $dbServices->getWhereSpptFinal($filter);

        

        $res2 = $dbServices->insertIntoHistory($rowSppt[0]['CPM_SPPT_DOC_ID']);

        if ($res2) {
            $res2 = $dbServices->delSpptFinal($rowSppt[0]['CPM_SPPT_DOC_ID']);
            if ($res2) {
                $res2 = $dbServices->delSpptExt($rowSppt[0]['CPM_SPPT_DOC_ID']);
                if ($res2) {
                    $res2 = $dbServices->delSpptCurrent($nopno);
                }
            }
        }
    }

    $res = true;
    if ($res2) {
        $res = $dbServices->editServices($nomor, $bVal);
    }

    if ($res === false) {
        echo mysqli_error($DBLink);
    }

    if ($res) {
        echo 'Data berhasil disimpan...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_penghapusan'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        echo mysqli_error($DBLink);
    }
}

$save = isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : '';

if ($save == 'Simpan') {
    if ($arConfig['usertype'] == "pendata") {
        save(1);
    } elseif ($arConfig['usertype'] == "verifikator") {
        save(2);
    } elseif ($arConfig['usertype'] == "penyetuju") {
        save(3);
    }
} elseif ($save == 'Kirim ke Verifikasi') {
    save(2);
} elseif ($save == 'Submit Verifikasi') {
    if ($_REQUEST['rekomendasi'] == "y") {
        save(3);
    } elseif ($_REQUEST['rekomendasi'] == "n") {
        save(5);
    }
} elseif ($save == 'Submit Persetujuan') {
    if ($_REQUEST['rekomendasi'] == "y") {
        save(4);
    } elseif ($_REQUEST['rekomendasi'] == "n") {
        save(6);
    }
} else {
    $svcid = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
    $nobutton = @isset($_REQUEST['nobutton']) ? $_REQUEST['nobutton'] : false;
    $readonly = @isset($_REQUEST['readonly']) ? $_REQUEST['readonly'] : false;
    $initData = getInitData($svcid);
    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($initData);
}
