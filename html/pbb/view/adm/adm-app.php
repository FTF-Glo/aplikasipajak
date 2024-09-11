<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management app
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertAppAction();
	} else {
		insertApp();
	}
} else if ($subMode == "c") {
	// Submode copy
	if ($action) {
		// get parameter
		copyAppAction();
	} else {
		copyApp();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editAppAction();
	} else {
		editApp();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteApp();
} else if ($subMode == "t") {
	// Submode list app
	listApp();
} else if ($subMode == "lc") {
	// Submode list
	listAppCfg();
} else if ($subMode == "ic") {
	// Submode insert app configuration
	if ($action) {
		insertAppCfgAction();
	} else {
		insertAppCfg();
	}
} else if ($subMode == "ec") {
	// Submode edit app configuration
	if ($action) {
		editAppCfgAction();
	} else {
		editAppCfg();
	}
} else if ($subMode == "dc") {
	// Submode delete app configuration
	deleteAppCfg();
} else if ($subMode == "l") {
	// Submode list module
	if ($action) {
		saveModuleAccess();
	} else {
		listModuleAccess();
	}
} else {
	// List all
	$bOK = printAppSetting();
	if (!$bOK) {
		return;
	}
}

?>
<script type='text/javascript'>
var winInsertDb = null;

function changeDatabase(app) {
	var dbApp = document.getElementById('dbApp').value;
	if (dbApp != "-99") {
		return;
	}

	if (app != null) {
		if (!winInsertDb) {
			var url = Base64.encode("setting=1&m=d&sm=i&ar=" + app);
			winInsertDb = window.open(
				"main.php?param=" + url,
				"Insert database", 
				"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=500");
		} else if (winInsertDb.closed) {
			var url = Base64.encode("setting=1&m=d&sm=i&ar=" + app);
			winInsertDb = window.open(
				"main.php?param=" + url,
				"Insert database", 
				"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=500");
		} else {
			winInsertDb.focus();
		}
	} else {
		if (!winInsertDb) {
			var url = Base64.encode("setting=1&m=d&sm=i&ar=-99");
			winInsertDb = window.open(
				"main.php?param=" + url,
				"Insert database", 
				"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=500");
		} else if (winInsertDb.closed) {
			var url = Base64.encode("setting=1&m=d&sm=i&ar=-99");
			winInsertDb = window.open(
				"main.php?param=" + url,
				"Insert database", 
				"toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=500");
		} else {
			winInsertDb.focus();
		}
	}
}

function accessAll(value) {
	var i = 0;
	var moduleAccess = null;
	while (true) {
		moduleAccess = document.getElementById('moduleAccess' + i);
		if (moduleAccess == null) {
			break;
		} else {
			moduleAccess.checked = value;
		}
		i++;
	}
}
</script>
<?php

// Print list all applicatoin
function printAppSetting() {
	global $data, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
		
		echo "<div class='subTitle'>List applications</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=a&sm=i&n=1");
		echo "<a href='main.php?param=$url64'>Add new application</a>";
		echo "&nbsp;&nbsp;&nbsp;";
		$url64 = base64_encode("setting=1&m=a&sm=c");
		echo "<a href='main.php?param=$url64'>Copy application</a>";
		echo "<div class='spacer10'></div>";
		// print table app
		$bOK = $Setting->GetApp($appIds);
		if ($bOK) {
?>
<script type='text/javascript'>
function confirmDeleteApp(name, id) {
	var ans = confirm("Delete application '" + name + "' ?");
	if (ans) {
		var url = Base64.encode("setting=1&m=a&sm=d&i=" + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
			// var_dump($appIds);
			echo "<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "<th>Description</th>";
			echo "<th>Database</th>";
			echo "<th width='600px'>Query</th>";
			echo "</tr>";
			
			foreach ($appIds as $appId) {
				$id = $appId["id"];
				$name = $appId["name"];
				$desc = $appId["desc"];
				$query = $appId["query"];
				$dbId = $appId["db"];
				
				echo "<tr>";
				echo "<td align='center' valign='top'>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=a&sm=e&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				echo "<a href='#' onClick='confirmDeleteApp(\"$name\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=a&sm=t&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/sapu.png' alt='Test query' title='Test query'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=a&sm=l&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/list-items.gif' alt='List module' title='List module'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=a&sm=lc&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/tools.png' alt='List configuration' title='List configuration'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td valign='top'>$id&nbsp;</td>";
				echo "<td valign='top' width='100px'>$name&nbsp;</td>";
				echo "<td valign='top' width='100px'>$desc&nbsp;</td>";
				if ($dbId == null) {
					echo "<td align='center' valign='top'>-</td>";
				} else {
					$dbName = $User->GetDatabaseName($dbId);
					echo "<td valign='top' width='50px'>$dbName&nbsp;</td>";
				}
				echo "<td valign='top' width='500px'>$query&nbsp;</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteApp() {
	global $Setting, $id;
	$bOK = $Setting->DeleteApp($id);
	if ($bOK) {
		echo "<div>Application successfully deleted...</div>";
	} else {
		echo "<div>Application fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=a");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertAppAction() {
	global $Setting, $User, $idApp, $nameApp, $descApp, $dbApp, $queryApp;
	
	// $idApp = (isset($_REQUEST['idApp']) ? trim($_REQUEST['idApp']) : '');
	// $nameApp = (isset($_REQUEST['nameApp']) ? trim($_REQUEST['nameApp']) : '');
	// $descApp = (isset($_REQUEST['descApp']) ? trim($_REQUEST['descApp']) : '');
	// $dbApp = (isset($_REQUEST['dbApp']) ? trim($_REQUEST['dbApp']) : '');
	// $queryApp = (isset($_REQUEST['queryApp']) ? trim($_REQUEST['queryApp']) : '');
	
	if ($dbApp == "0") {
		$dbApp = "";
	}
	
	$bOK = $Setting->InsertApp($idApp, $nameApp, $descApp, $dbApp, $queryApp);
	
	$dbName = $User->GetDatabaseName($dbApp);
	$idApp = htmlentities($idApp, ENT_QUOTES);
	$nameApp = htmlentities($nameApp, ENT_QUOTES);
	$descApp = htmlentities($descApp, ENT_QUOTES);	
	$dbName = htmlentities($dbName, ENT_QUOTES);	
	$queryApp = htmlentities($queryApp, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=a&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formApp'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert Application";
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
	echo "<td valign='top'><input type='hidden' id='idApp' name='idApp' value='$idApp' autocomplete='off'></input>$idApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameApp' name='nameApp' value='$nameApp' autocomplete='off'></input>$nameApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descApp' name='descApp' value='$descApp' autocomplete='off'></input>$descApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Database</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='dbApp' name='dbApp' value='$dbApp' autocomplete='off'></input>$dbName</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Query</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='queryApp' name='queryApp' value='$queryApp' autocomplete='off'></input>$queryApp</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formApp\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=a");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function insertApp() {
	global $Setting, $mode, $idApp, $nameApp, $descApp, $dbApp, $queryApp;
	
	// $idApp = (isset($_REQUEST['idApp']) ? trim($_REQUEST['idApp']) : '');
	// $nameApp = (isset($_REQUEST['nameApp']) ? trim($_REQUEST['nameApp']) : '');
	// $descApp = (isset($_REQUEST['descApp']) ? trim($_REQUEST['descApp']) : '');
	// $dbApp = (isset($_REQUEST['dbApp']) ? trim($_REQUEST['dbApp']) : '');
	// $queryApp = (isset($_REQUEST['queryApp']) ? trim($_REQUEST['queryApp']) : '');
	
	if ($idApp == "") {
		// Initial insert
		$idApp = "a" . $Setting->GetNextAppId();
	}
	
	$arDatabase = null;
	$bOK = $Setting->GetDatabase($arDatabase);
	if (!$bOK) {
		return;
	}

	$idApp = htmlentities($idApp, ENT_QUOTES);
	$nameApp = htmlentities($nameApp, ENT_QUOTES);
	$descApp = htmlentities($descApp, ENT_QUOTES);	
	$dbApp = htmlentities($dbApp, ENT_QUOTES);	
	$queryApp = htmlentities($queryApp, ENT_QUOTES);	
	
	echo "<div class='subTitle'>Insert new application</div>";
	echo "<div class='spacer10'></div>";
				
	$url64 = base64_encode("setting=1&m=a&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idApp' name='idApp' value='$idApp' length='30' size='20' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameApp' name='nameApp' value='$nameApp' length='100' size='30' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textarea id='descApp' name='descApp' cols='50' rows='3'>$descApp</textarea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Database</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<select id='dbApp' name='dbApp' onChange='changeDatabase(null);'>";
	echo "<option value='0'>-----------------</option>";
	foreach ($arDatabase as $db) {
		$dbId = $db["id"];
		$dbName = $db["name"];
		
		$dbId = htmlentities($dbId);
		$dbName = htmlentities($dbName);
	
		echo "<option value='$dbId' ";
		if ($dbId == $dbApp) {
			echo "selected";
		}
		echo ">$dbName</option>";
	}
	echo "<option value='-99'>&lt; Insert new database &gt;</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Query</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textarea id='queryApp' name='queryApp' cols='50' rows='5'>$queryApp</textarea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'></td>";
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

function copyAppAction() {
	global $Setting, $User, $idApp, $nameApp, $descApp, $copyApp;
	
	$bOK = $Setting->CopyApp($idApp, $nameApp, $descApp, $copyApp);
	
	$appSourceName = $User->GetAppName($copyApp);
	$idApp = htmlentities($idApp, ENT_QUOTES);
	$nameApp = htmlentities($nameApp, ENT_QUOTES);
	$descApp = htmlentities($descApp, ENT_QUOTES);
	$appSourceName = htmlentities($appSourceName, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=a&sm=c");
	echo "<form method='POST' action='main.php?param=$url64' id='formApp'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Copy Application";
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
	echo "<td valign='top'><input type='hidden' id='idApp' name='idApp' value='$idApp'></input>$idApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameApp' name='nameApp' value='$nameApp'></input>$nameApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descApp' name='descApp' value='$descApp'></input>$descApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Source</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='copyApp' name='copyApp' value='$copyApp'></input>$appSourceName</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formApp\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=a");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function copyApp() {
	global $Setting, $mode, $idApp, $nameApp, $descApp, $copyApp;
	
	if ($idApp == "") {
		// Initial insert
		$idApp = "a" . $Setting->GetNextAppId();
	}
	
	$arDatabase = null;
	$bOK = $Setting->GetDatabase($arDatabase);
	if (!$bOK) {
		return;
	}

	$idApp = htmlentities($idApp, ENT_QUOTES);
	$nameApp = htmlentities($nameApp, ENT_QUOTES);
	$descApp = htmlentities($descApp, ENT_QUOTES);	
	
	echo "<div class='subTitle'>Copy application</div>";
	echo "<div class='spacer10'></div>";
				
	$url64 = base64_encode("setting=1&m=a&sm=c&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idApp' name='idApp' value='$idApp' length='30' size='20' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameApp' name='nameApp' value='$nameApp' length='100' size='30' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textarea id='descApp' name='descApp' cols='50' rows='3'>$descApp</textarea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Source</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<select id='copyApp' name='copyApp'>";
	echo "<option value='-1'>-------</option>";
	$bOK = $Setting->GetApp($appIds);
	if ($bOK) {
		foreach ($appIds as $appId) {
			$id = $appId["id"];
			$name = $appId["name"];

			if ($copyApp == $id) {
				echo "<option value='$id' selected>$name</option>";
			}
			echo "<option value='$id'>$name</option>";
		}
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'></td>";
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

function editAppAction() {
	global $Setting, $User, $idApp, $nameApp, $descApp, $queryApp, $dbApp;
	
	// $idApp = (isset($_REQUEST['idApp']) ? trim($_REQUEST['idApp']) : '');
	// $nameApp = (isset($_REQUEST['nameApp']) ? trim($_REQUEST['nameApp']) : '');
	// $descApp = (isset($_REQUEST['descApp']) ? trim($_REQUEST['descApp']) : '');
	// $queryApp = (isset($_REQUEST['queryApp']) ? trim($_REQUEST['queryApp']) : '');
	// $dbApp = (isset($_REQUEST['dbApp']) ? trim($_REQUEST['dbApp']) : '');

	if ($dbApp == "0") {
		$dbApp = "";
	}
	
	$bOK = $Setting->EditApp($idApp, $nameApp, $descApp, $dbApp, $queryApp);
	
	$dbName = $User->GetDatabaseName($dbApp);
	$idApp = htmlentities($idApp);
	$nameApp = htmlentities($nameApp);
	$descApp = htmlentities($descApp);
	$dbName = htmlentities($dbName);
	$queryApp = htmlentities($queryApp);
	
	$url64 = base64_encode("setting=1&m=a&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formApp'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit Application";
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
	echo "<td valign='top'><input type='hidden' id='idApp' name='idApp' value='$idApp' autocomplete='off'></input>$idApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameApp' name='nameApp' value='$nameApp' autocomplete='off'></input>$nameApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descApp' name='descApp' value='$descApp' autocomplete='off'></input>$descApp</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Database</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='dbApp' name='dbApp' value='$dbApp' autocomplete='off'></input>$dbName</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Query</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='queryApp' name='queryApp' value='$queryApp' autocomplete='off'></input>$queryApp</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formApp\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=a");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function editApp() {
	global $Setting, $id, $mode;
	
	$arApp = $Setting->GetAppDetail($id);
	$idApp = $arApp["id"];
	$nameApp = $arApp["name"];
	$descApp = $arApp["desc"];
	$queryApp = $arApp["query"];
	$dbApp = $arApp["db"];
	
	// quote fix
	$idApp = htmlentities($idApp, ENT_QUOTES);
	$nameApp = htmlentities($nameApp, ENT_QUOTES);
	$descApp = htmlentities($descApp, ENT_QUOTES);
	$queryApp = htmlentities($queryApp, ENT_QUOTES);
	$dbApp = htmlentities($dbApp, ENT_QUOTES);
	
	$arDatabase = null;
	$bOK = $Setting->GetDatabase($arDatabase);
	if (!$bOK) {
		return;
	}
	
	echo "<div class='subTitle'>Edit application '$nameApp'</div>";
	echo "<div class='spacer10'></div>";
				
	$url64 = base64_encode("setting=1&m=a&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>$idApp<input type='hidden' id='idApp' name='idApp' value='$idApp' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameApp' name='nameApp' length='100' size='30' value='$nameApp' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textarea id='descApp' name='descApp' cols='50' rows='3'>$descApp</textarea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Database</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'>";
	echo "<select id='dbApp' name='dbApp' onChange='changeDatabase(\"$id\");'>";
	echo "<option value='0'>-----------------</option>";
	foreach ($arDatabase as $db) {
		$dbId = $db["id"];
		$dbName = $db["name"];
		
		$dbId = htmlentities($dbId);
		$dbName = htmlentities($dbName);
	
		echo "<option value='$dbId' ";
		if ($dbId == $dbApp) {
			echo "selected";
		}
		echo ">$dbName</option>";
	}
	echo "<option value='-99'>&lt; Insert new database &gt;</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Query</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textarea id='queryApp' name='queryApp' cols='50' rows='5'>$queryApp</textarea></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='10px'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' align='center'>";
	$url64 = base64_encode("setting=1&m=a");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function listApp() {
	global $Setting, $id, $User;
	
	$appName = $User->GetAppName($id);
	$appName = htmlentities($appName, ENT_QUOTES);
	$dbId = $User->GetDatabaseFromApp($id);
	$dbName = $User->GetDatabaseName($dbId);
	$dbName = htmlentities($dbName, ENT_QUOTES);
	
	$arConfig = $User->GetDatabaseConfig($dbId);
	
	$terminal = null;
	$bOK = $User->GetTerminal($id, "", $terminal);
	// var_dump($terminal);
	
	echo "<div class='subTitle'>Terminal in application '$appName'</div>";
	echo "<div class='spacer10'></div>";
	
	if ($bOK === true) {
		echo "<div>From database: <b>$dbName</b></div>";
		echo "<div class='spacer10'></div>";
		echo "<table cellspacing='3px' cellpadding='3px' id='tableTerminal'>";
		echo "<tr>";
		foreach ($arConfig as $value) {
			$header = $value["value"];
			
			echo "<th>$header</th>";
		}
		echo "</tr>";
		
		// print function
		echo "<tr>";
		foreach ($terminal as $term) {
			foreach ($arConfig as $value) {
				$key = $value["key"];
				$header = $value["value"];
				
				$value = $term[$header];
				echo "<td>$value&nbsp;</td>";
			}
			echo "</tr>";
		}
	} else if ($bOK == -1) {
		// error query not specified
		echo "<div>Error: Query not specified</div>";
	} else if ($bOK == -2) {
		// error database not specified
		echo "<div>Error: Database not specified</div>";
	} else if ($bOK == -3) {
		// erorr query is not select
		echo "<div>Error: Query is not 'SELECT'</div>";
	} else if ($bOK == -4) {
		// database connection failed
		echo "<div>Error: Database connection failed</div>";
		// echo "$dbId";
		$arDatabase = $Setting->GetDatabaseDetail($dbId);
		if ($arDatabase == null) {
			return;
		}
		$nameDb = $arDatabase["name"];
		$schemaDb = $arDatabase["schema"];
		$portDb = $arDatabase["port"];
		$hostDb = $arDatabase["host"];
		$userDb = $arDatabase["user"];
		$pwdDb = $arDatabase["pwd"];
		
		$n = strlen($pwdDb);
		$pwdDbDisp = "";
		for ($i = 0; $i < $n; $i++) {
			$pwdDbDisp .= "&bull;";
		}
	
		echo "<table class='transparent'>";
		echo "<tr>";
		echo "<th colspan='3' align='left'>Detail</th>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Host</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'>$hostDb</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Port</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'>$portDb</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Schema</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'>$schemaDb</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Username</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'>$userDb</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign='top'>Password</td>";
		echo "<td valign='top'>:</td>";
		echo "<td valign='top'>$pwdDb</td>";
		echo "</tr>";
	} else if ($bOK == -5) {
		// no database configuration specified
		echo "<div>Error: Database configuration not specified</div>";
	} else if ($bOK == -6) {
		// error database not specified
		echo "<div>Error: Must accessed from module</div>";
	}
	
	// print table end
	echo "</table>";
}

function listModuleAccess($confirmMessage = null) {
	global $data, $User, $Setting, $id;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
		
		$appname = $User->GetAppName($id);
		$appname = htmlentities($appname);
		
		echo "<div class='subTitle'>List module in application '$appname'</div>";
		echo "<div class='spacer10'></div>";
		
		echo "<div class='confirmation'>$confirmMessage</div>";
				
		echo "<div class='spacer10'></div>";
		// print table app
		$arModule = $User->GetModuleAccessable($id);
		$allModuleCount = $User->GetAllModuleCount();
		$bOK = $Setting->GetModule($arAllModule);
		if ($bOK) {
			// var_dump($arModule);
			// var_dump($arAllModule);
			$url64 = base64_encode("setting=1&m=a&sm=l&i=$id");
			echo "<form action='main.php?param=$url64' method='POST'>";
			echo "<input type='hidden' id='a' name='a' value='1'></input>";
			echo "<table cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>";
			echo "<label><input type='checkbox' id='moduleAccessAll' onClick='accessAll(this.checked)' ";
			if ($allModuleCount > 0 && $allModuleCount == count($arModule)) {
				echo "checked";
			}
			echo "></input>";
			echo "Access</label>";
			echo "</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "</tr>";
			
			$i = 0;
			// var_dump($arModule);
			foreach ($arAllModule as $mod) {
				$moduleId = $mod["id"];
				$moduleName = $mod["name"];
				
				echo "<tr>";
				echo "<td align='center'>";
				echo "<input type='checkbox' id='moduleAccess$i' name='moduleAccess[$i]' value='$moduleId' onClick='if (!this.checked) document.getElementById(\"moduleAccessAll\").checked = false;' ";
				if ($arModule != null && in_array($moduleId, $arModule)) {
					echo "checked";
				}
				echo "></input>";
				echo "</td>";
				echo "<td valign='top'>$moduleId&nbsp;</td>";
				echo "<td valign='top'>$moduleName&nbsp;</td>";
				echo "</tr>";
				
				$i++;
			}
			echo "<tr>";
			echo "<td colspan='3'>";
			echo "<input type='submit' value='Save'>";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
			echo "</form>";
		}
	}
	return $bOK;
}

function saveModuleAccess() {
	global $User, $id, $moduleAccess;

	$newModuleAccess = $moduleAccess;
	$arModuleAccess = $User->GetModuleAccessable($id);
	$confirmMessage = "Nothing changed";
	
	if ($newModuleAccess == null) {
		$User->DeleteModuleAccessable($id, null);
		$confirmMessage = "Clear all module in this app";
	} else {
		$arInsert = array();
		$i = 0;
		foreach ($newModuleAccess as $newAccess) {
			if ($arModuleAccess == null || ($arModuleAccess != null && !in_array($newAccess, $arModuleAccess))) {
				$arInsert[$i] = $newAccess;
				$i++;
			}
		}
		
		if ($i > 0) {
			$User->InsertModuleAccessable($id, $arInsert);
			$confirmMessage = "Successfully change module";
		}
		
		$arDelete = array();
		$i = 0;
		if ($arModuleAccess != null) {
			foreach ($arModuleAccess as $oldAccess) {
				if (!in_array($oldAccess, $newModuleAccess)) {
					$arDelete[$i] = $oldAccess;
					$i++;
				}
			}
		}
		
		if ($i > 0) {
			$User->DeleteModuleAccessable($id, $arDelete);
			$confirmMessage = "Successfully change module";
		}
	}
	
	// display list
	listModuleAccess($confirmMessage);
}

function listAppCfg() {
	global $Setting, $User, $id;
	
	$appName = $User->GetAppName($id);
	echo "<div class='subTitle'>List configuration in application '$appName'</div>";
	echo "<div class='spacer10'></div>";
				
	// insert module configuration
	$url64 = base64_encode("setting=1&m=a&sm=ic&i=$id");
	echo "<a href='main.php?param=$url64'>Add new configuration</a>";
	echo "<div class='spacer10'></div>";
	
	$arConfig = $Setting->GetAppConfig($id);
	if ($arConfig) {
?>
<script	type='text/javascript'>
function confirmDeleteAppCfg(id, key) {
	var ans = confirm("Delete configuration '" + key + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=a&sm=dc&i=' + id + '&k=' + key);
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
			$url64 = base64_encode("setting=1&m=a&sm=ec&i=$id&k=$mKey");
			echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit configuration' title='Edit configuration'></img></a>";
			echo "&nbsp;";
			echo "<a href='#' onClick='confirmDeleteAppCfg(\"$id\", \"$mKey\")''><img border='0' src='image/icon/cancel.png' alt='Delete configuration' title='Delete configuration'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "<td valign='top'>$mKey&nbsp;</td>";
			echo "<td width='500px' valign='top'>" . nl2br($mValue) . "&nbsp;</td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		echo "No configuration available.";
	}
}

function insertAppCfgAction() {
	global $Setting, $User, $id, $mode, $subMode, $key, $value;

	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	$bOK = $Setting->InsertAppConfig($id, $key, $value);
	
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$appName = $User->GetAppName($id);
	
	$url64 = base64_encode("setting=1&m=a&sm=ic&i=$id");
	echo "<form method='POST' action='main.php?param=$url64' id='formApp'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert application $appName's configuration";
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
	echo "<td valign='top' width='200'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
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
		echo "<a href='#' onClick='document.getElementById(\"formApp\").submit();'>Edit configuration</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=$mode&sm=lc&i=$id");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function insertAppCfg() {
	global $id, $User, $mode, $subMode, $key, $value;
	
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$appName = $User->GetAppName($id);
	echo "<div class='subTitle'>Add new configuration for application '$appName'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=$mode&sm=$subMode&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Key</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='key' id='key' size='20' length='45' value='$key' autocomplete='off'></input></td>";
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
	$url64 = base64_encode("setting=1&m=$mode&sm=$subMode&i=$id");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function editAppCfgAction() {
	global $Setting, $User, $id, $mode, $oldKey, $key, $value, $ar, $md;

	// $oldKey = (isset($_REQUEST['oldKey']) ? trim($_REQUEST['oldKey']) : '');
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	// Redirect from module approve sms
	// $appId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $moduleId = (isset($_REQUEST['md']) ? trim($_REQUEST['md']) : '');
	$appId = $ar;
	$moduleId = $md;
	
	$bOK = $Setting->EditAppConfig($id, $oldKey, $key, $value);
	
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$appName = $User->GetAppName($id);
	
	$url64 = base64_encode("setting=1&m=$mode&sm=ic&i=$id&k=$oldKey");
	echo "<form method='POST' action='main.php?param=$url64' id='formApp'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit module $appName's configuration";
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
		echo "<a href='#' onClick='document.getElementById(\"formApp\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		if ($appId != "" && $moduleId != "") {
			$url64 = base64_encode("a=$appId&m=$moduleId");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Redirect to locket SMS... <img src='image/icon/wait.gif' alt=''></img>";
		} else {
			$url64 = base64_encode("setting=1&m=$mode&sm=lc&i=$id");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		}
	}
	echo "</div>";
}

function editAppCfg() {
	global $Setting, $User, $id, $mode, $subMode, $o, $k, $ar, $md;
	
	// $oldKey = (isset($_REQUEST['o']) ? trim($_REQUEST['o']) : '');
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$oldKey = $o;
	$key = $k;
	if ($oldKey == "") {
		$oldKey = $key;
	}
	
	// Redirect from module approve sms
	// $appId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $moduleId = (isset($_REQUEST['md']) ? trim($_REQUEST['md']) : '');
	$appId = $ar;
	$moduleId = $md;
	
	$value = $Setting->GetAppConfigValue($id, $oldKey);
	
	// quote fix
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$appName = $User->GetAppName($id);
	echo "<div class='subTitle'>Edit configuration '$oldKey' in application '$appName'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=$mode&sm=$subMode&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Key</td>";
	echo "<td>:</td>";
	echo "<td>";
	if ($appId != "" && $moduleId != "") {
		echo "<input type='hidden' name='key' id='key' value='$key'></input>$key";
		echo "<input type='hidden' name='oldKey' id='oldKey' value='$oldKey'></input>";
		echo "<input type='hidden' name='ar' id='ar' value='$appId'></input>";
		echo "<input type='hidden' name='md' id='md' value='$moduleId'></input>";
	} else {
		echo "<input type='text' name='key' id='key' size='20' length='45' value='$key' autocomplete='off'></input>";
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
	$url64 = base64_encode("setting=1&m=$mode&sm=$subMode&i=$id");
	echo "<input type='button' value='Cancel' onClick='window.location.href=\"main.php?param=$url64\"'></input>";
	echo "&nbsp;&nbsp;";
	echo "<input type='submit' value='Save'></input>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function deleteAppCfg() {
	global $Setting, $id, $mode, $k;
	$key = $k;
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$bOK = $Setting->DeleteAppConfig($id, $key);
	if ($bOK) {
		echo "<div>Application configuration successfully deleted...</div>";
	} else {
		echo "<div>Application configuration fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=$mode&sm=lc&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}



?>
