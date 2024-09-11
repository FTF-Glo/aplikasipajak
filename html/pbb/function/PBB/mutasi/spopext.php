<?php
if (!isset($data)) {	die("Forbidden direct access");}
if (!$User) {	die("Access not permitted");}
$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);
if (!$bOK) {	die("Function access not permitted");}
require_once("inc/payment/uuid.php");

require_once("inc/payment/comm-central.php");

$dbSpptExt = null;
$dbSppt = null;
$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($module);	

if (isset($jenis) && $jenis == 'penggabungan') {	
    require_once("inc/PBB/dbSpptExtPenggabungan.php");
    require_once("inc/PBB/dbSpptPenggabungan.php");
    $dbSpptExt = new DbSpptExtPenggabungan($dbSpec);
    $dbSppt = new DbSpptPenggabungan($dbSpec);
}else{
    require_once("inc/PBB/dbSpptExtPerubahan.php");
    require_once("inc/PBB/dbSpptPerubahan.php");
    $dbSpptExt = new DbSpptExtPerubahan($dbSpec);
    $dbSppt = new DbSpptPerubahan($dbSpec);
}

if (isset($idd) && isset($v)) {	
	$docVal = $dbSppt->get($idd);	
        $NOP = $docVal[0]['CPM_NOP'];	
	$OP_JML_BANGUNAN = $docVal[0]['CPM_OP_JML_BANGUNAN'];
        $SID = $docVal[0]['CPM_SID'];
}

//////////////////////////////// Process Saving Form Lampiran 1/////////////////////////////
if (isset($newLamp) || isset($newFinal) || isset($newNilai)) {
	$content = array();	
	$content['cpm_op_penggunaan'] 			= $OP_PENGGUNAAN;	
	$content['CPM_OP_LUAS_BANGUNAN'] 		= $OP_LUAS_BANGUNAN;	
	$content['CPM_OP_JML_LANTAI'] 			= $OP_JML_LANTAI;	
	$content['CPM_OP_THN_DIBANGUN'] 		= $OP_THN_DIBANGUN;	
	$content['CPM_OP_THN_RENOVASI'] 		= $OP_THN_RENOVASI;	
	$content['CPM_OP_DAYA'] 			= $OP_DAYA;	
	$content['CPM_OP_KONDISI'] 			= $OP_KONDISI;	
	$content['CPM_OP_KONSTRUKSI'] 			= $OP_KONSTRUKSI;	
	$content['CPM_OP_ATAP'] 			= $OP_ATAP;	
	$content['CPM_OP_DINDING'] 			= $OP_DINDING;	
	$content['CPM_OP_LANTAI'] 			= $OP_LANTAI;	
	$content['CPM_OP_LANGIT'] 			= $OP_LANGIT;	
	$content['CPM_FOP_AC_SPLIT'] 			= (trim($FOP_AC_SPLIT) == '')? '0':$FOP_AC_SPLIT;	
	$content['CPM_FOP_AC_WINDOW'] 			= (trim($FOP_AC_WINDOW) == '')? '0':$FOP_AC_WINDOW;	
	$content['CPM_FOP_AC_CENTRAL'] 			= (trim($FOP_AC_CENTRAL) == '')? '0':$FOP_AC_CENTRAL;	
	$content['CPM_FOP_KOLAM_LUAS'] 			= (trim($FOP_KOLAM_LUAS) == '')? '0':$FOP_KOLAM_LUAS;	
	$content['CPM_FOP_KOLAM_LAPISAN'] 		= $FOP_KOLAM_LAPISAN;	
	$content['CPM_FOP_PERKERASAN_RINGAN'] 		= (trim($FOP_PERKERASAN_RINGAN) == '')? '0':$FOP_PERKERASAN_RINGAN;	
	$content['CPM_FOP_PERKERASAN_SEDANG'] 		= (trim($FOP_PERKERASAN_SEDANG) == '')? '0':$FOP_PERKERASAN_SEDANG;	
	$content['CPM_FOP_PERKERASAN_BERAT'] 		= (trim($FOP_PERKERASAN_BERAT) == '')? '0':$FOP_PERKERASAN_BERAT;	
	$content['CPM_FOP_PERKERASAN_PENUTUP'] 		= (trim($FOP_PERKERASAN_PENUTUP) == '')? '0':$FOP_PERKERASAN_PENUTUP;	
	$content['CPM_FOP_TENIS_LAMPU_BETON'] 		= (trim($FOP_TENIS_LAMPU_BETON) == '')? '0':$FOP_TENIS_LAMPU_BETON;	
	$content['CPM_FOP_TENIS_LAMPU_ASPAL'] 		= (trim($FOP_TENIS_LAMPU_ASPAL) == '')? '0':$FOP_TENIS_LAMPU_ASPAL;	
	$content['CPM_FOP_TENIS_LAMPU_TANAH'] 		= (trim($FOP_TENIS_LAMPU_TANAH) == '')? '0':$FOP_TENIS_LAMPU_TANAH;	
	$content['CPM_FOP_TENIS_TANPA_LAMPU_BETON']     = (trim($FOP_TENIS_TANPA_LAMPU_BETON) == '')? '0':$FOP_TENIS_TANPA_LAMPU_BETON;	
	$content['CPM_FOP_TENIS_TANPA_LAMPU_ASPAL']     = (trim($FOP_TENIS_TANPA_LAMPU_ASPAL) == '')? '0':$FOP_TENIS_TANPA_LAMPU_ASPAL;	
	$content['CPM_FOP_TENIS_TANPA_LAMPU_TANAH']     = (trim($FOP_TENIS_TANPA_LAMPU_TANAH) == '')? '0':$FOP_TENIS_TANPA_LAMPU_TANAH;	
	$content['CPM_FOP_LIFT_PENUMPANG'] 		= (trim($FOP_LIFT_PENUMPANG) == '')? '0':$FOP_LIFT_PENUMPANG;	
	$content['CPM_FOP_LIFT_KAPSUL'] 		= (trim($FOP_LIFT_KAPSUL) == '')? '0':$FOP_LIFT_KAPSUL;	
	$content['CPM_FOP_LIFT_BARANG'] 		= (trim($FOP_LIFT_BARANG) == '')? '0':$FOP_LIFT_BARANG;	
	$content['CPM_FOP_ESKALATOR_SEMPIT'] 		= (trim($FOP_ESKALATOR_SEMPIT) == '')? '0':$FOP_ESKALATOR_SEMPIT;	
	$content['CPM_FOP_ESKALATOR_LEBAR'] 		= (trim($FOP_ESKALATOR_LEBAR) == '')? '0':$FOP_ESKALATOR_LEBAR;	
        $content['CPM_PAGAR_BATA_PANJANG']              = '0';
        $content['CPM_PAGAR_BESI_PANJANG']              = '0';

        if($FOP_PAGAR > 0){
            if($FOP_PAGAR_BAHAN == 'Bata/Batako'){
                $content['CPM_PAGAR_BATA_PANJANG']      = $FOP_PAGAR;
            }else {
                $content['CPM_PAGAR_BESI_PANJANG']      = $FOP_PAGAR;
            }
        }
	$content['CPM_PEMADAM_HYDRANT']                 = ($PEMADAM_HYDRANT == '1')? '1':'0';	
	$content['CPM_PEMADAM_SPRINKLER']               = ($PEMADAM_SPRINKLER == '1')? '1':'0';
	$content['CPM_PEMADAM_FIRE_ALARM']              = ($PEMADAM_FIRE_ALARM == '1')? '1':'0';
	$content['CPM_FOP_SALURAN']                     = (trim($FOP_SALURAN) == '')? '0':$FOP_SALURAN;
	$content['CPM_FOP_SUMUR'] 			= (trim($FOP_SUMUR) == '')? '0':$FOP_SUMUR;
	$content['CPM_JPB2_KELAS_BANGUNAN']             = isset($JPB2_KELAS_BANGUNAN) ? $JPB2_KELAS_BANGUNAN : "0";	
	
        $content['CPM_JPB3_TINGGI_KOLOM']               = isset($JPB3_TINGGI_KOLOM) ? $JPB3_TINGGI_KOLOM : "0";	
	$content['CPM_JPB3_LEBAR_BENTANG']              = isset($JPB3_LEBAR_BENTANG) ? $JPB3_LEBAR_BENTANG : "0";	
	$content['CPM_JPB3_DAYA_DUKUNG_LANTAI']         = isset($JPB3_DAYA_DUKUNG_LANTAI) ? $JPB3_DAYA_DUKUNG_LANTAI : "0";	
	$content['CPM_JPB3_KELILING_DINDING']           = isset($JPB3_KELILING_DINDING) ? $JPB3_KELILING_DINDING : "0";	
	$content['CPM_JPB3_LUAS_MEZZANINE']             = isset($JPB3_LUAS_MEZZANINE) ? $JPB3_LUAS_MEZZANINE : "0";
        
        $content['CPM_JPB8_TINGGI_KOLOM']               = isset($JPB8_TINGGI_KOLOM) ? $JPB8_TINGGI_KOLOM : "0";	
	$content['CPM_JPB8_LEBAR_BENTANG']              = isset($JPB8_LEBAR_BENTANG) ? $JPB8_LEBAR_BENTANG : "0";	
	$content['CPM_JPB8_DAYA_DUKUNG_LANTAI']         = isset($JPB8_DAYA_DUKUNG_LANTAI) ? $JPB8_DAYA_DUKUNG_LANTAI : "0";	
	$content['CPM_JPB8_KELILING_DINDING']           = isset($JPB8_KELILING_DINDING) ? $JPB8_KELILING_DINDING : "0";	
	$content['CPM_JPB8_LUAS_MEZZANINE']             = isset($JPB8_LUAS_MEZZANINE) ? $JPB8_LUAS_MEZZANINE : "0";
        
        $content['CPM_JPB4_KELAS_BANGUNAN']             = isset($JPB4_KELAS_BANGUNAN) ? $JPB4_KELAS_BANGUNAN : "0";	
        $content['CPM_JPB5_KELAS_BANGUNAN']             = isset($JPB5_KELAS_BANGUNAN) ? $JPB5_KELAS_BANGUNAN : "0";	
	$content['CPM_JPB5_LUAS_KMR_AC_CENTRAL']        = isset($JPB5_LUAS_KMR_AC_CENTRAL) ? $JPB5_LUAS_KMR_AC_CENTRAL : "0";	
	$content['CPM_JPB5_LUAS_RUANG_AC_CENTRAL']      = isset($JPB5_LUAS_RUANG_AC_CENTRAL) ? $JPB5_LUAS_RUANG_AC_CENTRAL : "0";	
	$content['CPM_JPB6_KELAS_BANGUNAN']             = isset($JPB6_KELAS_BANGUNAN) ? $JPB6_KELAS_BANGUNAN : "0";	
	$content['CPM_JPB7_JENIS_HOTEL']                = isset($JPB7_JENIS_HOTEL) ? $JPB7_JENIS_HOTEL : "0";	
	$content['CPM_JPB7_JUMLAH_BINTANG']             = isset($JPB7_JUMLAH_BINTANG) ? $JPB7_JUMLAH_BINTANG : "0";	
	$content['CPM_JPB7_JUMLAH_KAMAR']               = isset($JPB7_JUMLAH_KAMAR) ? $JPB7_JUMLAH_KAMAR : "0";	
	$content['CPM_JPB7_LUAS_KMR_AC_CENTRAL']        = isset($JPB7_LUAS_KMR_AC_CENTRAL) ? $JPB7_LUAS_KMR_AC_CENTRAL : "0";	
	$content['CPM_JPB7_LUAS_RUANG_AC_CENTRAL']      = isset($JPB7_LUAS_RUANG_AC_CENTRAL) ? $JPB7_LUAS_RUANG_AC_CENTRAL : "0";	
	$content['CPM_JPB9_KELAS_BANGUNAN']             = isset($JPB9_KELAS_BANGUNAN) ? $JPB9_KELAS_BANGUNAN : "0";	
	$content['CPM_JPB12_TIPE_BANGUNAN']             = isset($JPB12_TIPE_BANGUNAN) ? $JPB12_TIPE_BANGUNAN : "0";	
	$content['CPM_JPB13_KELAS_BANGUNAN']            = isset($JPB13_KELAS_BANGUNAN) ? $JPB13_KELAS_BANGUNAN : "0";	
	$content['CPM_JPB13_JUMLAH_APARTEMEN']          = isset($JPB13_JUMLAH_APARTEMEN) ? $JPB13_JUMLAH_APARTEMEN : "0";	
	$content['CPM_JPB13_LUAS_APARTEMEN_AC_CENTRAL'] = isset($JPB13_LUAS_APARTEMEN_AC_CENTRAL) ? $JPB13_LUAS_APARTEMEN_AC_CENTRAL : "0";	
	$content['CPM_JPB13_LUAS_RUANG_AC_CENTRAL']     = isset($JPB13_LUAS_RUANG_AC_CENTRAL) ? $JPB13_LUAS_RUANG_AC_CENTRAL : "0";	
	$content['CPM_JPB15_TANGKI_MINYAK_KAPASITAS']   = isset($JPB15_TANGKI_MINYAK_KAPASITAS) ? $JPB15_TANGKI_MINYAK_KAPASITAS : "0";	
	$content['CPM_JPB15_TANGKI_MINYAK_LETAK']       = isset($JPB15_TANGKI_MINYAK_LETAK) ? $JPB15_TANGKI_MINYAK_LETAK : "0";	
	$content['CPM_JPB16_KELAS_BANGUNAN']            = isset($JPB16_KELAS_BANGUNAN) ? $JPB16_KELAS_BANGUNAN : "0";
	$content['CPM_PAYMENT_PENILAIAN_BGN']           = $PAYMENT_PENILAIAN_BGN;	
        $content['CPM_PAYMENT_SISTEM'] = isset($PAYMENT_SISTEM) ? $PAYMENT_SISTEM/1000 : "";	
        if($PAYMENT_PENILAIAN_BGN=='sistem') $content['CPM_PAYMENT_INDIVIDU'] = "0";	
	else $content['CPM_PAYMENT_INDIVIDU'] = isset($PAYMENT_INDIVIDU) ? $PAYMENT_INDIVIDU/1000 : "0";
	
	if ($dbSpptExt->isExist($idd, $v, $OP_NUM)) {	
		// echo '1'; exit;
		$bOK = $dbSpptExt->edit($idd, $v, $OP_NUM, $content);	
	} else {	
		// echo '2'; exit;
		$bOK = $dbSpptExt->add($idd, $v, $OP_NUM, $content);	
	}		
	//go to page 2 or finalize data	
	if ($bOK) {
                if (isset($jenis) && $jenis == 'penggabungan')
                    $sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"".$appConfig['tahun_tagihan']."\",\"KELURAHAN\":\"\",\"TIPE\":\"4\",\"NOP\":\"".$NOP."\",\"SUSULAN\":\"0\"}";
                else $sRequestStream = "{\"PAN\":\"TPM\",\"TAHUN\":\"".$appConfig['tahun_tagihan']."\",\"KELURAHAN\":\"\",\"TIPE\":\"3\",\"NOP\":\"".$NOP."\",\"SUSULAN\":\"0\"}";
                $bOK = GetRemoteResponse($appConfig['TPB_ADDRESS'], $appConfig['TPB_PORT'], $appConfig['TPB_TIMEOUT'], $sRequestStream, $sResp);
                // die($sResp);
		if (isset($newNilai)) {	
                    if (isset($jenis) && $jenis == 'penggabungan')
                        $params = "a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&jenis=penggabungan&idd=".$idd."&v=".$v."&num=" . $OP_NUM;
                    else $params = "a=$a&m=$m&f=" . $arConfig['id_lampiran'] . "&idd=".$idd."&v=".$v."&num=" . $OP_NUM;
                    header("Location: main.php?param=".base64_encode($params));	
                } else {
                    if (isset($jenis) && $jenis == 'penggabungan')
                        $params = "a=$a&m=$m&f=" . $arConfig['id_penggabungan_form'] . "&svcid=" . $SID;
                    else $params = "a=$a&m=$m&f=" . $arConfig['id_perubahan_form'] . "&svcid=" . $SID;
                    header("Location: main.php?param=".base64_encode($params));
		}
        }
}

if (isset($idd) && isset($v)) {	
        $extVal = $dbSpptExt->get($idd, $v, $num);
        
        foreach ($extVal[0] as $key => $value) {		
		$tKey = substr($key,4);		
		$$tKey = $value;	
	}
}?>
<script type="text/javascript" src="inc/datepicker/datepickercontrol.js"></script>
<script type="text/javascript" src="function/PBB/consol/script.js?v.0.0.0.3"></script>
<link rel="stylesheet" href="function/PBB/consol/newspop.css" type="text/css">
<link type="text/css" rel="stylesheet" href="inc/datepicker/datepickercontrol.css"> 
<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">

<div class="col-md-12">
<?php $NBParam = base64_encode('{"ServerAddress":"'.$appConfig['TPB_ADDRESS'].'","ServerPort":"'.$appConfig['TPB_PORT'].'","ServerTimeOut":"'.$appConfig['TPB_TIMEOUT'].'"}');
include("lamp1.php");?>
</div>