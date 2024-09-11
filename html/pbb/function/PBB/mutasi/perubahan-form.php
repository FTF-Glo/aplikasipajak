<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbSpptPerubahan.php");
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
$dbUtils = new DbUtils($dbSpec);
$dbSpptPerubahan = new DbSpptPerubahan($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);

$NBParam = base64_encode('{"ServerAddress":"' . $appConfig['TPB_ADDRESS'] . '","ServerPort":"' . $appConfig['TPB_PORT'] . '","ServerTimeOut":"' . $appConfig['TPB_TIMEOUT'] . '"}');
$NOP = '';
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

function getKecamatan($idKab = "")
{
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

function getKelurahan($idKec = "")
{
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

function formPenerimaan($initData)
{
    global $a, $m, $arConfig, $appConfig, $nobutton, $readonly, $dbUtils, $DBLink;
    $today = date("d-m-Y");
    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = "";

    $bSlash = "\'";
    $ktip = "'";

    $optionProvWP = "";

    $hiddenModeInput = null;

    if (isset($initData['CPM_ID']) && $initData['CPM_ID'] != '') {

        $initData['CPM_DATE_RECEIVE'] = substr($initData['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($initData['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($initData['CPM_DATE_RECEIVE'], 0, 4);
        $initData['CPM_SPPT_PAYMENT_DATE'] = substr($initData['CPM_SPPT_PAYMENT_DATE'], 8, 2) . '-' . substr($initData['CPM_SPPT_PAYMENT_DATE'], 5, 2) . '-' . substr($initData['CPM_SPPT_PAYMENT_DATE'], 0, 4);

        if ($initData['CPM_SID'] != '') {
            $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
        }
    }

    $cpmid = null;
    if (isset($initData['CPM_ID'])) {
        $cpmid = $initData['CPM_ID'];
    }

    $qry = "SELECT CPM_SPPT_DOC_ID, CPM_OP_NUM FROM cppmod_pbb_service_change_ext WHERE CPM_SPPT_DOC_ID = (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_service_change WHERE CPM_SID='{$cpmid}') ";

    $res = mysqli_query($DBLink, $qry);

    $HtmlExt = "";
    $op_num = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $param = "a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idd=" . $initData['CPM_SPPT_DOC_ID'] . "&v=" . $initData['CPM_SPPT_DOC_VERSION'] . "&num=" . $row['CPM_OP_NUM'];
        $HtmlExt .= "<li>
                <a href='main.php?param=" . base64_encode($param) . "' title=\"Buka Lampiran\">Lampiran Bangunan " . $row['CPM_OP_NUM'] . "</a> &nbsp;&nbsp;&nbsp;";
        if (!$readonly) $HtmlExt .= "<a href='#' onClick=\"deleteLampiran('" . $row['CPM_SPPT_DOC_ID'] . "', '" . $row['CPM_OP_NUM'] . "','{$initData['CPM_ID']}');\"><img border=\"0\" alt=\"Hapus Lampiran\" src=\"image/icon/delete.png\"></a>";
        $HtmlExt .= "</li>";
        //        $op_num = $row['CPM_OP_NUM'];
        $op_num++;
    }

    $btnTambahLampiran = "";
    if ((isset($initData['CPM_OP_JML_BANGUNAN']) && $initData['CPM_OP_JML_BANGUNAN'] > 0) && ($op_num < $initData['CPM_OP_JML_BANGUNAN']) && !$readonly) {
        $btnTambahLampiran = "<input type='button' value='Tambah Baru' onclick=\"javascript:window.location='main.php?param=" . base64_encode("a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idd=" . $initData['CPM_SPPT_DOC_ID'] . "&v=" . $initData['CPM_SPPT_DOC_VERSION'] . "&num=" . ($op_num + 1)) . "'\">";
    }

    $CPM_KODE_LOKASI = isset($initData['CPM_NOP']) ? $initData['CPM_NOP'] : '';
    
    $CPM_KODE_LOKASI = substr($CPM_KODE_LOKASI, 0,10);
    
    $bZNT = $dbUtils->getZNT_with_kelas(array("CPM_KODE_LOKASI" => $CPM_KODE_LOKASI));
    $optionZNT = "";
    $optionZNT .= "<option value='' >-</option>";
    if ($bZNT != null && count($bZNT) > 0) {
        foreach ($bZNT as $row) {
            $optionZNT .= "<option value='" . $row['CPM_KODE_ZNT'] . "' " . (($initData['CPM_OT_ZONA_NILAI'] == $row['CPM_KODE_ZNT']) ? "selected" : "") . ">" . $row['CPM_KODE_ZNT'] . " - " . number_format($row['CPM_NIR'], 0, ",", ".") . "</option>";
            //if($initData['CPM_OT_ZONA_NILAI'] == '' || $initData['CPM_OT_ZONA_NILAI'] == '0'){
            $selectZNT = "<select name=\"OT_ZONA_NILAI\" id=\"OT_ZONA_NILAI\">
                            " . $optionZNT . "
                            </select>";
            /*}else{
            $selectZNT = "<input type=\"hidden\" name=\"OT_ZONA_NILAI\" value=\"".$initData['CPM_OT_ZONA_NILAI']."\"/>
                            <select disabled name=\"OT_ZONA_NILAI_TEMP\" id=\"OT_ZONA_NILAI_TEMP\">
                            ".$optionZNT."
                            </select>";
        }*/
        }
    }


    $OP_LUAS_TANAH_VIEW = '0';
    if (isset($initData['CPM_OP_LUAS_TANAH'])) {
        if (strrchr($initData['CPM_OP_LUAS_TANAH'], '.') != '') {
            $OP_LUAS_TANAH_VIEW = number_format($initData['CPM_OP_LUAS_TANAH'], 2, ',', '.');
        } else {
            $OP_LUAS_TANAH_VIEW = number_format($initData['CPM_OP_LUAS_TANAH'], 0, ',', '.');
        }
    }

    $OP_LUAS_BANGUNAN_VIEW = '0';
    if (isset($initData['CPM_OP_LUAS_BANGUNAN'])) {
        if (strrchr($initData['CPM_OP_LUAS_BANGUNAN'], '.') != '') {
            $OP_LUAS_BANGUNAN_VIEW = number_format($initData['CPM_OP_LUAS_BANGUNAN'], 2, ',', '.');
        } else $OP_LUAS_BANGUNAN_VIEW = number_format($initData['CPM_OP_LUAS_BANGUNAN'], 0, ',', '.');
    }

    $optionRT = $optionRW = '';
    for ($i=1; $i <= 225; $i++) {
        $x_num = sprintf("%03d", $i);
        $selectit = (isset($initData['CPM_OP_RT']) && $i==(int)$initData['CPM_OP_RT']) ? 'selected':'';
        $optionRT .= "<option $selectit value=$x_num>$x_num</option>";
    }

    for ($i=0; $i <= 99; $i++) {
        $x_num = sprintf("%02d", $i);
        $selectit = (isset($initData['CPM_OP_RW']) && $i==(int)$initData['CPM_OP_RW']) ? 'selected':'';
        $optionRW .= "<option $selectit value=$x_num>$x_num</option>";
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
            $('#noktp').focus();
            $(':radio[name=\"OT_JENIS\"]').change(function() {
                var jenis_bumi = $(this).filter(':checked').val();
                if(jenis_bumi == '2' || jenis_bumi == '3'){
                    $(':text[name=\"jmlBangunan\"]').val(0);
                    $(':text[name=\"jmlBangunan\"]').attr('disabled','disabled');
                }else{
                    $(':text[name=\"jmlBangunan\"]').removeAttr('disabled');
                }
            });
          
            $( \"input:submit, input:button\").button();
			$(\"#form-penerimaan\").submit(function(e){
				ids = 0;
				$.each($(\".attach:checked\"), function() {
					ids +=  parseInt($(this).val());
				});
				
				$(\"#attachment\").val(ids);
			});
		
//			$('#tglMasuk').datepicker({dateFormat: 'dd-mm-yy'});
//                        $('#tglTerimaSPPT').datepicker({dateFormat: 'dd-mm-yy'});
			
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

    if (isset($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != '')
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
                    nmWp : \"required\",
                    tglMasuk : \"required\",
                    almtWP : \"required\",
                    rtWP : {
                            required : true,
                            number : true
                          },
                    rwWP : {
                            required : true,
                            number : true
                          },
                    hpWP : {
                            required : true,
                            number : true
                          },
                    nop : {
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
                    luasBumi : {
                            required : true,
                            number : true
                          },
                    luasBangunan1 : {
                            required : true,
                            number : true
                          },
                    statusMilik : \"required\",      
                    pekerjaan : \"required\",
                    kodepos : \"required\",
                    noktp : \"required\"
                },
                messages : {
                    nmKuasa : \"\",
                    nmWp : \"\",
                    tglMasuk : \"\",
                    almtWP : \"\",
                    rtWP : \"\",
                    rwWP : \"\",
                    hpWP : \"\",
                    nop : \"\",
                    spptTahun : \"\",
                    pajakTerutang : \"\",
                    luasBumi : \"\",
                    luasBangunan1 : \"\",
                    statusMilik : \"pilih\",      
                    pekerjaan : \"pilih\",
                    kodepos : \"\",
                    noktp : \"\"
                }
            });
            
            $('#nmKuasa').focusout(function(){
                $('#nmWp').attr('value',$(this).val());
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
        
            $('#propinsiPerubahan').change(function(){
                getWilayahMutasi(1,$(this).val());
                $('#kecamatanPerubahan').html(\"<option value=''>--Pilih Kabupaten Dulu--</option>\");
                $('#kelurahanPerubahan').html(\"<option value=''>--Pilih Kecamatan Dulu--</option>\");
            });
            
            $('#kabupatenPerubahan').change(function(){
                getWilayahMutasi(2,$(this).val());
            });
            
            $('#kecamatanPerubahan').change(function(){
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
                            case 1 : $('#kabupatenPerubahan').html(data[0]);
                                     $('#kecamatanPerubahan').html(data[1]);
                                     $('#kelurahanPerubahan').html(data[2]);
                                     break;
                            case 2 : $('#kecamatanPerubahan').html(data[0]);
                                     $('#kelurahanPerubahan').html(data[1]);
                                     break;
                            case 3 : $('#kelurahanPerubahan').html(msg);break;
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
                var noktp = x.value.replace(/[^0-9.]/g, '');
                x.value = noktp;
                
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
                            $(\"input[name=pekerjaan][value=\" + json.CPM_WP_PEKERJAAN + \"]\").attr('checked', 'checked').attr('disabled', false);;
                            document.getElementById(\"nmWpPerubahan\").value = json.CPM_WP_NAMA;
                            document.getElementById(\"almtWPPerubahan\").value = json.CPM_WP_ALAMAT;
                            document.getElementById(\"rtWPPerubahan\").value = json.CPM_WP_RT;
                            document.getElementById(\"rwWPPerubahan\").value = json.CPM_WP_RW;
                            document.getElementById(\"propinsiPerubahan\").value = json.CPM_WP_PROPINSI;
                            document.getElementById(\"kabupatenPerubahan\").value = json.CPM_WP_KOTAKAB;
                            document.getElementById(\"kecamatanPerubahan\").value = json.CPM_WP_KECAMATAN;
                            document.getElementById(\"kelurahanPerubahan\").value = json.CPM_WP_KELURAHAN;
                            document.getElementById(\"kodepos\").value = json.CPM_WP_KODEPOS;
                            document.getElementById(\"hpWPPerubahan\").value = json.CPM_WP_NO_HP;
                            $(\"#div-tmbahwp\").html(\"<a href=javascript:displayFormWp('\"+noktp+\"')>Edit WP?</a>\");
                            alert('No KTP Ditemukan');
                        } else {
                            alert('NO KTP Tidak Ditemukan');                            
                            document.getElementById(\"nmWpPerubahan\").value = '';
                            document.getElementById(\"almtWPPerubahan\").value = '';
                            document.getElementById(\"rtWPPerubahan\").value = '';
                            document.getElementById(\"rwWPPerubahan\").value = '';
                            document.getElementById(\"propinsiPerubahan\").value = '';
                            document.getElementById(\"kabupatenPerubahan\").value = '';
                            document.getElementById(\"kecamatanPerubahan\").value = '';
                            document.getElementById(\"kelurahanPerubahan\").value = '';
                            document.getElementById(\"kodepos\").value = '';
                            document.getElementById(\"hpWPPerubahan\").value = '';
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
    <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\" style=\"max-width:990px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f\">
        $hiddenModeInput
        <input type=\"hidden\" name=\"spptTahunRubah\" size=\"4\" maxlength=\"4\" value=\"" . ((isset($initData['CPM_SPPT_YEAR_BERLAKU']) && $initData['CPM_SPPT_YEAR_BERLAKU'] != '') ? $initData['CPM_SPPT_YEAR_BERLAKU'] : '') . "\"/>
        <input type=\"hidden\" name=\"spptTahunPajak\" size=\"4\" maxlength=\"4\" value=\"" . ((isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : '') . "\"/>
        <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
        <table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
            <tr>
                <td colspan=\"4\"><strong><font size=\"+2\">Penerimaan Berkas Perubahan Data PBB-P2</font></strong><br /><hr><br /></td>
            </tr>
            <tr valign=\"top\">
                <td width=\"50%\" style=\"padding-right:15px\"><table border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA OBJEK PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">1.</div></td>
                            <td width=\"9%\"><label for=\"nop\">NOP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nop\" id=\"nop\" size=\"40\" maxlength=\"50\" readonly=\"readonly\" value=\"" . (isset($initData['CPM_NOP']) ? $initData['CPM_NOP'] : '') . "\" placeholder=\"NOP\" />
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">2.</div></td>
                            <td><label for=\"almtOP\">Alamat</label></td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"almtOPPerubahan\" id=\"almtOPPerubahan\" size=\"40\" maxlength=\"70\" value=\"" . (isset($initData['CPM_OP_ALAMAT']) ? str_replace($bSlash, $ktip, $initData['CPM_OP_ALAMAT']) : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">3.</div></td>
                            <td><label for=\"rtOP\">RT / RW</label></td>
                            <td>
                                <select name=\"rtOPPerubahan\" id=\"rtOPPerubahan\" " . (($readonly) ? 'disabled' : null) . " style=\"width:60px;background:transparent;border:#8f8f9d solid 1px\">$optionRT</select>&nbsp;/&nbsp;
                                <select name=\"rwOPPerubahan\" id=\"rwOPPerubahan\" " . (($readonly) ? 'disabled' : null) . " style=\"width:45px;background:transparent;border:#8f8f9d solid 1px\">$optionRW</select>
                                <!--input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rtOPPerubahan\" id=\"rtOPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . (isset($initData['CPM_OP_RT']) ? $initData['CPM_OP_RT'] : '') . "\" placeholder=\"000\"/-->
                                <!--input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rwOPPerubahan\" id=\"rwOPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . (isset($initData['CPM_OP_RW']) ? $initData['CPM_OP_RW'] : '') . "\" placeholder=\"000\"/-->
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">4.</div></td>
                            <td><label for=\"kotaOP\">Kabupaten/Kota</label></td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kotaOP\" value=\"" . $appConfig['NAMA_KOTA'] . "\" size=\"40\" placeholder=\"Kota\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">5.</div></td>
                            <td><label for=\"kotaOP\">Kecamatan</label></td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kecamatanOP\" value=\"" . (isset($initData['CPC_TKC_KECAMATAN']) ? $initData['CPC_TKC_KECAMATAN'] : '') . "\" size=\"40\" placeholder=\"Kecamatan\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">6.</div></td>
                            <td><label for=\"kotaOP\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kelurahanOP\" value=\"" . (isset($initData['CPC_TKL_KELURAHAN']) ? $initData['CPC_TKL_KELURAHAN'] : '') . "\" size=\"40\" placeholder=\"" . $appConfig['LABEL_KELURAHAN'] . "\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA TANAH</h3></td></tr>
                        <tr>
                            <td><div align=\"right\">7.</div></td>
                            <td>Luas Tanah</td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"luasBumi\" id=\"luasBumi\" size=\"7\" maxlength=\"6\" value=\"" . (isset($initData['CPM_OP_LUAS_TANAH']) ? $initData['CPM_OP_LUAS_TANAH'] : '') . "\" placeholder=\"0\" class=\"text-right\" onkeypress=\"return iniAngkaDenganKoma(event, this);\"/>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Nomor Sertifikat</td>
                            <td><input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nomorSertifikat\" id=\"nomorSertifikat\" size=\"20\" maxlength=\"25\" value=\"" . (isset($initData['CPM_NOMOR_SERTIFIKAT']) ? $initData['CPM_NOMOR_SERTIFIKAT'] : '') . "\"/></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Tanggal Sertifikat</td>
                            <td><input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tanggalSertifikat\" id=\"tanggalSertifikat\" size=\"14\" maxlength=\"10\" value=\"" . (isset($initData['CPM_TANGGAL_SERTIFIKAT']) ? $initData['CPM_TANGGAL_SERTIFIKAT'] : '') . "\"/></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Nama di Sertifikat</td>
                            <td><input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"namaSertifikat\" id=\"namaSertifikat\" size=\"35\" maxlength=\"40\" value=\"" . (isset($initData['CPM_NAMA_SERTIFIKAT']) ? $initData['CPM_NAMA_SERTIFIKAT'] : '') . "\"/></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Jenis Sertifikat</td>
                            <td>
                                <select name=\"jenisHak\" id=\"jenisHak\">
                                    <option value=\"\">Pilih jenis hak</option>
                                    <option value=\"HM\" ". ((isset($initData['CPM_JENIS_HAK']) && $initData['CPM_JENIS_HAK']=='HM') ? 'selected':'') .">HAK MILIK (HM)</option>
                                    <option value=\"HGU\" ". ((isset($initData['CPM_JENIS_HAK']) && $initData['CPM_JENIS_HAK']=='HGU') ? 'selected':'') .">HAK GUNA USAH (HGU)</option>
                                    <option value=\"HGM\" ". ((isset($initData['CPM_JENIS_HAK']) && $initData['CPM_JENIS_HAK']=='HGM') ? 'selected':'') .">HAK GUNA BANGUNAN (HGM)</option>
                                    <option value=\"HP\" ". ((isset($initData['CPM_JENIS_HAK']) && $initData['CPM_JENIS_HAK']=='HP') ? 'selected':'') .">HAK PAKAI (HP)</option>
                                    <option value=\"HPL\" ". ((isset($initData['CPM_JENIS_HAK']) && $initData['CPM_JENIS_HAK']=='HPL') ? 'selected':'') .">HAK PENGELOLAAN (HPL)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><div align=\"right\">8.</div></td>
                            <td>Zona Nilai Tanah</td>
                            <td>" . (isset($selectZNT) ? $selectZNT : '') . "</td>
                        </tr>
                        <tr valign=\"top\">
                            <td><div align=\"right\">9.</div></td>
                            <td>Jenis Tanah</td>
                            <td>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"OT_JENIS\" VALUE=\"1\" " . ((isset($initData['CPM_OT_JENIS']) && $initData['CPM_OT_JENIS'] == 1) ? "checked" : "") . " checked> Tanah + Bangunan</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"OT_JENIS\" VALUE=\"2\" " . ((isset($initData['CPM_OT_JENIS']) && $initData['CPM_OT_JENIS'] == 2) ? "checked" : "") . "> Kavling siap bangun</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"OT_JENIS\" VALUE=\"3\" " . ((isset($initData['CPM_OT_JENIS']) && $initData['CPM_OT_JENIS'] == 3) ? "checked" : "") . "> Tanah kosong</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"OT_JENIS\" VALUE=\"4\" " . ((isset($initData['CPM_OT_JENIS']) && $initData['CPM_OT_JENIS'] == 4) ? "checked" : "") . "> Fasilitas umum</input></label>
                                " . (($readonly) ? "<input type='hidden' name='OT_JENIS' value='" . $initData['CPM_OT_JENIS'] . "' />" : null) . " 
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA BANGUNAN</h3></td></tr>
                        <tr>
                            <td><div align=\"right\">10.</div></td>
                            <td>Jumlah Bangunan</td>
                            <td>
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"jmlBangunan\" id=\"jmlBangunan\" size=\"10\" maxlength=\"8\" value=\"" . (isset($initData['CPM_OP_JML_BANGUNAN']) ? $initData['CPM_OP_JML_BANGUNAN'] : '') . "\" placeholder=\"0\" />
                            </td>
                        </tr>
                        <tr>
                            <td valign=\"top\"><div align=\"right\">11.</div></td>
                            <td valign=\"top\">Data Bangunan</td>
                            <td>
                                " . $HtmlExt . "<br/>
                                " . $btnTambahLampiran . "
                                
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA PENILAIAN</h3></td></tr>
                        <tr><td colspan=\"3\">
                            <table width=\"100%\">
                            <tr>
                                    <th> </th>
                                    <th>Luas</th>
                                    <th>Kelas</th>
                                    <th>NJOP / M2</th>
                                    <th>NJOP</th>
                            </tr>
                            <tr>
				<td>Bumi</td>
                                <td align=\"right\">" . $OP_LUAS_TANAH_VIEW . "</td>
                                <td align=\"center\">" . (isset($initData['CPM_OP_KELAS_TANAH']) ? $initData['CPM_OP_KELAS_TANAH'] : '') . "</td>
                                <td align=\"right\">" . (isset($initData['CPM_NJOP_TANAH']) && ($initData['CPM_NJOP_TANAH'] != 0 || $initData['CPM_NJOP_TANAH'] != '') ? number_format($initData['CPM_NJOP_TANAH'] / $initData['CPM_OP_LUAS_TANAH'], 0, ',', '.') : "0") . "</td>
                                <td align=\"right\">" . (isset($initData['CPM_NJOP_TANAH']) ? number_format($initData['CPM_NJOP_TANAH'], 0, ',', '.') : '') . "</td>
                            </tr>
                            <tr>
				<td>Bangunan</td>
				<td align=\"right\">" . $OP_LUAS_BANGUNAN_VIEW . "</td>
                                <td align=\"center\">" . (isset($initData['CPM_OP_KELAS_BANGUNAN']) ? $initData['CPM_OP_KELAS_BANGUNAN'] : '') . "</td>
                                <td align=\"right\">" . ((isset($initData['CPM_NJOP_BANGUNAN']) && $initData['CPM_NJOP_BANGUNAN'] != 0 && $initData['CPM_NJOP_BANGUNAN'] != '') && ($initData['CPM_OP_LUAS_BANGUNAN'] != 0 && $initData['CPM_OP_LUAS_BANGUNAN'] != '') ? number_format($initData['CPM_NJOP_BANGUNAN'] / $initData['CPM_OP_LUAS_BANGUNAN'], 0, ',', '.') : "0") . "</td>
                                <td align=\"right\">" . (isset($initData['CPM_NJOP_BANGUNAN']) ? number_format($initData['CPM_NJOP_BANGUNAN'], 0, ',', '.') : '') . "</td>
                            </tr>
                            </table>
                            <input type=\"button\" name=\"hitung\" value=\"Nilai Ulang\" id=\"hitung-njop\"/>
                        </td></tr>
                    </table></td>
                <td width=\"50%\" valign=\"top\" style=\"padding-left:15px\"><table border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA WAJIB PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">13.</div></td>
                            <td width=\"9%\"><label>Nomor KTP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"noktp\" id=\"noktp\" size=\"40\" onblur=\"return cekWP(event, this);\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_NO_KTP']) && $initData['CPM_WP_NO_KTP'] != '') ? $initData['CPM_WP_NO_KTP'] : '') . "\" placeholder=\"No KTP\" autofocus />
                                <span id=\"div-loadwp-wait\"></span>
                                <span id=\"div-tmbahwp\"></span>
                            </td>
                        </tr>
                        <tr>
                            <td valign=\"top\"><div align=\"right\">14.</div></td>
                            <td>
                                Status<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemilik\" " . ((isset($initData['CPM_WP_STATUS']) && $initData['CPM_WP_STATUS'] == 'Pemilik') ? 'checked' : '') . "> Pemilik</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Penyewa\" " . ((isset($initData['CPM_WP_STATUS']) && $initData['CPM_WP_STATUS'] == 'Penyewa') ? 'checked' : '') . "> Penyewa</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pengelola\" " . ((isset($initData['CPM_WP_STATUS']) && $initData['CPM_WP_STATUS'] == 'Pengelola') ? 'checked' : '') . "> Pengelola</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemakai\" " . ((isset($initData['CPM_WP_STATUS']) && $initData['CPM_WP_STATUS'] == 'Pemakai') ? 'checked' : '') . "> Pemakai</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Sengketa\" " . ((isset($initData['CPM_WP_STATUS']) && $initData['CPM_WP_STATUS'] == 'Sengketa') ? 'checked' : '') . "> Sengketa</label><br>
                            </td>
                            <td valign=\"top\">
                                Pekerjaan<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"PNS\" " . ((isset($initData['CPM_WP_PEKERJAAN']) && $initData['CPM_WP_PEKERJAAN'] == 'PNS') ? 'checked' : 'disabled') . "> PNS</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"TNI\" " . ((isset($initData['CPM_WP_PEKERJAAN']) && $initData['CPM_WP_PEKERJAAN'] == 'TNI') ? 'checked' : 'disabled') . "> TNI</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Pensiunan\" " . ((isset($initData['CPM_WP_PEKERJAAN']) && $initData['CPM_WP_PEKERJAAN'] == 'Pensiunan') ? 'checked' : 'disabled') . "> Pensiunan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Badan\" " . ((isset($initData['CPM_WP_PEKERJAAN']) && $initData['CPM_WP_PEKERJAAN'] == 'Badan') ? 'checked' : 'disabled') . "> Badan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Lainnya\" " . ((isset($initData['CPM_WP_PEKERJAAN']) && $initData['CPM_WP_PEKERJAAN'] == 'Lainnya') ? 'checked' : 'disabled') . "> Lainnya</label><br>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">15.</div></td>
                            <td width=\"9%\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"nmWpPerubahan\" id=\"nmWpPerubahan\" size=\"40\" maxlength=\"50\" value=\"" . (isset($initData['CPM_WP_NAMA']) ? str_replace($bSlash, $ktip, $initData['CPM_WP_NAMA']) : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">16.</div></td>
                            <td width=\"9%\">No. HP WP</td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"hpWPPerubahan\" id=\"hpWPPerubahan\" size=\"15\" maxlength=\"15\" value=\"" . (isset($initData['CPM_WP_NO_HP']) ? $initData['CPM_WP_NO_HP'] : '') . "\" placeholder=\"Nomor HP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">17.</div></td>
                            <td width=\"9%\"><label for=\"almtWP\">Alamat WP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"almtWPPerubahan\" id=\"almtWPPerubahan\" size=\"40\" maxlength=\"70\" value=\"" . (isset($initData['CPM_WP_ALAMAT']) ? str_replace($bSlash, $ktip, $initData['CPM_WP_ALAMAT']) : '') . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">18.</div></td>
                            <td width=\"9%\"><label for=\"rtWP\">RT/RW</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rtWPPerubahan\" id=\"rtWPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . (isset($initData['CPM_WP_RT']) ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"00\"/>&nbsp;/
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rwWPPerubahan\" id=\"rwWPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . (isset($initData['CPM_WP_RW']) ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">19.</div></td>
                            <td width=\"9%\"><label for=\"propinsiPerubahan\">Provinsi</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"propinsiPerubahan\" id=\"propinsiPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_PROPINSI']) && $initData['CPM_WP_PROPINSI'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_PROPINSI']) : '') . "\" placeholder=\"Provinsi\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">20.</div></td>
                            <td width=\"9%\"><label for=\"kabupatenPerubahan\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kabupatenPerubahan\" id=\"kabupatenPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_KOTAKAB']) && $initData['CPM_WP_KOTAKAB'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_KOTAKAB']) : '') . "\" placeholder=\"Kabupaten\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">21.</div></td>
                            <td width=\"9%\"><label for=\"kecamatanPerubahan\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kecamatanPerubahan\" id=\"kecamatanPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_KECAMATAN']) && $initData['CPM_WP_KECAMATAN'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_KECAMATAN']) : '') . "\" placeholder=\"Kecamatan\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">22.</div></td>
                            <td width=\"9%\"><label for=\"kelurahan\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kelurahanPerubahan\" id=\"kelurahanPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_KELURAHAN']) && $initData['CPM_WP_KELURAHAN'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_KELURAHAN']) : '') . "\" placeholder=\"" . $appConfig['LABEL_KELURAHAN'] . "\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">23.</div></td>
                            <td width=\"9%\"><label>Kode Pos</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . "  type=\"text\" name=\"kodepos\" id=\"kodepos\" size=\"10\" maxlength=\"10\" value=\"" . ((isset($initData['CPM_WP_KODEPOS']) && $initData['CPM_WP_KODEPOS'] != '') ? $initData['CPM_WP_KODEPOS'] : '') . "\" placeholder=\"Kodepos\" />
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA LOKET PELAYANAN</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">24.</div></td>
                            <td width=\"9%\">Nomor</td>
                            <td width=\"10%\">
                                <input type=\"hidden\" name=\"nopno\" value=\"" . (isset($initData['CPM_NOP']) ? $initData['CPM_NOP'] : '') . "\">
                                <input type=\"hidden\" name=\"sppt_doc_id\" value=\"" . (isset($initData['CPM_SPPT_DOC_ID']) ? $initData['CPM_SPPT_DOC_ID'] : '') . "\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nomor\" id=\"nomor\" size=\"40\" maxlength=\"50\" value=\"" . (isset($initData['CPM_ID']) ? $initData['CPM_ID'] : '') . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">25.</div></td>
                            <td width=\"9%\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"40\" maxlength=\"50\" value=\"" . (isset($initData['CPM_REPRESENTATIVE']) ? str_replace($bSlash, $ktip, $initData['CPM_REPRESENTATIVE']) : '') . "\" placeholder=\"Nama Kuasa\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">26.</div></td>
                            <td width=\"9%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . (isset($initData['CPM_DATE_RECEIVE']) ? $initData['CPM_DATE_RECEIVE'] : '') . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">27.</div></td>
                            <td width=\"9%\"><label for=\"spptTahun\">SPPT Tahun</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"spptTahun\" id=\"spptTahun\" size=\"4\" maxlength=\"4\" value=\"" . (isset($initData['CPM_SPPT_YEAR']) ? $initData['CPM_SPPT_YEAR'] : '') . "\" placeholder=\"0000\"/>                    
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">28.</div></td>
                            <td width=\"9%\"><label for=\"tahunBerlaku\">Tahun Berlaku Perubahan</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tahunBerlaku\" id=\"tahunBerlaku\" size=\"4\" maxlength=\"4\" value=\"" . (isset($initData['CPM_SPPT_YEAR_BERLAKU']) ? $initData['CPM_SPPT_YEAR_BERLAKU'] : '') . "\" placeholder=\"0000\"/>                    
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">29.</div></td>
                            <td width=\"9%\">Jumlah Pajak Terutang</td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"pajakTerutang\" id=\"pajakTerutang\" size=\"40\" maxlength=\"50\" value=\"" . (isset($initData['CPM_SPPT_DUE']) ? $initData['CPM_SPPT_DUE'] : '') . "\" placeholder=\"0\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">30.</div></td>
                            <td width=\"9%\"><label for=\"tglTerimaSPPT\">Tanggal Bayar SPPT</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglTerimaSPPT\" id=\"tglTerimaSPPT\" readonly=\"readonly\" value=\"" . (isset($initData['CPM_SPPT_PAYMENT_DATE']) ? $initData['CPM_SPPT_PAYMENT_DATE'] : '') . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\" valign=\"top\"><div align=\"right\">&nbsp;</div></td>                                          
                            <td width=\"10%\" valign=\"top\" colspan=\"2\">
                                <p style=\"margin-bottom : 8px\">Kelengkapan Dokumen</p>
                                <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                    <li id=\"berkas1\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                                    <li id=\"berkas3\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                    <li id=\"berkas5\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 16) ? "checked=\"checked\"" : "") . "> Foto Copy Bukti Kepemilikan Tanah</li>
                                    <li id=\"berkas6\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 32) ? "checked=\"checked\"" : "") . "> Foto Copy IMB</li>
                                    <li id=\"berkas8\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 128) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                                    <li id=\"berkas9\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" " . ((isset($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 256) ? "checked=\"checked\"" : "") . "> Surat Ketetapan Pajak Daerah (SKPD).</li>
                                </ol>
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
                    <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan Sementara\" />
                    <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                    &nbsp;
                    <input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['id_perubahan']) . "\"' />
                </td>
            </tr>";
    $kirim = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
				<hr><br>
                    <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim ke Verifikasi\" />
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
                            <tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input type=\"submit\" name=\"btn-save\" value=\"" . (($arConfig['usertype'] == 'verifikator') ? 'Submit Verifikasi' : 'Submit Persetujuan') . "\"></td></tr>
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

function save($status)
{
    global $data, $DBLink, $uname, $arConfig, $appConfig, $dbServices, $readonly, $dbSpptPerubahan, $dbUtils, $dbGwCurrent, $dbWajibPajak, $dbSpec, $appDbLink;

    $today = date("Y-m-d");
    $mode = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
    $uuid = c_uuid();
    $nomor = isset($_REQUEST['nomor']) ? $_REQUEST['nomor'] : '';
    $nopno = $_REQUEST['nopno'];
    $svcid = $_REQUEST['svcid'];
    $sppt_doc_id = $_REQUEST['sppt_doc_id'];
    // var_dump('adasd','adasd','adasd','adasd','adasd','adasd','adasd','adasd','adasd','adasd',$status);die;
    if (!$readonly) {
        $aVal['CPM_OP_ALAMAT'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['almtOPPerubahan']));
        $aVal['CPM_OP_RT'] = $_REQUEST['rtOPPerubahan'];
        $aVal['CPM_OP_RW'] = $_REQUEST['rwOPPerubahan'];
        $aVal['CPM_WP_NAMA'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['nmWpPerubahan']));
        $aVal['CPM_WP_ALAMAT'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['almtWPPerubahan']));
        $aVal['CPM_WP_RT'] = $_REQUEST['rtWPPerubahan'];
        $aVal['CPM_WP_RW'] = $_REQUEST['rwWPPerubahan'];
        $aVal['CPM_WP_PROPINSI'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['propinsiPerubahan']));
        $aVal['CPM_WP_KOTAKAB'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kabupatenPerubahan']));
        $aVal['CPM_WP_KELURAHAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kelurahanPerubahan']));
        $aVal['CPM_WP_KECAMATAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kecamatanPerubahan']));
        $aVal['CPM_WP_NO_HP'] = $_REQUEST['hpWPPerubahan'];

        $aVal['CPM_OP_LUAS_TANAH'] = $_REQUEST['luasBumi'];

        $aSerti['CPM_NOMOR_SERTIFIKAT'] = $_REQUEST['nomorSertifikat'];
        $aSerti['CPM_TANGGAL'] = $_REQUEST['tanggalSertifikat'];
        $aSerti['CPM_NAMA_SERTIFIKAT'] = $_REQUEST['namaSertifikat'];
        $aSerti['CPM_JENIS_HAK'] = $_REQUEST['jenisHak'];
        $aSerti['CPM_NAMA_PEMEGANG'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['nmWpPerubahan']));
        
        $aVal['CPM_OT_ZONA_NILAI'] = $_REQUEST['OT_ZONA_NILAI'];
        $aVal['CPM_OT_JENIS'] = $_REQUEST['OT_JENIS'];
        $aVal['CPM_WP_STATUS'] = $_REQUEST['statusMilik'];
        $aVal['CPM_WP_PEKERJAAN'] = $_REQUEST['pekerjaan'];
        $aVal['CPM_WP_KODEPOS'] = $_REQUEST['kodepos'];
        $aVal['CPM_WP_NO_KTP'] = strtoupper($_REQUEST['noktp']);
        $aVal['CPM_OP_JML_BANGUNAN'] = isset($_REQUEST['jmlBangunan']) && !empty($_REQUEST['jmlBangunan']) ? $_REQUEST['jmlBangunan'] : 0;

        $bVal['CPM_WP_NAME'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['nmWpPerubahan']));
        $bVal['CPM_WP_ADDRESS'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['almtWPPerubahan']));
        $bVal['CPM_WP_RT'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['rtWPPerubahan']));
        $bVal['CPM_WP_RW'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['rwWPPerubahan']));
        $bVal['CPM_WP_PROVINCE'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['propinsiPerubahan']));
        $bVal['CPM_WP_KABUPATEN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kabupatenPerubahan']));
        $bVal['CPM_WP_KECAMATAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kecamatanPerubahan']));
        $bVal['CPM_WP_KELURAHAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kelurahanPerubahan']));
        $bVal['CPM_WP_HANDPHONE'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['hpWPPerubahan']));
    }
    // var_dump($aVal['CPM_OP_RT'], $aVal['CPM_OP_RW']);die;
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


    $res3 = $res2 = $res = true;
    if (!$readonly) {
        $res = $dbSpptPerubahan->edit($nomor, $aVal);
        $res = $dbSpptPerubahan->update_sertifikat($nopno, $aSerti);
        $res2 = $dbServices->editServices($nomor, $bVal);

        // die(var_dump($res, $res2));

        $sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"" . $appConfig['tahun_tagihan'] . "\",\"KELURAHAN\":\"\",\"TIPE\":\"3\",\"NOP\":\"" . $nopno . "\",\"SUSULAN\":\"0\"}";
        // die(var_dump($res));
        $bOK = GetRemoteResponse($appConfig['TPB_ADDRESS'], $appConfig['TPB_PORT'], $appConfig['TPB_TIMEOUT'], $sRequestStream, $sResp);
    }

    if ($res) {
            
        if ($status == 4) {

            #mengambil data pada final atau susulan
            $dataPenetapan = $dbUtils->selectPenetapan($_REQUEST['nopno'], $appConfig, c_uuid());
            $dataPenetapan['SPPT_TAHUN_PAJAK'] = $_REQUEST['spptTahunRubah'];

            #mengambil data dari service change
            if ($dataPerubahan = $dbSpptPerubahan->get($_REQUEST['sppt_doc_id'])) {
                $dataPerubahan = $dataPerubahan[0];

                $contentWP['CPM_WP_STATUS'] = $dataPerubahan['CPM_WP_STATUS'];
                $contentWP['CPM_WP_PEKERJAAN'] = $dataPerubahan['CPM_WP_PEKERJAAN'];
                $contentWP['CPM_WP_NAMA'] = $dataPerubahan['CPM_WP_NAMA'];
                $contentWP['CPM_WP_ALAMAT'] = $dataPerubahan['CPM_WP_ALAMAT'];
                $contentWP['CPM_WP_KELURAHAN'] = $dataPerubahan['CPM_WP_KELURAHAN'];
                $contentWP['CPM_WP_RT'] = $dataPerubahan['CPM_WP_RT'];
                $contentWP['CPM_WP_RW'] = $dataPerubahan['CPM_WP_RW'];
                $contentWP['CPM_WP_PROPINSI'] = $dataPerubahan['CPM_WP_PROPINSI'];
                $contentWP['CPM_WP_KOTAKAB'] = $dataPerubahan['CPM_WP_KOTAKAB'];
                $contentWP['CPM_WP_KECAMATAN'] = $dataPerubahan['CPM_WP_KECAMATAN'];
                $contentWP['CPM_WP_KODEPOS'] = $dataPerubahan['CPM_WP_KODEPOS'];
                $contentWP['CPM_WP_NO_HP'] = $dataPerubahan['CPM_WP_NO_HP'];
            }


            /*pengecekan :
			 * a) jika objek tanah dari fasum (4) menjadi bukan fasum (!=4) dan (tahun penetapan = tahun berlaku) maka input ke tagihan dan current
			 * b) jika tidak maka jika (tahun penetapan = tahun berlaku) maka update ke current
			 * selanjutnya setelah a atau b dilakukan update ke final
			 * dan hapus data service change
			 */

            // var_dump($_REQUEST['spptTahunRubah']); 
            //    var_dump($_REQUEST['spptTahunPajak']);
            //    exit;

            // jika spptTahunRubah & spptTahunPajak sama maka, langsung rubah pada saat ini 
            // jika berbeda maka hanya mengubah di final saja

            $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']);
            // var_dump( $_REQUEST['spptTahunPajak']);die;

            if ($_REQUEST['spptTahunRubah'] == $_REQUEST['spptTahunPajak']) { // pasti akan sama
                // echo "masuk 1";

                if ($dataPenetapan['CPM_OT_JENIS'] == 4 && $dataPerubahan['CPM_OT_JENIS'] != 4) {

                    #jika iya maka insert ke tagihan dan current

                    //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) or die(mysqli_error($DBLink));
                    if (!$GWDBLink) {
                        $res3 = false;
                        echo mysqli_error($GWDBLink);
                    }
                    if ($res3) {
                        $res3 = $dbGwCurrent->insertIntoTagihanSPPT($dataPenetapan, $appConfig, $GWDBLink);
                        if ($res3) {
                            $res3 = $dbGwCurrent->insertIntoCurrent($dataPenetapan, $appConfig);
                        }
                    }
                    mysqli_close($GWDBLink);
                } else { // jika CPM OT JENIS BUKAN 4
                    // echo "masuk 123";



                    // NEW 2018 - 4 -4

                    #Penetapan Ulang by ZNK (20 Juni 2017)

                    #ambil data current untuk mendapatkan nilai NJOPTKP dan Tagihan sebelumnya
                    $dataCurrent   = $dbGwCurrent->getDataCurrent($_REQUEST['nopno']);
                    $tagihanLama   = $dataCurrent['SPPT_PBB_HARUS_DIBAYAR'];
                    
                    mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                    #ambil data service change untuk mendapatkan total NJOP yang baru
                    $dataPerubahan = $dbServices->getDataChange($_REQUEST['nopno']);
                    
                    
                    $totalNJOPBaru = $dataPerubahan['CPM_NJOP_TANAH'] + $dataPerubahan['CPM_NJOP_BANGUNAN'];
                    if($dataPerubahan['CPM_NJOP_BANGUNAN']>0 && $totalNJOPBaru>10000000){
                        $njoptkp = 10000000;
                    }else{
                        $njoptkp = 0;
                    }
                    $njkp = $totalNJOPBaru - $njoptkp;

                    $tagihanBaru   = hitungTagihan($totalNJOPBaru, $njoptkp);
                    $njkp_baru     = getNJKP($totalNJOPBaru, $njoptkp);
                  

                    mysqli_select_db($GWDBLink, $appConfig['GW_DBNAME']);

                    $tglPenetapan       = date("Y-m-d");
                    // var_dump($appConfig['GW_DBNAME']);
                    $sppt               = $dbGwCurrent->getDataTagihanSPPT($_REQUEST['nopno'], $_REQUEST['spptTahunPajak'], $GWDBLink);
                    // var_dump($sppt);
                    // exit;
                    
                    $valTagihanSPPT = array();
                    $valCurrentSPPT = array();
                    if($sppt['PAYMENT_FLAG'] == 1) { // jika sudah bayar maka
                        // $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR']   = round($tagihanBaru); Nominal Terbayar didak bisa di edit
                    }else{ // jika belum bayar maka
                        $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR']   = round($tagihanBaru);
                        $valCurrentSPPT['SPPT_PBB_HARUS_DIBAYAR']   =  round($tagihanBaru);
                    } // end jika belum bayar
                    $valTagihanSPPT['WP_PEKERJAAN']     = $dataPerubahan['CPM_WP_PEKERJAAN'];
                    $valTagihanSPPT['WP_NAMA']          = $dataPerubahan['CPM_WP_NAMA'];
                    $valTagihanSPPT['WP_ALAMAT']        = $dataPerubahan['CPM_WP_ALAMAT'];
                    $valTagihanSPPT['WP_KELURAHAN']     = $dataPerubahan['CPM_WP_KELURAHAN'];
                    $valTagihanSPPT['WP_RT']            = $dataPerubahan['CPM_WP_RT'];
                    $valTagihanSPPT['WP_RW']            = $dataPerubahan['CPM_WP_RW'];
                    $valTagihanSPPT['WP_KOTAKAB']       = $dataPerubahan['CPM_WP_KOTAKAB'];
                    $valTagihanSPPT['WP_KECAMATAN']     = $dataPerubahan['CPM_WP_KECAMATAN'];
                    $valTagihanSPPT['WP_KODEPOS']       = $dataPerubahan['CPM_WP_KODEPOS'];
                    $valTagihanSPPT['WP_NO_HP']         = $dataPerubahan['CPM_WP_NO_HP'];

                    $valTagihanSPPT['OP_LUAS_BUMI']     = $dataPerubahan['CPM_OP_LUAS_TANAH'];
                    $valTagihanSPPT['OP_LUAS_BANGUNAN'] = $dataPerubahan['CPM_OP_LUAS_BANGUNAN'];
                    $valTagihanSPPT['OP_KELAS_BUMI']    = $dataPerubahan['CPM_OP_KELAS_TANAH'];
                    $valTagihanSPPT['OP_KELAS_BANGUNAN'] = $dataPerubahan['CPM_OP_KELAS_BANGUNAN'];
                    $valTagihanSPPT['OP_NJOP_BUMI']     = $dataPerubahan['CPM_NJOP_TANAH'];
                    $valTagihanSPPT['OP_NJOP_BANGUNAN'] = $dataPerubahan['CPM_NJOP_BANGUNAN'];
                    $valTagihanSPPT['OP_NJOP']          = $totalNJOPBaru;
                    $valTagihanSPPT['OP_NJOPTKP']       = $njoptkp;
                    $valTagihanSPPT['OP_NJKP']          = $njkp_baru;

                    $valTagihanSPPT['OP_ALAMAT']        = $dataPerubahan['CPM_OP_ALAMAT'];
                    $valTagihanSPPT['OP_RT']            = $dataPerubahan['CPM_OP_RT'];
                    $valTagihanSPPT['OP_RW']            = $dataPerubahan['CPM_OP_RW'];

                    // $valCurrentSPPT['SELISIH']                  = $selisih;  
                    // $valCurrentSPPT['PAYMENT_TYPE']             = $paymentType;  
                    // $valCurrentSPPT['TGL_PENETAPAN_ULANG']      = $tglPenetapan;
                    
                    $pcsPenetapanUlang = $dbGwCurrent->updateTagihanSPPT($_REQUEST['nopno'], $_REQUEST['spptTahunPajak'], $valTagihanSPPT, $GWDBLink);
                    if ($pcsPenetapanUlang) {
                        mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                        $res3 = $dbSpptPerubahan->updateToCurrent($svcid, $appConfig);
                        if ($res3) {
                            $pcsPenetapanUlang = $dbGwCurrent->updateCurrentSPPT($_REQUEST['nopno'], $_REQUEST['spptTahunPajak'], $valCurrentSPPT, $appConfig);
                        }
                    } else {
                        echo "ERR_UTS";
                        $pcsPenetapanUlang = false;
                    }
                    mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                    //end by 35utech 
                    // $pcsPenetapanUlang = true;
                }

                // end jika sppt tahun rubah sama dengan sppt tahun pajak

            } else { //  jika sppt tahun rubah berbeda dengan sppt tahun pajak

                // echo "masuk 2";


                $dataCurrent   = $dbGwCurrent->getDataCurrent($_REQUEST['nopno']);
                $tagihanLama   = round($dataCurrent['SPPT_PBB_HARUS_DIBAYAR']);


                $dataPerubahan = $dbServices->getDataChange($_REQUEST['nopno']);

                $totalNJOPBaru = $dataPerubahan['CPM_NJOP_TANAH'] + $dataPerubahan['CPM_NJOP_BANGUNAN'];
                if($dataPerubahan['CPM_NJOP_BANGUNAN']>0 && $totalNJOPBaru>10000000){
                    $njoptkp = 10000000;
                }else{
                    $njoptkp = 0;
                }
                $njkp = $totalNJOPBaru - $njoptkp;
                $tagihanBaru   = hitungTagihan($totalNJOPBaru, $njoptkp);
                $njkp_baru     = getNJKP($totalNJOPBaru, $njoptkp);


                $GWDBLink = mysqli_connect($appConfig['GW_DBHOST'], $appConfig['GW_DBUSER'], $appConfig['GW_DBPWD'], $appConfig['GW_DBNAME'], $appConfig['GW_DBPORT']);
                if (!$GWDBLink) {
                    $res3 = false;
                    echo mysqli_error($GWDBLink);
                }
                //mysql_select_db($appConfig['GW_DBNAME'], $GWDBLink) ;

                $sppt = $dbGwCurrent->getDataTagihanSPPT($_REQUEST['nopno'], $_REQUEST['spptTahunPajak'], $GWDBLink); // ambil data SPPT

                $valTagihanSPPT = array();
                if ($sppt['PAYMENT_FLAG'] == 1) { // jika sudah bayar - nominal tidak bisa di edit
                    // $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR']   = round($tagihanBaru);
                }else{
                    $valTagihanSPPT['SPPT_PBB_HARUS_DIBAYAR']   = round($tagihanBaru);
                }
                $valTagihanSPPT['WP_PEKERJAAN']     = $dataPerubahan['CPM_WP_PEKERJAAN'];
                $valTagihanSPPT['WP_NAMA']          = $dataPerubahan['CPM_WP_NAMA'];
                $valTagihanSPPT['WP_ALAMAT']        = $dataPerubahan['CPM_WP_ALAMAT'];
                $valTagihanSPPT['WP_KELURAHAN']     = $dataPerubahan['CPM_WP_KELURAHAN'];
                $valTagihanSPPT['WP_RT']            = $dataPerubahan['CPM_WP_RT'];
                $valTagihanSPPT['WP_RW']            = $dataPerubahan['CPM_WP_RW'];
                $valTagihanSPPT['WP_KOTAKAB']       = $dataPerubahan['CPM_WP_KOTAKAB'];
                $valTagihanSPPT['WP_KECAMATAN']     = $dataPerubahan['CPM_WP_KECAMATAN'];
                $valTagihanSPPT['WP_KODEPOS']       = $dataPerubahan['CPM_WP_KODEPOS'];
                $valTagihanSPPT['WP_NO_HP']         = $dataPerubahan['CPM_WP_NO_HP'];

                $valTagihanSPPT['OP_LUAS_BUMI']     = $dataPerubahan['CPM_OP_LUAS_TANAH'];
                $valTagihanSPPT['OP_LUAS_BANGUNAN'] = $dataPerubahan['CPM_OP_LUAS_BANGUNAN'];
                $valTagihanSPPT['OP_KELAS_BUMI']    = $dataPerubahan['CPM_OP_KELAS_TANAH'];
                $valTagihanSPPT['OP_KELAS_BANGUNAN'] = $dataPerubahan['CPM_OP_KELAS_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP_BUMI']     = $dataPerubahan['CPM_NJOP_TANAH'];
                $valTagihanSPPT['OP_NJOP_BANGUNAN'] = $dataPerubahan['CPM_NJOP_BANGUNAN'];
                $valTagihanSPPT['OP_NJOP']          = $totalNJOPBaru;
                $valTagihanSPPT['OP_NJOPTKP']       = $njoptkp;
                $valTagihanSPPT['OP_NJKP']          = $njkp_baru;

                $valTagihanSPPT['OP_ALAMAT']        = $dataPerubahan['CPM_OP_ALAMAT'];
                $valTagihanSPPT['OP_RT']            = $dataPerubahan['CPM_OP_RT'];
                $valTagihanSPPT['OP_RW']            = $dataPerubahan['CPM_OP_RW'];

                $valCurrentSPPT['SPPT_PBB_HARUS_DIBAYAR']   = round($tagihanBaru);
                $res3 = $dbGwCurrent->updateTagihanSPPT($_REQUEST['nopno'], $_REQUEST['spptTahunPajak'], $valTagihanSPPT, $GWDBLink);
                if ($res3) {
                    mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
                    $res3 = $dbSpptPerubahan->updateToCurrent($svcid, $appConfig);
                    // $res3 = $dbGwCurrent->updateCurrentSPPT($_REQUEST['nopno'],$_REQUEST['spptTahunPajak'],$valCurrentSPPT,$appConfig);
                    // exit;   
                    // var_dump($res3);
                    if (!$res3) {
                        echo "ERR_UCS ";
                        $res3 = false;
                    }
                } else {
                    echo "ERR_UTS";
                    $res3 = false;
                }
            }

            mysqli_select_db($GWDBLink, $appConfig['ADMIN_SW_DBNAME']);
            if ($res3) {
                $res = $dbWajibPajak->save($dataPerubahan['CPM_WP_NO_KTP'], $contentWP);
                $res3 = $dbSpptPerubahan->updateToFinal($_REQUEST['sppt_doc_id'], $svcid);
                if ($res3) {
                    $res3 = $dbSpptPerubahan->deleteDataPerubahan($_REQUEST['sppt_doc_id']);
                } else {
                    echo "Gagal melakukan penghapusan data perubahan";
                }
            } else {
                echo "Gagal melakukan perubahan";
            }
        }
        // echo "masuk";
        if ($res && $res3) {
            // var_dump($bVal);die;
           
            $res2 = $dbServices->editServices($svcid, $bVal);
            if ($res2) {
                echo 'Data berhasil disimpan...!';

                if ($_REQUEST['btn-save'] == 'Simpan Sementara')
                    $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_perubahan_form'] . "&svcid=" . $nomor;
                else $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_perubahan'];

                echo "<script language='javascript'>
                        $(document).ready(function(){
                            window.location = \"./main.php?param=" . base64_encode($params) . "\"
                        })
                    </script>";
            } else {
                echo "Terjadi kegagalan.<br/>";
                echo mysqli_error($DBLink);
            }
        } else {
            echo "Terjadi kegagalan.<br/>";
            echo mysqli_error($DBLink);
        }
    } else {
        echo "Terjadi kegagalan.<br/>";
        echo mysqli_error($DBLink);
    }

    //    if ($res === false || $res2 === false || $res3 === false) {
    //        echo $res . $res2 . $res3;
    //        die();
    //    }


}

$save = isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : '';

if ($save == 'Simpan Sementara') {
    if ($arConfig['usertype'] == "pendata") {
        save(1);
    } elseif ($arConfig['usertype'] == "verifikator") {
        save(2);
    } elseif ($arConfig['usertype'] == "penyetuju") {
        save(3);
    }
} elseif ($save == 'Simpan') {
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


    $initData = $dbSpptPerubahan->getInitData($svcid);
    $NOP = (isset($initData['CPM_NOP']) ? $initData['CPM_NOP'] : '');

    if($NOP!=''){
        $sertifikats = $dbSpptPerubahan->get_sertifikat($NOP);
        if(count($sertifikats)>0){
            $sertifikats = $sertifikats[0];
            $initData['CPM_NOMOR_SERTIFIKAT']   = $sertifikats['CPM_NOMOR_SERTIFIKAT'];
            $initData['CPM_TANGGAL_SERTIFIKAT'] = $sertifikats['CPM_TANGGAL'];
            $initData['CPM_NAMA_SERTIFIKAT']    = $sertifikats['CPM_NAMA_SERTIFIKAT'];
            $initData['CPM_JENIS_HAK']          = $sertifikats['CPM_JENIS_HAK'];
        }
    }

    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    // echo "ok";
    // var_dump($initData);
    echo formPenerimaan($initData);
}

function hitungTagihan($njop, $njoptkp)
{
    global $appConfig, $dbUtils;

    $njoptkp    = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);

    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }

    $tarif      = $dbUtils->getTarif($njkp);
    $tagihan    = $njkp * ($tarif / 100);

    if ($tagihan < $minTagihan) $tagihan = $minTagihan;

    return $tagihan;
}

function getNJKP($njop, $njoptkp)
{
    global $appConfig, $dbUtils;

    $njoptkp    = ($njoptkp != 0 || $njoptkp != null || $njoptkp != "" ? $njoptkp : 0);
    $minTagihan = ($appConfig['minimum_sppt_pbb_terhutang'] != 0 ? $appConfig['minimum_sppt_pbb_terhutang'] : 0);

    if ($njop > $njoptkp) {
        $njkp = $njop - $njoptkp;
    } else {
        $njkp = 0;
    }
   
    return $njkp;
}
?>


<script type="text/javascript">
    $("#hitung-njop").click(function() {
        $("#load-mask").css("display", "block");
        $("#load-content").fadeIn();

        loadNB('<?php echo $NBParam ?>');
    });

    function loadNBSuccess(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");

        if (params.responseText) {
            var objResult = Ext.decode(params.responseText);

            if (objResult.RC == "0000") {
                alert('Penilaian sukses.');
                //document.location.reload(true);
            } else {
                alert('Gagal melakukan penilaian. Terjadi kesalahan server');
            }
        } else {
            alert('Gagal melakukan penilaian. Terjadi kesalahan server');
        }
    }

    function loadNBFailure(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
    }

    function loadNB(svr_param) {

        var params = "{\"SVR_PRM\":\"" + svr_param + "\",\"NOP\":\"<?php echo $NOP; ?>\", \"TAHUN\":\"<?php echo $appConfig['tahun_tagihan']; ?>\", \"TIPE\":\"3\", \"SUSULAN\":\"0\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
            url: 'inc/PBB/svc-penilaian.php',
            success: loadNBSuccess,
            failure: loadNBFailure,
            params: {
                req: params
            }
        });

    }

    function deleteLampiranSuccess(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");

        if (params.responseText) {
            if (params.responseText == "sukses") {
                alert('Menghapus lampiran sukses.');
                document.location.reload(true);
            } else {
                alert('Gagal menghapus lampiran. Terjadi kesalahan server');
            }
        } else {
            alert('Gagal menghapus lampiran. Terjadi kesalahan server');
        }
    }

    function deleteLampiranFailure(params) {
        $("#load-content").css("display", "none");
        $("#load-mask").css("display", "none");
        alert('Gagal menghapus lampiran. Terjadi kesalahan server');
    }

    function deleteLampiran(doc_id, op_num, cpm_id) {
        if (confirm('Anda yakin data lampiran akan dihapus?')) {
            var params = "{\"TYPE\":\"PERUBAHAN\", \"DOC_ID\":\"" + doc_id + "\", \"OP_NUM\":\"" + op_num + "\", \"CPM_ID\":\"" + cpm_id + "\"}";
            params = Base64.encode(params);
            Ext.Ajax.request({
                url: 'function/PBB/mutasi/svc-deletelampiran.php',
                success: deleteLampiranSuccess,
                failure: deleteLampiranFailure,
                params: {
                    req: params
                }
            });
        }
    }

    function iniAngkaDenganKoma(evt, x) {
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if (charCode >= 46 || (charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
            return true;
        } else {
            alert("Input hanya boleh angka dan titik!");
            return false;
        }
    }
</script>
<style type="text/css">
    #btnClose {
        cursor: pointer;
    }

    .linkto:hover,
    .linkstpd:hover,
    .linkdate:hover {
        color: #ce7b00;
    }

    .linkto,
    .linkstpd,
    .linkdate {
        text-decoration: underline;
        cursor: pointer;
    }

    #load-mask,
    #load-content {
        display: none;
        position: fixed;
        height: 100%;
        width: 100%;
        top: 0;
        left: 0;
    }

    #load-mask {
        background-color: #000000;
        filter: alpha(opacity=70);
        opacity: 0.7;
        z-index: 1;
    }

    #load-content {
        z-index: 2;
    }

    #closeddate {
        cursor: pointer;
    }

    #loader {
        margin-right: auto;
        margin-left: auto;
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }

    .table-penilaian th {
        background-color: #ffffff;
        color: #000000;
        padding-bottom: 4px;
        padding-top: 5px;
        text-align: center;
    }

    .table-penilaian td,
    .table-penilaian th {
        border: 1px solid #000000;
        padding: 3px 7px 2px;
        cellspacing: 0px;
    }

    .table-penilaian {
        border-collapse: collapse;
        width: 100%;
    }
</style>

<div class="col-md-12">
    <div id="load-content">
        <div id="loader">
            <img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
        </div>
    </div>
    <div id="load-mask"></div>
</div>