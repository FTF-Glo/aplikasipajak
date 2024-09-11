<?php
    $sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pengurangan', '', dirname(__FILE__))).'/';
	require_once($sRootPath . "inc/payment/constant.php");
	require_once($sRootPath . "inc/payment/comm-central.php");
	require_once($sRootPath . "inc/payment/inc-payment-c.php");
	require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
	require_once($sRootPath . "inc/payment/prefs-payment.php");
	require_once($sRootPath . "inc/payment/db-payment.php");
	require_once($sRootPath . "inc/check-session.php");
	require_once($sRootPath . "inc/payment/json.php");
	require_once($sRootPath . "inc/payment/sayit.php");
	require_once($sRootPath . "inc/central/user-central.php");
	require_once($sRootPath . "inc/central/dbspec-central.php");
	require_once($sRootPath . "function/PBB/gwlink.php");
	//require_once($sRootPath . "function/PBB/gwlink2.php");

	require_once($sRootPath . "inc/PBB/dbFinalSppt.php");
	require_once($sRootPath . "inc/PBB/dbServices.php");
	require_once($sRootPath . "inc/PBB/dbGwCurrent.php");
	require_once($sRootPath . "inc/PBB/dbUtils.php");
	
    SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	
	$nop = @isset($_REQUEST['nop']) ? $_REQUEST['nop'] : "";
	$app = @isset($_REQUEST['app']) ? $_REQUEST['app'] : "";
	$thn = @isset($_REQUEST['thn']) ? $_REQUEST['thn'] : "";
	
	$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);
	$appDbLink = $User->GetDbConnectionFromApp($app);
	$appConfig = $User->GetAppConfig($app);
	$dbSpec = new SCANCentralDbSpecific(DEBUG, LOG_DMS_FILENAME, $appDbLink);
	
	$dbFinalSppt = new DbFinalSppt($dbSpec);
	$dbServices = new DbServices($dbSpec);
	$dbGwCurrent = new DbGwCurrent($dbSpec);
	
	$isExistSusulan	= $dbFinalSppt->isNopExistInSusulan($nop);
	$isExistFinal 	= $dbFinalSppt->isNopExist($nop);
	
	if($isExistFinal){
		/* $dbFinalSppt->moveToSusulanExt($nop);
		$dbFinalSppt->moveToSusulan($nop);
		$dbFinalSppt->delFinalExt($nop);
		$dbFinalSppt->delFinal($nop);		
		$dbServices->updateSvcByNop($nop);
		if($dbFinalSppt->moveToSusulan($nop);){
			echo "Berhasil dikirim ke Penetapan!";
		} else {
			echo "Pengiriman gagal!";
		} */
		
		$sOK = $dbServices->updateSvcByNop($nop,$thn);
		if(!$sOK){
			echo "Ada error di fungsi updateSvcByNop()!";
		}
		
		#DELETE DARI TABEL cppmod_pbb_sppt_current-->OK
		$OK2 = $dbGwCurrent->del($nop,$thn);
		if(!$OK2){
			echo "Ada error di fungsi insertToSPPTPengurangan()!";
		}
		#DELETE DARI PBB_SPPT GATEWAY-->OK
		$delOK = delGateWayPBBSPPT($nop,$thn);
		if(!$delOK){
			echo "Ada error di fungsi delGateWayPBBSPPT()!";
		}
		#Cek bulan penetapan
		if($appConfig['susulan_start'] <= date('n') && date('n') <= $appConfig['susulan_end']){
			#Jika tidak dalam periode penetapan (Sususlan)
			#Data di cppmod_pbb_sppt_final dicopy ke SPPT_SUSULAN dan SPPT_EXT_SUSULAN field TAHUN_PENETAPAN diisi 0
			$cOK = $dbFinalSppt->moveToSusulan($nop,$thn);
			if(!$cOK){
				echo "Ada error di fungsi moveToSusulan()!"; 
			}
			$dOK = $dbFinalSppt->moveToSusulanExt($nop,$thn);
			if(!$dOK){
				echo "Ada error di fungsi moveToSusulanExt()!"; 
			} 
			
			$eOK = $dbFinalSppt->editThnSusulan($nop,$thn);
			if(!$eOK){
				echo "Ada error di fungsi editThnSusulan()!"; 
			} 
			$fOK = $dbFinalSppt->delFinalExt($nop,$thn);
			if(!$fOK){
				echo "Ada error di fungsi delFinalExt()!"; 
			}
			$gOK = $dbFinalSppt->delFinal($nop,$thn);
			if(!$gOK){
				echo "Ada error di fungsi delFinal()!";  
			}  
			else {
				echo "Kirim ke Penetapan berhasil!";
			}
		} else {
			#Jika masih dalam periode penetapan
			#Field CPM_SPPT_TAHUN di tabel cppmod_pbb_sppt_final di update menjadi 0
			$bOK = $dbFinalSppt->editFromPersetujuan($nop,$thn);
			if($bOK){
				echo "Ada error di fungsi editFromPersetujuan()!";
			}
		}
	} else if($isExistSusulan){
			$sOK = $dbServices->updateSvcByNop($nop,$thn);
			if(!$sOK){
				echo "Ada error di fungsi updateSvcByNop()!";
			}
			$eOK = $dbFinalSppt->editThnSusulan($nop,$thn);
			if(!$eOK){
				echo "Ada error di fungsi editThnSusulan()!"; 
			} else {
				echo "Kirim ke Penetapan berhasil!";
			}
	} else {
		echo "Tidak ada data di Final atau Susulan!";
	} 
	
	
   
?>