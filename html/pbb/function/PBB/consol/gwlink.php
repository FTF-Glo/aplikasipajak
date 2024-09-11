<?php
$arConfig = $User->GetModuleConfig($module);
$appConfig = $User->GetAppConfig($application);
$GwDbLink = null;

// print_r($appConfig);

function setLink(&$DBLink)
{
	global $sRootPath, $arConfig;

	$bOK = false;

	$iErrCode = 0;
	$DBLink = NULL;
	$DBConn = NULL;
	SCANPayment_ConnectToDB($DBLink, $DBConn, $arConfig['GwDbHost'], $arConfig['GwDbUser'], $arConfig['GwDbPwd'], $arConfig['GwDbSchema']);
	if ($iErrCode != 0) {
		$sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
			error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_DMS_FILENAME);
		exit(1);
	} else {
		$bOK = true;
	}

	return $bOK;
}

function sqlQuery($query, &$result)
{
	global $GwDbLink;

	$bOK = false;

	$sQ = $query;

	if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
	if ($result = mysqli_query($GwDbLink, $sQ)) {
		$bOK = true;
	} else {
		echo mysqli_error($GwDbLink);
	}

	return $bOK;
}

function saveGatewayCurrent($aValue, $aExt = array())
{
	global $dbSpec, $appConfig;

	$bSuccess = false;

	$SPPT_PBB_HARUS_DIBAYAR = 0;
	$OP_LUAS_BANGUNAN = 0;
	$OP_NJOP_BANGUNAN = 0;
	$OP_NJOP_BUMI = $aValue['CPM_NJOP_TANAH'] * $aValue['CPM_OT_LUAS'];

	//menghitung nilai untuk tanah
	$SPPT_PBB_HARUS_DIBAYAR = ($aValue['CPM_OT_PENILAIAN_TANAH'] == "individu") ? $aValue['CPM_OT_PAYMENT_INDIVIDU'] : $aValue['CPM_OT_PAYMENT_SISTEM'];

	for ($i = 0; $i < count($aExt); $i++) {
		$OP_LUAS_BANGUNAN += $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];
		$OP_NJOP_BANGUNAN += $aExt[$i]['CPM_NJOP_BANGUNAN'] * $aExt[$i]['CPM_OP_LUAS_BANGUNAN'];
		$SPPT_PBB_HARUS_DIBAYAR += ($aExt[$i]['CPM_PAYMENT_PENILAIAN_BGN'] == "individu") ? $aExt[$i]['CPM_PAYMENT_INDIVIDU'] : $aExt[$i]['CPM_PAYMENT_SISTEM'];
	}

	$SPPT_TANGGAL_JATUH_TEMPO = strftime("%Y-%m-%d", strtotime(date("Y-m-d", strtotime($aValue['CPM_TRAN_DATE'])) . " +" . $appConfig['expired_days'] . " day"));
	$SPPT_TAHUN_PAJAK = ($appConfig['tahun_tagihan'] != "") ? $appConfig['tahun_tagihan'] : date("Y");
	$SPPT_TANGGAL_TERBIT = strftime("%Y-%m-%d", strtotime(date("Y-m-d", strtotime($aValue['CPM_TRAN_DATE'])) . " +" . $appConfig['terbit_date'] . " day"));
	$SPPT_TANGGAL_CETAK = strftime("%Y-%m-%d", strtotime(date("Y-m-d", strtotime($aValue['CPM_TRAN_DATE'])) . " +" . $appConfig['cetak_date'] . " day"));

	/*$OP_NJOP = $OP_NJOP_BANGUNAN+$OP_NJOP_BUMI;
	if ($OP_NJOP >= $appConfig['minimum_njoptkp']) {
		$OP_NJOPTKP = $appConfig['minimum_njoptkp'];
	} else {
		$OP_NJOPTKP = $OP_NJOP;
	}*/

	$query = "SELECT * FROM cppmod_pbb_sppt where CPM_NOP = '" . $aValue['CPM_NOP'] . "'";
	$dbSpec->sqlQueryRow($query, $resu);

	$jum = 0;

	if (count($resu) > 0) {
		$query = "SELECT * FROM cppmod_pbb_sppt where CPM_WP_NO_KTP = '" . $resu[0]['CPM_WP_NO_KTP'] . "'";
		$jum = $dbSpec->sqlQueryRows($query);
	}

	$OP_NJOP = $OP_NJOP_BANGUNAN + $OP_NJOP_BUMI;
	if ($jum >= 10) {
		$OP_NJOPTKP = 0;
	} else {
		if ($OP_NJOP >= $appConfig['minimum_njoptkp']) {
			$OP_NJOPTKP = $appConfig['minimum_njoptkp'];
		} else {
			$OP_NJOPTKP = $OP_NJOP;
		}
	}

	$OP_NJKP = $OP_NJOP - $OP_NJOPTKP;

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
			OP_KELURAHAN
		) VALUES (
			'" . $aValue['CPM_NOP'] . "',
			'" . $SPPT_TAHUN_PAJAK . "',
			'" . $SPPT_TANGGAL_TERBIT . "',
			'" . $SPPT_TANGGAL_CETAK . "',
			'" . $SPPT_TANGGAL_JATUH_TEMPO . "',
			'" . $SPPT_PBB_HARUS_DIBAYAR . "',
			'" . $aValue['CPM_WP_NAMA'] . "',
			'" . $aValue['CPM_WP_ALAMAT'] . "',
			'" . $aValue['CPM_WP_RT'] . "',
			'" . $aValue['CPM_WP_RW'] . "',
			'" . $aValue['CPM_WP_KELURAHAN'] . "',
			'" . $aValue['CPM_WP_KECAMATAN'] . "',
			'" . $aValue['CPM_WP_KOTAKAB'] . "',
			'" . $aValue['CPM_WP_KODEPOS'] . "',
			'" . $aValue['CPM_OT_LUAS'] . "',
			'" . $OP_LUAS_BANGUNAN . "',
			'A34',
			'A20',
			'" . $OP_NJOP_BUMI . "',
			'" . $OP_NJOP_BANGUNAN . "',
			'" . $OP_NJOP . "',
			'" . $OP_NJOPTKP . "',
			'" . $OP_NJKP . "',
			'" . $aValue['CPM_OP_ALAMAT'] . " " . $aValue['CPM_OP_NOMOR'] . "',
			'" . $aValue['CPM_OP_RT'] . "',
			'" . $aValue['CPM_OP_RW'] . "',
			'" . $aValue['CPM_OP_KELURAHAN'] . "'
		)";

	// echo $sQ;

	return $dbSpec->sqlQuery($sQ, $res);
}

function getGatewayCurrent($filter = array())
{
	global $dbSpec;

	$query = "SELECT * FROM cppmod_pbb_sppt_current ";

	if (count($filter) > 0) {
		$query .= "WHERE ";
		$last_key = end(array_keys($filter));

		foreach ($filter as $key => $value) {
			$value = mysql_real_escape_string(trim($value));
			if ($key == "NOP")
				$query .= " $key = '$value' ";
			else
				$query .= " $key LIKE '%$value%' ";
			if ($key != $last_key) $query .= " AND ";
		}
	}
	echo $query;
	return $dbSpec->sqlQueryRow($query, $res);
}
