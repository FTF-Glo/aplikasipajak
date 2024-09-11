<?php

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'BPHTB' . DIRECTORY_SEPARATOR . 'uploadberkas', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/json.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery.ui.button.js\"></script>";

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"function/BPHTB/berkas/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"function/BPHTB/berkas/func-mod-pelayanan.css\" type=\"text/css\">\n";
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

function getConfigValue($id, $key) {
    global $DBLink;
    $qry = "select * from central_app_config where CTR_AC_AID = '" . $id . "' and CTR_AC_KEY = '$key'";
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";echo "<link rel=\"stylesheet\" href=\"function/BPHTB/berkas/func-mod-pelayanan.css\" type=\"text/css\">\n";
        echo mysqli_error($DBLink);
    }
    while ($row = mysqli_fetch_assoc($res)) {
        return $row['CTR_AC_VALUE'];
    }
}
function jenishak($js){
	global $DBLink;
	
	$texthtml= "<select name=\"jnsPerolehan\" style=\"width:250px;\" id=\"jnsper\" onchange='cleancheckbox();javascript:showJnsPerolehan(this);'>";
	$qry = "select * from cppmod_ssb_jenis_hak ORDER BY CPM_KD_JENIS_HAK asc";
					//echo $qry;exit;
					$res = mysqli_query($DBLink, $qry);
					$texthtml .="<option value=\"0\" />Pilih Jenis Hak</option>";
						while($data = mysqli_fetch_assoc($res)){
							if(($js!=$data['CPM_KD_JENIS_HAK'])||($js=="")){
								$selected= "";
							}else{
								$selected= "selected";
							}
							$texthtml .= "<option value=\"".$data['CPM_KD_JENIS_HAK']."\" ".$selected." >".str_pad($data['CPM_KD_JENIS_HAK'],2,"0",STR_PAD_LEFT)." ".$data['CPM_JENIS_HAK']."</option>";
						}
$texthtml .="			      </select>";
return $texthtml;
	
}

function getberkas($no,$ssb_id,$berkas){
	global $DBLink;
	$thn_berkas=explode(".",$berkas);
	
	$qry = "select * from cppmod_ssb_upload_file where CPM_SSB_ID = '" . $ssb_id . "' and CPM_KODE_LAMPIRAN = '".$no."'";
	//echo $qry; exit;
    $res = mysqli_query($DBLink, $qry);
    if ($res === false) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    }
	$row=mysqli_num_rows($res);
	if($row>=1){
		while ($row = mysqli_fetch_assoc($res)) {
			  $berkas="<a href ='function/BPHTB/uploadberkas/berkas/{$thn_berkas[0]}/{$berkas}/{$row['CPM_FILE_NAME']}' target='_blank'>Download/view</a>";
		}
	}else{
		$berkas="";
	}
	return $berkas;
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
	$lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";
    $lampiran[] = "";

    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
    $jnsPerolehan[] = "";
	$jnsPerolehan[] = "";

    $strJnsPerolehan = "";
	
	$jnsPerolehan =jenishak("");
    $value['CPM_BERKAS_NOPEL'] = "";
    if (isset($_REQUEST['svcid'])) {
        $query = "select * from cppmod_ssb_berkas where CPM_BERKAS_ID = '{$_REQUEST['svcid']}'";
        $result = mysqli_query($DBLink, $query);
        $value = mysqli_fetch_array($result);
		$jnsPerolehan = jenishak($value['CPM_BERKAS_JNS_PEROLEHAN']);
       
		
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
        $lampiran[10]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "11") !== false) ? "checked" : "";
        $lampiran[11]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "12") !== false) ? "checked" : "";
		$lampiran[12]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "13") !== false) ? "checked" : "";
        $lampiran[13]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "14") !== false) ? "checked" : "";
		$lampiran[14]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "15") !== false) ? "checked" : "";
        $lampiran[15]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "16") !== false) ? "checked" : "";
		$lampiran[16]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "17") !== false) ? "checked" : "";
        $lampiran[17]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "18") !== false) ? "checked" : "";
		$lampiran[18]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "19") !== false) ? "checked" : "";
        $lampiran[19]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "20") !== false) ? "checked" : "";
        $lampiran[20]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "21") !== false) ? "checked" : "";
        $lampiran[21]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "22") !== false) ? "checked" : "";
        $lampiran[22]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "23") !== false) ? "checked" : "";
        $lampiran[23]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "24") !== false) ? "checked" : "";
        $lampiran[24]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "25") !== false) ? "checked" : "";
        $lampiran[25]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "26") !== false) ? "checked" : "";
        $lampiran[26]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "27") !== false) ? "checked" : "";
        $lampiran[27]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "28") !== false) ? "checked" : "";
        $lampiran[28]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "29") !== false) ? "checked" : "";
		//tambahan upload
		$lampiran[30]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "30") !== false) ? "checked" : "";
        $lampiran[31]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "31") !== false) ? "checked" : "";
        $lampiran[32]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "32") !== false) ? "checked" : "";
        $lampiran[33]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "33") !== false) ? "checked" : "";
		$lampiran[34]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "34") !== false) ? "checked" : "";
		$lampiran[35]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "35") !== false) ? "checked" : "";
		$lampiran[36]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "36") !== false) ? "checked" : "";
		$lampiran[37]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "37") !== false) ? "checked" : "";
		
		$lampiran[38]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "38") !== false) ? "checked" : "";
		$lampiran[39]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "39") !== false) ? "checked" : "";
		$lampiran[40]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "40") !== false) ? "checked" : "";
		$lampiran[41]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "41") !== false) ? "checked" : "";
		$lampiran[42]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "42") !== false) ? "checked" : "";
		$lampiran[43]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "43") !== false) ? "checked" : "";
		$lampiran[44]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "44") !== false) ? "checked" : "";
		
		$lampiran[45]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "45") !== false) ? "checked" : "";
		$lampiran[46]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "46") !== false) ? "checked" : "";
		$lampiran[47]= (strpos($value['CPM_BERKAS_LAMPIRAN'], "47") !== false) ? "checked" : "";

    }
	
	$berkas_lamp1=getberkas(1,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp2=getberkas(2,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp3=getberkas(3,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp4=getberkas(4,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp5=getberkas(5,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp6=getberkas(6,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp7=getberkas(7,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp8=getberkas(8,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp9=getberkas(9,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp10=getberkas(10,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp11=getberkas(11,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp12=getberkas(12,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp13=getberkas(13,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp14=getberkas(14,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp15=getberkas(15,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp16=getberkas(16,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp17=getberkas(17,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp18=getberkas(18,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp19=getberkas(19,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp20=getberkas(20,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp21=getberkas(21,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp22=getberkas(22,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp23=getberkas(23,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp24=getberkas(24,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp25=getberkas(25,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp26=getberkas(26,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp27=getberkas(27,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp28=getberkas(28,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	//tambahan upload
	$berkas_lamp30=getberkas(30,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp31=getberkas(31,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp32=getberkas(32,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp33=getberkas(33,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp34=getberkas(34,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp35=getberkas(35,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp36=getberkas(36,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp37=getberkas(37,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);

	$berkas_lamp38=getberkas(38,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp39=getberkas(39,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp40=getberkas(40,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp41=getberkas(41,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp42=getberkas(42,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp43=getberkas(43,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp44=getberkas(44,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);

	$berkas_lamp45=getberkas(45,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp46=getberkas(46,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);
	$berkas_lamp47=getberkas(47,$value['CPM_SSB_DOC_ID'],$value['CPM_BERKAS_NOPEL']);

    $html = "
    <style>
    #main-content {
        width: 900px;
    }
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }
	.fileContainer {
			overflow: hidden;
			position: relative;
		}

		.fileContainer [type=file] {
			cursor: inherit;
			display: block;
			font-size: 999px;
			filter: alpha(opacity=0);
			min-height: 100%;
			min-width: 100%;
			opacity: 0;
			position: absolute;
			right: 0;
			text-align: right;
			top: 0;
		}
		
    </style>
			
    <script language=\"javascript\">
        jQuery.validator.setDefaults({
            debug: true,
            success: \"valid\"
          });
         $(document).ready(function(){
			 $('#jnsper').attr('disabled','disabled');
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
                         \"noPel\" : \"required\",
                         \"telpWp\" : \"required\"
                         
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
                         \"noPel\" : \"harus diisi\",
                         \"telpWp\" : \"harus diisi\"
                    }
             });
             
            $(\"#btn-simpan\").click(function(){
				var id_bks=document.getElementById(\"idssb\").value;
                $(\"#process\").val($(this).val());
                if(form.valid()){

                   document.getElementById(\"form-penerimaan\").submit();                   
                }
            });
            
            $(\".jnsPerolehan\").hide();
            disabledJnsPerolehan();
            enabledJnsPerolehan('#jnsPerolehan" . $strJnsPerolehan . "')
                

            $( \"#nop\" ).focusout(function()
		{
                var val = $.trim($(\"#nop\").val());
                showDialog('Load', '<img src=\"image/icon/loading.gif\" width=\"32\" height=\"32\" style=\"margin-right:8px;\" align=\"absmiddle\"/>Tunggu', 'prompt', false, true);
                    $.ajax({
                        type: \"post\",
                        data: \"nop=\" + val,
                        url: \"./function/BPHTB/berkas/svc-check-nop.php\",
                        dataType: \"json\",
                        success: function(res) {

                            if (res.message)
                                alert(res.message);

                            $('#errnop').remove();
                            if (res.denied)
                                if (res.denied == 1) {


                                    var errnop = $(\"<span id='errnop'><br>\" + res.message + \"</span>\").css({'color': '#FF0000'});

                            }
                            

                            hideDialog();           
                        },
                        error: hideDialog() , //function(res){ hideMask();console.log(res) },
                        failure: hideDialog() //function(res){ hideMask();console.log(res) }
                    });

                                });
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
		function checkfile(){
					
				var id_bks=document.getElementById(\"idssb\").value;
                $.ajax({
                        url: \"./function/BPHTB/uploadberkas/svc-cek-kelberkas.php\",
                        method: \"post\",
                        data: {id_bks:id_bks},
                        success: function(msg)
                        {                        
                            if(msg==\"1\"){
                                return true;
                            }else{
                                return true;
                            }
                        }
                    });  
            }
		function cleancheckbox(){
					var b1=document.getElementById(\"lamp1\");				var b77=document.getElementById(\"lamp77\");
					var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
					var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
					var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
					var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
					var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
					var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
					var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
					var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
					var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
					var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
					var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
					var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
					var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
					var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
					var b16=document.getElementById(\"lamp16\");            var b92=document.getElementById(\"lamp92\");
					var b17=document.getElementById(\"lamp17\");            var b93=document.getElementById(\"lamp93\");
					var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
					var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
					var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
					var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
					var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
					var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
					var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
					var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
					var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
					var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
					var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
					var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
					var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
					var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
					var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
					var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
					var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
					var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
					var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
					var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
					var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
					var b39=document.getElementById(\"lamp39\");			var b115=document.getElementById(\"lamp115\");
					var b40=document.getElementById(\"lamp40\");		    var b116=document.getElementById(\"lamp116\");
					var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
					var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
					var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
					var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
					var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
					var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
					var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
					var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
					var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
					var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
					var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
					var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
					var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
					var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
					var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
					var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
					var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
					var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
					var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
					var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
					var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
					var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
					var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
					var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
					var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
					var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
					var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
					var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
					var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
					var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
					var b71=document.getElementById(\"lamp71\");            var b147=document.getElementById(\"lamp147\");
					var b72=document.getElementById(\"lamp72\");            var b148=document.getElementById(\"lamp148\");
					var b73=document.getElementById(\"lamp73\");            var b149=document.getElementById(\"lamp149\");
					var b74=document.getElementById(\"lamp74\");            var b150=document.getElementById(\"lamp150\");
					var b75=document.getElementById(\"lamp75\");            var b151=document.getElementById(\"lamp151\");
					var b76=document.getElementById(\"lamp76\");			var b152=document.getElementById(\"lamp152\");
					var b153=document.getElementById(\"lamp153\");
					var b154=document.getElementById(\"lamp154\");
					var b155=document.getElementById(\"lamp155\");
					
					var b156=document.getElementById(\"lamp156\");
					var b157=document.getElementById(\"lamp157\");
					var b158=document.getElementById(\"lamp158\");
					var b159=document.getElementById(\"lamp159\");
					var b160=document.getElementById(\"lamp160\");
					var b161=document.getElementById(\"lamp161\");
					var b162=document.getElementById(\"lamp162\");
					
					var b163=document.getElementById(\"lamp163\");
					var b164=document.getElementById(\"lamp164\");
					var b165=document.getElementById(\"lamp165\");
					var b166=document.getElementById(\"lamp166\");
					var b167=document.getElementById(\"lamp167\");
					var b168=document.getElementById(\"lamp168\");
					var b169=document.getElementById(\"lamp169\");
					
					var b170=document.getElementById(\"lamp170\");
					var b171=document.getElementById(\"lamp171\");
					var b172=document.getElementById(\"lamp172\");
					var b173=document.getElementById(\"lamp173\");
					var b174=document.getElementById(\"lamp174\");
					var b175=document.getElementById(\"lamp175\");
					var b176=document.getElementById(\"lamp176\");
					
					var b177=document.getElementById(\"lamp177\");
					var b178=document.getElementById(\"lamp178\");
					var b179=document.getElementById(\"lamp179\");
					var b180=document.getElementById(\"lamp180\");
					var b181=document.getElementById(\"lamp181\");
					var b182=document.getElementById(\"lamp182\");
					var b183=document.getElementById(\"lamp183\");
					
					var b184=document.getElementById(\"lamp184\");
					var b185=document.getElementById(\"lamp185\");
					var b186=document.getElementById(\"lamp186\");
					var b187=document.getElementById(\"lamp187\");
					var b188=document.getElementById(\"lamp188\");
					var b189=document.getElementById(\"lamp189\");
					var b190=document.getElementById(\"lamp190\");
					
					var b191=document.getElementById(\"lamp191\");
					var b192=document.getElementById(\"lamp192\");
					var b193=document.getElementById(\"lamp193\");
					var b194=document.getElementById(\"lamp194\");
					var b195=document.getElementById(\"lamp195\");
					var b196=document.getElementById(\"lamp196\");
					var b197=document.getElementById(\"lamp197\");
					
					var b198=document.getElementById(\"lamp198\");
					var b199=document.getElementById(\"lamp199\");
					var b200=document.getElementById(\"lamp200\");
					var b201=document.getElementById(\"lamp201\");
					var b202=document.getElementById(\"lamp202\");
					var b203=document.getElementById(\"lamp203\");
					var b204=document.getElementById(\"lamp204\");
					var b205=document.getElementById(\"lamp205\");
					var b206=document.getElementById(\"lamp206\");
					var b207=document.getElementById(\"lamp207\");
					var b208=document.getElementById(\"lamp208\");
					var b209=document.getElementById(\"lamp209\");
					var b210=document.getElementById(\"lamp210\");
					var b211=document.getElementById(\"lamp211\");
					var b212=document.getElementById(\"lamp212\");
					
					b1.checked=false;			  b77.checked=false;
					b2.checked=false;             b78.checked=false;
					b3.checked=false;             b79.checked=false;
					b4.checked=false;             b80.checked=false;
					b5.checked=false;             b81.checked=false;
					b6.checked=false;             b82.checked=false;
					b7.checked=false;             b83.checked=false;
					b8.checked=false;             b84.checked=false;
					b9.checked=false;             b85.checked=false;
					b10.checked=false;            b86.checked=false;
					b11.checked=false;            b87.checked=false;
					b12.checked=false;            b88.checked=false;
					b13.checked=false;            b89.checked=false;
					b14.checked=false;            b90.checked=false;
					b15.checked=false;            b91.checked=false;
					b16.checked=false;            b92.checked=false;
					b17.checked=false;            b93.checked=false;
					b18.checked=false;            b94.checked=false;
					b19.checked=false;            b95.checked=false;
					b20.checked=false;            b96.checked=false;
					b21.checked=false;            b97.checked=false;
					b22.checked=false;            b98.checked=false;
					b23.checked=false;            b99.checked=false;
					b24.checked=false;            b100.checked=false;
					b25.checked=false;            b101.checked=false;
					b26.checked=false;            b102.checked=false;
					b27.checked=false;            b103.checked=false;
					b28.checked=false;            b104.checked=false;
					b29.checked=false;            b105.checked=false;
					b30.checked=false;            b106.checked=false;
					b31.checked=false;            b107.checked=false;
					b32.checked=false;            b108.checked=false;
					b33.checked=false;            b109.checked=false;
					b34.checked=false;            b110.checked=false;
					b35.checked=false;            b111.checked=false;
					b36.checked=false;            b112.checked=false;
					b37.checked=false;            b113.checked=false;
					b38.checked=false;            b114.checked=false;
					b39.checked=false;			  b115.checked=false;
					b40.checked=false;		      b116.checked=false;
					b41.checked=false;            b117.checked=false;
					b42.checked=false;            b118.checked=false;
					b43.checked=false;            b119.checked=false;
					b44.checked=false;            b120.checked=false;
					b45.checked=false;            b121.checked=false;
					b46.checked=false;            b122.checked=false;
					b47.checked=false;            b123.checked=false;
					b48.checked=false;            b124.checked=false;
					b49.checked=false;            b125.checked=false;
					b50.checked=false;            b126.checked=false;
					b51.checked=false;            b127.checked=false;
					b52.checked=false;            b128.checked=false;
					b53.checked=false;            b129.checked=false;
					b54.checked=false;            b130.checked=false;
					b55.checked=false;            b131.checked=false;
					b56.checked=false;            b132.checked=false;
					b57.checked=false;            b133.checked=false;
					b58.checked=false;            b134.checked=false;
					b59.checked=false;            b135.checked=false;
					b60.checked=false;            b136.checked=false;
					b61.checked=false;            b137.checked=false;
					b62.checked=false;            b138.checked=false;
					b63.checked=false;            b139.checked=false;
					b64.checked=false;            b140.checked=false;
					b65.checked=false;            b141.checked=false;
					b66.checked=false;            b142.checked=false;
					b67.checked=false;            b143.checked=false;
					b68.checked=false;            b144.checked=false;
					b69.checked=false;            b145.checked=false;
					b70.checked=false;            b146.checked=false;
					b71.checked=false;            b147.checked=false;
					b72.checked=false;            b148.checked=false;
					b73.checked=false;            b149.checked=false;
					b74.checked=false;            b150.checked=false;
					b75.checked=false;            b151.checked=false;
					b76.checked=false;			  b152.checked=false;
					b153.checked=false;
					b154.checked=false;
					b155.checked=false;
					
					b156.checked=false;
					b157.checked=false;
					b158.checked=false;
					b159.checked=false;
					b160.checked=false;
					b161.checked=false;
					b162.checked=false;
					
					b163.checked=false;
					b164.checked=false;
					b165.checked=false;
					b166.checked=false;
					b167.checked=false;
					b168.checked=false;
					b169.checked=false;
					
					b170.checked=false;
					b171.checked=false;
					b172.checked=false;
					b173.checked=false;
					b174.checked=false;
					b175.checked=false;
					b176.checked=false;
					
					b177.checked=false;
					b178.checked=false;
					b179.checked=false;
					b180.checked=false;
					b181.checked=false;
					b182.checked=false;
					b183.checked=false;
					
					b184.checked=false;
					b185.checked=false;
					b186.checked=false;
					b187.checked=false;
					b188.checked=false;
					b189.checked=false;
					b190.checked=false;
					
					b191.checked=false;
					b192.checked=false;
					b193.checked=false;
					b194.checked=false;
					b195.checked=false;
					b196.checked=false;
					b197.checked=false;
					
					b198.checked=false;
					b199.checked=false;
					b200.checked=false;
					b201.checked=false;
					b202.checked=false;
					b203.checked=false;
					b204.checked=false;
					b205.checked=false;
					b206.checked=false;
					b207.checked=false;
					b208.checked=false;
					b209.checked=false;
					b210.checked=false;
					b211.checked=false;
					b212.checked=false;
		}           
		function validateCheckBoxes() 
				{
					// for(var i=1;i<=146;i++){
					// var b[i]=document.getElementById('lamp'+i);
					// eval( 'var b'+i+' = document.getElementById(\"lamp' + i +'\");' );
					// }
					var b1=document.getElementById(\"lamp1\");				var b77=document.getElementById(\"lamp77\");
					var b2=document.getElementById(\"lamp2\");              var b78=document.getElementById(\"lamp78\");
					var b3=document.getElementById(\"lamp3\");              var b79=document.getElementById(\"lamp79\");
					var b4=document.getElementById(\"lamp4\");              var b80=document.getElementById(\"lamp80\");
					var b5=document.getElementById(\"lamp5\");              var b81=document.getElementById(\"lamp81\");
					var b6=document.getElementById(\"lamp6\");              var b82=document.getElementById(\"lamp82\");
					var b7=document.getElementById(\"lamp7\");              var b83=document.getElementById(\"lamp83\");
					var b8=document.getElementById(\"lamp8\");              var b84=document.getElementById(\"lamp84\");
					var b9=document.getElementById(\"lamp9\");              var b85=document.getElementById(\"lamp85\");
					var b10=document.getElementById(\"lamp10\");            var b86=document.getElementById(\"lamp86\");
					var b11=document.getElementById(\"lamp11\");            var b87=document.getElementById(\"lamp87\");
					var b12=document.getElementById(\"lamp12\");            var b88=document.getElementById(\"lamp88\");
					var b13=document.getElementById(\"lamp13\");            var b89=document.getElementById(\"lamp89\");
					var b14=document.getElementById(\"lamp14\");            var b90=document.getElementById(\"lamp90\");
					var b15=document.getElementById(\"lamp15\");            var b91=document.getElementById(\"lamp91\");
					var b16=document.getElementById(\"lamp16\");            var b92=document.getElementById(\"lamp92\");
					var b17=document.getElementById(\"lamp17\");            var b93=document.getElementById(\"lamp93\");
					var b18=document.getElementById(\"lamp18\");            var b94=document.getElementById(\"lamp94\");
					var b19=document.getElementById(\"lamp19\");            var b95=document.getElementById(\"lamp95\");
					var b20=document.getElementById(\"lamp20\");            var b96=document.getElementById(\"lamp96\");
					var b21=document.getElementById(\"lamp21\");            var b97=document.getElementById(\"lamp97\");
					var b22=document.getElementById(\"lamp22\");            var b98=document.getElementById(\"lamp98\");
					var b23=document.getElementById(\"lamp23\");            var b99=document.getElementById(\"lamp99\");
					var b24=document.getElementById(\"lamp24\");            var b100=document.getElementById(\"lamp100\");
					var b25=document.getElementById(\"lamp25\");            var b101=document.getElementById(\"lamp101\");
					var b26=document.getElementById(\"lamp26\");            var b102=document.getElementById(\"lamp102\");
					var b27=document.getElementById(\"lamp27\");            var b103=document.getElementById(\"lamp103\");
					var b28=document.getElementById(\"lamp28\");            var b104=document.getElementById(\"lamp104\");
					var b29=document.getElementById(\"lamp29\");            var b105=document.getElementById(\"lamp105\");
					var b30=document.getElementById(\"lamp30\");            var b106=document.getElementById(\"lamp106\");
					var b31=document.getElementById(\"lamp31\");            var b107=document.getElementById(\"lamp107\");
					var b32=document.getElementById(\"lamp32\");            var b108=document.getElementById(\"lamp108\");
					var b33=document.getElementById(\"lamp33\");            var b109=document.getElementById(\"lamp109\");
					var b34=document.getElementById(\"lamp34\");            var b110=document.getElementById(\"lamp110\");
					var b35=document.getElementById(\"lamp35\");            var b111=document.getElementById(\"lamp111\");
					var b36=document.getElementById(\"lamp36\");            var b112=document.getElementById(\"lamp112\");
					var b37=document.getElementById(\"lamp37\");            var b113=document.getElementById(\"lamp113\");
					var b38=document.getElementById(\"lamp38\");            var b114=document.getElementById(\"lamp114\");
					var b39=document.getElementById(\"lamp39\");			var b115=document.getElementById(\"lamp115\");
					var b40=document.getElementById(\"lamp40\");		    var b116=document.getElementById(\"lamp116\");
					var b41=document.getElementById(\"lamp41\");            var b117=document.getElementById(\"lamp117\");
					var b42=document.getElementById(\"lamp42\");            var b118=document.getElementById(\"lamp118\");
					var b43=document.getElementById(\"lamp43\");            var b119=document.getElementById(\"lamp119\");
					var b44=document.getElementById(\"lamp44\");            var b120=document.getElementById(\"lamp120\");
					var b45=document.getElementById(\"lamp45\");            var b121=document.getElementById(\"lamp121\");
					var b46=document.getElementById(\"lamp46\");            var b122=document.getElementById(\"lamp122\");
					var b47=document.getElementById(\"lamp47\");            var b123=document.getElementById(\"lamp123\");
					var b48=document.getElementById(\"lamp48\");            var b124=document.getElementById(\"lamp124\");
					var b49=document.getElementById(\"lamp49\");            var b125=document.getElementById(\"lamp125\");
					var b50=document.getElementById(\"lamp50\");            var b126=document.getElementById(\"lamp126\");
					var b51=document.getElementById(\"lamp51\");            var b127=document.getElementById(\"lamp127\");
					var b52=document.getElementById(\"lamp52\");            var b128=document.getElementById(\"lamp128\");
					var b53=document.getElementById(\"lamp53\");            var b129=document.getElementById(\"lamp129\");
					var b54=document.getElementById(\"lamp54\");            var b130=document.getElementById(\"lamp130\");
					var b55=document.getElementById(\"lamp55\");            var b131=document.getElementById(\"lamp131\");
					var b56=document.getElementById(\"lamp56\");            var b132=document.getElementById(\"lamp132\");
					var b57=document.getElementById(\"lamp57\");            var b133=document.getElementById(\"lamp133\");
					var b58=document.getElementById(\"lamp58\");            var b134=document.getElementById(\"lamp134\");
					var b59=document.getElementById(\"lamp59\");            var b135=document.getElementById(\"lamp135\");
					var b60=document.getElementById(\"lamp60\");            var b136=document.getElementById(\"lamp136\");
					var b61=document.getElementById(\"lamp61\");            var b137=document.getElementById(\"lamp137\");
					var b62=document.getElementById(\"lamp62\");            var b138=document.getElementById(\"lamp138\");
					var b63=document.getElementById(\"lamp63\");            var b139=document.getElementById(\"lamp139\");
					var b64=document.getElementById(\"lamp64\");            var b140=document.getElementById(\"lamp140\");
					var b65=document.getElementById(\"lamp65\");            var b141=document.getElementById(\"lamp141\");
					var b66=document.getElementById(\"lamp66\");            var b142=document.getElementById(\"lamp142\");
					var b67=document.getElementById(\"lamp67\");            var b143=document.getElementById(\"lamp143\");
					var b68=document.getElementById(\"lamp68\");            var b144=document.getElementById(\"lamp144\");
					var b69=document.getElementById(\"lamp69\");            var b145=document.getElementById(\"lamp145\");
					var b70=document.getElementById(\"lamp70\");            var b146=document.getElementById(\"lamp146\");
					var b71=document.getElementById(\"lamp71\");            var b147=document.getElementById(\"lamp147\");
					var b72=document.getElementById(\"lamp72\");            var b148=document.getElementById(\"lamp148\");
					var b73=document.getElementById(\"lamp73\");            var b149=document.getElementById(\"lamp149\");
					var b74=document.getElementById(\"lamp74\");            var b150=document.getElementById(\"lamp150\");
					var b75=document.getElementById(\"lamp75\");            var b151=document.getElementById(\"lamp151\");
					var b76=document.getElementById(\"lamp76\");            var b152=document.getElementById(\"lamp152\");
					var b153=document.getElementById(\"lamp153\");
					var b154=document.getElementById(\"lamp154\");
					var b155=document.getElementById(\"lamp155\");
					
					var b156=document.getElementById(\"lamp156\");
					var b157=document.getElementById(\"lamp157\");
					var b158=document.getElementById(\"lamp158\");
					var b159=document.getElementById(\"lamp159\");
					var b160=document.getElementById(\"lamp160\");
					var b161=document.getElementById(\"lamp161\");
					var b162=document.getElementById(\"lamp162\");
					
					var b163=document.getElementById(\"lamp163\");
					var b164=document.getElementById(\"lamp164\");
					var b165=document.getElementById(\"lamp165\");
					var b166=document.getElementById(\"lamp166\");
					var b167=document.getElementById(\"lamp167\");
					var b168=document.getElementById(\"lamp168\");
					var b169=document.getElementById(\"lamp169\");
					
					var b170=document.getElementById(\"lamp170\");
					var b171=document.getElementById(\"lamp171\");
					var b172=document.getElementById(\"lamp172\");
					var b173=document.getElementById(\"lamp173\");
					var b174=document.getElementById(\"lamp174\");
					var b175=document.getElementById(\"lamp175\");
					var b176=document.getElementById(\"lamp176\");
					
					var b177=document.getElementById(\"lamp177\");
					var b178=document.getElementById(\"lamp178\");
					var b179=document.getElementById(\"lamp179\");
					var b180=document.getElementById(\"lamp180\");
					var b181=document.getElementById(\"lamp181\");
					var b182=document.getElementById(\"lamp182\");
					var b183=document.getElementById(\"lamp183\");
					
					var b184=document.getElementById(\"lamp184\");
					var b185=document.getElementById(\"lamp185\");
					var b186=document.getElementById(\"lamp186\");
					var b187=document.getElementById(\"lamp187\");
					var b188=document.getElementById(\"lamp188\");
					var b189=document.getElementById(\"lamp189\");
					var b190=document.getElementById(\"lamp190\");
					
					var b191=document.getElementById(\"lamp191\");
					var b192=document.getElementById(\"lamp192\");
					var b193=document.getElementById(\"lamp193\");
					var b194=document.getElementById(\"lamp194\");
					var b195=document.getElementById(\"lamp195\");
					var b196=document.getElementById(\"lamp196\");
					var b197=document.getElementById(\"lamp197\");
					
					var b198=document.getElementById(\"lamp198\");
					var b199=document.getElementById(\"lamp199\");
					var b200=document.getElementById(\"lamp200\");
					var b201=document.getElementById(\"lamp201\");
					var b202=document.getElementById(\"lamp202\");
					var b203=document.getElementById(\"lamp203\");
					var b204=document.getElementById(\"lamp204\");
					var b205=document.getElementById(\"lamp205\");
					var b206=document.getElementById(\"lamp206\");
					var b207=document.getElementById(\"lamp207\");
					var b208=document.getElementById(\"lamp208\");
					var b209=document.getElementById(\"lamp209\");
					var b210=document.getElementById(\"lamp210\");
					var b211=document.getElementById(\"lamp211\");
					var b212=document.getElementById(\"lamp212\");
					
					
					if(document.getElementById(\"jnsper\").value=='1'){
						if((b1.checked==true)&&(b2.checked==true)&&(b3.checked==true)&&(b4.checked==true)&&(b5.checked==true)&&(b6.checked==true)&&(b7.checked==true)&&(b8.checked==true)&&(b9.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					
					}else if(document.getElementById(\"jnsper\").value=='2'){
						if((b10.checked==true)&&(b11.checked==true)&&(b12.checked==true)&&(b13.checked==true)&&(b14.checked==true)&&(b15.checked==true)&&(b16.checked==true)&&(b17.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='3'){
						if((b18.checked==true)&&(b19.checked==true)&&(b20.checked==true)&&(b21.checked==true)&&(b22.checked==true)&&(b23.checked==true)&&(b24.checked==true)&&(b25.checked==true)&&(b26.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='4'){
						if((b27.checked==true)&&(b28.checked==true)&&(b29.checked==true)&&(b30.checked==true)&&(b31.checked==true)&&(b32.checked==true)&&(b33.checked==true)&&(b34.checked==true)&&(b35.checked==true)&&(b36.checked==true)&&(b37.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='5'){
						if((b38.checked==true)&&(b39.checked==true)&&(b40.checked==true)&&(b41.checked==true)&&(b42.checked==true)&&(b43.checked==true)&&(b44.checked==true)&&(b45.checked==true)&&(b46.checked==true)&&(b47.checked==true)&&(b48.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='6'){
						if((b49.checked==true)&&(b50.checked==true)&&(b51.checked==true)&&(b52.checked==true)&&(b53.checked==true)&&(b54.checked==true)&&(b55.checked==true)&&(b56.checked==true)&&(b57.checked==true)&&(b58.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='7'){
						if((b59.checked==true)&&(b60.checked==true)&&(b61.checked==true)&&(b62.checked==true)&&(b63.checked==true)&&(b64.checked==true)&&(b65.checked==true)&&(b66.checked==true)&&(b67.checked==true)&&(b68.checked==true)&&(b69.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='8'){
						if((b70.checked==true)&&(b71.checked==true)&&(b72.checked==true)&&(b73.checked==true)&&(b74.checked==true)&&(b64.checked==true)&&(b75.checked==true)&&(b76.checked==true)&&(b77.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='9'){
						if((b78.checked==true)&&(b79.checked==true)&&(b80.checked==true)&&(b81.checked==true)&&(b82.checked==true)&&(b83.checked==true)&&(b84.checked==true)&&(b85.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='10'){
						if((b86.checked==true)&&(b87.checked==true)&&(b88.checked==true)&&(b89.checked==true)&&(b90.checked==true)&&(b91.checked==true)&&(b92.checked==true)&&(b93.checked==true)&&(b94.checked==true)&&(b95.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='11'){
						if((b96.checked==true)&&(b97.checked==true)&&(b98.checked==true)&&(b99.checked==true)&&(b100.checked==true)&&(b101.checked==true)&&(b102.checked==true)&&(b103.checked==true)&&(b104.checked==true)&&(b105.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='12'){
						if((b106.checked==true)&&(b107.checked==true)&&(b108.checked==true)&&(b109.checked==true)&&(b110.checked==true)&&(b111.checked==true)&&(b112.checked==true)&&(b113.checked==true)&&(b114.checked==true)&&(b115.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='13'){
						if((b116.checked==true)&&(b117.checked==true)&&(b118.checked==true)&&(b119.checked==true)&&(b120.checked==true)&&(b121.checked==true)&&(b122.checked==true)&&(b123.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='14'){
						if((b124.checked==true)&&(b125.checked==true)&&(b126.checked==true)&&(b127.checked==true)&&(b128.checked==true)&&(b129.checked==true)&&(b130.checked==true)&&(b131.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='21'){
						if((b132.checked==true)&&(b133.checked==true)&&(b134.checked==true)&&(b135.checked==true)&&(b136.checked==true)&&(b137.checked==true)&&(b138.checked==true)&&(b139.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}else if(document.getElementById(\"jnsper\").value=='22'){
						if((b140.checked==true)&&(b141.checked==true)&&(b142.checked==true)&&(b143.checked==true)&&(b144.checked==true)&&(b145.checked==true)&&(b146.checked==true)){
							return true;
						}else{
							alert(\"Lengkapi berkas\");
							return false;
						}
					}
					
					
				}
				
				
				
				function check_extension(file) {
				  var extension = file.toString().substr((file.toString().lastIndexOf('.') +1)).toLowerCase();
				  if (!/(jpg|png|jpeg|pdf)$/ig.test(extension)) {
					alert(\"Invalid file type: \"+extension+\".  Please use JPEG, JPG, PDF or PNG.\");
					$(\"#file\").val(\"\");
				  }
				}
				function showInputfile(checkbox,file)
				{
					
					if (checkbox.checked)
					{
						$('#file_lamp'+file).removeAttr('hidden','hidden');
						$('#btn_'+file).removeAttr('hidden','hidden');
					}else{
						$('#file_lamp'+file).attr('hidden','hidden');
						$('#btn_'+file).attr('hidden','hidden');
					}
				}
				function gethide(){
					var f = document.getElementById('form-penerimaan');
					var els = f.elements;
					for (var i = 0, len = els.length; i < len; i++) {
						x = els[i];
						if (x.type == 'file'){
								if($('#file_lamp'+i).is(':visible')){
								if ($('#file_lamp'+i).get(0).files.length == 0) {
									alert(\"Belum memilih file.\");
									
									return false;
									
								}
							}
						}
					}
					
				}
				
				function upload(id_file,no,i){
					var form_data = new FormData();
					form_data.append('file', document.getElementById(id_file).files[0]);
					   
					   var jp='{$value['CPM_BERKAS_JNS_PEROLEHAN']}';
					   var ssb_id='{$value['CPM_SSB_DOC_ID']}';
					   var id_berkas='{$value['CPM_BERKAS_NOPEL']}';
					   $.ajax({
						url:'function/BPHTB/uploadberkas/upload.php?jp='+jp+'&ssb_id='+ssb_id+'&id_berkas='+id_berkas+'&no='+no,
						type:'POST',
						data: form_data,
						contentType: false,
						cache: false,
						processData: false,
						beforeSend:function(){
						 $('#uploaded_image_'+i).html(\"<label class='text-success'>Image Uploading...</label>\");
						},   
						success:function(data)
						{
						 $('#uploaded_image_'+i).html(data);
						 //$(e).attr('hidden','hidden');
						}
					   });
				 }	
    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
        <input type=\"hidden\" name=\"process\" id=\"process\">
        <input type=\"hidden\" name=\"idssb\" id=\"idssb\" value=\"{$value['CPM_BERKAS_ID']}\">
	<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
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
                          <!-- <input type=\"radio\" name=\"jnsPerolehan\" value=\"1\" {$jnsPerolehan[1]} onclick=\"javascript:showJnsPerolehan(this)\"/> SK<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"2\" {$jnsPerolehan[2]} onclick=\"javascript:showJnsPerolehan(this)\"/> JUAL-BELI<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"3\" {$jnsPerolehan[3]} onclick=\"javascript:showJnsPerolehan(this)\"/> HIBAH<br/>
                          <input type=\"radio\" name=\"jnsPerolehan\" value=\"4\" {$jnsPerolehan[4]} onclick=\"javascript:showJnsPerolehan(this)\"/> WARIS<br/> -->
						  ".$jnsPerolehan."
						</td>
                      </tr>
                      <tr>
                        <td width=\"39%\">Persyaratan Administrasi :</td>
                        <td width=\"60%\">
                        </td>
                      </tr>
                      <tr>
                                              <td width=\"39%\" style=\"color:red\">File Maximal 2 Mb</td>

                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan1\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
										<td width='50%'>
										
										<tr>
										<td>
											<li> 1. Fotocopy KTP Penjual</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp147' id='file_lamp147'><input type='button' id='btn_147' name='btn_147' value='Upload'  onclick=\"upload('file_lamp147',30,  147);\"><span   id='uploaded_image_147'>".$berkas_lamp30."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 2. Fotocopy KTP Pembeli</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp148' id='file_lamp148'><input type='button' id='btn_148' name='btn_148' value='Upload'  onclick=\"upload('file_lamp148',31,  148);\"><span   id='uploaded_image_148'>".$berkas_lamp31."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 3. Fotocopy Kartu Keluarga Penjual</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp149' id='file_lamp149'><input type='button' id='btn_149' name='btn_149' value='Upload'  onclick=\"upload('file_lamp149',32,  149);\"><span   id='uploaded_image_149'>".$berkas_lamp32."</span></td>
										</tr>
										
										<tr>    
										<td>	
											<li> 4. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp211' id='file_lamp211' >                                                                             <input type='button' id='btn_211' name='btn_211' value='Upload'  onclick=\"upload('file_lamp211',5, 211);\"><span  id='uploaded_image_211'>".$berkas_lamp5."</span></td>
										</tr>  
										
										<tr>
										<td>
											<li> 5. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td> 																																					  <input onchange='check_extension(this.value)' type='file' name='file_lamp6' id='file_lamp6'><input type='button' id='btn_6' name='btn_6' value='Upload'  onclick=\"upload('file_lamp6',6,  6);\"><span   id='uploaded_image_6'>".$berkas_lamp6."</span></td>
										</tr>
										

										<tr>
										<td>
											<li> 6. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
										
										<tr>
										<td>
											 a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp158' id='file_lamp158'><input type='button' id='btn_158' name='btn_158' value='Upload'  onclick=\"upload('file_lamp158',40,  158);\"><span   id='uploaded_image_158'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp159' id='file_lamp159'><input type='button' id='btn_159' name='btn_159' value='Upload'  onclick=\"upload('file_lamp159',41,  159);\"><span   id='uploaded_image_159'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp160' id='file_lamp160'><input type='button' id='btn_160' name='btn_160' value='Upload'  onclick=\"upload('file_lamp160',42,  160);\"><span   id='uploaded_image_160'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp161' id='file_lamp161'><input type='button' id='btn_161' name='btn_161' value='Upload'  onclick=\"upload('file_lamp161',43,  161);\"><span   id='uploaded_image_161'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp162' id='file_lamp162'><input type='button' id='btn_162' name='btn_162' value='Upload'  onclick=\"upload('file_lamp162',44,  162);\"><span   id='uploaded_image_162'>".$berkas_lamp44."</span></td>
										</tr>
										

										
										<tr>
										<td>
											<li> 7. Fotocopy Surat Keterangan jual/beli</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp9' id='file_lamp9'><input type='button' id='btn_9' name='btn_9' value='Upload'  onclick=\"upload('file_lamp9',9,  9);\"><span   id='uploaded_image_9'>".$berkas_lamp9."</span></td>
										</tr>
										
										
										<tr>
										<td>
											<li> 8. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp156' id='file_lamp156'><input type='button' id='btn_156' name='btn_156' value='Upload'  onclick=\"upload('file_lamp156',38,  156);\"><span   id='uploaded_image_156'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 9. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp157' id='file_lamp157'><input type='button' id='btn_157' name='btn_157' value='Upload'  onclick=\"upload('file_lamp157',39,  157);\"><span   id='uploaded_image_157'>".$berkas_lamp39."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp198' id='file_lamp198'><input type='button' id='btn_198' name='btn_198' value='Upload'  onclick=\"upload('file_lamp198',46,  198);\"><span   id='uploaded_image_198'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 11. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp199' id='file_lamp199'><input type='button' id='btn_199' name='btn_199' value='Upload'  onclick=\"upload('file_lamp199',47,  199);\"><span   id='uploaded_image_199'>".$berkas_lamp47."</span></td>
										</tr>
										

								</table>
							</ul>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan2\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp10' id='file_lamp10' >																				<input type='button' id='btn_10' name='btn_10' value='Upload'  onclick=\"upload('file_lamp10',1, 10);\"><span  id='uploaded_image_10'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>2. SSPD-BPHTB </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp11' id='file_lamp11' >                                                                                                    <input type='button' id='btn_11' name='btn_11' value='Upload'  onclick=\"upload('file_lamp11',2, 11);\"><span  id='uploaded_image_11'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp12' id='file_lamp12' >                                                        <input type='button' id='btn_12' name='btn_12' value='Upload'  onclick=\"upload('file_lamp12',3, 12);\"><span  id='uploaded_image_12'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp13' id='file_lamp13' >   <input type='button' id='btn_13' name='btn_13' value='Upload'  onclick=\"upload('file_lamp13',4, 13);\"><span  id='uploaded_image_13'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp14' id='file_lamp14' >                                                                            <input type='button' id='btn_14' name='btn_14' value='Upload'  onclick=\"upload('file_lamp14',5, 14);\"><span  id='uploaded_image_14'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp15' id='file_lamp15' >                                                           <input type='button' id='btn_15' name='btn_15' value='Upload'  onclick=\"upload('file_lamp15',6, 15);\"><span  id='uploaded_image_15'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp16' id='file_lamp16' >                            <input type='button' id='btn_16' name='btn_16' value='Upload'  onclick=\"upload('file_lamp16',7, 16);\"><span  id='uploaded_image_16'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>8. Surat Pernyataan Tukar Hak Atas Tanah dari yang mengalihkan hak </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp17' id='file_lamp17' >                                              <input type='button' id='btn_17' name='btn_17' value='Upload'  onclick=\"upload('file_lamp17',10, 17);\"><span  id='uploaded_image_17'>".$berkas_lamp10."</span></td>												  
									        
								            
								</tr></table>
                            </ul>           
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan3\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'>
									<tr>
									<td width='50%'>
										<li>1. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp20' id='file_lamp20' >                                                         <input type='button' id='btn_20' name='btn_20' value='Upload'  onclick=\"upload('file_lamp20',3, 20);\"><span  id='uploaded_image_20'>".$berkas_lamp3."</span></td>
									</tr>    
									
									<tr>    
									<td>	
										<li>2. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp22' id='file_lamp22' >                                                                             <input type='button' id='btn_22' name='btn_22' value='Upload'  onclick=\"upload('file_lamp22',5, 22);\"><span  id='uploaded_image_22'>".$berkas_lamp5."</span></td>
									</tr>  

									<tr>
									<td>
										<li>3. Fotocopy Kartu Keluarga WP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp151' id='file_lamp151'><input type='button' id='btn_151' name='btn_151' value='Upload'  onclick=\"upload('file_lamp151',34,  151);\"><span   id='uploaded_image_151'>".$berkas_lamp34."</span></td>
									</tr>
									
									<tr>    
									<td>	
										<li>4. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp23' id='file_lamp23' >                                                            <input type='button' id='btn_23' name='btn_23' value='Upload'  onclick=\"upload('file_lamp23',6, 23);\"><span  id='uploaded_image_23'>".$berkas_lamp6."</span></td>
									</tr>   
									

									
										<tr>
										<td>
											<li> 5. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
									
									
									<tr>
										<td>
											a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp165' id='file_lamp165'><input type='button' id='btn_165' name='btn_165' value='Upload'  onclick=\"upload('file_lamp165',40,  165);\"><span   id='uploaded_image_165'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp166' id='file_lamp166'><input type='button' id='btn_166' name='btn_166' value='Upload'  onclick=\"upload('file_lamp166',41,  166);\"><span   id='uploaded_image_166'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp167' id='file_lamp167'><input type='button' id='btn_167' name='btn_167' value='Upload'  onclick=\"upload('file_lamp167',42,  167);\"><span   id='uploaded_image_167'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp168' id='file_lamp168'><input type='button' id='btn_168' name='btn_168' value='Upload'  onclick=\"upload('file_lamp168',43,  168);\"><span   id='uploaded_image_168'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp169' id='file_lamp169'><input type='button' id='btn_169' name='btn_169' value='Upload'  onclick=\"upload('file_lamp169',44,  169);\"><span   id='uploaded_image_169'>".$berkas_lamp44."</span></td>
										</tr>
									
									
									<tr>    
									<td>	
										<li>6. Fotocopy Surat Keterangan Waris atau Akta Hibah </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp26' id='file_lamp26' >                                      <input type='button' id='btn_26' name='btn_26' value='Upload'  onclick=\"upload('file_lamp26',12, 26);\"><span  id='uploaded_image_26'>".$berkas_lamp12."</span></td>
									</tr>
									
									<tr>
										<td>
											<li> 7. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp163' id='file_lamp163'><input type='button' id='btn_163' name='btn_163' value='Upload'  onclick=\"upload('file_lamp163',38,  163);\"><span   id='uploaded_image_163'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 8. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp164' id='file_lamp164'><input type='button' id='btn_164' name='btn_164' value='Upload'  onclick=\"upload('file_lamp164',39,  164);\"><span   id='uploaded_image_164'>".$berkas_lamp39."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 9. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp200' id='file_lamp200'><input type='button' id='btn_200' name='btn_200' value='Upload'  onclick=\"upload('file_lamp200',46,  200);\"><span   id='uploaded_image_200'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp201' id='file_lamp201'><input type='button' id='btn_201' name='btn_201' value='Upload'  onclick=\"upload('file_lamp201',47,  201);\"><span   id='uploaded_image_201'>".$berkas_lamp47."</span></td>
										</tr>
										
									
								</table>		
                            </ul>
                        </td>
                      </tr>
                      <tr class=\"jnsPerolehan\" id=\"jnsPerolehan4\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp27' id='file_lamp27' >																				<input type='button' id='btn_27' name='btn_27' value='Upload'  onclick=\"upload('file_lamp27',1, 27);\"><span  id='uploaded_image_27'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>2. SSPD-BPHTB</li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp28' id='file_lamp28' >                                                                                                     <input type='button' id='btn_28' name='btn_28' value='Upload'  onclick=\"upload('file_lamp28',2, 28);\"><span  id='uploaded_image_28'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp29' id='file_lamp29' >                                                         <input type='button' id='btn_29' name='btn_29' value='Upload'  onclick=\"upload('file_lamp29',3, 29);\"><span  id='uploaded_image_29'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp30' id='file_lamp30' >    <input type='button' id='btn_30' name='btn_30' value='Upload'  onclick=\"upload('file_lamp30',4, 30);\"><span  id='uploaded_image_30'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp31' id='file_lamp31' >                                                                             <input type='button' id='btn_31' name='btn_31' value='Upload'  onclick=\"upload('file_lamp31',5, 31);\"><span  id='uploaded_image_31'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp32' id='file_lamp32' >                                                            <input type='button' id='btn_32' name='btn_32' value='Upload'  onclick=\"upload('file_lamp32',6, 32);\"><span  id='uploaded_image_32'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp33' id='file_lamp33' >                             <input type='button' id='btn_33' name='btn_33' value='Upload'  onclick=\"upload('file_lamp33',7, 33);\"><span  id='uploaded_image_33'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp34' id='file_lamp34' >                                                           <input type='button' id='btn_34' name='btn_34' value='Upload'  onclick=\"upload('file_lamp34',13, 34);\"><span  id='uploaded_image_34'>".$berkas_lamp13."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy Surat/Keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp35' id='file_lamp35' >                                                                           <input type='button' id='btn_35' name='btn_35' value='Upload'  onclick=\"upload('file_lamp35',14, 35);\"><span  id='uploaded_image_35'>".$berkas_lamp14."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Fotocopy Surat Pernyataan Waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp36' id='file_lamp36' >                                                                              <input type='button' id='btn_36' name='btn_36' value='Upload'  onclick=\"upload('file_lamp36',15, 36);\"><span  id='uploaded_image_36'>".$berkas_lamp15."</span></td>
									</tr>
									<tr>
									<td>	
										<li>11. Fotocopy Surat Kuasa Waris dalam hal Dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp37' id='file_lamp37' >                                                              <input type='button' id='btn_37' name='btn_37' value='Upload'  onclick=\"upload('file_lamp37',16, 37);\"><span  id='uploaded_image_37'>".$berkas_lamp16."</span></td>
								</tr></table>
							</ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan5\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'>
								
								<tr>
									<td width='50%'>
										<li>1. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp38' id='file_lamp38' >                                                         <input type='button' id='btn_38' name='btn_38' value='Upload'  onclick=\"upload('file_lamp38',3, 38);\"><span  id='uploaded_image_38'>".$berkas_lamp3."</span></td>
									</tr>    
									
									<tr>    
									<td>	
										<li>2. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp39' id='file_lamp39' >                                                                             <input type='button' id='btn_39' name='btn_39' value='Upload'  onclick=\"upload('file_lamp39',5, 39);\"><span  id='uploaded_image_39'>".$berkas_lamp5."</span></td>
									</tr>  

									<tr>
									<td>
										<li>3. Fotocopy Kartu Keluarga WP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp40' id='file_lamp40'><input type='button' id='btn_40' name='btn_40' value='Upload'  onclick=\"upload('file_lamp40',34,  40);\"><span   id='uploaded_image_40'>".$berkas_lamp34."</span></td>
									</tr>
									
									<tr>    
									<td>	
										<li>4. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp41' id='file_lamp41' >                                                            <input type='button' id='btn_41' name='btn_41' value='Upload'  onclick=\"upload('file_lamp41',6, 41);\"><span  id='uploaded_image_41'>".$berkas_lamp6."</span></td>
									</tr>   
									

									
									<tr>
										<td>
											<li> 5. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
									
									<tr>
										<td>
											a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp172' id='file_lamp172'><input type='button' id='btn_172' name='btn_172' value='Upload'  onclick=\"upload('file_lamp172',40,  172);\"><span   id='uploaded_image_172'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp173' id='file_lamp173'><input type='button' id='btn_173' name='btn_173' value='Upload'  onclick=\"upload('file_lamp173',41,  173);\"><span   id='uploaded_image_173'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp174' id='file_lamp174'><input type='button' id='btn_174' name='btn_174' value='Upload'  onclick=\"upload('file_lamp174',42,  174);\"><span   id='uploaded_image_174'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp175' id='file_lamp175'><input type='button' id='btn_175' name='btn_175' value='Upload'  onclick=\"upload('file_lamp175',43,  175);\"><span   id='uploaded_image_175'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp176' id='file_lamp176'><input type='button' id='btn_176' name='btn_176' value='Upload'  onclick=\"upload('file_lamp176',44,  176);\"><span   id='uploaded_image_176'>".$berkas_lamp44."</span></td>
										</tr>
									
									
									
									<tr>    
									<td>	
										<li>6. Fotocopy Surat Keterangan Waris atau Akta Hibah </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp44' id='file_lamp44' >                                      <input type='button' id='btn_44' name='btn_44' value='Upload'  onclick=\"upload('file_lamp44',12, 44);\"><span  id='uploaded_image_44'>".$berkas_lamp12."</span></td>
									</tr>
									
									
									
									<tr>
										<td>
											<li> 7. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp170' id='file_lamp170'><input type='button' id='btn_170' name='btn_170' value='Upload'  onclick=\"upload('file_lamp170',38,  170);\"><span   id='uploaded_image_170'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 8. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp171' id='file_lamp171'><input type='button' id='btn_171' name='btn_171' value='Upload'  onclick=\"upload('file_lamp171',39,  171);\"><span   id='uploaded_image_171'>".$berkas_lamp39."</span></td>
										</tr>
										
										
										<tr>
										<td>
											<li> 9. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp202' id='file_lamp202'><input type='button' id='btn_202' name='btn_202' value='Upload'  onclick=\"upload('file_lamp202',46,  202);\"><span   id='uploaded_image_202'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp203' id='file_lamp203'><input type='button' id='btn_203' name='btn_203' value='Upload'  onclick=\"upload('file_lamp203',47,  203);\"><span   id='uploaded_image_203'>".$berkas_lamp47."</span></td>
										</tr>

									
								
								</table>
							</ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan6\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp49' id='file_lamp49' >																				<input type='button' id='btn_49' name='btn_49' value='Upload'  onclick=\"upload('file_lamp49',1, 49);\"><span  id='uploaded_image_49'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp50' id='file_lamp50' >                                                                                                     <input type='button' id='btn_50' name='btn_50' value='Upload'  onclick=\"upload('file_lamp50',2, 50);\"><span  id='uploaded_image_50'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp51' id='file_lamp51' >                                                         <input type='button' id='btn_51' name='btn_51' value='Upload'  onclick=\"upload('file_lamp51',3, 51);\"><span  id='uploaded_image_51'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp52' id='file_lamp52' >    <input type='button' id='btn_52' name='btn_52' value='Upload'  onclick=\"upload('file_lamp52',4, 52);\"><span  id='uploaded_image_52'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp53' id='file_lamp53' >                                                                             <input type='button' id='btn_53' name='btn_53' value='Upload'  onclick=\"upload('file_lamp53',5, 53);\"><span  id='uploaded_image_53'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp54' id='file_lamp54' >                                                            <input type='button' id='btn_54' name='btn_54' value='Upload'  onclick=\"upload('file_lamp54',6, 54);\"><span  id='uploaded_image_54'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp55' id='file_lamp55' >                             <input type='button' id='btn_55' name='btn_55' value='Upload'  onclick=\"upload('file_lamp55',7, 55);\"><span  id='uploaded_image_55'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy Akta Pendirian Perusahaan yang terbaru </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp56' id='file_lamp56' >                                                              <input type='button' id='btn_56' name='btn_56' value='Upload'  onclick=\"upload('file_lamp56',17, 56);\"><span  id='uploaded_image_56'>".$berkas_lamp17."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy NPWP Perusahaan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp57' id='file_lamp57' >                                                                                     <input type='button' id='btn_57' name='btn_57' value='Upload'  onclick=\"upload('file_lamp57',18, 57);\"><span  id='uploaded_image_57'>".$berkas_lamp18."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Surat Pernyataan Penggabungan/Peleburan/Pemekaran Usaha atau sejenisnya </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp58' id='file_lamp58' >                                      <input type='button' id='btn_58' name='btn_58' value='Upload'  onclick=\"upload('file_lamp58',19, 58);\"><span  id='uploaded_image_58'>".$berkas_lamp19."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan7\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>	
										<li>1. Formulir penyampaian SSPD BPHTB</li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp59' id='file_lamp59' >																				<input type='button' id='btn_59' name='btn_59' value='Upload'  onclick=\"upload('file_lamp59',1, 59);\"><span  id='uploaded_image_59'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp60' id='file_lamp60' >                                                                                                    <input type='button' id='btn_60' name='btn_60' value='Upload'  onclick=\"upload('file_lamp60',2, 60);\"><span  id='uploaded_image_60'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp61' id='file_lamp61' >                                                        <input type='button' id='btn_61' name='btn_61' value='Upload'  onclick=\"upload('file_lamp61',3, 61);\"><span  id='uploaded_image_61'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp62' id='file_lamp62' >   <input type='button' id='btn_62' name='btn_62' value='Upload'  onclick=\"upload('file_lamp62',4, 62);\"><span  id='uploaded_image_62'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp63' id='file_lamp63' >                                                                            <input type='button' id='btn_63' name='btn_63' value='Upload'  onclick=\"upload('file_lamp63',5, 63);\"><span  id='uploaded_image_63'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp64' id='file_lamp64' >                                                           <input type='button' id='btn_64' name='btn_64' value='Upload'  onclick=\"upload('file_lamp64',6, 64);\"><span  id='uploaded_image_64'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp65' id='file_lamp65' >                            <input type='button' id='btn_65' name='btn_65' value='Upload'  onclick=\"upload('file_lamp65',7, 65);\"><span  id='uploaded_image_65'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy KTP para ahli waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp66' id='file_lamp66' >                                                                                <input type='button' id='btn_66' name='btn_66' value='Upload'  onclick=\"upload('file_lamp66',20, 66);\"><span  id='uploaded_image_66'>".$berkas_lamp20."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy Surat/keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp67' id='file_lamp67' >                                                                          <input type='button' id='btn_67' name='btn_67' value='Upload'  onclick=\"upload('file_lamp67',21, 67);\"><span  id='uploaded_image_67'>".$berkas_lamp21."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp68' id='file_lamp68' >                                                                             <input type='button' id='btn_68' name='btn_68' value='Upload'  onclick=\"upload('file_lamp68',22, 68);\"><span  id='uploaded_image_68'>".$berkas_lamp22."</span></td>
									</tr>
									
									
									<!-- <tr>
									<td>	
										<li> Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp69' id='file_lamp69' >                                                                             <input type='button' id='btn_69' name='btn_69' value='Upload'  onclick=\"upload('file_lamp69',23, 69);\"><span  id='uploaded_image_69'>".$berkas_lamp23."</span></td>
								</tr>-->
								</table>
							</ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan8\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'>
									
									<tr>
									<td width='50%'>
										<li>1. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp70' id='file_lamp70' >                                                         <input type='button' id='btn_70' name='btn_70' value='Upload'  onclick=\"upload('file_lamp70',3,70);\"><span id='uploaded_image_70'>".$berkas_lamp3."</span></td>
									</tr> 
									
									<tr>    
									<td>	
										<li>2. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp71' id='file_lamp71' >                                                                             <input type='button' id='btn_71' name='btn_71' value='Upload'  onclick=\"upload('file_lamp71',5,71);\"><span id='uploaded_image_71'>".$berkas_lamp5."</span></td>
									</tr>   
									
									<tr>
									<td>
										<li>3. Harga Transaksi Tercantum Dalam Risalah Lelang</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp72' id='file_lamp72'><input type='button' id='btn_72' name='btn_72' value='Upload'  onclick=\"upload('file_lamp72',37,  72);\"><span   id='uploaded_image_72'>".$berkas_lamp37."</span></td>
									</tr>
									
									<tr>    
									<td>	
										<li>4. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp73' id='file_lamp73' >                                                            <input type='button' id='btn_73' name='btn_73' value='Upload'  onclick=\"upload('file_lamp73',6,73);\"><span id='uploaded_image_73'>".$berkas_lamp6."</span></td>
									</tr>   
									
 
									
									<tr>
										<td>
											<li> 5. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
									
										<tr>
										<td>
											 a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp186' id='file_lamp186'><input type='button' id='btn_186' name='btn_186' value='Upload'  onclick=\"upload('file_lamp186',40,  186);\"><span   id='uploaded_image_186'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp187' id='file_lamp187'><input type='button' id='btn_187' name='btn_187' value='Upload'  onclick=\"upload('file_lamp187',41,  187);\"><span   id='uploaded_image_187'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp188' id='file_lamp188'><input type='button' id='btn_188' name='btn_188' value='Upload'  onclick=\"upload('file_lamp188',42,  188);\"><span   id='uploaded_image_188'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp189' id='file_lamp189'><input type='button' id='btn_189' name='btn_189' value='Upload'  onclick=\"upload('file_lamp189',43,  189);\"><span   id='uploaded_image_189'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp190' id='file_lamp190'><input type='button' id='btn_190' name='btn_190' value='Upload'  onclick=\"upload('file_lamp190',44,  190);\"><span   id='uploaded_image_190'>".$berkas_lamp44."</span></td>
										</tr>
									
									
									
									
									
									
									<tr>
									<td>
										<li>6. Fotocopy Keterangan Badan Hukum, Hadiah, Lelang</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp76' id='file_lamp76'><input type='button' id='btn_76' name='btn_76' value='Upload'  onclick=\"upload('file_lamp76',36,  76);\"><span   id='uploaded_image_76'>".$berkas_lamp36."</span></td>
									</tr>
									
									
									<tr>
										<td>
											<li> 7. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp184' id='file_lamp184'><input type='button' id='btn_184' name='btn_184' value='Upload'  onclick=\"upload('file_lamp184',38,  184);\"><span   id='uploaded_image_184'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 8. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp185' id='file_lamp185'><input type='button' id='btn_185' name='btn_185' value='Upload'  onclick=\"upload('file_lamp185',39,  185);\"><span   id='uploaded_image_185'>".$berkas_lamp39."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 9. Surat Risalah Lelang</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp206' id='file_lamp206'><input type='button' id='btn_206' name='btn_206' value='Upload'  onclick=\"upload('file_lamp206',45,  206);\"><span   id='uploaded_image_206'>".$berkas_lamp45."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp207' id='file_lamp207'><input type='button' id='btn_207' name='btn_207' value='Upload'  onclick=\"upload('file_lamp207',46,  207);\"><span   id='uploaded_image_207'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 11. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp208' id='file_lamp208'><input type='button' id='btn_208' name='btn_208' value='Upload'  onclick=\"upload('file_lamp208',47,  208);\"><span   id='uploaded_image_208'>".$berkas_lamp47."</span></td>
										</tr>
									
								</table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                                                   
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan9\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
									<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp78' id='file_lamp78' >																				<input type='button' id='btn_78' name='btn_78' value='Upload'  onclick=\"upload('file_lamp78',1, 78);\"><span  id='uploaded_image_78'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp79' id='file_lamp79' >                                                                                                     <input type='button' id='btn_79' name='btn_79' value='Upload'  onclick=\"upload('file_lamp79',2, 79);\"><span  id='uploaded_image_79'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp80' id='file_lamp80' >                                                         <input type='button' id='btn_80' name='btn_80' value='Upload'  onclick=\"upload('file_lamp80',3, 80);\"><span  id='uploaded_image_80'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp81' id='file_lamp81' >    <input type='button' id='btn_81' name='btn_81' value='Upload'  onclick=\"upload('file_lamp81',4, 81);\"><span  id='uploaded_image_81'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp82' id='file_lamp82' >                                                                             <input type='button' id='btn_82' name='btn_82' value='Upload'  onclick=\"upload('file_lamp82',5, 82);\"><span  id='uploaded_image_82'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp83' id='file_lamp83' >                                                            <input type='button' id='btn_83' name='btn_83' value='Upload'  onclick=\"upload('file_lamp83',6, 83);\"><span  id='uploaded_image_83'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp84' id='file_lamp84' >                             <input type='button' id='btn_84' name='btn_84' value='Upload'  onclick=\"upload('file_lamp84',7, 84);\"><span  id='uploaded_image_84'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy Keputusan Hakim/Pengadilan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp85' id='file_lamp85' >                                                                          <input type='button' id='btn_85' name='btn_85' value='Upload'  onclick=\"upload('file_lamp85',25, 85);\"><span  id='uploaded_image_85'>".$berkas_lamp25."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan10\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp86' id='file_lamp86' >																				<input type='button' id='btn_86' name='btn_86' value='Upload'  onclick=\"upload('file_lamp86',1, 86);\"><span  id='uploaded_image_86'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp87' id='file_lamp87' >                                                                                                     <input type='button' id='btn_87' name='btn_87' value='Upload'  onclick=\"upload('file_lamp87',2, 87);\"><span  id='uploaded_image_87'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp88' id='file_lamp88' >                                                         <input type='button' id='btn_88' name='btn_88' value='Upload'  onclick=\"upload('file_lamp88',3, 88);\"><span  id='uploaded_image_88'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp89' id='file_lamp89' >    <input type='button' id='btn_89' name='btn_89' value='Upload'  onclick=\"upload('file_lamp89',4, 89);\"><span  id='uploaded_image_89'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp90' id='file_lamp90' >                                                                             <input type='button' id='btn_90' name='btn_90' value='Upload'  onclick=\"upload('file_lamp90',5, 90);\"><span  id='uploaded_image_90'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp91' id='file_lamp91' >                                                            <input type='button' id='btn_91' name='btn_91' value='Upload'  onclick=\"upload('file_lamp91',6, 91);\"><span  id='uploaded_image_91'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp92' id='file_lamp92' >                             <input type='button' id='btn_92' name='btn_92' value='Upload'  onclick=\"upload('file_lamp92',7, 92);\"><span  id='uploaded_image_92'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy Akta Pendirian Perusahaan yang terbaru </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp93' id='file_lamp93' >                                                              <input type='button' id='btn_93' name='btn_93' value='Upload'  onclick=\"upload('file_lamp93',17, 93);\"><span  id='uploaded_image_93'>".$berkas_lamp17."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy NPWP Perusahaan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp94' id='file_lamp94' >                                                                                     <input type='button' id='btn_94' name='btn_94' value='Upload'  onclick=\"upload('file_lamp94',18, 94);\"><span  id='uploaded_image_94'>".$berkas_lamp18."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Surat Pernyataan Penggabungan Usaha atau sejenisnya </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp95' id='file_lamp95' >                                                          <input type='button' id='btn_95' name='btn_95' value='Upload'  onclick=\"upload('file_lamp95',19, 95);\"><span  id='uploaded_image_95'>".$berkas_lamp19."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan11\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp96' id='file_lamp96' >																				<input type='button' id='btn_96' name='btn_96' value='Upload'  onclick=\"upload('file_lamp96',1, 96);\"><span  id='uploaded_image_96'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp97' id='file_lamp97' >                                                                                                    <input type='button' id='btn_97' name='btn_97' value='Upload'  onclick=\"upload('file_lamp97',2, 97);\"><span  id='uploaded_image_97'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp98' id='file_lamp98' >                                                        <input type='button' id='btn_98' name='btn_98' value='Upload'  onclick=\"upload('file_lamp98',3, 98);\"><span  id='uploaded_image_98'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp99' id='file_lamp99' >   <input type='button' id='btn_99' name='btn_99' value='Upload'  onclick=\"upload('file_lamp99',4, 99);\"><span  id='uploaded_image_99'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp100' id='file_lamp100' >                                                                         <input type='button' id='btn_100' name='btn_100' value='Upload'  onclick=\"upload('file_lamp100',5,100);\"><span id='uploaded_image_100'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp101' id='file_lamp101' >                                                        <input type='button' id='btn_101' name='btn_101' value='Upload'  onclick=\"upload('file_lamp101',6,101);\"><span id='uploaded_image_101'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp102' id='file_lamp102' >                         <input type='button' id='btn_102' name='btn_102' value='Upload'  onclick=\"upload('file_lamp102',7,102);\"><span id='uploaded_image_102'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy Akta Pendirian Perusahaan yang terbaru </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp103' id='file_lamp103' >                                                          <input type='button' id='btn_103' name='btn_103' value='Upload'  onclick=\"upload('file_lamp103',17,103);\"><span id='uploaded_image_103'>".$berkas_lamp17."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy NPWP Perusahaan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp104' id='file_lamp104' >                                                                                 <input type='button' id='btn_104' name='btn_104' value='Upload'  onclick=\"upload('file_lamp104',18,104);\"><span id='uploaded_image_104'>".$berkas_lamp18."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Surat Pernyataan Peleburan Usaha atau sejenisnya </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp105' id='file_lamp105' >                                                         <input type='button' id='btn_105' name='btn_105' value='Upload'  onclick=\"upload('file_lamp105',19,105);\"><span id='uploaded_image_105'>".$berkas_lamp19."</span></td>
								</tr></table>
							</ul>                                                
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan12\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp106' id='file_lamp106' >																				<input type='button' id='btn_106' name='btn_106' value='Upload'  onclick=\"upload('file_lamp106',1,106);\"><span id='uploaded_image_106'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp107' id='file_lamp107' >                                                                                                     <input type='button' id='btn_107' name='btn_107' value='Upload'  onclick=\"upload('file_lamp107',2,107);\"><span id='uploaded_image_107'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp108' id='file_lamp108' >                                                         <input type='button' id='btn_108' name='btn_108' value='Upload'  onclick=\"upload('file_lamp108',3,108);\"><span id='uploaded_image_108'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp109' id='file_lamp109' >    <input type='button' id='btn_109' name='btn_109' value='Upload'  onclick=\"upload('file_lamp109',4,109);\"><span id='uploaded_image_109'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp110' id='file_lamp110' >                                                                             <input type='button' id='btn_110' name='btn_110' value='Upload'  onclick=\"upload('file_lamp110',5,110);\"><span id='uploaded_image_110'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp111' id='file_lamp111' >                                                            <input type='button' id='btn_111' name='btn_111' value='Upload'  onclick=\"upload('file_lamp111',6,111);\"><span id='uploaded_image_111'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp112' id='file_lamp112' >                             <input type='button' id='btn_112' name='btn_112' value='Upload'  onclick=\"upload('file_lamp112',7,112);\"><span id='uploaded_image_112'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy Akta Pendirian Perusahaan yang terbaru </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp113' id='file_lamp113' >                                                              <input type='button' id='btn_113' name='btn_113' value='Upload'  onclick=\"upload('file_lamp113',17,113);\"><span id='uploaded_image_113'>".$berkas_lamp17."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy NPWP Perusahaan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp114' id='file_lamp114' >                                                                                     <input type='button' id='btn_114' name='btn_114' value='Upload'  onclick=\"upload('file_lamp114',18,114);\"><span id='uploaded_image_114'>".$berkas_lamp18."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Surat Pernyataan Pemekaran Usaha atau sejenisnya </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp115' id='file_lamp115' >                                                             <input type='button' id='btn_115' name='btn_115' value='Upload'  onclick=\"upload('file_lamp115',19,115);\"><span id='uploaded_image_115'>".$berkas_lamp19."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan13\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'>
									<tr>
									<td width='50%'>
										<li>1. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp118' id='file_lamp118' >                                                         <input type='button' id='btn_118' name='btn_118' value='Upload'  onclick=\"upload('file_lamp118',3,118);\"><span id='uploaded_image_118'>".$berkas_lamp3."</span></td>
									</tr> 
									
									<tr>    
									<td>	
										<li>2. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp120' id='file_lamp120' >                                                                             <input type='button' id='btn_120' name='btn_120' value='Upload'  onclick=\"upload('file_lamp120',5,120);\"><span id='uploaded_image_120'>".$berkas_lamp5."</span></td>
									</tr>   
									
									<tr>
									<td>
										<li>3. Harga Sudah Tercantum Surat Hadiah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp153' id='file_lamp153'><input type='button' id='btn_153' name='btn_153' value='Upload'  onclick=\"upload('file_lamp153',35,  153);\"><span   id='uploaded_image_153'>".$berkas_lamp35."</span></td>
									</tr>
									
									<tr>    
									<td>	
										<li>4. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp121' id='file_lamp121' >                                                            <input type='button' id='btn_121' name='btn_121' value='Upload'  onclick=\"upload('file_lamp121',6,121);\"><span id='uploaded_image_121'>".$berkas_lamp6."</span></td>
									</tr>   
									
  
									
									<tr>
										<td>
											<li> 5. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
									
									<tr>
										<td>
											a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp179' id='file_lamp179'><input type='button' id='btn_179' name='btn_179' value='Upload'  onclick=\"upload('file_lamp179',40,  179);\"><span   id='uploaded_image_179'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp180' id='file_lamp180'><input type='button' id='btn_180' name='btn_180' value='Upload'  onclick=\"upload('file_lamp180',41,  180);\"><span   id='uploaded_image_180'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp181' id='file_lamp181'><input type='button' id='btn_181' name='btn_181' value='Upload'  onclick=\"upload('file_lamp181',42,  181);\"><span   id='uploaded_image_181'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp182' id='file_lamp182'><input type='button' id='btn_182' name='btn_182' value='Upload'  onclick=\"upload('file_lamp182',43,  182);\"><span   id='uploaded_image_182'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp183' id='file_lamp183'><input type='button' id='btn_183' name='btn_183' value='Upload'  onclick=\"upload('file_lamp183',44,  183);\"><span   id='uploaded_image_183'>".$berkas_lamp44."</span></td>
										</tr>
									
									
									
									<tr>
									<td>
										<li> 6. Fotocopy Keterangan Badan Hukum, Hadiah, Lelang</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp155' id='file_lamp155'><input type='button' id='btn_155' name='btn_155' value='Upload'  onclick=\"upload('file_lamp155',36,  155);\"><span   id='uploaded_image_155'>".$berkas_lamp36."</span></td>
									</tr>
									
									<tr>
										<td>
											<li> 7. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp177' id='file_lamp177'><input type='button' id='btn_177' name='btn_177' value='Upload'  onclick=\"upload('file_lamp177',38,  177);\"><span   id='uploaded_image_177'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 8. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp178' id='file_lamp178'><input type='button' id='btn_178' name='btn_178' value='Upload'  onclick=\"upload('file_lamp178',39,  178);\"><span   id='uploaded_image_178'>".$berkas_lamp39."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 9. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp204' id='file_lamp204'><input type='button' id='btn_204' name='btn_204' value='Upload'  onclick=\"upload('file_lamp204',46,  204);\"><span   id='uploaded_image_204'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp205' id='file_lamp205'><input type='button' id='btn_205' name='btn_205' value='Upload'  onclick=\"upload('file_lamp205',47,  205);\"><span   id='uploaded_image_205'>".$berkas_lamp47."</span></td>
										</tr>
										
  
								</table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                                                      
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan14\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'>
								
										<td>
											<li> 1. Fotocopy KTP Penjual</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp124' id='file_lamp124'><input type='button' id='btn_124' name='btn_124' value='Upload'  onclick=\"upload('file_lamp124',30,  124);\"><span   id='uploaded_image_124'>".$berkas_lamp30."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 2. Fotocopy KTP Pembeli</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp125' id='file_lamp125'><input type='button' id='btn_125' name='btn_125' value='Upload'  onclick=\"upload('file_lamp125',31,  125);\"><span   id='uploaded_image_125'>".$berkas_lamp31."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 3. Fotocopy Kartu Keluarga Penjual</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp126' id='file_lamp126'><input type='button' id='btn_126' name='btn_126' value='Upload'  onclick=\"upload('file_lamp126',32,  126);\"><span   id='uploaded_image_126'>".$berkas_lamp32."</span></td>
										</tr>
										
										<tr>
										
										<tr>    
										<td>	
											<li> 4. Fotocopy SPPT PBB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp212' id='file_lamp212' >                                                                             <input type='button' id='btn_212' name='btn_212' value='Upload'  onclick=\"upload('file_lamp212',5, 212);\"><span  id='uploaded_image_212'>".$berkas_lamp5."</span></td>
										</tr>  
										
										<td>
											<li> 5. Fotocopy Bukti Pembayaran/Lunas PBB 5 tahun kebelakang </li></td><td> 																																					  <input onchange='check_extension(this.value)' type='file' name='file_lamp127' id='file_lamp127'><input type='button' id='btn_127' name='btn_127' value='Upload'  onclick=\"upload('file_lamp127',6,  127);\"><span   id='uploaded_image_127'>".$berkas_lamp6."</span></td>
										</tr>
										

										
										
										<tr>
										<td>
											<li> 6. Fotocopy Surat Keterangan Kepemilikan</li></td><td> 				</td>
										</tr>
										
										<tr>
										<td>
											a. Sertifikat Lampiran 1</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp193' id='file_lamp193'><input type='button' id='btn_193' name='btn_193' value='Upload'  onclick=\"upload('file_lamp193',40,  193);\"><span   id='uploaded_image_193'>".$berkas_lamp40."</span></td>
										</tr>
										
										<tr>
										<td>
											b. Sertifikat Lampiran 2</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp194' id='file_lamp194'><input type='button' id='btn_194' name='btn_194' value='Upload'  onclick=\"upload('file_lamp194',41,  194);\"><span   id='uploaded_image_194'>".$berkas_lamp41."</span></td>
										</tr>
										
										<tr>
										<td>
											c. Sertifikat Lampiran 3</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp195' id='file_lamp195'><input type='button' id='btn_195' name='btn_195' value='Upload'  onclick=\"upload('file_lamp195',42,  195);\"><span   id='uploaded_image_195'>".$berkas_lamp42."</span></td>
										</tr>
										
										<tr>
										<td>
											d. Sertifikat Lampiran 4</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp196' id='file_lamp196'><input type='button' id='btn_196' name='btn_196' value='Upload'  onclick=\"upload('file_lamp196',43,  196);\"><span   id='uploaded_image_196'>".$berkas_lamp43."</span></td>
										</tr>
										
										<tr>
										<td>
											e. Sertifikat Lampiran 5</td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp197' id='file_lamp197'><input type='button' id='btn_197' name='btn_197' value='Upload'  onclick=\"upload('file_lamp197',44,  197);\"><span   id='uploaded_image_197'>".$berkas_lamp44."</span></td>
										</tr>
										
										
										
										<tr>
										<td>
											<li> 7. Fotocopy Surat Keterangan jual/beli</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp130' id='file_lamp130'><input type='button' id='btn_130' name='btn_130' value='Upload'  onclick=\"upload('file_lamp130',9,  130);\"><span   id='uploaded_image_130'>".$berkas_lamp9."</span></td>
										</tr>
										<tr>    
										<td>    
											<li> 8. Daftar harga/Pricelist dalam hal pembelian dan pengembangan</li></td><td> 																																			  <input onchange='check_extension(this.value)' type='file' name='file_lamp131' id='file_lamp131'><input type='button' id='btn_131' name='btn_131' value='Upload'  onclick=\"upload('file_lamp131',8,  131);\"><span   id='uploaded_image_131'>".$berkas_lamp8."</span></td>
										</tr>  
								
								
										
										
										<tr>
										<td>
											<li> 9. Foto Rumah/Objek</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp191' id='file_lamp191'><input type='button' id='btn_191' name='btn_191' value='Upload'  onclick=\"upload('file_lamp191',38,  191);\"><span   id='uploaded_image_191'>".$berkas_lamp38."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 10. Foto Lokasi Google Map/Denah</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp192' id='file_lamp192'><input type='button' id='btn_192' name='btn_192' value='Upload'  onclick=\"upload('file_lamp192',39,  192);\"><span   id='uploaded_image_192'>".$berkas_lamp39."</span></td>
										</tr>
										
										
										<tr>
										<td>
											<li> 11. Upload Berkas NPWP</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp209' id='file_lamp209'><input type='button' id='btn_209' name='btn_209' value='Upload'  onclick=\"upload('file_lamp209',46,  209);\"><span   id='uploaded_image_209'>".$berkas_lamp46."</span></td>
										</tr>
										
										<tr>
										<td>
											<li> 12. Lain-Lain (Document Pendukung)</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp210' id='file_lamp210'><input type='button' id='btn_210' name='btn_210' value='Upload'  onclick=\"upload('file_lamp210',47,  210);\"><span   id='uploaded_image_210'>".$berkas_lamp47."</span></td>
										</tr>
										

									
								
								</table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                                                     
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan21\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp132' id='file_lamp132' >																				<input type='button' id='btn_132' name='btn_132' value='Upload'  onclick=\"upload('file_lamp132',1,132);\"><span id='uploaded_image_132'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>    
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp133' id='file_lamp133' >                                                                                                     <input type='button' id='btn_133' name='btn_133' value='Upload'  onclick=\"upload('file_lamp133',2,133);\"><span id='uploaded_image_133'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp134' id='file_lamp134' >                                                         <input type='button' id='btn_134' name='btn_134' value='Upload'  onclick=\"upload('file_lamp134',3,134);\"><span id='uploaded_image_134'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp135' id='file_lamp135' >    <input type='button' id='btn_135' name='btn_135' value='Upload'  onclick=\"upload('file_lamp135',4,135);\"><span id='uploaded_image_135'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp136' id='file_lamp136' >                                                                             <input type='button' id='btn_136' name='btn_136' value='Upload'  onclick=\"upload('file_lamp136',5,136);\"><span id='uploaded_image_136'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp137' id='file_lamp137' >                                                            <input type='button' id='btn_137' name='btn_137' value='Upload'  onclick=\"upload('file_lamp137',6,137);\"><span id='uploaded_image_137'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp138' id='file_lamp138' >                             <input type='button' id='btn_138' name='btn_138' value='Upload'  onclick=\"upload('file_lamp138',7,138);\"><span id='uploaded_image_138'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Surat Pelepasan Hak Atas Tanah dari BPN </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp139' id='file_lamp139' >                                                                      <input type='button' id='btn_139' name='btn_139' value='Upload'  onclick=\"upload('file_lamp139',28,139);\"><span id='uploaded_image_139'>".$berkas_lamp28."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                                                       
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan22\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp140' id='file_lamp140' >																				<input type='button' id='btn_140' name='btn_140' value='Upload'  onclick=\"upload('file_lamp140',1,140);\"><span id='uploaded_image_140'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp141' id='file_lamp141' >                                                                                                     <input type='button' id='btn_141' name='btn_141' value='Upload'  onclick=\"upload('file_lamp141',2,141);\"><span id='uploaded_image_141'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp142' id='file_lamp142' >                                                         <input type='button' id='btn_142' name='btn_142' value='Upload'  onclick=\"upload('file_lamp142',3,142);\"><span id='uploaded_image_142'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp143' id='file_lamp143' >    <input type='button' id='btn_143' name='btn_143' value='Upload'  onclick=\"upload('file_lamp143',4,143);\"><span id='uploaded_image_143'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp144' id='file_lamp144' >                                                                             <input type='button' id='btn_144' name='btn_144' value='Upload'  onclick=\"upload('file_lamp144',5,144);\"><span id='uploaded_image_144'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp145' id='file_lamp145' >                                                            <input type='button' id='btn_145' name='btn_145' value='Upload'  onclick=\"upload('file_lamp145',6,145);\"><span id='uploaded_image_145'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp146' id='file_lamp146' >                             <input type='button' id='btn_146' name='btn_146' value='Upload'  onclick=\"upload('file_lamp146',7,146);\"><span id='uploaded_image_146'>".$berkas_lamp7."</span></td>
								</tr></table>
							</ul>                                                 
                        </td>                                                     
                      </tr>                 
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan30\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
										<td width='50%'>
											<li>1. Formulir penyampaian SSPD BPHTB</li></td><td width='50%'> 																																						<input onchange='check_extension(this.value)' type='file' name='file_lamp147' id='file_lamp147'><input type='button' id='btn_147' name='btn_147' value='Upload'  onclick=\"upload('file_lamp147',1,147);\"><span id='uploaded_image_147'>".$berkas_lamp1."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>2. SSPD-BPHTB </li></td><td>																																											<input onchange='check_extension(this.value)' type='file' name='file_lamp148' id='file_lamp148'><input type='button' id='btn_148' name='btn_148' value='Upload'  onclick=\"upload('file_lamp148',2,  148);\"><span   id='uploaded_image_148'>".$berkas_lamp2."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili</li></td><td> 																																				  <input onchange='check_extension(this.value)' type='file' name='file_lamp149' id='file_lamp149'><input type='button' id='btn_149' name='btn_149' value='Upload'  onclick=\"upload('file_lamp149',3,149);\"><span   id='uploaded_image_149'>".$berkas_lamp3."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan</li></td><td>                          																	  <input onchange='check_extension(this.value)' type='file' name='file_lamp150' id='file_lamp150'><input type='button' id='btn_150' name='btn_150' value='Upload'  onclick=\"upload('file_lamp150',4,  150);\"><span   id='uploaded_image_150'>".$berkas_lamp4."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>5. Fotocopy SPPT yang sedang berjalan</li></td><td> 																																									  <input onchange='check_extension(this.value)' type='file' name='file_lamp151' id='file_lamp151'><input type='button' id='btn_151' name='btn_151' value='Upload'  onclick=\"upload('file_lamp151',5,  151);\"><span   id='uploaded_image_151'>".$berkas_lamp5."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009</li></td><td> 																																					  <input onchange='check_extension(this.value)' type='file' name='file_lamp152' id='file_lamp152'><input type='button' id='btn_152' name='btn_152' value='Upload'  onclick=\"upload('file_lamp152',6,  152);\"><span   id='uploaded_image_152'>".$berkas_lamp6."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir</li></td><td> 																													  <input onchange='check_extension(this.value)' type='file' name='file_lamp153' id='file_lamp153'><input type='button' id='btn_153' name='btn_153' value='Upload'  onclick=\"upload('file_lamp153',7,  153);\"><span   id='uploaded_image_153'>".$berkas_lamp7."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>8. Daftar harga/Pricelist dalam hal pembelian dan pengembangan</li></td><td> 																																			  <input onchange='check_extension(this.value)' type='file' name='file_lamp154' id='file_lamp154'><input type='button' id='btn_154' name='btn_154' value='Upload'  onclick=\"upload('file_lamp154',8,  154);\"><span   id='uploaded_image_154'>".$berkas_lamp8."</span></td>
										</tr>   
										<tr>    
										<td>    
											<li>9. Fotocopy Bukti transaksi/rincian pembayaran</li></td><td> 																																							  <input onchange='check_extension(this.value)' type='file' name='file_lamp155' id='file_lamp155'><input type='button' id='btn_155' name='btn_155' value='Upload'  onclick=\"upload('file_lamp155',9,  155);\"><span   id='uploaded_image_155'>".$berkas_lamp9."</span></td>
									</tr>
								</table>
							</ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan31\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                             <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp156' id='file_lamp156' >																				<input type='button' id='btn_156' name='btn_156' value='Upload'  onclick=\"upload('file_lamp156',1, 156);\"><span  id='uploaded_image_156'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp157' id='file_lamp157' >                                                                                                     <input type='button' id='btn_157' name='btn_157' value='Upload'  onclick=\"upload('file_lamp157',2, 157);\"><span  id='uploaded_image_157'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp158' id='file_lamp158' >                                                         <input type='button' id='btn_158' name='btn_158' value='Upload'  onclick=\"upload('file_lamp158',3, 158);\"><span  id='uploaded_image_158'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp159' id='file_lamp159' >    <input type='button' id='btn_159' name='btn_159' value='Upload'  onclick=\"upload('file_lamp159',4, 159);\"><span  id='uploaded_image_159'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp160' id='file_lamp160' >                                                                             <input type='button' id='btn_160' name='btn_160' value='Upload'  onclick=\"upload('file_lamp160',5, 160);\"><span  id='uploaded_image_160'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp161' id='file_lamp161' >                                                            <input type='button' id='btn_161' name='btn_161' value='Upload'  onclick=\"upload('file_lamp161',6, 161);\"><span  id='uploaded_image_161'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp162' id='file_lamp162' >                             <input type='button' id='btn_162' name='btn_162' value='Upload'  onclick=\"upload('file_lamp162',7, 162);\"><span  id='uploaded_image_162'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy KTP Para ahli Waris/penerima Hibah Wasiat </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp163' id='file_lamp163' >                                                           <input type='button' id='btn_163' name='btn_163' value='Upload'  onclick=\"upload('file_lamp163',13, 163);\"><span  id='uploaded_image_163'>".$berkas_lamp13."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy Surat/Keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp164' id='file_lamp164' >                                                                           <input type='button' id='btn_164' name='btn_164' value='Upload'  onclick=\"upload('file_lamp164',14, 164);\"><span  id='uploaded_image_164'>".$berkas_lamp14."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Fotocopy Surat Pernyataan Waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp165' id='file_lamp165' >                                                                              <input type='button' id='btn_165' name='btn_165' value='Upload'  onclick=\"upload('file_lamp165',15, 165);\"><span  id='uploaded_image_165'>".$berkas_lamp15."</span></td>
									</tr>
									<tr>
									<td>	
										<li>11. Fotocopy Surat Kuasa Waris dalam hal Dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp166' id='file_lamp166' >                                                              <input type='button' id='btn_166' name='btn_166' value='Upload'  onclick=\"upload('file_lamp166',16, 166);\"><span  id='uploaded_image_166'>".$berkas_lamp16."</span></td>
								</tr></table>
							</ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan32\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>
										<li>1. Formulir penyampaian SSPD BPHTB </li></td><td width='50%'> <input onchange='check_extension(this.value)' type='file' name='file_lamp167' id='file_lamp167' >																	 <input type='button' id='btn_167' name='btn_167' value='Upload'  onclick=\"upload('file_lamp167',1, 167);\"><span  id='uploaded_image_167'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp168' id='file_lamp168' >                                                                                                     <input type='button' id='btn_168' name='btn_168' value='Upload'  onclick=\"upload('file_lamp168',2, 168);\"><span  id='uploaded_image_168'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp169' id='file_lamp169' >                                                         <input type='button' id='btn_169' name='btn_169' value='Upload'  onclick=\"upload('file_lamp169',3, 169);\"><span  id='uploaded_image_169'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp170' id='file_lamp170' >    <input type='button' id='btn_170' name='btn_170' value='Upload'  onclick=\"upload('file_lamp170',4, 170);\"><span  id='uploaded_image_170'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp171' id='file_lamp171' >                                                                             <input type='button' id='btn_171' name='btn_171' value='Upload'  onclick=\"upload('file_lamp171',5, 171);\"><span  id='uploaded_image_171'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp172' id='file_lamp172' >                                                            <input type='button' id='btn_172' name='btn_172' value='Upload'  onclick=\"upload('file_lamp172',6, 172);\"><span  id='uploaded_image_172'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp173' id='file_lamp173' >                             <input type='button' id='btn_173' name='btn_173' value='Upload'  onclick=\"upload('file_lamp173',7, 173);\"><span  id='uploaded_image_173'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy KTP Pemberi dan Penerima Hibah yang masih berlaku </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp174' id='file_lamp174' >                                                   <input type='button' id='btn_174' name='btn_174' value='Upload'  onclick=\"upload('file_lamp174',11, 174);\"><span  id='uploaded_image_174'>".$berkas_lamp11."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Pertanyaan Hibah/Surat keterangan Hibah yang diketahui oleh Kepala Desa </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp175' id='file_lamp175' >                                      <input type='button' id='btn_175' name='btn_175' value='Upload'  onclick=\"upload('file_lamp175',12, 175);\"><span  id='uploaded_image_175'>".$berkas_lamp12."</span></td>
								</tr></table>		
                            </ul>
                        </td>
                      </tr>
					  <tr class=\"jnsPerolehan\" id=\"jnsPerolehan33\">
                        <td width=\"100%\" colspan=\"2\" valign=\"top\">
                            <ul id=\"lampiran\" style=\"margin-left: -20px;\">
								<table border='0'><tr>
									<td width='50%'>	
										<li>1. Formulir penyampaian SSPD BPHTB</li></td><td> <input onchange='check_extension(this.value)' type='file' name='file_lamp176' id='file_lamp176' >																				<input type='button' id='btn_176' name='btn_176' value='Upload'  onclick=\"upload('file_lamp176',1, 176);\"><span  id='uploaded_image_176'>".$berkas_lamp1."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>2. SSPD-BPHTB </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp177' id='file_lamp177' >                                                                                                    <input type='button' id='btn_177' name='btn_177' value='Upload'  onclick=\"upload('file_lamp177',2, 177);\"><span  id='uploaded_image_177'>".$berkas_lamp2."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>3. Fotocopy KTP WP yang masih berlaku/Keterangan domisili </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp178' id='file_lamp178' >                                                        <input type='button' id='btn_178' name='btn_178' value='Upload'  onclick=\"upload('file_lamp178',3, 61);\"><span  id='uploaded_image_178'>".$berkas_lamp3."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>4. Surat kuasa dari WP yang bermaterai dan Fotocopy KTP penerima kuasa yang masih berlaku dalam hal dikuasakan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp179' id='file_lamp179' >   <input type='button' id='btn_179' name='btn_179' value='Upload'  onclick=\"upload('file_lamp179',4, 179);\"><span  id='uploaded_image_179'>".$berkas_lamp4."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>5. Fotocopy SPPT yang sedang berjalan </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp180' id='file_lamp180' >                                                                            <input type='button' id='btn_180' name='btn_180' value='Upload'  onclick=\"upload('file_lamp180',5, 180);\"><span  id='uploaded_image_180'>".$berkas_lamp5."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>6. Fotocopy Bukti Pembayaran/Lunas PBB dari tahun 2009 </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp181' id='file_lamp181' >                                                           <input type='button' id='btn_181' name='btn_181' value='Upload'  onclick=\"upload('file_lamp181',6, 181);\"><span  id='uploaded_image_181'>".$berkas_lamp6."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>7. Fotocopy, Girik/Leter C yang ditandatangani oleh Camat/Kepala Desa dan dilegalisir </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp182' id='file_lamp182' >                            <input type='button' id='btn_182' name='btn_182' value='Upload'  onclick=\"upload('file_lamp182',7, 182);\"><span  id='uploaded_image_182'>".$berkas_lamp7."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>8. Fotocopy KTP para ahli waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp183' id='file_lamp183' >                                                                                <input type='button' id='btn_183' name='btn_183' value='Upload'  onclick=\"upload('file_lamp183',20, 183);\"><span  id='uploaded_image_183'>".$berkas_lamp20."</span></td>
									</tr>   
									<tr>    
									<td>	
										<li>9. Fotocopy Surat/keterangan Kematian </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp184' id='file_lamp184' >                                                                          <input type='button' id='btn_184' name='btn_184' value='Upload'  onclick=\"upload('file_lamp184',21, 184);\"><span  id='uploaded_image_184'>".$berkas_lamp21."</span></td>
									</tr>
									<tr>
									<td>	
										<li>10. Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp185' id='file_lamp185' >                                                                             <input type='button' id='btn_185' name='btn_185' value='Upload'  onclick=\"upload('file_lamp185',22, 185);\"><span  id='uploaded_image_185'>".$berkas_lamp22."</span></td>
									</tr>
									
									
									<!-- <tr>
									<td>	
										<li> Fotocopy Surat Pernyataan waris </li></td><td><input onchange='check_extension(this.value)' type='file' name='file_lamp186' id='file_lamp186' >                                                                             <input type='button' id='btn_186' name='btn_186' value='Upload'  onclick=\"upload('file_lamp186',23, 186);\"><span  id='uploaded_image_186'>".$berkas_lamp23."</span></td>
								</tr>-->
								</table>
							</ul>
                        </td>
                      </tr>
                      <tr><td colspan=\"2\"><h3>&nbsp;</h3></td></tr>                                                                          
                      <tr>
                        <td width=\"100%\" colspan=\"2\" valign=\"top\" align=\"center\">";
    $html .= (isset($_REQUEST['svcid'])) ? "<input type=\"button\" name=\"btn-save\" id=\"btn-simpan\" value=\"Update\" onclick='return checkfile();' />&nbsp;" : "<input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" onclick='return checkfile();' />&nbsp;";

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
    $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9, 5=> 6, 6=> 6, 7=> 9, 8 => 9, 9 => 6, 10 => 6, 11 => 9, 12 => 9, 13 => 6, 14 => 6, 21 => 9, 22 => 9);
    $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;
	$jp= @isset($_REQUEST['jnsPerolehan']) ? $_REQUEST['jnsPerolehan'] : "";
	for($i=1;$i<=146;$i++){
		if ($_REQUEST['file_lamp'.$i]!=""){
			
			echo $_REQUEST['file_lamp'.$i];
		
		}
	}
	//exit;
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
                    '%s','%s')", mysqli_escape_string($DBLink, $_POST['nop']), mysqli_escape_string($DBLink, $_POST['tglMasuk']), $lampiran, mysqli_escape_string($DBLink, $_SESSION['username']), mysqli_escape_string($DBLink, $_POST['alamatOp']), mysqli_escape_string($DBLink, $_POST['kelurahanOp']), mysqli_escape_string($DBLink, $_POST['kecamatanOp']), mysqli_escape_string($DBLink, $_POST['npwp']), mysqli_escape_string($DBLink, $_POST['namaWp']), mysqli_escape_string($DBLink, $_POST['jnsPerolehan']), mysqli_escape_string($DBLink, $_POST['noPel']), mysqli_escape_string($DBLink, $_POST['hargaTran']), mysqli_escape_string($DBLink, $_POST['telpWp']));

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
    $jumSyarat = array(1 => 6, 2 => 6, 3 => 9, 4 => 9, 5=> 6, 6=> 6, 7=> 9, 8 => 9, 9 => 6, 10 => 6, 11 => 9, 12 => 9, 13 => 6, 14 => 6, 21 => 9, 22 => 9);
    $status = (count($_POST['lampiran']) == $jumSyarat[$_POST['jnsPerolehan']]) ? 1 : 0;

    $qry = sprintf("UPDATE cppmod_ssb_berkas SET        
            CPM_BERKAS_NOPEL = '" . mysqli_escape_string($DBLink, $_POST['noPel']) . "',
            CPM_BERKAS_LAMPIRAN ='{$lampiran}',
            CPM_BERKAS_PETUGAS = '" . mysqli_escape_string($DBLink, $_SESSION['username']) . "',
                
            CPM_BERKAS_NOP = '" . mysqli_escape_string($DBLink, $_POST['nop']) . "',
            CPM_BERKAS_ALAMAT_OP = '" . mysqli_escape_string($DBLink, $_POST['alamatOp']) . "',
            CPM_BERKAS_KELURAHAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kelurahanOp']) . "', 
            CPM_BERKAS_KECAMATAN_OP = '" . mysqli_escape_string($DBLink, $_POST['kecamatanOp']) . "',
            CPM_BERKAS_NPWP = '" . mysqli_escape_string($DBLink, $_POST['npwp']) . "',
            CPM_BERKAS_NAMA_WP = '" . mysqli_escape_string($DBLink, $_POST['namaWp']) . "',  
            
            CPM_BERKAS_HARGA_TRAN = '" . mysqli_escape_string($DBLink, $_POST['hargaTran']) . "',
            CPM_BERKAS_TELP_WP = '" . mysqli_escape_string($DBLink, $_POST['telpWp']) . "',

            CPM_BERKAS_STATUS = '{$status}'
            WHERE CPM_BERKAS_ID = '" . mysqli_escape_string($DBLink, $_POST['idssb']) . "'");

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

