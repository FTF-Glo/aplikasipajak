<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'notaris', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "function/BPHTB/func-display-document.php");


echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.formatCurrency-1.4.0.min.js\" type=\"text/javascript\"></script>\n";
echo "<script src=\"function/BPHTB/notaris/func-new-ssb-pel.js?ver=1a\"></script>\n"; // function

echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"function/BPHTB/notaris/func-detail-notaris.css\">"; // function

echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/table/table.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/bphtb/css/button/buttons-core.css\">";
echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"inc/datepicker/datepickercontrol.css\">";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<script src=\"inc/datepicker/datepickercontrol.js\"></script>\n";

//echo "<script src=\"function/BPHTB/notaris/autofill.js\"></script>\n";

echo "<script type=\"text/javascript\" src=\"inc/js/jquery.validate.min.js\"></script>";

echo "<link type=\"text/css\" href=\"function/BPHTB/notaris/newspop.css\" rel=\"stylesheet\">"; // function

echo "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css\">";

//error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
//ini_set("display_errors", 1); 

function getConfigValue($key)
{
  global $DBLink;
  $id = $_REQUEST['a'];
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
function getRole()
{
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
function getNoPel($type)
{
  global $DBLink;
  $nomor = "1";
  $arrJnsPerolehan = array(1 => "01", 2 => "02", 3 => "03", 4 => "04", 5 => "05", 6 => "06", 7 => "07", 8 => "08", 9 => "09", 10 => "10", 11 => "11", 12 => "12", 13 => "13", 14 => "14", 21 => "21", 22 => "22", 30 => "30", 31 => "31", 32 => "32", 33 => "33");
  $jnsPerolehan = $arrJnsPerolehan[$type];
  $tahun = date("Y");

  $qry = "select * from cppmod_ssb_berkas WHERE CPM_BERKAS_JNS_PEROLEHAN = '{$type}'
            and DATE_FORMAT(STR_TO_DATE(CPM_BERKAS_TANGGAL,'%d-%m-%Y'),'%Y') ='{$tahun}' 
                order by CPM_BERKAS_ID DESC limit 0,1";
  $res = mysqli_query($DBLink, $qry);

  if ($row = mysqli_fetch_array($res)) {
    $nomor_exp = explode(".", $row['CPM_BERKAS_NOPEL']);
    $nomor = (int) $nomor_exp[2];
    $nomor++;
  }
  $nomor = str_pad($nomor, 5, "0", STR_PAD_LEFT);
  if ($type != "0") {
    $noPel = "{$tahun}.{$jnsPerolehan}.{$nomor}";
  } else {
    $noPel = "";
  }
  return $noPel;
}
function aphb()
{
  global $DBLink;

  $texthtml = " Hamparan <select name=\"pengurangan-aphb\" id=\"pengurangan-aphb\" onchange=\"checkTransLast();\">
				    <option value=\"\" disabled>Pilih</option>
				    ";
  $qry = "select * from cppmod_ssb_aphb ORDER BY CPM_APHB_KODE asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);

  while ($data = mysqli_fetch_assoc($res)) {

    $texthtml .= "<option value=\"" . $data['CPM_APHB'] . "\">" . str_pad($data['CPM_APHB_KODE'], 2, "0", STR_PAD_LEFT) . ":" . $data['CPM_APHB'] . "</option>";
  }
  $texthtml .= "			      </select>";
  return $texthtml;
}
function jenishak()
{
  global $DBLink;

  $texthtml = "<select name=\"right-land-build\" id=\"right-land-build\" onchange=\"checkTransLast();hidepasar();cekAPHB();\" style=\"height: 30px\">";
  $qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);

  while ($data = mysqli_fetch_assoc($res)) {
    $texthtml .= "<option value=\"" . $data['CPM_KD_JENIS_HAK'] . "\">" . str_pad($data['CPM_KD_JENIS_HAK'], 2, "0", STR_PAD_LEFT) . " " . $data['CPM_JENIS_HAK'] . "</option>";
  }
  $texthtml .= "			      </select>";
  return $texthtml;
}

function jenishakmilik()
{
  global $DBLink;

  $texthtml = "<select name=\"jenis_hak_milik\" id=\"jenis_hak_milik\" style=\"height: 30px\">";
  $qry = "select * from cppmod_ssb_jenis_hak_milik ORDER BY ID_JENIS_MILIK asc";
  // echo $qry;
  $res = mysqli_query($DBLink, $qry);

  while ($data = mysqli_fetch_assoc($res)) {
    $texthtml .= "<option value=\"" . $data['ID_JENIS_MILIK'] . "\">" . str_pad($data['ID_JENIS_MILIK'], 2, "0", STR_PAD_LEFT) . " " . $data['NAMA_JENIS_HAK_MILIK'] . "</option>";
  }
  $texthtml .= "			      </select>";
  return $texthtml;
}
function select_kecamatan()
{
  global $DBLink;

  $texthtml = "<select name=\"kecamatan\" id=\"kecamatan\" onchange=\"changekelurahann(this.value);\" style=\"height: 30px\">";
  $qry = "select * from cppmod_tax_kecamatan2 ORDER BY CPC_TKC_KECAMATAN asc";
  $res = mysqli_query($DBLink, $qry);

  $texthtml .= "<option disabled>-- Pilih Kecamatan --</option>";
  while ($data = mysqli_fetch_assoc($res)) {
    $texthtml .= "<option value=\"" . $data['CPC_TKC_KECAMATAN'] . "\">" . $data['CPC_TKC_KECAMATAN'] . "</option>";
  }
  $texthtml .= "</select>";
  return $texthtml;
}
function formSSB($val = array())
{
  global $DBLink;
  $value = array();
  $errMsg = array();
  $j = count($val);
  $err = "";
  for ($i = 0; $i < $j; $i++) {
    if ((substr($val[$i], 0, 5) == 'Error') || ($val[$i] == "")) {
      $errMsg[$i] = $val[$i];
      $value[$i] = "";
    } else {
      $errMsg[$i] = "";
      $value[$i] = $val[$i];
    }
  }
  $pengenaan = getConfigValue('PENGENAAN_HIBAH_WARIS');
  $hitungAPHB = getConfigValue('HITUNG_APHB');

  $configAPHB = getConfigValue('CONFIG_APHB');
  $configPengenaan = getConfigValue('CONFIG_PENGENAAN');
  // var_dump($value[47] == NULL);
  if ($value[47] == NULL || empty($value[47])) {
    $koordinat = $value[47];
  } else {
    $koordinat = 0;
  }

  if (getConfigValue('DENDA') == '1') {
    $c_denda = "$(\"#denda-value\").val(0);
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
    $kena_denda = "<tr>
					<td>Denda &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"denda-percent\" id=\"denda-percent\" onKeyPress=\"return numbersonly(this, event);checkTransaction();\" onkeyup=\"checkTransaction()\" title=\"Denda\" size=\"2\"  /> %</td>
					<td id=\"tdenda\" align=\"right\"><input type=\"text\" name=\"denda-value\" id=\"denda-value\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"checkTransaction()\" title=\"Denda\" readonly=\"readonly\"/></td>
				  </tr>";
    $kena_denda2 = "";
  } else {
    $c_denda = "$(\"#denda-value\").val(0);
					$(\"#denda-percent\").val(0);";
    $kena_denda = "";
    $kena_denda2 = "<input type=\"hidden\" name=\"denda-percent\" id=\"denda-percent\" />
					  <input type=\"hidden\" name=\"denda-value\" id=\"denda-value\"/>";
  }

  ($configAPHB == "1") ? $display_aphb = "" : $display_aphb = "style=\"display:none\"";
  ($configPengenaan == "1") ? $display_pengenaan = "" : $display_pengenaan = "style=\"display:none\"";
  $html = "
<!-- <link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\"> -->
<script language=\"javascript\">


var edit = false;
var hitungaphb = " . $hitungAPHB . ";
var configaphb = " . $configAPHB . ";
var configpengenaan = " . $configPengenaan . ";
$(function(){
	
	$('#loaderCek').hide();
	$('#pengurangan-aphb').attr(\"disabled\", \"disabled\");
    $(\"#op-znt\").val(\"0\");
	$(\"#name2\").mask(\"" . getConfigValue('KODE_DAERAH') . "?99999999999999\");
	$(\"#noktp\").focus(function() {
	  $(\"#noktp\").val(\"" . getConfigValue('KODE_DAERAH') . "\");
	});
	
	" . $c_denda . "
	
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
        
        
        var isFocus = true;
        $(\"#ceknop\").click(function(eve){
            eve.preventDefault();            
            if(isFocus){
                $(\"#load-pbb\").html(\"<img src='image/large-loading.gif' style='width:20px;'>\");
                var nop = $(\"#name2\").val();
                $.ajax({
                    type: \"POST\",
                    url: \"" . $sRootPath . "function/BPHTB/notaris/func-cekNOPPBB.php\",
                    data: \"nop=\"+nop,
                    success: function(data){
                        // console.log('ajirts');
                        if(data.length != 0){
                            setElementsVal(data);                      
                        }
                        else{
                            alert(\"NOP tidak tersedia/tidak terdata!\");
                        }
                        $(\"#load-pbb\").html(\"\");
                    }
                });   
                //isFocus = false;
            }
        });

        
        
       
        function setElementsVal(data){
            var elVal = data.split('*');
            
            //current
            
            $(\"#nama-wp-lama\").val(elVal[0]);
            $(\"#nama-wp-cert\").val(elVal[0]);
            /*$(\"#name\").val(elVal[0]);
            $(\"#npwp\").val('0');
            $(\"#noktp\").val('0000000000000000');
            $(\"#address\").val(elVal[1]);
            $(\"#kelurahan\").val(elVal[2]);
            $(\"#rt\").val(elVal[3]);
            $(\"#rw\").val(elVal[4]);
            $(\"#kecamatan\").val(elVal[5]);
            $(\"#kabupaten\").val(elVal[6]);
            $(\"#zip-code\").val(elVal[7]);*/            
            $(\"#address2\").val(elVal[8]);
            $(\"#kelurahan2\").val(elVal[9]);
            $(\"#rt2\").val(elVal[10]);
            $(\"#rw2\").val(elVal[11]);
            $(\"#kecamatan2\").val(elVal[12]);
            $(\"#kabupaten2\").val(elVal[13]);            
            $(\"#zip-code2\").val('0');
			$(\"#zip-code\").val('0');
            $(\"#land-area\").val(elVal[14]);
            $(\"#land-njop\").val(elVal[15]);
            $(\"#building-area\").val(elVal[16]);
            $(\"#building-njop\").val(elVal[17]);
            $(\"#right-year\").val(elVal[18]);
			$(\"#op-znt\").val(elVal[19]);
            
            //old
            $(\"#nmWPOld\").val(elVal[0]);
            $(\"#alamatWPOld\").val(elVal[1]);
            $(\"#kelurahanWPOld\").val(elVal[2]);
            $(\"#rtWPOld\").val(elVal[3]);
            $(\"#rwWPOld\").val(elVal[4]);
            $(\"#kecamatanWPOld\").val(elVal[5]);
            $(\"#kabupatenWPOld\").val(elVal[6]);
            $(\"#alamatOPOld\").val(elVal[8]);
            $(\"#kelurahanOPOld\").val(elVal[9]);
            $(\"#rtOPOld\").val(elVal[10]);
            $(\"#rwOPOld\").val(elVal[11]);
            $(\"#kecamatanOPOld\").val(elVal[12]);
            $(\"#kabupatenOPOld\").val(elVal[13]);            
            $(\"#luasBumiOld\").val(elVal[14]);
            $(\"#njopBumiOld\").val(elVal[15]);
            $(\"#luasBangunanOld\").val(elVal[16]);
            $(\"#njopBangunanOld\").val(elVal[17]);
            $(\"#tahunSPPTOld\").val(elVal[18]);
            
            addSN();
            addET();
            checkTransaction();            
            setDisabledVal(true);
            $(\"#cekUpdateData\").css('display','');
            changeColor('#eeeeee');
        }
        
        $(\"#chkDisElements\").click(function(){
            if ($(\"#chkDisElements\").is(\":checked\")){
                setDisabledVal(false);
                changeColor('#ffffff');
                $(\"#isPBB\").val(1);
            }else{
                setDisabledVal(true);
                changeColor('#eeeeee');
                $(\"#isPBB\").val(0);
            }        
        });
            
        function setDisabledVal(valDis){
            // console.log('masul');
            $(\"#nama-wp-lama\").attr('readonly',valDis);
            $(\"#nama-wp-cert\").attr('readonly',false);
            /*$(\"#name\").attr('readonly',valDis);
            $(\"#npwp\").attr('readonly',valDis);
            $(\"#noktp\").attr('readonly',valDis);
            $(\"#address\").attr('readonly',valDis);
            $(\"#kelurahan\").attr('readonly',valDis);
            $(\"#rt\").attr('readonly',valDis);
            $(\"#rw\").attr('readonly',valDis);
            $(\"#kecamatan\").attr('readonly',valDis);
            $(\"#kabupaten\").attr('readonly',valDis);
            $(\"#zip-code\").attr('readonly',valDis);*/
             
            $(\"#address2\").attr('readonly',false);
            $(\"#kelurahan2\").attr('readonly',false);
            $(\"#rt2\").attr('readonly',false);
            $(\"#rw2\").attr('readonly',false);
            $(\"#kecamatan2\").attr('readonly',false);
            $(\"#kabupaten2\").attr('readonly',false);          
            $(\"#zip-code2\").attr('readonly',false); 

            // $(\"#land-area\").attr('readonly',valDis);
            // $(\"#land-njop\").attr('readonly',valDis);
            // $(\"#building-area\").attr('readonly',valDis);
            /*if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
                $(\"#building-area\").attr('readonly',valDis);
            }*/
            // $(\"#building-njop\").attr('readonly',valDis);
            $(\"#right-year\").attr('readonly',valDis);
        }


     

        function changeColor(vColor){
            
            $(\"#nama-wp-lama\").css('background-color',vColor);
            // $(\"#nama-wp-cert\").css('background-color',vColor);
            /*$(\"#name\").css('background-color',vColor);
            $(\"#npwp\").css('background-color',vColor);
            $(\"#noktp\").css('background-color',vColor);
            $(\"#address\").css('background-color',vColor);
            $(\"#kelurahan\").css('background-color',vColor);
            $(\"#rt\").css('background-color',vColor);
            $(\"#rw\").css('background-color',vColor);
            $(\"#kecamatan\").css('background-color',vColor);
            $(\"#kabupaten\").css('background-color',vColor);
            $(\"#zip-code\").css('background-color',vColor);*/

           /* $(\"#address2\").css('background-color',vColor);
            $(\"#kelurahan2\").css('background-color',vColor);
            $(\"#rt2\").css('background-color',vColor);
            $(\"#rw2\").css('background-color',vColor);
            $(\"#kecamatan2\").css('background-color',vColor);
            $(\"#kabupaten2\").css('background-color',vColor);            
            $(\"#zip-code2\").css('background-color',vColor);*/

            $(\"#land-area\").css('background-color',vColor);
            $(\"#land-njop\").css('background-color',vColor);
            $(\"#building-area\").css('background-color',vColor);
            /*if ($(\"#building-area\").value =='0' || $(\"#building-area\").value ==''){
                    $(\"#building-area\").css('background-color',vColor);
            }*/
            
            $(\"#building-njop\").css('background-color',vColor);
            $(\"#right-year\").css('background-color',vColor);
        }
		
		


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
		var appID	= '" . $_REQUEST['a'] . "';	
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

   function fillFields() {
            const nop = document.getElementById('main-nop-input').value;

            if (nop.length === 18) {
                document.getElementById('nopsub-2-1').value = nop.substring(0, 2);
                document.getElementById('nopsub-2-2').value = nop.substring(2, 4);
                document.getElementById('nopsub-2-3').value = nop.substring(4, 7);
                document.getElementById('nopsub-2-4').value = nop.substring(7, 10);
                document.getElementById('nopsub-2-5').value = nop.substring(10, 13);
                document.getElementById('nopsub-2-6').value = nop.substring(13, 17);
                document.getElementById('nopsub-2-7').value = nop.substring(17, 18);
                syncInput(); // Panggil syncInput() untuk menggabungkan dan menampilkan hasil
            } else {
                alert('NOP harus terdiri dari 18 digit');
            }
        }

function syncInput() {
    var input1 = document.getElementById('nopsub-2-1').value;
    var input2 = document.getElementById('nopsub-2-2').value;
    var input3 = document.getElementById('nopsub-2-3').value;
    var input4 = document.getElementById('nopsub-2-4').value;
    var input5 = document.getElementById('nopsub-2-5').value;
    var input6 = document.getElementById('nopsub-2-6').value;
    var input7 = document.getElementById('nopsub-2-7').value;
    
    var fullName = input1 + '' + input2 + '' + input3+ '' + input4+ '' + input5+ '' + input6+ '' + input7; // Concatenate with spaces in between
    document.getElementById('name2').value = fullName;
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


</style>
<style scoped>

        .button-success,
        .button-error,
        .button-warning,
        .button-secondary {
            color: white;
            border-radius: 4px;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        .button-success {
            background: rgb(28, 184, 65); /* this is a green */
        }

        .button-error {
            background: rgb(202, 60, 60); /* this is a maroon */
        }

        .button-warning {
            background: rgb(223, 117, 20); /* this is an orange */
        }

        .button-secondary {
            background: rgb(66, 184, 221); /* this is a light blue */
        }

    </style>
<div id=\"main-content\">
<form name=\"form-notaris\" id=\"form-notaris\" method=\"post\" action=\"\" onsubmit=\"return checkform();\">
    <input type=\"hidden\" name=\"isPBB\" id=\"isPBB\" value=\"0\"/>
    <input type=\"hidden\" name=\"nmWPOld\" id=\"nmWPOld\"/>
    <input type=\"hidden\" name=\"npwpWPOld\" id=\"npwpWPOld\" value=\"0\"/>
    <input type=\"hidden\" name=\"noktpWPOld\" id=\"noktpWPOld\" value=\"0000000000000000\"/>
    <input type=\"hidden\" name=\"alamatWPOld\" id=\"alamatWPOld\"/>
    <input type=\"hidden\" name=\"kelurahanWPOld\" id=\"kelurahanWPOld\"/>
    <input type=\"hidden\" name=\"rtWPOld\" id=\"rtWPOld\"/>
    <input type=\"hidden\" name=\"rwWPOld\" id=\"rwWPOld\"/>
    <input type=\"hidden\" name=\"kecamatanWPOld\" id=\"kecamatanWPOld\"/>
    <input type=\"hidden\" name=\"kabupatenWPOld\" id=\"kabupatenWPOld\"/>
    <input type=\"hidden\" name=\"kodeposWPOld\" id=\"kodeposWPOld\" value=\"0\"/>
    <input type=\"hidden\" name=\"alamatOPOld\" id=\"alamatOPOld\"/>
    <input type=\"hidden\" name=\"kelurahanOPOld\" id=\"kelurahanOPOld\"/>
    <input type=\"hidden\" name=\"rtOPOld\" id=\"rtOPOld\"/>
    <input type=\"hidden\" name=\"rwOPOld\" id=\"rwOPOld\"/>
    <input type=\"hidden\" name=\"kecamatanOPOld\" id=\"kecamatanOPOld\"/>
    <input type=\"hidden\" name=\"kabupatenOPOld\" id=\"kabupatenOPOld\"/>
    <input type=\"hidden\" name=\"kodeposOPOld\" id=\"kodeposOPOld\" value=\"0\"/>
    <input type=\"hidden\" name=\"luasBumiOld\" id=\"luasBumiOld\"/>
    <input type=\"hidden\" name=\"njopBumiOld\" id=\"njopBumiOld\"/>
    <input type=\"hidden\" name=\"luasBangunanOld\" id=\"luasBangunanOld\"/>
    <input type=\"hidden\" name=\"njopBangunanOld\" id=\"njopBangunanOld\"/>
    <input type=\"hidden\" name=\"tahunSPPTOld\" id=\"tahunSPPTOld\"/>
    
		  <table width=\"953\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">

    <!--   <div class=\"container\">
          <div class=\"row\">

                <div class=\"form-group col-md-6\">
                    <label> NOP </label>
                    <div style=\"display: flex; align-items: center;\">
                        <div class=\"form-group\">
                    
                              <div class=\"col-md-1\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-1\" style=\"padding: 6px;\" name=\"nopsub-2-1\" id=\"nopsub-2-1\" placeholder=\"PR\" maxlength=\"2\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-1\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-2\" style=\"padding: 6px;\" name=\"nopsub-2-2\" id=\"nopsub-2-2\" placeholder=\"DTII\" maxlength=\"2\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-2\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-3\" style=\"padding: 6px;\" name=\"nopsub-2-3\" id=\"nopsub-2-3\" placeholder=\"KEC\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-2\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-4\" style=\"padding: 6px;\" name=\"nopsub-2-4\" id=\"nopsub-2-4\" placeholder=\"KEL\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-2\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-5\" style=\"padding: 6px;\" name=\"nopsub-2-5\" id=\"nopsub-2-5\" placeholder=\"BLOK\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-2\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-6\" style=\"padding: 6px;\" name=\"nopsub-2-6\" id=\"nopsub-2-6\" placeholder=\"NO.URUT\" maxlength=\"4\" oninput=\"syncInput()\" value=\"\">
                              </div>
                              <div class=\"col-md-2\" style=\"padding: 0\">
                                  <input type=\"text\" class=\"form-control nop-inputs-7\" style=\"padding: 6px;\" name=\"nopsub-2-7\" id=\"nopsub-2-7\" placeholder=\"KODE\" maxlength=\"1\" oninput=\"syncInput()\" value=\"\">
                              </div>
                          </div>
              
                    
                        <button type=\"button\" value=\"x\" onclick=\"javascript:$('#CPM_TGL_LAPOR1-{$id}').val('');\" class=\"btn btn-primary\" style=\"margin-top:-20px;margin-left:4px\">Cek NOP</button>
                    </div>
                </div>
                <div class=\"col-md-6\">
                  <div class=\"form-group\">
                    <label for=\"password\">Lokasi Objek Pajak</label>
                    <input type=\"password\" class=\"form-control\" id=\"password\" placeholder=\"Masukkan password\">
                  </div>
                </div>
          </div>
        
        </div> -->
       
			<tr>
			  <td colspan=\"2\" align=\"center\" style=\"border-radius: 10px 10px 0px 0px;\"><strong><font size=\"+2\">Form Surat Setoran Pajak Daerah <br>Bea Perolehan Hak Atas Tanah dan Bangunan <br>(SSPD-BPHTB)</font></strong><br /><br />
			</tr>
			
			
			
			  <td width=\"3%\" align=\"center\" valign=\"top\">
            <div  id='rcorners3'><font size='+1' color=\"white\"><b>A. </b></font></div>
        </td>
        
			   <td>
         <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
			
      <tr>
      <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td width=\"18%\">NOP PBB</td>
        <td>
          <div class=\"form-group\">
                
                <div class=\"col-md-12\" style=\"padding: 0\">
                  <input type=\"text\" class=\"form-control\" style=\"padding: 6px;\" id=\"main-nop-input\" placeholder=\"Masukkan NOP\" oninput=\"fillFields()\" maxlength=\"18\">
                </div>
                <div class=\"col-md-1\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-1\" style=\"padding: 6px;\" name=\"nopsub-2-1\" id=\"nopsub-2-1\" placeholder=\"PR\" maxlength=\"2\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-1\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-2\" style=\"padding: 6px;\" name=\"nopsub-2-2\" id=\"nopsub-2-2\" placeholder=\"DTII\" maxlength=\"2\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-2\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-3\" style=\"padding: 6px;\" name=\"nopsub-2-3\" id=\"nopsub-2-3\" placeholder=\"KEC\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-2\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-4\" style=\"padding: 6px;\" name=\"nopsub-2-4\" id=\"nopsub-2-4\" placeholder=\"KEL\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-2\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-5\" style=\"padding: 6px;\" name=\"nopsub-2-5\" id=\"nopsub-2-5\" placeholder=\"BLOK\" maxlength=\"3\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-2\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-6\" style=\"padding: 6px;\" name=\"nopsub-2-6\" id=\"nopsub-2-6\" placeholder=\"NO.URUT\" maxlength=\"4\" oninput=\"syncInput()\" value=\"\">
                </div>
                <div class=\"col-md-2\" style=\"padding: 0\">
                    <input type=\"text\" class=\"form-control nop-inputs-7\" style=\"padding: 6px;\" name=\"nopsub-2-7\" id=\"nopsub-2-7\" placeholder=\"KODE\" maxlength=\"1\" oninput=\"syncInput()\" value=\"\">
                </div>
           </div>
           
           </td>
           <td>
           <input type=\"button\" value=\"cek NOP\" onclick=\"cek_allow();checkNOP();\" id=\"ceknop\">
       </td>
      </tr>
         <tr>
				<td width=\"2%\"><div align=\"right\"></div></td>
				  <td width=\"18%\"></td> 
          <td width=\"30%\">
          <input type=\"hidden\" name=\"name2\" id=\"name2\"/> 
          </td>

				  <td><input type=\"hidden\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"-\"/></td> 
				  </tr>
				<tr>
				  <td valign='top'><div align=\"right\">2.</div></td>
				  <td valign='top'>Lokasi Objek Pajak</td>
				  <td><textarea name=\"address2\"  id=\"address2\" cols=\"55\" rows=\"4\" title=\"Lain-lain\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\">" . $value[13] . "</textarea>
				  </tr>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td>Kelurahan/Desa</td>
				  <td><input type=\"text\"  name=\"kelurahan2\" id=\"kelurahan2\" value=\"" . $value[15] . "\" onKeyPress=\"return nextFocus(this, event);\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" size=\"52\" maxlength=\"20\" title=\"Kelurahan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td><input type=\"text\" name=\"op-znt\" id=\"op-znt\" hidden=\"hidden\" value=\"" . $value[45] . "\"/></td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>RT/RW</td>
				  <td><input  type=\"text\" name=\"rt2\" id=\"rt2\" maxlength=\"3\" size=\"21\" value=\"" . $value[16] . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RT Objek Pajak\"/>
					/
					<input  type=\"text\" name=\"rw2\" id=\"rw2\" maxlength=\"3\" size=\"21\" value=\"" . $value[17] . "\" onKeyPress=\"return nextFocus(this, event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
				  <td>Kecamatan</td>
				  <td><input  type=\"text\" name=\"kecamatan2\" id=\"kecamatan2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $value[18] . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"52\" maxlength=\"20\" title=\"Kecamatan Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input  type=\"text\" name=\"kabupaten2\" id=\"kabupaten2\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $value[19] . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"52\" maxlength=\"20\" title=\"Kabupaten/Kota Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
				  <td>Kode Pos</td>
				  <td><input  type=\"text\" name=\"zip-code2\" id=\"zip-code2\" value=\"" . $value[20] . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"52\" maxlength=\"5\" title=\"Kode Pos Objek Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Nomor Sertifikat</td>
				  <td><input type=\"text\" name=\"certificate-number\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" id=\"certificate-number\" value=\"" . $value[28] . "\" size=\"52\" maxlength=\"80\" title=\"Nomor Sertifikat Tanah\"/></td>
				</tr>
				<tr>
					<td><div align=\"right\">9.</div></td>
					<td>Nama WP Sesuai Sertifikat</td> 
					  <td><input  type=\"text\" name=\"nama-wp-cert\" id=\"nama-wp-cert\" value=\"" . $value[41] . "\" size=\"52\" maxlength=\"52\" title=\"Nama WP Sesuai Sertifikat\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\"/>
					  </td>
				</tr>	
				<tr>
					<td><div align=\"right\">10.</div></td>
					<td>Nama WP Lama</td> 
					  <td><input type=\"text\" name=\"nama-wp-lama\" id=\"nama-wp-lama\" value=\"" . $value[40] . "\" size=\"52\" maxlength=\"30\" title=\"Nama WP Lama\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\"/>
					  </td>
				</tr>	
			  </table>
			  
			  
			  </td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>B. </b></font></div></td>
			  <td width=\"97%\"><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr id=\"cekUpdateData\" style=\"display:none;\">
				  <td width=\"2%\"><div align=\"right\"></div></td>
				  <td width=\"18%\"></td>
				  <td width=\"40%\"><!--<input type=\"checkbox\" name=\"chkDisElements\" id=\"chkDisElements\" > Silahkan ceklis untuk Update Data.--></td>
				   <td colspan=\"2\">&nbsp;</td>
				</tr>
				<tr>
				  <td width=\"2%\"><div align=\"right\">1.</div></td>
				  <td>Nomor KTP</td>
          <td width=\"30%\"><input type=\"text\" name=\"noktp\" id=\"noktp\" value=\"" . $value[38] . "\" onKeyPress=\"return nextFocus(this, event)\" size=\"33\" maxlength=\"24\" title=\"No KTP wajib di isi\" \" onblur=\"checkTransLast();\" /> <input type=\"button\" value=\"Cek KTP\" onclick=\"checkKTP();\" id=\"cekktp\"> <label id=\"load-ktp\"> <span style=\"color :red;  font-style: italic;\"><sup>Double klik</sup></span></label></td>
				<tr>
				  <td><div align=\"right\">2.</div></td>
				  <td>NPWP</td>
				  <td><input type=\"text\" name=\"npwp\" id=\"npwp\" value=\"" . $value[3] . "\" onkeypress=\"return nextFocus(this,event)\" size=\"52\" maxlength=\"15\" title=\"NPWP\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">3.</div></td>
				  <td width=\"18%\">Nama Wajib Pajak</td>
				  <td width=\"40%\"><input type=\"text\" name=\"name\" id=\"name\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $value[2] . "\" onkeypress=\"return nextFocus(this,event)\" size=\"52\" maxlength=\"60\" title=\"Nama Wajib Pajak\"/></td>
				   <td colspan=\"2\">&nbsp;</td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">4.</div></td>
				  <td>Alamat Wajib Pajak</td>
				  <td><textarea  name=\"address\" id=\"address\" cols=\"56\" rows=\"4\" title=\"Lain-lain\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\">" . $value[4] . "</textarea>
				  <td></td>
				  <td></td>
				</tr>
				<tr>
				  <td><div align=\"right\">5.</div></td>
                  <td>Kecamatan</td>
                  <td><input type=\"text\" name=\"kecamatan\" id=\"kecamatan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" onkeypress=\"return nextFocus(this,event)\" size=\"52\" maxlength=\"52\" title=\"Kecamatan Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">6.</div></td>
				  <td>RT/RW</td>
				  <td><input type=\"text\" name=\"rt\" id=\"rt\" maxlength=\"3\" size=\"22\"  value=\"" . $value[7] . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RT Wajib Pajak\"/> / <input type=\"text\" name=\"rw\" id=\"rw\" maxlength=\"3\" size=\"21\"  value=\"" . $value[8] . "\" onkeypress=\"return nextFocus(this,event)\" title=\"RW Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">7.</div></td>
                  <td>Kelurahan/Desa</td>
                  <td>
                    <input type=\"text\" name=\"kelurahan\" id=\"kelurahan\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"" . $value[6] . "\" onkeypress=\"return nextFocus(this,event)\" size=\"52\" maxlength=\"20\" title=\"Kelurahan/Desa Wajib Pajak\"/>
                  </td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">8.</div></td>
				  <td>Kabupaten/Kota</td>
				  <td><input type=\"text\" name=\"kabupaten\" id=\"kabupaten\" onKeyUp=\"javascript:{this.value = this.value.toUpperCase(); }\"  value=\"" . $value[10] . "\" onkeypress=\"return nextFocus(this,event)\"  size=\"52\" maxlength=\"20\" title=\"Kabupaten/Kota Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
				<tr>
				  <td><div align=\"right\">9.</div></td>
				  <td>Kode Pos</td>
				  <td><input type=\"text\" name=\"zip-code\" id=\"zip-code\"  value=\"" . $value[11] . "\" onKeyPress=\"return numbersonly(this, event)\"  size=\"52\" maxlength=\"5\" title=\"Kode Pos Wajib Pajak\"/></td>
				  <td>&nbsp;</td>
				  <td>&nbsp;</td>
				</tr>
			  </table>
			  <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td>10.</td>
				  <td width=\"22%\">Titik Koordinat</td>
				  <td><input type=\"text\" name=\"Koordinat\" id=\"Koordinat\" value=\"" . 0 . "\" onkeyup=\"javascript:{this.value = this.value.toUpperCase(); }\" value=\"\" onkeypress=\"return nextFocus(this,event)\" size=\"51\" title=\"Titik Koordinat Wajib diisi\" isdatepicker=\"true\"></td>
			    <tr>
                <tr>
                  <td width=\"14\"><div align=\"right\">11.</div></td>
                  <td colspan=\"2\">Jenis perolehan hak atas tanah atau bangunan</td>
                </tr>

                <tr>
                  <td><div align=\"right\"></div></td>
                  <td colspan=\"2\">
                    " . jenishak() . "
                    </td>
                <tr>
               
                <tr id=\"aphb\" " . $display_aphb . ">
                  <td><div align=\"right\"></div></td>
                  <td colspan=\"2\">" . aphb() . "</td>
                <tr>    

                <tr>
                <td><div align=\"right\">12.</div></td>
                        <td>Jenis Hak Milik</td>
                        <td colspan=\"2\">
                        " . jenishakmilik() . "
                        </td>
                       
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
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
			  <input type=\"text\" name=\"right-year\" id=\"right-year\" maxlength=\"4\" size=\"4\" value=\"" . $value[21] . "\" onKeyPress=\"return numbersonly(this, event)\" title=\"Tahun Pajak\"/></th>
			<th colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\">Luas x NJOP PBB /m²</th>
			</tr>
			</thead>
		  <tr>
          <td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Tanah / Bumi</td>
          <td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">11. Luas Tanah (Bumi)</td>
          <td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">12. NJOP Tanah (Bumi) /m²</td>
          <td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (11x12)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input  type=\"text\" name=\"land-area\" id=\"land-area\" value=\"" . $value[22] . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addSN();addET();checkTransaction();\" title=\"Luas Tanah\"/>
			  m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input  type=\"text\" name=\"land-njop\" id=\"land-njop\" value=\"" . $value[23] . "\" onKeyPress=\"return numbersonly(this, event)\"  onkeyup=\"addSN();addET();checkTransaction();\" title=\"NJOP Tanah\"/></td>
			<td width=\"28\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">13.</td>
			<td width=\"126\" align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\"  id=\"t1\">&nbsp;</td>
		  </tr>
		  <tr>
			<td rowspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#999999\">Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">14. Luas Bangunan</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00CCFF\">15. NJOP Bangunan / m²</td>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">Angka (14x15)</td>
			</tr>
		  <tr>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#CCCCCC\"><input  type=\"text\" name=\"building-area\" id=\"building-area\" value=\"" . $value[24] . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"Luas Bangunan\"/>
		m²</td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#999999\"><input  type=\"text\" name=\"building-njop\" id=\"building-njop\" value=\"" . $value[25] . "\" onKeyPress=\"return numbersonly(this, event)\" onkeyup=\"addET();addSN();checkTransaction();\" title=\"NJOP Bangunan\"/></td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">16.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t2\">&nbsp;</td>
		  </tr>
		  <tr>
			<td colspan=\"3\" align=\"right\" valign=\"middle\" bgcolor=\"#999999\">NJOP PBB </td>
			<td align=\"center\" valign=\"middle\" bgcolor=\"#00FFFF\">17.</td>
			<td align=\"right\" valign=\"middle\" bgcolor=\"#CCCCCC\" id=\"t3\">&nbsp;</td>
		  </tr>
			  </table>
			  
			 
			  <div id=\"nilai-pasar\">
				</div>
				<br>
				13. Nilai Pasar/Harga Transaksi Lelang  Rp. <input type=\"text\" name=\"trans-value\" id=\"trans-value\" value=\"" . $value[27] . "\" onKeyPress=\"return numbersonly(this, event)\"  onchange=\"checkTransaction()\" onkeyup=\"checkTransaction();\" title=\"Harga Transaksi\"/ onblur=\"loadLaikPasar();\">
        <div id=\"npoptkptahunlalu\">
			    <input type=\"checkbox\" id=\"tahunlalu\" onchange=\"handleCheckboxChange()\" value=\"9\"> Menggunakan Nilai NPOPTKP Tahun Lalu 
			  </div>
        </td>
			</tr>
			<tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>C. </b></font></div></td>
			  <td>
				
			  <table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td width=\"443\"><strong>AKUMULASI NILAI PEROLEHAN HAK SEBELUMNYA</strong></td>
				  <td width=\"457\" id=\"akumulasi\" align=\"right\"></td></td>
				</tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>D. </b></font></div></td>
			  <td><table width=\"900\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				  <tr>
					<td width=\"460\"><strong>Penghitungan BPHTB</strong></td>
					<td width=\"171\"><em><strong>Dalam rupiah</strong></em></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak (NPOP)</td>
					<td id=\"tNJOP\" align=\"right\">&nbsp;</td>
				  </tr>
				 <!-- <tr>
					<td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP)</td>
					<td id=\"NPOPTKP\" align=\"right\"></td>
				  </tr> -->
				  <tr>
					  <td>Nilai Perolehan Objek Pajak Tidak Kena Pajak (NPOPTKP) tes</td>
            <td align=\"right\"><input type=\"text\" id=\"NPOPTKP_input\" value=\"\"></td>
				  </tr>
				  <tr>
					<td>Nilai Perolehan Objek Pajak Kena Pajak(NPOPKP)</td>
					<td id=\"tNPOPKP\" align=\"right\">&nbsp;</td>
				  </tr>
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang terhutang</td>
					<td id=\"tBPHTBTS\" align=\"right\">&nbsp;</td>
				  </tr>
				   <tr " . $display_pengenaan . ">
					<td>Pengenaan Karena Waris/Hibah Wasiat/Pemberian Hak Pengelola &nbsp;&nbsp;<input style=\"border:none\" type=\"text\" id=\"Phibahwaris\" size=\"1\" name=\"Phibahwaris\" value=\"" . $pengenaan . "\" readonly=\"readonly\"/>%</td>
					<td id=\"tPengenaan\" align=\"right\">&nbsp;</td>
				  </tr>
				  <tr " . $display_aphb . " class='aphb_show'>
					<td>APHB &nbsp;&nbsp;</td>
					<td id=\"tAPHB\" align=\"right\">&nbsp;</td>
				  </tr>
				  " . $kena_denda . "" . $kena_denda2 . "
				  <tr>
					<td>Bea Perolehan atas Hak Tanah dan Bangunan yang harus dibayar</td>
					<td id=\"tBPHTBT\" align=\"right\">&nbsp;</td>
				  </tr>
                  <tr id=\"ketlaporan\">
                  </tr>
                  <tr id=\"ketlaporan2\">
                  </tr>
			  </table></td>
			</tr>
			<tr>
			  <td width=\"3%\" align=\"center\" valign=\"top\"><div  id='rcorners3'><font size='+1' color=\"white\"><b>E. </b></font></div></td>
			  <td><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
				<tr>
				  <td colspan=\"3\"><strong>Jumlah Setoran Berdasarkan : </strong><em>(tekan kotak yang sesuai)</em></td>
				  </tr>
				<tr>
				  <td width=\"24\" align=\"center\" valign=\"top\"><p>
					<label>
					  <input type=\"radio\" checked name=\"RadioGroup1\" value=\"1\" id=\"RadioGroup1_0\"  onclick=\"enableE(this,0);\"/>
					</label>
					<br />
					<br />
				  </p></td>
				  <td width=\"15\" align=\"right\" valign=\"top\">a.</td>
				  <td width=\"583\" valign=\"top\">Penghitungan Wajib Pajak</td>
				</tr>
                                <tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"2\" id=\"RadioGroup1_1\" disabled onclick=\"enableE(this,1);\"/></td>
				  <td align=\"right\" valign=\"top\">b.</td>
				  <td valign=\"top\"><select name=\"jsb-choose\" id=\"jsb-choose\">
					<option value=\"1\">STPD BPHTB</option>
					<option value=\"2\">SKPD Kurang Bayar</option>
					<option value=\"3\">SKPD Kurang Bayar Tambahan</option>
				  </select><font size=\"2\" color=\"red\">*hanya bisa dilakukan di menu kurang bayar</font></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Nomor : 
					<input type=\"text\" name=\"jsb-choose-number\" id=\"jsb-choose-number\" size=\"30\" maxlength=\"30\" value=\"" . $value[32] . "\" title=\"Nomor Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\">Tanggal : 
					<input type=\"text\" name=\"jsb-choose-date\" id=\"jsb-choose-date\" datepicker=\"true\" datepicker_format=\"DD/MM/YYYY\" readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . $value[33] . "\" title=\"Tanggal Surat Pengurangan\"/></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"3\" id=\"RadioGroup1_4\"  onclick=\"enableE(this,2);\"/></td>
				  <td align=\"right\" valign=\"top\">c.</td>
				  <td valign=\"top\">Pengurangan dihitung sendiri menjadi <select name=\"jsb-choose-percent\" id=\"jsb-choose-percent\" onchange=\"checkTransLast();\"><option value=\"0\">0</option>
				    
					";
  $qry = "select * from cppmod_ssb_pengurangan ORDER BY CPM_KODE_PENGURANGAN asc";
  //echo $qry;exit;
  $res = mysqli_query($DBLink, $qry);

  while ($data = mysqli_fetch_assoc($res)) {
    $html .= "<option value=\"" . $data['CPM_KODE_PENGURANGAN'] . "." . $data['CPM_PENGURANGAN'] . "\">Kode " . $data['CPM_KODE_PENGURANGAN'] . " : " . $data['CPM_PENGURANGAN'] . "%</option>";
  }
  $html .= "			      </select></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\">&nbsp;</td>
				  <td align=\"right\" valign=\"top\">&nbsp;</td>
				  <td valign=\"top\"><!-- Berdasakan peraturan KDH No : --> 
					<input type=\"text\" name=\"jsb-choose-role-number\" id=\"jsb-choose-role-number\" size=\"30\" maxlength=\"30\" value=\"-\" title=\"Peraturan KHD No\" hidden=\"hidden\" /></td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"4\" id=\"RadioGroup1_6\" onclick=\"enableE(this,3);\"/></td>
				  <td align=\"right\" valign=\"top\">d.</td>
				  <td valign=\"top\"><textarea name=\"jsb-etc\" id=\"jsb-etc\" cols=\"35\" rows=\"5\" title=\"Lain-lain\"></textarea>
				  </td>
				</tr>
				<tr>
				  <td align=\"center\" valign=\"top\"><input type=\"radio\" name=\"RadioGroup1\" value=\"5\" id=\"RadioGroup1_8\"  onclick=\"enableE(this,4);\" hidden=\"hidden\"/></td>
				  <td align=\"right\" valign=\"top\"></td>
				  <td valign=\"top\"><!-- Khusus untuk waris dan Hibah Pengurangan dihitung sendiri menjadi --> <input type=\"text\" name=\"jsb-choose-fraction1\" id=\"jsb-choose-fraction1\" size=\"1\" maxlength=\"2\" value=\"" . $value[43] . "\" title=\"pecahan 1\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/><input type=\"text\" name=\"jsb-choose-fraction2\" id=\"jsb-choose-fraction2\" size=\"1\" maxlength=\"2\" value=\"" . $value[44] . "\" title=\"pecahan 2\" onkeyup=\"checkTransaction()\" onKeyPress=\"return numbersonly(this, event)\" hidden=\"hidden\"/></td>
				</tr>
			  </table>
			  </td>
			</tr>
			<tr>
			<td colspan=\"2\" align=\"center\" valign=\"middle\" id=\"jmlBayar\">Jumlah yang dibayarkan : </td>
			</tr>
			<tr>
          <input type=\"hidden\" id=\"jsb-total-before\" name=\"jsb-total-before\">
          <input type=\"hidden\" id=\"hd-npoptkp\" name=\"hd-npoptkp\">
          <input type=\"hidden\" name=\"role\" id=\"role\" name=\"role\" value=\"" . getRole() . "\">
			<td colspan=\"2\" align=\"center\" valign=\"middle\"  style=\"border-radius: 0px 0px 10px 10px;\"><input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" class=\"button-success pure-button\"/>
			  &nbsp;&nbsp;&nbsp;
			  <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan dan Finalkan\" class=\"button-success pure-button\" /></td>
			</tr>
			
			
		  </table>
		</form>
</div>

  <div id=\"id01\" class=\"w3-modal\">
    <div class=\"w3-modal-content\">
      <div id=\"w3-container\">
        
        
      </div>
    </div>
</div>

";
  return $html;
}

function validation($str, &$err)
{
  $OK = true;
  $j = count($str);
  $err = "";
  /* for ($i=0; $i<$j ; $i++) {
      if (($i!=31) && ($i!=32) && ($i!=33) && ($i!=34) && ($i!=35) && ($i!=37) && ($i!=39)) {
      if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
      $err .= $str[$i] ."<br>\n";
      $OK = false;
      }
      }
      if ($str[30]==2) {
      if (($i==31) || ($i==32) || ($i==33)) {
      if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
      $err .= $str[$i] ."<br>\n";
      $OK = false;
      }
      }
      }
      if ($str[30]==3) {
      if (($i==39)   || ($i==37)){
      if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
      $err .= $str[$i] ."<br>\n";
      $OK = false;
      }
      }
      }
      if ($str[30]==4) {
      if ($i==35) {
      if ((substr($str[$i],0,5)=='Error') || ($str[$i]=="")) {
      $err .= $str[$i] ."<br>\n";
      $OK = false;
      }
      }
      }
      } */
  //print_r("ok:".$OK.$err.$j);
  return $OK;
}

function cek_sw_allow_input($nop)
{
  global $data, $DBLink;
  $boleh_input = true;

  $dbLimit = getConfigValue('TENGGAT_WAKTU');

  $cari = "select CPM_TRAN_SSB_ID,CPM_TRAN_STATUS,
                case when DATE_ADD(a.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) > CURDATE() then 0 else 1 end as KADALUARSA
                from cppmod_ssb_doc a inner JOIN cppmod_ssb_tranmain b ON
                a.CPM_SSB_ID = b.CPM_TRAN_SSB_ID
                where 
                a.CPM_OP_NOMOR = '" . mysqli_real_escape_string($DBLink, $nop) . "' and
                b.CPM_TRAN_FLAG = '0'
                order by (CPM_TRAN_DATE) desc limit 0,1";

  $query = mysqli_query($DBLink, $cari);
  if ($doc = mysqli_fetch_array($query)) {
    if ($doc['CPM_TRAN_STATUS'] == '5') {
      $boleh_input = true;
    } else {
      $boleh_input = false;
    }
  }

  return $boleh_input;
}

function cek_gw_allow_input($nop)
{
  global $data, $DBLink;
  $boleh_input = true;

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  $dbLimit = getConfigValue('TENGGAT_WAKTU');


  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

  $cari = "select payment_flag,
                case when 
                DATE_ADD(saved_date,INTERVAL {$dbLimit} day) > CURDATE() then 0
                else 1 end as KADALUARSA    
             from {$dbTable} 
             where 
                op_nomor ='" . mysqli_real_escape_string($DBLinkLookUp, $nop) . "'";
  $query = mysqli_query($DBLinkLookUp, $cari);
  if ($doc = mysqli_fetch_array($query)) {
    if ($doc['payment_flag'] == 0) {
      $boleh_input = ($doc['KADALUARSA'] == 1) ? true : false;
    }
  }
  return $boleh_input;
}
function getnpoptkp($noktp)
{
  global $data, $DBLink;
  $boleh_input = true;

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  $dbLimit = getConfigValue('TENGGAT_WAKTU');


  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

  $cari = "select count(*) AS jumlah from {$dbTable} WHERE wp_noktp='{$noktp}'";
  $query = mysqli_query($DBLinkLookUp, $cari);
  if ($query === false) {
    echo $qry . "<br>";
    echo mysqli_error($DBLinkLookUp);
  }
  while ($row = mysqli_fetch_assoc($query)) {
    return $row['jumlah'];
  }
}

function getNOKTP($noktp)
{
  global $DBLink;

  $N1 = getConfigValue('NPOPTKP_STANDAR');
  $N2 = getConfigValue('NPOPTKP_WARIS');
  $day = getConfigValue("BATAS_HARI_NPOPTKP");
  $dbLimit = getConfigValue('TENGGAT_WAKTU');

  $CHECK_NPOPTKP_KTP_PAYMENT = getConfigValue('CHECK_NPOPTKP_KTP_PAYMENT');

  $dbName = getConfigValue('BPHTBDBNAME');
  $dbHost = getConfigValue('BPHTBHOSTPORT');
  $dbPwd = getConfigValue('BPHTBPASSWORD');
  $dbTable = getConfigValue('BPHTBTABLE');
  $dbUser = getConfigValue('BPHTBUSERNAME');
  // Connect to lookup database
  SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);
  //payment_flag, mysqli_real_escape_string($payment_flag),
  $tahun = date('Y');
  $qry = "select * 
	        from cppmod_ssb_doc A LEFT JOIN cppmod_ssb_tranmain AS B ON A.CPM_SSB_ID = B.CPM_TRAN_SSB_ID
            where A.CPM_WP_NOKTP = '{$noktp}' and CPM_OP_THN_PEROLEH= '{$tahun}' and (DATE_ADD(DATE(A.CPM_SSB_CREATED), INTERVAL {$day} DAY) > CURDATE()) 
			AND B.CPM_TRAN_STATUS <> 4 AND B.CPM_TRAN_FLAG <> 1 AND A.CPM_OP_JENIS_HAK <> 30 AND A.CPM_OP_JENIS_HAK <> 31 
			AND A.CPM_OP_JENIS_HAK <> 32 AND A.CPM_OP_JENIS_HAK <> 33";
  //print_r($qry); 
  $res = mysqli_query($DBLink, $qry);
  if ($res === false) {
    return false;
  }

  if ($CHECK_NPOPTKP_KTP_PAYMENT == 0) {
    if (mysqli_num_rows($res)) {
      //$num_rows = mysql_num_rows($res);
      // while($row = mysql_fetch_assoc($res)){
      // $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
      // FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
      // //print_r($query2);
      // $r = mysql_query($query2, $DBLinkLookUp);
      // if ( $r === false ){
      // die("Error Insertxx: ".mysql_error());
      // }
      // if(mysql_num_rows ($r)){

      // while($rowx = mysql_fetch_assoc($r)){
      // if ($rowx['EXPRIRE']) {
      // return false;
      // }else{
      // $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
      // $r2 = mysql_query($query3, $DBLinkLookUp);
      // if ( $r2 === false ){
      // die("Error Insertxx: ".mysql_error());
      // }
      // if (mysql_num_rows($r2)) {
      // return true;
      // }
      // }
      // }
      // return true;
      // }else return false;
      // }
      return true;
    } else return false;
  } else {
    if (mysqli_num_rows($res)) {
      $num_rows = mysqli_num_rows($res);
      while ($row = mysqli_fetch_assoc($res)) {
        $query2 = "SELECT wp_noktp, op_nomor, if (date(expired_date) < CURDATE(),1,0) AS EXPRIRE 
							FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 0 ORDER BY saved_date DESC LIMIT 1";
        //print_r($query2);
        $r = mysqli_query($DBLinkLookUp, $query2);
        if ($r === false) {
          die("Error Insertxx: " . mysqli_error($DBLinkLookUp));
        }
        if (mysqli_num_rows($r)) {

          while ($rowx = mysqli_fetch_assoc($r)) {
            if ($rowx['EXPRIRE']) {
              return false;
            } else {
              $query3 = "SELECT wp_noktp, op_nomor FROM ssb WHERE wp_noktp ='{$noktp}' and payment_flag = 1 ORDER BY saved_date DESC LIMIT 1";
              $r2 = mysqli_query($DBLinkLookUp, $query3);
              if ($r2 === false) {
                die("Error Insertxx: " . mysqli_error($DBLinkLookUp));
              }
              if (mysqli_num_rows($r2)) {
                return true;
              }
            }
          }
          return true;
        } else return false;
      }
    } else return false;
  }
}
function save_berkas($idssb)
{
  global $DBLink, $data;
  $nop = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "";
  $jp = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";
  $nopel = getNoPel($jp);
  $alamat_op = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "";
  $kec_op = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "";
  $kel_op = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "";

  $noktp = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "";
  $npwp = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "";
  $npwp_as = $npwp;
  if ($npwp == "") {
    $npwp_as = $noktp;
  }
  $nama_wp = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "";
  $harga = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "";
  $opr = $data->uname;
  $iddoc = $idssb;
  $qry = sprintf(
    "INSERT INTO cppmod_ssb_berkas (
            CPM_BERKAS_NOP,CPM_BERKAS_TANGGAL,CPM_BERKAS_ALAMAT_OP,CPM_BERKAS_KELURAHAN_OP, 
            CPM_BERKAS_KECAMATAN_OP,CPM_BERKAS_NPWP,CPM_BERKAS_NAMA_WP,
            CPM_BERKAS_JNS_PEROLEHAN,CPM_BERKAS_NOPEL, 
            CPM_BERKAS_HARGA_TRAN,CPM_SSB_DOC_ID           
            ) VALUES ('%s','%s','%s',
                    '%s','%s','%s',                    
                    '%s','%s','%s',
                    '%s','%s')",
    mysqli_escape_string($DBLink, $nop),
    date('d-m-Y'),
    mysqli_escape_string($DBLink, $alamat_op),
    mysqli_escape_string($DBLink, $kel_op),
    mysqli_escape_string($DBLink, $kec_op),
    mysqli_escape_string($DBLink, $npwp_as),
    mysqli_escape_string($DBLink, $nama_wp),
    mysqli_escape_string($DBLink, $jp),
    mysqli_escape_string($DBLink, $nopel),
    mysqli_escape_string($DBLink, $harga),
    mysqli_escape_string($DBLink, $iddoc)
  );


  $result = mysqli_query($DBLink, $qry);
  if ($result === false) {
    //handle the error here
    print_r(mysqli_error($DBLink) . $qry);
  }
}
function getBPHTBPayment($lb, $nb, $lt, $nt, $h, $p, $jh, $NPOPTKP, $phw, $aphbt, $denda)
{
  //$a = $_REQUEST['a'];
  /*$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_STANDAR');
		
		$typeR = $jh;
		
		if (($typeR==4) || ($typeR==6)){
			$NPOPTKP =  $this->getConfigValue($a,'NPOPTKP_WARIS');
		} else {
			
		}*/

  /*if($this->getNOKTP($noktp,$nop,$tgl)) {	
			$NPOPTKP = 0;
		}*/
  $configAPHB = getConfigValue('CONFIG_APHB');
  $hitungaphb = getConfigValue('HITUNG_APHB');
  $configPengenaan = getConfigValue('CONFIG_PENGENAAN');
  $a = strval($lb) * strval($nb) + strval($lt) * strval($nt);
  $b = strval($h);
  $npop = 0;
  if ($jh == '15') {
    $npop = $b;
  } else {
    if ($b <= $a) $npop = $a;
    else $npop = $b;
  }
  $npkp = $npop - strval($NPOPTKP);
  $jmlByr = ($npop - strval($NPOPTKP)) * 0.05;
  $hbphtb = ($npop - strval($NPOPTKP)) * 0.05;
  $aphb = 0;
  $hbphtb_pengenaan = 0;
  $hbphtb_aphb = 0;
  if (($jh == 4) || ($jh == 5) || ($jh == 31)) {
    if ($configPengenaan == '1') {
      $hbphtb_pengenaan = ($hbphtb * $phw * 0.01);
      $jmlByr = $hbphtb - ($hbphtb_pengenaan);
    } else {
      $hbphtb_pengenaan = 0;
      $jmlByr = $hbphtb;
    }
  } else if ($jh == 7) {
    if ($configAPHB == '1') {
      $ex_aphb = explode("/", $aphbt);
      $aphb = $ex_aphb[0] / $ex_aphb[1];
      $hbphtb_pengenaan = 0;
      if ($hitungaphb == '1') {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05 * $aphb;
      } else if ($hitungaphb == '2') {
        $hbphtb_aphb = (($npop - strval($NPOPTKP)) * 0.05) - (($npop - strval($NPOPTKP)) * 0.05 * $aphb);
      } else if ($hitungaphb == '3') {
        $hbphtb_aphb = ($npop * $aphb) - (strval($NPOPTKP) * 0.05);
      } else if ($hitungaphb == '0') {
        $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
      }
    } else {
      $hbphtb_aphb = ($npop - strval($NPOPTKP)) * 0.05;
    }
    $jmlByr = $hbphtb_aphb;
  }
  $total_temp = $jmlByr;
  $tp = strval($p);
  if ($tp != 0) $jmlByr = $jmlByr - ($jmlByr * ($tp * 0.01));

  if ($denda > 0) {
    $jmlByr = $jmlByr + $denda;
  } else {
    $jmlByr = $jmlByr;
  }
  if ($jmlByr < 0) $jmlByr = 0;
  return $jmlByr;
}

function save($final)
{
  //echo "<pre>";print_r($_REQUEST);exit;
  global $data, $DBLink, $idocv2;
  $dat = $data;
  //print_r($appDbLink);
  $data = array();
  //$data[0] = $_REQUEST['tax-services-office']? $_REQUEST['tax-services-office'] :"Error: Nama Kantor Pelayanan Pajak Pratama tidak boleh dokosongkan !";
  //$data[1] = $_REQUEST['tax-services-office-code']? $_REQUEST['tax-services-office-code'] :"Error: Kode Kantor Pelayanan Pajak Pratama tidak boleh dikosongkan !";

  //tambahan nomor pendaftaran
  $today = date('Y-m-d');
  $pecahkan = explode('-', $today);
  $tah = $pecahkan[0];
  $bul = (int) $pecahkan[1];

  if ($bul == 1) {
    $bul_rom = 'I';
  } elseif ($bul == 2) {
    $bul_rom = 'II';
  } elseif ($bul == 3) {
    $bul_rom = 'III';
  } elseif ($bul == 4) {
    $bul_rom = 'IV';
  } elseif ($bul == 5) {
    $bul_rom = 'V';
  } elseif ($bul == 6) {
    $bul_rom = 'VI';
  } elseif ($bul == 7) {
    $bul_rom = 'VII';
  } elseif ($bul == 8) {
    $bul_rom = 'VIII';
  } elseif ($bul == 9) {
    $bul_rom = 'IX';
  } elseif ($bul == 10) {
    $bul_rom = 'X';
  } elseif ($bul == 11) {
    $bul_rom = 'XI';
  } else {
    $bul_rom = 'XII';
  }

  $tahbul = $tah . '/' . $bul_rom . '/BPHTB' . '/';
  $length = 4;
  $lenght_string = strlen($tahbul);
  $lenght_string = (int) $lenght_string + 1;

  $query = "SELECT MAX(SUBSTRING(CPM_NO_PENDAFTARAN,{$lenght_string}, {$length})) nomor FROM cppmod_ssb_doc WHERE CPM_NO_PENDAFTARAN LIKE '{$tahbul}%____'";
  $res = mysqli_query($DBLink, $query);
  $nomor = 1;
  if ($data = mysqli_fetch_assoc($res)) {
    $count = (int) $data['nomor'];
    $nomor = $count + 1;
  }

  $no_pendaftaran = $tahbul . str_pad($nomor, $length, '0', STR_PAD_LEFT);

  $isPBB = $_REQUEST['isPBB'];

  $dataOld[0] = $_REQUEST['nmWPOld'];
  $dataOld[1] = $_REQUEST['npwpWPOld'];
  $dataOld[2] = $_REQUEST['noktpWPOld'];
  $dataOld[3] = $_REQUEST['alamatWPOld'];
  $dataOld[4] = $_REQUEST['kelurahanWPOld'];
  $dataOld[5] = $_REQUEST['rtWPOld'];
  $dataOld[6] = $_REQUEST['rwWPOld'];
  $dataOld[7] = $_REQUEST['kecamatanWPOld'];
  $dataOld[8] = $_REQUEST['kabupatenWPOld'];
  $dataOld[9] = $_REQUEST['kodeposWPOld'];

  $dataOld[10] = $_REQUEST['name2'];
  $dataOld[11] = $_REQUEST['alamatOPOld'];
  $dataOld[12] = $_REQUEST['kelurahanOPOld'];
  $dataOld[13] = $_REQUEST['rtOPOld'];
  $dataOld[14] = $_REQUEST['rwOPOld'];
  $dataOld[15] = $_REQUEST['kecamatanOPOld'];
  $dataOld[16] = $_REQUEST['kabupatenOPOld'];
  $dataOld[17] = $_REQUEST['kodeposOPOld'];

  $dataOld[18] = $_REQUEST['luasBumiOld'];
  $dataOld[19] = $_REQUEST['njopBumiOld'];
  $dataOld[20] = $_REQUEST['luasBangunanOld'];
  $dataOld[21] = $_REQUEST['njopBangunanOld'];
  $dataOld[22] = $_REQUEST['tahunSPPTOld'];


  $data[0] = "-";
  $data[1] = "-";
  $cpm_wp_nama = $data[2] = @isset($_REQUEST['name']) ? $_REQUEST['name'] : "Error: Nama Wajib Pajak tidak boleh dikosongkan!";
  $data[3] = @isset($_REQUEST['npwp']) ? $_REQUEST['npwp'] : "Error: NPWP tidak boleh dikosongkan!";
  $data[4] = @isset($_REQUEST['address']) ? $_REQUEST['address'] : "Error: Alamat tidak boleh dikosongkan!";
  $data[5] = "-";
  $data[6] = @isset($_REQUEST['kelurahan']) ? $_REQUEST['kelurahan'] : "Error: Kelurahan tidak boleh dikosongkan!";
  $data[7] = @isset($_REQUEST['rt']) ? $_REQUEST['rt'] : "Error: RT tidak boleh dikosongkan!";
  $data[8] = @isset($_REQUEST['rw']) ? $_REQUEST['rw'] : "Error: RW tidak boleh dikosongkan!";
  $data[9] = @isset($_REQUEST['kecamatan']) ? $_REQUEST['kecamatan'] : "Error: Kecamatan tidak boleh dikosongkan!";
  $data[10] = @isset($_REQUEST['kabupaten']) ? $_REQUEST['kabupaten'] : "Error: Kabupaten tidak boleh dikosongkan!";
  $data[11] = @isset($_REQUEST['zip-code']) ? $_REQUEST['zip-code'] : "Error: Kode POS tidak boleh dikosongkan!";
  $cpm_op_nomor = $data[12] = @isset($_REQUEST['name2']) ? $_REQUEST['name2'] : "Error: NOP PBB tidak boleh dikosongkan!";
  $data[13] = @isset($_REQUEST['address2']) ? $_REQUEST['address2'] : "Error: Alamat Objek Pajak tidak boleh dikosongkan!";
  $data[14] = "-";
  $data[15] = @isset($_REQUEST['kelurahan2']) ? $_REQUEST['kelurahan2'] : "Error: Kelurahan Objek Pajak tidak boleh dikosongkan!";
  $data[16] = @isset($_REQUEST['rt2']) ? $_REQUEST['rt2'] : "Error: RT Objek Pajak tidak boleh dikosongkan!";
  $data[17] = @isset($_REQUEST['rw2']) ? $_REQUEST['rw2'] : "Error: RW Objek Pajak tidak boleh dikosongkan!";
  $data[18] = @isset($_REQUEST['kecamatan2']) ? $_REQUEST['kecamatan2'] : "Error: Kecamatan Objek Pajak tidak boleh dikosongkan!";
  $data[19] = @isset($_REQUEST['kabupaten2']) ? $_REQUEST['kabupaten2'] : "Error: Kabupaten Objek Pajak tidak boleh dikosongkan!";
  $data[20] = @isset($_REQUEST['zip-code2']) ? $_REQUEST['zip-code2'] : "Error: Kode POS Objek Pajak tidak boleh dikosongkan!";
  $data[21] = @isset($_REQUEST['right-year']) ? $_REQUEST['right-year'] : "Error: Tahun SPPT PBB tidak boleh dikosongkan!";
  $data[22] = @isset($_REQUEST['land-area']) ? $_REQUEST['land-area'] : "Error: Luas Tanah tidak boleh dikosongkan!";
  $data[23] = @isset($_REQUEST['land-njop']) ? $_REQUEST['land-njop'] : "Error: NJOP Tanah tidak boleh dikosongkan!";
  $data[24] = @isset($_REQUEST['building-area']) ? $_REQUEST['building-area'] : "Error: Luas Bangunan tidak boleh dikosongkan!";
  $data[25] = @isset($_REQUEST['building-njop']) ? $_REQUEST['building-njop'] : "Error: NJOP Bangunan tidak boleh dikosongkan!";
  $data[26] = @isset($_REQUEST['right-land-build']) ? $_REQUEST['right-land-build'] : "";

  $data[27] = @isset($_REQUEST['trans-value']) ? $_REQUEST['trans-value'] : "Error: Harga transasksi tidak boleh dikosongkan!";
  $data[28] = @isset($_REQUEST['certificate-number']) ? $_REQUEST['certificate-number'] : "Error: Nomor sertifikat tidak boleh dikosongkan!";
  $data[29] = @isset($_REQUEST['hd-npoptkp']) ? $_REQUEST['hd-npoptkp'] : "NPOPTKP"; // UNREMARK BY TAUFIQ
  // $data[29] = "NPOPTKP";
  $data[42] = @isset($_REQUEST['tNJOP']) ? $_REQUEST['tNJOP'] : "0";
  $data[30] = @isset($_REQUEST['RadioGroup1']) ? $_REQUEST['RadioGroup1'] : "Error: Pilihan Jumlah Setoran tidak dipilih!";
  $data[31] = @isset($_REQUEST['jsb-choose']) ? $_REQUEST['jsb-choose'] : "Error: Pilihan jenis tidak dipilih!";
  $data[32] = @isset($_REQUEST['jsb-choose-number']) ? $_REQUEST['jsb-choose-number'] : "Error: Nomor surat tidak boleh dikosongkan!";
  $data[33] = @isset($_REQUEST['jsb-choose-date']) ? $_REQUEST['jsb-choose-date'] : "Error: Tanggal surat tidak boleh dikosongkan!";
  $data[34] = "-"; //$_REQUEST['pdsk-choose']? $_REQUEST['pdsk-choose']:"Error: Pengurangan tidak dipilih!";
  $data[35] = @isset($_REQUEST['jsb-etc']) ? $_REQUEST['jsb-etc'] : "Error: Keterangan lain-lain tidak boleh dikosongkan!";
  $data[36] = @isset($_REQUEST['jsb-total-before']) ? $_REQUEST['jsb-total-before'] : "Error: Akumulasi nilai perolehan hak sebelumnya tidak boleh di kosongkan!";
  $data[37] = @isset($_REQUEST['jsb-choose-role-number']) ? $_REQUEST['jsb-choose-role-number'] : "Error: No Aturan KHD tidak boleh di kosongkan!";
  $data[38] = @isset($_REQUEST['noktp']) ? $_REQUEST['noktp'] : "Error: Nomor KTP tidak boleh dikosongkan!";
  $data[39] = @isset($_REQUEST['jsb-choose-percent']) ? $_REQUEST['jsb-choose-percent'] : "0";
  $data[40] = @isset($_REQUEST['nama-wp-lama']) ? $_REQUEST['nama-wp-lama'] : "Error: Nama WP lama tidak boleh dikosongkan!";
  $data[41] = @isset($_REQUEST['nama-wp-cert']) ? $_REQUEST['nama-wp-cert'] : "Error: Nama WP Sesuai Sertifikat tidak boleh dikosongkan!";
  $data[43] = @isset($_REQUEST['jsb-choose-fraction1']) ? $_REQUEST['jsb-choose-fraction1'] : "1";
  $data[44] = @isset($_REQUEST['jsb-choose-fraction2']) ? $_REQUEST['jsb-choose-fraction2'] : "1";
  $data[45] = @isset($_REQUEST['op-znt']) ? $_REQUEST['op-znt'] : "1";
  $data[46] = @isset($_REQUEST['pengurangan-aphb']) ? $_REQUEST['pengurangan-aphb'] : "1";
  $data[47] = @isset($_REQUEST['Koordinat']) ? $_REQUEST['Koordinat'] : "0";
  $denda = @isset($_REQUEST['denda-value']) ? $_REQUEST['denda-value'] : "0";
  $pdenda = @isset($_REQUEST['denda-percent']) ? $_REQUEST['denda-percent'] : "0";
  $data[48] = @isset($_REQUEST['jenis_hak_milik']) ? $_REQUEST['jenis_hak_milik'] : "";

  $pengenaan = 0;
  if (($_REQUEST['right-land-build'] == 5) || ($_REQUEST['right-land-build'] == 4) || ($_REQUEST['right-land-build'] == 3) || ($_REQUEST['right-land-build'] == 31)) {
    $pengenaan = getConfigValue('PENGENAAN_HIBAH_WARIS');
  }
  $jumlah = getnpoptkp($_REQUEST['noktp']);
  // REMARK BY TAUFIQ
  // if (($data[29] == "0") || ($data[29] == 0)) {
  // 	if (!getNOKTP($_REQUEST['noktp'])) {
  // 		//print_r($_REQUEST['right-land-build']);
  // 		if ($_REQUEST['right-land-build'] == 5) {
  // 			$data[29] = getConfigValue('NPOPTKP_WARIS');
  // 		} else if (($_REQUEST['right-land-build'] == 30) || ($_REQUEST['right-land-build'] == 31)|| ($_REQUEST['right-land-build'] == 32)|| ($_REQUEST['right-land-build'] == 33)) {
  // 			$data[29] = 0;
  // 		}else{
  // 			$data[29] = getConfigValue('NPOPTKP_STANDAR');
  // 		}
  // 	}
  // }

  $pAPHB = "";
  if (($_REQUEST['right-land-build'] == 33) || ($_REQUEST['right-land-build'] == 7)) {
    $pAPHB = $data[46];
  } else {
    $pAPHB = "";
  }
  $typeSurat = '';
  $typeSuratNomor = '';
  $typeSuratTanggal = '';
  $typePengurangan = '';
  $typeLainnya = '';
  $trdate = date("Y-m-d H:i:s");
  $opr = $dat->uname;
  $version = '1.0';
  $nokhd = "";
  $pengurangansplit = explode(".", $data[39]);
  $pengurangan = $pengurangansplit[1];
  $kdpengurangan = $pengurangansplit[0];
  $config_laik_btn = getConfigValue('CONFIG_PASAR_BTN_LOCK');
  if ($data[30] == 2) {
    $typeSurat = $data[31];
    $typeSuratNomor = $data[32];
    $typeSuratTanggal = $data[33];
  } else if ($data[30] == 3) {
    $typePengurangan = $data[34];
    $nokhd = $data[37];
  } else if ($data[30] == 4) {
    $typeLainnya = $data[35];
  } else if ($data[30] == 5) {
    $typePecahan  = $data[43] . "/" . $data[44];
  }

  $ccc = getBPHTBPayment($data[24], $data[25], $data[22], $data[23], $data[27], $pengurangan, $data[26], $data[29], $pengenaan, $pAPHB, $denda);
  if (validation($data, $err)) {

    $iddoc = c_uuid();
    $refnum = c_uuid();
    $tranid = c_uuid();

    // please note %d in the format string, using %s would be meaningless
    $query = sprintf("INSERT INTO cppmod_ssb_doc (
		CPM_SSB_ID,CPM_KPP,
		CPM_KPP_ID,CPM_WP_NAMA,
                CPM_WP_NPWP,CPM_WP_ALAMAT,
                CPM_WP_RT,CPM_WP_RW,
                CPM_WP_KELURAHAN,CPM_WP_KECAMATAN, 
                CPM_WP_KABUPATEN,CPM_WP_KODEPOS, 
		CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
		CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
		CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
		CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT, CPM_OP_NPOP,
		CPM_PAYMENT_TIPE_PECAHAN, CPM_PAYMENT_TYPE_KODE_PENGURANGAN, CPM_OP_ZNT, CPM_PENGENAAN, CPM_CONFIG_LAIK_BTN, CPM_APHB,CPM_DENDA,CPM_PERSEN_DENDA,CPM_BPHTB_BAYAR, CPM_NO_PENDAFTARAN,KOORDINAT, CPM_JENIS_HAK_MILIK) 
		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
		'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", mysqli_real_escape_string($DBLink, $iddoc), '', '', mysqli_real_escape_string($DBLink, $data[2]), mysqli_real_escape_string($DBLink, $data[3]), mysqli_real_escape_string($DBLink, nl2br($data[4])), mysqli_real_escape_string($DBLink, $data[7]), mysqli_real_escape_string($DBLink, $data[8]), mysqli_real_escape_string($DBLink, $data[6]), mysqli_real_escape_string($DBLink, $data[9]), mysqli_real_escape_string($DBLink, $data[10]), mysqli_real_escape_string($DBLink, $data[11]), mysqli_real_escape_string($DBLink, $data[12]), mysqli_real_escape_string($DBLink, nl2br($data[13])), mysqli_real_escape_string($DBLink, $data[16]), mysqli_real_escape_string($DBLink, $data[17]), mysqli_real_escape_string($DBLink, $data[15]), mysqli_real_escape_string($DBLink, $data[18]), mysqli_real_escape_string($DBLink, $data[19]), mysqli_real_escape_string($DBLink, $data[20]), mysqli_real_escape_string($DBLink, $data[21]), mysqli_real_escape_string($DBLink, $data[22]), mysqli_real_escape_string($DBLink, $data[24]), mysqli_real_escape_string($DBLink, $data[23]), mysqli_real_escape_string($DBLink, $data[25]), mysqli_real_escape_string($DBLink, $data[26]), mysqli_real_escape_string($DBLink, $data[27]), mysqli_real_escape_string($DBLink, $data[28]), mysqli_real_escape_string($DBLink, $data[29]), mysqli_real_escape_string($DBLink, $data[30]), mysqli_real_escape_string($DBLink, $typeSurat), mysqli_real_escape_string($DBLink, $typeSuratNomor), mysqli_real_escape_string($DBLink, $typeSuratTanggal), mysqli_real_escape_string($DBLink, $pengurangan), mysqli_real_escape_string($DBLink, $typeLainnya), mysqli_real_escape_string($DBLink, $trdate), mysqli_real_escape_string($DBLink, $opr), mysqli_real_escape_string($DBLink, $version), mysqli_real_escape_string($DBLink, $data[36]), mysqli_real_escape_string($DBLink, $nokhd), mysqli_real_escape_string($DBLink, $data[38]), mysqli_real_escape_string($DBLink, $data[40]), mysqli_real_escape_string($DBLink, $data[41]), mysqli_real_escape_string($DBLink, $data[42]), mysqli_real_escape_string($DBLink, $typePecahan), mysqli_real_escape_string($DBLink, $kdpengurangan), mysqli_real_escape_string($DBLink, $data[45]), $pengenaan, $config_laik_btn, $pAPHB, $denda, $pdenda, $ccc, $no_pendaftaran, $data[47], $data[48]);
    //echo $query;exit;
    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      print_r(mysqli_error($DBLink) . $query);
    }
    if ($final == 2) {
      save_berkas($iddoc);
    }

    $displayDocument = new displayDocument();
    $displayDocument->callInsertToGateway($iddoc, $opr);
    //--------------cppmod_ssb_doc_log
    // please note %d in the format string, using %s would be meaningless
    $query = sprintf(
      "INSERT INTO cppmod_ssb_doc_log (
                CPM_SSB_VERSION,CPM_SSB_AUTHOR,CPM_SSB_CREATED,
                CPM_SSB_LOG_DATE,CPM_SSB_EDITED_BY,CPM_SSB_EDITED_DATE,
                CPM_SSB_ID,CPM_KPP,
                CPM_KPP_ID,CPM_WP_NAMA,
                CPM_WP_NPWP,CPM_WP_ALAMAT,
                CPM_WP_RT,CPM_WP_RW,
                CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,
                CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
                CPM_OP_NOMOR,CPM_OP_LETAK,
                CPM_OP_RT,CPM_OP_RW,
                CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,
                CPM_OP_KABUPATEN,CPM_OP_KODEPOS,
                CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
                CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,
                CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,
                CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,
                CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
                CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,
                CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,
                CPM_PAYMENT_TIPE_OTHER, CPM_PAYMENT_TIPE_PECAHAN,
                CPM_PAYMENT_TYPE_KODE_PENGURANGAN) 
        		VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
        		'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
      mysqli_real_escape_string($DBLink, $version),
      mysqli_real_escape_string($DBLink, $opr),
      mysqli_real_escape_string($DBLink, $trdate),
      mysqli_real_escape_string($DBLink, $trdate),
      mysqli_real_escape_string($DBLink, $opr),
      mysqli_real_escape_string($DBLink, $trdate),
      mysqli_real_escape_string($DBLink, $iddoc),
      '',
      '',
      mysqli_real_escape_string($DBLink, $data[2]),
      mysqli_real_escape_string($DBLink, $data[3]),
      mysqli_real_escape_string($DBLink, nl2br($data[4])),
      mysqli_real_escape_string($DBLink, $data[7]),
      mysqli_real_escape_string($DBLink, $data[8]),
      mysqli_real_escape_string($DBLink, $data[6]),
      mysqli_real_escape_string($DBLink, $data[9]),
      mysqli_real_escape_string($DBLink, $data[10]),
      mysqli_real_escape_string($DBLink, $data[11]),
      mysqli_real_escape_string($DBLink, $data[12]),
      mysqli_real_escape_string($DBLink, nl2br($data[13])),
      mysqli_real_escape_string($DBLink, $data[16]),
      mysqli_real_escape_string($DBLink, $data[17]),
      mysqli_real_escape_string($DBLink, $data[15]),
      mysqli_real_escape_string($DBLink, $data[18]),
      mysqli_real_escape_string($DBLink, $data[19]),
      mysqli_real_escape_string($DBLink, $data[20]),
      mysqli_real_escape_string($DBLink, $data[21]),
      mysqli_real_escape_string($DBLink, $data[22]),
      mysqli_real_escape_string($DBLink, $data[24]),
      mysqli_real_escape_string($DBLink, $data[23]),
      mysqli_real_escape_string($DBLink, $data[25]),
      mysqli_real_escape_string($DBLink, $data[26]),
      mysqli_real_escape_string($DBLink, $data[27]),
      mysqli_real_escape_string($DBLink, $data[28]),
      mysqli_real_escape_string($DBLink, $data[29]),
      mysqli_real_escape_string($DBLink, $data[30]),
      mysqli_real_escape_string($DBLink, $typeSurat),
      mysqli_real_escape_string($DBLink, $typeSuratNomor),
      mysqli_real_escape_string($DBLink, $typeSuratTanggal),
      mysqli_real_escape_string($DBLink, $pengurangan),
      mysqli_real_escape_string($DBLink, $typeLainnya),
      mysqli_real_escape_string($DBLink, $typePecahan),
      mysqli_real_escape_string($DBLink, $kdpengurangan)
    );


    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      print_r(mysqli_error($DBLink) . $query);
    }

    //-------------- cpp_mod_ssb_to_pbb
    // please note %d in the format string, using %s would be meaningless
    $query = "INSERT INTO cpp_mod_ssb_to_pbb VALUES ('" . mysqli_real_escape_string($DBLink, $iddoc) . "','" . mysqli_real_escape_string($DBLink, $data[12]) . "',0);";

    #echo $query;exit;
    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      print_r(mysqli_error($DBLink) . $query);
    }


    //------------- CPPMOD_SSB_OLD
    if ($isPBB == 1) {
      // please note %d in the format string, using %s would be meaningless
      $query = sprintf(
        "INSERT INTO cppmod_ssb_doc_OLD (
                    CPM_SSB_ID,CPM_KPP,
                    CPM_KPP_ID,CPM_WP_NAMA,
                    CPM_WP_NPWP,CPM_WP_ALAMAT,
                    CPM_WP_RT,CPM_WP_RW,
                    CPM_WP_KELURAHAN,CPM_WP_KECAMATAN,
                    CPM_WP_KABUPATEN,CPM_WP_KODEPOS,
                    CPM_OP_NOMOR,CPM_OP_LETAK,CPM_OP_RT,CPM_OP_RW,CPM_OP_KELURAHAN,CPM_OP_KECAMATAN,CPM_OP_KABUPATEN,CPM_OP_KODEPOS,CPM_OP_THN_PEROLEH,CPM_OP_LUAS_TANAH,
                    CPM_OP_LUAS_BANGUN,CPM_OP_NJOP_TANAH,CPM_OP_NJOP_BANGUN,CPM_OP_JENIS_HAK,CPM_OP_HARGA,CPM_OP_NMR_SERTIFIKAT,CPM_OP_NPOPTKP,CPM_PAYMENT_TIPE,
                    CPM_PAYMENT_TIPE_SURAT,CPM_PAYMENT_TIPE_SURAT_NOMOR,CPM_PAYMENT_TIPE_SURAT_TANGGAL,CPM_PAYMENT_TIPE_PENGURANGAN,CPM_PAYMENT_TIPE_OTHER,CPM_SSB_CREATED,
                    CPM_SSB_AUTHOR,CPM_SSB_VERSION,CPM_SSB_AKUMULASI,CPM_PAYMENT_TIPE_KHD_NOMOR,CPM_WP_NOKTP,CPM_WP_NAMA_LAMA,CPM_WP_NAMA_CERT,CPM_OP_BPHTB_TU, CPM_PAYMENT_TIPE_PECAHAN, CPM_PAYMENT_TYPE_KODE_PENGURANGAN) 
                    VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
                    '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
        mysqli_real_escape_string($DBLink, $iddoc),
        '',
        '',
        mysqli_real_escape_string($DBLink, $dataOld[0]),
        mysqli_real_escape_string($DBLink, $dataOld[1]),
        mysqli_real_escape_string($DBLink, nl2br($dataOld[3])),
        mysqli_real_escape_string($DBLink, $dataOld[5]),
        mysqli_real_escape_string($DBLink, $dataOld[6]),
        mysqli_real_escape_string($DBLink, $dataOld[4]),
        mysqli_real_escape_string($DBLink, $dataOld[7]),
        mysqli_real_escape_string($DBLink, $dataOld[8]),
        mysqli_real_escape_string($DBLink, $dataOld[9]),
        mysqli_real_escape_string($DBLink, $dataOld[10]),
        mysqli_real_escape_string($DBLink, nl2br($dataOld[11])),
        mysqli_real_escape_string($DBLink, $dataOld[13]),
        mysqli_real_escape_string($DBLink, $dataOld[14]),
        mysqli_real_escape_string($DBLink, $dataOld[12]),
        mysqli_real_escape_string($DBLink, $dataOld[15]),
        mysqli_real_escape_string($DBLink, $dataOld[16]),
        mysqli_real_escape_string($DBLink, $dataOld[17]),
        mysqli_real_escape_string($DBLink, $dataOld[22]),
        mysqli_real_escape_string($DBLink, $dataOld[18]),
        mysqli_real_escape_string($DBLink, $dataOld[20]),
        mysqli_real_escape_string($DBLink, $dataOld[19]),
        mysqli_real_escape_string($DBLink, $dataOld[21]),
        mysqli_real_escape_string($DBLink, $data[26]),
        mysqli_real_escape_string($DBLink, $data[27]),
        mysqli_real_escape_string($DBLink, $data[28]),
        mysqli_real_escape_string($DBLink, $data[29]),
        mysqli_real_escape_string($DBLink, $data[30]),
        mysqli_real_escape_string($DBLink, $typeSurat),
        mysqli_real_escape_string($DBLink, $typeSuratNomor),
        mysqli_real_escape_string($DBLink, $typeSuratTanggal),
        mysqli_real_escape_string($DBLink, $pengurangan),
        mysqli_real_escape_string($DBLink, $typeLainnya),
        mysqli_real_escape_string($DBLink, $trdate),
        mysqli_real_escape_string($DBLink, $opr),
        mysqli_real_escape_string($DBLink, $version),
        mysqli_real_escape_string($DBLink, $data[36]),
        mysqli_real_escape_string($DBLink, $nokhd),
        mysqli_real_escape_string($DBLink, $dataOld[2]),
        mysqli_real_escape_string($DBLink, $data[40]),
        mysqli_real_escape_string($DBLink, $data[41]),
        mysqli_real_escape_string($DBLink, $bphtb_terutang),
        mysqli_real_escape_string($DBLink, $typePecahan),
        mysqli_real_escape_string($DBLink, $kdpengurangan)
      );

      #echo $query;exit;
      $result = mysqli_query($DBLink, $query);
      if ($result === false) {
        //handle the error here
        print_r(mysqli_error($DBLink) . $query);
      }
    }

    $query = "INSERT INTO cppmod_ssb_tranmain (CPM_TRAN_ID,CPM_TRAN_REFNUM,CPM_TRAN_SSB_ID,CPM_TRAN_SSB_VERSION,CPM_TRAN_STATUS,CPM_TRAN_FLAG,CPM_TRAN_DATE,CPM_TRAN_CLAIM,
		CPM_TRAN_OPR_NOTARIS) VALUES
		 ('" . $tranid . "','" . $refnum . "','" . $iddoc . "','" . $version . "','" . $final . "','0','" . $trdate . "','0','" . $opr . "')";

    $result = mysqli_query($DBLink, $query);
    if ($result === false) {
      //handle the error here
      echo mysqli_error($DBLink);
    } else {
      $idocv2 = $iddoc;
      $CPM_SSB_ID = mysqli_real_escape_string($DBLink, $iddoc);
      $log_input = "insert into cppmod_ssb_log(
                                    CPM_SSB_ID,
                                    CPM_SSB_LOG_ACTOR,
                                    CPM_SSB_LOG_ACTION,
                                    CPM_OP_NOMOR,
                                    CPM_WP_NAMA,
                                    CPM_SSB_AUTHOR) 
                            values ('" . $CPM_SSB_ID . "',
                                    '" . mysqli_real_escape_string($DBLink, $opr) . "',                                   
                                    '" . mysqli_real_escape_string($DBLink, $final) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_op_nomor) . "',
                                    '" . mysqli_real_escape_string($DBLink, $cpm_wp_nama) . "',
                                    '" . mysqli_real_escape_string($DBLink, $opr) . "')";
      mysqli_query($DBLink, $log_input);

      echo "Data Berhasil disimpan ...! ";
      // ADDING BY TAUFIQ
      $getdt = "select * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID='" . $CPM_SSB_ID . "'";
      // die(mysqli_real_escape_string($DBLink, $iddoc));

      $resultw = mysqli_query($DBLink, $getdt);
      if ($resultw === false)
        echo mysqli_error('error 1');
      $rows = mysqli_fetch_array($resultw);
      $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
      // $params = "a=" . $_REQUEST['a'] . "&m=mUploadBerkas&f=fUploadBerkas&tab=0&svcid=".$rows['CPM_BERKAS_ID'];
      $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
      echo "\n<script language=\"javascript\">\n";

      echo "	function delayer(){\n";
      echo "  hideMask();";
      echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
      echo "	}\n";
      echo "	Ext.onReady(function(){\n";
      echo "		setTimeout('delayer()', 2000);\n";
      echo "	});\n";
      echo "</script>\n";
    }
  } else {
    echo $err;
    echo formSSB($data);
  }
}

$save = $_REQUEST['btn-save'];
$nop = $_REQUEST['name2'];
if ($save == 'Simpan') {
  if (cek_sw_allow_input($nop) == true || cek_sw_allow_input($nop) == false) {
    if (cek_gw_allow_input($nop) == true || cek_gw_allow_input($nop) == false) {
      save(1);
    } else {
      echo "Maaf data anda tidak bisa ditindaklanjuti, karena data sebelumnya belum dibayar!";
    }
  } else {
    echo "Maaf data anda tidak bisa ditindaklanjuti, karena data sudah masuk dan sedang diproses!";
  }


  // ADDING BY TAUFIQ
  $encpt = mysqli_real_escape_string($DBLink, $idocv2);
  die($idocv2);
  $getdt = "select * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID='" . $encpt . "'";
  // die(mysqli_real_escape_string($DBLink, $iddoc));

  $resultw = mysqli_query($DBLink, $getdt);
  if ($resultw === false)
    echo mysqli_error('error 1');
  $rows = mysqli_fetch_array($resultw);
  // $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
  $params = "a=" . $_REQUEST['a'] . "&m=mUploadBerkas&f=fUploadBerkas&tab=0&svcid=" . $rows['CPM_BERKAS_ID'];
  $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
  echo "\n<script language=\"javascript\">\n";

  echo "	function delayer(){\n";
  echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
  echo "	}\n";
  echo "	Ext.onReady(function(){\n";
  echo "		setTimeout('delayer()', 3500);\n";
  echo "	});\n";
  echo "</script>\n";
} else if ($save == 'Simpan dan Finalkan') {
  if (cek_sw_allow_input($nop) == true || cek_sw_allow_input($nop) == false) {
    if (cek_gw_allow_input($nop) == true || cek_gw_allow_input($nop) == false) {
      if (getConfigValue('VERIFIKASI') != '1') {
        save(3);
      } else {
        save(2);
      }
    } else {
      echo "Maaf data anda tidak bisa ditindaklanjuti, karena data sudah terdaftar dan sudah melakukan pembayaran";
    }
  } else {
    echo "Maaf data anda tidak bisa ditindaklanjuti, karena sudah ditransaksikan sebelumnya. <br>Silahkan hubungi Administrator (DPPKAD)!";
  }

  // ADDING BY TAUFIQ
  $encpt = mysqli_real_escape_string($DBLink, $idocv2);
  // die($idocv2);
  $getdt = "select * FROM cppmod_ssb_berkas WHERE CPM_SSB_DOC_ID='" . $encpt . "'";
  // die(mysqli_real_escape_string($DBLink, $iddoc));

  $resultw = mysqli_query($DBLink, $getdt);
  if ($resultw === false)
    echo mysqli_error('error 1');
  $rows = mysqli_fetch_array($resultw);
  // $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&n=4";
  $params = "a=" . $_REQUEST['a'] . "&m=mUploadBerkas&f=fUploadBerkas&tab=0&svcid=" . $rows['CPM_BERKAS_ID'];
  $address = $_SERVER['HTTP_HOST'] . "payment/pc/svr/central/main.php?param=" . base64_encode($params);
  echo "\n<script language=\"javascript\">\n";
  echo "	function delayer(){\n";
  echo "		window.location = \"./main.php?param=" . base64_encode($params) . "\"\n";
  echo "	}\n";
  echo "	Ext.onReady(function(){\n";
  echo "		setTimeout('delayer()', 5000);\n";
  echo "	});\n";
  echo "</script>\n";
} else {
  echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
  echo formSSB();
}
?>

<script>
  var d = new Date();
  var n = d.getFullYear();
  $("#right-year").val(n);

  function checkKTP() {
    var noktp = document.getElementById('noktp').value;

    // Kirim nomor KTP ke server
    // Ganti URL_SERVER dengan URL backend Anda
    var url = 'function/BPHTB/notaris/func-cekNOKTP.php?noktp=' + noktp;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);

    xhr.onload = function() {
      if (xhr.status === 200) {
        // Data KTP diterima dari server
        var data = JSON.parse(xhr.responseText);
        if (data.success) {
          // Mengisi formulir input dengan data KTP
          document.getElementById('name').value = data.name || '';
          document.getElementById('npwp').value = data.npwp || '';
          document.getElementById('address').value = data.address || '';
          document.getElementById('kecamatan').value = data.kecamatan || '';
          document.getElementById('rt').value = data.rt || '';
          document.getElementById('rw').value = data.rw || '';
          document.getElementById('kelurahan').value = data.kelurahan || '';
          document.getElementById('kabupaten').value = data.kabupaten || '';
          document.getElementById('zip-code').value = data.kodepos || '';
        } else {
          alert('Data KTP tidak ditemukan.');
          document.getElementById('name').value = '';
          document.getElementById('npwp').value = '';
          document.getElementById('address').value = '';
          document.getElementById('kecamatan').value = '';
          document.getElementById('rt').value = '';
          document.getElementById('rw').value = '';
          document.getElementById('kelurahan').value = '';
          document.getElementById('kabupaten').value = '';
          document.getElementById('zip-code').value = '';
        }
      } else {
        alert('Terjadi kesalahan saat menghubungi server.');
      }
    };

    xhr.send();
  }
</script>