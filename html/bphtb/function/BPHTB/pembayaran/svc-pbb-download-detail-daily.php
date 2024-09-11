<?php

// PHP environment settings
set_time_limit(0);
date_default_timezone_set("Asia/Jakarta");

ob_start();

$sRootPath = str_replace('\\', '/', str_replace(DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.'PBB'.DIRECTORY_SEPARATOR.'pencatatan_pembayaran', '', dirname(__FILE__))).'/';
//require_once($sRootPath."inc/payment/constant.php");
//require_once($sRootPath."inc/payment/inc-payment-c.php");
//require_once($sRootPath."inc/payment/inc-payment-db-c.php");
//require_once($sRootPath."inc/payment/prefs-payment.php");
//require_once($sRootPath."inc/payment/db-payment.php");
//require_once($sRootPath."inc/payment/ctools.php");
//require_once($sRootPath."inc/payment/json.php");
//require_once($sRootPath."inc/payment/cdatetime.php");
//require_once($sRootPath."inc/central/user-central.php");
//require_once($sRootPath."inc/check-session.php");
require_once("../../../inc/payment/constant.php");
require_once("../../../inc/payment/inc-payment-c.php");
require_once("../../../inc/payment/inc-payment-db-c.php");
require_once("../../../inc/payment/prefs-payment.php");
require_once("../../../inc/payment/db-payment.php");
require_once("../../../inc/payment/ctools.php");
require_once("../../../inc/payment/json.php");
require_once("../../../inc/payment/sayit.php");
require_once("../../../inc/payment/cdatetime.php");
require_once("../../../inc/report_stts/eng-report-table.php");
require_once("../../../inc/check-session.php");
require_once("../../../inc/central/user-central.php");

SCANPayment_ConnectToDB($DBLink, $DBConn, ONPAYS_DBHOST, ONPAYS_DBUSER, ONPAYS_DBPWD, ONPAYS_DBNAME, true);
require_once('connectDB_GW.php');
require_once('queryOpen.php');
SCANPayment_ConnectToDB($DBLink2, $DBConn2, GW_DBHOST, GW_DBUSER, GW_DBPWD, GW_DBNAME, true);

$sQueryString = (@isset($_REQUEST['q']) ? $_REQUEST['q'] : '');
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$User = new SCANCentralUser(DEBUG, LOG_DMS_FILENAME, $DBLink);

$sBlockReq = base64_decode($sQueryString);
$dt = $json->decode($sBlockReq);
$myConn = $User->GetDbConnectionFromApp($dt->a);

// start stopwatch
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	$iStart = microtime(true);
}

function downloadFile ($file, $mimetype)
{
 $status = 0;
 if (($file != NULL) && file_exists($file)) {
   if(isset($_SERVER['HTTP_USER_AGENT']) &&
      preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']))
   {
     // IE Bug in download name workaround
     ini_set( 'zlib.output_compression','Off' );
   }
   // header ('Content-type: ' . mime_content_type($file)
   header ('Content-type: ' . $mimetype);
   header ('Content-Disposition: attachment; filename="'.basename($file).'"');
   header ('Expires: '.gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y"))).' GMT');
   header ('Accept-Ranges: bytes');
   // Use Cache-control: private not following:
   // header ('Cache-control: no-cache, must-revalidate');
   header("Cache-control: private");                   
   header ('Pragma: private');
   
   $size = filesize($file);
   if(isset($_SERVER['HTTP_RANGE'])) {
     list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']);
     //if yes, download missing part
     str_replace($range, "-", $range);
     $size2 = $size-1;
     $new_length = $size2-$range;
     header("HTTP/1.1 206 Partial Content");
     header("Content-Length: $new_length");
     header("Content-Range: bytes $range-$size2/$size");
   }
   else
   {
     $size2=$size-1;
     header("Content-Range: bytes 0-$size2/$size");
     header("Content-Length: ".$size);
   }
   if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		{
		  error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] size [$size $size2 $range $new_length]\n", 3, LOG_FILENAME);
		}
   if ($file = fopen($file, 'r')) {
     while(!feof($file) and (connection_status()==0)) {
       $buff=fread($file, 1024);
	   print($buff);
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
		{
		  error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] buff [$buff]\n", 3, LOG_FILENAME);
		}
       flush();
     }
     $status = (connection_status() == 0);
     fclose($file);
   }
 }
 return($status);
}

// global variables
$iCentralTS = time();
$json = new Services_JSON(SERVICES_JSON_SUPPRESS_ERRORS);
$toXls = true;
$xsvFile = "";

$header = array(
	// "0 NO"                   => "No.",
	"NOP"                       => "NOP/NPWP",
        "SPPT_TAHUN_PAJAK"          => "Tahun Pajak",
	"SPPT_PBB_HARUS_DIBAYAR"    => "Tagihan",
	"PBB_DENDA"                 => "Denda",
	"PBB_MISC_FEE"              => "Biaya Lain-Lain",
	"PBB_ADMIN_GW"              => "Biaya Admin",
	"TOTAL"                     => "Jumlah Total"
);

// ---------------
// LOCAL FUNCTIONS
// ---------------
function format($key, $value) {
	// Custom formatting
	if ($key == "PAYMENT_PAID") {
		return strftime("%d-%m-%Y %H:%M:%S", strtotime($value));
	}
	if ($key == "NOP") {
		return "&nbsp;".$value;
	}
	if($key=="SPPT_PBB_HARUS_DIBAYAR" || $key=="PBB_DENDA" || $key=="PBB_MISC_FEE" || $key=="PBB_ADMIN_GW" || $key=="TOTAL"){
		$value = $value * 1;
		$value = "Rp. " . number_format($value, 0, ',', '.');
		return $value;
	}
}

function generateReport($uid,$sTS, &$Response) {
	global $iErrCode, $sErrMsg, $json, $header, $xsvFile, $toXls, $DBConn2;
	$bOK = false;
	
	
	$sQCond = " WHERE PAYMENT_PAID like '$sTS%' AND PAYMENT_FLAG = 1 AND PAYMENT_OFFLINE_USER_ID = '$uid'";
	
// Construct query
	$selectClause = "";
	$first = true;
	foreach ($header as $key => $value) {
		if ($first) {
			$first = false;
		} else {
			$selectClause .= ", ";
		}
		$selectClause .= $key;
	}
	
	$sQ = "select NOP, SPPT_TAHUN_PAJAK, SPPT_PBB_HARUS_DIBAYAR , IFNULL(PBB_DENDA,0) AS PBB_DENDA, IFNULL(PBB_MISC_FEE,0) AS PBB_MISC_FEE, IFNULL(PBB_ADMIN_GW,0) AS PBB_ADMIN_GW, PBB_TOTAL_BAYAR  AS TOTAL  
			from PBB_SPPT $sQCond";
        
        
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
		error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, LOG_FILENAME);
	}
        if ($res = mysqli_query($sQ)) {
            	$nRes = mysqli_num_rows($res);
                
		$nRecord = $nRes;
		if ($nRes > 0) {
			// Column name
			if ($toXls) {
				// Xls format (HTML)
				$xsvFile = "<table border='1'>\n";
				$xsvFile .= "	<th>No.</th>\n";
				foreach ($header as $key => $value) {
					$xsvFile .= "	<th>$value</th>\n";
				}
				
			} else {
				// Csv format
				$xsvFile = "";
				$xsvFile .= "No.";
				foreach ($header as $key => $value) {
					$xsvFile .= "; ";
					$xsvFile .= $value;
				}
				$xsvFile .= "\n";
			}
			
			$i = 1;
			$summary["SPPT_PBB_HARUS_DIBAYAR"]=0;
			$summary["PBB_DENDA"]=0;
			$summary["PBB_MISC_FEE"]=0;
			$summary["PBB_ADMIN_GW"]=0;
			$summary["TOTAL"]=0;
			while ($row = mysqli_fetch_array($res)) {
                                if ($toXls) {
					$xsvFile .= "	<tr>\n";
					$xsvFile .= "		<td>$i</td>\n";
					foreach ($header as $key => $value) {
						if($key=="SPPT_PBB_HARUS_DIBAYAR" || $key=="PBB_DENDA" || $key=="PBB_MISC_FEE" || $key=="PBB_ADMIN_GW" || $key=="TOTAL"){
							$xsvFile .="		<td align='right'>" ;
							$summary[$key]+=(double)$row[$key];
						}else{
							$xsvFile .="		<td>" ;
						}
						$cellValue = format($key, $row[$key]);
						if (!$cellValue) {
							$cellValue = $row[$key];
						}
						$xsvFile .= trim($cellValue) . "</td>\n";
					}
					$xsvFile .= "	</tr>\n";
				} else {
					$xsvFile .= "$i.";
					foreach ($header as $key => $value) {
						if($key=="SPPT_PBB_HARUS_DIBAYAR" || $key=="PBB_DENDA" || $key=="PBB_MISC_FEE" || $key=="PBB_ADMIN_GW" || $key=="TOTAL"){
							$summary[$key]+=(double)$row[$key];
						}
						$cellValue = format($key, $row[$key]);
						if (!$cellValue) {
							$cellValue = $row[$key];
						}
						$xsvFile .= "; ";
						$xsvFile .= trim($cellValue);
					}
					$xsvFile .= "; ";
				}
				$i++;
			}
			
			if ($toXls) {
				$xsvFile .= "	<tr>\n";
					$xsvFile .= "		<td>&nbsp;</td>\n";
					foreach ($header as $key => $value) {
						if($key=="SPPT_PBB_HARUS_DIBAYAR" || $key=="PBB_DENDA" || $key=="PBB_MISC_FEE" || $key=="PBB_ADMIN_GW" || $key=="TOTAL"){
							$xsvFile .="		<td align='right'>" ;
							$cellValue = "<b>Rp. " . number_format($summary[$key], 0, ',', '.')."</b>";
						}else{
							$xsvFile .="		<td>" ;
							$cellValue = $row[$key];
						}
						$xsvFile .= trim($cellValue) . "</td>\n";
					}
				$xsvFile .= "	</tr>\n";
				$xsvFile .= "</table>\n";
			}else{
					foreach ($header as $key => $value) {
						if($key=="SPPT_PBB_HARUS_DIBAYAR" || $key=="PBB_DENDA" || $key=="PBB_MISC_FEE" || $key=="PBB_ADMIN_GW" || $key=="TOTAL"){
							$cellValue = "Rp. " . number_format($summary[$key], 0, ',', '.');
						}else{
							$cellValue =" ";
						}
						$xsvFile .= "; ";
						$xsvFile .= trim($cellValue);
					}
					$xsvFile .= "; ";
			}
			$bOK = true;
			
			$aResponse['xsvFile'] = $xsvFile;
		}
	} else {
		$iErrCode = -3;
		$sErrMsg = mysqli_error();
	//	echo $sErrMsg;
		$bOK = false;
		if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR)) {
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
		}
	}
	
	SCANPayment_CloseDB($DBLink);
	
	
	return $bOK;
}




// ------------
// MAIN PROGRAM
// ------------



$sClientRemoteAddress = $_SERVER['REMOTE_ADDR'];
if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] Request Stream [$sQueryString] Client Address [$sClientRemoteAddress]\n", 3, LOG_FILENAME);
}

if ($sQueryString != '') {
        $sBlockReq = base64_decode($sQueryString);
        if (CTOOLS_IsInFlag(DEBUG, DEBUG_INFO)) {
                error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Payment point do recon for [$sBlockReq]\n", 3, LOG_FILENAME.'-pp_recon');
        }

        if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
                error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] sQueryString [$sQueryString] sBlockReq [$sBlockReq]\n", 3, LOG_FILENAME);
        }

        if (trim($sBlockReq) != '') {
                generateReport($dt->uid,$dt->dateTrs, $aResponse);
        } 
}
ob_end_flush();
//echo $xsvFile;
if ($xsvFile) {
        $filename="/tmp/pbb_".$dt->uid."_".$dt->dateTrs.time().".xls.gz";
        $zp = gzopen($filename, "w9");
        // write string to file
        gzwrite($zp, $xsvFile);
        // close file
        gzclose($zp);
        downloadFile($filename,"application");
}
	

if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)) {
	$iEnd = microtime(true);
	$iExec = $iEnd - $iStart;
	error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [INFO] Executed in $iExec s = ".($iExec * 1000)." ms\n", 3, LOG_FILENAME);
}

SCANPayment_CloseDB($DBLink);
?>
