<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management locket
if ($subMode == "l") {
	// Submode list locket
	printLocketConfiguration();
} else if ($subMode == "ec") {
	// Edit configuration
	if ($action) {
		// get parameter
		editConfigurationAction();
	} else {
		editConfiguration();
	}
} else if ($subMode == "lk") {
	listKey();
} else if ($subMode == "dk") {
	deleteKey();
} else if ($subMode == "ek") {
	if ($action) {
		editKeyAction();
	} else {
		editKey();
	}
} else if ($subMode == "ik") {
	if ($action) {
		insertKeyAction();
	} else {
		insertKey();
	}
} else {
	// List all
	$bOK = printLocket();
	if (!$bOK) {
		return;
	}
}

function printLocket() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
		
		echo "<div class='subTitle'>List PP Module</div>";
		echo "<div class='spacer10'></div>";
		
		echo "<a href='setting.php?m=l&sm=lk'>List key</a>";
		echo "<div class='spacer10'></div>";
		
		$arModule = null;
		$bOK = $dbSpec->GetModuleOnpays($arModule);
		if ($bOK) {
			// var_dump($arModule);
			echo "<table cellspacing='1px' cellpadding='3px' border='1'>";
			echo "<tr>";
			echo "<th>Option</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "<th>Description</th>";
			echo "<th>Var Name</th>";
			echo "<th>Version</th>";
			echo "<th>Installed</th>";
			echo "</tr>";
			
			foreach ($arModule as $mod) {
				$id = $mod["id"];
				$name = $mod["name"];
				$desc = $mod["desc"];
				$varname = $mod["varname"];
				$version = $mod["version"];
				$installed = $mod["installed"];
				
				echo "<tr>";
				echo "<td align='center'>";
				echo "&nbsp;";
				echo "<a href='setting.php?m=l&sm=l&i=$id'><img border='0' src='image/icon/mgmt.gif' alt='Edit configuration' title='Edit configuration'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td>$id&nbsp;</td>";
				echo "<td>$name&nbsp;</td>";
				echo "<td>$desc&nbsp;</td>";
				echo "<td>$varname&nbsp;</td>";
				echo "<td>$version&nbsp;</td>";
				echo "<td>$installed&nbsp;</td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
	}
	return $bOK;
}

function printLocketConfiguration() {
	global $cData, $data, $json, $User, $Setting, $id;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
		$moduleName = $User->GetModuleLocketName($id);
		
		echo "<div class='subTitle'>Configuration in PP module '$moduleName'</div>";
		echo "<div class='spacer10'></div>";
		
		echo "<a href='setting.php?m=l&sm=ec&i=$id'>Edit configuration</a>";
		echo "<div class='spacer10'></div>";
		
		$arModuleConf = null;
		$bOK = $Setting->GetModuleLocketKeyConfiguration($arConfigKey);
		$bOK = $Setting->GetModuleOnpaysConfiguration($id, $arConfigKey, $arModuleConf);
		echo "<table cellspacing='1px' cellpadding='3px' border='1'>";
		echo "<tr>";
		echo "<th>Key</th>";
		echo "<th>Value</th>";
		echo "</tr>";
		
		foreach ($arConfigKey as $cKey) {
			$keyName = $cKey["keyName"];
			$key = $cKey["key"];
			$value = null;
			if (isset($arModuleConf[$key])) {
				$value = $arModuleConf[$key];
			}
		
			echo "<tr>";
			echo "<td>$keyName&nbsp;</td>";
			if ($value) {
				echo "<td>$value&nbsp;</td>";
			} else {
				echo "<td align='center'>-</td>";
			}
			echo "</tr>";
		}
		
		echo "</table>";
	}
	return $bOK;
}

function editConfiguration() {
	global $Setting, $User, $id;
	
	$arModConf = null;
	$bOK = $Setting->GetModuleLocketKeyConfiguration($arConfigKey);
	$bOK = $Setting->GetModuleOnpaysConfiguration($id, $arConfigKey, $arModConf);

	$moduleId = (isset($_REQUEST['moduleId']) ? trim($_REQUEST['moduleId']) : '');
	$arValue = array();
	if ($bOK) {
		foreach ($arConfigKey as $cKey) {
			$key = $cKey["key"];
			$arValue[$key] = (isset($arModConf[$key]) ? trim($arModConf[$key]) : '');
		}
	}
	$moduleName = $User->GetModuleLocketName($id);
	
	echo "<div class='subTitle'>Edit PP module configuration in '$moduleName'</div>";
	echo "<div class='spacer10'></div>";
				
	echo "<form method='POST' action='setting.php?m=l&sm=ec&a=1'>";
	echo "<input type='hidden' id='moduleId' name='moduleId' value='$id'></input>";
	echo "<table border='0'>";
	
	foreach ($arConfigKey as $cKey) {
		$keyName = $cKey["keyName"];
		$key = $cKey["key"];
		$keyValue = "";
		if (isset($arValue[$key])) {
			$keyValue = $arValue[$key];
		}
		
		echo "<tr>";
		echo "<td valign='top'>$keyName</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'><input type='text' id='$key' name='$key' value='$keyValue' length='100' size='30' autocomplete='off'></input></td>";
		echo "</tr>";
	}
	echo "<tr>";
	echo "<td colspan='3' height='10px'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editConfigurationAction() {
	global $Setting;
	
	$bOK = $Setting->GetModuleLocketKeyConfiguration($arConfigKey);
	$moduleId = (isset($_REQUEST['moduleId']) ? trim($_REQUEST['moduleId']) : '');
	$arValue = array();
	foreach ($arConfigKey as $cKey) {
		$key = $cKey["key"];
		$arValue[$key] = (isset($_REQUEST[$key]) ? trim($_REQUEST[$key]) : '');
	}
	
	$bOK = $Setting->EditModuleOnpaysConfiguration($moduleId, $arValue);
	
	echo "<form method='POST' action='setting.php?m=l&sm=i' id='formLocket'>";
	echo "<table border='0'>";
	echo "<tr>";
	echo "<th colspan='3'>";
	echo "Edit PP module Configuration";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	
	foreach ($arConfigKey as $cKey) {
		$key = $cKey["key"];
		$keyName = $cKey["keyName"];
		$keyName = htmlentities($keyName);
		$keyValue = $arValue[$key];
	
		echo "<tr>";
		echo "<td valign='top'>$keyName</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'><input type='hidden' id='$key' name='$key' value='$keyValue'></input>$keyValue</td>";
		echo "</tr>";
	}
	
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formLocket\").submit();'>Edit locket</a>&nbsp;&nbsp;";
	} else {
		echo "<meta http-equiv='REFRESH' content='1;url=setting.php?m=l&sm=l&i=$moduleId' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	// echo "<a href='setting.php?m=a'>Manage App</a>";
	echo "</div>";
	echo "</form>";
}

function listKey() {
	global $Setting;
	
	echo "<div class='subTitle'>List key</div>";
	echo "<div class='spacer10'></div>";
				
	echo "<a href='setting.php?m=l&sm=ik'>Add key</a>";
	echo "<div class='spacer10'></div>";
	
	$arKeys = null;
	$bOK = $Setting->GetModuleLocketKeyConfiguration($arKeys);
	if ($bOK) {
		echo "<form method='POST' action='setting.php?m=l&sm=ek&a=1'>";
		echo "<table cellspacing='1px' cellpadding='3px' border='1'>";
		echo "<tr>";
		echo "<th>Option</th>";
		echo "<th>Key</th>";
		echo "<th>Header</th>";
		echo "</tr>";
		
		foreach ($arKeys as $cKey) {
			$key = $cKey["key"];
			$keyHtml = htmlentities($key);
			$keyName = $cKey["keyName"];
			echo "<tr>";
			echo "<td align='center'>";
			echo "&nbsp;";
			echo "<a href='setting.php?m=l&sm=ek&i=$keyHtml'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
			echo "&nbsp;";
			echo "<a href='#' onClick='confirmDeleteKey(\"$keyName\", \"$key\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "<td valign='top'>$key</td>";
			echo "<td valign='top'>$keyName</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "</form>";
	}
}

function deleteKey() {
	global $Setting, $id;
	$bOK = $Setting->DeleteModuleLocketKeyConfiguration($id);
	if ($bOK) {
		echo "<div>Key successfully deleted...</div>";
	} else {
		echo "<div>Key fail to delete...</div>";
	}
	echo "<meta http-equiv='REFRESH' content='1;url=setting.php?m=l&sm=lk'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertKey() {
	global $Setting;
	
	$key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	$keyName = (isset($_REQUEST['keyName']) ? trim($_REQUEST['keyName']) : '');

	$key = htmlentities($key, ENT_QUOTES);
	$keyName = htmlentities($keyName, ENT_QUOTES);
	
	echo "<div class='subTitle'>Insert new key</div>";
	echo "<div class='spacer10'></div>";
		
	echo "<form method='POST' action='setting.php?m=l&sm=ik&a=1'>";
	echo "<table border='0'>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='key' name='key' length='100' size='20' value='$key' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='keyName' name='keyName' length='100' size='30' value='$keyName' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function insertKeyAction() {
	global $Setting;
	
	$key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	$keyName = (isset($_REQUEST['keyName']) ? trim($_REQUEST['keyName']) : '');

	$bOK = $Setting->InsertModuleLocketKeyConfiguration($key, $keyName);
	
	$key = htmlentities($key, ENT_QUOTES);
	$keyName = htmlentities($keyName, ENT_QUOTES);
	
	echo "<form method='POST' action='setting.php?m=l&sm=ik' id='formKey'>";
	echo "<table border='0'>";
	echo "<tr>";
	echo "<th>";
	echo "Insert Key";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='keyName' name='keyName' value='$keyName' autocomplete='off'></input>$keyName</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formKey\").submit();'>Edit key</a>&nbsp;&nbsp;";
	} else {
		echo "<meta http-equiv='REFRESH' content='1;url=setting.php?m=l&sm=lk' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	// echo "<a href='setting.php?m=u'>Manage User</a>";
	echo "</div>";
	echo "</form>";
}

function editKey() {
	global $Setting, $id;
	
	if ($id != "") {
		// initial edit
		$key = $id;
		$oldKey = $key;
		$keyName = $Setting->GetModuleLocketKeyConfigurationName($oldKey);
	} else {
		// next edit
		$key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
		$oldKey = (isset($_REQUEST['oldKey']) ? trim($_REQUEST['oldKey']) : '');
		$keyName = (isset($_REQUEST['keyName']) ? trim($_REQUEST['keyName']) : '');
	}
	
	// quote fix
	$key = htmlentities($key, ENT_QUOTES);
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$keyName = htmlentities($keyName, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit key '$keyName'</div>";
	echo "<div class='spacer10'></div>";
		
	echo "<form method='POST' action='setting.php?m=l&sm=ek&a=1'>";
	echo "<input type='hidden' id='oldKey' name='oldKey' value='$oldKey'></input>";
	echo "<table border='0'>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='key' name='key' value='$key' length='100' size='20' value='$key' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='keyName' name='keyName' length='100' size='30' value='$keyName' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editKeyAction() {
	global $Setting;
	
	$oldKey = (isset($_REQUEST['oldKey']) ? trim($_REQUEST['oldKey']) : '');
	$key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	$keyName = (isset($_REQUEST['keyName']) ? trim($_REQUEST['keyName']) : '');

	$bOK = $Setting->EditModuleLocketKeyConfiguration($oldKey, $key, $keyName);
	
	echo "<form method='POST' action='setting.php?m=l&sm=ek' id='formKey'>";
	echo "<input type='hidden' id='oldKey' name='oldKey' value='$oldKey' autocomplete='off'></input>";
	echo "<table border='0'>";
	echo "<tr>";
	echo "<th>";
	echo "Edit Key";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='keyName' name='keyName' value='$keyName' autocomplete='off'></input>$keyName</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formKey\").submit();'>Edit key</a>&nbsp;&nbsp;";
	} else {
		echo "<meta http-equiv='REFRESH' content='1;url=setting.php?m=l&sm=lk' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	// echo "<a href='setting.php?m=a'>Manage App</a>";
	echo "</div>";
	echo "</form>";
}

?>
