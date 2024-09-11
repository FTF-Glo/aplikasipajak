<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");

require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbWajibPajak.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";


echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($application);

$dbServices = new DbServices($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);

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

function getPropinsi() {
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

function getKabkota($idProv = "") {
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

function getKecamatan($idKab = "") {
    global $DBLink;

    $qwhere = "";
    if ($idKab) {
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
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

function getKelurahan($idKec = "") {
    global $DBLink;

    $qwhere = "";
    if ($idKec) {
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
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

function formPenerimaan($initData) {
    global $a, $m, $arConfig, $appConfig, $nobutton, $readonly;

    $today = date("d-m-Y");
    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = "";
	
	$bSlash = "\'";
	$ktip = "'";

    $optionProvWP = "";
    $optionProvMU = "";
    $hiddenModeInput = null;

    if ($initData['CPM_ID'] != '') {
        if ($initData['CPM_MU_ID'] != '') {
            $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
        }

    }


    // ((isset($initData['CPM_MU_NO_KTP']) && $initData['CPM_MU_NO_KTP'] != '') ? $initData['CPM_MU_NO_KTP'] : ((isset($initData['CPM_WP_NO_KTP']) && $initData['CPM_WP_NO_KTP'] != '') ? $initData['CPM_WP_NO_KTP'] : ))
    // echo '<pre>';
    // print_r($initData);
    // var_dump(((isset($initData['CPM_MU_NO_KTP']) && $initData['CPM_MU_NO_KTP'] != '') ? $initData['CPM_MU_NO_KTP'] : ''));
    // echo '</pre>';
    // exit;

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
    <div id=\"modalDialog\"></div>
    <script language=\"javascript\">
		$(\"#modalDialog\").dialog({
            autoOpen: false,
            modal: true,
            width: 900,
            resizable: false,
            draggable: false,
            height: 'auto',
            title: '',
            position: ['middle', 20]
        });
        
		function displayFormWp(id){
			$(\"#modalDialog\").dialog('open');
			$(\"#modalDialog\").load(\"function/PBB/nop/wp/form-edit-dialog.php?id=\"+id+\"&a={$a}&case=from_loket\");
		}
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
                        $('#tglMutasi').datepicker({dateFormat: 'dd-mm-yy'});
			
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
        $html .="
				var berkas = jenisBerkas[" . $initData['CPM_TYPE'] . "-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
			";


    $html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });

            $(\"#form-penerimaan\").validate({
                rules : {
                    nmKuasa : \"required\",
                    tglMasuk : \"required\",
                    almtWP : \"required\",
                    nop : {
                            required : true,
                            number : true
                          },
                    nmMutasi : \"required\",
                    almtMutasi : \"required\",
                    rtMutasi : {
                            required : true,
                            number : true
                          },
                    rwMutasi : {
                            required : true,
                            number : true
                          },
                    spptTahunRubah : {
                            required : true,
                            number : true
                          },
                    spptTahun : {
                            required : true,
                            number : true
                          },
                    pajakTerutang : {
                            required : true,
                            number : true
                          },
                    statusMilik : \"required\",      
                    pekerjaan : \"required\",
                    kodepos : \"required\",
                    noktp : \"required\",
                    propinsiMutasi : \"required\",
                    kabupatenMutasi : \"required\",
                    kecamatanMutasi : \"required\",
                    kelurahanMutasi : \"required\"
                },
                messages : {
                    nmKuasa : \"\",
                    tglMasuk : \"\",
                    nop : \"\",
                    nmMutasi : \"\",
                    almtMutasi : \"\",
                    rtMutasi : \"\",
                    rwMutasi : \"\",
                    spptTahunRubah : \"\",
                    spptTahun : \"\",
                    pajakTerutang : \"\",
                    statusMilik : \"pilih\",      
                    pekerjaan : \"pilih\",
                    kodepos : \"\",
                    noktp : \"\",
                    propinsiMutasi : \"\",
                    kabupatenMutasi : \"\",
                    kecamatanMutasi : \"\",
                    kelurahanMutasi : \"\"
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
        
            $('#propinsiMutasi').change(function(){
                getWilayahMutasi(1,$(this).val());
                $('#kecamatanMutasi').html(\"<option value=''>--Pilih Kabupaten Dulu--</option>\");
                $('#kelurahanMutasi').html(\"<option value=''>--Pilih Kecamatan Dulu--</option>\");
            });
            
            $('#kabupatenMutasi').change(function(){
                getWilayahMutasi(2,$(this).val());
            });
            
            $('#kecamatanMutasi').change(function(){
                getWilayahMutasi(3,$(this).val());
            });
            
            function getWilayahMutasi(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/PBB/loket/svc-search-city.php',
                   data: 'type='+type+'&id='+val,
                   success: function(msg){
                        var data = msg.split('|');                        
                        switch(type){
                            case 1 : $('#kabupatenMutasi').html(data[0]);
                                     $('#kecamatanMutasi').html(data[1]);
                                     $('#kelurahanMutasi').html(data[2]);
                                     break;
                            case 2 : $('#kecamatanMutasi').html(data[0]);
                                     $('#kelurahanMutasi').html(data[1]);
                                     break;
                            case 3 : $('#kelurahanMutasi').html(msg);break;
                        }
                   }
                 });
            }
            
        })
        
        function trim(str) {
            return str.replace(/^\s+|\s+$/g, '');
        }
    
        function cekWP(evt, x) {
            if(trim(x.value) == '') {
                var nop = document.getElementById(\"nop\").value;
                x.value = nop;
            }else{

                var noktp = x.value;
                document.getElementById(\"div-loadwp-wait\").innerHTML = '<img src=\"image/icon/loadinfo.net.gif\"/>';
                var params = \"{'noktp' : '\" + noktp + \"'}\";
                params = Base64.encode(params);
                Ext.Ajax.request({
                    url: 'inc/PBB/svc-noktp.php',
                    params: {req: params},
                    success: function(res) {
                        document.getElementById(\"div-loadwp-wait\").innerHTML = \"\";
                        var json = Ext.decode(res.responseText);
                        $(\"#div-tmbahwp\").html('');
                        $(\"input[name=pekerjaan]\").attr('disabled', true).attr('checked', false);
                        if (json.r == true) {
                            $(\"input[name=pekerjaan][value=\" + json.CPM_WP_PEKERJAAN + \"]\").attr('checked', 'checked');
                            document.getElementById(\"nmMutasi\").value = json.CPM_WP_NAMA;
                            document.getElementById(\"almtMutasi\").value = json.CPM_WP_ALAMAT;
                            document.getElementById(\"rtMutasi\").value = json.CPM_WP_RT;
                            document.getElementById(\"rwMutasi\").value = json.CPM_WP_RW;
                            document.getElementById(\"propinsiMutasi\").value = json.CPM_WP_PROPINSI;
                            document.getElementById(\"kabupatenMutasi\").value = json.CPM_WP_KOTAKAB;
                            document.getElementById(\"kecamatanMutasi\").value = json.CPM_WP_KECAMATAN;
                            document.getElementById(\"kelurahanMutasi\").value = json.CPM_WP_KELURAHAN;
                            document.getElementById(\"kodepos\").value = json.CPM_WP_KODEPOS;
                            $(\"#div-tmbahwp\").html(\"<a href=javascript:displayFormWp('\"+noktp+\"')>Edit WP?</a>\");
                            alert('No KTP Ditemukan');
                        } else {
                            alert('NO KTP Tidak Ditemukan');
                            document.getElementById(\"nmMutasi\").value = '';
                            document.getElementById(\"almtMutasi\").value = '';
                            document.getElementById(\"rtMutasi\").value = '';
                            document.getElementById(\"rwMutasi\").value = '';
                            document.getElementById(\"propinsiMutasi\").value = '';
                            document.getElementById(\"kabupatenMutasi\").value = '';
                            document.getElementById(\"kecamatanMutasi\").value = '';
                            document.getElementById(\"kelurahanMutasi\").value = '';
                            document.getElementById(\"kodepos\").value = '';
                            $(\"#div-tmbahwp\").html(\"<a href=javascript:displayFormWp('\"+noktp+\"')>No KTP tidak ditemukan, Input WP Baru?</a>\");
                        }
                    },
                    failure: function(res) {
                        document.getElementById(\"div-loadwp-wait\").innerHTML = \"\";
                        alert('Pengecekan No KTP Gagal!');
                    }
                });
            }
        }
    </script>
<div class=\"col-md-12\">
    <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\" style=\"background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f\">
        $hiddenModeInput
        <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
        <table class=\"table table-borderless\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
            <tr>
                <td colspan=\"4\"><strong><font size=\"+2\">Penerimaan Berkas Mutasi PBB-P2</font></strong><br /><hr><br /></td>
            </tr>
            <tr>
                <td width=\"1%\" align=\"center\">&nbsp;</td>
                <td width=\"49%\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">A. DATA WAJIB PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">1.</div></td>
                            <td width=\"9%\">Nomor</td>
                            <td width=\"10%\">
                                <input type=\"hidden\" name=\"nomor\" value=\"" . ($initData['CPM_ID']) . "\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nomor\" id=\"nomor\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">2.</div></td>
                            <td width=\"9%\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_REPRESENTATIVE'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_REPRESENTATIVE']) : '') . "\" placeholder=\"Nama Kuasa\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">3.</div></td>
                            <td width=\"9%\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmWp\" id=\"nmWp\" readonly=\"readonly\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_NAME'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_NAME']) : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">4.</div></td>
                            <td width=\"9%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . (($initData['CPM_DATE_RECEIVE'] != '') ? $initData['CPM_DATE_RECEIVE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">5.</div></td>
                            <td width=\"9%\"><label for=\"almtWP\">Alamat WP</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"almtWP\" id=\"almtWP\" readonly=\"readonly\" size=\"40\" maxlength=\"500\" value=\"" . (($initData['CPM_WP_ADDRESS'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_ADDRESS']) : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">6.</div></td>
                            <td width=\"9%\"><label for=\"rtWP\">RT/RW</label></td>
                            <td width=\"10%\">
                                <div style=\"display:flex\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rtWP\" id=\"rtWP\" readonly=\"readonly\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RT'] != '') ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"00\"/>
                                <span style=\"margin:10px 10px 0 10px\">/</span>
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rwWP\" id=\"rwWP\" readonly=\"readonly\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RW'] != '') ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">7.</div></td>
                            <td width=\"9%\"><label for=\"propinsi\">Provinsi</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"propinsi\" readonly=\"readonly\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_PROVINCE'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_PROVINCE']) : '') . "\" placeholder=\"Provinsi\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">8.</div></td>
                            <td width=\"9%\"><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kabupaten\" readonly=\"readonly\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KABUPATEN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KABUPATEN']) : '') . "\" placeholder=\"Kabupaten\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">9.</div></td>
                            <td width=\"9%\"><label for=\"kecamatan\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kecamatan\" readonly=\"readonly\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KECAMATAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KECAMATAN']) : '') . "\" placeholder=\"Kecamatan\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">10.</div></td>
                            <td width=\"9%\"><label for=\"kelurahan\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kelurahan\" readonly=\"readonly\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KELURAHAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KELURAHAN']) : '') . "\" placeholder=\"".$appConfig['LABEL_KELURAHAN']."\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">11.</div></td>
                            <td width=\"9%\">No. HP WP</td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"hpWP\" id=\"hpWP\" readonly=\"readonly\" size=\"15\" maxlength=\"15\" value=\"" . (($initData['CPM_WP_HANDPHONE'] != '') ? $initData['CPM_WP_HANDPHONE'] : '') . "\" placeholder=\"Nomor HP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">12.</div></td>
                            <td width=\"9%\"><label for=\"nop\">NOP</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " readonly=\"readonly\" type=\"text\" name=\"nop\" id=\"nop\" readonly=\"readonly\" size=\"40\" maxlength=\"18\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" placeholder=\"NOP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">13.</div></td>
                            <td width=\"9%\"><label for=\"spptTahun\">SPPT Tahun</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"spptTahun\" id=\"spptTahun\" readonly=\"readonly\" size=\"4\" maxlength=\"4\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : '') . "\" placeholder=\"0000\"/>                    
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">14.</div></td>
                            <td width=\"9%\">Jumlah Pajak Terutang</td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"pajakTerutang\" id=\"pajakTerutang\" readonly=\"readonly\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_SPPT_DUE'] != '') ? $initData['CPM_SPPT_DUE'] : '') . "\" placeholder=\"0\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">15.</div></td>
                            <td width=\"9%\"><label for=\"tglTerimaSPPT\">Tanggal Bayar SPPT</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglTerimaSPPT\" id=\"tglTerimaSPPT\" readonly=\"readonly\" value=\"" . (($initData['CPM_SPPT_PAYMENT_DATE'] != '') ? $initData['CPM_SPPT_PAYMENT_DATE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\" valign=\"top\"><div align=\"right\">&nbsp;</div></td>                                          
                            <td width=\"10%\" valign=\"top\" colspan=\"2\">
                                <p style=\"margin-bottom : 8px\">Kelengkapan Dokumen</p>
                                <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                    <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"1\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 1) ? "checked=\"checked\"":"")."> Surat Permohonan.</li>
                                    <li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"2\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 2) ? "checked=\"checked\"":"")."> Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP.</li>
                                    <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"4\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 4) ? "checked=\"checked\"":"")."> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                    <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"16\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 16) ? "checked=\"checked\"":"")."> Fotocopi Bukti Kepemilikan Tanah.</li>
                                    <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"32\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 32) ? "checked=\"checked\"":"")."> Fotocopi IMB.</li>
                                    <li id=\"berkas7\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"64\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 64) ? "checked=\"checked\"":"")."> Fotocopi Bukti Pelunasan PBB Tahun Sebelumnya.</li>
                                    <li id=\"berkas8\" class=\"berkas\" ><input type=\"checkbox\" " . (($readonly) ? 'disabled' : null) . " name=\"lampiran[]\" value=\"128\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 128) ? "checked=\"checked\"":"")."> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                                </ol>
                            </td>
                        </tr>
                    </table></td>
                <td width=\"1%\" align=\"center\">&nbsp;</td>
                <td width=\"49%\" valign=\"top\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">B. DATA PENERIMA MUTASI</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">16.</div></td>
                            <td width=\"9%\"><label for=\"tglMutasi\">Tanggal Mutasi</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglMutasi\" id=\"tglMutasi\" readonly=\"readonly\" value=\"" . (($initData['CPM_MU_DATE'] != '') ? $initData['CPM_MU_DATE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">17.</div></td>
                            <td width=\"9%\"><label>Nomor KTP</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"noktp\" id=\"noktp\" size=\"40\" onblur=\"return cekWP(event, this);\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_MU_NO_KTP']) && $initData['CPM_MU_NO_KTP'] != '') ? $initData['CPM_MU_NO_KTP'] : ((isset($initData['CPM_WP_NO_KTP']) && $initData['CPM_WP_NO_KTP'] != '') ? $initData['CPM_WP_NO_KTP'] : '' )) . "\" placeholder=\"No KTP\" />
                                <span id=\"div-loadwp-wait\"></span>
                                <span id=\"div-tmbahwp\">".((isset($initData['CPM_MU_NO_KTP']) && $initData['CPM_MU_NO_KTP'] != '') ? "<a href=javascript:displayFormWp('{$initData['CPM_MU_NO_KTP']}')>Edit WP?</a>" : ((isset($initData['CPM_WP_NO_KTP']) && $initData['CPM_WP_NO_KTP'] != '')  ? "<a href=javascript:displayFormWp('{$initData['CPM_WP_NO_KTP']}')>Edit WP?</a>" : ''))."</span>
                            </td>
                        </tr>
                        <tr>
                            <td valign=\"top\"><div align=\"right\">18.</div></td>
                            <td>
                                Status<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemilik\" " . ((isset($initData['CPM_MU_STATUS']) && $initData['CPM_MU_STATUS'] == 'Pemilik') ? 'checked' : '') . "> Pemilik</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Penyewa\" " . ((isset($initData['CPM_MU_STATUS']) && $initData['CPM_MU_STATUS'] == 'Penyewa') ? 'checked' : '') . "> Penyewa</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pengelola\" " . ((isset($initData['CPM_MU_STATUS']) && $initData['CPM_MU_STATUS'] == 'Pengelola') ? 'checked' : '') . "> Pengelola</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemakai\" " . ((isset($initData['CPM_MU_STATUS']) && $initData['CPM_MU_STATUS'] == 'Pemakai') ? 'checked' : '') . "> Pemakai</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Sengketa\" " . ((isset($initData['CPM_MU_STATUS']) && $initData['CPM_MU_STATUS'] == 'Sengketa') ? 'checked' : '') . "> Sengketa</label><br>
                            </td>
                            <td valign=\"top\">
                                Pekerjaan<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"PNS\" " . ((isset($initData['CPM_MU_PEKERJAAN']) && $initData['CPM_MU_PEKERJAAN'] == 'PNS') ? 'checked' : 'disabled') . "> PNS</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"TNI\" " . ((isset($initData['CPM_MU_PEKERJAAN']) && $initData['CPM_MU_PEKERJAAN'] == 'TNI') ? 'checked' : 'disabled') . "> TNI</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Pensiunan\" " . ((isset($initData['CPM_MU_PEKERJAAN']) && $initData['CPM_MU_PEKERJAAN'] == 'Pensiunan') ? 'checked' : 'disabled') . "> Pensiunan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Badan\" " . ((isset($initData['CPM_MU_PEKERJAAN']) && $initData['CPM_MU_PEKERJAAN'] == 'Badan') ? 'checked' : 'disabled') . "> Badan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Lainnya\" " . ((isset($initData['CPM_MU_PEKERJAAN']) && $initData['CPM_MU_PEKERJAAN'] == 'Lainnya') ? 'checked' : 'disabled') . "> Lainnya</label><br>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">19.</div></td>
                            <td width=\"9%\"><label for=\"nmMutasi\">Nama</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . "  type=\"text\" name=\"nmMutasi\" id=\"nmMutasi\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_MU_NAME'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_NAME']) : '') . "\" placeholder=\"Nama\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">20.</div></td>
                            <td width=\"9%\"><label for=\"almtMutasi\">Alamat</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"almtMutasi\" id=\"almtMutasi\" size=\"40\" maxlength=\"500\" value=\"" . (($initData['CPM_MU_ADDRESS'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_ADDRESS']) : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">21.</div></td>
                            <td width=\"9%\"><label for=\"rtMutasi\">RT/RW</label></td>
                            <td width=\"10%\">
                                <div style=\"display:flex\">    
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rtMutasi\" id=\"rtMutasi\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_MU_RT'] != '') ? $initData['CPM_MU_RT'] : '') . "\" placeholder=\"000\"/>
                                <span style=\"margin:10px 10px 0 10px\">/</span>
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rwMutasi\" id=\"rwMutasi\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_MU_RW'] != '') ? $initData['CPM_MU_RW'] : '') . "\" placeholder=\"000\"/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">22.</div></td>
                            <td width=\"9%\"><label for=\"propinsiMutasi\">Provinsi</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"propinsiMutasi\" id=\"propinsiMutasi\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_MU_PROVINCE'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_PROVINCE']) : '') . "\" placeholder=\"Provinsi\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">23.</div></td>
                            <td width=\"9%\"><label for=\"kabupatenMutasi\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kabupatenMutasi\" id=\"kabupatenMutasi\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_MU_KABUPATEN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_KABUPATEN']) : '') . "\" placeholder=\"Kabupaten\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">24.</div></td>
                            <td width=\"9%\"><label for=\"kecamatanMutasi\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kecamatanMutasi\" id=\"kecamatanMutasi\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_MU_KECAMATAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_KECAMATAN']) : '') . "\" placeholder=\"Kecamatan\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">25.</div></td>
                            <td width=\"9%\"><label for=\"kelurahanMutasi\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kelurahanMutasi\" id=\"kelurahanMutasi\" size=\"40\" maxlength=\"50\" value=\"" . (($initData['CPM_MU_KELURAHAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_MU_KELURAHAN']) : '') . "\" placeholder=\"".$appConfig['LABEL_KELURAHAN']."\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">26.</div></td>
                            <td width=\"9%\"><label>Kode Pos</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'disabled' : 'readonly') . "  type=\"text\" name=\"kodepos\" id=\"kodepos\" size=\"10\" maxlength=\"10\" value=\"" . ((isset($initData['CPM_MU_KODEPOS']) && $initData['CPM_MU_KODEPOS'] != '') ? $initData['CPM_MU_KODEPOS'] : '') . "\" placeholder=\"Kodepos\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">27.</div></td>
                            <td width=\"15%\"><label for=\"spptTahunRubah\">Tahun Perubahan SPPT</label></td>
                            <td width=\"10%\">
                                <input class=\"form-control\" " . (($readonly) ? 'readonly' : null) . " type=\"text\" name=\"spptTahunRubah\" id=\"spptTahunRubah\" readonly=\"readonly\" size=\"4\" maxlength=\"4\" value=\"" . (($initData['CPM_MU_START_YEAR'] != '') ? $initData['CPM_MU_START_YEAR'] : $appConfig['tahun_tagihan']) . "\" placeholder=\"0000\"/>                    
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
                    <input class=\"btn btn-primary bg-maka\" type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['id_mutasi']) . "\"' />
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

function getInitData($id) {
    global $DBLink;

    $qry = "SELECT * FROM cppmod_pbb_service_mutations where CPM_MU_SID = '{$id}'";
    $res = mysqli_query($DBLink, $qry);
    $row = mysqli_num_rows($res);

    // die(var_dump($id, $row));
    if ($row == 0) {
        return getDataDefault($id);
    } else {
        $qry = "SELECT a.*, b.* FROM cppmod_pbb_services a JOIN cppmod_pbb_service_mutations b WHERE a.CPM_ID = '{$id}'
				AND a.CPM_ID = b.CPM_MU_SID";
		//echo $qry;exit;
        $res = mysqli_query($DBLink, $qry);
        if (!$res) {
            echo $qry . "<br>";
            echo mysqli_error($DBLink);
            return getDataDefault();
        } else {
            while ($row = mysqli_fetch_assoc($res)) {
                $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
                $row['CPM_SPPT_PAYMENT_DATE'] = substr($row['CPM_SPPT_PAYMENT_DATE'], 8, 2) . '-' . substr($row['CPM_SPPT_PAYMENT_DATE'], 5, 2) . '-' . substr($row['CPM_SPPT_PAYMENT_DATE'], 0, 4);
                $row['CPM_MU_DATE'] = substr($row['CPM_MU_DATE'], 8, 2) . '-' . substr($row['CPM_MU_DATE'], 5, 2) . '-' . substr($row['CPM_MU_DATE'], 0, 4);
                /* ?> <pre> <?php print_r($row); ?> </pre> <?php
				exit; */
				return $row;
            }
        }
    }
}

function getDataDefault($id) {
    global $DBLink;

    $qry = "SELECT * FROM cppmod_pbb_services WHERE CPM_ID = '{$id}'";
    $res = mysqli_query($DBLink, $qry);

    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $default = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
            $row['CPM_SPPT_PAYMENT_DATE'] = substr($row['CPM_SPPT_PAYMENT_DATE'], 8, 2) . '-' . substr($row['CPM_SPPT_PAYMENT_DATE'], 5, 2) . '-' . substr($row['CPM_SPPT_PAYMENT_DATE'], 0, 4);
            $default = $row;
        }
        $additional = array('CPM_MU_ID' => '', 'CPM_MU_SID' => '', 'CPM_MU_NAME' => '', 'CPM_MU_ADDRESS' => '', 'CPM_MU_RT' => '',
            'CPM_MU_RW' => '', 'CPM_MU_KELURAHAN' => '', 'CPM_MU_START_YEAR' => '',
            'CPM_MU_PROVINCE' => '', 'CPM_MU_KECAMATAN' => '', 'CPM_MU_KABUPATEN' => '', 'CPM_MU_DATE' => '');

        return $default + $additional;
    }
}

function save($status) {
    global $data, $DBLink, $uname, $arConfig, $dbServices, $readonly, $dbWajibPajak,$appConfig,$dbGwCurrent,$svcid;

    $today = date("Y-m-d");
    $mode = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
    $uuid = c_uuid();
    $nomor = $_REQUEST['nomor'];
    if (!$readonly) {
        $aVal['CPM_MU_NAME'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['nmMutasi']));
        $aVal['CPM_MU_ADDRESS'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['almtMutasi']));
        $aVal['CPM_MU_RT'] = strtoupper($_REQUEST['rtMutasi']);
        $aVal['CPM_MU_RW'] = strtoupper($_REQUEST['rwMutasi']);
        $aVal['CPM_MU_PROVINCE'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['propinsiMutasi']));
        $aVal['CPM_MU_KABUPATEN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kabupatenMutasi']));
        $aVal['CPM_MU_KECAMATAN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kecamatanMutasi']));
        $aVal['CPM_MU_KELURAHAN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kelurahanMutasi']));
        $aVal['CPM_MU_START_YEAR'] = $_REQUEST['spptTahunRubah'];
        $aVal['CPM_MU_DATE'] = substr($_REQUEST['tglMutasi'], 6, 4) . '-' . substr($_REQUEST['tglMutasi'], 3, 2) . '-' . substr($_REQUEST['tglMutasi'], 0, 2);
        $aVal['CPM_MU_STATUS'] = $_REQUEST['statusMilik'];
        $aVal['CPM_MU_PEKERJAAN'] = $_REQUEST['pekerjaan'];
        $aVal['CPM_MU_KODEPOS'] = strtoupper($_REQUEST['kodepos']);
        $aVal['CPM_MU_NO_KTP'] = strtoupper($_REQUEST['noktp']);
        
        $bVal['CPM_REPRESENTATIVE'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['nmKuasa']));
        $bVal['CPM_WP_NAME'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['nmWp']));
        $bVal['CPM_DATE_RECEIVE'] = substr($_REQUEST['tglMasuk'], 6, 4) . '-' . substr($_REQUEST['tglMasuk'], 3, 2) . '-' . substr($_REQUEST['tglMasuk'], 0, 2);
        $bVal['CPM_WP_ADDRESS'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['almtWP']));
        $bVal['CPM_WP_RT'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['rtWP']));
        $bVal['CPM_WP_RW'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['rwWP']));
        $bVal['CPM_WP_PROVINCE'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['propinsi']));
        $bVal['CPM_WP_KABUPATEN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kabupaten']));
        $bVal['CPM_WP_KECAMATAN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kecamatan']));
        $bVal['CPM_WP_KELURAHAN'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['kelurahan']));
        $bVal['CPM_WP_HANDPHONE'] = mysqli_real_escape_string($DBLink ,strtoupper($_REQUEST['hpWP']));
        $bVal['CPM_OP_NUMBER'] = $_REQUEST['nop'];
        $bVal['CPM_SPPT_YEAR'] = $_REQUEST['spptTahun'];
        $bVal['CPM_SPPT_DUE'] = $_REQUEST['pajakTerutang'];
        $bVal['CPM_SPPT_PAYMENT_DATE'] = substr($_REQUEST['tglTerimaSPPT'], 6, 4) . '-' . substr($_REQUEST['tglTerimaSPPT'], 3, 2) . '-' . substr($_REQUEST['tglTerimaSPPT'], 0, 2);
        $bVal['CPM_ATTACHMENT'] = $_REQUEST['attachment'];
        
        $contentWP['CPM_WP_STATUS'] = $_REQUEST['statusMilik'];
        $contentWP['CPM_WP_PEKERJAAN'] = $_REQUEST['pekerjaan'];
        $contentWP['CPM_WP_NAMA'] = strtoupper($_REQUEST['nmMutasi']);
        $contentWP['CPM_WP_ALAMAT'] = strtoupper($_REQUEST['almtMutasi']);
        $contentWP['CPM_WP_KELURAHAN'] = strtoupper($_REQUEST['kelurahanMutasi']);
        $contentWP['CPM_WP_RT'] = strtoupper($_REQUEST['rtMutasi']);
        $contentWP['CPM_WP_RW'] = strtoupper($_REQUEST['rwMutasi']);
        $contentWP['CPM_WP_PROPINSI'] = strtoupper($_REQUEST['propinsiMutasi']);
        $contentWP['CPM_WP_KOTAKAB'] = strtoupper($_REQUEST['kabupatenMutasi']);
        $contentWP['CPM_WP_KECAMATAN'] = strtoupper($_REQUEST['kecamatanMutasi']);
        $contentWP['CPM_WP_KODEPOS'] = strtoupper($_REQUEST['kodepos']);
        $contentWP['CPM_WP_NO_HP'] = strtoupper($_REQUEST['hpWP']);
    }
    $bVal['CPM_STATUS'] = $status;
    if ($_REQUEST['btn-save'] == 'Kirim ke Verifikasi') {
        $bVal['CPM_VALIDATOR'] = $uname;
        $bVal['CPM_DATE_VALIDATE'] = $today;
    }
    if ($_REQUEST['btn-save'] == 'Submit Verifikasi') {
        $bVal['CPM_VERIFICATOR'] = $uname;
        $bVal['CPM_DATE_VERIFICATION'] = $today;
    }
    if ($_REQUEST['btn-save'] == 'Submit Persetujuan') {
        $bVal['CPM_APPROVER'] = $uname;
        $bVal['CPM_DATE_APPROVER'] = $today;
    }
    ($_REQUEST['alasan'] != '') ? $bVal['CPM_REFUSAL_REASON'] = $_REQUEST['alasan'] : null;

    if (!$readonly) {
        if ($mode == 'edit') {
            $res = $dbServices->editMutasi($nomor, $aVal);
        } else {
            $res = $dbServices->addMutasi($uuid, $nomor, $aVal);
        }
    } else {
        $res = true;
    }
    $res2 = $dbServices->editServices($nomor, $bVal);

    if ($status == 4) { // jika di setujui maka

        $filter['CPM_MU_SID'] = $nomor;
        $rowMutasi = $dbServices->getWhereMutasi($filter);

        $spptVal['CPM_WP_NAMA'] = addslashes($rowMutasi[0]['CPM_MU_NAME']);
        $spptVal['CPM_WP_ALAMAT'] = addslashes($rowMutasi[0]['CPM_MU_ADDRESS']);
        $spptVal['CPM_WP_RT'] = $rowMutasi[0]['CPM_MU_RT'];
        $spptVal['CPM_WP_RW'] = $rowMutasi[0]['CPM_MU_RW'];
        $spptVal['CPM_WP_PROPINSI'] = $rowMutasi[0]['CPM_MU_PROVINCE'];
        $spptVal['CPM_WP_KOTAKAB'] = $rowMutasi[0]['CPM_MU_KABUPATEN'];
        $spptVal['CPM_WP_KECAMATAN'] = $rowMutasi[0]['CPM_MU_KECAMATAN'];
        $spptVal['CPM_WP_KELURAHAN'] = $rowMutasi[0]['CPM_MU_KELURAHAN'];
        $spptVal['CPM_WP_NO_HP'] = $rowMutasi[0]['CPM_WP_HANDPHONE'];
        $spptVal['CPM_WP_STATUS'] = $rowMutasi[0]['CPM_MU_STATUS'];
        $spptVal['CPM_WP_PEKERJAAN'] = $rowMutasi[0]['CPM_MU_PEKERJAAN'];
        $spptVal['CPM_WP_KODEPOS'] = $rowMutasi[0]['CPM_MU_KODEPOS'];
        $spptVal['CPM_WP_NO_KTP'] = $rowMutasi[0]['CPM_MU_NO_KTP'];

		
        $res3 = $dbServices->editSpptFinal($rowMutasi[0]['CPM_OP_NUMBER'], $spptVal);
        
        $curVal['WP_NAMA'] 		= addslashes($rowMutasi[0]['CPM_MU_NAME']);
        $curVal['WP_ALAMAT'] 	= addslashes($rowMutasi[0]['CPM_MU_ADDRESS']);
        $curVal['WP_RT'] 		= $rowMutasi[0]['CPM_MU_RT'];
        $curVal['WP_RW'] 		= $rowMutasi[0]['CPM_MU_RW'];
        $curVal['WP_KOTAKAB'] 	= $rowMutasi[0]['CPM_MU_KABUPATEN'];
        $curVal['WP_KECAMATAN'] = $rowMutasi[0]['CPM_MU_KECAMATAN'];
        $curVal['WP_KELURAHAN'] = $rowMutasi[0]['CPM_MU_KELURAHAN'];
        $curVal['WP_NO_HP'] 	= $rowMutasi[0]['CPM_WP_HANDPHONE'];
        $curVal['WP_KODEPOS'] 	= $rowMutasi[0]['CPM_MU_KODEPOS'];

		//tambah pengecekan tahun berlaku
		//if tahun  sama dengan tahun tagihan saat ini maka 
		// exit;

		if($_REQUEST['spptTahunRubah']==$appConfig['tahun_tagihan']){
		
			// EDIT SPPT CURRENT
			$res3 = $dbServices->editSpptCurrent($rowMutasi[0]['CPM_OP_NUMBER'], $curVal);
			  
			//NEW START
			// NEW 2018 - 4 -4
                    
            #Penetapan Ulang by ZNK (20 Juni 2017)
        
            #ambil data current untuk mendapatkan nilai NJOPTKP dan Tagihan sebelumnya
            $dataCurrent   = $dbGwCurrent->getDataCurrent($rowMutasi[0]['CPM_OP_NUMBER']);
            $njoptkp       = $dataCurrent['OP_NJOPTKP'];
            $tagihanLama   = $dataCurrent['SPPT_PBB_HARUS_DIBAYAR'];
            
   
            //update CURRENT
			// $res3 = $dbSpptPerubahan->updateToCurrent($svcid, $appConfig);
            // var_dump($res3);
            $tglPenetapan       = date("Y-m-d");
            $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'] . ":".$appConfig['GW_DBPORT'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'],$appConfig['GW_DBNAME']);
             if (!$GWDBLink) {
                $res3 = false;    
                  echo mysqli_error($GWDBLink); 
            }  
              //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) ;
            // $penetapanTerakhir  = $dbGwCurrent->getLastPenetapan($_REQUEST['nopno'],$_REQUEST['spptTahunPajak'],$GWDBLink);
            $sppt               = $dbGwCurrent->getDataTagihanSPPT($rowMutasi[0]['CPM_OP_NUMBER'],$_REQUEST['spptTahunRubah'],$GWDBLink);
            // var_dump($sppt);
            // exit;
            // exit;
            if($sppt['PAYMENT_FLAG']==1){ // jika sudah bayar maka
                // lanjutkan final
            }else{ // jika belum bayar maka

                $res4 = $dbGwCurrent->updateTagihanSPPT($rowMutasi[0]['CPM_OP_NUMBER'],$_REQUEST['spptTahunRubah'],$curVal,$GWDBLink);
                
            } // end jika belum bayar


			// NEW END
		
		}
		else{
			$res=true;
		}
         mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']) ;
		$dbWajibPajak->save($aVal['CPM_MU_NO_KTP'],$contentWP);
    } // end jika di setujui

    if ($res === false || $res2 === false || $res3 === false || $res4 === false) {
        echo $res.$res2.$res3.$res4;
        die();
    }

    if ($res && $res2) {
        echo 'Data berhasil disimpan...!';
        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_mutasi'];
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
              </script>";
    } else {
        echo mysqli_error($DBLink);
    }
}

$save = isset($_REQUEST['btn-save'])?$_REQUEST['btn-save']:'';

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
    // echo "<pre>";
    // print_r($initData);
    // echo "</pre>";
    // exit;
    echo "<script>
    $(document).ready(function(){
        $('#noktp').trigger('blur');
    });

    </script>";
    echo formPenerimaan($initData);
}
?>

