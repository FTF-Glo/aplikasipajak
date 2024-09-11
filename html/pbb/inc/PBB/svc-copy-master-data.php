<?php
$penilaianTimeOut = 3600;
set_time_limit(3700);
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/central/dbspec-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	exit(1);
}

//variable for input program:
$getSvcRequest 	= (@isset($_REQUEST['req']) ? $_REQUEST['req'] : '');
$getSvcRequest 	= base64_decode($getSvcRequest);
$json 			= new Services_JSON();
$prm 			= $json->decode($getSvcRequest);
$tahun 			= $prm->TAHUN;

//COPY DATA CODE
$insertAll = insertAll();
if ($insertAll == true) {
	echo "0000";
}
// else {
// $bOK = DelAll();
// if($bOK){
// echo "0001";
// } else echo "Terjadi Kesalahan Server";
// }


function insertAll()
{
	global $DBLink;

	$daya_dukung 			 = existData('cppmod_pbb_dbkb_daya_dukung', 'CPM_THN_DBKB_DAYA_DUKUNG');
	$jpb12 		 			 = existData('cppmod_pbb_dbkb_jpb12', 'CPM_THN_DBKB_JPB12');
	$jpb13 		 			 = existData('cppmod_pbb_dbkb_jpb13', 'CPM_THN_DBKB_JPB13');
	$jpb14 		 			 = existData('cppmod_pbb_dbkb_jpb14', 'CPM_THN_DBKB_JPB14');
	$jpb15 		 			 = existData('cppmod_pbb_dbkb_jpb15', 'CPM_THN_DBKB_JPB15');
	$jpb16 		 			 = existData('cppmod_pbb_dbkb_jpb16', 'CPM_THN_DBKB_JPB16');
	$jpb2 		 			 = existData('cppmod_pbb_dbkb_jpb2', 'CPM_THN_DBKB_JPB2');
	$jpb3 		 			 = existData('cppmod_pbb_dbkb_jpb3', 'CPM_THN_DBKB_JPB3');
	$jpb4 		 			 = existData('cppmod_pbb_dbkb_jpb4', 'CPM_THN_DBKB_JPB4');
	$jpb5 		 			 = existData('cppmod_pbb_dbkb_jpb5', 'CPM_THN_DBKB_JPB5');
	$jpb6 		 			 = existData('cppmod_pbb_dbkb_jpb6', 'CPM_THN_DBKB_JPB6');
	$jpb7 		 			 = existData('cppmod_pbb_dbkb_jpb7', 'CPM_THN_DBKB_JPB7');
	$jpb8 		 			 = existData('cppmod_pbb_dbkb_jpb8', 'CPM_THN_DBKB_JPB8');
	$jpb9 		 			 = existData('cppmod_pbb_dbkb_jpb9', 'CPM_THN_DBKB_JPB9');
	$material	 			 = existData('cppmod_pbb_dbkb_material', 'CPM_THN_DBKB_MATERIAL');
	$mezanin	 			 = existData('cppmod_pbb_dbkb_mezanin', 'CPM_THN_DBKB_MEZANIN');
	$standard				 = existData('cppmod_pbb_dbkb_standard', 'CPM_THN_DBKB_STANDARD');
	$fas_dep_jpb_kls_bintang = existData('cppmod_pbb_fas_dep_jpb_kls_bintang', 'THN_DEP_JPB_KLS_BINTANG');
	$fas_dep_min_max		 = existData('cppmod_pbb_fas_dep_min_max', 'THN_DEP_MIN_MAX');
	$fas_non_dep			 = existData('cppmod_pbb_fas_non_dep', 'THN_NON_DEP');
	$kegiatan_harga			 = existData('cppmod_pbb_kegiatan_harga', 'CPM_TAHUN');
	$hrg_kegiatan_jpb8		 = existData('cppmod_pbb_hrg_kegiatan_jpb8', 'CPM_THN_HRG_PEKERJAAN_JPB8');
	$resource_harga			 = existData('cppmod_pbb_resource_harga', 'CPM_TAHUN');
	$harga_satuan			 = existData('cppmod_pbb_harga_satuan', 'CPM_TAHUN');
	$kayu_ulin				 = existData('cppmod_pbb_kayu_ulin', 'THN_STATUS_KAYU_ULIN');

	if (!$daya_dukung) {
		$insertDayaDukung = insertDayaDukung();
	}
	// else{
	// 	return true;
	// }


	if (!$jpb12) {
		$insertJPB12 = insertJPB12();
	}
	if (!$jpb13) {
		$insertJPB13 = insertJPB13();
	}
	if (!$jpb14) {
		$insertJPB14 = insertJPB14();
	}
	if (!$jpb15) {
		$insertJPB15 = insertJPB15();
	}
	if (!$jpb16) {
		$insertJPB16 = insertJPB16();
	}
	if (!$jpb2) {
		$insertJPB2 = insertJPB2();
	}
	if (!$jpb3) {
		$insertJPB3 = insertJPB3();
	}
	if (!$jpb4) {
		$insertJPB4 = insertJPB4();
	}
	if (!$jpb5) {
		$insertJPB5 = insertJPB5();
	}
	if (!$jpb6) {
		$insertJPB6 = insertJPB6();
	}
	if (!$jpb7) {
		$insertJPB7 = insertJPB7();
	}
	if (!$jpb8) {
		$insertJPB8 = insertJPB8();
	}
	if (!$jpb9) {
		$insertJPB9 = insertJPB9();
	}
	if (!$material) {
		$insertMaterial = insertMaterial();
	}
	if (!$mezanin) {
		$insertMezanin = insertMezanin();
	}
	if (!$standard) {
		$insertStandard = insertStandard();
	}
	if (!$fas_dep_jpb_kls_bintang) {
		$insertFasDepJPBKlsBintang = insertFasDepJPBKlsBintang();
	}
	if (!$fas_dep_min_max) {
		$insertFasDepMinMax = insertFasDepMinMax();
	}
	if (!$fas_non_dep) {
		$insertFasNonDep = insertFasNonDep();
	}
	if (!$harga_satuan) {
		$insertHargaSatuan = insertHargaSatuan();
	}
	if (!$hrg_kegiatan_jpb8) {
		$insertHrgKegiatanJPB8 = insertHrgKegiatanJPB8();
	}
	if (!$kayu_ulin) {
		$insertKayuUlin = insertKayuUlin();
	}
	if (!$kegiatan_harga) {
		$insertKegiatanHarga = insertKegiatanHarga();
	}
	if (!$resource_harga) {
		$insertResourceHarga = insertResourceHarga();
	}
	// else {
	// 	return true;
	// }

	if (($insertDayaDukung == true) && ($insertJPB12 == true) && ($insertJPB13 == true) && ($insertJPB14 == true) && ($insertJPB15 == true) && ($insertJPB16 == true)
		&& ($insertJPB2 == true) && ($insertJPB3 == true) && ($insertJPB4 == true) && ($insertJPB5 == true) && ($insertJPB6 == true) && ($insertJPB7 == true)
		&& ($insertJPB8 == true) && ($insertJPB9 == true) && ($insertMaterial == true) && ($insertMezanin == true) && ($insertStandard == true)
		&& ($insertFasDepJPBKlsBintang == true) && ($insertFasDepMinMax == true) && ($insertFasNonDep == true) && ($insertHargaSatuan == true)
		&& ($insertHrgKegiatanJPB8 == true) && ($insertKayuUlin == true) && ($insertKegiatanHarga == true) && ($insertResourceHarga == true)
	) {
		return true;
	} else
		return false;
}

function DelAll()
{
	global $DBLink, $tahun;

	$delDaya_dukung 			 = qDelete('cppmod_pbb_dbkb_daya_dukung', 'CPM_THN_DBKB_DAYA_DUKUNG');
	$delJpb12 		 			 = qDelete('cppmod_pbb_dbkb_jpb12', 'CPM_THN_DBKB_JPB12');
	$delJpb13 		 			 = qDelete('cppmod_pbb_dbkb_jpb13', 'CPM_THN_DBKB_JPB13');
	$delJpb14 		 			 = qDelete('cppmod_pbb_dbkb_jpb14', 'CPM_THN_DBKB_JPB14');
	$delJpb15 		 			 = qDelete('cppmod_pbb_dbkb_jpb15', 'CPM_THN_DBKB_JPB15');
	$delJpb16 		 			 = qDelete('cppmod_pbb_dbkb_jpb16', 'CPM_THN_DBKB_JPB16');
	$delJpb2 		 			 = qDelete('cppmod_pbb_dbkb_jpb2', 'CPM_THN_DBKB_JPB2');
	$delJpb3 		 			 = qDelete('cppmod_pbb_dbkb_jpb3', 'CPM_THN_DBKB_JPB3');
	$delJpb4 		 			 = qDelete('cppmod_pbb_dbkb_jpb4', 'CPM_THN_DBKB_JPB4');
	$delJpb5 		 			 = qDelete('cppmod_pbb_dbkb_jpb5', 'CPM_THN_DBKB_JPB5');
	$delJpb6 		 			 = qDelete('cppmod_pbb_dbkb_jpb6', 'CPM_THN_DBKB_JPB6');
	$delJpb7 		 			 = qDelete('cppmod_pbb_dbkb_jpb7', 'CPM_THN_DBKB_JPB7');
	$delJpb8 		 			 = qDelete('cppmod_pbb_dbkb_jpb8', 'CPM_THN_DBKB_JPB8');
	$delJpb9 		 			 = qDelete('cppmod_pbb_dbkb_jpb9', 'CPM_THN_DBKB_JPB9');
	$delMaterial	 			 = qDelete('cppmod_pbb_dbkb_material', 'CPM_THN_DBKB_MATERIAL');
	$delMezanin	 			 	 = qDelete('cppmod_pbb_dbkb_mezanin', 'CPM_THN_DBKB_MEZANIN');
	$delStandard				 = qDelete('cppmod_pbb_dbkb_standard', 'CPM_THN_DBKB_STANDARD');
	$delFas_dep_jpb_kls_bintang  = qDelete('cppmod_pbb_fas_dep_jpb_kls_bintang', 'THN_DEP_JPB_KLS_BINTANG');
	$delFas_dep_min_max		 	 = qDelete('cppmod_pbb_fas_dep_min_max', 'THN_DEP_MIN_MAX');
	$delFas_non_dep			 	 = qDelete('cppmod_pbb_fas_non_dep', 'THN_NON_DEP');
	$delKegiatan_harga			 = qDelete('cppmod_pbb_kegiatan_harga', 'CPM_TAHUN');
	$delHrg_kegiatan_jpb8		 = qDelete('cppmod_pbb_hrg_kegiatan_jpb8', 'CPM_THN_HRG_PEKERJAAN_JPB8');
	$delResource_harga			 = qDelete('cppmod_pbb_resource_harga', 'CPM_TAHUN');
	$delHarga_satuan			 = qDelete('cppmod_pbb_harga_satuan', 'CPM_TAHUN');
	$delKayu_ulin				 = qDelete('cppmod_pbb_kayu_ulin', 'THN_STATUS_KAYU_ULIN');


	if (($delDayaDukung == true) && ($delJPB12 == true) && ($delJPB13 == true) && ($delJPB14 == true) && ($delJPB15 == true) && ($delJPB16 == true)
		&& ($delJPB2 == true) && ($delJPB3 == true) && ($delJPB4 == true) && ($delJPB5 == true) && ($delJPB6 == true) && ($delJPB7 == true)
		&& ($delJPB8 == true) && ($delJPB9 == true) && ($delMaterial == true) && ($delMezanin == true) && ($delStandard == true)
		&& ($delFasDepJPBKlsBintang == true) && ($delFasDepMinMax == true) && ($delFasNonDep == true) && ($delHargaSatuan == true)
		&& ($delHrgKegiatanJPB8 == true) && ($delKayuUlin == true) && ($delKegiatanHarga == true)
	) {
		return true;
	} else {
		return false;
	}
}

function qDelete($tabel, $fieldTahun)
{
	global $DBLink, $tahun;

	$qDelete = "DELETE FROM " . $tabel . " WHERE " . $fieldTahun . " = '" . $tahun . "' ";
	$res = mysqli_query($DBLink, $qDelete);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	} else
		return true;
}

function existData($tabel, $fieldTahun)
{
	global $DBLink, $tahun;

	$qCheck = "SELECT * FROM " . $tabel . " WHERE " . $fieldTahun . " = '" . $tahun . "' ";
	$res = mysqli_query($DBLink, $qCheck);
	if ($res === false) {
		echo mysqli_error($DBLink);
		exit();
	}

	$row = mysqli_num_rows($res);
	if ($row > 0)
		return true;
	else
		return false;
}

function insertDayaDukung()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_daya_dukung SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_TYPE_KONSTRUKSI,
					CPM_NILAI_DBKB_DAYA_DUKUNG
				FROM
					cppmod_pbb_dbkb_daya_dukung
				WHERE
					CPM_THN_DBKB_DAYA_DUKUNG = '" . ($tahun - 1) . "' ORDER BY CPM_TYPE_KONSTRUKSI ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB12()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb12 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_TYPE_DBKB_JPB12,
					CPM_NILAI_DBKB_JPB12
				FROM
					cppmod_pbb_dbkb_jpb12
				WHERE
					CPM_THN_DBKB_JPB12 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB13()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb13 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB13,
					CPM_LANTAI_MIN_JPB13,
					CPM_LANTAI_MAX_JPB13,
					CPM_NILAI_DBKB_JPB13
				FROM
					cppmod_pbb_dbkb_jpb13
				WHERE
					CPM_THN_DBKB_JPB13 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB14()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb14 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_NILAI_DBKB_JPB14
				FROM
					cppmod_pbb_dbkb_jpb14
				WHERE
					CPM_THN_DBKB_JPB14 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB15()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb15 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_JNS_TANGKI_DBKB_JPB15,
					CPM_KAPASITAS_MIN_DBKB_JPB15,
					CPM_KAPASITAS_MAX_DBKB_JPB15,
					CPM_NILAI_DBKB_JPB15
				FROM
					cppmod_pbb_dbkb_jpb15
				WHERE
					CPM_THN_DBKB_JPB15 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB16()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb16 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB16,
					CPM_LANTAI_MIN_JPB16,
					CPM_LANTAI_MAX_JPB16,
					CPM_NILAI_DBKB_JPB16
				FROM
					cppmod_pbb_dbkb_jpb16
				WHERE
					CPM_THN_DBKB_JPB16 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB2()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb2 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB2,
					CPM_LANTAI_MIN_JPB2,
					CPM_LANTAI_MAX_JPB2,
					CPM_NILAI_DBKB_JPB2
				FROM
					cppmod_pbb_dbkb_jpb2
				WHERE
					CPM_THN_DBKB_JPB2 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB3()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb3 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_LBR_BENT_MIN_DBKB_JPB3,
					CPM_LBR_BENT_MAX_DBKB_JPB3,
					CPM_TINGGI_KOLOM_MIN_DBKB_JPB3,
					CPM_TINGGI_KOLOM_MAX_DBKB_JPB3,
					CPM_NILAI_DBKB_JPB3
				FROM
					cppmod_pbb_dbkb_jpb3
				WHERE
					CPM_THN_DBKB_JPB3 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB4()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb4 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB4,
					CPM_LANTAI_MIN_DBKB_JPB4,
					CPM_LANTAI_MAX_DBKB_JPB4,
					CPM_NILAI_DBKB_JPB4
				FROM
					cppmod_pbb_dbkb_jpb4
				WHERE
					CPM_THN_DBKB_JPB4 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB5()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb5 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB5,
					CPM_LANTAI_MIN_JPB5,
					CPM_LANTAI_MAX_JPB5,
					CPM_NILAI_DBKB_JPB5
				FROM
					cppmod_pbb_dbkb_jpb5
				WHERE
					CPM_THN_DBKB_JPB5 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB6()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb6 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB6,
					CPM_NILAI_DBKB_JPB6
				FROM
					cppmod_pbb_dbkb_jpb6
				WHERE
					CPM_THN_DBKB_JPB6 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB7()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb7 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_JNS_DBKB_JPB7,
					CPM_BINTANG_DBKB_JPB7,
					CPM_LANTAI_MIN_JPB7,
					CPM_LANTAI_MAX_JPB7,
					CPM_NILAI_DBKB_JPB7
				FROM
					cppmod_pbb_dbkb_jpb7
				WHERE
					CPM_THN_DBKB_JPB7 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB8()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb8 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_LBR_BENT_MIN_DBKB_JPB8,
					CPM_LBR_BENT_MAX_DBKB_JPB8,
					CPM_TINGGI_KOLOM_MIN_DBKB_JPB8,
					CPM_TINGGI_KOLOM_MAX_DBKB_JPB8,
					CPM_NILAI_DBKB_JPB8
				FROM
					cppmod_pbb_dbkb_jpb8
				WHERE
					CPM_THN_DBKB_JPB8 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertJPB9()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_jpb9 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KLS_DBKB_JPB9,
					CPM_LANTAI_MIN_JPB9,
					CPM_LANTAI_MAX_JPB9,
					CPM_NILAI_DBKB_JPB9
				FROM
					cppmod_pbb_dbkb_jpb9
				WHERE
					CPM_THN_DBKB_JPB9 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertMaterial()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_material SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KD_PEKERJAAN,
					CPM_KD_KEGIATAN,
					CPM_NILAI_DBKB_MATERIAL
				FROM
					cppmod_pbb_dbkb_material
				WHERE
					CPM_THN_DBKB_MATERIAL = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertMezanin()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_mezanin SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_NILAI_DBKB_MEZANIN
				FROM
					cppmod_pbb_dbkb_mezanin
				WHERE
					CPM_THN_DBKB_MEZANIN = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertStandard()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_dbkb_standard SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KD_JPB,
					CPM_TIPE_BNG,
					CPM_KD_BNG_LANTAI,
					CPM_NILAI_DBKB_STANDARD
				FROM
					cppmod_pbb_dbkb_standard
				WHERE
					CPM_THN_DBKB_STANDARD = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertFasDepJPBKlsBintang()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_fas_dep_jpb_kls_bintang SELECT
					KD_PROPINSI,
					KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					KD_FASILITAS,
					KD_JPB,
					KLS_BINTANG,
					NILAI_FASILITAS_KLS_BINTANG
				FROM
					cppmod_pbb_fas_dep_jpb_kls_bintang
				WHERE
					THN_DEP_JPB_KLS_BINTANG = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertFasDepMinMax()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_fas_dep_min_max SELECT
					KD_PROPINSI,
					KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					KD_FASILITAS,
					KLS_DEP_MIN,
					KLS_DEP_MAX,
					NILAI_DEP_MIN_MAX
				FROM
					cppmod_pbb_fas_dep_min_max
				WHERE
					THN_DEP_MIN_MAX = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertFasNonDep()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_fas_non_dep SELECT
					KD_PROPINSI,
					KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					KD_FASILITAS,
					NILAI_NON_DEP
				FROM
					cppmod_pbb_fas_non_dep
				WHERE
					THN_NON_DEP = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertHargaSatuan()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_harga_satuan SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KODE_PEKERJAAN,
					CPM_KODE_KEGIATAN,
					CPM_HARGA
				FROM
					cppmod_pbb_harga_satuan
				WHERE
					CPM_TAHUN = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertHrgKegiatanJPB8()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_hrg_kegiatan_jpb8 SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KD_PEKERJAAN,
					CPM_KD_KEGIATAN,
					CPM_LBR_BENT_MIN_HRG_JPB8,
					CPM_LBR_BENT_MAX_HRG_JPB8,
					CPM_TING_KOLOM_MIN_HRG_JPB8,
					CPM_TING_KOLOM_MAX_HRG_JPB8,
					CPM_HRG_KEGIATAN_JPB8
				FROM
					cppmod_pbb_hrg_kegiatan_jpb8
				WHERE
					CPM_THN_HRG_PEKERJAAN_JPB8 = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertKayuUlin()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_kayu_ulin SELECT
					KD_PROPINSI,
					KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					STATUS_KAYU_ULIN
				FROM
					cppmod_pbb_kayu_ulin
				WHERE
					THN_STATUS_KAYU_ULIN = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertKegiatanHarga()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_kegiatan_harga SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KODE_JPB,
					CPM_TIPE_BNG,
					CPM_KODE_BNG_LANTAI,
					CPM_KODE_PEKERJAAN,
					CPM_KODE_KEGIATAN,
					CPM_HARGA
				FROM
					cppmod_pbb_kegiatan_harga
				WHERE
					CPM_TAHUN = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}

function insertResourceHarga()
{
	global $DBLink, $tahun;

	$qInsert = "INSERT INTO cppmod_pbb_resource_harga SELECT
					CPM_KD_PROPINSI,
					CPM_KD_DATI2,
					'" . $tahun . "' AS TAHUN,
					CPM_KODE_GROUP,
					CPM_KODE_RESOURCE,
					CPM_HARGA,
					CPM_STATUS
				FROM
					cppmod_pbb_resource_harga
				WHERE
					CPM_TAHUN = '" . ($tahun - 1) . "' ";
	$res = mysqli_query($DBLink, $qInsert);
	if ($res === false) {
		// echo $qInsert;
		// echo mysqli_error($DBLink);
		return false;
	} else
		return $res;
}
