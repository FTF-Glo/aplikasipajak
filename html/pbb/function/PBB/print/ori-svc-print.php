<?php
/* 
 *  Print SPPT 
 *  Author By ardi@vsi.co.id
 */

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB/print', '', dirname(__FILE__))).'/';
require_once($sRootPath."inc/payment/ctools.php"); 
require_once($sRootPath."inc/payment/comm-central.php");
require_once($sRootPath."inc/payment/json.php");
require_once($sRootPath."inc/payment/sayit.php");
require_once($sRootPath."inc/payment/cdatetime.php");
require_once($sRootPath."inc/payment/error-messages.php"); 

require_once($sRootPath."inc/report/eng-report.php");
require_once($sRootPath."inc/payment/inc-payment-c.php");
require_once($sRootPath."inc/payment/inc-payment-db-c.php");
require_once($sRootPath."inc/payment/prefs-payment.php");
require_once($sRootPath."inc/payment/db-payment.php");
require_once($sRootPath."inc/check-session.php");
require_once($sRootPath."inc/central/user-central.php");
require_once($sRootPath."inc/central/setting-central.php");
require_once($sRootPath."inc/payment/nid.php");


SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME);
if ($iErrCode != 0)
{
  $sErrMsg = 'FATAL ERROR: '.$sErrMsg;
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".(basename(__FILE__)).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  exit(1);
}

function doPrint($id, &$arrValues) {
	global $paymentDt,$host,$port,$timeOut,$DBLink;
	
	$paymentDt = strftime("%Y%m%d%H%M%S",time());
	
	$arrValues['result'] = true;
	$arrValues['message'] = 'Payment Berhasil !';
	$arrValues['printValue'] = printRequest($id, $strHTML);
	$arrValues['HtmlValue'] = $strHTML;
	// echo $strHTML; exit();
	return true;
}

function getValuesForPrint(&$aTemplateValues,$row)
{
	global $appConfig;
        
        $row['NOP'] = substr($row['NOP'],0,2).'.'.substr($row['NOP'],2,2).'.'.substr($row['NOP'],4,3).'.'.substr($row['NOP'],7,3).'.'.substr($row['NOP'],10,3).'-'.substr($row['NOP'],13,4).'.'.substr($row['NOP'],17,1);
        $aTemplateValues["SPPT_TAHUN_PAJAK"] = $row['SPPT_TAHUN_PAJAK'];
	$aTemplateValues["NOP"]= $row['NOP'];
	$aTemplateValues["OP_ALAMAT"] = $row['OP_ALAMAT'];
	$aTemplateValues["OP_RT"] = $row['OP_RT'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_KELURAHAN"] = $row['OP_KELURAHAN'];
	$aTemplateValues["OP_KECAMATAN"] = $row['OP_KECAMATAN'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_RW"] = $row['OP_RW'];
	$aTemplateValues["OP_KOTAKAB"] = $row['OP_KOTAKAB'];        
        
	$aTemplateValues["WP_NAMA"] = $row['WP_NAMA'];
	$aTemplateValues["WP_ALAMAT"] = $row['WP_ALAMAT'];
	$aTemplateValues["WP_RT"] = $row['WP_RT'];
	$aTemplateValues["WP_RW"] = $row['WP_RW'];
	$aTemplateValues["WP_KELURAHAN"] = $row['WP_KELURAHAN'];
	$aTemplateValues["WP_KECAMATAN"] = $row['WP_KECAMATAN'];
	$aTemplateValues["WP_KOTAKAB"] = $row['WP_KOTAKAB'];
	$aTemplateValues["WP_KODEPOS"] = $row['WP_KODEPOS'];
        
        $OP_LUAS_TANAH_VIEW = '0';
        if(strrchr($row['OP_LUAS_BUMI'],'.') != '') {
            $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'],2,',','.');
        }else $OP_LUAS_TANAH_VIEW = number_format($row['OP_LUAS_BUMI'],0,',','.');
        $aTemplateValues["OP_LUAS_BUMI"] = str_pad($OP_LUAS_TANAH_VIEW, 10, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_KELAS_BUMI"] = $row['OP_KELAS_BUMI'];
	$aTemplateValues["OP_NJOP_BUMI_M2"] = str_pad(number_format($row['OP_NJOP_BUMI_M2'],0,'','.'), 11, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP_BUMI"] = str_pad(number_format($row['OP_NJOP_BUMI'],0,'','.'), 17, " ", STR_PAD_LEFT);
	
        if($row['OP_LUAS_BUMI_BERSAMA'] != null && $row['OP_LUAS_BANGUNAN_BERSAMA'] != null ){
            $aTemplateValues["TITLE_BANGUNAN_BER"] = 'BANGUNAN BERSAMA';
            $aTemplateValues["OP_LUAS_BANGUNAN_BER"] = str_pad(number_format($row['OP_LUAS_BANGUNAN_BERSAMA'],0,'','.'), 6, " ", STR_PAD_LEFT);
            $aTemplateValues["OP_KELAS_BANGUNAN_BER"] = $row['OP_KELAS_BANGUNAN_BERSAMA'];
            $aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2_BERSAMA'],0,'','.'), 11, " ", STR_PAD_LEFT);
            $aTemplateValues["OP_NJOP_BANGUNAN_BER"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_BERSAMA'],0,'','.'), 17, " ", STR_PAD_LEFT);
            
            $aTemplateValues["TITLE_BUMI_BER"] = 'BUMI BERSAMA';
            $aTemplateValues["OP_LUAS_BUMI_BER"] = str_pad(number_format($row['OP_LUAS_BUMI_BERSAMA'],0,'','.'), 10, " ", STR_PAD_LEFT);
            $aTemplateValues["OP_KELAS_BUMI_BER"] = $row['OP_KELAS_BUMI_BERSAMA'];
            $aTemplateValues["OP_NJOP_BUMI_M2_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_M2_BERSAMA'],0,'','.'), 11, " ", STR_PAD_LEFT);
            $aTemplateValues["OP_NJOP_BUMI_BER"] = str_pad(number_format($row['OP_NJOP_BUMI_BERSAMA'],0,'','.'), 17, " ", STR_PAD_LEFT);
            
        }else{
            $aTemplateValues["TITLE_BANGUNAN_BER"] = ' ';
            $aTemplateValues["OP_LUAS_BANGUNAN_BER"] = ' ';
            $aTemplateValues["OP_KELAS_BANGUNAN_BER"] = ' ';
            $aTemplateValues["OP_NJOP_BANGUNAN_M2_BER"] = ' ';
            $aTemplateValues["OP_NJOP_BANGUNAN_BER"] = ' ';
            
            $aTemplateValues["TITLE_BUMI_BER"] = ' ';
            $aTemplateValues["OP_LUAS_BUMI_BER"] = ' ';
            $aTemplateValues["OP_KELAS_BUMI_BER"] = ' ';
            $aTemplateValues["OP_NJOP_BUMI_M2_BER"] = ' ';
            $aTemplateValues["OP_NJOP_BUMI_BER"] = ' ';
        } 
	$aTemplateValues["TITLE_BANGUNAN"] = 'BANGUNAN';
        $OP_LUAS_BANGUNAN_VIEW = '0';
        if(strrchr($row['OP_LUAS_BANGUNAN'],'.') != '') {
            $OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'],2,',','.');
        }else $OP_LUAS_BANGUNAN_VIEW = number_format($row['OP_LUAS_BANGUNAN'],0,',','.');
	$aTemplateValues["OP_LUAS_BANGUNAN"] = str_pad($OP_LUAS_BANGUNAN_VIEW, 10, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_KELAS_BANGUNAN"] = $row['OP_KELAS_BANGUNAN'];
	$aTemplateValues["OP_NJOP_BANGUNAN_M2"] = str_pad(number_format($row['OP_NJOP_BANGUNAN_M2'],0,'','.'), 11, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP_BANGUNAN"] = str_pad(number_format($row['OP_NJOP_BANGUNAN'],0,'','.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOP"] = str_pad(number_format($row['OP_NJOP'],0,'','.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJOPTKP"] = str_pad(number_format($row['OP_NJOPTKP'],0,'','.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJKP"] = str_pad(number_format($row['OP_NJKP'],0,'','.'), 17, " ", STR_PAD_LEFT);
	$aTemplateValues["OP_NJKP_TANPA_PADDING"] = number_format($row['OP_NJKP'],0,'','.');
	$aTemplateValues["OP_TARIF"] = rtrim( $row['OP_TARIF'] , "0" );
	
        $aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = ' ';
        $aTemplateValues["SPPT_PBB_PENGURANGAN"] = ' ';
        $aTemplateValues["TITLE_PENGURANGAN1"] = ' ';
        $aTemplateValues["TITLE_PENGURANGAN2"] = ' ';
        $SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'];
        if($row['SPPT_PBB_PENGURANGAN'] > 0){
            $SPPT_PBB_SEBELUM_PENGURANGAN = $row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'];
            $aTemplateValues["SPPT_PBB_PERSEN_PENGURANGAN"] = $row['SPPT_PBB_PERSEN_PENGURANGAN'];
            $aTemplateValues["SPPT_PBB_PENGURANGAN"] = str_pad(number_format($row['SPPT_PBB_PENGURANGAN'],0,'','.'), 17, " ", STR_PAD_LEFT);
            $aTemplateValues["TITLE_PENGURANGAN1"] = 'SPPT PENGURANGAN';
            $aTemplateValues["TITLE_PENGURANGAN2"] = '= '. number_format($row['SPPT_PBB_PERSEN_PENGURANGAN'],0,'','').' % x '.number_format($row['SPPT_PBB_HARUS_DIBAYAR'] + $row['SPPT_PBB_PENGURANGAN'],0,'','.');
        }
        
        $aTemplateValues["SPPT_PBB_SEBELUM_PENGURANGAN"] = str_pad(number_format($SPPT_PBB_SEBELUM_PENGURANGAN,0,'','.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR"] = str_pad(number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,'','.'), 17, " ", STR_PAD_LEFT);
        $aTemplateValues["SPPT_PBB_HARUS_DIBAYAR_TANPA_PADDING"] = number_format($row['SPPT_PBB_HARUS_DIBAYAR'],0,'','.');
	$aTemplateValues["SPPT_TANGGAL_JATUH_TEMPO"] = substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 8, 2).' '.strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 5, 2) - 1))).' '.substr($row['SPPT_TANGGAL_JATUH_TEMPO'], 0, 4);
	$aTemplateValues["SPPT_TANGGAL_TERBIT"] = substr($row['SPPT_TANGGAL_TERBIT'], 8, 2).' '.strtoupper(GetIndonesianMonthShort((substr($row['SPPT_TANGGAL_TERBIT'], 5, 2) - 1))).' '.substr($row['SPPT_TANGGAL_TERBIT'], 0, 4);
	$aTemplateValues["SPPT_DOC_ID"] = $row['SPPT_DOC_ID'];
	$aTemplateValues["TERBILANG"] = strtoupper (SayInIndonesian($row['SPPT_PBB_HARUS_DIBAYAR']));
	$aTemplateValues["TEMPAT_PEMBAYARAN"] = $appConfig['TEMPAT_PEMBAYARAN'];
	$aTemplateValues["NAMA_KOTA"] = $appConfig['NAMA_KOTA_PENGESAHAN'];
	$aTemplateValues["NAMA_PEJABAT_SK2"] = $appConfig['NAMA_PEJABAT_SK2'];
	$aTemplateValues["NAMA_PEJABAT_SK2_NIP"] = $appConfig['NAMA_PEJABAT_SK2_NIP'];
	$aTemplateValues["NAMA_PEJABAT_SK2_JABATAN"] = $appConfig['NAMA_PEJABAT_SK2_JABATAN'];
	$aTemplateValues["SEKTOR"] = str_pad($row['CPC_NM_SEKTOR'], 10, " ", STR_PAD_LEFT);
	$aTemplateValues["AKUN"] = $row['CPC_KD_AKUN'];
        
	
		
  return true;
} // end of

function printRequest($id, &$strHTML) {
	global $DBLink, $tTime, $modConfig, $sRootPath,$sdata, $Setting, $prm;
	
	$sTemplateFile = $sRootPath.("function/PBB/print/svc-print.xml");
	$driver="epson";
	
	$re = new reportEngine($sTemplateFile,$driver);
		
	$query = "SELECT 
                A.SPPT_TAHUN_PAJAK, A.NOP,
                A.OP_ALAMAT,A.OP_RT,A.OP_RW, A.OP_KELURAHAN, A.OP_KECAMATAN, A.OP_KOTAKAB,
                A.WP_NAMA,A.WP_ALAMAT, A.WP_RT, A.WP_RW, A.WP_KELURAHAN, A.WP_KECAMATAN, A.WP_KOTAKAB, A.WP_KODEPOS,
                A.OP_LUAS_BUMI, A.OP_LUAS_BANGUNAN, A.OP_KELAS_BUMI, A.OP_KELAS_BANGUNAN, A.OP_NJOP_BUMI, A.OP_NJOP_BANGUNAN, A.OP_NJOP,
                A.OP_NJOPTKP,A.OP_NJKP,
                A.SPPT_TANGGAL_JATUH_TEMPO, A.SPPT_PBB_HARUS_DIBAYAR, A.SPPT_TANGGAL_TERBIT, 
                A.SPPT_PBB_PENGURANGAN, A.SPPT_PBB_PERSEN_PENGURANGAN, A.OP_TARIF, A.SPPT_DOC_ID, A.OP_TARIF,
                A.OP_LUAS_BUMI_BERSAMA, A.OP_LUAS_BANGUNAN_BERSAMA, 
                A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,                
                A.OP_KELAS_BUMI_BERSAMA, A.OP_KELAS_BANGUNAN_BERSAMA,
                A.OP_NJOP_BUMI_BERSAMA, A.OP_NJOP_BANGUNAN_BERSAMA,
                C.CPC_NM_SEKTOR, C.CPC_KD_AKUN
                FROM cppmod_pbb_sppt_current A 
                LEFT JOIN cppmod_tax_kelurahan B ON A.OP_KELURAHAN_KODE=B.CPC_TKL_ID 
                LEFT JOIN cppmod_pbb_jns_sektor C ON C.CPC_KD_SEKTOR=B.CPC_TKL_KDSEKTOR
                where A.NOP='$id'";
	
	$result = mysqli_query($DBLink, $query);
	if ($row = mysqli_fetch_array($result)) {
		$row['OP_NJOP_BUMI_M2'] = $row['OP_NJOP_BUMI'] / $row['OP_LUAS_BUMI'];
                $row['OP_NJOP_BANGUNAN_M2'] = $row['OP_NJOP_BANGUNAN'] / $row['OP_LUAS_BANGUNAN'];
                
                $row['OP_NJOP_BUMI_M2_BERSAMA'] = $row['OP_NJOP_BUMI_BERSAMA'] / $row['OP_LUAS_BUMI_BERSAMA'];
                $row['OP_NJOP_BANGUNAN_M2_BERSAMA'] = $row['OP_NJOP_BANGUNAN_BERSAMA'] / $row['OP_LUAS_BANGUNAN_BERSAMA'];                
	}
	
	$re = new reportEngine($sTemplateFile,$driver);
		
	if (GetValuesForPrint($aTemplateValue, $row))
        {
		$re->ApplyTemplateValue($aTemplateValue);
		if($driver=="other"){
			$re->Print2OnpaysTXT($printValue);
			$strTXT = $printValue;
		}else{
			$re->Print2TXT($printValue);
			$strTXT = base64_encode($printValue);
		}
                
		
                $re->PrintHTML($strHTML);
//                echo $strHTML; exit();
        }
	return $strTXT;
} 

$tTime = time();
$paymentDt;
$params = @isset($_REQUEST['req']) ? $_REQUEST['req'] : '';
$p = base64_decode($params);
$json = new Services_JSON();
$prm = $json->decode($p);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$appConfig = $User->GetAppConfig($prm->appID);

$Setting = new SCANCentralSetting(DEBUG, LOG_FILENAME, $DBLink);

$arrValues = array();

if(stillInSession($DBLink,$json,$sdata)){
	
	if ($params) {
		doPrint($prm->NOP, $arrValues);                
	}
} else {
	$arrValues['result'] = false;
	$arrValues['message'] = "Payment Gagal dengan kode error !\n Invalid access. Silahkan lakukan login";
}

$val = $json->encode($arrValues);
echo base64_encode($val);

?>