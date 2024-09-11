<?php
set_time_limit(0);
//set_include_path(get_include_path().PATH_SEPARATOR.str_replace(DIRECTORY_SEPARATOR.'inc', '', dirname(__FILE__)));
$sRootPathLogPayment = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'payment', '', dirname(__FILE__)).'/');
require_once($sRootPathLogPayment."inc/payment/constant.php");
require_once($sRootPathLogPayment."inc/payment/ctools.php");
require_once($sRootPathLogPayment."inc/payment/uuid.php");

// LOG_FILENAME must be defined & declared as a global constant

// $iType = {0 = SYSTEM, 1 = MODULE, 2 = USER}
// $iMsgType = {0 = INFO, 1 = WARNING, 2 = ERROR}
function SCANPayment_Log($DBLink, $sOwner = 'SYSTEM', $iType = 0, $iMsgType = 0, $sMsg = '')
{
  $bOK = false;
  $sThisFile = basename(__FILE__);

  $sQ = "insert into c_log(C_L_ID, C_L_OWNER, C_L_TYPE, C_L_TS, C_L_MSG_TYPE, C_L_MSG) values('".c_uuid()."', '".CTOOLS_ValidateQueryForDB($sOwner, "'", 'MYSQL')."', $iType, ".time().", $iMsgType, '".CTOOLS_ValidateQueryForDB($sMsg, "'", 'MYSQL')."')";
  if (mysqli_query($DBLink, $sQ))
  {
    $bOK = true;
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$this->sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }

  return $bOK;
} // end of SCANPayment_Log

?>
