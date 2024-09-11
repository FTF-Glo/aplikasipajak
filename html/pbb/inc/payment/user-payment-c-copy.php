<?php
class SCANPaymentPointUser
{
  private $iDebug = 0;
  private $sLogFilename = "";

  private $DBLink = NULL;

  private $sThisFile;
  
  private $iErrCode = 0;
  private $sErrMsg = '';

  public function __construct($iDebug = 0, $sLogFilename, $DBLink)
  {
    $this->iDebug = $iDebug;
    $this->sLogFilename = $sLogFilename;

    $this->DBLink = $DBLink;

    $this->sThisFile = basename(__FILE__);
  } // end of __construct

  private function SetError($iErrCode=0, $sErrMsg='')
  {
    $this->iErrCode = $iErrCode;
    $this->sErrMsg = $sErrMsg;
  } // end of SetError

  public function GetLastError(&$iErrCode, &$sErrMsg)
  {
    $iErrCode = $this->iErrCode;
    $sErrMsg = $this->sErrMsg;
  } // end of GetLastError

  public function IsAuthUser($sUName, $sUPwd, &$sUID)
  {
    $bAuth = false;

    // -- $sQ = "select * from CPCCORE_USER where CPC_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."' and CPC_U_PWD='".md5($sUPwd)."'";
	$sQ = "select * from central_user where CTR_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."' and CTR_U_PWD='".md5($sUPwd)."'";

	//echo $sQ."<br>";
    if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [sQ] $sQ\n", 3, $this->sLogFilename);
    if ($res = mysqli_query($this->DBLink, $sQ))
    {
      $nRes = mysqli_num_rows($res);
      $nRecord = $nRes;
      if ($nRes > 0)
      {
        $row = mysqli_fetch_array($res);
        // -- $sUID = $row['CPC_U_ID'];
       $sUID = $row['CTR_U_ID'];
	$bAuth = true;
      }
    }
    else
    {
      $this->iErrCode = -3;
      $this->sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFilename);
      //fb($sQ);
    }

    return $bAuth;
  } // end of IsAuthUser
  
  public function IsExistUser($sUName, &$sSavedUID)
  {
    $bExist = false;

    // -- $sQ = "select * from CPCCORE_USER where CPC_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."'";
    $sQ = "select * from central_user where CTR_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."'";

	if ($res = mysqli_query($this->DBLink, $sQ))
    {
      $nRes = mysqli_num_rows($res);
      if ($nRes > 0)
      {
        if ($row = mysqli_fetch_array($res))
        {
          // -- $sSavedUID = $row['CPC_U_ID'];
	   $sSavedUID = $row['CTR_U_ID'];
          $bExist = true;
        }
      }
    }
    else
    {
      $this->iErrCode = -3;
      $this->sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFilename);
    }

    return $bExist;
  } // end of IsExistUser

  public function ChangePassword($sUID, $sUName, $sUPwd)
  {
    $bChanged = false;

    // -- $sQ = "update CPCCORE_USER set CPC_U_PWD='".md5($sUPwd)."' where CPC_U_ID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' and CPC_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."'";
    $sQ = "update ctr_user set CTR_U_PWD='".md5($sUPwd)."' where CTR_U_ID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' and CTR_U_UID='".CTOOLS_ValidateQueryForDB($sUName, "'", "MYSQL")."'";

	if (mysqli_query($this->DBLink, $sQ))
    {
      $bChanged = true;
    }
    else
    {
      $this->iErrCode = -3;
      $this->sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFilename);
    }

    return $bChanged;
  } // end of ChangePassword

  public function GetModulesPermissions($sUID, &$aPerms)
  {
    // remove all existing elements
    CTOOLS_ArrayRemoveAllElement($aPerms);

    $bOK = false;

    $sQ="select M.CPC_M_ID MID, M.CPC_M_NAME MNAME, M.CPC_M_VARNAME MVARNAME, M.CPC_M_AUTOLOAD MAUTOLOAD, 
P.CPC_RM_AR_CUST1 MPERMS1, P.CPC_RM_AR_CUST2 MPERMS2, P.CPC_RM_AR_CUST3 MPERMS3, P.CPC_RM_AR_CUST4 MPERMS4, 
P.CPC_RM_AR_CUST5 MPERMS5, M.CPC_M_TYPE MTYPE from cpccore_role_user_module P, 
CPCCORE_MODULES M, cpccore_role_user_module_TO_MODULE M2M,
CPCCORE_USER_ROLE_USER_MODULE RUM 
where
M2M.CPC_RM2M_RID=P.CPC_RM_ID
and M2M.CPC_RM2M_MID=M.CPC_M_ID 
and RUM.CPC_URUM_M2MID=M2M.CPC_RM2M_ID
and M.CPM_M_ISPPMODULE=1
and RUM.CPC_URUM_UID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' order by M.CPC_M_TYPE asc, M.CPC_M_INSTALLED asc";
//var_dump( $sQ);
    if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
    if ($res = mysqli_query($this->DBLink, $sQ))
    {
      $bOK = true;
      $nRes = mysqli_num_rows($res);
      $nRecord = $nRes;
      if ($nRes > 0)
      {
        $i = 0;
        while ($row = mysqli_fetch_array($res))
        {
          $aPerms[$i][0] = $row['MID'];
          $aPerms[$i][1] = $row['MNAME'];
          $aPerms[$i][2] = $row['MVARNAME'];
          $aPerms[$i][3] = $row['MTYPE'];
          $aPerms[$i][4] = array();
          $aPerms[$i][4][0] = $row['MPERMS1'];
          $aPerms[$i][4][1] = $row['MPERMS2'];
          $aPerms[$i][4][2] = $row['MPERMS3'];
          $aPerms[$i][4][3] = $row['MPERMS4'];
          $aPerms[$i][4][4] = $row['MPERMS5'];
          $aPerms[$i][5] = $row['MAUTOLOAD'];
          $i++;
        }
      }
    }
    else
    {
      $iErrCode = -3;
      $sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFilename);
    }

    return $bOK;
  } // end of GetUserModulesPermissions


  public function GetCentralModulesPermissions($sUID, &$aPerms)
  {
    // remove all existing elements
    CTOOLS_ArrayRemoveAllElement($aPerms);

    $bOK = false;

    $sQ="select M.CPC_M_ID MID, M.CPC_M_NAME MNAME, M.CPC_M_VARNAME MVARNAME, M.CPC_M_AUTOLOAD MAUTOLOAD, 
P.CPC_RM_AR_CUST1 MPERMS1, P.CPC_RM_AR_CUST2 MPERMS2, P.CPC_RM_AR_CUST3 MPERMS3, P.CPC_RM_AR_CUST4 MPERMS4, 
P.CPC_RM_AR_CUST5 MPERMS5, M.CPC_M_TYPE MTYPE from cpccore_role_user_module P, 
CPCCORE_MODULES M, cpccore_role_user_module_TO_MODULE M2M,
CPCCORE_USER_ROLE_USER_MODULE RUM 
where
M2M.CPC_RM2M_RID=P.CPC_RM_ID
and M2M.CPC_RM2M_MID=M.CPC_M_ID 
and RUM.CPC_URUM_M2MID=M2M.CPC_RM2M_ID
and M.CPM_M_ISPPMODULE=0
and RUM.CPC_URUM_UID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' order by M.CPC_M_TYPE asc, M.CPC_M_INSTALLED asc";
    if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
    if ($res = mysqli_query($this->DBLink, $sQ))
    {
      $bOK = true;
      $nRes = mysqli_num_rows($res);
      $nRecord = $nRes;
      if ($nRes > 0)
      {
        $i = 0;
        while ($row = mysqli_fetch_array($res))
        {
          $aPerms[$i][0] = $row['MID'];
          $aPerms[$i][1] = $row['MNAME'];
          $aPerms[$i][2] = $row['MVARNAME'];
          $aPerms[$i][3] = $row['MTYPE'];
          $aPerms[$i][4] = array();
          $aPerms[$i][4][0] = $row['MPERMS1'];
          $aPerms[$i][4][1] = $row['MPERMS2'];
          $aPerms[$i][4][2] = $row['MPERMS3'];
          $aPerms[$i][4][3] = $row['MPERMS4'];
          $aPerms[$i][4][4] = $row['MPERMS5'];
          $aPerms[$i][5] = $row['MAUTOLOAD'];
          $i++;
        }
      }
    }
    else
    {
      $iErrCode = -3;
      $sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_ERROR))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$this->sErrMsg."\n", 3, $this->sLogFilename);
    }

    return $bOK;
  } // end of GetUserCentralModulesPermissions

  
  public function GetModulePermissionFromArrayByID($sModID, $iIdx, $aModPerms)
  {
    $iPerm = 0;
    $bFound = false; $i = 0; $n = sizeof($aModPerms);
    while (($i <= $n) && (!$bFound))
    {
      if ($aModPerms[$i][0] == $sModID)
      {
        $iPerm = $aModPerms[$i][4][$iIdx];
        $bFound = true;
      }
      else $i++;
    }

    //if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
    //  error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] iPerm [$iPerm]\n", 3, $this->sLogFilename);

    return $iPerm;
  } // end of GetModulePermissionFromArrayByID

  public function GetArrayModulePermissionFromArrayByID($sModID, $aModPerms)
  {
    $aPerm = Array();
    $bFound = false; $i = 0; $n = sizeof($aModPerms);
    while (($i <= $n) && (!$bFound))
    {
      if ($aModPerms[$i][0] == $sModID)
      {
        $aPerm = $aModPerms[$i][4];
        $bFound = true;
      }
      else $i++;
    }

    //if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
    //  error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".basename(__FILE__).":".__LINE__."] [DEBUG] iPerm [$iPerm]\n", 3, $this->sLogFilename);

    return $aPerm;
  } // end of GetArrayModulePermissionFromArrayByID

  public function isBlockedUser($sPPID, $sUID)
  {
    $bBlocked = true;
	$hitung = 1;
    // -- $sQ = "select * from CPCCORE_PAYMENT_POINT_USER_BLOCK where CPC_PPUB_UID='".CTOOLS_ValidateQueryForDB($sUID, "'", "MYSQL")."' and CPC_PPUB_ID='".$sPPID."'";
   	$sQ = "select * from central_user where CTR_U_UID='$sUID'";
	// echo $sQ;
    if ($res = mysqli_query($this->DBLink, $sQ))
    {
      $nRes = mysqli_num_rows($res);
      $nRecord = $nRes;
      if ($nRes > 0)
      {
        $row = mysqli_fetch_array($res);        
        // -- $bBlocked = ($row['CPC_PPUB_BLOCKED']==1);
	$bBlocked = (0==1);
      }
    }
    else
    {
      $iErrCode = -3;
      $sErrMsg = mysqli_error($this->DBLink);
      if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
        error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sLogFilename.":".__LINE__."] [ERROR] [".$this->iErrCode."] ".$his->sErrMsg."\n", 3, $sLogFileName);
      
	//fb($sQ);
     }

    return false;
  } // end of isBlockedUser

  public function CountModulesFromArray($aModPerms)
  {
    $nMod = 0;

    foreach($aModPerms as $val)
    {
      if ($val[3] == 1) $nMod++;
    }

    return $nMod;
  } // end of CountModulesFromArray

} // end of SCANPaymentPointUser
?>
