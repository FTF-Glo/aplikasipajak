<?php
set_time_limit(0);
//set_include_path(get_include_path().PATH_SEPARATOR.str_replace(DIRECTORY_SEPARATOR.'inc', '', dirname(__FILE__)));
$sRootPathPrefsPayment = str_replace('\\', "/", str_replace(DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'payment', '', dirname(__FILE__)).'/');
require_once($sRootPathPrefsPayment."inc/payment/constant.php");
require_once($sRootPathPrefsPayment."inc/payment/ctools.php");

// LOG_FILENAME must be defined & declared as a global constant

function SCANPayment_Pref_GetAll($DBLink, &$aPrefs)
{
  $sThisFile = basename(__FILE__);

  $sQ = "select * from c_registry";
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      while ($row = mysqli_fetch_array($res))
      {
        $aPrefs[$row['C_R_KEY']] = $row['C_R_VALUE'];
      }
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }
} // end of SCANPayment_Pref_GetAll

function SCANPayment_Pref_GetAllWithFilter($DBLink, $sFilter, &$aPrefs)
{
  $sThisFile = basename(__FILE__);

  $sQ = "select * from c_registry where C_R_KEY like '".CTOOLS_ValidateQueryForDB($sFilter, "'", 'MYSQL')."'";
  // echo "sQ [$sQ]\n";
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      while ($row = mysqli_fetch_array($res))
      {
        $aPrefs[$row['C_R_KEY']] = $row['C_R_VALUE'];
        //echo "values [".($row['C_R_KEY'])."] [".($row['C_R_VALUE'])."]\n";
      }
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }
} // end of SCANPayment_Pref_GetAllWithFilter

function SCANPayment_Pref_GetValueForKey($DBLink, $sKey, $sDefault, &$sValue)
{
  $sThisFile = basename(__FILE__);
  $iErrCode = 0;

  $sValue = $sDefault;

  $sQ = "select * from c_registry where C_R_KEY='".CTOOLS_ValidateQueryForDB($sKey, "'", 'MYSQL')."'";
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      if ($row = mysqli_fetch_array($res))
      {
        $sValue = $row['C_R_VALUE'];
      }
    }
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }

  return $iErrCode;
} // end of SCANPayment_Pref_GetValueForKey

function SCANPayment_Pref_SetValueForKey($DBLink, $sKey, $sValue)
{
  $sThisFile = basename(__FILE__);
  $iErrCode = 0;

  $sQ = "select * from c_registry where C_R_KEY='".CTOOLS_ValidateQueryForDB($sKey, "'", 'MYSQL')."'";
  if ($res = mysqli_query($DBLink, $sQ))
  {
    $nRes = mysqli_num_rows($res);
    $nRecord = $nRes;
    if ($nRes > 0)
    {
      $sQ = "UPDATE c_registry set C_R_VALUE='".CTOOLS_ValidateQueryForDB($sValue, "'", 'MYSQL')."' where C_R_KEY='".CTOOLS_ValidateQueryForDB($sKey, "'", 'MYSQL')."'";
    }
	else{
		$sQ = "INSERT INTO c_registry(C_R_KEY,C_R_VALUE) VALUES('".CTOOLS_ValidateQueryForDB($sKey, "'", 'MYSQL')."','".CTOOLS_ValidateQueryForDB($sValue, "'", 'MYSQL')."')";
	}
	mysqli_query($DBLink, $sQ);
  }
  else
  {
    $iErrCode = -3;
    $sErrMsg = mysqli_error($DBLink);
    if (CTOOLS_IsInFlag(DEBUG, DEBUG_ERROR))
      error_log ("[".$sThisFile.":".__LINE__."] [ERROR] [$iErrCode] $sErrMsg\n", 3, LOG_FILENAME);
  }

  return $iErrCode;
} // end of SCANPayment_ReadPreferenceFromDBForKey
?>
