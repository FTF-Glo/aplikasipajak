<?php

function phpPenilaian($param, $directTo, $isReturn=false)
{
    $url = $directTo . "?req=" . base64_encode($param);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if($isReturn) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    curl_exec($ch);
    curl_close($ch);
}


///
/// this function is Deprecated
/// 
function GetRemoteResponse($address, $port, $timeout, $out, &$sResp)
{
  $s = '';
  $bTimeout = 0;
  
  $fp = fsockopen($address, $port, $errno, $errstr, $timeout);

  if (!$fp){
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_DEBUG)){
      error_log ("$errstr ($errno)\n", 3, LOG_FILENAME);
      //var_dump(error_get_last());
    }
  }else{
	  // $n = fwrite($fp, GetLengthByte(strlen($out)), 2); //byte order
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
    $i = 1;
    while ((!feof($fp)) && ($bTimeout==0) && (!$bDone))
    {
      $info = @stream_get_meta_data($fp);
      if ($info['timed_out'])
      {
        $bTimeout = 1;
      }

      if ($bTimeout==0)
      {
        $c = fread($fp, 1); //var_dump($c);exit;
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