<?php


$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris', '', dirname(__FILE__))).'/';

require_once($sRootPath."inc/payment/constant.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/payment/json.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME,true);
if ($iErrCode != 0) {
	$sErrMsg = 'FATAL ERROR: '.$sErrMsg;
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
	exit(1);
}

error_reporting(E_ALL);
ini_set("display_errors", 1); 

$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);

function getConfigValue ($key) {
	global $DBLink;	
	//$qry = "select * from central_app_config where CTR_AC_KEY = '$key'";
	$qry = "select * from central_app_config where CTR_AC_AID = 'aBPHTB' and CTR_AC_KEY = '$key'";
	
		$res = mysqli_query($DBLink, $qry);
		if ( $res === false ){
			echo $qry ."<br>";
			echo mysqli_error($DBLink);
		}
		while ($row = mysqli_fetch_assoc($res)) {
			return $row['CTR_AC_VALUE'];
		}
	
}

// function getConfigure ($appID) {
  // $config = array();
  // $a=$appID;
  // $config['TENGGAT_WAKTU'] = getConfigValue($a,'TENGGAT_WAKTU');
  // $config['NPOPTKP_STANDAR'] = getConfigValue($a,'NPOPTKP_STANDAR');
  // $config['NPOPTKP_WARIS'] = getConfigValue($a,'NPOPTKP_WARIS');
  // $config['TARIF_BPHTB'] = getConfigValue($a,'TARIF_BPHTB');
  // $config['PRINT_SSPD_BPHTB'] = getConfigValue($a,'PRINT_SSPD_BPHTB');
  // $config['NAMA_DINAS'] = getConfigValue($a,'NAMA_DINAS');
  // $config['ALAMAT'] = getConfigValue($a,'ALAMAT');
  // $config['NAMA_DAERAH'] = getConfigValue($a,'NAMA_DAERAH');
  // $config['KODE_POS'] = getConfigValue($a,'KODE_POS');
  // $config['NO_TELEPON'] = getConfigValue($a,'NO_TELEPON');
  // $config['NO_FAX'] = getConfigValue($a,'NO_FAX');
  // $config['EMAIL'] = getConfigValue($a,'EMAIL');
  // $config['WEBSITE'] = getConfigValue($a,'WEBSITE');
  // $config['KODE_DAERAH'] = getConfigValue($a,'KODE_DAERAH');
  // $config['KEPALA_DINAS'] = getConfigValue($a,'KEPALA_DINAS');
  // $config['NAMA_JABATAN'] = getConfigValue($a,'NAMA_JABATAN');
  // $config['NIP'] = getConfigValue($a,'NIP');
  // $config['NAMA_PJB_PENGESAH'] = getConfigValue($a,'NAMA_PJB_PENGESAH');
  // $config['JABATAN_PJB_PENGESAH'] = getConfigValue($a,'JABATAN_PJB_PENGESAH');
  // $config['NIP_PJB_PENGESAH'] = getConfigValue($a,'NIP_PJB_PENGESAH');
  // return $config;
// }

$nop = @isset($_REQUEST['nop']) ? intval($_REQUEST['nop']) : "";
$appId =base64_decode(@isset($_REQUEST['axx']) ? $_REQUEST['axx'] : "");
//$result = array();
function cek_sw_allow_input($nop) {
    global $DBLink;
    $boleh_input = 1;

    $dbLimit = getConfigValue('TENGGAT_WAKTU');
    
   $cari = "select CPM_TRAN_SSB_ID,CPM_TRAN_STATUS,
                case when DATE_ADD(a.CPM_SSB_CREATED,INTERVAL {$dbLimit} day) > CURDATE() then 0 else 1 end as KADALUARSA
                from cppmod_ssb_doc a inner JOIN cppmod_ssb_tranmain b ON
                a.CPM_SSB_ID = b.CPM_TRAN_SSB_ID
                where 
                a.CPM_OP_NOMOR = '" . mysqli_real_escape_string($DBLink, $nop) . "' and
                b.CPM_TRAN_FLAG = '0'
                order by (CPM_TRAN_DATE) desc limit 0,1";
    //echo $cari;exit;          
    $query = mysqli_query($DBLink, $cari);
    if ($doc = mysqli_fetch_array($query)) {
        if ($doc['KADALUARSA'] == 1 || $doc['CPM_TRAN_STATUS']=='4' ) {
            $boleh_input = "1";
        }else if ($doc['CPM_TRAN_STATUS']=='5' ) {
            $boleh_input = 1;            
        }else{
            $boleh_input = 0;
        }
    }    
    
    return $boleh_input;
}
function cek_gw_allow_input($nop) {
    global $DBLink;
    $boleh_input = 1;

    $dbName = getConfigValue('BPHTBDBNAME');
    $dbHost = getConfigValue('BPHTBHOSTPORT');
    $dbPwd = getConfigValue('BPHTBPASSWORD');
    $dbTable = getConfigValue('BPHTBTABLE');
    $dbUser = getConfigValue('BPHTBUSERNAME');
    $dbLimit = getConfigValue('TENGGAT_WAKTU');


    SCANPayment_ConnectToDB($DBLinkLookUp, $DBConn2, $dbHost, $dbUser, $dbPwd, $dbName);

    $cari = "select payment_flag,
                case when 
                DATE_ADD(saved_date,INTERVAL {$dbLimit} day) > CURDATE() then 0
                else 1 end as KADALUARSA    
             from {$dbTable} 
             where 
                op_nomor ='" . mysqli_real_escape_string($DBLinkLookUp, $nop) . "'";
	//echo $cari;
    $query = mysqli_query($DBLinkLookUp, $cari);
    if ($doc = mysqli_fetch_array($query)) {
        if ($doc['payment_flag'] == 0) {            
            $boleh_input = ($doc['KADALUARSA'] == 1) ? 1 : 0;
        }
    }
    return $boleh_input;
}
$hasil=1;
if (cek_sw_allow_input($nop) == 1) {
        if (cek_gw_allow_input($nop) == 1) {
            $hasil=1;
        } else {
			$hasil=2;
        }
    } else {
		$hasil=3;
    }
// if ($id) {
	 $result['success'] = true;
	// if (($id==4) || ($id==6)){
		// $result["result"] =  getConfigValue($appId,'NPOPTKP_WARIS');
	// } else {
		// $result["result"] =  getConfigValue($appId,'NPOPTKP_STANDAR');
	// }
	
	// $sResponse = $json->encode($result);
	// echo $sResponse;
// }
$result['result'] = $hasil;
$sResponse = $json->encode($result);
echo $sResponse;
SCANPayment_CloseDB($DBLink);
?>
