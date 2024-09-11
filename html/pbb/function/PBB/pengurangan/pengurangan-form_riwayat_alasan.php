<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/uuid.php");
require_once($sRootPath."inc/PBB/dbServices.php");
require_once($sRootPath."inc/PBB/dbGwCurrent.php");
require_once($sRootPath."inc/PBB/dbFinalSppt.php");
require_once($sRootPath."function/PBB/gwlink.php");

echo '<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>';
echo '<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>';

echo '<script src="inc/js/jquery.maskedinput-1.3.min.js"></script>';
echo '<script src="inc/js/jquery.validate.min.js"></script>';
echo '<script language="javascript" src="inc/payment/base64.js" type="text/javascript"></script>';

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig  = $User->GetModuleConfig($m);	
$appConfig = $User->GetAppConfig($a);
$dbServices = new DbServices($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);

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

function isExistSID($nomor=""){
	global $DBLink;
	$query = "SELECT CPM_RE_SID FROM cppmod_pbb_service_reduce WHERE CPM_RE_SID='$nomor'";
	$res = mysqli_query($DBLink, $query);
	$nRes = mysqli_num_rows($res);
	return $nRes;
}

function getLastSKNumber(){
    global $DBLink;	
    
	$qry = "SELECT MAX(CPM_SK_NO) AS SK_NUMBER FROM cppmod_pbb_generate_sk_number";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['SK_NUMBER'];
        }
		return "0";
    }
}

function generateSKNumber(){
	global $appConfig;

	$lastNumber = getLastSKNumber();
	$newNumber = $lastNumber+1;
	if(trim($appConfig['NOMOR_SK_OTOMATIS'])=='1'){
		return $newNumber.$appConfig['NOMOR_SK_FORMAT'];
	} else 
		return NULL;
}

function updateReduce($nomor='',$noSK='', $date=''){
	global $DBLink;	
	
	$qry = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_SK_NUMBER = '$noSK', CPM_RE_SK_DATE = '$date' WHERE CPM_RE_SID='$nomor'";
	// echo $qry; exit;
	$res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }else
		return $res;
	
}

function isHaveSKNumber($nomor){
    global $DBLink;	
    
	$qry = "SELECT CPM_RE_SK_NUMBER AS SK_NUMBER FROM cppmod_pbb_service_reduce WHERE CPM_RE_SID='$nomor'";
	// echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
        return false;
    }
    else{
        $SKNumber = '';
        while ($row = mysqli_fetch_assoc($res)) {
            $SKNumber = $row['SK_NUMBER'];
        }
        if($SKNumber != null && $SKNumber != '') return true;
        else return false;
    }
}

function formPenerimaan($initData,$initDataRed) {   
    global $a, $m, $appConfig, $arConfig, $dis, $tab;
	
	$today = date("d-m-Y");    
    
	$hiddenIdInput = $nomor = '';
	
	$bSlash = "\'";
	$ktip = "'";
	
	$reduceBefore = getReduceBefore($initData['CPM_OP_NUMBER']);
	    
	if($initData['CPM_ID'] != '') {
            //$hiddenModeInput = '<input type="hidden" name="mode" value="edit">';

            $kecOP = getKecamatan($initData['CPM_OP_KECAMATAN']);
            $kelOP = getKelurahan($initData['CPM_OP_KELURAHAN']);

            foreach($kecOP as $row){
            if($initData['CPM_OP_KECAMATAN'] == $row['id'])
                $optionKecOP .= "<option value=".$row['id']." selected=\"selected\">".$row['name']."</option>";
            else
                $optionKecOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
	    }
	    foreach($kelOP as $row){
	        if($initData['CPM_OP_KELURAHAN'] == $row['id'])
	            $optionKelOP .= "<option value=".$row['id']." selected=\"selected\">".$row['name']."</option>";
	        else
	            $optionKelOP .= "<option value=".$row['id'].">".$row['name']."</option>";            
	    }
		
	}else{
            $nomor = generateNumber(date('Y'), date('m'));
            $kecWP = $kecOP = getKecamatan($cityID);
	    $kelWP = $kelOP = getKelurahan($kecWP[0]['id']);
		
            foreach($kecWP as $row){
	        $optionKecWP .= "<option value=".$row['id'].">".$row['name']."</option>";            
	    }
	    foreach($kelWP as $row){
	        $optionKelWP .= "<option value=".$row['id'].">".$row['name']."</option>";            
	    }
		
		$optionKecOP = $optionKecWP;
		$optionKelOP = $optionKelWP;
	}
	
	$pisAlasan = array();
	if($initDataRed['CPM_RE_ARGUEMENT']!=''){
		$pisAlasan = explode("#",$initDataRed['CPM_RE_ARGUEMENT']);
	} else {
		$pisAlasan = explode("#",$reduceBefore['CPM_RE_ARGUEMENT']);
	}
	$pisSbrPenghasilan = array();
	if($initDataRed['CPM_RE_INCOME_SOURCE']!=''){
		$pisSbrPenghasilan = explode("#",$initDataRed['CPM_RE_INCOME_SOURCE']);
	} else {
		$pisSbrPenghasilan = explode("#",$reduceBefore['CPM_RE_INCOME_SOURCE']);
	}
	
    $html = "
    <style>
    #main-content {
        width: 1060px;
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
		
			$('#tglMasuk').datepicker({dateFormat: 'dd-mm-yy'});
			$('#tglSPPT').datepicker({dateFormat: 'dd-mm-yy'});
			
			var jenisBerkas = new Array();
            jenisBerkas[0] = new Array(1,2,3,5,6);
            jenisBerkas[1] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[2] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[3] = new Array(1,2,3,5,6,7,8);
            jenisBerkas[4] = new Array(1,3,5,6,8,9);
            jenisBerkas[5] = new Array(1,3,5,6,8,9);
            jenisBerkas[6] = new Array(4,12,13,10);            
            jenisBerkas[7] = new Array(1,2,3,5,6,7,8);         
            jenisBerkas[8] = new Array(1,3,5,6,8,9);
            jenisBerkas[9] = new Array(1,3,5,6,8,9,10);
            
            $('#jnsBerkas').change(function(){
                var berkas = jenisBerkas[$(this).val()-1];                
                $('.berkas').hide();
                
                for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
            });
			";
			
	if($initData['CPM_TYPE']!='')
		$html .="
				var berkas = jenisBerkas[".$initData['CPM_TYPE']."-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
			";
            
			
	$html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });
			
			$(document).ready(function() {
				$(\"#myForm\").validate();
			})

            $(\"#form-penerimaan\").validate({
                rules : {
                    nmKuasa : \"required\",
                    tglMasuk : \"required\",
                    hpWP : {
                            required : true,
                            number : true
                          },
                    nop : {
                            required : true,
                            number : true
                          },
                    thnPajak : \"required\",
                    PBBterutang : \"required\"
                },
                messages : {
                    nmKuasa : \"Wajib diisi\",
                    tglMasuk : \"Wajib diisi\",
                    hpWP : \"Wajib diisi\",
                    nop : \"Wajib diisi\",
                    thnPajak : \"Wajib diisi\",
                    PBBterutang : \"Wajib diisi\"
                }
            });
            
            $('#kecamatanOP').change(function(){
                $.ajax({
                   type: 'POST',
                   url: './function/tax/service/svc-search-city.php',
                   data: 'type=3&id='+$(this).val(),
                   success: function(msg){
                        $('#kelurahanOP').html(msg);
                   }
                 });
            });
            
            function getWilayah(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/tax/service/svc-search-city.php',
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
		
		

    </script>
    <div id=\"main-content-pengurangan\"><form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	$hiddenModeInput
	<input type=\"hidden\" name=\"nomorHidden\" id=\"nomorHidden\" readonly=\"readonly\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_ID']!='')? $initData['CPM_ID']:$nomor)."\"/>
	<input type=\"hidden\" name=\"nopHidden\" id=\"nopHidden\" readonly=\"readonly\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_OP_NUMBER']!='')? $initData['CPM_OP_NUMBER']:$nop)."\"/>
	<input type=\"hidden\" name=\"thnPajakHidden\" id=\"thnPajakHidden\" readonly=\"readonly\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:$thnPajak)."\"/>
	<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table width=\"1024\" border=\"0\" cellspacing=\"1\" cellpadding=\"10\">
                            <tr>
                              <td colspan=\"2\" align=\"center\"><strong><font size=\"+2\">Pengurangan</font></strong><br /><br /></td>
                            </tr>
                            <tr>
                              <td align=\"center\"><table width=\"auto\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td width=\"auto\">Nomor</td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nomor\" id=\"nomor\" readonly=\"readonly\"  size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_ID']!='')? $initData['CPM_ID']:$nomor)."\" placeholder=\"Nomor\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td colspan=\"2\"><strong>Letak Objek Pajak</strong></td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\"  size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_REPRESENTATIVE']!='')? str_replace($bSlash,$ktip,$initData['CPM_REPRESENTATIVE']):'')."\" placeholder=\"Nama Kuasa\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"></td>
                                      <td width=\"auto\">
										
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nmWp\" readonly=\"readonly\" id=\"nmWp\"  size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_WP_NAME']!='')? str_replace($bSlash,$ktip,$initData['CPM_WP_NAME']):'')."\" placeholder=\"Nama Wajib Pajak\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									   <td width=\"auto\"><label for=\"almtOP\">Alamat OP</label></td>
                                      <td width=\"auto\">
										<!-- <input type=\"text\" name=\"almtOP\" id=\"almtOP\"  size=\"50\" maxlength=\"500\" value=\"".(($initData['CPM_OP_ADDRESS']!='')? str_replace($bSlash,$ktip,$initData['CPM_OP_ADDRESS']):'')."\" placeholder=\"Alamat\" /> -->
										<textarea rows=\"2\" cols=\"40\" readonly=\"readonly\" name=\"almtOP\" id=\"almtOP\" placeholder=\"Alamat\">".(($initData['CPM_OP_ADDRESS']!='')? str_replace($bSlash,$ktip,$initData['CPM_OP_ADDRESS']):'')."</textarea>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\"  value=\"".(($initData['CPM_DATE_RECEIVE']!='')? $initData['CPM_DATE_RECEIVE']:$today)."\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"rtOP\">RT/RW</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"rtOP\" readonly=\"readonly\" id=\"rtOP\"  size=\"3\" maxlength=\"3\" value=\"".(($initData['CPM_OP_RT']!='')? $initData['CPM_OP_RT']:'')."\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwOP\" readonly=\"readonly\" id=\"rwOP\"  size=\"3\" maxlength=\"3\" value=\"".(($initData['CPM_OP_RW']!='')? $initData['CPM_OP_RW']:'')."\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"almtWP\">Alamat WP</label></td>
                                      <td width=\"auto\">
                                            <textarea rows=\"2\" cols=\"40\" name=\"almtWP\" id=\"almtWP\" readonly=\"readonly\" placeholder=\"Alamat\">".(($initData['CPM_WP_ADDRESS']!='')? str_replace($bSlash,$ktip,$initData['CPM_WP_ADDRESS']):'')."</textarea>
                                        </td>
                                        <td width=\"30\">&nbsp</td>
                                        <td width=\"auto\"><label for=\"provinsiOP\">Provinsi</label></td>
                                      <td width=\"auto\">
                                        <input type=\"hidden\" name=\"propinsiOP\" value=\"".$appConfig['KODE_PROVINSI']."\">
                                        <input type=\"text\" name=\"propinsiOPname\" readonly=\"readonly\"  size=\"25\" value=\"".$appConfig['NAMA_PROVINSI']."\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"rtWP\">RT/RW</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"rtWP\" readonly=\"readonly\" id=\"rtWP\"  size=\"3\" maxlength=\"3\" value=\"".(($initData['CPM_WP_RT']!='')? $initData['CPM_WP_RT']:'')."\" placeholder=\"00\"/>&nbsp;/
                                        <input type=\"text\" name=\"rwWP\" readonly=\"readonly\" id=\"rwWP\"  size=\"3\" maxlength=\"3\" value=\"".(($initData['CPM_WP_RW']!='')? $initData['CPM_WP_RW']:'')."\" placeholder=\"00\"/>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                                      <td width=\"auto\">
                                        <input type=\"hidden\" name=\"kabupatenOP\" value=\"".$appConfig['KODE_KOTA']."\">
                                        <input type=\"text\" name=\"kabupatenOPname\" readonly=\"readonly\" size=\"25\" value=\"".$appConfig['NAMA_KOTA']."\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"propinsi\">Provinsi</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"propinsi\" id=\"propinsi\"  readonly=\"readonly\" size=\"25\" value=\"".(($initData['CPM_WP_PROVINCE']!='')? $initData['CPM_WP_PROVINCE']:'')."\" placeholder=\"Provinsi\"/>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"kecamatanOP\">Kecamatan</label></td>
                                      <td width=\"auto\">
                                        <input type=\"hidden\" name=\"kecamatanOP\" value=\"".$initData['CPM_OP_KECAMATAN']."\">
                                        <input type=\"text\" name=\"kecamatanOPname\" readonly=\"readonly\" size=\"25\" value=\"".$kecOP[0]['name']."\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"kabupaten\" readonly=\"readonly\" id=\"kabupaten\"  size=\"25\" value=\"".(($initData['CPM_WP_KABUPATEN']!='')? $initData['CPM_WP_KABUPATEN']:'')."\" placeholder=\"Kabupaten\"/>
                                      </td>
                                        <td width=\"30\">&nbsp</td>
                                        <td width=\"auto\"><label for=\"kelurahanOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                                      <td width=\"auto\">
                                        <input type=\"hidden\" name=\"kelurahanOP\" value=\"".$initData['CPM_OP_KELURAHAN']."\">
                                        <input type=\"text\" name=\"kelurahanOPname\" readonly=\"readonly\" size=\"25\" value=\"".$kelOP[0]['name']."\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kecamatan\">Kecamatan</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"kecamatan\" id=\"kecamatan\"  readonly=\"readonly\" size=\"25\" value=\"".(($initData['CPM_WP_KECAMATAN']!='')? $initData['CPM_WP_KECAMATAN']:'')."\" placeholder=\"Kecamatan\"/>
                                      </td>
                                        <td width=\"30\">&nbsp</td>
                                        <td width=\"auto\"><label for=\"PBBterutang\">PBB yang terutang</label></td>
                                      <td width=\"auto\">
                                            <input type=\"text\" name=\"PBBterutang\" id=\"PBBterutang\" readonly=\"readonly\" size=\"25\" maxlength=\"50\" value=\"".(($initData['CPM_SPPT_DUE']!='')? $initData['CPM_SPPT_DUE']:'')."\" placeholder=\"PBB yang terutang\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\"><label for=\"kelurahan\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"kelurahan\" id=\"kelurahan\"  readonly=\"readonly\" size=\"25\" value=\"".(($initData['CPM_WP_KELURAHAN']!='')? $initData['CPM_WP_KELURAHAN']:'')."\" placeholder=\"".$appConfig['LABEL_KELURAHAN']."\"/>
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"tglSPPT\">Tahun Pajak</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"thnPajak\" id=\"thnPajak\" readonly=\"readonly\"  size=\"10\" maxlength=\"10\" value=\"".(($initData['CPM_SPPT_YEAR']!='')? $initData['CPM_SPPT_YEAR']:'')."\" placeholder=\"Tahun Pajak\"/>                                      
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\">No. HP WP</td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"hpWP\" id=\"hpWP\"  size=\"15\" maxlength=\"15\" value=\"".(($initData['CPM_WP_HANDPHONE']!='')? $initData['CPM_WP_HANDPHONE']:'')."\" placeholder=\"Nomor HP\" />
                                      </td>
									  <td width=\"30\">&nbsp</td>
									  <td width=\"auto\"><label for=\"tglSPPT\">Pengurangan PBB sebesar</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"persenPengurangan\" id=\"persenPengurangan\"  size=\"10\" maxlength=\"3\" value=\"".(($initDataRed['CPM_RE_PERCENT']!='')? $initDataRed['CPM_RE_PERCENT']:$reduceBefore['CPM_RE_PERCENT'])."\"/>   
										% dari PBB yang terutang
                                      </td>
                                    </tr>
									  <tr>
                                      <!-- <td width=\"auto\"><label for=\"jnsBerkas\">Jenis Berkas</label></td>
                                      <td width=\"auto\">
                                        <select name=\"jnsBerkas\" id=\"jnsBerkas\" >
                                            <option value=\"1\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==1)? 'selected="selected"':'').">OP Baru</option>
                                            <option value=\"2\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==2)? 'selected="selected"':'').">Pemecahan</option>
                                            <option value=\"3\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==3)? 'selected="selected"':'').">Penggabungan</option>
                                            <option value=\"4\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==4)? 'selected="selected"':'').">Mutasi</option>
                                            <option value=\"5\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==5)? 'selected="selected"':'').">Perubahan Data</option>
                                            <option value=\"6\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==6)? 'selected="selected"':'').">Pembatalan</option>
                                            <option value=\"7\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==7)? 'selected="selected"':'').">Salinan</option>
                                            <option value=\"8\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==8)? 'selected="selected"':'').">Penghapusan</option>
                                            <option value=\"9\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==9)? 'selected="selected"':'').">Pengurangan</option>
                                            <option value=\"10\" ".(($initData['CPM_TYPE']!='' && $initData['CPM_TYPE']==10)? 'selected="selected"':'').">Keberatan</option>
                                        </select>
                                      </td> -->
                                    </tr>
									<tr>
                                      <td width=\"auto\"><label for=\"nop\">NOP</label></td>
                                      <td width=\"auto\">
                                        <input type=\"text\" name=\"nop\" id=\"nop\" readonly=\"readonly\" size=\"50\" maxlength=\"50\" value=\"".(($initData['CPM_OP_NUMBER']!='')? $initData['CPM_OP_NUMBER']:'')."\" placeholder=\"NOP\" />
                                      </td>
									    <td width=\"30\">&nbsp</td>
                                      <td width=\"auto\" colspan=\"2\" rowspan=\"2\">
									Alasan mengajukan permohonan <br><br> 
										<label for=\"alasan1\">*</label><input type=\"text\" name=\"alasan1\" id=\"alasan1\"  size=\"80\" maxlength=\"500\" value=\"".$pisAlasan[0]."\" placeholder=\"Alasan 1\" /><br><br>
										<label for=\"alasan2\">*</label><input type=\"text\" name=\"alasan2\" id=\"alasan2\"  size=\"80\" maxlength=\"500\" value=\"".$pisAlasan[1]."\"placeholder=\"Alasan 2\" /><br><br>
										<label for=\"alasan3\">*</label><input type=\"text\" name=\"alasan3\" id=\"alasan3\"  size=\"80\" maxlength=\"500\" value=\"".$pisAlasan[2]."\"placeholder=\"Alasan 3\" /><br><br>
										<label for=\"alasan4\">*</label><input type=\"text\" name=\"alasan4\" id=\"alasan4\"  size=\"80\" maxlength=\"500\" value=\"".$pisAlasan[3]."\"placeholder=\"Alasan 4\" />
                                      </td>
                                    </tr>
                                    <tr>
                                      <td width=\"auto\" valign=\"top\" colspan=\"3\" rowspan=\"2\">Kelengkapan Dokumen : <br><br>
                                          <ol id=\"lampiran\" style=\"margin-left: -20px;\">
											  
											  <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"1\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 1)) ? "checked=\"checked\"":"")."> Surat Permohonan</li>
                                              <li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"2\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 2)) ? "checked=\"checked\"":"")."> Daftar Penghasilan / Slip Gaji / Laporan R-L / SK. Pensiun / SPPT PPh / Dokumen lain yang dipersamakan.</li>
                                              <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"4\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 4)) ? "checked=\"checked\"":"")."> Fotocopi SPPT PBB yang akan diajukan Permohonan Pengurangan.</li>
											  <li id=\"berkas4\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"8\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 8)) ? "checked=\"checked\"":"")."> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
											  <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"16\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 16)) ? "checked=\"checked\"":"")."> Surat Keterangan : Tidak Mampu / Tidak Bekerja / Tidak Ada Penghasilan / Lainnya / Dokumen lain yang dipersamakan dan telah ditandatangani oleh Pejabat Berwenang.</li>
                                              <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"32\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 32)) ? "checked=\"checked\"":"")."> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
											  <li id=\"berkas7\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"64\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 64)) ? "checked=\"checked\"":"")."> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Pengurangan.</li>
                                              <li id=\"berkas8\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"128\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 128)) ? "checked=\"checked\"":"")."> Fotocopi Pembayaran Rekening Listrik , dan / atau Telepon / Hp, dan / atau PDAM Bulan Terakhir.</li>
                                              <li id=\"berkas9\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"256\" class=\"attach\" ".((($initData['CPM_TYPE']==9) && ($initData['CPM_ATTACHMENT']& 256)) ? "checked=\"checked\"":"")."> Fotocopi Izin Mendirikan Bangunan (IMB), khusus bangunan yang bersifat komersil.</li>
							
                                          </ol>
									 </td>                                          
                                    </tr>
									<tr>
										<td colspan=\"2\">
											Besar penghasilan perbulan <input type=\"text\" name=\"bsrPenghasilan\" id=\"bsrPenghasilan\"  size=\"15\" maxlength=\"15\" value=\"".(($initDataRed['CPM_RE_INCOME']!='')? $initDataRed['CPM_RE_INCOME']:$reduceBefore['CPM_RE_INCOME'])."\"/><br><br>
											Besar penghasilan diatas diperoleh dari: <br><br>
											<label for=\"sbrPenghasilan1\">*</label><input type=\"text\" name=\"sbrPenghasilan1\" id=\"sbrPenghasilan1\"  size=\"80\" maxlength=\"500\" value=\"".$pisSbrPenghasilan[0]."\" placeholder=\"Sumber Penghasilan 1\" /><br><br>
											<label for=\"sbrPenghasilan2\">*</label><input type=\"text\" name=\"sbrPenghasilan2\" id=\"sbrPenghasilan2\"  size=\"80\" maxlength=\"500\" value=\"".$pisSbrPenghasilan[1]."\" placeholder=\"Sumber Penghasilan 2\" /><br><br>
											<label for=\"sbrPenghasilan3\">*</label><input type=\"text\" name=\"sbrPenghasilan3\" id=\"sbrPenghasilan3\"  size=\"80\" maxlength=\"500\" value=\"".$pisSbrPenghasilan[2]."\" placeholder=\"Sumber Penghasilan 3\" /><br><br>
											<label for=\"sbrPenghasilan4\">*</label><input type=\"text\" name=\"sbrPenghasilan4\" id=\"sbrPenghasilan4\"  size=\"80\" maxlength=\"500\" value=\"".$pisSbrPenghasilan[3]."\" placeholder=\"Sumber Penghasilan 4\" /><br><br>
											<label for=\"sbrPenghasilan5\">*</label><input type=\"text\" name=\"sbrPenghasilan5\" id=\"sbrPenghasilan5\"  size=\"80\" maxlength=\"500\" value=\"".$pisSbrPenghasilan[4]."\" placeholder=\"Sumber Penghasilan 5\" />
										</td>
									</tr>
                              </table></td>
                            </tr>";
                    $form = "<script language='javascript' type='text/javascript'>
							function changeDis() {
								for (var i=0;i<document.forms[0].elements.length;i++) {
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"a\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('persenTerVerifikasi').disabled=false;
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									}
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && (document.forms[0].elements[i].value == \"b\" || document.forms[0].elements[i].value == \"c\") && document.forms[0].elements[i].checked == true)){
										document.getElementById('persenTerVerifikasi').disabled=true;
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									} 
								}
							}
							
							function checkMinTagihan(min){
								var minTagihan  = ".$appConfig['minimum_sppt_pbb_terhutang'].";
								var persen	    = document.getElementById('persenTerVerifikasi').value;
								var tagihan     = document.getElementById('PBBterutang').value;
								var rekomenA	= document.getElementById('r1').checked;
								var rekomenC	= document.getElementById('r3').checked;
								var result	    = tagihan-(tagihan*(persen/100));
								if(rekomenA!=true && rekomenC!=true){
									alert('Pilih rekomendasi anda.');
									return false;
								} else if(result<minTagihan && rekomenA == true){
									alert('Hasil pengurangan tidak boleh kurang dari minimum tagihan.');
									return false;
								} else {
									return true;
								}
							}
							</script>
							<tr align=\"center\">
								<td colspan=\"2\">
									 <form name=\"form\" id =\"form\" class=\"form\" method=\"post\">
										<table border=0 cellpadding=5>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												<input type=\"radio\" name=\"rekomendasi\" id=\"r1\" value=\"a\" onclick=\"changeDis()\"><label> Setuju pengurangan PBB sebesar </label>
												<input type=\"text\" name=\"persenTerVerifikasi\" id=\"persenTerVerifikasi\" size=\"10\" maxlength=\"10\" disabled value=\"".(($initDataRed['CPM_RE_PERCENT']!='')? $initDataRed['CPM_RE_PERCENT']:'')."\"/> 
												<label>% dari PBB yang terutang</label><br>
												<label><input type=\"radio\" name=\"rekomendasi\" id=\"r3\" value=\"c\" onclick=\"changeDis()\"> Tolak</label><br>
												</td>	
											</tr>
											<tr><td valign=\"top\"class=\"tbl-rekomen\">
												Alasan<br><textarea name=\"alasan\" id=\"alasan\" cols=70 rows=5 disabled class=\"required\" title=\"Alasan wajib diisi\"></textarea></td></tr>
											<tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><!-- <input type=\"button\" onclick=\"checkMinTagihan()\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\"> --><input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\" onclick=\"return checkMinTagihan();\"></td></tr>
										</table>
									</form>
								</td>
                            </tr>";
			$form2 = "<script>
							function changeDis() {
								for (var i=0;i<document.forms[0].elements.length;i++) {
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"a\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('persenTerVerifikasi').disabled=false;
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									}
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && (document.forms[0].elements[i].value == \"b\" || document.forms[0].elements[i].value == \"c\") && document.forms[0].elements[i].checked == true)){
										document.getElementById('persenTerVerifikasi').disabled=true;
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									} 
								}
							}
							function checkMinTagihan(min){
								var minTagihan  = ".$appConfig['minimum_sppt_pbb_terhutang'].";
								var persen	    = document.getElementById('persenTerVerifikasi').value;
								var tagihan     = document.getElementById('PBBterutang').value;
								var rekomenA	= document.getElementById('r1').checked;
								var rekomenB	= document.getElementById('r2').checked;
								var rekomenC	= document.getElementById('r3').checked;
								var result	    = tagihan-(tagihan*(persen/100));
								if(rekomenA!=true && rekomenC!=true && rekomenB!=true){
									alert('Pilih rekomendasi anda.');
									return false;
								} else if(result<minTagihan && rekomenA == true){
									alert('Hasil pengurangan tidak boleh kurang dari minimum tagihan.');
									return false;
								} else {
									return true;
								}
							}
							</script>
							<tr align=\"center\">
								<td colspan=\"2\">
									 <form name=\"form\" method=\"post\">
										<table border=0 cellpadding=5>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Informasi Verifikasi</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												".(($initDataRed['CPM_RE_PERCENT_APPROVE']=='' || $initDataRed['CPM_RE_PERCENT_APPROVE']=='0')? '<label>Ditolak, dengan alasan sebagai berikut:</label><br>':'<label>Setuju dengan pengurangan PBB sebesar </label><label><b>'.$initDataRed['CPM_RE_PERCENT_APPROVE'].'</b></label><label>% dari PBB yang terutang<br>dengan alasan sebagai berikut:</label><br>')."
                                                                                                <label><b>".(($initData['CPM_APPROVAL_REASON']!='')? $initData['CPM_APPROVAL_REASON']:'Tidak ada alasan yang tercatat')."<b></label>
												</td>	
											</tr>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												<input type=\"radio\" name=\"rekomendasi\" id=\"r1\" value=\"a\" onclick=\"changeDis()\"><label> Setuju pengurangan PBB sebesar </label>
												<input type=\"text\" name=\"persenTerVerifikasi\" id=\"persenTerVerifikasi\" size=\"10\" maxlength=\"10\" disabled value=\"".(($initDataRed['CPM_RE_PERCENT_APPROVE']!='')? $initDataRed['CPM_RE_PERCENT_APPROVE']:'')."\"/> 
												<label>% dari PBB yang terutang</label><br>
												<label><input type=\"radio\" name=\"rekomendasi\" id=\"r2\" value=\"b\" onclick=\"changeDis()\"> Tolak, kembalikan ke Staff</label><br>
												<label><input type=\"radio\" name=\"rekomendasi\" id=\"r3\" value=\"c\" onclick=\"changeDis()\"> Tolak</label><br>
												</td>	
											</tr>
											<tr><td valign=\"top\"class=\"tbl-rekomen\">
												Alasan<br><textarea name=\"alasan\" id=\"alasan\" cols=70 rows=5 disabled class=\"required\" title=\"Alasan wajib diisi\"></textarea></td></tr>
											<tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\" onclick=\"return checkMinTagihan()\"></td></tr>
										</table>
									</form>
								</td>
                            </tr>"; 
			$persenApp = "<tr align=\"center\">
								<td colspan=\"2\">
								<form name=\"form\" id =\"form\" method=\"post\">
										<table border=0 cellpadding=5>
											
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												Pengurangan PBB yang disetujui adalah sebesar
												<input type=\"text\" name=\"persenUpdate\" id=\"persenUpdate\" size=\"10\" maxlength=\"3\" value=\"".(($initDataRed['CPM_RE_PERCENT_APPROVE']!='')? $initDataRed['CPM_RE_PERCENT_APPROVE']:'')."\"/> 
												<label>% dari PBB yang terutang</label><br>
												</td>	
											</tr>
											<tr><td colspan=2 align=\"center\" class=\"tbl-rekomen\">
											<input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\">&nbsp;
											<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."")."\"' /></td></tr>
										</table>
									</form>
								</td>
                            </tr>";
                  $simpan = "<tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">
                                  <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                                  &nbsp;
                                  <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Finalkan\" />
                                  &nbsp;
								  <input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."")."\"' />
                              </td>
                            </tr>";
					 $end = "<tr>
                              <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
                            </tr>
                      </table>
                    </form>
				</div>";
        
	if(($dis==0) && (($tab==10) || ($tab==12))) {
		$html .= $simpan . $end;
	} else if(($dis==0) && ($tab==20)){
		$html .= $form . $end;
	} else if(($dis==1) && ($tab==30)){
		$html .= $form2 . $end;
	} else if(($dis==1)&&($tab==33)){
		$html .= $persenApp . $end;
	} else if($dis==1){
		$html .= $end;
	} 
    return $html;
}

function getInitData($id=""){    
    global $DBLink;	
    
	if($id == '') return getDataDefault();
	
    $qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
		return getDataDefault();
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'],8,2).'-'.substr($row['CPM_DATE_RECEIVE'],5,2).'-'.substr($row['CPM_DATE_RECEIVE'],0,4);
			return $row;
        }                
    }
}

function getReduce($id=""){
	global $DBLink;	
	
    $qry = "select * from cppmod_pbb_service_reduce where CPM_RE_SID='{$id}'";
	//echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_RE_DATE_SPPT'] = substr($row['CPM_RE_DATE_SPPT'],8,2).'-'.substr($row['CPM_RE_DATE_SPPT'],5,2).'-'.substr($row['CPM_RE_DATE_SPPT'],0,4);
			return $row;
        }                
    }
	
}

function getReduceBefore($nop=""){
	global $DBLink;	
	
    $qry = "SELECT
				*
			FROM
				cppmod_pbb_service_reduce A
			LEFT JOIN cppmod_pbb_services B ON A.CPM_RE_SID = B.CPM_ID
			WHERE CPM_OP_NUMBER = '$nop' ORDER BY CPM_SPPT_YEAR DESC";
	//echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
			$row['CPM_RE_DATE_SPPT'] = substr($row['CPM_RE_DATE_SPPT'],8,2).'-'.substr($row['CPM_RE_DATE_SPPT'],5,2).'-'.substr($row['CPM_RE_DATE_SPPT'],0,4);
			return $row;
        }                
    }
	
}

function getDataDefault(){
	$default = array('CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '', 
	'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '', 
	'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => '');
}

function getLastNumber($year,$mon){
    global $DBLink;	
    
	$qry = "select SUBSTRING(max(CPM_ID), -3) as CPM_ID from cppmod_pbb_services where CPM_ID like 'SPOP/{$year}/{$mon}%'";
	
    $res = mysqli_query($DBLink, $qry);
    if (!$res){
        echo $qry ."<br>";
        echo mysqli_error($DBLink);
    }
    else{
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CPM_ID'];
        }
		
		return "000";
    }
}

function generateNumber($year,$mon){
	$lastNumber = getLastNumber($year,$mon);
	$newNumber = $lastNumber+1;
	return "SPOP/".$year."/".$mon."/".substr('000'.$newNumber, -3);
}

function insertToSPPTPengurangan($nomorHidden,$nopHidden,$persenTerVerifikasi){
	global $DBLink;
	$qry = "INSERT INTO cppmod_pbb_sppt_pengurangan 
				SELECT 
					CPM_OP_NUMBER,
					CPM_SPPT_YEAR,
					(SELECT ({$persenTerVerifikasi}/100)*(SELECT CPM_SPPT_DUE FROM cppmod_pbb_services WHERE CPM_ID = '{$nomorHidden}')) AS NILAI,
					(SELECT CPM_RE_PERCENT_APPROVE FROM cppmod_pbb_service_reduce WHERE CPM_RE_SID = '{$nomorHidden}') AS PERCENT
				FROM cppmod_pbb_services WHERE CPM_ID = '{$nomorHidden}'";
	//echo $qry."<br>";exit;
	$res = mysqli_query($DBLink, $qry);
	if ($res === false){
            echo $qry ."<br>";exit;
    } 
	return $res;
}

function hitungPengurangan($tagihan,$persen){
	global $appConfig;
	
	$minTagihan = $appConfig['minimum_sppt_pbb_terhutang'];
	$result		= $tagihan-($tagihan*($persen/100));
	
	if($result>$minTagihan)
		return true;
	else
		return false;
}

function save($status){
    global $data, $DBLink, $uname, $dis, $tab, $validator, $rekomendasi, $dbGwCurrent,$dbFinalSppt,$arConfig, $appConfig;
	$dateValidate		= date("Y-m-d");
    $nomor 				= $_REQUEST['nomor'];
	$nomorHidden		= $_REQUEST['nomorHidden'];
	$thnPajak			= $_REQUEST['thnPajak'];
	$thnPajakHidden		= $_REQUEST['thnPajakHidden'];
	$pbbterutang		= $_REQUEST['PBBterutang'];
	$tglSPPT			= $_REQUEST['tglSPPT'];
	$persenPengurangan	= $_REQUEST['persenPengurangan'];
	$bsrPenghasilan 	= $_REQUEST['bsrPenghasilan'];
	$persenUpdate		= $_REQUEST['persenUpdate'];
	// $LHPNumber 			= generateLHPNumber();
	// if($LHPNumber)
		// $LHPDate = date('Y-m-d');
	// else
		// $LHPDate = NULL;	
	
	$selectedRadio		= $_REQUEST['rekomendasi'];
	if($selectedRadio == 'a'){
		//Minimum Tagihan
		//$minTagihan = $appConfig['minimum_sppt_pbb_terhutang'];
		//Persen disetujui
		$persenTerVerifikasi= $_REQUEST['persenTerVerifikasi'];
		$alasanSetuju = $_REQUEST['alasan'];
		$alasanTolak = "";
	} else if($selectedRadio == 'b'){
		$alasanTolak = $_REQUEST['alasan'];
		$alasanSetuju = "";
	} else if($selectedRadio == 'c'){
		$persenTerVerifikasi= 0;
		$alasanSetuju = $_REQUEST['alasan'];
		$alasanTolak = "";
	} 
	
	//variable sumber penghasilan
	$sbrPenghasilan		= array();
	if($_REQUEST['sbrPenghasilan1']) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan1']);
	if($_REQUEST['sbrPenghasilan2']) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan2']);
	if($_REQUEST['sbrPenghasilan3']) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan3']);
	if($_REQUEST['sbrPenghasilan4']) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan4']);
	if($_REQUEST['sbrPenghasilan5']) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan5']);
	$gabSbrPenghasilan	= implode("#",$sbrPenghasilan);
	//variable alasan
	$alasan				= array();
	if($_REQUEST['alasan1']) array_push($alasan, $_REQUEST['alasan1']);
	if($_REQUEST['alasan2']) array_push($alasan, $_REQUEST['alasan2']);
	if($_REQUEST['alasan3']) array_push($alasan, $_REQUEST['alasan3']);
	if($_REQUEST['alasan4']) array_push($alasan, $_REQUEST['alasan4']);
	$gabAlasan			= implode("#",$alasan);
	$nmKuasa 			= mysql_real_escape_string($_REQUEST['nmKuasa']);
    $nmWp 				= mysql_real_escape_string($_REQUEST['nmWp']);
    $tglMasuk 			= substr($_REQUEST['tglMasuk'],6,4).'-'.substr($_REQUEST['tglMasuk'],3,2).'-'.substr($_REQUEST['tglMasuk'],0,2);
    $almtWP 			= mysql_real_escape_string($_REQUEST['almtWP']);
    $rtWP 				= $_REQUEST['rtWP'];
    $rwWP 				= $_REQUEST['rwWP'];
    $propinsiWP 		= $_REQUEST['propinsi'];
    $kabupatenWP 		= $_REQUEST['kabupaten'];
    $kecamatanWP		= $_REQUEST['kecamatan'];
    $kelurahanWP 		= $_REQUEST['kelurahan'];
    $hpWP 				= $_REQUEST['hpWP'];
    $nop 				= $_REQUEST['nop'];
	$nopHidden			= $_REQUEST['nopHidden'];
    $almtOP 			= mysql_real_escape_string($_REQUEST['almtOP']);
    $rtOP 				= $_REQUEST['rtOP'];
    $rwOP 				= $_REQUEST['rwOP'];
    $kecamatanOP		= $_REQUEST['kecamatanOP'];
    $kelurahanOP 		= $_REQUEST['kelurahanOP'];
    $attachment 		= $_REQUEST['attachment'];
	
	#Jika pada modul persetujuan 
	if(($dis==1) && ($tab==30)){
		#UPDATE cppmod_pbb_service_reduce FOR PERSETUJUAN-->OK
		$qry1 = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_ARGUEMENT='{$gabAlasan}',CPM_RE_INCOME='{$bsrPenghasilan}',CPM_RE_INCOME_SOURCE='{$gabSbrPenghasilan}',CPM_RE_PERCENT='{$persenPengurangan}', CPM_RE_PERCENT_APPROVE='{$persenTerVerifikasi}' WHERE CPM_RE_SID='{$nomorHidden}'";
		
		#UPDATE cppmod_pbb_services FOR PERSETUJUAN-->OK
		$qry2 = "UPDATE cppmod_pbb_services SET CPM_STATUS='{$status}', CPM_REFUSAL_REASON='{$alasanTolak}', 
				CPM_APPROVER='{$uname}', CPM_DATE_APPROVER='{$dateValidate}', CPM_REPRESENTATIVE='{$nmKuasa}' WHERE CPM_ID='{$nomorHidden}'";
                                
	}
	else if($tab==33){
		#UPDATE cppmod_pbb_service_reduce
		$q1  = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_ARGUEMENT='{$gabAlasan}',CPM_RE_INCOME='{$bsrPenghasilan}',CPM_RE_INCOME_SOURCE='{$gabSbrPenghasilan}',CPM_RE_PERCENT='{$persenPengurangan}',CPM_RE_PERCENT_APPROVE='{$persenUpdate}' WHERE CPM_RE_SID='{$nomorHidden}'";
                
		#UPDATE GW PBB_SPPT

                $q2 = "UPDATE cppmod_pbb_sppt_pengurangan SET CPM_PNG_PERSEN = '{$persenUpdate}', 
				CPM_PNG_NILAI = (SELECT({$persenUpdate}/100)*(SELECT CPM_SPPT_DUE FROM cppmod_pbb_services WHERE CPM_ID = '{$nomorHidden}'))
				WHERE CPM_PNG_NOP = '{$nopHidden}' AND CPM_PNG_TAHUN = '".$_REQUEST['thnPajakHidden']."'";
                $q3 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}' WHERE CPM_ID = '{$nomorHidden}' ";
                
	} else {
	
		if(!isExistSID($nomor)){
			$qry1 = "INSERT INTO cppmod_pbb_service_reduce (CPM_RE_ID,CPM_RE_SID,CPM_RE_ARGUEMENT,CPM_RE_INCOME,CPM_RE_INCOME_SOURCE,CPM_RE_PERCENT,CPM_RE_PERCENT_APPROVE) VALUES ('default','{$nomor}','{$gabAlasan}','{$bsrPenghasilan}','{$gabSbrPenghasilan}','{$persenPengurangan}','{$persenTerVerifikasi}')";
		}else{
			$qry1 = "UPDATE cppmod_pbb_service_reduce SET CPM_RE_ARGUEMENT='{$gabAlasan}',CPM_RE_INCOME='{$bsrPenghasilan}',CPM_RE_INCOME_SOURCE='{$gabSbrPenghasilan}',CPM_RE_PERCENT='{$persenPengurangan}',CPM_RE_PERCENT_APPROVE='{$persenTerVerifikasi}' WHERE CPM_RE_SID = '{$nomor}'";
		}
		
		if(($dis==0) && (($tab==10) || ($tab==12))){
			$qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', 
					CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', CPM_WP_KELURAHAN='{$kelurahanWP}', 
					CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', 
					CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', CPM_OP_ADDRESS='{$almtOP}', 
					CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', 
					CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
					CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, 
					CPM_SPPT_DUE={$pbbterutang}, CPM_SPPT_YEAR='{$thnPajak}',CPM_REFUSAL_REASON='{$alasanTolak}', 
					CPM_VALIDATOR='{$uname}', CPM_DATE_VALIDATE='{$dateValidate}' WHERE CPM_ID = '{$nomor}' ";

		} else if(($dis==0) && ($tab==20)){
			$qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', 
					CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', CPM_WP_KELURAHAN='{$kelurahanWP}', 
					CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', 
					CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', CPM_OP_ADDRESS='{$almtOP}', 
					CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', 
					CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
					CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, 
					CPM_SPPT_DUE={$pbbterutang}, CPM_SPPT_YEAR='{$thnPajak}',CPM_APPROVAL_REASON='{$alasanSetuju}',CPM_REFUSAL_REASON='{$alasanTolak}', 
					CPM_VERIFICATOR='{$uname}', CPM_DATE_VERIFICATION='{$dateValidate}' WHERE CPM_ID = '{$nomor}' ";
		}
	}
	
	if($tab==33){
		$r1 = $r2 = $r3 = false;
                $r2 = mysqli_query($DBLink, $q2);
		if($r2){
			$r1 = mysqli_query($DBLink, $q1);
                        if($r1){
                                $r3 = mysqli_query($DBLink, $q3);

                        } else {
                                echo mysqli_error($DBLink);
                        }
		} else {
			echo mysqli_error($DBLink);
		}	
		if (( $r1 === false ) || ($r2 === false) || ($r3 === false)){
				echo $q1 ."<br>"; 
				echo $q2 ."<br>"; 
				echo mysqli_error($DBLink);
				exit;
		} 
		if($r1 && $r2 && $r3){
			echo 'Data berhasil disimpan...!';
			$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
			echo "<script language='javascript'>
					$(document).ready(function(){
						window.location = \"./main.php?param=".base64_encode($params)."\"
					})
				  </script>";
		}
		else{
			echo mysqli_error($DBLink);
		}
	} else {
		//echo 'test'; exit;
		$res1 = mysqli_query($DBLink, $qry1);
		$res2 = mysqli_query($DBLink, $qry2);
		if (( $res1 === false ) || ($res2 === false)){
				echo $qry1 ."<br>"; 
				echo $qry2 ."<br>"; 
				echo mysqli_error($DBLink);
				exit;
		} 
		
		if(($selectedRadio == 'a')&&($tab==30)){
			#INSERT KE TABEL cppmod_pbb_sppt_pengurangan-->OK
			insertToSPPTPengurangan($nomorHidden,$nopHidden,$persenTerVerifikasi);
			
			if($appConfig['NOMOR_LHP_OTOMATIS']=='1' && $arConfig['usertype'] =='persetujuan'){
                    //Insert to GENERATE_SK_NUMBER
                    if(!isHaveSKNumber($nomor)){
                        $SKNumber 			= generateSKNumber();
                        $Date 				= date('Y-m-d');
                        $upReduce = updateReduce($nomor, $SKNumber, $Date);
                        if($upReduce){
                                $tmp      = str_replace($appConfig['NOMOR_SK_FORMAT'],"",$SKNumber);
                                $qry   = "INSERT INTO cppmod_pbb_generate_sk_number (CPM_SK_ID, CPM_SK_NO, CPM_DATE_CREATED) VALUES ('$SKNumber', '$tmp','$Date')";
                                $res   = mysqli_query($DBLink, $qry);
                                if ($res === false){
                                        echo $qry ."<br>";
                                        echo mysqli_error($DBLink);
                                }
                        }
                    }
                }
		}
		
		if($res1 && $res2){
			if($status == 1){
				echo 'Data berhasil disimpan...!';
			} else if($status == 2){
				echo 'Data berhasil dikirim ke Verifikasi...!';
			} else if($status == 3){
				echo 'Data berhasil dikirim ke Persetujuan...!';
			} else if($status == 4){
				echo 'Data berhasil disetujui...!';
			}
			$params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m'];
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

$save 		 = $_REQUEST['btn-save'];
$rekomendasi = $_REQUEST['rekomendasi'];
$validator 	 = $arConfig['usertype'];
// echo "<pre>";
// print_r($_REQUEST); exit;

if($save == 'Simpan') {
    save(1);
} else if ($save == 'Finalkan') {
    save(2); 
} else if (($save == 'Kirim') && ($arConfig['usertype'] == 'verifikasi')){
		if ($rekomendasi == "a" || $rekomendasi == "c"){
			save(3);
		}
		else if ($rekomendasi == "b"){
			save(5);
		}
} else if (($save == 'Kirim') && ($arConfig['usertype'] == 'persetujuan')){
		if ($rekomendasi == "a" || $rekomendasi == "c"){
			save(4);
		}
		else if ($rekomendasi == "b"){
			save(6);
		}
} else {
    $svcid  		= @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
	$dis			= @isset($_REQUEST['dis']) ? $_REQUEST['dis'] : 0;
	$tab			= @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : "";
	$initData 	 	= getInitData($svcid);
	$initDataRed 	= getReduce($svcid);
	//echo $svcid;
	#echo $dis;
	echo "<script language=\"javascript\">var axx = '".base64_encode($_REQUEST['a'])."'</script> ";
	echo formPenerimaan($initData, $initDataRed);	
}
/*
	Keterangan Status:
	0 Pending di Penerimaan
	1 Pending di Pendata
	2 Pending di Verifikasi
	3 Pending di Persetujuan
	4 Selesai
	5 Ditolak di Verifikasi 
	6 Ditolak di Persetujuan
*/
?>
..