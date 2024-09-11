<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'loket', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/PBB/loket/jquery.validate.min.js\"></script>\n";
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

function getKecamatan($idKec = '', $idKab = "")
{
  global $DBLink;

  $qwhere = "";
  if ($idKab) {
    $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
  } else if ($idKec) {
    $qwhere = " WHERE CPC_TKC_ID='$idKec'";
  }

  $qry = "select * from cppmod_tax_kecamatan " . $qwhere . " ORDER BY CPC_TKC_ID";
  $res = mysqli_query($DBLink, $qry);
  if (!$res) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  } else {
    $data = array();
    while ($row = mysqli_fetch_assoc($res)) {
      $digit3 = substr($row['CPC_TKC_ID'], 4, 3) . " - ";

      $tmp = array(
        'id' => $row['CPC_TKC_ID'],
        'pid' => $row['CPC_TKC_KKID'],
        'name' => $digit3 . $row['CPC_TKC_KECAMATAN']

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

  $qry = "select * from cppmod_tax_kelurahan " . $qwhere . " ORDER BY CPC_TKL_ID";
  $res = mysqli_query($DBLink, $qry);
  if (!$res) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  } else {
    $data = array();
    while ($row = mysqli_fetch_assoc($res)) {
      $digit3 = substr($row['CPC_TKL_ID'], 7, 3) . " - ";

      $tmp = array(
        'id' => $row['CPC_TKL_ID'],
        'pid' => $row['CPC_TKL_KCID'],
        'name' => $digit3 . $row['CPC_TKL_KELURAHAN']
      );
      $data[] = $tmp;
    }
    return $data;
  }
}

function formPenerimaan($initData)
{
  global $a, $m, $appConfig, $arConfig, $uname;

  $today = date("d-m-Y");

  $cityID = $appConfig['KODE_KOTA'];
  $cityName = $appConfig['NAMA_KOTA'];
  $optionCityOP = "<option value=$cityID>$cityName</option>";

  $provID = $appConfig['KODE_PROVINSI'];
  $provName = $appConfig['NAMA_PROVINSI'];
  $optionProvOP = "<option value=$provID>$provName</option>";

  $hiddenIdInput = $nomor = '';
  $kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
  $optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

  $bSlash = "\'";
  $ktip = "'";

  $hiddenModeInput = null;

  $optionProvWP = "";
  $disableComboBerkas = "";
  if ($initData['CPM_ID'] != '') {
    $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
    $disableComboBerkas = "disabled=\"true\"";

    if ($initData['CPM_TYPE'] == '1' || $initData['CPM_TYPE'] == '12') {
      $kecOP = getKecamatan('', $cityID);

      $kelOP = getKelurahan('', $initData['CPM_OP_KECAMATAN']);
    } else {
      $kecOP = getKecamatan($initData['CPM_OP_KECAMATAN']);

      $kelOP = getKelurahan($initData['CPM_OP_KELURAHAN']);
    }
    foreach ($kecOP as $row) {
      if ($initData['CPM_OP_KECAMATAN'] == $row['id']) {
        $optionKecOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
      } else {
        $optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
      }
    }
    foreach ($kelOP as $row) {
      if ($initData['CPM_OP_KELURAHAN'] == $row['id']) {
        $optionKelOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
      } else {
        $optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
      }
    }
  } else {

    $nomor = generateNumber(date('Y'), $initData['CPM_TYPE']);

    $kecOP = getKecamatan('', $cityID);
    $kelOP = getKelurahan('', $kecOP[0]['id']);

    foreach ($kecOP as $row) {
      $optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
    }
    foreach ($kelOP as $row) {
      $optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
    }
  }

  // sebagai Tanda Development
  // $dev = false;
  // $arrstringbrowser = explode(' ', $_SERVER['HTTP_USER_AGENT']);
  // if(in_array("Firefox/114.0", $arrstringbrowser)) $dev = true;

  // var_dump($nomor);exit;
  $html = "
    <style>
    #main-content {
        width: 100%;
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
        var jnsBerkas = '" . $initData['CPM_TYPE'] . "';
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
                $('#noKtpWp').attr('readonly','readonly');
                $('#almtWP').attr('readonly','readonly');
                $('#rtWP').attr('readonly','readonly');
                $('#rwWP').attr('readonly','readonly');
                $('#hpWP').attr('readonly','readonly');

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
				jenisBerkas[0] = new Array(1,2,3,5,6,14,110);
				jenisBerkas[1] = new Array(1,2,3,5,6,7,8,108,109,110);
				jenisBerkas[2] = new Array(1,2,3,5,6,7,8,110);
				jenisBerkas[3] = new Array(1,2,3,5,6,7,8,110);
				jenisBerkas[4] = new Array(1,3,5,6,8,9,110);
				jenisBerkas[5] = new Array(1,3,5,6,8,9,110);
				jenisBerkas[6] = new Array(4,12,13,10,110);            
				jenisBerkas[7] = new Array(1,2,3,5,6,7,8,110);         
				jenisBerkas[8] = new Array(91,92,93,94,95,96,97,98,99,110);
				jenisBerkas[9] = new Array(101,102,103,104,105,106,107,110);
				jenisBerkas[10] = new Array(1,3,5,6,8,9,110);
        jenisBerkas[11] = new Array(91,92,93,94,95,96,97,98,99,110);
        jenisBerkas[12] = new Array(101,102,103,104,105,106,107,110); ";

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

  $html .= "$('#jnsBerkas').change(function(){
                var berkas = jenisBerkas[$(this).val()-1];
                $('.berkas').hide();
                
				//$('#nomor').val(nomorBerkas[$(this).val()-1]);
                
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

  $params = "a=" . $a . "&m=" . $m;
  $link = "main.php?param=" . base64_encode($params . "&f=" . $arConfig['form_input']);

  $html .= "//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });            
            ";
  if ($initData['CPM_TYPE'] == '1' || $initData['CPM_TYPE'] == '12') {
    $html .= "$(\"#form-penerimaan\").validate({
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
            });
            
            $(\"#modalDialog\").dialog({
				autoOpen: false,
				modal: true,
				width: 900,
				resizable: false,
				draggable: false,
				height: 'auto',
				title: '',
				position: ['top', 50]
			});";
  } else {
    $html .= "
            $(\"#modalDialog\").dialog({
                autoOpen: false,
                modal: true,
                width: 900,
                resizable: false,
                draggable: false,
                height: 'auto',
                title: '',
                position: ['top', 50]
            });
                
                $(\"#form-penerimaan\").validate({
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
                          },
                    tahunBerlaku : {
                            required : true,
                            number : true
                          }       
                },
                messages : {
                    nmKuasa : \"Wajib diisi\",
                    tahun : \"Wajib diisi\",
                    tahunBerlaku : \"Wajib diisi\",
                    nop : \"Wajib diisi\",
                    tglMasuk : \"Wajib diisi\"
                }
            });";
  }

  $html .= "    
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
        if(nop=='') return false;
				var tahun = $.trim($('#tahun').val());
        var jnsBerkas = $('#jnsBerkas').val();
				
				$.ajax({
					type: 'POST',
					data: 'jnsBerkas='+jnsBerkas+'&nop='+nop+'&tahun='+tahun+'&GW_DBHOST=" . $appConfig['GW_DBHOST'] . "&GW_DBNAME=" . $appConfig['GW_DBNAME'] . "&GW_DBUSER=" . $appConfig['GW_DBUSER'] . "&GW_DBPWD=" . $appConfig['GW_DBPWD'] . "',
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
							$('#noKtpWp').val(d.dataOP.noKtpWP);
							$('#nmWp').val(d.dataOP.namaWP);
							$('#almtWP').val(d.dataOP.alamatWP);
							$('#rtWP').val(d.dataOP.rtWP);
							$('#rwWP').val(d.dataOP.rwWP);
							$('#hpWP').val(d.dataOP.noHP);
							
							$('#kelurahan').val(d.dataOP.kelurahanWP);
							$('#kecamatan').val(d.dataOP.kecamatanWP);
							$('#kabupaten').val(d.dataOP.kabupatenWP);							
							$('#propinsi').val(d.dataOP.propinsiWP);
							console.log(jnsBerkas)
							if(jnsBerkas == '5') $('#tahun').val(d.dataOP.tahunPenetapan);
                                                        
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
							if(jnsBerkas == '5') $('#tahun').val('');

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
		
		function displayInputWp(id){
			$(\"#modalDialog\").dialog('open');
			$(\"#modalDialog\").load(\"function/PBB/nop/wp/form-edit-dialog.php?id=\"+id+\"&a={$a}\");
		}
		
		function cekWP(evt, x) {
			if($.trim(x.value) != '') {
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
							document.getElementById(\"nmWp\").value = json.CPM_WP_NAMA;
							document.getElementById(\"almtWP\").value = json.CPM_WP_ALAMAT;
							document.getElementById(\"rtWP\").value = json.CPM_WP_RT;
							document.getElementById(\"rwWP\").value = json.CPM_WP_RW;
							document.getElementById(\"propinsi\").value = json.CPM_WP_PROPINSI;
							document.getElementById(\"kabupaten\").value = json.CPM_WP_KOTAKAB;
							document.getElementById(\"kecamatan\").value = json.CPM_WP_KECAMATAN;
							document.getElementById(\"kelurahan\").value = json.CPM_WP_KELURAHAN;
							document.getElementById(\"hpWP\").value = json.CPM_WP_NO_HP;
							alert('Wajib Pajak : '+json.CPM_WP_NAMA +'\\n\\nYakin ini ?');
							$(\"#div-tmbahwp\").html(\"<a href=javascript:displayInputWp('\"+noktp+\"')>Edit WP?</a>\");
						} else {
							alert('NO KTP Tidak Ditemukan');                            
							document.getElementById(\"nmWp\").value = '';
							document.getElementById(\"almtWP\").value = '';
							document.getElementById(\"rtWP\").value = '';
							document.getElementById(\"rwWP\").value = '';
							document.getElementById(\"propinsi\").value = '';
							document.getElementById(\"kabupaten\").value = '';
							document.getElementById(\"kecamatan\").value = '';
							document.getElementById(\"kelurahan\").value = '';
							document.getElementById(\"hpWP\").value = '';
							$(\"#div-tmbahwp\").html(\"<a href=javascript:displayInputWp('\"+noktp+\"')>No KTP tidak ditemukan, Input WP Baru?</a>\");
						}
					},
					failure: function(res) {
						document.getElementById(\"div-loadwp-wait\").innerHTML = \"\";
						alert('Pengecekan No KTP Gagal!');
					}
				});
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
					window.location.href = '" . $link . "&jnsBerkas='+$(this).val();";
  /*if($(this).val()=='1'){
						document.getElementById(\"tnop\").style.display = 'none';
                                                window.location.href = '".$linkopbaru."';
					}
					else{
						document.getElementById(\"tnop\").style.display = '';
                                                window.location.href = '".$link."&jnsBerkas='+$(this).val();
					}*/
  $html .= "});
				
		});

    </script>
    <div class=\"col-md-12\">
      <div id=\"main-content\">
      <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">" .
    $hiddenModeInput . "
	      <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
        <div class=\"col-md-1\"></div>
        <div class=\"col-md-11\" style=\"max-width:900px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f\">
            <div style=\"text-align:center\"><font size=\"+2\"><b>Penerimaan Berkas Pelayanan PBB-P2</b></font></div><br /><hr>
            <div class=\"row\">
              <div class=\"col-md-6\">
                <div class=\"form-group\">
                  <label>Nomor</label>
                  <input type=\"text\" name=\"nomor\" id=\"nomor\" class=\"form-control\" maxlength=\"40\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                </div>
              </div>
            </div>
            <div class=row>
              <div class=\"col-md-4\">
                <div class=\"form-group\">
                  <label for=jnsBerkas>Jenis Berkas</label>
                  <select name=jnsBerkas class=\"form-control\" id=\"jnsBerkas\" " . $disableComboBerkas . ">
                    <option value=1 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 1) ? 'selected="selected"' : '') . ">01 OP Baru</option>
                    <option value=2 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 2) ? 'selected="selected"' : '') . ">02 Pemecahan</option>
                    <option value=3 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 3) ? 'selected="selected"' : '') . ">03 Penggabungan</option>
                    <option value=4 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 4) ? 'selected="selected"' : '') . ">04 Mutasi</option>
                    <option value=5 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 5) ? 'selected="selected"' : '') . ">05 Perubahan Data</option>
                    <!--<option value=6 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 6) ? 'selected="selected"' : '') . ">06 Pembatalan</option>-->
                    <option value=7 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 7) ? 'selected="selected"' : '') . ">07 Salinan</option>
                    <option value=8 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 8) ? 'selected="selected"' : '') . ">08 Penghapusan</option>
                    <option value=9 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 9) ? 'selected="selected"' : '') . ">09 Pengurangan SPPT</option>
                    <option value=10 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 10) ? 'selected="selected"' : '') . ">10 Keberatan</option>
                    <option value=11 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 11) ? 'selected="selected"' : '') . ">11 Cetak SKNJOP</option>
                    <option value=12 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 12) ? 'selected="selected"' : '') . ">12 Pengurangan Denda</option>
                    <option value=13 " . (($initData['CPM_TYPE'] != '' && $initData['CPM_TYPE'] == 13) ? 'selected="selected"' : '') . ">13 Restitusi/Kompensasi</option>
                  </select>
                </div>
              </div>";
  // NEW BY TRY SETYO
  if (in_array($initData['CPM_TYPE'], array(2, 5, 4))) {
    $isreadonly = "readonly";
  } else {
    $isreadonly = " ";
  }
  // END NEW BY TRY SETYO


  if($initData['CPM_TYPE'] == 1 || $initData['CPM_TYPE'] == 2 || $initData['CPM_TYPE'] == 3 || $initData['CPM_TYPE'] == 4) {
    $tahunselect = (isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan'];
    $html .= "<div class=\"col-md-2\">
                <div class=\"form-group\">
                  <label for=\"\">Tahun Pajak</label>
                  <input type=\"text\" class=\"form-control\" name=tahun id=tahun readonly maxlength=4 value=$tahunselect />
                </div>
              </div>";
  }elseif($initData['CPM_TYPE'] == 5) {
    $tahunselect = (isset($initData['CPM_SPPT_YEAR_BERLAKU']) && $initData['CPM_SPPT_YEAR_BERLAKU'] != '') ? $initData['CPM_SPPT_YEAR_BERLAKU'] : $appConfig['tahun_tagihan'];
    $html .= "<div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"\">Tahun Berlaku Perubahan</label>
                  <input type=\"text\" class=\"form-control\" name=tahunBerlaku id=tahunBerlaku $isreadonly maxlength=4 value=$tahunselect />
                </div>
              </div>";
  }elseif($initData['CPM_TYPE'] == 12) {
    $tahunselect = (isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : ($appConfig['tahun_tagihan']-1);
    $html .= "<div class=\"col-md-2\">
                <div class=\"form-group\">
                  <label for=\"\">Tahun Pajak</label>
                  <select class=\"form-control\" name=tahun id=tahun $isreadonly>
                      <option value=2015".(($tahunselect==2015)?' selected':'').">2015</option>
                      <option value=2016".(($tahunselect==2016)?' selected':'').">2016</option>
                      <option value=2017".(($tahunselect==2017)?' selected':'').">2017</option>
                      <option value=2018".(($tahunselect==2018)?' selected':'').">2018</option>
                      <option value=2019".(($tahunselect==2019)?' selected':'').">2019</option>
                      <option value=2020".(($tahunselect==2020)?' selected':'').">2020</option>
                      <option value=2021".(($tahunselect==2021)?' selected':'').">2021</option>
                      <option value=2022".(($tahunselect==2022)?' selected':'').">2022</option>
                      <option value=2023".(($tahunselect==2023)?' selected':'').">2023</option>
                  </select>
                </div>
              </div>";
  }else{
    $tahunselect = (isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $appConfig['tahun_tagihan'];
    $html .= "<div class=\"col-md-2\">
                <div class=\"form-group\">
                  <label for=\"\">Tahun Pajak</label>
                  <select class=\"form-control\" name=tahun id=tahun $isreadonly>
                    <option value=2015".(($tahunselect==2015)?' selected':'').">2015</option>
                    <option value=2016".(($tahunselect==2016)?' selected':'').">2016</option>
                    <option value=2017".(($tahunselect==2017)?' selected':'').">2017</option>
                    <option value=2018".(($tahunselect==2018)?' selected':'').">2018</option>
                    <option value=2019".(($tahunselect==2019)?' selected':'').">2019</option>
                    <option value=2020".(($tahunselect==2020)?' selected':'').">2020</option>
                    <option value=2021".(($tahunselect==2021)?' selected':'').">2021</option>
                    <option value=2022".(($tahunselect==2022)?' selected':'').">2022</option>
                    <option value=2023".(($tahunselect==2023)?' selected':'').">2023</option>
                    <option value=2024".(($tahunselect==2024)?' selected':'').">2024</option>
                  </select>
                </div>
              </div>";
  }

  $html .= "</div>";


  // if($initData['CPM_TYPE'] == 5){
  //     $html .= "<tr>
  //       <td width=\"39%\">Tahun Berlaku Perubahan</td>
  //       <td width=\"60%\">
  //         <input type=\"text\" name=\"tahunBerlaku\" id=\"tahunBerlaku\" maxlength=\"4\" onkeypress=\"return iniAngka(event,this)\" readonly=\"readonly\" value=\"".(($initData['CPM_SPPT_YEAR_BERLAKU']!='')? $initData['CPM_SPPT_YEAR_BERLAKU']:$appConfig['tahun_tagihan']+1)."\" placeholder=\"Tahun\" />
  //       </td>
  //     </tr>";
  // }else{
  //     $html .= "<tr>
  //       <td width=\"39%\">Tahun Pajak</td>
  //       <td width=\"60%\">
  //         <input type=\"text\" name=\"tahun\" id=\"tahun\" maxlength=\"4\" onkeypress=\"return iniAngka(event,this)\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$appConfig['tahun_tagihan'])."\" placeholder=\"Tahun\" />
  //       </td>
  //     </tr>";
  // }

  if($initData['CPM_TYPE']=='9') {
    $subjenis = (isset($initData['CPM_SUBTYPE']) && $initData['CPM_SUBTYPE'] != '') ? $initData['CPM_SUBTYPE'] : 1;
    $html .= '<div class="row">
                <div class="col-md-12">
                  <input type="radio" id="subjenis1" name="subjenis" value="1"'.(($subjenis==1)?' checked':'').'>
                  <label for="subjenis1">Pengurangan PBB (tahun yang dipilih)</label><br>

                  <input type="radio" id="subjenis2" name="subjenis" value="2"'.(($subjenis==2)?' checked':'').' disabled>
                  <label for="subjenis2">Pengurangan Sebelum SPPT Terbit</label><br>

                  <input type="radio" id="subjenis3" name="subjenis" value="3"'.(($subjenis==3)?' checked':'').' disabled>
                  <label for="subjenis3">Pengurangan Pengenaan JPB</label><br>

                  <input type="radio" id="subjenis4" name="subjenis" value="4"'.(($subjenis==4)?' checked':'').'>
                  <label for="subjenis4">Pengurangan Permanen</label>
                </div>
              </div>';
  }

  $html .= "<div class=\"row\">
              <div class=\"col-md-12\">
                <h3 style=\"width:unset\">A. DATA OBJEK PAJAK</h3>
              </div>
              <div class=\"col-md-12\" id='tnop' style=\"display:none\">
                <div class=\"row\">
                  <div class=\"col-md-4\">
                    <div class=\"form-group\">
                      <label for=\"nop\">NOP</label>
                      <input type=\"text\" class=\"form-control\" name=\"nop\" id=\"nop\" maxlength=\"18\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" placeholder=\"NOP\" />
                    </div>
                  </div>
                  <div class=\"col-md-1\">
                    <br />
                    <button type=\"button\" class=\"btn btn-primary bg-maka\" name=\"btn-cek-NOP\" id=\"btn-cek\" onclick=\"getDataOp()\" value=\"Cek\" style=\"margin-top:7px\">Cek</button>
                  </div>
                </div>
              </div>";
  if ($initData['CPM_TYPE'] == 5) {
    $html .= "<div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"\">Tahun Penetapan Terakhir</label>
                  <input type=\"text\" class=\"form-control\" name=\"tahun\" id=\"tahun\" readonly maxlength=\"4\" value=\"" . ((isset($initData['CPM_SPPT_YEAR']) && $initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : "") . "\" placeholder=\"Tahun\" />
                </div>
              </div>";
  }
  $html .= "</div>
            <div class=\"row\">
              <div class=\"col-md-6\">
                <div class=\"form-group\">
                  <label for=\"almtOP\">Alamat Objek Pajak</label>
                  <input type=\"text\" class=\"form-control\" name=\"almtOP\" id=\"almtOP\" maxlength=\"33\" value=\"" . (($initData['CPM_OP_ADDRESS'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_OP_ADDRESS']) : '') . "\" placeholder=\"Alamat\" />
                </div>
              </div>
              <div class=\"col-md-4\">
                <div class=\"form-group\">
                  <label for=\"rtOP\">RT / RW</label>
                  <div class=\"row\">";
          if($initData['CPM_TYPE'] == 1){

            $optionRT = $optionRW = '';
            for ($i=1; $i <= 299; $i++) {
              $x_num = sprintf("%03d", $i);
              $selectit = ($i==1) ? 'selected':'';
              $optionRT .= "<option $selectit value=$x_num>$x_num</option>";
            }
            for ($i=0; $i <= 59; $i++) {
              $x_num = sprintf("%02d", $i);
              $selectit = ($i==0) ? 'selected':'';
              $optionRW .= "<option $selectit value=$x_num>$x_num</option>";
            }

            $html .= "<div class=\"col-md-5\">
                        <select name=\"rtOP\" id=\"rtOP\" class=\"form-control\">$optionRT</select>
                      </div>
                      <div class=\"col-md-1\" style=\"text-align:center;margin-top:5px;padding:0px\">/</div>
                      <div class=\"col-md-5\">
                        <select name=\"rwOP\" id=\"rwOP\" class=\"form-control\">$optionRW</select>
                      </div>";
          }else{
            $html .= "<div class=\"col-md-5\">
                        <input type=\"text\" class=\"form-control\" name=\"rtOP\" id=\"rtOP\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_OP_RT'] != '') ? $initData['CPM_OP_RT'] : '') . "\" placeholder=\"000\"/>
                      </div>
                      <div class=\"col-md-1\" style=\"text-align:center;margin-top:5px;padding:0px\">/</div>
                      <div class=\"col-md-5\">
                        <input type=\"text\" class=\"form-control\" name=\"rwOP\" id=\"rwOP\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_OP_RW'] != '') ? $initData['CPM_OP_RW'] : '') . "\" placeholder=\"00\"/>
                      </div>";
          }
        $html .= "</div>
                </div>
              </div>
            </div>
            <div class=\"row\" style=\"display: none\">
              <div class=\"col-md-6\">
                <div class=\"form-group\">
                  <label for=\"nomorOP\">Blok/Kav/Nomor</label>
                  <input type=\"text\" class=\"form-control\" name=\"nomorOP\" id=\"nomorOP\" maxlength=\"10\"  placeholder=\"Nomor\" />
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"provinsiOP\">Provinsi</label>
                  <select class=\"form-control\" name=\"propinsiOP\" id=\"propinsiOP\">" . $optionProvOP . "</select>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kabupatenOP\">Kabupaten</label>
                  <select class=\"form-control\" name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kecamatanOP\">Kecamatan</label></td>
                  <select class=\"form-control\" name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kelurahanOP\">Desa</label>
                  <select class=\"form-control\" name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-12\">
                <h3 style=\"width:unset\">B. DATA WAJIB PAJAK</h3>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"nmKuasa\">Nama Kuasa</label>
                  <input type=\"text\" class=\"form-control\" name=\"nmKuasa\" id=\"nmKuasa\" maxlength=\"33\" value=\"" . (($initData['CPM_REPRESENTATIVE'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_REPRESENTATIVE']) : '') . "\" placeholder=\"Nama Kuasa\" />
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"noKtpWp\">No KTP</label>
                  <input type=\"text\" class=\"form-control\" name=\"noKtpWp\" onblur=\"return cekWP(event, this);\" id=\"noKtpWp\" maxlength=\"25\" value=\"" . ((isset($initData['CPM_WP_NO_KTP']) && $initData['CPM_WP_NO_KTP'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_NO_KTP']) : '') . "\" placeholder=\"No KTP\" />
                  <br/>
                  <span id=\"div-loadwp-wait\"></span>
                  <span id=\"div-tmbahwp\"></span>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"nmWp\">Nama Wajib Pajak</label></td>
                  <input type=\"text\" class=\"form-control\" readonly name=\"nmWp\" id=\"nmWp\" maxlength=\"33\" value=\"" . (($initData['CPM_WP_NAME'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_NAME']) : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"tglMasuk\">Tanggal Surat Masuk</label>
                  <input type=\"text\" class=\"form-control\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . ((isset($initData['CPM_DATE_RECEIVE']) && $initData['CPM_DATE_RECEIVE'] != '') ? $initData['CPM_DATE_RECEIVE'] : $today) . "\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-6\">
                <div class=\"form-group\">
                  <label for=\"almtWP\">Alamat WP</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"almtWP\" id=\"almtWP\" maxlength=\"45\" value=\"" . (($initData['CPM_WP_ADDRESS'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_ADDRESS']) : '') . "\" placeholder=\"Alamat\" />
                </div>
              </div>
              <div class=\"col-md-4\">
                <div class=\"form-group\">
                  <label for=\"rtWP\">RT / RW</label></td>
                  <div class=\"row\">
                    <div class=\"col-md-5\">
                      <input type=\"text\" class=\"form-control\" readonly name=\"rtWP\" id=\"rtWP\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_WP_RT'] != '') ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"000\"/>
                    </div>
                    <div class=\"col-md-1\" style=\"margin-top:5px;text-align:center;padding:0px\">/</div>
                    <div class=\"col-md-5\">
                      <input type=\"text\" class=\"form-control\" readonly name=\"rwWP\" id=\"rwWP\" maxlength=\"3\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_WP_RW'] != '') ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"propinsi\">Provinsi</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"propinsi\" id=\"propinsi\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_PROVINCE'] != '') ? $initData['CPM_WP_PROVINCE'] : '') . "\" placeholder=\"Provinsi\" />
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kabupaten\">Kabupaten/Kota</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"kabupaten\" id=\"kabupaten\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KABUPATEN'] != '') ? $initData['CPM_WP_KABUPATEN'] : '') . "\" placeholder=\"Kabupaten\"/>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kecamatan\">Kecamatan</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"kecamatan\" id=\"kecamatan\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KECAMATAN'] != '') ? $initData['CPM_WP_KECAMATAN'] : '') . "\" placeholder=\"Kecamatan\"/>
                </div>
              </div>
              <div class=\"col-md-3\">
                <div class=\"form-group\">
                  <label for=\"kelurahan\">" . $appConfig['LABEL_KELURAHAN'] . "</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"kelurahan\" id=\"kelurahan\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KELURAHAN'] != '') ? $initData['CPM_WP_KELURAHAN'] : '') . "\" placeholder=\"Kelurahan\"/>
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-4\">
                <div class=\"form-group\">
                  <label for=\"\">No. HP WP</label>
                  <input type=\"text\" class=\"form-control\" readonly name=\"hpWP\" id=\"hpWP\" maxlength=\"15\" onkeypress=\"return iniAngka(event,this)\" value=\"" . (($initData['CPM_WP_HANDPHONE'] != '') ? $initData['CPM_WP_HANDPHONE'] : '') . "\" placeholder=\"Nomor HP\" />
                </div>
              </div>
              <div class=\"col-md-8\">
                <div class=\"form-group\">
                  <label for=\"lon\">Koordinat</label>
                  <div class=\"row\">
                    <div class=\"col-md-2\" style=\"margin-top: 5px;\">Latitude:</div>
                    <div class=\"col-md-4\">
                      <input type=\"text\" name=\"lat\" id=\"lat\" class=\"form-control\" value=\"" . (($initData['CPM_LAT'] != '') ? $initData['CPM_LAT'] : '') . "\" />
                    </div>
                    <div class=\"col-md-2\" style=\"margin-top: 5px;\">Longitude:</div>
                    <div class=\"col-md-4\">
                      <input type=\"text\" name=\"lon\" id=\"lon\" class=\"form-control\" value=\"" . (($initData['CPM_LON'] != '') ? $initData['CPM_LON'] : '') . "\" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class=\"row\">
              <div class=\"col-md-12\" style=\"text-align:right\">
                <button class=\"btn btn-primary bg-maka\" value=\"Cari Latitude & Longitude\" onClick=\"window.open('{$appConfig['MAP_URL']}/?action=searchKelurahan&kelurahan='+$('#kelurahanOP option:selected').text(),'_blank');\">Cari Latitude & Longitude</button>
                <button class=\"btn btn-primary bg-maka\" value=\"Lihat Latitude & Longitude dalam Peta\" onClick=\"viewMap('{$appConfig['MAP_URL']}');\">Lihat Latitude & Longitude dalam Peta</button>
              </div>
            </div>";
  $html .= "<div class=\"row\" style=\"margin-top: 20px\">
              <div class=\"col-md-12\">
                Lampiran :
                <ol id=\"lampiran\" style=\"margin-left: -20px;\">
				  <li id=\"berkas110\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"8388608\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8388608)) ? "checked=\"checked\"" : "") . "> Surat Keterangan Desa.</li>
                  <li id=\"berkas1\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1)) ? "checked=\"checked\"" : "") . "> Surat Permohonan.</li>
                  <li id=\"berkas2\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 2)) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Objek Pajak (SPOP) dan lampiran SPOP.</li>
                  <li id=\"berkas3\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4)) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas4\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8)) ? "checked=\"checked\"" : "") . "> Fotocopi KTP / Kartu Keluarga.</li>
                  <li id=\"berkas5\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 16)) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Kepemilikan Tanah.</li>
                  <li id=\"berkas6\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 32)) ? "checked=\"checked\"" : "") . "> Fotocopi IMB.</li>
                  <li id=\"berkas7\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 64)) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Pelunasan PBB Tahun Sebelumnya.</li>
                  <li id=\"berkas8\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 128)) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                  <li id=\"berkas9\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 256)) ? "checked=\"checked\"" : "") . "> Surat Ketetapan Pajak Daerah (SKPD).</li>
                  <li id=\"berkas10\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"512\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 512)) ? "checked=\"checked\"" : "") . "> Surat Setoran Pajak Daerah (SSPD).</li>
                  <li id=\"berkas11\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"1024\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1024)) ? "checked=\"checked\"" : "") . "> Surat Kuasa (bila dikuasakan).</li>
                  <li id=\"berkas12\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"2048\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 2048)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB / SKP tahun lalu.</li>
                  <li id=\"berkas13\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"4096\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4096)) ? "checked=\"checked\"" : "") . "> Fotocopi bukti pembayaran PBB yang terakhir.</li>
                  <li id=\"berkas14\" class=\"berkas\" > <input type=\"checkbox\" name=\"lampiran[]\" value=\"8192\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8192)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB tetangga terdekat.</li>
                <!--<li id=\"berkas14\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"8192\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8192) ? "checked=\"checked\"" : "") . "> Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas15\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"16384\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 16384) ? "checked=\"checked\"" : "") . "> Fotocopi Sertifikat Objek PBB yang diajukan permohonan pengurangan / Bukti kepemilikan tanah lainnya / dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas16\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"32768\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 32768) ? "checked=\"checked\"" : "") . "> Fotocopi Sertifikat Objek PBB yang diajukan keberatan / Bukti kepemilikan tanah lainnya / dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas17\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"65536\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 65536) ? "checked=\"checked\"" : "") . "> Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</li>
                  <li id=\"berkas18\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"131072\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 131072) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</li>
                  <li id=\"berkas19\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"262144\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 262144) ? "checked=\"checked\"" : "") . "> Surat Pernyataan Besarnya Penghasilan.</li>
                  <li id=\"berkas20\" class=\"berkas\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"524288\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 524288) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB tetangga terdekat.</li>
                  <li id=\"berkas21\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"1048576\" class=\"attach\" " . ((is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1048576) ? "checked=\"checked\"" : "") . "> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>-->
                  <li id=\"berkas91\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1)) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                  <li id=\"berkas92\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 2)) ? "checked=\"checked\"" : "") . "> Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas93\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</li>
                  <li id=\"berkas94\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8)) ? "checked=\"checked\"" : "") . "> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
                  <li id=\"berkas95\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 16)) ? "checked=\"checked\"" : "") . "> Surat Keterangan : Tidak Mampu / Tidak Bekerja / Tidak Ada Penghasilan / Lainnya / Dokumen lain yang dipersamakan dan telah ditandatangani oleh Pejabat Berwenang.</li>
                  <li id=\"berkas96\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 32)) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas97\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 64)) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Pengurangan.</li>
                  <li id=\"berkas98\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 128)) ? "checked=\"checked\"" : "") . "> Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</li>
                  <li id=\"berkas99\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 9) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 256)) ? "checked=\"checked\"" : "") . "> Fotocopi Izin Mendirikan Bangunan (IMB), khusus bangunan yang bersifat komersil.</li>
                  <li id=\"berkas101\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 1)) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                  <li id=\"berkas102\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"2\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 2)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB yang akan diajukan Permohonan Keberatan.</li>
                  <li id=\"berkas103\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4)) ? "checked=\"checked\"" : "") . "> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
                  <li id=\"berkas104\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"8\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 8)) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                  <li id=\"berkas105\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 16)) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Keberatan.</li>
                  <li id=\"berkas106\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 32)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB tetangga terdekat.</li>
                  <li id=\"berkas107\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"64\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] == 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 64)) ? "checked=\"checked\"" : "") . "> Fotocopi Izin Mendirikan Bangunan (IMB), apabila objek yang diajukan keberatan memiliki bangunan.<br/>(Khusus bangunan yang bersifat komersil)</li>
                  <li id=\"berkas108\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"2097152\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 2097152)) ? "checked=\"checked\"" : "") . "> Surat Keterangan dari Pemecahan dari Kelurahan.</li>
                  <li id=\"berkas109\" class=\"berkas\" style=\"display: none;\"> <input type=\"checkbox\" name=\"lampiran[]\" value=\"4194304\" class=\"attach\" " . (((is_numeric($initData['CPM_TYPE']) && $initData['CPM_TYPE'] != 9 && $initData['CPM_TYPE'] != 10) && (is_numeric($initData['CPM_ATTACHMENT']) && $initData['CPM_ATTACHMENT'] & 4194304)) ? "checked=\"checked\"" : "") . "> Foto Copy NPWP (jika ada).</li>
                </ol>
              </div>
            </div>
            <div class=\"row\" style=\"margin-top: 20px\">
              <div class=\"col-md-12\">";
  if ($initData['CPM_STATUS'] > 0) {
    $html .= "<button class=\"btn btn-primary bg-maka\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Kembali\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"'>Kembali</button> ";
  } else {
    $html .= "<input type=\"hidden\" name=\"jmlTagihan\" id=\"jmlTagihan\" value=\"" . ((isset($initData['CPM_SPPT_DUE']) && $initData['CPM_SPPT_DUE'] != '') ? $initData['CPM_SPPT_DUE'] : 0) . "\">
    <input type=\"hidden\" name=\"tglBayar\" id=\"tglBayar\" value=\"" . ((isset($initData['CPM_SPPT_PAYMENT_DATE']) && $initData['CPM_SPPT_PAYMENT_DATE'] != '') ? $initData['CPM_SPPT_PAYMENT_DATE'] : '') . "\">
    <hr style=\"border-top:1px solid #717171\">
    <button type=\"submit\" class=\"btn btn-primary bg-maka\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\">Simpan</button> ";
    if (preg_match("/$uname/i", $appConfig['ROLE_LOKET'])) {
      $html .= "<button type=\"submit\" class=\"btn btn-primary bg-maka\" name=\"btn-save\" id=\"btn-kirim\" value=\"Kirim\">Kirim</button> ";
    }
    $html .= "<button name=\"btn-cancel\" class=\"btn btn-primary bg-maka\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"'>Batal</button> ";
  }

  $html .= "  </div>
            </div>
          </form>
        </div>
      </div>";
  return $html;
}

function getInitData($id = "")
{
  global $DBLink;

  if ($id == '') return getDataDefault();

  $qry = "SELECT * FROM cppmod_pbb_services WHERE CPM_ID='{$id}'";

  $res = mysqli_query($DBLink, $qry);
  if (!$res) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
    return getDataDefault();
  } else {
    while ($row = mysqli_fetch_assoc($res)) {
      $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
      
      $tipe = $row['CPM_TYPE'];
      if($tipe==9){
        $row['CPM_SUBTYPE'] = 1;
        $cpmid = $row['CPM_ID'];
        $q = "SELECT CPM_SUBTYPE FROM cppmod_pbb_service_reduce_subtype WHERE CPM_ID='{$cpmid}'";
        $res2 = mysqli_query($DBLink, $q);
        $n = mysqli_num_rows($res2);
        if($n>0){
          $r = mysqli_fetch_assoc($res2);
          $row['CPM_SUBTYPE'] = $r['CPM_SUBTYPE'];
        }
      }
      
      return $row;
    }
  }
}

function getDataDefault()
{
  $jnsBerkas = isset($_REQUEST['jnsBerkas']) ? $_REQUEST['jnsBerkas'] : 1;
  $default = array(
    'CPM_ID' => '', 
    'CPM_TYPE' => $jnsBerkas, 
    'CPM_REPRESENTATIVE' => '', 
    'CPM_WP_NAME' => '', 
    'CPM_WP_ADDRESS' => '', 
    'CPM_WP_RT' => '', 
    'CPM_WP_RW' => '',
    'CPM_WP_KELURAHAN' => '', 
    'CPM_WP_KECAMATAN' => '', 
    'CPM_WP_KABUPATEN' => '', 
    'CPM_WP_PROVINCE' => '', 
    'CPM_WP_HANDPHONE' => '', 
    'CPM_OP_KECAMATAN' => '', 
    'CPM_OP_KELURAHAN' => '',
    'CPM_OP_RW' => '', 
    'CPM_OP_RT' => '', 
    'CPM_OP_ADDRESS' => '', 
    'CPM_OP_NUMBER' => '', 
    'CPM_ATTACHMENT' => '', 
    'CPM_STATUS' => 0, 
    'CPM_LAT' => '-5.3977439', 
    'CPM_LON' => '105.0716717'
  );
  return $default;
}

function getLastNumber($suffix)
{
  global $DBLink;

  $qry = "SELECT MAX(CPM_NO) AS CPM_NO FROM cppmod_pbb_generate_service_number WHERE CPM_ID LIKE '%{$suffix}'";

  $res = mysqli_query($DBLink, $qry);
  if (!$res) {
    echo $qry . "<br>";
    echo mysqli_error($DBLink);
  } else {
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

function generateNumber($year, $jnsBerkas)
{
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
  $numberSuffix[11] = $arConfig['FORMAT_NOMOR_BERKAS_SKNJOP'];
  $numberSuffix[12] = $arConfig['FORMAT_NOMOR_BERKAS_PENGURANGAN_DENDA'];
  $numberSuffix[13] = $arConfig['FORMAT_NOMOR_BERKAS_RESTITUSI'];
  $lastNumber = getLastNumber($numberSuffix[$jnsBerkas]);
  $newNumber = $lastNumber + 1;

  return $newNumber . $numberSuffix[$jnsBerkas];
}

function save($status)
{
  global $data, $DBLink, $uname;

  $mode  = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
  if ($mode == 'edit')
    $nomor = $_REQUEST['nomor'];
  else $nomor = generateNumber($_REQUEST['tahun'], $_REQUEST['jnsBerkas']);

  $nmKuasa = mysqli_real_escape_string($DBLink, $_REQUEST['nmKuasa']);
  $nmWp = mysqli_real_escape_string($DBLink, $_REQUEST['nmWp']);
  $noKtpWp = mysqli_real_escape_string($DBLink, $_REQUEST['noKtpWp']);
  $tglMasuk = substr($_REQUEST['tglMasuk'], 6, 4) . '-' . substr($_REQUEST['tglMasuk'], 3, 2) . '-' . substr($_REQUEST['tglMasuk'], 0, 2);
  $almtWP = mysqli_real_escape_string($DBLink, $_REQUEST['almtWP']);
  $rtWP = $_REQUEST['rtWP'];
  $rwWP = $_REQUEST['rwWP'];
  $propinsiWP = $_REQUEST['propinsi'];
  $kabupatenWP = $_REQUEST['kabupaten'];
  $kecamatanWP = $_REQUEST['kecamatan'];
  $kelurahanWP = $_REQUEST['kelurahan'];
  $hpWP = $_REQUEST['hpWP'];
  $jnsBerkas = $_REQUEST['jnsBerkas'];
  $nop = $_REQUEST['nop'];
  $almtOP = mysqli_real_escape_string($DBLink, $_REQUEST['almtOP']);
  $nomorOP = $_REQUEST['nomorOP'];
  $rtOP = $_REQUEST['rtOP'];
  $rwOP = $_REQUEST['rwOP'];
  $kecamatanOP = $_REQUEST['kecamatanOP'];
  $kelurahanOP = $_REQUEST['kelurahanOP'];
  $attachment = $_REQUEST['attachment'];
  $lat = $_REQUEST['lat'];
  $lon = $_REQUEST['lon'];
  $tahunBerlaku = isset($_REQUEST['tahunBerlaku']) ? $_REQUEST['tahunBerlaku'] : "";

  if($jnsBerkas==9){
    $subjenis = $_REQUEST['subjenis'];
  }

  $tahun = $_REQUEST['tahun'];
  if ($_REQUEST['jmlTagihan'] == '') {
    $jmlTagihan = 0;
  } else {
    $jmlTagihan = $_REQUEST['jmlTagihan'];
  }
  $tglBayar = $_REQUEST['tglBayar'];


  if ($mode == 'edit') {
    $qry = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', 
		CPM_WP_KELURAHAN='{$kelurahanWP}', CPM_WP_KECAMATAN='{$kecamatanWP}',	CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', 
		CPM_OP_ADDRESS='{$almtOP}', CPM_OP_ADDRESS_NO='{$nomorOP}', CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
		CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, CPM_SPPT_DUE={$jmlTagihan}, CPM_SPPT_YEAR='{$tahun}', CPM_SPPT_PAYMENT_DATE='{$tglBayar}',
		CPM_LON='{$lon}', CPM_LAT='{$lat}', CPM_SPPT_YEAR_BERLAKU = '{$tahunBerlaku}', CPM_WP_NO_KTP = '{$noKtpWp}' WHERE CPM_ID = '{$nomor}' ";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }

    if ($res) {
      if($jnsBerkas==9){
        $arrsubJsnis = [
                        '00', 
                        '01 Pengurangan PBB Pada Tahun Dipilih', 
                        '02 Pengurangan Sebelum SPPT Terbit', 
                        '03 Pengurangan Pengenaan JPB', 
                        '04 Pengurangan Permanen'
                      ];
        $nmJENIS = $arrsubJsnis[$subjenis];
        $qry = "UPDATE cppmod_pbb_service_reduce_subtype 
                SET CPM_SUBTYPE='{$subjenis}', CPM_SUBTYPE_NAME='{$nmJENIS}' 
                WHERE CPM_ID = '{$nomor}'";
        mysqli_query($DBLink, $qry);
      }

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
  } else {
    $tmp = explode('/', $nomor);
    $qry = "INSERT INTO cppmod_pbb_generate_service_number (CPM_ID, CPM_NO, CPM_CREATOR, CPM_DATE_CREATED) VALUES ('{$nomor}', '" . $tmp[0] . "','{$uname}', '{$tglMasuk}')";

    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
      echo $qry . "<br>";
      echo mysqli_error($DBLink);
    }

    if ($res) {
      $qry = "INSERT INTO cppmod_pbb_services (CPM_ID, CPM_TYPE, CPM_REPRESENTATIVE, CPM_WP_NAME, CPM_WP_ADDRESS, CPM_WP_RT, CPM_WP_RW, CPM_WP_KELURAHAN, CPM_WP_KECAMATAN,
			CPM_WP_KABUPATEN, CPM_WP_PROVINCE, CPM_WP_HANDPHONE, CPM_OP_NUMBER, CPM_OP_ADDRESS, CPM_OP_ADDRESS_NO, CPM_OP_RT, CPM_OP_RW, CPM_OP_KELURAHAN, CPM_OP_KECAMATAN, CPM_ATTACHMENT, CPM_RECEIVER, 
			CPM_DATE_RECEIVE, CPM_STATUS, CPM_SPPT_DUE, CPM_SPPT_YEAR, CPM_SPPT_PAYMENT_DATE, CPM_LON, CPM_LAT, CPM_SPPT_YEAR_BERLAKU, CPM_WP_NO_KTP) VALUES ('{$nomor}', {$jnsBerkas}, '{$nmKuasa}', '{$nmWp}', '{$almtWP}',
			'{$rtWP}', '{$rwWP}', '{$kelurahanWP}', '{$kecamatanWP}', '{$kabupatenWP}', '{$propinsiWP}', 
			'{$hpWP}', '{$nop}', '{$almtOP}', '{$nomorOP}', '{$rtOP}', '{$rwOP}', '{$kelurahanOP}', '{$kecamatanOP}', 
			{$attachment}, '{$uname}', '{$tglMasuk}', '{$status}', '{$jmlTagihan}',
			 '{$tahun}', '{$tglBayar}','{$lon}','{$lat}','{$tahunBerlaku}','{$noKtpWp}')";

      $res2 = mysqli_query($DBLink, $qry);
      if ($res2 === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
      }

      if ($res2) {
        if($jnsBerkas==9){
          $arrsubJsnis = [
                          '00', 
                          '01 Pengurangan PBB Pada Tahun Dipilih', 
                          '02 Pengurangan Sebelum SPPT Terbit', 
                          '03 Pengurangan Pengenaan JPB', 
                          '04 Pengurangan Permanen'
                        ];
          $nmJENIS = $arrsubJsnis[$subjenis];
          $qry = "INSERT INTO cppmod_pbb_service_reduce_subtype (CPM_ID, CPM_SUBTYPE, CPM_SUBTYPE_NAME) 
                  VALUES ('{$nomor}', {$subjenis}, '{$nmJENIS}')";
          mysqli_query($DBLink, $qry);
        }

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
    } else {
      echo mysqli_error($DBLink);
    }
  }
}

$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($m);

$save = isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : '';

if ($save == 'Simpan') {
  save(0);
} else if ($save == 'Kirim') {
  if ($_REQUEST['jnsBerkas'] == '7')
    save(3);
  else if ($_REQUEST['jnsBerkas'] == '11')
    save(4);
  else
    save(1);
} else {
  $svcid  = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
  $initData = getInitData($svcid);

  echo "<script language=\"javascript\">
			var axx = '" . base64_encode($_REQUEST['a']) . "';
			
			function viewMap(map_url) {
				var latitude = document.getElementById('lat').value;
				var longitude = document.getElementById('lon').value;
				var controller = map_url.match('controller');
				var tmp = map_url.split('&');
				if ((latitude.length > 0) && (longitude.length > 0)) {
					if (controller == 'controller'){
						window.open(tmp[0]+'&action=searchPoint&lon='+longitude+'&lat='+latitude, '_blank');
					} else {
						window.open(map_url+'?action=searchPoint&lon='+longitude+'&lat='+latitude, '_blank');
					}
				} else {
					alert('Latitude dan Longitude belum terisi');
				}
			}

		</script> ";
  echo formPenerimaan($initData);
}
