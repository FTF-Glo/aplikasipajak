<?php
//var_dump($arConfig);
// prevent direct access
// ini_set("display_errors", 1); error_reporting(E_ALL);
ini_set('memory_limit', '500M');
ini_set("max_execution_time", "100000");

if (!isset($data)) {
	return;
}

$uid = $data->uid;

// get module
$bOK = $User->GetModuleInArea($uid, $area, $moduleIds);

//prevent access to not accessible module
if (!$bOK) {
	return false;
}

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'PBB', '', dirname(__FILE__))) . '/';
require_once($sRootPath . "inc/payment/uuid.php");
require_once($sRootPath . "inc/PBB/dbSppt.php");
require_once($sRootPath . "inc/PBB/dbSpptExt.php");
require_once($sRootPath . "inc/PBB/dbSpptTran.php");
require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
require_once($sRootPath . "inc/PBB/dbSpptHistory.php");
require_once($sRootPath . "inc/PBB/dbUtils.php");
require_once($sRootPath . "inc/PBB/dbWajibPajak.php");
require_once($sRootPath . "function/PBB/gwlink.php");
require_once($sRootPath . "inc/PBB/dbServices.php");

$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$dbSppt = new DbSppt($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbGwCurrent = new DbGwCurrent($dbSpec);
$dbSpptHistory = new DbSpptHistory($dbSpec);
$dbFinalSppt = new DbFinalSppt($dbSpec);
$dbUtils = new DbUtils($dbSpec);
$dbWajibPajak = new DbWajibPajak($dbSpec);
$dbServices = new DbServices($dbSpec);

// Get User Area Config
$userArea = $dbUtils->getUserDetailPbb($uid);

if ($userArea == null) {
	echo "Aplikasi tidak dapat digunakan karena anda tidak terdaftar sebagai user PBB pada area manapun";
	return false;
} else {
	$userArea = $userArea[0];
}
// print_r($_REQUEST);

//bila diakses pada masa non susulan, maka periksa agar tidak ada data susulan. Kalau ada, pindahkan dulu ke data final
/*if (date('n') < $appConfig['susulan_start'] || $appConfig['susulan_end'] < date('n')) {
	$aSPPTSusulan = $dbFinalSppt->getSusulan();
	// echo "test"; exit;
	if ($aSPPTSusulan != null && count($aSPPTSusulan) > 0) {
		//masih ada data susulan. Pindahkan ke final
		foreach ($aSPPTSusulan as $SPPTSusulan) {

			//cek DOC ID dan DOC VERSION tersebut ada atau tidak di final
			if (isset($SPPTSusulan['CPM_SPPT_DOC_ID']) && isset($SPPTSusulan['CPM_SPPT_DOC_VERSION']) && $dbFinalSppt->isExist($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION'])) {
				//ada data di final. Hapus dulu yang difinal
				$dbFinalSppt->del($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
				$dbFinalSppt->delExt($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
			}

			$bOKMove = true;
			//pindahkan data yang di SPPT
			if (isset($SPPTSusulan['CPM_SPPT_DOC_ID']) && isset($SPPTSusulan['CPM_SPPT_DOC_VERSION'])) {
				$bOKMove &= $dbFinalSppt->move($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
			}

			//pindahkan data yang di SPPT_EXT
			if (isset($SPPTSusulan['CPM_SPPT_DOC_ID']) && isset($SPPTSusulan['CPM_SPPT_DOC_VERSION'])) {
				$bOKMove &= $dbFinalSppt->moveExt($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
			}

			if ($bOKMove && isset($SPPTSusulan['CPM_SPPT_DOC_ID']) && isset($SPPTSusulan['CPM_SPPT_DOC_VERSION'])) {
				//echo "sini 5<br>";
				$dbFinalSppt->delSusulan($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
				$dbFinalSppt->delSusulanExt($SPPTSusulan['CPM_SPPT_DOC_ID'], $SPPTSusulan['CPM_SPPT_DOC_VERSION']);
			}
		}
	}
}*/

// Periksa availability modul apabila file ini diakses sebagai penetapan ataupun susulan (disesuaikan dengan tanggal berlaku)
/*if ($module == $appConfig['id_mdl_susulan']) {
	//cek tanggal
	if (date('n') < $appConfig['susulan_start'] || $appConfig['susulan_end'] < date('n')) {
		$nama_bulan = array("dummy", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		echo "<div style=\"padding: 5 .7em; margin: .5em\" class=\"ui-state-error ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>PERHATIAN: </strong>\n
		Modul Penetapan Susulan hanya bisa diakses antara bulan " . $nama_bulan[$appConfig['susulan_start']] . " hingga bulan " . $nama_bulan[$appConfig['susulan_end']] . " <br>
               <a href='main.php?param=" . base64_encode("a=$application&m=" . $appConfig['id_mdl_penetapan']) . "'>Klik disini untuk menuju Modul Penetapan</a></div>";

		return false;
	} else {
		echo "<div style=\"padding: 5 .7em; margin: .2em\" class=\"ui-state-highlight ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>Info: </strong>\n
		Pastikan memeriksa tanggal penetapan sebelum memproses penetapan!</div>";
	}
} else if ($module == $appConfig['id_mdl_penetapan']) {
	//cek tanggal
	if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
		$nama_bulan = array("dummy", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

		echo "<div style=\"padding: 5 .7em; margin: .5em\" class=\"ui-state-error ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>PERHATIAN: </strong>\n
		Modul Penetapan hanya bisa diakses sebelum bulan " . $nama_bulan[$appConfig['susulan_start']] . " dan setelah bulan " . $nama_bulan[$appConfig['susulan_end']] . " <br>
               <a href='main.php?param=" . base64_encode("a=$application&m=" . $appConfig['id_mdl_susulan']) . "'>Klik disini untuk menuju Modul Penetapan SPOP Susulan</a></div>";

		return false;
	} else {
		echo "<div style=\"padding: 5 .7em; margin: .2em\" class=\"ui-state-highlight ui-corner-all\">\n
		<span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: .3em;\"></span>\n
		<strong>Info: </strong>\n
		Pastikan memeriksa tanggal penetapan sebelum memproses penetapan!</div>";
	}
}*/

//Finalizing Part
if (isset($_REQUEST['btn-finalize']) && isset($_REQUEST['check-all'])) {
	$aVal['CPM_TRAN_FLAG'] = 1;
	foreach ($_REQUEST['check-all'] as $id) {
		$lastID = c_uuid();
		$vals 	= $dbSpptTran->get($id);

		$dbSpptTran->edit($id, $aVal);
		if ($appConfig['jumlah_verifikasi'] == 0) {
			$vals[0]['CPM_TRAN_STATUS'] = 4;
		} else {
			$vals[0]['CPM_TRAN_STATUS'] = 1;
		}
		// echo "<pre>";
		// die(var_dump($vals));


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
				$bOK = $dbSpptHistory->goSusulan($lastID);
			} else {
				$bOK = $dbSpptHistory->goFinal($lastID);
			}
		}
	}
	// die(var_dump($_REQUEST));

}

if (isset($_REQUEST['btn-backtoloket']) && isset($_REQUEST['check-all-tertunda'])) {
	foreach ($_REQUEST['check-all-tertunda'] as $id) {
		$today = date("Y-m-d");
		$bVal['CPM_STATUS'] = 0;
		// $bVal['CPM_WHO_RETURN'] = $uid;
		// $bVal['CPM_DATE_RETURN'] = $today;
		$dbServices->editServices($id, $bVal);
	}
}

//$dbFinalSppt->get();
//Penetapan part, oleh pejabat dispenda

if (isset($_REQUEST['btn-penetapan'])) {
	$kecamatan = $_REQUEST['kecamatan'];
	if (!isset($_REQUEST['check-all'])) {
		//Penetapan per Kecamatan
		if ($kecamatan) {
			if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end'])
				$aSPOP = $dbFinalSppt->getSusulan();
			else
				$aSPOP = $dbFinalSppt->get($id = "", $vers = "", $filter = array("CPM_OP_KECAMATAN" => $kecamatan, "CPM_SPPT_THN_PENETAPAN !" => $appConfig['tahun_tagihan']));
		} else {
			//proses apabila tidak seluruh data di centang untuk diproses
			if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end'])
				$aSPOP = $dbFinalSppt->getSusulan();
			else
				$aSPOP = $dbFinalSppt->get($id = "", $vers = "", $filter = array("CPM_SPPT_THN_PENETAPAN !" => $appConfig['tahun_tagihan']));
		}
		foreach ($aSPOP as $tSpop) {
			if ($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']) {
				//proses penetapan untuk data susulan
				$aExt = $dbFinalSppt->getExtSusulan($tSpop['CPM_SPPT_DOC_ID'], $tSpop['CPM_SPPT_DOC_VERSION']);
				$bOK = saveGatewayCurrent($tSpop, $aExt);
				if ($bOK) {
					$bOK = $dbFinalSppt->editSusulan($tSpop['CPM_SPPT_DOC_ID'], $tSpop['CPM_SPPT_DOC_VERSION'], array("CPM_SPPT_THN_PENETAPAN" => $appConfig['tahun_tagihan']));
				}
			} else {
				$aExt = $dbFinalSppt->getExt($tSpop['CPM_SPPT_DOC_ID'], $tSpop['CPM_SPPT_DOC_VERSION']);
				$bOK = saveGatewayCurrent($tSpop, $aExt);
				saveGateWayPBBSPPT($tSpop);
				$bOK = true;

				if ($bOK) {
					$bOK = $dbFinalSppt->edit($tSpop['CPM_SPPT_DOC_ID'], $tSpop['CPM_SPPT_DOC_VERSION'], array("CPM_SPPT_THN_PENETAPAN" => $appConfig['tahun_tagihan']));
					if ($bOK) {
						$dbSpptHistory->goHistory($appConfig['tahun_tagihan'], $tSpop, $aExt);
					}
				}
			}
		}
	}
} else {
	//memproses seluruh NOP karena dicentang semua
	$checkall = null;
	if (isset($_REQUEST['check-all'])) {
		$checkall = $_REQUEST['check-all'];
	}

	if ($checkall != null && count($checkall) > 0) {
		foreach ($_REQUEST['check-all'] as $doc_id) {

			$aSPOP = $dbFinalSppt->getSusulan($doc_id);
			// die($aSPOP);

			$aExt = $dbFinalSppt->getExtSusulan($aSPOP[0]['CPM_SPPT_DOC_ID'], $aSPOP[0]['CPM_SPPT_DOC_VERSION']);

			$bOK = saveGatewayCurrent($aSPOP[0], $aExt);
			// $bOk = false;

			if ($bOK) {
				$bOK = $dbFinalSppt->editSusulan($aSPOP[0]['CPM_SPPT_DOC_ID'], $aSPOP[0]['CPM_SPPT_DOC_VERSION'], array("CPM_SPPT_THN_PENETAPAN" => $appConfig['tahun_tagihan']));
			}
		}
	}
}
//deleting part
if (isset($_REQUEST['btn-delete']) && isset($_REQUEST['check-all'])) {
	foreach ($_REQUEST['check-all'] as $id) {
		$vals = $dbSpptTran->get($id);

		// print_r($vals); exit;

		$refnum = $vals[0]['CPM_TRAN_REFNUM'];
		$docId = $vals[0]['CPM_TRAN_SPPT_DOC_ID'];
		$docVers = $vals[0]['CPM_SPPT_DOC_VERSION'];

		$bOK = $dbSppt->movePBBSPPTToHistory($docId, $docVers);

		if ($bOK) {

			//delete or tran main contain specific refnum
			$bOK = $dbSpptTran->del("", $refnum);

			//delete or doc contain specific docId
			if ($bOK) {

				$valSPPT = $dbSppt->get($docId);

				$bOK = $dbSppt->del($docId);

				//also delete extension
				if ($bOK) {
					$bOK = $dbSpptExt->del($docId);
					if (!$bOK) {
						echo "<div class='error'>Gagal! Terjadi kesalahan saat penghapusan lampiran dokumen</div>";
					}

					if (trim($valSPPT[0]['CPM_NOP_BERSAMA']) != '') {
						$bOK = $dbSppt->delAnggota($valSPPT[0]['CPM_NOP_BERSAMA'], $valSPPT[0]['CPM_NOP']);
					}
				} else {
					echo "<div class='error'>Gagal! Terjadi kesalahan saat penghapusan dokumen</div>";
				}
			} else {
				echo "<div class='error'>Gagal! Terjadi kesalahan saat penghapusan transaksi</div>";
			}
		} else {
			echo "<div class='error'>Gagal! Terjadi kesalahan saat pemindahan data ke histori</div>";
		}
	}
}


function displayMenuPendata()
{  // srch
	global $a, $m, $srch;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'10'}") . "\">Sementara (Draft)</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'5'}") . "\">Tertunda</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'30'}") . "\">Ditolak</a></li>\n";
	echo "\t</ul>\n";
}

#Verifikasi I
function displayMenuKelurahan()
{
	global $a, $m, $srch;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'21'}") . "\">Tertunda</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'31'}") . "\">Ditolak</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'41'}") . "\">Disetujui</a></li>\n";
	echo "\t</ul>\n";
}

#Verifikasi II
function displayMenuDispenda()
{
	global $arConfig, $a, $m, $srch;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'22'}") . "\">Tertunda</a>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'32'}") . "\">Ditolak</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'42'}") . "\">Disetujui</a></li>\n";
	echo "\t</ul>\n";
}

#Verifikasi III)
function displayMenuPenilaian()
{
	global $arConfig, $a, $m, $srch;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'25'}") . "\">Tertunda</a>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'35'}") . "\">Ditolak</a></li>\n";
	echo "\t</ul>\n";
}

function displayMenuPenetapan()
{
	global $arConfig, $a, $m, $srch, $filter;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'24'}") . "\">Tertunda</a>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'50'}") . "\">Telah Ditetapkan</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'60'}") . "\">SPPT</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'65'}") . "\">OP Fasilitas Umum</a></li>\n";
	echo "\t\t<li><a href=\"view/PBB/page-simulasi-ketetapan-cek-nop-tunggakan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'77'}") . "\">Cek NOP Tunggakan</a></li>\n";
	echo "\t</ul>\n";
}

function displayMenuPenetapanMundur()
{
	global $arConfig, $a, $m, $srch, $filter;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'26'}") . "\">Penetapan Mundur</a>\n";
	// echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'20'}") . "\">Dalam Proses</a></li>\n";
	// echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'50'}") . "\">Telah Ditetapkan</a></li>\n";
	// echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'60'}") . "\">SPPT</a></li>\n";
	// echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'65'}") . "\">OP Fasilitas Umum</a></li>\n";
	// echo "\t\t<li><a href=\"view/PBB/page-cek-nop-tunggakan.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'77'}") . "\">Cek NOP Tunggakan</a></li>\n";
	echo "\t</ul>\n";
}

function displayMenuPenilaianMassal()
{
	global $arConfig, $a, $m, $srch, $filter;

	echo "\t<ul>\n";
	echo "\t\t<li><a href=\"view/PBB/page-stimulus.php?q=" . base64_encode("{'a':'$a', 'm':'$m', 's':'80'}") . "\">Daftar Objek Pajak</a>\n";
	echo "\t</ul>\n";
}

?>
<link href="inc/PBB/jquery-tooltip/jquery.tooltip.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-ui-1.8.18.custom/js/jquery-ui-1.8.18.custom.min.js"></script>
<script type="text/javascript" src="inc/PBB/jquery-tooltip/jquery.tooltip.js"></script>
<script type="text/javascript" src="inc/PBB/svc-print.js"></script>
<script type="text/javascript" src="function/PBB/consol/scripts.js"></script>

<script type="text/javascript">
	var userType = '<?php echo $arConfig['usertype']; ?>';
	var page = 1;

	function filKel(sel, sts) {
		var tab = sel;
		//Pendataan
		if (userType == 'consol') {
			if (sel == 10) sel = 0;
			if (sel == 5) sel = 1;
			if (sel == 20) sel = 2;
			if (sel == 30) sel = 3;
		}
		//Verifikasi I	
		if (sel == 21) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 31) sel = 2;
		if (sel == 41) sel = 3;

		//Verifikasi II	
		if (sel == 22) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 32) sel = 2;
		if (sel == 42) sel = 3;

		//Verifikasi III	
		if (sel == 25) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 35) sel = 2;

		//Penetapan
		if (sel == 24) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 50) sel = 2;
		if (sel == 60) sel = 3;
		if (sel == 65) sel = 4;
		if (sel == 77) sel = 5;

		//Penetapan Mundur

		if (sel == 80) sel = 0;
		var kel = sts.value;
		var displayDat = $("#tampilkan_data").val();

		var param = {};
		param.kel = kel;
		param.displayDat = displayDat;

		if (tab != 24 && tab != 20 && tab != 50 && tab != 60 && tab != 65 && tab != 77 && tab != 80 && tab != 26) {

			if ($("#tahun-" + sts.value).size()) param.tahun = $("#tahun-" + sts.value).val();
		}
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: param
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);

	}

	function displayDat(sel, sts) {
		var tab = sel;
		//Pendataan
		if (userType == 'consol') {
			if (sel == 10) sel = 0;
			if (sel == 5) sel = 1;
			if (sel == 20) sel = 2;
			if (sel == 30) sel = 3;
		}
		//Verifikasi I	
		if (sel == 21) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 31) sel = 2;
		if (sel == 41) sel = 3;

		//Verifikasi II	
		if (sel == 22) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 32) sel = 2;
		if (sel == 42) sel = 3;

		//Verifikasi III	
		if (sel == 25) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 35) sel = 2;

		//Penetapan
		if (sel == 24) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 50) sel = 2;
		if (sel == 60) sel = 3;
		if (sel == 65) sel = 4;
		if (sel == 77) sel = 5;

		//Penetapan Mundur

		if (sel == 80) sel = 0;
		var kel = $("#kel").val();
		var displayDat = sts.value;

		var param = {};
		param.kel = kel;
		param.displayDat = displayDat;

		if (tab != 24 && tab != 20 && tab != 50 && tab != 60 && tab != 65 && tab != 77 && tab != 80 && tab != 26) {

			if ($("#tahun-" + sts.value).size()) param.tahun = $("#tahun-" + sts.value).val();
		}
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: param
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);

	}

	// function searchMultiNOP (sel) {
	// //Penetapan
	// if (sel==24) sel = 0;

	// var param = {};
	// param.nop 		 = $("#daftarNOP").val();
	// param.filterType = $("input[name=tipeFilter]:checked").val();

	// $( "#tabsContent" ).tabs( "option", "ajaxOptions", { async: false, data: param } );
	// $( "#tabsContent" ).tabs( "option", "selected", sel );
	// $( "#tabsContent" ).tabs('load', sel); 
	// }

	function iniAngka(evt, x) {
		var charCode = (evt.which) ? evt.which : event.keyCode;
		//alert(charCode);
		if ((charCode >= 48 && charCode <= 57) || charCode == 8 || charCode == 13) {
			return true;
		} else {
			alert("Input hanya boleh angka!");
			return false;
		}
	}

	function searchMultiNOP(sel) {

		if (sel == 24) sel = 0;

		// alert(sel);
		var kel = $("#kelNOP").val();

		var blok1 = $("#blok1").val();
		var nourut1 = $("#nourut1").val();
		var jnsNOP1 = $("#jnsNOP1").val();

		var blok2 = $("#blok2").val();
		var nourut2 = $("#nourut2").val();
		var jnsNOP2 = $("#jnsNOP2").val();

		var nop1 = blok1 + nourut1 + jnsNOP1;
		var nop2 = blok2 + nourut2 + jnsNOP2;
		if (sel != 0) {
			if (blok1 != "" && nourut1 != "" && jnsNOP1 != "" && blok2 != "" && nourut2 != "" && jnsNOP2 != "") {
				if (blok2 >= blok1) {
					var param = {};
					param.nop1 = kel + nop1;
					param.nop2 = kel + nop2;
					param.filterType = $("input[name=tipeFilter]:checked").val();

					$("#tabsContent").tabs("option", "ajaxOptions", {
						async: false,
						data: param
					});
					$("#tabsContent").tabs("option", "selected", sel);
					$("#tabsContent").tabs('load', sel);
				} else {
					alert("Blok kedua tidak boleh lebih kecil dari Blok pertama!");
				}
			} else {
				alert("Mohon isi lengkap!");
			}
		} else {
			var param = {};
			param.nop = $("#daftarNOP").val();
			param.filterType = $("input[name=tipeFilter]:checked").val();
			// param.tahun		 = $("#tahun").val()
			// alert(JSON.stringify(param));
			$("#tabsContent").tabs("option", "ajaxOptions", {
				async: false,
				data: param
			});
			$("#tabsContent").tabs("option", "selected", sel);
			$("#tabsContent").tabs('load', sel);
		}
	}

	function setTabs(sel, sts, np) {
		var tab = sel;
		if (userType == 'consol') {
			//Pendataan
			if (sel == 10) sel = 0;
			if (sel == 5) sel = 1;
			if (sel == 20) sel = 2;
			if (sel == 30) sel = 3;
		}
		//Verfikasi I
		if (sel == 21) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 31) sel = 2;
		if (sel == 41) sel = 3;
		//Verifikasi II
		if (sel == 22) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 32) sel = 2;
		if (sel == 42) sel = 3;
		//Verifikasi III
		if (sel == 25) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 35) sel = 2;
		//Penetapan
		if (sel == 24) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 50) sel = 2;
		if (sel == 60) sel = 3;
		if (sel == 65) sel = 4;
		if (sel == 77) sel = 5;
		//Penilaian Massal
		if (sel == 80) sel = 0;

		var param = {};
		param.srch = $("#srch-" + sts).val();
		if (tab != 24 && tab != 20 && tab != 50 && tab != 60 && tab != 65 && tab != 77 && tab != 80 && tab != 26) {
			if ($("#tahun-" + sts).size()) param.tahun = $("#tahun-" + sts).val();
		}
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: param
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}

	function setPage(sel, sts, np) {
		if (np == 1) page++;
		else page--;
		var tab = sel;
		if (userType == 'consol') {
			//Pendataan
			if (sel == 10) sel = 0;
			if (sel == 5) sel = 1;
			if (sel == 20) sel = 2;
			if (sel == 30) sel = 3;
		}
		//Verfikasi I
		if (sel == 21) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 31) sel = 2;
		if (sel == 41) sel = 3;
		//Verifikasi II
		if (sel == 22) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 32) sel = 2;
		if (sel == 42) sel = 3;
		//Verifikasi III
		if (sel == 25) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 35) sel = 2;
		//Penetapan
		if (sel == 24) sel = 0;
		if (sel == 20) sel = 1;
		if (sel == 50) sel = 2;
		if (sel == 60) sel = 3;
		if (sel == 65) sel = 4;
		if (sel == 77) sel = 5;

		var kel = '';
		//Penilaian Massal
		if (sel == 80) {
			sel = 0;
			kel = $('#kel').val();
		}

		var param = {};
		param.srch = $("#srch-" + sts).val();
		param.page = page;
		param.np = np;
		param.kel = kel;
		param.displayDat = $("#tampilkan_data").val();
		if (tab != 24 && tab != 20 && tab != 50 && tab != 60 && tab != 65 && tab != 77 && tab != 80 && tab != 26) { //80 : Penilaian Massal
			if ($("#tahun-" + sts).size()) param.tahun = $("#tahun-" + sts).val();
		}
		$("#tabsContent").tabs("option", "ajaxOptions", {
			async: false,
			data: param
		});
		$("#tabsContent").tabs("option", "selected", sel);
		$("#tabsContent").tabs('load', sel);
	}

	$(document).ready(function() {
		$("input:submit, input:button").button();
		$("#all-check-button").click(function() {

			$('.check-all').each(function() {
				this.checked = $("#all-check-button").is(':checked');
			});
		});

		$("#tabsContent").tabs({
			load: function(e, ui) {
				$(ui.panel).find(".tab-loading").remove();
			},
			select: function(e, ui) {
				var $panel = $(ui.panel);

				if ($panel.is(":empty")) {
					$panel.append("<div class='tab-loading'><img src=\"image/icon/loadinfo.net.gif\" /> Loading, please wait...</div>")
				}
			}
		});

	});

	function printdata() {
		$("input:checkbox[name='check-all\\[\\]']").each(function() {
			if ($(this).is(":checked")) {
				printCommand('<?php echo $a; ?>', $(this).val());
			}
		});
	}
</script>

<div class="col-md-12">
	<div id="tabsContent">

		<?php
		// ec
		// echo "<pre>";
		// print_r($arConfig);
		// echo "</pre>";
		// var_dump($arConfig);
		if ($arConfig['usertype'] == "consol") {
			displayMenuPendata();
		} else if ($arConfig['usertype'] == "kelurahan") {
			displayMenuKelurahan();
		} else if ($arConfig['usertype'] == "dispenda") {
			displayMenuDispenda();
			#VerIII
		} else if ($arConfig['usertype'] == "dispenda2") {
			displayMenuPenilaian();
		} else if ($arConfig['usertype'] == "pejabatdispenda") {
			displayMenuPenetapan();
		} else if ($arConfig['usertype'] == "pejabatdispenda2") {
			displayMenuPenetapanMundur();
		} else if ($arConfig['usertype'] == "pejabatdispendacetak") {
			displayMenuPencetakan();
		} else if ($arConfig['usertype'] == "dispenda-penilaian") {
			displayMenuPenilaianMassal();
		}

		?>
	</div>
	<div id="load-content">
		<div id="loader">
			<img src="image/icon/loading-big.gif" style="margin-right: auto;margin-left: auto;" />
		</div>
	</div>
	<div id="load-mask"></div>
</div>