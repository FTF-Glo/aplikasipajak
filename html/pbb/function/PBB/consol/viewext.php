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

require_once("inc/PBB/dbSppt.php");
require_once("inc/PBB/dbSpptTran.php");
require_once("inc/PBB/dbSpptExt.php");

$arConfig = $User->GetModuleConfig($module);	
$dbSppt = new DbSppt($dbSpec);
$dbSpptTran = new DbSpptTran($dbSpec);
$dbSpptExt = new DbSpptExt($dbSpec);

// echo "<pre>";
// print_r($_REQUEST);
// echo "</pre>";

//Preparing Parameters
if (isset($idt)) {
	$tran = $dbSpptTran->get($idt);
	$idd = $tran[0]['CPM_TRAN_SPPT_DOC_ID'];
	$v = $tran[0]['CPM_SPPT_DOC_VERSION'];
}

if (isset($idd) && isset($v)) {
	$extVal = $dbSpptExt->get($idd, $v, $num);
	foreach ($extVal[0] as $key => $value) {
		$tKey = substr($key,4);
		$$tKey = $value;
	}

	$docVal = $dbSppt->get($idd, $v);
	
	$SPPT_ID = $docVal[0]['CPM_SPPT_ID'];
	$SPPT_TIPE = $docVal[0]['CPM_SPPT_TIPE'];
	$NOP = $docVal[0]['CPM_NOP'];
	$OP_JML_BANGUNAN = $docVal[0]['CPM_OP_JML_BANGUNAN'];
	$JPB = array(
			"-", 
			"Perumahan",
			"Perkantoran Swasta",
			"Pabrik",
			"Toko/Apotik/Pasar/Ruko",
			"Rumah Sakit/Klinik",
			"Olah Raga/Rekreasi",
			"Hotel/Wisma",
			"Bengkel/Gudang/Pertanian",
			"Gedung Pemerintah",
			"Lain-lain",
			"Bangunan Tidak Kena Pajak",
			"Bangunan Parkir",
			"Apartemen",
			"Pompa Bensin",
			"Tangki Minyak",
			"Gedung Sekolah");
}

echo "<link rel=\"stylesheet\" href=\"function/PBB/viewspop.css\" type=\"text/css\">";
include("exttmp.php");


?>