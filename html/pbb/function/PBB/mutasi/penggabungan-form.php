<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'mutasi', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbServices.php");
require_once($sRootPath . "inc/PBB/dbSpptPenggabungan.php");
require_once($sRootPath . "inc/PBB/dbWajibPajak.php");

echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js\"></script>";
echo "<script type=\"text/javascript\" src=\"inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js\"></script>";


echo "<script src=\"inc/js/jquery.maskedinput-1.3.min.js\"></script>\n";
echo "<script src=\"inc/js/jquery.validate.min.js\"></script>\n";
echo "<script language=\"javascript\" src=\"inc/payment/base64.js\" type=\"text/javascript\"></script>\n";

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);

$arConfig = $User->GetModuleConfig($m);
$appConfig = $User->GetAppConfig($application);

$dbServices = new DbServices($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbSpptPenggabungan = new DbSpptPenggabungan($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);

$NBParam = base64_encode('{"ServerAddress":"'.$appConfig['TPB_ADDRESS'].'","ServerPort":"'.$appConfig['TPB_PORT'].'","ServerTimeOut":"'.$appConfig['TPB_TIMEOUT'].'"}');
$NOP = '';
function getConfigValue($id, $key) {
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

function getPropinsi() {
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

function getKabkota($idProv = "") {
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

function getKecamatan($idKab = "") {
    global $DBLink;

    $qwhere = "";
    if ($idKab) {
        $qwhere = " WHERE CPC_TKC_KKID='$idKab'";
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

function getKelurahan($idKec = "") {
    global $DBLink;

    $qwhere = "";
    if ($idKec) {
        $qwhere = " WHERE CPC_TKL_KCID='$idKec'";
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

function formPenerimaan($initData) {
    global $DBLink, $a, $m, $arConfig, $appConfig, $nobutton, $readonly;
    	
    $bSlash = "\'";
    $ktip = "'";

    if ($initData['TOTAL'] > 0) {
            $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
            $editMode = true;
    }
	
	// echo "<pre>";
	// print_r($initData);
	// echo "</pre>";
	// exit();

    $html = "
    <style>
    #main-content {
        width: 800px;
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
            ";
		
    $html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });

        })
    </script>
<div class=row>
    <div class=\"col-md-1\"></div>
    <div  class=\"col-md-11\" style=\"max-width:930px;background:#FFF;padding:25px;box-shadow:0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);border:solid 1px #54936f\">
        <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
            $hiddenModeInput
            <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
            <table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
                <tr>
                    <td colspan=\"4\" align=\"center\"><strong><font size=\"+2\">Penerimaan Berkas Penggabungan PBB-P2</font></strong><br /><br /></td>
                </tr>
                <tr>
                    <td colspan=\"4\" width=\"1%\" align=\"center\">
                        <table id=\"list-split\">
                                    <thead>
                                        <tr>
                                            <td colspan=\"5\">
                                                <div style=\"display:flex\">    
                                                <span style=\"margin: 10px 10px 0 0\">Digabung Sebanyak</span> 
                                                <input class=\"form-control\" style=\"width:50px; margin: 0 10px 0 0\" " . (($readonly) ? 'readonly' : null) . " type=\"text\" name=\"addrows\" id=\"addrows\" size=\"6\">
                                                <input class=\"btn btn-primary bg-maka\" " . (($readonly) ? 'disabled' : null) . " type=\"button\" name=\"button\" id=\"button\" value=\"OK\" onClick=\"addRows();\">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><div align=\"center\">NOP</div></th>
                                            <th><div align=\"center\">Nama Wajib Pajak</div></th>
                                            <th><div align=\"center\">Luas Tanah</div></th>
                                            <th><div align=\"center\">Luas Bangunan</div></th>
                                            <th><div align=\"center\">Keterangan</div></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Induk <input type=\"text\" name=\"add-list-split-nop[]\" id=\"0\" class=\"nopmerge\" size=\"25\" value=\"" . (($initData['CPM_OP_NUMBER'] != '') ? $initData['CPM_OP_NUMBER'] : '') . "\" readonly=\"readonly\" /></td>
                                            <td><input type=\"text\" name=\"add-list-split-nama[]\" id=\"add-list-split-nama0\" value=\"" . (($initData['CPM_WP_NAMA'] != '') ? $initData['CPM_WP_NAMA'] : '') . "\" size=\"30\" readonly=\"readonly\" /></td>
                                            <td><input type=\"text\" name=\"add-list-split-lt[]\" id=\"add-list-split-lt0\" value=\"" . (($initData['CPM_OP_LUAS_TANAH'] != '') ? $initData['CPM_OP_LUAS_TANAH'] : '') . "\" size=\"10\" class=\"auto\" readonly=\"readonly\" /></td>
                                            <td><input type=\"text\" name=\"add-list-split-lb[]\" id=\"add-list-split-lb0\" value=\"" . (($initData['CPM_OP_LUAS_BANGUNAN'] != '') ? $initData['CPM_OP_LUAS_BANGUNAN'] : '') . "\" size=\"10\" class=\"auto\"readonly=\"readonly\" /></td>
                                            <td><input type=\"text\" name=\"add-list-split-kt[]\" id=\"add-list-split-kt0\" size=\"15\" " . (($readonly) ? 'readonly' : null) . " /></td>
                                            <td><input type=\"hidden\" name=\"add-list-split-almt[]\" id=\"add-list-split-almt0\" value=\"" . (($initData['CPM_OP_ALAMAT'] != '') ? $initData['CPM_OP_ALAMAT'] : '') . "\" size=\"25\" class=\"auto\"readonly=\"readonly\" /></td>
                                        </tr>";
            if ($editMode) {
                $qry = "SELECT * FROM cppmod_pbb_service_merge WHERE CPM_MG_SID = '" . $initData['CPM_ID'] . "'";
                $res = mysqli_query($DBLink, $qry);
                $count = 0;
                while ($row = mysqli_fetch_assoc($res)) {
                    $count++;
                    $html .= "<tr>
                                                        <td>Anak  <input type=\"text\" name=\"add-list-split-nop[]\" id=\"" . $count . "\" class=\"nopmerge\" size=\"25\" " . (($readonly) ? 'readonly' : null) . " value=\"" . $row['CPM_MG_NOP_ANAK'] . "\" /></td>
                                                        <td><input type=\"text\" name=\"add-list-split-nama[]\" id=\"add-list-split-nama" . $count . "\" size=\"30\" readonly=\"readonly\" value=\"" . $row['CPM_MG_WP_NAME'] . "\" /></td>
                                                        <td><input type=\"text\" name=\"add-list-split-lt[]\" id=\"add-list-split-lt" . $count . "\" size=\"10\" class=\"auto\" readonly=\"readonly\" value=\"" . $row['CPM_MG_LUAS_TANAH'] . "\" /></td>
                                                        <td><input type=\"text\" name=\"add-list-split-lb[]\" id=\"add-list-split-lb" . $count . "\" size=\"10\" class=\"auto\" readonly=\"readonly\" value=\"" . $row['CPM_MG_LUAS_BANGUNAN'] . "\" /></td>
                                                        <td><input type=\"text\" name=\"add-list-split-kt[]\" id=\"add-list-split-kt" . $count . "\" size=\"15\" " . (($readonly) ? 'readonly' : null) . " value=\"" . $row['CPM_MG_KET'] . "\" /></td>
                                                    </tr>";
                }
            } else {
                $html .= "<tr>
                                                    <td>Anak  <input type=\"text\" name=\"add-list-split-nop[]\" id=\"1\" class=\"nopmerge\" size=\"25\" /></td>
                                                    <td><input type=\"text\" name=\"add-list-split-nama[]\" id=\"add-list-split-nama1\" size=\"30\" readonly=\"readonly\" /></td>
                                                    <td><input type=\"text\" name=\"add-list-split-lt[]\" id=\"add-list-split-lt1\" size=\"10\" class=\"auto\" readonly=\"readonly\" /></td>
                                                    <td><input type=\"text\" name=\"add-list-split-lb[]\" id=\"add-list-split-lb1\" size=\"10\" class=\"auto\" readonly=\"readonly\" /></td>
                                                    <td><input type=\"text\" name=\"add-list-split-kt[]\" id=\"add-list-split-kt1\" size=\"15\" /></td>
                                                    <td><input type=\"hidden\" name=\"add-list-split-almt[]\" id=\"add-list-split-almt1\" size=\"25\" /></td>
                                                </tr>";
            }
            $html .= "</tbody>
                                    </table>
                        </td>
                    </tr>";
            $simpan = "
                <tr>
                    <td colspan=\"4\" align=\"center\" valign=\"middle\">
                    <input type=\"hidden\" name=\"nomor\" value=\"".$initData['CPM_ID']."\" />
                    <input type=\"hidden\" name=\"nopno\" value=\"".$initData['CPM_OP_NUMBER']."\" />
                        <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                        &nbsp;
                        <input class=\"btn btn-primary bg-maka\" type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['id_penggabungan']) . "\"' />
                    </td>
                </tr>";
            $end = "<tr>
                    <td colspan=\"4\" align=\"center\" valign=\"middle\">&nbsp;</td>
                </tr>
            </table>
        </form>
    </div>
</div>";
    if (!$nobutton) {
        if ($arConfig['usertype'] != "penyetuju") {
            $html .= $simpan;
        }
        if ($arConfig['usertype'] == "pendata") {
            $html .= $kirim;
        } elseif ($arConfig['usertype'] == ("verifikator" || "penyetuju")) {
            $html .= $rekomendasi;
        }
    }
    $html .= $end;
    return $html;
}

function formPenerimaanDetail($initData, $row) {
    global $a, $m, $arConfig, $appConfig, $nobutton, $readonly, $dbUtils, $DBLink;
	
    $today = date("d-m-Y");
    $hiddenIdInput = $nomor = '';
    $kabkotaWP = $kecWP = $kelWP = null;
    $optionKabWP = $optionKecWP = $optionKelWP = "";

    $bSlash = "\'";
    $ktip = "'";

    
    $optionProvWP = "";

    if ($initData['CPM_ID'] != '') {
        
        $initData['CPM_DATE_RECEIVE'] = substr($initData['CPM_DATE_RECEIVE'], 8, 2) . '-' . substr($initData['CPM_DATE_RECEIVE'], 5, 2) . '-' . substr($initData['CPM_DATE_RECEIVE'], 0, 4);
        $initData['CPM_SPPT_PAYMENT_DATE'] = substr($initData['CPM_SPPT_PAYMENT_DATE'], 8, 2) . '-' . substr($initData['CPM_SPPT_PAYMENT_DATE'], 5, 2) . '-' . substr($initData['CPM_SPPT_PAYMENT_DATE'], 0, 4);
        
        if ($initData['CPM_SID'] != '') {
            $hiddenModeInput = '<input type="hidden" name="mode" value="edit">';
        }
        
    }
	
	$qGetMerge = "SELECT CPM_MG_LUAS_TANAH, CPM_MG_LUAS_BANGUNAN FROM cppmod_pbb_service_merge WHERE CPM_MG_SID = '" . $initData['CPM_ID'] . "'";
    $res = mysqli_query($DBLink, $qGetMerge);
	$data = array();
	while ($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
    } 
	$sumLt = 0;
	$sumLb = 0;
	if($initData['CPM_UPDATE_FLAG']=='1'){
		$sumLt = 0;
	} else {
		for( $i=0; $i<count($data); $i++){
			$sumLt += $data[$i]['CPM_MG_LUAS_TANAH'];
			$sumLb += $data[$i]['CPM_MG_LUAS_BANGUNAN'];
		}
	}

	if(($initData['CPM_OP_LUAS_BANGUNAN']+$sumLb)>0){
		$initData['CPM_OT_JENIS'] = 1;
		$qJmlBng = "SELECT COUNT(*) AS JML_BANGUNAN FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID = '" . $initData['CPM_SPPT_DOC_ID'] . "'";
		$res = mysqli_query($DBLink, $qJmlBng);
		$result = mysqli_fetch_assoc($res);
		$initData['CPM_OP_JML_BANGUNAN'] = $result['JML_BANGUNAN'];
	}
    
    $qry = "SELECT CPM_SPPT_DOC_ID, CPM_OP_NUM FROM cppmod_pbb_service_merge_sppt_ext WHERE CPM_SPPT_DOC_ID = (SELECT CPM_SPPT_DOC_ID FROM cppmod_pbb_service_merge_sppt WHERE CPM_SID='{$initData['CPM_ID']}') ";
    
    $res = mysqli_query($DBLink, $qry);
    
    $HtmlExt = "";
    $op_num = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $param = "a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&jenis=penggabungan&idd=".$initData['CPM_SPPT_DOC_ID']."&v=".$initData['CPM_SPPT_DOC_VERSION']."&num=" . $row['CPM_OP_NUM'];
        $HtmlExt .= "<li>
                <a href='main.php?param=" . base64_encode($param) . "' title=\"Buka Lampiran\">Lampiran Bangunan " . $row['CPM_OP_NUM'] . "</a> &nbsp;&nbsp;&nbsp;";
        if(!$readonly) $HtmlExt .= "<a href='#' onClick=\"deleteLampiran('".$row['CPM_SPPT_DOC_ID']."', '".$row['CPM_OP_NUM']."','{$initData['CPM_ID']}');\"><img border=\"0\" alt=\"Hapus Lampiran\" src=\"image/icon/delete.png\"></a>";
        $HtmlExt .= "</li>";
        $op_num++;
    }
    
    $btnTambahLampiran = "";
    if (($initData['CPM_OP_JML_BANGUNAN'] > 0) && ($op_num < $initData['CPM_OP_JML_BANGUNAN']) && !$readonly) {
        $btnTambahLampiran = "<input class=\"btn btn-primary bg-maka\" type='button' value='Tambah Baru' onclick=\"javascript:window.location='main.php?param=" . base64_encode("a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&jenis=penggabungan&idd=".$initData['CPM_SPPT_DOC_ID']."&v=".$initData['CPM_SPPT_DOC_VERSION']."&num=".($op_num+1)) . "'\">";
    } 
    
    $bZNT = $dbUtils->getZNT(null, array("CPM_KODE_LOKASI" => substr($initData['CPM_NOP'], 0,10)));
    
    $optionZNT = "";
    $optionZNT .= "<option value='' >-</option>";
    foreach ($bZNT as $row)
        $optionZNT .= "<option value='" . $row['CPM_KODE_ZNT'] . "' " . (($initData['CPM_OT_ZONA_NILAI'] == $row['CPM_KODE_ZNT']) ? "selected" : "") . ">" . $row['CPM_KODE_ZNT'] . " - " . number_format($row['CPM_NIR'], 0, ",", ".") . "</option>";
    //if($initData['CPM_OT_ZONA_NILAI'] == '' || $initData['CPM_OT_ZONA_NILAI'] == '0'){
        $selectZNT = "<select name=\"OT_ZONA_NILAI\" id=\"OT_ZONA_NILAI\">
                        ".$optionZNT."
                        </select>";
    /*}else{
        $selectZNT = "<input type=\"hidden\" name=\"OT_ZONA_NILAI\" value=\"".$initData['CPM_OT_ZONA_NILAI']."\"/>
                        <select disabled name=\"OT_ZONA_NILAI_TEMP\" id=\"OT_ZONA_NILAI_TEMP\">
                        ".$optionZNT."
                        </select>";
    }*/
    
    
    $OP_LUAS_TANAH_VIEW = '0';
    if(isset($initData['CPM_OP_LUAS_TANAH'])){
        if(strrchr($initData['CPM_OP_LUAS_TANAH'],'.') != '') {
            $OP_LUAS_TANAH_VIEW = number_format(($initData['CPM_OP_LUAS_TANAH']+$sumLt),2,',','.');
        }else {$OP_LUAS_TANAH_VIEW = number_format(($initData['CPM_OP_LUAS_TANAH']+$sumLt),0,',','.');}
    } 

    $OP_LUAS_BANGUNAN_VIEW = '0';
    if(isset($initData['CPM_OP_LUAS_BANGUNAN'])){
        if(strrchr($initData['CPM_OP_LUAS_BANGUNAN'],'.') != '')  {
            $OP_LUAS_BANGUNAN_VIEW = number_format($initData['CPM_OP_LUAS_BANGUNAN'],2,',','.');
        }else $OP_LUAS_BANGUNAN_VIEW = number_format($initData['CPM_OP_LUAS_BANGUNAN'],0,',','.');
    } 

    $html = "
    <style>
    #main-content {
        width: 996px;
    }
    
    #form-penerimaan input.error {
        border-color: #ff0000;
    }
    #form-penerimaan textarea.error {
        border-color: #ff0000;
    }

    </style>
    <div id=\"modalDialog\"></div>
    <script language=\"javascript\">
		$(\"#modalDialog\").dialog({
            autoOpen: false,
            modal: true,
            width: 900,
            resizable: false,
            draggable: false,
            height: 'auto',
            title: '',
            position: ['middle', 20]
        });
        
		function displayFormWp(id){
			$(\"#modalDialog\").dialog('open');
			$(\"#modalDialog\").load(\"function/PBB/nop/wp/form-edit-dialog.php?id=\"+id+\"&a={$a}&case=from_loket\");
		}
		
        $(document).ready(function(){
            $(':radio[name=\"OT_JENIS\"]').change(function() {
                var jenis_bumi = $(this).filter(':checked').val();
                if(jenis_bumi == '2' || jenis_bumi == '3'){
                    $(':text[name=\"jmlBangunan\"]').val(0);
                    $(':text[name=\"jmlBangunan\"]').attr('disabled','disabled');
                }else{
                    $(':text[name=\"jmlBangunan\"]').removeAttr('disabled');
                }
            });
            
            $( \"input:submit, input:button\").button();
			$(\"#form-penerimaan\").submit(function(e){
				ids = 0;
				$.each($(\".attach:checked\"), function() {
					ids +=  parseInt($(this).val());
				});
				
				$(\"#attachment\").val(ids);
			});
		
//			$('#tglMasuk').datepicker({dateFormat: 'dd-mm-yy'});
//                        $('#tglTerimaSPPT').datepicker({dateFormat: 'dd-mm-yy'});
			
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
            jenisBerkas[9] = new Array(1,3,5,6,8,9,10);";

    if ($initData['CPM_TYPE'] != '')
        $html .="
				var berkas = jenisBerkas[" . $initData['CPM_TYPE'] . "-1];
				$('.berkas').hide();
				for(var i=0; i<berkas.length; i++){
                  $('#berkas'+berkas[i]).show();
                }
			";
	

    $html .="//ADD CUSTOM FOR VALIDATION LETTERS ONLY
            jQuery.validator.addMethod(\"accept\", function(value, element, param) {
              return value.match(new RegExp(\"^\" + param + \"$\"));
            });

            $(\"#form-penerimaan\").validate({
                rules : {
                    nmKuasa : \"required\",
                    nmWp : \"required\",
                    tglMasuk : \"required\",
                    almtWP : \"required\",
                    rtWP : {
                            required : true,
                            number : true
                          },
                    rwWP : {
                            required : true,
                            number : true
                          },
                    hpWP : {
                            required : true,
                            number : true
                          },
                    nop : {
                            required : true,
                            number : true
                          },
                    spptTahun : {
                            required : true,
                            number : true
                          },
                    pajakTerutang : {
                            required : true,
                            number : true
                          },
                    luasBumi : {
                            required : true,
                            number : true
                          },
                    luasBangunan1 : {
                            required : true,
                            number : true
                          },
                    statusMilik : \"required\",      
                    pekerjaan : \"required\",
                    kodepos : \"required\",
                    noktp : \"required\"
                },
                messages : {
                    nmKuasa : \"\",
                    nmWp : \"\",
                    tglMasuk : \"\",
                    almtWP : \"\",
                    rtWP : \"\",
                    rwWP : \"\",
                    hpWP : \"\",
                    nop : \"\",
                    spptTahun : \"\",
                    pajakTerutang : \"\",
                    luasBumi : \"\",
                    luasBangunan1 : \"\",
                    statusMilik : \"pilih\",      
                    pekerjaan : \"pilih\",
                    kodepos : \"\",
                    noktp : \"\"
                }
            });
            
            $('#nmKuasa').focusout(function(){
                $('#nmWp').attr('value',$(this).val());
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
            
            function getWilayah(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/PBB/loket/svc-search-city.php',
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
        
            $('#propinsiPerubahan').change(function(){
                getWilayahMutasi(1,$(this).val());
                $('#kecamatanPerubahan').html(\"<option value=''>--Pilih Kabupaten Dulu--</option>\");
                $('#kelurahanPerubahan').html(\"<option value=''>--Pilih Kecamatan Dulu--</option>\");
            });
            
            $('#kabupatenPerubahan').change(function(){
                getWilayahMutasi(2,$(this).val());
            });
            
            $('#kecamatanPerubahan').change(function(){
                getWilayahMutasi(3,$(this).val());
            });
            
            function getWilayahMutasi(type,val){
                $.ajax({
                   type: 'POST',
                   url: './function/PBB/loket/svc-search-city.php',
                   data: 'type='+type+'&id='+val,
                   success: function(msg){
                        var data = msg.split('|');                        
                        switch(type){
                            case 1 : $('#kabupatenPerubahan').html(data[0]);
                                     $('#kecamatanPerubahan').html(data[1]);
                                     $('#kelurahanPerubahan').html(data[2]);
                                     break;
                            case 2 : $('#kecamatanPerubahan').html(data[0]);
                                     $('#kelurahanPerubahan').html(data[1]);
                                     break;
                            case 3 : $('#kelurahanPerubahan').html(msg);break;
                        }
                   }
                 });
            }
        })
    
        function trim(str) {
            return str.replace(/^\s+|\s+$/g, '');
        }
    
        function cekWP(evt, x) {
            if(trim(x.value) == '') {
                var nop = document.getElementById(\"nop\").value;
                x.value = nop;
            }else{

                var noktp = x.value;
                document.getElementById(\"div-loadwp-wait\").innerHTML = '<img src=\"image/icon/loadinfo.net.gif\"/>';
                var params = \"{'noktp' : '\" + noktp + \"'}\";
                params = Base64.encode(params);
                Ext.Ajax.request({
                    url: 'inc/PBB/svc-noktp.php',
                    params: {req: params},
                    success: function(res) {
                        document.getElementById(\"div-loadwp-wait\").innerHTML = \"\";
                        var json = Ext.decode(res.responseText);
                        $(\"#div-tmbahwp\").html('');
                        $(\"input[name=pekerjaan]\").attr('disabled', true).attr('checked', false);
                        if (json.r == true) {
                            $(\"input[name=pekerjaan][value=\" + json.CPM_WP_PEKERJAAN + \"]\").attr('checked', 'checked');
                            document.getElementById(\"nmWpPerubahan\").value = json.CPM_WP_NAMA;
                            document.getElementById(\"almtWPPerubahan\").value = json.CPM_WP_ALAMAT;
                            document.getElementById(\"rtWPPerubahan\").value = json.CPM_WP_RT;
                            document.getElementById(\"rwWPPerubahan\").value = json.CPM_WP_RW;
                            document.getElementById(\"propinsiPerubahan\").value = json.CPM_WP_PROPINSI;
                            document.getElementById(\"kabupatenPerubahan\").value = json.CPM_WP_KOTAKAB;
                            document.getElementById(\"kecamatanPerubahan\").value = json.CPM_WP_KECAMATAN;
                            document.getElementById(\"kelurahanPerubahan\").value = json.CPM_WP_KELURAHAN;
                            document.getElementById(\"kodepos\").value = json.CPM_WP_KODEPOS;
                            document.getElementById(\"hpWPPerubahan\").value = json.CPM_WP_NO_HP;
                            $(\"#div-tmbahwp\").html(\"<a href=javascript:displayFormWp('\"+noktp+\"')>Edit WP?</a>\");
                            alert('No KTP Ditemukan');
                        } else {
                            alert('NO KTP Tidak Ditemukan');
                            document.getElementById(\"nmWpPerubahan\").value = '';
                            document.getElementById(\"almtWPPerubahan\").value = '';
                            document.getElementById(\"rtWPPerubahan\").value = '';
                            document.getElementById(\"rwWPPerubahan\").value = '';
                            document.getElementById(\"propinsiPerubahan\").value = '';
                            document.getElementById(\"kabupatenPerubahan\").value = '';
                            document.getElementById(\"kecamatanPerubahan\").value = '';
                            document.getElementById(\"kelurahanPerubahan\").value = '';
                            document.getElementById(\"kodepos\").value = '';
                            document.getElementById(\"hpWPPerubahan\").value = '';
                            $(\"#div-tmbahwp\").html(\"<a href=javascript:displayFormWp('\"+noktp+\"')>No KTP tidak ditemukan, Input WP Baru?</a>\");
                        }
                    },
                    failure: function(res) {
                        document.getElementById(\"div-loadwp-wait\").innerHTML = \"\";
                        alert('Pengecekan No KTP Gagal!');
                    }
                });
            }
        }
    </script>
<div id=\"main-content\">
    <form name=\"form-penerimaan\" id=\"form-penerimaan\" method=\"post\" action=\"\">
        $hiddenModeInput
        <input type=\"hidden\" name=\"spptTahunRubah\" size=\"4\" maxlength=\"4\" value=\"" . (($initData['CPM_CH_START_YEAR'] != '') ? $initData['CPM_CH_START_YEAR'] : $appConfig['tahun_tagihan']) . "\"/>
        <input type=\"hidden\" name=\"attachment\" id=\"attachment\" value=\"\"/>
        <table class=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"8\">
            <tr valign=\"top\">
                <td width=\"1%\" align=\"center\">&nbsp;</td>
                <td width=\"49%\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                    <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA OBJEK PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">1.</div></td>
                            <td width=\"9%\"><label for=\"nop\">NOP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nop\" id=\"nop\" size=\"40\" maxlength=\"50\" readonly=\"readonly\" value=\"" . ($initData['CPM_NOP']) . "\" placeholder=\"NOP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">2.</div></td>
                            <td width=\"9%\"><label for=\"almtOP\">Alamat</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"almtOPPerubahan\" id=\"almtOPPerubahan\" size=\"40\" maxlength=\"70\" value=\"" . (str_replace($bSlash,$ktip,$initData['CPM_OP_ALAMAT'])) . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">3.</div></td>
                            <td width=\"9%\"><label for=\"rtOP\">RT/RW</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rtOPPerubahan\" id=\"rtOPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . ($initData['CPM_OP_RT']) . "\" placeholder=\"000\"/>&nbsp;/
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"rwOPPerubahan\" id=\"rwOPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . ($initData['CPM_OP_RW']) . "\" placeholder=\"000\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">4.</div></td>
                            <td width=\"9%\"><label for=\"kotaOP\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kotaOP\" value=\"".$appConfig['NAMA_KOTA']."\" size=\"40\" placeholder=\"Kota\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">5.</div></td>
                            <td width=\"9%\"><label for=\"kotaOP\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kecamatanOP\" value=\"".$initData['CPC_TKC_KECAMATAN']."\" size=\"40\" placeholder=\"Kecamatan\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">6.</div></td>
                            <td width=\"9%\"><label for=\"kotaOP\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"kelurahanOP\" value=\"".$initData['CPC_TKL_KELURAHAN']."\" size=\"40\" placeholder=\"".$appConfig['LABEL_KELURAHAN']."\" readonly=\"readonly\"/>
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA TANAH</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">7.</div></td>
                            <td width=\"9%\">Luas Tanah</td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"luasBumi\" id=\"luasBumi\" size=\"10\" maxlength=\"10\" value=\"" . (($initData['CPM_OP_LUAS_TANAH']=='' || $initData['CPM_OP_LUAS_TANAH']==NULL) ? (0+$sumLt) : ($initData['CPM_OP_LUAS_TANAH']+$sumLt)) . "\" placeholder=\"0\" onkeypress=\"return iniAngkaDenganKoma(event, this);\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">8.</div></td>
                            <td width=\"9%\">Zona Nilai Tanah</td>
                            <td width=\"10%\">
                                ".$selectZNT."
                            </td>
                        </tr>
                        <tr valign=\"top\">
                            <td width=\"1%\"><div align=\"right\">9.</div></td>
                            <td width=\"9%\">Jenis Tanah</td>
                            <td width=\"10%\">
                                <label><input " . (($readonly) ? 'disabled' : null)." type=\"radio\" name=\"OT_JENIS\" VALUE=\"1\" ".(($initData['CPM_OT_JENIS'] == 1) ? "checked" : "") ." checked> Tanah + Bangunan</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null)." type=\"radio\" name=\"OT_JENIS\" VALUE=\"2\" ".(($initData['CPM_OT_JENIS'] == 2) ? "checked" : "") ."> Kavling siap bangun</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null)." type=\"radio\" name=\"OT_JENIS\" VALUE=\"3\" ".(($initData['CPM_OT_JENIS'] == 3) ? "checked" : "") ."> Tanah kosong</input></label><br/>
                                <label><input " . (($readonly) ? 'disabled' : null)." type=\"radio\" name=\"OT_JENIS\" VALUE=\"4\" ".(($initData['CPM_OT_JENIS'] == 4) ? "checked" : "") ."> Fasilitas umum</input></label>
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA BANGUNAN</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">10.</div></td>
                            <td width=\"9%\">Jumlah Bangunan</td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"jmlBangunan\" id=\"jmlBangunan\" size=\"10\" maxlength=\"8\" value=\"" . ($initData['CPM_OP_JML_BANGUNAN']) . "\" placeholder=\"0\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\" valign=\"top\"><div align=\"right\">11.</div></td>
                            <td width=\"9%\" valign=\"top\">Data Bangunan</td>
                            <td width=\"10%\">
                                ".$HtmlExt."<br/>
                                ".$btnTambahLampiran."
                                
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA PENILAIAN</h3></td></tr>
                        <tr><td colspan=\"3\">
                            <table width=\"100%\">
                            <tr>
                                    <th> </th>
                                    <th>Luas</th>
                                    <th>Kelas</th>
                                    <th>NJOP / M2</th>
                                    <th>NJOP</th>
                            </tr>
                            <tr>
				<td>Bumi</td>
                                <td align=\"right\">".$OP_LUAS_TANAH_VIEW."</td>
                                <td align=\"center\">".$initData['CPM_OP_KELAS_TANAH']."</td>
                                <td align=\"right\">".(($initData['CPM_NJOP_TANAH'] != 0 || $initData['CPM_NJOP_TANAH'] != '')?number_format($initData['CPM_NJOP_TANAH']/$initData['CPM_OP_LUAS_TANAH'],0,',','.'):"0")."</td>
                                <td align=\"right\">".number_format($initData['CPM_NJOP_TANAH'],0,',','.')."</td>
                            </tr>
                            <tr>
				<td>Bangunan</td>
				<td align=\"right\">".$OP_LUAS_BANGUNAN_VIEW."</td>
                                <td align=\"center\">".$initData['CPM_OP_KELAS_BANGUNAN']."</td>
                                <td align=\"right\">".(($initData['CPM_NJOP_BANGUNAN'] != 0 || $initData['CPM_NJOP_BANGUNAN'] != '')?number_format($initData['CPM_NJOP_BANGUNAN']/$initData['CPM_OP_LUAS_BANGUNAN'],0,',','.'):"0")."</td>
                                <td align=\"right\">".number_format($initData['CPM_NJOP_BANGUNAN'],0,',','.')."</td>
                            </tr>
                            </table>
                            <input class=\"btn btn-primary bg-maka\" type=\"button\" name=\"hitung\" value=\"Nilai Ulang\" id=\"hitung-njop\"/>
                        </td></tr>
                    </table></td>
                <td width=\"1%\" align=\"center\">&nbsp;</td>
                <td width=\"49%\" valign=\"top\"><table width=\"450\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA WAJIB PAJAK</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">13.</div></td>
                            <td width=\"9%\"><label>Nomor KTP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"noktp\" id=\"noktp\" size=\"40\" maxlength=\"25\" onblur=\"return cekWP(event, this);\" value=\"" . (($initData['CPM_WP_NO_KTP'] != '') ? $initData['CPM_WP_NO_KTP'] : '') . "\" placeholder=\"No KTP\" />
                                <span id=\"div-loadwp-wait\"></span>
                                <span id=\"div-tmbahwp\">".(($initData['CPM_WP_NO_KTP'] != '') ? "<a href=javascript:displayFormWp('{$initData['CPM_WP_NO_KTP']}')>Edit WP?</a>" : '')."</span>
                            </td>
                        </tr>
                        <tr>
                            <td valign=\"top\"><div align=\"right\">14.</div></td>
                            <td>
                                Status<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemilik\" " . (($initData['CPM_WP_STATUS'] == 'Pemilik') ? 'checked' : '') . "> Pemilik</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Penyewa\" " . (($initData['CPM_WP_STATUS'] == 'Penyewa') ? 'checked' : '') . "> Penyewa</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pengelola\" " . (($initData['CPM_WP_STATUS'] == 'Pengelola') ? 'checked' : '') . "> Pengelola</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Pemakai\" " . (($initData['CPM_WP_STATUS'] == 'Pemakai') ? 'checked' : '') . "> Pemakai</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"statusMilik\" id=\"statusMilik\" value=\"Sengketa\" " . (($initData['CPM_WP_STATUS'] == 'Sengketa') ? 'checked' : '') . "> Sengketa</label><br>
                            </td>
                            <td valign=\"top\">
                                Pekerjaan<br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"PNS\" " . (($initData['CPM_WP_PEKERJAAN'] == 'PNS') ? 'checked' : 'disabled') . "> PNS</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"TNI\" " . (($initData['CPM_WP_PEKERJAAN'] == 'TNI') ? 'checked' : 'disabled') . "> TNI</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Pensiunan\" " . (($initData['CPM_WP_PEKERJAAN'] == 'Pensiunan') ? 'checked' : 'disabled') . "> Pensiunan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Badan\" " . (($initData['CPM_WP_PEKERJAAN'] == 'Badan') ? 'checked' : 'disabled') . "> Badan</label><br>
                                <label><input " . (($readonly) ? 'disabled' : null) . " type=\"radio\" name=\"pekerjaan\" id=\"pekerjaan\" value=\"Lainnya\" " . (($initData['CPM_WP_PEKERJAAN'] == 'Lainnya') ? 'checked' : 'disabled') . "> Lainnya</label><br>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">15.</div></td>
                            <td width=\"9%\"><label for=\"nmWp\">Nama Wajib Pajak</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"nmWpPerubahan\" id=\"nmWpPerubahan\" size=\"40\" maxlength=\"50\" value=\"" . (str_replace($bSlash,$ktip,$initData['CPM_WP_NAMA'])) . "\" placeholder=\"Nama Wajib Pajak\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">16.</div></td>
                            <td width=\"9%\">No. HP WP</td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"hpWPPerubahan\" id=\"hpWPPerubahan\" size=\"15\" maxlength=\"15\" value=\"" . ($initData['CPM_WP_NO_HP']) . "\" placeholder=\"Nomor HP\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">17.</div></td>
                            <td width=\"9%\"><label for=\"almtWP\">Alamat WP</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"almtWPPerubahan\" id=\"almtWPPerubahan\" size=\"40\" maxlength=\"70\" value=\"" . (str_replace($bSlash,$ktip,$initData['CPM_WP_ALAMAT'])) . "\" placeholder=\"Alamat\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">18.</div></td>
                            <td width=\"9%\"><label for=\"rtWP\">RT/RW</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rtWPPerubahan\" id=\"rtWPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . ($initData['CPM_WP_RT']) . "\" placeholder=\"00\"/>&nbsp;/
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"rwWPPerubahan\" id=\"rwWPPerubahan\" size=\"3\" maxlength=\"3\" value=\"" . ($initData['CPM_WP_RW']) . "\" placeholder=\"00\"/>
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">19.</div></td>
                            <td width=\"9%\"><label for=\"propinsiPerubahan\">Provinsi</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"propinsiPerubahan\" id=\"propinsiPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_PROPINSI'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_PROPINSI']) : '') . "\" placeholder=\"Provinsi\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">20.</div></td>
                            <td width=\"9%\"><label for=\"kabupatenPerubahan\">Kabupaten/Kota</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kabupatenPerubahan\" id=\"kabupatenPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KOTAKAB'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KOTAKAB']) : '') . "\" placeholder=\"Kabupaten\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">21.</div></td>
                            <td width=\"9%\"><label for=\"kecamatanPerubahan\">Kecamatan</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kecamatanPerubahan\" id=\"kecamatanPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KECAMATAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KECAMATAN']) : '') . "\" placeholder=\"Kecamatan\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">22.</div></td>
                            <td width=\"9%\"><label for=\"kelurahan\">".$appConfig['LABEL_KELURAHAN']."</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . " type=\"text\" name=\"kelurahanPerubahan\" id=\"kelurahanPerubahan\" size=\"40\" maxlength=\"25\" value=\"" . (($initData['CPM_WP_KELURAHAN'] != '') ? str_replace($bSlash,$ktip,$initData['CPM_WP_KELURAHAN']) : '') . "\" placeholder=\"".$appConfig['LABEL_KELURAHAN']."\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">23.</div></td>
                            <td width=\"9%\"><label>Kode Pos</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : 'readonly') . "  type=\"text\" name=\"kodepos\" id=\"kodepos\" size=\"10\" maxlength=\"10\" value=\"" . (($initData['CPM_WP_KODEPOS'] != '') ? $initData['CPM_WP_KODEPOS'] : '') . "\" placeholder=\"Kodepos\" />
                            </td>
                        </tr>
                        <tr><td colspan=\"3\"><h3 style=\"width:450px;\">DATA LOKET PELAYANAN</h3></td></tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">24.</div></td>
                            <td width=\"9%\">Nomor</td>
                            <td width=\"10%\">
                                <input type=\"hidden\" name=\"nopno\" value=\"" . ($initData['CPM_NOP']) . "\">
                                <input type=\"hidden\" name=\"sppt_doc_id\" value=\"" . ($initData['CPM_SPPT_DOC_ID']) . "\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nomor\" id=\"nomor\" size=\"40\" maxlength=\"50\" value=\"" . ($initData['CPM_ID']) . "\" readonly=\"readonly\" placeholder=\"Nomor\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">25.</div></td>
                            <td width=\"9%\"><label for=\"nmKuasa\">Nama Kuasa</label></td>
                            <td width=\"10%\">
                                <input " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"nmKuasa\" id=\"nmKuasa\" size=\"40\" maxlength=\"50\" value=\"" . (str_replace($bSlash,$ktip,$initData['CPM_REPRESENTATIVE'])) . "\" placeholder=\"Nama Kuasa\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">26.</div></td>
                            <td width=\"9%\"><label for=\"tglMasuk\">Tanggal Surat Masuk</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglMasuk\" id=\"tglMasuk\" readonly=\"readonly\" value=\"" . ($initData['CPM_DATE_RECEIVE']) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">27.</div></td>
                            <td width=\"9%\"><label for=\"spptTahun\">SPPT Tahun</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"spptTahun\" id=\"spptTahun\" size=\"4\" maxlength=\"4\" value=\"" . ($initData['CPM_SPPT_YEAR']) . "\" placeholder=\"0000\"/>                    
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">28.</div></td>
                            <td width=\"9%\">Jumlah Pajak Terutang</td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"pajakTerutang\" id=\"pajakTerutang\" size=\"40\" maxlength=\"50\" value=\"" . ($initData['CPM_SPPT_DUE']) . "\" placeholder=\"0\" />
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\"><div align=\"right\">29.</div></td>
                            <td width=\"9%\"><label for=\"tglTerimaSPPT\">Tanggal Bayar SPPT</label></td>
                            <td width=\"10%\">
                                <input readonly=\"readonly\" " . (($readonly) ? 'disabled' : null) . " type=\"text\" name=\"tglTerimaSPPT\" id=\"tglTerimaSPPT\" readonly=\"readonly\" value=\"" . ($initData['CPM_SPPT_PAYMENT_DATE']) . "\" size=\"10\" maxlength=\"10\" placeholder=\"Tgl Masuk\"/>                                      
                            </td>
                        </tr>
                        <tr>
                            <td width=\"1%\" valign=\"top\"><div align=\"right\">&nbsp;</div></td>                                          
                            <td width=\"10%\" valign=\"top\" colspan=\"2\">
                                <p style=\"margin-bottom : 8px\">Kelengkapan Dokumen</p>
                                <ol id=\"lampiran\" style=\"margin-left: -20px;\">
                                    <li id=\"berkas1\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"1\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 1) ? "checked=\"checked\"" : "") . "> Surat Permohonan</li>
                                    <li id=\"berkas3\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"4\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 4) ? "checked=\"checked\"" : "") . "> Fotocopi Identitas : KTP / SIM / Paspor yang masih berlaku / Dokumen lain yang dipersamakan.</li>
                                    <li id=\"berkas5\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"16\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 16) ? "checked=\"checked\"" : "") . "> Foto Copy Bukti Kepemilikan Tanah</li>
                                    <li id=\"berkas6\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"32\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 32) ? "checked=\"checked\"" : "") . "> Foto Copy IMB</li>
                                    <li id=\"berkas8\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"128\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 128) ? "checked=\"checked\"" : "") . "> Surat Pemberitahuan Pajak Terutang (SPPT).</li>
                                    <li id=\"berkas9\" class=\"berkas\" ><input " . (($readonly) ? 'disabled' : null) . " type=\"checkbox\" name=\"lampiran[]\" value=\"256\" class=\"attach\" " . (($initData['CPM_ATTACHMENT'] & 256) ? "checked=\"checked\"" : "") . "> Surat Ketetapan Pajak Daerah (SKPD).</li>
                                </ol>
                            </td>
                        </tr>
                    </table></td>
            </tr>
            <tr>
                <td colspan=\"4\">&nbsp;</td>
            </tr>";
    $simpan = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
                <input type=\"hidden\" name=\"nomor\" value=\"".$initData['CPM_ID']."\" />
                    <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan Sementara\" />
                    <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Simpan\" />
                    &nbsp;
                    <input class=\"btn btn-primary bg-maka\" type=\"button\" name = \"btn-cancel\" id = \"btn-cancel\" value=\"Batal\" onclick='window.location.href=\"main.php?param=" . base64_encode("a=" . $a . "&m=" . $m . "&f=" . $arConfig['id_perubahan']) . "\"' />
                </td>
            </tr>";
    $kirim = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
                    <input class=\"btn btn-primary bg-maka\" type=\"submit\" name=\"btn-save\" id=\"btn-save\" value=\"Kirim ke Verifikasi\" />
                </td>
            </tr>";
    $rekomendasi = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">
                    <form method=\"post\">
                        <table border=0 cellpadding=5>
                            <tr><td colspan=2 class=\"tbl-rekomen\"><b>Masukkan rekomendasi anda</b></td></tr>
                            <tr><td class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" value=\"y\"> Setuju</label></td><td class=\"tbl-rekomen\">&nbsp;</td></tr>
                            <tr><td valign=\"top\"class=\"tbl-rekomen\"><label><input type=\"radio\" name=\"rekomendasi\" value=\"n\"> Tolak</label></td>
                                <td class=\"tbl-rekomen\">Alasan<br><textarea name=\"alasan\" cols=70 rows=7></textarea></td></tr>
                            <tr><td colspan=2 align=\"right\" class=\"tbl-rekomen\"><input type=\"submit\" name=\"btn-save\" value=\"" . (($arConfig['usertype'] == 'verifikator') ? 'Submit Verifikasi' : 'Submit Persetujuan') . "\"></td></tr>
                        </table>
                    </form>
                </td>
            </tr>";
    $end = "
            <tr>
                <td colspan=\"4\" align=\"center\" valign=\"middle\">&nbsp;</td>
            </tr>
        </table>
    </form>
</div>";
    if (!$nobutton) {
        if ($arConfig['usertype'] != "penyetuju") {
            $html .= $simpan;
        }
        if ($arConfig['usertype'] == "pendata") {
            $html .= $kirim;
        } elseif ($arConfig['usertype'] == ("verifikator" || "penyetuju")) {
            $html .= $rekomendasi;
        }
    }
    $html .= $end;
    return $html;
}

function save($status) {
    global $data, $DBLink, $uname, $arConfig, $appConfig, $dbServices, $readonly, $dbSpptPenggabungan, $dbWajibPajak;

    $today = date("Y-m-d");
    $mode = @isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";
    
    // print_r($_REQUEST);
    // exit;
    if (isset($_REQUEST['add-list-split-nop'])) {
        $uuid = c_uuid();
        $nomor = $_REQUEST['nomor'];
        $nopno = $_REQUEST['nopno'];
        
        $dbServices->delPenggabungan($nomor);
        $splitNop = $_REQUEST['add-list-split-nop'];
        $splitNm = $_REQUEST['add-list-split-nama'];
        $splitLt = $_REQUEST['add-list-split-lt'];
        $splitLb = $_REQUEST['add-list-split-lb'];
        $splitKt = $_REQUEST['add-list-split-kt'];
        for ($i = 1; $i < count($splitNop); $i++) {
            if ($splitNop[$i] != '') {
                $aVal['CPM_MG_NOP_INDUK'] = $nopno;
                $aVal['CPM_MG_NOP_ANAK'] = $splitNop[$i];
                $aVal['CPM_MG_WP_NAME'] = $splitNm[$i];
                $aVal['CPM_MG_LUAS_TANAH'] = $splitLt[$i];
                $aVal['CPM_MG_LUAS_BANGUNAN'] = $splitLb[$i];
                $aVal['CPM_MG_KET'] = $splitKt[$i];
                $res = $dbServices->addPenggabungan(c_uuid(), $nomor, $aVal);
            }
        }
        echo 'Data berhasil disimpan...!';

        $params = "a=".$_REQUEST['a']."&m=".$_REQUEST['m']."&f=".$arConfig['id_penggabungan_form']."&svcid=".$nomor;
        
        echo "<script language='javascript'>
                $(document).ready(function(){
                    window.location = \"./main.php?param=" . base64_encode($params) . "\"
                })
            </script>";
    }else{
    
        $nomor = $_REQUEST['nomor'];
        $readonly = (isset($_REQUEST['$readonly']) && $_REQUEST['$readonly']='true')? true:false;

        if (!$readonly) {
            $aVal['CPM_OP_ALAMAT'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['almtOPPerubahan']));
            $aVal['CPM_OP_RT'] = strtoupper($_REQUEST['rtOPPerubahan']);
            $aVal['CPM_OP_RW'] = strtoupper($_REQUEST['rwOPPerubahan']);
            $aVal['CPM_WP_NAMA'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['nmWpPerubahan']));
            $aVal['CPM_WP_ALAMAT'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['almtWPPerubahan']));
            $aVal['CPM_WP_RT'] = strtoupper($_REQUEST['rtWPPerubahan']);
            $aVal['CPM_WP_RW'] = strtoupper($_REQUEST['rwWPPerubahan']);
            $aVal['CPM_WP_PROPINSI'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['propinsiPerubahan']));
            $aVal['CPM_WP_KOTAKAB'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kabupatenPerubahan']));
            $aVal['CPM_WP_KELURAHAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kelurahanPerubahan']));
            $aVal['CPM_WP_KECAMATAN'] = mysqli_real_escape_string($DBLink, strtoupper($_REQUEST['kecamatanPerubahan']));
            $aVal['CPM_WP_NO_HP'] = strtoupper($_REQUEST['hpWPPerubahan']);
            $aVal['CPM_OP_LUAS_TANAH'] = $_REQUEST['luasBumi'];
            $aVal['CPM_OT_ZONA_NILAI'] = $_REQUEST['OT_ZONA_NILAI'];
            $aVal['CPM_OT_JENIS'] = $_REQUEST['OT_JENIS'];
            $aVal['CPM_WP_STATUS'] = $_REQUEST['statusMilik'];
            $aVal['CPM_WP_PEKERJAAN'] = $_REQUEST['pekerjaan'];
            $aVal['CPM_WP_KODEPOS'] = $_REQUEST['kodepos'];
            $aVal['CPM_WP_NO_KTP'] = strtoupper($_REQUEST['noktp']);
            $aVal['CPM_OP_JML_BANGUNAN'] = $_REQUEST['jmlBangunan'];

        }

   

        $bVal['CPM_STATUS'] = $status;
        if ($_REQUEST['btn-save'] == 'Kirim ke Verifikasi') {
            $bVal['CPM_VALIDATOR'] = $uname;
            $bVal['CPM_DATE_VALIDATE'] = $today;
        }
        if ($_REQUEST['btn-save'] == 'Submit Verifikasi') {
            $bVal['CPM_VERIFICATOR'] = $uname;
            $bVal['CPM_DATE_VERIFICATION'] = $today;
        }
        if ($_REQUEST['btn-save'] == 'Submit Persetujuan') {
            $bVal['CPM_APPROVER'] = $uname;
            $bVal['CPM_DATE_APPROVER'] = $today;
        }
        ($_REQUEST['alasan'] != '') ? $bVal['CPM_REFUSAL_REASON'] = $_REQUEST['alasan'] : null;
        
        if (!$readonly) {
			$res = $dbSpptPenggabungan->edit($nomor, $aVal);
			$sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"".$appConfig['tahun_tagihan']."\",\"KELURAHAN\":\"\",\"TIPE\":\"4\",\"NOP\":\"".$_REQUEST['nopno']."\",\"SUSULAN\":\"0\"}";
			$bOK = GetRemoteResponse($appConfig['TPB_ADDRESS'], $appConfig['TPB_PORT'], $appConfig['TPB_TIMEOUT'], $sRequestStream, $sResp);

            // die(var_dump($sResp, $res));
        } else {
            $res = true;
        }
        
        $res3 = $res2 = true;
        if($res){
            if ($status == 4) {
                $res3 = $dbSpptPenggabungan->deleteDataAnak($_REQUEST['svcid']);
				// var_dump($res3); exit;
                if($res3){

					if($dt = $dbSpptPenggabungan->get($_REQUEST['sppt_doc_id'])){
						$dt = $dt[0];
						$contentWP['CPM_WP_STATUS'] = $dt['CPM_WP_STATUS'];
						$contentWP['CPM_WP_PEKERJAAN'] = $dt['CPM_WP_PEKERJAAN'];
						$contentWP['CPM_WP_NAMA'] = strtoupper($dt['CPM_WP_NAMA']);
						$contentWP['CPM_WP_ALAMAT'] = strtoupper($dt['CPM_WP_ALAMAT']);
						$contentWP['CPM_WP_KELURAHAN'] = strtoupper($dt['CPM_WP_KELURAHAN']);
						$contentWP['CPM_WP_RT'] = strtoupper($dt['CPM_WP_RT']);
						$contentWP['CPM_WP_RW'] = strtoupper($dt['CPM_WP_RW']);
						$contentWP['CPM_WP_PROPINSI'] = strtoupper($dt['CPM_WP_PROPINSI']);
						$contentWP['CPM_WP_KOTAKAB'] = strtoupper($dt['CPM_WP_KOTAKAB']);
						$contentWP['CPM_WP_KECAMATAN'] = strtoupper($dt['CPM_WP_KECAMATAN']);
						$contentWP['CPM_WP_KODEPOS'] = strtoupper($dt['CPM_WP_KODEPOS']);
						$contentWP['CPM_WP_NO_HP'] = strtoupper($dt['CPM_WP_NO_HP']);
						$dbWajibPajak->save($dt['CPM_WP_NO_KTP'],$contentWP);
					}
					
                    $res3 = $dbSpptPenggabungan->updateToFinal($_REQUEST['sppt_doc_id']);
                    if($res3){
                        $res3 = $dbSpptPenggabungan->updateToCurrent($_REQUEST['svcid'], $appConfig);
                        if($res3){
                            $res3 = $dbSpptPenggabungan->deleteDataPenggabungan($_REQUEST['sppt_doc_id']);
                        }
                    }
                }
            }
           		   
            if($res && $res3){				
                $res2 = $dbServices->editServices($_REQUEST['svcid'], $bVal);
				$cVal['CPM_UPDATE_FLAG'] = '1';
				$res4 = $dbSpptPenggabungan->edit($nomor, $cVal);

                if ($res2) {
                    echo 'Data berhasil disimpan...!';
                    
                    if ($_REQUEST['btn-save'] == 'Simpan Sementara')
                        $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_penggabungan_form'] . "&svcid=" . $nomor;
                    else $params = "a=" . $_REQUEST['a'] . "&m=" . $_REQUEST['m'] . "&f=" . $arConfig['id_penggabungan'];

                    echo "<script language='javascript'>
                            $(document).ready(function(){
                                window.location = \"./main.php?param=" . base64_encode($params) . "\"
                            })
                        </script>";
                } else {
                    echo "Terjadi kegagalan, pada saat mengubah pelayanan, silahkan coba beberapa saat. <br/>";
                    echo mysqli_error($DBLink);
                }
            } else {
                echo "Terjadi kegagalan,  pada saat mengubah mengubah data / menghapus data NOP, silahkan coba beberapa saat. <br/>";
                echo mysqli_error($DBLink);
            }
        }else{
            echo "Terjadi kegagalan.<br/>";
            echo mysqli_error($DBLink);
        }
    }

    
}

$save = isset($_REQUEST['btn-save'])?$_REQUEST['btn-save']:'';

if ($save == 'Simpan Sementara') {
    if ($arConfig['usertype'] == "pendata") {
        save(1);
    } elseif ($arConfig['usertype'] == "verifikator") {
        save(2);
    } elseif ($arConfig['usertype'] == "penyetuju") {
        save(3);
    }
} elseif ($save == 'Simpan') {
    if ($arConfig['usertype'] == "pendata") {
        save(1);
    } elseif ($arConfig['usertype'] == "verifikator") {
        save(2);
    } elseif ($arConfig['usertype'] == "penyetuju") {
        save(3);
    }
} elseif ($save == 'Kirim ke Verifikasi') {
    save(2);
} elseif ($save == 'Submit Verifikasi') {
    if ($_REQUEST['rekomendasi'] == "y") {
        save(3);
    } elseif ($_REQUEST['rekomendasi'] == "n") {
        save(5);
    }
} elseif ($save == 'Submit Persetujuan') {
    if ($_REQUEST['rekomendasi'] == "y") {
        save(4);
    } elseif ($_REQUEST['rekomendasi'] == "n") {
        save(6);
    }
} else {
    $svcid = @isset($_REQUEST['svcid']) ? $_REQUEST['svcid'] : "";
    $nobutton = @isset($_REQUEST['nobutton']) ? $_REQUEST['nobutton'] : false;
    $readonly = @isset($_REQUEST['readonly']) ? $_REQUEST['readonly'] : false;
    
    $initData = $dbSpptPenggabungan->getInitDataNOP($svcid);


    echo "<script language=\"javascript\">var axx = '" . base64_encode($_REQUEST['a']) . "'</script> ";
    echo formPenerimaan($initData);
    
    // die(var_dump('masuk', $initData['TOTAL']));

    if($initData['TOTAL'] > 0){
        $initData = $dbSpptPenggabungan->getInitData($svcid);   
        $NOP = $initData['CPM_NOP'];
        echo formPenerimaanDetail($initData);
    }
}
?>


<script type="text/javascript">

var GW_DBHOST = '<?php echo $appConfig['GW_DBHOST'];?>';
var GW_DBPORT = '<?php echo isset($appConfig['GW_DBPORT'])? $appConfig['GW_DBPORT']:'3306';?>';
var GW_DBNAME = '<?php echo $appConfig['GW_DBNAME'];?>';
var GW_DBUSER = '<?php echo $appConfig['GW_DBUSER'];?>';
var GW_DBPWD  = '<?php echo $appConfig['GW_DBPWD'];?>';

$("#hitung-njop").click(function(){
    $("#load-mask").css("display","block");
    $("#load-content").fadeIn();
        
    loadNB('<?php echo $NBParam?>');
});

function loadNBSuccess(params){
        $("#load-content").css("display","none");
        $("#load-mask").css("display","none");
	
	if(params.responseText){
		var objResult=Ext.decode(params.responseText);

		if (objResult.RC == "0000") {
			alert('Penilaian sukses.');
                        document.location.reload(true);
		} else {
			alert('Gagal melakukan penilaian. Terjadi kesalahan server');
		}
	} else {
		alert('Gagal melakukan penilaian. Terjadi kesalahan server');
	}
}

function loadNBFailure(params){
	$("#load-content").css("display","none");
        $("#load-mask").css("display","none");
        alert('Gagal melakukan penilaian. Terjadi kesalahan server');
}

function loadNB(svr_param) {

        var params = "{\"SVR_PRM\":\""+svr_param+"\",\"NOP\":\"<?php echo $NOP;?>\", \"TAHUN\":\"<?php echo $appConfig['tahun_tagihan'];?>\", \"TIPE\":\"4\", \"SUSULAN\":\"0\"}";
        params = Base64.encode(params);
        Ext.Ajax.request({
                url : 'inc/PBB/svc-penilaian.php',
                success: loadNBSuccess,
                failure: loadNBFailure,			
                params :{req:params}
        });   

}

function deleteLampiranSuccess(params){
        $("#load-content").css("display","none");
        $("#load-mask").css("display","none");
	
	if(params.responseText){
		if (params.responseText == "sukses") {
			alert('Menghapus lampiran sukses.');
                        document.location.reload(true);
		} else {
			alert('Gagal menghapus lampiran. Terjadi kesalahan server');
		}
	} else {
		alert('Gagal menghapus lampiran. Terjadi kesalahan server');
	}
}

function deleteLampiranFailure(params){
	$("#load-content").css("display","none");
        $("#load-mask").css("display","none");
        alert('Gagal menghapus lampiran. Terjadi kesalahan server');
}
function deleteLampiran(doc_id, op_num, cpm_id) {
        if(confirm('Anda yakin data lampiran akan dihapus?')){
            var params = "{\"TYPE\":\"PENGGABUNGAN\", \"DOC_ID\":\""+doc_id+"\", \"OP_NUM\":\""+op_num+"\", \"CPM_ID\":\""+cpm_id+"\"}";
            params = Base64.encode(params);
            Ext.Ajax.request({
                    url : 'function/PBB/mutasi/svc-deletelampiran.php',
                    success: deleteLampiranSuccess,
                    failure: deleteLampiranFailure,			
                    params :{req:params}
            }); 
        }
}

function iniAngkaDenganKoma(evt, x) {
    var charCode = (evt.which) ? evt.which : event.keyCode;

    if (charCode >= 46 || (charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
        return true;
    } else {
        alert("Input hanya boleh angka dan titik!");
        return false;
    }
}


getLuasBB($.trim($('#nop').val()), 0);
function getLuasBB(nop, place) {
	var thn  = '<?php echo $appConfig['tahun_tagihan'];?>';
	var almt = $('#add-list-split-almt0').val();
	var nopInduk = $('#0').val();
    $.ajax({
        type: 'POST',
        data: 'nop='+nop+'&nopInduk='+nopInduk+'&thn='+thn+'&almt='+almt+'&GW_DBHOST='+GW_DBHOST+'&GW_DBNAME='+GW_DBNAME+'&GW_DBUSER='+GW_DBUSER+'&GW_DBPWD='+GW_DBPWD+'&GW_DBPORT='+GW_DBPORT,
        url: './function/PBB/mutasi/dataBB.php',
        success: function(res) {
			console.log(res)
            var d = jQuery.parseJSON(res);
            if (d.r == true) {
					if (nop) {
						$('#add-list-split-nama' + place).val(d.dataBumi.namaWP);
						$('#add-list-split-lt' + place).val(d.dataBumi.luas);
						$('#add-list-split-lb' + place).val(d.dataBumi.luasBangun);
						$('#add-list-split-almt' + place).val(d.dataBumi.alamat);
					}
            } else {
                alert(d.errstr);
                $('#add-list-split-nama' + place).val('');
                $('#add-list-split-lt' + place).val('');
                $('#add-list-split-lb' + place).val('');
				$('#add-list-split-almt' + place).val('');
				
            }
            ;
        }
    });
}

var m = 0;
jQuery(function($) {
    //$('input.auto').autoNumeric();
});

// aldes, benerin fitur add row
function addRows() {

    if(parseInt($("#addrows").val()) < 2) {
        return;
    }

    var m = $('#list-split tbody tr').length;
    console.log(m, $("#addrows").val()); 
    if (m < parseInt($("#addrows").val())) {
        $("#list-split tbody tr:gt(0)").remove();
    }else {
        $("#list-split tbody tr:gt("+ (parseInt($("#addrows").val()) - 1) +")").remove();
    }
    var jtr = parseInt($('#list-split tbody tr').length);
    c = ($("#addrows").val());
    if (jtr < c) {
        m = c - jtr;
        for (var i = 0; i < m; i++) {
            $("table#list-split tbody tr:last").after('<tr>\n\
<td>Anak <input type="text" value="" name="add-list-split-nop[]" id="' + (i + jtr) + '" class="nopmerge" size="25"></td>\n\
<td><input type="text" value="" name="add-list-split-nama[]" id="add-list-split-nama' + (i + jtr) + '" size="30" readonly="readonly"></td>\n\
<td><input type="text" value="" name="add-list-split-lt[]" id="add-list-split-lt' + (i + jtr) + '" size="10"  class="auto" readonly="readonly"></td>\n\
<td><input type="text" value="" name="add-list-split-lb[]" id="add-list-split-lb' + (i + jtr) + '" size="10" class="auto" readonly="readonly"></td>\n\
<td><input type="text" value="" name="add-list-split-kt[]" id="add-list-split-kt' + (i + jtr) + '" size="15"></td>\n\
<td><input type="hidden" value="" name="add-list-split-almt[]" id="add-list-split-almt' + (i + jtr) + '" size="25"></td></tr>\n');
        }
    }
$('.nopmerge').focusout(function() {
        var val = $(this).val();
        var id = $(this).attr('id');
        getLuasBB(val, id);
    });
}
$('.nopmerge').focusout(function() {
        var val = $(this).val();
        var id = $(this).attr('id');
        getLuasBB(val, id);
    });
</script>
<style type="text/css">
    
    #btnClose{cursor: pointer;}
    .linkto:hover, .linkstpd:hover, .linkdate:hover{color: #ce7b00;}
    .linkto, .linkstpd, .linkdate{text-decoration: underline; cursor: pointer;}
    #load-mask, #load-content{
        display:none;
        position:fixed;
        height:100%;
        width:100%;
        top:0;
        left:0;
    }
    #load-mask{
        background-color:#000000;
        filter:alpha(opacity=70);
        opacity:0.7;
        z-index:1;
    }
    #load-content{
        z-index: 2;
    }

    #closeddate{cursor: pointer;}
    #loader {
        margin-right: auto;
        margin-left: auto; 
        background-color: #ffffff;
        width: 100px;
        height: 100px;
        margin-top: 200px;
    }
	
	.table-penilaian th {
	    background-color: #ffffff;
	    color: #000000;
	    padding-bottom: 4px;
	    padding-top: 5px;
	    text-align: center;
	}
	.table-penilaian td, .table-penilaian th {
	    border: 1px solid #000000;
	    padding: 3px 7px 2px;
		cellspacing:0px;
	}
	.table-penilaian
	{
		border-collapse: collapse;
		width: 100%;
	}
</style>

<div id="load-content">
    <div id="loader">
        <img src="image/icon/loading-big.gif"  style="margin-right: auto;margin-left: auto;"/>
    </div>
</div>
<div id="load-mask"></div>
