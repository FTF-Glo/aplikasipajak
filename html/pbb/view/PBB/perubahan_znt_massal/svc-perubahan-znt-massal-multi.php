<?php 

session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'perubahan_znt_massal', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

/* inisiasi parameter */
$q 		= @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$page 	= @isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$find 	= @isset($_REQUEST['find']) ? $_REQUEST['find'] : "";
$np 	= @isset($_REQUEST['np']) ? $_REQUEST['np'] :1;

$q = base64_decode($q);
$q = $json->decode($q);

$a = $q->a;
$m = $q->m;
$n = $q->n;
$tab 	= $q->tab;
$uname 	= $q->u;
$uid 	= $q->uid;

$User 		= new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
$arConfig 	= $User->GetModuleConfig($m);
$appConfig 	= $User->GetAppConfig($a);

// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";
// exit;
/*proses simpan / update znt */
if(isset($_POST['action'])){
	$response['msg'] = 'Proses data berhasil.';
	
	// print_r($_REQUEST['listNOP_multi']);
	// exit;
	
	$nop1 				= $_POST['nop1'];
	$nop2 				= $_POST['nop2'];
	$old_znt 			= $_POST['old_znt'];
	$new_znt 			= $_POST['new_znt'];
	$no_doc 			= $_POST['no_doc'];
	$tgl_pendataan 		= $_POST['tgl_pendataan'];
	$nip_pendata 		= $_POST['nip_pendata'];
	$tgl_pemeriksaan 	= $_POST['tgl_pemeriksaan'];
	$nip_pemeriksa 		= $_POST['nip_pemeriksa'];

	if($_POST['action'] == 'btn-save'){
	
		$listNOP = explode(",",$_REQUEST['listNOP_multi']);
		foreach ($listNOP as $key2 => $value2) {
			$value2 = trim($value2);
			$string_nop.= "'$value2',";
		}
		$string_nop =rtrim($string_nop,",");


		// echo $string_nop;
		// $listNOP = getListNOP($nop1,$nop2);
		// $c = count($listNOP);
		$c = count($listNOP);
		if($c>0){ // jika data tidak ditemukan
			// $valZNT = array();
			$valZNT['KD_ZNT_BARU'] 	= $new_znt;
			$valZNT['STATUS'] 		= 0;
			$valZNT['TGL_INPUT'] 	= date("Y-m-d H:i:s");
			// $response['msg'] = print_r($valZNT);
		

			$counter = 0;
			foreach($listNOP as $val){

				$valZNT['NOP'] 	= $val;		
				$valZNT['KD_ZNT_LAMA'] = getZNT($val);
				$addTempZNTMassal = addToTempZNTMassal($valZNT);
				if ($addTempZNTMassal){	
					$counter++;
				}
				// else{
				// 	$counter = "999999999999";
				// }
			}

			// exit;
			// echo "<pre>";
			// print_r($valZNT);
			// echo "</pre>";
			// exit;
			
			$valDoc['DOK_NOMOR'] 			= $no_doc;
			$valDoc['DOK_TGL_PENDATAAN'] 	= $tgl_pendataan;
			$valDoc['DOK_NIP_PENDATA'] 		= $nip_pendata;
			$valDoc['DOK_TGL_PEMERIKSAAN'] 	= $tgl_pemeriksaan;
			$valDoc['DOK_NIP_PEMERIKSA'] 	= $nip_pemeriksa;
			$valDoc['DOK_TGL_PEREKAMAN'] 	= date("Y-m-d");
			$valDoc['DOK_NIP_PEREKAMAN'] 	= $uname;
			
			$bOK = addToDocumentZNTMassal($valDoc);
			
			if($bOK){
				$bOK = updateFinal();
				if($bOK){
					$bOK = updateSusulan();
					if($bOK){
						$response['msg'] = 'Data berhasil diproses sejumlah : '.$counter;
					} else {
						$response['msg'] = 'Gagal update ZNT : ERR02';
					}
				} else {
					$response['msg'] = 'Gagal update ZNT : ERR01';
				}
			} else {
				$response['msg'] = 'Gagal input dokumen';
			}
		}else{
			$response['msg'] = 'Data tidak ditemukan';
		}
		
	}
	exit($json->encode($response));
}


if (isset($_REQUEST['cari'])){
	echo "cari dlu";
}

function getZNT($nop){
	global $DBLink,$appConfig;
	$nop = trim($nop);
	$query = "SELECT CPM_OT_ZONA_NILAI FROM cppmod_pbb_sppt_final  WHERE CPM_NOP = '$nop' ";
	 $res = mysqli_query($DBLink, $query);
	 // echo mysqli_num_rows($res);

	 if (mysqli_num_rows($res)==0){
		$query = "SELECT CPM_OT_ZONA_NILAI FROM cppmod_pbb_sppt_susulan  WHERE CPM_NOP = '$nop' ";
		 $res = mysqli_query($DBLink, $query);
		 if (mysqli_num_rows($res)<1){
		 	return false;
		 }
	 }


    if ($res==false){
        // echo $query ."<br>";
        // echo mysqli_error($DBLink);
        return false;
    }else{
    	$data = mysqli_fetch_assoc($res); 
    	return $data['CPM_OT_ZONA_NILAI'];
    	// return mysqli_num_rows($data);
    	// print_r($data);
    	// return $data['CPM_OT_ZONA_NILAI'] ;
    }

}
function getListNOP($nop1,$nop2){
	global $DBLink,$appConfig,$old_znt;
	
	$thn_tagihan = $appConfig['tahun_tagihan'];
	
	$query = "SELECT CPM_NOP FROM cppmod_pbb_sppt_final WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL) AND CPM_OT_ZONA_NILAI = '{$old_znt}'
			 UNION
			 SELECT CPM_NOP FROM cppmod_pbb_sppt_susulan WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL) AND CPM_OT_ZONA_NILAI = '{$old_znt}'";
	// echo $query; exit;
    $res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row['CPM_NOP'];
        }        
        return $data;
    }
	
}

function updateFinal(){
	global $DBLink,$appConfig,$nop1,$nop2,$old_znt,$new_znt,$string_nop;
	
	$thn_tagihan = $appConfig['tahun_tagihan'];
	
	// $query = "UPDATE cppmod_pbb_sppt_final SET CPM_OT_ZONA_NILAI = '{$new_znt}' 
	// 		  WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' 
	// 		  AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL)
	// 		  AND CPM_OT_ZONA_NILAI = '{$old_znt}' ";
	// BY 35U
		$new_znt = strtoupper($new_znt);
      $query = "UPDATE cppmod_pbb_sppt_final SET CPM_OT_ZONA_NILAI = '{$new_znt}' 
	  WHERE CPM_NOP in ($string_nop) ";
	 
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
	
	return $res;
}

function updateSusulan(){
	global $DBLink,$appConfig,$nop1,$nop2,$old_znt,$new_znt,$string_nop;
	$new_znt = strtoupper($new_znt);
	
	$thn_tagihan = $appConfig['tahun_tagihan'];
	
	// $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_OT_ZONA_NILAI = '{$new_znt}' 
	// 		  WHERE CPM_NOP BETWEEN '{$nop1}' AND '{$nop2}' 
	// 		  AND (CPM_SPPT_THN_PENETAPAN <> '{$thn_tagihan}' OR CPM_SPPT_THN_PENETAPAN IS NULL)
	// 		  AND CPM_OT_ZONA_NILAI = '{$old_znt}' ";
	  $query = "UPDATE cppmod_pbb_sppt_susulan SET CPM_OT_ZONA_NILAI = '{$new_znt}' 
	  WHERE CPM_NOP in ($string_nop) ";
	 

	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
	
	return $res;
}

function addToTempZNTMassal($post){
	global $DBLink;
	
	foreach($post as $key => $val) {
		$val = mysql_real_escape_string(trim($val));
		$colName[] = $key;
		$colVal[] = "'$val'";
	}
	$colName = implode(',', $colName);
	$colVal = implode(',', $colVal);
	$query = "INSERT INTO cppmod_pbb_temp_znt_massal (".$colName.") VALUES(".$colVal.")";

	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
	
	return $res;
}


function addToDocumentZNTMassal($post){
	global $DBLink;
	
	foreach($post as $key => $val) {
		$val = mysql_real_escape_string(trim($val));
		$colName[] = $key;
		$colVal[] = "'$val'";
	}
	$colName = implode(',', $colName);
	$colVal = implode(',', $colVal);
	$query = "INSERT INTO cppmod_pbb_dokumen_znt_massal (".$colName.") VALUES(".$colVal.")";
	// echo $query; exit;
	$res = mysqli_query($DBLink, $query);
    if (!$res){
        echo $query ."<br>";
        echo mysqli_error($DBLink);
    }
	
	return $res;
}


/*form penbentukan */
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

function getTahun($awal = 0,$akhir = 0){
	$awal = $awal == 0? date('Y')-5 : $awal;
	$akhir = $akhir == 0? date('Y') : $akhir;
	
	$optTahun = "";
	for($x = $akhir; $x>=$awal; $x--){
		$optTahun.= "<option value='{$x}'>{$x}</option>";            
	}
	return $optTahun;
}

$cityID = $appConfig['KODE_KOTA'];
$cityName = $appConfig['NAMA_KOTA'];
$optionCityOP = "<option valued=$cityID>$cityName</option>";

$provID = $appConfig['KODE_PROVINSI'];
$provName = $appConfig['NAMA_PROVINSI'];
$optionProvOP = "<option valued=$provID>$provName</option>";

$hiddenIdInput = $nomor = '';
$kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;

$kecOP = getKecamatan('',$cityID);

$optionKecOP = "<option value=''>Kecamatan</option>";
foreach($kecOP as $row){
	$optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
}
$optionKelOP = "<option value=''>Kelurahan</option>";

			
$html ="
<div class='full'>
	<div class='loader'></div>
</div>
<style type='text/css'>
	.full{
		width:100%;
		height:100%;
		position:fixed;
		z-index:100;
		background:rgba(255,255,255,0.8);
		left:0px;
		top:0px;
		display:none;
	}
	.loader {
		position:absolute;
		top:0px;
		bottom:0px;
		left:0px;
		right:0px;
		margin:auto;
	    border: 16px solid #f3f3f3; /* Light grey */
	    border-top: 16px solid #3498db; /* Blue */
	    border-radius: 50%;
	    width: 120px;
	    height: 120px;
	    animation: spin 2s linear infinite;
	}

	@keyframes spin {
	    0% { transform: rotate(0deg); }
	    100% { transform: rotate(360deg); }
	}
	.file-shorcut{
		width: 90px;
		height: 20px;
		padding:10px;
		background: #B3C8FF;
		color: black;
		text-align: center;
		border-radius: 10px;
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		cursor: pointer;
	}
</style>

<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab1\">

	<table style='display:none'>
		<tr>
			<td>
			<form   id='TheForm-upload-csv' method='post' enctype='multipart/form-data' action='view/PBB/pembatalan-sppt/svc-get-csv-data.php'>
			<input class='item-hide-file' type='file' name='file' accept='application/vnd.ms-excel' required /> 
			</form>
			</td>
		</tr>
	</table>
	<!--<input type=\"hidden\" name=\"provid\" id=\"provid\" size=\"26\" value=\"".$provID."\"/>
	<input type=\"hidden\" name=\"cityid\" id=\"cityid\" size=\"26\" value=\"".$cityID."\"/>-->
	<label for='multi'>
	<input type=\"radio\" name=\"tipeFilter\" id=\"multi\" value=\"multi\" checked> Filter Multiple NOP 
	</label>

	&nbsp;&nbsp;&nbsp;
	<label for='csv'>
	<input type=\"radio\" name=\"tipeFilter\" id=\"csv\" value=\"single\"> Upload CSV 
	</label>
	<form  name=\"form-penerimaan-multi\" id=\"form-penerimaan-multi\" method=\"post\" action=\"\">

	<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
		<tr><td colspan=\"2\"><strong><font size=\"+1\">DATA OBJEK PAJAK</font></strong><hr/></td></tr>
		<tbody id=\"info_lengkap\">
	

		</tbody>
		<tr>
		  <td width=\"39%\"><label for=\"kelurahanOP\">NOP</label></td>
		  <td width=\"60%\" >
			
			<p class='file-shorcut' onclick='ambilFile()' >Upload CSV</p>
				
			<textarea class='item-hide-textarea' name=\"NOP_multi\" id=\"NOP_multi\" style=\"width:350px;height:100px;\"></textarea>

			<textarea class='item-hide-textarea-temp' name=\"NOP_multi_temp\" id=\"NOP_multi_temp\" style=\"width:350px;height:100px;display:none\"></textarea>
			
							
		  	<input type='hidden' name='cari' value='ya' />
		  </td>
		</tr>
		
		
		
		<tr>
			<td>
			<input type=\"submit\" name=\"btn-cari\" id=\"btn-cari\" value=\"Cari\" />&nbsp;
			</td>
		</tr>

		</table>

		</form>
		<div id='frame-tbl-monitoring'>
	    	<div  id='monitoring-content-multi' class='monitoring-content'></div>
		</div>


		<hr>

		<form id='form-check-znt'>
			<table id='table-konfirmasi' style='display:none' cellpadding='3' border='0' width='500'>
			<tr>
				<td colspan='2'>
				<br>
				<br>
					<hr>
					<input type='hidden' value='$_REQUEST[q]' name='q' />
				</td>
				 <td rowspan='5' align='center'>
			  	<p style='color:red;font-size:20px;' class='nir-value'></p>
			  </td>
			</tr>

					<tr>
			  <td width=\"70px\"><label for=\"kecamatanOP\">Kecamatan</label></td>
			  <td width=\"100px\">
				<select name=\"kecamatanOP_multi\" id=\"kecamatanOP_multi\" style=\"width:150px\">$optionKecOP</select>
			  </td>
			</tr>
			<tr>
			  <td width=\"70px\"><label for=\"kelurahanOP_multi\">".$appConfig['LABEL_KELURAHAN']."</label></td>
			  <td width=\"100px\">
				<select name=\"kelurahanOP_multi\" id=\"kelurahanOP_multi\" style=\"width:150px\">$optionKelOP</select>
			  </td>
			</tr>
			<tr>
			  <td width=\"70px\">Kode ZNT Baru</td>
			  <td width=\"100px\">
				<input style='text-transform:uppercase' type=\"text\" name=\"kd_znt_baru\" id=\"kd_znt_baru_multi\" maxlength=\"2\" size=\"5\" placeholder=\"ZNT\"/>

			  </td>
			 
			</tr>

			<tr>
				<td colspan='2' width='300'>
					<input type='submit' value='Cek ZNT' />
				<br>
					<hr>
				</td>
			</tr>
			</table>
		</form>

		<form  name=\"form-penerimaan-multi-ubah\" id=\"form-penerimaan-multi-ubah\" method=\"post\" action=\"\">


		<table id='table-konfirmasi-2' style='display:none' cellpadding='3'>
		<tr><td colspan=\"2\"><strong><font size=\"+1\">
		<br>
		<br>
		DATA DOKUMEN</font></strong><hr/></td></tr>
		<tr>
		  <td width=\"39%\">Nomor Dokumen</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"no_doc\" id=\"no_doc_multi\" size=\"26\" placeholder=\"Nomor Dokumen\"/>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">Tanggal Pendataan</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"tgl_pendataan\" id=\"tgl_pendataan_multi\" size=\"10\" placeholder=\"Tanggal\"/>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">NIP Pendata</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"nip_pendata\" id=\"nip_pendata_multi\" size=\"26\" placeholder=\"NIP Pendata\"/>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">Tanggal Pemeriksaan</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"tgl_pemeriksaan\" id=\"tgl_pemeriksaan_multi\" size=\"10\" placeholder=\"Tanggal\"/>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">NIP Pemeriksa</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"nip_pemeriksa\" id=\"nip_pemeriksa_multi\" size=\"26\" placeholder=\"NIP Pemeriksa\"/>
		  </td>
		</tr>
		<tr>
		  <td colspan=\"2\" valign=\"middle\">&nbsp;<hr/></td>
		</tr>
		<tr>
		  <td colspan=\"2\" align=\"center\" valign=\"middle\">
			<input type=\"button\" name=\"btn-save\" id=\"btn-save\" value=\"Ubah ZNT\" />&nbsp;
		  </td>
		</tr>
	</table>
	</form>
</div>";
echo $html;
?>

<script>
function ambilFile(){	
	$(".item-hide-file").trigger("click");
}

$(document).ready(function(){
	$(".item-hide-file").css("opacity","0");
	$(".item-hide-textarea").show();
	$("#btn-cari").show();
	$(".file-shorcut").hide();


	$(document).on("keyup","#kd_znt_baru_multi",function(e){
		$("#table-konfirmasi-2").fadeOut();
		$(".nir-value").html("Silahkan Cek ZNT");
	});

	$(document).on("keyup","#NOP_multi",function(e){
		var ini = $(this).val();
		$("#NOP_multi_temp").val(ini);
	});
	$(document).on("click","#multi",function(e){

		$(".item-hide-file").css("opacity","0");
		$(".item-hide-textarea").show();
		$("#btn-cari").show();
		$(".file-shorcut").hide();
	});
	$(document).on("click","#csv",function(e){
		$("#btn-cari").hide();
		$(".item-hide-textarea").hide();
		$(".item-hide-file").css("opacity","1");
		$(".file-shorcut").show();
	});
	$(document).on("change",".item-hide-file",function(e){
		// alert("123");
		$("#TheForm-upload-csv").trigger("submit");
	});
	$(document).on("submit","#form-check-znt",function(e){
		e.preventDefault();
		$.ajax({
	        type: "POST",
	         // url: 'view/PBB/perubahan_znt_massal/svc-get-nop-multi.php',   
			url: "view/PBB/perubahan_znt_massal/svc-check-znt.php",
			// contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			// processData: false, // NEEDED, DON'T OMIT THIS
			data : $(this).serialize(),
			success: function(data){

				var data = JSON.parse(data);
				if (parseInt(data.NIR)>0){		
					$("html, body").animate({ scrollTop: $(document).height() }, 1000);
					$(".nir-value").html("Nilai NIR : <BR>"+data.NIR);
					$("#table-konfirmasi-2").show();
					$("#no_doc_multi").focus();
					// $("#kd_znt_baru_multi").attr("readonly",true);
				}else{
					$("#table-konfirmasi-2").hide();
					$(".nir-value").html("Kode ZNT tidak ditemukan di Kelurahan yang dipilih");
					// $("#kd_znt_baru_multi").removeAttr("readonly");
				}
	        },
			error : function(data){
				console.log(data)
			}
	    });
		
	});
	$(document).on("submit","#TheForm-upload-csv",function(e){
		e.preventDefault();
		$.ajax({
	        type: "POST",
	         // url: 'view/PBB/perubahan_znt_massal/svc-get-nop-multi.php',   
			url: "view/PBB/perubahan_znt_massal/svc-get-csv-data.php",
			contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			processData: false, // NEEDED, DON'T OMIT THIS
			data : new FormData(this),
			beforeSend : function(d){
				$(".full").show();
			},
			success: function(data){
				$(".full").hide();
				$('input[type="file"]').val("");
				$(".item-hide-textarea").val(data);	
				$(".item-hide-textarea-temp").val(data);	
				$("#form-penerimaan-multi").submit();	
	        },
			error : function(data){
				console.log(data)
			}
	    });


	});
	
	$('#tgl_pendataan').datepicker({dateFormat:'yy-mm-dd'});
	$('#tgl_pemeriksaan').datepicker({dateFormat:'yy-mm-dd'});
	$('#tgl_pendataan_multi').datepicker({dateFormat:'yy-mm-dd'});
	$('#tgl_pemeriksaan_multi').datepicker({dateFormat:'yy-mm-dd'});
	
	$(document).on("submit","#form-penerimaan-multi",function(e){
		e.preventDefault();
		var submit = $(this).find("input[type='submit']");
		$.ajax({
			type: 'POST',
			data : $(this).serialize(),
		    url: 'view/PBB/perubahan_znt_massal/svc-get-nop-multi.php',   
		    beforeSend:function(d){
		    	submit.attr("disabled","true");
		    },	
			success : function(d){
		    	submit.removeAttr("disabled");
				$(".full").hide();
				// $(".item-hide-textarea").val("");		


				$("#monitoring-content-multi").html(d);
				var num = jQuery("#monitoring-content-multi").find("#num-row").html();
				if ( parseInt(num) >0){
					$("#table-konfirmasi").show();
				}else{
					$("#table-konfirmasi").hide();
				}
			},
			beforeSend :function(d){
				$(".full").show();
			},
			error : function(d){
				alert(JSON.stringify(d));
			}
		});
	});
	$('.tab1 #kecamatanOP_multi').change(function(){
		if($(this).val() == ''){
			var msg = '<option value>Kelurahan</option>';
			$('.tab1 #kelurahanOP_multi').html(msg);
		}else{
			$.ajax({
			   type: 'POST',
			   url: './function/PBB/loket/svc-search-city.php',
			   data: 'type=3&id='+$(this).val(),
			   success: function(msg){
					var opt = '<option value>Kelurahan</option>';
					opt += msg;
					$('.tab1 #kelurahanOP_multi').html(opt);
			   }
			});
		}
	});
	
	$('.tab1 #btn-save').click(function(e){
		e.preventDefault();
		var $btn 	 		= $(this);
		var $kec	 	 	= $('.tab1 #kecamatanOP_multi');
		var $kel 	 		= $('.tab1 #kelurahanOP_multi');
		var $blok 	 		= $('.tab1 #blok_multi');
		var $old_znt  		= $('.tab1 #kd_znt_lama_multi');
		var $new_znt  		= $('.tab1 #kd_znt_baru_multi');
		var $no_urut1 		= $('.tab1 #no_urut1_multi');
		var $jenis1	 		= $('.tab1 #jenis1_multi');
		var $no_urut2 		= $('.tab1 #no_urut2_multi');
		var $jenis2	 		= $('.tab1 #jenis2_multi');
		var $no_doc	 		= $('.tab1 #no_doc_multi');
		var $tgl_pendataan	= $('.tab1 #tgl_pendataan_multi');
		var $nip_pendata	= $('.tab1 #nip_pendata_multi');
		var $tgl_pemeriksaan= $('.tab1 #tgl_pemeriksaan_multi');
		var $nip_pemeriksa	= $('.tab1 #nip_pemeriksa_multi');
		
		if($kec.val() == ''){
			$kec.focus();
			alert('Silakan pilih kecamatan');
			return false;
		} else if($kel.val() == ''){
			$kel.focus();
			alert('Silakan pilih kelurahan');
			return false;
		} else if($blok.val() == ''){
			$blok.focus();
			alert('Silakan isi blok');
			return false;
		} else if($old_znt.val() == ''){
			$old_znt.focus();
			alert('Silakan isi ZNT lama');
			return false;
		} else if($new_znt.val() == ''){
			$new_znt.focus();
			alert('Silakan isi ZNT baru');
			return false;
		} else if($no_urut1.val() == ''){
			$no_urut1.focus();
			alert('Silakan isi nomor urut 1');
			return false;
		} else if($jenis1.val() == ''){
			$jenis1.focus();
			alert('Silakan isi jenis NOP 1');
			return false;
		} else if($no_urut2.val() == ''){
			$no_urut2.focus();
			alert('Silakan nomor urut 2');
			return false;
		} else if($jenis2.val() == ''){
			$jenis2.focus();
			alert('Silakan isi jenis NOP 2');
			return false;
		}  else if($no_doc.val() == ''){
			$no_doc.focus();
			alert('Silakan isi nomor dokumen');
			return false;
		} else if($tgl_pendataan.val() == ''){
			$tgl_pendataan.focus();
			alert('Silakan isi tanggal pendataan');
			return false;
		} else if($nip_pendata.val() == ''){
			$nip_pendata.focus();
			alert('Silakan isi nip pendata');
			return false;
		} else if($tgl_pemeriksaan.val() == ''){
			$tgl_pemeriksaan.focus();
			alert('Silakan isi tanggal pendataan');
			return false;
		} else if($nip_pemeriksa.val() == ''){
			$nip_pemeriksa.focus();
			alert('Silakan isi nip pendata');
			return false;
		}
		
		var idkel 		= $kel.val();
		var blok		= $blok.val();
		var no_urut1 	= $no_urut1.val();
		var jenis1 		= $jenis1.val();
		var no_urut2 	= $no_urut2.val();
		var jenis2 		= $jenis2.val();
		var nop1		= idkel+blok+no_urut1+jenis1;
		var nop2		= idkel+blok+no_urut2+jenis2;
		var ask = 'Apakah anda yakin untuk mengubah ZNT untuk  '+$("#num-row").html()+' NOP ?';		
		if(confirm(ask) === false) return false;
		
		// $btn.attr('disabled',true);
		$.ajax({
			type: 'POST',
			url: './view/PBB/perubahan_znt_massal/svc-perubahan-znt-massal-multi.php',
			beforeSend : function(e){
				$(".full").show();
			},
			dataType : 'json',
			data: {
				action:$(this).attr('id'),
				// listNOP_multi:$("#NOP_multi_temp").val(),
				listNOP_multi:$("#NOP_multi").val(),
				nop1:nop1,
				nop2:nop2,
				old_znt:$old_znt.val(),
				new_znt:$new_znt.val(),
				no_doc:$no_doc.val(),
				tgl_pendataan:$tgl_pendataan.val(),
				nip_pendata:$nip_pendata.val(),
				tgl_pemeriksaan:$tgl_pemeriksaan.val(),
				nip_pemeriksa:$nip_pemeriksa.val(),
				q:'<?php echo $_REQUEST['q']?>'
			},
			success: function(res){
				alert(res.msg);
				$(".full").hide();
				$("#form-penerimaan-multi").submit();
				setTabs(1);
				// $btn.removeAttr('disabled');
			}
		});
	});
	
});

function iniAngka(evt,x){
	if ($(x).attr('readonly') == 'readonly') return false;
	var charCode = (evt.which) ? evt.which : event.keyCode;
	if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13){
		return true;
	}else{
		alert('Input hanya boleh angka!');
		return false;
	}
}
</script>
