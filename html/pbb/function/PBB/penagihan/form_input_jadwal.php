<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/central/user-central.php");
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

function formPenerimaan($initData) {   
    global $a, $m, $appConfig, $arConfig, $mode;
	// print_r($initData);
	$jam1 		= isset($initData['SP_JAM'])?substr($initData['SP_JAM'],0,2):'';
	$jam2 		= isset($initData['SP_JAM'])?substr($initData['SP_JAM'],3,2):'';
	$tahun		= isset($initData['SP_TAHUN'])?explode(',',$initData['SP_TAHUN']):'';
	$c			= isset($tahun)?count($tahun):'';
	// echo $c;
	$tgl		= '';
	if($initData){
		$tgl	= substr($initData['SP_TANGGAL'],8,2).'-'.substr($initData['SP_TANGGAL'],5,2).'-'.substr($initData['SP_TANGGAL'],0,4);
    }
	$bSlash = "\'";
    $ktip = "'";
	$cbTahun = "";
	for($i=0;$i<$c;$i++){
        if(isset($tahun[$i])){
            $cbTahun .= "<input type=\"checkbox\" name=\"tahun[]\" id=\"tahun\" value=\"".$tahun[$i]."\" checked readonly>".$tahun[$i]."<br>";
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
        $(document).ready(function(){
            $( \"input:submit, input:button\").button();
			$(\"#form-penerimaan\").submit(function(e){
				ids = 0;
				$.each($(\".attach:checked\"), function() {
					ids +=  parseInt($(this).val());
				});
				
				$(\"#attachment\").val(ids);
			});
		
			$('#tanggal').datepicker({dateFormat: 'dd-mm-yy'});
			
			";
			
    $params = "a=".$a."&m=".$m;
    $link = "main.php?param=".base64_encode($params."&f=".$arConfig['form_input_jadwal']);
        
	$html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });
            $(\"#form-penerimaan\").validate({
            rules : {
            nomor : \"required\",
            nop : \"required\",
            tahun : \"required\",
            tanggal : \"required\",
            jam1 : {
                required : true,
                number : true
            },
            jam2 : {
                required : true,
                number : true
            },
            acara : \"required\",
                tempat : \"required\"                          
            },
            messages : {
                nomor : \"Wajib diisi\",
				nop : \"Wajib diisi\",
				tahun : \"Wajib diisi\",
				jam1 : \"Wajib diisi\",
				jam2 : \"Wajib diisi\",
				acara : \"Wajib diisi\",
                tempat : \"Wajib diisi\"
            }
			});
            
        })
		
		function getDataTahun(){
				var nop 	= $.trim($('#nop').val());
				var dbhost	= '".$appConfig['GW_DBHOST']."';
				var dbuser	= '".$appConfig['GW_DBUSER']."';
				var dbpwd	= '".$appConfig['GW_DBPWD']."';
				var dbname	= '".$appConfig['GW_DBNAME']."';
				
				$.ajax({
					type: 'POST',
					url: './function/PBB/penagihan/svc-get-tahun-pemanggilan.php',
					data: 'nop='+nop+'&dbhost='+dbhost+'&dbuser='+dbuser+'&dbpwd='+dbpwd+'&dbname='+dbname,
					dataType : 'json', 
					success: function(d){
						$('#lbTahun').removeAttr('hidden');
						console.log(d.dataTahun);
						if(d.dataTahun==''){
								label = '<label>Data tidak ditemukan!</label>';
								document.getElementById('year').innerHTML = label;
								console.log(d);
						} else {
							var jml 	  = (d.dataTahun.TAHUN_SP2[0]).length;
							var i 		  = 0;
							$('#nama').val(d.dataTahun.WP_NAMA[i]);
							$('#tagihan').val(d.dataTahun.KETETAPAN_SP2[i]);
							var cBoxTahun = '';
							if(jml>0){
								while(i < jml){
									cBoxTahun += '<input type=\"checkbox\" name=\"tahun[]\" id=\"tahun\" value=\"'+d.dataTahun.TAHUN_SP2[0][i]+'\">'+d.dataTahun.TAHUN_SP2[0][i]+'<br>';	
									i++;
								}
								document.getElementById('year').innerHTML = cBoxTahun ;
								// alert(d.dataTahun.WP_NAMA[0]);
							}else {
								label = '<label><center>Data tidak ditemukan!</center></label>';
								document.getElementById('year').innerHTML = label;
								console.log(d);
							}
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

    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">";
    if(isset($hiddenModeInput)){
        $html .= $hiddenModeInput;
    }
	
	$html .= "<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                            <tr>
                              <td colspan=\"2\"><strong><font size=\"+2\">Form Surat Pemanggilan</font></strong><br /><hr><br /></td>
                            </tr>
                            <tr>
                              <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">&nbsp;</font></strong></td>
                              <td width=\"97%\"><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td width=\"39%\">Nomor</td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nomor\" id=\"nomor\" size=\"40\" maxlength=\"50\" value=\"".((isset($initData['SP_NOMOR']) && $initData['SP_NOMOR']!='')? $initData['SP_NOMOR']:'')."\" ".(($mode=='edit')? 'readonly=\"true\"':'')." placeholder=\"Nomor\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\"><label for=\"nop\">NOP</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nop\" id=\"nop\" size=\"50\" maxlength=\"22\" onblur=\"getDataTahun()\" value=\"".((isset($initData['SP_NOP']) && $initData['SP_NOP']!='')? $initData['SP_NOP']:'')."\" ".(($mode=='edit')? 'readonly=\"true\"':'')." placeholder=\"NOP\" />
                                        <!-- <input type=\"button\" name=\"btn-cek-NOP\" id=\"btn-cek\" onclick=\"getDataTahun()\" value=\"Cek\" /> -->
                                      </td>
                                    </tr>
									<tr>
										<td valign=\"top\"><label id=\"lbTahun\" ".(($mode=='edit')? '' :'hidden').">Tahun Pajak</label></td><td id=\"year\">".(($mode=='edit')? $cbTahun :'')."</td>
									</tr>
                                    <tr><td colspan=\"2\"><input type=\"hidden\" name=\"nama\" id=\"nama\" size=\"40\" maxlength=\"50\"/><input type=\"hidden\" name=\"tagihan\" id=\"tagihan\" size=\"40\" maxlength=\"50\"/><h3><br>Jadwal Pemanggilan</h3></td></tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"tanggal\">Tanggal</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"tanggal\" id=\"tanggal\" size=\"10\" maxlength=\"10\" value=\"".(($tgl!='')? $tgl:'')."\" placeholder=\"Tanggal\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"jam\">Jam</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"jam1\" id=\"jam1\" size=\"2\" maxlength=\"2\" value=\"".(($jam1!='')? $jam1:'')."\" placeholder=\"00\"/> : 
										<input type=\"text\" name=\"jam2\" id=\"jam2\" size=\"2\" maxlength=\"2\" value=\"".(($jam2!='')? $jam2:'')."\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"acara\">Acara</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"acara\" id=\"acara\" size=\"40\" maxlength=\"40\" value=\"".((isset($initData['SP_ACARA']) && $initData['SP_ACARA']!='')? $initData['SP_ACARA']:'')."\" placeholder=\"Acara\"/>&nbsp;
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"39%\"><label for=\"tempat\">Tempat</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"tempat\" id=\"tempat\" size=\"40\" maxlength=\"40\" value=\"".((isset($initData['SP_TEMPAT']) && $initData['SP_TEMPAT']!='')? $initData['SP_TEMPAT']:'')."\" placeholder=\"Tempat\"/>&nbsp;
                                      </td>
                                    </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td colspan=\"2\">&nbsp;</td>
                            </tr>                        
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">
                                <hr><br><input type=\"submit\" name=\"btn-save\" id=\"btn-simpan\" value=\"Simpan\" />
								&nbsp;
                                <input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."&f=".$arConfig['fProgresSP']."")."\"' />
							  </td>
                            </tr>
                            <tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
                            </tr>
                      </table>
                    </form></div>";
    return $html;
}

function getInitData($id=""){    
    global $DBLink, $GWDBLink;

    if($id == '') return getDataDefault();
	
    $qry = "SELECT * FROM pbb_spt_pemanggilan WHERE SP_NOMOR = '{$id}' ";
	
    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        //echo $qry ."<br>";
        //echo mysqli_error($DBLink);
		return getDataDefault();
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			return $row;
        }                
    }
}

function getDataDefault(){
        $default = array();
        return $default;        
}

// function getLastNumber($suffix){
    // global $DBLink;	
    
	// $qry = "select max(CPM_NO) as CPM_NO from cppmod_pbb_generate_service_number where CPM_ID like '%{$suffix}'";
	
    // $res = mysqli_query($DBLink, $qry);
    // if (!$res){
        // echo $qry ."<br>";
        // echo mysqli_error($DBLink);
    // }
    // else{
        // while ($row = mysqli_fetch_assoc($res)) {
            // return $row['CPM_NO'];
        // }
		
		// return "0";
    // }
// }

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
    global $data, $DBLink, $uname, $GWDBLink, $arConfig,$mode;
    
    // $mode  		= @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
	// if($mode == 'edit')
		// $nomor 	= $_REQUEST['nomor'];
    // else $nomor = generateNumber($_REQUEST['tahun'],$_REQUEST['jnsBerkas']);
	// echo "test";exit;
	$arrNmHari	= array(1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu');
    $nomor 		= $_REQUEST['nomor'];
    $nop 		= $_REQUEST['nop'];
    $tahun 		= implode(',',$_REQUEST['tahun']);
	$nama		= mysqli_real_escape_string($GWDBLink, $_REQUEST['nama']);
	$tagihan	= $_REQUEST['tagihan'];
	$timestamp	= strtotime($_REQUEST['tanggal']);
	$hari		= date('N', $timestamp);;
    $jam1 		= $_REQUEST['jam1'];
    $jam2 		= $_REQUEST['jam2'];
	$jam		= $jam1.":".$jam2;
    $acara		= mysqli_real_escape_string($GWDBLink, $_REQUEST['acara']);
	$tempat		= mysqli_real_escape_string($GWDBLink, $_REQUEST['tempat']);
	$tanggal	= substr($_REQUEST['tanggal'],6,4).'-'.substr($_REQUEST['tanggal'],3,2).'-'.substr($_REQUEST['tanggal'],0,2);
	
	if($mode == 'edit'){
		$qry = "UPDATE pbb_sppt_pemanggilan SET SP_HARI = '$arrNmHari[$hari]', SP_TANGGAL = '$tanggal', SP_JAM = '$jam', SP_ACARA = '$acara', SP_TEMPAT = '$tempat' WHERE SP_NOMOR = '$nomor'";
		$res = mysqli_query($GWDBLink, $qry);
	    if ( $res === false ){
	            echo $qry ."<br>";
	            echo mysqli_error($DBLink);
	    }
		
	    if($res){
	        echo 'Data berhasil disimpan...!';
	        $params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&f=".$arConfig['fProgresSP'];
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
	        $qry = "INSERT INTO pbb_sppt_pemanggilan VALUES ('{$nomor}','{$nop}','{$tahun}','{$nama}','{$arrNmHari[$hari]}','{$tanggal}','{$jam}','{$acara}','{$tempat}','{$tagihan}')";
		
			$res2 = mysqli_query($GWDBLink, $qry);
		    if ( $res2 === false ){
		            echo $qry ."<br>";
		            echo mysqli_error($DBLink);
		    }
			
		    if($res2){
				echo 'Data berhasil disimpan...!';
		        $params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&f=".$arConfig['fProgresSP'];
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
	
}

$appConfig 	= $User->GetAppConfig($application);	
$arConfig 	= $User->GetModuleConfig($m);
$save 		= isset($_REQUEST['btn-save'])?$_REQUEST['btn-save']:'';
$mode		= $_REQUEST['mode'];
// print_r($_REQUEST);
$db_host 	= $appConfig['GW_DBHOST'];
$db_name 	= $appConfig['GW_DBNAME'];
$db_user 	= $appConfig['GW_DBUSER'];
$db_pwd 	= $appConfig['GW_DBPWD'];

$GWDBLink = mysqli_connect($db_host,$db_user,$db_pwd, $db_name) or die(mysqli_error($DBLink));
//mysql_select_db($db_name,$GWDBLink);
if($save == 'Simpan') {
    save(0);
} else {
    $svcid  	= @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
	$initData 	= getInitData($svcid);
	// print_r($initData);
        
	echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
	echo formPenerimaan($initData);	
}

?>
