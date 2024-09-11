<?php
require_once("../../../inc/payment/constant.php");
require_once("../../../inc/payment/inc-payment-c.php");
require_once("../../../inc/payment/inc-payment-db-c.php");
require_once("../../../inc/payment/prefs-payment.php");
require_once("../../../inc/payment/db-payment.php");
require_once("../../../inc/payment/ctools.php");
require_once("../../../inc/payment/json.php");
require_once("../../../inc/payment/log-payment.php");
require_once("../../../inc/payment/sayit.php");
require_once("../../../inc/payment/cdatetime.php");
require_once("../../../inc/report/eng-report-table.php");
require_once("../../../inc/check-session.php");
require_once("../../../inc/central/user-central.php");
SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

function GetValuesForPrint($DATETRAN,$UID,&$header,&$body,&$footer) {
	$header = array();
	$body   = array();
	$footer = array();
	$header['OPERATOR'] = $UID;
        $DISPLAYDATETRAN = explode ('-',$DATETRAN);
	$header['TGL'] = $DISPLAYDATETRAN[2].'-'.$DISPLAYDATETRAN[1].'-'.$DISPLAYDATETRAN[0];
	$header['TGL_PRINT'] = strftime("%d-%m-%Y", time());
	
        $sQCond = " where PAYMENT_PAID like '$DATETRAN%' AND PAYMENT_OFFLINE_USER_ID = '$UID' AND PAYMENT_FLAG = '1' ";
        
        $sql = "SELECT COUNT(*) NTRANSACTION FROM PBB_SPPT A $sQCond ";
        
        $result = mysqli_query($DBLink, $sql);
        if($row = mysqli_fetch_array($result)){
            $footer["LEMBAR_REK"] = number_format($row['NTRANSACTION'], 0, ',', '.');
            $header["LEMBAR_REK"] = number_format($row['NTRANSACTION'], 0, ',', '.');
        }
	
	$sql = "SELECT SUM(PBB_TOTAL_BAYAR) SUM_TRANSACTION FROM PBB_SPPT A $sQCond ";
        $result = mysqli_query($DBLink, $sql);
        $totalBayar = 0;
        if($row = mysqli_fetch_array($result)){
            $totalBayar = $row['SUM_TRANSACTION'];
            $footer["RP_TAG"] = number_format($row['SUM_TRANSACTION'], 0, ',', '.');
        }
	SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
	$sqls     = "SELECT * FROM central_app_config WHERE CTR_AC_AID = 'aPBB' and CTR_AC_KEY='kota'";
	$results  = mysqli_query($DBLink, $sqls);
	$baris 	 = mysqli_fetch_array($results);
	$kota 	 = $baris['CTR_AC_VALUE'];
	
	$footer["RP_ADM"] =  '0';
	$footer["BILL_TAG"] = $footer["RP_TAG"];
	
        $body[0] = array();

        $body[0]['TAX_NAME'] = 'PBB';
        $body[0]['AREA_NAME'] = $kota;
        $body[0]['TOTAL_COUNT'] = $footer["LEMBAR_REK"];

        //$tagihan = $row['AMOUNT'] - $row['ADMIN_FEE'];
        $body[0]['TAGIHAN'] = $footer["RP_TAG"];

        $body[0]['ADMIN_FEE'] = '0';

        $amount = '1000000';
        $body[0]['TOTAL'] = (isset($footer["RP_TAG"]) ? $footer["RP_TAG"] : 0);
		
	
	
	
	$footer["RP_REK"] = (isset($footer["RP_TAG"]) ? $footer["RP_TAG"] : 0);
	$footer["RP_TERBILANG"] = SayInIndonesian($totalBayar)." rupiah";
	$footer["CUSTOM_NOTES"] = " ";
	
	//SCANPayment_CloseDB($DBLink);
	return 1;
} 

function printReceipt($DATETRAN,$UID, &$printHTML,&$printCode) {
	global $sTemplateFile,$User,$driver;

	$err = "error";
	$printHTML = 'Data kosong !';


	
	$sTemplateFile = "pbb-daily-report.xml";

	getValuesForPrint($DATETRAN,$UID,$header,$body,$footer);
	$re = new ReportEngineTable($sTemplateFile, $header, $body, $footer);
	$re->Print2TXT($printValue);
	$printValue = base64_encode($printValue);
	$re->PrintHTML($printhtml);
	$printHTML = $printhtml;
	$printCode = $printValue;
		
	return 1;
}

$aResponse = array();
$aResponse['resulte'] = false;
$aResponse['message'] = 0;
$aResponse['printHTML'] = array();
$aResponse['aBill'] = array();
$aResponse['printCode'] = array();

$sQueryString = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : '');
$driver = "epson";
if ($sQueryString != '') {
    $sBlockReq = base64_decode($sQueryString);
    $dt = json_decode($sBlockReq);
    $driver = $dt->driver;
    if(printReceipt($dt->dateTrs, $dt->uid, $printHTML, $printCode)) {
            $aResponse['result'] = true;
            $aResponse['printHTML'] = $printHTML;
            $aResponse['printCode'] = $printCode; 
    } else {
            // reconcil was failed
            $aResponse['message'] = "Gagal printing [-401]"; // unable to reconcil (update db)
    }

} else {
        $aResponse['message'] = "Gagal printing [-501]"; // invalid request (require more specific stuffs)
}
$sResponse = json_encode($aResponse);
echo base64_encode($sResponse);


?>