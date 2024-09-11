<?php
$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB', '', dirname(__FILE__))).'/';
require_once($sRootPath . "inc/PBB/dbUtils.php");

$dbUtils = new DbUtils($dbSpec);
//$arConfig = $User->GetModuleConfig($module);
//$appConfig = $User->GetAppConfig($application);
// $GwDbLink = null;

// print_r($appConfig);
/* DEPRECATED
function setLink(&$DBLink) {
	global $sRootPath, $arConfig;
	
	$bOK = false;
	
	$iErrCode = 0;
	$DBLink = NULL;
	$DBConn = NULL;
	SCANPayment_ConnectToDB($DBLink, $DBConn, $arConfig['GwDbHost'], $arConfig['GwDbUser'], $arConfig['GwDbPwd'], $arConfig['GwDbSchema']);
	if ($iErrCode != 0) {
	  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
	  exit(1);
	} else {
		$bOK = true;
	}

	return $bOK;
}

function sqlQuery($query, &$result) {
	global $GwDbLink;

	$bOK = false;
		
	$sQ = $query;
	
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
	if ($result = mysql_query($sQ, $GwDbLink)) {
		$bOK = true;
	} else {
		echo mysqli_error($result);
	}
	
	return $bOK;
}
*/

function saveGatewayCurrent($aValue, $aExt=array()) 
{
	global $dbSpec, $appConfig, $dbUtils;
	
	$bSuccess = false;
	
	$SPPT_PBB_HARUS_DIBAYAR = 0;
	$OP_LUAS_BANGUNAN = 0;
	$OP_NJOP_BANGUNAN = 0;
	$OP_NJOP_BUMI = $aValue['CPM_NJOP_TANAH'] * $aValue['CPM_OT_LUAS'];
	
	//menghitung nilai untuk tanah
	$SPPT_PBB_HARUS_DIBAYAR = ($aValue['CPM_OT_PENILAIAN_TANAH']=="individu") ? $aValue['CPM_OT_PAYMENT_INDIVIDU'] : $aValue['CPM_OT_PAYMENT_SISTEM'];
	
	for ($i=0; $i<count($aExt); $i++) {
		$OP_LUAS_BANGUNAN += $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];
		$OP_NJOP_BANGUNAN += $aExt[$i]['CPM_NJOP_BANGUNAN'] * $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];		
		$SPPT_PBB_HARUS_DIBAYAR += ($aExt[$i]['CPM_PAYMENT_PENILAIAN_BGN']=="individu") ? $aExt[$i]['CPM_PAYMENT_INDIVIDU'] : $aExt[$i]['CPM_PAYMENT_SISTEM'];
	}
	
	$SPPT_TANGGAL_JATUH_TEMPO = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['expired_days']." day"));
	$SPPT_TAHUN_PAJAK = ($appConfig['tahun_tagihan']!="") ? $appConfig['tahun_tagihan'] : date("Y");
	$SPPT_TANGGAL_TERBIT = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['terbit_date']." day"));
	$SPPT_TANGGAL_CETAK = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['cetak_date']." day"));
		
	$OP_NJOP = $OP_NJOP_BANGUNAN+$OP_NJOP_BUMI;
	if ($OP_NJOP >= $appConfig['minimum_njoptkp']) {
		$OP_NJOPTKP = $appConfig['minimum_njoptkp'];
	} else {
		$OP_NJOPTKP = $OP_NJOP;
	}
	$OP_NJKP = $OP_NJOP-$OP_NJOPTKP;
	
	$sQ = "INSERT INTO cppmod_pbb_sppt_current (
			NOP, 
			SPPT_TAHUN_PAJAK, 
			SPPT_TANGGAL_TERBIT, 
			SPPT_TANGGAL_CETAK, 
			SPPT_TANGGAL_JATUH_TEMPO, 
			SPPT_PBB_HARUS_DIBAYAR, 
			WP_NAMA, 
			WP_ALAMAT, 
			WP_RT, 
			WP_RW, 
			WP_KELURAHAN, 
			WP_KECAMATAN, 
			WP_KOTAKAB, 
			WP_KODEPOS,
			WP_NO_HP, 			
			OP_LUAS_BUMI, 
			OP_LUAS_BANGUNAN, 
			OP_KELAS_BUMI, 
			OP_KELAS_BANGUNAN, 
			OP_NJOP_BUMI, 
			OP_NJOP_BANGUNAN, 
			OP_NJOP, 
			OP_NJOPTKP, 
			OP_NJKP, 			
			OP_ALAMAT, 
			OP_RT, 
			OP_RW, 
			OP_KELURAHAN,
			OP_KECAMATAN,
			OP_KOTAKAB,
			OP_KELURAHAN_KODE,
			OP_KECAMATAN_KODE,
			OP_KOTAKAB_KODE
		) VALUES (
			'".$aValue['CPM_NOP']."',
			'".$SPPT_TAHUN_PAJAK."',
			'".$SPPT_TANGGAL_TERBIT."',
			'".$SPPT_TANGGAL_CETAK."',
			'".$SPPT_TANGGAL_JATUH_TEMPO."',
			'".$SPPT_PBB_HARUS_DIBAYAR."',
			'".$aValue['CPM_WP_NAMA']."',
			'".$aValue['CPM_WP_ALAMAT']."',
			'".$aValue['CPM_WP_RT']."',
			'".$aValue['CPM_WP_RW']."',
			'".$dbUtils->getKelurahanNama($aValue['CPM_WP_KELURAHAN'])."',
			'".$dbUtils->getKecamatanNama($aValue['CPM_WP_KECAMATAN'])."',
			'".$dbUtils->getKabkotaNama($aValue['CPM_WP_KOTAKAB'])."',
			'".$aValue['CPM_WP_KODEPOS']."',
			'".$aValue['CPM_WP_NO_HP']."',
			'".$aValue['CPM_OT_LUAS']."',
			'".$OP_LUAS_BANGUNAN."',
			'A34',
			'A20',
			'".$OP_NJOP_BUMI."',
			'".$OP_NJOP_BANGUNAN."',
			'".$OP_NJOP."',
			'".$OP_NJOPTKP."',
			'".$OP_NJKP."',
			'".$aValue['CPM_OP_ALAMAT']." ".$aValue['CPM_OP_NOMOR']."',
			'".$aValue['CPM_OP_RT']."',
			'".$aValue['CPM_OP_RW']."',
			'".$dbUtils->getKelurahanNama($aValue['CPM_OP_KELURAHAN'])."',
			'".$dbUtils->getKecamatanNama($aValue['CPM_OP_KECAMATAN'])."',
			'".$dbUtils->getKabkotaNama($aValue['CPM_OP_KOTAKAB'])."',
			'".$aValue['CPM_OP_KELURAHAN']."',
			'".$aValue['CPM_OP_KECAMATAN']."',
			'".$aValue['CPM_OP_KOTAKAB']."'
		)";
	
	// echo $sQ;exit;
	//saveGateWayPBBSPPT($aValue);
	return $returnString==1? $query : $dbSpec->sqlQuery($sQ, $res);
}

function getGatewayCurrent($filter=array()) {
	global $dbSpec, $dbUtils;

	$query = "SELECT * FROM cppmod_pbb_sppt_current ";
	
	if (count($filter) > 0) {
		$query .="WHERE ";
		$last_key = array_keys($filter);
		$last_key = end($last_key);

		foreach ($filter as $key => $value) {
			$value = mysqli_real_escape_string($this->dbSpec->getDBLink(), trim($value));
			if ($key == "NOP") 
				$query .= " $key = '$value' ";
			else
				$query .= " $key LIKE '%$value%' ";
			if ($key != $last_key) $query .= " AND ";
		}		
	}
	#echo $query;
	return $dbSpec->sqlQueryRow($query, $res);
}

function saveGateWayPBBSPPT($aValue) {
	global $dbSpec,$appConfig,$arConfig, $dbUtils;
		$C_HOST_PORT = $appConfig['GW_DBHOST'];
		$C_USER = $appConfig['GW_DBUSER'];
		$C_PWD = $appConfig['GW_DBPWD'];
		$C_DB = $appConfig['GW_DBNAME'];
		$LDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB) or die(mysqli_error($dcSpec->getDBLink()));
		//mysqli_select_db($C_DB,$LDBLink);
		
	$SPPT_PBB_HARUS_DIBAYAR = 0;
	$OP_LUAS_BANGUNAN = 0;
	$OP_NJOP_BANGUNAN = 0;
	$OP_NJOP_BUMI = $aValue['CPM_NJOP_TANAH'] * $aValue['CPM_OT_LUAS'];
	
	//menghitung nilai untuk tanah
	$SPPT_PBB_HARUS_DIBAYAR = ($aValue['CPM_OT_PENILAIAN_TANAH']=="individu") ? $aValue['CPM_OT_PAYMENT_INDIVIDU'] : $aValue['CPM_OT_PAYMENT_SISTEM'];
	
	for ($i=0; $i<count($aExt); $i++) {
		$OP_LUAS_BANGUNAN += $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];
		$OP_NJOP_BANGUNAN += $aExt[$i]['CPM_NJOP_BANGUNAN'] * $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];		
		$SPPT_PBB_HARUS_DIBAYAR += ($aExt[$i]['CPM_PAYMENT_PENILAIAN_BGN']=="individu") ? $aExt[$i]['CPM_PAYMENT_INDIVIDU'] : $aExt[$i]['CPM_PAYMENT_SISTEM'];
	}
	
	$SPPT_TANGGAL_JATUH_TEMPO = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['expired_days']." day"));
	$SPPT_TAHUN_PAJAK = ($appConfig['tahun_tagihan']!="") ? $appConfig['tahun_tagihan'] : date("Y");
	$SPPT_TANGGAL_TERBIT = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['terbit_date']." day"));
	$SPPT_TANGGAL_CETAK = strftime("%Y-%m-%d",strtotime(date("Y-m-d") . " +".$appConfig['cetak_date']." day"));
		
	$OP_NJOP = $OP_NJOP_BANGUNAN+$OP_NJOP_BUMI;
	if ($OP_NJOP >= $appConfig['minimum_njoptkp']) {
		$OP_NJOPTKP = $appConfig['minimum_njoptkp'];
	} else {
		$OP_NJOPTKP = $OP_NJOP;
	}
	$OP_NJKP = $OP_NJOP-$OP_NJOPTKP;
	
	$query = "INSERT INTO PBB_SPPT (
			NOP, 
			SPPT_TAHUN_PAJAK, 
			SPPT_TANGGAL_TERBIT, 
			SPPT_TANGGAL_CETAK, 
			SPPT_TANGGAL_JATUH_TEMPO, 
			SPPT_PBB_HARUS_DIBAYAR, 
			WP_NAMA, 
			WP_ALAMAT, 
			WP_RT, 
			WP_RW, 
			WP_KELURAHAN, 
			WP_KECAMATAN, 
			WP_KOTAKAB, 
			WP_KODEPOS, 			
			OP_LUAS_BUMI, 
			OP_LUAS_BANGUNAN, 
			OP_KELAS_BUMI, 
			OP_KELAS_BANGUNAN, 
			OP_NJOP_BUMI, 
			OP_NJOP_BANGUNAN, 
			OP_NJOP, 
			OP_NJOPTKP, 
			OP_NJKP, 			
			OP_ALAMAT, 
			OP_RT, 
			OP_RW, 
			OP_KELURAHAN,
			OP_KECAMATAN,
			OP_KOTAKAB,
			OP_KELURAHAN_KODE,
			OP_KECAMATAN_KODE,
			OP_KOTAKAB_KODE
		) VALUES (
			'".$aValue['CPM_NOP']."',
			'".$SPPT_TAHUN_PAJAK."',
			'".$SPPT_TANGGAL_TERBIT."',
			'".$SPPT_TANGGAL_CETAK."',
			'".$SPPT_TANGGAL_JATUH_TEMPO."',
			'".$SPPT_PBB_HARUS_DIBAYAR."',
			'".$aValue['CPM_WP_NAMA']."',
			'".$aValue['CPM_WP_ALAMAT']."',
			'".$aValue['CPM_WP_RT']."',
			'".$aValue['CPM_WP_RW']."',
			'".$dbUtils->getKelurahanNama($aValue['CPM_WP_KELURAHAN'])."',
			'".$dbUtils->getKecamatanNama($aValue['CPM_WP_KECAMATAN'])."',
			'".$dbUtils->getKabkotaNama($aValue['CPM_WP_KOTAKAB'])."',
			'".$aValue['CPM_WP_KODEPOS']."',
			'".$aValue['CPM_OT_LUAS']."',
			'".$OP_LUAS_BANGUNAN."',
			'A34',
			'A20',
			'".$OP_NJOP_BUMI."',
			'".$OP_NJOP_BANGUNAN."',
			'".$OP_NJOP."',
			'".$OP_NJOPTKP."',
			'".$OP_NJKP."',
			'".$aValue['CPM_OP_ALAMAT']." ".$aValue['CPM_OP_NOMOR']."',
			'".$aValue['CPM_OP_RT']."',
			'".$aValue['CPM_OP_RW']."',
			'".$dbUtils->getKelurahanNama($aValue['CPM_OP_KELURAHAN'])."',
			'".$dbUtils->getKecamatanNama($aValue['CPM_OP_KECAMATAN'])."',
			'".$dbUtils->getKabkotaNama($aValue['CPM_OP_KOTAKAB'])."',
			'".$aValue['CPM_OP_KELURAHAN']."',
			'".$aValue['CPM_OP_KECAMATAN']."',
			'".$aValue['CPM_OP_KOTAKAB']."'
		)";
		// echo $query;exit;
		mysqli_query($LDBLink, $query) or die("error fungsi: saveGateWayPBBSPPT() ".mysqli_error($LDBLink));
/*OP_PROVINSI_KODE

PAYMENT_FLAG
PAYMENT_PAID
PAYMENT_REF_NUMBER
PAYMENT_BANK_CODE
PAYMENT_SW_REFNUM
PAYMENT_GW_REFNUM
PAYMENT_SW_ID
PAYMENT_MERCHANT_CODE
PBB_COLLECTIBLE
*/
}

function delGateWayPBBSPPT($nop,$tahun) {
		global $dbSpec,$appConfig,$arConfig, $dbUtils;
				$C_HOST_PORT = $appConfig['GW_DBHOST'];
				$C_USER = $appConfig['GW_DBUSER'];
				$C_PWD = $appConfig['GW_DBPWD'];
				$C_DB = $appConfig['GW_DBNAME'];
				$LDBLink = mysqli_connect($C_HOST_PORT,$C_USER,$C_PWD,$C_DB) or die(mysqli_error($dbSpec->getDBLink()));
				//mysql_select_db($C_DB,$LDBLink);
				
		$nop   = mysqli_real_escape_string($LDBLink, trim($nop));	
		$tahun = mysqli_real_escape_string($LDBLink, trim($tahun));
		
		$query = "DELETE FROM pbb_sppt WHERE NOP='$nop' ";
		if($tahun){
			$query .= "AND SPPT_TAHUN_PAJAK = '$tahun'";
		}
		//echo $query;exit;		
		mysqli_query($LDBLink, $query) or die("error fungsi: delGateWayPBBSPPT() ".mysqli_error($LDBLink));
}

function updateGateWayPBBSPPT($nop, $tahun, $aValue) {
	global $dbSpec,$appConfig,$arConfig, $dbUtils;
		$C_HOST_PORT = $appConfig['GW_DBHOST'];
		$C_USER = $appConfig['GW_DBUSER'];
		$C_PWD = $appConfig['GW_DBPWD'];
		$C_DB = $appConfig['GW_DBNAME'];

		$explodeHostPost = explode(':', $C_HOST_PORT);
		$newHost = $explodeHostPost[0];
		$newPort = (isset($explodeHostPost[1]) && $explodeHostPost[1]) ? $explodeHostPost[1] : '3306';

		$LDBLink = mysqli_connect($newHost,$C_USER,$C_PWD,$C_DB, $newPort) or die(mysqli_error($dbSpec->getDBLink()));
		//mysql_select_db($C_DB,$LDBLink);
		
		//$last_key = end(array_keys($aValue));
		$last_key = array_keys($aValue);
		$last_key = end($last_key);
        $query = "UPDATE pbb_sppt SET ";
		
        foreach ($aValue as $key => $value) {
                $query .= "$key='$value'";
                if ($key != $last_key) {
                        $query .= ", ";
                }
        }

        $query .= " WHERE NOP='$nop' AND SPPT_TAHUN_PAJAK='$tahun'";
		
        mysqli_query($LDBLink, $query) or die("error fungsi: saveGateWayPBBSPPT() ".mysqli_error($LDBLink));

}

function updateGatewayCurrent($nop, $aValue) 
{
	global $dbSpec, $appConfig, $dbUtils;
	
	$bSuccess = false;
	
	//$last_key = end(array_keys($aValue));
	$last_key = array_keys($aValue);
	$last_key = end($last_key);
	$query = "UPDATE cppmod_pbb_sppt_current SET ";

	foreach ($aValue as $key => $value) {
			$query .= "$key='$value'";
			if ($key != $last_key) {
					$query .= ", ";
			}
	}

	$query .= " WHERE NOP='$nop'";
		
	echo $query; exit;
        
	return $returnString==1? $query : $dbSpec->sqlQuery($sQ, $res);
}

function hitung($aValue) {
    
    global $dbSpec, $appConfig, $dbUtils;
    
            
    $NJOPTKP = ($appConfig['minimum_njoptkp']== null ) ? 10000000 :$appConfig['minimum_njoptkp'];
    $minPBBHarusBayar = ($appConfig['minimum_sppt_pbb_terhutang']== null ) ? 25000 :$appConfig['minimum_sppt_pbb_terhutang'];


    $NJOP = $aValue['CPM_NJOP_TANAH'] + $aValue['CPM_NJOP_BANGUNAN'];
	
    if($NJOP > $NJOPTKP)
        $NJKP = $NJOP - $NJOPTKP;
    else $NJKP = 0;
    
    $aValue['OP_NJOP'] = $NJOP;
    $aValue['OP_NJKP'] = $NJKP;
    $aValue['OP_NJOPTKP'] = $NJOPTKP;
    
    $cari_tarif = "select CPM_TRF_TARIF from cppmod_pbb_tarif where
                    CPM_TRF_NILAI_BAWAH <= " . $NJKP . " AND
                    CPM_TRF_NILAI_ATAS >= " . $NJKP; 
    if (!$this->dbSpec->sqlQuery($cari_tarif, $resTarif)) {
        return false;
    }
	
    $dataTarif = mysqli_fetch_array($resTarif);
    $op_tarif = $dataTarif['CPM_TRF_TARIF'];
    $aValue['OP_TARIF'] = $op_tarif;
    $PBB_HARUS_DIBAYAR = $NJKP * ($op_tarif / 100);

    if($PBB_HARUS_DIBAYAR < $minPBBHarusBayar)
        $PBB_HARUS_DIBAYAR = $minPBBHarusBayar;
    $aValue['SPPT_PBB_HARUS_DIBAYAR'] = $PBB_HARUS_DIBAYAR;
    
    return $aValue;
}
?>