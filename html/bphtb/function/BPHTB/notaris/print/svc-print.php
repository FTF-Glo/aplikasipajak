<?php
/* 
 *  Print SSPD - BPHTB
 *  Author By ardi@vsi.co.id
 *  06-12-2016
 */
 
#error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
#ini_set("display_errors", 1);

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'BPHTB'.DIRECTORY_SEPARATOR.'notaris'.DIRECTORY_SEPARATOR.'print', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/report/eng-report-bphtb.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");
require_once($sRootPath."function/BPHTB/notaris/print/svc-data.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
if ($iErrCode != 0) {
    $sErrMsg = 'FATAL ERROR: ' . $sErrMsg;
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
        error_log("[" . strftime("%Y%m%d%H%M%S", time()) . "][" . (basename(__FILE__)) . ":" . __LINE__ . "] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
    exit(1);
}
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);


function doPrint() {
	$arrValues['code'] = '00';
	$arrValues['data'] = printRequest();
	return $arrValues;
}

function GetValuesForPrint() {
	global $idssb, $ids;
	$data = getData($idssb);
    $kode_bayar = getDocId($ids,$data->CPM_WP_NAMA);
    
    $a = strval($data->CPM_OP_LUAS_BANGUN) * strval($data->CPM_OP_NJOP_BANGUN) + strval($data->CPM_OP_LUAS_TANAH) * strval($data->CPM_OP_NJOP_TANAH);
    $b = strval($data->CPM_OP_HARGA);
    $NPOPTKP = $data->CPM_OP_NPOPTKP;
    $type = $data->CPM_PAYMENT_TIPE;
    $NOP = $data->CPM_OP_NOMOR;
    $c1 = " ";
    $c2 = " ";
    $c3 = " ";
    $c4 = " ";

    if ($type == '1')
        $c1 = "X";
    if ($type == '2')
        $c2 = "X";
    if ($type == '3')
        $c3 = "X";
    if ($type == '4')
        $c4 = "X";

    if ($b < $a)
        $npop = $a;
    else
        $npop = $b;

    $n = strval($data->CPM_PAYMENT_TIPE_PENGURANGAN);
    $a = $npop - strval($NPOPTKP) > 0 ? $npop - strval($NPOPTKP) : 0;
    $m = ($a) * 0.05;
    $a = $a * 0.05;

    if ($n != 0)
        $m = $m - $m * ($n * 0.01);
    $b = $npop - $NPOPTKP;
    if ($b < 0)
        $b = 0;
    if (($data->CPM_PAYMENT_TIPE == '2') && (!is_null($data->CPM_OP_BPHTB_TU))) {
        $a = $data->CPM_OP_BPHTB_TU;
        $m = $a;
    }
    
    $npwpd = $data->CPM_WP_NPWP;
    $nop = $data->CPM_OP_NOMOR;
    
    $data->CPM_WP_NAMA = strtoupper($data->CPM_WP_NAMA);
    $data->CPM_WP_NPWP =
		substr($npwpd, 0,2).' '.
		substr($npwpd, 2,3).' '.
		substr($npwpd, 5,3).' '.
		substr($npwpd, 8,1).' '.
		substr($npwpd, 9,3).' '.
		substr($npwpd, 12,3);
		    
    $data->CPM_OP_NOMOR = 
		substr($nop, 0,2).' '.
		substr($nop, 2,2).' '.
		substr($nop, 4,3).' '.
		substr($nop, 7,3).' '.
		substr($nop, 10,3).' '.
		substr($nop, 13,4).' '.
		substr($nop, 17,1);
    
    $data->CPM_OP_JENIS_HAK = str_pad($data->CPM_OP_JENIS_HAK, 2, 0, STR_PAD_LEFT);
    $data->CPM_PAYMENT_TIPE_PENGURANGAN = str_pad($data->CPM_PAYMENT_TIPE_PENGURANGAN, 2, 0, STR_PAD_LEFT);
    
    $data->CPM_OP_LUAS_TANAH = number_format(intval($data->CPM_OP_LUAS_TANAH));
    $data->CPM_OP_LUAS_BANGUN = number_format(intval($data->CPM_OP_LUAS_BANGUN));
    $data->CPM_OP_NJOP_TANAH = number_format(intval($data->CPM_OP_NJOP_TANAH));
    $data->CPM_OP_NJOP_BANGUN = number_format(intval($data->CPM_OP_NJOP_BANGUN));
    
    $data->CPM_OP_LUAS_NJOP_TANAH = number_format(intval($data->CPM_OP_NJOP_TANAH) * ($data->CPM_OP_LUAS_TANAH), 0, ',', '.');
    $data->CPM_OP_LUAS_NJOP_BANGUN = number_format(intval($data->CPM_OP_NJOP_BANGUN) * ($data->CPM_OP_LUAS_BANGUN), 0, ',', '.');
    $data->CPM_OP_LUAS_NJOP_TANAH_BANGUN = number_format(intval($data->CPM_OP_NJOP_BANGUN) * ($data->CPM_OP_LUAS_BANGUN) + intval($data->CPM_OP_NJOP_TANAH) * ($data->CPM_OP_LUAS_TANAH), 0, ',', '.');
    $data->CPM_OP_HARGA = number_format($data->CPM_OP_HARGA, 0, ',', '.');
    
    $data->CPM_OP_NPOP = number_format(intval($npop));
    $data->CPM_OP_NPOPTKP = number_format(intval($data->CPM_OP_NPOPTKP));
    $data->C3 = number_format($b, 0, ',', '.');
    $data->C4 = number_format($a, 0, ',', '.');
    
    $data->DA = $c1;
    $data->DB = $c2;
    $data->DC = $c3;
    $data->DD = $c4;
    $data->JUMLAH_DISETOR_ANGKA = number_format($m, 0, ',', '.');
    
    $terbilang = strtoupper(SayInIndonesian(number_format($m, 0, ',', ''))).' RUPIAH';
    $terbilang1 = '';
    $terbilang2 = '';
    $arrTerbilang = explode(" ",$terbilang);
    if(count($arrTerbilang)>8){
		for($x=0;$x<8;$x++){
			$terbilang1.= $arrTerbilang[$x];
		}
		for($x=8;$x<count($arrTerbilang);$x++){
			$terbilang2.= $arrTerbilang[$x];
		}
	}else{
		$terbilang1 = $terbilang;
	}
    
    $data->JUMLAH_DISETOR_TERBILANG1 = $terbilang1;
    $data->JUMLAH_DISETOR_TERBILANG2 = $terbilang2;
    
    $data->NAMA_PJB_PENGESAH = getConfigValue('NAMA_PJB_PENGESAH');
    return (array) $data;
} 

function printRequest() {
	global $sRootPath;
	
	$sTemplateFile = $sRootPath."function/BPHTB/notaris/print/sspd-report.xml";
	$driver="epson-bphtb";
	
	$re = new reportEngineBPHTB($sTemplateFile,$driver);
	
	if ($aTemplateValue = GetValuesForPrint()){
		$re->ApplyTemplateValue($aTemplateValue);
		if($driver=="other"){
			$re->Print2OnpaysTXT($printValue);
			$strTXT = $printValue;
		}else{
			$re->Print2TXT($printValue);
			$strTXT = base64_encode($printValue);
		}
	}
	return $strTXT;
} 

$q = @isset($_REQUEST['q']) ? $_REQUEST['q'] : "";
$q = base64_decode($q);
$q = $json->decode($q);

$idssb = $q->id;
$uname = $q->uname;
$draf = $q->draf;
$setuju = isset($q->setuju) ? $q->setuju : 0;
$ids = base64_decode($q->axx);
$appID = $ids;
$arrValues = array();

if(stillInSession($DBLink,$json,$sdata)){
	$arrValues = doPrint();
} else {
	$arrValues['code'] = '10';
}

$val = $json->encode($arrValues);
echo base64_encode($val);

?>
