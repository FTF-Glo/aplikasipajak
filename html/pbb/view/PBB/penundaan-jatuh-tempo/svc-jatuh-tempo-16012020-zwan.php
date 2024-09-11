<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'penundaan-jatuh-tempo', '', dirname(__FILE__))).'/';
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


/*proses simpan / delete pembentukkan */
if(isset($_POST['action'])){
	// print_r($_REQUEST);
	
	$response['msg'] = 'Proses data berhasil.';
	
	$kec = $_POST['kec'];
	$kel = $_POST['kel'];
	$nop = $_POST['nop'];
	$thn = $_POST['thn'];
	$tgl_jatuh_tempo = $_POST['tgl_jatuh_tempo'];

	if($_POST['action'] == 'btn-save'){
		$GW_DBHOST = $appConfig['GW_DBHOST'];
		$GW_DBUSER = $appConfig['GW_DBUSER'];
		$GW_DBPWD = $appConfig['GW_DBPWD'];
		$GW_DBNAME = $appConfig['GW_DBNAME'];
		$GWDBLink = mysqli_connect($GW_DBHOST,$GW_DBUSER,$GW_DBPWD,$GW_DBNAME) or die(mysqli_error($DBLink));
		//mysql_select_db($GW_DBNAME,$GWDBLink);
		// var_dump($_REQUEST); exit();
		if(empty($nop)){
			$nop = $appConfig['KODE_KOTA'];
			$nop = empty($kel)? $nop : $kel;
			$nop = empty($kec)? $nop : $kec;
			
			$where = " NOP LIKE '{$nop}%' ";
		}else{
			$where = " NOP = '{$nop}' ";
		}
		$buku = @isset($_REQUEST['buku']) ? $_REQUEST['buku'] : "";
		$qBuku = "";
		 if($buku != 0){
		 switch ($buku){
		 case 1 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 100000) "; break;
		 case 12 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
		 case 123 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 1234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 12345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 0 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 2 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 500000) "; break;
		 case 23 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 234 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 2345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 100001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 3 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 2000000) "; break;
		 case 34 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 345 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 500001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 4 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 5000000) "; break;
		 case 45 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 2000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 case 5 : $qBuku = " AND (SPPT_PBB_HARUS_DIBAYAR >= 5000001 AND SPPT_PBB_HARUS_DIBAYAR <= 999999999999999) "; break;
		 }
		 }
		
		#update data pbb sppt (GW)
		$param = array(
			"SPPT_TANGGAL_JATUH_TEMPO = '{$tgl_jatuh_tempo}'"
		);
		
		$sets = implode(',',$param); 
		$query = "update PBB_SPPT set {$sets} where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL) $qBuku;";
		$query1 = "UPDATE SW_PBB.cppmod_pbb_sppt_cetak_{$thn} SET {$sets} WHERE SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} ;";
		// echo $query;exit;
		$res = mysqli_query($GWDBLink, $query);
		$res1 = mysqli_query($GWDBLink, $query1);
		
		if($res){
			$query = "select count(*) as TOTAL from PBB_SPPT where SPPT_TAHUN_PAJAK = '{$thn}' AND {$where} AND (PAYMENT_FLAG != '1' OR PAYMENT_FLAG IS NULL $qBuku;)";
			$res = mysqli_query($GWDBLink, $query);
			$data = mysqli_fetch_assoc($res);
			$response['msg'] = 'Data berhasil diproses sejumlah : '.$data['TOTAL'];
		}else{
			$response['msg'] = 'Data gagal diproses';
		}
		
	}
	
	exit($json->encode($response));
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
	      	$digit3 = " - ".substr($row['CPC_TKC_ID'],4,3);
            $tmp = array(
                'id' => $row['CPC_TKC_ID'],
                'pid' => $row['CPC_TKC_KKID'],
                'name' => $row['CPC_TKC_KECAMATAN'].$digit3
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
	      	$digit3 = " - ".substr($row['CPC_TKL_ID'],7,3);
            $tmp = array(
                'id' => $row['CPC_TKL_ID'],
                'pid' => $row['CPC_TKL_KCID'],
                'name' => $row['CPC_TKL_KELURAHAN'].$digit3
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

$optionKecOP = "<option value=''>Semua Kecamatan</option>";
foreach($kecOP as $row){
	$optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
}
$optionKelOP = "<option value=''>Semua Kelurahan</option>";


$html ="
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab1\">
	<form name=\"form-penerimaan\" nilai='btn-save' id=\"form-penerimaan\" method=\"post\" action=\"\">
	<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
		<tr><td colspan=\"2\"><strong><font size=\"+1\">DATA OBJEK PAJAK</font></strong><hr/></td></tr>
		<tbody id=\"info_lengkap\">
		<tr>
		  <td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
		  <td width=\"60%\">
			<select name=\"propinsiOP\" id=\"propinsiOP\" style=\"width:150px\">$optionProvOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
		  <td width=\"60%\">
			<select name=\"kabupatenOP\" id=\"kabupatenOP\" style=\"width:150px\">$optionCityOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kecamatanOP\">Kecamatan</label></td>
		  <td width=\"60%\">
			<select name=\"kecamatanOP\" id=\"kecamatanOP\" style=\"width:150px\">$optionKecOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
		  <td width=\"60%\">
			<select name=\"kelurahanOP\" id=\"kelurahanOP\" style=\"width:150px\">$optionKelOP</select>
		  </td>
		</tr>
		</tbody>
		<tr>
		  <td width=\"39%\">Tahun Pajak</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" onkeypress=\"return iniAngka(event, this)\"  maxlength=\"4\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$appConfig['tahun_tagihan'])."\" placeholder=\"Tahun\" />
		  </td>
		</tr>

		<tr>
		  <td width=\"39%\">Buku</td>
		  <td width=\"60%\">
		  	<select name=\"buku\" id=\"buku\">
		  		<option value='1'>Buku 1</option>
		  		<option value='12'>Buku 2</option>
		  		<option value='123'>Buku 3</option>
		  		<option value='1234'>Buku 4</option>
		  		<option value='12345'>Buku 5</option>
		  	</select>
		  </td>
		</tr>

		<tr>
		  <td width=\"39%\">Tanggal Jatuh Tempo Baru</td>
		  <td width=\"60%\">
			<input type=\"text\" name=\"tgl_jatuh_tempo\" id=\"tgl_jatuh_tempo\" size=\"20\" readonly placeholder=\"Tanggal Jatuh Tempo\"/>
		  </td>
		</tr>

		<tr>
		  <td width=\"\">NOP</td>
		  <td width=\"\" colspan=\"4\">
			<input type=\"text\" name=\"nop\" id=\"nop\" size=\"30\" onkeypress=\"return iniAngka(event, this)\" maxlength=\"18\" placeholder=\"Centang untuk memasukan NOP\" readonly/>
			<input type=\"checkbox\" id=\"only_nop\"> <label for=\"only_nop\">Proses NOP ini saja.</label>
		  </td>
		</tr>
		<tr>
		  <td colspan=\"2\" valign=\"middle\">&nbsp;<hr/></td>
		</tr>
		<tr>
		  <td colspan=\"2\" align=\"center\" valign=\"middle\">
			<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Ubah Jatuh Tempo\" />&nbsp;
		  </td>
		</tr>
	</table>
	</form>
</div>";
echo $html;
?>

<script>
$(document).ready(function(){
	
	$('#tgl_jatuh_tempo').datepicker({dateFormat:'yy-mm-dd'});
	
	$('.tab1 #kecamatanOP').change(function(){
		if($(this).val() == ''){
			var msg = '<option value>Semua Kelurahan</option>';
			$('.tab1 #kelurahanOP').html(msg);
		}else{
			$.ajax({
			   type: 'POST',
			   url: './function/PBB/loket/svc-search-city.php',
			   data: 'type=3&id='+$(this).val(),
			   success: function(msg){
					var opt = '<option value>Semua Kelurahan</option>';
					opt += msg;
					$('.tab1 #kelurahanOP').html(opt);
			   }
			});
		}
	});
	
	$('#only_nop').click(function(){
		if($(this).is(':checked')){
			$('#info_lengkap').hide();
			$('.tab1 #nop').removeAttr('readonly').attr('placeholder','Masukkan NOP');
		}else{
			$('.tab1 #nop').attr('readonly','readonly').attr('placeholder','Centang untuk memasukkan NOP').val('');
			$('#info_lengkap').show();
		}
	});
	
	// $('.tab1 #btn-save').click(function(){

	// });
	$("#form-penerimaan").submit(function(e){
		e.preventDefault();
		// alert ('123');
		// return false;
		
		var $btn = $(this);
		var kec = $('.tab1 #kecamatanOP').val();
		var kel = $('.tab1 #kelurahanOP').val();
		var thn = $('.tab1 #tahun').val();
		var buku = $('.tab1 #buku').val();
		
		var $nop = $('.tab1 #nop');
		var nop = $nop.val();
		var $tgl_jatuh_tempo = $('.tab1 #tgl_jatuh_tempo');
		var only_nop = $('#only_nop').is(':checked');
		
		if($tgl_jatuh_tempo.val() == ''){
			$tgl_jatuh_tempo.focus();
			alert('Silakan isi jatuh tempo');
			return false;
		}
		
		if(only_nop){
			if($.trim($nop.val()).length != 18){
				$nop.focus();
				alert('Silakan Isi NOP (18 Karakter).');
				return false;
			}else{
				if(confirm('Apakah anda yakin untuk mengubah jatuh tempo untuk \nNOP '+nop+' ini ?') === false) return false;
			}
		}else{
			nop = '';
			var nmprop = $('.tab1 #propinsiOP option:selected').text();
			var nmkab = $('.tab1 #kabupatenOP option:selected').text();
			var nmkec = $('.tab1 #kecamatanOP option:selected').text();
			var nmkel = $('.tab1 #kelurahanOP option:selected').text();
			var ask = 'Apakah anda yakin untuk mengubah jatuh tempo untuk';
			ask += '\nPropinsi : '+nmprop;
			ask += '\nKabupaten : '+nmkab;
			ask += '\nKecamatan : '+nmkec;
			ask += '\nKelurahan : '+nmkel;
			
			if(confirm(ask) === false) return false;
		}
		
		$btn.attr('disabled',true);
		$.ajax({
			type: 'POST',
			url: './view/PBB/penundaan-jatuh-tempo/svc-jatuh-tempo.php',
			dataType : 'json',
			data: {
				// action:$(this).attr('id'),
				action:$(this).attr('nilai'),
				kec:kec,
				kel:kel,
				nop:nop,
				thn:thn,
				buku:buku,
				tgl_jatuh_tempo:$tgl_jatuh_tempo.val(),
				q:'<?php echo $_REQUEST['q']?>'
			},
			success: function(res){
				alert(res.msg);
				$btn.removeAttr('disabled');
				setTabs(0);
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
