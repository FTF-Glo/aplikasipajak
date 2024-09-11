<?php
// require constant.php (END_OF_MSG)

function ConstructAbsoluteHTTPURL($sHost, $iPort, $sRootPath, $sPath)
{
  $sMyRootPath = $sRootPath;
  $sMyPath = $sPath;
  $sMyRootPath = trim($sMyRootPath, '/');
  $sMyPath = ltrim($sMyPath, '/');
  $s = 'http://' . $sHost;
  if ($iPort != 80) $s .= ':' . $iPort;
  $s .= '/' . $sMyRootPath . '/' . $sMyPath;
  return $s;
} // end of ConstructAbsoluteHTTPURL

function CentralGetResponse($sReqStream, $aPrefs, $sRemotePath)
{
  $sReqStreamToSend = $sReqStream.END_OF_MSG;

  $sURL = ConstructAbsoluteHTTPURL(isset($aPrefs['PP.electric.PC.connection.server.host']) ? $aPrefs['PP.electric.PC.connection.server.host'] : 'localhost', isset($aPrefs['PP.electric.PC.connection.server.port']) ? $aPrefs['PP.electric.PC.connection.server.port'] : '80', isset($aPrefs['PP.electric.PC.connection.server.root']) ? $aPrefs['PP.electric.PC.connection.server.root'] : 'payment', "$sRemotePath?".urlencode($sReqStreamToSend));
  $sResp = trim(file_get_contents($sURL, FALSE, NULL), "\r\n");

  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".basename(__FILE__).":".__LINE__."] [DEBUG] sURL [$sURL] sResp [$sResp]\n", 3, LOG_FILENAME);

  return $sResp;
} // end of CentralGetResponse

function CentralGetResponse2($sReqStream, $aPrefs, $sRemotePath)
{
  $sReqStreamToSend = $sReqStream.END_OF_MSG;

  $sURL = ConstructAbsoluteHTTPURL(isset($aPrefs['PP.electric.PC.connection.server.host']) ? $aPrefs['PP.electric.PC.connection.server.host'] : 'localhost', isset($aPrefs['PP.electric.PC.connection.server.port']) ? $aPrefs['PP.electric.PC.connection.server.port'] : '80', '/', "$sRemotePath?".urlencode($sReqStreamToSend));
  $sResp = trim(file_get_contents($sURL, FALSE, NULL), "\r\n");

  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".basename(__FILE__).":".__LINE__."] [DEBUG] sURL [$sURL] sResp [$sResp]\n", 3, LOG_FILENAME);

  return $sResp;
} // end of CentralGetResponse

// FUNCTION CALLER must include http-funcs.php to use this function
function CentralPostResponse($sEncodedFormQuery, $aPrefs, $sRemotePath)
{
  global $iErrCode, $sErrMsg;

  $aCustomHeader = Array();
  $aCustomHeader['Content-Type'] = 'application/x-www-form-urlencoded';
  $aCustomHeader['Cache-Control'] = 'max-age=0';
  $sCentralHost = (isset($aPrefs['PP.electric.PC.connection.server.host']) ? $aPrefs['PP.electric.PC.connection.server.host'] : 'localhost');
  $iCentralPort = intval(isset($aPrefs['PP.electric.PC.connection.server.port']) ? $aPrefs['PP.electric.PC.connection.server.port'] : '80');
  $sCentralRoot = trim(trim(isset($aPrefs['PP.electric.PC.connection.server.root']) ? $aPrefs['PP.electric.PC.connection.server.root'] : 'payment'), '/');
  $iCentralTimeout = intval(isset($aPrefs['PP.electric.PC.connection.timeout']) ? $aPrefs['PP.electric.PC.connection.timeout'] : '60');
  $sCentralPath = ltrim(trim($sRemotePath), '/');
  //echo "http://$sCentralHost:$iCentralPort/$sCentralRoot/$sCentralPath";

  $s = DoHTTPPost($iErrCode, $sErrMsg, 'post', $sCentralHost, $iCentralPort, "/$sCentralRoot/$sCentralPath", $sEncodedFormQuery, $aCustomHeader, $iCentralTimeout);
  $sResp = GetHTTPDataRaw($s);
  $sResp = rtrim($sResp, "\r\n");
  if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
    error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] host [$sCentralHost] port [$iCentralPort] timeout [$iCentralTimeout] sEncodedFormQuery [$sEncodedFormQuery] iErrCode [$iErrCode] sErrMsg [$sErrMsg] s [$s] sResp [$sResp]\n", 3, LOG_FILENAME);

  return $sResp;
} // end of CentralPostResponse

function GetRemoteResponse($address, $port, $timeout, $out, &$sResp)
{
  $s = '';
  $bTimeout = 0;
  
  $fp = fsockopen($address, $port, $errno, $errstr, $timeout);

  if (!$fp)
  {
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	{
		error_log ("$errstr ($errno)\n", 3, LOG_FILENAME);
		//var_dump(error_get_last());
	}
  }
  else
  {
	//$n = fwrite($fp, GetLengthByte(strlen($out)), 2); //byte order
    $n = fwrite($fp, $out, strlen($out));
    $n = fwrite($fp, chr(-1));
	if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	{
		error_log ("[PARAM] [$address, $port, $errno, $errstr, $timeout, ".strlen($out)."]\n", 3, LOG_FILENAME);
		error_log ("[REQUEST] [$out]\n", 3, LOG_FILENAME);
	}
    @stream_set_timeout($fp, $timeout);

    $c = '';
    $bDone = false;
    $bHead = false;
    $lenCount = 0;
    $i = 0;
    while ((!feof($fp)) && ($bTimeout==0) && (!$bDone))
    {
      $info = @stream_get_meta_data($fp);
      if ($info['timed_out'])
      {
        $bTimeout = 1;
      }

      if ($bTimeout==0)
      {
		$c = fread($fp, 1);
		if($c != chr(-1))
		{
			$s .= $c;
		}
		else
			$bDone = true;
      } // end of !$bTimeout
    }
    
    fclose($fp);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG))
	{
		error_log ("[RESPONSE] [$s] timeout: ".print_r($bTimeout,TRUE)." \n", 3, LOG_FILENAME);
	}
  }
  $sResp = $s;

  return $bTimeout;
}
?>
