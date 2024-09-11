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
	$response['msg'] = 'Data berhasil diproses.';
	
	if($_POST['action'] == 'loadData'){
		
		$query = "SELECT a.*, 
		a.CPM_BLOK AS BLOK,
		kel.CPC_TKL_KELURAHAN AS KEL,
		kec.CPC_TKC_KECAMATAN AS KEC
		FROM cppmod_pbb_sppt_download a 
		LEFT JOIN cppmod_tax_kelurahan kel ON kel.CPC_TKL_ID = SUBSTR(a.CPM_BLOK,1,10)
		LEFT JOIN cppmod_tax_kecamatan kec ON kec.CPC_TKC_ID = SUBSTR(a.CPM_BLOK,1,7)
		ORDER BY a.CPM_BLOK, a.CPM_TAHUN, a.CPM_DATETIME ASC";
		$res = mysqli_query($DBLink, $query);
		$rowsData = "";
		$no=0;
		
		while($row = mysql_fetch_object($res)){
			$class = ($no%2==0)? 'tdbody1' : 'tdbody2';
			$status = ($row->CPM_STATUS == 1)? 'Complete' : '<img src="/image/large-loading.gif" style="width:15px" /> Proses...';
			
			$arrFile = explode(';',$row->CPM_FILES);
			
			$downloadLink = '';
			$x = 1;
			foreach($arrFile as $file){
				$arrName = explode('/',$file);
				$file = base64_encode($file);
				$downloadLink .= "<a href='view/PBB/pencetakan-dhkp-sppt/download-link-sppt.php?file={$file}' onclick=\"javascript:return confirm('Yakin untuk mendownload file :\\n".end($arrName)."?')\">".substr(end($arrName),15,3).".pdf</a> ";
				$x++;
			}
			
			$blok = explode('-',$row->BLOK);
			
			$hapus = "<a href=\"javascript:void(0)\" onclick=\"javascript:hapus('{$row->CPM_ID}')\">Hapus</a>";
			$aksi = ($row->CPM_STATUS == 1)? $downloadLink : '-';
			$rowsData .= "<tr>
				<td class='{$class}'>".(++$no)."</td>
				<td class='{$class}' align='left'>{$row->KEC}</td>
				<td class='{$class}' align='left'>{$row->KEL}</td>
				<td class='{$class}' align='center'>".substr($blok[0],-3)."-".substr($blok[1],-3)."</td>
				<td class='{$class}' align='center'>{$row->CPM_TAHUN}</td>
				<td class='{$class}' align='center'>{$row->CPM_BUKU}</td>
				<td class='{$class}' align='right'>{$row->CPM_JUMLAH_NOP}</td>
				<td class='{$class}' align='right'>{$row->CPM_SIZE}</td>
				<td class='{$class}' align='right'>{$row->CPM_DATETIME}</td>
				<td class='{$class}' align='center'>{$status}</td>
				<td class='{$class}' align='center'>{$aksi}</td>
				<td class='{$class}' align='center'>{$hapus}</td>
			</tr>";
		}
		$response['totalRows'] = $no;
		$response['table'] = $rowsData;
		
	}elseif($_POST['action'] == 'hapusData'){
		$id = $_POST['id'];
		$query = sprintf("SELECT * FROM cppmod_pbb_sppt_download WHERE CPM_ID = '%s'", $id);
		$res = mysqli_query($DBLink, $query);
		if($row = mysql_fetch_object($res)){
			
			if($row->CPM_STATUS == 0){
				$response['msg'] = 'Data tidak bisa dihapus selama status masih dalam proses';
			}else{
				$files = explode(';',$row->CPM_FILES);
				foreach($files as $file){
					unlink($file);
				}
				$query = sprintf("DELETE FROM cppmod_pbb_sppt_download WHERE CPM_ID = '%s'", $id);
				if(!mysqli_query($DBLink, $query)){
					$response['msg'] = 'Data gagal dihapus, silakan coba lagi.';
				}
			}
			
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
<div id=\"main-content\" class=\"tab2\">
	<form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" style=\"border:1px #CCC solid;width:1300px\" action=\"\">
	
		<table width=\"1300\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
			<tr><td colspan=\"7\">&nbsp;</td></tr>
			<tr>
			  <td width=\"1%\">&nbsp;</td>
			  <td width=\"10%\"><label for=\"provinsiOP\">Provinsi</label></td>
			  <td width=\"20%\">
				<select name=\"propinsiOP\" id=\"propinsiOP\">$optionProvOP</select>
			  </td>
			  
			  <td width=\"10%\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
			  <td width=\"20%\">
				<select name=\"kelurahanOP\" id=\"kelurahanOP\">$optionKelOP</select>
			  </td>
			  <!-- Edited By ZNK (Add Filter Buku)-->
			  <td width=\"10%\">Buku</td>
			  <td width=\"40%\">
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
				<input type=\"button\" name=\"btn-generate-report\" id=\"btn-generate-report\" value=\"Download SPPT\" class=\"ui-button ui-widget ui-state-default ui-corner-all\">
			  </td>
			  <!-- End ZNK -->
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
			  <td></td>
			  <td></td>
			</tr>
			
			<tr>
			  <td width=\"2%\">&nbsp;</td>
			  <td width=\"\"><label for=\"kecamatanOP\">Kecamatan</label></td>
			  <td width=\"\">
				<select name=\"kecamatanOP\" id=\"kecamatanOP\">$optionKecOP</select>
			  </td>
			  
			  <td width=\"\">Blok</td>
			  <td width=\"\">
				<input type=\"text\" name=\"blok\" id=\"blok\" size=\"10\" maxlength=\"3\" placeholder=\"Blok\" onkeypress=\"return iniAngka(event, this)\" required=\"true\"/> sd 
				<input type=\"text\" name=\"blok2\" id=\"blok2\" size=\"10\" maxlength=\"3\" placeholder=\"Blok\" onkeypress=\"return iniAngka(event, this)\" required=\"true\"/>
			  </td>
			  <td></td>
			  <td></td>
			</tr>
			<tr>
				<td colspan=\"7\">
					<div class=\"ui-widget consol-main-content\">
						<div class=\"ui-widget-content consol-main-content-inner\">
							<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" width=\"100%\">
								<thead>
									<tr>
										<td class=\"tdheader\" width=\"50px\">No</td>
										<td class=\"tdheader\" width=\"150px\">Kecamatan</td>
										<td class=\"tdheader\" width=\"150px\">Kelurahan</td>
										<td class=\"tdheader\" width=\"80px\">Blok</td>
										<td class=\"tdheader\" width=\"80px\">Tahun</td>
										<td class=\"tdheader\" width=\"140px\">Buku</td>
										<td class=\"tdheader\" width=\"110px\">Jumlah NOP</td>
										<td class=\"tdheader\" width=\"100px\">Size</td>
										<td class=\"tdheader\" width=\"150px\">Tanggal</td>
										<td class=\"tdheader\" width=\"50px\">Status</td>
										<td class=\"tdheader\" width=\"200px\">Download</td>
										<td class=\"tdheader\" width=\"50px\">Hapus</td>
									</tr>
								</thead>
								<tbody id=\"table-pencetakan-sppt\" class=\"table-pencetakan-sppt\"></tbody>
							</table>
						</div>
						<div class=\"ui-widget-header consol-main-content-footer\"><div style=\"float:left\"></div>
							<div style=\"float:left\"><div>
								Total data : <span id=\"table-pencetakan-sppt-totalRows\">0</span><br/>
							</div></div>
						</div>
					</div>
				</td>
			</tr>
			<tr><td colspan=\"7\"><br/></td></tr>
		</table>
	
	</form>
</div>";
echo $html;
?>

<script>
$(document).ready(function(){
	$('.tab2 #kecamatanOP').change(function(){
		$.ajax({
		   type: 'POST',
		   url: './function/PBB/loket/svc-search-city.php',
		   data: 'type=3&id='+$(this).val(),
		   success: function(msg){
				$('.tab2 #kelurahanOP').html(msg);
		   }
		 });
	});
	
	$('.tab2 #btn-generate-report').click(function(){
		var $btn = $(this);
		var $blok = $('.tab2 #blok');
		var $blok2 = $('.tab2 #blok2');
		var $kel = $('.tab2 #kelurahanOP option:selected');
		var $buku = $('.tab2 #buku option:selected'); //Add By ZNK 
				
		var postData = {
			kd_kel:$kel.val(),
			blok:$blok.val(),
			blok2:$blok2.val(),
			kd_buku:$buku.val(), //Add By ZNK 
			buku:$buku.text(), //Add By ZNK 
			thn:$('.tab2 #tahun').val(),
			q:'<?php echo $_REQUEST['q']?>'
		};
		
		if(postData.blok.length == 0 || postData.blok2.length == 0){ 
			alert("Isi Blok terlebuh dahulu!");
			$blok.focus();
			return false;
		}
		
		if(confirm('Apakah anda yakin untuk download SPPT?') === false) return false;
		
		//post('view/PBB/pencetakan-dhkp-sppt/svc-topdf-sppt.php', postData);
		$btn.attr('disabled',true).val('Loading...');
		$.ajax({
			type: 'POST',
			url: 'view/PBB/pencetakan-dhkp-sppt/svc-topdf-sppt.php',
			data: postData,
			synch:true,
			success:function(res){
				if($.trim(res).length > 0) alert(res);
			}
		 });
		alert('Data sedang diproses..');
		$btn.removeAttr('disabled').val('Download SPPT');
		loadData();
	});
	
	loadData();
});

function hapus(id){
	var postData = {
		action:'hapusData',
		id:id,
		q:'<?php echo $_REQUEST['q']?>'
	};
	
	if(confirm('Apakah anda yakin untuk menghapus hasil download ini?') === false) return false;
	
	$.ajax({
		type: 'POST',
		url: 'view/PBB/pencetakan-dhkp-sppt/svc-pencetakan-sppt.php',
		data: postData,
		dataType:'json',
		success : function(res){
			alert(res.msg);
			loadData();
		}
	});
	
}
	
var myVar = setInterval(function(){ startData() }, 5000);
function startData() {
	loadData();
}

function stopData() {
	clearInterval(myVar);
}

function loadData(){
	var postData = {
		action:'loadData'
	}
	$.ajax({
		type: 'POST',
		url: 'view/PBB/pencetakan-dhkp-sppt/svc-pencetakan-sppt.php',
		data: postData,
		dataType:'json',
		success : function(res){
			$('#table-pencetakan-sppt').html(res.table);
			$('#table-pencetakan-sppt-totalRows').html(res.totalRows);
		}
	});
}

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
