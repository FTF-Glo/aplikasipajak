<?php
class SCANCentralSetting {
	private $iDebug = 0;
	private $sLogFilename = "";
	private $DBLink = NULL;
	private $sThisFile;
	private $iErrCode = 0;
	private $sErrMsg = '';

	public function __construct($iDebug = 0, $sLogFilename, $DBLink) {
		$this->iDebug = $iDebug;
		$this->sLogFilename = $sLogFilename;
		$this->DBLink = $DBLink;
		$this->sThisFile = basename(__FILE__);
	}

	private function SetError($iErrCode=0, $sErrMsg='') {
		$this->iErrCode = $iErrCode;
		$this->sErrMsg = $sErrMsg;
	}

	public function GetLastError(&$iErrCode, &$sErrMsg) {
		$iErrCode = $this->iErrCode;
		$sErrMsg = $this->sErrMsg;
	}
	
	//Backward Compatibility
	public function DeleteArea($areaId) {
		return $this->DeleteApp($areaId);
	}

	public function DeleteApp($appId) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
	
		// DELETE APP
		$sQ = "delete from central_app where CTR_A_ID = '" . $appId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// DELETE USER TO APP
		if (!$bOK) {
			return false;
		}
		$sQ = "delete from central_user_to_app where CTR_APP_ID = '" . $appId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	public function InsertArea($id, $name, $desc, $dbId, $query) {
		return $this->InsertApp($id, $name, $desc, $dbId, $query);
	}

	public function InsertApp($id, $name, $desc, $dbId, $query) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$query = mysqli_real_escape_string($this->DBLink, $query);
	
		$sQ = "insert into central_app values (" .
			"'" . $id . "', " .
			"'" . $name . "', " .
			"'" . $desc . "', " .
			"NOW(), " .
			"'" . $query . "', " .
			"'" . $dbId . "' ) ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function CopyArea($id, $name, $desc, $copyApp) {
		return $this->CopyApp($id, $name, $desc, $copyApp);
	}
	public function CopyApp($id, $name, $desc, $copyApp) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		
		// 1 copy app
		$arAppCopy = $this->GetAppDetail($copyApp);
		$dbId = $arAppCopy["db"];
		$query = $arAppCopy["query"];
		$query = mysqli_real_escape_string($this->DBLink, $query);
	
		$sQ = "insert into central_app values (" .
			"'" . $id . "', " .
			"'" . $name . "', " .
			"'" . $desc . "', " .
			"NOW(), " .
			"'" . $query . "', " .
			"'" . $dbId . "' ) ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// -- Addendum --
		
		// 2 copy module
		$arModuleInsert = $this->GetModuleIdInApp($copyApp);
		$this->InsertModuleAccessable($id, $arModuleInsert);
		
		// 3 copy app config
		$arAppConfig = $this->GetAppConfig($copyApp);
		foreach ($arAppConfig as $config) {
			$key = $config["key"];
			$value = $config["value"];
		
			$this->InsertAppConfig($id, $key, $value);
		}
		
		// 4 copy role
		$arRoleId = $this->GetRoleIdInApp($copyApp);
		foreach ($arRoleId as $iRole) {
			$userId = $iRole["userId"];
			$rmId = $iRole["rmId"];
			
			$this->ChangeRole($userId, $id, $rmId);
		}
		
		return $bOK;
	}
	public function GetRoleIdInArea($app) {
		return $this->GetRoleIdInApp($app);
	}
	public function GetRoleIdInApp($app) {
		// FIX: mysql escape string
		$app = mysqli_real_escape_string($this->DBLink, $app);
		
		$sQ = "SELECT * FROM central_user_to_app C WHERE CTR_APP_ID = '" . $app . "' ";
		// echo $sQ;
		$bOK = false;
		$arApp = null;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				$arApp = array();
				while ($row = mysqli_fetch_array($res)) {
					$arApp[$i]["userId"] = $row["CTR_USER_ID"];
					$arApp[$i]["rmId"] = $row["CTR_RM_ID"];
					$i++;
				}
			}
		}
		
		return $arApp;
	}
	
	public function GetModuleIdInArea($app) {
		return $this->GetModuleIdInApp($app);
	}
	public function GetModuleIdInApp($app) {
		// FIX: mysql escape string
		$app = mysqli_real_escape_string($this->DBLink, $app);
		
		CTOOLS_ArrayRemoveAllElement($moduleIds);
		$bOK = false;
		
		$sQ = "SELECT DISTINCT F.CTR_RM2F_MID " .
				"FROM central_user_to_app C, central_role_module_to_function F, central_module M, central_app_to_module AM " .
				"WHERE C.CTR_RM_ID = F.CTR_RM2F_ID AND M.CTR_M_ID = F.CTR_RM2F_MID AND AM.CTR_AM_MID = F.CTR_RM2F_MID AND AM.CTR_AM_AID = C.CTR_APP_ID AND ";
		if ($app != "") {
			$sQ .= "C.CTR_APP_ID = '" . $app . "' ";
		}
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$moduleIds[$i] = $row["CTR_RM2F_MID"];
					// $moduleIds[$i]["id"] = $row["CTR_RM2F_MID"];
					// $moduleIds[$i]["name"] = $row["CTR_M_NAME"];
					// $moduleIds[$i]["priv"] = $row["CTR_RM2F_PRIV"];
					$i++;
				}
			}
		}
		return $moduleIds;
	}
	
	public function InsertModuleAccessable($appId, $arModuleInsert) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$bOK = false;
		
		$sQ = "INSERT INTO central_app_to_module VALUES ";
		$first = true;
		foreach ($arModuleInsert as $modInsert) {
			// FIX: mysql escape string
			$modInsert = mysqli_real_escape_string($this->DBLink, $modInsert);
			
			if ($first) {
				$first = false;
			} else {
				$sQ .= ", ";
			}
			$sQ .= "('$appId', '$modInsert')";
		}
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditArea($id, $name, $desc, $dbId, $query) {
		return $this->EditApp($id, $name, $desc, $dbId, $query) ;	
	}
	public function EditApp($id, $name, $desc, $dbId, $query) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$query = mysqli_real_escape_string($this->DBLink, $query);
		
		$sQ = "update central_app set " .
			"CTR_A_NAME = '" . $name . "', " .
			"CTR_A_DESC = '" . $desc . "', " .
			"CTR_A_DB = '" . $dbId . "', " .
			"CTR_A_QUERY = '" . $query . "' " .
			"where CTR_A_ID = '" . $id . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function GetArea(&$arApp) {
		return $this->GetApp($arApp) ;
	}
	public function GetApp(&$arApp) {
		CTOOLS_ArrayRemoveAllElement($arApp);
		$bOK = false;
		
		$sQ = "select * from central_app order by LPAD(CTR_A_ID,11,'0') asc"; 
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arApp[$i]["id"] = $row["CTR_A_ID"];
					$arApp[$i]["name"] = $row["CTR_A_NAME"];
					$arApp[$i]["desc"] = $row["CTR_A_DESC"];
					$arApp[$i]["query"] = $row["CTR_A_QUERY"];
					$arApp[$i]["db"] = $row["CTR_A_DB"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	public function GetAreaDetail($appId) {
		return $this->GetAppDetail($appId);
	}
	public function GetAppDetail($appId) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		
		$sQ = "select * from central_app where CTR_A_ID = '" . $appId . "' ";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arApp = array();
					$arApp["id"] = $row["CTR_A_ID"];
					$arApp["name"] = $row["CTR_A_NAME"];
					$arApp["desc"] = $row["CTR_A_DESC"];
					$arApp["db"] = $row["CTR_A_DB"];
					$arApp["query"] = $row["CTR_A_QUERY"];
				}
			}
		}
		
		return $arApp;
	}
	
	public function GetAreaConfig($appId) {
		return $this->GetAppConfig($appId) ;
	}
	public function GetAppConfig($appId) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		
		$arConfig = null;
		$sQ = "select * from central_app_config where CTR_AC_AID = '" . $appId . "' " . 
				"order by CTR_AC_KEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				$arConfig = array();
				while ($row = mysqli_fetch_array($res)) {
					$arConfig[$i]["key"] = $row["CTR_AC_KEY"];
					$arConfig[$i]["value"] = $row["CTR_AC_VALUE"];
					$i++;
				}
			}
		}
		
		return $arConfig;
	}
	
	public function GetAreaConfigValue($appId, $key) {
		return $this->GetAppConfigValue($appId, $key);
	}
	public function GetAppConfigValue($appId, $key) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "select CTR_AC_VALUE from central_app_config " .
				"where CTR_AC_AID = '" . $appId . "' " . 
				"and CTR_AC_KEY = '" . $key . "' " .
				"order by CTR_AC_KEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$value = $row["CTR_AC_VALUE"];
					return $value;
				}
			}
		}
		
		return null;
	}

	public function GetAppConfigValueByKey($appId, $key) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "select CTR_AC_KEY,CTR_AC_VALUE from central_app_config " .
				"where CTR_AC_AID = '" . $appId . "' " . 
				"and CTR_AC_KEY like '" . $key . "%'" .
				"order by CTR_AC_KEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				while ($row = mysqli_fetch_array($res)) {
					$value[$row["CTR_AC_KEY"]] = $row["CTR_AC_VALUE"];
					
				}
			}
			mysqli_free_result($res);
			return $value;
		}
		
		return null;
	}
	public function DeleteAreaConfig($appId, $key) {
		return $this->DeleteAppConfig($appId, $key);
	}
	public function DeleteAppConfig($appId, $key) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "delete from central_app_config where CTR_AC_AID = '" . $appId . "' " .
				"and CTR_AC_KEY = '" . $key . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function InsertAreaConfig($appId, $key, $value){
		return $this->InsertAppConfig($appId, $key, $value);
	}
	public function InsertAppConfig($appId, $key, $value) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		$value = mysqli_real_escape_string($this->DBLink, $value);
		
		$sQ = "insert into central_app_config values (" .
			"'" . $appId . "', " .
			"'" . $key . "', " .
			"'" . $value . "') ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditAreaConfig($appId, $oldKey, $newKey, $newValue){
		return $this->EditAppConfig($appId, $oldKey, $newKey, $newValue);
	}
	public function EditAppConfig($appId, $oldKey, $newKey, $newValue) {
		// FIX: mysql escape string
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$oldKey = mysqli_real_escape_string($this->DBLink, $oldKey);
		$newKey = mysqli_real_escape_string($this->DBLink, $newKey);
		$newValue = mysqli_real_escape_string($this->DBLink, $newValue);
		
		$sQ = "update central_app_config set " .
			"CTR_AC_KEY = '" . $newKey . "', " .
			"CTR_AC_VALUE = '" . $newValue . "' " .
			"where CTR_AC_AID = '" . $appId . "' " .
			"and CTR_AC_KEY = '" . $oldKey . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	
	
	public function GetModule(&$arModule) {
		CTOOLS_ArrayRemoveAllElement($arModule);
		$bOK = false;
		
		$sQ = "select * from central_module order by LPAD(CTR_M_ID,11,'0') asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arModule[$i]["id"] = $row["CTR_M_ID"];
					$arModule[$i]["name"] = $row["CTR_M_NAME"];
					$arModule[$i]["desc"] = $row["CTR_M_DESC"];
					$arModule[$i]["view"] = $row["CTR_M_VIEW"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetModuleDetail($moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		$bOK = false;
		$arModule = null;
		
		$sQ = "select * from central_module where CTR_M_ID = '" . $moduleId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arModule = array();
					$arModule["id"] = $row["CTR_M_ID"];
					$arModule["name"] = $row["CTR_M_NAME"];
					$arModule["desc"] = $row["CTR_M_DESC"];
					$arModule["view"] = $row["CTR_M_VIEW"];
				}
			}
		}
		
		return $arModule;
	}
	
	public function DeleteModule($moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		// DELETE MODULE
		$sQ = "delete from central_module where CTR_M_ID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// DELETE ROLE MODULE TO FUNCTION
		if (!$bOK) {
			return false;
		}
		$sQ = "delete from central_role_module_to_function where CTR_RM2F_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// DELETE MODULE CONFIG
		if (!$bOK) {
			return false;
		}
		$sQ = "delete from central_module_config where CTR_CFG_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function InsertModule($id, $name, $desc, $view) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		$view = mysqli_real_escape_string($this->DBLink, $view);
		
		$sQ = "insert into central_module values (" .
			"'" . $id . "', " .
			"'" . $name . "', " .
			"'" . $desc . "', " .
			"'" . $view . "') " .
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditModule($id, $name, $desc, $view) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		$view = mysqli_real_escape_string($this->DBLink, $view);
		
		$sQ = "update central_module set " .
			"CTR_M_NAME = '" . $name . "', " .
			"CTR_M_DESC = '" . $desc . "', " .
			"CTR_M_VIEW = '" . $view . "' " .
			"where CTR_M_ID = '" . $id . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	
	public function GetUser(&$arUser, $filter=null) {
		CTOOLS_ArrayRemoveAllElement($arUser);
		$bOK = false;
		$sQcond = null;
		
		if (isset($filter)) {
			// $multi = (count($filter) > 1);
			
			$sQcond = "where ";
			foreach ($filter as $key => $value) {
				$sQcond .= $key ." like '%".$value."%' and ";
			}
			//omits the last 'and' if any
			$pos = strrpos($sQcond, " and ");
			if ($pos !== false) {
				//found last ' and ';
				$sQcond = substr($sQcond, 0, $pos);
			}
		}
		
		$sQ = "select * from central_user ";
		$sQ.= $sQcond;
		$sQ.= " order by LPAD(CTR_U_ID,11,'0') asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arUser[$i]["id"] = $row["CTR_U_ID"];
					$arUser[$i]["uid"] = $row["CTR_U_UID"];
					$arUser[$i]["isAdmin"] = $row["CTR_U_ADMIN"];
					$arUser[$i]["blocked"] = $row["CTR_U_BLOCKED"];
					$arUser[$i]["multLogin"] = $row["CTR_U_MULT_LOGIN"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetUserDemo(&$arUser, $filter=null) {
		CTOOLS_ArrayRemoveAllElement($arUser);
		$bOK = false;
		
		if (isset($filter)) {
			// $multi = (count($filter) > 1);
			
			$sQcond = "where ";
			foreach ($filter as $key => $value) {
				$sQcond .= $key ." like '".$value."%' and ";
			}
			//omits the last 'and' if any
			$pos = strrpos($sQcond, " and ");
			if ($pos !== false) {
				//found last ' and ';
				$sQcond = substr($sQcond, 0, $pos);
			}
		}
		
		$sQ = "select * from central_user ";
		$sQ.= $sQcond;
		$sQ.= " order by LPAD(CTR_U_ID,11,'0') asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arUser[$i]["id"] = $row["CTR_U_ID"];
					$arUser[$i]["uid"] = $row["CTR_U_UID"];
					$arUser[$i]["isAdmin"] = $row["CTR_U_ADMIN"];
					$arUser[$i]["blocked"] = $row["CTR_U_BLOCKED"];
					$arUser[$i]["multLogin"] = $row["CTR_U_MULT_LOGIN"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetUserDetail($userId) {
		// FIX: mysql escape string
		$userId = mysqli_real_escape_string($this->DBLink, $userId);
		
		$bOK = false;
		$arUser = null;
		
		$sQ = "select * from central_user where CTR_U_ID = '" . $userId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arUser = array();
					$arUser["id"] = $row["CTR_U_ID"];
					$arUser["uid"] = $row["CTR_U_UID"];
					$arUser["isAdmin"] = $row["CTR_U_ADMIN"];
					$arUser["blocked"] = $row["CTR_U_BLOCKED"];
					$arUser["multLogin"] = $row["CTR_U_MULT_LOGIN"];
				}
			}
		}
		
		return $arUser;
	}
	
	public function DeleteUser($userId) {
		// FIX: mysql escape string
		$userId = mysqli_real_escape_string($this->DBLink, $userId);
		
		// DELETE USER
		$sQ = "delete from central_user where CTR_U_ID = '" . $userId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// DELETE USER TO APP
		if (!$bOK) {
			return $bOK;
		}
		$sQ = "delete from central_user_to_app where CTR_USER_ID = '" . $userId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function InsertUser($id, $uid, $pwd, $isAdmin, $isSupervisor, $multLogin = 0,$userTheme='default') {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$uid = mysqli_real_escape_string($this->DBLink, $uid);
		$pwd = mysqli_real_escape_string($this->DBLink, $pwd);
		$md5Pwd = md5($pwd);
		
		$manageBit = 0;
		if ($isAdmin == null) {
			$isAdmin = "0";
			$manageBit = 0;
		} else {
			$manageBit = 1;
		}
		
		// NEW: Supervisor
		if ($isSupervisor == null) {
		} else {
			$manageBit += 10;
		}
		
		if ($multLogin) {
			$multLogin = 1;
		} else {
			$multLogin = 0;
		}
		$userTheme=$this->GetAppConfigValue('aAdmPajakKabSKB', 'userTheme');
		$sQ = "insert into central_user " .
			" (CTR_U_ID, CTR_U_UID, CTR_U_PWD, CTR_U_LASTUPDATE, CTR_U_LASTLOGIN, CTR_U_ADMIN, CTR_U_BLOCKED, CTR_U_MULT_LOGIN,CTR_U_STYLE) " .
			" values (" .
			"'" . $id . "', " .
			"'" . $uid . "', " .
			"'" . $md5Pwd . "', " .
			"NOW(), " .
			"0, " .
			$manageBit . ", ".
			"0, " .
			$multLogin . ",'$userTheme') ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditUser($id, $uid, $pwd, $bitAdmin="", $isAdmin, $isSupervisor, $multLogin) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$uid = mysqli_real_escape_string($this->DBLink, $uid);
		$pwd = mysqli_real_escape_string($this->DBLink, $pwd);
		// echo "pwd = $pwd<br />\n";
		
		// Admin
		$manageBit = $bitAdmin + 0;
		if ($isAdmin == null) {
			$isAdmin = "0";
			$manageBit += 0;
		} else {
			$manageBit += 1;
		}
		
		// NEW: Supervisor
		if ($isSupervisor == null) {
		} else {
			$manageBit += 10;
		}
		
		// Multiple Login
		if ($multLogin) {
			$multLogin = 1;
		} else {
			$multLogin = 0;
		}
		
		$sQ = "update central_user set " .
			"CTR_U_UID = '" . $uid . "', " .
			"CTR_U_ADMIN = " . $manageBit . ", " .
			"CTR_U_LASTUPDATE = NOW(), " .
			"CTR_U_MULT_LOGIN = $multLogin ";
		if (trim($pwd) != "") {
			$md5Pwd = md5($pwd);
			$sQ .= ", CTR_U_PWD = '" . $md5Pwd . "' ";
		}
		$sQ .= "where CTR_U_ID = '" . $id . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}

	
	public function GetRole(&$arRole) {
		CTOOLS_ArrayRemoveAllElement($arRole);
		$bOK = false;
		
		$sQ = "select * from central_role_module order by LPAD(CTR_RM_ID,11,'0') asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arRole[$i]["id"] = $row["CTR_RM_ID"];
					$arRole[$i]["name"] = $row["CTR_RM_NAME"];
					$arRole[$i]["desc"] = $row["CTR_RM_DESC"];
					$arRole[$i]["autoLoad"] = $row["CTR_RM_AUTOLOAD"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetRoleDetail($roleId) {
		// FIX: mysql escape string
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		$bOK = false;
		
		$sQ = "select * from central_role_module where CTR_RM_ID = '" . $roleId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arRole = array();
					$arRole["id"] = $row["CTR_RM_ID"];
					$arRole["name"] = $row["CTR_RM_NAME"];
					$arRole["desc"] = $row["CTR_RM_DESC"];
					$arRole["autoLoad"] = $row["CTR_RM_AUTOLOAD"];
				}
			}
		}
		
		return $arRole;
	}
	
	public function DeleteRole($roleId) {
		// FIX: mysql escape string
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		// DELETE ROLE
		$sQ = "delete from central_role_module where CTR_RM_ID = '" . $roleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		// DELETE USER TO APP
		if (!$bOK) {
			return false;
		}
		$sQ = "delete from central_user_to_app where CTR_RM_ID = '" . $roleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function InsertRole($id, $name, $desc) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		
		$sQ = "insert into central_role_module (CTR_RM_ID, CTR_RM_NAME, CTR_RM_DESC) values (" .
			"'" . $id . "', " .
			"'" . $name . "', " .
			"'" . $desc . "') ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditRole($id, $name, $desc, $autoLoad="") {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$desc = mysqli_real_escape_string($this->DBLink, $desc);
		$autoLoad = mysqli_real_escape_string($this->DBLink, $autoLoad);
		
		$sQ = "update central_role_module set " .
			"CTR_RM_NAME = '" . $name . "', " .
			"CTR_RM_DESC = '" . $desc . "', " .
			"CTR_RM_AUTOLOAD = '" . $autoLoad . "' " .
			"where CTR_RM_ID = '" . $id . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	
	public function GetFunction(&$arFunction) {
		CTOOLS_ArrayRemoveAllElement($arFunction);
		$bOK = false;
		
		$sQ = "select * from central_function order by LPAD(CTR_FUNC_MID,11,'0') asc, LPAD(CTR_FUNC_ID,11,'0') asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arFunction[$i]["id"] = $row["CTR_FUNC_ID"];
					
					// module
					$mid = $row["CTR_FUNC_MID"];
					$arModule = $this->GetModuleDetail($mid);
					if ($arModule) {
						$arFunction[$i]["mid"] = $mid;
						$arFunction[$i]["mname"] = $arModule["name"];
					}

					$arFunction[$i]["name"] = $row["CTR_FUNC_NAME"];
					$arFunction[$i]["priv"] = $row["CTR_FUNC_PRIV"];
					$arFunction[$i]["page"] = $row["CTR_FUNC_PAGE"];
					$arFunction[$i]["image"] = $row["CTR_FUNC_IMAGE"];
					$arFunction[$i]["pos"] = $row["CTR_FUNC_POS"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetFunctionDetail($functionId) {
		// FIX: mysql escape string
		$functionId = mysqli_real_escape_string($this->DBLink, $functionId);
		
		$bOK = false;
		$arFunction = null;
		
		$sQ = "select * from central_function where CTR_FUNC_ID = '" . $functionId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arFunction = array();
					$arFunction["id"] = $row["CTR_FUNC_ID"];
					$arFunction["mid"] = $row["CTR_FUNC_MID"];
					$arFunction["name"] = $row["CTR_FUNC_NAME"];
					$arFunction["priv"] = $row["CTR_FUNC_PRIV"];
					$arFunction["page"] = $row["CTR_FUNC_PAGE"];
					$arFunction["image"] = $row["CTR_FUNC_IMAGE"];
					$arFunction["pos"] = $row["CTR_FUNC_POS"];
				}
			}
		}
		
		return $arFunction;
	}
	
	public function DeleteFunction($functionId) {
		// FIX: mysql escape string
		$functionId = mysqli_real_escape_string($this->DBLink, $functionId);
		
		// DELETE FUNCTION
		$sQ = "delete from central_function where CTR_FUNC_ID = '" . $functionId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function GetNextPrivFunction($moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		$sQ = "SELECT (MAX(CTR_FUNC_PRIV) * 2) AS MAX FROM central_function " .
				"WHERE CTR_FUNC_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextUserId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_U_ID, 2) AS UNSIGNED)) + 1) AS MAX FROM central_user";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextRoleId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_RM_ID, 3) AS UNSIGNED)) + 1) AS MAX FROM central_role_module";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextFunctionId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_FUNC_ID, 2) AS UNSIGNED)) + 1) AS MAX FROM central_function";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextAreaId() {
		return $this->GetNextAppId();
	}
	public function GetNextAppId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_A_ID, 2) AS UNSIGNED)) + 1) AS MAX FROM central_app";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextModuleId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_M_ID, 2) AS UNSIGNED)) + 1) AS MAX FROM central_module";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextDbId() {
		$sQ = "SELECT (MAX(CAST(SUBSTRING(CTR_DB_ID, 2) AS UNSIGNED)) + 1) AS MAX FROM central_database";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function GetNextPosDatabaseCfg($dbId) {
		// FIX: mysql escape string
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		
		$sQ = "SELECT (MAX(CTR_DB_POS) + 1) AS MAX FROM central_database_config WHERE CTR_DB_AID = '" . $dbId . "' ";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function InsertFunction($id, $mid, $name, $page, $image, $pos) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$mid = mysqli_real_escape_string($this->DBLink, $mid);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$page = mysqli_real_escape_string($this->DBLink, $page);
		$image = mysqli_real_escape_string($this->DBLink, $image);
		
		if ($mid == "0") {
			// Module belum dipilih
			return false;
		}
	
		// if (strlen(trim($priv)) == 0) {
			// $priv = 0;
		// }
		
		$priv = $this->GetNextPrivFunction($mid);
	
		$sQ = "insert into central_function values (" .
			"'" . $id . "', " .
			"'" . $mid . "', " .
			"'" . $name . "', " .
			$priv . ", " .
			"'" . $page . "', " .
			"'" . $image . "', " .
			$pos . ") ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditFunction($id, $mid, $name, $page, $image, $pos) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$mid = mysqli_real_escape_string($this->DBLink, $mid);
		$name = mysqli_real_escape_string($this->DBLink, $name);
		$page = mysqli_real_escape_string($this->DBLink, $page);
		$image = mysqli_real_escape_string($this->DBLink, $image);
		
		$sQ = "update central_function set " .
			"CTR_FUNC_MID = '" . $mid . "', " .
			"CTR_FUNC_NAME = '" . $name . "', " .
			"CTR_FUNC_PAGE = '" . $page . "', " .
			"CTR_FUNC_IMAGE = '" . $image . "', " .
			"CTR_FUNC_POS = '" . $pos . "' " .
			"where CTR_FUNC_ID = '" . $id . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function GetFunctionInModule($roleId, $moduleId, &$arFunction) {
		// FIX: mysql escape string
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		CTOOLS_ArrayRemoveAllElement($arFunction);
		$bOK = false;
		
		if ($roleId != "") {
			// DEPRECATED: Query lama, tidak memunculkan module yang tidak dimiliki dari role
			$sQ = "SELECT * FROM central_role_module_to_function MF, central_function C " .
				"WHERE MF.CTR_RM2F_MID = C.CTR_FUNC_MID AND MF.CTR_RM2F_ID = '" . $roleId . "' " .
				" AND MF.CTR_RM2F_MID = '" . $moduleId . "' ORDER BY LPAD(CTR_FUNC_ID, 11, '0')";
		} else {
			// $sQ = "SELECT DISTINCT CTR_FUNC_ID, CTR_FUNC_MID, CTR_FUNC_PRIV, CTR_FUNC_NAME, CTR_FUNC_PAGE, CTR_FUNC_IMAGE " .
				// "FROM central_role_module_to_function MF, central_function C " .
				// "WHERE MF.CTR_RM2F_MID = C.CTR_FUNC_MID AND MF.CTR_RM2F_MID = '" . $moduleId . "' ";
			$sQ = "SELECT * FROM central_function C WHERE CTR_FUNC_MID = '" . $moduleId . "' ORDER BY LPAD(CTR_FUNC_ID, 11, '0')";
		}
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arFunction[$i]["id"] = $row["CTR_FUNC_ID"];
					$arFunction[$i]["mid"] = $row["CTR_FUNC_MID"];
					$arFunction[$i]["funcPriv"] = $row["CTR_FUNC_PRIV"];
					$arFunction[$i]["name"] = $row["CTR_FUNC_NAME"];
					$arFunction[$i]["page"] = $row["CTR_FUNC_PAGE"];
					$arFunction[$i]["image"] = $row["CTR_FUNC_IMAGE"];
					$arFunction[$i]["pos"] = $row["CTR_FUNC_POS"];
					$i++;
				}
			}
		}
		return $bOK;
	}
	
	public function IsModuleGrantedInRole($moduleId, $roleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		$bOK = false;

		$sQ = "SELECT * " .
				"FROM central_role_module_to_function F " .
				"WHERE F.CTR_RM2F_MID = '" . $moduleId . "' " .
				"AND F.CTR_RM2F_ID = '" . $roleId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
			}
		}
		
		return $bOK;
	}
	
	public function IsFunctionGrantedInRole($moduleId, $funcId, $roleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$funcId = mysqli_real_escape_string($this->DBLink, $funcId);
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		$bOK = false;

		$sQ = "SELECT DISTINCT CF.CTR_FUNC_PRIV, F.CTR_RM2F_PRIV " .
				"FROM central_role_module_to_function F, central_function CF " .
				"WHERE CF.CTR_FUNC_MID = F.CTR_RM2F_MID " .
				"AND F.CTR_RM2F_MID = '" . $moduleId . "' " .
				"AND CF.CTR_FUNC_ID = '" . $funcId . "' " .
				"AND F.CTR_RM2F_ID = '" . $roleId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$userPriv = $row["CTR_FUNC_PRIV"];
				$priv = $row["CTR_RM2F_PRIV"];
				
				// convert to int
				$priv += 0;
				$userPriv += 0;
				
				// echo $priv . " = " . $userPriv;
				// echo " || ";
				
				$bOK = (($userPriv & $priv) != 0);
				// echo ($userPriv & $priv);
				// echo "<br /><br />\n";
			}
		}
		
		return $bOK;
	}
	
	public function GetPrivilege($functionId) {
		// FIX: mysql escape string
		$functionId = mysqli_real_escape_string($this->DBLink, $functionId);
		
		$sQ = "SELECT CTR_FUNC_PRIV FROM central_function C WHERE C.CTR_FUNC_ID = '" . $functionId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$priv = $row["CTR_FUNC_PRIV"];
				return $priv;
			}
		}
	}
	
	public function GetRolePrivilege($roleId, $moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		$sQ = "SELECT CTR_RM2F_PRIV FROM central_role_module_to_function C " .
				"WHERE CTR_RM2F_ID = '" . $roleId . "' " .
				"AND CTR_RM2F_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$rolePriv = $row["CTR_RM2F_PRIV"];
				return $rolePriv;
			}
		}
		return 0;
	}
	
	public function GrantFunction($roleId, $moduleId, $functionId, $granted) {
		// FIX: mysql escape string
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$functionId = mysqli_real_escape_string($this->DBLink, $functionId);
		
		$all = ($functionId == "all" && $functionId != "");
		
		// echo "role = $roleId\n";
		// echo "module = $moduleId\n";
		// echo "function = $functionId\n";
		
		$modulePriv = 0;
		$arFunction = null;
		$bOK = $this->GetFunctionInModule("", $moduleId, $arFunction);
		if ($bOK) {
			if ($all && !$granted) {
				// do nothing
			} else {
				foreach($arFunction as $func) {
					$id = $func["id"];
					
					// DEPRECATED: mengambil role privilege dari fungsi khusus
					// $rolePriv = $func["rolePriv"];
					$rolePriv = $this->GetRolePrivilege($roleId, $moduleId);
					$priv = $func["funcPriv"];
					
					// echo "id = $id\n";
					// echo "<br />\n";
					// echo "modulePriv = $modulePriv\n";
					// echo "<br />\n";
					// echo "priv = $priv\n";
					// echo "<br />\n";
					// echo "functionId = $functionId\n";
					// echo "<br />\n";
					// echo "rolePriv = $rolePriv\n";
					// echo "<br />\n";
					// echo "<br />\n";
					
					// convert to int
					$rolePriv += 0;
					$priv += 0;
					
					if ($granted) {
						// Grant permission
						if (($all) || ($id == $functionId) || (($rolePriv & $priv) != 0)) {
							// echo "<b>Grant!</b>\n";
							// echo "<br />\n";
							$modulePriv |= $priv;
						}
					} else {
						// Decline permission
						if (($id != $functionId) && (($rolePriv & $priv) != 0)) {
							// echo "<b>Decline!</b>\n";
							// echo "<br />\n";
							$modulePriv |= $priv;
						}
					}
				}
			}
		}
		
		$grantedModule = $this->IsModuleGrantedInRole($moduleId, $roleId);
		if ($grantedModule) {
			$sQ = "update central_role_module_to_function set " .
				"CTR_RM2F_PRIV = '" . $modulePriv . "' " .
				"where CTR_RM2F_ID = '" . $roleId . "' " .
				"and CTR_RM2F_MID = '" . $moduleId . "' ";
		} else {
			$sQ = "insert into central_role_module_to_function values (" .
				"'" . $roleId . "', " .
				"'" . $moduleId . "', " .
				"'" . $modulePriv . "') ";
		}
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function GetRoleInArea($userId, $appId) {
		return $this->GetRoleInApp($userId, $appId) ;
	}
	public function GetRoleInApp($userId, $appId) {
		// FIX: mysql escape string
		$userId = mysqli_real_escape_string($this->DBLink, $userId);
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		
		$sQ = "SELECT CTR_RM_ID FROM central_user_to_app C " .
				"WHERE C.CTR_USER_ID = '" . $userId . "' " .
				"AND C.CTR_APP_ID = '" . $appId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$row = mysqli_fetch_array($res);
				$role = $row["CTR_RM_ID"];
				return $role;
			}
		}
		
		return null;
	}
	
	public function ChangeRole($userId, $appId, $roleId) {
		// FIX: mysql escape string
		$userId = mysqli_real_escape_string($this->DBLink, $userId);
		$appId = mysqli_real_escape_string($this->DBLink, $appId);
		$roleId = mysqli_real_escape_string($this->DBLink, $roleId);
		
		$bOK = false;
		
		if ($roleId == "-1") {
			// Delete
			// echo "Decline";
			$sQ = "delete from central_user_to_app where CTR_USER_ID = '" . $userId . "' " .
					"AND CTR_APP_ID = '" . $appId . "' ";
		} else {
			$rmId = $this->GetRoleInApp($userId, $appId);
			if ($rmId) {
				// Update
				// echo "Update";
				$sQ = "update central_user_to_app set " .
						"CTR_RM_ID = '" . $roleId . "' " .
						"where CTR_USER_ID = '" . $userId . "' " .
						"AND CTR_APP_ID = '" . $appId . "' ";
			} else {
				// Insert
				// $newRoleId = $this->GetNextRoleId();
				$sQ = "insert into central_user_to_app values (" .
						// "'" . $newRoleId . "', " .
						"'" . $userId . "', " .
						"'" . $appId . "', " .
						"'" . $roleId . "') ";
			}
		}
		
		// echo $sQ;
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}	
		return $bOK;
	}
	

	
	public function GetModuleConfig($moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		$arConfig = null;
		
		$sQ = "select * from central_module_config where CTR_CFG_MID = '" . $moduleId . "' " . 
				"order by CTR_CFG_MKEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				$arConfig = array();
				while ($row = mysqli_fetch_array($res)) {
					$arConfig[$i]["key"] = $row["CTR_CFG_MKEY"];
					$arConfig[$i]["value"] = $row["CTR_CFG_MVALUE"];
					$i++;
				}
			}
		}
		
		return $arConfig;
	}
	
	public function GetModuleConfigValue($funcId, $key) {
		// FIX: mysql escape string
		$funcId = mysqli_real_escape_string($this->DBLink, $funcId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "select CTR_CFG_MVALUE from central_module_config " .
				"where CTR_CFG_MID = '" . $funcId . "' " . 
				"and CTR_CFG_MKEY = '" . $key . "' " .
				"order by CTR_CFG_MKEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$value = $row["CTR_CFG_MVALUE"];
					return $value;
				}
			}
		}
		
		return null;
	}
	
	public function DeleteAllModuleConfig($moduleId) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		$sQ = "delete from central_module_config where CTR_CFG_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function DeleteModuleConfig($moduleId, $key) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "delete from central_module_config where CTR_CFG_MID = '" . $moduleId . "' " .
				"and CTR_CFG_MKEY = '" . $key . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function InsertModuleConfig($moduleId, $key, $value) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		$value = mysqli_real_escape_string($this->DBLink, $value);
		
		// trim all
		$moduleId = trim($moduleId);
		$key = trim($key);
		$value = trim($value);
		
		$sQ = "insert into central_module_config values (" .
			"'" . $moduleId . "', " .
			"'" . $key . "', " .
			"'" . $value . "') ";
		 //echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditModuleConfig($moduleId, $oldKey, $newKey, $newValue) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		$oldKey = mysqli_real_escape_string($this->DBLink, $oldKey);
		$newKey = mysqli_real_escape_string($this->DBLink, $newKey);
		$newValue = mysqli_real_escape_string($this->DBLink, $newValue);
		
		// trim all
		$moduleId = trim($moduleId);
		$oldKey = trim($oldKey);
		$newKey = trim($newKey);
		$newValue = trim($newValue);
		
		$sQ = "update central_module_config set " .
			"CTR_CFG_MKEY = '" . $newKey . "', " .
			"CTR_CFG_MVALUE = '" . $newValue . "' " .
			"where CTR_CFG_MID = '" . $moduleId . "' " .
			"and CTR_CFG_MKEY = '" . $oldKey . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	
	public function EditModuleOnpaysConfiguration($moduleId, $arConfigKey) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		// Query
		$sQ = "update central_module_locket set CTR_L_MVALUE = case CTR_L_MKEY ";
		foreach ($arConfigKey as $cKey => $cValue) {
			$sQ .= "when '" . $cKey . "' then '" . $cValue . "' ";
		}
		$sQ .= "end where CTR_L_MID = '" . $moduleId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function GetModuleOnpaysConfiguration($moduleId, $arConfigKey, &$arModConf) {
		// FIX: mysql escape string
		$moduleId = mysqli_real_escape_string($this->DBLink, $moduleId);
		
		CTOOLS_ArrayRemoveAllElement($arModConf);
		$sQ = "SELECT * FROM central_module_locket WHERE CTR_L_MID = '" . $moduleId . "' " .
				"ORDER BY CTR_L_MKEY ASC";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				$bOK = true;
				$arModConf = array();
				while ($row = mysqli_fetch_array($res)) {
					// $arModConf[$i]["mid"] = $row["CPC_L_MID"];
					// $arModConf[$i]["key"] = $row["CPC_L_MKEY"];
					// $arModConf[$i]["value"] = $row["CPC_L_MVALUE"];
					$key = $row["CTR_L_MKEY"];
					$value = $row["CTR_L_MVALUE"];
					$arModConf[$key] = $value;
					$i++;
				}
			}
			
			$len = count($arConfigKey);
			if ($len > $nRes) {
				$sQ = "INSERT INTO central_module_locket VALUES ";
				$first = true;
				foreach ($arConfigKey as $cKey) {
					$key = $cKey["key"];
					// Key yang di-insert hanya jika jumlah row < jumlah arConfigKey
					if (!isset($arModConf[$key])) {
						if ($first) {
							$first = false;
						} else {
							$sQ .= ", ";
						}
						$sQ .= "(" . 
							"'" . $moduleId . "', " .
							"'" . $key . "', " .
							"''" .
							")";
					}
				}
				if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
					error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
				if ($res = mysqli_query($this->DBLink, $sQ)) {
					$bOK = true;
				}
			}
			

		}
		return $bOK;
	}
	
	public function GetModuleLocketKeyConfiguration(&$arKeys) {
		CTOOLS_ArrayRemoveAllElement($arKeys);
		$sQ = "SELECT * FROM central_module_locket_config ORDER BY CTR_ML_KEY ASC";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$i = 0;
				$bOK = true;
				$arKeys = array();
				while ($row = mysqli_fetch_array($res)) {
					$arKeys[$i]["key"] = $row["CTR_ML_KEY"];
					$arKeys[$i]["keyName"] = $row["CTR_ML_HEADER"];
					$i++;
				}
			}
		}
		return $bOK;
	}
	
	public function GetModuleLocketKeyConfigurationName($key) {
		// FIX: mysql escape string
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		CTOOLS_ArrayRemoveAllElement($arKeys);
		$sQ = "SELECT CTR_ML_HEADER FROM central_module_locket_config WHERE " .
				"CTR_ML_KEY = '" . $key . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				if ($row = mysqli_fetch_array($res)) {
					$keyName = $row["CTR_ML_HEADER"];
					return $keyName;
				}
			}
		}
		return null;
	}
	
	public function InsertModuleLocketKeyConfiguration($key, $keyName) {
		// FIX: mysql escape string
		$key = mysqli_real_escape_string($this->DBLink, $key);
		$keyName = mysqli_real_escape_string($this->DBLink, $keyName);
		
		$sQ = "INSERT INTO central_module_locket_config VALUES (".
				"'" . $key . "', " .
				"'" . $keyName . "') ";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}

	public function DeleteModuleLocketKeyConfiguration($key) {
		// FIX: mysql escape string
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "DELETE FROM central_module_locket_config WHERE ".
				"CTR_ML_KEY = '" . $key . "' ";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}

	public function EditModuleLocketKeyConfiguration($oldKey, $key, $keyName) {
		// FIX: mysql escape string
		$oldKey = mysqli_real_escape_string($this->DBLink, $oldKey);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		$keyName = mysqli_real_escape_string($this->DBLink, $keyName);
		
		$sQ = "UPDATE central_module_locket_config SET ".
				"CTR_ML_KEY = '" . $key . "', " .
				"CTR_ML_HEADER = '" . $keyName . "' " .
				"WHERE CTR_ML_KEY = '" . $oldKey . "' ";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	
	public function GetDatabase(&$arDatabases) {
		CTOOLS_ArrayRemoveAllElement($arDatabases);
		$bOK = false;
		
		$sQ = "select * from central_database order by CTR_DB_ID asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				while ($row = mysqli_fetch_array($res)) {
					$arDatabases[$i]["id"] = $row["CTR_DB_ID"];
					$arDatabases[$i]["name"] = $row["CTR_DB_NAME"];
					$arDatabases[$i]["schema"] = $row["CTR_DB_SCHEMA"];
					$arDatabases[$i]["host"] = $row["CTR_DB_HOST"];
					$arDatabases[$i]["port"] = $row["CTR_DB_PORT"];
					$arDatabases[$i]["user"] = $row["CTR_DB_USER"];
					// $arDatabases[$i]["pwd"] = $row["CTR_DB_PWD"];
					$i++;
				}
			}
		}
		
		return $bOK;
	}
	
	public function GetDatabaseDetail($databaseId) {
		// FIX: mysql escape string
		$databaseId = mysqli_real_escape_string($this->DBLink, $databaseId);
		$arDatabases = null;
		
		$bOK = false;
		$arUser = null;
		
		$sQ = "select * from central_database where CTR_DB_ID = '" . $databaseId . "' ";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arDatabases = array();
					$arDatabases["id"] = $row["CTR_DB_ID"];
					$arDatabases["name"] = $row["CTR_DB_NAME"];
					$arDatabases["schema"] = $row["CTR_DB_SCHEMA"];
					$arDatabases["host"] = $row["CTR_DB_HOST"];
					$arDatabases["port"] = $row["CTR_DB_PORT"];
					$arDatabases["user"] = $row["CTR_DB_USER"];
					
					// decrypt paswword
					require_once("inc/key/safe.php");
					$pwdDb = $row["CTR_DB_PWD"];
					$decPwdDb = decrypt($pwdDb);
					
					$arDatabases["pwd"] = $decPwdDb;
				}
			}
		}
		
		return $arDatabases;
	}
	
	public function DeleteDatabase($databaseId) {
		// FIX: mysql escape string
		$databaseId = mysqli_real_escape_string($this->DBLink, $databaseId);
		
		// DELETE DATABASE
		$sQ = "delete from central_database where CTR_DB_ID = '" . $databaseId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function InsertDatabase($idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb) {
		// FIX: mysql escape string
		$idDb = mysqli_real_escape_string($this->DBLink, $idDb);
		$nameDb = mysqli_real_escape_string($this->DBLink, $nameDb);
		$schemaDb = mysqli_real_escape_string($this->DBLink, $schemaDb);
		$hostDb = mysqli_real_escape_string($this->DBLink, $hostDb);
		$portDb = mysqli_real_escape_string($this->DBLink, $portDb);
		$userDb = mysqli_real_escape_string($this->DBLink, $userDb);
		$pwdDb = mysqli_real_escape_string($this->DBLink, $pwdDb);
		
		// DEPRECATED: Password database disimpan plain-text
		// $md5Pwd = md5($pwdDb);
		$md5Pwd = $pwdDb;
		
		if ($portDb == "") {
			$portDb = 0;
		}
	
		$sQ = "insert into central_database values (" .
			"'" . $idDb . "', " .
			"'" . $nameDb . "', " .
			"'" . $schemaDb . "', " .
			"'" . $hostDb . "', " .
			$portDb . ", " .
			"'" . $userDb . "', " .
			"'" . $md5Pwd . "') ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditDatabase($idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb) {
		// FIX: mysql escape string
		$idDb = mysqli_real_escape_string($this->DBLink, $idDb);
		$nameDb = mysqli_real_escape_string($this->DBLink, $nameDb);
		$schemaDb = mysqli_real_escape_string($this->DBLink, $schemaDb);
		$hostDb = mysqli_real_escape_string($this->DBLink, $hostDb);
		$portDb = mysqli_real_escape_string($this->DBLink, $portDb);
		$userDb = mysqli_real_escape_string($this->DBLink, $userDb);
		$pwdDb = mysqli_real_escape_string($this->DBLink, $pwdDb);
		
		$sQ = "update central_database set " .
			"CTR_DB_NAME = '" . $nameDb . "', " .
			"CTR_DB_SCHEMA = '" . $schemaDb . "', " .
			"CTR_DB_HOST = '" . $hostDb . "', " .
			"CTR_DB_PORT = " . $portDb . ", " .
			"CTR_DB_USER = '" . $userDb . "' ";
			
		// DEPRECATED: Password database disimpan plain-text
		if (trim($pwdDb) != "") {
			// $md5Pwd = md5($pwdDb);
			// $sQ .= ", CTR_DB_PWD = '" . $md5Pwd . "' ";
			
			// encrypt
			require_once("inc/key/safe.php");
			$encPwdDb = encrypt($pwdDb);
			$sQ .= ", CTR_DB_PWD = '" . $encPwdDb . "' ";
		}
		// $sQ .= ", CTR_DB_PWD = '" . $pwdDb . "' ";
		
		$sQ .= "where CTR_DB_ID = '" . $idDb . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function GetDatabaseConfigValue($dbId, $key) {
		// FIX: mysql escape string
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "select CTR_DB_POS, CTR_DB_VALUE from central_database_config " .
				"where CTR_DB_AID = '" . $dbId . "' " . 
				"and CTR_DB_KEY = '" . $key . "' " .
				"order by CTR_DB_KEY asc";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$ar["pos"] = $row["CTR_DB_POS"];
					$ar["value"] = $row["CTR_DB_VALUE"];
					return $ar;
				}
			}
		}
		
		return null;
	}
	
	public function DeleteDatabaseConfig($dbId, $key) {
		// FIX: mysql escape string
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		
		$sQ = "delete from central_database_config where CTR_DB_AID = '" . $dbId . "' " .
				"and CTR_DB_KEY = '" . $key . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function InsertDatabaseConfig($dbId, $pos, $key, $value) {
		// FIX: mysql escape string
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$key = mysqli_real_escape_string($this->DBLink, $key);
		$value = mysqli_real_escape_string($this->DBLink, $value);
		
		$sQ = "insert into central_database_config values (" .
			"'" . $dbId . "', " .
			"'" . $key . "', " .
			"'" . $value . "', " .
			$pos . ") ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditDatabaseConfig($dbId, $oldKey, $pos, $newKey, $newValue) {
		// FIX: mysql escape string
		$dbId = mysqli_real_escape_string($this->DBLink, $dbId);
		$oldKey = mysqli_real_escape_string($this->DBLink, $oldKey);
		$pos = mysqli_real_escape_string($this->DBLink, $pos);
		$newKey = mysqli_real_escape_string($this->DBLink, $newKey);
		$newValue = mysqli_real_escape_string($this->DBLink, $newValue);
		
		$sQ = "update central_database_config set " .
			"CTR_DB_POS = '" . $pos . "', " .
			"CTR_DB_KEY = '" . $newKey . "', " .
			"CTR_DB_VALUE = '" . $newValue . "' " .
			"where CTR_DB_AID = '" . $dbId . "' " .
			"and CTR_DB_KEY = '" . $oldKey . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function CopyConfigDatabase($fromDbId, $toDbId) {
		// FIX: mysql escape string
		$fromDbId = mysqli_real_escape_string($this->DBLink, $fromDbId);
		$toDbId = mysqli_real_escape_string($this->DBLink, $toDbId);
		
		$sQ = "select * from central_database_config where CTR_DB_AID = '" . $fromDbId . "' " . 
				"order by CTR_DB_POS asc";
		// echo $sQ;
		
		$insertSQ = "insert into central_database_config values ";
		$first = true;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$arConfig = array();
				while ($row = mysqli_fetch_array($res)) {
					$pos = $row["CTR_DB_POS"];
					$key = $row["CTR_DB_KEY"];
					$value = $row["CTR_DB_VALUE"];
					
					if ($first) {
						$first = false;
					} else {
						$insertSQ .= ", ";
					}
					$insertSQ .= "('" . $toDbId . "', '" . $key . "', '" . $value . "', " . $pos . ") ";
				}
			}
		}
		
		// echo $insertSQ;
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] insertSQ [$insertSQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $insertSQ)) {
			$bOK = true;
		}
			
		return $bOK;
	}
	
	public function CopyConfigModule($fromModuleId, $toModuleId) {
		// FIX: mysql escape string
		$fromModuleId = mysqli_real_escape_string($this->DBLink, $fromModuleId);
		$toModuleId = mysqli_real_escape_string($this->DBLink, $toModuleId);
		
		$sQ = "select * from central_module_config where CTR_CFG_MID = '" . $fromModuleId . "' ";
		// echo $sQ;
		
		$insertSQ = "insert into central_module_config values ";
		$first = true;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$arConfig = array();
				while ($row = mysqli_fetch_array($res)) {
					$pos = $row["CTR_CFG_MID"];
					$key = $row["CTR_CFG_MKEY"];
					$value = $row["CTR_CFG_MVALUE"];
					
					if ($first) {
						$first = false;
					} else {
						$insertSQ .= ", ";
					}
					$insertSQ .= "('" . $toModuleId . "', '" . $key . "', '" . $value . "' )";
				}
			}
		}
		
		// echo $insertSQ;
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] insertSQ [$insertSQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $insertSQ)) {
			$bOK = true;
		}
			
		return $bOK;
	}
	public function CopyConfigArea($fromAppId, $toAppId) {
		return $this->CopyConfigApp($fromAppId, $toAppId) ;
	}
	public function CopyConfigApp($fromAppId, $toAppId) {
		// FIX: mysql escape string
		$fromAppId = mysqli_real_escape_string($this->DBLink, $fromAppId);
		$toAppId = mysqli_real_escape_string($this->DBLink, $toAppId);
		
		$sQ = "select * from central_app_config where CTR_AC_AID = '" . $fromAppId . "' ";
		// echo $sQ;
		
		$insertSQ = "insert into central_app_config values ";
		$first = true;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$arConfig = array();
				while ($row = mysqli_fetch_array($res)) {
					$pos = $row["CTR_AC_AID"];
					$key = $row["CTR_AC_KEY"];
					$value = $row["CTR_AC_VALUE"];
					
					if ($first) {
						$first = false;
					} else {
						$insertSQ .= ", ";
					}
					$insertSQ .= "('" . $toAppId . "', '" . $key . "', '" . $value . "' )";
				}
			}
		}
		
		// echo $insertSQ;
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] insertSQ [$insertSQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $insertSQ)) {
			$bOK = true;
		}
			
		return $bOK;
	}
	

	public function GetApplicationSetting(&$arSetting, $settingId = null) {
		// FIX: mysql escape string
		$settingId = mysqli_real_escape_string($this->DBLink, $settingId);
		
		CTOOLS_ArrayRemoveAllElement($arSetting);
		$bOK = false;
		
		if ($settingId == null) {
			$sQ = "select * from central_setting order by CTR_ID";
			// echo $sQ;
			
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
			if ($res = mysqli_query($this->DBLink, $sQ)) {
				$nRes = mysqli_num_rows($res);
				if ($nRes > 0) {
					$bOK = true;
					$i = 0;
					while ($row = mysqli_fetch_array($res)) {
						$arSetting[$i]["id"] = $row["CTR_ID"];
						$arSetting[$i]["title"] = $row["CTR_TITLE"];
						$arSetting[$i]["stylePath"] = $row["CTR_STYLE_PATH"];
						$arSetting[$i]["footer"] = $row["CTR_FOOTER"];
						$i++;
					}
				}
			}
		} else {
			$sQ = "select * from central_setting where CTR_ID = '" . $settingId . "' ";
			// echo $sQ;
			
			if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
				error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
			if ($res = mysqli_query($this->DBLink, $sQ)) {
				$nRes = mysqli_num_rows($res);
				if ($nRes > 0) {
					$bOK = true;
					if ($row = mysqli_fetch_array($res)) {
						$arSetting["title"] = $row["CTR_TITLE"];
						$arSetting["stylePath"] = $row["CTR_STYLE_PATH"];
						$arSetting["footer"] = $row["CTR_FOOTER"];
					}
				}
			}
		}
		return $bOK;
	}
	
	public function GetSettingDetail($settingId = null) {
		// FIX: mysql escape string
		$settingId = mysqli_real_escape_string($this->DBLink, $settingId);
		
		$bOK = false;
		$arSetting = null;
		
		if ($settingId == null) {
			$sQ = "select * from central_setting";
		} else {
			$sQ = "select * from central_setting where CTR_ID = '" . $settingId . "' ";
		}
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				if ($row = mysqli_fetch_array($res)) {
					$arSetting = array();
					$arSetting["id"] = $row["CTR_ID"];
					$arSetting["title"] = $row["CTR_TITLE"];
					$arSetting["stylePath"] = $row["CTR_STYLE_PATH"];
					$arSetting["footer"] = $row["CTR_FOOTER"];
				}
			}
		}
		
		return $arSetting;
	}
	
	public function DeleteSetting($settingId) {
		// FIX: mysql escape string
		$settingId = mysqli_real_escape_string($this->DBLink, $settingId);
		
		// DELETE USER
		$sQ = "delete from central_setting where CTR_ID = '" . $settingId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		
		return $bOK;
	}
	
	public function InsertSetting($id, $title, $stylePath, $footer) {
		// FIX: mysql escape string
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$title = mysqli_real_escape_string($this->DBLink, $title);
		$stylePath = mysqli_real_escape_string($this->DBLink, $stylePath);
		$footer = mysqli_real_escape_string($this->DBLink, $footer);
		
		$sQ = "insert into central_setting values (" .
			"'" . $id . "', " .
			"'" . $title . "', " .
			"'" . $stylePath . "', " .
			"'" . $footer . "') ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditSetting($oldId, $id, $title, $stylePath, $footer) {
		// FIX: mysql escape string
		$oldId = mysqli_real_escape_string($this->DBLink, $oldId);
		$id = mysqli_real_escape_string($this->DBLink, $id);
		$title = mysqli_real_escape_string($this->DBLink, $title);
		$stylePath = mysqli_real_escape_string($this->DBLink, $stylePath);
		$footer = mysqli_real_escape_string($this->DBLink, $footer);
		
		$sQ = "update central_setting set " .
			"CTR_ID = '" . $id . "', " .
			"CTR_TITLE = '" . $title . "', " .
			"CTR_STYLE_PATH = '" . $stylePath . "', " .
			"CTR_FOOTER = '" . $footer . "' " .
			"where CTR_ID = '" . $oldId . "' ";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	// NEW: 2 Agustus 2010
	// Custom Authentication
	public function GetAllAuth($findActiveOnly = true) {
		$bOK = false;
		$arAuth = null;
		if ($findActiveOnly) {
			$sQ = "select * from central_auth where CTR_A_ACTIVE = 1 order by CTR_A_ORDER";
		} else {
			$sQ = "select * from central_auth order by CTR_A_ORDER";
		}
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				$arAuth = array();
				while ($row = mysqli_fetch_array($res)) {
					$arAuth[$i]["id"] = $row["CTR_A_ID"];
					$arAuth[$i]["class"] = $row["CTR_A_CLASS"];
					$arAuth[$i]["active"] = $row["CTR_A_ACTIVE"];
					$arAuth[$i]["order"] = $row["CTR_A_ORDER"];
					$i++;
				}
			}
		}
		
		return $arAuth;
	}
	
	public function GetAuth($authId) {
		// FIX: mysql escape string
		$authId = mysqli_real_escape_string($this->DBLink, $authId);
		
		$bOK = false;
		$arAuth = null;
		$sQ = "select * from central_auth where CTR_A_ID = $authId order by CTR_A_ORDER";
		// echo $sQ;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$arAuth = array();
				if ($row = mysqli_fetch_array($res)) {
					$arAuth["id"] = $row["CTR_A_ID"];
					$arAuth["class"] = $row["CTR_A_CLASS"];
					$arAuth["active"] = $row["CTR_A_ACTIVE"];
					$arAuth["order"] = $row["CTR_A_ORDER"];
				}
			}
		}
		
		return $arAuth;
	}
	
	public function GetNextAuthOrder() {
		$sQ = "SELECT (MAX(CTR_A_ORDER) + 1) AS MAX FROM central_auth";
		// echo $sQ;
		
		$bOK = false;
		$max = 1;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$nRes = mysqli_num_rows($res);
			if ($nRes > 0) {
				$bOK = true;
				$i = 0;
				if ($row = mysqli_fetch_array($res)) {
					$max = $row["MAX"];
				}
			}
		}
		if ($max == null) {
			$max = 1;
		}
		return $max;
	}
	
	public function InsertAuth($className, $active) {
		// FIX: mysql escape string
		$className = mysqli_real_escape_string($this->DBLink, $className);
		
		$max = $this->GetNextAuthOrder();
		
		$sQ = "insert into central_auth (CTR_A_CLASS, CTR_A_ACTIVE, CTR_A_ORDER) values (" .
			"'" . $className . "', " .
			$active . ", " .
			$max . ") ";
		// echo $sQ;
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}	
	public function DeleteAuth($authId) {
		// FIX: mysql escape string
		$authId = mysqli_real_escape_string($this->DBLink, $authId);
		
		$sQ = "delete from central_auth where CTR_A_ID = $authId";
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
	
	public function EditAuth($authId, $className, $active, $order) {
		// FIX: mysql escape string
		$authId = mysqli_real_escape_string($this->DBLink, $authId);
		$className = mysqli_real_escape_string($this->DBLink, $className);
		$active = mysqli_real_escape_string($this->DBLink, $active);
		$order = mysqli_real_escape_string($this->DBLink, $order);
		
		$sQ = "update central_auth set " .
			"CTR_A_CLASS = '" . $className . "', " .
			"CTR_A_ACTIVE = " . $active . ", " .
			"CTR_A_ORDER = " . $order . " " .
			"where CTR_A_ID = " . $authId;
		// echo $sQ;
		
		$bOK = false;
		
		if (CTOOLS_IsInFlag($this->iDebug, DEBUG_DEBUG))
			error_log ("[".strftime("%Y%m%d%H%M%S", time())."][".$this->sThisFile.":".__LINE__."] [DEBUG] sQ [$sQ]\n", 3, $this->sLogFilename);
		if ($res = mysqli_query($this->DBLink, $sQ)) {
			$bOK = true;
		}
		return $bOK;
	}
}
?>
