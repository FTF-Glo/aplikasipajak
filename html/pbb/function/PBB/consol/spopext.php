<?php
if (!isset($data)) {
	die("Forbidden direct access");
}
if (!$User) {
	die("Access not permitted");
}
$bOK = $User->IsFunctionGranted($uid, $area, $module, $function);
if (!$bOK) {
	die("Function access not permitted");
}
require_once("inc/payment/uuid.php");
require_once("inc/PBB/dbSpptExt.php");
require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbWajibPajak.php");
require_once("inc/PBB/dbSpptHistory.php");
require_once("inc/payment/comm-central.php");

$dbSpptExt = new DbSpptExt($dbSpec);
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$appConfig = $User->GetAppConfig($application);
$arConfig = $User->GetModuleConfig($module);

$isSusulan = ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']);

// echo "<pre>";
// print_r($arConfig);
// echo "</pre>";
// exit;
//Preparing Parameters

if (isset($idt)) {
	$tran = $dbSpptTran->get($idt);
	$idd = $tran[0]['CPM_TRAN_SPPT_DOC_ID'];
	$v = $tran[0]['CPM_SPPT_DOC_VERSION'];
}
if (isset($idd) && isset($v)) {
	$docVal = $dbSppt->get($idd);
	// die(var_dump($docVal));
	$NOP = $docVal[0]['CPM_NOP'];
	$OP_JML_BANGUNAN = $docVal[0]['CPM_OP_JML_BANGUNAN'];
}
//////////////////////////////// Process Saving Form Lampiran 1/////////////////////////////
if (isset($newLamp) || isset($newFinal)  || isset($newNilai)) {
	// die($PAYMENT_SISTEM);
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
	$content['CPM_FOP_AC_SPLIT'] 			= (trim($FOP_AC_SPLIT) == '') ? '0' : $FOP_AC_SPLIT;
	$content['CPM_FOP_AC_WINDOW'] 			= (trim($FOP_AC_WINDOW) == '') ? '0' : $FOP_AC_WINDOW;
	$content['CPM_FOP_AC_CENTRAL'] 			= (trim($FOP_AC_CENTRAL) == '') ? '0' : $FOP_AC_CENTRAL;
	$content['CPM_FOP_KOLAM_LUAS'] 			= (trim($FOP_KOLAM_LUAS) == '') ? '0' : $FOP_KOLAM_LUAS;
	$content['CPM_FOP_KOLAM_LAPISAN'] 		= $FOP_KOLAM_LAPISAN;
	$content['CPM_FOP_PERKERASAN_RINGAN'] 		= (trim($FOP_PERKERASAN_RINGAN) == '') ? '0' : $FOP_PERKERASAN_RINGAN;
	$content['CPM_FOP_PERKERASAN_SEDANG'] 		= (trim($FOP_PERKERASAN_SEDANG) == '') ? '0' : $FOP_PERKERASAN_SEDANG;
	$content['CPM_FOP_PERKERASAN_BERAT'] 		= (trim($FOP_PERKERASAN_BERAT) == '') ? '0' : $FOP_PERKERASAN_BERAT;
	$content['CPM_FOP_PERKERASAN_PENUTUP'] 		= (trim($FOP_PERKERASAN_PENUTUP) == '') ? '0' : $FOP_PERKERASAN_PENUTUP;
	$content['CPM_FOP_TENIS_LAMPU_BETON'] 		= (trim($FOP_TENIS_LAMPU_BETON) == '') ? '0' : $FOP_TENIS_LAMPU_BETON;
	$content['CPM_FOP_TENIS_LAMPU_ASPAL'] 		= (trim($FOP_TENIS_LAMPU_ASPAL) == '') ? '0' : $FOP_TENIS_LAMPU_ASPAL;
	$content['CPM_FOP_TENIS_LAMPU_TANAH'] 		= (trim($FOP_TENIS_LAMPU_TANAH) == '') ? '0' : $FOP_TENIS_LAMPU_TANAH;
	$content['CPM_FOP_TENIS_TANPA_LAMPU_BETON']     = (trim($FOP_TENIS_TANPA_LAMPU_BETON) == '') ? '0' : $FOP_TENIS_TANPA_LAMPU_BETON;
	$content['CPM_FOP_TENIS_TANPA_LAMPU_ASPAL']     = (trim($FOP_TENIS_TANPA_LAMPU_ASPAL) == '') ? '0' : $FOP_TENIS_TANPA_LAMPU_ASPAL;
	$content['CPM_FOP_TENIS_TANPA_LAMPU_TANAH']     = (trim($FOP_TENIS_TANPA_LAMPU_TANAH) == '') ? '0' : $FOP_TENIS_TANPA_LAMPU_TANAH;
	$content['CPM_FOP_LIFT_PENUMPANG'] 		= (trim($FOP_LIFT_PENUMPANG) == '') ? '0' : $FOP_LIFT_PENUMPANG;
	$content['CPM_FOP_LIFT_KAPSUL'] 		= (trim($FOP_LIFT_KAPSUL) == '') ? '0' : $FOP_LIFT_KAPSUL;
	$content['CPM_FOP_LIFT_BARANG'] 		= (trim($FOP_LIFT_BARANG) == '') ? '0' : $FOP_LIFT_BARANG;
	$content['CPM_FOP_ESKALATOR_SEMPIT'] 		= (trim($FOP_ESKALATOR_SEMPIT) == '') ? '0' : $FOP_ESKALATOR_SEMPIT;
	$content['CPM_FOP_ESKALATOR_LEBAR'] 		= (trim($FOP_ESKALATOR_LEBAR) == '') ? '0' : $FOP_ESKALATOR_LEBAR;
	$content['CPM_PAGAR_BATA_PANJANG']              = '0';
	$content['CPM_PAGAR_BESI_PANJANG']              = '0';

	if ($FOP_PAGAR > 0) {
		if ($FOP_PAGAR_BAHAN == 'Bata/Batako') {
			$content['CPM_PAGAR_BATA_PANJANG']      = $FOP_PAGAR;
		} else {
			$content['CPM_PAGAR_BESI_PANJANG']      = $FOP_PAGAR;
		}
	}
	$content['CPM_PEMADAM_HYDRANT']                 = ($PEMADAM_HYDRANT == '1') ? '1' : '0';
	$content['CPM_PEMADAM_SPRINKLER']               = ($PEMADAM_SPRINKLER == '1') ? '1' : '0';
	$content['CPM_PEMADAM_FIRE_ALARM']              = ($PEMADAM_FIRE_ALARM == '1') ? '1' : '0';
	$content['CPM_FOP_SALURAN']                     = (trim($FOP_SALURAN) == '') ? '0' : $FOP_SALURAN;
	$content['CPM_FOP_SUMUR'] 			= (trim($FOP_SUMUR) == '') ? '0' : $FOP_SUMUR;
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
	$content['CPM_PAYMENT_SISTEM'] = isset($PAYMENT_SISTEM) ? $PAYMENT_SISTEM / 1000 : "";
	if ($PAYMENT_PENILAIAN_BGN == 'sistem') $content['CPM_PAYMENT_INDIVIDU'] = "0";
	else $content['CPM_PAYMENT_INDIVIDU'] = isset($PAYMENT_INDIVIDU) ? $PAYMENT_INDIVIDU / 1000 : "0";
	//	$content['CPM_NJOP_BANGUNAN'] = $NJOP_BANGUNAN;

	if ($dbSpptExt->isExist($idd, $v, $OP_NUM)) {
		$bOK = $dbSpptExt->edit($idd, $v, $OP_NUM, $content);
	} else {
		$bOK = $dbSpptExt->add($idd, $v, $OP_NUM, $content);
	}

	//go to page 2 or finalize data	
	if ($bOK) {
		$directTo = BASE_URL."inc/PBB/svc-bangunan.php";
		$param = '{NOP:"' .$NOP. '", TAHUN:"' .$appConfig['tahun_tagihan']. '", TABEL1:"cppmod_pbb_sppt", TABEL2:"cppmod_pbb_sppt_ext"}';
		phpPenilaian($param, $directTo);

		if (isset($newNilai)) {
			header("Location: main.php?param=" . base64_encode("a=$a&m=$m&f=$f&idt=$idt&num=" . ($OP_NUM)));
			
		} else if (isset($newFinal)) {
			$lastID = c_uuid();
			$aVal['CPM_TRAN_FLAG'] = 1;
			$vals 	= $dbSpptTran->get($idt);

			//Add By ZNK (05 Juni 2017) : Bug Fixing data hilang ketika ditolak dan difinalkan melalui button Finalkan di LSPOP
			$vers = (isset($docVal[0]['CPM_SPPT_DOC_VERSION']) ? $docVal[0]['CPM_SPPT_DOC_VERSION'] : "1");
			if ($vals[0]['CPM_TRAN_STATUS'] == 6 || $vals[0]['CPM_TRAN_STATUS'] == 7 || $vals[0]['CPM_TRAN_STATUS'] == 8) {

				$oldver = $vers;
				$vers 	= $vers + 1;
				$vals[0]['CPM_SPPT_DOC_VERSION'] = $vers;

				unset($docVal[0]['CPM_SPPT_DOC_ID']);
				unset($docVal[0]['CPM_SPPT_DOC_VERSION']);
				//Insert dengan versi baru
				$bOK 	= $dbSppt->add($idd, $vers, $docVal[0]);
				if ($bOK) {
					//doc version sudah di increment, sekarang mengincrement ext version
					$bOK = $dbSpptExt->incVers($idd, $vers, $oldver);
					if ($bOK) {
						//Delete versi lama
						$bOK = $dbSppt->del($idd, $oldver);
						if ($bOK) {
							//Delete versi lama
							$bOK = $dbSpptExt->del($idd, $oldver);
						} else {
							echo "ERR003";
						}
					} else {
						echo "ERR002";
					}
				} else {
					echo "ERR001";
				}
			}
			//====END BY ZNK=====

			$dbSpptTran->edit($idt, $aVal);
			if ($appConfig['jumlah_verifikasi'] == 0) {
				$vals[0]['CPM_TRAN_STATUS'] = 4;
			} else {
				$vals[0]['CPM_TRAN_STATUS'] = 1;
			}
			$idd 		= $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
			$v 			= $vals[0]['CPM_SPPT_DOC_VERSION'];
			$docSPPT 	= $dbSppt->get($idd, $v);

			unset($vals[0]['CPM_TRAN_ID']);
			unset($vals[0]['CPM_TRAN_DATE']);
			$dbSpptTran->add($lastID, $vals[0]);

			if ($appConfig['jumlah_verifikasi'] == 0) {

				$contentWP = array();
				$contentWP['CPM_WP_STATUS'] = $docSPPT[0]['CPM_WP_STATUS'];
				$contentWP['CPM_WP_PEKERJAAN'] = $docSPPT[0]['CPM_WP_PEKERJAAN'];
				$contentWP['CPM_WP_NAMA'] = strtoupper($docSPPT[0]['CPM_WP_NAMA']);
				$contentWP['CPM_WP_ALAMAT'] = strtoupper($docSPPT[0]['CPM_WP_ALAMAT']);
				$contentWP['CPM_WP_KELURAHAN'] = strtoupper($docSPPT[0]['CPM_WP_KELURAHAN']);
				$contentWP['CPM_WP_RT'] = strtoupper($docSPPT[0]['CPM_WP_RT']);
				$contentWP['CPM_WP_RW'] = strtoupper($docSPPT[0]['CPM_WP_RW']);
				$contentWP['CPM_WP_PROPINSI'] = strtoupper($docSPPT[0]['CPM_WP_PROPINSI']);
				$contentWP['CPM_WP_KOTAKAB'] = strtoupper($docSPPT[0]['CPM_WP_KOTAKAB']);
				$contentWP['CPM_WP_KECAMATAN'] = strtoupper($docSPPT[0]['CPM_WP_KECAMATAN']);
				$contentWP['CPM_WP_KODEPOS'] = strtoupper($docSPPT[0]['CPM_WP_KODEPOS']);
				$contentWP['CPM_WP_NO_HP'] = strtoupper($docSPPT[0]['CPM_WP_NO_HP']);

				$bOK = $dbWajibPajak->save($docSPPT[0]['CPM_WP_NO_KTP'], $contentWP);

				/*kirim data peta*/
				$url = $appConfig['MAP_URL'] . "service/migrate/movetopersil";

				$query = sprintf("SELECT CPC_TKL_KELURAHAN FROM cppmod_tax_kelurahan WHERE CPC_TKL_ID = '%s'", $docSPPT[0]['CPM_OP_KELURAHAN']);
				$dtKel = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
				$nmKel = $dtKel['CPC_TKL_KELURAHAN'];

				$query = sprintf("SELECT CPC_TKC_KECAMATAN FROM cppmod_tax_kecamatan WHERE CPC_TKC_ID = '%s'", $docSPPT[0]['CPM_OP_KECAMATAN']);
				$dtKec = mysqli_fetch_assoc(mysqli_query($DBLink, $query));
				$nmKec = $dtKec['CPC_TKC_KECAMATAN'];

				$jenisTanah = array(
					1 => 'TANAH+BANGUNAN',
					2 => 'KAVLING SIAP BANGUN',
					3 => 'TANAH KOSONG',
					4 => 'FASILITAS UMUM'
				);
				$vars = array(
					'jns_tanah' => $jenisTanah[$docSPPT[0]['CPM_OT_JENIS']],
					'kelas_znt' => $docSPPT[0]['CPM_OT_ZONA_NILAI'],
					'nir' => '',
					'cpm_sppt_id' => $docSPPT[0]['CPM_SPPT_DOC_ID'],
					'jns_trnks' => '',
					'nop' => $docSPPT[0]['CPM_NOP'],
					'nop_baru' => $docSPPT[0]['CPM_NOP'],
					'nop_brsm' => $docSPPT[0]['CPM_NOP'],
					'nop_asal' => $docSPPT[0]['CPM_NOP'],
					'op_kel' => $nmKel,
					'op_kec' => $nmKec,
					'status' => strtoupper($docSPPT[0]['CPM_WP_STATUS']),
					'pekerjaan' => $docSPPT[0]['CPM_WP_PEKERJAAN'],
					'nama_sp' => $docSPPT[0]['CPM_WP_NAMA'],
					'latitude' => $docSPPT[0]['CPM_OT_LATITUDE'],
					'longitude' => $docSPPT[0]['CPM_OT_LATITUDE'],
					'geom' => ''
				);

				$postData = http_build_query($vars);
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($ch);
				/* end kirim*/

				if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
					$bOK = $dbSpptHistory->goSusulan($idt);
				} else {
					$bOK = $dbSpptHistory->goFinal($idt);
				}
			}

			header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
		} else {
			if ($OP_NUM < $OP_JML_BANGUNAN) {
				header("Location: main.php?param=" . base64_encode("a=$a&m=$m&f=$f&idt=$idt&num=" . ($OP_NUM + 1)));
			} else {
				header("Location: main.php?param=" . base64_encode("a=$a&m=$m"));
			}
		}
	}
}
//////////////////////////////// Process Saving Form Lampiran 2/////////////////////////////
/* DEPRECATED
if (isset($newLamp2) || isset($newLamp2next)) {		
	$content = array();	
	$content['CPM_PABRIK_TINGGI'] = isset($PABRIK_TINGGI) ? $PABRIK_TINGGI : "";	
	$content['CPM_PABRIK_LEBAR'] = isset($PABRIK_LEBAR) ? $PABRIK_LEBAR : "";	
	$content['CPM_PABRIK_DAYA'] = isset($PABRIK_DAYA) ? $PABRIK_DAYA : "";	
	$content['CPM_PABRIK_KELILING'] = isset($PABRIK_KELILING) ? $PABRIK_KELILING : "";	
	$content['CPM_PABRIK_LUAS'] = isset($PABRIK_LUAS) ? $PABRIK_LUAS : "";	
	$content['CPM_OP_KELAS'] = isset($OP_KELAS) ? $OP_KELAS : "";	
	$content['CPM_OP_LUAS_KMR'] = isset($OP_LUAS_KMR) ? $OP_LUAS_KMR : "";	
	$content['CPM_OP_LUAS_LAIN'] = isset($OP_LUAS_LAIN) ? $OP_LUAS_LAIN : "";	
	$content['CPM_OP_JML_KMR'] = isset($OP_JML_KMR) ? $OP_JML_KMR : "";	
	$content['CPM_OP_HOTEL_BINTANG'] = isset($OP_HOTEL_BINTANG) ? $OP_HOTEL_BINTANG : "";	
	$content['CPM_OP_TANGKI_KAPASITAS'] = isset($OP_TANGKI_KAPASITAS) ? $OP_TANGKI_KAPASITAS : "";	
	$content['CPM_OP_TANGKI_LETAK'] = isset($OP_TANGKI_LETAK) ? $OP_TANGKI_LETAK : "";	
	$content['CPM_PAYMENT_SISTEM'] = isset($PAYMENT_SISTEM) ? $PAYMENT_SISTEM : "";	
	$content['CPM_PAYMENT_INDIVIDU'] = isset($PAYMENT_INDIVIDU) ? $PAYMENT_INDIVIDU : "";		
	//check availability	
	if ($dbSpptExt->isExist($idd, $v, $num)) {		
		$bOK = $dbSpptExt->edit ($idd, $v, $num, $content);	
	} else {		
		$bOK = $dbSpptExt->add ($idd, $v, $num, $content);	
	}		
	//determine next page	
	if ($bOK) {		
		if (isset($newLamp2)) {			
			header("Location: main.php?param=".base64_encode("a=$a&m=$m"));		
		} else if (isset($newLamp2next)) {			
			header("Location: main.php?param=".base64_encode("a=$a&m=$m&f=$f&idt=$idt"));		
		}	
	} else {	
		echo "<div style='error'>Penyimpanan gagal</div>";	
	}
}*/
if (isset($idd) && isset($v)) {
	// die('tes');
	$extVal = $dbSpptExt->get($idd, $v, $num);
	foreach ($extVal[0] as $key => $value) {
		$tKey = substr($key, 4);
		$$tKey = $value;
	}
} ?>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/datepicker/datepickercontrol.js"></script>
<script type="text/javascript" src="function/PBB/consol/script.js"></script>
<link rel="stylesheet" href="function/PBB/consol/newspop.css" type="text/css">
<link type="text/css" rel="stylesheet" href="inc/datepicker/datepickercontrol.css">
<input type="hidden" id="DPC_TODAY_TEXT" value="today">
<input type="hidden" id="DPC_BUTTON_TITLE" value="Open calendar...">
<input type="hidden" id="DPC_MONTH_NAMES" value="['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']">
<input type="hidden" id="DPC_DAY_NAMES" value="['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']">
<?php $NBParam = base64_encode('{"ServerAddress":"' . $arConfig['TPB_ADDRESS'] . '","ServerPort":"' . $arConfig['TPB_PORT'] . '","ServerTimeOut":"' . $arConfig['TPB_TIMEOUT'] . '"}');
?>
<div class="col-md-12">
	<?php include("lamp1.php"); ?>
</div>