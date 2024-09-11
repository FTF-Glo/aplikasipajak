<?php  

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR . 'function' . DIRECTORY_SEPARATOR . 'PBB' . DIRECTORY_SEPARATOR . 'penilaian_bangunan', '', dirname(__FILE__))) . '/';

require_once($sRootPath . "inc/payment/ctools.php");
require_once($sRootPath . "inc/payment/constant.php");
require_once($sRootPath . "inc/payment/comm-central.php");
require_once($sRootPath . "inc/payment/inc-payment-c.php");
require_once($sRootPath . "inc/payment/inc-payment-db-c.php");
require_once($sRootPath . "inc/payment/prefs-payment.php");
require_once($sRootPath . "inc/payment/db-payment.php");
require_once($sRootPath . "inc/check-session.php");
require_once($sRootPath . "inc/payment/json.php");
require_once($sRootPath . "inc/payment/sayit.php");
require_once($sRootPath . "inc/central/setting-central.php");
require_once($sRootPath . "inc/central/user-central.php");
require_once($sRootPath . "inc/central/dbspec-central.php");
require_once($sRootPath . "inc/payment/uuid.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$setting 	= new SCANCentralSetting (0,LOG_FILENAME,$DBLink);

$tab  		= @isset($_REQUEST['tab']) ? $_REQUEST['tab'] :'';
$tahun 		= @isset($_REQUEST['tahun']) ? $_REQUEST['tahun'] :'';
$vACSplit	= @isset($_REQUEST['vACSplit']) ? $_REQUEST['vACSplit'] :'0';
$vACWindow      = @isset($_REQUEST['vACWindow']) ? $_REQUEST['vACWindow'] :'0';
$vKantor1	= @isset($_REQUEST['vKantor1']) ? $_REQUEST['vKantor1'] :'0';
$vKantor2	= @isset($_REQUEST['vKantor2']) ? $_REQUEST['vKantor2'] :'0';
$vHotel1	= @isset($_REQUEST['vHotel1']) ? $_REQUEST['vHotel1'] :'0';
$vHotel2	= @isset($_REQUEST['vHotel2']) ? $_REQUEST['vHotel2'] :'0';
$vHotel3	= @isset($_REQUEST['vHotel3']) ? $_REQUEST['vHotel3'] :'0';
$vHotel4	= @isset($_REQUEST['vHotel4']) ? $_REQUEST['vHotel4'] :'0';
$vToko1		= @isset($_REQUEST['vToko1']) ? $_REQUEST['vToko1'] :'0';
$vToko2		= @isset($_REQUEST['vToko2']) ? $_REQUEST['vToko2'] :'0';
$vToko3		= @isset($_REQUEST['vToko3']) ? $_REQUEST['vToko3'] :'0';
$vRS1		= @isset($_REQUEST['vRS1']) ? $_REQUEST['vRS1'] :'0';
$vRS2		= @isset($_REQUEST['vRS2']) ? $_REQUEST['vRS2'] :'0';
$vRS3		= @isset($_REQUEST['vRS3']) ? $_REQUEST['vRS3'] :'0';
$vApt1		= @isset($_REQUEST['vApt1']) ? $_REQUEST['vApt1'] :'0';
$vApt2		= @isset($_REQUEST['vApt2']) ? $_REQUEST['vApt2'] :'0';
$vBngLain	= @isset($_REQUEST['vBngLain']) ? $_REQUEST['vBngLain'] :'0';

$vKlmRenang1	= @isset($_REQUEST['vKlmRenang1']) ? $_REQUEST['vKlmRenang1'] :'0';
$vKlmRenang2	= @isset($_REQUEST['vKlmRenang2']) ? $_REQUEST['vKlmRenang2'] :'0';
$vKlmRenang3	= @isset($_REQUEST['vKlmRenang3']) ? $_REQUEST['vKlmRenang3'] :'0';
$vKlmRenang4	= @isset($_REQUEST['vKlmRenang4']) ? $_REQUEST['vKlmRenang4'] :'0';
$vKlmRenang5	= @isset($_REQUEST['vKlmRenang5']) ? $_REQUEST['vKlmRenang5'] :'0';
$vKlmRenang6	= @isset($_REQUEST['vKlmRenang6']) ? $_REQUEST['vKlmRenang6'] :'0';
$vKlmRenang7	= @isset($_REQUEST['vKlmRenang7']) ? $_REQUEST['vKlmRenang7'] :'0';
$vKlmRenang8	= @isset($_REQUEST['vKlmRenang8']) ? $_REQUEST['vKlmRenang8'] :'0';
$vKlmRenang9	= @isset($_REQUEST['vKlmRenang9']) ? $_REQUEST['vKlmRenang9'] :'0';
$vKlmRenang10	= @isset($_REQUEST['vKlmRenang10']) ? $_REQUEST['vKlmRenang10'] :'0';

$vPerkerasan1	= @isset($_REQUEST['vPerkerasan1']) ? $_REQUEST['vPerkerasan1'] :'0';
$vPerkerasan2	= @isset($_REQUEST['vPerkerasan2']) ? $_REQUEST['vPerkerasan2'] :'0';
$vPerkerasan3	= @isset($_REQUEST['vPerkerasan3']) ? $_REQUEST['vPerkerasan3'] :'0';
$vPerkerasan4	= @isset($_REQUEST['vPerkerasan4']) ? $_REQUEST['vPerkerasan4'] :'0';

$vlapTenis1	= @isset($_REQUEST['vlapTenis1']) ? $_REQUEST['vlapTenis1'] :'0';
$vlapTenis2	= @isset($_REQUEST['vlapTenis2']) ? $_REQUEST['vlapTenis2'] :'0';
$vlapTenis3	= @isset($_REQUEST['vlapTenis3']) ? $_REQUEST['vlapTenis3'] :'0';
$vlapTenis4	= @isset($_REQUEST['vlapTenis4']) ? $_REQUEST['vlapTenis4'] :'0';
$vlapTenis5	= @isset($_REQUEST['vlapTenis5']) ? $_REQUEST['vlapTenis5'] :'0';
$vlapTenis6	= @isset($_REQUEST['vlapTenis6']) ? $_REQUEST['vlapTenis6'] :'0';
$vlapTenis7	= @isset($_REQUEST['vlapTenis7']) ? $_REQUEST['vlapTenis7'] :'0';
$vlapTenis8	= @isset($_REQUEST['vlapTenis8']) ? $_REQUEST['vlapTenis8'] :'0';
$vlapTenis9	= @isset($_REQUEST['vlapTenis9']) ? $_REQUEST['vlapTenis9'] :'0';
$vlapTenis10	= @isset($_REQUEST['vlapTenis10']) ? $_REQUEST['vlapTenis10'] :'0';
$vlapTenis11	= @isset($_REQUEST['vlapTenis11']) ? $_REQUEST['vlapTenis11'] :'0';
$vlapTenis12	= @isset($_REQUEST['vlapTenis12']) ? $_REQUEST['vlapTenis12'] :'0';

$vlift1         = @isset($_REQUEST['vlift1']) ? $_REQUEST['vlift1'] :'0';
$vlift2         = @isset($_REQUEST['vlift2']) ? $_REQUEST['vlift2'] :'0';
$vlift3         = @isset($_REQUEST['vlift3']) ? $_REQUEST['vlift3'] :'0';
$vlift4         = @isset($_REQUEST['vlift4']) ? $_REQUEST['vlift4'] :'0';
$vlift5         = @isset($_REQUEST['vlift5']) ? $_REQUEST['vlift5'] :'0';
$vlift6         = @isset($_REQUEST['vlift6']) ? $_REQUEST['vlift6'] :'0';
$vlift7         = @isset($_REQUEST['vlift7']) ? $_REQUEST['vlift7'] :'0';
$vlift8         = @isset($_REQUEST['vlift8']) ? $_REQUEST['vlift8'] :'0';
$vlift9         = @isset($_REQUEST['vlift9']) ? $_REQUEST['vlift9'] :'0';
$vlift10	= @isset($_REQUEST['vlift10']) ? $_REQUEST['vlift10'] :'0';
$vlift11	= @isset($_REQUEST['vlift11']) ? $_REQUEST['vlift11'] :'0';
$vlift12	= @isset($_REQUEST['vlift12']) ? $_REQUEST['vlift12'] :'0';

$vEsc1          = @isset($_REQUEST['vEsc1']) ? $_REQUEST['vEsc1'] :'0';
$vEsc2          = @isset($_REQUEST['vEsc2']) ? $_REQUEST['vEsc2'] :'0';

$vpagar1        = @isset($_REQUEST['vpagar1']) ? $_REQUEST['vpagar1'] :'0';
$vpagar2        = @isset($_REQUEST['vpagar2']) ? $_REQUEST['vpagar2'] :'0';

$vProtApi1      = @isset($_REQUEST['vProtApi1']) ? $_REQUEST['vProtApi1'] :'0';
$vProtApi2      = @isset($_REQUEST['vProtApi2']) ? $_REQUEST['vProtApi2'] :'0';
$vProtApi3      = @isset($_REQUEST['vProtApi3']) ? $_REQUEST['vProtApi3'] :'0';

$vGenset1       = @isset($_REQUEST['vGenset1']) ? $_REQUEST['vGenset1'] :'0';
$vGenset2       = @isset($_REQUEST['vGenset2']) ? $_REQUEST['vGenset2'] :'0';
$vGenset3       = @isset($_REQUEST['vGenset3']) ? $_REQUEST['vGenset3'] :'0';
$vGenset4       = @isset($_REQUEST['vGenset4']) ? $_REQUEST['vGenset4'] :'0';

$vPABX          = @isset($_REQUEST['vPABX']) ? $_REQUEST['vPABX'] :'0';

$vAirArt        = @isset($_REQUEST['vAirArt']) ? $_REQUEST['vAirArt'] :'0';

$vboihotel1     = @isset($_REQUEST['vboihotel1']) ? $_REQUEST['vboihotel1'] :'0';
$vboihotel2     = @isset($_REQUEST['vboihotel2']) ? $_REQUEST['vboihotel2'] :'0';
$vboihotel3     = @isset($_REQUEST['vboihotel3']) ? $_REQUEST['vboihotel3'] :'0';
$vboiapart1     = @isset($_REQUEST['vboiapart1']) ? $_REQUEST['vboiapart1'] :'0';
$vboiapart2     = @isset($_REQUEST['vboiapart2']) ? $_REQUEST['vboiapart2'] :'0';

$vListrik       = @isset($_REQUEST['vListrik']) ? $_REQUEST['vListrik'] :'0';
// echo $tahun; exit;
// print_r($_REQUEST);exit;

function updateNilaiFasNonDep($kode,$nilai){
	global $DBLink, $tahun;
	
	$query = "UPDATE cppmod_pbb_fas_non_dep SET NILAI_NON_DEP = '{$nilai}' WHERE THN_NON_DEP = '{$tahun}' AND KD_FASILITAS = '{$kode}' ";
	// echo $query; exit;
	$result = mysqli_query($DBLink, $query);
                
    if (!$result) {
        return false;
    }
		return true;
}

function updateNilaiFasDepKlsBintang($kode,$kd_jpb,$kls_bintang,$nilai){
	global $DBLink, $tahun;
	
	$query = "UPDATE cppmod_pbb_fas_dep_jpb_kls_bintang SET NILAI_FASILITAS_KLS_BINTANG = '{$nilai}' WHERE THN_DEP_JPB_KLS_BINTANG = '{$tahun}' AND KD_FASILITAS = '{$kode}' AND KD_JPB = '{$kd_jpb}' AND KLS_BINTANG = '{$kls_bintang}' ";
	// echo $query; exit;
	$result = mysqli_query($DBLink, $query);
                
    if (!$result) {
        return false;
    }
		return true;
}

function updateNilaiFasDepMinMax($kode,$min,$max,$nilai){
	global $DBLink, $tahun;
	
	$query = "UPDATE cppmod_pbb_fas_dep_min_max SET NILAI_DEP_MIN_MAX = '{$nilai}' WHERE THN_DEP_MIN_MAX = '{$tahun}' AND KD_FASILITAS = '{$kode}' AND KLS_DEP_MIN = '{$min}' AND KLS_DEP_MAX = '{$max}' ";
	// echo $query; exit;
	$result = mysqli_query($DBLink, $query);
                
    if (!$result) {
        return false;
    }
		return true;
}

function saveAC(){
	global $tahun,$vACSplit,$vACWindow,$vKantor1,$vKantor2,$vHotel1,$vHotel2,$vHotel3,$vHotel4,$vToko1,$vToko2,$vToko3,$vRS1,$vRS2,$vRS3,$vApt1,$vApt2,$vBngLain;
	
	$bOK = false;

	$bOK = updateNilaiFasNonDep('01',$vACSplit);
	if($bOK){
		$bOK = updateNilaiFasNonDep('02',$vACWindow);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('03','02','1',$vKantor1);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('03','02','3',$vKantor2);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('04','07','3',$vHotel1);
	} 
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('04','07','4',$vHotel2);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('05','07','3',$vHotel3);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('05','07','4',$vHotel4);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('06','04','1',$vToko1);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('06','04','2',$vToko2);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('06','04','3',$vToko3);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('07','05','1',$vRS1);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('07','05','3',$vRS2);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('08','05','1',$vRS3);
	} 
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('09','13','1',$vApt1);
	} 
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('10','13','1',$vApt2);
	} 
	$bOK = updateNilaiFasNonDep('11',$vBngLain);
	
	return $bOK;
}

function saveKlmRenang(){
	global $vKlmRenang1,$vKlmRenang2,$vKlmRenang3,$vKlmRenang4,$vKlmRenang5,$vKlmRenang6,$vKlmRenang7,$vKlmRenang8,$vKlmRenang9,$vKlmRenang10;
	
	$bOK = false;
	$bOK = updateNilaiFasDepMinMax('12','0','50',$vKlmRenang1);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('12','51','100',$vKlmRenang2);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('12','101','200',$vKlmRenang3);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('12','201','400',$vKlmRenang4);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('12','401','999999',$vKlmRenang5);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('13','0','50',$vKlmRenang6);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('13','51','100',$vKlmRenang7);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('13','101','200',$vKlmRenang8);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('13','201','400',$vKlmRenang9);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('13','401','999999',$vKlmRenang10);

	
	return $bOK;
}

function savePerkerasan(){
	global $vPerkerasan1,$vPerkerasan2,$vPerkerasan3,$vPerkerasan4;
        
        $bOK = false;
        $bOK = updateNilaiFasNonDep('14',$vPerkerasan1);
	if($bOK){
		$bOK = updateNilaiFasNonDep('15',$vPerkerasan2);
        }
	if($bOK){
		$bOK = updateNilaiFasNonDep('16',$vPerkerasan3);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('17',$vPerkerasan4);
        }
        
        return $bOK;
}

function saveLapTenis(){
    global $vlapTenis1, $vlapTenis2, $vlapTenis3, $vlapTenis4, $vlapTenis5, $vlapTenis6, $vlapTenis7, $vlapTenis8, $vlapTenis9, $vlapTenis10, $vlapTenis11, $vlapTenis12;
    $bOK = false;
        $bOK = updateNilaiFasNonDep('18',$vlapTenis1);
	if($bOK){
		$bOK = updateNilaiFasNonDep('19',$vlapTenis2);
        }
	if($bOK){
		$bOK = updateNilaiFasNonDep('20',$vlapTenis3);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('21',$vlapTenis4);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('22',$vlapTenis5);
        }
	if($bOK){
		$bOK = updateNilaiFasNonDep('23',$vlapTenis6);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('24',$vlapTenis7);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('25',$vlapTenis8);
        }
	if($bOK){
		$bOK = updateNilaiFasNonDep('26',$vlapTenis9);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('27',$vlapTenis10);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('28',$vlapTenis11);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('29',$vlapTenis12);
        }
        
        return $bOK;
}

function saveLift(){
    global $vlift1, $vlift2, $vlift3, $vlift4, $vlift5, $vlift6, $vlift7, $vlift8, $vlift9, $vlift10, $vlift11, $vlift12;
    
    $bOK = false;
	$bOK = updateNilaiFasDepMinMax('30','0','4',$vlift1);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('30','5','9',$vlift2);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('30','10','19',$vlift3);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('30','20','99',$vlift4);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('31','0','4',$vlift5);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('31','5','9',$vlift6);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('31','10','19',$vlift7);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('31','20','99',$vlift8);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('32','0','4',$vlift9);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('32','5','9',$vlift10);
        if($bOK)
		$bOK = updateNilaiFasDepMinMax('32','10','19',$vlift11);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('32','20','99',$vlift12);

	
	return $bOK;
}
function saveEsc(){
    global $vEsc1, $vEsc2;
    
    $bOK = false;
        $bOK = updateNilaiFasNonDep('33',$vEsc1);
	if($bOK){
		$bOK = updateNilaiFasNonDep('34',$vEsc2);
        }
        
        return $bOK;
}
function savePagar(){
    global $vpagar1, $vpagar2;
    
    $bOK = false;
        $bOK = updateNilaiFasNonDep('35',$vpagar1);
	if($bOK){
		$bOK = updateNilaiFasNonDep('36',$vpagar2);
        }
        
        return $bOK;
}
function saveProtApi(){
    global $vProtApi1, $vProtApi2, $vProtApi3;
    
    $bOK = false;
        $bOK = updateNilaiFasNonDep('37',$vProtApi1);
	if($bOK){
		$bOK = updateNilaiFasNonDep('38',$vProtApi2);
        }
        if($bOK){
		$bOK = updateNilaiFasNonDep('39',$vProtApi3);
        }
        
        return $bOK;
    
}
function saveGenset(){
    global $vGenset1, $vGenset2, $vGenset3, $vGenset4;
    
    $bOK = false;
	$bOK = updateNilaiFasDepMinMax('40','0','99',$vGenset1);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('40','100','249',$vGenset2);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('40','250','499',$vGenset3);
	if($bOK)
		$bOK = updateNilaiFasDepMinMax('40','500','999999',$vGenset4);
        
        return $bOK;
}
function savePABX(){
    global $vPABX;
    
     $bOK = false;
        $bOK = updateNilaiFasNonDep('41',$vPABX);
        
        return $bOK;
}
function saveAirArt(){
    global $vAirArt;
    
     $bOK = false;
        $bOK = updateNilaiFasNonDep('42',$vAirArt);
        
        return $bOK;
}
function saveBoiler(){
    global $vboihotel1, $vboihotel2, $vboihotel3, $vboiapart1, $vboiapart2;
    
    $bOK = false;

	$bOK = updateNilaiFasDepKlsBintang('43','07','2',$vboihotel1);
	
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('43','07','4',$vboihotel2);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('43','07','5',$vboihotel3);
	}
        if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('45','13','1',$vboiapart1);
	}
	if($bOK){
		$bOK = updateNilaiFasDepKlsBintang('45','13','3',$vboiapart2);
	}
        
        return $bOK;
}
function saveListrik(){
    global $vListrik;
    
    $bOK = false;
        $bOK = updateNilaiFasNonDep('44',$vListrik);
        
        return $bOK;
}
//save execution
switch($tab){
	case 1 :  $bOK = saveAC(); 
				break;
	case 2 :  $bOK = saveKlmRenang(); 
				break;
	case 3 :  $bOK = savePerkerasan(); 
				break;
	case 4 :  $bOK = saveLapTenis(); 
				break;
	case 5 :  $bOK = saveLift(); 
				break;
        case 6 :  $bOK = saveEsc();
                                break;
        case 7 :  $bOK = savePagar();
                                break;
        case 8 :  $bOK = saveProtApi();
                                break;
        case 9 :  $bOK = saveGenset();
                                break;  
        case 10 :  $bOK = savePABX();
                                break;   
        case 11 :  $bOK = saveAirArt();
                                break;
        case 12 :  $bOK = saveBoiler();
                                break;
        case 13 :  $bOK = saveListrik();
                                break;                    
}

if(!$bOK){
    $respon['respon'] = false;
	$respon['message'] = $err;
}else{
	$respon['respon'] = true;
	$respon['message'] = "sukses";
}
echo json_encode($respon);exit;
?>