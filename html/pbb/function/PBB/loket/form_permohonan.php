<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'loket', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/uuid.php");
require_once($sRootPath."inc/PBB/dbUtils.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";


echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/PBB/loket/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
            
 function getConfigValue ($id,$key) {
    global $DBLink;	
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

function getPropinsi(){    
    global $DBLink;	
    
    $qry = "select * from cppmod_tax_propinsi";
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
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

function getKabkota($idProv=""){    
    global $DBLink;	
    
    $qwhere = "";
    if($idProv){
        $qwhere = " WHERE CPC_TK_PID='$idProv'";
    }
    
    $qry = "select * from cppmod_tax_kabkota ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
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

function getKecamatan($idKec='', $idKab=""){
    global $DBLink;	
    
    $qwhere = "";
    if($idKab){
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
    }else if($idKec){
        $qwhere = " WHERE CPC_TKC_ID='$idKec'";
    }
    
    $qry = "select * from cppmod_tax_kecamatan ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
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

function getKelurahan($idKel='',$idKec=""){    
    global $DBLink;	
    
    $qwhere = "";
    if($idKec){
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
    }else if($idKel){
        $qwhere = " WHERE CPC_TKL_ID='$idKel'";
    }
    
    $qry = "select * from cppmod_tax_kelurahan ".$qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
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

function formPermohonan($initData) {   
    global $a, $m, $appConfig, $arConfig;

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

    $bSlash = "\'";
    $ktip = "'";
	
    $optionProvWP = "";
    $disableComboBerkas = "";
	if($initData['CPM_ID'] != '') {
		$hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
		$disableComboBerkas = "disabled=\"true\"";
		
                if($initData['CPM_TYPE'] =='1'){
                    $kecOP = getKecamatan('',$cityID);

                    $kelOP = getKelurahan('',$initData['CPM_OP_KECAMATAN']);
                }else{
                    $kecOP = getKecamatan($initData['CPM_OP_KECAMATAN']);

                    $kelOP = getKelurahan($initData['CPM_OP_KELURAHAN']);
                }
                    foreach($kecOP as $row){
                        if($initData['CPM_OP_KECAMATAN'] == $row['id'])
                            $optionKecOP .= "<option value=".$row['id']." selected=\"selected\">".$row['name']."</option>";
                        else
                            $optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
                    }
                    foreach($kelOP as $row){
                        if($initData['CPM_OP_KELURAHAN'] == $row['id'])
                            $optionKelOP .= "<option value=".$row['id']." selected=\"selected\">".$row['name']."</option>";
                        else
                            $optionKelOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
                    }
                
	}else{

		$nomor = generateNumber(date('Y'), $initData['CPM_TYPE']);
                
                $kecOP = getKecamatan('',$cityID);
                $kelOP = getKelurahan('',$kecOP[0]['id']);

                foreach($kecOP as $row){
                    $optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
                }
                foreach($kelOP as $row){
                    $optionKelOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
                }
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
        var jnsBerkas = '".$initData['CPM_TYPE']."';
        $(document).ready(function(){
            if(jnsBerkas !== '1'){
                $('#almtOP').attr('readonly','readonly');
                $('#nomorOP').attr('readonly','readonly');
                $('#rtOP').attr('readonly','readonly');
                $('#rwOP').attr('readonly','readonly');

                /*$('#kelurahanOP').attr('disabled','disabled');
                $('#kecamatanOP').attr('disabled','disabled');
                $('#kabupatenOP').attr('disabled','disabled');
                $('#propinsiOP').attr('disabled','disabled');*/

                $('#nmWp').attr('readonly','readonly');
                $('#almtWP').attr('readonly','readonly');
                $('#rtWP').attr('readonly','readonly');
                $('#rwWP').attr('readonly','readonly');

                //$('#hpWP').attr('readonly','readonly');

                $('#kelurahan').attr('readonly','readonly');
                $('#kecamatan').attr('readonly','readonly');
                $('#kabupaten').attr('readonly','readonly');
                $('#propinsi').attr('readonly','readonly');
            }
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
          jenisBerkas[0] = new Array(1,2,3,5,6,14);
          jenisBerkas[1] = new Array(1,2,3,5,6,7,8);
          jenisBerkas[2] = new Array(1,2,3,5,6,7,8);
          jenisBerkas[3] = new Array(1,2,3,5,6,7,8);
          jenisBerkas[4] = new Array(1,3,5,6,8,9);
          jenisBerkas[5] = new Array(1,3,5,6,8,9);
          jenisBerkas[6] = new Array(4,12,13,10);            
          jenisBerkas[7] = new Array(1,2,3,5,6,7,8);         
          jenisBerkas[8] = new Array(91,92,93,94,95,96,97,98,99);
          jenisBerkas[9] = new Array(101,102,103,104,105,106,107);
          jenisBerkas[10] = new Array(101,102,103,104,105,106,107);
          jenisBerkas[11] = new Array(101,102,103,104,105,106,107);
          jenisBerkas[12] = new Array(101,102,103,104,105,106,107);
            
			/*var nomorBerkas = new Array();
			nomorBerkas[0] = '".$nomorPelayananSPOP."';
			nomorBerkas[1] = '".$nomorPelayananSPOP."';
			nomorBerkas[2] = '".$nomorPelayananSPOP."';
			nomorBerkas[3] = '".$nomorPelayananSPOP."';
			nomorBerkas[4] = '".$nomorPelayananSPOP."';
			nomorBerkas[5] = '".$nomorPelayananSPOP."';
			nomorBerkas[6] = '".$nomorPelayananSPOP."';
			nomorBerkas[7] = '".$nomorPelayananSPOP."';
			nomorBerkas[8] = '".$nomorPelayananPKP."';
			nomorBerkas[9] = '".$nomorPelayananPKP."';*/
			
            $('#jnsBerkas').change(function(){
                var berkas = jenisBerkas[$(this).val()-1];
                $('.berkas').hide();
                
				//$('#nomor').val(nomorBerkas[$(this).val()-1]);
                
                for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
            });
			";
			
	if($initData['CPM_TYPE']!='')
		$html .="
				var berkas = jenisBerkas[".$initData['CPM_TYPE']."-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
			";
        
        $params = "a=".$a."&m=".$m;
        $link = "main.php?param=".base64_encode($params."&f=".$arConfig['form_input']);
        
	$html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });
            ";
        if($initData['CPM_TYPE'] =='1'){
            $html .="$(\"#form-penerimaan\").validate({
                rules : {
                    nmKuasa : \"required\",
                    nmWp : \"required\",
                    tglMasuk : \"required\",
                    almtWP : \"required\",
                   
                    tahun : {
                            required : true,
                            number : true
                          },
                    rtWP : {
                            required : true,
                            number : true
                          },
                    rwWP : {
                            required : true,
                            number : true
                          },
					propinsi : {
                            required : true,
                          },
					kabupaten : {
                            required : true,
                          },
					kecamatan : {
                            required : true,
                          },
					kelurahan : {
                            required : true,
                          },
                    hpWP : {
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
                    nmKuasa : \"Wajib diisi\",
                    nmWp : \"Wajib diisi\",
                    tahun : \"Wajib diisi\",
                    tglMasuk : \"Wajib diisi\",
                    almtWP : \"Wajib diisi\",                    
                    rtWP : \"Wajib diisi\",
                    rwWP : \"Wajib diisi\",
                    propinsi : \"Wajib diisi\",
                    kabupaten : \"Wajib diisi\",
                    kecamatan : \"Wajib diisi\",
                    kelurahan : \"Wajib diisi\",
                    hpWP : \"Wajib diisi\",
                    almtOP : \"Wajib diisi\",
                    rtOP : \"Wajib diisi\",
                    rwOP : \"Wajib diisi\"
                }
            });";
        }else {
            $html .="$(\"#form-penerimaan\").validate({
                rules : {
                    nop : {
                            required : true,
                            number : true
                          },
                    nmKuasa : \"required\",
                    tglMasuk : \"required\",
                    tahun : {
                            required : true,
                            number : true
                          }       
                },
                messages : {
                    nmKuasa : \"Wajib diisi\",
                    tahun : \"Wajib diisi\",
                    nop : \"Wajib diisi\",
                    tglMasuk : \"Wajib diisi\"
                }
            });";
        }
        
        $html .="    
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
		
		function getDataOp(){
				var nop = $.trim($('#nop').val());
				var tahun = $.trim($('#tahun').val());
				var jnsBerkas = $('#jnsBerkas').val();
				
				$.ajax({
					type: 'POST',
					data: 'jnsBerkas='+jnsBerkas+'&nop='+nop+'&tahun='+tahun+'&GW_DBHOST=".$appConfig['GW_DBHOST']."&GW_DBNAME=".$appConfig['GW_DBNAME']."&GW_DBUSER=".$appConfig['GW_DBUSER']."&GW_DBPWD=".$appConfig['GW_DBPWD']."',
					url: './function/PBB/loket/dataOP.php',
					success: function(res){
						d=jQuery.parseJSON(res);                                                
						if(d.r == true){							
							$('#almtOP').val(d.dataOP.alamatOP);
							$('#nomorOP').val(d.dataOP.nomorOP);
							$('#rtOP').val(d.dataOP.rtOP);
							$('#rwOP').val(d.dataOP.rwOP);
							$('#kelurahanOP').html('<option value=\"'+d.dataOP.idkelurahanOP+'\">'+d.dataOP.kelurahanOP+'</option>');
							$('#kecamatanOP').html('<option value=\"'+d.dataOP.idkecamatanOP+'\">'+d.dataOP.kecamatanOP+'</option>');
							$('#kabupatenOP').html('<option value=\"'+d.dataOP.idkabupatenOP+'\">'+d.dataOP.kabupatenOP+'</option>');
							$('#propinsiOP').html('<option value=\"'+d.dataOP.idpropinsiOP+'\">'+d.dataOP.propinsiOP+'</option>');
							$('#nmWp').val(d.dataOP.namaWP);
							$('#almtWP').val(d.dataOP.alamatWP);
							$('#rtWP').val(d.dataOP.rtWP);
							$('#rwWP').val(d.dataOP.rwWP);
							$('#hpWP').val(d.dataOP.noHP);
							
							$('#kelurahan').val(d.dataOP.kelurahanWP);
							$('#kecamatan').val(d.dataOP.kecamatanWP);
							$('#kabupaten').val(d.dataOP.kabupatenWP);							
							$('#propinsi').val(d.dataOP.propinsiWP);
                                                        
							$('#jmlTagihan').val(d.dataTagihan.tagihan);
							$('#tglBayar').val(d.dataTagihan.tgl_pembayaran);
                                                        
							$('#btn-simpan').removeAttr('disabled');
							$('#btn-kirim').removeAttr('disabled');

						} else {
							alert(d.errstr);
							$('#almtOP').val('');
							$('#nomorOP').val('');
							$('#rtOP').val('');
							$('#rwOP').val('');
							$('#kelurahanOP').html('<option value=\"\">-</option>');
							$('#kecamatanOP').html('<option value=\"\">-</option>');
							$('#kabupatenOP').html('<option value=\"\">-</option>');
							$('#propinsiOP').html('<option value=\"\">-</option>');
							$('#nmWp').val('');
							$('#almtWP').val('');
							$('#rtWP').val('');
							$('#rwWP').val('');
							$('#hpWP').val('');
							
							$('#kelurahan').val('');
							$('#kecamatan').val('');
							$('#kabupaten').val('');
							$('#propinsi').val('');

							$('#jmlTagihan').val('');
							$('#tglBayar').val('');

							$('#btn-simpan').attr('disabled','disabled');
							$('#btn-kirim').attr('disabled','disabled');
						}  
					}	
				});
		}

                
                function iniAngka(evt,x){
                    var charCode = (evt.which) ? evt.which : event.keyCode;
                    if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13){
                        return true;
                    }else{
                        alert('Input hanya boleh angka!');
                        return false;
                    }
                }
                
		$(document).ready(function(){
				if($(\"#jnsBerkas\").val()=='1'){
					document.getElementById(\"tnop\").style.display = 'none';
				}
				else{
					document.getElementById(\"tnop\").style.display = '';
				}
				$(\"#jnsBerkas\").change(function(){
					window.location.href = '".$link."&jnsBerkas='+$(this).val();
                                        /*if($(this).val()=='1'){
						document.getElementById(\"tnop\").style.display = 'none';
                                                window.location.href = '".$linkopbaru."';
					}
					else{
						document.getElementById(\"tnop\").style.display = '';
                                                window.location.href = '".$link."&jnsBerkas='+$(this).val();
					}*/
				});
				
		});

    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	$hiddenModeInput
	<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                            <tr>
                              <td colspan=\"2\"><strong><font size=\"+2\">Form Permohonan</font></strong><br /><hr><br /></td>
                            </tr>
                            <tr>
                              <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">&nbsp;</font></strong></td>
                              <td width=\"97%\"><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td width=\"39%\">Nomor</td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nomor\" id=\"nomor\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_ID']!='')? $initData['CPM_ID']:$nomor)."\" readonly=\"readonly\" placeholder=\"Nomor\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\"><label for=\"jnsBerkas\">Jenis Berkas</label></td>
                                      <td width=\"60%\">
                                        <select name=\"jnsBerkas\" id=\"jnsBerkas\" ".$disableComboBerkas.">
                                            <option value=\"1\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==1)? 'selected="selected"':'').">OP Baru</option>
                                            <option value=\"2\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==2)? 'selected="selected"':'').">Pemecahan</option>
                                            <option value=\"3\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==3)? 'selected="selected"':'').">Penggabungan</option>
                                            <option value=\"4\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==4)? 'selected="selected"':'').">Mutasi</option>
                                            <option value=\"5\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==5)? 'selected="selected"':'').">Perubahan Data</option>
                                            <!--<option value=\"6\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==6)? 'selected="selected"':'').">Pembatalan</option>-->
                                            <option value=\"7\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==7)? 'selected="selected"':'').">Salinan</option>
                                            <option value=\"8\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==8)? 'selected="selected"':'').">Penghapusan</option>
                                            <option value=\"9\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==9)? 'selected="selected"':'').">Pengurangan</option>
                                            <option value=\"10\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==10)? 'selected="selected"':'').">Keberatan</option>
                                        </select>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\">Tahun Pajak</td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" maxlength=\"4\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$appConfig['tahun_tagihan'])."\" placeholder=\"Tahun\" />
                                      </td>
                                    </tr>
                                    <tr><td colspan=\"2\"><h3>A. DATA OBJEK PAJAK</h3></td></tr>
                                    <tbody id='tnop' style=\"display:none;\">
                                    <tr>
                                      <td width=\"39%\"><label for=\"nop\">NOP</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" maxlength=\"22\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_OP_NUMBER']!='')? $initData['CPM_OP_NUMBER']:'')."\" placeholder=\"NOP\" />
                                        <input type=\"button\" name=\"btn-cek-NOP\" id=\"btn-cek\" onclick=\"getDataOp()\" value=\"Cek\" />
                                      </td>
                                    </tr>
                                    </tbody>
                                    <tr>
                                      <td width=\"39%\"><label for=\"almtOP\">Alamat Objek Pajak</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"almtOP\" id=\"almtOP\" size=\"50\" maxlength=\"70\" value=\"".(($initData['CPM_OP_ADDRESS']!='')? str_replace($bSlash,$ktip,$initData['CPM_OP_ADDRESS']):'')."\" placeholder=\"Alamat\" />
                                      </td>
                                    </tr>
                                    <tr style='display:none'>
                                      <td width=\"39%\"><label for=\"nomorOP\">Blok/Kav/Nomor</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nomorOP\" id=\"nomorOP\" size=\"10\" maxlength=\"10\"  placeholder=\"Nomor\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"rtOP\">RT/RW</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"rtOP\" id=\"rtOP\" size=\"3\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_OP_RT']!='')? $initData['CPM_OP_RT']:'')."\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwOP\" id=\"rwOP\" size=\"3\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_OP_RW']!='')? $initData['CPM_OP_RW']:'')."\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
                                      <td width=\"60%\">
                                        <select name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                                      <td width=\"60%\">
                                        <select name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kecamatanOP\">Kecamatan</label></td>
                                      <td width=\"60%\">
                                        <select name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                                      <td width=\"60%\">
                                        <select name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
                                      </td>
                                    </tr>
									<tr><td colspan=\"2\"><h3>B. DATA WAJIB PAJAK</h3></td></tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_REPRESENTATIVE']!='')? str_replace($bSlash,$ktip,$initData['CPM_REPRESENTATIVE']):'')."\" placeholder=\"Nama Kuasa\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nmWp\" id=\"nmWp\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_WP_NAME']!='')? str_replace($bSlash,$ktip,$initData['CPM_WP_NAME']):'')."\" placeholder=\"Nama Wajib Pajak\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"".(($initData['CPM_DATE_RECEIVE']!='')? $initData['CPM_DATE_RECEIVE']:$today)."\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"almtWP\">Alamat WP</label></td>
                                      <td width=\"60%\">
										<input type=\"text\" name=\"almtWP\" id=\"almtWP\" size=\"50\" maxlength=\"70\" value=\"".(($initData['CPM_WP_ADDRESS']!='')? str_replace($bSlash,$ktip,$initData['CPM_WP_ADDRESS']):'')."\" placeholder=\"Alamat\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"rtWP\">RT/RW</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"rtWP\" id=\"rtWP\" size=\"3\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_WP_RT']!='')? $initData['CPM_WP_RT']:'')."\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwWP\" id=\"rwWP\" size=\"3\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_WP_RW']!='')? $initData['CPM_WP_RW']:'')."\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"propinsi\">Provinsi</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"propinsi\" id=\"propinsi\" size=\"25\"  maxlength=\"25\" value=\"".(($initData['CPM_WP_PROVINCE']!='')? $initData['CPM_WP_PROVINCE'] :'')."\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"kabupaten\" id=\"kabupaten\" size=\"25\" maxlength=\"25\" value=\"".(($initData['CPM_WP_KABUPATEN']!='')? $initData['CPM_WP_KABUPATEN']:'')."\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kecamatan\">Kecamatan</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"kecamatan\" id=\"kecamatan\" size=\"25\" maxlength=\"25\" value=\"".(($initData['CPM_WP_KECAMATAN']!='')? $initData['CPM_WP_KECAMATAN']:'')."\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"kelurahan\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" size=\"25\" maxlength=\"25\" value=\"".(($initData['CPM_WP_KELURAHAN']!='')? $initData['CPM_WP_KELURAHAN']:'')."\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\">No. HP WP</td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"hpWP\" id=\"hpWP\" size=\"15\" maxlength=\"15\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_WP_HANDPHONE']!='')? $initData['CPM_WP_HANDPHONE']:'')."\" placeholder=\"Nomor HP\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\">Lampiran :</td>
                                      <td width=\"60%\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"100%\" colspan=\"2\" valign=\"top\">
                                          <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                              <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 1)) ? "checked=\"checked\"":"")."> Surat Permohonan.</li>
                                              <li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 2)) ? "checked=\"checked\"":"")."> Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP.</li>
                                              <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 4)) ? "checked=\"checked\"":"")."> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas4\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 8)) ? "checked=\"checked\"":"")."> Fotocopi KTP / Kartu Keluarga.</li>
                                              <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 16)) ? "checked=\"checked\"":"")."> Fotocopi Bukti Kepemilikan Tanah.</li>
                                              <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 32)) ? "checked=\"checked\"":"")."> Fotocopi IMB.</li>
                                              <li id=\"berkas7\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 64)) ? "checked=\"checked\"":"")."> Fotocopi Bukti Pelunasan PBB Tahun Sebelumnya.</li>
                                              <li id=\"berkas8\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 128)) ? "checked=\"checked\"":"")."> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                                              <li id=\"berkas9\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 256)) ? "checked=\"checked\"":"")."> Surat Ketetapan Pajak Daerah (SKPD).</li>
                                              <li id=\"berkas10\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"512\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 512)) ? "checked=\"checked\"":"")."> Surat Setoran Pajak Daerah (SSPD).</li>
                                              <li id=\"berkas11\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"1024\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 1024)) ? "checked=\"checked\"":"")."> Surat Kuasa (bila dikuasakan).</li>
                                              <li id=\"berkas12\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"2048\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 2048)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB / SKP tahun lalu.</li>
                                              <li id=\"berkas13\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"4096\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 4096)) ? "checked=\"checked\"":"")."> Fotocopi bukti pembayaran PBB yang terakhir.</li>
                                              <li id=\"berkas14\" class=\"berkas\" ><input type=\"checkbox\" name=\"lampiran[]\" value=\"8192\" class=\"attach\" ".((($initData['CPM_TYPE']!=9 && $initData['CPM_TYPE']!=10) && ($initData['CPM_ATTACHMENT']& 8192)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB tetangga terdekat.</li>
											  <!--<li id=\"berkas14\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"8192\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 8192) ? "checked=\"checked\"":"")."> Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas15\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"16384\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 16384) ? "checked=\"checked\"":"")."> Fotocopi Sertifikat Objek PBB yang diajukan permohonan pengurangan / Bukti kepemilikan tanah lainnya / dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas16\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"32768\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 32768) ? "checked=\"checked\"":"")."> Fotocopi Sertifikat Objek PBB yang diajukan keberatan / Bukti kepemilikan tanah lainnya / dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas17\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"65536\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 65536) ? "checked=\"checked\"":"")."> Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</li>
                                              <li id=\"berkas18\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"131072\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 131072) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</li>
                                              <li id=\"berkas19\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"262144\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 262144) ? "checked=\"checked\"":"")."> Surat Pernyataan Besarnya Penghasilan.</li>
                                              <li id=\"berkas20\" class=\"berkas\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"524288\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 524288) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB tetangga terdekat.</li>
                                              <li id=\"berkas21\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"1048576\" class=\"attach\" ".(($initData['CPM_ATTACHMENT']& 1048576) ? "checked=\"checked\"":"")."> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>-->
											  
											  <li id=\"berkas91\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 1)) ? "checked=\"checked\"":"")."> Surat Permohonan</li>
                                              <li id=\"berkas92\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 2)) ? "checked=\"checked\"":"")."> Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas93\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 4)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</li>
											  <li id=\"berkas94\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 8)) ? "checked=\"checked\"":"")."> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
											  <li id=\"berkas95\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 16)) ? "checked=\"checked\"":"")."> Surat Keterangan : Tidak Mampu / Tidak Bekerja / Tidak Ada Penghasilan / Lainnya / Dokumen lain yang dipersamakan dan telah ditandatangani oleh Pejabat Berwenang.</li>
                                              <li id=\"berkas96\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 32)) ? "checked=\"checked\"":"")."> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
											  <li id=\"berkas97\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 64)) ? "checked=\"checked\"":"")."> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Pengurangan.</li>
                                              <li id=\"berkas98\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 128)) ? "checked=\"checked\"":"")."> Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</li>
                                              <li id=\"berkas99\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 256)) ? "checked=\"checked\"":"")."> Fotocopi Izin Mendirikan Bangunan (IMB), khusus bangunan yang bersifat komersil.</li>
											  
											  <li id=\"berkas101\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 1)) ? "checked=\"checked\"":"")."> Surat Permohonan</li>
                                              <li id=\"berkas102\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 2)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB yang akan diajukan Permohonan Keberatan.</li>
                                              <li id=\"berkas103\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 4)) ? "checked=\"checked\"":"")."> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
											  <li id=\"berkas104\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 8)) ? "checked=\"checked\"":"")."> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
											  <li id=\"berkas105\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 16)) ? "checked=\"checked\"":"")."> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Keberatan.</li>
                                              <li id=\"berkas106\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 32)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB tetangga terdekat.</li>
                                              <li id=\"berkas107\" class=\"berkas\" style=\"display: none;\"><input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" ".((($initData['CPM_TYPE']==10) && ($initData['CPM_ATTACHMENT']& 64)) ? "checked=\"checked\"":"")."> Fotocopi Izin Mendirikan Bangunan (IMB), apabila objek yang diajukan keberatan memiliki bangunan.<br/>(Khusus bangunan yang bersifat komersil)</li>
                                          </ol>
                                      </td>
                                    </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td colspan=\"2\">&nbsp;</td>
                            </tr>                        
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">";
                              if($initData['CPM_STATUS'] > 0){
                                  $html .="<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Kembali\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."")."\"' />";
                              }else{
                                  $html .="
								  <input type=\"hidden\" name=\"jmlTagihan\" id=\"jmlTagihan\" value=\"".(($initData['CPM_SPPT_DUE']!='')? $initData['CPM_SPPT_DUE']:0)."\">
                                  <input type=\"hidden\" name=\"tglBayar\" id=\"tglBayar\" value=\"".(($initData['CPM_SPPT_PAYMENT_DATE']!='')? $initData['CPM_SPPT_PAYMENT_DATE']:'')."\">
                                   <hr><br><input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" />
                                &nbsp;
                                  <input type=\"submit\" name=\"btn-save\" id=\"btn-kirim\" value=\"Kirim\" />
                                  &nbsp;
                                    <input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."")."\"' />";
                              }
                                  
                    $html .= "</td>
                            </tr>
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
                            </tr>
                      </table>
                    </form></div>";
    return $html;
}

function getInitData($id=""){    
    global $DBLink;	
    
    if($id == '') return getDataDefault();
	
    $qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
		return getDataDefault();
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'],8,2).'-'.substr($row['CPM_DATE_RECEIVE'],5,2).'-'.substr($row['CPM_DATE_RECEIVE'],0,4);
			return $row;
        }                
    }
}

function getDataDefault(){
	$jnsBerkas = isset($_REQUEST['jnsBerkas'])? $_REQUEST['jnsBerkas']:1;
        $default = array('CPM_ID' => '', 'CPM_TYPE' => $jnsBerkas, 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '', 
	'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '', 
	'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '', 'CPM_STATUS' => 0);
        return $default;        
}

function getLastNumber($suffix){
    global $DBLink;	
    
	$qry = "select max(CPM_NO) as CPM_NO from cppmod_pbb_generate_service_number where CPM_ID like '%{$suffix}'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CPM_NO'];
        }
		
		return "0";
    }
}

/* function generateNumber($year,$mon){
	$lastNumber = getLastNumber($year,$mon);
	$newNumber = $lastNumber+1;
	return "SPOP/".$year."/".$mon."/".substr('000'.$newNumber, -3);
} */

function generateNumber($year,$jnsBerkas){
        global $arConfig;
	$numberSuffix = array();
	$numberSuffix[1] = $arConfig['FORMAT_NOMOR_BERKAS_OPBARU'];
	$numberSuffix[2] = $arConfig['FORMAT_NOMOR_BERKAS_PEMECAHAN'];
	$numberSuffix[3] = $arConfig['FORMAT_NOMOR_BERKAS_PENGGABUNGAN'];
	$numberSuffix[4] = $arConfig['FORMAT_NOMOR_BERKAS_MUTASI'];
	$numberSuffix[5] = $arConfig['FORMAT_NOMOR_BERKAS_PERUBAHAN'];
	$numberSuffix[6] = $arConfig['FORMAT_NOMOR_BERKAS_PEMBATALAN'];
	$numberSuffix[7] = $arConfig['FORMAT_NOMOR_BERKAS_SALINAN'];
	$numberSuffix[8] = $arConfig['FORMAT_NOMOR_BERKAS_PENGHAPUSAN'];
	$numberSuffix[9] = $arConfig['FORMAT_NOMOR_BERKAS_PENGURANGAN'];
	$numberSuffix[10] = $arConfig['FORMAT_NOMOR_BERKAS_KEBERATAN'];
	$lastNumber = getLastNumber($numberSuffix[$jnsBerkas]);
	$newNumber = $lastNumber+1;
	return $newNumber.$numberSuffix[$jnsBerkas];
}

function save($status){
    global $data, $DBLink, $uname;
    
    $mode  = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
	if($mode == 'edit')
		$nomor = $_REQUEST['nomor'];
    else $nomor = generateNumber($_REQUEST['tahun'],$_REQUEST['jnsBerkas']);
	
    $nmKuasa = mysql_real_escape_string($_REQUEST['nmKuasa']);
    $nmWp = mysql_real_escape_string($_REQUEST['nmWp']);
    $tglMasuk = substr($_REQUEST['tglMasuk'],6,4).'-'.substr($_REQUEST['tglMasuk'],3,2).'-'.substr($_REQUEST['tglMasuk'],0,2);
    $almtWP = mysql_real_escape_string($_REQUEST['almtWP']);
    $rtWP = mysql_real_escape_string($_REQUEST['rtWP']);
    $rwWP = mysql_real_escape_string($_REQUEST['rwWP']);
    $propinsiWP = mysql_real_escape_string($_REQUEST['propinsi']);
    $kabupatenWP = mysql_real_escape_string($_REQUEST['kabupaten']);
    $kecamatanWP = mysql_real_escape_string($_REQUEST['kecamatan']);
    $kelurahanWP = mysql_real_escape_string($_REQUEST['kelurahan']);
    $hpWP = mysql_real_escape_string($_REQUEST['hpWP']);
    $jnsBerkas = $_REQUEST['jnsBerkas'];
    $nop = $_REQUEST['nop'];
    $almtOP = mysql_real_escape_string($_REQUEST['almtOP']);
    $nomorOP = $_REQUEST['nomorOP'];
    $rtOP = mysql_real_escape_string($_REQUEST['rtOP']);
    $rwOP = mysql_real_escape_string($_REQUEST['rwOP']);
    $kecamatanOP = $_REQUEST['kecamatanOP'];
    $kelurahanOP = $_REQUEST['kelurahanOP'];
    $attachment = $_REQUEST['attachment'];
	
    $tahun = $_REQUEST['tahun'];
	if ($_REQUEST['jmlTagihan']==''){
		$jmlTagihan = 0;
	} else {
		$jmlTagihan = $_REQUEST['jmlTagihan'];
	}
    $tglBayar = $_REQUEST['tglBayar'];
	
	
	if($mode == 'edit'){
		$qry = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', 
		CPM_WP_KELURAHAN='{$kelurahanWP}', CPM_WP_KECAMATAN='{$kecamatanWP}',	CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', 
		CPM_OP_ADDRESS='{$almtOP}', CPM_OP_ADDRESS_NO='{$nomorOP}', CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
		CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, CPM_SPPT_DUE={$jmlTagihan}, CPM_SPPT_YEAR='{$tahun}', CPM_SPPT_PAYMENT_DATE='{$tglBayar}'  WHERE CPM_ID = '{$nomor}' ";
		
		$res = mysqli_query($DBLink, $qry);
	    if ( $res === false ){
	            echo $qry ."<br>";
	            echo mysqli_error($DBLink);
	    }
		
	    if($res){
	        echo 'Data berhasil disimpan...!';
	        $params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
	        echo "<script language='javascript'>
	                $(document).ready(function(){
	                    window.location = \"./main.php?param=".base64_encode($params)."\"
	                })
	              </script>";
	    }
	    else{
	        echo mysqli_error($DBLink);
	    }
	}else{
		$tmp = explode('/',$nomor);
		$qry = "INSERT INTO cppmod_pbb_generate_service_number (CPM_ID, CPM_NO, CPM_CREATOR, CPM_DATE_CREATED) VALUES ('{$nomor}', '".$tmp[0]."','{$uname}', '{$tglMasuk}')";
		
		$res = mysqli_query($DBLink, $qry);
	    if ( $res === false ){
	            echo $qry ."<br>";
	            echo mysqli_error($DBLink);
	    }
		
	    if($res){
	        $qry = "INSERT INTO cppmod_pbb_services (CPM_ID, CPM_TYPE, CPM_REPRESENTATIVE, CPM_WP_NAME, CPM_WP_ADDRESS, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN,
			CPM_WP_KABUPATEN, CPM_WP_PROVINCE, CPM_WP_HANDPHONE, CPM_OP_NUMBER, CPM_OP_ADDRESS, CPM_OP_ADDRESS_NO, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_ATTACHMENT, CPM_RECEIVER, 
			CPM_DATE_RECEIVE, CPM_STATUS, CPM_SPPT_DUE, CPM_SPPT_YEAR, CPM_SPPT_PAYMENT_DATE) VALUES ('{$nomor}', {$jnsBerkas}, '{$nmKuasa}', '{$nmWp}', '{$almtWP}', '{$rtWP}', '{$rwWP}', '{$kelurahanWP}', '{$kecamatanWP}', '{$kabupatenWP}', '{$propinsiWP}', 
			'{$hpWP}', '{$nop}', '{$almtOP}', '{$nomorOP}', '{$rtOP}', '{$rwOP}', '{$kelurahanOP}', '{$kecamatanOP}', {$attachment}, '{$uname}', '{$tglMasuk}', '{$status}', '{$jmlTagihan}', '{$tahun}', '{$tglBayar}')";
		
			$res2 = mysqli_query($DBLink, $qry);
		    if ( $res2 === false ){
		            echo $qry ."<br>";
		            echo mysqli_error($DBLink);
		    }
			
		    if($res2){
				echo 'Data berhasil disimpan...!';
		        $params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
		        echo "<script language='javascript'>
		                $(document).ready(function(){
		                    window.location = \"./main.php?param=".base64_encode($params)."\"
		                })
		              </script>";
			}
		    else{
		        echo mysqli_error($DBLink);
		    }
	    }
	    else{
	        echo mysqli_error($DBLink);
	    }
	}
	
}

$appConfig = $User->GetAppConfig($application);	
$arConfig = $User->GetModuleConfig($m);

$save = isset($_REQUEST['btn-save'])?$_REQUEST['btn-save']:'';

if($save == 'Simpan') {
    save(0);
} else if ($save == 'Kirim') {
    if($_REQUEST['jnsBerkas'] == '7') save(3); 
    else save(1);    
} else {
    $svcid  = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
	$initData = getInitData($svcid);
        
	echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
	echo formPermohonan($initData);	
}

?>

