<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'berkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/BPHTB/berkas/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getConfigValue($id, $key) {
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

function formPenerimaan($value) {
    global $a, $m, $appConfig, $arConfig, $DBLink;

    $today = date("d-m-Y");
    $value = explode(",", "CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP,CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,
                CPM_BERKAS_NAMA_WP,CPM_BERKAS_ALAMAT_WP,CPM_BERKAS_STATUS,CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL");

    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";

    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";

    $strJnsPerolehan = "";
    $value['CPM_BERKAS_NOPEL'] = "";
    if (isset($_REQUEST['svcid'])) {
        $query = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID = '{$_REQUEST['svcid']}'";
        $result = mysqli_query($DBLink, $query);
        $value = mysqli_fetch_array($result);

        $jnsPerolehan[1] = ($value['CPM_BERKAS_JNS_PEROLEHAN'] == 1) ? "checked" : "";
        $jnsPerolehan[2] = ($value['CPM_BERKAS_JNS_PEROLEHAN'] == 2) ? "checked" : "";
        $jnsPerolehan[3] = ($value['CPM_BERKAS_JNS_PEROLEHAN'] == 3) ? "checked" : "";
        $jnsPerolehan[4] = ($value['CPM_BERKAS_JNS_PEROLEHAN'] == 4) ? "checked" : "";
        $strJnsPerolehan = $value['CPM_BERKAS_JNS_PEROLEHAN'];

        $lampiran[0] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "1") !== false) ? "checked" : "";
        $lampiran[1] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "2") !== false) ? "checked" : "";
        $lampiran[2] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "3") !== false) ? "checked" : "";
        $lampiran[3] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "4") !== false) ? "checked" : "";
        $lampiran[4] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "5") !== false) ? "checked" : "";
        $lampiran[5] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "6") !== false) ? "checked" : "";
        $lampiran[6] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "7") !== false) ? "checked" : "";
        $lampiran[7] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "8") !== false) ? "checked" : "";
        $lampiran[8] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "9") !== false) ? "checked" : "";
        $lampiran[9] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "10") !== false) ? "checked" : "";
        $lampiran[10] = (strpos($value['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "checked" : "";
    }



    $html = "
    <style>
    #main-content {
        width: 788px;
    }
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }

    </style>
    <script language=\"javascript\">
        jQuery.validator.setDefaults({
            debug: true,
            success: \"valid\"
          });
         $(document).ready(function(){
             var form = $(\"#form-penerimaan\");
             form.validate({
                 rules : {
                         \"nop\" :{
                                    required : true,
                                    digits : true,
                                    },
                         \"alamatOp\" : \"required\",
                         \"kelurahanOp\" : \"required\",
                         \"kecamatanOp\" : \"required\",
                         \"npwp\" : \"required\",
                         \"namaWp\" : \"required\",
                         \"alamatWp\" : \"required\",
                         \"jnsPerolehan\" : \"required\",
                         \"noPel\" : \"required\"
                         },
                  messages : {
                         \"nop\" :{
                                    required : \"harus diisi\",
                                    digits : \"harus berupa angka\",
                                    },
                         \"alamatOp\" : \"harus diisi\",
                         \"kelurahanOp\" : \"harus diisi\",
                         \"kecamatanOp\" : \"harus diisi\",
                         \"npwp\" : \"harus diisi\",
                         \"namaWp\" : \"harus diisi\",
                         \"alamatWp\" : \"harus diisi\",
                         \"jnsPerolehan\":\"harus diisi\",
                         \"noPel\" : \"harus diisi\"
                    }
             });
             
            $(\"#btn-simpan\").click(function(){
                $(\"#process\").val($(this).val());
                if(form.valid()){
                   document.getElementById(\"form-penerimaan\").submit();                   
                }
            });
            
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            enabledJnsPerolehan('#jnsPerolehan" . $strJnsPerolehan . "')
        });
        
        function iniAngka(evt,x){
            x.value=x.value.replace(/[^0-9]+/g, '');
        }
        
        function disabledJnsPerolehan(){            
            $(\".jnsPerolehan input[type='checkbox']\").each(function(){
                $(this).prop('disabled','disabled')
            });
        }

        function enabledJnsPerolehan(id){
            $(id).show();
            $(id+\" input[type='checkbox']\").each(function(){
                $(this).removeAttr('disabled');
            });
        }
        
        function setNoPel(id){
            $.ajax({
                type : 'post',
                data : 'type='+id,
                url: './function/BPHTB/berkas/svc-get-nopel.php',
                success : function(res){
                    $('#noPel').val(res);
                }
            });
        }
        
        function showJnsPerolehan(obj){
            var id = obj.value;
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            setNoPel(id);
            $(\"#jnsPerolehan\"+id).show();
            enabledJnsPerolehan(\"#jnsPerolehan\"+id);            
        }
    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
        <input type=\"hidden\" name=\"process\" id=\"process\">
        <input type=\"hidden\" name=\"idssb\" id=\"idssb\" value=\"{$value['CPM_BERKAS_ID']}\">
	<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
              <tr>
                <td colspan=\"2\"><strong><font size=\"+2\">Penerimaan Berkas Pelayanan BPHTB</font></strong><br /><hr><br /></td>
              </tr>
                  <tr><td colspan=\"2\"><h3>A. DATA OBJEK PAJAK</h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"noPel\">Nomor Pelaporan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"noPel\" readonly id=\"noPel\" style=\"text-align:right\" value=\"{$value['CPM_BERKAS_NOPEL']}\" size=\"30\" maxlength=\"50\" placeholder=\"Nomor Pelayanan\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . (($value['CPM_BERKAS_TANGGAL'] != '') ? $value['CPM_BERKAS_TANGGAL'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"sptpd\">NOP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" " . (isset($_REQUEST['svcid']) ? "readonly" : "") . "  maxlength=\"22\" onblur=\"return iniAngka(event,this)\" onkeypress=\"return iniAngka(event,this)\" value=\"{$value['CPM_BERKAS_NOP']}\" placeholder=\"NOP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"alamatOp\">Alamat Objek Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"alamatOp\" id=\"alamatOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_ALAMAT_OP']}\" placeholder=\"Alamat Objek Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"kelurahanOp\">Kelurahan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kelurahanOp\" id=\"kelurahanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KELURAHAN_OP']}\" placeholder=\"Kelurahan Objek Pajak\" />
                        </td>
                      </tr>  
                      <tr>
                        <td width=\"39%\"><label for=\"kecamatanOp\">Kecamatan *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"kecamatanOp\" id=\"kecamatanOp\" size=\"50\" maxlength=\"70\" value=\"{$value['CPM_BERKAS_KECAMATAN_OP']}\" placeholder=\"Kecamatan Objek Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>B. DATA WAJIB PAJAK</h3></td></tr>                                                                        
                      <tr>
                        <td width=\"39%\"><label for=\"npwp\">NPWP / KTP *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"npwp\" id=\"npwp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NPWP']}\" placeholder=\"NPWP / KTP\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"namaWp\">Nama Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"namaWp\" id=\"namaWp\" size=\"50\" maxlength=\"50\" value=\"{$value['CPM_BERKAS_NAMA_WP']}\" placeholder=\"Nama Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\"><label for=\"telpWp\">Nomor Telp Wajib Pajak *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"telpWp\" id=\"telpWp\" size=\"50\" maxlength=\"20\" value=\"{$value['CPM_BERKAS_TELP_WP']}\" placeholder=\"Nomor Telp Wajib Pajak\" />
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3></h3></td></tr>
                      <tr>
                        <td width=\"39%\"><label for=\"hargaTran\">Harga Transaksi *</label></td>
                        <td width=\"60%\">
                          <input type=\"text\" name=\"hargaTran\" style=\"text-align:right\" id=\"hargaTran\" size=\"50\" maxlength=\"12\" value=\"{$value['CPM_BERKAS_HARGA_TRAN']}\" placeholder=\"Harga Transaksi\" />
                        </td>
                      </tr>
                      <tr valign=\"top\">
                        <td width=\"39%\"><label for=\"jnsPerolehan\">Jenis Perolehan *</label></td>
                        <td width=\"60%\">
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"1\" {$jnsPerolehan[1]} onclick=\"javascript:showJnsPerolehan(this)\"/> SK<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"2\" {$jnsPerolehan[2]} onclick=\"javascript:showJnsPerolehan(this)\"/> JUAL-BELI<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"3\" {$jnsPerolehan[3]} onclick=\"javascript:showJnsPerolehan(this)\"/> HIBAH<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"4\" {$jnsPerolehan[4]} onclick=\"javascript:showJnsPerolehan(this)\"/> WARIS<br/>
                        </td>
                      </tr>
                      <tr>
                        <td width=\"39%\">Persyaratan Administrasi :</td>
                        <td width=\"60%\">
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan1\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" {$lampiran[0]}> Photocopy KTP</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" {$lampiran[1]}> Photocopy SK BPN</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"3\" {$lampiran[2]}> Photocopy SPPT PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" {$lampiran[3]}> Photocopy Bukti Lunas PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"5\" {$lampiran[4]}> Surat Pernyataan Bermaterai</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"6\" {$lampiran[5]}> Surat Kuasa Bermaterai (Bila Dikuasakan)</li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan2\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" {$lampiran[0]}> Photocopy KTP Pembeli dan Penjual</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" {$lampiran[1]}> Photocopy Sertifikat Tanah</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"3\" {$lampiran[2]}> Photocopy SPPT PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" {$lampiran[3]}> Photocopy Bukti Lunas PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"5\" {$lampiran[4]}> Surat Pernyataan Bermaterai</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"6\" {$lampiran[5]}> Surat Kuasa Bermaterai (Bila Dikuasakan)</li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan3\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" {$lampiran[0]}> Photocopy KTP Pemberi Hibah dan Penerima Hibah</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" {$lampiran[1]}> Photocopy Sertifikat Tanah</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"3\" {$lampiran[2]}> Photocopy SPPT PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" {$lampiran[3]}> Photocopy Bukti Lunas PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"5\" {$lampiran[4]}> Photocopy Surat Pernyataan Hibah</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"6\" {$lampiran[5]}> Photocopy Kartu Keluarga</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"7\" {$lampiran[6]}> Photocopy Akte Kelahiran</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" {$lampiran[7]}> Surat Pernyataan Bermaterai</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"9\" {$lampiran[8]}> Surat Kuasa Bermaterai (Bila Dikuasakan)</li>
                            </ol>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan4\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" {$lampiran[0]}> Photocopy Ahli Waris</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" {$lampiran[1]}> Photocopy Sertifikat Tanah</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"3\" {$lampiran[2]}> Photocopy SPPT PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" {$lampiran[3]}> Photocopy Bukti Lunas PBB Tahun Terhutang</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"5\" {$lampiran[4]}> Photocopy Surat Keterangan Waris</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"6\" {$lampiran[5]}> Photocopy Surat Kuasa Waris</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"7\" {$lampiran[6]}> Photocopy Surat Kematian</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" {$lampiran[7]}> Surat Pernyataan Bermaterai</li>
                                <li><input type=\"checkbox\" name=\"lampiran[]\" value=\"9\" {$lampiran[8]}> Surat Kuasa Bermaterai (Bila Dikuasakan)</li>
                            </ol>
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>&nbsp;</h3></td></tr>                                                                        
                      <tr>
                        <td width=\"100%\" colspan=\"2\" valign=\"top\" align=\"center\">";
    $html .= (isset($_REQUEST['svcid'])) ? "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Update\" />&nbsp;" : "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" />&nbsp;";

    $html.= "<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"' />
                </td>
                      </tr>
                </table>
                </td>
              </tr>
              <tr>
                <td colspan=\"2\">&nbsp;</td>
              </tr>                        
              <tr>
                <td colspan=\"2\" align=\"center\" valign=\"middle\"></td>
            </tr>
            <tr>
              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
      </table>
    </form></div>";
    return $html;
}

function save($status) {
    global $data, $DBLink, $uname;
    $lampiran = implode(";", $_POST['lampiran']);
    $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9);
    $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;

    $qry = sprintf("INSERT INTO cppmod_ssb_berkas (
            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_LAMPIRAN,
            CPM_BERKAS_PETUGAS,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
             CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL,CPM_BERKAS_STATUS, 
            CPM_BERKAS_HARGA_TRAN, CPM_BERKAS_TELP_WP            
            ) VALUES ('%s','%s','%s',
                    '%s','%s','%s',                    
                    '%s','%s','%s',
                    '%s','%s',{$status},
                    '%s','%s')", mysqli_escape_string($_POST['nop']), mysqli_escape_string($_POST['tglMasuk']), $lampiran, mysqli_escape_string($_SESSION['username']), mysqli_escape_string($_POST['alamatOp']), mysqli_escape_string($_POST['kelurahanOp']), mysqli_escape_string($_POST['kecamatanOp']), mysqli_escape_string($_POST['npwp']), mysqli_escape_string($_POST['namaWp']), mysqli_escape_string($_POST['jnsPerolehan']), mysqli_escape_string($_POST['noPel']), mysqli_escape_string($_POST['hargaTran']), mysqli_escape_string($_POST['telpWp']));

    $res = mysqli_query($DBLink, $qry);
    if ($res) {
        echo 'Data berhasil disimpan...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        $err = mysqli_error($DBLink);
        echo strpos(strtolower($err), "duplicate") === false ? $err : "Ada kesalahan! dokumen sudah pernah diinput! data sudah tersedia dan silakan periksa pada tabel.";
    }
}

function update($status) {
    global $data, $DBLink, $uname;

    $lampiran = implode(";", $_POST['lampiran']);
    $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9);
    $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;

    $qry = sprintf("UPDATE cppmod_ssb_berkas SET        
            CPM_BERKAS_NOPEL = '" . mysqli_escape_string($_POST['noPel']) . "',
            CPM_BERKAS_JNS_PEROLEHAN = '{$_POST['jnsPerolehan']}',
            CPM_BERKAS_LAMPIRAN ='{$lampiran}',
            CPM_BERKAS_PETUGAS = '" . mysqli_escape_string($_SESSION['username']) . "',
                
            CPM_BERKAS_NOP = '" . mysqli_escape_string($_POST['nop']) . "',
            CPM_BERKAS_ALAMAT_OP = '" . mysqli_escape_string($_POST['alamatOp']) . "',
            CPM_BERKAS_KELURAHAN_OP = '" . mysqli_escape_string($_POST['kelurahanOp']) . "', 
            CPM_BERKAS_KECAMATAN_OP = '" . mysqli_escape_string($_POST['kecamatanOp']) . "',
            CPM_BERKAS_NPWP = '" . mysqli_escape_string($_POST['npwp']) . "',
            CPM_BERKAS_NAMA_WP = '" . mysqli_escape_string($_POST['namaWp']) . "',  
            
            CPM_BERKAS_HARGA_TRAN = '" . mysqli_escape_string($_POST['hargaTran']) . "',
            CPM_BERKAS_TELP_WP = '" . mysqli_escape_string($_POST['telpWp']) . "',

            CPM_BERKAS_STATUS = '{$status}'
            WHERE CPM_BERKAS_ID = '" . mysqli_escape_string($_POST['idssb']) . "'");

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }

    if ($res) {
        echo 'Data berhasil diupdate...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        echo mysqli_error($DBLink);
    }
}

$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($m);
$save = $_REQUEST['process'];

if ($save == 'Simpan') {
    save();
} elseif ($save == 'Update') {
    update();
} else {
    $svcid = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";

    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($value);
}
?>

