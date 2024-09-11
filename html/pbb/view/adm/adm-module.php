<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management module
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertModuleAction();
	} else {
		insertModule();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editModuleAction();
	} else {
		editModule();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteModule();
	
} else if ($subMode == "lc") {
	// Submode list
	if ($action) {
		copyModuleCfg();
	} else {
		listModuleCfg();
	}
} else if ($subMode == "ic") {
	// Submode insert module configuration
	if ($action) {
		insertModuleCfgAction();
	} else {
		insertModuleCfg();
	}
} else if ($subMode == "ec") {
	// Submode edit module configuration
	if ($action) {
		editModuleCfgAction();
	} else {
		editModuleCfg();
	}
} else if ($subMode == "dc") {
	// Submode delete module configuration
	deleteModuleCfg();
} else if ($subMode == "da") {
	// Submode delete all module configuration
	deleteAllModuleCfg();
} else {
	// List all
	$bOK = printModuleSetting();
	if (!$bOK) {
		return;
	}
}

function printModuleSetting() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
				
		echo "<div class='subTitle'>List modules</div>";
		echo "<div class='spacer10'></div>";
				
		$url64 = base64_encode("setting=1&m=m&sm=i");
		echo "<a href='main.php?param=$url64'>Add new module</a>";
		echo "<div class='spacer10'></div>";
		
		// print table Module
		$bOK = $Setting->GetModule($ModuleIds);
		if ($bOK) {
?>
<script type='text/javascript'>
function confirmDeleteModule(name, id) {
	var ans = confirm("Delete module '" + name + "' ?");
	if (ans) {
		var url = Base64.encode("setting=1&m=m&sm=d&i=" + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
			// var_dump($ModuleIds);
			echo "<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</td>";
			echo "<th>Id</td>";
			echo "<th>Name</td>";
			echo "<th>Description</td>";
			echo "<th>View</td>";
			echo "</tr>";
			
			foreach ($ModuleIds as $ModuleId) {
				$id = $ModuleId["id"];
				$name = $ModuleId["name"];
				$desc = $ModuleId["desc"];
				$view = $ModuleId["view"];
				
				$name = htmlentities($name, ENT_QUOTES);
				$desc = htmlentities($desc, ENT_QUOTES);
				$view = htmlentities($view, ENT_QUOTES);
				
				echo "<tr>";
				echo "<td align='center' valign='top'>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=m&sm=e&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				echo "<a href='#' onClick='confirmDeleteModule(\"$name\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/tools.png' alt='List configuration' title='List configuration'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td valign='top'>$id&nbsp;</td>";
				echo "<td valign='top'>$name&nbsp;</td>";
				echo "<td valign='top'>$desc&nbsp;</td>";
				if ($view) {
					echo "<td valign='top'>$view&nbsp;</td>";
				} else {
					echo "<td valign='top'><i>default</i>&nbsp;</td>";
				}
				echo "</tr>";
			}
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteModule() {
	global $Setting, $id;
	$bOK = $Setting->DeleteModule($id);
	if ($bOK) {
		echo "<div>Module successfully deleted...</div>";
	} else {
		echo "<div>Module fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=m");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertModuleAction() {
	global $Setting, $idModule, $nameModule, $descModule, $viewModule;
	
	// $idModule = (isset($_REQUEST['idModule']) ? trim($_REQUEST['idModule']) : '');
	// $nameModule = (isset($_REQUEST['nameModule']) ? trim($_REQUEST['nameModule']) : '');
	// $descModule = (isset($_REQUEST['descModule']) ? trim($_REQUEST['descModule']) : '');
	// $viewModule = (isset($_REQUEST['viewModule']) ? trim($_REQUEST['viewModule']) : '');

	$bOK = $Setting->InsertModule($idModule, $nameModule, $descModule, $viewModule);
	
	$idModule = htmlentities($idModule, ENT_QUOTES);
	$nameModule = htmlentities($nameModule, ENT_QUOTES);
	$descModule = htmlentities($descModule, ENT_QUOTES);
	$viewModule = htmlentities($viewModule, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=m&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formModule'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert Module";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idModule' name='idModule' value='$idModule' autocomplete='off'></input>$idModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameModule' name='nameModule' value='$nameModule' autocomplete='off'></input>$nameModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descModule' name='descModule' value='$descModule' autocomplete='off'></input>$descModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>View</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='viewModule' name='viewModule' value='$viewModule' autocomplete='off'></input>$viewModule</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formModule\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=m");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function insertModule() {
	global $Setting, $mode, $idModule, $nameModule, $descModule, $viewModule;
	
	// $idModule = (isset($_REQUEST['idModule']) ? trim($_REQUEST['idModule']) : '');
	// $nameModule = (isset($_REQUEST['nameModule']) ? trim($_REQUEST['nameModule']) : '');
	// $descModule = (isset($_REQUEST['descModule']) ? trim($_REQUEST['descModule']) : '');
	// $viewModule = (isset($_REQUEST['viewModule']) ? trim($_REQUEST['viewModule']) : '');
	
	if ($idModule == "") {
		// Initial insert
		$idModule = "m" . $Setting->GetNextModuleId();
	}
	
	// DEPRECATED: 'view/' pasti ditambahkan
	// if ($viewModule == "") {
		// Initial insert
		// $viewModule = "view/";
	// }

	$idModule = htmlentities($idModule, ENT_QUOTES);
	$nameModule = htmlentities($nameModule, ENT_QUOTES);
	$descModule = htmlentities($descModule, ENT_QUOTES);
	$viewModule = htmlentities($viewModule, ENT_QUOTES);
	
	echo "<div class='subTitle'>Insert new module</div>";
	echo "<div class='spacer10'></div>";
				
	$url64 = base64_encode("setting=1&m=m&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idModule' name='idModule' length='30' size='20' value='$idModule' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameModule' name='nameModule' length='100' size='30' value='$nameModule' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textArea id='descModule' name='descModule' cols='50' rows='3'>$descModule</textArea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>View</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>view/<input type='text' id='viewModule' name='viewModule' length='30' size='20' value='$viewModule' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	$url64 = base64_encode("setting=1&m=$mode");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editModuleAction() {
	global $Setting, $idModule, $nameModule, $descModule, $viewModule;
	
	// $idModule = (isset($_REQUEST['idModule']) ? trim($_REQUEST['idModule']) : '');
	// $nameModule = (isset($_REQUEST['nameModule']) ? trim($_REQUEST['nameModule']) : '');
	// $descModule = (isset($_REQUEST['descModule']) ? trim($_REQUEST['descModule']) : '');
	// $viewModule = (isset($_REQUEST['viewModule']) ? trim($_REQUEST['viewModule']) : '');

	$bOK = $Setting->EditModule($idModule, $nameModule, $descModule, $viewModule);
	
	$url64 = base64_encode("setting=1&m=m&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formModule'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit Module";
	if ($bOK) {
		echo " Succeed";
	} else {
		echo " Failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idModule' name='idModule' value='$idModule' autocomplete='off'></input>$idModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameModule' name='nameModule' value='$nameModule' autocomplete='off'></input>$nameModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descModule' name='descModule' value='$descModule' autocomplete='off'></input>$descModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>View</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='viewModule' name='viewModule' value='$viewModule' autocomplete='off'></input>$viewModule</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formModule\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=m");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function editModule() {
	global $Setting, $id, $mode;
	
	$arModule = $Setting->GetModuleDetail($id);
	$idModule = $arModule["id"];
	$nameModule = $arModule["name"];
	$descModule = $arModule["desc"];
	$viewModule = $arModule["view"];
	
	// quote fix
	$idModule = htmlentities($idModule, ENT_QUOTES);
	$nameModule = htmlentities($nameModule, ENT_QUOTES);
	$descModule = htmlentities($descModule, ENT_QUOTES);
	$viewModule = htmlentities($viewModule, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit module '$nameModule'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=m&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idModule' name='idModule' value='$idModule' autocomplete='off'></input>$idModule</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameModule' name='nameModule' length='100' size='30' value='$nameModule' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textArea id='descModule' name='descModule' cols='50' rows='3'>$descModule</textArea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>View</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>view/<input type='text' id='viewModule' name='viewModule' length='100' size='30' value='$viewModule' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	$url64 = base64_encode("setting=1&m=$mode");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function listModuleCfg() {
	global $Setting, $User, $id;
	
	$moduleName = $User->GetModuleName($id);
	echo "<div class='subTitle'>List configuration in module '$moduleName'</div>";
	echo "<div class='spacer10'></div>";
				
	$arConfig = $Setting->GetModuleConfig($id);
	
	// insert module configuration
	$url64 = base64_encode("setting=1&m=m&sm=ic&i=$id");
	echo "<a href='main.php?param=$url64'>Add new configuration</a>";
	if ($arConfig) {
		echo "&nbsp;&nbsp;&nbsp;";
		echo "<a href='#' onClick='confirmDeleteAllModuleCfg(\"$moduleName\", \"$id\")'>Delete all configuration</a>";
	}
	echo "<div class='spacer10'></div>";
	
	if ($arConfig) {
?>
<script type='text/javascript'>
function confirmDeleteModuleCfg(id, key) {
	var ans = confirm("Delete configuration '" + key + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=m&sm=dc&i=' + id + '&k=' + key);
		window.location.href = 'main.php?param=' + url;
	}
}
function confirmDeleteAllModuleCfg(name, id) {
	var ans = confirm("Delete all configuration in module '" + name + "' ?");
	if (ans) {
		var url = Base64.encode("setting=1&m=m&sm=da&i=" + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
		echo "<table cellspacing='3px' cellpadding='3px'>";
		echo "<tr>";
		echo "<th>Option</th>";
		echo "<th>Key</th>";
		echo "<th>Value</th>";
		echo "</tr>";
		foreach ($arConfig as $conf) {
			$mKey = $conf["key"];
			$mValue = $conf["value"];
			
			echo "<tr>";
			echo "<td valign='top'>";
			echo "&nbsp;";
			$url64 = base64_encode("setting=1&m=m&sm=ec&i=$id&k=$mKey");
			echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit configuration' title='Edit configuration'></img></a>";
			echo "&nbsp;";
			echo "<a href='#' onClick='confirmDeleteModuleCfg(\"$id\", \"$mKey\")''><img border='0' src='image/icon/cancel.png' alt='Delete configuration' title='Delete configuration'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "<td valign='top'>$mKey&nbsp;</td>";
			echo "<td width='500px' valign='top'>" . nl2br($mValue) . "&nbsp;</td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		// echo "No configuration available.";
		
		$arModules = null;
		$bOK = $Setting->GetModule($arModules);
		if ($bOK) {
			// copy database configuration
			$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
			echo "<form method='POST' action='main.php?param=$url64'>";
			echo "<input type='hidden' id='a' name='a' value='1'></input>";
			echo "<div>";
			echo "No configuration available. Init configuration from ";
			echo "<select id='moduleInit' name='moduleInit'>";
			echo "<option value='-'>--------</option>";
			foreach ($arModules as $iModule) {
				$modId = $iModule["id"];
				$modName = $iModule["name"];
			
				if ($modId != $id) {
					echo "<option value='$modId'>$modName</option>";
				}
			}
			echo "</select>";
			echo "<input type='submit' value='Init'></input>";
			echo "</div>";
			
			echo "<div class='spacer10'></div>";
			echo "</form>";
		}
	}
}

function copyModuleCfg() {
	global $Setting, $id, $moduleInit;
	
	if ($moduleInit != "") {
		$bOK = $Setting->CopyConfigModule($moduleInit, $id);
		
		if ($bOK) {
			echo "<div>Module configuration successfully copied...</div>";
		} else {
			echo "<div>Module configuration failed to copy...</div>";
		}
		$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
}

function insertModuleCfgAction() {
	global $Setting, $User, $id, $key, $value;

	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	$bOK = $Setting->InsertModuleConfig($id, $key, $value);
	
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$moduleName = $User->GetModuleName($id);
	
	$url64 = base64_encode("setting=1&m=m&sm=ic&i=$id");
	echo "<form method='POST' action='main.php?param=$url64' id='formModule'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert module $moduleName's configuration";
	if ($bOK) {
		echo " succeed";
	} else {
		echo " failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Value</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='value' name='value' value='$value' autocomplete='off'></input>$value</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formModule\").submit();'>Edit configuration</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function insertModuleCfg() {
	global $id, $User, $mode, $subMode, $key, $value;
	
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$moduleName = $User->GetModuleName($id);
	echo "<div class='subTitle'>Add new configuration for module '$moduleName'</div>";
	echo "<div class='spacer10'></div>";
				
	$url64 = base64_encode("setting=1&m=m&sm=ic&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Key</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='key' id='key' size='30' length='45' value='$key' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Value</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<textarea name='value' id='value' cols='30' rows='2'>$value</textarea>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	$url64 = base64_encode("setting=1&m=$mode&sm=lc&i=$id");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editModuleCfgAction() {
	global $Setting, $User, $id, $oldKey, $key, $value, $ar, $md;

	// $oldKey = (isset($_REQUEST['oldKey']) ? trim($_REQUEST['oldKey']) : '');
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	// Redirect from module approve sms
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $moduleId = (isset($_REQUEST['md']) ? trim($_REQUEST['md']) : '');
	$areaId = $ar;
	$moduleId = $md;
	
	$bOK = $Setting->EditModuleConfig($id, $oldKey, $key, $value);
	
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$moduleName = $User->GetModuleName($id);
	
	$url64 = base64_encode("setting=1&m=m&sm=ec&i=$id&k=$oldKey");
	echo "<form method='POST' action='main.php?param=$url64' id='formModule'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit module $moduleName's configuration";
	if ($bOK) {
		echo " succeed";
	} else {
		echo " failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Key</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Value</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='value' name='value' value='$value' autocomplete='off'></input>$value</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formModule\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		if ($areaId != "" && $moduleId != "") {
			$url64 = base64_encode("a=$areaId&m=$moduleId");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Redirect to locket SMS... <img src='image/icon/wait.gif' alt=''></img>";
		} else {
			$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		}
	}
	echo "</div>";
}

function editModuleCfg() {
	global $Setting, $User, $id, $mode, $subMode, $o, $k, $ar, $md;
	
	// $oldKey = (isset($_REQUEST['o']) ? trim($_REQUEST['o']) : '');
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$oldKey = $o;
	$key = $k;
	if ($oldKey == "") {
		$oldKey = $key;
	}
	
	// Redirect from module approve sms
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $moduleId = (isset($_REQUEST['md']) ? trim($_REQUEST['md']) : '');
	$areaId = $ar;
	$moduleId = $md;
	
	$value = $Setting->GetModuleConfigValue($id, $oldKey);
	
	// quote fix
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$moduleName = $User->GetModuleName($id);
	echo "<div class='subTitle'>Edit configuration '$oldKey' in module '$moduleName'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=m&sm=ec&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Key</td>";
	echo "<td>:</td>";
	echo "<td>";
	if ($areaId != "" && $moduleId != "") {
		echo "<input type='hidden' name='key' id='key' value='$key'></input>$key";
		echo "<input type='hidden' name='oldKey' id='oldKey' value='$oldKey'></input>";
		echo "<input type='hidden' name='ar' id='ar' value='$areaId'></input>";
		echo "<input type='hidden' name='md' id='md' value='$moduleId'></input>";
	} else {
		echo "<input type='text' name='key' id='key' size='30' length='45' value='$key' autocomplete='off'></input>";
		echo "<input type='hidden' name='oldKey' id='oldKey' value='$oldKey' autocomplete='off'></input>";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Value</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<textarea name='value' id='value' cols='30' rows='2'>$value</textarea>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	if ($areaId != "" && $moduleId != "") {
		$url64 = base64_encode("a=$areaId&m=$moduleId");
		echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	} else {
		$url64 = base64_encode("setting=1&m=$mode&sm=lc&i=$id");
		echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	}
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function deleteModuleCfg() {
	global $Setting, $id, $k;
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$key = $k;
	$bOK = $Setting->DeleteModuleConfig($id, $key);
	if ($bOK) {
		echo "<div>Module configuration successfully deleted...</div>";
	} else {
		echo "<div>Module configuration fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function deleteAllModuleCfg() {
	global $Setting, $id;
	$bOK = $Setting->DeleteAllModuleConfig($id);
	if ($bOK) {
		echo "<div>Module configuration successfully emptied...</div>";
	} else {
		echo "<div>Module configuration fail to empty...</div>";
	}
	$url64 = base64_encode("setting=1&m=m&sm=lc&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}


?>
