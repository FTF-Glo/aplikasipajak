<?php 
session_start();

error_reporting(E_ERROR);
ini_set('display_errors', 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pencetakan-dhkp-sppt', '', dirname(__FILE__))).'/';
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


/*proses simpan / tampil data */
if(isset($_POST['action'])){
	$response['msg'] = 'Proses data berhasil.';
	
	$kel = $_POST['kel'];
	$thn_awal = $_POST['thn_awal'];
	$thn_akhir = $_POST['thn_akhir'];
	$thn_kegiatan = $_POST['thn_kegiatan'];
	$nop = $_POST['nop'];
		
		
	if($_POST['action'] == 'btn-cari'){
		
		$query = sprintf("SELECT * FROM cppmod_dafnom_op WHERE 
		(NOP LIKE '%s' AND TAHUN_KEGIATAN = '%s') ", $kel."%", $thn_kegiatan);
		
		$query .= (empty($nop))? '' : sprintf("AND NOP = '%s'", $nop);
		
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no=0;
		while($row = mysql_fetch_object($res)){
			$rowsData .= "<tr>
				<td>".(++$no)."</td>
				<td>{$row->NOP}<span class='nop' style='display:none'>{$row->NOP}</span></td>
				<td>{$row->ALAMAT_OP}</td>
				<td><input type='number' value='{$row->KATEGORI}' class='kategori' maxlength='1' min='1' max='4' size='5'></td>
				<td><input type='text' value='{$row->KETERANGAN}' class='keterangan' size='50'></td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
		
	}elseif($_POST['action'] == 'btn-save'){
		
		$lnop = explode(';',$_POST['nop']);
		$lkategori = explode(';',$_POST['kategori']);
		$lketerangan = explode(';',$_POST['keterangan']);
		
		$rows = array();
		
		$x = 0;
		foreach($lnop as $nop){
			
			if(!empty($nop)){
				$nop = mysql_escape_string($nop);
				$kategori = mysql_escape_string($lkategori[$x]);
				$keterangan = mysql_escape_string($lketerangan[$x]);
				$x++;
				
				$param = array(
					"KATEGORI = '{$kategori}'",
					"KETERANGAN = '{$keterangan}'"
				);
				
				$sets = implode(',',$param); 
				$query = "update cppmod_dafnom_op set {$sets} where NOP='{$nop}'";
				$sql = mysqli_query($DBLink, $query);
			}
			
			$response['msg'] = "Data berhasil disimpan.";
		}
	}
	
	exit($json->encode($response));
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

function getTahun($awal = 0,$akhir = 0){
	$awal = $awal == 0? date('Y')-5 : $awal;
	$akhir = $akhir == 0? date('Y') : $akhir;
	
	$optTahun = "";
	for($x = $akhir; $x>=$awal; $x--){
		$optTahun.= "<option value='{$x}'>{$x}</option>";            
	}
	return $optTahun;
}

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
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" style=\"border:1px #CCC solid;width:1220px\" action=\"\">
	
		<table width=\"1220\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
			<tr><td colspan=\"5\">&nbsp;</td></tr>
			<tr>
			  <td width=\"1%\">&nbsp;</td>
			  <td width=\"10%\"><label for=\"provinsiOP\">Provinsi</label></td>
			  <td width=\"20%\">
				<select name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
			  </td>
			  
			  <td width=\"10%\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
			  <td width=\"60%\">
				<select name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
			  </td>
			  
			</tr>
			<tr>
			  <td width=\"2%\">&nbsp;</td>
			  <td width=\"\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
			  <td width=\"\">
				<select name=\"kabupatenOP\" id=\"kabupatenOP\">$optionCityOP</select>
			  </td>
			  
			  <td width=\"\">Tahun</td>
			  <td width=\"\">
				<input type=\"text\" name=\"tahun\" id=\"tahun\" size=\"5\" maxlength=\"4\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$appConfig['tahun_tagihan'])."\" placeholder=\"Tahun\" />
			  </td>
			  
			</tr>
			
			<tr>
			  <td width=\"2%\">&nbsp;</td>
			  <td width=\"\"><label for=\"kecamatanOP\">Kecamatan</label></td>
			  <td width=\"\">
				<select name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
			  </td>
			  
			  <td width=\"\">Buku</td>
			  <td width=\"\">
				<select id=\"buku\" name=\"buku\">
					<option value=\"0\" >Pilih Buku</option>
					<option value=\"1\" >Buku 1</option>
					<option value=\"12\" >Buku 1,2</option>
					<option value=\"123\" >Buku 1,2,3</option>
					<option value=\"1234\" >Buku 1,2,3,4</option>
					<option value=\"12345\" >Buku 1,2,3,4,5</option>
					<option value=\"2\" >Buku 2</option>
					<option value=\"23\" >Buku 2,3</option>
					<option value=\"234\" >Buku 2,3,4</option>
					<option value=\"2345\" >Buku 2,3,4,5</option>
					<option value=\"3\" >Buku 3</option>
					<option value=\"34\" >Buku 3,4</option>
					<option value=\"345\" >Buku 3,4,5</option>
					<option value=\"4\" >Buku 4</option>
					<option value=\"45\" >Buku 4,5</option>
					<option value=\"5\" >Buku 5</option>
				</select>
				<input type=\"button\" name=\"btn-generate-report\" id=\"btn-generate-report\" value=\"Download DHKP\" class=\"ui-button ui-widget ui-state-default ui-corner-all\">
			  </td>
			</tr>
			<!--<tr>
				<td colspan=\"5\">
					<div class=\"ui-widget consol-main-content\">
						<div class=\"ui-widget-content consol-main-content-inner\">
							<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">
								<thead>
									<tr>
										<td class=\"tdheader\" width=\"50px\">No</td>
										<td class=\"tdheader\" width=\"110px\">NOP</td>
										<td class=\"tdheader\" width=\"420px\">Letak OP</td>
										<td class=\"tdheader\" width=\"50px\">Kategori</td>
										<td class=\"tdheader\" width=\"460px\">Keterangan</td>
									</tr>
								</thead>
								<tbody id=\"table-kategori-op\" class=\"table-kategori-op\"></tbody>
							</table>
						</div>
						<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\"></div>
							<div style=\"float:left\"><div>
								Total data : <span id=\"table-kategori-op-totalRows\">0</span><br/>
							</div></div>
						</div>
					</div>
				</td>
			</tr>-->
			<tr><td colspan=\"5\"><br/></td></tr>
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
	
	$('.tab1 #btn-generate-report').click(function(){
		var $btn = $(this);
		var $prop = $('.tab1 #propinsiOP option:selected');
		var $kota = $('.tab1 #kabupatenOP option:selected');
		var $kec = $('.tab1 #kecamatanOP option:selected');
		var $kel = $('.tab1 #kelurahanOP option:selected');
		var $buku = $('.tab1 #buku option:selected');
				
		var postData = {
			kd_prop:$prop.val(),
			kd_kota:$kota.val(),
			kd_kec:$kec.val(),
			kd_kel:$kel.val(),
			prop:$prop.text(),
			kota:$kota.text(),
			kec:$kec.text(),
			kel:$kel.text(),
			thn:$('.tab1 #tahun').val(),
			kd_buku:$buku.val(),
			buku:$buku.text(),
			q:'<?php echo $_REQUEST['q']?>'
		};
		
		if(postData.kd_buku == 0){ 
			alert("Pilih Buku terlebuh dahulu!");
			$buku.focus();
			return false;
		}
		
		if(confirm('Apakah anda yakin untuk download DHKP?') === false) return false;
		post('view/PBB/pencetakan-dhkp-sppt/svc-toexcel-dhkp.php', postData);
	});
});

function post(path, params, method) {
    method = method || "post";
    var target = '_blank';
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    form.setAttribute("target", target);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);
    form.submit();
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

function setKategoriAll(){
	var kat = $('#kategori-all').val();
	$('input.kategori').each(function(){
		$(this).val(kat);
	});
	
}
</script>
