<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penagihan', '', dirname(__FILE__))).'/';
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

function formPenerimaan($initData) {   
    global $a, $m, $appConfig, $arConfig, $mode;

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
    $link = "main.php?param=".base64_encode($params."&f=".$arConfig['form_kejaksaan']);
        
	$html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });
            $(\"#form-penerimaan\").validate({
				rules : {
					nomor : \"required\",
					nama : \"required\",
					jabatan : \"required\",
					alamat : \"required\"                      
				},
				messages : {
					nomor : \"Wajib diisi\",
					nama : \"Wajib diisi\",
					jabatan : \"Wajib diisi\",
					alamat : \"Wajib diisi\"
				}
			});
            
        })

    </script>
    <div id=\"main-content\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	$hiddenModeInput
	<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                            <tr>
                              <td colspan=\"2\"><strong><font size=\"+2\">Form Surat Pengantar Kejaksaan</font></strong><br /><hr><br /></td>
                            </tr>
                            <tr>
                              <td width=\"3%\" align=\"center\"><strong><font size=\"+2\">&nbsp;</font></strong></td>
                              <td width=\"97%\"><table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td width=\"39%\">Nomor</td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nomor\" id=\"nomor\" size=\"40\" maxlength=\"50\" value=\"".(($initData['SPK_NOMOR']!='')? $initData['SPK_NOMOR']:'')."\" ".(($mode=='edit')? 'readonly=\"true\"':'')." placeholder=\"Nomor\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\"><label for=\"nama\">Nama</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"nama\" id=\"nama\" size=\"50\" maxlength=\"22\" value=\"".(($initData['SPK_NAMA']!='')? $initData['SPK_NAMA']:'')."\" placeholder=\"Nama\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\"><label for=\"jabatan\">Jabatan</label></td>
                                      <td width=\"60%\">
                                        <input type=\"text\" name=\"jabatan\" id=\"jabatan\" size=\"50\" maxlength=\"22\" value=\"".(($initData['SPK_JABATAN']!='')? $initData['SPK_JABATAN']:'')."\" placeholder=\"Jabatan\" />
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"39%\"><label for=\"alamat\">Alamat</label></td>
                                      <td width=\"60%\">
										<!-- <textarea rows=\"4\" name=\"alamat\" id=\"alamat\" cols=\"45\" placeholder=\"Alamat\">".(($initData['SPK_ALAMAT']!='')? $initData['SPK_ALAMAT']:'')."</textarea> -->
                                        <input type=\"text\" name=\"alamat\" id=\"alamat\" size=\"50\" maxlength=\"100\" value=\"".(($initData['SPK_ALAMAT']!='')? $initData['SPK_ALAMAT']:'')."\" placeholder=\"Alamat\" />
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
	
    $qry = "SELECT * FROM SURAT_PENGANTAR_KEJAKSAAN WHERE SPK_NOMOR = '{$id}' ";
	
    $res = mysqli_query($GWDBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
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

function save($status){
    global $data, $DBLink, $uname, $GWDBLink, $arConfig,$mode;
    
    $nomor 		= $_REQUEST['nomor'];
    $nama		= mysql_real_escape_string($_REQUEST['nama']);
	$jabatan	= mysql_real_escape_string($_REQUEST['jabatan']);
	$alamat		= mysql_real_escape_string($_REQUEST['alamat']);
	
	if($mode == 'edit'){
		$qry = "UPDATE SURAT_PENGANTAR_KEJAKSAAN SET SPK_NAMA = '$nama', SPK_JABATAN = '$jabatan', SPK_ALAMAT = '$alamat' WHERE SPK_NOMOR = '$nomor'";
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
	        $qry = "INSERT INTO SURAT_PENGANTAR_KEJAKSAAN VALUES ('{$nomor}','{$nama}','{$jabatan}','{$alamat}')";
		
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
$save 		= $_REQUEST['btn-save'];
$mode		= $_REQUEST['mode'];
// print_r($_REQUEST);
$db_host 	= $appConfig['GW_DBHOST'];
$db_name 	= $appConfig['GW_DBNAME'];
$db_user 	= $appConfig['GW_DBUSER'];
$db_pwd 	= $appConfig['GW_DBPWD'];

$GWDBLink = mysqli_connect($db_host,$db_user,$db_pwd,$db_name) or die(mysqli_error($DBLink));
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
