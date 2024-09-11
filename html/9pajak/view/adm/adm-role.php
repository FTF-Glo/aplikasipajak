<?php

// Prevent direct access to file
if ($User == null) {
	die();
}

// Mode management Role
if ($subMode == "i") {
	// Submode insert
	if ($action) {
		// get parameter
		insertRoleAction();
	} else {
		insertRole();
	}
} else if ($subMode == "e") {
	// Submode edit
	if ($action) {
		// get parameter
		editRoleAction();
	} else {
		editRole();
	}
} else if ($subMode == "d") {
	// Submode delete
	deleteRole();
} else if ($subMode == "l") {
	// Submode list
	listModule();
} else if ($subMode == "g") {
	// Submode grant
	grantModule();
} else if ($subMode == "c") {
	// Submode decline
	declineModule();
} else if ($subMode == "a") {
	// Auto load
	chooseAutoLoad();
} else {
	// List all
	$bOK = printRoleSetting();
	if (!$bOK) {
		return;
	}
}
	
function printShowHide($userId, $array, $index, $mode, $charLimit, $lineLimit) {
	$first = true;
	$charCounter = 0;
	$lineCounter = 0;
	$hide = false;
	foreach ($array as $iter) {
		// $iterId = $iter["id"];
		// $iterName = $iter["name"];
		$iterValue = $iter[$index];
		
		// Comma
		if ($first) {
			$first = false;
		} else {
			echo ", ";
		}
		
		// Cek new line
		$lengthChar = strlen($iterValue);
		if ($charCounter + $lengthChar > $charLimit && $charCounter > 0) {
			echo "<br />";
			$charCounter = 0;
			$lineCounter++;
		}
		
		// Cek show/hide
		if ($lineCounter >= $lineLimit && !$hide) {
			echo "<div id='showLink" . $mode . $userId . "'>... <a href='#' style='text-decoration:none'" .
					"onClick='showMore(\"" . $mode . "\", \"$userId\")'>(see more)</a></div>";
			echo "<div class='hide' id='more" . $mode . $userId . "'>";
			$hide = true;
		}
		
		echo $iterValue;
		$charCounter += $lengthChar;
	}
	if ($hide) {
		echo "<br /><a href='#' style='text-decoration:none' id='hideLink" . $mode . $userId . "' " .
				"onClick='hideMore(\"" . $mode . "\", \"$userId\")'>(hide)</a>";
		echo "</div>";
	}
}

function printRoleSetting() {
	global $cData, $data, $json, $User, $Setting;
	$bOK = false;
	
	if ($data) {
		$uid = $data->uid;
				
		echo "<div class='subTitle'>List role modules</div>";
		echo "<div class='spacer10'></div>";
		
		$url64 = base64_encode("setting=1&m=r&sm=i");
		echo "<a href='main.php?param=$url64'>Add new role</a>";
		echo "<div class='spacer10'></div>";
		
		// print table Role
		$bOK = $Setting->GetRole($RoleIds);
		if ($bOK) {
?>
<script type='text/javascript'>
function confirmDeleteRole(name, id) {
	var ans = confirm("Delete role '" + name + "' ?");
	if (ans) {
		var url = Base64.encode('setting=1&m=r&sm=d&i=' + id);
		window.location.href = 'main.php?param=' + url;
	}
}

function showMore(mode, userId) {
	var div = document.getElementById('more' + mode + userId);
	div.style.visibility = "visible";
	div.style.display = "block";
	
	var link = document.getElementById('showLink' + mode + userId);
	link.style.visibility = "hidden";
	link.style.display = "none";
}

function hideMore(mode, userId) {
	var div = document.getElementById('more' + mode + userId);
	div.style.visibility = "hidden";
	div.style.display = "none";
	
	var link = document.getElementById('showLink' + mode + userId);
	link.style.visibility = "visible";
	link.style.display = "block";
}
</script>
<?php
			// var_dump($RoleIds);
			echo "<table cellspacing='3px' cellpadding='3px'>";
			echo "<tr>";
			echo "<th>Option</th>";
			echo "<th>Id</th>";
			echo "<th>Name</th>";
			echo "<th>Description</th>";
			echo "<th>List Accessable Module</th>";
			echo "<th>Auto-Load</th>";
			echo "</tr>";
			
			foreach ($RoleIds as $RoleId) {
				$id = $RoleId["id"];
				$name = $RoleId["name"];
				$desc = $RoleId["desc"];
				$autoLoad = $RoleId["autoLoad"];
				
				echo "<tr>";
				echo "<td align='center' valign='top'>";
				echo "&nbsp;";
				// edit
				$url64 = base64_encode("setting=1&m=r&sm=e&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/mgmt.gif' alt='Edit' title='Edit'></img></a>";
				echo "&nbsp;";
				// delete
				echo "<a href='#' onClick='confirmDeleteRole(\"$name\", \"$id\")'><img border='0' src='image/icon/cancel.png' alt='Delete' title='Delete'></img></a>";
				echo "&nbsp;";
				// edit module access
				$url64 = base64_encode("setting=1&m=r&sm=l&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/list-items.gif' alt='Edit Module Access' title='Edit Module Access'></img></a>";
				echo "&nbsp;";
				// choose auto-load
				$url64 = base64_encode("setting=1&m=r&sm=a&i=$id");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/wizard.png' alt='Choose Auto-Load' title='Choose Auto-Load'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td valign='top'>$id&nbsp;</td>";
				echo "<td valign='top'>$name&nbsp;</td>";
				echo "<td valign='top'>$desc&nbsp;</td>";
				
				// NEW: accessable module name
				echo "<td valign='top'>";
				$arAccessedModule = $User->GetAccessableModuleName($id);
				if ($arAccessedModule != null) {
					printShowHide($id, $arAccessedModule, "moduleName", "Module", 50, 4);
				}
				echo "</td>";
				
				// NEW: auto-load
				if ($autoLoad == null) {
					$nameLoad = "-";
				} else {
					$ar = explode(",", $autoLoad);
					
					$count = count($ar);
					$areaId = $ar[0];
					$arArea = $Setting->GetAreaDetail($areaId);
					$nameLoad = "&nbsp;Application '" . $arArea["name"] . "'&nbsp;";
					$appLoad = $areaId;
					if ($count > 1) {
						$moduleId = $ar[1];
						$arModule = $Setting->GetModuleDetail($moduleId);
						$nameLoad .= "<br />&nbsp;Module '" . $arModule["name"] . "'&nbsp;";
						$moduleLoad = $moduleId;
					}
				}
				echo "<td valign='top' align='center'>$nameLoad</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
	return $bOK;
}

function deleteRole() {
	global $Setting, $id;
	$bOK = $Setting->DeleteRole($id);
	if ($bOK) {
		echo "<div>Role successfully deleted...</div>";
	} else {
		echo "<div>Role fail to delete...</div>";
	}
	$url64 = base64_encode("setting=1&m=r");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function insertRoleAction() {
	global $Setting, $User, $id, $idRole, $nameRole, $descRole, $ref, $u, $ar;
	
	// $idRole = (isset($_REQUEST['idRole']) ? trim($_REQUEST['idRole']) : '');
	// $nameRole = (isset($_REQUEST['nameRole']) ? trim($_REQUEST['nameRole']) : '');
	// $descRole = (isset($_REQUEST['descRole']) ? trim($_REQUEST['descRole']) : '');

	$bOK = $Setting->InsertRole($idRole, $nameRole, $descRole);
	
	$idRole = htmlentities($idRole, ENT_QUOTES);
	$nameRole = htmlentities($nameRole, ENT_QUOTES);
	$descRole = htmlentities($descRole, ENT_QUOTES);
	
	$url64 = base64_encode("setting=1&m=r&sm=i");
	echo "<form method='POST' action='main.php?param=$url64' id='formRole'>";
	
	// NEW: bisa back to change role from user
	// $ref = (isset($_REQUEST['ref']) ? trim($_REQUEST['ref']) : '');
	// $userId = (isset($_REQUEST['u']) ? trim($_REQUEST['u']) : '');
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	$userId = $u;
	$areaId = $ar;
	if ($ref == "changeRole" && $userId != "") {
		echo "<input type='hidden' id='ref' name='ref' value='changeRole'></input>";
		echo "<input type='hidden' id='u' name='u' value='$userId'></input>";
		echo "<input type='hidden' id='ar' name='ar' value='$areaId'></input>";
	}
	
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Insert Role";
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
	echo "<td valign='top'><input type='hidden' id='idRole' name='idRole' value='$idRole' autocomplete='off'></input>$idRole</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameRole' name='nameRole' value='$nameRole' autocomplete='off'></input>$nameRole</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descRole' name='descRole' value='$descRole' autocomplete='off'></input>$descRole</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formRole\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		if ($ref == "changeRole" && $userId != "" && $areaId != "") {
			$url64 = base64_encode("setting=1&m=u&sm=c&i=$userId&ar=$areaId&r=$idRole");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			
			$username = $User->GetUserName($userId);
			$areaname = $User->GetAreaName($areaId);
			
			echo "Automatically saving granted role to user '$username' in area '$areaname'<br />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		} else {
			$url64 = base64_encode("setting=1&m=r");
			echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
			echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
		}
	}
	echo "</div>";
	echo "</form>";
}

function insertRole() {
	global $Setting, $User, $id, $idRole, $nameRole, $descRole, $ref, $u, $ar, $mode;
	
	// $idRole = (isset($_REQUEST['idRole']) ? trim($_REQUEST['idRole']) : '');
	// $nameRole = (isset($_REQUEST['nameRole']) ? trim($_REQUEST['nameRole']) : '');
	// $descRole = (isset($_REQUEST['descRole']) ? trim($_REQUEST['descRole']) : '');
	
	if ($idRole == "") {
		// initial insert
		$idRole = "rm" . $Setting->GetNextRoleId();
	}

	$idRole = htmlentities($idRole, ENT_QUOTES);
	$nameRole = htmlentities($nameRole, ENT_QUOTES);
	$descRole = htmlentities($descRole, ENT_QUOTES);	
	
	$url64 = base64_encode("setting=1&m=r&sm=i&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<div class='subTitle'>Insert new role</div>";
	echo "<div class='spacer10'></div>";

	// NEW: bisa back to change role from user
	// $ref = (isset($_REQUEST['ref']) ? trim($_REQUEST['ref']) : '');
	// $userId = (isset($_REQUEST['u']) ? trim($_REQUEST['u']) : '');
	// $areaId = (isset($_REQUEST['ar']) ? trim($_REQUEST['ar']) : '');
	$areaId = $ar;
	$userId = $u;
	if ($ref == "changeRole" && $userId != "" && $areaId != "") {
		echo "<input type='hidden' id='ref' name='ref' value='changeRole'></input>";
		echo "<input type='hidden' id='u' name='u' value='$userId'></input>";
		echo "<input type='hidden' id='ar' name='ar' value='$areaId'></input>";
		
		$username = $User->GetUserName($userId);
		$areaname = $User->GetAreaName($areaId);
		$username = htmlentities($username);
		$areaname = htmlentities($areaname);
		
		$url64 = base64_encode("setting=1&m=u&sm=l&i=$userId");
		echo "<a href='main.php?param=$url64'>&lsaquo; Back to change role from user '$username' in area '$areaname'</a>";
		echo "<div class='spacer10'></div>";
	}
	
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='idRole' name='idRole' length='30' size='20' value='$idRole' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameRole' name='nameRole' length='100' size='30' value='$nameRole' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textArea id='descRole' name='descRole' cols='50' rows='3'>$descRole</textArea></td>";
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

function editRoleAction() {
	global $Setting, $idRole, $nameRole, $descRole;
	
	// $idRole = (isset($_REQUEST['idRole']) ? trim($_REQUEST['idRole']) : '');
	// $nameRole = (isset($_REQUEST['nameRole']) ? trim($_REQUEST['nameRole']) : '');
	// $descRole = (isset($_REQUEST['descRole']) ? trim($_REQUEST['descRole']) : '');
	
	$bOK = $Setting->EditRole($idRole, $nameRole, $descRole);
	
	$url64 = base64_encode("setting=1&m=r&sm=e");
	echo "<form method='POST' action='main.php?param=$url64' id='formRole'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<th colspan='3' align='left'>";
	echo "Edit Role";
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
	echo "<td valign='top'><input type='hidden' id='idRole' name='idRole' value='$idRole' autocomplete='off'></input>$idRole</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='nameRole' name='nameRole' value='$nameRole' autocomplete='off'></input>$nameRole</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='descRole' name='descRole' value='$descRole' autocomplete='off'></input>$descRole</td>";
	echo "</tr>";
	echo "</table>";
	
	echo "<div class='spacer20'></div>";
	echo "<div>";
	if (!$bOK) {
		echo "<a href='#' onClick='document.getElementById(\"formRole\").submit();'>Edit form</a>&nbsp;&nbsp;";
	} else {
		$url64 = base64_encode("setting=1&m=r");
		echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64' />";
		echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
	}
	echo "</div>";
}

function editRole() {
	global $Setting, $id;
	
	$arRole = $Setting->GetRoleDetail($id);
	$idRole = $arRole["id"];
	$nameRole = $arRole["name"];
	$descRole = $arRole["desc"];
	
	// quote fix
	$idRole = htmlentities($idRole, ENT_QUOTES);
	$nameRole = htmlentities($nameRole, ENT_QUOTES);
	$descRole = htmlentities($descRole, ENT_QUOTES);
	
	echo "<div class='subTitle'>Edit role '$nameRole'</div>";
	echo "<div class='spacer10'></div>";
	
	$url64 = base64_encode("setting=1&m=r&sm=e&a=1");
	echo "<form method='POST' action='main.php?param=$url64'>";
	echo "<table class='transparent'>";
	echo "<tr>";
	echo "<td valign='top'>Id</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='hidden' id='idRole' name='idRole' value='$idRole' autocomplete='off'></input>$idRole</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Name</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><input type='text' id='nameRole' name='nameRole' length='100' size='30' value='$nameRole' autocomplete='off'></input></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td valign='top'>Description</td>";
	echo "<td valign='top'>:</td>";
	echo "<td valign='top'><textArea id='descRole' name='descRole' cols='50' rows='3'>$descRole</textArea></td>";
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

function listModule() {
	global $Setting, $User, $id, $data;
	
	$uid = null;
	if ($data) {
		$uid = $data->uid;
	}
	
	$arModule = null;
	$arRole = null;
	
	$bOK = $Setting->GetModule($arModule);
	if ($bOK) {
		$roleName = $User->GetRoleName($id);
		echo "<div class='subTitle'>List modules in role '$roleName'</div>";
		echo "<div class='spacer10'></div>";
	
		// var_dump($arModule);
		foreach ($arModule as $mod) {
			$moduleId = $mod["id"];
			$moduleName = $mod["name"];
			$arFunction = null;
			$bOK = $Setting->GetFunctionInModule("", $moduleId, $arFunction);
			
			echo "<div class='subSubTitle'>$moduleName";
			echo "<span style='font-size:10pt; font-weight:normal;'>(Id: $moduleId)</span>";
			$url64 = base64_encode("setting=1&m=f&sm=i&mid=$moduleId&r=$id");
			echo "<span style='font-size:10pt; font-weight:normal; padding-left:20px;'><a href='main.php?param=$url64'>Insert new function to module '$moduleName'</a></span>";
			echo "</div>";
			// echo "<div class='spacer10'></div>";
			
			if ($bOK) {
				echo "<table cellspacing='3px' cellpadding='3px'>";
				echo "<tr>";
				echo "<th>Option</th>";
				echo "<th>Id</th>";
				echo "<th>Module</th>";
				echo "<th>Name</th>";
				echo "<th>Page</th>";
				echo "<th>Position</th>";
				echo "<th>Image</th>";
				echo "<th>Permission</th>";
				echo "</tr>";
			
				// var_dump($arFunction);
				foreach ($arFunction as $func) {
					$funcId = $func["id"];
					$funcMid = $func["mid"];
					$moduleName = $User->GetModuleName($funcMid);
					$name = $func["name"];
					$page = $func["page"];
					$pos = $func["pos"];
					$image = $func["image"];
					
					$grantedFunction = $Setting->IsFunctionGrantedInRole($funcMid, $funcId, $id);
					
					echo "<tr>";
					echo "<td align='center'>";
					echo "&nbsp;";
					// ---- Show edit & delete
					if ($grantedFunction) {
						$url64 = base64_encode("setting=1&m=r&sm=c&i=$moduleId&r=$id&f=$funcId");
						echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/delete.png' alt='Decline permission' title='Decline permission'></img></a>";
						echo "&nbsp;";
					} else {
						$url64 = base64_encode("setting=1&m=r&sm=g&i=$moduleId&r=$id&f=$funcId");
						echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/accept.png' alt='Grant permission' title='Grant permission'></img></a>";
						echo "&nbsp;";
					}
					echo "</td>";
					echo "<td>$funcId&nbsp;</td>";
					echo "<td>$moduleName&nbsp;</td>";
					echo "<td>$name&nbsp;</td>";
					echo "<td>$page&nbsp;</td>";
					if ($pos == 0) {
						// Per terminal
						echo "<td>Per terminal&nbsp;</td>";
					} else if ($pos == 1) {
						// Per module
						echo "<td>Per module&nbsp;</td>";
					} else if ($pos == 2) {
						// Hide
						echo "<td>Hide&nbsp;</td>";
					}
					echo "<td>";
					echo "&nbsp;<img src='image/icon/$image' alt=''></img>&nbsp;&nbsp;$image&nbsp;";
					echo "</td>";
					echo "<td align='center'>";
					if ($grantedFunction) {
						echo "<span style='color:green; font-weight:bold;'>Granted</span>";
						$granted = true;
					} else {
						echo "<span style='color:red; font-weight:bold;'>Declined</span>";
					}
					echo "</td>";
					echo "</tr>";
				}
				
				// Grant/Decline all permission
				echo "<tr>";
				echo "<td>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=r&sm=c&i=$moduleId&r=$id&f=all");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/delete.png' alt='Decline permission' title='Decline permission'></img></a>";
				echo "&nbsp;";
				$url64 = base64_encode("setting=1&m=r&sm=g&i=$moduleId&r=$id&f=all");
				echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/accept.png' alt='Grant permission' title='Grant permission'></img></a>";
				echo "&nbsp;";
				echo "</td>";
				echo "<td colspan='7' align='left'>";
				echo "<span style='color:red; font-weight:bold;'>Declined all</span>";
				echo "/";
				echo "<span style='color:green; font-weight:bold;'>Granted all</span>";
				echo "</td>";
				echo "</tr>";
				
				echo "</table>";
				echo "<div class='spacer20'></div>";
			} else {
				// Empty
				echo "<div class='spacer5'></div>";
				echo "<div>&nbsp;&nbsp;No function defined</div>";
				echo "<div class='spacer20'></div>";
			}
		}
	}
}

function grantModule() {
	global $Setting, $function, $id, $r;
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$roleId = $r;
	$moduleId = $id;
	if ($roleId == "") {
		return false;
	}
	
	// Decline module
	$bOK = $Setting->GrantFunction($roleId, $moduleId, $function, true);
	if ($bOK) {
		echo "<div>Permission successfully granted...</div>";
	} else {
		echo "<div>Permission fail to grant...</div>";
	}
	$url64 = base64_encode("setting=1&m=r&sm=l&i=$roleId");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function declineModule() {
	global $Setting, $function, $id, $r;
	// $roleId = (isset($_REQUEST['r']) ? trim($_REQUEST['r']) : '');
	$roleId = $r;
	$moduleId = $id;
	if ($roleId == "") {
		return false;
	}
	
	// Decline module
	$bOK = $Setting->GrantFunction($roleId, $moduleId, $function, false);
	if ($bOK) {
		echo "<div>Permission successfully declined...</div>";
	} else {
		echo "<div>Permission fail to decline...</div>";
	}
	$url64 = base64_encode("setting=1&m=r&sm=l&i=$roleId");
	echo "<meta http-equiv='REFRESH' content='1;url=main.php?param=$url64'>";
	echo "Wait a moment... <img src='image/icon/wait.gif' alt=''></img>";
}

function chooseAutoLoad() {
	global $Setting, $User, $id, $choose, $chLoad;
	
	$appLoad = "";
	$moduleLoad = "";
	
	// get role information
	$arRole = $Setting->GetRoleDetail($id);
	$arAccessed = $User->GetAccessable($id);
	//var_dump($arAccessed);
	if ($arRole != null) {
		$roleName = $arRole["name"];
		$roleDesc = $arRole["desc"];
		$autoLoad = $arRole["autoLoad"];
		$nameLoad = "-";
		
		if (isset($chLoad)) {
			$Setting->EditRole($id, $roleName, $roleDesc, $chLoad);
			$autoLoad = $chLoad;
		}
		
		if ($autoLoad != null) {
			$ar = explode(",", $autoLoad);
			
			$count = count($ar);
			$areaId = $ar[0];
			$arArea = $Setting->GetAreaDetail($areaId);
			$nameLoad = "Application '" . $arArea["name"] . "'";
			$appLoad = $areaId;
			if ($count > 1) {
				$moduleId = $ar[1];
				$arModule = $Setting->GetModuleDetail($moduleId);
				$nameLoad .= "&nbsp;&nbsp;&nbsp;Module '" . $arModule["name"] . "' ";
				$moduleLoad = $moduleId;
			}
		}
	}
	// var_dump($arAccessed);
	
	// print header
	echo "<div class='subTitle'>Choose Auto-Load for Role '$roleName'</div>";
	echo "<div class='spacer10'></div>";
	echo "<div>Auto-Load Chosen: <b>$nameLoad</b>";
	if ($nameLoad != "-") {
		$url64 = base64_encode("setting=1&m=r&sm=a&i=$id&chLoad=");
		echo "<a href='main.php?param=$url64'><img border='0' src='image/icon/cancel.png' alt='Clear auto-load' title='Clear auto-load'></img></a>";
	}
	echo "</div>";
	echo "<div class='spacer10'></div>";
	echo "<table cellspacing='3px' cellpadding='3px'>";
	echo "<tr>";
	echo "<th>Application</th>";
	echo "<th>Module</th>";
	echo "</tr>";
	
	// iterate accessed area/module
	$arAppId = array();
	$arModuleId = array();
	$arAppName = array();
	$arModuleName = array();
	$nApp = 0;
	$nModule = 0;
	foreach ($arAccessed as $iAccess) {
		$appId = $iAccess["appId"];
		$moduleId = $iAccess["moduleId"];
		$appName = $iAccess["appName"];
		$moduleName = $iAccess["moduleName"];
		
		$st = "<tr>";
		$empty = 0;
		
		if (!in_array($appId, $arAppId)) {
			echo "<tr>";
			echo "<td colspan='2' style='border-bottom: #7c7c7c solid 1px; font-size: 1%;' height='1'>&nbsp;</td>";
			echo "</tr>";
		
			// write
			if ($appLoad == $appId && $moduleLoad == "") {
				$st .= "<td><b>$appName</b></td>";
			} else {
				$url64 = base64_encode("setting=1&m=r&sm=a&i=$id&chLoad=$appId");
				$st .= "<td><a href='main.php?param=$url64'>$appName</a></td>";
			}
			
			// add to array
			$arAppId[$nApp] = $appId;
			$arAppName[$nApp] = $appName;
			$nApp++;
			
			$printBorder = true;
		} else {
			// already written
			$st .= "<td>&nbsp;</td>";
			$empty++;
		}
		
		if ($appLoad == $appId && $moduleLoad == $moduleId) {
			$st .= "<td><b>$moduleName</b></td>";
		} else {
			$url64 = base64_encode("setting=1&m=r&sm=a&i=$id&chLoad=$appId,$moduleId");
			$st .= "<td><a href='main.php?param=$url64'>$moduleName</a></td>";
		}
		
		// add to array
		$arModuleId[$nModule] = $moduleId;
		$arModuleName[$nModule] = $moduleName;
		$nModule++;
		
		$st .= "</tr>";
		if ($empty < 2) {
			echo $st;
		}
	}
	
	// calibrate end size
	$end = $nModule;
	if ($end < $nApp) {
		$end = $nApp;
	}
	
	// write list
	/*
	for ($i = 0; $i < $end; $i++) {
		echo "<tr>";
		
		// write app
		if ($i < $nApp) {
			$appId = $arAppId[$i];
			$appName = $arAppName[$i];
			
			$url64 = base64_encode("setting=1&m=r&sm=a&choose=1&i=$id");
			echo "<td><a href='main.php?param=$url64'>$appName</a></td>";
		} else {
			// empty
			echo "<td>&nbsp;</td>";
		}
		
		// write module
		if ($i < $nModule) {
			$moduleId = $arModuleId[$i];
			$moduleName = $arModuleName[$i];
			
			$url64 = base64_encode("setting=1&m=r&sm=a&choose=1&i=$id");
			echo "<td><a href='main.php?param=$url64'>$moduleName</a></td>";
		} else {
			// empty
			echo "<td>&nbsp;</td>";
		}
		
		echo "</tr>";
	}
	*/

	echo "</table>";
}

?>
