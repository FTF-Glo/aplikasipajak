<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Jakarta');
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pemeliharaan-data-piutang', '', dirname(__FILE__))).'/';
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
	$response['msg'] = 'Proses data berhasil.';
	
	$kel = $_POST['kel'];
	$thn_awal = $_POST['thn_awal'];
	$thn_akhir = $_POST['thn_akhir'];
	$thn_kegiatan = $_POST['thn_kegiatan'];
	
	$GW_DBHOST = $appConfig['GW_DBHOST'];
	$GW_DBUSER = $appConfig['GW_DBUSER'];
	$GW_DBPWD = $appConfig['GW_DBPWD'];
	$GW_DBNAME = $appConfig['GW_DBNAME'];
	$GWDBLink = mysqli_connect($GW_DBHOST,$GW_DBUSER,$GW_DBPWD,$GW_DBNAME) or die(mysqli_error($DBLink));
	//mysql_select_db($GW_DBNAME,$GWDBLink);
		
	if($_POST['action'] == 'btn-save'){
		#mengambil list nop yang BELUM BAYAR dari pbb sppt (GW)
		$query = sprintf("SELECT * FROM $appConfig[ADMIN_GW_DBNAME].PBB_SPPT WHERE 
		SUBSTR(NOP,1,10) = '%s' 
		AND (SPPT_TAHUN_PAJAK >= '%s' AND SPPT_TAHUN_PAJAK <= '%s')
		AND (PAYMENT_FLAG!='1' OR PAYMENT_FLAG IS NULL ) ", $kel, $thn_awal, $thn_akhir);
		
		$rows = array();
		$res = mysqli_query($GWDBLink, $query);
		while($row = mysqli_fetch_assoc($res)){
			$rows[] = $row;
		}
		
		#jika data BELUM BAYAR tersedia
		$response['get_from_pbb_sppt'] = count($rows);
		if(count($rows)>0){

			#insert ke data pemeliharaan piutang (GW)
			$response['insert_to_pbb_sppt_piutang']=0;
			foreach($rows as $row){
				$param = array();
				$param['NOP'] = "'{$row['NOP']}'";
				$param['ALAMAT_OP'] = "'{$row['OP_ALAMAT']}'";
				$param['TAHUN_PAJAK'] = "'{$row['SPPT_TAHUN_PAJAK']}'";
				$param['TAHUN_KEGIATAN'] = "'{$thn_kegiatan}'";
				$param['CREATED_AT'] = "'". date('Y-m-d H:i:s') ."'";
				
				$fields = implode(',',array_keys($param));
				$values = implode(',',array_values($param));
				$query = "INSERT IGNORE INTO $appConfig[ADMIN_GW_DBNAME].PBB_SPPT_DATA_PIUTANG({$fields}) values ({$values})";				
				$res = mysqli_query($GWDBLink, $query);
				$response['insert_to_pbb_sppt_piutang'] += $res? 1 : 0;
			}
		}
		
		/*==============================================*/
		
		#mengambil list nop dari pbb sppt final (SW)
		$query = sprintf("SELECT * FROM $appConfig[ADMIN_SW_DBNAME].cppmod_pbb_sppt_final WHERE 
		SUBSTR(CPM_NOP,1,10) = '%s' ", $kel);
		
		$rows = array();
		$res = mysqli_query($DBLink, $query);
		while($row = mysqli_fetch_assoc($res)){
			$rows[] = $row;
		}
		
		#jika data FINAL tersedia
		$response['get_from_pbb_final'] = count($rows);
		if(count($rows)>0){
			
			//insert ke data pemeliharaan (SW)
			$response['insert_to_dafnom_op']=0;
			foreach($rows as $row){
				$param = array();
				$param['NOP'] = "'{$row['CPM_NOP']}'";
				$param['ALAMAT_OP'] = "'{$row['CPM_OP_ALAMAT']}'";
				$param['TAHUN_KEGIATAN'] = "'{$thn_kegiatan}'";
				$param['CREATED_AT'] = "'". date('Y-m-d H:i:s') ."'";
				
				$fields = implode(',',array_keys($param));
				$values = implode(',',array_values($param));
				$query = "INSERT IGNORE INTO $appConfig[ADMIN_SW_DBNAME].cppmod_dafnom_op({$fields}) values ({$values})";
				
				$res = mysqli_query($DBLink, $query);
				$response['insert_to_dafnom_op'] += $res? 1 : 0;
			}
		}
		
	}elseif($_POST['action'] == 'btn-delete'){
		$query = sprintf("DELETE FROM $appConfig[ADMIN_GW_DBNAME].PBB_SPPT_DATA_PIUTANG WHERE SUBSTR(NOP,1,10) = '%s'", $kel);
		$res = mysqli_query($GWDBLink, $query);
		
		$query = sprintf("DELETE FROM $appConfig[ADMIN_SW_DBNAME].cppmod_dafnom_op WHERE SUBSTR(NOP,1,10) = '%s'", $kel);
		$res = mysqli_query($DBLink, $query);
		
		$response['msg'] = 'Data berhasil dihapus';
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
$optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";

$kecOP = getKecamatan('',$cityID);
$kelOP = getKelurahan('',$kecOP[0]['id']);

foreach($kecOP as $row){
	$optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
}
foreach($kelOP as $row){
	$optionKelOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
}

$html ="
<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>
<div id=\"main-content\" class=\"tab1\">
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	<table width=\"747\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
		<tr><td colspan=\"2\"><strong><font size=\"+1\">DATA OBJEK PAJAK</font></strong><hr/></td></tr>
		<tr>
		  <td width=\"39%\"><label for=\"provinsiOP\">Provinsi</label></td>
		  <td width=\"60%\">
			<select class=\"form-control\" name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
		  <td width=\"60%\">
			<select class=\"form-control\" name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kecamatanOP\">Kecamatan</label></td>
		  <td width=\"60%\">
			<select class=\"form-control\" name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
		  <td width=\"60%\">
			<select class=\"form-control\" name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">Tahun Pajak</td>
		  <td width=\"60%\">
			<select class=\"form-control\" name=\"tahun_awal\" id=\"tahun_awal\" style=\"width:80%;max-width:130px!important;display: inline-block\" >".getTahun()."</select> s.d 
			<select class=\"form-control\" name=\"tahun_akhir\" id=\"tahun_akhir\" style=\"width:80%;max-width:130px!important;display: inline-block\" >".getTahun()."</select>
		  </td>
		</tr>
		<tr>
		  <td width=\"39%\">Tahun Kegiatan</td>
		  <td width=\"60%\">
			<input class=\"form-control\" type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" maxlength=\"4\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$appConfig['tahun_tagihan'])."\" placeholder=\"Tahun\" readonly />
		  </td>
		</tr>
		<tr>
		  <td colspan=\"2\" valign=\"middle\">&nbsp;<hr/></td>
		</tr>
		<tr>
		  <td colspan=\"2\" align=\"center\" valign=\"middle\">
			
			<input class=\"btn btn-primary bg-blue\" type=\"button\" name=\"btn-delete\" id=\"btn-delete\" value=\"Hapus\" />&nbsp;
			<input class=\"btn btn-primary bg-orange\" type=\"button\" name=\"btn-save\" id=\"btn-save\" value=\"Proses\" />&nbsp;
			  
		  </td>
		</tr>
	</table>
	</form>
</div>";
echo $html;
?>

<script>
$(document).ready(function(){
	$('.tab1 #kecamatanOP').change(function(){
		$.ajax({
		   type: 'POST',
		   url: './function/PBB/loket/svc-search-city.php',
		   data: 'type=3&id='+$(this).val(),
		   success: function(msg){
				$('.tab1 #kelurahanOP').html(msg);
		   }
		});
	});
	
	$('.tab1 #btn-save, .tab1 #btn-delete').click(function(){
		var $btn = $(this);
		var kel = $('.tab1 #kelurahanOP').val();
		var thn_awal = $('.tab1 #tahun_awal').val();
		var thn_akhir = $('.tab1 #tahun_akhir').val();
		var thn_kegiatan = $('.tab1 #tahun').val();
		
		$btn.attr('disabled',true).val('Loading...');
		$.ajax({
			type: 'POST',
			url: './view/PBB/pemeliharaan-data-piutang/svc-pembentukan-daftar-nop.php',
			dataType : 'json',
			data: {
				action:$btn.attr('id'),
				kel:kel,
				thn_awal:thn_awal,
				thn_akhir:thn_akhir,
				thn_kegiatan:thn_kegiatan,
				q:'<?php echo $_REQUEST['q']?>'
			},
			success: function(res){
				alert(res.msg);
				$btn.removeAttr('disabled').val( $btn.attr('id') == 'btn-save'? 'Proses' : 'Hapus');
			}
		});
	});
	
});
</script>
