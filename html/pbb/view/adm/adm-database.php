<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management Database
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertDatabaseAction();
	} else {
		insertDatabase();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editDatabaseAction();
	} else {
		editDatabase();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteDatabase();
} else if ($subMode == "t") {
	// Submode test connection
	testConnection();
} else if ($subMode == "lc") {
	// Submode list configuration
	if ($action) {
		copyConfiguration();
	} else {
		listConfiguration();
	}
} else if ($subMode == "ic") {
	// Submode insert database configuration
	if ($action) {
		insertDatabaseCfgAction();
	} else {
		insertDatabaseCfg();
	}
} else if ($subMode == "ec") {
	// Submode edit database configuration
	if ($action) {
		editDatabaseCfgAction();
	} else {
		editDatabaseCfg();
	}
} else if ($subMode == "dc") {
	// Submode delete database configuration
	deleteDatabaseCfg();
} else {
	// List all
	$bOK = printDatabaseSetting();
	if (!$bOK) {
		return;
	}
}

function printDatabaseSetting() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
				
		echo "<div class='subTitle'>List Databases</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=d&sm=i");
		echo "<a href='main.php?param=$url64'>Add new database</a>";
		echo "<div class='spacer10'></div>";
		
		// print table Database
		$arDatabases = null;
		$bOK = $Setting->GetDatabase($arDatabases);
		if ($bOK) {
?>
<script type='text/javascript'>
function confirmDeleteDb(name, id) {
	var ans = confirm("Delete database '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=d&sm=d&i=' + id);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php	
			echo "<table class=\"table table-bordered\" cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "<th>Schema</th>";
			echo "<th>Host</th>";
			echo "<th>Port</th>";
			echo "<th>Username</th>";
			echo "</tr>";
			
			foreach ($arDatabases as $db) {
				$dbId = $db["id"];
				$dbName = $db["name"];
				$dbSchema = $db["schema"];
				$dbHost = $db["host"];
				$dbPort = $db["port"];
				$dbUser = $db["user"];
				
				echo "<tr>";
				echo "<td align='center'>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=d&sm=e&i=$dbId");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				echo "<a href='#' onClick='confirmDeleteDb(\"$dbName\", \"$dbId\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=d&sm=t&i=$dbId");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/coll_go.png' alt='Test connection' title='Test connection'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=d&sm=lc&i=$dbId");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/tools.png' alt='List configuration' title='List configuration'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td>$dbId&nbsp;</td>";
				echo "<td>$dbName&nbsp;</td>";
				echo "<td>$dbSchema&nbsp;</td>";
				echo "<td>$dbHost&nbsp;</td>";
				echo "<td>$dbPort&nbsp;</td>";
				echo "<td>$dbUser&nbsp;</td>";
				echo "</tr>";
			}
			
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteDatabase() {
	global $Setting, $id;
	$bOK = $Setting->DeleteDatabase($id);
	if ($bOK) {
		echo "<div>Database successfully deleted...</div>";
	} else {
		echo "<div>Database fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=d");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertDatabase() {
	global $Setting, $User, $idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb, $ar, $mode;
	
	// $idDb = (isset($_REQUEST['idDb']) ? trim($_REQUEST['idDb']) : '');
	// $nameDb = (isset($_REQUEST['nameDb']) ? trim($_REQUEST['nameDb']) : '');
	// $schemaDb = (isset($_REQUEST['schemaDb']) ? trim($_REQUEST['schemaDb']) : '');
	// $hostDb = (isset($_REQUEST['hostDb']) ? trim($_REQUEST['hostDb']) : '');
	// $portDb = (isset($_REQUEST['portDb']) ? trim($_REQUEST['portDb']) : '');
	// $userDb = (isset($_REQUEST['userDb']) ? trim($_REQUEST['userDb']) : '');
	// $pwdDb = (isset($_REQUEST['pwdDb']) ? trim($_REQUEST['pwdDb']) : '');
	
	// Insert from area
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// echo "ar = $ar<br />";
	$areaId = $ar;
	
	if ($idDb == "") {
		// initial insert, ambil next id 'd?'
		$idDb = "d" . $Setting->GetNextDbId();
	}

	$idDb = htmlentities($idDb, ENT_QUOTES);
	$nameDb = htmlentities($nameDb, ENT_QUOTES);
	$schemaDb = htmlentities($schemaDb, ENT_QUOTES);
	$hostDb = htmlentities($hostDb, ENT_QUOTES);
	$portDb = htmlentities($portDb, ENT_QUOTES);
	$userDb = htmlentities($userDb, ENT_QUOTES);
	$pwdDb = htmlentities($pwdDb, ENT_QUOTES);
	
	echo "<div class='subTitle'>Insert new database";
	// NEW: Insert from area
	$forArea = false;
	if ($areaId) {
		$areaName = $User->GetAreaName($areaId);
		$areaName = htmlentities($areaName);
		echo " for area '$areaName'";
		$forArea = true;
	}
	echo "</div>";
	echo "<div class='spacer10'></div>";
		
	$url64 = base64_encode("setting=1&m=d&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64' onSubmit='return cekConfirmPassword(\"pwdDb\", \"pwdDb2\");'>";
	echo "<input type='hidden' id='ar' name='ar' value='$areaId'></input>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idDb' name='idDb' length='30' size='20' value='$idDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameDb' name='nameDb' length='100' size='30' value='$nameDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Schema</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='schemaDb' name='schemaDb' length='100' size='30' value='$schemaDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Host</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='hostDb' name='hostDb' length='100' size='30' value='$hostDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Port</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='portDb' name='portDb' length='100' size='30' value='$portDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Username</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='userDb' name='userDb' length='100' size='30' value='$userDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdDb' name='pwdDb' length='100' size='30' value='' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Confirm Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdDb2' name='pwdDb2' length='100' size='30' value='' autocomplete='off'></input></td>";
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

function insertDatabaseAction() {
	global $Setting, $idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb, $ar;
	
	// $idDb = (isset($_REQUEST['idDb']) ? trim($_REQUEST['idDb']) : '');
	// $nameDb = (isset($_REQUEST['nameDb']) ? trim($_REQUEST['nameDb']) : '');
	// $schemaDb = (isset($_REQUEST['schemaDb']) ? trim($_REQUEST['schemaDb']) : '');
	// $hostDb = (isset($_REQUEST['hostDb']) ? trim($_REQUEST['hostDb']) : '');
	// $portDb = (isset($_REQUEST['portDb']) ? trim($_REQUEST['portDb']) : '');
	// $userDb = (isset($_REQUEST['userDb']) ? trim($_REQUEST['userDb']) : '');
	// $pwdDb = (isset($_REQUEST['pwdDb']) ? trim($_REQUEST['pwdDb']) : '');
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	$areaId = $ar;
	
	$bOK = $Setting->InsertDatabase($idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb);

	$idDb = htmlentities($idDb, ENT_QUOTES);
	$nameDb = htmlentities($nameDb, ENT_QUOTES);
	$schemaDb = htmlentities($schemaDb, ENT_QUOTES);
	$hostDb = htmlentities($hostDb, ENT_QUOTES);
	$portDb = htmlentities($portDb, ENT_QUOTES);
	$userDb = htmlentities($userDb, ENT_QUOTES);
	$pwdDb = htmlentities($pwdDb, ENT_QUOTES);
	
	// DEPRECATED: Password database disimpan plain-text
	$n = strlen($pwdDb);
	$pwdDbDisp = "";
	for ($i = 0; $i < $n; $i++) {
		$pwdDbDisp .= "&bull;";
	}
	
	$url64 = base64_encode("setting=1&m=d&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formDb'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert Database";
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
	echo "<td valign='top'><input type='hidden' id='idDb' name='idDb' value='$idDb'></input>$idDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameDb' name='nameDb' value='$nameDb'></input>$nameDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Schema</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='schemaDb' name='schemaDb' value='$schemaDb'></input>$schemaDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Host</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='hostDb' name='hostDb' value='$hostDb'></input>$hostDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Port</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='portDb' name='portDb' value='$portDb'></input>$portDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Username</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='userDb' name='userDb' value='$userDb'></input>$userDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='pwdDb' name='pwdDb' value='$pwdDb'></input>$pwdDbDisp</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formDb\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		// echo "areaId = $areaId<br />";
		if ($areaId == null) {
			$url64 = base64_encode("setting=1&m=d");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		} else {
			// Saving for area id
?>
<script type'text/javascript'>
// Db information
var idDb = document.getElementById('idDb').value;
var nameDb = document.getElementById('nameDb').value;

// Add saved database to option
var dbArea = parent.opener.document.getElementById('dbArea');
dbArea.options[dbArea.options.length - 1] = new Option(nameDb, idDb, true);

// Add new option
dbArea.options[dbArea.options.length] = new Option("< Insert new role >", -99);

// Close self
self.close();
</script>
<?php
		}
	}
	echo "</div>";
	echo "</form>";
}

function editDatabase() {
	global $Setting, $id;
	
	$arDatabase = $Setting->GetDatabaseDetail($id);
	if ($arDatabase == null) {
		return;
	}
	$idDb = $arDatabase["id"];
	$nameDb = $arDatabase["name"];
	$schemaDb = $arDatabase["schema"];
	$hostDb = $arDatabase["host"];
	$portDb = $arDatabase["port"];
	$userDb = $arDatabase["user"];
	$pwdDb = $arDatabase["pwd"];
	
	// quote fix
	$idDb = htmlentities($idDb, ENT_QUOTES);
	$nameDb = htmlentities($nameDb, ENT_QUOTES);
	$schemaDb = htmlentities($schemaDb, ENT_QUOTES);
	$hostDb = htmlentities($hostDb, ENT_QUOTES);
	$portDb = htmlentities($portDb, ENT_QUOTES);
	$userDb = htmlentities($userDb, ENT_QUOTES);
	$pwdDb = htmlentities($pwdDb, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit database '$nameDb'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=d&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64' onSubmit='return cekConfirmPassword(\"pwdDb\", \"pwdDb2\");'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idDb' name='idDb' value='$idDb'></input>$idDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameDb' name='nameDb' length='100' size='30' value='$nameDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Schema</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='schemaDb' name='schemaDb' length='100' size='30' value='$schemaDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Host</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='hostDb' name='hostDb' length='100' size='30' value='$hostDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Port</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='portDb' name='portDb' length='100' size='30' value='$portDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Username</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='userDb' name='userDb' length='100' size='30' value='$userDb' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='3' height='25px' valign='bottom'>";
	echo "<div style='font-size:8pt; text-align:center;'><b>for reset password only</b></div>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdDb' name='pwdDb' length='100' size='30' value='' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Confirm Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='password' id='pwdDb2' name='pwdDb2' length='100' size='30' value='' autocomplete='off'></input></td>";
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

function editDatabaseAction() {
	global $Setting, $idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb;
	
	// $idDb = (isset($_REQUEST['idDb']) ? trim($_REQUEST['idDb']) : '');
	// $nameDb = (isset($_REQUEST['nameDb']) ? trim($_REQUEST['nameDb']) : '');
	// $schemaDb = (isset($_REQUEST['schemaDb']) ? trim($_REQUEST['schemaDb']) : '');
	// $hostDb = (isset($_REQUEST['hostDb']) ? trim($_REQUEST['hostDb']) : '');
	// $portDb = (isset($_REQUEST['portDb']) ? trim($_REQUEST['portDb']) : '');
	// $userDb = (isset($_REQUEST['userDb']) ? trim($_REQUEST['userDb']) : '');
	// $pwdDb = (isset($_REQUEST['pwdDb']) ? trim($_REQUEST['pwdDb']) : '');
	
	$n = strlen($pwdDb);
	$pwdDbDisp = "";
	for ($i = 0; $i < $n; $i++) {
		$pwdDbDisp .= "&bull;";
	}
	
	$bOK = $Setting->EditDatabase($idDb, $nameDb, $schemaDb, $hostDb, $portDb, $userDb, $pwdDb);

	$idDb = htmlentities($idDb, ENT_QUOTES);
	$nameDb = htmlentities($nameDb, ENT_QUOTES);
	$schemaDb = htmlentities($schemaDb, ENT_QUOTES);
	$hostDb = htmlentities($hostDb, ENT_QUOTES);
	$portDb = htmlentities($portDb, ENT_QUOTES);
	$userDb = htmlentities($userDb, ENT_QUOTES);
	$pwdDb = htmlentities($pwdDb, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=d&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formDb'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit Database";
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
	echo "<td valign='top'><input type='hidden' id='idDb' name='idDb' value='$idDb'></input>$idDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameDb' name='nameDb' value='$nameDb'></input>$nameDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Schema</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='schemaDb' name='schemaDb' value='$schemaDb'></input>$schemaDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Host</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='hostDb' name='hostDb' value='$hostDb'></input>$hostDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Port</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='portDb' name='portDb' value='$portDb'></input>$portDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Username</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='userDb' name='userDb' value='$userDb'></input>$userDb</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Password</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='pwdDb' name='pwdDb' value='$pwdDb'></input>$pwdDbDisp</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formDb\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=d");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function listArea() {
	global $Setting, $User, $id;
	
	$arArea = null;
	$bOK = $Setting->GetArea($arArea);
	$bOK2 = $Setting->GetRole($arRole);
	
	$username = $User->GetUsername($id);
	
	echo "<div class='subTitle'>Area & role from user '$username'</div>";
	echo "<div class='spacer10'></div>";
	
	if ($bOK && $bOK2) {
		// var_dump($arArea);
		
		echo "<table cellspacing='3px' cellpadding='3px'>";
		echo "<tr>";
		echo "<th>Area</th>";
		echo "<th>Role Module</th>";
		echo "<th>Change</th>";
		echo "</tr>";
		
		$i = 1;
		foreach ($arArea as $area) {
			$areaId = $area["id"];
			$areaName = $area["name"];
			
			echo "<tr>";
			echo "<td align='center'>$areaName&nbsp;</td>";

			echo "<td align='center'>";
			$role = $Setting->GetRoleInArea($id, $areaId);
			if ($role) {
				$roleName = $User->GetRoleName($role);
				echo "<span id='role$i' style='color:green; font-weight:bold;'>Granted as $roleName</span>";
			} else {
				echo "<span id='role$i' style='color:red; font-weight:bold;'>Decline</span>";
			}
			echo "<select id='selectRole$i' name='selectRole$i' style='display:none' onFocus='focusSelectRole($i);' onBlur='selectSelectRole($i);' onChange='changeRole($i, \"$id\", \"$areaId\");'>";
			echo "<option value='0'>---------</option>";
			echo "<option value='-1'>Decline</option>";
			foreach ($arRole as $rowRole) {
				$rowRoleId = $rowRole["id"];
				$rowRoleName = $rowRole["name"];
				$rowRoleName = htmlentities($rowRoleName);
				echo "<option value='$rowRoleId'>Grant as $rowRoleName</option>";
			}
			
			echo "<option value='new'>&lt; Insert new role &gt;</option>";
			echo "</select>";
			echo "</td>";
			
			echo "<td align='center'>";
			echo "&nbsp;";
			echo "<a href='#' onClick='showSelectRole($i)'><img border='0' src='image/icon/group_key.png' alt='Change role' title='Change role'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "</tr>";
			
			$i++;
		}
		
		echo "</table>";
	}
}

function changeRole() {
	global $Setting, $id, $role, $ar, $r;
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$areaId = $ar;
	$roleId = $r;

	// echo "user = $id<br />";
	// echo "area = $areaId<br />";
	// echo "role = $roleId<br />";
	
	$bOK = $Setting->ChangeRole($id, $areaId, $roleId);
	if ($bOK) {
		echo "<div>Role successfully changed...</div>";
	} else {
		echo "<div>Role fail to change...</div>";
	}
	$url64 = base64_encode("setting=1&m=u&sm=lc&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function testConnection() {
	global $Setting, $id, $role;
	
	$arDatabase = $Setting->GetDatabaseDetail($id);
	if ($arDatabase == null) {
		return;
	}
	$linkDb = null;
	$connDb = null;
	$nameDb = $arDatabase["name"];
	$schemaDb = $arDatabase["schema"];
	$portDb = $arDatabase["port"];
	$hostDb = $arDatabase["host"];
	$hostPort = $hostDb . ":" . $portDb;
	$userDb = $arDatabase["user"];
	$pwdDb = $arDatabase["pwd"];
	
	$n = strlen($pwdDb);
	$pwdDbDisp = "";
	for ($i = 0; $i < $n; $i++) {
		$pwdDbDisp .= "&bull;";
	}
	
	// HIDING ERROR/WARNING
	echo "<span style='display:block'>";
	SCANPayment_ConnectToDB($linkDb, $connDb, $hostPort, $userDb, $pwdDb, $schemaDb);
	echo "</span>";
	
	echo "<div>Test connection database '$nameDb' ... ";
	$succeed = true;
	if ($linkDb != null && $connDb != null) {
		echo "succeed!</div>";
	} else {
		echo "failed!</div>";
		$succeed = false;
	}
	echo "<div class='spacer10'></div>";
	
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
	echo "<td valign='top'>$pwdDbDisp</td>";
	echo "</tr>";
	echo "</table>";
	
	if (!$succeed) {
		echo "<div class='spacer20'></div>";
		$url64 = base64_encode("setting=1&m=d&sm=e&i=$id");
		echo "<div><a href='main.php?param=$url64'>Edit form</a></div>";
	}
}


function listConfiguration() {
	global $Setting, $id, $User;
	
	$dbName = $User->GetDatabaseName($id);
	$dbName = htmlentities($dbName, ENT_QUOTES);
	
	echo "<div class='subTitle'>List table configuration in database '$dbName'</div>";
	echo "<div class='spacer10'></div>";
				
	// insert database configuration
	$url64 = base64_encode("setting=1&m=d&sm=ic&i=$id");
	echo "<a href='main.php?param=$url64'>Add new configuration</a>";
	echo "<div class='spacer10'></div>";
	
	$arConfig = $User->GetDatabaseConfig($id);
	if ($arConfig) {
?>
<script type='text/javascript'>
function confirmDeleteDatabaseCfg(id, key) {
	var ans = confirm("Delete configuration '" + key + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=d&sm=dc&i=' + id + '&k=' + key);
		window.location.href = 'main.php?param=' + url;
	}
}
</script>
<?php
		echo "<table cellspacing='3px' cellpadding='3px'>";
		echo "<tr>";
		echo "<th>Option</th>";
		echo "<th>Position</th>";
		echo "<th>Column</th>";
		echo "<th>Header</th>";
		echo "</tr>";
		foreach ($arConfig as $conf) {
			$pos = $conf["pos"];
			$key = $conf["key"];
			$value = $conf["value"];
			
			echo "<tr>";
			echo "<td>";
			echo "&nbsp;";
			$url64 = base64_encode("setting=1&m=d&sm=ec&i=$id&k=$key");
			echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit configuration' title='Edit configuration'></img></a>";
			echo "&nbsp;";
			echo "<a href='#' onClick='confirmDeleteDatabaseCfg(\"$id\", \"$key\")''><img border='0' src='image/icon/cancel.png' alt='Delete configuration' title='Delete configuration'></img></a>";
			echo "&nbsp;";
			echo "</td>";
			echo "<td>$pos&nbsp;</td>";
			echo "<td>$key&nbsp;</td>";
			echo "<td>$value&nbsp;</td>";
			echo "</tr>";
		}
		echo "</table>";
	} else {
		$arDatabases = null;
		$bOK = $Setting->GetDatabase($arDatabases);
		if ($bOK) {
			// copy database configuration
			$url64 = base64_encode("setting=1&m=d&sm=lc&i=$id");
			echo "<form method='POST' action='main.php?param=$url64'>";
			echo "<input type='hidden' id='a' name='a' value='1'></input>";
			echo "<div>";
			echo "No configuration available. Init configuration from ";
			echo "<select id='dbIdInit' name='dbIdInit'>";
			echo "<option value='-'>--------</option>";
			foreach ($arDatabases as $db) {
				$dbId = $db["id"];
				$dbName = $db["name"];
			
				if ($dbId != $id) {
					echo "<option value='$dbId'>$dbName</option>";
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

function copyConfiguration() {
	global $Setting, $id, $dbIdInit;
	
	if ($dbIdInit != "") {
		$Setting->CopyConfigDatabase($dbIdInit, $id);
	}

	listConfiguration();
}

function insertDatabaseCfgAction() {
	global $Setting, $User, $id, $pos, $key, $value;

	// $pos = (isset($_REQUEST['pos']) ? trim($_REQUEST['pos']) : '');
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	$bOK = $Setting->InsertDatabaseConfig($id, $pos, $key, $value);
	
	$dbName = $User->GetDatabaseName($id);
	$dbName = htmlentities($dbName, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=d&sm=ic&i=$id");
	echo "<form method='POST' action='main.php?param=$url64' id='formArea'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert database $dbName's configuration";
	if ($bOK) {
		echo " succeed";
	} else {
		echo " failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top' width='200'><input type='hidden' id='pos' name='pos' value='$pos' autocomplete='off'></input>$pos</td>";
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
		echo "<a href='#' onClick='document.getElementById(\"formArea\").submit();'>Edit configuration</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=d&sm=lc&i=$id");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
	echo "</form>";
}

function insertDatabaseCfg() {
	global $Setting, $id, $User, $pos, $key, $value;
	
	// $pos = (isset($_REQUEST['pos']) ? trim($_REQUEST['pos']) : '');
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	
	if ($pos == "") {
		// Initial insert
		$pos = $Setting->GetNextPosDatabaseCfg($id);
	}
	$dbName = $User->GetDatabaseName($id);
	
	$pos = htmlentities($pos, ENT_QUOTES);
	$dbName = htmlentities($dbName, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	echo "<div class='subTitle'>Add new configuration for database '$dbName'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=d&sm=ic&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Position</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='pos' id='pos' size='5' length='11' value='$pos' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Column</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='key' id='key' size='20' length='45' value='$key' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Header</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='value' id='value' size='20' length='100' value='$value' autocomplete='off'></input></td>";
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

function editDatabaseCfgAction() {
	global $Setting, $User, $id, $oldKey, $key, $value, $pos;

	// $oldKey = (isset($_REQUEST['oldKey']) ? trim($_REQUEST['oldKey']) : '');
	// $key = (isset($_REQUEST['key']) ? trim($_REQUEST['key']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	// $pos = (isset($_REQUEST['pos']) ? trim($_REQUEST['pos']) : '');
	
	$bOK = $Setting->EditDatabaseConfig($id, $oldKey, $pos, $key, $value);
	
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	$dbName = $User->GetDatabaseName($id);
	
	$url64 = base64_encode("setting=1&m=a&sm=ec&i=$id&k=$oldKey");
	echo "<form method='POST' action='main.php?param=$url64' id='formDatabase'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit database $dbName's configuration";
	if ($bOK) {
		echo " succeed";
	} else {
		echo " failed";
	}
	echo "</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Position</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='pos' name='pos' value='$pos' autocomplete='off'></input>$pos</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Column</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='key' name='key' value='$key' autocomplete='off'></input>$key</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Header</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='value' name='value' value='$value' autocomplete='off'></input>$value</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formDatabase\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=d&sm=lc&i=$id");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function editDatabaseCfg() {
	global $Setting, $User, $id, $o, $k, $pos, $value;
	
	// $oldKey = (isset($_REQUEST['o']) ? trim($_REQUEST['o']) : '');
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$oldKey = $o;
	$key = $k;
	// $pos = (isset($_REQUEST['pos']) ? trim($_REQUEST['pos']) : '');
	// $value = (isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '');
	if ($oldKey == "") {
		$oldKey = $key;
	}
	
	$ar = $Setting->GetDatabaseConfigValue($id, $oldKey);
	// var_dump($ar);
	// echo "pos = $pos";
	// echo "value = $value";
	if ($pos == "" && $value == "") {
		$pos = $ar["pos"];
		$value = $ar["value"];
	}
	
	// quote fix
	$dbName = $User->GetDatabaseName($id);
	$dbName = htmlentities($dbName, ENT_QUOTES);
	$oldKey = htmlentities($oldKey, ENT_QUOTES);
	$key = htmlentities($key, ENT_QUOTES);
	$value = htmlentities($value, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit configuration '$oldKey' in database '$dbName'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=d&sm=ec&i=$id&a=1");
	echo "<form action='main.php?param=$url64' method='POST'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td>Position</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='pos' id='pos' size='5' length='20' value='$pos' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Column</td>";
	echo "<td>:</td>";
	echo "<td>";
	echo "<input type='text' name='key' id='key' size='20' length='45' value='$key' autocomplete='off'></input>";
	echo "<input type='hidden' name='oldKey' id='oldKey' value='$oldKey' autocomplete='off'></input>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Header</td>";
	echo "<td>:</td>";
	echo "<td><input type='text' name='value' id='value' size='20' length='100' value='$value' autocomplete='off'></input></td>";
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

function deleteDatabaseCfg() {
	global $Setting, $id, $k;
	// $key = (isset($_REQUEST['k']) ? trim($_REQUEST['k']) : '');
	$key = $k;
	$bOK = $Setting->DeleteDatabaseConfig($id, $key);
	if ($bOK) {
		echo "<div>Database configuration successfully deleted...</div>";
	} else {
		echo "<div>Database configuration fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=d&sm=lc&i=$id");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}


?>
