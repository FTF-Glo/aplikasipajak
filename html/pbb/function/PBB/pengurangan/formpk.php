<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'pengurangan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");

echo "<link href=\"view/PBB/spop.css\" rel=\"stylesheet\" type=\"text/css\"/>";

echo "<link href=\"inc/PBB/jquery-tooltip/jquery.tooltip.css\" rel=\"stylesheet\" type=\"text/css\"/>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-tooltip/jquery.tooltip.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/tax/service/jquery.validate.min.js\"></script>\n";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/tax/mod-pelayanan/func-mod-pelayanan.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

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

function isExistSID($nomor = "")
{
    global $DBLink;
    $query = "SELECT CPM_RE_SID FROM cppmod_pbb_service_reduce WHERE CPM_RE_SID='$nomor'";
    $res = mysqli_query($DBLink, $query);
    $nRes = mysqli_num_rows($res);
    return $nRes;
}

function formPenerimaan($initData, $initDataRed)
{
    global $a, $m, $appConfig;

    $today = date("d-m-Y");

    $cityID = $appConfig['KODE_KOTA'];
    $cityName = $appConfig['NAMA_KOTA'];
    $optionCityOP = "<option valued=$cityID>$cityName</option>";

    $provID = $appConfig['KODE_PROVINSI'];
    $provName = $appConfig['NAMA_PROVINSI'];
    $optionProvOP = "<option valued=$provID>$provName</option>";


    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

    $allProv = getPropinsi();

    $optionProvWP = "";

    if ($initData['CPM_ID'] != '') {
        //$hiddenModeInput = '<input type="hidden" name="mode" value="edit">';

        $kabkotaWP = getKabkota($initData['CPM_WP_PROVINCE']);
        $kecWP = getKecamatan($initData['CPM_WP_KABUPATEN']);
        $kelWP = getKelurahan($initData['CPM_WP_KECAMATAN']);

        if ($initData['CPM_WP_KABUPATEN'] == $initData['CPM_OP_KABUPATEN']) $kecOP = $kecWP;
        else $kecOP = getKecamatan($cityID);

        if ($initData['CPM_WP_KECAMATAN'] == $initData['CPM_OP_KECAMATAN']) $kelOP = $kelWP;
        else $kelOP = getKelurahan($initData['CPM_OP_KECAMATAN']);

        foreach ($allProv as $row) {
            if ($initData['CPM_WP_PROVINCE'] == $row['id'])
                $optionProvWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionProvWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

        foreach ($kabkotaWP as $row) {
            if ($initData['CPM_WP_KABUPATEN'] == $row['id'])
                $optionKabWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKabWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

        foreach ($kecWP as $row) {
            if ($initData['CPM_WP_KECAMATAN'] == $row['id'])
                $optionKecWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKecWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kelWP as $row) {
            if ($initData['CPM_WP_KELURAHAN'] == $row['id'])
                $optionKelWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKelWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

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
    } else {
        $nomor = generateNumber(date('Y'), date('m'));
        $kabkotaWP = getKabkota($provID);
        $kecWP = $kecOP = getKecamatan($cityID);
        $kelWP = $kelOP = getKelurahan($kecWP[0]['id']);

        foreach ($allProv as $row) {
            if ($provID == $row['id'])
                $optionProvWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";


            else
                $optionProvWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kabkotaWP as $row) {
            if ($cityID == $row['id'])
                $optionKabWP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKabWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

        foreach ($kecWP as $row) {
            $optionKecWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kelWP as $row) {
            $optionKelWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

        $optionKecOP = $optionKecWP;
        $optionKelOP = $optionKelWP;
    }


    $html = "
    <style>
    input {
        border:1px solid #dadada;
        border-radius:2px;
        font-size:12px;
        padding:4px; 
    }

    input:focus { 
        outline:none;
        border-color:#9ecaed;
        box-shadow:0 0 10px #9ecaed;
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
            
			$(\"#form-penerimaan\").submit(function(e){
				ids = 0;
				$.each($(\".attach:checked\"), function() {
					ids +=  parseInt($(this).val());
				});
				
				$(\"#attachment\").val(ids);
			});
		
			$('#tglMasuk').datepicker({dateFormat: 'dd-mm-yy'});
			$('#tglSPPT').datepicker({dateFormat: 'dd-mm-yy'});
			
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
            jenisBerkas[9] = new Array(1,3,5,6,8,9,10);
            
            $('#jnsBerkas').change(function(){
                var berkas = jenisBerkas[$(this).val()-1];                
                $('.berkas').hide();
                
                for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
            });
			";

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
                    almtOP : \"required\",
                    rtOP : {
                            required : true,
                            number : true
                          },
                    rwOP : {
                            required : true,
                            number : true
                          }                          
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
                    almtOP : \"\",
                    rtOP : \"\",
                    rwOP : \"\"
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
            
            $('#kecamatanOP').change(function(){
                $.ajax({
                   type: 'POST',
                   url: './function/tax/service/svc-search-city.php',
                   data: 'type=3&id='+$(this).val(),
                   success: function(msg){
                        $('#kelurahanOP').html(msg);
                   }
                 });
            });
            
            function getWilayah(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/tax/service/svc-search-city.php',
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
		
		function getDataOp(){
				nop = $.trim($('#nop').val());
				$.ajax({
					type: 'POST',
					data: 'nop='+nop,
					url: './function/PBB/loket/dataOP.php',
					success: function(res){
						d=jQuery.parseJSON(res);
						if($.trim(d.alamatOP)==''){
							alert(\"NOP tidak tersedia\");
							return false;
						}
						$('#almtOP').val(d.alamatOP);
						$('#rtOP').val(d.rtOP);
						$('#rwOP').val(d.rwOP);
						$('#kelurahanOP').html('<option value=\"'+d.idkelurahanOP+'\">'+d.kelurahanOP+'</option>');
						$('#kecamatanOP').html('<option value=\"'+d.idkecamatanOP+'\">'+d.kecamatanOP+'</option>');
						$('#kabupatenOP').html('<option value=\"'+d.idkabupatenOP+'\">'+d.kabupatenOP+'</option>');
						$('#propinsiOP').html('<option value=\"'+d.idpropinsiOP+'\">'+d.propinsiOP+'</option>');
						$('#nmWp').val(d.namaWP);
						$('#almtWP').val(d.alamatWP);
						$('#rtWP').val(d.rtWP);
						$('#rwWP').val(d.rwWP);
						$('#kelurahan').html('<option value=\"'+d.idkelurahanWP+'\">'+d.kelurahanWP+'</option>');
						$('#kecamatan').html('<option value=\"'+d.idkecamatanWP+'\">'+d.kecamatanWP+'</option>');
						$('#kabupaten').html('<option value=\"'+d.idkabupatenWP+'\">'+d.kabupatenWP+'</option>');
						$('#propinsi').html('<option value=\"'+d.idpropinsiWP+'\">'+d.propinsiWP+'</option>');
					}	
				});
		}

    </script>
    <div id=\"main-content-pengurangan\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	$hiddenModeInput
	<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table width=\"1024\" border=\"0\" cellspacing=\"1\" cellpadding=\"10\">
                            <tr>
                              <td colspan=\"2\" align=\"center\"><strong><font size=\"+2\">Pengurangan.</font></strong><br /><br /></td>
                            </tr>
                            <tr>
                              <td align=\"center\"><table width=\"auto\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td width=\"auto\">Nomor</td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nomor\" id=\"nomor\" size=\"50\" maxlength=\"50\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td colspan=\"2\"><strong>Letak Objek Pajak</strong></td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"50\" maxlength=\"50\" value=\"" . (($initData['CPM_REPRESENTATIVE'] != '') ? $initData['CPM_REPRESENTATIVE'] : '') . "\" placeholder=\"Nama Kuasa\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"almtWP\">Alamat OP</label></td>
                                      <td width=\"auto\">
										<input type=\"text\" name=\"almtOP\" id=\"almtOP\" size=\"50\" maxlength=\"500\" value=\"" . (($initData['CPM_OP_ADDRESS'] != '') ? $initData['CPM_OP_ADDRESS'] : '') . "\" placeholder=\"Alamat\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nmWp\" id=\"nmWp\" size=\"50\" maxlength=\"50\" value=\"" . (($initData['CPM_WP_NAME'] != '') ? $initData['CPM_WP_NAME'] : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"rtOP\">RT/RW</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"rtOP\" id=\"rtOP\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RT'] != '') ? $initData['CPM_OP_RT'] : '') . "\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwOP\" id=\"rwOP\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RW'] != '') ? $initData['CPM_OP_RW'] : '') . "\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . (($initData['CPM_DATE_RECEIVE'] != '') ? $initData['CPM_DATE_RECEIVE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"provinsiOP\">Provinsi</label></td>
                                      <td width=\"auto\">
                                        <select name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"almtWP\">Alamat WP</label></td>
                                      <td width=\"auto\">
										<input type=\"text\" name=\"almtWP\" id=\"almtWP\" size=\"50\" maxlength=\"500\" value=\"" . (($initData['CPM_WP_ADDRESS'] != '') ? $initData['CPM_WP_ADDRESS'] : '') . "\" placeholder=\"Alamat\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"rtWP\">RT/RW</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"rtWP\" id=\"rtWP\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RT'] != '') ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwWP\" id=\"rwWP\" size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RW'] != '') ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"kecamatanOP\">Kecamatan</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"propinsi\">Provinsi</label></td>
                                      <td width=\"auto\">
                                        <select name=\"propinsi\" id=\"propinsi\">$optionProvWP</select>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"kelurahanOP\">Kelurahan</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kabupaten\" id=\"kabupaten\">$optionKabWP</select>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"PBBterutang\">PBB yang terutang</label></td>
                                      <td width=\"auto\">
										<input type=\"text\" name=\"PBBterutang\" id=\"PBBterutang\" size=\"50\" maxlength=\"50\" value=\"" . (($initDataRed['CPM_RE_DUE'] != '') ? $initDataRed['CPM_RE_DUE'] : '') . "\" placeholder=\"PBB yang terutang\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kecamatan\">Kecamatan</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kecamatan\" id=\"kecamatan\">$optionKecWP</select>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"tglSPPT\">Tanggal SPPT/SKP PBB diterima</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"tglSPPT\" id=\"tglSPPT\" size=\"10\" maxlength=\"10\" value=\"" . (($initData['CPM_RE_DATE_SPPT'] != '') ? $initData['CPM_RE_DATE_SPPT'] : $today) . "\" placeholder=\"Tgl SPPT\"/>                                      
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kelurahan\">Kelurahan</label></td>
                                      <td width=\"auto\">
                                        <select name=\"kelurahan\" id=\"kelurahan\">$optionKelWP</select>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"tglSPPT\">Tahun Pajak</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"thnPajak\" id=\"thnPajak\" size=\"10\" maxlength=\"10\" value=\"" . (($initDataRed['CPM_RE_YEAR'] != '') ? $initDataRed['CPM_RE_YEAR'] : '') . "\" placeholder=\"Tahun Pajak\"/>                                      
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\">No. HP WP</td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"hpWP\" id=\"hpWP\" size=\"15\" maxlength=\"15\" value=\"" . (($initData['CPM_WP_HANDPHONE'] != '') ? $initData['CPM_WP_HANDPHONE'] : '') . "\" placeholder=\"Nomor HP\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"tglSPPT\">Pengurangan PBB sebesar</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"persenPengurangan\" id=\"persenPengurangan\" size=\"10\" maxlength=\"10\" value=\"" . (($initDataRed['CPM_RE_PERCENT'] != '') ? $initDataRed['CPM_RE_PERCENT'] : '') . "\"/>   
										% dari PBB yang terutang
                                      </td>
                                    </tr>
									  <tr>
                                      <!-- <td width=\"auto\"><label for=\"jnsBerkas\">Jenis Berkas</label></td>
                                      <td width=\"auto\">
                                        <select name=\"jnsBerkas\" id=\"jnsBerkas\">
                                            <option value=\"1\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 1) ? 'selected="selected"' : '') . ">OP Baru</option>
                                            <option value=\"2\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 2) ? 'selected="selected"' : '') . ">Pemecahan</option>
                                            <option value=\"3\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 3) ? 'selected="selected"' : '') . ">Penggabungan</option>
                                            <option value=\"4\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 4) ? 'selected="selected"' : '') . ">Mutasi</option>
                                            <option value=\"5\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 5) ? 'selected="selected"' : '') . ">Perubahan Data</option>
                                            <option value=\"6\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 6) ? 'selected="selected"' : '') . ">Pembatalan</option>
                                            <option value=\"7\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 7) ? 'selected="selected"' : '') . ">Salinan</option>
                                            <option value=\"8\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 8) ? 'selected="selected"' : '') . ">Penghapusan</option>
                                            <option value=\"9\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 9) ? 'selected="selected"' : '') . ">Pengurangan</option>
                                            <option value=\"10\" " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 10) ? 'selected="selected"' : '') . ">Keberatan</option>
                                        </select>
                                      </td> -->
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nop\">NOP</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" maxlength=\"50\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" placeholder=\"NOP\" />
                                      </td>
									    <td width=\"30\">&nbsp</td>
                                      <td width=\"auto\" colspan=\"2\" rowspan=\"2\">
									Alasan mengajukan permohonan <br><br>
										<label for=\"alasan1\">*</label><input type=\"text\" name=\"alasan1\" id=\"alasan1\" size=\"80\" maxlength=\"500\" value=\"" . (($initDataRed['CPM_RE_ARGUEMENT'] != '') ? $initDataRed['CPM_RE_ARGUEMENT'] : '') . "\" placeholder=\"Alasan 1\" /><br><br>
										<label for=\"alasan2\">*</label><input type=\"text\" name=\"alasan2\" id=\"alasan2\" size=\"80\" maxlength=\"500\" placeholder=\"Alasan 2\" /><br><br>
										<label for=\"alasan3\">*</label><input type=\"text\" name=\"alasan3\" id=\"alasan3\" size=\"80\" maxlength=\"500\" placeholder=\"Alasan 3\" /><br><br>
										<label for=\"alasan4\">*</label><input type=\"text\" name=\"alasan4\" id=\"alasan4\" size=\"80\" maxlength=\"500\" placeholder=\"Alasan 4\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\" valign=\"top\" colspan=\"3\">Kelengkapan Dokumen : <br><br>
                                          <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                              <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 1) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                                              <li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 2) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP</li>
                                              <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 4) ? "checked=\"checked\"" : "") . "> Foto Copy KTP</li>
                                              <li id=\"berkas4\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 8) ? "checked=\"checked\"" : "") . "> Foto Copy KTP / Kartu Keluarga</li>
                                              <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 16) ? "checked=\"checked\"" : "") . "> Foto Copy Bukti Kepemilikan Tanah</li>
                                              <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 32) ? "checked=\"checked\"" : "") . "> Foto Copy IMB</li>
                                              <li id=\"berkas7\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 64) ? "checked=\"checked\"" : "") . "> Bukti Pelunasan PBB Tahun Sebelumnya</li>
                                              <li id=\"berkas8\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 128) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Pajak Terutan (SPPT)</li>
                                              <li id=\"berkas9\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 256) ? "checked=\"checked\"" : "") . "> Surat Ketetapan Pajak Daerah (SKPD)</li>
                                              <li id=\"berkas10\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"512\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 512) ? "checked=\"checked\"" : "") . "> Surat Setoran Pajak Daerah (SSPD)</li>
                                              <li id=\"berkas11\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"1024\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 1024) ? "checked=\"checked\"" : "") . "> Surat Kuasa (bila dikuasakan)</li>
                                              <li id=\"berkas12\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"2048\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 2048) ? "checked=\"checked\"" : "") . "> Foto Copy SPPT PBB / SKP tahun lalu</li>
                                              <li id=\"berkas13\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"4096\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 4096) ? "checked=\"checked\"" : "") . "> Foto Copy bukti pembayaran PBB yang terakhir</li>
                                          </ol>
									 </td>                                          
                                    </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td colspan=\"2\">&nbsp;</td>
                            </tr>                        
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">
                                  <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                                  &nbsp;
                                  <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\" />
                                  &nbsp;
								  <input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"' />
                              </td>
                            </tr>
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
                            </tr>
                      </table>
                    </form></div>";
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

function getDataDefault()
{
    $default = array(
        'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
        'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
        'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
    );
}

function getLastNumber($year, $mon)
{
    global $DBLink;

    $qry = "select SUBSTRING(max(CPM_ID), -3) as CPM_ID from cppmod_pbb_services where CPM_ID like 'SPOP/{$year}/{$mon}%'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CPM_ID'];
        }

        return "000";
    }
}

function generateNumber($year, $mon)
{
    $lastNumber = getLastNumber($year, $mon);
    $newNumber = $lastNumber + 1;
    return "SPOP/" . $year . "/" . $mon . "/" . substr('000' . $newNumber, -3);
}

function save($status)
{
    global $data, $DBLink, $uid;

    //$mode  				= @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
    $nomor                 = $_REQUEST['nomor'];
    $thnPajak            = $_REQUEST['thnPajak'];
    $pbbterutang        = $_REQUEST['PBBterutang'];
    $tglSPPT            = $_REQUEST['tglSPPT'];
    $persenPengurangan    = $_REQUEST['persenPengurangan'];
    //variable alasan
    $alasan                = array();
    if ($_REQUEST['alasan1']) array_push($alasan, $_REQUEST['alasan1']);
    if ($_REQUEST['alasan2']) array_push($alasan, $_REQUEST['alasan2']);
    if ($_REQUEST['alasan3']) array_push($alasan, $_REQUEST['alasan3']);
    if ($_REQUEST['alasan4']) array_push($alasan, $_REQUEST['alasan4']);
    $gabAlasan            = implode("#", $alasan);

    $nmKuasa             = $_REQUEST['nmKuasa'];
    $nmWp                 = $_REQUEST['nmWp'];
    $tglMasuk             = substr($_REQUEST['tglMasuk'], 6, 4) . '-' . substr($_REQUEST['tglMasuk'], 3, 2) . '-' . substr($_REQUEST['tglMasuk'], 0, 2);
    $almtWP             = $_REQUEST['almtWP'];
    $rtWP                 = $_REQUEST['rtWP'];
    $rwWP                 = $_REQUEST['rwWP'];
    $propinsiWP         = $_REQUEST['propinsi'];
    $kabupatenWP         = $_REQUEST['kabupaten'];
    $kecamatanWP        = $_REQUEST['kecamatan'];
    $kelurahanWP         = $_REQUEST['kelurahan'];
    $hpWP                 = $_REQUEST['hpWP'];
    $nop                 = $_REQUEST['nop'];
    $almtOP             = $_REQUEST['almtOP'];
    $rtOP                 = $_REQUEST['rtOP'];
    $rwOP                 = $_REQUEST['rwOP'];
    $kecamatanOP        = $_REQUEST['kecamatanOP'];
    $kelurahanOP         = $_REQUEST['kelurahanOP'];
    $attachment         = $_REQUEST['attachment'];

    if (!isExistSID($nomor)) {
        $qry1 = "INSERT INTO cppmod_pbb_service_reduce (CPM_RE_ID,CPM_RE_SID,CPM_RE_DUE,CPM_RE_YEAR,CPM_RE_DATE_SPPT,CPM_RE_ARGUEMENT,CPM_RE_PERCENT) VALUES ('default','{$nomor}','{$thnPajak}','{$pbbterutang}','{$tglSPPT}','{$gabAlasan}','{$persenPengurangan}')";
    } else {
        $qry1 = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_DUE='{$pbbterutang}',CPM_RE_YEAR='{$thnPajak}',CPM_RE_DATE_SPPT='{$tglSPPT}',CPM_RE_ARGUEMENT='{$gabAlasan}',CPM_RE_PERCENT='{$persenPengurangan}'";
    }

    $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', 
			CPM_WP_KELURAHAN='{$kelurahanWP}', CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', 
			CPM_OP_ADDRESS='{$almtOP}', CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
			CPM_RECEIVER='{$uid}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status} WHERE CPM_ID = '{$nomor}' ";
    //echo $qry1; echo $qry2; exit;

    $res1 = mysqli_query($DBLink, $qry1);
    $res2 = mysqli_query($DBLink, $qry2);
    if (($res1 === false) || ($res2 === false)) {
        echo $qry1 . "<br>";
        echo $qry2 . "<br>";
        echo mysqli_error($DBLink);
    }

    if ($res1 && $res2) {
        echo 'Data berhasil disimpan...!';
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

$save = isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : '';

if ($save == 'Simpan') {
    save(1);
} else if ($save == 'Kirim') {
    save(2);
} else {
    $svcid          = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
    $initData          = getInitData($svcid);
    $initDataRed     = getReduce($svcid);
    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($initData, $initDataRed);
}