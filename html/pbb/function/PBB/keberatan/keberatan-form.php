<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'keberatan', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "function/PBB/gwlink.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/payment/json.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";
//

echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig  = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($a);
$dbServices = new DbServices($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$nopz = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
$dataTB = getTanahBumi($nopz);

function getConfigValue($id, $key)
{
    global $DBLink;
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

function getPropinsi()
{
    global $DBLink;

    $qry = "select * from cppmod_tax_propinsi";
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TP_ID'],
                'name' => $row['CPC_TP_PROPINSI']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function getKabkota($idProv = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idProv) {
        $qwhere = " WHERE CPC_TK_PID='$idProv'";
    }

    $qry = "select * from cppmod_tax_kabkota " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        $data = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp = array(
                'id' => $row['CPC_TK_ID'],
                'pid' => $row['CPC_TK_PID'],
                'name' => $row['CPC_TK_KABKOTA']
            );
            $data[] = $tmp;
        }
        return $data;
    }
}

function getKecamatan($idKec = '', $idKab = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idKab) {
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
    } else if ($idKec) {
        $qwhere = " WHERE CPC_TKC_ID='$idKec'";
    }

    $qry = "select * from cppmod_tax_kecamatan " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
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

function getKelurahan($idKel = '', $idKec = "")
{
    global $DBLink;

    $qwhere = "";
    if ($idKec) {
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
    } else if ($idKel) {
        $qwhere = " WHERE CPC_TKL_ID='$idKel'";
    }

    $qry = "select * from cppmod_tax_kelurahan " . $qwhere;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
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

function isExistSID($nomor = "")
{
    global $DBLink;
    $query = "SELECT CPM_OB_SID FROM cppmod_pbb_service_objection WHERE CPM_OB_SID='$nomor'";

    $res = mysqli_query($DBLink, $query);
    $nRes = mysqli_num_rows($res);
    return $nRes;
}

function showZNT($initData, $kode_znt = '')
{
    global $dbUtils;

    $bZNT = array();
    $locCode = $initData['CPM_OP_KELURAHAN'];
    $bZNT = $dbUtils->getZNT(null, array("CPM_KODE_LOKASI" => $locCode));
    $opZNT = '';
    $opZNT .= "<option value='' " . ((isset($kode_znt) && $kode_znt == '') ? "selected" : "") . ">Pilih ZNT</option>";
    foreach ($bZNT as $row)
        $opZNT .= "<option value='" . $row['CPM_KODE_ZNT'] . "-" . $row['CPM_NIR'] . "' " . ((isset($kode_znt) && $kode_znt == $row['CPM_KODE_ZNT']) ? "selected" : "") . ">" . $row['CPM_KODE_ZNT'] . " - " . number_format($row['CPM_NIR'], 0, ',', '.') . "</option>";

    return $opZNT;
}

function showKelasBng($initData, $njop_m2)
{
    global $DBLink;
    $qry = "SELECT CPM_KELAS, (CPM_NJOP_M2*1000) as CPM_NJOP_M2 FROM cppmod_pbb_kelas_bangunan WHERE CPM_THN_AKHIR='9999' ORDER BY CPM_KELAS";
    $opKelas = "<option value='' " . ((isset($njop_m2) && $njop_m2 == '') ? "selected" : "") . ">Pilih Kelas</option>";
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
        return false;
    } else {

        while ($row = mysqli_fetch_assoc($res)) {
            $opKelas .= "<option value='" . $row['CPM_NJOP_M2'] . "' " . ((isset($njop_m2) && $njop_m2 == $row['CPM_NJOP_M2']) ? "selected" : "") . ">" . $row['CPM_KELAS'] . " - " . number_format($row['CPM_NJOP_M2'], 0, ',', '.') . "</option>";
        }
    }

    return $opKelas;
}

function getTanahBumi($nop = "")
{
    global $dbFinalSppt;

    $filter  = array();
    $filter['CPM_NOP'] = $nop;
    $final      = $dbFinalSppt->isNopExist($nop);
    if ($final)
        $data = $dbFinalSppt->get($id = "", $vers = "", $filter);
    else
        $data = $dbFinalSppt->getSusulan($id = "", $vers = "", $filter);
    return $data;
}

function getLastSKNumber()
{
    global $DBLink;

    $qry = "SELECT MAX(CPM_SK_NO) AS SK_NUMBER FROM cppmod_pbb_generate_sk_number";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['SK_NUMBER'];
        }
        return "0";
    }
}

function generateSKNumber()
{
    global $appConfig;

    $lastNumber = getLastSKNumber();
    $newNumber = $lastNumber + 1;
    if (trim($appConfig['NOMOR_SK_OTOMATIS']) == '1') {
        return $newNumber . $appConfig['NOMOR_SK_FORMAT'];
    } else
        return NULL;
}

function updateReduce($nomor = '', $noSK = '', $date = '')
{
    global $DBLink;

    $qry = "UPDATE cppmod_pbb_service_objection SET CPM_OB_SK_NUMBER = '$noSK', CPM_OB_SK_DATE = '$date' WHERE CPM_OB_SID='$nomor'";
    // echo $qry; exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else
        return $res;
}

function isHaveSKNumber($nomor)
{
    global $DBLink;

    $qry = "SELECT CPM_OB_SK_NUMBER AS SK_NUMBER FROM cppmod_pbb_service_objection WHERE CPM_OB_SID='$nomor'";
    // echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
        return false;
    } else {
        $SKNumber = '';
        while ($row = mysqli_fetch_assoc($res)) {
            $SKNumber = $row['SK_NUMBER'];
        }
        if ($SKNumber != null && $SKNumber != '') return true;
        else return false;
    }
}

function formPenerimaan($initData, $initDataOb)
{
    global $a, $m, $appConfig, $arConfig, $dis, $tab, $rekomendasi;

    $today = date("d-m-Y");
    $cityID = $appConfig['KODE_KOTA'];
    $cityName = $appConfig['NAMA_KOTA'];
    $optionCityOP = "<option valued=$cityID>$cityName</option>";

    $provID = $appConfig['KODE_PROVINSI'];
    $provName = $appConfig['NAMA_PROVINSI'];
    $optionProvOP = "<option valued=$provID>$provName</option>";


    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = $kecOP = $kelOP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = $optionKecOP = $optionKelOP = "";


    $bSlash = "\'";
    $ktip = "'";

    $allProv = getPropinsi();

    $optionProvWP = "";
    $kode_znt = isset($initDataOb['CPM_OB_ZNT_CODE']) ? $initDataOb['CPM_OB_ZNT_CODE'] : '';
    $optZNT = showZNT($initData, $kode_znt);
    //	$kode_znt_rec = $initDataOb['CPM_OB_ZNT_CODE_RECOMMEND'];
    //	$optZNTRec = showZNT($initData,$kode_znt_rec);
    $CPM_OB_NJOP_BANGUNAN_APP = isset($initDataOb['CPM_OB_NJOP_BANGUNAN_APP']) ? $initDataOb['CPM_OB_NJOP_BANGUNAN_APP'] : '';
    $optKelasBng = showKelasBng($initData, $CPM_OB_NJOP_BANGUNAN_APP);
    if ($initData['CPM_ID'] != '') {
        //$hiddenModeInput = '<input type="hidden" name="mode" value="edit">';

        $kecOP = getKecamatan($initData['CPM_OP_KECAMATAN']);
        $kelOP = getKelurahan($initData['CPM_OP_KELURAHAN']);

        foreach ($kecOP as $row) {
            if ($initData['CPM_OP_KECAMATAN'] == $row['id'])
                $optionKecOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKecOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kelOP as $row) {
            if ($initData['CPM_OP_KELURAHAN'] == $row['id'])
                $optionKelOP .= "<option value=" . $row['id'] . " selected=\"selected\">" . $row['name'] . "</option>";
            else
                $optionKelOP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
    } else {
        $nomor = generateNumber(date('Y'), date('m'));
        $kecWP = $kecOP = getKecamatan($cityID);
        $kelWP = $kelOP = getKelurahan($kecWP[0]['id']);

        foreach ($kecWP as $row) {
            $optionKecWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
        foreach ($kelWP as $row) {
            $optionKelWP .= "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }

        $optionKecOP = $optionKecWP;
        $optionKelOP = $optionKelWP;
    }

    $pisAlasan = array();
    if (isset($initDataOb['CPM_OB_ARGUEMENT']) && $initDataOb['CPM_OB_ARGUEMENT'] != '') {
        $pisAlasan = explode("#", $initDataOb['CPM_OB_ARGUEMENT']);
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

    if ($initData['CPM_TYPE'] != '')
        $html .= "
				var berkas = jenisBerkas[" . $initData['CPM_TYPE'] . "-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                    $('#berkas'+berkas[i]).show();
                }
			";


    $html .= "//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
                return value.match(new RegExp(\"^\" + param + \"$\"));
            });

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

            $('#propinsi').change(function(){
                getWilayah(1,$(this).val());
                $('#kecamatan').html(\"<option value=''>--Pilih Kabupaten Dulu--</option>\");
                $('#kelurahan').html(\"<option value=''>--Pilih Kecamatan Dulu--</option>\");
            });
            
            $('#kabupaten').change(function(){
                getWilayah(2,$(this).val());
            });
            
            $('#kecamatan').change(function(){
                getWilayah(3,$(this).val());
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
    <div class=\"col-md-12\">
    <div id=\"main-content-pengurangan\">
    <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
	" . (isset($hiddenModeInput) ? $hiddenModeInput : '') . "
	<input type=\"hidden\" name=\"nomorHidden\" id=\"nomorHidden\" readonly=\"readonly\"  maxlength=\"50\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\"/>
	<input type=\"hidden\" name=\"nopHidden\" id=\"nopHidden\" readonly=\"readonly\"  maxlength=\"50\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : $nop) . "\"/>
	<input type=\"hidden\" name=\"thnPajakHidden\" id=\"thnPajakHidden\" readonly=\"readonly\"  maxlength=\"50\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : $thnPajak) . "\"/>
	<input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
                      <table class=\"table\" border=\"0\" cellspacing=\"1\" cellpadding=\"10\">
                            <tr>
                              <td colspan=\"2\" align=\"center\"><strong><font size=\"+2\">Keberatan</font></strong><br /><br /></td>
                            </tr>
                            <tr>
                              <td align=\"center\">
                              <table class=\"table table-borderless\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                                    <tr>
                                      <td><strong>Nomor</strong></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"nomor\" id=\"nomor\" readonly=\"readonly\"   maxlength=\"50\" value=\"" . (($initData['CPM_ID'] != '') ? $initData['CPM_ID'] : $nomor) . "\" placeholder=\"Nomor\" />
                                      </td>
									  <td>&nbsp</td>
									  <td colspan=\"2\"><strong>Letak Objek Pajak</strong></td>
                                    </tr>
									<tr>
                                      <td><label for=\"nmKuasa\">Nama Kuasa</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\"   maxlength=\"50\" value=\"" . (($initData['CPM_REPRESENTATIVE'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_REPRESENTATIVE']) : '') . "\" placeholder=\"Nama Kuasa\" />
                                      </td>
									  <td>&nbsp</td>
									  <td></td>
                                      <td>
                                      </td>
                                    </tr>
									<tr>
                                      <td><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"nmWp\" id=\"nmWp\" readonly=\"readonly\"  maxlength=\"50\" value=\"" . (($initData['CPM_WP_NAME'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_NAME']) : '') . "\" placeholder=\"Nama Wajib Pajak\" />
                                      </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"almtOP\">Alamat OP</label></td>
                                      <td>
                                            <textarea style=\"width: 300px;\" class=\"form-control\" rows=\"2\" cols=\"40\" name=\"almtOP\" readonly=\"readonly\" id=\"almtOP\" placeholder=\"Alamat\">" . (($initData['CPM_OP_ADDRESS'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_OP_ADDRESS']) : '') . "</textarea>
                                        </td>
                                    </tr>
									<tr>
                                      <td><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\"  value=\"" . (($initData['CPM_DATE_RECEIVE'] != '') ? $initData['CPM_DATE_RECEIVE'] : $today) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                                      </td>
									  <td>&nbsp</td>
									  <td><label for=\"rtOP\">RT/RW</label></td>
                                      <td style=\"display:flex\">
                                        <input style=\"width: 70px;\" class=\"form-control\" type=\"text\" name=\"rtOP\" id=\"rtOP\"  readonly=\"readonly\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RT'] != '') ? $initData['CPM_OP_RT'] : '') . "\" placeholder=\"00\"/>
                                        <span style=\"margin:10px 10px 0 10px\">/</span>
                                        <input style=\"width: 70px;\" class=\"form-control\" type=\"text\" name=\"rwOP\" id=\"rwOP\"  readonly=\"readonly\" maxlength=\"3\" value=\"" . (($initData['CPM_OP_RW'] != '') ? $initData['CPM_OP_RW'] : '') . "\" placeholder=\"00\"/>
                                      </td>
                                    </tr>
									<tr>
                                      <td><label for=\"almtWP\">Alamat WP</label></td>
                                      <td>
                                            <textarea style=\"width: 300px;\"  class=\"form-control\" rows=\"2\" name=\"almtWP\" readonly=\"readonly\" id=\"almtWP\" placeholder=\"Alamat\">" . (($initData['CPM_WP_ADDRESS'] != '') ? str_replace($bSlash, $ktip, $initData['CPM_WP_ADDRESS']) : '') . "</textarea>
                                        </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"provinsiOP\">Provinsi</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"hidden\" name=\"propinsiOP\" value=\"" . $appConfig['KODE_PROVINSI'] . "\">
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"propinsiOPname\" readonly=\"readonly\"  value=\"" . $appConfig['NAMA_PROVINSI'] . "\">
                                      </td>
                                    </tr>
									<tr>
                                      <td><label for=\"rtWP\">RT/RW</label></td>
                                      <td style=\"display:flex\">
                                        <input style=\"width: 70px;\" class=\"form-control\" type=\"text\" name=\"rtWP\" readonly=\"readonly\" id=\"rtWP\"  size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RT'] != '') ? $initData['CPM_WP_RT'] : '') . "\" placeholder=\"00\"/>
                                        <span style=\"margin:10px 10px 0 10px\">/</span>
                                        <input style=\"width: 70px;\" class=\"form-control\" type=\"text\" name=\"rwWP\" readonly=\"readonly\" id=\"rwWP\"  size=\"3\" maxlength=\"3\" value=\"" . (($initData['CPM_WP_RW'] != '') ? $initData['CPM_WP_RW'] : '') . "\" placeholder=\"00\"/>
                                      </td>
									  <td>&nbsp</td>
									  <td><label for=\"kabupatenOP\">Kabupaten/Kota</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"hidden\" name=\"kabupatenOP\" value=\"" . $appConfig['KODE_KOTA'] . "\">
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kabupatenOPname\" readonly=\"readonly\" value=\"" . $appConfig['NAMA_KOTA'] . "\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td><label for=\"propinsi\">Provinsi</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"propinsi\" id=\"propinsi\" readonly=\"readonly\"  value=\"" . (($initData['CPM_WP_PROVINCE'] != '') ? $initData['CPM_WP_PROVINCE'] : '') . "\" placeholder=\"Provinsi\"/>
                                      </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"kecamatanOP\">Kecamatan</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"hidden\" name=\"kecamatanOP\" value=\"" . $initData['CPM_OP_KECAMATAN'] . "\">
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kecamatanOPname\" readonly=\"readonly\" value=\"" . $kecOP[0]['name'] . "\">
                                      </td>
                                    </tr>
                                    <tr>
                                      <td><label for=\"kabupaten\">Kabupaten/Kota</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kabupaten\" id=\"kabupaten\" readonly=\"readonly\" value=\"" . (($initData['CPM_WP_KABUPATEN'] != '') ? $initData['CPM_WP_KABUPATEN'] : '') . "\" placeholder=\"Kabupaten\"/>
                                      </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"kelurahanOP\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                                      <td>
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"hidden\" name=\"kelurahanOP\" value=\"" . $initData['CPM_OP_KELURAHAN'] . "\">
                                        <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kelurahanOPname\" readonly=\"readonly\" value=\"" . $kelOP[0]['name'] . "\">
                                      </td>
                                    </tr>
                                    <tr>
                                        <td><label for=\"kecamatan\">Kecamatan</label></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kecamatan\" id=\"kecamatan\" readonly=\"readonly\" value=\"" . (($initData['CPM_WP_KECAMATAN'] != '') ? $initData['CPM_WP_KECAMATAN'] : '') . "\" placeholder=\"Kecamatan\"/>
                                        </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"PBBterutang\">PBB yang terutang</label></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"PBBterutang\" id=\"PBBterutang\" readonly=\"readonly\" maxlength=\"50\" value=\"" . (($initData['CPM_SPPT_DUE'] != '') ? $initData['CPM_SPPT_DUE'] : '') . "\" placeholder=\"PBB yang terutang\" />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for=\"kelurahan\">" . $appConfig['LABEL_KELURAHAN'] . "</label></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"kelurahan\" id=\"kelurahan\" readonly=\"readonly\" value=\"" . (($initData['CPM_WP_KELURAHAN'] != '') ? $initData['CPM_WP_KELURAHAN'] : '') . "\" placeholder=\"" . $appConfig['LABEL_KELURAHAN'] . "\"/>
                                        </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"tglSPPT\">Tahun Pajak</label></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"thnPajak\" id=\"thnPajak\"  readonly=\"readonly\" size=\"10\" maxlength=\"10\" value=\"" . (($initData['CPM_SPPT_YEAR'] != '') ? $initData['CPM_SPPT_YEAR'] : '') . "\" placeholder=\"Tahun Pajak\"/>                                      
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. HP WP</strong></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"hpWP\" id=\"hpWP\" size=\"15\" maxlength=\"15\" value=\"" . (($initData['CPM_WP_HANDPHONE'] != '') ? $initData['CPM_WP_HANDPHONE'] : '') . "\" placeholder=\"Nomor HP\" />
                                        </td>
                                        <td>&nbsp</td>
                                        <td><label for=\"tglSPPT\"></label></td>
                                        <td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for=\"nop\">NOP</label></td>
                                        <td>
                                            <input style=\"width: 300px;\" class=\"form-control\" type=\"text\" name=\"nop\" readonly=\"readonly\" id=\"nop\"   maxlength=\"50\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" placeholder=\"NOP\" />
                                        </td>
                                            <td>&nbsp</td>
                                        <td colspan=\"2\" rowspan=\"2\">
                                        Alasan Keberatan <br><br> 
                                            <div>
                                                <label for=\"alasan1\">*</label>
                                                <input class=\"form-control\" type=\"text\" name=\"alasan1\" id=\"alasan1\" maxlength=\"500\" value=\"" . (isset($pisAlasan[0]) ? $pisAlasan[0] : '') . "\" placeholder=\"Alasan 1\" />
                                            </div>
                                            <div>
                                                <label for=\"alasan2\">*</label>
                                                <input class=\"form-control\" type=\"text\" name=\"alasan2\" id=\"alasan2\" maxlength=\"500\" value=\"" . (isset($pisAlasan[1]) ? $pisAlasan[1] : '') . "\"placeholder=\"Alasan 2\" />
                                            </div>
                                            <div>
                                                <label for=\"alasan3\">*</label>
                                                <input class=\"form-control\" type=\"text\" name=\"alasan3\" id=\"alasan3\" maxlength=\"500\" value=\"" . (isset($pisAlasan[2]) ? $pisAlasan[2] : '') . "\"placeholder=\"Alasan 3\" />
                                            </div>
                                            <div>
                                                <label for=\"alasan4\">*</label>
                                                <input class=\"form-control\" type=\"text\" name=\"alasan4\" id=\"alasan4\" maxlength=\"500\" value=\"" . (isset($pisAlasan[3]) ? $pisAlasan[3] : '') . "\"placeholder=\"Alasan 4\" />
                                            </div>            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign=\"top\" colspan=\"3\" rowspan=\"2\">Kelengkapan Dokumen : <br><br>
                                            <ol id=\"lampiran\" style=\"margin-left: -20px;width:500px\">
                                                <li id=\"berkas1\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"1\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 1)) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                                                <li id=\"berkas2\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"2\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 2)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB yang akan diajukan Permohonan Keberatan.</li>
                                                <li id=\"berkas3\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"4\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 4)) ? "checked=\"checked\"" : "") . "> Tidak ada tunggakan PBB tahun-tahun sebelumnya.</li>
                                                <li id=\"berkas4\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"8\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 8)) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                                <li id=\"berkas5\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"16\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 16)) ? "checked=\"checked\"" : "") . "> Fotocopi Bukti Kepemilikan Tanah : Sertifikat / Pengoperan / Pengakuan Hak / Akta / Dokumen lain yang dipersamakan atas Objek PBB yang diajukan Permohonan Keberatan.</li>
                                                <li id=\"berkas6\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"32\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 32)) ? "checked=\"checked\"" : "") . "> Fotocopi SPPT PBB tetangga terdekat.</li>
                                                <li id=\"berkas7\" class=\"berkas\" ><input type=\"checkbox\"  name=\"lampiran[]\" value=\"64\" class=\"attach\" " . ((($initData['CPM_TYPE'] == 10) && ($initData['CPM_ATTACHMENT'] & 64)) ? "checked=\"checked\"" : "") . "> Fotocopi Izin Mendirikan Bangunan (IMB), apabila objek yang diajukan keberatan memiliki bangunan.<br/>(Khusus bangunan yang bersifat komersil)</li>
                                            </ol>
									    </td>                                          
                                    </tr>
									<tr>
										<td colspan=\"2\"></td>
									</tr>
                                </table></td>
                            </tr>";
    $form = "<script>
                            function ygdirubahClick(cb) {
                                document.getElementById(cb.value).disabled=!cb.checked;
                            }
							function changeDis() {
                                                                
								for (var i=0;i<document.forms[0].elements.length;i++) {
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"y\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									}
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"n\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									} 
								}
							}
							</script>
							<tr align=\"center\">
								<td colspan=\"2\">
									<form form name=\"form\" id=\"form\" method=\"post\">
										<p id=\"recMsg\"></p>
										<table border=0 cellpadding=5>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												<input type=\"radio\" name=\"rekomendasi\" id=\"rekomendasi\" value=\"y\" onchange=\"changeDis()\"><label> Setuju dengan</label><br/>
                                                <input type=\"checkbox\" name=\"ygdirubah[]\" value=\"luas_bumi\" onclick='ygdirubahClick(this);'> <label>Luas Bumi</label> <input type=\"text\" id=\"luas_bumi\" name=\"luas_bumi\" value=\"\" width=\"7\" maxlength=\"9\" disabled> m2<br/>
                                                <input type=\"checkbox\" name=\"ygdirubah[]\" value=\"znt\" onclick='ygdirubahClick(this);'> <label>Nilai NIR</label> <select name=\"znt\" id=\"znt\" disabled>" . $optZNT . " </select><br/>
                                                <input type=\"checkbox\" name=\"ygdirubah[]\" value=\"luas_bng\" onclick='ygdirubahClick(this);'> <label>Luas Bangunan</label> <input type=\"text\" id=\"luas_bng\" name=\"luas_bng\" value=\"\" width=\"7\" maxlength=\"9\" disabled> m2<br/>
                                                <input type=\"checkbox\" name=\"ygdirubah[]\" value=\"nilai_bng\" onclick='ygdirubahClick(this);'> <label>Kelas - NJOP Bangunan</label> <select name=\"nilai_bng\" id=\"nilai_bng\" disabled>" . $optKelasBng . " </select> /m2<br />
                                                <input type=\"checkbox\" name=\"ygdirubah[]\" value=\"biaya_sppt\" onclick='ygdirubahClick(this);'> <label>Membayar sesuai ketentuan</label> <input type=\"text\" id=\"biaya_sppt\" name=\"biaya_sppt\" value=\"\" width=\"7\" disabled>
											</tr>
											<tr><td valign=\"top\"class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" id=\"rekomendasi\" value=\"n\" onchange=\"changeDis()\"> Tolak</label></td></tr>
											<tr><td class=\"tbl-rekomen\">Alasan<br><textarea name=\"alasan\" id=\"alasan\" cols=70 rows=7 disabled></textarea></td></tr>
											<tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\" ></td></tr>
										</table>
									</form>
								</td>
                            </tr>";
    $hasilVerifikasi = "";
    if (isset($initDataOb['CPM_OB_LUAS_TANAH_APP']) && $initDataOb['CPM_OB_LUAS_TANAH_APP'] != NULL) $hasilVerifikasi .= "<label>Luas Bumi</label> <input type=\"text\" id=\"luas_bumiV\" name=\"luas_bumiV\" value=\"" . $initDataOb['CPM_OB_LUAS_TANAH_APP'] . "\" width=\"7\" maxlength=\"9\" disabled> m2<br/>";
    if (isset($initDataOb['CPM_OB_ZNT_CODE']) && $initDataOb['CPM_OB_ZNT_CODE'] != NULL) $hasilVerifikasi .= "<label>Nilai NIR </label><select name=\"zntV\" id=\"zntV\" disabled>" . $optZNT . "</select><br/>";
    if (isset($initDataOb['CPM_OB_LUAS_BANGUNAN_APP']) && $initDataOb['CPM_OB_LUAS_BANGUNAN_APP'] != NULL) $hasilVerifikasi .= "<label>Luas Bangunan</label> <input type=\"text\" id=\"luas_bngV\" name=\"luas_bngV\" value=\"" . $initDataOb['CPM_OB_LUAS_BANGUNAN_APP'] . "\" width=\"7\" maxlength=\"9\" disabled> m2<br/>";
    if (isset($initDataOb['CPM_OB_NJOP_BANGUNAN_APP']) && $initDataOb['CPM_OB_NJOP_BANGUNAN_APP'] != NULL) $hasilVerifikasi .= "<label>Kelas - NJOP Bangunan</label> <select name=\"nilai_bngV\" id=\"nilai_bngV\" disabled>" . $optKelasBng . " </select> /m2<br>";
    $formPersetujuan = "<script>
							function ygdirubahClick(cb) {
                                document.getElementById(cb.value).disabled=!cb.checked;
                            }
							function changeDis() {
                                                                
								for (var i=0;i<document.forms[0].elements.length;i++) {
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"y\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('ygdirubah_luas_bumi').disabled=false;
                                        document.getElementById('luas_bumi').disabled=!document.getElementById('ygdirubah_luas_bumi').checked;
										document.getElementById('ygdirubah_znt').disabled=false;
                                        document.getElementById('znt').disabled=!document.getElementById('ygdirubah_znt').checked;
										document.getElementById('ygdirubah_luas_bng').disabled=false;
                                        document.getElementById('luas_bng').disabled=!document.getElementById('ygdirubah_luas_bng').checked;
										document.getElementById('ygdirubah_nilai_bng').disabled=false;
                                        document.getElementById('nilai_bng').disabled=!document.getElementById('ygdirubah_nilai_bng').checked;
                                        document.getElementById('ygdirubah_biaya_sppt').disabled=false;
                                        document.getElementById('biaya_sppt').disabled=!document.getElementById('ygdirubah_biaya_sppt').checked;
                                        document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									}
									if ((document.forms[0].elements[i].name == \"rekomendasi\" && document.forms[0].elements[i].value == \"n\" && document.forms[0].elements[i].checked == true)){
										document.getElementById('ygdirubah_luas_bumi').disabled=true;
                                        document.getElementById('luas_bumi').disabled=true;
										document.getElementById('ygdirubah_znt').disabled=true;
                                        document.getElementById('znt').disabled=true;
										document.getElementById('ygdirubah_luas_bng').disabled=true;
                                        document.getElementById('luas_bng').disabled=true;
										document.getElementById('ygdirubah_nilai_bng').disabled=true;
                                        document.getElementById('nilai_bng').disabled=true;
                                        document.getElementById('ygdirubah_biaya_sppt').disabled=true;
                                        document.getElementById('biaya_sppt').disabled=true;
                                        document.getElementById('alasan').disabled=false;
										document.getElementById('btn-save').disabled=false;
									} 
								}
							}
							</script>
							<tr align=\"center\">
								<td colspan=\"2\">
									<form form name=\"form\" id=\"form\" method=\"post\">
										<p id=\"recMsg\"></p>
										<table border=0 cellpadding=5>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Informasi Verifikasi</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												<label> Setuju dengan</label><br/>
                                                " . (($initData['CPM_STATUS'] == '3') ? $hasilVerifikasi . 'Dengan alasan sebagai berikut:<br><label><b>' . (($initData['CPM_APPROVAL_REASON'] != '') ? $initData['CPM_APPROVAL_REASON'] : 'Tidak ada alasan yang tercatat') . '</b></label>' : '<label>Ditolak, dengan alasan sebagai berikut:<br><label><b>' . (($initData['CPM_REFUSAL_REASON'] != '') ? $initData['CPM_REFUSAL_REASON'] : 'Tidak ada alasan yang tercatat') . '</b></label>') . "
												</td>	
											</tr>
											<tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
												<input type=\"radio\" name=\"rekomendasi\" id=\"rekomendasi\" value=\"y\" onchange=\"changeDis()\"><label> Setuju dengan</label><br/>
                                                <input type=\"checkbox\" id=\"ygdirubah_luas_bumi\" name=\"ygdirubah[]\" value=\"luas_bumi\" onclick='ygdirubahClick(this);' " . (isset($initDataOb['CPM_OB_LUAS_TANAH_APP']) && $initDataOb['CPM_OB_LUAS_TANAH_APP'] != NULL ? 'checked' : '') . " disabled> <label>Luas Bumi</label> <input type=\"text\" id=\"luas_bumi\" name=\"luas_bumi\" value=\"" . (isset($initDataOb['CPM_OB_LUAS_TANAH_APP']) && $initDataOb['CPM_OB_LUAS_TANAH_APP'] != NULL ? $initDataOb['CPM_OB_LUAS_TANAH_APP'] : '') . "\" width=\"7\" maxlength=\"9\" disabled> m2<br/>
                                                <input type=\"checkbox\" id=\"ygdirubah_znt\" name=\"ygdirubah[]\" value=\"znt\" onclick='ygdirubahClick(this);' " . (isset($initDataOb['CPM_OB_ZNT_CODE']) && $initDataOb['CPM_OB_ZNT_CODE'] != NULL ? 'checked' : '') . " disabled> <label>Nilai NIR</label> <select name=\"znt\" id=\"znt\" disabled>" . $optZNT . " </select><br/>
                                                <input type=\"checkbox\" id=\"ygdirubah_luas_bng\" name=\"ygdirubah[]\" value=\"luas_bng\" onclick='ygdirubahClick(this);' " . (isset($initDataOb['CPM_OB_LUAS_BANGUNAN_APP']) && $initDataOb['CPM_OB_LUAS_BANGUNAN_APP'] != NULL ? 'checked' : '') . " disabled> <label>Luas Bangunan</label> <input type=\"text\" id=\"luas_bng\" name=\"luas_bng\" value=\"" . (isset($initDataOb['CPM_OB_LUAS_BANGUNAN_APP']) ? $initDataOb['CPM_OB_LUAS_BANGUNAN_APP'] : '') . "\" width=\"7\" maxlength=\"9\" disabled> m2<br/>
                                                <input type=\"checkbox\" id=\"ygdirubah_nilai_bng\" name=\"ygdirubah[]\" value=\"nilai_bng\" onclick='ygdirubahClick(this);' " . (isset($initDataOb['CPM_OB_NJOP_BANGUNAN_APP']) && $initDataOb['CPM_OB_NJOP_BANGUNAN_APP'] != NULL ? 'checked' : '') . " disabled> <label>Kelas - NJOP Bangunan</label> <select name=\"nilai_bng\" id=\"nilai_bng\" disabled>" . $optKelasBng . " </select> /m2<br />
                                                <input type=\"checkbox\" id=\"ygdirubah_biaya_sppt\" name=\"ygdirubah[]\" value=\"biaya_sppt\" onclick='ygdirubahClick(this);' " . (isset($initDataOb['CPM_OB_BIAYA_SPPT']) && $initDataOb['CPM_OB_BIAYA_SPPT'] != NULL ? 'checked' : '') . " disabled> <label>Membayar sesuai pengajuan</label> <input type=\"text\" id=\"biaya_sppt\" name=\"biaya_sppt\" value=\"" . (isset($initDataOb['CPM_OB_BIAYA_SPPT']) ? $initDataOb['CPM_OB_BIAYA_SPPT'] : '') . "\" width=\"7\" disabled>
											</tr>
                                            <tr><td valign=\"top\"class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" id=\"rekomendasi\" value=\"n\" onchange=\"changeDis()\"> Tolak</label></td></tr>
                                            <tr><td>&nbsp;</td></tr>
											<tr><td class=\"tbl-rekomen\">Alasan<br><textarea name=\"alasan\" id=\"alasan\" cols=70 rows=7 disabled title=\"Alasan wajib diisi\"></textarea></td></tr>
											<tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim\" ></td></tr>
										</table>
									</form>
								</td>
                            </tr>";
    //				$formApp = "<tr align=\"center\">
    //								<td colspan=\"2\">
    //									 <form form name=\"form\" id=\"form\" method=\"post\">
    //										<p id=\"recMsg\"></p>
    //										<table border=0 cellpadding=5>
    //											<tr><td class=\"tbl-rekomen\" valign=\"top\" colspan=2>
    //												<input type=\"hidden\" name=\"rekomendasi\" value=\"y\">
    //                                                                                                <label>Nilai ZNT yang disetujui</label>
    //												<select name=\"znt\" id=\"znt\">
    //													 ".$optZNT." 
    //												</select>
    //											</tr>
    //                                                                                        <tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\">
    //                                                                                        <input type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\">&nbsp;
    //											<input type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=".base64_encode("a=".$a."&m=".$m."")."\"' /></td></tr>
    //										</table>
    //									</form>
    //								</td>
    //                            </tr>"; 
    $simpan = "<tr>
                                <td colspan=\"2\" align=\"center\" valign=\"middle\">
                                    <button class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\">Simpan</button>
                                    &nbsp;
                                    <button class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Finalkan\">Finalkan</button>
                                    &nbsp;
                                    <button class=\"btn btn-primary bg-maka\" type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "") . "\"'>Batal</button>
                                </td>
                            </tr>";
    $end = "<tr>
                                <td colspan=\"2\" align=\"center\" valign=\"middle\">&nbsp;</td>
                            </tr>
                        </table>
                    </form>
                </div>
                </div>";

    if (($dis == 0) && (($tab == 10) || ($tab == 12))) {
        $html .= $simpan . $end;
    } else if (($dis == 0) && ($tab == 20)) { //1
        $html .= $form . $end;
    } else if (($dis == 0) && ($tab == 30)) { //1
        $html .= $formPersetujuan . $end;
    } else if (($dis == 0) && ($tab == 33)) { //1
        //$html .= $formApp . $end;
        $html .= $end;
    } else if ($dis == 0) { //1
        $html .= $end;
    }
    return $html;
}

function getInitData($id = "")
{
    global $DBLink;

    if ($id == '') return getDataDefault();

    $qry = "select * from cppmod_pbb_services where CPM_ID='{$id}'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
        return getDataDefault();
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            $row['CPM_DATE_RECEIVE'] = substr($row['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($row['CPM_DATE_RECEIVE'], 0, 4);
            return $row;
        }
    }
}

function getObjection($id = "")
{
    global $DBLink;

    $qry = "select * from cppmod_pbb_service_objection where CPM_OB_SID='{$id}'";
    //echo $qry;exit;
    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            //$row['CPM_RE_DATE_SPPT'] = substr($row['CPM_RE_DATE_SPPT'],8,2).'-'.substr($row['CPM_RE_DATE_SPPT'],5,2).'-'.substr($row['CPM_RE_DATE_SPPT'],0,4);
            return $row;
        }
    }
}

function getDataDefault()
{
    $default = array(
        'CPM_ID' => '', 'CPM_TYPE' => '', 'CPM_REPRESENTATIVE' => '', 'CPM_WP_NAME' => '', 'CPM_WP_ADDRESS' => '', 'CPM_WP_RT' => '', 'CPM_WP_RW' => '',
        'CPM_WP_KELURAHAN' => '', 'CPM_WP_KECAMATAN' => '', 'CPM_WP_KABUPATEN' => '', 'CPM_WP_PROVINCE' => '', 'CPM_WP_HANDPHONE' => '', 'CPM_OP_KECAMATAN' => '', 'CPM_OP_KELURAHAN' => '',
        'CPM_OP_RW' => '', 'CPM_OP_RT' => '', 'CPM_OP_ADDRESS' => '', 'CPM_OP_NUMBER' => '', 'CPM_ATTACHMENT' => ''
    );
}

function getLastNumber($year, $mon)
{
    global $DBLink;

    $qry = "select SUBSTRING(max(CPM_ID), -3) as CPM_ID from cppmod_pbb_services where CPM_ID like 'SPOP/{$year}/{$mon}%'";

    $res = mysqli_query($DBLink, $qry);
    if (!$res) {
        echo $qry . "<br>";
        echo mysqli_error($DBLink);
    } else {
        while ($row = mysqli_fetch_assoc($res)) {
            return $row['CPM_ID'];
        }

        return "000";
    }
}

function generateNumber($year, $mon)
{
    $lastNumber = getLastNumber($year, $mon);
    $newNumber = $lastNumber + 1;
    return "SPOP/" . $year . "/" . $mon . "/" . substr('000' . $newNumber, -3);
}

function save($status)
{
    global $data, $DBLink, $uname, $dis, $tab, $appConfig, $arConfig, $validator, $rekomendasi, $dbGwCurrent, $dbFinalSppt, $dataTB, $initDataOb;
    // echo "<pre>";
    // print_r($_REQUEST);
    // echo "</pre>";
    // exit;

    //	echo $tab.'  = '.$dis;exit();
    $dateValidate        = date("Y-m-d");
    $nomor                 = $_REQUEST['nomor'];
    $nomorHidden        = $_REQUEST['nomorHidden'];
    $thnPajak            = $_REQUEST['thnPajak'];
    $thnPajakHidden        = $_REQUEST['thnPajakHidden'];
    $pbbterutang        = $_REQUEST['PBBterutang'];
    $tglSPPT            = isset($_REQUEST['tglSPPT']) ? $_REQUEST['tglSPPT'] : '';
    //$alasanTolak		= $_REQUEST['alasan'];

    $selectedRadio        = $_REQUEST['rekomendasi'];
    if ($selectedRadio == 'y') {
        $alasanSetuju = $_REQUEST['alasan'];
        $alasanTolak = "";
    } else if ($selectedRadio == 'n') {
        $alasanTolak = $_REQUEST['alasan'];
        $alasanSetuju = "";
    }

    //variable sumber penghasilan
    $sbrPenghasilan        = array();
    if (isset($_REQUEST['sbrPenghasilan1'])) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan1']);
    if (isset($_REQUEST['sbrPenghasilan2'])) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan2']);
    if (isset($_REQUEST['sbrPenghasilan3'])) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan3']);
    if (isset($_REQUEST['sbrPenghasilan4'])) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan4']);
    if (isset($_REQUEST['sbrPenghasilan5'])) array_push($sbrPenghasilan, $_REQUEST['sbrPenghasilan5']);
    $gabSbrPenghasilan    = implode("#", $sbrPenghasilan);
    //variable alasan
    $alasan                = array();
    if ($_REQUEST['alasan1']) array_push($alasan, $_REQUEST['alasan1']);
    if ($_REQUEST['alasan2']) array_push($alasan, $_REQUEST['alasan2']);
    if ($_REQUEST['alasan3']) array_push($alasan, $_REQUEST['alasan3']);
    if ($_REQUEST['alasan4']) array_push($alasan, $_REQUEST['alasan4']);
    $gabAlasan            = implode("#", $alasan);
    $gabAlasanCom        = implode(",", $alasan);
    $nmKuasa             = mysqli_real_escape_string($DBLink, $_REQUEST['nmKuasa']);
    $nmWp                 = mysqli_real_escape_string($DBLink, $_REQUEST['nmWp']);
    $tglMasuk             = substr($_REQUEST['tglMasuk'], 6, 4) . '-' . substr($_REQUEST['tglMasuk'], 3, 2) . '-' . substr($_REQUEST['tglMasuk'], 0, 2);
    $almtWP             = mysqli_real_escape_string($DBLink, $_REQUEST['almtWP']);
    $rtWP                 = $_REQUEST['rtWP'];
    $rwWP                 = $_REQUEST['rwWP'];
    $propinsiWP         = $_REQUEST['propinsi'];
    $kabupatenWP         = $_REQUEST['kabupaten'];
    $kecamatanWP        = $_REQUEST['kecamatan'];
    $kelurahanWP         = $_REQUEST['kelurahan'];
    $hpWP                 = $_REQUEST['hpWP'];
    $nop                 = $_REQUEST['nop'];
    $nopHidden            = $_REQUEST['nopHidden'];
    $almtOP             = mysqli_real_escape_string($DBLink, $_REQUEST['almtOP']);
    $rtOP                 = $_REQUEST['rtOP'];
    $rwOP                 = $_REQUEST['rwOP'];
    $kecamatanOP        = $_REQUEST['kecamatanOP'];
    $kelurahanOP         = $_REQUEST['kelurahanOP'];
    $attachment         = $_REQUEST['attachment'];

    //DATA TANAH DAN BUMI
    $luasTanah             = isset($dataTB[0]['CPM_OP_LUAS_TANAH']) ? $dataTB[0]['CPM_OP_LUAS_TANAH'] : '';
    $NJOPTanah                = isset($dataTB[0]['CPM_NJOP_TANAH']) ? $dataTB[0]['CPM_NJOP_TANAH'] : '';
    $luasBangunan          = isset($dataTB[0]['CPM_OP_LUAS_BANGUNAN']) ? $dataTB[0]['CPM_OP_LUAS_BANGUNAN'] : '';
    $NJOPBangunan        = isset($dataTB[0]['CPM_NJOP_BANGUNAN']) ? $dataTB[0]['CPM_NJOP_BANGUNAN'] : '';
    $klsBangunan        = isset($dataTB[0]['CPM_OP_KELAS_BANGUNAN']) ? $dataTB[0]['CPM_OP_KELAS_BANGUNAN'] : '';

    #Jika pada modul persetujuan 
    if (($dis == 0) && ($tab == 30)) {
        $fieldZNT = 'CPM_OB_ZNT_CODE';
        $fieldLuasBumi = 'CPM_OB_LUAS_TANAH_APP';
        $fieldLuasBangunan = 'CPM_OB_LUAS_BANGUNAN_APP';
        $fieldNJOPBangunan = 'CPM_OB_NJOP_BANGUNAN_APP';

        $luas_bumi = 'NULL';
        $luas_bng = 'NULL';
        $nilai_bng = 'NULL';
        $znt = 'NULL';
        $biaya_sppt = (isset($_REQUEST['biaya_sppt']) && $_REQUEST['biaya_sppt']) ? $_REQUEST['biaya_sppt'] : 'NULL';
        $njop = 0;
        if (isset($_REQUEST['ygdirubah'])) {
            for ($i = 0; $i <= count($_REQUEST['ygdirubah']); $i++)
                $$_REQUEST['ygdirubah'][$i] = "'" . $_REQUEST[$_REQUEST['ygdirubah'][$i]] . "'";
        }

        $njop_tanah = (($znt != 'NULL') ? (int) substr($_REQUEST['znt'], 3) : ($NJOPTanah / $luasTanah)) * (($luas_bumi != 'NULL') ? str_replace("'", "", $luas_bumi) : $luasTanah);
        $njop_bng = (($nilai_bng != 'NULL') ? str_replace("'", "", $nilai_bng) : ($NJOPBangunan / $luasBangunan)) * (($luas_bng != 'NULL') ? str_replace("'", "", $luas_bng) : $luasBangunan);

        if ($znt != NULL) {
            $znt = isset($_REQUEST['znt']) ? substr($_REQUEST['znt'], 0, 2) : '';
        }

        $qry1 = "UPDATE cppmod_pbb_service_objection SET CPM_OB_ARGUEMENT='" . $gabAlasan . "',CPM_OB_ZNT_CODE='" . $znt . "',CPM_OB_LUAS_TANAH_APP=$luas_bumi,CPM_OB_LUAS_BANGUNAN_APP=$luas_bng,CPM_OB_NJOP_BANGUNAN_APP='" . $njop_bng . "', CPM_OB_NJOP_TANAH_APP='" . $njop_tanah . "', CPM_OB_BIAYA_SPPT=" . $biaya_sppt . " WHERE CPM_OB_SID='" . $nomorHidden . "'";

        #UPDATE cppmod_pbb_services FOR PERSETUJUAN-->OK
        $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='" . $nmKuasa . "', CPM_STATUS='" . $status . "', CPM_REFUSAL_REASON='" . $alasanTolak . "', CPM_APPROVER='" . $uname . "', CPM_DATE_APPROVER='" . $dateValidate . "' WHERE CPM_ID='" . $nomorHidden . "'";
    } else if ($tab == 33) {
        $qry1 = "UPDATE cppmod_pbb_service_objection SET CPM_OB_ARGUEMENT='" . $gabAlasan . "', CPM_OB_ZNT_CODE='" . $znt . "', CPM_OB_NJOP_TANAH_APP='" . $njop . "' WHERE CPM_OB_SID='" . $nomorHidden . "'";

        #UPDATE cppmod_pbb_services FOR PERSETUJUAN-->OK
        $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='" . $nmKuasa . "', CPM_REFUSAL_REASON='" . $alasanTolak . "' WHERE CPM_ID='" . $nomorHidden . "'";
    } else {
        if ($tab == 40) {
            $fieldZNT = "CPM_OB_ZNT_CODE_RECOMMEND";
            $fieldLuasBumi = 'CPM_OB_LUAS_TANAH_RECOMMEND';
            $fieldLuasBangunan = 'CPM_OB_LUAS_BANGUNAN_RECOMMEND';
            $fieldNJOPBangunan = 'CPM_OB_NJOP_BANGUNAN_RECOMMEND';
            $luas_bumi = NULL;
            $luas_bng = NULL;
            $nilai_bng = NULL;
            $znt = NULL;
            if (isset($_REQUEST['ygdirubah'])) {
                for ($i = 0; $i <= count($_REQUEST['ygdirubah']); $i++)
                    $$_REQUEST['ygdirubah'][$i] = $_REQUEST[$_REQUEST['ygdirubah'][$i]];
            }
            if ($znt != NULL) {
                $znt                = substr($_REQUEST['znt'], 0, 2);
            }
        } else if ($tab == 20) {
            $fieldZNT = 'CPM_OB_ZNT_CODE';
            $fieldLuasBumi = 'CPM_OB_LUAS_TANAH_APP';
            $fieldLuasBangunan = 'CPM_OB_LUAS_BANGUNAN_APP';
            $fieldNJOPBangunan = 'CPM_OB_NJOP_BANGUNAN_APP';
            $luas_bumi = 'NULL';
            $luas_bng = 'NULL';
            $nilai_bng = 'NULL';
            $znt = NULL;
            $biaya_sppt = (isset($_REQUEST['biaya_sppt']) && $_REQUEST['biaya_sppt']) ? $_REQUEST['biaya_sppt'] : 'NULL';
            if (isset($_REQUEST['ygdirubah'])) {
                for ($i = 0; $i <= count($_REQUEST['ygdirubah']); $i++)
                    if (isset($_REQUEST['ygdirubah'][$i]) && isset($_REQUEST[$_REQUEST['ygdirubah'][$i]])) {
                        $$_REQUEST['ygdirubah'][$i] = "'" . $_REQUEST[$_REQUEST['ygdirubah'][$i]] . "'";
                    }
            }
            if ($znt != NULL) {
                $znt    = substr($_REQUEST['znt'], 0, 2) ;
            }
        }
        if (($tab == 10) || ($tab == 12)) {
            if (!isExistSID($nomor)) {
                //$qry1 = "INSERT INTO cppmod_pbb_service_objection (CPM_OB_ID,CPM_OB_SID,CPM_OB_ARGUEMENT,CPM_OB_LUAS_TANAH,CPM_OB_NJOP_TANAH,CPM_OB_LUAS_BANGUNAN,CPM_OB_NJOP_BANGUNAN, CPM_OB_KELAS_BANGUNAN) VALUES ('','{$nomor}','{$gabAlasan}','{$luasTanah}','{$NJOPTanah}','{$luasBangunan}','{$NJOPBangunan}','{$klsBangunan}')";
                $qry1 = "INSERT INTO cppmod_pbb_service_objection (CPM_OB_SID,CPM_OB_ARGUEMENT,CPM_OB_LUAS_TANAH,CPM_OB_NJOP_TANAH,CPM_OB_LUAS_BANGUNAN,CPM_OB_NJOP_BANGUNAN, CPM_OB_KELAS_BANGUNAN) VALUES ('" . $nomor . "','" . $gabAlasan . "','" . $luasTanah . "','" . $NJOPTanah . "','" . $luasBangunan . "','" . $NJOPBangunan . "','" . $klsBangunan . "')";
            } else {
                $qry1 = "UPDATE cppmod_pbb_service_objection SET CPM_OB_ARGUEMENT='" . $gabAlasan . "' WHERE CPM_OB_SID = '" . $nomor . "'";
            }
        } else if (($tab != 10) || ($tab != 12)) {
            if (!isExistSID($nomor)) {
                //$qry1 = "INSERT INTO cppmod_pbb_service_objection (CPM_OB_ID,CPM_OB_SID,CPM_OB_ARGUEMENT,$fieldZNT,$fieldLuasBumi,$fieldLuasBangunan,$fieldNJOPBangunan,CPM_OB_BIAYA_SPPT) VALUES ('','".$nomor."','".$gabAlasan."','".$znt."',$luas_bumi,$luas_bng,$nilai_bng,$biaya_sppt)";
                $qry1 = "INSERT INTO cppmod_pbb_service_objection (CPM_OB_SID,CPM_OB_ARGUEMENT,$fieldZNT,$fieldLuasBumi,$fieldLuasBangunan,$fieldNJOPBangunan,CPM_OB_BIAYA_SPPT) VALUES ('" . $nomor . "','" . $gabAlasan . "','" . $znt . "',$luas_bumi,$luas_bng,$nilai_bng,$biaya_sppt)";
            } else {
                $qry1 = "UPDATE cppmod_pbb_service_objection SET CPM_OB_ARGUEMENT='" . $gabAlasan . "',$fieldZNT='" . $znt . "',$fieldLuasBumi=$luas_bumi,$fieldLuasBangunan=$luas_bng,$fieldNJOPBangunan=$nilai_bng,CPM_OB_BIAYA_SPPT=$biaya_sppt WHERE CPM_OB_SID = '" . $nomor . "'";
            }
        }

        if (($dis == 0) && (($tab == 10) || ($tab == 12))) {
            $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', 
					CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', CPM_WP_KELURAHAN='{$kelurahanWP}', 
					CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', 
					CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', CPM_OP_ADDRESS='{$almtOP}', 
					CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', 
					CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
					CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, 
					CPM_SPPT_DUE={$pbbterutang}, CPM_SPPT_YEAR='{$thnPajak}',CPM_REFUSAL_REASON='{$alasanTolak}',CPM_APPROVAL_REASON='{$alasanSetuju}', 
					CPM_VALIDATOR='{$uname}', CPM_DATE_VALIDATE='{$dateValidate}' WHERE CPM_ID = '{$nomor}' ";
        } else if (($dis == 0) && ($tab == 20)) {
            $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', 
					CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', CPM_WP_KELURAHAN='{$kelurahanWP}', 
					CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', 
					CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', CPM_OP_ADDRESS='{$almtOP}', 
					CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', 
					CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
					CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, 
					CPM_SPPT_DUE={$pbbterutang}, CPM_SPPT_YEAR='{$thnPajak}',CPM_REFUSAL_REASON='{$alasanTolak}',CPM_APPROVAL_REASON='{$alasanSetuju}', 
					CPM_VERIFICATOR='{$uname}', CPM_DATE_VERIFICATION='{$dateValidate}' WHERE CPM_ID = '{$nomor}' ";
        } else if (($dis == 0) && ($tab == 40)) {
            $qry2 = "UPDATE cppmod_pbb_services SET CPM_REPRESENTATIVE='{$nmKuasa}', CPM_WP_NAME='{$nmWp}', CPM_WP_ADDRESS='{$almtWP}', 
					CPM_WP_RT='{$rtWP}', CPM_WP_RW='{$rwWP}', CPM_WP_KELURAHAN='{$kelurahanWP}', 
					CPM_WP_KECAMATAN='{$kecamatanWP}', CPM_WP_KABUPATEN='{$kabupatenWP}', CPM_WP_PROVINCE='{$propinsiWP}', 
					CPM_WP_HANDPHONE='{$hpWP}', CPM_OP_NUMBER='{$nop}', CPM_OP_ADDRESS='{$almtOP}', 
					CPM_OP_RT='{$rtOP}', CPM_OP_RW='{$rwOP}', 
					CPM_OP_KELURAHAN='{$kelurahanOP}', CPM_OP_KECAMATAN='{$kecamatanOP}', CPM_ATTACHMENT={$attachment}, 
					CPM_RECEIVER='{$uname}', CPM_DATE_RECEIVE='{$tglMasuk}', CPM_STATUS={$status}, 
					CPM_SPPT_DUE={$pbbterutang}, CPM_SPPT_YEAR='{$thnPajak}',CPM_REFUSAL_REASON='{$alasanTolak}',CPM_RECOMMEND_REASON='{$alasanSetuju}', 
					CPM_RECOMMEND='{$uname}', CPM_RECOMMEND_DATE='{$dateValidate}' WHERE CPM_ID = '{$nomor}' ";
        }
    }

    $res1 = mysqli_query($DBLink, $qry1);
    $res2 = mysqli_query($DBLink, $qry2);
    if (($res1 === false) || ($res2 === false)) {
        echo $qry1 . "<br>";
        echo $qry2 . "<br>"; //exit;
        echo 'mysqlerror:' . mysqli_error($DBLink);
        // echo 'test';
        // var_dump($res1, $res2, $znt);
    }

    if (($selectedRadio == 'y') && ($tab == 30) && ($appConfig['NOMOR_LHP_OTOMATIS'] == '1') && ($arConfig['usertype'] == 'persetujuan-keberatan')) {
        //Insert to GENERATE_SK_NUMBER
        if (!isHaveSKNumber($nomor)) {
            $SKNumber             = generateSKNumber();
            $Date                 = date('Y-m-d');
            $upReduce = updateReduce($nomor, $SKNumber, $Date);
            if ($upReduce) {
                $tmp      = str_replace($appConfig['NOMOR_SK_FORMAT'], "", $SKNumber);
                $qry   = "INSERT INTO cppmod_pbb_generate_sk_number (CPM_SK_ID, CPM_SK_NO, CPM_DATE_CREATED) VALUES ('$SKNumber', '$tmp','$Date')";
                $res   = mysqli_query($DBLink, $qry);
                if ($res === false) {
                    echo $qry . "<br>";
                    echo mysqli_error($DBLink);
                }
            }
        }


        // jika disetujui maka update nilai ke GW.PBB_SPPT dan current(array)
        // by 35utech 
        $res3 = execute($nop, $thnPajak, $nomor);
    }
    // var_dump($res1);
    // var_dump($res2);
    // var_dump($res3);
    // exit;

    if ($res1 && $res2) {
        if ($status == 1) {
            echo 'Data berhasil disimpan...!';
        } else if ($status == 2) {
            echo 'Data berhasil dikirim ke Verifikasi...!';
            /* } else if($status == 7){
			echo 'Data berhasil dikirim ke Rekomendasi...!'; */
        } else if ($status == 3) {
            echo 'Data berhasil dikirim ke Persetujuan...!';
        } else if ($status == 4 && $res3) {
            echo 'Data berhasil disetujui...!';
            /* $tahun = $appConfig['tahun_tagihan'];
			$ServerAddress = $appConfig['TPB_ADDRESS'];
			$ServerPort = $appConfig['TPB_PORT'];
			$ServerTimeOut = $appConfig['TPB_TIMEOUT'];
			$sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"".$tahun."\",\"KELURAHAN\":\"\",\"TIPE\":\"2\",\"NOP\":\"".$nop."\",\"SUSULAN\":\"1\"}";
			$bOK = GetRemoteResponse($ServerAddress, $ServerPort, $ServerTimeOut, $sRequestStream, $sResp);
			if ($bOK == 0) {
				$sResp = rtrim($sResp, END_OF_MSG); // trim trailing '@'
				echo $sResp;
				//echo $ServerAddress.",".$ServerPort.",".$ServerTimeOut;exit;
			} */
        }
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

$save        = isset($_REQUEST['btn-save']) ? $_REQUEST['btn-save'] : '';
$rekomendasi = isset($_REQUEST['rekomendasi']) ? $_REQUEST['rekomendasi'] : '';

if ($save == 'Simpan') {
    save(1);
} else if ($save == 'Finalkan') {
    save(2);
} else if (($save == 'Kirim') && ($arConfig['usertype'] == 'verifikasi-keberatan')) {
    if ($rekomendasi == "y") {
        save(3);
    } else if ($rekomendasi == "n") {
        save(5);
    }
    /* } else if (($save == 'Submit') && ($arConfig['usertype'] == 'rekomendasi')){
		if ($rekomendasi == "y"){
			save(3);
		}
		else if ($rekomendasi == "n"){
			save(8);
		} */
} else if (($save == 'Kirim') && ($arConfig['usertype'] == 'persetujuan-keberatan')) {
    if ($rekomendasi == "y") {
        save(4);
    } else if ($rekomendasi == "n") {
        save(6);
    }
} else {
    // $urlParam       = @isset($_REQUEST['param']) ? base64_decode($_REQUEST['param']) : "";
    $svcid          = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
    $dis            = @isset($_REQUEST['dis']) ? $_REQUEST['dis'] : 0;
    $tab            = @isset($_REQUEST['tab']) ? $_REQUEST['tab'] : "";

    // parse_str($urlParam, $parsedUrlParam);
    // if(is_array($parsedUrlParam) && !empty($parsedUrlParam)) {
    //     $svcid = isset($parsedUrlParam['svcid']) ? $parsedUrlParam['svcid'] : "";
    //     $dis = isset($parsedUrlParam['dis']) ? $parsedUrlParam['dis'] : 0;
    //     $tab = isset($parsedUrlParam['tab']) ? $parsedUrlParam['tab'] : "";
    // }

    // var_dump($svcid,$dis,$tab);

    $initData       = getInitData($svcid);
    $initDataOb     = getObjection($svcid);
    //print_r ($dataTB);
    // $json = new Services_JSON();
    
    //echo $nopz;
    //echo $dataTB[0]['CPM_OP_LUAS_TANAH'];
    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($initData, $initDataOb);
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
	7 Pending di Rekomendasi
	8 DItolak di Rekomendasi
*/

######## START UPDATED 30 10 2018 BY 35UTECH #######################


function execute($nop2, $tahun2, $spop2)
{

    global $DBLink, $appConfig, $dbUtils;
    $sid                    = $spop2;
    $tahun                  = $tahun2;
    $nop                    = $nop2;
    $vObjection             = array();
    $getObjection           = getObjection2($sid);

    $njopBumi       = $njopBangunan   = 0;
    $luasBumi       = ($getObjection['CPM_OB_LUAS_TANAH_APP'] != NULL) ? $getObjection['CPM_OB_LUAS_TANAH_APP'] : $getObjection['CPM_OB_LUAS_TANAH'];
    $luasBangunan       = ($getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != NULL) ? $getObjection['CPM_OB_LUAS_BANGUNAN_APP'] : $getObjection['CPM_OB_LUAS_BANGUNAN'];
    if ($luasBumi > 0) $njopBumi       = ($getObjection['CPM_OB_NJOP_TANAH_APP'] / $luasBumi) / 1000;
    if ($luasBangunan > 0) $njopBangunan   = ($getObjection['CPM_OB_NJOP_BANGUNAN_APP'] / $luasBangunan) / 1000;

    $dataKlsTanah   = getKlsTanah($njopBumi, $tahun);
    $dataKlsBng     = getKlsBangunan($njopBangunan, $tahun);


    if ($getObjection['CPM_OB_ZNT_CODE'] != NULL)
        $vPBBSPPT['CPM_OT_ZONA_NILAI'] = $getObjection['CPM_OB_ZNT_CODE'];
    if ($getObjection['CPM_OB_LUAS_TANAH_APP'] != NULL)
        $vPBBSPPT['CPM_OP_LUAS_TANAH'] = $getObjection['CPM_OB_LUAS_TANAH_APP'];
    if ($getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != NULL)
        $vPBBSPPT['CPM_OP_LUAS_BANGUNAN'] = $getObjection['CPM_OB_LUAS_BANGUNAN_APP'];

    $vPBBSPPT['CPM_NJOP_BANGUNAN'] = $getObjection['CPM_OB_NJOP_BANGUNAN_APP'];
    $vPBBSPPT['CPM_NJOP_TANAH'] = $getObjection['CPM_OB_NJOP_TANAH_APP'];

    $vPBBSPPT['CPM_OP_KELAS_TANAH']         = $dataKlsTanah->CPM_KELAS;
    $vPBBSPPT['CPM_OP_KELAS_BANGUNAN']      = $dataKlsBng->CPM_KELAS;
    // echo "<pre>";
    // print_r($vPBBSPPT);
    // print_r($getObjection);
    // print_r($dataKlsTanah);
    // print_r($dataKlsBng);
    // echo "</pre>";




    $bOK = updateSPPTFinal($nop, $vPBBSPPT);
    if (!$bOK) {
        echo "Error Update Final";
        exit;
    }
    // return false;


    // var_dump($tahun);
    // var_dump($spop);

    $penetapan = $dbUtils->selectPenetapan($nop, $appConfig, '');
    // var_dump($penetapan);
    // exit;
    if (!$penetapan) return false;

    if ($getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != NULL && $getObjection['CPM_OB_LUAS_BANGUNAN_APP'] != $getObjection['CPM_OB_LUAS_BANGUNAN']) {
        $vExt['CPM_PAYMENT_INDIVIDU'] = $getObjection['CPM_OB_NJOP_BANGUNAN_APP'] / 1000;
        $vExt['CPM_OP_LUAS_BANGUNAN'] = $penetapan['CPM_OP_LUAS_BANGUNAN'];
        $bOK = updateSPPTFinalExt($nop, $vExt);

        if (!$bOK) {
            echo "Error Update Final Ext";
            exit;
            // return false;
        }
    }

    $vPBBSPPT = array();
    $vPBBSPPT['OP_LUAS_BUMI']               = $penetapan['CPM_OP_LUAS_TANAH'];
    $vPBBSPPT['OP_LUAS_BANGUNAN']           = $penetapan['CPM_OP_LUAS_BANGUNAN'];
    $vPBBSPPT['OP_KELAS_BUMI']              = $penetapan['CPM_OP_KELAS_TANAH'];
    $vPBBSPPT['OP_KELAS_BANGUNAN']          = $penetapan['CPM_OP_KELAS_BANGUNAN'];
    $vPBBSPPT['OP_NJOP_BUMI']               = $penetapan['CPM_NJOP_TANAH'];
    $vPBBSPPT['OP_NJOP_BANGUNAN']           = $penetapan['CPM_NJOP_BANGUNAN'];
    $vPBBSPPT['OP_NJOP']                    = $penetapan['OP_NJOP'];
    $vPBBSPPT['OP_NJKP']                    = $penetapan['OP_NJKP'];
    $vPBBSPPT['OP_NJOPTKP']                 = $penetapan['OP_NJOPTKP'];
    $vPBBSPPT['OP_TARIF']                   = $penetapan['OP_TARIF'];
    $vPBBSPPT['SPPT_PBB_HARUS_DIBAYAR']     = $penetapan['SPPT_PBB_HARUS_DIBAYAR'];
    $vPBBSPPT['SPPT_PBB_PENGURANGAN']       = "0";
    $vPBBSPPT['SPPT_PBB_PERSEN_PENGURANGAN'] = "0";

    $bOK = updateGatewayCurrent2($nop, $vPBBSPPT);
    // var_dump($bOK);
    // exit;

    if (!$bOK) {
        echo "Error Update Current";
        exit;
    }
    // return false;

    #############################################
    ############Proses Update PBB_SPPT###########
    unset($vPBBSPPT['OP_TARIF']);
    unset($vPBBSPPT['SPPT_PBB_PENGURANGAN']);
    unset($vPBBSPPT['SPPT_PBB_PERSEN_PENGURANGAN']);
    $bOK = updateGateWayPBBSPPT2($nop, $tahun, $vPBBSPPT);

    // var_dump($bOK);
    // echo "123";
    // exit;
    if (!$bOK) return false;
    // var_dump($appConfig['ADMIN_SW_DBNAME']);
    // exit;
    // mysql_select_db($appConfig_sw['ADMIN_SW_DBNAME']);

    return true;
    #############################################

}
function getKlsTanah($njop, $thnTagihan)
{
    global $data, $DBLink;

    $query = "SELECT * FROM cppmod_pbb_kelas_bumi WHERE CPM_NILAI_BAWAH < $njop AND CPM_NILAI_ATAS >= $njop and CPM_THN_AWAL <= '" . $thnTagihan . "' AND CPM_THN_AKHIR >= '" . $thnTagihan . "' AND CPM_KELAS <> 'XXX'";
    $res = mysqli_query($DBLink, $query);
    $json = new Services_JSON();

    $dataKls =  $json->decode(mysql2json($res, "data"));
    if (isset($dataKls->data[0])) return $dataKls->data[0];
    else return $json->decode("{ 'data': [ { 'CPM_KELAS' : 'XXX', 'CPM_THN_AWAL' : '2011', 'CPM_THN_AKHIR' : '9999', 'CPM_NILAI_BAWAH' : '0', 'CPM_NILAI_ATAS' : '999999', 'CPM_NJOP_M2' : '0' } ] }")->data[0];
}

function getKlsBangunan($njop, $thnTagihan)
{
    global $data, $DBLink;
    $query = "SELECT * FROM cppmod_pbb_kelas_bangunan WHERE CPM_NILAI_BAWAH < $njop AND CPM_NILAI_ATAS >= $njop and CPM_THN_AWAL <= '" . $thnTagihan . "' AND CPM_THN_AKHIR >= '" . $thnTagihan . "' AND CPM_KELAS <> 'XXX'";

    $res = mysqli_query($DBLink, $query);
    $json = new Services_JSON();

    $dataKls =  $json->decode(mysql2json($res, "data"));
    if (isset($dataKls->data[0])) return $dataKls->data[0];
    else return $json->decode("{ 'data': [ { 'CPM_KELAS' : 'XXX', 'CPM_THN_AWAL' : '2011', 'CPM_THN_AKHIR' : '9999', 'CPM_NILAI_BAWAH' : '0', 'CPM_NILAI_ATAS' : '999999', 'CPM_NJOP_M2' : '0' } ] }")->data[0];
}

function updateSPPTFinal($nop, $aValue)
{

    global $DBLink;

    $last_key = end(array_keys($aValue));
    $query = "UPDATE cppmod_pbb_sppt_final SET ";

    foreach ($aValue as $key => $value) {
        $query .= "$key='" . mysqli_real_escape_string($DBLink, $value) . "'";
        if ($key != $last_key) {
            $query .= ", ";
        }
    }

    $query .= " WHERE CPM_NOP='$nop'";

    $bOK = mysqli_query($DBLink, $query);
    if (!$bOK) return false;

    $query =  str_replace("cppmod_pbb_sppt_final", "cppmod_pbb_sppt_susulan", $query);
    // var_dump($query);
    // exit;
    return mysqli_query($DBLink, $query);
}

function updateSPPTFinalExt($nop, $aValue)
{
    global $DBLink;

    $querySelect = "SELECT X.CPM_SPPT_DOC_ID,Y.CPM_OP_NUM FROM cppmod_pbb_sppt_final X, cppmod_pbb_sppt_ext_final Y
        WHERE X.CPM_SPPT_DOC_ID=Y.CPM_SPPT_DOC_ID AND X.CPM_NOP='$nop' AND Y.CPM_OP_NUM <> '' ORDER BY Y.CPM_OP_NUM LIMIT 0,1";
    $res = mysqli_query($DBLink, $querySelect);
    if (!$res) {
        echo mysqli_error($DBLink);
        //echo $qGetObjection;
        return false;
    }
    $data = mysqli_fetch_array($res);
    if (!empty($data)) {
        $queryUpdate = "UPDATE cppmod_pbb_sppt_ext_final SET CPM_OP_LUAS_BANGUNAN='0', CPM_PAYMENT_PENILAIAN_BGN='individu',CPM_PAYMENT_INDIVIDU='0' 
            WHERE CPM_SPPT_DOC_ID='" . $data['CPM_SPPT_DOC_ID'] . "'";

        $bOK = mysqli_query($DBLink, $queryUpdate);
        if (!$bOK) return false;

        $queryUpdate = "UPDATE cppmod_pbb_sppt_ext_final SET CPM_OP_LUAS_BANGUNAN='" . $aValue['CPM_OP_LUAS_BANGUNAN'] . "', CPM_PAYMENT_INDIVIDU='" . $aValue['CPM_PAYMENT_INDIVIDU'] . "'
            WHERE CPM_SPPT_DOC_ID='" . $data['CPM_SPPT_DOC_ID'] . "' AND CPM_OP_NUM='" . $data['CPM_OP_NUM'] . "'";

        return mysqli_query($DBLink, $queryUpdate);
    } else {
        return true;
    }
}

function updateGatewayCurrent2($nop, $aValue)
{

    global $DBLink;

    $last_key = end(array_keys($aValue));
    $query = "UPDATE cppmod_pbb_sppt_current SET ";

    foreach ($aValue as $key => $value) {
        $query .= "$key='$value'";
        if ($key != $last_key) {
            $query .= ", ";
        }
    }

    $query .= " WHERE NOP='$nop'";
    // echo $query;
    // exit;

    return  mysqli_query($DBLink, $query);
}
function updateGateWayPBBSPPT2($nop, $tahun, $aValue)
{
    // global $DBLink, $C_HOST_PORT, $C_USER, $C_PWD, $C_DB;
    global $appConfig, $DBLink;
    $C_HOST_PORT = $appConfig['GW_DBHOST'];
    $C_USER = $appConfig['GW_DBUSER'];
    $C_PWD = $appConfig['GW_DBPWD'];
    $C_DB = $appConfig['GW_DBNAME'];

    $LDBLink = mysqli_connect($C_HOST_PORT, $C_USER, $C_PWD, $C_DB) or die(mysqli_error($DBLink));
    //mysql_select_db($C_DB,$LDBLink);

    $last_key = end(array_keys($aValue));
    $query = "UPDATE PBB_SPPT SET ";

    foreach ($aValue as $key => $value) {
        $query .= "$key='$value'";
        if ($key != $last_key) {
            $query .= ", ";
        }
    }

    $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun' AND (PAYMENT_FLAG IS NULL OR PAYMENT_FLAG != '1') ";
    // echo $query;
    // exit;

    $bOK = mysqli_query($LDBLink, $query);

    mysqli_close($LDBLink);

    return $bOK;
}

function getObjection2($sid)
{
    global $DBLink;
    $qGetObjection = "SELECT CPM_OB_ID, CPM_OB_SID, CPM_OB_ZNT_CODE, CPM_OB_LUAS_TANAH, CPM_OB_NJOP_TANAH, CPM_OB_KELAS_TANAH, CPM_OB_NJOP_TANAH_APP, CPM_OB_LUAS_BANGUNAN, CPM_OB_NJOP_BANGUNAN, CPM_OB_KELAS_BANGUNAN, CPM_OB_LUAS_TANAH_APP, CPM_OB_LUAS_BANGUNAN_APP, CPM_OB_NJOP_BANGUNAN_APP FROM cppmod_pbb_service_objection WHERE CPM_OB_SID = '$sid'";
    // echo $qGetObjection;
    // exit;
    $res = mysqli_query($DBLink, $qGetObjection);
    if (!$res) {
        echo mysqli_error($DBLink);
        echo $qGetObjection;
    }
    return mysqli_fetch_array($res);
}
function mysql2json($mysql_result, $name)
{
    $json = "{\n'$name': [\n";
    $field_names = array();
    $fields = mysqli_num_fields($mysql_result);
    for ($x = 0; $x < $fields; $x++) {
        $field_name = mysqli_fetch_field($mysql_result);
        if ($field_name) {
            $field_names[$x] = $field_name->name;
        }
    }
    $rows = mysqli_num_rows($mysql_result);
    for ($x = 0; $x < $rows; $x++) {
        $row = mysqli_fetch_array($mysql_result);
        $json .= "{\n";
        for ($y = 0; $y < count($field_names); $y++) {
            $json .= "'$field_names[$y]' :    '" . addslashes($row[$y]) . "'";
            if ($y == count($field_names) - 1) {
                $json .= "\n";
            } else {
                $json .= ",\n";
            }
        }
        if ($x == $rows - 1) {
            $json .= "\n}\n";
        } else {
            $json .= "\n},\n";
        }
    }
    $json .= "]\n}";
    return ($json);
}
   
######## END UPDATED 30 10 2018 BY 35UTECH #######################
